<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\DeliveryZone;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    private function getCompany()
    {
        $company = DeliveryCompany::forUser(auth()->user());
        abort_if(!$company, 404, "Aucune entreprise liée à ce compte.");
        return $company;
    }

    public function index()
    {
        $company = $this->getCompany();
        $zones   = DeliveryZone::where('delivery_company_id', $company->id)->orderBy('name')->paginate(12);
        $devise  = $company->currency ?? 'GNF';

        $svc          = app(SubscriptionService::class);
        $isBusiness   = $svc->companyPlan($company) === 'business';
        $maxDrivers   = SubscriptionService::COMP_FREE_MAX_DRIVERS;
        $maxZones     = SubscriptionService::COMP_FREE_MAX_ZONES;
        $maxOrders    = SubscriptionService::COMP_FREE_MAX_ORDERS;
        $totalDrivers = $company->drivers()->count();
        $totalZones   = $company->zones()->count();
        $usedOrders   = $svc->monthlyCompanyOrderCount($company);

        return view('company.zones.index', compact(
            'company', 'zones', 'devise',
            'isBusiness', 'maxDrivers', 'maxZones', 'maxOrders',
            'totalDrivers', 'totalZones', 'usedOrders'
        ));
    }

    public function store(Request $request)
    {
        $company = $this->getCompany();

        // Vérification limite plan gratuit : max 5 zones de livraison
        if (!app(SubscriptionService::class)->canCreateZone($company)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Limite atteinte : max 5 zones en Plan Gratuit.', 'upgrade' => route('company.subscription.upgrade')], 403);
            }
            return redirect()->route('company.subscription.upgrade')
                ->with('plan_error', 'Limite atteinte : le Plan Gratuit est limité à 5 zones. Passez au Plan Business pour en créer davantage.');
        }

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'description'       => ['nullable', 'string', 'max:255'],
            'price'             => ['required', 'numeric', 'min:0'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'color'             => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'name.required'              => 'Le nom de la zone est obligatoire.',
            'price.required'             => 'Le prix est obligatoire.',
            'estimated_minutes.required' => 'Le délai estimé est obligatoire.',
        ]);

        $data['delivery_company_id'] = $company->id;
        $data['active'] = true;

        DeliveryZone::create($data);

        return back()->with('success', 'Zone créée avec succès.');
    }

    public function update(Request $request, DeliveryZone $zone)
    {
        abort_if($zone->delivery_company_id !== $this->getCompany()->id, 403);

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'description'       => ['nullable', 'string', 'max:255'],
            'price'             => ['required', 'numeric', 'min:0'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'color'             => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $zone->update($data);

        return back()->with('success', 'Zone mise à jour.');
    }

    public function toggle(DeliveryZone $zone)
    {
        abort_if($zone->delivery_company_id !== $this->getCompany()->id, 403);
        $zone->update(['active' => !$zone->active]);
        return back()->with('success', $zone->active ? 'Zone activée.' : 'Zone désactivée.');
    }

    public function destroy(DeliveryZone $zone)
    {
        abort_if($zone->delivery_company_id !== $this->getCompany()->id, 403);
        $zone->delete();
        return back()->with('success', 'Zone supprimée.');
    }
}
