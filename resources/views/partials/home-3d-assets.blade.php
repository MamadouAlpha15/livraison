{{-- Styles + scripts de l'accueil 3D (partials/home-3d*.blade.php). Inclus une seule fois par welcome. --}}
@push('styles')
<style>
/* ════════════════════════════════════════════════════════════════
   ACCUEIL 3D — palette bleue Shopio (logo #1F7CF6), une seule famille
   (Plus Jakarta Sans 400→800), motion tokens du brief.
════════════════════════════════════════════════════════════════ */
.sp3 {
    --b: #1F7CF6; --b-btn: #1668D8; --b-h: #1260C9; --b-50: #EAF2FF; --b-100: #D6E6FF; --on-b: #FFFFFF;
    --y: #F59E0B; --y-50: #FFF4DB; --y-ink: #16162A;
    --deep: #0B1E3D; --deep-2: #12305C; --sky: #7DB4FF;
    --ink: #16162A; --ink-2: #45455E; --ink-3: #62627C;
    --bg: #F6F9FF; --surface: #FFFFFF; --line: #E3E8F2;
    --ok: #15803D; --warn: #B45309; --err: #B91C1C;
    --e-out: cubic-bezier(.22,1,.36,1); --e-in: cubic-bezier(.55,0,1,.45); --e-io: cubic-bezier(.65,0,.35,1); --e-spring: cubic-bezier(.34,1.56,.64,1);
    font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: var(--ink); background: var(--bg);
    font-size: 16px; line-height: 1.55; -webkit-font-smoothing: antialiased; overflow-x: clip;
}
:root[data-theme="dark"] .sp3 {
    --b: #4A9BFF; --b-btn: #1668D8; --b-h: #1260C9; --b-50: #10264A; --b-100: #163463;
    --y-50: #2A2210; --ink: #EEF3FB; --ink-2: #C3CDDC; --ink-3: #98A5B8;
    --bg: #081426; --surface: #0F2140; --line: #1E3358;
}
.sp3 *, .sp3 *::before, .sp3 *::after { box-sizing: border-box; }
.sp3 h1, .sp3 h2, .sp3 h3 { letter-spacing: -.025em; font-weight: 800; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
.sp3 .num { font-variant-numeric: tabular-nums; }
.sp3 svg.ico { vertical-align: middle; }
.sr3 { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.w3 { max-width: 1280px; margin: 0 auto; padding-inline: 32px; }
.sec3 { padding-block: 96px; }
.rel3 { position: relative; }
.stack3 { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.h2-3 { margin: 0; font-size: 46px; line-height: 1.04; color: var(--ink); }
.p3 { margin: 0; font-size: 17px; color: var(--ink-2); }
.blue3 { color: var(--b); }
.sky3 { color: var(--sky); }
.on-deep { color: #fff !important; }
.on-deep-2 { color: #C5D3E8 !important; }
.sec3-hd { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; flex-wrap: wrap; margin-bottom: 32px; }

/* Boutons pilule */
.btn3 { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 24px; border-radius: 999px;
        font: 700 16px/1 'Plus Jakarta Sans', sans-serif; text-decoration: none; border: 0; cursor: pointer; white-space: nowrap;
        transition: transform 180ms var(--e-out), background-color 180ms var(--e-out), box-shadow 180ms var(--e-out), color 180ms var(--e-out); }
.btn3:active { transform: scale(.97); }
.btn3-lg { min-height: 56px; padding: 0 28px; font-size: 17px; }
.sp3 .btn3-p { background: var(--b-btn); color: var(--on-b); }
.sp3 .btn3-p:hover { background: var(--b-h); color: var(--on-b); transform: translateY(-2px); box-shadow: 0 10px 24px rgba(22,104,216,.3); }
.sp3 .btn3-s { background: var(--surface); color: var(--ink); box-shadow: inset 0 0 0 1.5px var(--line); }
.sp3 .btn3-s:hover { box-shadow: inset 0 0 0 1.5px var(--b); color: var(--b); }
.sp3 .btn3-y { background: var(--y); color: #16162A; }
.sp3 .btn3-y:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(245,158,11,.35); }
.sp3 .btn3-off { background: var(--line); color: var(--ink-3); cursor: not-allowed; }
.sp3 a:focus-visible, .sp3 button:focus-visible, .sp3 input:focus-visible { outline: 3px solid var(--b); outline-offset: 2px; }

/* Onglet numéroté */
.tab3 { position: absolute; top: 0; left: 0; z-index: 3; width: 64px; height: 56px; border-radius: 0 0 24px 0;
        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; }
.tab3-b { background: var(--b-btn); color: #fff; }
.tab3-y { background: var(--y); color: #16162A; }

/* Badges */
.b3 { display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px; border-radius: 999px; font-size: 13px; font-weight: 800; white-space: nowrap; }
.b3-y { background: var(--y); color: #16162A; }
.b3-g { background: var(--b-50); color: var(--b-btn); }
.b3-w { background: #fff; color: var(--b-btn); }
:root[data-theme="dark"] .b3-g { color: var(--sky); }

/* ── HERO ── */
.h3 { position: relative; overflow: hidden; }
.h3-in { padding-top: 56px; padding-bottom: 64px; }
.h3-g { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); gap: 40px; align-items: center; }
.h3-copy { display: flex; flex-direction: column; gap: 22px; min-width: 0; }
.h3-copy > .b3 { align-self: flex-start; }
.h3-title { margin: 0; font-size: 64px; line-height: 1.02; text-transform: uppercase; color: var(--ink); text-wrap: balance; }
.ext3 { color: var(--b); text-shadow: 1px 1px 0 #1668D8, 2px 2px 0 #145FC6, 3px 3px 0 #1256B4, 4px 4px 0 #104DA2, 5px 5px 0 #0E4490, 10px 12px 20px rgba(11,30,61,.22); }
.h3-sub { margin: 0; font-size: 19px; color: var(--ink-2); max-width: 520px; }
.search3 { display: flex; align-items: center; gap: 10px; height: 56px; padding: 0 6px 0 20px; border-radius: 999px; background: var(--surface);
           box-shadow: inset 0 0 0 1.5px var(--line), 0 8px 24px rgba(22,22,42,.06); transition: box-shadow 180ms var(--e-out); color: var(--ink-3); }
.search3:focus-within { box-shadow: inset 0 0 0 2px var(--b), 0 8px 24px rgba(22,22,42,.08); }
.search3 input { flex: 1; min-width: 0; height: 100%; border: 0; outline: 0; background: transparent; font: 500 16px 'Plus Jakarta Sans', sans-serif; color: var(--ink); }
.search3 input::placeholder { color: var(--ink-3); }
.sp3 .search3 input:focus-visible { outline: none; }
.chips3 { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
.chip3 { display: inline-flex; align-items: center; min-height: 38px; padding: 0 14px; border-radius: 999px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--line);
         color: var(--ink-2); font-size: 14px; font-weight: 600; text-decoration: none; transition: box-shadow 180ms var(--e-out), color 180ms var(--e-out); }
.chip3:hover { box-shadow: inset 0 0 0 1.5px var(--b); color: var(--b); }
.h3-cta { display: flex; gap: 12px; flex-wrap: wrap; }
.feats3 { list-style: none; margin: 10px 0 0; padding: 0; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.feat3 { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--ink-2); line-height: 1.3; }
.feat3-ic { width: 48px; height: 48px; border-radius: 16px; background: var(--b-50); color: var(--b-btn); display: flex; align-items: center; justify-content: center; transition: transform 260ms var(--e-spring); }
.feat3:hover .feat3-ic, .feat3-d:hover .feat3-ic-d { transform: translateY(-4px) rotate(-6deg); }

/* Scène 3D */
.scene3-wrap { position: relative; height: 580px; min-width: 0; }
.scene3-fit { position: absolute; left: 0; top: 0; width: 620px; height: 580px; transform-origin: 0 0; transform: scale(var(--fit, 1)); }
.scene3 { position: absolute; inset: 0; perspective: 1500px; perspective-origin: 50% 40%; }
.stage3 { position: absolute; inset: 0; transform-style: preserve-3d; transform: rotateX(var(--rx, 6deg)) rotateY(var(--ry, -14deg)); transition: transform 500ms var(--e-out); }
.p3d { position: absolute; transform-style: preserve-3d; }
.sphere3 { width: 100%; height: 100%; border-radius: 50%; }
.sphere3-b { background: var(--b); box-shadow: inset -40px -50px 90px rgba(11,30,61,.42), inset 30px 30px 60px rgba(255,255,255,.14); }
.sphere3-y { background: var(--y); box-shadow: inset -14px -18px 30px rgba(140,80,0,.4), inset 10px 10px 20px rgba(255,255,255,.35); }
.lid3 { height: 340px; border-radius: 16px 16px 0 0; background: #1A2333; padding: 10px; transform-origin: 50% 100%; backface-visibility: hidden; animation: lid3 1000ms var(--e-io) 150ms backwards; }
.lid3-screen { height: 100%; border-radius: 8px; background: #F6F9FF; overflow: hidden; display: flex; flex-direction: column; font-size: 10px; color: #16162A; }
.deck3 { height: 300px; margin-inline: -30px; transform-origin: 50% 0; transform: rotateX(82deg); border-radius: 4px 4px 20px 20px;
         background: repeating-linear-gradient(90deg, #BEC6D2 0 22px, #AEB6C2 22px 24px); box-shadow: inset 0 0 0 12px #CDD3DC; }
.mini3-nav { display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #fff; border-bottom: 1px solid #E3E8F2; }
.mini3-nav i { width: 30px; height: 6px; border-radius: 3px; background: #E3E8F2; }
.mini3-nav i:first-of-type { margin-left: auto; }
.mini3-nav em { width: 36px; height: 14px; border-radius: 7px; background: #1668D8; }
.mini3-logo { display: inline-flex; align-items: center; gap: 5px; font-weight: 800; font-size: 11px; color: #16162A; }
.mini3-logo img { width: 14px; height: 14px; border-radius: 4px; }
.mini3-logo b { color: #1F7CF6; }
.mini3-hero { margin: 10px 12px 0; padding: 14px; border-radius: 10px; background: #1668D8; color: #fff; }
.mini3-hero strong { font-weight: 800; font-size: 13px; line-height: 1.2; display: block; }
.mini3-hero span { display: block; margin-top: 8px; height: 18px; border-radius: 9px; background: #fff; width: 75%; }
.mini3-cats { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 6px; padding: 10px 12px; }
.mini3-cats div { display: flex; flex-direction: column; align-items: center; gap: 3px; min-width: 0; }
.mini3-cats span { width: 100%; aspect-ratio: 1; border-radius: 8px; background: #EAF2FF; display: flex; align-items: center; justify-content: center; color: #45455E; }
.mini3-cats small { font-size: 8px; white-space: nowrap; overflow: hidden; max-width: 100%; }
.mini3-sec { padding: 0 12px; font-weight: 800; font-size: 10px; }
.mini3-prods { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; padding: 6px 12px; }
.mini3-prods div { background: #fff; border-radius: 8px; padding: 6px; box-shadow: 0 0 0 1px #E3E8F2; min-width: 0; }
.mini3-prods img, .mini3-prods i { display: block; width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 6px; background: #EAF2FF; }
.mini3-prods b { display: block; font-weight: 800; font-size: 8px; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.phone3 { height: 400px; border-radius: 34px; background: #16162A; padding: 8px; box-shadow: 0 30px 60px rgba(11,30,61,.35); }
.phone3-scr { height: 100%; border-radius: 27px; background: #F6F9FF; overflow: hidden; padding: 30px 12px 12px; display: flex; flex-direction: column; gap: 10px; color: #16162A; }
.phone3-hero { padding: 10px; border-radius: 12px; background: #1668D8; color: #fff; font-weight: 800; font-size: 12px; line-height: 1.25; }
.phone3-slides { position: relative; flex: 1; }
.phone3-slide { position: absolute; inset: 0; display: flex; flex-direction: column; gap: 6px; padding: 8px; border-radius: 14px; background: #fff; box-shadow: 0 0 0 1px #E3E8F2;
                opacity: 0; transform: translateX(18px); transition: opacity 450ms var(--e-out), transform 450ms var(--e-out); }
.phone3-slide.on { opacity: 1; transform: none; }
.phone3-img { position: relative; flex: 1; border-radius: 10px; background: #EAF2FF; overflow: hidden; }
.phone3-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.phone3-img .b3 { position: absolute; top: 6px; left: 6px; }
.phone3-name { font-weight: 700; font-size: 12px; }
.phone3-price { font-weight: 800; font-size: 13px; white-space: nowrap; }
.phone3-btn { height: 28px; border-radius: 14px; background: #1668D8; color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.pill3 { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px; background: #fff; box-shadow: 0 18px 36px rgba(11,30,61,.22); font-size: 13px; font-weight: 800; color: #16162A; white-space: nowrap; }
.pill3 svg { color: #1668D8; }
.pill3-d { background: #0B1E3D; color: #fff; }
.pill3-d svg { color: #7DB4FF; }
.coin3 { width: 68px; height: 68px; border-radius: 999px; background: #F59E0B; color: #16162A; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800;
         box-shadow: inset -7px -9px 14px rgba(140,80,0,.45), inset 5px 5px 10px rgba(255,255,255,.4), 0 18px 32px rgba(11,30,61,.25); }

/* ── Cartes produits 3D ── */
.c3 { position: relative; background: var(--surface); border-radius: 22px; overflow: hidden; box-shadow: 0 0 0 1px var(--line); display: flex; flex-direction: column; min-width: 0;
      transform: perspective(900px) rotateX(var(--crx, 0deg)) rotateY(var(--cry, 0deg)) translateY(var(--cty, 0px));
      transition: transform 140ms var(--e-out), box-shadow 240ms var(--e-out); }
.c3:hover { --cty: -6px; box-shadow: 0 0 0 1px var(--line), 0 18px 36px rgba(22,22,42,.10); }
.c3-media { position: relative; aspect-ratio: 1; overflow: hidden; background: var(--b-50); }
.c3-img { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #45455E; }
.c3-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 500ms var(--e-out); }
.c3:hover .c3-img img { transform: scale(1.06); }
.c3-ph { opacity: .55; }
.c3-media > .b3 { position: absolute; top: 12px; left: 12px; }
.c3-fav { position: absolute; top: 10px; right: 10px; width: 44px; height: 44px; border-radius: 999px; border: 0; background: rgba(255,255,255,.94); color: #45455E;
          display: flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; transition: transform 180ms var(--e-out); }
.c3-fav:hover { transform: scale(1.08); }
.c3-fav.on, .c3-fav.is-fav { color: #D9304F; }
.c3-fav.on svg, .c3-fav.is-fav svg { fill: currentColor; animation: heart3 380ms var(--e-spring); }
.c3-body { padding: 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
.c3-shop { display: flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 600; color: var(--ink-3); min-width: 0; }
.c3-shop svg { color: var(--b); flex-shrink: 0; }
.c3-shop span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.c3-name { color: var(--ink); text-decoration: none; font-weight: 700; font-size: 16px; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 43px; }
.c3-name:hover { color: var(--b); }
.c3-rate { display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--ink-2); }
.c3-rate svg { color: #F59E0B; fill: #F59E0B; }
.c3-rate strong { color: var(--ink); }
.c3-price { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
.c3-price span { font-size: 21px; font-weight: 800; white-space: nowrap; color: var(--ink); }
.c3-price s { font-size: 14px; color: var(--ink-3); white-space: nowrap; }
.c3-stock { height: 6px; border-radius: 6px; background: var(--y-50); overflow: hidden; }
.c3-stock i { display: block; height: 100%; width: 100%; background: var(--y); transform-origin: left center; transform: scaleX(var(--s)); }
.c3-stock-txt { font-size: 14px; font-weight: 700; color: var(--warn); }
.c3 .btn3 { margin-top: auto; min-height: 46px; font-size: 15px; width: 100%; }
.c3-glare { position: absolute; inset: 0; pointer-events: none; opacity: 0; transition: opacity 200ms var(--e-out); mix-blend-mode: soft-light;
            background: radial-gradient(circle at var(--gx, 50%) var(--gy, 50%), rgba(255,255,255,.65), rgba(255,255,255,0) 55%); }
.c3.is-tilt .c3-glare { opacity: 1; }

/* Ventes flash */
.count3 { display: flex; align-items: center; gap: 14px; padding: 12px 12px 12px 20px; border-radius: 999px; background: var(--deep); color: #fff; }
.count3-lbl { font-size: 14px; font-weight: 700; color: #C5D3E8; }
.count3-d { display: flex; align-items: center; gap: 6px; font-size: 24px; font-weight: 800; }
.count3-d > span { min-width: 50px; height: 50px; border-radius: 999px; background: var(--y); color: #16162A; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.count3-s b.tick3 { display: inline-block; animation: tick3 320ms linear; }
.rail3 { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(264px, 1fr); gap: 20px; overflow-x: auto; scroll-snap-type: x mandatory; padding: 6px 4px 14px; scrollbar-width: thin; }
.rail3 > * { scroll-snap-align: start; }

/* Comment ça marche */
.band3-light { background: var(--surface); border-block: 1px solid var(--line); }
.how3 { position: relative; }
.how3-line { position: absolute; left: 16.66%; right: 16.66%; top: 39px; height: 4px; border-radius: 4px; background: var(--b-50); }
.how3-fill { background: var(--b); transform-origin: left center; }
.g3-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; list-style: none; margin: 0; padding: 0; position: relative; }
.g3-3 > li { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 14px; padding: 0 12px; }
.g3-3 h3 { margin: 0; font-size: 24px; color: var(--ink); }
.g3-3 p { margin: 0; color: var(--ink-2); max-width: 320px; }
.step3-ic { width: 80px; height: 80px; border-radius: 999px; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 8px var(--surface); }
.step3-n { font-size: 14px; font-weight: 800; color: var(--b); }

/* Catégories */
.g4-3 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
.cat3 { position: relative; display: flex; flex-direction: column; justify-content: space-between; min-height: 176px; padding: 20px; border-radius: 24px; text-decoration: none; color: #16162A; overflow: hidden;
        transition: transform 240ms var(--e-out), box-shadow 240ms var(--e-out); }
.cat3:hover, .cat3:focus-visible { transform: perspective(700px) rotateX(9deg) rotateY(-9deg) translateY(-4px); box-shadow: 8px 18px 30px rgba(22,22,42,.14); }
.cat3-name { font-size: 20px; font-weight: 800; line-height: 1.15; max-width: 70%; }
.cat3-n { font-size: 14px; font-weight: 700; color: #1668D8; opacity: 0; transform: translateY(6px); transition: opacity 220ms var(--e-out), transform 220ms var(--e-out); }
.cat3:hover .cat3-n, .cat3:focus-visible .cat3-n { opacity: 1; transform: none; }
@media (hover: none) { .cat3-n { opacity: 1; transform: none; } }
.cat3-ic { position: absolute; right: 12px; bottom: 8px; color: #45455E; transition: transform 500ms var(--e-out); transform-origin: 80% 80%; }
.cat3:hover .cat3-ic { transform: scale(1.14) rotate(-6deg); }

/* Onglets pilule */
.tabs3 { position: relative; display: flex; width: 360px; max-width: 100%; padding: 4px; border-radius: 999px; background: var(--b-50); }
.tabbtn3 { position: relative; z-index: 1; flex: 1; min-height: 44px; border: 0; background: transparent; font: 700 15px/1 'Plus Jakarta Sans', sans-serif; color: var(--ink-3); cursor: pointer; transition: color 200ms var(--e-out); border-radius: 999px; }
.tabbtn3[aria-selected="true"] { color: #fff; }
.tabs3-ind { position: absolute; left: 4px; top: 4px; bottom: 4px; width: calc(50% - 4px); border-radius: 999px; background: var(--b-btn); transition: transform 340ms var(--e-io); }
.tabs3.is-best .tabs3-ind { transform: translateX(100%); }
.tabpanel3:not([hidden]) > * { animation: rise3 450ms var(--e-out) backwards; }
.tabpanel3:not([hidden]) > *:nth-child(2) { animation-delay: 60ms; } .tabpanel3:not([hidden]) > *:nth-child(3) { animation-delay: 120ms; } .tabpanel3:not([hidden]) > *:nth-child(4) { animation-delay: 180ms; }

/* Bandeaux */
.band3-deep { position: relative; background: var(--deep); color: #EEF3FB; overflow: hidden; }
.band3-amber { position: relative; background: var(--y-50); overflow: hidden; }
.band3-amber-sphere { position: absolute; right: -200px; top: -120px; width: 620px; height: 620px; border-radius: 50%; background: var(--y); opacity: .5; }
.g2-3 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 56px; align-items: center; }

/* 02 Livraison */
.dlv3 { display: grid; grid-template-columns: minmax(0, 1fr) 480px 250px; gap: 40px; align-items: center; }
.dlv3-feats { list-style: none; margin: 8px 0 0; padding: 0; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
.feat3-d { display: flex; flex-direction: column; gap: 8px; font-size: 14px; font-weight: 700; color: #C5D3E8; line-height: 1.3; }
.feat3-ic-d { width: 46px; height: 46px; border-radius: 14px; background: var(--deep-2); color: var(--sky); display: flex; align-items: center; justify-content: center; transition: transform 260ms var(--e-spring); }
.map3 { position: relative; width: 480px; max-width: 100%; height: 360px; border-radius: 28px; background: var(--deep-2); overflow: hidden; perspective: 900px; perspective-origin: 50% 25%; view-timeline-name: --map3; }
.iso3 { position: absolute; left: 0; top: 0; width: 480px; height: 360px; transform-style: preserve-3d; transform: rotateX(55deg) rotateZ(20deg) scale(1.08); }
.bb3 { position: absolute; transform-style: preserve-3d; }
.bb3-t { position: absolute; inset: 0; border-radius: 3px; background: #2B5A9E; }
.bb3-f { position: absolute; left: 0; transform-origin: 50% 0; transform: rotateX(90deg); background: #12305C; }
.bb3-r { position: absolute; top: 0; transform-origin: 0 50%; transform: rotateY(90deg); background: #1A427C; }
.stand3 { position: absolute; transform-origin: 50% 100%; transform: rotateZ(-20deg) rotateX(-55deg); }
.rider3 { left: 0; top: 0; offset-rotate: 0deg; offset-distance: 100%; width: 38px; height: 38px; border-radius: 999px; background: #F59E0B; color: #16162A;
          display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(0,0,0,.35); }
.pulse3 { position: absolute; width: 32px; height: 32px; border-radius: 999px; background: #4A9BFF; animation: pulse3 1.8s var(--e-out) infinite; }
.map3-card { position: absolute; left: 20px; top: 20px; width: 220px; padding: 14px; border-radius: 16px; background: #fff; color: #16162A; box-shadow: 0 16px 32px rgba(0,0,0,.3); }
.map3-card-hd { display: flex; align-items: center; gap: 10px; }
.map3-card-hd > span { width: 34px; height: 34px; border-radius: 10px; background: #EAF2FF; color: #1668D8; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.map3-card-hd strong { display: block; font-weight: 800; font-size: 14px; }
.map3-card-hd em { font-style: normal; font-size: 13px; font-weight: 700; color: #1668D8; }
.map3-card-ft { margin-top: 10px; padding-top: 10px; border-top: 1px solid #E3E8F2; font-size: 13px; color: #62627C; }
.map3-card-ft strong { color: #16162A; }
.dlv3-ph { width: 250px; height: 470px; border-radius: 38px; background: #000; padding: 9px; box-shadow: 0 30px 60px rgba(0,0,0,.45); }
.dlv3-ph-scr { height: 100%; border-radius: 30px; background: #F6F9FF; color: #16162A; padding: 36px 16px 16px; display: flex; flex-direction: column; gap: 12px; }
.track3 { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.track3 li { display: flex; gap: 10px; align-items: flex-start; }
.track3 li > span { width: 26px; height: 26px; border-radius: 999px; flex-shrink: 0; background: #fff; color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: inset 0 0 0 2px #C9D2DF; }
.track3 li > span svg { opacity: 0; }
.track3 li.done > span, .track3 li.cur > span { background: #1668D8; box-shadow: inset 0 0 0 2px #1668D8; }
.track3 li.done > span svg, .track3 li.cur > span svg { opacity: 1; }
.track3 li.cur > span { animation: stepin3 400ms var(--e-spring) both; }
.track3 li div { flex: 1; min-width: 0; padding-top: 2px; }
.track3 b { display: block; font-size: 13px; color: #62627C; }
.track3 li.done b, .track3 li.cur b { color: #16162A; }
.track3 small { font-size: 12px; color: #62627C; }
.dlv3-pay { margin-top: auto; padding: 10px; border-radius: 12px; background: #EAF2FF; font-size: 12px; font-weight: 700; color: #1668D8; }

/* 03 Paiements */
.pay3-g { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); gap: 48px; align-items: center; }
.pay3 { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; padding: 22px; border-radius: 22px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--line);
        transition: transform 240ms var(--e-out), box-shadow 240ms var(--e-out); }
.pay3:hover { transform: perspective(800px) rotateX(10deg) rotateY(-10deg) translateY(-4px); box-shadow: inset 0 0 0 1.5px var(--b), 0 14px 28px rgba(22,22,42,.08); }
.pay3-def { box-shadow: inset 0 0 0 2px var(--b-btn); }
.pay3 strong { font-size: 17px; line-height: 1.25; color: var(--ink); }
.pay3 > span:not(.pay3-ic):not(.b3) { font-size: 14px; color: var(--ink-2); }
.pay3-ic { width: 52px; height: 52px; border-radius: 16px; background: #F1F4FA; color: #16162A; display: flex; align-items: center; justify-content: center; }
.pay3-ic-def { background: var(--b-btn); color: #fff; }
.shield3 { width: 96px; height: 96px; border-radius: 28px; background: var(--b-btn); color: #fff; display: flex; align-items: center; justify-content: center;
           box-shadow: inset -8px -10px 18px rgba(11,30,61,.35), inset 6px 6px 12px rgba(255,255,255,.2), 0 16px 32px rgba(22,104,216,.3); }

/* 04 Vendeurs */
.checks3 { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.checks3 li { display: flex; gap: 12px; align-items: center; font-weight: 600; color: var(--ink); }
.checks3 li > span { width: 28px; height: 28px; border-radius: 999px; background: var(--b-btn); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.checks3-d li { color: #fff; }
.checks3-d li > span { background: rgba(125,180,255,.18); color: var(--sky); }
.lap3d { transform: perspective(1200px) rotateY(-12deg) rotateX(5deg); min-width: 0; }
.dash3-frame { border-radius: 18px 18px 0 0; background: #1A2333; padding: 10px; }
.dash3 { border-radius: 10px; background: #F6F9FF; overflow: hidden; display: grid; grid-template-columns: 120px minmax(0, 1fr); color: #16162A; font-size: 12px; }
.dash3-side { background: #fff; border-right: 1px solid #E3E8F2; padding: 12px 8px; display: flex; flex-direction: column; gap: 4px; }
.dash3-side > span { padding: 6px 8px; }
.dash3-side > span.on { border-radius: 8px; background: #1668D8; color: #fff; font-weight: 700; }
.dash3-main { padding: 14px; display: flex; flex-direction: column; gap: 12px; min-width: 0; }
.dash3-hd { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.dash3-hd strong { font-size: 14px; }
.dash3-hd span { font-size: 10px; font-weight: 700; color: #45455E; background: #EEF2F8; padding: 3px 8px; border-radius: 999px; white-space: nowrap; }
.dash3-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
.dash3-kpis div { padding: 10px; border-radius: 10px; background: #fff; box-shadow: 0 0 0 1px #E3E8F2; min-width: 0; }
.dash3-kpis small { display: block; font-size: 10px; color: #62627C; }
.dash3-kpis b { display: block; font-size: 15px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dash3-kpis b[data-gnf] { font-size: 12px; }
.dash3-chart { padding: 10px; border-radius: 10px; background: #fff; box-shadow: 0 0 0 1px #E3E8F2; }
.dash3-row { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; background: #fff; }
.dash3-row.new { box-shadow: 0 0 0 1.5px #1F7CF6; }
.dash3-row i { width: 8px; height: 8px; border-radius: 999px; background: #1F7CF6; flex-shrink: 0; }
.dash3-row i.y { background: #F59E0B; }
.dash3-row > strong, .dash3-row > span:not(.ok):not(.go) { flex: 1; min-width: 0; }
.dash3-row .ok, .dash3-row .go { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #EAF2FF; color: #1668D8; }
.dash3-row .go { background: #FFF4DB; color: #16162A; }
.dash3-row b { font-weight: 800; white-space: nowrap; }
.dash3-base { height: 14px; margin-inline: -24px; border-radius: 0 0 14px 14px; background: linear-gradient(#CDD3DC, #9EA7B4); }

/* Témoignages */
.rev3 { margin: 0; padding: 28px; border-radius: 24px; background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--line); display: flex; flex-direction: column; gap: 16px; color: var(--ink); }
.rev3-hl { background: var(--b-btn); color: #fff; box-shadow: none; }
.rev3-stars { display: flex; gap: 3px; color: #F59E0B; }
.rev3-stars .star-ico.is-empty { color: rgba(255,255,255,.35); }
.rev3 blockquote { margin: 0; font-size: 20px; line-height: 1.4; font-weight: 700; }
.rev3 figcaption { display: flex; align-items: center; gap: 12px; margin-top: auto; }
.rev3 figcaption small { display: block; font-size: 14px; opacity: .8; }
.rev3-av { width: 44px; height: 44px; border-radius: 999px; background: #F59E0B; color: #16162A; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }

/* 05 Application */
.app3-vis { display: flex; justify-content: center; position: relative; }
.app3-disc { position: absolute; width: 420px; max-width: 100%; aspect-ratio: 1; border-radius: 50%; background: var(--deep-2); top: 30px; }
.phone3d { transform: perspective(1200px) rotateY(-10deg) rotateX(3deg); }
.app3-phone { position: relative; width: 270px; height: 540px; border-radius: 44px; background: #000; padding: 10px; box-shadow: 0 30px 60px rgba(0,0,0,.5); }
.app3-scr { position: relative; height: 100%; border-radius: 35px; background: #F6F9FF; color: #16162A; overflow: hidden; padding: 46px 16px 16px; display: flex; flex-direction: column; gap: 12px; }
.notif3 { position: absolute; left: 10px; right: 10px; top: 10px; padding: 10px; border-radius: 16px; background: #fff; box-shadow: 0 10px 24px rgba(22,22,42,.18); display: flex; gap: 8px; align-items: center;
          animation: notif3 6s var(--e-io) 1s infinite both; }
.notif3 > span { width: 30px; height: 30px; border-radius: 9px; background: #EAF2FF; display: flex; align-items: center; justify-content: center; }
.notif3 img { width: 20px; height: 20px; border-radius: 5px; }
.notif3 b { display: block; font-size: 12px; }
.notif3 small { font-size: 12px; color: #45455E; }
.app3-search { display: block; height: 44px; border-radius: 999px; background: #fff; box-shadow: 0 0 0 1px #E3E8F2; }
.app3-tiles { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.app3-tiles i { display: block; height: 110px; border-radius: 16px; background: #EAF2FF; }
.app3-tabs { margin-top: auto; display: flex; justify-content: space-around; padding-top: 10px; border-top: 1px solid #E3E8F2; }
.app3-tabs i { width: 22px; height: 22px; border-radius: 6px; background: #CDD5E2; }
.app3-tabs i.on { background: #1668D8; }

/* 06 Appel final */
.cta3 { display: flex; align-items: center; justify-content: space-between; gap: 32px; flex-wrap: wrap; padding: 48px 56px; border-radius: 36px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--line); }
.cta3-art { position: relative; width: 300px; height: 220px; max-width: 100%; }
.cta3-shadow { position: absolute; left: 70px; top: 196px; width: 170px; height: 18px; border-radius: 50%; background: rgba(11,30,61,.18); }
.cta3-bagwrap { position: absolute; left: 85px; top: 48px; perspective: 700px; }
.bag3d { position: relative; width: 130px; height: 150px; transform-style: preserve-3d; transform: rotateY(-30deg) rotateX(-12deg); transition: transform 700ms var(--e-spring); }
.cta3d:hover .bag3d { transform: rotateY(24deg) rotateX(-12deg); }
.bag3-top { position: absolute; left: 0; top: 0; width: 130px; height: 46px; transform-origin: 50% 0; transform: rotateX(-90deg); background: #3B8DF8; }
.bag3-side { position: absolute; left: 130px; top: 0; width: 46px; height: 150px; transform-origin: 0 50%; transform: rotateY(90deg); background: #1260C9; border-radius: 0 10px 18px 0; }
.bag3-front { position: absolute; inset: 0; border-radius: 10px 10px 18px 18px; background: #1F7CF6; box-shadow: inset -10px -14px 24px rgba(11,30,61,.25); display: flex; align-items: center; justify-content: center; }
.cta3-dot { position: absolute; width: 48px; height: 48px; border-radius: 999px; display: flex; align-items: center; justify-content: center; }

/* ── Animations ── */
@keyframes rise3 { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
@keyframes mask3 { from { clip-path: inset(0 0 100% 0); transform: translateY(30px); } to { clip-path: inset(0 0 0 0); transform: none; } }
@keyframes blob3 { from { opacity: 0; transform: scale(.6); } to { opacity: 1; transform: none; } }
@keyframes lap3 { from { opacity: 0; transform: translateY(40px) scale(.96); } to { opacity: 1; transform: none; } }
@keyframes phn3 { from { opacity: 0; transform: translateX(60px) rotate(6deg); } to { opacity: 1; transform: rotate(0); } }
@keyframes lid3 { from { transform: rotateX(-100deg); } to { transform: rotateX(0); } }
@keyframes tick3 { from { opacity: .15; transform: translateY(-35%); } to { opacity: 1; transform: none; } }
@keyframes heart3 { 0% { transform: scale(.6); } 60% { transform: scale(1.25); } 100% { transform: scale(1); } }
@keyframes grow3 { from { transform: scaleX(0); } to { transform: scaleX(1); } }
@keyframes draw3 { from { stroke-dashoffset: 1; } to { stroke-dashoffset: 0; } }
@keyframes along3 { from { offset-distance: 0%; } to { offset-distance: 100%; } }
@keyframes pulse3 { 0% { transform: scale(.6); opacity: .7; } 100% { transform: scale(2.2); opacity: 0; } }
@keyframes notif3 { 0%, 8% { opacity: 0; transform: translateY(-16px); } 14%, 80% { opacity: 1; transform: none; } 90%, 100% { opacity: 0; transform: translateY(-16px); } }
@keyframes float3 { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
@keyframes stepin3 { from { opacity: .4; transform: scale(.7); } to { opacity: 1; transform: none; } }
.in3 { animation: rise3 650ms var(--e-out) backwards; }
.in3-mask { animation: mask3 750ms var(--e-out) backwards; }
.d3-1 { animation-delay: 80ms; } .d3-2 { animation-delay: 160ms; } .d3-3 { animation-delay: 240ms; } .d3-4 { animation-delay: 320ms; } .d3-5 { animation-delay: 400ms; }
.blob3 { animation: blob3 900ms var(--e-out) both; }
.lap3 { animation: lap3 800ms var(--e-out) 150ms both; }
.phn3 { animation: phn3 800ms var(--e-out) 350ms both; }
.float3 { animation: float3 4s var(--e-io) infinite; }

/* Apparitions au scroll : timeline CSS si supportée, sinon classe .is-in posée par l'IntersectionObserver.
   Sans JS ni support : tout reste visible. */
@supports (animation-timeline: view()) {
    .rv3 { animation: rise3 linear backwards; animation-timeline: view(); animation-range: entry 5% entry 45%; }
    .rv3-2 { animation-range: entry 12% entry 52%; } .rv3-3 { animation-range: entry 19% entry 59%; } .rv3-4 { animation-range: entry 26% entry 66%; }
    .rv3-line { animation: grow3 linear both; animation-timeline: view(); animation-range: entry 30% cover 50%; }
    .flip3d { animation: flip3 linear backwards; animation-timeline: view(); animation-range: entry 20% entry 70%; }
    @keyframes flip3 { from { transform: perspective(500px) rotateY(-180deg) scale(.8); } to { transform: perspective(500px) rotateY(0); } }
    .coin3d { animation: coin3 linear both; animation-timeline: view(); animation-range: entry 10% cover 60%; }
    @keyframes coin3 { from { transform: perspective(600px) rotateY(-360deg); } to { transform: perspective(600px) rotateY(0); } }
    .phone3d { animation: ph3 linear both; animation-timeline: view(); animation-range: entry 10% cover 50%; }
    @keyframes ph3 { from { transform: perspective(1200px) rotateY(-42deg) rotateX(12deg) translateY(40px); } to { transform: perspective(1200px) rotateY(-10deg) rotateX(3deg); } }
    .lap3d { animation: lp3 linear both; animation-timeline: view(); animation-range: entry 10% cover 45%; }
    @keyframes lp3 { from { transform: perspective(1200px) rotateY(-32deg) rotateX(14deg) translateY(40px); } to { transform: perspective(1200px) rotateY(-12deg) rotateX(5deg); } }
    .isoin3 { animation: isoin3 linear both; animation-timeline: --map3; animation-range: entry 10% cover 45%; }
    @keyframes isoin3 { from { transform: rotateX(15deg) rotateZ(0deg) scale(.92); } to { transform: rotateX(55deg) rotateZ(20deg) scale(1.08); } }
    .route3 { stroke-dasharray: 1; animation: draw3 linear both; animation-timeline: --map3; animation-range: entry 40% cover 55%; }
    .rider3 { animation: along3 linear both; animation-timeline: --map3; animation-range: entry 40% cover 55%; }
    .chartline3 { stroke-dasharray: 1; animation: draw3 linear both; animation-timeline: view(); animation-range: entry 20% cover 45%; }
    .par3 { animation: par3 linear both; animation-timeline: scroll(); animation-range: 0 700px; }
    @keyframes par3 { to { transform: translateY(-50px); } }
    /* Mobile : carrousel des ventes flash en coverflow */
    @media (max-width: 860px) {
        .rail3 > .c3 { animation: cover3 linear both; animation-timeline: view(inline); }
        @keyframes cover3 { 0% { transform: perspective(900px) rotateY(45deg) scale(.9); } 35%, 65% { transform: perspective(900px) rotateY(0) scale(1); } 100% { transform: perspective(900px) rotateY(-45deg) scale(.9); } }
    }
}
/* Repli sans scroll-timeline : apparition simple, déclenchée en JS */
@supports not (animation-timeline: view()) {
    .sp3-js .rv3:not(.is-in) { opacity: 0; transform: translateY(18px); }
    .sp3-js .rv3.is-in { animation: rise3 600ms var(--e-out) backwards; }
    .sp3-js .rv3-line:not(.is-in) { transform: scaleX(0); }
    .sp3-js .rv3-line.is-in { transform: scaleX(1); transition: transform 900ms var(--e-io); }
}

/* ── Responsive ── */
@media (max-width: 1100px) { .dlv3 { grid-template-columns: minmax(0, 1fr) 480px; } .dlv3-ph { display: none !important; } }
@media (max-width: 1024px) { .g4-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .h3-title { font-size: 52px; } }
@media (max-width: 860px) {
    .w3 { padding-inline: 16px; }
    .sec3 { padding-block: 64px; }
    .h3-g, .g2-3, .g3-3, .dlv3, .pay3-g { grid-template-columns: minmax(0, 1fr) !important; }
    .hide-m3 { display: none !important; }
    .h3-title { font-size: 38px; }
    .h2-3 { font-size: 30px !important; }
    .h3-sub { font-size: 17px; }
    .feats3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .h3-cta .btn3 { flex: 1 1 100%; }
    .scene3-wrap { height: calc(580px * var(--fit, .6)); }
    .dlv3-feats { grid-template-columns: minmax(0, 1fr); }
    .feat3-d { flex-direction: row; align-items: center; font-size: 15px; }
    .rail3 { grid-auto-columns: 78%; }
    .cta3 { padding: 32px 20px; }
    .cta3 .h2-3 { font-size: 28px !important; }
    .tab3 { width: 52px; height: 46px; font-size: 17px; }
    .dash3 { grid-template-columns: minmax(0, 1fr); }
    .dash3-side { display: none; }
    .dash3-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 420px) { .count3 { width: 100%; justify-content: space-between; } .count3-d > span { min-width: 42px; height: 42px; } .count3-d { font-size: 20px; } }

/* ── Mouvement réduit : scènes fixes, aucune rotation, simples fondus ── */
@media (prefers-reduced-motion: reduce) {
    .sp3 *, .sp3 *::before, .sp3 *::after { animation: none !important; transition-duration: 1ms !important; }
    .stage3 { transform: rotateX(6deg) rotateY(-14deg) !important; }
    .c3, .cat3:hover, .pay3:hover, .cta3d:hover .bag3d { transform: none !important; }
    .notif3 { opacity: 1; }
    .phone3-slide { transition: opacity 150ms linear !important; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';
    const root = document.getElementById('sp3');
    if (!root) return;
    document.documentElement.classList.add('sp3-js');
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const fine = window.matchMedia('(pointer: fine)').matches;

    /* ── Scène du hero : pivote selon la souris (±10° / ±20°), au doigt sur mobile ── */
    const hero = document.getElementById('hero3d');
    const stage = document.getElementById('stage3');
    if (hero && stage && !reduce) {
        let raf = 0;
        const move = (x, y) => {
            cancelAnimationFrame(raf);
            raf = requestAnimationFrame(() => {
                stage.style.setProperty('--rx', (6 - (y - 0.5) * 10).toFixed(2) + 'deg');
                stage.style.setProperty('--ry', (-14 + (x - 0.5) * 22).toFixed(2) + 'deg');
            });
        };
        const reset = () => { stage.style.removeProperty('--rx'); stage.style.removeProperty('--ry'); };
        hero.addEventListener('pointermove', e => {
            const r = hero.getBoundingClientRect();
            move((e.clientX - r.left) / r.width, (e.clientY - r.top) / r.height);
        }, { passive: true });
        hero.addEventListener('pointerleave', reset);
    }

    /* ── Mise à l'échelle de la scène pour qu'elle tienne dans sa colonne ── */
    const wrap = root.querySelector('.scene3-wrap');
    const fitScene = () => {
        if (!wrap || !wrap.clientWidth) return; // page encore masquée par le loader : on attend
        const s = Math.min(1, wrap.clientWidth / 620);
        wrap.style.setProperty('--fit', s.toFixed(3));
    };
    fitScene();
    window.addEventListener('resize', fitScene, { passive: true });
    if (wrap && 'ResizeObserver' in window) new ResizeObserver(fitScene).observe(wrap);

    /* ── Téléphone du hero : défilement des vrais produits ── */
    const slides = Array.from(root.querySelectorAll('.phone3-slide'));
    if (slides.length > 1 && !reduce) {
        let i = 0;
        setInterval(() => {
            if (document.hidden) return;
            slides[i].classList.remove('on');
            i = (i + 1) % slides.length;
            slides[i].classList.add('on');
        }, 2800);
    }

    /* ── Cartes produits : inclinaison ±8° qui suit la souris + reflet ── */
    if (fine && !reduce) {
        root.addEventListener('pointermove', e => {
            const c = e.target.closest('.tilt3d');
            if (!c) return;
            const r = c.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
            c.style.setProperty('--crx', ((0.5 - y) * 14).toFixed(2) + 'deg');
            c.style.setProperty('--cry', ((x - 0.5) * 16).toFixed(2) + 'deg');
            c.style.setProperty('--gx', (x * 100).toFixed(0) + '%');
            c.style.setProperty('--gy', (y * 100).toFixed(0) + '%');
            c.classList.add('is-tilt');
        }, { passive: true });
        root.addEventListener('pointerout', e => {
            const c = e.target.closest('.tilt3d');
            if (!c || c.contains(e.relatedTarget)) return;
            ['--crx', '--cry'].forEach(p => c.style.removeProperty(p));
            c.classList.remove('is-tilt');
        });
    }

    /* ── Compte à rebours des ventes flash (chiffres qui basculent) ── */
    const cd = document.getElementById('flash3Count');
    if (cd) {
        let left = parseInt(cd.dataset.seconds, 10) || 0;
        const pad = n => String(n).padStart(2, '0');
        const h = cd.querySelector('[data-h]'), m = cd.querySelector('[data-m]'), s = cd.querySelector('[data-s]'), sr = cd.querySelector('[data-sr]');
        const paint = () => {
            const hh = Math.floor(left / 3600), mm = Math.floor(left % 3600 / 60), ss = left % 60;
            h.textContent = pad(hh); m.textContent = pad(mm); s.textContent = pad(ss);
            if (!reduce) { s.classList.remove('tick3'); void s.offsetWidth; s.classList.add('tick3'); }
            sr.textContent = 'Se termine dans ' + hh + ' h ' + mm + ' min';
        };
        paint();
        setInterval(() => { if (left > 0) { left--; paint(); } }, 1000);
    }

    /* ── Onglets « Notre sélection » : indicateur pilule qui glisse ── */
    const tabs = root.querySelector('.tabs3');
    if (tabs) {
        const btns = Array.from(tabs.querySelectorAll('[role="tab"]'));
        const select = (b, focus) => {
            btns.forEach(x => {
                const on = x === b;
                x.setAttribute('aria-selected', on); x.tabIndex = on ? 0 : -1;
                const p = document.getElementById(x.getAttribute('aria-controls'));
                if (p) p.hidden = !on;
            });
            tabs.classList.toggle('is-best', b === btns[1]);
            if (focus) b.focus();
        };
        btns.forEach((b, i) => {
            b.addEventListener('click', () => select(b));
            b.addEventListener('keydown', e => {
                if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') { e.preventDefault(); select(btns[(i + 1) % btns.length], true); }
            });
        });
    }

    /* ── Suivi de livraison : les 4 étapes avancent (pause hors écran / mouvement réduit) ── */
    const track = document.getElementById('track3');
    const label = root.ownerDocument.querySelector('[data-dlv-label]');
    if (track && !reduce) {
        const items = Array.from(track.children);
        let step = 2, visible = false;
        if ('IntersectionObserver' in window) new IntersectionObserver(([e]) => { visible = e.isIntersecting; }).observe(track);
        setInterval(() => {
            if (!visible || document.hidden) return;
            step = (step + 1) % items.length;
            items.forEach((li, k) => { li.classList.toggle('done', k < step); li.classList.toggle('cur', k === step); });
            if (label) label.textContent = items[step].querySelector('b').textContent;
        }, 2200);
    }

    /* ── Repli des apparitions + compteurs de démonstration (montent à l'apparition) ── */
    const kpis = document.querySelectorAll('#kpi3 [data-kpi]');
    const fmt = n => String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    const runKpis = () => kpis.forEach(el => {
        const target = +el.dataset.kpi, gnf = el.hasAttribute('data-gnf');
        if (reduce) { el.textContent = fmt(target) + (gnf ? ' GNF' : ''); return; }
        const t0 = performance.now();
        const step = t => { const k = Math.min(1, (t - t0) / 1000), e = 1 - Math.pow(1 - k, 3); el.textContent = fmt(target * e) + (gnf ? ' GNF' : ''); if (k < 1) requestAnimationFrame(step); };
        requestAnimationFrame(step);
    });
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver(entries => entries.forEach(en => {
            if (!en.isIntersecting) return;
            en.target.classList.add('is-in');
            if (en.target.id === 'kpi3') runKpis();
            io.unobserve(en.target);
        }), { threshold: 0.15 });
        document.querySelectorAll('.sp3 .rv3, .sp3 .rv3-line, #kpi3').forEach(el => io.observe(el));
    } else {
        document.querySelectorAll('.sp3 .rv3, .sp3 .rv3-line').forEach(el => el.classList.add('is-in'));
        runKpis();
    }
})();
</script>
@endpush
