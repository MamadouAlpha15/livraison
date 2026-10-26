<?php

use App\Models\AdCampaign as C;
use App\Models\AdCampaignCreative as Creative;
use App\Models\AdCampaignPayment as Pay;
use App\Models\AdCampaignStat;
use App\Models\User;
use App\Support\Campaigns\CampaignCheckout;
use App\Support\Campaigns\CampaignPricing;
use App\Support\Campaigns\ShopCredit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    config(['chapchappay.api_key' => '']);
    Storage::fake('public');
});

function team(string $role = 'marketing'): User
{
    return User::factory()->create(['role' => $role]);
}

/** Campagne réseaux sociaux payée (paiement manuel) : en préparation. */
function paidSocial($shop = null, string $code = 'social_starter', array $attributes = []): C
{
    $shop ??= F::shop();
    $campaign = F::campaign($shop, $code, [], $attributes + ['starts_on' => today()->addDays(3)->toDateString(),
        'objective' => 'messages', 'targeting' => C::DEFAULT_TARGETING]);
    return app(CampaignCheckout::class)->markPaidManually($campaign, team('staff'));
}

function socialForm(array $over = []): array
{
    return $over + [
        'package' => 'social_starter', 'days' => 7, 'starts_on' => today()->addDays(2)->toDateString(),
        'scope' => 'shop', 'payment' => 'mobile_money', 'accept_rules' => '1',
        'objective' => 'visites', 'communes' => ['Ratoma'], 'age_min' => 20, 'age_max' => 35, 'genre' => 'femmes',
    ];
}

/* ── Commande côté vendeur ── */

test('le vendeur commande une campagne réseaux sociaux avec son objectif et son public', function () {
    $shop = F::shop();
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), socialForm())->assertRedirect();

    $c = $shop->adCampaigns()->sole();
    expect($c->type)->toBe('social')
        ->and($c->objective)->toBe('visites')
        ->and($c->price_gnf)->toBe(300000)
        ->and($c->ad_budget_gnf)->toBe(200000)
        // Starter : pas de choix de commune, toute la ville est visée
        ->and($c->targeting['communes'])->toBe(C::COMMUNES)
        ->and($c->targeting['age_min'])->toBe(20)
        ->and($c->targeting['genre'])->toBe('femmes');
});

test('la formule Premium garde les communes choisies', function () {
    $shop = F::shop();
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), socialForm(['package' => 'social_premium', 'days' => 30, 'communes' => ['Ratoma', 'Matoto']]));

    expect($shop->adCampaigns()->sole()->targeting['communes'])->toBe(['Ratoma', 'Matoto']);
});

test('une campagne réseaux sociaux demande un objectif et commence dans 2 jours au plus tôt', function () {
    $shop = F::shop();
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), socialForm(['objective' => null]))->assertSessionHasErrors('objective');
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), socialForm(['starts_on' => today()->addDay()->toDateString()]))->assertSessionHasErrors('campaign');
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.store'), socialForm(['age_min' => 40, 'age_max' => 30]))->assertSessionHasErrors('age_max');

    expect($shop->adCampaigns()->count())->toBe(0);
});

test('l\'assistant propose les étapes Objectif et Public', function () {
    $shop = F::shop();
    $this->actingAs($shop->owner)->get(route('boutique.campaigns.create', ['type' => 'social']))->assertOk()
        ->assertSee('Que veux-tu obtenir ?')->assertSee('Qui doit voir ta pub ?')->assertDontSee('Bientôt disponible');
});

/* ── Accès ── */

test('les rôles superadmin, staff et marketing ouvrent les campagnes ; marketing ne voit pas le reste du panneau', function () {
    foreach (['superadmin', 'staff', 'marketing'] as $role) {
        $this->actingAs(team($role))->get(route('admin.campaigns.index'))->assertOk()->assertSee('Campagnes');
    }
    $this->actingAs(team('marketing'))->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs(team('marketing'))->get('/dashboard')->assertRedirect(route('admin.campaigns.index'));

    $this->actingAs(F::shop()->owner)->get(route('admin.campaigns.index'))->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'client']))->get(route('admin.campaigns.index'))->assertForbidden();
});

test('le superadmin crée un compte marketing', function () {
    $this->actingAs(team('superadmin'))->post(route('admin.users.store-staff'), [
        'name' => 'Aïssatou Marketing', 'email' => 'marketing@test.invalid', 'password' => 'motdepasse1', 'role' => 'marketing',
    ])->assertRedirect();

    expect(User::where('email', 'marketing@test.invalid')->value('role'))->toBe('marketing');
});

/* ── Visuels ── */

test('visuels : dépôt, envoi, modification demandée puis validation', function () {
    $campaign = paidSocial();
    $owner = $campaign->shop->owner;
    $admin = team();
    expect($campaign->status)->toBe(C::STATUS_EN_PREPARATION);

    // Rien à envoyer tant qu'aucun visuel n'est déposé
    $this->actingAs($admin)->post(route('admin.campaigns.creatives.send', $campaign))->assertSessionHas('error');

    $this->actingAs($admin)->post(route('admin.campaigns.creatives.store', $campaign), [
        'files' => [UploadedFile::fake()->image('pub1.jpg', 1080, 1080)], 'caption' => 'Nouvelle collection',
    ])->assertSessionHas('success');
    $creative = $campaign->creatives()->sole();
    Storage::disk('public')->assertExists($creative->file_path);

    $this->actingAs($admin)->post(route('admin.campaigns.creatives.send', $campaign));
    expect($campaign->fresh()->status)->toBe(C::STATUS_VISUELS_A_VALIDER);

    // Le vendeur voit les visuels et demande une modification
    $this->actingAs($owner)->get(route('boutique.campaigns.show', $campaign))->assertOk()->assertSee('Valider les visuels')->assertSee('Nouvelle collection');
    $this->actingAs($owner)->post(route('boutique.campaigns.creatives.changes', $campaign), ['comment' => 'Mets le prix plus en grand'])->assertSessionHas('success');

    $campaign->refresh();
    expect($campaign->status)->toBe(C::STATUS_EN_PREPARATION)
        ->and($campaign->revision_count)->toBe(1)
        ->and($creative->fresh()->status)->toBe(Creative::STATUS_MODIFICATION_DEMANDEE)
        ->and($creative->fresh()->seller_comment)->toBe('Mets le prix plus en grand');

    // Deuxième proposition, validée
    $this->actingAs($admin)->post(route('admin.campaigns.creatives.store', $campaign), ['files' => [UploadedFile::fake()->image('pub2.png')]]);
    $this->actingAs($admin)->post(route('admin.campaigns.creatives.send', $campaign));
    $this->actingAs($owner)->post(route('boutique.campaigns.creatives.approve', $campaign))->assertSessionHas('success');

    $campaign->refresh();
    expect($campaign->status)->toBe(C::STATUS_PROGRAMMEE)
        ->and($campaign->creatives()->where('round', 2)->sole()->status)->toBe(Creative::STATUS_VALIDEE);
});

test('pas plus de 2 demandes de modification', function () {
    $campaign = paidSocial(attributes: ['revision_count' => 2]);
    $campaign->creatives()->create(['file_path' => 'x.jpg', 'type' => 'image', 'round' => 3]);
    app(\App\Support\Campaigns\CampaignAdmin::class)->sendCreatives($campaign);

    $this->actingAs($campaign->shop->owner)->post(route('boutique.campaigns.creatives.changes', $campaign), ['comment' => 'Encore une fois'])
        ->assertSessionHas('error');
    expect($campaign->fresh()->status)->toBe(C::STATUS_VISUELS_A_VALIDER);
});

test('un vendeur ne valide pas les visuels d\'une autre boutique', function () {
    $campaign = paidSocial();
    $campaign->creatives()->create(['file_path' => 'x.jpg', 'type' => 'image', 'round' => 1]);
    app(\App\Support\Campaigns\CampaignAdmin::class)->sendCreatives($campaign);

    $this->actingAs(F::shop()->owner)->post(route('boutique.campaigns.creatives.approve', $campaign))->assertNotFound();
    expect($campaign->fresh()->status)->toBe(C::STATUS_VISUELS_A_VALIDER);
});

test('un fichier qui n\'est ni une image ni une vidéo est refusé', function () {
    $campaign = paidSocial();
    $this->actingAs(team())->post(route('admin.campaigns.creatives.store', $campaign), [
        'files' => [UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload')],
    ])->assertSessionHasErrors('files.0');
    expect($campaign->creatives()->count())->toBe(0);
});

/* ── Mise en ligne et statistiques ── */

test('mise en ligne : l\'identifiant de la pub est obligatoire, une mise en ligne tardive garde toute la durée', function () {
    $campaign = paidSocial(attributes: ['starts_on' => today()->subDays(2)->toDateString()]);
    \App\Support\Campaigns\CampaignStateMachine::transition($campaign, C::STATUS_VISUELS_A_VALIDER);
    \App\Support\Campaigns\CampaignStateMachine::transition($campaign->fresh(), C::STATUS_PROGRAMMEE);
    $admin = team();

    $this->actingAs($admin)->post(route('admin.campaigns.launch', $campaign), ['external_ref' => ''])->assertSessionHas('error');
    $this->actingAs($admin)->post(route('admin.campaigns.launch', $campaign), ['external_ref' => '120210000123456'])->assertSessionHas('success');

    $campaign->refresh();
    expect($campaign->status)->toBe(C::STATUS_EN_COURS)
        ->and($campaign->external_ref)->toBe('120210000123456')
        ->and($campaign->starts_on->toDateString())->toBe(today()->toDateString())
        ->and($campaign->ends_on->toDateString())->toBe(CampaignPricing::endDate(today(), 7)->toDateString());
});

test('une pub mise en ligne avant la date prévue commence le jour même', function () {
    $campaign = paidSocial(attributes: ['starts_on' => today()->addDays(3)->toDateString()]);
    \App\Support\Campaigns\CampaignStateMachine::transition($campaign, C::STATUS_VISUELS_A_VALIDER);
    \App\Support\Campaigns\CampaignStateMachine::transition($campaign->fresh(), C::STATUS_PROGRAMMEE);

    $campaign = app(\App\Support\Campaigns\CampaignAdmin::class)->launch($campaign->fresh(), 'tt-778899');

    expect($campaign->starts_on->toDateString())->toBe(today()->toDateString())
        ->and($campaign->duration_days)->toBe(7)
        ->and($campaign->ends_on->toDateString())->toBe(CampaignPricing::endDate(today(), 7)->toDateString());
});

test('l\'équipe saisit les affichages et la dépense d\'un jour, une seule ligne par jour', function () {
    $campaign = paidSocial();
    $campaign->forceFill(['status' => C::STATUS_EN_COURS, 'launched_at' => now(), 'starts_on' => today()->subDay(), 'ends_on' => today()->addDays(5)])->save();
    $admin = team();

    $this->actingAs($admin)->post(route('admin.campaigns.stats.store', $campaign), ['day' => today()->toDateString(), 'impressions' => 1200, 'spend_gnf' => 25000]);
    $this->actingAs($admin)->post(route('admin.campaigns.stats.store', $campaign), ['day' => today()->toDateString(), 'impressions' => 1500, 'spend_gnf' => 30000]);
    $this->actingAs($admin)->post(route('admin.campaigns.stats.store', $campaign), ['day' => today()->addDay()->toDateString(), 'impressions' => 5, 'spend_gnf' => 0])
        ->assertSessionHas('error');

    $row = AdCampaignStat::where('ad_campaign_id', $campaign->id)->sole();
    expect($row->source)->toBe('admin')->and((int) $row->impressions)->toBe(1500)->and((int) $row->spend_gnf)->toBe(30000);
    $this->actingAs($campaign->shop->owner)->get(route('boutique.campaigns.show', $campaign))->assertSee("1\u{00A0}500", false);
});

/* ── Refus, paiement manuel, pause, clôture ── */

test('refus : motif obligatoire et remboursement intégral sur le Crédit Shopio', function () {
    $campaign = paidSocial();
    $admin = team();

    $this->actingAs($admin)->post(route('admin.campaigns.refuse', $campaign), ['reason' => ''])->assertSessionHasErrors('reason');
    $this->actingAs($admin)->post(route('admin.campaigns.refuse', $campaign), ['reason' => 'Produit interdit à la publicité'])->assertSessionHas('success');

    $campaign->refresh();
    expect($campaign->status)->toBe(C::STATUS_REFUSEE)
        ->and($campaign->refusal_reason)->toBe('Produit interdit à la publicité')
        ->and(ShopCredit::balance($campaign->shop))->toBe(300000)
        ->and($campaign->payments()->sole()->status)->toBe(Pay::STATUS_REMBOURSE);
    $this->actingAs($campaign->shop->owner)->get(route('boutique.campaigns.show', $campaign))->assertSee('Produit interdit à la publicité');
});

test('une campagne lancée ne peut plus être refusée', function () {
    $shop = F::shop();
    $campaign = F::campaign($shop, 'boost_produit', [F::product($shop)->id]);
    app(CampaignCheckout::class)->markPaidManually($campaign, team('staff'));
    expect($campaign->fresh()->status)->toBe(C::STATUS_EN_COURS);

    $this->actingAs(team())->post(route('admin.campaigns.refuse', $campaign), ['reason' => 'Trop tard pour refuser'])->assertSessionHas('error');
    expect($campaign->fresh()->status)->toBe(C::STATUS_EN_COURS);
});

test('paiement manuel enregistré par l\'équipe', function () {
    $shop = F::shop();
    $campaign = F::campaign($shop, 'boost_produit', [F::product($shop)->id]);
    $admin = team('staff');

    $this->actingAs($admin)->post(route('admin.campaigns.mark-paid', $campaign), ['reference' => 'REÇU-42'])->assertSessionHas('success');

    $pay = $campaign->payments()->sole();
    expect($pay->method)->toBe(Pay::METHOD_MANUEL)->and($pay->recorded_by)->toBe($admin->id)->and($pay->transaction_ref)->toBe('REÇU-42')
        ->and($campaign->fresh()->status)->toBe(C::STATUS_EN_COURS);
});

test('pause de l\'équipe : le vendeur ne la lève pas, l\'équipe relance puis clôture', function () {
    $shop = F::shop();
    $campaign = F::campaign($shop, 'boost_produit', [F::product($shop)->id]);
    app(CampaignCheckout::class)->markPaidManually($campaign, team('staff'));
    $admin = team();

    $this->actingAs($admin)->post(route('admin.campaigns.pause', $campaign));
    expect($campaign->fresh()->pause_reason)->toBe('admin');
    $this->actingAs($shop->owner)->post(route('boutique.campaigns.resume', $campaign))->assertSessionHas('error');

    $this->actingAs($admin)->post(route('admin.campaigns.resume', $campaign));
    expect($campaign->fresh()->status)->toBe(C::STATUS_EN_COURS);
    $this->actingAs($admin)->post(route('admin.campaigns.close', $campaign));
    expect($campaign->fresh()->status)->toBe(C::STATUS_TERMINEE);
});

test('le vendeur ne met pas lui-même en pause une pub réseaux sociaux', function () {
    $campaign = paidSocial();
    $campaign->forceFill(['status' => C::STATUS_EN_COURS, 'launched_at' => now()])->save();

    $this->actingAs($campaign->shop->owner)->post(route('boutique.campaigns.pause', $campaign))->assertSessionHas('error');
    expect($campaign->fresh()->status)->toBe(C::STATUS_EN_COURS);
});

/* ── Formules ── */

test('l\'équipe change les prix : les nouvelles commandes les utilisent', function () {
    $boost = F::package('boost_produit');
    $social = F::package('social_pro');
    $admin = team();

    $this->actingAs($admin)->put(route('admin.campaigns.packages.update', $boost), [
        'name' => 'Boost produit', 'is_active' => '1', 'max_actives' => 8, 'prices' => [3 => 30000, 7 => 60000, 30 => 180000],
    ])->assertRedirect(route('admin.campaigns.packages'));
    $this->actingAs($admin)->put(route('admin.campaigns.packages.update', $social), [
        'name' => 'Pro', 'is_active' => '0', 'ad_budget_gnf' => 600000, 'fee_gnf' => 250000,
    ]);

    expect(CampaignPricing::quote($boost->fresh(), 7)['price_gnf'])->toBe(60000)
        ->and($boost->fresh()->max_actives)->toBe(8)
        ->and($social->fresh()->priceFor(14))->toBe(850000)
        ->and($social->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->put(route('admin.campaigns.packages.update', $boost), [
        'name' => 'Boost produit', 'max_actives' => 8, 'prices' => [3 => 10, 7 => 60000, 30 => 180000],
    ])->assertSessionHasErrors('prices.3');
});

/* ── Pages ── */

test('les pages de l\'équipe s\'affichent, avec ce qui est à traiter', function () {
    $campaign = paidSocial();
    $marketing = team();

    $this->actingAs($marketing)->get(route('admin.campaigns.index', ['filtre' => 'a-traiter']))->assertOk()
        ->assertSee($campaign->shop->name)->assertSee('à préparer');
    $this->actingAs($marketing)->get(route('admin.campaigns.show', $campaign))->assertOk()
        ->assertSee('Prépare les visuels')->assertSee('?c=' . $campaign->id, false)->assertDontSee('Panneau admin');
    $this->actingAs($marketing)->get(route('admin.campaigns.packages'))->assertOk()->assertSee('Budget pub');
    $this->actingAs($marketing)->get(route('admin.campaigns.index', ['q' => $campaign->shop->name, 'type' => 'social']))->assertOk()->assertSee('n°' . $campaign->id);
});
