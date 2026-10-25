<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\AdCampaignPayment;
use App\Services\ChapChapPayService;
use App\Support\Campaigns\CampaignCheckout;
use App\Support\Campaigns\Payment\ChapChapCampaignGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook ChapChap Pay des paiements de campagnes (POST /payment/campaign/callback, sans CSRF,
 * vérifié par signature HMAC). Séparé des webhooks des abonnements et des commandes.
 */
class AdCampaignPaymentController extends Controller
{
    public function __construct(private ChapChapPayService $chapChapPay, private CampaignCheckout $checkout) {}

    public function callback(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $decoded = json_decode($rawBody, true);

        // ChapChap Pay envoie parfois le JSON encodé deux fois ; la signature porte alors sur le JSON interne
        // (même traitement que Payment\ChapChapPayController@callback).
        [$signedBody, $data] = is_string($decoded)
            ? [$decoded, json_decode($decoded, true) ?: []]
            : [$rawBody, $decoded ?: []];

        if (!$this->chapChapPay->verifyWebhookSignature($signedBody, $request->header('CCP-HMAC-Signature'))) {
            Log::warning('[Campagnes] Webhook : signature invalide ou absente', ['payload' => $data]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payment = $this->findPayment($data['operation_id'] ?? null, $data['order_id'] ?? null);
        if (!$payment) {
            Log::error('[Campagnes] Webhook : paiement introuvable', ['payload' => $data]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        match (ChapChapCampaignGateway::status($data['status']['code'] ?? null)) {
            AdCampaignPayment::STATUS_PAYE => $this->checkout->confirm(
                $payment,
                ChapChapCampaignGateway::operator($data),
                $data['transaction']['id'] ?? $data['transaction']['reference'] ?? null
            ),
            AdCampaignPayment::STATUS_ECHOUE => $this->checkout->fail($payment),
            default => Log::info('[Campagnes] Webhook : statut intermédiaire ignoré', ['status' => $data['status'] ?? null]),
        };

        return response()->json(['ok' => true]);
    }

    /** Par identifiant d'opération, sinon par la référence CAMP-{id} envoyée à la création. */
    private function findPayment(?string $operationId, ?string $orderId): ?AdCampaignPayment
    {
        if ($operationId && ($payment = AdCampaignPayment::where('gateway_operation_id', $operationId)->first())) {
            return $payment;
        }
        if ($orderId && preg_match('/^CAMP-(\d+)$/', $orderId, $m)) {
            $payment = AdCampaignPayment::find((int) $m[1]);
            if ($payment && $operationId && !$payment->gateway_operation_id) {
                $payment->update(['gateway_operation_id' => $operationId]);
            }
            return $payment;
        }
        return null;
    }
}
