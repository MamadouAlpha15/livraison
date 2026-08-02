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
