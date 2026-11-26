<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Support\Growth;
use App\Support\HomeSections;
use App\Support\PageModels;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * « Ma vitrine » : le thème de la boutique (Express ou Signature), sa couleur, son adresse courte,
 * l'affichage des fiches produit et le mode vacances. Enregistré et publié tout de suite.
 * Agit toujours sur la boutique de l'utilisateur connecté, jamais sur un id fourni.
 */
class StorefrontController extends Controller
{
    /** Couleurs proposées (le vendeur peut aussi choisir la sienne). */
    public const COLORS = ['#2F5D1E' => 'Vert forêt', '#1F7CF6' => 'Bleu Shopio', '#0B1E3D' => 'Bleu nuit', '#B42318' => 'Rouge', '#C2410C' => 'Orange brûlé', '#7C3AED' => 'Violet', '#16162A' => 'Noir'];

    private function shop(): Shop
    {
        $user = Auth::user();
        $shop = $user->shop ?? Shop::where('user_id', $user->id)->first();
        abort_unless($shop, 404, 'Aucune boutique associée à ce compte.');
        return $shop;
    }

    public function edit(): View
    {
        $shop = $this->shop();
        $config = Storefront::forShop($shop);
        $publicUrl = Storefront::publicUrl($shop);

        return view('vendeur.shop.storefront', [
            'shop'      => $shop,
            'config'    => $config,
            'accent'    => Storefront::isHex($shop->accent_color) ? strtoupper($shop->accent_color) : '#2F5D1E',
            'colors'    => self::COLORS,
            'models'    => PageModels::MODELS,
            'publicUrl' => $publicUrl,
            'slugBase'  => rtrim(route('public.shops.slug', 'x'), 'x'),
            'qrSvg'     => self::qrSvg(TrafficSource::tag($publicUrl, 'qr', 'print'), 220),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $data = $request->validate([
            'page_model'   => ['required', Rule::in(array_keys(PageModels::MODELS))],
            'accent'       => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'slug'         => ['nullable', 'string', 'max:60'],
            'show_stock'   => ['nullable', 'boolean'],
            'show_strike'  => ['nullable', 'boolean'],
            'show_nego'    => ['nullable', 'boolean'],
            'vacation'     => ['nullable', 'boolean'],
            'vacation_msg' => ['nullable', 'string', 'max:120'],
        ], [
            'page_model.in' => 'Choisissez un des thèmes proposés.',
            'accent.regex'  => 'Couleur invalide.',
            'vacation_msg.max' => 'Le message de vacances ne peut pas dépasser 120 caractères.',
        ]);

        // Adresse courte (facultative)
        $slug = Storefront::slugify((string) ($data['slug'] ?? ''));
        if ($slug !== '' && ($error = Storefront::slugError($slug, $shop->id))) {
            return back()->withErrors(['slug' => 'Adresse de la boutique : ' . $error])->withInput();
        }

        $config = Storefront::forShop($shop);
        $config['page_model'] = $data['page_model'];
        foreach (['show_stock', 'show_strike', 'show_nego', 'vacation'] as $k) $config['settings'][$k] = $request->boolean($k);
        $msg = trim(strip_tags((string) ($data['vacation_msg'] ?? '')));
        $config['settings']['vacation_msg'] = $msg !== '' ? $msg : Storefront::settingsDefaults()['vacation_msg'];

        // Publié tout de suite ; un ancien brouillon (outil retiré) est abandonné
        Growth::publish($shop, Storefront::normalize($config), strtoupper($data['accent']), ['slug' => $slug !== '' ? $slug : null]);

        return redirect()->route('boutique.storefront.edit')->with('success', $config['settings']['vacation']
            ? 'Vitrine enregistrée. Mode vacances actif : les commandes sont en pause.'
            : 'Vitrine enregistrée. Vos clients voient maintenant ces réglages.');
    }

    // ─────────────────────────────────────────────────────────── Sections de l'accueil

    /** « Boutique → Sections » : sections de la page d'accueil, une liste par thème, avec aperçu en direct. */
    public function sections(Request $request): View
    {
        $shop = $this->shop();
        $config = Storefront::forShop($shop);
        // Page modifiée : l'accueil d'un thème (Express, Signature) ou la page produit
        // (thème en ligne sans sections, comme Lookbook : on ouvre l'accueil Express)
        $theme = isset(HomeSections::PAGES[$request->query('theme')]) ? $request->query('theme') : (isset(HomeSections::PAGES[$config['page_model']]) ? $config['page_model'] : 'express');
        $defaults = HomeSections::DEFAULTS;
        // Titre d'accueil prévu : le slogan de la boutique, sinon son nom (Signature) ou la phrase du thème
        $slogan = (string) $config['cover']['slogan'];
        if ($slogan !== '') $defaults['express']['hero']['title'] = $defaults['signature']['hero']['title'] = $slogan;
        if ($defaults['signature']['hero']['title'] === '') $defaults['signature']['hero']['title'] = $shop->name;

        $images = HomeSections::images($config['home']);

        // Produits proposés pour l'aperçu de la page produit (brouillons compris : vus par leur seul vendeur)
        $previewProducts = $shop->products()->orderByDesc('is_active')->orderByDesc('is_featured')->latest()->take(60)->get(['id', 'name', 'is_active'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name . ($p->is_active ? '' : ' (brouillon)'), 'url' => \App\Support\ProductPage::url($shop, $p)])->values();

        return view('vendeur.shop.sections', [
            'shop'       => $shop,
            'pages'      => HomeSections::PAGES,
            'previewProducts' => $previewProducts,
            'theme'      => $theme,
            'published'  => $config['page_model'],
            'home'       => $config['home'],
            'models'     => PageModels::MODELS,
            'defaults'   => $defaults,
            'images'     => $images,
            'categories' => collect(Storefront::categoryLinks($shop))->pluck('name')->values(),
            'publicUrl'  => Storefront::publicUrl($shop),
            'ai'         => self::aiState($shop),
        ]);
    }

    /** Enregistre et publie les sections d'un thème (JSON). */
    public function saveSections(Request $request): JsonResponse
    {
        $shop = $this->shop();
        $data = $request->validate([
            'theme'    => ['required', Rule::in(array_keys(HomeSections::PAGES))],
            'sections' => ['required', 'array', 'max:' . (count(HomeSections::BUILTIN['signature']) + HomeSections::CUSTOM_MAX)],
        ], ['theme.in' => 'Thème inconnu.', 'sections.max' => 'Trop de sections : ' . HomeSections::CUSTOM_MAX . ' sections ajoutées au maximum.']);

        $config = Storefront::forShop($shop);
        $config['home'][$data['theme']] = HomeSections::normalize($data['theme'], $data['sections']);
        $shop->update(['storefront' => Storefront::normalize($config)]);

        return response()->json(['ok' => true, 'sections' => Storefront::forShop($shop->fresh())['home'][$data['theme']]]);
    }

    /** Shopio IA est-il proposé à cette boutique ? (plan Pro en cours, clé configurée) */
    public static function aiState(Shop $shop): array
    {
        $pro = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
        return ['pro' => (bool) $pro, 'ready' => $pro && (string) config('services.anthropic.key') !== ''];
    }

    /**
     * « Améliorer avec Shopio IA » : réécrit les textes d'une section (ou de toute la page) et les renvoie à
     * l'éditeur, sans rien enregistrer : le vendeur relit, corrige, puis publie. Plan Pro, 20 demandes par heure.
     */
    public function sectionsAi(Request $request, \App\Services\SectionsAi $ai): JsonResponse
    {
        $shop = $this->shop();
        $data = $request->validate([
            'theme'    => ['required', Rule::in(array_keys(HomeSections::PAGES))],
            'sections' => ['required', 'array', 'min:1', 'max:' . (count(HomeSections::BUILTIN['signature']) + HomeSections::CUSTOM_MAX)],
            'hint'     => ['nullable', 'string', 'max:300'],
        ]);
        if (!self::aiState($shop)['pro']) {
            return response()->json(['error' => 'Shopio IA est réservé au plan Pro. Passez au plan Pro pour améliorer vos sections en un clic.'], 403);
        }
        $key = 'ai-sections:' . Auth::id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['error' => 'Limite atteinte (20 demandes par heure). Réessayez plus tard.'], 429);
        }
        \Illuminate\Support\Facades\RateLimiter::hit($key, 3600);

        try {
            return response()->json(['sections' => $ai->improve($shop, $data['theme'], $data['sections'], (string) ($data['hint'] ?? ''))]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
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
    public static function qrSvg(string $url, int $size, bool $withLogo = false): ?string
    {
        if (!class_exists(\BaconQrCode\Writer::class)) return null;

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size, 1),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        // $withLogo : correction d'erreur renforcée (Q, 25 %) pour poser un logo au centre sans gêner la lecture
        $svg = (new \BaconQrCode\Writer($renderer))->writeString($url, 'UTF-8',
            $withLogo ? \BaconQrCode\Common\ErrorCorrectionLevel::Q() : \BaconQrCode\Common\ErrorCorrectionLevel::L());

        // Retire la déclaration XML pour pouvoir l'insérer directement dans la page
        return trim(preg_replace('/^<\?xml[^>]*\?>/', '', $svg));
    }
}
