<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PushService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private function company(): DeliveryCompany
    {
        $company = DeliveryCompany::forUser(auth()->user());

        if (!$company) {
            abort(403, "Aucune entreprise de livraison liée à ce compte.");
        }

        return $company;
    }

    public function index(Request $request)
    {
        $company = $this->company();

        $svc = app(SubscriptionService::class);
        if ($svc->companyPlan($company) === 'free') {
            $usedThisMonth = $svc->monthlyCompanyOrderCount($company);
            if ($usedThisMonth > SubscriptionService::COMP_FREE_MAX_ORDERS) {
                return redirect()->route('company.subscription.upgrade')
                    ->with('plan_error', "Limite atteinte : {$usedThisMonth}/10 commandes ce mois. Passez au Plan Business pour continuer.");
            }
        }

        $query = Order::with(['client', 'shop', 'driver', 'items', 'deliveryZone'])
            ->where('delivery_company_id', $company->id)
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('id', 'like', "%{$s}%")
                  ->orWhere('delivery_destination', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
                  ->orWhereHas('shop',   fn($sh) => $sh->where('name', 'like', "%{$s}%"));
            });
        }

        $period = $request->input('period', 'all');
        match($period) {
            'today'     => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            'week'      => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month'     => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'custom'    => $query->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
                                 ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->date_to)),
            default     => null,
        };

        if ($request->filled('boutique')) {
            $query->where('shop_id', $request->boutique);
        }

        $orders = $query->paginate(20)->withQueryString();

        $boutiqueId = $request->filled('boutique') ? (int) $request->boutique : null;
        $shopFilter = $boutiqueId ? \App\Models\Shop::find($boutiqueId) : null;

        // Base sans filtre période (pour en_livraison qui est un état courant)
        $base = fn() => Order::where('delivery_company_id', $company->id)
            ->when($boutiqueId, fn($q) => $q->where('shop_id', $boutiqueId));

        // Base avec le filtre période appliqué — suit exactement le filtre de la liste
        $statsBase = function() use ($company, $boutiqueId, $period, $request) {
            $q = Order::where('delivery_company_id', $company->id)
                ->when($boutiqueId, fn($qq) => $qq->where('shop_id', $boutiqueId));
            match($period) {
                'today'     => $q->whereDate('created_at', today()),
                'yesterday' => $q->whereDate('created_at', today()->subDay()),
                'week'      => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month'     => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                'custom'    => $q->when($request->filled('date_from'), fn($q2) => $q2->whereDate('created_at', '>=', $request->date_from))
                                 ->when($request->filled('date_to'),   fn($q2) => $q2->whereDate('created_at', '<=', $request->date_to)),
                default     => null,
            };
            return $q;
        };

        $stats = [
            'total'        => $statsBase()->count(),
            'en_attente'   => $statsBase()->where('status', Order::STATUS_EN_ATTENTE)->count(),
            'en_livraison' => $statsBase()->where('status', Order::STATUS_EN_LIVRAISON)->count(),
            'livrees'      => $statsBase()->where('status', Order::STATUS_LIVREE)->count(),
            'revenus'      => $statsBase()->where('status', Order::STATUS_LIVREE)->sum('delivery_fee'),
        ];

        $drivers = $company->drivers()->orderByRaw("FIELD(status,'available','busy','offline')")->orderBy('name')->get();

        $devise       = $company->currency ?? 'GNF';
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.orders.index', compact(
            'orders', 'stats', 'drivers', 'company', 'period', 'devise', 'shopFilter',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders', 'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function assign(Request $request, Order $order)
    {
        $company = $this->company();

        abort_unless((int) $order->delivery_company_id === $company->id, 403, 'Cette commande ne vous appartient pas.');

        $data = $request->validate([
            'driver_id'            => ['required', 'exists:drivers,id'],
            'delivery_fee'         => ['required', 'numeric', 'min:0'],
            'delivery_destination' => ['nullable', 'string', 'max:255'],
        ]);

        $driver = Driver::where('id', $data['driver_id'])
            ->where('delivery_company_id', $company->id)
            ->firstOrFail();

        // Statut → confirmée : c'est le livreur qui déclenche "en_livraison" depuis son dashboard
        $order->update([
            'driver_id'            => $driver->id,
            'delivery_fee'         => $data['delivery_fee'],
            'delivery_destination' => $data['delivery_destination'] ?? $order->delivery_destination,
            'status'               => Order::STATUS_CONFIRMEE,
        ]);

        // Le statut du chauffeur et son is_available ne changent PAS ici.
        // C'est le livreur qui passe "en_livraison" depuis son propre dashboard.

        // Push au chauffeur s'il a un compte utilisateur lié
        try {
            $driverUser = $driver->user;
            if ($driverUser) {
                app(PushService::class)->sendToUser(
                    $driverUser,
                    'Nouvelle commande assignée 📦',
                    'Commande #' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . ' · ' . number_format($order->total, 0, ',', ' ') . ' ' . ($company->currency ?? 'GNF'),
                    1,
                    '/livreur/orders'
                );
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success'      => true,
            'driver_name'  => $driver->name,
            'driver_phone' => $driver->phone,
            'delivery_fee' => number_format($data['delivery_fee'], 0, ',', ' '),
            'status'       => Order::STATUS_CONFIRMEE,
        ]);
    }

    /**
     * Choisit le meilleur chauffeur disponible pour une commande :
     * 1. Priorité aux chauffeurs disponibles couvrant la même zone que la commande.
     * 2. À défaut (aucun chauffeur dans cette zone), n'importe quel chauffeur disponible de l'entreprise.
     * 3. Parmi les candidats, celui qui a le moins de commandes actives en ce moment (équilibrage de charge).
     */
    private function pickBestDriver(DeliveryCompany $company, Order $order): ?Driver
    {
        $candidates = collect();

        if ($order->delivery_zone_id) {
            $candidates = Driver::where('delivery_company_id', $company->id)
                ->where('status', 'available')
                ->where('zone_id', $order->delivery_zone_id)
                ->get();
        }

        if ($candidates->isEmpty()) {
            $candidates = Driver::where('delivery_company_id', $company->id)
                ->where('status', 'available')
                ->get();
        }

        if ($candidates->isEmpty()) {
            return null;
        }

        return $candidates
            ->map(function ($driver) {
                $driver->active_orders_count = Order::where('driver_id', $driver->id)
                    ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
                    ->count();
                return $driver;
            })
            ->sortBy('active_orders_count')
            ->first();
    }

    /**
     * Trouve les commandes "sœurs" d'une commande : même trajet (même delivery_batch_id),
     * ou à défaut même client + même boutique — encore en attente, sans chauffeur.
     * Sert à ne facturer les frais de livraison qu'une seule fois pour un même client
     * qui a commandé plusieurs produits (donc plusieurs commandes) en une fois.
     */
    private function findGroupSiblings(DeliveryCompany $company, Order $order): \Illuminate\Support\Collection
    {
        $query = Order::where('delivery_company_id', $company->id)
            ->where('id', '!=', $order->id)
            ->whereNull('driver_id')
            ->where('status', Order::STATUS_EN_ATTENTE);

        if ($order->delivery_batch_id) {
            $query->where('delivery_batch_id', $order->delivery_batch_id);
        } else {
            $query->where('user_id', $order->user_id)->where('shop_id', $order->shop_id);
        }

        return $query->get();
    }

    /**
     * Assigne UN chauffeur à un groupe de commandes formant un même trajet
     * (même client + même boutique, ou déjà un même delivery_batch_id) :
     * - un seul chauffeur pour tout le groupe (choisi sur la 1ère commande du groupe)
     * - les frais de livraison ne sont appliqués qu'une seule fois (1ère commande, les autres à 0)
     * - un delivery_batch_id commun est créé si le groupe a plusieurs commandes et n'en avait pas encore
     * - une seule notification push envoyée pour tout le groupe (pas une par commande)
     * Retourne null si aucun chauffeur disponible.
     */
    private function assignGroupToDriver(DeliveryCompany $company, \Illuminate\Support\Collection $groupOrders): ?array
    {
        $representative = $groupOrders->first();
        $driver = $this->pickBestDriver($company, $representative);

        if (! $driver) {
            return null;
        }

        $batchId = $representative->delivery_batch_id;
        if (! $batchId && $groupOrders->count() > 1) {
            $batchId = (string) \Illuminate\Support\Str::uuid();
        }

        $appliedFee = 0;
        foreach ($groupOrders->values() as $i => $order) {
            // Même client/trajet : les frais de livraison ne comptent qu'une fois, sur la 1ère commande
            $fee = $i === 0
                ? ($order->delivery_fee ?? optional(\App\Models\DeliveryZone::find($order->delivery_zone_id))->price ?? 0)
                : 0;
            if ($i === 0) $appliedFee = $fee;

            $order->update([
                'driver_id'         => $driver->id,
                'delivery_fee'      => $fee,
                'delivery_batch_id' => $batchId ?? $order->delivery_batch_id,
                'status'            => Order::STATUS_CONFIRMEE,
            ]);
        }

        try {
            $driverUser = $driver->user;
            if ($driverUser) {
                $total = $groupOrders->sum('total');
                app(PushService::class)->sendToUser(
                    $driverUser,
                    'Nouvelle commande assignée 📦',
                    $groupOrders->count() > 1
                        ? $groupOrders->count() . ' commandes (même client) · ' . number_format($total, 0, ',', ' ') . ' ' . ($company->currency ?? 'GNF')
                        : 'Commande #' . str_pad($representative->id, 4, '0', STR_PAD_LEFT) . ' · ' . number_format($representative->total, 0, ',', ' ') . ' ' . ($company->currency ?? 'GNF'),
                    1,
                    '/livreur/orders'
                );
            }
        } catch (\Throwable $e) {}

        return ['driver' => $driver, 'fee' => $appliedFee];
    }

    /**
     * Assigne automatiquement le meilleur chauffeur disponible à une commande.
     * Si d'autres commandes en attente du même client (même boutique, ou même batch)
     * sont aussi non assignées, elles sont assignées en même temps au même chauffeur,
     * avec les frais de livraison appliqués une seule fois pour tout le groupe.
     */
    public function autoAssign(Order $order)
    {
        $company = $this->company();

        abort_unless((int) $order->delivery_company_id === $company->id, 403, 'Cette commande ne vous appartient pas.');

        if ($order->driver_id) {
            return response()->json(['success' => false, 'message' => 'Cette commande a déjà un chauffeur assigné.'], 422);
        }

        $siblings    = $this->findGroupSiblings($company, $order);
        $groupOrders = collect([$order])->merge($siblings)->values();

        $result = $this->assignGroupToDriver($company, $groupOrders);

        if (! $result) {
            return response()->json(['success' => false, 'message' => 'Aucun chauffeur disponible actuellement.'], 422);
        }

        return response()->json([
            'success'       => true,
            'driver_id'     => $result['driver']->id,
            'driver_name'   => $result['driver']->name,
            'driver_phone'  => $result['driver']->phone,
            'delivery_fee'  => number_format($result['fee'], 0, ',', ' '),
            'grouped_count' => $groupOrders->count(),
            'status'        => Order::STATUS_CONFIRMEE,
        ]);
    }

    /**
     * Assigne automatiquement TOUTES les commandes en attente non assignées de l'entreprise.
     * Regroupe d'abord par trajet (même batch, sinon même client + même boutique) pour que
     * chaque groupe n'ait qu'un seul chauffeur et une seule facturation de frais de livraison.
     */
    public function bulkAutoAssign(Request $request)
    {
        $company = $this->company();

        $orders = Order::where('delivery_company_id', $company->id)
            ->where('status', Order::STATUS_EN_ATTENTE)
            ->whereNull('driver_id')
            ->orderBy('created_at') // les plus anciennes en attente en premier
            ->get();

        $groups = $orders->groupBy(function ($o) {
            return $o->delivery_batch_id
                ? 'batch_' . $o->delivery_batch_id
                : 'client_' . $o->user_id . '_shop_' . $o->shop_id;
        });

        $assigned = 0;
        $skipped  = 0;
        $trips    = 0;

        foreach ($groups as $groupOrders) {
            $result = $this->assignGroupToDriver($company, $groupOrders->values());

            if (! $result) {
                $skipped += $groupOrders->count();
                continue;
            }

            $assigned += $groupOrders->count();
            $trips++;
        }

        return response()->json([
            'success'  => true,
            'assigned' => $assigned,
            'skipped'  => $skipped,
            'trips'    => $trips,
        ]);
    }

    public function mapView()
    {
        $company = $this->company();

        $svc          = app(SubscriptionService::class);
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.carte.index', compact(
            'company',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders',
            'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function mapData()
    {
        $company = $this->company();

        $orders = Order::with(['shop', 'client', 'driver'])
            ->where('delivery_company_id', $company->id)
            ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
            ->whereNotNull('driver_id')
            ->get();

        // One entry per driver — no duplicate markers for drivers with multiple orders
        $grouped = $orders->groupBy('driver_id')->map(function ($driverOrders) {
            // Best GPS = order with the most recent ping that actually has coordinates
            $gpsOrder = $driverOrders
                ->filter(fn($o) => $o->current_lat && $o->current_lng)
                ->sortByDesc('last_ping_at')
                ->first() ?? $driverOrders->first();

            $driver = optional($driverOrders->first()->driver);

            $status = $driverOrders->contains('status', Order::STATUS_EN_LIVRAISON)
                ? Order::STATUS_EN_LIVRAISON
                : Order::STATUS_CONFIRMEE;

            $ordersList = $driverOrders->map(fn($o) => [
                'id'          => $o->id,
                'shop'        => optional($o->shop)->name   ?? '—',
                'client'      => optional($o->client)->name ?? '—',
                'destination' => $o->delivery_destination   ?? '',
                'status'      => $o->status,
                'client_lat'  => $o->client_lat,
                'client_lng'  => $o->client_lng,
                'vendor_lat'  => $o->vendor_lat,
                'vendor_lng'  => $o->vendor_lng,
            ])->values()->all();

            $destination = $driverOrders->pluck('delivery_destination')
                ->filter()->unique()->implode(' · ');

            $clients = $driverOrders
                ->map(fn($o) => optional($o->client)->name ?? null)
                ->filter()->unique()->implode(', ');

            return [
                'driver_id'    => $driverOrders->first()->driver_id,
                'driver'       => $driver->name  ?? 'Non assigné',
                'driver_phone' => $driver->phone ?? '',
                'status'       => $status,
                'phase'        => $status === Order::STATUS_EN_LIVRAISON ? 2 : 1,
                'lat'          => $gpsOrder->current_lat,
                'lng'          => $gpsOrder->current_lng,
                'ping'         => $gpsOrder->last_ping_at?->toIso8601String(),
                'ping_ago'     => $gpsOrder->last_ping_at?->diffForHumans() ?? 'jamais',
                'orders'       => $ordersList,
                'order_count'  => $driverOrders->count(),
                'client'       => $clients,
                'destination'  => $destination,
                'shop'         => optional($driverOrders->first()->shop)->name ?? '—',
            ];
        })->values();

        return response()->json(['ok' => true, 'orders' => $grouped]);
    }

    public function inProgress()
    {
        $company = $this->company();

        $orders = Order::with(['shop', 'client', 'driver'])
            ->where('delivery_company_id', $company->id)
            ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
            ->latest('updated_at')
            ->get();

        $stats = [
            'total'        => $orders->count(),
            'confirmees'   => $orders->where('status', Order::STATUS_CONFIRMEE)->count(),
            'en_livraison' => $orders->where('status', Order::STATUS_EN_LIVRAISON)->count(),
        ];

        $svc          = app(SubscriptionService::class);
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.livraisons.index', compact(
            'orders', 'stats', 'company',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders',
            'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function inProgressData()
    {
        $company = $this->company();

        $orders = Order::with(['shop', 'client', 'driver'])
            ->where('delivery_company_id', $company->id)
            ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
            ->latest('updated_at')
            ->get()
            ->map(fn($o) => [
                'id'          => $o->id,
                'shop'        => optional($o->shop)->name    ?? '—',
                'client'      => optional($o->client)->name  ?? '—',
                'destination' => $o->delivery_destination    ?? '—',
                'driver'      => optional($o->driver)->name  ?? 'Non assigné',
                'driver_phone'=> optional($o->driver)->phone ?? '',
                'status'      => $o->status,
                'fee'         => $o->delivery_fee            ?? 0,
                'updated_at'  => $o->updated_at->diffForHumans(),
                'updated_ts'  => $o->updated_at->timestamp,
            ]);

        return response()->json(['ok' => true, 'orders' => $orders]);
    }

    public function historique(Request $request)
    {
        $company = $this->company();

        $query = Order::with(['client', 'shop', 'driver'])
            ->where('delivery_company_id', $company->id)
            ->whereIn('status', [Order::STATUS_LIVREE, Order::STATUS_ANNULEE])
            ->latest('updated_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('id', 'like', "%{$s}%")
                  ->orWhere('delivery_destination', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
                  ->orWhereHas('shop',   fn($sh) => $sh->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('driver', fn($d)  => $d->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        if ($request->filled('period')) {
            match($request->period) {
                'today' => $query->whereDate('updated_at', today()),
                'week'  => $query->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year),
                default => null,
            };
        }

        $orders = $query->paginate(25)->withQueryString();

        $shopId     = $request->filled('shop_id') ? (int) $request->shop_id : null;
        $shopFilter = $shopId ? \App\Models\Shop::find($shopId) : null;
        $period     = $request->input('period', '');

        // Base filtrée par période + shop + driver — suit exactement les filtres actifs
        $statsBase = function() use ($company, $shopId, $period, $request) {
            $q = Order::where('delivery_company_id', $company->id)
                ->whereIn('status', [Order::STATUS_LIVREE, Order::STATUS_ANNULEE])
                ->when($shopId, fn($qq) => $qq->where('shop_id', $shopId))
                ->when($request->filled('driver_id'), fn($qq) => $qq->where('driver_id', $request->driver_id));

            if ($period) {
                match($period) {
                    'today' => $q->whereDate('updated_at', today()),
                    'week'  => $q->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'month' => $q->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year),
                    default => null,
                };
            } elseif ($request->filled('date_from') || $request->filled('date_to')) {
                $q->when($request->filled('date_from'), fn($q2) => $q2->whereDate('updated_at', '>=', $request->date_from))
                  ->when($request->filled('date_to'),   fn($q2) => $q2->whereDate('updated_at', '<=', $request->date_to));
            }
            return $q;
        };

        // Revenus global (référence fixe, sans filtre période)
        $revenusGlobal = Order::where('delivery_company_id', $company->id)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->where('status', Order::STATUS_LIVREE)
            ->sum('delivery_fee');

        $stats = [
            'total_livrees'  => $statsBase()->where('status', Order::STATUS_LIVREE)->count(),
            'total_annulees' => $statsBase()->where('status', Order::STATUS_ANNULEE)->count(),
            'revenus'        => $statsBase()->where('status', Order::STATUS_LIVREE)->sum('delivery_fee'),
            'revenus_total'  => $revenusGlobal,
        ];

        $drivers = $company->drivers()->orderBy('name')->get();

        $svc          = app(SubscriptionService::class);
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.historique.index', compact(
            'orders', 'stats', 'drivers', 'company', 'shopFilter', 'period',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders',
            'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function clients(Request $request)
    {
        $company = $this->company();

        $search = $request->input('search');

        $clientsQuery = \App\Models\User::whereHas('orders', fn($q) =>
            $q->where('delivery_company_id', $company->id)
        )->withCount([
            'orders as total_orders' => fn($q) => $q->where('delivery_company_id', $company->id),
            'orders as livrees'      => fn($q) => $q->where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE),
            'orders as en_cours'     => fn($q) => $q->where('delivery_company_id', $company->id)->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON]),
            'orders as annulees'     => fn($q) => $q->where('delivery_company_id', $company->id)->where('status', Order::STATUS_ANNULEE),
        ])->withSum(['orders as total_montant' => fn($q) =>
            $q->where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE)
        ], 'total')
        ->withMax(['orders as derniere_commande' => fn($q) =>
            $q->where('delivery_company_id', $company->id)
        ], 'created_at')
        ->withMax(['orders as order_phone' => fn($q) =>
            $q->where('delivery_company_id', $company->id)->whereNotNull('client_phone')
        ], 'client_phone')
        ->withMax(['orders as order_address' => fn($q) =>
            $q->where('delivery_company_id', $company->id)->whereNotNull('delivery_destination')
        ], 'delivery_destination');

        if ($search) {
            $clientsQuery->where(fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            );
        }

        $clients = $clientsQuery->orderByDesc('total_orders')->paginate(18)->withQueryString();

        $stats = [
            'total_clients' => \App\Models\User::whereHas('orders', fn($q) => $q->where('delivery_company_id', $company->id))->count(),
            'total_livrees' => Order::where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE)->count(),
            'top_client'    => \App\Models\User::whereHas('orders', fn($q) => $q->where('delivery_company_id', $company->id))
                                ->withCount(['orders as total_orders' => fn($q) => $q->where('delivery_company_id', $company->id)])
                                ->orderByDesc('total_orders')->first(),
        ];

        $svc          = app(SubscriptionService::class);
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.clients.index', compact(
            'clients', 'stats', 'company', 'search',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders',
            'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function boutiques(Request $request)
    {
        $company = $this->company();

        $search = $request->input('search');

        // Boutiques ayant passé des commandes via cette entreprise
        $shopsQuery = \App\Models\Shop::whereHas('orders', fn($q) =>
            $q->where('delivery_company_id', $company->id)
        )->withCount([
            'orders as total_orders'     => fn($q) => $q->where('delivery_company_id', $company->id),
            'orders as livrees'          => fn($q) => $q->where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE),
            'orders as en_cours'         => fn($q) => $q->where('delivery_company_id', $company->id)->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON]),
            'orders as annulees'         => fn($q) => $q->where('delivery_company_id', $company->id)->where('status', Order::STATUS_ANNULEE),
        ])->withSum(['orders as revenus' => fn($q) =>
            $q->where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE)
        ], 'delivery_fee');

        if ($search) {
            $shopsQuery->where(fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
            );
        }

        $shops = $shopsQuery->orderByDesc('total_orders')->paginate(12)->withQueryString();

        $stats = [
            'total_boutiques' => \App\Models\Shop::whereHas('orders', fn($q) => $q->where('delivery_company_id', $company->id))->count(),
            'total_livrees'   => Order::where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE)->count(),
            'total_revenus'   => Order::where('delivery_company_id', $company->id)->where('status', Order::STATUS_LIVREE)->sum('delivery_fee'),
        ];

        $svc          = app(SubscriptionService::class);
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.boutiques.index', compact(
            'shops', 'stats', 'company', 'search',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders',
            'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function notifications()
    {
        $company = $this->company();

        $base = Order::where('delivery_company_id', $company->id)
            ->where('status', Order::STATUS_EN_ATTENTE);

        $totalPending = (clone $base)->count();

        $orders = $base->with(['shop', 'client'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($o) => [
                'id'         => $o->id,
                'shop_name'  => optional($o->shop)->name ?? '—',
                'client'     => optional($o->client)->name ?? '—',
                'address'    => $o->delivery_destination ?? '—',
                'created_at' => $o->created_at->diffForHumans(),
                'created_ts' => $o->created_at->timestamp,
            ]);

        return response()->json(['ok' => true, 'orders' => $orders, 'total_pending' => $totalPending]);
    }

    public function liveStats(): \Illuminate\Http\JsonResponse
    {
        $company = $this->company();

        $base = fn() => Order::where('delivery_company_id', $company->id);

        return response()->json([
            'total'        => $base()->count(),
            'en_attente'   => $base()->where('status', Order::STATUS_EN_ATTENTE)->count(),
            'en_livraison' => $base()->where('status', Order::STATUS_EN_LIVRAISON)->count(),
            'livrees'      => $base()->where('status', Order::STATUS_LIVREE)->count(),
            'revenus'      => (int) $base()->where('status', Order::STATUS_LIVREE)->sum('delivery_fee'),
            'latest_id'    => (int) ($base()->max('id') ?? 0),
        ]);
    }

    /**
     * Libère le chauffeur seulement s'il n'a plus de commandes actives.
     * Respecte son is_available pour choisir entre 'available' et 'offline'.
     */
    private function releaseDriverIfFree(Driver $driver): void
    {
        $hasActive = Order::where('driver_id', $driver->id)
            ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
            ->exists();

        if ($hasActive) return;

        $isOnline = $driver->user_id
            ? (bool) \App\Models\User::where('id', $driver->user_id)->value('is_available')
            : false;

        $driver->update(['status' => $isOnline ? 'available' : 'offline']);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $company = $this->company();

        abort_unless((int) $order->delivery_company_id === $company->id, 403, 'Cette commande ne vous appartient pas.');

        $allowed = [
            Order::STATUS_EN_LIVRAISON,
            Order::STATUS_LIVREE,
            Order::STATUS_ANNULEE,
        ];

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', $allowed)],
        ]);

        $driverId = $order->driver_id;
        $order->update(['status' => $data['status']]);

        if ($data['status'] === Order::STATUS_LIVREE) {
            $order->load('payment');
            if ($order->payment) {
                $order->payment->update(['status' => 'payé']);
            } else {
                Payment::create(['order_id' => $order->id, 'method' => 'cash', 'amount' => $order->total, 'status' => 'payé']);
            }
        }

        if ($driverId) {
            $driver = Driver::find($driverId);
            if ($driver) {
                if (in_array($data['status'], [Order::STATUS_LIVREE, Order::STATUS_ANNULEE], true)) {
                    $this->releaseDriverIfFree($driver);
                } elseif ($data['status'] === Order::STATUS_EN_LIVRAISON) {
                    $driver->update(['status' => 'busy']);
                }
            }
        }

        return response()->json(['success' => true, 'status' => $data['status']]);
    }

    public function cancel(Request $request, Order $order)
    {
        $company = $this->company();
        abort_unless((int) $order->delivery_company_id === $company->id, 403);
        abort_unless(!in_array($order->status, [Order::STATUS_LIVREE, Order::STATUS_ANNULEE]), 422, 'Impossible d\'annuler cette commande.');

        $driverId = $order->driver_id;
        $order->update(['status' => Order::STATUS_ANNULEE]);

        if ($driverId) {
            $driver = Driver::find($driverId);
            if ($driver) $this->releaseDriverIfFree($driver);
        }

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, Order $order)
    {
        $company = $this->company();
        abort_unless((int) $order->delivery_company_id === $company->id, 403);
        abort_unless($order->status === Order::STATUS_ANNULEE, 422, 'Seules les commandes annulées peuvent être restaurées.');

        $order->update([
            'status'      => Order::STATUS_EN_ATTENTE,
            'driver_id'   => null,
            'delivery_fee'=> null,
        ]);

        return response()->json(['success' => true]);
    }

    public function bulkCancel(Request $request)
    {
        $company = $this->company();
        $ids = $request->validate(['order_ids' => 'required|array|min:1'])['order_ids'];

        // Récupérer les driver_ids concernés avant l'annulation
        $driverIds = Order::whereIn('id', $ids)
            ->where('delivery_company_id', $company->id)
            ->whereNotIn('status', [Order::STATUS_LIVREE, Order::STATUS_ANNULEE])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->unique();

        $count = Order::whereIn('id', $ids)
            ->where('delivery_company_id', $company->id)
            ->whereNotIn('status', [Order::STATUS_LIVREE, Order::STATUS_ANNULEE])
            ->update(['status' => Order::STATUS_ANNULEE]);

        // Libérer les chauffeurs qui n'ont plus de commandes actives
        foreach ($driverIds as $driverId) {
            $driver = Driver::find($driverId);
            if ($driver) $this->releaseDriverIfFree($driver);
        }

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function bulkRestore(Request $request)
    {
        $company = $this->company();
        $ids = $request->validate(['order_ids' => 'required|array|min:1'])['order_ids'];

        $count = Order::whereIn('id', $ids)
            ->where('delivery_company_id', $company->id)
            ->where('status', Order::STATUS_ANNULEE)
            ->update([
                'status'       => Order::STATUS_EN_ATTENTE,
                'driver_id'    => null,
                'delivery_fee' => null,
            ]);

        return response()->json(['success' => true, 'count' => $count]);
    }
}
