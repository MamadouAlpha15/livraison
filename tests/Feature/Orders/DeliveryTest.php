<?php

use App\Models\DeliveryCompany;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\User;
use App\Services\PushService;
use Tests\Support\CampaignFixtures as F;

/*
 * Livraison d'une commande depuis sa fiche (page Commandes) : livreur de la boutique ou entreprise
 * de livraison. L'ancienne page « Livraisons » renvoie vers Commandes.
 */

beforeEach(function () {
    app()->instance(PushService::class, new class {
        public array $sent = [];
        public function sendToUser(User $user, string $title, string $body, int $badge = 0, string $url = '/'): void { $this->sent[] = [$user->id, $title]; }
    });
    $this->shop = F::shop();
    $this->owner = $this->shop->owner;
    $this->owner->forceFill(['shop_id' => $this->shop->id, 'role_in_shop' => 'admin'])->save();
    $this->order = Order::forceCreate(['shop_id' => $this->shop->id, 'total' => 150000, 'status' => Order::STATUS_EN_ATTENTE, 'client_name' => 'Mariama Diallo', 'client_phone' => '622458137']);
});

test('l\'ancienne page Livraisons renvoie vers Commandes, en gardant la recherche', function () {
    $this->actingAs($this->owner)->get(route('boutique.orders.livraisons', ['search' => $this->order->id]))
        ->assertRedirect(route('boutique.orders.index', ['q' => $this->order->id]));

    $this->actingAs($this->owner)->get(route('boutique.orders.index'))->assertOk()
        ->assertDontSee('🛵 Livraisons</a>', false)->assertSee('Mariama Diallo');
});

test('assigner un livreur de la boutique confirme la commande et le prévient', function () {
    $rider = User::factory()->create(['role' => 'livreur', 'shop_id' => $this->shop->id, 'name' => 'Ibrahima', 'is_available' => true]);
    $this->order->forceFill(['followup' => 'injoignable'])->save();

    $this->actingAs($this->owner)->postJson(route('boutique.orders.delivery', $this->order), ['kind' => 'rider', 'rider_id' => $rider->id])
        ->assertOk()
        ->assertJsonPath('order.delivery.kind', 'rider')
        ->assertJsonPath('order.delivery.name', 'Ibrahima')
        ->assertJsonPath('order.stage', 'confirmee');

    $o = $this->order->fresh();
    expect($o->livreur_id)->toBe($rider->id)->and($o->status)->toBe(Order::STATUS_CONFIRMEE)->and($o->followup)->toBeNull()
        ->and(collect(app(PushService::class)->sent)->pluck(0))->toContain($rider->id);
});

test('un livreur d\'une autre boutique est refusé', function () {
    $other = F::shop();
    $rider = User::factory()->create(['role' => 'livreur', 'shop_id' => $other->id]);

    $this->actingAs($this->owner)->postJson(route('boutique.orders.delivery', $this->order), ['kind' => 'rider', 'rider_id' => $rider->id])
        ->assertStatus(422)->assertJsonValidationErrors('rider_id');
    expect($this->order->fresh()->livreur_id)->toBeNull();
});

test('confier à une entreprise de livraison applique le prix de la zone et attend son acceptation', function () {
    $owner = User::factory()->create(['role' => 'company']);
    $company = DeliveryCompany::forceCreate(['name' => 'Conakry Express', 'user_id' => $owner->id, 'approved' => true, 'active' => true]);
    $zone = DeliveryZone::forceCreate(['delivery_company_id' => $company->id, 'name' => 'Ratoma', 'price' => 25000, 'active' => true]);

    $this->actingAs($this->owner)->get(route('boutique.orders.index'))->assertOk()->assertSee('Conakry Express');

    $this->actingAs($this->owner)->postJson(route('boutique.orders.delivery', $this->order), ['kind' => 'company', 'company_id' => $company->id, 'zone_id' => $zone->id])
        ->assertOk()
        ->assertJsonPath('order.delivery.kind', 'company')
        ->assertJsonPath('order.delivery.driver', null);

    $o = $this->order->fresh();
    expect($o->delivery_company_id)->toBe($company->id)->and((int) $o->delivery_fee)->toBe(25000)->and($o->status)->toBe(Order::STATUS_EN_ATTENTE);
});

test('une commande livrée ou d\'une autre boutique ne peut pas être assignée', function () {
    $rider = User::factory()->create(['role' => 'livreur', 'shop_id' => $this->shop->id]);
    $this->order->update(['status' => Order::STATUS_LIVREE]);
    $this->actingAs($this->owner)->postJson(route('boutique.orders.delivery', $this->order), ['kind' => 'rider', 'rider_id' => $rider->id])
        ->assertStatus(422)->assertJsonPath('message', 'Cette commande est déjà livrée.');

    $foreign = Order::forceCreate(['shop_id' => F::shop()->id, 'total' => 1000, 'status' => Order::STATUS_EN_ATTENTE]);
    $this->actingAs($this->owner)->postJson(route('boutique.orders.delivery', $foreign), ['kind' => 'rider', 'rider_id' => $rider->id])
        ->assertNotFound();
});
