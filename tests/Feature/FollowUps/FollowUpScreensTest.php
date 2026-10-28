<?php

use App\Models\FollowUpRule;
use App\Models\PromoCode;
use App\Models\User;
use App\Services\FollowUp\FollowUpCatalog as Cat;
use App\Support\Ai\TextModel;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->ai = new class implements TextModel {
        public array $calls = [];
        public function complete(string $system, array $messages, int $maxTokens = 1500): string
        {
            $this->calls[] = $messages[0]['content'];
            return 'Salut {prenom_client}, ton offre pour {produit} à {prix_negocie} t\'attend !';
        }
    };
    app()->instance(TextModel::class, $this->ai);
    $this->shop = F::shop();
    $this->vendor = $this->shop->owner;
    $this->vendor->forceFill(['shop_id' => $this->shop->id])->save();
    F::product($this->shop, ['name' => 'Robe en wax', 'price' => 150000]);
});

function ruleForm(array $over = []): array
{
    return $over + [
        'enabled' => '1', 'delay_value' => 3, 'delay_unit' => 'heures', 'max_attempts' => 2, 'interval_value' => 1, 'interval_unit' => 'jours',
        'template' => 'Bonjour {prenom_client}, {produit} à {prix_negocie}.', 'tone' => 'amical', 'locale' => 'fr',
        'send_from_hour' => 9, 'send_to_hour' => 20, 'excluded_days' => [0], 'channels' => ['messaging', 'in_app'],
    ];
}

test('la page Relances liste les situations avec leurs réglages et les chiffres', function () {
    $this->actingAs($this->vendor)->get(route('boutique.followups.index'))->assertOk()
        ->assertSee('Offre de prix sans réponse')->assertSee('Commande à confirmer')->assertSee('Produit de nouveau en stock')
        ->assertDontSee('Vendeur inactif')
        ->assertSee('Relances envoyées');
});

test('le vendeur règle une relance : délais, texte, ton, heures, jours, canaux, code promo de sa boutique', function () {
    $promo = PromoCode::forceCreate(['shop_id' => $this->shop->id, 'code' => 'WAX10', 'type' => 'percent', 'value' => 10, 'is_active' => true]);
    $other = PromoCode::forceCreate(['shop_id' => F::shop()->id, 'code' => 'AUTRE', 'type' => 'percent', 'value' => 10, 'is_active' => true]);

    $this->actingAs($this->vendor)->put(route('boutique.followups.update', Cat::OFFER), ruleForm(['promo_code_id' => $other->id]))
        ->assertSessionHasErrors('promo_code_id');

    $this->actingAs($this->vendor)->put(route('boutique.followups.update', Cat::OFFER), ruleForm(['promo_code_id' => $promo->id]))
        ->assertRedirect(route('boutique.followups.index'));

    $rule = FollowUpRule::where('shop_id', $this->shop->id)->where('type', Cat::OFFER)->sole();
    expect($rule->only(['delay_minutes', 'max_attempts', 'interval_minutes', 'send_from_hour', 'send_to_hour', 'excluded_days', 'channels', 'promo_code_id']))
        ->toBe(['delay_minutes' => 180, 'max_attempts' => 2, 'interval_minutes' => 1440, 'send_from_hour' => 9, 'send_to_hour' => 20,
            'excluded_days' => [0], 'channels' => ['messaging', 'in_app'], 'promo_code_id' => $promo->id])
        ->and($rule->template)->toBe('Bonjour {prenom_client}, {produit} à {prix_negocie}.');
});

test('texte par défaut laissé tel quel : la relance suit le ton choisi', function () {
    $this->actingAs($this->vendor)->put(route('boutique.followups.update', Cat::OFFER),
        ruleForm(['template' => Cat::template(Cat::OFFER, 'urgent'), 'tone' => 'urgent']))->assertRedirect();
    expect(FollowUpRule::where('type', Cat::OFFER)->sole()->template)->toBeNull();
});

test('validation : variable inconnue, heures incohérentes, aucun canal', function () {
    $this->actingAs($this->vendor)->put(route('boutique.followups.update', Cat::OFFER),
        ruleForm(['template' => 'Salut {nom_client}', 'send_from_hour' => 20, 'send_to_hour' => 9, 'channels' => []]))
        ->assertSessionHasErrors(['template', 'send_to_hour', 'channels']);
});

test('activer ou désactiver une situation en un geste', function () {
    $this->actingAs($this->vendor)->post(route('boutique.followups.toggle', Cat::ABANDONED))->assertRedirect();
    expect(FollowUpRule::resolve($this->shop->id, Cat::ABANDONED)->enabled)->toBeTrue();
    $this->actingAs($this->vendor)->post(route('boutique.followups.toggle', Cat::ABANDONED));
    expect(FollowUpRule::resolve($this->shop->id, Cat::ABANDONED)->enabled)->toBeFalse();
});

test('aperçu avec des données d\'exemple, et « Générer avec Shopio IA »', function () {
    $this->actingAs($this->vendor)->postJson(route('boutique.followups.preview', Cat::OFFER), ['template' => 'Salut {prenom_client}, {produit} à {prix}.', 'tone' => 'amical'])
        ->assertOk()->assertJsonPath('preview', "Salut Aïssatou, Robe en wax à 150\u{00A0}000\u{00A0}GNF.\n\nRéponds STOP pour ne plus recevoir ces rappels.");

    $this->actingAs($this->vendor)->postJson(route('boutique.followups.generate', Cat::OFFER), ['tone' => 'amical', 'locale' => 'fr', 'wish' => 'parler de la livraison'])
        ->assertOk()->assertJsonPath('template', 'Salut {prenom_client}, ton offre pour {produit} à {prix_negocie} t\'attend !');
    expect($this->ai->calls[0])->toContain('Robe en wax')->toContain('Offre de prix sans réponse')->toContain('parler de la livraison');
});

test('réservé au propriétaire de la boutique ; l\'admin règle la relance des vendeurs inactifs', function () {
    $client = User::factory()->create(['role' => 'client']);
    $this->actingAs($client)->get(route('boutique.followups.index'))->assertForbidden();
    $this->actingAs($this->vendor)->get(route('boutique.followups.edit', Cat::INACTIVE_VENDOR))->assertNotFound();

    $staff = User::factory()->create(['role' => 'staff']);
    $this->actingAs($staff)->put(route('admin.reminders.vendor-rule'), [
        'enabled' => '1', 'delay_value' => 10, 'delay_unit' => 'jours', 'max_attempts' => 2, 'interval_value' => 5, 'interval_unit' => 'jours',
        'tone' => 'amical', 'locale' => 'fr', 'send_from_hour' => 8, 'send_to_hour' => 21, 'channels' => ['in_app'],
        'template' => Cat::template(Cat::INACTIVE_VENDOR),
    ])->assertSessionHasNoErrors();
    expect(FollowUpRule::whereNull('shop_id')->where('type', Cat::INACTIVE_VENDOR)->sole()->only(['delay_minutes', 'template']))
        ->toBe(['delay_minutes' => 14400, 'template' => null]);
    $this->actingAs($staff)->get(route('admin.reminders.index'))->assertOk()->assertSee('Vendeurs inactifs');
});
