<?php

namespace App\Events;

use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Paiement d'abonnement remboursé (SubscriptionService::markRefunded). */
class SubscriptionRefunded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Subscription $subscription) {}
}
