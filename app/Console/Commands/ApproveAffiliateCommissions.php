<?php

namespace App\Console\Commands;

use App\Services\AffiliateService;
use Illuminate\Console\Command;

/** Chaque jour : les commissions dont le délai de validation est passé deviennent disponibles au retrait. */
class ApproveAffiliateCommissions extends Command
{
    protected $signature = 'affiliates:approve-commissions';

    protected $description = 'Valide les commissions d\'affiliation dont le délai de validation est passé';

    public function handle(AffiliateService $affiliates): int
    {
        $count = $affiliates->approveDueCommissions();
        $this->info("{$count} commission(s) validée(s).");
        return self::SUCCESS;
    }
}
