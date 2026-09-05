<?php

namespace App\Http\Resources\Api\V1;

use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'description' => $this->description,
            'address'     => $this->address,
            'phone'       => $this->when($request->routeIs('api.v1.shops.show'), $this->phone),
            'country'     => $this->country,
            'currency'    => $this->currency ?? 'GNF',
            'image_url'   => $this->image ? ImageOptimizer::url($this->image, 'medium') : null,
        ];
    }
}
