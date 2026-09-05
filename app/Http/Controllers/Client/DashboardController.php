<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShopMessage;
use App\Models\Product;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        /* ── Catalogue produits (remplace l'ancienne grille de boutiques) ──
           Mêmes filtres pays/approbation que le reste du tableau de bord,
           avec recherche (s) et catégorie (cat) optionnelles. */
        $productsQuery = Product::where('is_active', true)
            ->whereHas('shop', function ($q) use ($user) {
                $q->where('is_approved', true);
                if ($user->country) $q->where('country', $user->country);
            })
            ->with(['shop:id,name,image,country,type,currency']);

        if ($s = request('s')) {
            $productsQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }
        if ($cat = request('cat')) {
            $productsQuery->where('category', $cat);
        }

        /* ── Prix RÉELLEMENT appliqué (vente flash comprise) — même règle que
           Product::getCurrentPriceAttribute() (accesseur PHP, pas une colonne, donc
           pas filtrable/triable directement, d'où l'expression SQL équivalente
           ci-dessous). Identique à WelcomeController (page d'accueil), pour un
           comportement cohérent partout sur le site. */
        $currentPriceSql = "CASE WHEN flash_price IS NOT NULL AND flash_ends_at IS NOT NULL AND flash_ends_at > NOW() AND (flash_starts_at IS NULL OR flash_starts_at <= NOW()) THEN flash_price ELSE price END";

        $minPrice = request('min_price');
        $maxPrice = request('max_price');
        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $productsQuery->whereRaw("{$currentPriceSql} >= ?", [(float) $minPrice]);
        }
        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $productsQuery->whereRaw("{$currentPriceSql} <= ?", [(float) $maxPrice]);
        }

        $sort = request('sort', 'newest');
        if (!in_array($sort, ['newest', 'price_asc', 'price_desc', 'popular'], true)) {
            $sort = 'newest';
        }
        switch ($sort) {
            case 'price_asc':
                $productsQuery->orderByRaw("{$currentPriceSql} ASC");
                break;
            case 'price_desc':
                $productsQuery->orderByRaw("{$currentPriceSql} DESC");
                break;
            case 'popular':
                $productsQuery->withCount(['orderItems as sold_count' => function ($q) {
                    $q->whereHas('order', fn ($o) => $o->where('status', 'livrée'));
                }])->orderByDesc('sold_count');
                break;
            default:
                $productsQuery->latest();
        }

        $products = $productsQuery->paginate(24)->withQueryString()->fragment('catalogue');

        /* ── Populaire par catégorie ("Populaire en Parfums", "Populaire en Montres"…) ──
           Pour chaque grande catégorie, on montre les produits les plus VENDUS
           (quantités des commandes livrées), pas juste les plus récents. Si une
           catégorie n'a pas encore de vente, on retombe sur ses produits les plus
           récents pour ne jamais afficher une section vide dès le lancement.
           Masqué pendant une recherche/filtre (comme le reste du catalogue). */
        $categoryGroups = collect();
        if (!request('s') && !request('cat')) {
            $topCategoryNames = Product::where('is_active', true)
                ->whereHas('shop', function ($q) use ($user) {
                    $q->where('is_approved', true);
                    if ($user->country) $q->where('country', $user->country);
                })
                ->whereNotNull('category')->where('category', '!=', '')
                ->select('category')
                ->groupBy('category')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(10)
                ->pluck('category');

            foreach ($topCategoryNames as $catName) {
                $soldInCat = OrderItem::query()
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->where('orders.status', Order::STATUS_LIVREE)
                    ->where('products.category', $catName)
                    ->where('products.is_active', true)
                    ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_sold')
                    ->groupBy('order_items.product_id')
                    ->orderByDesc('total_sold')
                    ->limit(6)
                    ->pluck('total_sold', 'order_items.product_id');

                if ($soldInCat->isNotEmpty()) {
                    $catProducts = Product::whereIn('id', $soldInCat->keys())
                        ->whereHas('shop', function ($q) use ($user) {
                            $q->where('is_approved', true);
                            if ($user->country) $q->where('country', $user->country);
                        })
                        ->with('shop:id,name,image,user_id,currency')
                        ->get()
                        ->sortByDesc(fn ($p) => $soldInCat[$p->id])
                        ->values();
                    $catProducts->each(fn ($p) => $p->total_sold = (int) $soldInCat[$p->id]);
                } else {
                    $catProducts = Product::where('is_active', true)
                        ->where('category', $catName)
                        ->whereHas('shop', function ($q) use ($user) {
                            $q->where('is_approved', true);
                            if ($user->country) $q->where('country', $user->country);
                        })
                        ->with('shop:id,name,image,user_id,currency')
                        ->latest()
                        ->limit(6)
                        ->get();
                }

                if ($catProducts->count() >= 2) {
                    $categoryGroups->push(['name' => $catName, 'products' => $catProducts]);
                }
            }
        }

        /* ── Stats globales ── */
        $shopQuery = Shop::where('is_approved', true);
        if ($user->country) $shopQuery->where('country', $user->country);
        $shopCount = $shopQuery->count();

        $productCount = Product::whereHas('shop', function($q) use ($user) {
            $q->where('is_approved', true);
            if ($user->country) $q->where('country', $user->country);
        })->where('is_active', true)->count();

        $deliveredCount = Order::where('status', Order::STATUS_LIVREE)->count();
        $clientCount    = User::where('role', 'client')->count();

        /* ── Catégories de produits avec comptage ──
           Alias `type`/`shop_count` conservés pour rester compatibles avec la vue
           (qui filtrait auparavant les boutiques) : elle affiche maintenant des
           catégories de PRODUITS mais réutilise les mêmes noms de colonnes. */
        $categories = Product::where('is_active', true)
            ->whereNotNull('category')->where('category', '!=', '')
            ->whereHas('shop', function ($q) use ($user) {
                $q->where('is_approved', true);
                if ($user->country) $q->where('country', $user->country);
            })
            ->groupBy('category')
            ->selectRaw('category as type, count(*) as shop_count')
            ->orderByDesc('shop_count')
            ->get();

        /* ── Top boutiques sidebar ── */
        $topQuery = Shop::where('is_approved', true);
        if ($user->country) $topQuery->where('country', $user->country);
        $allTopShops = $topQuery
            ->withCount(['orders as sales_count'])
            ->addSelect(DB::raw('
                (SELECT AVG(r.rating) FROM reviews r INNER JOIN orders o ON o.id = r.order_id WHERE o.shop_id = shops.id) as avg_rating,
                (SELECT COUNT(r.id)   FROM reviews r INNER JOIN orders o ON o.id = r.order_id WHERE o.shop_id = shops.id) as reviews_count
            '))
            ->orderByDesc(DB::raw('COALESCE(avg_rating, 0)'))
            ->orderByDesc('sales_count')
            ->get();
        $topShops = $allTopShops->take(4);

        /* ── Commandes récentes ── */
        $recentOrders = Order::where('user_id', $user->id)
            ->with('shop')->latest()->take(4)->get();

        /* ── Favoris ── */
        $favoriteIds = $user->favorites()->pluck('shops.id')->toArray();

        /* ── Recommandé pour vous ──
           Basé sur les catégories déjà achetées + les produits favoris.
           Si le client est nouveau (aucun historique), on retombe sur les
           produits vedettes / en vente flash pour ne jamais afficher un bloc vide. */
        $purchasedCategories = OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id))
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereNotNull('products.category')
            ->select('products.category', DB::raw('COUNT(*) as cnt'))
            ->groupBy('products.category')
            ->orderByDesc('cnt')
            ->limit(5)
            ->pluck('products.category')
            ->toArray();

        $favoriteProductIds = $user->favoriteProducts()->pluck('products.id')->toArray();
        $cartCount = (int) \App\Models\CartItem::where('user_id', $user->id)->sum('quantity');

        $recoQuery = Product::where('is_active', true)
            ->whereHas('shop', function ($q) use ($user) {
                $q->where('is_approved', true);
                if ($user->country) $q->where('country', $user->country);
            })
            ->with(['shop:id,name,currency,country', 'activeVariants:id,product_id,stock']);

        if (!empty($purchasedCategories) || !empty($favoriteProductIds)) {
            $recoQuery->where(function ($q) use ($purchasedCategories, $favoriteProductIds) {
                if (!empty($purchasedCategories)) $q->orWhereIn('category', $purchasedCategories);
                if (!empty($favoriteProductIds))  $q->orWhereIn('id', $favoriteProductIds);
            });
        } else {
            $recoQuery->where(function ($q) {
                $q->where('is_featured', true)
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('flash_price')->where('flash_ends_at', '>', now());
                  });
            });
        }

        $recommendedProducts = $recoQuery
            ->inRandomOrder()
            ->limit(30)
            ->get()
            ->reject(function ($p) {
                return $p->has_variants
                    ? $p->activeVariants->every(fn ($v) => $v->stock <= 0)
                    : $p->out_of_stock;
            })
            ->take(10)
            ->values();

        /* ── Ventes Flash actives (toutes boutiques confondues) ──
           Triées par urgence (celles qui se terminent le plus tôt en premier). */
        $flashProducts = Product::where('is_active', true)
            ->whereNotNull('flash_price')
            ->whereNotNull('flash_ends_at')
            ->where('flash_ends_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('flash_starts_at')->orWhere('flash_starts_at', '<=', now());
            })
            ->whereHas('shop', function ($q) use ($user) {
                $q->where('is_approved', true);
                if ($user->country) $q->where('country', $user->country);
            })
            ->with(['shop:id,name,currency,country', 'activeVariants:id,product_id,stock'])
            ->orderBy('flash_ends_at')
            ->limit(20)
            ->get()
            ->reject(function ($p) {
                return $p->has_variants
                    ? $p->activeVariants->every(fn ($v) => $v->stock <= 0)
                    : $p->out_of_stock;
            })
            ->take(10)
            ->values();

        /* ── Fidélité : progression vers le prochain palier (widget sidebar) ──
           Les points valent 1:1 en GNF de réduction (LoyaltyService::redeemPoints).
           On affiche une barre de progression vers un palier "rond" suivant,
           purement motivationnel (n'importe quel solde est déjà utilisable). */
        $loyaltyPoints = $user->loyalty_points ?? 0;
        $loyaltyLadder = [500, 1000, 2500, 5000, 10000, 25000, 50000, 100000, 250000, 500000, 1000000];
        $loyaltyNextMilestone = collect($loyaltyLadder)->first(fn ($m) => $m > $loyaltyPoints);
        if (!$loyaltyNextMilestone) {
            $loyaltyNextMilestone = (intdiv($loyaltyPoints, 500000) + 1) * 500000;
        }
        $loyaltyPrevMilestone = collect($loyaltyLadder)->filter(fn ($m) => $m <= $loyaltyPoints)->last() ?? 0;
        $loyaltyProgressPercent = $loyaltyNextMilestone > $loyaltyPrevMilestone
            ? (int) round((($loyaltyPoints - $loyaltyPrevMilestone) / ($loyaltyNextMilestone - $loyaltyPrevMilestone)) * 100)
            : 100;
        $loyaltyReferralBonus = LoyaltyService::REFERRAL_REFERRER_BONUS;

        /* ── Messages client ── */
        $clientId   = $user->id;
        $myMessages = ShopMessage::where(function($q) use ($clientId) {
                $q->where('sender_id', $clientId)->orWhere('receiver_id', $clientId);
            })
            ->with(['sender', 'receiver', 'product', 'product.shop'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function($m) use ($clientId) {
                return ($m->product?->shop_id ?? 0) . '-' . ($m->product_id ?? '0');
            });

        $myUnread = ShopMessage::where('receiver_id', $clientId)->whereNull('read_at')->count();

        return view('dashboards.client', compact(
            'products', 'categoryGroups', 'recentOrders', 'myMessages', 'myUnread',
            'shopCount', 'productCount', 'deliveredCount', 'clientCount',
            'categories', 'topShops', 'allTopShops', 'favoriteIds',
            'recommendedProducts', 'favoriteProductIds', 'flashProducts', 'cartCount',
            'loyaltyPoints', 'loyaltyNextMilestone', 'loyaltyProgressPercent', 'loyaltyReferralBonus',
            'sort', 'minPrice', 'maxPrice'
        ));
    }
}
