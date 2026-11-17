<?php

use App\Models\FollowUp;
use App\Models\FollowUpOptOut;
use App\Models\FollowUpRule;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\ShopMessage;
use App\Models\StockAlert;
use App\Models\User;
use App\Services\FollowUp\FollowUpCatalog as Cat;
use App\Services\FollowUp\TemplateRenderer;
use App\Services\FollowUpService;
use App\Services\PushService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->push = new class {
        public array $sent = [];
        public function sendToUser(User $user, string $title, string $body, int $badge = 0, string $url = '/'): void { $this->sent[] = [$user->id, $title, $body]; }
    };
    app()->instance(PushService::class, $this->push);

    // Lundi 10 h à Conakry (UTC) : dans les heures d'envoi par défaut (8 h – 21 h)
    $this->travelTo(Carbon::parse('2026-11-02 10:00:00'));
    $this->shop = F::shop();
    $this->shop->update(['name' => 'Chez Fanta', 'currency' => 'GNF']);
    $this->vendor = $this->shop->owner;
    $this->product = F::product($this->shop, ['name' => 'Robe en wax', 'price' => 150000]);
    $this->client = User::factory()->create(['role' => 'client', 'name' => 'Aïssatou Bah', 'phone' => '622458137']);
});

/** Offre du vendeur au client, envoyée il y a $hours heures */
function offer($test, int $hours = 3, string $type = ShopMessage::TYPE_PRICE_OFFER, ?int $shopId = null, ?int $vendorId = null, ?int $productId = null): ShopMessage
{
    $m = ShopMessage::create([
        'shop_id' => $shopId ?? $test->shop->id, 'product_id' => $productId ?? $test->product->id,
        'sender_id' => $vendorId ?? $test->vendor->id, 'receiver_id' => $test->client->id,
        'type' => $type, 'proposed_price' => 135000, 'proposal_status' => ShopMessage::STATUS_PENDING, 'body' => 'Offre',
    ]);
    $m->forceFill(['created_at' => now()->subHours($hours)])->save();
    return $m;
}

function runFollowUps(): array
{
    return app(FollowUpService::class)->run();
}

function rule(int|null $shopId, string $type, array $values): FollowUpRule
{
    $rule = FollowUpRule::defaults($shopId, $type);
    $rule->fill($values)->save();
    return $rule;
}

/* ── Déclenchement ── */

test('offre sans réponse depuis 2 h : relance dans la messagerie et dans l\'appli, variables remplacées', function () {
    offer($this, 3);
    offer($this, 1, productId: F::product($this->shop)->id); // trop récente

    $r = runFollowUps();
    expect($r['created'])->toBe(1)->and($r['dispatched'])->toBe(1);

    $f = FollowUp::sole();
    expect($f->status)->toBe('sent')->and($f->type)->toBe(Cat::OFFER)->and($f->channels)->toBe(['messaging', 'in_app'])
        ->and($f->message)->toBe("Bonjour Aïssatou, Chez Fanta t'a proposé Robe en wax à 135\u{00A0}000\u{00A0}GNF. L'offre t'attend toujours : tu peux la confirmer dans la messagerie. " . route('client.products.show', $this->product));

    $posted = ShopMessage::where('follow_up_id', $f->id)->sole();
    expect($posted->sender_id)->toBe($this->vendor->id)->and($posted->receiver_id)->toBe($this->client->id)
        ->and($posted->body)->toContain('Réponds STOP pour ne plus recevoir ces rappels.')
        ->and($this->client->notifications()->where('data->type', 'relance')->count())->toBe(1)
        ->and($this->push->sent)->toHaveCount(1);
});

test('jamais deux fois la même relance ; au plus 2 relances espacées d\'un jour', function () {
    offer($this, 3);
    runFollowUps();
    runFollowUps();
    expect(FollowUp::count())->toBe(1);

    $this->travel(23)->hours();
    runFollowUps();
    expect(FollowUp::count())->toBe(1);

    $this->travel(2)->hours();                    // 11 h le lendemain
    runFollowUps();
    $this->travel(2)->days();
    runFollowUps();
    expect(FollowUp::orderBy('attempt')->pluck('attempt')->all())->toBe([1, 2])
        ->and(FollowUp::where('status', 'sent')->count())->toBe(2);

    // Envoi relancé à la main sur une relance déjà envoyée : rien ne repart
    expect(app(FollowUpService::class)->send(FollowUp::first()->id))->toBe('sent')
        ->and(ShopMessage::whereNotNull('follow_up_id')->count())->toBe(2);
});

/* ── Arrêt automatique ── */

test('arrêt dès que le client répond, et sa réponse est comptée', function () {
    offer($this, 3);
    runFollowUps();
    ShopMessage::create(['shop_id' => $this->shop->id, 'product_id' => $this->product->id, 'sender_id' => $this->client->id,
        'receiver_id' => $this->vendor->id, 'type' => 'text', 'body' => 'je réfléchis']);

    expect(FollowUp::sole()->responded_at)->not->toBeNull();
    $this->travel(2)->days();
    runFollowUps();
    expect(FollowUp::count())->toBe(1);
});

test('arrêt si l\'offre est acceptée ; la commande obtenue est attribuée à la relance', function () {
    $offer = offer($this, 3);
    runFollowUps();
    $offer->update(['proposal_status' => ShopMessage::STATUS_ACCEPTED]);
    Order::forceCreate(['user_id' => $this->client->id, 'shop_id' => $this->shop->id, 'total' => 135000, 'status' => Order::STATUS_EN_ATTENTE]);

    $f = FollowUp::sole();
    expect($f->recovered_amount)->toBe(135000)->and($f->recovered_order_id)->not->toBeNull();

    $this->travel(2)->days();
    runFollowUps();
    expect(FollowUp::where('type', Cat::OFFER)->count())->toBe(1);
    expect(app(FollowUpService::class)->stats($this->shop->id))->toMatchArray(['sent' => 1, 'orders' => 1, 'amount' => 135000]);
});

test('une relance prévue est arrêtée si la situation n\'est plus d\'actualité au moment de l\'envoi', function () {
    $offer = offer($this, 3);
    $f = FollowUp::create(['shop_id' => $this->shop->id, 'user_id' => $this->client->id, 'type' => Cat::OFFER, 'subject_type' => 'message',
        'subject_id' => $offer->id, 'attempt' => 1, 'status' => 'scheduled', 'scheduled_at' => now()]);
    $offer->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

    expect(app(FollowUpService::class)->send($f->id))->toBe('stopped')
        ->and($f->fresh()->stop_reason)->toBe('accepte');
});

/* ── Heures, jours, limite quotidienne ── */

test('respect des heures d\'envoi et des jours exclus (heure de Conakry)', function () {
    rule($this->shop->id, Cat::OFFER, ['enabled' => true, 'send_from_hour' => 8, 'send_to_hour' => 21, 'excluded_days' => [0]]);
    $this->travelTo(Carbon::parse('2026-11-02 22:30:00'));
    offer($this, 3);

    runFollowUps();
    expect(FollowUp::sole()->status)->toBe('scheduled');

    $this->travelTo(Carbon::parse('2026-11-03 07:50:00'));
    runFollowUps();
    expect(FollowUp::sole()->status)->toBe('scheduled');

    $this->travelTo(Carbon::parse('2026-11-03 08:05:00'));
    runFollowUps();
    expect(FollowUp::sole()->status)->toBe('sent');

    // Dimanche exclu
    $other = User::factory()->create(['role' => 'client']);
    $this->client = $other;
    $this->travelTo(Carbon::parse('2026-11-08 10:00:00')); // dimanche
    offer($this, 3);
    runFollowUps();
    expect(FollowUp::where('user_id', $other->id)->sole()->status)->toBe('scheduled');
});

test('pas plus d\'une relance par client et par jour, toutes boutiques confondues', function () {
    $shop2 = F::shop();
    offer($this, 3);
    offer($this, 4, shopId: $shop2->id, vendorId: $shop2->owner->id, productId: F::product($shop2)->id);

    runFollowUps();
    expect(FollowUp::where('status', 'sent')->count())->toBe(1)
        ->and(FollowUp::where('status', 'scheduled')->count())->toBe(1);

    $this->travel(1)->days();
    runFollowUps();
    expect(FollowUp::where('status', 'sent')->count())->toBe(2);
});

/* ── STOP ── */

test('le client écrit STOP : plus jamais de relance de cette boutique, confirmation envoyée', function () {
    offer($this, 3);
    ShopMessage::create(['shop_id' => $this->shop->id, 'sender_id' => $this->client->id, 'receiver_id' => $this->vendor->id, 'type' => 'text', 'body' => ' stop ']);

    expect(FollowUpOptOut::where('user_id', $this->client->id)->where('shop_id', $this->shop->id)->exists())->toBeTrue()
        ->and(ShopMessage::where('receiver_id', $this->client->id)->latest('id')->first()->body)->toContain('tu ne recevras plus de rappels de Chez Fanta');

    $this->travel(1)->days();
    offer($this, 3, productId: F::product($this->shop)->id);
    runFollowUps();
    expect(FollowUp::where('status', 'sent')->count())->toBe(0);
});

test('lien de désinscription signé : plus aucune relance d\'aucune boutique', function () {
    $this->get('/relances/stop/' . $this->client->id)->assertForbidden();
    $this->get(URL::signedRoute('followups.stop', ['user' => $this->client->id]))->assertOk()->assertSee('des boutiques Shopio');

    offer($this, 3);
    runFollowUps();
    expect(FollowUp::sole()->status)->toBe('stopped')->and(FollowUp::sole()->stop_reason)->toBe('stop');
});

/* ── Variables ── */

test('remplacement des variables : passages [[ ]] retirés si une variable est vide, variables inconnues retirées', function () {
    $tpl = 'Bonjour {prenom_client}, {produit} à {prix}.[[ Code {code_promo}[[ jusqu\'au {date_limite}]].]] {inconnue}Fin';

    expect(TemplateRenderer::render($tpl, ['{prenom_client}' => 'Fanta', '{produit}' => 'Pagne', '{prix}' => '50 000 GNF', '{code_promo}' => 'WAX10', '{date_limite}' => '30/11/2026']))
        ->toBe('Bonjour Fanta, Pagne à 50 000 GNF. Code WAX10 jusqu\'au 30/11/2026. Fin')
        ->and(TemplateRenderer::render($tpl, ['{prenom_client}' => 'Fanta', '{produit}' => 'Pagne', '{prix}' => '50 000 GNF', '{code_promo}' => 'WAX10', '{date_limite}' => '']))
        ->toBe('Bonjour Fanta, Pagne à 50 000 GNF. Code WAX10. Fin')
        ->and(TemplateRenderer::render($tpl, ['{prenom_client}' => 'Fanta', '{produit}' => 'Pagne', '{prix}' => '50 000 GNF', '{code_promo}' => '']))
        ->toBe('Bonjour Fanta, Pagne à 50 000 GNF. Fin')
        ->and(TemplateRenderer::unknownVariables($tpl))->toBe(['{inconnue}']);
});

test('code promo choisi par le vendeur joint au message ; ignoré s\'il a expiré', function () {
    $promo = PromoCode::forceCreate(['shop_id' => $this->shop->id, 'code' => 'WAX10', 'type' => 'percent', 'value' => 10, 'is_active' => true, 'expires_at' => now()->addDays(10)]);
    rule($this->shop->id, Cat::OFFER, ['enabled' => true, 'promo_code_id' => $promo->id]);
    offer($this, 3);
    runFollowUps();
    expect(FollowUp::sole()->message)->toContain('Code promo : WAX10.');

    $promo->update(['expires_at' => now()->subDay()]);
    $vars = app(FollowUpService::class)->sampleVariables($this->shop, FollowUpRule::resolve($this->shop->id, Cat::OFFER));
    expect($vars['{code_promo}'])->toBe('');
});

/* ── Autres situations ── */

test('commande à confirmer : rappel au vendeur, arrêté quand il la confirme', function () {
    $order = Order::forceCreate(['user_id' => $this->client->id, 'shop_id' => $this->shop->id, 'total' => 200000, 'status' => Order::STATUS_EN_ATTENTE]);
    $order->forceFill(['created_at' => now()->subHours(2)])->save();

    runFollowUps();
    $f = FollowUp::where('type', Cat::ORDER)->sole();
    expect($f->user_id)->toBe($this->vendor->id)->and($f->channels)->toBe(['in_app'])
        ->and($f->message)->toContain("La commande de Aïssatou (200\u{00A0}000\u{00A0}GNF) attend ta confirmation");

    $order->update(['status' => Order::STATUS_CONFIRMEE]);
    $this->travel(4)->hours();
    runFollowUps();
    expect(FollowUp::where('type', Cat::ORDER)->count())->toBe(1);
});

test('produit de nouveau en stock : message personnalisé de la boutique au lieu de la notification simple', function () {
    $this->product->update(['stock' => 0]);
    StockAlert::create(['user_id' => $this->client->id, 'product_id' => $this->product->id]);
    $this->push->sent = [];

    // Le service des alertes appelle cette méthode après la réponse HTTP ; ici on l'appelle directement
    $this->product->update(['stock' => 4]);
    expect(app(FollowUpService::class)->productRestocked($this->product->fresh()))->toBeTrue();
    runFollowUps();

    $f = FollowUp::where('type', Cat::RESTOCK)->sole();
    expect($f->status)->toBe('sent')->and($f->product_id)->toBe($this->product->id)
        ->and($f->message)->toContain('Robe en wax est de nouveau en stock chez Chez Fanta')
        ->and(StockAlert::count())->toBe(0)
        ->and(collect($this->push->sent)->pluck(1)->all())->not->toContain('De retour en stock');
});

test('conversation abandonnée : relance seulement si le vendeur l\'a activée', function () {
    $question = ShopMessage::create(['shop_id' => $this->shop->id, 'product_id' => $this->product->id, 'sender_id' => $this->client->id, 'receiver_id' => $this->vendor->id, 'type' => 'text', 'body' => 'Il reste la taille M ?']);
    $question->forceFill(['created_at' => now()->subDays(3)])->save();
    $answer = ShopMessage::create(['shop_id' => $this->shop->id, 'product_id' => $this->product->id, 'sender_id' => $this->vendor->id, 'receiver_id' => $this->client->id, 'type' => 'text', 'body' => 'Oui !']);
    $answer->forceFill(['created_at' => now()->subDays(2)])->save();

    runFollowUps();
    expect(FollowUp::count())->toBe(0); // désactivée par défaut

    rule($this->shop->id, Cat::ABANDONED, ['enabled' => true]);
    runFollowUps();
    expect(FollowUp::where('type', Cat::ABANDONED)->sole()->message)->toContain('tu t\'intéressais à Robe en wax');
});

test('client qui ne commande plus : relance de fidélité après le délai', function () {
    rule($this->shop->id, Cat::LOYALTY, ['enabled' => true, 'delay_minutes' => 30 * 1440, 'channels' => ['in_app']]);
    $order = Order::forceCreate(['user_id' => $this->client->id, 'shop_id' => $this->shop->id, 'total' => 80000, 'status' => Order::STATUS_LIVREE]);
    $order->forceFill(['created_at' => now()->subDays(20)])->save();

    runFollowUps();
    expect(FollowUp::count())->toBe(0);

    $this->travel(11)->days();
    runFollowUps();
    expect(FollowUp::where('type', Cat::LOYALTY)->sole()->message)->toContain('ça fait un moment');
});

test('admin : relance du vendeur dont la boutique n\'a aucun produit en ligne', function () {
    $empty = F::shop();
    $empty->forceFill(['created_at' => now()->subDays(15)])->save();

    runFollowUps();
    $f = FollowUp::where('type', Cat::INACTIVE_VENDOR)->sole();
    expect($f->user_id)->toBe($empty->owner->id)->and($f->subject_type)->toBe('shop_sans_produit');
});

test('relance désactivée par le vendeur : rien n\'est envoyé', function () {
    rule($this->shop->id, Cat::OFFER, ['enabled' => false]);
    offer($this, 3);
    runFollowUps();
    expect(FollowUp::count())->toBe(0);
});
