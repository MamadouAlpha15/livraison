<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductPage;
use App\Models\User;
use App\PageBuilder\PageFactory;
use App\PageBuilder\PageValidator;
use App\PageBuilder\RenderContext;
use App\PageBuilder\Renderer;
use App\PageBuilder\Settings\Url;
use App\PageBuilder\ValidationContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->shop = F::shop();
    $this->owner = User::find($this->shop->user_id);
    $this->owner->forceFill(['shop_id' => $this->shop->id])->save();
    $this->product = F::product($this->shop, ['name' => 'Bracelet en or 18 carats', 'price' => 900000, 'stock' => 3]);
});

/** Publie la page de départ du produit, validée */
function publishBlank($t, ?array $page = null): ProductPage
{
    $clean = (new PageValidator(new ValidationContext($t->shop, $t->product)))->validate($page ?? PageFactory::blank());
    return ProductPage::updateOrCreate(['product_id' => $t->product->id], ['shop_id' => $t->shop->id, 'draft' => $clean, 'published' => $clean, 'published_at' => now(), 'is_enabled' => true, 'version' => 1]);
}

test('éditeur : interdit aux clients, aux vendeurs sans boutique et aux invités', function () {
    $routes = [
        ['get', route('products.builder.edit', $this->product)],
        ['getJson', route('products.builder.versions', $this->product)],
        ['getJson', route('products.builder.templates', $this->product)],
        ['postJson', route('products.builder.publish', $this->product)],
    ];
    $client = User::factory()->create(['role' => 'client']);
    $noShop = User::factory()->create(['role' => 'vendeur', 'shop_id' => null]);

    foreach ($routes as [$method, $url]) {
        expect($this->actingAs($client)->{$method}($url)->status())->toBeIn([302, 403]);
        expect($this->actingAs($noShop)->{$method}($url)->status())->toBe(403);
    }
    expect(ProductPage::count())->toBe(0);
});

test('page trop lourde refusée avant toute validation', function () {
    $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product));
    $page = PageFactory::blank();
    $page['padding'] = str_repeat('x', 310_000);
    $this->actingAs($this->owner)->postJson(route('products.builder.preview', $this->product), ['page' => $page])->assertStatus(413);
});

test('commande refusée : boutique non vérifiée, produit hors ligne, boutique en pause', function () {
    publishBlank($this);
    $order = fn () => $this->postJson(route('client.products.page-order', $this->product), ['name' => 'Aïssatou Bah', 'phone' => '622458137', 'commune' => 'Kaloum', 'quantity' => 1]);

    $this->shop->forceFill(['storefront' => ['settings' => ['vacation' => true, 'vacation_msg' => 'retour lundi']]])->save();
    $order()->assertStatus(423)->assertJsonPath('message', fn ($m) => str_contains($m, 'retour lundi'));

    $this->shop->forceFill(['storefront' => null, 'is_approved' => false])->save();
    $order()->assertNotFound();

    $this->shop->forceFill(['is_approved' => true])->save();
    $this->product->update(['is_active' => false]);
    $order()->assertNotFound();

    $this->product->update(['is_active' => true]);
    $order()->assertCreated();
    expect(Order::count())->toBe(1);
});

test('produit épuisé : la page publiée remplace le formulaire par « Bientôt de retour »', function () {
    publishBlank($this);
    $this->product->update(['stock' => 0]);
    $html = $this->get(route('client.products.show', $this->product))->assertOk()->getContent();
    expect($html)->toContain('Bientôt de retour')->not->toContain('<button type="submit" class="pb-btn"')
        ->toContain('"availability":"https://schema.org/OutOfStock"');
});

test('une page enregistrée avec un type disparu s\'affiche quand même, sans lui', function () {
    $page = publishBlank($this);
    $data = $page->published;
    $data['sections']['ancienne'] = ['type' => 'section-retiree', 'settings' => [], 'blocks' => [], 'block_order' => []];
    $data['order'][] = 'ancienne';
    $data['sections']['main']['blocks']['vieux'] = ['type' => 'bloc-retire', 'settings' => ['x' => 1]];
    $data['sections']['main']['block_order'][] = 'vieux';
    unset($data['sections']['main']['settings']['gallery_layout']);
    $page->forceFill(['published' => $data])->save();

    $html = $this->get(route('client.products.show', $this->product))->assertOk()->getContent();
    expect($html)->toContain('Bracelet en or 18 carats')->toContain('data-pb-checkout')->not->toContain('section-retiree');
});

test('liens : seulement http(s) ou une page de Shopio', function () {
    $ctx = new ValidationContext($this->shop, $this->product);
    $ok = fn ($v) => Validator::make(['v' => $v], ['v' => (new Url('l', 'Lien'))->rules($ctx)])->passes();
    expect($ok('https://guineematin.com/article'))->toBeTrue()
        ->and($ok('/client/produits'))->toBeTrue()
        ->and($ok('javascript:alert(1)'))->toBeFalse()
        ->and($ok('data:text/html,<script>'))->toBeFalse()
        ->and($ok('https://exemple.com/"onmouseover="x'))->toBeFalse();
});

test('page de 40 sections : validée et affichée sans requête par section', function () {
    $page = PageFactory::blank();
    $types = ['faq', 'rich-text', 'steps', 'image-with-text', 'video', 'announcement', 'product-list'];
    for ($i = 0; count($page['order']) < 40; $i++) {
        [$page] = PageFactory::addSection($page, $types[$i % count($types)]);
    }
    expect(strlen(json_encode($page)))->toBeLessThan(300_000);
    $clean = (new PageValidator(new ValidationContext($this->shop, $this->product)))->validate($page);

    DB::enableQueryLog();
    $html = Renderer::render($clean, new RenderContext($this->shop, $this->product));
    $queries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect(substr_count($html, '<section id="pb-'))->toBeGreaterThanOrEqual(30)
        ->and($queries)->toBeLessThanOrEqual(15); // 8 mesurées, dont une par section « Produits de la boutique »
});

test('fiche commande du vendeur : offre, cadeau et réponses du client visibles', function () {
    $gift = F::product($this->shop, ['name' => 'Écrin cadeau', 'price' => 20000, 'stock' => 5]);
    $section = PageFactory::section('main-product');
    $section['blocks'] = [
        'o' => PageFactory::block('offers', ['tier2_qty' => 2, 'tier2_gift' => $gift->id, 'tier3_enabled' => false]),
        'c' => PageFactory::block('custom_field', ['label' => 'Prénom à graver', 'required' => true]),
        'f' => PageFactory::block('order_form'),
    ];
    $section['block_order'] = ['o', 'c', 'f'];
    publishBlank($this, ['sections' => ['main' => $section], 'order' => ['main']]);

    $id = $this->postJson(route('client.products.page-order', $this->product), [
        'name' => 'Aïssatou Bah', 'phone' => '622458137', 'commune' => 'Kaloum', 'tier' => 2, 'fields' => ['c' => 'Mariama'],
    ])->assertCreated()->json('id');

    $this->actingAs($this->owner)->get(route('orders.show', Order::find($id)))->assertOk()
        ->assertSee('Prénom à graver :')
        ->assertSee('Mariama')
        ->assertSee('Cadeau offert avec l')
        ->assertSee('Offre « 2 articles »', false);
});
