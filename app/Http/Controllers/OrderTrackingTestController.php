<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderTrackingTestController extends Controller
{
    public function simulate(Order $order)
    {
        $u = auth()->user();
        abort_unless($u, 401);

        $ok = false;
        if (in_array($u->role, ['superadmin','admin'])) $ok = true;
        if ($u->shop_id && $u->shop_id === $order->shop_id) $ok = true;
        if ($order->user_id === $u->id) $ok = true;
        if ($order->livreur_id === $u->id) $ok = true;
        if (! $ok) {
            $driver = \App\Models\Driver::where('user_id', $u->id)->first();
            if ($driver && (int) $order->driver_id === $driver->id) $ok = true;
        }
        abort_unless($ok, 403);

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

