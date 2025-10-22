<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderStatusNotification;

class OrderController extends Controller
{
    public function index()
    {
        // Commandes assignées au livreur connecté
        $orders = Order::where('livreur_id', Auth::id())->latest()->paginate(10);
        return view('livreur.orders.index', compact('orders'));
    }

    public function start(Order $order)
    {
        if ($order->livreur_id !== Auth::id()) { // Vérifie que le livreur est bien assigné à cette commande
            abort(403, 'Accès interdit');
        }

        $order->status = 'delivering';
        $order->save();
        $order->client->notify(new OrderStatusNotification($order, '🚚 Votre commande est en cours de livraison.'));


        return redirect()->route('livreur.orders.index')->with('success', 'Commande marquée comme "en livraison".');
    }

    public function complete(Order $order)
    {
        if ($order->livreur_id !== Auth::id()) { // Vérifie que le livreur est bien assigné à cette commande
            abort(403, 'Accès interdit');
        }

        $order->status = 'delivered';
        $order->save();

        // Mettre à jour paiement associé
   if ($order->payment) {
    $order->payment->status = 'paid';
    $order->payment->save();

    // Notification au client
        $order->client->notify(new OrderStatusNotification(
            $order,
            '✅ Votre commande a été livrée avec succès.'
        ));

        return redirect()->route('livreur.orders.index')->with('success', 'Commande livrée avec succès.');
    }
}
}