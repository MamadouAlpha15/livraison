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

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        // ✅ On garde l'ID de la boutique (si présent) pour l’envoyer à la vue
        $shopId = $request->get('shop_id');
        return view('auth.register', compact('shopId'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:client,admin,vendeur,livreur'], // 👈 choix du rôle
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'shop_id' => ['nullable', 'exists:shops,id'], // 👈 sécurisation de l’ID boutique
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->role === 'client' ? $request->phone : null,
            'address' => $request->role === 'client' ? $request->address : null,
            'role' => $request->role, // 👈 rôle choisi
        ]);

        event(new Registered($user));

        Auth::login($user);

        // ✅ Si l’utilisateur venait pour suivre une boutique, on l’ajoute direct
        if ($request->filled('shop_id') && $user->role === 'client') {
            $shop = Shop::find($request->shop_id);
            if ($shop) {
                $user->subscribedShops()->attach($shop->id);
            }
        }

        // ✅ Redirection selon rôle
        if ($user->role === 'admin') {
            return redirect()->route('boutique.dashboard');
        }
       

        return redirect()->route('client.dashboard');
    }
}
