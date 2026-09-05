<?php

namespace App\Http\Controllers\Api\V1;

// ─────────────────────────────────────────────────────────────────────────────
// NotificationController (API v1) — reprend exactement la logique de
// Client\ShopMessageController::pollAll() (messages non lus + mises à jour de
// commandes récentes), pour alimenter la cloche de notifications de l'app,
// comme sur le site.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShopMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $client = $request->user();

        $messagesUnread = ShopMessage::where('receiver_id', $client->id)->whereNull('read_at')->count();

        $latestMessages = ShopMessage::where('receiver_id', $client->id)
            ->whereNull('read_at')
            ->with(['sender:id,name', 'product:id,name,shop_id', 'product.shop:id,name'])
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(fn ($m) => [
                'id'           => $m->id,
                'sender_name'  => $m->sender?->name ?? 'Vendeur',
                'shop_name'    => $m->product?->shop?->name,
                'product_id'   => $m->product_id,
                'product_name' => $m->product?->name,
                'body'         => Str::limit($m->body ?? '', 60),
                'created_at'   => $m->created_at->toIso8601String(),
            ]);

        $orderUpdates = Order::where('user_id', $client->id)
            ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON, Order::STATUS_LIVREE])
            ->where('updated_at', '>=', now()->subHours(48))
            ->with('shop:id,name')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get()
            ->map(fn ($o) => [
                'id'         => $o->id,
                'status'     => $o->status,
                'shop_name'  => $o->shop?->name ?? 'Boutique',
                'total'      => (float) $o->total,
                'updated_at' => $o->updated_at->toIso8601String(),
            ]);

        $orderUpdatesUnseen = Order::where('user_id', $client->id)
            ->whereIn('status', [Order::STATUS_CONFIRMEE, Order::STATUS_EN_LIVRAISON, Order::STATUS_LIVREE])
            ->where('updated_at', '>', $client->orders_badge_seen_at ?? now()->subHours(48))
            ->count();

        return response()->json([
            'data' => [
                'messages_unread'      => $messagesUnread,
                'latest_messages'      => $latestMessages,
                'order_updates'        => $orderUpdates,
                'order_updates_unseen' => $orderUpdatesUnseen,
            ],
        ]);
    }

    /** POST /api/v1/notifications/mark-orders-seen — efface le badge commandes (comme la visite de /client/orders sur le site) */
    public function markOrdersSeen(Request $request): JsonResponse
    {
        $request->user()->update(['orders_badge_seen_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
