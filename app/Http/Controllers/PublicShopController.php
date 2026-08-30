<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopVisit;

class PublicShopController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $search = $request->input('q');
        $type   = $request->input('type');

        $query = Shop::where('is_approved', true)
            ->withCount('products')
            ->with(['products' => function ($q) {
                $q->select('id', 'shop_id', 'name', 'category');
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',    'like', "%{$search}%")
                  ->orWhere('type',  'like', "%{$search}%")
                  ->orWhereHas('products', function ($pq) use ($search) {
                      $pq->where('name',     'like', "%{$search}%")
                         ->orWhere('category','like', "%{$search}%");
                  });
            });
        }
        if ($type) {
            $query->where('type', $type);
        }

        $shops       = $query->latest()->paginate(12)->withQueryString();
        $totalShops  = Shop::where('is_approved', true)->count();
        $types       = Shop::where('is_approved', true)->whereNotNull('type')->distinct()->pluck('type');

        $q = $search;
        return view('shops.index', compact('shops', 'totalShops', 'types', 'type', 'q'));
    }

    public function products(Shop $shop)
    {
        abort_unless($shop->is_approved, 404);

        ShopVisit::record($shop->id);

        $products = $shop->products()
            ->where('is_active', true)
            ->latest()
            ->paginate(20);

        $categories = $shop->products()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();

        // Avis réels du vendeur (remplace les faux "(rand(10,200))" affichés avant sur chaque carte produit).
        $reviewStats  = Review::where('vendeur_id', $shop->user_id)
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();
        $reviewCount  = (int) ($reviewStats->cnt ?? 0);
        $reviewAvg    = $reviewCount > 0 ? round((float) $reviewStats->avg_rating, 1) : null;

        // Commandes livrées : signal de confiance ("X commandes livrées") pour rassurer un nouveau client.
        $deliveredCount = Order::where('shop_id', $shop->id)
            ->where('status', Order::STATUS_LIVREE)
            ->count();

        return view('public.shops.products', compact(
            'shop', 'products', 'categories', 'reviewAvg', 'reviewCount', 'deliveredCount'
        ));
    }
}
