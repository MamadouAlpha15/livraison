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
            'phone'         => 'required|string|max:255',
            'password'     => 'required|min:6|confirmed',
            'role_in_shop' => 'required|in:vendeur,livreur,employe',
        ]);

        $globalRole = match ($request->role_in_shop) {
            'vendeur' => 'vendeur',
            'livreur' => 'livreur',
            default   => 'employe',
        };

        User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'phone'                =>$request->phone,
            'password'             => Hash::make($request->password),
            'role'                 => $globalRole,
            'shop_id'              => $shopId,
            'role_in_shop'         => $request->role_in_shop,
            'must_change_password' => true,
        ]);

        return redirect()->route('boutique.employees.create')
            ->with('success', 'Compte créé avec succès !')
            ->with('new_email', $request->email)
            ->with('new_password', $request->password)
            ->with('new_name', $request->name);
    }

    public function edit(User $employee)
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        abort_unless($shop && $employee->shop_id === $shop->id, 403);

        return view('vendeur.employees.edit', compact('employee'));
    }

    public function update(Request $request, User $employee)
    {
        $user   = Auth::user();
        $shop   = $user->shop ?: $user->assignedShop;
        abort_unless($shop && $employee->shop_id === $shop->id, 403);

        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:255',
            'role_in_shop' => 'required|in:vendeur,livreur,employe',
        ]);

        $employee->update([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'role_in_shop' => $request->role_in_shop,
            'role'         => match($request->role_in_shop) {
                'vendeur' => 'vendeur',
                'livreur' => 'livreur',
                default   => 'employe',
            },
        ]);

        return redirect()->route('boutique.employees.index')
            ->with('success', 'Employé mis à jour avec succès.');
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
