<?php

use App\Support\Storefront;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CampaignFixtures as F;

/* Photos Shopio IA des sections : vraie photo du produit mise en scène, ou décor seul ; plan Pro ; OpenAI simulé. */

function sxPng(): string
{
    $im = imagecreatetruecolor(64, 64);
    imagefill($im, 0, 0, imagecolorallocate($im, 200, 120, 40));
    ob_start(); imagepng($im); return (string) ob_get_clean();
}

beforeEach(function () {
    config(['filesystems.default' => 'public', 'services.openai.key' => 'sk-test', 'services.openai.image_model' => 'gpt-image-1']);
    Storage::fake('public');
    $this->shop = F::shop();
    $this->shop->update(['slug' => 'chez-fanta', 'currency' => 'GNF']);
    $this->owner = $this->shop->owner;
    $this->owner->forceFill(['shop_id' => $this->shop->id, 'role_in_shop' => 'admin'])->save();
    Storage::disk('public')->put('products/medium/sac.png', sxPng());
    $this->product = F::product($this->shop, ['name' => 'Sac en wax', 'image' => 'products/medium/sac.png']);
    $this->pro = function () {
        $this->shop->forceFill(['plan' => 'pro', 'plan_expires_at' => now()->addMonth()])->save();
        $this->owner->unsetRelation('shop');
    };
    $this->ok = ['data' => [['b64_json' => base64_encode(sxPng())]]];
});

test('réservé au plan Pro, sans appel au service de photos', function () {
    Http::fake();
    $this->actingAs($this->owner)->postJson(route('boutique.storefront.sections.image'), ['type' => 'slideshow'])->assertForbidden();
    Http::assertNothingSent();
});

test('décor seul : la photo est créée sans produit puis rangée comme une photo importée', function () {
    ($this->pro)();
    Http::fake(['api.openai.com/v1/images/generations' => Http::response($this->ok)]);
    $j = $this->actingAs($this->owner)->postJson(route('boutique.storefront.sections.image'), ['type' => 'slideshow', 'wish' => 'marché coloré'])
        ->assertOk()->json();
    expect($j['path'])->toMatch(Storefront::COVER_PATH);
    Storage::disk('public')->assertExists(str_replace('/medium/', '/large/', $j['path']));
    Http::assertSent(fn ($r) => str_ends_with($r->url(), '/images/generations')
        && $r['size'] === '1536x1024' && str_contains($r['prompt'], 'NO product') && str_contains($r['prompt'], 'marché coloré'));
});

test('mise en scène : la vraie photo du produit est envoyée, seul le décor change', function () {
    ($this->pro)();
    Http::fake(['api.openai.com/v1/images/edits' => Http::response($this->ok)]);
    $this->actingAs($this->owner)->postJson(route('products.page.section-image', $this->product), ['type' => 'imgtext', 'product_id' => $this->product->id])
        ->assertOk()->assertJsonStructure(['path', 'url']);
    Http::assertSent(function ($r) {
        $parts = collect($r->data())->keyBy('name');
        return str_ends_with($r->url(), '/images/edits') && $r->isMultipart()
            && $parts->has('image') && str_contains($parts['prompt']['contents'], 'exactly identical') && $parts['size']['contents'] === '1024x1024';
    });
});

test('Claude dirige : sa description du produit et du décor est celle que dessine OpenAI', function () {
    ($this->pro)();
    $seen = new ArrayObject();
    app()->instance(\App\Services\SectionImageAi::class, new class ($seen) extends \App\Services\SectionImageAi {
        public function __construct(private ArrayObject $seen) {}
        protected function direct(\App\Models\Shop $shop, string $type, ?\App\Models\Product $product, ?array $photo, string $wish): array
        {
            $this->seen['photo'] = $photo !== null; $this->seen['wish'] = $wish;
            return ['product' => 'an orange wax handbag with a gold clasp', 'scene' => 'a sunny wooden table in a modern Conakry living room'];
        }
    });
    Http::fake(['api.openai.com/v1/images/edits' => Http::response($this->ok)]);
    $this->actingAs($this->owner)->postJson(route('products.page.section-image', $this->product), ['type' => 'imgtext', 'product_id' => $this->product->id, 'wish' => 'ambiance Tabaski'])
        ->assertOk();
    expect($seen['photo'])->toBeTrue()->and($seen['wish'])->toBe('ambiance Tabaski');
    Http::assertSent(function ($r) {
        $prompt = collect($r->data())->keyBy('name')['prompt']['contents'];
        return str_contains($prompt, 'orange wax handbag') && str_contains($prompt, 'modern Conakry living room') && str_contains($prompt, 'exactly identical');
    });
});

test('demande vérifiée : section sans photo, produit d’une autre boutique, autre produit', function () {
    ($this->pro)();
    Http::fake();
    $url = route('boutique.storefront.sections.image');
    $this->actingAs($this->owner)->postJson($url, ['type' => 'text'])->assertStatus(422);
    $this->actingAs($this->owner)->postJson($url, ['type' => 'imgtext', 'product_id' => F::product(F::shop())->id])->assertStatus(422);
    // Page d'un produit d'une autre boutique : refusée
    $this->actingAs($this->owner)->postJson(route('products.page.section-image', F::product(F::shop())), ['type' => 'imgtext'])->assertForbidden();
    Http::assertNothingSent();
});

test('messages clairs : demande refusée par le service, clé manquante, produit sans photo', function () {
    ($this->pro)();
    Http::fake(['api.openai.com/*' => Http::response(['error' => ['code' => 'moderation_blocked', 'message' => 'blocked', 'type' => 'image_generation_user_error']], 400)]);
    $url = route('boutique.storefront.sections.image');
    $this->actingAs($this->owner)->postJson($url, ['type' => 'hero'])->assertStatus(502)->assertJsonPath('error', fn ($e) => str_contains($e, 'refusé'));

    $nophoto = F::product($this->shop, ['name' => 'Sans photo']);
    $this->actingAs($this->owner)->postJson($url, ['type' => 'hero', 'product_id' => $nophoto->id])->assertStatus(502)->assertJsonPath('error', fn ($e) => str_contains($e, 'Décor seul'));

    config(['services.openai.key' => '']);
    $this->actingAs($this->owner)->postJson($url, ['type' => 'hero'])->assertStatus(502)->assertJsonPath('error', fn ($e) => str_contains($e, 'pas configurée'));
});

test('les éditeurs proposent « Créer la photo » avec les produits à mettre en scène', function () {
    ($this->pro)();
    $this->actingAs($this->owner)->get(route('boutique.storefront.sections'))->assertOk()
        ->assertSee('photo-ia', false)->assertSee('"aiShots":[{"id":' . $this->product->id, false);
    $this->actingAs($this->owner)->get(route('products.page.edit', $this->product))->assertOk()
        ->assertSee('page-de-vente\/photo-ia', false)->assertSee('"aiImageTypes":["imgtext","slideshow","hero"]', false);
});
