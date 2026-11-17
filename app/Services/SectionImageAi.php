<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shopio IA — photos des sections (image et texte, diaporama, grande image d'accueil), avec OpenAI gpt-image.
 *
 * Honnêteté : l'IA n'invente jamais le produit. Soit elle part de la VRAIE photo du produit et change seulement
 * le décor (fond, ambiance, lumière) ; soit elle crée un décor seul, sans aucun produit. Jamais de texte, de prix,
 * de logo ni de visage reconnaissable dans l'image. Le vendeur vérifie le résultat avant d'enregistrer.
 * La photo est rangée comme une photo importée (covers/…, WebP 300/800/1600 px).
 */
class SectionImageAi
{
    /** Sections qui ont une photo, et le format de l'image créée. */
    public const TYPES = ['imgtext' => '1024x1024', 'slideshow' => '1536x1024', 'hero' => '1536x1024'];

    private const KEEP = 'Keep the product from the input image exactly identical: same shape, colours, materials, '
        . 'proportions, logo, label text and every detail. Do not add, remove, resize or modify anything on the product. '
        . 'Change only the background, the surface it stands on, the light and the setting.';

    private const NEVER = 'No added text, no letters, no prices, no logos, no watermark, no recognisable human face.';

    /** Styles d'une photo du produit : studio (fond blanc, photo de catalogue) ou mise en scène (décor). */
    public const STYLES = ['studio' => 'Fond blanc studio', 'scene' => 'Mise en scène'];

    /**
     * Crée la photo. $product : produit à mettre en scène (sa vraie photo est gardée) ; null = décor seul.
     * $source : chemin d'une vraie photo produit déjà envoyée (création d'un produit, avant qu'il existe).
     * $style : 'scene' (décor) ou 'studio' (fond blanc). $folder : 'covers' (sections) ou 'products' / 'products/gallery'.
     * Retourne ['path' => …/medium/….webp, 'url' => …].
     * @throws \RuntimeException avec un message prêt à montrer au vendeur
     */
    public function make(Shop $shop, string $type, ?Product $product = null, string $wish = '', string $style = 'scene', ?string $source = null, string $folder = 'covers'): array
    {
        $key = (string) config('services.openai.key');
        if ($key === '') throw new \RuntimeException('La création de photos n’est pas configurée sur ce serveur (clé OpenAI manquante).');
        $size = self::TYPES[$type] ?? '1024x1024';
        $wish = Str::limit(trim(strip_tags($wish)), 300, '');
        $model = (string) config('services.openai.image_model', 'gpt-image-2');
        $layout = $size === '1536x1024' ? ' Wide banner composition: keep calm, uncluttered space on one side for a title.' : '';

        set_time_limit(240);
        $photo = $source !== null ? $this->photoFrom([$source]) : ($product ? $this->photo($product) : null);
        // Claude dirige : il regarde la vraie photo et la boutique, puis décrit le produit et le décor à dessiner
        $art = $this->direct($shop, $type, $product, $photo, $wish);
        try {
            $http = Http::withToken($key)->timeout(180)->connectTimeout(15);
            if ($photo) {
                $about = ($art['product'] ?? '') !== '' ? ' The product is: ' . $art['product'] . '.' : '';
                $prompt = $style === 'studio'
                    ? 'Professional e-commerce catalogue photo (main product photo of an online shop). ' . self::KEEP . $about
                        . ' Pure white (#FFFFFF) seamless studio background, soft diffused professional lighting, a subtle natural shadow under the product,'
                        . ' product centred and filling about 75% of the square frame, sharp focus, true colours.'
                        . ($wish !== '' ? ' Seller request (only if it keeps the product identical): ' . $wish . '.' : '') . ' ' . self::NEVER
                    : 'Professional e-commerce photo for an online shop in Guinea (West Africa). ' . self::KEEP . $about
                        . ' New setting: ' . (($art['scene'] ?? '') !== '' ? $art['scene'] : ($wish !== '' ? $wish : 'a clean, bright and elegant setting that suits this product')) . '.'
                        . ' Realistic light and natural shadows, sharp focus on the product.' . $layout . ' ' . self::NEVER;
                $res = $http->attach('image', $photo['bytes'], 'produit.' . $photo['ext'], ['Content-Type' => $photo['mime']])
                    ->post('https://api.openai.com/v1/images/edits', [
                        'model' => $model, 'prompt' => $prompt, 'size' => $size, 'quality' => 'medium', 'n' => 1,
                    // « Fidélité haute » au produit : réglage propre à gpt-image-1 (les modèles suivants la gèrent d'eux-mêmes)
                    ] + ($model === 'gpt-image-1' ? ['input_fidelity' => 'high'] : []));
            } else {
                $what = trim(($shop->type ? $shop->type . ', ' : '') . collect(\App\Support\Storefront::categoryLinks($shop))->pluck('name')->take(5)->implode(', '));
                $prompt = 'Photographic background image for a section of an online shop in Guinea (West Africa)'
                    . ($what !== '' ? " selling: {$what}" : '') . '. '
                    . (($art['scene'] ?? '') !== '' ? 'Scene: ' . $art['scene'] . '. ' : ($wish !== '' ? "Scene: {$wish}. " : 'Scene: a soft, warm and elegant setting (textures, light, decor). '))
                    . 'Show NO product of any kind: only a setting or atmosphere.' . $layout . ' ' . self::NEVER;
                $res = $http->asJson()->post('https://api.openai.com/v1/images/generations', [
                    'model' => $model, 'prompt' => $prompt, 'size' => $size, 'quality' => 'medium', 'n' => 1,
                ]);
            }
        } catch (ConnectionException $e) {
            Log::warning('Shopio IA photo : connexion', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Le service de photos ne répond pas : réessayez dans un instant.');
        }

        if (!$res->successful()) {
            $code = (string) $res->json('error.code');
            Log::error('Shopio IA photo : erreur API', ['status' => $res->status(), 'code' => $code, 'error' => Str::limit((string) $res->json('error.message'), 300)]);
            throw new \RuntimeException(match (true) {
                $code === 'moderation_blocked' || $res->json('error.type') === 'image_generation_user_error' => 'Le service de photos a refusé cette demande. Changez la description du décor puis réessayez.',
                $res->status() === 401 => 'La création de photos n’est pas disponible pour le moment (clé refusée).',
                $res->status() === 429 => 'Le service de photos est très sollicité : réessayez dans une minute.',
                default => 'La photo n’a pas pu être créée. Réessayez.',
            });
        }
        $bytes = base64_decode((string) $res->json('data.0.b64_json'), true);
        if (!$bytes || @getimagesizefromstring($bytes) === false) throw new \RuntimeException('Le service de photos a renvoyé une image illisible. Réessayez.');

        // Photo du produit : rangée comme une photo produit envoyée par le vendeur
        if (str_starts_with($folder, 'products')) return \App\Support\ProductPhotos::store($bytes, $folder);
        // Photo de section : rangée comme une photo de section importée (mêmes tailles, même dossier)
        $tmp = tempnam(sys_get_temp_dir(), 'sxai');
        try {
            file_put_contents($tmp, $bytes);
            $path = ImageOptimizer::store(new UploadedFile($tmp, 'shopio-ia.png', 'image/png', null, true), 'covers', true);
        } finally {
            @unlink($tmp);
        }
        return ['path' => $path, 'url' => ImageOptimizer::url(str_replace('/medium/', '/large/', $path), 'medium')];
    }

    private const DIRECTOR = <<<'TXT'
Tu es le directeur artistique de Shopio IA, pour des boutiques en ligne en Guinée. Un outil de création d'images
(OpenAI gpt-image) va dessiner la photo d'une section de la boutique. Tu écris, en anglais, ce qu'il doit dessiner.

- product : si une photo de produit est jointe, décris précisément et uniquement ce que tu vois sur le produit
  (sorte d'objet, forme, couleurs, matières, motifs, inscriptions visibles), pour que l'outil le garde identique.
  Sans photo : chaîne vide. N'invente rien que la photo ne montre pas.
- scene : le décor seul (lieu, surface, accessoires discrets, lumière, couleurs, ambiance), en 1 à 3 phrases.
  Choisis un décor qui met le produit en valeur et qui parle aux clients guinéens (ex. intérieur moderne de
  Conakry, tissu wax, bois, lumière chaude), sans cliché. Suis la consigne du vendeur si elle est donnée.
  Jamais de texte, lettres, prix, logo, ni visage reconnaissable dans la scène. Sans photo de produit :
  aucun produit dans la scène, seulement une ambiance cohérente avec ce que vend la boutique.
TXT;

    /**
     * Direction artistique par Claude : ['product' => description fidèle du produit vu, 'scene' => décor], en anglais.
     * Facultative : en cas d'absence de clé ou d'erreur, renvoie [] et la photo est créée avec la description standard.
     */
    protected function direct(Shop $shop, string $type, ?Product $product, ?array $photo, string $wish): array
    {
        $key = (string) config('services.anthropic.key');
        if ($key === '') return [];
        $cats = collect(\App\Support\Storefront::categoryLinks($shop))->pluck('name')->take(8)->implode(', ');
        $facts = "Boutique : {$shop->name}" . ($shop->type ? " ({$shop->type})" : '') . "\n"
            . ($cats !== '' ? "Catégories : {$cats}\n" : '')
            . ($photo ? 'Produit à mettre en scène' . ($product ? " : {$product->name}" . ($product->category ? " ({$product->category})" : '') : ' : voir la photo jointe') . "\n" : "Photo sans produit (décor seul)\n")
            . ($type === 'product' ? "Usage : photo du produit pour sa fiche (carrée)\n"
                : 'Section : ' . (\App\Support\HomeSections::TYPES[$type][0] ?? $type) . ' — ' . ((self::TYPES[$type] ?? '') === '1536x1024' ? 'grande bannière horizontale, un côté calme pour un titre' : 'photo carrée à côté d’un texte') . "\n")
            . ($wish !== '' ? "Consigne du vendeur : {$wish}\n" : '');
        $content = [];
        if ($photo && strlen($photo['bytes']) <= 4 * 1048576) {
            $content[] = ['type' => 'image', 'source' => ['type' => 'base64', 'mediaType' => $photo['mime'], 'data' => base64_encode($photo['bytes'])]];
        }
        $content[] = ['type' => 'text', 'text' => $facts];
        $str = ['type' => 'string'];
        try {
            $client = new \Anthropic\Client(apiKey: $key, requestOptions: ['timeout' => 60, 'maxRetries' => 1]);
            $message = $client->beta->messages->create(
                model: SalesPageAi::MODEL,
                maxTokens: 4000,
                system: [['type' => 'text', 'text' => self::DIRECTOR, 'cacheControl' => ['type' => 'ephemeral']]],
                messages: [['role' => 'user', 'content' => $content]],
                outputConfig: ['effort' => 'low', 'format' => ['type' => 'json_schema', 'schema' => [
                    'type' => 'object', 'properties' => ['product' => $str, 'scene' => $str], 'required' => ['product', 'scene'], 'additionalProperties' => false,
                ]]],
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
            if ($message->stopReason !== 'end_turn') return [];
            $text = '';
            foreach ($message->content as $block) if ($block->type === 'text') $text .= $block->text;
            $data = json_decode($text, true);
            if (!is_array($data)) return [];
            return ['product' => $product ? Str::limit(trim((string) ($data['product'] ?? '')), 400, '') : '', 'scene' => Str::limit(trim((string) ($data['scene'] ?? '')), 600, '')];
        } catch (\Throwable $e) {
            Log::warning('Shopio IA photo : direction artistique indisponible', ['error' => Str::limit($e->getMessage(), 300)]);
            return [];
        }
    }

    /** Photos créées par vendeur et par jour (chaque photo coûte : on garde une limite raisonnable). */
    public const DAILY = 20;

    /**
     * Demande de l'éditeur (Boutique → Sections ou page produit) : plan Pro, section avec photo, produit de la
     * boutique, limite du jour. Rien n'est enregistré dans la page : le vendeur vérifie, puis enregistre.
     */
    public function respond(\Illuminate\Http\Request $request, Shop $shop): \Illuminate\Http\JsonResponse
    {
        if (!($shop->plan === 'pro' && $shop->plan_expires_at?->isFuture())) {
            return response()->json(['error' => 'Les photos Shopio IA sont réservées au plan Pro.'], 403);
        }
        $data = $request->validate([
            'type'       => ['required', \Illuminate\Validation\Rule::in(array_keys(self::TYPES))],
            'product_id' => ['nullable', 'integer'],
            'wish'       => ['nullable', 'string', 'max:300'],
        ], ['type.in' => 'Cette section n’a pas de photo.', 'wish.max' => 'Description du décor trop longue : 300 caractères maximum.']);
        $product = null;
        if (!empty($data['product_id'])) {
            $product = Product::where('shop_id', $shop->id)->find($data['product_id']);
            if (!$product) return response()->json(['error' => 'Produit introuvable dans votre boutique.'], 422);
        }
        $key = 'ai-images:' . $request->user()->id;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, self::DAILY)) {
            return response()->json(['error' => 'Limite atteinte (' . self::DAILY . ' photos par jour). Réessayez demain.'], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 86400);

        try {
            return response()->json($this->make($shop, $data['type'], $product, (string) ($data['wish'] ?? '')));
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    /** Vraie photo du produit (photo principale, sinon la première de la galerie). */
    private function photo(Product $product): array
    {
        $gallery = is_array($product->gallery) ? $product->gallery : (json_decode((string) $product->gallery, true) ?: []);
        return $this->photoFrom(array_merge([$product->image], $gallery));
    }

    /** Première photo lisible parmi ces chemins (disque des images). */
    private function photoFrom(array $paths): array
    {
        $disk = Storage::disk(ImageOptimizer::activeDisk());
        foreach (array_filter($paths, 'is_string') as $path) {
            try {
                if (!$disk->exists($path)) continue;
                $bytes = $disk->get($path);
                $info = @getimagesizefromstring((string) $bytes);
                $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
                if ($ext && strlen($bytes) <= 20 * 1048576) return ['bytes' => $bytes, 'mime' => $info['mime'], 'ext' => $ext];
            } catch (\Throwable $e) {
                continue;
            }
        }
        throw new \RuntimeException('Ce produit n’a pas de photo utilisable : ajoutez-en une, ou choisissez « Décor seul ».');
    }

    /** Produits que le vendeur peut mettre en scène (avec une photo), pour la liste de l'éditeur. */
    public static function choices(Shop $shop, ?Product $first = null): array
    {
        $list = $shop->products()->whereNotNull('image')->where('image', '!=', '')->orderByDesc('is_featured')->latest()->take(40)->get(['id', 'name'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => Str::limit($p->name, 50)]);
        if ($first) $list = $list->reject(fn ($p) => $p['id'] === $first->id)->prepend(['id' => $first->id, 'name' => Str::limit($first->name, 50)]);
        return $list->values()->all();
    }
}
