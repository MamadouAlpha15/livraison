<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\InactivityReminderService;

/** Lien « Me désinscrire » des e-mails de relance (lien signé, sans connexion). */
class ReminderUnsubscribeController extends Controller
{
    public function __invoke(User $user, InactivityReminderService $reminders)
    {
        $reminders->optOut($user);
        return view('reminders.unsubscribed');
    }
}
