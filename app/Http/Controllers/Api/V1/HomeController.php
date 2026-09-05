<?php

namespace App\Http\Controllers\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// HomeController (API v1) — données du tableau de bord client mobile : ventes
// flash, "populaire par catégorie", recommandations personnalisées. Reprend
// la même logique que Client\DashboardController côté web (même sections,
// mêmes règles de filtrage pays/boutique approuvée), pour que l'app Flutter
// affiche la même chose que la page d'accueil du site.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user('sanctum');
        $country = $user?->country;

        $approvedShop = function ($q) use ($country) {
            $q->where('is_approved', true);
            if ($country) $q->where('country', $country);
        };

        // ── Ventes Flash actives, toutes boutiques confondues ──
        $flashProducts = Product::where('is_active', true)
            ->whereNotNull('flash_price')->whereNotNull('flash_ends_at')
            ->where('flash_ends_at', '>', now())
            ->where(fn ($q) => $q->whereNull('flash_starts_at')->orWhere('flash_starts_at', '<=', now()))
            ->whereHas('shop', $approvedShop)
            ->with(['shop:id,name,image,country,currency', 'activeVariants:id,product_id,stock'])
            ->orderBy('flash_ends_at')
            ->limit(20)
            ->get()
            ->reject(fn ($p) => $p->has_variants ? $p->activeVariants->every(fn ($v) => $v->stock <= 0) : $p->out_of_stock)
            ->take(10)
            ->values();

        // ── Populaire par catégorie (meilleures ventes réelles) ──
        $topCategoryNames = Product::where('is_active', true)
            ->whereHas('shop', $approvedShop)
            ->whereNotNull('category')->where('category', '!=', '')
            ->select('category')->groupBy('category')
            ->orderByRaw('COUNT(*) DESC')->limit(8)
            ->pluck('category');

        $categoryGroups = collect();
        foreach ($topCategoryNames as $catName) {
            $soldInCat = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.status', Order::STATUS_LIVREE)
                ->where('products.category', $catName)
                ->where('products.is_active', true)
                ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_sold')
                ->groupBy('order_items.product_id')
                ->orderByDesc('total_sold')->limit(6)
                ->pluck('total_sold', 'order_items.product_id');

            if ($soldInCat->isNotEmpty()) {
                $catProducts = Product::whereIn('id', $soldInCat->keys())
                    ->whereHas('shop', $approvedShop)
                    ->with('shop:id,name,image,currency')
                    ->get()->sortByDesc(fn ($p) => $soldInCat[$p->id])->values();
            } else {
                $catProducts = Product::where('is_active', true)->where('category', $catName)
                    ->whereHas('shop', $approvedShop)
                    ->with('shop:id,name,image,currency')
                    ->latest()->limit(6)->get();
            }

            if ($catProducts->count() >= 2) {
                $categoryGroups->push(['name' => $catName, 'products' => $catProducts]);
            }
        }

        // ── Recommandé pour vous (historique d'achat + favoris, sinon vedettes/flash) ──
        $recommendedProducts = collect();
        if ($user) {
            $purchasedCategories = OrderItem::whereHas('order', fn ($q) => $q->where('user_id', $user->id))
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->whereNotNull('products.category')
                ->select('products.category')->distinct()->limit(5)
                ->pluck('products.category')->toArray();

            $favoriteProductIds = $user->favoriteProducts()->pluck('products.id')->toArray();

            $recoQuery = Product::where('is_active', true)->whereHas('shop', $approvedShop)
                ->with(['shop:id,name,currency,country', 'activeVariants:id,product_id,stock']);

            if (!empty($purchasedCategories) || !empty($favoriteProductIds)) {
                $recoQuery->where(function ($q) use ($purchasedCategories, $favoriteProductIds) {
                    if (!empty($purchasedCategories)) $q->orWhereIn('category', $purchasedCategories);
                    if (!empty($favoriteProductIds)) $q->orWhereIn('id', $favoriteProductIds);
                });
            } else {
                $recoQuery->where(fn ($q) => $q->where('is_featured', true)
                    ->orWhere(fn ($q2) => $q2->whereNotNull('flash_price')->where('flash_ends_at', '>', now())));
            }

            $recommendedProducts = $recoQuery->inRandomOrder()->limit(30)->get()
                ->reject(fn ($p) => $p->has_variants ? $p->activeVariants->every(fn ($v) => $v->stock <= 0) : $p->out_of_stock)
                ->take(10)->values();
        }

        // ── Statistiques de confiance (bandeau du haut, comme sur le site) ──
        $shopQuery = Shop::where('is_approved', true);
        if ($country) $shopQuery->where('country', $country);

        return response()->json([
            'data' => [
                'flash_products' => ProductResource::collection($flashProducts),
                'recommended_products' => ProductResource::collection($recommendedProducts),
                'category_groups' => $categoryGroups->map(fn ($g) => [
                    'name' => $g['name'],
                    'products' => ProductResource::collection($g['products']),
                ])->values(),
                'stats' => [
                    'shop_count'      => $shopQuery->count(),
                    'product_count'   => Product::where('is_active', true)->whereHas('shop', $approvedShop)->count(),
                    'delivered_count' => Order::where('status', Order::STATUS_LIVREE)->count(),
                    'client_count'    => User::where('role', 'client')->count(),
                ],
            ],
        ]);
    }
}
