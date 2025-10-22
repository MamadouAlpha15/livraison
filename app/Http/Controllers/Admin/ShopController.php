<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Liste toutes les boutiques (avec leur propriétaire).
     */
    public function index()
    {
        $shops = Shop::with('owner')->latest()->paginate(10);
        return view('admin.shops.index', compact('shops'));
    }

    /**
     * Approuver ou désapprouver une boutique.
     * Quand approuvée → le vendeur devient admin de SA boutique.
     */
    public function update(Request $request, Shop $shop)
    {
        $shop->is_approved = !$shop->is_approved;
        $shop->save();

        // ✅ Si approuvée, on donne le rôle admin boutique au propriétaire
        if ($shop->is_approved && $shop->owner) {
            $shop->owner->update([
                'shop_id' => $shop->id,
                'role_in_shop' => 'admin',
            ]);
        }

        return redirect()
            ->route('admin.shops.index')
            ->with('success', 'Statut de la boutique mis à jour avec succès.');
    }

    /**
     * Supprimer une boutique (et ses relations si cascade activée).
     */
    public function destroy(Shop $shop)
    {
        $shop->delete();
        return redirect()
            ->route('admin.shops.index')
            ->with('success', 'Boutique supprimée avec succès.');
    }
}
