<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Ancienne page boutique des clients connectés : elle ignorait la vitrine réglée par le
     * vendeur (sections, produits à la une, couleur). Tout le monde voit désormais la même
     * vitrine (public.shops.products), qui gère aussi les clients connectés.
     * Les paramètres (utm_source, catégorie…) sont conservés pour le suivi des visites.
     */
    public function show(Shop $shop, Request $request): RedirectResponse
    {
        abort_unless($shop->is_approved, 404);

        return redirect()->route('public.shops.products', array_merge(['shop' => $shop], $request->query()));
    }
}
