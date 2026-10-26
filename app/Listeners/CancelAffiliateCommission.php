<?php

namespace App\Listeners;

use App\Events\SubscriptionRefunded;
use App\Services\AffiliateService;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

/** Abonnement remboursé : la commission d'affiliation liée est annulée. */
class CancelAffiliateCommission implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public int $tries = 3;
    public array $backoff = [60, 300];

    public function handle(SubscriptionRefunded $event): void
    {
        app(AffiliateService::class)->cancelForRefund($event->subscription);
    }
}
