{{--
    resources/views/vendeur/shop/storefront.blade.php
    Route     : GET /boutique/vitrine → Vendeur\StorefrontController@edit → name('boutique.storefront.edit')
    Rôle      : « Éditeur de ma boutique » (maquette Editeur.dc.html) — rail d'outils (Blocs, Style,
                Thèmes, Réglages), panneau d'outils, aperçu en direct de la vraie page, panneau du bloc
                sélectionné. Annuler / rétablir (Ctrl+Z / Ctrl+Y), glisser-déposer des blocs.
                L'aperçu charge /shops/{id}/products?apercu=1 : la page lit le brouillon envoyé en
                session (POST boutique.storefront.draft), visible du seul propriétaire.
                « Publier » envoie l'état complet (JSON) → StorefrontController@update.
    Variables : $shop, $config, $accent, $products, $categories, $promoCodes, $history, $checklist,
                $publicUrl, $previewUrl, $qrSvg, $imageUrls, $storageBase
--}}
@extends('layouts.app')
@section('title', 'Éditeur de ma boutique · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    use App\Support\Storefront as SF;
    $blockMeta = collect(SF::BLOCKS)->map(fn ($m, $k) => ['label' => $m[0], 'desc' => $m[1], 'single' => $m[2], 'trust' => $m[3]])->all();
@endphp

@push('styles')
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Nunito:wght@700&family=Lora:wght@700&display=swap" rel="stylesheet">
<style>
/* Éditeur de boutique : barre bleu nuit, rail d'outils, panneau, aperçu, panneau du bloc */
:root {
    --vf-blue: #1F7CF6; --vf-btn: #1668D8; --vf-btn-h: #1260C9; --vf-b50: #EAF2FF; --vf-b100: #D6E6FF;
    --vf-night: #0B1E3D; --vf-night2: #132B52; --vf-sky: #7DB4FF; --vf-amber: #F59E0B; --vf-amb50: #FEF3E0; --vf-ambi: #92400E;
    --vf-bg: #F6F9FF; --vf-stage: #DCE6F5; --vf-surf: #FFFFFF; --vf-line: #E3E8F2;
    --vf-ink: #16162A; --vf-ink2: #45455E; --vf-ink3: #62627C; --vf-ok: #15803D; --vf-ok50: #E7F6EC; --vf-err: #B91C1C;
    --vf-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --vf-out: cubic-bezier(.22,1,.36,1); --vf-io: cubic-bezier(.65,0,.35,1); --vf-pop: cubic-bezier(.34,1.56,.64,1);
}
[data-theme="dark"] {
    --vf-b50: #13284B; --vf-b100: #1B3563; --vf-bg: #0A1426; --vf-stage: #071022; --vf-surf: #10203B; --vf-line: #22355A;
    --vf-ink: #EAF0FA; --vf-ink2: #C3CEE0; --vf-ink3: #9DAAC2; --vf-ok: #4ADE80; --vf-ok50: #0F2A1C; --vf-err: #F87171; --vf-amb50: #2E2208; --vf-ambi: #FBBF24;
}
/* Outil de travail : pas de fenêtre « Installer l'application » ni de bandeau de notifications par-dessus */
#pwaModal, #pushNotifBanner { display: none !important; }
html, body { margin: 0; background: var(--vf-bg); color: var(--vf-ink); font-family: var(--vf-font); -webkit-font-smoothing: antialiased; }
.vf, .vf * { box-sizing: border-box; }
.vf { min-height: 100vh; display: flex; flex-direction: column; font-size: 14.5px; line-height: 1.5; }
.vf-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.vf :focus-visible { outline: 3px solid var(--vf-blue); outline-offset: 2px; }
.vf h2, .vf h3 { margin: 0; letter-spacing: -.01em; font-weight: 800; }
.vf-num { font-variant-numeric: tabular-nums; }

/* ── Barre du haut ── */
.vf-top { position: sticky; top: 0; z-index: 40; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px max(18px, env(safe-area-inset-right, 0px)) 10px max(18px, env(safe-area-inset-left, 0px)); background: var(--vf-night); color: #fff; }
.vf-brand { display: flex; align-items: center; gap: 12px; flex: 1 1 240px; min-width: 0; }
.vf-brand img { width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0; }
.vf-brand h1 { margin: 0; font-size: 18px; font-weight: 800; color: #fff; }
.vf-brand p { margin: 0; font-size: 12.5px; color: #9FB3D3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vf-pillset { display: flex; gap: 2px; padding: 4px; border-radius: 999px; background: var(--vf-night2); }
.vf-pillset button { min-height: 36px; padding: 0 12px; border: 0; border-radius: 999px; background: transparent; color: #C9D6EA; font: 700 13px var(--vf-font); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background-color 200ms var(--vf-out), color 200ms; }
.vf-pillset button:hover:not(:disabled) { color: #fff; }
.vf-pillset button[aria-pressed="true"] { background: #fff; color: var(--vf-night); }
.vf-pillset button:disabled { opacity: .35; cursor: default; }
.vf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 999px; border: 0; font: 700 14px/1 var(--vf-font); text-decoration: none; cursor: pointer; white-space: nowrap; transition: transform 180ms var(--vf-out), background-color 180ms, box-shadow 180ms, color 180ms, opacity 180ms; }
.vf-btn:active:not(:disabled) { transform: scale(.97); }
.vf-btn:disabled { opacity: .45; cursor: default; }
.vf-btn-sm { min-height: 34px; padding: 0 12px; font-size: 13px; }
.vf-btn-night { background: var(--vf-night2); color: #fff; }
.vf-btn-night:hover { background: #1B3563; color: #fff; }
.vf-btn-amber { background: var(--vf-amber); color: #16162A; }
.vf-btn-amber:hover { box-shadow: 0 8px 20px rgba(245,158,11,.35); color: #16162A; }
.vf-btn-blue { background: var(--vf-btn); color: #fff; }
.vf-btn-blue:hover { background: var(--vf-btn-h); color: #fff; }
.vf-btn-line { background: var(--vf-surf); color: var(--vf-ink); box-shadow: inset 0 0 0 1.5px var(--vf-line); }
.vf-btn-line:hover:not(:disabled) { box-shadow: inset 0 0 0 1.5px var(--vf-blue); color: var(--vf-btn); }
.vf-btn-danger { background: var(--vf-surf); color: var(--vf-err); box-shadow: inset 0 0 0 1.5px var(--vf-line); }
.vf-btn-danger:hover { box-shadow: inset 0 0 0 1.5px var(--vf-err); }
.vf-btn-wa { background: #15803D; color: #fff; }
.vf-btn-wa:hover { background: #116B33; color: #fff; }
.vf-dirty { display: none; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #FBBF24; }
.vf-dirty::before { content: ''; width: 8px; height: 8px; border-radius: 999px; background: currentColor; }
.vf.is-dirty .vf-dirty { display: inline-flex; }

.vf-flash { margin: 12px 20px 0; padding: 12px 16px; border-radius: 16px; font-weight: 700; font-size: 14px; }
.vf-flash-ok { background: var(--vf-ok50); color: var(--vf-ok); }
.vf-flash-err { background: #FDECEC; color: #B91C1C; }

/* ── Corps : rail | panneau | aperçu | bloc sélectionné ── */
.vf-body { flex: 1; display: grid; grid-template-columns: 84px 320px minmax(0, 1fr) 320px; gap: 14px; padding: 14px 16px 30px; align-items: start; }
.vf-rail { position: sticky; top: 78px; display: flex; flex-direction: column; gap: 6px; padding: 8px; background: var(--vf-night); border-radius: 22px; }
.vf-rail button { display: flex; flex-direction: column; align-items: center; gap: 4px; width: 68px; padding: 10px 4px; border: 0; border-radius: 16px; background: transparent; color: #9FB3D3; font: 700 11px var(--vf-font); cursor: pointer; transition: background-color 200ms, color 200ms; }
.vf-rail button:hover { color: #fff; }
.vf-rail button[aria-pressed="true"] { background: #fff; color: var(--vf-night); }
.vf-col { display: flex; flex-direction: column; gap: 12px; min-width: 0; }
.vf-panel { background: var(--vf-surf); border-radius: 22px; box-shadow: 0 0 0 1px var(--vf-line); padding: 16px; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
.vf-panel-in { animation: vfIn 320ms var(--vf-out) backwards; }
@keyframes vfIn { from { opacity: 0; transform: translateY(6px); } }
.vf-panel h2 { font-size: 16px; }
.vf-panel h3 { font-size: 14px; }
.vf-hint { margin: 0; font-size: 13px; color: var(--vf-ink2); }
.vf-lbl { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--vf-ink3); margin-bottom: 5px; }
.vf-field { display: flex; flex-direction: column; }
.vf-input, .vf-select, .vf-area { width: 100%; min-height: 42px; border: 0; border-radius: 12px; padding: 0 12px; background: var(--vf-surf); color: var(--vf-ink); font: 500 14px var(--vf-font); box-shadow: inset 0 0 0 1.5px var(--vf-line); outline: 0; }
.vf-area { padding: 10px 12px; min-height: 92px; resize: vertical; line-height: 1.45; }
.vf-input:focus, .vf-select:focus, .vf-area:focus { box-shadow: inset 0 0 0 2px var(--vf-blue); }
.vf-chars { font-size: 12px; color: var(--vf-ink3); text-align: right; margin-top: 3px; }
.vf-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; white-space: nowrap; }
.vf-row { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 14px; background: var(--vf-bg); }
.vf-row-txt { flex: 1; min-width: 0; }
.vf-row-txt b { display: block; font-size: 14px; }
.vf-row-txt span { display: block; font-size: 12px; color: var(--vf-ink3); }

/* Segments */
.vf-seg { display: flex; padding: 4px; gap: 2px; border-radius: 999px; background: var(--vf-b50); }
.vf-seg button { flex: 1; min-height: 36px; border: 0; border-radius: 999px; background: transparent; font: 700 13px var(--vf-font); color: var(--vf-ink2); cursor: pointer; padding: 0 8px; transition: background-color 200ms var(--vf-out), color 200ms, box-shadow 200ms; }
.vf-seg button[aria-pressed="true"] { background: var(--vf-surf); color: var(--vf-btn); box-shadow: 0 2px 6px rgba(11,30,61,.12); }
.vf-seg.is-disabled { opacity: .45; pointer-events: none; }

/* Interrupteur */
.vf-sw { position: relative; flex-shrink: 0; width: 46px; height: 28px; border-radius: 999px; border: 0; cursor: pointer; padding: 0; background: #C3CBDA; transition: background-color 200ms var(--vf-out); }
.vf-sw span { position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 999px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,.25); transition: transform 260ms var(--vf-pop); }
.vf-sw[aria-checked="true"] { background: var(--vf-btn); }
.vf-sw[aria-checked="true"] span { transform: translateX(18px); }
.vf-sw.is-amber[aria-checked="true"] { background: var(--vf-amber); }
.vf-sw:disabled { opacity: .5; cursor: default; }

/* Structure de la page (glisser-déposer) */
.vf-tree { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; }
.vf-node { display: flex; align-items: center; gap: 8px; padding: 6px 6px 6px 8px; border-radius: 14px; background: var(--vf-bg); cursor: grab; border: 0; transition: background-color 160ms, box-shadow 160ms, transform 220ms var(--vf-out), opacity 160ms; }
.vf-node:hover { background: var(--vf-b50); }
.vf-node.is-sel { background: var(--vf-b50); box-shadow: inset 0 0 0 2px var(--vf-blue); }
.vf-node.is-off .vf-node-name { color: var(--vf-ink3); text-decoration: line-through; }
.vf-node.is-dragging { opacity: .35; }
.vf-node.is-over { box-shadow: inset 0 3px 0 var(--vf-amber); }
.vf-node.is-moved { animation: vfMoved 420ms var(--vf-pop); }
@keyframes vfMoved { 0% { transform: scale(.96); background: var(--vf-b100); } }
.vf-node-ico { width: 32px; height: 32px; border-radius: 10px; background: var(--vf-surf); color: var(--vf-btn); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 0 0 1px var(--vf-line); }
.vf-node-txt { flex: 1; min-width: 0; text-align: left; }
.vf-node-name { display: block; font-size: 13.5px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vf-node-sub { display: block; font-size: 11.5px; color: var(--vf-ink3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vf-node-lock { color: var(--vf-ink3); display: flex; }
.vf-mini { width: 30px; height: 30px; border-radius: 9px; border: 0; background: transparent; color: var(--vf-ink2); cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.vf-mini:hover:not(:disabled) { background: var(--vf-surf); color: var(--vf-btn); }
.vf-mini:disabled { opacity: .3; cursor: default; }
.vf-drop-end { padding: 12px; border-radius: 14px; border: 2px dashed #B8CCEB; text-align: center; font-size: 12.5px; font-weight: 700; color: var(--vf-ink3); transition: background-color 200ms, border-color 200ms; }
.vf-drop-end.is-over { background: var(--vf-amb50); border-color: var(--vf-amber); color: var(--vf-ambi); }

/* Bibliothèque de blocs */
.vf-lib { display: flex; flex-direction: column; gap: 6px; }
.vf-libb { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border: 0; border-radius: 14px; background: var(--vf-bg); color: var(--vf-ink); font: 600 13.5px var(--vf-font); text-align: left; cursor: grab; width: 100%; transition: transform 220ms var(--vf-out), background-color 200ms, box-shadow 220ms; }
.vf-libb:hover { background: var(--vf-b50); transform: perspective(500px) rotateY(-8deg) translateX(3px); box-shadow: -6px 8px 16px rgba(11,30,61,.08); }
.vf-libb small { display: block; font-size: 11.5px; font-weight: 500; color: var(--vf-ink3); }
.vf-libb .vf-plus { margin-left: auto; font-size: 18px; color: var(--vf-btn); }

/* Style */
.vf-swatches { display: flex; gap: 10px; flex-wrap: wrap; }
.vf-swatch { width: 38px; height: 38px; border-radius: 12px; border: 0; cursor: pointer; box-shadow: inset -4px -5px 8px rgba(0,0,0,.22), inset 3px 3px 6px rgba(255,255,255,.25); transition: transform 220ms var(--vf-pop); }
.vf-swatch:hover { transform: perspective(400px) rotateX(12deg) rotateY(-12deg) scale(1.08); }
.vf-swatch[aria-pressed="true"] { outline: 3px solid var(--vf-ink); outline-offset: 3px; }
.vf-color { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 14px; background: var(--vf-bg); cursor: pointer; }
.vf-color input { width: 44px; height: 34px; border: 0; padding: 0; background: none; cursor: pointer; }
.vf-ct { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 14px; }
.vf-ct-aa { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
.vf-tiles { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.vf-tile { border: 0; padding: 0; cursor: pointer; border-radius: 14px; min-height: 66px; box-shadow: 0 0 0 1px var(--vf-line); background: var(--vf-surf); color: var(--vf-ink); transition: transform 220ms var(--vf-pop); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; }
.vf-tile:hover { transform: translateY(-2px); }
.vf-tile[aria-pressed="true"] { box-shadow: 0 0 0 3px var(--vf-blue); }
.vf-tile b { font-size: 22px; }
.vf-tile small { font: 700 12px var(--vf-font); color: var(--vf-ink3); }
.vf-tiles-4 { grid-template-columns: repeat(4, 1fr); }
.vf-tiles-4 .vf-tile { min-height: 54px; }
.vf-bglbl { display: grid; grid-template-columns: repeat(4, 1fr); font-size: 11.5px; font-weight: 700; color: var(--vf-ink3); text-align: center; }
.vf-btnprev { display: flex; justify-content: center; padding: 12px; border-radius: 14px; background: var(--vf-bg); }

/* Thèmes, historique */
.vf-theme-sw { display: flex; width: 54px; height: 38px; border-radius: 10px; overflow: hidden; flex-shrink: 0; box-shadow: 0 0 0 1px var(--vf-line); }
.vf-theme-sw span { flex: 1; }
.vf-row.is-active { box-shadow: 0 0 0 2px var(--vf-ok); }
.vf-dot { width: 8px; height: 8px; border-radius: 8px; flex-shrink: 0; background: #C3CBDA; }
.vf-templates { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.vf-tpl { display: flex; flex-direction: column; align-items: flex-start; gap: 3px; padding: 10px 12px; border: 0; border-radius: 14px; background: var(--vf-bg); color: var(--vf-ink); text-align: left; cursor: pointer; font-family: inherit; box-shadow: inset 0 0 0 1px var(--vf-line); transition: transform 260ms var(--vf-out), box-shadow 200ms; }
.vf-tpl:hover { transform: perspective(600px) rotateX(6deg) rotateY(-6deg) translateY(-2px); box-shadow: inset 0 0 0 1.5px var(--tc); }
.vf-tpl-dot { width: 20px; height: 20px; border-radius: 7px; background: var(--tc); }
.vf-tpl b { font-size: 13.5px; }
.vf-tpl small { font-size: 11.5px; color: var(--vf-ink3); line-height: 1.35; }

/* Adresse */
.vf-slug { display: flex; align-items: center; border-radius: 12px; box-shadow: inset 0 0 0 1.5px var(--vf-line); overflow: hidden; background: var(--vf-surf); }
.vf-slug span { padding: 0 2px 0 12px; font-size: 12.5px; color: var(--vf-ink3); white-space: nowrap; }
.vf-slug input { flex: 1; min-width: 0; height: 42px; border: 0; outline: 0; font: 700 14px var(--vf-font); color: var(--vf-btn-h); background: transparent; }
.vf-slug:focus-within { box-shadow: inset 0 0 0 2px var(--vf-blue); }
.vf-msg { font-size: 12.5px; font-weight: 700; }
.vf-msg.is-ok { color: var(--vf-ok); } .vf-msg.is-err { color: var(--vf-err); }

/* Qualité */
.vf-quality-hd { display: flex; align-items: center; gap: 14px; }
.vf-ring { --p: 0; width: 56px; height: 56px; flex-shrink: 0; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; color: var(--vf-ink2);
    background: radial-gradient(closest-side, var(--vf-surf) 76%, transparent 78%), conic-gradient(var(--vf-ring, var(--vf-btn)) calc(var(--p) * 1%), var(--vf-b100) 0); }
.vf-ring b { font-size: 16px; color: var(--vf-ink); }
.vf-ring.is-full { --vf-ring: var(--vf-ok); }
.vf-checks { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 2px; }
.vf-check { display: flex; align-items: center; gap: 10px; min-height: 34px; font-size: 13px; }
.vf-check-ico { width: 20px; height: 20px; border-radius: 999px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 2px var(--vf-line); color: transparent; }
.vf-check.is-done .vf-check-ico { background: var(--vf-ok); color: #fff; box-shadow: none; }
.vf-check-lbl { flex: 1; min-width: 0; font-weight: 600; }
.vf-check.is-done .vf-check-lbl { color: var(--vf-ink3); font-weight: 500; }
.vf-check-cta { font-size: 12px; font-weight: 800; color: var(--vf-btn); text-decoration: none; padding: 5px 10px; border-radius: 999px; background: var(--vf-b50); white-space: nowrap; }
.vf-check.is-done .vf-check-cta { display: none; }

/* Inspecteur : photos, questions, produits à la une */
.vf-ins-hd { display: flex; align-items: center; gap: 10px; }
.vf-ins-ico { width: 38px; height: 38px; border-radius: 12px; background: var(--vf-b50); color: var(--vf-btn); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.vf-note { padding: 12px; border-radius: 14px; background: var(--vf-bg); font-size: 13px; color: var(--vf-ink2); }
.vf-photo { position: relative; border-radius: 14px; overflow: hidden; background: var(--vf-b50); aspect-ratio: 4 / 3; display: flex; align-items: center; justify-content: center; color: var(--vf-btn); }
.vf-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
.vf-photos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; }
.vf-photos .vf-photo { aspect-ratio: 1; }
.vf-photo-x { position: absolute; top: 4px; right: 4px; width: 26px; height: 26px; border-radius: 999px; border: 0; background: rgba(11,30,61,.75); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.vf-upload { border: 2px dashed #B8CCEB; background: transparent; cursor: pointer; font: 700 12.5px var(--vf-font); flex-direction: column; gap: 4px; }
.vf-upload:hover { border-color: var(--vf-blue); background: var(--vf-b50); }
.vf-upload.is-busy { opacity: .6; pointer-events: none; }
.vf-faq-item { display: flex; flex-direction: column; gap: 6px; padding: 10px; border-radius: 14px; background: var(--vf-bg); }
.vf-faq-item .vf-area { min-height: 64px; }
.vf-picks { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; max-height: 300px; overflow-y: auto; scrollbar-width: thin; }
.vf-pick { width: 100%; display: flex; align-items: center; gap: 10px; padding: 6px 8px; border: 0; border-radius: 14px; background: transparent; color: var(--vf-ink); text-align: left; cursor: pointer; font-family: inherit; }
.vf-pick:hover { background: var(--vf-bg); }
.vf-pick.is-on { background: var(--vf-b50); box-shadow: inset 0 0 0 1.5px var(--vf-btn); }
.vf-pick-img { width: 38px; height: 38px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: var(--vf-b50); }
.vf-pick-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.vf-pick-txt { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.vf-pick-name { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vf-pick-price { font-size: 11.5px; color: var(--vf-ink3); }
.vf-pick-rank { width: 22px; height: 22px; border-radius: 999px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 11.5px; font-weight: 800; box-shadow: inset 0 0 0 2px var(--vf-line); }
.vf-pick.is-on .vf-pick-rank { background: var(--vf-btn); color: #fff; box-shadow: none; }
.vf-empty { padding: 22px 8px; text-align: center; color: var(--vf-ink3); }
.vf-empty strong { display: block; color: var(--vf-ink); }
.vf-kbd { font-size: 12.5px; color: var(--vf-ink2); display: flex; flex-direction: column; gap: 4px; }
.vf-kbd kbd { font: 700 11px var(--vf-font); padding: 2px 6px; border-radius: 6px; background: var(--vf-bg); box-shadow: inset 0 -1px 0 var(--vf-line); }

/* ── Aperçu ── */
.vf-stage { position: sticky; top: 78px; height: calc(100vh - 100px); min-height: 520px; padding: 18px; border-radius: 28px; background: var(--vf-stage); display: flex; align-items: flex-start; justify-content: center; overflow: hidden; perspective: 2000px; }
.vf-device { position: relative; transform-style: preserve-3d; transition: transform 700ms var(--vf-io); animation: vfPin 600ms var(--vf-out) backwards; }
@keyframes vfPin { from { opacity: 0; transform: rotateX(10deg) translateY(20px); } }
.vf-device.is-turning { transform: rotateY(-14deg) scale(.96); }
.vf-browser { width: 100%; border-radius: 18px; overflow: hidden; background: var(--vf-surf); box-shadow: 0 30px 70px rgba(11,30,61,.22); }
.vf-browser-bar { display: flex; align-items: center; gap: 10px; height: 36px; padding: 0 14px; background: var(--vf-b50); border-bottom: 1px solid var(--vf-line); }
.vf-dots { display: flex; gap: 6px; } .vf-dots i { width: 10px; height: 10px; border-radius: 999px; background: #C3CBDA; }
.vf-url { flex: 1; min-width: 0; height: 24px; border-radius: 999px; background: var(--vf-surf); display: flex; align-items: center; padding: 0 12px; font-size: 12px; color: var(--vf-ink3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vf-viewport { position: relative; overflow: hidden; background: #fff; }
.vf-frame { display: block; border: 0; transform-origin: 0 0; background: #fff; transition: opacity 220ms var(--vf-out); }
.vf-viewport.is-loading .vf-frame { opacity: .6; }
.vf-loading { position: absolute; top: 10px; right: 12px; display: none; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; background: var(--vf-night); color: #fff; font-size: 12px; font-weight: 700; z-index: 2; }
.vf-viewport.is-loading .vf-loading { display: inline-flex; }
.vf-device.is-phone .vf-browser { border-radius: 40px; padding: 10px; background: #0B1E3D; }
.vf-device.is-phone .vf-browser-bar { display: none; }
.vf-device.is-phone .vf-viewport { border-radius: 30px; }

/* Partage */
.vf-share-back { position: fixed; inset: 0; z-index: 80; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(11,30,61,.55); animation: vfFade 250ms var(--vf-out); }
.vf-share-back[hidden] { display: none; }
@keyframes vfFade { from { opacity: 0; } }
.vf-share { position: relative; width: 100%; max-width: 520px; max-height: calc(100vh - 32px); overflow-y: auto; background: var(--vf-surf); border-radius: 24px; padding: 24px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 30px 60px -20px rgba(11,30,61,.5); animation: vfDrop 450ms var(--vf-out); }
@keyframes vfDrop { from { opacity: 0; transform: perspective(900px) rotateX(-18deg) translateY(-10px); } }
.vf-share h2 { font-size: 20px; padding-right: 40px; }
.vf-share-x { position: absolute; top: 14px; right: 14px; width: 40px; height: 40px; border-radius: 999px; border: 0; background: var(--vf-bg); color: var(--vf-ink); font-size: 22px; cursor: pointer; }
.vf-share-row { display: flex; gap: 8px; } .vf-share-row .vf-input { flex: 1; min-width: 0; }
.vf-qr { display: flex; gap: 16px; align-items: center; padding: 14px; border-radius: 18px; background: var(--vf-bg); flex-wrap: wrap; }
.vf-qr-img { width: 128px; height: 128px; flex-shrink: 0; padding: 8px; border-radius: 14px; background: #fff; }
.vf-qr-img svg { width: 100%; height: 100%; display: block; }

/* Liens des catégories */
/* Bloc Catégories : chaque catégorie et son bouton « Partager » */
.vf-catlinks { display: flex; flex-direction: column; gap: 6px; }
.vf-catlink { display: flex; align-items: center; gap: 10px; padding: 8px 8px 8px 12px; border-radius: 14px; background: var(--vf-bg); }
.vf-catlink > div { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.vf-catlink b { font-size: 13.5px; overflow-wrap: anywhere; }
.vf-catlink span { font-size: 12px; color: var(--vf-ink3); }

/* Toast */
.vf-toast { position: fixed; left: 50%; bottom: calc(24px + env(safe-area-inset-bottom, 0px)); z-index: 90; display: flex; align-items: center; gap: 10px; padding: 13px 20px; border-radius: 999px; background: var(--vf-night); color: #fff; font-weight: 700; box-shadow: 0 16px 32px rgba(11,30,61,.35); transform: translate(-50%, 0); animation: vfToast 300ms var(--vf-out) both; }
.vf-toast[hidden] { display: none; }
@keyframes vfToast { from { opacity: 0; transform: translate(-50%, 16px); } }

@media (max-width: 1380px) {
    /* Le bloc sélectionné passe en haut de la colonne d'outils, l'aperçu prend toute la hauteur */
    .vf-body { grid-template-columns: 84px 320px minmax(0, 1fr); }
    .vf-rail { grid-column: 1; grid-row: 1 / span 2; }
    .vf-inspect { grid-column: 2; grid-row: 1; }
    #vfTools { grid-column: 2; grid-row: 2; }
    .vf-stage { grid-column: 3; grid-row: 1 / span 2; }
}
@media (max-width: 960px) {
    .vf-body { grid-template-columns: minmax(0, 1fr); padding: 12px; }
    .vf-rail, .vf-inspect, #vfTools, .vf-stage { grid-column: auto; grid-row: auto; }
    .vf-kbd-card { display: none; }
    .vf-rail { position: static; flex-direction: row; justify-content: space-between; }
    .vf-rail button { flex: 1; width: auto; }
    .vf-stage { position: relative; top: 0; height: 600px; order: -1; }
    .vf-inspect { grid-column: auto; grid-row: auto; }
}
@media (prefers-reduced-motion: reduce) {
    .vf *, .vf *::before, .vf *::after { animation: none !important; transition-duration: 1ms !important; }
    .vf-libb:hover, .vf-swatch:hover, .vf-tpl:hover, .vf-tile:hover { transform: none; }
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
                <h1>Éditeur de ma boutique</h1>
                <p>Cliquez sur un bloc pour le modifier · glissez-le pour le déplacer</p>
            </div>
        </div>
        <span class="vf-dirty" role="status">Non publié</span>
        <div class="vf-pillset">
            <button type="button" id="vfUndo" disabled title="Annuler (Ctrl+Z)">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h11a5 5 0 0 1 0 10h-3"/></svg>Annuler
            </button>
            <button type="button" id="vfRedo" disabled title="Rétablir (Ctrl+Y)" aria-label="Rétablir">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 14 5-5-5-5"/><path d="M20 9H9a5 5 0 0 0 0 10h3"/></svg>
            </button>
        </div>
        <div class="vf-pillset" role="group" aria-label="Appareil">
            <button type="button" data-device="desktop" aria-pressed="true">Ordinateur</button>
            <button type="button" data-device="phone" aria-pressed="false">Téléphone</button>
        </div>
        <a class="vf-btn vf-btn-night" href="{{ $publicUrl }}" target="_blank" rel="noopener">Vitrine</a>
        <a class="vf-btn vf-btn-night" href="{{ route('boutique.dashboard') }}">Tableau de bord</a>
        <button type="button" class="vf-btn vf-btn-night" data-share-open="">Partager</button>
        <form method="POST" action="{{ route('boutique.storefront.update') }}" id="vfPublishForm">
            @csrf @method('PUT')
            <input type="hidden" name="payload" id="vfPayload">
            <button type="submit" class="vf-btn vf-btn-amber" id="vfPublish">Publier</button>
        </form>
    </header>

    @if(session('success'))<div class="vf-flash vf-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="vf-flash vf-flash-err" role="alert">{{ $errors->first() }}</div>@endif

    <div class="vf-body">
        {{-- ══ Rail d'outils ══ --}}
        <nav class="vf-rail" aria-label="Outils" id="vfRail">
            @foreach(['blocks' => ['Blocs', 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM17.5 14v7M14 17.5h7'], 'style' => ['Style', 'M12 22a10 10 0 1 1 10-10c0 2-1.5 3-3 3h-2.5a2 2 0 0 0-1.5 3.3A2 2 0 0 1 12 22zM7.5 11.5h.01M10.5 7.5h.01M15.5 8.5h.01'], 'themes' => ['Thèmes', 'M12 8v4l3 2M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5'], 'set' => ['Réglages', 'M4 7h10M18 7h2M4 17h4M12 17h8M16 5v4M10 15v4']] as $k => [$label, $path])
            <button type="button" data-panel="{{ $k }}" aria-pressed="{{ $k === 'blocks' ? 'true' : 'false' }}">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $path }}"/></svg>{{ $label }}
            </button>
            @endforeach
        </nav>

        {{-- ══ Panneau d'outils (rempli par le script) ══ --}}
        <aside class="vf-col" id="vfTools" aria-live="polite"></aside>

        {{-- ══ Aperçu en direct (vraie page publique) ══ --}}
        <section class="vf-stage" id="vfStage" aria-label="Aperçu de la boutique">
            <div class="vf-device" id="vfDevice">
                <div class="vf-browser">
                    <div class="vf-browser-bar" aria-hidden="true">
                        <span class="vf-dots"><i></i><i></i><i></i></span>
                        <span class="vf-url" id="vfUrl">{{ preg_replace('#^https?://#', '', $publicUrl) }}</span>
                    </div>
                    <div class="vf-viewport" id="vfViewport">
                        <span class="vf-loading">Mise à jour…</span>
                        <iframe class="vf-frame" id="vfFrame" title="Aperçu de votre boutique" loading="eager"></iframe>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══ Bloc sélectionné ══ --}}
        <aside class="vf-col vf-inspect" id="vfInspect" aria-label="Bloc sélectionné"></aside>
    </div>

    {{-- ══ Partager : la boutique entière ou une de ses catégories (chaque catégorie a son lien, automatiquement) ══ --}}
    @php $waText = 'Découvrez ma boutique ' . $shop->name . ' sur Shopio : ' . \App\Support\TrafficSource::tag($publicUrl, 'whatsapp'); @endphp
    <div class="vf-share-back" id="vfShare" hidden>
        <div class="vf-share" role="dialog" aria-modal="true" aria-labelledby="vfShareTitle">
            <button type="button" class="vf-share-x" data-share-close aria-label="Fermer">&times;</button>
            <h2 id="vfShareTitle">{{ session('published') ? 'Boutique publiée : partagez-la' : 'Partager' }}</h2>
            <p class="vf-hint" id="vfShareHint">Envoyez le lien à vos clients ou imprimez le QR code pour votre comptoir.</p>
            @if(count($categoryLinks))
            <label class="vf-lbl" for="vfShareWhat">Que voulez-vous partager ?</label>
            <select class="vf-select" id="vfShareWhat">
                <option value="">Toute la boutique</option>
                <optgroup label="Une catégorie">
                    @foreach($categoryLinks as $i => $c)<option value="{{ $i }}">{{ $c['name'] }} · {{ $c['count'] }} produit{{ $c['count'] > 1 ? 's' : '' }}</option>@endforeach
                </optgroup>
            </select>
            @endif
            <label class="vf-lbl" for="vfShareUrl">Lien</label>
            <div class="vf-share-row">
                <input type="text" class="vf-input" id="vfShareUrl" value="{{ $publicUrl }}" readonly>
                <button type="button" class="vf-btn vf-btn-blue" id="vfCopy">Copier</button>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="https://wa.me/?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener" class="vf-btn vf-btn-wa" id="vfShareWa">Envoyer sur WhatsApp</a>
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="vf-btn vf-btn-line" id="vfShareOpen">Ouvrir la page</a>
            </div>
            @if($qrSvg)
            <div class="vf-qr">
                <div class="vf-qr-img" id="vfShareQr" role="img" aria-label="QR code menant à cette page">{!! $qrSvg !!}</div>
                <div style="flex:1 1 200px;min-width:0">
                    <strong id="vfShareQrTitle">QR code de la boutique</strong>
                    <p class="vf-hint" style="margin:4px 0 10px">À coller sur votre comptoir, vos sacs, vos rayons ou vos cartes de visite.</p>
                    <a href="{{ route('boutique.storefront.qr') }}" class="vf-btn vf-btn-line" id="vfShareQrDl">Télécharger pour imprimer</a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('boutique.storefront.reset') }}" id="vfResetForm" hidden>@csrf</form>
    <div class="vf-toast" id="vfToast" role="status" hidden></div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    /* ───────────────────────── Données ───────────────────────── */
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const URLS = {
        draft: @json(route('boutique.storefront.draft')), image: @json(route('boutique.storefront.image')),
        slug: @json(route('boutique.storefront.slug')), preview: @json($previewUrl),
        shopEdit: @json(route('shop.edit', $shop)), productNew: @json(route('products.create')),
    };
    const BLOCKS = @json($blockMeta);
    const CORE = @json(SF::CORE);
    const FONTS = @json(collect(SF::FONTS)->map(fn ($f) => ['label' => $f[0], 'css' => $f[1]]));
    const PRESETS = @json(SF::COLOR_PRESETS);
    const THEMES = @json(SF::THEMES);
    const TEMPLATES = @json(SF::TEMPLATES);
    const BTN_SUGG = @json(SF::BTN_SUGGESTIONS);
    const DEFAULTS = @json(collect(SF::BLOCKS)->keys()->mapWithKeys(fn ($t) => [$t => SF::blockDefaults($t)]));
    const LIMITS = { featured: {{ SF::FEATURED_MAX }}, gallery: {{ SF::GALLERY_MAX }}, faq: {{ SF::FAQ_MAX }}, themes: {{ SF::THEMES_MAX }}, blocks: {{ SF::MAX_BLOCKS }}, ann: {{ SF::ANNOUNCEMENT_MAX }} };
    const PRODUCTS = @json($products);
    const CATEGORIES = @json($categories);
    const CAT_LINKS = @json($categoryLinks);
    const PROMOS = @json($promoCodes);
    const HISTORY = @json($history);
    const CHECKLIST = @json($checklist);
    const SHOP = { name: @json($shop->name), address: @json((string) $shop->address), hasImage: @json((bool) $shop->image), slugBase: @json(rtrim(url('/s'), '/') . '/'), defaultUrl: @json(route('public.shops.products', $shop)) };
    const IMG_URLS = @json((object) $imageUrls);
    const STORAGE = @json($storageBase);
    const BG = { white: ['#FFFFFF', '#16162A'], soft: ['#F6F9FF', '#16162A'], texture: ['#FBF8F3', '#16162A'], dark: ['#0B1E3D', '#FFFFFF'] };
    const ICON = {
        __banner: 'M3 5h18v14H3zM3 15l5-5 4 4 3-3 6 6', announcement: 'M13 3 5 13.5h6L10 21l9-11h-6z', categories: 'M4 6h16M4 12h10M4 18h6',
        featured: 'M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z', products: 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        reviews: 'm12 2 3 6.5 7 .8-5.2 4.8 1.4 7L12 17.6 5.8 21l1.4-7L2 9.3l7-.8z', delivery: 'M2 6h20v12H2zM12 9.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5',
        contact: 'M4 5h16v11H9l-5 4zM8 9h8M8 12h5', text: 'M4 6h16M4 12h16M4 18h10', imgtext: 'M3 5h8v14H3zM14 7h7M14 12h7M14 17h5',
        video: 'M3 5h18v14H3zM10 9v6l5-3z', gallery: 'M3 3h11v11H3zM17 3h4v4h-4zM17 10h4v11h-4zM3 17h11v4H3z',
        countdown: 'M12 8v5l3 2M9 2h6M12 22a8 8 0 1 0 0-16 8 8 0 0 0 0 16', faq: 'M9 9a3 3 0 1 1 4 2.8c-.6.3-1 .9-1 1.6V14M12 18h.01',
        story: 'M4 19V5a2 2 0 0 1 2-2h12v18H6a2 2 0 0 1-2-2zM8 7h6', hours: 'M12 22s7-6.5 7-12a7 7 0 0 0-14 0c0 5.5 7 12 7 12zM12 12a2 2 0 1 0 0-4 2 2 0 0 0 0 4',
        social: 'M18 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6M6 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6M18 22a3 3 0 1 0 0-6 3 3 0 0 0 0 6M8.6 13.5l6.8 4M15.4 6.5l-6.8 4',
        selection: 'M3 12 12 3h8v8l-9 9zM15.5 8.5h.01',
    };
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* État de l'éditeur : la vitrine (config), la couleur principale et l'adresse */
    let S = { config: @json($config), accent: @json($accent), slug: @json((string) $shop->slug) };
    let sel = 'products', panel = 'blocks', device = 'desktop';
    let past = [], future = [], dirty = false;

    const $ = id => document.getElementById(id);
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const svg = (d, s) => '<svg width="' + (s || 18) + '" height="' + (s || 18) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + d + '"/></svg>';
    const clone = o => JSON.parse(JSON.stringify(o));
    const blk = id => S.config.blocks.find(b => b.id === id);
    const imgUrl = p => p ? (IMG_URLS[p] || STORAGE + p.replace('/medium/', '/thumb/')) : '';
    const uid = () => 'b' + Math.random().toString(36).slice(2, 10);

    /* ───────────────────────── Historique : annuler / rétablir ───────────────────────── */
    function snapshot() { return JSON.stringify(S); }
    function commit(label, mutate) {
        past.push({ s: snapshot(), label: label });
        if (past.length > 60) past.shift();
        future = [];
        mutate();
        changed();
    }
    /* Saisie de texte : un seul point d'annulation par champ (au moment où on y entre) */
    let typingField = null;
    function beginTyping(key, label) {
        if (typingField === key) return;
        typingField = key;
        past.push({ s: snapshot(), label: label });
        future = [];
    }
    function undo() {
        if (!past.length) return;
        const last = past.pop();
        future.unshift({ s: snapshot(), label: last.label });
        S = JSON.parse(last.s); typingField = null;
        if (!blk(sel) && sel !== '__banner') sel = 'products';
        renderAll(); changed(); toast('Annulé : ' + last.label);
    }
    function redo() {
        if (!future.length) return;
        const next = future.shift();
        past.push({ s: snapshot(), label: next.label });
        S = JSON.parse(next.s); typingField = null;
        renderAll(); changed(); toast('Rétabli : ' + next.label);
    }
    $('vfUndo').addEventListener('click', undo);
    $('vfRedo').addEventListener('click', redo);
    document.addEventListener('keydown', e => {
        const tag = e.target.tagName;
        const typing = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z' && !typing) { e.preventDefault(); e.shiftKey ? redo() : undo(); }
        else if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y' && !typing) { e.preventDefault(); redo(); }
        else if (!typing && e.altKey && (e.key === 'ArrowUp' || e.key === 'ArrowDown') && blk(sel)) { e.preventDefault(); move(sel, e.key === 'ArrowUp' ? -1 : 1); }
        else if (!typing && e.key === 'Delete' && blk(sel) && !BLOCKS[blk(sel).type].single) { e.preventDefault(); removeBlock(sel); }
        else if (e.key === 'Escape' && !$('vfShare').hidden) closeShare();
    });

    /* ───────────────────────── Aperçu ───────────────────────── */
    const frame = $('vfFrame'), viewport = $('vfViewport'), stage = $('vfStage'), devEl = $('vfDevice');
    let timer = null, savedScroll = 0, frameReady = false, sending = null;
    function changed() {
        if (!dirty) { dirty = true; $('vf').classList.add('is-dirty'); }
        $('vfUndo').disabled = !past.length; $('vfRedo').disabled = !future.length;
        clearTimeout(timer);
        timer = setTimeout(refresh, 450);
    }
    async function refresh() {
        viewport.classList.add('is-loading');
        try {
            const res = await fetch(URLS.draft, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ config: S.config, accent: S.accent }) });
            if (!res.ok) { const j = await res.json().catch(() => ({})); viewport.classList.remove('is-loading'); toast(j.message || 'Aperçu impossible à mettre à jour.'); return; }
        } catch (e) { viewport.classList.remove('is-loading'); toast('Connexion perdue : l’aperçu n’a pas été mis à jour.'); return; }
        try { savedScroll = frame.contentWindow.scrollY || 0; } catch (e) { savedScroll = 0; }
        frameReady = false;
        frame.src = URLS.preview + '&t=' + Date.now();
    }
    frame.addEventListener('load', () => {
        viewport.classList.remove('is-loading');
        try { frame.contentWindow.scrollTo(0, savedScroll); } catch (e) {}
    });
    window.addEventListener('message', e => {
        if (e.origin !== location.origin || !e.data) return;
        if (e.data.type === 'sf-ready') { frameReady = true; highlight(false); }
        if (e.data.type === 'sf-select') { select(e.data.id, false); }
    });
    function highlight(scroll) { if (frameReady) frame.contentWindow.postMessage({ type: 'sf-highlight', id: sel, scroll: scroll }, location.origin); }

    function fit() {
        const W = device === 'phone' ? 390 : 1280, H = device === 'phone' ? 800 : 900;
        const pad = device === 'phone' ? 20 : 0, chrome = device === 'phone' ? 20 : 36;
        const availW = stage.clientWidth - 36, availH = stage.clientHeight - 36 - chrome;
        const scale = Math.min(1, (availW - pad) / W, device === 'phone' ? (availH - pad) / H : 1);
        const viewH = device === 'phone' ? H : Math.max(420, availH / scale);
        frame.style.width = W + 'px'; frame.style.height = viewH + 'px'; frame.style.transform = 'scale(' + scale + ')';
        viewport.style.width = (W * scale) + 'px'; viewport.style.height = (viewH * scale) + 'px';
        devEl.style.width = (W * scale + pad) + 'px';
    }
    document.querySelectorAll('[data-device]').forEach(b => b.addEventListener('click', () => {
        device = b.dataset.device;
        document.querySelectorAll('[data-device]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        if (!reduce) { devEl.classList.add('is-turning'); setTimeout(() => devEl.classList.remove('is-turning'), 380); }
        devEl.classList.toggle('is-phone', device === 'phone');
        fit();
    }));
    new ResizeObserver(fit).observe(stage);

    /* ───────────────────────── Blocs : ajout, déplacement, suppression ───────────────────────── */
    function blockName(b) { return BLOCKS[b.type].label; }
    function blockSub(b) {
        const d = b.d || {};
        if (b.type === 'announcement') return d.text || 'Texte à écrire';
        if (d.title) return d.title;
        if (b.type === 'hours') return d.addr || SHOP.address || 'Adresse à indiquer';
        if (b.type === 'selection') return d.category || 'Toute la boutique';
        return BLOCKS[b.type].desc;
    }
    function insert(type, at) {
        if (S.config.blocks.length >= LIMITS.blocks) return toast('Maximum ' + LIMITS.blocks + ' blocs sur la page.');
        const nb = { id: uid(), type: type, on: true, d: clone(DEFAULTS[type] || {}) };
        if (type === 'hours' && !nb.d.addr) nb.d.addr = SHOP.address;
        commit('Bloc ajouté : ' + BLOCKS[type].label, () => { S.config.blocks.splice(at, 0, nb); sel = nb.id; });
        renderAll(); highlightSoon();
    }
    function insertAfterSel(type) {
        const i = S.config.blocks.findIndex(b => b.id === sel);
        insert(type, i >= 0 ? i + 1 : S.config.blocks.length);
    }
    function move(id, dir) {
        const i = S.config.blocks.findIndex(b => b.id === id), j = i + dir;
        if (i < 0 || j < 0 || j >= S.config.blocks.length) return;
        commit('Bloc déplacé : ' + blockName(S.config.blocks[i]), () => { const a = S.config.blocks; [a[i], a[j]] = [a[j], a[i]]; });
        renderStructure(id); renderInspector();
    }
    function moveTo(id, at) {
        const from = S.config.blocks.findIndex(b => b.id === id);
        if (from < 0 || from === at || from + 1 === at) return;
        commit('Bloc déplacé : ' + blockName(S.config.blocks[from]), () => {
            const it = S.config.blocks.splice(from, 1)[0];
            S.config.blocks.splice(from < at ? at - 1 : at, 0, it);
        });
        renderStructure(id); renderInspector();
    }
    function duplicate(id) {
        const i = S.config.blocks.findIndex(b => b.id === id), b = S.config.blocks[i];
        if (!b || BLOCKS[b.type].single) return;
        const nb = { id: uid(), type: b.type, on: true, d: clone(b.d) };
        commit('Bloc dupliqué : ' + blockName(b), () => { S.config.blocks.splice(i + 1, 0, nb); sel = nb.id; });
        renderAll(); highlightSoon();
    }
    function removeBlock(id) {
        const b = blk(id);
        if (!b || BLOCKS[b.type].single) return;
        commit('Bloc supprimé : ' + blockName(b), () => { S.config.blocks = S.config.blocks.filter(x => x.id !== id); sel = 'products'; });
        renderAll(); toast('Bloc supprimé · Ctrl+Z pour annuler');
    }
    function toggleBlock(id) {
        const b = blk(id);
        if (!b || BLOCKS[b.type].trust) return;
        commit((b.on ? 'Bloc masqué : ' : 'Bloc affiché : ') + blockName(b), () => { b.on = !b.on; });
        renderStructure(); renderInspector();
    }
    function select(id, scroll) {
        if (id !== '__banner' && !blk(id)) return;
        sel = id; typingField = null;
        renderStructure(); renderInspector();
        highlight(scroll !== false);
        if (window.innerWidth < 1381) $('vfInspect').scrollIntoView({ block: 'nearest', behavior: reduce ? 'auto' : 'smooth' });
    }
    const highlightSoon = () => setTimeout(() => highlight(true), 900);

    /* ───────────────────────── Panneau : Blocs ───────────────────────── */
    let dragId = null, dragNew = null;
    function renderBlocksPanel() {
        const lib = Object.keys(BLOCKS).filter(t => !BLOCKS[t].single);
        return '<section class="vf-panel vf-panel-in"><h2>Structure de la page</h2>' +
            '<p class="vf-hint">Glissez pour réordonner. L’œil masque un bloc sans le perdre.</p>' +
            '<ol class="vf-tree" id="vfTree"></ol>' +
            '<div class="vf-drop-end" id="vfDropEnd">Déposez un bloc ici pour l’ajouter en bas de la page</div></section>' +
            '<section class="vf-panel"><h2>Ajouter un bloc</h2><p class="vf-hint">Glissez un bloc dans la structure, ou cliquez pour l’ajouter sous le bloc sélectionné.</p><div class="vf-lib">' +
            lib.map(t => '<button type="button" class="vf-libb" draggable="true" data-add="' + t + '"><span class="vf-node-ico">' + svg(ICON[t]) + '</span><span style="flex:1;min-width:0">' + esc(BLOCKS[t].label) + '<small>' + esc(BLOCKS[t].desc) + '</small></span><span class="vf-plus" aria-hidden="true">+</span></button>').join('') +
            '</div></section>' +
            '<section class="vf-panel"><h3>Toujours affichés</h3><p class="vf-hint">« Avis clients » et « Paiement et livraison » rassurent les clients sur toutes les boutiques Shopio : vous pouvez les déplacer, pas les masquer. Les avis n’apparaissent que s’ils viennent de vrais clients livrés.</p></section>' +
            '<section class="vf-panel vf-kbd-card"><h3>Raccourcis</h3><div class="vf-kbd"><span><kbd>Ctrl</kbd> + <kbd>Z</kbd> annuler · <kbd>Ctrl</kbd> + <kbd>Y</kbd> rétablir</span><span><kbd>Suppr</kbd> retirer le bloc sélectionné</span><span><kbd>Alt</kbd> + <kbd>↑</kbd> <kbd>↓</kbd> déplacer le bloc</span></div></section>';
    }
    function renderStructure(movedId) {
        const tree = $('vfTree');
        if (!tree) return;
        const n = S.config.blocks.length;
        const node = (b, i) => {
            const meta = BLOCKS[b.type];
            return '<li class="vf-node' + (sel === b.id ? ' is-sel' : '') + (b.on ? '' : ' is-off') + (movedId === b.id ? ' is-moved' : '') + '" draggable="true" data-id="' + b.id + '">' +
                '<span class="vf-node-ico">' + svg(ICON[b.type]) + '</span>' +
                '<button type="button" class="vf-node-txt" data-pick="' + b.id + '" style="border:0;background:none;padding:0;cursor:pointer;color:inherit"><span class="vf-node-name">' + esc(meta.label) + '</span><span class="vf-node-sub">' + esc(blockSub(b)) + '</span></button>' +
                (meta.trust ? '<span class="vf-node-lock" title="Toujours affiché">' + svg('M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4', 15) + '</span>'
                    : '<button type="button" class="vf-mini" data-toggle="' + b.id + '" aria-label="' + (b.on ? 'Masquer' : 'Afficher') + ' ' + esc(meta.label) + '" title="' + (b.on ? 'Masquer' : 'Afficher') + '">' + svg(b.on ? 'M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z' : 'M3 3l18 18M10.6 6.1A9.7 9.7 0 0 1 12 6c6 0 9.5 6 9.5 6a17 17 0 0 1-3 3.6M6.6 6.6A17 17 0 0 0 2.5 12S6 18.5 12 18.5a9 9 0 0 0 4.4-1.1', 16) + '</button>') +
                '<button type="button" class="vf-mini" data-up="' + b.id + '" aria-label="Monter ' + esc(meta.label) + '"' + (i === 0 ? ' disabled' : '') + '>' + svg('m6 15 6-6 6 6', 15) + '</button>' +
                '<button type="button" class="vf-mini" data-down="' + b.id + '" aria-label="Descendre ' + esc(meta.label) + '"' + (i === n - 1 ? ' disabled' : '') + '>' + svg('m6 9 6 6 6-6', 15) + '</button></li>';
        };
        tree.innerHTML = '<li class="vf-node' + (sel === '__banner' ? ' is-sel' : '') + '" data-banner="1" style="cursor:pointer"><span class="vf-node-ico">' + svg(ICON.__banner) + '</span><button type="button" class="vf-node-txt" data-pick="__banner" style="border:0;background:none;padding:0;cursor:pointer;color:inherit"><span class="vf-node-name">En-tête</span><span class="vf-node-sub">Toujours en haut · logo, nom, bannière</span></button></li>' +
            S.config.blocks.map(node).join('');
    }
    function bindBlocksPanel() {
        const tree = $('vfTree'), end = $('vfDropEnd');
        renderStructure();
        tree.addEventListener('click', e => {
            const t = e.target.closest('[data-pick],[data-toggle],[data-up],[data-down]');
            if (!t) return;
            if (t.dataset.pick) select(t.dataset.pick);
            else if (t.dataset.toggle) toggleBlock(t.dataset.toggle);
            else if (t.dataset.up) move(t.dataset.up, -1);
            else if (t.dataset.down) move(t.dataset.down, 1);
        });
        tree.addEventListener('dragstart', e => {
            const li = e.target.closest('[data-id]');
            if (!li) return;
            dragId = li.dataset.id; dragNew = null;
            try { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', dragId); } catch (x) {}
            li.classList.add('is-dragging');
        });
        tree.addEventListener('dragover', e => {
            const li = e.target.closest('[data-id]');
            if (!li || (!dragId && !dragNew)) return;
            e.preventDefault();
            tree.querySelectorAll('.is-over').forEach(x => x.classList.remove('is-over'));
            li.classList.add('is-over');
        });
        tree.addEventListener('drop', e => {
            const li = e.target.closest('[data-id]');
            if (!li) return;
            e.preventDefault();
            const at = S.config.blocks.findIndex(b => b.id === li.dataset.id);
            if (dragNew) insert(dragNew, at); else if (dragId) moveTo(dragId, at);
            dragEnd();
        });
        end.addEventListener('dragover', e => { if (dragId || dragNew) { e.preventDefault(); end.classList.add('is-over'); } });
        end.addEventListener('dragleave', () => end.classList.remove('is-over'));
        end.addEventListener('drop', e => { e.preventDefault(); if (dragNew) insert(dragNew, S.config.blocks.length); else if (dragId) moveTo(dragId, S.config.blocks.length); dragEnd(); });
        document.querySelectorAll('[data-add]').forEach(b => {
            b.addEventListener('click', () => insertAfterSel(b.dataset.add));
            b.addEventListener('dragstart', e => { dragNew = b.dataset.add; dragId = null; try { e.dataTransfer.effectAllowed = 'copy'; e.dataTransfer.setData('text/plain', dragNew); } catch (x) {} });
            b.addEventListener('dragend', dragEnd);
        });
        tree.addEventListener('dragend', dragEnd);
    }
    function dragEnd() {
        dragId = null; dragNew = null;
        document.querySelectorAll('.is-over,.is-dragging').forEach(x => x.classList.remove('is-over', 'is-dragging'));
    }

    /* ───────────────────────── Panneau : Style ───────────────────────── */
    function lum(h) { return [1, 3, 5].map(i => parseInt(h.substr(i, 2), 16) / 255).map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4)).reduce((a, v, i) => a + v * [0.2126, 0.7152, 0.0722][i], 0); }
    function ratio(a, b) { const x = lum(a), y = lum(b); return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05); }
    function inkFor(c) { return ratio(c, '#FFFFFF') >= 3 ? '#FFFFFF' : '#16162A'; }
    function setStyle(key, value, label) { commit(label, () => { S.config.style[key] = value; }); renderTools(); }
    function renderStylePanel() {
        const st = S.config.style, c = S.accent, ink = inkFor(c), bg = BG[st.bg];
        const btnCt = ratio(c, ink), titleOk = ratio(c, bg[0]) >= 3, ok = btnCt >= 3 && titleOk;
        const solid = st.fill === 'solid', outline = titleOk ? c : bg[1];
        const seg = (key, opts, label) => '<div class="vf-seg" role="group" aria-label="' + label + '">' + Object.entries(opts).map(([v, l]) => '<button type="button" data-style="' + key + '" data-value="' + v + '" aria-pressed="' + (st[key] === v) + '">' + l + '</button>').join('') + '</div>';
        return '<section class="vf-panel vf-panel-in"><h2>Couleur principale</h2>' +
            '<div class="vf-swatches" role="group" aria-label="Couleurs proposées">' + Object.entries(PRESETS).map(([hex, name]) => '<button type="button" class="vf-swatch" data-color="' + hex + '" aria-label="' + esc(name) + '" title="' + esc(name) + '" aria-pressed="' + (c.toUpperCase() === hex) + '" style="background:' + hex + '"></button>').join('') + '</div>' +
            '<label class="vf-color"><input type="color" id="vfColorFree" value="' + c + '"><span style="flex:1;font-weight:700">Couleur libre</span><span class="vf-num" style="font-size:13px;color:var(--vf-ink3)">' + c.toUpperCase() + '</span></label>' +
            '<div class="vf-ct" style="background:' + (ok ? 'var(--vf-ok50)' : 'var(--vf-amb50)') + '"><span class="vf-ct-aa" style="background:' + c + ';color:' + ink + '">Aa</span><div><div style="font-size:13px;font-weight:800;color:' + (ok ? 'var(--vf-ok)' : 'var(--vf-ambi)') + '">' + (ok ? 'Lisible partout' : (btnCt >= 3 ? 'Corrigé automatiquement' : 'Contraste faible')) + '</div><div style="font-size:12px;color:var(--vf-ink2)">' +
            (ok ? 'Texte des boutons : ' + (ink === '#FFFFFF' ? 'blanc' : 'foncé') + ' (contraste ' + btnCt.toFixed(1).replace('.', ',') + ':1).' : (btnCt >= 3 ? 'Trop clair pour les titres sur ce fond : Shopio les affiche en foncé.' : 'Choisissez une couleur plus foncée ou plus claire.')) + '</div></div></div></section>' +
            '<section class="vf-panel"><h2>Police</h2><div class="vf-tiles">' + Object.entries(FONTS).map(([k, f]) => '<button type="button" class="vf-tile" data-style="font" data-value="' + k + '" aria-pressed="' + (st.font === k) + '" style="font-family:' + esc(f.css) + '"><b>Aa</b><small>' + esc(f.label) + '</small></button>').join('') + '</div>' +
            '<span class="vf-lbl" style="margin:4px 0 0">Taille du texte</span>' + seg('size', { s: 'Petit', m: 'Normal', l: 'Grand' }, 'Taille du texte') + '</section>' +
            '<section class="vf-panel"><h2>Boutons</h2>' + seg('btn', { pill: 'Arrondis', square: 'Carrés' }, 'Forme des boutons') + seg('fill', { solid: 'Pleins', outline: 'Contour' }, 'Remplissage des boutons') +
            '<div class="vf-btnprev"><span class="vf-btn" style="background:' + (solid ? c : 'transparent') + ';color:' + (solid ? ink : outline) + ';box-shadow:' + (solid ? 'none' : 'inset 0 0 0 2px ' + outline) + ';border-radius:' + (st.btn === 'pill' ? '999px' : '6px') + ';font-family:' + esc(FONTS[st.font].css) + '">' + esc(S.config.settings.btn_text) + '</span></div></section>' +
            '<section class="vf-panel"><h2>Fond de page</h2><div class="vf-tiles vf-tiles-4">' +
            [['white', '#FFFFFF'], ['soft', '#F6F9FF'], ['texture', 'radial-gradient(rgba(11,30,61,.09) 1.2px, transparent 1.2px) 0 0 / 10px 10px #FBF8F3'], ['dark', '#0B1E3D']].map(([k, prev]) => '<button type="button" class="vf-tile" data-style="bg" data-value="' + k + '" aria-pressed="' + (st.bg === k) + '" aria-label="' + ({ white: 'Blanc', soft: 'Doux', texture: 'Texture', dark: 'Sombre' })[k] + '" style="background:' + prev + '"></button>').join('') +
            '</div><div class="vf-bglbl"><span>Blanc</span><span>Doux</span><span>Texture</span><span>Sombre</span></div></section>';
    }
    function bindStylePanel() {
        document.querySelectorAll('[data-color]').forEach(b => b.addEventListener('click', () => { commit('Couleur : ' + PRESETS[b.dataset.color], () => { S.accent = b.dataset.color; }); renderTools(); }));
        $('vfColorFree').addEventListener('change', e => { const v = e.target.value.toUpperCase(); commit('Couleur libre ' + v, () => { S.accent = v; }); renderTools(); });
        document.querySelectorAll('[data-style]').forEach(b => b.addEventListener('click', () => {
            const labels = { font: 'Police', size: 'Taille du texte', btn: 'Boutons', fill: 'Boutons', bg: 'Fond' };
            setStyle(b.dataset.style, b.dataset.value, labels[b.dataset.style] + ' : ' + (b.textContent.trim() || b.getAttribute('aria-label')));
        }));
    }

    /* ───────────────────────── Panneau : Thèmes ───────────────────────── */
    const sameStyle = t => t.color.toUpperCase() === S.accent.toUpperCase() && ['font', 'size', 'btn', 'fill', 'bg'].every(k => t[k] === S.config.style[k]);
    function applyTheme(style, name) {
        commit('Thème activé : ' + name, () => { S.accent = style.color.toUpperCase(); ['font', 'size', 'btn', 'fill', 'bg'].forEach(k => { S.config.style[k] = style[k]; }); });
        renderTools(); toast('Thème « ' + name + ' » appliqué (non publié)');
    }
    function renderThemesPanel() {
        const row = (t, key, saved) => {
            const act = sameStyle(t.style);
            return '<div class="vf-row' + (act ? ' is-active' : '') + '"><span class="vf-theme-sw"><span style="background:' + (BG[t.style.bg] || BG.soft)[0] + '"></span><span style="background:' + t.style.color + '"></span></span>' +
                '<div class="vf-row-txt"><b style="font-family:' + esc(FONTS[t.style.font].css) + '">' + esc(t.name) + '</b><span>' + esc(t.desc || ('Votre thème · ' + FONTS[t.style.font].label.toLowerCase())) + '</span></div>' +
                (act ? '<span class="vf-chip" style="background:var(--vf-ok50);color:var(--vf-ok)">Actif</span>' : '<button type="button" class="vf-btn vf-btn-line vf-btn-sm" data-theme-apply="' + key + '">Activer</button>') +
                (saved ? '<button type="button" class="vf-mini" data-theme-del="' + key + '" aria-label="Supprimer le thème ' + esc(t.name) + '">' + svg('M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3', 15) + '</button>' : '') + '</div>';
        };
        const builtIn = Object.entries(THEMES).map(([k, t]) => row(t, 'b:' + k, false)).join('');
        const saved = S.config.themes.map(t => row(t, 's:' + t.id, true)).join('');
        return '<section class="vf-panel vf-panel-in"><h2>Thèmes</h2><p class="vf-hint">Préparez une apparence par occasion et activez-la d’un clic. Rien n’est visible des clients avant « Publier ».</p>' +
            '<div style="display:flex;flex-direction:column;gap:8px">' + builtIn + saved + '</div>' +
            '<form id="vfThemeForm" style="display:flex;gap:8px"><label style="flex:1"><span class="vf-sr">Nom du thème</span><input class="vf-input" id="vfThemeName" maxlength="40" placeholder="Ex. Fête de l’indépendance"></label><button type="submit" class="vf-btn vf-btn-blue">Enregistrer</button></form>' +
            '<p class="vf-hint" style="font-size:12px">Enregistre la couleur, la police, les boutons et le fond actuels (' + S.config.themes.length + ' / ' + LIMITS.themes + ').</p></section>' +
            '<section class="vf-panel"><h2>Mise en page de départ</h2><p class="vf-hint">Réordonne les blocs de base et règle l’affichage des produits selon votre activité.</p><div class="vf-templates">' +
            Object.entries(TEMPLATES).map(([k, t]) => '<button type="button" class="vf-tpl" data-template="' + k + '" style="--tc:' + t.accent + '"><span class="vf-tpl-dot" aria-hidden="true"></span><b>' + esc(t.label) + '</b><small>' + esc(t.hint) + '</small></button>').join('') + '</div></section>' +
            '<section class="vf-panel"><h2>Historique des publications</h2>' +
            (HISTORY.length ? '<div style="display:flex;flex-direction:column;gap:4px">' + HISTORY.map((h, i) => '<div class="vf-row" style="padding:8px 10px' + (i === 0 ? ';background:var(--vf-b50)' : '') + '"><span class="vf-dot" style="background:' + (i === 0 ? 'var(--vf-blue)' : '#C3CBDA') + '"></span><div class="vf-row-txt"><b style="font-size:13px">' + (i === 0 ? 'Version en ligne' : 'Publication') + '</b><span class="vf-num">' + esc(h.at) + '</span></div>' + (i === 0 ? '' : '<button type="button" class="vf-btn vf-btn-line vf-btn-sm" data-restore="' + i + '">Restaurer</button>') + '</div>').join('') + '</div>'
                : '<p class="vf-hint">Vos publications apparaîtront ici : vous pourrez revenir à une version précédente.</p>') + '</section>';
    }
    function bindThemesPanel() {
        document.querySelectorAll('[data-theme-apply]').forEach(b => b.addEventListener('click', () => {
            const [kind, key] = b.dataset.themeApply.split(':');
            const t = kind === 'b' ? THEMES[key] : S.config.themes.find(x => x.id === key);
            if (t) applyTheme(t.style, t.name);
        }));
        document.querySelectorAll('[data-theme-del]').forEach(b => b.addEventListener('click', () => {
            const id = b.dataset.themeDel.split(':')[1], t = S.config.themes.find(x => x.id === id);
            commit('Thème supprimé : ' + (t ? t.name : ''), () => { S.config.themes = S.config.themes.filter(x => x.id !== id); });
            renderTools();
        }));
        $('vfThemeForm').addEventListener('submit', e => {
            e.preventDefault();
            const name = $('vfThemeName').value.trim();
            if (!name) return toast('Donnez un nom au thème');
            if (S.config.themes.length >= LIMITS.themes) return toast('Maximum ' + LIMITS.themes + ' thèmes enregistrés');
            commit('Thème enregistré : ' + name, () => { S.config.themes.push({ id: 't' + Math.random().toString(36).slice(2, 10), name: name, style: Object.assign({ color: S.accent }, S.config.style) }); });
            renderTools(); toast('Thème « ' + name + ' » enregistré (publié avec la boutique)');
        });
        document.querySelectorAll('[data-template]').forEach(b => b.addEventListener('click', () => {
            const t = TEMPLATES[b.dataset.template];
            commit('Mise en page : ' + t.label, () => {
                const slots = S.config.blocks.map((x, i) => CORE.includes(x.type) ? i : -1).filter(i => i >= 0);
                const ordered = t.order.map(k => S.config.blocks.find(x => x.type === k)).filter(Boolean);
                slots.forEach((slot, n) => { S.config.blocks[slot] = ordered[n]; });
                S.config.blocks.forEach(x => { if (CORE.includes(x.type) && !BLOCKS[x.type].trust) x.on = !t.off.includes(x.type); });
                Object.assign(S.config, { layout: t.layout, columns: t.columns, sort: t.sort, cards: t.cards, banner: t.banner });
                S.accent = t.accent;
            });
            renderAll(); toast('Mise en page « ' + t.label + ' » appliquée (non publiée)');
        }));
        document.querySelectorAll('[data-restore]').forEach(b => b.addEventListener('click', () => {
            const h = HISTORY[+b.dataset.restore];
            commit('Version restaurée du ' + h.at, () => { S.config = clone(h.config); S.accent = h.accent; });
            sel = 'products'; renderAll(); toast('Version du ' + h.at + ' restaurée (non publiée)');
        }));
    }

    /* ───────────────────────── Panneau : Réglages ───────────────────────── */
    let slugTimer = null, slugState = { ok: true, message: '' };
    function renderSettingsPanel() {
        const set = S.config.settings;
        const sw = (key, on, label, amber) => '<button type="button" class="vf-sw' + (amber ? ' is-amber' : '') + '" role="switch" aria-checked="' + !!on + '" aria-label="' + esc(label) + '" data-set-toggle="' + key + '"><span></span></button>';
        const done = CHECKLIST.filter(c => c.key === 'featured' ? (S.config.featured_ids.length || c.flagged) : (c.key === 'slug' ? !!S.slug : c.done)).length;
        const pct = Math.round(done / Math.max(1, CHECKLIST.length) * 100);
        return '<section class="vf-panel vf-panel-in" id="reglages"><h2>Adresse de la boutique</h2>' +
            '<div class="vf-slug"><span>' + esc(SHOP.slugBase.replace(/^https?:\/\//, '')) + '</span><input id="vfSlug" value="' + esc(S.slug) + '" maxlength="40" aria-label="Adresse de la boutique" placeholder="nom-de-ma-boutique" autocomplete="off"></div>' +
            '<span class="vf-msg ' + (S.slug ? (slugState.ok ? 'is-ok' : 'is-err') : '') + '" id="vfSlugMsg">' + esc(S.slug ? slugState.message : 'Facultatif · sans adresse, le lien reste ' + SHOP.defaultUrl.replace(/^https?:\/\//, '')) + '</span></section>' +
            '<section class="vf-panel"><h2>Texte des boutons</h2><input class="vf-input" id="vfBtnText" maxlength="20" value="' + esc(set.btn_text) + '" aria-label="Texte du bouton d’achat">' +
            '<div style="display:flex;gap:6px;flex-wrap:wrap">' + BTN_SUGG.map(s => '<button type="button" class="vf-btn vf-btn-line vf-btn-sm" data-btn-sugg="' + esc(s) + '">' + esc(s) + '</button>').join('') + '</div></section>' +
            '<section class="vf-panel"><h2>Fiches produit</h2>' +
            [['show_stock', 'Stock restant', 'Affiche « plus que N en stock » quand le stock est bas'], ['show_strike', 'Prix barré', 'Seulement si vous indiquez un ancien prix'], ['show_nego', 'Bouton « Négocier le prix »', 'Le client propose un prix dans le chat']].map(([k, l, dsc]) => '<div class="vf-row"><div class="vf-row-txt"><b>' + l + '</b><span>' + dsc + '</span></div>' + sw(k, set[k], l) + '</div>').join('') + '</section>' +
            '<section class="vf-panel"><div style="display:flex;align-items:center;gap:10px"><h2 style="flex:1">Mode vacances</h2>' + sw('vacation', set.vacation, 'Mode vacances', true) + '</div>' +
            '<p class="vf-hint">La boutique reste visible, mais les commandes sont en pause (vitrine, fiches produit et panier). Vos produits ne sont pas perdus.</p>' +
            '<input class="vf-input" id="vfVacMsg" maxlength="120" value="' + esc(set.vacation_msg) + '" aria-label="Message de vacances" placeholder="Ex. De retour le 5 novembre"></section>' +
            '<section class="vf-panel"><div style="display:flex;align-items:center;gap:10px"><h2 style="flex:1">Message de bienvenue</h2>' + sw('welcome', set.welcome, 'Message de bienvenue', false) + '</div>' +
            '<p class="vf-hint">S’affiche une seule fois, à la première visite d’un client, avec un de vos codes promo.</p>' +
            (PROMOS.length ? '<label class="vf-field"><span class="vf-lbl">Code offert</span><select class="vf-select" id="vfWelcomePromo"><option value="">Choisir un code…</option>' + PROMOS.map(p => '<option value="' + p.id + '"' + (+set.welcome_promo_id === p.id ? ' selected' : '') + '>' + esc(p.label) + '</option>').join('') + '</select></label>' +
                '<button type="button" class="vf-btn vf-btn-line" id="vfShowWelcome"' + (set.welcome && set.welcome_promo_id ? '' : ' disabled') + '>Voir le message</button>'
                : '<div class="vf-note">Créez d’abord un code dans <a href="' + esc(@json(route('boutique.promo-codes.index'))) + '">Codes promo</a> (plan Pro).</div>') + '</section>' +
            '<section class="vf-panel"><div class="vf-quality-hd"><span class="vf-ring' + (pct === 100 ? ' is-full' : '') + '" style="--p:' + pct + '" aria-hidden="true"><b class="vf-num">' + pct + '</b>%</span><div><h2>Qualité de la boutique</h2><p class="vf-hint">' + done + ' sur ' + CHECKLIST.length + ' points complétés</p></div></div>' +
            '<ul class="vf-checks">' + CHECKLIST.map(c => { const ok = c.key === 'featured' ? (S.config.featured_ids.length || c.flagged) : (c.key === 'slug' ? !!S.slug : c.done); return '<li class="vf-check' + (ok ? ' is-done' : '') + '"><span class="vf-check-ico" aria-hidden="true">' + svg('m5 12.5 4.5 4.5L19 7.5', 12) + '</span><span class="vf-check-lbl">' + esc(c.label) + '<span class="vf-sr">' + (ok ? ' : fait' : ' : à faire') + '</span></span><a class="vf-check-cta" href="' + esc(c.url) + '" data-check-link>' + esc(c.cta) + '</a></li>'; }).join('') + '</ul></section>' +
            '<section class="vf-panel"><h3>Recommencer à zéro</h3><p class="vf-hint">Remet les blocs, le style et les réglages par défaut (la version en ligne est remplacée).</p><button type="button" class="vf-btn vf-btn-danger" id="vfReset">Réinitialiser la boutique</button></section>';
    }
    function setSetting(key, value, label) { commit(label, () => { S.config.settings[key] = value; }); }
    function bindSettingsPanel() {
        const slug = $('vfSlug');
        slug.addEventListener('focus', () => beginTyping('slug', 'Adresse modifiée'));
        slug.addEventListener('input', () => {
            const v = slug.value.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9-]+/g, '-').replace(/^-+/, '').slice(0, 40);
            if (v !== slug.value) slug.value = v;
            S.slug = v; dirtyOnly();
            clearTimeout(slugTimer);
            const msg = $('vfSlugMsg');
            if (!v) { slugState = { ok: true, message: '' }; msg.className = 'vf-msg'; msg.textContent = 'Facultatif'; return; }
            msg.className = 'vf-msg'; msg.textContent = 'Vérification…';
            slugTimer = setTimeout(async () => {
                try {
                    const r = await fetch(URLS.slug + '?slug=' + encodeURIComponent(v), { headers: { 'Accept': 'application/json' } });
                    const j = await r.json();
                    slugState = { ok: j.ok, message: j.message };
                    msg.className = 'vf-msg ' + (j.ok ? 'is-ok' : 'is-err');
                    msg.textContent = j.ok ? 'Adresse disponible : ' + (j.url || '').replace(/^https?:\/\//, '') : j.message;
                } catch (e) { msg.textContent = 'Vérification impossible pour le moment.'; }
            }, 400);
        });
        const btnText = $('vfBtnText');
        btnText.addEventListener('focus', () => beginTyping('btn_text', 'Texte du bouton modifié'));
        btnText.addEventListener('input', () => { S.config.settings.btn_text = btnText.value; changed(); });
        document.querySelectorAll('[data-btn-sugg]').forEach(b => b.addEventListener('click', () => { setSetting('btn_text', b.dataset.btnSugg, 'Texte du bouton : ' + b.dataset.btnSugg); renderTools(); }));
        document.querySelectorAll('[data-set-toggle]').forEach(b => b.addEventListener('click', () => {
            const k = b.dataset.setToggle, on = !S.config.settings[k];
            if (k === 'welcome' && on && !S.config.settings.welcome_promo_id && !PROMOS.length) return toast('Créez d’abord un code promo pour le message de bienvenue.');
            setSetting(k, on, b.getAttribute('aria-label') + (on ? ' activé' : ' désactivé'));
            renderTools();
            if (k === 'vacation') toast(on ? 'Mode vacances activé · commandes en pause après publication' : 'Boutique rouverte après publication');
        }));
        const vac = $('vfVacMsg');
        vac.addEventListener('focus', () => beginTyping('vac', 'Message de vacances modifié'));
        vac.addEventListener('input', () => { S.config.settings.vacation_msg = vac.value; changed(); });
        $('vfWelcomePromo')?.addEventListener('change', e => { setSetting('welcome_promo_id', e.target.value ? +e.target.value : null, 'Code de bienvenue choisi'); renderTools(); });
        $('vfShowWelcome')?.addEventListener('click', () => {
            if (frameReady) frame.contentWindow.postMessage({ type: 'sf-welcome' }, location.origin);
        });
        document.querySelectorAll('[data-check-link]').forEach(a => a.addEventListener('click', e => {
            const href = a.getAttribute('href');
            if (href === '#bloc-featured') { e.preventDefault(); showPanel('blocks'); select('featured'); }
            else if (href === '#reglages') { e.preventDefault(); $('vfSlug').focus(); }
        }));
        let armed = null;
        $('vfReset').addEventListener('click', e => {
            const b = e.currentTarget;
            if (b.dataset.armed) { dirty = false; $('vfResetForm').submit(); return; }
            b.dataset.armed = '1'; b.textContent = 'Confirmer la réinitialisation';
            clearTimeout(armed); armed = setTimeout(() => { delete b.dataset.armed; b.textContent = 'Réinitialiser la boutique'; }, 4000);
        });
    }
    function dirtyOnly() { if (!dirty) { dirty = true; $('vf').classList.add('is-dirty'); } $('vfUndo').disabled = !past.length; }

    /* ───────────────────────── Bloc sélectionné (inspecteur) ───────────────────────── */
    const FIELDS = {
        text: [['title', 'Titre', 'text', 80], ['body', 'Texte', 'area', 1500]],
        story: [['title', 'Titre', 'text', 80], ['body', 'Votre histoire', 'area', 1500]],
        imgtext: [['title', 'Titre', 'text', 80], ['body', 'Texte', 'area', 1200]],
        video: [['title', 'Titre', 'text', 80], ['url', 'Lien YouTube ou TikTok', 'url', 300, 'https://www.tiktok.com/@…']],
        gallery: [['title', 'Titre', 'text', 80]],
        countdown: [['title', 'Titre', 'text', 80], ['sub', 'Offre', 'text', 140, 'Ex. −10 % sur les montres'], ['end', 'Fin de la promotion', 'datetime-local']],
        faq: [['title', 'Titre', 'text', 80]],
        hours: [['addr', 'Adresse', 'text', 160], ['days', 'Jours d’ouverture', 'text', 60], ['open', 'Ouverture', 'time'], ['close', 'Fermeture', 'time']],
        social: [['wa', 'WhatsApp', 'tel', 20, '+224 6.. .. .. ..'], ['fb', 'Facebook', 'text', 120, 'facebook.com/…'], ['tt', 'TikTok', 'text', 120, '@…'], ['ig', 'Instagram', 'text', 120, '@…']],
        selection: [['title', 'Titre', 'text', 80]],
        announcement: [['text', 'Texte du bandeau', 'text', LIMITS.ann, 'Ex. Livraison offerte ce week-end à Conakry']],
    };
    function field(b, f) {
        const [key, label, type, max, ph] = f, v = b.d[key] == null ? '' : b.d[key];
        const id = 'vfF-' + key;
        const input = type === 'area'
            ? '<textarea class="vf-area" id="' + id + '" data-field="' + key + '" maxlength="' + max + '" placeholder="' + esc(ph || '') + '">' + esc(v) + '</textarea>'
            : '<input class="vf-input" id="' + id + '" type="' + type + '" data-field="' + key + '"' + (max ? ' maxlength="' + max + '"' : '') + ' value="' + esc(v) + '" placeholder="' + esc(ph || (key === 'addr' ? SHOP.address : '')) + '">';
        return '<label class="vf-field" for="' + id + '"><span class="vf-lbl">' + label + '</span>' + input + (key === 'text' && b.type === 'announcement' ? '<span class="vf-chars"><span id="vfAnnN">' + v.length + '</span> / ' + max + '</span>' : '') + '</label>';
    }
    function segField(key, opts, value, label) {
        return '<div><span class="vf-lbl">' + label + '</span><div class="vf-seg" role="group" aria-label="' + label + '">' + opts.map(([v, l]) => '<button type="button" data-seg="' + key + '" data-value="' + v + '" aria-pressed="' + (String(value) === String(v)) + '">' + l + '</button>').join('') + '</div></div>';
    }
    function renderInspector() {
        const box = $('vfInspect');
        if (sel === '__banner') {
            box.innerHTML = '<section class="vf-panel vf-panel-in">' + inspHead('__banner', 'En-tête', 'Toujours en haut de la boutique') +
                segField('cfg:banner', [['large', 'Grande'], ['compact', 'Compacte'], ['none', 'Sans']], S.config.banner, 'Bannière') +
                '<div class="vf-note">Le logo, le nom et la description se changent dans <a href="' + esc(URLS.shopEdit) + '">Ma boutique</a>. Le badge « Boutique vérifiée » est attribué par Shopio.</div></section>';
            bindInspector(); return;
        }
        const b = blk(sel);
        if (!b) { box.innerHTML = '<section class="vf-panel"><div class="vf-empty"><strong>Aucun bloc sélectionné</strong>Cliquez sur un bloc de la page pour le modifier.</div></section>'; return; }
        const meta = BLOCKS[b.type];
        let body = '';
        if (FIELDS[b.type]) body += FIELDS[b.type].map(f => field(b, f)).join('');
        switch (b.type) {
            case 'products':
                body += segField('cfg:layout', [['grid', 'Grille'], ['list', 'Liste'], ['carousel', 'Carrousel']], S.config.layout, 'Affichage') +
                    segField('cfg:columns', [[2, '2 col.'], [3, '3 col.'], [4, '4 col.']], S.config.columns, 'Colonnes sur ordinateur') +
                    '<label class="vf-field"><span class="vf-lbl">Ordre</span><select class="vf-select" data-cfg="sort">' + [['featured', 'Mis en avant'], ['recent', 'Plus récents'], ['price_asc', 'Prix croissant'], ['price_desc', 'Prix décroissant']].map(([v, l]) => '<option value="' + v + '"' + (S.config.sort === v ? ' selected' : '') + '>' + l + '</option>').join('') + '</select></label>' +
                    segField('cfg:cards', [['rounded', 'Arrondies'], ['square', 'Droites']], S.config.cards, 'Forme des cartes') +
                    '<p class="vf-hint">' + PRODUCTS.length + ' produit' + (PRODUCTS.length > 1 ? 's' : '') + ' en ligne · toujours 2 colonnes sur téléphone. <a href="' + esc(URLS.productNew) + '">Ajouter un produit</a></p>';
                break;
            case 'featured':
                body += '<p class="vf-hint">Choisissez jusqu’à ' + LIMITS.featured + ' produits, dans l’ordre d’affichage. Sans choix, c’est le dernier produit « mis en avant » qui s’affiche.</p>' +
                    (PRODUCTS.length ? '<input type="search" class="vf-input" id="vfFeatSearch" placeholder="Rechercher un produit…" aria-label="Rechercher un produit"><ul class="vf-picks" id="vfPicks">' + PRODUCTS.map(p => { const r = S.config.featured_ids.indexOf(p.id); return '<li data-name="' + esc(p.name.toLowerCase()) + '"><button type="button" class="vf-pick' + (r >= 0 ? ' is-on' : '') + '" data-pick-prod="' + p.id + '" aria-pressed="' + (r >= 0) + '"><span class="vf-pick-img">' + (p.thumb ? '<img src="' + esc(p.thumb) + '" alt="" loading="lazy">' : '') + '</span><span class="vf-pick-txt"><span class="vf-pick-name">' + esc(p.name) + '</span><span class="vf-pick-price vf-num">' + esc(p.price) + (p.featured ? ' · mis en avant' : '') + '</span></span><span class="vf-pick-rank">' + (r >= 0 ? r + 1 : '') + '</span></button></li>'; }).join('') + '</ul>'
                        : '<div class="vf-note">Aucun produit en ligne. <a href="' + esc(URLS.productNew) + '">Ajouter un produit</a></div>');
                break;
            case 'imgtext':
                body += '<div><span class="vf-lbl">Photo</span>' + (b.d.img ? '<div class="vf-photo"><img src="' + esc(imgUrl(b.d.img)) + '" alt=""><button type="button" class="vf-photo-x" data-img-del aria-label="Retirer la photo">×</button></div>' : '<button type="button" class="vf-photo vf-upload" data-upload="img">' + svg(ICON.imgtext, 26) + 'Ajouter une photo</button>') + '</div>' +
                    segField('d:side', [['left', 'À gauche'], ['right', 'À droite']], b.d.side, 'Position de la photo');
                break;
            case 'gallery':
                body += '<div><span class="vf-lbl">Photos · ' + b.d.imgs.length + ' / ' + LIMITS.gallery + '</span><div class="vf-photos">' + b.d.imgs.map((p, i) => '<div class="vf-photo"><img src="' + esc(imgUrl(p)) + '" alt=""><button type="button" class="vf-photo-x" data-gal-del="' + i + '" aria-label="Retirer la photo ' + (i + 1) + '">×</button></div>').join('') +
                    (b.d.imgs.length < LIMITS.gallery ? '<button type="button" class="vf-photo vf-upload" data-upload="gallery" aria-label="Ajouter des photos">+</button>' : '') + '</div></div>';
                break;
            case 'faq':
                body += b.d.items.map((it, i) => '<div class="vf-faq-item"><label class="vf-field"><span class="vf-lbl">Question ' + (i + 1) + '</span><input class="vf-input" data-faq="' + i + '" data-k="q" maxlength="140" value="' + esc(it.q) + '"></label><label class="vf-field"><span class="vf-lbl">Réponse</span><textarea class="vf-area" data-faq="' + i + '" data-k="a" maxlength="600">' + esc(it.a) + '</textarea></label><button type="button" class="vf-btn vf-btn-danger vf-btn-sm" data-faq-del="' + i + '">Retirer la question</button></div>').join('') +
                    (b.d.items.length < LIMITS.faq ? '<button type="button" class="vf-btn vf-btn-line" data-faq-add>Ajouter une question</button>' : '') +
                    '<p class="vf-hint">Une question sans réponse n’est pas affichée aux clients.</p>';
                break;
            case 'selection':
                body += '<label class="vf-field"><span class="vf-lbl">Catégorie</span><select class="vf-select" data-dsel="category"><option value="">Toute la boutique</option>' + CATEGORIES.map(c => '<option value="' + esc(c) + '"' + (b.d.category === c ? ' selected' : '') + '>' + esc(c) + '</option>').join('') + '</select></label>' +
                    segField('d:limit', [[4, '4 produits'], [8, '8 produits']], b.d.limit, 'Nombre de produits');
                break;
            case 'countdown':
                body += '<p class="vf-hint">Le compte à rebours disparaît tout seul à la fin de la promotion.</p>';
                break;
            case 'video':
                body += '<p class="vf-hint">YouTube se lit dans la page ; TikTok et Facebook ouvrent la vidéo dans l’application.</p>';
                break;
            case 'social':
                body += '<p class="vf-hint">Les réseaux sans lien ne sont pas affichés aux clients.</p>';
                break;
            case 'categories':
                body += '<div class="vf-note">Les catégories viennent de vos produits. Le bloc apparaît dès qu’il y en a au moins deux.</div>' +
                    (CAT_LINKS.length
                        ? '<div><span class="vf-lbl">Lien de chaque catégorie</span><p class="vf-hint" style="margin-bottom:8px">Prêt à partager, rien à régler : le client arrive directement sur les produits de la catégorie.</p><div class="vf-catlinks">' +
                          CAT_LINKS.map((c, i) => '<div class="vf-catlink"><div><b>' + esc(c.name) + '</b><span>' + c.count + ' produit' + (c.count > 1 ? 's' : '') + '</span></div><button type="button" class="vf-btn vf-btn-line vf-btn-sm" data-share-open="' + i + '">Partager</button></div>').join('') + '</div></div>'
                        : '<p class="vf-hint">Indiquez une catégorie sur vos produits (ex. « Montres ») : chacune aura son lien à partager.</p>');
                break;
            case 'contact': body += '<div class="vf-note">Téléphone, adresse et e-mail viennent de <a href="' + esc(URLS.shopEdit) + '?etape=2">Ma boutique</a>.</div>'; break;
            case 'reviews': body += '<div class="vf-note">Ce bloc est géré par Shopio pour la confiance des clients : vous pouvez le déplacer, pas le modifier ni le masquer. Seuls les avis de vrais clients livrés sont affichés.</div>'; break;
            case 'delivery': body += '<div class="vf-note">Ce bloc est géré par Shopio pour la confiance des clients : vous pouvez le déplacer, pas le modifier ni le masquer.</div>'; break;
        }
        const actions = meta.single
            ? (meta.trust ? '' : '<button type="button" class="vf-btn vf-btn-line" data-act="toggle">' + (b.on ? 'Masquer ce bloc' : 'Afficher ce bloc') + '</button>')
            : '<div style="display:flex;gap:8px;flex-wrap:wrap"><button type="button" class="vf-btn vf-btn-line" data-act="dup">Dupliquer</button><button type="button" class="vf-btn vf-btn-danger" data-act="del">Supprimer ce bloc</button></div>';
        box.innerHTML = '<section class="vf-panel vf-panel-in">' + inspHead(b.type, meta.label, b.on ? meta.desc : 'Masqué : les clients ne le voient pas') + body + actions + '</section>';
        bindInspector();
    }
    function inspHead(type, name, sub) {
        return '<div class="vf-ins-hd"><span class="vf-ins-ico">' + svg(ICON[type]) + '</span><div style="flex:1;min-width:0"><span class="vf-lbl" style="margin:0">Bloc sélectionné</span><h2>' + esc(name) + '</h2><span class="vf-hint" style="font-size:12px">' + esc(sub) + '</span></div></div>';
    }
    function bindInspector() {
        const box = $('vfInspect'), b = blk(sel);
        box.querySelectorAll('[data-field]').forEach(inp => {
            const key = inp.dataset.field;
            const instant = inp.type === 'datetime-local' || inp.type === 'time';
            inp.addEventListener('focus', () => { if (!instant) beginTyping(sel + ':' + key, 'Texte modifié : ' + BLOCKS[b.type].label); });
            inp.addEventListener(instant ? 'change' : 'input', () => {
                if (instant) commit(BLOCKS[b.type].label + ' modifié', () => { b.d[key] = inp.value; });
                else { b.d[key] = inp.value; changed(); }
                if (key === 'text' && $('vfAnnN')) $('vfAnnN').textContent = inp.value.length;
                updateTreeSub(b);
            });
        });
        box.querySelectorAll('[data-seg]').forEach(btn => btn.addEventListener('click', () => {
            const [scope, key] = btn.dataset.seg.split(':'), raw = btn.dataset.value, v = /^\d+$/.test(raw) ? +raw : raw;
            commit((scope === 'cfg' ? '' : BLOCKS[b.type].label + ' : ') + btn.textContent.trim(), () => { if (scope === 'cfg') S.config[key] = v; else b.d[key] = v; });
            renderInspector();
        }));
        box.querySelector('[data-cfg="sort"]')?.addEventListener('change', e => { commit('Ordre des produits', () => { S.config.sort = e.target.value; }); });
        box.querySelector('[data-dsel]')?.addEventListener('change', e => { commit('Sélection : ' + (e.target.value || 'toute la boutique'), () => { b.d.category = e.target.value; }); updateTreeSub(b); });
        box.querySelector('[data-act="toggle"]')?.addEventListener('click', () => toggleBlock(b.id));
        box.querySelector('[data-act="dup"]')?.addEventListener('click', () => duplicate(b.id));
        box.querySelector('[data-act="del"]')?.addEventListener('click', () => removeBlock(b.id));
        // Produits à la une
        box.querySelector('#vfPicks')?.addEventListener('click', e => {
            const btn = e.target.closest('[data-pick-prod]'); if (!btn) return;
            const id = +btn.dataset.pickProd, at = S.config.featured_ids.indexOf(id);
            if (at < 0 && S.config.featured_ids.length >= LIMITS.featured) return toast('Maximum ' + LIMITS.featured + ' produits : retirez-en un pour en choisir un autre.');
            commit(at >= 0 ? 'Produit retiré de la une' : 'Produit mis à la une', () => { at >= 0 ? S.config.featured_ids.splice(at, 1) : S.config.featured_ids.push(id); });
            const q = $('vfFeatSearch').value; renderInspector(); $('vfFeatSearch').value = q; filterPicks(q);
        });
        box.querySelector('#vfFeatSearch')?.addEventListener('input', e => filterPicks(e.target.value));
        // Questions fréquentes
        box.querySelectorAll('[data-faq]').forEach(inp => {
            inp.addEventListener('focus', () => beginTyping(sel + ':faq' + inp.dataset.faq + inp.dataset.k, 'Question modifiée'));
            inp.addEventListener('input', () => { b.d.items[+inp.dataset.faq][inp.dataset.k] = inp.value; changed(); });
        });
        box.querySelector('[data-faq-add]')?.addEventListener('click', () => { commit('Question ajoutée', () => { b.d.items.push({ q: '', a: '' }); }); renderInspector(); });
        box.querySelectorAll('[data-faq-del]').forEach(x => x.addEventListener('click', () => { commit('Question retirée', () => { b.d.items.splice(+x.dataset.faqDel, 1); }); renderInspector(); }));
        // Photos
        box.querySelector('[data-img-del]')?.addEventListener('click', () => { commit('Photo retirée', () => { b.d.img = null; }); renderInspector(); });
        box.querySelectorAll('[data-gal-del]').forEach(x => x.addEventListener('click', () => { commit('Photo retirée de la galerie', () => { b.d.imgs.splice(+x.dataset.galDel, 1); }); renderInspector(); }));
        box.querySelectorAll('[data-upload]').forEach(x => x.addEventListener('click', () => pickImages(b, x)));
        box.querySelectorAll('[data-cat-copy]').forEach(x => x.addEventListener('click', async () => {
            try { await navigator.clipboard.writeText(x.dataset.catCopy); x.textContent = 'Copié'; } catch (e) { window.prompt('Copiez ce lien :', x.dataset.catCopy); }
            setTimeout(() => { x.textContent = 'Copier'; }, 2000);
        }));
    }
    function filterPicks(q) {
        q = (q || '').trim().toLowerCase();
        document.querySelectorAll('#vfPicks li').forEach(li => { li.hidden = q && !li.dataset.name.includes(q); });
    }
    function updateTreeSub(b) {
        const el = document.querySelector('.vf-node[data-id="' + b.id + '"] .vf-node-sub');
        if (el) el.textContent = blockSub(b);
    }
    function pickImages(b, btn) {
        const input = document.createElement('input');
        input.type = 'file'; input.accept = 'image/jpeg,image/png,image/webp';
        input.multiple = btn.dataset.upload === 'gallery';
        input.addEventListener('change', async () => {
            const room = btn.dataset.upload === 'gallery' ? LIMITS.gallery - b.d.imgs.length : 1;
            const files = [...input.files].slice(0, room);
            if (!files.length) return;
            btn.classList.add('is-busy'); btn.textContent = 'Envoi…';
            const paths = [];
            for (const f of files) {
                const fd = new FormData(); fd.append('file', f); fd.append('_token', CSRF);
                try {
                    const r = await fetch(URLS.image, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
                    const j = await r.json();
                    if (!r.ok || !j.path) { toast((j.errors && j.errors.file && j.errors.file[0]) || j.message || 'Photo refusée.'); continue; }
                    IMG_URLS[j.path] = j.url; paths.push(j.path);
                } catch (e) { toast('Envoi impossible, vérifiez la connexion.'); }
            }
            if (paths.length) commit(paths.length > 1 ? paths.length + ' photos ajoutées' : 'Photo ajoutée', () => { if (btn.dataset.upload === 'gallery') b.d.imgs.push(...paths); else b.d.img = paths[0]; });
            renderInspector();
        });
        input.click();
    }

    /* ───────────────────────── Rendu général ───────────────────────── */
    function renderTools() {
        const box = $('vfTools');
        box.innerHTML = panel === 'blocks' ? renderBlocksPanel() : panel === 'style' ? renderStylePanel() : panel === 'themes' ? renderThemesPanel() : renderSettingsPanel();
        if (panel === 'blocks') bindBlocksPanel();
        else if (panel === 'style') bindStylePanel();
        else if (panel === 'themes') bindThemesPanel();
        else bindSettingsPanel();
    }
    function renderAll() { renderTools(); renderInspector(); $('vfUndo').disabled = !past.length; $('vfRedo').disabled = !future.length; }
    function showPanel(p) {
        panel = p; typingField = null;
        document.querySelectorAll('[data-panel]').forEach(b => b.setAttribute('aria-pressed', b.dataset.panel === p ? 'true' : 'false'));
        renderTools();
    }
    document.querySelectorAll('[data-panel]').forEach(b => b.addEventListener('click', () => showPanel(b.dataset.panel)));

    /* ───────────────────────── Publier, partager, toast ───────────────────────── */
    $('vfPublishForm').addEventListener('submit', e => {
        if (S.slug && !slugState.ok) { e.preventDefault(); showPanel('set'); toast('Choisissez une adresse de boutique valide.'); return; }
        if (S.config.settings.welcome && !S.config.settings.welcome_promo_id) { e.preventDefault(); showPanel('set'); toast('Choisissez le code du message de bienvenue, ou désactivez-le.'); return; }
        $('vfPayload').value = JSON.stringify(S);
        dirty = false;
        const btn = $('vfPublish'); btn.disabled = true; btn.textContent = 'Publication…';
    });
    window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    let toastTimer = null;
    function toast(msg) {
        const t = $('vfToast');
        t.innerHTML = svg('m5 12.5 4.5 4.5L19 7.5', 18) + esc(msg);
        t.hidden = false;
        clearTimeout(toastTimer); toastTimer = setTimeout(() => { t.hidden = true; }, 2600);
    }
    const share = $('vfShare');
    let shareReturn = null;
    const SHARE_SHOP = { url: @json($publicUrl), wa: @json('https://wa.me/?text=' . rawurlencode($waText)), qr: @json($qrSvg), dl: @json(route('boutique.storefront.qr')) };
    /* i : index de la catégorie dans CAT_LINKS, ou -1 pour toute la boutique */
    function setShareTarget(i) {
        const c = CAT_LINKS[i];
        const t = c ? {
            url: c.url, qr: c.qr, dl: SHARE_SHOP.dl + '?categorie=' + encodeURIComponent(c.slug),
            wa: 'https://wa.me/?text=' + encodeURIComponent('Découvrez « ' + c.name + ' » chez ' + SHOP.name + ' sur Shopio : ' + c.wa),
        } : SHARE_SHOP;
        if ($('vfShareWhat')) $('vfShareWhat').value = c ? String(i) : '';
        $('vfShareUrl').value = t.url;
        $('vfShareWa').href = t.wa;
        $('vfShareOpen').href = t.url;
        $('vfShareHint').textContent = c
            ? 'Le client arrive directement sur les ' + c.count + ' produit' + (c.count > 1 ? 's' : '') + ' « ' + c.name + ' ».'
            : 'Envoyez le lien à vos clients ou imprimez le QR code pour votre comptoir.';
        if ($('vfShareQr') && t.qr) {
            $('vfShareQr').innerHTML = t.qr;
            $('vfShareQrTitle').textContent = c ? 'QR code « ' + c.name + ' »' : 'QR code de la boutique';
            $('vfShareQrDl').href = t.dl;
        }
    }
    function openShare(i) {
        shareReturn = document.activeElement;
        setShareTarget(i === undefined || i === '' ? -1 : +i);
        share.hidden = false;
        share.querySelector('[data-share-close]').focus();
    }
    function closeShare() { share.hidden = true; if (shareReturn) shareReturn.focus(); }
    document.addEventListener('click', e => { const b = e.target.closest('[data-share-open]'); if (b) { e.preventDefault(); openShare(b.dataset.shareOpen); } });
    if ($('vfShareWhat')) $('vfShareWhat').addEventListener('change', e => setShareTarget(e.target.value === '' ? -1 : +e.target.value));
    share.querySelector('[data-share-close]').addEventListener('click', closeShare);
    share.addEventListener('click', e => { if (e.target === share) closeShare(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !share.hidden) closeShare(); });
    $('vfCopy').addEventListener('click', async e => {
        const input = $('vfShareUrl'), btn = e.currentTarget;
        try { await navigator.clipboard.writeText(input.value); } catch (err) { input.select(); document.execCommand && document.execCommand('copy'); }
        btn.textContent = 'Copié'; setTimeout(() => { btn.textContent = 'Copier'; }, 2000);
    });

    /* ───────────────────────── Démarrage ───────────────────────── */
    if (S.slug) slugState = { ok: true, message: 'Adresse en ligne : ' + (SHOP.slugBase + S.slug).replace(/^https?:\/\//, '') };
    renderAll();
    fit();
    refresh();
    @if(session('published')) openShare(); @endif
})();
</script>
@endpush
