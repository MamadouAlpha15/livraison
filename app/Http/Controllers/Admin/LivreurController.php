<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DeliveryCompany;
use Illuminate\Http\Request;

class LivreurController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with('company')
            ->withCount('orders')
            ->latest();

        if ($request->filled('company_id')) {
            $query->where('delivery_company_id', $request->company_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%");
            });
        }

        $livreurs = $query->paginate(20)->withQueryString();

        $base = Driver::query();
        if ($request->filled('company_id')) {
            $base->where('delivery_company_id', $request->company_id);
        }

        $stats = [
            'total'      => (clone $base)->count(),
            'available'  => (clone $base)->where('status', 'available')->count(),
            'busy'       => (clone $base)->where('status', 'busy')->count(),
            'offline'    => (clone $base)->where('status', 'offline')->count(),
            'livraisons' => Driver::withCount('orders')->get()->sum('orders_count'),
        ];

        $companies       = DeliveryCompany::orderBy('name')->get(['id', 'name']);
        $filteredCompany = $request->filled('company_id') ? DeliveryCompany::find($request->company_id) : null;

        return view('admin.livreurs.index', compact('livreurs', 'stats', 'companies', 'filteredCompany'));
    }
}
