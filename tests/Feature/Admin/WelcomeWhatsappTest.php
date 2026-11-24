<?php

use App\Models\Shop;
use App\Models\User;
use App\Support\WelcomeWhatsapp;
use Illuminate\Support\Facades\Storage;

/* Bienvenue WhatsApp : l'équipe Shopio écrit à chaque nouvel inscrit (lien wa.me, envoi manuel). Aucun envoi automatique. */

beforeEach(function () {
    Storage::fake('local');
    $this->admin = User::factory()->create(['role' => 'superadmin', 'name' => 'Équipe Shopio']);
    $this->vendor = User::factory()->create(['role' => 'admin', 'name' => 'Kadiatou Barry', 'phone' => '620000001', 'country' => 'GN']);
    $this->shop = Shop::forceCreate(['user_id' => $this->vendor->id, 'name' => 'Maison Kadi', 'slug' => 'maison-kadi', 'email' => 'kadi@test.invalid', 'phone' => '+224620000009', 'country' => 'GN', 'is_approved' => true]);
    $this->vendor->forceFill(['shop_id' => $this->shop->id])->save();
    $this->client = User::factory()->create(['role' => 'client', 'name' => 'Mamadou Diallo', 'phone' => '620 00 00 02', 'country' => 'GN']);
});

test('numéros : indicatif du pays ajouté, + et 00 gardés, numéro trop court refusé', function () {
    expect(WelcomeWhatsapp::number('+224620000001', 'GN'))->toBe('224620000001')
        ->and(WelcomeWhatsapp::number('620 00 00 02', 'GN'))->toBe('224620000002')
        ->and(WelcomeWhatsapp::number('00224 620000003', 'GN'))->toBe('224620000003')
        ->and(WelcomeWhatsapp::number('771234567', 'SN'))->toBe('221771234567')
        ->and(WelcomeWhatsapp::number('0612345678', 'FR'))->toBe('33612345678')
        ->and(WelcomeWhatsapp::number('12', 'GN'))->toBeNull()
        ->and(WelcomeWhatsapp::number('', 'GN'))->toBeNull();
});

test('la page liste les inscrits avec un lien wa.me : numéro de la boutique, prénom et boutique dans le message', function () {
    $html = $this->actingAs($this->admin)->get(route('admin.welcome.index'))->assertOk()
        ->assertSee('Bienvenue WhatsApp')->assertSee('Kadiatou Barry')->assertSee('Mamadou Diallo')->getContent();
    $vendorMsg = WelcomeWhatsapp::message($this->vendor->fresh());
    expect($vendorMsg)->toStartWith('Bonjour Kadiatou')->toContain('« Maison Kadi »')->not->toContain('{')
        ->and($html)->toContain(e('https://wa.me/224620000009?text=' . rawurlencode($vendorMsg)))   // numéro de la boutique
        ->and($html)->toContain('https://wa.me/224620000002?text=');                              // numéro du client, indicatif ajouté
    // Filtres : vendeurs seulement
    $this->actingAs($this->admin)->get(route('admin.welcome.index', ['qui' => 'vendor']))->assertSee('Kadiatou Barry')->assertDontSee('Mamadou Diallo');
});

test('vendeur sans boutique : « Boutique pas encore créée », pas de lien ; sans numéro : « Marquer contacté »', function () {
    $noShop = User::factory()->create(['role' => 'admin', 'name' => 'Sans Boutique', 'phone' => '620000005', 'country' => 'GN']);
    $noPhone = User::factory()->create(['role' => 'client', 'name' => 'Sans Numero', 'phone' => null, 'country' => 'GN']);
    expect(WelcomeWhatsapp::link($noShop))->toBeNull()->and(WelcomeWhatsapp::link($noPhone))->toBeNull();
    $this->actingAs($this->admin)->get(route('admin.welcome.index'))->assertSee('Boutique pas encore créée')->assertSee('Pas de numéro')->assertSee('Marquer contacté');
});

test('marquer contacté puis annuler ; la personne passe dans « Déjà contactés »', function () {
    $url = route('admin.welcome.mark', $this->client);
    $j = $this->actingAs($this->admin)->postJson($url, ['done' => true])->assertOk()->json();
    expect($j['done'])->toBeTrue()->and($j['pending'])->toBe(1)->and($this->client->fresh()->welcome_sent_by)->toBe($this->admin->id);
    $this->actingAs($this->admin)->get(route('admin.welcome.index'))->assertDontSee('Mamadou Diallo');
    $this->actingAs($this->admin)->get(route('admin.welcome.index', ['vue' => 'done']))->assertSee('Mamadou Diallo')->assertSee('Contacté le');
    $j = $this->actingAs($this->admin)->postJson($url, ['done' => false])->assertOk()->json();
    expect($j['pending'])->toBe(2)->and($this->client->fresh()->welcome_sent_at)->toBeNull();
    // Un compte qui n'est ni vendeur ni client : 404
    $this->actingAs($this->admin)->postJson(route('admin.welcome.mark', $this->admin), ['done' => true])->assertNotFound();
});

test('messages : enregistrés, nettoyés, puis remis par défaut', function () {
    $this->actingAs($this->admin)->put(route('admin.welcome.texts'), ['vendor' => "Salut {prenom} <b>!</b>\r\nTa boutique : {boutique}", 'client' => 'Bonjour {prenom}'])->assertRedirect();
    expect(WelcomeWhatsapp::texts()['vendor'])->toBe("Salut {prenom} !\nTa boutique : {boutique}")
        ->and(WelcomeWhatsapp::message($this->vendor->fresh()))->toBe("Salut Kadiatou !\nTa boutique : Maison Kadi");
    $this->actingAs($this->admin)->put(route('admin.welcome.texts'), ['vendor' => '', 'client' => 'x'])->assertSessionHasErrors('vendor');
    $this->actingAs($this->admin)->put(route('admin.welcome.texts'), ['reset' => 1])->assertRedirect();
    expect(WelcomeWhatsapp::texts())->toBe(WelcomeWhatsapp::DEFAULTS);
});

test('réservé à l’équipe Shopio, et la pastille du menu montre les personnes à contacter', function () {
    $this->actingAs($this->vendor)->get(route('admin.welcome.index'))->assertForbidden();
    $this->actingAs($this->client)->postJson(route('admin.welcome.mark', $this->client), ['done' => true])->assertForbidden();
    expect($this->actingAs($this->admin)->get(route('admin.users.index'))->assertOk()->getContent())
        ->toMatch('#data-wl-badge[^>]*display:inline-flex[^>]*>2</span>#');
});
