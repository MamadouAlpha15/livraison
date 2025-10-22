<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function index()
    {
        // =========================
        // 📊 Stats globales
        // =========================
        $totalOrders     = Order::count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $totalRevenue    = Payment::where('status', 'paid')->sum('amount');
        $totalShops      = Shop::count();
        $totalClients    = User::where('role', 'client')->count();
        $totalLivreurs   = User::where('role', 'livreur')->count();

        // =========================
        // 🏆 Classement des vendeurs par revenus
        // =========================
        $topVendeurs = User::where('users.role', 'vendeur')
            ->leftJoin('shops', 'users.id', '=', 'shops.user_id')
            ->leftJoin('orders', 'shops.id', '=', 'orders.shop_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('SUM(CASE WHEN orders.status = "delivered" THEN orders.total ELSE 0 END) as revenue')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // =========================
        // 🏆 Classement des livreurs par nombre de livraisons
        // =========================
        $topLivreurs = User::where('users.role', 'livreur')
            ->leftJoin('orders', 'users.id', '=', 'orders.livreur_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(CASE WHEN orders.status = "delivered" THEN 1 END) as deliveries')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('deliveries')
            ->take(5)
            ->get();

        // =========================
        // 🔹 Envoi à la vue
        // =========================
        return view('admin.stats.index', compact(
            'totalOrders',
            'deliveredOrders',
            'totalRevenue',
            'totalShops',
            'totalClients',
            'totalLivreurs',
            'topVendeurs',
            'topLivreurs'
        ));
    }
}
