<?php

use App\Models\Order;
use App\Models\ProductPage;
use App\Models\User;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->shop = F::shop();
    $this->owner = User::find($this->shop->user_id);
    $this->owner->forceFill(['shop_id' => $this->shop->id])->save();
    $this->product = F::product($this->shop, ['name' => 'Tecno Spark 20 Pro', 'price' => 1450000, 'stock' => 8]);
    $this->toggle = fn (bool $on) => $this->actingAs($this->owner)->putJson(route('products.builder.toggle', $this->product), ['enabled' => $on]);
    $this->order = fn () => $this->postJson(route('client.products.page-order', $this->product), ['name' => 'Aïssatou Bah', 'phone' => '622458137', 'commune' => 'Kaloum', 'quantity' => 1]);
});

test('la page construite est désactivée par défaut, même publiée', function () {
    $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product))->assertOk()
        ->assertViewHas('config', fn ($c) => $c['published']['enabled'] === false);
    expect(ProductPage::where('product_id', $this->product->id)->first()->is_enabled)->toBeFalse();

    $this->actingAs($this->owner)->postJson(route('products.builder.publish', $this->product))->assertOk()->assertJsonPath('enabled', false);
    auth()->logout();

    $this->get(route('client.products.show', $this->product))->assertOk()->assertSee('id="ppForm"', false)->assertDontSee('data-pb-checkout', false);
    ($this->order)()->assertNotFound();
});

test('impossible d\'activer une page jamais publiée', function () {
    $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product));
    ($this->toggle)(true)->assertStatus(422)->assertJsonValidationErrors(['enabled']);
    expect(ProductPage::where('product_id', $this->product->id)->first()->is_enabled)->toBeFalse();
});

test('activer puis désactiver : les clients passent d\'une page à l\'autre, sans rien perdre', function () {
    $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product));
    $this->actingAs($this->owner)->postJson(route('products.builder.publish', $this->product))->assertOk();

    ($this->toggle)(true)->assertOk()->assertJsonPath('enabled', true);
    auth()->logout();
    $this->get(route('client.products.show', $this->product))->assertSee('data-pb-checkout', false)->assertSee('Tecno Spark 20 Pro');
    ($this->order)()->assertCreated();

    ($this->toggle)(false)->assertOk()->assertJsonPath('message', fn ($m) => str_contains($m, 'page produit habituelle'));
    auth()->logout();
    $this->get(route('client.products.show', $this->product))->assertSee('id="ppForm"', false)->assertDontSee('data-pb-checkout', false);
    ($this->order)()->assertNotFound();

    // Le brouillon, la version publiée et l'historique sont gardés : réactiver remet la même page
    $page = ProductPage::where('product_id', $this->product->id)->first();
    expect($page->isPublished())->toBeTrue()->and($page->versions()->count())->toBe(1)->and(Order::count())->toBe(1);
    ($this->toggle)(true)->assertOk();
    auth()->logout();
    $this->get(route('client.products.show', $this->product))->assertSee('data-pb-checkout', false);
});

test('chaque produit a son propre interrupteur ; personne d\'autre ne peut le changer', function () {
    $second = F::product($this->shop, ['name' => 'Samsung Galaxy A15']);
    foreach ([$this->product, $second] as $p) {
        $this->actingAs($this->owner)->get(route('products.builder.edit', $p));
        $this->actingAs($this->owner)->postJson(route('products.builder.publish', $p))->assertOk();
    }
    ($this->toggle)(true)->assertOk();

    expect(ProductPage::where('product_id', $this->product->id)->value('is_enabled'))->toBeTrue()
        ->and(ProductPage::where('product_id', $second->id)->value('is_enabled'))->toBeFalse();
    auth()->logout();
    $this->get(route('client.products.show', $second))->assertSee('id="ppForm"', false);

    $intruder = User::find(F::shop()->user_id);
    $this->actingAs($intruder)->putJson(route('products.builder.toggle', $second), ['enabled' => true])->assertForbidden();
    expect(ProductPage::where('product_id', $second->id)->value('is_enabled'))->toBeFalse();
});
