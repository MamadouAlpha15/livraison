<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use Illuminate\Http\Request;

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

        $entreprises = $query->paginate(20)->withQueryString();

        $base = DeliveryCompany::query();

        $stats = [
            'total'      => (clone $base)->count(),
            'approved'   => (clone $base)->where('approved', true)->count(),
            'pending'    => (clone $base)->where('approved', false)->count(),
            'active'     => (clone $base)->where('approved', true)->where('active', true)->count(),
            'inactive'   => (clone $base)->where('active', false)->count(),
            'drivers'    => \App\Models\Driver::count(),
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
}
