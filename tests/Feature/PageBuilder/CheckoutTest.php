<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductPage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\PageBuilder\Blocks\Delivery;
use App\PageBuilder\Commerce\Checkout;
use App\PageBuilder\PageFactory;
use App\PageBuilder\PageValidator;
use App\PageBuilder\RenderContext;
use App\PageBuilder\Renderer;
use App\PageBuilder\ValidationContext;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->shop = F::shop();
    $this->shop->forceFill(['product_page' => ['delivery_fees' => ['Ratoma' => 20000]]])->save();
    $this->product = F::product($this->shop, ['name' => 'Savon noir artisanal', 'price' => 100000, 'stock' => 20, 'description' => 'Savon fait main à Kindia.']);
});

/** Publie une page dont la section Produit contient ces blocs : [[type, réglages], …] (formulaire ajouté s'il manque) */
function publishBlocks($product, array $blocks): ProductPage
{
    $section = ['type' => 'main-product', 'settings' => [], 'blocks' => [], 'block_order' => []];
    if (!collect($blocks)->contains(fn ($b) => $b[0] === 'order_form')) {
        $blocks[] = ['order_form', []];
    }
    foreach ($blocks as $i => [$type, $settings]) {
        $section['blocks']["b{$i}"] = PageFactory::block($type, $settings);
        $section['block_order'][] = "b{$i}";
    }
    $page = (new PageValidator(new ValidationContext($product->shop, $product)))->validate(['sections' => ['main' => $section], 'order' => ['main']]);
    return ProductPage::create(['product_id' => $product->id, 'shop_id' => $product->shop_id, 'draft' => $page, 'published' => $page, 'published_at' => now(), 'is_enabled' => true, 'version' => 1]);
}

function guestOrder(array $over = []): array
{
    return $over + ['name' => 'Aïssatou Bah', 'phone' => '622458137', 'commune' => 'Ratoma', 'quartier' => 'Kipé'];
}

test('calcul des offres : remise en %, en montant, plafond de 90 %, jamais négatif', function () {
    expect(Checkout::discounted(200000, 'percent', 10))->toBe(180000)
        ->and(Checkout::discounted(300000, 'percent', 99))->toBe(30000)
        ->and(Checkout::discounted(300000, 'amount', 50000))->toBe(250000)
        ->and(Checkout::discounted(300000, 'amount', 900000))->toBe(0)
        ->and(Checkout::discounted(300000, 'none', 40))->toBe(300000);

    $page = publishBlocks($this->product, [['offers', ['tier2_value' => 10, 'tier3_discount' => 'amount', 'tier3_value' => 60000]]]);
    $tiers = Checkout::fromPage($page->published, $this->product)->tiers(100000);

    expect(array_keys($tiers))->toBe([1, 2, 3])
        ->and($tiers[1])->toMatchArray(['qty' => 1, 'total' => 100000, 'saving' => 0])
        ->and($tiers[2])->toMatchArray(['qty' => 2, 'base' => 200000, 'total' => 180000, 'saving' => 20000])
        ->and($tiers[3])->toMatchArray(['qty' => 3, 'base' => 300000, 'total' => 240000, 'badge' => 'Meilleure économie']);
});

test('la page publiée remplace la page produit actuelle ; sans publication, rien ne change', function () {
    $this->get(route('client.products.show', $this->product))->assertOk()->assertSee('id="ppForm"', false);

    $page = publishBlocks($this->product, [['title', []], ['price', []], ['offers', []]]);
    $html = $this->get(route('client.products.show', $this->product))->assertOk()
        ->assertDontSee('id="ppForm"', false)
        ->assertSee('data-pb-checkout', false)
        ->assertSee('commander-page', false)
        ->getContent();
    expect($html)->toContain("180\u{00A0}000")->toContain("255\u{00A0}000")->not->toContain('data-section-id');

    // Brouillon modifié mais pas publié : les clients voient toujours la version publiée
    $page->update(['draft' => PageFactory::blank()]);
    $this->get(route('client.products.show', $this->product))->assertSee("255\u{00A0}000");
});

test('commande avec offre, cadeau, produit complémentaire et champ personnalisé : tout est recalculé', function () {
    $gift = F::product($this->shop, ['name' => 'Gant de gommage', 'price' => 15000, 'stock' => 5]);
    $extra = F::product($this->shop, ['name' => 'Huile de karité', 'price' => 50000, 'stock' => 4]);
    publishBlocks($this->product, [
        ['offers', ['tier3_gift' => $gift->id]],
        ['upsells', ['products' => [$extra->id], 'discount' => 20]],
        ['custom_field', ['label' => 'Parfum souhaité', 'field_type' => 'select', 'options' => "Nature\nCitron", 'required' => true]],
    ]);

    // Le navigateur envoie aussi de faux prix : ignorés
    $res = $this->postJson(route('client.products.page-order', $this->product), guestOrder([
        'tier' => 3, 'upsells' => [$extra->id], 'fields' => ['b2' => 'Citron'], 'total' => 1, 'price' => 1,
    ]))->assertCreated();

    $order = Order::with('items')->find($res->json('id'));
    expect((int) $order->total)->toBe(255000 + 40000)
        ->and((int) $order->delivery_fee)->toBe(20000)
        ->and($res->json('total'))->toBe(255000 + 40000 + 20000)
        ->and($order->items)->toHaveCount(3);

    [$main, $giftLine, $upsell] = $order->items->sortBy('id')->values()->all();
    expect($main->quantity)->toBe(3)
        ->and((float) $main->price)->toBe(85000.0)
        ->and($main->variant_name)->toBe('3 articles')
        ->and($main->options['offer'])->toMatchArray(['tier' => 3, 'qty' => 3, 'base' => 300000, 'saving' => 45000])
        ->and($main->options['custom'])->toBe([['label' => 'Parfum souhaité', 'value' => 'Citron']])
        ->and($giftLine->product_id)->toBe($gift->id)
        ->and((float) $giftLine->price)->toBe(0.0)
        ->and($giftLine->options['gift'])->toBeTrue()
        ->and($upsell->product_id)->toBe($extra->id)
        ->and((float) $upsell->price)->toBe(40000.0);

    expect(Payment::where('order_id', $order->id)->value('amount'))->toEqual(295000)
        ->and($this->product->fresh()->stock)->toBe(17)
        ->and($gift->fresh()->stock)->toBe(4)
        ->and($extra->fresh()->stock)->toBe(3);
});

test('choix truqués refusés : offre désactivée, produit non proposé, réponse hors liste ou manquante', function () {
    $other = F::product($this->shop, ['name' => 'Autre produit']);
    $foreign = F::product(F::shop());
    publishBlocks($this->product, [
        ['offers', ['tier2_enabled' => false]],
        ['upsells', ['products' => []]],
        ['custom_field', ['label' => 'Taille', 'field_type' => 'select', 'options' => "S\nM", 'required' => true]],
    ]);
    $url = route('client.products.page-order', $this->product);
    $ok = ['fields' => ['b2' => 'M']];

    $this->postJson($url, guestOrder(['tier' => 2] + $ok))->assertStatus(422)->assertJsonPath('message', 'Cette offre n\'existe plus : recharge la page.');
    $this->postJson($url, guestOrder(['tier' => 1, 'upsells' => [$other->id]] + $ok))->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'complémentaire'));
    $this->postJson($url, guestOrder(['tier' => 1, 'upsells' => [$foreign->id]] + $ok))->assertStatus(422);
    $this->postJson($url, guestOrder(['tier' => 1, 'fields' => ['b2' => 'XXL']]))->assertStatus(422)->assertJsonValidationErrors(['fields.b2']);
    $this->postJson($url, guestOrder(['tier' => 1]))->assertStatus(422)->assertJsonValidationErrors(['fields.b2'])
        ->assertJsonPath('errors', fn ($e) => str_contains($e['fields.b2'][0], '« Taille »'));
    $this->postJson($url, guestOrder())->assertStatus(422)->assertJsonValidationErrors(['tier']);

    expect(Order::count())->toBe(0)->and($this->product->fresh()->stock)->toBe(20);
});

test('sans offres : quantité limitée, prix et stock de l\'option choisie', function () {
    $red = ProductVariant::create(['product_id' => $this->product->id, 'name' => 'Rouge', 'price' => 120000, 'stock' => 3, 'is_active' => true]);
    publishBlocks($this->product, [['variants', []], ['quantity', ['max' => 3]]]);
    $url = route('client.products.page-order', $this->product);

    $this->postJson($url, guestOrder(['quantity' => 1]))->assertStatus(422)->assertJsonValidationErrors(['variant_id']);
    $this->postJson($url, guestOrder(['variant_id' => $red->id, 'quantity' => 4]))->assertStatus(422)->assertJsonValidationErrors(['quantity']);

    $res = $this->postJson($url, guestOrder(['variant_id' => $red->id, 'quantity' => 2]))->assertCreated();
    $item = OrderItem::where('order_id', $res->json('id'))->first();
    expect((int) Order::find($res->json('id'))->total)->toBe(240000)
        ->and($item->product_variant_id)->toBe($red->id)
        ->and($item->variant_name)->toBe('Rouge')
        ->and($red->fresh()->stock)->toBe(1);

    $this->postJson($url, guestOrder(['variant_id' => $red->id, 'quantity' => 2, 'confirm_duplicate' => 1]))->assertStatus(422)
        ->assertJsonPath('message', 'Il ne reste que 1 en stock : choisis une quantité plus petite.');
});

test('stock insuffisant ou page non publiée : aucune commande', function () {
    $this->product->update(['stock' => 1]);
    publishBlocks($this->product, [['offers', []]]);
    $this->postJson(route('client.products.page-order', $this->product), guestOrder(['tier' => 2]))->assertStatus(422)
        ->assertJsonPath('message', 'Il ne reste que 1 en stock : choisis une quantité plus petite.');

    $other = F::product($this->shop);
    $this->postJson(route('client.products.page-order', $other), guestOrder())->assertNotFound();
    ProductPage::create(['product_id' => $other->id, 'shop_id' => $this->shop->id, 'draft' => PageFactory::blank()]);
    $this->postJson(route('client.products.page-order', $other), guestOrder())->assertNotFound();

    expect(Order::count())->toBe(0);
});

test('le formulaire de commande est obligatoire et toujours visible dans la section Produit', function () {
    $validate = fn ($page) => (new PageValidator(new ValidationContext($this->shop, $this->product)))->validate($page);
    $page = PageFactory::blank();
    $formId = collect($page['sections']['main']['blocks'])->search(fn ($b) => $b['type'] === 'order_form');

    $hidden = $page;
    $hidden['sections']['main']['blocks'][$formId]['disabled'] = true;
    expect(fn () => $validate($hidden))->toThrow(ValidationException::class, 'Formulaire de commande');

    $removed = $page;
    unset($removed['sections']['main']['blocks'][$formId]);
    $removed['sections']['main']['block_order'] = array_values(array_diff($removed['sections']['main']['block_order'], [$formId]));
    expect(fn () => $validate($removed))->toThrow(ValidationException::class, 'obligatoire');
});

test('preuve sociale : uniquement des données réelles', function () {
    $render = fn (array $blocks, bool $editing = false) => Renderer::render(publishBlocks($this->product, $blocks)->published, new RenderContext($this->shop, $this->product->fresh(), $editing));
    $blocks = [
        ['rating', []], ['stock', ['threshold' => 10]], ['sold_count', ['min' => 1]],
        ['countdown', ['source' => 'date', 'ends_at' => now()->subDay()->format('Y-m-d\TH:i')]],
    ];

    // Ni avis, ni commande livrée, stock au-dessus du seuil, échéance passée : rien n'est affiché
    $html = $render($blocks);
    expect($html)->not->toContain('class="pb-rating')->not->toContain('class="pb-stock')->not->toContain('ont déjà reçu')->not->toContain('class="pb-cd"');
    ProductPage::query()->delete();

    // Dans l'éditeur, le vendeur voit pourquoi
    expect($render($blocks, true))->toContain('apparaîtra dès 1 avis réel')->toContain('stock actuel : 20');
    ProductPage::query()->delete();

    // Avec de vraies données
    $client = User::factory()->create(['role' => 'client']);
    $order = Order::forceCreate(['user_id' => $client->id, 'shop_id' => $this->shop->id, 'total' => 100000, 'status' => Order::STATUS_LIVREE]);
    OrderItem::create(['order_id' => $order->id, 'product_id' => $this->product->id, 'quantity' => 1, 'price' => 100000]);
    Review::forceCreate(['order_id' => $order->id, 'user_id' => $client->id, 'rating' => 4, 'comment' => 'Très bon savon']);
    $this->product->update(['stock' => 4]);
    $blocks[3] = ['countdown', ['source' => 'date', 'ends_at' => now()->addDays(2)->format('Y-m-d\TH:i')]];

    $html = $render($blocks);
    expect($html)->toContain('(1 avis)')->toContain('Plus que 4 en stock')->toContain('1 clients ont déjà reçu ce produit')->toContain('class="pb-cd"');
});

test('livraison estimée : délais de la boutique, sans dimanche', function () {
    $saturday = Carbon::parse('2026-10-31 10:00'); // samedi
    [$from, $to] = Delivery::window(['min_days' => 1, 'max_days' => 3, 'skip_sunday' => true], $saturday);
    expect($from->toDateString())->toBe('2026-11-02')  // dimanche sauté
        ->and($to->toDateString())->toBe('2026-11-04');

    [$from, $to] = Delivery::window(['min_days' => 5, 'max_days' => 2, 'skip_sunday' => false], $saturday);
    expect($from->toDateString())->toBe('2026-11-05')->and($to->toDateString())->toBe('2026-11-05'); // plus long ≥ plus court
});
