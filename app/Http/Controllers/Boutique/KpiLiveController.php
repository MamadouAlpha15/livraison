<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class KpiLiveController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!$request->ajax()) abort(403);

        $shop = Auth::user()->shop;
        if (!$shop) return response()->json(['error' => 'Aucune boutique'], 403);

        $now   = Carbon::now();
        $today = $now->toDateString();
        $yest  = $now->copy()->subDay()->toDateString();

        /* ── Mois courant ── */
        $caGrossMonth        = (float) $shop->orders()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->where('status', 'livrée')->sum('total');
        $commissionsPaieesMonth = (float) \App\Models\CourierCommission::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year))->where('status', 'payée')->sum('amount');
        $caMonth             = max(0, $caGrossMonth - $commissionsPaieesMonth);

        $cmdMonth            = $shop->orders()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->whereNotIn('status', ['annulée', 'cancelled'])->count();
        $livres              = $shop->orders()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->where('status', 'livrée')->count();
        $totalCmdMonth       = $shop->orders()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->whereNotIn('status', ['annulée', 'cancelled'])->count();
        $panier              = $livres > 0 ? round($caMonth / $livres) : 0;
        $tauxLiv             = $totalCmdMonth > 0 ? round(($livres / $totalCmdMonth) * 100, 1) : 0;

        /* Delta CA vs mois précédent — comparer NET vs NET */
        $caGrossLastMonth      = (float) $shop->orders()->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->where('status', 'livrée')->sum('total');
        $commissionsLastMonth  = (float) \App\Models\CourierCommission::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year))->where('status', 'payée')->sum('amount');
        $caNetLastMonth        = max(0, $caGrossLastMonth - $commissionsLastMonth);
        $caDelta               = $caNetLastMonth > 0 ? round((($caMonth - $caNetLastMonth) / $caNetLastMonth) * 100, 1) : ($caMonth > 0 ? 100 : 0);

        /* Delta panier moyen vs mois précédent */
        $livresPrev   = $shop->orders()->whereMonth('created_at', $now->copy()->subMonth()->month)->whereYear('created_at', $now->copy()->subMonth()->year)->where('status', 'livrée')->count();
        $panierPrev   = $livresPrev > 0 ? round($caNetLastMonth / $livresPrev) : 0;
        $panierDelta  = $panierPrev > 0 ? round((($panier - $panierPrev) / $panierPrev) * 100, 1) : ($panier > 0 ? 100 : 0);

        /* ── Aujourd'hui ── */
        $caGrossToday = (float) $shop->orders()->whereDate('created_at', $today)->where('status', 'livrée')->sum('total');
        $commToday    = (float) \App\Models\CourierCommission::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->whereDate('created_at', $today))->where('status', 'payée')->sum('amount');
        $caToday      = max(0, $caGrossToday - $commToday);

        $cmdToday = $shop->orders()->whereDate('created_at', $today)->whereNotIn('status', ['annulée', 'cancelled'])->count();
        $cmdYest  = $shop->orders()->whereDate('created_at', $yest)->whereNotIn('status', ['annulée', 'cancelled'])->count();

        $caYest       = max(0, (float) $shop->orders()->whereDate('created_at', $yest)->where('status', 'livrée')->sum('total')
                        - (float) \App\Models\CourierCommission::whereHas('order', fn($q) => $q->where('shop_id', $shop->id)->whereDate('created_at', $yest))->where('status', 'payée')->sum('amount'));
        $caTodayDelta = $caYest > 0 ? round((($caToday - $caYest) / $caYest) * 100, 1) : ($caToday > 0 ? 100 : 0);

        /* ── Kanban ── */
        $kanban = [
            'en_attente'   => $shop->orders()->whereIn('status', ['pending', 'en attente', 'en_attente'])->count(),
            'confirmees'   => $shop->orders()->whereIn('status', ['confirmed', 'confirmée', 'processing'])->count(),
            'en_livraison' => $shop->orders()->whereIn('status', ['en_livraison', 'delivering', 'shipped'])->count(),
            'terminees'    => $shop->orders()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->where('status', 'livrée')->count(),
            'annulees'     => $shop->orders()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->whereIn('status', ['annulée', 'cancelled'])->count(),
        ];

        $devise = $shop->currency ?? 'GNF';

        return response()->json([
            'ca_month'          => number_format($caMonth, 0, ',', ' '),
            'ca_delta'          => $caDelta,
            'cmd_month'         => $cmdMonth,
            'cmd_today'         => $cmdToday,
            'cmd_yest'          => $cmdYest,
            'panier'            => number_format($panier, 0, ',', ' '),
            'panier_delta'      => $panierDelta,
            'taux_liv'          => $tauxLiv,
            'livres'            => $livres,
            'total_cmd_month'   => $totalCmdMonth,
            'ca_today'          => number_format($caToday, 0, ',', ' '),
            'ca_today_delta'    => $caTodayDelta,
            'devise'            => $devise,
            'kanban'            => $kanban,
        ]);
    }
}
