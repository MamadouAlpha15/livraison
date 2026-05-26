<?php

namespace App\Http\Middleware;

// ─────────────────────────────────────────────────────────────────────────────
// Middleware CheckCompanyPlan
// Bloque l'accès aux routes réservées au Plan Business des entreprises.
// Usage dans les routes : ->middleware('company.plan:business')
// ─────────────────────────────────────────────────────────────────────────────

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;

class CheckCompanyPlan
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next, string $requiredPlan = 'business')
    {
        $user    = $request->user();
        // deliveryCompany() → company_id | ownedCompany() → user_id (propriétaire)
        $company = $user?->deliveryCompany ?? $user?->ownedCompany;

        if (!$company) {
            return $next($request);
        }

        $currentPlan = $this->subscriptionService->companyPlan($company);

        if ($requiredPlan === 'business' && $currentPlan !== 'business') {
            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => 'plan_required',
                    'message' => 'Cette fonctionnalité nécessite le Plan Business.',
                    'upgrade' => route('company.subscription.upgrade'),
                ], 403);
            }

            return redirect()->route('company.subscription.upgrade')
                ->with('plan_error', 'Cette fonctionnalité est réservée au Plan Business. Passez au Business pour y accéder.');
        }

        return $next($request);
    }
}
