<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SalesPageAi;
use App\Services\SectionAi;
use App\Support\HomeSections;
use App\Support\ProductDesign;
use App\Support\ProductPage;
use App\Support\Storefront;
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
        $sf = Storefront::forShop($shop);
        $l = is_array($product->landing) ? $product->landing : [];
        $design = ProductDesign::normalize($l['design'] ?? null);
        $ownPage = $design['page'] !== null;
        if (!$ownPage) $design['page'] = $sf['home']['product'];
        $ownForm = $design['form_own'];
        if (!$ownForm) $design['form'] = $sf['form'];
        unset($l['design']);

        $products = $shop->products()->latest()->take(80)->get(['id', 'name', 'is_active'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'active' => (bool) $p->is_active, 'url' => route('products.page.edit', $p)])->values();

        return view('vendeur.products.page', [
            'shop' => $shop, 'product' => $product, 'devise' => $shop->currency ?? 'GNF',
            'l' => $l, 'design' => $design, 'ownPage' => $ownPage, 'ownForm' => $ownForm,
            'unit' => (int) round($product->current_price),
            'photos' => ProductPage::photos($product),
            'products' => $products,
            'images' => HomeSections::images(['p' => $design['page']]),
            'categories' => collect(Storefront::categoryLinks($shop))->pluck('name')->values(),
            'rating' => $this->rating($shop),
            'publicUrl' => $shop->is_approved ? ProductPage::url($shop, $product) : null,
        ]);
    }

    /** Vraie note de la boutique (bandeau note) : jamais saisie. */
    private function rating($shop): ?array
    {
        $r = \App\Models\Review::where('vendeur_id', $shop->user_id)->selectRaw('COUNT(*) as n, AVG(rating) as avg')->first();
        return ($r->n ?? 0) > 0 ? ['avg' => round((float) $r->avg, 1), 'count' => (int) $r->n] : null;
    }

    /**
     * « Remplir avec Shopio IA » : rédige la page et la renvoie au formulaire (rien n'est enregistré :
     * le vendeur relit, corrige, puis enregistre). Réservé au plan Pro, comme les autres outils Shopio IA.
     */
    public function generate(Request $request, Product $product, SalesPageAi $ai)
    {
        $this->own($product);
        $shop = Auth::user()->shop;
        if (!($shop->plan === 'pro' && $shop->plan_expires_at?->isFuture())) {
            return response()->json(['error' => 'Shopio IA est réservé au plan Pro. Passez au plan Pro pour remplir la page en un clic.'], 403);
        }
        // Consigne facultative du vendeur (ton, occasion…), jamais au-dessus des règles d'honnêteté
        $instructions = $request->validate(['instructions' => ['nullable', 'string', 'max:500']])['instructions'] ?? '';
        $key = 'ai-page:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json(['error' => 'Limite atteinte (10 pages par heure). Réessayez plus tard.'], 429);
        }
        RateLimiter::hit($key, 3600);

        try {
            return response()->json($ai->generate($product, $shop, false, (string) $instructions));
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * « Rédiger avec Shopio IA » pour les sections de la page (une section, ou toutes les sections ajoutées) :
     * renvoie leur contenu rédigé ; rien n'est enregistré avant « Sauvegarder ». Plan Pro, 30 rédactions par heure.
     */
    public function sectionsAi(Request $request, Product $product, SectionAi $ai)
    {
        $this->own($product);
        $shop = Auth::user()->shop;
        if (!($shop->plan === 'pro' && $shop->plan_expires_at?->isFuture())) {
            return response()->json(['error' => 'Shopio IA est réservé au plan Pro. Passez au plan Pro pour rédiger vos sections en un clic.'], 403);
        }
        $data = $request->validate([
            'sections' => ['required', 'array', 'min:1', 'max:40'],
            'sections.*.id' => ['required', 'string', 'max:24'],
            'sections.*.type' => ['required', 'string', 'max:24'],
            'instructions' => ['nullable', 'string', 'max:500'],
        ]);
        $key = 'ai-sections:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['error' => 'Limite atteinte (30 rédactions par heure). Réessayez plus tard.'], 429);
        }
        RateLimiter::hit($key, 3600);

        try {
            return response()->json(['sections' => (object) $ai->write($shop, 'product', $request->input('sections'), $product, (string) ($data['instructions'] ?? ''))]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * Galerie → « Photos pro » : Shopio IA crée une photo du produit (fond blanc studio ou mise en scène) à partir
     * de sa vraie photo. Elle n'est ajoutée au produit qu'après accord du vendeur (photoAttach).
     */
    public function photoAi(Request $request, Product $product, \App\Services\SectionImageAi $ai)
    {
        $this->own($product);
        $shop = Auth::user()->shop;
        if (!($shop->plan === 'pro' && $shop->plan_expires_at?->isFuture())) {
            return response()->json(['error' => 'Les photos Shopio IA sont réservées au plan Pro.'], 403);
        }
        $data = $request->validate([
            'style' => ['required', \Illuminate\Validation\Rule::in(array_keys(\App\Services\SectionImageAi::STYLES))],
            'wish'  => ['nullable', 'string', 'max:300'],
        ]);
        $key = 'ai-images:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, \App\Services\SectionImageAi::DAILY)) {
            return response()->json(['error' => 'Limite atteinte (' . \App\Services\SectionImageAi::DAILY . ' photos par jour). Réessayez demain.'], 429);
        }
        RateLimiter::hit($key, 86400);
        try {
            $r = $ai->make($shop, 'product', $product, (string) ($data['wish'] ?? ''), $data['style'], null, 'products/gallery');
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }
        // Seules les photos créées ici, pour ce produit, pourront être ajoutées
        $made = array_slice(array_merge($request->session()->get('ai_product_photos.' . $product->id, []), [$r['path']]), -20);
        $request->session()->put('ai_product_photos.' . $product->id, $made);
        return response()->json($r);
    }

    /** Ajoute une photo créée par Shopio IA à la galerie du produit, ou en fait la photo principale. */
    public function photoAttach(Request $request, Product $product)
    {
        $this->own($product);
        $data = $request->validate(['path' => ['required', 'string', 'regex:' . \App\Support\ProductPhotos::PATH], 'main' => ['boolean']]);
        if (!in_array($data['path'], $request->session()->get('ai_product_photos.' . $product->id, []), true)) {
            return response()->json(['error' => 'Photo inconnue : créez-la de nouveau.'], 422);
        }
        $gallery = array_values(array_filter($product->gallery_array, fn ($p) => $p !== $data['path']));
        if (count($gallery) >= 20) return response()->json(['error' => 'Galerie pleine (20 photos) : retirez-en une dans la fiche du produit.'], 422);
        if (!empty($data['main'])) {
            if ($product->image) array_unshift($gallery, $product->image);
            $product->image = $data['path'];
        } else {
            $gallery[] = $data['path'];
        }
        $product->gallery = $gallery ? json_encode(array_values($gallery)) : null;
        $product->save();
        return response()->json(['photos' => ProductPage::photos($product->fresh())]);
    }

    /** « Créer la photo avec Shopio IA » dans une section de la page produit (voir App\Services\SectionImageAi). */
    public function sectionImage(Request $request, Product $product, \App\Services\SectionImageAi $ai)
    {
        $this->own($product);
        return $ai->respond($request, Auth::user()->shop);
    }

    /**
     * Éditeur produit (JSON) : contenu de la page de vente, mise en page (bloc d'achat, sections), couleurs,
     * formulaire (de ce produit ou de toute la boutique) et offres. Les packs sont vérifiés ici.
     */
    public function update(Request $request, Product $product)
    {
        $this->own($product);
        $shop = Auth::user()->shop;
        $request->validate([
            'content' => ['required', 'array'],
            'design' => ['required', 'array'],
            'form_scope' => ['required', 'in:shop,product'],
            'page_scope' => ['required', 'in:shop,product,all'],
            'content.packs' => ['nullable', 'array', 'max:' . ProductPage::PACKS_MAX],
            'content.faq' => ['nullable', 'array', 'max:10'],
        ]);
        $c = (array) $request->input('content');
        $s = fn ($v, int $max) => mb_substr(trim(strip_tags((string) (is_scalar($v) ? $v : ''))), 0, $max);
        $list = fn ($v, int $n, int $max) => array_slice(array_values(array_filter(array_map(fn ($x) => $s($x, $max), is_array($v) ? $v : []), fn ($x) => $x !== '')), 0, $n);
        $block = function ($v, array $keys) use ($s) {
            $out = [];
            foreach ($keys as $k => $max) $out[$k] = $s(is_array($v) ? ($v[$k] ?? '') : '', $max);
            return $out;
        };

        // Packs : quantité et prix remplis ensemble, toujours moins cher que l'achat à l'unité, une quantité par pack
        $unit = (int) round($product->current_price);
        $devise = $shop->currency ?? 'GNF';
        $packs = []; $errors = [];
        foreach (array_values((array) ($c['packs'] ?? [])) as $i => $k) {
            $qty = (int) ($k['qty'] ?? 0); $price = (int) preg_replace('/\D/', '', (string) ($k['price'] ?? ''));
            if (!$qty && !$price) continue;
            if ($qty < 2 || $qty > 10) { $errors["packs.$i"] = 'Un pack contient de 2 à 10 produits.'; continue; }
            if (!$price) { $errors["packs.$i"] = 'Indiquez le prix total du pack de ' . $qty . '.'; continue; }
            if ($price >= $qty * $unit) { $errors["packs.$i"] = 'Le pack de ' . $qty . ' doit coûter moins de ' . number_format($qty * $unit, 0, ',', ' ') . ' ' . $devise . ' (' . $qty . ' × le prix à l’unité).'; continue; }
            if (isset($packs[$qty])) { $errors["packs.$i"] = 'Il y a déjà un pack de ' . $qty . ' produits.'; continue; }
            $packs[$qty] = ['qty' => $qty, 'price' => $price, 'label' => $s($k['label'] ?? '', 20)];
        }
        if ($errors) throw ValidationException::withMessages($errors);
        ksort($packs);

        $love = is_array($c['love'] ?? null) ? $c['love'] : [];
        $compare = is_array($c['compare'] ?? null) ? $c['compare'] : [];
        $faq = array_slice(array_values(array_filter(array_map(fn ($f) => ['q' => $s($f['q'] ?? '', 140), 'a' => $s($f['a'] ?? '', 600)], is_array($c['faq'] ?? null) ? $c['faq'] : []), fn ($f) => $f['q'] !== '' && $f['a'] !== '')), 0, 10);

        $design = ProductDesign::normalize($request->input('design'));
        // Formulaire : pour toute la boutique, ou propre à ce produit
        $sf = Storefront::forShop($shop);
        if ($request->input('form_scope') === 'shop') {
            $sf['form'] = $design['form'];
            $design['form_own'] = false;
        } else {
            $design['form_own'] = true;
        }
        // Sections de la page : propres à ce produit, celles de la boutique, ou appliquées à tous les produits
        $scope = $request->input('page_scope');
        if ($scope === 'all' && $design['page']) $sf['home']['product'] = $design['page'];
        if ($scope !== 'product') $design['page'] = null;
        $shop->update(['storefront' => Storefront::normalize($sf)]);

        $product->landing = [
            'hook' => $s($c['hook'] ?? '', 220),
            'benefits' => $list($c['benefits'] ?? [], 8, 90),
            'promise' => $s($c['promise'] ?? '', 90),
            'packs' => array_values($packs),
            'usage' => $s($c['usage'] ?? '', 1500), 'details' => $s($c['details'] ?? '', 3000), 'shipping' => $s($c['shipping'] ?? '', 1500),
            'highlight' => $block($c['highlight'] ?? [], ['title' => 70, 'text' => 600, 'cta' => 24]),
            'story' => $block($c['story'] ?? [], ['title' => 80, 'kicker' => 140, 'text' => 1200]),
            'audience' => $block($c['audience'] ?? [], ['title' => 60, 'kicker' => 80, 'text' => 1200]),
            'love' => $block($love, ['title' => 80, 'kicker' => 120]) + ['items' => array_slice(array_values(array_filter(array_map(fn ($i) => ['title' => $s($i['title'] ?? '', 60), 'text' => $s($i['text'] ?? '', 160)], is_array($love['items'] ?? null) ? $love['items'] : []), fn ($i) => $i['title'] !== '')), 0, 6)],
            'compare' => $block($compare, ['title' => 80]) + ['rows' => $list($compare['rows'] ?? [], 8, 60)],
            'faq' => $faq,
            'design' => $design,
        ];
        $product->save();

        return response()->json(['ok' => true, 'packs' => array_values($packs)]);
    }
}
