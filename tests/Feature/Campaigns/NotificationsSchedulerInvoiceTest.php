<?php

use App\Models\AdCampaign as C;
use App\Models\AdCampaignPayment as Pay;
use App\Models\User;
use App\Notifications\CampaignNotification;
use App\Support\Campaigns\CampaignAdmin;
use App\Support\Campaigns\CampaignCheckout;
use App\Support\Campaigns\CampaignScheduler;
use App\Support\Campaigns\CampaignStateMachine;
use App\Support\Campaigns\ShopCredit;
use App\Support\Sms\SmsSender;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    config(['chapchappay.api_key' => '']);
    // SMS capturés au lieu d'être écrits dans le journal
    $this->sms = new class implements SmsSender {
        public array $sent = [];
        public function send(string $phone, string $message): bool { $this->sent[] = [$phone, $message]; return true; }
    };
    app()->instance(SmsSender::class, $this->sms);
});

function campaignEvents(User $user): array
{
    return $user->notifications()->where('type', CampaignNotification::class)->get()->pluck('data.event')->all();
}

function paidBoost(array $productAttributes = [], array $campaignAttributes = []): C
{
    $shop = F::shop();
    $product = F::product($shop, $productAttributes);
    $campaign = F::campaign($shop, 'boost_produit', [$product->id], $campaignAttributes);
    return app(CampaignCheckout::class)->markPaidManually($campaign, User::factory()->create(['role' => 'staff']));
}

function socialWithCreatives(): C
{
    $shop = F::shop();
    $shop->owner->forceFill(['phone' => '622458137'])->save();
    $campaign = F::campaign($shop, 'social_starter', [], ['starts_on' => today()->addDays(3)->toDateString(), 'objective' => 'messages', 'targeting' => C::DEFAULT_TARGETING]);
    $campaign = app(CampaignCheckout::class)->markPaidManually($campaign, User::factory()->create(['role' => 'staff']));
    $campaign->creatives()->create(['file_path' => 'x.jpg', 'type' => 'image', 'round' => 1]);
    return app(CampaignAdmin::class)->sendCreatives($campaign);
}

/* ── Notifications ── */

test('paiement reçu : le vendeur et l\'équipe sont prévenus', function () {
    $marketing = User::factory()->create(['role' => 'marketing']);
    $campaign = paidBoost();

    // Lancée tout de suite : « Paiement reçu » l'annonce, pas de second message « en ligne »
    expect(campaignEvents($campaign->shop->owner))->toContain('paiement_recu')->not->toContain('en_ligne')
        ->and(campaignEvents($marketing))->toContain('equipe_paiement_recu')
        ->and(campaignEvents(User::factory()->create(['role' => 'client'])))->toBe([]);
});

test('visuels prêts : notification et SMS au vendeur ; modification demandée : l\'équipe est prévenue', function () {
    $marketing = User::factory()->create(['role' => 'marketing']);
    $campaign = socialWithCreatives();
    $owner = $campaign->shop->owner;

    expect(campaignEvents($owner))->toContain('visuels_prets')
        ->and($this->sms->sent)->toHaveCount(1)
        ->and($this->sms->sent[0][0])->toBe('622458137')
        ->and($this->sms->sent[0][1])->toContain('visuels de ta campagne n°' . $campaign->id);

    app(CampaignCheckout::class)->requestCreativeChanges($campaign, 'Le logo est trop petit');
    $note = $marketing->notifications()->where('type', CampaignNotification::class)->get()->firstWhere('data.event', 'equipe_modification_demandee');
    expect($note->data['body'])->toContain('Le logo est trop petit');
});

test('refus : notification et SMS avec le motif', function () {
    $campaign = socialWithCreatives();
    app(CampaignAdmin::class)->refuse($campaign->fresh(), 'Produit interdit', User::factory()->create(['role' => 'staff']));

    expect(campaignEvents($campaign->shop->owner))->toContain('refusee')
        ->and(collect($this->sms->sent)->last()[1])->toContain('Produit interdit');
});

test('le vendeur voit ses nouveautés ; ouvrir la campagne les marque comme lues', function () {
    $campaign = paidBoost();
    $owner = $campaign->shop->owner;

    $this->actingAs($owner)->get(route('boutique.campaigns.index'))->assertOk()->assertSee('Nouveautés')->assertSee('Paiement reçu');
    $this->actingAs($owner)->get(route('boutique.campaigns.show', $campaign))->assertOk();
    expect($owner->unreadNotifications()->count())->toBe(0);

    app(CampaignAdmin::class)->pause($campaign->fresh());
    app(CampaignAdmin::class)->close($campaign->fresh());
    expect($owner->fresh()->unreadNotifications()->count())->toBe(1);
    $this->actingAs($owner)->post(route('boutique.campaigns.news.read-all'))->assertRedirect();
    expect($owner->fresh()->unreadNotifications()->count())->toBe(0);
});

test('une notification qui échoue ne bloque pas le changement de statut', function () {
    app()->instance(SmsSender::class, new class implements SmsSender {
        public function send(string $phone, string $message): bool { throw new RuntimeException('Fournisseur SMS en panne'); }
    });
    $campaign = socialWithCreatives();
    expect($campaign->fresh()->status)->toBe(C::STATUS_VISUELS_A_VALIDER);
});

/* ── Tâche planifiée ── */

test('démarre une mise en avant programmée ; une pub réseaux sociaux attend l\'équipe', function () {
    $boost = paidBoost([], ['starts_on' => today()->addDay()->toDateString()]);
    expect($boost->status)->toBe(C::STATUS_PROGRAMMEE);

    $this->travel(1)->days();
    $social = socialWithCreatives();
    app(CampaignCheckout::class)->approveCreatives($social);
    $social->forceFill(['starts_on' => today()])->save();

    $result = app(CampaignScheduler::class)->run();

    expect($result['demarrees'])->toBe(1)
        ->and($boost->fresh()->status)->toBe(C::STATUS_EN_COURS)
        ->and($social->fresh()->status)->toBe(C::STATUS_PROGRAMMEE)
        ->and(campaignEvents($boost->shop->owner))->toContain('en_ligne');
});

test('rupture : pause, puis reprise avec les jours reportés', function () {
    $campaign = paidBoost();
    $product = $campaign->products()->first();
    $endsOn = $campaign->ends_on->copy();

    $product->forceFill(['stock' => 0])->save();
    app(CampaignScheduler::class)->run();
    $campaign->refresh();
    expect($campaign->status)->toBe(C::STATUS_EN_PAUSE)->and($campaign->pause_reason)->toBe('rupture')
        ->and(campaignEvents($campaign->shop->owner))->toContain('pause_rupture');

    $this->travel(3)->days();
    $product->forceFill(['stock' => 4])->save();
    app(CampaignScheduler::class)->run();
    $campaign->refresh();
    expect($campaign->status)->toBe(C::STATUS_EN_COURS)
        ->and($campaign->ends_on->toDateString())->toBe($endsOn->addDays(3)->toDateString())
        ->and(campaignEvents($campaign->shop->owner))->toContain('reprise');
});

test('termine les campagnes dont la date est passée et prévient le vendeur', function () {
    $campaign = paidBoost();
    $this->travel($campaign->duration_days + 1)->days();
    $campaign->products()->first()->forceFill(['stock' => 5])->save();

    app(CampaignScheduler::class)->run();

    expect($campaign->fresh()->status)->toBe(C::STATUS_TERMINEE)
        ->and(campaignEvents($campaign->shop->owner))->toContain('rapport_pret');
});

test('supprime les impayées de plus de 24 h, sauf un paiement Mobile Money en cours', function () {
    $shop = F::shop();
    $old = F::campaign($shop, 'boutique_une');
    $recent = F::campaign(F::shop(), 'boutique_une');
    $pending = F::campaign(F::shop(), 'boutique_une');
    C::whereKey([$old->id, $pending->id])->update(['created_at' => now()->subHours(30)]);
    $pending->payments()->create(['method' => Pay::METHOD_MOBILE_MONEY, 'amount_gnf' => $pending->price_gnf, 'status' => Pay::STATUS_EN_ATTENTE]);

    expect(app(CampaignScheduler::class)->deleteUnpaid())->toBe(1);
    expect(C::find($old->id))->toBeNull()
        ->and(C::find($recent->id))->not->toBeNull()
        ->and(C::find($pending->id))->not->toBeNull()
        ->and($old->items()->count())->toBe(0);
});

test('visuels : rappel après 48 h, annulation et budget pub remboursé après 5 jours', function () {
    $campaign = socialWithCreatives();
    $owner = $campaign->shop->owner;

    $this->travel(49)->hours();
    expect(app(CampaignScheduler::class)->remindCreatives())->toBe(1)
        ->and(app(CampaignScheduler::class)->remindCreatives())->toBe(0)
        ->and(campaignEvents($owner))->toContain('rappel_visuels');

    $this->travel(4)->days();
    expect(app(CampaignScheduler::class)->expireCreatives())->toBe(1);
    expect($campaign->fresh()->status)->toBe(C::STATUS_ANNULEE)
        ->and(ShopCredit::balance($campaign->shop))->toBe($campaign->ad_budget_gnf)
        ->and(campaignEvents($owner))->toContain('annulee_visuels');
});

test('fin dans 2 jours : une seule notification, avec le lien Prolonger', function () {
    $campaign = paidBoost();
    $this->travel($campaign->duration_days - 3)->days();

    expect(app(CampaignScheduler::class)->warnEndingSoon())->toBe(1)
        ->and(app(CampaignScheduler::class)->warnEndingSoon())->toBe(0);
    $note = $campaign->shop->owner->notifications()->get()->firstWhere('data.event', 'fin_proche');
    expect($note->data['action'])->toBe('Prolonger')->and($note->data['url'])->toContain('depuis=' . $campaign->id);
});

test('la commande campaigns:tick tourne et est planifiée toutes les heures', function () {
    $this->artisan('campaigns:tick')->assertSuccessful();
    $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())->map->command->filter()->implode(' ');
    expect($events)->toContain('campaigns:tick');
});

/* ── Facture ── */

test('facture PDF : réseaux sociaux sur deux lignes, réservée à la boutique et à l\'équipe', function () {
    $campaign = socialWithCreatives();
    $lines = \App\Support\Campaigns\CampaignInvoice::lines($campaign);
    expect($lines)->toHaveCount(2)
        ->and($lines[0]['amount'])->toBe(200000)
        ->and($lines[1]['amount'])->toBe(100000)
        ->and(\App\Support\Campaigns\CampaignInvoice::number($campaign))->toBe('FC-' . now()->format('Y') . '-' . str_pad($campaign->id, 6, '0', STR_PAD_LEFT));

    $this->actingAs($campaign->shop->owner)->get(route('boutique.campaigns.invoice', $campaign))
        ->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->actingAs(F::shop()->owner)->get(route('boutique.campaigns.invoice', $campaign))->assertNotFound();
    $this->actingAs(User::factory()->create(['role' => 'marketing']))->get(route('admin.campaigns.invoice', $campaign))->assertOk();
});

test('pas de facture pour une campagne non payée ; les factures sont dans Rapports', function () {
    $shop = F::shop();
    $unpaid = F::campaign($shop, 'boutique_une');
    $this->actingAs($shop->owner)->get(route('boutique.campaigns.invoice', $unpaid))->assertNotFound();

    $paid = paidBoost();
    $paid->shop->owner->forceFill(['shop_id' => $paid->shop_id])->save(); // comme un vrai propriétaire
    $paid->shop->forceFill(['plan' => 'pro', 'plan_expires_at' => now()->addMonth()])->save(); // Rapports : plan Pro
    $this->actingAs($paid->shop->owner->fresh())->get(route('boutique.reports.index'))->assertOk()
        ->assertSee('Factures des campagnes')->assertSee(route('boutique.campaigns.invoice', $paid), false);
});
