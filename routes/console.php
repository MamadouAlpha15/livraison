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

// ─────────────────────────────────────────────────────────────────────────────
// Résumé de la journée envoyé aux boutiques à 20h (heure de Conakry = UTC) :
// commandes reçues, livrées et encaissées, en attente, stock faible.
// ─────────────────────────────────────────────────────────────────────────────
Schedule::command('shops:daily-summary')->dailyAt('20:00')->name('shop-daily-summary')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────────────────────
// Campagnes sponsorisées : compteurs d'affichages, clics et messages (tenus en cache)
// recopiés dans les statistiques par jour, que le vendeur voit sur sa page Campagnes.
// ─────────────────────────────────────────────────────────────────────────────
Schedule::command('campaigns:flush-stats')->everyTenMinutes()->name('campaigns-flush-stats')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────────────────────
// Campagnes, toutes les heures : démarre les mises en avant programmées, pause / reprise selon
// le stock, termine celles dont la date est passée, supprime les impayées de plus de 24 h,
// rappels (visuels à valider, fin dans 2 jours) et annulation des visuels restés sans réponse.
// ─────────────────────────────────────────────────────────────────────────────
Schedule::command('campaigns:tick')->hourly()->name('campaigns-tick')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────────────────────
// Affiliation, chaque jour : les commissions dont le délai de validation est passé
// deviennent disponibles au retrait (App\Services\AffiliateService::approveDueCommissions).
// ─────────────────────────────────────────────────────────────────────────────
Schedule::command('affiliates:approve-commissions')->dailyAt('02:30')->name('affiliates-approve-commissions')->withoutOverlapping();
