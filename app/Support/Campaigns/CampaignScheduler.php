<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign as C;
use App\Models\AdCampaignPayment;
use App\Models\AdPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Tâche planifiée des campagnes (toutes les heures, commande campaigns:tick) :
 * démarre les mises en avant programmées, met en pause ou relance selon le stock, termine celles
 * dont la date est passée, supprime les impayées de plus de 24 h, envoie les rappels
 * (visuels à valider, fin dans 2 jours) et annule les campagnes dont les visuels restent sans réponse.
 * Chaque campagne est traitée à part : une erreur sur l'une n'arrête pas les autres.
 */
class CampaignScheduler
{
    public const UNPAID_HOURS          = 24;
    /** Paiement Mobile Money encore en cours : la campagne n'est pas supprimée pendant ce délai. */
    public const PENDING_PAYMENT_HOURS = 2;
    public const CREATIVES_REMIND_HOURS = 48;
    public const CREATIVES_EXPIRE_DAYS  = 5;
    public const ENDING_SOON_DAYS       = 2;

    public function __construct(private CampaignCheckout $checkout, private CampaignNotifier $notifier) {}

    /** @return array<string, int> nombre de campagnes traitées par étape */
    public function run(): array
    {
        return [
            'demarrees'       => $this->startScheduled(),
            'en_pause'        => $this->pauseOutOfStock(),
            'reprises'        => $this->resumeBackInStock(),
            'terminees'       => $this->finishEnded(),
            'supprimees'      => $this->deleteUnpaid(),
            'rappels_visuels' => $this->remindCreatives(),
            'annulees'        => $this->expireCreatives(),
            'fin_proche'      => $this->warnEndingSoon(),
        ];
    }

    /** Mises en avant payées dont la date de début est arrivée. (Les pubs réseaux sociaux sont mises en ligne par l'équipe.) */
    public function startScheduled(): int
    {
        return $this->each(
            C::where('status', C::STATUS_PROGRAMMEE)->where('type', AdPackage::TYPE_BOOST)->whereDate('starts_on', '<=', today()->toDateString()),
            fn (C $c) => CampaignStateMachine::transition($c, C::STATUS_EN_COURS)
        );
    }

    /** Mise en avant dont un produit promu est en rupture ou désactivé : en pause, jours restants reportés. */
    public function pauseOutOfStock(): int
    {
        return $this->each(
            C::with('products.variants')->where('status', C::STATUS_EN_COURS)->where('type', AdPackage::TYPE_BOOST),
            function (C $c) {
                if ($c->products->isEmpty() || $c->products->every(fn ($p) => CampaignRules::isPromotable($p))) {
                    return false;
                }
                CampaignStateMachine::transition($c, C::STATUS_EN_PAUSE, ['pause_reason' => 'rupture']);
            }
        );
    }

    /** Pause pour rupture : reprend quand tous les produits promus sont de nouveau en vente. */
    public function resumeBackInStock(): int
    {
        return $this->each(
            C::with('products.variants')->where('status', C::STATUS_EN_PAUSE)->where('pause_reason', 'rupture'),
            function (C $c) {
                if (!$c->products->every(fn ($p) => CampaignRules::isPromotable($p))) {
                    return false;
                }
                CampaignStateMachine::transition($c, C::STATUS_EN_COURS);
            }
        );
    }

    /** Campagnes en cours dont la date de fin est passée. (En pause : la fin est repoussée à la reprise.) */
    public function finishEnded(): int
    {
        return $this->each(
            C::where('status', C::STATUS_EN_COURS)->whereDate('ends_on', '<', today()->toDateString()),
            fn (C $c) => CampaignStateMachine::transition($c, C::STATUS_TERMINEE)
        );
    }

    /**
     * Campagnes non payées depuis plus de 24 h : supprimées, leur place est libérée.
     * Un paiement Mobile Money en cours est d'abord vérifié auprès de l'agrégateur ; s'il a été
     * ouvert il y a moins de 2 h, la campagne attend le prochain passage.
     */
    public function deleteUnpaid(): int
    {
        return $this->each(
            C::where('status', C::STATUS_A_PAYER)->where('created_at', '<=', now()->subHours(self::UNPAID_HOURS)),
            function (C $c) {
                foreach ($c->payments()->where('status', AdCampaignPayment::STATUS_EN_ATTENTE)->get() as $payment) {
                    $this->checkout->refresh($payment);
                }
                $c->refresh();
                $recentPending = $c->payments()->where('status', AdCampaignPayment::STATUS_EN_ATTENTE)
                    ->where('created_at', '>', now()->subHours(self::PENDING_PAYMENT_HOURS))->exists();
                if ($c->status !== C::STATUS_A_PAYER || $recentPending) {
                    return false;
                }
                DB::transaction(function () use ($c) {
                    $deleted = C::whereKey($c->id)->where('status', C::STATUS_A_PAYER)->delete();
                    if ($deleted) {
                        // Lignes liées supprimées explicitement (aussi sans clés étrangères en cascade)
                        $c->items()->delete();
                        $c->payments()->delete();
                    }
                });
            }
        );
    }

    /** Visuels envoyés il y a 48 h sans réponse : un rappel au vendeur. */
    public function remindCreatives(): int
    {
        return $this->each(
            C::where('status', C::STATUS_VISUELS_A_VALIDER)->whereNull('creatives_reminded_at')
                ->where('creatives_sent_at', '<=', now()->subHours(self::CREATIVES_REMIND_HOURS))
                ->where('creatives_sent_at', '>', now()->subDays(self::CREATIVES_EXPIRE_DAYS)),
            function (C $c) {
                $c->forceFill(['creatives_reminded_at' => now()])->save();
                $this->notifier->shop($c, 'rappel_visuels');
            }
        );
    }

    /** Visuels sans réponse depuis 5 jours : campagne annulée, budget pub remboursé sur le Crédit Shopio. */
    public function expireCreatives(): int
    {
        return $this->each(
            C::where('status', C::STATUS_VISUELS_A_VALIDER)->where('creatives_sent_at', '<=', now()->subDays(self::CREATIVES_EXPIRE_DAYS)),
            function (C $c) {
                $refund = DB::transaction(function () use ($c) {
                    $refund = CampaignRefundPolicy::amount($c, CampaignRefundPolicy::CASE_EXPIRATION_VISUELS);
                    CampaignStateMachine::transition($c, C::STATUS_ANNULEE);
                    $this->checkout->refund($c, $refund, 'Campagne n°' . $c->id . ' annulée : visuels non validés');
                    return $refund;
                });
                $this->notifier->shop($c->fresh(), 'annulee_visuels', ['refund' => $refund]);
            }
        );
    }

    /** Fin dans 2 jours : le vendeur peut prolonger. Une seule fois par campagne, pas pour celles de 3 jours (annoncée dès le début). */
    public function warnEndingSoon(): int
    {
        return $this->each(
            C::where('status', C::STATUS_EN_COURS)->whereNull('ending_soon_notified_at')
                ->where('duration_days', '>', self::ENDING_SOON_DAYS + 1)
                ->whereDate('ends_on', '>=', today()->toDateString())
                ->whereDate('ends_on', '<=', today()->addDays(self::ENDING_SOON_DAYS)->toDateString()),
            function (C $c) {
                $c->forceFill(['ending_soon_notified_at' => now()])->save();
                $this->notifier->shop($c, 'fin_proche');
            }
        );
    }

    /** Applique $action à chaque campagne ; $action renvoie false quand il n'y avait rien à faire. */
    private function each(Builder $query, callable $action): int
    {
        $done = 0;
        foreach ($query->orderBy('id')->get() as $campaign) {
            try {
                if ($action($campaign) !== false) {
                    $done++;
                }
            } catch (InvalidCampaignTransition) {
                // Changée entre-temps par une autre requête : rien à faire
            } catch (\Throwable $e) {
                report($e);
            }
        }
        return $done;
    }
}
