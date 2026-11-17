<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\ShopMessage;
use App\Models\User;
use App\Services\FollowUpService;

/**
 * Arrêt automatique des relances : un message du client (réponse ou STOP) ou une commande.
 * Ne bloque jamais l'envoi du message ni la création de la commande.
 */
class FollowUpActivityObserver
{
    public function created(ShopMessage|Order $model): void
    {
        try {
            $service = app(FollowUpService::class);
            if ($model instanceof Order) {
                $service->orderCreated($model);
                return;
            }
            if ($model->shop_id && User::whereKey($model->sender_id)->value('role') === 'client') {
                $service->clientMessaged($model);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
