<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\DeliveryCompany;
use App\Models\Order;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryZone::with('company')->latest();

        if ($request->filled('company_id')) {
            $query->where('delivery_company_id', $request->company_id);
        }

        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%");
            });
        }

        $zones = $query->paginate(20)->withQueryString();

        $base = DeliveryZone::query();
        if ($request->filled('company_id')) {
            $base->where('delivery_company_id', $request->company_id);
        }

        $stats = [
            'total'    => (clone $base)->count(),
            'actives'  => (clone $base)->where('active', true)->count(),
            'inactives'=> (clone $base)->where('active', false)->count(),
            'prix_moy' => (clone $base)->where('active', true)->avg('price'),
            'prix_min' => (clone $base)->where('active', true)->min('price'),
            'prix_max' => (clone $base)->where('active', true)->max('price'),
        ];

        $companies       = DeliveryCompany::orderBy('name')->get(['id', 'name', 'currency', 'country']);
        $filteredCompany = $request->filled('company_id') ? DeliveryCompany::find($request->company_id) : null;

        return view('admin.zones.index', compact('zones', 'stats', 'companies', 'filteredCompany'));
    }

    public function toggleActive(DeliveryZone $zone)
    {
        $zone->update(['active' => !$zone->active]);
        $state = $zone->fresh()->active ? 'activée' : 'désactivée';
        return back()->with('success', "La zone \"{$zone->name}\" a été {$state}.");
    }
}
