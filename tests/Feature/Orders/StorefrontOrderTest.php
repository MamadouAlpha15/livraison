<?php

use App\Models\Order;
use App\Models\User;
use App\Support\PageModels;
use Tests\Support\CampaignFixtures as F;

/* Commande passée depuis la vitrine (thèmes Express et Signature) : invité ou client connecté, paiement à la livraison,
   lien de suivi court renvoyé à la page. */

beforeEach(function () {
    $this->shop = F::shop();
    $this->product = F::product($this->shop, ['name' => 'Sac en wax', 'price' => 150000, 'stock' => 5]);
    $this->body = ['items' => [['id' => $this->product->id, 'qty' => 2]], 'phone' => '622 12 34 56', 'commune' => PageModels::COMMUNES[0], 'landmark' => 'Près de la mosquée'];
});

test('un invité passe commande depuis la vitrine : commande, stock, lien de suivi court', function () {
    $j = $this->postJson(route('public.shops.order', $this->shop), $this->body + ['name' => 'Mariama Diallo'])->assertCreated()->json();
    $order = Order::findOrFail($j['id']);
    expect((int) $order->total)->toBe(300000)->and($order->client_name)->toBe('Mariama Diallo')
        ->and($this->product->fresh()->stock)->toBe(3)
        ->and($j['tracking'])->toEndWith('/suivi/' . $order->tracking_token);
    $this->get($j['tracking'])->assertOk();
});

test('un client connecté passe commande depuis la vitrine', function () {
    $client = User::factory()->create(['role' => 'client']);
    $j = $this->actingAs($client)->postJson(route('public.shops.order', $this->shop), $this->body)->assertCreated()->json();
    expect(Order::findOrFail($j['id'])->user_id)->toBe($client->id);
});
