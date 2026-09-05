<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /** GET /api/v1/favorites */
    public function index(Request $request): JsonResponse
    {
        $products = $request->user()->favoriteProducts()
            ->with('shop:id,name,image,country,currency')
            ->orderByDesc('product_favorites.created_at')
            ->paginate(24);

        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /** POST /api/v1/products/{product}/favorite — bascule favori/pas favori */
    public function toggle(Request $request, Product $product): JsonResponse
    {
        $user   = $request->user();
        $result = $user->favoriteProducts()->toggle($product->id);

        return response()->json([
            'favorited' => count($result['attached']) > 0,
            'count'     => $user->favoriteProducts()->count(),
        ]);
    }
}
