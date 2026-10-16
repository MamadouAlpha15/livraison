<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopVisit;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function show(Shop $shop, Request $request)
    {
        abort_unless($shop->is_approved, 404);

        ShopVisit::record($shop->id);

        /* Catégories distinctes des produits actifs de cette boutique */
        $categories = $shop->products()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        /* Produits actifs — filtrables par catégorie si passée en GET */
        $query = $shop->products()
            ->where('is_active', true)
            ->where('is_available', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        /* Vedettes en tête, puis les autres par ordre de création desc */
        $products = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->paginate(16)
            ->withQueryString();

        $devise = $shop->currency ?? 'GNF';

        // Note réelle du vendeur (avis clients) — remplace les étoiles et compteurs
        // aléatoires (rand) affichés avant sur chaque produit.
        $reviewStats = Review::where('vendeur_id', $shop->user_id)
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();
        $reviewCount = (int) ($reviewStats->cnt ?? 0);
        $reviewAvg   = $reviewCount > 0 ? round((float) $reviewStats->avg_rating, 1) : null;

        return view('client.shops.show', compact(
            'shop', 'products', 'categories', 'devise', 'reviewAvg', 'reviewCount'
        ));
    }
}