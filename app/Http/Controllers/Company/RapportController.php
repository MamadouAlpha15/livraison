<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CourierCommission;
use App\Models\DeliveryCompany;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $user    = auth()->user();
        $company = DeliveryCompany::forUser($user);

        if (!$company) {
            return redirect()->route('company.dashboard');
        }

        $days  = (int) $request->query('days', 30);
        $days  = in_array($days, [7, 30, 90, 365]) ? $days : 30;
        $from  = Carbon::now()->subDays($days)->startOfDay();
        $devise = $company->currency ?? 'GNF';

        // ── Commandes
        $ordersBase = Order::where('delivery_company_id', $company->id)
            ->where('created_at', '>=', $from);

        $totalOrders    = (clone $ordersBase)->count();
        $totalLivrees   = (clone $ordersBase)->where('status', Order::STATUS_LIVREE)->count();
        $totalAnnulees  = (clone $ordersBase)->where('status', Order::STATUS_ANNULEE)->count();
        $totalEnCours   = (clone $ordersBase)->whereIn('status', [Order::STATUS_EN_LIVRAISON, Order::STATUS_CONFIRMEE])->count();
        $tauxReussite   = ($totalLivrees + $totalAnnulees) > 0
            ? round($totalLivrees / ($totalLivrees + $totalAnnulees) * 100, 1)
            : null;

        // ── Revenus (commissions)
        $commBase   = CourierCommission::whereHas('order', fn($q) => $q->where('delivery_company_id', $company->id))
            ->where('created_at', '>=', $from);
        $revenusTotal   = (clone $commBase)->sum('amount');
        $revenusEncaiss = (clone $commBase)->where('status', CourierCommission::STATUS_PAYEE)->sum('amount');
        $revenusAttente = (clone $commBase)->where('status', CourierCommission::STATUS_EN_ATTENTE)->sum('amount');

        // ── Délai moyen
        $avgMins = Order::where('delivery_company_id', $company->id)
            ->where('status', Order::STATUS_LIVREE)
            ->where('created_at', '>=', $from)
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as m')
            ->value('m');
        $avgMins = $avgMins ? (int) round($avgMins) : null;

        // ── Note moyenne
        $ratingBase  = Review::whereHas('order', fn($q) =>
            $q->where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE)
        )->whereNotNull('rating')->where('rating', '>', 0)->where('created_at', '>=', $from);
        $ratingCount = (clone $ratingBase)->count();
        $avgRating   = $ratingCount > 0 ? round((float)(clone $ratingBase)->avg('rating'), 1) : null;

        // ── Top livreurs
        $topDrivers = Driver::withCount(['orders as livrees' => fn($q) =>
                $q->where('delivery_company_id', $company->id)
                  ->where('status', Order::STATUS_LIVREE)
                  ->where('created_at', '>=', $from)
            ])
            ->where('delivery_company_id', $company->id)
            ->orderByDesc('livrees')
            ->limit(8)
            ->get();

        // ── Performance par zone
        $zonePerf = Order::where('delivery_company_id', $company->id)
            ->where('status', Order::STATUS_LIVREE)
            ->where('created_at', '>=', $from)
            ->whereNotNull('delivery_zone_id')
            ->with('deliveryZone')
            ->get()
            ->groupBy('delivery_zone_id')
            ->map(fn($grp) => [
                'name'    => $grp->first()->deliveryZone?->name ?? 'Zone #' . $grp->first()->delivery_zone_id,
                'count'   => $grp->count(),
                'revenue' => $grp->sum('delivery_fee'),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(8);

        // ── Top boutiques partenaires
        $topShops = Order::where('delivery_company_id', $company->id)
            ->where('created_at', '>=', $from)
            ->whereNotNull('shop_id')
            ->with('shop')
            ->get()
            ->groupBy('shop_id')
            ->map(fn($grp) => [
                'name'     => $grp->first()->shop?->name ?? 'Boutique #' . $grp->first()->shop_id,
                'total'    => $grp->count(),
                'livrees'  => $grp->where('status', Order::STATUS_LIVREE)->count(),
                'revenue'  => $grp->sum('delivery_fee'),
            ])
            ->sortByDesc('total')
            ->values()
            ->take(8);

        // ── Graphe commandes par jour
        $chartDays   = min($days, 30);
        $ordersChart = collect();
        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->format('Y-m-d');
            $ordersChart->push([
                'label' => Carbon::now()->subDays($i)->format('d/m'),
                'total' => Order::where('delivery_company_id', $company->id)
                    ->whereDate('created_at', $day)->count(),
                'livrees' => Order::where('delivery_company_id', $company->id)
                    ->whereDate('created_at', $day)->where('status', Order::STATUS_LIVREE)->count(),
            ]);
        }

        return view('company.rapport.index', compact(
            'company', 'devise', 'days',
            'totalOrders', 'totalLivrees', 'totalAnnulees', 'totalEnCours', 'tauxReussite',
            'revenusTotal', 'revenusEncaiss', 'revenusAttente',
            'avgMins', 'avgRating', 'ratingCount',
            'topDrivers', 'zonePerf', 'topShops',
            'ordersChart'
        ));
    }
}
