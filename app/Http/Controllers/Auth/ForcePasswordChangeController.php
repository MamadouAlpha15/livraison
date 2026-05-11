<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeController extends Controller
{
    public function showForm()
    {
        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $user = Auth::user();

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        $role = $user->role ?? 'client';

        $redirect = match(true) {
            $role === 'livreur'                       => route('livreur.dashboard'),
            in_array($role, ['vendeur', 'employe'])   => route('boutique.dashboard'),
            $role === 'client'                        => route('client.dashboard'),
            $role === 'company'                       => route('company.orders.index'),
            default                                   => route('admin.dashboard'),
        };

        return redirect($redirect)->with('success', '✅ Mot de passe mis à jour ! Bienvenue sur Shopio.');
    }
}
