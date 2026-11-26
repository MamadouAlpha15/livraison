<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopLook;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Boutique → Looks (thème Lookbook) : créer des tenues mises en scène à partir des produits de la boutique,
 * avec leur photo portée, et indiquer les tailles proposées pour chaque pièce.
 */
class LookController extends Controller
{
    private function shop(): Shop
    {
        $user = Auth::user();
        $shop = $user->shop ?: $user->assignedShop;
        abort_unless($shop, 403);
        return $shop;
    }

    private function own(ShopLook $look): Shop
    {
        $shop = $this->shop();
        abort_unless((int) $look->shop_id === (int) $shop->id, 404);
        return $shop;
    }

    public function index(): View
    {
        $shop = $this->shop();
        $looks = ShopLook::where('shop_id', $shop->id)->with('products:id,name,price,image')->orderBy('position')->orderBy('id')->get();
        $theme = \App\Support\Storefront::forShop($shop)['page_model'] ?? null;
        $cut = [
            'available' => \App\Services\BackgroundRemover::available(),
            'busy'      => \Illuminate\Support\Facades\Cache::has(\App\Jobs\RemoveBackgroundJob::busyKey($shop->id)),
            'items'     => $this->cutoutItems($shop),
        ];
        return view('boutique.looks.index', compact('shop', 'looks', 'theme', 'cut'));
    }

    /** Photos que le thème Lookbook peut afficher détourées : produits en ligne, leurs motifs, les looks. */
    private function cutoutItems(Shop $shop): array
    {
        $items = [];
        $products = Product::where('shop_id', $shop->id)->where('is_active', true)->whereNotNull('image')->orderBy('name')->get(['id', 'name', 'image', 'image_cutout']);
        foreach ($products as $p) $items[] = ['key' => 'product:' . $p->id, 'label' => $p->name, 'image' => $p->image, 'cutout' => $p->image_cutout];
        $variants = \App\Models\ProductVariant::whereIn('product_id', $products->pluck('id'))->where('is_active', true)->whereNotNull('image')->get(['id', 'product_id', 'name', 'image', 'image_cutout']);
        $names = $products->pluck('name', 'id');
        foreach ($variants as $v) $items[] = ['key' => 'variant:' . $v->id, 'label' => $names[$v->product_id] . ' · ' . $v->name, 'image' => $v->image, 'cutout' => $v->image_cutout];
        foreach (ShopLook::where('shop_id', $shop->id)->whereNotNull('image')->get(['id', 'title', 'image', 'image_cutout']) as $l) {
            $items[] = ['key' => 'look:' . $l->id, 'label' => 'Look « ' . $l->title . ' »', 'image' => $l->image, 'cutout' => $l->image_cutout];
        }
        return $items;
    }

    /** « Retirer le fond » (une photo ou toutes celles pas encore détourées) ou « Garder l'originale ». */
    public function cutout(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^(all|(product|variant|look):\d+)$/'],
            'action' => ['required', 'in:run,undo'],
        ]);
        $items = collect($this->cutoutItems($shop))->keyBy('key');

        if ($data['action'] === 'undo') {
            abort_unless($items->has($data['target']), 404);
            [$type, $id] = explode(':', $data['target']);
            $model = ['product' => Product::class, 'variant' => \App\Models\ProductVariant::class, 'look' => ShopLook::class][$type]::find((int) $id);
            \App\Services\BackgroundRemover::forget($model->image_cutout);
            $model->forceFill(['image_cutout' => null])->saveQuietly();
            return back()->with('success', 'La photo d’origine est de nouveau utilisée.');
        }

        abort_unless(\App\Services\BackgroundRemover::available(), 422, 'Le détourage n’est pas activé sur ce serveur.');
        $busy = \App\Jobs\RemoveBackgroundJob::busyKey($shop->id);
        if (\Illuminate\Support\Facades\Cache::has($busy)) return back()->with('success', 'Un détourage est déjà en cours. Actualise la page dans une minute.');

        $keys = $data['target'] === 'all'
            ? $items->filter(fn ($i) => !$i['cutout'])->keys()->take(40)
            : collect([$data['target']])->filter(fn ($k) => $items->has($k));
        if ($keys->isEmpty()) return back()->with('success', 'Toutes les photos sont déjà détourées.');

        $targets = $keys->map(fn ($k) => ['type' => explode(':', $k)[0], 'id' => (int) explode(':', $k)[1]])->values()->all();
        \Illuminate\Support\Facades\Cache::put($busy, true, now()->addMinutes(15));
        $job = new \App\Jobs\RemoveBackgroundJob($shop->id, $targets);
        // File d'attente « sync » (sans worker) : le détourage se fait juste après l'envoi de la page
        if (config('queue.default') === 'sync') dispatch($job)->afterResponse(); else dispatch($job);

        return back()->with('success', count($targets) === 1
            ? 'Détourage lancé : la photo sera prête dans quelques secondes. Actualise la page.'
            : 'Détourage de ' . count($targets) . ' photos lancé : quelques secondes par photo. Actualise la page dans un moment.');
    }

    public function create(): View
    {
        return $this->form($this->shop(), new ShopLook(['is_active' => true]));
    }

    public function edit(ShopLook $look): View
    {
        return $this->form($this->own($look), $look->load('products'));
    }

    private function form(Shop $shop, ShopLook $look): View
    {
        $products = Product::where('shop_id', $shop->id)->orderByDesc('is_active')->orderBy('name')->get(['id', 'name', 'price', 'image', 'is_active', 'sizes']);
        return view('boutique.looks.form', [
            'shop' => $shop, 'look' => $look, 'products' => $products,
            'chosen' => $look->exists ? $look->products->pluck('id')->all() : [],
            'max' => ShopLook::MAX_ITEMS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $look = new ShopLook(['shop_id' => $shop->id, 'position' => (int) ShopLook::where('shop_id', $shop->id)->max('position') + 1]);
        $this->save($request, $shop, $look);
        return redirect()->route('boutique.looks.index')->with('success', 'Look « ' . $look->title . ' » créé.');
    }

    public function update(Request $request, ShopLook $look): RedirectResponse
    {
        $this->save($request, $this->own($look), $look);
        return redirect()->route('boutique.looks.index')->with('success', 'Look « ' . $look->title . ' » enregistré.');
    }

    private function save(Request $request, Shop $shop, ShopLook $look): void
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'min:2', 'max:60'],
            'scene'      => ['nullable', 'string', 'max:120'],
            'image'      => ['nullable', 'image', 'max:8192'],
            'is_active'  => ['nullable', 'boolean'],
            'items'      => ['required', 'array', 'min:1', 'max:' . ShopLook::MAX_ITEMS],
            'items.*'    => ['nullable', 'integer'],
            'sizes'      => ['array'],
            'sizes.*'    => ['nullable', 'string', 'max:80'],
            'pins'       => ['array'],
            'pins.*'     => ['nullable', 'string', 'regex:/^\d{1,3},\d{1,3}$/'],
        ], [
            'title.required' => 'Donne un nom au look, par exemple « Jour de marché ».',
            'items.required' => 'Choisis au moins une pièce.',
            'items.min'      => 'Choisis au moins une pièce.',
            'image.image'    => 'La photo doit être une image (JPG, PNG, WebP).',
        ]);

        // Pièces : uniquement des produits de la boutique, sans doublon, dans l'ordre choisi
        $own = Product::where('shop_id', $shop->id)->pluck('id')->flip();
        $ids = collect($data['items'])->filter()->map(fn ($v) => (int) $v)->unique()->filter(fn ($id) => isset($own[$id]))->values();
        abort_if($ids->isEmpty(), 422, 'Choisis au moins une pièce.');

        // Points sur la photo (« x,y » en %), donnés ligne par ligne : on les rattache au produit de la ligne
        $pins = [];
        foreach ($data['items'] as $k => $pid) {
            $raw = $data['pins'][$k] ?? null;
            if (!$pid || !$raw) continue;
            [$x, $y] = array_map('intval', explode(',', $raw));
            $pins[(int) $pid] = ['pin_x' => min(100, $x), 'pin_y' => min(100, $y)];
        }
        // Nouvelle photo : l'ancienne version sans fond et les anciens points ne correspondent plus
        if ($request->hasFile('image')) $pins = [];

        DB::transaction(function () use ($request, $data, $look, $ids, $pins) {
            $look->fill([
                'title'     => trim(strip_tags($data['title'])),
                'scene'     => trim(strip_tags((string) ($data['scene'] ?? ''))) ?: null,
                'is_active' => $request->boolean('is_active'),
            ]);
            if ($request->hasFile('image')) {
                $look->image = ImageOptimizer::store($request->file('image'), 'looks', true);
                \App\Services\BackgroundRemover::forget($look->image_cutout);
                $look->image_cutout = null;
            }
            $look->save();
            $look->products()->sync($ids->mapWithKeys(fn ($id, $k) => [$id => ['position' => $k] + ($pins[$id] ?? ['pin_x' => null, 'pin_y' => null])])->all());

            // Tailles proposées pour chaque pièce (« S, M, L, XL ») : enregistrées sur le produit
            foreach ($data['sizes'] ?? [] as $pid => $raw) {
                if (!$ids->contains((int) $pid)) continue;
                $sizes = collect(preg_split('/[,;\/]+/', (string) $raw))->map(fn ($s) => mb_strtoupper(mb_substr(trim($s), 0, 8)))->filter()->unique()->take(10)->values()->all();
                Product::whereKey((int) $pid)->update(['sizes' => $sizes ? json_encode($sizes) : null]);
            }
        });
    }

    public function destroy(ShopLook $look): RedirectResponse
    {
        $this->own($look);
        $title = $look->title;
        $look->delete();
        return redirect()->route('boutique.looks.index')->with('success', 'Look « ' . $title . ' » supprimé.');
    }

    /** Monter ou descendre un look dans la vitrine. */
    public function move(Request $request, ShopLook $look): RedirectResponse
    {
        $shop = $this->own($look);
        $list = ShopLook::where('shop_id', $shop->id)->orderBy('position')->orderBy('id')->get()->values();
        $i = $list->search(fn ($l) => $l->id === $look->id);
        $j = $request->input('dir') === 'up' ? $i - 1 : $i + 1;
        if ($i !== false && isset($list[$j])) {
            [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
            foreach ($list as $k => $l) if ($l->position !== $k) $l->forceFill(['position' => $k])->save();
        }
        return redirect()->route('boutique.looks.index');
    }
}
