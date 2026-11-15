<?php

use App\Models\ProductPage;
use App\Models\User;
use App\PageBuilder\PageFactory;
use App\PageBuilder\RenderContext;
use App\PageBuilder\Renderer;
use Database\Factories\ProductPageFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CampaignFixtures as F;

/** Propriétaire d'une boutique, rattaché à sa boutique, et un produit en ligne */
function builderVendor(array $productAttributes = []): array
{
    $shop = F::shop();
    $user = User::find($shop->user_id);
    $user->forceFill(['shop_id' => $shop->id])->save();
    return [$user, $shop, F::product($shop, $productAttributes)];
}

/** Révision attendue par le serveur pour le brouillon enregistré */
function builderRevision($product): string
{
    return \App\Http\Controllers\Vendeur\PageBuilderController::revision(ProductPage::where('product_id', $product->id)->first());
}

test('l\'éditeur s\'ouvre avec la section Produit et les schémas', function () {
    [$user, , $product] = builderVendor();

    $this->actingAs($user)->get(route('products.builder.edit', $product))
        ->assertOk()
        ->assertSee('Ajouter une section')
        ->assertSee(route('products.builder.frame', $product), false)
        ->assertSee('page-builder', false);

    $page = ProductPage::where('product_id', $product->id)->first();
    expect($page->draft['order'])->toBe(['main'])
        ->and($page->draft['sections']['main']['type'])->toBe('main-product')
        ->and($page->isPublished())->toBeFalse();

    // Le cadre d'aperçu affiche le brouillon, avec le pont de l'éditeur
    $this->actingAs($user)->get(route('products.builder.frame', $product))
        ->assertOk()
        ->assertSee('data-section-id="main"', false)
        ->assertSee('pb:render', false)
        ->assertSee($product->name);
});

test('un vendeur ne touche pas aux pages des autres boutiques', function () {
    [$user] = builderVendor();
    [, , $foreign] = builderVendor();
    $page = PageFactory::blank();

    $this->actingAs($user)->get(route('products.builder.edit', $foreign))->assertForbidden();
    $this->actingAs($user)->get(route('products.builder.frame', $foreign))->assertForbidden();
    $this->actingAs($user)->postJson(route('products.builder.preview', $foreign), ['page' => $page])->assertForbidden();
    $this->actingAs($user)->putJson(route('products.builder.save', $foreign), ['page' => $page, 'revision' => ''])->assertForbidden();
    $this->actingAs($user)->postJson(route('products.builder.publish', $foreign))->assertForbidden();
    $this->actingAs($user)->postJson(route('products.builder.upload', $foreign), ['image' => UploadedFile::fake()->image('a.jpg')])->assertForbidden();

    expect(ProductPage::where('product_id', $foreign->id)->exists())->toBeFalse();

    auth()->logout();
    $this->get(route('products.builder.edit', $foreign))->assertRedirect(route('login'));
});

test('enregistrement du brouillon : validé, nettoyé, et refusé si la page a changé ailleurs', function () {
    [$user, , $product] = builderVendor();
    // L'empreinte envoyée à l'éditeur doit être acceptée telle quelle au premier enregistrement
    $revision = $this->actingAs($user)->get(route('products.builder.edit', $product))->viewData('config')['revision'];
    expect($revision)->toBe(builderRevision($product));

    [$page, $id] = PageFactory::addSection(PageFactory::blank(), 'rich-text');
    $page['sections'][$id]['settings']['content'] = '<p>Livré à <strong>Conakry</strong></p><script>alert(1)</script>';

    $r = $this->actingAs($user)->putJson(route('products.builder.save', $product), ['page' => $page, 'revision' => $revision])
        ->assertOk()->assertJsonPath('pending', true);
    $draft = ProductPage::where('product_id', $product->id)->first()->draft;
    expect($draft['order'])->toBe(['main', $id])
        ->and($draft['sections'][$id]['settings']['content'])->toBe('<p>Livré à <strong>Conakry</strong></p>')
        ->and($r->json('revision'))->toBe(builderRevision($product))->not->toBe($revision);

    // Ancienne révision (autre onglet) : refusé, rien n'est écrasé
    $page['order'] = ['main'];
    unset($page['sections'][$id]);
    $this->actingAs($user)->putJson(route('products.builder.save', $product), ['page' => $page, 'revision' => $revision])
        ->assertStatus(409)->assertJsonPath('message', fn ($m) => str_contains($m, 'modifiée ailleurs'));
    expect(ProductPage::where('product_id', $product->id)->first()->draft['order'])->toBe(['main', $id]);

    // Page invalide : erreurs par emplacement, brouillon inchangé
    $bad = PageFactory::blank();
    $bad['sections']['main']['settings']['padding_top_mobile'] = 999;
    $this->actingAs($user)->putJson(route('products.builder.save', $product), ['page' => $bad, 'revision' => $r->json('revision')])
        ->assertStatus(422)->assertJsonValidationErrors(['sections.main.settings.padding_top_mobile']);
    expect(ProductPage::where('product_id', $product->id)->first()->draft['order'])->toBe(['main', $id]);
});

test('aperçu en direct : rendu de la page non enregistrée, ou erreurs', function () {
    [$user, , $product] = builderVendor(['price' => 85000, 'original_price' => 100000]);

    [$page, $id] = PageFactory::addSection(PageFactory::blank(), 'rich-text');
    $page['sections'][$id]['settings']['heading'] = 'Fabriqué en Guinée';
    $page['sections'][$id]['settings']['content'] = '<p onclick="x()">Texte <a href="javascript:alert(1)">piège</a></p>';

    $html = $this->actingAs($user)->postJson(route('products.builder.preview', $product), ['page' => $page])
        ->assertOk()->getContent();

    expect($html)->toContain('Fabriqué en Guinée')
        ->toContain('data-section-id="' . $id . '"')
        ->toContain('−15 %')
        ->not->toContain('onclick')->not->toContain('javascript:');
    expect(ProductPage::where('product_id', $product->id)->exists())->toBeFalse(); // rien d'enregistré

    $page['sections'][$id]['type'] = 'section-pirate';
    $this->actingAs($user)->postJson(route('products.builder.preview', $product), ['page' => $page])
        ->assertStatus(422)->assertJsonValidationErrors(["sections.{$id}.type"]);
});

test('publication : le brouillon devient la version publiée, 10 versions gardées', function () {
    [$user, , $product] = builderVendor();
    $this->actingAs($user)->get(route('products.builder.edit', $product));

    $this->actingAs($user)->postJson(route('products.builder.publish', $product))
        ->assertOk()->assertJsonPath('version', 1);

    $page = ProductPage::where('product_id', $product->id)->first();
    expect($page->isPublished())->toBeTrue()
        ->and($page->hasUnpublishedChanges())->toBeFalse()
        ->and($page->published_at)->not->toBeNull()
        ->and($page->versions)->toHaveCount(1)
        ->and($page->versions->first()->published_by)->toBe($user->id);

    for ($i = 0; $i < 11; $i++) {
        $this->actingAs($user)->postJson(route('products.builder.publish', $product))->assertOk();
    }
    $page->refresh();
    expect($page->version)->toBe(12)
        ->and($page->versions()->count())->toBe(ProductPage::VERSIONS_KEPT)
        ->and($page->versions()->pluck('version')->all())->toBe(range(12, 3));
});

test('envoi d\'image : WebP dans le dossier de la boutique, accepté ensuite par les réglages', function () {
    Storage::fake('public');
    config(['filesystems.default' => 'public']);
    [$user, $shop, $product] = builderVendor();

    $r = $this->actingAs($user)->postJson(route('products.builder.upload', $product), ['image' => UploadedFile::fake()->image('photo.jpg', 1200, 900)])
        ->assertOk();
    $path = $r->json('path');

    expect($path)->toMatch('#^page-builder/' . $shop->id . '/medium/[A-Za-z0-9]{20}\.webp$#');
    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertExists(str_replace('/medium/', '/large/', $path));
    Storage::disk('public')->assertExists(str_replace('/medium/', '/thumb/', $path));

    $this->actingAs($user)->postJson(route('products.builder.upload', $product), ['image' => UploadedFile::fake()->create('virus.php', 10, 'text/plain')])
        ->assertStatus(422)->assertJsonValidationErrors(['image']);
});

test('rendu : sections et blocs masqués absents, ancien prix seulement s\'il est plus élevé', function () {
    [, $shop, $product] = builderVendor(['price' => 50000, 'original_price' => 40000]);
    $page = ProductPageFactory::sampleData();
    [$page, $id] = PageFactory::addSection($page, 'rich-text');
    $page['sections'][$id]['settings']['heading'] = 'Section cachée';
    $page['sections'][$id]['disabled'] = true;
    $page['sections']['main']['blocks']['title']['disabled'] = true;

    $html = Renderer::render($page, new RenderContext($shop, $product));

    expect($html)->not->toContain('Section cachée')
        ->not->toContain('class="pb-title')
        ->not->toContain('<s>')
        ->not->toContain('data-section-id') // hors éditeur : pas d'attributs d'édition
        ->not->toContain('pb:select')
        ->toContain('pb-price');
});
