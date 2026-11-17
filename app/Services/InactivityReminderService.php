<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\DeliveryCompany;
use App\Models\InactivityReminder;
use App\Models\Order;
use App\Models\Product;
use App\Models\PushSubscription;
use App\Models\ReminderSetting;
use App\Models\User;
use App\Notifications\InactivityReminderNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Relance des clients et des entreprises de livraison inactifs (« Tu nous manques »).
 *   - Inactif = aucune visite (site ou application) depuis N jours (réglable par public).
 *   - Jusqu'à max_steps rappels par période d'inactivité, espacés de interval_days ; tout recommence
 *     quand la personne revient (middleware TrackLastActivity, qui note aussi le retour).
 *   - Canaux : notification push, e-mail (avec lien de désinscription), SMS ; plafond d'envois par jour.
 *   - Les messages ne citent que des chiffres réels (nouveaux produits, panier, commandes en attente).
 * Lancé chaque jour par reminders:inactive ; réglages dans Admin → Relances.
 */
class InactivityReminderService
{
    /** Commandes terminées : elles n'attendent plus l'entreprise de livraison */
    private const DONE_ORDER_STATUSES = [Order::STATUS_LIVREE, Order::STATUS_ANNULEE, 'livree', 'annulee', 'delivered', 'cancelled'];

    private ?ReminderSetting $settings = null;

    public function settings(): ReminderSetting
    {
        return $this->settings ??= ReminderSetting::current();
    }

    public function refreshSettings(): void
    {
        $this->settings = null;
    }

    /** Comptes du public inactifs depuis assez longtemps, joignables et non désinscrits */
    public function candidates(string $audience): Builder
    {
        $days = $this->settings()->daysFor($audience);

        return User::query()
            ->where('role', $audience === InactivityReminder::AUDIENCE_COMPANY ? 'company' : 'client')
            ->whereNull('reminders_opt_out_at')
            ->whereNotNull('email_verified_at')
            ->whereNotNull('last_active_at')
            ->where('last_active_at', '<=', now()->subDays($days))
            ->when($audience === InactivityReminder::AUDIENCE_COMPANY, fn ($q) => $q->whereExists(fn ($e) => $e
                ->select(DB::raw(1))->from('delivery_companies')
                ->whereColumn('delivery_companies.user_id', 'users.id')
                ->where('delivery_companies.active', true)->where('delivery_companies.approved', true)));
    }

    /** Numéro du prochain rappel à envoyer (1, 2, 3…), ou null s'il n'en faut pas aujourd'hui */
    public function dueStep(User $user, string $audience): ?int
    {
        $s = $this->settings();
        if (!$user->last_active_at || $user->reminders_opt_out_at
            || $user->last_active_at->gt(now()->subDays($s->daysFor($audience)))) {
            return null;
        }

        $last = InactivityReminder::where('user_id', $user->id)
            ->where('sent_at', '>', $user->last_active_at)
            ->orderByDesc('sent_at')->first();
        if (!$last) {
            return 1;
        }
        if ($last->step >= $s->max_steps || $last->sent_at->gt(now()->subDays($s->interval_days))) {
            return null;
        }
        return $last->step + 1;
    }

    /**
     * Envoi du jour : clients puis entreprises, les plus anciens inactifs d'abord, dans la limite
     * du plafond quotidien (envois déjà faits aujourd'hui compris).
     *
     * @return array{client:int, company:int}
     */
    public function run(): array
    {
        $sent = [InactivityReminder::AUDIENCE_CLIENT => 0, InactivityReminder::AUDIENCE_COMPANY => 0];
        $s = $this->settings();
        if (!$s->enabled) {
            return $sent;
        }

        $remaining = $s->daily_limit - InactivityReminder::where('sent_at', '>=', today())->count();
        foreach (array_keys($sent) as $audience) {
            if ($remaining <= 0) {
                break;
            }
            $this->candidates($audience)->orderBy('id')->chunkById(200, function ($users) use ($audience, &$remaining, &$sent) {
                foreach ($users as $user) {
                    if ($remaining <= 0) {
                        return false;
                    }
                    if (($step = $this->dueStep($user, $audience)) !== null && $this->send($user, $audience, $step)) {
                        $sent[$audience]++;
                        $remaining--;
                    }
                }
            });
        }
        return $sent;
    }

    /** Nombre de rappels qui partiraient aujourd'hui (sans plafond), par public */
    public function preview(): array
    {
        $due = [];
        foreach (array_keys(InactivityReminder::AUDIENCES) as $audience) {
            $due[$audience] = 0;
            $this->candidates($audience)->chunkById(500, function ($users) use ($audience, &$due) {
                foreach ($users as $user) {
                    if ($this->dueStep($user, $audience) !== null) {
                        $due[$audience]++;
                    }
                }
            });
        }
        return $due;
    }

    /** Envoie le rappel n° $step et l'enregistre ; null si aucun canal n'est disponible */
    public function send(User $user, string $audience, int $step): ?InactivityReminder
    {
        $s = $this->settings();
        $channels = array_values(array_filter([
            $s->channel_push && PushSubscription::where('user_id', $user->id)->exists() ? 'push' : null,
            $s->channel_mail && $user->email ? 'mail' : null,
            $s->channel_sms && $user->phone ? 'sms' : null,
        ]));
        if ($channels === []) {
            return null;
        }

        [$title, $body, $url, $sms] = $this->message($user, $audience, $step);

        $reminder = InactivityReminder::create([
            'user_id'  => $user->id,
            'audience' => $audience,
            'step'     => $step,
            'channels' => $channels,
            'sent_at'  => now(),
        ]);
        $user->notify(new InactivityReminderNotification($audience, $step, $title, $body, $url, $channels, $sms));

        return $reminder;
    }

    /**
     * Texte du rappel, simple et avec des chiffres réels uniquement.
     *
     * @return array{0:string, 1:string, 2:string, 3:string} titre, message, lien, SMS
     */
    public function message(User $user, string $audience, int $step): array
    {
        $n = fn (int $v) => number_format($v, 0, ',', "\u{00A0}");

        if ($audience === InactivityReminder::AUDIENCE_COMPANY) {
            $company = $user->ownedCompany ?? ($user->company_id ? DeliveryCompany::find($user->company_id) : null);
            $pending = $company ? Order::where('delivery_company_id', $company->id)->whereNotIn('status', self::DONE_ORDER_STATUSES)->count() : 0;
            $week = Order::where('created_at', '>=', now()->subDays(7))->count();
            $name = $company?->name ?? $user->name;

            $title = match ($step) {
                1       => 'Les boutiques Shopio ont besoin de livreurs',
                2       => "{$name}, on ne t'a pas vu depuis un moment",
                default => 'Dernier rappel : reviens sur Shopio',
            };
            $body = $pending > 0
                ? "{$n($pending)} commande(s) confiée(s) à ton entreprise attendent ton suivi sur Shopio."
                : ($week > 0
                    ? "Les boutiques Shopio ont reçu {$n($week)} commandes cette semaine. Connecte-toi pour recevoir des livraisons."
                    : 'Connecte-toi à ton espace Shopio pour recevoir les prochaines livraisons des boutiques.');
            if ($step >= $this->settings()->max_steps) {
                $body .= " C'est notre dernier rappel.";
            }
            $url = route('company.dashboard');
            $sms = 'Shopio : ' . ($pending > 0 ? "{$pending} commande(s) attendent ton entreprise." : 'des boutiques cherchent des livreurs.') . ' ' . $url;

            return [$title, $body, $url, $sms];
        }

        $first = trim(explode(' ', trim($user->name))[0] ?? '') ?: $user->name;
        $newProducts = Product::where('is_active', true)->where('created_at', '>', $user->last_active_at)->count();
        $cart = (int) CartItem::where('user_id', $user->id)->sum('quantity');

        $title = match ($step) {
            1       => "{$first}, tu nous manques sur Shopio",
            2       => 'De nouveaux produits t\'attendent',
            default => 'Dernier rappel de Shopio',
        };
        $parts = [];
        if ($newProducts > 0) {
            $parts[] = "Depuis ta dernière visite, {$n($newProducts)} nouveaux produits sont arrivés sur Shopio.";
        }
        if ($cart > 0) {
            $parts[] = "Tu as encore {$n($cart)} article(s) dans ton panier.";
        }
        if ($parts === []) {
            $parts[] = 'Viens découvrir les boutiques et les produits du moment sur Shopio, livrés chez toi.';
        }
        if ($step >= $this->settings()->max_steps) {
            $parts[] = "C'est notre dernier rappel.";
        }
        $url = url('/');
        $sms = 'Shopio : ' . ($newProducts > 0 ? "{$newProducts} nouveaux produits depuis ta dernière visite." : 'de nouvelles offres t\'attendent.') . ' ' . $url;

        return [$title, implode(' ', $parts), $url, $sms];
    }

    /** Stop aux relances pour ce compte (lien dans l'e-mail) */
    public function optOut(User $user): void
    {
        if (!$user->reminders_opt_out_at) {
            $user->forceFill(['reminders_opt_out_at' => now()])->save();
        }
    }

    /**
     * Visite d'un compte : dernière activité mise à jour (au plus toutes les 15 minutes) et les rappels
     * reçus pendant son absence sont comptés comme « revenu ».
     */
    public function touch(User $user): void
    {
        $previous = $user->last_active_at;
        if ($previous && $previous->gt(now()->subMinutes(15))) {
            return;
        }
        // Sans toucher updated_at : la date de modification du compte garde son sens
        DB::table('users')->where('id', $user->id)->update(['last_active_at' => now()]);
        $user->setAttribute('last_active_at', now());
        $user->syncOriginalAttribute('last_active_at');

        if ($previous) {
            InactivityReminder::where('user_id', $user->id)->whereNull('returned_at')
                ->where('sent_at', '>', $previous)->update(['returned_at' => now(), 'updated_at' => now()]);
        }
    }

    /** Chiffres de la page admin : envois et retours sur 30 jours, désinscrits */
    public function stats(): array
    {
        $since = now()->subDays(30);
        $rows = InactivityReminder::where('sent_at', '>=', $since)
            ->selectRaw('audience, count(*) as sent, sum(case when returned_at is not null then 1 else 0 end) as returned')
            ->groupBy('audience')->get()->keyBy('audience');

        $out = [];
        foreach (array_keys(InactivityReminder::AUDIENCES) as $audience) {
            $sent = (int) ($rows[$audience]->sent ?? 0);
            $returned = (int) ($rows[$audience]->returned ?? 0);
            $out[$audience] = ['sent' => $sent, 'returned' => $returned, 'rate' => $sent ? (int) round($returned * 100 / $sent) : null];
        }
        $out['opt_outs'] = User::whereNotNull('reminders_opt_out_at')->count();
        return $out;
    }
}
