<?php

namespace App\Services;

// ─────────────────────────────────────────────────────────────────────────────
// ShopPayoutService
// Gère le reversement aux boutiques de leur part sur les commandes payées en
// ligne (ChapChap Pay). Shopio prélève une commission (config chapchappay.
// platform_fee_percent, 1% par défaut) sur chaque paiement en ligne, puis
// reverse le reste vers le Mobile Money enregistré par la boutique.
//
// Étape 1 (actuelle) : reversement déclenché manuellement par l'admin, commande
// par commande, depuis /admin/reglements. Rien n'est automatique pour l'instant
// — le temps de vérifier que tout fonctionne correctement avec de l'argent réel.
// ─────────────────────────────────────────────────────────────────────────────

use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class ShopPayoutService
{
    public function __construct(private ChapChapPayService $chapChapPay) {}

    // ── Calcule la commission Shopio et le montant net dû à la boutique ───────
    public function computeShares(float $amountGnf): array
    {
        $feePercent = (float) config('chapchappay.platform_fee_percent', 1);
        $fee = (int) round($amountGnf * $feePercent / 100);
        $net = (int) round($amountGnf) - $fee;

        return ['fee' => $fee, 'net' => $net];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Appelé juste après confirmation d'un paiement en ligne (webhook 'success') :
    // fige la commission et le montant dû, marque le paiement "à reverser".
    // ─────────────────────────────────────────────────────────────────────────
    public function markDue(Payment $payment): void
    {
        $shares = $this->computeShares((float) $payment->amount);

        $payment->update([
            'payout_status'        => Payment::PAYOUT_DUE,
            'platform_fee_amount'  => $shares['fee'],
            'payout_amount'        => $shares['net'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Déclenche réellement le reversement vers la boutique (appelé depuis
    // l'admin). Nécessite que la boutique ait renseigné son Mobile Money.
    // ─────────────────────────────────────────────────────────────────────────
    public function sendPayout(Payment $payment): array
    {
        $shop = $payment->order?->shop;

        if (!$shop) {
            return ['success' => false, 'message' => 'Boutique introuvable pour cette commande.'];
        }

        if (!$shop->payout_wallet_type || !$shop->payout_wallet_number) {
            return ['success' => false, 'message' => "La boutique n'a pas encore renseigné son numéro Mobile Money."];
        }

        if (in_array($payment->payout_status, [Payment::PAYOUT_SENT, Payment::PAYOUT_PROCESSING], true)) {
            return ['success' => false, 'message' => 'Ce reversement est déjà en cours ou déjà effectué.'];
        }

        if (!$payment->payout_amount) {
            return ['success' => false, 'message' => 'Aucun montant dû calculé pour ce paiement.'];
        }

        $result = $this->chapChapPay->createPayoutRequest(
            amountGnf:            (float) $payment->payout_amount,
            walletType:           $shop->payout_wallet_type,
            walletAccountNumber:  $shop->payout_wallet_number,
            note:                 'Commande #' . $payment->order_id . ' — ' . $shop->name,
        );

        if ($result['success']) {
            $payment->update([
                'payout_status'    => Payment::PAYOUT_PROCESSING,
                'payout_reference' => $result['payout_request_id'],
                'payout_sent_at'   => now(),
            ]);
            Log::info("[ShopPayout] Reversement demandé pour paiement #{$payment->id} (boutique #{$shop->id}) : {$payment->payout_amount} GNF");
        } else {
            $payment->update(['payout_status' => Payment::PAYOUT_FAILED]);
            Log::error("[ShopPayout] Échec demande de reversement paiement #{$payment->id}", ['message' => $result['message'] ?? '']);
        }

        return $result;
    }
}
