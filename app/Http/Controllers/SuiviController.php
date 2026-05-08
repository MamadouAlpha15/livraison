<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class SuiviController extends Controller
{
    public function show(Order $order)
    {
        $order->load(['livreur', 'shop', 'items.product', 'review']);

        // Trouver les commandes du même groupe (batch_id commun = bulk-assignées ensemble)
        $groupOrders = collect([$order]);

        if ($order->delivery_batch_id) {
            // Commandes du même lot : toutes celles avec le même batch_id
            $siblings = Order::with(['items.product'])
                ->where('delivery_batch_id', $order->delivery_batch_id)
                ->where('id', '!=', $order->id)
                ->get();

            if ($siblings->isNotEmpty()) {
                $groupOrders = collect([$order])->concat($siblings);
            }
        }

        return view('orders.show', compact('order', 'groupOrders'));
    }

    public function gps(Order $order){
         $order->load(['livreur', 'shop', 'items.product', 'review']);
        return view('orders.show2', compact('order'));
    }

    public function position(Order $order)
    {
        return response()->json([
            'order_id' => $order->id,
            'lat'      => $order->current_lat,
            'lng'      => $order->current_lng,
            'status'   => $order->status,
            'updated'  => optional($order->last_ping_at)?->toIso8601String(),
        ]);
    }

    public function data(Order $order)
    {
        $order->load(['livreur', 'shop']);

        $statusMap = [
            'en_attente'   => ['label' => 'En attente',   'ico' => '⏳', 'badge' => 'badge-attente',   'step' => 0],
            'confirmée'    => ['label' => 'Confirmée',    'ico' => '📦', 'badge' => 'badge-confirm',   'step' => 1],
            'en_livraison' => ['label' => 'En livraison', 'ico' => '🚴', 'badge' => 'badge-livraison', 'step' => 2],
            'livrée'       => ['label' => 'Livrée',       'ico' => '✅', 'badge' => 'badge-livree',    'step' => 3],
            'annulée'      => ['label' => 'Annulée',      'ico' => '❌', 'badge' => 'badge-annulee',   'step' => -1],
        ];
        $sInfo = $statusMap[$order->status] ?? $statusMap['en_attente'];

        $livreur = null;
        if ($order->livreur) {
            // Livreur boutique (livreur_id → User)
            $name    = $order->livreur->name;
            $parts   = explode(' ', $name);
            $livreur = [
                'name'     => $name,
                'initials' => strtoupper(substr($parts[0], 0, 1)).strtoupper(substr($parts[1] ?? substr($name, 1, 1), 0, 1)),
                'phone'    => $order->livreur->phone,
            ];
        } elseif ($order->driver_id) {
            // Chauffeur entreprise (driver_id → Driver)
            $driver = \App\Models\Driver::find($order->driver_id);
            if ($driver) {
                $name    = $driver->name;
                $parts   = explode(' ', $name);
                $livreur = [
                    'name'     => $name,
                    'initials' => strtoupper(substr($parts[0], 0, 1)).strtoupper(substr($parts[1] ?? substr($name, 1, 1), 0, 1)),
                    'phone'    => $driver->phone,
                ];
            }
        }

        return response()->json([
            'status'       => $order->status,
            'status_label' => $sInfo['label'],
            'status_ico'   => $sInfo['ico'],
            'status_badge' => $sInfo['badge'],
            'step'         => $sInfo['step'],
            'lat'          => $order->current_lat,
            'lng'          => $order->current_lng,
            'updated'      => optional($order->last_ping_at)?->toIso8601String(),
            'is_delivered' => $order->status === 'livrée',
            'is_cancelled' => $order->status === 'annulée',
            'is_ongoing'   => $order->status === 'en_livraison',
            'livreur'      => $livreur,
        ]);
    }
}
