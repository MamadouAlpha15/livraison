<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Order;
use App\Models\ShopMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        /* ── Boutiques approuvées — filtrées par pays du client ── */
        $type   = request('type');
        $query  = Shop::where('is_approved', true)
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
            ->latest();

        // Filtre par pays du client connecté
        if ($user->country) {
            $query->where('country', $user->country);
        }

        // Filtre par type de boutique
        if ($type && $type !== 'Toutes') {
            $query->where('type', 'LIKE', "%{$type}%");
        }

        $shops = $query->paginate(12)->withQueryString();

        /* ── Commandes récentes ── */
        $recentOrders = Order::where('user_id', $user->id)
            ->with('shop')
            ->latest()
            ->take(4)
            ->get();

        /* ── Messages client ── */
        $clientId   = $user->id;
        $myMessages = ShopMessage::where(function($q) use ($clientId) {
                $q->where('sender_id',   $clientId)
                  ->orWhere('receiver_id', $clientId);
            })
            ->with(['sender', 'receiver', 'product', 'product.shop'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function($m) use ($clientId) {
                $shopId = $m->product?->shop_id ?? 0;
                return $shopId . '-' . ($m->product_id ?? '0');
            });

        $myUnread = ShopMessage::where('receiver_id', $clientId)
            ->whereNull('read_at')
            ->count();

        return view('dashboards.client', compact('shops', 'recentOrders', 'myMessages', 'myUnread'));
    }
}