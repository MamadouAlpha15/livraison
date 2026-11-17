<?php

use App\Models\User;
use Tests\Support\CampaignFixtures as F;

/* « Ma boutique » (/shop/{id}/edit) : une seule page, 4 parties l'une sous l'autre, un menu, un bouton Enregistrer. */

beforeEach(function () {
    $this->shop = F::shop();
    $this->owner = User::findOrFail($this->shop->user_id);
});

test('la page montre les 4 parties, le menu et un seul bouton Enregistrer', function () {
    $html = $this->actingAs($this->owner)->get(route('shop.edit', $this->shop))->assertOk()->getContent();
    foreach (['section-1', 'section-2', 'section-3', 'section-4', 'seNav', 'submitBtn'] as $id) {
        expect($html)->toContain('id="' . $id . '"');
    }
    foreach (['name', 'type', 'email', 'phone', 'address', 'country', 'currency', 'payout_wallet_type', 'payout_wallet_number', 'accent_color', 'description', 'meta_pixel_id', 'image'] as $field) {
        expect($html)->toContain('name="' . $field . '"');
    }
    // Plus d'étapes Précédent / Suivant
    expect($html)->not->toContain('id="stepNext"')->not->toContain('id="stepPrev"');
});

test('une autre boutique ne peut pas ouvrir la page', function () {
    $other = User::findOrFail(F::shop()->user_id);
    $this->actingAs($other)->get(route('shop.edit', $this->shop))->assertForbidden();
});

test('enregistrer garde le même fonctionnement', function () {
    $this->actingAs($this->owner)->put(route('shop.update', $this->shop), [
        'name' => 'Boutique Aminata', 'email' => 'aminata@test.invalid', 'type' => 'Vêtements',
        'phone' => '622 00 00 00', 'country' => 'GN', 'currency' => 'GNF', 'accent_color' => '#1F7CF6',
        'description' => 'Pagnes et wax livrés à Conakry.',
    ])->assertRedirect(route('shop.index', $this->shop->id));
    expect($this->shop->fresh())->name->toBe('Boutique Aminata')->type->toBe('Vêtements')->country->toBe('GN');
});
