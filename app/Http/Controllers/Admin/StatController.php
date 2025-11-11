<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shopId = $user->currentShopId();
        $isSuper = ($user->role === 'superadmin');

        // base queries
        $ordersQuery = Order::query();
        $paymentsQuery = Payment::query();

        if (!$isSuper) {
            $ordersQuery->where('shop_id', $shopId);
            $paymentsQuery->whereHas('order', function ($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            });
        }

        // Statistiques globales (utiliser des clones pour ne pas muter les builders)
        $totalOrders = (clone $ordersQuery)->count();
        $deliveredOrders = (clone $ordersQuery)->where('status', 'livrée')->count();

        $totalRevenue = (clone $paymentsQuery)->where('status', 'payé')->sum('amount');

        $ordersToday = (clone $ordersQuery)->whereDate('created_at', now())->count();
        $revenueToday = (clone $paymentsQuery)->where('status', 'payé')->whereDate('created_at', now())->sum('amount');

        // -------------------------------
        // Commandes par mois
        // -------------------------------
        $rawOrdersMonth = (clone $ordersQuery)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $months = range(1, 12);
        $monthNames = [1=>'Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

        $ordersPerMonth = collect($months)->mapWithKeys(function($m) use ($rawOrdersMonth, $monthNames){
            return [$monthNames[$m] => $rawOrdersMonth->get($m, 0)];
        });

        // -------------------------------
        // Commandes par jour du mois courant
        // -------------------------------
        $daysInMonth = now()->daysInMonth;
        $rawOrdersDay = (clone $ordersQuery)
            ->whereMonth('created_at', now()->month)
            ->selectRaw('DAY(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $ordersPerDay = collect(range(1, $daysInMonth))->mapWithKeys(function($d) use ($rawOrdersDay){
            // label "01", "02", ... for better chart display
            $label = str_pad($d, 2, '0', STR_PAD_LEFT);
            return [$label => $rawOrdersDay->get($d, 0)];
        });

        // -------------------------------
        // Revenus par jour du mois courant (à partir des payments)
        // -------------------------------
        $rawRevenueDay = (clone $paymentsQuery)
            ->where('status', 'payé')
            ->whereMonth('created_at', now()->month)
            ->selectRaw('DAY(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $revenuePerDay = collect(range(1, $daysInMonth))->mapWithKeys(function($d) use ($rawRevenueDay){
            $label = str_pad($d, 2, '0', STR_PAD_LEFT);
            return [$label => (float) $rawRevenueDay->get($d, 0)];
        });

        // -------------------------------
        // Top vendeurs
        // -------------------------------
        $topVendeurs = User::from('users')
            ->where('role', 'vendeur')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.id','users.name','users.email',
                DB::raw('SUM(CASE WHEN orders.status = "livrée" THEN orders.total ELSE 0 END) as revenue'))
            ->when(!$isSuper, fn($q)=> $q->where('orders.shop_id', $shopId))
            ->groupBy('users.id','users.name','users.email')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // -------------------------------
        // Top livreurs
        // -------------------------------
        $topLivreurs = User::from('users')
            ->where(function($q){ $q->where('role','livreur')->orWhere('role_in_shop','livreur'); })
            ->leftJoin('orders', 'users.id','=','orders.livreur_id')
            ->select('users.id','users.name','users.email',
                DB::raw('COUNT(CASE WHEN orders.status="livrée" THEN 1 END) as deliveries'))
            ->when(!$isSuper, fn($q)=> $q->where('orders.shop_id', $shopId))
            ->groupBy('users.id','users.name','users.email')
            ->orderByDesc('deliveries')
            ->take(5)
            ->get();

        return view('admin.stats.index', compact(
            'totalOrders','deliveredOrders','totalRevenue','ordersToday','revenueToday',
            'ordersPerMonth','ordersPerDay','revenuePerDay','topVendeurs','topLivreurs'
        ));
    }
}
