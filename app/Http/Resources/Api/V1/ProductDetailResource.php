<?php

namespace App\Http\Resources\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// ProductDetailResource — fiche produit complète (galerie, description,
// variantes) pour l'écran de détail produit uniquement.
// ─────────────────────────────────────────────────────────────────────────────

use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $gallery = json_decode($this->gallery ?? '[]', true) ?: [];

        $photos = array_values(array_filter(array_merge(
            $this->image ? [ImageOptimizer::url($this->image, 'large') ?? asset('storage/'.$this->image)] : [],
            array_map(fn ($g) => ImageOptimizer::url($g, 'large') ?? asset('storage/'.$g), $gallery)
        )));

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'category'       => $this->category,
            'price'          => (float) $this->price,
            'original_price' => $this->original_price ? (float) $this->original_price : null,
            'current_price'  => $this->current_price,
            'is_flash_active'        => $this->is_flash_active,
            'flash_discount_percent' => $this->is_flash_active ? $this->flash_discount_percent : null,
            'flash_ends_at'          => $this->is_flash_active ? $this->flash_ends_at?->toIso8601String() : null,
            'stock'          => $this->when($this->relationLoaded('variants') ? $this->variants->isEmpty() : true, $this->stock),
            'unit'           => $this->unit,
            'preparation_time' => $this->preparation_time,
            'photos'         => $photos,
            'variants'       => ProductVariantResource::collection($this->whenLoaded('variants')),
            'shop'           => new ShopResource($this->whenLoaded('shop')),
            'is_favorited'   => $this->when(isset($this->is_favorited), (bool) ($this->is_favorited ?? false)),
        ];
    }
}
