<?php

namespace App\Listeners;

use App\Events\SubscriptionPaid;
use App\Services\AffiliateService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

/** Abonnement payé par une boutique parrainée : commission, statut « Abonnée », mois offert au parrain. */
class CreateAffiliateCommission implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;
    public array $backoff = [60, 300];

    public function handle(SubscriptionPaid $event): void
    {
        app(AffiliateService::class)->handleSubscriptionPayment($event->subscription->fresh());
    }
}
