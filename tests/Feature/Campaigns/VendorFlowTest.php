<?php

use App\Models\AdCampaign as C;
use App\Models\AdCampaignPayment as Pay;
use App\Models\ShopCreditEntry;
use App\Models\User;
use App\Support\Campaigns\ShopCredit;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    // Paiement simulé (aucun appel réseau) et signature de webhook connue
    config(['chapchappay.api_key' => '', 'chapchappay.hmac_key' => 'cle-de-test']);
});

function boostForm(array $products, array $over = []): array
{
    return $over + [
        'package' => 'boost_produit', 'days' => 7, 'starts_on' => today()->toDateString(),
        'scope' => 'products', 'products' => $products, 'payment' => 'credit', 'accept_rules' => '1',
    ];
}

test('le propriétaire voit ses pages Campagnes', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $this->actingAs($shop->owner)->get(route('boutique.campaigns.index'))->assertOk()->assertSee('Mes campagnes');
    $this->actingAs($shop->owner)->get(route('boutique.campaigns.create', ['produit' => $product->id]))->assertOk()->assertSee($product->name);
});

test('un invité ou un client n\'y a pas accès', function () {
    $this->get(route('boutique.campaigns.index'))->assertRedirect(route('login'));
    $client = User::factory()->create(['role' => 'client']);
    $this->actingAs($client)->get(route('boutique.campaigns.index'))->assertForbidden();
});

test('une boutique ne voit pas les campagnes d\'une autre', function () {
    $mine = F::shop();
    $other = F::shop();
    $campaign = F::campaign($other, 'boutique_une');

    $this->actingAs($mine->owner)->get(route('boutique.campaigns.show', $campaign))->assertNotFound();
    $this->actingAs($mine->owner)->post(route('boutique.campaigns.cancel', $campaign))->assertNotFound();
    expect($campaign->fresh()->status)->toBe(C::STATUS_A_PAYER);
});

test('le prix est calculé par le serveur, pas pris du formulaire', function () {
    $shop = F::shop();
    ShopCredit::credit($shop, 500000, ShopCreditEntry::REASON_REMBOURSEMENT);
    $product = F::product($shop);

    $this->actingAs($shop->owner)
        ->post(route('boutique.campaigns.store'), boostForm([$product->id], ['price_gnf' => 1, 'price' => 1, 'amount' => 1]))
        ->assertRedirect();

    $campaign = C::latest('id')->first();
    expect($campaign->price_gnf)->toBe(50000)
        ->and(ShopCredit::balance($shop))->toBe(450000)
        ->and($campaign->payments()->first()->amount_gnf)->toBe(50000);
});

test('payée avec le Crédit Shopio aujourd\'hui : la mise en avant démarre tout de suite', function () {
    $shop = F::shop();
    ShopCredit::credit($shop, 100000, ShopCreditEntry::REASON_REMBOURSEMENT);
    $product = F::product($shop);

    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), boostForm([$product->id]))
        ->assertSessionHas('success');

    $c = C::latest('id')->first();
    expect($c->status)->toBe(C::STATUS_EN_COURS)
        ->and($c->payments()->first()->method)->toBe(Pay::METHOD_CREDIT)
        ->and($c->items()->pluck('product_id')->all())->toBe([$product->id]);
});

test('une mise en avant qui commence plus tard reste programmée', function () {
    $shop = F::shop();
    ShopCredit::credit($shop, 100000, ShopCreditEntry::REASON_REMBOURSEMENT);
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), boostForm([F::product($shop)->id], ['starts_on' => today()->addDays(3)->toDateString()]));

    expect(C::latest('id')->first()->status)->toBe(C::STATUS_PROGRAMMEE);
});

test('crédit insuffisant : la campagne reste à payer, avec un message', function () {
    $shop = F::shop();
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), boostForm([F::product($shop)->id]))
        ->assertSessionHas('error');

    expect(C::latest('id')->first()->status)->toBe(C::STATUS_A_PAYER)
        ->and(ShopCredit::balance($shop))->toBe(0);
});

test('les règles refusent la commande et le vendeur voit pourquoi', function () {
    $shop = F::shop();
    $other = F::shop();
    $this->actingAs($shop->owner)
        ->post(route('boutique.campaigns.store'), boostForm([F::product($other)->id]))
        ->assertSessionHasErrors('campaign');
    expect(C::count())->toBe(0);

    $this->actingAs($shop->owner)
        ->post(route('boutique.campaigns.store'), boostForm([F::product($shop)->id], ['accept_rules' => null]))
        ->assertSessionHasErrors('accept_rules');
});

test('Mobile Money : page de paiement, paiement réussi, campagne lancée', function () {
    $shop = F::shop();
    $product = F::product($shop);

    $res = $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), boostForm([$product->id], ['payment' => 'mobile_money']));
    $payment = Pay::latest('id')->first();
    $res->assertRedirect(route('boutique.campaigns.fake-pay', $payment));
    expect($payment->status)->toBe(Pay::STATUS_EN_ATTENTE)->and($payment->amount_gnf)->toBe(50000);

    $this->actingAs($shop->owner)
        ->post(route('boutique.campaigns.fake-pay.confirm', $payment), ['result' => 'paye', 'operator' => 'mtn_momo'])
        ->assertRedirect(route('boutique.campaigns.payment.return', $payment));

    expect($payment->fresh()->status)->toBe(Pay::STATUS_PAYE)
        ->and($payment->fresh()->operator)->toBe('mtn_momo')
        ->and($payment->campaign->fresh()->status)->toBe(C::STATUS_EN_COURS);
});

test('webhook ChapChap : signature vérifiée, paiement confirmé une seule fois', function () {
    $shop = F::shop();
    $campaign = F::campaign($shop, 'boutique_une');
    $payment = $campaign->payments()->create(['method' => 'mobile_money', 'amount_gnf' => 150000, 'status' => 'en_attente', 'gateway_operation_id' => 'OP-1']);
    $body = json_encode(['operation_id' => 'OP-1', 'order_id' => 'CAMP-' . $payment->id, 'status' => ['code' => 'success'], 'transaction' => ['payment_method' => 'Orange Money']]);

    $this->call('POST', route('payment.campaign.callback'), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_CCP_HMAC_SIGNATURE' => 'fausse'], $body)
        ->assertStatus(401);
    expect($payment->fresh()->status)->toBe(Pay::STATUS_EN_ATTENTE);

    $sig = hash_hmac('sha256', $body, 'cle-de-test');
    foreach ([1, 2] as $_) {   // ChapChap peut renvoyer le même webhook
        $this->call('POST', route('payment.campaign.callback'), [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_CCP_HMAC_SIGNATURE' => $sig], $body)
            ->assertOk();
    }

    expect($payment->fresh()->status)->toBe(Pay::STATUS_PAYE)
        ->and($payment->fresh()->operator)->toBe('orange_money')
        ->and($campaign->fresh()->status)->toBe(C::STATUS_EN_COURS)
        ->and(Pay::where('ad_campaign_id', $campaign->id)->count())->toBe(1);
});

test('paiement reçu après une annulation : la somme part sur le Crédit Shopio', function () {
    $shop = F::shop();
    $campaign = F::campaign($shop, 'boutique_une');
    $payment = $campaign->payments()->create(['method' => 'mobile_money', 'amount_gnf' => 150000, 'status' => 'en_attente', 'gateway_operation_id' => 'OP-2']);
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.cancel', $campaign));
    $payment->update(['status' => 'en_attente']); // le vendeur a payé juste avant d'annuler

    app(\App\Support\Campaigns\CampaignCheckout::class)->confirm($payment->fresh());

    expect($campaign->fresh()->status)->toBe(C::STATUS_ANNULEE)
        ->and(ShopCredit::balance($shop))->toBe(150000)
        ->and($payment->fresh()->status)->toBe(Pay::STATUS_REMBOURSE);
});

test('annulation d\'une mise en avant programmée : remboursement intégral sur le Crédit Shopio', function () {
    $shop = F::shop();
    ShopCredit::credit($shop, 150000, ShopCreditEntry::REASON_REMBOURSEMENT);
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'),
        ['package' => 'boutique_une', 'days' => 7, 'starts_on' => today()->addDays(5)->toDateString(), 'scope' => 'shop', 'payment' => 'credit', 'accept_rules' => '1']);
    $campaign = C::latest('id')->first();
    expect(ShopCredit::balance($shop))->toBe(0);

    $this->actingAs($shop->owner)->post(route('boutique.campaigns.cancel', $campaign))->assertSessionHas('success');

    expect($campaign->fresh()->status)->toBe(C::STATUS_ANNULEE)
        ->and(ShopCredit::balance($shop))->toBe(150000);
});

test('une campagne lancée ne peut plus être annulée', function () {
    $shop = F::shop();
    $campaign = F::campaign($shop, 'boutique_une', [], ['status' => C::STATUS_EN_COURS, 'paid_at' => now(), 'launched_at' => now()]);
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.cancel', $campaign))->assertSessionHas('error');
    expect($campaign->fresh()->status)->toBe(C::STATUS_EN_COURS);
});

test('pause et reprise par le vendeur', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $campaign = F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS, 'paid_at' => now(), 'launched_at' => now()]);

    $this->actingAs($shop->owner)->post(route('boutique.campaigns.pause', $campaign))->assertSessionHas('success');
    expect($campaign->fresh()->status)->toBe(C::STATUS_EN_PAUSE)->and($campaign->fresh()->pause_reason)->toBe('vendeur');

    $this->actingAs($shop->owner)->post(route('boutique.campaigns.resume', $campaign))->assertSessionHas('success');
    expect($campaign->fresh()->status)->toBe(C::STATUS_EN_COURS);
});

test('une pause pour rupture ne se lève pas à la main', function () {
    $shop = F::shop();
    $campaign = F::campaign($shop, 'boost_produit', [F::product($shop)->id], ['status' => C::STATUS_EN_PAUSE, 'pause_reason' => 'rupture', 'paused_at' => now()]);
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.resume', $campaign))->assertSessionHas('error');
    expect($campaign->fresh()->status)->toBe(C::STATUS_EN_PAUSE);
});

test('prix et places à l\'étape « Formule »', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $this->actingAs($shop->owner)
        ->postJson(route('boutique.campaigns.availability'), ['package' => 'boost_produit', 'days' => 30, 'starts_on' => today()->toDateString(), 'products' => [$product->id]])
        ->assertOk()->assertJson(['ok' => true, 'price_gnf' => 150000, 'full' => null]);
});
