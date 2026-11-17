<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SalesPageAi;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

/**
 * « Créer avec Shopio IA » (Produits → Créer avec Shopio IA) : un produit en 3 étapes.
 *   1. Photos (1 à 4, la première est la principale), envoyées une à une par products.upload.image.
 *   2. Prix, prix barré, stock, catégorie, nom facultatif, consignes de style pour l'IA.
 *   3. Création : le produit est créé en brouillon ; Shopio IA trouve le nom et la description d'après
 *      les photos et rédige la page de vente (packs suggérés −10 % / −20 %). Le vendeur relit tout dans
 *      l'écran « Page de vente » (aperçu en direct) puis publie. Sans Shopio IA (plan gratuit, clé absente,
 *      erreur), le produit est quand même créé avec le nom saisi.
 */
class ProductWizardController extends Controller
{
    /** Chemins rendus par ProductController::uploadImage (WebP optimisé). */
    private const IMAGE_PATH = '#^products(/gallery)?/medium/[A-Za-z0-9]{20}\.webp$#';

    private static function aiReady($shop): bool
    {
        return $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture() && (string) config('services.anthropic.key') !== '';
    }

    public function create()
    {
        $shop = Auth::user()->shop;
        if (!$shop || !$shop->is_approved) {
            return redirect()->route('shop.index')->with('error', 'Votre boutique doit être validée avant d’ajouter des produits.');
        }
        if (!app(SubscriptionService::class)->canCreateProduct($shop)) {
            return redirect()->route('boutique.subscription.upgrade')
                ->with('plan_error', 'Limite atteinte : le Plan Gratuit est limité à 5 produits. Passez au Plan Pro pour en créer davantage.');
        }
        $categories = collect(ProductController::CATEGORIES)
            ->merge($shop->products()->whereNotNull('category')->where('category', '!=', '')->distinct()->pluck('category'))
            ->unique()->sort()->values();

        return view('vendeur.products.wizard', [
            'shop' => $shop, 'devise' => $shop->currency ?? 'GNF', 'categories' => $categories, 'aiReady' => self::aiReady($shop),
            // Photos pro : plan Pro et service de photos configuré
            'photoAi' => $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture() && (string) config('services.openai.key') !== '',
        ]);
    }

    /** « Photo pro » : à partir d'une vraie photo envoyée, Shopio IA crée une photo studio ou mise en scène. */
    public function photo(Request $request, \App\Services\SectionImageAi $ai)
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || !$shop->is_approved, 403);
        if (!($shop->plan === 'pro' && $shop->plan_expires_at?->isFuture())) {
            return response()->json(['error' => 'Les photos Shopio IA sont réservées au plan Pro.'], 403);
        }
        $data = $request->validate([
            'source' => ['required', 'string', 'regex:' . \App\Support\ProductPhotos::PATH],
            'style'  => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Services\SectionImageAi::STYLES))],
            'wish'   => ['nullable', 'string', 'max:300'],
        ], ['source.regex' => 'Envoyez d’abord une vraie photo du produit.']);
        if (!Storage::disk('public')->exists($data['source'])) {
            return response()->json(['error' => 'Photo introuvable : ajoutez-la de nouveau.'], 422);
        }
        $key = 'ai-images:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, \App\Services\SectionImageAi::DAILY)) {
            return response()->json(['error' => 'Limite atteinte (' . \App\Services\SectionImageAi::DAILY . ' photos par jour). Réessayez demain.'], 429);
        }
        RateLimiter::hit($key, 86400);
        try {
            return response()->json($ai->make($shop, 'product', null, (string) ($data['wish'] ?? ''), $data['style'], $data['source'], 'products'));
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    public function store(Request $request, SalesPageAi $ai)
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || !$shop->is_approved, 403);
        if (!app(SubscriptionService::class)->canCreateProduct($shop)) {
            return response()->json(['error' => 'Limite atteinte : le Plan Gratuit est limité à 5 produits.', 'upgrade' => route('boutique.subscription.upgrade')], 402);
        }
        $aiReady = self::aiReady($shop);

        $data = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:4'],
            'images.*' => ['required', 'string', 'regex:' . self::IMAGE_PATH],
            'price' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'original_price' => ['nullable', 'integer', 'gt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'category' => ['nullable', 'string', 'max:100'],
            'name' => [$aiReady ? 'nullable' : 'required', 'string', 'max:120'],
            'instructions' => ['nullable', 'string', 'max:500'],
        ], [
            'images.required' => 'Ajoutez au moins une photo du produit.',
            'images.max' => '4 photos au maximum.',
            'images.*.regex' => 'Une photo n’a pas été envoyée correctement : retirez-la puis ajoutez-la de nouveau.',
            'price.required' => 'Indiquez le prix de vente.',
            'price.integer' => 'Le prix doit être un nombre entier, sans espace ni lettre.',
            'original_price.gt' => 'Le prix barré doit être plus élevé que le prix de vente.',
            'stock.required' => 'Indiquez combien de pièces vous avez en stock.',
            'name.required' => 'Indiquez le nom du produit (la rédaction automatique demande le plan Pro).',
        ]);
        // Les photos doivent exister (envoyées juste avant par ce vendeur, pendant cette étape)
        foreach ($data['images'] as $path) {
            if (!Storage::disk('public')->exists($path)) {
                return response()->json(['errors' => ['images' => ['Une photo est introuvable : ajoutez-la de nouveau.']]], 422);
            }
        }

        $images = array_values(array_unique($data['images']));
        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => trim((string) ($data['name'] ?? '')) !== '' ? trim($data['name']) : 'Nouveau produit',
            'price' => $data['price'],
            'original_price' => $data['original_price'] ?? null,
            'category' => trim((string) ($data['category'] ?? '')) ?: null,
            'stock' => $data['stock'],
            'unit' => 'pièce',
            'is_active' => false,              // brouillon : publié par le vendeur après relecture
            'is_featured' => false,
            'is_available' => true,
            'image' => $images[0],
            'gallery' => count($images) > 1 ? json_encode(array_slice($images, 1)) : null,
        ]);

        $warning = null;
        if ($aiReady) {
            $key = 'ai-page:' . Auth::id();
            if (RateLimiter::tooManyAttempts($key, 10)) {
                $warning = 'Limite Shopio IA atteinte (10 par heure) : le produit est créé, complétez ses textes vous-même ou réessayez plus tard.';
            } else {
                RateLimiter::hit($key, 3600);
                // Nom saisi par le vendeur (gardé), sinon vide : l'IA le trouve d'après les photos
                $sellerName = trim((string) ($data['name'] ?? ''));
                $placeholder = $product->name;
                $product->name = $sellerName; // en mémoire seulement, pour la consigne envoyée à l'IA
                try {
                    $r = $ai->generate($product, $shop, true, (string) ($data['instructions'] ?? ''));
                    $landing = $r['landing'];
                    $landing['packs'] = $r['packs'];
                    $product->forceFill([
                        'name' => $sellerName !== '' ? $sellerName : ($r['name'] !== '' ? $r['name'] : $placeholder),
                        'description' => $r['description'] !== '' ? $r['description'] : null,
                        'landing' => $landing,
                    ])->save();
                } catch (\RuntimeException $e) {
                    $product->name = $placeholder;
                    Log::info('Création de produit : Shopio IA indisponible', ['product' => $product->id, 'error' => $e->getMessage()]);
                    $warning = $e->getMessage() . ' Le produit est créé : complétez ses textes vous-même.';
                }
            }
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'redirect' => route('products.page.edit', $product) . '?nouveau=1',
            'warning' => $warning,
        ], 201);
    }
}
