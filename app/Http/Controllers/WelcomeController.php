<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

/**
 * ═══════════════════════════════════════════════════════════════
 * WelcomeController — Page d'accueil publique
 * ═══════════════════════════════════════════════════════════════
 * Injecte dans la vue welcome (style "Jumia" : produits + catégories
 * affichés dès l'arrivée du visiteur) :
 *   $flashProducts        → Collection<Product>  (ventes flash actives)
 *   $recommendedProducts  → Collection<Product>  (produits vedette)
 *   $shops                → Collection<Shop>     (boutiques à la une)
 *   $products             → LengthAwarePaginator<Product> (catalogue complet)
 *   $categories           → Collection<string>
 *   $categoryGroups       → Collection<{name, products}> (aperçu par catégorie, hors recherche)
 *   $bestSellers          → Collection<Product>  (meilleures ventes réelles, hors recherche/filtre)
 *   $testimonials         → Collection<Review> (avis clients réels, note ≥ 4, hors recherche)
 *   $favoritedIds         → array<int> (ids produits favoris du client connecté)
 *   $shopRatings          → array<int, {avg, count}> (note moyenne des boutiques, clé = user_id du vendeur)
 *   $stats                → array { total_shops, total_products, total_orders }
 */
class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->catalogueData($request);

        /* Requête AJAX (recherche en direct, sans rechargement de page) :
           on ne renvoie que le fragment "résultats" au lieu de la page entière. */
        if ($request->ajax()) {
            return view('partials.catalogue-results', $data);
        }

        return view('welcome', $data);
    }

    /**
     * Page d'accueil alternative (même contenu que index(), autre gabarit).
     * Accessible aux invités comme aux clients.
     */
    public function catalogue(Request $request)
    {
        return view('welcome2', $this->catalogueData($request));
    }

    /**
     * GET /search-suggestions?q=... — autocomplétion de la barre de recherche
     * (accueil). Renvoie quelques produits correspondants, avant même que le
     * visiteur ait fini de taper ou appuyé sur "Rechercher".
     */
    public function suggestions(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $user = auth()->user();
        $approvedShopFilter = function ($qq) use ($user) {
            $qq->where('is_approved', true);
            if ($user && $user->country) {
                $qq->where('country', $user->country);
            }
        };

        $products = Product::where('is_active', true)
            ->whereHas('shop', $approvedShopFilter)
            ->where('name', 'like', "%{$q}%")
            ->with('shop:id,name')
            ->latest()
            ->limit(6)
            ->get();

        return response()->json([
            'suggestions' => $products->map(fn ($p) => [
                'name'  => $p->name,
                'price' => number_format($p->current_price, 0, ',', ' ') . ' GNF',
                'image' => $p->image ? asset('storage/' . $p->image) : null,
                'shop'  => $p->shop->name ?? null,
                'url'   => route('client.orders.createFromProduct', $p),
            ]),
        ]);
    }

    /**
     * Construit le jeu de données "catalogue" partagé par les deux gabarits
     * d'accueil : ventes flash, produits recommandés, boutiques à la une,
     * catalogue complet filtrable (recherche / catégorie) et catégories.
     */
    private function catalogueData(Request $request): array
    {
        $user = auth()->user();
        $isSearching = (bool) $request->get('s');
        $isFiltering = (bool) $request->get('cat');

        $approvedShopFilter = function ($q) use ($user) {
            $q->where('is_approved', true);
            if ($user && $user->country) {
                $q->where('country', $user->country);
            }
        };

        /* ── Produits en vente flash (bandeau) ── */
        $flashProducts = Product::where('is_active', true)
            ->whereHas('shop', $approvedShopFilter)
            ->whereNotNull('flash_price')
            ->whereNotNull('flash_ends_at')
            ->where('flash_ends_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('flash_starts_at')->orWhere('flash_starts_at', '<=', now());
            })
            ->with('shop:id,name,image,user_id')
            ->latest()
            ->limit(10)
            ->get();

        /* ── Produits recommandés (mis en vedette par les boutiques) ── */
        $recommendedProducts = Product::where('is_active', true)
            ->whereHas('shop', $approvedShopFilter)
            ->where('is_featured', true)
            ->with('shop:id,name,image,user_id')
            ->latest()
            ->limit(10)
            ->get();

        /* ── Boutiques à la une ── */
        $shops = Shop::where('is_approved', true)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit(8)
            ->get();

        /* ── Catalogue complet (recherche / catégorie / pagination) ── */
        $query = Product::where('is_active', true)
            ->whereHas('shop', $approvedShopFilter)
            ->with(['shop:id,name,image,country,type,user_id']);

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

        /* ── Prix RÉELLEMENT appliqué (vente flash comprise) — même règle que
           Product::getCurrentPriceAttribute() (accesseur PHP, pas une colonne, donc
           pas filtrable/triable directement, d'où l'expression SQL équivalente
           ci-dessous). Réutilisée à la fois pour le filtre min/max et pour le tri
           par prix juste après : sinon un produit en vente flash à 5 000 GNF
           s'afficherait comme "cher" ou hors filtre à cause de son prix barré
           (10 000 GNF) au lieu du prix réel. */
        $currentPriceSql = "CASE WHEN flash_price IS NOT NULL AND flash_ends_at IS NOT NULL AND flash_ends_at > NOW() AND (flash_starts_at IS NULL OR flash_starts_at <= NOW()) THEN flash_price ELSE price END";

        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $query->whereRaw("{$currentPriceSql} >= ?", [(float) $minPrice]);
        }
        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $query->whereRaw("{$currentPriceSql} <= ?", [(float) $maxPrice]);
        }

        /* ── Tri ── */
        $sort = $request->get('sort', 'newest');
        if (!in_array($sort, ['newest', 'price_asc', 'price_desc', 'popular'], true)) {
            $sort = 'newest';
        }
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw("{$currentPriceSql} ASC");
                break;
            case 'price_desc':
                $query->orderByRaw("{$currentPriceSql} DESC");
                break;
            case 'popular':
                $query->withCount(['orderItems as sold_count' => function ($q) {
                    $q->whereHas('order', fn ($o) => $o->where('status', 'livrée'));
                }])->orderByDesc('sold_count');
                break;
            default:
                $query->latest();
        }

        /* ->fragment('catalogue') : chaque lien de pagination pointe vers #catalogue,
           pour que la page suivante/précédente atterrisse directement sur les produits
           au lieu de tout en haut (sinon il faut redescendre à chaque clic). */
        $products = $query->paginate(24)->withQueryString()->fragment('catalogue');

        $categories = Product::select('category')
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->whereHas('shop', $approvedShopFilter)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        /* ── Aperçus par catégorie ("Populaire en ...") — uniquement sur l'accueil
           par défaut, pas pendant une recherche/filtre (économise des requêtes).
           Toutes les catégories ayant au moins 2 produits sont affichées (pas
           seulement les 4 plus fournies) : sur un jeune catalogue avec peu de
           catégories, se limiter au "top 4" pouvait masquer la fonctionnalité
           entière. La limite haute (12) protège juste contre un catalogue
           très diversifié plus tard. ── */
        $categoryGroups = collect();
        if (!$isSearching && !$isFiltering) {
            $topCategories = Product::where('is_active', true)
                ->whereHas('shop', $approvedShopFilter)
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->select('category')
                ->groupBy('category')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(12)
                ->pluck('category');

            foreach ($topCategories as $catName) {
                $catProducts = Product::where('is_active', true)
                    ->whereHas('shop', $approvedShopFilter)
                    ->where('category', $catName)
                    ->with('shop:id,name,image,user_id')
                    ->latest()
                    ->limit(6)
                    ->get();

                if ($catProducts->count() >= 2) {
                    $categoryGroups->push(['name' => $catName, 'products' => $catProducts]);
                }
            }
        }

        /* ── Meilleures ventes réelles (quantités des commandes LIVRÉES, pas des vœux) ──
           uniquement sur l'accueil par défaut, comme les aperçus par catégorie. ── */
        $bestSellers = collect();
        if (!$isSearching && !$isFiltering) {
            $soldByProduct = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.status', Order::STATUS_LIVREE)
                ->where('products.is_active', true)
                ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_sold')
                ->groupBy('order_items.product_id')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->pluck('total_sold', 'order_items.product_id');

            if ($soldByProduct->isNotEmpty()) {
                $bestSellers = Product::whereIn('id', $soldByProduct->keys())
                    ->whereHas('shop', $approvedShopFilter)
                    ->with('shop:id,name,image,user_id')
                    ->get()
                    ->sortByDesc(fn ($p) => $soldByProduct[$p->id])
                    ->values();

                $bestSellers->each(function ($p) use ($soldByProduct) {
                    $p->total_sold = (int) $soldByProduct[$p->id];
                });
            }
        }

        /* ── Avis clients réels (note ≥ 4, avec commentaire) pour la section témoignages ── */
        $testimonials = collect();
        if (!$isSearching && !$isFiltering) {
            $testimonials = Review::whereNotNull('comment')
                ->where('comment', '!=', '')
                ->where('rating', '>=', 4)
                ->with(['client:id,name', 'vendeur:id,name'])
                ->latest()
                ->limit(6)
                ->get()
                ->filter(fn ($r) => $r->client) // évite les avis dont le client aurait été supprimé
                ->values();
        }

        /* ── Note moyenne des boutiques (calculée depuis les avis réels de leurs commandes) ── */
        $vendeurIds = collect([$flashProducts, $recommendedProducts, $bestSellers, collect($products->items())])
            ->merge($categoryGroups->pluck('products'))
            ->flatten(1)
            ->pluck('shop.user_id')
            ->filter()
            ->unique()
            ->values();

        $shopRatings = $vendeurIds->isEmpty() ? [] : Review::whereIn('vendeur_id', $vendeurIds)
            ->selectRaw('vendeur_id, AVG(rating) as avg_rating, COUNT(*) as cnt')
            ->groupBy('vendeur_id')
            ->get()
            ->keyBy('vendeur_id')
            ->map(fn ($r) => ['avg' => round((float) $r->avg_rating, 1), 'count' => (int) $r->cnt])
            ->all();

        /* ── Produits favoris du client connecté (pour l'état du cœur ♥) ── */
        $favoritedIds = ($user && $user->role === 'client')
            ? $user->favoriteProducts()->pluck('products.id')->all()
            : [];

        /* ── Chiffres clés (compteurs) ── */
        $stats = [
            'total_shops'    => Shop::where('is_approved', true)->count(),
            'total_products' => Product::where('is_active', true)->whereHas('shop', $approvedShopFilter)->count(),
            'total_orders'   => Order::count(),
        ];

        return compact(
            'flashProducts', 'recommendedProducts', 'shops', 'products', 'categories',
            'categoryGroups', 'bestSellers', 'testimonials', 'favoritedIds', 'shopRatings', 'stats', 'sort',
            'minPrice', 'maxPrice'
        );
    }
}
