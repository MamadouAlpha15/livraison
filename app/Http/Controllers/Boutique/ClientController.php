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

        $now    = Carbon::now();
        $search = $request->input('search');
        $sortBy = $request->input('sort', 'total_depense');

        /* ── VALIDATION DU TRI ────────────────────────────────────────
         * On n'accepte que des colonnes connues pour éviter les injections.
         * ──────────────────────────────────────────────────────────── */
        $allowed = ['total_depense', 'nb_commandes', 'derniere_cmd'];
        if (!in_array($sortBy, $allowed)) {
            $sortBy = 'total_depense';
        }

        /* ── RECHERCHE : on récupère d'abord les user_id correspondants ──
         * Problème : on ne peut pas faire whereHas() sur une requête
         * groupée car MySQL interdit de filtrer sur des colonnes non
         * agrégées dans un GROUP BY.
         *
         * Solution : on cherche d'abord les user_id dans la table users
         * qui matchent la recherche, puis on filtre les orders par ces IDs.
         * ──────────────────────────────────────────────────────────── */
        $userIds = null; // null = pas de filtre recherche
        if ($search) {
            $userIds = User::where(function ($q) use ($search) {
                    $q->where('name',  'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->pluck('id')
                ->toArray();
        }

        /* ── CALCUL DES TOP CLIENTS DU MOIS ──────────────────────────
         * On les calcule ici pour afficher le badge "Top" sur chaque
         * client qui figure dans le top 5 du mois en cours.
         * ──────────────────────────────────────────────────────────── */
        $topClientIds = $shop->orders()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->select('user_id', DB::raw('SUM(total) as total_mois'))
            ->groupBy('user_id')
            ->orderByDesc('total_mois')
            ->take(5)
            ->pluck('user_id')
            ->toArray();

        /* ── LISTE COMPLÈTE DES CLIENTS ───────────────────────────────
         * On groupe les commandes par user_id et on calcule les stats.
         * Si une recherche est active, on filtre sur les user_id trouvés.
         * ──────────────────────────────────────────────────────────── */
        $query = $shop->orders()
            ->with('user')
            ->select(
                'user_id',
                DB::raw('SUM(total) as total_depense'),    // total dépensé tous les temps
                DB::raw('COUNT(*) as nb_commandes'),        // nombre total de commandes
                DB::raw('MAX(created_at) as derniere_cmd'), // date de la dernière commande
                DB::raw('MIN(created_at) as premiere_cmd')  // date de la première commande
            )
            ->groupBy('user_id');

        /* ── APPLICATION DU FILTRE RECHERCHE ─────────────────────────
         * Si $userIds est un tableau vide → aucun résultat correspondant.
         * Si $userIds est null → pas de filtre (afficher tout).
         * ──────────────────────────────────────────────────────────── */
        if ($userIds !== null) {
            if (empty($userIds)) {
                // Aucun user ne correspond → forcer un résultat vide
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('user_id', $userIds);
            }
        }

        /* ── TRI ──────────────────────────────────────────────────────*/
        $query->orderByDesc($sortBy);

        // Paginer avec 15 clients par page, conserver les paramètres GET
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

        return view('boutique.clients.show', compact(
            'user',
            'commandes',
            'stats',
            'isTop',
            'shop'
        ));
    }
}