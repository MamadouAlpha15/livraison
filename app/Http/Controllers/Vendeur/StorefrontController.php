<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\ImageOptimizer;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * « Personnaliser ma vitrine » : réglages de la page publique de la boutique
 * (ordre et visibilité des sections, produits à la une, affichage, bannière, couleur),
 * indicateur de qualité et partage (lien, WhatsApp, QR code).
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

    public function edit(): View
    {
        $shop = $this->shop();
        $config = Storefront::forShop($shop);
        $publicUrl = route('public.shops.products', $shop);

        // Produits proposés dans le sélecteur « Produit à la une »
        $products = $shop->products()->where('is_active', true)
            ->orderByDesc('is_featured')->latest()
            ->get(['id', 'name', 'image', 'price', 'flash_price', 'flash_starts_at', 'flash_ends_at', 'is_featured'])
            ->map(fn ($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'price'    => number_format($p->current_price, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF'),
                'thumb'    => $p->image ? (ImageOptimizer::url($p->image, 'thumb') ?? asset('storage/' . $p->image)) : null,
                'featured' => (bool) $p->is_featured,
            ]);

        return view('vendeur.shop.storefront', [
            'shop'      => $shop,
            'config'    => $config,
            'accent'    => $shop->accent_color ?: '#1F7CF6',
            'products'  => $products,
            'checklist' => Storefront::checklist($shop, $config, $products->count()),
            'publicUrl' => $publicUrl,
            // Le QR code et le lien WhatsApp sont marqués pour apparaître dans Marketing → sources
            'qrSvg'     => self::qrSvg(TrafficSource::tag($publicUrl, 'qr', 'print'), 220),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = $this->shop();

        $request->validate([
            'accent_color'   => ['nullable', Rule::in(ShopController::ACCENT_COLORS)],
            'announcement'   => ['nullable', 'string', 'max:' . Storefront::ANNOUNCEMENT_MAX],
            'sections'       => ['nullable', 'array', 'max:' . count(Storefront::SECTIONS)],
            'featured_ids'   => ['nullable', 'array', 'max:' . Storefront::FEATURED_MAX],
            'featured_ids.*' => ['integer', Rule::exists('products', 'id')->where('shop_id', $shop->id)],
        ], [
            'announcement.max'      => "Le texte d'annonce ne peut pas dépasser " . Storefront::ANNOUNCEMENT_MAX . ' caractères.',
            'featured_ids.max'      => 'Choisissez au maximum ' . Storefront::FEATURED_MAX . ' produits à la une.',
            'featured_ids.*.exists' => 'Un des produits choisis n’appartient pas à votre boutique.',
        ]);

        $shop->update([
            'storefront'   => Storefront::normalize($request->only(['sections', 'announcement', 'layout', 'columns', 'sort', 'cards', 'banner', 'featured_ids'])),
            'accent_color' => $request->input('accent_color') ?: $shop->accent_color,
        ]);

        // « published » ouvre le panneau de partage (lien, WhatsApp, QR code) au retour sur l'éditeur
        return redirect()->route('boutique.storefront.edit')
            ->with('success', 'Vitrine publiée. Vos clients voient maintenant ces réglages.')
            ->with('published', true);
    }

    public function reset(): RedirectResponse
    {
        $shop = $this->shop();
        $shop->update(['storefront' => null, 'accent_color' => null]);

        return redirect()->route('boutique.storefront.edit')->with('success', 'Vitrine réinitialisée avec les réglages par défaut.');
    }

    /** QR code de la vitrine à télécharger (SVG net à toutes les tailles, pour l'imprimer). */
    public function qr(): Response
    {
        $shop = $this->shop();
        $svg = self::qrSvg(TrafficSource::tag(route('public.shops.products', $shop), 'qr', 'print'), 1024);
        abort_unless($svg, 404);

        $file = 'qr-vitrine-' . \Illuminate\Support\Str::slug($shop->name) . '.svg';
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
