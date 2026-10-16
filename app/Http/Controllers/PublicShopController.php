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

    public function products(Shop $shop, Request $request)
    {
        abort_unless($shop->is_approved, 404);

        // Réglages de la vitrine. En aperçu (éditeur « Personnaliser ma vitrine »), le
        // propriétaire peut passer une configuration non publiée dans ?vitrine=… : elle
        // n'est appliquée qu'à lui, jamais aux visiteurs.
        $sf = Storefront::forShop($shop);
        $sfAccent = $shop->accent_color ?: '#1F7CF6';
        $isOwner = Auth::check() && ((int) Auth::id() === (int) $shop->user_id || (int) Auth::user()->shop_id === (int) $shop->id);
        $isPreview = $isOwner && $request->filled('vitrine');
        if ($isPreview) {
            $sf = Storefront::fromPreviewParam($request->query('vitrine')) ?? $sf;
            if (isset(Storefront::ACCENT_INK[$request->query('accent')])) $sfAccent = $request->query('accent');
        }

        // Visite (une par visiteur et par jour, hors vendeur et robots) et sa provenance
        // (Facebook, Instagram, WhatsApp…) : tableau de bord + page Marketing
        TrafficSource::recordLanding($shop, $request, $isPreview);

        $query = $shop->products()->where('is_active', true);
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

        return view('public.shops.products', compact(
            'shop', 'products', 'categories', 'reviewAvg', 'reviewCount', 'deliveredCount',
            'sf', 'sfAccent', 'isPreview', 'featuredProducts', 'latestReviews'
        ));
    }
}
