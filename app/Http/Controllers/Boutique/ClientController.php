<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * ═══════════════════════════════════════════════════════════════
 * ClientController — Gestion des clients de la boutique
 * ═══════════════════════════════════════════════════════════════
 *
 * Ce contrôleur liste tous les clients qui ont passé au moins
 * une commande dans la boutique du propriétaire connecté.
 *
 * Pour chaque client on calcule :
 *   - Total dépensé (tous les temps)
 *   - Nombre de commandes
 *   - Dernière commande (date)
 *   - Statut "top client" (dans le top 5 du mois)
 *
 * Routes :
 *   GET  /boutique/clients          → index()   (liste paginée)
 *   GET  /boutique/clients/{user}   → show()    (fiche client)
 */
class ClientController extends Controller
{
    /**
     * Liste paginée de tous les clients de la boutique.
     * Avec recherche par nom/téléphone et tri dynamique.
     */
    public function index(Request $request)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()->route('boutique.dashboard')
                ->with('error', 'Aucune boutique trouvée.');
        }

        $now = Carbon::now();

        // Normalisation : trim + null si vide
        $search = trim((string) $request->input('search', ''));
        $search = $search !== '' ? $search : null;

        // Validation du tri (liste blanche)
        $sortBy  = $request->input('sort', 'total_depense');
        $allowed = ['total_depense', 'nb_commandes', 'derniere_cmd'];
        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'total_depense';
        }

        /* ── RECHERCHE ────────────────────────────────────────────────
         * On cherche d'abord les user_id dans la table users qui matchent,
         * puis on filtre les orders par ces IDs (GROUP BY incompatible
         * avec whereHas sur colonnes non agrégées).
         * ──────────────────────────────────────────────────────────── */
        $filterUserIds = null; // null = pas de filtre actif

        if ($search !== null) {
            $filterUserIds = User::where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->pluck('id')->toArray();
        }

        /* ── TOP 5 DU MOIS ────────────────────────────────────────── */
        $topClientIds = $shop->orders()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->select('user_id', DB::raw('SUM(total) as total_mois'))
            ->groupBy('user_id')
            ->orderByDesc('total_mois')
            ->take(5)
            ->pluck('user_id')
            ->toArray();

        /* ── LISTE CLIENTS (groupée par user_id) ─────────────────── */
        $query = $shop->orders()
            ->with('user')
            ->select(
                'user_id',
                DB::raw('SUM(total)       as total_depense'),
                DB::raw('COUNT(*)         as nb_commandes'),
                DB::raw('MAX(created_at)  as derniere_cmd'),
                DB::raw('MIN(created_at)  as premiere_cmd')
            )
            ->groupBy('user_id');

        /* ── APPLICATION DU FILTRE ────────────────────────────────────
         * $filterUserIds === null  → aucun filtre (afficher tous)
         * $filterUserIds === []    → aucun user trouvé → résultat vide
         *                           on passe [-1] car whereIn([]) = no-op en SQL
         * $filterUserIds = [1,2,3] → filtrer sur ces IDs
         * ──────────────────────────────────────────────────────────── */
        if ($filterUserIds !== null) {
            $query->whereIn('user_id', empty($filterUserIds) ? [-1] : $filterUserIds);
        }

        $query->orderByDesc($sortBy);

        $clients = $query->paginate(15)->withQueryString();

        /* ── KPI GLOBAUX ──────────────────────────────────────────────
         * Statistiques affichées en haut de la page.
         * ──────────────────────────────────────────────────────────── */
        $totalClients     = $shop->orders()->distinct('user_id')->count('user_id');
        $nouveauxCeMois   = $shop->orders()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->distinct('user_id')
            ->count('user_id');
        $caTotal          = (float) $shop->orders()->sum('total');

        return view('boutique.clients.index', compact(
            'clients',
            'topClientIds',
            'search',
            'sortBy',
            'totalClients',
            'nouveauxCeMois',
            'caTotal',
            'shop'
        ));
    }

    /**
     * Fiche complète d'un client : toutes ses commandes dans la boutique.
     */
    public function show(User $user)
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            abort(403, 'Aucune boutique.');
        }

        // Récupère toutes les commandes de CE client dans CETTE boutique
        $commandes = $shop->orders()
            ->with(['items.product'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        // Statistiques du client dans cette boutique
        $stats = $shop->orders()
            ->where('user_id', $user->id)
            ->selectRaw('
                SUM(total) as total_depense,
                COUNT(*) as nb_commandes,
                MAX(created_at) as derniere_cmd,
                MIN(created_at) as premiere_cmd
            ')
            ->first();

        // Est-il dans le top 5 ce mois ?
        $now = Carbon::now();
        $topClientIds = $shop->orders()
            ->whereMonth('created_at', $now->month)
            ->select('user_id', DB::raw('SUM(total) as total_mois'))
            ->groupBy('user_id')
            ->orderByDesc('total_mois')
            ->take(5)
            ->pluck('user_id')
            ->toArray();

        $isTop = in_array($user->id, $topClientIds);

        $devise = $shop->currency ?? 'GNF';

        return view('boutique.clients.show', compact(
            'user',
            'commandes',
            'stats',
            'isTop',
            'shop',
            'devise',
            
        ));
    }
}