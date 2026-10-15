<?php

namespace App\Services;

// ─────────────────────────────────────────────────────────────────────────────
// ShopPayoutService
// Gère le reversement aux boutiques de leur part sur les commandes payées en
// ligne (ChapChap Pay). La commission Shopio (config chapchappay.
// platform_fee_percent, 5% par défaut) est ajoutée EN PLUS du prix du vendeur
// et payée par le CLIENT — la boutique reçoit donc toujours l'intégralité du
// prix qu'elle a fixé, sans aucune retenue. Cette marge couvre à la fois le
// revenu de Shopio et d'éventuels frais que ChapChap Pay prélèverait lui-même
// sur le reversement (retrait) vers la boutique.
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

    // ── Calcule ce que le client doit payer (prix vendeur + commission Shopio)
    // quand il choisit de payer en ligne. Appelé AVANT le paiement, pour savoir
    // quel montant demander à ChapChap Pay. ────────────────────────────────────
    public function computeClientCharge(float $vendorAmountGnf): array
    {
        $feePercent = (float) config('chapchappay.platform_fee_percent', 5);
        $vendorAmount = (int) round($vendorAmountGnf);
        $fee = (int) round($vendorAmountGnf * $feePercent / 100);

        return ['vendor_amount' => $vendorAmount, 'fee' => $fee, 'client_charge' => $vendorAmount + $fee];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Appelé juste après confirmation d'un paiement en ligne (webhook 'success') :
    // fige la commission et le montant dû à la boutique, marque le paiement
    // "à reverser". Le montant dû = le prix de la commande (order->total),
    // intégralement — PAS une part de ce que le client a payé (qui inclut déjà
    // la commission Shopio en plus).
    // ─────────────────────────────────────────────────────────────────────────
    public function markDue(Payment $payment): void
    {
        $vendorAmount = (int) round((float) ($payment->order->total ?? 0));
        $fee = max(0, (int) round((float) $payment->amount) - $vendorAmount);

        $payment->update([
            'payout_status'        => Payment::PAYOUT_DUE,
            'platform_fee_amount'  => $fee,
            'payout_amount'        => $vendorAmount,
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
