<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function company(): DeliveryCompany
    {
        $company = DeliveryCompany::forUser(auth()->user());

        if (!$company) {
            abort(403, "Aucune entreprise de livraison liée à ce compte.");
        }

        return $company;
    }

    private function isOwner(DeliveryCompany $company): bool
    {
        return $company->user_id === auth()->id();
    }

    public function index()
    {
        $company = $this->company();
        $isOwner = $this->isOwner($company);

        $members = User::where('company_id', $company->id)->orderBy('name')->get();

        return view('company.users.index', compact('company', 'members', 'isOwner'));
    }

    public function store(Request $request)
    {
        $company = $this->company();

        if (!$this->isOwner($company)) {
            abort(403, "Seul le propriétaire peut ajouter des membres.");
        }

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? null,
            'password'             => Hash::make($data['password']),
            'role'                 => 'company',
            'company_id'           => $company->id,
            'must_change_password' => true,
        ]);

        return back()->with('success', "Utilisateur ajouté avec succès.");
    }

    public function destroy(User $user)
    {
        $company = $this->company();

        if (!$this->isOwner($company)) {
            abort(403, "Seul le propriétaire peut retirer des membres.");
        }

        if ($user->company_id !== $company->id) {
            abort(403);
        }

        $user->update(['company_id' => null, 'role' => 'client']);

        return back()->with('success', "Accès retiré pour {$user->name}.");
    }
}
