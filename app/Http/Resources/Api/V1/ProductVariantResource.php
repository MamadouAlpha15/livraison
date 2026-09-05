<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'price'         => $this->effective_price,
            'stock'         => $this->stock,
            'out_of_stock'  => $this->out_of_stock,
            'image_url'     => $this->image_url,
        ];
    }
}
