<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AffiliateStatus;
use App\Enums\CommissionStatus;
use App\Enums\PayoutStatus;
use App\Enums\ReferralStatus;
use App\Exports\CommissionsCsv;
use App\Http\Controllers\Controller;
use App\Http\Requests\Affiliation\AffiliateSettingsRequest;
use App\Http\Requests\Affiliation\StoreAffiliateRequest;
use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Payout;
use App\Models\Referral;
use App\Models\ReferralClick;
use App\Services\AffiliateService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Équipe Shopio → Affiliation : affiliés (vendeurs et externes), boutiques parrainées à vérifier,
 * commissions (export CSV, remboursement), retraits à verser et réglages du programme.
 */
class AffiliationController extends Controller
{
    public function __construct(private AffiliateService $affiliates) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Affiliate::class);

        $commissionSums = Commission::selectRaw('status, sum(amount) as total')->groupBy('status')->pluck('total', 'status');
        $summary = [
            'affiliates'   => Affiliate::where('status', AffiliateStatus::Active)->count(),
            'toValidate'   => Affiliate::where('status', AffiliateStatus::Pending)->count(),
            'clicks30'     => ReferralClick::where('created_at', '>=', now()->subDays(30))->count(),
            'referrals'    => Referral::where('status', '!=', ReferralStatus::Cancelled)->count(),
            'paying'       => Referral::where('status', ReferralStatus::Paying)->count(),
            'pending'      => (int) ($commissionSums[CommissionStatus::Pending->value] ?? 0),
            'approved'     => (int) ($commissionSums[CommissionStatus::Approved->value] ?? 0),
            'paid'         => (int) ($commissionSums[CommissionStatus::Paid->value] ?? 0),
            'flagged'      => Referral::where('flagged', true)->where('status', '!=', ReferralStatus::Cancelled)->count(),
            'payouts'      => Payout::where('status', PayoutStatus::Requested)->count(),
            'payoutsTotal' => (int) Payout::where('status', PayoutStatus::Requested)->sum('amount'),
        ];

        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type');
        $status = $request->query('statut');
        $affiliates = Affiliate::with('user:id,name,email,phone,shop_id')
            ->withCount(['referrals', 'referrals as paying_count' => fn ($r) => $r->where('status', ReferralStatus::Paying)])
            ->withSum(['commissions as earned' => fn ($c) => $c->whereIn('status', [CommissionStatus::Approved, CommissionStatus::Paid])], 'amount')
            ->when(in_array($type, ['vendor', 'external'], true), fn ($query) => $query->where('type', $type))
            ->when(in_array($status, array_column(AffiliateStatus::cases(), 'value'), true), fn ($query) => $query->where('status', $status))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('code', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))))
            // Demandes à valider en premier
            ->orderByRaw('case when status = ? then 0 else 1 end', [AffiliateStatus::Pending->value])
            ->latest()->paginate(20)->withQueryString();

        return view('admin.affiliation.index', compact('summary', 'affiliates', 'q', 'type', 'status'));
    }

    public function storeAffiliate(StoreAffiliateRequest $request)
    {
        $affiliate = $this->affiliates->createExternal($request->validated(), $request->boolean('with_access'));

        return redirect()->route('admin.affiliation.index')->with('success', 'Affilié « ' . $affiliate->displayName() . ' » créé avec le code ' . $affiliate->code . '.'
            . ($request->boolean('with_access') ? ' Il a reçu un e-mail pour choisir son mot de passe.' : ''));
    }

    public function updateAffiliate(Request $request, Affiliate $affiliate)
    {
        Gate::authorize('update', $affiliate);
        $data = $request->validate([
            'status'          => ['required', Rule::enum(AffiliateStatus::class)],
            'commission_rate' => ['required', 'numeric', 'between:0,100'],
        ]);
        $affiliate->update($data);

        return back()->with('success', 'Affilié « ' . $affiliate->displayName() . ' » mis à jour.');
    }

    public function approveAffiliate(Affiliate $affiliate)
    {
        Gate::authorize('update', $affiliate);
        $this->affiliates->approveAffiliate($affiliate);
        return back()->with('success', '« ' . $affiliate->displayName() . ' » est validé : son lien ' . $affiliate->code . ' fonctionne et il a été prévenu.');
    }

    public function rejectAffiliate(Request $request, Affiliate $affiliate)
    {
        Gate::authorize('update', $affiliate);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);
        $this->affiliates->rejectAffiliate($affiliate, $data['reason'] ?? null);
        return back()->with('success', 'Demande de « ' . $affiliate->displayName() . ' » refusée. Il a été prévenu.');
    }

    public function referrals(Request $request)
    {
        Gate::authorize('viewAny', Affiliate::class);
        $filter = $request->query('filtre', 'a-verifier');

        $referrals = Referral::with(['shop:id,name,email,phone,created_at', 'affiliate.user:id,name,shop_id'])
            ->when($filter === 'a-verifier', fn ($q) => $q->where('flagged', true)->where('status', '!=', ReferralStatus::Cancelled))
            ->when(in_array($filter, array_column(ReferralStatus::cases(), 'value'), true), fn ($q) => $q->where('status', $filter))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.affiliation.referrals', compact('referrals', 'filter'));
    }

    public function clearFlag(Referral $referral)
    {
        Gate::authorize('update', $referral);
        $this->affiliates->clearFlag($referral);
        return back()->with('success', 'Parrainage de « ' . $referral->shop?->name . ' » vérifié : ses commissions suivent leur cours.');
    }

    public function cancelReferral(Referral $referral)
    {
        Gate::authorize('update', $referral);
        $this->affiliates->cancelReferral($referral);
        return back()->with('success', 'Parrainage de « ' . $referral->shop?->name . ' » annulé, commissions non versées annulées.');
    }

    public function commissions(Request $request)
    {
        Gate::authorize('viewAny', Commission::class);
        $filter = $request->query('statut');

        $commissions = $this->commissionQuery($filter)
            ->with(['referral.shop:id,name', 'affiliate.user:id,name,shop_id', 'subscriptionPayment:id,amount,refunded_at'])
            ->latest()->paginate(25)->withQueryString();

        return view('admin.affiliation.commissions', compact('commissions', 'filter'));
    }

    public function exportCommissions(Request $request)
    {
        Gate::authorize('viewAny', Commission::class);
        return CommissionsCsv::download($this->commissionQuery($request->query('statut')),
            'commissions-affiliation-' . now()->format('Y-m-d') . '.csv', true);
    }

    /** Le paiement de l'abonnement a été remboursé à la boutique : la commission liée est annulée */
    public function refund(Commission $commission, SubscriptionService $subscriptions)
    {
        Gate::authorize('update', $commission);
        abort_unless($commission->subscriptionPayment, 404);
        if ($commission->status === CommissionStatus::Paid) {
            return back()->withErrors(['refund' => 'Cette commission a déjà été versée : elle ne peut plus être annulée ici.']);
        }

        $subscriptions->markRefunded($commission->subscriptionPayment);
        return back()->with('success', 'Remboursement enregistré : la commission de ' . \App\Support\Money::gnf($commission->amount) . ' est annulée.');
    }

    public function payouts(Request $request)
    {
        Gate::authorize('viewAny', Payout::class);
        $filter = $request->query('statut', PayoutStatus::Requested->value);

        $payouts = Payout::with(['affiliate.user:id,name,shop_id', 'processor:id,name'])
            ->withCount('commissions')
            ->when(in_array($filter, array_column(PayoutStatus::cases(), 'value'), true), fn ($q) => $q->where('status', $filter))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.affiliation.payouts', compact('payouts', 'filter'));
    }

    public function payPayout(Request $request, Payout $payout)
    {
        Gate::authorize('update', $payout);
        $data = $request->validate(['reference' => ['required', 'string', 'max:100']],
            ['reference.required' => 'Indique la référence de la transaction Mobile Money.']);

        try {
            $this->affiliates->markPayoutPaid($payout, $request->user(), $data['reference']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
        return back()->with('success', 'Retrait de ' . \App\Support\Money::gnf($payout->amount) . ' marqué comme payé.');
    }

    public function rejectPayout(Request $request, Payout $payout)
    {
        Gate::authorize('update', $payout);
        $data = $request->validate(['note' => ['required', 'string', 'max:255']],
            ['note.required' => 'Explique pourquoi le retrait est refusé : l\'affilié reçoit ce message.']);

        try {
            $this->affiliates->rejectPayout($payout, $request->user(), $data['note']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
        return back()->with('success', 'Retrait refusé : le solde redevient disponible pour l\'affilié.');
    }

    public function settings()
    {
        Gate::authorize('create', Affiliate::class);
        return view('admin.affiliation.settings', ['settings' => $this->affiliates->settings()]);
    }

    public function updateSettings(AffiliateSettingsRequest $request)
    {
        $this->affiliates->settings()->update($request->validated());
        $this->affiliates->refreshSettings();
        return back()->with('success', 'Réglages enregistrés. Ils valent pour les prochaines commissions et inscriptions.');
    }

    private function commissionQuery(?string $status)
    {
        return Commission::query()
            ->when(in_array($status, array_column(CommissionStatus::cases(), 'value'), true), fn ($q) => $q->where('status', $status));
    }
}
