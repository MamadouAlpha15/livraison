<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\DeliveryCompany;

class WelcomeController extends Controller
{
    public function index()
    {
        // Boutiques approuvées, avec nombre de produits
        $shops = Shop::where('is_approved', true)
            ->withCount('products')
            ->latest()
            ->paginate(12);

        // Entreprises de livraison approuvées
        $companies = DeliveryCompany::where('approved', true)
            ->where('active', true)
            ->latest()
            ->get();

        return view('welcome', compact('shops', 'companies'));
    }


   
}
