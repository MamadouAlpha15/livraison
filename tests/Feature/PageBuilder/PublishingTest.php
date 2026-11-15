<?php

use App\Http\Controllers\Vendeur\PageBuilderController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PageTemplate;
use App\Models\ProductPage;
use App\Models\Review;
use App\Models\User;
use App\PageBuilder\PageFactory;
use App\PageBuilder\PageValidator;
use App\PageBuilder\RenderContext;
use App\PageBuilder\Renderer;
use App\PageBuilder\Templates\ShopioTemplates;
use App\PageBuilder\ValidationContext;
use Illuminate\Support\Facades\DB;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->shop = F::shop();
    $this->owner = User::find($this->shop->user_id);
    $this->owner->forceFill(['shop_id' => $this->shop->id])->save();
    $this->product = F::product($this->shop, ['name' => 'Montre connectée Haylou', 'price' => 390000, 'description' => 'Écran AMOLED, 7 jours d\'autonomie.']);
});

/** Ouvre l'éditeur (la page est créée) et renvoie la révision attendue */
function openEditor($t): string
{
    return $t->actingAs($t->owner)->get(route('products.builder.edit', $t->product))->viewData('config')['revision'];
}

test('les 9 modèles Shopio s\'installent une fois, sont valides et s\'affichent', function () {
    ShopioTemplates::sync();
    ShopioTemplates::sync();
    $keys = PageTemplate::whereNull('shop_id')->orderBy('position')->pluck('key')->all();
    expect($keys)->toBe(['classique', 'express', 'confiance', 'promo', 'histoire', 'mode', 'beaute', 'electronique', 'unique']);

    $validator = new PageValidator(new ValidationContext($this->shop, $this->product));
    foreach (PageTemplate::whereNull('shop_id')->get() as $tpl) {
        $page = $validator->validate($tpl->data);
        $main = collect($page['sections'])->firstWhere('type', 'main-product');
        expect(collect($main['blocks'])->pluck('type'))->toContain('order_form', 'title', 'price');

        $html = Renderer::render($page, new RenderContext($this->shop, $this->product));
        expect($html)->toContain('data-pb-checkout')
            ->not->toContain('class="pb-rv-card"')   // aucun avis inventé
            ->not->toContain('class="pb-cd"');         // pas de compte à rebours sans vraie échéance
    }

    // Un modèle désactivé par l'équipe le reste après une mise à jour
    PageTemplate::where('key', 'promo')->update(['is_active' => false]);
    ShopioTemplates::sync();
    expect(PageTemplate::where('key', 'promo')->value('is_active'))->toBeFalse();
});

test('modèles : liste, application au brouillon, et ceux des autres boutiques interdits', function () {
    openEditor($this);
    $other = PageTemplate::create(['shop_id' => F::shop()->id, 'name' => 'Secret', 'data' => PageFactory::blank()]);

    $list = $this->actingAs($this->owner)->getJson(route('products.builder.templates', $this->product))->assertOk();
    expect(collect($list->json('shopio'))->pluck('name'))->toContain('Classique', 'Électronique')
        ->and(collect($list->json('mine'))->pluck('name'))->not->toContain('Secret');

    $express = PageTemplate::where('key', 'express')->first();
    $r = $this->actingAs($this->owner)->postJson(route('products.builder.templates.apply', [$this->product, $express]))->assertOk()
        ->assertJsonPath('message', 'Modèle « Express » appliqué au brouillon.');
    $page = ProductPage::where('product_id', $this->product->id)->first();
    expect($page->page_template_id)->toBe($express->id)
        ->and($page->draft['sections']['main']['settings']['gallery_layout'])->toBe('slider')
        ->and($page->isPublished())->toBeFalse()
        ->and($r->json('revision'))->toBe(PageBuilderController::revision($page));

    $this->actingAs($this->owner)->postJson(route('products.builder.templates.apply', [$this->product, $other]))->assertForbidden();
    $express->update(['is_active' => false]);
    $this->actingAs($this->owner)->postJson(route('products.builder.templates.apply', [$this->product, $express]))->assertForbidden();
});

test('mes modèles : enregistrer la page, la réutiliser sur un autre produit, supprimer', function () {
    openEditor($this);
    [$page, $id] = PageFactory::addSection(PageFactory::blank(), 'faq');

    $this->actingAs($this->owner)->postJson(route('products.builder.templates.store', $this->product), ['page' => $page, 'name' => '', 'category' => 'general'])
        ->assertStatus(422)->assertJsonValidationErrors(['name']);
    $id = $this->actingAs($this->owner)->postJson(route('products.builder.templates.store', $this->product), ['page' => $page, 'name' => '<b>Montres</b>', 'category' => 'electronique'])
        ->assertCreated()->json('id');
    $tpl = PageTemplate::find($id);
    expect($tpl->shop_id)->toBe($this->shop->id)->and($tpl->name)->toBe('Montres')->and($tpl->created_by)->toBe($this->owner->id);

    $second = F::product($this->shop, ['name' => 'Montre Xiaomi']);
    $this->actingAs($this->owner)->get(route('products.builder.edit', $second));
    $this->actingAs($this->owner)->postJson(route('products.builder.templates.apply', [$second, $tpl]))->assertOk();
    expect(collect(ProductPage::where('product_id', $second->id)->first()->draft['sections'])->pluck('type'))->toContain('faq');

    // Les modèles Shopio ne se suppriment pas ; ceux d'une autre boutique non plus
    ShopioTemplates::sync();
    $this->actingAs($this->owner)->deleteJson(route('products.builder.templates.destroy', [$this->product, PageTemplate::where('key', 'classique')->first()]))->assertForbidden();
    $this->actingAs($this->owner)->deleteJson(route('products.builder.templates.destroy', [$this->product, $tpl]))->assertOk();
    expect(PageTemplate::find($id))->toBeNull();
});

test('historique : une ancienne publication revient dans le brouillon, sans être publiée', function () {
    $revision = openEditor($this);
    $publish = fn () => $this->actingAs($this->owner)->postJson(route('products.builder.publish', $this->product))->assertOk();
    $publish(); // version 1 : page de départ

    [$page, $id] = PageFactory::addSection(PageFactory::blank(), 'faq');
    $this->actingAs($this->owner)->putJson(route('products.builder.save', $this->product), ['page' => $page, 'revision' => PageBuilderController::revision(ProductPage::where('product_id', $this->product->id)->first())])->assertOk();
    $publish(); // version 2 : avec la FAQ

    $list = $this->actingAs($this->owner)->getJson(route('products.builder.versions', $this->product))->assertOk()->json('versions');
    expect(array_column($list, 'version'))->toBe([2, 1])->and($list[0]['current'])->toBeTrue()->and($list[0]['by'])->toBe($this->owner->name);

    $this->actingAs($this->owner)->postJson(route('products.builder.restore', [$this->product, 1]))->assertOk();
    $p = ProductPage::where('product_id', $this->product->id)->first();
    expect($p->draft['order'])->toBe(['main'])
        ->and(collect($p->published['sections'])->pluck('type'))->toContain('faq') // la page en ligne n'a pas changé
        ->and($p->hasUnpublishedChanges())->toBeTrue();

    $this->actingAs($this->owner)->postJson(route('products.builder.restore', [$this->product, 99]))->assertNotFound();
    $intruder = User::find(F::shop()->user_id);
    $this->actingAs($intruder)->getJson(route('products.builder.versions', $this->product))->assertForbidden();
});

test('cache : la page publiée est servie depuis le cache aux visiteurs, avec un jeton de formulaire valide', function () {
    openEditor($this);
    $this->actingAs($this->owner)->postJson(route('products.builder.publish', $this->product))->assertOk();
    $this->actingAs($this->owner)->putJson(route('products.builder.toggle', $this->product), ['enabled' => true])->assertOk();
    auth()->logout();

    $first = $this->get(route('client.products.show', $this->product))->assertOk()->getContent();
    expect($first)->toContain("390\u{00A0}000")->not->toContain(Renderer::CSRF_PLACEHOLDER)
        ->and($first)->toContain('content="' . csrf_token() . '"');

    // Changement en base sans passer par le modèle : la page en cache est resservie
    DB::table('products')->where('id', $this->product->id)->update(['price' => 1000]);
    expect($this->get(route('client.products.show', $this->product))->getContent())->toContain("390\u{00A0}000");

    // Modification normale du produit : la page est recalculée
    $this->product->refresh()->update(['price' => 350000]);
    expect($this->get(route('client.products.show', $this->product))->getContent())->toContain("350\u{00A0}000");

    // Un client connecté a sa propre page (nom pré-rempli), jamais celle du cache
    $client = User::factory()->create(['role' => 'client', 'name' => 'Fatoumata Camara']);
    expect($this->actingAs($client)->get(route('client.products.show', $this->product))->getContent())->toContain('value="Fatoumata Camara"');
    auth()->logout();
    expect($this->get(route('client.products.show', $this->product))->getContent())->not->toContain('Fatoumata Camara');
});

test('référencement : titre, description, partage et données structurées, sans rien inventer', function () {
    $this->product->update(['name' => 'Montre </script><script>alert(1)</script>']);
    [$page, $id] = PageFactory::addSection(PageFactory::blank(), 'faq');
    $validate = fn ($p) => (new PageValidator(new ValidationContext($this->shop, $this->product)))->validate($p);
    $render = fn ($p) => Renderer::render($validate($p), new RenderContext($this->shop, $this->product->fresh()));

    $html = $render($page);
    expect($html)->toContain('<meta name="description" content="Écran AMOLED, 7 jours d&#039;autonomie.">')
        ->toContain('<link rel="canonical" href="' . route('client.products.show', $this->product) . '">')
        ->toContain('"@type":"Product"')->toContain('"priceCurrency":"GNF"')->toContain('"availability":"https://schema.org/InStock"')
        ->toContain('"@type":"FAQPage"')->toContain('Dois-je payer avant la livraison ?')
        ->not->toContain('aggregateRating')                  // pas d'avis : pas de note
        ->not->toContain('</script><script>alert(1)')       // nom du produit échappé dans le JSON-LD
        ->not->toContain('noindex');

    $page['seo'] = ['title' => 'Montre Haylou à Conakry', 'description' => '<b>Livrée</b> en 24 h, payée à la livraison.'];
    expect($render($page))->toContain('<title>Montre Haylou à Conakry</title>')->toContain('content="Livrée en 24 h, payée à la livraison."');

    $page['seo'] = ['title' => str_repeat('a', 71)];
    expect(fn () => $validate($page))->toThrow(\Illuminate\Validation\ValidationException::class, '70 caractères');

    // Avec de vrais avis, la note apparaît
    $client = User::factory()->create(['role' => 'client']);
    $order = Order::forceCreate(['user_id' => $client->id, 'shop_id' => $this->shop->id, 'total' => 1, 'status' => Order::STATUS_LIVREE]);
    OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'quantity' => 1, 'price' => 1]);
    Review::forceCreate(['order_id' => $order->id, 'user_id' => $client->id, 'rating' => 5, 'comment' => 'Parfaite']);
    unset($page['seo']);
    expect($render($page))->toContain('"aggregateRating":{"@type":"AggregateRating","ratingValue":5');

    // Dans l'éditeur : pas de référencement, page non indexée
    $editing = Renderer::render($validate($page), new RenderContext($this->shop, $this->product, true));
    expect($editing)->toContain('noindex')->not->toContain('application/ld+json');
});
