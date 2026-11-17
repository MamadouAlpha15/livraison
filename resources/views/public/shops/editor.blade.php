{{--
    resources/views/public/shops/editor.blade.php
    Adresse  : /s/{slug}?edition=1 (propriétaire de la boutique seulement, vérifié par PublicShopController)
    Rôle     : « Modifier la page », directement sur la vitrine en ligne.
               - En haut : Retour, sélecteur de page avec recherche (Page d'accueil, Page catalogue, Panier, Produits →
                 page de vente de chaque produit), thème modifié, état, Annuler, Enregistrer le brouillon, Partager
                 un aperçu, Publier.
               - À gauche, onglets « Sections » (Logo de la boutique, sections de la page avec interrupteur, menu ⋮,
                 glisser pour réordonner, champs dépliables, « + Ajouter », Réglages de la page, Pied de page) et
                 « Couleurs » (couleur principale, fond, texte du thème).
               - À droite : aperçu téléphone / ordinateur et « Voir » ; la vitrine s'affiche dans un cadre (?cadre=1),
                 rendue avec la version en cours de saisie ; chaque section y a aussi sa petite barre.
               Les visiteurs ne voient les changements qu'après « Publier ».
    Données  : $ed (Vendeur\StorefrontController::editor), $shop, $theme
--}}
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex">
<title>Modifier la page · {{ $shop->name }}</title>
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
@include('vendeur.shop._sx-style')
<style>
html, body { margin: 0; height: 100%; overflow: hidden; background: #EEF2F8; }
.sxe { display: flex; flex-direction: column; height: 100vh; height: 100dvh; }
/* ── Barre du haut ── */
.sxe-top { position: relative; z-index: 60; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px max(14px, env(safe-area-inset-left, 0px)); background: var(--sx-surf); box-shadow: 0 1px 0 var(--sx-line); }
.sxe-back { display: inline-flex; align-items: center; gap: 4px; padding: 0 12px 0 8px; min-height: 38px; border: 0; border-radius: 10px; background: transparent; color: var(--sx-ink); font: 700 13.5px var(--sx-font); cursor: pointer; }
.sxe-back:hover { background: var(--sx-bg); }
.sxe-pagebox { position: relative; margin: 0 auto; }
.sxe-pagebtn { display: inline-flex; align-items: center; gap: 8px; min-height: 40px; padding: 0 12px 0 8px; border: 1px solid var(--sx-line); border-radius: 12px; background: var(--sx-surf); color: var(--sx-ink); font: 700 14px var(--sx-font); cursor: pointer; }
.sxe-pagebtn i { width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: var(--sx-b50); color: var(--sx-blue); font-style: normal; }
.sxe-caret { display: flex; color: var(--sx-ink3); }
.sxe-acts .sx-btn svg, .sxe-view svg { color: var(--sx-ink2); flex-shrink: 0; }
.sxe-acts .sx-btn-main svg { color: #fff; }
.sxe-menu { position: absolute; left: 50%; top: calc(100% + 6px); transform: translateX(-50%); width: 300px; max-width: calc(100vw - 20px); padding: 8px; border-radius: 14px; background: var(--sx-surf); box-shadow: 0 0 0 1px var(--sx-line), 0 24px 50px -20px rgba(11,30,61,.4); z-index: 70; }
.sxe-menu[hidden] { display: none; }
.sxe-menu input { margin-bottom: 6px; }
.sxe-mi { display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px; border: 0; border-radius: 10px; background: transparent; color: var(--sx-ink); font: 600 14px var(--sx-font); text-align: left; cursor: pointer; text-decoration: none; }
.sxe-mi:hover, .sxe-mi[aria-current="page"] { background: var(--sx-b50); }
.sxe-mi i { width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--sx-bg); box-shadow: inset 0 0 0 1px var(--sx-line); color: var(--sx-ink2); font-style: normal; overflow: hidden; }
.sxe-mi b { display: flex; }
.sxe-mi i img { width: 100%; height: 100%; object-fit: cover; }
.sxe-mi span { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sxe-mi small { color: var(--sx-ink3); font-weight: 700; }
.sxe-mi b { color: var(--sx-blue); }
.sxe-plist { max-height: 300px; overflow-y: auto; }
.sxe-seg { display: inline-flex; padding: 3px; gap: 2px; border-radius: 999px; background: var(--sx-bg); box-shadow: inset 0 0 0 1px var(--sx-line); }
.sxe-seg button { min-height: 32px; padding: 0 12px; border: 0; border-radius: 999px; background: transparent; color: var(--sx-ink2); font: 700 12.5px var(--sx-font); cursor: pointer; white-space: nowrap; }
.sxe-seg button[aria-pressed="true"] { background: var(--sx-surf); color: var(--sx-blue); box-shadow: 0 1px 4px rgba(11,30,61,.15); }
.sxe-seg button { display: inline-flex; align-items: center; gap: 6px; }
.sxe-seg button i { font-style: normal; font-size: 10.5px; font-weight: 700; padding: 1px 7px; border-radius: 999px; background: var(--sx-b50); color: var(--sx-blue); }
/* État : pastille neutre, seul le petit point change de couleur */
.sxe-state { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 700; padding: 5px 11px; border-radius: 999px; white-space: nowrap; background: var(--sx-surf); color: var(--sx-ink2); box-shadow: inset 0 0 0 1px var(--sx-line); }
.sxe-state::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: #9DAAC2; flex-shrink: 0; }
.sxe-state.is-dirty::before { background: #F59E0B; } .sxe-state.is-draft::before { background: #1F7CF6; } .sxe-state.is-ok::before { background: #16A34A; }
.sxe-state.is-err { color: #B91C1C; } .sxe-state.is-err::before { background: #DC2626; }
.sxe-state:empty { display: none; }
.sxe-acts { display: flex; gap: 6px; flex-wrap: wrap; }
.sxe-acts .sx-btn { min-height: 38px; font-size: 13px; }
/* ── Corps : colonne de gauche + aperçu ── */
.sxe-body { flex: 1; min-height: 0; display: flex; }
.sxe-side { width: 400px; flex-shrink: 0; display: flex; flex-direction: column; min-height: 0; background: var(--sx-surf); box-shadow: 1px 0 0 var(--sx-line); }
.sxe-tabs { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; padding: 10px 12px 0; }
.sxe-tabs button { display: flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; border: 0; border-radius: 10px; background: transparent; color: var(--sx-ink2); font: 700 14px var(--sx-font); cursor: pointer; }
.sxe-tabs button svg { color: var(--sx-ink3); }
.sxe-tabs button[aria-selected="true"] { background: var(--sx-bg); color: var(--sx-ink); box-shadow: inset 0 0 0 1px var(--sx-line); }
.sxe-tabs button[aria-selected="true"] svg { color: var(--sx-blue); }
.sxe-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; display: grid; grid-template-columns: minmax(0, 1fr); gap: 10px; align-content: start; }
.sxe-scroll > *, .sxe-body-in > * { min-width: 0; }
.sxe-body-in { grid-template-columns: minmax(0, 1fr); }
.sxe-body-in .sx-ai { flex-wrap: wrap; }
.sxe-hd { display: flex; align-items: flex-end; justify-content: space-between; gap: 8px; margin-top: 6px; }
.sxe-hd b { display: block; font-size: 14.5px; } .sxe-hd small { display: block; color: var(--sx-ink3); font-size: 12px; }
/* Une ligne de la liste (section, logo, réglages, pied de page) */
.sxe-it { border-radius: 14px; background: var(--sx-surf); box-shadow: inset 0 0 0 1px var(--sx-line); transition: box-shadow 160ms; }
.sxe-it:hover { box-shadow: inset 0 0 0 1px #B9C7DD; }
.sxe-it.is-open { box-shadow: inset 0 0 0 1.5px var(--sx-blue); }
.sxe-it.is-off .sxe-name { color: var(--sx-ink3); }
.sxe-it.is-drag { opacity: .4; } .sxe-it.is-over { box-shadow: inset 0 2px 0 var(--sx-blue), inset 0 0 0 1px var(--sx-line); }
.sxe-row { display: flex; align-items: center; gap: 8px; min-height: 52px; padding: 4px 6px 4px 10px; }
.sxe-grip { color: var(--sx-ink3); cursor: grab; display: flex; }
.sxe-ico { width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--sx-bg); color: var(--sx-ink2); box-shadow: inset 0 0 0 1px var(--sx-line); }
.sxe-it.is-open .sxe-ico { background: var(--sx-b50); color: var(--sx-blue); box-shadow: none; }
.sxe-name { flex: 1; min-width: 0; border: 0; background: transparent; padding: 6px 0; text-align: left; cursor: pointer; color: var(--sx-ink); font: 700 14px var(--sx-font); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sxe-name small { display: block; font-weight: 500; font-size: 12px; color: var(--sx-ink3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sxe-sw { position: relative; flex-shrink: 0; width: 44px; height: 26px; border-radius: 999px; border: 0; cursor: pointer; padding: 0; background: #C3CBDA; }
.sxe-sw::after { content: ''; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,.25); transition: transform 200ms var(--sx-out); }
.sxe-sw[aria-checked="true"] { background: #1F7CF6; } .sxe-sw[aria-checked="true"]::after { transform: translateX(18px); }
.sxe-sw:disabled { opacity: .5; cursor: not-allowed; }
.sxe-ib { width: 32px; height: 32px; flex-shrink: 0; border: 0; border-radius: 9px; background: transparent; color: var(--sx-ink2); display: flex; align-items: center; justify-content: center; cursor: pointer; }
.sxe-ib:hover { background: var(--sx-bg); }
.sxe-chev { transition: transform 200ms var(--sx-out); } .is-open > .sxe-row .sxe-chev { transform: rotate(180deg); }
.sxe-more { position: relative; }
.sxe-pop { position: absolute; right: 0; top: calc(100% + 4px); z-index: 20; width: 180px; padding: 6px; border-radius: 12px; background: var(--sx-surf); box-shadow: 0 0 0 1px var(--sx-line), 0 18px 40px -18px rgba(11,30,61,.45); }
.sxe-pop button { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border: 0; border-radius: 8px; background: transparent; color: var(--sx-ink); font: 600 13.5px var(--sx-font); cursor: pointer; text-align: left; }
.sxe-pop button svg { color: var(--sx-ink3); flex-shrink: 0; } .sxe-pop .is-del svg { color: inherit; }
.sxe-pop button:hover:not(:disabled) { background: var(--sx-bg); } .sxe-pop button:disabled { opacity: .4; cursor: default; } .sxe-pop .is-del { color: #B91C1C; }
.sxe-body-in { display: grid; gap: 12px; padding: 4px 12px 14px; border-top: 1px solid var(--sx-line); padding-top: 12px; }
.sxe-add { display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 46px; border: 1.5px dashed #C3CBDA; border-radius: 14px; background: transparent; color: var(--sx-blue); font: 700 13.5px var(--sx-font); cursor: pointer; }
.sxe-add:hover { background: var(--sx-b50); }
.sxe-note { margin: 0; padding: 10px 12px; border-radius: 12px; background: var(--sx-bg); color: var(--sx-ink2); font-size: 13px; line-height: 1.45; box-shadow: inset 0 0 0 1px var(--sx-line); }
.sxe-r { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px; background: var(--sx-bg); }
.sxe-r > div { flex: 1; min-width: 0; } .sxe-r b { display: block; font-size: 13.5px; } .sxe-r span { display: block; font-size: 12px; color: var(--sx-ink3); }
.sxe-pill { display: inline-flex; align-items: center; height: 24px; padding: 0 9px; border-radius: 999px; font-size: 11.5px; font-weight: 800; background: var(--sx-surf); color: var(--sx-ink2); box-shadow: inset 0 0 0 1px var(--sx-line); white-space: nowrap; }
.sxe-sws { display: flex; gap: 8px; flex-wrap: wrap; }
.sxe-swatch { width: 34px; height: 34px; border-radius: 50%; border: 0; cursor: pointer; box-shadow: inset 0 0 0 1px rgba(0,0,0,.12); }
.sxe-swatch[aria-checked="true"] { box-shadow: 0 0 0 3px var(--sx-surf), 0 0 0 5px var(--sx-ink); }
.sxe-opts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.sxe-opt { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 6px; border: 0; border-radius: 12px; background: var(--sx-bg); cursor: pointer; font: 700 12px var(--sx-font); color: var(--sx-ink2); box-shadow: inset 0 0 0 1.5px var(--sx-line); }
.sxe-opt[aria-checked="true"] { box-shadow: inset 0 0 0 2px #1F7CF6; color: var(--sx-blue); }
.sxe-opt i { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #0B1E3D center / cover; font-style: normal; font-weight: 800; color: #fff; }
.sxe-opt i img { width: 100%; height: 100%; object-fit: cover; }
.sxe-wa { background: #E5DDD5; border-radius: 14px; padding: 10px; }
.sxe-wa-card { max-width: 300px; margin-left: auto; background: #D9FDD3; border-radius: 12px 4px 12px 12px; padding: 4px; color: #111B21; }
.sxe-wa-card > div { background: #F0F2F5; border-radius: 8px; overflow: hidden; }
.sxe-wa-img { position: relative; aspect-ratio: 1.91; background: #0B1E3D center / cover; } .sxe-wa-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sxe-wa-logo { position: absolute; left: 10px; bottom: 10px; width: 36px; height: 36px; border-radius: 50%; border: 3px solid #fff; overflow: hidden; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; }
.sxe-wa-logo img { width: 100%; height: 100%; object-fit: cover; }
.sxe-wa-txt { padding: 8px 10px 10px; } .sxe-wa-txt b { display: block; font-size: 13px; } .sxe-wa-txt span { display: block; font-size: 12px; color: #54656F; } .sxe-wa-txt small { color: #8696A0; font-size: 11px; }
.sxe-search { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; background: var(--sx-bg); }
.sxe-search i { width: 44px; height: 44px; border-radius: 12px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-style: normal; font-weight: 800; }
.sxe-search i img { width: 100%; height: 100%; object-fit: cover; }
.sxe-search b { display: block; font-size: 14px; } .sxe-search p { margin: 2px 0 0; font-size: 12px; color: var(--sx-ink2); }
.sxe-lib { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.sxe-lib button { display: flex; flex-direction: column; align-items: flex-start; gap: 3px; padding: 10px 12px; border: 0; border-radius: 12px; background: var(--sx-bg); text-align: left; cursor: pointer; font: 700 13px var(--sx-font); color: var(--sx-ink); box-shadow: inset 0 0 0 1px var(--sx-line); }
.sxe-lib button:hover { box-shadow: inset 0 0 0 1.5px #1F7CF6; } .sxe-lib span { font-size: 12px; font-weight: 500; color: var(--sx-ink3); }
.sxe-sub { display: flex; align-items: center; gap: 6px; } .sxe-sub b { font-size: 15px; }
.sxe-link { display: flex; gap: 6px; } .sxe-link input { flex: 1; min-width: 0; }
.sxe-color { display: grid; gap: 8px; padding: 12px; border-radius: 14px; box-shadow: inset 0 0 0 1px var(--sx-line); }
.sxe-color-hd { display: flex; align-items: center; gap: 10px; }
.sxe-color-hd b { flex: 1; font-size: 14px; }
.sxe-color-hd input[type=color] { width: 40px; height: 34px; padding: 0; border: 0; border-radius: 8px; background: none; cursor: pointer; }
.sxe-color-hd code { font: 700 12.5px ui-monospace, monospace; color: var(--sx-ink2); }
/* ── Aperçu ── */
.sxe-stage { position: relative; flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 12px; overflow: hidden; }
.sxe-stagebar { display: flex; align-items: center; gap: 8px; }
.sxe-view { display: inline-flex; align-items: center; gap: 6px; min-height: 34px; padding: 0 12px; border-radius: 999px; background: var(--sx-surf); color: var(--sx-ink); font: 700 13px var(--sx-font); text-decoration: none; box-shadow: inset 0 0 0 1px var(--sx-line); }
.sxe-frames { position: relative; flex: 1; width: 100%; min-height: 0; border-radius: 14px; overflow: hidden; background: #fff; box-shadow: 0 20px 50px -30px rgba(11,30,61,.6); transition: width 300ms var(--sx-out); }
.sxe-frames.is-phone { width: 400px; max-width: 100%; border-radius: 28px; border: 8px solid #0B1E3D; }
.sxe-frames iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; background: #fff; }
.sxe-frames iframe.is-back { visibility: hidden; }
.sxe-busy { position: absolute; top: 12px; left: 50%; transform: translateX(-50%); z-index: 5; padding: 6px 12px; border-radius: 999px; background: rgba(11,30,61,.85); color: #fff; font: 700 12px var(--sx-font); pointer-events: none; }
.sxe-sheetbtn { display: none; }
.sxe [hidden] { display: none !important; }
/* Page d'un produit : l'éditeur de la page de vente, intégré */
.sxe-pmode { position: relative; flex: 1; min-width: 0; }
.sxe-pmode iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; background: #F6F9FF; }
.sxe-pill.is-on { background: #E7F6EC; color: #15803D; box-shadow: none; } .sxe-pill.is-draft { background: #FEF3C7; color: #78350F; box-shadow: none; }
/* ── Téléphone : la colonne devient un panneau en bas de l'écran ── */
@media (max-width: 900px) {
    .sxe-top { padding: 8px 10px; gap: 6px; }
    .sxe-pagebox { order: 3; width: 100%; } .sxe-pagebtn { width: 100%; }
    .sxe-themes { order: 4; } .sxe-state { order: 5; }
    .sxe-acts { order: 6; width: 100%; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .sxe-acts .sx-btn { padding: 0 4px; font-size: 11.5px; } .sxe-acts .sx-btn span { display: none; }
    .sxe-stage { padding: 0; } .sxe-stagebar { display: none; }
    .sxe-frames, .sxe-frames.is-phone { border: 0; border-radius: 0; width: 100%; box-shadow: none; }
    .sxe-side { position: fixed; left: 0; right: 0; bottom: 0; width: auto; height: 62vh; z-index: 50; border-radius: 20px 20px 0 0; box-shadow: 0 -20px 50px -20px rgba(11,30,61,.5); padding-bottom: env(safe-area-inset-bottom, 0px); transition: transform 260ms var(--sx-out); }
    .sxe-side.is-down { transform: translateY(calc(100% - 56px)); }
    .sxe-sheetbtn { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; min-height: 26px; border: 0; background: transparent; color: var(--sx-ink2); font: 700 12px var(--sx-font); cursor: pointer; padding-top: 6px; }
    .sxe-sheetbtn::before { content: ''; width: 40px; height: 4px; border-radius: 4px; background: var(--sx-line); position: absolute; top: 6px; left: 50%; margin-left: -20px; }
}
</style>
</head>
<body>
@php
    /* Icônes au trait, toutes du même style (gris ou bleu Shopio selon le bouton) */
    $EI = [
        'back' => 'M15 6l-6 6 6 6', 'home' => 'M3 10.5 12 4l9 6.5V20H3zM9 20v-6h6v6', 'caret' => 'm6 9 6 6 6-6',
        'undo' => 'M9 14 4 9l5-5M4 9h11a5 5 0 0 1 0 10h-3', 'save' => 'M5 3h11l3 3v15H5zM8 3v5h7V3M8 21v-7h8v7',
        'link' => 'M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1',
        'eye' => 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
        'mobile' => 'M7 2h10v20H7zM11 18h2', 'desk' => 'M3 4h18v12H3zM8 20h8M12 16v4',
        'sections' => 'M4 5h16v6H4zM4 15h16v4H4z',
        'palette' => 'M12 3a9 9 0 1 0 0 18c.9 0 1.5-.7 1.5-1.5 0-.4-.2-.8-.4-1.1-.3-.3-.4-.6-.4-1 0-.8.7-1.4 1.5-1.4H16a5 5 0 0 0 5-5c0-4.4-4-8-9-8zM7.5 11h.01M10 7.5h.01M14.5 7.5h.01',
    ];
    $ei = fn (string $k, int $s = 17) => '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $EI[$k] . '"/></svg>';
@endphp
<div class="sx sxe" id="sxe">
    <header class="sxe-top">
        <button type="button" class="sxe-back" id="sxeQuit" title="Quitter l’éditeur">{!! $ei('back') !!}<span>Retour</span></button>
        <div class="sxe-pagebox">
            <button type="button" class="sxe-pagebtn" id="sxePageBtn" aria-haspopup="true" aria-expanded="false"><i id="sxePageIco">{!! $ei('home', 15) !!}</i><span id="sxePageLbl">Page d’accueil</span><span class="sxe-caret">{!! $ei('caret', 15) !!}</span></button>
            <div class="sxe-menu" id="sxePageMenu" role="menu" hidden>
                <input class="sx-input" type="search" id="sxePageQ" placeholder="Rechercher une page ou un produit" aria-label="Rechercher une page ou un produit">
                <div id="sxePageList"></div>
            </div>
        </div>
        <div class="sxe-seg sxe-themes" role="group" aria-label="Thème modifié" id="sxeThemes">
            @foreach($ed['models'] as $k => [$label])
            <button type="button" data-theme="{{ $k }}" aria-pressed="{{ $k === $theme ? 'true' : 'false' }}">{{ $label }}@if($k === $ed['published'])<i>en ligne</i>@endif</button>
            @endforeach
        </div>
        <span class="sxe-state" id="sxeState" role="status" aria-live="polite"></span>
        <nav class="sxe-acts" id="sxeActs" aria-label="Actions">
            <button type="button" class="sx-btn" id="sxeUndo" title="Annuler">{!! $ei('undo', 16) !!}<span>Annuler</span></button>
            <button type="button" class="sx-btn" id="sxeSave" title="Enregistrer le brouillon">{!! $ei('save', 16) !!}<span>Enregistrer le brouillon</span></button>
            <button type="button" class="sx-btn" id="sxeShare" title="Partager un aperçu">{!! $ei('link', 16) !!}<span>Partager un aperçu</span></button>
            <button type="button" class="sx-btn sx-btn-main" id="sxePublish" title="Publier">Publier</button>
        </nav>
        {{-- Page d'un produit : la barre pilote l'éditeur de la page de vente (intégré ci-dessous) --}}
        <nav class="sxe-acts" id="sxePActs" aria-label="Actions du produit" hidden>
            <span class="sxe-pill" id="sxePStatus"></span>
            <a class="sx-btn" id="sxePView" target="_blank" rel="noopener" title="Voir la page du produit">{!! $ei('eye', 16) !!}<span>Voir</span></a>
            <button type="button" class="sx-btn" id="sxePSave" title="Sauvegarder la page du produit">{!! $ei('save', 16) !!}<span>Sauvegarder</span></button>
            <button type="button" class="sx-btn sx-btn-main" id="sxePPub" title="Publier le produit">Publier</button>
        </nav>
    </header>
    <div class="sxe-body">
        <aside class="sxe-side" id="sxeSide" aria-label="Modifier la page">
            <button type="button" class="sxe-sheetbtn" id="sxeSheet" aria-expanded="true">Réduire le panneau</button>
            <div class="sxe-tabs" role="tablist" aria-label="Réglages">
                <button type="button" role="tab" data-tab="sections" aria-selected="true">{!! $ei('sections', 16) !!} Sections</button>
                <button type="button" role="tab" data-tab="colors" aria-selected="false">{!! $ei('palette', 16) !!} Couleurs</button>
            </div>
            <div class="sxe-scroll" id="sxeSideB"></div>
        </aside>
        <div class="sxe-stage">
            <div class="sxe-stagebar">
                <div class="sxe-seg" role="group" aria-label="Appareil"><button type="button" data-dev="phone" aria-pressed="false" title="Téléphone">{!! $ei('mobile', 15) !!} Téléphone</button><button type="button" data-dev="desk" aria-pressed="true" title="Ordinateur">{!! $ei('desk', 15) !!} Ordinateur</button></div>
                <a class="sxe-view" id="sxeView" href="{{ $ed['urls']['public'] }}" target="_blank" rel="noopener">{!! $ei('eye', 15) !!} Voir</a>
            </div>
            <div class="sxe-frames" id="sxeFrames">
                <iframe id="sxeF0" title="Votre vitrine" src="{{ $ed['urls']['frame'] }}&modele={{ $theme }}"></iframe>
                <iframe id="sxeF1" title="Votre vitrine (mise à jour)" class="is-back" aria-hidden="true" tabindex="-1"></iframe>
                <div class="sxe-busy" id="sxeBusy" hidden>Mise à jour…</div>
            </div>
        </div>
        <div class="sxe-pmode" id="sxePMode" hidden></div>
    </div>
    <input type="file" id="sxeFile" accept="image/jpeg,image/png,image/webp" class="sx-sr" tabindex="-1">
    <input type="file" id="sxeShareFile" accept="image/jpeg,image/png,image/webp" class="sx-sr" tabindex="-1">
    <input type="file" id="sxeLogoFile" accept="image/jpeg,image/png,image/webp" class="sx-sr" tabindex="-1">
</div>

@include('vendeur.shop._section-fields')
<script>
(function () {
    'use strict';
    const G = @json($ed);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    let theme = G.theme;
    let X = window.SXFields(G, theme);
    const { esc, svg, IC } = X;
    let S = JSON.parse(JSON.stringify(G.state));
    let saved = JSON.stringify(S), hasDraft = G.hasDraft;
    let tab = 'sections', open = null, view = 'list', viewArg = null, menuFor = null, page = 'home';
    const PI = { home: 'M3 10.5 12 4l9 6.5V20H3zM9 20v-6h6v6', catalogue: 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z', panier: 'M3 4h2l2.4 11h10.2L20 7H6.2M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2M17 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2', tag: 'M3 12V4h8l10 10-8 8zM7.5 8.5h.01', back: 'M15 6l-6 6 6 6', check: 'M20 6 9 17l-5-5' };
    const PAGES = { home: [svg(PI.home, 16), 'Page d’accueil'], catalogue: [svg(PI.catalogue, 16), 'Page catalogue'], panier: [svg(PI.panier, 16), 'Panier'] };
    const ICO = { up: 'M12 19V5M5 12l7-7 7 7', down: 'M12 5v14M19 12l-7 7-7-7', dup: 'M8 8h12v12H8zM4 16V4h12', del: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3', back: 'M15 6l-6 6 6 6', grip: 'M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01', more: 'M12 5h.01M12 12h.01M12 19h.01', chev: 'm6 9 6 6 6-6', sec: 'M4 5h16v6H4zM4 15h16v4H4z', logo: 'M3 10l9-6 9 6v10H3zM9 20v-6h6v6', set: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 4.6 9a1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z', foot: 'M4 4h16v16H4zM4 15h16', plus: 'M12 5v14M5 12h14' };

    /* ───────── État : non enregistré, brouillon, publié ───────── */
    const dirty = () => JSON.stringify(S) !== saved;
    function paintState(msg, cls) {
        const el = $('sxeState');
        if (msg) { el.textContent = msg; el.className = 'sxe-state ' + (cls || ''); return; }
        if (dirty()) { el.textContent = 'Modifications non enregistrées'; el.className = 'sxe-state is-dirty'; }
        else if (hasDraft) { el.textContent = 'Brouillon pas encore publié'; el.className = 'sxe-state is-draft'; }
        else { el.textContent = 'Tout est publié'; el.className = 'sxe-state is-ok'; }
    }
    window.addEventListener('beforeunload', e => { if (dirty()) { e.preventDefault(); e.returnValue = ''; } });
    async function send(method, url, body) {
        let r, j = {};
        try { r = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: body ? JSON.stringify(body) : undefined }); j = await r.json().catch(() => ({})); }
        catch (x) { throw new Error('Connexion impossible : vérifiez votre internet puis réessayez.'); }
        if (!r.ok) throw new Error(j.error || (j.errors && Object.values(j.errors)[0][0]) || j.message || 'Une erreur est survenue. Réessayez.');
        return j;
    }
    const identityPayload = () => ({ name: S.identity.name, slogan: S.identity.slogan, color: S.identity.color, logo: S.identity.logo, share_src: S.identity.share_src, share_img: S.identity.share_img });
    const payload = () => ({ theme, page, home: S.home, settings: S.settings, colors: S.colors, identity: identityPayload() });

    /* ───────── Vitrine dans le cadre : rendue avec la version en cours, sans clignoter ───────── */
    const F = [$('sxeF0'), $('sxeF1')];
    let front = 0, renderT = null, renderSeq = 0, selected = null;
    function change(redraw) { paintState(); if (redraw) draw(); clearTimeout(renderT); renderT = setTimeout(render, 450); }
    async function render() {
        const seq = ++renderSeq;
        $('sxeBusy').hidden = false;
        try {
            const j = await send('POST', G.urls.render, payload());
            if (seq !== renderSeq) return;
            const back = F[1 - front];
            back.dataset.wanted = String(seq);
            back.src = j.url;
        } catch (e) { $('sxeBusy').hidden = true; paintState(e.message, 'is-err'); }
    }
    function frameReady(win) {
        const i = F.findIndex(f => f.contentWindow === win);
        if (i === -1) return;
        if (i !== front && F[i].dataset.wanted === String(renderSeq)) {
            try { if (page === 'home') win.scrollTo(0, F[front].contentWindow.scrollY); } catch (x) {}
            F[i].classList.remove('is-back'); F[i].removeAttribute('aria-hidden'); F[i].tabIndex = 0;
            F[front].classList.add('is-back'); F[front].setAttribute('aria-hidden', 'true'); F[front].tabIndex = -1;
            front = i;
            $('sxeBusy').hidden = true;
        }
        if (i === front && selected) try { win.SXF.select(selected, false); } catch (x) {}
    }
    function selectInFrame(id, scroll) { selected = id; try { F[front].contentWindow.SXF.select(id, scroll); } catch (x) {} }
    const viewUrl = () => G.urls.public + (page === 'catalogue' ? '?vue=produits' : '');

    /* ───────── Sections ───────── */
    const list = () => S.home[theme];
    const idx = id => list().findIndex(s => s.id === id);
    const newId = () => 's' + Math.random().toString(36).slice(2, 10).padEnd(8, '0');
    const customCount = () => list().filter(s => !X.isBuiltin(s.type)).length;
    const short = s => { try { return X.summary(s); } catch (x) { return ''; } };
    function act(a, id) {
        const i = idx(id), s = list()[i];
        if (!s) return;
        if (a === 'edit') { tab = 'sections'; view = 'list'; open = id; draw(); scrollToItem(id); selectInFrame(id, true); return; }
        if (a === 'up' || a === 'down') { const j = i + (a === 'up' ? -1 : 1); if (j < 0 || j >= list().length) return; [list()[i], list()[j]] = [list()[j], list()[i]]; change(true); return; }
        if (a === 'toggle') { s.on = !s.on; change(true); return; }
        if (a === 'dup') {
            if (X.isBuiltin(s.type)) return;
            if (customCount() >= G.limits.custom) return alert(G.limits.custom + ' sections ajoutées au maximum.');
            const c = JSON.parse(JSON.stringify(s)); c.id = newId();
            list().splice(i + 1, 0, c); open = c.id; change(true); return;
        }
        if (a === 'del') {
            if (X.isBuiltin(s.type)) return;
            if (!confirm('Supprimer la section « ' + G.types[s.type][0] + ' » ?')) return;
            list().splice(i, 1); if (open === id) open = null; change(true);
        }
    }
    // La vitrine dans le cadre appelle ces actions (barres des sections, « + Ajouter », en-tête)
    window.SXE = {
        frameReady,
        header() { tab = 'sections'; view = 'list'; open = 'logo'; draw(); scrollToItem('logo'); },
        add(afterId) { tab = 'sections'; view = 'library'; viewArg = afterId; draw(); openSheet(); },
        action(a, id) { act(a, id); openSheet(); },
    };
    function scrollToItem(id) { const el = document.querySelector('[data-item="' + CSS.escape(id) + '"]'); if (el) el.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); openSheet(); }

    /* ───────── Colonne de gauche ───────── */
    const sw = (data, on, dis, label) => '<button type="button" class="sxe-sw" role="switch" ' + data + ' aria-checked="' + !!on + '" aria-label="' + esc(label) + '"' + (dis ? ' disabled' : '') + '></button>';
    const ib = (data, d, label) => '<button type="button" class="sxe-ib" ' + data + ' aria-label="' + esc(label) + '" title="' + esc(label) + '">' + svg(d, 18) + '</button>';
    function block(key, icon, title, sub, body, extra) {
        const isOpen = open === key;
        return '<div class="sxe-it' + (isOpen ? ' is-open' : '') + '" data-item="' + key + '"><div class="sxe-row"><span class="sxe-ico">' + svg(icon, 16) + '</span>' +
            '<button type="button" class="sxe-name" data-toggle-open="' + key + '" aria-expanded="' + isOpen + '">' + esc(title) + (sub ? '<small>' + esc(sub) + '</small>' : '') + '</button>' + (extra || '') +
            ib('data-toggle-open="' + key + '"', ICO.chev, isOpen ? 'Replier' : 'Déplier').replace('<svg', '<svg class="sxe-chev"') + '</div>' +
            (isOpen ? '<div class="sxe-body-in">' + body() + '</div>' : '') + '</div>';
    }
    function sectionItem(s, i) {
        const t = G.types[s.type], isOpen = open === s.id, builtin = X.isBuiltin(s.type), n = list().length;
        const pop = menuFor === s.id ? '<div class="sxe-pop" role="menu">' +
            '<button type="button" data-act="up" ' + (i === 0 ? 'disabled' : '') + '>' + svg(ICO.up, 15) + ' Monter</button><button type="button" data-act="down" ' + (i === n - 1 ? 'disabled' : '') + '>' + svg(ICO.down, 15) + ' Descendre</button>' +
            (builtin ? '' : '<button type="button" data-act="dup">' + svg(ICO.dup, 15) + ' Dupliquer</button><button type="button" class="is-del" data-act="del">' + svg(ICO.del, 15) + ' Supprimer</button>') +
            (builtin ? '<button type="button" disabled title="Section du thème">Section du thème : pas supprimable</button>' : '') + '</div>' : '';
        return '<div class="sxe-it' + (isOpen ? ' is-open' : '') + (s.on ? '' : ' is-off') + '" data-item="' + s.id + '" data-sid="' + s.id + '" draggable="true">' +
            '<div class="sxe-row"><span class="sxe-grip" aria-hidden="true" title="Glisser pour déplacer">' + svg(ICO.grip, 16) + '</span>' +
            '<button type="button" class="sxe-name" data-toggle-open="' + s.id + '" aria-expanded="' + isOpen + '">' + esc(t[0]) + '<small>' + esc(short(s)) + '</small></button>' +
            sw('data-act="toggle"', s.on, false, (s.on ? 'Masquer ' : 'Afficher ') + t[0]) +
            '<span class="sxe-more">' + ib('data-menu="' + s.id + '" aria-haspopup="true" aria-expanded="' + (menuFor === s.id) + '"', ICO.more, 'Plus d’actions') + pop + '</span>' +
            ib('data-toggle-open="' + s.id + '"', ICO.chev, isOpen ? 'Replier' : 'Déplier').replace('<svg', '<svg class="sxe-chev"') + '</div>' +
            (isOpen ? '<div class="sxe-body-in">' + sectionFields(s, i) + '</div>' : '') + '</div>';
    }
    function sectionFields(s, i) {
        const t = G.types[s.type], fields = G.fields[s.type] || [];
        return '<p class="sx-hint" style="margin:0">' + esc(t[1]) + '</p>' +
            (X.aiWritable(s.type) ? X.aiRow() : '') +
            (fields.length ? fields.map(f => X.field(s, i, f)).join('') : '<p class="sx-hint">Rien à écrire : elle se remplit avec vos produits.</p>') +
            (s.type === 'stats' ? '<p class="sx-hint">Les chiffres viennent de votre boutique (avis, commandes livrées, produits) : ils ne peuvent pas être modifiés.</p>' : '') +
            (X.isBuiltin(s.type) ? '<p class="sx-hint">Section du thème : elle peut être masquée ou déplacée, pas supprimée. Un champ vide garde le texte prévu (affiché en gris).</p>' : '<button type="button" class="sx-btn sx-btn-sm sx-btn-danger" data-act="del" style="justify-self:start">' + svg(IC.trash, 15) + ' Supprimer la section</button>');
    }
    function draw() {
        document.querySelectorAll('[data-tab]').forEach(b => b.setAttribute('aria-selected', b.dataset.tab === tab ? 'true' : 'false'));
        const B = $('sxeSideB');
        if (tab === 'colors') { B.innerHTML = colorsTab(); return; }
        if (view === 'library') { B.innerHTML = libraryView(); return; }
        if (view === 'share') { B.innerHTML = shareView(); return; }
        const logo = block('logo', ICO.logo, 'Logo de la boutique', 'Nom, slogan, logo, photo de partage', headerBody);
        const settings = block('settings', ICO.set, 'Réglages de la page', 'Preuves de confiance, mode WhatsApp', settingsBody);
        const footer = block('footer', ICO.foot, 'Pied de page', 'Coordonnées et informations', footerBody);
        if (page !== 'home') {
            B.innerHTML = logo + '<p class="sxe-note">' + (page === 'catalogue' ? 'La page catalogue montre tous vos produits, avec les catégories, la recherche et le tri. Elle suit votre thème : ses couleurs se règlent dans l’onglet « Couleurs ».' : 'Le panier s’ouvre sur toutes les pages de votre vitrine. Il suit votre thème ; la commande se fait toujours en une minute, payée à la livraison.') + '</p>' + settings + footer;
            return;
        }
        B.innerHTML = logo +
            '<div class="sxe-hd"><div><b>Sections de la page</b><small>Glissez pour réordonner · « + Ajouter » en bas</small></div></div>' +
            list().map(sectionItem).join('') +
            (customCount() < G.limits.custom ? '<button type="button" class="sxe-add" data-add-end>' + svg(ICO.plus, 16) + ' Ajouter une section</button>' : '<p class="sx-hint">Maximum ' + G.limits.custom + ' sections ajoutées.</p>') +
            settings + footer;
    }

    /* ───────── Bibliothèque : ajouter une section ───────── */
    function libraryView() {
        return '<div class="sxe-sub"><button type="button" class="sxe-ib" data-back aria-label="Retour">' + svg(ICO.back, 18) + '</button><b>Ajouter une section</b></div>' +
            (customCount() >= G.limits.custom ? '<p class="sx-hint">Maximum ' + G.limits.custom + ' sections ajoutées.</p>' :
            '<input class="sx-input" type="search" id="sxeLibQ" placeholder="Rechercher une section…" aria-label="Rechercher une section">' +
            Object.entries(G.library).map(([k, [label, types]]) => '<div><b class="sx-lbl" style="margin:4px 0 6px">' + esc(label) + '</b><div class="sxe-lib">' + types.map(ty => '<button type="button" data-addtype="' + ty + '" data-q="' + esc((G.types[ty][0] + ' ' + G.types[ty][1]).toLowerCase()) + '"><b>+ ' + esc(G.types[ty][0]) + '</b><span>' + esc(G.types[ty][1]) + '</span></button>').join('') + '</div></div>').join(''));
    }

    /* ───────── Couleurs (par thème ; vide = couleurs du thème) ───────── */
    function colorsTab() {
        const c = S.colors[theme], d = G.themeColors[theme];
        return '<p class="sx-hint" style="margin:0">Couleurs du thème <b>' + esc(G.models[theme][0]) + '</b>. Chaque thème garde les siennes. Laissez « Par défaut » pour garder celles prévues par le thème.</p>' +
            Object.entries(G.colorKeys).map(([k, label]) => {
                const v = c[k] || d[k];
                return '<div class="sxe-color"><div class="sxe-color-hd"><b>' + esc(label) + '</b><code>' + esc(c[k] ? v : 'Par défaut') + '</code><input type="color" data-color-key="' + k + '" value="' + esc(v.toLowerCase()) + '" aria-label="' + esc(label) + '"></div>' +
                    (k === 'accent' ? '<div class="sxe-sws">' + G.palette.map(h => '<button type="button" class="sxe-swatch" data-color-set="' + k + '" data-v="' + h + '" aria-checked="' + (c[k] === h) + '" aria-label="' + h + '" style="background:' + h + '"></button>').join('') + '</div>' : '') +
                    (k === 'accent' ? '<span class="sx-hint">Boutons, prix et liens.</span>' : k === 'bg' ? '<span class="sx-hint">Le fond de la page, derrière les sections.</span>' : '<span class="sx-hint">Les titres et les textes. Gardez un bon contraste avec le fond.</span>') +
                    (c[k] ? '<button type="button" class="sx-btn sx-btn-sm" data-color-reset="' + k + '" style="justify-self:start">Par défaut</button>' : '') + '</div>';
            }).join('');
    }

    /* ───────── Logo de la boutique : nom, slogan, couleur, logo, photo de partage ───────── */
    const I = () => S.identity;
    const lum = h => [1, 3, 5].map(i => parseInt(h.substr(i, 2), 16) / 255).map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4)).reduce((a, v, i) => a + v * [0.2126, 0.7152, 0.0722][i], 0);
    const inkFor = c => (1.05 / (lum(c) + 0.05)) >= 3 ? '#FFFFFF' : '#16162A';
    let uploadedLogo = null;
    const initials = () => { const w = (I().name || 'S').replace(/[^\p{L}\p{N}\s]/gu, ' ').trim().split(/\s+/); return ((w[0] || 'S')[0] + (w[1] ? w[1][0] : '')).toUpperCase(); };
    const logoUrl = () => I().logo === 'initials' ? null : (I().logo === 'current' ? I().logoUrl : (uploadedLogo && uploadedLogo.path === I().logo ? uploadedLogo.url : I().logoUrl));
    const logoBox = () => logoUrl() ? '<img src="' + esc(logoUrl()) + '" alt="">' : '<span style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:' + I().color + ';color:' + inkFor(I().color) + '">' + esc(initials()) + '</span>';
    const shareUrl = () => I().share_src === 'photo' ? (G.coverUrls[I().share_img] || null) : I().share_src === 'product' ? G.productPhoto : null;
    function headerBody() {
        const colors = Object.assign({}, G.colors); if (!colors[I().color]) colors[I().color] = 'Votre couleur actuelle';
        const logos = [];
        if (G.state.identity.logoUrl) logos.push(['current', 'Logo actuel', '<img src="' + esc(G.state.identity.logoUrl) + '" alt="">']);
        logos.push(['initials', 'Initiales', '<span style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:' + I().color + ';color:' + inkFor(I().color) + '">' + esc(initials()) + '</span>']);
        logos.push(['upload', uploadedLogo ? 'Nouveau logo' : 'Importer', uploadedLogo ? '<img src="' + esc(uploadedLogo.url) + '" alt="">' : svg(ICO.plus, 20)]);
        const curLogo = I().logo === 'initials' ? 'initials' : I().logo === 'current' ? 'current' : 'upload';
        const shares = [['default', null], ['product', G.productPhoto], ['night', null], ['gold', null], ['blue', null]].filter(([k, u]) => k !== 'product' || u);
        if (I().share_img && G.coverUrls[I().share_img]) shares.push(['photo', G.coverUrls[I().share_img]]);
        const img = shareUrl();
        return '<div><label class="sx-lbl" for="sxeName">Nom de la boutique</label><input class="sx-input" id="sxeName" data-id="name" maxlength="' + G.shop.nameMax + '" value="' + esc(I().name) + '"><span class="sx-count" id="sxeNameN">' + I().name.length + ' / 30</span></div>' +
            '<div><label class="sx-lbl" for="sxeSlogan">Slogan</label><input class="sx-input" id="sxeSlogan" data-id="slogan" maxlength="60" value="' + esc(I().slogan) + '" placeholder="Ex. Montres et parfums authentiques à Conakry"><span class="sx-count" id="sxeSloganN">' + I().slogan.length + ' / 60</span></div>' +
            '<div><span class="sx-lbl">Couleur de la boutique</span><div class="sxe-sws" role="radiogroup" aria-label="Couleur de la boutique">' + Object.entries(colors).map(([hex, n]) => '<button type="button" class="sxe-swatch" role="radio" data-idcolor="' + hex + '" aria-checked="' + (I().color === hex) + '" aria-label="' + esc(n) + '" title="' + esc(n) + '" style="background:' + hex + '"></button>').join('') + '</div><span class="sx-hint">Logo en initiales, pages produit, thème Express.</span></div>' +
            '<div><span class="sx-lbl">Logo</span><div class="sxe-opts" role="radiogroup" aria-label="Logo">' + logos.map(([k, l, i]) => '<button type="button" class="sxe-opt" role="radio" data-logo="' + k + '" aria-checked="' + (curLogo === k) + '"><i>' + i + '</i>' + l + '</button>').join('') + '</div><p class="sx-hint">Logo carré, 512 × 512 px conseillé. PNG, JPG ou WebP, 2 Mo maximum.</p><p class="sx-msg" id="sxeLogoMsg" aria-live="polite"></p></div>' +
            '<div><span class="sx-lbl">Photo de partage</span><p class="sx-hint" style="margin:0 0 8px">L’image montrée quand le lien de votre boutique est partagé sur WhatsApp ou Facebook.</p><div class="sxe-opts" role="radiogroup" aria-label="Photo de partage">' + shares.map(([k, u]) => '<button type="button" class="sxe-opt" role="radio" data-share="' + k + '" aria-checked="' + (I().share_src === k) + '"><i' + (u ? '' : ' style="background:' + G.gradients[k] + '"') + '>' + (u ? '<img src="' + esc(u) + '" alt="">' : '') + '</i>' + esc(G.sources[k]) + '</button>').join('') +
                '<button type="button" class="sxe-opt" data-share-upload><i style="background:var(--sx-bg);color:var(--sx-blue)">' + svg(ICO.plus, 20) + '</i>Importer</button></div><p class="sx-msg" id="sxeShareMsg" aria-live="polite"></p></div>' +
            '<div><span class="sx-lbl">Partagée sur WhatsApp</span><div class="sxe-wa"><div class="sxe-wa-card"><div><div class="sxe-wa-img" style="background:' + (img ? '#0B1E3D' : (G.gradients[I().share_src] || G.gradients.default)) + '">' + (img ? '<img src="' + esc(img) + '" alt="">' : '') + '<span class="sxe-wa-logo">' + logoBox() + '</span></div><div class="sxe-wa-txt"><b>' + esc(I().name) + '</b><span>' + esc(I().slogan || G.shop.description || 'Boutique en ligne sur Shopio') + '</span><small>' + esc(G.shop.host.split('/')[0]) + '</small></div></div></div></div></div>' +
            '<div><span class="sx-lbl">Dans la recherche Shopio</span><div class="sxe-search"><i>' + logoBox() + '</i><div style="min-width:0"><b>' + esc(I().name) + (G.shop.verified ? ' <span class="sxe-pill">Vérifiée</span>' : '') + '</b><p>' + esc(I().slogan || G.shop.type || 'Boutique Shopio') + '</p></div></div></div>';
    }

    /* ───────── Réglages de la page : preuves de confiance, mode WhatsApp (chiffres réels) ───────── */
    function settingsBody() {
        const P = G.proofs, rt = P.response, set = S.settings;
        const rtTxt = rt.samples < P.responseMin ? 'Pas encore assez de messages (30 derniers jours)' : (rt.minutes <= 60 ? 'Délai habituel : ' + rt.minutes + ' min · affiché sur la vitrine' : 'Délai habituel : ' + (rt.minutes >= 120 ? Math.round(rt.minutes / 60) + ' h' : rt.minutes + ' min') + ' · pas affiché');
        return '<p class="sx-hint" style="margin:0">Preuves de confiance : affichées seulement quand elles sont vraies. Le badge vérifié et la note ne peuvent pas être masqués.</p>' +
            '<div class="sxe-r"><div><b>Boutique vérifiée</b><span>' + (G.shop.verified ? 'Votre boutique est vérifiée par Shopio' : 'En attente de vérification par Shopio') + '</span></div><span class="sxe-pill">' + (G.shop.verified ? 'Toujours affiché' : 'En attente') + '</span></div>' +
            '<div class="sxe-r"><div><b>' + P.delivered + ' commande' + (P.delivered > 1 ? 's' : '') + ' livrée' + (P.delivered > 1 ? 's' : '') + '</b><span>' + (P.delivered >= P.deliveredMin ? 'Affiché sur la vitrine' : 'Affiché à partir de ' + P.deliveredMin + ' commandes livrées') + '</span></div>' + sw('data-set="proof_delivered"', set.proof_delivered, false, 'Afficher le nombre de commandes livrées') + '</div>' +
            '<div class="sxe-r"><div><b>Répond en moins d’1 h</b><span>' + esc(rtTxt) + '</span></div>' + sw('data-set="proof_response"', set.proof_response, false, 'Afficher le délai de réponse') + '</div>' +
            '<div class="sxe-r"><div><b>' + (P.reviews >= P.ratingMin ? 'Note ' + String(P.reviewAvg).replace('.', ',') : 'Note') + '</b><span>' + (P.reviews >= P.ratingMin ? P.reviews + ' avis de clients livrés' : 'Pas encore de données · ' + P.ratingMin + ' avis minimum (' + P.reviews + ' pour l’instant)') + '</span></div><span class="sxe-pill">Verrouillée</span></div>' +
            '<div class="sxe-r"><div><b>Mode WhatsApp</b><span>Le bouton devient « Commander sur WhatsApp ». La commande reste enregistrée dans Shopio, puis le client l’envoie à la boutique avec un message déjà rempli.</span></div>' + sw('data-set="wa_mode"', set.wa_mode, !G.shop.phone, 'Mode WhatsApp') + '</div>' +
            (G.shop.phone ? '' : '<p class="sxe-note">Ajoutez d’abord le numéro WhatsApp de la boutique dans <a href="' + esc(G.urls.shopPhone) + '">Ma boutique</a>.</p>');
    }
    function footerBody() {
        return '<p class="sx-hint" style="margin:0">Le pied de page reprend le nom et le slogan de la boutique, vos coordonnées et les informations de commande (paiement à la livraison, confirmation par téléphone, suivi sur Shopio). Ces informations sont toujours vraies : elles ne se modifient pas ici.</p>' +
            '<a class="sx-btn sx-btn-sm" href="' + esc(G.urls.shopEdit) + '" style="justify-self:start">Modifier mes coordonnées (Ma boutique)</a>';
    }

    /* ───────── Partager un aperçu (48 h) : le brouillon, pour avoir un avis avant de publier ───────── */
    function shareView() {
        const p = G.preview;
        return '<div class="sxe-sub"><button type="button" class="sxe-ib" data-back aria-label="Retour">' + svg(ICO.back, 18) + '</button><b>Partager un aperçu</b></div>' +
            '<p class="sx-hint" style="margin:0">Faites relire votre vitrine avant de la publier : le lien montre votre <b>brouillon</b> pendant 48 h à qui le reçoit, avec un formulaire pour vous laisser un avis.</p>' +
            (p ? '<div class="sxe-link"><input class="sx-input" id="sxePvLink" value="' + esc(p.url) + '" readonly aria-label="Lien d’aperçu"><button type="button" class="sx-btn sx-btn-sm sx-btn-main" data-pv="copy">Copier</button></div>' +
                '<p class="sx-hint" style="margin:0">Valable jusqu’au ' + esc(p.expires) + '. ' + (p.feedback ? p.feedback + ' avis reçu' + (p.feedback > 1 ? 's' : '') + ' : <a href="' + esc(G.urls.quality) + '">les lire</a>.' : 'Aucun avis pour l’instant.') + '</p>' +
                '<div style="display:flex;gap:8px;flex-wrap:wrap"><a class="sx-btn sx-btn-sm" target="_blank" rel="noopener" href="https://wa.me/?text=' + encodeURIComponent('Peux-tu regarder la nouvelle vitrine de ' + I().name + ' avant que je la publie ? Ton avis m’aide : ' + p.url) + '">Envoyer sur WhatsApp</a><a class="sx-btn sx-btn-sm" href="' + esc(p.url) + '" target="_blank" rel="noopener">Ouvrir</a><button type="button" class="sx-btn sx-btn-sm sx-btn-danger" data-pv="off">Désactiver</button></div>' +
                '<button type="button" class="sx-btn sx-btn-sm" data-pv="new" style="justify-self:start">Mettre à jour avec mes derniers changements</button>'
              : '<button type="button" class="sx-btn sx-btn-main" data-pv="new" style="justify-self:start">Créer un lien d’aperçu</button>') +
            '<p class="sx-msg" id="sxePvMsg" aria-live="polite"></p>';
    }

    /* ───────── Clics dans la colonne ───────── */
    const side = $('sxeSideB');
    const curSection = el => { const it = el.closest('[data-sid]'); return it ? list()[idx(it.dataset.sid)] : null; };
    let uploadFor = null;
    side.addEventListener('click', async e => {
        const t = e.target;
        if (t.closest('[data-back]')) { view = 'list'; draw(); return; }
        const tg = t.closest('[data-toggle-open]');
        if (tg) { const k = tg.dataset.toggleOpen; open = open === k ? null : k; menuFor = null; draw(); if (open && idx(open) > -1) selectInFrame(open, true); return; }
        const mn = t.closest('[data-menu]'); if (mn) { menuFor = menuFor === mn.dataset.menu ? null : mn.dataset.menu; draw(); return; }
        const ad = t.closest('[data-add-end]'); if (ad) { view = 'library'; viewArg = list().length ? list()[list().length - 1].id : null; draw(); return; }
        const add = t.closest('[data-addtype]');
        if (add) {
            const s = { id: newId(), type: add.dataset.addtype, on: true, sp: [0, 0], d: X.blank(add.dataset.addtype) };
            const at = viewArg ? idx(viewArg) + 1 : 0;
            list().splice(at, 0, s); view = 'list'; open = s.id; change(true); scrollToItem(s.id); return;
        }
        // Couleurs
        const cs = t.closest('[data-color-set]'); if (cs) { S.colors[theme][cs.dataset.colorSet] = cs.dataset.v; change(true); return; }
        const cr = t.closest('[data-color-reset]'); if (cr) { S.colors[theme][cr.dataset.colorReset] = null; change(true); return; }
        // Réglages de la page
        const st = t.closest('[data-set]'); if (st) { if (st.disabled) return; S.settings[st.dataset.set] = !S.settings[st.dataset.set]; change(true); return; }
        // Logo de la boutique
        const ic = t.closest('[data-idcolor]'); if (ic) { I().color = ic.dataset.idcolor; change(true); return; }
        const lg = t.closest('[data-logo]');
        if (lg) {
            if (lg.dataset.logo === 'upload') { if (uploadedLogo && I().logo !== uploadedLogo.path) { I().logo = uploadedLogo.path; change(true); } else $('sxeLogoFile').click(); return; }
            I().logo = lg.dataset.logo; change(true); return;
        }
        const sh = t.closest('[data-share]'); if (sh) { I().share_src = sh.dataset.share; change(true); return; }
        if (t.closest('[data-share-upload]')) { $('sxeShareFile').click(); return; }
        // Aperçu partageable
        const pv = t.closest('[data-pv]'); if (pv) return shareClick(pv);
        // Section : actions et champs
        const s = curSection(t); if (!s) return;
        const a = t.closest('[data-act]'); if (a) { menuFor = null; act(a.dataset.act, s.id); return; }
        const d = s.d;
        const sai = t.closest('[data-sai]');
        if (sai) {
            const msg = sai.parentNode.querySelector('[data-sai-msg]');
            if (!G.aiPro) { msg.textContent = 'Shopio IA est réservé au plan Pro.'; return; }
            sai.disabled = true; msg.textContent = 'Shopio IA rédige cette section… (environ 15 secondes)';
            try { const res = await X.aiWrite(G.urls.ai, [s], { theme: theme, instructions: '' }); if (res[s.id]) s.d = res[s.id]; change(true); }
            catch (err) { msg.textContent = err.message; msg.className = 'sx-msg is-err'; sai.disabled = false; }
            return;
        }
        const aim = t.closest('[data-aimg]'); if (aim) { X.aiPhoto(s, aim, () => change(true)); return; }
        const sg = t.closest('[data-seg]'); if (sg) { d[sg.dataset.seg] = sg.dataset.seg === 'limit' ? +sg.dataset.v : sg.dataset.v; change(true); return; }
        const la = t.closest('[data-list-add]'); if (la) { const k = la.dataset.listAdd; if (d[k].length < X.MAX) { d[k].push(JSON.parse(JSON.stringify(X.newItem(X.kindOf(s.type, k))))); change(true); } return; }
        const ld = t.closest('[data-list-del]'); if (ld) { d[ld.dataset.listDel].splice(+ld.dataset.n, 1); change(true); return; }
        const ico = t.closest('[data-icon]'); if (ico) { d.items[+ico.dataset.n].icon = ico.dataset.icon; change(true); return; }
        const rt = t.closest('[data-rating]'); if (rt) { d.items[+rt.dataset.n].rating = +rt.dataset.rating; change(true); return; }
        const up = t.closest('[data-upload]'); if (up) { uploadFor = { s, mode: up.dataset.upload, k: up.dataset.lk, n: up.dataset.n === undefined ? null : +up.dataset.n }; $('sxeFile').click(); return; }
        if (t.closest('[data-img-del]')) { d.img = null; change(true); }
    });
    // Saisie : la vitrine suit pendant la frappe (la colonne n'est pas redessinée, le curseur reste en place)
    side.addEventListener('input', e => {
        const el = e.target;
        if (el.id === 'sxeLibQ') { const q = el.value.trim().toLowerCase(); document.querySelectorAll('[data-addtype]').forEach(b => { b.hidden = q && !b.dataset.q.includes(q); }); return; }
        if (el.dataset.colorKey) { S.colors[theme][el.dataset.colorKey] = el.value.toUpperCase(); const code = el.parentNode.querySelector('code'); if (code) code.textContent = el.value.toUpperCase(); change(false); return; }
        if (el.dataset.id) {
            I()[el.dataset.id] = el.value;
            const n = $(el.dataset.id === 'name' ? 'sxeNameN' : 'sxeSloganN'); if (n) n.textContent = el.value.length + ' / ' + (el.dataset.id === 'name' ? 30 : 60) + (el.dataset.id === 'name' && el.value.length > 30 ? ' · nom actuel gardé tel quel, raccourcissez-le pour le modifier' : '');
            change(false); return;
        }
        const s = curSection(el); if (!s) return;
        const d = s.d;
        if (el.dataset.k) { d[el.dataset.k] = el.value; const c = el.parentNode.querySelector('[data-count="' + el.dataset.k + '"]'); if (c) c.textContent = el.value.length + ' / ' + el.maxLength; }
        else if (el.dataset.lk && el.type !== 'checkbox') { const arr = d[el.dataset.lk], n = +el.dataset.n; if (el.dataset.f) arr[n][el.dataset.f] = el.value; else arr[n] = el.value; }
        else return;
        change(false);
    });
    side.addEventListener('change', e => {
        const el = e.target;
        if (el.dataset.colorKey) { draw(); return; }
        const s = curSection(el); if (!s) return;
        const d = s.d;
        if (el.tagName === 'SELECT' && el.dataset.k) d[el.dataset.k] = el.value;
        else if (el.dataset.bool) d[el.dataset.bool] = el.checked;
        else if (el.type === 'checkbox' && el.dataset.lk) d[el.dataset.lk][+el.dataset.n][el.dataset.f] = el.checked;
        else return;
        change(false);
    });
    // Glisser-déposer pour réordonner
    let dragId = null;
    side.addEventListener('dragstart', e => { const it = e.target.closest('[data-sid]'); if (!it || e.target.closest('input,textarea,select')) { e.preventDefault(); return; } dragId = it.dataset.sid; it.classList.add('is-drag'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', dragId); });
    side.addEventListener('dragover', e => { const it = e.target.closest('[data-sid]'); if (!dragId || !it) return; e.preventDefault(); document.querySelectorAll('.sxe-it.is-over').forEach(x => x.classList.remove('is-over')); it.classList.add('is-over'); });
    side.addEventListener('drop', e => { const it = e.target.closest('[data-sid]'); if (!dragId || !it) return; e.preventDefault(); const from = idx(dragId), to = idx(it.dataset.sid); if (from !== to && from > -1) { const [m] = list().splice(from, 1); list().splice(to, 0, m); change(true); } dragId = null; });
    side.addEventListener('dragend', () => { dragId = null; document.querySelectorAll('.is-drag,.is-over').forEach(x => x.classList.remove('is-drag', 'is-over')); });
    document.addEventListener('click', e => { if (menuFor && !e.target.closest('.sxe-more')) { menuFor = null; draw(); } });

    /* ───────── Photos (sections, logo, partage) ───────── */
    async function uploadImage(file, url, maxMb) {
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) throw new Error('Format refusé : JPG, PNG ou WebP.');
        if (file.size > maxMb * 1048576) throw new Error('Photo trop lourde : ' + maxMb + ' Mo maximum.');
        const fd = new FormData(); fd.append('file', file); fd.append('_token', CSRF);
        let r, j = {};
        try { r = await fetch(url, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } }); j = await r.json().catch(() => ({})); }
        catch (x) { throw new Error('Envoi impossible : vérifiez la connexion.'); }
        if (!r.ok) throw new Error((j.errors && j.errors.file && j.errors.file[0]) || j.message || 'Photo refusée.');
        return j;
    }
    $('sxeFile').addEventListener('change', async e => {
        const f = e.target.files[0]; e.target.value = '';
        const u = uploadFor; if (!f || !u) return;
        try {
            paintState('Envoi de la photo…');
            const j = await uploadImage(f, G.urls.upload, 5);
            G.images[j.path] = j.url;
            if (u.mode === 'img') u.s.d.img = j.path;
            else if (u.mode === 'new') u.s.d[u.k].push({ img: j.path, title: '', text: '', button: '', link: '' });
            else u.s.d[u.k][u.n].img = j.path;
            change(true);
        } catch (err) { paintState(err.message, 'is-err'); }
    });
    $('sxeLogoFile').addEventListener('change', async e => {
        const f = e.target.files[0]; e.target.value = ''; if (!f) return;
        try { paintState('Envoi du logo…'); uploadedLogo = await uploadImage(f, G.urls.logoUpload, 2); I().logo = uploadedLogo.path; change(true); }
        catch (err) { paintState(err.message, 'is-err'); }
    });
    $('sxeShareFile').addEventListener('change', async e => {
        const f = e.target.files[0]; e.target.value = ''; if (!f) return;
        try {
            paintState('Envoi de la photo…');
            const j = await uploadImage(f, G.urls.coverUpload, 5);
            G.coverUrls[j.path] = j.url; I().share_src = 'photo'; I().share_img = j.path; change(true);
            if (j.width && j.width < 1200) paintState('Image de ' + j.width + ' px de large : elle risque d’être floue, visez 1600 px.', 'is-err');
        } catch (err) { paintState(err.message, 'is-err'); }
    });

    async function shareClick(b) {
        const msg = $('sxePvMsg');
        try {
            if (b.dataset.pv === 'copy') { const v = $('sxePvLink').value; try { await navigator.clipboard.writeText(v); } catch (x) { $('sxePvLink').select(); document.execCommand && document.execCommand('copy'); } msg.textContent = 'Lien copié.'; return; }
            if (b.dataset.pv === 'off') { await send('DELETE', G.urls.preview); G.preview = null; draw(); return; }
            b.disabled = true; msg.textContent = 'Enregistrement du brouillon…';
            await saveDraft(true);
            const j = await send('POST', G.urls.preview);
            G.preview = { url: j.url, expires: j.expires, feedback: 0 }; draw();
            $('sxePvMsg').textContent = 'Lien prêt : il montre votre brouillon, pas encore la version en ligne.';
        } catch (err) { msg.textContent = err.message; msg.className = 'sx-msg is-err'; b.disabled = false; }
    }

    /* ───────── Sélecteur de page : accueil, catalogue, panier, produits ───────── */
    let pageSub = null; // null | 'products'
    function drawPageMenu() {
        const q = $('sxePageQ').value.trim().toLowerCase();
        const match = t => !q || t.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').includes(q.normalize('NFD').replace(/[̀-ͯ]/g, ''));
        const prods = G.products.filter(p => match(p.name));
        const item = (k, [ico, label]) => '<button type="button" class="sxe-mi" role="menuitem" data-page="' + k + '"' + (page === k ? ' aria-current="page"' : '') + '><i>' + ico + '</i><span>' + esc(label) + '</span>' + (page === k ? '<b>' + svg(PI.check, 16) + '</b>' : '') + '</button>';
        const prodItem = p => '<button type="button" class="sxe-mi" role="menuitem" data-pid="' + p.id + '"' + (prod && prod.id === p.id ? ' aria-current="page"' : '') + '><i>' + (p.thumb ? '<img src="' + esc(p.thumb) + '" alt="">' : svg(PI.tag, 16)) + '</i><span>' + esc(p.name) + (p.draft ? ' (brouillon)' : '') + '</span>' + (prod && prod.id === p.id ? '<b>' + svg(PI.check, 16) + '</b>' : '') + '</button>';
        if (pageSub === 'products' && !q) {
            $('sxePageList').innerHTML = '<button type="button" class="sxe-mi" data-psub=""><i>' + svg(PI.back, 16) + '</i><span>Produits</span></button><div class="sxe-plist">' + (G.products.length ? G.products.map(prodItem).join('') : '<p class="sx-hint" style="padding:8px">Aucun produit pour l’instant.</p>') + '</div>';
            return;
        }
        $('sxePageList').innerHTML = Object.entries(PAGES).filter(([k, v]) => match(v[1])).map(([k, v]) => item(k, v)).join('') +
            (q ? (prods.length ? '<div class="sxe-plist">' + prods.slice(0, 30).map(prodItem).join('') + '</div>' : '') :
                '<button type="button" class="sxe-mi" data-psub="products"><i>' + svg(PI.tag, 16) + '</i><span>Produits</span><small>' + G.products.length + ' ›</small></button>') +
            (q && !prods.length && !Object.values(PAGES).some(v => match(v[1])) ? '<p class="sx-hint" style="padding:8px">Aucune page ni aucun produit pour « ' + esc(q) + ' ».</p>' : '');
    }
    function toggleMenu(show) { const m = $('sxePageMenu'); m.hidden = !show; $('sxePageBtn').setAttribute('aria-expanded', show ? 'true' : 'false'); if (show) { pageSub = null; $('sxePageQ').value = ''; drawPageMenu(); $('sxePageQ').focus(); } }
    $('sxePageBtn').addEventListener('click', () => toggleMenu($('sxePageMenu').hidden));
    $('sxePageQ').addEventListener('input', drawPageMenu);
    $('sxePageList').addEventListener('click', e => {
        const sub = e.target.closest('[data-psub]'); if (sub) { pageSub = sub.dataset.psub || null; drawPageMenu(); return; }
        const pr = e.target.closest('[data-pid]');
        if (pr) { const p = G.products.find(x => x.id === +pr.dataset.pid); toggleMenu(false); if (p) openProduct(p); return; }
        const p = e.target.closest('[data-page]'); if (!p) return;
        if (!leaveProduct()) return;
        page = p.dataset.page;
        $('sxePageIco').innerHTML = PAGES[page][0]; $('sxePageLbl').textContent = PAGES[page][1];
        $('sxeView').href = viewUrl();
        toggleMenu(false); open = null; view = 'list'; draw(); clearTimeout(renderT); render();
    });

    /* ───────── Page d'un produit : l'éditeur de la page de vente, dans le même écran ───────── */
    let prod = null, pwin = null, pobs = null;
    const pdoc = () => { try { return pwin && pwin.document; } catch (x) { return null; } };
    const productDirty = () => { try { return !!(pwin && pwin.PE && pwin.PE.dirty()); } catch (x) { return false; } };
    function setMode(product) {
        const on = !!product;
        document.querySelector('.sxe-side').hidden = on; document.querySelector('.sxe-stage').hidden = on;
        $('sxePMode').hidden = !on; $('sxeActs').hidden = on; $('sxeThemes').hidden = on; $('sxePActs').hidden = !on;
    }
    /** Quitte la page du produit (après accord s'il reste des changements). Faux si le vendeur reste. */
    function leaveProduct() {
        if (!prod) return true;
        if (productDirty() && !confirm('Les changements de la page « ' + prod.name + ' » ne sont pas sauvegardés et seront perdus. Continuer ?')) return false;
        try { pwin && pwin.PE && pwin.PE.leave(); } catch (x) {}
        if (pobs) pobs.disconnect();
        $('sxePMode').innerHTML = ''; prod = null; pwin = null;
        setMode(null); paintState(); syncUrl();
        return true;
    }
    // L'adresse suit le produit ouvert : un rechargement de la page y revient
    function syncUrl() {
        const u = new URL(location.href);
        if (prod) u.searchParams.set('produit', prod.id); else u.searchParams.delete('produit');
        u.searchParams.delete('ouvrir');
        history.replaceState(null, '', u.pathname + u.search);
    }
    function openProduct(p) {
        if (prod && prod.id === p.id) return;
        if (!leaveProduct()) return;
        prod = p; page = 'product';
        $('sxePageIco').innerHTML = svg(PI.tag, 15); $('sxePageLbl').textContent = p.name;
        $('sxePView').href = p.public;
        setMode(p); syncUrl();
        $('sxePStatus').textContent = 'Chargement…'; $('sxePStatus').className = 'sxe-pill';
        paintState('Page de vente : « ' + p.name + ' »', 'is-draft');
        const f = document.createElement('iframe');
        f.title = 'Page de vente de ' + p.name; f.src = p.url;
        f.addEventListener('load', () => { pwin = f.contentWindow; mirror(); });
        $('sxePMode').appendChild(f);
    }
    // La barre reflète l'éditeur intégré : état, statut du produit, bouton Publier / Dépublier
    function mirror() {
        const d = pdoc(); if (!d || !d.getElementById('peState')) return;
        const paint = () => {
            const st = d.getElementById('peState'), pub = d.getElementById('pePublish'), status = d.getElementById('peStatus');
            const cls = st.classList.contains('is-dirty') ? 'is-dirty' : st.classList.contains('is-err') ? 'is-err' : st.classList.contains('is-ok') ? 'is-ok' : '';
            paintState(st.textContent.trim(), cls);
            $('sxePPub').textContent = pub.textContent.trim(); $('sxePPub').disabled = pub.disabled;
            $('sxePStatus').textContent = status.textContent.trim(); $('sxePStatus').className = 'sxe-pill ' + (status.classList.contains('is-on') ? 'is-on' : 'is-draft');
            $('sxePSave').disabled = d.getElementById('peSave').disabled;
        };
        if (pobs) pobs.disconnect();
        pobs = new MutationObserver(paint);
        ['peState', 'pePublish', 'peStatus', 'peSave'].forEach(id => { const el = d.getElementById(id); if (el) pobs.observe(el, { childList: true, characterData: true, subtree: true, attributes: true }); });
        paint();
    }
    $('sxePSave').addEventListener('click', () => { const d = pdoc(); if (d) d.getElementById('peSave').click(); });
    $('sxePPub').addEventListener('click', () => { const d = pdoc(); if (d) d.getElementById('pePublish').click(); });
    document.addEventListener('click', e => { if (!$('sxePageMenu').hidden && !e.target.closest('.sxe-pagebox')) toggleMenu(false); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !$('sxePageMenu').hidden) { toggleMenu(false); $('sxePageBtn').focus(); } });

    /* ───────── Barre : thème, onglets, appareil, Annuler, Enregistrer, Publier, Retour ───────── */
    $('sxeThemes').addEventListener('click', e => {
        const b = e.target.closest('[data-theme]'); if (!b || b.dataset.theme === theme) return;
        theme = b.dataset.theme; X = window.SXFields(G, theme);
        document.querySelectorAll('[data-theme]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        open = null; view = 'list'; draw(); clearTimeout(renderT); render();
    });
    document.querySelectorAll('[data-tab]').forEach(b => b.addEventListener('click', () => { tab = b.dataset.tab; view = 'list'; draw(); }));
    document.querySelectorAll('[data-dev]').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('[data-dev]').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
        $('sxeFrames').classList.toggle('is-phone', b.dataset.dev === 'phone');
    }));
    function openSheet() { $('sxeSide').classList.remove('is-down'); $('sxeSheet').textContent = 'Réduire le panneau'; $('sxeSheet').setAttribute('aria-expanded', 'true'); }
    $('sxeSheet').addEventListener('click', () => {
        const down = $('sxeSide').classList.toggle('is-down');
        $('sxeSheet').textContent = down ? 'Ouvrir le panneau' : 'Réduire le panneau';
        $('sxeSheet').setAttribute('aria-expanded', down ? 'false' : 'true');
    });
    async function saveDraft(quiet) {
        await send('PUT', G.urls.draft, payload());
        saved = JSON.stringify(S); hasDraft = true;
        if (!quiet) paintState('Brouillon enregistré · vos clients voient encore la version en ligne', 'is-draft'); else paintState();
    }
    $('sxeSave').addEventListener('click', async () => { const b = $('sxeSave'); b.disabled = true; try { await saveDraft(false); } catch (err) { paintState(err.message, 'is-err'); } b.disabled = false; });
    $('sxeShare').addEventListener('click', () => { tab = 'sections'; view = 'share'; draw(); openSheet(); });
    $('sxePublish').addEventListener('click', async () => {
        if (!confirm('Publier ces changements ? Vos clients verront la nouvelle vitrine tout de suite.')) return;
        const b = $('sxePublish'); b.disabled = true; b.textContent = 'Publication…';
        try { await send('POST', G.urls.publish, payload()); saved = JSON.stringify(S); hasDraft = false; paintState('Publié : vos clients voient maintenant cette vitrine', 'is-ok'); }
        catch (err) { paintState(err.message, 'is-err'); }
        b.disabled = false; b.textContent = 'Publier';
    });
    $('sxeUndo').addEventListener('click', async () => {
        if (dirty()) {
            if (!confirm('Annuler les modifications faites depuis le dernier enregistrement ?')) return;
            S = JSON.parse(saved); uploadedLogo = null; draw(); clearTimeout(renderT); render(); paintState(); return;
        }
        if (hasDraft) {
            if (!confirm('Revenir à la version en ligne ? Le brouillon enregistré sera supprimé.')) return;
            try { await send('DELETE', G.urls.draft); hasDraft = false; location.reload(); } catch (err) { paintState(err.message, 'is-err'); }
            return;
        }
        paintState('Rien à annuler : tout est publié', 'is-ok');
    });
    $('sxeQuit').addEventListener('click', () => {
        if (!leaveProduct()) return;
        if (dirty() && !confirm('Vos modifications ne sont pas enregistrées et seront perdues. Quitter quand même ?')) return;
        if (!dirty() && hasDraft && !confirm('Votre brouillon n’est pas publié : vos clients voient encore l’ancienne version. Quitter quand même ?')) return;
        saved = JSON.stringify(S);
        location.href = G.urls.public;
    });

    paintState();
    if (G.open === 'entete') open = 'logo';
    else if (G.open === 'reglages') open = 'settings';
    else if (G.open === 'apercu') view = 'share';
    draw();
    // Lien direct …?edition=1&produit=92 : l'éditeur s'ouvre sur la page de vente de ce produit
    if (G.openProduct) { const p = G.products.find(x => x.id === G.openProduct); if (p) openProduct(p); }
})();
</script>
</body>
</html>
