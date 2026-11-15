<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductPage;
use App\Models\StockAlert;
use App\Models\User;
use App\PageBuilder\PageFactory;
use App\PageBuilder\PageValidator;
use App\PageBuilder\ValidationContext;
use App\Support\CustomerRisk;
use App\Support\PhoneKey;
use App\Support\Sms\NimbaSmsSender;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->shop = F::shop();
    $this->owner = User::find($this->shop->user_id);
    $this->owner->forceFill(['shop_id' => $this->shop->id])->save();
    $this->product = F::product($this->shop, ['name' => 'Montre Haylou', 'price' => 100000, 'stock' => 20]);
    $this->url = route('client.products.page-order', $this->product);
});

/** Page publiée et activée ; $form : réglages du formulaire de commande */
function guardedPage($t, array $form = []): void
{
    $page = PageFactory::blank();
    foreach ($page['sections']['main']['blocks'] as $id => $b) {
        if ($b['type'] === 'order_form') {
            $page['sections']['main']['blocks'][$id]['settings'] = array_replace($b['settings'], $form);
        }
    }
    $clean = (new PageValidator(new ValidationContext($t->shop, $t->product)))->validate($page);
    ProductPage::updateOrCreate(['product_id' => $t->product->id], ['shop_id' => $t->shop->id, 'draft' => $clean, 'published' => $clean, 'published_at' => now(), 'is_enabled' => true, 'version' => 1]);
}

function guestForm(array $over = []): array
{
    return $over + ['name' => 'Aïssatou Bah', 'phone' => '622458137', 'commune' => 'Kaloum', 'quantity' => 1];
}

/** Commande terminée avec ce numéro (pour l'historique du client) */
function pastOrder($shop, string $phone, string $status): Order
{
    return Order::forceCreate(['shop_id' => $shop->id, 'total' => 1000, 'status' => $status, 'client_phone' => $phone, 'client_name' => 'Test']);
}

function nimbaOn(): void
{
    config(['services.nimba' => ['service_id' => 'sid-test', 'secret' => 'secret-test', 'sender' => 'Shopio']]);
}

test('clé de téléphone : un même numéro, quel que soit le format', function () {
    foreach (['622458137', '622 45 81 37', '+224 622 45 81 37', '00224622458137', '224622458137'] as $format) {
        expect(PhoneKey::of($format))->toBe('622458137');
    }
    expect(PhoneKey::of('12'))->toBeNull()
        ->and(PhoneKey::masked('+224622458137'))->toBe('+224 6•• •• 81 37');

    $order = pastOrder($this->shop, '+224 622 45 81 37', Order::STATUS_LIVREE);
    expect($order->fresh()->client_phone_key)->toBe('622458137');
});

test('produit épuisé : « Me prévenir du retour en stock » sur la page construite', function () {
    guardedPage($this);
    $this->product->update(['stock' => 0]);

    $guest = $this->get(route('client.products.show', $this->product))->assertOk()->getContent();
    expect($guest)->toContain('Me prévenir du retour en stock')
        ->toContain(e(route('login', ['redirect' => route('client.products.show', $this->product)])));

    $client = User::factory()->create(['role' => 'client']);
    $this->actingAs($client)->get(route('client.products.show', $this->product))
        ->assertSee('data-pb-stock="' . route('client.products.stock-alert', $this->product) . '"', false)
        ->assertSee('aria-pressed="false"', false);

    $this->actingAs($client)->postJson(route('client.products.stock-alert', $this->product))->assertOk()->assertJsonPath('active', true);
    expect(StockAlert::where('user_id', $client->id)->where('product_id', $this->product->id)->exists())->toBeTrue();
    $this->actingAs($client)->get(route('client.products.show', $this->product))->assertSee('Alerte activée : tu seras prévenu');
});

test('commande en double : avertissement, puis le client peut confirmer', function () {
    guardedPage($this);
    $first = $this->postJson($this->url, guestForm())->assertCreated()->json('id');

    $this->postJson($this->url, guestForm(['phone' => '+224 622 45 81 37']))->assertStatus(409)
        ->assertJsonPath('duplicate', true)
        ->assertJsonPath('message', fn ($m) => str_contains($m, 'Montre Haylou') && str_contains($m, 'il y a 1 min') && !str_contains($m, 'n°'));
    expect(Order::count())->toBe(1);

    $second = $this->postJson($this->url, guestForm(['confirm_duplicate' => 1]))->assertCreated()->json('id');
    expect(OrderItem::where('order_id', $second)->first()->options['duplicate_of'])->toBe($first);

    // Le vendeur voit l'avertissement dans la fiche de commande
    $this->actingAs($this->owner)->get(route('orders.show', $second))->assertSee('Possible doublon de la commande n° ' . $first);
    auth()->logout();

    // Plus de 30 minutes après, ou si la commande précédente est annulée : pas d'avertissement
    $this->travel(31)->minutes();
    $this->postJson($this->url, guestForm())->assertCreated();
    Order::query()->update(['status' => Order::STATUS_ANNULEE]);
    $this->postJson($this->url, guestForm())->assertCreated();
});

test('commande en double aussi sur la page produit habituelle', function () {
    $this->product->forceFill(['page' => ['offers' => [['key' => 'un', 'label' => '1 montre', 'qty' => 1, 'price' => 100000]]]])->save();
    $old = route('client.products.order', $this->product);
    $form = ['offer' => 'un', 'name' => 'Aïssatou Bah', 'phone' => '622458137', 'commune' => 'Kaloum'];

    $first = $this->postJson($old, $form)->assertCreated()->json('id');
    $this->postJson($old, $form)->assertStatus(409)->assertJsonPath('duplicate', true);
    $id = $this->postJson($old, $form + ['confirm_duplicate' => 1])->assertCreated()->json('id');
    expect(OrderItem::where('order_id', $id)->first()->options['duplicate_of'])->toBe($first);
});

test('historique du client : totaux sur tout Shopio, visibles par le vendeur', function () {
    expect(CustomerRisk::forPhone('622458137')['level'])->toBe('new');

    $other = F::shop();
    pastOrder($other, '622458137', Order::STATUS_LIVREE);
    expect(CustomerRisk::forPhone('+224622458137')['level'])->toBe('good');
    pastOrder($other, '622 45 81 37', Order::STATUS_ANNULEE);
    expect(CustomerRisk::forPhone('622458137')['level'])->toBe('watch');
    pastOrder($this->shop, '00224622458137', Order::STATUS_ANNULEE);
    $risk = CustomerRisk::forPhone('622458137');
    expect($risk['level'])->toBe('risky')->and($risk['summary'])->toBe('1 livrée · 2 annulées sur Shopio');

    guardedPage($this);
    $id = $this->postJson($this->url, guestForm())->assertCreated()->json('id');
    $this->actingAs($this->owner)->get(route('orders.show', $id))
        ->assertSee('Client à risque')->assertSee('1 livrée · 2 annulées sur Shopio')
        ->assertDontSee($other->name); // aucune autre boutique nommée
});

test('code SMS : envoyé par Nimba, vérifié, puis la commande part', function () {
    nimbaOn();
    Http::fake(['api.nimbasms.com/*' => Http::response(['messageid' => 'abc'], 201)]);
    guardedPage($this, ['verify_phone' => 'always']);

    $this->postJson($this->url, guestForm(['phone' => '622 45 81 37']))->assertStatus(202)
        ->assertJsonPath('otp_required', true)->assertJsonPath('message', fn ($m) => str_contains($m, '+224 6•• •• 81 37'));
    expect(Order::count())->toBe(0);

    $code = null;
    Http::assertSent(function (HttpRequest $r) use (&$code) {
        preg_match('/est (\d{6})\./', $r['message'], $m);
        $code = $m[1] ?? null;
        return $r->url() === NimbaSmsSender::URL && $r['to'] === ['+224622458137'] && $r['sender_name'] === 'Shopio'
            && $r->hasHeader('Authorization', 'Basic ' . base64_encode('sid-test:secret-test'));
    });

    // Nouvel envoi demandé tout de suite : pas de deuxième SMS
    $this->postJson($this->url, guestForm())->assertStatus(202);
    Http::assertSentCount(1);

    $wrong = $code === '000000' ? '111111' : '000000';
    $this->postJson($this->url, guestForm(['otp' => $wrong]))->assertStatus(422)->assertJsonValidationErrors(['otp']);
    $id = $this->postJson($this->url, guestForm(['otp' => $code]))->assertCreated()->json('id');
    expect(OrderItem::where('order_id', $id)->first()->options['phone_verified'])->toBeTrue();

    // Le code est consommé : il ne sert pas deux fois
    $this->postJson($this->url, guestForm(['otp' => $code, 'confirm_duplicate' => 1]))->assertStatus(422);
});

test('code SMS : seulement quand le réglage le demande', function () {
    nimbaOn();
    Http::fake(['api.nimbasms.com/*' => Http::response([], 201)]);

    guardedPage($this, ['verify_phone' => 'above', 'verify_above' => 500000]);
    $this->postJson($this->url, guestForm())->assertCreated();               // 100 000 GNF : sous le seuil

    guardedPage($this, ['verify_phone' => 'risky']);
    $this->postJson($this->url, guestForm(['phone' => '655112233']))->assertCreated(); // nouveau numéro
    pastOrder(F::shop(), '664001122', Order::STATUS_ANNULEE);
    pastOrder(F::shop(), '664001122', Order::STATUS_ANNULEE);
    $this->postJson($this->url, guestForm(['phone' => '664001122']))->assertStatus(202); // numéro à risque

    Http::assertSentCount(1);
});

test('code SMS : sans fournisseur configuré, les commandes passent ; si Nimba échoue, rien n\'est créé', function () {
    config(['services.nimba' => ['service_id' => null, 'secret' => null, 'sender' => null]]);
    Http::fake(['api.nimbasms.com/*' => Http::response(['detail' => 'Sender name not approved'], 400)]);
    guardedPage($this, ['verify_phone' => 'always']);
    $this->postJson($this->url, guestForm())->assertCreated();
    Http::assertNothingSent();

    nimbaOn();
    $this->postJson($this->url, guestForm(['phone' => '628000111']))->assertStatus(503)
        ->assertJsonPath('message', fn ($m) => str_contains($m, "n'a pas pu partir"));
    expect(Order::count())->toBe(1);
});

test('éditeur : réglage du code SMS, avec un avertissement tant que les SMS ne sont pas branchés', function () {
    config(['services.nimba' => ['service_id' => null, 'secret' => null, 'sender' => null]]);
    guardedPage($this, ['verify_phone' => 'always']);
    $this->actingAs($this->owner)->get(route('products.builder.frame', $this->product))->assertOk()
        ->assertSee('aucun fournisseur SMS n\'est configuré', false);

    $schema = $this->actingAs($this->owner)->get(route('products.builder.edit', $this->product))->viewData('config')['blocks']['order_form'];
    expect(collect($schema['settings'])->firstWhere('id', 'verify_phone')['default'])->toBe('never');
});
