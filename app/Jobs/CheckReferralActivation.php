<?php

namespace App\Jobs;

use App\Services\AffiliateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Une boutique parrainée a un nouveau produit ou une nouvelle commande : devient-elle « Active » ? */
class CheckReferralActivation implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $uniqueFor = 60;

    public function __construct(public int $shopId) {}

    public function uniqueId(): string
    {
        return (string) $this->shopId;
    }

    public function handle(AffiliateService $affiliates): void
    {
        $affiliates->checkActivation($this->shopId);
    }
}
