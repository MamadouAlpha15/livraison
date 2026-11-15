<?php

use App\Models\Shop;
use App\Models\User;
use App\Support\ProductPage;
use App\Support\Storefront;
use Tests\Support\CampaignFixtures as F;

/*
 * Seuls les thèmes Express et Signature existent : choix à la création de la boutique,
 * ancienne apparence ignorée, anciennes fiches produit redirigées vers la page du thème.
 */

test('le vendeur choisit Signature à la création de sa boutique', function () {
    $owner = User::factory()->create(['role' => 'admin', 'country' => 'GN']);

    $this->actingAs($owner)->get(route('shop.create'))->assertOk()
        ->assertSee('Thème de la boutique')->assertSee('Express')->assertSee('Signature');

    $this->actingAs($owner)
        ->post(route('shop.store'), ['name' => 'Chez Mariama', 'phone' => '625 11 22 33', 'currency' => 'GNF', 'page_model' => 'signature'])
        ->assertRedirect(route('boutique.dashboard'));

    $shop = Shop::where('user_id', $owner->id)->firstOrFail();
    expect(Storefront::forShop($shop)['page_model'])->toBe('signature');
});

test('sans choix, la boutique démarre avec Express ; un thème inconnu est refusé', function () {
    $owner = User::factory()->create(['role' => 'admin', 'country' => 'GN']);

    $this->actingAs($owner)->post(route('shop.store'), ['name' => 'Chez Fodé', 'currency' => 'GNF', 'page_model' => 'luxe'])
        ->assertSessionHasErrors('page_model');
    expect(Shop::where('user_id', $owner->id)->exists())->toBeFalse();

    $this->actingAs($owner)->post(route('shop.store'), ['name' => 'Chez Fodé', 'currency' => 'GNF'])->assertRedirect();
    expect(Storefront::forShop(Shop::where('user_id', $owner->id)->firstOrFail())['page_model'])->toBe('express');
});

test('l\'ancienne apparence de la vitrine est ignorée', function () {
    $config = Storefront::normalize([
        'page_model' => 'prestige', // ancien modèle supprimé
        'template' => 'luxe', 'themes' => [['name' => 'Tabaski', 'style' => ['color' => '#15803D']]],
        'style' => ['font' => 'elegante', 'bg' => 'dark'], 'layout' => 'carousel', 'blocks' => [['type' => 'text']],
        'settings' => ['vacation' => true],
    ]);

    expect(array_keys($config))->toEqualCanonicalizing(['page_model', 'home', 'form', 'cover', 'logo', 'settings'])
        ->and($config['page_model'])->toBe('express')
        ->and($config['settings']['vacation'])->toBeTrue();
});

test('l\'ancienne fiche produit mène à la page produit du thème, avec le suivi des visites', function () {
    $shop = F::shop();
    $shop->update(['slug' => 'chez-fanta']);
    $product = F::product($shop, ['name' => 'Robe en wax']);

    $this->get(route('client.products.show', ['product' => $product, 'utm_source' => 'whatsapp']))
        ->assertStatus(301)
        ->assertRedirect(ProductPage::url($shop, $product) . '?utm_source=whatsapp');

    expect($product->fresh()->publicUrl())->toBe(url('/s/chez-fanta/p/' . $product->id . '-robe-en-wax'));
    $this->get($product->fresh()->publicUrl())->assertOk()->assertSee('Robe en wax');
});
