<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        /* ── Boutiques approuvées ──────────────────────────────────
         * - withCount produits actifs → $shop->products_count
         * - avg_rating et reviews_count via sous-requêtes SQL
         *   pour éviter tout N+1 dans la vue
         * ────────────────────────────────────────────────────────── */
        $shops = Shop::where('is_approved', true)
            ->withCount([
                'products as products_count' => fn($q) => $q->where('is_active', true),
            ])
            ->addSelect(DB::raw('
                (SELECT AVG(r.rating)
                 FROM reviews r
                 INNER JOIN orders o ON o.id = r.order_id
                 WHERE o.shop_id = shops.id
                ) as avg_rating,
                (SELECT COUNT(r.id)
                 FROM reviews r
                 INNER JOIN orders o ON o.id = r.order_id
                 WHERE o.shop_id = shops.id
                ) as reviews_count
            '))
            ->latest()
            ->paginate(12);

        /* ── Commandes récentes du client ──────────────────────────
         * 4 dernières commandes avec la boutique associée
         * ────────────────────────────────────────────────────────── */
        $recentOrders = Order::where('user_id', $user->id)
            ->with('shop')
            ->latest()
            ->take(4)
            ->get();

        return view('dashboards.client', compact('shops', 'recentOrders'));
    }
}