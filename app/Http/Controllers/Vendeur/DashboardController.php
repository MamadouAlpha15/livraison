<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User; // ✅ important

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shop = $user->shop ?: $user->assignedShop;

        if (!$shop) {
            return view('vendeur.no_shop');
        }

        // ✅ Récupération des produits de la boutique
        $products = Product::where('shop_id', $shop->id)
            ->latest()
            ->paginate(10);

        // ✅ Récupération des livreurs EN LIGNE de cette boutique
        $livreursDisponibles = User::where(function ($q) use ($shop) {
                $q->where('role', 'livreur')
                  ->orWhere('role_in_shop', 'livreur');
            })
            ->where('shop_id', $shop->id)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        return view('dashboards.vendeur', compact('shop', 'products', 'livreursDisponibles'));
    }
}
