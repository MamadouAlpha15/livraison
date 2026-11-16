<?php

use App\Models\Product;
use App\Services\ProductImportAi;
use App\Services\ProductImporter;
use App\Support\ProductPhotos;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CampaignFixtures as F;

/* Importateur Produit Shopio : lecture des pages (Shopify, JSON-LD, AliExpress, sécurité), calcul du prix en GNF,
   analyse par Shopio IA (simulée) et création du produit. Aucun appel réseau ni IA réel. */

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

/** Shopio IA simulée : renvoie $answer et garde ce qu'elle a reçu. */
function impAi(array $answer): ArrayObject
{
    $seen = new ArrayObject();
    app()->instance(ProductImportAi::class, new class ($answer, $seen) extends ProductImportAi {
        public function __construct(private array $answer, private ArrayObject $seen) {}
        protected function ask(string $key, array $content, bool $useFetch): array
        {
            $this->seen['content'] = $content; $this->seen['fetch'] = $useFetch;
            return $this->answer;
        }
    });
    return $seen;
}

function impAnswer(array $over = []): array
{
    return $over + [
        'statut' => 'ok', 'message' => '',
        'source' => ['titre_origine' => '2024 Hot Sale Wireless Headphones', 'prix_fournisseur' => 12.5, 'devise' => 'USD', 'moq' => 2, 'fournisseur' => 'Shenzhen Audio'],
        'titre' => 'Casque Bluetooth sans fil', 'accroche' => 'Ta musique partout, sans fil.',
        'avantages_produit' => ['🎧 Bluetooth 5.3', '🔋 Batterie [À COMPLÉTER]', 'en trop'],
        'description' => 'Un casque sans fil pour écouter ta musique.',
        'caracteristiques' => [['nom' => 'Bluetooth', 'valeur' => '5.3'], ['nom' => 'Poids', 'valeur' => '[À COMPLÉTER]']],
        'variantes' => [['type' => 'Couleur', 'options' => ['Noir', 'Blanc']]],
        'contenu_colis' => ['1 casque', '1 câble USB-C'],
        'faq' => [['question' => 'Je paie quand ?', 'reponse' => 'À la livraison, en espèces.'], ['question' => 'Quel délai ?', 'reponse' => '[DÉLAI À COMPLÉTER]']],
        'categorie' => 'Électronique & Téléphones', 'conseil_photo' => 'Prends tes propres photos du produit reçu.',
        'photos' => ['https://ae01.alicdn.com/kf/a.jpg', 'https://ae01.alicdn.com/kf/b.jpg'], 'a_completer' => ['Le poids du casque'],
    ];
}

beforeEach(function () {
    Storage::fake('public');
    config(['services.anthropic.key' => 'sk-test']);
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

/* ── Prix en GNF : calculé par Shopio, jamais par l'IA ── */

test('prix : revient, vente arrondie aux 5 000 supérieurs, minimum privé aux 1 000 supérieurs', function () {
    $p = ProductImportAi::pricing(12.5, 'USD', 9000, 15000, 20, 40);
    expect($p['revient'])->toBe(150000)->and($p['vente'])->toBe(210000)->and($p['minimum'])->toBe(150000)->and($p['lignes'])->toHaveCount(3);
    $q = ProductImportAi::pricing(10, 'USD', 8650, 7000, 15, 35); // revient 106 475 ; vente 143 741,25
    expect($q['revient'])->toBe(106475)->and($q['vente'])->toBe(145000)->and($q['minimum'])->toBe(107000);
    // Taux absent : rien n'est deviné
    $m = ProductImportAi::pricing(12.5, 'USD', null, 15000, 20, 40);
    expect($m['vente'])->toBeNull()->and(implode(' ', $m['manque']))->toContain('[TAUX À COMPLÉTER]');
});

/* ── Analyse ── */

test('analyse : réservée au plan Pro ; page lue par Shopio puis envoyée à Shopio IA', function () {
    $url = route('products.import.analyze');
    $this->actingAs($this->owner)->postJson($url, ['url' => 'https://store.youcan.shop/casque'])->assertForbidden();
    ($this->pro)();
    Http::fake(['store.youcan.shop/*' => Http::response('<html><head><meta property="og:title" content="Wireless headphones"></head><body>' . str_repeat('Bluetooth 5.3 headphones. ', 20) . '</body></html>')]);
    $seen = impAi(impAnswer());
    $j = $this->actingAs($this->owner)->postJson($url, ['url' => 'https://store.youcan.shop/casque', 'ville' => 'Conakry'])->assertOk()->json();
    expect($j['statut'])->toBe('ok')->and($j['lecture'])->toBe('shopio')->and($j['token'])->toBeString()
        ->and($j['source']['prix_fournisseur'])->toEqual(12.5)->and($j['source']['moq'])->toBe(2)
        ->and($j['page']['avantages_produit'])->toHaveCount(2)->and($j['page']['titre'])->toBe('Casque Bluetooth sans fil')
        ->and($seen['fetch'])->toBeFalse();
    expect(end($seen['content'])['text'])->toContain('Page lue par Shopio')->toContain('Bluetooth 5.3 headphones')->toContain('Ville de livraison du vendeur : Conakry');
});

test('analyse : site qui bloque Shopio → Claude ouvre le lien lui-même ; texte copié et captures acceptés', function () {
    ($this->pro)();
    Http::fake(['www.alibaba.com/*' => Http::response('denied', 403)]);
    $seen = impAi(impAnswer());
    $this->actingAs($this->owner)->postJson(route('products.import.analyze'), ['url' => 'https://www.alibaba.com/product-detail/x.html'])->assertOk();
    expect($seen['fetch'])->toBeTrue()->and(end($seen['content'])['text'])->toContain('web_fetch');

    $shot = \Illuminate\Http\UploadedFile::fake()->image('capture.png', 400, 800);
    $j = $this->actingAs($this->owner)->post(route('products.import.analyze'), ['url' => 'https://www.alibaba.com/product-detail/x.html', 'text' => 'Wireless headphones US $12.50', 'shots' => [$shot]], ['Accept' => 'application/json'])
        ->assertOk()->json();
    expect($j['lecture'])->toBe('captures')->and($seen['fetch'])->toBeFalse()
        ->and($seen['content'][0]['type'])->toBe('image')->and(end($seen['content'])['text'])->toContain('Wireless headphones US $12.50');
});

test('analyse : produit refusé, page illisible ou pas un produit → message clair et aucune création possible', function () {
    ($this->pro)();
    Http::fake(['shop.example/*' => Http::response('<html><head><meta property="og:title" content="Sac"></head><body>' . str_repeat('x ', 200) . '</body></html>')]);
    impAi(['statut' => 'refuse', 'message' => 'C’est une réplique de marque : je ne peux pas créer cette page.']);
    $j = $this->actingAs($this->owner)->postJson(route('products.import.analyze'), ['url' => 'https://shop.example/sac'])->assertOk()->json();
    expect($j['statut'])->toBe('refuse')->and($j['message'])->toContain('réplique');
    $this->actingAs($this->owner)->postJson(route('products.import.create'), ['token' => $j['token'], 'droits' => true, 'titre' => 'Sac', 'prix' => 100000, 'stock' => 3])
        ->assertStatus(422);
    impAi(['statut' => 'illisible']);
    expect($this->actingAs($this->owner)->postJson(route('products.import.analyze'), ['url' => 'https://shop.example/sac'])->json('message'))
        ->toBe('Je n’arrive pas à lire cette page. Copie-colle le titre, la description et le prix, ou envoie des captures d’écran.');
});

/* ── Création ── */

test('création : prix recalculé, minimum privé, droits confirmés, rien « À COMPLÉTER » chez le client', function () {
    ($this->pro)();
    Http::fake([
        'shop.example/*' => Http::response('<html><head><meta property="og:title" content="Casque"></head><body>' . str_repeat('casque ', 100) . '</body></html>'),
        'ae01.alicdn.com/*' => Http::response(impPng()),
    ]);
    impAi(impAnswer());
    $token = $this->actingAs($this->owner)->postJson(route('products.import.analyze'), ['url' => 'https://shop.example/casque'])->json('token');
    $create = route('products.import.create');
    $body = ['token' => $token, 'titre' => 'Casque Bluetooth sans fil', 'prix' => 210000, 'stock' => 5, 'ville' => 'Conakry',
        'taux' => 9000, 'transport' => 15000, 'douane' => 20, 'marge' => 40, 'photos' => ['https://ae01.alicdn.com/kf/a.jpg', 'https://evil.example/x.jpg']];

    $this->actingAs($this->owner)->postJson($create, $body)->assertStatus(422)->assertJsonValidationErrors('droits');
    $this->actingAs($this->owner)->postJson($create, ['droits' => true] + array_diff_key($body, ['stock' => 1]))->assertJsonValidationErrors('stock');

    $j = $this->actingAs($this->owner)->postJson($create, $body + ['droits' => true])->assertCreated()->json();
    $p = Product::find($j['id']);
    expect($p->is_active)->toBeFalse()->and((int) $p->price)->toBe(210000)->and((int) $p->min_price)->toBe(150000)
        ->and($p->category)->toBe('Électronique & Téléphones')->and($p->image)->toMatch(ProductPhotos::PATH)->and($p->gallery_array)->toBe([])
        ->and($p->landing['benefits'])->toBe(['🎧 Bluetooth 5.3', '🚚 Livraison à Conakry', ProductImportAi::PAYMENT_BENEFIT])
        ->and($p->landing['details'])->toContain('Bluetooth : 5.3')->toContain('Couleur : Noir, Blanc')->toContain('1 câble USB-C')->not->toContain('COMPLÉTER')
        ->and($p->landing['faq'])->toBe([['q' => 'Je paie quand ?', 'a' => 'À la livraison, en espèces.']])
        ->and($p->landing['source']['prix_fournisseur'])->toEqual(12.5)->and($p->landing['source']['droits_confirmes'])->toBeTrue()
        ->and($j['redirect'])->toContain('page-de-vente');
    Http::assertNotSent(fn ($q) => str_contains($q->url(), 'evil.example'));
    // Analyse utilisée une seule fois
    $this->actingAs($this->owner)->postJson($create, $body + ['droits' => true])->assertStatus(410);
});

test('écran : bouton d’import dans les produits, importateur avec le bouton des favoris', function () {
    ($this->pro)();
    $this->actingAs($this->owner)->get(route('products.index'))->assertOk()->assertSee(route('products.import'));
    $html = $this->actingAs($this->owner)->get(route('products.import'))->assertOk()
        ->assertSee('Importer un produit depuis un lien')->assertSee('📥 Importer dans Shopio')->getContent();
    preg_match('#class="ip-bm" href="(javascript:[^"]+)"#', $html, $m);
    $js = rawurldecode(html_entity_decode(substr($m[1], strlen('javascript:'))));
    expect($js)->toContain(json_encode(route('products.import'), JSON_UNESCAPED_SLASHES))->toContain('innerText')->not->toContain("\n");
    $this->actingAs($this->owner)->get(route('products.wizard'))->assertOk()->assertSee(route('products.import'));
});
