{{--
    resources/views/vendeur/shop/grow.blade.php
    Route     : GET /boutique/vitrine/grandir?onglet=… → Vendeur\GrowController@index → name('boutique.grow')
    Rôle      : « Faire grandir ma vitrine » — 5 onglets :
                1. Couverture et logo (publiés tout de suite)   2. Partage (liens, QR, affiche, liens suivis, campagne)
                3. Personnalisation (modèle, diapos, collections ; publier / brouillon / programmer)
                4. Conversion (preuves de confiance, mode WhatsApp, sections automatiques)   5. Qualité (statistiques, score, lien d'aperçu)
                Toutes les valeurs affichées viennent de la boutique : aucun chiffre n'est inventé.
--}}
@extends('layouts.app')
@section('title', 'Faire grandir ma vitrine · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    use App\Support\Storefront as SF;
    $tabs = ['couverture' => 'Couverture et logo', 'partage' => 'Partage', 'personnalisation' => 'Personnalisation', 'conversion' => 'Conversion', 'qualite' => 'Qualité'];
    $set = $published['settings'];
    $has = fn ($t) => collect($published['blocks'])->contains(fn ($b) => $b['type'] === $t && $b['on']);
    $ink = SF::inkFor($accent);
    $host = preg_replace('#^https?://#', '', $publicUrl);
    $q = $quality;
    $ringColor = $q['score'] >= 80 ? '#15803D' : ($q['score'] >= 50 ? '#1F7CF6' : '#F59E0B');
@endphp

@push('styles')
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<style>
/* Faire grandir ma vitrine : en-tête bleu nuit à onglets, panneaux arrondis, aperçus en grand */
:root {
    --gw-blue: #1F7CF6; --gw-btn: #1668D8; --gw-btn-h: #1260C9; --gw-b50: #EAF2FF; --gw-b100: #D6E6FF;
    --gw-night: #0B1E3D; --gw-night2: #132B52; --gw-amber: #F59E0B; --gw-amb50: #FEF3E0; --gw-ambi: #92400E;
    --gw-bg: #F6F9FF; --gw-stage: #DCE6F5; --gw-surf: #FFFFFF; --gw-line: #E3E8F2;
    --gw-ink: #16162A; --gw-ink2: #45455E; --gw-ink3: #62627C; --gw-ok: #15803D; --gw-ok50: #E7F6EC; --gw-err: #B91C1C; --gw-err50: #FDECEC;
    --gw-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --gw-out: cubic-bezier(.22,1,.36,1); --gw-pop: cubic-bezier(.34,1.56,.64,1);
}
[data-theme="dark"] {
    --gw-b50: #13284B; --gw-b100: #1B3563; --gw-bg: #0A1426; --gw-stage: #071022; --gw-surf: #10203B; --gw-line: #22355A;
    --gw-ink: #EAF0FA; --gw-ink2: #C3CEE0; --gw-ink3: #9DAAC2; --gw-ok: #4ADE80; --gw-ok50: #0F2A1C; --gw-err: #F87171; --gw-err50: #2A1215; --gw-amb50: #2E2208; --gw-ambi: #FBBF24;
}
#pwaModal, #pushNotifBanner { display: none !important; }
html, body { margin: 0; background: var(--gw-bg); color: var(--gw-ink); font-family: var(--gw-font); -webkit-font-smoothing: antialiased; }
.gw, .gw * { box-sizing: border-box; }
.gw { min-height: 100vh; font-size: 14.5px; line-height: 1.5; }
.gw-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.gw :focus-visible { outline: 3px solid var(--gw-blue); outline-offset: 2px; }
.gw h2, .gw h3 { margin: 0; font-weight: 800; letter-spacing: -.02em; }
.gw h2 { font-size: 17px; } .gw h3 { font-size: 15px; }
.gw-num { font-variant-numeric: tabular-nums; }
.gw-hint { margin: 0; font-size: 13px; color: var(--gw-ink2); }
.gw-lbl { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--gw-ink3); margin-bottom: 6px; }

/* ── En-tête : titre, onglets en pilule, liens ── */
.gw-top { position: sticky; top: 0; z-index: 40; background: var(--gw-night); color: #fff; padding: 12px max(18px, env(safe-area-inset-right, 0px)) 12px max(18px, env(safe-area-inset-left, 0px)); display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px 16px; align-items: center; }
.gw-brand { display: flex; align-items: center; gap: 12px; min-width: 0; }
.gw-brand img { width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0; }
.gw-brand h1 { margin: 0; font-size: 19px; font-weight: 800; letter-spacing: -.02em; color: #fff; }
.gw-brand p { margin: 0; font-size: 12.5px; color: #9FB3D3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gw-links { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
.gw-tabs { grid-column: 1 / -1; display: flex; gap: 4px; padding: 4px; border-radius: 999px; background: var(--gw-night2); overflow-x: auto; scrollbar-width: none; justify-self: start; max-width: 100%; }
.gw-tabs::-webkit-scrollbar { display: none; }
.gw-tab { min-height: 40px; padding: 0 16px; border: 0; border-radius: 999px; background: transparent; color: #C9D6EA; font: 700 14px var(--gw-font); white-space: nowrap; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; transition: background-color 180ms var(--gw-out), color 180ms; }
.gw-tab:hover { color: #fff; }
.gw-tab[aria-selected="true"] { background: #fff; color: var(--gw-night); }

/* ── Boutons, champs, segments, interrupteurs ── */
.gw-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 999px; border: 0; font: 700 14px/1 var(--gw-font); text-decoration: none; cursor: pointer; white-space: nowrap; transition: transform 180ms var(--gw-out), background-color 180ms, box-shadow 180ms, color 180ms; }
.gw-btn:active:not(:disabled) { transform: scale(.97); }
.gw-btn:disabled { opacity: .45; cursor: default; }
.gw-btn-sm { min-height: 36px; padding: 0 14px; font-size: 13px; }
.gw-btn-night { background: var(--gw-night2); color: #fff; } .gw-btn-night:hover { background: #1B3563; color: #fff; }
.gw-btn-amber { background: var(--gw-amber); color: #16162A; } .gw-btn-amber:hover { box-shadow: 0 8px 20px rgba(245,158,11,.35); color: #16162A; }
.gw-btn-blue { background: var(--gw-btn); color: #fff; } .gw-btn-blue:hover { background: var(--gw-btn-h); color: #fff; }
.gw-btn-line { background: var(--gw-surf); color: var(--gw-ink); box-shadow: inset 0 0 0 1.5px var(--gw-line); }
.gw-btn-line:hover:not(:disabled) { box-shadow: inset 0 0 0 1.5px var(--gw-blue); color: var(--gw-btn); }
.gw-btn-wa { background: #15803D; color: #fff; } .gw-btn-wa:hover { background: #116B33; color: #fff; }
.gw-btn-danger { background: var(--gw-surf); color: var(--gw-err); box-shadow: inset 0 0 0 1.5px var(--gw-line); }
.gw-btn-danger:hover { box-shadow: inset 0 0 0 1.5px var(--gw-err); }
.gw-input, .gw-select { width: 100%; min-height: 44px; border: 0; border-radius: 12px; padding: 0 12px; background: var(--gw-surf); color: var(--gw-ink); font: 500 14.5px var(--gw-font); box-shadow: inset 0 0 0 1.5px var(--gw-line); outline: 0; }
.gw-input:focus, .gw-select:focus { box-shadow: inset 0 0 0 2px var(--gw-blue); }
.gw-input[aria-invalid="true"] { box-shadow: inset 0 0 0 2px var(--gw-err); }
textarea.gw-input { padding: 10px 12px; min-height: 96px; resize: vertical; line-height: 1.45; }
.gw-field { display: flex; flex-direction: column; gap: 4px; }
.gw-count { font-size: 12px; color: var(--gw-ink3); text-align: right; }
.gw-err { margin: 0; font-size: 13px; font-weight: 700; color: var(--gw-err); }
.gw-warn { margin: 0; font-size: 13px; font-weight: 700; color: var(--gw-ambi); }
.gw-okmsg { margin: 0; font-size: 13px; font-weight: 700; color: var(--gw-ok); }
.gw-seg { display: flex; padding: 4px; gap: 2px; border-radius: 999px; background: var(--gw-b50); }
.gw-seg button { flex: 1; min-height: 38px; border: 0; border-radius: 999px; background: transparent; font: 700 13px var(--gw-font); color: var(--gw-ink2); cursor: pointer; padding: 0 10px; transition: background-color 200ms var(--gw-out), color 200ms, box-shadow 200ms; }
.gw-seg button[aria-pressed="true"] { background: var(--gw-surf); color: var(--gw-btn); box-shadow: 0 2px 6px rgba(11,30,61,.12); }
.gw-sw { position: relative; flex-shrink: 0; width: 48px; height: 28px; border-radius: 999px; border: 0; cursor: pointer; padding: 0; background: #C3CBDA; transition: background-color 200ms var(--gw-out); }
.gw-sw span { position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 999px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,.25); transition: transform 260ms var(--gw-pop); }
.gw-sw[aria-checked="true"] { background: var(--gw-btn); }
.gw-sw[aria-checked="true"] span { transform: translateX(20px); }
.gw-sw.is-green[aria-checked="true"] { background: #15803D; }
.gw-sw:disabled { opacity: .5; cursor: not-allowed; }
.gw-row { display: flex; align-items: center; gap: 12px; padding: 11px 12px; border-radius: 16px; background: var(--gw-bg); }
.gw-row-txt { flex: 1; min-width: 0; }
.gw-row-txt b { display: block; font-size: 14px; }
.gw-row-txt span { display: block; font-size: 12.5px; color: var(--gw-ink3); }
.gw-row-txt .gw-pill { display: inline-flex; vertical-align: middle; margin-left: 4px; }
.gw-range { display: flex; flex-direction: column; gap: 6px; }
.gw-range label { display: flex; justify-content: space-between; font-size: 13.5px; font-weight: 700; }
.gw-range output { color: var(--gw-btn); font-variant-numeric: tabular-nums; }
.gw-range input { width: 100%; accent-color: var(--gw-btn); min-height: 28px; }
.gw-pill { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; font-size: 12px; font-weight: 800; white-space: nowrap; }
.gw-pill-ok { background: var(--gw-ok50); color: var(--gw-ok); }
.gw-pill-grey { background: var(--gw-bg); color: var(--gw-ink2); box-shadow: inset 0 0 0 1px var(--gw-line); }
.gw-pill-amber { background: var(--gw-amb50); color: var(--gw-ambi); }
.gw-pill-blue { background: var(--gw-b50); color: var(--gw-btn); }
.gw-pill-auto { background: var(--gw-night); color: #fff; letter-spacing: .06em; height: 22px; font-size: 10.5px; }

/* ── Mise en page ── */
.gw-flash { margin: 14px max(18px, env(safe-area-inset-left, 0px)) 0; padding: 12px 16px; border-radius: 16px; font-weight: 700; }
.gw-flash-ok { background: var(--gw-ok50); color: var(--gw-ok); }
.gw-flash-err { background: var(--gw-err50); color: var(--gw-err); }
.gw-main { padding: 16px max(18px, env(safe-area-inset-right, 0px)) 40px max(18px, env(safe-area-inset-left, 0px)); max-width: 1500px; margin: 0 auto; }
.gw-tabpanel { perspective: 1600px; }
.gw-tabpanel.is-entering > * { animation: gwTabIn 520ms var(--gw-out) backwards; transform-origin: 50% 0; }
@keyframes gwTabIn { from { opacity: 0; transform: rotateX(-8deg) translateY(14px); } }
.gw-split { display: grid; grid-template-columns: 340px minmax(0, 1fr); gap: 16px; align-items: start; }
.gw-side, .gw-stack { display: flex; flex-direction: column; gap: 14px; min-width: 0; }
.gw-grid2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; align-items: start; }
.gw-card { background: var(--gw-surf); border-radius: 24px; box-shadow: 0 0 0 1px var(--gw-line), 0 10px 30px -22px rgba(11,30,61,.35); padding: 18px; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
.gw-card-hd { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.gw-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.gw-sticky { position: sticky; top: 132px; }

/* ── Onglet 1 : couverture et logo ── */
.gw-drop { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 18px 14px; border: 2px dashed #B9C7DD; border-radius: 18px; text-align: center; cursor: pointer; background: var(--gw-bg); transition: border-color 180ms, background-color 180ms; }
.gw-drop:hover, .gw-drop.is-over { border-color: var(--gw-blue); background: var(--gw-b50); }
.gw-drop b { font-size: 14.5px; color: var(--gw-btn); }
.gw-drop span { font-size: 12.5px; color: var(--gw-ink3); }
.gw-drop:focus-within { outline: 3px solid var(--gw-blue); outline-offset: 2px; }
.gw-thumbs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.gw-thumb { position: relative; height: 72px; border: 0; border-radius: 14px; padding: 0; cursor: pointer; overflow: hidden; background: #0B1E3D center / cover; box-shadow: inset 0 0 0 1px rgba(0,0,0,.06); transition: transform 180ms var(--gw-out), box-shadow 180ms; }
.gw-thumb:hover { transform: translateY(-2px); }
.gw-thumb[aria-checked="true"] { box-shadow: 0 0 0 3px var(--gw-surf), 0 0 0 5px var(--gw-blue); }
.gw-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gw-thumb span { position: absolute; left: 6px; right: 6px; bottom: 5px; font-size: 11px; font-weight: 800; color: #fff; text-shadow: 0 1px 6px rgba(0,0,0,.6); text-align: left; line-height: 1.2; }
.gw-logos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.gw-logo-opt { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 6px; border: 0; border-radius: 16px; background: var(--gw-bg); cursor: pointer; font: 700 12.5px var(--gw-font); color: var(--gw-ink2); box-shadow: inset 0 0 0 1.5px var(--gw-line); transition: box-shadow 180ms; }
.gw-logo-opt[aria-checked="true"] { box-shadow: inset 0 0 0 2px var(--gw-blue); color: var(--gw-btn); }
.gw-logo-opt i { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--gw-surf); font-style: normal; font-weight: 800; color: var(--gw-ink3); box-shadow: inset 0 0 0 1px var(--gw-line); }
.gw-logo-opt i img { width: 100%; height: 100%; object-fit: cover; }
.gw-swatches { display: flex; gap: 10px; flex-wrap: wrap; }
.gw-swatch { width: 40px; height: 40px; border-radius: 999px; border: 0; cursor: pointer; box-shadow: inset 0 0 0 1px rgba(0,0,0,.1); transition: transform 180ms var(--gw-pop); }
.gw-swatch:hover { transform: scale(1.08); }
.gw-swatch[aria-checked="true"] { box-shadow: 0 0 0 3px var(--gw-surf), 0 0 0 5px var(--gw-ink); }
/* Aperçus : navigateur (ordinateur) ou téléphone, un seul à la fois */
.gw-stage { background: var(--gw-stage); border-radius: 22px; padding: 18px; display: flex; justify-content: center; overflow: hidden; }
.gw-browser { width: 100%; border-radius: 16px; overflow: hidden; background: #F6F9FF; box-shadow: 0 30px 60px -30px rgba(11,30,61,.5); animation: gwIn 450ms var(--gw-out); }
.gw-bar { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #E9EEF7; }
.gw-dots { display: flex; gap: 6px; } .gw-dots i { width: 10px; height: 10px; border-radius: 50%; background: #C3CBDA; }
.gw-url { flex: 1; min-width: 0; padding: 6px 12px; border-radius: 999px; background: #fff; color: #45455E; font-size: 12.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gw-phone { width: 390px; max-width: 100%; border: 11px solid #0B1E3D; border-radius: 44px; overflow: hidden; background: #F6F9FF; box-shadow: 0 30px 60px -24px rgba(11,30,61,.55); animation: gwIn 450ms var(--gw-out); }
@keyframes gwIn { from { opacity: 0; transform: perspective(1200px) rotateY(-14deg) scale(.96); } }
.gw-page { --surface: #fff; --border: #E3E8F2; --text: #16162A; --text-2: #45455E; --grey-2: #EEF2F8; color: #16162A; }
.gw-prods { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; padding: 18px 24px 24px; }
.gw-phone .gw-prods { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 14px 16px 18px; gap: 10px; }
.gw-prod { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 0 0 1px #E3E8F2; }
.gw-prod-img { aspect-ratio: 1; background: #EAF2FF; display: flex; align-items: center; justify-content: center; color: #7DB4FF; }
.gw-prod-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gw-prod div { padding: 8px 10px 10px; }
.gw-prod b { display: block; font-size: 12.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gw-prod span { font-size: 12px; font-weight: 800; color: #1260C9; font-variant-numeric: tabular-nums; }
.gw-zone { position: absolute; top: 0; bottom: 0; left: 50%; transform: translateX(-50%); border: 2px dashed rgba(255,255,255,.9); box-shadow: 0 0 0 9999px rgba(6,18,38,.35); z-index: 2; pointer-events: none; }
.gw-zone span { position: absolute; top: 8px; left: 50%; transform: translateX(-50%); padding: 3px 10px; border-radius: 999px; background: rgba(6,18,38,.75); color: #fff; font-size: 11.5px; font-weight: 800; white-space: nowrap; }
.gw-two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
/* Carte de partage WhatsApp et ligne de recherche */
.gw-wa { background: #E5DDD5; border-radius: 18px; padding: 14px; }
.gw-wa-bubble { max-width: 340px; margin-left: auto; background: #D9FDD3; border-radius: 12px 4px 12px 12px; padding: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.12); color: #111B21; }
.gw-wa-card { background: #F0F2F5; border-radius: 8px; overflow: hidden; }
.gw-wa-img { position: relative; aspect-ratio: 1.91; background: #0B1E3D center / cover; overflow: hidden; }
.gw-wa-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gw-wa-logo { position: absolute; left: 10px; bottom: 10px; width: 40px; height: 40px; border-radius: 50%; border: 3px solid #fff; overflow: hidden; background: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; }
.gw-wa-logo img { width: 100%; height: 100%; object-fit: cover; }
.gw-wa-price { position: absolute; right: 10px; bottom: 10px; padding: 5px 10px; border-radius: 999px; background: #fff; color: #111B21; font-size: 12.5px; font-weight: 800; font-variant-numeric: tabular-nums; }
.gw-wa-txt { padding: 8px 10px 10px; }
.gw-wa-txt b { display: block; font-size: 13.5px; }
.gw-wa-txt span { display: block; font-size: 12.5px; color: #54656F; }
.gw-wa-txt small { display: block; margin-top: 3px; font-size: 11.5px; color: #8696A0; }
.gw-wa-msg { padding: 6px 8px 2px; font-size: 13.5px; white-space: pre-line; overflow-wrap: anywhere; }
.gw-wa-time { display: block; text-align: right; font-size: 11px; color: #667781; padding: 0 6px 2px; }
.gw-search { display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 18px; background: var(--gw-bg); }
.gw-search-logo { width: 52px; height: 52px; border-radius: 16px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.gw-search-logo img { width: 100%; height: 100%; object-fit: cover; }
.gw-search b { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: 15px; }
.gw-search p { margin: 2px 0 0; font-size: 13px; color: var(--gw-ink2); }
.gw-search small { display: block; margin-top: 4px; font-size: 12px; color: var(--gw-ink3); }

/* ── Onglet 2 : partage ── */
.gw-picks { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 8px; }
.gw-pick { position: relative; aspect-ratio: 1; border: 0; padding: 0; border-radius: 14px; overflow: hidden; cursor: pointer; background: var(--gw-b50); color: var(--gw-btn); font-weight: 800; box-shadow: inset 0 0 0 1px var(--gw-line); transition: transform 180ms var(--gw-out); }
.gw-pick:hover { transform: translateY(-2px); }
.gw-pick[aria-pressed="true"] { box-shadow: 0 0 0 3px var(--gw-surf), 0 0 0 5px var(--gw-blue); }
.gw-pick img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gw-link { display: flex; align-items: center; gap: 8px; padding: 6px 6px 6px 12px; border-radius: 14px; background: var(--gw-b50); color: var(--gw-btn); }
.gw-link svg { flex-shrink: 0; }
.gw-link input { flex: 1; min-width: 0; border: 0; background: transparent; color: var(--gw-btn); font: 700 13.5px var(--gw-font); outline: 0; }
.gw-qr-wrap { display: flex; gap: 18px; align-items: center; flex-wrap: wrap; }
.gw-qr { position: relative; width: 200px; height: 200px; flex-shrink: 0; padding: 12px; border-radius: 22px; background: #fff; box-shadow: 0 20px 40px -20px rgba(11,30,61,.5), 0 0 0 1px var(--gw-line); transform: perspective(700px) rotateX(16deg) rotateY(-18deg); transition: transform 450ms var(--gw-out); }
.gw-qr:hover { transform: none; }
.gw-qr img { width: 100%; height: 100%; display: block; }
.gw-qr-logo { position: absolute; left: 50%; top: 50%; width: 22%; height: 22%; transform: translate(-50%, -50%); border-radius: 10px; border: 3px solid #fff; overflow: hidden; background: var(--gw-blue); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; }
.gw-qr-logo img { width: 100%; height: 100%; object-fit: cover; }
.gw-table-wrap { overflow-x: auto; }
.gw-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.gw-table th { text-align: left; font-size: 11.5px; letter-spacing: .06em; text-transform: uppercase; color: var(--gw-ink3); padding: 6px 10px; font-weight: 800; }
.gw-table td { padding: 10px; border-top: 1px solid var(--gw-line); vertical-align: middle; }
.gw-table td.gw-num, .gw-table th.gw-num { text-align: right; }
.gw-chan { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; font-size: 12px; font-weight: 800; color: #fff; }
.gw-camp { display: flex; flex-direction: column; gap: 8px; padding: 12px; border-radius: 18px; background: var(--gw-bg); }
.gw-camp-hd { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.gw-code { font: 800 14px var(--gw-font); letter-spacing: .06em; padding: 4px 10px; border-radius: 10px; background: var(--gw-surf); box-shadow: inset 0 0 0 1.5px var(--gw-line); }
.gw-lock { display: flex; gap: 12px; align-items: flex-start; padding: 14px; border-radius: 18px; background: var(--gw-amb50); color: var(--gw-ambi); }

/* ── Onglet 3 : personnalisation ── */
.gw-tpls { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.gw-tpl { position: relative; display: flex; flex-direction: column; gap: 8px; padding: 10px; border: 0; border-radius: 20px; background: var(--gw-bg); text-align: left; cursor: pointer; font-family: var(--gw-font); color: var(--gw-ink); box-shadow: inset 0 0 0 1.5px var(--gw-line); transition: transform 260ms var(--gw-out), box-shadow 180ms; }
.gw-tpl:hover { transform: perspective(700px) rotateX(4deg) translateY(-3px); }
.gw-tpl[aria-pressed="true"] { box-shadow: inset 0 0 0 2.5px var(--gw-blue); }
.gw-tpl-mini { height: 96px; border-radius: 12px; overflow: hidden; display: grid; grid-template-rows: 30px 1fr; background: #fff; }
.gw-tpl-mini > i { display: block; }
.gw-tpl-mini > div { display: grid; gap: 4px; padding: 6px; }
.gw-tpl-mini > div i { display: block; border-radius: 4px; background: #E3E8F2; }
.gw-tpl b { font-size: 14px; }
.gw-pms { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; }
.gw-pm { position: relative; display: flex; flex-direction: column; gap: 6px; padding: 10px; border-radius: 18px; background: var(--gw-bg); box-shadow: inset 0 0 0 1.5px var(--gw-line); }
.gw-pm[aria-current="true"] { box-shadow: inset 0 0 0 2.5px var(--gw-blue); background: var(--gw-b50); }
.gw-pm-sw { height: 64px; border-radius: 12px; position: relative; overflow: hidden; }
.gw-pm button { border: 0; background: none; padding: 0; text-align: left; cursor: pointer; color: var(--gw-ink); font-family: var(--gw-font); display: flex; flex-direction: column; gap: 6px; }
.gw-pm b { font-size: 14px; } .gw-pm small { font-size: 11.5px; color: var(--gw-ink3); line-height: 1.35; }
.gw-pm a { font-size: 12.5px; font-weight: 800; color: var(--gw-btn); }
.gw-pm .gw-pill { position: absolute; top: 14px; right: 14px; }
@media (max-width: 1180px) { .gw-pms { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 760px) { .gw-pms { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.gw-tpl small { font-size: 12px; color: var(--gw-ink3); line-height: 1.35; }
.gw-tpl .gw-pill { position: absolute; top: 16px; right: 16px; }
.gw-slides { display: flex; flex-direction: column; gap: 8px; }
.gw-slide { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 16px; background: var(--gw-bg); }
.gw-slide-th { width: 86px; height: 46px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: #0B1E3D center / cover; }
.gw-slide-th img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gw-slide b { flex: 1; min-width: 0; font-size: 13.5px; }
.gw-adder { display: flex; flex-direction: column; gap: 10px; padding: 12px; border-radius: 18px; background: var(--gw-b50); }
.gw-cols { display: flex; flex-direction: column; gap: 10px; }
.gw-col { display: flex; flex-direction: column; gap: 10px; padding: 12px; border-radius: 18px; background: var(--gw-bg); }
.gw-col-hd { display: flex; align-items: center; gap: 12px; }
.gw-stack3 { position: relative; width: 64px; height: 52px; flex-shrink: 0; }
.gw-stack3 i { position: absolute; top: 0; width: 44px; height: 52px; border-radius: 10px; border: 2px solid var(--gw-surf); background: var(--gw-b100) center / cover; box-shadow: 0 4px 10px rgba(11,30,61,.18); }
.gw-stack3 i:nth-child(1) { left: 0; transform: rotate(-8deg); } .gw-stack3 i:nth-child(2) { left: 10px; transform: rotate(-2deg); } .gw-stack3 i:nth-child(3) { left: 20px; transform: rotate(5deg); }
.gw-col-body { display: grid; gap: 8px; }
.gw-checklist { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 6px; max-height: 240px; overflow-y: auto; padding: 2px; }
.gw-check { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 12px; background: var(--gw-surf); font-size: 12.5px; cursor: pointer; box-shadow: inset 0 0 0 1px var(--gw-line); }
.gw-check input { accent-color: var(--gw-btn); width: 18px; height: 18px; flex-shrink: 0; }
.gw-check img { width: 28px; height: 28px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.gw-check span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.gw-modes { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.gw-mode { display: flex; flex-direction: column; gap: 2px; padding: 12px; border-radius: 16px; background: var(--gw-bg); cursor: pointer; box-shadow: inset 0 0 0 1.5px var(--gw-line); }
.gw-mode:has(input:checked) { box-shadow: inset 0 0 0 2px var(--gw-blue); background: var(--gw-b50); }
.gw-mode input { position: absolute; opacity: 0; pointer-events: none; }
.gw-mode b { font-size: 14px; }
.gw-mode span { font-size: 12px; color: var(--gw-ink3); }
.gw-mode:focus-within { outline: 3px solid var(--gw-blue); outline-offset: 2px; }

/* ── Onglet 4 : conversion ── */
.gw-conv { display: grid; grid-template-columns: 380px minmax(0, 1fr); gap: 18px; align-items: start; }
.gw-tilt { display: flex; justify-content: center; padding: 26px 10px; border-radius: 26px; background: var(--gw-stage); perspective: 1400px; }
.gw-phone2 { position: relative; width: 340px; height: 690px; max-width: 100%; border: 11px solid #0B1E3D; border-radius: 46px; background: #fff; overflow: hidden; transform: rotateY(-10deg) rotateX(4deg); box-shadow: 30px 40px 60px -30px rgba(11,30,61,.6); transition: transform 600ms var(--gw-out); color: #16162A; }
.gw-phone2:hover { transform: none; }
.gw-ph-scroll { position: absolute; inset: 0 0 76px; overflow: hidden; }
.gw-ph-inner { animation: gwScroll 14s ease-in-out infinite alternate; }
@keyframes gwScroll { 0%, 12% { transform: translateY(0); } 88%, 100% { transform: translateY(var(--scroll, -220px)); } }
.gw-ph-img { aspect-ratio: 1; background: #EAF2FF; display: flex; align-items: center; justify-content: center; color: #7DB4FF; }
.gw-ph-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gw-ph-body { padding: 14px 16px 20px; display: flex; flex-direction: column; gap: 8px; }
.gw-ph-body h4 { margin: 0; font-size: 18px; font-weight: 800; letter-spacing: -.02em; }
.gw-ph-price { font-size: 20px; font-weight: 800; color: #1260C9; font-variant-numeric: tabular-nums; }
.gw-ph-proofs { display: flex; gap: 6px; flex-wrap: wrap; }
.gw-ph-proofs span { display: inline-flex; align-items: center; height: 24px; padding: 0 9px; border-radius: 999px; background: #EAF2FF; color: #1260C9; font-size: 11.5px; font-weight: 800; }
.gw-ph-proofs span.is-ok { background: #E7F6EC; color: #15803D; }
.gw-ph-body p { margin: 0; font-size: 13px; color: #45455E; }
.gw-ph-bar { position: absolute; left: 0; right: 0; bottom: 0; display: flex; gap: 8px; padding: 12px 12px 16px; background: #fff; box-shadow: 0 -8px 20px rgba(11,30,61,.08); }
.gw-ph-bar span { flex: 1; display: flex; align-items: center; justify-content: center; min-height: 46px; border-radius: 999px; font-weight: 800; font-size: 13.5px; text-align: center; }
.gw-ph-buy { background: #1F7CF6; color: #fff; } .gw-ph-buy.is-wa { background: #15803D; flex: 1.6; }
.gw-ph-nego { background: #EAF2FF; color: #1260C9; }
.gw-proof { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 16px; background: var(--gw-bg); }
.gw-proof-ico { width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: var(--gw-b50); color: var(--gw-btn); }
.gw-proof.is-locked .gw-proof-ico { background: var(--gw-surf); color: var(--gw-ink3); box-shadow: inset 0 0 0 1px var(--gw-line); }

/* ── Onglet 5 : qualité ── */
.gw-tiles { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.gw-tile { padding: 16px; border-radius: 20px; background: var(--gw-surf); box-shadow: 0 0 0 1px var(--gw-line); }
.gw-tile b { display: block; font-size: 28px; font-weight: 800; letter-spacing: -.02em; font-variant-numeric: tabular-nums; }
.gw-tile span { font-size: 13px; color: var(--gw-ink3); font-weight: 600; }
.gw-tile.is-night { background: var(--gw-night); color: #fff; box-shadow: none; }
.gw-tile.is-night span { color: #9FB3D3; }
.gw-bars { display: flex; flex-direction: column; gap: 10px; }
.gw-barrow { display: grid; grid-template-columns: 130px minmax(0, 1fr) 54px; gap: 10px; align-items: center; font-size: 13px; }
.gw-barrow span:first-child { font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.gw-track { height: 12px; border-radius: 999px; background: var(--gw-b50); overflow: hidden; }
.gw-track i { display: block; height: 100%; border-radius: 999px; background: var(--gw-blue); transform-origin: left; animation: gwGrow 700ms var(--gw-out) backwards; }
@keyframes gwGrow { from { transform: scaleX(0); } }
.gw-barrow b { text-align: right; font-variant-numeric: tabular-nums; }
.gw-rank { display: flex; flex-direction: column; gap: 6px; counter-reset: rank; }
.gw-rank li { list-style: none; display: grid; grid-template-columns: 28px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 10px 12px; border-radius: 14px; background: var(--gw-bg); font-size: 13.5px; }
.gw-rank li::before { counter-increment: rank; content: counter(rank); width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--gw-b50); color: var(--gw-btn); font-weight: 800; font-size: 12.5px; }
.gw-rank ol { margin: 0; padding: 0; }
.gw-amberbox { padding: 16px; border-radius: 22px; background: var(--gw-amb50); color: var(--gw-ink); display: flex; flex-direction: column; gap: 10px; }
.gw-amberbox h3 { color: var(--gw-ambi); }
.gw-seen { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 14px; background: var(--gw-surf); }
.gw-seen img, .gw-seen i { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; flex-shrink: 0; background: var(--gw-b50); }
.gw-seen div { flex: 1; min-width: 0; }
.gw-seen b { display: block; font-size: 13.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.gw-seen span { font-size: 12px; color: var(--gw-ink3); }
.gw-score { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
.gw-ring { --p: 0; position: relative; width: 150px; height: 150px; border-radius: 50%; flex-shrink: 0; background: conic-gradient(var(--ring) calc(var(--p) * 1%), var(--gw-b50) 0); display: flex; align-items: center; justify-content: center; animation: gwIn 600ms var(--gw-out); }
.gw-ring::before { content: ''; position: absolute; inset: 14px; border-radius: 50%; background: var(--gw-surf); }
.gw-ring b { position: relative; font-size: 38px; font-weight: 800; letter-spacing: -.03em; font-variant-numeric: tabular-nums; }
.gw-ring b small { font-size: 16px; }
.gw-tips { display: flex; flex-direction: column; gap: 8px; margin: 0; padding: 0; list-style: none; }
.gw-tip { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 16px; background: var(--gw-bg); }
.gw-tip-ico { width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--gw-amb50); color: var(--gw-ambi); }
.gw-tip.is-done .gw-tip-ico { background: var(--gw-ok50); color: var(--gw-ok); }
.gw-tip.is-done .gw-tip-txt b { text-decoration: line-through; text-decoration-color: rgba(98,98,124,.5); color: var(--gw-ink2); }
.gw-tip-txt { flex: 1; min-width: 0; }
.gw-tip-txt b { display: block; font-size: 14px; }
.gw-tip-txt span { display: block; font-size: 12.5px; color: var(--gw-ink3); }
.gw-fb { display: flex; flex-direction: column; gap: 8px; }
.gw-fb-item { padding: 12px; border-radius: 16px; background: var(--gw-bg); }
.gw-fb-item b { font-size: 13.5px; } .gw-fb-item small { color: var(--gw-ink3); margin-left: 6px; }
.gw-fb-item p { margin: 4px 0 0; font-size: 14px; white-space: pre-line; overflow-wrap: anywhere; }
.gw-empty { padding: 16px; border-radius: 18px; border: 1.5px dashed var(--gw-line); color: var(--gw-ink2); font-size: 13.5px; text-align: center; }

.gw-toast { position: fixed; left: 50%; bottom: max(20px, env(safe-area-inset-bottom, 0px)); transform: translateX(-50%); z-index: 90; display: flex; align-items: center; gap: 8px; padding: 12px 18px; border-radius: 999px; background: var(--gw-night); color: #fff; font-weight: 700; box-shadow: 0 16px 40px rgba(11,30,61,.35); animation: gwToast 320ms var(--gw-pop); max-width: calc(100% - 32px); }
.gw-toast[hidden] { display: none; }
@keyframes gwToast { from { opacity: 0; transform: translate(-50%, 16px); } }

/* En-tête « couverture » (mêmes styles que la vitrine) */
@include('public.shops._cover-css')

@media (max-width: 1180px) {
    .gw-split, .gw-conv { grid-template-columns: minmax(0, 1fr); }
    .gw-sticky { position: static; }
    .gw-tpls { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .gw-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 760px) {
    .gw-top { grid-template-columns: minmax(0, 1fr); }
    .gw-links { justify-content: flex-start; }
    .gw-grid2, .gw-two, .gw-modes { grid-template-columns: minmax(0, 1fr); }
    .gw-picks { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .gw-prods { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 14px; }
    .gw-stage { padding: 10px; }
    .gw-barrow { grid-template-columns: 96px minmax(0, 1fr) 44px; }
    .gw-phone2 { transform: none; }
}
@media (prefers-reduced-motion: reduce) {
    .gw *, .gw *::before, .gw *::after { animation: none !important; transition-duration: 1ms !important; }
    .gw-qr, .gw-phone2 { transform: none; }
}
</style>
@endpush

@section('content')
<div class="gw" id="gw">
    {{-- ══ En-tête ══ --}}
    <header class="gw-top">
        <div class="gw-brand">
            <img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="" width="40" height="40">
            <div style="min-width:0">
                <h1>Faire grandir ma vitrine</h1>
                <p>{{ $shop->name }} · partage, personnalisation, conversion et qualité</p>
            </div>
        </div>
        <nav class="gw-links" aria-label="Autres pages">
            <a class="gw-btn gw-btn-night gw-btn-sm" href="{{ $publicUrl }}" target="_blank" rel="noopener">Vitrine</a>
            <a class="gw-btn gw-btn-night gw-btn-sm" href="{{ route('boutique.storefront.edit') }}">Éditeur</a>
            <a class="gw-btn gw-btn-night gw-btn-sm" href="{{ route('boutique.dashboard') }}">Tableau de bord</a>
        </nav>
        <div class="gw-tabs" role="tablist" aria-label="Outils">
            @foreach($tabs as $k => $label)
            <a class="gw-tab" role="tab" id="t-{{ $k }}" href="{{ route('boutique.grow', ['onglet' => $k]) }}" aria-controls="tab-{{ $k }}" aria-selected="{{ $tab === $k ? 'true' : 'false' }}" tabindex="{{ $tab === $k ? '0' : '-1' }}" data-tab="{{ $k }}">{{ $label }}</a>
            @endforeach
        </div>
    </header>

    @if(session('success'))<div class="gw-flash gw-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="gw-flash gw-flash-err" role="alert">{{ $errors->first() }}</div>@endif

    <main class="gw-main">
    {{-- ═══════════════════════ 1. COUVERTURE ET LOGO ═══════════════════════ --}}
    <section class="gw-tabpanel" id="tab-couverture" role="tabpanel" aria-labelledby="t-couverture" @if($tab !== 'couverture') hidden @endif>
        <form method="POST" action="{{ route('boutique.grow.identity') }}" id="gwIdForm" class="gw-split" novalidate>
            @csrf @method('PUT')
            @foreach(['cover_src', 'cover_img', 'cover_y', 'cover_veil', 'cover_height', 'name_on', 'logo_src', 'logo_img', 'logo_shape', 'logo_pos', 'logo_size', 'color'] as $f)
            <input type="hidden" name="{{ $f }}" id="h_{{ $f }}">
            @endforeach
            <div class="gw-side">
                <section class="gw-card" aria-labelledby="gwCoverH">
                    <h2 id="gwCoverH">Couverture</h2>
                    <label class="gw-drop" for="gwCoverFile" id="gwCoverDrop">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1F7CF6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 15v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-4M12 3v12M7 8l5-5 5 5"/></svg>
                        <b>Importer ma couverture</b>
                        <span>JPG, PNG ou WebP · 5 Mo max · idéal 1600 × 600 px</span>
                        <input type="file" id="gwCoverFile" accept="image/jpeg,image/png,image/webp" class="gw-sr">
                    </label>
                    <p id="gwCoverMsg" class="gw-hint" aria-live="polite"></p>
                    <div class="gw-thumbs" role="radiogroup" aria-label="Choix de la couverture" id="gwCoverThumbs"></div>
                    <div class="gw-range"><label for="gwY">Cadrage vertical <output id="gwYOut" for="gwY"></output></label><input type="range" id="gwY" min="0" max="100" step="1"></div>
                    <div class="gw-range"><label for="gwVeil">Voile sombre <output id="gwVeilOut" for="gwVeil"></output></label><input type="range" id="gwVeil" min="0" max="70" step="1"></div>
                    <div><span class="gw-lbl" id="gwHeightL">Hauteur</span><div class="gw-seg" role="group" aria-labelledby="gwHeightL" data-seg="height">@foreach(SF::COVER_HEIGHTS as $k => $l)<button type="button" data-v="{{ $k }}">{{ $l }}</button>@endforeach</div></div>
                    <div class="gw-row"><div class="gw-row-txt"><b>Montrer la zone visible sur téléphone</b><span>Bande en pointillés sur l’aperçu ordinateur</span></div><button type="button" class="gw-sw" role="switch" aria-checked="false" id="gwZone" aria-label="Montrer la zone visible sur téléphone"><span></span></button></div>
                </section>

                <section class="gw-card" aria-labelledby="gwLogoH">
                    <h2 id="gwLogoH">Logo</h2>
                    <div class="gw-logos" role="radiogroup" aria-label="Choix du logo" id="gwLogoOpts"></div>
                    <input type="file" id="gwLogoFile" accept="image/png,image/jpeg,image/webp" class="gw-sr" tabindex="-1">
                    <p class="gw-hint">Conseil : logo carré, 512 × 512 px, fond transparent ou uni. PNG, JPG ou WebP, 2 Mo maximum.</p>
                    <p id="gwLogoMsg" class="gw-hint" aria-live="polite"></p>
                    <div><span class="gw-lbl" id="gwShapeL">Forme</span><div class="gw-seg" role="group" aria-labelledby="gwShapeL" data-seg="shape">@foreach(SF::LOGO_SHAPES as $k => $l)<button type="button" data-v="{{ $k }}">{{ $l }}</button>@endforeach</div></div>
                    <div><span class="gw-lbl" id="gwPosL">Position</span><div class="gw-seg" role="group" aria-labelledby="gwPosL" data-seg="pos">@foreach(SF::LOGO_POSITIONS as $k => $l)<button type="button" data-v="{{ $k }}">{{ $l }}</button>@endforeach</div></div>
                    <div class="gw-range"><label for="gwSize">Taille <output id="gwSizeOut" for="gwSize"></output></label><input type="range" id="gwSize" min="64" max="140" step="2"></div>
                </section>

                <section class="gw-card" aria-labelledby="gwNameH">
                    <h2 id="gwNameH">Nom, slogan et couleur</h2>
                    <div class="gw-field">
                        <label class="gw-lbl" for="gwName">Nom de la boutique</label>
                        <input class="gw-input" id="gwName" name="name" maxlength="{{ max(30, mb_strlen($shop->name)) }}" data-max="30" required value="{{ old('name', $shop->name) }}" aria-describedby="gwNameCount gwNameErr" @error('name') aria-invalid="true" @enderror>
                        <span class="gw-count" id="gwNameCount"></span>
                        <p class="gw-err" id="gwNameErr" role="alert">@error('name'){{ $message }}@enderror</p>
                    </div>
                    <div class="gw-field">
                        <label class="gw-lbl" for="gwSlogan">Slogan</label>
                        <input class="gw-input" id="gwSlogan" name="slogan" maxlength="60" value="{{ old('slogan', $published['cover']['slogan']) }}" placeholder="Ex. Montres et parfums authentiques à Conakry" aria-describedby="gwSloganCount">
                        <span class="gw-count" id="gwSloganCount"></span>
                    </div>
                    <div class="gw-row"><div class="gw-row-txt"><b>Écrire le nom sur la couverture</b><span>Sinon il s’affiche sous la photo</span></div><button type="button" class="gw-sw" role="switch" aria-checked="true" id="gwNameOn" aria-label="Écrire le nom sur la couverture"><span></span></button></div>
                    <div><span class="gw-lbl" id="gwColorL">Couleur</span><div class="gw-swatches" role="radiogroup" aria-labelledby="gwColorL" id="gwColors"></div></div>
                    <div class="gw-actions">
                        <button type="button" class="gw-btn gw-btn-line" id="gwIdReset">Annuler</button>
                        <button type="submit" class="gw-btn gw-btn-amber" style="flex:1">Enregistrer et publier</button>
                    </div>
                </section>
            </div>

            <div class="gw-stack gw-sticky">
                <section class="gw-card" aria-labelledby="gwPrevH">
                    <div class="gw-card-hd">
                        <h2 id="gwPrevH">Aperçu en grand</h2>
                        <div class="gw-seg" role="group" aria-label="Appareil" data-seg="device" style="min-width:240px"><button type="button" data-v="desktop">Ordinateur</button><button type="button" data-v="phone">Téléphone</button></div>
                    </div>
                    <div class="gw-stage" id="gwStage"></div>
                </section>
                <div class="gw-two">
                    <section class="gw-card" aria-labelledby="gwWaH"><h3 id="gwWaH">Partagée sur WhatsApp</h3><div id="gwWaCard"></div></section>
                    <section class="gw-card" aria-labelledby="gwSearchH"><h3 id="gwSearchH">Dans la recherche Shopio</h3><div id="gwSearchRow"></div></section>
                </div>
            </div>
        </form>
    </section>

    {{-- ═══════════════════════ 2. PARTAGE ═══════════════════════ --}}
    <section class="gw-tabpanel" id="tab-partage" role="tabpanel" aria-labelledby="t-partage" @if($tab !== 'partage') hidden @endif>
        <div class="gw-grid2">
            <div class="gw-stack">
                <section class="gw-card" aria-labelledby="gwPLH">
                    <h2 id="gwPLH">Lien d’un produit</h2>
                    @if($products->isEmpty())
                    <div class="gw-empty">Aucun produit en ligne pour l’instant. <a href="{{ route('products.create') }}">Ajouter un produit</a></div>
                    @else
                    <div class="gw-picks" role="group" aria-label="Produit à partager" id="gwPicks"></div>
                    <label class="gw-sr" for="gwPickAll">Choisir un autre produit</label>
                    <select class="gw-select" id="gwPickAll"><option value="">Autre produit…</option>@foreach($products as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }} · {{ $p['priceLabel'] }}</option>@endforeach</select>
                    <div class="gw-link"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg><input id="gwPLink" readonly aria-label="Lien du produit"><button type="button" class="gw-btn gw-btn-blue gw-btn-sm" data-copy="#gwPLink">Copier</button></div>
                    <div class="gw-actions">
                        <button type="button" class="gw-btn gw-btn-line gw-btn-sm" id="gwPQr">QR code</button>
                        <a class="gw-btn gw-btn-wa gw-btn-sm" id="gwPWa" target="_blank" rel="noopener">Partager sur WhatsApp</a>
                    </div>
                    <div class="gw-field"><label class="gw-lbl" for="gwPText">Texte de partage prêt</label><textarea class="gw-input" id="gwPText" rows="4"></textarea></div>
                    <div class="gw-wa" aria-label="Aperçu du message WhatsApp"><div class="gw-wa-bubble" id="gwPBubble"></div></div>
                    @endif
                </section>
            </div>
            <div class="gw-stack">
                <section class="gw-card" aria-labelledby="gwQrH">
                    <h2 id="gwQrH">QR code</h2>
                    <div class="gw-seg" role="group" aria-label="Cible du QR code" data-seg="qr"><button type="button" data-v="shop">Boutique</button><button type="button" data-v="cat" @if(!count($categoryLinks)) disabled @endif>Catégorie</button><button type="button" data-v="product" @if($products->isEmpty()) disabled @endif>Produit</button></div>
                    <div id="gwQrCat" hidden><label class="gw-sr" for="gwQrCatSel">Catégorie</label><select class="gw-select" id="gwQrCatSel">@foreach($categoryLinks as $c)<option value="{{ $c['slug'] }}">{{ $c['name'] }} ({{ $c['count'] }})</option>@endforeach</select></div>
                    <div id="gwQrProd" hidden><label class="gw-sr" for="gwQrProdSel">Produit</label><select class="gw-select" id="gwQrProdSel">@foreach($products as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>@endforeach</select></div>
                    <div class="gw-qr-wrap">
                        <div class="gw-qr"><img id="gwQrImg" alt="QR code" width="176" height="176"><span class="gw-qr-logo" aria-hidden="true">@if($logoUrl)<img src="{{ $logoUrl }}" alt="">@else{{ $initials }}@endif</span></div>
                        <div class="gw-stack" style="flex:1 1 200px;gap:10px">
                            <p class="gw-hint" id="gwQrHint">Mène à votre boutique. Le QR mène toujours à la version en ligne de la page choisie.</p>
                            <div class="gw-actions">
                                <button type="button" class="gw-btn gw-btn-blue gw-btn-sm" id="gwQrPng">Télécharger (PNG)</button>
                                <a class="gw-btn gw-btn-line gw-btn-sm" id="gwQrPoster" target="_blank" rel="noopener">Affiche A5</a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <section class="gw-card" style="margin-top:14px" aria-labelledby="gwTrH">
            <div class="gw-card-hd"><h2 id="gwTrH">Liens suivis</h2><span class="gw-pill gw-pill-grey">30 derniers jours · aucun chiffre n’est inventé</span></div>
            <p class="gw-hint">Partagez ces liens : chaque visite et chaque commande est rattachée au bon canal.</p>
            <div class="gw-table-wrap">
                <table class="gw-table">
                    <thead><tr><th scope="col">Lien</th><th scope="col">Canal</th><th scope="col" class="gw-num">Visites</th><th scope="col" class="gw-num">Commandes</th></tr></thead>
                    <tbody>
                    @php $chanColors = ['whatsapp' => '#15803D', 'facebook' => '#1668D8', 'instagram' => '#9D174D', 'tiktok' => '#16162A', 'qr' => '#92400E']; @endphp
                    @foreach($tracked as $t)
                    <tr>
                        <td style="min-width:220px"><div class="gw-link" style="padding:4px 4px 4px 10px"><input value="{{ $t['url'] }}" readonly aria-label="Lien {{ $t['label'] }}" id="gwTr{{ $loop->index }}"><button type="button" class="gw-btn gw-btn-line gw-btn-sm" data-copy="#gwTr{{ $loop->index }}">Copier</button></div></td>
                        <td><span class="gw-chan" style="background:{{ $chanColors[$t['source']] ?? '#45455E' }}">{{ $t['source'] === 'qr' ? 'Affiche / QR' : $t['label'] }}</span></td>
                        <td class="gw-num">{{ number_format($t['visits'], 0, ',', ' ') }}</td>
                        <td class="gw-num">{{ number_format($t['orders'], 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="gw-card" style="margin-top:14px" aria-labelledby="gwCaH">
            <h2 id="gwCaH">Lien de campagne</h2>
            <p class="gw-hint">Une remise, une date de fin : Shopio crée un vrai code promo et un lien vers la catégorie choisie. Le code s’applique à toute la boutique.</p>
            @if(!$isPro)
            <div class="gw-lock"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4"/></svg><div><b>Réservé au plan Pro</b><p class="gw-hint" style="color:inherit">Les liens de campagne utilisent les codes promo. <a href="{{ route('boutique.subscription.upgrade') }}">Passer au plan Pro</a></p></div></div>
            @else
            <form method="POST" action="{{ route('boutique.grow.campaign') }}" class="gw-grid2" style="grid-template-columns:minmax(0,1.4fr) minmax(0,.8fr) minmax(0,1fr) auto;align-items:end" id="gwCampForm" novalidate>
                @csrf
                <div class="gw-field"><label class="gw-lbl" for="gwCaCat">Catégorie</label><select class="gw-select" id="gwCaCat" name="category"><option value="">Toute la boutique</option>@foreach($categoryLinks as $c)<option value="{{ $c['slug'] }}" @selected(old('category') === $c['slug'])>{{ $c['name'] }}</option>@endforeach</select></div>
                <div class="gw-field"><label class="gw-lbl" for="gwCaPct">Remise %</label><input class="gw-input" id="gwCaPct" name="discount" type="number" min="1" max="90" inputmode="numeric" value="{{ old('discount', 10) }}" aria-describedby="gwCaPctErr" @error('discount') aria-invalid="true" @enderror></div>
                <div class="gw-field"><label class="gw-lbl" for="gwCaEnd">Date de fin</label><input class="gw-input" id="gwCaEnd" name="ends" type="date" min="{{ now('Africa/Conakry')->toDateString() }}" value="{{ old('ends') }}" aria-describedby="gwCaEndErr" @error('ends') aria-invalid="true" @enderror></div>
                <button type="submit" class="gw-btn gw-btn-blue">Créer</button>
                <p class="gw-err" id="gwCaPctErr" role="alert" style="grid-column:1/-1">@error('discount'){{ $message }}@enderror</p>
                <p class="gw-err" id="gwCaEndErr" role="alert" style="grid-column:1/-1;margin-top:-10px">@error('ends'){{ $message }}@enderror @error('campaign'){{ $message }}@enderror</p>
            </form>
            @endif
            @if(count($campaigns))
            <div class="gw-stack" style="gap:8px">
                @foreach($campaigns as $c)
                <div class="gw-camp">
                    <div class="gw-camp-hd">
                        <span class="gw-code">{{ $c['code'] }}</span>
                        <b>−{{ $c['value'] }} · {{ $c['target'] }}</b>
                        <span class="gw-pill {{ $c['state'] === 'Active' ? 'gw-pill-amber' : 'gw-pill-grey' }}">{{ $c['state'] === 'Active' ? 'Expire le ' . $c['expires'] : $c['state'] }}</span>
                        <span class="gw-pill gw-pill-grey">{{ $c['uses'] }} utilisation{{ $c['uses'] > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="gw-link"><input id="gwCa{{ $c['id'] }}" value="{{ $c['url'] }}" readonly aria-label="Lien de la campagne {{ $c['code'] }}"><button type="button" class="gw-btn gw-btn-blue gw-btn-sm" data-copy="#gwCa{{ $c['id'] }}">Copier</button>
                        <form method="POST" action="{{ route('boutique.grow.campaign.stop', $c['id']) }}" onsubmit="return confirm('Arrêter cette campagne ? Le code {{ $c['code'] }} ne fonctionnera plus.')">@csrf<button type="submit" class="gw-btn gw-btn-danger gw-btn-sm">Arrêter</button></form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </section>
    </section>

    {{-- ═══════════════════════ 3. PERSONNALISATION ═══════════════════════ --}}
    <section class="gw-tabpanel" id="tab-personnalisation" role="tabpanel" aria-labelledby="t-personnalisation" @if($tab !== 'personnalisation') hidden @endif>
        <form method="POST" action="{{ route('boutique.grow.personalize') }}" id="gwPerForm" class="gw-stack" novalidate>
            @csrf @method('PUT')
            <input type="hidden" name="payload" id="gwPerPayload">
            <section class="gw-card" aria-labelledby="gwPmH">
                <div class="gw-card-hd"><h2 id="gwPmH">Modèle de page</h2><span class="gw-hint">Même parcours d’achat, styles différents · à essayer sur votre vitrine sans rien publier</span></div>
                <div class="gw-pms" id="gwPms"></div>
            </section>
            <section class="gw-card" aria-labelledby="gwTplH">
                <div class="gw-card-hd"><h2 id="gwTplH">Modèles</h2><span class="gw-hint">Ordre des blocs, affichage des produits et couleur</span></div>
                <div class="gw-tpls" id="gwTpls"></div>
            </section>
            <div class="gw-grid2">
                <section class="gw-card" aria-labelledby="gwSlH">
                    <div class="gw-card-hd"><h2 id="gwSlH">Bannière en carrousel</h2><span class="gw-hint">1 à 4 diapos · une toutes les 3,8 s</span></div>
                    <div class="gw-page" style="border-radius:18px;overflow:hidden;--sf-accent:{{ $workingAccent }}" id="gwCarousel"></div>
                    <div class="gw-slides" id="gwSlides"></div>
                    <button type="button" class="gw-btn gw-btn-line" id="gwAddSlide">+ Ajouter une diapo</button>
                    <div class="gw-adder" id="gwSlideAdder" hidden>
                        <span class="gw-lbl" style="margin:0">Nouvelle diapo</span>
                        <div class="gw-seg" role="group" aria-label="Type de diapo" data-seg="slidekind"><button type="button" data-v="product">Photo produit</button><button type="button" data-v="gradient">Dégradé</button><button type="button" data-v="photo">Ma photo</button></div>
                        <div id="gwAddProduct"><label class="gw-sr" for="gwAddProductSel">Produit</label><select class="gw-select" id="gwAddProductSel">@foreach($products->whereNotNull('photo') as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>@endforeach</select></div>
                        <div id="gwAddGradient" hidden><label class="gw-sr" for="gwAddGradSel">Dégradé</label><select class="gw-select" id="gwAddGradSel"><option value="night">Dégradé nuit</option><option value="gold">Dégradé or</option><option value="blue">Dégradé bleu</option></select></div>
                        <div id="gwAddPhoto" hidden><label class="gw-btn gw-btn-line" style="width:100%" for="gwSlideFile">Choisir une photo (5 Mo max)</label><input type="file" id="gwSlideFile" accept="image/jpeg,image/png,image/webp" class="gw-sr"><p class="gw-hint" id="gwSlideMsg" aria-live="polite"></p></div>
                        <div class="gw-actions"><button type="button" class="gw-btn gw-btn-blue gw-btn-sm" id="gwSlideOk">Ajouter</button><button type="button" class="gw-btn gw-btn-line gw-btn-sm" id="gwSlideCancel">Annuler</button></div>
                    </div>
                </section>
                <section class="gw-card" aria-labelledby="gwColH">
                    <div class="gw-card-hd"><h2 id="gwColH">Collections</h2><span class="gw-hint">{{ SF::COLLECTIONS_MAX }} au maximum</span></div>
                    <div class="gw-cols" id="gwCols"></div>
                    <div class="gw-adder">
                        <span class="gw-lbl" style="margin:0">Créer une collection</span>
                        <div style="display:flex;gap:6px;flex-wrap:wrap" aria-label="Idées">
                            <button type="button" class="gw-btn gw-btn-line gw-btn-sm" data-col-idea="manual" data-col-name="Idées cadeaux">Idées cadeaux</button>
                            <button type="button" class="gw-btn gw-btn-line gw-btn-sm" data-col-idea="under" data-col-name="Petits prix">Petits prix</button>
                            <button type="button" class="gw-btn gw-btn-line gw-btn-sm" data-col-idea="new" data-col-name="Nouveautés de la semaine">Nouveautés</button>
                        </div>
                        <div class="gw-field"><label class="gw-sr" for="gwColName">Nom de la collection</label><input class="gw-input" id="gwColName" maxlength="40" placeholder="Nom de la collection" aria-describedby="gwColErr"></div>
                        <div class="gw-field"><label class="gw-sr" for="gwColKind">Type</label><select class="gw-select" id="gwColKind">@foreach(SF::COLLECTION_KINDS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select></div>
                        <div class="gw-field" id="gwColMaxF" hidden><label class="gw-lbl" for="gwColMax">Prix maximum ({{ $shop->currency ?? 'GNF' }})</label><input class="gw-input" id="gwColMax" type="number" min="1" inputmode="numeric"></div>
                        <p class="gw-err" id="gwColErr" role="alert"></p>
                        <button type="button" class="gw-btn gw-btn-blue gw-btn-sm" id="gwColAdd" style="align-self:flex-start">Créer la collection</button>
                    </div>
                </section>
            </div>
            <section class="gw-card" aria-labelledby="gwPubH">
                <div class="gw-card-hd">
                    <h2 id="gwPubH">Brouillon et programmation</h2>
                    <span class="gw-pill {{ ['published' => 'gw-pill-ok', 'draft' => 'gw-pill-grey', 'scheduled' => 'gw-pill-amber'][$status[0]] }}">{{ $status[1] }}</span>
                </div>
                <p class="gw-hint">{{ $status[2] }}</p>
                <div class="gw-modes" role="radiogroup" aria-label="Quand publier">
                    <label class="gw-mode"><input type="radio" name="mode" value="now" @checked(old('mode', 'now') === 'now')><b>Publier maintenant</b><span>Vos clients voient les changements tout de suite</span></label>
                    <label class="gw-mode"><input type="radio" name="mode" value="draft" @checked(old('mode') === 'draft')><b>Garder en brouillon</b><span>Rien ne change pour vos clients</span></label>
                    <label class="gw-mode"><input type="radio" name="mode" value="schedule" @checked(old('mode') === 'schedule')><b>Programmer</b><span>Publication automatique à l’heure choisie</span></label>
                </div>
                <div class="gw-field" id="gwWhenF" @if(old('mode') !== 'schedule') hidden @endif>
                    <label class="gw-lbl" for="gwWhen">Date et heure (heure de Conakry)</label>
                    <input class="gw-input" type="datetime-local" id="gwWhen" name="publish_at" value="{{ old('publish_at', $shop->storefront_publish_at?->timezone('Africa/Conakry')->format('Y-m-d\TH:i')) }}" min="{{ now('Africa/Conakry')->addMinutes(5)->format('Y-m-d\TH:i') }}" aria-describedby="gwWhenErr" style="max-width:320px" @error('publish_at') aria-invalid="true" @enderror>
                    <p class="gw-err" id="gwWhenErr" role="alert">@error('publish_at'){{ $message }}@enderror</p>
                </div>
                <div class="gw-actions">
                    <button type="submit" class="gw-btn gw-btn-amber" id="gwPerSubmit">Publier maintenant</button>
                    @if($status[0] !== 'published')
                    <button type="submit" form="gwDiscard" class="gw-btn gw-btn-danger">Supprimer le brouillon</button>
                    @endif
                    <a class="gw-btn gw-btn-line" href="{{ route('boutique.grow', ['onglet' => 'qualite']) }}#apercu">Faire relire avec un lien d’aperçu</a>
                </div>
            </section>
        </form>
        <form method="POST" action="{{ route('boutique.grow.draft.discard') }}" id="gwDiscard" onsubmit="return confirm('Supprimer le brouillon ? La version publiée reste en ligne.')">@csrf @method('DELETE')</form>
    </section>

    {{-- ═══════════════════════ 4. CONVERSION ═══════════════════════ --}}
    <section class="gw-tabpanel" id="tab-conversion" role="tabpanel" aria-labelledby="t-conversion" @if($tab !== 'conversion') hidden @endif>
        @php
            $demo = $products->first(fn ($p) => $p['photo']) ?? $products->first();
            $rt = $responseTime;
            $rtOk = $rt['samples'] >= \App\Support\Growth::RESPONSE_MIN_SAMPLES && $rt['minutes'] !== null && $rt['minutes'] <= 60;
        @endphp
        <form method="POST" action="{{ route('boutique.grow.conversion') }}" class="gw-conv" id="gwConvForm">
            @csrf @method('PUT')
            @foreach(['wa_mode' => $set['wa_mode'], 'proof_delivered' => $set['proof_delivered'], 'proof_response' => $set['proof_response'], 'trending' => $has('trending'), 'lowstock' => $has('lowstock')] as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v ? 1 : 0 }}" id="c_{{ $k }}">
            @endforeach
            <div class="gw-tilt" aria-label="Aperçu d’une fiche produit sur téléphone">
                <div class="gw-phone2">
                    <div class="gw-ph-scroll"><div class="gw-ph-inner">
                        <div class="gw-ph-img">@if($demo && $demo['photo'])<img src="{{ $demo['photo'] }}" alt="">@else<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 5h18v14H3zM3 15l5-5 4 4 3-3 6 6"/></svg>@endif</div>
                        <div class="gw-ph-body">
                            <span style="font-size:11.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#62627C">{{ $demo['cat'] ?? '' }}</span>
                            <h4>{{ $demo['name'] ?? 'Votre premier produit' }}</h4>
                            <div class="gw-ph-price">{{ $demo['priceLabel'] ?? '' }}</div>
                            <div class="gw-ph-proofs" id="gwPhProofs"></div>
                            <p>{{ $demo && $demo['desc'] !== '' ? $demo['desc'] : '[Description du produit]' }}</p>
                            <p style="font-weight:700;color:#16162A">Paiement à la livraison, en espèces, au livreur.</p>
                        </div>
                    </div></div>
                    <div class="gw-ph-bar"><span class="gw-ph-buy" id="gwPhBuy">Commander</span><span class="gw-ph-nego" id="gwPhNego">Négocier</span></div>
                </div>
            </div>
            <div class="gw-stack">
                <section class="gw-card" aria-labelledby="gwPrH">
                    <h2 id="gwPrH">Preuves de confiance</h2>
                    <p class="gw-hint">Affichées seulement quand elles sont vraies. Le badge vérifié et la note ne peuvent pas être masqués.</p>
                    <div class="gw-proof is-locked"><span class="gw-proof-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg></span><div class="gw-row-txt"><b>Boutique vérifiée</b><span>{{ $shop->is_approved ? 'Votre boutique est vérifiée par Shopio' : 'En attente de vérification par Shopio' }}</span></div><span class="gw-pill {{ $shop->is_approved ? 'gw-pill-ok' : 'gw-pill-grey' }}">{{ $shop->is_approved ? 'Toujours affiché' : 'En attente' }}</span></div>
                    <div class="gw-proof"><span class="gw-proof-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 7h12v10H2zM14 10h4l4 4v3h-8M6.5 17.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM17.5 17.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/></svg></span><div class="gw-row-txt"><b class="gw-num">{{ $delivered }} commande{{ $delivered > 1 ? 's' : '' }} livrée{{ $delivered > 1 ? 's' : '' }}</b><span>{{ $delivered >= \App\Support\Growth::DELIVERED_MIN ? 'Affiché sur la vitrine' : 'Affiché à partir de ' . \App\Support\Growth::DELIVERED_MIN . ' commandes livrées' }}</span></div><button type="button" class="gw-sw" role="switch" data-conv="proof_delivered" aria-checked="{{ $set['proof_delivered'] ? 'true' : 'false' }}" aria-label="Afficher le nombre de commandes livrées"><span></span></button></div>
                    <div class="gw-proof"><span class="gw-proof-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 7v5l3 2M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20"/></svg></span><div class="gw-row-txt"><b>Répond en moins d’1 h</b><span>@if($rt['samples'] < \App\Support\Growth::RESPONSE_MIN_SAMPLES)Pas encore assez de messages (30 derniers jours)@elseif($rtOk)Délai habituel : {{ $rt['minutes'] }} min · affiché sur la vitrine @else Délai habituel : {{ $rt['minutes'] >= 120 ? round($rt['minutes'] / 60) . ' h' : $rt['minutes'] . ' min' }} · pas affiché @endif</span></div><button type="button" class="gw-sw" role="switch" data-conv="proof_response" aria-checked="{{ $set['proof_response'] ? 'true' : 'false' }}" aria-label="Afficher le délai de réponse"><span></span></button></div>
                    <div class="gw-proof is-locked"><span class="gw-proof-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 3 6.5 7 .8-5.2 4.8 1.4 7L12 17.6 5.8 21l1.4-7L2 9.3l7-.8z"/></svg></span><div class="gw-row-txt"><b>{{ $reviewCount >= \App\Support\Growth::RATING_MIN ? 'Note ' . number_format($reviewAvg, 1, ',', ' ') : 'Note' }}</b><span>{{ $reviewCount >= \App\Support\Growth::RATING_MIN ? $reviewCount . ' avis de clients livrés' : 'Pas encore de données · ' . \App\Support\Growth::RATING_MIN . ' avis minimum (' . $reviewCount . ' pour l’instant)' }}</span></div><span class="gw-pill gw-pill-grey"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 11h14v10H5zM8 11V7a4 4 0 0 1 8 0v4"/></svg> Verrouillée</span></div>
                </section>
                <section class="gw-card" aria-labelledby="gwWaModeH">
                    <div class="gw-card-hd"><h2 id="gwWaModeH">Mode WhatsApp</h2><button type="button" class="gw-sw is-green" role="switch" data-conv="wa_mode" aria-checked="{{ $set['wa_mode'] ? 'true' : 'false' }}" aria-label="Mode WhatsApp" @if(!$shop->phone) disabled @endif><span></span></button></div>
                    <p class="gw-hint">Le bouton devient « Commander sur WhatsApp ». La commande reste enregistrée dans Shopio, puis le client l’envoie à la boutique avec un message déjà rempli.</p>
                    @if(!$shop->phone)<p class="gw-warn">Ajoutez d’abord le numéro WhatsApp de la boutique dans <a href="{{ route('shop.edit', $shop) }}?etape=2">Ma boutique</a>.</p>@endif
                    <div class="gw-wa"><div class="gw-wa-bubble"><div class="gw-wa-msg">Bonjour {{ $shop->name }}, je viens de commander sur Shopio : {{ $demo['name'] ?? '[Produit]' }} ({{ $demo['priceLabel'] ?? '[prix]' }}). Commande n° [n]. {{ $demo['url'] ?? $publicUrl }}</div><span class="gw-wa-time">Message envoyé par le client</span></div></div>
                </section>
                <section class="gw-card" aria-labelledby="gwDynH">
                    <h2 id="gwDynH">Sections dynamiques</h2>
                    <div class="gw-row"><div class="gw-row-txt"><b>Les plus vus cette semaine <span class="gw-pill gw-pill-auto">AUTO</span></b><span>{{ $trendingCount ? $trendingCount . ' produit' . ($trendingCount > 1 ? 's' : '') . ' vu' . ($trendingCount > 1 ? 's' : '') . ' cette semaine' : 'Aucune vue cette semaine : la section reste cachée jusqu’aux premières visites' }}</span></div><button type="button" class="gw-sw" role="switch" data-conv="trending" aria-checked="{{ $has('trending') ? 'true' : 'false' }}" aria-label="Afficher les plus vus"><span></span></button></div>
                    <div class="gw-row"><div class="gw-row-txt"><b>Bientôt en rupture <span class="gw-pill gw-pill-auto">AUTO</span></b><span>{{ $lowStockCount ? $lowStockCount . ' produit' . ($lowStockCount > 1 ? 's' : '') . ' à 2 unités ou moins (stock réel)' : 'Aucun produit à 2 unités ou moins : la section reste cachée' }}</span></div><button type="button" class="gw-sw" role="switch" data-conv="lowstock" aria-checked="{{ $has('lowstock') ? 'true' : 'false' }}" aria-label="Afficher bientôt en rupture"><span></span></button></div>
                </section>
                <button type="submit" class="gw-btn gw-btn-amber" style="align-self:flex-start">Enregistrer et publier</button>
            </div>
        </form>
    </section>

    {{-- ═══════════════════════ 5. QUALITÉ ═══════════════════════ --}}
    <section class="gw-tabpanel" id="tab-qualite" role="tabpanel" aria-labelledby="t-qualite" @if($tab !== 'qualite') hidden @endif>
        <div class="gw-stack">
            <div class="gw-tiles">
                <div class="gw-tile"><b>{{ number_format($visits['today'], 0, ',', ' ') }}</b><span>Visites aujourd’hui</span></div>
                <div class="gw-tile"><b>{{ number_format($visits['week'], 0, ',', ' ') }}</b><span>Sur 7 jours</span></div>
                <div class="gw-tile"><b>{{ number_format($visits['month'], 0, ',', ' ') }}</b><span>Sur 30 jours</span></div>
                <div class="gw-tile is-night"><b>{{ number_format($visits['total'], 0, ',', ' ') }}</b><span>Au total</span></div>
            </div>
            <div class="gw-grid2">
                <section class="gw-card" aria-labelledby="gwClH">
                    <div class="gw-card-hd"><h2 id="gwClH">Clics par section</h2><span class="gw-pill gw-pill-grey">30 jours</span></div>
                    @if(count($sectionClicks))
                    @php $maxC = max(array_column($sectionClicks, 'clicks')); @endphp
                    <div class="gw-bars">@foreach($sectionClicks as $c)<div class="gw-barrow"><span>{{ $c['label'] }}</span><div class="gw-track" role="img" aria-label="{{ $c['clicks'] }} clics"><i style="width:{{ max(3, round($c['clicks'] / $maxC * 100)) }}%"></i></div><b>{{ number_format($c['clicks'], 0, ',', ' ') }}</b></div>@endforeach</div>
                    @else
                    <div class="gw-empty">Pas encore de clics enregistrés. Ils sont comptés à chaque clic d’un client dans une section de la vitrine.</div>
                    @endif
                </section>
                <section class="gw-card" aria-labelledby="gwRkH">
                    <div class="gw-card-hd"><h2 id="gwRkH">Classement des catégories</h2><span class="gw-pill gw-pill-grey">30 jours</span></div>
                    @if(count($categoryRanking))
                    <div class="gw-rank"><ol>@foreach(array_slice($categoryRanking, 0, 6) as $r)<li><span style="font-weight:700">{{ $r['name'] }}</span><span class="gw-num" style="color:var(--gw-ink3);font-size:12.5px">{{ number_format($r['views'], 0, ',', ' ') }} vue{{ $r['views'] > 1 ? 's' : '' }} · {{ $r['orders'] }} commande{{ $r['orders'] > 1 ? 's' : '' }}</span></li>@endforeach</ol></div>
                    @else
                    <div class="gw-empty">Pas encore de vues de fiches produit sur 30 jours.</div>
                    @endif
                </section>
            </div>
            @if(count($seenNeverOrdered))
            <section class="gw-amberbox" aria-labelledby="gwSeenH">
                <h3 id="gwSeenH">Vus mais jamais commandés</h3>
                <p class="gw-hint" style="color:inherit">Vus au moins 5 fois en 30 jours, sans commande : vérifiez la photo, le prix et la description.</p>
                <div class="gw-grid2" style="gap:8px">@foreach($seenNeverOrdered as $p)<div class="gw-seen">@if($p['thumb'])<img src="{{ $p['thumb'] }}" alt="">@else<i></i>@endif<div><b>{{ $p['name'] }}</b><span class="gw-num">{{ $p['views'] }} vues · 0 commande</span></div><a class="gw-btn gw-btn-line gw-btn-sm" href="{{ $p['edit'] }}">Modifier</a></div>@endforeach</div>
            </section>
            @endif
            <div class="gw-grid2">
                <section class="gw-card" aria-labelledby="gwScH">
                    <h2 id="gwScH">Score de vitrine</h2>
                    <div class="gw-score">
                        <div class="gw-ring" style="--p: {{ $q['score'] }}; --ring: {{ $ringColor }}" role="img" aria-label="Score de vitrine : {{ $q['score'] }} sur 100"><b class="gw-num">{{ $q['score'] }}<small>/100</small></b></div>
                        <p class="gw-hint" style="flex:1 1 160px">{{ $q['done'] }} conseil{{ $q['done'] > 1 ? 's' : '' }} sur {{ count($q['tips']) }} déjà suivi{{ $q['done'] > 1 ? 's' : '' }}. Chaque point vient de votre boutique : rien n’est estimé.</p>
                    </div>
                    <ul class="gw-tips">
                        @foreach(collect($q['tips'])->sortBy('done') as $t)
                        <li class="gw-tip {{ $t['done'] ? 'is-done' : '' }}">
                            <span class="gw-tip-ico" aria-hidden="true">@if($t['done'])<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>@else<b style="font-size:14px">!</b>@endif</span>
                            <div class="gw-tip-txt"><b>{{ $t['title'] }}<span class="gw-sr">{{ $t['done'] ? ' : fait' : ' : à corriger' }}</span></b><span>{{ $t['hint'] }}</span></div>
                            @unless($t['done'])<a class="gw-btn gw-btn-line gw-btn-sm" href="{{ $t['url'] }}">{{ $t['cta'] }}</a>@endunless
                        </li>
                        @endforeach
                    </ul>
                </section>
                <section class="gw-card" id="apercu" aria-labelledby="gwApH">
                    <h2 id="gwApH">Aperçu partageable</h2>
                    <p class="gw-hint">Faites relire votre vitrine avant de la publier : le lien montre la version en préparation (brouillon s’il y en a un), pendant 48 h, à qui le reçoit.</p>
                    @if($previewUrl)
                    <div class="gw-link"><input id="gwApLink" value="{{ $previewUrl }}" readonly aria-label="Lien d’aperçu"><button type="button" class="gw-btn gw-btn-blue gw-btn-sm" data-copy="#gwApLink">Copier</button></div>
                    <p class="gw-hint">Valable jusqu’au {{ $shop->preview_expires_at->timezone('Africa/Conakry')->translatedFormat('j F à H:i') }}.</p>
                    <div class="gw-actions">
                        <a class="gw-btn gw-btn-wa gw-btn-sm" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode('Peux-tu regarder la nouvelle vitrine de ' . $shop->name . ' avant que je la publie ? Ton avis m’aide : ' . $previewUrl) }}">Envoyer sur WhatsApp</a>
                        <a class="gw-btn gw-btn-line gw-btn-sm" href="{{ $previewUrl }}" target="_blank" rel="noopener">Ouvrir</a>
                        <form method="POST" action="{{ route('boutique.grow.preview.disable') }}">@csrf @method('DELETE')<button type="submit" class="gw-btn gw-btn-danger gw-btn-sm">Désactiver</button></form>
                    </div>
                    @else
                    <form method="POST" action="{{ route('boutique.grow.preview') }}">@csrf<button type="submit" class="gw-btn gw-btn-blue">Créer un lien d’aperçu</button></form>
                    @endif
                    <h3 style="margin-top:6px">Avis reçus</h3>
                    @if($feedback->isNotEmpty())
                    <div class="gw-fb">@foreach($feedback as $f)<div class="gw-fb-item"><b>{{ $f['name'] }}</b><small>{{ \Carbon\Carbon::parse($f['at'])->timezone('Africa/Conakry')->translatedFormat('j M, H:i') }}</small><p>{{ $f['message'] }}</p></div>@endforeach</div>
                    @else
                    <div class="gw-empty">Aucun avis pour l’instant. Les personnes qui ouvrent le lien peuvent laisser un avis en bas de la page.</div>
                    @endif
                </section>
            </div>
        </div>
    </section>
    </main>
    <div class="gw-toast" id="gwToast" role="status" hidden></div>
</div>
@endsection

@push('scripts')
@include('vendeur.shop._grow-script')
@endpush
