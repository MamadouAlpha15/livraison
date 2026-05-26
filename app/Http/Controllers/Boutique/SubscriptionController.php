<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    // Affiche la page de choix de plan pour la boutique
    public function upgrade(SubscriptionService $service)
    {
        $shop = Auth::user()->shop;
        abort_unless($shop, 404, 'Boutique introuvable.');

        // Passe le plan actuel à la vue pour afficher le bon état des boutons
        $currentPlan = $service->shopPlan($shop);

        return view('boutique.subscription.upgrade', compact('shop', 'currentPlan'));
    }
}
