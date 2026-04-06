<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Order;
use App\Models\User;
use App\Models\DeliveryCompany;

/**
 * ═══════════════════════════════════════════════════════════════
 * WelcomeController — Page d'accueil publique
 * ═══════════════════════════════════════════════════════════════
 * Injecte dans la vue welcome :
 *   $shops     → boutiques approuvées (avec nb produits)
 *   $companies → entreprises de livraison approuvées
 *   $stats     → chiffres clés pour la section hero
 */
class WelcomeController extends Controller
{
    public function index()
    {
        /* ── Boutiques approuvées pour la vitrine ── */
        $shops = Shop::where('is_approved', true)
            ->withCount('products')
            ->latest()
            ->paginate(12);

        /* ── Entreprises de livraison ── */
        $companies = DeliveryCompany::where('approved', true)
            ->where('active', true)
            ->latest()
            ->get();

        /* ── Stats sociales (compteurs animés dans le hero) ── */
        $stats = [
            'total_shops'    => Shop::where('is_approved', true)->count(),
            'total_orders'   => Order::count(),
            'total_clients'  => User::where('role', 'client')->count(),
            'total_livreurs' => User::where('role', 'livreur')->count(),
        ];

        return view('welcome', compact('shops', 'companies', 'stats'));
    }
}