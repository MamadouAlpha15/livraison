<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->shop; // grâce au fix du modèle, renvoie bien la boutique

        if (!$shop) {
            return redirect()->route('shop.index')
                ->with('error', 'Vous devez être rattaché à une boutique.');
        }

        $orders = Order::with(['items.product', 'client'])
            ->where('shop_id', $shop->id)
            ->latest()
            ->paginate(10);

        return view('vendeur.orders.index', compact('orders'));
    }

    public function confirm(Order $order)
    {
        $shopId = Auth::user()->shop_id;
        abort_unless($shopId && $order->shop_id == $shopId, 403, 'Action non autorisée');

        if ($order->status !== 'pending') {
            return back()->with('warning', 'Cette commande ne peut pas être confirmée.');
        }

        $order->update([
            'status' => 'confirmed',
        ]);

        return back()->with('success', 'Commande confirmée.');
    }

    public function cancel(Order $order)
    {
        $shopId = Auth::user()->shop_id;
        abort_unless($shopId && $order->shop_id == $shopId, 403, 'Action non autorisée');

        if (!in_array($order->status, ['pending','confirmed'])) {
            return back()->with('warning', 'Cette commande ne peut pas être annulée.');
        }

        $order->update([
            'status' => 'canceled',
        ]);

        return back()->with('success', 'Commande annulée.');
    }
}
