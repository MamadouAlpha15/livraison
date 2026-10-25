<?php

namespace App\Support\Campaigns;

use App\Models\AdCampaign;
use App\Models\Shop;
use App\Models\ShopCreditEntry;
use Illuminate\Support\Facades\DB;

/**
 * « Crédit Shopio » d'une boutique : alimenté par les remboursements de campagnes,
 * utilisable pour payer une campagne, non retirable.
 */
class ShopCredit
{
    public static function balance(Shop|int $shop): int
    {
        $shopId = $shop instanceof Shop ? $shop->id : $shop;
        return (int) ShopCreditEntry::where('shop_id', $shopId)->sum('amount_gnf');
    }

    public static function credit(Shop $shop, int $amount, string $reason, ?AdCampaign $campaign = null, ?string $note = null, ?int $by = null): ShopCreditEntry
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Un crédit doit être positif.');
        }
        return self::entry($shop, $amount, $reason, $campaign, $note, $by);
    }

    /**
     * Retire $amount du crédit. Le solde est relu sous verrou : deux paiements lancés en même
     * temps ne peuvent pas dépenser le même crédit.
     *
     * @throws InsufficientCredit
     */
    public static function debit(Shop $shop, int $amount, string $reason, ?AdCampaign $campaign = null, ?string $note = null, ?int $by = null): ShopCreditEntry
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Un débit doit être positif.');
        }

        return DB::transaction(function () use ($shop, $amount, $reason, $campaign, $note, $by) {
            Shop::whereKey($shop->id)->lockForUpdate()->first();
            $balance = self::balance($shop);
            if ($balance < $amount) {
                throw new InsufficientCredit(
                    'Ton Crédit Shopio (' . CampaignPricing::format($balance) . ') ne suffit pas pour payer '
                    . CampaignPricing::format($amount) . '.'
                );
            }
            return self::entry($shop, -$amount, $reason, $campaign, $note, $by);
        });
    }

    private static function entry(Shop $shop, int $amount, string $reason, ?AdCampaign $campaign, ?string $note, ?int $by): ShopCreditEntry
    {
        return ShopCreditEntry::create([
            'shop_id'        => $shop->id,
            'amount_gnf'     => $amount,
            'reason'         => $reason,
            'ad_campaign_id' => $campaign?->id,
            'note'           => $note,
            'created_by'     => $by,
        ]);
    }
}
