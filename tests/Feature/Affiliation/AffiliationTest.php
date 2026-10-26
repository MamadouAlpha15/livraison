<?php

use App\Enums\CommissionStatus;
use App\Enums\PayoutStatus;
use App\Enums\ReferralStatus;
use App\Models\Affiliate;
use App\Models\AffiliateReward;
use App\Models\AffiliateSetting;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Referral;
use App\Models\ReferralClick;
use App\Models\Shop;
use App\Models\ShopCreditEntry;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AffiliateService;
use App\Services\SubscriptionService;
use App\Support\Money;
use App\Support\Sms\SmsSender;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->sms = new class implements SmsSender {
        public array $sent = [];
        public function send(string $phone, string $message): bool { $this->sent[] = [$phone, $message]; return true; }
    };
    app()->instance(SmsSender::class, $this->sms);
});

/* ── Aides ── */

/** Nouveau vendeur inscrit, sans boutique encore */
function newOwner(array $attributes = []): User
{
    return User::factory()->create($attributes + ['role' => 'admin', 'country' => 'GN']);
}

function shopForm(array $overrides = []): array
{
    return $overrides + ['name' => 'Chez Aïssatou', 'phone' => '625 11 22 33', 'currency' => 'GNF'];
}

/** Paiement d'abonnement confirmé par ChapChap Pay, comme le fait le webhook */
function payPro(Shop $shop, int $amount = 150000): Subscription
{
    $subscription = Subscription::create([
        'subscriber_type'   => Shop::class,
        'subscriber_id'     => $shop->id,
        'plan'              => 'pro',
        'amount'            => $amount,
        'currency'          => 'GNF',
        'payment_method'    => 'orange_money',
        'payment_reference' => 'TEST-' . uniqid(),
        'status'            => 'pending',
    ]);
    app(SubscriptionService::class)->activate($subscription, ['status' => ['code' => 'success']]);
    return $subscription->fresh();
}

function settings(array $values): void
{
    AffiliateSetting::current()->update($values);
    app(AffiliateService::class)->refreshSettings();
}

/* ── Suivi du lien et attribution ── */

test('le lien ?ref= pose le cookie 30 jours et compte le clic (5 par heure et par IP au plus)', function () {
    $affiliate = Affiliate::factory()->vendor()->create(['code' => 'AMINA47']);

    $response = $this->get('/register?ref=amina47');
    $response->assertCookie(AffiliateService::COOKIE, 'AMINA47');
    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === AffiliateService::COOKIE);
    expect($cookie->getExpiresTime())->toBeGreaterThan(now()->addDays(29)->timestamp)
        ->and(ReferralClick::where('affiliate_id', $affiliate->id)->count())->toBe(1)
        ->and(ReferralClick::first()->ip_hash)->not->toBe('127.0.0.1')->toHaveLength(64);

    foreach (range(1, 6) as $i) {
        $this->get('/register?ref=AMINA47');
    }
    expect(ReferralClick::where('affiliate_id', $affiliate->id)->count())->toBe(5);
});

test('code inconnu ou affilié suspendu : ni cookie ni clic', function () {
    Affiliate::factory()->suspended()->create(['code' => 'STOP99']);
    $this->get('/register?ref=STOP99')->assertCookieMissing(AffiliateService::COOKIE);
    $this->get('/register?ref=INCONNU1')->assertCookieMissing(AffiliateService::COOKIE);
});

test('attribution par cookie : le code est pré-rempli et la boutique est rattachée au parrain', function () {
    $affiliate = Affiliate::factory()->vendor()->create(['code' => 'AMINA47']);
    $owner = newOwner();

    $this->actingAs($owner)->withCookie(AffiliateService::COOKIE, 'AMINA47')
        ->get(route('shop.create'))
        ->assertOk()
        ->assertSee('value="AMINA47"', false);

    $this->actingAs($owner)->withCookie(AffiliateService::COOKIE, 'AMINA47')
        ->post(route('shop.store'), shopForm(['referral_code' => 'AMINA47', 'source' => 'whatsapp']))
        ->assertRedirect(route('boutique.dashboard'));

    $shop = Shop::where('user_id', $owner->id)->firstOrFail();
    $referral = Referral::where('referred_shop_id', $shop->id)->firstOrFail();
    expect($referral->affiliate_id)->toBe($affiliate->id)
        ->and($referral->status)->toBe(ReferralStatus::Registered)
        ->and($referral->source)->toBe('whatsapp')
        ->and($referral->flagged)->toBeFalse()
        // L'essai gratuit fonctionne comme avant
        ->and($shop->fresh()->plan)->toBe('pro');

    // Le parrain est prévenu
    expect($affiliate->user->notifications()->where('data->event', 'nouveau_filleul')->count())->toBe(1);
});

test('attribution par code saisi à la main, sans cookie', function () {
    $affiliate = Affiliate::factory()->create(['code' => 'RADIO24']);
    $owner = newOwner();

    $this->actingAs($owner)->post(route('shop.store'), shopForm(['referral_code' => 'radio24']))->assertRedirect();

    $shop = Shop::where('user_id', $owner->id)->firstOrFail();
    expect($shop->referral?->affiliate_id)->toBe($affiliate->id);
});

test('un code inconnu est refusé et rien n\'est créé ; sans code la boutique se crée normalement', function () {
    $owner = newOwner();

    $this->actingAs($owner)->post(route('shop.store'), shopForm(['referral_code' => 'FAUX123']))
        ->assertSessionHasErrors('referral_code');
    expect(Shop::where('user_id', $owner->id)->exists())->toBeFalse();

    $this->actingAs($owner)->post(route('shop.store'), shopForm())->assertRedirect(route('boutique.dashboard'));
    expect(Shop::where('user_id', $owner->id)->exists())->toBeTrue()
        ->and(Referral::count())->toBe(0);
});

test('auto-parrainage bloqué : même utilisateur, même téléphone ou même e-mail', function () {
    $affiliate = Affiliate::factory()->vendor()->create(['code' => 'MOIMEME1']);
    $sponsor = $affiliate->user;

    // Même utilisateur (il ouvre une 2e boutique avec son propre code)
    $this->actingAs($sponsor)->post(route('shop.store'), shopForm(['referral_code' => 'MOIMEME1']))
        ->assertSessionHasErrors(['referral_code' => 'Tu ne peux pas utiliser ton propre code de parrainage.']);

    // Même téléphone, écrit autrement
    $owner = newOwner();
    $this->actingAs($owner)->post(route('shop.store'), shopForm(['referral_code' => 'MOIMEME1', 'phone' => '+224 ' . $sponsor->phone]))
        ->assertSessionHasErrors('referral_code');

    // Même e-mail que la boutique du parrain
    $this->actingAs($owner)->post(route('shop.store'), shopForm(['referral_code' => 'MOIMEME1', 'email' => strtoupper($affiliate->shop()->email)]))
        ->assertSessionHasErrors('referral_code');

    expect(Referral::count())->toBe(0);
});

test('même appareil ou même IP que le parrain : le parrainage est créé mais marqué à vérifier', function () {
    $affiliate = Affiliate::factory()->vendor()->create(['code' => 'DEVICE12']);
    $deviceId = str_repeat('a', 32);
    $affiliate->update(['device_hash' => AffiliateService::hash($deviceId)]);
    $admin = User::factory()->create(['role' => 'superadmin']);
    $owner = newOwner();

    $this->actingAs($owner)->withCookie(AffiliateService::DEVICE_COOKIE, $deviceId)
        ->post(route('shop.store'), shopForm(['referral_code' => 'DEVICE12']))->assertRedirect();

    $referral = Referral::firstOrFail();
    expect($referral->flagged)->toBeTrue()
        ->and($referral->flag_reason)->toContain('appareil')
        ->and($admin->notifications()->where('data->event', 'filleul_a_verifier')->count())->toBe(1);

    // Même IP (127.0.0.1 en test) que la dernière visite du parrain
    $affiliate->update(['device_hash' => null, 'last_ip_hash' => AffiliateService::hash('127.0.0.1')]);
    $other = newOwner();
    $this->actingAs($other)->post(route('shop.store'), shopForm(['name' => 'Autre', 'referral_code' => 'DEVICE12']))->assertRedirect();
    expect(Shop::where('user_id', $other->id)->first()->referral->flag_reason)->toContain('IP');
});

/* ── Passage à « Active » ── */

test('la boutique parrainée devient « Active » avec ses produits en ligne et sa première commande', function () {
    settings(['active_min_products' => 3, 'active_min_orders' => 1]);
    $referral = Referral::factory()->create();
    $shopId = $referral->referred_shop_id;
    $product = fn () => Product::forceCreate(['shop_id' => $shopId, 'name' => 'Pagne ' . uniqid(), 'price' => 50000, 'stock' => 5, 'is_active' => true, 'category' => 'Vêtements & Mode']);

    $product(); $product(); $product();
    expect($referral->fresh()->status)->toBe(ReferralStatus::Registered);

    // Une commande annulée ne compte pas
    Order::forceCreate(['shop_id' => $shopId, 'total' => 50000, 'status' => Order::STATUS_ANNULEE]);
    expect($referral->fresh()->status)->toBe(ReferralStatus::Registered);

    Order::forceCreate(['shop_id' => $shopId, 'total' => 50000, 'status' => Order::STATUS_EN_ATTENTE]);
    expect($referral->fresh()->status)->toBe(ReferralStatus::Active)
        ->and($referral->fresh()->activated_at)->not->toBeNull();
});

/* ── Commissions ── */

test('paiement d\'abonnement : commission en attente, boutique « Abonnée », jamais deux commissions pour un paiement', function () {
    settings(['validation_days' => 30]);
    $affiliate = Affiliate::factory()->vendor()->create(['commission_rate' => 20]);
    $referral = Referral::factory()->active()->create(['affiliate_id' => $affiliate->id]);

    $payment = payPro($referral->shop, 150000);

    $commission = Commission::sole();
    expect($commission->amount)->toBe(30000)
        ->and($commission->status)->toBe(CommissionStatus::Pending)
        ->and($commission->subscription_payment_id)->toBe($payment->id)
        ->and($commission->available_at->isSameDay(now()->addDays(30)))->toBeTrue()
        ->and($referral->fresh()->status)->toBe(ReferralStatus::Paying)
        ->and($referral->fresh()->first_paid_at)->not->toBeNull();

    // Webhook reçu deux fois
    app(AffiliateService::class)->handleSubscriptionPayment($payment);
    expect(Commission::count())->toBe(1);

    // L'essai gratuit (0 GNF) ne crée pas de commission
    app(SubscriptionService::class)->startShopTrial($referral->shop);
    expect(Commission::count())->toBe(1);
});

test('plus de commission après la durée prévue, ni pour un affilié suspendu', function () {
    settings(['commission_months' => 12]);
    $referral = Referral::factory()->create();
    payPro($referral->shop);

    $this->travel(13)->months();
    payPro($referral->shop);
    expect(Commission::count())->toBe(1);

    $other = Referral::factory()->create(['affiliate_id' => Affiliate::factory()->suspended()]);
    payPro($other->shop);
    expect(Commission::count())->toBe(1);
});

test('validation quotidienne : la commission devient disponible après le délai, sauf parrainage à vérifier', function () {
    settings(['validation_days' => 30]);
    $ok = Referral::factory()->create();
    $suspect = Referral::factory()->flagged()->create();
    payPro($ok->shop);
    payPro($suspect->shop);

    $this->artisan('affiliates:approve-commissions')->assertSuccessful();
    expect(Commission::where('status', CommissionStatus::Approved)->count())->toBe(0);

    $this->travel(31)->days();
    $this->artisan('affiliates:approve-commissions')->expectsOutputToContain('1 commission(s)')->assertSuccessful();

    expect(Commission::where('referral_id', $ok->id)->first()->status)->toBe(CommissionStatus::Approved)
        ->and(Commission::where('referral_id', $suspect->id)->first()->status)->toBe(CommissionStatus::Pending);

    // Vérifié par l'équipe : validée au passage suivant
    app(AffiliateService::class)->clearFlag($suspect->fresh());
    $this->artisan('affiliates:approve-commissions');
    expect(Commission::where('referral_id', $suspect->id)->first()->status)->toBe(CommissionStatus::Approved);
});

test('remboursement : la commission liée est annulée, y compris dans un retrait pas encore versé', function () {
    settings(['payout_threshold' => 10000]);
    $referral = Referral::factory()->create(['affiliate_id' => Affiliate::factory()->withAccount()]);
    $first = payPro($referral->shop);
    $second = payPro($referral->shop);
    Commission::query()->update(['status' => CommissionStatus::Approved]);

    $payout = app(AffiliateService::class)->requestPayout($referral->affiliate, 'orange_money', '622458137');
    expect($payout->amount)->toBe(60000);

    app(SubscriptionService::class)->markRefunded($first);

    expect(Commission::where('subscription_payment_id', $first->id)->first()->status)->toBe(CommissionStatus::Cancelled)
        ->and(Commission::where('subscription_payment_id', $second->id)->first()->status)->toBe(CommissionStatus::Approved)
        ->and($payout->fresh()->amount)->toBe(30000)
        ->and($first->fresh()->refunded_at)->not->toBeNull();
});

/* ── Mois offert au parrain ── */

test('premier paiement du filleul : abonnement du parrain prolongé d\'un mois, ou Crédit Shopio s\'il n\'en a pas', function () {
    settings(['reward_months' => 1, 'annual_reward_cap' => 6]);
    $affiliate = Affiliate::factory()->vendor()->create();
    $sponsorShop = $affiliate->shop();
    app(SubscriptionService::class)->startShopTrial($sponsorShop);
    $endBefore = $sponsorShop->fresh()->plan_expires_at;

    $referral = Referral::factory()->create(['affiliate_id' => $affiliate->id]);
    payPro($referral->shop);
    payPro($referral->shop); // 2e paiement : pas de 2e mois offert

    expect(AffiliateReward::count())->toBe(1)
        ->and(AffiliateReward::first()->kind)->toBe(AffiliateReward::KIND_EXTENSION)
        ->and($sponsorShop->fresh()->plan_expires_at->toDateString())->toBe($endBefore->copy()->addDays(30)->toDateString());

    // Parrain sans abonnement en cours : Crédit Shopio de la valeur d'un mois Pro
    $free = Affiliate::factory()->vendor()->create();
    $referral2 = Referral::factory()->create(['affiliate_id' => $free->id]);
    payPro($referral2->shop);
    $entry = ShopCreditEntry::where('shop_id', $free->shop()->id)->sole();
    expect($entry->reason)->toBe(ShopCreditEntry::REASON_PARRAINAGE)
        ->and($entry->amount_gnf)->toBe((int) config('chapchappay.plans.pro', 150000));
});

test('plafond annuel des mois offerts respecté', function () {
    settings(['reward_months' => 1, 'annual_reward_cap' => 2]);
    $affiliate = Affiliate::factory()->vendor()->create();

    foreach (range(1, 3) as $i) {
        payPro(Referral::factory()->create(['affiliate_id' => $affiliate->id])->shop);
    }
    expect(AffiliateReward::where('affiliate_id', $affiliate->id)->sum('months'))->toBe(2);

    // Nouvelle année : le compteur repart
    $this->travelTo(now()->addYear()->startOfYear()->addDay());
    payPro(Referral::factory()->create(['affiliate_id' => $affiliate->id])->shop);
    expect(AffiliateReward::where('affiliate_id', $affiliate->id)->count())->toBe(3);
});

/* ── Retraits ── */

test('retrait impossible au-delà du solde validé, ni deux fois les mêmes commissions', function () {
    settings(['payout_threshold' => 50000]);
    $affiliate = Affiliate::factory()->vendor()->create();
    $service = app(AffiliateService::class);
    $commission = Commission::factory()->approved()->create(['affiliate_id' => $affiliate->id, 'amount' => 30000]);
    Commission::factory()->create(['affiliate_id' => $affiliate->id, 'amount' => 90000]); // encore en attente

    expect(fn () => $service->requestPayout($affiliate, 'orange_money', '622458137'))->toThrow(ValidationException::class);

    Commission::factory()->approved()->create(['affiliate_id' => $affiliate->id, 'amount' => 30000]);
    expect(fn () => $service->requestPayout($affiliate, 'orange_money', '622458137', 70000))->toThrow(ValidationException::class);

    $payout = $service->requestPayout($affiliate, 'orange_money', '622458137');
    expect($payout->amount)->toBe(60000)
        ->and($service->availableBalance($affiliate))->toBe(0)
        ->and(fn () => $service->requestPayout($affiliate, 'orange_money', '622458137'))->toThrow(ValidationException::class);

    // Par la page vendeur aussi
    $this->actingAs($affiliate->user)->post(route('vendeur.parrainage.payout'), ['method' => 'mtn_momo', 'phone' => '664112233'])
        ->assertSessionHasErrors('payout');
    expect(Payout::count())->toBe(1);

    // Versé par l'équipe : commissions payées, SMS envoyé
    $service->markPayoutPaid($payout, User::factory()->create(['role' => 'staff']), 'OM-123456');
    expect($commission->fresh()->status)->toBe(CommissionStatus::Paid)
        ->and($payout->fresh()->status)->toBe(PayoutStatus::Paid)
        ->and($this->sms->sent)->toHaveCount(1)
        ->and($this->sms->sent[0][1])->toContain('60' . "\u{00A0}" . '000' . "\u{00A0}" . 'GNF');
});

test('retrait refusé : le solde redevient disponible', function () {
    settings(['payout_threshold' => 10000]);
    $affiliate = Affiliate::factory()->vendor()->create();
    Commission::factory()->approved()->create(['affiliate_id' => $affiliate->id, 'amount' => 30000]);
    $service = app(AffiliateService::class);

    $payout = $service->requestPayout($affiliate, 'orange_money', '622458137');
    $service->rejectPayout($payout, User::factory()->create(['role' => 'staff']), 'Numéro introuvable.');

    expect($service->availableBalance($affiliate))->toBe(30000)
        ->and($service->requestPayout($affiliate, 'mtn_momo', '664112233')->amount)->toBe(30000);
});

/* ── Accès ── */

test('un vendeur ne voit que ses filleuls et commissions ; seule l\'équipe accède à la gestion', function () {
    $mine = Affiliate::factory()->vendor()->create();
    $other = Affiliate::factory()->vendor()->create();
    $myCommission = Commission::factory()->create(['affiliate_id' => $mine->id, 'amount' => 11111]);
    $otherCommission = Commission::factory()->create(['affiliate_id' => $other->id, 'amount' => 22222]);
    $vendor = $mine->user;

    expect(Gate::forUser($vendor)->allows('view', $other))->toBeFalse()
        ->and(Gate::forUser($vendor)->allows('view', $mine))->toBeTrue()
        ->and(Gate::forUser($vendor)->allows('view', $otherCommission->referral))->toBeFalse()
        ->and(Gate::forUser($vendor)->allows('view', $otherCommission))->toBeFalse()
        ->and(Gate::forUser($vendor)->allows('update', $myCommission))->toBeFalse();

    $page = $this->actingAs($vendor)->get(route('vendeur.parrainage'))->assertOk();
    $page->assertSee($mine->code)->assertSee($myCommission->referral->shop->name)
        ->assertDontSee($other->code)->assertDontSee($otherCommission->referral->shop->name);

    $csv = $this->actingAs($vendor)->get(route('vendeur.parrainage.export'))->assertOk()->streamedContent();
    expect($csv)->toContain('11111')->not->toContain('22222');

    $this->actingAs($vendor)->get(route('admin.affiliation.index'))->assertForbidden();
    $this->actingAs($vendor)->post(route('admin.affiliation.payouts.pay', Payout::factory()->create(['affiliate_id' => $mine->id])), ['reference' => 'X'])->assertForbidden();

    $staff = User::factory()->create(['role' => 'staff']);
    foreach (['index', 'referrals', 'commissions', 'payouts', 'settings'] as $page) {
        $this->actingAs($staff)->get(route('admin.affiliation.' . $page))->assertOk();
    }
    $this->actingAs($staff)->get(route('admin.affiliation.commissions.export'))->assertOk();
});

test('l\'équipe crée un affilié externe avec accès ; il voit son tableau de bord', function () {
    \Illuminate\Support\Facades\Notification::fake();
    $staff = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($staff)->post(route('admin.affiliation.affiliates.store'), [
        'name' => 'Mariama Influence', 'email' => 'mariama@test.invalid', 'phone' => '622000111', 'with_access' => '1',
    ])->assertRedirect(route('admin.affiliation.index'));

    $affiliate = Affiliate::where('email', 'mariama@test.invalid')->sole();
    expect($affiliate->user->role)->toBe('affilie')
        ->and((float) $affiliate->commission_rate)->toBe(20.0)
        ->and($affiliate->code)->toStartWith('MARIAMA');
    \Illuminate\Support\Facades\Notification::assertSentTo($affiliate->user, \Illuminate\Auth\Notifications\ResetPassword::class);

    $this->actingAs($affiliate->user)->get(route('affilie.dashboard'))->assertOk()->assertSee($affiliate->code);
    $this->actingAs($affiliate->user)->get(route('vendeur.parrainage'))->assertForbidden();
});

test('accueil : bouton « Devenir affilié » vers la page publique du programme', function () {
    $this->get('/')->assertOk()
        ->assertSee(route('affiliation.join'), false)
        ->assertSee('Devenir affilié');

    $this->get(route('affiliation.join'))->assertOk()
        ->assertSee('Créer mon compte affilié')
        ->assertSee('20 %');
});

test('une personne externe s\'inscrit elle-même, vérifie son e-mail et reçoit son code', function () {
    $staff = User::factory()->create(['role' => 'superadmin']);

    $this->post(route('affiliation.join.store'), [
        'name' => 'Ibrahima Sow', 'email' => 'Ibrahima@Test.invalid', 'phone' => '622 45 81 37',
        'password' => 'Motdepasse-2026', 'password_confirmation' => 'Motdepasse-2026', 'terms' => '1',
    ])->assertRedirect(route('verification.otp.show'));

    $user = User::where('email', 'ibrahima@test.invalid')->sole();
    expect($user->role)->toBe('affilie')->and($user->email_verified_at)->toBeNull()
        ->and(Affiliate::count())->toBe(0);

    $this->post(route('verification.otp.verify'), ['code' => $user->fresh()->otp_code])
        ->assertRedirect(route('affilie.dashboard'));

    // En attente de validation : ni lien ni code affichés, l'équipe est prévenue
    $page = $this->get(route('affilie.dashboard'))->assertOk()->assertSee('Ta demande est en cours de vérification');
    $affiliate = $user->affiliate()->sole();
    $page->assertDontSee($affiliate->code);
    expect($affiliate->type)->toBe(\App\Enums\AffiliateType::External)
        ->and($affiliate->status)->toBe(\App\Enums\AffiliateStatus::Pending)
        ->and($affiliate->code)->toStartWith('IBRAHIMA')
        ->and($affiliate->phone)->toBe('622 45 81 37')
        ->and($staff->notifications()->where('data->event', 'affilie_a_valider')->count())->toBe(1);

    // Connecté : plus de formulaire, un lien vers son tableau de bord
    $this->get(route('affiliation.join'))->assertSee('Tu es déjà inscrit');
    $this->post(route('affiliation.join.store'), ['name' => 'X'])->assertRedirect();
    expect(User::where('role', 'affilie')->count())->toBe(1);
});

test('affilié en attente : lien inactif et retrait impossible tant que l\'équipe ne l\'a pas validé', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $user = User::factory()->create(['role' => 'affilie', 'phone' => '622458137']);
    $affiliate = app(AffiliateService::class)->externalAffiliateFor($user);
    $owner = newOwner();

    // Le lien ne pose pas de cookie, le code est refusé à la création de boutique
    $this->get('/register?ref=' . $affiliate->code)->assertCookieMissing(AffiliateService::COOKIE);
    $this->actingAs($owner)->post(route('shop.store'), shopForm(['referral_code' => $affiliate->code]))
        ->assertSessionHasErrors('referral_code');
    $this->actingAs($user)->post(route('affilie.payout'), ['method' => 'orange_money', 'phone' => '622458137'])->assertForbidden();

    // L'équipe valide : il est prévenu (compte, e-mail, SMS avec son code), son lien fonctionne
    $this->actingAs($staff)->get(route('admin.affiliation.index'))->assertSee('affilié(s) à valider')->assertSee('Valider');
    $this->actingAs($staff)->post(route('admin.affiliation.affiliates.approve', $affiliate))->assertRedirect();

    expect($affiliate->fresh()->status)->toBe(\App\Enums\AffiliateStatus::Active)
        ->and($user->notifications()->where('data->event', 'affilie_valide')->count())->toBe(1)
        ->and($this->sms->sent)->toHaveCount(1)
        ->and($this->sms->sent[0][1])->toContain($affiliate->code);
    $this->actingAs($user)->get(route('affilie.dashboard'))->assertSee($affiliate->code)->assertDontSee('en cours de vérification');
    $this->actingAs($owner)->post(route('shop.store'), shopForm(['referral_code' => $affiliate->code]))->assertRedirect(route('boutique.dashboard'));
    expect(Referral::where('affiliate_id', $affiliate->id)->count())->toBe(1);
});

test('l\'équipe refuse un affilié en attente : il est prévenu avec le motif', function () {
    $staff = User::factory()->create(['role' => 'superadmin']);
    $user = User::factory()->create(['role' => 'affilie']);
    $affiliate = app(AffiliateService::class)->externalAffiliateFor($user);

    $this->actingAs($staff)->post(route('admin.affiliation.affiliates.reject', $affiliate), ['reason' => 'Coordonnées incomplètes.'])->assertRedirect();

    expect($affiliate->fresh()->status)->toBe(\App\Enums\AffiliateStatus::Suspended)
        ->and($user->notifications()->where('data->event', 'affilie_refuse')->first()->data['body'])->toContain('Coordonnées incomplètes.');

    // Un vendeur ne peut ni valider ni refuser
    $vendor = Affiliate::factory()->vendor()->create()->user;
    $this->actingAs($vendor)->post(route('admin.affiliation.affiliates.approve', $affiliate))->assertForbidden();
});

test('inscription affilié refusée avec un e-mail déjà utilisé ou sans accepter les conditions', function () {
    User::factory()->create(['email' => 'pris@test.invalid']);

    $this->post(route('affiliation.join.store'), [
        'name' => 'Test', 'email' => 'pris@test.invalid', 'phone' => '622458137',
        'password' => 'Motdepasse-2026', 'password_confirmation' => 'Motdepasse-2026',
    ])->assertSessionHasErrors(['email', 'terms']);
});

test('montants en GNF : entiers, espace entre les milliers', function () {
    expect(Money::gnf(150000))->toBe("150\u{00A0}000\u{00A0}GNF")
        ->and(Money::gnf(1234567.6))->toBe("1\u{00A0}234\u{00A0}568\u{00A0}GNF")
        ->and(Money::gnf(500, false))->toBe('500');
});
