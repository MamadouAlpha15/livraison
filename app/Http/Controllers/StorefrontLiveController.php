<?php

namespace App\Http\Controllers;

use App\Models\LiveVisitor;
use App\Models\Product;
use App\Models\Shop;
use App\Support\GeoCountry;
use App\Support\TrafficSource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Signal « je suis sur la boutique » envoyé par la vitrine toutes les 20 s (vue en direct du vendeur).
 * Enregistre : identifiant aléatoire du navigateur, étape (parcourt, regarde un produit, panier, formulaire),
 * produit regardé, nombre d'articles du panier, pays. Jamais d'adresse IP.
 * Ignoré pour la boutique elle-même (vendeur, équipe) et pour les robots.
 */
class StorefrontLiveController extends Controller
{
    public function ping(Shop $shop, Request $request): Response
    {
        abort_unless($shop->is_approved, 404);
        $u = Auth::user();
        if ($u && ((int) $u->id === (int) $shop->user_id || (int) ($u->shop_id ?? 0) === (int) $shop->id)) return response()->noContent();
        if (TrafficSource::isBot($request)) return response()->noContent();

        $data = $request->validate([
            'visitor'    => ['required', 'string', 'regex:/^[a-z0-9]{12,40}$/'],
            'stage'      => ['required', 'in:browse,product,cart,form'],
            'product_id' => ['nullable', 'integer'],
            'cart_items' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        // Produit de cette boutique seulement
        $productId = !empty($data['product_id']) && Product::where('shop_id', $shop->id)->whereKey($data['product_id'])->exists() ? (int) $data['product_id'] : null;
        $stage = $data['stage'] === 'product' && !$productId ? 'browse' : $data['stage'];

        $row = LiveVisitor::firstOrNew(['shop_id' => $shop->id, 'visitor' => $data['visitor']]);
        // Pays lu une seule fois par visiteur et par jour (l'IP n'est jamais gardée)
        if (!$row->exists || !$row->last_seen_at || $row->last_seen_at->lt(now()->subDay())) {
            $row->country = GeoCountry::of($request);
            $row->first_seen_at = now();
        } elseif ($row->last_seen_at->lt(now()->subMinutes(30))) {
            $row->first_seen_at = now(); // retour après une longue absence : nouvelle visite
        }
        $row->fill(['stage' => $stage, 'product_id' => $productId, 'cart_items' => (int) ($data['cart_items'] ?? 0), 'last_seen_at' => now()]);
        try {
            $row->save();
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Deux onglets du même navigateur au même instant : l'autre signal a déjà créé la ligne
        }

        return response()->noContent();
    }
}
