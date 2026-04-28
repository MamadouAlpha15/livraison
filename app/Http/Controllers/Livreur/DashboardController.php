<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $livreur = Auth::user();
        $shop    = $livreur->shop ?? $livreur->assignedShop;
        $devise  = $shop?->currency ?? 'GNF';

        // Livreur entreprise : driver lié au compte
        $driver = Driver::where('user_id', $livreur->id)->first();

        $orders = Order::with(['client', 'user', 'shop'])
            ->where(function ($q) use ($livreur, $driver) {
                $q->where('livreur_id', $livreur->id);
                if ($driver) {
                    $q->orWhere('driver_id', $driver->id);
                }
            })
            ->latest()
            ->get();

        $totalAssigned = $orders->count();
        $enCours       = $orders->whereIn('status', ['delivering', 'en_livraison', 'shipped'])->count();
        $terminees     = $orders->whereIn('status', ['delivered', 'livrée', 'completed'])->count();
        $enAttente     = $orders->whereIn('status', ['ready', 'prête', 'assigned', 'confirmée', 'en_attente'])->count();

        $totalCommission = $livreur->courierCommissions()->sum('amount');
        $recentOrders    = $orders->take(5);

        return view('dashboards.livreur', compact(
            'livreur', 'devise', 'shop',
            'totalAssigned', 'enCours', 'terminees', 'enAttente',
            'totalCommission', 'recentOrders'
        ));
    }
}
