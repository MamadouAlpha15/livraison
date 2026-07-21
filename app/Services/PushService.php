<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PushSubscription;
use App\Models\ShopMessage;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\Messaging\InvalidArgument;
use Kreait\Firebase\Messaging\CloudMessage;

class PushService
{
    private WebPush $webPush;
    private Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject'    => 'mailto:' . config('app.support_email', 'support@shopio-app.com'),
                'publicKey'  => config('app.vapid_public_key'),
                'privateKey' => config('app.vapid_private_key'),
            ],
        ]);

        $this->messaging = $messaging;
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

    // Notifie le personnel de la boutique (propriétaire + employés/vendeurs), hors livreurs.
    // Utilisé pour "nouvelle commande" afin que les employés voient aussi l'alerte, pas seulement le propriétaire.
    public function notifyShopStaff(\App\Models\Shop $shop, string $title, string $body, string $url = '/', ?int $excludeUserId = null): void
    {
        $staff = User::where('shop_id', $shop->id)
            ->whereIn('role_in_shop', ['employe', 'vendeur'])
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->get();

        foreach ($staff as $user) {
            $this->sendToUser($user, $title, $body, $this->vendorBadgeCount($user), $url);
        }
    }

    public function sendToUser(User $user, string $title, string $body, int $badge = 0, string $url = '/'): void
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $webSubs = $subscriptions->where('type', 'webpush');
        $fcmSubs = $subscriptions->where('type', 'fcm');

        if ($webSubs->isNotEmpty()) {
            $this->sendWebPush($webSubs, $title, $body, $badge, $url);
        }

        foreach ($fcmSubs as $sub) {
            $this->sendFcm($sub, $title, $body, $badge, $url);
        }
    }

    private function sendWebPush($subscriptions, string $title, string $body, int $badge, string $url): void
    {
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

    private function sendFcm(PushSubscription $sub, string $title, string $body, int $badge, string $url): void
    {
        $message = CloudMessage::fromArray([
            'token' => $sub->endpoint,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'android' => [
                'priority' => 'high',
                'notification' => [
                    'sound'                  => 'default',
                    'default_sound'          => true,
                    'notification_priority'  => 'PRIORITY_MAX',
                    'visibility'             => 'PUBLIC',
                ],
            ],
            'data' => [
                'url'   => $url,
                'badge' => (string) $badge,
            ],
        ]);

        try {
            $this->messaging->send($message);
        } catch (NotFound|InvalidArgument $e) {
            // Jeton FCM invalide/expiré : on retire l'abonnement
            $sub->delete();
        } catch (\Throwable $e) {
            Log::warning('[Push][FCM] Échec envoi: ' . $e->getMessage(), ['user_id' => $sub->user_id]);
        }
    }
}
