<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Http\Request;

class VendeurController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereNotNull('shop_id')
            ->with('shop')
            ->latest();

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->filled('role')) {
            $query->where('role_in_shop', $request->role);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%");
            });
        }

        $vendeurs = $query->paginate(20)->withQueryString();

        // Base stats : filtrée par boutique si un shop_id est sélectionné
        $base = User::whereNotNull('shop_id');
        if ($request->filled('shop_id')) {
            $base->where('shop_id', $request->shop_id);
        }

        $stats = [
            'total'       => (clone $base)->count(),
            'admins'      => (clone $base)->where('role_in_shop', 'admin')->count(),
            'vendeurs'    => (clone $base)->where('role_in_shop', 'vendeur')->count(),
            'employes'    => (clone $base)->whereIn('role_in_shop', ['employe','caissier'])->count(),
            'livreurs'    => (clone $base)->where('role_in_shop', 'livreur')->count(),
            'disponibles' => (clone $base)->where('is_available', true)->count(),
        ];

        $filteredShop = $request->filled('shop_id')
            ? Shop::find($request->shop_id)
            : null;

        $shops = Shop::orderBy('name')->get(['id', 'name']);

        return view('admin.vendeurs.index', compact('vendeurs', 'stats', 'shops', 'filteredShop'));
    }
}
