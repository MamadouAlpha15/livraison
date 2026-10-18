<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use App\Models\Shop;
use App\Support\Growth;
use App\Support\PageModels;
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

    /** Clic dans une section de la vitrine (onglet Qualité, « clics par section »). */
    public function click(Shop $shop, Request $request)
    {
        $id = (string) $request->input('block');
        $type = $id === '__banner' ? '__banner' : (Storefront::block(Storefront::forShop($shop), $id)['type'] ?? null);
        if ($type) \App\Models\StorefrontClick::record($shop->id, $type);
        return response()->noContent();
    }

    public function products(Shop $shop, Request $request, ?string $categorySlug = null, ?string $collectionId = null, ?array $previewLink = null)
    {
        abort_unless($shop->is_approved, 404);
        // Publication programmée arrivée à son heure (onglet Personnalisation)
        if (Growth::applyScheduled($shop)) $shop->refresh();

        // Lien d'une catégorie : on retrouve son nom parmi celles des produits en ligne.
        // Catégorie inconnue (lien ancien, produits retirés) → la boutique entière, sans erreur.
        $activeCategory = null;
        if ($categorySlug !== null) {
            $activeCategory = collect(Storefront::categoryLinks($shop))->firstWhere('slug', $categorySlug);
            if (!$activeCategory) return redirect()->to(Storefront::publicUrl($shop));
        }

        // Réglages de la vitrine. En aperçu (éditeur de boutique, ?apercu=1), le brouillon
        // gardé en session est affiché au seul propriétaire, jamais aux visiteurs.
        // Lien d'aperçu (48 h) : la version en préparation, pour qui a le lien.
        $sf = Storefront::forShop($shop);
        $sfAccent = Storefront::isHex($shop->accent_color) ? strtoupper($shop->accent_color) : '#1F7CF6';
        $isOwner = Auth::check() && ((int) Auth::id() === (int) $shop->user_id || (int) Auth::user()->shop_id === (int) $shop->id);
        $isPreview = $isOwner && $request->boolean('apercu') && !$previewLink;
        if ($isPreview && ($draft = $request->session()->get('storefront_draft.' . $shop->id))) {
            $sf = Storefront::normalize($draft['config'] ?? null);
            if (Storefront::isHex($draft['accent'] ?? null)) $sfAccent = $draft['accent'];
        }
        if ($previewLink) [$sf, $sfAccent] = [$previewLink['config'], $previewLink['accent']];

        // Collection (lien partagé) : inconnue ou masquée → la boutique entière
        $activeCollection = null;
        if ($collectionId !== null) {
            $c = Growth::collection($sf, $collectionId);
            if (!$c || !$c['on']) return redirect()->to(Storefront::publicUrl($shop));
            $activeCollection = $c + ['url' => Growth::collectionUrl($shop, $c)];
        }

        // Visite (une par visiteur et par jour, hors vendeur et robots) et sa provenance
        // (Facebook, Instagram, WhatsApp…) : tableau de bord + page Marketing
        TrafficSource::recordLanding($shop, $request, $isPreview || (bool) $previewLink);

        if ($activeCollection) {
            $all = Growth::collectionProducts($shop, $activeCollection, null);
            $activeCollection['count'] = $all->count();
            $page = max(1, $request->integer('page', 1));
            $products = (new \Illuminate\Pagination\LengthAwarePaginator($all->forPage($page, 20)->values(), $all->count(), 20, $page, ['path' => $request->url()]))->withQueryString();
        } else {
            $query = $shop->products()->where('is_active', true)
                ->when($activeCategory, fn ($q) => $q->where('category', $activeCategory['name']));
            match ($sf['sort']) {
                'price_asc'  => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'recent'     => $query->latest(),
                default      => $query->orderByDesc('is_featured')->latest(),
            };
            $products = $query->paginate(20)->withQueryString();
        }

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

        // Collections affichées (bloc « Collections »), sections automatiques, preuves de confiance
        $collectionRows = collect($sf['collections'])->where('on', true)
            ->map(fn ($c) => ['c' => $c, 'url' => Growth::collectionUrl($shop, $c), 'items' => Growth::collectionProducts($shop, $c, 4)])
            ->filter(fn ($r) => $r['items']->isNotEmpty())->values();
        $hasBlock = fn ($t) => collect($sf['blocks'])->contains(fn ($b) => $b['type'] === $t && $b['on']);
        $trending = $hasBlock('trending') ? Growth::trending($shop) : collect();
        $lowStock = $hasBlock('lowstock') ? Growth::lowStock($shop) : collect();
        $proofs = Growth::proofs($shop, $sf['settings'], $deliveredCount, $reviewCount);
        $coverSlides = $sf['cover']['on'] ? Growth::slides($shop, $sf) : [];
        $shareImage = Growth::shareImage($shop, $sf);

        // Message de bienvenue : seulement avec un vrai code promo encore utilisable
        $welcomePromo = null;
        $set = $sf['settings'];
        if ($set['welcome'] && $set['welcome_promo_id'] && !$set['vacation'] && !$previewLink) {
            $welcomePromo = \App\Models\PromoCode::where('shop_id', $shop->id)->whereKey($set['welcome_promo_id'])->active()
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->where(fn ($q) => $q->whereNull('max_uses')->orWhereColumn('uses_count', '<', 'max_uses'))
                ->first();
        }

        // Modèle de page (Prestige, Pop, Épuré, Éditorial, Fil). Le vendeur peut essayer un autre modèle
        // sur sa vitrine (?modele=…) sans rien publier ; l'aperçu de l'éditeur et les collections restent en Standard.
        $tryModel = $isOwner && !$isPreview && !$previewLink && isset(PageModels::MODELS[$request->query('modele')]) ? $request->query('modele') : null;
        $pageModel = $tryModel ?? $sf['page_model'];
        if (PageModels::isModel($pageModel) && !$isPreview && !$activeCollection) {
            return view('public.shops.models.' . $pageModel, [
                'shop' => $shop, 'pm' => PageModels::data($shop, $sf, $deliveredCount, $reviewAvg, $reviewCount, $proofs),
                'model' => $pageModel, 'isOwner' => $isOwner, 'publishedModel' => $sf['page_model'],
                'activeCategory' => $activeCategory, 'previewLink' => $previewLink, 'shareImage' => $shareImage,
                'pageTitle' => $activeCategory ? $activeCategory['name'] . ' · ' . $shop->name : $shop->name . ' — Boutique en ligne',
            ]);
        }
        $isOwnerBar = $isOwner && !$isPreview && !$previewLink;
        $publishedModel = $sf['page_model'];

        return view('public.shops.products', compact(
            'isOwnerBar', 'pageModel', 'publishedModel',
            'shop', 'products', 'categories', 'reviewAvg', 'reviewCount', 'deliveredCount',
            'sf', 'sfAccent', 'isPreview', 'featuredProducts', 'latestReviews', 'selections', 'welcomePromo',
            'activeCategory', 'activeCollection', 'collectionRows', 'trending', 'lowStock', 'proofs', 'coverSlides',
            'shareImage', 'previewLink'
        ));
    }
}
