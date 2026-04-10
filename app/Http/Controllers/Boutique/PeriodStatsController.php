<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/**
 * PeriodStatsController — Endpoint AJAX sélecteur de période
 *
 * RÈGLE MÉTIER :
 *   $ca = sum(total) WHERE status = 'livrée' UNIQUEMENT
 *   Une commande non livrée ne génère aucun revenu.
 *   Cohérent avec le dashboard et les KPI.
 */
class PeriodStatsController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!$request->ajax()) abort(403);

        $shop = Auth::user()->shop;
        if (!$shop) return response()->json(['error' => 'Aucune boutique'], 403);

        $period = $request->input('period', 'this_month');
        $now    = Carbon::now();

        switch ($period) {
            case 'today':
                $from = $now->copy()->startOfDay(); $to = $now->copy()->endOfDay(); $groupBy = 'hour'; break;
            case 'yesterday':
                $from = $now->copy()->subDay()->startOfDay(); $to = $now->copy()->subDay()->endOfDay(); $groupBy = 'hour'; break;
            case '7days':
                $from = $now->copy()->subDays(6)->startOfDay(); $to = $now->copy()->endOfDay(); $groupBy = 'day'; break;
            case '30days':
                $from = $now->copy()->subDays(29)->startOfDay(); $to = $now->copy()->endOfDay(); $groupBy = 'day'; break;
            case 'this_month':
                $from = $now->copy()->startOfMonth(); $to = $now->copy()->endOfMonth(); $groupBy = 'day'; break;
            case 'last_month':
                $from = $now->copy()->subMonth()->startOfMonth(); $to = $now->copy()->subMonth()->endOfMonth(); $groupBy = 'day'; break;
            case 'this_year':
                $from = $now->copy()->startOfYear(); $to = $now->copy()->endOfYear(); $groupBy = 'month'; break;
            case 'last_year':
                $from = $now->copy()->subYear()->startOfYear(); $to = $now->copy()->subYear()->endOfYear(); $groupBy = 'month'; break;
            default:
                $from = $now->copy()->startOfMonth(); $to = $now->copy()->endOfMonth(); $groupBy = 'day'; break;
        }

        /*
         * $nb     = toutes commandes NON annulées (volume d'activité — KPI "Commandes")
         * $livres = commandes LIVRÉES             (sous-ensemble de $nb)
         * $ca     = sum(total) sur LIVRÉES only   ← règle métier
         * $panier = $ca / $livres                 ← panier moyen réel
         * $taux   = $livres / $nb × 100
         */
        $nb          = $shop->orders()->whereBetween('created_at', [$from, $to])->whereNotIn('status', ['annulée', 'cancelled'])->count();
        $livres      = $shop->orders()->whereBetween('created_at', [$from, $to])->where('status', 'livrée')->count();
        $caGross     = (float) $shop->orders()->whereBetween('created_at', [$from, $to])->where('status', 'livrée')->sum('total');

        /* Commissions PAYÉES sur cette période → soustraites du CA brut */
        $commissions = (float) \App\Models\CourierCommission::whereHas('order', function ($q) use ($shop, $from, $to) {
            $q->where('shop_id', $shop->id)->whereBetween('created_at', [$from, $to]);
        })->where('status', 'payée')->sum('amount');

        /* Revenu NET = CA brut - commissions payées */
        $ca     = max(0, $caGross - $commissions);
        $panier = $livres > 0 ? round($ca / $livres) : 0;
        $taux   = $nb     > 0 ? round(($livres / $nb) * 100, 1) : 0;

        /* ── Points du graphique — barres = CA livrées par intervalle ── */
        $points = [];

        if ($groupBy === 'hour') {
            for ($h = 0; $h < 24; $h += 2) {
                $hStart = $from->copy()->setHour($h)->setMinute(0)->setSecond(0);
                $hEnd   = $from->copy()->setHour($h + 1)->setMinute(59)->setSecond(59);
                $points[] = [
                    'label' => $hStart->format('H') . 'h',
                    'ca'    => max(0, (float) $shop->orders()->whereBetween('created_at', [$hStart, $hEnd])->where('status', 'livrée')->sum('total')
                                - (float) \App\Models\CourierCommission::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->whereBetween('created_at', [$hStart, $hEnd]))->where('status', 'payée')->sum('amount')),
                    'nb'    => $shop->orders()->whereBetween('created_at', [$hStart, $hEnd])->whereNotIn('status', ['annulée', 'cancelled'])->count(),
                ];
            }

        } elseif ($groupBy === 'day') {
            $diff = $from->diffInDays($to) + 1;

            if ($diff > 14) {
                $step = max(1, (int) ceil($diff / 14));
                $cur  = $from->copy();
                while ($cur <= $to) {
                    $end = $cur->copy()->addDays($step - 1)->endOfDay();
                    if ($end > $to) $end = $to->copy()->endOfDay();
                    $points[] = [
                        'label' => $cur->format('d/m'),
                        'ca'    => max(0, (float) $shop->orders()->whereBetween('created_at', [$cur->copy()->startOfDay(), $end])->where('status', 'livrée')->sum('total')
                                    - (float) \App\Models\CourierCommission::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->whereBetween('created_at', [$cur->copy()->startOfDay(), $end]))->where('status', 'payée')->sum('amount')),
                        'nb'    => $shop->orders()->whereBetween('created_at', [$cur->copy()->startOfDay(), $end])->whereNotIn('status', ['annulée', 'cancelled'])->count(),
                    ];
                    $cur->addDays($step);
                }
            } else {
                for ($d = 0; $d < $diff; $d++) {
                    $day = $from->copy()->addDays($d);
                    $points[] = [
                        'label' => $day->isoFormat('dd'),
                        'ca'    => max(0, (float) $shop->orders()->whereDate('created_at', $day->toDateString())->where('status', 'livrée')->sum('total')
                                    - (float) \App\Models\CourierCommission::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->whereDate('created_at', $day->toDateString()))->where('status', 'payée')->sum('amount')),
                        'nb'    => $shop->orders()->whereDate('created_at', $day->toDateString())->whereNotIn('status', ['annulée', 'cancelled'])->count(),
                    ];
                }
            }

        } elseif ($groupBy === 'month') {
            $cur = $from->copy()->startOfMonth();
            while ($cur <= $to) {
                $points[] = [
                    'label' => $cur->isoFormat('MMM'),
                    'ca'    => max(0, (float) $shop->orders()->whereMonth('created_at', $cur->month)->whereYear('created_at', $cur->year)->where('status', 'livrée')->sum('total')
                                - (float) \App\Models\CourierCommission::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->whereMonth('created_at', $cur->month)->whereYear('created_at', $cur->year))->where('status', 'payée')->sum('amount')),
                    'nb'    => $shop->orders()->whereMonth('created_at', $cur->month)->whereYear('created_at', $cur->year)->whereNotIn('status', ['annulée', 'cancelled'])->count(),
                ];
                $cur->addMonth();
            }
        }

        return response()->json([
            'ca'     => $ca,
            'nb'     => $nb,
            'panier' => $panier,
            'taux'   => $taux,
            'points' => $points,
        ]);
    }
}