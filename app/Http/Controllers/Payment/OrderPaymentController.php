<?php

namespace App\Http\Controllers\Payment;

// ─────────────────────────────────────────────────────────────────────────────
// OrderPaymentController
// Webhook + pages de retour pour le paiement en ligne d'une COMMANDE (client),
// via ChapChap Pay. Séparé de Payment\ChapChapPayController (qui gère les
// abonnements Pro/Business) car ici l'acheteur peut être un invité sans compte.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\ChapChapPayService;
use App\Services\ShopPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderPaymentController extends Controller
{
    public function __construct(
        private ChapChapPayService $chapChapPay,
        private ShopPayoutService  $payoutService,
    ) {}

    // ─── Webhook ChapChap Pay pour un paiement de commande ────────────────────
    // POST /payment/order/callback (pas de CSRF, vérifié par signature HMAC)
    public function callback(Request $request)
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('CCP-HMAC-Signature');
        $decoded   = json_decode($rawBody, true);

        // Même contournement que ChapChapPayController::callback() : ChapChap Pay
        // envoie parfois le payload encodé deux fois en JSON.
        if (is_string($decoded)) {
            $signedBody = $decoded;
            $data       = json_decode($decoded, true) ?: [];
        } else {
            $signedBody = $rawBody;
            $data       = $decoded ?: [];
        }

        Log::info('[OrderPayment] Webhook reçu', ['payload' => $data]);

        if (!$this->chapChapPay->verifyWebhookSignature($signedBody, $signature)) {
            Log::warning('[OrderPayment] Signature invalide ou absente', ['payload' => $data]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $operationId = $data['operation_id'] ?? null;
        $orderIdRef  = $data['order_id'] ?? null; // notre référence interne (voir initiatePayment())
        $statusCode  = $data['status']['code'] ?? null;

        $payment = $operationId
            ? Payment::where('gateway_operation_id', $operationId)->first()
            : null;

        if (!$payment && $orderIdRef) {
            $payment = Payment::where('gateway_operation_id', $orderIdRef)->first();
        }

        if (!$payment) {
            Log::error('[OrderPayment] Paiement introuvable', ['operation_id' => $operationId, 'order_id' => $orderIdRef]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        $this->applyStatus($payment, $statusCode, $data);

        return response()->json(['ok' => true]);
    }

    // ─── Page de retour après paiement réussi/annulé ──────────────────────────
    // GET /payment/order/success?order={id}
    public function success(Request $request)
    {
        $order = Order::findOrFail($request->query('order'));
        $payment = $order->payment;

        // Si le webhook n'est pas encore arrivé, on vérifie directement auprès de l'API
        if ($payment && $payment->status !== 'payé' && $payment->gateway_operation_id) {
            $verify = $this->chapChapPay->verifyPayment($payment->gateway_operation_id);
            if ($verify['success']) {
                $this->applyStatus($payment, $verify['status'], $verify['raw']);
                $payment->refresh();
            }
        }

        $routeName = auth()->check() ? 'client.orders.index' : 'suivi.show';
        $routeArg  = auth()->check() ? [] : $order;

        if ($payment && $payment->status === 'payé') {
            return redirect()->route($routeName, $routeArg)
                ->with('success', 'Paiement reçu ! Votre commande est confirmée. 🎉');
        }

        return redirect()->route($routeName, $routeArg)
            ->with('info', "Le paiement n'a pas encore été confirmé. Ça peut prendre quelques instants.");
    }

    // GET /payment/order/failed?order={id}
    public function failed(Request $request)
    {
        $order = Order::find($request->query('order'));
        $routeName = auth()->check() ? 'client.orders.index' : 'suivi.show';
        $routeArg  = auth()->check() ? [] : $order;

        return redirect()->route($routeName, $routeArg)
            ->with('danger', "Le paiement en ligne a été annulé ou a échoué. Votre commande reste enregistrée, vous pouvez réessayer ou payer en espèces à la livraison.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Applique un statut ChapChap Pay à un Payment : marque payé + calcule la
    // part de la boutique (idempotent — un statut déjà appliqué ne l'est pas
    // deux fois, un webhook 'success' peut activer un paiement resté pending).
    // ─────────────────────────────────────────────────────────────────────────
    private function applyStatus(Payment $payment, ?string $statusCode, array $raw): void
    {
        if ($payment->status === 'payé') {
            return; // déjà traité
        }

        match ($statusCode) {
            'success' => (function () use ($payment) {
                $payment->update(['status' => 'payé', 'paid_at' => now()]);
                $this->payoutService->markDue($payment);
                Log::info("[OrderPayment] Paiement #{$payment->id} confirmé (commande #{$payment->order_id})");
            })(),
            'failed', 'canceled', 'expired', 'error' => $payment->update(['status' => 'en_attente']),
            default => Log::info('[OrderPayment] Statut intermédiaire ignoré', ['status' => $statusCode]),
        };
    }
}
