<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class StockAlertService
{
    // Vérifie le stock d'un produit sans variantes et notifie le vendeur si besoin
    public function checkProduct(Product $product): void
    {
        if ($product->stock === null) {
            return; // stock non géré pour ce produit
        }

        if ($product->stock <= $product->low_stock_threshold) {
            if ($product->low_stock_notified_at === null) {
                $this->notify($product, $product->name, $product->stock);
                $product->forceFill(['low_stock_notified_at' => now()])->saveQuietly();
            }
        } elseif ($product->low_stock_notified_at !== null) {
            // Stock réapprovisionné : on pourra alerter à nouveau la prochaine fois
            $product->forceFill(['low_stock_notified_at' => null])->saveQuietly();
        }
    }

    // Vérifie le stock d'une variante et notifie le vendeur si besoin
    public function checkVariant(ProductVariant $variant): void
    {
        $threshold = $variant->product?->low_stock_threshold ?? 5;

        if ($variant->stock <= $threshold) {
            if ($variant->low_stock_notified_at === null) {
                $this->notify($variant->product, $variant->product->name . ' — ' . $variant->name, $variant->stock);
                $variant->forceFill(['low_stock_notified_at' => now()])->saveQuietly();
            }
        } elseif ($variant->low_stock_notified_at !== null) {
            $variant->forceFill(['low_stock_notified_at' => null])->saveQuietly();
        }
    }

    protected function notify(Product $product, string $label, int $stock): void
    {
        $owner = $product->shop?->user;
        if (!$owner) {
            return;
        }

        $body = $stock <= 0
            ? "\"{$label}\" est en rupture de stock."
            : "\"{$label}\" — plus que {$stock} en stock.";

        try {
            app(PushService::class)->sendToUser(
                $owner,
                $stock <= 0 ? '❌ Rupture de stock' : '⚠️ Stock faible',
                $body,
                0,
                '/products'
            );
        } catch (\Throwable $e) {}
    }
}
