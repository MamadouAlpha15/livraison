{{--
    <head> commun aux modèles de page boutique : titre, aperçu de partage (WhatsApp, Facebook),
    jeton CSRF (commande), styles du parcours d'achat partagé (fiche rapide, panier, commande).
    Chaque modèle définit ses couleurs et polices avec les variables --m-* (voir plus bas).
    Variables : $shop, $pm, $pageTitle, $shareImage, $previewLink, $activeCategory
--}}
@php
    $mDesc = $activeCategory
        ? $activeCategory['count'] . ' produit' . ($activeCategory['count'] > 1 ? 's' : '') . ' « ' . $activeCategory['name'] . ' » chez ' . $shop->name . '. Commandez en ligne, paiement à la livraison.'
        : ($pm['shop']['slogan'] ?: $shop->name . ' — commandez en ligne sur Shopio, paiement à la livraison.');
    $mImg = $shareImage ?: asset('images/shopio-logo-192.png');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ \Illuminate\Support\Str::limit($mDesc, 160) }}">
@if($previewLink)<meta name="robots" content="noindex, nofollow">@endif
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit($mDesc, 200) }}">
<meta property="og:image" content="{{ $mImg }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="Shopio">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0B1E3D">
<link rel="icon" href="{{ asset('images/shopio-logo-192.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
@if(\App\Support\MetaPixel::activeFor($shop, false))@include('partials.meta-pixel', ['pixelId' => $shop->meta_pixel_id])@endif
<style>
/* ── Parcours d'achat commun : fiche rapide, panier (tiroir), commande, confirmation ──
   Variables posées par chaque modèle : --m-bg --m-surface --m-ink --m-ink2 --m-line --m-accent --m-accent-ink
   --m-radius --m-btn-radius --m-title --m-body --m-shadow --m-soft */
*, *::before, *::after { box-sizing: border-box; }
[hidden] { display: none !important; }
html { -webkit-text-size-adjust: 100%; }
body { margin: 0; background: var(--m-bg); color: var(--m-ink); font-family: var(--m-body); -webkit-font-smoothing: antialiased; }
img { max-width: 100%; }
button { font-family: inherit; }
:focus-visible { outline: 3px solid #1F7CF6; outline-offset: 2px; }
.sm-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.sm-scrim { position: fixed; inset: 0; z-index: 900; background: rgba(4,10,24,.58); backdrop-filter: blur(3px); animation: smFade 260ms cubic-bezier(.22,1,.36,1); }
.sm-scrim[hidden], .sm-quick[hidden], .sm-cart[hidden], .sm-toast[hidden] { display: none; }
@keyframes smFade { from { opacity: 0; } }
.sm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 22px; border: 0; border-radius: var(--m-btn-radius, 999px); font: 800 15px var(--m-body); cursor: pointer; text-decoration: none; transition: transform 180ms cubic-bezier(.22,1,.36,1), filter 180ms, box-shadow 180ms; }
.sm-btn:active:not(:disabled) { transform: scale(.97); }
.sm-btn:disabled { opacity: .5; cursor: not-allowed; }
.sm-btn-main { background: var(--m-accent); color: var(--m-accent-ink); }
.sm-btn-main:hover:not(:disabled) { filter: brightness(1.08); }
.sm-btn-ghost { background: transparent; color: var(--m-ink); box-shadow: inset 0 0 0 1.5px var(--m-line); }
.sm-btn-wa { background: #15803D; color: #fff; }
.sm-x { position: absolute; top: 12px; right: 12px; z-index: 2; width: 44px; height: 44px; border: 0; border-radius: 999px; background: var(--m-soft); color: var(--m-ink); font-size: 22px; line-height: 1; cursor: pointer; }
.sm-fav { width: 48px; height: 48px; flex-shrink: 0; border: 0; border-radius: var(--m-btn-radius, 999px); background: var(--m-soft); color: var(--m-ink2); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: transform 260ms cubic-bezier(.34,1.56,.64,1), color 180ms; }
.sm-fav[aria-pressed="true"] { color: #E11D48; }
.sm-fav[aria-pressed="true"] svg { fill: currentColor; }
.sm-fav.is-pop { transform: scale(1.25); }
/* Fiche rapide : entrée en perspective */
.sm-quick { position: fixed; z-index: 950; left: 50%; top: 50%; width: min(960px, calc(100% - 24px)); max-height: calc(100% - 24px); overflow-y: auto; transform: translate(-50%, -50%); display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); background: var(--m-surface); color: var(--m-ink); border-radius: var(--m-radius); box-shadow: 0 40px 90px -30px rgba(0,0,0,.6); animation: smQuickIn 480ms cubic-bezier(.22,1,.36,1); }
@keyframes smQuickIn { from { opacity: 0; transform: translate(-50%, -46%) perspective(1200px) rotateX(-14deg) scale(.96); } }
.sm-q-media { position: relative; min-height: 320px; background: var(--m-soft); display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: var(--m-radius) 0 0 var(--m-radius); }
.sm-q-media img { width: 100%; height: 100%; object-fit: cover; display: block; position: absolute; inset: 0; }
.sm-q-media span { font: 800 40px var(--m-title); color: var(--m-ink2); opacity: .5; padding: 20px; text-align: center; }
.sm-q-body { padding: 26px 24px 22px; display: flex; flex-direction: column; gap: 10px; min-width: 0; }
.sm-q-cat { font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--m-ink2); }
.sm-q-body h2 { margin: 0; padding-right: 40px; font: 700 clamp(22px, 3vw, 30px)/1.15 var(--m-title); letter-spacing: -.01em; overflow-wrap: anywhere; }
.sm-q-price { font: 800 24px var(--m-body); font-variant-numeric: tabular-nums; color: var(--m-accent-text, var(--m-ink)); }
.sm-q-price s { margin-left: 8px; font-size: 15px; font-weight: 600; color: var(--m-ink2); }
.sm-badges { display: flex; gap: 6px; flex-wrap: wrap; }
.sm-badge { display: inline-flex; align-items: center; gap: 5px; height: 28px; padding: 0 11px; border-radius: 999px; background: var(--m-soft); color: var(--m-ink); font-size: 12.5px; font-weight: 700; }
.sm-badge.is-ok { background: #E7F6EC; color: #15803D; }
.sm-q-desc { margin: 0; color: var(--m-ink2); font-size: 14.5px; line-height: 1.55; white-space: pre-line; }
.sm-q-actions { display: flex; gap: 8px; }
.sm-q-actions .sm-btn { flex: 1; }
.sm-q-links { display: flex; flex-direction: column; gap: 4px; }
.sm-q-links a { color: var(--m-ink); font-weight: 700; font-size: 14px; text-underline-offset: 3px; }
.sm-note { margin: 0; font-size: 13px; color: var(--m-ink2); }
.sm-q-sugg h3 { margin: 6px 0 8px; font: 700 15px var(--m-title); }
.sm-sugg { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.sm-sugg button { border: 0; padding: 0; background: none; color: var(--m-ink); text-align: left; cursor: pointer; font: 600 12.5px var(--m-body); }
.sm-sugg i { display: block; aspect-ratio: 1; border-radius: calc(var(--m-radius) * .6); background: var(--m-soft) center / cover; margin-bottom: 5px; }
.sm-sugg b { display: block; font-variant-numeric: tabular-nums; }
/* Panier : tiroir à droite qui entre en pivotant */
.sm-cart { position: fixed; z-index: 950; top: 0; right: 0; bottom: 0; width: min(430px, 100%); display: flex; flex-direction: column; background: var(--m-surface); color: var(--m-ink); box-shadow: -30px 0 60px -20px rgba(0,0,0,.45); transform-origin: right center; animation: smCartIn 520ms cubic-bezier(.22,1,.36,1); padding-bottom: env(safe-area-inset-bottom, 0px); }
@keyframes smCartIn { from { opacity: 0; transform: perspective(1400px) rotateY(-38deg) translateX(40px); } }
.sm-cart-hd { display: flex; align-items: center; gap: 10px; padding: 18px 70px 14px 20px; border-bottom: 1px solid var(--m-line); }
.sm-cart-hd h2 { margin: 0; font: 700 21px var(--m-title); }
.sm-cart-body { flex: 1; overflow-y: auto; padding: 14px 20px; display: flex; flex-direction: column; gap: 12px; }
.sm-line { display: grid; grid-template-columns: 64px minmax(0, 1fr) auto; gap: 12px; align-items: center; }
.sm-line i { width: 64px; height: 64px; border-radius: calc(var(--m-radius) * .6); background: var(--m-soft) center / cover; }
.sm-line b { display: block; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sm-line small { color: var(--m-ink2); font-size: 12.5px; font-variant-numeric: tabular-nums; }
.sm-qty { display: inline-flex; align-items: center; gap: 2px; margin-top: 6px; padding: 3px; border-radius: 999px; background: var(--m-soft); }
.sm-qty button { width: 32px; height: 32px; border: 0; border-radius: 999px; background: var(--m-surface); color: var(--m-ink); font-size: 17px; font-weight: 800; cursor: pointer; }
.sm-qty button:disabled { opacity: .35; cursor: default; }
.sm-qty span { min-width: 26px; text-align: center; font-weight: 800; font-variant-numeric: tabular-nums; }
.sm-rm { border: 0; background: none; color: var(--m-ink2); font: 700 12.5px var(--m-body); text-decoration: underline; cursor: pointer; padding: 6px 0; }
.sm-line-total { font-weight: 800; font-variant-numeric: tabular-nums; text-align: right; }
.sm-cart-ft { padding: 14px 20px 18px; border-top: 1px solid var(--m-line); display: flex; flex-direction: column; gap: 10px; }
.sm-total { display: flex; justify-content: space-between; align-items: baseline; font-size: 15px; }
.sm-total b { font-size: 22px; font-variant-numeric: tabular-nums; }
.sm-empty { margin: auto 0; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; color: var(--m-ink2); padding: 30px 10px; }
.sm-empty svg { color: var(--m-line); }
/* Commande */
.sm-form { display: flex; flex-direction: column; gap: 12px; }
.sm-field { display: flex; flex-direction: column; gap: 5px; }
.sm-field label { font-size: 13px; font-weight: 800; }
.sm-field label small { font-weight: 600; color: var(--m-ink2); }
.sm-field input, .sm-field select { width: 100%; min-height: 48px; padding: 0 14px; border: 0; border-radius: calc(var(--m-radius) * .5 + 4px); background: var(--m-soft); color: var(--m-ink); font: 500 15.5px var(--m-body); box-shadow: inset 0 0 0 1.5px var(--m-line); outline: 0; }
.sm-field input:focus, .sm-field select:focus { box-shadow: inset 0 0 0 2px #1F7CF6; }
.sm-field [aria-invalid="true"] { box-shadow: inset 0 0 0 2px #DC2626; }
.sm-err { margin: 0; color: #DC2626; font-size: 13px; font-weight: 700; }
.sm-cod { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: calc(var(--m-radius) * .6 + 4px); background: #E7F6EC; color: #14532D; font-size: 14px; }
.sm-cod svg { flex-shrink: 0; margin-top: 1px; }
.sm-general { padding: 10px 12px; border-radius: 12px; background: #FDECEC; color: #B91C1C; font-weight: 700; font-size: 14px; }
.sm-general:empty { display: none; }
/* Confirmation : coche verte animée */
.sm-done { margin: auto 0; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 20px 6px; }
.sm-check { width: 92px; height: 92px; }
.sm-check circle { fill: none; stroke: #15803D; stroke-width: 5; stroke-dasharray: 280; stroke-dashoffset: 280; animation: smDraw 700ms cubic-bezier(.22,1,.36,1) forwards; }
.sm-check path { fill: none; stroke: #15803D; stroke-width: 6; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 60; stroke-dashoffset: 60; animation: smDraw 420ms 520ms cubic-bezier(.34,1.56,.64,1) forwards; }
@keyframes smDraw { to { stroke-dashoffset: 0; } }
.sm-done h3 { margin: 4px 0 0; font: 700 24px var(--m-title); }
.sm-done p { margin: 0; color: var(--m-ink2); }
.sm-done .sm-btn { width: 100%; }
.sm-toast { position: fixed; z-index: 990; left: 50%; bottom: max(20px, env(safe-area-inset-bottom, 0px)); transform: translateX(-50%); padding: 12px 18px; border-radius: 999px; background: #0B1E3D; color: #fff; font: 700 14px var(--m-body); box-shadow: 0 16px 40px rgba(0,0,0,.35); animation: smToast 320ms cubic-bezier(.34,1.56,.64,1); max-width: calc(100% - 32px); text-align: center; }
@keyframes smToast { from { opacity: 0; transform: translate(-50%, 14px); } }
/* L'icône du panier rebondit à chaque ajout */
.sm-bounce { animation: smBounce 560ms cubic-bezier(.34,1.56,.64,1); }
@keyframes smBounce { 0% { transform: scale(1); } 35% { transform: scale(1.28) rotate(-8deg); } 70% { transform: scale(.94); } 100% { transform: scale(1); } }
[data-sm-count]:empty { display: none; }
/* Bandeaux : vacances, lien d'aperçu */
.sm-banner { display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; padding: 10px max(16px, env(safe-area-inset-left, 0px)); font: 600 14px var(--m-body); text-align: center; }
.sm-banner.is-vac { background: #F59E0B; color: #16162A; }
.sm-banner.is-plink { background: #0B1E3D; color: #fff; }
.sm-banner.is-plink b { color: #FCD34D; }
.sm-fb { max-width: 640px; margin: 40px auto; padding: 22px; border-radius: 20px; background: #fff; color: #16162A; box-shadow: 0 0 0 1px #E3E8F2; display: flex; flex-direction: column; gap: 10px; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.sm-fb h2 { margin: 0; font-size: 19px; } .sm-fb p { margin: 0; color: #45455E; }
.sm-fb input, .sm-fb textarea { width: 100%; border: 0; border-radius: 12px; padding: 12px 14px; background: #F1F5FB; font: 500 15px inherit; font-family: inherit; box-shadow: inset 0 0 0 1.5px #E3E8F2; }
.sm-fb textarea { min-height: 96px; }
.sm-fb button { align-self: flex-start; min-height: 46px; padding: 0 20px; border: 0; border-radius: 999px; background: #1668D8; color: #fff; font: 800 15px inherit; font-family: inherit; cursor: pointer; }
@media (max-width: 760px) {
    .sm-quick { grid-template-columns: minmax(0, 1fr); top: auto; bottom: 0; left: 0; transform: none; width: 100%; max-height: 92vh; border-radius: var(--m-radius) var(--m-radius) 0 0; animation: smSheetIn 460ms cubic-bezier(.22,1,.36,1); }
    @keyframes smSheetIn { from { transform: translateY(40px) perspective(1200px) rotateX(-10deg); opacity: 0; } }
    .sm-q-media { min-height: 0; aspect-ratio: 1.2; border-radius: var(--m-radius) var(--m-radius) 0 0; }
}
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: 1ms !important; animation-iteration-count: 1 !important; transition-duration: 1ms !important; scroll-behavior: auto !important; }
}
</style>
