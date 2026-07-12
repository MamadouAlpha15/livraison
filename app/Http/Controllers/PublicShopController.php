<?php

namespace App\Http\Controllers;

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

        return view('public.shops.products', compact('shop', 'products', 'categories'));
    }
}
