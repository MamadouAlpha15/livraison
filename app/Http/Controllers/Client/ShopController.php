<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function show(Shop $shop, Request $request)
    {
        abort_unless($shop->is_approved, 404);

        /* Catégories distinctes des produits actifs de cette boutique */
        $categories = $shop->products()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        /* Produits actifs — filtrables par catégorie si passée en GET */
        $query = $shop->products()
            ->where('is_active', true)
            ->where('is_available', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        /* Vedettes en tête, puis les autres par ordre de création desc */
        $products = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate(16)
            ->withQueryString();

        $devise = $shop->currency ?? 'GNF';

        return view('client.shops.show', compact(
            'shop', 'products', 'categories', 'devise'
        ));
    }
}