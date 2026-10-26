<?php

use App\Models\AdCampaign as C;
use App\Models\AdCampaignStat;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShopMessage;
use App\Models\User;
use App\Support\Campaigns\AdAttribution;
use App\Support\Campaigns\CampaignTracker;
use App\Support\Campaigns\SponsoredPlacements;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CampaignFixtures as F;

beforeEach(fn () => SponsoredPlacements::reset());

/** Page de résultats « organiques » : produits d'une autre boutique, sans campagne. */
function organicPage(string $category, int $count = 10): LengthAwarePaginator
{
    $shop = F::shop();
    $items = collect(range(1, $count))->map(fn () => F::product($shop, ['category' => $category]));
    return new LengthAwarePaginator($items, $count, 24);
}

test('les boutiques à la une sont celles dont la campagne est en cours', function () {
    $running = F::shop();
    $unpaid = F::shop();
    F::product($running);
    $campaign = F::campaign($running, 'boutique_une', [], ['status' => C::STATUS_EN_COURS]);
    F::campaign($unpaid, 'boutique_une');

    $shops = SponsoredPlacements::featuredShops();

    expect($shops->pluck('id')->all())->toBe([$running->id])
        ->and($shops->first()->sponsored_campaign_id)->toBe($campaign->id)
        ->and($shops->first()->products_count)->toBe(1)
        ->and(CampaignTracker::count($campaign->id, 'impressions'))->toBe(1);
});

test('dans une catégorie, le top catégorie passe en tête et le boost est inséré', function () {
    $shop = F::shop();
    $topProduct = F::product($shop);
    $boosted = F::product($shop);
    $top = F::campaign($shop, 'top_categorie', [$topProduct->id], ['status' => C::STATUS_EN_COURS]);
    $boost = F::campaign($shop, 'boost_produit', [$boosted->id], ['status' => C::STATUS_EN_COURS]);

    $page = SponsoredPlacements::blend(organicPage('Beauté & Cosmétiques'), null, 'Beauté & Cosmétiques');
    $items = $page->getCollection();

    expect($items->first()->id)->toBe($topProduct->id)
        ->and($items->first()->sponsored_campaign_id)->toBe($top->id)
        ->and($items->pluck('id'))->toContain($boosted->id)
        ->and($items->firstWhere('id', $boosted->id)->sponsored_campaign_id)->toBe($boost->id)
        ->and($items)->toHaveCount(12)
        ->and(CampaignTracker::count($top->id, 'impressions'))->toBe(1)
        ->and(CampaignTracker::count($boost->id, 'impressions'))->toBe(1);
});

test('une autre catégorie, une recherche sans rapport ou une campagne non lancée ne montrent rien', function () {
    $shop = F::shop();
    $boosted = F::product($shop, ['name' => 'Crème hydratante karité']);
    $paused = F::product($shop, ['name' => 'Savon noir']);
    F::campaign($shop, 'boost_produit', [$boosted->id], ['status' => C::STATUS_EN_COURS]);
    F::campaign($shop, 'boost_produit', [$paused->id], ['status' => C::STATUS_EN_PAUSE]);

    $other = SponsoredPlacements::blend(organicPage('Mode & Vêtements', 3), null, 'Mode & Vêtements');
    $search = SponsoredPlacements::blend(organicPage('Beauté & Cosmétiques', 3), 'téléphone');
    $match = SponsoredPlacements::blend(organicPage('Beauté & Cosmétiques', 3), 'karité');

    expect($other->getCollection()->whereNotNull('sponsored_campaign_id'))->toBeEmpty()
        ->and($search->getCollection()->whereNotNull('sponsored_campaign_id'))->toBeEmpty()
        ->and($match->getCollection()->pluck('id'))->toContain($boosted->id)->not->toContain($paused->id);
});

test('un produit en rupture n\'est plus mis en avant', function () {
    $shop = F::shop();
    $product = F::product($shop);
    F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);
    $product->forceFill(['stock' => 0])->save();

    $page = SponsoredPlacements::blend(organicPage('Beauté & Cosmétiques', 3), null, 'Beauté & Cosmétiques');

    expect($page->getCollection()->pluck('id'))->not->toContain($product->id);
});

test('le site affiche le badge Sponsorisé et le lien de clic', function () {
    $shop = F::shop();
    $product = F::product($shop, ['name' => 'Parfum boisé sponsorisé']);
    $campaign = F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);

    $this->get('/?cat=' . urlencode('Beauté & Cosmétiques'))
        ->assertOk()
        ->assertSee('Parfum boisé sponsorisé')
        ->assertSee('Sponsorisé')
        ->assertSee('c=' . $campaign->id, false);
});

test('un clic ?c= est compté une fois par jour et attribue la commande sur le site', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $campaign = F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);
    $this->actingAs(User::factory()->create(['role' => 'client']));

    $response = $this->get('/?c=' . $campaign->id)->assertOk()->assertCookie(AdAttribution::COOKIE);
    $this->get('/?c=' . $campaign->id)->assertOk();
    expect(CampaignTracker::count($campaign->id, 'clicks'))->toBe(1);

    // Le cookie (chiffré) revient avec la requête suivante : la commande est attribuée
    $cookie = $response->getCookie(AdAttribution::COOKIE, false);
    expect(AdAttribution::campaignForOrder($shop->id, null, decryptedCookieRequest($cookie->getValue())))->toBe($campaign->id)
        ->and(AdAttribution::campaignForOrder(F::shop()->id, null, decryptedCookieRequest($cookie->getValue())))->toBeNull();
});

/** Requête dont le cookie d'attribution est déchiffré comme le ferait le middleware EncryptCookies. */
function decryptedCookieRequest(string $encrypted): Illuminate\Http\Request
{
    $value = Illuminate\Cookie\CookieValuePrefix::validate(
        AdAttribution::COOKIE,
        app('encrypter')->decrypt($encrypted, false),
        app('encrypter')->getAllKeys()
    );
    return Illuminate\Http\Request::create('/', 'GET', [], [AdAttribution::COOKIE => $value]);
}

test('un lien de campagne non lancée ou inexistante ne compte rien', function () {
    $shop = F::shop();
    $campaign = F::campaign($shop, 'boutique_une'); // à payer

    $this->get('/?c=' . $campaign->id)->assertOk()->assertCookieMissing(AdAttribution::COOKIE);
    $this->get('/?c=999999')->assertOk();

    expect(CampaignTracker::count($campaign->id, 'clicks'))->toBe(0);
});

test('dans l\'app, le clic attribue la commande du panier pendant 7 jours', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $campaign = F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);
    $client = User::factory()->create(['role' => 'client']);
    Sanctum::actingAs($client);

    $this->getJson('/api/v1/products/' . $product->id . '?c=' . $campaign->id)->assertOk();
    expect(CampaignTracker::count($campaign->id, 'clicks'))->toBe(1);

    CartItem::create(['user_id' => $client->id, 'product_id' => $product->id, 'quantity' => 1]);
    $this->postJson('/api/v1/cart/checkout', ['delivery_destination' => 'Kaloum, Conakry', 'client_phone' => '622 45 81 37'])
        ->assertCreated();

    expect(Order::where('user_id', $client->id)->value('ad_campaign_id'))->toBe($campaign->id);

    // Après 7 jours, plus d'attribution
    $this->travel(8)->days();
    expect(AdAttribution::campaignForOrder($shop->id, $client))->toBeNull();
});

test('une commande sans clic n\'est attribuée à aucune campagne', function () {
    $shop = F::shop();
    $product = F::product($shop);
    F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);
    $client = User::factory()->create(['role' => 'client']);
    Sanctum::actingAs($client);

    CartItem::create(['user_id' => $client->id, 'product_id' => $product->id, 'quantity' => 1]);
    $this->postJson('/api/v1/cart/checkout', ['delivery_destination' => 'Kaloum, Conakry', 'client_phone' => '622 45 81 37'])
        ->assertCreated();

    expect(Order::where('user_id', $client->id)->value('ad_campaign_id'))->toBeNull();
});

test('le premier message d\'un client après un clic compte pour la campagne', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $campaign = F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);
    $client = User::factory()->create(['role' => 'client']);
    $this->actingAs($client);
    AdAttribution::registerClick($campaign->id, request(), $client);

    foreach (['Bonjour, il est disponible ?', 'Et en rouge ?'] as $body) {
        ShopMessage::create(['shop_id' => $shop->id, 'product_id' => $product->id, 'sender_id' => $client->id,
            'receiver_id' => $shop->user_id, 'body' => $body, 'type' => ShopMessage::TYPE_TEXT]);
    }

    expect(CampaignTracker::count($campaign->id, 'messages'))->toBe(1);
});

test('le flush recopie les compteurs et peut être relancé sans doubler', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $campaign = F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);
    CampaignTracker::impressions([$campaign->id, $campaign->id, $campaign->id]);
    CampaignTracker::click($campaign->id);

    $this->artisan('campaigns:flush-stats')->assertSuccessful();
    $this->artisan('campaigns:flush-stats')->assertSuccessful();

    $row = AdCampaignStat::where('ad_campaign_id', $campaign->id)->where('day', today()->toDateString())->sole();
    expect($row->source)->toBe('auto')
        ->and((int) $row->impressions)->toBe(3)
        ->and((int) $row->clicks)->toBe(1);
});

test('l\'API renvoie les boutiques à la une et le champ sponsored_campaign_id', function () {
    $shop = F::shop();
    $product = F::product($shop);
    $featured = F::campaign($shop, 'boutique_une', [], ['status' => C::STATUS_EN_COURS]);
    $boost = F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);

    $this->getJson('/api/v1/home')->assertOk()
        ->assertJsonPath('data.featured_shops.0.id', $shop->id)
        ->assertJsonPath('data.featured_shops.0.sponsored_campaign_id', $featured->id);

    $list = $this->getJson('/api/v1/products?cat=' . urlencode('Beauté & Cosmétiques'))->assertOk()->json('data');
    expect(collect($list)->firstWhere('id', $product->id)['sponsored_campaign_id'] ?? null)->toBe($boost->id);
});

test('les pages client affichent les boutiques à la une et les produits sponsorisés', function () {
    $shop = F::shop();
    $product = F::product($shop, ['name' => 'Huile de coco sponsorisée']);
    $featured = F::campaign($shop, 'boutique_une', [], ['status' => C::STATUS_EN_COURS]);
    $boost = F::campaign($shop, 'boost_produit', [$product->id], ['status' => C::STATUS_EN_COURS]);
    $client = User::factory()->create(['role' => 'client']);
    $cat = urlencode('Beauté & Cosmétiques');

    $this->actingAs($client)->get(route('client.dashboard') . '?cat=' . $cat)->assertOk()
        ->assertSee('à la une', false)->assertSee('c=' . $featured->id, false)->assertSee('c=' . $boost->id, false);
    $this->actingAs($client)->get(route('client.products.index') . '?cat=' . $cat)->assertOk()
        ->assertSee('Huile de coco sponsorisée')->assertSee('Sponsorisé')->assertSee('c=' . $boost->id, false);
    $this->get(route('welcome.produits'))->assertOk()->assertSee('c=' . $featured->id, false);
});
