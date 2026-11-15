{{--
    Styles communs du modèle « Express » (accueil, catalogue, page produit). Blanc, boutons noirs, couleur
    de la boutique (accent_color, vert par défaut) pour les prix et la commande, sections sauge et menthe.
    Titres Poppins, texte Inter, touches en Instrument Serif italique. Variables --m-* : parcours d'achat (_head).
    Variables : $shop
--}}
@php
    $exAcc = \App\Support\Storefront::isHex($shop->accent_color) ? strtoupper($shop->accent_color) : '#2F5D1E';
    [$exR, $exG, $exB] = sscanf($exAcc, '#%02x%02x%02x');
    $exLum = fn ($c) => ($c /= 255) <= .03928 ? $c / 12.92 : (($c + .055) / 1.055) ** 2.4;
    // Texte des boutons : noir si la couleur de la boutique est claire, blanc sinon (lisibilité)
    $exAccInk = (.2126 * $exLum($exR) + .7152 * $exLum($exG) + .0722 * $exLum($exB)) > .4 ? '#111111' : '#FFFFFF';
@endphp
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=Instrument+Serif:ital@1&display=swap" rel="stylesheet">
<style>
:root {
    --acc: {{ $exAcc }}; --acc-ink: {{ $exAccInk }}; --acc-soft: color-mix(in srgb, var(--acc) 12%, #fff); --acc-dark: color-mix(in srgb, var(--acc) 82%, #000);
    --ink: #111111; --ink-2: #4B4B4B; --ink-3: #8A8A8A; --line: #E7E7E4; --soft: #F5F5F3; --sage: #E9EEE6; --mint: #E8FAF0; --foot: #6F6B66;
    --m-bg: #FFFFFF; --m-surface: #FFFFFF; --m-ink: var(--ink); --m-ink2: var(--ink-2); --m-line: var(--line); --m-soft: var(--soft);
    --m-accent: var(--acc); --m-accent-ink: var(--acc-ink); --m-accent-text: var(--acc-dark); --m-radius: 16px; --m-btn-radius: 10px;
    --m-title: 'Poppins', system-ui, sans-serif; --m-body: 'Inter', system-ui, sans-serif;
    --serif: 'Instrument Serif', Georgia, serif; --out: cubic-bezier(.22,1,.36,1);
}
body { background: #fff; font-size: 15px; }
a { color: inherit; }
.ex-wrap { max-width: 1180px; margin: 0 auto; padding: 0 max(20px, env(safe-area-inset-left, 0px)); }
.ex-narrow { max-width: 920px; }
.ex-acc { color: var(--acc); }
.ex-serif { font-family: var(--serif); font-style: italic; font-weight: 400; }
.ex-h2 { margin: 0; font: 700 clamp(26px, 3.4vw, 38px)/1.15 var(--m-title); letter-spacing: -.02em; text-wrap: balance; }
.ex-sub { margin: 8px 0 0; color: var(--ink-2); line-height: 1.6; }
.ex-caps { margin: 10px 0 0; color: var(--ink-3); font-size: 12px; font-weight: 600; letter-spacing: .22em; text-transform: uppercase; line-height: 1.7; }
.ex-sec { padding: clamp(48px, 7vw, 84px) 0; }
/* Boutons */
.ex-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 50px; padding: 0 26px; border: 0; border-radius: 10px; background: #111; color: #fff; font: 700 14px var(--m-title); letter-spacing: .04em; text-transform: uppercase; text-decoration: none; cursor: pointer; transition: transform 200ms var(--out), background-color 200ms, box-shadow 200ms; box-shadow: 0 8px 20px -10px rgba(0,0,0,.6); }
.ex-btn:hover { background: #000; transform: translateY(-2px); }
.ex-btn:active { transform: scale(.98); }
.ex-btn.is-acc { background: var(--acc); color: var(--acc-ink); }
.ex-btn.is-acc:hover { background: var(--acc-dark); }
.ex-btn.is-line { background: #fff; color: var(--ink); box-shadow: inset 0 0 0 1.5px var(--ink); text-transform: none; letter-spacing: 0; font-family: var(--m-body); }
.ex-btn.is-line:hover { background: var(--soft); }
.ex-btn.is-pill { border-radius: 999px; }
.ex-btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }
.ex-btn svg { transition: transform 200ms var(--out); } .ex-btn:hover svg { transform: translateX(3px); }
/* Bandeau défilant */
.ex-marquee { overflow: hidden; background: var(--acc); color: var(--acc-ink); font-size: 13px; font-weight: 600; }
.ex-track { display: flex; width: max-content; animation: exMarq 40s linear infinite; }
.ex-track span { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; white-space: nowrap; }
.ex-track span::after { content: ''; width: 4px; height: 4px; margin-left: 22px; border-radius: 50%; background: currentColor; opacity: .5; }
.ex-marquee:hover .ex-track { animation-play-state: paused; }
@keyframes exMarq { to { transform: translateX(-50%); } }
.ex-band { overflow: hidden; background: var(--acc); color: var(--acc-ink); }
.ex-band .ex-track { animation-duration: 26s; }
.ex-band .ex-track span { padding: 14px 30px; font: 800 14px var(--m-title); letter-spacing: .08em; text-transform: uppercase; }
.ex-band .ex-track span::after { display: none; }
/* En-tête : menu à gauche, nom au centre, recherche et panier à droite */
.ex-head { position: sticky; top: 0; z-index: 60; background: rgba(255,255,255,.94); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid transparent; transition: border-color 250ms, box-shadow 250ms; }
.ex-head.is-stuck { border-color: var(--line); box-shadow: 0 8px 24px -18px rgba(0,0,0,.35); }
.ex-head .ex-wrap { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 12px; min-height: 68px; }
.ex-nav { display: flex; gap: 4px; }
.ex-nav a { padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; color: var(--ink-2); }
.ex-nav a:hover, .ex-nav a[aria-current="page"] { color: var(--ink); background: var(--soft); }
.ex-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; min-width: 0; }
.ex-brand img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
.ex-brand b { font: 700 21px var(--m-title); letter-spacing: -.02em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ex-tools { display: flex; justify-content: flex-end; gap: 6px; }
.ex-icon { position: relative; width: 44px; height: 44px; border: 0; border-radius: 10px; background: transparent; color: var(--ink); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
.ex-icon:hover { background: var(--soft); }
.ex-icon [data-sm-count] { position: absolute; top: 3px; right: 2px; min-width: 19px; height: 19px; padding: 0 5px; border-radius: 999px; background: var(--acc); color: var(--acc-ink); font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
.ex-icon [data-sm-count]:empty { display: none; }
.ex-menu { display: none; }
.ex-search { border-top: 1px solid var(--line); background: #fff; }
.ex-search form { display: flex; gap: 8px; padding: 12px 0; }
.ex-search input { flex: 1; min-height: 46px; padding: 0 16px; border: 0; border-radius: 10px; background: var(--soft); font: 500 15px var(--m-body); box-shadow: inset 0 0 0 1px var(--line); outline: 0; }
.ex-search input:focus { box-shadow: inset 0 0 0 2px var(--acc); }
/* Cartes produit */
.ex-card { display: flex; flex-direction: column; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 0 0 1px var(--line), 0 10px 26px -20px rgba(0,0,0,.4); transition: transform 300ms var(--out), box-shadow 300ms var(--out); }
.ex-card:hover { transform: translateY(-4px); box-shadow: 0 0 0 1px var(--line), 0 22px 40px -24px rgba(0,0,0,.45); }
.ex-card-img { position: relative; display: block; aspect-ratio: 1; background: var(--soft); overflow: hidden; }
.ex-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 700ms var(--out); }
.ex-card:hover .ex-card-img img { transform: scale(1.05); }
.ex-card-img .ex-noimg { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 14px; text-align: center; font: 700 16px var(--m-title); color: var(--ink-3); }
.ex-tag { position: absolute; left: 10px; top: 10px; padding: 4px 9px; border-radius: 6px; background: #111; color: #fff; font-size: 11.5px; font-weight: 800; letter-spacing: .03em; }
.ex-tag.is-sale { background: #D92D20; }
.ex-card-body { display: flex; flex-direction: column; gap: 4px; padding: 12px 14px 14px; flex: 1; }
.ex-card-body h3 { margin: 0; font: 600 14.5px/1.35 var(--m-body); }
.ex-card-body h3 a { text-decoration: none; }
.ex-price { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; font: 800 16px var(--m-body); font-variant-numeric: tabular-nums; }
.ex-price s { color: var(--ink-3); font-size: 13px; font-weight: 500; }
.ex-card .ex-btn { margin-top: auto; min-height: 42px; padding: 0 14px; font-size: 13px; text-transform: none; letter-spacing: 0; box-shadow: none; }
.ex-card-body .ex-price { margin-bottom: 10px; }
.ex-stars { color: #F5A623; letter-spacing: 1px; }
/* Questions fréquentes */
.ex-faq { display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.4fr); gap: clamp(26px, 5vw, 70px); align-items: start; }
.ex-faq h2 { font-size: clamp(26px, 3vw, 34px); }
.ex-faq-who { display: flex; align-items: center; margin-top: 20px; }
.ex-faq-who i { width: 38px; height: 38px; margin-left: -8px; border-radius: 50%; border: 2px solid #fff; background: var(--acc); color: var(--acc-ink); display: flex; align-items: center; justify-content: center; font: 700 14px var(--m-title); font-style: normal; }
.ex-faq-who i:first-child { margin-left: 0; }
.ex-faq-who small { margin-left: 10px; color: var(--ink-2); font-size: 13px; }
.ex-faq details { border-bottom: 1px solid rgba(0,0,0,.09); }
.ex-faq summary { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 58px; padding: 12px 0; cursor: pointer; list-style: none; font-weight: 600; font-size: 15px; }
.ex-faq summary::-webkit-details-marker { display: none; }
.ex-faq summary::after { content: '+'; flex-shrink: 0; color: var(--acc); font: 400 26px/1 var(--m-body); transition: transform 300ms var(--out); }
.ex-faq details[open] summary::after { transform: rotate(45deg); }
.ex-faq details p { margin: 0 0 18px; color: var(--ink-2); line-height: 1.65; animation: exIn 400ms var(--out); }
@keyframes exIn { from { opacity: 0; transform: translateY(8px); } }
/* Avis */
.ex-reviews { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(260px, 100%), 1fr)); max-width: 1000px; margin-inline: auto; gap: 16px; margin-top: 32px; }
.ex-review { display: flex; flex-direction: column; gap: 12px; padding: 22px; border-radius: 14px; background: #fff; box-shadow: 0 0 0 1px var(--line); }
.ex-review p { margin: 0; color: var(--ink-2); line-height: 1.6; flex: 1; }
.ex-review footer { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 14px; }
.ex-review footer i { width: 36px; height: 36px; border-radius: 50%; background: var(--soft); display: flex; align-items: center; justify-content: center; font-style: normal; font-weight: 800; }
.ex-review footer small { display: block; color: var(--acc-dark); font-size: 12px; font-weight: 600; }
/* Pied de page */
.ex-foot { padding: 56px 0 calc(34px + env(safe-area-inset-bottom, 0px)); background: var(--foot); color: #F3F1EE; font-size: 14px; }
.ex-foot-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) repeat(2, minmax(0, 1fr)); gap: 36px; }
.ex-foot b.ex-foot-name { display: block; font: 700 20px var(--m-title); margin-bottom: 10px; }
.ex-foot p { margin: 0; max-width: 40ch; line-height: 1.6; color: #E6E2DD; }
.ex-foot h3 { margin: 0 0 12px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #fff; }
.ex-foot ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 9px; color: #E6E2DD; }
.ex-foot a { text-decoration: none; } .ex-foot a:hover { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
.ex-foot-bottom { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,.18); font-size: 13px; color: #E6E2DD; }
/* Apparitions au défilement */
.ex-js [data-rv] { opacity: 0; transform: translateY(22px); transition: opacity 800ms var(--out), transform 800ms var(--out); transition-delay: var(--d, 0ms); }
.ex-js [data-rv].is-in { opacity: 1; transform: none; }
@media (max-width: 860px) {
    .ex-head .ex-wrap { grid-template-columns: auto 1fr auto; }
    .ex-nav { display: none; position: absolute; left: 0; right: 0; top: 100%; flex-direction: column; padding: 8px max(20px, env(safe-area-inset-left, 0px)) 14px; background: #fff; border-bottom: 1px solid var(--line); box-shadow: 0 20px 30px -24px rgba(0,0,0,.4); }
    .ex-nav.is-open { display: flex; }
    .ex-nav a { padding: 12px; font-size: 15px; }
    .ex-menu { display: inline-flex; }
    .ex-brand { justify-content: center; }
    .ex-brand b { font-size: 18px; }
    .ex-faq, .ex-foot-grid { grid-template-columns: minmax(0, 1fr); }
}
@media (prefers-reduced-motion: reduce) {
    .ex-js [data-rv] { opacity: 1; transform: none; }
    .ex-track { animation: none; }
}
</style>
<script>document.documentElement.classList.add('ex-js');</script>
