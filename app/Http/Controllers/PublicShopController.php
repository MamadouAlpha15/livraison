<?php

namespace App\Http\Controllers;

use App\Models\Shop;

class PublicShopController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $search = $request->input('q');
        $type   = $request->input('type');

        $query = Shop::where('is_approved', true)->withCount('products');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
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

    public function products(Shop $shop) // Route Model Binding
    {
        abort_unless($shop->is_approved, 404); // Assure que la boutique est approuvée

        $products = $shop->products()->latest()->paginate(12);  // Pagination
        return view('public.shops.products', compact('shop','products'));
    }
}
