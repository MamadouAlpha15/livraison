<?php

use App\Models\AdCampaign as C;
use App\Models\AdCampaignCreative;
use App\Models\ShopCreditEntry;
use App\Support\Campaigns\CampaignRefundPolicy as Refund;
use App\Support\Campaigns\CampaignStateMachine as SM;
use App\Support\Campaigns\InsufficientCredit;
use App\Support\Campaigns\ShopCredit;
use Tests\Support\CampaignFixtures as F;

test('le Crédit Shopio est la somme des mouvements', function () {
    $shop = F::shop();
    ShopCredit::credit($shop, 300000, ShopCreditEntry::REASON_REMBOURSEMENT);
    ShopCredit::debit($shop, 50000, ShopCreditEntry::REASON_PAIEMENT_CAMPAGNE);

    expect(ShopCredit::balance($shop))->toBe(250000);
});

test('un paiement plus grand que le crédit est refusé, sans mouvement', function () {
    $shop = F::shop();
    ShopCredit::credit($shop, 40000, ShopCreditEntry::REASON_REMBOURSEMENT);

    expect(fn () => ShopCredit::debit($shop, 50000, ShopCreditEntry::REASON_PAIEMENT_CAMPAGNE))
        ->toThrow(InsufficientCredit::class, 'ne suffit pas');
    expect(ShopCredit::balance($shop))->toBe(40000)
        ->and(ShopCreditEntry::where('shop_id', $shop->id)->count())->toBe(1);
});

test('le crédit d\'une boutique ne sert pas à une autre', function () {
    $a = F::shop();
    $b = F::shop();
    ShopCredit::credit($a, 100000, ShopCreditEntry::REASON_REMBOURSEMENT);

    expect(ShopCredit::balance($b))->toBe(0);
});

test('refus par Shopio : remboursement intégral', function () {
    $c = SM::transition(F::campaign(F::shop(), 'social_pro', [], ['starts_on' => today()->addDays(3)]), C::STATUS_PAYEE);
    expect(Refund::amount($c, Refund::CASE_REFUS))->toBe(750000);
});

test('annulation d\'une mise en avant avant le lancement : remboursement intégral', function () {
    $c = F::campaign(F::shop(), 'boutique_une', [], ['starts_on' => today()->addDays(5)]);
    $c = SM::transition(SM::transition($c, C::STATUS_PAYEE), C::STATUS_PROGRAMMEE);
    expect(Refund::amount($c, Refund::CASE_ANNULATION_VENDEUR))->toBe(150000);
});

test('annulation d\'une campagne réseaux sociaux : intégral avant les visuels, budget pub après', function () {
    $c = F::campaign(F::shop(), 'social_pro', [], ['starts_on' => today()->addDays(3)]);
    $c = SM::transition(SM::transition($c, C::STATUS_PAYEE), C::STATUS_EN_PREPARATION);
    expect(Refund::amount($c, Refund::CASE_ANNULATION_VENDEUR))->toBe(750000);

    AdCampaignCreative::create(['ad_campaign_id' => $c->id, 'file_path' => 'campagnes/1.jpg', 'type' => 'image']);
    expect(Refund::amount($c->fresh(), Refund::CASE_ANNULATION_VENDEUR))->toBe(550000);
});

test('visuels non validés sous 5 jours : budget pub remboursé', function () {
    $c = F::campaign(F::shop(), 'social_starter', [], ['starts_on' => today()->addDays(3)]);
    foreach ([C::STATUS_PAYEE, C::STATUS_EN_PREPARATION, C::STATUS_VISUELS_A_VALIDER] as $to) {
        $c = SM::transition($c, $to);
    }
    expect(Refund::amount($c, Refund::CASE_EXPIRATION_VISUELS))->toBe(200000);
});

test('après le lancement ou sans paiement : aucun remboursement', function () {
    $unpaid = F::campaign(F::shop(), 'boutique_une');
    expect(Refund::amount($unpaid, Refund::CASE_ANNULATION_VENDEUR))->toBe(0);

    $c = F::campaign(F::shop(), 'boutique_une');
    foreach ([C::STATUS_PAYEE, C::STATUS_PROGRAMMEE, C::STATUS_EN_COURS] as $to) {
        $c = SM::transition($c, $to);
    }
    expect(Refund::amount($c, Refund::CASE_REFUS))->toBe(0)
        ->and(Refund::amount($c, Refund::CASE_ANNULATION_VENDEUR))->toBe(0);
});
