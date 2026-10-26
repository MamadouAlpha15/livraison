<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAlert;
use Illuminate\Support\Facades\Cache;

/**
 * Alertes envoyées aux clients sur les produits qui les intéressent :
 *  - retour en stock (clients inscrits via « Me prévenir ») ;
 *  - baisse de prix (clients qui ont le produit dans leur liste de souhaits).
 * Les notifications partent après la réponse HTTP pour ne pas ralentir le vendeur.
 */
class ClientProductAlertService
{
    /** Le produit (ou une de ses variantes) vient de repasser au-dessus de zéro. */
    public function productRestocked(Product $product): void
    {
        $productId = $product->id;
        dispatch(function () use ($productId) {
            $product = Product::with('shop')->find($productId);
            if (! $product || ! $product->is_active) return;

            StockAlert::with('user')->where('product_id', $productId)->get()->each(function (StockAlert $alert) use ($product) {
                if ($alert->user) {
                    try {
                        app(PushService::class)->sendToUser(
                            $alert->user,
                            'De retour en stock',
                            $product->name . ' est de nouveau disponible' . ($product->shop ? ' chez ' . $product->shop->name : '') . '.',
                            1,
                            route('client.products.show', $product, false)
                        );
                    } catch (\Throwable $e) {}
                }
                $alert->delete(); // une alerte = une notification
            });
        })->afterResponse();
    }

    /** Le prix appliqué vient de baisser : prévenir les clients qui ont mis le produit en favori. */
    public function priceDropped(Product $product, float $oldPrice, float $newPrice): void
    {
        if ($newPrice <= 0 || $newPrice >= $oldPrice) return;

        $productId = $product->id;
        dispatch(function () use ($productId, $oldPrice, $newPrice) {
            $product = Product::with('shop')->find($productId);
            if (! $product || ! $product->is_active) return;

            $currency = $product->shop->currency ?? 'GNF';
            $pct      = (int) round((1 - $newPrice / $oldPrice) * 100);

            $product->favoritedBy()->where('role', 'client')->get()->each(function ($user) use ($product, $newPrice, $currency, $pct) {
                // Au plus une alerte de prix par client et par produit toutes les 24 h
                if (! Cache::add("price-drop:{$user->id}:{$product->id}", true, now()->addDay())) return;
                try {
                    app(PushService::class)->sendToUser(
                        $user,
                        'Baisse de prix' . ($pct > 0 ? " : -{$pct}%" : ''),
                        $product->name . ' passe à ' . number_format($newPrice, 0, ',', ' ') . ' ' . $currency . '.',
                        1,
                        route('client.products.show', $product, false)
                    );
                } catch (\Throwable $e) {}
            });
        })->afterResponse();
    }

    /** Prix appliqué avant la modification (prix flash si une vente flash était active). */
    public static function originalCurrentPrice(Product $product): float
    {
        $before = (new Product)->setRawAttributes($product->getRawOriginal());
        return $before->current_price;
    }
}
