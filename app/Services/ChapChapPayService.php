<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChapChapPayService
{
    private string $baseUrl;
    private string $apiKey;
    private string $hmacKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('chapchappay.base_url', 'https://chapchappay.com/api'), '/');
        $this->apiKey  = config('chapchappay.api_key', '');
        $this->hmacKey = config('chapchappay.hmac_key', '');
    }

    private function sign(string $rawBody): string
    {
        return hash_hmac('sha256', $rawBody, $this->hmacKey);
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::withHeaders(['CCP-Api-Key' => $this->apiKey, 'Accept' => 'application/json']);
        // Contourne l'absence de CA bundle configuré en local (Windows/XAMPP/Laragon typiquement)
        if (app()->environment('local')) {
            $client = $client->withoutVerifying();
        }
        return $client;
    }

    /**
     * Crée une opération E-Commerce (lien de paiement) via ChapChap Pay.
     * POST /ecommerce/create
     * Retourne ['success', 'operation_id', 'payment_url', 'raw']
     */
    public function createOperation(
        float  $amountGnf,
        string $orderId,
        string $description,
        string $notifyUrl = '',
        string $returnUrl = '',
        string $cancelUrl = ''
    ): array {
        try {
            $payload = array_filter([
                'amount'      => $amountGnf,
                'order_id'    => $orderId,
                'description' => $description,
                'notify_url'  => $notifyUrl,
                'return_url'  => $returnUrl,
                'cancel_url'  => $cancelUrl,
            ], fn ($v) => $v !== '' && $v !== null);

            $body = json_encode($payload);

            $response = $this->http()
                ->withHeaders(['CCP-HMAC-Signature' => $this->sign($body)])
                ->withBody($body, 'application/json')
                ->post($this->baseUrl . '/ecommerce/create');

            $data = $response->json();

            Log::info('[ChapChapPay] createOperation', [
                'order_id'    => $orderId,
                'amount'      => $amountGnf,
                'http_status' => $response->status(),
                'response'    => $data,
            ]);

            if ($response->successful() && !empty($data['payment_url'])) {
                return [
                    'success'      => true,
                    'operation_id' => $data['operation_id'] ?? '',
                    'payment_url'  => $data['payment_url'],
                    'raw'          => $data,
                ];
            }

            $msg = $data['message'] ?? 'Erreur lors de la création du paiement.';
            return ['success' => false, 'message' => $msg, 'raw' => $data];

        } catch (\Throwable $e) {
            Log::error('[ChapChapPay] createOperation exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur de connexion à ChapChap Pay.'];
        }
    }

    /**
     * Vérifie le statut d'une opération via son operation_id.
     * GET /ecommerce/{operation_id}
     * Retourne ['success', 'status', 'raw']
     */
    public function verifyPayment(string $operationId): array
    {
        try {
            $response = $this->http()->get($this->baseUrl . '/ecommerce/' . $operationId);

            $data = $response->json();

            Log::info('[ChapChapPay] verifyPayment', [
                'operation_id' => $operationId,
                'http_status'  => $response->status(),
                'response'     => $data,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status'  => $data['status']['code'] ?? 'unknown',
                    'raw'     => $data,
                ];
            }

            return ['success' => false, 'status' => 'error', 'raw' => $data];

        } catch (\Throwable $e) {
            Log::error('[ChapChapPay] verifyPayment exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 'error'];
        }
    }

    /**
     * Envoie une demande de règlement (reversement) vers le Mobile Money d'une
     * boutique. POST /payout/{access_code}/request
     * Utilise l'agent "Règlements" dédié (config chapchappay.payout_access_code/pin),
     * distinct de l'API Key principale utilisée pour encaisser.
     * Retourne ['success', 'payout_request_id', 'status', 'raw']
     */
    public function createPayoutRequest(
        float  $amountGnf,
        string $walletType,
        string $walletAccountNumber,
        string $note = ''
    ): array {
        $accessCode = config('chapchappay.payout_access_code', '');
        $pin        = config('chapchappay.payout_pin', '');

        if (empty($accessCode) || empty($pin)) {
            Log::error('[ChapChapPay] Agent de règlement non configuré (CHAPCHAPPAY_PAYOUT_ACCESS_CODE/PIN manquants)');
            return ['success' => false, 'message' => 'Agent de règlement non configuré.'];
        }

        try {
            $payload = [
                'agent_pin'     => $pin,
                'payout_amount' => $amountGnf,
                'payout_mode'   => 'wallet_transfer',
                'payout_data'   => [
                    'wallet_type'           => $walletType,
                    'wallet_account_number' => $walletAccountNumber,
                ],
                'note' => $note,
            ];

            $body = json_encode($payload);

            $response = $this->http()
                ->withHeaders(['CCP-HMAC-Signature' => $this->sign($body)])
                ->withBody($body, 'application/json')
                ->post($this->baseUrl . '/payout/' . $accessCode . '/request');

            $data = $response->json();

            Log::info('[ChapChapPay] createPayoutRequest', [
                'wallet_type' => $walletType,
                'amount'      => $amountGnf,
                'http_status' => $response->status(),
                'response'    => $data,
            ]);

            if ($response->successful() && !empty($data['payout_request_id'])) {
                return [
                    'success'          => true,
                    'payout_request_id' => $data['payout_request_id'],
                    'status'           => $data['payout_request_status'] ?? 'new',
                    'raw'              => $data,
                ];
            }

            $msg = $data['message'] ?? 'Erreur lors de la demande de règlement.';
            return ['success' => false, 'message' => $msg, 'raw' => $data];

        } catch (\Throwable $e) {
            Log::error('[ChapChapPay] createPayoutRequest exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur de connexion à ChapChap Pay.'];
        }
    }

    /**
     * Vérifie le statut d'une demande de règlement.
     * GET /payout/{access_code}/request/{payout_request_id}
     */
    public function verifyPayoutRequest(string $payoutRequestId): array
    {
        $accessCode = config('chapchappay.payout_access_code', '');

        try {
            $response = $this->http()->get($this->baseUrl . '/payout/' . $accessCode . '/request/' . $payoutRequestId);
            $data = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status'  => $data['payout_request_status'] ?? 'unknown',
                    'raw'     => $data,
                ];
            }

            return ['success' => false, 'status' => 'error', 'raw' => $data];

        } catch (\Throwable $e) {
            Log::error('[ChapChapPay] verifyPayoutRequest exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 'error'];
        }
    }

    /**
     * Vérifie la signature HMAC-SHA256 d'un webhook ChapChap Pay.
     * En-tête CCP-HMAC-Signature = HMAC-SHA256(corps JSON brut, clé d'encryptage), hex minuscule.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        if (empty($this->hmacKey)) {
            Log::warning('[ChapChapPay] CHAPCHAPPAY_HMAC_KEY non configuré');
            return false;
        }

        if (empty($signature)) {
            Log::warning('[ChapChapPay] Webhook reçu sans signature');
            return false;
        }

        $expected = $this->sign($rawBody);
        $valid    = hash_equals($expected, $signature);

        if (!$valid) {
            Log::warning('[ChapChapPay] Signature webhook ne correspond pas', [
                'received_signature' => $signature,
                'expected_signature' => $expected,
            ]);
        }

        return $valid;
    }
}
