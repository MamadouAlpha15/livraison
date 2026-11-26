{{--
    resources/views/boutique/looks/index.blade.php
    Route     : GET /boutique/looks → Boutique\LookController@index → name('boutique.looks.index')
    Rôle      : Liste des looks (thème Lookbook) : photo, pièces, total, en ligne ou non, ordre dans la vitrine.
    Variables : $shop, $looks, $theme (thème publié)
--}}
@extends('layouts.app')
@section('title', 'Looks · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    $img = fn ($p) => $p ? (\App\Services\ImageOptimizer::url($p, 'thumb') ?? asset('storage/' . $p)) : null;
    $n = fn ($v) => number_format((int) $v, 0, ',', ' ');
@endphp
@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.lk { padding: 18px 28px 80px; max-width: 980px; width: 100%; }
.lk-head { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
.lk-head > div { flex: 1; min-width: 240px; }
.lk h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; }
.lk-head p { margin: 4px 0 0; color: var(--text-2); font-size: 14px; }
.lk-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; padding: 0 16px; border: 0; border-radius: 10px; font: 700 13.5px var(--font); cursor: pointer; text-decoration: none; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); white-space: nowrap; }
.lk-btn.is-main { background: var(--sv-btn, #1F7CF6); color: #fff; box-shadow: none; }
.lk-btn.is-sm { min-height: 32px; padding: 0 10px; font-size: 12.5px; }
.lk-flash { padding: 12px 14px; border-radius: 12px; font-weight: 700; font-size: 13.5px; margin-bottom: 12px; background: var(--sv-ok50, #F0FDF4); color: var(--sv-ok, #15803D); }
.lk-note { padding: 12px 14px; border-radius: 12px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); font-size: 13.5px; color: var(--text-2); margin-bottom: 12px; }
.lk-note a { color: var(--sv-btn, #1F7CF6); font-weight: 800; }
.lk-list { display: grid; gap: 10px; }
.lk-card { display: grid; grid-template-columns: 92px 1fr auto; gap: 14px; align-items: center; padding: 12px; border-radius: 16px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); }
.lk-card.is-off { opacity: .6; }
.lk-ph { width: 92px; height: 112px; border-radius: 12px; background: var(--bg); object-fit: cover; }
.lk-card h2 { margin: 0; font-size: 16px; }
.lk-card small { display: block; color: var(--text-2); font-size: 12.5px; margin-top: 2px; }
.lk-pieces { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
.lk-pieces span { display: inline-flex; align-items: center; gap: 6px; padding: 3px 8px 3px 3px; border-radius: 999px; background: var(--bg); font-size: 12px; font-weight: 700; }
.lk-pieces img, .lk-pieces i { width: 22px; height: 22px; border-radius: 999px; object-fit: cover; background: var(--border); }
.lk-acts { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }
.lk-acts form { margin: 0; }
.lk-mv { display: flex; gap: 4px; }
.lk-pill { padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 800; background: #F0FDF4; color: #15803D; }
.lk-pill.off { background: var(--bg); color: var(--text-2); }
.lk-empty { padding: 40px 20px; text-align: center; border-radius: 16px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); color: var(--text-2); }
.lk-empty b { display: block; color: var(--text); font-size: 17px; margin-bottom: 6px; }
.cut { margin-top: 18px; padding: 16px; border-radius: 16px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); display: grid; gap: 12px; }
.cut-hd { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.cut-hd > div { flex: 1; min-width: 220px; }
.cut h2 { margin: 0; font-size: 16px; }
.cut-hd p { margin: 2px 0 0; color: var(--text-2); font-size: 13px; }
.cut-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
.cut-it { display: grid; gap: 6px; padding: 8px; border-radius: 12px; background: var(--bg); }
.cut-ph { position: relative; aspect-ratio: 3 / 4; border-radius: 10px; overflow: hidden; background: repeating-conic-gradient(#EDF0F5 0% 25%, #fff 0% 50%) 50% / 16px 16px; }
.cut-ph img { width: 100%; height: 100%; object-fit: contain; }
.cut-ph span { position: absolute; top: 6px; left: 6px; padding: 2px 7px; border-radius: 999px; font-size: 10.5px; font-weight: 800; background: #fff; color: var(--text-2); }
.cut-ph span.ok { background: #F0FDF4; color: #15803D; }
.cut-it b { font-size: 12px; line-height: 1.3; }
.cut-it form { margin: 0; }
.cut-busy { padding: 10px 12px; border-radius: 10px; background: #EFF6FF; color: #1667D6; font-weight: 700; font-size: 13px; }
@media (max-width: 700px) { .lk { padding: 14px 14px 80px; } .lk-card { grid-template-columns: 70px 1fr; } .lk-ph { width: 70px; height: 86px; } .lk-acts { grid-column: 1 / -1; flex-direction: row; justify-content: flex-end; flex-wrap: wrap; } }
</style>
@include('boutique._partials.sv-theme')
@endpush
@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'looks'])
    <main class="main vs-main">
        <div class="lk">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu" style="margin-bottom:8px">☰</button>
            <div class="lk-head">
                <div><h1>Looks</h1><p>Des tenues mises en scène avec tes produits : le client commande le look entier ou une seule pièce.</p></div>
                <a class="lk-btn is-main" href="{{ route('boutique.looks.create') }}">+ Nouveau look</a>
            </div>
            @if(session('success'))<p class="lk-flash" role="status">{{ session('success') }}</p>@endif
            @if($theme !== 'lookbook')
            <p class="lk-note">Les looks s’affichent avec le thème <b>Lookbook</b>. <a href="{{ \App\Support\Storefront::publicUrl($shop) }}?modele=lookbook">Essayer le thème sur ma vitrine</a></p>
            @endif

            @if($looks->isEmpty())
                <div class="lk-empty"><b>Aucun look pour l’instant</b>Crée ton premier look : une robe et un sac, une tenue de fête, un ensemble pagne…</div>
            @else
            <div class="lk-list">
                @foreach($looks as $k => $look)
                @php $photo = $img($look->image) ?? $img($look->products->first()?->image); @endphp
                <article class="lk-card {{ $look->is_active ? '' : 'is-off' }}">
                    @if($photo)<img class="lk-ph" src="{{ $photo }}" alt="" loading="lazy">@else<div class="lk-ph"></div>@endif
                    <div>
                        <h2>{{ $look->title }} <span class="lk-pill {{ $look->is_active ? '' : 'off' }}">{{ $look->is_active ? 'En ligne' : 'Masqué' }}</span></h2>
                        <small>{{ $look->scene ?: 'Sans description' }} · {{ $look->products->count() }} pièce{{ $look->products->count() > 1 ? 's' : '' }} · {{ $n($look->products->sum('price')) }} {{ $shop->currency ?? 'GNF' }}</small>
                        <div class="lk-pieces">@foreach($look->products as $p)<span>@if($t = $img($p->image))<img src="{{ $t }}" alt="">@else<i></i>@endif{{ \Illuminate\Support\Str::limit($p->name, 22) }}</span>@endforeach</div>
                    </div>
                    <div class="lk-acts">
                        <a class="lk-btn is-sm" href="{{ route('boutique.looks.edit', $look) }}">Modifier</a>
                        <div class="lk-mv">
                            <form method="POST" action="{{ route('boutique.looks.move', $look) }}">@csrf<input type="hidden" name="dir" value="up"><button class="lk-btn is-sm" @disabled($k === 0) aria-label="Monter « {{ $look->title }} »">↑</button></form>
                            <form method="POST" action="{{ route('boutique.looks.move', $look) }}">@csrf<input type="hidden" name="dir" value="down"><button class="lk-btn is-sm" @disabled($k === $looks->count() - 1) aria-label="Descendre « {{ $look->title }} »">↓</button></form>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            @endif

            {{-- Détourage des photos (fond retiré) pour l'effet « produit qui flotte » du thème Lookbook --}}
            <section class="cut" aria-labelledby="cutH">
                <div class="cut-hd">
                    <div><h2 id="cutH">Photos sans fond</h2><p>Le thème Lookbook fait flotter tes produits : retire le fond de leurs photos. La photo d’origine est gardée pour les autres thèmes.</p></div>
                    @if($cut['available'] && !$cut['busy'] && collect($cut['items'])->contains(fn ($i) => !$i['cutout']))
                    <form method="POST" action="{{ route('boutique.looks.cutout') }}">@csrf<input type="hidden" name="target" value="all"><input type="hidden" name="action" value="run"><button class="lk-btn is-main">Retirer le fond de {{ collect($cut['items'])->filter(fn ($i) => !$i['cutout'])->count() }} photo(s)</button></form>
                    @endif
                </div>
                @if(!$cut['available'])
                    <p class="lk-note" style="margin:0">Le détourage automatique n’est pas activé sur ce serveur. Tu peux envoyer des photos déjà sans fond (PNG transparent) en attendant.</p>
                @elseif($cut['busy'])
                    <p class="cut-busy" role="status">Détourage en cours… quelques secondes par photo. <a href="{{ route('boutique.looks.index') }}">Actualiser</a></p>
                @endif
                @if(empty($cut['items']))
                    <p style="margin:0;color:var(--text-2);font-size:13.5px">Aucune photo pour l’instant : ajoute des produits avec une photo.</p>
                @else
                <div class="cut-grid">
                    @foreach($cut['items'] as $it)
                    <div class="cut-it">
                        <div class="cut-ph"><img src="{{ $img($it['cutout'] ?: $it['image']) }}" alt="" loading="lazy"><span class="{{ $it['cutout'] ? 'ok' : '' }}">{{ $it['cutout'] ? 'Sans fond' : 'Originale' }}</span></div>
                        <b>{{ \Illuminate\Support\Str::limit($it['label'], 40) }}</b>
                        @if($it['cutout'])
                            <form method="POST" action="{{ route('boutique.looks.cutout') }}">@csrf<input type="hidden" name="target" value="{{ $it['key'] }}"><input type="hidden" name="action" value="undo"><button class="lk-btn is-sm" style="width:100%">Garder l’originale</button></form>
                        @elseif($cut['available'] && !$cut['busy'])
                            <form method="POST" action="{{ route('boutique.looks.cutout') }}">@csrf<input type="hidden" name="target" value="{{ $it['key'] }}"><input type="hidden" name="action" value="run"><button class="lk-btn is-sm" style="width:100%">Retirer le fond</button></form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </section>
        </div>
    </main>
</div>
@if($cut['busy'])<meta http-equiv="refresh" content="8">@endif
@endsection
