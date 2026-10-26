<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Support\Facades\Auth;

/** « Me prévenir du retour en stock » sur un produit épuisé (active / désactive). */
class StockAlertController extends Controller
{
    public function toggle(Product $product)
    {
        $alert = StockAlert::where('user_id', Auth::id())->where('product_id', $product->id)->first();

        if ($alert) {
            $alert->delete();
            $active = false;
        } else {
            StockAlert::create(['user_id' => Auth::id(), 'product_id' => $product->id]);
            $active = true;
        }

        return response()->json([
            'active'  => $active,
            'message' => $active
                ? 'Nous vous préviendrons dès que ce produit sera de retour.'
                : 'Alerte retirée.',
        ]);
    }
}
