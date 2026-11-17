<?php

namespace App\Console\Commands;

use App\Services\FollowUpService;
use Illuminate\Console\Command;

/** Toutes les 10 minutes : détecte les situations à relancer et met en file d'attente les envois dus. */
class RunFollowUps extends Command
{
    protected $signature = 'followups:run';

    protected $description = 'Crée et envoie les relances automatiques des boutiques';

    public function handle(FollowUpService $followUps): int
    {
        $r = $followUps->run();
        $this->info("Relances créées : {$r['created']}, suivantes : {$r['next']}, envois lancés : {$r['dispatched']}.");
        return self::SUCCESS;
    }
}
