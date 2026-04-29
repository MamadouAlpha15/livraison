<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Order $order)
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $authorized = false;
        if (in_array($user->role, ['superadmin','admin'])) $authorized = true; // admins ont acces a tout
        if ($user->shop_id && $user->shop_id === $order->shop_id) $authorized = true; // users de la boutique
        if ($order->user_id === $user->id) $authorized = true; // client proprietaire de la commande
        if ($order->livreur_id === $user->id) $authorized = true;

        abort_unless($authorized, 403, 'Non autorisé');

        return response()->json([
            'order_id' => $order->id,
            'lat'      => $order->current_lat,
            'lng'      => $order->current_lng,
            'status'   => $order->status,
            'updated'  => optional($order->last_ping_at)?->toIso8601String(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $user = $request->user();
        abort_unless($user, 401);

        // Livreur boutique (livreur_id) OU chauffeur entreprise (driver_id)
        $authorized = $order->livreur_id === $user->id;
        if (! $authorized) {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if ($driver && (int) $order->driver_id === $driver->id) {
                $authorized = true;
            }
        }
        abort_unless($authorized, 403, 'Réservé au livreur assigné');

        $data = $request->validate([
            'lat' => ['required','numeric','between:-90,90'],
            'lng' => ['required','numeric','between:-180,180'],
        ]);

        $order->update([
            'current_lat' => $data['lat'],
            'current_lng' => $data['lng'],
            'last_ping_at'=> now(),
        ]);

        return response()->json(['ok' => true]);
    }

    
}
