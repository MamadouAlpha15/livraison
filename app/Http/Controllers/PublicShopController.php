<?php

namespace App\Http\Controllers;

use App\Models\Shop;

class PublicShopController extends Controller
{
    public function products(Shop $shop) // Route Model Binding
    {
        abort_unless($shop->is_approved, 404); // Assure que la boutique est approuvée

        $products = $shop->products()->latest()->paginate(12);  // Pagination
        return view('public.shops.products', compact('shop','products'));
    }
}
