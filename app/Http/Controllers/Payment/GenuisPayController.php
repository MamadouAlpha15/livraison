<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Shop;
use App\Models\Subscription;
use App\Services\GenuisPayService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenuisPayController extends Controller
{
    public function __construct(
        private GenuisPayService    $genuisPay,
        private SubscriptionService $subscriptionService
    ) {}

    // ─── Affiche la page de checkout ─────────────────────────────────────────
    // GET /payment/checkout?type=shop|company&id={id}
    public function checkout(Request $request)
    {
        $type = $request->query('type');
        $id   = $request->query('id');

        [$subscriber, $plan, $amountGnf, $amountXof] = $this->resolveSubscriber($type, $id);

        abort_unless($subscriber, 404);
        $this->authorizeSubscriber($subscriber);

        $amount      = $amountGnf;
        $userCountry = Auth::user()->country ?? '';
        return view('payment.checkout', compact('subscriber', 'type', 'plan', 'amount', 'amountXof', 'userCountry'));
    }

    // ─── Initie le paiement ───────────────────────────────────────────────────
    // POST /payment/initiate
    public function initiate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:shop,company',
            'id'   => 'required|integer',
        ]);

        [$subscriber, $plan, $amountGnf, $amountXof] = $this->resolveSubscriber($request->type, $request->id);

        abort_unless($subscriber, 404);
        $this->authorizeSubscriber($subscriber);

        $internalRef = 'SUB-' . strtoupper(Str::random(8)) . '-' . now()->timestamp;

        // On stocke le montant en GNF (devise locale affichée au client)
        $subscription = Subscription::create([
            'subscriber_type'   => get_class($subscriber),
            'subscriber_id'     => $subscriber->id,
            'plan'              => $plan,
            'amount'            => $amountGnf,
            'currency'          => 'GNF',
            'payment_method'    => 'genuispay',
            'payment_reference' => $internalRef,
            'status'            => 'pending',
        ]);

        $user   = Auth::user();
        // On envoie le montant en XOF à l'API GenuisPay (seule devise acceptée)
        $result = $this->genuisPay->initiatePayment(
            amount:        $amountXof,
            internalRef:   $internalRef,
            description:   "Abonnement Plan {$plan} — {$subscriber->name}",
            customerName:  $user->name  ?? '',
            customerEmail: $user->email ?? '',
            customerPhone: $user->phone ?? '',
            paymentMethod: '',
            successUrl:    route('payment.success'),
            errorUrl:      route('payment.failed'),
            metadata:      [
                'subscription_id' => $subscription->id,
                'subscriber_id'   => $subscriber->id,
                'subscriber_type' => $request->type,
                'plan'            => $plan,
            ]
        );

        if (!$result['success']) {
            $subscription->update(['status' => 'failed']);
            return back()->withErrors(['payment' => $result['message'] ?? 'Erreur lors de l\'initiation du paiement.']);
        }

        // Remplace l'internal ref par la référence GenuisPay (MTX-... ou SANDBOX_...)
        $subscription->update(['payment_reference' => $result['reference']]);

        return redirect($result['checkout_url']);
    }

    // ─── Webhook GenuisPay ───────────────────────────────────────────────────
    // POST /payment/callback  (exclue du CSRF)
    public function callback(Request $request)
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-Webhook-Signature', '');
        $timestamp = $request->header('X-Webhook-Timestamp', '');
        $event     = $request->header('X-Webhook-Event', '');

        Log::info('[GenuisPay] Webhook reçu', ['event' => $event, 'timestamp' => $timestamp]);

        if (!$this->genuisPay->verifyWebhookSignature($rawBody, $signature, $timestamp)) {
            Log::warning('[GenuisPay] Signature invalide');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload  = json_decode($rawBody, true);
        $data     = $payload['data'] ?? [];
        $genuisRef = $data['reference'] ?? null;

        if (!$genuisRef) {
            return response()->json(['error' => 'Missing reference'], 400);
        }

        // Recherche par référence GenuisPay (MTX-...)
        $subscription = Subscription::where('payment_reference', $genuisRef)->first();

        // Fallback : via metadata.internal_ref (SUB-...) si la mise à jour n'a pas eu le temps
        if (!$subscription) {
            $internalRef = $data['metadata']['internal_ref'] ?? null;
            if ($internalRef) {
                $subscription = Subscription::where('payment_reference', $internalRef)->first();
                if ($subscription) {
                    $subscription->update(['payment_reference' => $genuisRef]);
                }
            }
        }

        if (!$subscription) {
            Log::error('[GenuisPay] Subscription introuvable', ['reference' => $genuisRef]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        if ($subscription->status === 'active') {
            return response()->json(['ok' => true, 'note' => 'already_active']);
        }

        match ($event) {
            'payment.success'                          => $this->subscriptionService->activate($subscription, $data),
            'payment.failed', 'payment.cancelled',
            'payment.expired'                          => $this->subscriptionService->markFailed($subscription, $data),
            default => Log::info('[GenuisPay] Événement ignoré', ['event' => $event]),
        };

        return response()->json(['ok' => true]);
    }

    // ─── Page succès ─────────────────────────────────────────────────────────
    // GET /payment/success
    public function success(Request $request)
    {
        $user = Auth::user();

        Log::info('[Payment] Success redirect', [
            'query'   => $request->all(),
            'user_id' => $user?->id,
            'shop_id' => $user?->shop_id,
        ]);

        // Cherche la dernière subscription pending de cet utilisateur (shop ou company)
        $subscription = null;
        if ($user) {
            // Essaie d'abord via la ref GenuisPay dans les query params
            $gpRef = $request->query('reference') ?? $request->query('transaction_ref') ?? null;
            if ($gpRef) {
                $subscription = Subscription::where('payment_reference', $gpRef)
                    ->where('status', 'pending')->first();
            }
            // Fallback : dernière subscription pending de la boutique
            if (!$subscription && $user->shop_id) {
                $subscription = Subscription::where('status', 'pending')
                    ->where('subscriber_type', Shop::class)
                    ->where('subscriber_id', $user->shop_id)
                    ->latest()->first();
            }
            // Fallback : dernière subscription pending de la company
            if (!$subscription) {
                $companyId = $user->deliveryCompany?->id ?? $user->ownedCompany?->id;
                if ($companyId) {
                    $subscription = Subscription::where('status', 'pending')
                        ->where('subscriber_type', \App\Models\DeliveryCompany::class)
                        ->where('subscriber_id', $companyId)
                        ->latest()->first();
                }
            }
        }

        if ($subscription && !str_starts_with($subscription->payment_reference, 'SUB-')) {
            // GenuisPay envoie ?reference=...&status=completed dans l'URL de retour
            $gpStatus  = $request->query('status');
            $isSandbox = config('genuispay.sandbox', true);

            if ($gpStatus === 'completed' || $isSandbox) {
                // Sandbox : GenuisPay ne redirige vers success_url que si scenario=success
                // Production : on fait confiance au status=completed envoyé par GenuisPay dans l'URL
                $this->subscriptionService->activate($subscription, [
                    'source' => $isSandbox ? 'sandbox_redirect' : 'redirect_completed',
                    'status' => $gpStatus,
                ]);
                $subscription->refresh();
                Log::info('[Payment] Activation via redirect', [
                    'ref'    => $subscription->payment_reference,
                    'status' => $gpStatus,
                    'mode'   => $isSandbox ? 'sandbox' : 'production',
                ]);
            } else {
                // Fallback : vérification via API si pas de status dans l'URL
                $verify = $this->genuisPay->verifyPayment($subscription->payment_reference);
                Log::info('[Payment] Verify API result', ['verify' => $verify]);
                if ($verify['success'] && in_array($verify['status'], ['completed', 'success'])) {
                    $this->subscriptionService->activate($subscription, $verify['raw']);
                    $subscription->refresh();
                }
            }
        }

        $dashRoute = route('boutique.dashboard');
        return view('payment.success', compact('subscription', 'dashRoute'));
    }

    // ─── Page échec ──────────────────────────────────────────────────────────
    // GET /payment/failed
    public function failed(Request $request)
    {
        $subscription = null;
        return view('payment.failed', compact('subscription'));
    }

    // ─── Privé ───────────────────────────────────────────────────────────────
    private function resolveSubscriber(string $type, int|string $id): array
    {
        $rate = config('genuispay.gnf_to_xof_rate', 13.15);
        $gnfPro  = config('genuispay.plans_gnf.pro',      100000);
        $gnfBiz  = config('genuispay.plans_gnf.business', 150000);
        $xofPro  = config('genuispay.plans.pro',  (int) ceil($gnfPro  / $rate));
        $xofBiz  = config('genuispay.plans.business', (int) ceil($gnfBiz / $rate));

        return match ($type) {
            'shop'    => [Shop::find($id),            'pro',      $gnfPro, $xofPro],
            'company' => [DeliveryCompany::find($id), 'business', $gnfBiz, $xofBiz],
            default   => [null, null, null, null],
        };
    }

    private function authorizeSubscriber($subscriber): void
    {
        $user = Auth::user();
        if ($subscriber instanceof Shop) {
            abort_unless($user->shop_id === $subscriber->id || $user->role === 'superadmin', 403);
        }
        if ($subscriber instanceof DeliveryCompany) {
            $userCompanyId = $user->deliveryCompany?->id ?? $user->ownedCompany?->id;
            abort_unless($userCompanyId === $subscriber->id || $user->role === 'superadmin', 403);
        }
    }
}
