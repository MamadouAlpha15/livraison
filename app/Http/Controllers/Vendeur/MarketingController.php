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


    public function index(Request $request): View
    {
        $shop = $this->shop();
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
                ->with('error', 'Vous avez déjà ' . MarketingCampaign::MAX_ACTIVE . ' campagnes. Archivez-en une pour en créer une nouvelle.');
        }

        $data = $request->validate([
            'name'          => ['required', 'string', 'min:2', 'max:60'],
            'channel'       => ['required', Rule::in(TrafficSource::LINK_CHANNELS)],
            'promo_code_id' => ['nullable', 'integer',
                Rule::exists('promo_codes', 'id')->where('shop_id', $shop->id),
                // Un code promo ne sert qu'à une campagne active, sinon ses ventes seraient comptées deux fois
                Rule::unique('marketing_campaigns', 'promo_code_id')->where('shop_id', $shop->id)->whereNull('archived_at')],
        ], [
            'name.required'        => 'Donnez un nom à la campagne (ex. « Influenceuse Awa »).',
            'name.min'             => 'Le nom de la campagne est trop court.',
            'name.max'             => 'Le nom de la campagne ne peut pas dépasser 60 caractères.',
            'channel.in'           => 'Choisissez où vous allez partager le lien.',
            'promo_code_id.exists' => "Ce code promo n'appartient pas à votre boutique.",
            'promo_code_id.unique' => 'Ce code promo est déjà lié à une autre campagne.',
        ]);

        MarketingCampaign::create([
            'shop_id'       => $shop->id,
            'name'          => trim($data['name']),
            'slug'          => MarketingCampaign::uniqueSlug($shop->id, $data['name']),
            'channel'       => $data['channel'],
            'promo_code_id' => $data['promo_code_id'] ?? null,
        ]);

        return redirect()->to(route('boutique.marketing') . '#campagnes')->with('success', 'Campagne créée. Copiez son lien et partagez-le.');
    }

    /** Archive la campagne : son lien mène toujours à la boutique, mais n'est plus compté à part. */
    public function archiveCampaign(MarketingCampaign $campaign): RedirectResponse
    {
        abort_unless((int) $campaign->shop_id === (int) $this->shop()->id, 403);
        $campaign->update(['archived_at' => now()]);

        return redirect()->to(route('boutique.marketing') . '#campagnes')->with('success', 'Campagne « ' . $campaign->name . ' » archivée.');
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
