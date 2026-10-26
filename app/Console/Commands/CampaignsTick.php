<?php

namespace App\Console\Commands;

use App\Support\Campaigns\CampaignScheduler;
use Illuminate\Console\Command;

/** Passage horaire sur les campagnes : démarrages, pauses, fins, impayées, rappels (voir CampaignScheduler). */
class CampaignsTick extends Command
{
    protected $signature = 'campaigns:tick';

    protected $description = 'Démarre, met en pause, termine et relance les campagnes ; supprime les impayées ; envoie les rappels';

    public function handle(CampaignScheduler $scheduler): int
    {
        foreach ($scheduler->run() as $step => $count) {
            $this->line(str_pad($step, 16) . $count);
        }
        return self::SUCCESS;
    }
}
