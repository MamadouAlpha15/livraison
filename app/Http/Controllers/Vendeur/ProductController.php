<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->shop;
        if (!$shop || !$shop->is_approved) { // Vérifie si la boutique existe et est approuvée
            return redirect()->route('shop.index')->with('error', 'Votre boutique doit être validée avant d’ajouter des produits.');
        }
 
        $products = $shop->products()->latest()->paginate(10); // Récupère les produits de la boutique
        return view('vendeur.products.index', compact('products'));
    }

    public function create()
    {
        $shop = Auth::user()->shop; // Récupère la boutique de l'utilisateur connecté
        if (!$shop || !$shop->is_approved) {
            return redirect()->route('shop.index')->with('error', 'Votre boutique doit être validée avant d’ajouter des produits.');
        }
        return view('vendeur.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|',
            'description' => 'nullable|string',
        ]);

        $shop = Auth::user()->shop;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'shop_id' => $shop->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
        ]);

        return redirect()->route('products.index')->with('success', 'Produit ajouté avec succès.');
    }


    public function edit(Product $product)
{
    // Vérifier que le produit appartient au vendeur connecté
  
    
    return view('vendeur.products.edit', compact('product'));
}

public function update(Request $request, Product $product)
{
   

    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'image' => 'nullable|image|',
        'description' => 'nullable|string',
    ]);

    $data = $request->only(['name','price','description']); // Récupère les données sauf l'image

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('products', 'public');
    }

    $product->update($data);

    return redirect()->route('products.index')->with('success', 'Produit mis à jour avec succès.');
}

public function destroy(Product $product)
{
   

    $product->delete();

    return redirect()->route('products.index')->with('success', 'Produit supprimé avec succès.');
}

}
