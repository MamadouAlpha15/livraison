<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'status'                => $this->status,
            // Code à donner au livreur à la remise (le client propriétaire est seul à lire cette ressource)
            'delivery_code'         => in_array($this->status, [\App\Models\Order::STATUS_LIVREE, \App\Models\Order::STATUS_ANNULEE], true) ? null : $this->delivery_code,
            'cancel_reason'         => $this->cancel_reason,
            'cancelled_by'          => $this->cancelled_by,
            'total'                 => (float) $this->total,
            'delivery_destination'  => $this->delivery_destination,
            'client_phone'          => $this->client_phone,
            'created_at'            => $this->created_at?->toIso8601String(),
            'delivered_at'          => $this->delivered_at?->toIso8601String(),
            'shop'                  => new ShopResource($this->whenLoaded('shop')),
            'items'                 => OrderItemResource::collection($this->whenLoaded('items')),
            'payment_method'        => $this->whenLoaded('payment', fn () => $this->payment?->method),
            'payment_status'        => $this->whenLoaded('payment', fn () => $this->payment?->status),
            // Suivi livraison : position du livreur en direct, si une mission est en cours.
            'livreur'               => $this->when($this->livreur_id, fn () => [
                'name'  => $this->livreur?->name,
                'phone' => $this->livreur?->phone,
            ]),
            'tracking' => $this->when($this->current_lat && $this->current_lng, fn () => [
                'lat'          => (float) $this->current_lat,
                'lng'          => (float) $this->current_lng,
                'last_ping_at' => $this->last_ping_at?->toIso8601String(),
            ]),
            'delivery_proof_photo_url' => $this->delivery_proof_photo_url,
        ];
    }
}
