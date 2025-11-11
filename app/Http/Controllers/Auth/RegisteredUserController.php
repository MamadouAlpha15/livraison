<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\DeliveryCompany;


class RegisteredUserController extends Controller
{
    /**
     * Affiche la page d'inscription
     */
    public function create(Request $request): View
    {
        // On garde l’ID de la boutique (si présent) pour un éventuel lien de suivi
        $shopId = $request->get('shop_id');
        return view('auth.register', compact('shopId'));
    }

    /**
     * Gère la création du compte
     */
    public function store(Request $request): RedirectResponse
    {
        // Validation des champs
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:client,admin,company,livreur'], // ✅ ajout de company ici
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'shop_id' => ['nullable', 'exists:shops,id'],
        ]);

        // Création du compte utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => in_array($request->role, ['client', 'livreur']) ? $request->phone : null,
            'address' => $request->role === 'client' ? $request->address : null,
            'role' => $request->role,
        ]);

        event(new Registered($user));
        Auth::login($user);

        // Si le client s'inscrit depuis une boutique à suivre
        if ($request->filled('shop_id') && $user->role === 'client') {
            $shop = Shop::find($request->shop_id);
            if ($shop) {
                $user->subscribedShops()->attach($shop->id);
            }
        }

        // Redirections selon le rôle
        switch ($user->role) {
            case 'admin':
                return redirect()->route('boutique.dashboard');
            case 'company':
                return redirect()->route('company.dashboard'); // ✅ redirection spécifique entreprise
            case 'livreur':
                return redirect()->route('livreur.dashboard');
            default:
                return redirect()->route('client.dashboard');
        }
    }
}
