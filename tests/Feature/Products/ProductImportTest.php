<?php

use App\Models\Product;
use App\Services\ProductImporter;
use App\Support\ProductPhotos;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CampaignFixtures as F;

/* « Importer les photos d'un produit » : lecture des pages (Shopify, JSON-LD, AliExpress, sécurité), photos trouvées,
   photos choisies ajoutées à la fiche produit habituelle. Aucun appel réseau réel, aucune IA. */

function impPng(int $w = 400): string
{
    $im = imagecreatetruecolor($w, $w);
    imagefill($im, 0, 0, imagecolorallocate($im, 30, 120, 200));
    ob_start(); imagepng($im); return (string) ob_get_clean();
}

/** Lecteur de pages dont les adresses IP sont fixées (pas de vraie résolution DNS pendant les tests). */
function impReader(array $ips = []): ProductImporter
{
    $r = new class ($ips) extends ProductImporter {
        public function __construct(private array $ips) {}
        protected function resolve(string $host): array { return $this->ips[$host] ?? ['93.184.216.34']; }
    };
    app()->instance(ProductImporter::class, $r);
    return $r;
}

beforeEach(function () {
    Storage::fake('public');
    $this->shop = F::shop();
    $this->owner = $this->shop->owner;
    $this->owner->forceFill(['shop_id' => $this->shop->id, 'role_in_shop' => 'admin'])->save();
    $this->pro = function () {
        $this->shop->forceFill(['plan' => 'pro', 'plan_expires_at' => now()->addMonth()])->save();
        $this->owner->unsetRelation('shop');
    };
    impReader();
});

/* ── Lecture des pages ── */

test('lecture : boutique Shopify, page avec données produit, balises de partage AliExpress', function () {
    $ld = '<html><body><h1>Montre</h1><script type="application/ld+json">' . json_encode(['@graph' => [['@type' => 'Product', 'name' => 'Montre dorée', 'description' => 'Acier.',
        'image' => ['/img/1.png', ['@type' => 'ImageObject', 'url' => 'https://cdn.youcan.shop/2.png']], 'offers' => [['price' => '299', 'priceCurrency' => 'MAD']]]]]) . '</script><p>Bracelet en acier inoxydable</p></body></html>';
    Http::fake([
        'boutique-ama.com/products/sac-wax.json' => Http::response(['product' => ['title' => 'Sac en wax', 'body_html' => '<p>Coton</p>', 'images' => [['src' => 'https://cdn.shopify.com/a.png']], 'variants' => [['price' => '45.00', 'title' => 'Rouge']]]]),
        'boutique-ama.com/products/sac-wax' => Http::response('<html><body>Chargement…</body></html>'),
        'store.youcan.shop/montre' => Http::response($ld),
        'fr.aliexpress.com/item/*' => Http::response('<html><head><meta property="og:title" content="Casque"><meta property="og:image" content="https://ae01.alicdn.com/kf/main.jpg"></head><body><script>{"imagePathList":["https://ae01.alicdn.com/kf/p1.jpg","https://ae01.alicdn.com/kf/p1.jpg_50x50.jpg"]}</script></body></html>'),
    ]);
    $r = app(ProductImporter::class);
    $shopify = $r->read('https://www.boutique-ama.com/products/sac-wax');
    expect($shopify['name'])->toBe('Sac en wax')->and($shopify['price'])->toBe('45.00')->and($shopify['text'])->toContain('Coton')->toContain('Rouge');
    $page = $r->read('https://store.youcan.shop/montre');
    expect($page['name'])->toBe('Montre dorée')->and($page['currency'])->toBe('MAD')->and($page['images'])->toContain('https://store.youcan.shop/img/1.png')
        ->and($page['text'])->toContain('Bracelet en acier inoxydable');
    $ali = $r->read('https://fr.aliexpress.com/item/1.html');
    expect($ali['name'])->toBe('Casque')->and($ali['images'])->toBe(['https://ae01.alicdn.com/kf/main.jpg', 'https://ae01.alicdn.com/kf/p1.jpg']);
});

test('lecture : adresses internes refusées (aussi après redirection), site qui bloque', function () {
    $r = impReader(['localhost' => ['127.0.0.1'], 'intranet.local' => ['10.0.0.5']]);
    Http::fake(['public-shop.com/*' => Http::response('', 302, ['Location' => 'http://intranet.local/admin']), 'www.alibaba.com/*' => Http::response('denied', 403)]);
    expect(fn () => $r->read('http://localhost/x'))->toThrow(RuntimeException::class, 'Lien non accepté.')
        ->and(fn () => $r->read('https://public-shop.com/p/1'))->toThrow(RuntimeException::class, 'Lien non accepté.')
        ->and(fn () => $r->read('https://example.com:8080/x'))->toThrow(RuntimeException::class)
        ->and(fn () => $r->read('https://www.alibaba.com/product-detail/x.html'))->toThrow(RuntimeException::class, 'bloque');
    Http::assertNotSent(fn ($q) => str_contains($q->url(), 'intranet.local') || str_contains($q->url(), 'localhost'));
    // Photos : les adresses internes sont ignorées, les petites images aussi
    Http::fake(['ae01.alicdn.com/kf/a.jpg' => Http::response(impPng()), 'ae01.alicdn.com/kf/tiny.jpg' => Http::response(impPng(40))]);
    $photos = $r->downloadPhotos(['https://ae01.alicdn.com/kf/a.jpg', 'http://intranet.local/x.png', 'https://ae01.alicdn.com/kf/tiny.jpg']);
    expect($photos)->toHaveCount(1)->and($photos[0]['path'])->toMatch(ProductPhotos::PATH);
});

/* ── Photos ── */

test('photos : le lien donne les photos de la page, sans texte ni prix ; ouvert à tous les plans', function () {
    Http::fake([
        'shop.example/*' => Http::response('<html><head><meta property="og:title" content="Casque"><meta property="og:image" content="https://cdn.example.com/a.png"><meta name="twitter:image" content="https://cdn.example.com/b.png"></head><body></body></html>'),
        'cdn.example.com/*' => Http::response(impPng()),
    ]);
    $j = $this->actingAs($this->owner)->postJson(route('products.import.photos'), ['url' => 'https://shop.example/casque'])->assertOk()->json();
    expect($j['token'])->toBeString()->and($j['photos'])->toHaveCount(2)->and($j['photos'][0]['path'])->toMatch(ProductPhotos::PATH)
        ->and($j)->not->toHaveKey('page')->not->toHaveKey('source');
});

test('photos : site qui bloque → message clair ; photos lues par le bouton des favoris acceptées, adresses internes ignorées', function () {
    impReader(['intranet.local' => ['10.0.0.5']]);
    Http::fake(['www.alibaba.com/*' => Http::response('denied', 403), 'ae01.alicdn.com/*' => Http::response(impPng())]);
    $url = route('products.import.photos');
    $this->actingAs($this->owner)->postJson($url, ['url' => 'https://www.alibaba.com/product-detail/x.html'])->assertStatus(422)->assertJson(['blocked' => true]);
    $j = $this->actingAs($this->owner)->postJson($url, ['url' => 'https://www.alibaba.com/product-detail/x.html',
        'images' => ['https://ae01.alicdn.com/kf/a.jpg', 'http://intranet.local/x.png', 'javascript:alert(1)']])->assertOk()->json();
    expect($j['photos'])->toHaveCount(1);
    Http::assertNotSent(fn ($q) => str_contains($q->url(), 'intranet.local'));
});

test('utiliser : droits confirmés, seules les photos trouvées ; la fiche produit s’ouvre avec elles, les autres sont effacées', function () {
    Http::fake(['shop.example/*' => Http::response('<html><head><meta property="og:image" content="https://cdn.example.com/a.png"><meta property="og:image" content="https://cdn.example.com/b.png"><meta name="twitter:image" content="https://cdn.example.com/c.png"></head></html>'),
        'cdn.example.com/*' => Http::response(impPng())]);
    $f = $this->actingAs($this->owner)->postJson(route('products.import.photos'), ['url' => 'https://shop.example/p'])->json();
    [$a, $b, $c] = array_column($f['photos'], 'path');
    $use = route('products.import.use');
    $this->actingAs($this->owner)->postJson($use, ['token' => $f['token'], 'photos' => [$b]])->assertJsonValidationErrors('droits');
    $j = $this->actingAs($this->owner)->postJson($use, ['token' => $f['token'], 'droits' => true, 'photos' => [$b, 'products/medium/AAAAAAAAAAAAAAAAAAAA.webp', $a]])->assertOk()->json();
    expect($j['redirect'])->toBe(route('products.create'))->and($j['photos'])->toBe(2);
    Storage::disk('public')->assertMissing($c);
    // Jeton utilisé une seule fois
    $this->actingAs($this->owner)->postJson($use, ['token' => $f['token'], 'droits' => true, 'photos' => [$a]])->assertStatus(410);
    // La fiche produit habituelle : $b en photo principale, $a dans la galerie ; rien n'est créé avant « Enregistrer »
    $html = $this->actingAs($this->owner)->get(route('products.create'))->assertOk()->assertSee('2 photos importées depuis le lien')->getContent();
    expect($html)->toContain(str_replace('/', '\/', $b))->toContain(str_replace('/', '\/', $a))->and(Product::count())->toBe(0);
    // Une seule fois : en revenant sur la fiche, plus de photos préremplies
    $this->actingAs($this->owner)->get(route('products.create'))->assertOk()->assertDontSee('photos importées depuis le lien');
});

test('écran : bouton d’import dans les produits, page sans IA ni prix, avec le bouton des favoris', function () {
    $this->actingAs($this->owner)->get(route('products.index'))->assertOk()->assertSee(route('products.import'));
    $html = $this->actingAs($this->owner)->get(route('products.import'))->assertOk()
        ->assertSee('Importer les photos d’un produit', false)->assertSee('Trouver les photos')->getContent();
    $page = substr($html, strpos($html, '<div class="ip">'), strpos($html, '</main>') - strpos($html, '<div class="ip">'));
    expect($page)->not->toContain('Shopio IA')->not->toContain('GNF')->not->toContain('Analyser');
    preg_match('#class="ip-bm" href="(javascript:[^"]+)"#', $html, $m);
    $js = rawurldecode(html_entity_decode(substr($m[1], strlen('javascript:'))));
    expect($js)->toContain(json_encode(route('products.import'), JSON_UNESCAPED_SLASHES))->not->toContain("\n");
});
