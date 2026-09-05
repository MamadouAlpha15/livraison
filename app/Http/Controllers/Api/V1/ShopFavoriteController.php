<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Boutiques suivies ("favorites" côté site — distinct du wishlist produit,
 * voir Api\V1\FavoriteController). Reprend Client\FavoriteController.
 */
class ShopFavoriteController extends Controller
{
    /** GET /api/v1/shop-favorites */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $favorites = $user->favorites()
            ->where('is_approved', true)
            ->withCount(['products as products_count' => fn ($q) => $q->where('is_active', true)])
            ->withCount(['orders as sales_count'])
            ->get();

        return response()->json([
            'data' => $favorites->map(fn ($s) => [
                'id'             => $s->id,
                'name'           => $s->name,
                'type'           => $s->type,
                'image_url'      => $s->image ? ImageOptimizer::url($s->image, 'medium') : null,
                'country'        => $s->country,
                'products_count' => $s->products_count,
                'sales_count'    => $s->sales_count,
            ]),
        ]);
    }

    /** POST /api/v1/shops/{shop}/favorite — bascule suivi/pas suivi */
    public function toggle(Request $request, Shop $shop): JsonResponse
    {
        $user   = $request->user();
        $result = $user->favorites()->toggle($shop->id);

        return response()->json([
            'favorited' => count($result['attached']) > 0,
            'count'     => $user->favorites()->count(),
        ]);
    }
}
