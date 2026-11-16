<?php

use App\Models\Order;
use App\Models\User;
use App\Support\OrderAccess;
use Illuminate\Support\Facades\URL;
use Tests\Support\CampaignFixtures as F;

/* Lien de suivi court /suivi/{tracking_token} : impossible à deviner, sans le numéro de la commande ;
   les anciens liens signés /suivi/{numéro}?signature=… restent valables et mènent au lien court. */

beforeEach(function () {
    $this->shop = F::shop();
    $this->client = User::factory()->create(['role' => 'client']);
    $this->order = Order::forceCreate(['user_id' => $this->client->id, 'shop_id' => $this->shop->id, 'total' => 150000, 'status' => Order::STATUS_EN_ATTENTE]);
});

test('chaque nouvelle commande reçoit un jeton de 20 lettres et chiffres, différent des autres', function () {
    $other = Order::forceCreate(['shop_id' => $this->shop->id, 'total' => 50000, 'status' => Order::STATUS_EN_ATTENTE]);
    expect($this->order->tracking_token)->toMatch('/^[a-z0-9]{20}$/')
        ->and($other->tracking_token)->not->toBe($this->order->tracking_token);
    // Le lien partagé ne montre pas le numéro de la commande
    $url = OrderAccess::trackingUrl($this->order);
    expect($url)->toEndWith('/suivi/' . $this->order->tracking_token)->not->toContain('signature');
});

test('le lien court ouvre le suivi pour quiconque l’a reçu ; un numéro seul ou un faux jeton donnent 404', function () {
    $this->get(OrderAccess::trackingUrl($this->order))->assertOk();
    $this->getJson(OrderAccess::trackingUrl($this->order, 'suivi.data'))->assertOk();
    $this->get('/suivi/' . $this->order->id)->assertNotFound();
    $this->getJson('/suivi/' . $this->order->id . '/data')->assertNotFound();
    $this->get('/suivi/' . str_repeat('a', 20))->assertNotFound();
});

test('un ancien lien signé déjà envoyé reste valable et mène au lien court', function () {
    $old = url(URL::signedRoute('suivi.show', ['suivi' => $this->order->id], null, false));
    $this->get($old)->assertRedirect(OrderAccess::trackingUrl($this->order));
    $oldData = url(URL::signedRoute('suivi.data', ['suivi' => $this->order->id], null, false));
    $this->getJson($oldData)->assertOk();
    // Le client connecté et la boutique ouvrent aussi l'adresse par numéro, puis passent au lien court
    $this->actingAs($this->client)->get('/suivi/' . $this->order->id)->assertRedirect(OrderAccess::trackingUrl($this->order));
});

test('une commande sans jeton (enregistrée hors Eloquent) en reçoit un quand on demande son lien', function () {
    \Illuminate\Support\Facades\DB::table('orders')->where('id', $this->order->id)->update(['tracking_token' => null]);
    $order = $this->order->fresh();
    $url = OrderAccess::trackingUrl($order);
    expect($order->fresh()->tracking_token)->toMatch('/^[a-z0-9]{20}$/');
    $this->get($url)->assertOk();
});
