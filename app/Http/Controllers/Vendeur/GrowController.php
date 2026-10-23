<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Concerns\ResolvesOwnShop;
use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopTraffic;
use App\Services\ImageOptimizer;
use App\Services\SubscriptionService;
use App\Support\Growth;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * « Faire grandir ma vitrine » : 5 onglets.
 *  1. Couverture et logo — enregistrés et publiés tout de suite.
 *  2. Partage — liens et QR codes (boutique, catégorie, produit), affiche A5, liens suivis, lien de campagne.
 *  3. Personnalisation — modèle, diapos de la couverture, collections ; publier, garder en brouillon ou programmer.
 *  4. Conversion — preuves de confiance, mode WhatsApp, sections automatiques.
 *  5. Qualité — visites, clics par section, catégories, score de vitrine, lien d'aperçu 48 h.
 * Agit toujours sur la boutique de l'utilisateur connecté.
 */
class GrowController extends Controller
{
    use ResolvesOwnShop;

    public const TABS = ['couverture', 'partage', 'personnalisation', 'conversion', 'qualite'];


    private function isPro(Shop $shop): bool
    {
        return app(SubscriptionService::class)->shopPlan($shop) === 'pro';
    }

    private function back(string $tab): RedirectResponse
    {
        return redirect()->route('boutique.grow', ['onglet' => $tab]);
    }

    private function money(Shop $shop, $v): string
    {
        return number_format((float) $v, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF');
    }

    public function index(Request $request): View
    {
        $shop = $this->shop();
        Growth::applyScheduled($shop);
        $shop->refresh();

        $tab = in_array($request->query('onglet'), self::TABS, true) ? $request->query('onglet') : 'couverture';
        $published = Storefront::forShop($shop);
        [$working, $workingAccent] = Growth::working($shop);
        $publicUrl = Storefront::publicUrl($shop);

        $products = $shop->products()->where('is_active', true)->orderByDesc('is_featured')->latest()
            ->get(['id', 'name', 'image', 'price', 'category', 'description', 'stock', 'flash_price', 'flash_starts_at', 'flash_ends_at', 'is_featured', 'created_at'])
            ->map(fn ($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'cat'   => (string) $p->category,
                'price' => (int) $p->current_price,
                'priceLabel' => $this->money($shop, $p->current_price),
                'thumb' => $p->image ? (ImageOptimizer::url($p->image, 'thumb') ?? asset('storage/' . $p->image)) : null,
                'photo' => $p->image ? (ImageOptimizer::url($p->image, 'medium') ?? asset('storage/' . $p->image)) : null,
                'url'   => route('client.orders.createFromProduct', $p),
                'desc'  => Str::limit(trim(strip_tags((string) $p->description)), 220),
                'stock' => $p->stock,
                'new'   => $p->created_at && $p->created_at->gte(now()->subDays(7)),
            ])->values();

        $reviewStats = Review::where('vendeur_id', $shop->user_id)->selectRaw('COUNT(*) as n, AVG(rating) as avg')->first();
        $reviewCount = (int) ($reviewStats->n ?? 0);
        $delivered = Order::where('shop_id', $shop->id)->where('status', Order::STATUS_LIVREE)->count();
        $categoryLinks = Storefront::categoryLinks($shop);

        // Photos déjà importées pour la couverture (miniatures des diapos)
        $coverUrls = collect($working['cover']['slides'])->merge($published['cover']['slides'])
            ->filter(fn ($s) => $s['src'] === 'photo' && $s['img'])
            ->mapWithKeys(fn ($s) => [$s['img'] => ImageOptimizer::url(str_replace('/medium/', '/large/', $s['img']), 'medium')])->all();

        return view('vendeur.shop.grow', [
            'shop'        => $shop,
            'tab'         => $tab,
            'published'   => $published,
            'working'     => $working,
            'accent'      => Growth::accent($shop),
            'workingAccent' => $workingAccent,
            'status'      => Growth::status($shop),
            'publicUrl'   => $publicUrl,
            'products'    => $products,
            'productPhoto' => Growth::productPhoto($shop),
            'logoUrl'     => Growth::logoUrl($shop),
            'initials'    => Growth::initials($shop->name),
            'coverUrls'   => $coverUrls,
            'reviewCount' => $reviewCount,
            'reviewAvg'   => $reviewCount ? round((float) $reviewStats->avg, 1) : null,
            'delivered'   => $delivered,
            'responseTime' => Growth::responseTime($shop),
            'categoryLinks' => $categoryLinks,
            'tracked'     => $this->tracked($shop),
            'campaigns'   => $this->campaignLinks($shop, $categoryLinks),
            'isPro'       => $this->isPro($shop),
            'collections' => collect($working['collections'])->map(fn ($c) => $c + [
                'url'   => Growth::collectionUrl($shop, $c),
                'count' => Growth::collectionProducts($shop, $c, null)->count(),
                'thumbs' => Growth::collectionProducts($shop, $c, 3)->map(fn ($p) => $p->image ? ImageOptimizer::url($p->image, 'thumb') : null)->values(),
            ])->values(),
            'trendingCount' => Growth::trending($shop)->count(),
            'lowStockCount' => Growth::lowStock($shop)->count(),
            'visits'      => Growth::visits($shop),
            'sectionClicks' => Growth::sectionClicks($shop),
            'categoryRanking' => Growth::categoryRanking($shop),
            'seenNeverOrdered' => Growth::seenNeverOrdered($shop),
            'quality'     => Growth::quality($shop, $published),
            'previewUrl'  => Growth::previewActive($shop) ? route('storefront.preview-link', $shop->preview_token) : null,
            'feedback'    => collect($shop->preview_feedback ?? [])->sortByDesc('at')->values(),
        ]);
    }

    /** Liens suivis (30 jours) : visites et commandes par provenance, chiffres réels. */
    private function tracked(Shop $shop): array
    {
        $from = now()->subDays(29)->startOfDay();
        $visits = ShopTraffic::where('shop_id', $shop->id)->where('day', '>=', $from->toDateString())
            ->selectRaw('source, SUM(visits) as v')->groupBy('source')->pluck('v', 'source');
        $orders = Order::where('shop_id', $shop->id)->where('created_at', '>=', $from)->whereNotNull('source')
            ->selectRaw('source, COUNT(*) as n')->groupBy('source')->pluck('n', 'source');
        $links = collect(TrafficSource::LINK_CHANNELS);
        return $links->map(fn ($src) => [
            'source' => $src,
            'label'  => TrafficSource::label($src),
            'url'    => TrafficSource::tag(Storefront::publicUrl($shop), $src, $src === 'qr' ? 'print' : 'social'),
            'visits' => (int) ($visits[$src] ?? 0),
            'orders' => (int) ($orders[$src] ?? 0),
        ])->values()->all();
    }

    /** Liens de campagne créés ici (une remise, une date de fin, un code promo). */
    private function campaignLinks(Shop $shop, array $categoryLinks): array
    {
        return MarketingCampaign::where('shop_id', $shop->id)->active()->whereNotNull('promo_code_id')
            ->with('promoCode')->latest()->take(10)->get()
            ->filter(fn ($c) => $c->promoCode)
            ->map(function ($c) use ($shop, $categoryLinks) {
                $cat = $c->landing ? collect($categoryLinks)->firstWhere('slug', $c->landing) : null;
                $p = $c->promoCode;
                $expired = $p->expires_at && $p->expires_at->isPast();
                return [
                    'id'      => $c->id,
                    'code'    => $p->code,
                    'value'   => $p->type === 'percent' ? $p->value . ' %' : number_format($p->value, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF'),
                    'target'  => $cat['name'] ?? 'Toute la boutique',
                    'url'     => TrafficSource::tag($cat['url'] ?? Storefront::publicUrl($shop), $c->channel, 'social', $c->slug),
                    'expires' => $p->expires_at?->timezone('Africa/Conakry')->format('d/m/Y'),
                    'state'   => !$p->is_active ? 'Arrêtée' : ($expired ? 'Terminée' : 'Active'),
                    'uses'    => (int) $p->uses_count,
                ];
            })->values()->all();
    }

    // ─────────────────────────────────────────────────────────── 1. Couverture et logo

    /** Photo de couverture : WebP en 800 et 1600 px. La largeur d'origine sert à prévenir d'un flou. */
    public function uploadCover(Request $request): JsonResponse
    {
        $this->shop();
        $request->validate(['file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']], [
            'file.required' => 'Choisissez une image.',
            'file.image'    => 'Ce fichier n’est pas une image.',
            'file.mimes'    => 'Formats acceptés : JPG, PNG ou WebP.',
            'file.max'      => 'Image trop lourde : 5 Mo maximum.',
        ]);
        $width = @getimagesize($request->file('file')->getRealPath())[0] ?? null;
        $path = ImageOptimizer::store($request->file('file'), 'covers', true);
        return response()->json(['path' => $path, 'url' => ImageOptimizer::url(str_replace('/medium/', '/large/', $path), 'medium'), 'width' => $width]);
    }

    /** Logo : remplace le logo de la boutique à l'enregistrement de l'onglet. */
    public function uploadLogo(Request $request): JsonResponse
    {
        $this->shop();
        $request->validate(['file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']], [
            'file.required' => 'Choisissez une image.',
            'file.image'    => 'Ce fichier n’est pas une image.',
            'file.mimes'    => 'Formats acceptés : PNG, JPG ou WebP.',
            'file.max'      => 'Logo trop lourd : 2 Mo maximum.',
        ]);
        $path = ImageOptimizer::store($request->file('file'), 'shops');
        $request->session()->put('grow_logo_upload', $path); // seul un logo importé par ce vendeur peut être enregistré
        return response()->json(['path' => $path, 'url' => ImageOptimizer::url($path, 'thumb')]);
    }

    public function saveIdentity(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        // 30 caractères pour un nouveau nom ; un nom existant plus long reste accepté tel quel (jamais tronqué)
        $nameMax = max(30, mb_strlen((string) $shop->name));
        $data = $request->validate([
            'name'        => ['required', 'string', 'min:2', 'max:' . $nameMax, fn ($a, $v, $fail) => mb_strlen($v) > 30 && trim($v) !== $shop->name ? $fail('Le nom ne peut pas dépasser 30 caractères.') : null],
            'slogan'      => ['nullable', 'string', 'max:60'],
            'color'       => ['required', Rule::in(array_keys(Storefront::IDENTITY_COLORS + [Growth::accent($shop) => '']))],
            'cover_src'   => ['required', Rule::in(array_keys(Storefront::COVER_SOURCES))],
            'cover_img'   => ['nullable', 'string', 'regex:' . Storefront::COVER_PATH],
            'cover_y'     => ['required', 'integer', 'between:0,100'],
            'cover_veil'  => ['required', 'integer', 'between:0,70'],
            'cover_height' => ['required', Rule::in(array_keys(Storefront::COVER_HEIGHTS))],
            'name_on'     => ['nullable', 'boolean'],
            'logo_src'    => ['required', Rule::in(['current', 'initials', 'upload'])],
            'logo_img'    => ['nullable', 'string'],
            'logo_shape'  => ['required', Rule::in(array_keys(Storefront::LOGO_SHAPES))],
            'logo_pos'    => ['required', Rule::in(array_keys(Storefront::LOGO_POSITIONS))],
            'logo_size'   => ['required', 'integer', 'between:64,140'],
        ], [
            'name.required' => 'Le nom de la boutique est obligatoire.',
            'name.min'      => 'Le nom est trop court (2 caractères minimum).',
            'name.max'      => 'Le nom ne peut pas dépasser 30 caractères.',
            'slogan.max'    => 'Le slogan ne peut pas dépasser 60 caractères.',
            'cover_img.regex' => 'Importez de nouveau votre photo de couverture.',
        ]);
        if ($data['cover_src'] === 'photo' && empty($data['cover_img'])) {
            return $this->back('couverture')->withErrors(['cover_img' => 'Importez une photo pour « Ma photo », ou choisissez une autre couverture.'])->withInput();
        }

        $extra = ['name' => trim($data['name'])];
        $logoSrc = $data['logo_src'];
        if ($logoSrc === 'upload') {
            $path = $request->session()->pull('grow_logo_upload');
            if (!$path || $path !== ($data['logo_img'] ?? null)) {
                return $this->back('couverture')->withErrors(['logo_img' => 'Importez de nouveau votre logo.'])->withInput();
            }
            ImageOptimizer::delete($shop->image);
            $extra['image'] = $path;
            $logoSrc = 'current';
        }
        if ($logoSrc === 'current' && !$shop->image && !isset($extra['image'])) $logoSrc = 'initials';

        $slide = ['src' => $data['cover_src'], 'img' => $data['cover_src'] === 'photo' ? $data['cover_img'] : null, 'pid' => null];
        Growth::applyEverywhere($shop, function (array $c) use ($data, $slide, $logoSrc) {
            $slides = $c['cover']['slides'];
            $slides[0] = $slide; // la couverture = la première diapo
            $c['cover'] = ['on' => true, 'slides' => $slides, 'y' => $data['cover_y'], 'veil' => $data['cover_veil'], 'height' => $data['cover_height'],
                'name_on' => (bool) ($data['name_on'] ?? false), 'slogan' => (string) ($data['slogan'] ?? '')];
            $c['logo'] = ['src' => $logoSrc, 'shape' => $data['logo_shape'], 'pos' => $data['logo_pos'], 'size' => $data['logo_size']];
            return $c;
        }, strtoupper($data['color']), $extra);

        // Le brouillon éventuel garde lui aussi la nouvelle couverture et le nouveau logo
        $fresh = $shop->fresh();
        $pub = Storefront::forShop($fresh);
        $this->syncDraft($fresh, fn ($c) => array_merge($c, ['cover' => $pub['cover'], 'logo' => $pub['logo']]));

        return $this->back('couverture')->with('success', 'Couverture et logo publiés. Vos clients les voient maintenant.');
    }

    /** Recopie une partie de la version publiée dans le brouillon en attente. */
    private function syncDraft(Shop $shop, callable $change): void
    {
        $draft = $shop->storefront_draft;
        if (!is_array($draft) || !isset($draft['config'])) return;
        $draft['config'] = Storefront::normalize($change(Storefront::normalize($draft['config'])));
        $shop->update(['storefront_draft' => $draft]);
    }

    // ─────────────────────────────────────────────────────────── 2. Partage

    /** Lien de campagne : remise sur une catégorie (ou toute la boutique), avec un vrai code promo et une date de fin. */
    public function createCampaign(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        if (!$this->isPro($shop)) {
            return $this->back('partage')->withErrors(['campaign' => 'Les liens de campagne avec remise utilisent les codes promo, réservés au plan Pro.']);
        }
        $cats = Storefront::categoryLinks($shop);
        $data = $request->validate([
            'category' => ['nullable', Rule::in(array_column($cats, 'slug'))],
            'discount' => ['required', 'integer', 'between:1,90'],
            'ends'     => ['required', 'date_format:Y-m-d'],
        ], [
            'category.in'       => 'Cette catégorie n’existe plus dans la boutique.',
            'discount.required' => 'Indiquez la remise, de 1 à 90 %.',
            'discount.integer'  => 'La remise est un nombre entier, de 1 à 90 %.',
            'discount.between'  => 'La remise doit être comprise entre 1 et 90 %.',
            'ends.required'     => 'Choisissez la date de fin de la campagne.',
            'ends.date_format'  => 'Date de fin invalide.',
        ]);
        $ends = Carbon::createFromFormat('Y-m-d', $data['ends'], 'Africa/Conakry')->endOfDay();
        if ($ends->isPast()) {
            return $this->back('partage')->withErrors(['ends' => 'La date de fin est déjà passée : choisissez aujourd’hui ou plus tard.'])->withInput();
        }
        if (MarketingCampaign::where('shop_id', $shop->id)->active()->count() >= MarketingCampaign::MAX_ACTIVE) {
            return $this->back('partage')->withErrors(['campaign' => 'Vous avez déjà ' . MarketingCampaign::MAX_ACTIVE . ' campagnes en cours : arrêtez-en une d’abord.']);
        }

        $cat = $data['category'] ? collect($cats)->firstWhere('slug', $data['category']) : null;
        $base = strtoupper(preg_replace('/[^A-Za-z]/', '', Str::ascii(Str::before($cat['name'] ?? $shop->name, ' '))));
        $base = substr($base !== '' ? $base : 'PROMO', 0, 10) . $data['discount'];
        $code = $base;
        for ($i = 2; PromoCode::where('shop_id', $shop->id)->where('code', $code)->exists(); $i++) $code = $base . '-' . $i;

        $promo = PromoCode::create([
            'shop_id' => $shop->id, 'code' => $code, 'type' => 'percent', 'value' => $data['discount'],
            'expires_at' => $ends->copy()->utc(), 'is_active' => true,
        ]);
        $name = ($cat['name'] ?? 'Toute la boutique') . ' −' . $data['discount'] . ' %';
        MarketingCampaign::create([
            'shop_id' => $shop->id, 'name' => $name, 'slug' => MarketingCampaign::uniqueSlug($shop->id, strtolower($code)),
            'channel' => 'whatsapp', 'landing' => $cat['slug'] ?? null, 'promo_code_id' => $promo->id,
        ]);

        return $this->back('partage')->with('success', 'Lien de campagne créé avec le code ' . $code . ' (valable jusqu’au ' . $ends->format('d/m/Y') . ').');
    }

    public function stopCampaign(MarketingCampaign $campaign): RedirectResponse
    {
        $shop = $this->shop();
        abort_unless($campaign->shop_id === $shop->id, 404);
        $campaign->update(['archived_at' => now()]);
        $campaign->promoCode?->update(['is_active' => false]);
        return $this->back('partage')->with('success', 'Campagne arrêtée : le code ' . ($campaign->promoCode->code ?? '') . ' ne fonctionne plus.');
    }

    /** Lien et nom d'une cible de partage : boutique, catégorie ou produit. */
    private function target(Shop $shop, Request $request): array
    {
        if ($request->filled('produit')) {
            $p = $shop->products()->where('is_active', true)->findOrFail((int) $request->query('produit'));
            return [route('client.orders.createFromProduct', $p), $p->name, $this->money($shop, $p->current_price)];
        }
        if ($request->filled('categorie')) {
            $c = collect(Storefront::categoryLinks($shop))->firstWhere('slug', $request->query('categorie'));
            abort_unless($c, 404);
            return [$c['url'], $c['name'], $c['count'] . ' produit' . ($c['count'] > 1 ? 's' : '')];
        }
        return [Storefront::publicUrl($shop), $shop->name, null];
    }

    /** QR code d'une cible (affiché dans la page, ou téléchargé). */
    public function qr(Request $request)
    {
        $shop = $this->shop();
        [$url, $name] = $this->target($shop, $request);
        $svg = StorefrontController::qrSvg(TrafficSource::tag($url, 'qr', 'print'), 600, true);
        abort_unless($svg, 404);
        return response($svg, 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'private, max-age=600']);
    }

    /** Affiche A5 à imprimer : nom, QR code, lien. */
    public function poster(Request $request): View
    {
        $shop = $this->shop();
        [$url, $name, $sub] = $this->target($shop, $request);
        return view('vendeur.shop.poster', [
            'shop' => $shop, 'title' => $name, 'sub' => $sub, 'url' => $url,
            'qrSvg' => StorefrontController::qrSvg(TrafficSource::tag($url, 'qr', 'print'), 900, true),
            'logoUrl' => Growth::logoUrl($shop), 'initials' => Growth::initials($shop->name), 'accent' => Growth::accent($shop),
        ]);
    }

    // ─────────────────────────────────────────────────────────── 3. Personnalisation

    public function savePersonalize(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $payload = json_decode((string) $request->input('payload'), true);
        if (!is_array($payload)) return $this->back('personnalisation')->withErrors(['payload' => 'Réglages illisibles, rechargez la page.']);
        $mode = $request->input('mode');
        if (!in_array($mode, ['now', 'draft', 'schedule'], true)) $mode = 'draft';

        $at = null;
        if ($mode === 'schedule') {
            try { $at = Carbon::createFromFormat('Y-m-d\TH:i', (string) $request->input('publish_at'), 'Africa/Conakry'); } catch (\Throwable $e) { $at = null; }
            if (!$at) return $this->back('personnalisation')->withErrors(['publish_at' => 'Choisissez la date et l’heure de publication.'])->withInput();
            if ($at->lte(now()->addMinutes(4))) return $this->back('personnalisation')->withErrors(['publish_at' => 'Choisissez une heure dans au moins 5 minutes.'])->withInput();
            if ($at->gt(now()->addDays(60))) return $this->back('personnalisation')->withErrors(['publish_at' => 'Programmez dans les 60 prochains jours.'])->withInput();
        }

        [$config, $accent] = Growth::working($shop);
        // Modèle de départ : ordre des blocs de base et affichage des produits
        $tpl = $payload['template'] ?? '';
        if ($tpl !== $config['template'] && isset(Storefront::TEMPLATES[$tpl])) {
            $t = Storefront::TEMPLATES[$tpl];
            $slots = array_keys(array_filter($config['blocks'], fn ($b) => in_array($b['type'], Storefront::CORE, true)));
            $ordered = array_values(array_filter(array_map(fn ($k) => collect($config['blocks'])->firstWhere('type', $k), $t['order'])));
            foreach ($slots as $n => $slot) if (isset($ordered[$n])) $config['blocks'][$slot] = $ordered[$n];
            foreach ($config['blocks'] as &$b) {
                if (in_array($b['type'], Storefront::CORE, true) && !Storefront::BLOCKS[$b['type']][3]) $b['on'] = !in_array($b['type'], $t['off'], true);
            }
            unset($b);
            $config = array_merge($config, array_intersect_key($t, array_flip(['layout', 'columns', 'sort', 'cards', 'banner'])), ['template' => $tpl]);
            $accent = $t['accent'];
        }

        // Modèle de page côté client
        if (isset(\App\Support\PageModels::MODELS[$payload['page_model'] ?? ''])) $config['page_model'] = $payload['page_model'];

        // Diapos de la couverture (1 à 4) : produits et photos vérifiés
        $slides = Storefront::normalizeCover(['slides' => $payload['slides'] ?? []])['slides'];
        $own = $shop->products()->pluck('id')->all();
        foreach ($slides as &$s) if ($s['pid'] && !in_array($s['pid'], $own, true)) $s['pid'] = null;
        unset($s);
        $config['cover']['slides'] = $slides;
        if (count($slides) > 1 || $slides[0]['src'] !== 'default') $config['cover']['on'] = true;

        // Collections : uniquement des produits de la boutique ; le bloc « Collections » suit
        $cols = Storefront::normalizeCollections($payload['collections'] ?? []);
        foreach ($cols as &$c) $c['ids'] = array_values(array_intersect($c['ids'], $own));
        unset($c);
        $config['collections'] = $cols;
        $config = $this->ensureBlock($config, 'collections', collect($cols)->contains('on', true), 'categories');

        $config = Storefront::normalize($config);
        if ($mode === 'now') {
            Growth::publish($shop, $config, $accent);
            return $this->back('personnalisation')->with('success', 'Vitrine publiée. Vos clients voient maintenant ces changements.');
        }
        $shop->update(['storefront_draft' => ['config' => $config, 'accent' => $accent], 'storefront_publish_at' => $at?->copy()->utc()]);
        return $this->back('personnalisation')->with('success', $mode === 'schedule'
            ? 'Publication programmée le ' . $at->translatedFormat('j F à H:i') . ' (heure de Conakry).'
            : 'Brouillon enregistré : rien ne change pour vos clients tant que vous ne publiez pas.');
    }

    /** Supprime le brouillon en attente (et sa programmation). */
    public function discardDraft(): RedirectResponse
    {
        $shop = $this->shop();
        $shop->update(['storefront_draft' => null, 'storefront_publish_at' => null]);
        return $this->back('personnalisation')->with('success', 'Brouillon supprimé : la version publiée reste en ligne.');
    }

    /** Ajoute (affiché) ou masque un bloc géré ici. Ajouté après $after s'il n'existe pas encore. */
    private function ensureBlock(array $config, string $type, bool $on, string $after): array
    {
        foreach ($config['blocks'] as &$b) {
            if ($b['type'] === $type) { $b['on'] = $on; return $config; }
        }
        unset($b);
        if (!$on) return $config;
        $i = collect($config['blocks'])->search(fn ($b) => $b['type'] === $after);
        array_splice($config['blocks'], $i === false ? count($config['blocks']) : $i + 1, 0, [['id' => $type, 'type' => $type, 'on' => true, 'd' => Storefront::blockDefaults($type)]]);
        return $config;
    }

    /** Adopter un modèle de page (barre « Modèles de boutique Shopio » de la vitrine) : publié tout de suite. */
    public function useModel(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $model = $request->validate(['model' => ['required', Rule::in(array_keys(\App\Support\PageModels::MODELS))]], ['model.in' => 'Modèle inconnu.'])['model'];
        $apply = function (array $c) use ($model) { $c['page_model'] = $model; return $c; };
        Growth::applyEverywhere($shop, $apply);
        $this->syncDraft($shop->fresh(), $apply);
        return redirect()->to(Storefront::publicUrl($shop))->with('model_used', \App\Support\PageModels::MODELS[$model][0]);
    }

    // ─────────────────────────────────────────────────────────── 4. Conversion

    public function saveConversion(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $on = fn ($k) => $request->boolean($k);
        if ($on('wa_mode') && !$shop->phone) {
            return $this->back('conversion')->withErrors(['wa_mode' => 'Ajoutez d’abord le numéro WhatsApp de la boutique dans « Ma boutique ».']);
        }
        $apply = function (array $c) use ($on) {
            $c['settings']['wa_mode'] = $on('wa_mode');
            $c['settings']['proof_delivered'] = $on('proof_delivered');
            $c['settings']['proof_response'] = $on('proof_response');
            $c = $this->ensureBlock($c, 'trending', $on('trending'), 'featured');
            return $this->ensureBlock($c, 'lowstock', $on('lowstock'), 'trending');
        };
        Growth::applyEverywhere($shop, $apply);
        $this->syncDraft($shop->fresh(), $apply);
        return $this->back('conversion')->with('success', 'Réglages de conversion publiés.');
    }

    // ─────────────────────────────────────────────────────────── 5. Qualité : lien d'aperçu

    public function createPreview(): RedirectResponse
    {
        $shop = $this->shop();
        $shop->update(['preview_token' => Str::lower(Str::random(24)), 'preview_expires_at' => now()->addHours(Growth::PREVIEW_HOURS), 'preview_feedback' => []]);
        return $this->back('qualite')->with('success', 'Lien d’aperçu créé, valable ' . Growth::PREVIEW_HOURS . ' h.');
    }

    public function disablePreview(): RedirectResponse
    {
        $shop = $this->shop();
        $shop->update(['preview_token' => null, 'preview_expires_at' => null]);
        return $this->back('qualite')->with('success', 'Lien d’aperçu désactivé : il ne fonctionne plus.');
    }
}
