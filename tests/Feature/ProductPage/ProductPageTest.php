<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\PushService;
use App\Support\ProductPage;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    app()->instance(PushService::class, new class { public function sendToUser(...$a): void {} public function vendorBadgeCount(...$a): int { return 0; } public function notifyShopStaff(...$a): void {} });
    $this->shop = F::shop();
    $this->shop->update(['name' => 'Aïcha Parfums', 'currency' => 'GNF']);
    $this->vendor = $this->shop->owner;
    $this->vendor->forceFill(['shop_id' => $this->shop->id])->save();
    $this->product = F::product($this->shop, ['name' => 'Parfum Oud Royal 100 ml', 'price' => 250000, 'original_price' => 310000, 'stock' => 7, 'description' => 'Un parfum chaud et boisé.']);
});

function withOffers($test): void
{
    $test->product->forceFill(['page' => ['offers' => [
        ['key' => 'un', 'label' => '1 flacon', 'subtitle' => 'Parfum Oud Royal 100 ml', 'qty' => 1, 'price' => 250000, 'badge' => null],
        ['key' => 'deux', 'label' => '2 flacons', 'subtitle' => 'Un pour toi, un à offrir', 'qty' => 2, 'price' => 450000, 'badge' => 'Le plus choisi'],
    ], 'points' => ['Tient toute la journée'], 'urgency' => true, 'urgency_under' => 10]])->save();
}

function orderForm(array $over = []): array
{
    return $over + ['offer' => 'deux', 'name' => 'Aïssatou Bah', 'phone' => '622458137', 'commune' => 'Ratoma', 'quartier' => 'Kipé'];
}

/* ── Page client ── */

test('page produit par défaut : prix, promo, urgence réelle, formulaire, étapes et FAQ de départ', function () {
    $this->get(route('client.products.show', $this->product))->assertOk()
        ->assertSee('Parfum Oud Royal 100 ml')
        ->assertSee("250\u{00A0}000\u{00A0}GNF", false)->assertSee('-19 %')->assertSee("-60\u{00A0}000\u{00A0}GNF", false)
        ->assertSee('Plus que 7 en stock')
        ->assertSee('Commande maintenant')->assertSee('Commander · Payer à la livraison')->assertSee('Faire une offre au vendeur')
        ->assertSee('Comment ça se passe')->assertSee('Dois-je payer avant la livraison ?')
        ->assertSee('Un parfum chaud et boisé.')
        ->assertSee('Les avis vérifiés des clients livrés apparaîtront ici.')
        ->assertDontSee('Choisis ton offre'); // une seule offre : pas de choix
});

test('offres du vendeur : l\'offre avec un badge est cochée, l\'économie est calculée', function () {
    withOffers($this);
    $this->get(route('client.products.show', $this->product))->assertOk()
        ->assertSee('Choisis ton offre')->assertSee('2 flacons')->assertSee('Le plus choisi')
        ->assertSee("Tu économises 50\u{00A0}000", false)
        ->assertSee('value="deux"', false)
        ->assertSee('Tient toute la journée');
});

test('réglages de la boutique : bandeau, couleur, badges, étapes, FAQ', function () {
    $this->shop->forceFill(['product_page' => ['banner' => 'Livraison partout à Conakry', 'accent' => '#1F7CF6', 'badges' => ['cod'],
        'steps' => ['Tu commandes'], 'faq' => [['q' => 'Livrez-vous à Kindia ?', 'a' => 'Oui, en 3 jours.']], 'show_offer_button' => false]])->save();

    $this->get(route('client.products.show', $this->product))->assertOk()
        ->assertSee('Livraison partout à Conakry')->assertSee('--accent: #1F7CF6', false)->assertSee('--on-accent: #FFFFFF', false)
        ->assertSee('Paiement à la livraison')->assertDontSee('Boutique vérifiée</span>', false)
        ->assertSee('Tu commandes')->assertSee('Livrez-vous à Kindia ?')
        ->assertDontSee('Faire une offre au vendeur');
});

test('avis réels affichés : laissés sur des commandes contenant ce produit', function () {
    $client = User::factory()->create(['role' => 'client', 'name' => 'Mariama Sow']);
    $order = Order::forceCreate(['user_id' => $client->id, 'shop_id' => $this->shop->id, 'total' => 250000, 'status' => Order::STATUS_LIVREE]);
    $order->items()->create(['product_id' => $this->product->id, 'quantity' => 1, 'price' => 250000]);
    Review::forceCreate(['order_id' => $order->id, 'user_id' => $client->id, 'rating' => 5, 'comment' => 'Odeur magnifique !']);

    $this->get(route('client.products.show', $this->product))->assertSee('Odeur magnifique !')->assertSee('Mariama')->assertSee('★ 5,0');
});

test('produit en rupture : pas de formulaire, alerte de retour en stock', function () {
    $this->product->update(['stock' => 0]);
    $this->get(route('client.products.show', $this->product))->assertOk()
        ->assertSee('Bientôt de retour')->assertDontSee('Commander · Payer à la livraison');
});

/* ── Commande ── */

test('commande d\'une offre : prix lu côté serveur, livraison de la commune, stock réduit', function () {
    withOffers($this);
    $this->shop->forceFill(['product_page' => ['delivery_fees' => ['Ratoma' => 20000]]])->save();

    $res = $this->postJson(route('client.products.order', $this->product), orderForm(['price' => 1]))->assertCreated();
    expect($res->json('total'))->toBe(470000);

    $order = Order::with('items', 'payment')->sole();
    expect((int) $order->total)->toBe(450000)
        ->and((int) $order->delivery_fee)->toBe(20000)
        ->and($order->delivery_destination)->toBe('Ratoma — Kipé')
        ->and($order->client_name)->toBe('Aïssatou Bah')
        ->and($order->items->sole()->quantity)->toBe(2)
        ->and((float) $order->items->sole()->price)->toBe(225000.0)
        ->and($order->items->sole()->variant_name)->toBe('2 flacons')
        ->and((int) $order->payment->amount)->toBe(450000)
        ->and($this->product->fresh()->stock)->toBe(5);
});

test('commande refusée : offre inconnue, stock insuffisant, champs manquants', function () {
    withOffers($this);
    $this->postJson(route('client.products.order', $this->product), orderForm(['offer' => 'trois']))->assertStatus(422)->assertJsonPath('message', 'Cette offre n\'existe plus : recharge la page.');

    $this->product->update(['stock' => 1]);
    $this->postJson(route('client.products.order', $this->product), orderForm())->assertStatus(422)->assertJsonPath('message', 'Il ne reste que 1 en stock : choisis une offre plus petite.');

    $this->postJson(route('client.products.order', $this->product), ['offer' => 'un'])->assertStatus(422)->assertJsonValidationErrors(['name', 'phone', 'commune']);
    expect(Order::count())->toBe(0);
});

test('sans offre définie : commande à l\'unité au prix actuel', function () {
    $this->postJson(route('client.products.order', $this->product), orderForm(['offer' => 'un', 'commune' => 'Kaloum']))->assertCreated()
        ->assertJsonPath('total', 250000);
    expect((int) Order::sole()->total)->toBe(250000)->and(Order::sole()->delivery_fee)->toBeNull();
});

/* ── Personnalisation par le vendeur ── */

test('le vendeur règle les pages de sa boutique : couleur, frais par commune, badges', function () {
    $this->actingAs($this->vendor)->get(route('boutique.product-page.edit'))->assertOk()->assertSee('Frais de livraison par commune');
    $this->actingAs($this->vendor)->put(route('boutique.product-page.update'), [
        'banner' => 'Livraison partout à Conakry', 'accent' => '#F59E0B', 'badges' => ['cod', 'verified'],
        'steps' => ['Tu commandes', '', 'Tu paies à la réception'], 'delivery_fees' => ['Ratoma' => 20000, 'Kaloum' => '', 'Inconnue' => 5],
        'faq' => [['q' => 'Livrez-vous le dimanche ?', 'a' => 'Non.']], 'show_offer_button' => '1',
        'template' => 'confiance', 'guarantee' => 'Échange sous 3 jours', 'whatsapp' => '1',
    ])->assertSessionHasNoErrors();

    $page = ProductPage::shop($this->shop->fresh());
    expect($page['accent'])->toBe('#F59E0B')->and($page['delivery_fees'])->toBe(['Ratoma' => 20000])
        ->and($page['steps'])->toBe(['Tu commandes', 'Tu paies à la réception'])->and($page['badges'])->toBe(['cod', 'verified'])
        ->and($page['template'])->toBe('confiance')->and($page['guarantee'])->toBe('Échange sous 3 jours');

    $this->actingAs($this->vendor)->put(route('boutique.product-page.update'), ['accent' => '#1F7CF6', 'template' => 'inconnu'])
        ->assertSessionHasErrors('template');
});

/* ── Modèles de page ── */

/** Position d'un texte dans la page (pour vérifier l'ordre des sections) */
function posIn(string $html, string $needle): int
{
    $p = strpos($html, $needle);
    expect($p)->not->toBeFalse("« {$needle} » absent de la page");
    return $p;
}

test('les 5 modèles s\'affichent, chacun avec son ordre de sections', function () {
    withOffers($this);
    $html = fn ($tpl) => $this->get(route('client.products.show', ['product' => $this->product, 'modele' => $tpl]))->assertOk()->assertSee('pp--' . $tpl, false)->getContent();

    // Classique : offres → commande → pourquoi
    $h = $html('classique');
    expect(posIn($h, 'Choisis ton offre'))->toBeLessThan(posIn($h, 'Commande maintenant'))
        ->and(posIn($h, 'Commande maintenant'))->toBeLessThan(posIn($h, 'Pourquoi tu vas l\'adorer'));

    // Express : la commande arrive avant tout le reste, sans étapes
    $h = $html('express');
    expect(posIn($h, 'Commande maintenant'))->toBeLessThan(posIn($h, 'Pourquoi tu vas l\'adorer'))
        ->and(str_contains($h, 'Comment ça se passe'))->toBeFalse();

    // Confiance : avis avant la commande
    $h = $html('confiance');
    expect(posIn($h, 'Avis clients'))->toBeLessThan(posIn($h, 'Commande maintenant'));

    // Promo : économie en grand
    $h = $html('promo');
    expect(posIn($h, 'Tu économises'))->toBeLessThan(posIn($h, 'Commande maintenant'))->and($h)->toContain('sur le prix habituel');

    // Histoire : le texte et les étapes avant la commande
    $h = $html('histoire');
    expect(posIn($h, 'Pourquoi tu vas l\'adorer'))->toBeLessThan(posIn($h, 'Comment ça se passe'))
        ->and(posIn($h, 'Comment ça se passe'))->toBeLessThan(posIn($h, 'Commande maintenant'));
});

test('modèle Promo : compte à rebours seulement pendant une vraie vente flash', function () {
    $this->freezeTime();
    $this->shop->forceFill(['product_page' => ['template' => 'promo']])->save();
    $this->get(route('client.products.show', $this->product))->assertOk()->assertDontSee('Vente flash : se termine dans');

    $this->product->update(['flash_price' => 200000, 'flash_starts_at' => now()->subHour(), 'flash_ends_at' => now()->addHours(5)]);
    $this->get(route('client.products.show', $this->product))->assertOk()
        ->assertSee('Vente flash : se termine dans')->assertSee('data-ends="' . now()->addHours(5)->timestamp . '"', false);
});

test('modèle propre au produit, garantie et WhatsApp de la boutique', function () {
    $this->shop->update(['phone' => '622458137']);
    $this->shop->forceFill(['product_page' => ['template' => 'classique', 'guarantee' => 'Échange sous 3 jours', 'whatsapp' => true]])->save();
    $this->product->forceFill(['page' => ['template' => 'confiance']])->save();

    $this->get(route('client.products.show', $this->product))->assertOk()
        ->assertSee('pp--confiance', false)
        ->assertSee('Notre engagement')->assertSee('Échange sous 3 jours')
        ->assertSee('https://wa.me/224622458137', false);

    // Le vendeur choisit le modèle d'un produit
    $this->product->forceFill(['page' => ['template' => 'express']])->save();
    expect(ProductPage::template($this->shop->fresh(), $this->product->fresh()))->toBe('express');
});

test('un client n\'a pas accès aux réglages des pages produit', function () {
    $this->actingAs(User::factory()->create(['role' => 'client']))->get(route('boutique.product-page.edit'))->assertForbidden();
});

test('texte du bouton lisible sur la couleur choisie', function () {
    expect(ProductPage::onAccent('#C8F135'))->toBe('#16162A')
        ->and(ProductPage::onAccent('#1F7CF6'))->toBe('#FFFFFF')
        ->and(ProductPage::onAccent('#16162A'))->toBe('#FFFFFF')
        // Sur la carte bleu nuit, le bleu Shopio est éclairci pour rester lisible ; une couleur claire reste telle quelle
        ->and(ProductPage::accentOnNight('#1F7CF6'))->not->toBe('#1F7CF6')
        ->and(ProductPage::accentOnNight('#C8F135'))->toBe('#C8F135')
        ->and(ProductPage::shopDefaults()['accent'])->toBe('#1F7CF6');
});
