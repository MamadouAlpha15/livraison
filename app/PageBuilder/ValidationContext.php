<?php

namespace App\PageBuilder;

use App\Models\Product;
use App\Models\Shop;

/**
 * Contexte de validation d'une page : la boutique du vendeur (ses produits, ses images) et le produit de la page.
 * Un réglage « produit » ou « image » n'accepte que ce qui appartient à cette boutique.
 */
class ValidationContext
{
    /** @var array<int, bool>|null produits en ligne de la boutique (lus une seule fois) */
    private ?array $productIds = null;

    public function __construct(public Shop $shop, public ?Product $product = null) {}

    public function ownsProduct(int $id): bool
    {
        $this->productIds ??= array_fill_keys(
            Product::where('shop_id', $this->shop->id)->where('is_active', true)->pluck('id')->all(), true
        );
        return isset($this->productIds[$id]);
    }

    /** Dossier des médias envoyés par cette boutique pour ses pages */
    public function mediaPrefix(): string
    {
        return 'page-builder/' . $this->shop->id . '/';
    }
}
