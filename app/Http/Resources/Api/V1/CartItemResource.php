<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'product_id'    => $this->product_id,
            'variant_id'    => $this->product_variant_id,
            'product_name'  => $this->product->name,
            'variant_name'  => $this->variant?->name,
            'image_url'     => $this->product->image ? ImageOptimizer::url($this->product->image, 'thumb') : null,
            'unit_price'    => $this->unit_price,
            'quantity'      => $this->quantity,
            'subtotal'      => $this->subtotal,
            'available_stock' => $this->available_stock,
            'shop'          => [
                'id'   => $this->product->shop->id,
                'name' => $this->product->shop->name,
            ],
        ];
    }
}
