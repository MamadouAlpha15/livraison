<?php

use App\Models\Order;
use App\Models\ShopTestimonial;
use App\Models\User;
use Tests\Support\CampaignFixtures as F;

/* Témoignages des vendeurs sur Shopio : écrits par le propriétaire, publiés par l'équipe Shopio,
   affichés sur la page d'accueil avec le vrai nombre de commandes livrées. Aucun avis inventé. */

beforeEach(function () {
    config(['shopio.support_whatsapp' => '']);
    $this->shop = F::shop();
    $this->shop->update(['slug' => 'maison-kadi', 'name' => 'Maison Kadi']);
    $this->owner = User::findOrFail($this->shop->user_id);
    $this->owner->forceFill(['shop_id' => $this->shop->id])->save();
    $this->tm = fn (array $over = []) => ShopTestimonial::create($over + [
        'shop_id' => $this->shop->id, 'user_id' => $this->owner->id, 'rating' => 5,
        'text' => 'Mes clients commandent en une minute et paient à la livraison.', 'author' => 'Kadiatou', 'author_role' => 'Fondatrice', 'status' => 'pending',
    ]);
});

test('un témoignage en attente n’apparaît pas sur la page d’accueil', function () {
    ($this->tm)();
    $this->get('/')->assertOk()->assertDontSee('Mes clients commandent en une minute')->assertDontSee('Ce qu’en disent les vendeurs', false);
});

test('un témoignage publié apparaît avec la boutique et son vrai nombre de commandes livrées', function () {
    ($this->tm)(['status' => 'approved', 'approved_at' => now()]);
    foreach (['livrée', 'livrée', 'livrée', 'en_attente'] as $st) {
        Order::forceCreate(['shop_id' => $this->shop->id, 'total' => 100000, 'status' => $st]);
    }
    $this->get('/')->assertOk()
        ->assertSee('Mes clients commandent en une minute')
        ->assertSee('Kadiatou, Fondatrice')
        ->assertSee('Maison Kadi')
        ->assertSee('3 commandes', false);
});

test('le vendeur écrit son avis ; toute modification le remet en attente', function () {
    $body = ['rating' => 5, 'text' => 'Shopio m’aide à vendre partout en Guinée, simplement.', 'author' => 'Kadiatou', 'consent' => '1'];
    $this->actingAs($this->owner)->get(route('boutique.testimonial.edit'))->assertOk();
    $this->actingAs($this->owner)->put(route('boutique.testimonial.update'), $body)->assertRedirect(route('boutique.testimonial.edit'));
    $t = ShopTestimonial::where('shop_id', $this->shop->id)->firstOrFail();
    expect($t->status)->toBe('pending');
    $t->update(['status' => 'approved', 'approved_at' => now()]);
    $this->actingAs($this->owner)->put(route('boutique.testimonial.update'), ['text' => 'Shopio m’aide à vendre partout, et mes clients paient à la livraison.'] + $body);
    expect($t->fresh()->status)->toBe('pending')->and($t->fresh()->approved_at)->toBeNull();
});

test('un employé de la boutique (pas le propriétaire) ne peut pas ouvrir « Ton avis sur Shopio »', function () {
    $staff = User::factory()->create(['role' => 'admin']);
    $staff->forceFill(['shop_id' => $this->shop->id])->save();
    $this->actingAs($staff)->get(route('boutique.testimonial.edit'))->assertForbidden();
    $employe = User::factory()->create(['role' => 'employe']);
    $employe->forceFill(['shop_id' => $this->shop->id])->save();
    $this->actingAs($employe)->get(route('boutique.testimonial.edit'))->assertForbidden();
});

test('seuls le superadmin et l’équipe Shopio peuvent publier un témoignage', function () {
    $t = ($this->tm)();
    $client = User::factory()->create(['role' => 'client']);
    foreach ([$this->owner, $client] as $u) {
        $this->actingAs($u)->post(route('admin.avis.testimonials.approve', $t))->assertForbidden();
    }
    expect($t->fresh()->status)->toBe('pending');
    foreach (['staff', 'superadmin'] as $role) {
        $t->update(['status' => 'pending']);
        $this->actingAs(User::factory()->create(['role' => $role]))->post(route('admin.avis.testimonials.approve', $t))->assertRedirect();
        expect($t->fresh()->status)->toBe('approved');
    }
});

test('le bouton WhatsApp du support n’apparaît que si le numéro est réglé', function () {
    $this->get('/')->assertOk()->assertDontSee('https://wa.me/', false);
    config(['shopio.support_whatsapp' => '224000000000']);
    $this->get('/')->assertOk()->assertSee('https://wa.me/224000000000', false);
});
