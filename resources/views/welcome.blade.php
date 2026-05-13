{{--
=====================================================
WELCOME.BLADE.PHP — Page d'accueil moderne & attractive
=====================================================
Variables injectées depuis WelcomeController :
  $shops       → Collection<Shop>  (boutiques approuvées, paginées)
  $companies   → Collection<DeliveryCompany>
  $stats       → object { total_shops, total_orders, total_clients, total_livreurs }
=====================================================
--}}

@extends('layouts.app')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ════════════════════════════════════════════════════════════════
   VARIABLES & BASE
════════════════════════════════════════════════════════════════ */
:root {
    --green:     #6366f1;
    --green-dk:  #4f46e5;
    --green-lt:  #e0e7ff;
    --green-mlt: #eef2ff;
    --violet:    #8b5cf6;
    --indigo-lt: #a5b4fc;
    --dark:      #0a0a1e;
    --dark-2:    #0f0f3a;
    --text:      #0f172a;
    --text-2:    #475569;
    --muted:     #94a3b8;
    --surface:   #ffffff;
    --border:    #e2e8f0;
    --font:      'Plus Jakarta Sans', sans-serif;
    --display:   'Clash Display', 'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r:         16px;
    --r-sm:      10px;
}

*, *::before, *::after { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
    font-family: var(--font);
    background: #f8fafc;
    color: var(--text);
    margin: 0;
    -webkit-font-smoothing: antialiased;
}

/* ════════════════════════════════════════════════════════════════
   NAVBAR OVERRIDE — transparente sur le hero
════════════════════════════════════════════════════════════════ */
.navbar { display: none !important; } /* On a notre propre nav */

/* ── Notre navbar custom ── */
.top-nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    padding: 0 40px;
    height: 64px;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(10,10,30,.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(99,102,241,.12);
    transition: background .3s;
}
.nav-brand {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; color: #fff;
    font-size: 18px; font-weight: 700; letter-spacing: -.3px;
}
.nav-brand-icon {
    width: 34px; height: 34px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    box-shadow: 0 2px 10px rgba(99,102,241,.4);
}
.nav-logo-img {
    height: 40px; width: 40px; object-fit: cover; border-radius: 10px;
    border: 2px solid rgba(170,40,217,.5);
    box-shadow: 0 0 0 3px rgba(41,29,149,.35), 0 4px 14px rgba(170,40,217,.35);
    flex-shrink: 0;
}
.nav-brand-name {
    background: linear-gradient(90deg, #c4b5fd, #e879f9);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 18px; font-weight: 800; letter-spacing: -.3px;
}
.nav-links {
    display: flex; align-items: center; gap: 8px;
}
.nav-link-item {
    padding: 8px 16px; border-radius: 8px;
    font-size: 13.5px; font-weight: 600;
    text-decoration: none; transition: all .15s;
    color: rgba(255,255,255,.7);
}
.nav-link-item:hover { background: rgba(255,255,255,.08); color: #fff; }
.nav-btn {
    padding: 8px 20px; border-radius: 8px;
    font-size: 13.5px; font-weight: 700;
    text-decoration: none; transition: all .15s;
    background: var(--green); color: #fff;
    border: 1px solid var(--green-dk);
}
.nav-btn:hover { background: var(--green-dk); color: #fff; }
.nav-btn-outline {
    background: transparent; border: 1px solid rgba(255,255,255,.2);
    color: rgba(255,255,255,.8);
}
.nav-btn-outline:hover { border-color: rgba(255,255,255,.5); color: #fff; background: rgba(255,255,255,.06); }

/* ════════════════════════════════════════════════════════════════
   HERO SECTION
════════════════════════════════════════════════════════════════ */
.hero-section {
    min-height: 100vh;
    background: linear-gradient(90deg, #100c31, #261360, #772595);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 100px 24px 80px;
    position: relative; overflow: hidden;
}

/* Grille de points en fond */
.hero-section::before {
    content: '';
    position: absolute; inset: 0;
    background-image:
        radial-gradient(circle at 1px 1px, rgba(255,255,255,.06) 1px, transparent 0);
    background-size: 32px 32px;
    pointer-events: none;
}

/* Halo indigo/violet derrière le titre */
.hero-glow {
    position: absolute;
    width: 700px; height: 700px;
    background: radial-gradient(circle, rgba(99,102,241,.22) 0%, rgba(139,92,246,.1) 45%, transparent 70%);
    top: 50%; left: 50%; transform: translate(-50%, -60%);
    pointer-events: none;
}

.hero-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(99,102,241,.14);
    border: 1px solid rgba(99,102,241,.3);
    color: #a5b4fc;
    font-size: 12px; font-weight: 700;
    padding: 6px 14px; border-radius: 20px;
    margin-bottom: 24px;
    animation: fadeDown .6s ease both;
}
.hero-badge-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #a5b4fc;
    box-shadow: 0 0 6px #a5b4fc;
    animation: blink 2s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

.hero-title {
    font-family: var(--display);
    font-size: clamp(42px, 7vw, 76px);
    font-weight: 700;
    color: #fff;
    text-align: center;
    line-height: 1.07;
    letter-spacing: -2px;
    max-width: 820px;
    margin: 0 auto 20px;
    animation: fadeDown .7s .1s ease both;
}
.hero-title span {
    background: linear-gradient(135deg, #a5b4fc, #6366f1, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-sub {
    font-size: 17px; color: rgba(255,255,255,.55);
    text-align: center; max-width: 520px;
    margin: 0 auto 36px; line-height: 1.7;
    font-weight: 400;
    animation: fadeDown .7s .2s ease both;
}

.hero-cta {
    display: flex; align-items: center; gap: 12px;
    flex-wrap: wrap; justify-content: center;
    animation: fadeDown .7s .3s ease both;
}
.cta-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 14px 28px; border-radius: 12px;
    font-size: 14.5px; font-weight: 700; font-family: var(--font);
    text-decoration: none; transition: all .2s;
    border: none; cursor: pointer;
}
.cta-primary {
    background: linear-gradient(135deg,#6366f1,#4f46e5); color: #fff;
    box-shadow: 0 4px 20px rgba(99,102,241,.45);
}
.cta-primary:hover {
    background: linear-gradient(135deg,#4f46e5,#3730a3); color: #fff;
    box-shadow: 0 6px 28px rgba(99,102,241,.55);
    transform: translateY(-1px);
}
.cta-secondary {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.15);
    color: rgba(255,255,255,.85);
}
.cta-secondary:hover {
    background: rgba(255,255,255,.12);
    color: #fff; transform: translateY(-1px);
}

/* ── Stats rapides sous le hero ── */
.hero-stats {
    display: flex; gap: 40px; flex-wrap: wrap;
    justify-content: center; margin-top: 56px;
    animation: fadeDown .7s .4s ease both;
}
.hero-stat { text-align: center; }
.hero-stat-val {
    font-size: 28px; font-weight: 700; color: #fff;
    font-family: var(--mono); letter-spacing: -1px;
    display: block;
}
.hero-stat-lbl {
    font-size: 12px; color: rgba(255,255,255,.4);
    font-weight: 500; display: block; margin-top: 3px;
}
.hero-stat-sep {
    width: 1px; background: rgba(255,255,255,.1);
    align-self: stretch;
}

/* ── Image dashboard mockup ── */
.hero-mockup {
    margin-top: 64px; width: 100%; max-width: 1020px;
    animation: fadeUp .9s .5s ease both;
    position: relative;
}

/* Halo lumineux derrière */
.hero-mockup::before {
    content: '';
    position: absolute;
    inset: -40px -60px;
    background: radial-gradient(ellipse at 50% 60%, rgba(99,102,241,.25) 0%, rgba(139,92,246,.1) 50%, transparent 75%);
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
}

/* Grille déco derrière */
.hero-mockup::after {
    content: '';
    position: absolute;
    inset: 20px -20px -20px;
    background-image:
        linear-gradient(rgba(99,102,241,.08) 1px, transparent 1px),
        linear-gradient(90deg, rgba(99,102,241,.08) 1px, transparent 1px);
    background-size: 36px 36px;
    border-radius: 20px;
    z-index: 0;
    mask-image: radial-gradient(ellipse at 50% 50%, black 30%, transparent 80%);
}

.hero-mockup-outer {
    position: relative; z-index: 1;
    transform: perspective(1600px) rotateX(3deg);
    transform-origin: center bottom;
    transition: transform .6s ease;
}
.hero-mockup-outer:hover { transform: perspective(1600px) rotateX(0deg); }

.hero-mockup-inner {
    position: relative;
    border-radius: 14px; overflow: hidden;
    box-shadow:
        0 0 0 1px rgba(255,255,255,.10),
        0 2px 0 rgba(255,255,255,.06),
        0 20px 60px rgba(0,0,0,.7),
        0 60px 120px rgba(0,0,0,.5),
        0 0 80px rgba(99,102,241,.18),
        inset 0 1px 0 rgba(255,255,255,.08);
}

/* Barre navigateur réaliste */
.hero-mockup-bar {
    background: linear-gradient(180deg, #252f3e 0%, #1e2a38 100%);
    height: 40px; display: flex; align-items: center; gap: 10px; padding: 0 16px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    position: relative;
}
.mockup-dots { display: flex; gap: 7px; flex-shrink: 0; }
.mockup-dot  { width: 11px; height: 11px; border-radius: 50%; }
.mockup-url  {
    flex: 1; max-width: 340px; margin: 0 auto;
    background: rgba(255,255,255,.07); border-radius: 6px;
    height: 24px; display: flex; align-items: center; padding: 0 10px; gap: 6px;
    font-size: 11px; color: rgba(255,255,255,.45); font-family: monospace;
    border: 1px solid rgba(255,255,255,.06);
}
.mockup-url-lock { font-size: 10px; color: #10b981; }
.mockup-actions { display: flex; gap: 4px; flex-shrink: 0; }
.mockup-action-btn {
    width: 24px; height: 18px; border-radius: 4px;
    background: rgba(255,255,255,.06); display: flex; align-items: center; justify-content: center;
    font-size: 9px; color: rgba(255,255,255,.3);
}

.hero-mockup img {
    width: 100%; display: block;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
    will-change: transform;
    filter: url(#img-sharpen) contrast(1.06) saturate(1.04);
    -webkit-filter: contrast(1.06) saturate(1.04);
    image-rendering: -webkit-optimize-contrast;
}

/* Cartes stat flottantes */
.mockup-float {
    position: absolute; z-index: 4;
    background: rgba(10, 10, 30, 0.9);
    backdrop-filter: blur(12px) saturate(1.4);
    -webkit-backdrop-filter: blur(12px) saturate(1.4);
    border: 1px solid rgba(99,102,241,.2);
    border-radius: 12px; padding: 10px 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.4), 0 0 0 1px rgba(99,102,241,.12);
    animation: floatCard 3s ease-in-out infinite;
    min-width: 140px;
}
.mockup-float.f1 { top: 14%; left: -60px; animation-delay: 0s; }
.mockup-float.f2 { top: 44%; right: -64px; animation-delay: 1s; }
.mockup-float.f3 { bottom: 12%; left: -48px; animation-delay: 2s; }
@keyframes floatCard {
    0%, 100% { transform: translateY(0px); }
    50%       { transform: translateY(-8px); }
}
.mf-label { font-size: 10px; color: rgba(255,255,255,.45); font-weight: 500; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .06em; }
.mf-val   { font-size: 18px; font-weight: 800; color: #fff; font-family: monospace; line-height: 1; }
.mf-val span { font-size: 10px; font-weight: 600; color: #a5b4fc; margin-left: 4px; }
.mf-ico   { font-size: 20px; margin-bottom: 4px; }
.mf-bar   { height: 3px; border-radius: 2px; background: rgba(255,255,255,.1); margin-top: 6px; overflow: hidden; }
.mf-bar-fill { height: 100%; border-radius: 2px; background: linear-gradient(90deg, #6366f1, #8b5cf6); }

/* Badge "LIVE" */
.mockup-live {
    position: absolute; top: -14px; right: 20px; z-index: 5;
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #fff; font-size: 10px; font-weight: 700; letter-spacing: .08em;
    padding: 4px 10px; border-radius: 20px;
    display: flex; align-items: center; gap: 5px;
    box-shadow: 0 4px 16px rgba(99,102,241,.45);
}
.mockup-live-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #fff; animation: pulse 1.4s ease infinite;
}
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

.hero-mockup-placeholder {
    background: linear-gradient(180deg, #0f0f2e 0%, #0a0a1e 100%);
    height: 480px; display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: 12px;
}
.hero-mockup-placeholder .ico { font-size: 48px; opacity: .4; }
.hero-mockup-placeholder p { font-size: 13px; color: rgba(255,255,255,.3); font-weight: 500; }

@media (max-width: 900px) {
    .mockup-float.f1, .mockup-float.f2, .mockup-float.f3 { display: none; }
    .hero-mockup-outer { transform: none !important; }
    .hero-mockup::after { display: none; }
}

/* Animations */
@keyframes fadeDown {
    from { opacity: 0; transform: translateY(-16px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* ════════════════════════════════════════════════════════════════
   SECTION COMMUNE
════════════════════════════════════════════════════════════════ */
.section { padding: 90px 24px; }
.section-inner { max-width: 1100px; margin: 0 auto; }
.section-badge {
    display: inline-block;
    background: var(--green-mlt); color: var(--green-dk);
    border: 1px solid var(--green-lt);
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1px; padding: 4px 12px; border-radius: 20px;
    margin-bottom: 14px;
}
.section-title {
    font-family: var(--display);
    font-size: clamp(28px, 4vw, 44px);
    font-weight: 700; color: var(--text);
    letter-spacing: -1px; margin: 0 0 12px;
    line-height: 1.15;
}
.section-title span { color: var(--green); }
.section-sub {
    font-size: 16px; color: var(--text-2);
    max-width: 520px; line-height: 1.7; margin: 0 0 48px;
}

/* ════════════════════════════════════════════════════════════════
   FEATURES — GRILLE ALTERNÉE
════════════════════════════════════════════════════════════════ */
.features-section { background: #fff; }

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.feature-card {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 28px 24px;
    transition: box-shadow .2s, border-color .2s, transform .2s;
    position: relative; overflow: hidden;
}
.feature-card:hover {
    box-shadow: 0 8px 32px rgba(99,102,241,.1);
    border-color: var(--green-lt);
    transform: translateY(-3px);
}
.feature-card::after {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
    opacity: 0; transition: opacity .2s;
}
.feature-card:hover::after { opacity: 1; }
.feature-ico {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; margin-bottom: 16px;
    background: var(--green-mlt); border: 1px solid var(--green-lt);
}
.feature-title { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
.feature-desc  { font-size: 13.5px; color: var(--text-2); line-height: 1.6; margin: 0; }

/* ════════════════════════════════════════════════════════════════
   COMMENT CA MARCHE — ÉTAPES
════════════════════════════════════════════════════════════════ */
.how-section { background: #f8fafc; }

.steps-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0; position: relative;
}
.steps-grid::before {
    content: '';
    position: absolute; top: 40px; left: 10%; right: 10%;
    height: 2px;
    background: linear-gradient(90deg, var(--green-lt), #6366f1, var(--green-lt));
    z-index: 0;
}
.step-item {
    display: flex; flex-direction: column;
    align-items: center; text-align: center;
    padding: 0 16px; position: relative; z-index: 1;
}
.step-num {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 800; font-family: var(--mono);
    background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff;
    box-shadow: 0 4px 16px rgba(99,102,241,.4);
    margin-bottom: 18px;
    border: 3px solid #fff;
}
.step-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
.step-desc  { font-size: 12.5px; color: var(--text-2); line-height: 1.6; }

/* ════════════════════════════════════════════════════════════════
   BOUTIQUES EN LIGNE — VITRINE
════════════════════════════════════════════════════════════════ */
.shops-section { background: #fff; }

.shops-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 36px;
}
.shop-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 18px;
    overflow: hidden;
    transition: all .25s;
    text-decoration: none;
    display: flex; flex-direction: column;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.shop-card:hover {
    box-shadow: 0 16px 48px rgba(99,102,241,.12);
    border-color: var(--green-lt);
    transform: translateY(-6px);
}
.shop-img-wrap {
    height: 180px; overflow: hidden;
    background: linear-gradient(135deg, #eef2ff, #f5f3ff);
    display: flex; align-items: center; justify-content: center;
    position: relative;
}
.shop-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .45s ease;
}
.shop-card:hover .shop-img-wrap img { transform: scale(1.07); }
.shop-img-placeholder { font-size: 52px; opacity: .55; }
.shop-img-wrap .shop-badge {
    position: absolute; top: 12px; right: 12px;
    background: rgba(99,102,241,.9); color: #fff;
    font-size: 10px; font-weight: 700;
    padding: 3px 10px; border-radius: 20px;
    backdrop-filter: blur(4px);
}
.shop-info { padding: 18px 20px 20px; flex: 1; display: flex; flex-direction: column; }
.shop-name { font-size: 16px; font-weight: 800; color: var(--text); margin-bottom: 5px; line-height: 1.25; }
.shop-meta { font-size: 12px; color: var(--muted); margin-bottom: 14px; }
.shop-cta {
    margin-top: auto;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 18px; border-radius: 10px;
    font-size: 13px; font-weight: 700;
    background: var(--green-mlt); color: var(--green-dk);
    border: 1.5px solid var(--green-lt);
    transition: all .15s;
}
.shop-card:hover .shop-cta {
    background: var(--green); color: #fff; border-color: var(--green-dk);
}

/* ════════════════════════════════════════════════════════════════
   TÉMOIGNAGES / SOCIAL PROOF
════════════════════════════════════════════════════════════════ */
.proof-section { background: var(--dark); padding: 80px 24px; }

.proof-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 20px; max-width: 1100px; margin: 0 auto;
}
.proof-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: var(--r);
    padding: 24px 22px;
    transition: background .2s;
}
.proof-card:hover { background: rgba(255,255,255,.07); }
.proof-stars { color: #fbbf24; font-size: 14px; margin-bottom: 12px; }
.proof-text {
    font-size: 14px; color: rgba(255,255,255,.7);
    line-height: 1.65; margin-bottom: 16px; font-style: italic;
}
.proof-author { display: flex; align-items: center; gap: 10px; }
.proof-av {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.proof-name  { font-size: 13px; font-weight: 700; color: #fff; }
.proof-role  { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 1px; }

/* ════════════════════════════════════════════════════════════════
   PRIX / PLANS
════════════════════════════════════════════════════════════════ */
.pricing-section { background: #f8fafc; }

.pricing-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.pricing-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 30px 26px;
    position: relative; overflow: hidden;
    transition: all .2s;
}
.pricing-card.popular {
    border-color: var(--green);
    box-shadow: 0 0 0 2px rgba(16,185,129,.15), 0 8px 32px rgba(16,185,129,.12);
}
.pricing-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(0,0,0,.08);
}
.pricing-popular-badge {
    position: absolute; top: 16px; right: -24px;
    background: var(--green); color: #fff;
    font-size: 10px; font-weight: 700; letter-spacing: .5px;
    padding: 4px 32px; transform: rotate(45deg);
    transform-origin: top right;
}
.pricing-name  { font-size: 13px; font-weight: 700; color: var(--green); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
.pricing-price { font-size: 36px; font-weight: 800; color: var(--text); font-family: var(--mono); letter-spacing: -1px; }
.pricing-price span { font-size: 15px; font-weight: 500; color: var(--muted); }
.pricing-desc  { font-size: 13px; color: var(--text-2); margin: 10px 0 20px; }
.pricing-features { list-style: none; padding: 0; margin: 0 0 24px; display: flex; flex-direction: column; gap: 10px; }
.pricing-features li {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: var(--text-2);
}
.pricing-features li::before {
    content: '✓'; color: var(--green);
    font-weight: 700; font-size: 13px; flex-shrink: 0;
}
.pricing-btn {
    display: block; width: 100%; text-align: center;
    padding: 12px; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 700; font-family: var(--font);
    text-decoration: none; transition: all .15s;
}
.pricing-btn-outline {
    border: 1.5px solid var(--border);
    color: var(--text-2); background: transparent;
}
.pricing-btn-outline:hover { border-color: var(--green); color: var(--green); }
.pricing-btn-filled {
    background: var(--green); color: #fff; border: none;
    box-shadow: 0 4px 14px rgba(16,185,129,.3);
}
.pricing-btn-filled:hover { background: var(--green-dk); color: #fff; }

/* ════════════════════════════════════════════════════════════════
   CTA ENTREPRISE DE LIVRAISON
════════════════════════════════════════════════════════════════ */
.company-cta {
    background: linear-gradient(135deg, #0a0a1e 0%, #0f0f3a 60%, #10103a 100%);
    border: 1px solid rgba(99,102,241,.2);
    border-radius: 20px;
    padding: 56px 40px;
    display: flex; align-items: center;
    justify-content: space-between; gap: 32px;
    flex-wrap: wrap;
    position: relative; overflow: hidden;
    margin: 0 24px;
    box-shadow: 0 8px 40px rgba(99,102,241,.15);
}
.company-cta::before {
    content: '';
    position: absolute; right: -80px; top: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(99,102,241,.2) 0%, rgba(139,92,246,.08) 50%, transparent 70%);
}
.company-cta-txt h2 {
    font-family: var(--display);
    font-size: 30px; font-weight: 700; color: #fff;
    letter-spacing: -.5px; margin: 0 0 8px;
}
.company-cta-txt p { font-size: 14.5px; color: rgba(255,255,255,.55); margin: 0; }
.company-cta-actions { display: flex; gap: 12px; flex-shrink: 0; flex-wrap: wrap; }

/* ════════════════════════════════════════════════════════════════
   FAQ
════════════════════════════════════════════════════════════════ */
.faq-section { background: #fff; }
.faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.faq-item {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    padding: 20px 22px;
    cursor: pointer;
    transition: border-color .15s;
}
.faq-item:hover { border-color: var(--green-lt); }
.faq-q {
    font-size: 14px; font-weight: 600; color: var(--text);
    display: flex; justify-content: space-between; align-items: center;
    gap: 12px; user-select: none;
}
.faq-q .arrow {
    font-size: 18px; color: var(--muted);
    transition: transform .2s; flex-shrink: 0;
}
.faq-item.open .faq-q .arrow { transform: rotate(45deg); color: var(--green); }
.faq-a {
    font-size: 13px; color: var(--text-2); line-height: 1.65;
    margin-top: 12px; display: none;
}
.faq-item.open .faq-a { display: block; }

/* ════════════════════════════════════════════════════════════════
   FOOTER
════════════════════════════════════════════════════════════════ */
.site-footer {
    background: var(--dark);
    padding: 48px 40px 28px;
    color: rgba(255,255,255,.45);
}
.footer-inner {
    max-width: 1100px; margin: 0 auto;
    display: flex; justify-content: space-between;
    align-items: flex-start; gap: 32px; flex-wrap: wrap;
    margin-bottom: 32px;
}
.footer-brand { max-width: 260px; }
.footer-logo {
    display: flex; align-items: center; gap: 9px;
    font-size: 16px; font-weight: 700; color: #fff;
    text-decoration: none; margin-bottom: 10px;
}
.footer-logo-ico {
    width: 30px; height: 30px; border-radius: 7px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
}
.footer-desc { font-size: 12.5px; line-height: 1.65; }
.footer-col h4 {
    font-size: 12px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1px; color: rgba(255,255,255,.6);
    margin-bottom: 14px;
}
.footer-col a {
    display: block; font-size: 13px; color: rgba(255,255,255,.4);
    text-decoration: none; margin-bottom: 8px; transition: color .15s;
}
.footer-col a:hover { color: rgba(255,255,255,.8); }
.footer-bottom {
    max-width: 1100px; margin: 0 auto;
    padding-top: 20px; border-top: 1px solid rgba(255,255,255,.06);
    display: flex; align-items: center; justify-content: space-between;
    font-size: 12px; flex-wrap: wrap; gap: 8px;
}

/* Perspective moins agressive pour éviter le flou */
.hero-mockup-outer {
    transform: perspective(2400px) rotateX(2deg);
}
.hero-mockup-outer:hover { transform: perspective(2400px) rotateX(0deg); }

/* ════════════════════════════════════════════════════════════════
   HAMBURGER MOBILE
════════════════════════════════════════════════════════════════ */
.nav-hamburger {
    display: none; flex-direction: column; gap: 5px;
    cursor: pointer; padding: 6px; border: none; background: none;
}
.nav-hamburger span {
    display: block; width: 22px; height: 2px;
    background: rgba(255,255,255,.8); border-radius: 2px;
    transition: transform .25s, opacity .25s;
}
.nav-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.nav-hamburger.open span:nth-child(2) { opacity: 0; }
.nav-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

.nav-mobile-menu {
    display: none; position: fixed;
    top: 64px; left: 0; right: 0; z-index: 99;
    background: rgba(10,10,30,.97);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(99,102,241,.12);
    padding: 16px 20px 24px;
    flex-direction: column; gap: 6px;
}
.nav-mobile-menu.open { display: flex; }
.nav-mobile-link {
    display: block; padding: 11px 14px; border-radius: 10px;
    font-size: 15px; font-weight: 600; color: rgba(255,255,255,.75);
    text-decoration: none; transition: background .15s, color .15s;
}
.nav-mobile-link:hover { background: rgba(255,255,255,.08); color: #fff; }
.nav-mobile-divider { height: 1px; background: rgba(255,255,255,.07); margin: 8px 0; }
.nav-mobile-btn {
    display: block; padding: 13px; border-radius: 10px;
    font-size: 15px; font-weight: 700; text-align: center;
    text-decoration: none; margin-top: 4px;
}
.nav-mobile-btn-outline {
    border: 1.5px solid rgba(255,255,255,.2); color: rgba(255,255,255,.8);
    background: transparent;
}
.nav-mobile-btn-green {
    background: linear-gradient(135deg,#6366f1,#4f46e5); color: #fff;
}

/* ════════════════════════════════════════════════════════════════
   RESPONSIVE — TABLET (≤ 1024px)
════════════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
    .hero-mockup { padding: 0 12px; }
    .pricing-grid { grid-template-columns: repeat(2,1fr); }
    .company-cta  { padding: 44px 32px; }
}

/* ════════════════════════════════════════════════════════════════
   RESPONSIVE — TABLETTE (≤ 900px)
════════════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    /* Navbar */
    .top-nav { padding: 0 20px; }
    .nav-links { display: none; }
    .nav-logo-img { width: 36px; height: 36px; }
    .nav-brand-name { font-size: 16px; }
    .nav-hamburger { display: flex; }

    /* Hero */
    .hero-section { padding: 88px 20px 60px; }
    .hero-stats { gap: 24px; flex-wrap: wrap; }

    /* Mockup */
    .mockup-float { display: none; }
    .hero-mockup  { padding: 0 8px; margin-top: 48px; }
    .hero-mockup::after { display: none; }
    .hero-mockup-outer { transform: none !important; }

    /* Grilles */
    .features-grid { grid-template-columns: repeat(2,1fr); gap: 14px; }
    .steps-grid    { grid-template-columns: repeat(2,1fr); gap: 36px 20px; }
    .steps-grid::before { display: none; }
    .shops-grid    { grid-template-columns: repeat(2,1fr); gap: 16px; }
    .proof-grid    { grid-template-columns: repeat(2,1fr); }
    .pricing-grid  { grid-template-columns: 1fr; max-width: 420px; margin: 0 auto; }
    .faq-grid      { grid-template-columns: 1fr; }

    /* Sections */
    .section { padding: 60px 20px; }
    .section-sub { margin-bottom: 32px; }

    /* CTA entreprise */
    .company-cta {
        margin: 0; flex-direction: column;
        align-items: flex-start; padding: 36px 28px; border-radius: 16px;
    }

    /* Footer */
    .footer-inner { gap: 24px; }
    .site-footer  { padding: 40px 24px 24px; }
}

/* ════════════════════════════════════════════════════════════════
   RESPONSIVE — MOBILE (≤ 640px)
════════════════════════════════════════════════════════════════ */
@media (max-width: 640px) {
    /* Hero */
    .hero-section { padding: 80px 16px 52px; }
    .hero-title   { letter-spacing: -1px; }
    .hero-sub     { font-size: 15px; }
    .hero-cta     { flex-direction: column; align-items: stretch; width: 100%; }
    .cta-btn      { justify-content: center; padding: 14px 20px; }
    .hero-stats   { gap: 16px; }
    .hero-stat-sep { display: none; }
    .hero-stat-val { font-size: 22px; }

    /* Mockup bar */
    .mockup-url   { max-width: 160px; font-size: 9px; }
    .mockup-actions { display: none; }

    /* Grilles */
    .features-grid { grid-template-columns: 1fr; gap: 12px; }
    .steps-grid    { grid-template-columns: 1fr; gap: 28px; }
    .shops-grid    { grid-template-columns: 1fr; gap: 14px; }
    .proof-grid    { grid-template-columns: 1fr; }

    /* Cards */
    .feature-card  { padding: 22px 18px; }
    .shop-img-wrap { height: 160px; }

    /* Sections */
    .section       { padding: 48px 16px; }
    .section-title { letter-spacing: -.5px; margin-bottom: 10px; }

    /* CTA Livraison */
    .company-cta  { margin: 0; padding: 28px 20px; border-radius: 14px; }
    .company-cta-txt h2 { font-size: 22px; }
    .company-cta-actions { width: 100%; flex-direction: column; }
    .company-cta-actions .cta-btn { justify-content: center; }

    /* Pricing */
    .pricing-grid  { max-width: 100%; }

    /* Footer */
    .footer-inner  { flex-direction: column; gap: 28px; }
    .footer-bottom { flex-direction: column; text-align: center; gap: 6px; }
    .site-footer   { padding: 36px 16px 20px; }
}

/* ════════════════════════════════════════════════════════════════
   RESPONSIVE — PETIT MOBILE (≤ 400px)
════════════════════════════════════════════════════════════════ */
@media (max-width: 400px) {
    .hero-section  { padding: 74px 14px 44px; }
    .nav-logo-img  { width: 32px; height: 32px; border-radius: 8px; }
    .nav-brand-name { font-size: 14px; }
    .hero-mockup-bar { height: 32px; }
    .mockup-dot    { width: 8px; height: 8px; }
    .mockup-url    { display: none; }
    .hero-stats    { flex-direction: column; align-items: center; gap: 14px; }
}
</style>
@endpush

@section('content')

{{-- Filtre SVG invisible pour accentuer la netteté de l'image --}}
<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden">
    <defs>
        <filter id="img-sharpen" x="0" y="0" width="100%" height="100%" color-interpolation-filters="sRGB">
            <feConvolveMatrix order="3" preserveAlpha="true"
                kernelMatrix="0 -0.6 0  -0.6 3.4 -0.6  0 -0.6 0"/>
        </filter>
    </defs>
</svg>

{{-- ══════════════════════════════════════════
     NAVBAR CUSTOM
══════════════════════════════════════════ --}}
<nav class="top-nav">
    <a href="{{ url('/') }}" class="nav-brand">
        <img src="/images/shopio3.jpeg" alt="Shopio" class="nav-logo-img">
        <span class="nav-brand-name">{{ config('app.name', 'Shopio') }}</span>
    </a>
    <div class="nav-links">
        <a href="#features"  class="nav-link-item">Fonctionnalités</a>
        <a href="#how"       class="nav-link-item">Comment ça marche</a>
        <a href="#shops"     class="nav-link-item">Boutiques</a>
        <a href="#pricing"   class="nav-link-item">Tarifs</a>
        @guest
        <a href="{{ route('login') }}"    class="cta-btn nav-btn nav-btn-outline" style="padding:8px 16px;font-size:13px">Connexion</a>
        <a href="{{ route('register') }}" class="cta-btn nav-btn" style="padding:8px 16px;font-size:13px">S'inscrire</a>
        @else
        @php
            $role = Auth::user()->role;
            $map  = ['superadmin'=>'admin.dashboard','admin'=>'boutique.dashboard','vendeur'=>'vendeur.dashboard','client'=>'client.dashboard','company'=>'company.dashboard','livreur'=>'livreur.dashboard'];
        @endphp
        @if(isset($map[$role]))
        <a href="{{ route($map[$role]) }}" class="cta-btn nav-btn" style="padding:8px 16px;font-size:13px">Mon dashboard →</a>
        @endif
        @endguest
    </div>

    {{-- Hamburger mobile --}}
    <button class="nav-hamburger" id="navHamburger" aria-label="Menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

{{-- Menu mobile déroulant --}}
<div class="nav-mobile-menu" id="navMobileMenu">
    <a href="#features"  class="nav-mobile-link" onclick="closeMobileMenu()">✨ Fonctionnalités</a>
    <a href="#how"       class="nav-mobile-link" onclick="closeMobileMenu()">🔄 Comment ça marche</a>
    <a href="#shops"     class="nav-mobile-link" onclick="closeMobileMenu()">🏪 Boutiques</a>
    <a href="#pricing"   class="nav-mobile-link" onclick="closeMobileMenu()">💳 Tarifs</a>
    <div class="nav-mobile-divider"></div>
    @guest
    <a href="{{ route('login') }}"    class="nav-mobile-btn nav-mobile-btn-outline">Se connecter</a>
    <a href="{{ route('register') }}" class="nav-mobile-btn nav-mobile-btn-green">🚀 Créer un compte — Gratuit</a>
    @else
    @php
        $role2 = Auth::user()->role;
        $map2  = ['superadmin'=>'admin.dashboard','admin'=>'boutique.dashboard','vendeur'=>'vendeur.dashboard','client'=>'client.dashboard','company'=>'company.dashboard','livreur'=>'livreur.dashboard'];
    @endphp
    @if(isset($map2[$role2]))
    <a href="{{ route($map2[$role2]) }}" class="nav-mobile-btn nav-mobile-btn-green">Mon dashboard →</a>
    @endif
    @endguest
</div>

{{-- ══════════════════════════════════════════
     HERO
══════════════════════════════════════════ --}}
<section class="hero-section">
    <div class="hero-glow"></div>

    {{-- Badge "plateforme active" --}}
    <div class="hero-badge">
        <span class="hero-badge-dot"></span>
        Plateforme de gestion boutique & livraisons
    </div>

    {{-- Titre principal --}}
    <h1 class="hero-title">
        Gérez votre boutique<br>
        <span>comme un pro</span>
    </h1>

    {{-- Sous-titre --}}
    <p class="hero-sub">
        Commandes, livraisons, paiements, clients — tout en un seul dashboard moderne.
        Lancez-vous en moins de 5 minutes.
    </p>

    {{-- CTA buttons --}}
    <div class="hero-cta">
        @guest
        <a href="{{ route('register') }}" class="cta-btn cta-primary">
            🚀 Créer ma boutique — Gratuit
        </a>
        <a href="{{ route('login') }}" class="cta-btn cta-secondary">
            Déjà inscrit ? Connexion →
        </a>
        @else
        @php
            $role = Auth::user()->role;
            $map  = ['superadmin'=>'admin.dashboard','admin'=>'boutique.dashboard','vendeur'=>'vendeur.dashboard','client'=>'client.dashboard','company'=>'company.dashboard','livreur'=>'livreur.dashboard'];
        @endphp
        @if(isset($map[$role]))
        <a href="{{ route($map[$role]) }}" class="cta-btn cta-primary">
            Aller à mon dashboard →
        </a>
        @endif
        @endguest
    </div>

    {{-- Stats sociales --}}
    <div class="hero-stats">
        <div class="hero-stat">
            <span class="hero-stat-val" data-count="{{ $stats['total_shops'] ?? 0 }}">0</span>
            <span class="hero-stat-lbl">Boutiques actives</span>
        </div>
        <div class="hero-stat-sep"></div>
        <div class="hero-stat">
            <span class="hero-stat-val" data-count="{{ $stats['total_orders'] ?? 0 }}">0</span>
            <span class="hero-stat-lbl">Commandes traitées</span>
        </div>
        <div class="hero-stat-sep"></div>
        <div class="hero-stat">
            <span class="hero-stat-val" data-count="{{ $stats['total_clients'] ?? 0 }}">0</span>
            <span class="hero-stat-lbl">Clients satisfaits</span>
        </div>
        <div class="hero-stat-sep"></div>
        <div class="hero-stat">
            <span class="hero-stat-val" data-count="{{ $stats['total_livreurs'] ?? 0 }}">0</span>
            <span class="hero-stat-lbl">Livreurs disponibles</span>
        </div>
    </div>

    {{-- Mockup dashboard --}}
    <div class="hero-mockup">

        {{-- Cartes flottantes --}}
        <div class="mockup-float f1">
            <div class="mf-ico">💰</div>
            <div class="mf-label">CA aujourd'hui</div>
            <div class="mf-val">2 840 000 <span>↑ +18%</span></div>
            <div class="mf-bar"><div class="mf-bar-fill" style="width:72%"></div></div>
        </div>
        <div class="mockup-float f2">
            <div class="mf-ico">📦</div>
            <div class="mf-label">Commandes en attente</div>
            <div class="mf-val">12 <span>en cours</span></div>
            <div class="mf-bar"><div class="mf-bar-fill" style="width:55%"></div></div>
        </div>
        <div class="mockup-float f3">
            <div class="mf-ico">🛵</div>
            <div class="mf-label">Livreurs disponibles</div>
            <div class="mf-val">5 <span>actifs</span></div>
            <div class="mf-bar"><div class="mf-bar-fill" style="width:83%"></div></div>
        </div>

        <div class="hero-mockup-outer">
            {{-- Badge LIVE --}}
            <div class="mockup-live">
                <span class="mockup-live-dot"></span>
                LIVE PREVIEW
            </div>

            <div class="hero-mockup-inner">
                {{-- Barre navigateur réaliste --}}
                <div class="hero-mockup-bar">
                    <div class="mockup-dots">
                        <span class="mockup-dot" style="background:#ff5f57"></span>
                        <span class="mockup-dot" style="background:#febc2e"></span>
                        <span class="mockup-dot" style="background:#28c840"></span>
                    </div>
                    <div class="mockup-url">
                        <span class="mockup-url-lock" style="color:#a5b4fc">🔒</span>
                        shopio.app/boutique/dashboard
                    </div>
                    <div class="mockup-actions">
                        <div class="mockup-action-btn">←</div>
                        <div class="mockup-action-btn">→</div>
                        <div class="mockup-action-btn">↻</div>
                    </div>
                </div>

                @if(file_exists(public_path('images/dashboard2.png')))
                    <img src="{{ asset('images/dashboard1.png') }}" alt="Dashboard Shopio preview" loading="lazy">
                @else
                    <div class="hero-mockup-placeholder">
                        <span class="ico">📊</span>
                        <p>Aperçu du dashboard — ajoute dashboard2.png dans public/images/</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════
     FONCTIONNALITÉS
══════════════════════════════════════════ --}}
<section class="section features-section" id="features">
    <div class="section-inner">
        <div class="section-badge">Fonctionnalités</div>
        <h2 class="section-title">Tout ce dont votre boutique<br><span>a besoin</span></h2>
        <p class="section-sub">Une suite complète d'outils pour gérer, livrer et analyser votre activité au quotidien.</p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-ico">📦</div>
                <h3 class="feature-title">Gestion des commandes</h3>
                <p class="feature-desc">Recevez, confirmez et suivez chaque commande en temps réel. Assignez un livreur en un clic.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico">🚴</div>
                <h3 class="feature-title">Livraison GPS</h3>
                <p class="feature-desc">Suivez vos livreurs en direct sur la carte. Le client est notifié à chaque étape de sa commande.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico">📊</div>
                <h3 class="feature-title">Dashboard analytics</h3>
                <p class="feature-desc">CA, panier moyen, taux de livraison, top clients — tout en un coup d'œil avec des graphiques clairs.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico">👥</div>
                <h3 class="feature-title">Gestion d'équipe</h3>
                <p class="feature-desc">Ajoutez  vos livreurs. Chaque rôle a son propre espace de travail dédié.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico">💳</div>
                <h3 class="feature-title">Paiements & commissions</h3>
                <p class="feature-desc">Suivez vos revenus, gérez les commissions livreurs et exportez vos rapports en Excel ou PDF.</p>
            </div>
            <div class="feature-card">
                <div class="feature-ico">🏢</div>
                <h3 class="feature-title">Entreprises partenaires</h3>
                <p class="feature-desc">Pas de livreurs ? Contactez directement une entreprise de livraison partenaire via le chat intégré.</p>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════
     COMMENT CA MARCHE
══════════════════════════════════════════ --}}
<section class="section how-section" id="how">
    <div class="section-inner">
        <div class="section-badge">Simple & rapide</div>
        <h2 class="section-title">Lancez-vous en <span>4 étapes</span></h2>
        <p class="section-sub">De l'inscription à votre première vente livrée, le processus est simple et guidé.</p>

        <div class="steps-grid">
            <div class="step-item">
                <div class="step-num">1</div>
                <h4 class="step-title">Créez votre compte</h4>
                <p class="step-desc">Inscrivez-vous en moins de 2 minutes. Choisissez votre rôle : boutique, livreur ou client.</p>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <h4 class="step-title">Configurez votre boutique</h4>
                <p class="step-desc">Ajoutez vos produits, définissez vos prix et personnalisez votre espace en quelques clics.</p>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <h4 class="step-title">Recevez des commandes</h4>
                <p class="step-desc">Les clients commandent directement sur votre boutique. Vous êtes notifié en temps réel.</p>
            </div>
            <div class="step-item">
                <div class="step-num">4</div>
                <h4 class="step-title">Livrez & encaissez</h4>
                <p class="step-desc">Assignez un livreur, suivez la livraison GPS et recevez votre paiement automatiquement.</p>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════
     BOUTIQUES EN LIGNE
══════════════════════════════════════════ --}}
@if(isset($shops) && $shops->count() > 0)
<section class="section shops-section" id="shops">
    <div class="section-inner">
        <div class="section-badge">Marketplace</div>
        <h2 class="section-title">Boutiques <span>disponibles</span></h2>
        <p class="section-sub">Découvrez les boutiques déjà présentes sur la plateforme et commandez dès maintenant.</p>

        <div class="shops-grid">
            @foreach($shops->take(3) as $shop)
            <a href="{{ route('public.shops.products', $shop) }}" class="shop-card">
                <div class="shop-img-wrap">
                    @if(!empty($shop->image))
                        <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}" loading="lazy">
                    @else
                        <span class="shop-img-placeholder">🛍️</span>
                    @endif
                    <span class="shop-badge">✓ Actif</span>
                </div>
                <div class="shop-info">
                    <div class="shop-name">{{ $shop->name }}</div>
                    <div class="shop-meta">
                        {{ $shop->type ?? 'Boutique' }} &nbsp;·&nbsp;
                        📦 {{ $shop->products_count ?? 0 }} produit{{ ($shop->products_count ?? 0) > 1 ? 's' : '' }}
                        @if($shop->address) &nbsp;·&nbsp; 📍 {{ Str::limit($shop->address, 18) }} @endif
                    </div>
                    <span class="shop-cta">Visiter la boutique →</span>
                </div>
            </a>
            @endforeach
        </div>

        <div style="text-align:center">
            <a href="{{ route('shops.index') }}" class="cta-btn cta-primary" style="display:inline-flex;align-items:center;gap:8px">
                🏪 Voir toutes les boutiques
                <span style="background:rgba(255,255,255,.2);padding:2px 9px;border-radius:20px;font-size:12px">{{ $shops->count() }}+</span>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ══════════════════════════════════════════
     TÉMOIGNAGES
══════════════════════════════════════════ --}}
<section style="background:var(--dark);padding:80px 24px">
    <div style="max-width:1100px;margin:0 auto">
        <div style="text-align:center;margin-bottom:48px">
            <div class="section-badge" style="background:rgba(99,102,241,.14);border-color:rgba(99,102,241,.3);color:#a5b4fc">Ils nous font confiance</div>
            <h2 class="section-title" style="color:#fff">Ce que disent<br><span>nos utilisateurs</span></h2>
        </div>
        <div class="proof-grid">
            <div class="proof-card">
                <div class="proof-stars">★★★★★</div>
                <p class="proof-text">"Depuis que j'utilise cette plateforme, mes ventes ont augmenté de 40%. Le suivi des livraisons en temps réel a vraiment changé la donne avec mes clients."</p>
                <div class="proof-author">
                    <div class="proof-av" style="background:linear-gradient(135deg,#6366f1,#4338ca)">AM</div>
                    <div>
                        <div class="proof-name">Aminata Camara</div>
                        <div class="proof-role">Propriétaire boutique · Conakry</div>
                    </div>
                </div>
            </div>
            <div class="proof-card">
                <div class="proof-stars">★★★★★</div>
                <p class="proof-text">"Le dashboard est incroyablement clair. En quelques secondes je vois mon CA du jour, mes commandes en attente et mes livreurs disponibles."</p>
                <div class="proof-author">
                    <div class="proof-av" style="background:#2563eb">MB</div>
                    <div>
                        <div class="proof-name">Mamadou Barry</div>
                        <div class="proof-role">Gérant · Boutique électronique</div>
                    </div>
                </div>
            </div>
            <div class="proof-card">
                <div class="proof-stars">★★★★★</div>
                <p class="proof-text">"Notre entreprise de livraison a rejoint la plateforme il y a 3 mois. Nous avons maintenant des contrats réguliers avec 12 boutiques différentes."</p>
                <div class="proof-author">
                    <div class="proof-av" style="background:#7c3aed">FD</div>
                    <div>
                        <div class="proof-name">Fatoumata Diallo</div>
                        <div class="proof-role">Directrice · Rapide Livraison</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

 {{-- ══════════════════════════════════════════
     TARIFS
══════════════════════════════════════════ --}}
 <!-- <section class="section pricing-section" id="pricing">
    <div class="section-inner">
        <div style="text-align:center;margin-bottom:48px">
            <div class="section-badge">Tarification</div>
            <h2 class="section-title">Des tarifs <span>transparents</span></h2>
            <p class="section-sub" style="margin:0 auto">Commencez gratuitement, évoluez selon vos besoins.</p>
        </div>
        <div class="pricing-grid">

            {{-- Gratuit --}}
            <div class="pricing-card">
                <div class="pricing-name">Gratuit</div>
                <div class="pricing-price">0 <span>GNF/mois</span></div>
                <div class="pricing-desc">Pour démarrer et tester la plateforme.</div>
                <ul class="pricing-features">
                    <li>1 boutique</li>
                    <li>Jusqu'à 20 produits</li>
                    <li>50 commandes/mois</li>
                    <li>1 livreur</li>
                    <li>Support communauté</li>
                </ul>
                <a href="{{ route('register') }}" class="pricing-btn pricing-btn-outline">Commencer gratuitement</a>
            </div>

            {{-- Pro --}}
            <div class="pricing-card popular">
                <div class="pricing-popular-badge">POPULAIRE</div>
                <div class="pricing-name">Pro</div>
                <div class="pricing-price">50k <span>GNF/mois</span></div>
                <div class="pricing-desc">Pour les boutiques en pleine croissance.</div>
                <ul class="pricing-features">
                    <li>1 boutique</li>
                    <li>Produits illimités</li>
                    <li>Commandes illimitées</li>
                    <li>5 livreurs</li>
                    <li>Analytics avancés</li>
                    <li>Export Excel & PDF</li>
                    <li>Support prioritaire</li>
                </ul>
                <a href="{{ route('register') }}" class="pricing-btn pricing-btn-filled">Démarrer Pro</a>
            </div>

            {{-- Entreprise --}}
            <div class="pricing-card">
                <div class="pricing-name">Entreprise</div>
                <div class="pricing-price">Sur <span>devis</span></div>
                <div class="pricing-desc">Pour les grandes structures multi-boutiques.</div>
                <ul class="pricing-features">
                    <li>Boutiques illimitées</li>
                    <li>Équipe illimitée</li>
                    <li>API dédiée</li>
                    <li>Livreurs illimités</li>
                    <li>Tableau de bord multi-boutiques</li>
                    <li>Gestionnaire de compte dédié</li>
                </ul>
                <a href="{{ route('support.index') }}" class="pricing-btn pricing-btn-outline">Nous contacter</a>
            </div>

        </div>
    </div>
</section>
!-->

{{-- ══════════════════════════════════════════
     CTA ENTREPRISE LIVRAISON
══════════════════════════════════════════ --}}
<div style="padding:0 0 80px">
    <div class="company-cta">
        <div class="company-cta-txt">
            <h2>Vous avez une entreprise de livraison ?</h2>
            <p>Rejoignez notre réseau de partenaires. Accédez aux boutiques qui ont besoin de livreurs et développez votre activité.</p>
        </div>
        <div class="company-cta-actions">
            <a href="{{ route('register', ['role'=>'company']) }}" class="cta-btn cta-primary">
                🚚 Rejoindre comme partenaire
            </a>
            <a href="{{ route('delivery.companies.index') }}" class="cta-btn cta-secondary">
                Voir les partenaires →
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     FAQ
══════════════════════════════════════════ --}}
<section class="section faq-section" id="faq">
    <div class="section-inner">
        <div style="text-align:center;margin-bottom:40px">
            <div class="section-badge">FAQ</div>
            <h2 class="section-title">Questions <span>fréquentes</span></h2>
        </div>
        <div class="faq-grid">
            <div class="faq-item">
                <div class="faq-q">Comment créer ma boutique ? <span class="arrow">+</span></div>
                <div class="faq-a">Inscrivez-vous, choisissez le rôle "Admin Boutique", puis cliquez sur "Créer une boutique". Remplissez les informations de base et vous êtes prêt en moins de 5 minutes.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Comment fonctionne la livraison ? <span class="arrow">+</span></div>
                <div class="faq-a">Vous pouvez créer vos propres livreurs ou contacter une entreprise partenaire via le chat intégré. Le suivi GPS est automatique dès que le livreur démarre sa course.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Puis-je avoir plusieurs boutiques ? <span class="arrow">+</span></div>
                <div class="faq-a">Oui, vous pouvez creé plusieurs boutiques.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Comment les paiements sont-ils gérés ? <span class="arrow">+</span></div>
                <div class="faq-a">Le système de paiement est cash à la livraison. Chaque commande livrée génère un enregistrement de paiement automatique avec calcul des commissions livreurs.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Est-ce que je peux exporter mes données ? <span class="arrow">+</span></div>
                <div class="faq-a">Oui, vous pouvez exporter vos commandes, paiements et statistiques en Excel ou PDF directement depuis votre dashboard,.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Comment contacter le support ? <span class="arrow">+</span></div>
                <div class="faq-a">Un système de tickets est intégré directement dans la plateforme. Créez un ticket et notre équipe vous répond dans les 24h ouvrées.</div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════
     FOOTER
══════════════════════════════════════════ --}}
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="{{ url('/') }}" class="footer-logo">
                <img src="/images/shopio3.jpeg" alt="Shopio" style="width:40px;height:40px;object-fit:cover;border-radius:10px;border:2px solid rgba(170,40,217,.4);box-shadow:0 0 0 3px rgba(41,29,149,.25)">
                <span style="background:linear-gradient(90deg,#c4b5fd,#e879f9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;font-weight:800">{{ config('app.name', 'Shopio') }}</span>
            </a>
            <p class="footer-desc">La plateforme tout-en-un pour gérer votre boutique, vos livraisons et vos clients en Guinée.</p>
        </div>
        <div class="footer-col">
            <h4>Plateforme</h4>
            <a href="#features">Fonctionnalités</a>
            <a href="#pricing">Tarifs</a>
            <a href="#how">Comment ça marche</a>
            <a href="{{ route('delivery.companies.index') }}">Entreprises livraison</a>
        </div>
        <div class="footer-col">
            <h4>Compte</h4>
            <a href="{{ route('login') }}">Connexion</a>
            <a href="{{ route('register') }}">Inscription</a>
            @auth
            <a href="{{ route('profile.edit') }}">Mon profil</a>
            @endauth
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <a href="{{ route('support.index') }}">Centre d'aide</a>
            <a href="{{ route('support.create') }}">Ouvrir un ticket</a>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; {{ date('Y') }} {{ config('app.name', 'ShopManager') }} — Tous droits réservés</span>
        <span>Fait avec ❤️ en Guinée 🇬🇳</span>
    </div>
</footer>

@endsection

@push('scripts')
<script>
function closeMobileMenu() {
    const h = document.getElementById('navHamburger');
    const m = document.getElementById('navMobileMenu');
    if (h && m) {
        m.classList.remove('open');
        h.classList.remove('open');
        h.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {

    /* ── Animation compteurs hero ──────────────────────────────────
     * Les chiffres des stats s'animent de 0 vers leur valeur réelle
     * au chargement de la page.
     * ──────────────────────────────────────────────────────────── */
    document.querySelectorAll('.hero-stat-val[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count) || 0;
        if (target === 0) { el.textContent = '0'; return; }
        const duration = 1600;
        const step     = 16;
        const increment = target / (duration / step);
        let current = 0;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                el.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                el.textContent = Math.floor(current).toLocaleString();
            }
        }, step);
    });

    /* ── FAQ accordion ────────────────────────────────────────────*/
    document.querySelectorAll('.faq-item').forEach(item => {
        item.querySelector('.faq-q').addEventListener('click', () => {
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            if (!isOpen) item.classList.add('open');
        });
    });

    /* ── Navbar scroll effect ─────────────────────────────────────*/
    const nav = document.querySelector('.top-nav');
    window.addEventListener('scroll', () => {
        nav.style.background = window.scrollY > 60
            ? 'rgba(10,10,30,.98)'
            : 'rgba(10,10,30,.92)';
    });

    /* ── Hamburger menu mobile ───────────────────────────────────*/
    const hamburger   = document.getElementById('navHamburger');
    const mobileMenu  = document.getElementById('navMobileMenu');
    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', () => {
            const open = mobileMenu.classList.toggle('open');
            hamburger.classList.toggle('open', open);
            hamburger.setAttribute('aria-expanded', open);
            document.body.style.overflow = open ? 'hidden' : '';
        });
        // Fermer en cliquant hors du menu
        document.addEventListener('click', e => {
            if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
                closeMobileMenu();
            }
        });
    }

    /* ── Reveal au scroll ─────────────────────────────────────────
     * Les cartes de features et étapes apparaissent en fondu
     * au fur et à mesure du scroll.
     * ──────────────────────────────────────────────────────────── */
    const observer = new IntersectionObserver(entries => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity  = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, i * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.feature-card, .step-item, .shop-card, .proof-card, .pricing-card, .faq-item').forEach(el => {
        el.style.opacity   = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity .5s ease, transform .5s ease';
        observer.observe(el);
    });

});
</script>
@endpush