<?php

namespace App\Support\Campaigns\Payment;

use App\Models\AdCampaignPayment;

/**
 * Paiement Mobile Money d'une campagne (Orange Money ou MTN, choisi par le vendeur sur la page de paiement).
 * Implémentations : ChapChapCampaignGateway (réel) et FakeCampaignGateway (local, sans clé ChapChap).
 */
interface CampaignPaymentGateway
{
    /**
     * Ouvre une opération de paiement.
     * @return array{success: bool, operation_id?: string, payment_url?: string, message?: string}
     */
    public function start(AdCampaignPayment $payment, string $description, string $returnUrl, string $notifyUrl, string $cancelUrl): array;

    /**
     * Statut actuel d'une opération auprès de l'agrégateur.
     * @return array{status: 'paye'|'echoue'|'en_attente', operator: ?string, transaction_ref: ?string}
     */
    public function check(AdCampaignPayment $payment): array;

    /** Faux paiement (local) : la page de paiement simulée de Shopio remplace celle de l'agrégateur. */
    public function isFake(): bool;
}
