<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\Order;
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
        $shopId      = $request->get('shop_id');
        $intent      = $request->get('intent', '');
        $defaultRole = match($intent) {
            'pro'      => 'admin',
            'business' => 'company',
            default    => $request->get('role', 'client'),
        };

        // Sauvegarder en session pour que Google auth puisse aussi rediriger
        if ($request->filled('redirect')) {
            $target = $request->redirect;
            if (str_starts_with($target, url('/'))) {
                session(['product_redirect' => $target]);
            }
        }

        // Commande passée sans compte : à rattacher au compte une fois créé
        if ($request->filled('order_id')) {
            session(['link_order_id' => $request->order_id]);
        }

        // Lien de parrainage : mémorise le code tant que l'inscription n'est pas finalisée
        if ($request->filled('ref')) {
            session(['referral_code' => strtoupper($request->get('ref'))]);
        }

        return view('auth.register', compact('shopId', 'intent', 'defaultRole'));
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

        // Code de parrainage : champ du formulaire en priorité, sinon celui mémorisé en session
        $referredBy = null;
        if ($request->role === 'client') {
            $refCode = strtoupper($request->get('ref', session('referral_code', '')));
            if ($refCode) {
                $referredBy = User::where('referral_code', $refCode)->value('id');
            }
        }

        // Création du compte utilisateur
        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'phone'       => in_array($request->role, ['client', 'livreur']) ? $request->phone : null,
            'address'     => $request->role === 'client' ? $request->address : null,
            'country'     => strtoupper($request->country),
            'role'        => $request->role,
            'referred_by' => $referredBy,
        ]);

        session()->forget('referral_code');

        event(new Registered($user));
        Auth::login($user);

        Order::attachGuestOrderFromSession($user);

        if ($request->filled('intent') && in_array($request->intent, ['pro', 'business'])) {
            session(['payment_intent' => $request->intent]);
        }

        // Si le client s'inscrit depuis une boutique à suivre
        if ($request->filled('shop_id') && $user->role === 'client') {
            $shop = Shop::find($request->shop_id);
            if ($shop) {
                $user->subscribedShops()->attach($shop->id);
            }
        }

        // Si le client vient d'une page produit, le renvoyer directement dessus
        if ($user->role === 'client' && $request->filled('redirect')) {
            $target = $request->redirect;
            // Sécurité : on n'accepte que les URLs du même domaine
            if (str_starts_with($target, url('/'))) {
                return redirect($target);
            }
        }

        // Redirections selon le rôle
        switch ($user->role) {
            case 'admin':
                return redirect()->route('boutique.dashboard');
            case 'company':
                return redirect()->route('company.dashboard');
            case 'livreur':
                return redirect()->route('livreur.dashboard');
            default:
                return redirect()->route('client.dashboard');
        }
    }
}
