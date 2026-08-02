<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Shop;
use App\Models\Subscription;
use App\Services\ChapChapPayService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChapChapPayController extends Controller
{
    public function __construct(
        private ChapChapPayService  $chapChapPay,
        private SubscriptionService $subscriptionService
    ) {}

    // ─── Affiche la page de checkout ─────────────────────────────────────────
    // GET /payment/checkout?type=shop|company&id={id}
    public function checkout(Request $request)
    {
        $type = $request->query('type');
        $id   = $request->query('id');

        [$subscriber, $plan, $amount] = $this->resolveSubscriber($type, $id);

        abort_unless($subscriber, 404);
        $this->authorizeSubscriber($subscriber);

        // Bloque si abonnement déjà actif
        if ($this->hasActiveSubscription($subscriber)) {
            $dash = $type === 'company' ? route('company.dashboard') : route('boutique.dashboard');
            return redirect($dash)->with('info', 'Vous avez déjà un abonnement actif.');
        }

        return view('payment.checkout', compact('subscriber', 'type', 'plan', 'amount'));
    }

    // ─── Initie le paiement ───────────────────────────────────────────────────
    // POST /payment/initiate
    public function initiate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:shop,company',
            'id'   => 'required|integer',
        ]);

        [$subscriber, $plan, $amount] = $this->resolveSubscriber($request->type, $request->id);

        abort_unless($subscriber, 404);
        $this->authorizeSubscriber($subscriber);

        // Double vérification : bloque si déjà actif (protection contre multi-clic)
        if ($this->hasActiveSubscription($subscriber)) {
            $dash = $request->type === 'company' ? route('company.dashboard') : route('boutique.dashboard');
            return redirect($dash)->with('info', 'Vous avez déjà un abonnement actif.');
        }

        // Annule les éventuels pending en cours pour éviter les fantômes
        Subscription::where('subscriber_type', get_class($subscriber))
            ->where('subscriber_id', $subscriber->id)
            ->where('status', 'pending')
            ->update(['status' => 'failed']);

        $internalRef = 'SUB-' . strtoupper(Str::random(8)) . '-' . now()->timestamp;

        $subscription = Subscription::create([
            'subscriber_type'   => get_class($subscriber),
            'subscriber_id'     => $subscriber->id,
            'plan'              => $plan,
            'amount'            => $amount,
            'currency'          => 'GNF',
            'payment_method'    => 'chapchappay',
            'payment_reference' => $internalRef,
            'status'            => 'pending',
        ]);

        $result = $this->chapChapPay->createOperation(
            amountGnf:   $amount,
            orderId:     $internalRef,
            description: "Abonnement Plan {$plan} — {$subscriber->name}",
            notifyUrl:   route('payment.callback'),
            returnUrl:   route('payment.success'),
            cancelUrl:   route('payment.failed'),
        );

        if (!$result['success']) {
            $subscription->update(['status' => 'failed']);
            return back()->withErrors(['payment' => $result['message'] ?? 'Erreur lors de l\'initiation du paiement.']);
        }

        // Remplace l'internal ref par l'operation_id ChapChap Pay
        $subscription->update(['payment_reference' => $result['operation_id'] ?: $internalRef]);

        return redirect($result['payment_url']);
    }

    // ─── Webhook ChapChap Pay ─────────────────────────────────────────────────
    // POST /payment/callback  (exclue du CSRF)
    // Payload E-Commerce : { order_id, operation_id, amount, description, status: {code, description}, transaction: {...} }
    public function callback(Request $request)
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('CCP-HMAC-Signature');

        $decoded = json_decode($rawBody, true);

        // ChapChap Pay envoie parfois le payload encodé deux fois en JSON (observé en
        // sandbox, en-tête User-Agent "python-requests" côté leur serveur — probablement
        // `requests.post(url, json=json.dumps(payload))` au lieu de `json=payload`).
        // Dans ce cas, décoder $rawBody une fois renvoie une chaîne (le JSON "interne"),
        // pas un tableau. Leur signature HMAC est calculée sur CE JSON interne, avant le
        // double encodage accidentel — donc c'est lui qu'il faut utiliser pour vérifier.
        if (is_string($decoded)) {
            $signedBody = $decoded;
            $data       = json_decode($decoded, true) ?: [];
        } else {
            $signedBody = $rawBody;
            $data       = $decoded ?: [];
        }

        Log::info('[ChapChapPay] Webhook reçu', ['payload' => $data]);

        if (!$this->chapChapPay->verifyWebhookSignature($signedBody, $signature)) {
            Log::warning('[ChapChapPay] Signature invalide ou absente', [
                'payload' => $data,
                'headers' => $request->headers->all(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $operationId = $data['operation_id'] ?? null;
        $orderId     = $data['order_id'] ?? null;
        $statusCode  = $data['status']['code'] ?? null;

        // Recherche par operation_id (référence stockée après initiate())
        $subscription = $operationId
            ? Subscription::where('payment_reference', $operationId)->first()
            : null;

        // Fallback : par order_id (= référence interne SUB-... si l'update n'a pas eu le temps)
        if (!$subscription && $orderId) {
            $subscription = Subscription::where('payment_reference', $orderId)->first();
            if ($subscription && $operationId) {
                $subscription->update(['payment_reference' => $operationId]);
            }
        }

        if (!$subscription) {
            Log::error('[ChapChapPay] Subscription introuvable', ['operation_id' => $operationId, 'order_id' => $orderId]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        // Idempotence : un statut success déjà traité ne doit pas être ré-appliqué,
        // mais un nouveau webhook success doit toujours pouvoir activer un abonnement
        // resté en pending/failed (retry client après un premier échec).
        if ($subscription->status === 'active') {
            return response()->json(['ok' => true, 'note' => 'already_active']);
        }

        match ($statusCode) {
            'success'                                  => $this->subscriptionService->activate($subscription, $data),
            'failed', 'canceled', 'expired', 'error'   => $this->subscriptionService->markFailed($subscription, $data),
            default => Log::info('[ChapChapPay] Statut intermédiaire ignoré', ['status' => $statusCode]),
        };

        return response()->json(['ok' => true]);
    }

    // ─── Page succès ─────────────────────────────────────────────────────────
    // GET /payment/success (return_url ChapChap Pay)
    public function success(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $subscription = $this->findLatestSubscription($user);

            // Si le webhook n'est pas encore arrivé, on vérifie directement auprès de l'API
            if ($subscription && $subscription->status === 'pending') {
                $verify = $this->chapChapPay->verifyPayment($subscription->payment_reference);
                if ($verify['success'] && $verify['status'] === 'success') {
                    $this->subscriptionService->activate($subscription, $verify['raw']);
                    $subscription->refresh();
                } elseif ($verify['success'] && in_array($verify['status'], ['failed', 'canceled', 'expired', 'error'])) {
                    $this->subscriptionService->markFailed($subscription, $verify['raw']);
                    $subscription->refresh();
                }
            }

            $isCompany = $subscription
                ? $subscription->subscriber_type === DeliveryCompany::class
                : (bool) ($user->deliveryCompany?->id ?? $user->ownedCompany?->id);

            $dashRoute = $isCompany ? route('company.dashboard') : route('boutique.dashboard');

            // Le message de succès ne doit s'afficher que si le paiement est réellement actif
            if ($subscription && $subscription->status === 'active') {
                return redirect($dashRoute)->with('payment_success', true);
            }

            return redirect($dashRoute)->with('payment_failed', true);
        }

        return redirect()->route('login');
    }

    // ─── Page échec / annulation ────────────────────────────────────────────
    // GET /payment/failed (cancel_url ChapChap Pay)
    public function failed(Request $request)
    {
        $subscription = null;
        return view('payment.failed', compact('subscription'));
    }

    // ─── Privé ───────────────────────────────────────────────────────────────
    private function resolveSubscriber(string $type, int|string $id): array
    {
        $pro = config('chapchappay.plans.pro', 150000);
        $biz = config('chapchappay.plans.business', 100000);

        return match ($type) {
            'shop'    => [Shop::find($id),            'pro',      $pro],
            'company' => [DeliveryCompany::find($id), 'business', $biz],
            default   => [null, null, null],
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

    private function hasActiveSubscription($subscriber): bool
    {
        return Subscription::where('subscriber_type', get_class($subscriber))
            ->where('subscriber_id', $subscriber->id)
            ->where('status', 'active')
            ->exists();
    }

    // Retrouve le dernier abonnement (pending ou active) de l'utilisateur connecté,
    // via sa boutique ou son entreprise de livraison.
    private function findLatestSubscription($user): ?Subscription
    {
        if ($user->shop_id) {
            $sub = Subscription::whereIn('status', ['pending', 'active'])
                ->where('subscriber_type', Shop::class)
                ->where('subscriber_id', $user->shop_id)
                ->latest()->first();
            if ($sub) return $sub;
        }

        $companyId = $user->deliveryCompany?->id ?? $user->ownedCompany?->id;
        if ($companyId) {
            return Subscription::whereIn('status', ['pending', 'active'])
                ->where('subscriber_type', DeliveryCompany::class)
                ->where('subscriber_id', $companyId)
                ->latest()->first();
        }

        return null;
    }
}
