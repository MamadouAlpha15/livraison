<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Concerns\ResolvesOwnShop;
use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdCampaignPayment;
use App\Models\AdPackage;
use App\Models\Product;
use App\Support\Campaigns\CampaignCheckout;
use App\Support\Campaigns\CampaignInbox;
use App\Support\Campaigns\CampaignInvoice;
use App\Support\Campaigns\CampaignNotAllowed;
use App\Support\Campaigns\CampaignPricing;
use App\Support\Campaigns\CampaignResults;
use App\Support\Campaigns\CampaignRules;
use App\Support\Campaigns\CampaignStateMachine;
use App\Support\Campaigns\InsufficientCredit;
use App\Support\Campaigns\InvalidCampaignTransition;
use App\Support\Campaigns\Payment\CampaignPaymentGateway;
use App\Support\Campaigns\ShopCredit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Espace vendeur → Campagnes : liste, assistant de commande, paiement et suivi.
 * Seul le propriétaire de la boutique y accède (groupe de routes role:admin).
 */
class AdCampaignController extends Controller
{
    use ResolvesOwnShop;

    /** Filtres de la liste → statuts affichés. */
    public const FILTERS = [
        'toutes'    => null,
        'en-cours'  => [AdCampaign::STATUS_PAYEE, AdCampaign::STATUS_PROGRAMMEE, AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE, AdCampaign::STATUS_EN_PREPARATION],
        'a-valider' => [AdCampaign::STATUS_VISUELS_A_VALIDER, AdCampaign::STATUS_A_PAYER],
        'terminees' => [AdCampaign::STATUS_TERMINEE, AdCampaign::STATUS_ANNULEE, AdCampaign::STATUS_REFUSEE],
    ];

    public function __construct(private CampaignCheckout $checkout) {}

    public function index(Request $request): View
    {
        $shop   = $this->shop();
        $filter = array_key_exists($request->query('filtre'), self::FILTERS) ? $request->query('filtre') : 'toutes';

        $campaigns = $shop->adCampaigns()->with(['package', 'items.product:id,name'])
            ->when(self::FILTERS[$filter], fn ($q, $statuses) => $q->whereIn('status', $statuses))
            ->latest('id')->paginate(15)->withQueryString();

        return view('boutique.campaigns.index', [
            'shop'      => $shop,
            'campaigns' => $campaigns,
            'results'   => CampaignResults::forCampaigns($campaigns->getCollection()),
            'summary'   => CampaignResults::monthSummary($shop),
            'filter'    => $filter,
            'packages'  => AdPackage::active()->orderBy('sort_order')->get()->groupBy('type'),
            'credit'    => ShopCredit::balance($shop),
            'news'      => CampaignInbox::latest($request->user()),
        ]);
    }

    /** « Tout marquer comme lu » sur les nouveautés des campagnes. */
    public function readAllNews(Request $request): RedirectResponse
    {
        CampaignInbox::markAllRead($request->user());
        return back();
    }

    /** Facture PDF d'une campagne payée (aussi listée dans Finances → Rapports). */
    public function invoice(AdCampaign $campaign)
    {
        $this->authorizeCampaign($campaign);
        abort_unless(CampaignInvoice::available($campaign), 404);
        return CampaignInvoice::download($campaign);
    }

    public function create(Request $request): View
    {
        $shop = $this->shop();
        $packages = AdPackage::active()->orderBy('sort_order')->get();

        $products = $shop->products()->with('variants')->orderByDesc('is_active')->orderBy('name')
            ->get(['id', 'shop_id', 'name', 'image', 'price', 'stock', 'is_active', 'category'])
            ->map(fn (Product $p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'category'   => $p->category,
                'price'      => CampaignPricing::format((int) round((float) $p->price)),
                'image'      => $p->image ? (\App\Services\ImageOptimizer::url($p->image, 'thumb') ?? asset('storage/' . $p->image)) : null,
                'promotable' => CampaignRules::isPromotable($p),
            ]);

        // Pré-remplissage : bouton « Booster » d'un produit, ou « Prolonger » / « Relancer » une campagne
        $prefill = ['type' => $request->query('type') === 'social' ? 'social' : 'boost', 'products' => [], 'scope' => null,
                    'package' => null, 'days' => null, 'starts_on' => null, 'objective' => null, 'targeting' => AdCampaign::DEFAULT_TARGETING];
        if ($productId = (int) $request->query('produit')) {
            if ($products->contains('id', $productId)) {
                $prefill['products'] = [$productId];
                $prefill['scope'] = 'products';
            }
        }
        if ($from = $shop->adCampaigns()->with('items', 'package')->find((int) $request->query('depuis'))) {
            $ids = $from->items->pluck('product_id')->filter()->values()->all();
            $prefill = [
                'type'      => $from->type,
                'products'  => $ids,
                'scope'     => $ids ? 'products' : 'shop',
                'package'   => $from->package->code,
                'days'      => $from->duration_days,
                // Prolonger : le lendemain de la fin ; relancer : au plus tôt
                'starts_on' => $from->ends_on->gte(today()) ? $from->ends_on->copy()->addDay()->toDateString() : null,
                'objective' => $from->objective,
                'targeting' => ($from->targeting ?? []) + AdCampaign::DEFAULT_TARGETING,
            ];
        }

        return view('boutique.campaigns.create', [
            'shop'       => $shop,
            'packages'   => $packages,
            'products'   => $products,
            'prefill'    => $prefill,
            'credit'     => ShopCredit::balance($shop),
            'earliest'   => [
                'boost'  => today()->toDateString(),
                'social' => today()->addDays(CampaignRules::SOCIAL_MIN_LEAD_DAYS)->toDateString(),
            ],
        ]);
    }

    /** Prix, date de fin et places disponibles pour l'étape « Formule » (recalculés à chaque changement). */
    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'package'    => ['required', 'string', 'exists:ad_packages,code'],
            'days'       => ['required', 'integer', 'min:1', 'max:90'],
            'starts_on'  => ['required', 'date'],
            'products'   => ['array', 'max:10'],
            'products.*' => ['integer'],
        ]);
        $shop    = $this->shop();
        $package = AdPackage::where('code', $data['package'])->firstOrFail();
        $start   = Carbon::parse($data['starts_on'])->startOfDay();
        $days    = (int) $data['days'];

        if ($package->priceFor($days) === null) {
            return response()->json(['ok' => false, 'errors' => ["La formule « {$package->name} » ne propose pas {$days} jours."]]);
        }

        $quote  = CampaignPricing::quote($package, $days);
        $errors = CampaignRules::validate($shop, $package, $data['products'] ?? [], $start, $days);
        $full   = null;
        if ($package->max_actives !== null) {
            $products   = Product::whereIn('id', $data['products'] ?? [])->where('shop_id', $shop->id)->get();
            $categories = CampaignRules::capacityCategories($package, $products);
            $end        = CampaignPricing::endDate($start, $days);
            if (CampaignRules::isFull($package, $start, $end, $categories)) {
                $next = CampaignRules::nextFreeDate($package, $start, $days, $categories);
                $full = ['next' => $next?->toDateString(), 'next_label' => $next?->translatedFormat('j F Y')];
            }
        }

        return response()->json([
            'ok'        => $errors === [],
            'errors'    => $errors,
            'full'      => $full,
            'price_gnf' => $quote['price_gnf'],
            'price'     => CampaignPricing::format($quote['price_gnf']),
            'ad_budget' => $quote['ad_budget_gnf'] ? CampaignPricing::format($quote['ad_budget_gnf']) : null,
            'fee'       => CampaignPricing::format($quote['fee_gnf']),
            'ends_on'   => CampaignPricing::endDate($start, $days)->translatedFormat('j F Y'),
            'starts_label' => $start->translatedFormat('j F Y'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package'      => ['required', 'string', 'exists:ad_packages,code'],
            'days'         => ['required', 'integer', 'min:1', 'max:90'],
            'starts_on'    => ['required', 'date'],
            'scope'        => ['required', 'in:shop,products'],
            'products'     => ['array', 'max:10'],
            'products.*'   => ['integer'],
            'payment'      => ['required', 'in:credit,mobile_money'],
            'accept_rules' => ['accepted'],
            // Réseaux sociaux : objectif et public (recalculés par CampaignRules::targeting)
            'objective'    => ['nullable', 'in:' . implode(',', array_keys(AdCampaign::OBJECTIVES))],
            'communes'     => ['array'],
            'communes.*'   => ['in:' . implode(',', AdCampaign::COMMUNES)],
            'age_min'      => ['nullable', 'integer', 'between:' . AdCampaign::AGE_MIN . ',' . AdCampaign::AGE_MAX],
            'age_max'      => ['nullable', 'integer', 'between:' . AdCampaign::AGE_MIN . ',' . AdCampaign::AGE_MAX, 'gte:age_min'],
            'genre'        => ['nullable', 'in:' . implode(',', array_keys(AdCampaign::GENDERS))],
        ], [
            'accept_rules.accepted' => 'Coche « J\'accepte les règles des campagnes » pour continuer.',
            'payment.required'      => 'Choisis un moyen de paiement.',
            'age_max.gte'           => 'L\'âge maximum doit être supérieur ou égal à l\'âge minimum.',
        ]);

        $shop    = $this->shop();
        $package = AdPackage::where('code', $data['package'])->firstOrFail();
        if ($package->isSocial() && empty($data['objective'])) {
            return back()->withInput()->withErrors(['objective' => 'Choisis l\'objectif de ta campagne.']);
        }
        $productIds = $data['scope'] === 'products' ? ($data['products'] ?? []) : [];
        $extra = $package->isSocial()
            ? ['objective' => $data['objective'], 'targeting' => CampaignRules::targeting($package, $data)]
            : [];

        try {
            $campaign = $this->checkout->create($shop, $package, $productIds, Carbon::parse($data['starts_on']), (int) $data['days'], $request->user(), $extra);
        } catch (CampaignNotAllowed $e) {
            return back()->withInput()->withErrors(['campaign' => $e->errors()]);
        }

        return $this->takePayment($campaign, $data['payment'], $request);
    }

    public function show(Request $request, AdCampaign $campaign): View
    {
        $this->authorizeCampaign($campaign);
        CampaignInbox::markCampaignRead($request->user(), $campaign->id);
        $campaign->load(['package', 'items.product', 'payments' => fn ($q) => $q->latest('id'), 'creatives' => fn ($q) => $q->orderByDesc('round')->orderBy('id')]);
        $results = CampaignResults::forCampaign($campaign);

        $elapsed = null;
        if (in_array($campaign->status, [AdCampaign::STATUS_EN_COURS, AdCampaign::STATUS_EN_PAUSE, AdCampaign::STATUS_TERMINEE], true)) {
            $last = $campaign->status === AdCampaign::STATUS_TERMINEE ? $campaign->ends_on : today()->min($campaign->ends_on);
            $elapsed = max(0, min($campaign->duration_days, (int) $campaign->starts_on->diffInDays($last) + 1));
        }

        return view('boutique.campaigns.show', [
            'shop'        => $this->shop(),
            'campaign'    => $campaign,
            'results'     => $results,
            'costPerOrder'=> CampaignResults::costPerOrder($campaign, $results['orders']),
            'daily'       => CampaignResults::daily($campaign),
            'orders'      => $campaign->orders()->with('client:id,name')->latest()->take(5)->get(),
            'elapsed'     => $elapsed,
            'credit'      => ShopCredit::balance($campaign->shop_id),
            'canCancel'   => CampaignStateMachine::can($campaign, AdCampaign::STATUS_ANNULEE),
            // Une pub Meta / TikTok se met en pause par l'équipe Shopio, pas depuis l'espace vendeur
            'canPause'    => $campaign->status === AdCampaign::STATUS_EN_COURS && $campaign->isBoost(),
            'canResume'   => $campaign->status === AdCampaign::STATUS_EN_PAUSE && $campaign->pause_reason === 'vendeur',
            'revisionsLeft' => max(0, CampaignStateMachine::MAX_REVISIONS - $campaign->revision_count),
        ]);
    }

    /** Le vendeur valide les visuels proposés par l'équipe Shopio. */
    public function approveCreatives(AdCampaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);
        return $this->attempt($campaign, fn () => $this->checkout->approveCreatives($campaign),
            'Visuels validés. Ta campagne sera mise en ligne à partir du ' . $campaign->starts_on->translatedFormat('j F') . '.');
    }

    /** Le vendeur demande une modification des visuels (2 fois au plus), avec un commentaire. */
    public function requestCreativeChanges(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);
        $data = $request->validate(['comment' => ['required', 'string', 'min:5', 'max:1000']], [
            'comment.required' => 'Explique ce que tu veux changer sur les visuels.',
            'comment.min'      => 'Explique ce que tu veux changer sur les visuels.',
        ]);
        if ($campaign->revision_count >= CampaignStateMachine::MAX_REVISIONS) {
            return back()->with('error', 'Tu as déjà demandé ' . CampaignStateMachine::MAX_REVISIONS . ' modifications : valide les visuels pour lancer ta campagne.');
        }
        return $this->attempt($campaign, fn () => $this->checkout->requestCreativeChanges($campaign, $data['comment']),
            'Demande envoyée. L\'équipe Shopio te propose de nouveaux visuels rapidement.');
    }

    public function pay(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);
        $data = $request->validate(['payment' => ['required', 'in:credit,mobile_money']]);
        return $this->takePayment($campaign, $data['payment'], $request);
    }

    public function pause(AdCampaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);
        return $this->attempt($campaign, fn () => $this->checkout->pauseByVendor($campaign),
            'Campagne en pause. Les jours restants seront reportés quand tu la relanceras.');
    }

    public function resume(AdCampaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);
        return $this->attempt($campaign, fn () => $this->checkout->resumeByVendor($campaign), 'Campagne relancée.');
    }

    public function cancel(Request $request, AdCampaign $campaign): RedirectResponse
    {
        $this->authorizeCampaign($campaign);
        try {
            $refund = $this->checkout->cancelByVendor($campaign, $request->user());
        } catch (InvalidCampaignTransition) {
            return back()->with('error', 'Cette campagne a déjà commencé : elle ne peut plus être annulée.');
        }
        return redirect()->route('boutique.campaigns.show', $campaign)->with('success', $refund > 0
            ? 'Campagne annulée. ' . CampaignPricing::format($refund) . ' ont été ajoutés à ton Crédit Shopio.'
            : 'Campagne annulée.');
    }

    /** Retour de la page de paiement Mobile Money : on vérifie le statut si le webhook n'est pas encore arrivé. */
    public function paymentReturn(AdCampaignPayment $payment): RedirectResponse
    {
        $this->authorizeCampaign($payment->campaign);
        $payment = $this->checkout->refresh($payment);
        $route = redirect()->route('boutique.campaigns.show', $payment->ad_campaign_id);

        return match ($payment->status) {
            AdCampaignPayment::STATUS_PAYE, AdCampaignPayment::STATUS_REMBOURSE => $route->with('success', 'Paiement reçu : ta campagne est confirmée.'),
            AdCampaignPayment::STATUS_ECHOUE => $route->with('error', 'Le paiement n\'a pas abouti. Tu peux réessayer.'),
            default => $route->with('info', 'Paiement en cours de confirmation. Cette page se mettra à jour dans quelques instants.'),
        };
    }

    /** Page de paiement simulée (hors production, sans clé ChapChap Pay). */
    public function fakePay(AdCampaignPayment $payment, CampaignPaymentGateway $gateway): View
    {
        abort_unless($gateway->isFake(), 404);
        $this->authorizeCampaign($payment->campaign);
        return view('boutique.campaigns.fake-pay', ['payment' => $payment->load('campaign.package'), 'shop' => $this->shop()]);
    }

    public function fakePayConfirm(Request $request, AdCampaignPayment $payment, CampaignPaymentGateway $gateway): RedirectResponse
    {
        abort_unless($gateway->isFake(), 404);
        $this->authorizeCampaign($payment->campaign);
        $data = $request->validate(['result' => ['required', 'in:paye,echoue'], 'operator' => ['nullable', 'in:orange_money,mtn_momo']]);

        if ($payment->status === AdCampaignPayment::STATUS_EN_ATTENTE) {
            $data['result'] === 'paye'
                ? $this->checkout->confirm($payment, $data['operator'] ?? 'orange_money', 'TEST-' . $payment->id)
                : $this->checkout->fail($payment);
        }
        return redirect()->route('boutique.campaigns.payment.return', $payment);
    }

    private function takePayment(AdCampaign $campaign, string $method, Request $request): RedirectResponse
    {
        try {
            if ($method === 'credit') {
                $this->checkout->payWithCredit($campaign, $request->user());
                return redirect()->route('boutique.campaigns.show', $campaign)->with('success', 'Paiement reçu : ta campagne est confirmée.');
            }
            return redirect()->away($this->checkout->startMobileMoney($campaign->fresh('package')));
        } catch (InsufficientCredit $e) {
            return redirect()->route('boutique.campaigns.show', $campaign)->with('error', $e->getMessage() . ' Paie par Mobile Money.');
        } catch (CampaignNotAllowed $e) {
            return redirect()->route('boutique.campaigns.show', $campaign)->with('error', implode(' ', $e->errors()));
        }
    }

    private function attempt(AdCampaign $campaign, callable $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (CampaignNotAllowed $e) {
            return back()->with('error', implode(' ', $e->errors()));
        } catch (InvalidCampaignTransition) {
            return back()->with('error', 'Cette action n\'est pas possible dans l\'état actuel de la campagne.');
        }
        return redirect()->route('boutique.campaigns.show', $campaign)->with('success', $success);
    }

    /** 404 plutôt que 403 : ne pas confirmer qu'une campagne existe sous ce numéro. */
    private function authorizeCampaign(?AdCampaign $campaign): void
    {
        abort_unless($campaign && (int) $campaign->shop_id === (int) $this->shop()->id, 404);
    }
}
