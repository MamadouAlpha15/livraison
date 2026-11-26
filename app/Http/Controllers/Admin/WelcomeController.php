<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\WelcomeWhatsapp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Admin → Bienvenue WhatsApp : les nouveaux inscrits (vendeurs et clients au compte vérifié), avec pour chacun
 * un bouton qui ouvre WhatsApp sur sa conversation, message de bienvenue déjà écrit. Ouvrir WhatsApp marque
 * la personne « contactée » (on peut annuler). Les deux messages se modifient en haut de la page.
 */
class WelcomeController extends Controller
{
    public const DAYS = 60;

    private function base(string $kind)
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->whereIn('role', $kind === 'vendor' ? ['admin'] : ($kind === 'client' ? ['client'] : ['admin', 'client']))
            ->where('created_at', '>=', now()->subDays(self::DAYS));
    }

    /** Nombre de personnes à contacter (pastille du menu). */
    public static function pending(): int
    {
        return (new self)->base('all')->whereNull('welcome_sent_at')->count();
    }

    public function index(Request $request): View
    {
        $show = in_array($request->query('vue'), ['todo', 'done', 'all'], true) ? $request->query('vue') : 'todo';
        $kind = in_array($request->query('qui'), ['vendor', 'client'], true) ? $request->query('qui') : 'all';

        $q = $this->base($kind)->with('shop')->latest();
        if ($show === 'todo') $q->whereNull('welcome_sent_at');
        if ($show === 'done') $q->whereNotNull('welcome_sent_at');
        $users = $q->paginate(30)->withQueryString();

        $texts = WelcomeWhatsapp::texts();
        $rows = $users->getCollection()->map(fn (User $u) => [
            'user'    => $u,
            'contact' => WelcomeWhatsapp::contact($u),
            'link'    => WelcomeWhatsapp::link($u, $texts),
            'message' => WelcomeWhatsapp::message($u, $texts),
        ]);

        $stats = [
            'todo'    => $this->base('all')->whereNull('welcome_sent_at')->count(),
            'done'    => $this->base('all')->whereNotNull('welcome_sent_at')->count(),
            'week'    => $this->base('all')->where('created_at', '>=', now()->subDays(7))->count(),
        ];
        $senders = User::whereIn('id', $users->getCollection()->pluck('welcome_sent_by')->filter()->unique())->pluck('name', 'id');

        return view('admin.welcome.index', compact('users', 'rows', 'texts', 'stats', 'show', 'kind', 'senders'));
    }

    /** Marque la personne « contactée » (appelé quand on ouvre WhatsApp), ou annule. */
    public function mark(Request $request, User $user): JsonResponse
    {
        abort_unless(in_array($user->role, ['admin', 'client'], true), 404);
        $done = $request->boolean('done', true);
        $user->forceFill(['welcome_sent_at' => $done ? now() : null, 'welcome_sent_by' => $done ? Auth::id() : null])->saveQuietly();
        return response()->json(['ok' => true, 'done' => $done, 'at' => $done ? now()->format('d/m H:i') : null, 'pending' => self::pending()]);
    }

    /** Enregistre les deux messages (vendeurs, clients). */
    public function texts(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vendor' => ['required', 'string', 'max:' . WelcomeWhatsapp::MAX],
            'client' => ['required', 'string', 'max:' . WelcomeWhatsapp::MAX],
        ], ['*.required' => 'Le message ne peut pas être vide.', '*.max' => 'Le message est trop long (' . WelcomeWhatsapp::MAX . ' caractères au plus).']);
        if ($request->boolean('reset')) {
            WelcomeWhatsapp::save(WelcomeWhatsapp::DEFAULTS['vendor'], WelcomeWhatsapp::DEFAULTS['client']);
            return back()->with('success', 'Messages remis par défaut.');
        }
        WelcomeWhatsapp::save($data['vendor'], $data['client']);
        return back()->with('success', 'Messages de bienvenue enregistrés.');
    }
}
