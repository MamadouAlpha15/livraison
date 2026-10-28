<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FollowUpRuleRequest;
use App\Jobs\SendInactivityReminders;
use App\Models\FollowUp;
use App\Models\FollowUpRule;
use App\Services\FollowUp\FollowUpCatalog;
use App\Services\FollowUpService;
use App\Models\InactivityReminder;
use App\Services\InactivityReminderService;
use Illuminate\Http\Request;

/**
 * Équipe Shopio → Relances : rappels « Tu nous manques » aux clients et entreprises de livraison inactifs.
 * Réglages, chiffres (envois, retours, désinscrits), derniers envois, envoi immédiat.
 */
class ReminderController extends Controller
{
    public function __construct(private InactivityReminderService $reminders) {}

    public function index(Request $request)
    {
        $audience = $request->query('public');

        return view('admin.reminders.index', [
            'settings' => $this->reminders->settings(),
            'stats'    => $this->reminders->stats(),
            'due'      => $this->reminders->preview(),
            'sentToday' => InactivityReminder::where('sent_at', '>=', today())->count(),
            'audience' => $audience,
            'vendorRule'  => FollowUpRule::resolve(null, FollowUpCatalog::INACTIVE_VENDOR),
            'vendorSent'  => FollowUp::where('type', FollowUpCatalog::INACTIVE_VENDOR)->where('status', FollowUp::STATUS_SENT)->where('sent_at', '>=', now()->subDays(30))->count(),
            'shopStats'   => app(FollowUpService::class)->stats(null),
            'recent'   => InactivityReminder::with('user:id,name,email,phone')
                ->when(isset(InactivityReminder::AUDIENCES[$audience]), fn ($q) => $q->where('audience', $audience))
                ->latest('sent_at')->paginate(25)->withQueryString(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'client_days'   => ['required', 'integer', 'between:1,365'],
            'company_days'  => ['required', 'integer', 'between:1,365'],
            'interval_days' => ['required', 'integer', 'between:1,180'],
            'max_steps'     => ['required', 'integer', 'between:1,10'],
            'daily_limit'   => ['required', 'integer', 'between:1,100000'],
        ]);
        foreach (['enabled', 'channel_push', 'channel_mail', 'channel_sms'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        if (!$data['channel_push'] && !$data['channel_mail'] && !$data['channel_sms']) {
            return back()->withErrors(['channels' => 'Garde au moins un canal : notification, e-mail ou SMS.'])->withInput();
        }

        $this->reminders->settings()->update($data);
        $this->reminders->refreshSettings();
        return back()->with('success', 'Réglages des relances enregistrés.');
    }

    /** Relance « vendeur inactif » (règle de l'admin, envoyée par FollowUpService) */
    public function updateVendorRule(FollowUpRuleRequest $request)
    {
        FollowUpRule::updateOrCreate(['shop_id' => null, 'type' => FollowUpCatalog::INACTIVE_VENDOR], $request->ruleData());
        return back()->with('success', 'Relance des vendeurs inactifs enregistrée.');
    }

    public function sendNow()
    {
        if (!$this->reminders->settings()->enabled) {
            return back()->withErrors(['send' => 'Les relances sont désactivées : active-les d\'abord dans les réglages.']);
        }
        SendInactivityReminders::dispatch();
        return back()->with('success', 'Envoi lancé : les rappels partent en arrière-plan, dans la limite du plafond du jour.');
    }
}
