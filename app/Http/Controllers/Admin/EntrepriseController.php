<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntrepriseController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryCompany::withCount(['drivers', 'zones'])
            ->with('user')
            ->latest();

        if ($request->filled('status')) {
            match($request->status) {
                'approved'  => $query->where('approved', true),
                'pending'   => $query->where('approved', false),
                'active'    => $query->where('active', true)->where('approved', true),
                'inactive'  => $query->where('active', false),
                default     => null,
            };
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('address', 'like', "%$q%");
            });
        }

        if ($request->filled('plan')) {
            if ($request->plan === 'business') {
                $query->where('plan', 'business')->where('plan_expires_at', '>', now());
            } else {
                $query->where(function ($q) {
                    $q->whereNull('plan')
                      ->orWhere('plan', 'free')
                      ->orWhere('plan_expires_at', '<=', now());
                });
            }
        }

        $entreprises = $query->paginate(20)->withQueryString();

        $base = DeliveryCompany::query();

        $stats = [
            'total'      => (clone $base)->count(),
            'approved'   => (clone $base)->where('approved', true)->count(),
            'pending'    => (clone $base)->where('approved', false)->count(),
            'active'     => (clone $base)->where('approved', true)->where('active', true)->count(),
            'inactive'   => (clone $base)->where('active', false)->count(),
            'drivers'    => \App\Models\Driver::count(),
            'business'   => (clone $base)->where('plan', 'business')->where('plan_expires_at', '>', now())->count(),
        ];

        $countries = DeliveryCompany::whereNotNull('country')
            ->distinct()->pluck('country')->sort()->values();

        return view('admin.entreprises.index', compact('entreprises', 'stats', 'countries'));
    }

    public function approve(DeliveryCompany $entreprise)
    {
        $entreprise->update(['approved' => true, 'approved_at' => now()]);
        return back()->with('success', "L'entreprise {$entreprise->name} a été approuvée.");
    }

    public function reject(DeliveryCompany $entreprise)
    {
        $entreprise->update(['approved' => false, 'approved_at' => null]);
        return back()->with('success', "L'entreprise {$entreprise->name} a été rejetée.");
    }

    public function toggleActive(DeliveryCompany $entreprise)
    {
        $entreprise->update(['active' => !$entreprise->active]);
        $state = $entreprise->fresh()->active ? 'activée' : 'désactivée';
        return back()->with('success', "L'entreprise {$entreprise->name} a été {$state}.");
    }

    /**
     * Purge définitive : supprime tout ce qui appartient aux entreprises sélectionnées.
     */
    public function purgeBulk(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return back()->with('error', 'Aucune entreprise sélectionnée.');
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $companyId) {
                $company = DeliveryCompany::find($companyId);
                if (!$company) continue;

                // --- Commandes liées à cette entreprise (neutraliser, pas supprimer) ---
                $orderIds = DB::table('orders')->where('delivery_company_id', $companyId)->pluck('id');
                if ($orderIds->isNotEmpty()) {
                    DB::table('courier_commissions')->whereIn('order_id', $orderIds)->delete();
                    DB::table('orders')->whereIn('id', $orderIds)->update([
                        'delivery_company_id' => null,
                        'driver_id'           => null,
                        'delivery_zone_id'    => null,
                    ]);
                }

                // --- Messages entre boutiques et entreprise ---
                DB::table('delivery_messages')->where('delivery_company_id', $companyId)->delete();

                // --- Avis sur l'entreprise ---
                DB::table('delivery_company_reviews')->where('delivery_company_id', $companyId)->delete();

                // --- Zones de livraison ---
                DB::table('delivery_zones')->where('delivery_company_id', $companyId)->delete();

                // --- Abonnements ---
                DB::table('subscriptions')
                    ->where('subscriber_type', DeliveryCompany::class)
                    ->where('subscriber_id', $companyId)
                    ->delete();

                // --- Drivers (leurs user_id + les Driver records) ---
                $driverUserIds = DB::table('drivers')->where('delivery_company_id', $companyId)->pluck('user_id')->toArray();
                DB::table('drivers')->where('delivery_company_id', $companyId)->delete();

                // --- Membres (users avec company_id) ---
                $memberIds = User::where('company_id', $companyId)->pluck('id')->toArray();

                // --- Propriétaire ---
                $allUserIds = array_unique(array_merge($driverUserIds, $memberIds));
                if ($company->user_id && !in_array($company->user_id, $allUserIds)) {
                    $allUserIds[] = $company->user_id;
                }

                if (!empty($allUserIds)) {
                    DB::table('push_subscriptions')->whereIn('user_id', $allUserIds)->delete();
                    $uTicketIds = DB::table('support_tickets')->whereIn('user_id', $allUserIds)->pluck('id');
                    if ($uTicketIds->isNotEmpty()) {
                        DB::table('support_messages')->whereIn('ticket_id', $uTicketIds)->delete();
                        DB::table('support_tickets')->whereIn('id', $uTicketIds)->delete();
                    }
                    User::whereIn('id', $allUserIds)->delete();
                }

                // --- L'entreprise elle-même ---
                $company->delete();
            }
        });

        $count = count($ids);
        return redirect()->route('admin.dashboard')
            ->with('success', "$count entreprise(s) supprimée(s) définitivement.");
    }
}
