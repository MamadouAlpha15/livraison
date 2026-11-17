<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Tests\Support\CampaignFixtures as F;

/* Fiche d'un client (/boutique/clients/{id}) : ses commandes dans cette boutique, comme sur la page Commandes. */

beforeEach(function () {
    $this->shop = F::shop();
    $this->owner = User::findOrFail($this->shop->user_id);
    $this->owner->forceFill(['shop_id' => $this->shop->id])->save();
    $this->client = User::factory()->create(['role' => 'client', 'name' => 'Mariama Diallo']);
    $p = F::product($this->shop, ['name' => 'Sac en wax', 'price' => 150000]);
    foreach ([Order::STATUS_EN_ATTENTE, Order::STATUS_LIVREE] as $i => $st) {
        $o = Order::forceCreate(['user_id' => $this->client->id, 'shop_id' => $this->shop->id, 'total' => 150000 * ($i + 1), 'status' => $st, 'delivery_destination' => 'Kaloum', 'client_phone' => '622123456']);
        OrderItem::create(['order_id' => $o->id, 'product_id' => $p->id, 'quantity' => $i + 1, 'price' => 150000]);
    }
});

test('la fiche montre les chiffres calculés et les commandes avec les étapes de la page Commandes', function () {
    $html = $this->actingAs($this->owner->fresh())->get(route('boutique.clients.show', $this->client))->assertOk()->getContent();
    expect($html)->toContain('Mariama Diallo')
        ->toContain('450 000')            // total dépensé : 150 000 + 300 000
        ->toContain('Non traitée')->toContain('Livrée')
        ->toContain('Sac en wax')
        ->toContain('commande=')          // chaque commande s'ouvre sur la page Commandes
        ->not->toContain('En_attente');
});

test('une personne qui n’a jamais commandé dans la boutique donne 404', function () {
    $stranger = User::factory()->create(['role' => 'client']);
    $this->actingAs($this->owner->fresh())->get(route('boutique.clients.show', $stranger))->assertNotFound();
});
