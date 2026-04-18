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
            'image_uploaded'   => 'nullable|string|max:500', // 
            'gallery_uploaded' => 'nullable|array|max:20',
            'gallery_uploaded.*' => 'nullable|string|max:500',
        ]);

        $shop = Auth::user()->shop;

        // Image principale uploadée via AJAX (chemin déjà stocké sur le disque)
        $imagePath = $request->input('image_uploaded') ?: null;

        // Galerie uploadée via AJAX
        $gallery = [];
        foreach ($request->input('gallery_uploaded', []) as $path) {
            if ($path && is_string($path)) {
                $gallery[] = $path;
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
            'image_uploaded'   => 'nullable|string|max:500',
            'gallery_keep'     => 'nullable|array',
            'gallery_keep.*'   => 'nullable|string|max:500',
            'gallery_uploaded' => 'nullable|array|max:20',
            'gallery_uploaded.*' => 'nullable|string|max:500',
        ]);

        $data = $request->only([
            'name', 'description', 'price', 'original_price',
            'category', 'stock', 'unit', 'preparation_time',
            'allergens', 'tags',
        ]);

        $data['is_active']    = $request->boolean('is_active', $product->is_active);
        $data['is_featured']  = $request->boolean('is_featured', false);
        $data['is_available'] = $request->boolean('is_available', true);

        // Image principale : chemin uploadé via AJAX
        $newImagePath = $request->input('image_uploaded') ?: null;
        if ($newImagePath !== $product->image) {
            // Nouvelle image ou suppression → supprimer l'ancienne
            if ($product->image) ImageOptimizer::delete($product->image);
            $data['image'] = $newImagePath;
        }

        // Galerie : gallery_keep[] = existantes à conserver, gallery_uploaded[] = nouvelles (AJAX)
        $keep    = array_filter($request->input('gallery_keep', []), fn($p) => $p && is_string($p));
        $current = $product->gallery ? json_decode($product->gallery, true) : [];

        // Supprimer les fichiers retirés par l'utilisateur
        foreach ($current as $path) {
            if (!in_array($path, $keep)) {
                ImageOptimizer::delete($path);
            }
        }

        $newGallery = array_values($keep);
        foreach ($request->input('gallery_uploaded', []) as $path) {
            if ($path && is_string($path)) {
                $newGallery[] = $path;
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

    /**
     * Upload AJAX d'une seule image (galerie ou principale).
     * Chaque image est envoyée séparément → jamais de POST trop grand.
     * Retourne le chemin stocké pour l'injecter dans un champ caché du formulaire.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file'   => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:20480'],
            'folder' => ['nullable', 'string', 'in:products,products/gallery'],
        ]);

        $folder = $request->input('folder', 'products/gallery');
        $path   = ImageOptimizer::store($request->file('file'), $folder);

        return response()->json(['path' => $path, 'url' => ImageOptimizer::url($path, 'medium')]);
    }

    /**
     * Filtre les fichiers invalides ou vides d'un champ file[] avant la validation.
     * Résout l'erreur "The images.X failed to upload." causée par la règle implicite
     * `uploaded` de Laravel qui s'exécute même quand le fichier est vide ou corrompu.
     *
     * IMPORTANT : on utilise \Symfony\Component\HttpFoundation\File\UploadedFile
     * car $request->files->get() retourne des instances Symfony (pas Laravel).
     * \Illuminate\Http\UploadedFile étend cette classe, donc le check couvre les deux.
     */
    private function cleanImageFiles(Request $request, string $field): void
    {
        $value = $request->files->get($field);

        if ($value === null) {
            return; // rien à nettoyer
        }

        $isValid = fn($f) =>
            $f instanceof \Symfony\Component\HttpFoundation\File\UploadedFile
            && $f->isValid()
            && $f->getSize() > 0;

        // Fichier unique (champ image principale)
        if (!is_array($value)) {
            if (!$isValid($value)) {
                $request->files->remove($field);
            }
            return;
        }

        // Tableau de fichiers (champ images[])
        $valid = array_values(array_filter($value, $isValid));

        if (empty($valid)) {
            $request->files->remove($field);
        } else {
            $request->files->set($field, $valid);
        }
    }
}