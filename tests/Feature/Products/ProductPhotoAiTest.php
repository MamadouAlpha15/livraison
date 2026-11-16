<?php

use App\Support\ProductPhotos;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CampaignFixtures as F;

/* Photos pro du produit (Shopio IA) : fond blanc studio ou mise en scène, depuis la vraie photo ; OpenAI simulé. */

function ppPng(): string
{
    $im = imagecreatetruecolor(300, 300);
    imagefill($im, 0, 0, imagecolorallocate($im, 220, 90, 30));
    ob_start(); imagepng($im); return (string) ob_get_clean();
}

beforeEach(function () {
    config(['filesystems.default' => 'public', 'services.openai.key' => 'sk-test', 'services.anthropic.key' => '']);
    Storage::fake('public');
    $this->shop = F::shop();
    $this->owner = $this->shop->owner;
    $this->owner->forceFill(['shop_id' => $this->shop->id, 'role_in_shop' => 'admin'])->save();
    $this->real = 'products/medium/' . str_repeat('r', 20) . '.webp';
    Storage::disk('public')->put($this->real, ppPng());
    $this->product = F::product($this->shop, ['name' => 'Sac en wax', 'image' => $this->real]);
    $this->pro = function () {
        $this->shop->forceFill(['plan' => 'pro', 'plan_expires_at' => now()->addMonth()])->save();
        $this->owner->unsetRelation('shop');
    };
    Http::fake(['api.openai.com/v1/images/edits' => Http::response(['data' => [['b64_json' => base64_encode(ppPng())]]])]);
});

test('création d’un produit : photo studio à partir de la vraie photo envoyée', function () {
    $url = route('products.wizard.photo');
    $this->actingAs($this->owner)->postJson($url, ['source' => $this->real, 'style' => 'studio'])->assertForbidden();
    ($this->pro)();
    $j = $this->actingAs($this->owner)->postJson($url, ['source' => $this->real, 'style' => 'studio'])->assertOk()->json();
    expect($j['path'])->toMatch(ProductPhotos::PATH)->and($j['path'])->toStartWith('products/medium/');
    Http::assertSent(function ($r) {
        $parts = collect($r->data())->keyBy('name');
        return str_ends_with($r->url(), '/images/edits') && str_contains($parts['prompt']['contents'], 'Pure white')
            && str_contains($parts['prompt']['contents'], 'exactly identical') && $parts['size']['contents'] === '1024x1024';
    });
    // Seulement une vraie photo produit déjà envoyée, et un style connu
    $this->actingAs($this->owner)->postJson($url, ['source' => 'covers/medium/' . str_repeat('x', 20) . '.webp', 'style' => 'studio'])->assertStatus(422);
    $this->actingAs($this->owner)->postJson($url, ['source' => $this->real, 'style' => 'avatar'])->assertStatus(422);
    $this->actingAs($this->owner)->postJson($url, ['source' => 'products/medium/' . str_repeat('z', 20) . '.webp', 'style' => 'scene'])->assertStatus(422);
});

test('galerie : la photo créée n’est ajoutée qu’avec l’accord du vendeur', function () {
    ($this->pro)();
    $j = $this->actingAs($this->owner)->postJson(route('products.photos.ai', $this->product), ['style' => 'scene', 'wish' => 'salon moderne'])->assertOk()->json();
    expect($j['path'])->toStartWith('products/gallery/medium/');
    expect($this->product->fresh()->image)->toBe($this->real); // rien n'a changé tant que le vendeur n'a pas choisi

    $this->actingAs($this->owner)->postJson(route('products.photos.attach', $this->product), ['path' => $j['path'], 'main' => true])
        ->assertOk()->assertJsonCount(2, 'photos');
    $p = $this->product->fresh();
    expect($p->image)->toBe($j['path'])->and($p->gallery_array)->toBe([$this->real]);
});

test('galerie : impossible d’ajouter une photo qui n’a pas été créée ici, ou sur le produit d’une autre boutique', function () {
    ($this->pro)();
    $this->actingAs($this->owner)->postJson(route('products.photos.attach', $this->product), ['path' => 'products/gallery/medium/' . str_repeat('q', 20) . '.webp'])
        ->assertStatus(422);
    $other = F::product(F::shop(), ['image' => $this->real]);
    $this->actingAs($this->owner)->postJson(route('products.photos.ai', $other), ['style' => 'studio'])->assertForbidden();
    $this->actingAs($this->owner)->postJson(route('products.photos.attach', $other), ['path' => $this->real])->assertForbidden();
});

test('l’éditeur produit propose les photos pro', function () {
    ($this->pro)();
    $this->actingAs($this->owner)->get(route('products.page.edit', $this->product))->assertOk()
        ->assertSee('photos-ia', false)->assertSee('photos-ia\/ajouter', false);
});
