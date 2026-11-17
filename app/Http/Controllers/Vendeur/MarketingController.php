<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\CatalogFeedController;
use App\Http\Controllers\Concerns\ResolvesOwnShop;
use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductView;
use App\Models\Shop;
use App\Models\ShopTraffic;
use App\Services\ImageOptimizer;
use App\Support\MetaPixel;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Page « Marketing » du vendeur :
 *  - d'où viennent ses visiteurs et ses commandes (Facebook, Instagram, WhatsApp, QR…) ;
 *  - liens de suivi à partager sur chaque réseau ;
 *  - campagnes nommées (lien + code promo éventuel) et leurs résultats ;
 *  - statistiques par produit (vues, commandes, fiches à revoir) ;
 *  - catalogue produits pour Facebook / Instagram ;
 *  - pixel Meta : état et mode test.
 * Agit toujours sur la boutique de l'utilisateur connecté.
 */
class MarketingController extends Controller
{
    use ResolvesOwnShop;

    public const PERIODS = [7, 30, 90];

    /** Vues minimum avant de signaler une fiche « à revoir ». */
    public const REVIEW_MIN_VIEWS = 15;


    /** Onglets : statistiques, partage (ex-« Boutique → Partage »), qualité de la vitrine (ex-« Boutique → Qualité »). */
    public const TABS = ['statistiques' => 'Statistiques', 'partage' => 'Partage', 'qualite' => 'Qualité de la vitrine'];

    public function index(Request $request): View
    {
        $shop = $this->shop();
        $tab = isset(self::TABS[$request->query('onglet')]) ? $request->query('onglet') : 'statistiques';
        if ($tab === 'partage') return $this->sharing($shop);
        if ($tab === 'qualite') return $this->quality($shop);
        $days = in_array($request->integer('jours'), self::PERIODS, true) ? $request->integer('jours') : 30;
        $from = now()->subDays($days - 1)->startOfDay();

        // Visites par source et par jour
        $traffic = ShopTraffic::where('shop_id', $shop->id)->where('day', '>=', $from->toDateString())
            ->get(['day', 'source', 'visits']);
        $visitsBySource = $traffic->groupBy('source')->map->sum('visits');
        $visitsByDay = $traffic->groupBy(fn ($t) => $t->day->toDateString())->map->sum('visits');

        // Commandes par source (hors annulées pour le chiffre d'affaires)
        $orders = Order::where('shop_id', $shop->id)->where('created_at', '>=', $from)
            ->selectRaw("COALESCE(source, 'inconnu') as src, COUNT(*) as n, SUM(CASE WHEN status <> ? THEN total ELSE 0 END) as revenue", [Order::STATUS_ANNULEE])
            ->groupBy('src')->get()->keyBy('src');

        $rows = collect(array_keys(TrafficSource::LABELS))
            ->map(fn ($src) => [
                'source'  => $src,
                'label'   => TrafficSource::label($src),
                'visits'  => (int) ($visitsBySource[$src] ?? 0),
                'orders'  => (int) ($orders[$src]->n ?? 0),
                'revenue' => (float) ($orders[$src]->revenue ?? 0),
            ])
            ->filter(fn ($r) => $r['visits'] > 0 || $r['orders'] > 0)
            ->sortByDesc(fn ($r) => [$r['orders'], $r['visits']])
            ->values();

        $unknown = $orders['inconnu'] ?? null;

        $series = collect(range($days - 1, 0))->map(function ($ago) use ($visitsByDay) {
            $d = now()->subDays($ago);
            return ['date' => $d->toDateString(), 'label' => $d->translatedFormat('j M'), 'visits' => (int) ($visitsByDay[$d->toDateString()] ?? 0)];
        });

        $publicUrl = Storefront::publicUrl($shop);
        $links = collect(TrafficSource::LINK_CHANNELS)->mapWithKeys(fn ($c) => [$c => TrafficSource::tag($publicUrl, $c, $c === 'qr' ? 'print' : 'social')]);

        $products = $shop->products()->where('is_active', true)->latest()->get(['id', 'name'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'url' => route('client.orders.createFromProduct', $p)]);

        $catalogCount = CatalogFeedController::query($shop)->count();

        return view('vendeur.shop.marketing', [
            'shop'          => $shop,
            'tab'           => 'statistiques',
            'days'          => $days,
            'rows'          => $rows,
            'totals'        => [
                'visits'  => (int) $visitsBySource->sum(),
                'orders'  => (int) $orders->sum('n'),
                'revenue' => (float) $orders->sum('revenue'),
            ],
            'unknownOrders' => (int) ($unknown->n ?? 0),
            'series'        => $series,
            'publicUrl'     => $publicUrl,
            'links'         => $links,
            'products'      => $products,
            'categoryLinks' => Storefront::categoryLinks($shop),
            'catalogUrl'    => route('public.shops.catalog', $shop),
            'catalogCount'  => $catalogCount,
            'withoutPhoto'  => max(0, $products->count() - $catalogCount),
            'pixelTest'     => MetaPixel::test(),
            'qrSvg'         => StorefrontController::qrSvg($links['qr'], 160),
            'campaigns'     => $this->campaigns($shop),
            'promoCodes'    => \App\Models\PromoCode::where('shop_id', $shop->id)->where('is_active', true)->orderBy('code')->get(['id', 'code']),
            'productStats'  => $this->productStats($shop, $from),
        ]);
    }

    // ─────────────────────────────────────────────────────────── Onglet « Partage »

    private function money(Shop $shop, $v): string
    {
        return number_format((float) $v, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF');
    }

    /** Lien d'un produit, QR code et affiche, liens par réseau (30 jours), liens de campagne avec remise. */
    private function sharing(Shop $shop): View
    {
        $publicUrl = Storefront::publicUrl($shop);
        $products = $shop->products()->where('is_active', true)->orderByDesc('is_featured')->latest()
            ->get(['id', 'name', 'image', 'price', 'flash_price', 'flash_starts_at', 'flash_ends_at'])
            ->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name, 'priceLabel' => $this->money($shop, $p->current_price),
                'thumb' => $p->image ? (ImageOptimizer::url($p->image, 'thumb') ?? asset('storage/' . $p->image)) : null,
                'photo' => $p->image ? (ImageOptimizer::url($p->image, 'medium') ?? asset('storage/' . $p->image)) : null,
                'url' => route('client.orders.createFromProduct', $p),
            ])->values();
        $categoryLinks = Storefront::categoryLinks($shop);
        return view('vendeur.shop.marketing', [
            'shop' => $shop, 'tab' => 'partage', 'publicUrl' => $publicUrl, 'host' => preg_replace('#^https?://#', '', $publicUrl),
            'products' => $products, 'categoryLinks' => $categoryLinks, 'tracked' => $this->tracked($shop),
            'promoLinks' => $this->promoLinks($shop, $categoryLinks), 'isPro' => app(\App\Services\SubscriptionService::class)->shopPlan($shop) === 'pro',
            'logoUrl' => \App\Support\Growth::logoUrl($shop), 'initials' => \App\Support\Growth::initials($shop->name),
        ]);
    }

    /** Liens par réseau : visites et commandes des 30 derniers jours, chiffres réels. */
    private function tracked(Shop $shop): array
    {
        $from = now()->subDays(29)->startOfDay();
        $visits = ShopTraffic::where('shop_id', $shop->id)->where('day', '>=', $from->toDateString())
            ->selectRaw('source, SUM(visits) as v')->groupBy('source')->pluck('v', 'source');
        $orders = Order::where('shop_id', $shop->id)->where('created_at', '>=', $from)->whereNotNull('source')
            ->selectRaw('source, COUNT(*) as n')->groupBy('source')->pluck('n', 'source');
        return collect(TrafficSource::LINK_CHANNELS)->map(fn ($src) => [
            'source' => $src,
            'label'  => TrafficSource::label($src),
            'url'    => TrafficSource::tag(Storefront::publicUrl($shop), $src, $src === 'qr' ? 'print' : 'social'),
            'visits' => (int) ($visits[$src] ?? 0),
            'orders' => (int) ($orders[$src] ?? 0),
        ])->values()->all();
    }

    /** Liens de campagne avec remise (un vrai code promo, une date de fin). */
    private function promoLinks(Shop $shop, array $categoryLinks): array
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

    /** Lien de campagne : remise sur une catégorie (ou toute la boutique), avec un vrai code promo et une date de fin. */
    public function createPromoLink(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $back = fn () => redirect()->route('boutique.marketing', ['onglet' => 'partage']);
        if (app(\App\Services\SubscriptionService::class)->shopPlan($shop) !== 'pro') {
            return $back()->withErrors(['campaign' => 'Les liens de campagne avec remise utilisent les codes promo, réservés au plan Pro.']);
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
        $ends = \Carbon\Carbon::createFromFormat('Y-m-d', $data['ends'], 'Africa/Conakry')->endOfDay();
        if ($ends->isPast()) {
            return $back()->withErrors(['ends' => 'La date de fin est déjà passée : choisissez aujourd’hui ou plus tard.'])->withInput();
        }
        if (MarketingCampaign::where('shop_id', $shop->id)->active()->count() >= MarketingCampaign::MAX_ACTIVE) {
            return $back()->withErrors(['campaign' => 'Vous avez déjà ' . MarketingCampaign::MAX_ACTIVE . ' campagnes en cours : arrêtez-en une d’abord.']);
        }

        $cat = !empty($data['category']) ? collect($cats)->firstWhere('slug', $data['category']) : null;
        $base = strtoupper(preg_replace('/[^A-Za-z]/', '', \Illuminate\Support\Str::ascii(\Illuminate\Support\Str::before($cat['name'] ?? $shop->name, ' '))));
        $base = substr($base !== '' ? $base : 'PROMO', 0, 10) . $data['discount'];
        $code = $base;
        for ($i = 2; \App\Models\PromoCode::where('shop_id', $shop->id)->where('code', $code)->exists(); $i++) $code = $base . '-' . $i;

        $promo = \App\Models\PromoCode::create([
            'shop_id' => $shop->id, 'code' => $code, 'type' => 'percent', 'value' => $data['discount'],
            'expires_at' => $ends->copy()->utc(), 'is_active' => true,
        ]);
        MarketingCampaign::create([
            'shop_id' => $shop->id, 'name' => ($cat['name'] ?? 'Toute la boutique') . ' −' . $data['discount'] . ' %',
            'slug' => MarketingCampaign::uniqueSlug($shop->id, strtolower($code)),
            'channel' => 'whatsapp', 'landing' => $cat['slug'] ?? null, 'promo_code_id' => $promo->id,
        ]);
        return $back()->with('success', 'Lien de campagne créé avec le code ' . $code . ' (valable jusqu’au ' . $ends->format('d/m/Y') . ').');
    }

    public function stopPromoLink(MarketingCampaign $campaign): RedirectResponse
    {
        $shop = $this->shop();
        abort_unless($campaign->shop_id === $shop->id, 404);
        $campaign->update(['archived_at' => now()]);
        $campaign->promoCode?->update(['is_active' => false]);
        return redirect()->route('boutique.marketing', ['onglet' => 'partage'])
            ->with('success', 'Campagne arrêtée : le code ' . ($campaign->promoCode->code ?? '') . ' ne fonctionne plus.');
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

    /** QR code d'une cible (affiché dans la page, ou téléchargé en PNG). */
    public function qr(Request $request)
    {
        $shop = $this->shop();
        [$url] = $this->target($shop, $request);
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
            'logoUrl' => \App\Support\Growth::logoUrl($shop), 'initials' => \App\Support\Growth::initials($shop->name), 'accent' => \App\Support\Growth::accent($shop),
        ]);
    }

    // ─────────────────────────────────────────────────────────── Onglet « Qualité de la vitrine »

    /** Visites, classement des catégories, vus jamais commandés, score de vitrine, avis reçus sur l'aperçu. */
    private function quality(Shop $shop): View
    {
        $q = \App\Support\Growth::quality($shop, Storefront::forShop($shop));
        return view('vendeur.shop.marketing', [
            'shop' => $shop, 'tab' => 'qualite', 'publicUrl' => Storefront::publicUrl($shop),
            'visits' => \App\Support\Growth::visits($shop), 'categoryRanking' => \App\Support\Growth::categoryRanking($shop),
            'seenNeverOrdered' => \App\Support\Growth::seenNeverOrdered($shop), 'quality' => $q,
            'previewUrl' => \App\Support\Growth::previewActive($shop) ? route('storefront.preview-link', $shop->preview_token) : null,
            'feedback' => collect($shop->preview_feedback ?? [])->sortByDesc('at')->values(),
        ]);
    }

    /** Campagnes actives avec leurs résultats depuis leur création. */
    private function campaigns(Shop $shop)
    {
        $campaigns = MarketingCampaign::where('shop_id', $shop->id)->active()->with('promoCode:id,code')->latest()->get();
        if ($campaigns->isEmpty()) return collect();

        $visits = ShopTraffic::where('shop_id', $shop->id)->whereIn('campaign_id', $campaigns->pluck('id'))
            ->selectRaw('campaign_id, SUM(visits) as v')->groupBy('campaign_id')->pluck('v', 'campaign_id');

        $publicUrl = Storefront::publicUrl($shop);

        return $campaigns->map(function (MarketingCampaign $c) use ($shop, $visits, $publicUrl) {
            // Commandes arrivées par le lien, ou passées avec le code promo de la campagne
            // (un client peut taper le code entendu dans une vidéo sans cliquer sur le lien).
            $orders = Order::where('shop_id', $shop->id)
                ->where('created_at', '>=', $c->created_at)
                ->where(function ($q) use ($c) {
                    $q->where('campaign_id', $c->id);
                    if ($c->promo_code_id) $q->orWhere('promo_code_id', $c->promo_code_id);
                })
                ->selectRaw('COUNT(*) as n, SUM(CASE WHEN status <> ? THEN total ELSE 0 END) as revenue', [Order::STATUS_ANNULEE])
                ->first();

            return [
                'id'      => $c->id,
                'name'    => $c->name,
                'channel' => $c->channel,
                'promo'   => $c->promoCode?->code,
                'since'   => $c->created_at,
                'url'     => TrafficSource::tag($publicUrl, $c->channel, $c->channel === 'qr' ? 'print' : 'social', $c->slug),
                'visits'  => (int) ($visits[$c->id] ?? 0),
                'orders'  => (int) ($orders->n ?? 0),
                'revenue' => (float) ($orders->revenue ?? 0),
            ];
        });
    }

    /**
     * Produits de la période : vues de la fiche, commandes et chiffre d'affaires.
     * « À revoir » : beaucoup vu mais peu commandé (souvent le prix, la photo ou la description).
     */
    private function productStats(Shop $shop, $from)
    {
        $views = ProductView::where('shop_id', $shop->id)->where('day', '>=', $from->toDateString())
            ->selectRaw('product_id, SUM(views) as v')->groupBy('product_id')->pluck('v', 'product_id');

        $sales = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.shop_id', $shop->id)
            ->where('orders.created_at', '>=', $from)
            ->where('orders.status', '<>', Order::STATUS_ANNULEE)
            ->selectRaw('order_items.product_id, COUNT(DISTINCT orders.id) as n, SUM(order_items.quantity) as qty, SUM(order_items.quantity * order_items.price) as revenue')
            ->groupBy('order_items.product_id')->get()->keyBy('product_id');

        $ids = $views->keys()->merge($sales->keys())->unique();
        if ($ids->isEmpty()) return collect();

        $products = $shop->products()->whereIn('id', $ids)->get(['id', 'name', 'image'])->keyBy('id');

        $rows = $ids->map(function ($id) use ($views, $sales, $products) {
            $p = $products[$id] ?? null;
            if (!$p) return null;
            $v = (int) ($views[$id] ?? 0);
            $n = (int) ($sales[$id]->n ?? 0);
            return [
                'id'      => $p->id,
                'name'    => $p->name,
                'thumb'   => $p->image ? ImageOptimizer::url($p->image, 'thumb') : null,
                'views'   => $v,
                'orders'  => $n,
                'qty'     => (int) ($sales[$id]->qty ?? 0),
                'revenue' => (float) ($sales[$id]->revenue ?? 0),
                'rate'    => $v > 0 ? round(min($n, $v) / $v * 100, 1) : null,
            ];
        })->filter()->values();

        // Taux moyen de la boutique, pour repérer les fiches nettement en dessous
        $totalViews = $rows->sum('views');
        $avgRate = $totalViews > 0 ? $rows->sum(fn ($r) => min($r['orders'], $r['views'])) / $totalViews * 100 : 0;

        return $rows->map(function ($r) use ($avgRate) {
            $r['review'] = $r['views'] >= self::REVIEW_MIN_VIEWS && ($r['orders'] === 0 || $r['rate'] < $avgRate / 2);
            return $r;
        })->sortByDesc(fn ($r) => [$r['views'], $r['orders']])->values()->take(15);
    }

    public function storeCampaign(Request $request): RedirectResponse
    {
        $shop = $this->shop();

        if (MarketingCampaign::where('shop_id', $shop->id)->active()->count() >= MarketingCampaign::MAX_ACTIVE) {
            return redirect()->to(route('boutique.marketing') . '#campagnes')
                ->with('error', 'Vous avez déjà ' . MarketingCampaign::MAX_ACTIVE . ' liens de suivi. Archivez-en un pour en créer un nouveau.');
        }

        $data = $request->validate([
            'name'          => ['required', 'string', 'min:2', 'max:60'],
            'channel'       => ['required', Rule::in(TrafficSource::LINK_CHANNELS)],
            'promo_code_id' => ['nullable', 'integer',
                Rule::exists('promo_codes', 'id')->where('shop_id', $shop->id),
                // Un code promo ne sert qu'à une campagne active, sinon ses ventes seraient comptées deux fois
                Rule::unique('marketing_campaigns', 'promo_code_id')->where('shop_id', $shop->id)->whereNull('archived_at')],
        ], [
            'name.required'        => 'Donnez un nom au lien (ex. « Influenceuse Awa »).',
            'name.min'             => 'Le nom du lien est trop court.',
            'name.max'             => 'Le nom du lien ne peut pas dépasser 60 caractères.',
            'channel.in'           => 'Choisissez où vous allez partager le lien.',
            'promo_code_id.exists' => "Ce code promo n'appartient pas à votre boutique.",
            'promo_code_id.unique' => 'Ce code promo est déjà lié à un autre lien.',
        ]);

        MarketingCampaign::create([
            'shop_id'       => $shop->id,
            'name'          => trim($data['name']),
            'slug'          => MarketingCampaign::uniqueSlug($shop->id, $data['name']),
            'channel'       => $data['channel'],
            'promo_code_id' => $data['promo_code_id'] ?? null,
        ]);

        return redirect()->to(route('boutique.marketing') . '#campagnes')->with('success', 'Lien de suivi créé. Copiez-le et partagez-le.');
    }

    /** Archive la campagne : son lien mène toujours à la boutique, mais n'est plus compté à part. */
    public function archiveCampaign(MarketingCampaign $campaign): RedirectResponse
    {
        abort_unless((int) $campaign->shop_id === (int) $this->shop()->id, 403);
        $campaign->update(['archived_at' => now()]);

        return redirect()->to(route('boutique.marketing') . '#campagnes')->with('success', 'Lien « ' . $campaign->name . ' » archivé.');
    }

    /** Active le pixel pour le vendeur pendant 30 minutes et ouvre sa vitrine. */
    public function startPixelTest(): RedirectResponse
    {
        $shop = $this->shop();
        if (!$shop->meta_pixel_id) {
            return redirect()->route('boutique.marketing')->with('error', "Ajoutez d'abord l'identifiant de votre pixel dans Ma boutique → Concevoir.");
        }
        MetaPixel::startTest($shop);
        return redirect()->route('public.shops.products', $shop);
    }

    public function stopPixelTest(): RedirectResponse
    {
        MetaPixel::stopTest();
        return redirect()->route('boutique.marketing')->withFragment('pixel')->with('success', 'Mode test du pixel terminé.');
    }
}
