{{--
    resources/views/vendeur/shop/storefront.blade.php
    Route     : GET /boutique/vitrine → Vendeur\StorefrontController@edit → name('boutique.storefront.edit')
    Rôle      : « Personnaliser ma vitrine » — réglages à gauche, aperçu en direct à droite.
                L'aperçu est la vraie page publique (/shops/{id}/products) chargée dans un cadre
                avec ?vitrine=<réglages non publiés> : seul le propriétaire voit ces réglages.
    Variables : $shop, $config (App\Support\Storefront::normalize), $accent, $products (nb actifs)
--}}
@extends('layouts.app')
@section('title', 'Personnaliser ma vitrine · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    use App\Support\Storefront as SF;
    $publicUrl = route('public.shops.products', $shop);
    $accents = ['#1F7CF6' => 'Bleu Shopio', '#0B1E3D' => 'Bleu nuit', '#F59E0B' => 'Ambre', '#15803D' => 'Vert'];
    $visible = SF::visibleCount($config);
    // Premier chargement de l'aperçu déjà en mode « aperçu » (fenêtres du site masquées, réglages actuels)
    $previewSrc = $publicUrl . '?vitrine=' . rtrim(strtr(base64_encode(json_encode($config)), '+/', '-_'), '=') . '&accent=' . urlencode($accent);
@endphp

@push('styles')
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<style>
/* Éditeur de vitrine : barre bleu nuit, réglages à gauche (360 px), aperçu à droite */
:root {
    --vf-blue: #1F7CF6; --vf-btn: #1668D8; --vf-btn-h: #1260C9; --vf-b50: #EAF2FF; --vf-b100: #D6E6FF;
    --vf-night: #0B1E3D; --vf-night2: #132B52; --vf-sky: #7DB4FF; --vf-amber: #F59E0B;
    --vf-bg: #F6F9FF; --vf-stage: #E8EFFB; --vf-surf: #FFFFFF; --vf-line: #E3E8F2;
    --vf-ink: #16162A; --vf-ink2: #45455E; --vf-ink3: #62627C; --vf-ok: #15803D; --vf-err: #B91C1C;
    --vf-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --vf-out: cubic-bezier(.22,1,.36,1); --vf-io: cubic-bezier(.65,0,.35,1); --vf-pop: cubic-bezier(.34,1.56,.64,1);
}
[data-theme="dark"] {
    --vf-b50: #13284B; --vf-b100: #1B3563; --vf-bg: #0A1426; --vf-stage: #071022; --vf-surf: #10203B; --vf-line: #22355A;
    --vf-ink: #EAF0FA; --vf-ink2: #C3CEE0; --vf-ink3: #9DAAC2; --vf-ok: #4ADE80; --vf-err: #F87171;
}
html, body { margin: 0; background: var(--vf-bg); color: var(--vf-ink); font-family: var(--vf-font); -webkit-font-smoothing: antialiased; font-variant-numeric: tabular-nums; }
.vf, .vf * { box-sizing: border-box; }
.vf { min-height: 100vh; display: flex; flex-direction: column; }
.vf-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.vf :focus-visible { outline: 3px solid var(--vf-blue); outline-offset: 2px; }

/* Barre du haut */
.vf-top { position: sticky; top: 0; z-index: 30; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 12px max(20px, env(safe-area-inset-right, 0px)) 12px max(20px, env(safe-area-inset-left, 0px)); background: var(--vf-night); color: #fff; }
.vf-brand { display: flex; align-items: center; gap: 12px; flex: 1 1 260px; min-width: 0; }
.vf-brand img { width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0; }
.vf-brand h1 { margin: 0; font-size: 18px; font-weight: 800; letter-spacing: -.01em; color: #fff; }
.vf-brand p { margin: 2px 0 0; font-size: 13px; color: #9FB3D3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vf-top-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.vf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 999px; border: 0; font: 700 14px/1 var(--vf-font); text-decoration: none; cursor: pointer; white-space: nowrap; transition: transform 180ms var(--vf-out), background-color 180ms, box-shadow 180ms, color 180ms; }
.vf-btn:active:not(:disabled) { transform: scale(.97); }
.vf-btn-night { background: var(--vf-night2); color: #fff; }
.vf-btn-night:hover { background: #1B3563; color: #fff; }
.vf-btn-ghost { background: transparent; color: #fff; box-shadow: inset 0 0 0 1.5px #2B4A7E; }
.vf-btn-ghost:hover { box-shadow: inset 0 0 0 1.5px var(--vf-sky); color: #fff; }
.vf-btn-ghost.is-armed { background: #7F1D1D; box-shadow: none; }
.vf-btn-amber { background: var(--vf-amber); color: #16162A; }
.vf-btn-amber:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(245,158,11,.35); color: #16162A; }
.vf-dirty { display: none; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #FBBF24; }
.vf-dirty::before { content: ''; width: 8px; height: 8px; border-radius: 999px; background: currentColor; }
.vf.is-dirty .vf-dirty { display: inline-flex; }

.vf-flash { margin: 14px 20px 0; padding: 12px 16px; border-radius: 16px; font-weight: 700; font-size: 14px; }
.vf-flash-ok { background: #E7F6EC; color: #15803D; }
.vf-flash-err { background: #FDECEC; color: #B91C1C; }
[data-theme="dark"] .vf-flash-ok { background: #0F2A1C; color: #4ADE80; }
[data-theme="dark"] .vf-flash-err { background: #2A1214; color: #F87171; }

/* Corps */
.vf-body { flex: 1; display: grid; grid-template-columns: 360px minmax(0, 1fr); min-height: 0; }
.vf-panel { padding: 18px 16px 40px; display: flex; flex-direction: column; gap: 14px; border-right: 1px solid var(--vf-line); background: var(--vf-bg); }
.vf-card { background: var(--vf-surf); border-radius: 22px; box-shadow: 0 0 0 1px var(--vf-line); padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.vf-card-hd { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; }
.vf-card h2 { margin: 0; font-size: 15px; font-weight: 800; letter-spacing: -.01em; }
.vf-count { font-size: 12.5px; font-weight: 700; color: var(--vf-ink3); }
.vf-help { margin: -4px 0 0; font-size: 12.5px; color: var(--vf-ink3); }
.vf-lbl { font-size: 11.5px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--vf-ink3); margin-bottom: 6px; display: block; }

/* Contrôle segmenté (radios) */
.vf-seg { display: flex; padding: 4px; gap: 4px; border-radius: 999px; background: var(--vf-b50); }
.vf-seg label { flex: 1; position: relative; }
.vf-seg input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.vf-seg span { display: flex; align-items: center; justify-content: center; min-height: 38px; padding: 0 10px; border-radius: 999px; font-size: 13px; font-weight: 700; color: var(--vf-ink2); cursor: pointer; transition: background-color 200ms var(--vf-out), color 200ms, box-shadow 200ms; text-align: center; }
.vf-seg input:checked + span { background: var(--vf-surf); color: var(--vf-btn); box-shadow: 0 2px 8px rgba(11,30,61,.12); }
.vf-seg input:focus-visible + span { outline: 3px solid var(--vf-blue); outline-offset: 1px; }
.vf-seg.is-disabled { opacity: .45; pointer-events: none; }

/* Sections */
.vf-sections { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 6px; }
.vf-sec { display: flex; align-items: center; gap: 6px; padding: 6px 6px 6px 12px; border-radius: 14px; background: var(--vf-bg); transition: transform 320ms var(--vf-out), background-color 200ms; }
.vf-sec.is-moved { animation: vfMoved 420ms var(--vf-pop); }
@keyframes vfMoved { 0% { transform: scale(.97); background: var(--vf-b100); } 100% { transform: none; } }
.vf-sec-name { flex: 1; min-width: 0; font-size: 13.5px; font-weight: 700; }
.vf-sec.is-off .vf-sec-name { color: var(--vf-ink3); text-decoration: line-through; text-decoration-thickness: 1px; }
.vf-move { width: 32px; height: 32px; border-radius: 10px; border: 0; background: var(--vf-surf); color: var(--vf-ink2); cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 1px var(--vf-line); }
.vf-move:hover:not(:disabled) { color: var(--vf-btn); box-shadow: inset 0 0 0 1.5px var(--vf-blue); }
.vf-move:disabled { opacity: .35; cursor: default; }
.vf-switch { position: relative; flex-shrink: 0; width: 46px; height: 28px; }
.vf-switch input { position: absolute; inset: 0; opacity: 0; cursor: pointer; margin: 0; z-index: 1; }
.vf-switch span { position: absolute; inset: 0; border-radius: 999px; background: #C3CBDA; transition: background-color 200ms var(--vf-out); }
.vf-switch span::after { content: ''; position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 999px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,.25); transition: transform 260ms var(--vf-pop); }
.vf-switch input:checked + span { background: var(--vf-btn); }
.vf-switch input:checked + span::after { transform: translateX(18px); }
.vf-switch input:focus-visible + span { outline: 3px solid var(--vf-blue); outline-offset: 2px; }

.vf-input, .vf-select { width: 100%; min-height: 44px; border: 0; border-radius: 14px; padding: 0 14px; background: var(--vf-surf); color: var(--vf-ink); font: 500 14.5px var(--vf-font); box-shadow: inset 0 0 0 1.5px var(--vf-line); outline: 0; }
.vf-input:focus, .vf-select:focus { box-shadow: inset 0 0 0 2px var(--vf-blue); }
.vf-chars { font-size: 12px; color: var(--vf-ink3); text-align: right; }

/* Couleurs */
.vf-swatches { display: flex; gap: 10px; }
.vf-swatch { position: relative; }
.vf-swatch input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.vf-swatch span { display: block; width: 44px; height: 44px; border-radius: 14px; background: var(--sw); cursor: pointer; box-shadow: inset -5px -6px 10px rgba(0,0,0,.22), inset 4px 4px 8px rgba(255,255,255,.25); transition: transform 220ms var(--vf-pop); }
.vf-swatch:hover span { transform: perspective(400px) rotateX(12deg) rotateY(-12deg) scale(1.06); }
.vf-swatch input:checked + span { outline: 3px solid var(--vf-ink); outline-offset: 3px; }
.vf-swatch input:focus-visible + span { outline: 3px solid var(--vf-blue); outline-offset: 3px; }

/* Aperçu */
.vf-stage { position: sticky; top: 68px; height: calc(100vh - 68px); padding: 20px; background: var(--vf-stage); display: flex; align-items: flex-start; justify-content: center; overflow: hidden; perspective: 1600px; }
.vf-device { position: relative; transform-style: preserve-3d; transition: transform 700ms var(--vf-io), width 700ms var(--vf-io); }
.vf-device.is-turning { transform: rotateY(-14deg) scale(.96); }
.vf-browser { width: 100%; border-radius: 18px; overflow: hidden; background: var(--vf-surf); box-shadow: 0 0 0 1px var(--vf-line), 0 24px 48px -20px rgba(11,30,61,.35); }
.vf-browser-bar { display: flex; align-items: center; gap: 10px; height: 38px; padding: 0 14px; background: var(--vf-b50); border-bottom: 1px solid var(--vf-line); }
.vf-dots { display: flex; gap: 6px; }
.vf-dots i { width: 10px; height: 10px; border-radius: 999px; background: #C3CBDA; }
.vf-url { flex: 1; min-width: 0; height: 24px; border-radius: 999px; background: var(--vf-surf); display: flex; align-items: center; padding: 0 12px; font-size: 12px; color: var(--vf-ink3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vf-viewport { position: relative; overflow: hidden; background: #fff; }
.vf-frame { display: block; border: 0; transform-origin: 0 0; background: #fff; transition: opacity 220ms var(--vf-out); }
.vf-viewport.is-loading .vf-frame { opacity: .55; }
.vf-loading { position: absolute; top: 10px; right: 12px; display: none; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; background: var(--vf-night); color: #fff; font-size: 12px; font-weight: 700; z-index: 2; }
.vf-viewport.is-loading .vf-loading { display: inline-flex; }
/* Téléphone */
.vf-device.is-phone .vf-browser { border-radius: 38px; padding: 12px; background: #0B1E3D; box-shadow: inset -6px -8px 16px rgba(0,0,0,.35), 0 30px 60px -20px rgba(11,30,61,.5); }
.vf-device.is-phone .vf-browser-bar { display: none; }
.vf-device.is-phone .vf-viewport { border-radius: 28px; }

/* Qualité de la vitrine */
.vf-quality-hd { display: flex; align-items: center; gap: 14px; }
.vf-ring { --p: 0; position: relative; width: 58px; height: 58px; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; color: var(--vf-ink2);
    background: radial-gradient(closest-side, var(--vf-surf) 76%, transparent 78%), conic-gradient(var(--vf-ring, var(--vf-btn)) calc(var(--p) * 1%), var(--vf-b100) 0); transition: --p 600ms var(--vf-io); }
.vf-ring b { font-size: 17px; color: var(--vf-ink); }
.vf-ring.is-full { --vf-ring: var(--vf-ok); }
.vf-checks { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.vf-check { display: flex; align-items: center; gap: 10px; min-height: 36px; font-size: 13.5px; }
.vf-check-ico { width: 22px; height: 22px; border-radius: 999px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 2px var(--vf-line); color: transparent; transition: background-color 250ms var(--vf-out), color 250ms, transform 300ms var(--vf-pop); }
.vf-check.is-done .vf-check-ico { background: var(--vf-ok); color: #fff; box-shadow: none; }
.vf-check-lbl { flex: 1; min-width: 0; color: var(--vf-ink); font-weight: 600; }
.vf-check.is-done .vf-check-lbl { color: var(--vf-ink3); font-weight: 500; }
.vf-check-cta { font-size: 12.5px; font-weight: 800; color: var(--vf-btn); text-decoration: none; padding: 6px 10px; border-radius: 999px; background: var(--vf-b50); white-space: nowrap; }
.vf-check-cta:hover { background: var(--vf-b100); }
.vf-check.is-done .vf-check-cta { display: none; }
[data-theme="dark"] .vf-check-cta { color: var(--vf-sky); }

/* Modèles */
.vf-templates { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.vf-tpl { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; padding: 12px; border: 0; border-radius: 16px; background: var(--vf-bg); color: var(--vf-ink); text-align: left; cursor: pointer; font-family: inherit; box-shadow: inset 0 0 0 1px var(--vf-line); transition: transform 260ms var(--vf-out), box-shadow 200ms; }
.vf-tpl:hover { transform: perspective(600px) rotateX(6deg) rotateY(-6deg) translateY(-2px); box-shadow: inset 0 0 0 1.5px var(--tc), 0 10px 20px -10px rgba(11,30,61,.3); }
.vf-tpl.is-applied { box-shadow: inset 0 0 0 2px var(--tc); }
.vf-tpl-dot { width: 22px; height: 22px; border-radius: 8px; background: var(--tc); box-shadow: inset -3px -4px 6px rgba(0,0,0,.2), inset 2px 2px 4px rgba(255,255,255,.25); }
.vf-tpl-name { font-size: 14px; font-weight: 800; }
.vf-tpl-hint { font-size: 12px; color: var(--vf-ink3); line-height: 1.35; }

/* Produit à la une */
.vf-picks { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; max-height: 280px; overflow-y: auto; scrollbar-width: thin; }
.vf-pick { width: 100%; display: flex; align-items: center; gap: 10px; padding: 6px 8px; border: 0; border-radius: 14px; background: transparent; color: var(--vf-ink); text-align: left; cursor: pointer; font-family: inherit; transition: background-color 160ms var(--vf-out); }
.vf-pick:hover { background: var(--vf-bg); }
.vf-pick.is-on { background: var(--vf-b50); box-shadow: inset 0 0 0 1.5px var(--vf-btn); }
.vf-pick-img { width: 40px; height: 40px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: var(--vf-b50); }
.vf-pick-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.vf-pick-txt { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.vf-pick-name { font-size: 13.5px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vf-pick-price { font-size: 12px; color: var(--vf-ink3); }
.vf-pick-rank { width: 24px; height: 24px; border-radius: 999px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; box-shadow: inset 0 0 0 2px var(--vf-line); }
.vf-pick.is-on .vf-pick-rank { background: var(--vf-btn); color: #fff; box-shadow: none; animation: vfPop 320ms var(--vf-pop); }
@keyframes vfPop { from { transform: scale(.4); } }
.vf-feat-err { color: var(--vf-err); font-weight: 700; margin: 0; }
.vf-num { font-variant-numeric: tabular-nums; }

/* Partage */
.vf-btn-blue { background: var(--vf-btn); color: #fff; }
.vf-btn-blue:hover { background: var(--vf-btn-h); color: #fff; }
.vf-btn-line { background: var(--vf-surf); color: var(--vf-ink); box-shadow: inset 0 0 0 1.5px var(--vf-line); }
.vf-btn-line:hover { box-shadow: inset 0 0 0 1.5px var(--vf-blue); color: var(--vf-btn); }
.vf-btn-wa { background: #15803D; color: #fff; }
.vf-btn-wa:hover { background: #116B33; color: #fff; }
.vf-share-back { position: fixed; inset: 0; z-index: 80; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(11,30,61,.55); animation: vfFade 250ms var(--vf-out); }
.vf-share-back[hidden] { display: none; }
@keyframes vfFade { from { opacity: 0; } }
.vf-share { position: relative; width: 100%; max-width: 520px; max-height: calc(100vh - 32px); overflow-y: auto; background: var(--vf-surf); border-radius: 24px; padding: 24px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 30px 60px -20px rgba(11,30,61,.5); transform-origin: 50% 0; animation: vfDrop 450ms var(--vf-out); }
@keyframes vfDrop { from { opacity: 0; transform: perspective(900px) rotateX(-18deg) translateY(-10px); } }
.vf-share h2 { margin: 0; font-size: 20px; font-weight: 800; padding-right: 40px; }
.vf-share-x { position: absolute; top: 14px; right: 14px; width: 40px; height: 40px; border-radius: 999px; border: 0; background: var(--vf-bg); color: var(--vf-ink); font-size: 22px; cursor: pointer; }
.vf-share-row { display: flex; gap: 8px; }
.vf-share-row .vf-input { flex: 1; min-width: 0; }
.vf-share-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.vf-qr { display: flex; gap: 16px; align-items: center; padding: 14px; border-radius: 18px; background: var(--vf-bg); flex-wrap: wrap; }
.vf-qr-img { width: 132px; height: 132px; flex-shrink: 0; padding: 8px; border-radius: 14px; background: #fff; }
.vf-qr-img svg { width: 100%; height: 100%; display: block; }
.vf-qr > div:last-child { flex: 1 1 200px; min-width: 0; }

@media (max-width: 960px) {
    .vf-body { grid-template-columns: minmax(0, 1fr); }
    .vf-panel { border-right: 0; order: 2; }
    .vf-stage { position: relative; top: 0; height: 640px; order: 1; }
}
@media (max-width: 520px) {
    .vf-top-actions { width: 100%; }
    .vf-top-actions .vf-btn { flex: 1 1 auto; }
    .vf-stage { height: 560px; padding: 12px; }
}
@media (prefers-reduced-motion: reduce) {
    .vf-device, .vf-sec, .vf-switch span::after, .vf-swatch span { transition: none !important; animation: none !important; }
    .vf-device.is-turning { transform: none; }
    .vf-tpl, .vf-share, .vf-share-back, .vf-pick-rank, .vf-ring, .vf-check-ico { animation: none !important; transition: none !important; }
    .vf-tpl:hover { transform: none; }
}
</style>
@endpush

@section('content')
<div class="vf" id="vf">
    {{-- ══ Barre du haut ══ --}}
    <header class="vf-top">
        <div class="vf-brand">
            <img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="" width="40" height="40">
            <div style="min-width:0">
                <h1>Personnaliser ma vitrine</h1>
                <p>{{ $shop->name }} · les changements s'affichent en direct dans l'aperçu</p>
            </div>
        </div>
        <span class="vf-dirty" role="status">Modifications non publiées</span>
        <div class="vf-top-actions">
            <a href="{{ route('boutique.dashboard') }}" class="vf-btn vf-btn-night">Retour au tableau de bord</a>
            <button type="button" class="vf-btn vf-btn-ghost" data-share-open>Partager</button>
            <form method="POST" action="{{ route('boutique.storefront.reset') }}" id="resetForm">
                @csrf
                <button type="submit" class="vf-btn vf-btn-ghost" id="resetBtn">Réinitialiser</button>
            </form>
            <button type="submit" form="vfForm" class="vf-btn vf-btn-amber" id="publishBtn">Publier la vitrine</button>
        </div>
    </header>

    @if(session('success'))<div class="vf-flash vf-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="vf-flash vf-flash-err" role="alert">{{ $errors->first() }}</div>@endif

    <div class="vf-body">
        {{-- ══ Réglages ══ --}}
        <form class="vf-panel" id="vfForm" method="POST" action="{{ route('boutique.storefront.update') }}">
            @csrf
            @method('PUT')

            {{-- ══ Qualité de la vitrine ══ --}}
            @php
                $qDone = collect($checklist)->where('done', true)->count();
                $qTotal = count($checklist);
                $qPct = (int) round($qDone / max(1, $qTotal) * 100);
            @endphp
            <section class="vf-card vf-quality" aria-labelledby="vfQualTitle">
                <div class="vf-quality-hd">
                    <span class="vf-ring" style="--p: {{ $qPct }}" aria-hidden="true"><b class="vf-num" id="vfQualPct">{{ $qPct }}</b>%</span>
                    <div>
                        <h2 id="vfQualTitle">Qualité de la vitrine</h2>
                        <p class="vf-help" style="margin:2px 0 0" id="vfQualTxt">{{ $qDone }} sur {{ $qTotal }} points complétés</p>
                    </div>
                </div>
                <ul class="vf-checks">
                    @foreach($checklist as $item)
                    <li class="vf-check {{ $item['done'] ? 'is-done' : '' }}" data-check="{{ $item['key'] }}" @if(!empty($item['live'])) data-flagged="{{ $item['flagged'] ? '1' : '0' }}" @endif>
                        <span class="vf-check-ico" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                        </span>
                        <span class="vf-check-lbl">{{ $item['label'] }}<span class="vf-sr">{{ $item['done'] ? ' : fait' : ' : à faire' }}</span></span>
                        <a href="{{ $item['url'] }}" class="vf-check-cta">{{ $item['cta'] }}</a>
                    </li>
                    @endforeach
                </ul>
            </section>

            <section class="vf-card" aria-labelledby="vfPrevTitle">
                <h2 id="vfPrevTitle">Aperçu</h2>
                <div class="vf-seg" role="radiogroup" aria-labelledby="vfPrevTitle">
                    <label><input type="radio" name="_device" value="desktop" checked><span>Ordinateur</span></label>
                    <label><input type="radio" name="_device" value="phone"><span>Téléphone</span></label>
                </div>
            </section>

            {{-- ══ Modèles prêts à l'emploi ══ --}}
            <section class="vf-card" aria-labelledby="vfTplTitle">
                <h2 id="vfTplTitle">Partir d'un modèle</h2>
                <p class="vf-help">Applique un ordre de sections, un affichage et une couleur. Rien n'est publié avant « Publier la vitrine ».</p>
                <div class="vf-templates">
                    @foreach(SF::TEMPLATES as $tKey => $t)
                    <button type="button" class="vf-tpl" data-template="{{ $tKey }}" style="--tc: {{ $t['accent'] }}">
                        <span class="vf-tpl-dot" aria-hidden="true"></span>
                        <span class="vf-tpl-name">{{ $t['label'] }}</span>
                        <span class="vf-tpl-hint">{{ $t['hint'] }}</span>
                    </button>
                    @endforeach
                </div>
            </section>

            {{-- ══ Produit à la une ══ --}}
            <section class="vf-card" id="vfFeatured" aria-labelledby="vfFeatTitle">
                <div class="vf-card-hd">
                    <h2 id="vfFeatTitle">Produit à la une</h2>
                    <span class="vf-count"><span id="vfFeatCount">{{ count($config['featured_ids']) }}</span> / {{ SF::FEATURED_MAX }}</span>
                </div>
                <p class="vf-help">Choisissez jusqu'à {{ SF::FEATURED_MAX }} produits, dans l'ordre d'affichage. Sans choix, c'est le dernier produit « mis en avant » qui s'affiche.</p>
                @if($products->isEmpty())
                    <p class="vf-help" style="margin:0">Aucun produit en ligne pour l'instant. <a href="{{ route('products.create') }}">Ajouter un produit</a></p>
                @else
                    <label for="vfFeatSearch" class="vf-sr">Rechercher un produit</label>
                    <input type="search" class="vf-input" id="vfFeatSearch" placeholder="Rechercher un produit…" autocomplete="off">
                    <div id="vfFeatInputs">
                        @foreach($config['featured_ids'] as $fid)<input type="hidden" name="featured_ids[]" value="{{ $fid }}">@endforeach
                    </div>
                    <ul class="vf-picks" id="vfPicks" aria-label="Produits">
                        @foreach($products as $p)
                        @php $rank = array_search($p['id'], $config['featured_ids']); @endphp
                        <li>
                            <button type="button" class="vf-pick {{ $rank !== false ? 'is-on' : '' }}" data-id="{{ $p['id'] }}" data-name="{{ mb_strtolower($p['name']) }}" aria-pressed="{{ $rank !== false ? 'true' : 'false' }}">
                                <span class="vf-pick-img">@if($p['thumb'])<img src="{{ $p['thumb'] }}" alt="" width="40" height="40" loading="lazy">@endif</span>
                                <span class="vf-pick-txt"><span class="vf-pick-name">{{ $p['name'] }}</span><span class="vf-pick-price vf-num">{{ $p['price'] }}@if($p['featured']) · mis en avant @endif</span></span>
                                <span class="vf-pick-rank" aria-hidden="true">{{ $rank !== false ? $rank + 1 : '' }}</span>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                    <p class="vf-help vf-feat-err" id="vfFeatErr" role="status" hidden>Maximum {{ SF::FEATURED_MAX }} produits : retirez-en un pour en choisir un autre.</p>
                @endif
            </section>

            <section class="vf-card" aria-labelledby="vfSecTitle">
                <div class="vf-card-hd">
                    <h2 id="vfSecTitle">Sections de la page</h2>
                    <span class="vf-count"><span id="vfVisible">{{ $visible }}</span> / {{ count(SF::SECTIONS) }} visibles</span>
                </div>
                <p class="vf-help">Montez, descendez ou masquez chaque bloc.</p>
                <ol class="vf-sections" id="vfSections">
                    @foreach($config['sections'] as $i => $sec)
                    <li class="vf-sec {{ $sec['on'] ? '' : 'is-off' }}" data-key="{{ $sec['key'] }}">
                        <input type="hidden" name="sections[{{ $i }}][key]" value="{{ $sec['key'] }}" data-role="key">
                        <input type="hidden" name="sections[{{ $i }}][on]" value="0" data-role="off">
                        <span class="vf-sec-name" id="vfSec-{{ $sec['key'] }}">{{ SF::SECTIONS[$sec['key']] }}</span>
                        <button type="button" class="vf-move" data-move="-1" aria-label="Monter « {{ SF::SECTIONS[$sec['key']] }} »">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 15 6-6 6 6"/></svg>
                        </button>
                        <button type="button" class="vf-move" data-move="1" aria-label="Descendre « {{ SF::SECTIONS[$sec['key']] }} »">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <span class="vf-switch">
                            <input type="checkbox" role="switch" name="sections[{{ $i }}][on]" value="1" data-role="on" {{ $sec['on'] ? 'checked' : '' }} aria-labelledby="vfSec-{{ $sec['key'] }}">
                            <span aria-hidden="true"></span>
                        </span>
                    </li>
                    @endforeach
                </ol>
                <div>
                    <label class="vf-lbl" for="announcement">Texte du bandeau d'annonce</label>
                    <input type="text" class="vf-input" id="announcement" name="announcement" maxlength="{{ SF::ANNOUNCEMENT_MAX }}"
                           value="{{ old('announcement', $config['announcement']) }}" placeholder="Ex. Livraison offerte ce week-end à Conakry">
                    <div class="vf-chars"><span id="vfChars">{{ mb_strlen($config['announcement']) }}</span> / {{ SF::ANNOUNCEMENT_MAX }}</div>
                </div>
            </section>

            <section class="vf-card" aria-labelledby="vfProdTitle">
                <h2 id="vfProdTitle">Produits <span class="vf-count">· {{ $products->count() }} en ligne</span></h2>
                <div>
                    <span class="vf-lbl" id="vfLayoutLbl">Affichage</span>
                    <div class="vf-seg" role="radiogroup" aria-labelledby="vfLayoutLbl">
                        @foreach(SF::LAYOUTS as $v => $l)
                        <label><input type="radio" name="layout" value="{{ $v }}" {{ $config['layout'] === $v ? 'checked' : '' }}><span>{{ $l }}</span></label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <span class="vf-lbl" id="vfColsLbl">Colonnes sur ordinateur</span>
                    <div class="vf-seg {{ $config['layout'] === 'list' ? 'is-disabled' : '' }}" id="vfCols" role="radiogroup" aria-labelledby="vfColsLbl">
                        @foreach(SF::COLUMNS as $c)
                        <label><input type="radio" name="columns" value="{{ $c }}" {{ $config['columns'] === $c ? 'checked' : '' }}><span>{{ $c }} colonnes</span></label>
                        @endforeach
                    </div>
                    <p class="vf-help" style="margin-top:6px">Toujours 2 colonnes sur téléphone.</p>
                </div>
                <div>
                    <label class="vf-lbl" for="sort">Ordre</label>
                    <select class="vf-select" id="sort" name="sort">
                        @foreach(SF::SORTS as $v => $l)<option value="{{ $v }}" {{ $config['sort'] === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <span class="vf-lbl" id="vfCardsLbl">Forme des cartes</span>
                    <div class="vf-seg" role="radiogroup" aria-labelledby="vfCardsLbl">
                        @foreach(SF::CARDS as $v => $l)
                        <label><input type="radio" name="cards" value="{{ $v }}" {{ $config['cards'] === $v ? 'checked' : '' }}><span>{{ $l }}</span></label>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="vf-card" aria-labelledby="vfLookTitle">
                <h2 id="vfLookTitle">Apparence</h2>
                <fieldset style="border:0;margin:0;padding:0">
                    <legend class="vf-lbl">Couleur d'accent</legend>
                    <div class="vf-swatches">
                        @foreach($accents as $hex => $name)
                        <label class="vf-swatch" style="--sw: {{ $hex }}">
                            <input type="radio" name="accent_color" value="{{ $hex }}" {{ $accent === $hex ? 'checked' : '' }}>
                            <span aria-hidden="true"></span><span class="vf-sr">{{ $name }}</span>
                        </label>
                        @endforeach
                    </div>
                </fieldset>
                <div>
                    <span class="vf-lbl" id="vfBannerLbl">Bannière</span>
                    <div class="vf-seg" role="radiogroup" aria-labelledby="vfBannerLbl">
                        @foreach(SF::BANNERS as $v => $l)
                        <label><input type="radio" name="banner" value="{{ $v }}" {{ $config['banner'] === $v ? 'checked' : '' }}><span>{{ $l }}</span></label>
                        @endforeach
                    </div>
                </div>
            </section>
        </form>

        {{-- ══ Aperçu en direct (vraie page publique) ══ --}}
        <div class="vf-stage" id="vfStage" aria-label="Aperçu de la vitrine">
            <div class="vf-device" id="vfDevice">
                <div class="vf-browser">
                    <div class="vf-browser-bar" aria-hidden="true">
                        <span class="vf-dots"><i></i><i></i><i></i></span>
                        <span class="vf-url">{{ preg_replace('#^https?://#', '', $publicUrl) }}</span>
                    </div>
                    <div class="vf-viewport" id="vfViewport">
                        <span class="vf-loading">Mise à jour…</span>
                        <iframe class="vf-frame" id="vfFrame" title="Aperçu de votre vitrine" src="{{ $previewSrc }}" loading="eager"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Partager ma vitrine (s'ouvre aussi automatiquement après une publication) ══ --}}
    @php
        $waText = 'Découvrez ma boutique ' . $shop->name . ' sur Shopio : ' . \App\Support\TrafficSource::tag($publicUrl, 'whatsapp');
    @endphp
    <div class="vf-share-back" id="vfShare" hidden>
        <div class="vf-share" role="dialog" aria-modal="true" aria-labelledby="vfShareTitle">
            <button type="button" class="vf-share-x" data-share-close aria-label="Fermer">&times;</button>
            <h2 id="vfShareTitle">{{ session('published') ? 'Vitrine publiée : partagez-la' : 'Partager ma vitrine' }}</h2>
            <p class="vf-help" style="margin:0">Envoyez le lien à vos clients ou imprimez le QR code pour votre boutique.</p>

            <label class="vf-lbl" for="vfShareUrl" style="margin-top:6px">Lien de la vitrine</label>
            <div class="vf-share-row">
                <input type="text" class="vf-input" id="vfShareUrl" value="{{ $publicUrl }}" readonly>
                <button type="button" class="vf-btn vf-btn-blue" id="vfCopy">Copier</button>
            </div>

            <div class="vf-share-actions">
                <a href="https://wa.me/?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener" class="vf-btn vf-btn-wa">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l1.3-3.9A8 8 0 1 1 8 19z"/></svg>
                    Envoyer sur WhatsApp
                </a>
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="vf-btn vf-btn-line">Ouvrir ma vitrine</a>
            </div>

            @if($qrSvg)
            <div class="vf-qr">
                <div class="vf-qr-img" role="img" aria-label="QR code menant à votre vitrine">{!! $qrSvg !!}</div>
                <div>
                    <strong>QR code de la vitrine</strong>
                    <p class="vf-help" style="margin:4px 0 10px">À coller sur votre comptoir, vos sacs ou vos cartes de visite : vos clients le scannent avec leur téléphone.</p>
                    <a href="{{ route('boutique.storefront.qr') }}" class="vf-btn vf-btn-line">Télécharger pour imprimer</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const PUBLIC_URL = @json($publicUrl);
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const form = document.getElementById('vfForm'), root = document.getElementById('vf');
    const list = document.getElementById('vfSections');
    const frame = document.getElementById('vfFrame'), viewport = document.getElementById('vfViewport');
    const device = document.getElementById('vfDevice'), stage = document.getElementById('vfStage');
    const TEMPLATES = @json(SF::TEMPLATES);
    const FEATURED_MAX = {{ SF::FEATURED_MAX }};
    let picked = @json($config['featured_ids']);
    let dirty = false, timer = null, savedScroll = 0;

    /* ── Réglages courants → objet (même format que App\Support\Storefront) ── */
    function config() {
        const val = n => (form.querySelector('[name="' + n + '"]:checked') || form.querySelector('[name="' + n + '"]') || {}).value;
        return {
            sections: [...list.children].map(li => ({ key: li.dataset.key, on: li.querySelector('[data-role="on"]').checked })),
            announcement: document.getElementById('announcement').value.trim(),
            layout: val('layout'), columns: parseInt(val('columns'), 10), sort: document.getElementById('sort').value,
            cards: val('cards'), banner: val('banner'),
            featured_ids: picked.slice()
        };
    }
    const b64url = s => btoa(unescape(encodeURIComponent(s))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

    /* ── Aperçu : recharge la vraie page avec les réglages non publiés ── */
    function refresh() {
        const accent = (form.querySelector('[name="accent_color"]:checked') || {}).value || '';
        try { savedScroll = frame.contentWindow.scrollY || 0; } catch (e) { savedScroll = 0; }
        viewport.classList.add('is-loading');
        frame.src = PUBLIC_URL + '?vitrine=' + b64url(JSON.stringify(config())) + '&accent=' + encodeURIComponent(accent);
    }
    frame.addEventListener('load', () => {
        viewport.classList.remove('is-loading');
        try { frame.contentWindow.scrollTo(0, savedScroll); } catch (e) {}
    });
    function changed() {
        if (!dirty) { dirty = true; root.classList.add('is-dirty'); }
        clearTimeout(timer);
        timer = setTimeout(refresh, 350);
    }

    /* ── Sections : monter / descendre / masquer ── */
    function renumber() {
        [...list.children].forEach((li, i) => {
            li.querySelectorAll('input[name^="sections["]').forEach(inp => { inp.name = inp.name.replace(/sections\[\d+\]/, 'sections[' + i + ']'); });
            li.querySelector('[data-move="-1"]').disabled = i === 0;
            li.querySelector('[data-move="1"]').disabled = i === list.children.length - 1;
        });
        document.getElementById('vfVisible').textContent = [...list.querySelectorAll('[data-role="on"]')].filter(c => c.checked).length;
    }
    list.addEventListener('click', e => {
        const btn = e.target.closest('[data-move]');
        if (!btn) return;
        const li = btn.closest('li'), dir = parseInt(btn.dataset.move, 10);
        const sib = dir < 0 ? li.previousElementSibling : li.nextElementSibling;
        if (!sib) return;
        dir < 0 ? list.insertBefore(li, sib) : list.insertBefore(sib, li);
        if (!reduce) { li.classList.remove('is-moved'); void li.offsetWidth; li.classList.add('is-moved'); }
        renumber();
        btn.disabled ? li.querySelector('[data-move="' + (-dir) + '"]').focus() : btn.focus();
        changed();
    });
    list.addEventListener('change', e => {
        if (e.target.dataset.role !== 'on') return;
        e.target.closest('li').classList.toggle('is-off', !e.target.checked);
        renumber();
        changed();
    });

    /* ── Autres réglages ── */
    form.addEventListener('change', e => {
        if (e.target.name === '_device' || e.target.dataset.role === 'on') return;
        if (e.target.name === 'layout') document.getElementById('vfCols').classList.toggle('is-disabled', e.target.value === 'list');
        changed();
    });
    const ann = document.getElementById('announcement');
    ann.addEventListener('input', () => { document.getElementById('vfChars').textContent = ann.value.length; changed(); });

    /* ── Ordinateur / téléphone : la page est rendue à sa vraie largeur puis réduite pour tenir ── */
    let mode = 'desktop';
    function fit() {
        const W = mode === 'phone' ? 390 : 1280, H = mode === 'phone' ? 780 : 860;
        const pad = mode === 'phone' ? 24 : 0, chrome = mode === 'phone' ? 24 : 38;
        const availW = stage.clientWidth - 40, availH = stage.clientHeight - 40 - chrome;
        const scale = Math.min(1, (availW - pad) / W, mode === 'phone' ? (availH - pad) / H : 1);
        const viewH = mode === 'phone' ? H : Math.max(420, (availH) / scale);
        frame.style.width = W + 'px';
        frame.style.height = viewH + 'px';
        frame.style.transform = 'scale(' + scale + ')';
        viewport.style.width = (W * scale) + 'px';
        viewport.style.height = (viewH * scale) + 'px';
        device.style.width = (W * scale + pad) + 'px';
    }
    form.querySelectorAll('[name="_device"]').forEach(r => r.addEventListener('change', () => {
        mode = r.value;
        if (!reduce) { device.classList.add('is-turning'); setTimeout(() => device.classList.remove('is-turning'), 380); }
        device.classList.toggle('is-phone', mode === 'phone');
        fit();
    }));
    new ResizeObserver(fit).observe(stage);
    fit();

    renumber();

    /* ── Publier / réinitialiser ── */
    form.addEventListener('submit', () => {
        dirty = false;
        const b = document.getElementById('publishBtn');
        b.disabled = true; b.textContent = 'Publication…';
    });
    const resetBtn = document.getElementById('resetBtn');
    let armed = null;
    document.getElementById('resetForm').addEventListener('submit', e => {
        if (resetBtn.classList.contains('is-armed')) { dirty = false; return; }
        e.preventDefault();
        resetBtn.classList.add('is-armed');
        resetBtn.textContent = 'Confirmer la réinitialisation';
        clearTimeout(armed);
        armed = setTimeout(() => { resetBtn.classList.remove('is-armed'); resetBtn.textContent = 'Réinitialiser'; }, 4000);
    });
    window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    /* ── Qualité de la vitrine : le point « produit à la une » suit la sélection en direct ── */
    function updateQuality() {
        const featItem = document.querySelector('[data-check="featured"]');
        if (featItem) featItem.classList.toggle('is-done', picked.length > 0 || featItem.dataset.flagged === '1');
        const items = [...document.querySelectorAll('.vf-check')];
        const done = items.filter(i => i.classList.contains('is-done')).length;
        const pct = Math.round(done / Math.max(1, items.length) * 100);
        document.getElementById('vfQualPct').textContent = pct;
        document.getElementById('vfQualTxt').textContent = done + ' sur ' + items.length + ' points complétés';
        const ring = document.querySelector('.vf-ring');
        ring.style.setProperty('--p', pct);
        ring.classList.toggle('is-full', pct === 100);
    }
    updateQuality();

    /* ── Produit à la une : sélection ordonnée, 3 au maximum ── */
    const picks = document.getElementById('vfPicks');
    function renderPicks() {
        if (!picks) return;
        picks.querySelectorAll('.vf-pick').forEach(b => {
            const rank = picked.indexOf(parseInt(b.dataset.id, 10));
            b.classList.toggle('is-on', rank >= 0);
            b.setAttribute('aria-pressed', rank >= 0 ? 'true' : 'false');
            b.querySelector('.vf-pick-rank').textContent = rank >= 0 ? rank + 1 : '';
        });
        document.getElementById('vfFeatInputs').innerHTML = picked.map(id => '<input type="hidden" name="featured_ids[]" value="' + id + '">').join('');
        document.getElementById('vfFeatCount').textContent = picked.length;
        updateQuality();
    }
    picks?.addEventListener('click', e => {
        const b = e.target.closest('.vf-pick');
        if (!b) return;
        const id = parseInt(b.dataset.id, 10), at = picked.indexOf(id), err = document.getElementById('vfFeatErr');
        if (at >= 0) picked.splice(at, 1);
        else if (picked.length >= FEATURED_MAX) { err.hidden = false; setTimeout(() => { err.hidden = true; }, 3500); return; }
        else picked.push(id);
        err.hidden = true;
        renderPicks();
        changed();
    });
    document.getElementById('vfFeatSearch')?.addEventListener('input', e => {
        const q = e.target.value.trim().toLowerCase();
        picks.querySelectorAll('.vf-pick').forEach(b => { b.parentElement.hidden = q && !b.dataset.name.includes(q); });
    });
    if (location.hash === '#vfFeatured') document.getElementById('vfFeatSearch')?.focus();

    /* ── Modèles : réordonne les sections et règle tous les champs (non publié) ── */
    function setRadio(name, value) {
        const r = form.querySelector('[name="' + name + '"][value="' + value + '"]');
        if (r) r.checked = true;
    }
    document.querySelectorAll('[data-template]').forEach(btn => btn.addEventListener('click', () => {
        const t = TEMPLATES[btn.dataset.template];
        if (!t) return;
        t.order.forEach(key => {
            const li = list.querySelector('[data-key="' + key + '"]');
            if (!li) return;
            list.appendChild(li);
            const on = !t.off.includes(key);
            li.querySelector('[data-role="on"]').checked = on;
            li.classList.toggle('is-off', !on);
        });
        setRadio('layout', t.layout); setRadio('columns', t.columns); setRadio('cards', t.cards);
        setRadio('banner', t.banner); setRadio('accent_color', t.accent);
        document.getElementById('sort').value = t.sort;
        document.getElementById('vfCols').classList.toggle('is-disabled', t.layout === 'list');
        document.querySelectorAll('[data-template]').forEach(b => b.classList.toggle('is-applied', b === btn));
        renumber();
        changed();
    }));

    /* ── Partager ma vitrine ── */
    const share = document.getElementById('vfShare');
    let shareReturn = null;
    function openShare() {
        shareReturn = document.activeElement;
        share.hidden = false;
        share.querySelector('[data-share-close]').focus();
    }
    function closeShare() { share.hidden = true; if (shareReturn) shareReturn.focus(); }
    document.querySelectorAll('[data-share-open]').forEach(b => b.addEventListener('click', openShare));
    share.querySelector('[data-share-close]').addEventListener('click', closeShare);
    share.addEventListener('click', e => { if (e.target === share) closeShare(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !share.hidden) closeShare(); });
    document.getElementById('vfCopy').addEventListener('click', async e => {
        const input = document.getElementById('vfShareUrl'), btn = e.currentTarget;
        try { await navigator.clipboard.writeText(input.value); }
        catch (err) { input.select(); document.execCommand && document.execCommand('copy'); }
        btn.textContent = 'Copié';
        setTimeout(() => { btn.textContent = 'Copier'; }, 2000);
    });
    @if(session('published'))
    openShare();
    @endif
})();
</script>
@endpush
