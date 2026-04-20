<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $livreur = Auth::user();
        $shop    = $livreur->shop ?? $livreur->assignedShop;
        $devise  = $shop?->currency ?? 'GNF';

        $orders = $livreur->ordersAsLivreur()->with('user')->latest()->get();

        $totalAssigned   = $orders->count();
        $enCours         = $orders->whereIn('status', ['delivering','en_livraison','shipped'])->count();
        $terminees       = $orders->whereIn('status', ['delivered','livrée','completed'])->count();
        $enAttente       = $orders->whereIn('status', ['ready','prête','assigned'])->count();

        $totalCommission = $livreur->courierCommissions()->sum('amount');
        $recentOrders    = $orders->take(5);

        return view('dashboards.livreur', compact(
            'livreur','devise','shop',
            'totalAssigned','enCours','terminees','enAttente',
            'totalCommission','recentOrders'
        ));
    }
}
