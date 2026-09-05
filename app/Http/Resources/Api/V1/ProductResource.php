<?php

namespace App\Http\Resources\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// ProductResource — format JSON allégé pour les listes de produits (catalogue,
// recherche, produits d'une boutique). Le détail complet (variantes, galerie,
// description) est dans ProductDetailResource, utilisé seulement sur la fiche
// produit pour ne pas alourdir les listes.
// ─────────────────────────────────────────────────────────────────────────────

use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'category'       => $this->category,
            'price'          => (float) $this->price,
            'original_price' => $this->original_price ? (float) $this->original_price : null,
            'current_price'  => $this->current_price,
            'is_flash_active'          => $this->is_flash_active,
            'flash_discount_percent'   => $this->is_flash_active ? $this->flash_discount_percent : null,
            'flash_ends_at'            => $this->is_flash_active ? $this->flash_ends_at?->toIso8601String() : null,
            'stock'          => $this->when($this->stock !== null, $this->stock),
            'unit'           => $this->unit,
            'image_url'      => $this->image ? ImageOptimizer::url($this->image, 'medium') : null,
            'thumb_url'      => $this->image ? ImageOptimizer::url($this->image, 'thumb') : null,
            'shop'           => [
                'id'       => $this->shop->id,
                'name'     => $this->shop->name,
                'image_url' => $this->shop->image ? ImageOptimizer::url($this->shop->image, 'thumb') : null,
                'currency' => $this->shop->currency ?? 'GNF',
            ],
        ];
    }
}
