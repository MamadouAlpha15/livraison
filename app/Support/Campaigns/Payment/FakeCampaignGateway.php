<?php

namespace App\Support\Campaigns\Payment;

use App\Models\AdCampaignPayment;

/**
 * Paiement simulé, utilisé hors production quand aucune clé ChapChap Pay n'est configurée :
 * le vendeur est envoyé sur une page Shopio où il choisit « paiement réussi » ou « échoué ».
 * Jamais utilisé en production (voir AppServiceProvider).
 */
class FakeCampaignGateway implements CampaignPaymentGateway
{
    public function start(AdCampaignPayment $payment, string $description, string $returnUrl, string $notifyUrl, string $cancelUrl): array
    {
        return [
            'success'      => true,
            'operation_id' => 'FAKE-' . $payment->id,
            'payment_url'  => route('boutique.campaigns.fake-pay', $payment),
        ];
    }

    public function check(AdCampaignPayment $payment): array
    {
        return ['status' => $payment->status, 'operator' => $payment->operator, 'transaction_ref' => $payment->transaction_ref];
    }

    public function isFake(): bool
    {
        return true;
    }
}
