{{--
    resources/views/boutique/looks/form.blade.php
    Route     : GET /boutique/looks/nouveau, GET /boutique/looks/{look}/modifier → Boutique\LookController
    Rôle      : Créer ou modifier un look : nom, scène, photo portée, pièces (produits) et tailles de chaque pièce.
    Variables : $shop, $look, $products, $chosen (ids des pièces), $max
--}}
@extends('layouts.app')
@section('title', ($look->exists ? 'Modifier le look' : 'Nouveau look') . ' · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    $img = fn ($p) => $p ? (\App\Services\ImageOptimizer::url($p, 'medium') ?? asset('storage/' . $p)) : null;
    $slots = array_pad(old('items', $chosen), $max, null);
    $sizesOf = fn ($p) => implode(', ', json_decode((string) $p->sizes, true) ?: []);
    // Points sur la photo : la photo affichée dans la vitrine (version sans fond en priorité)
    $pinPhoto = $img($look->image_cutout) ?? $img($look->image);
    $pinOf = [];
    if ($look->exists) foreach ($look->products as $p) if ($p->pivot->pin_x !== null) $pinOf[$p->id] = $p->pivot->pin_x . ',' . $p->pivot->pin_y;
    $pinSlots = collect($slots)->map(fn ($sel, $k) => $sel ? old('pins.' . $k, $pinOf[(int) $sel] ?? '') : '')->all();
@endphp
@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.lf { padding: 18px 28px 80px; max-width: 820px; width: 100%; }
.lf-back { display: inline-grid; place-items: center; width: 34px; height: 34px; border-radius: 9px; color: var(--text-2); text-decoration: none; box-shadow: inset 0 0 0 1px var(--border); background: var(--surface); }
.lf h1 { margin: 14px 0 4px; font-size: 26px; font-weight: 800; letter-spacing: -.02em; }
.lf > p { margin: 0 0 16px; color: var(--text-2); font-size: 14px; }
.lf-card { padding: 18px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); margin-bottom: 12px; display: grid; gap: 14px; }
.lf-card h2 { margin: 0; font-size: 15px; }
.lf-lbl { display: block; font-weight: 700; font-size: 13.5px; margin-bottom: 6px; }
.lf-hint { display: block; color: var(--text-2); font-size: 12px; margin-top: 4px; }
.lf-in, .lf-sel { width: 100%; min-height: 42px; padding: 0 12px; border: 0; border-radius: 10px; background: var(--bg); color: var(--text); font: 600 14px var(--font); box-shadow: inset 0 0 0 1px var(--border); }
.lf-in:focus, .lf-sel:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue, #1F7CF6); }
.lf-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.lf-photo { display: grid; grid-template-columns: 120px 1fr; gap: 14px; align-items: center; }
.lf-photo img, .lf-photo .ph { width: 120px; height: 150px; border-radius: 12px; object-fit: cover; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); }
.lf-piece { display: grid; grid-template-columns: 26px 1fr 190px; gap: 10px; align-items: center; }
.lf-piece b { display: grid; place-items: center; width: 26px; height: 26px; border-radius: 999px; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); font-size: 12px; }
.lf-chk { display: flex; gap: 10px; align-items: center; font-weight: 700; font-size: 14px; }
.lf-chk input { width: 18px; height: 18px; accent-color: var(--sv-btn, #1F7CF6); }
.lf-acts { display: flex; gap: 8px; flex-wrap: wrap; justify-content: space-between; align-items: center; }
.lf-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; border: 0; border-radius: 10px; font: 700 14px var(--font); cursor: pointer; text-decoration: none; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); }
.lf-btn.is-main { background: var(--sv-btn, #1F7CF6); color: #fff; box-shadow: none; }
.lf-btn.is-del { color: #DC2626; }
.lf-pins { display: grid; grid-template-columns: minmax(0, 260px) 1fr; gap: 16px; align-items: start; }
.lf-pinbox { position: relative; border-radius: 12px; background: #fff; box-shadow: inset 0 0 0 1px var(--border); touch-action: none; user-select: none; }
.lf-pinbox img { display: block; width: 100%; height: auto; border-radius: 12px; pointer-events: none; }
.lf-dot { position: absolute; width: 30px; height: 30px; margin: -15px 0 0 -15px; display: grid; place-items: center; border: 0; border-radius: 999px; background: #151413; color: #fff; font: 700 12px var(--font); cursor: grab; box-shadow: 0 0 0 3px rgba(255,255,255,.9), 0 6px 14px -4px rgba(0,0,0,.4); }
.lf-dot:active { cursor: grabbing; }
.lf-dot[hidden] { display: none; }
.lf-err { padding: 12px 14px; border-radius: 12px; background: #FEF2F2; color: #DC2626; font-weight: 700; font-size: 13.5px; margin-bottom: 12px; }
@media (max-width: 700px) { .lf-pins { grid-template-columns: 1fr; } .lf { padding: 14px 14px 80px; } .lf-row { grid-template-columns: 1fr; } .lf-piece { grid-template-columns: 26px 1fr; } .lf-piece .lf-in { grid-column: 2; } .lf-photo { grid-template-columns: 90px 1fr; } .lf-photo img, .lf-photo .ph { width: 90px; height: 112px; } }
</style>
@include('boutique._partials.sv-theme')
@endpush
@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'looks'])
    <main class="main vs-main">
        <div class="lf">
            <a class="lf-back" href="{{ route('boutique.looks.index') }}" aria-label="Retour aux looks">‹</a>
            <h1>{{ $look->exists ? 'Modifier « ' . $look->title . ' »' : 'Nouveau look' }}</h1>
            <p>Un look, c’est une tenue mise en scène : une robe et un sac, un ensemble pagne… Le client peut tout commander d’un coup, ou une seule pièce.</p>
            @if($errors->any())<p class="lf-err" role="alert">{{ $errors->first() }}</p>@endif

            <form method="POST" action="{{ $look->exists ? route('boutique.looks.update', $look) : route('boutique.looks.store') }}" enctype="multipart/form-data">
                @csrf @if($look->exists) @method('PUT') @endif
                <section class="lf-card">
                    <h2>Le look</h2>
                    <div class="lf-row">
                        <div><label class="lf-lbl" for="lfTitle">Nom</label><input class="lf-in" id="lfTitle" name="title" maxlength="60" required placeholder="Ex. : Jour de marché" value="{{ old('title', $look->title) }}"><span class="lf-hint">Court et évocateur : il s’affiche en grand dans la vitrine.</span></div>
                        <div><label class="lf-lbl" for="lfScene">Petite description (facultatif)</label><input class="lf-in" id="lfScene" name="scene" maxlength="120" placeholder="Ex. : Robe pagne et cabas assorti" value="{{ old('scene', $look->scene) }}"></div>
                    </div>
                    <div class="lf-photo">
                        @if($u = $img($look->image))<img src="{{ $u }}" alt="Photo actuelle du look">@else<div class="ph"></div>@endif
                        <div><label class="lf-lbl" for="lfImg">Photo du look porté (facultatif)</label><input class="lf-in" id="lfImg" name="image" type="file" accept="image/*" style="padding:9px"><span class="lf-hint">Idéalement en pied, sur fond clair ou sans fond. Sans photo, celle de la première pièce est utilisée.</span></div>
                    </div>
                    <label class="lf-chk"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $look->is_active ?? true))> En ligne dans la vitrine</label>
                </section>

                <section class="lf-card">
                    <h2>Les pièces</h2>
                    <span class="lf-hint" style="margin-top:-8px">Choisis jusqu’à {{ $max }} produits, dans l’ordre d’affichage. À droite, les tailles proposées (laisse vide pour un sac, un pagne…). Les motifs viennent des variantes de chaque produit.</span>
                    @foreach($slots as $k => $sel)
                    <div class="lf-piece">
                        <b>{{ $k + 1 }}</b>
                        <select class="lf-sel" name="items[{{ $k }}]" aria-label="Pièce {{ $k + 1 }}" data-piece>
                            <option value="">— Aucune —</option>
                            @foreach($products as $p)<option value="{{ $p->id }}" data-sizes="{{ $sizesOf($p) }}" @selected((int) $sel === $p->id)>{{ $p->name }}{{ $p->is_active ? '' : ' (hors ligne)' }}</option>@endforeach
                        </select>
                        @php $cur = $products->firstWhere('id', (int) $sel); @endphp
                        <input class="lf-in" data-sizes-in @if($cur) name="sizes[{{ $cur->id }}]" @endif placeholder="Tailles : S, M, L, XL" aria-label="Tailles de la pièce {{ $k + 1 }}" value="{{ $cur ? $sizesOf($cur) : '' }}" @unless($cur) disabled @endunless>
                    </div>
                    @endforeach
                </section>

                <section class="lf-card">
                    <h2>Les points sur la photo</h2>
                    @if($pinPhoto)
                    <div class="lf-pins">
                        <div class="lf-pinbox" id="lfPinBox">
                            <img src="{{ $pinPhoto }}" alt="Photo du look">
                            @foreach($slots as $k => $sel)
                            <button type="button" class="lf-dot" data-dot="{{ $k }}" aria-label="Point de la pièce {{ $k + 1 }}" @unless($sel) hidden @endunless>{{ $k + 1 }}</button>
                            <input type="hidden" name="pins[{{ $k }}]" value="{{ $pinSlots[$k] }}" data-pin-in="{{ $k }}">
                            @endforeach
                        </div>
                        <span class="lf-hint" style="margin:0">Fais glisser chaque numéro sur sa pièce (la robe, le sac…). Dans la vitrine, le client touche le point pour voir la pièce et la commander.<br><br>Si tu changes la photo ou si tu retires son fond, replace les points.</span>
                    </div>
                    @else
                    <span class="lf-hint" style="margin-top:-8px">Ajoute une photo du look puis enregistre : tu pourras placer un point numéroté sur chaque pièce. Sans photo, les points sont placés automatiquement.</span>
                    @endif
                </section>

                <div class="lf-acts">
                    <button type="submit" class="lf-btn is-main">{{ $look->exists ? 'Enregistrer' : 'Créer le look' }}</button>
                    <a class="lf-btn" href="{{ route('boutique.looks.index') }}">Annuler</a>
                </div>
            </form>
            @if($look->exists)
            <form method="POST" action="{{ route('boutique.looks.destroy', $look) }}" onsubmit="return confirm('Supprimer ce look ? Les produits ne sont pas supprimés.')" style="margin-top:14px">
                @csrf @method('DELETE')<button type="submit" class="lf-btn is-del">Supprimer ce look</button>
            </form>
            @endif
        </div>
    </main>
</div>
<script>
// Le champ « tailles » suit le produit choisi dans chaque ligne
document.querySelectorAll('[data-piece]').forEach(sel => sel.addEventListener('change', () => {
    const inp = sel.parentElement.querySelector('[data-sizes-in]'), opt = sel.selectedOptions[0];
    if (!sel.value) { inp.value = ''; inp.disabled = true; inp.removeAttribute('name'); return; }
    inp.disabled = false; inp.name = 'sizes[' + sel.value + ']'; inp.value = opt.dataset.sizes || '';
}));

// Points sur la photo : un numéro par pièce choisie, à faire glisser sur la pièce
(function () {
    const box = document.getElementById('lfPinBox');
    if (!box) return;
    const sels = [...document.querySelectorAll('[data-piece]')];
    const inp = k => box.querySelector('[data-pin-in="' + k + '"]');
    const dot = k => box.querySelector('[data-dot="' + k + '"]');
    function place(k) {
        let v = inp(k).value;
        if (!v) { const n = sels.filter(s => s.value).length, i = sels.slice(0, k).filter(s => s.value).length; v = '50,' + Math.round(n < 2 ? 50 : 22 + 60 * i / (n - 1)); }
        const [x, y] = v.split(',').map(Number);
        dot(k).style.left = x + '%'; dot(k).style.top = y + '%';
    }
    function sync() { sels.forEach((s, k) => { dot(k).hidden = !s.value; if (!s.value) inp(k).value = ''; place(k); }); }
    sels.forEach(s => s.addEventListener('change', sync));
    let drag = null;
    box.addEventListener('pointerdown', e => { const d = e.target.closest('[data-dot]'); if (!d) return; drag = +d.dataset.dot; d.setPointerCapture(e.pointerId); e.preventDefault(); });
    box.addEventListener('pointermove', e => {
        if (drag === null) return;
        const r = box.getBoundingClientRect();
        const x = Math.round(Math.min(100, Math.max(0, (e.clientX - r.left) / r.width * 100)));
        const y = Math.round(Math.min(100, Math.max(0, (e.clientY - r.top) / r.height * 100)));
        inp(drag).value = x + ',' + y; place(drag);
    });
    ['pointerup', 'pointercancel'].forEach(t => box.addEventListener(t, () => { drag = null; }));
    sync();
})();
</script>
@endsection
