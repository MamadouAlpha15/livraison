<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * ── INDEX : Liste des paiements de la boutique ─────────────────
     *
     * Affiche uniquement les paiements confirmés ('payé') liés aux
     * commandes livrées ('livrée') de la boutique du vendeur connecté.
     *
     * Injecte $shop et $devise pour afficher la bonne devise partout.
     */
    public function index()
    {
        $shop = Auth::user()->shop;

        if (!$shop) {
            return redirect()->route('vendeur.dashboard')
                ->with('error', 'Vous devez avoir une boutique pour consulter vos revenus.');
        }

        /* ── Devise de la boutique ── */
        $devise = $shop->currency ?? 'GNF';

        /* ── Paiements filtrés ── */
        $payments = Payment::with('order.user')
            ->whereHas('order', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id)
                      ->where('status', 'livrée');
            })
            ->where('status', 'payé')
            ->latest()
            ->paginate(10);

        /* ── Total de la page entière (pas juste la page courante) ── */
        $totalRevenue = Payment::whereHas('order', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id)
                      ->where('status', 'livrée');
            })
            ->where('status', 'payé')
            ->sum('amount');

        return view('vendeur.payments.index', compact(
            'payments',
            'totalRevenue',
            'shop',
            'devise'
        ));
    }
}