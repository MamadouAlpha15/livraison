{{--
    Page produit (lien partagé par le vendeur, accessible sans compte) : galerie, prix, offres groupées,
    formulaire « Commande maintenant » payé à la livraison, badges, points forts, étapes, avis réels, FAQ.
    Tout le contenu est personnalisable par le vendeur (App\Support\ProductPage).
    Données : Client\ProductController@show
--}}
@extends('layouts.app')

@section('title', $product->name . ' · ' . $shop->name)

@php
    $bodyClass = 'is-dashboard';
    use App\Support\IconLibrary as Ico;
    use App\Support\Money;
    use App\Support\PageModels;
    use App\Services\ImageOptimizer;

    $photo = fn ($p) => $p ? (ImageOptimizer::url($p) ?? asset('storage/' . $p)) : null;
    $photos = array_values(array_filter(array_merge([$photo($product->image)], array_map($photo, $product->gallery_array))));
    $price = (float) $product->current_price;
    $original = (float) ($product->original_price ?? 0);
    $hasPromo = $original > $price;
    $discountPct = $hasPromo ? (int) round(100 - $price * 100 / $original) : 0;
    $unavailable = !$product->is_active || $product->is_available === false || ($product->stock !== null && $product->stock <= 0 && $variants->isEmpty());
    $lowStock = $productPage['urgency'] && $product->stock !== null && $product->stock > 0 && $product->stock <= (int) $productPage['urgency_under'];
    $accent = $shopPage['accent'];
    $onAccent = \App\Support\ProductPage::onAccent($accent);
    $accentNight = \App\Support\ProductPage::accentOnNight($accent);
    $fees = $shopPage['delivery_fees'];
    $user = auth()->user();
    $intro = $productPage['intro'] ?: $product->description;
    $points = array_values(array_filter($productPage['points']));
    $badges = array_intersect_key(\App\Support\ProductPage::BADGES, array_flip($shopPage['badges']));
    if (!$shop->is_approved) unset($badges['verified']);
    $initials = mb_strtoupper(mb_substr($shop->name, 0, 1) . mb_substr(\Illuminate\Support\Str::after($shop->name, ' ') ?: $shop->name, 0, 1));
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* Page produit aux couleurs de Shopio : fond clair, encre #16162A, carte de commande bleu nuit,
   couleur du bouton choisie par le vendeur (bleu Shopio par défaut) */
:root { color-scheme: light; }
.pp { --ink: #16162A; --ink2: #45455E; --muted: #62627C; --line: #E3E8F2; --soft: #F6F9FF; --tint: #EAF2FF; --card: #FFFFFF; --bg: #FFFFFF;
      --night: #0B1E3D; --night2: #132B52; --night-line: #1C3560; --night-text: #9FB3D3;
      --accent: {{ $accent }}; --on-accent: {{ $onAccent }}; --accent-night: {{ $accentNight }};
      --ok: #15803D; --warn: #B45309; --font: 'Plus Jakarta Sans', system-ui, sans-serif;
      background: var(--bg); color: var(--ink); font-family: var(--font); min-height: 100vh; -webkit-font-smoothing: antialiased; }
body { background: #FFFFFF !important; }
.pp-cta:hover:not(:disabled) { filter: brightness(1.06); box-shadow: 0 10px 24px -10px var(--accent); }
.pp *, .pp *::before, .pp *::after { box-sizing: border-box; }
.pp-num { font-variant-numeric: tabular-nums; }
.pp-banner { background: var(--night); color: #fff; text-align: center; font-size: 13px; font-weight: 700; padding: 9px max(16px, env(safe-area-inset-right, 0px)) 9px max(16px, env(safe-area-inset-left, 0px)); }
.pp-wrap { max-width: 1080px; margin: 0 auto; padding: 0 max(16px, env(safe-area-inset-right, 0px)) 120px max(16px, env(safe-area-inset-left, 0px)); }
.pp-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 0; }
.pp-round { width: 44px; height: 44px; border-radius: 999px; border: 1px solid var(--line); background: #fff; color: var(--ink); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; cursor: pointer; flex: none; }
.pp-round:focus-visible, .pp-cta:focus-visible, .pp-ghost:focus-visible, .pp-offer:focus-within, .pp-chip:focus-visible { outline: 3px solid var(--ink); outline-offset: 2px; }
.pp-shop { display: inline-flex; align-items: center; gap: 10px; min-width: 0; text-decoration: none; color: var(--ink); font-weight: 800; font-size: 16px; }
.pp-shop-av { width: 36px; height: 36px; border-radius: 10px; background: var(--accent); color: var(--on-accent); display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex: none; overflow: hidden; }
.pp-shop-av img { width: 100%; height: 100%; object-fit: cover; }
.pp-shop span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pp-tick { color: var(--ink); flex: none; display: inline-flex; }

.pp-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 20px; }
.pp-grid > * { min-width: 0; }
.pp-title, .pp-text, .pp-points li, .pp-offer-main, .pp-faq p, .pp-review { overflow-wrap: anywhere; }
.pp-gal-main { position: relative; aspect-ratio: 1 / 1.1; border-radius: 22px; background: var(--soft); overflow: hidden; }
.pp-gal-main img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pp-gal-empty { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--muted); }
.pp-badge-off { position: absolute; top: 14px; left: 14px; background: #F59E0B; color: #16162A; font-weight: 800; font-size: 13px; padding: 5px 10px; border-radius: 10px; }
.pp-count { position: absolute; right: 14px; bottom: 14px; background: var(--night); color: #fff; font-weight: 700; font-size: 12px; padding: 5px 10px; border-radius: 999px; }
.pp-thumbs { display: flex; gap: 10px; margin-top: 10px; overflow-x: auto; scrollbar-width: none; }
.pp-thumbs::-webkit-scrollbar { display: none; }
.pp-thumb { flex: 0 0 calc(25% - 8px); min-width: 64px; aspect-ratio: 1 / .85; border-radius: 14px; border: 2px solid transparent; background: var(--soft); padding: 0; overflow: hidden; cursor: pointer; }
.pp-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.pp-thumb[aria-current="true"] { border-color: var(--accent); }

.pp-title { margin: 4px 0 0; font-size: 26px; line-height: 1.2; font-weight: 800; letter-spacing: -.02em; text-wrap: balance; }
.pp-price { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
.pp-price strong { font-size: 28px; font-weight: 800; letter-spacing: -.01em; }
.pp-price s { color: var(--muted); font-size: 15px; font-weight: 600; }
.pp-save { display: inline-block; margin-top: 8px; background: var(--accent); color: var(--on-accent); font-weight: 800; font-size: 13px; padding: 4px 10px; border-radius: 999px; }
.pp-urgent { display: flex; align-items: center; gap: 6px; margin-top: 10px; color: var(--warn); font-size: 13px; font-weight: 700; }
.pp-urgent i { width: 7px; height: 7px; border-radius: 999px; background: currentColor; }
.pp-h2 { margin: 0 0 12px; font-size: 19px; font-weight: 800; letter-spacing: -.01em; }
.pp-sec { margin-top: 28px; }

.pp-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.pp-chip { min-height: 40px; padding: 0 14px; border-radius: 999px; border: 1.5px solid var(--line); background: #fff; color: var(--ink); font: 600 14px var(--font); cursor: pointer; }
.pp-chip[aria-pressed="true"] { border-color: var(--accent); background: var(--accent); color: var(--on-accent); }
.pp-chip:disabled { opacity: .4; text-decoration: line-through; cursor: not-allowed; }

.pp-offers { display: grid; gap: 10px; }
.pp-offer { position: relative; display: flex; align-items: center; gap: 14px; padding: 16px; border-radius: 18px; border: 1.5px solid var(--line); background: #fff; cursor: pointer; }
.pp-offer input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
.pp-offer-dot { width: 22px; height: 22px; border-radius: 999px; border: 2px solid #C9CFDB; flex: none; display: flex; align-items: center; justify-content: center; }
.pp-offer:has(input:checked) { border: 2px solid var(--accent); background: var(--soft); }
.pp-offer:has(input:checked) .pp-offer-dot { border-color: var(--accent); background: var(--accent); box-shadow: inset 0 0 0 4px #fff; }
.pp-offer-main { flex: 1; min-width: 0; display: grid; gap: 3px; }
.pp-offer-main strong { font-size: 15px; }
.pp-offer-main span { font-size: 13px; color: var(--muted); }
.pp-offer-main .pp-offer-badge { justify-self: start; background: var(--accent); color: var(--on-accent); font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 999px; }
.pp-offer-price { text-align: right; display: grid; gap: 3px; flex: none; }
.pp-offer-price strong { font-size: 16px; }
.pp-offer-price span { font-size: 12px; font-weight: 700; color: var(--ok); }

.pp-order { margin-top: 24px; background: var(--night); color: #fff; border-radius: 24px; padding: 22px 18px; display: grid; gap: 14px; scroll-margin-top: 16px; }
.pp-order h2 { margin: 0; font-size: 22px; font-weight: 800; }
.pp-order > p { margin: -8px 0 0; font-size: 13px; color: var(--night-text); }
.pp-field { display: grid; gap: 6px; min-width: 0; }
.pp-field label { font-size: 13px; font-weight: 700; }
.pp-input, .pp-select { width: 100%; min-height: 50px; border-radius: 14px; border: 0; background: #fff; color: var(--ink); padding: 0 14px; font: 500 16px var(--font); }
.pp-input:focus, .pp-select:focus { outline: 3px solid var(--accent); outline-offset: 1px; }
.pp-input.is-err, .pp-select.is-err { outline: 2px solid #F87171; }
.pp-phone { display: flex; gap: 8px; }
.pp-phone span { display: inline-flex; align-items: center; padding: 0 14px; border-radius: 14px; background: var(--night2); color: #fff; font-weight: 700; }
.pp-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.pp-err { color: #FCA5A5; font-size: 13px; font-weight: 600; }
.pp-sum { border-top: 1px dashed var(--night-line); padding-top: 12px; display: grid; gap: 8px; font-size: 14px; color: #C9D6EA; }
.pp-sum div { display: flex; justify-content: space-between; gap: 12px; }
.pp-sum .pp-total { color: #fff; font-weight: 800; font-size: 16px; }
.pp-sum .pp-total strong { color: var(--accent-night); font-size: 22px; }
.pp-cta { width: 100%; min-height: 56px; border-radius: 16px; border: 0; background: var(--accent); color: var(--on-accent); font: 800 16px var(--font); display: inline-flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; text-decoration: none; }
.pp-cta:disabled { opacity: .6; cursor: wait; }
.pp-ghost { width: 100%; min-height: 50px; border-radius: 16px; border: 1.5px solid var(--night-line); background: transparent; color: #fff; font: 700 15px var(--font); display: inline-flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; }
.pp-ghost:hover { border-color: #fff; color: #fff; }
.pp-flash { border-radius: 14px; padding: 12px 14px; font-size: 14px; font-weight: 700; }
.pp-flash.err { background: #3B1A24; color: #FECACA; }
.pp-done { display: grid; gap: 12px; text-align: center; justify-items: center; padding: 8px 0; }
.pp-done-ico { width: 56px; height: 56px; border-radius: 999px; background: var(--accent); color: var(--on-accent); display: flex; align-items: center; justify-content: center; }

.pp-trust { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 20px; }
.pp-trust div { background: var(--soft); color: var(--ink); box-shadow: inset 0 0 0 1px var(--line); border-radius: 16px; padding: 14px 8px; text-align: center; display: grid; gap: 6px; justify-items: center; font-size: 12px; font-weight: 700; line-height: 1.3; }
.pp-text { margin: 0; color: var(--ink2); font-size: 15px; line-height: 1.6; white-space: pre-line; }
.pp-points { list-style: none; margin: 14px 0 0; padding: 0; display: grid; gap: 10px; }
.pp-points li { display: flex; gap: 10px; align-items: flex-start; font-size: 15px; font-weight: 500; }
.pp-points li span:first-child { flex: none; width: 22px; height: 22px; border-radius: 999px; background: var(--accent); color: var(--on-accent); display: flex; align-items: center; justify-content: center; margin-top: 1px; }
.pp-steps { list-style: none; margin: 0; padding: 0; display: grid; gap: 14px; counter-reset: st; }
.pp-steps li { counter-increment: st; display: flex; gap: 12px; align-items: center; font-size: 15px; }
.pp-steps li::before { content: counter(st); flex: none; width: 30px; height: 30px; border-radius: 999px; background: var(--night); color: #fff; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; }
.pp-steps li:first-child::before { background: var(--accent); color: var(--on-accent); }
.pp-reviews { display: grid; gap: 10px; }
.pp-review { border: 1px solid var(--line); border-radius: 16px; padding: 14px; display: grid; gap: 6px; }
.pp-stars { color: #F59E0B; letter-spacing: 2px; font-size: 14px; }
.pp-empty { border: 1.5px dashed var(--line); border-radius: 16px; padding: 18px; text-align: center; color: var(--muted); font-size: 14px; line-height: 1.5; }
.pp-faq details { border-bottom: 1px solid var(--line); padding: 14px 0; }
.pp-faq summary { cursor: pointer; font-weight: 700; font-size: 15px; list-style: none; display: flex; gap: 10px; align-items: center; }
.pp-faq summary::-webkit-details-marker { display: none; }
.pp-faq summary::before { content: '▸'; transition: transform .15s; }
.pp-faq details[open] summary::before { transform: rotate(90deg); }
.pp-faq p { margin: 10px 0 0 22px; color: var(--ink2); font-size: 14.5px; line-height: 1.55; white-space: pre-line; }
.pp-foot { text-align: center; font-size: 12px; color: var(--muted); margin-top: 32px; }
.pp-foot strong { color: var(--ink); }

.pp-sticky { position: fixed; left: 0; right: 0; bottom: 0; z-index: 50; padding: 10px max(12px, env(safe-area-inset-right, 0px)) calc(10px + env(safe-area-inset-bottom, 0px)) max(12px, env(safe-area-inset-left, 0px)); }
.pp-sticky-in { max-width: 560px; margin: 0 auto; background: var(--night); color: #fff; border-radius: 20px; padding: 10px 10px 10px 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 12px 30px -10px rgba(11,30,61,.55); }
.pp-sticky small { display: block; font-size: 11px; color: var(--night-text); }
.pp-sticky strong { font-size: 17px; }
.pp-sticky .pp-cta { width: auto; min-height: 48px; padding: 0 22px; font-size: 15px; }
.pp-sticky[hidden] { display: none; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

/* Sections ajoutées : garantie, WhatsApp, compte à rebours */
.pp-guarantee { display: flex; gap: 14px; align-items: flex-start; padding: 16px; border-radius: 18px; background: var(--tint); }
.pp-guarantee-ico { flex: none; width: 44px; height: 44px; border-radius: 14px; background: var(--accent); color: var(--on-accent); display: flex; align-items: center; justify-content: center; }
.pp-guarantee strong { font-size: 15px; }
.pp-guarantee p { margin: 4px 0 0; color: var(--ink2); font-size: 14.5px; line-height: 1.5; overflow-wrap: anywhere; }
.pp-wa { margin-top: 20px; width: 100%; min-height: 50px; border-radius: 16px; border: 1.5px solid var(--line); background: #fff; color: var(--ink); font: 700 15px var(--font); display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; }
.pp-wa:hover { border-color: var(--accent); color: var(--ink); }
.pp-wa svg { color: #16A34A; }
.pp-countdown { margin-top: 16px; display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 16px; background: var(--night); color: #fff; font-size: 14px; font-weight: 600; }
.pp-countdown strong { color: var(--accent-night); font-size: 17px; letter-spacing: .02em; }
.pp-promo-hero { margin-top: 14px; padding: 16px 18px; border-radius: 18px; background: var(--accent); color: var(--on-accent); display: grid; gap: 2px; }
.pp-promo-hero span { font-size: 13px; font-weight: 700; opacity: .9; }
.pp-promo-hero strong { font-size: 30px; font-weight: 800; letter-spacing: -.01em; }
.pp-promo-hero em { font-style: normal; font-size: 13px; font-weight: 700; opacity: .9; }

/* ── Modèle Express : tout tient dans le premier écran ── */
.pp--express .pp-gal-main { aspect-ratio: 4 / 3; }
.pp--express .pp-thumbs { display: none; }
.pp--express .pp-title { font-size: 22px; }
.pp--express .pp-offers { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
.pp--express .pp-offer { padding: 12px; gap: 10px; flex-wrap: wrap; }
.pp--express .pp-offer-price { text-align: left; width: 100%; padding-left: 32px; }
.pp--express .pp-order { margin-top: 16px; }

/* ── Modèle Confiance : garanties et avis en premier, ton sobre ── */
.pp--confiance .pp-trust { margin-top: 22px; grid-template-columns: 1fr; }
.pp--confiance .pp-trust div { grid-auto-flow: column; justify-content: start; text-align: left; padding: 14px 16px; font-size: 14px; background: #fff; }
.pp--confiance .pp-guarantee { margin-top: 14px; }
.pp--confiance .pp-review { background: var(--soft); border-color: transparent; }

/* ── Modèle Promo : prix et économie en très grand ── */
.pp--promo .pp-price strong { font-size: 34px; color: var(--ink); }
.pp--promo .pp-price s { font-size: 18px; }
.pp--promo .pp-save { display: none; }
.pp--promo .pp-badge-off { font-size: 18px; padding: 8px 14px; border-radius: 14px; }
.pp--promo .pp-offer-price span { font-size: 13px; }

/* ── Modèle Histoire : le texte et les bienfaits racontés en grand ── */
.pp--histoire .pp-why .pp-h2 { font-size: 24px; }
.pp--histoire .pp-why .pp-text { font-size: 17px; line-height: 1.7; color: var(--ink); }
.pp--histoire .pp-points { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
.pp--histoire .pp-points li { background: var(--soft); border-radius: 16px; padding: 14px; }

@media (min-width: 900px) {
    .pp-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 40px; align-items: start; }
    .pp-gallery { position: sticky; top: 16px; }
    .pp-title { font-size: 30px; }
    .pp-sticky { display: none; }
    .pp-wrap { padding-bottom: 60px; }
}
@media (max-width: 380px) { .pp-row2 { grid-template-columns: 1fr; } .pp-trust div { font-size: 11px; } }
@media (prefers-reduced-motion: reduce) { .pp-faq summary::before { transition: none; } }
</style>
@endpush

@section('content')
<div class="pp pp--{{ $template }}">
    @if(filled($shopPage['banner']))<div class="pp-banner">{{ $shopPage['banner'] }}</div>@endif

    <div class="pp-wrap">
        <div class="pp-top">
            <button type="button" class="pp-round" onclick="history.length > 1 ? history.back() : (location.href='{{ \App\Support\Storefront::publicUrl($shop) }}')" aria-label="Retour">{!! Ico::svg('arrow-left', '', 20) !!}</button>
            <a href="{{ \App\Support\Storefront::publicUrl($shop) }}" class="pp-shop">
                <span class="pp-shop-av">@if($shop->image)<img src="{{ $photo($shop->image) }}" alt="">@else{{ $initials }}@endif</span>
                <span>{{ $shop->name }}</span>
                @if($shop->is_approved)<span class="pp-tick" title="Boutique vérifiée">{!! Ico::svg('check', '', 16) !!}</span>@endif
            </a>
            <a href="{{ $user && $user->role === 'client' ? route('client.cart.index') : \App\Support\Storefront::publicUrl($shop) }}" class="pp-round" aria-label="{{ $user && $user->role === 'client' ? 'Mon panier' : 'Voir la boutique' }}">{!! Ico::svg('bag', '', 20) !!}</a>
        </div>

        <div class="pp-grid">
            {{-- Galerie --}}
            <div class="pp-gallery">
                <div class="pp-gal-main">
                    @if($photos)
                        <img id="ppMain" src="{{ $photos[0] }}" alt="{{ $product->name }}">
                    @else
                        <div class="pp-gal-empty">{!! Ico::svg('image', '', 48) !!}</div>
                    @endif
                    @if($hasPromo)<span class="pp-badge-off">-{{ $discountPct }} %</span>@endif
                    @if(count($photos) > 1)<span class="pp-count" id="ppCount">1 / {{ count($photos) }}</span>@endif
                </div>
                @if(count($photos) > 1)
                <div class="pp-thumbs" role="list">
                    @foreach($photos as $i => $src)
                    <button type="button" class="pp-thumb" data-src="{{ $src }}" data-i="{{ $i + 1 }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Photo {{ $i + 1 }}"><img src="{{ $src }}" alt="" loading="lazy"></button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Infos et commande --}}
            <div>
                <h1 class="pp-title">{{ $product->name }}</h1>
                <div class="pp-price pp-num">
                    <strong>{{ Money::gnf($price) }}</strong>
                    @if($hasPromo)<s>{{ Money::gnf($original) }}</s>@endif
                </div>
                @if($hasPromo)<span class="pp-save pp-num">-{{ Money::gnf($original - $price) }}</span>@endif
                @if($lowStock)<div class="pp-urgent"><i></i> Plus que {{ $product->stock }} en stock</div>@endif
                @if($template === 'promo' && $hasPromo)
                <div class="pp-promo-hero pp-num">
                    <span>Tu économises</span>
                    <strong>{{ Money::gnf($original - $price) }}</strong>
                    <em>-{{ $discountPct }} % sur le prix habituel</em>
                </div>
                @endif

                {{-- Sections dans l'ordre du modèle choisi (App\Support\ProductPage::TEMPLATES) --}}
                @foreach(\App\Support\ProductPage::TEMPLATES[$template]['sections'] as $section)
                    @include('client.product-page._' . $section)
                @endforeach

                <p class="pp-foot">Boutique propulsée par <strong>Shopio</strong></p>
            </div>
        </div>
    </div>

    @unless($unavailable)
    <div class="pp-sticky" id="ppSticky">
        <div class="pp-sticky-in">
            <div><small>Total</small><strong class="pp-num" id="ppStickyTotal">{{ Money::gnf($selected['price']) }}</strong></div>
            <button type="button" class="pp-cta" id="ppStickyBtn">Commander</button>
        </div>
    </div>
    @endunless
</div>
@endsection

@push('scripts')
<script>
(() => {
    const offers = @json(collect($offers)->keyBy('key'));
    const fees = @json((object) $fees);
    const nf = n => new Intl.NumberFormat('fr-FR').format(Math.round(n)).replace(/ |\s/g, ' ') + ' GNF';
    const $ = id => document.getElementById(id);

    // Galerie
    const main = $('ppMain');
    document.querySelectorAll('.pp-thumb').forEach(t => t.addEventListener('click', () => {
        if (main) main.src = t.dataset.src;
        document.querySelectorAll('.pp-thumb').forEach(x => x.setAttribute('aria-current', x === t ? 'true' : 'false'));
        if ($('ppCount')) $('ppCount').textContent = t.dataset.i + ' / ' + document.querySelectorAll('.pp-thumb').length;
    }));

    // Options (taille, couleur…)
    document.querySelectorAll('#ppVariants .pp-chip').forEach(c => c.addEventListener('click', () => {
        document.querySelectorAll('#ppVariants .pp-chip').forEach(x => x.setAttribute('aria-pressed', x === c ? 'true' : 'false'));
        if ($('ppVariant')) $('ppVariant').value = c.dataset.variant;
        if (c.dataset.image && main) main.src = c.dataset.image;
    }));

    const form = $('ppForm');
    if (!form) return;

    // Totaux : offre choisie + livraison de la commune (si la boutique l'a indiquée)
    function refresh() {
        const o = offers[$('ppOffer').value];
        const commune = $('ppCommune').value;
        const fee = commune && fees[commune] !== undefined && fees[commune] !== null && fees[commune] !== '' ? Number(fees[commune]) : null;
        $('ppSumLabel').textContent = o.label;
        $('ppSumPrice').textContent = nf(o.price);
        $('ppFeeLabel').textContent = commune ? 'Livraison (' + commune + ')' : 'Livraison';
        $('ppFee').textContent = !commune ? 'Choisis ta commune' : (fee === null ? 'Confirmée par la boutique' : nf(fee));
        const total = o.price + (fee || 0);
        $('ppTotal').textContent = nf(total);
        if ($('ppStickyTotal')) $('ppStickyTotal').textContent = nf(total);
        return total;
    }
    document.querySelectorAll('[name=offer_pick]').forEach(r => r.addEventListener('change', () => { $('ppOffer').value = r.value; refresh(); }));
    $('ppCommune').addEventListener('change', refresh);

    // Barre du bas : aller au formulaire, ou commander s'il est rempli
    const sticky = $('ppSticky');
    if (sticky) {
        $('ppStickyBtn').addEventListener('click', () => {
            if ($('ppPhone').value.trim() && $('ppCommune').value) form.requestSubmit();
            else { $('ppOrder').scrollIntoView({ behavior: 'smooth' }); setTimeout(() => (($('ppName').value ? $('ppPhone') : $('ppName'))).focus(), 400); }
        });
        new IntersectionObserver(([e]) => { sticky.hidden = e.isIntersecting; }).observe($('ppOrder'));
    }

    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        form.querySelectorAll('[data-err]').forEach(e => e.textContent = '');
        form.querySelectorAll('.is-err').forEach(e => e.classList.remove('is-err'));
        $('ppError').hidden = true;
        const btn = $('ppSubmit'); btn.disabled = true;
        const total = refresh();
        try {
            const res = await fetch(form.dataset.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
            });
            const data = await res.json().catch(() => ({}));
            if (res.status === 201) {
                form.hidden = true; if (sticky) sticky.remove();
                $('ppDoneTotal').textContent = nf(data.total ?? total);
                $('ppTrack').href = data.tracking;
                $('ppDone').hidden = false;
                $('ppOrder').scrollIntoView({ behavior: 'smooth' });
                return;
            }
            // Même produit commandé avec ce numéro il y a peu : le client confirme qu'il s'agit d'une nouvelle commande
            if (res.status === 409 && data.duplicate) {
                if (window.confirm(data.message + '\n\nC\'est bien une nouvelle commande ? Touche OK pour l\'envoyer quand même.')) {
                    const again = form.querySelector('input[name="confirm_duplicate"]') || Object.assign(document.createElement('input'), { type: 'hidden', name: 'confirm_duplicate' });
                    again.value = '1';
                    form.appendChild(again);
                    btn.disabled = false;
                    form.requestSubmit();
                    return;
                }
                $('ppError').textContent = data.message;
                $('ppError').hidden = false;
                return;
            }
            if (data.errors) {
                Object.entries(data.errors).forEach(([k, v]) => {
                    const slot = form.querySelector(`[data-err="${k}"]`);
                    const input = form.querySelector(`[name="${k}"]`);
                    if (slot) slot.textContent = v[0]; else { $('ppError').textContent = v[0]; $('ppError').hidden = false; }
                    if (input) input.classList.add('is-err');
                });
            } else {
                $('ppError').textContent = data.message || 'La commande n\'a pas pu être envoyée. Réessaie.';
                $('ppError').hidden = false;
            }
        } catch (e) {
            $('ppError').textContent = 'Connexion perdue : vérifie ta connexion et réessaie.';
            $('ppError').hidden = false;
        } finally { btn.disabled = false; }
    });

    refresh();
})();

// Compte à rebours de la vente flash (modèle Promo) : fin réelle de la promotion
document.querySelectorAll('.pp-countdown').forEach(box => {
    const end = Number(box.dataset.ends) * 1000, out = box.querySelector('[data-timer]');
    const tick = () => {
        const s = Math.max(0, Math.floor((end - Date.now()) / 1000));
        if (s === 0) { box.remove(); return; }
        const d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60), sec = s % 60;
        out.textContent = (d ? d + ' j ' : '') + [h, m, sec].map(n => String(n).padStart(2, '0')).join(':');
    };
    tick(); setInterval(tick, 1000);
});

// Alerte de retour en stock (client connecté)
document.getElementById('ppStock')?.addEventListener('click', async (e) => {
    const b = e.currentTarget;
    const res = await fetch(b.dataset.url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
    const d = await res.json().catch(() => ({}));
    const on = d.active ?? b.getAttribute('aria-pressed') !== 'true';
    b.setAttribute('aria-pressed', on ? 'true' : 'false');
    b.querySelector('span').textContent = on ? 'Alerte activée : tu seras prévenu' : 'Me prévenir du retour en stock';
});
</script>
@endpush
