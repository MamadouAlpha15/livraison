<?php

namespace App\Http\Controllers\Employe;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\DeliveryZone;
use App\Models\DeliveryMessage;
use App\Models\Order;
use App\Models\User;
use App\Models\ShopMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderStatusNotification;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $shopId = Auth::user()->currentShopId();
        if (!$shopId) {
            return redirect()->route('employe.dashboard')
                ->with('error', 'Aucune boutique rattachée à votre compte.');
        }

        $q = Order::with(['client', 'shop', 'livreur', 'driver', 'deliveryCompany', 'items.product'])
            ->inShop($shopId)
            ->latest();

        // Filtre recherche (client name ou order id)
        if ($search = $request->get('search')) {
            $q->where(function ($query) use ($search) {
                $query->whereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%"))
                      ->orWhere('id', is_numeric($search) ? $search : null);
            });
        }

        // Filtre statut
        if ($status = $request->get('status')) {
            $q->where('status', $status);
        }

        // Filtre date
        $dateFilter = $request->get('date', 'all');
        $dateFrom   = $request->get('from');
        $dateTo     = $request->get('to');
        match ($dateFilter) {
            'today'     => $q->whereDate('created_at', today()),
            'yesterday' => $q->whereDate('created_at', today()->subDay()),
            'week'      => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month'  => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'custom' => $q->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
                          ->when($dateTo,   fn($qq) => $qq->whereDate('created_at', '<=', $dateTo)),
            default  => null,
        };

        $orders = $q->paginate(15)->withQueryString();

        $livreurs          = User::livreurs()->inShop($shopId)->orderBy('name')->get();
        $shop              = Auth::user()->shop ?? Auth::user()->assignedShop;
        $shopCountry       = $shop?->country ?? Auth::user()->country;
        $deliveryCompanies = DeliveryCompany::where('approved', true)->where('active', true)
            ->when($shopCountry, fn($q) => $q->where('country', $shopCountry))
            ->orderBy('name')->get(['id','name','phone','image','commission_percent']);
        $devise            = $shop?->currency ?? 'GNF';

        $clientMessages = ShopMessage::where('shop_id', $shopId)
            ->with(['sender', 'receiver', 'product'])
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($m) {
                $clientId = optional($m->sender)->role === 'client'
                    ? $m->sender_id
                    : $m->receiver_id;
                return $clientId . '-' . ($m->product_id ?? '0');
            });

        return view('employe.orders.index', compact(
            'orders', 'livreurs', 'deliveryCompanies', 'devise', 'shop', 'clientMessages',
            'search', 'status', 'dateFilter', 'dateFrom', 'dateTo'
        ));
    }

    public function carte()
    {
        $shopId = Auth::user()->currentShopId();
        if (!$shopId) return redirect()->route('employe.orders.index');
        $shop = Auth::user()->shop ?? Auth::user()->assignedShop;
        return view('employe.orders.carte', compact('shop'));
    }

    public function carteData()
    {
        $shopId = Auth::user()->currentShopId();
        abort_unless($shopId, 403);

        $orders = Order::with(['client', 'driver', 'livreur'])
            ->inShop($shopId)
            ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
            ->get();

        // Clé unifiée : d_{driver_id} pour chauffeur entreprise, l_{livreur_id} pour livreur boutique
        $grouped = $orders->groupBy(function ($o) {
            if ($o->driver_id)  return 'd_' . $o->driver_id;
            if ($o->livreur_id) return 'l_' . $o->livreur_id;
            return null;
        })->filter(fn($g, $k) => $k !== null);

        $result = $grouped->map(function ($driverOrders, $key) {
            $first = $driverOrders->first();

            // Meilleure position GPS = ping le plus récent avec coordonnées réelles
            $gpsOrder = $driverOrders
                ->filter(fn($o) => $o->current_lat && $o->current_lng)
                ->sortByDesc('last_ping_at')
                ->first() ?? $first;

            if ($first->driver_id) {
                $d           = optional($first->driver);
                $driverName  = $d->name  ?? 'Chauffeur';
                $driverPhone = $d->phone ?? '';
                $type        = 'company';
            } else {
                $d           = optional($first->livreur);
                $driverName  = $d->name  ?? 'Livreur boutique';
                $driverPhone = $d->phone ?? '';
                $type        = 'boutique';
            }

            $status = $driverOrders->contains('status', Order::STATUS_EN_LIVRAISON)
                ? Order::STATUS_EN_LIVRAISON
                : Order::STATUS_CONFIRMEE;

            $ordersList = $driverOrders->map(fn($o) => [
                'id'          => $o->id,
                'client'      => optional($o->client)->name ?? '—',
                'destination' => $o->delivery_destination   ?? '',
            ])->values()->all();

            $destination = $driverOrders->pluck('delivery_destination')
                ->filter()->unique()->implode(' · ');

            // Découper en trajets : batch groupé (delivery_batch_id commun) OU commande individuelle
            $trips = $driverOrders
                ->groupBy(fn($o) => $o->delivery_batch_id ? 'batch_' . $o->delivery_batch_id : '__solo__' . $o->id)
                ->map(function ($tripOrders, $tripKey) {
                    $isBatch = str_starts_with($tripKey, 'batch_');
                    return [
                        'type'        => $isBatch ? 'batch' : 'solo',
                        'count'       => $tripOrders->count(),
                        'destination' => $tripOrders->pluck('delivery_destination')->filter()->unique()->implode(' · '),
                        'orders'      => $tripOrders->map(fn($o) => [
                            'id'          => $o->id,
                            'client'      => optional($o->client)->name ?? '—',
                            'destination' => $o->delivery_destination ?? '',
                        ])->values()->all(),
                    ];
                })->values()->all();

            return [
                'key'          => $key,
                'driver'       => $driverName,
                'driver_phone' => $driverPhone,
                'type'         => $type,
                'status'       => $status,
                'lat'          => $gpsOrder->current_lat,
                'lng'          => $gpsOrder->current_lng,
                'ping'         => $gpsOrder->last_ping_at?->toIso8601String(),
                'ping_ago'     => $gpsOrder->last_ping_at?->diffForHumans() ?? 'jamais',
                'orders'       => $ordersList,
                'order_count'  => $driverOrders->count(),
                'destination'  => $destination,
                'trips'        => $trips,
                'trip_count'   => count($trips),
            ];
        })->values();

        return response()->json(['ok' => true, 'drivers' => $result]);
    }

    public function pendingJson(Request $request)
    {
        $shopId = Auth::user()->currentShopId();
        if (!$shopId) return response()->json([]);

        $orders = Order::with(['client', 'items.product'])
            ->inShop($shopId)
            ->whereIn('status', ['en_attente', 'pending', 'en attente'])
            ->whereNull('livreur_id')
            ->whereNull('delivery_company_id')
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn($o) => [
                'id'      => $o->id,
                'num'     => '#' . str_pad($o->id, 5, '0', STR_PAD_LEFT),
                'client'  => $o->client->name ?? 'Client',
                'total'   => number_format($o->total, 0, ',', ' '),
                'address' => $o->delivery_destination ?? ($o->client?->address ?? ''),
                'photo'   => ($img = $o->items->first()?->product?->image) ? asset('storage/' . $img) : null,
            ]);

        return response()->json($orders);
    }

    public function bulkAssign(Request $request)
    {
        $shopId = Auth::user()->currentShopId();
        if (! $shopId) return response()->json(['success' => false, 'message' => 'Boutique introuvable.'], 403);

        $data = $request->validate([
            'order_ids'           => 'required|array|min:1|max:50',
            'order_ids.*'         => 'integer',
            'livreur_id'          => 'nullable|exists:users,id',
            'delivery_company_id' => 'nullable|exists:delivery_companies,id',
            'delivery_zone_id'    => 'nullable|exists:delivery_zones,id',
            'delivery_fee'        => 'nullable|numeric|min:0',
        ]);

        if (empty($data['livreur_id']) && empty($data['delivery_company_id'])) {
            return response()->json(['success' => false, 'message' => 'Aucun livreur ou entreprise sélectionné.'], 422);
        }

        $orders = Order::whereIn('id', $data['order_ids'])->where('shop_id', $shopId)->get();

        // Pré-filtrer les commandes non encore assignées
        $eligible = $orders->filter(fn($o) => !$o->livreur_id && !$o->delivery_company_id);

        // batch_id seulement si 2+ commandes → système de regroupement intentionnel
        $batchId  = $eligible->count() > 1 ? \Illuminate\Support\Str::uuid()->toString() : null;
        $assigned = 0;

        foreach ($eligible as $order) {
            if (! empty($data['livreur_id'])) {
                $order->livreur_id        = $data['livreur_id'];
                $order->delivery_batch_id = $batchId; // null pour 1 commande, UUID pour groupe
                if (in_array($order->status, ['en_attente', 'pending'])) {
                    $order->status = 'confirmée';
                }
            } else {
                $order->delivery_company_id = $data['delivery_company_id'];
                $order->delivery_batch_id   = $batchId;
                if (!empty($data['delivery_zone_id']))  $order->delivery_zone_id = $data['delivery_zone_id'];
                if (isset($data['delivery_fee']) && $data['delivery_fee'] !== null) {
                    $order->delivery_fee = $data['delivery_fee'];
                } elseif (!empty($data['delivery_zone_id'])) {
                    $order->delivery_fee = \App\Models\DeliveryZone::find($data['delivery_zone_id'])?->price;
                }
            }
            $order->save();
            $assigned++;
        }

        return response()->json(['success' => true, 'assigned' => $assigned]);
    }

    public function bulkCancel(Request $request)
    {
        $shopId = Auth::user()->currentShopId();
        if (!$shopId) return response()->json(['success' => false], 403);

        $ids = $request->validate(['order_ids' => 'required|array|min:1'])['order_ids'];

        $count = Order::whereIn('id', $ids)
            ->where('shop_id', $shopId)
            ->whereNotIn('status', ['annulée', 'livrée'])
            ->update(['status' => 'annulée']);

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function bulkRestore(Request $request)
    {
        $shopId = Auth::user()->currentShopId();
        if (!$shopId) return response()->json(['success' => false], 403);

        $ids = $request->validate(['order_ids' => 'required|array|min:1'])['order_ids'];

        $orders = Order::whereIn('id', $ids)
            ->where('shop_id', $shopId)
            ->whereIn('status', ['annulée', 'cancelled'])
            ->get();

        foreach ($orders as $order) {
            $order->update([
                'status'              => 'en_attente',
                'livreur_id'          => null,
                'delivery_company_id' => null,
                'driver_id'           => null,
                'delivery_zone_id'    => null,
                'delivery_fee'        => null,
            ]);
        }

        return response()->json(['success' => true, 'count' => $orders->count()]);
    }

    public function assign(Request $request, Order $order)
    {
        $shopId = Auth::user()->currentShopId();
        abort_unless($shopId && $order->shop_id === $shopId, 403, 'Commande hors de votre boutique.');

        $data    = $request->validate(['livreur_id' => ['required', 'exists:users,id']]);
        $livreur = User::livreurs()->inShop($shopId)->findOrFail($data['livreur_id']);

        $order->livreur_id = $livreur->id;
        if ($order->status === 'en_attente') {
            $order->status = 'confirmée';
        }
        $order->save();

        if (method_exists($livreur, 'notify')) {
            $livreur->notify(new OrderStatusNotification($order, 'Une nouvelle commande vous a été assignée.'));
        }

        return back()->with('success', 'Commande assignée au livreur.');
    }

    public function sendToCompany(Request $request, Order $order)
    {
        $shopId = Auth::user()->currentShopId();
        abort_unless($shopId && $order->shop_id === $shopId, 403, 'Commande hors de votre boutique.');

        $data = $request->validate([
            'delivery_company_id' => ['required', 'exists:delivery_companies,id'],
            'delivery_zone_id'    => ['nullable', 'exists:delivery_zones,id'],
            'delivery_fee'        => ['nullable', 'numeric', 'min:0'],
        ]);

        $company = DeliveryCompany::where('id', $data['delivery_company_id'])
            ->where('approved', true)
            ->where('active', true)
            ->firstOrFail();

        $fee = $data['delivery_fee'] ?? null;
        if (!$fee && !empty($data['delivery_zone_id'])) {
            $fee = DeliveryZone::find($data['delivery_zone_id'])?->price;
        }

        $shopCountry  = $order->shop?->country ?? null;
        $shopCurrency = $shopCountry
            ? \App\Models\DeliveryCompany::currencyForCountry($shopCountry)
            : ($company->currency ?? 'GNF');

        $order->update([
            'delivery_company_id' => $company->id,
            'delivery_zone_id'    => $data['delivery_zone_id'] ?? null,
            'delivery_fee'        => $fee,
            'livreur_id'          => null,
            'driver_id'           => null,
            'status'              => Order::STATUS_EN_ATTENTE,
        ]);

        // Message automatique dans le chat pour notifier l'entreprise
        \App\Models\DeliveryMessage::create([
            'delivery_company_id' => $company->id,
            'shop_id'             => $shopId,
            'sender_id'           => Auth::id(),
            'sender_role'         => 'shop',
            'message'             => "📦 Nouvelle commande #" . str_pad($order->id, 5, '0', STR_PAD_LEFT)
                . " confiée à votre entreprise.\n"
                . "Client : " . ($order->client->name ?? '—')
                . " · " . ($order->client->phone ?? '')
                . "\nDestination : " . ($order->delivery_destination ?: ($order->client->address ?? 'Non renseignée'))
                . (!empty($data['delivery_zone_id']) ? "\nZone : " . (DeliveryZone::find($data['delivery_zone_id'])?->name ?? '—') : '')
                . ($fee ? "\nFrais de livraison : " . number_format($fee, 0, ',', ' ') . " {$shopCurrency}" : '')
                . "\nMontant commande : " . number_format($order->total, 0, ',', ' ') . " {$shopCurrency}",
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Commande #$order->id confiée à {$company->name}."]);
        }

        return back()->with('success', "Commande #$order->id confiée à {$company->name}.");
    }

    public function cancel(Order $order)
    {
        $order->update(['status' => 'annulée']);
        return back()->with('success', 'Commande #' . $order->id . ' annulée.');
    }

    public function restore(Order $order)
    {
        if (!in_array($order->status, ['annulée', 'cancelled'])) {
            return back()->with('warning', 'Cette commande ne peut pas être restaurée.');
        }
        $order->update([
            'status'              => 'en_attente',
            'livreur_id'          => null,
            'delivery_company_id' => null,
            'driver_id'           => null,
            'delivery_zone_id'    => null,
            'delivery_fee'        => null,
        ]);
        return back()->with('success', 'Commande #' . $order->id . ' restaurée — prête à réassigner.');
    }

    public function rateCompany(Request $request, Order $order)
    {
        $shopId = Auth::user()->currentShopId();
        abort_unless($shopId && $order->shop_id === $shopId, 403, 'Commande hors de votre boutique.');
        abort_unless($order->status === 'livrée' && $order->delivery_company_id, 422, 'Cette commande ne peut pas être notée.');

        $existing = \App\Models\Review::where('order_id', $order->id)->where('user_id', Auth::id())->first();
        if ($existing) {
            return response()->json(['error' => 'Vous avez déjà noté cette commande.'], 422);
        }

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        \App\Models\Review::create([
            'order_id' => $order->id,
            'user_id'  => Auth::id(),
            'rating'   => $data['rating'],
            'comment'  => $data['comment'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Avis enregistré !']);
    }
}