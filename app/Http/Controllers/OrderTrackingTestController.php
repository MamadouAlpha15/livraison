<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderTrackingTestController extends Controller
{
    public function simulate(Order $order)
    {
        // Outil de test : il réécrit les positions GPS, donc jamais en production
        abort_if(app()->isProduction(), 404);

        $u = auth()->user();
        abort_unless($u, 401);
        abort_unless(\App\Support\OrderAccess::isStaff($u, $order), 403);

        // Point de base Conakry
        $baseLat = 9.6412;
        $baseLng = -13.5784;

        $delta = fn() => rand(-5, 5) / 1000.0; // ~100–500 m

        $order->update([
            // Position livreur
            'current_lat'  => ($order->current_lat ?? $baseLat) + $delta(),
            'current_lng'  => ($order->current_lng ?? $baseLng) + $delta(),
            'last_ping_at' => now(),
            // Position vendeur (boutique)
            'vendor_lat'                => $baseLat + $delta(),
            'vendor_lng'                => $baseLng + $delta(),
            'vendor_location_shared_at' => now(),
            // Position client
            'client_lat'                => $baseLat + $delta(),
            'client_lng'                => $baseLng + $delta(),
            'client_location_shared_at' => now(),
        ]);

        return back()->with('success', '✅ Positions simulées (livreur + vendeur + client)');
    }
}

