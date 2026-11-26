{{--
    Modèle de page « Cabine » : la cabine d'essayage.
      Cabine  : à gauche la boutique (recherche, rayons, filtres, grille, favoris) ; au centre la tenue (ou le
                coffret) composée des photos sans fond des produits, posée sur un plateau qui tourne (glisser),
                avec la barre d'outils (annuler, rayons, « Inspire-moi ») et les pièces portées ; à droite
                « Ton look » (pièces, total, bon à savoir, Commander) et « Recommandé pour vous ».
                Changer une pièce la révèle par un balayage lumineux.
      Sections : la cabine est la section « Cabine d'essayage » de Boutique → Sections ; en dessous,
                Engagements, Comment commander, Avis, Questions et toutes les sections ajoutées par le vendeur
                (textes modifiables et améliorables avec Shopio IA).
      Commande : payée à la livraison (nom, téléphone, commune, repère), motif et taille de chaque pièce ;
                paniers abandonnés comme les autres thèmes.
    Données : PageModels::data() + App\Support\Cabine::data() (rayons, mise en scène, produits).
--}}
@php
    use App\Support\HomeSections as HS;
    $home = $pm['home']['cabine'] ?? HS::defaults('cabine');
    $studio = collect($home)->firstWhere('type', 'studio') ?? ['d' => HS::blank('studio')];
    $cb = \App\Support\Cabine::data($shop, $pm, $studio);
    $s = $pm['shop'];
    $T = fn ($sec, $k) => HS::txt('cabine', $sec, $k);
    $waDigits = preg_replace('/\D/', '', (string) $shop->phone);
    if (strlen($waDigits) === 9) $waDigits = '224' . $waDigits;
    $wa = strlen($waDigits) >= 8 ? 'https://wa.me/' . $waDigits . '?text=' . rawurlencode('Bonjour ' . $shop->name . ', j’ai une question sur un produit.') : null;
    $ambiance = HS::imgUrl($studio['d']['img'] ?? null);
@endphp
<!doctype html>
<html lang="fr" class="no-js">
<head>
@include('public.shops.models._head')
<link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,500;0,6..96,600;1,6..96,500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --page: #efede9; --panel: #f7f6f3; --tile: #ebe9e5; --ink: #151515; --ink-2: #5e5c58; --mute: #9b9892; --line: #e4e1dc; --float: #fbfaf8; --btn: #2b2b2b;
    --shadow: 0 1px 1px rgba(0,0,0,.02), 0 10px 30px -18px rgba(40,30,20,.18);
    --serif: 'Bodoni Moda', Georgia, serif; --sans: 'Inter', system-ui, sans-serif;
    --spring: cubic-bezier(.34,1.56,.64,1); --out: cubic-bezier(.22,1,.36,1);
    /* Variables communes des sections ajoutées (_section) */
    --m-bg: var(--page); --m-surface: var(--panel); --m-ink: var(--ink); --m-ink2: var(--ink-2); --m-line: var(--line); --m-soft: var(--tile);
    --m-accent: var(--btn); --m-accent-ink: #fff; --m-accent-text: var(--ink); --m-radius: 14px; --m-btn-radius: 9px;
    --m-title: var(--serif); --m-body: var(--sans); --m-shadow: var(--shadow);
}
* { box-sizing: border-box; }
html, body { margin: 0; }
body { background: var(--page); color: var(--ink); font: 400 clamp(11px, .86vw, 13px)/1.45 var(--sans); -webkit-font-smoothing: antialiased; overflow-x: hidden; }
button, input, select { font: inherit; color: inherit; }
button { cursor: pointer; }
a { color: inherit; }
:focus-visible { outline: 2px solid var(--ink); outline-offset: 2px; }
[hidden] { display: none !important; }
.cb-sr { position: absolute; width: 1px; height: 1px; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; }
.cb-panel { background: var(--panel); border-radius: 14px; box-shadow: var(--shadow); }

/* ── Cabine : 3 colonnes ── */
#app { height: 100vh; display: grid; grid-template-columns: minmax(0,357fr) minmax(0,500fr) minmax(0,292fr); padding: 0 7.3vw; }
.cb-left { display: flex; flex-direction: column; padding: 2.6vh 0 3.4vh; min-height: 0; }
.cb-brand { height: 6vh; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-shrink: 0; }
.cb-logo { font: 500 clamp(20px, 2.15vw, 32px)/1 var(--serif); letter-spacing: .035em; text-transform: uppercase; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cb-ask { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; text-decoration: none; color: var(--ink-2); white-space: nowrap; }
.cb-ask:hover { color: var(--ink); }
.cb-shop { margin-top: 1.6vh; flex: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }
.cb-search { margin: 2vh 1.1vw 0; height: clamp(34px, 4.6vh, 44px); border-radius: 22px; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.04), 0 6px 18px -12px rgba(40,30,20,.25); display: flex; align-items: center; gap: 8px; padding: 0 16px; flex-shrink: 0; }
.cb-search input { flex: 1; min-width: 0; border: 0; outline: none; background: none; font-size: 12px; }
.cb-search input::placeholder { color: var(--mute); }
.cb-tabs { position: relative; display: flex; gap: clamp(12px, 1.75vw, 26px); margin: 1.6vh 1.1vw 0; border-bottom: 1px solid var(--line); overflow-x: auto; scrollbar-width: none; flex-shrink: 0; }
.cb-tabs::-webkit-scrollbar { display: none; }
.cb-tab { flex-shrink: 0; border: 0; background: none; padding: 0 0 9px; color: var(--mute); font-size: 12px; white-space: nowrap; }
.cb-tab[aria-selected="true"] { color: var(--ink); font-weight: 500; }
.cb-tabline { position: absolute; bottom: -1px; height: 2px; background: var(--ink); transition: left .4s cubic-bezier(.6,0,.2,1), width .4s cubic-bezier(.6,0,.2,1); }
.cb-scroll { flex: 1; min-height: 0; overflow-y: auto; padding: 2vh 1.1vw; scrollbar-width: none; }
.cb-scroll::-webkit-scrollbar { display: none; }
.cb-row { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1.2vh; }
.cb-row h2 { margin: 0; font-size: 12.5px; font-weight: 600; }
.cb-row button, .cb-row span { border: 0; background: none; padding: 0; font-size: 11px; color: var(--ink-2); }
.cb-row button { text-decoration: underline; text-underline-offset: 3px; }
.cb-tiles { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 2.4vh; }
.cb-tileb { height: clamp(40px, 7.6vh, 58px); border-radius: 8px; border: 1.5px solid transparent; background: var(--tile); display: grid; place-items: center; text-align: center; line-height: 1.05; padding: 0 4px; transition: transform .25s var(--out), background-color .25s, border-color .25s; }
.cb-tileb:hover { transform: translateY(-2px); }
.cb-tileb[aria-pressed="true"] { background: #fff; border-color: var(--ink); }
.cb-tileb.w1 { font: 600 10px var(--sans); letter-spacing: .2em; text-transform: uppercase; }
.cb-tileb.w2 { font: italic 500 14px var(--serif); }
.cb-tileb.w3 { font: 500 12px var(--serif); letter-spacing: .08em; text-transform: uppercase; }
.cb-tileb.w4 { font: 600 9.5px var(--sans); letter-spacing: .14em; text-transform: uppercase; }
.cb-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2vh 1.1vw; }
.cb-card { position: relative; border: 0; background: none; padding: 0; text-align: left; display: flex; flex-direction: column; gap: 3px; }
.cb-card.is-in { animation: cbUp .38s var(--out) backwards; animation-delay: calc(var(--k) * 35ms); }
@keyframes cbUp { from { opacity: 0; transform: translateY(10px); } }
.cb-thumb { position: relative; aspect-ratio: 523/400; border-radius: 8px; background: var(--tile); box-shadow: inset 0 0 0 1.5px transparent; transition: box-shadow .2s; overflow: hidden; }
.cb-thumb img { position: absolute; inset: 6% 8%; width: 84%; height: 88%; object-fit: contain; mix-blend-mode: multiply; }
.cb-card:hover .cb-thumb { box-shadow: inset 0 0 0 1.5px #d3cfc8; }
.cb-card.is-worn .cb-thumb { box-shadow: inset 0 0 0 1.5px var(--ink); }
.cb-heart { position: absolute; top: 6px; right: 6px; width: 26px; height: 26px; display: grid; place-items: center; border: 0; border-radius: 50%; background: rgba(255,255,255,.85); z-index: 2; }
.cb-heart svg { transition: transform .45s var(--spring); }
.cb-heart[aria-pressed="true"] svg { fill: var(--ink); transform: scale(1.12); }
.cb-worn { position: absolute; left: 6px; bottom: 6px; padding: 3px 7px; border-radius: 4px; background: var(--ink); color: #fff; font-size: 9px; font-weight: 600; letter-spacing: .12em; transform: translateY(8px) scale(.8); opacity: 0; transition: transform .45s var(--spring), opacity .2s; }
.cb-card.is-worn .cb-worn { transform: none; opacity: 1; }
.cb-try { position: absolute; right: 6px; bottom: 6px; padding: 4px 9px; border-radius: 999px; background: #fff; font-size: 10.5px; font-weight: 500; opacity: 0; transition: opacity .2s; }
.cb-card:hover .cb-try, .cb-card:focus-visible .cb-try { opacity: 1; }
.cb-card.is-worn .cb-try { display: none; }
.cb-store { font-size: 9.5px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; margin-top: 4px; }
.cb-name { color: var(--ink-2); font-size: 11.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cb-price { font-weight: 500; font-size: 11.5px; }
.cb-price s { color: var(--mute); font-weight: 400; margin-right: 4px; }
.cb-empty { grid-column: 1 / -1; text-align: center; color: var(--ink-2); padding: 30px 0; }
.cb-bar { display: flex; justify-content: space-around; border-top: 1px solid var(--line); padding: 8px 6px; flex-shrink: 0; }
.cb-bar button { border: 0; background: none; display: flex; flex-direction: column; align-items: center; gap: 3px; font-size: 10px; color: var(--mute); padding: 2px 6px; }
.cb-bar button[aria-pressed="true"] { color: var(--ink); }

/* ── Scène ── */
.cb-stage { position: relative; min-height: 0; }
#figure { position: absolute; left: 45%; transform: translateX(-50%); z-index: 2; }
#figure.is-tenue { top: 3.6vh; height: 85.5vh; aspect-ratio: 1360/2048; }
#figure.is-coffret { bottom: 11vh; width: min(96%, 66vh); aspect-ratio: 1.2; }
#body { position: absolute; inset: 0; perspective: 1600px; cursor: grab; touch-action: pan-y; user-select: none; -webkit-user-select: none; }
#body.is-drag { cursor: grabbing; }
#spin { position: absolute; inset: 0; transform: rotateY(var(--ry, 0deg)); transform-origin: 50% 100%; transform-style: preserve-3d; }
.cb-zone { position: absolute; }
.cb-zone img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; pointer-events: none; -webkit-user-drag: none; mix-blend-mode: multiply; filter: drop-shadow(0 14px 18px rgba(40,30,20,.16)); }
.is-tenue .z1 { left: 16%; top: 1.5%; width: 68%; height: 43%; z-index: 3; } .is-tenue .z1 img { object-position: 50% 100%; }
.is-tenue .z2 { left: 21%; top: 41%; width: 58%; height: 34%; z-index: 2; } .is-tenue .z2 img { object-position: 50% 0%; }
.is-tenue .z3 { left: 24%; top: 74%; width: 52%; height: 24.5%; z-index: 1; } .is-tenue .z3 img { object-position: 50% 100%; }
.is-tenue .z4 { left: 60%; top: 37%; width: 40%; height: 30%; z-index: 4; } .is-tenue .z4 img { object-position: 50% 50%; }
.is-coffret #spin { display: flex; align-items: flex-end; justify-content: center; gap: 4%; }
.is-coffret .cb-zone { position: relative; flex: 0 1 auto; aspect-ratio: var(--ar, .6); max-width: 46%; }
.is-coffret .cb-zone img { object-position: 50% 100%; }
.is-coffret .z1 { height: 100%; } .is-coffret .z2 { height: 86%; } .is-coffret .z3 { height: 76%; } .is-coffret .z4 { height: 68%; }
.cb-glint { position: absolute; left: -6%; right: -6%; height: 2px; background: #fff; box-shadow: 0 0 10px 3px rgba(255,255,255,.9), 0 0 26px 6px rgba(255,255,255,.55); z-index: 9; pointer-events: none; opacity: 0; }
/* Plateau tournant */
#tt { position: absolute; left: 45%; transform: translateX(-50%); z-index: 1; pointer-events: none; }
#tt .top { position: absolute; inset: 0 0 16% 0; border-radius: 50%; background: radial-gradient(ellipse at 50% 38%, #fdfcfb, #f3f1ee 60%, #e7e4df); z-index: 2; }
#tt .side { position: absolute; inset: 16% 0 0 0; border-radius: 50%; background: linear-gradient(#e2dfda, #d3cfc8); z-index: 1; }
#tt svg { position: absolute; inset: 0 0 16% 0; width: 100%; height: 84%; z-index: 3; overflow: visible; }
#tt ellipse { fill: none; stroke: rgba(0,0,0,.13); stroke-width: 1; stroke-dasharray: 3 5; animation: cbDash 30s linear infinite; }
#tt.is-fast ellipse { animation-duration: 6s; }
@keyframes cbDash { to { stroke-dashoffset: -300; } }
#tt .lab { position: absolute; left: 0; right: 0; bottom: 30%; z-index: 4; text-align: center; font-size: 10px; letter-spacing: .16em; color: var(--mute); }
#tt .lab i { display: inline-block; font-style: normal; transition: transform .4s var(--out); }
.cb-stage:hover #tt .lab i:first-child { transform: translateX(-4px); } .cb-stage:hover #tt .lab i:last-child { transform: translateX(4px); }
#shadow { position: absolute; left: 45%; width: 46%; height: 4vh; transform: translate(-50%, -50%); border-radius: 50%; background: radial-gradient(rgba(0,0,0,.26), transparent 70%); filter: blur(2px); z-index: 1; pointer-events: none; }
/* Rail d'outils */
.cb-rail { position: absolute; left: 8.4%; top: 8.6vh; display: flex; flex-direction: column; gap: clamp(6px, 1.2vh, 10px); z-index: 6; }
.cb-rb { position: relative; width: clamp(30px, 5.2vh, 40px); height: clamp(30px, 5.2vh, 40px); border-radius: 50%; border: 0; background: var(--float); display: grid; place-items: center; box-shadow: 0 2px 8px -3px rgba(40,30,20,.25); transition: transform .25s var(--spring), background-color .2s, color .2s; }
.cb-rb:hover { transform: scale(1.08); }
.cb-rb[aria-pressed="true"] { background: var(--ink); color: #fff; }
.cb-rb:disabled { opacity: .4; cursor: default; transform: none; }
.cb-rb .tip { position: absolute; left: calc(100% + 10px); top: 50%; transform: translate(-4px, -50%); white-space: nowrap; padding: 5px 10px; border-radius: 999px; background: var(--ink); color: #fff; font-size: 11px; opacity: 0; pointer-events: none; transition: opacity .2s, transform .3s var(--out); }
.cb-rb:hover .tip, .cb-rb:focus-visible .tip { opacity: 1; transform: translate(0, -50%); }
.cb-rb b { font-size: 11px; font-weight: 600; }
/* Pièces portées */
.cb-strip { position: absolute; left: 75.4%; top: 10.8vh; padding: 6px; border-radius: 10px; background: var(--float); box-shadow: var(--shadow); display: flex; flex-direction: column; gap: 6px; z-index: 6; }
.cb-st { width: clamp(36px, 4vw, 56px); aspect-ratio: 523/537; border-radius: 7px; background: var(--tile); border: 0; padding: 0; position: relative; overflow: hidden; box-shadow: inset 0 0 0 1.5px transparent; transition: box-shadow .2s; }
.cb-st img { position: absolute; inset: 8%; width: 84%; height: 84%; object-fit: contain; mix-blend-mode: multiply; }
.cb-st:hover, .cb-st.is-hl { box-shadow: inset 0 0 0 1.5px var(--ink); }
.cb-st.is-pop img { animation: cbPop .5s var(--spring); }
@keyframes cbPop { from { transform: scale(.6); } }
/* Studio / Pièce */
.cb-mode { position: absolute; left: 72%; top: 4vh; display: flex; padding: 3px; border-radius: 999px; background: var(--float); box-shadow: var(--shadow); z-index: 6; }
.cb-mode button { position: relative; z-index: 1; border: 0; background: none; padding: 4px 12px; font-size: 11px; font-weight: 500; transition: color .3s; }
.cb-mode button[aria-pressed="true"] { color: #fff; }
.cb-mode i { position: absolute; top: 3px; bottom: 3px; left: 3px; width: calc(50% - 3px); border-radius: 999px; background: var(--ink); transition: transform .4s cubic-bezier(.6,0,.2,1); }
.cb-mode.is-room i { transform: translateX(100%); }
#room { position: absolute; left: 4%; right: 4%; top: 2.6vh; bottom: 3.4vh; border-radius: 16px; overflow: hidden; z-index: 0; opacity: 0; transform: scale(.97); transition: opacity .6s, transform .8s var(--out); pointer-events: none; }
#room.is-on { opacity: 1; transform: none; }
#room img { width: 100%; height: 100%; object-fit: cover; }
#room::before, #room::after { content: ''; position: absolute; width: 22px; height: 22px; border: 2px solid #fff; z-index: 2; }
#room::before { left: 12px; top: 12px; border-right: 0; border-bottom: 0; } #room::after { right: 12px; bottom: 12px; border-left: 0; border-top: 0; }
#room .scan { position: absolute; left: 0; right: 0; height: 2px; background: #fff; box-shadow: 0 0 14px 4px rgba(255,255,255,.7); top: 0; opacity: 0; }
#room.is-on .scan { animation: cbScan 1.4s ease-in-out .2s; }
@keyframes cbScan { 0% { top: 0; opacity: 1; } 100% { top: 100%; opacity: 0; } }
#room .lbl { position: absolute; left: 50%; bottom: 14px; transform: translateX(-50%); padding: 5px 12px; border-radius: 999px; background: rgba(0,0,0,.45); color: #fff; font-size: 11px; display: flex; align-items: center; gap: 7px; backdrop-filter: blur(6px); white-space: nowrap; }
#room .lbl i { width: 7px; height: 7px; border-radius: 50%; background: #ef4444; animation: cbPulse 1.4s infinite; }
@keyframes cbPulse { 50% { opacity: .3; } }
.cb-hint { position: absolute; left: 45%; bottom: 1.2vh; transform: translateX(-50%); font-size: 10.5px; color: var(--mute); white-space: nowrap; z-index: 3; text-align: center; }

/* ── Ton look ── */
.cb-right { display: flex; flex-direction: column; gap: 1.4vh; padding: 8.6vh 0 3.4vh; min-height: 0; }
.cb-look { flex: 1; min-height: 0; display: flex; flex-direction: column; padding: 16px 16px 14px; }
.cb-look-h { display: flex; justify-content: space-between; align-items: baseline; padding-bottom: 8px; border-bottom: 1px solid var(--line); }
.cb-look-h b { font-size: 11px; font-weight: 600; letter-spacing: .2em; }
.cb-look-h span { font-weight: 600; font-variant-numeric: tabular-nums; }
.cb-rows { flex: 1; min-height: 0; overflow-y: auto; scrollbar-width: none; display: flex; flex-direction: column; }
.cb-lr { display: grid; grid-template-columns: auto 1fr auto; gap: 10px; align-items: center; padding: 6px 0; border: 0; border-bottom: 1px solid var(--line); background: none; text-align: left; flex: 1 1 0; min-height: 0; max-height: 12vh; }
.cb-lr .th { height: min(9vh, 100%); aspect-ratio: 523/450; border-radius: 8px; background: var(--tile); position: relative; overflow: hidden; }
.cb-lr .th img { position: absolute; inset: 7%; width: 86%; height: 86%; object-fit: contain; mix-blend-mode: multiply; }
.cb-lr.is-swap .th img { animation: cbSwap .55s var(--spring); }
@keyframes cbSwap { from { transform: scale(.6) rotate(-8deg); } }
.cb-lr .tx { min-width: 0; display: flex; flex-direction: column; gap: 1px; }
.cb-lr .tx small { font-size: 9px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; }
.cb-lr .tx span { color: var(--ink-2); font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cb-lr .tx em { font-style: normal; font-weight: 500; font-size: 11px; }
.cb-lr .chev { color: var(--mute); transition: transform .25s; }
.cb-lr:hover .chev { transform: translateX(2px); color: var(--ink); }
.cb-lr:hover .th, .cb-lr.is-hl .th { box-shadow: inset 0 0 0 1.5px var(--ink); }
.cb-lr .x { border: 0; background: none; color: var(--mute); font-size: 16px; padding: 4px; }
.cb-know { padding: 10px 0 2px; display: grid; gap: 6px; }
.cb-know > b { font-size: 10px; font-weight: 600; letter-spacing: .2em; color: var(--ink-2); }
.cb-k { display: flex; align-items: center; gap: 8px; font-size: 11.5px; }
.cb-k i { width: 16px; height: 16px; border-radius: 50%; border: 1.5px solid var(--ink); display: grid; place-items: center; flex-shrink: 0; transition: background-color .3s; transition-delay: var(--d, 0ms); }
.cb-k i svg { opacity: 0; } .cb-k i path { stroke-dasharray: 14; stroke-dashoffset: 14; }
.cb-k.is-ok i { background: var(--ink); }
.cb-k.is-ok i svg { opacity: 1; } .cb-k.is-ok i path { animation: cbTick .45s ease-out forwards; animation-delay: var(--d, 0ms); }
@keyframes cbTick { to { stroke-dashoffset: 0; } }
.cb-k span { flex: 1; }
.cb-k small { color: var(--ink-2); font-size: 10.5px; }
.cb-buy { position: relative; overflow: hidden; margin-top: 12px; height: clamp(36px, 5.2vh, 44px); border: 0; border-radius: 9px; background: var(--btn); color: #fff; font-weight: 500; font-size: 12.5px; display: flex; align-items: center; justify-content: center; gap: 8px; flex-shrink: 0; }
.cb-buy::before { content: ''; position: absolute; top: 0; bottom: 0; width: 40%; left: -50%; background: linear-gradient(100deg, transparent, rgba(255,255,255,.3), transparent); transform: skewX(-18deg); }
.cb-buy.is-shine::before { animation: cbShine .8s ease-out; }
@keyframes cbShine { to { left: 120%; } }
.cb-buy:disabled { opacity: .5; cursor: not-allowed; }
.cb-reco { padding: 12px 14px; flex-shrink: 0; }
.cb-reco h3 { margin: 0 0 8px; font-size: 11.5px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.cb-recos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.cb-rc { border: 0; background: none; padding: 0; text-align: left; display: flex; flex-direction: column; gap: 1px; min-width: 0; }
.cb-rc .th { aspect-ratio: 523/450; border-radius: 8px; background: var(--tile); position: relative; overflow: hidden; margin-bottom: 4px; transition: transform .25s var(--out); }
.cb-rc:hover .th { transform: translateY(-2px); }
.cb-rc .th img { position: absolute; inset: 7%; width: 86%; height: 86%; object-fit: contain; mix-blend-mode: multiply; }
.cb-rc small { font-size: 9px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cb-rc span { font-size: 10.5px; color: var(--ink-2); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cb-rc em { font-style: normal; font-size: 10.5px; font-weight: 500; }

/* Message */
#toast { position: fixed; left: 50%; top: 18px; z-index: 200; transform: translate(-50%, -20px) scale(.9); opacity: 0; padding: 8px 16px; border-radius: 999px; background: var(--ink); color: #fff; font-size: 12px; pointer-events: none; transition: transform .45s var(--spring), opacity .25s; white-space: nowrap; }
#toast.is-on { transform: translate(-50%, 0); opacity: 1; }
.cb-fly { position: fixed; z-index: 150; border-radius: 8px; background: var(--tile) center/contain no-repeat; pointer-events: none; transition: transform .6s cubic-bezier(.5,0,.2,1), opacity .6s; }

/* ── Commande ── */
.cb-modal { position: fixed; inset: 0; z-index: 120; display: none; align-items: flex-end; justify-content: center; background: rgba(21,21,21,.42); }
.cb-modal.is-open { display: flex; }
.cb-form { width: 100%; max-width: 520px; max-height: 92vh; overflow-y: auto; padding: 20px 18px calc(20px + env(safe-area-inset-bottom, 0px)); border-radius: 18px 18px 0 0; background: var(--float); display: grid; gap: 12px; animation: cbIn .4s var(--out); font-size: 13px; }
@keyframes cbIn { from { transform: translateY(40px); opacity: 0; } }
@media (min-width: 700px) { .cb-modal { align-items: center; } .cb-form { border-radius: 18px; } }
.cb-form h2 { margin: 0; font: 500 24px var(--serif); }
.cb-form label { display: block; font-weight: 600; font-size: 12px; margin-bottom: 5px; }
.cb-form input:not([type=checkbox]), .cb-form select { width: 100%; min-height: 44px; padding: 0 12px; border: 1px solid var(--line); border-radius: 9px; background: #fff; font-size: 15px; }
.cb-form input:focus, .cb-form select:focus { outline: none; border-color: var(--ink); }
.cb-op { display: grid; grid-template-columns: 52px 1fr; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--line); }
.cb-op .th { width: 52px; height: 52px; border-radius: 8px; background: var(--tile); position: relative; overflow: hidden; }
.cb-op .th img { position: absolute; inset: 8%; width: 84%; height: 84%; object-fit: contain; mix-blend-mode: multiply; }
.cb-op-h { display: flex; justify-content: space-between; gap: 8px; align-items: center; }
.cb-op-h label { display: flex; align-items: center; gap: 8px; margin: 0; font-weight: 500; font-size: 13px; }
.cb-pills { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 7px; }
.cb-pill { min-width: 38px; min-height: 32px; padding: 0 10px; border-radius: 999px; border: 1px solid var(--line); background: #fff; font-size: 12px; }
.cb-pill[aria-pressed="true"] { background: var(--ink); border-color: var(--ink); color: #fff; }
.cb-pill:disabled { opacity: .35; text-decoration: line-through; cursor: not-allowed; }
.cb-pills.is-missing .cb-pill { border-color: #b42318; }
.cb-sum { display: flex; justify-content: space-between; font-weight: 600; font-size: 14px; padding-top: 4px; }
.cb-err { color: #b42318; font-weight: 600; font-size: 12.5px; margin: 0; }
.cb-err:empty { display: none; }
.cb-x { justify-self: end; margin: -6px -4px -8px 0; width: 34px; height: 34px; border: 0; border-radius: 50%; background: var(--tile); font-size: 17px; }
.cb-main { width: 100%; min-height: 48px; border: 0; border-radius: 9px; background: var(--btn); color: #fff; font-weight: 500; font-size: 14px; }
.cb-main:disabled { opacity: .5; }
.cb-done { text-align: center; padding: 10px 0; }
.cb-done i { display: grid; place-items: center; width: 58px; height: 58px; margin: 0 auto 12px; border-radius: 50%; background: var(--ink); color: #fff; font-style: normal; font-size: 26px; }

/* ── Sections sous la cabine ── */
.cb-wrap { width: 100%; max-width: 1160px; margin: 0 auto; padding: 0 24px; }
.cb-sec { padding: clamp(48px, 7vw, 88px) 0; }
.cb-h2 { margin: 0; font: 500 clamp(26px, 3.2vw, 40px)/1.1 var(--serif); letter-spacing: -.01em; text-wrap: balance; }
.cb-sub { margin: 10px 0 0; color: var(--ink-2); font-size: 14px; max-width: 60ch; }
.cb-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 22px; border: 0; border-radius: 9px; background: var(--btn); color: #fff; font: 500 14px var(--sans); text-decoration: none; }
.cb-kick { font-size: 10.5px; font-weight: 600; letter-spacing: .2em; color: var(--ink-2); text-transform: uppercase; margin: 0 0 8px; }
.cb-prom { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.cb-prom > div { padding: 22px; }
.cb-prom b { display: block; font: 500 19px var(--serif); margin-bottom: 4px; }
.cb-prom span { color: var(--ink-2); font-size: 13.5px; }
.cb-how { display: grid; grid-template-columns: 1fr 1.2fr; gap: 40px; align-items: center; }
.cb-steps { display: grid; gap: 10px; counter-reset: st; }
.cb-steps div { padding: 16px 18px 16px 56px; position: relative; font-size: 14px; }
.cb-steps div::before { counter-increment: st; content: counter(st); position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 28px; height: 28px; border-radius: 50%; background: var(--ink); color: #fff; display: grid; place-items: center; font-weight: 600; font-size: 12.5px; }
.cb-nums { display: flex; gap: 40px; flex-wrap: wrap; margin: 26px 0; }
.cb-nums b { display: block; font: 500 36px var(--serif); }
.cb-nums span { color: var(--ink-2); font-size: 13px; }
.cb-revs { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
.cb-rev { margin: 0; padding: 20px; }
.cb-rev blockquote { margin: 8px 0 12px; font-size: 14px; }
.cb-rev figcaption { font-size: 12.5px; font-weight: 600; }
.cb-faq { display: grid; grid-template-columns: 1fr 1.4fr; gap: 40px; }
.cb-faq details { border-bottom: 1px solid var(--line); padding: 14px 0; }
.cb-faq summary { cursor: pointer; font-weight: 500; font-size: 14.5px; list-style: none; display: flex; justify-content: space-between; gap: 12px; }
.cb-faq summary::after { content: '+'; font-size: 18px; color: var(--ink-2); }
.cb-faq details[open] summary::after { content: '−'; }
.cb-faq p { margin: 10px 0 0; color: var(--ink-2); font-size: 14px; }
.js [data-rv] { opacity: 0; transform: translateY(14px); transition: opacity .7s var(--out), transform .8s var(--out); transition-delay: var(--d, 0ms); }
.js [data-rv].is-rv { opacity: 1; transform: none; }
.cb-foot { padding: 30px 24px 40px; text-align: center; color: var(--mute); font-size: 12px; }

@media (max-width: 1100px) { #app { padding: 0 3vw; } }
@media (max-width: 860px) {
    body { font-size: 13px; }
    #app { height: auto; display: flex; flex-direction: column; padding: 0 16px; gap: 14px; }
    .cb-left { order: 3; padding: 0; height: 88svh; }
    .cb-brand { order: -1; }
    .cb-stage { order: 1; height: min(92svh, 820px); }
    .cb-right { order: 2; padding: 0; }
    .cb-look { flex: none; }
    .cb-lr { flex: none; max-height: none; }
    .cb-lr .th { height: 64px; }
    #figure, #tt, #shadow, .cb-hint { left: 50%; }
    #figure.is-tenue { top: 7%; height: 80%; }
    #figure.is-coffret { bottom: 13%; width: 92%; }
    .cb-rail { left: 0; top: 6%; }
    .cb-strip { left: auto; right: 0; top: 8%; }
    .cb-mode { left: auto; right: 0; top: 0; }
    .cb-rb .tip { display: none; }
    #room { left: 0; right: 0; top: 0; bottom: 0; }
    .cb-prom, .cb-how, .cb-faq { grid-template-columns: 1fr; gap: 18px; }
    .cb-mobhead { display: flex !important; gap: 12px; }
    .cb-mobhead .cb-logo { min-width: 0; font-size: clamp(18px, 6vw, 24px); }
    .cb-brand { display: none; }
}
.cb-mobhead { display: none; align-items: center; justify-content: space-between; padding: 14px 0 0; }
@media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; transition: none !important; } .js [data-rv] { opacity: 1; transform: none; } }
</style>
</head>
<body>
@include('public.shops.models._top')
@php $studioOn = collect($home)->contains(fn ($x) => $x['type'] === 'studio' && $x['on']); @endphp
@foreach($home as $sec)
@continue(!$sec['on'])
@switch($sec['type'])
    @case('studio')
    <div class="cb-mobhead cb-wrap" style="padding-inline:16px"><a class="cb-logo" href="{{ $s['url'] }}">{{ $T($sec, 'title') ?: $s['name'] }}</a>@if($wa)<a class="cb-ask" href="{{ $wa }}" target="_blank" rel="noopener">Une question ?</a>@endif</div>
    @if(!count($cb['slots']))
    <section class="cb-sec"><div class="cb-wrap" style="text-align:center"><h1 class="cb-h2">{{ $T($sec, 'title') ?: $s['name'] }}</h1><p class="cb-sub" style="margin-inline:auto">{{ $T($sec, 'text') }}</p><p class="cb-sub" style="margin-inline:auto">La cabine s’ouvrira dès que la boutique aura des produits en ligne avec photo.</p></div></section>
    @else
    <main id="app" aria-label="Cabine d’essayage">
        <div class="cb-left">
            <div class="cb-brand">
                <a class="cb-logo" href="{{ $s['url'] }}">{{ $T($sec, 'title') ?: $s['name'] }}</a>
                @if($wa)<a class="cb-ask" href="{{ $wa }}" target="_blank" rel="noopener"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l1.3-4A8 8 0 1 1 8 18.7z"/></svg>Une question ?</a>@endif
            </div>
            <section class="cb-shop cb-panel" aria-label="La boutique">
                <label class="cb-search"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg><span class="cb-sr">Rechercher</span><input type="search" id="q" placeholder="Rechercher un produit, une catégorie…" autocomplete="off"></label>
                <div class="cb-tabs" role="tablist" aria-label="Rayons" id="tabs"><span class="cb-tabline" id="tabline"></span></div>
                <div class="cb-scroll" id="scroll">
                    <div class="cb-row"><h2>Filtres rapides</h2><button type="button" id="clearTile">Tout voir</button></div>
                    <div class="cb-tiles" id="tiles">
                        <button type="button" class="cb-tileb w1" data-tile="new" aria-pressed="false">Nouveau</button>
                        <button type="button" class="cb-tileb w2" data-tile="promo" aria-pressed="false">Promos</button>
                        <button type="button" class="cb-tileb w3" data-tile="saved" aria-pressed="false">Favoris</button>
                        <button type="button" class="cb-tileb w4" data-tile="soft" aria-pressed="false">Prix doux</button>
                    </div>
                    <div class="cb-row"><h2 id="gridH">Populaire dans la boutique</h2><span id="count"></span></div>
                    <div class="cb-grid" id="grid"></div>
                </div>
                <nav class="cb-bar" aria-label="Raccourcis">
                    <button type="button" data-nav="home"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l9-7 9 7v9H3z"/></svg>Accueil</button>
                    <button type="button" data-nav="explore" aria-pressed="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M15.5 8.5l-2 5-5 2 2-5z"/></svg>Explorer</button>
                    <button type="button" data-nav="inspire"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/></svg>Inspire-moi</button>
                    <button type="button" data-nav="saved" aria-pressed="false"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/></svg>Favoris</button>
                    <button type="button" data-nav="order"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14l-1 12H6zM9 8V6a3 3 0 0 1 6 0v2"/></svg>Commander</button>
                </nav>
            </section>
        </div>

        <section class="cb-stage" aria-label="Essayage">
            @if($ambiance)<div id="room" aria-hidden="true"><img src="{{ $ambiance }}" alt="" loading="lazy"><span class="scan"></span><span class="lbl"><i></i>Aperçu · dans la pièce</span></div>@endif
            <div id="tt" aria-hidden="true"><span class="side"></span><span class="top"></span><svg viewBox="0 0 100 30" preserveAspectRatio="none"><ellipse cx="50" cy="15" rx="44" ry="10.5" pathLength="300"/></svg><span class="lab"><i>⟲</i> 360° <i>⟳</i></span></div>
            <span id="shadow" aria-hidden="true"></span>
            <div id="figure" class="is-{{ $cb['layout'] }}" role="img" aria-label="Votre tenue"><div id="body"><div id="spin"></div></div></div>
            <div class="cb-rail" id="rail" role="toolbar" aria-label="Outils de la cabine"></div>
            <div class="cb-strip" id="strip" aria-label="Pièces portées"></div>
            @if($ambiance)<div class="cb-mode" id="mode" role="group" aria-label="Fond"><i aria-hidden="true"></i><button type="button" data-mode="studio" aria-pressed="true">Studio</button><button type="button" data-mode="room" aria-pressed="false">Pièce</button></div>@endif
            <p class="cb-hint">{{ $T($sec, 'text') }}</p>
        </section>

        <aside class="cb-right" aria-label="Ton look">
            <section class="cb-look cb-panel">
                <div class="cb-look-h"><b>TON LOOK</b><span id="total" aria-live="polite"></span></div>
                <div class="cb-rows" id="rows"></div>
                <div class="cb-know" id="know"><b>BON À SAVOIR</b></div>
                <button type="button" class="cb-buy" id="buy"><span id="buyTxt">{{ $T($sec, 'button') }}</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M9 7h8v8"/></svg></button>
            </section>
            <section class="cb-reco cb-panel" aria-labelledby="recoH">
                <h3 id="recoH"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/></svg>Recommandé pour vous</h3>
                <div class="cb-recos" id="recos"></div>
            </section>
        </aside>
    </main>
    @endif
    @break

    @case('promise')
    <section class="cb-sec" style="padding-block:clamp(28px,4vw,48px)" aria-label="Nos engagements">
        <div class="cb-wrap"><div class="cb-prom">
            @foreach([0, 1, 2] as $i)@php $it = HS::item('cabine', $sec, $i); @endphp
            <div class="cb-panel" data-rv style="--d:{{ $i * 80 }}ms"><b>{{ $it['title'] }}</b><span>{{ $it['text'] }}</span></div>
            @endforeach
        </div></div>
    </section>
    @break

    @case('how')
    <section class="cb-sec" aria-labelledby="cbHowH">
        <div class="cb-wrap cb-how">
            <div data-rv><p class="cb-kick">Comment ça marche</p><h2 class="cb-h2" id="cbHowH">{{ $T($sec, 'title') }}</h2><p class="cb-sub">{{ $T($sec, 'text') }}</p>@if($studioOn)<p style="margin-top:22px"><a class="cb-btn" href="#app">Ouvrir la cabine</a></p>@endif</div>
            <div class="cb-steps" data-rv style="--d:100ms">
                <div class="cb-panel">Touchez une pièce : elle s’essaie aussitôt sur la tenue.</div>
                <div class="cb-panel">Changez de haut, de bas ou d’accessoire autant que vous voulez.</div>
                <div class="cb-panel">Commandez le look entier ou une seule pièce. Vous payez au livreur.</div>
            </div>
        </div>
    </section>
    @break

    @case('reviews')
    @if($pm['rating'] || $pm['delivered'] || count($pm['reviews']))
    <section class="cb-sec" aria-labelledby="cbRevH" style="background:var(--panel)">
        <div class="cb-wrap">
            <div data-rv><p class="cb-kick">Ils ont commandé</p><h2 class="cb-h2" id="cbRevH">{{ $T($sec, 'title') }}</h2></div>
            @if($pm['rating'] || $pm['delivered'])
            <div class="cb-nums" data-rv>
                @if($pm['delivered'])<div><b>{{ number_format($pm['delivered'], 0, ',', ' ') }}</b><span>commandes livrées</span></div>@endif
                @if($pm['rating'])<div><b>{{ number_format($pm['rating']['avg'], 1, ',', ' ') }} / 5</b><span>note moyenne sur {{ $pm['rating']['count'] }} avis</span></div>@endif
            </div>
            @endif
            @if(count($pm['reviews']))
            <div class="cb-revs">
                @foreach($pm['reviews'] as $i => $r)
                <figure class="cb-rev cb-panel" style="background:#fff" data-rv><span role="img" aria-label="{{ max(1, min(5, $r['rating'])) }} sur 5">{{ str_repeat('★', max(1, min(5, $r['rating']))) }}</span><blockquote>{{ $r['text'] }}</blockquote><figcaption>{{ $r['name'] }}</figcaption></figure>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @endif
    @break

    @case('faq')
    @php
        $faqItems = collect($sec['d']['items'])->filter(fn ($q) => $q['q'] !== '')->values();
        if ($faqItems->isEmpty()) $faqItems = collect([
            ['q' => 'Comment fonctionne la cabine ?', 'a' => 'Touchez une pièce dans la boutique : elle s’essaie aussitôt sur la tenue. Les photos montrent les vrais produits de la boutique.'],
            ['q' => 'Quand est-ce que je paie ?', 'a' => 'À la livraison, en espèces, au livreur. Vous ne payez rien avant d’avoir le produit en main.'],
            ['q' => 'Comment choisir ma taille ?', 'a' => 'Au moment de commander, choisissez la taille de chaque pièce. La boutique vous appelle pour confirmer avant l’envoi.'],
            ['q' => 'Puis-je commander une seule pièce ?', 'a' => 'Oui. Dans la commande, décochez les pièces que vous ne voulez pas.'],
            ['q' => 'Comment suivre ma commande ?', 'a' => 'Après l’envoi, un lien « Suivre ma commande » s’affiche. Vous suivez chaque étape sur Shopio.'],
        ]);
    @endphp
    <section class="cb-sec" aria-labelledby="cbFaqH">
        <div class="cb-wrap cb-faq">
            <div data-rv><p class="cb-kick">Questions</p><h2 class="cb-h2" id="cbFaqH">{{ $T($sec, 'title') }}</h2><p class="cb-sub">Une autre question ? La boutique vous répond quand elle vous appelle pour confirmer.</p></div>
            <div data-rv style="--d:100ms">@foreach($faqItems as $i => $f)<details @if($i === 0) open @endif><summary>{{ $f['q'] }}</summary>@if($f['a'] !== '')<p style="white-space:pre-line">{{ $f['a'] }}</p>@endif</details>@endforeach</div>
        </div>
    </section>
    @break

    @default
    @include('public.shops.models._section', ['sec' => $sec, 'theme' => 'cabine'])
@endswitch
@endforeach
<footer class="cb-foot">{{ $s['name'] }} · Paiement à la livraison · Boutique sur Shopio</footer>

{{-- Commande payée à la livraison --}}
<div class="cb-modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="fH">
    <form class="cb-form" id="form" novalidate>
        <button type="button" class="cb-x" id="fx" aria-label="Fermer">×</button>
        <div id="fBody">
            <h2 id="fH">Votre commande</h2>
            <p style="margin:4px 0 6px;color:var(--ink-2)">Paiement à la livraison. La boutique vous appelle pour confirmer.</p>
            <div id="fItems"></div>
            <p class="cb-sum"><span>Total à payer à la livraison</span><span id="fTotal"></span></p>
            <div style="display:grid;gap:12px;margin-top:8px">
                <div><label for="fName">Prénom et nom</label><input id="fName" autocomplete="name" maxlength="80" value="{{ $pm['user']['name'] ?? '' }}"></div>
                <div><label for="fPhone">Téléphone</label><input id="fPhone" type="tel" inputmode="tel" autocomplete="tel" maxlength="16" placeholder="622 00 00 00" value="{{ $pm['user']['phone'] ?? '' }}"></div>
                <div><label for="fCommune">Commune</label><select id="fCommune"><option value="">Choisissez votre commune</option>@foreach($pm['communes'] as $c)<option>{{ $c }}</option>@endforeach</select></div>
                <div><label for="fLand">Adresse ou repère</label><input id="fLand" maxlength="120" placeholder="Ex. : près de la pharmacie"></div>
                <p class="cb-err" id="fErr" role="alert"></p>
                <button type="submit" class="cb-main" id="fSend">Confirmer la commande</button>
            </div>
        </div>
    </form>
</div>
<div id="toast" role="status" aria-live="polite"></div>

<script>
document.documentElement.classList.replace('no-js', 'js');
// Apparition des sections au défilement
(function () {
    const els = document.querySelectorAll('[data-rv]');
    if (!('IntersectionObserver' in window)) { els.forEach(e => e.classList.add('is-rv')); return; }
    const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-rv'); io.unobserve(e.target); } }), { rootMargin: '0px 0px -8% 0px' });
    els.forEach(e => io.observe(e));
})();
</script>
@if(count($cb['slots']) && $studioOn)
<script>
(function () {
    'use strict';
    /* ════════ Données ════════ */
    const PM = @json($pm), CB = @json($cb);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const CUR = PM.shop.currency || 'GNF';
    const RM = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $ = id => document.getElementById(id);
    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const money = n => Math.round(n).toLocaleString('fr-FR').replace(/[  ]/g, ' ') + ' ' + CUR;
    const P = id => CB.products[id];
    const SLOTS = CB.slots, LAYOUT = CB.layout;
    const ZONE_ICON = {
        1: 'M8 4l-4 3 2 4 2-1v10h8V10l2 1 2-4-4-3c-.5 1.6-2 2.5-4 2.5S8.5 5.6 8 4z',
        2: 'M7 4h10l2 16h-5l-2-9-2 9H5z',
        3: 'M4 15c3 0 5-1 6-5h3c0 3 2 5 7 6v3H4z',
        4: 'M6 9h12l-1 11H7zM9 9a3 3 0 0 1 6 0',
    };
    const photoOf = (id, vid) => { const p = P(id), o = vid ? p.opts.find(o => o.id === vid) : null; return (o && o.photo) || p.photo || (p.opts[0] && p.opts[0].photo); };
    const thumbOf = id => P(id).thumb || P(id).photo;

    /* ════════ État ════════ */
    let look = {}, history = [], extras = [], variant = {}, active = 0;
    SLOTS.forEach((sl, i) => { look[sl.key] = sl.shared ? (sl.ids[i] ?? sl.ids[0]) : sl.ids[0]; });
    const SKEY = 'shopio_cb_saved_' + PM.shop.id;
    let saved = new Set(); try { saved = new Set((JSON.parse(localStorage.getItem(SKEY)) || []).filter(id => P(id))); } catch (e) {}
    const saveSaved = () => { try { localStorage.setItem(SKEY, JSON.stringify([...saved])); } catch (e) {} };
    const filter = { cat: 'all', tile: null, q: '' };
    const slotOfProduct = id => {
        const own = SLOTS.findIndex(sl => look[sl.key] === id); if (own >= 0) return own;
        const shared = SLOTS[0] && SLOTS[0].shared;
        if (shared) return SLOTS[0].ids.includes(id) ? active : -1;
        return SLOTS.findIndex(sl => sl.ids.includes(id));
    };
    const worn = () => SLOTS.map(sl => look[sl.key]);
    const isWorn = id => worn().includes(id) || extras.includes(id);

    /* ════════ Scène : une zone par rayon, deux calques (ancien / nouveau) révélés par un balayage ════════ */
    const spin = $('spin');
    SLOTS.forEach((sl, i) => {
        const z = document.createElement('div');
        z.className = 'cb-zone z' + (LAYOUT === 'tenue' ? sl.zone : i + 1);
        z.dataset.slot = sl.key;
        z.innerHTML = '<img class="old" alt="" draggable="false"><img class="new" alt="" draggable="false">';
        spin.appendChild(z);
        const src = photoOf(look[sl.key]);
        z.querySelectorAll('img').forEach(im => { im.src = src; });
        fitZone(z, z.querySelector('.old'));
    });
    const glint = document.createElement('span'); glint.className = 'cb-glint'; spin.appendChild(glint);
    function fitZone(z, im) {
        if (LAYOUT !== 'coffret') return;
        const set = () => { if (im.naturalWidth) z.style.setProperty('--ar', (im.naturalWidth / im.naturalHeight).toFixed(3)); };
        if (im.complete) set(); else im.addEventListener('load', set, { once: true });
    }
    const sweeps = {};
    function render(key) {
        const z = spin.querySelector('[data-slot="' + key + '"]'); if (!z) return;
        const oldI = z.querySelector('.old'), newI = z.querySelector('.new');
        if (sweeps[key]) sweeps[key].finish();
        const src = photoOf(look[key], variant[look[key]]);
        const done = () => { oldI.src = src; oldI.style.clipPath = ''; newI.style.clipPath = ''; glint.style.opacity = 0; fitZone(z, oldI); delete sweeps[key]; };
        newI.src = src;
        if (RM) { done(); return; }
        const go = () => {
            const fr = z.offsetTop / spin.offsetHeight, fh = z.offsetHeight / spin.offsetHeight;
            let raf = 0, t0 = performance.now(), finished = false;
            const finish = () => { if (finished) return; finished = true; cancelAnimationFrame(raf); done(); };
            sweeps[key] = { finish };
            if (LAYOUT === 'coffret' && newI.naturalWidth) z.style.setProperty('--ar', (newI.naturalWidth / newI.naturalHeight).toFixed(3));
            const step = t => {
                const u = Math.min(1, (t - t0) / 720), y = (u < .5 ? 2 * u * u : 1 - Math.pow(-2 * u + 2, 2) / 2) * 100;
                newI.style.clipPath = 'inset(0 0 ' + (100 - y) + '% 0)';
                oldI.style.clipPath = 'inset(' + y + '% 0 0 0)';
                glint.style.top = ((fr + fh * y / 100) * 100) + '%'; glint.style.opacity = u < 1 ? 1 : 0;
                if (u < 1) raf = requestAnimationFrame(step); else finish();
            };
            raf = requestAnimationFrame(step);
        };
        newI.style.clipPath = 'inset(0 0 100% 0)';
        if (newI.complete && newI.naturalWidth) go(); else { newI.onload = go; newI.onerror = () => { toast('Photo indisponible'); done(); }; }
    }

    /* ════════ Changer une pièce ════════ */
    function snapshot() { return { look: Object.assign({}, look), extras: extras.slice() }; }
    function wear(id, opts) {
        opts = opts || {};
        const si = slotOfProduct(id);
        if (si < 0) { // produit hors cabine : ajouté à la commande, en plus du look
            if (extras.includes(id)) return;
            history.push(snapshot()); extras.push(id); sync(); toast('Ajouté à votre commande · ' + P(id).name); return;
        }
        const sl = SLOTS[si];
        if (look[sl.key] === id) return;
        if (sl.shared && worn().includes(id)) return;
        if (!opts.noHistory) history.push(snapshot());
        look[sl.key] = id; render(sl.key);
        if (sl.shared) active = (si + 1) % SLOTS.length; else active = si;
        sync(sl.key);
        if (opts.from) fly(opts.from, sl.key);
        if (!opts.quiet) toast('Essai · ' + P(id).name);
    }
    function cycle(si) {
        const sl = SLOTS[si]; active = si;
        const ids = sl.ids.filter(id => !sl.shared || id === look[sl.key] || !worn().includes(id));
        if (ids.length < 2) { toast('Une seule pièce dans ce rayon'); return; }
        const next = ids[(ids.indexOf(look[sl.key]) + 1) % ids.length];
        wear(next);
    }
    function undo() {
        const prev = history.pop(); if (!prev) return;
        const changed = SLOTS.filter(sl => prev.look[sl.key] !== look[sl.key]).map(sl => sl.key);
        look = prev.look; extras = prev.extras;
        changed.forEach(render); sync(changed[0]);
        toast('Annulé');
    }
    let inspiring = false;
    function inspire() {
        if (inspiring) return; inspiring = true;
        history.push(snapshot());
        const used = new Set(), plan = [];
        SLOTS.forEach(sl => {
            const pool = sl.ids.filter(id => id !== look[sl.key] && !used.has(id));
            const pick = pool.length ? pool[Math.floor(Math.random() * pool.length)] : look[sl.key];
            used.add(pick); plan.push([sl, pick]);
        });
        plan.forEach(([sl, id], k) => setTimeout(() => {
            if (sl.shared) look[sl.key] = null; // libère la place avant d'y mettre la nouvelle pièce
            look[sl.key] = id; render(sl.key); sync(sl.key);
            if (k === plan.length - 1) { inspiring = false; toast('Inspire-moi · une nouvelle tenue'); }
        }, RM ? 0 : k * 520));
    }

    /* ════════ Interface ════════ */
    const SLOT_ZONE = i => LAYOUT === 'tenue' ? SLOTS[i].zone : 0;
    function railHTML() {
        const ic = d => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round" aria-hidden="true"><path d="' + d + '"/></svg>';
        return '<button type="button" class="cb-rb" data-act="undo" aria-label="Annuler">' + ic('M9 14L4 9l5-5M4 9h10a6 6 0 0 1 0 12h-3') + '<span class="tip">Annuler</span></button>'
            + SLOTS.map((sl, i) => '<button type="button" class="cb-rb" data-slot-btn="' + i + '" aria-pressed="false" aria-label="' + esc(sl.label) + '">' + (SLOT_ZONE(i) ? ic(ZONE_ICON[SLOT_ZONE(i)]) : '<b>' + (i + 1) + '</b>') + '<span class="tip" data-tip="' + i + '"></span></button>').join('')
            + '<button type="button" class="cb-rb" data-act="inspire" aria-label="Inspire-moi">' + ic('M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z') + '<span class="tip">Inspire-moi · une tenue au hasard</span></button>';
    }
    $('rail').innerHTML = railHTML();
    $('strip').innerHTML = SLOTS.map(sl => '<button type="button" class="cb-st" data-st="' + sl.key + '" aria-label="' + esc(sl.label) + ' : changer"><img alt=""></button>').join('');
    // Onglets : Tout + rayons de la boutique
    const cats = CB.cats;
    $('tabs').insertAdjacentHTML('afterbegin', [['all', 'Tout']].concat(cats.map(c => [c, c])).map(([k, l]) => '<button type="button" class="cb-tab" role="tab" data-cat="' + esc(k) + '" aria-selected="' + (k === 'all') + '">' + esc(l) + '</button>').join(''));
    function moveTabLine() { const a = $('tabs').querySelector('[aria-selected="true"]'); if (!a) return; $('tabline').style.left = a.offsetLeft + 'px'; $('tabline').style.width = a.offsetWidth + 'px'; }

    const prices = Object.values(CB.products).map(p => p.price).sort((a, b) => a - b);
    const median = prices.length ? prices[Math.floor(prices.length / 2)] : 0;
    const norm = v => String(v).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    function shown() {
        return Object.values(CB.products).filter(p => {
            if (filter.cat !== 'all' && (p.cat || 'Nos produits') !== filter.cat) return false;
            if (filter.tile === 'new' && !p.new) return false;
            if (filter.tile === 'promo' && !(p.old && p.old > p.price)) return false;
            if (filter.tile === 'saved' && !saved.has(p.id)) return false;
            if (filter.tile === 'soft' && p.price > median) return false;
            if (filter.q && !norm(p.name + ' ' + p.cat).includes(norm(filter.q))) return false;
            return true;
        });
    }
    function priceHTML(p) { return (p.old && p.old > p.price ? '<s>' + money(p.old) + '</s>' : '') + money(p.price); }
    function renderGrid() {
        const L = shown();
        $('gridH').textContent = filter.tile === 'saved' ? 'Vos favoris' : filter.tile === 'new' ? 'Les nouveautés' : filter.tile === 'promo' ? 'En promotion' : filter.tile === 'soft' ? 'À petit prix' : filter.cat !== 'all' ? filter.cat : 'Populaire dans la boutique';
        $('count').textContent = L.length + ' pièce' + (L.length > 1 ? 's' : '');
        $('grid').innerHTML = L.length ? L.map((p, k) => '<div class="cb-card is-in' + (isWorn(p.id) ? ' is-worn' : '') + '" role="button" tabindex="0" data-wear="' + p.id + '" style="--k:' + Math.min(k, 14) + '" aria-label="Essayer ' + esc(p.name) + '">'
            + '<div class="cb-thumb">' + (thumbOf(p.id) ? '<img src="' + esc(thumbOf(p.id)) + '" alt="" loading="lazy">' : '')
            + '<button type="button" class="cb-heart" data-heart="' + p.id + '" aria-pressed="' + saved.has(p.id) + '" aria-label="Favori : ' + esc(p.name) + '"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/></svg></button>'
            + '<span class="cb-worn">PORTÉ</span><span class="cb-try">' + (slotOfProduct(p.id) < 0 ? 'Ajouter' : 'Essayer') + '</span></div>'
            + '<span class="cb-store">' + esc(p.cat || 'Boutique') + '</span><span class="cb-name">' + esc(p.name) + '</span><span class="cb-price">' + priceHTML(p) + '</span></div>').join('')
            : '<p class="cb-empty">Aucune pièce ne correspond à vos filtres.</p>';
    }
    function setFilter(f) {
        Object.assign(filter, f);
        $('tabs').querySelectorAll('[data-cat]').forEach(b => b.setAttribute('aria-selected', b.dataset.cat === filter.cat));
        $('tiles').querySelectorAll('[data-tile]').forEach(b => b.setAttribute('aria-pressed', b.dataset.tile === filter.tile));
        document.querySelectorAll('[data-nav="saved"]').forEach(b => b.setAttribute('aria-pressed', filter.tile === 'saved'));
        moveTabLine(); renderGrid();
    }

    let shownTotal = 0, totalRaf = 0;
    const lookLines = () => worn().map((id, i) => ({ id, slot: i })).concat(extras.map(id => ({ id, slot: -1 })));
    const priceOf = id => { const p = P(id), o = variant[id] ? p.opts.find(o => o.id === variant[id]) : null; return o ? o.price : p.price; };
    function sync(changedKey) {
        // Total qui compte jusqu'à sa nouvelle valeur
        const total = lookLines().reduce((s, l) => s + priceOf(l.id), 0);
        const from = shownTotal, t0 = performance.now(); cancelAnimationFrame(totalRaf);
        const step = t => { const u = RM ? 1 : Math.min(1, (t - t0) / 600), v = from + (total - from) * (1 - Math.pow(1 - u, 3)); $('total').textContent = money(v); shownTotal = v; if (u < 1) totalRaf = requestAnimationFrame(step); else shownTotal = total; };
        totalRaf = requestAnimationFrame(step);
        // Lignes du look
        $('rows').innerHTML = lookLines().map(l => { const p = P(l.id); return '<div class="cb-lr' + (l.slot >= 0 && SLOTS[l.slot].key === changedKey ? ' is-swap' : '') + '" role="button" tabindex="0" ' + (l.slot >= 0 ? 'data-cycle="' + l.slot + '"' : '') + ' data-row="' + (l.slot >= 0 ? SLOTS[l.slot].key : 'x' + l.id) + '" aria-label="' + (l.slot >= 0 ? 'Changer : ' : '') + esc(p.name) + '">'
            + '<span class="th">' + (thumbOf(l.id) ? '<img src="' + esc(thumbOf(l.id)) + '" alt="">' : '') + '</span>'
            + '<span class="tx"><small>' + esc(l.slot >= 0 ? SLOTS[l.slot].label : 'En plus') + '</small><span>' + esc(p.name) + '</span><em>' + money(priceOf(l.id)) + '</em></span>'
            + (l.slot >= 0 ? '<span class="chev" aria-hidden="true">›</span>' : '<button type="button" class="x" data-unextra="' + l.id + '" aria-label="Retirer ' + esc(p.name) + '">×</button>') + '</div>'; }).join('');
        // Pièces portées et boutons du rail
        SLOTS.forEach((sl, i) => {
            const st = $('strip').querySelector('[data-st="' + sl.key + '"]'), im = st.querySelector('img'), src = thumbOf(look[sl.key]);
            if (im.getAttribute('src') !== src) { im.src = src; if (changedKey === sl.key) { st.classList.remove('is-pop'); void st.offsetWidth; st.classList.add('is-pop'); } }
            const tip = $('rail').querySelector('[data-tip="' + i + '"]'); tip.textContent = sl.label + ' · ' + P(look[sl.key]).name;
            $('rail').querySelector('[data-slot-btn="' + i + '"]').setAttribute('aria-pressed', i === active && SLOTS[0].shared ? 'true' : 'false');
        });
        $('rail').querySelector('[data-act="undo"]').disabled = !history.length;
        // Bon à savoir (que des faits vrais)
        const L = lookLines().map(l => P(l.id));
        const inStock = L.every(p => p.stock === null || p.stock === undefined || p.stock > 0);
        const sized = L.filter(p => p.sizes.length);
        const know = [
            [true, 'Paiement à la livraison', 'En espèces'],
            [true, 'Appel de confirmation', 'Avant l’envoi'],
            [inStock, inStock ? 'Tout est disponible' : 'Une pièce est épuisée', inStock ? '' : 'Retirez-la de la commande'],
            [true, sized.length ? 'Tailles au choix' : 'Taille unique', sized.length ? 'À la commande' : ''],
        ];
        $('know').innerHTML = '<b>BON À SAVOIR</b>' + know.map(([ok, t, n], k) => '<div class="cb-k' + (ok ? ' is-ok' : '') + '" style="--d:' + k * 140 + 'ms"><i><svg width="9" height="9" viewBox="0 0 12 12" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 6.2l2.3 2.3L9.5 3.8"/></svg></i><span>' + esc(t) + '</span>' + (n ? '<small>' + esc(n) + '</small>' : '') + '</div>').join('');
        $('buy').disabled = !!PM.settings.vacation;
        renderRecos(); renderGrid();
    }
    function renderRecos() {
        const w = new Set(lookLines().map(l => l.id));
        const wornCats = new Set(lookLines().map(l => P(l.id).cat));
        const pool = Object.values(CB.products).filter(p => !w.has(p.id) && slotOfProduct(p.id) >= 0);
        pool.sort((a, b) => (wornCats.has(b.cat) - wornCats.has(a.cat)) || ((b.old ? 1 : 0) - (a.old ? 1 : 0)) || ((b.new ? 1 : 0) - (a.new ? 1 : 0)));
        const pick = pool.slice(0, 3);
        $('recos').innerHTML = pick.map(p => '<button type="button" class="cb-rc" data-wear="' + p.id + '" aria-label="Essayer ' + esc(p.name) + '"><span class="th">' + (thumbOf(p.id) ? '<img src="' + esc(thumbOf(p.id)) + '" alt="" loading="lazy">' : '') + '</span><small>' + esc(p.cat || 'Boutique') + '</small><span>' + esc(p.name) + '</span><em>' + money(p.price) + '</em></button>').join('');
        $('recos').closest('section').hidden = !pick.length;
    }
    let toastT = 0;
    function toast(m) { const t = $('toast'); t.textContent = m; t.classList.add('is-on'); clearTimeout(toastT); toastT = setTimeout(() => t.classList.remove('is-on'), 1800); }
    function fly(fromEl, key) {
        if (RM || !fromEl) return;
        const img = fromEl.querySelector('img'), st = $('strip').querySelector('[data-st="' + key + '"]'); if (!img || !st) return;
        const a = img.getBoundingClientRect(), b = st.getBoundingClientRect();
        const f = document.createElement('div'); f.className = 'cb-fly';
        f.style.cssText = 'left:' + a.left + 'px;top:' + a.top + 'px;width:' + a.width + 'px;height:' + a.height + 'px;background-image:url("' + img.src + '")';
        document.body.appendChild(f);
        requestAnimationFrame(() => { f.style.transform = 'translate(' + (b.left - a.left) + 'px,' + (b.top - a.top) + 'px) scale(' + (b.width / a.width) + ')'; f.style.opacity = .3; });
        setTimeout(() => f.remove(), 650);
    }

    /* ════════ Clics et clavier ════════ */
    document.addEventListener('click', e => {
        const t = e.target;
        const heart = t.closest('[data-heart]');
        if (heart) { e.stopPropagation(); const id = +heart.dataset.heart; saved.has(id) ? saved.delete(id) : saved.add(id); saveSaved(); heart.setAttribute('aria-pressed', saved.has(id)); if (filter.tile === 'saved') renderGrid(); return; }
        const un = t.closest('[data-unextra]'); if (un) { history.push(snapshot()); extras = extras.filter(id => id !== +un.dataset.unextra); sync(); return; }
        const w = t.closest('[data-wear]'); if (w) { wear(+w.dataset.wear, { from: w }); return; }
        const cy = t.closest('[data-cycle]'); if (cy) { const i = +cy.dataset.cycle; cycle(i); setFilter({ cat: SLOTS[i].cat || 'all' }); return; }
        const st = t.closest('[data-st]'); if (st) { cycle(SLOTS.findIndex(sl => sl.key === st.dataset.st)); return; }
        const sb = t.closest('[data-slot-btn]'); if (sb) { const i = +sb.dataset.slotBtn; cycle(i); setFilter({ cat: SLOTS[i].cat || 'all' }); return; }
        const act = t.closest('[data-act]'); if (act) { act.dataset.act === 'undo' ? undo() : inspire(); return; }
        const cat = t.closest('[data-cat]'); if (cat) { setFilter({ cat: cat.dataset.cat }); return; }
        const tile = t.closest('[data-tile]'); if (tile) { setFilter({ tile: filter.tile === tile.dataset.tile ? null : tile.dataset.tile }); return; }
        const nav = t.closest('[data-nav]');
        if (nav) {
            const n = nav.dataset.nav;
            if (n === 'home') { $('q').value = ''; setFilter({ cat: 'all', tile: null, q: '' }); $('scroll').scrollTo({ top: 0, behavior: 'smooth' }); }
            if (n === 'saved') setFilter({ tile: filter.tile === 'saved' ? null : 'saved' });
            if (n === 'inspire') inspire();
            if (n === 'order') openOrder();
            return;
        }
        const md = t.closest('[data-mode]');
        if (md) { const room = md.dataset.mode === 'room'; $('room').classList.toggle('is-on', room); $('mode').classList.toggle('is-room', room); $('mode').querySelectorAll('[data-mode]').forEach(b => b.setAttribute('aria-pressed', b === md)); return; }
    });
    $('clearTile').addEventListener('click', () => setFilter({ tile: null }));
    $('q').addEventListener('input', e => setFilter({ q: e.target.value.trim() }));
    document.addEventListener('keydown', e => {
        if ((e.key === 'Enter' || e.key === ' ') && e.target.matches('[role="button"][data-wear], [role="button"][data-cycle]')) { e.preventDefault(); e.target.click(); }
        if (e.key === 'Escape' && $('modal').classList.contains('is-open')) closeOrder();
    });
    // Survol : la ligne du look et sa vignette se répondent
    document.addEventListener('pointerover', e => {
        const r = e.target.closest('[data-row]'), s = e.target.closest('[data-st]');
        document.querySelectorAll('#strip .is-hl, #rows .is-hl').forEach(x => x.classList.remove('is-hl'));
        if (r) $('strip').querySelector('[data-st="' + r.dataset.row + '"]')?.classList.add('is-hl');
        if (s) $('rows').querySelector('[data-row="' + s.dataset.st + '"]')?.classList.add('is-hl');
    });

    /* ════════ Plateau : glisser pour tourner, retour en douceur ════════ */
    const body = $('body'), tt = $('tt'), shadow = $('shadow'), fig = $('figure');
    let ry = 0, drag = null, raf = 0;
    function placeTable() {
        const r = fig.getBoundingClientRect(), stage = fig.parentNode.getBoundingClientRect();
        const w = r.width * (LAYOUT === 'tenue' ? 1.12 : .9), h = Math.max(36, w / 3.3);
        const feet = r.bottom - stage.top - r.height * (LAYOUT === 'tenue' ? .014 : 0);
        tt.style.width = w + 'px'; tt.style.height = h + 'px'; tt.style.top = (feet - .4 * h) + 'px';
        shadow.style.top = (feet - h * .02) + 'px'; shadow.style.width = (w * .46 / (LAYOUT === 'tenue' ? 1.12 : .9)) + 'px';
    }
    function apply() { spin.style.setProperty('--ry', ry.toFixed(2) + 'deg'); shadow.style.transform = 'translate(calc(-50% + ' + (ry * .4).toFixed(1) + 'px), -50%)'; }
    function loop() {
        raf = 0;
        if (drag) return;
        ry *= .92; apply();
        if (Math.abs(ry) > .05) raf = requestAnimationFrame(loop); else { ry = 0; apply(); tt.classList.remove('is-fast'); }
    }
    body.addEventListener('pointerdown', e => { if (e.button > 0) return; drag = { x: e.clientX, ry }; body.classList.add('is-drag'); tt.classList.add('is-fast'); try { body.setPointerCapture(e.pointerId); } catch (x) {} });
    body.addEventListener('pointermove', e => { if (!drag) return; ry = Math.max(-34, Math.min(34, drag.ry + (e.clientX - drag.x) * .22)); apply(); });
    const end = () => { if (!drag) return; drag = null; body.classList.remove('is-drag'); if (!raf) raf = requestAnimationFrame(loop); };
    body.addEventListener('pointerup', end); body.addEventListener('pointercancel', end);
    addEventListener('resize', () => { placeTable(); moveTabLine(); });
    if ('ResizeObserver' in window) new ResizeObserver(placeTable).observe(fig);

    /* ════════ Commande payée à la livraison ════════ */
    let order = [];
    function openOrder() {
        if (PM.settings.vacation) { toast(PM.settings.vacationMsg || 'La boutique est en pause.'); return; }
        order = lookLines().map(l => { const p = P(l.id); return { id: l.id, on: !(p.stock !== null && p.stock !== undefined && p.stock <= 0), variant: variant[l.id] || ((p.opts.find(o => o.stock === null || o.stock > 0) || {}).id ?? null), size: p.sizes.length === 1 ? p.sizes[0] : null }; });
        drawOrder();
        $('fErr').textContent = '';
        $('modal').classList.add('is-open');
        const b = $('buy'); b.classList.remove('is-shine'); void b.offsetWidth; b.classList.add('is-shine');
        setTimeout(() => ($('fName').value ? $('fPhone') : $('fName')).focus(), 60);
    }
    function closeOrder() { $('modal').classList.remove('is-open'); }
    const linePrice = l => { const p = P(l.id), o = l.variant ? p.opts.find(o => o.id === l.variant) : null; return o ? o.price : p.price; };
    function drawOrder() {
        $('fItems').innerHTML = order.map((l, k) => {
            const p = P(l.id);
            return '<div class="cb-op"><span class="th">' + (thumbOf(l.id) ? '<img src="' + esc(thumbOf(l.id)) + '" alt="">' : '') + '</span><div>'
                + '<div class="cb-op-h"><label><input type="checkbox" data-on="' + k + '"' + (l.on ? ' checked' : '') + '> ' + esc(p.name) + '</label><b>' + money(linePrice(l)) + '</b></div>'
                + (p.opts.length > 1 ? '<div class="cb-pills" role="group" aria-label="Motif">' + p.opts.map(o => '<button type="button" class="cb-pill" data-ov="' + k + ':' + o.id + '" aria-pressed="' + (o.id === l.variant) + '"' + (o.stock !== null && o.stock <= 0 ? ' disabled' : '') + '>' + esc(o.name) + '</button>').join('') + '</div>' : '')
                + (p.sizes.length ? '<div class="cb-pills" role="group" aria-label="Taille" data-sizes="' + k + '">' + p.sizes.map(z => '<button type="button" class="cb-pill" data-oz="' + k + ':' + esc(z) + '" aria-pressed="' + (z === l.size) + '">' + esc(z) + '</button>').join('') + '</div>' : '')
                + '</div></div>';
        }).join('');
        $('fTotal').textContent = money(order.filter(l => l.on).reduce((s, l) => s + linePrice(l), 0));
    }
    $('fItems').addEventListener('click', e => {
        const ov = e.target.closest('[data-ov]'), oz = e.target.closest('[data-oz]');
        if (ov) { const [k, v] = ov.dataset.ov.split(':'); order[+k].variant = +v; variant[order[+k].id] = +v; render(SLOTS.find(sl => look[sl.key] === order[+k].id)?.key); drawOrder(); sync(); }
        if (oz) { const i = oz.dataset.oz.indexOf(':'); order[+oz.dataset.oz.slice(0, i)].size = oz.dataset.oz.slice(i + 1); drawOrder(); }
    });
    $('fItems').addEventListener('change', e => { const c = e.target.closest('[data-on]'); if (c) { order[+c.dataset.on].on = c.checked; drawOrder(); } });
    $('buy').addEventListener('click', openOrder);
    $('fx').addEventListener('click', closeOrder);
    $('modal').addEventListener('click', e => { if (e.target === $('modal')) closeOrder(); });

    const VK = 'shopio_pm_visitor_' + PM.shop.id;
    let VISITOR = ''; try { VISITOR = JSON.parse(localStorage.getItem(VK)) || ''; } catch (e) {}
    if (!/^[a-z0-9]{12,40}$/.test(VISITOR)) { VISITOR = ''; const a = new Uint8Array(16); crypto.getRandomValues(a); a.forEach(b => { VISITOR += (b % 36).toString(36); }); try { localStorage.setItem(VK, JSON.stringify(VISITOR)); } catch (e) {} }
    const payload = () => ({ visitor: VISITOR, items: order.filter(l => l.on).map(l => ({ id: l.id, qty: 1, variant: l.variant, size: l.size })), name: $('fName').value.trim(), phone: $('fPhone').value, commune: $('fCommune').value, landmark: $('fLand').value.trim() });
    let capT = 0;
    $('form').addEventListener('input', () => {
        if (!PM.settings.carts || !PM.urls.cart) return;
        clearTimeout(capT);
        capT = setTimeout(() => { const d = payload(); d.items = d.items.map(i => ({ id: i.id, qty: i.qty })); fetch(PM.urls.cart, { method: 'POST', keepalive: true, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(d) }).catch(() => {}); }, 1500);
    });
    $('form').addEventListener('submit', async e => {
        e.preventDefault();
        const d = payload(), err = $('fErr'), btn = $('fSend');
        if (!d.items.length) { err.textContent = 'Cochez au moins une pièce.'; return; }
        const miss = order.findIndex(l => l.on && P(l.id).sizes.length && !l.size);
        if (miss >= 0) { err.textContent = 'Choisissez la taille de « ' + P(order[miss].id).name + ' ».'; $('fItems').querySelector('[data-sizes="' + miss + '"]').classList.add('is-missing'); return; }
        if (!d.name && !PM.user) { err.textContent = 'Indiquez votre prénom et votre nom.'; $('fName').focus(); return; }
        if (d.phone.replace(/\D/g, '').length < 8) { err.textContent = 'Indiquez votre numéro de téléphone.'; $('fPhone').focus(); return; }
        if (!d.commune) { err.textContent = 'Choisissez votre commune.'; $('fCommune').focus(); return; }
        btn.disabled = true; btn.textContent = 'Envoi…'; err.textContent = '';
        try {
            const r = await fetch(PM.urls.order, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(d) });
            const j = await r.json().catch(() => ({}));
            if (!r.ok) { err.textContent = (j.errors ? Object.values(j.errors)[0][0] : null) || j.message || 'La commande n’a pas pu être envoyée. Réessayez.'; return; }
            $('fBody').innerHTML = '<div class="cb-done"><i>✓</i><h2>Commande envoyée</h2><p style="color:var(--ink-2)">' + esc(PM.shop.name) + ' vous appelle pour confirmer. Vous payez à la livraison.</p><button type="button" class="cb-main" id="fAgain">Continuer</button></div>';
            $('fAgain').addEventListener('click', closeOrder);
            toast(d.items.length + ' pièce' + (d.items.length > 1 ? 's' : '') + ' commandée' + (d.items.length > 1 ? 's' : '') + ' · ' + money(order.filter(l => l.on).reduce((s, l) => s + linePrice(l), 0)));
        } catch (x) { err.textContent = 'Pas de connexion. Vérifiez votre réseau puis réessayez.'; }
        finally { if ($('fSend')) { $('fSend').disabled = false; $('fSend').textContent = 'Confirmer la commande'; } }
    });

    /* Démarrage */
    sync(); placeTable(); moveTabLine();
    if (document.fonts) document.fonts.ready.then(moveTabLine);
})();
</script>
@endif
</body>
</html>
