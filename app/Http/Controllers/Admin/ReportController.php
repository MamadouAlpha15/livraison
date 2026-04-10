<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\CourierCommission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/**
 * ReportController — Rapports & statistiques de la boutique
 *
 * RÈGLE MÉTIER — identique au dashboard et PeriodStatsController :
 *   Revenu NET = sum(orders.total WHERE status='livrée') - commissions PAYÉES
 *
 * On n'utilise PAS la table payments car elle peut avoir des montants différents.
 * La source unique de vérité = orders.total des commandes livrées.
 */
class ReportController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $shopId  = $user->currentShopId();
        $isSuper = ($user->role === 'superadmin');
        $now     = Carbon::now();

        $shop   = $user->shop ?? $user->assignedShop;
        $devise = $shop?->currency ?? 'GNF';

        $ordersQ = Order::query();
        if (!$isSuper) {
            $ordersQ->where('shop_id', $shopId);
        }

        $ordersQValid   = (clone $ordersQ)->whereNotIn('status', ['annulée', 'cancelled']);
        $ordersQLivrees = (clone $ordersQ)->where('status', 'livrée');

        /* ══ KPIs GLOBAUX ══ */
        $totalOrders      = $ordersQ->count();
        $pendingOrders    = (clone $ordersQ)->where('status', Order::STATUS_EN_ATTENTE)->count();
        $deliveringOrders = (clone $ordersQ)->where('status', Order::STATUS_EN_LIVRAISON)->count();
        $deliveredOrders  = (clone $ordersQ)->where('status', Order::STATUS_LIVREE)->count();
        $cancelledOrders  = (clone $ordersQ)->whereIn('status', ['annulée','cancelled'])->count();

        /* CA brut total = sum(orders.total) WHERE livrée — même source que dashboard */
        $totalRevenueGross = (float)(clone $ordersQLivrees)->sum('total');

        /* Commissions PAYÉES totales */
        $totalCommPaid = $isSuper
            ? (float) CourierCommission::where('status', 'payée')->sum('amount')
            : (float) CourierCommission::where('shop_id', $shopId)->where('status', 'payée')->sum('amount');

        /* Revenu NET global */
        $totalRevenue = max(0, $totalRevenueGross - $totalCommPaid);

        /* Taux de livraison */
        $totalOrdersValid = (clone $ordersQValid)->count();
        $tauxLivraison = $totalOrdersValid > 0
            ? round(($deliveredOrders / $totalOrdersValid) * 100, 1)
            : 0;

        /* Panier moyen NET */
        $panierMoyen = $deliveredOrders > 0
            ? round($totalRevenue / $deliveredOrders)
            : 0;

        /* ══ DONNÉES MENSUELLES — même logique que dashboard ══ */

        $ordersThisMonth = (clone $ordersQValid)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        /* CA brut ce mois = orders.total WHERE livrée (identique au dashboard) */
        $revenueThisMonthGross = (float)(clone $ordersQLivrees)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('total');

        /* Commissions payées ce mois */
        $commPaidThisMonth = $isSuper
            ? (float) CourierCommission::where('status', 'payée')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('amount')
            : (float) CourierCommission::where('shop_id', $shopId)
                ->where('status', 'payée')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('amount');

        /* Revenu NET ce mois */
        $revenueThisMonth = max(0, $revenueThisMonthGross - $commPaidThisMonth);

        /* Mois précédent NET */
        $revenueLastMonthGross = (float)(clone $ordersQLivrees)
            ->whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->sum('total');

        $commPaidLastMonth = $isSuper
            ? (float) CourierCommission::where('status', 'payée')
                ->whereMonth('created_at', $now->copy()->subMonth()->month)
                ->whereYear('created_at', $now->copy()->subMonth()->year)
                ->sum('amount')
            : (float) CourierCommission::where('shop_id', $shopId)
                ->where('status', 'payée')
                ->whereMonth('created_at', $now->copy()->subMonth()->month)
                ->whereYear('created_at', $now->copy()->subMonth()->year)
                ->sum('amount');

        $revenueLastMonthNet = max(0, $revenueLastMonthGross - $commPaidLastMonth) ?: 1;
        $revenueDelta = round((($revenueThisMonth - $revenueLastMonthNet) / $revenueLastMonthNet) * 100, 1);

        /* ══ GRAPHIQUE 6 MOIS — même logique que dashboard ══ */
        $chartMois = collect(range(5, 0))->map(function ($i) use ($ordersQLivrees, $ordersQValid, $now, $shopId, $isSuper) {
            $mois = $now->copy()->subMonths($i);

            /* CA brut = orders.total WHERE livrée ce mois */
            $revGross = (float)(clone $ordersQLivrees)
                ->whereMonth('created_at', $mois->month)
                ->whereYear('created_at', $mois->year)
                ->sum('total');

            /* Commissions payées ce mois */
            $commPaid = $isSuper
                ? (float) CourierCommission::where('status', 'payée')
                    ->whereMonth('created_at', $mois->month)
                    ->whereYear('created_at', $mois->year)
                    ->sum('amount')
                : (float) CourierCommission::where('shop_id', $shopId)
                    ->where('status', 'payée')
                    ->whereMonth('created_at', $mois->month)
                    ->whereYear('created_at', $mois->year)
                    ->sum('amount');

            return [
                'label'   => $mois->isoFormat('MMM YY'),
                'orders'  => (clone $ordersQValid)
                    ->whereMonth('created_at', $mois->month)
                    ->whereYear('created_at', $mois->year)
                    ->count(),
                'revenue' => max(0, $revGross - $commPaid),
                'actuel'  => $i === 0,
            ];
        });

        $maxRevenue = $chartMois->max('revenue') ?: 1;
        $maxOrders  = $chartMois->max('orders')  ?: 1;

        /* ══ COMMISSIONS ══ */
        $commissionsPending = $isSuper
            ? CourierCommission::where('status', 'en_attente')->sum('amount')
            : CourierCommission::where('shop_id', $shopId)->where('status', 'en_attente')->sum('amount');

        $commissionsPaid = $isSuper
            ? CourierCommission::where('status', 'payée')->sum('amount')
            : CourierCommission::where('shop_id', $shopId)->where('status', 'payée')->sum('amount');

        /* ══ ÉQUIPE ══ */
        if ($isSuper) {
            $vendors  = User::where('role', 'vendeur')->count();
            $livreurs = User::where('role', 'livreur')->orWhere('role_in_shop', 'livreur')->count();
        } else {
            $vendors = User::where('role', 'vendeur')
                ->whereIn('id', fn($s) => $s->select('user_id')->from('orders')
                    ->where('shop_id', $shopId)->groupBy('user_id'))
                ->count();

            $livreurs = User::where(fn($q) => $q
                    ->where('role', 'livreur')
                    ->orWhere('role_in_shop', 'livreur'))
                ->whereIn('id', fn($s) => $s->select('livreur_id')->from('orders')
                    ->where('shop_id', $shopId)->whereNotNull('livreur_id')->groupBy('livreur_id'))
                ->count();
        }

        /* ── Top 5 produits vendus ── */
        $topProducts = [];
        if ($shop) {
            $topProducts = $shop->products()
                ->withCount(['orderItems as ventes' => fn($q) => $q
                    ->whereHas('order', fn($o) => $o
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year))])
                ->orderByDesc('ventes')
                ->take(5)
                ->get();
        }

        return view('admin.reports.index', compact(
            'totalOrders', 'totalRevenue', 'pendingOrders',
            'deliveringOrders', 'deliveredOrders', 'cancelledOrders',
            'tauxLivraison', 'panierMoyen',
            'ordersThisMonth', 'revenueThisMonth', 'revenueDelta',
            'chartMois', 'maxRevenue', 'maxOrders',
            'commissionsPending', 'commissionsPaid',
            'vendors', 'livreurs',
            'topProducts',
            'shopId', 'isSuper',
            'shop', 'devise'
        ));
    }
}