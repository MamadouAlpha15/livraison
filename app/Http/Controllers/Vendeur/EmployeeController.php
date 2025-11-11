<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        $shopId = $shop?->id;

        abort_unless($shopId, 403, 'Aucune boutique rattachée.');

        $employees = User::where('shop_id', $shopId)
            ->whereIn('role_in_shop', ['vendeur','livreur','employe'])
            ->latest()
            ->paginate(15);

        return view('vendeur.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('vendeur.employees.create');
    }

    public function store(Request $request)
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        $shopId = $shop?->id;

        abort_unless($shopId, 403, 'Aucune boutique rattachée.');

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|min:6|confirmed',
            'role_in_shop' => 'required|in:vendeur,livreur,employe',
        ]);

        $globalRole = match ($request->role_in_shop) {
            'vendeur' => 'vendeur',
            'livreur' => 'livreur',
            default   => 'employe',
        };

        User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => $globalRole,      // rôle global
            'shop_id'      => $shopId,          // <= rattache bien à MA boutique
            'role_in_shop' => $request->role_in_shop,
        ]);

        return redirect()->route('boutique.employees.index')
            ->with('success','Utilisateur ajouté avec succès.');
    }

    public function destroy(User $employee)
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        $shopId = $shop?->id;

        abort_unless($shopId && $employee->shop_id === $shopId, 403, 'Action non autorisée.');

        $employee->delete();

        return redirect()->route('boutique.employees.index')
            ->with('success','Utilisateur supprimé avec succès.');
    }
}
