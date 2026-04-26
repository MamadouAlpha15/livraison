<?php

namespace App\Http\Controllers\Employe;

use App\Http\Controllers\Controller;
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

        $q = Order::with(['client', 'shop', 'livreur', 'items.product'])
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
            'today'  => $q->whereDate('created_at', today()),
            'week'   => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month'  => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'custom' => $q->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
                          ->when($dateTo,   fn($qq) => $qq->whereDate('created_at', '<=', $dateTo)),
            default  => null,
        };

        $orders = $q->paginate(15)->withQueryString();

        $livreurs = User::livreurs()->inShop($shopId)->orderBy('name')->get();
        $shop     = Auth::user()->shop ?? Auth::user()->assignedShop;
        $devise   = $shop?->currency ?? 'GNF';

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
            'orders', 'livreurs', 'devise', 'shop', 'clientMessages',
            'search', 'status', 'dateFilter', 'dateFrom', 'dateTo'
        ));
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
        $order->update(['status' => 'pending', 'livreur_id' => null]);
        return back()->with('success', 'Commande #' . $order->id . ' restaurée — prête à réassigner.');
    }
}