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
        $company = DeliveryCompany::where('user_id', auth()->id())->first();

        if (!$company) {
            abort(403, "Aucune entreprise de livraison liée à ce compte.");
        }

        return $company;
    }

    public function index(Request $request)
    {
        $company = $this->company();

        $query = Order::with(['client', 'shop', 'driver', 'items'])
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

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('boutique')) {
            $query->where('shop_id', $request->boutique);
        }

        $orders = $query->paginate(20)->withQueryString();

        $base = fn() => Order::where('delivery_company_id', $company->id);

        $stats = [
            'total'         => $base()->count(),
            'en_attente'    => $base()->where('status', Order::STATUS_EN_ATTENTE)->count(),
            'en_livraison'  => $base()->where('status', Order::STATUS_EN_LIVRAISON)->count(),
            'livrees_today' => $base()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', today())->count(),
            'revenus_today' => $base()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', today())->sum('delivery_fee'),
        ];

        $drivers = $company->drivers()->orderByRaw("FIELD(status,'available','busy','offline')")->orderBy('name')->get();

        return view('company.orders.index', compact('orders', 'stats', 'drivers', 'company'));
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
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'shop'         => optional($o->shop)->name    ?? '—',
                'client'       => optional($o->client)->name  ?? '—',
                'destination'  => $o->delivery_destination    ?? '',
                'driver'       => optional($o->driver)->name  ?? 'Non assigné',
                'driver_phone' => optional($o->driver)->phone ?? '',
                'status'       => $o->status,
                'lat'          => $o->current_lat,
                'lng'          => $o->current_lng,
                'ping'         => $o->last_ping_at?->toIso8601String(),
                'ping_ago'     => $o->last_ping_at?->diffForHumans() ?? 'jamais',
                'fee'          => $o->delivery_fee ?? 0,
            ]);

        return response()->json(['ok' => true, 'orders' => $orders]);
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

        $base = fn() => Order::where('delivery_company_id', $company->id);

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

        return view('company.historique.index', compact('orders', 'stats', 'drivers', 'company'));
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
}
