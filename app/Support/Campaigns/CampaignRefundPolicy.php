<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;

/**
 * Montant remboursé (sur le Crédit Shopio) quand une campagne s'arrête avant d'avoir été diffusée :
 *  - refus par l'équipe Shopio : prix intégral ;
 *  - annulation par le vendeur avant le lancement : prix intégral pour une mise en avant ;
 *    pour les réseaux sociaux, budget pub seulement si les visuels ont déjà été créés ;
 *  - annulation automatique (visuels non validés sous 5 jours) : budget pub ;
 *  - après le lancement : rien.
 */
class CampaignRefundPolicy
{
    public const CASE_REFUS              = 'refus';
    public const CASE_ANNULATION_VENDEUR = 'annulation_vendeur';
    public const CASE_EXPIRATION_VISUELS = 'expiration_visuels';

    public static function amount(AdCampaign $campaign, string $case): int
    {
        if (!$campaign->paid_at || self::launched($campaign)) {
            return 0;
        }

        return match ($case) {
            self::CASE_REFUS              => $campaign->price_gnf,
            self::CASE_EXPIRATION_VISUELS => $campaign->ad_budget_gnf,
            self::CASE_ANNULATION_VENDEUR => $campaign->isSocial() && self::creativesCreated($campaign)
                ? $campaign->ad_budget_gnf
                : $campaign->price_gnf,
            default => throw new \InvalidArgumentException("Cas de remboursement inconnu : {$case}"),
        };
    }

    public static function launched(AdCampaign $campaign): bool
    {
        return $campaign->launched_at !== null || in_array($campaign->status, [
            AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE, AdCampaign::STATUS_TERMINEE,
        ], true);
    }

    private static function creativesCreated(AdCampaign $campaign): bool
    {
        return $campaign->creatives_sent_at !== null || $campaign->creatives()->exists();
    }
}
