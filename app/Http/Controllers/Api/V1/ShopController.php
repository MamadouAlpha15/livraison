<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ShopResource;
use App\Models\Order;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopVisit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * GET /api/v1/shops
     * Query params optionnels : s (recherche), page.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $query = Shop::where('is_approved', true);
        if ($user?->country) $query->where('country', $user->country);

        if ($s = $request->get('s')) {
            $query->where('name', 'like', "%{$s}%");
        }

        $shops = $query->latest()->paginate(20)->withQueryString();

        return response()->json([
            'data' => ShopResource::collection($shops->items()),
            'meta' => [
                'current_page' => $shops->currentPage(),
                'last_page'    => $shops->lastPage(),
                'total'        => $shops->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/shops/{shop}
     * Reprend PublicShopController::products() : fiche boutique avec note
     * moyenne, nombre d'avis et de livraisons réussies (signaux de confiance).
     */
    public function show(Request $request, Shop $shop): JsonResponse
    {
        abort_unless($shop->is_approved, 404);

        ShopVisit::record($shop->id);

        $reviewStats = Review::where('vendeur_id', $shop->user_id)
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')->first();
        $reviewCount = (int) ($reviewStats->cnt ?? 0);
        $reviewAvg   = $reviewCount > 0 ? round((float) $reviewStats->avg_rating, 1) : null;

        $deliveredCount = Order::where('shop_id', $shop->id)->where('status', Order::STATUS_LIVREE)->count();

        return response()->json([
            'data' => [
                ...(new ShopResource($shop))->resolve($request),
                'review_avg'      => $reviewAvg,
                'review_count'    => $reviewCount,
                'delivered_count' => $deliveredCount,
            ],
        ]);
    }

    /** GET /api/v1/shops/{shop}/products — catalogue de cette boutique */
    public function products(Request $request, Shop $shop): JsonResponse
    {
        abort_unless($shop->is_approved, 404);

        $query = $shop->products()->where('is_active', true)->with('shop:id,name,image,currency');

        if ($cat = $request->get('cat')) {
            $query->where('category', $cat);
        }

        $products = $query->latest()->paginate(24)->withQueryString();

        return response()->json([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /** GET /api/v1/shops/{shop}/reviews — avis clients (les plus récents en premier) */
    public function reviews(Request $request, Shop $shop): JsonResponse
    {
        abort_unless($shop->is_approved, 404);

        $reviews = Review::where('vendeur_id', $shop->user_id)
            ->with('client:id,name')
            ->whereNotNull('comment')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => collect($reviews->items())->map(fn ($r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'comment'    => $r->comment,
                'client_name'=> $r->client?->name ?? 'Client',
                'created_at' => $r->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'total'        => $reviews->total(),
            ],
        ]);
    }
}
