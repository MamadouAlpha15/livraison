<?php

use App\Models\MarketingCampaign;
use App\Models\Shop;
use App\Models\ShopTraffic;
use App\Models\User;
use App\Support\Storefront;
use Tests\Support\CampaignFixtures as F;

/* « Modifier la page » directement sur la vitrine (/s/{slug}?edition=1), anciennes adresses de la page
   « Boutique », onglets Partage et Qualité de Marketing. */

beforeEach(function () {
    $this->shop = F::shop();
    $this->shop->forceFill(['slug' => 'chez-fanta', 'currency' => 'GNF', 'phone' => '+224622123456'])->save();
    $this->owner = $this->shop->owner;
    $this->owner->forceFill(['shop_id' => $this->shop->id, 'role_in_shop' => 'admin'])->save();
    $this->url = route('public.shops.slug', 'chez-fanta');
    F::product($this->shop, ['name' => 'Sac en wax']);
});

/** Sections Express de la version en ligne, avec une section « Texte » ajoutée. */
function leHome(Shop $shop, string $title): array
{
    $home = Storefront::forShop($shop)['home']['express'];
    $home[] = ['id' => 'sabcdef12', 'type' => 'text', 'on' => true, 'sp' => [0, 0], 'd' => ['title' => $title, 'text' => 'Notre histoire.', 'align' => 'left']];
    return $home;
}
function leIdentity(array $over = []): array
{
    return $over + ['name' => 'Chez Fanta', 'slogan' => 'Le wax de Conakry', 'color' => '#1F7CF6', 'logo' => 'initials', 'share_src' => 'default', 'share_img' => null];
}

test('un visiteur, un client et un employé ne voient jamais le mode édition, même avec ?edition=1', function () {
    $this->get($this->url . '?edition=1')->assertOk()->assertDontSee('Modifier la page')->assertDontSee('data-sx=', false);
    $client = User::factory()->create(['role' => 'client']);
    $this->actingAs($client)->get($this->url . '?edition=1&cadre=1')->assertOk()->assertDontSee('data-sx=', false)->assertDontSee('sxe-bar', false);

    $employee = User::factory()->create(['role' => 'employe', 'shop_id' => $this->shop->id]);
    $this->actingAs($employee)->get($this->url . '?edition=1')->assertOk()->assertDontSee('Modifier la page');
    $this->actingAs($employee)->get($this->url . '?edition=1&cadre=1')->assertOk()->assertDontSee('data-sx=', false);
    // Un compte admin rattaché à la boutique mais qui n'en est pas le propriétaire : refusé aussi
    $coAdmin = User::factory()->create(['role' => 'admin', 'shop_id' => $this->shop->id]);
    $this->actingAs($coAdmin)->get($this->url . '?edition=1')->assertOk()->assertDontSee('Modifier la page');
    $this->actingAs($coAdmin)->putJson(route('boutique.storefront.edit.draft'), ['home' => ['express' => leHome($this->shop, 'X')]])->assertForbidden();
});

test('le propriétaire ouvre l’éditeur : barre, vitrine dans un cadre avec ses sections modifiables', function () {
    $this->actingAs($this->owner)->get($this->url . '?edition=1')->assertOk()
        ->assertSee('Modifier la page')->assertSee('Enregistrer le brouillon')->assertSee('Partager un aperçu')->assertSee('Publier')
        ->assertSee('edition=1&amp;cadre=1', false);
    $this->actingAs($this->owner)->get($this->url . '?edition=1&cadre=1')->assertOk()
        ->assertSee('data-sx="hero"', false)->assertSee('data-sx="header"', false)->assertSee('Ajouter une section');
    // Barre « Thèmes Shopio » : « Modifier la page » remplace « Boutique »
    $this->actingAs($this->owner)->get($this->url)->assertOk()->assertSee('Modifier la page')->assertDontSee('vitrine/grandir');
});

test('brouillon : enregistré pour le propriétaire, jamais montré aux visiteurs avant « Publier »', function () {
    $this->actingAs($this->owner)->putJson(route('boutique.storefront.edit.draft'), ['home' => ['express' => leHome($this->shop, 'Notre atelier à Madina')]])
        ->assertOk();
    expect(collect($this->shop->fresh()->storefront_draft['config']['home']['express'])->firstWhere('id', 'sabcdef12')['d']['title'])->toBe('Notre atelier à Madina');

    // Visiteur : toujours la version en ligne
    auth()->logout();
    $this->get($this->url)->assertOk()->assertDontSee('Notre atelier à Madina');
    // Le cadre de l'éditeur et le lien d'aperçu montrent le brouillon
    $this->actingAs($this->owner)->get($this->url . '?edition=1&cadre=1')->assertSee('Notre atelier à Madina');
    $preview = $this->actingAs($this->owner)->postJson(route('boutique.storefront.preview'))->assertOk()->json('url');
    auth()->logout();
    $this->get($preview)->assertOk()->assertSee('Notre atelier à Madina');

    // Publier : les visiteurs le voient
    $this->actingAs($this->owner)->postJson(route('boutique.storefront.edit.publish'), ['home' => ['express' => leHome($this->shop, 'Notre atelier à Madina')]])->assertOk();
    expect($this->shop->fresh()->storefront_draft)->toBeNull();
    auth()->logout();
    $this->get($this->url)->assertOk()->assertSee('Notre atelier à Madina');
});

test('rendu en direct : la version en cours de saisie s’affiche dans le cadre sans rien enregistrer', function () {
    $j = $this->actingAs($this->owner)->postJson(route('boutique.storefront.edit.render'), ['theme' => 'express', 'home' => ['express' => leHome($this->shop, 'Titre en cours de saisie')]])
        ->assertOk()->json();
    expect($j['url'])->toContain('cadre=1')->toContain('rendu=');
    $this->actingAs($this->owner)->get($j['url'])->assertOk()->assertSee('Titre en cours de saisie');
    expect($this->shop->fresh()->storefront_draft)->toBeNull();
});

test('en-tête : nom, slogan, couleur et logo enregistrés en brouillon puis publiés', function () {
    $this->actingAs($this->owner)->putJson(route('boutique.storefront.edit.draft'), ['identity' => leIdentity(['name' => 'Maison Fanta', 'color' => '#15803D'])])->assertOk();
    $shop = $this->shop->fresh();
    expect($shop->name)->not->toBe('Maison Fanta')->and($shop->storefront_draft['extra']['name'])->toBe('Maison Fanta');

    $this->actingAs($this->owner)->postJson(route('boutique.storefront.edit.publish'), ['identity' => leIdentity(['name' => 'Maison Fanta', 'color' => '#15803D'])])->assertOk();
    $shop = $shop->fresh();
    expect($shop->name)->toBe('Maison Fanta')->and($shop->accent_color)->toBe('#15803D')
        ->and(Storefront::forShop($shop)['cover']['slogan'])->toBe('Le wax de Conakry');

    // Règles reprises de l'ancienne page : nom trop court, couleur inconnue, logo qui n'a pas été importé ici
    $this->actingAs($this->owner)->putJson(route('boutique.storefront.edit.draft'), ['identity' => leIdentity(['name' => 'A'])])->assertStatus(422);
    $this->actingAs($this->owner)->putJson(route('boutique.storefront.edit.draft'), ['identity' => leIdentity(['color' => '#123456'])])->assertStatus(422);
    $this->actingAs($this->owner)->putJson(route('boutique.storefront.edit.draft'), ['identity' => leIdentity(['logo' => 'shops/medium/autre.webp'])])->assertStatus(422);
});

test('réglages de la page : preuves de confiance et mode WhatsApp (numéro obligatoire)', function () {
    $this->actingAs($this->owner)->putJson(route('boutique.storefront.edit.draft'), ['settings' => ['wa_mode' => true, 'proof_delivered' => false, 'proof_response' => true]])->assertOk();
    expect($this->shop->fresh()->storefront_draft['config']['settings'])->toMatchArray(['wa_mode' => true, 'proof_delivered' => false]);
    $this->shop->forceFill(['phone' => null])->save();
    $this->owner->unsetRelation('shop');
    $this->actingAs($this->owner)->putJson(route('boutique.storefront.edit.draft'), ['settings' => ['wa_mode' => true]])->assertStatus(422)->assertJsonValidationErrors('wa_mode');
});

test('couleurs par thème : vides par défaut, appliquées à la vitrine après publication, couleur invalide refusée', function () {
    auth()->logout();
    $this->get($this->url)->assertOk()->assertDontSee('--forest: #B42318', false);
    $colors = ['signature' => ['accent' => '#B42318', 'bg' => '#FFFFFF', 'ink' => null], 'express' => ['accent' => null, 'bg' => null, 'ink' => null]];
    $this->actingAs($this->owner)->putJson(route('boutique.storefront.edit.draft'), ['colors' => ['signature' => ['accent' => 'rouge']]])->assertStatus(422);
    $this->actingAs($this->owner)->postJson(route('boutique.storefront.edit.publish'), ['colors' => $colors])->assertOk();
    expect(Storefront::forShop($this->shop->fresh())['colors']['signature'])->toBe(['accent' => '#B42318', 'bg' => '#FFFFFF', 'ink' => null]);
    auth()->logout();
    // Signature reçoit ses couleurs ; Express garde les siennes (rien de choisi pour lui)
    $this->get($this->url . '?modele=signature')->assertOk();
    $this->actingAs($this->owner)->get($this->url . '?modele=signature')->assertSee('--forest: #B42318', false)->assertSee('--ivory: #FFFFFF', false);
    $this->actingAs($this->owner)->get($this->url . '?modele=express')->assertDontSee('--acc: #B42318', false);
});

test('sélecteur de page : catalogue et panier dans le cadre, produits vers leur page de vente', function () {
    $this->actingAs($this->owner)->get($this->url . '?edition=1')->assertOk()
        ->assertSee('Rechercher une page ou un produit')->assertSee('Sac en wax')->assertSee('page-de-vente', false)
        ->assertSee('Couleurs')->assertSee('Voir');
    $j = $this->actingAs($this->owner)->postJson(route('boutique.storefront.edit.render'), ['theme' => 'express', 'page' => 'catalogue'])->assertOk()->json();
    expect($j['url'])->toContain('vue=produits');
    $this->actingAs($this->owner)->get($j['url'])->assertOk()->assertSee('Tous nos produits');
    expect($this->actingAs($this->owner)->postJson(route('boutique.storefront.edit.render'), ['page' => 'panier'])->json('url'))->toContain('panier=1');
});

test('page d’un produit dans le même écran : l’éditeur de la page de vente s’ouvre en mode intégré', function () {
    $product = $this->shop->products()->firstOrFail();
    $editor = $this->actingAs($this->owner)->get($this->url . '?edition=1')->assertOk()->getContent();
    // Le sélecteur ouvre la page de vente intégrée ; « Voir » mène à la vraie page du produit
    expect($editor)->toContain(str_replace('/', '\/', route('products.page.edit', $product) . '?integre=1'))
        ->toContain(str_replace('/', '\/', \App\Support\ProductPage::url($this->shop, $product)));
    $this->actingAs($this->owner)->get(route('products.page.edit', $product) . '?integre=1')->assertOk()->assertSee('pe is-embedded', false)->assertSee('window.PE', false);
    $this->actingAs($this->owner)->get(route('products.page.edit', $product))->assertOk()->assertDontSee('pe is-embedded', false);
    // Le produit d'une autre boutique reste refusé, même intégré
    $other = F::product(F::shop());
    $this->actingAs($this->owner)->get(route('products.page.edit', $other) . '?integre=1')->assertForbidden();
    // Lien direct …?edition=1&produit=ID : l'éditeur s'ouvre sur ce produit (jamais sur celui d'une autre boutique)
    $this->actingAs($this->owner)->get($this->url . '?edition=1&produit=' . $product->id)->assertOk()->assertSee('"openProduct":' . $product->id, false);
    $this->actingAs($this->owner)->get($this->url . '?edition=1&produit=' . $other->id)->assertOk()->assertSee('"openProduct":null', false);
});

test('chaque ancienne adresse de la page « Boutique » mène à la bonne nouvelle place', function () {
    $this->actingAs($this->owner);
    $this->get('/boutique/vitrine/grandir')->assertRedirect(route('boutique.storefront.live', ['ouvrir' => 'entete']));
    $this->get('/boutique/vitrine/grandir?onglet=couverture')->assertRedirect(route('boutique.storefront.live', ['ouvrir' => 'entete']));
    $this->get('/boutique/vitrine/grandir?onglet=conversion')->assertRedirect(route('boutique.storefront.live', ['ouvrir' => 'reglages']));
    $this->get('/boutique/vitrine/grandir?onglet=partage')->assertRedirect(route('boutique.marketing', ['onglet' => 'partage']));
    $this->get('/boutique/vitrine/grandir?onglet=qualite')->assertRedirect(route('boutique.marketing', ['onglet' => 'qualite']));
    $this->get(route('boutique.storefront.live', ['ouvrir' => 'entete']))->assertRedirect($this->url . '?edition=1&ouvrir=entete');
    // L'éditeur s'ouvre sur l'en-tête
    $this->get($this->url . '?edition=1&ouvrir=entete')->assertOk()->assertSee('"open":"entete"', false);
});

test('Marketing : onglets Partage et Qualité de la vitrine, avec des chiffres réels', function () {
    $this->actingAs($this->owner);
    $this->get(route('boutique.marketing', ['onglet' => 'partage']))->assertOk()
        ->assertSee('Lien d’un produit')->assertSee('QR code et affiche')->assertSee('Liens de suivi par réseau')->assertSee('Lien de campagne avec remise');
    $this->get(route('boutique.marketing', ['onglet' => 'qualite']))->assertOk()
        ->assertSee('Score de vitrine')->assertSee('Classement des catégories')->assertSee('Avis reçus sur l’aperçu');
    $this->get(route('boutique.marketing'))->assertOk()->assertSee('D\'où viennent vos clients', false);
    $this->get(route('boutique.marketing.poster'))->assertOk()->assertSee($this->shop->name);
});

test('lien de campagne : créé dans Marketing, et un lien déjà partagé compte toujours ses visites', function () {
    $this->shop->forceFill(['plan' => 'pro', 'plan_expires_at' => now()->addMonth()])->save();
    $this->owner->unsetRelation('shop');
    $this->actingAs($this->owner)->post(route('boutique.marketing.promo-link'), ['discount' => 15, 'ends' => now('Africa/Conakry')->addDays(5)->toDateString()])
        ->assertRedirect(route('boutique.marketing', ['onglet' => 'partage']));
    $campaign = MarketingCampaign::where('shop_id', $this->shop->id)->sole();
    expect($campaign->promoCode->value)->toEqual(15);

    // Un visiteur arrive par le lien de la campagne (adresse inchangée) : la visite est comptée pour elle
    auth()->logout();
    $this->get($this->url . '?utm_source=whatsapp&utm_medium=social&utm_campaign=' . $campaign->slug)->assertOk();
    expect((int) ShopTraffic::where('shop_id', $this->shop->id)->where('campaign_id', $campaign->id)->sum('visits'))->toBe(1);

    $this->actingAs($this->owner)->post(route('boutique.marketing.promo-link.stop', $campaign))->assertRedirect();
    expect($campaign->fresh()->archived_at)->not->toBeNull()->and($campaign->promoCode->fresh()->is_active)->toBeFalse();
});
