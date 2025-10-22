<?php

namespace App\Http\Controllers;

use App\Models\Shop;

class WelcomeController extends Controller
{
    public function index()
    {
        // Boutiques approuvées, avec nombre de produits
        $shops = Shop::where('is_approved', true) // Seulement les boutiques approuvées
            ->withCount('products')
            ->latest()
            ->paginate(12);

        return view('welcome', compact('shops'));
    }
}
