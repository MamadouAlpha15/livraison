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
    public function index()
    {
        $shopId = Auth::user()->currentShopId();
        if (!$shopId) {
            return redirect()->route('employe.dashboard')
                ->with('error', 'Aucune boutique rattachée à votre compte.');
        }

        $orders = Order::with(['client', 'shop', 'livreur', 'items.product'])
            ->inShop($shopId)
            ->latest()
            ->paginate(10);

        $livreurs = User::livreurs()->inShop($shopId)->orderBy('name')->get();
        $shop     = Auth::user()->shop ?? Auth::user()->assignedShop;
        $devise   = $shop?->currency ?? 'GNF';

        // Récupérer tous les messages de la boutique
        // groupés par (client_id - product_id)
        $clientMessages = ShopMessage::where('shop_id', $shopId)
            ->with(['sender', 'receiver', 'product'])
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($m) {
                // Le client = celui dont le role est 'client'
                $clientId = optional($m->sender)->role === 'client'
                    ? $m->sender_id
                    : $m->receiver_id;
                return $clientId . '-' . ($m->product_id ?? '0');
            });

        return view('employe.orders.index', compact(
            'orders', 'livreurs', 'devise', 'shop', 'clientMessages'
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