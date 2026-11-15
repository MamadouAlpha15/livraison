<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PageTemplate;
use App\Models\ProductPage;
use App\Models\ProductPageVersion;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Gate;
use Tests\Support\CampaignFixtures as F;

test('une page par produit, brouillon et version publiée en JSON', function () {
    $page = ProductPage::factory()->create();

    expect($page->product->productPage->is($page))->toBeTrue()
        ->and($page->shop->productPages)->toHaveCount(1)
        ->and($page->draft['order'])->toBe(['main'])
        ->and($page->draft['sections']['main']['block_order'])->toBe(['title', 'price', 'order'])
        ->and($page->isPublished())->toBeFalse()
        ->and($page->hasUnpublishedChanges())->toBeTrue();

    $published = ProductPage::factory()->published()->create();
    expect($published->isPublished())->toBeTrue()->and($published->hasUnpublishedChanges())->toBeFalse();

    expect(fn () => ProductPage::create(['product_id' => $page->product_id, 'shop_id' => $page->shop_id]))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('versions publiées : de la plus récente à la plus ancienne, supprimées avec la page', function () {
    $page = ProductPage::factory()->published()->create();
    foreach ([1, 2, 3] as $v) {
        ProductPageVersion::create(['product_page_id' => $page->id, 'version' => $v, 'data' => $page->draft]);
    }

    expect($page->versions()->pluck('version')->all())->toBe([3, 2, 1]);
    $page->delete();
    expect(ProductPageVersion::count())->toBe(0);
});

test('modèles : ceux de Shopio et ceux de la boutique, jamais ceux d\'une autre', function () {
    $shop = F::shop();
    $other = F::shop();
    PageTemplate::factory()->create(['name' => 'Shopio Mode', 'key' => 'mode', 'category' => 'mode']);
    PageTemplate::factory()->create(['name' => 'Le mien', 'shop_id' => $shop->id]);
    PageTemplate::factory()->create(['name' => 'Celui d\'à côté', 'shop_id' => $other->id]);
    PageTemplate::factory()->create(['name' => 'Désactivé', 'is_active' => false]);

    expect(PageTemplate::availableTo($shop)->pluck('name')->all())->toBe(['Shopio Mode', 'Le mien'])
        ->and($shop->pageTemplates()->pluck('name')->all())->toBe(['Le mien']);
});

test('droits : un vendeur ne modifie que les pages de ses produits et n\'utilise que ses modèles', function () {
    $page = ProductPage::factory()->create();
    $owner = $page->shop->owner;
    $intruder = ProductPage::factory()->create()->shop->owner;
    $client = User::factory()->create(['role' => 'client']);
    $shopio = PageTemplate::factory()->create();
    $mine = PageTemplate::factory()->create(['shop_id' => $page->shop_id]);
    $theirs = PageTemplate::factory()->create(['shop_id' => $intruder->shop_id]);

    expect(Gate::forUser($owner)->allows('edit-product-page', $page->product))->toBeTrue()
        ->and(Gate::forUser($intruder)->allows('edit-product-page', $page->product))->toBeFalse()
        ->and(Gate::forUser($client)->allows('edit-product-page', $page->product))->toBeFalse()
        ->and(Gate::forUser($owner)->allows('use-page-template', $shopio))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('use-page-template', $mine))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('use-page-template', $theirs))->toBeFalse()
        ->and(Gate::forUser($owner)->allows('manage-page-template', $mine))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('manage-page-template', $shopio))->toBeFalse();
});

test('les choix du client sur la page sont gardés sur la ligne de commande', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $order = Order::forceCreate(['shop_id' => $shop->id, 'total' => 100000, 'status' => Order::STATUS_EN_ATTENTE]);
    $item = OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 2, 'price' => 50000,
        'options' => ['palier' => '2 articles -10 %', 'gravure' => 'Fatou']]);

    expect($item->fresh()->options)->toBe(['palier' => '2 articles -10 %', 'gravure' => 'Fatou']);
});
