<?php

namespace App\Support\Campaigns\Payment;

use App\Models\AdCampaignPayment;
use App\Services\ChapChapPayService;

/** Paiement des campagnes par ChapChap Pay (même agrégateur que les commandes et les abonnements). */
class ChapChapCampaignGateway implements CampaignPaymentGateway
{
    public function __construct(private ChapChapPayService $chapChapPay) {}

    /** Référence envoyée à ChapChap (order_id) : retrouve le paiement si le webhook arrive avant l'enregistrement de l'opération. */
    public static function reference(AdCampaignPayment $payment): string
    {
        return 'CAMP-' . $payment->id;
    }

    public function start(AdCampaignPayment $payment, string $description, string $returnUrl, string $notifyUrl, string $cancelUrl): array
    {
        return $this->chapChapPay->createOperation(
            amountGnf:   $payment->amount_gnf,
            orderId:     self::reference($payment),
            description: $description,
            notifyUrl:   $notifyUrl,
            returnUrl:   $returnUrl,
            cancelUrl:   $cancelUrl,
        );
    }

    public function check(AdCampaignPayment $payment): array
    {
        if (!$payment->gateway_operation_id) {
            return ['status' => AdCampaignPayment::STATUS_EN_ATTENTE, 'operator' => null, 'transaction_ref' => null];
        }
        $result = $this->chapChapPay->verifyPayment($payment->gateway_operation_id);

        return [
            'status'          => $result['success'] ? self::status($result['status'] ?? null) : AdCampaignPayment::STATUS_EN_ATTENTE,
            'operator'        => self::operator($result['raw'] ?? []),
            'transaction_ref' => $result['raw']['transaction']['id'] ?? $result['raw']['transaction']['reference'] ?? null,
        ];
    }

    public function isFake(): bool
    {
        return false;
    }

    /** Code de statut ChapChap → statut du paiement de campagne. */
    public static function status(?string $code): string
    {
        return match ($code) {
            'success'                               => AdCampaignPayment::STATUS_PAYE,
            'failed', 'canceled', 'expired', 'error' => AdCampaignPayment::STATUS_ECHOUE,
            default                                 => AdCampaignPayment::STATUS_EN_ATTENTE,
        };
    }

    /** Opérateur utilisé (Orange Money ou MTN), s'il figure dans la réponse ChapChap. */
    public static function operator(array $payload): ?string
    {
        $hint = strtolower(implode(' ', array_filter([
            $payload['transaction']['payment_method'] ?? null,
            $payload['transaction']['operator'] ?? null,
            $payload['transaction']['provider'] ?? null,
            $payload['payment_method'] ?? null,
        ], 'is_string')));

        return match (true) {
            str_contains($hint, 'orange') => 'orange_money',
            str_contains($hint, 'mtn')    => 'mtn_momo',
            default                       => null,
        };
    }
}
