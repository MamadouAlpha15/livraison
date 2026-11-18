{{--
    resources/views/landing.blade.php
    Route     : GET / → LandingController@index → name('landing')
    Rôle      : Page d'accueil de Shopio pour les vendeurs, avec de petites démos animées des fonctionnalités :
                création de boutique en 8 étapes, produit avec Shopio IA, page produit en sections, commandes,
                paniers abandonnés, thèmes. Le marché des clients est à /marche (route « welcome »).
                Les démos sont jouées en HTML/CSS (aucune vidéo) et s'arrêtent hors de l'écran.
    Variables : $stats (null si trop peu de boutiques), $trialDays, $cta, $ctaLabel, $user
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Shopio · Ta boutique en ligne, payée à la livraison</title>
<meta name="description" content="Crée ta boutique en ligne en quelques minutes : pages produit qui vendent, commandes payées à la livraison, relance des paniers abandonnés et livreurs. Fait pour la Guinée et l'Afrique.">
<link rel="icon" href="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}">
<meta property="og:title" content="Shopio · Ta boutique en ligne, payée à la livraison">
<meta property="og:description" content="Crée ta boutique en quelques minutes et vends partout, paiement à la livraison.">
<meta property="og:image" content="{{ asset('images/shopio-logo-512.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --ink: #0F172A; --ink-2: #475569; --ink-3: #94A3B8; --line: #E2E8F0; --soft: #F8FAFC; --soft-2: #F1F5F9;
    --accent: #1F7CF6; --accent-dk: #1667D6; --accent-50: #EFF6FF; --accent-100: #DBEAFE;
    --ok: #16A34A; --ok-50: #F0FDF4; --warn: #B45309; --warn-50: #FFFBEB; --err: #DC2626;
    --r: 18px; --font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --shadow: 0 1px 2px rgba(15,23,42,.04), 0 12px 32px -12px rgba(15,23,42,.18);
}
*, *::before, *::after { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body { margin: 0; background: #fff; color: var(--ink); font-family: var(--font); -webkit-font-smoothing: antialiased; line-height: 1.55; overflow-x: hidden; }
img, svg { display: block; }
a { color: inherit; }
.wrap { width: 100%; max-width: 1160px; margin: 0 auto; padding: 0 20px; }
.sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
:focus-visible { outline: 3px solid #93C5FD; outline-offset: 2px; border-radius: 6px; }

/* ── Boutons ── */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 48px; padding: 0 22px; border: 0; border-radius: 12px; font: 700 15px var(--font); text-decoration: none; cursor: pointer; transition: background-color .15s, box-shadow .15s, transform .1s; white-space: nowrap; }
.btn:active { transform: scale(.98); }
.btn-main { background: var(--accent); color: #fff; box-shadow: 0 8px 20px -8px rgba(31,124,246,.6); }
.btn-main:hover { background: var(--accent-dk); }
.btn-line { background: #fff; color: var(--ink); box-shadow: inset 0 0 0 1.5px var(--line); }
.btn-line:hover { box-shadow: inset 0 0 0 1.5px var(--ink-3); }
.btn-sm { min-height: 40px; padding: 0 16px; font-size: 14px; border-radius: 10px; }

/* ── En-tête ── */
.nav { position: sticky; top: 0; z-index: 50; background: rgba(255,255,255,.86); backdrop-filter: saturate(1.4) blur(12px); -webkit-backdrop-filter: saturate(1.4) blur(12px); border-bottom: 1px solid transparent; transition: border-color .2s; }
.nav.is-scrolled { border-bottom-color: var(--line); }
.nav .wrap { display: flex; align-items: center; gap: 20px; height: 68px; }
.logo { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 19px; text-decoration: none; letter-spacing: -.02em; }
.logo img { width: 34px; height: 34px; border-radius: 9px; }
.nav-links { display: flex; gap: 4px; margin-left: 12px; }
.nav-links a { padding: 8px 12px; border-radius: 9px; color: var(--ink-2); font-weight: 600; font-size: 14.5px; text-decoration: none; }
.nav-links a:hover { background: var(--soft-2); color: var(--ink); }
.nav-end { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.nav-login { padding: 8px 12px; color: var(--ink-2); font-weight: 700; font-size: 14.5px; text-decoration: none; }
.nav-login:hover { color: var(--ink); }
.nav-burger { display: none; width: 42px; height: 42px; border: 0; border-radius: 10px; background: var(--soft-2); cursor: pointer; place-items: center; }
.nav-menu { display: none; }
@media (max-width: 1020px) {
    .nav-links, .nav-login { display: none; }
    .nav-burger { display: grid; }
    .nav-menu.is-open { display: grid; gap: 2px; padding: 8px 20px 16px; border-bottom: 1px solid var(--line); background: #fff; }
    .nav-menu a { padding: 12px 4px; font-weight: 700; text-decoration: none; border-bottom: 1px solid var(--soft-2); }
}
@media (max-width: 420px) { .nav-end .btn { padding: 0 12px; font-size: 13.5px; } }
/* Petits téléphones (320–380 px) : barre du haut plus serrée, logo sans le nom sous 360 px */
@media (max-width: 380px) { .nav .wrap { gap: 10px; padding: 0 14px; } .nav-end { gap: 6px; } .nav-end .btn { padding: 0 10px; font-size: 12.5px; } .nav-burger { width: 38px; height: 38px; } }
@media (max-width: 360px) { .logo { font-size: 0; gap: 0; } }

/* ── Héros ── */
.hero { padding: 56px 0 40px; background: radial-gradient(1200px 500px at 70% -10%, var(--accent-50), transparent 70%); }
.hero .wrap { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr); gap: 48px; align-items: center; }
.pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px 6px 6px; border-radius: 999px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); font-size: 13px; font-weight: 700; color: var(--ink-2); }
.pill b { padding: 3px 9px; border-radius: 999px; background: var(--accent); color: #fff; font-size: 11.5px; }
.hero h1 { margin: 18px 0 16px; font-size: clamp(36px, 5.4vw, 60px); line-height: 1.04; font-weight: 800; letter-spacing: -.035em; text-wrap: balance; }
.hero h1 em { font-style: normal; color: var(--accent); }
.hero-sub { margin: 0 0 26px; max-width: 540px; font-size: clamp(16px, 1.6vw, 18.5px); color: var(--ink-2); }
.hero-cta { display: flex; flex-wrap: wrap; gap: 10px; }
.hero-proof { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 22px 0 0; padding: 0; list-style: none; color: var(--ink-2); font-size: 14px; font-weight: 600; }
.hero-proof li { display: flex; align-items: center; gap: 7px; }
.hero-proof svg { color: var(--ok); }

/* Téléphone de démonstration */
.stage { position: relative; display: grid; place-items: center; min-height: 560px; }
.phone { position: relative; width: 300px; height: 600px; padding: 12px; border-radius: 46px; background: #0F172A; box-shadow: 0 40px 80px -30px rgba(15,23,42,.45), inset 0 0 0 2px #1E293B; }
.phone::before { content: ''; position: absolute; top: 20px; left: 50%; width: 92px; height: 24px; margin-left: -46px; border-radius: 999px; background: #0F172A; z-index: 3; }
.screen { position: relative; height: 100%; overflow: hidden; border-radius: 35px; background: #fff; }
.ps-top { display: flex; align-items: center; gap: 8px; padding: 46px 16px 10px; border-bottom: 1px solid var(--soft-2); }
.ps-av { width: 28px; height: 28px; border-radius: 8px; background: var(--ink); color: #fff; display: grid; place-items: center; font-size: 11px; font-weight: 800; }
.ps-top b { font-size: 13px; } .ps-top small { display: block; font-size: 10.5px; color: var(--ink-3); font-weight: 600; }
.ps-body { padding: 12px 14px; display: grid; gap: 10px; transition: transform .7s cubic-bezier(.22,1,.36,1); }
.ps-img { height: 168px; border-radius: 16px; background: linear-gradient(160deg, #F5EFE6, #E9DFD0); display: grid; place-items: center; position: relative; }
.ps-img .tag { position: absolute; top: 10px; left: 10px; padding: 3px 8px; border-radius: 999px; background: #fff; font-size: 10px; font-weight: 800; color: var(--err); }
.ps-name { margin: 0; font-size: 15px; font-weight: 800; letter-spacing: -.01em; }
.ps-price { display: flex; align-items: baseline; gap: 8px; font-weight: 800; font-size: 15px; }
.ps-price s { color: var(--ink-3); font-size: 12px; font-weight: 600; }
.ps-stars { font-size: 11px; color: var(--ink-2); font-weight: 600; }
.ps-packs { display: grid; gap: 6px; }
.ps-pack { display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border-radius: 10px; box-shadow: inset 0 0 0 1.5px var(--line); font-size: 11.5px; font-weight: 700; transition: box-shadow .25s, background-color .25s; }
.ps-pack i { font-style: normal; font-size: 10px; padding: 2px 6px; border-radius: 999px; background: var(--ok-50); color: var(--ok); }
.ps-pack.is-on { box-shadow: inset 0 0 0 2px var(--accent); background: var(--accent-50); }
.ps-form { display: grid; gap: 6px; }
.ps-field { height: 34px; padding: 0 10px; border-radius: 9px; box-shadow: inset 0 0 0 1.5px var(--line); display: flex; align-items: center; font-size: 11.5px; font-weight: 600; color: var(--ink-3); transition: box-shadow .2s; }
.ps-field.is-typing { box-shadow: inset 0 0 0 2px var(--accent); color: var(--ink); }
.ps-field.has-val { color: var(--ink); }
.ps-buy { height: 40px; border-radius: 11px; background: var(--accent); color: #fff; display: grid; place-items: center; font-weight: 800; font-size: 13px; transition: transform .15s; }
.ps-buy.is-press { transform: scale(.96); }
.ps-done { position: absolute; inset: 0; display: grid; place-items: center; align-content: center; gap: 10px; padding: 24px; background: #fff; text-align: center; opacity: 0; pointer-events: none; transition: opacity .35s; }
.ps-done.is-on { opacity: 1; }
.ps-done .ok { width: 56px; height: 56px; border-radius: 999px; background: var(--ok); color: #fff; display: grid; place-items: center; }
.ps-done b { font-size: 16px; } .ps-done small { color: var(--ink-2); font-size: 12px; }
.cursor { position: absolute; z-index: 5; width: 22px; height: 22px; border-radius: 999px; background: rgba(15,23,42,.18); box-shadow: 0 0 0 2px rgba(255,255,255,.9); pointer-events: none; transition: transform .6s cubic-bezier(.22,1,.36,1), opacity .2s; opacity: 0; }
.cursor.is-tap { animation: tap .35s ease; }
@keyframes tap { 50% { box-shadow: 0 0 0 10px rgba(31,124,246,.18); } }
.toast { position: absolute; z-index: 6; display: flex; align-items: center; gap: 10px; padding: 10px 14px 10px 10px; border-radius: 14px; background: #fff; box-shadow: var(--shadow), inset 0 0 0 1px var(--line); font-size: 12.5px; opacity: 0; transform: translateY(10px) scale(.98); transition: opacity .35s, transform .45s cubic-bezier(.22,1,.36,1); }
.toast.is-on { opacity: 1; transform: none; }
.toast .ti { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; background: var(--soft-2); color: var(--ink); flex-shrink: 0; }
.toast b { display: block; font-size: 12.5px; } .toast small { color: var(--ink-2); font-size: 11.5px; }
.toast-1 { right: -12px; top: 92px; }
.toast-2 { left: -18px; bottom: 120px; }
@media (max-width: 960px) {
    .hero .wrap { grid-template-columns: 1fr; gap: 28px; }
    .stage { min-height: 0; }
    .toast-1 { right: 0; } .toast-2 { left: 0; }
}
@media (max-width: 420px) { .phone { transform: scale(.9); margin: -30px 0; } .toast { transform: scale(.92); } .toast.is-on { transform: scale(.92); } }

/* ── Héros v2 : texte centré, capture produit (tableau de bord + téléphone) ── */
.hero-in { display: grid !important; grid-template-columns: 1fr !important; gap: 46px !important; }
.hero-txt { max-width: 860px; margin: 0 auto; text-align: center; }
.hero-txt .hero-sub { margin-left: auto; margin-right: auto; }
.hero-txt .hero-cta, .hero-txt .hero-proof { justify-content: center; }
.shot { position: relative; width: 100%; max-width: 1080px; margin: 0 auto; padding: 0 170px 40px 0; }
.dash .dash-in { display: grid; grid-template-columns: 170px minmax(0, 1fr); height: 440px; }
.dash-side { background: #0B1E3D; padding: 14px 10px; display: grid; gap: 3px; align-content: start; }
.dash-side .lg { display: flex; align-items: center; gap: 8px; padding: 2px 6px 12px; color: #fff; font-weight: 800; font-size: 12.5px; }
.dash-side .lg i { width: 22px; height: 22px; border-radius: 6px; background: var(--accent); }
.dash-side span { display: flex; align-items: center; gap: 8px; height: 30px; padding: 0 8px; border-radius: 8px; color: #9FB3D3; font-size: 11.5px; font-weight: 700; }
.dash-side span.on { background: var(--accent); color: #fff; }
.dash-side span em { margin-left: auto; font-style: normal; font-size: 10px; padding: 1px 6px; border-radius: 999px; background: #F59E0B; color: #16162A; }
.dash-main { background: var(--soft); padding: 16px 110px 16px 16px; display: grid; gap: 10px; align-content: start; overflow: hidden; }
.dash-hd { display: flex; align-items: center; justify-content: space-between; }
.dash-hd b { font-size: 15px; } .dash-hd small { display: block; color: var(--ink-2); font-size: 11px; font-weight: 600; }
.dash-acts { display: flex; gap: 6px; flex-wrap: wrap; }
.dash-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
.dash-kpi { padding: 10px 12px; }
.dash-kpi small { display: block; font-size: 10.5px; color: var(--ink-2); font-weight: 700; }
.dash-kpi b { display: block; font-size: 17px; letter-spacing: -.02em; margin-top: 2px; font-variant-numeric: tabular-nums; }
.dash-kpi i { font-style: normal; font-size: 10px; font-weight: 800; color: var(--ok); }
.dash-row { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); gap: 8px; }
.dash-chart, .dash-orders { padding: 12px; }
.dash-chart small, .dash-orders small { font-size: 11px; font-weight: 800; color: var(--ink-2); }
.dash-chart svg { width: 100%; height: 150px; margin-top: 6px; }
.dash-line { fill: none; stroke: var(--accent); stroke-width: 2.5; stroke-linecap: round; stroke-dasharray: 600; stroke-dashoffset: 600; }
.dash-line.is-on { animation: draw 1.6s cubic-bezier(.22,1,.36,1) forwards; }
.dash-area { fill: url(#dashFill); opacity: 0; transition: opacity .8s .6s; }
.dash-area.is-on { opacity: 1; }
@keyframes draw { to { stroke-dashoffset: 0; } }
.dash-o { display: grid; grid-template-columns: 1fr auto; gap: 2px 8px; align-items: center; padding: 8px 0; border-top: 1px solid var(--soft-2); font-size: 11.5px; }
.dash-o:first-of-type { border-top: 0; }
.dash-o b { font-size: 12px; } .dash-o span { color: var(--ink-2); }
.dash-o .st { grid-row: span 2; height: 22px; font-size: 10.5px; }
.dash-o.is-new { animation: fin .5s cubic-bezier(.22,1,.36,1) both; background: linear-gradient(90deg, var(--accent-50), transparent); margin: 0 -12px; padding: 8px 12px; }
.shot .phone { position: absolute; right: 0; bottom: -10px; transform: scale(.86); transform-origin: bottom right; z-index: 3; }
.shot .toast-1 { right: 230px; top: auto; bottom: 330px; left: auto; }
.shot .toast-2 { right: 250px; bottom: 150px; left: auto; }
@media (max-width: 960px) {
    .shot { padding: 0 0 20px; display: grid; place-items: center; min-height: 560px; }
    .shot .dash { display: none; }
    .shot .phone { position: relative; right: auto; bottom: auto; transform: none; }
    .shot .toast-1 { right: 0; bottom: auto; top: 92px; }
    .shot .toast-2 { left: 0; right: auto; bottom: 120px; }
}
@media (max-width: 420px) { .shot .phone { transform: scale(.9); margin: -30px 0; } }

/* ── Avant / avec ── */
.vs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; max-width: 980px; margin: 0 auto; }
.vs-card { padding: 28px; border-radius: 22px; }
.vs-card h3 { margin: 0 0 16px; font-size: 18px; }
.vs-card ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 12px; }
.vs-card li { display: flex; gap: 10px; font-size: 15px; font-weight: 600; }
.vs-card li svg { flex-shrink: 0; margin-top: 3px; }
.vs-before { background: var(--soft); color: var(--ink-2); }
.vs-before li svg { color: var(--ink-3); }
.vs-after { background: #fff; box-shadow: 0 0 0 2px var(--accent), var(--shadow); }
.vs-after li svg { color: var(--ok); }
@media (max-width: 760px) { .vs { grid-template-columns: 1fr; } }

/* ── Calculateur ── */
.calc-sec { background: var(--ink); color: #fff; }
.calc-sec .kicker { color: #93C5FD; }
.calc { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); gap: 40px; align-items: center; }
.calc h2 { color: #fff; }
.calc > div > p { color: #CBD5E1; font-size: 17px; margin: 0 0 12px; }
.calc small.note { color: #94A3B8; font-size: 13px; }
.calc-card { padding: 26px; border-radius: 22px; background: #fff; color: var(--ink); display: grid; gap: 18px; margin: 0; }
.calc-f label { display: flex; justify-content: space-between; gap: 10px; font-weight: 700; font-size: 14px; margin-bottom: 8px; }
.calc-f label output { color: var(--accent-dk); font-variant-numeric: tabular-nums; white-space: nowrap; }
.calc-f input[type=range] { width: 100%; accent-color: var(--accent); height: 28px; }
.calc-res { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding-top: 14px; border-top: 1px solid var(--line); }
.calc-res div { padding: 14px; border-radius: 14px; background: var(--soft); }
.calc-res small { display: block; color: var(--ink-2); font-size: 12.5px; font-weight: 700; }
.calc-res b { font-size: 24px; letter-spacing: -.02em; font-variant-numeric: tabular-nums; }
.calc-res div.is-main { background: var(--accent-50); }
.calc-res div.is-main b { color: var(--accent-dk); }
@media (max-width: 860px) { .calc { grid-template-columns: 1fr; gap: 24px; } }
@media (max-width: 420px) { .calc-res { grid-template-columns: 1fr; } .calc-card { padding: 20px; } }

/* ── Comparaison ── */
.cmp-wrap { max-width: 980px; margin: 0 auto; overflow-x: auto; border-radius: 18px; box-shadow: inset 0 0 0 1px var(--line); }
.cmp { width: 100%; min-width: 620px; border-collapse: collapse; font-size: 14.5px; }
.cmp th, .cmp td { padding: 15px 16px; text-align: center; border-bottom: 1px solid var(--soft-2); }
.cmp th:first-child, .cmp td:first-child { text-align: left; font-weight: 600; }
.cmp thead th { font-size: 14px; color: var(--ink-2); background: var(--soft); }
.cmp .is-us { background: var(--accent-50); color: var(--accent-dk); font-weight: 800; }
.cmp tbody tr:last-child td { border-bottom: 0; }
.cmp .y { color: var(--ok); } .cmp .n { color: var(--ink-3); }
.cmp .s { color: var(--ink-2); font-size: 12.5px; font-weight: 600; }
.cmp td svg { margin: 0 auto; }

/* ── Bouton fixe sur téléphone ── */
.mcta { display: none; }
@media (max-width: 640px) {
    .mcta { display: flex; position: fixed; left: 12px; right: 12px; bottom: calc(12px + env(safe-area-inset-bottom, 0px)); z-index: 60; transform: translateY(140%); transition: transform .35s cubic-bezier(.22,1,.36,1); }
    .mcta.is-on { transform: none; }
    .mcta .btn { flex: 1; box-shadow: 0 12px 30px -10px rgba(15,23,42,.45); }
    .foot { padding-bottom: 96px; }
}

/* ── Pays et devises (défilement continu) ── */
.mq { padding: 18px 0 22px; border-bottom: 1px solid var(--soft-2); overflow: hidden; }
.mq-t { margin: 0 0 12px; text-align: center; color: var(--ink-3); font-size: 12.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
.mq-vp { position: relative; overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent); }
.mq-track { display: flex; gap: 10px; width: max-content; animation: mq 48s linear infinite; }
.mq-vp:hover .mq-track { animation-play-state: paused; }
.mq-c { display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px 0 6px; border-radius: 999px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); font-size: 13.5px; font-weight: 700; white-space: nowrap; }
.mq-c b { display: inline-grid; place-items: center; min-width: 30px; height: 26px; padding: 0 6px; border-radius: 999px; background: var(--soft-2); color: var(--ink-2); font-size: 11px; letter-spacing: .04em; }
.mq-c small { color: var(--ink-3); font-weight: 700; font-size: 12px; }
@keyframes mq { to { transform: translateX(-50%); } }

/* ── Parcours d'une commande (défilement avec téléphone fixe) ── */
.story { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 56px; align-items: start; }
.story-steps { display: grid; }
.sstep { min-height: 62vh; display: flex; flex-direction: column; justify-content: center; padding: 24px 0; opacity: .32; transition: opacity .4s; }
.sstep.is-on { opacity: 1; }
.sstep .n { display: inline-grid; place-items: center; width: 34px; height: 34px; margin-bottom: 14px; border-radius: 999px; background: var(--soft-2); color: var(--ink-2); font-weight: 800; font-size: 14px; transition: background-color .3s, color .3s; }
.sstep.is-on .n { background: var(--accent); color: #fff; }
.sstep h3 { margin: 0 0 8px; font-size: clamp(22px, 2.6vw, 30px); letter-spacing: -.02em; line-height: 1.15; }
.sstep p { margin: 0; max-width: 460px; color: var(--ink-2); font-size: 16.5px; }
.sstep .inline { display: none; }
.story-dev { position: sticky; top: calc(50vh - 290px); display: grid; place-items: center; }
.story-dev .phone { width: 280px; height: 570px; }
.sscr { position: absolute; inset: 0; padding: 50px 14px 14px; display: grid; gap: 10px; align-content: start; background: #fff; opacity: 0; transform: translateY(12px); transition: opacity .45s, transform .55s cubic-bezier(.22,1,.36,1); }
.sscr.is-on { opacity: 1; transform: none; }
.sscr h4 { margin: 0; font-size: 15px; letter-spacing: -.01em; }
.sscr .sub { color: var(--ink-2); font-size: 11.5px; font-weight: 600; margin-top: -6px; }
.s-card { padding: 12px; border-radius: 12px; box-shadow: inset 0 0 0 1px var(--line); background: #fff; display: grid; gap: 6px; font-size: 12px; }
.s-card.is-hl { box-shadow: inset 0 0 0 2px var(--accent); background: var(--accent-50); }
.s-row { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.s-row b { font-size: 12.5px; }
.s-mute { color: var(--ink-2); font-weight: 600; }
.s-wa { padding: 10px 12px; border-radius: 12px 12px 12px 3px; background: var(--soft-2); font-size: 12px; line-height: 1.45; }
.s-wa.me { border-radius: 12px 12px 3px 12px; background: #DCFCE7; justify-self: end; max-width: 85%; }
.s-av { width: 30px; height: 30px; border-radius: 999px; background: var(--soft-2); display: grid; place-items: center; font-size: 11px; font-weight: 800; color: var(--ink-2); flex-shrink: 0; }
.s-map { height: 150px; border-radius: 12px; background: linear-gradient(0deg, #EEF2F7, #F8FAFC); position: relative; overflow: hidden; }
.s-map svg { position: absolute; inset: 0; width: 100%; height: 100%; }
.s-route { fill: none; stroke: var(--accent); stroke-width: 3; stroke-dasharray: 6 6; }
.s-dot { offset-path: path('M20 130 C70 120 80 70 130 75 S200 30 250 24'); offset-distance: 0%; position: absolute; left: 0; top: 0; width: 14px; height: 14px; margin: -7px 0 0 -7px; border-radius: 999px; background: var(--accent); box-shadow: 0 0 0 6px rgba(31,124,246,.2); }
.sscr.is-on .s-dot { animation: ride 4s ease-in-out infinite; }
@keyframes ride { 0% { offset-distance: 0%; } 80%, 100% { offset-distance: 100%; } }
.s-track { display: grid; gap: 0; }
.s-tr { display: grid; grid-template-columns: 22px 1fr; gap: 10px; font-size: 12px; font-weight: 700; }
.s-tr i { width: 14px; height: 14px; margin: 2px auto 0; border-radius: 999px; background: var(--line); position: relative; }
.s-tr i::after { content: ''; position: absolute; left: 6px; top: 16px; width: 2px; height: 18px; background: var(--line); }
.s-tr:last-child i::after { display: none; }
.s-tr.ok i { background: var(--ok); } .s-tr.ok i::after { background: var(--ok); }
.s-tr.now i { background: var(--accent); box-shadow: 0 0 0 4px var(--accent-100); }
.s-tr span { padding-bottom: 14px; } .s-tr small { display: block; color: var(--ink-3); font-weight: 600; }
.s-big { display: grid; place-items: center; gap: 8px; padding: 26px 10px; text-align: center; }
.s-big .ok { width: 60px; height: 60px; border-radius: 999px; background: var(--ok); color: #fff; display: grid; place-items: center; }
.s-big b { font-size: 24px; letter-spacing: -.02em; }
@media (max-width: 900px) {
    .story { grid-template-columns: 1fr; gap: 0; }
    .story-dev { display: none; }
    .sstep { min-height: 0; opacity: 1; padding: 0 0 34px; }
    .sstep .n { background: var(--accent); color: #fff; }
    .sstep .inline { display: block; margin-top: 16px; max-width: 360px; }
    .sstep .inline .sscr { position: relative; inset: auto; opacity: 1; transform: none; padding: 14px; border-radius: 18px; box-shadow: inset 0 0 0 1px var(--line); }
}

/* ── Partage partout ── */
.share { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; max-width: 900px; margin: 0 auto; }
.share span { display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 16px; border-radius: 12px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); font-weight: 700; font-size: 14.5px; transition: transform .2s, box-shadow .2s; }
.share span:hover { transform: translateY(-2px); box-shadow: var(--shadow), inset 0 0 0 1px var(--line); }
.share svg { color: var(--ink-2); }

/* ── Héros : léger effet 3D au survol (souris uniquement) ── */
@media (hover: hover) and (pointer: fine) and (min-width: 961px) {
    .shot { perspective: 1800px; }
    .shot .dash { transition: transform .5s cubic-bezier(.22,1,.36,1); transform-origin: 50% 60%; will-change: transform; }
}
@media (prefers-reduced-motion: reduce) {
    .mq-track { animation: none; flex-wrap: wrap; justify-content: center; width: auto; }
    .mq-track [aria-hidden="true"] { display: none; }
    .mq-vp { -webkit-mask-image: none; mask-image: none; }
}

/* ── Témoignages (validés par l'équipe Shopio) ── */
.tms { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.tms.is-1 { grid-template-columns: minmax(0, 620px); justify-content: center; }
.tms.is-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); max-width: 900px; margin: 0 auto; }
.tm { display: flex; flex-direction: column; gap: 14px; padding: 24px; border-radius: 20px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); }
.tm .st { color: #F59E0B; letter-spacing: 2px; font-size: 15px; height: auto; padding: 0; background: none; }
.tm q { margin: 0; font-size: 16px; line-height: 1.6; quotes: '« ' ' »'; flex: 1; }
.tm-who { display: flex; align-items: center; gap: 12px; padding-top: 14px; border-top: 1px solid var(--soft-2); }
.tm-av { width: 42px; height: 42px; border-radius: 12px; background: var(--ink); color: #fff; display: grid; place-items: center; font-weight: 800; font-size: 14px; flex-shrink: 0; overflow: hidden; }
.tm-av img { width: 100%; height: 100%; object-fit: cover; }
.tm-who b { display: block; font-size: 14.5px; }
.tm-who small { display: block; color: var(--ink-2); font-size: 12.5px; font-weight: 600; }
.tm-who a { color: var(--accent-dk); font-weight: 700; text-decoration: none; }
.tm-who a:hover { text-decoration: underline; }
@media (max-width: 960px) { .tms, .tms.is-2 { grid-template-columns: 1fr; max-width: 620px; margin: 0 auto; } }

/* ── WhatsApp du support ── */
.wa-fab { position: fixed; right: 18px; bottom: calc(18px + env(safe-area-inset-bottom, 0px)); z-index: 70; display: inline-flex; align-items: center; gap: 8px; height: 52px; padding: 0 18px 0 14px; border-radius: 999px; background: #fff; color: var(--ink); box-shadow: 0 14px 34px -12px rgba(15,23,42,.45), inset 0 0 0 1px var(--line); font-weight: 800; font-size: 14px; text-decoration: none; transition: transform .2s, box-shadow .2s; }
.wa-fab:hover { transform: translateY(-2px); }
.wa-fab i { width: 32px; height: 32px; border-radius: 999px; background: #16A34A; color: #fff; display: grid; place-items: center; }
.faq-more { margin: 18px auto 0; text-align: center; color: var(--ink-2); font-weight: 600; }
.faq-more a { color: var(--accent-dk); font-weight: 800; }
@media (max-width: 640px) {
    .wa-fab { padding: 0; width: 52px; justify-content: center; bottom: calc(84px + env(safe-area-inset-bottom, 0px)); right: 14px; }
    .wa-fab span { display: none; }
    .wa-fab i { width: 52px; height: 52px; }
}

/* ── Bandeau ── */
.band { padding: 22px 0; border-block: 1px solid var(--soft-2); background: var(--soft); }
.band ul { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px 34px; margin: 0; padding: 0; list-style: none; color: var(--ink-2); font-weight: 700; font-size: 14.5px; }
.band li { display: flex; align-items: center; gap: 8px; }
.band svg { color: var(--ink-3); }

/* ── Sections ── */
.sec { padding: 88px 0; }
.sec-hd { max-width: 680px; margin: 0 auto 40px; text-align: center; }
.kicker { display: inline-block; margin-bottom: 10px; color: var(--accent); font-weight: 800; font-size: 13px; letter-spacing: .08em; text-transform: uppercase; }
.sec h2 { margin: 0 0 12px; font-size: clamp(28px, 3.6vw, 42px); line-height: 1.1; letter-spacing: -.03em; font-weight: 800; text-wrap: balance; }
.sec-hd p { margin: 0; color: var(--ink-2); font-size: 17px; }

/* ── Démos ── */
.demos { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 28px; align-items: start; }
.dtabs { display: grid; gap: 6px; position: sticky; top: 88px; }
.dtab { position: relative; display: grid; grid-template-columns: 40px 1fr; gap: 2px 12px; align-items: center; width: 100%; padding: 14px; border: 0; border-radius: 14px; background: transparent; color: var(--ink); text-align: left; font: inherit; cursor: pointer; overflow: hidden; transition: background-color .2s; }
.dtab:hover { background: var(--soft); }
.dtab[aria-selected="true"] { background: #fff; box-shadow: var(--shadow), inset 0 0 0 1px var(--line); }
.dtab .di { grid-row: span 2; width: 40px; height: 40px; border-radius: 11px; background: var(--soft-2); color: var(--ink-2); display: grid; place-items: center; transition: background-color .2s, color .2s; }
.dtab[aria-selected="true"] .di { background: var(--accent); color: #fff; }
.dtab b { font-size: 15px; } .dtab small { color: var(--ink-2); font-size: 13px; line-height: 1.35; }
.dtab .bar { position: absolute; left: 14px; right: 14px; bottom: 6px; height: 3px; border-radius: 999px; background: var(--soft-2); opacity: 0; }
.dtab .bar i { display: block; height: 100%; width: 0; border-radius: inherit; background: var(--accent); }
.dtab[aria-selected="true"] .bar { opacity: 1; }
.dview { position: relative; }
.win { border-radius: 16px; background: #fff; box-shadow: 0 30px 60px -30px rgba(15,23,42,.35), inset 0 0 0 1px var(--line); overflow: hidden; }
.win-bar { display: flex; align-items: center; gap: 12px; height: 42px; padding: 0 14px; background: var(--soft); border-bottom: 1px solid var(--line); }
.win-dots { display: flex; gap: 6px; } .win-dots i { width: 10px; height: 10px; border-radius: 999px; background: #CBD5E1; }
.win-url { flex: 1; max-width: 420px; margin: 0 auto; padding: 4px 12px; border-radius: 8px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); color: var(--ink-2); font-size: 12px; font-weight: 600; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.win-body { position: relative; height: 470px; overflow: hidden; }
.dpanel { position: absolute; inset: 0; padding: 22px; opacity: 0; visibility: hidden; transition: opacity .35s, visibility .35s; }
.dpanel.is-on { opacity: 1; visibility: visible; }
.dcap { margin: 14px 4px 0; color: var(--ink-2); font-size: 14.5px; min-height: 44px; }
.dcap b { color: var(--ink); }
.replay { position: absolute; right: 12px; bottom: 12px; z-index: 4; display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border: 0; border-radius: 999px; background: rgba(255,255,255,.92); box-shadow: inset 0 0 0 1px var(--line); color: var(--ink-2); font: 700 12px var(--font); cursor: pointer; }
@media (max-width: 960px) {
    .demos { grid-template-columns: minmax(0, 1fr); }
    .demos > * { min-width: 0; }
    .dtabs { position: static; grid-auto-flow: column; grid-auto-columns: minmax(210px, 1fr); overflow-x: auto; scroll-snap-type: x mandatory; padding: 4px 2px 10px; margin: 0 -20px; padding-inline: 20px; scrollbar-width: none; }
    .dtabs::-webkit-scrollbar { display: none; }
    .dtab { scroll-snap-align: start; }
    .win-body { height: 440px; }
    .dpanel { padding: 16px; }
}

/* Éléments communs aux démos */
.m-h { margin: 0 0 4px; font-size: 18px; font-weight: 800; letter-spacing: -.02em; }
.m-sub { margin: 0 0 14px; color: var(--ink-2); font-size: 12.5px; }
.m-card { border-radius: 12px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); }
.m-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 34px; padding: 0 12px; border-radius: 9px; background: var(--accent); color: #fff; font-weight: 800; font-size: 12px; transition: transform .15s; }
.m-btn.is-ghost { background: #fff; color: var(--ink); box-shadow: inset 0 0 0 1px var(--line); }
.m-btn.is-press { transform: scale(.94); }
.m-chip { display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px; border-radius: 999px; background: var(--soft-2); color: var(--ink-2); font-size: 11.5px; font-weight: 700; white-space: nowrap; transition: background-color .3s, color .3s; }
.m-chip b { color: var(--ink); }
.m-chip.is-on { background: var(--ink); color: #fff; } .m-chip.is-on b { color: #fff; }
.st { display: inline-flex; align-items: center; gap: 5px; height: 24px; padding: 0 9px; border-radius: 999px; font-size: 11px; font-weight: 800; transition: background-color .35s, color .35s; }
.st-new { background: var(--warn-50); color: var(--warn); } .st-ok { background: var(--accent-50); color: var(--accent-dk); }
.st-go { background: #F0F9FF; color: #0369A1; } .st-done { background: var(--ok-50); color: var(--ok); }
.st-grey { background: var(--soft-2); color: var(--ink-2); }
.fade-in { animation: fin .45s cubic-bezier(.22,1,.36,1) both; }
@keyframes fin { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
.caret::after { content: ''; display: inline-block; width: 1.5px; height: 1em; margin-left: 1px; vertical-align: -2px; background: var(--accent); animation: blink 1s steps(1) infinite; }
@keyframes blink { 50% { opacity: 0; } }

/* Démo 1 : création de boutique */
.ob { display: grid; place-items: center; height: 100%; }
.ob-box { width: 100%; max-width: 330px; text-align: center; }
.ob-logo { width: 40px; height: 40px; margin: 0 auto 12px; border-radius: 11px; }
.ob-in { height: 44px; border-radius: 11px; box-shadow: inset 0 0 0 2px var(--accent); display: grid; place-items: center; font-weight: 700; font-size: 14px; }
.ob-hint { margin: 8px 0 12px; font-size: 11.5px; color: var(--ink-3); }
.ob-opt { display: flex; align-items: center; gap: 10px; height: 44px; margin-bottom: 8px; padding: 0 12px; border-radius: 11px; box-shadow: inset 0 0 0 1.5px var(--line); font-size: 13px; font-weight: 700; text-align: left; transition: box-shadow .25s, background-color .25s; }
.ob-opt.is-on { box-shadow: inset 0 0 0 2px var(--accent); background: var(--accent-50); }
.ob-opt svg { color: var(--ink-2); }
.ob-dots { display: flex; justify-content: center; gap: 5px; margin-top: 16px; }
.ob-dots i { width: 6px; height: 6px; border-radius: 999px; background: var(--line); transition: width .3s, background-color .3s; }
.ob-dots i.on { width: 20px; background: var(--accent); } .ob-dots i.done { background: var(--accent-100); }
.ob-task { display: flex; align-items: center; gap: 10px; height: 40px; margin-bottom: 7px; padding: 0 12px; border-radius: 10px; font-size: 13px; font-weight: 600; color: var(--ink-3); text-align: left; transition: background-color .3s, color .3s; }
.ob-task .s { width: 18px; height: 18px; border-radius: 999px; box-shadow: inset 0 0 0 1.5px var(--line); display: grid; place-items: center; }
.ob-task.run { color: var(--ink); box-shadow: inset 0 0 0 1px var(--line); }
.ob-task.run .s { border: 2px solid var(--accent-100); border-top-color: var(--accent); box-shadow: none; animation: spin .8s linear infinite; }
.ob-task.ok { color: var(--ink); background: var(--ok-50); }
.ob-task.ok .s { background: var(--ok); color: #fff; box-shadow: none; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Démo 2 : IA */
.ai { display: grid; grid-template-columns: 200px 1fr; gap: 18px; height: 100%; }
.ai-photo { height: 200px; border-radius: 14px; background: linear-gradient(160deg, #F5EFE6, #E9DFD0); display: grid; place-items: center; position: relative; overflow: hidden; }
.ai-photo.is-empty { background: var(--soft); box-shadow: inset 0 0 0 2px dashed var(--line); }
.ai-drop { position: absolute; inset: 10px; border: 2px dashed #CBD5E1; border-radius: 10px; display: grid; place-items: center; color: var(--ink-3); font-size: 12px; font-weight: 700; transition: opacity .3s; }
.ai-scan { position: absolute; left: 0; right: 0; height: 40px; background: linear-gradient(180deg, transparent, rgba(31,124,246,.18), transparent); top: -40px; }
.ai-scan.is-on { animation: scan 1.4s ease-in-out 2; }
@keyframes scan { to { top: 100%; } }
.ai-side { display: grid; gap: 8px; align-content: start; }
.ai-row { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; font-size: 12px; color: var(--ink-2); font-weight: 600; }
.ai-row b { color: var(--ink); }
.ai-out { display: grid; gap: 8px; align-content: start; }
.ai-badge { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; background: var(--accent-50); color: var(--accent-dk); font-size: 11.5px; font-weight: 800; justify-self: start; }
.ai-title { margin: 0; font-size: 19px; font-weight: 800; letter-spacing: -.02em; min-height: 26px; }
.ai-text { margin: 0; color: var(--ink-2); font-size: 13px; min-height: 60px; }
.ai-li { display: flex; gap: 8px; font-size: 12.5px; font-weight: 600; }
.ai-li svg { color: var(--ok); flex-shrink: 0; margin-top: 2px; }
.ai-price { display: flex; gap: 8px; flex-wrap: wrap; }
.ai-price span { padding: 6px 10px; border-radius: 9px; box-shadow: inset 0 0 0 1px var(--line); font-size: 12px; font-weight: 700; }
.ai-price span.is-on { box-shadow: inset 0 0 0 2px var(--accent); background: var(--accent-50); }
@media (max-width: 640px) { .ai { grid-template-columns: 1fr; } .ai-photo { height: 120px; } .ai-side { display: none; } }

/* Démo 3 : sections */
.sx { display: grid; grid-template-columns: 210px 1fr; gap: 16px; height: 100%; }
.sx-list { display: grid; gap: 6px; align-content: start; }
.sx-item { display: flex; align-items: center; gap: 8px; height: 38px; padding: 0 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-radius: 10px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); font-size: 12.5px; font-weight: 700; transition: transform .45s cubic-bezier(.22,1,.36,1), box-shadow .2s; }
.sx-item svg { color: var(--ink-3); }
.sx-item.is-drag { box-shadow: var(--shadow), inset 0 0 0 2px var(--accent); transform: scale(1.03); z-index: 2; }
.sx-add { height: 38px; border-radius: 10px; border: 1.5px dashed #CBD5E1; display: flex; align-items: center; justify-content: center; gap: 6px; color: var(--ink-2); font-size: 12.5px; font-weight: 700; }
.sx-prev { border-radius: 14px; background: var(--soft); padding: 12px; display: grid; gap: 8px; align-content: start; overflow: hidden; }
.sx-blk { border-radius: 10px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); padding: 10px; transition: transform .45s cubic-bezier(.22,1,.36,1); }
.sx-blk h5 { margin: 0 0 6px; font-size: 11px; color: var(--ink-3); text-transform: uppercase; letter-spacing: .06em; }
.sx-ln { height: 8px; border-radius: 999px; background: var(--soft-2); margin-top: 5px; }
.sx-img { height: 70px; border-radius: 8px; background: linear-gradient(160deg, #F5EFE6, #E9DFD0); }
.sx-row { display: flex; gap: 6px; }
.sx-row > * { flex: 1; }
@media (max-width: 640px) { .sx { grid-template-columns: 1fr; } .sx-list { grid-template-columns: 1fr 1fr; } .sx-prev { display: none; } }

/* Démo 4 : commandes */
.od-chips { display: flex; gap: 6px; overflow: hidden; margin-bottom: 12px; }
.od-day { margin: 2px 0 8px; font-size: 11px; font-weight: 800; letter-spacing: .08em; color: var(--ink-3); }
.od-list { display: grid; gap: 8px; }
.od-card { display: grid; grid-template-columns: 1fr auto; gap: 6px 10px; align-items: center; padding: 12px 14px; transition: box-shadow .3s; }
.od-card.is-hl { box-shadow: inset 0 0 0 2px var(--accent); }
.od-card b { font-size: 13.5px; }
.od-meta { display: flex; gap: 14px; font-size: 11.5px; color: var(--ink-2); }
.od-meta strong { color: var(--ink); }
.od-acts { grid-column: 1 / -1; display: flex; gap: 6px; height: 0; overflow: hidden; transition: height .35s; }
.od-card.is-open .od-acts { height: 34px; }
.od-wa { position: absolute; right: 22px; bottom: 22px; width: 250px; padding: 12px; border-radius: 14px; background: #fff; box-shadow: var(--shadow), inset 0 0 0 1px var(--line); font-size: 12px; opacity: 0; transform: translateY(10px); transition: opacity .35s, transform .45s; }
.od-wa.is-on { opacity: 1; transform: none; }
.od-wa .bubble { margin-top: 8px; padding: 8px 10px; border-radius: 10px 10px 10px 2px; background: var(--soft-2); color: var(--ink); }

/* Démo 5 : paniers abandonnés */
.ca { display: grid; grid-template-columns: 220px 1fr; gap: 18px; height: 100%; }
.ca-mini { border-radius: 14px; background: var(--soft); padding: 14px; display: grid; gap: 7px; align-content: start; position: relative; }
.ca-mini small { font-size: 11px; color: var(--ink-3); font-weight: 700; }
.ca-leave { position: absolute; inset: 0; border-radius: 14px; background: rgba(248,250,252,.92); display: grid; place-items: center; text-align: center; font-size: 12px; font-weight: 700; color: var(--ink-2); opacity: 0; transition: opacity .35s; }
.ca-leave.is-on { opacity: 1; }
.ca-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 10px; }
.ca-stat { padding: 10px; }
.ca-stat small { display: block; font-size: 10.5px; color: var(--ink-2); font-weight: 700; }
.ca-stat b { font-size: 17px; letter-spacing: -.02em; }
.ca-card { display: grid; gap: 8px; padding: 12px 14px; margin-bottom: 8px; }
.ca-card .top { display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--ink-2); }
.ca-card .top b { color: var(--ink); font-size: 13.5px; }
.ca-acts { display: flex; gap: 6px; flex-wrap: wrap; }
@media (max-width: 640px) { .ca { grid-template-columns: 1fr; } .ca-mini { display: none; } }

/* Démo 6 : thèmes */
.th { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; height: 100%; }
.th-site { border-radius: 14px; overflow: hidden; box-shadow: inset 0 0 0 1px var(--line); transition: transform .5s cubic-bezier(.22,1,.36,1), box-shadow .3s, opacity .3s; display: flex; flex-direction: column; }
.th-site.is-on { box-shadow: 0 0 0 3px var(--accent); transform: translateY(-4px); }
.th-site.is-off { opacity: .55; }
.th-hd { padding: 12px; display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; }
.th-hd i { width: 22px; height: 22px; border-radius: 6px; }
.th-cover { height: 80px; margin: 0 12px; border-radius: 10px; }
.th-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 12px; }
.th-p { border-radius: 9px; padding: 6px; }
.th-p div { height: 54px; border-radius: 7px; margin-bottom: 6px; }
.th-p span { display: block; height: 6px; border-radius: 999px; margin-top: 4px; }
.th-lbl { margin-top: auto; padding: 10px 12px; font-size: 12px; font-weight: 800; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(0,0,0,.06); }
.th-ex { background: #F6F9FF; color: #0F172A; }
.th-ex .th-hd i, .th-ex .th-cover { background: linear-gradient(135deg, #1F7CF6, #60A5FA); }
.th-ex .th-p { background: #fff; } .th-ex .th-p div { background: #E2E8F0; } .th-ex .th-p span { background: #CBD5E1; }
.th-sg { background: #FBF8F3; color: #1C1917; }
.th-sg .th-hd i, .th-sg .th-cover { background: linear-gradient(135deg, #14532D, #3F6212); }
.th-sg .th-p { background: #fff; } .th-sg .th-p div { background: #EDE7DC; } .th-sg .th-p span { background: #D6CFC2; }
@media (max-width: 640px) { .th { grid-template-columns: 1fr; } .th-site.is-off { display: none; } }

/* ── Fonctionnalités ── */
.feat { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.fcard { padding: 22px; border-radius: var(--r); background: #fff; box-shadow: inset 0 0 0 1px var(--line); transition: box-shadow .2s, transform .2s; }
.fcard:hover { box-shadow: var(--shadow), inset 0 0 0 1px var(--line); transform: translateY(-2px); }
.fcard .fi { width: 42px; height: 42px; border-radius: 12px; background: var(--soft-2); color: var(--ink); display: grid; place-items: center; margin-bottom: 14px; }
.fcard h3 { margin: 0 0 6px; font-size: 16px; letter-spacing: -.01em; }
.fcard p { margin: 0; color: var(--ink-2); font-size: 14px; }
@media (max-width: 960px) { .feat { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 520px) { .feat { grid-template-columns: 1fr; } }

/* ── Étapes ── */
.steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; counter-reset: s; }
.step { padding: 26px; border-radius: var(--r); background: var(--soft); }
.step::before { counter-increment: s; content: counter(s); display: grid; place-items: center; width: 34px; height: 34px; margin-bottom: 16px; border-radius: 999px; background: var(--ink); color: #fff; font-weight: 800; font-size: 14px; }
.step h3 { margin: 0 0 6px; font-size: 17px; }
.step p { margin: 0; color: var(--ink-2); font-size: 14.5px; }
@media (max-width: 860px) { .steps { grid-template-columns: 1fr; } }

/* ── Chiffres ── */
.stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 34px; }
.stats div { padding: 20px; border-radius: var(--r); box-shadow: inset 0 0 0 1px var(--line); text-align: center; }
.stats b { display: block; font-size: 30px; letter-spacing: -.03em; }
.stats span { color: var(--ink-2); font-size: 14px; font-weight: 600; }

/* ── Tarifs ── */
.plans { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; max-width: 860px; margin: 0 auto; }
.plan { padding: 28px; border-radius: 22px; box-shadow: inset 0 0 0 1px var(--line); background: #fff; display: grid; gap: 14px; align-content: start; }
.plan.is-pro { box-shadow: 0 0 0 2px var(--accent), var(--shadow); }
.plan h3 { margin: 0; font-size: 20px; display: flex; align-items: center; gap: 10px; }
.plan h3 span { padding: 3px 10px; border-radius: 999px; background: var(--accent-50); color: var(--accent-dk); font-size: 12px; }
.plan > p { margin: 0; color: var(--ink-2); }
.plan ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 9px; }
.plan li { display: flex; gap: 10px; font-size: 14.5px; font-weight: 600; }
.plan li svg { color: var(--ok); flex-shrink: 0; margin-top: 3px; }
.plan { position: relative; }
.plan .price { display: flex; align-items: baseline; flex-wrap: wrap; gap: 4px 8px; padding: 6px 0 14px; border-bottom: 1px solid var(--soft-2); }
.plan .price b { font-size: 40px; line-height: 1; letter-spacing: -.035em; font-variant-numeric: tabular-nums; }
.plan .price span { color: var(--ink-2); font-weight: 700; font-size: 15px; }
.plan .price small { flex-basis: 100%; color: var(--ink-3); font-size: 13px; font-weight: 600; }
.plan-flag { position: absolute; top: -13px; left: 24px; padding: 4px 12px; border-radius: 999px; background: var(--accent); color: #fff; font-size: 12px; font-weight: 800; }
.plan-note { color: var(--ink-3); font-size: 12.5px; font-weight: 600; line-height: 1.45; text-align: center; }
.ptable { max-width: 860px; margin: 22px auto 0; }
.ptable summary { width: max-content; margin: 0 auto; padding: 10px 16px; border-radius: 10px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); font-weight: 700; font-size: 14.5px; cursor: pointer; list-style: none; }
.ptable summary::-webkit-details-marker { display: none; }
.ptable summary::after { content: ' ▾'; color: var(--ink-3); }
.ptable[open] summary::after { content: ' ▴'; }
.ptable .cmp { min-width: 520px; background: #fff; }
.ptable .cmp .n { color: var(--ink-3); }
.nav-links a.is-on { color: var(--ink); background: var(--soft-2); }
@media (max-width: 760px) { .plans { grid-template-columns: 1fr; } }

/* ── FAQ ── */
.faq { max-width: 760px; margin: 0 auto; display: grid; gap: 10px; }
.faq details { border-radius: 14px; box-shadow: inset 0 0 0 1px var(--line); background: #fff; }
.faq summary { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 18px 20px; font-weight: 700; cursor: pointer; list-style: none; }
.faq summary::-webkit-details-marker { display: none; }
.faq summary::after { content: '+'; font-size: 22px; font-weight: 500; color: var(--ink-3); transition: transform .2s; }
.faq details[open] summary::after { transform: rotate(45deg); }
.faq details p { margin: 0; padding: 0 20px 18px; color: var(--ink-2); }

/* ── Appel final ── */
.final { margin: 0 20px 80px; padding: 64px 24px; border-radius: 28px; background: var(--ink); color: #fff; text-align: center; }
.final h2 { margin: 0 0 12px; font-size: clamp(28px, 4vw, 44px); letter-spacing: -.03em; line-height: 1.1; text-wrap: balance; }
.final p { margin: 0 auto 26px; max-width: 520px; color: #CBD5E1; font-size: 17px; }
.final .btn-line { background: transparent; color: #fff; box-shadow: inset 0 0 0 1.5px #334155; }
.final .hero-cta { justify-content: center; }

/* ── Pied de page ── */
.foot { padding: 34px 0 calc(34px + env(safe-area-inset-bottom, 0px)); border-top: 1px solid var(--soft-2); color: var(--ink-2); font-size: 14px; }
.foot .wrap { display: flex; flex-wrap: wrap; gap: 14px 28px; align-items: center; }
.foot nav { display: flex; flex-wrap: wrap; gap: 6px 20px; margin-left: auto; }
.foot a { text-decoration: none; font-weight: 600; } .foot a:hover { color: var(--ink); }

/* Apparition au défilement */
.rv { opacity: 0; transform: translateY(18px); transition: opacity .6s cubic-bezier(.22,1,.36,1), transform .6s cubic-bezier(.22,1,.36,1); }
.rv.is-in { opacity: 1; transform: none; }
@media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    .rv { opacity: 1; transform: none; transition: none; }
    *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; }
}
</style>
</head>
<body>
@php
    $ico = [
        'check' => 'M5 12.5 10 17 19 7',
        'x'     => 'M6 6l12 12M18 6 6 18',
        'store' => 'M4 9l1.5-5h13L20 9M4 9v11h16V9M4 9c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3M10 20v-5h4v5',
        'spark' => 'M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8zM19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z',
        'layers'=> 'M12 3 3 8l9 5 9-5zM3 13l9 5 9-5M3 17.5l9 5 9-5',
        'box'   => 'M12 3 4 7v10l8 4 8-4V7zM4 7l8 4 8-4M12 11v10',
        'cart'  => 'M3 4h2l2.4 11.2a1 1 0 0 0 1 .8h9.2a1 1 0 0 0 1-.8L21 8H6.2M10 20.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2zM18 20.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2z',
        'brush' => 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z',
        'cash'  => 'M3 6.5h18v11H3zM12 14.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM6 9.5v.01M18 14.5v.01',
        'bike'  => 'M6 19.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM18 19.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM8.5 17h7M15 7h2.5l2.5 6.5M6 14.5 9 9h5l1 5',
        'pin'   => 'M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21zM12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z',
        'users' => 'M9 11.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM3 20c.8-3.4 3.2-5.5 6-5.5s5.2 2.1 6 5.5M16 4.5a3.5 3.5 0 0 1 0 7M21 20c-.5-2.4-1.8-4.1-3.6-4.9',
        'tag'   => 'M3 12 12 3h8v8l-9 9zM15.5 8.5h.01',
        'chart' => 'M4 19h16M7 16V9M12 16V5M17 16v-4',
        'qr'    => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 14h2M14 18h2v2M18 18h2v2',
        'chat'  => 'M4 5h16v11H9l-5 4zM8 9h8M8 12h5',
        'phone' => 'M8 3h8a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM11 18h2',
        'shield'=> 'M12 3 4 6v6c0 4.5 3.4 8.3 8 9 4.6-.7 8-4.5 8-9V6zM8.5 12l2.5 2.5 4.5-5',
        'menu'  => 'M4 7h16M4 12h16M4 17h16',
        'play'  => 'M7 5v14l11-7z',
        'replay'=> 'M3 12a9 9 0 1 0 3-6.7M3 4v5h5',
        'grip'  => 'M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01',
        'globe' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z',
        'sprout'=> 'M12 20v-8M12 12c0-3.5-2.5-6-7-6 0 4 2.5 6 7 6zM12 14c0-3 2-5.5 6.5-5.5 0 3.5-2 5.5-6.5 5.5',
        'upload'=> 'M12 16V4M7 9l5-5 5 5M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3',
        'whats' => 'M4 20l1.3-4A8 8 0 1 1 8 18.7zM9 9.5c0 3 2.5 5.5 5.5 5.5l1.2-1.2-2-1-1 .8a4 4 0 0 1-2.3-2.3l.8-1-1-2z',
    ];
    $i = fn ($k, $s = 20) => '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$ico[$k].'"/></svg>';
    $logo = \App\Support\Assets::v('images/shopio-logo-192.png');
    $register = route('register', ['role' => 'admin']);
    $bag = '<svg width="120" height="120" viewBox="0 0 120 120" aria-hidden="true"><defs><linearGradient id="bg1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#3F3A35"/><stop offset="1" stop-color="#1F1B18"/></linearGradient></defs><path d="M38 44c0-14 10-24 22-24s22 10 22 24" fill="none" stroke="#2A2622" stroke-width="6" stroke-linecap="round"/><rect x="22" y="42" width="76" height="62" rx="14" fill="url(#bg1)"/><rect x="50" y="58" width="20" height="8" rx="3" fill="#C9A86A"/></svg>';
@endphp

<header class="nav" id="nav">
    <div class="wrap">
        <a class="logo" href="{{ route('landing') }}"><img src="{{ $logo }}" alt="" width="34" height="34">Shopio</a>
        <nav class="nav-links" aria-label="Sections de la page">
            <a href="#demos">Démos</a>
            <a href="#fonctionnalites">Fonctionnalités</a>
            <a href="#comparer">Comparer</a>
            <a href="#tarifs">Tarifs</a>
            <a href="#faq">Questions</a>
            <a href="{{ route('welcome') }}">Marché</a>
        </nav>
        <div class="nav-end">
            @guest<a class="nav-login" href="{{ route('login') }}">Se connecter</a>@endguest
            <a class="btn btn-main btn-sm" href="{{ $cta }}">{{ $ctaLabel }}</a>
            <button class="nav-burger" type="button" id="navBurger" aria-expanded="false" aria-controls="navMenu" aria-label="Ouvrir le menu">{!! $i('menu') !!}</button>
        </div>
    </div>
    <nav class="nav-menu" id="navMenu" aria-label="Menu">
        <a href="#demos">Démos</a><a href="#fonctionnalites">Fonctionnalités</a><a href="#comparer">Comparer</a><a href="#tarifs">Tarifs</a><a href="#faq">Questions</a><a href="{{ route('welcome') }}">Marché Shopio</a>
        @guest<a href="{{ route('login') }}">Se connecter</a>@endguest
    </nav>
</header>

<main>
    {{-- ═══ Héros ═══ --}}
    <section class="hero">
        <div class="wrap hero-in">
            <div class="hero-txt">
                <span class="pill"><b>Nouveau</b> Commandes, paniers abandonnés et Shopio IA</span>
                <h1>Ta boutique en ligne qui <em>vend</em>, payée à la livraison.</h1>
                <p class="hero-sub">Crée ta boutique en quelques minutes, publie des pages produit qui donnent envie, reçois les commandes au même endroit et relance les clients partis sans commander.</p>
                <div class="hero-cta">
                    <a class="btn btn-main" href="{{ $cta }}">{{ $ctaLabel }} <span aria-hidden="true">→</span></a>
                    <a class="btn btn-line" href="#demos">{!! $i('play', 16) !!} Voir les démos</a>
                </div>
                <ul class="hero-proof">
                    <li>{!! $i('check', 16) !!} {{ $trialDays }} jours de Plan Pro offerts</li>
                    <li>{!! $i('check', 16) !!} Paiement à la livraison</li>
                    <li>{!! $i('check', 16) !!} Puis gratuit, ou Pro à {{ number_format($pro['price'], 0, ',', ' ') }} GNF / mois</li>
                </ul>
            </div>

            {{-- Démo du héros : le client commande sur son téléphone, la commande arrive dans le tableau de bord --}}
            <div class="shot stage" role="img" aria-label="Démo : un client commande sur son téléphone et la commande arrive dans le tableau de bord de la boutique">
                <div class="win dash" aria-hidden="true">
                    <div class="win-bar"><span class="win-dots"><i></i><i></i><i></i></span><span class="win-url">shopio · /boutique/dashboard</span></div>
                    <div class="dash-in">
                        <div class="dash-side">
                            <div class="lg"><i></i>Maison Kadi</div>
                            <span class="on">{!! $i('chart', 14) !!} Accueil</span>
                            <span>{!! $i('box', 14) !!} Commandes <em id="dashBadge">2</em></span>
                            <span>{!! $i('tag', 14) !!} Produits</span>
                            <span>{!! $i('cart', 14) !!} Paniers abandonnés</span>
                            <span>{!! $i('bike', 14) !!} Livreurs</span>
                            <span>{!! $i('users', 14) !!} Équipe</span>
                            <span>{!! $i('brush', 14) !!} Ma vitrine</span>
                        </div>
                        <div class="dash-main">
                            <div class="dash-hd"><div><b>Salut, Kadi</b><small>Voici ta journée</small></div><span class="m-chip">● En direct</span></div>
                            <div class="dash-acts"><span class="m-btn" style="height:28px;font-size:11px">Traiter les commandes</span><span class="m-btn is-ghost" style="height:28px;font-size:11px">+ Nouvelle commande</span><span class="m-btn is-ghost" style="height:28px;font-size:11px">Partager ma boutique</span></div>
                            <div class="dash-kpis">
                                <div class="m-card dash-kpi"><small>Revenus livrés</small><b id="kRev">3 250 000</b><i>GNF ce mois</i></div>
                                <div class="m-card dash-kpi"><small>Commandes du jour</small><b id="kCmd">12</b><i>+3 vs hier</i></div>
                                <div class="m-card dash-kpi"><small>Taux de confirmation</small><b id="kConf">82 %</b><i>ce mois</i></div>
                                <div class="m-card dash-kpi"><small>Taux de livraison</small><b id="kLiv">91 %</b><i>ce mois</i></div>
                            </div>
                            <div class="dash-row">
                                <div class="m-card dash-chart"><small>Revenus · 7 jours</small>
                                    <svg viewBox="0 0 300 120" preserveAspectRatio="none"><defs><linearGradient id="dashFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1F7CF6" stop-opacity=".18"/><stop offset="1" stop-color="#1F7CF6" stop-opacity="0"/></linearGradient></defs>
                                        <path class="dash-area" id="dashArea" d="M0 92 C30 88 45 70 75 74 S120 50 150 58 195 30 225 38 270 18 300 12 L300 120 L0 120Z"/>
                                        <path class="dash-line" id="dashLine" d="M0 92 C30 88 45 70 75 74 S120 50 150 58 195 30 225 38 270 18 300 12"/>
                                    </svg>
                                </div>
                                <div class="m-card dash-orders"><small>Dernières commandes</small><div id="dashList"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="phone" aria-hidden="true" id="hpPhone">
                    <div class="screen" id="hp">
                        <div class="ps-top"><span class="ps-av">MK</span><div><b>Maison Kadi</b><small>shopio · /s/maison-kadi</small></div></div>
                        <div class="ps-body" id="hpBody">
                            <div class="ps-img"><span class="tag">-25 %</span>{!! $bag !!}</div>
                            <div>
                                <p class="ps-name">Sac Kadi en cuir</p>
                                <div class="ps-stars">Livré à Conakry · payé à la livraison</div>
                            </div>
                            <div class="ps-price">250 000 GNF <s>330 000</s></div>
                            <div class="ps-packs">
                                <div class="ps-pack" data-pack="1">1 sac <span>250 000 GNF</span></div>
                                <div class="ps-pack" data-pack="2">2 sacs <span><i>-10 %</i> 450 000 GNF</span></div>
                            </div>
                            <div class="ps-form">
                                <div class="ps-field" data-f="name">Prénom et nom</div>
                                <div class="ps-field" data-f="phone">Téléphone</div>
                                <div class="ps-field" data-f="commune">Commune</div>
                            </div>
                            <div class="ps-buy" id="hpBuy">Commander · paiement à la livraison</div>
                        </div>
                        <div class="ps-done" id="hpDone"><span class="ok">{!! $i('check', 28) !!}</span><b>Commande envoyée !</b><small>La boutique t'appelle pour confirmer. Tu paies à la livraison.</small></div>
                    </div>
                    <span class="cursor" id="hpCur"></span>
                </div>
                <div class="toast toast-1" id="hpT1" aria-hidden="true"><span class="ti">{!! $i('box', 18) !!}</span><div><b>Nouvelle commande · 450 000 GNF</b><small>Aminata B. · Ratoma</small></div></div>
                <div class="toast toast-2" id="hpT2" aria-hidden="true"><span class="ti">{!! $i('whats', 18) !!}</span><div><b>Confirmée sur WhatsApp</b><small>Livraison prévue demain</small></div></div>
            </div>
        </div>
    </section>

<div class="band">
        <div class="wrap">
            <ul>
                <li>{!! $i('cash', 18) !!} Paiement à la livraison</li>
                <li>{!! $i('globe', 18) !!} Ton adresse /s/ta-boutique</li>
                <li>{!! $i('whats', 18) !!} Commandes et relances WhatsApp</li>
                <li>{!! $i('bike', 18) !!} Livreurs et entreprises de livraison</li>
                <li>{!! $i('phone', 18) !!} Pensé pour le téléphone</li>
            </ul>
        </div>
    </div>

    {{-- ═══ Pays et devises : la boutique prend la devise et l'indicatif du pays choisi à la création ═══ --}}
    @php
        $mqCur = fn ($c) => ['XOF' => 'FCFA', 'XAF' => 'FCFA', 'EUR' => '€', 'USD' => '$', 'CAD' => '$ CA'][$c] ?? $c;
        $mqList = collect(\App\Http\Controllers\Vendeur\ShopOnboardingController::COUNTRIES)
            ->map(fn ($v, $code) => [$code, $v[0], $mqCur(\App\Models\DeliveryCompany::currencyForCountry($code))]);
    @endphp
    <div class="mq" aria-labelledby="mqT">
        <p class="mq-t" id="mqT">Ta boutique dans ta devise, avec l'indicatif de ton pays</p>
        <div class="mq-vp">
            <div class="mq-track">
                @foreach([false, true] as $dup)
                    @foreach($mqList as [$code, $cname, $cur])
                    <span class="mq-c" @if($dup) aria-hidden="true" @endif><b>{{ $code }}</b>{{ $cname }} <small>{{ $cur }}</small></span>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>


    {{-- ═══ Avant / avec ═══ --}}
    <section class="sec" style="padding-bottom:0">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Pourquoi Shopio</span>
                <h2>Fini les commandes perdues dans WhatsApp</h2>
                <p>Vendre en ligne, ce n'est pas seulement poster des photos. C'est suivre chaque client jusqu'à la livraison.</p>
            </div>
            <div class="vs">
                <div class="vs-card vs-before rv">
                    <h3>Sans Shopio</h3>
                    <ul>
                        <li>{!! $i('x', 18) !!} Les commandes se perdent entre les messages</li>
                        <li>{!! $i('x', 18) !!} Les adresses sont notées dans un cahier</li>
                        <li>{!! $i('x', 18) !!} Les clients qui hésitent repartent, sans laisser de trace</li>
                        <li>{!! $i('x', 18) !!} Tu ne sais pas combien tu as vraiment encaissé</li>
                        <li>{!! $i('x', 18) !!} Tes livreurs t'appellent pour chaque adresse</li>
                    </ul>
                </div>
                <div class="vs-card vs-after rv">
                    <h3>Avec Shopio</h3>
                    <ul>
                        <li>{!! $i('check', 18) !!} Chaque commande arrive au même endroit, avec son étape</li>
                        <li>{!! $i('check', 18) !!} Nom, téléphone, commune et repère sont déjà remplis</li>
                        <li>{!! $i('check', 18) !!} Les paniers abandonnés te donnent un numéro à rappeler</li>
                        <li>{!! $i('check', 18) !!} Ton tableau de bord compte ce qui est livré et encaissé</li>
                        <li>{!! $i('check', 18) !!} Tu assignes un livreur, ton client suit sa commande</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ Démos ═══ --}}
    <section class="sec" id="demos">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Démos</span>
                <h2>Regarde Shopio en action</h2>
                <p>Six petites démos de ce que tu fais au quotidien. Elles défilent toutes seules, ou touche celle qui t'intéresse.</p>
            </div>

            <div class="demos rv">
                <div class="dtabs" role="tablist" aria-label="Démos">
                    @foreach([
                        ['create', 'store', 'Créer sa boutique', '8 petites questions, et c’est prêt'],
                        ['ai', 'spark', 'Produit avec Shopio IA', 'Une photo, l’IA écrit la page'],
                        ['sections', 'layers', 'Page produit en sections', 'Ajoute, déplace, c’est en ligne'],
                        ['orders', 'box', 'Commandes', 'Confirme, programme, livre'],
                        ['carts', 'cart', 'Paniers abandonnés', 'Rappelle ceux qui sont partis'],
                        ['themes', 'brush', 'Thèmes', 'Express ou Signature, en un clic'],
                    ] as $k => [$key, $icon, $title, $sub])
                    <button class="dtab" role="tab" id="tab-{{ $key }}" aria-controls="demo-{{ $key }}" aria-selected="{{ $k === 0 ? 'true' : 'false' }}" tabindex="{{ $k === 0 ? 0 : -1 }}" data-demo="{{ $key }}">
                        <span class="di">{!! $i($icon) !!}</span><b>{{ $title }}</b><small>{{ $sub }}</small><span class="bar"><i></i></span>
                    </button>
                    @endforeach
                </div>

                <div class="dview">
                    <div class="win">
                        <div class="win-bar"><span class="win-dots"><i></i><i></i><i></i></span><span class="win-url" id="dUrl">shopio · /boutique/shops/create</span></div>
                        <div class="win-body" aria-live="off">
                            {{-- 1. Création de boutique --}}
                            <div class="dpanel is-on" id="demo-create" role="tabpanel" aria-labelledby="tab-create" data-url="/boutique/shops/create">
                                <div class="ob"><div class="ob-box" id="obBox"></div></div>
                            </div>
                            {{-- 2. Shopio IA --}}
                            <div class="dpanel" id="demo-ai" role="tabpanel" aria-labelledby="tab-ai" data-url="/products/creer-avec-ia" hidden>
                                <div class="ai">
                                    <div class="ai-side">
                                        <div class="ai-photo is-empty" id="aiPhoto"><span class="ai-drop" id="aiDrop">{!! $i('upload', 18) !!}&nbsp;Dépose une photo</span><span class="ai-scan" id="aiScan"></span></div>
                                        <div class="ai-row">{!! $i('tag', 16) !!} Catégorie : <b id="aiCat">…</b></div>
                                        <div class="ai-row">{!! $i('cash', 16) !!} Ton prix : <b id="aiCost">…</b></div>
                                    </div>
                                    <div class="ai-out" id="aiOut"></div>
                                </div>
                            </div>
                            {{-- 3. Sections --}}
                            <div class="dpanel" id="demo-sections" role="tabpanel" aria-labelledby="tab-sections" data-url="/products/12/page-de-vente" hidden>
                                <div class="sx">
                                    <div class="sx-list" id="sxList"></div>
                                    <div class="sx-prev" id="sxPrev"></div>
                                </div>
                            </div>
                            {{-- 4. Commandes --}}
                            <div class="dpanel" id="demo-orders" role="tabpanel" aria-labelledby="tab-orders" data-url="/boutique/orders" hidden>
                                <p class="m-h">Commandes <span class="m-chip" style="margin-left:6px;height:22px;font-size:10.5px">● Données en direct</span></p>
                                <div class="od-chips" id="odChips"></div>
                                <p class="od-day">AUJOURD’HUI</p>
                                <div class="od-list" id="odList"></div>
                                <div class="od-wa" id="odWa"><b>WhatsApp · Aminata Bah</b><div class="bubble">Bonjour Aminata, ici Maison Kadi. Je confirme ta commande : 2 sacs Kadi, 450 000 GNF. Livraison demain ?</div></div>
                            </div>
                            {{-- 5. Paniers abandonnés --}}
                            <div class="dpanel" id="demo-carts" role="tabpanel" aria-labelledby="tab-carts" data-url="/boutique/paniers-abandonnes" hidden>
                                <div class="ca">
                                    <div class="ca-mini" id="caMini">
                                        <small>FORMULAIRE DE COMMANDE</small>
                                        <div class="ps-field" id="caName">Prénom et nom</div>
                                        <div class="ps-field" id="caPhone">Téléphone</div>
                                        <div class="ps-field">Commune</div>
                                        <div class="ps-buy" style="height:34px;font-size:12px">Commander</div>
                                        <div class="ca-leave" id="caLeave">Le client ferme la page<br>sans commander…</div>
                                    </div>
                                    <div>
                                        <div class="ca-stats">
                                            <div class="m-card ca-stat"><small>À rappeler</small><b id="caS1">2</b></div>
                                            <div class="m-card ca-stat"><small>À récupérer</small><b id="caS2">380 000</b></div>
                                            <div class="m-card ca-stat"><small>Récupérés</small><b id="caS3">5</b></div>
                                        </div>
                                        <div style="display:flex;gap:6px;margin-bottom:10px" id="caTabs"><span class="m-chip is-on">À rappeler</span><span class="m-chip">Injoignables</span><span class="m-chip">Récupérés</span></div>
                                        <div id="caList"></div>
                                    </div>
                                </div>
                            </div>
                            {{-- 6. Thèmes --}}
                            <div class="dpanel" id="demo-themes" role="tabpanel" aria-labelledby="tab-themes" data-url="/s/maison-kadi" hidden>
                                <div class="th">
                                    @foreach(['ex' => ['Express', 'Qui vend : packs, commande en 1 minute'], 'sg' => ['Signature', 'Premium clair : ivoire et vert profond']] as $t => [$tn, $td])
                                    <div class="th-site th-{{ $t }}" data-theme="{{ $t }}">
                                        <div class="th-hd"><i></i>Maison Kadi</div>
                                        <div class="th-cover"></div>
                                        <div class="th-grid">@for($n = 0; $n < 4; $n++)<div class="th-p"><div></div><span style="width:80%"></span><span style="width:45%"></span></div>@endfor</div>
                                        <div class="th-lbl"><span>{{ $tn }}</span><small style="font-weight:600;opacity:.7">{{ $td }}</small></div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <button type="button" class="replay" id="dReplay">{!! $i('replay', 14) !!} Rejouer</button>
                        </div>
                    </div>
                    <p class="dcap" id="dCap" aria-live="polite"></p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ Parcours d'une commande : le téléphone reste fixe, l'écran change avec chaque étape ═══ --}}
    @php
        $story = [
            ['Le client commande en une minute', 'Il choisit son pack, laisse son nom, son téléphone et sa commune. Pas de compte, pas de carte : il paiera à la livraison. Toi, tu reçois la commande tout de suite.'],
            ['Tu confirmes en un geste', 'Appel, WhatsApp ou SMS depuis la fiche de la commande, avec un message tout prêt. La commande passe à « Confirmée ».'],
            ['Tu assignes un livreur', 'Un de tes livreurs ou une entreprise de livraison partenaire. Il voit l’adresse, le repère et le montant à encaisser.'],
            ['Ton client suit sa livraison', 'Il reçoit un lien de suivi : il sait où en est sa commande, et toi tu évites dix appels.'],
            ['Livré, encaissé, compté', 'La commande passe à « Livrée » et ton tableau de bord compte ce que tu as vraiment encaissé.'],
        ];
        $screens = [
            '<h4>Commandes</h4><div class="sub">Aujourd’hui · en direct</div>'
              . '<div class="s-card is-hl"><div class="s-row"><b>Aminata Bah</b><span class="st st-new">Non traitée</span></div><div class="s-row s-mute"><span>2 × Sac Kadi</span><b>450 000 GNF</b></div><div class="s-mute">Ratoma · près de la pharmacie</div></div>'
              . '<div class="s-card"><div class="s-row"><b>Ibrahima Sow</b><span class="st st-ok">Confirmée</span></div><div class="s-row s-mute"><span>1 article</span><span>250 000 GNF</span></div></div>'
              . '<div class="s-card"><div class="s-row"><b>Fatoumata Camara</b><span class="st st-done">Livrée</span></div><div class="s-row s-mute"><span>1 article</span><span>120 000 GNF</span></div></div>',
            '<div class="s-row" style="justify-content:flex-start;gap:10px"><span class="s-av">AB</span><div><h4>Aminata Bah</h4><div class="sub" style="margin:0">WhatsApp</div></div></div>'
              . '<div class="s-wa me">Bonjour Aminata, ici Maison Kadi. Je confirme ta commande : 2 sacs Kadi, 450 000 GNF. Livraison demain ?</div>'
              . '<div class="s-wa">Oui demain c’est bon, merci 🙏</div>'
              . '<div class="s-card is-hl"><div class="s-row"><b>Commande #8</b><span class="st st-ok">Confirmée</span></div></div>',
            '<h4>Assigner un livreur</h4><div class="sub">Commande #8 · Ratoma</div>'
              . '<div class="s-card is-hl"><div class="s-row"><span style="display:flex;gap:10px;align-items:center"><span class="s-av">OD</span><b>Ousmane D.</b></span><span class="st st-done">Assigné</span></div></div>'
              . '<div class="s-card"><div class="s-row"><span style="display:flex;gap:10px;align-items:center"><span class="s-av">MK</span><b>Mohamed K.</b></span><span class="s-mute">Disponible</span></div></div>'
              . '<div class="s-card"><div class="s-row"><span style="display:flex;gap:10px;align-items:center"><span class="s-av">EX</span><b>Express Conakry</b></span><span class="s-mute">Entreprise</span></div></div>'
              . '<div class="s-card"><div class="s-row s-mute"><span>À encaisser</span><b style="color:var(--ink)">450 000 GNF</b></div></div>',
            '<h4>Suivi de ta commande</h4><div class="sub">Maison Kadi · #8</div>'
              . '<div class="s-map"><svg viewBox="0 0 270 150" preserveAspectRatio="none" aria-hidden="true"><path class="s-route" d="M20 130 C70 120 80 70 130 75 S200 30 250 24"/></svg><span class="s-dot"></span></div>'
              . '<div class="s-track"><div class="s-tr ok"><i></i><span>Commande confirmée<small>Hier, 15:02</small></span></div><div class="s-tr now"><i></i><span>En route avec Ousmane<small>Arrivée vers 10:30</small></span></div><div class="s-tr"><i></i><span>Livrée</span></div></div>',
            '<div class="s-big"><span class="ok">' . $i('check', 30) . '</span><b>450 000 GNF</b><span class="s-mute">encaissés à la livraison</span></div>'
              . '<div class="s-card"><div class="s-row"><b>Commande #8</b><span class="st st-done">Livrée</span></div></div>'
              . '<div class="s-card"><div class="s-row s-mute"><span>Revenus livrés ce mois</span><b style="color:var(--ink)">3 700 000 GNF</b></div></div>',
        ];
    @endphp
    <section class="sec" id="parcours" style="padding-bottom:40px">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Du clic à la livraison</span>
                <h2>Une commande, suivie de bout en bout</h2>
                <p>Voici ce qui se passe quand un client commande dans ta boutique.</p>
            </div>
            <div class="story">
                <div class="story-steps">
                    @foreach($story as $k => [$title, $text])
                    <div class="sstep {{ $k === 0 ? 'is-on' : '' }}" data-s="{{ $k }}">
                        <span class="n">{{ $k + 1 }}</span>
                        <h3>{{ $title }}</h3>
                        <p>{{ $text }}</p>
                        <div class="inline" aria-hidden="true"><div class="sscr">{!! $screens[$k] !!}</div></div>
                    </div>
                    @endforeach
                </div>
                <div class="story-dev" aria-hidden="true">
                    <div class="phone"><div class="screen">
                        @foreach($screens as $k => $html)<div class="sscr {{ $k === 0 ? 'is-on' : '' }}" data-s="{{ $k }}">{!! $html !!}</div>@endforeach
                    </div></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ Calculateur (paniers abandonnés) ═══ --}}
    <section class="sec calc-sec" id="calcul">
        <div class="wrap calc">
            <div class="rv">
                <span class="kicker">Paniers abandonnés</span>
                <h2>Combien de ventes laisses-tu partir chaque mois ?</h2>
                <p>Mets tes propres chiffres. Shopio te montre les clients qui ont laissé leur numéro sans commander : à toi de les rappeler.</p>
                <small class="note">Estimation calculée avec les chiffres que tu choisis, pas une promesse.</small>
            </div>
            <form class="calc-card rv" id="calc" onsubmit="return false">
                <div class="calc-f"><label for="cN">Paniers abandonnés par mois <output id="cNo" for="cN">60</output></label><input type="range" id="cN" min="5" max="500" step="5" value="60"></div>
                <div class="calc-f"><label for="cB">Panier moyen <output id="cBo" for="cB">250 000 GNF</output></label><input type="range" id="cB" min="25000" max="2000000" step="25000" value="250000"></div>
                <div class="calc-f"><label for="cR">Clients récupérés en les rappelant <output id="cRo" for="cR">20 %</output></label><input type="range" id="cR" min="5" max="60" step="5" value="20"></div>
                <div class="calc-res" aria-live="polite">
                    <div><small>Ventes récupérées</small><b id="cOut1">12</b></div>
                    <div class="is-main"><small>En plus, chaque mois</small><b id="cOut2">3 000 000 GNF</b></div>
                </div>
            </form>
        </div>
    </section>

    {{-- ═══ Fonctionnalités ═══ --}}
    <section class="sec" id="fonctionnalites" style="background:var(--soft)">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Fonctionnalités</span>
                <h2>Tout ce qu’il faut pour vendre, au même endroit</h2>
                <p>De la vitrine à la livraison, sans site à coder ni application à installer.</p>
            </div>
            <div class="feat">
                @foreach([
                    ['store', 'Ta vitrine en ligne', 'Une adresse /s/ta-boutique, deux thèmes et des sections que tu modifies toi-même.'],
                    ['layers', 'Pages produit qui vendent', 'Galerie, avantages, avis, packs à prix réduit et formulaire de commande en une page.'],
                    ['spark', 'Shopio IA', 'Une photo, et l’IA écrit le titre, la description et les arguments de vente (Plan Pro).'],
                    ['cash', 'Paiement à la livraison', 'Ton client commande en une minute et paie quand il reçoit, en GNF.'],
                    ['box', 'Commandes en 8 étapes', 'Non traitée, confirmée, programmée, en livraison, livrée… avec appel, WhatsApp et notes.'],
                    ['cart', 'Paniers abandonnés', 'Ceux qui laissent leur numéro sans commander apparaissent, prêts à être relancés.'],
                    ['bike', 'Livreurs et partenaires', 'Assigne tes livreurs ou une entreprise de livraison, et suis chaque colis.'],
                    ['pin', 'Suivi pour le client', 'Un lien de suivi pour que ton client sache où en est sa commande.'],
                    ['users', 'Équipe et closers', 'Donne un accès à ton équipe pour confirmer les commandes et rappeler les clients.'],
                    ['tag', 'Codes promo', 'Crée des réductions pour tes meilleurs clients ou tes campagnes.'],
                    ['chart', 'Marketing et pixel Meta', 'Liens de campagne suivis, pixel Meta et catalogue pour tes publicités.'],
                    ['qr', 'QR code et affiche', 'Imprime le QR code de ta boutique pour ton magasin ou tes sacs.'],
                ] as [$icon, $title, $text])
                <article class="fcard rv">
                    <span class="fi">{!! $i($icon) !!}</span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $text }}</p>
                </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══ Partage partout ═══ --}}
    <section class="sec" style="padding-bottom:0">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Ton lien partout</span>
                <h2>Un seul lien pour tous tes clients</h2>
                <p>Ta boutique a son adresse. Mets-la dans ta bio, tes statuts, tes publicités ou sur ton comptoir.</p>
            </div>
            <div class="share rv">
                <span>{!! $i('whats') !!} Statut et groupes WhatsApp</span>
                <span>{!! $i('chat') !!} Facebook et Messenger</span>
                <span>{!! $i('phone') !!} Bio Instagram et TikTok</span>
                <span>{!! $i('chart') !!} Publicités avec le pixel Meta</span>
                <span>{!! $i('qr') !!} QR code en magasin</span>
                <span>{!! $i('globe') !!} /s/ta-boutique</span>
                <span>{!! $i('store') !!} Marché Shopio</span>
            </div>
        </div>
    </section>

    {{-- ═══ Comparaison ═══ --}}
    <section class="sec" id="comparer" style="padding-bottom:0">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Comparer</span>
                <h2>WhatsApp seul, un site classique ou Shopio ?</h2>
                <p>Shopio est fait pour la vente payée à la livraison : de la commande jusqu'au livreur.</p>
            </div>
            @php
                $cmpRows = [
                    ['Ta boutique en ligne, avec ton adresse', 'n', 'y'],
                    ['Commande en une minute, payée à la livraison', 'À la main', 'Souvent paiement en ligne'],
                    ['Toutes les commandes au même endroit, par étape', 'n', 'Selon le site'],
                    ['Relance des paniers abandonnés, avec le numéro', 'n', 'Avec une extension'],
                    ['Livreurs, entreprises de livraison et suivi', 'n', 'n'],
                    ['Équipe avec des accès séparés', 'n', 'Selon l’offre'],
                    ['Prêt en quelques minutes, sans développeur', 'y', 'n'],
                ];
                $cmpCell = fn ($v) => match ($v) {
                    'y' => '<td class="y">' . $i('check', 18) . '<span class="sr">Oui</span></td>',
                    'n' => '<td class="n" aria-label="Non">—</td>',
                    default => '<td class="s">' . e($v) . '</td>',
                };
            @endphp
            <div class="cmp-wrap rv">
                <table class="cmp">
                    <thead><tr><th scope="col"><span class="sr">Fonction</span></th><th scope="col">WhatsApp seul</th><th scope="col">Site classique</th><th scope="col" class="is-us">Shopio</th></tr></thead>
                    <tbody>
                        @foreach($cmpRows as [$label, $wa, $site])
                        <tr><th scope="row" style="font-weight:600">{{ $label }}</th>{!! $cmpCell($wa) !!}{!! $cmpCell($site) !!}<td class="y is-us">{!! $i('check', 18) !!}<span class="sr">Oui</span></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ═══ Comment ça marche ═══ --}}
    <section class="sec">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Comment ça marche</span>
                <h2>En ligne aujourd’hui, en trois étapes</h2>
            </div>
            <div class="steps">
                <div class="step rv"><h3>Crée ta boutique</h3><p>Le nom, ton pays, ton numéro WhatsApp. Ta boutique a son adresse tout de suite.</p></div>
                <div class="step rv"><h3>Ajoute tes produits</h3><p>Une photo et un prix suffisent. Shopio IA peut écrire la page de vente pour toi.</p></div>
                <div class="step rv"><h3>Partage et livre</h3><p>Partage ton lien sur WhatsApp, Facebook ou TikTok. Les commandes arrivent, tu livres, ton client paie.</p></div>
            </div>
            @if($stats)
            <div class="stats rv">
                <div><b>{{ number_format($stats['shops'], 0, ',', ' ') }}</b><span>boutiques ouvertes</span></div>
                <div><b>{{ number_format($stats['products'], 0, ',', ' ') }}</b><span>produits en ligne</span></div>
                <div><b>{{ number_format($stats['delivered'], 0, ',', ' ') }}</b><span>commandes livrées</span></div>
            </div>
            @endif
        </div>
    </section>

    {{-- ═══ Témoignages : seulement ceux validés par l'équipe Shopio, avec les vraies commandes livrées ═══ --}}
    @if($testimonials->isNotEmpty())
    <section class="sec" id="avis" style="padding-bottom:0">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Ils vendent avec Shopio</span>
                <h2>Ce qu’en disent les vendeurs</h2>
                <p>Des avis de vraies boutiques Shopio, avec le nombre de commandes qu’elles ont livrées.</p>
            </div>
            <div class="tms {{ $testimonials->count() === 1 ? 'is-1' : ($testimonials->count() === 2 ? 'is-2' : '') }}">
                @foreach($testimonials as $tm)
                <figure class="tm rv" style="margin:0">
                    <span class="st" role="img" aria-label="{{ $tm['rating'] }} sur 5">{{ str_repeat('★', $tm['rating']) }}{{ str_repeat('☆', 5 - $tm['rating']) }}</span>
                    <blockquote style="margin:0;flex:1;display:flex"><q>{{ $tm['text'] }}</q></blockquote>
                    <figcaption class="tm-who">
                        <span class="tm-av">@if($tm['logo'])<img src="{{ $tm['logo'] }}" alt="" width="42" height="42" loading="lazy">@else{{ $tm['initials'] }}@endif</span>
                        <span>
                            <b>{{ $tm['author'] }}{{ $tm['role'] ? ', ' . $tm['role'] : '' }}</b>
                            <small><a href="{{ $tm['url'] }}">{{ $tm['shop'] }}</a>@if($tm['delivered'] > 0) · {{ number_format($tm['delivered'], 0, ',', ' ') }} commande{{ $tm['delivered'] > 1 ? 's' : '' }} livrée{{ $tm['delivered'] > 1 ? 's' : '' }}@endif</small>
                        </span>
                    </figcaption>
                </figure>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ═══ Tarifs (prix réels : config chapchappay.plans.pro, limites de SubscriptionService) ═══ --}}
    @php
        $gnf = fn ($n) => number_format($n, 0, ',', ' ');
        $perDay = (int) round($pro['price'] / max(1, $pro['days']));
        // [libellé, gratuit (true / false / texte), pro (true / texte)]
        $planRows = [
            ['Vitrine, adresse /s/ta-boutique et thèmes', true, true],
            ['Paiement à la livraison', true, true],
            ['Produits', $pro['products'] . ' au maximum', 'Sans limite'],
            ['Commandes traitées', $pro['orders'] . ' par mois', 'Sans limite'],
            ['Page Commandes et paniers abandonnés', true, true],
            ['Shopio IA et assistant de ventes IA', false, true],
            ['Équipe et closers', false, true],
            ['Livreurs, entreprises partenaires et carte GPS', false, true],
            ['Codes promo', false, true],
            ['Statistiques et rapports', false, true],
        ];
        $planCell = fn ($v) => $v === true ? $i('check', 16) . '<span class="sr">Inclus</span>' : ($v === false ? '<span class="n" aria-label="Non inclus">—</span>' : e($v));
    @endphp
    <section class="sec" id="tarifs" style="background:var(--soft)">
        <div class="wrap">
            <div class="sec-hd rv">
                <span class="kicker">Tarifs</span>
                <h2>Un prix simple, sans surprise</h2>
                <p>Commence gratuitement. Chaque nouvelle boutique reçoit {{ $trialDays }} jours de Plan Pro offerts, sans carte bancaire.</p>
            </div>
            <div class="plans">
                <div class="plan rv">
                    <h3>Gratuit</h3>
                    <p>Pour démarrer et faire tes premières ventes.</p>
                    <div class="price"><b>0</b><span>GNF</span><small>pour toujours</small></div>
                    <ul>
                        <li>{!! $i('check', 16) !!} Ta vitrine et ton adresse /s/…</li>
                        <li>{!! $i('check', 16) !!} Jusqu’à {{ $pro['products'] }} produits</li>
                        <li>{!! $i('check', 16) !!} {{ $pro['orders'] }} commandes traitées par mois</li>
                        <li>{!! $i('check', 16) !!} Commandes et paniers abandonnés</li>
                    </ul>
                    <a class="btn btn-line" href="{{ $cta }}">{{ $ctaLabel }}</a>
                </div>
                <div class="plan is-pro rv">
                    <span class="plan-flag">Recommandé pour vendre plus</span>
                    <h3>Pro <span>{{ $trialDays }} jours offerts</span></h3>
                    <p>Pour vendre plus et travailler en équipe.</p>
                    <div class="price"><b>{{ $gnf($pro['price']) }}</b><span>GNF / mois</span><small>soit environ {{ $gnf($perDay) }} GNF par jour</small></div>
                    <ul>
                        <li>{!! $i('check', 16) !!} Produits et commandes sans limite</li>
                        <li>{!! $i('check', 16) !!} Shopio IA et assistant de ventes IA</li>
                        <li>{!! $i('check', 16) !!} Équipe, livreurs, partenaires et carte GPS</li>
                        <li>{!! $i('check', 16) !!} Codes promo, statistiques et rapports</li>
                    </ul>
                    <a class="btn btn-main" href="{{ $cta }}">Essayer {{ $trialDays }} jours gratuits</a>
                    <small class="plan-note">Sans engagement · renouvellement manuel · paiement par Orange Money, MTN, PayCard ou carte Visa</small>
                </div>
            </div>

            <details class="ptable rv">
                <summary>Comparer les plans en détail</summary>
                <div class="cmp-wrap" style="margin-top:14px">
                    <table class="cmp">
                        <thead><tr><th scope="col"><span class="sr">Fonction</span></th><th scope="col">Gratuit</th><th scope="col" class="is-us">Pro · {{ $gnf($pro['price']) }} GNF / mois</th></tr></thead>
                        <tbody>
                            @foreach($planRows as [$label, $free, $paid])
                            <tr><th scope="row" style="font-weight:600">{{ $label }}</th><td class="{{ $free === true ? 'y' : ($free === false ? 'n' : 's') }}">{!! $planCell($free) !!}</td><td class="{{ $paid === true ? 'y' : 's' }} is-us">{!! $planCell($paid) !!}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    </section>

    {{-- ═══ Questions ═══ --}}
    <section class="sec" id="faq">
        <div class="wrap">
            <div class="sec-hd rv"><span class="kicker">Questions</span><h2>Tu te demandes peut-être…</h2></div>
            <div class="faq rv">
                <details><summary>Mes clients doivent-ils payer en ligne ?</summary><p>Non. Ton client commande en laissant son nom, son téléphone et sa commune, puis il paie à la livraison.</p></details>
                <details><summary>Faut-il un site internet ou un nom de domaine ?</summary><p>Non. Ta boutique a tout de suite son adresse Shopio, du type /s/ta-boutique, que tu partages où tu veux.</p></details>
                <details><summary>C’est quoi un panier abandonné ?</summary><p>Un client qui a rempli le formulaire de commande sans valider. Shopio te le montre avec son numéro, pour que tu puisses l’appeler ou lui écrire sur WhatsApp. Le formulaire le prévient que son numéro sert à la boutique.</p></details>
                <details><summary>Combien coûte le Plan Pro ?</summary><p>{{ number_format($pro['price'], 0, ',', ' ') }} GNF pour {{ $pro['days'] }} jours, soit environ {{ number_format((int) round($pro['price'] / max(1, $pro['days'])), 0, ',', ' ') }} GNF par jour. Les {{ $trialDays }} premiers jours sont offerts à chaque nouvelle boutique.</p></details>
                <details><summary>Comment je paie l’abonnement ?</summary><p>Depuis ton tableau de bord, par Orange Money, MTN, PayCard ou carte Visa. Le renouvellement est manuel : rien n’est prélevé sans toi.</p></details>
                <details><summary>Que se passe-t-il à la fin de l’essai ?</summary><p>Ta boutique repasse automatiquement sur le plan Gratuit ({{ $pro['products'] }} produits, {{ $pro['orders'] }} commandes traitées par mois). Tu ne perds ni tes produits ni tes commandes, et tu peux passer au Pro quand tu veux.</p></details>
                <details><summary>Shopio IA est-il inclus ?</summary><p>Shopio IA est réservé au Plan Pro, offert pendant {{ $trialDays }} jours à chaque nouvelle boutique.</p></details>
                <details><summary>Je peux travailler avec mon équipe ?</summary><p>Oui. Avec le Plan Pro, tu ajoutes tes employés et tes livreurs. Tu choisis si tes closers voient les paniers abandonnés.</p></details>
            </div>
            @if($whatsapp)<p class="faq-more rv">Une autre question ? <a href="{{ $whatsapp }}" target="_blank" rel="noopener">Écris-nous sur WhatsApp</a>, on te répond.</p>@endif
        </div>
    </section>

    <section class="final rv">
        <h2>Prêt à vendre en ligne ?</h2>
        <p>Crée ta boutique en quelques minutes. {{ $trialDays }} jours de Plan Pro offerts, sans carte bancaire. Ensuite : gratuit, ou {{ number_format($pro['price'], 0, ',', ' ') }} GNF par mois pour le Pro.</p>
        <div class="hero-cta">
            <a class="btn btn-main" href="{{ $cta }}">{{ $ctaLabel }} <span aria-hidden="true">→</span></a>
            <a class="btn btn-line" href="{{ route('welcome') }}">Voir le marché Shopio</a>
        </div>
    </section>
</main>

@if($whatsapp)
<a class="wa-fab" href="{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="Écrire au support Shopio sur WhatsApp"><i>{!! $i('whats', 18) !!}</i><span>Une question ?</span></a>
@endif
<div class="mcta" id="mcta"><a class="btn btn-main" href="{{ $cta }}">{{ $ctaLabel }} <span aria-hidden="true">→</span></a></div>


<footer class="foot">
    <div class="wrap">
        <a class="logo" href="{{ route('landing') }}"><img src="{{ $logo }}" alt="" width="28" height="28">Shopio</a>
        <span>© {{ now()->year }} Shopio</span>
        <nav aria-label="Liens">
            <a href="{{ route('welcome') }}">Marché</a>
            @guest<a href="{{ route('login') }}">Se connecter</a><a href="{{ $register }}">Créer ma boutique</a>@endguest
            @if($whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener">Support WhatsApp</a>@endif
            <a href="{{ route('legal.terms') }}">Conditions d’utilisation</a>
        </nav>
    </div>
</footer>

<script>
(function () {
    const $ = id => document.getElementById(id);
    const REDUCE = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ICON = @json(collect($ico)->map(fn ($d) => $d));
    const svg = (k, s = 16) => '<svg width="' + s + '" height="' + s + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' + ICON[k] + '"/></svg>';

    /* En-tête : bordure au défilement, menu mobile */
    const nav = $('nav');
    const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 8);
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
    $('navBurger').addEventListener('click', () => {
        const open = $('navMenu').classList.toggle('is-open');
        $('navBurger').setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    $('navMenu').addEventListener('click', e => { if (e.target.closest('a')) { $('navMenu').classList.remove('is-open'); $('navBurger').setAttribute('aria-expanded', 'false'); } });

    /* Apparition au défilement */
    const rv = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); rv.unobserve(e.target); } }), { threshold: .12 });
    document.querySelectorAll('.rv').forEach(el => rv.observe(el));

    /* Petit moteur de scènes : chaque démo est une suite d'étapes, annulée si on change de démo */
    function runner() {
        let token = 0;
        return {
            start(fn) { const t = ++token; const alive = () => t === token; const wait = ms => new Promise((res, rej) => setTimeout(() => alive() ? res() : rej('stop'), REDUCE ? Math.min(ms, 60) : ms)); fn(wait, alive).catch(() => {}); },
            stop() { token++; },
        };
    }
    async function type(el, text, wait, speed = 55) {
        el.classList.add('is-typing'); el.classList.remove('has-val');
        for (let k = 1; k <= text.length; k++) { el.textContent = text.slice(0, k); await wait(speed); }
        el.classList.remove('is-typing'); el.classList.add('has-val');
    }

    /* ─────────── Démo du héros ─────────── */
    const hero = runner();
    function playHero() {
        hero.start(async wait => {
            const body = $('hpBody'), cur = $('hpCur'), done = $('hpDone');
            const fields = [...body.querySelectorAll('.ps-field')], labels = ['Prénom et nom', 'Téléphone', 'Commune'];
            const tap = async (el) => {
                const ph = $('hpPhone'), pr = ph.getBoundingClientRect(), r = el.getBoundingClientRect(), z = pr.width / ph.offsetWidth || 1;
                cur.style.opacity = 1; cur.style.transform = 'translate(' + ((r.left - pr.left + r.width * .6) / z - 11) + 'px,' + ((r.top - pr.top + r.height / 2) / z - 11) + 'px)';
                await wait(650); cur.classList.remove('is-tap'); void cur.offsetWidth; cur.classList.add('is-tap'); await wait(250);
            };
            const ST = { n: ['st-new', 'Non traitée'], c: ['st-ok', 'Confirmée'], l: ['st-done', 'Livrée'] };
            const row = (name, amt, st, isNew) => '<div class="dash-o' + (isNew ? ' is-new' : '') + '"><b>' + name + '</b><span class="st ' + ST[st][0] + '">' + ST[st][1] + '</span><span>' + amt + ' GNF</span></div>';
            const fmtN = n => Math.round(n).toLocaleString('fr-FR').replace(/ | /g, ' ');
            const count = async (el, to, suffix) => { for (let k = 1; k <= 14; k++) { el.textContent = fmtN(to * k / 14) + suffix; await wait(45); } };
            for (;;) {
                $('dashList').innerHTML = row('Ibrahima Sow', '250 000', 'n') + row('Fatoumata Camara', '120 000', 'c') + row('Mamadou Diallo', '380 000', 'l');
                $('dashBadge').textContent = '2'; $('kCmd').textContent = '12';
                ['dashLine', 'dashArea'].forEach(id => { const e = $(id); e.classList.remove('is-on'); void e.getBoundingClientRect(); e.classList.add('is-on'); });
                count($('kRev'), 3250000, '').catch(() => {}); count($('kConf'), 82, ' %').catch(() => {}); count($('kLiv'), 91, ' %').catch(() => {});
                body.style.transform = ''; done.classList.remove('is-on'); $('hpT1').classList.remove('is-on'); $('hpT2').classList.remove('is-on');
                body.querySelectorAll('.ps-pack').forEach(p => p.classList.remove('is-on'));
                fields.forEach((f, k) => { f.textContent = labels[k]; f.classList.remove('has-val', 'is-typing'); });
                cur.style.opacity = 0; await wait(1200);
                await tap(body.querySelector('[data-pack="2"]')); body.querySelector('[data-pack="2"]').classList.add('is-on'); await wait(500);
                body.style.transform = 'translateY(-150px)'; await wait(700);
                await tap(fields[0]); await type(fields[0], 'Aminata Bah', wait);
                await tap(fields[1]); await type(fields[1], '622 45 18 90', wait, 45);
                await tap(fields[2]); await type(fields[2], 'Ratoma', wait);
                await tap($('hpBuy')); $('hpBuy').classList.add('is-press'); await wait(160); $('hpBuy').classList.remove('is-press');
                cur.style.opacity = 0; done.classList.add('is-on'); await wait(700);
                $('hpT1').classList.add('is-on');
                $('dashList').insertAdjacentHTML('afterbegin', row('Aminata Bah', '450 000', 'n', true)); $('dashList').lastElementChild.remove();
                $('dashBadge').textContent = '3'; $('kCmd').textContent = '13'; await wait(2000);
                $('hpT2').classList.add('is-on');
                const first = $('dashList').firstElementChild.querySelector('.st'); first.className = 'st st-ok'; first.textContent = 'Confirmée';
                $('dashBadge').textContent = '2'; await wait(3600);
            }
        });
    }

    /* ─────────── Calculateur (chiffres choisis par le visiteur) ─────────── */
    const fmt = n => Math.round(n).toLocaleString('fr-FR').replace(/ | /g, ' ');
    function calc() {
        const n = +$('cN').value, b = +$('cB').value, r = +$('cR').value;
        $('cNo').textContent = n; $('cBo').textContent = fmt(b) + ' GNF'; $('cRo').textContent = r + ' %';
        const won = Math.round(n * r / 100);
        $('cOut1').textContent = fmt(won); $('cOut2').textContent = fmt(won * b) + ' GNF';
    }
    ['cN', 'cB', 'cR'].forEach(id => $(id).addEventListener('input', calc)); calc();

    /* ─────────── Bouton fixe sur téléphone : après le héros, caché sur l'appel final ─────────── */
    const mcta = $('mcta'); let pastHero = false, onFinal = false;
    const upd = () => mcta.classList.toggle('is-on', pastHero && !onFinal);
    new IntersectionObserver(es => es.forEach(e => { pastHero = !e.isIntersecting && e.boundingClientRect.top < 0; upd(); })).observe(document.querySelector('.hero-cta'));
    new IntersectionObserver(es => es.forEach(e => { onFinal = e.isIntersecting; upd(); })).observe(document.querySelector('.final'));

    /* ─────────── Parcours d'une commande : l'étape au milieu de l'écran pilote le téléphone ─────────── */
    const sSteps = [...document.querySelectorAll('.sstep')], sScr = [...document.querySelectorAll('.story-dev .sscr')];
    const setStory = k => {
        sSteps.forEach(s => s.classList.toggle('is-on', +s.dataset.s === k));
        sScr.forEach(s => s.classList.toggle('is-on', +s.dataset.s === k));
    };
    const sObs = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) setStory(+e.target.dataset.s); }), { rootMargin: '-45% 0px -45% 0px' });
    sSteps.forEach(s => sObs.observe(s));

    /* ─────────── Héros : léger effet 3D qui suit la souris ─────────── */
    const shot = document.querySelector('.shot'), dash = document.querySelector('.shot .dash');
    if (shot && dash && !REDUCE && window.matchMedia('(hover: hover) and (pointer: fine) and (min-width: 961px)').matches) {
        let raf = 0;
        shot.addEventListener('mousemove', e => {
            cancelAnimationFrame(raf);
            raf = requestAnimationFrame(() => {
                const r = shot.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
                dash.style.transform = 'rotateY(' + (x * 6).toFixed(2) + 'deg) rotateX(' + (-y * 5).toFixed(2) + 'deg)';
            });
        });
        shot.addEventListener('mouseleave', () => { dash.style.transform = ''; });
    }

    /* ─────────── Menu : section visible mise en avant ─────────── */
    const navA = [...document.querySelectorAll('.nav-links a[href^="#"]')];
    const navObs = new IntersectionObserver(es => es.forEach(e => {
        if (!e.isIntersecting) return;
        navA.forEach(a => { const on = a.getAttribute('href') === '#' + e.target.id; a.classList.toggle('is-on', on); if (on) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current'); });
    }), { rootMargin: '-40% 0px -55% 0px' });
    navA.forEach(a => { const t = document.querySelector(a.getAttribute('href')); if (t) navObs.observe(t); });

    /* ─────────── Démos ─────────── */
    const demo = runner();
    const CAPS = {
        create:   '<b>Créer sa boutique</b> : une question par écran (nom, expérience, ventes, équipe, livraison, pays, WhatsApp), puis Shopio prépare tout.',
        ai:       '<b>Shopio IA</b> : dépose une photo, donne ton prix. L’IA reconnaît le produit et écrit la page de vente. Tu relis et tu publies.',
        sections: '<b>Page produit en sections</b> : ajoute une section (avis, packs, FAQ…), déplace-la, et la page se met à jour.',
        orders:   '<b>Commandes</b> : chaque commande passe par ses étapes. Appel, WhatsApp et SMS en un geste, avec un message tout prêt.',
        carts:    '<b>Paniers abandonnés</b> : le client remplit le formulaire puis s’en va. Il apparaît dans « À rappeler » ; tu le relances, il commande.',
        themes:   '<b>Thèmes</b> : passe d’Express à Signature sans rien perdre. Tes produits et tes sections suivent.',
    };

    const D = {};
    D.create = async (wait) => {
        const box = $('obBox');
        const dots = n => '<div class="ob-dots">' + Array.from({ length: 8 }, (_, k) => '<i class="' + (k === n ? 'on' : (k < n ? 'done' : '')) + '"></i>').join('') + '</div>';
        const head = (t, s, logo) => (logo ? '<img class="ob-logo" src="{{ $logo }}" alt="">' : '') + '<p class="m-h">' + t + '</p><p class="m-sub">' + s + '</p>';
        box.innerHTML = '<div class="fade-in">' + head('Bienvenue sur Shopio', 'Crée ta boutique et commence à vendre en quelques minutes.', true) + '<div class="m-btn" style="width:100%;height:42px" id="obGo">Commencer</div>' + dots(0) + '</div>';
        await wait(1100); $('obGo').classList.add('is-press'); await wait(200);
        box.innerHTML = '<div class="fade-in">' + head('Comment s’appelle ta boutique ?', 'Tu pourras le changer plus tard', true) + '<div class="ob-in"><span id="obName" class="caret"></span></div><p class="ob-hint" id="obSlug">&nbsp;</p><div class="m-btn" style="width:100%;height:42px">Continuer →</div>' + dots(1) + '</div>';
        const name = 'Maison Kadi';
        for (let k = 1; k <= name.length; k++) { $('obName').textContent = name.slice(0, k); if (k > 2) $('obSlug').innerHTML = 'Ton adresse : <b>/s/' + name.slice(0, k).toLowerCase().replace(/\s+/g, '-') + '</b>'; await wait(85); }
        await wait(700);
        const ask = async (n, t, s, opts, pick) => {
            box.innerHTML = '<div class="fade-in">' + head(t, s) + opts.map((o, k) => '<div class="ob-opt" data-k="' + k + '">' + svg(o[0], 18) + o[1] + '</div>').join('') + dots(n) + '</div>';
            await wait(1000); box.querySelector('[data-k="' + pick + '"]').classList.add('is-on'); await wait(650);
        };
        await ask(2, 'Ton expérience e-commerce', 'Pour mieux t’accompagner', [['sprout', 'Je débute dans le e-commerce'], ['phone', 'Je vends sur les réseaux sociaux'], ['store', 'J’ai déjà une boutique en ligne']], 1);
        await ask(5, 'Comment tu gères la livraison ?', 'On adapte les options à ta situation', [['chat', 'Pas encore de solution'], ['box', 'Une entreprise de livraison'], ['bike', 'Mes propres livreurs'], ['users', 'Les deux']], 2);
        box.innerHTML = '<div class="fade-in">' + head('Ton numéro WhatsApp', 'Tes clients pourront t’écrire sur WhatsApp') + '<div class="ob-in" style="justify-content:start;padding:0 14px;display:flex;gap:6px"><span style="color:var(--ink-2)">+224</span><span id="obWa" class="caret"></span></div><p class="ob-hint">&nbsp;</p><div class="m-btn" style="width:100%;height:42px" id="obMake">Créer ma boutique →</div>' + dots(7) + '</div>';
        const wa = '622 12 34 56'; for (let k = 1; k <= wa.length; k++) { $('obWa').textContent = wa.slice(0, k); await wait(70); }
        await wait(500); $('obMake').classList.add('is-press'); await wait(250);
        const tasks = ['Réservation de ton adresse', 'Création de la boutique', 'Mise en place de ta vitrine', 'Préparation du tableau de bord'];
        box.innerHTML = '<div class="fade-in" style="text-align:left"><p class="m-h">Création en cours…</p><p class="m-sub">Ça prend quelques secondes, on met tout en place pour toi.</p>' + tasks.map((t, k) => '<div class="ob-task" data-t="' + k + '"><span class="s"></span>' + t + '</div>').join('') + '</div>';
        for (let k = 0; k < tasks.length; k++) {
            const el = box.querySelector('[data-t="' + k + '"]'); el.classList.add('run'); await wait(650);
            el.classList.remove('run'); el.classList.add('ok'); el.querySelector('.s').innerHTML = svg('check', 11);
        }
        await wait(600);
        box.innerHTML = '<div class="fade-in"><span class="ps-done is-on" style="position:static;padding:0"><span class="ok">' + svg('check', 26) + '</span><b>Maison Kadi est en ligne</b><small>shopio · /s/maison-kadi · {{ $trialDays }} jours de Pro offerts</small></span></div>';
        await wait(2600);
    };

    D.ai = async (wait) => {
        const ph = $('aiPhoto'), out = $('aiOut');
        ph.classList.add('is-empty'); ph.querySelector('.ai-bag')?.remove(); $('aiDrop').style.opacity = 1;
        $('aiCat').textContent = '…'; $('aiCost').textContent = '…'; out.innerHTML = '<span class="ai-badge">' + svg('spark', 14) + ' Shopio IA</span><p class="ai-text" style="color:var(--ink-3)">Ajoute une photo de ton produit : l’IA s’occupe du texte.</p>';
        await wait(1200);
        ph.classList.remove('is-empty'); $('aiDrop').style.opacity = 0; ph.insertAdjacentHTML('afterbegin', '<span class="ai-bag">' + @json($bag) + '</span>');
        $('aiScan').classList.remove('is-on'); void ph.offsetWidth; $('aiScan').classList.add('is-on');
        out.innerHTML = '<span class="ai-badge">' + svg('spark', 14) + ' L’IA regarde ta photo…</span>';
        await wait(2400);
        $('aiCat').textContent = 'Sacs et maroquinerie'; await wait(400); $('aiCost').textContent = '250 000 GNF'; await wait(500);
        out.innerHTML = '<span class="ai-badge">' + svg('spark', 14) + ' Page de vente écrite</span><p class="ai-title" id="aiT"></p><p class="ai-text" id="aiX"></p><div id="aiL" style="display:grid;gap:6px"></div><div class="ai-price" id="aiP"></div>';
        const tt = 'Sac Kadi en cuir, élégant et solide';
        for (let k = 1; k <= tt.length; k++) { $('aiT').textContent = tt.slice(0, k); await wait(28); }
        const tx = 'Un sac en cuir souple qui va avec tout : bureau, cérémonie ou sortie. Assez grand pour ton téléphone, ton portefeuille et tes clés.';
        for (let k = 1; k <= tx.length; k += 3) { $('aiX').textContent = tx.slice(0, k); await wait(18); }
        $('aiX').textContent = tx;
        for (const l of ['Cuir de qualité, coutures solides', 'Fermeture sécurisée', 'Livré chez toi, payé à la livraison']) { $('aiL').insertAdjacentHTML('beforeend', '<div class="ai-li fade-in">' + svg('check', 15) + l + '</div>'); await wait(380); }
        $('aiP').innerHTML = '<span class="fade-in is-on">1 sac · 250 000 GNF</span><span class="fade-in">2 sacs · 450 000 GNF</span>';
        await wait(800);
        $('aiP').insertAdjacentHTML('afterend', '<div class="fade-in" style="display:flex;gap:6px;margin-top:4px"><span class="m-btn">Publier la page</span><span class="m-btn is-ghost">Modifier</span></div>');
        await wait(3000);
    };

    D.sections = async (wait) => {
        const L = $('sxList'), P = $('sxPrev');
        const S = {
            gallery: ['Galerie photos', 'layers', '<h5>Galerie</h5><div class="sx-img"></div>'],
            title:   ['Titre et prix', 'tag', '<h5>Titre et prix</h5><div class="sx-ln" style="width:70%;height:10px;background:#CBD5E1"></div><div class="sx-ln" style="width:35%"></div>'],
            benefits:['Avantages', 'check', '<h5>Avantages</h5><div class="sx-ln" style="width:85%"></div><div class="sx-ln" style="width:70%"></div><div class="sx-ln" style="width:78%"></div>'],
            packs:   ['Packs à prix réduit', 'cash', '<h5>Packs</h5><div class="sx-row"><div class="sx-ln" style="height:26px;border-radius:7px"></div><div class="sx-ln" style="height:26px;border-radius:7px;background:var(--accent-100)"></div></div>'],
            reviews: ['Avis clients', 'chat', '<h5>Avis clients</h5><div class="sx-row"><div class="sx-ln" style="height:30px;border-radius:7px"></div><div class="sx-ln" style="height:30px;border-radius:7px"></div></div>'],
            form:    ['Formulaire de commande', 'box', '<h5>Formulaire</h5><div class="sx-ln" style="height:14px"></div><div class="sx-ln" style="height:14px"></div><div class="sx-ln" style="height:16px;background:var(--accent)"></div>'],
        };
        let order = ['gallery', 'title', 'benefits', 'form'];
        const draw = (hl, add) => {
            L.innerHTML = order.map(k => '<div class="sx-item' + (k === hl ? ' is-drag' : '') + (k === add ? ' fade-in' : '') + '">' + svg('grip', 15) + svg(S[k][1], 15) + S[k][0] + '</div>').join('') + '<div class="sx-add" id="sxAdd">+ Ajouter une section</div>';
            P.innerHTML = order.map(k => '<div class="sx-blk' + (k === add ? ' fade-in' : '') + '">' + S[k][2] + '</div>').join('');
        };
        draw(); await wait(1400);
        $('sxAdd').style.borderColor = 'var(--accent)'; $('sxAdd').style.color = 'var(--accent)'; await wait(500);
        order.splice(3, 0, 'reviews'); draw(null, 'reviews'); await wait(1500);
        $('sxAdd').style.borderColor = 'var(--accent)'; $('sxAdd').style.color = 'var(--accent)'; await wait(500);
        order.splice(2, 0, 'packs'); draw(null, 'packs'); await wait(1500);
        draw('reviews'); await wait(700);
        order = ['gallery', 'title', 'reviews', 'packs', 'benefits', 'form']; draw('reviews'); await wait(900);
        draw(); await wait(2600);
    };

    D.orders = async (wait) => {
        const chips = $('odChips'), list = $('odList'), wa = $('odWa');
        const C = { all: ['Tous', 4], nouvelle: ['Non traitée', 2], confirmee: ['Confirmée', 1], livraison: ['En livraison', 0], livree: ['Livrée', 1] };
        const drawChips = on => { chips.innerHTML = Object.entries(C).map(([k, [l, n]]) => '<span class="m-chip' + (k === on ? ' is-on' : '') + '">' + l + ' <b>' + n + '</b></span>').join(''); };
        const ST = { nouvelle: ['st-new', 'Non traitée'], confirmee: ['st-ok', 'Confirmée'], programmee: ['st-grey', 'Programmée'], livraison: ['st-go', 'En livraison'], livree: ['st-done', 'Livrée'] };
        const cards = [
            ['Aminata Bah', '450 000', '2 articles', '14:55', 'nouvelle'],
            ['Ibrahima Sow', '250 000', '1 article', '14:20', 'nouvelle'],
            ['Fatoumata Camara', '120 000', '1 article', '11:02', 'confirmee'],
        ];
        const drawCards = () => {
            list.innerHTML = cards.map((c, k) => '<div class="m-card od-card" data-c="' + k + '"><b>' + c[0] + '</b><span class="st ' + ST[c[4]][0] + '" data-st>' + ST[c[4]][1] + ' ▾</span><div class="od-meta"><span>Montant <strong>' + c[1] + ' GNF</strong></span><span>' + c[2] + '</span><span>' + c[3] + '</span></div><div class="od-acts"><span class="m-btn is-ghost">Appeler</span><span class="m-btn is-ghost" data-wa>WhatsApp</span><span class="m-btn is-ghost">SMS</span><span class="m-btn is-ghost">Copier</span></div></div>').join('');
        };
        const setSt = (k, s) => { cards[k][4] = s; const el = list.querySelector('[data-c="' + k + '"] [data-st]'); el.className = 'st ' + ST[s][0]; el.textContent = ST[s][1] + ' ▾'; };
        wa.classList.remove('is-on'); drawChips('all'); drawCards(); await wait(1300);
        const c0 = list.querySelector('[data-c="0"]'); c0.classList.add('is-hl', 'is-open'); await wait(1000);
        c0.querySelector('[data-wa]').classList.add('is-press'); await wait(200); c0.querySelector('[data-wa]').classList.remove('is-press');
        wa.classList.add('is-on'); await wait(2200); wa.classList.remove('is-on');
        setSt(0, 'confirmee'); C.nouvelle[1] = 1; C.confirmee[1] = 2; drawChips('confirmee'); await wait(1400);
        c0.classList.remove('is-open');
        setSt(0, 'livraison'); C.confirmee[1] = 1; C.livraison[1] = 1; drawChips('livraison'); await wait(1400);
        setSt(0, 'livree'); C.livraison[1] = 0; C.livree[1] = 2; drawChips('livree'); await wait(1200);
        c0.classList.remove('is-hl');
        const c1 = list.querySelector('[data-c="1"]'); c1.classList.add('is-hl'); await wait(700);
        setSt(1, 'programmee'); c1.querySelector('.od-meta').insertAdjacentHTML('beforeend', '<span class="fade-in"><strong>Prévue demain à 10:30</strong></span>'); C.nouvelle[1] = 0; drawChips('all'); await wait(2400);
        c1.classList.remove('is-hl'); await wait(800);
        Object.assign(C, { all: ['Tous', 4], nouvelle: ['Non traitée', 2], confirmee: ['Confirmée', 1], livraison: ['En livraison', 0], livree: ['Livrée', 1] });
        cards[0][4] = 'nouvelle'; cards[1][4] = 'nouvelle';
    };

    D.carts = async (wait) => {
        const list = $('caList'), leave = $('caLeave');
        const n = $('caName'), p = $('caPhone');
        n.textContent = 'Prénom et nom'; p.textContent = 'Téléphone'; n.classList.remove('has-val'); p.classList.remove('has-val'); leave.classList.remove('is-on');
        $('caS1').textContent = '2'; $('caS2').textContent = '380 000'; $('caS3').textContent = '5';
        const card = (name, ph, amt, when, extra = '') => '<div class="m-card ca-card fade-in" ' + extra + '><div class="top"><b>' + name + '</b><span>' + when + '</span></div><div class="od-meta"><span>' + ph + '</span><span><strong>' + amt + ' GNF</strong> · Sac Kadi</span></div><div class="ca-acts"><span class="m-btn is-ghost">Appeler</span><span class="m-btn is-ghost" data-wa>WhatsApp</span><span class="m-btn" data-make>Créer la commande</span></div></div>';
        list.innerHTML = card('Mariama Diallo', '628 •• •• 41', '130 000', 'il y a 2 h');
        await wait(900);
        await type(n, 'Kadiatou Barry', wait, 60); await type(p, '621 33 •• ••', wait, 60);
        await wait(500); leave.classList.add('is-on'); await wait(1600);
        list.insertAdjacentHTML('afterbegin', card('Kadiatou Barry', '621 33 •• ••', '250 000', 'à l’instant', 'data-new'));
        $('caS1').textContent = '3'; $('caS2').textContent = '630 000'; await wait(1300);
        const nc = list.querySelector('[data-new]'); nc.style.boxShadow = 'inset 0 0 0 2px var(--accent)';
        nc.querySelector('[data-wa]').classList.add('is-press'); await wait(200); nc.querySelector('[data-wa]').classList.remove('is-press');
        nc.insertAdjacentHTML('beforeend', '<div class="od-wa is-on fade-in" style="position:static;width:auto;box-shadow:inset 0 0 0 1px var(--line)"><b>WhatsApp</b><div class="bubble">Bonjour Kadiatou, ici Maison Kadi 👋 Ton Sac Kadi t’attend. Je te le réserve ?</div></div>');
        await wait(2300);
        nc.querySelector('[data-make]').classList.add('is-press'); await wait(200);
        nc.style.opacity = '.4'; await wait(400); nc.remove();
        $('caS1').textContent = '2'; $('caS2').textContent = '380 000'; $('caS3').textContent = '6';
        list.insertAdjacentHTML('afterbegin', '<div class="m-card ca-card fade-in" style="background:var(--ok-50)"><div class="top"><b>Kadiatou Barry</b><span class="st st-done">Récupéré</span></div><div class="od-meta"><span>Commande créée · <strong>250 000 GNF</strong></span></div></div>');
        await wait(3000);
    };

    D.themes = async (wait) => {
        const s = [...document.querySelectorAll('.th-site')];
        const on = t => s.forEach(el => { el.classList.toggle('is-on', el.dataset.theme === t); el.classList.toggle('is-off', el.dataset.theme !== t); });
        s.forEach(el => el.classList.remove('is-on', 'is-off')); await wait(900);
        on('ex'); await wait(2200); on('sg'); await wait(2200); on('ex'); await wait(1800);
    };

    /* Onglets des démos : défilement automatique, clavier, arrêt hors écran */
    const tabs = [...document.querySelectorAll('.dtab')];
    let current = 0, inView = false, auto = true, barAnim = null;
    function show(idx, user) {
        if (user) auto = false;
        current = (idx + tabs.length) % tabs.length;
        tabs.forEach((t, k) => { t.setAttribute('aria-selected', k === current ? 'true' : 'false'); t.tabIndex = k === current ? 0 : -1; t.querySelector('.bar i').style.width = '0'; });
        document.querySelectorAll('.dpanel').forEach(p => { const on = p.id === 'demo-' + tabs[current].dataset.demo; p.classList.toggle('is-on', on); p.hidden = !on; });
        const panel = $('demo-' + tabs[current].dataset.demo);
        $('dUrl').textContent = 'shopio · ' + panel.dataset.url;
        $('dCap').innerHTML = CAPS[tabs[current].dataset.demo];
        if (window.innerWidth < 960 && user) tabs[current].scrollIntoView({ block: 'nearest', inline: 'center', behavior: REDUCE ? 'auto' : 'smooth' });
        play();
    }
    function play() {
        if (barAnim) barAnim.cancel();
        if (!inView) { demo.stop(); return; }
        const key = tabs[current].dataset.demo, bar = tabs[current].querySelector('.bar i'), t0 = performance.now();
        demo.start(async (wait, alive) => {
            await D[key](wait);
            if (alive() && auto) show(current + 1); else if (alive()) play();
        });
        // Barre de progression indicative (durée moyenne d'une démo)
        if (!REDUCE && bar.animate) barAnim = bar.animate([{ width: '0%' }, { width: '100%' }], { duration: 14000, fill: 'forwards', easing: 'linear' });
    }
    tabs.forEach((t, k) => {
        t.addEventListener('click', () => show(k, true));
        t.addEventListener('keydown', e => {
            const d = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[e.key];
            if (d) { e.preventDefault(); show(current + d, true); tabs[current].focus(); }
        });
    });
    $('dReplay').addEventListener('click', () => play());
    new IntersectionObserver(es => es.forEach(e => { inView = e.isIntersecting; play(); }), { threshold: .25 }).observe(document.querySelector('.dview'));
    new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) playHero(); else hero.stop(); }), { threshold: .2 }).observe(document.querySelector('.stage'));
    document.addEventListener('visibilitychange', () => { if (document.hidden) { demo.stop(); hero.stop(); } else { play(); playHero(); } });
    show(0);
})();
</script>
</body>
</html>
