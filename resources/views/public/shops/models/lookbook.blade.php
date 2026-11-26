{{--
    Modèle de page « Lookbook » : le défilé « Essaie le look » (mode, pagne, parfums, accessoires).
      Défilé  : scène blanche ; les looks détourés tournent sur un carrousel 3D (silhouettes du fond floutées)
                sur ordinateur, marchent en diagonale sur téléphone ; barre 01 → N qui avance toute seule
                (5 s par look), flèches, pause ; curseur « Glisser / Voir » ; balayage à la souris ou au doigt.
      Voir    : le look grandit dans une fenêtre avec un point numéroté sur chaque pièce (placé par le vendeur
                dans Boutique → Looks) ; toucher un point montre la pièce dans le panneau.
      Panneau : « Dans ce look » : chaque pièce avec son prix, « + » pour choisir motif et taille, « Compléter
                le look » (total) et « Ajouter le look complet ». Sur téléphone : pièces en carrousel sous la
                scène, choix de la taille dans un volet qui monte du bas.
      Panier  : gardé sur le téléphone du client ; commande Shopio payée à la livraison (nom, téléphone,
                commune, repère), motif et taille transmis ; paniers abandonnés comme les autres thèmes.
      Toute la boutique : les produits qui ne tiennent pas dans le défilé, en grille sous la scène.
    Données : PageModels::data() + App\Support\Lookbook::data() (défilé, pièces, motifs, tailles, points).
--}}
@php
    $lb = \App\Support\Lookbook::data($shop, $pm);
    $s = $pm['shop'];
    $waDigits = preg_replace('/\D/', '', (string) $shop->phone);
    if (strlen($waDigits) === 9) $waDigits = '224' . $waDigits;
    $wa = strlen($waDigits) >= 8 ? 'https://wa.me/' . $waDigits . '?text=' . rawurlencode('Bonjour ' . $shop->name . ', j’ai une question sur un produit.') : null;
    $nRun = count($lb['runway']);
    $sub = $lb['looks'] > 0
        ? $lb['looks'] . ' ' . ($lb['looks'] > 1 ? $lb['word']['plural'] : mb_strtolower($lb['word']['one'])) . ', ' . $lb['word']['sub'] . '. Touche un point pour voir la pièce.'
        : 'Glisse pour parcourir la boutique. Touche un produit pour le voir de près.';
@endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
<style>
:root { color-scheme: light; }
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; max-width: 100%; font-family: Helvetica, Arial, sans-serif; color: #151413; background: #fff; }
body { overflow-x: hidden; }
button, input, select { font: inherit; }
a { color: inherit; text-decoration: none; }
button { color: inherit; }
button, a { -webkit-tap-highlight-color: transparent; }
button:focus-visible, a:focus-visible { outline: 2px solid #151413; outline-offset: 4px; }
[hidden] { display: none !important; }

/* ── Bandeau + en-tête ── */
.rwp-promise { text-align: center; padding: 7px 12px; font-size: 10px; letter-spacing: .16em; text-transform: uppercase; color: #66625c; border-bottom: 1px solid #ececec; }
.site-header { height: 76px; padding: 0 40px; display: flex; align-items: center; gap: 28px; border-bottom: 1px solid #e4e4e4; background: #fff; position: relative; z-index: 50; }
.brand { margin-right: auto; display: inline-flex; align-items: center; gap: 10px; min-width: 0; }
.brand i { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 999px; overflow: hidden; background: #151413; color: #fff; font: 600 12px Helvetica, Arial, sans-serif; font-style: normal; flex-shrink: 0; }
.brand i img { width: 100%; height: 100%; object-fit: cover; }
.brand b { font-weight: 600; font-size: 15px; letter-spacing: .08em; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.site-nav { display: flex; gap: 26px; font-size: 13px; font-weight: 600; }
.site-nav a { padding: 12px 0; white-space: nowrap; }
.site-nav a:hover { text-decoration: underline; text-underline-offset: 4px; }
.site-nav a[aria-current="page"] { text-decoration: underline; text-underline-offset: 4px; }
.bag-btn { display: flex; align-items: center; justify-content: center; gap: 5px; background: none; border: 0; min-width: 44px; min-height: 44px; cursor: pointer; }
.bag-btn span { font-size: 11px; font-weight: 600; min-width: 10px; }

/* ── Section défilé ── (réglages sur :root : le volet du bas, hors de la section, en a besoin aussi) */
:root {
    --rw-stage: #ffffff; --rw-panel: #ffffff; --rw-text: #151413; --rw-muted: #66625c; --rw-accent: #0e0e0e; --rw-on-accent: #fff; --rw-line: #c8c8c8;
    --rw-h: 46px; --rw-sub: 12px; --rw-label: 10px; --rw-chip: 17px; --rw-ph: 18px; --rw-pt-size: 13px; --rw-price: 12px; --rw-opt: 11px; --rw-total: 16px; --rw-thumb: 92px;
    --rw-panel-w: 380px; --rw-panel-pad: 32px; --rw-pill-radius: 30px; --rw-add-radius: 20px; --rw-bar-r: 20px; --rw-speed: 5s;
    --rw-ease: cubic-bezier(.16,1,.3,1); --rw-ease-silk: cubic-bezier(.65,0,.35,1); --rw-ease-spring: cubic-bezier(.34,1.56,.64,1); --rw-ease-sheet: cubic-bezier(.32,.72,0,1);
}
.rw {
    display: block; position: relative; color: var(--rw-text); background: var(--rw-panel); -webkit-font-smoothing: antialiased;
}
.rw__sticky { height: var(--rw-frame-h, 100svh); display: flex; flex-direction: column; }
.rw__grid { flex: 1 1 auto; min-height: 0; display: grid; grid-template-columns: minmax(0,1fr) var(--rw-panel-w); grid-template-rows: minmax(0,1fr); width: 100%; }
.rw__left { position: relative; display: flex; flex-direction: column; background: var(--rw-stage); overflow: hidden; min-height: 0; }
.rw__panel { position: relative; background: var(--rw-panel); border-left: 1px solid var(--rw-line); }
.rw__panel-inner { position: absolute; inset: 0; display: flex; flex-direction: column; }
.rw__sr { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }

.rw__head { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; padding: 40px 56px 0; position: relative; z-index: 5; }
.rw__intro { display: flex; flex-direction: column; gap: 12px; max-width: 640px; }
.rw__title { margin: 0; font-weight: 400; font-size: var(--rw-h); line-height: 1; letter-spacing: -.02em; }
.rw__sub { margin: 0; font-size: var(--rw-sub); line-height: 1.55; color: var(--rw-muted); max-width: 46ch; }

/* Scène */
.rw__stage { position: relative; flex: 1 1 auto; min-height: 0; touch-action: pan-y; user-select: none; -webkit-user-select: none; cursor: grab; outline: none; overflow: hidden; z-index: 0; }
.rw.is-dragging .rw__stage { cursor: grabbing; }
.rw__stage:focus-visible { box-shadow: inset 0 0 0 2px var(--rw-accent); }
.rw__actors { position: absolute; inset: 0; }
.rw__model { position: absolute; left: 0; bottom: 0; transform-origin: 50% 100%; will-change: transform, opacity; opacity: 0; }
.rw__figure { position: relative; height: 560px; width: 315px; }
.rw__art { position: absolute; inset: 0; transform: translateY(calc(var(--rw-foot, 0) * 100%)); }
.rw__art img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; object-position: 50% 100%; pointer-events: none; -webkit-user-drag: none; mix-blend-mode: multiply; opacity: 0; }
.rw__art img.is-decoded { opacity: 1; }
.rw__parts { position: absolute; inset: 0; display: flex; align-items: flex-end; justify-content: center; pointer-events: none; }
.rw__parts img { position: static; inset: auto; width: auto; height: 100%; flex: 0 0 auto; }
.rw__hit { position: absolute; inset: 0; z-index: 1; background: transparent; border: 0; border-radius: 24px; cursor: pointer; padding: 0; }
.rw__hit.is-front { cursor: zoom-in; }
.rw__hit:focus-visible { outline: 2px solid var(--rw-accent); outline-offset: 6px; }
.rw__shadow { position: absolute; left: 55%; bottom: 0; width: 120%; height: 9%; min-height: 14px; max-height: 46px; transform: translate(-50%, 50%); filter: blur(2px); background: radial-gradient(closest-side, rgb(20 16 12/.30), rgb(20 16 12/.14) 45%, rgb(20 16 12/.04) 75%, rgb(20 16 12/0)); pointer-events: none; }

/* Balayage lumineux */
.rw__sweep { position: absolute; inset: 0; z-index: 1550; pointer-events: none; mix-blend-mode: soft-light; overflow: hidden; }
.rw__sweep::before { content: ''; position: absolute; top: -20%; bottom: -20%; width: 42%; background: linear-gradient(100deg, transparent, rgb(255 255 255/.25) 42%, rgb(255 255 255/.55) 50%, rgb(255 255 255/.25) 58%, transparent); transform: translateX(-130%) skewX(-12deg); opacity: 0; }
.rw__stage.is-sweep .rw__sweep::before { animation: rw-sweep 1.5s var(--rw-ease-silk); }
@keyframes rw-sweep { 0% { transform: translateX(-130%) skewX(-12deg); opacity: 0; } 18% { opacity: 1; } 100% { transform: translateX(330%) skewX(-12deg); opacity: 0; } }

/* Curseur « Glisser / Voir » */
.rw--cursor .rw__stage, .rw--cursor .rw__stage * { cursor: none !important; }
.rw__cursor { position: absolute; left: 0; top: 0; width: 0; height: 0; z-index: 1700; pointer-events: none; }
.rw__cursor-in { position: absolute; left: -42px; top: -42px; width: 84px; height: 84px; border-radius: 50%; display: grid; place-items: center; color: var(--rw-text); border: 1px solid color-mix(in srgb, var(--rw-text) 28%, transparent); background: color-mix(in srgb, #fff 40%, transparent); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); opacity: 0; transform: scale(.3); transition: transform .6s var(--rw-ease), opacity .35s, background .35s, border-color .35s, color .35s; }
.rw__cursor-label { grid-area: 1/1; font-size: 10px; letter-spacing: .24em; padding-left: .24em; text-transform: uppercase; white-space: nowrap; transition: opacity .25s, letter-spacing .7s var(--rw-ease); }
.rw__cursor-view { opacity: 0; }
.rw__cursor.is-on .rw__cursor-in { opacity: 1; transform: scale(1); }
.rw__cursor.is-view .rw__cursor-drag { opacity: 0; } .rw__cursor.is-view .rw__cursor-view { opacity: 1; }
.rw__cursor.is-link .rw__cursor-in { transform: scale(.2); background: var(--rw-accent); border-color: transparent; }
.rw__cursor.is-link .rw__cursor-label { opacity: 0; }
.rw.is-dragging .rw__cursor-in { transform: scale(.74); background: var(--rw-accent); border-color: var(--rw-accent); color: var(--rw-on-accent); }
.rw.is-dragging .rw__cursor-drag { letter-spacing: .42em; opacity: 1; } .rw.is-dragging .rw__cursor-view { opacity: 0; }

/* Ligne de progression (téléphone) */
.rw__walk { display: none; }

/* Commandes */
.rw__controls { display: flex; align-items: center; gap: 20px; padding: 6px 44px 26px; position: relative; z-index: 5; }
.rw__icon { width: 44px; height: 44px; display: grid; place-items: center; border: 0; background: none; color: var(--rw-text); position: relative; cursor: pointer; flex-shrink: 0; }
.rw__icon::before { content: ''; position: absolute; inset: 2px; border-radius: 50%; border: 1px solid currentColor; opacity: 0; transform: scale(.55); transition: transform .7s var(--rw-ease), opacity .4s; }
.rw__icon:hover::before { opacity: .22; transform: scale(1); }
.rw__icon svg { transition: transform .5s cubic-bezier(.2,1.6,.4,1); }
.rw__icon[data-rw-prev]:hover svg { transform: translateX(-4px); } .rw__icon[data-rw-next]:hover svg { transform: translateX(4px); }
.rw__icon:active svg { transform: scale(.88); }
.rw__icon:focus-visible { outline: 2px solid var(--rw-accent); outline-offset: 2px; border-radius: 4px; }
.rw__auto .i-pause { display: none; } .rw.is-playing .rw__auto .i-pause { display: block; } .rw.is-playing .rw__auto .i-play { display: none; }
.rw__ticks { flex: 1; display: flex; gap: 6px; min-width: 0; }
.rw__tick { flex: 0 1 44px; min-width: 14px; height: 44px; display: flex; flex-direction: column; justify-content: center; gap: 9px; padding: 0; border: 0; background: none; color: var(--rw-muted); text-align: left; cursor: pointer; transition: flex-basis .8s cubic-bezier(.2,1.25,.3,1), color .3s; }
.rw__tick[aria-current="true"] { flex: 0 0 190px; color: var(--rw-text); }
.rw__tick:hover { color: var(--rw-text); }
.rw__tick-bar { position: relative; display: block; width: 100%; height: 2px; overflow: hidden; background: color-mix(in srgb, var(--rw-text) 14%, transparent); transition: transform .5s var(--rw-ease); }
.rw__tick:hover .rw__tick-bar { transform: scaleY(1.5); }
.rw__tick-bar::after { content: ''; position: absolute; inset: 0; background: var(--rw-text); transform: scaleX(0); transform-origin: left; transition: transform 1s var(--rw-ease), opacity .4s, background .4s; }
.rw__tick.is-past .rw__tick-bar::after { transform: scaleX(1); opacity: .35; }
.rw__tick[aria-current="true"] .rw__tick-bar::after { transform: scaleX(1); background: var(--rw-accent); opacity: 1; }
.rw.is-playing .rw__tick[aria-current="true"] .rw__tick-bar::after { transform: scaleX(0); transition: none; }
.rw.is-playing .rw__tick[aria-current="true"].is-run .rw__tick-bar::after { animation: rw-fill var(--rw-speed) linear forwards; }
@keyframes rw-fill { from { transform: scaleX(0); } to { transform: scaleX(1); } }
.rw__tick-label { display: block; font-size: 10px; letter-spacing: .12em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-variant-numeric: tabular-nums; }
.rw__tick-name { display: none; }
.rw__tick[aria-current="true"] .rw__tick-name { display: inline; animation: rw-name-in 1.1s var(--rw-ease) .1s backwards; }
@keyframes rw-name-in { from { opacity: 0; letter-spacing: .34em; } }

/* Panneau « Dans ce look » */
.rw__panel-head { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; padding: 40px var(--rw-panel-pad) 18px; }
.rw__panel-title { margin: 0; font-weight: 400; font-size: var(--rw-ph); line-height: 1.1; }
.rw__label { font-size: var(--rw-label); letter-spacing: .16em; text-transform: uppercase; color: var(--rw-muted); white-space: nowrap; }
.rw__label.is-swap { animation: rw-fade .8s var(--rw-ease); }
@keyframes rw-fade { from { opacity: 0; transform: translateY(6px); } }
.rw__look-panel { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; }
.rw__mhead { display: none; }
.rw__items { list-style: none; margin: 0; padding: 6px var(--rw-panel-pad) 20px; display: flex; flex-direction: column; overflow-y: auto; flex: 1 1 auto; min-height: 0; overscroll-behavior: contain; scrollbar-width: thin; }
.rw__item { position: relative; display: grid; grid-template-columns: var(--rw-thumb) minmax(0,1fr) 44px; column-gap: 18px; align-items: center; padding: 18px 0; border-top: 1px solid var(--rw-line); }
.rw__item:last-child { border-bottom: 1px solid var(--rw-line); }
.rw__item::after { content: ''; position: absolute; left: 0; right: 0; bottom: -1px; height: 1px; background: var(--rw-accent); transform: scaleX(0); transform-origin: left; transition: transform .9s var(--rw-ease); }
.rw__item.is-hot::after { transform: scaleX(1); }
@media (hover: hover) { .rw__item:hover::after { transform: scaleX(1); } }
.rw__media { position: relative; display: block; aspect-ratio: 4/5; overflow: hidden; background: color-mix(in srgb, var(--rw-text) 5%, #fff); border: 0; padding: 0; cursor: pointer; width: 100%; }
.rw__media img { width: 100%; height: 100%; object-fit: contain; display: block; transition: transform 1.4s var(--rw-ease); mix-blend-mode: multiply; }
@media (hover: hover) { .rw__item:hover .rw__media img { transform: scale(1.07); } }
.rw__num { position: absolute; top: 6px; right: 6px; width: 20px; height: 20px; border-radius: 50%; display: grid; place-items: center; background: rgb(255 255 255/.9); color: var(--rw-text); font-size: 10px; font-weight: 600; transition: background .3s, color .3s, transform .45s cubic-bezier(.2,1.6,.4,1); }
.rw__item.is-hot .rw__num { background: var(--rw-accent); color: var(--rw-on-accent); transform: scale(1.12); }
@media (hover: hover) { .rw__item:hover .rw__num { background: var(--rw-accent); color: var(--rw-on-accent); transform: scale(1.12); } }
.rw__tag { position: absolute; top: 11px; left: 8px; z-index: 2; display: flex; flex-direction: column; transform-origin: 10px 0; pointer-events: none; }
.rw__tag::before { content: ''; width: 1px; height: 11px; margin-left: 10px; background: color-mix(in srgb, var(--rw-text) 50%, transparent); }
.rw__tag-text { background: var(--rw-text); color: var(--rw-panel); font-size: 9px; font-weight: 600; letter-spacing: .14em; text-transform: uppercase; padding: 4px 6px; border-radius: 2px; }
@media (hover: hover) { .rw__item:hover .rw__tag { animation: rw-swing 1.4s cubic-bezier(.3,.7,.4,1); } }
@keyframes rw-swing { 0% { transform: rotate(-16deg); } 25% { transform: rotate(11deg); } 45% { transform: rotate(-7deg); } 65% { transform: rotate(4deg); } 82% { transform: rotate(-2deg); } 100% { transform: none; } }
.rw__info { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
.rw__type { font-size: 10px; letter-spacing: .14em; text-transform: uppercase; color: var(--rw-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rw__name { font-size: var(--rw-pt-size); font-weight: 500; line-height: 1.3; align-self: flex-start; background: linear-gradient(currentColor,currentColor) 0 100% / 0 1px no-repeat; transition: background-size .7s var(--rw-ease); border: 0; padding: 0; text-align: left; cursor: pointer; }
.rw__name:hover { background-size: 100% 1px; }
.rw__price { font-size: var(--rw-price); display: flex; gap: 8px; flex-wrap: wrap; }
.rw__price s { color: var(--rw-muted); }
.rw__sel { font-size: 11px; color: var(--rw-muted); }
.rw__add { width: 44px; height: 44px; display: grid; place-items: center; border: 0; background: none; align-self: end; position: relative; cursor: pointer; padding: 0; }
.rw__add svg { grid-area: 1/1; transition: transform .55s cubic-bezier(.2,1.6,.4,1), opacity .2s; }
.rw__add .i-check, .rw__add .i-spin { opacity: 0; }
.rw__add::before { content: ''; position: absolute; inset: 4px; border-radius: var(--rw-add-radius); border: 1px solid currentColor; opacity: 0; transform: scale(.55); transition: transform .7s var(--rw-ease), opacity .4s; }
.rw__add:hover::before, .rw__item.is-picking .rw__add::before { opacity: .22; transform: scale(1); }
.rw__item.is-picking .rw__add .i-plus { transform: rotate(45deg); }
.rw__item.is-loading .rw__add .i-plus { opacity: 0; }
.rw__item.is-loading .rw__add .i-spin { opacity: 1; animation: rw-spin .8s linear infinite; }
@keyframes rw-spin { to { transform: rotate(360deg); } }
.rw__item.is-added .rw__add .i-plus { opacity: 0; }
.rw__item.is-added .rw__add .i-check { opacity: 1; color: var(--rw-accent); animation: rw-pop .6s cubic-bezier(.2,1.6,.4,1); }
.rw__item.is-added .rw__add .i-check path { stroke-dasharray: 24; stroke-dashoffset: 24; animation: rw-draw .7s var(--rw-ease) .1s forwards; }
.rw__item.is-added .rw__add::after { content: ''; position: absolute; inset: 4px; border-radius: var(--rw-add-radius); border: 1px solid var(--rw-accent); animation: rw-burst 1s var(--rw-ease) forwards; }
.rw__item.is-added:not(.is-picking) .rw__add::before { opacity: 0; }
@keyframes rw-pop { 0% { transform: scale(.4); } 60% { transform: scale(1.25); } 100% { transform: scale(1); } }
@keyframes rw-draw { to { stroke-dashoffset: 0; } }
@keyframes rw-burst { from { transform: scale(.55); opacity: .9; } to { transform: scale(1.7); opacity: 0; } }
.rw__add:disabled { opacity: .35; cursor: not-allowed; }

/* Choix motif / taille */
.rw__picker { grid-column: 1 / -1; display: flex; flex-direction: column; gap: 14px; padding-top: 16px; }
.rw__picker.is-open { animation: rw-drop .7s var(--rw-ease); }
.rw__picker.is-open .rw__opt { animation: rw-rise .8s var(--rw-ease) backwards; }
.rw__picker.is-open .rw__confirm { animation: rw-rise .8s var(--rw-ease) .16s backwards; }
@keyframes rw-drop { from { opacity: 0; transform: translateY(-8px); } }
@keyframes rw-rise { from { opacity: 0; transform: translateY(14px); } }
.rw__picker-head { display: none; }
.rw__opt { border: 0; padding: 0; margin: 0; min-width: 0; }
.rw__opt-name { font-size: var(--rw-opt); margin-bottom: 8px; padding: 0; }
.rw__opt-name span { color: var(--rw-muted); }
.rw__vals { display: flex; flex-wrap: wrap; gap: 8px; }
.rw__pill { position: relative; }
.rw__pill input { position: absolute; inset: 0; opacity: 0; margin: 0; cursor: pointer; }
.rw__pill-label { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-width: 44px; min-height: 40px; padding: 0 14px; border-radius: var(--rw-pill-radius); border: 1px solid var(--rw-line); background: transparent; font-size: var(--rw-opt); transition: background .3s, color .3s, border-color .3s, transform .45s var(--rw-ease-spring); }
.rw__pill-label img { width: 22px; height: 22px; border-radius: 50%; object-fit: cover; margin-left: -8px; }
.rw__pill:hover .rw__pill-label { border-color: var(--rw-text); }
.rw__pill input:checked + .rw__pill-label { background: var(--rw-text); color: var(--rw-on-accent); border-color: var(--rw-text); }
.rw__pill input:active + .rw__pill-label { transform: scale(.94); }
.rw__pill input:focus-visible + .rw__pill-label { outline: 2px solid var(--rw-accent); outline-offset: 2px; }
.rw__pill.is-unavailable .rw__pill-label { color: var(--rw-muted); background: linear-gradient(to top right, transparent calc(50% - .5px), var(--rw-line) calc(50% - .5px), var(--rw-line) calc(50% + .5px), transparent calc(50% + .5px)); }
.rw__pill.is-unavailable input { cursor: not-allowed; }
.rw__opt.is-missing .rw__pill-label { border-color: #B42318; }
.rw__confirm, .rw__cta { position: relative; overflow: hidden; border: 0; border-radius: 0; background: var(--rw-text); color: var(--rw-on-accent); font-weight: 500; letter-spacing: .02em; display: flex; align-items: center; justify-content: center; gap: 10px; cursor: pointer; width: 100%; }
.rw__confirm { min-height: 46px; font-size: 11px; transition: transform .4s cubic-bezier(.2,1.5,.4,1); }
.rw__confirm::before, .rw__cta::before { content: ''; position: absolute; top: 0; bottom: 0; left: 0; width: 60%; background: linear-gradient(100deg, transparent, rgb(255 255 255/.26) 50%, transparent); transform: translateX(-140%) skewX(-18deg); }
.rw__confirm:hover, .rw__cta:hover { transform: translateY(-1px); }
.rw__confirm:hover::before, .rw__cta:hover::before { transform: translateX(260%) skewX(-18deg); transition: transform 1.1s var(--rw-ease); }
.rw__confirm:disabled { opacity: .45; cursor: not-allowed; }

/* Bas du panneau : le look complet */
.rw__bundle { padding: 20px var(--rw-panel-pad) 28px; display: flex; flex-direction: column; gap: 16px; background: var(--rw-panel); border-top: 1px solid var(--rw-line); }
.rw__msg { margin: 0; font-size: 11px; line-height: 1.45; color: #B42318; }
.rw__msg:empty { display: none; }
.rw__msg:not(:empty) { animation: rw-rise .6s var(--rw-ease); }
.rw__bundle-row { display: flex; justify-content: space-between; align-items: flex-end; gap: 12px; }
.rw__bundle-copy { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
.rw__bundle-title { font-size: 13px; font-weight: 500; }
.rw__bundle-note { font-size: 11px; color: var(--rw-muted); }
.rw__bundle-price { display: flex; align-items: baseline; gap: 10px; white-space: nowrap; }
.rw__bundle-price s { font-size: 11px; color: var(--rw-muted); }
.rw__bundle-price strong { font-weight: 400; font-size: var(--rw-total); }
.rw__cta { min-height: 56px; font-size: 13px; gap: 12px; transition: transform .6s var(--rw-ease), background .45s, color .45s, box-shadow .6s var(--rw-ease); }
.rw__cta:hover { transform: translateY(-2px); box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--rw-text) 80%, transparent); }
.rw__cta:hover .i-arrow { transform: translateX(4px); }
.rw__cta .i-arrow { transition: transform .5s cubic-bezier(.2,1.6,.4,1); }
.rw__cta:active { transform: scale(.985); }
.rw__cta .i-spin, .rw__cta-m, .rw__cta-done { display: none; }
.rw__cta.is-loading .i-arrow { display: none; } .rw__cta.is-loading .i-spin { display: block; animation: rw-spin .8s linear infinite; }
.rw__look-panel.is-complete .rw__cta { background: var(--rw-accent); animation: rw-glow 1.8s var(--rw-ease); }
.rw__look-panel.is-complete .rw__cta-add, .rw__look-panel.is-complete .rw__cta-m { display: none !important; }
.rw__look-panel.is-complete .rw__cta-done { display: inline; animation: rw-rise .7s var(--rw-ease); }
@keyframes rw-glow { from { box-shadow: 0 0 0 0 color-mix(in srgb, var(--rw-accent) 55%, transparent); } to { box-shadow: 0 0 0 16px transparent; } }

/* Entrée des pièces à chaque look */
.rw__look-panel.is-in .rw__item { animation: rw-item-in 1.1s var(--rw-ease) backwards; animation-delay: calc(var(--i) * 85ms + 140ms); }
.rw__look-panel.is-in .rw__media { animation: rw-curtain 1.2s var(--rw-ease) backwards; animation-delay: calc(var(--i) * 85ms + 200ms); }
.rw__look-panel.is-in .rw__media img { animation: rw-media-in 1.8s var(--rw-ease) backwards; animation-delay: calc(var(--i) * 85ms + 200ms); }
.rw__look-panel.is-in .rw__bundle-row { animation: rw-rise 1s var(--rw-ease) .38s backwards; }
.rw__look-panel.is-in .rw__cta { animation: rw-rise 1s var(--rw-ease) .46s backwards; }
@keyframes rw-item-in { from { opacity: 0; transform: translateY(26px); filter: blur(8px); } }
@keyframes rw-curtain { from { clip-path: inset(0 0 100% 0); } to { clip-path: inset(0); } }
@keyframes rw-media-in { from { transform: scale(1.22); filter: saturate(.4) brightness(1.08); } }

/* Points (dans « Voir ») */
.rw__pins { position: absolute; inset: 0; z-index: 2; }
.rw__pin { position: absolute; width: 44px; height: 44px; margin: -22px 0 0 -22px; display: grid; place-items: center; border: 0; background: transparent; padding: 0; cursor: pointer; }
.rw__pin-dot { position: relative; width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center; background: rgb(255 255 255/.86); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); color: var(--rw-text); font-size: 11px; font-weight: 600; line-height: 1; box-shadow: 0 4px 14px -4px rgb(0 0 0/.35); transition: transform .5s cubic-bezier(.2,1.6,.4,1), background .25s, color .25s; }
.rw__pin-dot::after, .rw__pin-dot::before { content: ''; position: absolute; inset: -5px; border-radius: 50%; border: 1px solid rgb(255 255 255/.8); animation: rw-ring 2.6s cubic-bezier(.2,.6,.3,1) infinite; pointer-events: none; }
.rw__pin-dot::before { border-color: rgb(255 255 255/.55); animation-delay: 1.3s; }
@keyframes rw-ring { from { transform: scale(.8); opacity: .9; } to { transform: scale(1.7); opacity: 0; } }
.rw__pin:hover .rw__pin-dot, .rw__pin.is-hot .rw__pin-dot { transform: scale(1.3); background: var(--rw-accent); color: var(--rw-on-accent); box-shadow: 0 0 0 6px color-mix(in srgb, var(--rw-accent) 22%, transparent), 0 8px 20px -6px rgb(0 0 0/.45); }
.rw__pin:focus-visible { outline: none; } .rw__pin:focus-visible .rw__pin-dot { box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--rw-accent); }
.rw__pin::after { content: attr(data-tip); position: absolute; left: calc(100% - 6px); top: 50%; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding: 7px 12px; border-radius: 999px; background: rgb(255 255 255/.88); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); color: var(--rw-text); font-size: 11px; font-weight: 500; letter-spacing: .04em; line-height: 1; box-shadow: 0 10px 26px -12px rgb(0 0 0/.4); opacity: 0; transform: translate(-8px, -50%) scale(.96); transform-origin: left; transition: opacity .35s, transform .6s var(--rw-ease); pointer-events: none; }
.rw__pin.is-left::after { left: auto; right: calc(100% - 6px); transform-origin: right; transform: translate(8px, -50%) scale(.96); }
.rw__pin.is-hot::after, .rw__pin:focus-visible::after { opacity: 1; transform: translate(0, -50%); }
@media (hover: hover) { .rw__pin:hover::after { opacity: 1; transform: translate(0, -50%); } }

/* Fenêtre « Voir le look » */
.rw__lb { position: absolute; inset: 0; z-index: 40; visibility: hidden; pointer-events: none; transition: visibility 0s linear .95s; }
.rw__lb::before { content: ''; position: absolute; inset: 0; background: var(--rw-stage); opacity: 0; transition: opacity .6s var(--rw-ease) .15s; }
.rw--zoom .rw__lb { visibility: visible; pointer-events: auto; transition: none; }
.rw--zoom .rw__lb::before { opacity: 1; transition: opacity .45s; }
.rw__actors, .rw__head, .rw__walk, .rw__controls { transition: opacity .7s var(--rw-ease) .2s; }
.rw--zoom .rw__actors, .rw--zoom .rw__head, .rw--zoom .rw__walk, .rw--zoom .rw__controls { opacity: 0; pointer-events: none; transition: opacity .35s; }
.rw__lb-stage { position: absolute; inset: 88px 48px 48px; display: flex; align-items: flex-end; justify-content: center; cursor: zoom-out; }
.rw__lb-model { position: relative; flex: 0 0 auto; cursor: default; transition: transform .95s var(--rw-ease); }
.rw__lb-model .rw__hit { display: none; }
.rw__lb-top { position: absolute; top: 0; left: 0; right: 0; display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 28px 32px 0 56px; opacity: 0; transform: translateY(-10px); transition: opacity .3s, transform .3s; z-index: 3; }
.rw--zoom .rw__lb-top { opacity: 1; transform: none; transition: opacity .8s .35s, transform 1s var(--rw-ease) .35s; }
.rw__lb-cap { margin: 0; display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.rw__lb-kicker { font-size: 10px; letter-spacing: .2em; text-transform: uppercase; color: var(--rw-muted); font-variant-numeric: tabular-nums; }
.rw__lb-name { font-size: var(--rw-chip); line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rw__lb-close { width: 48px; height: 48px; flex-shrink: 0; border-radius: 50%; display: grid; place-items: center; border: 1px solid color-mix(in srgb, var(--rw-text) 22%, transparent); background: color-mix(in srgb, #fff 55%, transparent); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); cursor: pointer; transition: transform .6s var(--rw-ease), border-color .6s; }
.rw__lb-close:hover { transform: rotate(90deg); border-color: var(--rw-text); }
.rw__lb-close:focus-visible { outline: 2px solid var(--rw-accent); outline-offset: 3px; }
.rw__lb-hint { position: absolute; left: 0; right: 0; bottom: 16px; margin: 0; text-align: center; font-size: 10px; letter-spacing: .2em; text-transform: uppercase; color: var(--rw-muted); pointer-events: none; opacity: 0; transition: opacity .3s; }
.rw--zoom .rw__lb-hint { opacity: 1; transition: opacity 1s var(--rw-ease) 1.1s; }
.rw--zoom .rw__lb .rw__pin-dot { animation: rw-pin-in .9s var(--rw-ease-spring) backwards; animation-delay: calc(var(--pi) * 110ms + .6s); }
@keyframes rw-pin-in { from { transform: scale(0); opacity: 0; } }

/* Apparition de la section */
.rw--js .rw__title, .rw--js .rw__sub, .rw--js .rw__panel-head { opacity: 0; transform: translateY(22px); filter: blur(8px); }
.rw--js .rw__title { letter-spacing: .03em; }
.rw--js .rw__stage, .rw--js .rw__controls, .rw--js .rw__panel { opacity: 0; }
.rw--js.is-revealed .rw__title, .rw--js.is-revealed .rw__sub, .rw--js.is-revealed .rw__panel-head { opacity: 1; transform: none; filter: none; transition: opacity 1.1s var(--rw-ease), transform 1.4s var(--rw-ease), filter 1.1s var(--rw-ease), letter-spacing 1.8s var(--rw-ease); }
.rw--js.is-revealed .rw__title { letter-spacing: -.02em; transition-delay: .12s; }
.rw--js.is-revealed .rw__sub { transition-delay: .26s; }
.rw--js.is-revealed .rw__panel-head { transition-delay: .34s; }
.rw--js.is-revealed .rw__stage { opacity: 1; transition: opacity 1.6s var(--rw-ease) .1s; }
.rw--js.is-revealed .rw__panel { opacity: 1; transition: opacity 1.2s var(--rw-ease) .25s; }
.rw--js.is-revealed .rw__controls { opacity: 1; transition: opacity 1.2s var(--rw-ease) .55s; }

/* Volet du bas (téléphone, et « Toute la boutique ») */
.rw-sheet-host { position: relative; z-index: 2147483000; font-family: Helvetica, Arial, sans-serif; color: #151413; }
.rw__pick-scrim { position: fixed; inset: 0; z-index: 2147483000; border: 0; padding: 0; background: rgb(12 10 8/.38); opacity: 0; visibility: hidden; pointer-events: none; transition: opacity .5s var(--rw-ease), visibility 0s linear .5s; }
.rw-sheet-host.is-open .rw__pick-scrim { opacity: 1; visibility: visible; pointer-events: auto; transition: opacity .5s var(--rw-ease); }
.rw-sheet-host .rw__picker { position: fixed; left: 0; right: 0; bottom: 0; z-index: 2147483001; max-height: min(82vh, 640px); overflow-y: auto; overscroll-behavior: contain; gap: 18px; padding: 14px 20px calc(20px + env(safe-area-inset-bottom, 0px)); background: #fff; border-radius: 20px 20px 0 0; box-shadow: 0 -24px 60px -24px rgb(0 0 0/.4); animation: rw-sheet-up .65s var(--rw-ease-sheet); --rw-opt: 12px; }
.rw-sheet-host .rw__picker::before { content: ''; width: 34px; height: 4px; border-radius: 2px; background: rgb(21 20 19/.16); align-self: center; flex-shrink: 0; }
@media (min-width: 750px) { .rw-sheet-host .rw__picker { left: 50%; right: auto; bottom: 24px; width: 440px; margin-left: -220px; border-radius: 20px; } }
@keyframes rw-sheet-up { from { transform: translateY(100%); } }
.rw-sheet-host .rw__picker-head { display: grid; grid-template-columns: 52px 1fr 44px; gap: 14px; align-items: center; padding-bottom: 16px; border-bottom: 1px solid #c8c8c8; }
.rw__picker-head img { width: 52px; height: 65px; object-fit: contain; background: #f5f5f4; mix-blend-mode: multiply; }
.rw__picker-head b { display: block; font-size: 12px; font-weight: 500; }
.rw__picker-head small { display: block; font-size: 12px; color: #66625c; margin-top: 4px; }
.rw__pick-close { width: 44px; height: 44px; border: 0; background: none; font-size: 22px; cursor: pointer; transition: transform .3s; }
.rw__pick-close:active { transform: rotate(90deg) scale(.9); }
.rw-sheet-host .rw__confirm { min-height: 54px; font-size: 12px; }

/* ── Toute la boutique ── */
.shop-all { max-width: 1320px; margin: 0 auto; padding: 72px 40px 96px; }
.shop-all h2 { margin: 0 0 8px; font-weight: 400; font-size: 32px; letter-spacing: -.02em; }
.shop-all > p { margin: 0 0 28px; color: #66625c; font-size: 12px; }
.shop-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 28px 20px; }
.shop-card { position: relative; display: flex; flex-direction: column; gap: 6px; }
.shop-card .rw__media { aspect-ratio: 4/5; }
.shop-card .rw__type { margin-top: 6px; }
.shop-card .rw__name { cursor: default; }
.shop-card .rw__add { position: absolute; right: 6px; top: 6px; background: rgb(255 255 255/.9); border-radius: 50%; }
.shop-card.is-added .rw__add .i-plus { opacity: 0; } .shop-card.is-added .rw__add .i-check { opacity: 1; }

/* ── Panier + commande ── */
.bag-dialog { border: 1px solid #d9d9d9; background: #fff; color: #151413; padding: 24px; width: min(560px, 100vw - 32px); max-height: 88dvh; overflow-y: auto; }
.bag-dialog::backdrop { background: #0005; backdrop-filter: blur(4px); }
.bag-heading { display: flex; justify-content: space-between; align-items: center; gap: 20px; }
.bag-heading h2 { margin: 0; font-size: 25px; font-weight: 400; }
.bag-close { width: 44px; height: 44px; border: 0; background: none; font-size: 28px; cursor: pointer; }
.bag-note { font-size: 12px; color: #666; line-height: 1.5; margin: 8px 0 4px; }
.bag-row { display: grid; grid-template-columns: 60px 1fr auto; gap: 18px; align-items: center; padding: 16px 0; border-bottom: 1px solid #ddd; }
.bag-row img { width: 60px; height: 75px; object-fit: contain; mix-blend-mode: multiply; background: #f5f5f4; }
.bag-row h3 { margin: 0 0 6px; font-size: 14px; font-weight: 400; }
.bag-row p { margin: 0; font-size: 12px; color: #66625c; }
.bag-qty { display: inline-flex; align-items: center; gap: 2px; margin-top: 8px; border: 1px solid #ddd; }
.bag-qty button { width: 32px; height: 30px; border: 0; background: none; cursor: pointer; font-size: 15px; }
.bag-qty span { min-width: 22px; text-align: center; font-size: 12px; }
.bag-rm { border: 0; background: none; font-size: 20px; padding: 12px; cursor: pointer; color: #66625c; }
.bag-total { text-align: right; font-size: 15px; padding: 14px 0; margin: 0; }
.bag-main { width: 100%; min-height: 54px; border: 0; background: #151413; color: #fff; font-size: 13px; font-weight: 500; letter-spacing: .02em; cursor: pointer; }
.bag-main:disabled { opacity: .4; cursor: not-allowed; }
.bag-form { display: grid; gap: 12px; margin-top: 14px; }
.bag-form label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; }
.bag-form input, .bag-form select { width: 100%; min-height: 46px; padding: 0 12px; border: 1px solid #c8c8c8; border-radius: 0; background: #fff; color: #151413; font-size: 15px; }
.bag-form input:focus, .bag-form select:focus { outline: none; border-color: #151413; box-shadow: inset 0 0 0 1px #151413; }
.bag-err { color: #B42318; font-size: 12.5px; font-weight: 600; margin: 0; }
.bag-err:empty { display: none; }
.bag-back { border: 0; background: none; padding: 8px 0; font-size: 12px; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; justify-self: start; }
.bag-done { text-align: center; padding: 18px 0 6px; }
.bag-done i { display: grid; place-items: center; width: 60px; height: 60px; margin: 0 auto 14px; border-radius: 50%; background: #151413; color: #fff; font-style: normal; font-size: 26px; }
.bag-done h3 { margin: 0 0 8px; font-weight: 400; font-size: 22px; }
.bag-done p { margin: 0 0 18px; color: #66625c; font-size: 13px; }
#bag-status { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #151413; color: #fff; padding: 12px 20px; font-size: 12px; z-index: 2147483002; opacity: 0; transition: opacity .25s; pointer-events: none; white-space: nowrap; }
#bag-status.is-on { opacity: 1; }
.rwp-empty { padding: 120px 20px; text-align: center; color: #66625c; }

/* ── Tablette ── */
@media (max-width: 1099px) and (min-width: 750px) {
    .rw__head { padding: 32px 32px 0; flex-direction: column; align-items: flex-start; }
    .rw__controls { padding: 6px 22px 22px; }
    .rw__tick[aria-current="true"] { flex-basis: 130px; }
    .rw { --rw-panel-w: 340px; --rw-panel-pad: 24px; --rw-thumb: 78px; }
}
@media (max-width: 960px) { .site-header { padding: 0 24px; gap: 18px; } .site-nav { gap: 16px; } }

/* ── Téléphone ── */
@media (max-width: 749px) {
    .site-header { height: 60px; padding: 0 16px; gap: 12px; }
    .brand i { width: 30px; height: 30px; }
    .brand b { font-size: 13px; }
    .site-nav { gap: 14px; font-size: 11px; }
    .site-nav .nav-more, .site-nav .nav-main { display: none; }
    .rw { --rw-h: 24px; --rw-label: 9px; --rw-chip: 18px; --rw-ph: 16px; --rw-pt-size: 12px; --rw-thumb: 62px; --rw-panel-pad: 20px; }
    .rw__grid { display: flex; flex-direction: column; position: relative; overflow: hidden; }
    .rw__left { flex: 1 1 auto; z-index: 1; padding-bottom: 20px; }
    .rw__head { flex-direction: column; align-items: stretch; gap: 18px; padding: 24px 20px 0; }
    .rw__intro { gap: 10px; }
    .rw__title { font-size: min(24px, 11.5vw); }
    .rw__controls { display: none; }
    .rw__walk { display: block; position: absolute; left: 50%; bottom: 8px; z-index: 1600; width: min(140px, 38%); transform: translateX(-50%); }
    .rw__walk-line { position: relative; display: block; height: 2px; background: color-mix(in srgb, var(--rw-text) 14%, transparent); overflow: hidden; }
    .rw__walk-line::after { content: ''; position: absolute; inset: 0; background: var(--rw-accent); transform: scaleX(var(--rw-progress, 0)); transform-origin: left; transition: transform 1.1s var(--rw-ease); }
    .rw__panel { position: relative; z-index: 2; flex: 0 0 auto; border-left: 0; margin-top: -20px; border-radius: var(--rw-bar-r) var(--rw-bar-r) 0 0; box-shadow: 0 -14px 30px -24px rgb(0 0 0/.35); }
    .rw__panel-inner { position: static; }
    .rw__panel-head { display: none; }
    .rw__mhead { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 14px 20px 6px; }
    .rw__mhead-title { font-size: var(--rw-ph); line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rw__look-panel.is-in .rw__mhead { animation: rw-fade .8s var(--rw-ease); }
    .rw__items { flex-direction: row; flex: 0 0 auto; gap: 0; padding: 8px 20px 6px; overflow-x: auto; overflow-y: hidden; scroll-snap-type: x mandatory; scroll-padding-inline: 20px; overscroll-behavior-x: contain; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
    .rw__items::-webkit-scrollbar { display: none; }
    .rw__item { flex: 0 0 calc(100% - 64px); scroll-snap-align: start; margin-right: 16px; padding: 6px 16px 6px 0; column-gap: 14px; border-top: 0; border-right: 1px solid var(--rw-line); }
    .rw__item:last-child { margin-right: 0; border-bottom: 0; border-right: 0; flex-basis: 100%; }
    .rw__item:only-child { flex-basis: 100%; }
    .rw__item::after { display: none; }
    .rw__add { align-self: center; }
    .rw__tag { left: 6px; top: -1px; }
    .rw__look-panel.is-in .rw__item { animation: rw-card-in .9s var(--rw-ease) backwards; animation-delay: calc(var(--i) * 80ms + 100ms); }
    .rw__look-panel.is-in .rw__media, .rw__look-panel.is-in .rw__media img { animation: none; }
    @keyframes rw-card-in { from { opacity: 0; transform: translateY(10px); } }
    .rw__bundle { padding: 10px 20px calc(16px + env(safe-area-inset-bottom, 0px)); gap: 10px; border-top: 0; }
    .rw__bundle-row { display: none; }
    .rw__cta { min-height: 54px; }
    .rw__cta .i-arrow, .rw__cta-add { display: none; }
    .rw__cta-m { display: inline; }
    .rw__look-panel.is-in .rw__cta { animation: rw-rise 1s var(--rw-ease) .3s backwards; }
    .rw__lb { bottom: 20px; }
    .rw__lb-stage { inset: 72px 16px 40px; }
    .rw__lb-top { padding: 16px 12px 0 20px; }
    .rw__lb-close { width: 44px; height: 44px; }
    .rw__lb-hint { bottom: 12px; }
    .rw__pin::after { max-width: 150px; }
    .shop-all { padding: 48px 16px 80px; }
    .shop-all h2 { font-size: 24px; }
    .shop-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px 12px; }
}
@media (max-width: 374px) {
    .rw__head { padding: 20px 16px 0; }
    .rw__mhead, .rw__bundle { padding-left: 16px; padding-right: 16px; }
    .rw__items { padding-left: 16px; padding-right: 16px; scroll-padding-inline: 16px; }
    .rw__item { flex-basis: calc(100% - 44px); column-gap: 12px; }
}
@media (max-height: 520px) and (orientation: landscape) {
    .rw__head { padding-top: 14px; } .rw__sub { display: none; } .rw__title { font-size: min(var(--rw-h), 36px); }
    .rw__panel-head { padding: 18px var(--rw-panel-pad) 10px; } .rw__controls { padding-bottom: 8px; }
}

/* Mouvements réduits */
@media (prefers-reduced-motion: reduce) {
    .rw *, .rw *::before, .rw *::after, .rw-sheet-host *, .rw-sheet-host *::before, .rw-sheet-host *::after { animation: none !important; transition: none !important; }
    .rw--js .rw__title, .rw--js .rw__sub, .rw--js .rw__panel-head, .rw--js .rw__stage, .rw--js .rw__controls, .rw--js .rw__panel { opacity: 1; transform: none; filter: none; }
    html { scroll-behavior: auto; }
    #bag-status { transition: none; }
}
</style>
</head>
<body class="rwp">
@include('public.shops.models._top')
<div class="rwp-promise">Paiement à la livraison · Commande en 1 minute</div>
<header class="site-header">
    <a href="{{ $s['url'] }}" class="brand" aria-label="{{ $s['name'] }}, accueil">
        <i>@if($s['logo'])<img src="{{ $s['logo'] }}" alt="">@else{{ $s['initials'] }}@endif</i>
        <b>{{ $s['name'] }}</b>
    </a>
    <nav class="site-nav" aria-label="Navigation principale">
        @if($nRun)<a href="#rw" class="nav-main" aria-current="page">{{ $lb['looks'] ? ucfirst($lb['word']['plural']) : 'La vitrine' }}</a>@endif
        @if($lb['more'])<a href="#boutique" class="nav-more">Toute la boutique</a>@endif
        @if($wa)<a href="{{ $wa }}" target="_blank" rel="noopener">Une question ?</a>@endif
    </nav>
    <button type="button" class="bag-btn" id="open-bag" aria-label="Ouvrir mon panier">
        <svg width="22" height="24" viewBox="0 0 24 26" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><path d="M4 8h16l1 16H3L4 8zM8 8V6a4 4 0 0 1 8 0v2"/></svg>
        <span data-cart-count>0</span>
    </button>
</header>

<main>
@if(!$nRun && !$lb['more'])
    <p class="rwp-empty">Cette boutique n’a pas encore de produit en ligne.</p>
@endif
@if($nRun)
<section class="rw rw--js" id="rw" data-word="{{ $lb['word']['one'] }}" aria-label="{{ $lb['word']['title'] }}">
    <div class="rw__sticky">
        <div class="rw__grid">
            <div class="rw__left">
                <div class="rw__head">
                    <div class="rw__intro">
                        <h1 class="rw__title">{{ $lb['looks'] ? $lb['word']['title'] : $s['name'] . '.' }}</h1>
                        <p class="rw__sub">{{ $sub }}</p>
                    </div>
                    <span class="rw__sr" aria-live="polite" aria-atomic="true" data-rw-live></span>
                </div>
                <div class="rw__stage" data-rw-stage tabindex="0" role="region" aria-roledescription="carrousel" aria-label="{{ $lb['word']['title'] }} Utilise les flèches du clavier ou glisse pour changer.">
                    <div class="rw__actors" data-rw-actors></div>
                    <span class="rw__sweep"></span>
                    <span class="rw__cursor" data-rw-cursor aria-hidden="true"><span class="rw__cursor-in"><span class="rw__cursor-label rw__cursor-drag">Glisser</span><span class="rw__cursor-label rw__cursor-view">Voir</span></span></span>
                    <span class="rw__walk" aria-hidden="true"><span class="rw__walk-line"></span></span>
                </div>
                <div class="rw__controls" @if($nRun < 2) hidden @endif>
                    <button type="button" class="rw__icon" data-rw-prev aria-label="Précédent"><svg width="26" height="14" viewBox="0 0 26 14" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M25 7H1M7 1L1 7l6 6"/></svg></button>
                    <div class="rw__ticks" data-rw-ticks></div>
                    <button type="button" class="rw__icon rw__auto" data-rw-auto aria-pressed="false" aria-label="Lecture automatique"><svg class="i-play" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4.5v15l12.5-7.5z"/></svg><svg class="i-pause" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.5 4.5h3.5v15H6.5zM14 4.5h3.5v15H14z"/></svg></button>
                    <button type="button" class="rw__icon" data-rw-next aria-label="Suivant"><svg width="26" height="14" viewBox="0 0 26 14" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 7h24M19 1l6 6-6 6"/></svg></button>
                </div>
                <div class="rw__lb" data-rw-lb role="region" aria-label="Voir de près" aria-hidden="true">
                    <div class="rw__lb-stage" data-rw-lb-stage></div>
                    <div class="rw__lb-top">
                        <p class="rw__lb-cap"><span class="rw__lb-kicker" data-rw-lb-kicker></span><span class="rw__lb-name" data-rw-lb-name></span></p>
                        <button type="button" class="rw__lb-close" data-rw-zoom-close aria-label="Fermer"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg></button>
                    </div>
                    <p class="rw__lb-hint">Touche un point pour voir la pièce</p>
                </div>
            </div>
            <aside class="rw__panel" aria-label="{{ $lb['word']['in'] }}">
                <div class="rw__panel-inner">
                    <div class="rw__panel-head"><h2 class="rw__panel-title" data-rw-ptitle>{{ $lb['word']['in'] }}</h2><span class="rw__label" data-rw-pieces></span></div>
                    <div data-rw-panels style="display:contents"></div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endif

@if($lb['more'])
<section class="shop-all" id="boutique" aria-labelledby="shopAllH">
    <h2 id="shopAllH">Toute la boutique</h2>
    <p>Paiement à la livraison. Touche « + » pour choisir et ajouter au panier.</p>
    <div class="shop-grid" data-shop-grid></div>
</section>
@endif
</main>

<dialog class="bag-dialog" id="bag-dialog" aria-labelledby="bagH">
    <div class="bag-heading"><h2 id="bagH">Mon panier</h2><button type="button" class="bag-close" id="close-bag" aria-label="Fermer le panier">×</button></div>
    <div id="bag-view">
        <p class="bag-note">Tu paies à la livraison. {{ $s['name'] }} t’appelle pour confirmer ta commande.</p>
        <div id="bag-items"></div>
        <p class="bag-total" id="bag-total"></p>
        <button type="button" class="bag-main" id="bag-order">Commander · paiement à la livraison</button>
    </div>
    <form class="bag-form" id="bag-form" novalidate hidden>
        <button type="button" class="bag-back" id="bag-back">‹ Retour au panier</button>
        <div><label for="bfName">Prénom et nom</label><input id="bfName" autocomplete="name" maxlength="80" value="{{ $pm['user']['name'] ?? '' }}"></div>
        <div><label for="bfPhone">Téléphone</label><input id="bfPhone" type="tel" inputmode="tel" autocomplete="tel" maxlength="16" placeholder="622 00 00 00" value="{{ $pm['user']['phone'] ?? '' }}">@if($pm['settings']['carts'] ?? false)<p class="bag-note" style="margin:4px 0 0">Ton numéro sert seulement à la boutique pour finaliser ta commande.</p>@endif</div>
        <div><label for="bfCommune">Commune</label><select id="bfCommune"><option value="">Choisis ta commune</option>@foreach($pm['communes'] as $c)<option>{{ $c }}</option>@endforeach</select></div>
        <div><label for="bfLand">Adresse ou repère</label><input id="bfLand" maxlength="120" placeholder="Ex. : près de la pharmacie"></div>
        <p class="bag-total" id="bf-total" style="padding:4px 0"></p>
        <p class="bag-err" id="bf-err" role="alert"></p>
        <button type="submit" class="bag-main" id="bf-send">Confirmer la commande</button>
    </form>
    <div id="bag-done" hidden></div>
</dialog>
<div id="bag-status" role="status" aria-live="polite"></div>

<script>
(function () {
    'use strict';
    const PM = @json($pm), LB = @json($lb);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const CUR = PM.shop.currency || 'GNF';
    const money = n => Math.round(n).toLocaleString('fr-FR').replace(/[  ]/g, ' ') + ' ' + CUR;
    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const pad = n => String(n).padStart(2, '0');
    const P = id => LB.products[id];
    const RM = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
    const $ = (sel, root) => (root || document).querySelector(sel);
    const $$ = (sel, root) => [...(root || document).querySelectorAll(sel)];
    const isPhone = () => window.innerWidth <= 749;
    const pieces = k => k + ' pièce' + (k > 1 ? 's' : '');

    /* ════════ Panier (gardé sur l'appareil du client) ════════ */
    const BAG_KEY = 'shopio_lb_bag_' + PM.shop.id;
    const lineOk = l => l && P(l.id) && Number.isSafeInteger(l.qty) && l.qty >= 1 && l.qty <= 20
        && (l.variant == null || P(l.id).opts.some(o => o.id === l.variant)) && (l.size == null || P(l.id).sizes.includes(l.size));
    let bag = [];
    try { bag = (JSON.parse(localStorage.getItem(BAG_KEY)) || []).filter(lineOk).slice(0, 30); } catch (e) { bag = []; }
    const saveBag = () => { try { localStorage.setItem(BAG_KEY, JSON.stringify(bag)); } catch (e) {} };
    const unitOf = l => { const p = P(l.id), o = p.opts.find(o => o.id === l.variant); return o ? o.price : p.price; };
    const bagTotal = () => bag.reduce((s, l) => s + unitOf(l) * l.qty, 0);
    function toast(msg) {
        const t = $('#bag-status'); t.textContent = msg; t.classList.add('is-on');
        clearTimeout(toast.t); toast.t = setTimeout(() => t.classList.remove('is-on'), 2500);
    }
    function addToBag(lines) {
        lines.forEach(n => {
            const same = bag.find(l => l.id === n.id && l.variant === n.variant && l.size === n.size);
            if (same) same.qty = Math.min(20, same.qty + 1); else bag.push({ id: n.id, variant: n.variant, size: n.size, qty: 1 });
        });
        saveBag(); renderBag();
        toast(lines.length > 1 ? 'Look ajouté à ton panier' : 'Ajouté à ton panier');
        if (navigator.vibrate && !RM) try { navigator.vibrate(12); } catch (e) {}
    }
    function renderBag() {
        const count = bag.reduce((s, l) => s + l.qty, 0);
        $$('[data-cart-count]').forEach(el => { el.textContent = count; });
        const box = $('#bag-items');
        box.innerHTML = bag.length ? bag.map((l, k) => {
            const p = P(l.id), o = p.opts.find(o => o.id === l.variant);
            const img = (o && o.thumb) || p.thumb;
            const det = [o ? o.name : '', l.size ? 'Taille ' + l.size : ''].filter(Boolean).join(' · ');
            return '<div class="bag-row">' + (img ? '<img src="' + esc(img) + '" alt="' + esc(p.name) + '">' : '<span></span>')
                + '<div><h3>' + esc(p.name) + '</h3><p>' + (det ? esc(det) + ' · ' : '') + money(unitOf(l)) + '</p>'
                + '<span class="bag-qty"><button type="button" data-bag-dec="' + k + '" aria-label="Un de moins">−</button><span>' + l.qty + '</span><button type="button" data-bag-inc="' + k + '" aria-label="Un de plus">+</button></span></div>'
                + '<button type="button" class="bag-rm" data-bag-rm="' + k + '" aria-label="Retirer ' + esc(p.name) + '">×</button></div>';
        }).join('') : '<p class="bag-note">Ton panier est vide. Choisis une pièce avec « + » pour l’ajouter.</p>';
        $('#bag-total').textContent = bag.length ? 'Total à payer à la livraison : ' + money(bagTotal()) : '';
        $('#bag-order').disabled = !bag.length;
    }
    const dlg = $('#bag-dialog');
    function openBag() {
        renderBag(); showBagStep('view');
        if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
    }
    function showBagStep(step) {
        $('#bag-view').hidden = step !== 'view'; $('#bag-form').hidden = step !== 'form'; $('#bag-done').hidden = step !== 'done';
        $('#bagH').textContent = step === 'form' ? 'Ta commande' : (step === 'done' ? 'Merci !' : 'Mon panier');
    }
    $('#open-bag').addEventListener('click', openBag);
    $('#close-bag').addEventListener('click', () => dlg.close());
    dlg.addEventListener('click', e => {
        if (e.target === dlg) { const r = dlg.getBoundingClientRect(); if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) dlg.close(); }
        const b = e.target.closest('[data-bag-rm],[data-bag-inc],[data-bag-dec]'); if (!b) return;
        const k = +(b.dataset.bagRm ?? b.dataset.bagInc ?? b.dataset.bagDec);
        if (b.dataset.bagRm !== undefined) bag.splice(k, 1);
        else if (b.dataset.bagInc !== undefined) bag[k].qty = Math.min(20, bag[k].qty + 1);
        else if (bag[k].qty > 1) bag[k].qty--; else bag.splice(k, 1);
        saveBag(); renderBag();
    });
    $('#bag-order').addEventListener('click', () => {
        if (PM.settings.vacation) { toast(PM.settings.vacationMsg || 'La boutique est en pause.'); return; }
        $('#bf-total').textContent = 'Total à payer à la livraison : ' + money(bagTotal());
        $('#bf-err').textContent = ''; showBagStep('form');
        setTimeout(() => ($('#bfName').value ? $('#bfPhone') : $('#bfName')).focus(), 40);
    });
    $('#bag-back').addEventListener('click', () => showBagStep('view'));

    /* Commande payée à la livraison (même parcours que les autres thèmes) */
    const VK = 'shopio_pm_visitor_' + PM.shop.id;
    let VISITOR = ''; try { VISITOR = JSON.parse(localStorage.getItem(VK)) || ''; } catch (e) {}
    if (!/^[a-z0-9]{12,40}$/.test(VISITOR)) { VISITOR = ''; const a = new Uint8Array(16); crypto.getRandomValues(a); a.forEach(b => { VISITOR += (b % 36).toString(36); }); try { localStorage.setItem(VK, JSON.stringify(VISITOR)); } catch (e) {} }
    const payload = () => ({ visitor: VISITOR, items: bag.map(l => ({ id: l.id, qty: l.qty, variant: l.variant, size: l.size })), name: $('#bfName').value.trim(), phone: $('#bfPhone').value, commune: $('#bfCommune').value, landmark: $('#bfLand').value.trim() });
    let capT = null;
    $('#bag-form').addEventListener('input', () => {
        if (!PM.settings.carts || !PM.urls.cart) return;
        clearTimeout(capT);
        capT = setTimeout(() => { const d = payload(); d.items = d.items.map(i => ({ id: i.id, qty: i.qty })); fetch(PM.urls.cart, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(d) }).catch(() => {}); }, 1500);
    });
    $('#bag-form').addEventListener('submit', async e => {
        e.preventDefault();
        const d = payload(), err = $('#bf-err'), btn = $('#bf-send');
        if (!d.items.length) { showBagStep('view'); return; }
        if (!d.name && !PM.user) { err.textContent = 'Indique ton prénom et ton nom.'; $('#bfName').focus(); return; }
        if (d.phone.replace(/\D/g, '').length < 8) { err.textContent = 'Indique ton numéro de téléphone.'; $('#bfPhone').focus(); return; }
        if (!d.commune) { err.textContent = 'Choisis ta commune.'; $('#bfCommune').focus(); return; }
        btn.disabled = true; btn.textContent = 'Envoi…'; err.textContent = '';
        try {
            const r = await fetch(PM.urls.order, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(d) });
            const j = await r.json().catch(() => ({}));
            if (!r.ok) { err.textContent = (j.errors ? Object.values(j.errors)[0][0] : null) || j.message || 'La commande n’a pas pu être envoyée. Réessaie.'; return; }
            bag = []; saveBag(); renderBag();
            $$('.rw__item.is-added, .shop-card.is-added').forEach(el => el.classList.remove('is-added'));
            $$('.rw__look-panel.is-complete').forEach(el => el.classList.remove('is-complete'));
            cards.forEach(c => { c.added = false; });
            $('#bag-done').innerHTML = '<div class="bag-done"><i>✓</i><h3>Commande envoyée</h3><p>' + esc(PM.shop.name) + ' t’appelle pour confirmer. Tu paies à la livraison.</p><button type="button" class="bag-main" data-bag-close>Continuer</button></div>';
            showBagStep('done');
        } catch (ex) {
            err.textContent = 'Pas de connexion. Vérifie ton réseau puis réessaie.';
        } finally { btn.disabled = false; btn.textContent = 'Confirmer la commande'; }
    });
    $('#bag-done').addEventListener('click', e => { if (e.target.closest('[data-bag-close]')) dlg.close(); });
    renderBag();

    /* ════════ Choix motif / taille (une « carte » par pièce affichée) ════════ */
    const cards = [];
    let uid = 0;
    const firstOk = p => (p.opts.find(o => o.stock === null || o.stock > 0) || {}).id ?? null;
    const soldOut = p => p.opts.length ? p.opts.every(o => o.stock !== null && o.stock <= 0) : (p.stock !== null && p.stock !== undefined && p.stock <= 0);
    const needsPick = p => p.opts.length > 1 || p.sizes.length > 0;
    function newCard(pid) {
        const p = P(pid);
        const c = { pid, id: 'rwk' + (++uid), variant: firstOk(p), size: p.sizes.length === 1 ? p.sizes[0] : null, touched: false, added: false, row: null, picker: null, add: null, home: null };
        cards.push(c); return c;
    }
    const priceOf = c => { const p = P(c.pid), o = p.opts.find(o => o.id === c.variant); return o ? o.price : p.price; };
    const ready = c => !P(c.pid).sizes.length || !!c.size;
    function pickerHTML(c) {
        const p = P(c.pid);
        let h = '<div class="rw__picker" id="' + c.id + '-pick" data-rw-picker="' + c.id + '" hidden>'
            + '<div class="rw__picker-head">' + (p.thumb ? '<img src="' + esc(p.thumb) + '" alt="">' : '<span></span>') + '<div><b>' + esc(p.name) + '</b><small data-pick-price>' + money(priceOf(c)) + '</small></div><button type="button" class="rw__pick-close" data-rw-pick-close aria-label="Fermer">×</button></div>';
        if (p.opts.length > 1) {
            h += '<fieldset class="rw__opt"><legend class="rw__opt-name">Motif <span data-opt-cur></span></legend><div class="rw__vals">'
                + p.opts.map(o => '<label class="rw__pill' + (o.stock !== null && o.stock <= 0 ? ' is-unavailable' : '') + '"><input type="radio" name="' + c.id + '-v" value="' + o.id + '" data-rw-variant' + (o.id === c.variant ? ' checked' : '') + (o.stock !== null && o.stock <= 0 ? ' disabled' : '') + '><span class="rw__pill-label">' + (o.thumb ? '<img src="' + esc(o.thumb) + '" alt="">' : '') + esc(o.name) + '</span></label>').join('')
                + '</div></fieldset>';
        }
        if (p.sizes.length) {
            h += '<fieldset class="rw__opt" data-size-set><legend class="rw__opt-name">Taille</legend><div class="rw__vals">'
                + p.sizes.map(z => '<label class="rw__pill"><input type="radio" name="' + c.id + '-s" value="' + esc(z) + '" data-rw-size' + (z === c.size ? ' checked' : '') + '><span class="rw__pill-label">' + esc(z) + '</span></label>').join('')
                + '</div></fieldset>';
        }
        h += '<button type="button" class="rw__confirm" data-rw-confirm="' + c.id + '"><span>Ajouter au panier</span><span class="rw__sr"> — ' + esc(p.name) + '</span></button></div>';
        return h;
    }
    function selText(c) {
        const p = P(c.pid), o = p.opts.find(o => o.id === c.variant);
        return [p.opts.length > 1 && o ? o.name : '', c.size ? 'Taille ' + c.size : ''].filter(Boolean).join(' · ');
    }
    function refreshCard(c) {
        const p = P(c.pid);
        if (c.row) {
            const pr = c.row.querySelector('[data-price]');
            if (pr) pr.innerHTML = (p.old && p.old > priceOf(c) ? '<s>' + money(p.old) + '</s>' : '') + '<span>' + money(priceOf(c)) + '</span>';
            const st = c.row.querySelector('[data-sel]'); if (st) st.textContent = c.touched ? selText(c) : '';
            const o = p.opts.find(o => o.id === c.variant), im = c.row.querySelector('.rw__media img');
            if (im && o && o.thumb && c.touched) im.src = o.thumb;
        }
        if (c.picker) {
            const pp = c.picker.querySelector('[data-pick-price]'); if (pp) pp.textContent = money(priceOf(c));
            const cur = c.picker.querySelector('[data-opt-cur]'); const o = p.opts.find(o => o.id === c.variant); if (cur) cur.textContent = o ? '· ' + o.name : '';
        }
        refreshBundle(c.row && c.row.closest('.rw__look-panel'));
    }
    const cardById = id => cards.find(c => c.id === id);

    /* Volet du bas : le choix sort de la carte et monte du bas de l'écran */
    let host = null, sheetCard = null;
    function openSheet(c) {
        closeSheet();
        if (!host) {
            host = document.createElement('div'); host.className = 'rw-sheet-host';
            host.innerHTML = '<button type="button" class="rw__pick-scrim" data-rw-pick-close tabindex="-1" aria-hidden="true"></button>';
            document.body.appendChild(host); // ses clics et choix remontent jusqu'au document (onClick, onChange)
        }
        c.home = { parent: c.picker.parentNode, next: c.picker.nextSibling };
        host.appendChild(c.picker); c.picker.hidden = false; c.picker.classList.add('is-open');
        sheetCard = c;
        requestAnimationFrame(() => { if (sheetCard === c) host.classList.add('is-open'); });
        if (c.row) { c.row.classList.add('is-picking'); c.add && c.add.setAttribute('aria-expanded', 'true'); }
        rw && rw.classList.add('rw--picking');
        const f = c.picker.querySelector('input:checked') || c.picker.querySelector('input:not(:disabled)') || c.picker.querySelector('.rw__confirm');
        f && f.focus({ preventScroll: true });
    }
    function closeSheet(returnFocus) {
        if (!sheetCard) return;
        const c = sheetCard; sheetCard = null;
        c.picker.hidden = true; c.picker.classList.remove('is-open');
        if (c.home) c.home.parent.insertBefore(c.picker, c.home.next);
        host && host.classList.remove('is-open');
        if (c.row) { c.row.classList.remove('is-picking'); c.add && c.add.setAttribute('aria-expanded', 'false'); }
        rw && rw.classList.remove('rw--picking');
        if (returnFocus && c.add) c.add.focus({ preventScroll: true });
    }
    const useSheet = c => isPhone() || !c.row || c.row.classList.contains('shop-card');
    function openPicker(c) {
        if (useSheet(c)) { openSheet(c); return; }
        c.picker.hidden = false; c.picker.classList.remove('is-open'); void c.picker.offsetWidth; c.picker.classList.add('is-open');
        c.row.classList.add('is-picking'); c.add.setAttribute('aria-expanded', 'true');
        const f = c.picker.querySelector('input:checked') || c.picker.querySelector('input:not(:disabled)');
        f && f.focus({ preventScroll: true });
        revealRow(c.row);
    }
    function closePicker(c) {
        if (sheetCard === c) { closeSheet(); return; }
        if (!c.picker || c.picker.hidden) return;
        c.picker.hidden = true; c.picker.classList.remove('is-open');
        if (c.row) { c.row.classList.remove('is-picking'); c.add && c.add.setAttribute('aria-expanded', 'false'); }
    }
    function markAdded(c) {
        c.added = true; closePicker(c);
        if (c.row) {
            c.row.classList.remove('is-loading', 'is-added'); void c.row.offsetWidth; c.row.classList.add('is-added');
            c.add && c.add.setAttribute('aria-label', 'Ajouté au panier : ' + P(c.pid).name);
        }
        refreshBundle(c.row && c.row.closest('.rw__look-panel'));
    }
    function addCard(c) {
        if (soldOut(P(c.pid))) return;
        if (!ready(c)) {
            const set = c.picker && c.picker.querySelector('[data-size-set]');
            if (set) { set.classList.add('is-missing'); setTimeout(() => set.classList.remove('is-missing'), 1600); }
            return;
        }
        c.touched = true; refreshCard(c);
        addToBag([{ id: c.pid, variant: c.variant, size: c.size }]);
        markAdded(c);
    }

    /* ════════ Défilé ════════ */
    const rw = $('#rw');
    const ENT = LB.runway, n = ENT.length;
    let stage, actors, panelsBox, bodies = [], panels = [];
    let N = 0, mode = 'ring', W = 0, H = 0, unit = 120, modelH = 400, fs = 1, maxW = 300;
    let index = 0, target = 0, cur = 0, vel = 0, lastT = 0, raf = 0, visible = true;
    let dragging = false, still = true, zoomed = false, engaged = false, playing = false, panelHover = false;
    let timer = 0, vhLatch = 0, wLatch = 0;
    const K = 95, C = 2 * Math.sqrt(K);
    const WORD = rw ? rw.dataset.word : 'Look';

    function buildPanel(e, k) {
        const items = e.items;
        const multi = items.length > 1;
        let h = '<div class="rw__look-panel' + (k === 0 ? ' is-in' : '') + '" data-rw-panel="' + k + '"' + (k ? ' hidden' : '') + '>'
            + '<div class="rw__mhead"><span class="rw__mhead-title">' + esc(e.title) + '</span><span class="rw__label">' + pieces(items.length) + '</span></div>'
            + '<ul class="rw__items" role="list">';
        items.forEach((it, i) => {
            const p = P(it.id), c = newCard(it.id); c.look = k; c.num = i + 1;
            const tag = p.old && p.old > p.price ? 'Promo' : (p.new ? 'Nouveau' : '');
            const so = soldOut(p);
            h += '<li class="rw__item" data-rw-card="' + (i + 1) + '" data-card="' + c.id + '" style="--i:' + (i + 1) + '">'
                + (tag ? '<span class="rw__tag"><span class="rw__tag-text">' + tag + '</span></span>' : '')
                + '<button type="button" class="rw__media" data-rw-open="' + c.id + '" tabindex="-1" aria-hidden="true">' + (p.thumb ? '<img src="' + esc(p.thumb) + '" alt="" loading="lazy" decoding="async">' : '') + '<span class="rw__num">' + (i + 1) + '</span></button>'
                + '<div class="rw__info"><span class="rw__type">' + esc(p.cat || ('Pièce ' + (i + 1))) + '</span>'
                + '<button type="button" class="rw__name" data-rw-open="' + c.id + '">' + esc(p.name) + '</button>'
                + '<span class="rw__price" data-price></span><span class="rw__sel" data-sel>' + (so ? 'Épuisé' : '') + '</span></div>'
                + '<button type="button" class="rw__add" data-rw-add="' + c.id + '"' + (so ? ' disabled' : '') + ' aria-expanded="false" aria-controls="' + c.id + '-pick" aria-label="Ajouter au panier : ' + esc(p.name) + '">'
                + '<svg class="i-plus" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" aria-hidden="true"><path d="M12 4v16M4 12h16"/></svg>'
                + '<svg class="i-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 12.5l5 5L19.5 7"/></svg>'
                + '<svg class="i-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M12 3a9 9 0 1 1-9 9"/></svg></button>'
                + pickerHTML(c) + '</li>';
        });
        h += '</ul><div class="rw__bundle"><p class="rw__msg" data-rw-msg role="status" aria-live="polite"></p>'
            + '<div class="rw__bundle-row"><span class="rw__bundle-copy"><span class="rw__bundle-title">' + (multi ? esc(LB.word.complete) : 'Paiement à la livraison') + '</span><span class="rw__bundle-note">' + (multi ? 'Les ' + items.length + ' pièces, livrées ensemble' : 'La boutique t’appelle pour confirmer') + '</span></span>'
            + '<span class="rw__bundle-price" data-bundle-price></span></div>'
            + '<button type="button" class="rw__cta" data-rw-all><span class="rw__cta-add">' + (multi ? 'Ajouter ' + (e.kind === 'look' ? 'le ' + WORD.toLowerCase() + ' complet' : 'tout') + ' au panier' : 'Ajouter au panier') + '</span>'
            + '<span class="rw__cta-m" data-cta-m></span><span class="rw__cta-done">' + (multi ? esc(LB.word.done) : 'Dans ton panier · Commander') + '</span>'
            + '<svg class="i-arrow" width="22" height="12" viewBox="0 0 26 14" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 7h24M19 1l6 6-6 6"/></svg>'
            + '<svg class="i-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M12 3a9 9 0 1 1-9 9"/></svg></button></div></div>';
        return h;
    }
    function panelCards(panel) { return panel ? $$('[data-card]', panel).map(li => cardById(li.dataset.card)) : []; }
    function refreshBundle(panel) {
        if (!panel) return;
        const cs = panelCards(panel), total = cs.reduce((s, c) => s + priceOf(c), 0);
        const old = cs.reduce((s, c) => { const p = P(c.pid); return s + Math.max(priceOf(c), p.old || 0); }, 0);
        const bp = $('[data-bundle-price]', panel);
        bp.innerHTML = (old > total ? '<s>' + money(old) + '</s>' : '') + '<strong>' + money(total) + '</strong>';
        $('[data-cta-m]', panel).textContent = (cs.length > 1 ? 'Ajouter ' + (ENT[+panel.dataset.rwPanel].kind === 'look' ? 'le ' + WORD.toLowerCase() : 'tout') : 'Ajouter') + ' — ' + money(total);
        const avail = cs.filter(c => !soldOut(P(c.pid)));
        panel.classList.toggle('is-complete', avail.length > 0 && avail.every(c => c.added));
    }
    function addAll(panel) {
        if (panel.classList.contains('is-complete')) { openBag(); return; }
        const msg = $('[data-rw-msg]', panel); msg.textContent = '';
        const todo = panelCards(panel).filter(c => !c.added && !soldOut(P(c.pid)));
        // Les tailles ne sont jamais devinées : une pièce avec tailles doit avoir été choisie par le client
        const okNow = todo.filter(ready), wait = todo.filter(c => !ready(c));
        if (okNow.length) {
            okNow.forEach(c => { c.touched = true; refreshCard(c); });
            addToBag(okNow.map(c => ({ id: c.pid, variant: c.variant, size: c.size })));
            okNow.forEach(markAdded);
        }
        if (wait.length) {
            msg.textContent = 'Choisis une taille pour : ' + wait.map(c => P(c.pid).name).join(', ');
            if (isPhone()) openSheet(wait[0]); else wait.forEach(openPicker);
        }
        engage();
    }

    if (rw && n) {
        stage = $('[data-rw-stage]', rw); actors = $('[data-rw-actors]', rw); panelsBox = $('[data-rw-panels]', rw);
        N = n >= 2 ? n * Math.ceil(8 / n) : 1;
        // Silhouettes : une par place sur l'anneau (les looks se répètent pour remplir le tour)
        for (let a = 0; a < N; a++) {
            const e = ENT[a % n];
            const el = document.createElement('div');
            el.className = 'rw__model'; el.dataset.index = a; el.setAttribute('aria-hidden', 'true');
            const lz = a < n ? 'eager' : 'lazy';
            const art = e.parts
                ? '<div class="rw__parts">' + e.parts.map((u, i) => '<img alt="' + esc(P(e.items[i].id).name) + '" draggable="false" decoding="async" loading="' + lz + '" style="height:' + partH(i) * 100 + '%">').join('') + '</div>'
                : '<img alt="' + esc(e.title) + '" draggable="false" decoding="async" loading="' + lz + '">';
            el.innerHTML = '<span class="rw__shadow"></span><div class="rw__figure"><div class="rw__art">' + art + '<button type="button" class="rw__hit" data-rw-go="' + a + '" aria-label="' + pad((a % n) + 1) + ' : ' + esc(e.title) + '" tabindex="-1"></button></div></div>';
            actors.appendChild(el);
            const imgs = $$('img', el);
            const s = { a, e: a % n, el, fig: $('.rw__figure', el), art: $('.rw__art', el), img: imgs[0], hit: $('.rw__hit', el), ar: e.parts ? .9 : .5625, w: 0, h: 0, x: 0, v: 0, g: 0, goal: 0, trip: null, blur: -1, op: -1 };
            const done = () => {
                if (!imgs.every(im => im.complete && im.naturalWidth)) return;
                imgs.forEach(im => im.classList.add('is-decoded'));
                s.ar = e.parts ? composite(e, imgs) : clamp(imgs[0].naturalWidth / imgs[0].naturalHeight, .3, 2.2);
                size(s); kick();
            };
            imgs.forEach((im, i) => {
                im.addEventListener('load', () => { (im.decode ? im.decode() : Promise.resolve()).then(done, done); }, { once: true });
                im.src = e.parts ? e.parts[i] : e.photo;
            });
            bodies.push(s);
        }
        // Repères 01 → N
        $('[data-rw-ticks]', rw).innerHTML = ENT.map((e, k) => '<button type="button" class="rw__tick" data-rw-tick="' + k + '" aria-current="' + (k === 0) + '" aria-label="' + pad(k + 1) + ' : ' + esc(e.title) + '"><span class="rw__tick-bar"></span><span class="rw__tick-label">' + pad(k + 1) + '<span class="rw__tick-name">&nbsp;&nbsp;' + esc(e.title) + '</span></span></button>').join('');
        // Panneaux
        panelsBox.innerHTML = ENT.map(buildPanel).join('');
        panels = $$('[data-rw-panel]', panelsBox);
        cards.forEach(c => { c.row = $('[data-card="' + c.id + '"]', panelsBox); c.picker = $('[data-rw-picker="' + c.id + '"]', c.row); c.add = $('[data-rw-add="' + c.id + '"]', c.row); refreshCard(c); });
        panels.forEach(refreshBundle);
        feet();
    }

    /* Look sans photo : pièces côte à côte, la première un peu plus grande ; un point au centre de chacune */
    function partH(i) { return i ? .84 : 1; }
    function composite(e, imgs) {
        const gap = .05, ars = imgs.map(im => clamp(im.naturalWidth / im.naturalHeight, .2, 2.5));
        const ws = ars.map((r, i) => r * partH(i)), total = ws.reduce((a, b) => a + b, 0) + gap * (ws.length - 1);
        let x = 0;
        ws.forEach((w, i) => { e.items[i].x = Math.round((x + w / 2) / total * 100); e.items[i].y = Math.round((1 - partH(i) * .45) * 100); x += w + gap; });
        imgs[0].parentNode.style.gap = (gap / total * 100).toFixed(2) + '%';
        return clamp(total, .3, 2.6);
    }

    /* Pieds au sol : on cherche la dernière ligne non transparente de chaque photo */
    function feet() {
        ENT.forEach((e, k) => {
            if (e.parts) return;
            const im = new Image();
            im.onload = () => {
                try {
                    const w = 48, h = Math.max(1, Math.round(48 * im.naturalHeight / im.naturalWidth));
                    const cv = document.createElement('canvas'); cv.width = w; cv.height = h;
                    const cx = cv.getContext('2d'); cx.drawImage(im, 0, 0, w, h);
                    const d = cx.getImageData(0, 0, w, h).data;
                    let last = h - 1;
                    outer: for (let y = h - 1; y >= 0; y--) { for (let x = 0; x < w; x++) if (d[(y * w + x) * 4 + 3] > 40) { last = y; break outer; } }
                    const f = clamp((h - 1 - last) / h, 0, .4);
                    bodies.filter(s => s.e === k).forEach(s => s.art.style.setProperty('--rw-foot', f.toFixed(3)));
                } catch (er) {}
            };
            im.src = e.photo;
        });
    }

    function size(s) {
        // Look aux pièces côte à côte : plus large qu'une silhouette, on lui laisse plus de place
        s.h = Math.round(Math.min(modelH, maxW * (ENT[s.e].parts ? (mode === 'classic' ? 1.36 : 1.4) : 1) / s.ar)); s.w = Math.round(s.h * s.ar);
        s.fig.style.height = s.h + 'px'; s.fig.style.width = s.w + 'px';
    }
    function measure() {
        if (!rw) return;
        const mob = isPhone();
        const prevMode = mode;
        mode = n < 2 ? 'single' : (mob ? 'classic' : 'ring');
        if (mob) {
            if (!vhLatch || window.innerWidth !== wLatch || Math.abs(window.innerHeight - vhLatch) >= 160) { vhLatch = window.innerHeight; wLatch = window.innerWidth; }
            rw.style.setProperty('--rw-frame-h', Math.round(vhLatch * 1.09) + 'px');
        } else {
            const top = rw.getBoundingClientRect().top + window.scrollY;
            rw.style.setProperty('--rw-frame-h', Math.max(380, window.innerHeight - top) + 'px');
        }
        const r = stage.getBoundingClientRect(); W = r.width; H = r.height;
        fs = mode === 'ring' ? 1.08 : 1;
        modelH = mode === 'ring' ? Math.max(200, Math.round((H - 34 - 18) / 1.08)) : Math.max(200, Math.round(H - (mob ? 24 : 36) - 18));
        maxW = W * (mode === 'ring' ? .34 : (mode === 'classic' ? .58 : .7));
        unit = mode === 'classic' ? Math.max(90, W * .32 * 1.6) : Math.max(mob ? 90 : 120, W * .3);
        bodies.forEach(size);
        if (prevMode !== mode) { bodies.forEach(s => { s.x = s.g = s.goal = wrapN(s.a - target); s.v = 0; s.trip = null; }); if (!isPhone()) closeSheet(); }
        if (prevMode !== mode && mode === 'classic') { bodies.forEach(s => { s.x = s.g = s.goal + (RM ? 0 : 1.15); }); }
        kick();
    }
    const wrapN = v => ((v % N) + N) % N;

    function put(s, x, bottom, sc, op, blur, z) {
        const half = s.w * sc / 2;
        if (x + half < -4 || x - half > W + 4) op = 0;
        s.el.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + (-bottom).toFixed(1) + 'px,0) translateX(-50%) scale(' + sc.toFixed(4) + ')';
        const o = +op.toFixed(3);
        if (o !== s.op) { s.el.style.opacity = o; s.el.style.visibility = o < .01 ? 'hidden' : ''; s.el.style.pointerEvents = o < .5 ? 'none' : ''; s.op = o; }
        s.el.style.zIndex = z;
        const b = Math.round(blur * 10) / 10;
        if (b !== s.blur) { s.el.style.filter = b > .05 ? 'blur(' + b + 'px)' : ''; s.blur = b; }
    }
    function renderRing() {
        const cx = .5 * W, mob = isPhone(), R = (mob ? .72 : .62) * W, Pp = (mob ? 1.9 : 1.65) * W;
        const floor = H - 34, horizon = .36 * H, step = 2 * Math.PI / N;
        bodies.forEach(s => {
            let phi = ((s.a - cur) * step) % (2 * Math.PI);
            if (phi > Math.PI) phi -= 2 * Math.PI; if (phi <= -Math.PI) phi += 2 * Math.PI;
            const z = R * (1 - Math.cos(phi)), persp = Pp / (Pp + z), sl = Math.abs(phi) / step;
            const sc = persp * (1 + (fs - 1) * clamp(1 - sl, 0, 1));
            const x = cx + R * Math.sin(phi) * persp;
            const feetY = horizon + (floor - horizon) * persp;
            const blur = sl <= 1 ? 1.8 * sl : Math.min(1.8 + (sl - 1) * 2, 6.5);
            const op = clamp((Math.cos(phi) + .55) / .3, 0, 1);
            put(s, x, H - feetY, sc, op, blur, Math.round(sc * 1000));
        });
    }
    function renderSingle() { bodies.forEach(s => put(s, .5 * W, 34, 1, 1, 0, 1000)); }
    function stepClassic(dt) {
        let moving = false;
        const ax = .3, ay = 22, sx = .32, sy = 22, sr = .76, ex = .55, d = 4;
        bodies.forEach(s => {
            const goal = wrapN(s.a - target);
            if (Math.abs(goal - s.goal) > N / 2 && !RM) s.trip = { from: s.x };
            s.goal = goal;
            if (RM) { s.x = s.g = goal; s.v = 0; s.trip = null; }
            else {
                const tau = dragging ? .016 : (s.trip ? .1 : .03 + .04 * Math.min(goal, 4));
                s.g += (goal - s.g) * (1 - Math.exp(-dt / tau));
                const h = dt / 3;
                for (let i = 0; i < 3; i++) { const acc = -K * (s.x - s.g) - C * s.v; s.v += acc * h; s.x += s.v * h; }
                if (s.trip && Math.abs(s.x - goal) < .02) s.trip = null;
                if (Math.abs(s.x - goal) < .0006 && Math.abs(s.v) < .0006 && Math.abs(s.g - goal) < .0006) { s.x = s.g = goal; s.v = 0; } else moving = true;
            }
            let k = s.x, e = -1;
            if (s.trip) {
                const span = Math.abs(goal - s.trip.from) || 1, u = clamp(Math.abs(s.x - s.trip.from) / span, 0, 1);
                if (goal > s.trip.from) { if (u < .5) e = 2 * u * 1.4; else k = goal + 2 * (1 - u); }
                else { if (u < .5) k = s.trip.from + 2 * u; else e = 2 * (1 - u) * 1.4; }
            } else if (k < 0) e = Math.min(-k, 1.4);
            if (e >= 0) put(s, W * (ax - ex * e), ay - 22 * e, 1 + .14 * e, Math.max(0, 1 - 1.05 * e), 4 * Math.min(e, 1), 1100);
            else {
                const op = k <= d - 1 ? 1 - .13 * k : Math.max(0, .61 * (d - k));
                const sc = Math.pow(sr, k);
                // Une silhouette large (pièces côte à côte) ne doit pas sortir par la gauche
                put(s, Math.max(W * (ax + sx * k), s.w * sc / 2 + 14), ay + sy * k, sc, op, Math.min(.9 * k, 3.2), 1000 - Math.round(10 * k));
            }
        });
        return moving;
    }
    function frame(t) {
        raf = 0;
        const dt = lastT ? Math.min(.034, (t - lastT) / 1000) : 1 / 60; lastT = t;
        let moving = false;
        if (mode === 'ring') {
            if (RM) { cur = target; vel = 0; }
            else {
                const h = dt / 3;
                for (let i = 0; i < 3; i++) { const acc = -K * (cur - target) - C * vel; vel += acc * h; cur += vel * h; }
                if (Math.abs(cur - target) <= .0005 && Math.abs(vel) <= .0005) { cur = target; vel = 0; } else moving = true;
            }
            renderRing();
        } else if (mode === 'classic') moving = stepClassic(dt);
        else renderSingle();
        setStill(!moving && !dragging);
        if ((moving || dragging) && visible && !document.hidden) raf = requestAnimationFrame(frame); else lastT = 0;
    }
    function kick() { if (!raf && rw && n) { lastT = 0; raf = requestAnimationFrame(frame); } }
    function setStill(v) {
        if (still === v) return; still = v;
        rw.classList.toggle('rw--still', v);
        frontHit();
    }
    // La silhouette de devant s'ouvre en grand (curseur « Voir ») ; les autres viennent devant
    const frontA = () => wrapN(Math.round(target));
    function rankOf(a) { let d = wrapN(a - Math.round(target)); if (mode === 'ring' && d > N / 2) d -= N; return d; }
    function frontHit() {
        const fa = frontA();
        bodies.forEach(s => {
            const k = rankOf(s.a), near = k !== 0 && Math.abs(k) <= (mode === 'ring' ? 2 : 3);
            s.hit.classList.toggle('is-front', k === 0);
            s.hit.tabIndex = (k === 0 || near) ? 0 : -1;
            s.hit.setAttribute('aria-label', (k === 0 ? 'Voir de près : ' : pad(s.e + 1) + ' : ') + ENT[s.e].title);
            if (k === 0 || near) s.el.removeAttribute('aria-hidden'); else s.el.setAttribute('aria-hidden', 'true');
        });
    }

    let first = true;
    function setIndex(i) {
        const changed = i !== index || first;
        index = i; kick();
        if (!changed) return;
        closeZoom(true); closeSheet();
        rw.style.setProperty('--rw-progress', ((i + 1) / n).toFixed(3));
        $$('[data-rw-tick]', rw).forEach((t, j) => { t.setAttribute('aria-current', j === i); t.classList.toggle('is-past', j < i); });
        panels.forEach((p, j) => { p.hidden = j !== i; p.classList.remove('is-in'); });
        const p = panels[i];
        if (!first) { void p.offsetWidth; p.classList.add('is-in'); }
        const e = ENT[i];
        $('[data-rw-ptitle]', rw).textContent = e.kind === 'look' ? LB.word.in : 'Le produit';
        const lab = $('[data-rw-pieces]', rw); lab.textContent = pieces(e.items.length);
        if (!first) {
            lab.classList.remove('is-swap'); void lab.offsetWidth; lab.classList.add('is-swap');
            $('[data-rw-live]', rw).textContent = pad(i + 1) + ' : ' + e.title;
            if (!RM) { stage.classList.remove('is-sweep'); void stage.offsetWidth; stage.classList.add('is-sweep'); }
        }
        hot(0);
        frontHit();
        restartBar();
        first = false;
    }
    function moveTo(T) { target = T; setStill(false); setIndex(((T % n) + n) % n); kick(); }
    const next = () => moveTo(Math.round(target) + 1);
    const prev = () => moveTo(Math.round(target) - 1);
    function go(i) { let d = ((i - index) % n + n) % n; if (d > n / 2) d -= n; moveTo(Math.round(target) + d); }

    /* Lecture automatique : 5 s par look ; s'arrête dès que le client choisit une pièce */
    function play(on) {
        playing = on && n > 1;
        rw.classList.toggle('is-playing', playing);
        const b = $('[data-rw-auto]', rw); b.setAttribute('aria-pressed', playing); b.setAttribute('aria-label', playing ? 'Mettre en pause' : 'Lecture automatique');
        restartBar();
    }
    function restartBar() {
        clearTimeout(timer);
        $$('.rw__tick.is-run', rw).forEach(t => t.classList.remove('is-run'));
        if (!playing) return;
        const t = $('[data-rw-tick="' + index + '"]', rw); void t.offsetWidth; t.classList.add('is-run');
        timer = setTimeout(() => {
            if (playing && visible && !dragging && !document.hidden && !panelHover && !zoomed && !engaged && !sheetCard) next(); else restartBar();
        }, 5000);
    }
    function engage() { if (!engaged && playing) { engaged = true; play(false); } engaged = true; }

    /* Lien point ↔ pièce */
    function hot(num, scroll) {
        $$('.is-hot', rw).forEach(el => el.classList.remove('is-hot'));
        if (!num) return;
        const scope = zoomed ? $('[data-rw-lb-stage]', rw) : null;
        scope && $$('[data-rw-pin="' + num + '"]', scope).forEach(el => el.classList.add('is-hot'));
        const row = $('[data-rw-card="' + num + '"]', panels[index]);
        if (row) { row.classList.add('is-hot'); if (scroll) revealRow(row); }
    }
    function revealRow(row) {
        const list = row.closest('.rw__items') || row.parentNode, bh = RM ? 'auto' : 'smooth';
        if (list.scrollWidth > list.clientWidth + 2) list.scrollTo({ left: row.offsetLeft - list.offsetLeft - 20, behavior: bh });
        else if (list.scrollHeight > list.clientHeight + 2) list.scrollTo({ top: row.offsetTop - list.offsetTop - 12, behavior: bh });
        else { const r = row.getBoundingClientRect(); if (r.top < 0 || r.bottom > window.innerHeight - 140) window.scrollBy({ top: r.top - .3 * window.innerHeight, behavior: bh }); }
    }

    /* Voir de près : la silhouette grandit avec ses points numérotés */
    let zoomT = 0;
    function openZoom() {
        const s = bodies.find(b => b.a === frontA()); if (!s) return;
        engage(); hot(0);
        clearTimeout(zoomT);
        const box = $('[data-rw-lb-stage]', rw), e = ENT[s.e];
        box.innerHTML = '';
        const wrap = document.createElement('div'); wrap.className = 'rw__lb-model';
        const fig = s.fig.cloneNode(true); fig.style.width = ''; fig.style.height = '';
        $('.rw__art', fig).style.setProperty('--rw-foot', s.art.style.getPropertyValue('--rw-foot') || 0);
        const pins = document.createElement('div'); pins.className = 'rw__pins';
        pins.innerHTML = e.items.map((it, i) => '<button type="button" class="rw__pin' + (it.x > 62 ? ' is-left' : '') + '" data-rw-pin="' + (i + 1) + '" data-tip="' + esc(P(it.id).name) + '" style="left:' + it.x + '%;top:' + it.y + '%;--pi:' + (i + 1) + '" aria-label="' + (i + 1) + ' : ' + esc(P(it.id).name) + '"><span class="rw__pin-dot">' + (i + 1) + '</span></button>').join('');
        $('.rw__art', fig).appendChild(pins);
        wrap.appendChild(fig); box.appendChild(wrap);
        const r = box.getBoundingClientRect();
        const fit = ar => { const hgt = Math.max(160, Math.min(r.height, r.width / ar)); fig.style.height = Math.round(hgt) + 'px'; fig.style.width = Math.round(hgt * ar) + 'px'; };
        fit(s.ar);
        // Photo pas encore chargée sur la scène : on l'affiche dès qu'elle arrive, à ses vraies proportions
        $$('img', fig).forEach(im => { im.classList.add('is-decoded'); im.loading = 'eager'; });
        const cim = $('img', fig);
        if (!e.parts && (!cim.complete || !cim.naturalWidth)) cim.addEventListener('load', () => { if (cim.naturalWidth) fit(clamp(cim.naturalWidth / cim.naturalHeight, .3, 2.2)); }, { once: true });
        $('[data-rw-lb-kicker]', rw).textContent = (e.kind === 'look' ? WORD : 'Pièce') + ' ' + pad(s.e + 1);
        $('[data-rw-lb-name]', rw).textContent = e.title;
        zoomed = true; rw.classList.add('rw--zoom'); $('[data-rw-lb]', rw).setAttribute('aria-hidden', 'false');
        if (!RM) {
            const a = s.fig.getBoundingClientRect(), b = fig.getBoundingClientRect();
            wrap.style.transition = 'none'; wrap.style.transformOrigin = '0 100%';
            wrap.style.transform = 'translate3d(' + (a.left - b.left) + 'px,' + (a.bottom - b.bottom) + 'px,0) scale(' + (a.height / b.height) + ')';
            wrap.dataset.at = wrap.style.transform;
            void wrap.offsetWidth; wrap.style.transition = ''; wrap.style.transform = '';
        }
        $('[data-rw-zoom-close]', rw).focus({ preventScroll: true });
    }
    function closeZoom(silent) {
        if (!zoomed) return;
        zoomed = false;
        const lb = $('[data-rw-lb]', rw), wrap = $('.rw__lb-model', lb);
        if (wrap && wrap.dataset.at && !RM && !silent) wrap.style.transform = wrap.dataset.at;
        rw.classList.remove('rw--zoom'); lb.setAttribute('aria-hidden', 'true');
        const hadFocus = lb.contains(document.activeElement);
        zoomT = setTimeout(() => { $('[data-rw-lb-stage]', rw).innerHTML = ''; }, silent ? 0 : 950);
        if (hadFocus) stage.focus({ preventScroll: true });
        hot(0);
    }

    /* ════════ Clics (section, volet, boutique) ════════ */
    let suppress = 0;
    function onClick(ev) {
        const t = ev.target;
        if (t.closest('[data-rw-pick-close]')) { closeSheet(true); return; }
        const conf = t.closest('[data-rw-confirm]');
        if (conf) { const c = cardById(conf.dataset.rwConfirm); c.touched = true; addCard(c); engage(); return; }
        const addB = t.closest('[data-rw-add], [data-rw-open]');
        if (addB) {
            const c = cardById(addB.dataset.rwAdd || addB.dataset.rwOpen); if (!c || soldOut(P(c.pid))) return;
            engage();
            const opened = sheetCard === c || (c.picker && !c.picker.hidden);
            if (opened) { closePicker(c); return; }
            if (!needsPick(P(c.pid)) && addB.dataset.rwAdd) { addCard(c); return; }
            openPicker(c); return;
        }
        if (!rw || !rw.contains(t)) return;
        if (t.closest('[data-rw-zoom-close]')) { closeZoom(); return; }
        if (zoomed && !t.closest('.rw__lb-model') && t.closest('[data-rw-lb]')) { closeZoom(); return; }
        if (t.closest('[data-rw-next]')) { next(); return; }
        if (t.closest('[data-rw-prev]')) { prev(); return; }
        const tick = t.closest('[data-rw-tick]'); if (tick) { go(+tick.dataset.rwTick); return; }
        if (t.closest('[data-rw-auto]')) { if (!playing) engaged = false; play(!playing); return; }
        const pin = t.closest('[data-rw-pin]'); if (pin) { engage(); hot(+pin.dataset.rwPin, true); return; }
        const hit = t.closest('[data-rw-go]');
        if (hit) {
            if (Date.now() < suppress) return;
            const k = rankOf(+hit.dataset.rwGo);
            if (k !== 0 && mode !== 'single') moveTo(Math.round(target) + k); else openZoom();
            return;
        }
        const all = t.closest('[data-rw-all]'); if (all) { addAll(all.closest('.rw__look-panel')); return; }
    }
    function onChange(ev) {
        const inp = ev.target, pk = inp.closest('[data-rw-picker]'); if (!pk) return;
        const c = cardById(pk.dataset.rwPicker); if (!c) return;
        if (inp.matches('[data-rw-variant]')) c.variant = +inp.value;
        if (inp.matches('[data-rw-size]')) c.size = inp.value;
        c.touched = true; engage(); refreshCard(c);
    }
    document.addEventListener('click', onClick);
    document.addEventListener('change', onChange);
    document.addEventListener('keydown', ev => {
        if (ev.key !== 'Escape') return;
        if (sheetCard) { closeSheet(true); return; }
        if (zoomed) closeZoom();
    });

    /* « Toute la boutique » */
    const grid = $('[data-shop-grid]');
    if (grid) {
        grid.innerHTML = LB.more.concat(Object.keys(LB.products).map(Number).filter(id => !LB.more.includes(id))).map(id => {
            const p = P(id), c = newCard(id), so = soldOut(p);
            return '<div class="shop-card" data-card="' + c.id + '"><button type="button" class="rw__media" data-rw-open="' + c.id + '" aria-label="Choisir ' + esc(p.name) + '">' + (p.thumb ? '<img src="' + esc(p.thumb) + '" alt="" loading="lazy" decoding="async">' : '') + '</button>'
                + '<button type="button" class="rw__add" data-rw-add="' + c.id + '"' + (so ? ' disabled' : '') + ' aria-label="Ajouter au panier : ' + esc(p.name) + '"><svg class="i-plus" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" aria-hidden="true"><path d="M12 4v16M4 12h16"/></svg><svg class="i-check" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 12.5l5 5L19.5 7"/></svg></button>'
                + '<span class="rw__type">' + esc(p.cat || '') + '</span><span class="rw__name">' + esc(p.name) + '</span><span class="rw__price" data-price></span><span class="rw__sel" data-sel>' + (so ? 'Épuisé' : '') + '</span>'
                + '<div hidden data-home>' + pickerHTML(c) + '</div></div>';
        }).join('');
        cards.filter(c => !c.row).forEach(c => { c.row = $('[data-card="' + c.id + '"]', grid); if (!c.row) return; c.picker = $('[data-rw-picker="' + c.id + '"]', c.row); c.add = $('[data-rw-add]', c.row); refreshCard(c); });
    }
    if (!rw || !n) return;

    /* ════════ Glisser (souris, doigt, pavé tactile, clavier) ════════ */
    let pd = null;
    stage.addEventListener('pointerdown', ev => {
        if (mode === 'single' || zoomed) return;
        if (ev.pointerType === 'mouse' && ev.button !== 0) return;
        pd = { x: ev.clientX, y: ev.clientY, t: target, id: ev.pointerId, samples: [{ x: ev.clientX, time: performance.now() }], drag: false };
    });
    stage.addEventListener('pointermove', ev => {
        if (!pd || ev.pointerId !== pd.id) return;
        if (ev.pointerType === 'mouse' && !(ev.buttons & 1)) { endDrag(ev); return; }
        const dx = ev.clientX - pd.x, dy = ev.clientY - pd.y;
        if (!pd.drag) {
            if (Math.abs(dy) > 10 && Math.abs(dy) > Math.abs(dx)) { pd = null; return; }
            if (Math.abs(dx) > 8 && Math.abs(dx) > 1.2 * Math.abs(dy)) { pd.drag = true; dragging = true; rw.classList.add('is-dragging'); try { stage.setPointerCapture(ev.pointerId); } catch (e) {} engage(); }
            else return;
        }
        pd.samples.push({ x: ev.clientX, time: performance.now() }); if (pd.samples.length > 6) pd.samples.shift();
        target = pd.t - dx / unit; setStill(false); kick();
    });
    function endDrag(ev) {
        if (!pd) return;
        const p = pd; pd = null;
        if (!p.drag) return;
        dragging = false; rw.classList.remove('is-dragging');
        const dx = (ev.clientX ?? p.samples[p.samples.length - 1].x) - p.x;
        const a = p.samples[0], b = p.samples[p.samples.length - 1];
        const vx = b.time > a.time ? (b.x - a.x) / (b.time - a.time) : 0;
        const from = Math.round(p.t);
        if (isPhone()) {
            moveTo(Math.abs(dx) > 30 || Math.abs(vx) > .3 ? from - (dx > 0 ? 1 : -1) : from);
        } else {
            let i = Math.round(target - vx * 160 / unit);
            if (i === from && Math.abs(vx) > .3) i = from - Math.sign(vx);
            moveTo(clamp(i, from - 2, from + 2));
        }
        suppress = Date.now() + 60;
    }
    stage.addEventListener('pointerup', endDrag);
    stage.addEventListener('pointercancel', endDrag);
    stage.addEventListener('click', ev => { if (Date.now() < suppress) { ev.stopPropagation(); ev.preventDefault(); } }, true);
    let wacc = 0, wlock = 0;
    stage.addEventListener('wheel', ev => {
        if (mode === 'single' || Math.abs(ev.deltaX) <= Math.abs(ev.deltaY)) return;
        ev.preventDefault();
        if (Date.now() < wlock) return;
        wacc += ev.deltaX;
        if (Math.abs(wacc) > 45) { engage(); wacc > 0 ? next() : prev(); wacc = 0; wlock = Date.now() + 520; }
    }, { passive: false });
    stage.addEventListener('keydown', ev => {
        if (ev.key === 'ArrowRight') { ev.preventDefault(); engage(); next(); }
        if (ev.key === 'ArrowLeft') { ev.preventDefault(); engage(); prev(); }
    });
    const panel = $('.rw__panel', rw);
    panel.addEventListener('pointerenter', ev => { if (ev.pointerType === 'mouse') { panelHover = true; } });
    panel.addEventListener('pointerleave', ev => { if (ev.pointerType === 'mouse') { panelHover = false; restartBar(); } });
    panel.addEventListener('pointerover', ev => { if (ev.pointerType !== 'mouse') return; const row = ev.target.closest('[data-rw-card]'); if (row && !row.classList.contains('is-hot')) hot(+row.dataset.rwCard); });
    panel.addEventListener('pointerout', ev => { const row = ev.target.closest('[data-rw-card]'); if (row && !row.contains(ev.relatedTarget)) hot(0); });

    /* Curseur « Glisser / Voir » (souris seulement) */
    const cursor = $('[data-rw-cursor]', rw);
    if (window.matchMedia('(hover: hover) and (pointer: fine)').matches && n > 1) {
        rw.classList.add('rw--cursor');
        let cx = 0, cy = 0, tx = 0, ty = 0, on = false, craf = 0;
        const loop = () => {
            craf = 0; const f = RM ? 1 : .18;
            cx += (tx - cx) * f; cy += (ty - cy) * f;
            cursor.style.transform = 'translate3d(' + cx + 'px,' + cy + 'px,0)';
            if (Math.abs(tx - cx) + Math.abs(ty - cy) > .2) craf = requestAnimationFrame(loop);
        };
        stage.addEventListener('pointermove', ev => {
            if (ev.pointerType !== 'mouse') return;
            const r = stage.getBoundingClientRect(); tx = ev.clientX - r.left; ty = ev.clientY - r.top;
            if (!on) { cx = tx; cy = ty; on = true; cursor.classList.add('is-on'); }
            const hit = ev.target.closest('.rw__hit');
            cursor.classList.toggle('is-view', !dragging && !!hit && hit.classList.contains('is-front'));
            cursor.classList.toggle('is-link', !dragging && !!hit && !hit.classList.contains('is-front'));
            if (!craf) craf = requestAnimationFrame(loop);
        });
        stage.addEventListener('pointerleave', () => { on = false; cursor.classList.remove('is-on', 'is-link', 'is-view'); });
    } else cursor.remove();

    /* Démarrage */
    let rt = 0;
    window.addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(measure, 120); });
    if ('ResizeObserver' in window) new ResizeObserver(() => { clearTimeout(rt); rt = setTimeout(measure, 120); }).observe(stage);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) { kick(); restartBar(); } });
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(es => { visible = es[0].isIntersecting; if (visible) kick(); }, { threshold: 0 }).observe(rw);
    }
    const reveal = () => { rw.classList.add('is-revealed'); };
    if (RM || !('IntersectionObserver' in window)) reveal();
    else { const io = new IntersectionObserver(es => { if (es[0].isIntersecting) { io.disconnect(); requestAnimationFrame(() => requestAnimationFrame(reveal)); setTimeout(reveal, 150); } }, { threshold: 0, rootMargin: '0px 0px -22% 0px' }); io.observe(rw); }

    // Lien direct vers un look : #look-3 ou #produit-12
    const m = location.hash.match(/^#((?:look|produit)-\d+)$/);
    const startAt = m ? Math.max(0, ENT.findIndex(e => e.key === m[1])) : 0;
    measure();
    cur = target = startAt; index = -1;
    cur -= RM ? 0 : 1.15;
    bodies.forEach(s => { s.goal = wrapN(s.a - target); s.x = s.g = s.goal + (RM ? 0 : 1.15); });
    setIndex(startAt);
    if (!RM && n > 1) play(true);
    if (m && startAt >= 0) setTimeout(() => rw.scrollIntoView({ behavior: 'auto' }), 0);
    kick();
})();
</script>
</body>
</html>
