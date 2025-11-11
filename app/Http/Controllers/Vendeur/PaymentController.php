<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        // Récupérer les paiements des commandes liées à la boutique du vendeur connecté
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()->route('vendeur.dashboard')->with('error', 'Vous devez avoir une boutique pour consulter vos revenus.');
        }

        $payments = Payment::whereHas('order', function($query) use ($shop) {
            $query->where('shop_id', $shop->id)
                  ->where('status', 'livrée'); // uniquement commandes livrées
        })->where('status', 'payé') // uniquement paiements confirmés
          ->latest()
          ->paginate(10);

        $totalRevenue = $payments->sum('amount');

        return view('vendeur.payments.index', compact('payments', 'totalRevenue'));
    }
}
