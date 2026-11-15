<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Shop;
use App\Services\ImageOptimizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Thèmes de la vitrine côté client, choisis dans « Ma vitrine » (ou la barre « Thèmes Shopio »
 * visible du vendeur sur sa vitrine) : Express (par défaut) et Signature. Chacun a son style et sa
 * mise en page, avec le même parcours d'achat : fiche rapide, favoris, vus récemment, panier,
 * commande (payée à la livraison) et confirmation. Toutes les données viennent de la boutique.
 */
class PageModels
{
    /** [nom, description courte]. */
    public const MODELS = [
        'express'   => ['Express', 'Qui vend : packs, commande en 1 minute, page produit longue'],
        'signature' => ['Signature', 'Premium clair : ivoire, encre et vert profond'],
    ];

    /** Thème d'une boutique qui n'en a pas choisi (ou dont l'ancien modèle a été retiré). */
    public const DEFAULT = 'express';

    /** Communes proposées à la commande. */
    public const COMMUNES = ['Kaloum', 'Dixinn', 'Matam', 'Ratoma', 'Matoto', 'Hors Conakry'];

    public const PRODUCTS_MAX = 150;

    public static function isModel(?string $m): bool
    {
        return is_string($m) && isset(self::MODELS[$m]);
    }

    /** Données d'un produit pour les modèles de page et la page produit (aussi un brouillon vu par son vendeur). */
    public static function product(Product $p, Shop $shop, array $set): array
    {
        $isClient = Auth::check() && Auth::user()->role === 'client';
        $old = $set['show_strike'] && $p->original_price && $p->original_price > $p->current_price ? (int) $p->original_price : null;
        return [
            'id'       => $p->id,
            'name'     => $p->name,
            'cat'      => (string) $p->category,
            'price'    => (int) $p->current_price,
            'old'      => $old,
            'photo'    => $p->image ? (ImageOptimizer::url($p->image, 'medium') ?? asset('storage/' . $p->image)) : null,
            'thumb'    => $p->image ? (ImageOptimizer::url($p->image, 'thumb') ?? asset('storage/' . $p->image)) : null,
            'desc'     => Str::limit(trim(strip_tags((string) $p->description)), 600),
            'stock'    => $p->has_variants ? null : $p->stock,
            'variants' => (bool) $p->has_variants,
            'featured' => (bool) $p->is_featured,
            'new'      => $p->created_at && $p->created_at->gte(now()->subDays(7)),
            'url'      => route('client.orders.createFromProduct', $p),
            'page'     => ProductPage::url($shop, $p),   // page produit publique (modèle Express)
            'packs'    => ProductPage::packs($p),        // packs à prix réduit, appliqués aussi à la commande
            'nego'     => $isClient ? route('client.messages.index', $p) : route('register', ['redirect' => route('client.messages.index', $p), 'role' => 'client']),
        ];
    }

    /**
     * Données d'une page modèle : boutique, produits en ligne, catégories, avis réels, réglages utiles.
     * $sf : réglages de la vitrine (publiés ou aperçu).
     */
    public static function data(Shop $shop, array $sf, int $delivered, ?float $reviewAvg, int $reviewCount, array $proofs): array
    {
        $set = $sf['settings'];
        $devise = $shop->currency ?? 'GNF';
        $isClient = Auth::check() && Auth::user()->role === 'client';

        $products = $shop->products()->where('is_active', true)->orderByDesc('is_featured')->latest()->take(self::PRODUCTS_MAX)->get()
            ->map(fn ($p) => self::product($p, $shop, $set))->values();

        $reviews = Review::where('vendeur_id', $shop->user_id)->whereNotNull('comment')->where('comment', '!=', '')
            ->with('client:id,name')->latest()->take(6)->get()
            ->map(fn ($r) => ['name' => $r->client?->name ? Str::of($r->client->name)->explode(' ')->first() : 'Client', 'rating' => (int) $r->rating, 'text' => Str::limit($r->comment, 280)])
            ->values();

        $user = Auth::user();
        $logo = $shop->image ? (ImageOptimizer::url($shop->image, 'thumb') ?? asset('storage/' . $shop->image)) : null;
        $slogan = $sf['cover']['slogan'] !== '' ? $sf['cover']['slogan'] : Str::limit(trim(strip_tags((string) $shop->description)), 90, '');

        return [
            'shop' => [
                'id' => $shop->id, 'name' => $shop->name, 'slogan' => $slogan, 'logo' => $logo, 'initials' => Growth::initials($shop->name),
                'handle' => $shop->slug ?: Str::slug($shop->name), 'verified' => (bool) $shop->is_approved, 'address' => (string) $shop->address,
                'type' => (string) $shop->type, 'url' => Storefront::publicUrl($shop), 'currency' => $devise,
            ],
            'products'   => $products,
            'categories' => collect(Storefront::categoryLinks($shop))->map(fn ($c) => ['name' => $c['name'], 'count' => $c['count'], 'url' => $c['url']])->values(),
            'reviews'    => $reviews,
            'rating'     => $proofs['rating'] && $reviewAvg ? ['avg' => $reviewAvg, 'count' => $reviewCount] : null,
            'delivered'  => $proofs['delivered'] ? $delivered : null,
            'settings'   => [
                'vacation' => $set['vacation'], 'vacationMsg' => $set['vacation_msg'], 'nego' => $set['show_nego'],
                'stock' => $set['show_stock'], 'wa' => $set['wa_mode'] && $shop->phone,
                // Paniers abandonnés : le formulaire envoie ce que le client a rempli (Commandes → Paniers abandonnés)
                'carts' => OrderBoard::settings($sf)['carts']['enabled'],
            ],
            // Sections de la page d'accueil, une liste par thème (Boutique → Sections)
            'home'       => $sf['home'],
            'communes'   => self::COMMUNES,
            'user'       => $user ? ['name' => $user->name, 'phone' => (string) ($user->phone ?? '')] : null,
            'urls'       => [
                'order'  => route('public.shops.order', $shop),
                'cart'   => route('public.shops.cart', $shop),
                'view'   => route('public.shops.view', [$shop, 0]), // « 0 » remplacé par l'identifiant du produit
                'login'  => route('login', ['redirect' => Storefront::publicUrl($shop)]),
                'follow' => $isClient ? route('client.favorites.toggle', $shop) : route('register', ['redirect' => Storefront::publicUrl($shop), 'role' => 'client']),
                'followIsForm' => $isClient,
            ],
            'today'      => now('Africa/Conakry')->toDateString(),
        ];
    }
}
