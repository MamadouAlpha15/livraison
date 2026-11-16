<?php

use Tests\Support\CampaignFixtures as F;

/* Page « Catalogue produits » (/products), design de la maquette Espace vendeur : chiffres, grille, interrupteur. */

beforeEach(function () {
    $this->shop = F::shop();
    $this->shop->update(['slug' => 'chez-fanta', 'currency' => 'GNF']);
    $this->owner = $this->shop->owner;
    $this->owner->forceFill(['shop_id' => $this->shop->id, 'role_in_shop' => 'admin'])->save();
});

test('la grille affiche les produits et des chiffres calculés', function () {
    F::product($this->shop, ['name' => "Crème L'Oréal", 'stock' => 3, 'price' => 45000, 'gallery' => json_encode(['products/a.webp', 'products/b.webp'])]);
    F::product($this->shop, ['name' => 'Sac en wax', 'stock' => 0]);
    F::product($this->shop, ['name' => 'Robe brodée', 'stock' => 12, 'is_active' => false]);

    $html = $this->actingAs($this->owner)->get(route('products.index'))->assertOk()
        ->assertSee('Produits')
        ->assertSee("Crème L'Oréal")
        ->assertSee('45 000 GNF')
        ->assertSee('Plus que 3')
        ->assertSee('Rupture de stock')
        ->assertSee('12 en stock')
        ->assertSee('products/a.webp', false) // sans photo principale : la première photo de la galerie
        ->assertSee('1–3 sur 3')
        ->getContent();

    // Tous 3 · Actifs 2 · Inactifs 1 · Rupture 1 · Stock faible 1 ; une seule étiquette « Inactif » visible
    expect($html)->toMatch('/Tous <span class="num"\s*>3</')
        ->and($html)->toMatch('/id="kpiActive"\s*>2</')
        ->and($html)->toMatch('/id="kpiInactive"\s*>1</')
        ->and($html)->toMatch('/Rupture <span class="num"\s*>1</')
        ->and($html)->toMatch('/Stock faible <span class="num"\s*>1</')
        ->and(preg_match_all('/data-status-badge\s*>/', $html))->toBe(1)
        ->and($html)->toContain('aria-label="Produit actif : Robe brodée"')
        ->and($html)->not->toMatch('/[\x{1F300}-\x{1FAFF}]/u'); // plus aucun emoji

    // Menu ⋯ : données JSON valides (apostrophe comprise) avec le lien public du thème
    preg_match_all("#data-menu='([^']*)'#", $html, $menus);
    $first = collect($menus[1])->map(fn ($j) => json_decode(html_entity_decode($j, ENT_QUOTES), true))->firstWhere('name', "Crème L'Oréal");
    expect($first)->not->toBeNull()->and($first['view'])->toContain('/s/chez-fanta/p/');

    // Le JavaScript de la page et les onclick sont lisibles tels qu'envoyés au navigateur
    preg_match_all('#<script>(.*?)</script>#s', $html, $m);
    $js = collect($m[1])->first(fn ($s) => str_contains($s, 'openGallery'));
    preg_match_all("#onclick='([^']*)'#", $html, $o);
    $file = sys_get_temp_dir() . '/shopio-products-' . getmypid() . '.js';
    file_put_contents($file, $js . "\n" . implode("\n", array_map(fn ($a) => 'void (function(){' . html_entity_decode($a, ENT_QUOTES) . '});', $o[1])));
    exec('node --check ' . escapeshellarg($file) . ' 2>&1', $out, $code);
    @unlink($file);
    expect($code)->toBe(0, implode("\n", $out));
});

test('filtres : inactifs, rupture, nombre par page', function () {
    F::product($this->shop, ['name' => 'Montre dorée', 'stock' => 2]);
    F::product($this->shop, ['name' => 'Sac en wax', 'stock' => 0]);
    F::product($this->shop, ['name' => 'Robe brodée', 'stock' => 12, 'is_active' => false]);
    foreach (range(1, 11) as $i) F::product($this->shop, ['name' => "Bracelet $i", 'stock' => 20]);

    $this->actingAs($this->owner)->get(route('products.index', ['status' => 'inactive']))
        ->assertSee('Robe brodée')->assertDontSee('Montre dorée');
    $this->actingAs($this->owner)->get(route('products.index', ['stock' => 'out']))
        ->assertSee('Sac en wax')->assertDontSee('Montre dorée');
    $this->actingAs($this->owner)->get(route('products.index', ['stock' => 'low']))
        ->assertSee('Montre dorée')->assertDontSee('Bracelet 1<');

    // 14 produits : 12 par défaut (3 rangées de 4), 24 sur demande
    $this->actingAs($this->owner)->get(route('products.index'))->assertSee('1–12 sur 14')->assertSee('1 / 2');
    $this->actingAs($this->owner)->get(route('products.index', ['par_page' => 24]))->assertSee('1–14 sur 14');
});

test('activer ou désactiver répond en JSON', function () {
    $p = F::product($this->shop, ['name' => 'Montre']);
    $this->actingAs($this->owner)->postJson(route('products.toggle', $p))->assertOk()->assertJson(['active' => false]);
    expect($p->fresh()->is_active)->toBeFalse();
});

test('sans produit, la page propose de créer le premier avec Shopio IA', function () {
    $this->actingAs($this->owner)->get(route('products.index'))->assertOk()
        ->assertSee('Votre premier produit, en une photo')->assertSee('Créer avec Shopio IA');
});
