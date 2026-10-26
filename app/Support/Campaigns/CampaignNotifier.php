<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\AdCampaignPayment;
use App\Models\User;
use App\Notifications\CampaignNotification;
use App\Services\PushService;
use App\Support\Sms\SmsSender;
use Illuminate\Support\Facades\Log;

/**
 * Prévient le vendeur (propriétaire de la boutique) ou l'équipe Shopio d'un événement d'une campagne :
 * notification dans le compte + push ; SMS en plus pour « visuels prêts » et « campagne refusée ».
 * Un échec de push ou de SMS ne bloque jamais l'action qui a déclenché la notification.
 */
class CampaignNotifier
{
    /** Rôles de l'équipe Shopio qui suivent les campagnes. */
    public const TEAM_ROLES = ['superadmin', 'staff', 'marketing'];

    /** Événements envoyés aussi par SMS au vendeur. */
    public const SMS_EVENTS = ['visuels_prets', 'refusee'];

    public function __construct(private SmsSender $sms) {}

    /** @param array $context données en plus du message (ex. 'refund' => montant remboursé) */
    public function shop(AdCampaign $campaign, string $event, array $context = []): void
    {
        $owner = $campaign->shop?->user;
        if (!$owner) {
            return;
        }
        [$title, $body, $url, $action] = $this->vendorMessage($campaign, $event, $context);
        $owner->notify(new CampaignNotification($campaign->id, $event, $title, $body, $url, $action));
        $this->push($owner, $title, $body, $url);

        if (in_array($event, self::SMS_EVENTS, true) && ($phone = $owner->phone ?: $campaign->shop->phone)) {
            try {
                $this->sms->send($phone, $this->smsText($campaign, $event, $context));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function team(AdCampaign $campaign, string $event, array $context = []): void
    {
        [$title, $body] = $this->teamMessage($campaign, $event, $context);
        $url = route('admin.campaigns.show', $campaign, false);
        foreach (User::whereIn('role', self::TEAM_ROLES)->get() as $member) {
            $member->notify(new CampaignNotification($campaign->id, $event, $title, $body, $url, 'Ouvrir la campagne'));
            $this->push($member, $title, $body, $url);
        }
    }

    /** @return array{0: string, 1: string, 2: string, 3: ?string} titre, texte, lien, libellé du bouton */
    private function vendorMessage(AdCampaign $c, string $event, array $context): array
    {
        $show  = route('boutique.campaigns.show', $c, false);
        $title = '« ' . $c->title() . ' »';
        $end   = $c->ends_on->translatedFormat('j F');

        return match ($event) {
            'paiement_recu' => ['Paiement reçu', "Ta campagne {$title} est payée (" . CampaignPricing::format($c->price_gnf) . '). '
                . ($c->isSocial() ? "L'équipe Shopio prépare tes visuels."
                    : ($c->starts_on->lte(today()) ? "Elle est en ligne dès maintenant, jusqu'au {$end}." : 'Elle démarre le ' . $c->starts_on->translatedFormat('j F') . '.')), $show, 'Voir la campagne'],
            'visuels_prets' => ['Tes visuels sont prêts', "Valide-les ou demande une modification pour lancer ta campagne {$title}.", $show . '#visuels', 'Valider les visuels'],
            'rappel_visuels' => ['Tes visuels attendent ta validation', "Sans réponse d'ici le " . $c->creatives_sent_at?->copy()->addDays(CampaignScheduler::CREATIVES_EXPIRE_DAYS)->translatedFormat('j F')
                . ', la campagne sera annulée et le budget pub remboursé sur ton Crédit Shopio.', $show . '#visuels', 'Valider les visuels'],
            'en_ligne' => ['Ta campagne est en ligne', "{$title} est diffusée jusqu'au {$end}.", $show, 'Suivre les résultats'],
            'pause_rupture' => ['Campagne en pause : rupture de stock', ($context['product'] ?? 'Un produit promu') . ' est en rupture ou désactivé. '
                . 'Ta campagne reprendra toute seule quand il sera de nouveau en vente, et les jours restants seront reportés.', $show, 'Voir la campagne'],
            'reprise' => ['Ta campagne a repris', "Le produit est de nouveau en vente : {$title} est diffusée jusqu'au {$end}.", $show, 'Voir la campagne'],
            'fin_proche' => ['Ta campagne se termine dans 2 jours', "{$title} se termine le {$end}. Prolonge-la pour garder ta visibilité.",
                route('boutique.campaigns.create', ['depuis' => $c->id], false), 'Prolonger'],
            'rapport_pret' => ['Le bilan de ta campagne est prêt', $this->reportSummary($c), $show, 'Voir le bilan'],
            'refusee' => ['Campagne refusée', "Ta campagne {$title} a été refusée : {$c->refusal_reason}"
                . ($this->refunded($c) ? ' ' . CampaignPricing::format($this->refunded($c)) . ' ont été ajoutés à ton Crédit Shopio.' : ''), $show, 'Voir la campagne'],
            'annulee_visuels' => ['Campagne annulée', 'Les visuels n\'ont pas été validés sous ' . CampaignScheduler::CREATIVES_EXPIRE_DAYS . ' jours : la campagne est annulée'
                . (($context['refund'] ?? 0) > 0 ? ' et ' . CampaignPricing::format($context['refund']) . ' ont été ajoutés à ton Crédit Shopio.' : '.'), $show, 'Voir la campagne'],
            default => throw new \InvalidArgumentException("Notification de campagne inconnue : {$event}"),
        };
    }

    /** @return array{0: string, 1: string} */
    private function teamMessage(AdCampaign $c, string $event, array $context): array
    {
        $shop = $c->shop?->name ?? 'Une boutique';
        return match ($event) {
            'equipe_paiement_recu' => ["Paiement reçu · campagne n°{$c->id}", "{$shop} a payé " . CampaignPricing::format($c->price_gnf) . ' pour « ' . $c->title() . ' ».'
                . ($c->isSocial() ? ' Visuels à préparer.' : '')],
            'equipe_modification_demandee' => ["Modification demandée · campagne n°{$c->id}", "{$shop} : « " . ($context['comment'] ?? 'voir la campagne') . ' »'],
            'equipe_visuels_valides' => ["Visuels validés · campagne n°{$c->id}", "{$shop} a validé les visuels : mets la pub en ligne à partir du " . $c->starts_on->translatedFormat('j F') . '.'],
            default => throw new \InvalidArgumentException("Notification d'équipe inconnue : {$event}"),
        };
    }

    private function smsText(AdCampaign $c, string $event, array $context): string
    {
        return match ($event) {
            'visuels_prets' => "Shopio : les visuels de ta campagne n°{$c->id} sont prêts. Valide-les ici : " . route('boutique.campaigns.show', $c) . '#visuels',
            'refusee'       => "Shopio : ta campagne n°{$c->id} a été refusée ({$c->refusal_reason}). Le remboursement est sur ton Crédit Shopio.",
        };
    }

    private function reportSummary(AdCampaign $c): string
    {
        $r = CampaignResults::forCampaign($c);
        $n = fn (int $v, string $word) => number_format($v, 0, ',', ' ') . ' ' . $word . ($v > 1 ? 's' : '');
        return '« ' . $c->title() . ' » est terminée : ' . $n($r['impressions'], 'affichage') . ', ' . $n($r['clicks'], 'visite')
            . ', ' . $n($r['orders'], 'commande') . '. Consulte le bilan complet.';
    }

    private function refunded(AdCampaign $c): int
    {
        return (int) $c->payments()->where('status', AdCampaignPayment::STATUS_REMBOURSE)->sum('refund_amount_gnf');
    }

    private function push(User $user, string $title, string $body, string $url): void
    {
        try {
            app(PushService::class)->sendToUser($user, $title, $body, 1, $url);
        } catch (\Throwable $e) {
            Log::warning('[Campagnes] Push non envoyé : ' . $e->getMessage(), ['user' => $user->id]);
        }
    }
}
