<?php

namespace App\Events;

use App\Models\AdCampaign;

/**
 * Une campagne a changé de statut (émis par CampaignStateMachine, une fois la transaction validée).
 * $before : ses attributs juste avant le changement (ex. la raison de la pause qui vient d'être levée).
 */
class CampaignStatusChanged
{
    public function __construct(
        public AdCampaign $campaign,
        public string $from,
        public string $to,
        public array $before = [],
    ) {}
}
