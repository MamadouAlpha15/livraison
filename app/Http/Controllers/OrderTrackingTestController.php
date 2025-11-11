<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderTrackingTestController extends Controller
{
     public function simulate(Order $order)
    {
        // sécurité basique : acteurs autorisés (admin/employés/vendeur de la boutique, client, livreur)
        $u = auth()->user();
        abort_unless($u, 401);

        $ok = false;
        if (in_array($u->role, ['superadmin','admin'])) $ok = true;
        if ($u->shop_id && $u->shop_id === $order->shop_id) $ok = true;
        if ($order->user_id === $u->id) $ok = true;
        if ($order->livreur_id === $u->id) $ok = true;
        abort_unless($ok, 403);

        // point de départ (fallback Conakry si vide)
        $lat = $order->current_lat ?? 9.6412;
        $lng = $order->current_lng ?? -13.5784;

        // décalage aléatoire léger (~100 à 300 mètres)
        $deltaLat = (rand(-3, 3)) / 1000.0;
        $deltaLng = (rand(-3, 3)) / 1000.0;

        $order->update([
            'current_lat' => $lat + $deltaLat,
            'current_lng' => $lng + $deltaLng,
            'last_ping_at'=> now(),
        ]);

        return back()->with('success', 'Position simulée ! Regarde la carte.');
    }
}

