<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\CourierCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageOptimizer;

class DeliveryCompanyController extends Controller
{  // liste des entreprises de livraison
    public function index()
    {
        $companies = DeliveryCompany::where('active', true)->where('approved', true)->paginate(12);
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
            'commission_percent' => ['required','numeric','between:0,100'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
        ]);

        // Stockage image optimisée en WebP
        if ($request->hasFile('image')) {
            $data['image'] = ImageOptimizer::store($request->file('image'), 'delivery_companies');
        }

        // lier la company à l'utilisateur connecté
        $data['user_id'] = $request->user()->id;
        $data['approved'] = false; // en attente d'approbation
        $data['active'] = true;
        $data['slug'] = Str::slug($data['name']) . '-' . time();

        $company = DeliveryCompany::create($data);

        // redirection DIRECTE vers le tableau de bord de l'entreprise,
        // l'utilisateur pourra accéder au dashboard et voir la page "en attente" si non approuvée
        return redirect()->route('company.dashboard')
                         ->with('success', 'Votre entreprise a été créée. Elle est en attente d’approbation par un administrateur. Vous pouvez accéder à votre tableau de bord en attendant.');
    }

    // afficher les détails d'une entreprise de livraison

     public function show(DeliveryCompany $company)
    {
        abort_unless($company->approved, 403, 'Entreprise non approuvée');
        return view('delivery.show', compact('company'));
    }

    // tableau de bord de l'entreprise de livraison
  public function dashboard(Request $request)
{
    $company = DeliveryCompany::where('user_id', $request->user()->id)->first();

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
    $deliveredToday = $baseOrders()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', $today)->count();
    $deliveredYday  = $baseOrders()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', $yesterday)->count();

    // ── Revenus : commissions payées par les boutiques à cette entreprise ──
    $baseComm = fn() => CourierCommission::whereHas('order', fn($q) => $q->where('delivery_company_id', $company->id))
                        ->where('status', CourierCommission::STATUS_PAYEE);

    $revenus      = $baseComm()->sum('amount');
    $revenusToday = $baseComm()->whereDate('paid_at', $today)->sum('amount');
    $revenusYday  = $baseComm()->whereDate('paid_at', $yesterday)->sum('amount');

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
        fn($d) => $baseComm()->whereDate('paid_at', now()->subDays($d)->toDateString())->sum('amount')
    )->values();

    $devise = $company->currency ?? 'GNF';

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
        'devise'
    ));
}


    // Validation par superadmin
    public function approve(DeliveryCompany $company)
    {
        $company->update(['approved' => true, 'approved_at' => now()]);
        return back()->with('success', "L'entreprise {$company->name} a été approuvée.");
    }
}
