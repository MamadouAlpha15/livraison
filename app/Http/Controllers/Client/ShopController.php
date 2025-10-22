<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function show(Shop $shop)
    {
        // On charge les produits de la boutique
        $products = $shop->products()->latest()->paginate(12);

        return view('client.shops.show', compact('shop', 'products'));
    }
}
