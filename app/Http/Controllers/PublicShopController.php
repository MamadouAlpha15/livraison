<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Models\Shop;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicShopController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $search = $request->input('q');
        $type   = $request->input('type');

        $query = Shop::where('is_approved', true)
            ->withCount('products')
            ->with(['products' => function ($q) {
                $q->select('id', 'shop_id', 'name', 'category');
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',    'like', "%{$search}%")
                  ->orWhere('type',  'like', "%{$search}%")
                  ->orWhereHas('products', function ($pq) use ($search) {
                      $pq->where('name',     'like', "%{$search}%")
                         ->orWhere('category','like', "%{$search}%");
                  });
            });
        }
        if ($type) {
            $query->where('type', $type);
        }

        $shops       = $query->latest()->paginate(12)->withQueryString();
        $totalShops  = Shop::where('is_approved', true)->count();
        $types       = Shop::where('is_approved', true)->whereNotNull('type')->distinct()->pluck('type');

        // Notes réelles (avis clients) des boutiques de la page, en une seule requête —
        // remplace les 5 étoiles décoratives affichées avant sur toutes les cartes.
        $shopRatings = Review::whereIn('vendeur_id', $shops->pluck('user_id')->filter()->unique())
            ->selectRaw('vendeur_id, COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->groupBy('vendeur_id')
            ->get()
            ->keyBy('vendeur_id');

        $q = $search;
        return view('shops.index', compact('shops', 'totalShops', 'types', 'type', 'q', 'shopRatings'));
    }

    /** Adresse courte /s/{slug} : même vitrine que /shops/{id}/products. */
    public function bySlug(string $slug, Request $request)
    {
        $shop = Shop::where('slug', $slug)->firstOrFail();
        return $this->products($shop, $request);
    }

    /** Une catégorie de la boutique : /shops/{id}/products/c/{categorie}. */
    public function category(Shop $shop, string $categorie, Request $request)
    {
        return $this->products($shop, $request, $categorie);
    }

    /** Une catégorie via l'adresse courte : /s/{slug}/{categorie}. */
    public function bySlugCategory(string $slug, string $categorie, Request $request)
    {
        $shop = Shop::where('slug', $slug)->firstOrFail();
        return $this->products($shop, $request, $categorie);
    }

    public function products(Shop $shop, Request $request, ?string $categorySlug = null)
    {
        abort_unless($shop->is_approved, 404);

        // Lien d'une catégorie : on retrouve son nom parmi celles des produits en ligne.
        // Catégorie inconnue (lien ancien, produits retirés) → la boutique entière, sans erreur.
        $activeCategory = null;
        if ($categorySlug !== null) {
            $activeCategory = collect(Storefront::categoryLinks($shop))->firstWhere('slug', $categorySlug);
            if (!$activeCategory) return redirect()->to(Storefront::publicUrl($shop));
        }

        // Réglages de la vitrine. En aperçu (éditeur de boutique, ?apercu=1), le brouillon
        // gardé en session est affiché au seul propriétaire, jamais aux visiteurs.
        $sf = Storefront::forShop($shop);
        $sfAccent = Storefront::isHex($shop->accent_color) ? strtoupper($shop->accent_color) : '#1F7CF6';
        $isOwner = Auth::check() && ((int) Auth::id() === (int) $shop->user_id || (int) Auth::user()->shop_id === (int) $shop->id);
        $isPreview = $isOwner && $request->boolean('apercu');
        if ($isPreview && ($draft = $request->session()->get('storefront_draft.' . $shop->id))) {
            $sf = Storefront::normalize($draft['config'] ?? null);
            if (Storefront::isHex($draft['accent'] ?? null)) $sfAccent = $draft['accent'];
        }

        // Visite (une par visiteur et par jour, hors vendeur et robots) et sa provenance
        // (Facebook, Instagram, WhatsApp…) : tableau de bord + page Marketing
        TrafficSource::recordLanding($shop, $request, $isPreview);

        $query = $shop->products()->where('is_active', true)
            ->when($activeCategory, fn ($q) => $q->where('category', $activeCategory['name']));
        match ($sf['sort']) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'recent'     => $query->latest(),
            default      => $query->orderByDesc('is_featured')->latest(),
        };
        $products = $query->paginate(20)->withQueryString();

        // Produit(s) à la une : choisis dans l'éditeur de vitrine, sinon le dernier « mis en avant »
        $featuredProducts = Storefront::featuredProducts($shop, $sf);

        // Derniers avis écrits par de vrais clients (aucun avis inventé)
        $latestReviews = Review::where('vendeur_id', $shop->user_id)
            ->whereNotNull('comment')->where('comment', '!=', '')
            ->with('client:id,name')
            ->latest()->take(3)->get();

        $categories = $shop->products()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();

        // Avis réels du vendeur (remplace les faux "(rand(10,200))" affichés avant sur chaque carte produit).
        $reviewStats  = Review::where('vendeur_id', $shop->user_id)
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();
        $reviewCount  = (int) ($reviewStats->cnt ?? 0);
        $reviewAvg    = $reviewCount > 0 ? round((float) $reviewStats->avg_rating, 1) : null;

        // Commandes livrées : signal de confiance ("X commandes livrées") pour rassurer un nouveau client.
        $deliveredCount = Order::where('shop_id', $shop->id)
            ->where('status', Order::STATUS_LIVREE)
            ->count();

        // Blocs « Sélection de produits » : une catégorie (ou toute la boutique), 4 ou 8 produits
        $selections = [];
        foreach ($sf['blocks'] as $b) {
            if ($b['type'] !== 'selection' || !$b['on']) continue;
            $selections[$b['id']] = $shop->products()->where('is_active', true)
                ->when($b['d']['category'] !== '', fn ($q) => $q->where('category', $b['d']['category']))
                ->orderByDesc('is_featured')->latest()->take($b['d']['limit'])->get();
        }

        // Message de bienvenue : seulement avec un vrai code promo encore utilisable
        $welcomePromo = null;
        $set = $sf['settings'];
        if ($set['welcome'] && $set['welcome_promo_id'] && !$set['vacation']) {
            $welcomePromo = \App\Models\PromoCode::where('shop_id', $shop->id)->whereKey($set['welcome_promo_id'])->active()
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->where(fn ($q) => $q->whereNull('max_uses')->orWhereColumn('uses_count', '<', 'max_uses'))
                ->first();
        }

        return view('public.shops.products', compact(
            'shop', 'products', 'categories', 'reviewAvg', 'reviewCount', 'deliveredCount',
            'sf', 'sfAccent', 'isPreview', 'featuredProducts', 'latestReviews', 'selections', 'welcomePromo',
            'activeCategory'
        ));
    }
}
