<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/**
 * ═══════════════════════════════════════════════════════════════
 * PeriodStatsController — Endpoint AJAX pour le sélecteur de période
 * ═══════════════════════════════════════════════════════════════
 *
 * Route : GET /boutique/period-stats?period=xxx
 * Retourne un JSON avec :
 *   - ca      : chiffre d'affaires de la période
 *   - nb      : nombre de commandes
 *   - panier  : panier moyen
 *   - taux    : taux de livraison (%)
 *   - points  : tableau de points pour le mini-graphique
 *               chaque point = { label, ca, nb }
 *
 * Périodes acceptées :
 *   yesterday, today, 7days, 30days,
 *   this_month, last_month, this_year, last_year
 */
class PeriodStatsController extends Controller
{
    public function __invoke(Request $request)
    {
        /* ── Sécurité : requête AJAX uniquement ────────────────────── */
        if (!$request->ajax()) {
            abort(403);
        }

        $shop = Auth::user()->shop;
        if (!$shop) {
            return response()->json(['error' => 'Aucune boutique'], 403);
        }

        $period = $request->input('period', 'this_month');
        $now    = Carbon::now();

        /* ── Calcul des dates de début et fin selon la période ────────
         * On définit $from et $to pour filtrer les commandes.
         * On définit aussi $groupBy pour savoir comment grouper
         * les points du graphique (par jour, semaine ou mois).
         * ──────────────────────────────────────────────────────────── */
        switch ($period) {

            case 'today':
                $from    = $now->copy()->startOfDay();
                $to      = $now->copy()->endOfDay();
                $groupBy = 'hour';   // graphique par heure
                break;

            case 'yesterday':
                $from    = $now->copy()->subDay()->startOfDay();
                $to      = $now->copy()->subDay()->endOfDay();
                $groupBy = 'hour';
                break;

            case '7days':
                $from    = $now->copy()->subDays(6)->startOfDay();
                $to      = $now->copy()->endOfDay();
                $groupBy = 'day';    // graphique par jour
                break;

            case '30days':
                $from    = $now->copy()->subDays(29)->startOfDay();
                $to      = $now->copy()->endOfDay();
                $groupBy = 'day';
                break;

            case 'this_month':
                $from    = $now->copy()->startOfMonth();
                $to      = $now->copy()->endOfMonth();
                $groupBy = 'day';
                break;

            case 'last_month':
                $from    = $now->copy()->subMonth()->startOfMonth();
                $to      = $now->copy()->subMonth()->endOfMonth();
                $groupBy = 'day';
                break;

            case 'this_year':
                $from    = $now->copy()->startOfYear();
                $to      = $now->copy()->endOfYear();
                $groupBy = 'month'; // graphique par mois
                break;

            case 'last_year':
                $from    = $now->copy()->subYear()->startOfYear();
                $to      = $now->copy()->subYear()->endOfYear();
                $groupBy = 'month';
                break;

            default:
                $from    = $now->copy()->startOfMonth();
                $to      = $now->copy()->endOfMonth();
                $groupBy = 'day';
                break;
        }

        /* ── Requête de base filtrée sur la période ───────────────────*/
        $base = $shop->orders()
            ->whereBetween('created_at', [$from, $to]);

        /* ── STATS GLOBALES DE LA PÉRIODE ────────────────────────────*/
        $ca     = (float) $base->sum('total');
        $nb     = $base->count();
        $livres = (clone $base)->where('status', 'livrée')->count();
        $panier = $nb > 0 ? round($ca / $nb) : 0;
        $taux   = $nb > 0 ? round(($livres / $nb) * 100, 1) : 0;

        /* ── POINTS DU MINI-GRAPHIQUE ────────────────────────────────
         * On génère les points selon le groupBy pour afficher
         * les barres correctement dans l'interface.
         * ──────────────────────────────────────────────────────────── */
        $points = [];

        if ($groupBy === 'hour') {
            /* Grouper par heure sur 24h (pour "aujourd'hui" et "hier") */
            for ($h = 0; $h < 24; $h += 2) { // toutes les 2h pour ne pas surcharger
                $hStart = $from->copy()->setHour($h)->setMinute(0)->setSecond(0);
                $hEnd   = $from->copy()->setHour($h + 1)->setMinute(59)->setSecond(59);
                $points[] = [
                    'label' => $hStart->format('H') . 'h',
                    'ca'    => (float) $shop->orders()->whereBetween('created_at', [$hStart, $hEnd])->sum('total'),
                    'nb'    => $shop->orders()->whereBetween('created_at', [$hStart, $hEnd])->count(),
                ];
            }

        } elseif ($groupBy === 'day') {
            /* Grouper par jour (pour 7j, 30j, ce mois, mois dernier) */
            $diff = $from->diffInDays($to) + 1;
            // Si trop de jours, on regroupe par semaine pour lisibilité
            if ($diff > 14) {
                // Grouper par tranche de ~3 jours
                $step = max(1, (int) ceil($diff / 14));
                $cur  = $from->copy();
                while ($cur <= $to) {
                    $end = $cur->copy()->addDays($step - 1)->endOfDay();
                    if ($end > $to) $end = $to->copy()->endOfDay();
                    $points[] = [
                        'label' => $cur->format('d/m'),
                        'ca'    => (float) $shop->orders()->whereBetween('created_at', [$cur->startOfDay(), $end])->sum('total'),
                        'nb'    => $shop->orders()->whereBetween('created_at', [$cur->startOfDay(), $end])->count(),
                    ];
                    $cur->addDays($step);
                }
            } else {
                for ($d = 0; $d < $diff; $d++) {
                    $day = $from->copy()->addDays($d);
                    $points[] = [
                        'label' => $day->isoFormat('dd'),
                        'ca'    => (float) $shop->orders()->whereDate('created_at', $day->toDateString())->sum('total'),
                        'nb'    => $shop->orders()->whereDate('created_at', $day->toDateString())->count(),
                    ];
                }
            }

        } elseif ($groupBy === 'month') {
            /* Grouper par mois (pour cette année, année dernière) */
            $cur = $from->copy()->startOfMonth();
            while ($cur <= $to) {
                $points[] = [
                    'label' => $cur->isoFormat('MMM'),
                    'ca'    => (float) $shop->orders()
                                    ->whereMonth('created_at', $cur->month)
                                    ->whereYear('created_at', $cur->year)
                                    ->sum('total'),
                    'nb'    => $shop->orders()
                                    ->whereMonth('created_at', $cur->month)
                                    ->whereYear('created_at', $cur->year)
                                    ->count(),
                ];
                $cur->addMonth();
            }
        }

        /* ── Réponse JSON ─────────────────────────────────────────── */
        return response()->json([
            'ca'     => $ca,
            'nb'     => $nb,
            'panier' => $panier,
            'taux'   => $taux,
            'points' => $points,
        ]);
    }
}