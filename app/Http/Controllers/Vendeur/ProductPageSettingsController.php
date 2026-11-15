<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Support\PageModels;
use App\Support\ProductPageSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Réglages communs des pages produit de la boutique (bandeau, couleur, badges, étapes, FAQ, livraison
 * par commune). La page d'un produit se construit dans l'éditeur de page (Vendeur\PageBuilderController).
 * Les lignes laissées vides sont ignorées.
 */
class ProductPageSettingsController extends Controller
{
    /* ── Réglages communs de la boutique ── */

    public function editShop()
    {
        $shop = $this->shop();
        return view('vendeur.product-page.shop', [
            'shop'     => $shop,
            'page'     => ProductPageSettings::shop($shop),
            'sample'   => Product::where('shop_id', $shop->id)->where('is_active', true)->latest()->first(),
        ]);
    }

    public function updateShop(Request $request)
    {
        $shop = $this->shop();
        $data = $request->validate([
            'banner'              => ['nullable', 'string', 'max:120'],
            'accent'              => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'badges'              => ['array'],
            'badges.*'            => [Rule::in(array_keys(ProductPageSettings::BADGES))],
            'steps'               => ['array', 'max:4'],
            'steps.*'             => ['nullable', 'string', 'max:120'],
            'faq'                 => ['array', 'max:' . ProductPageSettings::MAX_FAQ],
            'faq.*.q'             => ['nullable', 'string', 'max:160'],
            'faq.*.a'             => ['nullable', 'string', 'max:600', 'required_with:faq.*.q'],
            'delivery_fees'       => ['array'],
            'delivery_fees.*'     => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'template'            => ['required', Rule::in(array_keys(ProductPageSettings::TEMPLATES))],
            'guarantee'           => ['nullable', 'string', 'max:200'],
        ], [
            'accent.regex'         => 'Choisis une couleur dans la liste.',
            'faq.*.a.required_with' => 'Écris la réponse de chaque question.',
            'delivery_fees.*.integer' => 'Indique les frais de livraison en chiffres (ex. 20000).',
        ]);

        $shop->forceFill(['product_page' => [
            'banner'            => trim((string) ($data['banner'] ?? '')),
            'accent'            => strtoupper($data['accent']),
            'badges'            => array_values($data['badges'] ?? []),
            'steps'             => array_values(array_filter(array_map('trim', $data['steps'] ?? []))),
            'faq'               => $this->pairs($data['faq'] ?? []),
            'delivery_fees'     => collect($data['delivery_fees'] ?? [])->only(PageModels::COMMUNES)
                ->filter(fn ($v) => $v !== null && $v !== '')->map(fn ($v) => (int) $v)->all(),
            'show_offer_button' => $request->boolean('show_offer_button'),
            'template'          => $data['template'],
            'guarantee'         => filled($data['guarantee'] ?? null) ? trim($data['guarantee']) : null,
            'whatsapp'          => $request->boolean('whatsapp'),
        ]])->save();

        return back()->with('success', 'Réglages des pages produit enregistrés.');
    }

    /** Questions / réponses remplies seulement */
    private function pairs(array $rows): array
    {
        return array_values(array_map(fn ($r) => ['q' => trim($r['q']), 'a' => trim($r['a'])],
            array_filter($rows, fn ($r) => filled($r['q'] ?? null) && filled($r['a'] ?? null))));
    }

    private function shop(): Shop
    {
        $shop = Auth::user()->shop;
        abort_unless($shop, 403);
        return $shop;
    }
}
