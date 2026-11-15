<?php

use App\Models\ProductPage;
use App\Models\User;
use App\PageBuilder\Ai\PageWriter;
use App\PageBuilder\PageFactory;
use App\Support\Ai\AiUnavailable;
use App\Support\Ai\TextModel;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->shop = F::shop();
    $this->owner = User::find($this->shop->user_id);
    $this->owner->forceFill(['shop_id' => $this->shop->id])->save();
    $this->product = F::product($this->shop, ['name' => 'Beurre de karité pur', 'price' => 45000, 'description' => 'Karité brut de Kankan, pot de 250 g. Pour la peau et les cheveux.']);
});

/** Modèle d'IA de test : renvoie la réponse donnée et garde ce qu'on lui a demandé */
function fakeAi(string|Throwable $reply): object
{
    $fake = new class($reply) implements TextModel {
        public array $calls = [];
        public function __construct(private string|Throwable $reply) {}
        public function complete(string $system, array $messages, int $maxTokens = 1500): string
        {
            $this->calls[] = compact('system', 'messages');
            if ($this->reply instanceof Throwable) {
                throw $this->reply;
            }
            return $this->reply;
        }
    };
    app()->instance(TextModel::class, $fake);
    return $fake;
}

function aiReply(array $over = []): string
{
    return 'Voici : ' . json_encode($over + [
        'seo_title'       => 'Beurre de karité pur de Kankan, livré à Conakry',
        'seo_description' => 'Karité brut en pot de 250 g pour la peau et les cheveux. Paiement à la livraison.',
        'announcement'    => 'Paiement à la livraison partout à Conakry',
        'headline'        => 'Le karité brut, pour ta peau et tes cheveux',
        'intro'           => 'Un karité brut de Kankan, en pot de 250 g. Tu l\'utilises pour la peau et les cheveux.',
        'benefits'        => ['Karité brut de Kankan', 'Pot de 250 g', 'Pour la peau et les cheveux'],
        'faq'             => [
            ['q' => 'Dois-je payer avant la livraison ?', 'a' => 'Non, tu paies en espèces à la livraison.'],
            ['q' => 'Quelle est la taille du pot ?', 'a' => 'Le pot fait 250 g.'],
        ],
    ], JSON_UNESCAPED_UNICODE) . ' Bonne vente !';
}

test('Shopio IA écrit la page dans le brouillon, à partir de la seule fiche produit', function () {
    $ai = fakeAi(aiReply());
    $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product));
    [$page] = PageFactory::addSection(ProductPage::where('product_id', $this->product->id)->first()->draft, 'faq');
    ProductPage::where('product_id', $this->product->id)->first()->update(['draft' => $page]);

    $r = $this->actingAs($this->owner)->postJson(route('products.builder.ai', $this->product))->assertOk()
        ->assertJsonPath('message', fn ($m) => str_contains($m, 'relis-la'));
    $draft = ProductPage::where('product_id', $this->product->id)->first()->draft;

    expect($draft['order'][0])->toBe(PageWriter::ANNOUNCEMENT)
        ->and($draft['order'][1])->toBe('main')
        ->and($draft['order'][2])->toBe(PageWriter::STORY)
        ->and($draft['sections'][PageWriter::ANNOUNCEMENT]['settings']['text'])->toBe('Paiement à la livraison partout à Conakry')
        ->and($draft['sections'][PageWriter::STORY]['settings']['content'])->toContain('<li>Pot de 250 g</li>')
        ->and($draft['seo']['title'])->toBe('Beurre de karité pur de Kankan, livré à Conakry')
        ->and($r->json('page.seo.title'))->toBe('Beurre de karité pur de Kankan, livré à Conakry');

    // La FAQ existante est complétée sans doublon (« Dois-je payer… » y était déjà)
    $faq = collect($draft['sections'])->where('type', 'faq');
    expect($faq)->toHaveCount(1)
        ->and(collect($faq->first()['blocks'])->pluck('settings.question')->all())
        ->toBe(['Quand vais-je recevoir ma commande ?', 'Dois-je payer avant la livraison ?', 'Quelle est la taille du pot ?']);

    // Ce que l'IA a reçu : la fiche produit et la consigne de ne rien inventer
    expect($ai->calls[0]['messages'][0]['content'])->toContain('Beurre de karité pur')->toContain('Kankan, pot de 250 g')->toContain('45 000 GNF')
        ->and($ai->calls[0]['system'])->toContain('N\'invente aucun fait');

    // Relancer remplace la proposition au lieu d'empiler les sections
    $this->actingAs($this->owner)->postJson(route('products.builder.ai', $this->product))->assertOk();
    $draft = ProductPage::where('product_id', $this->product->id)->first()->draft;
    expect(collect($draft['sections'])->where('type', 'announcement'))->toHaveCount(1)
        ->and(collect($draft['sections'])->where('type', 'rich-text'))->toHaveCount(1)
        ->and(collect($draft['sections'])->where('type', 'faq')->first()['block_order'])->toHaveCount(3)
        ->and(ProductPage::where('product_id', $this->product->id)->first()->isPublished())->toBeFalse();
});

test('Shopio IA : texte nettoyé et longueurs respectées', function () {
    fakeAi(aiReply([
        'seo_title' => str_repeat('Karité ', 30),
        'intro'     => 'Super <script>alert(1)</script> karité <b>bio</b>.',
        'benefits'  => ['<img src=x onerror=alert(1)>Doux', '', 'Naturel'],
    ]));
    $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product));
    $this->actingAs($this->owner)->postJson(route('products.builder.ai', $this->product))->assertOk();

    $draft = ProductPage::where('product_id', $this->product->id)->first()->draft;
    $content = $draft['sections'][PageWriter::STORY]['settings']['content'];
    expect(mb_strlen($draft['seo']['title']))->toBeLessThanOrEqual(70)
        ->and($content)->not->toContain('<script')->not->toContain('onerror')->not->toContain('<img')
        ->toContain('<li>Doux</li>')->toContain('<li>Naturel</li>');
});

test('Shopio IA indisponible, réponse illisible ou quota atteint : message clair, brouillon intact', function () {
    $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product));
    $before = ProductPage::where('product_id', $this->product->id)->first()->draft;

    fakeAi(new AiUnavailable('Shopio IA n\'est pas disponible pour le moment.'));
    $this->actingAs($this->owner)->postJson(route('products.builder.ai', $this->product))->assertStatus(503)
        ->assertJsonPath('message', 'Shopio IA n\'est pas disponible pour le moment.');

    fakeAi('Désolé, je ne peux pas.');
    $this->actingAs($this->owner)->postJson(route('products.builder.ai', $this->product))->assertStatus(503)
        ->assertJsonPath('message', fn ($m) => str_contains($m, 'format inattendu'));

    fakeAi(aiReply(['headline' => '', 'intro' => '']));
    $this->actingAs($this->owner)->postJson(route('products.builder.ai', $this->product))->assertStatus(503);

    expect(ProductPage::where('product_id', $this->product->id)->first()->draft)->toBe($before);

    Cache::put('page-builder:ai:' . $this->shop->id . ':' . now()->toDateString(), PageWriter::DAILY_LIMIT, 3600);
    fakeAi(aiReply());
    $this->actingAs($this->owner)->postJson(route('products.builder.ai', $this->product))->assertStatus(429);

    $intruder = User::find(F::shop()->user_id);
    $this->actingAs($intruder)->postJson(route('products.builder.ai', $this->product))->assertForbidden();
});
