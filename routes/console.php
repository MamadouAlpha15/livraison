<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─────────────────────────────────────────────────────────────────────────────
// Tâche planifiée quotidienne : expire les abonnements dont la date est passée.
// Lance automatiquement chaque jour à minuit via le scheduler Laravel.
// Pour activer le scheduler, ajouter dans le cron serveur :
//   * * * * * php /chemin/vers/artisan schedule:run >> /dev/null 2>&1
// ─────────────────────────────────────────────────────────────────────────────
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $count = app(SubscriptionService::class)->expireOldSubscriptions();
    \Illuminate\Support\Facades\Log::info("[Scheduler] Expiration abonnements : {$count} traités.");
})->daily()->name('expire-subscriptions')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────────────────────
// Relance des paniers abandonnés (assistant IA, étape 4) : une fois par jour,
// à 10h (heure locale du serveur), pour ne pas notifier les clients en pleine nuit.
// ─────────────────────────────────────────────────────────────────────────────
Schedule::command('cart:remind-abandoned')->dailyAt('10:00')->name('remind-abandoned-carts')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────────────────────
// Vérifie chaque nuit que les photos produits existent réellement sur le disque
// (détecte les uploads cassés) — signale au SuperAdmin, qui peut prévenir le
// vendeur concerné directement dans son compte Shopio.
// ─────────────────────────────────────────────────────────────────────────────
Schedule::command('products:check-images')->dailyAt('03:00')->name('check-product-images')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────────────────────
// Vitrines programmées (Faire grandir ma vitrine → Personnalisation) : publiées à l'heure
// choisie. Elles le sont aussi à la première visite de la boutique, même sans scheduler.
// ─────────────────────────────────────────────────────────────────────────────
Schedule::call(function () {
    \App\Models\Shop::whereNotNull('storefront_publish_at')->where('storefront_publish_at', '<=', now())
        ->each(fn ($shop) => \App\Support\Growth::applyScheduled($shop));
})->everyMinute()->name('publish-scheduled-storefronts')->withoutOverlapping();
