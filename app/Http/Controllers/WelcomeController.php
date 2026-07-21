<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\DeliveryCompany;
use Illuminate\Http\Request;

/**
 * ═══════════════════════════════════════════════════════════════
 * WelcomeController — Page d'accueil publique
 * ═══════════════════════════════════════════════════════════════
 * Injecte dans la vue welcome :
 *   $shops     → boutiques approuvées (avec nb produits)
 *   $companies → entreprises de livraison approuvées
 *   $stats     → chiffres clés pour la section hero
 */
class WelcomeController extends Controller
{
    public function index()
    {
        /* ── Boutiques approuvées pour la vitrine ── */
        $shops = Shop::where('is_approved', true)
            ->withCount('products')
            ->latest()
            ->paginate(12);

        /* ── Entreprises de livraison (les plus actives d'abord) ── */
        $companies = DeliveryCompany::where('approved', true)
            ->where('active', true)
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->orderByDesc('id')
            ->get();

        /* ── Stats sociales (compteurs animés dans le hero) ── */
        $stats = [
            'total_shops'    => Shop::where('is_approved', true)->count(),
            'total_orders'   => Order::count(),
            'total_clients'  => User::where('role', 'client')->count(),
            'total_livreurs' => User::where('role', 'livreur')->count(),
        ];

        return view('welcome', compact('shops', 'companies', 'stats'));
    }

    /**
     * Page d'accueil alternative — style "Alibaba" : affiche directement
     * les produits (flash, recommandés, boutiques, catalogue complet)
     * au lieu de la page marketing. Accessible aux invités comme aux clients.
     */
    public function catalogue(Request $request)
    {
        $user = auth()->user();

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
            ->with('shop:id,name,image')
            ->latest()
            ->limit(10)
            ->get();

        /* ── Produits recommandés (mis en vedette par les boutiques) ── */
        $recommendedProducts = Product::where('is_active', true)
            ->whereHas('shop', $approvedShopFilter)
            ->where('is_featured', true)
            ->with('shop:id,name,image')
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
            ->with(['shop:id,name,image,country,type']);

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

        $products = $query->latest()->paginate(24)->withQueryString();

        $categories = Product::select('category')
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->whereHas('shop', $approvedShopFilter)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return view('welcome2', compact(
            'flashProducts', 'recommendedProducts', 'shops', 'products', 'categories'
        ));
    }
}