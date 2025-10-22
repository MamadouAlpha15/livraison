<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $totalRevenue = Payment::where('status', 'paid')->sum('amount'); // Somme des paiements réussis
        $pendingOrders = Order::where('status', 'pending')->count();     // Commandes en attente
        $deliveringOrders = Order::where('status', 'delivering')->count();   // Commandes en cours de livraison
        $deliveredOrders = Order::where('status', 'delivered')->count();      // Commandes livrées
        
        $vendors = User::where('role', 'vendeur')->count();  // Nombre de vendeurs
        $livreurs = User::where('role', 'livreur')->count(); // Nombre de livreurs

        return view('admin.reports.index', compact(
            'totalOrders',
            'totalRevenue',
            'pendingOrders',
            'deliveringOrders',
            'deliveredOrders',
            'vendors',
            'livreurs'
        ));
    }
}
