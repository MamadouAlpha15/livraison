<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Driver;
use App\Models\Order;
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

        $base = fn() => Order::where('delivery_company_id', $company->id)
            ->when($boutiqueId, fn($q) => $q->where('shop_id', $boutiqueId));

        $stats = [
            'total'         => $base()->count(),
            'en_attente'    => $base()->where('status', Order::STATUS_EN_ATTENTE)->count(),
            'en_livraison'  => $base()->where('status', Order::STATUS_EN_LIVRAISON)->count(),
            'livrees_today' => $base()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', today())->count(),
            'revenus_today' => $base()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', today())->sum('delivery_fee'),
        ];

        $drivers = $company->drivers()->orderByRaw("FIELD(status,'available','busy','offline')")->orderBy('name')->get();

        $devise = $company->currency ?? 'GNF';
        return view('company.orders.index', compact('orders', 'stats', 'drivers', 'company', 'period', 'devise', 'shopFilter'));
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

        return response()->json([
            'success'      => true,
            'driver_name'  => $driver->name,
            'driver_phone' => $driver->phone,
            'delivery_fee' => number_format($data['delivery_fee'], 0, ',', ' '),
            'status'       => Order::STATUS_CONFIRMEE,
        ]);
    }

    public function mapView()
    {
        $company = $this->company();
        return view('company.carte.index', compact('company'));
    }

    public function mapData()
    {
        $company = $this->company();

        $orders = Order::with(['shop', 'client', 'driver'])
            ->where('delivery_company_id', $company->id)
            ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
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

        return view('company.livraisons.index', compact('orders', 'stats', 'company'));
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

        $base = fn() => Order::where('delivery_company_id', $company->id)
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId));

        $stats = [
            'total_livrees'  => $base()->where('status', Order::STATUS_LIVREE)->count(),
            'total_annulees' => $base()->where('status', Order::STATUS_ANNULEE)->count(),
            'revenus_total'  => $base()->where('status', Order::STATUS_LIVREE)->sum('delivery_fee'),
            'revenus_month'  => $base()->where('status', Order::STATUS_LIVREE)
                                    ->whereMonth('updated_at', now()->month)
                                    ->whereYear('updated_at', now()->year)
                                    ->sum('delivery_fee'),
            'livrees_today'  => $base()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', today())->count(),
        ];

        $drivers = $company->drivers()->orderBy('name')->get();

        return view('company.historique.index', compact('orders', 'stats', 'drivers', 'company', 'shopFilter'));
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

        return view('company.clients.index', compact('clients', 'stats', 'company', 'search'));
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

        return view('company.boutiques.index', compact('shops', 'stats', 'company', 'search'));
    }

    public function notifications()
    {
        $company = $this->company();

        $orders = Order::with(['shop', 'client'])
            ->where('delivery_company_id', $company->id)
            ->where('status', Order::STATUS_EN_ATTENTE)
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

        return response()->json(['ok' => true, 'orders' => $orders]);
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

        $order->update(['status' => $data['status']]);

        if ($order->driver_id) {
            $freedDriver = Driver::find($order->driver_id);
            if ($freedDriver) {
                if (in_array($data['status'], [Order::STATUS_LIVREE, Order::STATUS_ANNULEE], true)) {
                    // Libérer le chauffeur : statut selon son is_available (ne pas forcer is_available)
                    $driverIsOnline = $freedDriver->user_id
                        ? (bool) \App\Models\User::where('id', $freedDriver->user_id)->value('is_available')
                        : false;
                    $freedDriver->update(['status' => $driverIsOnline ? 'available' : 'offline']);

                } elseif ($data['status'] === Order::STATUS_EN_LIVRAISON) {
                    // L'entreprise force manuellement en_livraison → marquer le chauffeur busy
                    $freedDriver->update(['status' => 'busy']);
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

        $order->update(['status' => Order::STATUS_ANNULEE]);

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

        $count = Order::whereIn('id', $ids)
            ->where('delivery_company_id', $company->id)
            ->whereNotIn('status', [Order::STATUS_LIVREE, Order::STATUS_ANNULEE])
            ->update(['status' => Order::STATUS_ANNULEE]);

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
