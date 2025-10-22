<?php

// app/Http/Controllers/Vendeur/DashboardController.php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Product; 

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $shop = $user->shop ?: $user->assignedShop; // ✅ prend aussi la boutique rattachée

        if (!$shop) {
            // pas de boutique pour ce compte
            return view('vendeur.no_shop');
        }
// ✅ récupérer les produits de cette boutique
        $products = Product::where('shop_id', $shop->id)->latest()->paginate(10);

        return view('dashboards.vendeur', compact('shop', 'products'));
    }
}
