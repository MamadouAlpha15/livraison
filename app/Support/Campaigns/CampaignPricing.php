<?php

namespace App\Support\Campaigns;

use App\Models\AdPackage;
use Carbon\CarbonInterface;

/**
 * Devis d'une campagne, toujours calculé côté serveur à partir de la formule
 * (jamais à partir d'un montant envoyé par le formulaire).
 */
class CampaignPricing
{
    /**
     * @return array{duration_days:int, price_gnf:int, ad_budget_gnf:int, fee_gnf:int}
     * @throws \InvalidArgumentException si la formule ne propose pas cette durée
     */
    public static function quote(AdPackage $package, int $days): array
    {
        $price = $package->priceFor($days);
        if ($price === null) {
            throw new \InvalidArgumentException("La formule « {$package->name} » ne propose pas {$days} jours.");
        }

        // Réseaux sociaux : budget pub + frais Shopio, affichés sur deux lignes de la facture.
        // Mise en avant : tout est frais Shopio.
        $adBudget = $package->isSocial() ? $package->ad_budget_gnf : 0;

        return [
            'duration_days' => $days,
            'price_gnf'     => $price,
            'ad_budget_gnf' => $adBudget,
            'fee_gnf'       => $price - $adBudget,
        ];
    }

    /** Dernier jour de diffusion : une campagne de 7 jours qui commence le 1er finit le 7. */
    public static function endDate(CarbonInterface $startsOn, int $days): CarbonInterface
    {
        return $startsOn->copy()->startOfDay()->addDays($days - 1);
    }

    /** Montant affiché en GNF, avec des espaces insécables : « 750 000 GNF ». */
    public static function format(int $amount): string
    {
        return number_format($amount, 0, ',', "\u{00A0}") . "\u{00A0}GNF";
    }
}
