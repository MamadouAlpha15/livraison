{{--
    Éditeur de page produit par sections et blocs (à la manière d'un thème Shopify).
    Ordinateur : arbre des sections | aperçu | réglages. Téléphone : trois onglets.
    Logique : resources/js/page-builder.js — Données : Vendeur\PageBuilderController@edit ($config)
--}}
@php
    use App\Support\IconLibrary as Ico;
    $dots = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>';
    $more = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg>';
    $undo = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/></svg>';
    $redo = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 14 5-5-5-5"/><path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/></svg>';
@endphp
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<title>Éditeur · {{ $product->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite('resources/js/page-builder.js')
<style>
/* Couleurs Shopio : bleu #1F7CF6, nuit #0B1E3D, encre #16162A, lignes #E3E8F2, fond #F6F9FF */
:root { color-scheme: light;
    --blue: #1F7CF6; --blue2: #1668D8; --tint: #EAF2FF; --night: #0B1E3D; --amber: #F59E0B;
    --ink: #16162A; --ink2: #45455E; --muted: #62627C; --line: #E3E8F2; --soft: #F6F9FF; --stage: #EDF1F8;
    --red: #C2410C; --red-bg: #FFF4ED; --ok: #15803D;
    --font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; }
*, *::before, *::after { box-sizing: border-box; }
html, body { height: 100%; }
body { margin: 0; background: var(--soft); color: var(--ink); font-family: var(--font); font-size: 14px; -webkit-font-smoothing: antialiased; overflow: hidden; }
[x-cloak] { display: none !important; }
button { font: inherit; color: inherit; }
button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible, [contenteditable]:focus-visible { outline: 3px solid rgba(31, 124, 246, .45); outline-offset: 1px; }
.pbe-ico { display: inline-flex; flex: none; }
.pbe-ico svg { display: block; }

/* Barre du haut */
.pbe-top { height: 60px; display: flex; align-items: center; gap: 10px; background: #FFFFFF; border-bottom: 1px solid var(--line);
           padding-left: max(12px, env(safe-area-inset-left, 0px)); padding-right: max(12px, env(safe-area-inset-right, 0px)); }
.pbe-back { display: inline-flex; align-items: center; gap: 6px; color: var(--ink2); text-decoration: none; font-weight: 700; padding: 8px 10px; border-radius: 10px; flex: none; }
.pbe-back:hover { background: var(--soft); color: var(--ink); }
.pbe-title { min-width: 0; flex: 1; display: flex; flex-direction: column; line-height: 1.25; }
.pbe-title strong { font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pbe-status { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pbe-status::before { content: ''; width: 7px; height: 7px; border-radius: 999px; background: var(--ok); flex: none; }
.pbe-status[data-status="dirty"]::before, .pbe-status[data-status="saving"]::before { background: var(--amber); }
.pbe-status[data-status="error"]::before, .pbe-status[data-status="conflict"]::before, .pbe-status[data-status="offline"]::before { background: var(--red); }
.pbe-status[data-status="error"], .pbe-status[data-status="conflict"] { color: var(--red); }
.pbe-seg { display: inline-flex; background: var(--soft); border: 1px solid var(--line); border-radius: 11px; padding: 3px; gap: 2px; flex: none; }
.pbe-seg button { border: 0; background: transparent; border-radius: 8px; padding: 6px 10px; cursor: pointer; color: var(--muted); display: inline-flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; }
.pbe-seg button[aria-pressed="true"] { background: #FFFFFF; color: var(--ink); box-shadow: 0 1px 2px rgba(11, 30, 61, .12); }
.pbe-iconbtn { width: 38px; height: 38px; border: 1px solid var(--line); background: #FFFFFF; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--ink2); flex: none; }
.pbe-iconbtn:hover:not(:disabled) { background: var(--soft); color: var(--ink); }
.pbe-iconbtn:disabled { opacity: .4; cursor: default; }
.pbe-publish { border: 0; background: var(--blue); color: #FFFFFF; font-weight: 800; border-radius: 11px; padding: 0 18px; height: 40px; cursor: pointer; flex: none; display: inline-flex; align-items: center; gap: 8px; }
.pbe-publish:hover:not(:disabled) { background: var(--blue2); }
.pbe-publish:disabled { opacity: .6; cursor: default; }
.pbe-pub-state { font-size: 12px; color: var(--muted); font-weight: 600; white-space: nowrap; }

/* Corps : trois colonnes */
.pbe-body { height: calc(100% - 60px); display: grid; grid-template-columns: 300px minmax(0, 1fr) 340px; }
.pbe-tree, .pbe-panel { background: #FFFFFF; overflow-y: auto; overscroll-behavior: contain; }
.pbe-tree { border-right: 1px solid var(--line); padding: 14px 10px 40px; }
.pbe-panel { border-left: 1px solid var(--line); padding: 16px 16px 60px; }
.pbe-stage { background: var(--stage); display: flex; justify-content: center; padding: 18px; overflow: hidden; min-width: 0; }
.pbe-frame-wrap { width: 100%; height: 100%; background: #FFFFFF; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 2px rgba(11, 30, 61, .08), 0 12px 32px -18px rgba(11, 30, 61, .35); transition: max-width .2s ease; }
.pbe-frame-wrap[data-device="mobile"] { max-width: 390px; border-radius: 26px; border: 6px solid var(--night); }
.pbe-frame-wrap iframe { width: 100%; height: 100%; border: 0; display: block; }
.pbe-h { margin: 4px 6px 10px; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }

/* Arbre */
.pbe-list { display: flex; flex-direction: column; gap: 2px; }
.pbe-item { display: flex; align-items: center; gap: 2px; border-radius: 10px; min-height: 40px; padding: 0 4px; position: relative; }
.pbe-item:hover { background: var(--soft); }
.pbe-item.is-sel { background: var(--tint); }
.pbe-item.is-sel .pbe-name { color: var(--blue2); }
.pbe-item.is-off .pbe-name { color: var(--muted); text-decoration: line-through; text-decoration-thickness: 1px; }
.pbe-item.has-err .pbe-name::after { content: ''; width: 8px; height: 8px; background: var(--red); border-radius: 999px; flex: none; margin-left: auto; }
.pbe-handle { width: 26px; height: 32px; border: 0; background: transparent; color: #A3A8BD; cursor: grab; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; flex: none; touch-action: none; }
.pbe-handle:hover { color: var(--ink2); }
.pbe-name { flex: 1; min-width: 0; border: 0; background: transparent; text-align: left; cursor: pointer; display: flex; align-items: center; gap: 8px; padding: 8px 4px; font-weight: 700; color: var(--ink); }
.pbe-name > span:not(.pbe-ico) { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pbe-name .pbe-ico { color: var(--muted); }
.pbe-act { width: 30px; height: 30px; border: 0; background: transparent; border-radius: 8px; cursor: pointer; color: var(--muted); display: inline-flex; align-items: center; justify-content: center; flex: none; opacity: 0; }
.pbe-item:hover .pbe-act, .pbe-item.is-sel .pbe-act, .pbe-act:focus-visible, .pbe-act[aria-expanded="true"], .pbe-item.is-off .pbe-act--eye { opacity: 1; }
@media (hover: none) { .pbe-act { opacity: 1; } }
.pbe-act:hover { background: #FFFFFF; color: var(--ink); }
.pbe-blocks { display: flex; flex-direction: column; gap: 2px; margin-left: 18px; padding-left: 8px; border-left: 1px solid var(--line); }
.pbe-blocks .pbe-name { font-weight: 600; }
.pbe-add { border: 0; background: transparent; color: var(--blue2); font-weight: 700; display: inline-flex; align-items: center; gap: 6px; padding: 8px 10px; border-radius: 10px; cursor: pointer; }
.pbe-add:hover { background: var(--tint); }
.pbe-add--block { margin-left: 26px; font-size: 13px; }
.pbe-add--section { width: 100%; justify-content: center; border: 1px dashed #B9C9E6; margin-top: 12px; padding: 12px; }
.pbe-ghost { opacity: .35; }
.pbe-menu { position: absolute; right: 4px; top: 100%; z-index: 30; background: #FFFFFF; border: 1px solid var(--line); border-radius: 12px; box-shadow: 0 16px 36px -14px rgba(11, 30, 61, .35); padding: 6px; min-width: 190px; display: flex; flex-direction: column; }
.pbe-menu button { border: 0; background: transparent; text-align: left; padding: 9px 10px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.pbe-menu button:hover:not(:disabled) { background: var(--soft); }
.pbe-menu button:disabled { color: #A3A8BD; cursor: default; }
.pbe-menu .is-danger { color: var(--red); }
.pbe-menu-wrap { position: relative; }
.pbe-menu--blocks { left: 26px; right: auto; top: 100%; }
.pbe-menu small { color: var(--muted); font-weight: 600; margin-left: auto; }
.pbe-alert { margin: 0 6px 12px; background: var(--red-bg); color: var(--red); border-radius: 10px; padding: 10px 12px; font-weight: 600; font-size: 13px; }

/* Réglages */
.pbe-panel-head { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px; }
.pbe-panel-head h2 { margin: 0; font-size: 17px; font-weight: 800; line-height: 1.3; }
.pbe-crumb { font-size: 12px; color: var(--muted); font-weight: 600; margin-bottom: 2px; }
.pbe-desc { margin: -6px 0 16px; color: var(--muted); font-size: 13px; line-height: 1.5; }
.pbe-fields { display: flex; flex-direction: column; gap: 16px; }
.pbe-field label, .pbe-lbl { display: block; font-weight: 700; font-size: 13px; margin-bottom: 6px; color: var(--ink); }
.pbe-fh { margin: 10px 0 -4px; padding-top: 16px; border-top: 1px solid var(--line); font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
.pbe-input, .pbe-select, .pbe-textarea { width: 100%; border: 1px solid #CBD3E1; border-radius: 10px; padding: 10px 12px; font: inherit; color: var(--ink); background: #FFFFFF; }
.pbe-textarea { min-height: 96px; resize: vertical; line-height: 1.5; }
.pbe-input:focus, .pbe-select:focus, .pbe-textarea:focus { border-color: var(--blue); outline: none; box-shadow: 0 0 0 3px rgba(31, 124, 246, .18); }
.pbe-field.has-err .pbe-input, .pbe-field.has-err .pbe-select, .pbe-field.has-err .pbe-textarea, .pbe-field.has-err .pbe-rte { border-color: var(--red); }
.pbe-info { margin: 6px 0 0; color: var(--muted); font-size: 12px; line-height: 1.45; }
.pbe-err { margin: 6px 0 0; color: var(--red); font-size: 12px; font-weight: 700; }
.pbe-range { display: flex; align-items: center; gap: 10px; }
.pbe-range input[type="range"] { flex: 1; accent-color: var(--blue); }
.pbe-range output { min-width: 58px; text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--ink2); }
.pbe-field label.pbe-switch, .pbe-switch { display: flex; align-items: center; justify-content: space-between; gap: 12px; cursor: pointer; margin: 0; }
.pbe-switch span { font-weight: 700; font-size: 13px; }
.pbe-switch input { appearance: none; width: 42px; height: 24px; border-radius: 999px; background: #CBD3E1; position: relative; cursor: pointer; flex: none; margin: 0; transition: background .15s; }
.pbe-switch input::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 999px; background: #FFFFFF; transition: transform .15s; box-shadow: 0 1px 2px rgba(0, 0, 0, .2); }
.pbe-switch input:checked { background: var(--blue); }
.pbe-switch input:checked::after { transform: translateX(18px); }
.pbe-color { display: flex; gap: 8px; }
.pbe-color input[type="color"] { width: 44px; height: 42px; border: 1px solid #CBD3E1; border-radius: 10px; padding: 3px; background: #FFFFFF; cursor: pointer; flex: none; }
.pbe-schemes { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.pbe-scheme { border: 2px solid var(--line); border-radius: 12px; padding: 0; cursor: pointer; background: #FFFFFF; overflow: hidden; text-align: left; display: flex; flex-direction: column; align-items: stretch; }
.pbe-scheme[aria-checked="true"] { border-color: var(--blue); }
.pbe-scheme-sw { height: 44px; display: flex; align-items: center; gap: 6px; padding: 0 10px; font-weight: 800; font-size: 15px; }
.pbe-scheme-sw i { width: 22px; height: 10px; border-radius: 999px; display: inline-block; }
.pbe-scheme span:last-child { display: block; flex: 1; padding: 6px 10px; font-size: 12px; font-weight: 700; color: var(--ink2); border-top: 1px solid var(--line); }
.pbe-rte-bar { display: flex; gap: 2px; border: 1px solid #CBD3E1; border-bottom: 0; border-radius: 10px 10px 0 0; padding: 4px; background: var(--soft); }
.pbe-rte-bar button { border: 0; background: transparent; min-width: 32px; height: 30px; border-radius: 7px; cursor: pointer; font-weight: 800; color: var(--ink2); }
.pbe-rte-bar button:hover { background: #FFFFFF; color: var(--ink); }
.pbe-rte { border: 1px solid #CBD3E1; border-radius: 0 0 10px 10px; padding: 10px 12px; min-height: 110px; max-height: 320px; overflow-y: auto; line-height: 1.55; background: #FFFFFF; }
.pbe-rte:focus { border-color: var(--blue); outline: none; box-shadow: 0 0 0 3px rgba(31, 124, 246, .18); }
.pbe-rte p { margin: 0 0 .6em; }
.pbe-image { border: 1px dashed #B9C9E6; border-radius: 12px; padding: 10px; display: flex; align-items: center; gap: 12px; background: var(--soft); }
.pbe-image img { width: 64px; height: 64px; object-fit: cover; border-radius: 9px; flex: none; background: #FFFFFF; }
.pbe-image-empty { width: 64px; height: 64px; border-radius: 9px; background: #FFFFFF; display: flex; align-items: center; justify-content: center; color: #A3A8BD; flex: none; }
.pbe-image-act { display: flex; flex-direction: column; gap: 4px; align-items: flex-start; }
.pbe-link { border: 0; background: transparent; padding: 2px 0; color: var(--blue2); font-weight: 700; cursor: pointer; }
.pbe-link--danger { color: var(--red); }
.pbe-icons { display: grid; grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); gap: 4px; max-height: 196px; overflow-y: auto; border: 1px solid var(--line); border-radius: 10px; padding: 6px; }
.pbe-icons button { height: 40px; border: 1px solid transparent; background: transparent; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--ink2); }
.pbe-icons button:hover { background: var(--soft); }
.pbe-icons button[aria-pressed="true"] { border-color: var(--blue); background: var(--tint); color: var(--blue2); }
.pbe-products { display: flex; flex-direction: column; gap: 4px; max-height: 260px; overflow-y: auto; border: 1px solid var(--line); border-radius: 10px; padding: 6px; }
.pbe-product { display: flex; align-items: center; gap: 10px; padding: 6px; border-radius: 8px; cursor: pointer; font-weight: 600; }
.pbe-product:hover { background: var(--soft); }
.pbe-product img, .pbe-product i { width: 34px; height: 34px; border-radius: 7px; object-fit: cover; background: var(--soft); flex: none; }
.pbe-product input { accent-color: var(--blue); width: 16px; height: 16px; margin: 0 0 0 auto; flex: none; }
.pbe-empty { text-align: center; padding: 40px 12px; color: var(--muted); }
.pbe-empty strong { display: block; color: var(--ink); font-size: 15px; margin-bottom: 6px; }
.pbe-panel-foot { margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; gap: 14px; flex-wrap: wrap; }

/* Bibliothèque des sections */
.pbe-modal { position: fixed; inset: 0; z-index: 50; background: rgba(11, 30, 61, .45); display: flex; align-items: center; justify-content: center; padding: 16px; }
.pbe-modal-box { background: #FFFFFF; border-radius: 18px; width: min(760px, 100%); max-height: min(720px, 100%); display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 30px 60px -20px rgba(11, 30, 61, .5); }
.pbe-modal-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--line); }
.pbe-modal-head h2 { margin: 0; font-size: 18px; font-weight: 800; }
.pbe-modal-body { overflow-y: auto; padding: 8px 18px 22px; }
.pbe-cat { margin: 16px 0 8px; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
.pbe-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 10px; }
.pbe-card { border: 1px solid var(--line); border-radius: 14px; padding: 14px; display: flex; flex-direction: column; gap: 8px; background: #FFFFFF; }
.pbe-card-name { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 15px; }
.pbe-card-name .pbe-ico { width: 32px; height: 32px; border-radius: 9px; background: var(--tint); color: var(--blue2); align-items: center; justify-content: center; }
.pbe-card p { margin: 0; color: var(--muted); font-size: 13px; line-height: 1.45; flex: 1; }
.pbe-card-presets { display: flex; flex-wrap: wrap; gap: 6px; }
.pbe-chip { border: 1px solid #B9C9E6; background: #FFFFFF; color: var(--blue2); font-weight: 700; font-size: 13px; padding: 7px 11px; border-radius: 999px; cursor: pointer; }
.pbe-chip:hover:not(:disabled) { background: var(--tint); border-color: var(--blue); }
.pbe-chip:disabled { color: #A3A8BD; border-color: var(--line); cursor: default; }

/* Outils : modèles, historique, référencement */
.pbe-live { margin: 0 4px 12px; padding: 12px; border-radius: 12px; border: 1px solid var(--line); background: var(--soft); }
.pbe-live.is-on { border-color: #B7E1C4; background: #EFFAF2; }
.pbe-live .pbe-switch span { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.pbe-live strong { font-size: 13px; }
.pbe-live small { font-size: 12px; font-weight: 600; color: var(--muted); line-height: 1.35; }
.pbe-switch input:disabled { opacity: .45; cursor: not-allowed; }
.pbe-tools { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; margin: 0 4px 14px; }
.pbe-tools button { border: 1px solid var(--line); background: #FFFFFF; border-radius: 10px; min-height: 40px; padding: 4px 6px; cursor: pointer; font-weight: 700; font-size: 12px; color: var(--ink2); display: inline-flex; align-items: center; justify-content: center; gap: 5px; }
.pbe-tools button:hover { background: var(--soft); color: var(--ink); }
.pbe-tools .pbe-ai { border-color: #B9C9E6; background: var(--tint); color: var(--blue2); }
.pbe-tools .pbe-ai:disabled { opacity: .7; cursor: progress; }
.pbe-tools button.has-err { border-color: var(--red); color: var(--red); }
.pbe-tpl-meta { display: flex; flex-wrap: wrap; gap: 4px; }
.pbe-tpl-meta span { font-size: 11px; font-weight: 700; color: var(--ink2); background: var(--soft); border: 1px solid var(--line); border-radius: 999px; padding: 2px 8px; }
.pbe-tpl-cat { font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: var(--blue2); }
.pbe-tpl-actions { display: flex; gap: 12px; align-items: center; }
.pbe-tpl-form { display: grid; grid-template-columns: 1fr 180px auto; gap: 8px; align-items: start; margin-top: 8px; }
@media (max-width: 640px) { .pbe-tpl-form { grid-template-columns: 1fr; } }
.pbe-vers { list-style: none; margin: 8px 0 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.pbe-vers li { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px; }
.pbe-vers strong { display: block; font-size: 14px; }
.pbe-vers small { color: var(--muted); font-weight: 600; }
.pbe-live { font-size: 11px; font-weight: 800; color: var(--ok); background: #EAF7EE; border-radius: 999px; padding: 2px 8px; margin-left: 6px; }
.pbe-serp { border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; background: #FFFFFF; font-family: Arial, sans-serif; }
.pbe-serp-url { font-size: 12px; color: #4D5156; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pbe-serp-title { font-size: 19px; color: #1A0DAB; line-height: 1.3; margin: 4px 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pbe-serp-desc { font-size: 13px; color: #4D5156; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.pbe-count { float: right; font-weight: 600; font-size: 12px; color: var(--muted); font-variant-numeric: tabular-nums; }
.pbe-count.is-over { color: var(--red); }

/* Messages */
.pbe-toast { position: fixed; left: 50%; bottom: max(20px, env(safe-area-inset-bottom, 0px)); transform: translateX(-50%); z-index: 60; background: var(--night); color: #FFFFFF; padding: 12px 16px; border-radius: 12px; font-weight: 600; max-width: min(520px, calc(100% - 32px)); box-shadow: 0 16px 36px -14px rgba(11, 30, 61, .6); }
.pbe-conflict { position: fixed; inset: 0; z-index: 70; background: rgba(11, 30, 61, .55); display: flex; align-items: center; justify-content: center; padding: 16px; }
.pbe-conflict div { background: #FFFFFF; border-radius: 16px; padding: 22px; max-width: 420px; }
.pbe-conflict h2 { margin: 0 0 8px; font-size: 18px; }
.pbe-conflict p { margin: 0 0 16px; color: var(--ink2); line-height: 1.5; }

/* Onglets du téléphone */
.pbe-tabs { display: none; }
@media (max-width: 899px) {
    .pbe-top { gap: 6px; }
    .pbe-back span, .pbe-seg, .pbe-pub-state { display: none; }
    .pbe-publish { padding: 0 14px; }
    .pbe-tabs { display: grid; grid-template-columns: repeat(3, 1fr); height: 48px; background: #FFFFFF; border-bottom: 1px solid var(--line); }
    .pbe-tabs button { border: 0; background: transparent; font-weight: 700; color: var(--muted); border-bottom: 2px solid transparent; cursor: pointer; }
    .pbe-tabs button[aria-selected="true"] { color: var(--blue2); border-bottom-color: var(--blue); }
    .pbe-body { height: calc(100% - 108px); display: block; }
    .pbe-tree, .pbe-panel, .pbe-stage { display: none; height: 100%; border: 0; }
    .pbe-body[data-tab="tree"] .pbe-tree, .pbe-body[data-tab="settings"] .pbe-panel { display: block; }
    .pbe-body[data-tab="preview"] .pbe-stage { display: flex; padding: 0; }
    .pbe-frame-wrap, .pbe-frame-wrap[data-device="mobile"] { max-width: none; border: 0; border-radius: 0; box-shadow: none; }
    .pbe-tree, .pbe-panel { padding-left: max(12px, env(safe-area-inset-left, 0px)); padding-right: max(12px, env(safe-area-inset-right, 0px)); }
    .pbe-act { opacity: 1; }
}
@media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>
</head>
<body x-data="pageBuilder(@js($config))" @keydown.escape.window="libraryOpen = false; modal = null; blockMenu = null; rowMenu = null" @click.window="if (!$event.target.closest('.pbe-menu-wrap')) { blockMenu = null; rowMenu = null }">

<header class="pbe-top">
    <a class="pbe-back" href="{{ route('products.edit', $product) }}" aria-label="Retour au produit">{!! Ico::svg('arrow-left', '', 18) !!}<span>Produit</span></a>
    <div class="pbe-title">
        <strong>{{ $product->name }}</strong>
        <span class="pbe-status" role="status" :data-status="status" x-text="statusText">Brouillon enregistré</span>
    </div>
    <div class="pbe-seg" role="group" aria-label="Taille de l'aperçu">
        <button type="button" :aria-pressed="device === 'mobile'" @click="device = 'mobile'">{!! Ico::svg('smartphone', '', 16) !!} Mobile</button>
        <button type="button" :aria-pressed="device === 'desktop'" @click="device = 'desktop'">{!! Ico::svg('laptop', '', 16) !!} Ordinateur</button>
    </div>
    <button type="button" class="pbe-iconbtn" @click="undo()" :disabled="!canUndo" aria-label="Annuler" title="Annuler (Ctrl + Z)">{!! $undo !!}</button>
    <button type="button" class="pbe-iconbtn" @click="redo()" :disabled="!canRedo" aria-label="Rétablir" title="Rétablir (Ctrl + Maj + Z)">{!! $redo !!}</button>
    <a class="pbe-iconbtn" x-show="published.version" x-cloak :href="urls.public" target="_blank" rel="noopener" aria-label="Voir la page publiée" title="Voir la page publiée">{!! Ico::svg('eye', '', 18) !!}</a>
    <span class="pbe-pub-state" x-text="(published.version ? (published.pending ? 'Changements non publiés' : 'Publiée · version ' + published.version) : 'Jamais publiée') + (published.enabled ? '' : ' · désactivée')"></span>
    <button type="button" class="pbe-publish" @click="publish()" :disabled="publishing || status === 'conflict'">
        <span x-text="publishing ? 'Publication…' : 'Publier'">Publier</span>
    </button>
</header>

<nav class="pbe-tabs" role="tablist" aria-label="Éditeur">
    <button type="button" role="tab" :aria-selected="tab === 'tree'" @click="tab = 'tree'">Sections</button>
    <button type="button" role="tab" :aria-selected="tab === 'preview'" @click="tab = 'preview'">Aperçu</button>
    <button type="button" role="tab" :aria-selected="tab === 'settings'" @click="tab = 'settings'">Réglages</button>
</nav>

<div class="pbe-body" :data-tab="tab" data-tab="tree">

    {{-- Arbre des sections et des blocs --}}
    <aside class="pbe-tree" aria-label="Sections de la page">
        <div class="pbe-live" :class="{ 'is-on': published.enabled }">
            <label class="pbe-switch" for="pbe-live">
                <span>
                    <strong>Visible par les clients</strong>
                    <small x-text="published.enabled ? 'Tes clients voient cette page.' : (published.version ? 'Désactivée : tes clients voient la page produit habituelle.' : 'Publie ta page, puis active-la ici.')"></small>
                </span>
                <input type="checkbox" role="switch" id="pbe-live" :checked="published.enabled" :disabled="togglingLive || (!published.version && !published.enabled)" @change="toggleLive($event.target.checked)">
            </label>
        </div>
        <div class="pbe-tools">
            <button type="button" class="pbe-ai" @click="aiWrite()" :disabled="aiBusy">{!! Ico::svg('sparkles', '', 16) !!} <span x-text="aiBusy ? 'Shopio IA écrit…' : 'Shopio IA'"></span></button>
            <button type="button" @click="openTemplates()">{!! Ico::svg('grid', '', 16) !!} Modèles</button>
            <button type="button" @click="openHistory()">{!! Ico::svg('clock', '', 16) !!} Historique</button>
            <button type="button" @click="modal = 'seo'" :class="{ 'has-err': hasError('seo') }">{!! Ico::svg('search', '', 16) !!} Google</button>
        </div>
        <p class="pbe-h">Page produit</p>
        <template x-for="msg in pageErrors" :key="msg">
            <p class="pbe-alert" x-text="msg"></p>
        </template>

        <div class="pbe-list" x-ref="sections">
            <template x-for="sid in page.order" :key="sid">
                <div class="pbe-section" :data-id="sid">
                    <div class="pbe-item" :class="{ 'is-sel': sel.section === sid && !sel.block, 'is-off': section(sid).disabled, 'has-err': hasError('sections.' + sid) }">
                        <button type="button" class="pbe-handle" data-handle="section" aria-label="Glisser pour déplacer la section">{!! $dots !!}</button>
                        <button type="button" class="pbe-name" @click="select(sid)">
                            <span class="pbe-ico" x-html="icons[sectionSchema(sid)?.icon] ?? ''"></span>
                            <span x-text="label(section(sid), sectionSchema(sid))"></span>
                        </button>
                        <template x-if="!isStatic(sid)">
                            <button type="button" class="pbe-act pbe-act--eye" @click="toggleSection(sid)" :aria-label="section(sid).disabled ? 'Afficher la section' : 'Masquer la section'" :title="section(sid).disabled ? 'Afficher' : 'Masquer'">
                                <span class="pbe-ico" x-html="icons[section(sid).disabled ? 'eye-off' : 'eye']"></span>
                            </button>
                        </template>
                        <div class="pbe-menu-wrap">
                            <button type="button" class="pbe-act" @click="rowMenu = rowMenu === sid ? null : sid" :aria-expanded="rowMenu === sid" aria-label="Plus d'actions">{!! $more !!}</button>
                            <div class="pbe-menu" x-show="rowMenu === sid" x-cloak x-transition.opacity>
                                <button type="button" @click="moveSection(sid, -1); rowMenu = null" :disabled="page.order.indexOf(sid) === 0">Monter</button>
                                <button type="button" @click="moveSection(sid, 1); rowMenu = null" :disabled="page.order.indexOf(sid) === page.order.length - 1">Descendre</button>
                                <button type="button" @click="openLibrary(page.order.indexOf(sid) + 1); rowMenu = null">Ajouter une section après</button>
                                <button type="button" @click="duplicateSection(sid); rowMenu = null" :disabled="isStatic(sid) || !canAddSection(section(sid).type)">Dupliquer</button>
                                <button type="button" class="is-danger" @click="removeSection(sid); rowMenu = null" :disabled="isStatic(sid)">
                                    <span x-text="isStatic(sid) ? 'Section obligatoire' : 'Supprimer'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <template x-if="blockTypes(sid).length">
                        <div>
                            <div class="pbe-blocks" x-init="$nextTick(() => sortBlocks($el, sid))">
                                <template x-for="bid in section(sid).block_order" :key="bid">
                                    <div class="pbe-block" :data-id="bid">
                                        <div class="pbe-item" :class="{ 'is-sel': sel.section === sid && sel.block === bid, 'is-off': section(sid).blocks[bid]?.disabled, 'has-err': hasError('sections.' + sid + '.blocks.' + bid) }">
                                            <button type="button" class="pbe-handle" data-handle="block" aria-label="Glisser pour déplacer le bloc">{!! $dots !!}</button>
                                            <button type="button" class="pbe-name" @click="select(sid, bid)">
                                                <span class="pbe-ico" x-html="icons[blockSchema(sid, bid)?.icon] ?? ''"></span>
                                                <span x-text="label(section(sid).blocks[bid], blockSchema(sid, bid))"></span>
                                            </button>
                                            <button type="button" class="pbe-act pbe-act--eye" x-show="!isRequiredBlock(sid, bid)" @click="toggleBlock(sid, bid)" :aria-label="section(sid).blocks[bid]?.disabled ? 'Afficher le bloc' : 'Masquer le bloc'" :title="section(sid).blocks[bid]?.disabled ? 'Afficher' : 'Masquer'">
                                                <span class="pbe-ico" x-html="icons[section(sid).blocks[bid]?.disabled ? 'eye-off' : 'eye']"></span>
                                            </button>
                                            <div class="pbe-menu-wrap">
                                                <button type="button" class="pbe-act" @click="rowMenu = rowMenu === sid + '/' + bid ? null : sid + '/' + bid" :aria-expanded="rowMenu === sid + '/' + bid" aria-label="Plus d'actions">{!! $more !!}</button>
                                                <div class="pbe-menu" x-show="rowMenu === sid + '/' + bid" x-cloak x-transition.opacity>
                                                    <button type="button" @click="duplicateBlock(sid, bid); rowMenu = null" :disabled="!canAddBlock(sid, section(sid).blocks[bid]?.type)">Dupliquer</button>
                                                    <button type="button" class="is-danger" @click="removeBlock(sid, bid); rowMenu = null" :disabled="isRequiredBlock(sid, bid)">
                                                        <span x-text="isRequiredBlock(sid, bid) ? 'Bloc obligatoire' : 'Supprimer'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="pbe-menu-wrap">
                                <button type="button" class="pbe-add pbe-add--block" @click="blockMenu = blockMenu === sid ? null : sid" :aria-expanded="blockMenu === sid">
                                    {!! Ico::svg('plus', '', 16) !!} Ajouter un bloc
                                </button>
                                <div class="pbe-menu pbe-menu--blocks" x-show="blockMenu === sid" x-cloak x-transition.opacity>
                                    <template x-for="bt in blockTypes(sid)" :key="bt.type">
                                        <button type="button" @click="addBlock(sid, bt.type)" :disabled="!canAddBlock(sid, bt.type)">
                                            <span class="pbe-ico" x-html="icons[bt.icon] ?? ''"></span>
                                            <span x-text="bt.name"></span>
                                            <small x-show="!canAddBlock(sid, bt.type)">Déjà là</small>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <button type="button" class="pbe-add pbe-add--section" @click="openLibrary()">{!! Ico::svg('plus', '', 16) !!} Ajouter une section</button>
    </aside>

    {{-- Aperçu --}}
    <section class="pbe-stage" aria-label="Aperçu" x-ref="stage">
        <div class="pbe-frame-wrap" :data-device="device" data-device="mobile" :style="desktopPreview ? { maxWidth: (1280 * scale) + 'px' } : {}">
            <iframe x-ref="frame" src="{{ $config['urls']['frame'] }}" title="Aperçu de la page produit"
                :style="desktopPreview ? { width: '1280px', height: (100 / scale) + '%', transform: 'scale(' + scale + ')', transformOrigin: '0 0' } : {}"></iframe>
        </div>
    </section>

    {{-- Réglages de l'élément sélectionné --}}
    <aside class="pbe-panel" aria-label="Réglages">
        <template x-if="!current">
            <div class="pbe-empty">
                <strong>Rien de sélectionné</strong>
                Choisis une section ou un bloc dans la liste, ou clique directement dans l'aperçu.
            </div>
        </template>

        <template x-if="current">
            <div>
                <div class="pbe-panel-head">
                    <div style="min-width:0">
                        <div class="pbe-crumb" x-text="current.kind === 'block' ? label(section(sel.section), sectionSchema(sel.section)) + ' › Bloc' : 'Section'"></div>
                        <h2 x-text="current.schema.name"></h2>
                    </div>
                </div>
                <p class="pbe-desc" x-show="current.schema.description" x-text="current.schema.description"></p>

                <div class="pbe-fields">
                    <template x-for="(s, si) in current.schema.settings" :key="selKey + ':' + si">
                        <div class="pbe-field" :class="{ 'has-err': fieldError(s.id) }">

                            <template x-if="s.type === 'header'">
                                <h3 class="pbe-fh" x-text="s.label"></h3>
                            </template>

                            <template x-if="s.type === 'video'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <input class="pbe-input" :id="'f-' + s.id" type="url" :value="val(s.id) && !val(s.id).startsWith('page-builder/') ? val(s.id) : ''"
                                        placeholder="Lien YouTube ou TikTok" @change="set(s.id, $event.target.value.trim() || null)">
                                    <div class="pbe-image" style="margin-top:8px">
                                        <span class="pbe-image-empty">{!! Ico::svg('camera', '', 22) !!}</span>
                                        <div class="pbe-image-act">
                                            <span class="pbe-info" style="margin:0" x-text="val(s.id)?.startsWith('page-builder/') ? 'Vidéo MP4 envoyée' : 'Ou envoie un MP4 (20 Mo, 60 s au plus)'"></span>
                                            <label class="pbe-link" style="margin:0">
                                                <span x-text="uploading === s.id ? 'Envoi…' : 'Envoyer un MP4'"></span>
                                                <input type="file" accept="video/mp4" hidden @change="upload(s, $event)" :disabled="uploading !== null">
                                            </label>
                                            <button type="button" class="pbe-link pbe-link--danger" x-show="val(s.id)" @click="set(s.id, null)">Retirer la vidéo</button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="s.type === 'text' || s.type === 'url'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <input class="pbe-input" :id="'f-' + s.id" :type="s.type === 'text' ? 'text' : 'url'" :value="val(s.id) ?? ''" :maxlength="s.max ?? null"
                                        :placeholder="s.placeholder ?? (s.type === 'url' ? 'https://…' : '')"
                                        @input="set(s.id, $event.target.value)">
                                </div>
                            </template>

                            <template x-if="s.type === 'textarea'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <textarea class="pbe-textarea" :id="'f-' + s.id" :maxlength="s.max ?? null" x-init="$el.value = val(s.id) ?? ''" @input="set(s.id, $event.target.value)"></textarea>
                                </div>
                            </template>

                            <template x-if="s.type === 'richtext'">
                                <div>
                                    <span class="pbe-lbl" :id="'l-' + s.id" x-text="s.label"></span>
                                    <div class="pbe-rte-bar" role="toolbar" :aria-label="'Mise en forme : ' + s.label">
                                        <button type="button" @mousedown.prevent @click="rte('bold', $el.parentElement.nextElementSibling)" aria-label="Gras"><b>G</b></button>
                                        <button type="button" @mousedown.prevent @click="rte('italic', $el.parentElement.nextElementSibling)" aria-label="Italique"><i>I</i></button>
                                        <button type="button" @mousedown.prevent @click="rte('underline', $el.parentElement.nextElementSibling)" aria-label="Souligné"><u>S</u></button>
                                        <button type="button" @mousedown.prevent @click="rte('insertUnorderedList', $el.parentElement.nextElementSibling)" aria-label="Liste à puces">• —</button>
                                        <button type="button" @mousedown.prevent @click="rte('link', $el.parentElement.nextElementSibling)" aria-label="Lien">Lien</button>
                                    </div>
                                    <div class="pbe-rte" contenteditable="true" role="textbox" aria-multiline="true" :aria-labelledby="'l-' + s.id"
                                        x-init="$el.innerHTML = val(s.id) ?? ''"
                                        @input="set(s.id, $el.innerHTML)"></div>
                                </div>
                            </template>

                            <template x-if="s.type === 'datetime'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <input class="pbe-input" type="datetime-local" :id="'f-' + s.id" :value="val(s.id) ?? ''" @change="set(s.id, $event.target.value || null)">
                                </div>
                            </template>

                            <template x-if="s.type === 'number'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <input class="pbe-input" type="number" inputmode="numeric" :id="'f-' + s.id" :min="s.min ?? null" :max="s.max ?? null" :value="val(s.id) ?? ''"
                                        @input="set(s.id, $event.target.value === '' ? null : Number($event.target.value))">
                                </div>
                            </template>

                            <template x-if="s.type === 'range'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <div class="pbe-range">
                                        <input type="range" :id="'f-' + s.id" :min="s.min" :max="s.max" :step="s.step" :value="val(s.id)" @input="set(s.id, Number($event.target.value))">
                                        <output :for="'f-' + s.id" x-text="val(s.id) + (s.unit ? ' ' + s.unit : '')"></output>
                                    </div>
                                </div>
                            </template>

                            <template x-if="s.type === 'select'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <select class="pbe-select" :id="'f-' + s.id" @change="set(s.id, $event.target.value)">
                                        <template x-for="o in s.options" :key="o.value">
                                            <option :value="o.value" :selected="String(val(s.id)) === o.value" x-text="o.label"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>

                            <template x-if="s.type === 'checkbox'">
                                <label class="pbe-switch" :for="'f-' + s.id">
                                    <span x-text="s.label"></span>
                                    <input type="checkbox" role="switch" :id="'f-' + s.id" :checked="!!val(s.id)" @change="set(s.id, $event.target.checked)">
                                </label>
                            </template>

                            <template x-if="s.type === 'color'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <div class="pbe-color">
                                        <input type="color" :aria-label="s.label" :value="val(s.id) || '#1F7CF6'" @input="set(s.id, $event.target.value.toUpperCase())">
                                        <input class="pbe-input" :id="'f-' + s.id" maxlength="7" :value="val(s.id) ?? ''" placeholder="#1F7CF6" @change="set(s.id, $event.target.value.trim() || null)">
                                    </div>
                                </div>
                            </template>

                            <template x-if="s.type === 'color_scheme'">
                                <div>
                                    <span class="pbe-lbl" x-text="s.label"></span>
                                    <div class="pbe-schemes" role="radiogroup" :aria-label="s.label">
                                        <template x-for="(sc, key) in schemes" :key="key">
                                            <button type="button" class="pbe-scheme" role="radio" :aria-checked="val(s.id) === key" @click="set(s.id, key)">
                                                <span class="pbe-scheme-sw" :style="{ background: sc.bg, color: sc.text }">Aa <i :style="{ background: sc.button }"></i></span>
                                                <span x-text="sc.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="s.type === 'image'">
                                <div>
                                    <span class="pbe-lbl" x-text="s.label"></span>
                                    <div class="pbe-image">
                                        <template x-if="val(s.id)"><img :src="mediaUrl(val(s.id))" alt=""></template>
                                        <template x-if="!val(s.id)"><span class="pbe-image-empty">{!! Ico::svg('image', '', 22) !!}</span></template>
                                        <div class="pbe-image-act">
                                            <label class="pbe-link" style="margin:0">
                                                <span x-text="uploading === s.id ? 'Envoi…' : (val(s.id) ? 'Changer l\'image' : 'Choisir une image')"></span>
                                                <input type="file" accept="image/jpeg,image/png,image/webp" hidden @change="upload(s, $event)" :disabled="uploading !== null">
                                            </label>
                                            <button type="button" class="pbe-link pbe-link--danger" x-show="val(s.id)" @click="set(s.id, null)">Retirer</button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template x-if="s.type === 'icon'">
                                <div>
                                    <span class="pbe-lbl" x-text="s.label"></span>
                                    <div class="pbe-icons" role="group" :aria-label="s.label">
                                        <template x-for="(svg, name) in icons" :key="name">
                                            <button type="button" :aria-pressed="val(s.id) === name" :aria-label="name" :title="name" @click="set(s.id, name)" x-html="svg"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="s.type === 'product'">
                                <div>
                                    <label :for="'f-' + s.id" x-text="s.label"></label>
                                    <select class="pbe-select" :id="'f-' + s.id" @change="set(s.id, $event.target.value === '' ? null : Number($event.target.value))">
                                        <option value="">Aucun produit</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id" :selected="val(s.id) === p.id" x-text="p.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>

                            <template x-if="s.type === 'product_list'">
                                <div>
                                    <span class="pbe-lbl" x-text="s.label + ' (' + (val(s.id) || []).length + '/' + (s.max ?? 8) + ')'"></span>
                                    <div class="pbe-products">
                                        <template x-for="p in products" :key="p.id">
                                            <label class="pbe-product">
                                                <template x-if="p.image"><img :src="p.image" alt=""></template>
                                                <template x-if="!p.image"><i></i></template>
                                                <span x-text="p.name"></span>
                                                <input type="checkbox" :checked="listHas(s.id, p.id)" :disabled="!listHas(s.id, p.id) && (val(s.id) || []).length >= (s.max ?? 8)" @change="listToggle(s, p.id)">
                                            </label>
                                        </template>
                                        <p class="pbe-info" x-show="!products.length">Aucun produit en ligne dans ta boutique.</p>
                                    </div>
                                </div>
                            </template>

                            <p class="pbe-info" x-show="s.info && s.type !== 'header'" x-text="s.info"></p>
                            <p class="pbe-err" x-show="fieldError(s.id)" x-text="fieldError(s.id)"></p>
                        </div>
                    </template>
                </div>

                <div class="pbe-panel-foot">
                    <template x-if="current.kind === 'section' && !isStatic(sel.section)">
                        <button type="button" class="pbe-link" @click="toggleSection(sel.section)" x-text="section(sel.section).disabled ? 'Afficher la section' : 'Masquer la section'"></button>
                    </template>
                    <template x-if="current.kind === 'section' && !isStatic(sel.section)">
                        <button type="button" class="pbe-link pbe-link--danger" @click="removeSection(sel.section)">Supprimer la section</button>
                    </template>
                    <template x-if="current.kind === 'block'">
                        <button type="button" class="pbe-link" @click="select(sel.section)">Réglages de la section</button>
                    </template>
                    <template x-if="current.kind === 'block' && !isRequiredBlock(sel.section, sel.block)">
                        <button type="button" class="pbe-link pbe-link--danger" @click="removeBlock(sel.section, sel.block)">Supprimer le bloc</button>
                    </template>
                </div>
            </div>
        </template>
    </aside>
</div>

{{-- Bibliothèque : ajouter une section --}}
<div class="pbe-modal" x-show="libraryOpen" x-cloak x-transition.opacity @click.self="libraryOpen = false">
    <div class="pbe-modal-box" role="dialog" aria-modal="true" aria-labelledby="pbe-lib-title">
        <div class="pbe-modal-head">
            <h2 id="pbe-lib-title">Ajouter une section</h2>
            <button type="button" class="pbe-iconbtn" @click="libraryOpen = false" aria-label="Fermer">{!! Ico::svg('x', '', 18) !!}</button>
        </div>
        <div class="pbe-modal-body">
            <p class="pbe-info" x-show="page.order.length >= 40">Ta page a déjà 40 sections, le maximum.</p>
            <template x-for="cat in library" :key="cat.key">
                <div>
                    <h3 class="pbe-cat" x-text="cat.label"></h3>
                    <div class="pbe-cards">
                        <template x-for="sec in cat.sections" :key="sec.type">
                            <div class="pbe-card">
                                <div class="pbe-card-name"><span class="pbe-ico" x-html="icons[sec.icon] ?? ''"></span><span x-text="sec.name"></span></div>
                                <p x-text="sec.description ?? ''"></p>
                                <div class="pbe-card-presets">
                                    <template x-for="(preset, i) in sec.presets" :key="i">
                                        <button type="button" class="pbe-chip" @click="addSection(sec.type, i)" :disabled="!canAddSection(sec.type)"
                                            x-text="sec.presets.length > 1 ? preset.name : 'Ajouter'"></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- Modèles de page --}}
<div class="pbe-modal" x-show="modal === 'templates'" x-cloak x-transition.opacity @click.self="modal = null">
    <div class="pbe-modal-box" role="dialog" aria-modal="true" aria-labelledby="pbe-tpl-title">
        <div class="pbe-modal-head">
            <h2 id="pbe-tpl-title">Modèles de page</h2>
            <button type="button" class="pbe-iconbtn" @click="modal = null" aria-label="Fermer">{!! Ico::svg('x', '', 18) !!}</button>
        </div>
        <div class="pbe-modal-body">
            <p class="pbe-info" x-show="modalLoading">Chargement…</p>
            <p class="pbe-info" x-show="!modalLoading">Un modèle remplace ton brouillon. Ta page en ligne ne change pas tant que tu ne publies pas, et « Annuler » te ramène à ta page actuelle.</p>

            <h3 class="pbe-cat">Modèles Shopio</h3>
            <div class="pbe-cards">
                <template x-for="t in templates.shopio" :key="t.id">
                    <div class="pbe-card">
                        <span class="pbe-tpl-cat" x-text="t.category"></span>
                        <div class="pbe-card-name"><span x-text="t.name"></span></div>
                        <p x-text="t.description"></p>
                        <div class="pbe-tpl-meta"><template x-for="(n, i) in t.sections" :key="i"><span x-text="n"></span></template></div>
                        <div class="pbe-tpl-actions"><button type="button" class="pbe-chip" @click="applyTemplate(t)">Utiliser ce modèle</button></div>
                    </div>
                </template>
            </div>

            <h3 class="pbe-cat">Mes modèles <span x-text="'(' + templates.mine.length + '/' + templates.max + ')'"></span></h3>
            <p class="pbe-info" x-show="!modalLoading && !templates.mine.length">Enregistre une page réussie comme modèle pour la réutiliser sur tes autres produits.</p>
            <div class="pbe-cards">
                <template x-for="t in templates.mine" :key="t.id">
                    <div class="pbe-card">
                        <span class="pbe-tpl-cat" x-text="t.category"></span>
                        <div class="pbe-card-name"><span x-text="t.name"></span></div>
                        <p x-text="t.description"></p>
                        <div class="pbe-tpl-actions">
                            <button type="button" class="pbe-chip" @click="applyTemplate(t)">Utiliser</button>
                            <button type="button" class="pbe-link pbe-link--danger" @click="deleteTemplate(t)">Supprimer</button>
                        </div>
                    </div>
                </template>
            </div>

            <h3 class="pbe-cat">Enregistrer cette page comme modèle</h3>
            <form class="pbe-tpl-form" @submit.prevent="saveTemplate()">
                <div>
                    <input id="pbe-tpl-name" class="pbe-input" maxlength="80" placeholder="Nom du modèle (ex. Montres)" aria-label="Nom du modèle" x-model="tplForm.name">
                    <p class="pbe-err" x-show="tplForm.error" x-text="tplForm.error"></p>
                </div>
                <select id="pbe-tpl-cat" class="pbe-select" aria-label="Catégorie du modèle" x-model="tplForm.category">
                    <template x-for="(label, key) in templates.categories" :key="key"><option :value="key" x-text="label"></option></template>
                </select>
                <button type="submit" class="pbe-publish" :disabled="!tplForm.name.trim()">Enregistrer</button>
            </form>
        </div>
    </div>
</div>

{{-- Historique des publications --}}
<div class="pbe-modal" x-show="modal === 'history'" x-cloak x-transition.opacity @click.self="modal = null">
    <div class="pbe-modal-box" role="dialog" aria-modal="true" aria-labelledby="pbe-hist-title" style="width:min(560px,100%)">
        <div class="pbe-modal-head">
            <h2 id="pbe-hist-title">Historique des publications</h2>
            <button type="button" class="pbe-iconbtn" @click="modal = null" aria-label="Fermer">{!! Ico::svg('x', '', 18) !!}</button>
        </div>
        <div class="pbe-modal-body">
            <p class="pbe-info" x-show="modalLoading">Chargement…</p>
            <p class="pbe-info" x-show="!modalLoading && !versions.length">Aucune publication pour l'instant. Chaque publication est gardée ici (les 10 dernières).</p>
            <ul class="pbe-vers">
                <template x-for="v in versions" :key="v.version">
                    <li>
                        <div>
                            <strong>Version <span x-text="v.version"></span><span class="pbe-live" x-show="v.current">En ligne</span></strong>
                            <small x-text="v.at + (v.by ? ' · ' + v.by : '')"></small>
                        </div>
                        <button type="button" class="pbe-chip" @click="restoreVersion(v)">Remettre dans le brouillon</button>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>

{{-- Référencement Google --}}
<div class="pbe-modal" x-show="modal === 'seo'" x-cloak x-transition.opacity @click.self="modal = null">
    <div class="pbe-modal-box" role="dialog" aria-modal="true" aria-labelledby="pbe-seo-title" style="width:min(600px,100%)">
        <div class="pbe-modal-head">
            <h2 id="pbe-seo-title">Google et partage du lien</h2>
            <button type="button" class="pbe-iconbtn" @click="modal = null" aria-label="Fermer">{!! Ico::svg('x', '', 18) !!}</button>
        </div>
        <div class="pbe-modal-body" style="display:grid;gap:16px;padding-top:16px">
            <div class="pbe-serp" aria-label="Aperçu dans Google">
                <div class="pbe-serp-url" x-text="seoPreview.url"></div>
                <div class="pbe-serp-title" x-text="seoPreview.title"></div>
                <div class="pbe-serp-desc" x-text="seoPreview.description"></div>
            </div>
            <div class="pbe-field" :class="{ 'has-err': errors['seo.title'] }">
                <label for="pbe-seo-t">Titre <span class="pbe-count" :class="{ 'is-over': (page.seo.title || '').length > seoDefaults.max.title }" x-text="(page.seo.title || '').length + ' / ' + seoDefaults.max.title"></span></label>
                <input id="pbe-seo-t" class="pbe-input" :placeholder="seoDefaults.title" :value="page.seo.title || ''" @input="setSeo('title', $event.target.value)">
                <p class="pbe-err" x-show="errors['seo.title']" x-text="errors['seo.title']?.[0]"></p>
            </div>
            <div class="pbe-field" :class="{ 'has-err': errors['seo.description'] }">
                <label for="pbe-seo-d">Description <span class="pbe-count" :class="{ 'is-over': (page.seo.description || '').length > seoDefaults.max.description }" x-text="(page.seo.description || '').length + ' / ' + seoDefaults.max.description"></span></label>
                <textarea id="pbe-seo-d" class="pbe-textarea" :placeholder="seoDefaults.description" x-effect="if (document.activeElement !== $el) $el.value = page.seo.description || ''" @input="setSeo('description', $event.target.value)"></textarea>
                <p class="pbe-err" x-show="errors['seo.description']" x-text="errors['seo.description']?.[0]"></p>
            </div>
            <p class="pbe-info" style="margin:0">Vide : Shopio utilise le nom et la description du produit. La photo principale sert d'image quand le lien est partagé sur WhatsApp ou Facebook.</p>
        </div>
    </div>
</div>

{{-- Page modifiée ailleurs : on ne remplace pas le travail de l'autre onglet --}}
<div class="pbe-conflict" x-show="status === 'conflict'" x-cloak role="alertdialog" aria-modal="true" aria-labelledby="pbe-conflict-title">
    <div>
        <h2 id="pbe-conflict-title">Page modifiée ailleurs</h2>
        <p x-text="message"></p>
        <button type="button" class="pbe-publish" @click="window.location.reload()">Recharger la page</button>
    </div>
</div>

<div class="pbe-toast" x-show="notice" x-cloak x-transition.opacity role="status" aria-live="polite" x-text="notice"></div>
</body>
</html>
