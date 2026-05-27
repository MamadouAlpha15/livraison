<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCompany;
use App\Models\DeliveryCompanyReview;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageOptimizer;

class DeliveryCompanyController extends Controller
{  // liste des entreprises de livraison
    public function index()
    {
        $filterCountry = null;
        if (auth()->check()) {
            $authUser      = auth()->user();
            $filterCountry = $authUser->shop?->country
                ?? $authUser->assignedShop?->country
                ?? $authUser->country;
        }

        $companies = DeliveryCompany::where('active', true)
            ->where('approved', true)
            ->when($filterCountry, fn($q) => $q->where('country', $filterCountry))
            ->withAvg('reviews', 'rating')
            ->withCount(['reviews', 'drivers'])
            ->paginate(12);
        return view('delivery.index', compact('companies'));
    }


   public function create()
    {
        return view('delivery.create'); // le beau formulaire
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:190'],
            'description' => ['nullable','string','max:2000'],
            'phone' => ['nullable','string','max:60'],
            'email' => ['nullable','email','max:190'],
            'address' => ['nullable','string','max:255'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        // Stockage image optimisée en WebP
        if ($request->hasFile('image')) {
            $data['image'] = ImageOptimizer::store($request->file('image'), 'delivery_companies');
        }

        // lier la company à l'utilisateur connecté
        $data['user_id'] = $request->user()->id;
        $data['country']  = $request->user()->country;
        $data['currency'] = $request->user()->country
            ? DeliveryCompany::currencyForCountry($request->user()->country)
            : 'GNF';
        $data['approved'] = false; // en attente d'approbation
        $data['active'] = true;
        $data['slug'] = Str::slug($data['name']) . '-' . time();

        $company = DeliveryCompany::create($data);

        // redirection DIRECTE vers le tableau de bord de l'entreprise,
        // l'utilisateur pourra accéder au dashboard et voir la page "en attente" si non approuvée
        if (session('payment_intent') === 'business') {
            session()->forget('payment_intent');
            return redirect()->route('payment.checkout', ['type' => 'company', 'id' => $company->id]);
        }

        return redirect()->route('company.dashboard')
                         ->with('success', "Votre entreprise a été créée. Elle est en attente d'approbation par un administrateur. Vous pouvez accéder à votre tableau de bord en attendant.");
    }

    // afficher les détails d'une entreprise de livraison

     public function show(DeliveryCompany $company)
    {
        abort_unless($company->approved, 403, 'Entreprise non approuvée');
        $company->load([
            'drivers',
            'zones'    => fn($q) => $q->where('active', true)->orderBy('price'),
            'reviews'  => fn($q) => $q->with('user')->latest(),
        ]);
        $zones      = $company->zones;
        $reviews    = $company->reviews;
        $avgRating  = $reviews->avg('rating') ?? 0;
        $userReview = auth()->check()
            ? $reviews->firstWhere('user_id', auth()->id())
            : null;
        return view('delivery.show', compact('company', 'zones', 'reviews', 'avgRating', 'userReview'));
    }

    public function zonesJson(DeliveryCompany $company)
    {
        $zones = $company->zones()
            ->where('active', true)
            ->orderBy('price')
            ->get(['id', 'name', 'price', 'estimated_minutes', 'color']);

        return response()->json($zones);
    }

    public function storeReview(Request $request, DeliveryCompany $company)
    {
        abort_unless(auth()->check(), 403);

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        DeliveryCompanyReview::updateOrCreate(
            ['delivery_company_id' => $company->id, 'user_id' => auth()->id()],
            $data
        );

        return back()->with('success', 'Votre avis a été enregistré.');
    }

    // tableau de bord de l'entreprise de livraison
  public function dashboard(Request $request)
{
    $company = DeliveryCompany::forUser($request->user());

    // 1) Si l'utilisateur n'a pas d'entreprise, on affiche la vue de création (ou une vue dédiée)
    if (! $company) {
        // Option A : afficher directement le formulaire de création (delivery.create)
        // return view('delivery.create');

        // Option B (recommandée) : afficher une page qui propose de créer + explique l'attente
        return view('delivery.create');
    }

    // 2) Si l'entreprise existe mais n'est pas approuvée -> page d'attente (dashboard restreint)
    if (! $company->approved) {
        return view('company.waiting_approval', compact('company'));
    }

    // 3) Entreprise approuvée -> dashboard complet
    $drivers = $company->drivers()->paginate(20);

    $today     = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();

    $baseOrders = fn() => Order::where('delivery_company_id', $company->id);

    // ── Stats KPI ──
    $pendingOrders      = $baseOrders()->where('status', Order::STATUS_EN_ATTENTE)->count();
    $pendingOrdersYday  = $baseOrders()->where('status', Order::STATUS_EN_ATTENTE)->whereDate('created_at', $yesterday)->count();
    $pendingOrdersToday = $baseOrders()->where('status', Order::STATUS_EN_ATTENTE)->whereDate('created_at', $today)->count();

    $availableDrivers = $company->drivers()->where('status', 'available')->count();
    $totalDrivers     = $company->drivers()->count();

    $inDelivery      = $baseOrders()->where('status', Order::STATUS_EN_LIVRAISON)->count();
    $inDeliveryYday  = $baseOrders()->where('status', Order::STATUS_EN_LIVRAISON)->whereDate('updated_at', $yesterday)->count();

    $delivered      = $baseOrders()->where('status', Order::STATUS_LIVREE)->count();
    $deliveredToday = $baseOrders()->where('status', Order::STATUS_LIVREE)->whereDate('delivered_at', $today)->count();
    $deliveredYday  = $baseOrders()->where('status', Order::STATUS_LIVREE)->whereDate('delivered_at', $yesterday)->count();

    // ── Revenus : delivery_fee des commandes livrées (basé sur delivered_at) ──
    $baseRev = fn() => Order::where('delivery_company_id', $company->id)
                             ->where('status', Order::STATUS_LIVREE);

    $revenus      = $baseRev()->sum('delivery_fee');
    $revenusToday = $baseRev()->whereDate('delivered_at', $today)->sum('delivery_fee');
    $revenusYday  = $baseRev()->whereDate('delivered_at', $yesterday)->sum('delivery_fee');

    // ── Pipeline ──
    $totalOrders = max($baseOrders()->count(), 1);
    $pipeData = [
        ['En attente de chauffeur', $baseOrders()->where('status', Order::STATUS_EN_ATTENTE)->count(),  '#eab308'],
        ['Assignées',               $baseOrders()->where('status', Order::STATUS_CONFIRMEE)->count(),   '#3b82f6'],
        ['En livraison',            $baseOrders()->where('status', Order::STATUS_EN_LIVRAISON)->count(),'#f59e0b'],
        ['Livrées',                 $baseOrders()->where('status', Order::STATUS_LIVREE)->count(),      '#10b981'],
        ['Annulées',                $baseOrders()->where('status', Order::STATUS_ANNULEE)->count(),     '#ef4444'],
    ];

    // ── Chauffeurs actifs (5 premiers) ──
    $activeDrivers = $company->drivers()->orderByRaw("FIELD(status,'available','busy','offline')")->limit(5)->get();

    // ── Graphique commandes : 7 derniers jours ──
    $ordersChart = collect(range(6, 0))->map(
        fn($d) => $baseOrders()->whereDate('created_at', now()->subDays($d)->toDateString())->count()
    )->values();

    // ── Graphique revenus : 30 derniers jours ──
    $revenueChart = collect(range(29, 0))->map(
        fn($d) => (int) $baseRev()->whereDate('delivered_at', now()->subDays($d)->toDateString())->sum('delivery_fee')
    )->values();

    // ── Graphique commandes : 30 derniers jours (sélecteur de période) ──
    $ordersChart30 = collect(range(29, 0))->map(
        fn($d) => $baseOrders()->whereDate('created_at', now()->subDays($d)->toDateString())->count()
    )->values();

    // ── Graphique revenus : 7 derniers jours (sélecteur de période) ──
    $revenueChart7 = collect(range(6, 0))->map(
        fn($d) => (int) $baseRev()->whereDate('delivered_at', now()->subDays($d)->toDateString())->sum('delivery_fee')
    )->values();

    // ── Performance réelle ──
    $prevM = now()->subMonth();

    // Taux de réussite
    $totalLivrees  = $baseOrders()->where('status', Order::STATUS_LIVREE)->count();
    $totalAnnulees = $baseOrders()->where('status', Order::STATUS_ANNULEE)->count();
    $tauxReussite  = ($totalLivrees + $totalAnnulees) > 0
        ? round($totalLivrees / ($totalLivrees + $totalAnnulees) * 100, 1)
        : null;

    $prevLivrees  = $baseOrders()->where('status', Order::STATUS_LIVREE)
        ->whereMonth('updated_at', $prevM->month)->whereYear('updated_at', $prevM->year)->count();
    $prevAnnulees = $baseOrders()->where('status', Order::STATUS_ANNULEE)
        ->whereMonth('updated_at', $prevM->month)->whereYear('updated_at', $prevM->year)->count();
    $tauxReussitePrev = ($prevLivrees + $prevAnnulees) > 0
        ? round($prevLivrees / ($prevLivrees + $prevAnnulees) * 100, 1)
        : null;

    // Temps moyen de traitement commande→livrée (30 derniers jours)
    $avgMins = $baseOrders()->where('status', Order::STATUS_LIVREE)
        ->whereDate('updated_at', '>=', now()->subDays(30))
        ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as m')
        ->value('m');
    $avgMins = $avgMins ? (int) round($avgMins) : null;

    $avgMinsPrev = $baseOrders()->where('status', Order::STATUS_LIVREE)
        ->whereMonth('updated_at', $prevM->month)->whereYear('updated_at', $prevM->year)
        ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as m')
        ->value('m');
    $avgMinsPrev = $avgMinsPrev ? (int) round($avgMinsPrev) : null;

    // Note moyenne (reviews des commandes livrées par cette entreprise)
    $ratingBase = \App\Models\Review::whereHas('order', fn($q) =>
        $q->where('delivery_company_id', $company->id)
          ->where('status', Order::STATUS_LIVREE)
    )->whereNotNull('rating')->where('rating', '>', 0);

    $ratingCount = $ratingBase->count();
    $avgRating   = $ratingCount > 0
        ? round((float) $ratingBase->avg('rating'), 1)
        : null;

    $latestReviews = (clone $ratingBase)
        ->with(['order.shop', 'client'])
        ->latest()
        ->limit(50)
        ->get();

    $devise      = $company->currency ?? 'GNF';
    $isBusiness  = $company->plan === 'business' && $company->plan_expires_at?->isFuture();
    $daysLeft    = $isBusiness ? (int) now()->diffInDays($company->plan_expires_at, false) : 0;
    $maxDrivers  = \App\Services\SubscriptionService::COMP_FREE_MAX_DRIVERS;
    $maxZones    = \App\Services\SubscriptionService::COMP_FREE_MAX_ZONES;
    $maxOrders   = \App\Services\SubscriptionService::COMP_FREE_MAX_ORDERS;
    $usedOrders  = $baseOrders()->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
    $totalZones  = $company->zones()->count();
    $isGuinea       = ($company->country ?? '') === 'GN';
    $bizXof         = number_format(config('genuispay.plans.business', 11400), 0, ',', ' ');
    $bizGnf         = number_format(config('genuispay.plans_gnf.business', 150000), 0, ',', ' ');
    $bizPriceLabel  = $isGuinea
        ? "{$bizXof} XOF/mois (≈ {$bizGnf} GNF 🇬🇳)"
        : "{$bizXof} XOF/mois";

    return view('company.dashboard', compact(
        'company', 'drivers',
        'pendingOrders', 'pendingOrdersToday', 'pendingOrdersYday',
        'availableDrivers', 'totalDrivers',
        'inDelivery', 'inDeliveryYday',
        'delivered', 'deliveredToday', 'deliveredYday',
        'revenus', 'revenusToday', 'revenusYday',
        'pipeData', 'totalOrders',
        'activeDrivers',
        'ordersChart', 'revenueChart',
        'ordersChart30', 'revenueChart7',
        'tauxReussite', 'tauxReussitePrev',
        'totalLivrees', 'totalAnnulees',
        'avgMins', 'avgMinsPrev',
        'avgRating', 'ratingCount', 'latestReviews',
        'devise',
        'isBusiness', 'daysLeft',
        'maxDrivers', 'maxZones', 'maxOrders', 'usedOrders', 'totalZones',
        'isGuinea', 'bizXof', 'bizGnf', 'bizPriceLabel'
    ));
}


    /* ── Live stats JSON — polling 20s depuis le dashboard ── */
    public function liveStats(Request $request)
    {
        $company = DeliveryCompany::forUser($request->user());
        if (! $company || ! $company->approved) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $devise    = $company->currency ?? 'GNF';

        $baseOrders = fn() => Order::where('delivery_company_id', $company->id);
        $baseRev    = fn() => Order::where('delivery_company_id', $company->id)
                                   ->where('status', Order::STATUS_LIVREE);

        $pending          = $baseOrders()->where('status', Order::STATUS_EN_ATTENTE)->count();
        $pendingToday     = $baseOrders()->where('status', Order::STATUS_EN_ATTENTE)->whereDate('created_at', $today)->count();
        $pendingYday      = $baseOrders()->where('status', Order::STATUS_EN_ATTENTE)->whereDate('created_at', $yesterday)->count();

        $available        = $company->drivers()->where('status', 'available')->count();
        $totalDrivers     = $company->drivers()->count();

        $inDelivery       = $baseOrders()->where('status', Order::STATUS_EN_LIVRAISON)->count();
        $inDeliveryYday   = $baseOrders()->where('status', Order::STATUS_EN_LIVRAISON)->whereDate('updated_at', $yesterday)->count();

        $delivered        = $baseOrders()->where('status', Order::STATUS_LIVREE)->count();
        $deliveredToday   = $baseOrders()->where('status', Order::STATUS_LIVREE)->whereDate('delivered_at', $today)->count();
        $deliveredYday    = $baseOrders()->where('status', Order::STATUS_LIVREE)->whereDate('delivered_at', $yesterday)->count();

        $revenus          = $baseRev()->sum('delivery_fee');
        $revenusToday     = $baseRev()->whereDate('delivered_at', $today)->sum('delivery_fee');

        $totalOrders = max($baseOrders()->count(), 1);
        $pipe = [
            ['lbl' => 'En attente de chauffeur', 'val' => $baseOrders()->where('status', Order::STATUS_EN_ATTENTE)->count(),   'color' => '#eab308'],
            ['lbl' => 'Assignées',                'val' => $baseOrders()->where('status', Order::STATUS_CONFIRMEE)->count(),    'color' => '#3b82f6'],
            ['lbl' => 'En livraison',             'val' => $baseOrders()->where('status', Order::STATUS_EN_LIVRAISON)->count(), 'color' => '#f59e0b'],
            ['lbl' => 'Livrées',                  'val' => $baseOrders()->where('status', Order::STATUS_LIVREE)->count(),       'color' => '#10b981'],
            ['lbl' => 'Annulées',                 'val' => $baseOrders()->where('status', Order::STATUS_ANNULEE)->count(),      'color' => '#ef4444'],
        ];
        foreach ($pipe as &$p) {
            $p['pct'] = round($p['val'] / $totalOrders * 100, 1);
        }

        $drivers = $company->drivers()
            ->orderByRaw("FIELD(status,'available','busy','offline')")
            ->limit(5)
            ->get()
            ->map(fn($d) => [
                'name'   => $d->name,
                'ini'    => strtoupper(substr($d->name ?? 'C', 0, 1)) . strtoupper(substr(explode(' ', $d->name ?? 'C H')[1] ?? '', 0, 1)),
                'status' => $d->status ?? 'offline',
                'phone'  => $d->phone,
            ]);

        $trend = function (int|float $today, int|float $yesterday): string {
            if ($yesterday == 0) return $today > 0 ? '↑ +100% vs hier' : '— vs hier';
            $pct = round(($today - $yesterday) / $yesterday * 100);
            return ($pct >= 0 ? '↑ +' : '↓ ') . $pct . '% vs hier';
        };

        $orders7  = collect(range(6, 0))->map(fn($d) => $baseOrders()->whereDate('created_at', now()->subDays($d)->toDateString())->count())->values();
        $orders30 = collect(range(29, 0))->map(fn($d) => $baseOrders()->whereDate('created_at', now()->subDays($d)->toDateString())->count())->values();
        $revenue7  = collect(range(6, 0))->map(fn($d) => (int) $baseRev()->whereDate('delivered_at', now()->subDays($d)->toDateString())->sum('delivery_fee'))->values();
        $revenue30 = collect(range(29, 0))->map(fn($d) => (int) $baseRev()->whereDate('delivered_at', now()->subDays($d)->toDateString())->sum('delivery_fee'))->values();

        return response()->json([
            'pending'        => $pending,
            'pending_trend'  => $trend($pendingToday, $pendingYday),
            'available'      => $available,
            'total_drivers'  => $totalDrivers,
            'in_delivery'    => $inDelivery,
            'delivery_trend' => $trend($inDelivery, $inDeliveryYday),
            'delivered'      => $delivered,
            'done_trend'     => $trend($deliveredToday, $deliveredYday),
            'revenus_fmt'    => number_format($revenus, 0, ',', ' '),
            'rev_trend'      => $revenusToday > 0
                ? '+' . number_format($revenusToday, 0, ',', ' ') . ' aujourd\'hui · ' . $devise
                : $devise . ' · Frais de livraison cumulés',
            'pipe'           => $pipe,
            'drivers'        => $drivers,
            'orders_7'       => $orders7,
            'orders_30'      => $orders30,
            'revenue_7'      => $revenue7,
            'revenue_30'     => $revenue30,
        ]);
    }

    // Validation par superadmin
    public function approve(DeliveryCompany $company)
    {
        $company->update(['approved' => true, 'approved_at' => now()]);
        return back()->with('success', "L'entreprise {$company->name} a été approuvée.");
    }
}
