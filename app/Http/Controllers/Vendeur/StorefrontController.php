<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Concerns\ResolvesOwnShop;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Support\Growth;
use App\Support\HomeSections;
use App\Support\PageModels;
use App\Support\Storefront;
use App\Support\StorefrontEditor;
use App\Support\TrafficSource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * « Ma vitrine » : le thème de la boutique (Express ou Signature), sa couleur, son adresse courte,
 * l'affichage des fiches produit et le mode vacances. Enregistré et publié tout de suite.
 * Agit toujours sur la boutique de l'utilisateur connecté, jamais sur un id fourni.
 */
class StorefrontController extends Controller
{
    use ResolvesOwnShop;

    /** Couleurs proposées (le vendeur peut aussi choisir la sienne). */
    public const COLORS = ['#2F5D1E' => 'Vert forêt', '#1F7CF6' => 'Bleu Shopio', '#0B1E3D' => 'Bleu nuit', '#B42318' => 'Rouge', '#C2410C' => 'Orange brûlé', '#7C3AED' => 'Violet', '#16162A' => 'Noir'];

    public function edit(): View
    {
        $shop = $this->shop();
        $config = Storefront::forShop($shop);
        $publicUrl = Storefront::publicUrl($shop);

        return view('vendeur.shop.storefront', [
            'shop'      => $shop,
            'config'    => $config,
            'accent'    => Storefront::isHex($shop->accent_color) ? strtoupper($shop->accent_color) : '#2F5D1E',
            'colors'    => self::COLORS,
            'models'    => PageModels::MODELS,
            'publicUrl' => $publicUrl,
            'slugBase'  => rtrim(route('public.shops.slug', 'x'), 'x'),
            'qrSvg'     => self::qrSvg(TrafficSource::tag($publicUrl, 'qr', 'print'), 220),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $data = $request->validate([
            'page_model'   => ['required', Rule::in(array_keys(PageModels::MODELS))],
            'accent'       => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'slug'         => ['nullable', 'string', 'max:60'],
            'show_stock'   => ['nullable', 'boolean'],
            'show_strike'  => ['nullable', 'boolean'],
            'show_nego'    => ['nullable', 'boolean'],
            'vacation'     => ['nullable', 'boolean'],
            'vacation_msg' => ['nullable', 'string', 'max:120'],
        ], [
            'page_model.in' => 'Choisissez le thème Express ou Signature.',
            'accent.regex'  => 'Couleur invalide.',
            'vacation_msg.max' => 'Le message de vacances ne peut pas dépasser 120 caractères.',
        ]);

        // Adresse courte (facultative)
        $slug = Storefront::slugify((string) ($data['slug'] ?? ''));
        if ($slug !== '' && ($error = Storefront::slugError($slug, $shop->id))) {
            return back()->withErrors(['slug' => 'Adresse de la boutique : ' . $error])->withInput();
        }

        $config = Storefront::forShop($shop);
        $config['page_model'] = $data['page_model'];
        foreach (['show_stock', 'show_strike', 'show_nego', 'vacation'] as $k) $config['settings'][$k] = $request->boolean($k);
        $msg = trim(strip_tags((string) ($data['vacation_msg'] ?? '')));
        $config['settings']['vacation_msg'] = $msg !== '' ? $msg : Storefront::settingsDefaults()['vacation_msg'];

        // Publié tout de suite ; un brouillon de « Modifier la page » en attente reçoit les mêmes réglages
        Growth::publish($shop, Storefront::normalize($config), strtoupper($data['accent']), ['slug' => $slug !== '' ? $slug : null], true);
        StorefrontEditor::syncDraft($shop->fresh(), function (array $c) use ($config) {
            $c['page_model'] = $config['page_model'];
            foreach (['show_stock', 'show_strike', 'show_nego', 'vacation', 'vacation_msg'] as $k) $c['settings'][$k] = $config['settings'][$k];
            return $c;
        });

        return redirect()->route('boutique.storefront.edit')->with('success', $config['settings']['vacation']
            ? 'Vitrine enregistrée. Mode vacances actif : les commandes sont en pause.'
            : 'Vitrine enregistrée. Vos clients voient maintenant ces réglages.');
    }

    // ─────────────────────────────────────────────────────────── Sections de l'accueil

    /** « Boutique → Sections » : sections de la page d'accueil, une liste par thème, avec aperçu en direct. */
    public function sections(Request $request): View
    {
        $shop = $this->shop();
        $config = Storefront::forShop($shop);
        // Page modifiée : l'accueil d'un thème (Express, Signature) ou la page produit
        $theme = isset(HomeSections::PAGES[$request->query('theme')]) ? $request->query('theme') : $config['page_model'];
        $defaults = HomeSections::DEFAULTS;
        // Titre d'accueil prévu : le slogan de la boutique, sinon son nom (Signature) ou la phrase du thème
        $slogan = (string) $config['cover']['slogan'];
        if ($slogan !== '') $defaults['express']['hero']['title'] = $defaults['signature']['hero']['title'] = $slogan;
        if ($defaults['signature']['hero']['title'] === '') $defaults['signature']['hero']['title'] = $shop->name;

        $images = HomeSections::images($config['home']);

        // Produits proposés pour l'aperçu de la page produit (brouillons compris : vus par leur seul vendeur)
        $previewProducts = $shop->products()->orderByDesc('is_active')->orderByDesc('is_featured')->latest()->take(60)->get(['id', 'name', 'is_active'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name . ($p->is_active ? '' : ' (brouillon)'), 'url' => \App\Support\ProductPage::url($shop, $p)])->values();

        return view('vendeur.shop.sections', [
            'shop'       => $shop,
            'pages'      => HomeSections::PAGES,
            'previewProducts' => $previewProducts,
            'theme'      => $theme,
            'published'  => $config['page_model'],
            'home'       => $config['home'],
            'models'     => PageModels::MODELS,
            'defaults'   => $defaults,
            'images'     => $images,
            'categories' => collect(Storefront::categoryLinks($shop))->pluck('name')->values(),
            'publicUrl'  => Storefront::publicUrl($shop),
        ]);
    }

    /** Enregistre et publie les sections d'un thème (JSON). */
    public function saveSections(Request $request): JsonResponse
    {
        $shop = $this->shop();
        $data = $request->validate([
            'theme'    => ['required', Rule::in(array_keys(HomeSections::PAGES))],
            'sections' => ['required', 'array', 'max:' . (count(HomeSections::BUILTIN['signature']) + HomeSections::CUSTOM_MAX)],
        ], ['theme.in' => 'Thème inconnu.', 'sections.max' => 'Trop de sections : ' . HomeSections::CUSTOM_MAX . ' sections ajoutées au maximum.']);

        $config = Storefront::forShop($shop);
        $config['home'][$data['theme']] = HomeSections::normalize($data['theme'], $data['sections']);
        $shop->update(['storefront' => Storefront::normalize($config)]);
        // Un brouillon de « Modifier la page » en attente garde les mêmes sections
        StorefrontEditor::syncDraft($shop->fresh(), function (array $c) use ($config, $data) { $c['home'][$data['theme']] = $config['home'][$data['theme']]; return $c; });

        return response()->json(['ok' => true, 'sections' => Storefront::forShop($shop->fresh())['home'][$data['theme']]]);
    }

    /**
     * « Rédiger avec Shopio IA » dans Boutique → Sections (une section, ou toutes) : renvoie leur contenu rédigé,
     * rien n'est enregistré avant « Enregistrer et publier ». Plan Pro, 30 rédactions par heure.
     */
    public function sectionsAi(Request $request, \App\Services\SectionAi $ai): JsonResponse
    {
        $shop = $this->shop();
        if (!($shop->plan === 'pro' && $shop->plan_expires_at?->isFuture())) {
            return response()->json(['error' => 'Shopio IA est réservé au plan Pro. Passez au plan Pro pour rédiger vos sections en un clic.'], 403);
        }
        $data = $request->validate([
            'theme'    => ['required', Rule::in(array_keys(HomeSections::PAGES))],
            'sections' => ['required', 'array', 'min:1', 'max:40'],
            'sections.*.id' => ['required', 'string', 'max:24'],
            'sections.*.type' => ['required', 'string', 'max:24'],
            'instructions' => ['nullable', 'string', 'max:500'],
        ]);
        $key = 'ai-sections:' . \Illuminate\Support\Facades\Auth::id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['error' => 'Limite atteinte (30 rédactions par heure). Réessayez plus tard.'], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 3600);

        try {
            return response()->json(['sections' => (object) $ai->write($shop, $data['theme'], $request->input('sections'), null, (string) ($data['instructions'] ?? ''))]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    /** « Créer la photo avec Shopio IA » dans Boutique → Sections (voir App\Services\SectionImageAi). */
    public function sectionImage(Request $request, \App\Services\SectionImageAi $ai): JsonResponse
    {
        return $ai->respond($request, $this->shop());
    }

    // ─────────────────────────────────────────────────────────── Modifier la page (sur la vitrine en ligne)

    /** Boutique du propriétaire connecté : jamais un employé (seul le propriétaire modifie sa vitrine). */
    private function owner(): Shop
    {
        $shop = $this->shop();
        abort_unless(StorefrontEditor::isOwner($shop, Auth::user()), 403, 'Seul le propriétaire de la boutique modifie la vitrine.');
        return $shop;
    }

    /** « Ma vitrine en direct » (menu) : la vitrine en mode édition, ouverte sur l'en-tête ou les réglages si demandé. */
    public function live(Request $request): RedirectResponse
    {
        $shop = $this->owner();
        $open = in_array($request->query('ouvrir'), ['entete', 'reglages', 'apercu'], true) ? $request->query('ouvrir') : null;
        return redirect()->to(StorefrontEditor::editUrl($shop, $open));
    }

    /**
     * Anciennes adresses de la page « Boutique » (/boutique/vitrine/grandir?onglet=…), supprimée :
     * couverture → en-tête, conversion → réglages de la page, partage et qualité → Marketing.
     */
    public function legacyGrow(Request $request): RedirectResponse
    {
        return match ($request->query('onglet')) {
            'partage'    => redirect()->route('boutique.marketing', ['onglet' => 'partage']),
            'qualite'    => redirect()->route('boutique.marketing', ['onglet' => 'qualite']),
            'conversion' => redirect()->route('boutique.storefront.live', ['ouvrir' => 'reglages']),
            default      => redirect()->route('boutique.storefront.live', ['ouvrir' => 'entete']),
        };
    }

    /** L'éditeur (page /s/{slug}?edition=1) : barre, vitrine dans un cadre, panneau des réglages. */
    public function editor(Shop $shop, Request $request): View
    {
        $state = StorefrontEditor::working($shop);
        $config = $state['config'];
        $published = Storefront::forShop($shop);
        $theme = PageModels::isModel($request->query('modele')) ? $request->query('modele') : $published['page_model'];

        $defaults = HomeSections::DEFAULTS;
        $slogan = (string) $config['cover']['slogan'];
        if ($slogan !== '') $defaults['express']['hero']['title'] = $defaults['signature']['hero']['title'] = $slogan;
        if ($defaults['signature']['hero']['title'] === '') $defaults['signature']['hero']['title'] = $state['name'];

        $share = $config['cover']['slides'][0] ?? ['src' => 'default', 'img' => null];
        $coverUrls = collect($config['cover']['slides'])->merge($published['cover']['slides'])
            ->filter(fn ($s) => $s['src'] === 'photo' && $s['img'])
            ->mapWithKeys(fn ($s) => [$s['img'] => \App\Services\ImageOptimizer::url(str_replace('/medium/', '/large/', $s['img']), 'medium')])->all();
        $reviewStats = \App\Models\Review::where('vendeur_id', $shop->user_id)->selectRaw('COUNT(*) as n, AVG(rating) as avg')->first();
        $reviewCount = (int) ($reviewStats->n ?? 0);
        $publicUrl = Storefront::publicUrl($shop);
        $isPro = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
        $logoUrl = $state['image'] ? (\App\Services\ImageOptimizer::url($state['image'], 'thumb') ?? asset('storage/' . $state['image'])) : null;

        $data = [
            // Champs des sections : même rendu que l'éditeur de sections (window.SXFields)
            'types' => HomeSections::TYPES, 'fields' => HomeSections::FIELDS, 'builtin' => HomeSections::BUILTIN, 'addable' => HomeSections::ADDABLE,
            'library' => HomeSections::CATEGORIES, 'defaults' => $defaults, 'tones' => HomeSections::TONES, 'styles' => HomeSections::STYLES,
            'formats' => HomeSections::FORMATS, 'icons' => HomeSections::ICONS, 'testimonial' => HomeSections::TESTIMONIAL_TYPES,
            'images' => (object) HomeSections::images($config['home']), 'categories' => collect(Storefront::categoryLinks($shop))->pluck('name')->values(),
            'limits' => ['custom' => HomeSections::CUSTOM_MAX, 'list' => HomeSections::LIST_MAX],
            'aiTypes' => \App\Services\SectionAi::writableTypes(), 'aiPro' => $isPro,
            'aiImageTypes' => array_keys(\App\Services\SectionImageAi::TYPES), 'aiShots' => \App\Services\SectionImageAi::choices($shop),
            // Thèmes
            'theme' => $theme, 'published' => $published['page_model'], 'models' => PageModels::MODELS,
            // Version de travail
            'state' => [
                'home' => array_intersect_key($config['home'], array_flip(StorefrontEditor::THEMES)),
                'settings' => array_intersect_key($config['settings'], array_flip(['wa_mode', 'proof_delivered', 'proof_response'])),
                'colors' => $config['colors'],
                'identity' => [
                    'name' => $state['name'], 'slogan' => $slogan, 'color' => strtoupper($state['accent']),
                    'logo' => $state['image'] && $config['logo']['src'] !== 'initials' ? 'current' : 'initials', 'logoUrl' => $logoUrl,
                    'share_src' => $share['src'], 'share_img' => $share['src'] === 'photo' ? $share['img'] : null,
                ],
            ],
            'hasDraft' => StorefrontEditor::hasDraft($shop),
            // Couleurs : celles prévues par chaque thème (montrées quand le vendeur n'a rien choisi) et la palette proposée
            'colorKeys' => Storefront::COLOR_KEYS,
            'themeColors' => [
                'express'   => ['accent' => strtoupper($state['accent']), 'bg' => '#FFFFFF', 'ink' => '#111111'],
                'signature' => ['accent' => '#1F3B2D', 'bg' => '#F6F2EA', 'ink' => '#1A1714'],
            ],
            'palette' => array_keys(self::COLORS + Storefront::IDENTITY_COLORS),
            // Sélecteur de page : accueil, catalogue, panier, et la page de vente de chaque produit
            // (le produit demandé par lien direct est toujours dans la liste, même au-delà des 300 premiers)
            'products' => $shop->products()->orderByRaw('id = ? desc', [(int) $request->query('produit')])->orderByDesc('is_active')->latest()->take(300)->get(['id', 'name', 'image', 'is_active'])
                ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'draft' => !$p->is_active, 'url' => route('products.page.edit', $p) . '?integre=1',
                    'public' => \App\Support\ProductPage::url($shop, $p),
                    'thumb' => $p->image ? \App\Services\ImageOptimizer::url($p->image, 'thumb') : null])->values(),
            // En-tête : couleurs, images de partage, aperçus WhatsApp et recherche Shopio
            'shop' => [
                'initials' => Growth::initials($state['name']), 'verified' => (bool) $shop->is_approved, 'address' => (string) $shop->address,
                'type' => (string) $shop->type, 'description' => \Illuminate\Support\Str::limit(trim(strip_tags((string) $shop->description)), 120),
                'host' => preg_replace('#^https?://#', '', $publicUrl), 'publicUrl' => $publicUrl, 'phone' => (bool) $shop->phone,
                'productCount' => $shop->products()->where('is_active', true)->count(), 'nameMax' => max(30, mb_strlen($state['name'])),
            ],
            'colors' => Storefront::IDENTITY_COLORS, 'gradients' => Storefront::COVER_GRADIENTS, 'sources' => Storefront::COVER_SOURCES,
            'coverUrls' => (object) $coverUrls, 'productPhoto' => Growth::productPhoto($shop),
            // Réglages de la page : chiffres réels
            'proofs' => [
                'delivered' => \App\Models\Order::where('shop_id', $shop->id)->where('status', \App\Models\Order::STATUS_LIVREE)->count(),
                'deliveredMin' => Growth::DELIVERED_MIN, 'response' => Growth::responseTime($shop), 'responseMin' => Growth::RESPONSE_MIN_SAMPLES,
                'reviews' => $reviewCount, 'reviewAvg' => $reviewCount ? round((float) $reviewStats->avg, 1) : null, 'ratingMin' => Growth::RATING_MIN,
            ],
            'preview' => Growth::previewActive($shop) ? ['url' => route('storefront.preview-link', $shop->preview_token), 'expires' => $shop->preview_expires_at->timezone('Africa/Conakry')->translatedFormat('j F à H:i'), 'feedback' => count($shop->preview_feedback ?? [])] : null,
            'open' => in_array($request->query('ouvrir'), ['entete', 'reglages', 'apercu'], true) ? $request->query('ouvrir') : null,
            // Lien direct vers la page de vente d'un produit (…?edition=1&produit=92) : seulement un produit de cette boutique
            'openProduct' => $request->filled('produit') && $shop->products()->whereKey((int) $request->query('produit'))->exists() ? (int) $request->query('produit') : null,
            'urls' => [
                'frame' => $publicUrl . '?edition=1&cadre=1', 'render' => route('boutique.storefront.edit.render'),
                'draft' => route('boutique.storefront.edit.draft'), 'publish' => route('boutique.storefront.edit.publish'),
                'coverUpload' => route('boutique.storefront.cover-upload'), 'logoUpload' => route('boutique.storefront.logo-upload'), 'upload' => route('boutique.storefront.cover-upload'),
                'preview' => route('boutique.storefront.preview'), 'ai' => route('boutique.storefront.sections.ai'), 'aiImage' => route('boutique.storefront.sections.image'),
                'public' => $publicUrl, 'quit' => route('boutique.dashboard'), 'shopPhone' => route('shop.edit', $shop) . '?etape=2',
                'quality' => route('boutique.marketing', ['onglet' => 'qualite']),
                'shopEdit' => route('shop.edit', $shop),
            ],
        ];
        return view('public.shops.editor', ['shop' => $shop, 'ed' => $data, 'theme' => $theme]);
    }

    /** Version en cours de saisie, rendue dans le cadre de l'éditeur (rien n'est enregistré). */
    public function renderDraft(Request $request): JsonResponse
    {
        $shop = $this->owner();
        $state = StorefrontEditor::build($shop, $request->all(), $request);
        $token = \Illuminate\Support\Str::random(20);
        Cache::put('sxe:' . $shop->id . ':' . $token, $state, now()->addMinutes(30));
        $model = PageModels::isModel($request->input('theme')) ? $request->input('theme') : null;
        // Page affichée dans le cadre : accueil, catalogue ou panier ouvert
        $page = ['catalogue' => '&vue=produits', 'panier' => '&panier=1'][$request->input('page')] ?? '';
        return response()->json(['url' => Storefront::publicUrl($shop) . '?edition=1&cadre=1&rendu=' . $token . ($model ? '&modele=' . $model : '') . $page]);
    }

    /** « Enregistrer le brouillon » : les visiteurs ne voient rien avant « Publier ». */
    public function saveDraft(Request $request): JsonResponse
    {
        $shop = $this->owner();
        StorefrontEditor::saveDraft($shop, StorefrontEditor::build($shop, $request->all(), $request));
        return response()->json(['ok' => true, 'message' => 'Brouillon enregistré. Vos clients voient encore la version en ligne.']);
    }

    /** « Publier » : la version de l'éditeur devient la vitrine en ligne. */
    public function publishDraft(Request $request): JsonResponse
    {
        $shop = $this->owner();
        StorefrontEditor::publish($shop, StorefrontEditor::build($shop, $request->all(), $request));
        return response()->json(['ok' => true, 'message' => 'Vitrine publiée : vos clients la voient maintenant.']);
    }

    /** Abandonner le brouillon : retour à la version en ligne. */
    public function discardDraft(): JsonResponse
    {
        StorefrontEditor::discardDraft($this->owner());
        return response()->json(['ok' => true]);
    }

    public function uploadCover(Request $request): JsonResponse
    {
        $this->shop(); // aussi utilisé par l'éditeur de sections et l'éditeur produit (photos de sections)
        return response()->json(StorefrontEditor::uploadCover($request));
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $this->owner();
        return response()->json(StorefrontEditor::uploadLogo($request));
    }

    /** « Utiliser ce thème » (barre « Thèmes Shopio » de la vitrine) : publié tout de suite. */
    public function useModel(Request $request): RedirectResponse
    {
        $shop = $this->owner();
        $model = $request->validate(['model' => ['required', Rule::in(array_keys(PageModels::MODELS))]], ['model.in' => 'Thème inconnu.'])['model'];
        StorefrontEditor::useModel($shop, $model);
        return redirect()->to(Storefront::publicUrl($shop))->with('model_used', PageModels::MODELS[$model][0]);
    }

    /** « Partager un aperçu » : lien de 48 h vers le brouillon, avec un formulaire d'avis. */
    public function createPreview(): JsonResponse
    {
        $shop = $this->owner();
        $url = StorefrontEditor::createPreview($shop);
        return response()->json(['url' => $url, 'expires' => $shop->fresh()->preview_expires_at->timezone('Africa/Conakry')->translatedFormat('j F à H:i')]);
    }

    public function disablePreview(): JsonResponse
    {
        StorefrontEditor::disablePreview($this->owner());
        return response()->json(['ok' => true]);
    }

    /** Disponibilité d'une adresse de boutique (/s/…). */
    public function slugCheck(Request $request): JsonResponse
    {
        $shop = $this->shop();
        $slug = Storefront::slugify((string) $request->query('slug', ''));
        $error = Storefront::slugError($slug, $shop->id);

        return response()->json([
            'slug'    => $slug,
            'ok'      => $error === null,
            'message' => $error ?? 'Adresse disponible',
            'url'     => $slug !== '' ? route('public.shops.slug', $slug) : null,
        ]);
    }

    /** QR code de la vitrine, ou d'une de ses catégories (?categorie=…), à télécharger (SVG net à toutes les tailles, pour l'imprimer). */
    public function qr(Request $request): Response
    {
        $shop = $this->shop();
        $url = Storefront::publicUrl($shop);
        $name = $shop->name;
        if ($request->filled('categorie')) {
            $cat = collect(Storefront::categoryLinks($shop))->firstWhere('slug', $request->query('categorie'));
            abort_unless($cat, 404);
            [$url, $name] = [$cat['url'], $shop->name . ' ' . $cat['name']];
        }
        $svg = self::qrSvg(TrafficSource::tag($url, 'qr', 'print'), 1024);
        abort_unless($svg, 404);

        $file = 'qr-vitrine-' . \Illuminate\Support\Str::slug($name) . '.svg';
        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
        ]);
    }

    /** Génère le QR code en SVG ; null si la bibliothèque bacon/bacon-qr-code n'est pas installée. */
    public static function qrSvg(string $url, int $size, bool $withLogo = false): ?string
    {
        if (!class_exists(\BaconQrCode\Writer::class)) return null;

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size, 1),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        // $withLogo : correction d'erreur renforcée (Q, 25 %) pour poser un logo au centre sans gêner la lecture
        $svg = (new \BaconQrCode\Writer($renderer))->writeString($url, 'UTF-8',
            $withLogo ? \BaconQrCode\Common\ErrorCorrectionLevel::Q() : \BaconQrCode\Common\ErrorCorrectionLevel::L());

        // Retire la déclaration XML pour pouvoir l'insérer directement dans la page
        return trim(preg_replace('/^<\?xml[^>]*\?>/', '', $svg));
    }
}
