<?php

use App\Models\LiveVisitor;
use App\Models\Order;
use App\Models\ShopTraffic;
use App\Models\User;
use App\Support\GeoCountry;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CampaignFixtures as F;

/* Vue en direct : signal de la vitrine (sans IP), chiffres réels, page vendeur avec la carte de l'Afrique. */

beforeEach(function () {
    $this->shop = F::shop();
    $this->shop->update(['slug' => 'chez-fanta', 'currency' => 'GNF', 'country' => 'GN']);
    $this->owner = $this->shop->owner;
    $this->owner->forceFill(['shop_id' => $this->shop->id, 'role_in_shop' => 'admin'])->save();
    $this->product = F::product($this->shop, ['name' => 'Sac en wax']);
});

test('le signal de la vitrine enregistre le pays et l’étape, jamais l’adresse IP', function () {
    $this->withHeader('CF-IPCountry', 'SN')->postJson(route('public.shops.live', $this->shop), [
        'visitor' => 'abc123def456ghi7', 'stage' => 'product', 'product_id' => $this->product->id, 'cart_items' => 2,
    ])->assertNoContent();

    $row = LiveVisitor::firstOrFail();
    expect($row->country)->toBe('SN')->and($row->stage)->toBe('product')->and($row->product_id)->toBe($this->product->id)->and($row->cart_items)->toBe(2)
        ->and(Schema::getColumnListing('live_visitors'))->not->toContain('ip')->not->toContain('ip_address');

    // Produit d'une autre boutique : refusé, le visiteur « parcourt » la boutique
    $other = F::product(F::shop());
    $this->postJson(route('public.shops.live', $this->shop), ['visitor' => 'abc123def456ghi7', 'stage' => 'product', 'product_id' => $other->id])->assertNoContent();
    expect($row->fresh()->stage)->toBe('browse')->and($row->fresh()->product_id)->toBeNull()
        ->and(LiveVisitor::count())->toBe(1);
});

test('la boutique elle-même, les robots et les identifiants invalides ne sont pas comptés', function () {
    $this->actingAs($this->owner)->postJson(route('public.shops.live', $this->shop), ['visitor' => 'abc123def456ghi7', 'stage' => 'browse'])->assertNoContent();
    auth()->logout();
    $this->withHeader('User-Agent', 'Googlebot/2.1')->postJson(route('public.shops.live', $this->shop), ['visitor' => 'zzz123def456ghi7', 'stage' => 'browse'])->assertNoContent();
    $this->withHeader('User-Agent', 'Mozilla/5.0')->postJson(route('public.shops.live', $this->shop), ['visitor' => '<script>', 'stage' => 'browse'])->assertStatus(422);
    expect(LiveVisitor::count())->toBe(0);
});

test('la base DB-IP donne le pays d’une adresse guinéenne', function () {
    if (!is_file(GeoCountry::path())) $this->markTestSkipped('Base DB-IP absente : php artisan shopio:geoip-update');
    expect(GeoCountry::lookup('41.223.48.10'))->toBe('GN')
        ->and(GeoCountry::lookup('127.0.0.1'))->toBeNull()
        ->and(GeoCountry::lookup('192.168.1.10'))->toBeNull();
});

test('les chiffres en direct sont calculés à partir des vraies données', function () {
    $mk = fn ($v, $stage, $country, $seen, $items = 0) => LiveVisitor::create(['shop_id' => $this->shop->id, 'visitor' => $v, 'stage' => $stage, 'cart_items' => $items, 'country' => $country, 'first_seen_at' => now()->subMinutes(3), 'last_seen_at' => $seen]);
    $mk('visitor0000000001', 'form', 'GN', now()->subSeconds(10), 2);
    $mk('visitor0000000002', 'cart', 'GN', now()->subSeconds(30), 1);
    $mk('visitor0000000003', 'browse', null, now()->subSeconds(5));
    $mk('visitor0000000004', 'browse', 'SN', now()->subMinutes(5)); // parti : plus en direct
    Order::forceCreate(['shop_id' => $this->shop->id, 'total' => 150000, 'status' => Order::STATUS_EN_ATTENTE, 'client_name' => 'Mariama Diallo']);
    Order::forceCreate(['shop_id' => $this->shop->id, 'total' => 90000, 'status' => Order::STATUS_ANNULEE]);
    ShopTraffic::create(['shop_id' => $this->shop->id, 'day' => today(), 'source' => 'facebook', 'visits' => 7]);

    $this->actingAs($this->owner)->getJson(route('boutique.live.data'))->assertOk()
        ->assertJsonPath('live', 3)
        ->assertJsonPath('todayVisitors', 7)
        ->assertJsonPath('todayOrders', 1)
        ->assertJsonPath('todaySales', '150 000 GNF')
        ->assertJsonPath('behavior.carts', 2)
        ->assertJsonPath('behavior.form', 1)
        ->assertJsonPath('behavior.orders', 1)
        ->assertJsonPath('map.visitors.GN', 2)
        ->assertJsonPath('map.orders.GN', 1)
        ->assertJsonPath('map.unplaced', 1)
        ->assertJsonFragment(['text' => 'Remplit le formulaire de commande · panier de 2'])
        ->assertJsonFragment(['place' => 'Guinée']);
});

test('un visiteur hors d’Afrique est nommé et placé sur la carte du monde', function () {
    LiveVisitor::create(['shop_id' => $this->shop->id, 'visitor' => 'visitor0000000009', 'stage' => 'browse', 'country' => 'FR', 'first_seen_at' => now(), 'last_seen_at' => now()]);
    $this->actingAs($this->owner)->getJson(route('boutique.live.data'))->assertOk()
        ->assertJsonPath('map.visitors.FR', 1)->assertJsonPath('map.unplaced', 0)
        ->assertJsonFragment(['place' => 'France']);
});

test('la page Vue en direct affiche le globe (vues Afrique et Monde) et un JavaScript valide', function () {
    $html = $this->actingAs($this->owner)->get(route('boutique.live'))->assertOk()
        ->assertSee('Vue en direct')->assertSee('Visiteurs en direct')->assertSee('Comportement des clients')
        ->assertSee('id="lvGlobe"', false)->assertSee('data-tab="africa"', false)->assertSee('data-tab="world"', false)
        ->assertSee('data-iso="GN"', false)->assertSee('data-iso="ZA"', false)->assertSee('data-iso="FR"', false)->assertSee('data-iso="BR"', false)
        ->getContent();
    // Un seul globe : chaque pays une fois, noms en français, petits pays en points (Seychelles)
    expect(substr_count($html, 'data-iso="GN"'))->toBe(1)->and($html)->toContain('data-name="États-Unis"')
        ->and($html)->toMatch('#<circle class="lv-isle[^"]*" data-iso="SC"#');
    // Les contours sont en longitude / latitude (la Guinée est autour de -11°, 10°)
    $gn = collect(\App\Support\LiveMaps::globe()['countries'])->firstWhere('iso', 'GN');
    expect($gn['c'][0])->toBeGreaterThan(-16)->toBeLessThan(-7)->and($gn['c'][1])->toBeGreaterThan(7)->toBeLessThan(13);

    preg_match_all('#<script>(.*?)</script>#s', $html, $m);
    $js = collect($m[1])->first(fn ($s) => str_contains($s, 'URL_DATA'));
    $file = sys_get_temp_dir() . '/shopio-live-' . getmypid() . '.js';
    file_put_contents($file, $js);
    exec('node --check ' . escapeshellarg($file) . ' 2>&1', $out, $code);
    @unlink($file);
    expect($code)->toBe(0, implode("\n", $out));

    // Un client n'y a pas accès
    $client = User::factory()->create(['role' => 'client']);
    $this->actingAs($client)->get(route('boutique.live'))->assertForbidden();
});

test('la vitrine envoie le signal en direct', function () {
    $this->get('/s/chez-fanta')->assertOk()->assertSee('en-direct', false)->assertSee('liveStart', false);
});
