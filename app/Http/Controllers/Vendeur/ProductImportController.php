<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductImportAi;
use App\Services\ProductImporter;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * « Importer un produit » (Produits → Importer depuis un lien) : l'Importateur Produit Shopio.
 *   1. analyze : lien (+ texte copié, captures d'écran, ou données du bouton « Importer dans Shopio ») → page produit
 *      préparée par Shopio IA, gardée 2 h côté serveur (jeton) : le prix fournisseur ne peut pas être modifié.
 *   2. create : le vendeur relit, complète (taux, transport, douane, marge, stock…), confirme ses droits sur les photos
 *      et textes ; le prix est recalculé ici, le produit est créé en brouillon et s'ouvre dans l'éditeur.
 */
class ProductImportController extends Controller
{
    private function shop()
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || !$shop->is_approved, 403);
        return $shop;
    }

    private static function pro($shop): bool
    {
        return $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
    }

    public function show()
    {
        $shop = Auth::user()->shop;
        if (!$shop || !$shop->is_approved) {
            return redirect()->route('shop.index')->with('error', 'Votre boutique doit être validée avant d’ajouter des produits.');
        }
        return view('vendeur.products.import', [
            'shop' => $shop, 'devise' => $shop->currency ?? 'GNF', 'pro' => self::pro($shop),
            'aiReady' => self::pro($shop) && (string) config('services.anthropic.key') !== '',
            'categories' => array_merge(ProductController::CATEGORIES, ['Autre']),
        ]);
    }

    public function analyze(Request $request, ProductImportAi $ai, ProductImporter $importer)
    {
        $shop = $this->shop();
        if (!self::pro($shop)) return response()->json(['error' => 'L’Importateur Produit Shopio est réservé au plan Pro.'], 403);
        $data = $request->validate([
            'url' => ['required', 'string', 'max:2000', 'url:http,https'],
            'ville' => ['nullable', 'string', 'max:60'],
            'delai' => ['nullable', 'string', 'max:120'],
            'echange' => ['nullable', 'string', 'max:300'],
            'text' => ['nullable', 'string', 'max:60000'],
            'shots' => ['nullable', 'array', 'max:4'],
            'shots.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hint' => ['nullable', 'string', 'max:20000'],
        ], ['url.required' => 'Colle le lien du produit.', 'url.url' => 'Lien non valide : colle l’adresse complète (https://…).',
            'shots.max' => '4 captures d’écran au maximum.', 'shots.*.image' => 'Une capture n’est pas une image.', 'shots.*.max' => 'Capture trop lourde : 5 Mo maximum.']);
        $key = 'ai-import:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['error' => 'Limite atteinte (20 analyses par heure). Réessaie plus tard.'], 429);
        }
        RateLimiter::hit($key, 3600);

        $shots = [];
        foreach ((array) $request->file('shots', []) as $f) {
            $shots[] = ['mime' => $f->getMimeType() === 'image/jpg' ? 'image/jpeg' : $f->getMimeType(), 'data' => base64_encode((string) file_get_contents($f->getRealPath()))];
        }
        $hint = json_decode((string) ($data['hint'] ?? ''), true);
        try {
            $r = $ai->analyze($shop, $data['url'], [
                'ville' => $data['ville'] ?? '', 'delai' => $data['delai'] ?? '', 'echange' => $data['echange'] ?? '',
                'text' => trim((string) ($data['text'] ?? '')), 'screenshots' => $shots, 'hint' => is_array($hint) ? $hint : [],
            ], $importer);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
        Log::info('Importateur Produit', ['site' => $r['source']['site'], 'statut' => $r['statut'], 'lecture' => $r['lecture'], 'raison' => $r['raison_lecture']]);
        unset($r['raison_lecture']);
        $r['token'] = (string) Str::uuid();
        Cache::put('product-import:' . Auth::id() . ':' . $r['token'], $r, now()->addHours(2));
        return response()->json($r);
    }

    public function create(Request $request, ProductImporter $importer)
    {
        $shop = $this->shop();
        if (!app(SubscriptionService::class)->canCreateProduct($shop)) {
            return response()->json(['error' => 'Limite atteinte : le Plan Gratuit est limité à 5 produits.'], 402);
        }
        $data = $request->validate([
            'token' => ['required', 'uuid'],
            'droits' => ['accepted'],
            'titre' => ['required', 'string', 'max:60'],
            'accroche' => ['nullable', 'string', 'max:220'],
            'avantages_produit' => ['nullable', 'array', 'max:2'], 'avantages_produit.*' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:1200'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'ville' => ['nullable', 'string', 'max:60'],
            'taux' => ['nullable', 'numeric', 'min:0.0001', 'max:1000000'],
            'transport' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'douane' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'marge' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'prix' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'ancien_prix' => ['nullable', 'integer', 'gt:prix'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'photos' => ['nullable', 'array', 'max:4'], 'photos.*' => ['string', 'max:2000'],
        ], [
            'droits.accepted' => 'Confirme que tu as le droit d’utiliser ces photos et ces textes (ton produit, ton fournisseur).',
            'titre.required' => 'Indique le titre du produit.', 'prix.required' => 'Indique ton prix de vente en GNF.',
            'prix.integer' => 'Le prix doit être un nombre entier, sans espace ni lettre.', 'stock.required' => 'Indique ton vrai stock (le stock du fournisseur n’est pas le tien).',
            'ancien_prix.gt' => 'Le prix barré doit être plus élevé que le prix de vente.',
        ]);
        $r = Cache::get('product-import:' . Auth::id() . ':' . $data['token']);
        if (!$r) return response()->json(['error' => 'Analyse expirée : relance l’analyse du lien.'], 410);
        if ($r['statut'] !== 'ok') return response()->json(['error' => $r['message'] ?: 'Ce produit ne peut pas être créé.'], 422);

        // Prix recalculé ici, depuis le prix fournisseur lu (jamais celui envoyé par le navigateur)
        $pricing = ProductImportAi::pricing($r['source']['prix_fournisseur'] ?: null, $r['source']['devise'],
            isset($data['taux']) ? (float) $data['taux'] : null, isset($data['transport']) ? (float) $data['transport'] : null,
            isset($data['douane']) ? (float) $data['douane'] : null, isset($data['marge']) ? (float) $data['marge'] : null);

        // Rien de « [À COMPLÉTER] » ne va jusqu'aux clients
        $public = fn ($s) => !preg_match('/\[[^\]]*(COMPLÉTER|VILLE)[^\]]*\]/iu', (string) $s);
        $p = $r['page'];
        $productBenefits = array_values(array_filter(array_map(fn ($v) => trim(strip_tags((string) $v)), $data['avantages_produit'] ?? $p['avantages_produit'])));
        $city = trim((string) ($data['ville'] ?? ''));
        $benefits = array_values(array_filter(ProductImportAi::benefits($productBenefits, $city), $public));
        if ($city === '') array_splice($benefits, count($productBenefits), 0, ['🚚 Livraison à domicile']);
        $specs = array_values(array_filter($p['caracteristiques'], fn ($c) => $public($c['valeur'])));
        $details = implode("\n", array_filter([
            $specs ? "Caractéristiques :\n" . implode("\n", array_map(fn ($c) => '• ' . $c['nom'] . ' : ' . $c['valeur'], $specs)) : '',
            $p['variantes'] ? "Disponible en :\n" . implode("\n", array_map(fn ($v) => '• ' . $v['type'] . ' : ' . implode(', ', $v['options']), $p['variantes'])) : '',
            $p['contenu_colis'] ? "Dans le colis :\n" . implode("\n", array_map(fn ($c) => '• ' . $c, $p['contenu_colis'])) : '',
        ]));
        $faq = array_values(array_filter(array_map(fn ($f) => ['q' => $f['question'], 'a' => $f['reponse']], $p['faq']), fn ($f) => $public($f['q']) && $public($f['a'])));

        // Photos choisies parmi celles de la page (téléchargées avec les contrôles habituels)
        $chosen = array_values(array_intersect($data['photos'] ?? $r['photos'], $r['photos']));
        $photos = array_column($importer->downloadPhotos($chosen, 4), 'path');

        $description = trim(strip_tags((string) ($data['description'] ?? $p['description'])));
        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => trim(strip_tags($data['titre'])),
            'description' => $public($description) ? $description : null,
            'price' => $data['prix'],
            'original_price' => $data['ancien_prix'] ?? null,
            'min_price' => $pricing['minimum'],
            'category' => in_array($cat = trim((string) ($data['categorie'] ?? $p['categorie'])), ProductController::CATEGORIES, true) ? $cat : null,
            'stock' => $data['stock'],
            'unit' => 'pièce',
            'is_active' => false, // brouillon : le vendeur relit la page puis publie
            'is_featured' => false,
            'is_available' => true,
            'image' => $photos[0] ?? null,
            'gallery' => count($photos) > 1 ? json_encode(array_slice($photos, 1)) : null,
            'landing' => [
                'hook' => $public($data['accroche'] ?? $p['accroche']) ? trim(strip_tags((string) ($data['accroche'] ?? $p['accroche']))) : '',
                'benefits' => $benefits,
                'details' => $details,
                'faq' => $faq,
                // Privé (jamais affiché) : d'où vient le produit et comment son prix a été calculé
                'source' => $r['source'] + ['calcul' => [
                    'taux_change' => $data['taux'] ?? null, 'transport_gnf' => $data['transport'] ?? null, 'douane_pourcent' => $data['douane'] ?? null,
                    'marge_pourcent' => $data['marge'] ?? null, 'prix_revient_gnf' => $pricing['revient'],
                ], 'photos_fournisseur' => true, 'droits_confirmes' => true, 'importe_le' => now()->toIso8601String()],
            ],
        ]);
        Cache::forget('product-import:' . Auth::id() . ':' . $data['token']);

        return response()->json([
            'id' => $product->id,
            'redirect' => route('products.page.edit', $product) . '?nouveau=1',
            'photos' => count($photos),
            'warning' => !$photos ? 'Aucune photo n’a pu être récupérée : ajoute tes vraies photos du produit avant de publier.' : null,
        ], 201);
    }
}
