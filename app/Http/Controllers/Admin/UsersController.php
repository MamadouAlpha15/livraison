<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $meInit = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));
        $role   = $request->input('role', 'all');
        $search = $request->input('search');

        $query = User::with(['shop', 'deliveryCompany', 'ownedCompany'])->latest();

        if ($role !== 'all') {
            if ($role === 'employe') {
                $query->whereIn('role', ['employe', 'admin', 'vendeur'])
                      ->whereNotNull('shop_id');
            } elseif ($role === 'company') {
                $query->where(function ($q) {
                    $q->where('role', 'company')
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('company_id')->where('role', '!=', 'livreur');
                      });
                });
            } else {
                $query->where('role', $role);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(30)->withQueryString();

        $stats = [
            'total'      => User::count(),
            'clients'    => User::where('role', 'client')->count(),
            'livreurs'   => User::where('role', 'livreur')->count(),
            'employes'   => User::whereIn('role', ['employe', 'vendeur', 'admin'])->whereNotNull('shop_id')->count(),
            'entreprises'=> User::where(function ($q) {
                $q->where('role', 'company')
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('company_id')->where('role', '!=', 'livreur');
                  });
            })->count(),
        ];

        return view('admin.users.index', compact('users', 'stats', 'role', 'search', 'meInit'));
    }
}
