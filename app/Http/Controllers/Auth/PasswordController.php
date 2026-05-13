<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user      = $request->user();
        $isGoogle  = !empty($user->google_id);

        $rules = ['password' => ['required', Password::defaults(), 'confirmed']];

        // Les comptes Google n'ont pas de vrai mot de passe connu → pas de vérification
        if (!$isGoogle) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validateWithBag('updatePassword', $rules);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Rediriger selon le rôle de l'utilisateur
        $role = $request->user()->role;
        if ($role === 'client') {
            return redirect()->route('client.dashboard')->with('status', 'password-updated');
        }

        return back()->with('status', 'password-updated');
    }
}
