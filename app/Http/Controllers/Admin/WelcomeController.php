<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\WelcomeWhatsapp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Admin → Bienvenue WhatsApp : l'équipe Shopio écrit à chaque nouvel inscrit (vendeur ou client) pour lui souhaiter
 * la bienvenue et lui proposer son aide. Envoi manuel : le lien wa.me ouvre WhatsApp avec le message prêt,
 * l'admin appuie sur « Envoyer », et la personne est marquée « contactée ». Aucun envoi automatique.
 * (À ne pas confondre avec App\Http\Controllers\WelcomeController, le marché des clients.)
 */
class WelcomeController extends Controller
{
    /** Inscrits des 60 derniers jours (e-mail vérifié), vendeurs (« admin ») et clients. */
    public const DAYS = 60;

    private static function base(): Builder
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->whereIn('role', ['admin', 'client'])
            ->where('created_at', '>=', now()->subDays(self::DAYS));
    }

    /** Personnes pas encore contactées (pastille du menu). 0 si la migration n'est pas encore passée. */
    public static function pending(): int
    {
        static $n = null;
        if ($n !== null) return $n;
        try {
            return $n = self::base()->whereNull('welcome_sent_at')->count();
        } catch (\Throwable $e) {
            return $n = 0;
        }
    }

    /** La migration 2026_11_24_100000_add_welcome_whatsapp_to_users est-elle passée ? */
    private static function ready(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn('users', 'welcome_sent_at');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function index(Request $request): View
    {
        // Base pas encore à jour : message clair au lieu d'une erreur 500
        if (!self::ready()) {
            return view('admin.welcome.index', [
                'users' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30), 'rows' => collect(),
                'vue' => 'todo', 'qui' => 'all', 'texts' => WelcomeWhatsapp::texts(), 'vars' => WelcomeWhatsapp::VARS,
                'stats' => ['todo' => 0, 'done' => 0, 'week' => 0], 'senders' => collect(), 'needsMigration' => true,
            ]);
        }
        $vue = in_array($request->query('vue'), ['todo', 'done', 'all'], true) ? $request->query('vue') : 'todo';
        $qui = in_array($request->query('qui'), ['all', 'vendor', 'client'], true) ? $request->query('qui') : 'all';

        $q = self::base()->with('shop');
        if ($vue === 'todo') $q->whereNull('welcome_sent_at');
        if ($vue === 'done') $q->whereNotNull('welcome_sent_at');
        if ($qui === 'vendor') $q->where('role', 'admin');
        if ($qui === 'client') $q->where('role', 'client');
        $users = $q->latest()->paginate(30)->withQueryString();

        $texts = WelcomeWhatsapp::texts();
        $rows = $users->getCollection()->map(function (User $u) use ($texts) {
            $contact = WelcomeWhatsapp::contact($u);
            $message = WelcomeWhatsapp::message($u, $contact, $texts);
            return ['user' => $u, 'contact' => $contact, 'message' => $message, 'link' => WelcomeWhatsapp::link($u, $contact, $message)];
        });

        return view('admin.welcome.index', [
            'users'   => $users,
            'rows'    => $rows,
            'vue'     => $vue,
            'qui'     => $qui,
            'texts'   => $texts,
            'vars'    => WelcomeWhatsapp::VARS,
            'stats'   => [
                'todo' => self::pending(),
                'done' => self::base()->whereNotNull('welcome_sent_at')->count(),
                'week' => self::base()->where('created_at', '>=', now()->subDays(7))->count(),
            ],
            // Admins qui ont envoyé les messages de cette page
            'senders' => User::whereIn('id', $users->getCollection()->pluck('welcome_sent_by')->filter()->unique())->pluck('name', 'id'),
        ]);
    }

    /** Marque la personne contactée (done=true) ou annule (done=false). */
    public function mark(Request $request, User $user): JsonResponse
    {
        abort_unless(in_array($user->role, ['admin', 'client'], true), 404);
        if (!self::ready()) return response()->json(['error' => 'Lancez d’abord la migration : php artisan migrate'], 503);
        $done = $request->boolean('done');
        $user->forceFill([
            'welcome_sent_at' => $done ? now() : null,
            'welcome_sent_by' => $done ? Auth::id() : null,
        ])->saveQuietly();

        return response()->json([
            'ok'      => true,
            'done'    => $done,
            'at'      => $done ? $user->welcome_sent_at->format('d/m H:i') : null,
            'by'      => $done ? (string) Auth::user()->name : null,
            'pending' => self::base()->whereNull('welcome_sent_at')->count(),
        ]);
    }

    /** Enregistre les deux messages, ou les remet par défaut (reset=1). */
    public function texts(Request $request): RedirectResponse
    {
        if ($request->boolean('reset')) {
            WelcomeWhatsapp::reset();
            return back()->with('success', 'Les messages par défaut sont remis.');
        }
        $data = $request->validate([
            'vendor' => ['required', 'string', 'max:' . WelcomeWhatsapp::MAX],
            'client' => ['required', 'string', 'max:' . WelcomeWhatsapp::MAX],
        ], [
            'vendor.required' => 'Écrivez le message pour les vendeurs.',
            'client.required' => 'Écrivez le message pour les clients.',
            'vendor.max'      => 'Le message pour les vendeurs dépasse 1000 caractères.',
            'client.max'      => 'Le message pour les clients dépasse 1000 caractères.',
        ]);
        WelcomeWhatsapp::save($data['vendor'], $data['client']);
        return back()->with('success', 'Messages de bienvenue enregistrés.');
    }
}
