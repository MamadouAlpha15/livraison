{{--
    Thème « espace vendeur » Shopio (bleu) — maquette Main.dc.html.
    À inclure APRÈS les styles propres à la page : @push('styles') @include('boutique._partials.sv-theme') @endpush
    Il ne touche qu'à l'apparence (couleurs, rayons, typo, états) des classes communes aux pages vendeur
    (sidebar, sb-*, topbar, tb-*, btn, card, kpi, tableaux, champs) : aucun identifiant ni script n'est modifié.
--}}
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<style id="sv-theme">
:root {
    --brand: #1668D8; --brand-dk: #1260C9; --brand-lt: #D6E6FF; --brand-mlt: #EAF2FF;
    --sv-blue: #1F7CF6; --sv-btn: #1668D8; --sv-btn-h: #1260C9; --sv-b50: #EAF2FF; --sv-b100: #D6E6FF;
    --sv-night: #0B1E3D; --sv-night2: #132B52; --sv-sky: #7DB4FF;
    --sv-amber: #F59E0B; --sv-amb50: #FEF3E0; --sv-amb-ink: #92400E;
    --sv-ok: #15803D; --sv-ok50: #E7F6EC; --sv-warn: #B45309; --sv-err: #B91C1C; --sv-err50: #FDECEC;
    --sv-e-out: cubic-bezier(.22,1,.36,1); --sv-e-io: cubic-bezier(.65,0,.35,1); --sv-e-pop: cubic-bezier(.34,1.56,.64,1);
    --bg: #F6F9FF; --surface: #FFFFFF; --border: #E3E8F2; --text: #16162A; --text-2: #45455E; --muted: #62627C;
    --font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --mono: 'Plus Jakarta Sans', system-ui, sans-serif;
}
[data-theme="dark"] {
    --brand-lt: #1B3563; --brand-mlt: #13284B;
    --sv-b50: #13284B; --sv-b100: #1B3563; --sv-amb50: #2E2208; --sv-amb-ink: #FBBF24;
    --sv-ok: #4ADE80; --sv-ok50: #0F2A1C; --sv-warn: #FBBF24; --sv-err: #F87171; --sv-err50: #2A1214;
    --bg: #0A1426; --surface: #10203B; --border: #22355A; --border-dk: #2B4370;
    --text: #EAF0FA; --text-2: #C3CEE0; --muted: #9DAAC2;
}
body { font-family: var(--font); font-variant-numeric: tabular-nums; }

/* ── Barre latérale bleu nuit, élément actif en pilule bleue ── */
.sidebar { background: var(--sv-night) !important; border-right: 0 !important; box-shadow: none !important; }
.sb-brand { border-bottom-color: #1C3560 !important; }
.sb-section { color: #8EA3C4 !important; font-size: 11px !important; letter-spacing: .09em !important; padding: 16px 12px 6px !important; }
.sb-nav .sb-item, .sidebar .sb-item { min-height: 44px; padding: 0 12px; border-radius: 14px; color: #C9D6EA; font-size: 15px; gap: 12px; transition: background-color 180ms var(--sv-e-out), color 180ms, transform 180ms var(--sv-e-out); }
.sidebar .sb-item:hover { background: var(--sv-night2) !important; color: #fff !important; transform: translateX(2px); }
.sidebar .sb-item.active { background: #1668D8 !important; color: #fff !important; box-shadow: 0 8px 18px rgba(22,104,216,.35) !important; }
.sidebar .sb-item.active::before { display: none !important; }
.sidebar .sb-item .ico { background: transparent !important; border: 0 !important; width: 22px; }
.sidebar .sb-badge { border-radius: 999px; font-family: var(--font); font-weight: 800; font-size: 12px; min-width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; padding: 0 7px; }
.sidebar .sb-group-toggle { min-height: 44px; border-radius: 14px; font-size: 15px !important; color: #C9D6EA !important; }
.sidebar .sb-user { background: var(--sv-night2); border-radius: 16px; padding: 12px; }
.sidebar .sb-av { background: var(--sv-blue) !important; box-shadow: none !important; }
.sidebar .sb-logout { border-radius: 999px; min-height: 40px; }
/* Indicateur « faites défiler » : placé juste sous l'en-tête avec une marge négative, il
   recouvrait la ligne « Boutique active · Admin ». La barre de défilement suffit. */
.sidebar .sb-scroll-hint { display: none !important; }

/* ── Barre du haut (fond plein : un backdrop-filter casserait les éléments position:fixed qu'elle contient) ── */
.main .topbar { min-height: 72px; height: auto; padding-top: 12px; padding-bottom: 12px; background: var(--bg) !important; box-shadow: none !important; border-bottom: 1px solid var(--border) !important; }
.main .topbar .tb-title { font-size: 20px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.main .topbar .tb-sub { font-size: 13px; color: var(--muted); }
.main .topbar .btn, .main .topbar .tb-icon-btn { min-height: 44px; min-width: 44px; justify-content: center; border-radius: 999px !important; padding: 0 16px; font-size: 14px; font-weight: 700; border: 0 !important; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); }
.main .topbar .btn:hover, .main .topbar .tb-icon-btn:hover { box-shadow: inset 0 0 0 1.5px var(--sv-blue); color: var(--sv-btn); background: var(--surface); }
.main .topbar .btn-primary { background: var(--sv-btn) !important; color: #fff !important; box-shadow: none; }
.main .topbar .btn-primary:hover { background: var(--sv-btn-h) !important; color: #fff !important; box-shadow: 0 8px 20px rgba(22,104,216,.28); }

/* ── Boutons : pilules ── */
.main .btn { border-radius: 999px; font-weight: 700; transition: transform 180ms var(--sv-e-out), background-color 180ms, box-shadow 180ms, color 180ms, border-color 180ms; }
.main .btn:active { transform: scale(.97); }
.main .btn-primary { background: var(--sv-btn); border-color: var(--sv-btn); color: #fff; }
.main .btn-primary:hover { background: var(--sv-btn-h); border-color: var(--sv-btn-h); color: #fff; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(22,104,216,.28); }
.main a:focus-visible, .main button:focus-visible, .main input:focus-visible, .main select:focus-visible, .main textarea:focus-visible, .sidebar a:focus-visible { outline: 3px solid var(--sv-blue); outline-offset: 2px; }

/* ── Cartes, indicateurs, tableaux ── */
.main .card { border-radius: 24px; border: 0; box-shadow: 0 0 0 1px var(--border); }
.main .kpi { border-radius: 24px; border: 0 !important; box-shadow: 0 0 0 1px var(--border) !important; transition: transform 260ms var(--sv-e-out), box-shadow 260ms var(--sv-e-out); }
.main .kpi::before { display: none !important; }
.main .kpi:hover { transform: perspective(800px) rotateX(7deg) rotateY(-7deg) translateY(-4px); box-shadow: 0 0 0 1px var(--border), 10px 18px 32px rgba(11,30,61,.10) !important; }
.main .kpi-val { font-family: var(--font) !important; font-weight: 800; letter-spacing: -.02em; }
.main .kpi-lbl { font-weight: 800; letter-spacing: .07em; color: var(--muted); }
.main table th { font-size: 11px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: var(--muted); }
.main table tbody tr { transition: background-color 160ms var(--sv-e-out); }
.main table tbody tr:hover { background: var(--sv-b50); }

/* ── Champs ── */
.main input[type="text"], .main input[type="search"], .main input[type="email"], .main input[type="tel"], .main input[type="number"],
.main input[type="date"], .main select, .main textarea { border-radius: 14px; font-family: var(--font); }
.main input:focus, .main select:focus, .main textarea:focus { border-color: var(--sv-blue) !important; box-shadow: 0 0 0 3px var(--sv-b50) !important; outline: none; }

@media (prefers-reduced-motion: reduce) {
    .main .kpi:hover, .sidebar .sb-item:hover, .main .btn-primary:hover { transform: none !important; }
}
</style>
