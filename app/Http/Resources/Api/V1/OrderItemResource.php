<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'product_name' => $this->product?->name ?? '(produit supprimé)',
            'variant_name' => $this->variant_name,
            'image_url'    => $this->product?->image ? ImageOptimizer::url($this->product->image, 'thumb') : null,
            'price'        => (float) $this->price,
            'quantity'     => $this->quantity,
            'subtotal'     => (float) $this->price * $this->quantity,
        ];
    }
}
