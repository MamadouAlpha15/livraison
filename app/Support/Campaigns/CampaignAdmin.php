<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\AdCampaignCreative;
use App\Models\AdCampaignStat;
use App\Models\AdPackage;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Actions de l'équipe Shopio (rôles superadmin, staff et marketing) sur les campagnes :
 * refus remboursé, pause, visuels, lancement, statistiques saisies, clôture, formules.
 */
class CampaignAdmin
{
    public const DISK = 'public';
    public const MAX_IMAGE_KB = 8 * 1024;
    public const MAX_VIDEO_KB = 50 * 1024;

    public function __construct(private CampaignCheckout $checkout) {}

    /**
     * Refus avant le lancement (produit interdit, photo trompeuse…) : remboursement intégral sur le Crédit Shopio.
     * Renvoie le montant remboursé.
     * @throws InvalidCampaignTransition
     */
    public function refuse(AdCampaign $campaign, string $reason, User $by): int
    {
        return DB::transaction(function () use ($campaign, $reason, $by) {
            $refund = CampaignRefundPolicy::amount($campaign, CampaignRefundPolicy::CASE_REFUS);
            CampaignStateMachine::transition($campaign, AdCampaign::STATUS_REFUSEE, ['refusal_reason' => $reason]);
            $this->checkout->refund($campaign, $refund, 'Campagne n°' . $campaign->id . ' refusée par l\'équipe Shopio', $by->id);
            return $refund;
        });
    }

    /** Pause décidée par l'équipe : le vendeur ne peut pas la lever lui-même. */
    public function pause(AdCampaign $campaign): AdCampaign
    {
        return CampaignStateMachine::transition($campaign, AdCampaign::STATUS_EN_PAUSE, ['pause_reason' => 'admin']);
    }

    /** @throws CampaignNotAllowed|InvalidCampaignTransition */
    public function resume(AdCampaign $campaign): AdCampaign
    {
        $unavailable = $campaign->isBoost() ? $campaign->products->reject(fn ($p) => CampaignRules::isPromotable($p)) : collect();
        if ($unavailable->isNotEmpty()) {
            throw new CampaignNotAllowed(['« ' . $unavailable->first()->name . ' » est en rupture de stock ou désactivé : la campagne reprendra quand il sera de nouveau en vente.']);
        }
        return CampaignStateMachine::transition($campaign, AdCampaign::STATUS_EN_COURS);
    }

    /**
     * Dépose un visuel pendant la préparation. Il fait partie de la proposition en cours
     * (tour = nombre de modifications demandées + 1).
     * @throws CampaignNotAllowed
     */
    public function addCreative(AdCampaign $campaign, UploadedFile $file, ?string $caption = null): AdCampaignCreative
    {
        $this->assertPreparing($campaign);
        $type = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image';

        return $campaign->creatives()->create([
            'file_path' => $file->store('campaign-creatives/' . $campaign->id, self::DISK),
            'type'      => $type,
            'caption'   => $caption,
            'status'    => AdCampaignCreative::STATUS_EN_ATTENTE,
            'round'     => $campaign->revision_count + 1,
        ]);
    }

    /** Retire un visuel pas encore envoyé au vendeur. @throws CampaignNotAllowed */
    public function deleteCreative(AdCampaignCreative $creative): void
    {
        $this->assertPreparing($creative->campaign);
        if ($creative->status !== AdCampaignCreative::STATUS_EN_ATTENTE) {
            throw new CampaignNotAllowed(['Ce visuel a déjà été vu par le vendeur : il reste dans l\'historique.']);
        }
        Storage::disk(self::DISK)->delete($creative->file_path);
        $creative->delete();
    }

    /** Envoie la proposition au vendeur, qui la valide ou demande une modification. @throws CampaignNotAllowed */
    public function sendCreatives(AdCampaign $campaign): AdCampaign
    {
        $this->assertPreparing($campaign);
        if (!$this->pendingCreatives($campaign)->exists()) {
            throw new CampaignNotAllowed(['Dépose au moins un visuel avant de l\'envoyer au vendeur.']);
        }
        return CampaignStateMachine::transition($campaign, AdCampaign::STATUS_VISUELS_A_VALIDER);
    }

    /** Visuels de la proposition en cours (pas encore vus, validés ou refusés par le vendeur). */
    public function pendingCreatives(AdCampaign $campaign)
    {
        return $campaign->creatives()->where('status', AdCampaignCreative::STATUS_EN_ATTENTE);
    }

    /**
     * Campagne mise en ligne. Réseaux sociaux : l'identifiant de la pub Meta / TikTok est obligatoire ;
     * la campagne commence le jour de sa mise en ligne et garde toute sa durée (dates recalculées si
     * elle part avant ou après la date prévue). Mise en avant : seulement à partir de sa date de début.
     * @throws CampaignNotAllowed|InvalidCampaignTransition
     */
    public function launch(AdCampaign $campaign, ?string $externalRef = null): AdCampaign
    {
        $externalRef = trim((string) $externalRef);
        $attributes = [];

        if ($campaign->isSocial()) {
            if ($externalRef === '') {
                throw new CampaignNotAllowed(['Colle l\'identifiant de la pub Meta ou TikTok avant de marquer la campagne lancée.']);
            }
            $attributes['external_ref'] = $externalRef;
            if (!$campaign->starts_on->isSameDay(today())) {
                $attributes['starts_on'] = today()->toDateString();
                $attributes['ends_on'] = CampaignPricing::endDate(today(), $campaign->duration_days)->toDateString();
            }
        } elseif ($campaign->starts_on->gt(today())) {
            throw new CampaignNotAllowed(['Cette mise en avant commence le ' . $campaign->starts_on->format('d/m/Y') . ' : elle démarrera toute seule ce jour-là.']);
        }

        return CampaignStateMachine::transition($campaign, AdCampaign::STATUS_EN_COURS, $attributes);
    }

    /**
     * Statistiques d'une journée pour une campagne réseaux sociaux (relevées dans Meta / TikTok).
     * Les visites, messages et commandes viennent du lien ?c= et sont comptés automatiquement.
     * @throws CampaignNotAllowed
     */
    public function recordStats(AdCampaign $campaign, CarbonInterface $day, int $impressions, int $spendGnf): AdCampaignStat
    {
        $day = Carbon::parse($day)->startOfDay();
        $errors = [];
        if (!$campaign->isSocial()) {
            $errors[] = 'Les statistiques d\'une mise en avant sont comptées automatiquement.';
        } elseif (!$campaign->launched_at) {
            $errors[] = 'La campagne n\'a pas encore été lancée.';
        } elseif ($day->lt($campaign->starts_on) || $day->gt($campaign->ends_on) || $day->gt(today())) {
            $errors[] = 'Choisis un jour entre le ' . $campaign->starts_on->format('d/m/Y') . ' et le ' . $campaign->ends_on->min(today())->format('d/m/Y') . '.';
        }
        if ($impressions < 0 || $spendGnf < 0) {
            $errors[] = 'Les nombres ne peuvent pas être négatifs.';
        }
        if ($errors) {
            throw new CampaignNotAllowed($errors);
        }

        return AdCampaignStat::updateOrCreate(
            ['ad_campaign_id' => $campaign->id, 'day' => $day->toDateString(), 'source' => 'admin'],
            ['impressions' => $impressions, 'spend_gnf' => $spendGnf]
        );
    }

    /** Clôture une campagne en cours ou en pause. @throws InvalidCampaignTransition */
    public function close(AdCampaign $campaign): AdCampaign
    {
        return CampaignStateMachine::transition($campaign, AdCampaign::STATUS_TERMINEE);
    }

    /**
     * Met à jour une formule. Mise en avant : un prix par durée et le nombre de places.
     * Réseaux sociaux : budget pub et frais Shopio, le prix est leur somme.
     */
    public function updatePackage(AdPackage $package, array $data): AdPackage
    {
        $changes = ['name' => $data['name'], 'is_active' => (bool) ($data['is_active'] ?? false)];

        if ($package->isSocial()) {
            $budget = (int) $data['ad_budget_gnf'];
            $fee    = (int) $data['fee_gnf'];
            $changes += [
                'ad_budget_gnf' => $budget,
                'fee_gnf'       => $fee,
                'prices'        => [(string) $package->duration_days => $budget + $fee],
            ];
        } else {
            $prices = [];
            foreach ($package->durations() as $days) {
                $prices[(string) $days] = (int) $data['prices'][$days];
            }
            $changes += ['prices' => $prices, 'max_actives' => (int) $data['max_actives']];
        }

        $package->update($changes);
        return $package;
    }

    /** @throws CampaignNotAllowed */
    private function assertPreparing(AdCampaign $campaign): void
    {
        if ($campaign->status !== AdCampaign::STATUS_EN_PREPARATION) {
            throw new CampaignNotAllowed(['Les visuels se déposent pendant la préparation de la campagne.']);
        }
    }
}
