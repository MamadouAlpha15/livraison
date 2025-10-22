<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /**
     * Afficher la boutique du vendeur connecté
     */
   public function index()
{
    $user = \Illuminate\Support\Facades\Auth::user();
    $shop = $user->shop ?: $user->assignedShop; // ✅

    return view('vendeur.shop.index', compact('shop'));
}

    /**
     * Formulaire de création d'une boutique
     */
    public function create()
    {
        return view('vendeur.shop.create');
    }

    /**
     * Sauvegarde d'une nouvelle boutique
     */
    public function store(Request $request)
    {
        // ✅ Validation
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'nullable|string|max:100',
            'address'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'image'       => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ]);

        // ✅ Upload image si présente
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('shops', 'public');
        }

        // ✅ Création de la boutique et lien avec le vendeur
        $shop = new Shop($validated);
        $shop->user_id = Auth::id();
        $shop->save();

        // ✅ Mise à jour du vendeur → il devient admin de sa boutique
        $user = Auth::user();
        $user->update([
            'shop_id' => $shop->id,
            'role_in_shop' => 'admin',
        ]);

        // ✅ Redirection vers le dashboard vendeur
        return redirect()
            ->route('boutique.dashboard')
            ->with('success', 'Boutique créée avec succès, en attente de validation par l’admin.');
    }

    /**
     * Modifier une boutique
     */
    public function edit(Shop $shop)
    {
        $this->authorize('update', $shop); // sécurité : seul le propriétaire peut modifier
        return view('vendeur.shop.edit', compact('shop'));
    }

    /**
     * Mettre à jour une boutique
     */
    public function update(Request $request, Shop $shop)
    {
        $this->authorize('update', $shop);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'nullable|string|max:100',
            'address'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'image'       => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('shops', 'public');
        }

        $shop->update($validated);

        return redirect()
            ->route('shop.index')
            ->with('success', 'Boutique mise à jour avec succès.');
    }

    /**
     * Supprimer une boutique
     */
    public function destroy(Shop $shop)
    {
        $this->authorize('delete', $shop);

        $shop->delete();

        return redirect()
            ->route('vendeur.dashboard')
            ->with('success', 'Boutique supprimée avec succès.');
    }

   public function admin()
{
    $user = Auth::user();
    $shop = $user->shop;

    // Si pas encore de boutique → rediriger vers la création
    if (!$shop) {
        return redirect()->route('boutique.shops.create')
            ->with('info', 'Vous devez créer une boutique pour accéder au tableau de bord.');
    }

    // Si l’utilisateur est bien admin de sa boutique
    if ($user->role === 'admin' || $user->role_in_shop === 'admin') {
        return view('boutique.dashboard', compact('shop'));
    }

    // Sinon → accès refusé
    abort(403, 'Accès réservé aux administrateurs de boutique.');
}

}
