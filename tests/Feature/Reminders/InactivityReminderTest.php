<?php

use App\Jobs\SendInactivityReminders;
use App\Models\CartItem;
use App\Models\DeliveryCompany;
use App\Models\InactivityReminder;
use App\Models\Order;
use App\Models\Product;
use App\Models\PushSubscription;
use App\Models\ReminderSetting;
use App\Models\User;
use App\Notifications\Channels\PushChannel;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\InactivityReminderNotification;
use App\Services\InactivityReminderService;
use App\Services\PushService;
use App\Support\Sms\SmsSender;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Support\CampaignFixtures as F;

beforeEach(function () {
    $this->sms = new class implements SmsSender {
        public array $sent = [];
        public function send(string $phone, string $message): bool { $this->sent[] = [$phone, $message]; return true; }
    };
    app()->instance(SmsSender::class, $this->sms);
    $this->push = new class {
        public array $sent = [];
        public function sendToUser(User $user, string $title, string $body, int $badge = 0, string $url = '/'): void { $this->sent[] = [$user->id, $title]; }
    };
    app()->instance(PushService::class, $this->push);
});

/* ── Aides ── */

function inactiveClient(int $days = 31, array $attributes = []): User
{
    $user = User::factory()->create($attributes + ['role' => 'client', 'phone' => '622458137']);
    $user->forceFill(['last_active_at' => now()->subDays($days)])->save();
    return $user->fresh();
}

function inactiveCompany(int $days = 15, bool $approved = true): array
{
    $user = User::factory()->create(['role' => 'company']);
    $user->forceFill(['last_active_at' => now()->subDays($days)])->save();
    $company = DeliveryCompany::forceCreate(['name' => 'Rapide Express ' . $user->id, 'slug' => 'rapide-' . $user->id, 'user_id' => $user->id,
        'active' => true, 'approved' => $approved, 'country' => 'GN', 'currency' => 'GNF']);
    return [$user->fresh(), $company];
}

function reminderSettings(array $values): void
{
    ReminderSetting::current()->update($values);
    app(InactivityReminderService::class)->refreshSettings();
}

function runReminders(): array
{
    return app(InactivityReminderService::class)->run();
}

/* ── Qui est relancé ── */

test('seuls les clients inactifs depuis le délai, vérifiés et non désinscrits sont relancés', function () {
    Notification::fake();
    $inactive = inactiveClient(31);
    $recent = inactiveClient(10);
    $optedOut = inactiveClient(60, ['reminders_opt_out_at' => now()]);
    $unverified = inactiveClient(60, ['email_verified_at' => null]);
    $vendor = User::factory()->create(['role' => 'admin']);
    $vendor->forceFill(['last_active_at' => now()->subDays(90)])->save();

    expect(runReminders())->toBe(['client' => 1, 'company' => 0]);

    Notification::assertSentTo($inactive, InactivityReminderNotification::class, function ($n, $channels) {
        return $n->step === 1 && in_array('mail', $channels, true) && in_array('database', $channels, true)
            && !in_array(PushChannel::class, $channels, true) && !in_array(SmsChannel::class, $channels, true);
    });
    foreach ([$recent, $optedOut, $unverified, $vendor] as $other) {
        Notification::assertNotSentTo($other, InactivityReminderNotification::class);
    }
    expect(InactivityReminder::sole()->only(['user_id', 'audience', 'step', 'channels']))
        ->toBe(['user_id' => $inactive->id, 'audience' => 'client', 'step' => 1, 'channels' => ['mail']]);
});

test('jusqu\'à 3 rappels espacés de 15 jours, puis plus rien tant que la personne ne revient pas', function () {
    Notification::fake();
    reminderSettings(['interval_days' => 15, 'max_steps' => 3]);
    $client = inactiveClient(31);

    runReminders();
    runReminders(); // même jour : pas de doublon
    expect(InactivityReminder::count())->toBe(1);

    $this->travel(14)->days();
    runReminders();
    expect(InactivityReminder::count())->toBe(1);

    $this->travel(2)->days();
    runReminders();
    $this->travel(16)->days();
    runReminders();
    $this->travel(16)->days();
    runReminders();

    expect(InactivityReminder::orderBy('id')->pluck('step')->all())->toBe([1, 2, 3]);
    Notification::assertSentTo($client, InactivityReminderNotification::class, fn ($n) => $n->step === 3 && str_contains($n->body, 'dernier rappel'));
});

test('la personne revient : sa visite est notée, le rappel compte comme un retour, et le cycle recommence', function () {
    Notification::fake();
    $client = inactiveClient(31);
    runReminders();

    $this->actingAs($client)->get(route('dashboard'));
    $client->refresh();
    expect($client->last_active_at->isToday())->toBeTrue()
        ->and(InactivityReminder::sole()->returned_at)->not->toBeNull();

    // De nouveau inactif 31 jours plus tard : nouveau cycle, en commençant au rappel n° 1
    $this->travel(31)->days();
    runReminders();
    expect(InactivityReminder::orderByDesc('id')->first()->step)->toBe(1)
        ->and(InactivityReminder::count())->toBe(2);
});

test('la dernière visite est notée au plus toutes les 15 minutes, sans toucher la date de modification du compte', function () {
    $client = User::factory()->create(['role' => 'client']);
    $updated = $client->updated_at;
    $this->travel(1)->hours();

    $this->actingAs($client)->get(route('dashboard'));
    $first = $client->fresh()->last_active_at;
    $this->travel(5)->minutes();
    $this->actingAs($client)->get(route('dashboard'));

    expect($client->fresh()->last_active_at->equalTo($first))->toBeTrue()
        ->and($client->fresh()->updated_at->equalTo($updated))->toBeTrue();
});

/* ── Messages ── */

test('message client : nombre réel de nouveaux produits et articles du panier', function () {
    $client = inactiveClient(31, ['name' => 'Aïssatou Bah']);
    $shop = F::shop();
    F::product($shop); F::product($shop);
    Product::query()->update(['created_at' => now()->subDays(5)]);
    $old = F::product($shop);
    $old->forceFill(['created_at' => now()->subDays(60)])->save();
    CartItem::forceCreate(['user_id' => $client->id, 'product_id' => $old->id, 'quantity' => 2]);

    [$title, $body] = app(InactivityReminderService::class)->message($client, 'client', 1);

    expect($title)->toBe('Aïssatou, tu nous manques sur Shopio')
        ->and($body)->toContain('2 nouveaux produits')
        ->and($body)->toContain('2 article(s) dans ton panier');
});

test('entreprise de livraison : relancée avec ses commandes en attente ; pas si elle n\'est pas approuvée', function () {
    Notification::fake();
    [$user, $company] = inactiveCompany(15);
    [$unapproved] = inactiveCompany(40, approved: false);
    $shop = F::shop();
    Order::forceCreate(['shop_id' => $shop->id, 'delivery_company_id' => $company->id, 'total' => 50000, 'status' => Order::STATUS_EN_ATTENTE]);
    Order::forceCreate(['shop_id' => $shop->id, 'delivery_company_id' => $company->id, 'total' => 50000, 'status' => Order::STATUS_LIVREE]);

    expect(runReminders())->toBe(['client' => 0, 'company' => 1]);
    Notification::assertSentTo($user, InactivityReminderNotification::class,
        fn ($n) => $n->audience === 'company' && str_contains($n->body, '1 commande(s) confiée(s)') && $n->url === route('company.dashboard'));
    Notification::assertNotSentTo($unapproved, InactivityReminderNotification::class);
});

/* ── Canaux et limites ── */

test('notification push si le téléphone est abonné, SMS si activé ; l\'e-mail contient un lien de désinscription', function () {
    reminderSettings(['channel_sms' => true]);
    $client = inactiveClient(31);
    PushSubscription::forceCreate(['user_id' => $client->id, 'endpoint' => 'https://push.test/1', 'endpoint_hash' => hash('sha256', 'https://push.test/1'), 'type' => 'fcm']);

    runReminders();

    expect(InactivityReminder::sole()->channels)->toBe(['push', 'mail', 'sms'])
        ->and($this->push->sent)->toHaveCount(1)
        ->and($this->sms->sent[0][0])->toBe('622458137')
        ->and($client->notifications()->where('data->type', 'rappel_inactivite')->count())->toBe(1);

    $mail = (new InactivityReminderNotification('client', 1, 'T', 'B', url('/'), ['mail'], 'S'))->toMail($client);
    expect(implode(' ', $mail->outroLines))->toContain('relances/desinscription/' . $client->id)->toContain('signature=');
});

test('plafond quotidien respecté, et rien ne part quand les relances sont désactivées', function () {
    Notification::fake();
    reminderSettings(['daily_limit' => 2]);
    inactiveClient(31); inactiveClient(40); inactiveClient(50);

    expect(runReminders()['client'])->toBe(2)
        ->and(runReminders()['client'])->toBe(0); // plafond déjà atteint aujourd'hui

    $this->travel(1)->days();
    reminderSettings(['enabled' => false]);
    expect(runReminders())->toBe(['client' => 0, 'company' => 0]);

    reminderSettings(['enabled' => true]);
    $this->artisan('reminders:inactive --dry-run')->expectsOutputToContain('À relancer : 1 client(s)')->assertSuccessful();
    expect(InactivityReminder::count())->toBe(2);
    $this->artisan('reminders:inactive')->expectsOutputToContain('Rappels envoyés : 1 client(s)')->assertSuccessful();
});

test('lien de désinscription : signé uniquement, et plus aucun rappel ensuite', function () {
    Notification::fake();
    $client = inactiveClient(31);

    $this->get('/relances/desinscription/' . $client->id)->assertForbidden();
    $this->get(URL::signedRoute('reminders.unsubscribe', ['user' => $client->id]))->assertOk()->assertSee('Tu ne recevras plus');

    expect($client->fresh()->reminders_opt_out_at)->not->toBeNull()
        ->and(runReminders()['client'])->toBe(0);
});

/* ── Page de l'équipe ── */

test('page Relances : réservée à l\'équipe, réglages et envoi immédiat', function () {
    Bus::fake([SendInactivityReminders::class]);
    $staff = User::factory()->create(['role' => 'staff']);
    inactiveClient(31);
    $vendor = User::factory()->create(['role' => 'admin']);

    $this->actingAs($vendor)->get(route('admin.reminders.index'))->assertForbidden();

    $this->actingAs($staff)->get(route('admin.reminders.index'))->assertOk()
        ->assertSee('Relances des inactifs')->assertSee('1 client(s)');

    $this->actingAs($staff)->put(route('admin.reminders.update'), [
        'enabled' => '1', 'client_days' => 45, 'company_days' => 20, 'interval_days' => 10, 'max_steps' => 2, 'daily_limit' => 100, 'channel_mail' => '1',
    ])->assertSessionHasNoErrors();
    expect(ReminderSetting::current()->only(['client_days', 'max_steps', 'channel_push', 'channel_mail']))
        ->toBe(['client_days' => 45, 'max_steps' => 2, 'channel_push' => false, 'channel_mail' => true]);

    // Au moins un canal
    $this->actingAs($staff)->put(route('admin.reminders.update'), [
        'enabled' => '1', 'client_days' => 45, 'company_days' => 20, 'interval_days' => 10, 'max_steps' => 2, 'daily_limit' => 100,
    ])->assertSessionHasErrors('channels');

    $this->actingAs($staff)->post(route('admin.reminders.send'))->assertSessionHas('success');
    Bus::assertDispatched(SendInactivityReminders::class);
});
