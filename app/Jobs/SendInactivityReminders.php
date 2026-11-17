<?php

namespace App\Jobs;

use App\Services\InactivityReminderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Envoi des rappels lancé à la main depuis Admin → Relances (« Envoyer maintenant »). */
class SendInactivityReminders implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;
    public int $uniqueFor = 600;

    public function handle(InactivityReminderService $reminders): void
    {
        $reminders->run();
    }
}
