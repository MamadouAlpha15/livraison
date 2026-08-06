<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Shop;

class PromoCodeService
{
    // Cherche un code promo valide pour cette boutique et ce sous-total
    public function findValid(string $code, Shop $shop, float $subtotal): array
    {
        $promo = PromoCode::where('shop_id', $shop->id)
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (!$promo) {
            return ['promo' => null, 'discount' => 0, 'error' => 'Code promo invalide.'];
        }

        $check = $promo->isValidFor($subtotal);
        if (!$check['ok']) {
            return ['promo' => null, 'discount' => 0, 'error' => $check['error']];
        }

        return ['promo' => $promo, 'discount' => $promo->calculateDiscount($subtotal), 'error' => null];
    }

    // Marque le code comme utilisé — appelé juste après la création de la commande
    public function redeem(PromoCode $promo): void
    {
        $promo->increment('uses_count');
    }

    // Rend l'utilisation si la commande est annulée après coup
    public function refundForCancelledOrder(Order $order): void
    {
        if (!$order->promo_code_id) {
            return;
        }

        $promo = PromoCode::find($order->promo_code_id);
        if ($promo && $promo->uses_count > 0) {
            $promo->decrement('uses_count');
        }
    }
}
