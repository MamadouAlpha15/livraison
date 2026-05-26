<?php

namespace App\Http\Middleware;

// ─────────────────────────────────────────────────────────────────────────────
// Middleware CheckShopPlan
// Bloque l'accès aux routes réservées au Plan Pro des boutiques.
// Usage dans les routes : ->middleware('shop.plan:pro')
//
// Si la boutique est en plan gratuit → redirige vers la page d'upgrade
// avec un message explicatif.
// ─────────────────────────────────────────────────────────────────────────────

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class CheckShopPlan
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next, string $requiredPlan = 'pro')
    {
        $user = $request->user();

        // Récupère la boutique de l'utilisateur connecté
        $shop = $user?->shop ?? ($user?->shop_id ? \App\Models\Shop::find($user->shop_id) : null);

        if (!$shop) {
            return $next($request); // Pas de boutique liée → on laisse passer
        }

        $currentPlan = $this->subscriptionService->shopPlan($shop);

        // Si le plan actuel ne correspond pas au plan requis → upgrade
        if ($requiredPlan === 'pro' && $currentPlan !== 'pro') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => 'plan_required',
                    'message' => 'Cette fonctionnalité nécessite le Plan Pro.',
                    'upgrade' => route('boutique.subscription.upgrade'),
                ], 403);
            }

            return redirect()->route('boutique.subscription.upgrade')
                ->with('plan_error', 'Cette fonctionnalité est réservée au Plan Pro. Passez au Pro pour y accéder.');
        }

        return $next($request);
    }
}
