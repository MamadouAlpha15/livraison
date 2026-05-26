<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenuisPayService
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiSecret;
    private string $webhookSecret;
    private string $currency;
    private string $gateway;
    private bool   $sandbox;

    public function __construct()
    {
        $this->baseUrl       = config('genuispay.base_url', 'https://pay.genius.ci/api/v1/merchant');
        $this->apiKey        = config('genuispay.api_key', '');
        $this->apiSecret     = config('genuispay.api_secret', '');
        $this->webhookSecret = config('genuispay.webhook_secret', '');
        $this->currency      = config('genuispay.currency', 'XOF');
        $this->gateway       = '';
        $this->sandbox       = (bool) config('genuispay.sandbox', true);
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::withHeaders($this->headers());
        if ($this->sandbox || app()->environment('local')) {
            $client = $client->withoutVerifying();
        }
        return $client;
    }

    private function headers(): array
    {
        return [
            'X-API-Key'    => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ];
    }

    /**
     * Crée un paiement GenuisPay.
     * $paymentMethod vide = mode checkout (GenuisPay affiche tous les moyens).
     * Retourne ['success', 'reference'(MTX-...), 'checkout_url', 'raw']
     */
    public function initiatePayment(
        int    $amount,
        string $internalRef,
        string $description,
        string $customerName  = '',
        string $customerPhone = '',
        string $customerEmail = '',
        string $paymentMethod = '',
        string $successUrl    = '',
        string $errorUrl      = '',
        array  $metadata      = []
    ): array {
        try {
            $payload = [
                'amount'      => $amount,
                'currency'    => $this->currency,
                'description' => $description,
                'customer'    => array_filter([
                    'name'  => $customerName,
                    'email' => $customerEmail,
                    'phone' => $customerPhone,
                ]),
                'success_url' => $successUrl,
                'error_url'   => $errorUrl,
                'metadata'    => array_merge($metadata, ['internal_ref' => $internalRef]),
            ];

            if ($paymentMethod) {
                $payload['payment_method'] = $paymentMethod;
            }

            $response = $this->http()
                ->post($this->baseUrl . '/payments', $payload);

            $data = $response->json();

            Log::info('[GenuisPay] initiatePayment', [
                'internal_ref' => $internalRef,
                'amount'       => $amount,
                'method'       => $paymentMethod ?: 'checkout',
                'http_status'  => $response->status(),
                'response'     => $data,
            ]);

            if ($response->successful() && ($data['success'] ?? false)) {
                $tx = $data['data'] ?? [];
                return [
                    'success'      => true,
                    'reference'    => $tx['reference'] ?? '',
                    'checkout_url' => $tx['checkout_url'] ?? $tx['payment_url'] ?? '',
                    'raw'          => $tx,
                ];
            }

            $msg = $data['error']['message'] ?? ($data['message'] ?? 'Erreur lors de l\'initiation du paiement.');
            return ['success' => false, 'message' => $msg, 'raw' => $data];

        } catch (\Throwable $e) {
            Log::error('[GenuisPay] initiatePayment exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erreur de connexion à GenuisPay.'];
        }
    }

    /**
     * Vérifie une transaction via sa référence GenuisPay (MTX-...).
     * Statuts : pending | processing | completed | failed | expired | cancelled
     * Retourne ['success', 'status', 'raw']
     */
    public function verifyPayment(string $genuisPayRef): array
    {
        try {
            $response = $this->http()
                ->get($this->baseUrl . '/payments/' . $genuisPayRef);

            $data = $response->json();

            Log::info('[GenuisPay] verifyPayment', [
                'reference'  => $genuisPayRef,
                'http_status'=> $response->status(),
                'response'   => $data,
            ]);

            if ($response->successful() && ($data['success'] ?? false)) {
                return [
                    'success' => true,
                    'status'  => $data['data']['status'] ?? 'unknown',
                    'raw'     => $data['data'] ?? [],
                ];
            }

            return ['success' => false, 'status' => 'error', 'raw' => $data];

        } catch (\Throwable $e) {
            Log::error('[GenuisPay] verifyPayment exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => 'error'];
        }
    }

    /**
     * Vérifie la signature HMAC-SHA256 d'un webhook GenuisPay.
     * Format doc : HMAC-SHA256(timestamp + "." + rawJsonBody, webhookSecret)
     * Headers : X-Webhook-Signature, X-Webhook-Timestamp
     * Protection replay : rejet si timestamp > 5 minutes
     */
    public function verifyWebhookSignature(string $rawBody, string $signature, string $timestamp): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('[GenuisPay] GENUISPAY_WEBHOOK_SECRET non configuré');
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            Log::warning('[GenuisPay] Webhook timestamp trop ancien', ['timestamp' => $timestamp, 'now' => time()]);
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $rawBody, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }
}
