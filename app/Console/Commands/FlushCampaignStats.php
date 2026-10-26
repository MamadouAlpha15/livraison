<?php

namespace App\Console\Commands;

use App\Support\Campaigns\CampaignTracker;
use Illuminate\Console\Command;

/** Recopie les compteurs des campagnes (affichages, clics, messages) dans les statistiques par jour. */
class FlushCampaignStats extends Command
{
    protected $signature = 'campaigns:flush-stats';

    protected $description = 'Enregistre les compteurs des campagnes sponsorisées dans ad_campaign_stats_daily';

    public function handle(): int
    {
        $rows = CampaignTracker::flush();
        $this->info($rows . ' ligne(s) de statistiques mises à jour.');
        return self::SUCCESS;
    }
}
