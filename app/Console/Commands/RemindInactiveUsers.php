<?php

namespace App\Console\Commands;

use App\Services\InactivityReminderService;
use Illuminate\Console\Command;

/** Chaque jour : rappel « Tu nous manques » aux clients et entreprises de livraison inactifs. */
class RemindInactiveUsers extends Command
{
    protected $signature = 'reminders:inactive {--dry-run : Compte les rappels à envoyer sans rien envoyer}';

    protected $description = 'Relance les clients et entreprises de livraison qui ne sont pas revenus sur Shopio';

    public function handle(InactivityReminderService $reminders): int
    {
        if ($this->option('dry-run')) {
            $due = $reminders->preview();
            $this->info("À relancer : {$due['client']} client(s), {$due['company']} entreprise(s) de livraison.");
            return self::SUCCESS;
        }

        $sent = $reminders->run();
        $this->info("Rappels envoyés : {$sent['client']} client(s), {$sent['company']} entreprise(s) de livraison.");
        return self::SUCCESS;
    }
}
