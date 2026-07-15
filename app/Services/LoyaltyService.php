<?php

namespace App\Services;

use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    // 1 point fidélité = 1 GNF de réduction future
    const EARN_RATE = 0.001; // 0,1% du montant de la commande gagné en points

    const REFERRAL_REFERRER_BONUS = 2000; // points offerts au parrain
    const REFERRAL_REFERRED_BONUS = 1000; // points offerts au filleul (bonus de bienvenue)

    const MAX_REDEEM_RATIO = 0.5; // les points ne peuvent couvrir que 50% max d'une commande

    // Appelé quand une commande passe au statut "livrée"
    public function awardForOrder(Order $order): void
    {
        if (!$order->user_id) {
            return; // commande invité sans compte client
        }

        // Idempotence : ne jamais créditer deux fois la même commande
        $already = LoyaltyTransaction::where('order_id', $order->id)
            ->where('type', LoyaltyTransaction::TYPE_EARN)
            ->exists();
        if ($already) {
            return;
        }

        $user = $order->client ?? User::find($order->user_id);
        if (!$user) {
            return;
        }

        $points = (int) round($order->total * self::EARN_RATE);
        if ($points > 0) {
            $this->credit($user, $points, LoyaltyTransaction::TYPE_EARN, $order->id,
                "Points gagnés sur la commande n°{$order->id}");
        }

        $this->maybeRewardReferral($user, $order);
    }

    // Récompense le parrain (et le filleul) à la 1ère commande livrée du filleul
    protected function maybeRewardReferral(User $user, Order $order): void
    {
        if (!$user->referred_by || $user->referral_rewarded_at) {
            return;
        }

        // "1ère commande livrée" : vérifie qu'aucune autre commande de ce client n'a déjà été livrée
        $priorDelivered = Order::where('user_id', $user->id)
            ->where('status', Order::STATUS_LIVREE)
            ->where('id', '!=', $order->id)
            ->exists();
        if ($priorDelivered) {
            return;
        }

        $referrer = User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        $this->credit($referrer, self::REFERRAL_REFERRER_BONUS, LoyaltyTransaction::TYPE_REFERRAL_REFERRER, $order->id,
            "Bonus de parrainage — {$user->name} a passé sa première commande");

        $this->credit($user, self::REFERRAL_REFERRED_BONUS, LoyaltyTransaction::TYPE_REFERRAL_REFERRED, $order->id,
            'Bonus de bienvenue parrainage');

        $user->forceFill(['referral_rewarded_at' => now()])->save();
    }

    // Utilise des points pour obtenir une réduction (en GNF) sur une commande en cours de création
    public function redeemPoints(User $user, int $points, ?int $orderId = null): int
    {
        $points = min($points, $user->loyalty_points);
        if ($points <= 0) {
            return 0;
        }

        $this->credit($user, -$points, LoyaltyTransaction::TYPE_REDEEM, $orderId,
            'Points utilisés pour une réduction');

        return $points; // 1 point = 1 GNF de réduction
    }

    // Rembourse les points utilisés si la commande est annulée après coup
    public function refundPointsForCancelledOrder(Order $order): void
    {
        if (!$order->user_id || $order->loyalty_points_used <= 0) {
            return;
        }

        // Idempotence : ne rembourse jamais deux fois la même commande
        $already = LoyaltyTransaction::where('order_id', $order->id)
            ->where('type', LoyaltyTransaction::TYPE_REDEEM_REFUND)
            ->exists();
        if ($already) {
            return;
        }

        $user = $order->client ?? User::find($order->user_id);
        if (!$user) {
            return;
        }

        $this->credit($user, $order->loyalty_points_used, LoyaltyTransaction::TYPE_REDEEM_REFUND, $order->id,
            "Remboursement des points utilisés — commande n°{$order->id} annulée");
    }

    // Plafond de points utilisables sur une commande donnée (50% max du total)
    public function maxRedeemableFor(User $user, float $orderTotal): int
    {
        $cap = (int) floor($orderTotal * self::MAX_REDEEM_RATIO);
        return max(0, min($user->loyalty_points, $cap));
    }

    protected function credit(User $user, int $points, string $type, ?int $orderId, string $description): void
    {
        DB::transaction(function () use ($user, $points, $type, $orderId, $description) {
            $user->refresh();
            $newBalance = max(0, $user->loyalty_points + $points);

            LoyaltyTransaction::create([
                'user_id'       => $user->id,
                'order_id'      => $orderId,
                'type'          => $type,
                'points'        => $points,
                'balance_after' => $newBalance,
                'description'   => $description,
            ]);

            $user->forceFill(['loyalty_points' => $newBalance])->save();
        });
    }
}
