<?php

namespace App\Http\Controllers\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// ProductController (API v1) — catalogue produits pour l'app Flutter.
// Reprend exactement les mêmes filtres (pays, boutique approuvée, recherche,
// catégorie) que Client\DashboardController / Client\ProductController côté
// web, pour que le catalogue affiché soit identique entre le site et l'app.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductDetailResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/v1/products
     * Query params optionnels : s (recherche), cat (catégorie), shop_id, page.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum'); // peut être null (catalogue accessible sans compte)

        $query = Product::where('is_active', true)
            ->whereHas('shop', function ($q) use ($user) {
                $q->where('is_approved', true);
                if ($user?->country) $q->where('country', $user->country);
            })
            ->with(['shop:id,name,image,country,currency']);

        if ($s = $request->get('s')) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        if ($cat = $request->get('cat')) {
            $query->where('category', $cat);
        }

        if ($shopId = $request->get('shop_id')) {
            $query->where('shop_id', $shopId);
        }

        $products = $query->latest()->paginate(24)->withQueryString();

        // Campagnes payées : produits sponsorisés dans la recherche et les catégories (pas dans le catalogue d'une boutique)
        if (!$request->get('shop_id')) {
            \App\Support\Campaigns\SponsoredPlacements::blend($products, $request->get('s'), $request->get('cat'), $user?->country);
        }

        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/products/{product}
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        if ($product->shop) \App\Support\TrafficSource::recordAppVisit($product->shop, $request);

        $product->load(['shop', 'activeVariants']);
        // Le nom de la relation chargée par activeVariants() diffère de "variants" —
        // on la ré-affecte pour que ProductDetailResource (whenLoaded('variants'))
        // la trouve sans dupliquer sa logique de tri/filtre.
        $product->setRelation('variants', $product->activeVariants);

        $user = $request->user('sanctum');
        if ($user) {
            $product->is_favorited = $user->favoriteProducts()->where('product_id', $product->id)->exists();
        }

        // Ouvert depuis un emplacement sponsorisé de l'app (?c=<campagne>) : clic compté, commande attribuée 7 jours
        if (ctype_digit((string) $request->query('c'))) {
            \App\Support\Campaigns\AdAttribution::registerClick((int) $request->query('c'), $request, $user);
        }

        return response()->json([
            'data' => new ProductDetailResource($product),
        ]);
    }

    /**
     * GET /api/v1/categories
     * Liste des catégories disponibles (pour les filtres côté app).
     */
    public function categories(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $categories = Product::select('category')
            ->where('is_active', true)
            ->whereNotNull('category')->where('category', '!=', '')
            ->whereHas('shop', function ($q) use ($user) {
                $q->where('is_approved', true);
                if ($user?->country) $q->where('country', $user->country);
            })
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return response()->json(['data' => $categories]);
    }
}
