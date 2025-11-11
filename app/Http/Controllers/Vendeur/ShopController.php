<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;            // ✅ on va lister les livreurs
use Illuminate\Support\Carbon;  // ✅ si tu veux gérer le "en ligne" par last_seen


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
    public function store(Request $request){
        // ✅ Validation
                $validated = $request->validate([
                    'name'        => 'required|string|max:255',
                    'type'        => 'nullable|string|max:100',
                    'address'     => 'nullable|string|max:255',
                    'email'       => 'required|email|unique:shops,email',
                    'phone'       => 'nullable|string|max:20',
                    'image'       => 'nullable|image',
                    'description' => 'nullable|string',
                    'commission_rate' => ['nullable','numeric','between:0,100'], // ✅ taux de commission entre 0 et 100
                ]);
                 // 🔁 Convertir 10 → 0.10, 15 → 0.15, etc.
    if (array_key_exists('commission_rate', $validated) && $validated['commission_rate'] !== null) {
        $validated['commission_rate'] = number_format(((float)$validated['commission_rate']) / 100, 4, '.', '');
    }
            

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
        if (auth()->user()->role !== 'admin') {
        abort(403, 'Accès refusé');
    }

    // si admin, continue
    $shop = Shop::findOrFail($shop->id);
    return view('vendeur.shop.edit', compact('shop'));
}
       
       
    

    /**
     * Mettre à jour une boutique
     */
   // app/Http/Controllers/Vendeur/ShopController.php

public function update(Request $request, \App\Models\Shop $shop)
{
    $data = $request->validate([
        'name'            => ['required','string','max:255'],
        'type'            => ['nullable','string','max:100'],
        'description'     => ['nullable','string','max:2000'],
        'address'         => ['nullable','string','max:255'],
        'phone'           => ['nullable','string','max:30'],
        'email'           => ['nullable','email'],
        // 🟢 ICI: on valide un pourcentage 0..100
        'commission_rate' => ['nullable','numeric','between:0,100'],
        'image'           => ['nullable','image','mimes:jpg,jpeg,png,webp'],
    ]);

    // 🔁 Convertir 10 → 0.10, 15 → 0.15, etc.
    if (array_key_exists('commission_rate', $data) && $data['commission_rate'] !== null) {
        $data['commission_rate'] = number_format(((float)$data['commission_rate']) / 100, 4, '.', '');
    }

    // Image (optionnel)
    if ($request->hasFile('image')) {
        if ($shop->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($shop->image);
        }
        $data['image'] = $request->file('image')->store('shops', 'public');
    } else {
        $data['image'] = $shop->image;
    }

    $shop->update($data);

    return redirect()->route('shop.index', $shop->id)
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
    $shop = $user->shop; // relation user->shop (à adapter si autre nom)

    // 1) Si pas de boutique → redirection vers création
    if (!$shop) {
        return redirect()
            ->route('boutique.shops.create')
            ->with('info', 'Vous devez créer une boutique pour accéder au tableau de bord.');
    }

    // 2) Autorisation: admin global OU admin dans la boutique
    if ($user->role === 'admin' || $user->role_in_shop === 'admin') {

        // --- RÉCUPÉRATION DES LIVREURS DISPONIBLES ---
        $livreursDisponibles = User::query()
            ->where('role', 'livreur')
            ->where('is_available', true)
            ->where('shop_id', $shop->id)          // filtre clé
            ->orderBy('name')
            ->get();

        // --- ENTREPRISES DE LIVRAISON (approuvées) pour le dropdown ---
        $deliveryCompanies = \App\Models\DeliveryCompany::query()
            ->where('approved', true)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // 3) On envoie TOUT ce que la Blade consomme
        return view('boutique.dashboard', [
            'shop' => $shop,
            'livreursDisponibles' => $livreursDisponibles,
            'deliveryCompanies' => $deliveryCompanies,
        ]);
    }

    // 4) Sinon → accès refusé
    abort(403, 'Accès réservé aux administrateurs de boutique.');
}





}
