<?php

use App\Services\SectionAi;
use App\Support\HomeSections;
use Tests\Support\CampaignFixtures as F;

/* Shopio IA sur les sections (page produit et page d'accueil) : sections prises en charge, contenu gardé, accès Pro. */

beforeEach(function () {
    $this->shop = F::shop();
    $this->shop->update(['slug' => 'chez-fanta', 'currency' => 'GNF']);
    $this->owner = $this->shop->owner;
    $this->owner->forceFill(['shop_id' => $this->shop->id, 'role_in_shop' => 'admin'])->save();
    $this->product = F::product($this->shop, ['name' => 'Sac en wax']);
    $this->pro = function () {
        $this->shop->forceFill(['plan' => 'pro', 'plan_expires_at' => now()->addMonth()])->save();
        $this->owner->unsetRelation('shop'); // l'utilisateur connecté relit sa boutique, désormais Pro
    };
});

test('l’IA prend en charge toute section ajoutée qui a du texte, et seulement celles-là', function () {
    $types = SectionAi::writableTypes();
    foreach (['text', 'imgtext', 'qa', 'faq2', 'icons', 'banner', 'marquee', 'compare', 'slideshow', 'promo', 'trust'] as $t) {
        expect($types)->toContain($t);
    }
    // Rien à écrire : forme du séparateur, ou produits de la boutique
    expect($types)->not->toContain('divider');
    // Avis et témoignages : seulement leur titre, jamais un avis inventé
    expect(array_column(SectionAi::writableFields('quotes'), 0))->toBe(['title']);
    // Code promo, liens et colonne « vous » du tableau : jamais écrits par l'IA
    expect(array_column(SectionAi::writableFields('promo'), 0))->toBe(['text']);
    expect(array_column(SectionAi::writableFields('banner'), 0))->not->toContain('link');
    expect(array_column(SectionAi::writableFields('compare'), 0))->not->toContain('us');
    // Toute section de la bibliothèque qui a un champ de texte est prise en charge (nouvelles sections incluses)
    foreach (HomeSections::ADDABLE as $t) {
        $hasText = collect(HomeSections::FIELDS[$t])->contains(fn ($f) => in_array($f[1], ['line', 'text'], true));
        if ($hasText) expect($types)->toContain($t);
    }
});

test('les textes de l’IA gardent photos, liens et réglages du vendeur', function () {
    $img = 'covers/medium/' . str_repeat('a', 20) . '.webp';
    $cur = HomeSections::normalizeData('imgtext', ['img' => $img, 'title' => 'Ancien', 'link' => 'https://exemple.com/promo', 'side' => 'right']);
    $d = SectionAi::merge('imgtext', $cur, ['title' => 'Nouveau titre', 'text' => 'Un texte clair.', 'button' => 'Voir', 'img' => null, 'link' => 'https://pirate.test'], SectionAi::writableFields('imgtext'));
    expect($d['title'])->toBe('Nouveau titre')->and($d['text'])->toBe('Un texte clair.')
        ->and($d['img'])->toBe($img)->and($d['link'])->toBe('https://exemple.com/promo')->and($d['side'])->toBe('right');

    // Diaporama : textes posés sur les photos existantes, liens gardés
    $slides = HomeSections::normalizeData('slideshow', ['items' => [['img' => $img, 'title' => '', 'text' => '', 'button' => '', 'link' => 'https://exemple.com/a']]]);
    $s = SectionAi::merge('slideshow', $slides, ['items' => [['title' => 'Nouvelle collection', 'text' => 'Du wax de qualité.', 'button' => 'Découvrir'], ['title' => 'En trop', 'text' => '', 'button' => '']]], SectionAi::writableFields('slideshow'));
    expect($s['items'])->toHaveCount(1)
        ->and($s['items'][0]['img'])->toBe($img)->and($s['items'][0]['link'])->toBe('https://exemple.com/a')
        ->and($s['items'][0]['title'])->toBe('Nouvelle collection');

    // Tableau : « vous » oui, « les autres » non, colonne « vous » non touchée
    $c = SectionAi::merge('compare', HomeSections::normalizeData('compare', ['us' => 'Chez Fanta']), ['title' => 'Pourquoi nous ?', 'items' => [['label' => 'Paiement à la livraison']]], SectionAi::writableFields('compare'));
    expect($c['us'])->toBe('Chez Fanta')->and($c['items'][0])->toMatchArray(['label' => 'Paiement à la livraison', 'us' => true, 'them' => false]);
});

test('page produit : rédiger une section est réservé au plan Pro', function () {
    $this->actingAs($this->owner)
        ->postJson(route('products.page.sections-ai', $this->product), ['sections' => [['id' => 'sabc12345', 'type' => 'text', 'd' => []]]])
        ->assertForbidden()->assertJsonPath('error', fn ($e) => str_contains($e, 'plan Pro'));
});

test('page produit : demande vérifiée, puis message clair si l’IA n’est pas configurée', function () {
    ($this->pro)();
    config(['services.anthropic.key' => '']);
    $url = route('products.page.sections-ai', $this->product);
    $this->actingAs($this->owner)->postJson($url, [])->assertStatus(422);
    $this->actingAs($this->owner)->postJson($url, ['sections' => [['id' => 'sabc12345', 'type' => 'text', 'd' => []]], 'instructions' => str_repeat('a', 501)])->assertStatus(422);
    $this->actingAs($this->owner)->postJson($url, ['sections' => [['id' => 'sabc12345', 'type' => 'text', 'd' => []]], 'instructions' => 'ton plus luxe'])
        ->assertStatus(502)->assertJsonPath('error', fn ($e) => str_contains($e, 'pas configuré'));
});

test('page produit : le produit d’une autre boutique est refusé', function () {
    ($this->pro)();
    $other = F::product(F::shop());
    $this->actingAs($this->owner)
        ->postJson(route('products.page.sections-ai', $other), ['sections' => [['id' => 'sabc12345', 'type' => 'text', 'd' => []]]])
        ->assertForbidden();
});

test('page produit : la consigne est acceptée par « Remplir la page »', function () {
    ($this->pro)();
    config(['services.anthropic.key' => '']);
    $url = route('products.page.ai', $this->product);
    $this->actingAs($this->owner)->postJson($url, ['instructions' => str_repeat('a', 501)])->assertStatus(422);
    $this->actingAs($this->owner)->postJson($url, ['instructions' => 'parlez de la Tabaski'])->assertStatus(502);
});

test('page d’accueil : réservé au plan Pro, thème vérifié', function () {
    $url = route('boutique.storefront.sections.ai');
    $body = ['theme' => 'express', 'sections' => [['id' => 'hero', 'type' => 'hero', 'd' => []]]];
    $this->actingAs($this->owner)->postJson($url, $body)->assertForbidden();

    ($this->pro)();
    config(['services.anthropic.key' => '']);
    $this->actingAs($this->owner)->postJson($url, ['theme' => 'inconnu'] + $body)->assertStatus(422);
    $this->actingAs($this->owner)->postJson($url, $body)->assertStatus(502);
});

test('les éditeurs proposent Shopio IA sur les sections', function () {
    ($this->pro)();
    $this->actingAs($this->owner)->get(route('boutique.storefront.sections'))->assertOk()
        ->assertSee('id="sxAiAll"', false)->assertSee('Rédiger toutes les sections avec Shopio IA')
        ->assertSee('sections\/ia', false);
    $this->actingAs($this->owner)->get(route('products.page.edit', $this->product))->assertOk()
        ->assertSee('sections-ia', false)->assertSee('"aiPro":true', false);
});
