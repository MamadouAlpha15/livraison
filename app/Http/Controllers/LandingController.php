<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Page d'accueil de Shopio (/) : présente les fonctionnalités aux vendeurs, avec de petites démos
 * animées (création de boutique, produit avec l'IA, page produit en sections, commandes, paniers
 * abandonnés, thèmes). Le marché des clients (produits, recherche) est à /marche (route « welcome »).
 * Les chiffres affichés sont réels (base de données) et n'apparaissent qu'à partir d'un vrai volume.
 */
class LandingController extends Controller
{
    /** En dessous, les chiffres ne sont pas affichés (une vitrine de 3 boutiques n'aide personne). */
    private const STATS_MIN_SHOPS = 50;

    public function index(): View
    {
        $stats = Cache::remember('landing.stats', 600, fn () => [
            'shops'     => Shop::where('is_approved', true)->count(),
            'products'  => Product::where('is_active', true)->count(),
            'delivered' => Order::where('status', 'livrée')->count(),
        ]);

        $user = Auth::user();
        // Où envoie le bouton principal : création de boutique (nouveau), tableau de bord (vendeur), marché (client)
        $cta = match (true) {
            !$user                                         => route('register', ['role' => 'admin']),
            $user->role === 'admin' && $user->shop_id      => route('boutique.dashboard'),
            $user->role === 'admin'                        => route('boutique.shops.create'),
            default                                        => route('welcome'),
        };

        return view('landing', [
            'stats'      => $stats['shops'] >= self::STATS_MIN_SHOPS ? $stats : null,
            'trialDays'  => SubscriptionService::TRIAL_DAYS,
            'cta'        => $cta,
            'ctaLabel'   => $user && $user->role === 'admin' && $user->shop_id ? 'Mon tableau de bord' : 'Créer ma boutique',
            'user'       => $user,
        ]);
    }
}
