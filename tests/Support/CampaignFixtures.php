<?php

namespace Tests\Support;

use App\Models\AdCampaign;
use App\Models\AdPackage;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Support\Campaigns\CampaignPricing;
use Illuminate\Support\Carbon;

/** Boutiques, produits et campagnes de test pour les tests « Campagnes ». */
class CampaignFixtures
{
    public static function shop(bool $approved = true): Shop
    {
        $owner = User::factory()->create(['role' => 'admin']);
        return Shop::forceCreate([
            'user_id'     => $owner->id,
            'name'        => 'Boutique ' . $owner->id,
            'email'       => "boutique{$owner->id}@test.invalid",
            'is_approved' => $approved,
        ]);
    }

    public static function product(Shop $shop, array $attributes = []): Product
    {
        return Product::forceCreate($attributes + [
            'shop_id'   => $shop->id,
            'name'      => 'Produit ' . uniqid(),
            'price'     => 100000,
            'stock'     => 10,
            'is_active' => true,
            'category'  => 'Beauté & Cosmétiques',
        ]);
    }

    public static function package(string $code): AdPackage
    {
        return AdPackage::where('code', $code)->firstOrFail();
    }

    /** Campagne créée directement dans un statut donné (sans passer par le paiement). */
    public static function campaign(Shop $shop, string $code, array $productIds = [], array $attributes = []): AdCampaign
    {
        $package = self::package($code);
        $days    = $attributes['duration_days'] ?? $package->duration_days;
        $start   = Carbon::parse($attributes['starts_on'] ?? today());
        $quote   = CampaignPricing::quote($package, $days);

        $campaign = AdCampaign::create($attributes + [
            'shop_id'       => $shop->id,
            'ad_package_id' => $package->id,
            'type'          => $package->type,
            'starts_on'     => $start->toDateString(),
            'ends_on'       => CampaignPricing::endDate($start, $days)->toDateString(),
            'duration_days' => $days,
            'status'        => AdCampaign::STATUS_A_PAYER,
            'price_gnf'     => $quote['price_gnf'],
            'ad_budget_gnf' => $quote['ad_budget_gnf'],
            'fee_gnf'       => $quote['fee_gnf'],
        ]);

        foreach ($productIds ?: [null] as $productId) {
            $campaign->items()->create(['product_id' => $productId]);
        }

        return $campaign->fresh();
    }
}
