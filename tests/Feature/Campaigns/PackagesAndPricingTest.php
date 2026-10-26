<?php

use App\Models\AdPackage;
use App\Support\Campaigns\CampaignPricing;
use Illuminate\Support\Carbon;
use Tests\Support\CampaignFixtures as F;

test('les 6 formules de départ sont créées par la migration', function () {
    expect(AdPackage::where('type', 'boost')->pluck('code')->all())
        ->toEqualCanonicalizing(['boost_produit', 'top_categorie', 'boutique_une'])
        ->and(AdPackage::where('type', 'social')->pluck('code')->all())
        ->toEqualCanonicalizing(['social_starter', 'social_pro', 'social_premium']);
});

test('mise en avant : 3 jours = 50 %, 30 jours = 3 fois le prix 7 jours', function (string $code, int $week) {
    $package = F::package($code);
    expect($package->durations())->toBe([3, 7, 30])
        ->and($package->priceFor(7))->toBe($week)
        ->and($package->priceFor(3))->toBe(intdiv($week, 2))
        ->and($package->priceFor(30))->toBe($week * 3);
})->with([
    ['boost_produit', 50000],
    ['top_categorie', 100000],
    ['boutique_une', 150000],
]);

test('réseaux sociaux : prix = budget pub + frais Shopio', function (string $code, int $days, int $budget, int $fee) {
    $quote = CampaignPricing::quote(F::package($code), $days);
    expect($quote)->toBe([
        'duration_days' => $days,
        'price_gnf'     => $budget + $fee,
        'ad_budget_gnf' => $budget,
        'fee_gnf'       => $fee,
    ]);
})->with([
    ['social_starter', 7, 200000, 100000],
    ['social_pro', 14, 550000, 200000],
    ['social_premium', 30, 1150000, 350000],
]);

test('le devis d\'une mise en avant est entièrement en frais Shopio', function () {
    expect(CampaignPricing::quote(F::package('boost_produit'), 30))
        ->toBe(['duration_days' => 30, 'price_gnf' => 150000, 'ad_budget_gnf' => 0, 'fee_gnf' => 150000]);
});

test('une durée non proposée est refusée', function () {
    CampaignPricing::quote(F::package('social_pro'), 7);
})->throws(InvalidArgumentException::class);

test('un prix modifié par l\'admin est pris en compte sans toucher au code', function () {
    $package = F::package('boost_produit');
    $package->update(['prices' => ['3' => 30000, '7' => 60000, '30' => 160000]]);
    expect(CampaignPricing::quote($package->fresh(), 7)['price_gnf'])->toBe(60000);
});

test('une campagne de 7 jours qui commence le 1er finit le 7', function () {
    expect(CampaignPricing::endDate(Carbon::parse('2026-11-01'), 7)->toDateString())->toBe('2026-11-07');
});

test('les montants s\'affichent avec des espaces', function () {
    expect(CampaignPricing::format(750000))->toBe("750\u{00A0}000\u{00A0}GNF");
});
