<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Services\ImageOptimizer;
use App\Services\ProductImporter;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * « Importer les photos d'un produit » (Produits → Importer un lien) : seulement les photos, rien d'autre.
 *   1. photos : lien (ou photos lues par le bouton « Importer dans Shopio » des favoris) → les photos de la page,
 *      téléchargées avec les contrôles habituels (ProductImporter) et gardées 2 h (jeton).
 *   2. use : le vendeur coche les photos à garder et confirme ses droits ; la fiche produit habituelle
 *      (products.create) s'ouvre avec ces photos déjà ajoutées. Le nom, le prix et la description, c'est lui qui les écrit.
 */
class ProductImportController extends Controller
{
    /** Photos montrées au plus, et photos gardées au plus (1 principale + la galerie). */
    public const FOUND = 12;
    public const KEEP = 10;

    private function shop()
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || !$shop->is_approved, 403);
        return $shop;
    }

    private static function cacheKey(string $token): string
    {
        return 'product-photos:' . Auth::id() . ':' . $token;
    }

    public function show()
    {
        $shop = Auth::user()->shop;
        if (!$shop || !$shop->is_approved) {
            return redirect()->route('shop.index')->with('error', 'Votre boutique doit être validée avant d’ajouter des produits.');
        }
        return view('vendeur.products.import', ['shop' => $shop, 'keep' => self::KEEP]);
    }

    public function photos(Request $request, ProductImporter $importer)
    {
        $this->shop();
        $data = $request->validate([
            'url' => ['required', 'string', 'max:2000', 'url:http,https'],
            // Photos lues dans le navigateur par le bouton « Importer dans Shopio » (sites qui bloquent Shopio)
            'images' => ['nullable', 'array', 'max:' . self::FOUND],
            'images.*' => ['string', 'max:2000'],
        ], ['url.required' => 'Colle le lien du produit.', 'url.url' => 'Lien non valide : colle l’adresse complète (https://…).']);

        $key = 'photo-import:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['error' => 'Limite atteinte (30 liens par heure). Réessaie plus tard.'], 429);
        }
        RateLimiter::hit($key, 3600);

        $urls = array_values(array_filter((array) ($data['images'] ?? []), fn ($u) => is_string($u) && preg_match('#^(https?:)?//#i', $u)));
        if (!$urls) {
            try {
                $urls = $importer->read($data['url'])['images'];
            } catch (\RuntimeException $e) {
                return response()->json(['error' => $e->getMessage(), 'blocked' => true], 422);
            }
        }
        $photos = $urls ? $importer->downloadPhotos($urls, self::FOUND) : [];
        if (!$photos) {
            return response()->json(['error' => 'Aucune photo n’a pu être récupérée sur cette page. Vérifie que c’est la page d’un seul produit, ou utilise le bouton « Importer dans Shopio ».', 'blocked' => true], 422);
        }

        $token = (string) Str::uuid();
        Cache::put(self::cacheKey($token), array_column($photos, 'path'), now()->addHours(2));
        return response()->json(['token' => $token, 'photos' => $photos]);
    }

    public function use(Request $request)
    {
        $shop = $this->shop();
        $data = $request->validate([
            'token' => ['required', 'uuid'],
            'droits' => ['accepted'],
            'photos' => ['required', 'array', 'min:1', 'max:' . self::KEEP],
            'photos.*' => ['string', 'max:200'],
        ], [
            'droits.accepted' => 'Confirme que tu as le droit d’utiliser ces photos (ton produit, ton fournisseur).',
            'photos.required' => 'Choisis au moins une photo.', 'photos.min' => 'Choisis au moins une photo.',
            'photos.max' => self::KEEP . ' photos au maximum.',
        ]);
        if (!app(SubscriptionService::class)->canCreateProduct($shop)) {
            return response()->json(['error' => 'Limite atteinte : le Plan Gratuit est limité à 5 produits.'], 402);
        }
        $found = Cache::get(self::cacheKey($data['token']));
        if (!$found) return response()->json(['error' => 'Photos expirées : colle de nouveau le lien.'], 410);

        // Seulement des photos trouvées pour ce jeton, dans l'ordre choisi (la première devient la photo principale)
        $chosen = array_values(array_unique(array_filter($data['photos'], fn ($p) => in_array($p, $found, true))));
        if (!$chosen) return response()->json(['error' => 'Choisis au moins une photo.'], 422);

        foreach (array_diff($found, $chosen) as $unused) ImageOptimizer::delete($unused);
        Cache::forget(self::cacheKey($data['token']));

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        session(['import_photos' => array_map(fn ($p) => ['path' => $p, 'url' => $disk->url($p)], $chosen)]);
        return response()->json(['redirect' => route('products.create'), 'photos' => count($chosen)]);
    }
}
