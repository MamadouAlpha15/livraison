<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Services\ImageOptimizer;


class ProductController extends Controller
{
    /* ─────────────────────────────────────────────
     | Catégories disponibles pour boutique / resto
     ───────────────────────────────────────────── */
    const CATEGORIES = [
        // Boutique générale
        'Vêtements & Mode',
        'Chaussures & Accessoires',
        'Électronique & Téléphones',
        'Beauté & Cosmétiques',
        'Maison & Décoration',
        'Épicerie & Alimentation',
        'Boissons',
        'Jouets & Enfants',
        'Sport & Loisirs',
        'Bijoux & Montres',
        // Restaurant / Food
        'Entrées',
        'Plats principaux',
        'Grillades & Viandes',
        'Poissons & Fruits de mer',
        'Végétarien',
        'Pizzas & Burgers',
        'Sandwichs & Wraps',
        'Salades',
        'Desserts & Pâtisseries',
        'Boissons chaudes',
        'Jus & Smoothies',
        'Formules & Menus',
        'Autre',
    ];

    /* ─────────────────────────────────────────────
     | INDEX
     ───────────────────────────────────────────── */
    public function index(Request $request)
    {
        $shop = Auth::user()->shop;
        if (!$shop || !$shop->is_approved) {
            return redirect()->route('shop.index')
                ->with('error', 'Votre boutique doit être validée avant d\'ajouter des produits.');
        }

        $query = $shop->products()->latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status') && \Illuminate\Support\Facades\Schema::hasColumn('products', 'is_active')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products      = $query->paginate(12)->withQueryString();

        /* Catégories prédéfinies + catégories personnalisées saisies manuellement
         * On récupère toutes les catégories utilisées dans les produits de la boutique
         * puis on soustrait les prédéfinies pour isoler les personnalisées */
        $predefinedCats  = self::CATEGORIES;
        $usedCats        = $shop->products()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->toArray();
        $customCats      = array_values(array_diff($usedCats, $predefinedCats));
        sort($customCats);

        /* $categories = prédéfinies + personnalisées (passées séparément à la vue) */
        $categories = $predefinedCats;

        $devise        = $shop->currency ?? 'GNF';
        $totalProducts = $shop->products()->count();

        /* Colonnes optionnelles : évite le crash si la migration n'a pas encore tourné */
        $hasIsActive    = \Illuminate\Support\Facades\Schema::hasColumn('products', 'is_active');
        $hasStock       = \Illuminate\Support\Facades\Schema::hasColumn('products', 'stock');
        $activeProducts = $hasIsActive ? $shop->products()->where('is_active', true)->count() : $totalProducts;
        $outOfStock     = $hasStock    ? $shop->products()->where('stock', '<=', 0)->count()   : 0;

        return view('vendeur.products.index', compact(
            'products', 'categories', 'customCats', 'devise',
            'totalProducts', 'activeProducts', 'outOfStock'
        ));
    }

    /* ─────────────────────────────────────────────
     | CREATE
     ───────────────────────────────────────────── */
    public function create()
    {
        $shop = Auth::user()->shop;
        if (!$shop || !$shop->is_approved) {
            return redirect()->route('shop.index')
                ->with('error', 'Votre boutique doit être validée avant d\'ajouter des produits.');
        }

        $categories = self::CATEGORIES;
        $devise     = $shop->currency ?? 'GNF';

        return view('vendeur.products.create', compact('categories', 'devise'));
    }

    /* ─────────────────────────────────────────────
     | STORE
     ───────────────────────────────────────────── */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'original_price'   => 'nullable|numeric|min:0',
            'description'      => 'nullable|string|max:2000',
            'category'         => 'nullable|string|max:100',
            'stock'            => 'nullable|integer|min:0',
            'unit'             => 'nullable|string|max:30',
            'preparation_time' => 'nullable|integer|min:0',
            'is_active'        => 'nullable|boolean',
            'is_featured'      => 'nullable|boolean',
            'is_available'     => 'nullable|boolean',
            'allergens'        => 'nullable|string|max:500',
            'tags'             => 'nullable|string|max:500',
            'image'            => 'nullable|image|max:20480',
            'images'           => 'nullable|array',
            'images.*'         => 'nullable|image|max:20480',
            'gallery_keep'     => 'nullable|array',
            'gallery_keep.*'   => 'nullable|string',
        ]);

        $shop = Auth::user()->shop;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = ImageOptimizer::store($request->file('image'), 'products');
        }

        $gallery = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                if ($img && $img->isValid()) {
                    $gallery[] = ImageOptimizer::store($img, 'products/gallery');
                }
            }
        }

        Product::create([
            'shop_id'          => $shop->id,
            'name'             => $request->name,
            'description'      => $request->description,
            'price'            => $request->price,
            'original_price'   => $request->original_price,
            'category'         => $request->category,
            'stock'            => $request->stock ?? 0,
            'unit'             => $request->unit ?? 'pièce',
            'preparation_time' => $request->preparation_time,
            'is_active'        => $request->boolean('is_active', true),
            'is_featured'      => $request->boolean('is_featured', false),
            'is_available'     => $request->boolean('is_available', true),
            'allergens'        => $request->allergens,
            'tags'             => $request->tags,
            'image'            => $imagePath,
            'gallery'          => !empty($gallery) ? json_encode($gallery) : null,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Produit "' . $request->name . '" ajouté avec succès !');
    }

    /* ─────────────────────────────────────────────
     | EDIT
     ───────────────────────────────────────────── */
    public function edit(Product $product)
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || $product->shop_id !== $shop->id, 403);

        $categories = self::CATEGORIES;
        $devise     = $shop->currency ?? 'GNF';

        return view('vendeur.products.edit', compact('product', 'categories', 'devise'));
    }

    /* ─────────────────────────────────────────────
     | UPDATE
     ───────────────────────────────────────────── */
    public function update(Request $request, Product $product)
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || $product->shop_id !== $shop->id, 403);

        $request->validate([
            'name'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'original_price'   => 'nullable|numeric|min:0',
            'description'      => 'nullable|string|max:2000',
            'category'         => 'nullable|string|max:100',
            'stock'            => 'nullable|integer|min:0',
            'unit'             => 'nullable|string|max:30',
            'preparation_time' => 'nullable|integer|min:0',
            'is_active'        => 'nullable|boolean',
            'is_featured'      => 'nullable|boolean',
            'is_available'     => 'nullable|boolean',
            'allergens'        => 'nullable|string|max:500',
            'tags'             => 'nullable|string|max:500',
            'image'            => 'nullable|image|max:20480',
            'images'           => 'nullable|array',
            'images.*'         => 'nullable|image|max:20480',
        ]);

        $data = $request->only([
            'name', 'description', 'price', 'original_price',
            'category', 'stock', 'unit', 'preparation_time',
            'allergens', 'tags',
        ]);

        $data['is_active']    = $request->boolean('is_active', $product->is_active);
        $data['is_featured']  = $request->boolean('is_featured', false);
        $data['is_available'] = $request->boolean('is_available', true);

        if ($request->hasFile('image')) {
            ImageOptimizer::delete($product->image);
            $data['image'] = ImageOptimizer::store($request->file('image'), 'products');
        }

        // Galerie : gallery_keep[] = chemins à conserver, images[] = nouvelles photos
        $keep    = $request->input('gallery_keep', []);
        $current = $product->gallery ? json_decode($product->gallery, true) : [];

        // Supprimer les fichiers qui ont été retirés (présents dans current mais absents de keep)
        foreach ($current as $path) {
            if (!in_array($path, $keep)) {
                ImageOptimizer::delete($path);
            }
        }

        // Nouvelles photos uploadées
        $newGallery = array_values($keep);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                if ($img && $img->isValid()) {
                    $newGallery[] = ImageOptimizer::store($img, 'products/gallery');
                }
            }
        }

        $data['gallery'] = !empty($newGallery) ? json_encode(array_values($newGallery)) : null;

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Produit "' . $product->name . '" mis à jour avec succès !');
    }

    /* ─────────────────────────────────────────────
     | TOGGLE actif / inactif (AJAX)
     ───────────────────────────────────────────── */
    public function toggleActive(Product $product)
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || $product->shop_id !== $shop->id, 403);

        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'active'  => $product->is_active,
            'message' => $product->is_active ? 'Produit activé' : 'Produit désactivé',
        ]);
    }

    /* ─────────────────────────────────────────────
     | DUPLICATE
     ───────────────────────────────────────────── */
    public function duplicate(Product $product)
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || $product->shop_id !== $shop->id, 403);

        $new = $product->replicate();
        $new->name      = $product->name . ' (copie)';
        $new->is_active = false;
        $new->save();

        return redirect()->route('products.edit', $new)
            ->with('success', 'Produit dupliqué. Modifiez et activez-le.');
    }

    /* ─────────────────────────────────────────────
     | DESTROY
     ───────────────────────────────────────────── */
    public function destroy(Product $product)
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || $product->shop_id !== $shop->id, 403);

        if ($product->image) Storage::disk('public')->delete($product->image);
        if ($product->gallery) {
            foreach (json_decode($product->gallery, true) as $img) {
                Storage::disk('public')->delete($img);
            }
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produit "' . $name . '" supprimé avec succès.');
    }

    
}