<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Shop;
use App\Services\ImageOptimizer;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * « Éditeur de ma boutique » : blocs de la page, style, thèmes et réglages de la vitrine,
 * avec aperçu en direct (brouillon en session, visible du seul propriétaire), publication
 * avec historique, partage (lien, WhatsApp, QR code).
 * Agit toujours sur la boutique de l'utilisateur connecté, jamais sur un id fourni.
 */
class StorefrontController extends Controller
{
    private function shop(): Shop
    {
        $user = Auth::user();
        $shop = $user->shop ?? Shop::where('user_id', $user->id)->first();
        abort_unless($shop, 404, 'Aucune boutique associée à ce compte.');
        return $shop;
    }

    private function draftKey(Shop $shop): string
    {
        return 'storefront_draft.' . $shop->id;
    }

    public function edit(): View
    {
        $shop = $this->shop();
        $config = Storefront::forShop($shop);
        $publicUrl = Storefront::publicUrl($shop);
        $devise = $shop->currency ?? 'GNF';

        $products = $shop->products()->where('is_active', true)
            ->orderByDesc('is_featured')->latest()
            ->get(['id', 'name', 'image', 'price', 'category', 'flash_price', 'flash_starts_at', 'flash_ends_at', 'is_featured'])
            ->map(fn ($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'price'    => number_format($p->current_price, 0, ',', ' ') . ' ' . $devise,
                'thumb'    => $p->image ? (ImageOptimizer::url($p->image, 'thumb') ?? asset('storage/' . $p->image)) : null,
                'featured' => (bool) $p->is_featured,
            ]);

        $categories = $shop->products()->where('is_active', true)->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category')->values();

        $promoCodes = PromoCode::where('shop_id', $shop->id)->where('is_active', true)->orderBy('code')
            ->get(['id', 'code', 'type', 'value', 'expires_at'])
            ->map(fn ($p) => ['id' => $p->id, 'code' => $p->code, 'label' => $p->code . ' · −' . ($p->type === 'percent' ? $p->value . ' %' : number_format($p->value, 0, ',', ' ') . ' ' . $devise)]);

        // Publications précédentes, restaurables dans l'éditeur (la plus récente d'abord)
        $history = collect($shop->storefront_history ?? [])->map(fn ($h) => [
            'at'     => \Carbon\Carbon::parse($h['at'] ?? now())->translatedFormat('j M, H:i'),
            'config' => Storefront::normalize($h['config'] ?? null),
            'accent' => Storefront::isHex($h['accent'] ?? null) ? strtoupper($h['accent']) : '#1F7CF6',
        ])->values();

        // Adresses des photos déjà placées dans les blocs (l'éditeur affiche leurs miniatures)
        $imageUrls = collect($config['blocks'])->flatMap(fn ($b) => array_filter(array_merge([$b['d']['img'] ?? null], $b['d']['imgs'] ?? [])))
            ->unique()->mapWithKeys(fn ($p) => [$p => ImageOptimizer::url($p, 'thumb')])->all();

        return view('vendeur.shop.storefront', [
            'imageUrls'  => $imageUrls,
            'storageBase' => rtrim(asset('storage'), '/') . '/',
            'shop'       => $shop,
            'config'     => $config,
            'accent'     => strtoupper($shop->accent_color ?: '#1F7CF6'),
            'products'   => $products,
            'categories' => $categories,
            // Pages à partager (fenêtre « Partager ») : chaque catégorie avec son lien WhatsApp et son QR code
            'categoryLinks' => collect(Storefront::categoryLinks($shop))->map(fn ($c) => $c + [
                'wa' => TrafficSource::tag($c['url'], 'whatsapp'),
                'qr' => self::qrSvg(TrafficSource::tag($c['url'], 'qr', 'print'), 220),
            ])->all(),
            'promoCodes' => $promoCodes,
            'history'    => $history,
            'checklist'  => Storefront::checklist($shop, $config, $products->count()),
            'publicUrl'  => $publicUrl,
            'previewUrl' => route('public.shops.products', $shop) . '?apercu=1',
            'qrSvg'      => self::qrSvg(TrafficSource::tag($publicUrl, 'qr', 'print'), 220),
        ]);
    }

    /** Valide un état envoyé par l'éditeur : [config normalisée, couleur] ou message d'erreur. */
    private function readState(Shop $shop, $config, $accent): array|string
    {
        if (!is_array($config)) return 'Réglages illisibles, rechargez la page.';
        if (!Storefront::isHex($accent)) return 'Couleur principale invalide.';
        $config = Storefront::normalize($config);

        if ($config['featured_ids']) {
            $own = $shop->products()->whereIn('id', $config['featured_ids'])->pluck('id')->all();
            if (count($own) !== count($config['featured_ids'])) return 'Un des produits à la une n’appartient pas à votre boutique.';
        }
        $promo = $config['settings']['welcome_promo_id'];
        if ($promo && !PromoCode::where('shop_id', $shop->id)->whereKey($promo)->exists()) {
            return 'Le code du message de bienvenue n’appartient pas à votre boutique.';
        }
        if ($config['settings']['welcome'] && !$promo) {
            return 'Choisissez un code promo pour le message de bienvenue, ou désactivez-le.';
        }
        return [$config, strtoupper($accent)];
    }

    /** Aperçu : garde l'état de l'éditeur en session, l'aperçu (?apercu=1) l'affiche au seul propriétaire. */
    public function draft(Request $request): JsonResponse
    {
        $shop = $this->shop();
        $state = $this->readState($shop, $request->input('config'), $request->input('accent'));
        if (is_string($state)) return response()->json(['message' => $state], 422);

        $request->session()->put($this->draftKey($shop), ['config' => $state[0], 'accent' => $state[1]]);
        return response()->json(['ok' => true]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $payload = json_decode((string) $request->input('payload'), true);
        $state = $this->readState($shop, $payload['config'] ?? null, $payload['accent'] ?? null);
        if (is_string($state)) return back()->withErrors(['payload' => $state]);
        [$config, $accent] = $state;

        // Adresse courte (facultative)
        $slug = Storefront::slugify((string) ($payload['slug'] ?? ''));
        if ($slug !== '' && ($error = Storefront::slugError($slug, $shop->id))) {
            return back()->withErrors(['slug' => 'Adresse de la boutique : ' . $error]);
        }

        $history = array_slice(array_merge(
            [['at' => now()->toIso8601String(), 'config' => $config, 'accent' => $accent]],
            $shop->storefront_history ?? []
        ), 0, Storefront::HISTORY_MAX);

        $shop->update([
            'storefront'         => $config,
            'accent_color'       => $accent,
            'slug'               => $slug !== '' ? $slug : null,
            'storefront_history' => $history,
        ]);
        $request->session()->forget($this->draftKey($shop));

        $msg = $config['settings']['vacation']
            ? 'Vitrine publiée. Mode vacances actif : les commandes sont en pause.'
            : 'Vitrine publiée. Vos clients voient maintenant ces réglages.';

        // « published » ouvre le panneau de partage (lien, WhatsApp, QR code) au retour sur l'éditeur
        return redirect()->route('boutique.storefront.edit')->with('success', $msg)->with('published', true);
    }

    public function reset(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $shop->update(['storefront' => null, 'accent_color' => null]);
        $request->session()->forget($this->draftKey($shop));

        return redirect()->route('boutique.storefront.edit')->with('success', 'Vitrine réinitialisée avec les réglages par défaut.');
    }

    /** Photo d'un bloc (image et texte, galerie) : optimisée en WebP, chemin à placer dans le bloc. */
    public function uploadImage(Request $request): JsonResponse
    {
        $this->shop();
        $request->validate(['file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']], [
            'file.image' => 'Ce fichier n’est pas une image.',
            'file.max'   => 'Image trop lourde (10 Mo maximum).',
            'file.mimes' => 'Formats acceptés : JPG, PNG ou WEBP.',
        ]);
        $path = ImageOptimizer::store($request->file('file'), 'storefront');

        return response()->json(['path' => $path, 'url' => ImageOptimizer::url($path, 'medium')]);
    }

    /** Disponibilité d'une adresse de boutique (/s/…). */
    public function slugCheck(Request $request): JsonResponse
    {
        $shop = $this->shop();
        $slug = Storefront::slugify((string) $request->query('slug', ''));
        $error = Storefront::slugError($slug, $shop->id);

        return response()->json([
            'slug'    => $slug,
            'ok'      => $error === null,
            'message' => $error ?? 'Adresse disponible',
            'url'     => $slug !== '' ? route('public.shops.slug', $slug) : null,
        ]);
    }

    /** QR code de la vitrine, ou d'une de ses catégories (?categorie=…), à télécharger (SVG net à toutes les tailles, pour l'imprimer). */
    public function qr(Request $request): Response
    {
        $shop = $this->shop();
        $url = Storefront::publicUrl($shop);
        $name = $shop->name;
        if ($request->filled('categorie')) {
            $cat = collect(Storefront::categoryLinks($shop))->firstWhere('slug', $request->query('categorie'));
            abort_unless($cat, 404);
            [$url, $name] = [$cat['url'], $shop->name . ' ' . $cat['name']];
        }
        $svg = self::qrSvg(TrafficSource::tag($url, 'qr', 'print'), 1024);
        abort_unless($svg, 404);

        $file = 'qr-vitrine-' . \Illuminate\Support\Str::slug($name) . '.svg';
        return response($svg, 200, [
            'Content-Type'        => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
        ]);
    }

    /** Génère le QR code en SVG ; null si la bibliothèque bacon/bacon-qr-code n'est pas installée. */
    public static function qrSvg(string $url, int $size): ?string
    {
        if (!class_exists(\BaconQrCode\Writer::class)) return null;

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size, 1),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $svg = (new \BaconQrCode\Writer($renderer))->writeString($url);

        // Retire la déclaration XML pour pouvoir l'insérer directement dans la page
        return trim(preg_replace('/^<\?xml[^>]*\?>/', '', $svg));
    }
}
