<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use App\PageBuilder\Media\Mp4;
use App\PageBuilder\PageFactory;
use App\PageBuilder\PageValidator;
use App\PageBuilder\Registry;
use App\PageBuilder\RenderContext;
use App\PageBuilder\Renderer;
use App\PageBuilder\ValidationContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->shop = F::shop();
    $this->owner = User::find($this->shop->user_id);
    $this->owner->forceFill(['shop_id' => $this->shop->id])->save();
    $this->product = F::product($this->shop, ['name' => 'Beurre de karité pur']);
});

/** Page Produit + une section (préréglage $preset, réglages modifiés), validée puis rendue */
function renderWith(string $type, array $settings = [], bool $editing = false, int $preset = 0, ?callable $tweak = null): string
{
    $t = test();
    [$page, $id] = PageFactory::addSection(PageFactory::blank(), $type, $preset);
    $page['sections'][$id]['settings'] = array_replace($page['sections'][$id]['settings'], $settings);
    if ($tweak) {
        $page['sections'][$id] = $tweak($page['sections'][$id]);
    }
    $clean = (new PageValidator(new ValidationContext($t->shop, $t->product)))->validate($page);
    return Renderer::render($clean, new RenderContext($t->shop, $t->product, $editing));
}

/** Petit fichier MP4 valide (ftyp + moov/mvhd) d'une durée donnée */
function mp4Bytes(int $seconds, int $version = 0): string
{
    $ftyp = pack('N', 20) . 'ftyp' . 'isom' . pack('N', 512) . 'isom';
    $body = $version === 0
        ? "\x00\x00\x00\x00" . pack('N', 0) . pack('N', 0) . pack('N', 1000) . pack('N', $seconds * 1000)
        : "\x01\x00\x00\x00" . pack('J', 0) . pack('J', 0) . pack('N', 1000) . pack('J', $seconds * 1000);
    $mvhd = pack('N', 8 + strlen($body) + 80) . 'mvhd' . $body . str_repeat("\x00", 80);
    return $ftyp . pack('N', 8 + strlen($mvhd)) . 'moov' . $mvhd;
}

test('bibliothèque : 9 sections à ajouter, rangées par catégorie', function () {
    $library = collect(Registry::library())->mapWithKeys(fn ($c) => [$c['key'] => array_column($c['sections'], 'type')]);

    expect($library->all())->toMatchArray([
        'produit'      => ['product-list'],
        'medias'       => ['image-with-text', 'video'],
        'preuve'       => ['featured-in', 'reviews'],
        'conversion'   => ['steps'],
        'contenu'      => ['faq', 'rich-text'],
        'mise_en_page' => ['announcement'],
    ]);
});

test('chaque préréglage de chaque section est valide et s\'affiche, dans l\'éditeur comme pour les clients', function () {
    foreach (Registry::sections() as $type => $section) {
        if ($section->isStatic()) {
            continue;
        }
        foreach (array_keys($section->presets()) as $i) {
            expect(renderWith($type, [], false, $i))->toContain('<main id="pb-main">');
            expect(renderWith($type, [], true, $i))->toContain('data-section-id');
        }
    }
});

test('avis : cachés sans avis réels, puis affichés avec le prénom et l\'initiale', function () {
    expect(renderWith('reviews'))->not->toContain('id="avis"')
        ->and(renderWith('reviews', [], true))->toContain('aucun pour l\'instant');

    $client = User::factory()->create(['role' => 'client', 'name' => 'Mariama Sow']);
    foreach ([[5, 'Très doux, je recommande'], [4, null]] as [$rating, $comment]) {
        $order = Order::forceCreate(['user_id' => $client->id, 'shop_id' => $this->shop->id, 'total' => 1, 'status' => Order::STATUS_LIVREE]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'quantity' => 1, 'price' => 1]);
        Review::forceCreate(['order_id' => $order->id, 'user_id' => $client->id, 'rating' => $rating, 'comment' => $comment]);
    }

    $html = renderWith('reviews');
    expect($html)->toContain('id="avis"')->toContain('Très doux, je recommande')->toContain('Mariama S.')
        ->toContain('4,5')->toContain('2 avis')
        ->and(substr_count($html, 'class="pb-rv-card"'))->toBe(1); // l'avis sans commentaire est écarté
    expect(substr_count(renderWith('reviews', ['only_commented' => false]), 'class="pb-rv-card"'))->toBe(2);
});

test('« Vu dans » : rien pour les clients sans confirmation du vendeur', function () {
    $logo = "page-builder/{$this->shop->id}/medium/" . str_repeat('aB3', 8) . '.webp';
    $tweak = function ($s) use ($logo) {
        $first = $s['block_order'][0];
        $s['blocks'][$first]['settings'] = ['image' => $logo, 'name' => 'Guinée Matin', 'link' => 'https://guineematin.com/article'];
        return $s;
    };

    expect(renderWith('featured-in', [], false, 0, $tweak))->not->toContain('Guinée Matin');
    expect(renderWith('featured-in', [], true, 0, $tweak))->toContain('Guinée Matin')->toContain('tant que tu n\'as pas coché');
    expect(renderWith('featured-in', ['confirmed' => true], false, 0, $tweak))
        ->toContain('alt="Guinée Matin"')->toContain('rel="noopener nofollow"')->toContain('Ils parlent de nous');

    // Confirmé mais sans aucun logo : la section reste cachée
    expect(renderWith('featured-in', ['confirmed' => true]))->not->toContain('Ils parlent de nous');
});

test('vidéo : YouTube sans cookies chargé au clic, TikTok intégré, MP4 de la boutique', function () {
    expect(renderWith('video', ['video' => 'https://youtu.be/dQw4w9WgXcQ']))
        ->toContain('data-pb-yt="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->toContain('i.ytimg.com/vi/dQw4w9WgXcQ')
        ->not->toContain('<iframe src="https://www.youtube');

    expect(renderWith('video', ['video' => 'https://www.tiktok.com/@shopio.gn/video/7301234567890123456']))
        ->toContain('src="https://www.tiktok.com/embed/v2/7301234567890123456"');

    $mp4 = "page-builder/{$this->shop->id}/videos/" . str_repeat('x', 32) . '.mp4';
    expect(renderWith('video', ['video' => $mp4, 'autoplay' => true]))->toContain('autoplay muted loop playsinline')->toContain($mp4);
    expect(renderWith('video'))->not->toContain('class="pb-vid-box"');
});

test('envoi de vidéo : MP4 de 60 secondes au plus, dans le dossier de la boutique', function () {
    Storage::fake('public');
    config(['filesystems.default' => 'public']);
    $url = route('products.builder.video', $this->product);

    expect(Mp4::duration(tap(tempnam(sys_get_temp_dir(), 'mp4'), fn ($f) => file_put_contents($f, mp4Bytes(42, 1)))))->toBe(42.0);

    $r = $this->actingAs($this->owner)->post($url, ['video' => UploadedFile::fake()->createWithContent('clip.mp4', mp4Bytes(30))], ['Accept' => 'application/json'])
        ->assertOk();
    expect($r->json('path'))->toMatch('#^page-builder/' . $this->shop->id . '/videos/[A-Za-z0-9]{32}\.mp4$#');
    Storage::disk('public')->assertExists($r->json('path'));

    // Le chemin renvoyé est accepté par le réglage « vidéo »
    expect(renderWith('video', ['video' => $r->json('path')]))->toContain('<video');

    $this->actingAs($this->owner)->post($url, ['video' => UploadedFile::fake()->createWithContent('long.mp4', mp4Bytes(95))], ['Accept' => 'application/json'])
        ->assertStatus(422)->assertJsonPath('errors.video.0', 'La vidéo dure 95 secondes : 60 secondes au plus.');
    $this->actingAs($this->owner)->post($url, ['video' => UploadedFile::fake()->createWithContent('faux.mp4', 'pas une vidéo')], ['Accept' => 'application/json'])
        ->assertStatus(422)->assertJsonValidationErrors(['video']);

    $other = User::find(F::shop()->user_id);
    $this->actingAs($other)->post($url, ['video' => UploadedFile::fake()->createWithContent('clip.mp4', mp4Bytes(10))], ['Accept' => 'application/json'])
        ->assertForbidden();
});

test('produits de la boutique : en ligne, sans le produit de la page ; ordre choisi respecté', function () {
    $a = F::product($this->shop, ['name' => 'Savon au karité']);
    $b = F::product($this->shop, ['name' => 'Huile de coco']);
    F::product($this->shop, ['name' => 'Produit hors ligne', 'is_active' => false]);
    F::product(F::shop(), ['name' => 'Produit d\'une autre boutique']);

    $html = renderWith('product-list');
    expect($html)->toContain('Savon au karité')->toContain('Huile de coco')
        ->not->toContain('Produit hors ligne')->not->toContain('autre boutique')
        ->not->toContain('class="pb-pl-card" href="' . route('client.products.show', $this->product) . '"');

    $manual = renderWith('product-list', ['source' => 'manual', 'products' => [$b->id, $a->id]]);
    expect(strpos($manual, 'Huile de coco'))->toBeLessThan(strpos($manual, 'Savon au karité'));
});

test('préréglages honnêtes : étapes numérotées et réponses vraies sur Shopio ; section vide = pas de bande', function () {
    $steps = renderWith('steps');
    expect(substr_count($steps, 'class="pb-step-item"'))->toBe(4)->and($steps)->toContain('Tu paies à la réception');
    expect(renderWith('faq'))->toContain('Dois-je payer avant la livraison ?');
    expect(renderWith('announcement', ['text' => '']))->not->toContain('pb-section--announcement');
    expect(renderWith('announcement'))->toContain('pb-section--announcement')->toContain('Paiement à la livraison');
    expect(renderWith('image-with-text', ['button_label' => 'Je commande']))->toContain('href="#commander"');
});

test('les aperçus en direct n\'épuisent pas les limites de publication et d\'envoi', function () {
    Storage::fake('public');
    config(['filesystems.default' => 'public']);
    $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product))->assertOk();

    for ($i = 0; $i < 15; $i++) {
        $this->actingAs($this->owner)->postJson(route('products.builder.preview', $this->product), ['page' => PageFactory::blank()])->assertOk();
    }
    $this->actingAs($this->owner)->post(route('products.builder.video', $this->product), ['video' => UploadedFile::fake()->createWithContent('clip.mp4', mp4Bytes(5))], ['Accept' => 'application/json'])->assertOk();
    $this->actingAs($this->owner)->postJson(route('products.builder.publish', $this->product))->assertOk();
});

test('un texte vidé dans l\'éditeur reste vide une fois enregistré (requête réelle)', function () {
    $revision = $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product))->viewData('config')['revision'];
    [$page, $id] = PageFactory::addSection(PageFactory::blank(), 'faq');
    $page['sections'][$id]['settings']['heading'] = '';

    $this->actingAs($this->owner)->putJson(route('products.builder.save', $this->product), ['page' => $page, 'revision' => $revision])->assertOk();
    expect(\App\Models\ProductPage::where('product_id', $this->product->id)->first()->draft['sections'][$id]['settings']['heading'])->toBe('');

    // Réglage absent : valeur par défaut
    unset($page['sections'][$id]['settings']['heading']);
    $html = $this->actingAs($this->owner)->postJson(route('products.builder.preview', $this->product), ['page' => $page])->assertOk()->getContent();
    expect($html)->toContain('<h2>Questions fréquentes</h2>');
});

test('avant / après sans photo : pas de cases vides chez les clients', function () {
    $public = renderWith('image-with-text', [], false, 1);
    expect($public)->not->toContain('class="pb-iwt-fig"')->toContain('pb-iwt--text')->toContain('Le résultat parle de lui-même');
    expect(renderWith('image-with-text', [], true, 1))->toContain('Choisis une image dans les réglages');

    $before = "page-builder/{$this->shop->id}/medium/" . str_repeat('aB3', 8) . '.webp';
    expect(substr_count(renderWith('image-with-text', ['image' => $before], false, 1), 'class="pb-iwt-fig"'))->toBe(1);
});
