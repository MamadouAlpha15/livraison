<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Support\OrderAccess;

class SuiviController extends Controller
{
    /** 404 plutôt que 403 : ne pas confirmer qu'une commande existe à ce numéro. */
    private function authorizeTracking(Order $order, Request $request): void
    {
        abort_unless(OrderAccess::canTrack($order, $request), 404);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeTracking($order, $request);
        $order->load(['livreur', 'shop', 'items.product', 'review']);

        // Trouver les commandes du même groupe (batch_id commun = bulk-assignées ensemble)
        $groupOrders = collect([$order]);

        if ($order->delivery_batch_id) {
            // Commandes du même lot ET du même client : un lot regroupe souvent plusieurs clients
            $siblings = Order::with(['items.product'])
                ->where('delivery_batch_id', $order->delivery_batch_id)
                ->where('id', '!=', $order->id)
                ->where(fn ($q) => $order->user_id
                    ? $q->where('user_id', $order->user_id)
                    : $q->whereNull('user_id')->where('client_phone', $order->client_phone))
                ->get();

            if ($siblings->isNotEmpty()) {
                $groupOrders = collect([$order])->concat($siblings);
            }
        }

        return view('orders.show', compact('order', 'groupOrders'));
    }

    public function position(Request $request, Order $order)
    {
        $this->authorizeTracking($order, $request);
        return response()->json([
            'order_id' => $order->id,
            'lat'      => $order->current_lat,
            'lng'      => $order->current_lng,
            'status'   => $order->status,
            'updated'  => optional($order->last_ping_at)?->toIso8601String(),
        ]);
    }

    public function data(Request $request, Order $order)
    {
        $this->authorizeTracking($order, $request);
        $order->load(['livreur', 'shop']);

        $statusMap = [
            'en_attente'   => ['label' => 'En attente',   'ico' => '⏳', 'badge' => 'badge-attente',   'step' => 0],
            'confirmée'    => ['label' => 'Confirmée',    'ico' => '📦', 'badge' => 'badge-confirm',   'step' => 1],
            'en_livraison' => ['label' => 'En livraison', 'ico' => '🚴', 'badge' => 'badge-livraison', 'step' => 2],
            'livrée'       => ['label' => 'Livrée',       'ico' => '✅', 'badge' => 'badge-livree',    'step' => 3],
            'annulée'      => ['label' => 'Annulée',      'ico' => '❌', 'badge' => 'badge-annulee',   'step' => -1],
        ];
        $sInfo = $statusMap[$order->status] ?? $statusMap['en_attente'];

        // Livreur boutique (livreur_id → User) ou chauffeur entreprise (driver_id → Driver)
        $courier = $order->livreur ?? ($order->driver_id ? \App\Models\Driver::find($order->driver_id) : null);
        $livreur = $courier ? [
            'name'     => $courier->name,
            'initials' => $this->initials($courier->name),
            'phone'    => $courier->phone,
        ] : null;

        return response()->json([
            'status'              => $order->status,
            'status_label'        => $sInfo['label'],
            'status_ico'          => $sInfo['ico'],
            'status_badge'        => $sInfo['badge'],
            'step'                => $sInfo['step'],
            'lat'                 => $order->current_lat,
            'lng'                 => $order->current_lng,
            'updated'             => optional($order->last_ping_at)?->toIso8601String(),
            'is_delivered'        => $order->status === 'livrée',
            'is_cancelled'        => $order->status === 'annulée',
            'is_ongoing'          => $order->status === 'en_livraison',
            'livreur'             => $livreur,
            'client_lat'          => $order->client_lat,
            'client_lng'          => $order->client_lng,
            'client_location_age' => $order->client_location_shared_at
                ? now()->diffInSeconds($order->client_location_shared_at)
                : null,
            'vendor_lat'          => $order->vendor_lat,
            'vendor_lng'          => $order->vendor_lng,
        ]);
    }

    /** « Mamadou Diallo » → « MD » ; un seul mot → ses deux premières lettres. */
    private function initials(string $name): string
    {
        $parts = explode(' ', $name);
        return strtoupper(substr($parts[0], 0, 1)) . strtoupper(substr($parts[1] ?? substr($name, 1, 1), 0, 1));
    }

    public function updateClientLocation(Request $request, Order $order)
    {
        // Le client, ou la personne à qui il a partagé le lien (celle qui réceptionne le colis)
        abort_unless($request->hasValidRelativeSignature() || OrderAccess::isOwner($order), 404);
        abort_if(in_array($order->status, [Order::STATUS_LIVREE, Order::STATUS_ANNULEE], true), 422);

        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $order->update([
            'client_lat'                  => $request->lat,
            'client_lng'                  => $request->lng,
            'client_location_shared_at'   => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
