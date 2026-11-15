<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SalesPageAi;
use App\Support\ProductPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * « Page de vente » d'un produit (Produits → Modifier → Page de vente) : le contenu de la page produit
 * longue (accroche, avantages, packs, sections, questions), enregistré dans products.landing.
 * Même format que celui rempli par Shopio IA (voir App\Support\ProductPage).
 */
class ProductPageController extends Controller
{
    private function own(Product $product): void
    {
        $shop = Auth::user()->shop;
        abort_if(!$shop || $product->shop_id !== $shop->id, 403);
    }

    public function edit(Product $product)
    {
        $this->own($product);
        $shop = Auth::user()->shop;
        return view('vendeur.products.page', [
            'shop' => $shop, 'product' => $product, 'devise' => $shop->currency ?? 'GNF',
            'l' => is_array($product->landing) ? $product->landing : [],
            'unit' => (int) round($product->current_price),
            'publicUrl' => $shop->is_approved ? ProductPage::url($shop, $product) : null,
        ]);
    }

    /**
     * « Remplir avec Shopio IA » : rédige la page et la renvoie au formulaire (rien n'est enregistré :
     * le vendeur relit, corrige, puis enregistre). Réservé au plan Pro, comme les autres outils Shopio IA.
     */
    public function generate(Product $product, SalesPageAi $ai)
    {
        $this->own($product);
        $shop = Auth::user()->shop;
        if (!($shop->plan === 'pro' && $shop->plan_expires_at?->isFuture())) {
            return response()->json(['error' => 'Shopio IA est réservé au plan Pro. Passez au plan Pro pour remplir la page en un clic.'], 403);
        }
        $key = 'ai-page:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json(['error' => 'Limite atteinte (10 pages par heure). Réessayez plus tard.'], 429);
        }
        RateLimiter::hit($key, 3600);

        try {
            return response()->json($ai->generate($product, $shop));
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    public function update(Request $request, Product $product)
    {
        $this->own($product);
        $t = fn (int $max) => ['nullable', 'string', 'max:' . $max];
        $request->validate([
            'hook' => $t(220), 'benefits' => $t(1000), 'promise' => $t(90),
            'packs' => ['nullable', 'array', 'max:' . ProductPage::PACKS_MAX],
            'packs.*.qty' => ['nullable', 'integer', 'min:2', 'max:10'],
            'packs.*.price' => ['nullable', 'integer', 'min:1'],
            'packs.*.label' => $t(20),
            'usage' => $t(1500), 'details' => $t(3000), 'shipping' => $t(1500),
            'highlight.title' => $t(70), 'highlight.text' => $t(600), 'highlight.cta' => $t(24),
            'story.title' => $t(80), 'story.kicker' => $t(140), 'story.text' => $t(1200),
            'audience.title' => $t(60), 'audience.kicker' => $t(80), 'audience.text' => $t(1200),
            'love.title' => $t(80), 'love.kicker' => $t(120), 'love_items' => $t(1500),
            'compare.title' => $t(80), 'compare_rows' => $t(800),
            'faq' => ['nullable', 'array', 'max:10'], 'faq.*.q' => $t(140), 'faq.*.a' => $t(600),
        ], [
            'packs.*.qty.min' => 'Un pack contient au moins 2 produits.',
            'packs.*.qty.max' => 'Un pack contient au plus 10 produits.',
            'packs.*.price.integer' => 'Le prix du pack doit être un nombre entier.',
            '*.max' => 'Ce texte est trop long.',
        ], [
            'hook' => 'accroche', 'promise' => 'promesse de délai',
        ]);

        $lines = fn ($v) => array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $v)), fn ($x) => $x !== ''));
        $s = fn ($v) => trim((string) $v);

        // Packs : quantité et prix remplis ensemble, toujours moins cher que l'achat à l'unité, une quantité par pack
        $unit = (int) round($product->current_price);
        $devise = Auth::user()->shop->currency ?? 'GNF';
        $packs = []; $errors = [];
        foreach ((array) $request->input('packs', []) as $i => $k) {
            $qty = (int) ($k['qty'] ?? 0); $price = (int) ($k['price'] ?? 0);
            if (!$qty && !$price) continue;
            if (!$qty || !$price) { $errors["packs.$i.price"] = 'Indiquez la quantité et le prix du pack ' . ($i + 1) . '.'; continue; }
            if ($price >= $qty * $unit) { $errors["packs.$i.price"] = 'Le pack de ' . $qty . ' doit coûter moins de ' . number_format($qty * $unit, 0, ',', ' ') . ' ' . $devise . ' (' . $qty . ' × le prix à l’unité).'; continue; }
            if (isset($packs[$qty])) { $errors["packs.$i.qty"] = 'Il y a déjà un pack de ' . $qty . ' produits.'; continue; }
            $packs[$qty] = ['qty' => $qty, 'price' => $price, 'label' => $s($k['label'] ?? '')];
        }
        if ($errors) throw ValidationException::withMessages($errors);
        ksort($packs);

        $loveItems = array_map(function ($line) {
            [$title, $text] = array_pad(preg_split('/\s*:\s*/u', $line, 2), 2, '');
            return ['title' => trim($title), 'text' => trim($text)];
        }, array_slice($lines($request->input('love_items')), 0, 6));
        $faq = array_values(array_filter(array_map(fn ($f) => ['q' => $s($f['q'] ?? ''), 'a' => $s($f['a'] ?? '')], (array) $request->input('faq', [])), fn ($f) => $f['q'] !== '' && $f['a'] !== ''));

        $block = fn (string $k, array $keys) => array_combine($keys, array_map(fn ($f) => $s($request->input("$k.$f")), $keys));
        $product->landing = [
            'hook' => $s($request->input('hook')),
            'benefits' => array_slice($lines($request->input('benefits')), 0, 8),
            'promise' => $s($request->input('promise')),
            'packs' => array_values($packs),
            'usage' => $s($request->input('usage')), 'details' => $s($request->input('details')), 'shipping' => $s($request->input('shipping')),
            'highlight' => $block('highlight', ['title', 'text', 'cta']),
            'story' => $block('story', ['title', 'kicker', 'text']),
            'audience' => $block('audience', ['title', 'kicker', 'text']),
            'love' => $block('love', ['title', 'kicker']) + ['items' => $loveItems],
            'compare' => $block('compare', ['title']) + ['rows' => array_slice($lines($request->input('compare_rows')), 0, 8)],
            'faq' => array_slice($faq, 0, 10),
        ];
        $product->save();

        return redirect()->route('products.page.edit', $product)->with('success', 'Page de vente enregistrée.');
    }
}
