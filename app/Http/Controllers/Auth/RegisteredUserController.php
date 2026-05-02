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
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:client,admin,company,livreur'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'address'  => ['nullable', 'string', 'max:255'],
            'country'  => ['required', 'string', 'size:2'],
            'shop_id'  => ['nullable', 'exists:shops,id'],
        ], [
            'name.required'      => 'Le nom complet est obligatoire.',
            'name.max'           => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required'     => 'L\'adresse email est obligatoire.',
            'email.email'        => 'Veuillez entrer une adresse email valide.',
            'email.unique'       => 'Cette adresse email est déjà utilisée.',
            'email.lowercase'    => 'L\'adresse email doit être en minuscules.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'role.required'      => 'Veuillez choisir un type de compte.',
            'role.in'            => 'Le type de compte sélectionné est invalide.',
            'country.required'   => 'Veuillez sélectionner votre pays.',
            'country.size'       => 'Le pays sélectionné est invalide.',
        ]);

        // Création du compte utilisateur
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => in_array($request->role, ['client', 'livreur']) ? $request->phone : null,
            'address'  => $request->role === 'client' ? $request->address : null,
            'country'  => strtoupper($request->country),
            'role'     => $request->role,
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
