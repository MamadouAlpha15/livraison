<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CourierCommission;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Order $order)
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $authorized = false;
        if (in_array($user->role, ['superadmin','admin'])) $authorized = true;
        if ($user->shop_id && $user->shop_id === $order->shop_id) $authorized = true;
        if ($order->user_id === $user->id) $authorized = true;
        if ($order->livreur_id === $user->id) $authorized = true;
        if (! $authorized) {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if ($driver && (int) $order->driver_id === $driver->id) $authorized = true;
        }

        abort_unless($authorized, 403, 'Non autorisé');

        return response()->json([
            'order_id'   => $order->id,
            'lat'        => $order->current_lat,
            'lng'        => $order->current_lng,
            'status'     => $order->status,
            'updated'    => optional($order->last_ping_at)?->toIso8601String(),
            'client_lat' => $order->client_lat,
            'client_lng' => $order->client_lng,
            'vendor_lat' => $order->vendor_lat,
            'vendor_lng' => $order->vendor_lng,
        ]);
    }

    public function nav(Order $order)
    {
        $user = auth()->user();
        abort_unless($user, 401);

        // Autorisé : livreur boutique, chauffeur entreprise, admin, vendeur de la boutique
        $authorized = in_array($user->role, ['superadmin', 'admin'])
            || ($user->shop_id && $user->shop_id === $order->shop_id)
            || $order->livreur_id === $user->id;

        if (! $authorized) {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if ($driver && (int) $order->driver_id === $driver->id) {
                $authorized = true;
            }
        }
        abort_unless($authorized, 403);

        $order->load(['shop', 'client']);

        // Phase active : avant en_livraison = aller chercher chez le vendeur
        //                en_livraison       = livrer au client
        $phase = ($order->status === Order::STATUS_EN_LIVRAISON) ? 2 : 1;

        return view('orders.nav', compact('order', 'phase'));
    }

    public function update(Request $request, Order $order)
    {
        $user = $request->user();
        abort_unless($user, 401);

        // Livreur boutique (livreur_id) OU chauffeur entreprise (driver_id)
        $authorized = $order->livreur_id === $user->id;
        $driver = null;
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

        $gpsData = [
            'current_lat'  => $data['lat'],
            'current_lng'  => $data['lng'],
            'last_ping_at' => now(),
        ];

        // Mettre à jour l'order ciblé
        $order->update($gpsData);

        // Propager le GPS aux autres commandes du même batch (livraison groupée intentionnelle)
        // → batch_id commun = livreur porte toutes ces commandes en 1 trajet
        if ($order->delivery_batch_id) {
            Order::where('delivery_batch_id', $order->delivery_batch_id)
                ->where('id', '!=', $order->id)
                ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
                ->update($gpsData);
        } elseif ($driver) {
            // Livreur entreprise (driver) sans batch : propager à ses autres commandes actives
            Order::where('driver_id', $driver->id)
                ->where('id', '!=', $order->id)
                ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
                ->update($gpsData);
        }
        // Assignation individuelle sans batch → GPS uniquement sur cette commande (pas de propagation)

        return response()->json(['ok' => true]);
    }

    public function driverUpdateStatus(Request $request, Order $order)
    {
        $user = $request->user();
        abort_unless($user, 401);

        // Seul le chauffeur assigné peut changer le statut
        $authorized = $order->livreur_id === $user->id;
        $driver = null;
        if (! $authorized) {
            $driver = \App\Models\Driver::where('user_id', $user->id)->first();
            if ($driver && (int) $order->driver_id === $driver->id) {
                $authorized = true;
            }
        }
        abort_unless($authorized, 403, 'Réservé au chauffeur assigné.');

        $data = $request->validate([
            'status' => ['required', 'in:en_livraison,livrée'],
        ]);

        abort_unless(
            ! in_array($order->status, [Order::STATUS_LIVREE, Order::STATUS_ANNULEE]),
            422,
            'Commande déjà terminée.'
        );

        $order->update(['status' => $data['status']]);

        // Propager le changement de statut aux commandes du même batch (même trajet)
        if ($order->delivery_batch_id) {
            $siblings = Order::where('delivery_batch_id', $order->delivery_batch_id)
                ->where('id', '!=', $order->id)
                ->whereNotIn('status', [Order::STATUS_LIVREE, Order::STATUS_ANNULEE]);

            if ($data['status'] === Order::STATUS_EN_LIVRAISON) {
                (clone $siblings)->where('status', Order::STATUS_CONFIRMEE)
                    ->update(['status' => Order::STATUS_EN_LIVRAISON]);
            } elseif ($data['status'] === Order::STATUS_LIVREE) {
                // Mass-update bypass le mutateur setStatusAttribute → delivered_at doit être explicite
                $siblings->update([
                    'status'       => Order::STATUS_LIVREE,
                    'delivered_at' => now(),
                ]);
            }
        }

        // Créer les commissions livreur quand statut → livrée
        if ($data['status'] === Order::STATUS_LIVREE) {
            $batchId = $order->delivery_batch_id;

            // Toutes les commandes concernées par ce trajet (order principal + siblings batch)
            $allOrders = $batchId
                ? Order::where('delivery_batch_id', $batchId)->get()
                : collect([$order]);

            foreach ($allOrders as $o) {
                // Éviter les doublons
                $exists = $batchId
                    ? CourierCommission::where('delivery_batch_id', $batchId)->exists()
                    : CourierCommission::where('order_id', $o->id)->exists();
                if ($exists) continue;

                // Montant : delivery_fee ou 0 (la boutique pourra le saisir manuellement)
                $isMultiClient = $batchId && $allOrders->pluck('user_id')->unique()->count() > 1;
                $commAmount = $isMultiClient
                    ? (float) $allOrders->sum('delivery_fee')
                    : (float) ($o->delivery_fee ?? 0);

                CourierCommission::create([
                    'order_id'          => $o->id,
                    'livreur_id'        => $o->livreur_id ?? $user->id,
                    'shop_id'           => $o->shop_id,
                    'delivery_batch_id' => $batchId,
                    'order_total'       => $o->total,
                    'rate'              => 0,
                    'amount'            => $commAmount,
                    'status'            => 'en_attente',
                ]);

                // Une seule commission pour tout le batch
                if ($batchId) break;
            }
        }

        // Libérer le chauffeur si livraison terminée
        if ($data['status'] === Order::STATUS_LIVREE && $driver) {
            $otherActive = Order::where('driver_id', $driver->id)
                ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON])
                ->where('id', '!=', $order->id)
                ->exists();
            if (! $otherActive) {
                $driver->update(['status' => 'available']);
            }
        } elseif ($data['status'] === Order::STATUS_EN_LIVRAISON && $driver) {
            $driver->update(['status' => 'busy']);
        }

        return response()->json(['ok' => true, 'status' => $data['status']]);
    }
}
