<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PushSubscription;
use App\Models\ShopMessage;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class PushService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject'    => 'mailto:' . config('app.support_email', 'support@shopio-app.com'),
                'publicKey'  => config('app.vapid_public_key'),
                'privateKey' => config('app.vapid_private_key'),
            ],
        ]);
    }

    public function vendorBadgeCount(User $user): int
    {
        $shop = $user->shop;
        if (!$shop) return 1;

        $pendingOrders = Order::where('shop_id', $shop->id)
            ->where('status', Order::STATUS_EN_ATTENTE)
            ->count();

        $unreadMessages = ShopMessage::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return max(1, $pendingOrders + $unreadMessages);
    }

    public function sendToUser(User $user, string $title, string $body, int $badge = 0, string $url = '/'): void
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'badge' => $badge,
            'url'   => $url,
        ]);

        foreach ($subscriptions as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'publicKey'       => $sub->public_key,
                'authToken'       => $sub->auth_token,
                'contentEncoding' => 'aesgcm',
            ]);

            $this->webPush->queueNotification($subscription, $payload);
        }

        foreach ($this->webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                Log::warning('[Push] Échec envoi: ' . $report->getReason(), [
                    'endpoint' => $report->getRequest()->getUri(),
                ]);
                // Supprimer les abonnements invalides/expirés
                if ($report->isSubscriptionExpired()) {
                    $expiredEndpoint = (string) $report->getRequest()->getUri();
                    PushSubscription::where('endpoint_hash', hash('sha256', $expiredEndpoint))->delete();
                }
            }
        }
    }
}
