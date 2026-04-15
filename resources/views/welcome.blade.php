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
    --green:     #10b981;
    --green-dk:  #059669;
    --green-lt:  #d1fae5;
    --green-mlt: #ecfdf5;
    --dark:      #0a1628;
    --dark-2:    #111f3a;
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
    background: rgba(10,22,40,.92);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255,255,255,.06);
    transition: background .3s;
}
.nav-brand {
    display: flex; align-items: center; gap: 10px;
    text-decoration: none; color: #fff;
    font-size: 18px; font-weight: 700; letter-spacing: -.3px;
}
.nav-brand-icon {
    width: 34px; height: 34px;
    background: linear-gradient(135deg, var(--green), #34d399);
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    box-shadow: 0 2px 10px rgba(16,185,129,.4);
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
    background: var(--dark);
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

/* Lumière verte derrière le titre */
.hero-glow {
    position: absolute;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(16,185,129,.18) 0%, transparent 70%);
    top: 50%; left: 50%; transform: translate(-50%, -60%);
    pointer-events: none;
}

.hero-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(16,185,129,.12);
    border: 1px solid rgba(16,185,129,.25);
    color: #34d399;
    font-size: 12px; font-weight: 700;
    padding: 6px 14px; border-radius: 20px;
    margin-bottom: 24px;
    animation: fadeDown .6s ease both;
}
.hero-badge-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #34d399;
    box-shadow: 0 0 6px #34d399;
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
    background: linear-gradient(135deg, #34d399, #10b981, #06b6d4);
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
    background: var(--green); color: #fff;
    box-shadow: 0 4px 20px rgba(16,185,129,.4);
}
.cta-primary:hover {
    background: var(--green-dk); color: #fff;
    box-shadow: 0 6px 28px rgba(16,185,129,.5);
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
    margin-top: 60px; width: 100%; max-width: 960px;
    animation: fadeUp .9s .5s ease both;
}
.hero-mockup-inner {
    position: relative;
    border-radius: 16px; overflow: hidden;
    box-shadow:
        0 0 0 1px rgba(255,255,255,.08),
        0 40px 80px rgba(0,0,0,.6),
        0 0 60px rgba(16,185,129,.08);
}
.hero-mockup-bar {
    background: #1e293b;
    height: 36px; display: flex; align-items: center; gap: 6px; padding: 0 14px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.mockup-dot { width: 10px; height: 10px; border-radius: 50%; }
.hero-mockup img {
    width: 100%; display: block;
}
.hero-mockup-placeholder {
    background: linear-gradient(180deg, #0f1f18 0%, #1a2e20 100%);
    height: 420px; display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: 12px;
}
.hero-mockup-placeholder .ico { font-size: 48px; opacity: .4; }
.hero-mockup-placeholder p { font-size: 13px; color: rgba(255,255,255,.3); font-weight: 500; }

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
    box-shadow: 0 8px 32px rgba(0,0,0,.07);
    border-color: var(--green-lt);
    transform: translateY(-3px);
}
.feature-card::after {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--green), #34d399);
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
    background: linear-gradient(90deg, var(--green-lt), var(--green), var(--green-lt));
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
    background: var(--green); color: #fff;
    box-shadow: 0 4px 16px rgba(16,185,129,.35);
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
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 32px;
}
.shop-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    transition: all .2s;
    text-decoration: none;
    display: block;
}
.shop-card:hover {
    box-shadow: 0 8px 28px rgba(0,0,0,.08);
    border-color: var(--green-lt);
    transform: translateY(-4px);
}
.shop-img-wrap {
    height: 120px; overflow: hidden;
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    display: flex; align-items: center; justify-content: center;
}
.shop-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s;
}
.shop-card:hover .shop-img-wrap img { transform: scale(1.05); }
.shop-img-placeholder { font-size: 40px; }
.shop-info { padding: 14px 16px; }
.shop-name { font-size: 13.5px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.shop-meta { font-size: 11px; color: var(--muted); }
.shop-badge {
    display: inline-block;
    background: var(--green-mlt); color: var(--green-dk);
    font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 20px;
    margin-top: 6px;
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
    background: linear-gradient(135deg, #0a1628 0%, #1a2e20 100%);
    border-radius: 20px;
    padding: 56px 40px;
    display: flex; align-items: center;
    justify-content: space-between; gap: 32px;
    flex-wrap: wrap;
    position: relative; overflow: hidden;
    margin: 0 24px;
}
.company-cta::before {
    content: '';
    position: absolute; right: -80px; top: -80px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(16,185,129,.15) 0%, transparent 70%);
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
    background: linear-gradient(135deg, var(--green), #34d399);
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

/* ════════════════════════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .features-grid  { grid-template-columns: repeat(2,1fr); }
    .steps-grid     { grid-template-columns: repeat(2,1fr); gap: 32px; }
    .steps-grid::before { display: none; }
    .shops-grid     { grid-template-columns: repeat(2,1fr); }
    .proof-grid     { grid-template-columns: 1fr; }
    .pricing-grid   { grid-template-columns: 1fr; }
    .faq-grid       { grid-template-columns: 1fr; }
    .top-nav        { padding: 0 20px; }
    .nav-links .nav-link-item { display: none; }
}
@media (max-width: 560px) {
    .features-grid  { grid-template-columns: 1fr; }
    .shops-grid     { grid-template-columns: repeat(2,1fr); }
    .hero-stats     { gap: 20px; }
    .hero-stat-sep  { display: none; }
    .company-cta    { margin: 0 16px; padding: 32px 22px; }
    .site-footer    { padding: 40px 20px 20px; }
}
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════
     NAVBAR CUSTOM
══════════════════════════════════════════ --}}
<nav class="top-nav">
    <a href="{{ url('/') }}" class="nav-brand">
        <div class="nav-brand-icon">🛍️</div>
        {{ config('app.name', 'ShopManager') }}
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
</nav>

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
    <div class="hero-mockup" style="max-width:960px;width:100%">
        <div class="hero-mockup-inner">
            <div class="hero-mockup-bar">
                <span class="mockup-dot" style="background:#ff5f57"></span>
                <span class="mockup-dot" style="background:#febc2e"></span>
                <span class="mockup-dot" style="background:#28c840"></span>
            </div>
            @if(file_exists(public_path('images/dashboard.png')))
                <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard preview">
            @else
                <div class="hero-mockup-placeholder">
                    <span class="ico">📊</span>
                    <p>Aperçu du dashboard — ajoute dashboard.png dans public/images/</p>
                </div>
            @endif
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
            @foreach($shops->take(8) as $shop)
            <a href="{{ route('public.shops.products', $shop) }}" class="shop-card">
                <div class="shop-img-wrap">
                    @if(!empty($shop->image))
                        <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}">
                    @else
                        <span class="shop-img-placeholder">🛍️</span>
                    @endif
                </div>
                <div class="shop-info">
                    <div class="shop-name">{{ $shop->name }}</div>
                    <div class="shop-meta">{{ $shop->type ?? 'Boutique' }} · {{ $shop->products_count ?? 0 }} produit(s)</div>
                    <div class="shop-badge">✓ Actif</div>
                </div>
            </a>
            @endforeach
        </div>

        <div style="text-align:center">
            <a href="{{ route('welcome') }}" class="cta-btn cta-primary" style="display:inline-flex">
                Voir toutes les boutiques →
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
            <div class="section-badge" style="background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.25);color:#34d399">Ils nous font confiance</div>
            <h2 class="section-title" style="color:#fff">Ce que disent<br><span>nos utilisateurs</span></h2>
        </div>
        <div class="proof-grid">
            <div class="proof-card">
                <div class="proof-stars">★★★★★</div>
                <p class="proof-text">"Depuis que j'utilise cette plateforme, mes ventes ont augmenté de 40%. Le suivi des livraisons en temps réel a vraiment changé la donne avec mes clients."</p>
                <div class="proof-author">
                    <div class="proof-av" style="background:#059669">AM</div>
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
                <div class="faq-a">Inscrivez-vous, choisissez le rôle "Vendeur", puis cliquez sur "Créer une boutique". Remplissez les informations de base et vous êtes prêt en moins de 5 minutes.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Comment fonctionne la livraison ? <span class="arrow">+</span></div>
                <div class="faq-a">Vous pouvez créer vos propres livreurs ou contacter une entreprise partenaire via le chat intégré. Le suivi GPS est automatique dès que le livreur démarre sa course.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Puis-je avoir plusieurs boutiques ? <span class="arrow">+</span></div>
                <div class="faq-a">Oui, avec le plan Entreprise vous pouvez gérer plusieurs boutiques depuis un seul compte administrateur avec un tableau de bord centralisé.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Comment les paiements sont-ils gérés ? <span class="arrow">+</span></div>
                <div class="faq-a">Le système de paiement est cash à la livraison. Chaque commande livrée génère un enregistrement de paiement automatique avec calcul des commissions livreurs.</div>
            </div>
            <div class="faq-item">
                <div class="faq-q">Est-ce que je peux exporter mes données ? <span class="arrow">+</span></div>
                <div class="faq-a">Oui, vous pouvez exporter vos commandes, paiements et statistiques en Excel ou PDF directement depuis votre dashboard, disponible dès le plan Pro.</div>
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
                <div class="footer-logo-ico">🛍️</div>
                {{ config('app.name', 'ShopManager') }}
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
            ? 'rgba(10,22,40,.98)'
            : 'rgba(10,22,40,.92)';
    });

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