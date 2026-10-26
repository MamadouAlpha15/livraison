<?php

use App\Models\AdCampaign as C;
use App\Support\Campaigns\CampaignStateMachine as SM;
use App\Support\Campaigns\InvalidCampaignTransition;
use Illuminate\Support\Carbon;
use Tests\Support\CampaignFixtures as F;

afterEach(fn () => Carbon::setTestNow());

test('parcours complet d\'une mise en avant', function () {
    $shop = F::shop();
    $c = F::campaign($shop, 'boost_produit', [F::product($shop)->id]);

    foreach ([C::STATUS_PAYEE, C::STATUS_PROGRAMMEE, C::STATUS_EN_COURS, C::STATUS_EN_PAUSE, C::STATUS_EN_COURS, C::STATUS_TERMINEE] as $to) {
        $c = SM::transition($c, $to);
    }

    expect($c->status)->toBe(C::STATUS_TERMINEE)
        ->and($c->paid_at)->not->toBeNull()
        ->and($c->launched_at)->not->toBeNull()
        ->and($c->finished_at)->not->toBeNull();
});

test('parcours complet d\'une campagne réseaux sociaux, avec une modification de visuels', function () {
    $c = F::campaign(F::shop(), 'social_starter', [], ['starts_on' => today()->addDays(3)]);

    foreach ([C::STATUS_PAYEE, C::STATUS_EN_PREPARATION, C::STATUS_VISUELS_A_VALIDER, C::STATUS_EN_PREPARATION,
              C::STATUS_VISUELS_A_VALIDER, C::STATUS_PROGRAMMEE, C::STATUS_EN_COURS, C::STATUS_TERMINEE] as $to) {
        $c = SM::transition($c, $to);
    }

    expect($c->status)->toBe(C::STATUS_TERMINEE)
        ->and($c->revision_count)->toBe(1)
        ->and($c->creatives_sent_at)->not->toBeNull();
});

test('transitions interdites', function (string $code, array $path, string $forbidden) {
    $c = F::campaign(F::shop(), $code, [], ['starts_on' => today()->addDays(3)]);
    foreach ($path as $to) {
        $c = SM::transition($c, $to);
    }
    expect(SM::can($c, $forbidden))->toBeFalse();
    SM::transition($c, $forbidden);
})->throws(InvalidCampaignTransition::class)->with([
    'non payée → en cours'                  => ['boutique_une', [], C::STATUS_EN_COURS],
    'boost payé → préparation des visuels'  => ['boutique_une', [C::STATUS_PAYEE], C::STATUS_EN_PREPARATION],
    'social payé → programmée directement'  => ['social_starter', [C::STATUS_PAYEE], C::STATUS_PROGRAMMEE],
    'refus après le lancement'              => ['boutique_une', [C::STATUS_PAYEE, C::STATUS_PROGRAMMEE, C::STATUS_EN_COURS], C::STATUS_REFUSEE],
    'annulation après le lancement'         => ['boutique_une', [C::STATUS_PAYEE, C::STATUS_PROGRAMMEE, C::STATUS_EN_COURS], C::STATUS_ANNULEE],
    'retour en arrière depuis terminée'     => ['boutique_une', [C::STATUS_PAYEE, C::STATUS_PROGRAMMEE, C::STATUS_EN_COURS, C::STATUS_TERMINEE], C::STATUS_EN_COURS],
]);

test('3e demande de modification des visuels refusée', function () {
    $c = F::campaign(F::shop(), 'social_pro', [], ['starts_on' => today()->addDays(3)]);
    $c = SM::transition($c, C::STATUS_PAYEE);
    $c = SM::transition($c, C::STATUS_EN_PREPARATION);
    for ($i = 0; $i < SM::MAX_REVISIONS; $i++) {
        $c = SM::transition($c, C::STATUS_VISUELS_A_VALIDER);
        $c = SM::transition($c, C::STATUS_EN_PREPARATION);
    }
    $c = SM::transition($c, C::STATUS_VISUELS_A_VALIDER);

    expect($c->revision_count)->toBe(2)
        ->and(SM::allowedTargets($c))->not->toContain(C::STATUS_EN_PREPARATION)
        ->and(SM::allowedTargets($c))->toContain(C::STATUS_PROGRAMMEE);
    SM::transition($c, C::STATUS_EN_PREPARATION);
})->throws(InvalidCampaignTransition::class, 'demandes de modification');

test('les jours de pause sont reportés à la fin de la campagne', function () {
    Carbon::setTestNow('2026-11-01 09:00');
    $c = F::campaign(F::shop(), 'boutique_une', [], ['starts_on' => '2026-11-01']);  // 1er → 7 novembre
    foreach ([C::STATUS_PAYEE, C::STATUS_PROGRAMMEE, C::STATUS_EN_COURS] as $to) {
        $c = SM::transition($c, $to);
    }

    Carbon::setTestNow('2026-11-03 15:00');
    $c = SM::transition($c, C::STATUS_EN_PAUSE, ['pause_reason' => 'rupture']);
    expect($c->pause_reason)->toBe('rupture')->and($c->paused_at)->not->toBeNull();

    Carbon::setTestNow('2026-11-06 08:00');                                           // 3 jours de pause
    $c = SM::transition($c, C::STATUS_EN_COURS);

    expect($c->ends_on->toDateString())->toBe('2026-11-10')
        ->and($c->paused_at)->toBeNull()
        ->and($c->pause_reason)->toBeNull();
});

test('le motif de refus est enregistré avec le statut', function () {
    $c = F::campaign(F::shop(), 'boutique_une');
    $c = SM::transition($c, C::STATUS_PAYEE);
    $c = SM::transition($c, C::STATUS_REFUSEE, ['refusal_reason' => 'Photo trompeuse']);

    expect($c->status)->toBe(C::STATUS_REFUSEE)
        ->and($c->refusal_reason)->toBe('Photo trompeuse')
        ->and($c->refused_at)->not->toBeNull();
});

test('deux changements simultanés : le second ne peut pas écraser le premier', function () {
    $c = SM::transition(F::campaign(F::shop(), 'boutique_une'), C::STATUS_PAYEE);
    $stale = C::find($c->id);                       // copie lue avant le changement
    SM::transition($c, C::STATUS_PROGRAMMEE);

    SM::transition($stale, C::STATUS_ANNULEE);      // l'autre requête croit encore « payée »
})->throws(InvalidCampaignTransition::class, 'a changé entre-temps');
