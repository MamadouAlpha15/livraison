<?php

namespace App\Http\Controllers\Employe;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
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
                ->with('error','Aucune boutique rattachée à votre compte.');
        }

        // ✅ Uniquement les commandes de MA boutique (tu peux garder confirmed si tu veux)
        $orders = Order::with(['client','shop','livreur','items.product'])
            ->inShop($shopId)
            ->latest()
            ->paginate(10);

        // ✅ Uniquement MES livreurs
        $livreurs = User::livreurs()->inShop($shopId)->orderBy('name')->get();

        return view('employe.orders.index', compact('orders','livreurs'));
    }

    public function assign(Request $request, Order $order)
    {
        $shopId = Auth::user()->currentShopId();
        abort_unless($shopId && $order->shop_id === $shopId, 403, 'Commande hors de votre boutique.');

        $data = $request->validate([
            'livreur_id' => ['required','exists:users,id'],
        ]);

        // ✅ Le livreur choisi doit appartenir à MA boutique
        $livreur = User::livreurs()->inShop($shopId)->findOrFail($data['livreur_id']);

        $order->livreur_id = $livreur->id;
        if ($order->status === 'en_attente') {
            $order->status = 'confirmée';
        }
        
        $order->save();

        // (optionnel) notifier le livreur
        if (method_exists($livreur, 'notify')) {
            $livreur->notify(new OrderStatusNotification($order, 'Une nouvelle commande vous a été assignée.'));
        }

        return back()->with('success','Commande assignée au livreur.');
    }
}
