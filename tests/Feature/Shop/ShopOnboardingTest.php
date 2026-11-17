<?php

use App\Models\Affiliate;
use App\Models\Referral;
use App\Models\Shop;
use App\Models\User;
use App\Services\AffiliateService;

/* Création de boutique en 8 étapes (ShopOnboardingController) : numéro WhatsApp vérifié, devise du pays,
   réponses gardées, essai Pro, parrainage par lien, et jamais de deuxième boutique. */

function onbAnswers(array $over = []): array
{
    return $over + ['name' => 'Maison Kadi', 'experience' => 'reseaux', 'sales' => 'lt30', 'team' => 'seul',
        'delivery' => 'livreurs', 'country' => 'GN', 'whatsapp' => '622 12 34 56'];
}

beforeEach(function () {
    $this->owner = User::factory()->create(['role' => 'admin', 'country' => 'GN']);
});

test('la page « Bienvenue sur Shopio » s’ouvre pour un vendeur sans boutique, avec son pays choisi d’avance', function () {
    $this->actingAs($this->owner)->get(route('shop.create'))->assertOk()
        ->assertSee('Bienvenue sur Shopio')->assertSee('Tu es basé où ?', false)->assertSee('shop\/demarrer', false);
});

test('8 étapes : numéro guinéen vérifié, boutique créée avec slug, +224, GNF et réponses gardées', function () {
    $url = route('shop.onboard');
    // Un numéro guinéen qui ne commence pas par 6 est refusé
    $this->actingAs($this->owner)->postJson($url, onbAnswers(['whatsapp' => '522 12 34 56']))
        ->assertStatus(422)->assertJsonPath('errors.whatsapp.0', 'Ce numéro ne semble pas valide pour Guinée.');
    expect(Shop::where('user_id', $this->owner->id)->exists())->toBeFalse();

    $j = $this->actingAs($this->owner)->postJson($url, onbAnswers())->assertOk()->json();
    $shop = Shop::where('user_id', $this->owner->id)->sole();
    expect($j['redirect'])->toBe(route('boutique.dashboard'))->and($j['slug'])->toBe('maison-kadi')
        ->and($shop->slug)->toBe('maison-kadi')->and($shop->phone)->toBe('+224622123456')->and($shop->currency)->toBe('GNF')
        ->and($shop->is_approved)->toBeTrue()->and($shop->plan)->toBe('pro')
        ->and($shop->onboarding)->toMatchArray(['experience' => 'reseaux', 'sales' => 'lt30', 'team' => 'seul', 'delivery' => 'livreurs'])
        ->and($this->owner->fresh()->only(['shop_id', 'role_in_shop']))->toBe(['shop_id' => $shop->id, 'role_in_shop' => 'admin']);
});

test('jamais de deuxième boutique (double clic, retour arrière)', function () {
    $this->actingAs($this->owner)->postJson(route('shop.onboard'), onbAnswers())->assertOk();
    $this->owner->refresh();
    $this->actingAs($this->owner)->postJson(route('shop.onboard'), onbAnswers(['name' => 'Autre boutique']))
        ->assertOk()->assertJsonPath('redirect', route('boutique.dashboard'));
    expect(Shop::where('user_id', $this->owner->id)->count())->toBe(1);
});

test('parrainage : un vendeur venu par un lien d’affilié est rattaché à son parrain', function () {
    $affiliate = Affiliate::factory()->vendor()->create(['code' => 'AMINA47']);
    // (en test, les cookies d'une requête JSON ne partent qu'avec withCredentials ; le navigateur les envoie toujours)
    $this->actingAs($this->owner)->withCookie(AffiliateService::COOKIE, 'AMINA47')->withCredentials()
        ->postJson(route('shop.onboard'), onbAnswers())->assertOk();
    $shop = Shop::where('user_id', $this->owner->id)->sole();
    $referral = Referral::where('referred_shop_id', $shop->id)->sole();
    expect($referral->affiliate_id)->toBe($affiliate->id)->and($referral->source)->toBe('ami');
});
