<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Order;
use App\Models\ShopMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        /* ── Boutiques approuvées — filtrées par pays du client ── */
        $type  = request('type');
        $query = Shop::where('is_approved', true)
            ->withCount(['products as products_count' => fn($q) => $q->where('is_active', true)])
            ->withCount(['orders as sales_count'])
            ->addSelect(DB::raw('
                (SELECT AVG(r.rating)  FROM reviews r INNER JOIN orders o ON o.id = r.order_id WHERE o.shop_id = shops.id) as avg_rating,
                (SELECT COUNT(r.id)    FROM reviews r INNER JOIN orders o ON o.id = r.order_id WHERE o.shop_id = shops.id) as reviews_count
            '))
            ->orderByDesc('sales_count')
            ->orderByDesc(DB::raw('COALESCE(avg_rating, 0)'));

        if ($user->country) {
            $query->where('country', $user->country);
        }
        if ($type && $type !== 'Toutes') {
            $query->where('type', 'LIKE', "%{$type}%");
        }

        $shops = $query->paginate(12)->withQueryString();

        /* ── Stats globales ── */
        $shopQuery = Shop::where('is_approved', true);
        if ($user->country) $shopQuery->where('country', $user->country);
        $shopCount = $shopQuery->count();

        $productCount = Product::whereHas('shop', function($q) use ($user) {
            $q->where('is_approved', true);
            if ($user->country) $q->where('country', $user->country);
        })->where('is_active', true)->count();

        $deliveredCount = Order::where('status', Order::STATUS_LIVREE)->count();
        $clientCount    = User::where('role', 'client')->count();

        /* ── Catégories avec comptage ── */
        $catQuery = Shop::where('is_approved', true)->whereNotNull('type')->where('type', '!=', '');
        if ($user->country) $catQuery->where('country', $user->country);
        $categories = $catQuery->groupBy('type')
            ->selectRaw('type, count(*) as shop_count')
            ->orderByDesc('shop_count')
            ->get();

        /* ── Top boutiques sidebar ── */
        $topQuery = Shop::where('is_approved', true);
        if ($user->country) $topQuery->where('country', $user->country);
        $topShops = $topQuery
            ->withCount(['orders as sales_count'])
            ->addSelect(DB::raw('
                (SELECT AVG(r.rating) FROM reviews r INNER JOIN orders o ON o.id = r.order_id WHERE o.shop_id = shops.id) as avg_rating,
                (SELECT COUNT(r.id)   FROM reviews r INNER JOIN orders o ON o.id = r.order_id WHERE o.shop_id = shops.id) as reviews_count
            '))
            ->orderByDesc(DB::raw('COALESCE(avg_rating, 0)'))
            ->orderByDesc('sales_count')
            ->take(4)
            ->get();

        /* ── Commandes récentes ── */
        $recentOrders = Order::where('user_id', $user->id)
            ->with('shop')->latest()->take(4)->get();

        /* ── Favoris ── */
        $favoriteIds = $user->favorites()->pluck('shops.id')->toArray();

        /* ── Messages client ── */
        $clientId   = $user->id;
        $myMessages = ShopMessage::where(function($q) use ($clientId) {
                $q->where('sender_id', $clientId)->orWhere('receiver_id', $clientId);
            })
            ->with(['sender', 'receiver', 'product', 'product.shop'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function($m) use ($clientId) {
                return ($m->product?->shop_id ?? 0) . '-' . ($m->product_id ?? '0');
            });

        $myUnread = ShopMessage::where('receiver_id', $clientId)->whereNull('read_at')->count();

        return view('dashboards.client', compact(
            'shops', 'recentOrders', 'myMessages', 'myUnread',
            'shopCount', 'productCount', 'deliveredCount', 'clientCount',
            'categories', 'topShops', 'favoriteIds'
        ));
    }
}
