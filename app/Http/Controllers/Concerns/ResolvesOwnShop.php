<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Shop;
use Illuminate\Support\Facades\Auth;

/** Boutique du vendeur connecté, pour les écrans de gestion de la vitrine. */
trait ResolvesOwnShop
{
    /** users.shop_id, sinon la boutique dont le compte est propriétaire ; 404 s'il n'en a aucune. */
    protected function shop(): Shop
    {
        $user = Auth::user();
        $shop = $user->shop ?? Shop::where('user_id', $user->id)->first();
        abort_unless($shop, 404, 'Aucune boutique associée à ce compte.');
        return $shop;
    }
}
