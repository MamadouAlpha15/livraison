<?php

namespace App\Http\Resources\Api\V1;

// Format allégé pour la liste des commandes ("Mes commandes"). Le détail
// complet (suivi livreur, position, preuve de livraison) est dans
// OrderDetailResource, utilisé seulement sur l'écran d'une commande précise.

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'status'     => $this->status,
            'total'      => (float) $this->total,
            'shop'       => [
                'id'   => $this->shop->id,
                'name' => $this->shop->name,
            ],
            'items_count' => $this->items->sum('quantity'),
            'created_at'  => $this->created_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'has_review'  => $this->relationLoaded('review') ? $this->review !== null : null,
        ];
    }
}
