<?php

namespace App\Http\Controllers\Employe;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\OrderStatusNotification;

class OrderController extends Controller
{
    public function index()
    {
        // Récupère uniquement les commandes confirmées (prêtes à être livrées)
        $orders = Order::where('status', 'confirmed')
            ->with(['client','shop','livreur'])
            ->latest()
            ->paginate(10);

        // Récupère tous les livreurs (users avec rôle livreur)
        $livreurs = User::where('role', 'livreur')->get();

        return view('employe.orders.index', compact('orders','livreurs'));
    }

    public function assign(Request $request, Order $order)
    {
        $request->validate([
            'livreur_id' => 'required|exists:users,id'
        ]);

        $order->livreur_id = $request->livreur_id;
        $order->status = 'confirmed'; // reste confirmé jusqu'à ce que le livreur commence
        $order->save(); // Sauvegarde la mise à jour de l'ordre
        $order->livreur->notify(new OrderStatusNotification($order, 'Une nouvelle commande vous a été assignée.')); // Notifie le livreur


        return redirect()->route('employe.orders.index')
            ->with('success', 'Commande assignée au livreur avec succès.');
    }
}
