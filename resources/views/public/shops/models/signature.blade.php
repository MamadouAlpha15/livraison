{{--
    Modèle de page « Signature » : premium clair. Ivoire #F6F2EA, encre #1A1714, vert profond #1F3B2D,
    laiton #B8893B (détails seulement). Titres Fraunces (serif), texte Plus Jakarta Sans, coins doux, boutons pilule.
    Accueil éditorial en deux colonnes avec carte produit flottante, engagements, catégories en tuiles photo,
    collection filtrable (12 par 12), pièce signature, « comment commander » en 3 étapes, chiffres réels,
    avis réels, questions fréquentes, favoris, barre panier fixe sur mobile. Apparitions au défilement,
    coupées si l'acheteur a réduit les animations. Données : PageModels::data() — rien n'est inventé :
    les chiffres, avis et remises n'apparaissent que s'ils existent dans la boutique.
--}}
@php
    use App\Support\HomeSections as HS;
    $s = $pm['shop'];
    // Sections de l'accueil, dans l'ordre choisi par le vendeur (Boutique → Sections)
    $home = $pm['home']['signature'] ?? HS::defaults('signature');
@endphp
<!doctype html>
<html lang="fr">
<head>
@include('public.shops.models._head')
<script>document.documentElement.classList.add('sg-js');</script>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500;1,9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --ivory: #F6F2EA; --ivory-2: #EFE9DD; --paper: #FFFDF8; --ink: #1A1714; --ink-2: #5E574D; --ink-3: #8A8276;
    --forest: #1F3B2D; --forest-2: #2C5240; --forest-soft: #E3EBE4; --brass: #B8893B; --sale: #A3341F;
    --m-bg: var(--ivory); --m-surface: var(--paper); --m-ink: var(--ink); --m-ink2: var(--ink-2); --m-line: rgba(26,23,20,.12); --m-soft: var(--ivory-2);
    --m-accent: var(--forest); --m-accent-ink: #FFFFFF; --m-accent-text: var(--forest); --m-radius: 22px; --m-btn-radius: 999px;
    --m-title: 'Fraunces', Georgia, serif; --m-body: 'Plus Jakarta Sans', system-ui, sans-serif;
    --m-shadow: 0 1px 2px rgba(26,23,20,.05), 0 12px 32px -12px rgba(26,23,20,.18);
    --out: cubic-bezier(.22,1,.36,1);
}
body { background: var(--ivory); }
a { color: inherit; }
.sg-wrap { max-width: 1240px; margin: 0 auto; padding: 0 max(20px, env(safe-area-inset-left, 0px)); }
.sg-kicker { display: inline-flex; align-items: center; gap: 8px; margin: 0; color: var(--forest); font-size: 12.5px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
.sg-kicker::before { content: ''; width: 18px; height: 1.5px; background: var(--brass); }
.sg-h2 { margin: 10px 0 0; font: 600 clamp(32px, 4.4vw, 52px)/1.06 var(--m-title); letter-spacing: -.02em; text-wrap: balance; font-variation-settings: 'opsz' 120; }
.sg-h2 em { font-style: italic; color: var(--forest); }
.sg-lead { margin: 12px 0 0; max-width: 56ch; color: var(--ink-2); font-size: 16.5px; line-height: 1.6; }
.sg-sec { padding: clamp(64px, 9vw, 112px) 0; }
/* Boutons */
.sg-btn { display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 54px; padding: 0 28px; border: 0; border-radius: 999px; background: var(--forest); color: #fff; font: 700 15.5px var(--m-body); text-decoration: none; cursor: pointer; transition: background-color 250ms var(--out), transform 250ms var(--out), box-shadow 250ms var(--out); box-shadow: 0 10px 24px -12px rgba(31,59,45,.7); }
.sg-btn:hover { background: var(--forest-2); transform: translateY(-2px); }
.sg-btn:active { transform: scale(.98); }
.sg-btn svg { transition: transform 250ms var(--out); }
.sg-btn:hover svg { transform: translateX(3px); }
.sg-btn.is-ghost { background: transparent; color: var(--ink); box-shadow: inset 0 0 0 1.5px rgba(26,23,20,.22); }
.sg-btn.is-ghost:hover { background: var(--paper); box-shadow: inset 0 0 0 1.5px var(--ink); }
.sg-btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }
/* Bandeau */
.sg-band { background: var(--ink); color: #EDE6D8; font-size: 13px; font-weight: 600; }
.sg-band .sg-wrap { display: flex; justify-content: center; align-items: center; gap: 8px 22px; flex-wrap: wrap; min-height: 40px; padding-block: 8px; text-align: center; }
.sg-band span { display: inline-flex; align-items: center; gap: 8px; }
.sg-band span + span::before { content: ''; width: 4px; height: 4px; border-radius: 50%; background: var(--brass); margin-right: 14px; }
/* En-tête */
.sg-head { position: sticky; top: 0; z-index: 60; background: rgba(246,242,234,.82); backdrop-filter: blur(16px) saturate(1.4); -webkit-backdrop-filter: blur(16px) saturate(1.4); transition: box-shadow 300ms var(--out); }
.sg-head.is-stuck { box-shadow: 0 1px 0 var(--m-line), 0 10px 30px -18px rgba(26,23,20,.3); }
.sg-head .sg-wrap { display: flex; align-items: center; gap: 20px; min-height: 76px; }
.sg-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; }
.sg-brand i { width: 44px; height: 44px; flex-shrink: 0; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--forest); color: #fff; font: 600 16px var(--m-title); font-style: normal; }
.sg-brand i img { width: 100%; height: 100%; object-fit: cover; background: var(--paper); }
.sg-brand b { font: 600 22px var(--m-title); letter-spacing: -.01em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sg-brand svg { flex-shrink: 0; color: var(--forest); }
.sg-nav { display: flex; gap: 4px; margin-left: auto; }
.sg-nav a { padding: 10px 14px; border-radius: 999px; text-decoration: none; font-size: 14.5px; font-weight: 600; color: var(--ink-2); transition: background-color 200ms, color 200ms; }
.sg-nav a:hover { background: var(--ivory-2); color: var(--ink); }
.sg-cart { position: relative; display: inline-flex; align-items: center; gap: 10px; min-height: 48px; padding: 0 18px 0 16px; border: 0; border-radius: 999px; background: var(--ink); color: #fff; font: 700 14.5px var(--m-body); cursor: pointer; transition: background-color 200ms; }
.sg-cart:hover { background: #000; }
.sg-cart span[data-sm-count]:empty { display: none; }
.sg-cart span[data-sm-count] { min-width: 24px; height: 24px; padding: 0 7px; border-radius: 999px; background: var(--brass); color: #1A1305; font-size: 12.5px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
/* Accueil */
.sg-hero { position: relative; padding: clamp(36px, 6vw, 72px) 0 clamp(56px, 7vw, 96px); overflow: hidden; }
.sg-hero::before { content: ''; position: absolute; right: -10%; top: -30%; width: 70%; height: 120%; background: radial-gradient(closest-side, rgba(31,59,45,.09), transparent 70%); pointer-events: none; }
.sg-hero .sg-wrap { position: relative; display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); gap: clamp(36px, 6vw, 80px); align-items: center; }
.sg-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.sg-chip { display: inline-flex; align-items: center; gap: 7px; min-height: 34px; padding: 0 14px; border-radius: 999px; background: var(--paper); color: var(--ink); font-size: 13px; font-weight: 700; box-shadow: inset 0 0 0 1px var(--m-line); }
.sg-chip.is-ok { background: var(--forest-soft); color: var(--forest); box-shadow: none; }
.sg-hero h1 { margin: 22px 0 0; font: 500 clamp(44px, 6.6vw, 88px)/.98 var(--m-title); letter-spacing: -.035em; text-wrap: balance; font-variation-settings: 'opsz' 144; overflow-wrap: anywhere; }
.sg-hero h1 em { font-style: italic; color: var(--forest); }
.sg-hero .sg-lead { font-size: 18px; margin-top: 22px; }
.sg-ctas { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 32px; }
.sg-proof { display: flex; align-items: center; gap: 22px; flex-wrap: wrap; margin-top: 34px; padding-top: 26px; border-top: 1px solid var(--m-line); }
.sg-proof div { display: flex; flex-direction: column; gap: 2px; }
.sg-proof b { font: 600 28px var(--m-title); letter-spacing: -.02em; font-variant-numeric: tabular-nums; }
.sg-proof small { color: var(--ink-2); font-size: 13px; font-weight: 600; }
.sg-stars { color: var(--brass); letter-spacing: 2px; font-size: 14px; }
.sg-visual { position: relative; min-height: 520px; }
.sg-visual-main { position: absolute; right: 0; top: 0; width: 82%; aspect-ratio: 4 / 5; border-radius: 28px; overflow: hidden; background: var(--ivory-2); box-shadow: 0 50px 80px -40px rgba(26,23,20,.45); }
.sg-visual-main img { width: 100%; height: 100%; object-fit: cover; display: block; transform: scale(1.04); transition: transform 1600ms var(--out); }
.sg-visual.is-in .sg-visual-main img { transform: none; }
.sg-visual-main .sg-mono { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font: italic 500 clamp(64px, 9vw, 120px) var(--m-title); color: var(--forest); background: radial-gradient(circle at 30% 20%, #fff, var(--ivory-2)); }
.sg-visual-alt { position: absolute; left: 0; top: 12%; width: 36%; aspect-ratio: 3 / 4; border-radius: 20px; overflow: hidden; background: var(--ivory-2); border: 6px solid var(--ivory); box-shadow: 0 30px 50px -28px rgba(26,23,20,.5); will-change: transform; }
.sg-visual-alt img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sg-float { position: absolute; left: 4%; bottom: 4%; z-index: 2; display: grid; grid-template-columns: 58px minmax(0, 1fr) auto; gap: 12px; align-items: center; width: min(360px, 88%); padding: 10px 10px 10px 10px; border-radius: 20px; background: rgba(255,253,248,.92); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 24px 50px -20px rgba(26,23,20,.4), inset 0 0 0 1px rgba(255,255,255,.7); animation: sgBob 7s ease-in-out infinite; }
.sg-float i { width: 58px; height: 58px; border-radius: 14px; background: var(--ivory-2) center / cover; }
.sg-float b { display: block; font-size: 14px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sg-float span { color: var(--forest); font-weight: 800; font-size: 14.5px; font-variant-numeric: tabular-nums; }
.sg-float button { width: 46px; height: 46px; border: 0; border-radius: 50%; background: var(--forest); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 250ms var(--out); }
.sg-float button:hover { transform: rotate(90deg); }
.sg-float button:disabled { opacity: .45; cursor: not-allowed; transform: none; }
.sg-seal { position: absolute; right: -14px; top: 24px; z-index: 2; width: 112px; height: 112px; border-radius: 50%; background: var(--ink); color: #EDE6D8; display: flex; align-items: center; justify-content: center; text-align: center; font: 700 11px/1.35 var(--m-body); letter-spacing: .12em; text-transform: uppercase; box-shadow: 0 20px 40px -18px rgba(0,0,0,.6); }
.sg-seal svg { position: absolute; inset: 0; animation: sgSpin 22s linear infinite; }
.sg-seal text { fill: #EDE6D8; font: 700 9px var(--m-body); letter-spacing: .08em; text-transform: uppercase; }
@keyframes sgBob { 50% { transform: translateY(-10px); } }
@keyframes sgSpin { to { transform: rotate(360deg); } }
/* Engagements */
.sg-promise { border-block: 1px solid var(--m-line); background: var(--paper); }
.sg-promise .sg-wrap { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
.sg-promise div { display: flex; gap: 14px; align-items: flex-start; padding: 28px 22px; }
.sg-promise div + div { border-left: 1px solid var(--m-line); }
.sg-promise svg { flex-shrink: 0; color: var(--forest); }
.sg-promise b { display: block; font-size: 15px; margin-bottom: 3px; }
.sg-promise span { color: var(--ink-2); font-size: 13.5px; line-height: 1.45; }
/* Catégories en tuiles */
.sg-cats { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(220px, 1fr); gap: 16px; margin-top: 34px; overflow-x: auto; padding-bottom: 6px; scroll-snap-type: x mandatory; scrollbar-width: thin; scrollbar-color: var(--forest) transparent; }
.sg-cat { position: relative; display: flex; align-items: flex-end; min-height: 280px; padding: 0; border: 0; border-radius: var(--m-radius); overflow: hidden; background: var(--forest); color: #fff; text-align: left; cursor: pointer; scroll-snap-align: start; isolation: isolate; }
.sg-cat img { position: absolute; inset: 0; z-index: -2; width: 100%; height: 100%; object-fit: cover; transition: transform 900ms var(--out); }
.sg-cat::before { content: ''; position: absolute; inset: 0; z-index: -1; background: linear-gradient(180deg, transparent 35%, rgba(16,14,12,.78)); }
.sg-cat:hover img { transform: scale(1.06); }
.sg-cat span { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; width: 100%; padding: 20px; }
.sg-cat b { font: 500 26px/1.1 var(--m-title); }
.sg-cat small { flex-shrink: 0; padding: 6px 10px; border-radius: 999px; background: rgba(255,255,255,.18); backdrop-filter: blur(6px); font-size: 12.5px; font-weight: 700; }
/* Collection */
.sg-tools { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.sg-filters { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin: 30px 0 26px; }
.sg-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
.sg-tabs button { min-height: 42px; padding: 0 16px; border: 0; border-radius: 999px; background: transparent; color: var(--ink-2); font: 700 14px var(--m-body); cursor: pointer; box-shadow: inset 0 0 0 1px var(--m-line); transition: background-color 200ms, color 200ms, box-shadow 200ms; }
.sg-tabs button:hover { color: var(--ink); box-shadow: inset 0 0 0 1px var(--ink-3); }
.sg-tabs button[aria-pressed="true"] { background: var(--ink); color: #fff; box-shadow: none; }
.sg-tabs small { margin-left: 6px; opacity: .65; font-variant-numeric: tabular-nums; }
.sg-sort { min-height: 44px; padding: 0 40px 0 16px; border: 0; border-radius: 999px; background: var(--paper) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none' stroke='%231A1714' stroke-width='1.8'%3E%3Cpath d='M1 1.5l5 5 5-5'/%3E%3C/svg%3E") no-repeat right 16px center; color: var(--ink); font: 700 14px var(--m-body); box-shadow: inset 0 0 0 1px var(--m-line); appearance: none; -webkit-appearance: none; cursor: pointer; }
.sg-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 34px 20px; }
.sg-card { position: relative; display: flex; flex-direction: column; animation: sgIn 700ms var(--out) both; animation-delay: calc(var(--i, 0) * 55ms); }
@keyframes sgIn { from { opacity: 0; transform: translateY(18px); } }
.sg-card-media { position: relative; border-radius: var(--m-radius); overflow: hidden; background: var(--ivory-2); }
.sg-card-img { display: block; width: 100%; aspect-ratio: 4 / 5; padding: 0; border: 0; background: none; cursor: pointer; }
.sg-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 1000ms var(--out); }
.sg-card:hover .sg-card-img img { transform: scale(1.05); }
.sg-card-img .sg-noimg { display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 18px; font: italic 500 22px/1.2 var(--m-title); color: var(--forest); text-align: center; }
.sg-card-quick { position: absolute; left: 50%; top: 50%; padding: 10px 16px; border-radius: 999px; background: rgba(255,253,248,.94); color: var(--ink); font-size: 13px; font-weight: 800; opacity: 0; transform: translate(-50%, -30%); transition: opacity 300ms var(--out), transform 300ms var(--out); pointer-events: none; white-space: nowrap; }
.sg-card:hover .sg-card-quick { opacity: 1; transform: translate(-50%, -50%); }
.sg-badges { position: absolute; left: 12px; top: 12px; display: flex; flex-direction: column; align-items: flex-start; gap: 6px; pointer-events: none; }
.sg-badge { padding: 5px 10px; border-radius: 999px; background: var(--paper); color: var(--ink); font-size: 11.5px; font-weight: 800; letter-spacing: .04em; }
.sg-badge.is-sale { background: var(--sale); color: #fff; }
.sg-badge.is-new { background: var(--forest); color: #fff; }
.sg-card-fav { position: absolute; top: 10px; right: 10px; width: 42px; height: 42px; border: 0; border-radius: 50%; background: rgba(255,253,248,.92); color: var(--ink); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 260ms cubic-bezier(.34,1.56,.64,1), color 200ms; }
.sg-card-fav[aria-pressed="true"] { color: #C0263D; } .sg-card-fav[aria-pressed="true"] svg { fill: currentColor; }
.sg-card-fav.is-pop { transform: scale(1.22); }
.sg-card-body { display: flex; flex-direction: column; gap: 4px; padding: 14px 4px 0; flex: 1; }
.sg-card-body small { color: var(--ink-3); font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
.sg-card-body h3 { margin: 0; font: 500 19px/1.25 var(--m-title); letter-spacing: -.01em; }
.sg-price { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; font-weight: 800; font-size: 16px; font-variant-numeric: tabular-nums; }
.sg-price s { color: var(--ink-3); font-size: 13.5px; font-weight: 600; }
.sg-card-add { margin-top: 12px; min-height: 46px; border: 0; border-radius: 999px; background: transparent; color: var(--ink); font: 800 14px var(--m-body); cursor: pointer; box-shadow: inset 0 0 0 1.5px rgba(26,23,20,.22); transition: background-color 220ms, color 220ms, box-shadow 220ms; }
.sg-card-add:hover:not(:disabled) { background: var(--forest); color: #fff; box-shadow: none; }
.sg-card-add.sm-added { animation: sgAdded 600ms var(--out); }
@keyframes sgAdded { 30% { background: var(--forest); color: #fff; transform: scale(.97); } }
.sg-card-add:disabled { opacity: .5; cursor: not-allowed; }
.sg-more { display: flex; justify-content: center; margin-top: 44px; }
.sg-empty { grid-column: 1 / -1; padding: 60px 20px; border-radius: var(--m-radius); text-align: center; color: var(--ink-2); background: var(--paper); box-shadow: inset 0 0 0 1px var(--m-line); }
/* Pièce signature */
.sg-spot { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: clamp(36px, 6vw, 84px); align-items: center; }
.sg-spot-media { position: relative; border-radius: 32px; overflow: hidden; aspect-ratio: 1; background: var(--ivory-2); box-shadow: var(--m-shadow); }
.sg-spot-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.sg-spot-price { position: absolute; right: 18px; bottom: 18px; padding: 12px 18px; border-radius: 999px; background: var(--ink); color: #fff; font-weight: 800; font-size: 17px; font-variant-numeric: tabular-nums; }
.sg-spot-desc { margin: 18px 0 0; color: var(--ink-2); font-size: 16px; line-height: 1.7; max-width: 52ch; display: -webkit-box; -webkit-line-clamp: 6; -webkit-box-orient: vertical; overflow: hidden; }
.sg-ticks { display: grid; gap: 10px; margin: 24px 0 0; padding: 0; list-style: none; }
.sg-ticks li { display: flex; gap: 10px; align-items: center; font-weight: 600; font-size: 15px; }
.sg-ticks li::before { content: ''; flex-shrink: 0; width: 22px; height: 22px; border-radius: 50%; background: var(--forest-soft) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%231F3B2D' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M2 6.5l2.6 2.6L10 3.5'/%3E%3C/svg%3E") no-repeat center; }
/* Comment commander */
.sg-how { background: var(--forest); color: #EEF2EC; }
.sg-how .sg-kicker { color: #CFE0D3; }
.sg-how .sg-h2 em { color: #E9D3A6; }
.sg-how .sg-lead { color: #BFD0C4; }
.sg-steps { position: relative; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-top: 46px; counter-reset: step; }
.sg-steps::before { content: ''; position: absolute; left: 8%; right: 8%; top: 34px; height: 1px; background: repeating-linear-gradient(90deg, rgba(255,255,255,.35) 0 6px, transparent 6px 12px); }
.sg-step { position: relative; padding: 0 10px; text-align: center; }
.sg-step i { position: relative; display: flex; align-items: center; justify-content: center; width: 68px; height: 68px; margin: 0 auto 18px; border-radius: 50%; background: #EEF2EC; color: var(--forest); font-style: normal; box-shadow: 0 0 0 8px var(--forest); }
.sg-step i::after { counter-increment: step; content: counter(step); position: absolute; right: -4px; top: -4px; width: 24px; height: 24px; border-radius: 50%; background: var(--brass); color: #1A1305; font: 800 12px/24px var(--m-body); text-align: center; }
.sg-step b { display: block; font: 500 23px var(--m-title); color: #fff; }
.sg-step span { display: block; margin: 8px auto 0; max-width: 30ch; color: #BFD0C4; font-size: 14.5px; line-height: 1.55; }
/* Chiffres */
.sg-numbers { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1px; margin-top: 38px; border-radius: var(--m-radius); overflow: hidden; background: var(--m-line); box-shadow: inset 0 0 0 1px var(--m-line); }
.sg-numbers div { padding: 30px 26px; background: var(--paper); }
.sg-numbers b { display: block; font: 500 clamp(40px, 5vw, 58px)/1 var(--m-title); letter-spacing: -.03em; color: var(--forest); font-variant-numeric: tabular-nums; }
.sg-numbers span { display: block; margin-top: 10px; color: var(--ink-2); font-size: 14.5px; font-weight: 600; }
/* Avis */
.sg-reviews { columns: 3 300px; column-gap: 18px; margin-top: 38px; }
.sg-review { break-inside: avoid; margin: 0 0 18px; padding: 26px; border-radius: var(--m-radius); background: var(--paper); box-shadow: var(--m-shadow); }
.sg-review blockquote { margin: 12px 0 0; font: 500 19px/1.5 var(--m-title); letter-spacing: -.005em; }
.sg-review figcaption { display: flex; align-items: center; gap: 10px; margin-top: 18px; font-size: 14px; font-weight: 700; }
.sg-review figcaption i { width: 36px; height: 36px; border-radius: 50%; background: var(--forest-soft); color: var(--forest); display: flex; align-items: center; justify-content: center; font: 600 15px var(--m-title); font-style: normal; }
/* Questions */
.sg-faq { display: grid; grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr); gap: clamp(30px, 6vw, 80px); align-items: start; }
.sg-faq details { border-bottom: 1px solid var(--m-line); }
.sg-faq summary { display: flex; align-items: center; justify-content: space-between; gap: 18px; min-height: 64px; padding: 14px 0; cursor: pointer; list-style: none; font: 500 20px/1.3 var(--m-title); }
.sg-faq summary::-webkit-details-marker { display: none; }
.sg-faq summary::after { content: ''; flex-shrink: 0; width: 36px; height: 36px; border-radius: 50%; background: var(--paper) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%231A1714' stroke-width='2' stroke-linecap='round'%3E%3Cpath d='M7 1v12M1 7h12'/%3E%3C/svg%3E") no-repeat center; box-shadow: inset 0 0 0 1px var(--m-line); transition: transform 300ms var(--out), background-color 300ms; }
.sg-faq details[open] summary::after { transform: rotate(45deg); background-color: var(--forest-soft); }
.sg-faq details p { margin: 0 0 22px; max-width: 60ch; color: var(--ink-2); font-size: 15.5px; line-height: 1.65; animation: sgIn 400ms var(--out); }
/* Favoris */
.sg-strip { display: grid; grid-auto-flow: column; grid-auto-columns: 168px; gap: 16px; overflow-x: auto; padding-bottom: 8px; scrollbar-width: thin; scrollbar-color: var(--forest) transparent; }
.sg-strip button { border: 0; padding: 0; background: none; color: var(--ink); text-align: left; cursor: pointer; font: 600 14px/1.3 var(--m-body); }
.sg-strip i { display: block; aspect-ratio: 4 / 5; margin-bottom: 8px; border-radius: 16px; background: var(--ivory-2) center / cover; }
.sg-strip b { display: block; margin-top: 2px; color: var(--forest); font-variant-numeric: tabular-nums; }
.sg-mine-empty { margin: 0; color: var(--ink-2); }
/* Pied de page */
.sg-foot { padding: 72px 0 calc(40px + env(safe-area-inset-bottom, 0px)); background: var(--ink); color: #BDB4A6; font-size: 14.5px; }
.sg-foot-top { display: grid; grid-template-columns: minmax(0, 1.4fr) repeat(2, minmax(0, 1fr)); gap: 40px; padding-bottom: 44px; border-bottom: 1px solid rgba(255,255,255,.12); }
.sg-foot-brand b { display: block; font: 500 34px var(--m-title); color: #F6F2EA; letter-spacing: -.02em; }
.sg-foot-brand p { margin: 10px 0 0; max-width: 40ch; line-height: 1.6; }
.sg-foot h3 { margin: 0 0 14px; color: #F6F2EA; font-size: 12.5px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
.sg-foot ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 10px; }
.sg-foot a { text-decoration: none; } .sg-foot a:hover { color: #fff; text-decoration: underline; text-underline-offset: 3px; }
.sg-foot-bottom { display: flex; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding-top: 24px; font-size: 13px; }
/* Barre panier fixe (mobile) */
.sg-dock { position: fixed; left: 12px; right: 12px; bottom: max(12px, env(safe-area-inset-bottom, 0px)); z-index: 70; display: none; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 8px 8px 18px; border-radius: 999px; background: var(--ink); color: #fff; box-shadow: 0 20px 40px -14px rgba(0,0,0,.55); animation: sgDock 420ms var(--out); }
.sg-dock small { display: block; color: #BDB4A6; font-size: 12px; font-weight: 600; }
.sg-dock b { font-size: 16px; font-variant-numeric: tabular-nums; }
.sg-dock button { min-height: 46px; padding: 0 20px; border: 0; border-radius: 999px; background: var(--brass); color: #1A1305; font: 800 14.5px var(--m-body); cursor: pointer; }
@keyframes sgDock { from { opacity: 0; transform: translateY(30px); } }
/* Apparitions au défilement */
.sg-js [data-rv] { opacity: 0; transform: translateY(26px); transition: opacity 900ms var(--out), transform 900ms var(--out); transition-delay: var(--d, 0ms); }
.sg-js [data-rv].is-in { opacity: 1; transform: none; }
@media (max-width: 1080px) {
    .sg-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .sg-promise .sg-wrap { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sg-promise div:nth-child(3) { border-left: 0; }
    .sg-promise div:nth-child(n+3) { border-top: 1px solid var(--m-line); }
}
@media (max-width: 860px) {
    .sg-nav { display: none; }
    .sg-head .sg-cart { margin-left: auto; }
    .sg-head .sg-wrap { min-height: 64px; }
    .sg-cart .sg-cart-l { display: none; }
    .sg-hero .sg-wrap, .sg-spot, .sg-faq { grid-template-columns: minmax(0, 1fr); }
    .sg-visual { min-height: 0; aspect-ratio: 1 / 1.08; }
    .sg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 26px 12px; }
    .sg-card-body h3 { font-size: 16.5px; }
    .sg-card-quick { display: none; }
    .sg-steps { grid-template-columns: minmax(0, 1fr); gap: 30px; }
    .sg-steps::before { left: 34px; right: auto; top: 0; bottom: 0; width: 1px; height: auto; background: repeating-linear-gradient(180deg, rgba(255,255,255,.35) 0 6px, transparent 6px 12px); }
    .sg-step { display: grid; grid-template-columns: 68px minmax(0, 1fr); gap: 4px 18px; text-align: left; padding: 0; }
    .sg-step i { margin: 0; grid-row: span 2; }
    .sg-step span { margin: 0; }
    .sg-foot-top { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    .sg-foot-brand { grid-column: 1 / -1; }
    .sg-seal { width: 92px; height: 92px; right: -4px; }
    body.has-dock { padding-bottom: 84px; }
    body.has-dock .sg-dock { display: flex; }
    body.has-dock .sm-toast { bottom: calc(84px + env(safe-area-inset-bottom, 0px)); }
}
@media (max-width: 560px) {
    .sg-promise .sg-wrap { grid-template-columns: minmax(0, 1fr); }
    .sg-promise div { padding: 18px 4px; }
    .sg-promise div + div { border-left: 0; border-top: 1px solid var(--m-line); }
    .sg-band span + span::before { display: none; }
    .sg-band .sg-wrap { gap: 2px 14px; font-size: 12.5px; }
    .sg-ctas .sg-btn { flex: 1 1 100%; }
    .sg-card-add { min-height: 42px; font-size: 13px; }
    .sg-filters { flex-direction: column; align-items: stretch; }
    .sg-filters > * { min-width: 0; max-width: 100%; }
    .sg-tabs { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 4px; }
    .sg-tabs button { flex-shrink: 0; }
}
@media (prefers-reduced-motion: reduce) {
    .sg-js [data-rv] { opacity: 1; transform: none; }
    .sg-float, .sg-seal svg { animation: none; }
}
</style>
@include('public.shops.models._colors', ['theme' => 'signature'])
</head>
<body>
@include('public.shops.models._top')
<div class="sg-band" role="note"><div class="sg-wrap">@if($s['verified'])<span>Boutique vérifiée par Shopio</span>@endif<span>Paiement à la livraison</span><span>Confirmation par téléphone</span>@if($s['address'] !== '')<span>{{ \Illuminate\Support\Str::limit($s['address'], 40) }}</span>@endif</div></div>
<header class="sg-head" id="sgHead" @if(!empty($editFrame)) data-sx="header" data-sx-name="En-tête" @endif>
    <div class="sg-wrap">
        <a class="sg-brand" href="{{ $s['url'] }}"><i>@if($s['logo'])<img src="{{ $s['logo'] }}" alt="">@else{{ $s['initials'] }}@endif</i><b>{{ $s['name'] }}</b>@if($s['verified'])<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" role="img" aria-label="Boutique vérifiée"><path d="M12 1.5l2.6 2 3.3-.2.9 3.2 2.8 1.8-1.2 3.1 1.2 3.1-2.8 1.8-.9 3.2-3.3-.2-2.6 2-2.6-2-3.3.2-.9-3.2-2.8-1.8 1.2-3.1-1.2-3.1 2.8-1.8.9-3.2 3.3.2z"/><path d="M8 12.2l2.6 2.6L16.2 9" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>@endif</a>
        <nav class="sg-nav" aria-label="Sections">@if(HS::shows($home, 'products'))<a href="#collection">La collection</a>@endif @if(HS::shows($home, 'how'))<a href="#commander">Commander</a>@endif @if(count($pm['reviews']) && HS::shows($home, 'reviews'))<a href="#avis">Avis</a>@endif @if(HS::shows($home, 'faq'))<a href="#questions">Questions</a>@endif</nav>
        <button type="button" class="sg-cart" data-sm-cart aria-label="Ouvrir le panier"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7zM9 7a3 3 0 0 1 6 0"/></svg><span class="sg-cart-l">Panier</span><span data-sm-count></span></button>
    </div>
</header>

<main>
@foreach($home as $sec)
@continue(!$sec['on'] && empty($editFrame))
@if(!empty($editFrame))<div class="sxe-sec" data-sx="{{ $sec['id'] }}" data-sx-type="{{ $sec['type'] }}" data-sx-on="{{ $sec['on'] ? 1 : 0 }}" data-sx-name="{{ HS::TYPES[$sec['type']][0] ?? $sec['type'] }}" data-sx-builtin="{{ in_array($sec['type'], HS::BUILTIN['signature'], true) ? 1 : 0 }}">@endif
@if($sec['on'])
@switch($sec['type'])
    @case('hero')
    @php
        $heroTitle = $sec['d']['title'] !== '' ? $sec['d']['title'] : ($s['slogan'] ?: $s['name']);
        $words = preg_split('/\s+/u', trim($heroTitle));
        $last = array_pop($words);
        $heroHead = count($words) ? implode(' ', $words) . ' ' : '';
        $heroImg = HS::imgUrl($sec['d']['img']);
    @endphp
    <section class="sg-hero" aria-labelledby="sgTitle">
        <div class="sg-wrap">
            <div>
                <div class="sg-chips" data-rv>
                    @if($s['verified'])<span class="sg-chip is-ok"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12.5l5 5L20 6.5"/></svg>Boutique vérifiée</span>@endif
                    <span class="sg-chip">Paiement à la livraison</span>
                </div>
                <h1 id="sgTitle" data-rv style="--d:80ms">{{ $heroHead }}<em>{{ $last }}</em></h1>
                <p class="sg-lead" data-rv style="--d:160ms">@if($sec['d']['text'] !== ''){{ $sec['d']['text'] }}@else{{ $s['slogan'] !== '' && $s['slogan'] !== $s['name'] ? $s['name'] . ' · ' : '' }}{{ HS::txt('signature', $sec, 'text') }}@endif</p>
                <div class="sg-ctas" data-rv style="--d:240ms">
                    <a class="sg-btn" href="{{ HS::shows($home, 'products') ? '#collection' : $s['url'] . '?vue=produits' }}">{{ HS::txt('signature', $sec, 'button') }} <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                    @if(HS::shows($home, 'how'))<a class="sg-btn is-ghost" href="#commander">Comment commander</a>@endif
                </div>
                @if($pm['rating'] || $pm['delivered'])
                <div class="sg-proof" data-rv style="--d:320ms">
                    @if($pm['rating'])<div><b>{{ number_format($pm['rating']['avg'], 1, ',', ' ') }} <span class="sg-stars" aria-hidden="true">★★★★★</span></b><small>sur {{ $pm['rating']['count'] }} avis de clients</small></div>@endif
                    @if($pm['delivered'])<div><b>{{ number_format($pm['delivered'], 0, ',', ' ') }}</b><small>commandes livrées</small></div>@endif
                </div>
                @endif
            </div>
            <div class="sg-visual" id="sgVisual" data-rv style="--d:120ms" @if($heroImg) data-img="{{ $heroImg }}" @endif></div>
        </div>
    </section>
    @break

    @case('promise')
    @php $pIcons = [
        '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/>',
        '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
        '<path d="M1 6h13v10H1zM14 9h4l4 4v3h-8z"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
    ]; @endphp
    <section class="sg-promise" aria-label="Nos engagements">
        <div class="sg-wrap">
            @foreach($pIcons as $i => $path)
            @php $it = HS::item('signature', $sec, $i); @endphp
            <div data-rv style="--d:{{ $i * 80 }}ms"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $path !!}</svg><p style="margin:0"><b>{{ $it['title'] }}</b><span>{{ $it['text'] }}</span></p></div>
            @endforeach
            <div data-rv style="--d:240ms"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2l8 3v6c0 5-3.4 9.4-8 11-4.6-1.6-8-6-8-11V5z"/><path d="M8.5 12l2.5 2.5 4.5-5"/></svg><p style="margin:0">@if($s['verified'])<b>Boutique vérifiée</b><span>Contrôlée par Shopio avant d’ouvrir.</span>@else<b>Commande via Shopio</b><span>Votre commande est enregistrée et suivie.</span>@endif</p></div>
        </div>
    </section>
    @break

    @case('categories')
    @if(count($pm['categories']) > 1)
    <section class="sg-sec" aria-labelledby="sgCatH" style="padding-bottom:0">
        <div class="sg-wrap">
            <div data-rv><p class="sg-kicker">Explorer</p><h2 class="sg-h2" id="sgCatH">@if($sec['d']['title'] !== ''){{ $sec['d']['title'] }}@else Par <em>univers</em>@endif</h2></div>
            <div class="sg-cats" id="sgCats"></div>
        </div>
    </section>
    @endif
    @break

    @case('products')
    <section class="sg-sec" id="collection" aria-labelledby="sgColH">
        <div class="sg-wrap">
            <div class="sg-tools" data-rv>
                <div><p class="sg-kicker">La collection</p><h2 class="sg-h2" id="sgColH">@if($sec['d']['title'] !== ''){{ $sec['d']['title'] }}@else Pensé pour <em>durer</em>@endif</h2>@if($sec['d']['text'] !== '')<p class="sg-lead" style="margin-top:10px">{{ $sec['d']['text'] }}</p>@endif</div>
                <p class="sg-lead" id="sgCount" style="margin:0" aria-live="polite"></p>
            </div>
            <div class="sg-filters">
                <div class="sg-tabs" role="group" aria-label="Catégories" id="sgTabs"></div>
                <label><span class="sm-sr">Trier</span><select class="sg-sort" id="sgSort"><option value="">Mis en avant</option><option value="new">Nouveautés</option><option value="asc">Prix croissant</option><option value="desc">Prix décroissant</option></select></label>
            </div>
            <div class="sg-grid" id="sgGrid"></div>
            <div class="sg-more"><button type="button" class="sg-btn is-ghost" id="sgMore" hidden>Voir plus de produits</button></div>
        </div>
    </section>
    @break

    @case('spotlight')
    <section class="sg-sec" id="signature" aria-labelledby="sgSpotH" style="background:var(--paper);border-block:1px solid var(--m-line)">
        <div class="sg-wrap" id="sgSpot"></div>
    </section>
    @break

    @case('how')
    <section class="sg-sec sg-how" id="commander" aria-labelledby="sgHowH">
        <div class="sg-wrap">
            <div data-rv style="text-align:center;display:flex;flex-direction:column;align-items:center"><p class="sg-kicker">Simple et sûr</p><h2 class="sg-h2" id="sgHowH">@if($sec['d']['title'] !== ''){{ $sec['d']['title'] }}@else Commander en <em>une minute</em>@endif</h2><p class="sg-lead">{{ HS::txt('signature', $sec, 'text') }}</p></div>
            <ol class="sg-steps" style="list-style:none;padding:0">
                <li class="sg-step" data-rv><i><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7zM9 7a3 3 0 0 1 6 0"/></svg></i><b>Vous commandez</b><span>Votre nom, votre téléphone et votre commune. C’est tout.</span></li>
                <li class="sg-step" data-rv style="--d:120ms"><i><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg></i><b>On vous appelle</b><span>La boutique confirme la commande, le délai et les frais de livraison.</span></li>
                <li class="sg-step" data-rv style="--d:240ms"><i><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/></svg></i><b>Vous payez à la livraison</b><span>En espèces, au livreur, une fois le colis entre vos mains.</span></li>
            </ol>
            <div style="display:flex;justify-content:center;margin-top:44px" data-rv><button type="button" class="sg-btn" style="background:#F6F2EA;color:var(--forest)" data-sm-cart>Voir mon panier</button></div>
        </div>
    </section>
    @break

    @case('reviews')
    @if($pm['rating'] || $pm['delivered'] || count($pm['reviews']))
    <section class="sg-sec" id="avis" aria-labelledby="sgRevH">
        <div class="sg-wrap">
            <div data-rv><p class="sg-kicker">Ils ont commandé</p><h2 class="sg-h2" id="sgRevH">@if($sec['d']['title'] !== ''){{ $sec['d']['title'] }}@else Ce qu’en disent <em>nos clients</em>@endif</h2></div>
            @if($pm['rating'] || $pm['delivered'])
            <div class="sg-numbers" data-rv>
                @if($pm['delivered'])<div><b data-sg-count="{{ $pm['delivered'] }}">{{ number_format($pm['delivered'], 0, ',', ' ') }}</b><span>commandes livrées</span></div>@endif
                @if($pm['rating'])<div><b>{{ number_format($pm['rating']['avg'], 1, ',', ' ') }}<small style="font-size:.45em;color:var(--ink-3)"> / 5</small></b><span>note moyenne sur {{ $pm['rating']['count'] }} avis</span></div>@endif
                <div><b data-sg-count="{{ count($pm['products']) }}">{{ count($pm['products']) }}</b><span>produit{{ count($pm['products']) > 1 ? 's' : '' }} en vente</span></div>
            </div>
            @endif
            @if(count($pm['reviews']))
            <div class="sg-reviews">
                @foreach($pm['reviews'] as $i => $r)
                <figure class="sg-review" data-rv style="--d:{{ min($i, 5) * 70 }}ms">
                    <span class="sg-stars" role="img" aria-label="{{ max(1, min(5, $r['rating'])) }} sur 5">{{ str_repeat('★', max(1, min(5, $r['rating']))) }}<span style="opacity:.25">{{ str_repeat('★', 5 - max(1, min(5, $r['rating']))) }}</span></span>
                    <blockquote>{{ $r['text'] }}</blockquote>
                    <figcaption><i aria-hidden="true">{{ mb_strtoupper(mb_substr($r['name'], 0, 1)) }}</i>{{ $r['name'] }}</figcaption>
                </figure>
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
        if ($faqItems->isEmpty()) $faqItems = collect(array_values(array_filter([
            ['q' => 'Comment passer commande ?', 'a' => 'Ajoutez vos produits au panier, puis indiquez votre nom, votre téléphone et votre commune. Cela prend moins d’une minute.'],
            ['q' => 'Quand est-ce que je paie ?', 'a' => 'À la livraison, en espèces, au livreur. Vous ne payez rien avant d’avoir le produit en main.'],
            ['q' => 'Comment savoir si ma commande est confirmée ?', 'a' => 'La boutique vous appelle au numéro indiqué pour confirmer la commande, le délai et les frais de livraison.'],
            ['q' => 'Puis-je commander plusieurs produits ?', 'a' => 'Oui. Ajoutez-les tous au panier : ils partent dans une seule commande.'],
            ['q' => 'Comment suivre ma commande ?', 'a' => 'Après l’envoi, un lien « Suivre ma commande » s’affiche. Vous suivez chaque étape sur Shopio.'],
            $pm['settings']['nego'] ? ['q' => 'Puis-je négocier le prix ?', 'a' => 'Oui. Ouvrez un produit puis touchez « Négocier le prix avec la boutique ».'] : null,
        ])));
    @endphp
    <section class="sg-sec" id="questions" aria-labelledby="sgFaqH" style="background:var(--paper);border-top:1px solid var(--m-line)">
        <div class="sg-wrap sg-faq">
            <div data-rv><p class="sg-kicker">Questions</p><h2 class="sg-h2" id="sgFaqH">@if($sec['d']['title'] !== ''){{ $sec['d']['title'] }}@else Les réponses à vos <em>questions</em>@endif</h2><p class="sg-lead">Une autre question ? La boutique vous répond quand elle vous appelle pour confirmer.</p></div>
            <div data-rv style="--d:100ms">
                @foreach($faqItems as $i => $f)<details @if($i === 0) open @endif><summary>{{ $f['q'] }}</summary>@if($f['a'] !== '')<p style="white-space:pre-line">{{ $f['a'] }}</p>@endif</details>@endforeach
            </div>
        </div>
    </section>
    @break

    @default
    @include('public.shops.models._section', ['sec' => $sec, 'theme' => 'signature'])
@endswitch
@endif
@if(!empty($editFrame))</div>@endif
@endforeach

    <section class="sg-sec" id="favoris" aria-labelledby="sgFavH">
        <div class="sg-wrap">
            <h2 class="sg-h2" id="sgFavH" style="font-size:34px">Vos favoris</h2>
            <div id="sgFavs" style="margin-top:20px"></div>
            <h2 class="sg-h2" style="font-size:28px;margin-top:44px">Vus récemment</h2>
            <div id="sgRecent" style="margin-top:20px"></div>
        </div>
    </section>
</main>

<footer class="sg-foot">
    <div class="sg-wrap">
        <div class="sg-foot-top">
            <div class="sg-foot-brand"><b>{{ $s['name'] }}</b><p>{{ $s['slogan'] !== '' ? $s['slogan'] : 'Commandez en ligne, payez à la livraison.' }}</p></div>
            <div><h3>Boutique</h3><ul>@if(HS::shows($home, 'products'))<li><a href="#collection">La collection</a></li>@endif @if(count($pm['reviews']) && HS::shows($home, 'reviews'))<li><a href="#avis">Avis clients</a></li>@endif<li><a href="#favoris">Mes favoris</a></li></ul></div>
            <div><h3>Aide</h3><ul>@if(HS::shows($home, 'how'))<li><a href="#commander">Comment commander</a></li>@endif @if(HS::shows($home, 'faq'))<li><a href="#questions">Questions fréquentes</a></li>@endif @if($s['address'] !== '')<li>{{ \Illuminate\Support\Str::limit($s['address'], 50) }}</li>@endif</ul></div>
        </div>
        <div class="sg-foot-bottom"><span>© {{ date('Y') }} {{ $s['name'] }}</span><span>Paiement à la livraison · Boutique sur Shopio</span></div>
    </div>
</footer>

<div class="sg-dock" id="sgDock" aria-hidden="true"><div><small><span data-sm-count></span> article(s)</small><b data-sm-total></b></div><button type="button" data-sm-cart tabindex="-1">Commander</button></div>

@include('public.shops.models._kit')
@if(!empty($editFrame))@include('public.shops.models._edit-frame')@endif
<script>
(function () {
    'use strict';
    const S = window.SM, D = S.data, esc = S.esc, $ = id => document.getElementById(id);
    const heart = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 7.9 3.6 4.5 7 4.5c2 0 3.5 1.1 5 3 1.5-1.9 3-3 5-3 3.4 0 5.6 3.4 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"/></svg>';
    const plus = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';
    const withPhoto = S.products.filter(p => p.photo);
    const off = p => p.old ? Math.round((1 - p.price / p.old) * 100) : 0;

    /* ── Apparitions au défilement ── */
    const io = 'IntersectionObserver' in window && !S.reduce ? new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } }), { rootMargin: '0px 0px -8% 0px', threshold: .08 }) : null;
    const watch = root => (root || document).querySelectorAll('[data-rv]:not(.is-in)').forEach(el => io ? io.observe(el) : el.classList.add('is-in'));

    /* ── En-tête : ombre une fois la page défilée ── */
    const head = $('sgHead');
    const onScroll = () => head.classList.toggle('is-stuck', window.scrollY > 8);
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();

    /* ── Accueil : photo principale, photo secondaire, carte produit flottante, sceau tournant ── */
    const hero = S.products.find(p => p.featured && p.photo) || withPhoto[0] || S.products[0];
    const alt = hero ? withPhoto.find(p => p.id !== hero.id) : null;
    const vis = $('sgVisual'), ownImg = vis && vis.dataset.img;
    if (vis && (hero || ownImg)) {
        vis.innerHTML =
            '<div class="sg-visual-main">' + (ownImg ? '<img src="' + esc(ownImg) + '" alt="" fetchpriority="high">' : hero.photo ? '<img src="' + esc(hero.photo) + '" alt="' + esc(hero.name) + '" fetchpriority="high">' : '<span class="sg-mono">' + esc(D.shop.initials) + '</span>') + '</div>' +
            (alt ? '<div class="sg-visual-alt" id="sgAlt"><img src="' + esc(alt.photo) + '" alt="' + esc(alt.name) + '"></div>' : '') +
            '<div class="sg-seal" aria-hidden="true"><svg viewBox="0 0 112 112"><defs><path id="sgCirc" d="M56 56m-42 0a42 42 0 1 1 84 0a42 42 0 1 1-84 0"/></defs><text><textPath href="#sgCirc" textLength="258" lengthAdjust="spacingAndGlyphs">Paiement à la livraison · Rien à payer avant ·</textPath></text></svg><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="position:static;animation:none"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/></svg></div>' +
            (hero ? '<div class="sg-float"><i' + (hero.thumb ? ' style="background-image:url(\'' + esc(hero.thumb) + '\')"' : '') + '></i><div style="min-width:0"><b>' + esc(hero.name) + '</b><span>' + esc(S.money(hero.price)) + '</span></div><button type="button" data-sm-add="' + hero.id + '" aria-label="' + esc(S.buyLabel(hero)) + ' : ' + esc(hero.name) + '"' + (S.canBuy(hero) ? '' : ' disabled') + '>' + plus + '</button></div>' : '');
        const altEl = $('sgAlt');
        if (altEl && !S.reduce) {
            let tick = false;
            window.addEventListener('scroll', () => { if (tick) return; tick = true; requestAnimationFrame(() => { altEl.style.transform = 'translateY(' + Math.min(80, window.scrollY * .08) + 'px)'; tick = false; }); }, { passive: true });
        }
    } else if (vis) { vis.hidden = true; }

    /* ── Catégories en tuiles photo : cliquer filtre la collection ── */
    let cat = @json($activeCategory['name'] ?? ''), sort = '', shown = 12;
    const PAGE = 12;
    if ($('sgCats')) {
        $('sgCats').innerHTML = D.categories.map(c => { const p = withPhoto.find(x => x.cat === c.name); return '<button type="button" class="sg-cat" data-cat="' + esc(c.name) + '">' + (p ? '<img src="' + esc(p.photo) + '" alt="" loading="lazy">' : '') + '<span><b>' + esc(c.name) + '</b><small>' + c.count + ' produit' + (c.count > 1 ? 's' : '') + '</small></span></button>'; }).join('');
        $('sgCats').addEventListener('click', e => { const b = e.target.closest('[data-cat]'); if (!b) return; cat = b.dataset.cat; shown = PAGE; renderTabs(); renderGrid(); if ($('collection')) $('collection').scrollIntoView({ behavior: S.reduce ? 'auto' : 'smooth' }); else location.href = D.shop.url + '?vue=produits'; });
    }

    /* ── La collection : onglets, tri, grille, « voir plus » ── */
    function renderTabs() {
        if (!$('sgTabs')) return;
        const tabs = [['', 'Tout', S.products.length]].concat(D.categories.map(c => [c.name, c.name, c.count]));
        $('sgTabs').innerHTML = tabs.map(([v, l, c]) => '<button type="button" data-cat="' + esc(v) + '" aria-pressed="' + (cat === v) + '">' + esc(l) + '<small>' + c + '</small></button>').join('');
    }
    if ($('sgGrid')) {
        $('sgTabs').addEventListener('click', e => { const b = e.target.closest('[data-cat]'); if (b) { cat = b.dataset.cat; shown = PAGE; renderTabs(); renderGrid(); } });
        $('sgSort').addEventListener('change', e => { sort = e.target.value; shown = PAGE; renderGrid(); });
        $('sgMore').addEventListener('click', () => { const from = shown; shown += PAGE; renderGrid(from); });
    }
    const card = (p, i) => {
        const pct = off(p), badges = (pct >= 5 ? '<span class="sg-badge is-sale">−' + pct + ' %</span>' : '') + (p.new ? '<span class="sg-badge is-new">Nouveau</span>' : '') + (D.settings.stock && p.stock !== null && p.stock > 0 && p.stock <= 5 ? '<span class="sg-badge">Plus que ' + p.stock + '</span>' : '');
        return '<article class="sg-card" style="--i:' + Math.min(i, 8) + '"><div class="sg-card-media">' +
            '<button type="button" class="sg-card-img" data-sm-quick="' + p.id + '" aria-label="Aperçu rapide : ' + esc(p.name) + '">' + (p.photo ? '<img src="' + esc(p.photo) + '" alt="" loading="lazy">' : '<span class="sg-noimg">' + esc(p.name) + '</span>') + '<span class="sg-card-quick" aria-hidden="true">Aperçu rapide</span></button>' +
            (badges ? '<div class="sg-badges">' + badges + '</div>' : '') +
            '<button type="button" class="sg-card-fav" data-sm-fav="' + p.id + '" aria-pressed="' + S.fav(p.id) + '" aria-label="Ajouter aux favoris : ' + esc(p.name) + '">' + heart + '</button></div>' +
            '<div class="sg-card-body">' + (p.cat ? '<small>' + esc(p.cat) + '</small>' : '') + '<h3>' + esc(p.name) + '</h3><p class="sg-price" style="margin:0">' + esc(S.money(p.price)) + (p.old ? '<s>' + esc(S.money(p.old)) + '</s>' : '') + '</p>' +
            '<button type="button" class="sg-card-add" data-sm-add="' + p.id + '"' + (S.canBuy(p) ? '' : ' disabled') + '>' + esc(S.buyLabel(p)) + '</button></div></article>';
    };
    function list() {
        let l = S.products.filter(p => !cat || p.cat === cat);
        if (sort === 'asc') l = l.slice().sort((a, b) => a.price - b.price);
        if (sort === 'desc') l = l.slice().sort((a, b) => b.price - a.price);
        if (sort === 'new') l = l.slice().sort((a, b) => (b.new ? 1 : 0) - (a.new ? 1 : 0));
        return l;
    }
    function renderGrid(from) {
        if (!$('sgGrid')) return;
        const l = list(), page = l.slice(0, shown);
        $('sgCount').textContent = l.length + ' produit' + (l.length > 1 ? 's' : '') + (cat ? ' · ' + cat : '');
        if (!l.length) $('sgGrid').innerHTML = '<div class="sg-empty">Aucun produit dans cette catégorie pour l’instant.</div>';
        else if (from) $('sgGrid').insertAdjacentHTML('beforeend', page.slice(from).map((p, i) => card(p, i)).join(''));
        else $('sgGrid').innerHTML = page.map(card).join('');
        $('sgMore').hidden = l.length <= shown;
    }
    renderTabs(); renderGrid();

    /* ── Pièce signature ── */
    const spot = S.products.find(p => p.featured && p.photo && (!hero || p.id !== hero.id)) || hero;
    if (spot && $('sgSpot')) {
        const pct = off(spot);
        $('sgSpot').innerHTML = '<div class="sg-spot"><div class="sg-spot-media" data-rv>' + (spot.photo ? '<img src="' + esc(spot.photo) + '" alt="' + esc(spot.name) + '" loading="lazy">' : '<span class="sg-mono" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font:italic 500 90px var(--m-title);color:var(--forest)">' + esc(D.shop.initials) + '</span>') + '<span class="sg-spot-price">' + esc(S.money(spot.price)) + '</span></div>' +
            '<div data-rv style="--d:120ms"><p class="sg-kicker">' + esc(spot.cat || 'La pièce signature') + '</p><h2 class="sg-h2" id="sgSpotH">' + esc(spot.name) + '</h2>' +
            '<p class="sg-price" style="margin:14px 0 0;font-size:22px">' + esc(S.money(spot.price)) + (spot.old ? '<s>' + esc(S.money(spot.old)) + '</s>' : '') + (pct >= 5 ? '<span class="sg-badge is-sale">−' + pct + ' %</span>' : '') + '</p>' +
            (spot.desc ? '<p class="sg-spot-desc">' + esc(spot.desc) + '</p>' : '') +
            '<ul class="sg-ticks"><li>Paiement à la livraison, en espèces</li><li>Appel de confirmation avant l’envoi</li><li>Suivi de la commande sur Shopio</li></ul>' +
            '<div class="sg-ctas"><button type="button" class="sg-btn" data-sm-add="' + spot.id + '"' + (S.canBuy(spot) ? '' : ' disabled') + '>' + esc(S.buyLabel(spot)) + '</button><button type="button" class="sg-btn is-ghost" data-sm-quick="' + spot.id + '">Voir le détail</button></div></div></div>';
    } else if ($('signature')) { $('signature').hidden = true; }

    /* ── Chiffres qui montent une fois visibles ── */
    document.querySelectorAll('[data-sg-count]').forEach(el => {
        const to = +el.dataset.sgCount; if (S.reduce || !io || to < 10) return;
        const fmt = n => Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        const o = new IntersectionObserver(([e]) => { if (!e.isIntersecting) return; o.disconnect(); const t0 = performance.now(); const step = t => { const k = Math.min(1, (t - t0) / 1400); el.textContent = fmt(to * (1 - Math.pow(1 - k, 3))); if (k < 1) requestAnimationFrame(step); }; requestAnimationFrame(step); }, { threshold: .4 });
        el.textContent = '0'; o.observe(el);
    });

    /* ── Favoris, vus récemment, barre panier fixe ── */
    const strip = l => '<div class="sg-strip">' + l.map(p => '<button type="button" data-sm-quick="' + p.id + '"><i' + (p.thumb ? ' style="background-image:url(\'' + esc(p.thumb) + '\')"' : '') + '></i>' + esc(p.name) + '<b>' + esc(S.money(p.price)) + '</b></button>').join('') + '</div>';
    const dock = $('sgDock');
    function renderMine() {
        const f = S.favs().map(S.byId).filter(Boolean), r = S.recent();
        $('sgFavs').innerHTML = f.length ? strip(f) : '<p class="sg-mine-empty">Touchez le cœur d’un produit pour le retrouver ici.</p>';
        $('sgRecent').innerHTML = r.length ? strip(r) : '<p class="sg-mine-empty">Les produits que vous regardez apparaîtront ici.</p>';
        const has = S.cartCount() > 0;
        document.body.classList.toggle('has-dock', has);
        dock.setAttribute('aria-hidden', has ? 'false' : 'true');
        dock.querySelector('button').tabIndex = has ? 0 : -1;
    }
    S.subscribe(renderMine); renderMine();

    watch();
})();
</script>
</body>
</html>
