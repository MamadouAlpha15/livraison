<?php

namespace App\Observers;

use App\Enums\ReferralStatus;
use App\Jobs\CheckReferralActivation;
use App\Models\Order;
use App\Models\Product;
use App\Models\Referral;

/**
 * Produit créé (ou remis en ligne) ou commande créée dans une boutique parrainée encore « Inscrite » :
 * la vérification « Active » part en file d'attente. Ne bloque jamais la création du produit ou de la commande.
 */
class ReferralActivityObserver
{
    public function created(Product|Order $model): void
    {
        $this->check($model->shop_id);
    }

    public function updated(Product|Order $model): void
    {
        if ($model instanceof Product && $model->wasChanged('is_active') && $model->is_active) {
            $this->check($model->shop_id);
        }
    }

    private function check(?int $shopId): void
    {
        if (!$shopId) {
            return;
        }
        try {
            if (Referral::where('referred_shop_id', $shopId)->where('status', ReferralStatus::Registered)->exists()) {
                CheckReferralActivation::dispatch($shopId)->afterCommit();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
