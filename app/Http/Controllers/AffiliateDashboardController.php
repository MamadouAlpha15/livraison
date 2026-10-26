<?php

namespace App\Http\Controllers;

use App\Exports\CommissionsCsv;
use App\Http\Controllers\Vendeur\StorefrontController;
use App\Http\Requests\Affiliation\PayoutRequest;
use App\Models\Affiliate;
use App\Models\Shop;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Tableau de bord du parrainage : /vendeur/parrainage (propriétaire de boutique) et
 * /affilie/tableau-de-bord (affilié externe). Lien et QR code, filleuls, commissions, retraits.
 * Chacun ne voit que son propre compte (AffiliatePolicy).
 */
class AffiliateDashboardController extends Controller
{
    public function __construct(private AffiliateService $affiliates) {}

    public function index(Request $request)
    {
        $affiliate = $this->affiliate($request);
        $this->affiliates->rememberAffiliateDevice($affiliate, $request);
        $isVendor = $this->isVendor($request);

        // Notifications du parrainage lues dès l'ouverture de la page
        $request->user()->unreadNotifications()->where('data->type', 'affiliation')->update(['read_at' => now()]);

        return view($isVendor ? 'vendeur.parrainage' : 'affilie.dashboard', [
            'affiliate'   => $affiliate,
            'shop'        => $isVendor ? Shop::find($request->user()->shop_id) : null,
            'stats'       => $this->affiliates->stats($affiliate),
            'settings'    => $this->affiliates->settings(),
            'referrals'   => $affiliate->referrals()->with('shop:id,name')->latest()->paginate(10, ['*'], 'filleuls')->withQueryString(),
            'commissions' => $affiliate->commissions()->with('referral.shop:id,name')->latest()->paginate(10, ['*'], 'commissions')->withQueryString(),
            'payouts'     => $affiliate->payouts()->latest()->limit(10)->get(),
            'rewards'     => $isVendor ? $affiliate->rewards()->with('referral.shop:id,name')->latest()->limit(10)->get() : collect(),
            'qrSvg'       => StorefrontController::qrSvg($affiliate->link(), 200),
            'routes'      => $this->routeNames($request),
        ]);
    }

    public function qr(Request $request)
    {
        $affiliate = $this->affiliate($request);
        $svg = StorefrontController::qrSvg($affiliate->link(), 1024);
        abort_unless($svg, 404);

        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="qr-parrainage-' . strtolower($affiliate->code) . '.svg"',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $affiliate = $this->affiliate($request);
        return CommissionsCsv::download(
            $affiliate->commissions()->getQuery(),
            'commissions-parrainage-' . now()->format('Y-m-d') . '.csv'
        );
    }

    public function payout(PayoutRequest $request)
    {
        $affiliate = $this->affiliate($request);
        Gate::authorize('requestPayout', $affiliate);

        try {
            $payout = $this->affiliates->requestPayout($affiliate, $request->validated('method'), $request->validated('phone'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route($this->routeNames($request)['index'])
            ->with('success', 'Demande envoyée : ' . \App\Support\Money::gnf($payout->amount) . ' seront versés sur ton ' . $payout->methodLabel() . ' après vérification par l\'équipe Shopio.');
    }

    /* ── Privé ── */

    private function affiliate(Request $request): Affiliate
    {
        $user = $request->user();
        $affiliate = $this->isVendor($request)
            ? $this->affiliates->vendorAffiliateFor($user)
            : $this->affiliates->externalAffiliateFor($user);
        abort_unless($affiliate, 404);
        Gate::authorize('view', $affiliate);
        return $affiliate;
    }

    private function isVendor(Request $request): bool
    {
        return $request->routeIs('vendeur.parrainage*');
    }

    private function routeNames(Request $request): array
    {
        return $this->isVendor($request)
            ? ['index' => 'vendeur.parrainage', 'qr' => 'vendeur.parrainage.qr', 'export' => 'vendeur.parrainage.export', 'payout' => 'vendeur.parrainage.payout']
            : ['index' => 'affilie.dashboard', 'qr' => 'affilie.qr', 'export' => 'affilie.export', 'payout' => 'affilie.payout'];
    }
}
