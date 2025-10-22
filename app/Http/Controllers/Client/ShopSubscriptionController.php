<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

class ShopSubscriptionController extends Controller
{
    public function store(Shop $shop)
    {
        // Abonnement client -> boutique
        Auth::user()->subscribedShops()->syncWithoutDetaching([$shop->id]); 
        return back()->with('success', 'Inscription à la boutique réussie ✅');
    }
}
