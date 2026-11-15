<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Models\Shop;
use App\Support\Growth;
use App\Support\PageModels;
use App\Support\ProductPage;
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

    /** Une collection de la boutique : /shops/{id}/products/collection/{collection}. */
    public function collection(Shop $shop, string $collection, Request $request)
    {
        return $this->products($shop, $request, null, $collection);
    }

    /** Une collection via l'adresse courte : /s/{slug}/collection/{collection}. */
    public function bySlugCollection(string $slug, string $collection, Request $request)
    {
        $shop = Shop::where('slug', $slug)->firstOrFail();
        return $this->products($shop, $request, null, $collection);
    }

    /** Page produit via l'adresse courte : /s/{slug}/p/{12-nom-du-produit}. */
    public function bySlugProduct(string $slug, string $produit, Request $request)
    {
        $shop = Shop::where('slug', $slug)->firstOrFail();
        return $this->product($shop, $produit, $request);
    }

    /**
     * Page produit publique longue (modèle Express) : galerie, packs, commande en 1 minute sur la page,
     * sections de vente (contenu products.landing), avis réels, barre « Commander » fixe.
     * Même parcours d'achat que les modèles de page (panier partagé, commande payée à la livraison).
     */
    public function product(Shop $shop, string $produit, Request $request)
    {
        abort_unless($shop->is_approved, 404);
        if (Growth::applyScheduled($shop)) $shop->refresh();
        $isOwner = Auth::check() && ((int) Auth::id() === (int) $shop->user_id || (int) Auth::user()->shop_id === (int) $shop->id);
        // Un brouillon n'est visible que de sa boutique (aperçu de l'écran « Page de vente »)
        $product = $shop->products()->when(!$isOwner, fn ($q) => $q->where('is_active', true))->findOrFail(ProductPage::idFrom($produit));
        // Adresse canonique (nom du produit changé, lien ancien) : une seule adresse par produit
        if ($produit !== ProductPage::param($product)) return redirect()->to(ProductPage::url($shop, $product), 301);

        $sf = Storefront::forShop($shop);
        if ($product->is_active) {
            TrafficSource::recordLanding($shop, $request, false);
            TrafficSource::recordProductView($product, $request);
        }

        $reviewStats = Review::where('vendeur_id', $shop->user_id)->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating, SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as happy')->first();
        $reviewCount = (int) ($reviewStats->cnt ?? 0);
        $reviewAvg = $reviewCount > 0 ? round((float) $reviewStats->avg_rating, 1) : null;
        $deliveredCount = Order::where('shop_id', $shop->id)->where('status', Order::STATUS_LIVREE)->count();
        $proofs = Growth::proofs($shop, $sf['settings'], $deliveredCount, $reviewCount);
        $pm = PageModels::data($shop, $sf, $deliveredCount, $reviewAvg, $reviewCount, $proofs);
        $photos = ProductPage::photos($product);
        $productData = collect($pm['products'])->firstWhere('id', $product->id);
        if (!$productData) { // brouillon : absent de la liste des produits en ligne
            $productData = PageModels::product($product, $shop, $sf['settings']);
            $pm['products'] = collect($pm['products'])->push($productData)->values();
        }

        return view('public.shops.express.product', [
            'shop' => $shop, 'pm' => $pm, 'model' => 'express', 'isOwner' => false, 'publishedModel' => $sf['page_model'],
            'activeCategory' => null, 'previewLink' => null, 'shareImage' => $photos[0] ?? null,
            'pageTitle' => $product->name . ' · ' . $shop->name,
            'product' => $productData, 'draft' => !$product->is_active,
            'photos' => $photos, 'content' => ProductPage::content($product), 'ownerEdit' => $isOwner,
            // Chiffres réels (jamais saisis) : part d'avis à 4 ou 5 étoiles, seulement à partir de 5 avis
            'happy' => $reviewCount >= 5 ? (int) round(100 * (int) $reviewStats->happy / $reviewCount) : null,
        ]);
    }

    /** Lien d'aperçu partageable (48 h) : la vitrine en cours de préparation, avec un formulaire d'avis. */
    public function previewLink(string $token, Request $request)
    {
        $shop = Shop::where('preview_token', $token)->first();
        if (!$shop || !Growth::previewActive($shop)) {
            return response()->view('public.shops.preview-expired', [], 410);
        }
        [$config, $accent] = Growth::working($shop);
        return $this->products($shop, $request, null, null, ['config' => $config, 'accent' => $accent, 'token' => $token, 'expires' => $shop->preview_expires_at]);
    }

    /** Avis laissé sur un lien d'aperçu (visible du vendeur dans l'onglet Qualité). */
    public function previewFeedback(string $token, Request $request)
    {
        $shop = Shop::where('preview_token', $token)->first();
        abort_unless($shop && Growth::previewActive($shop), 410);
        $data = $request->validate([
            'name'    => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'message.required' => 'Écrivez votre avis avant d’envoyer.',
            'message.min'      => 'Votre avis est trop court.',
            'message.max'      => 'Votre avis ne peut pas dépasser 500 caractères.',
        ]);
        $list = $shop->preview_feedback ?? [];
        if (count($list) >= Growth::FEEDBACK_MAX) {
            return back()->withErrors(['message' => 'Ce lien a déjà reçu le nombre maximum d’avis.']);
        }
        $clean = fn ($v) => trim(strip_tags(mb_scrub((string) $v, 'UTF-8'))); // texte mal encodé : caractères invalides retirés
        $message = $clean($data['message']);
        if (mb_strlen($message) < 3) return back()->withErrors(['message' => 'Votre avis est trop court.'])->withInput();
        $list[] = ['name' => $clean($data['name'] ?? '') ?: 'Anonyme', 'message' => $message, 'at' => now()->toIso8601String()];
        $shop->update(['preview_feedback' => $list]);
        return back()->with('feedback_sent', true);
    }

    /**
     * Vue d'un produit ouverte en fiche rapide dans un modèle de page boutique : comptée comme une vue
     * de sa fiche (une par visiteur et par jour, hors vendeur et robots), pour les statistiques du vendeur.
     */
    public function productView(Shop $shop, \App\Models\Product $product, Request $request)
    {
        if ($product->shop_id === $shop->id && $product->is_active) TrafficSource::recordProductView($product, $request);
        return response()->noContent();
    }

    /**
     * Vitrine publique : toujours un thème (Express par défaut, ou Signature). Le vendeur peut essayer
     * l'autre thème sur sa vitrine (?modele=…) sans rien publier. Lien d'aperçu (48 h) : la version
     * en préparation, avec un formulaire d'avis.
     */
    public function products(Shop $shop, Request $request, ?string $categorySlug = null, ?string $collectionId = null, ?array $previewLink = null)
    {
        abort_unless($shop->is_approved, 404);
        // Publication programmée arrivée à son heure
        if (Growth::applyScheduled($shop)) $shop->refresh();

        // Les collections n'existent plus : un ancien lien partagé mène à la boutique entière
        if ($collectionId !== null) return redirect()->to(Storefront::publicUrl($shop));

        // Lien d'une catégorie : on retrouve son nom parmi celles des produits en ligne.
        // Catégorie inconnue (lien ancien, produits retirés) → la boutique entière, sans erreur.
        $activeCategory = null;
        if ($categorySlug !== null) {
            $activeCategory = collect(Storefront::categoryLinks($shop))->firstWhere('slug', $categorySlug);
            if (!$activeCategory) return redirect()->to(Storefront::publicUrl($shop));
        }

        $sf = $previewLink ? $previewLink['config'] : Storefront::forShop($shop);
        $isOwner = Auth::check() && ((int) Auth::id() === (int) $shop->user_id || (int) Auth::user()->shop_id === (int) $shop->id);

        // Visite (une par visiteur et par jour, hors vendeur et robots) et sa provenance
        // (Facebook, Instagram, WhatsApp…) : tableau de bord + page Marketing
        TrafficSource::recordLanding($shop, $request, (bool) $previewLink);

        // Avis réels, commandes livrées et preuves de confiance (jamais inventés)
        $reviewStats = Review::where('vendeur_id', $shop->user_id)->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')->first();
        $reviewCount = (int) ($reviewStats->cnt ?? 0);
        $reviewAvg = $reviewCount > 0 ? round((float) $reviewStats->avg_rating, 1) : null;
        $deliveredCount = Order::where('shop_id', $shop->id)->where('status', Order::STATUS_LIVREE)->count();
        $proofs = Growth::proofs($shop, $sf['settings'], $deliveredCount, $reviewCount);

        $tryModel = $isOwner && !$previewLink && PageModels::isModel($request->query('modele')) ? $request->query('modele') : null;
        $pageModel = $tryModel ?? $sf['page_model'];

        return view('public.shops.models.' . $pageModel, [
            'shop' => $shop, 'pm' => PageModels::data($shop, $sf, $deliveredCount, $reviewAvg, $reviewCount, $proofs),
            'model' => $pageModel, 'isOwner' => $isOwner, 'publishedModel' => $sf['page_model'],
            'activeCategory' => $activeCategory, 'previewLink' => $previewLink, 'shareImage' => Growth::shareImage($shop, $sf),
            'pageTitle' => $activeCategory ? $activeCategory['name'] . ' · ' . $shop->name : $shop->name . ' — Boutique en ligne',
        ]);
    }
}
