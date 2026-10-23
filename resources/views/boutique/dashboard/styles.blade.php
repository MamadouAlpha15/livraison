<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    /* ── Accent indigo premium ── */
    --brand:      #1668D8;
    --brand-dk:   #1260C9;
    --brand-lt:   #D6E6FF;
    --brand-mlt:  #EAF2FF;

    /* ── Sidebar noir profond ── */
    --sb-bg:      #0B1E3D;
    --sb-border:  rgba(255,255,255,.08);
    --sb-act:     rgba(22,104,216,.52);
    --sb-hov:     rgba(255,255,255,.07);
    --sb-txt:     rgba(255,255,255,.62);
    --sb-txt-act: #fff;

    /* ── Surfaces ── */
    --bg:         #f8fafc;
    --surface:    #ffffff;
    --border:     #e2e8f0;
    --border-dk:  #cbd5e1;

    /* ── Texte ── */
    --text:       #0f172a;
    --text-2:     #475569;
    --muted:      #64748b;

    /* ── Typo & divers ── */
    --font:       'Plus Jakarta Sans', sans-serif;
    --mono:       'JetBrains Mono', monospace;
    --r:          12px;
    --r-sm:       8px;
    --r-xs:       5px;
    --shadow-sm:  0 1px 3px rgba(0,0,0,.05), 0 1px 2px rgba(0,0,0,.03);
    --shadow:     0 4px 20px rgba(0,0,0,.06), 0 1px 4px rgba(0,0,0,.03);
    --shadow-lg:  0 10px 40px rgba(0,0,0,.08);
    --sb-w:       232px;
    --top-h:      58px;
}

/* ══ MODE SOMBRE — activé via data-theme="dark" sur <html> (bouton en haut à droite) ══ */
[data-theme="dark"] {
    --brand-lt:   rgba(22,104,216,.16);
    --brand-mlt:  rgba(22,104,216,.09);

    --bg:         #0b0f1a;
    --surface:    #141925;
    --border:     #262d3d;
    --border-dk:  #343d52;

    --text:       #e6e9f0;
    --text-2:     #b0b8c9;
    --muted:      #7b869c;

    --shadow-sm:  0 1px 3px rgba(0,0,0,.35), 0 1px 2px rgba(0,0,0,.25);
    --shadow:     0 4px 20px rgba(0,0,0,.4), 0 1px 4px rgba(0,0,0,.25);
    --shadow-lg:  0 10px 40px rgba(0,0,0,.5);
}
/* Surfaces "neutres" codées en dur (pas via variable) qui doivent aussi passer en sombre */
[data-theme="dark"] .tab-bar,
[data-theme="dark"] .bq-chat-panel,
[data-theme="dark"] .bq-msg-row.theirs .bq-msg-bubble,
[data-theme="dark"] .bq-chat-input-zone,
[data-theme="dark"] .bq-order-select,
[data-theme="dark"] .bq-order-card,
[data-theme="dark"] .bq-zone-result,
[data-theme="dark"] .bq-zone-chip-change,
[data-theme="dark"] .bq-notif-panel,
[data-theme="dark"] .bq-order-card-thumb {
    background: var(--surface) !important;
    border-color: var(--border) !important;
    color: var(--text);
}
[data-theme="dark"] .bq-chat-msgs,
[data-theme="dark"] .lv-row:hover,
[data-theme="dark"] .bq-zone-empty,
[data-theme="dark"] .rc-delta.flat,
[data-theme="dark"] .p-muted {
    background: #10131d !important;
}
[data-theme="dark"] input,
[data-theme="dark"] select,
[data-theme="dark"] textarea {
    background: var(--surface);
    color: var(--text);
    border-color: var(--border-dk);
}
[data-theme="dark"] input::placeholder,
[data-theme="dark"] textarea::placeholder { color: var(--muted); }
[data-theme="dark"] .sb-badge.warn { color: #0b0f1a; }
/* Ombres de survol (cartes/KPI) recalées pour le fond sombre */
[data-theme="dark"] .kpi:hover { box-shadow: 0 8px 28px rgba(0,0,0,.45), 0 2px 6px rgba(0,0,0,.3); }
[data-theme="dark"] .card:hover { box-shadow: var(--shadow-lg); }
/* Barre "Visites boutique" — couleurs codées en dur (bleu clair) */
[data-theme="dark"] .visits-banner {
    background: rgba(2,132,199,.1) !important;
    border-color: rgba(2,132,199,.3) !important;
}
[data-theme="dark"] .visits-pill { background: rgba(2,132,199,.18) !important; color: #7dd3fc !important; }
[data-theme="dark"] .visits-label { color: #7dd3fc !important; }
/* Barres de progression "Top produits" */
[data-theme="dark"] .sp-track { background: var(--border) !important; }

html { font-family: var(--font); }
body { background: var(--bg); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

.dash-wrap { display: flex; min-height: 100vh; }
.dash-wrap .sidebar { flex-shrink: 0; }
.dash-wrap .main { margin-left: var(--sb-w); flex: 1; min-width: 0; }

/* SIDEBAR */
.sidebar { background: linear-gradient(180deg, #0B1E3D 0%, #0B1E3D 40%, #0B1E3D 100%); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; width: var(--sb-w); overflow-y: scroll; scrollbar-width: thin; scrollbar-color: rgba(22,104,216,.35) transparent; z-index: 40; border-right: 1px solid rgba(22,104,216,.15); box-shadow: 6px 0 30px rgba(0,0,0,.35); -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-track { background: rgba(255,255,255,.04); }
.sidebar::-webkit-scrollbar-thumb { background: rgba(22,104,216,.4); border-radius: 4px; }
.sidebar::-webkit-scrollbar-thumb:hover { background: rgba(22,104,216,.7); }
.sb-brand { padding: 18px 16px 14px; border-bottom: 1px solid var(--sb-border); flex-shrink: 0; position: relative; }
.sb-close { display: none; position: absolute; top: 14px; right: 12px; width: 30px; height: 30px; border-radius: 8px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.6); cursor: pointer; align-items: center; justify-content: center; transition: background .15s, color .15s; flex-shrink: 0; }
.sb-ft-ico { width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7; }
.sb-close:hover { background: rgba(239,68,68,.18); border-color: rgba(239,68,68,.3); color: #fca5a5; }
@media (max-width: 900px) { .sb-close { display: flex; } }
.sb-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.sb-shop-name { font-size: 14.5px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 148px; letter-spacing: -.3px; color: #fff; }
.sb-status { display: flex; align-items: center; gap: 6px; margin-top: 9px; font-size: 11px; color: rgba(255,255,255,.58); font-weight: 500; }
.pulse { width: 6px; height: 6px; border-radius: 50%; background: #6ee7b7; flex-shrink: 0; animation: blink 2.2s ease-in-out infinite; box-shadow: 0 0 5px #6ee7b7; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.35} }
.sb-nav { padding: 10px 10px 32px; flex: 1; display: flex; flex-direction: column; gap: 1px; overflow: visible; }
.sb-section { font-size: 9.5px; text-transform: uppercase; letter-spacing: 1.8px; color: rgba(255,255,255,.48); padding: 16px 10px 5px; font-weight: 800; }
.sb-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); text-decoration: none; transition: background .15s, color .15s; position: relative; letter-spacing: -.1px; }
.sb-item:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-item.active { background: var(--sb-act); color: #fff; box-shadow: 0 2px 12px rgba(22,104,216,.25); }
.sb-item.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: #7DB4FF; border-radius: 0 3px 3px 0; box-shadow: 2px 0 8px rgba(165,180,252,.5); }
.sb-item .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-radius: 7px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); transition: background .15s; }
.sb-item:hover .ico { background: rgba(255,255,255,.11); border-color: rgba(255,255,255,.14); }
.sb-item.active .ico { background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.25); }
.sb-badge { margin-left: auto; background: var(--brand); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 7px; font-family: var(--mono); min-width: 20px; text-align: center; }
.sb-badge.warn { background: #f59e0b; }
.sb-group { display: flex; flex-direction: column; }
.sb-group-toggle { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); font-size: 13.5px; font-weight: 600; color: rgba(255,255,255,.78); cursor: pointer; transition: background .15s, color .15s; user-select: none; border: none; background: none; width: 100%; text-align: left; font-family: var(--font); letter-spacing: -.1px; }
.sb-group-toggle:hover { background: var(--sb-hov); color: rgba(255,255,255,.96); }
.sb-group-toggle.open { color: #fff; background: rgba(255,255,255,.05); }
.sb-group-toggle .ico { font-size: 13px; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-radius: 7px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); }
.sb-group-toggle .sb-arrow { margin-left: auto; font-size: 10px; color: rgba(255,255,255,.32); transition: transform .2s; flex-shrink: 0; }
.sb-group-toggle.open .sb-arrow { transform: rotate(90deg); color: rgba(255,255,255,.6); }
.sb-sub { display: none; flex-direction: column; gap: 1px; margin-left: 12px; padding-left: 14px; border-left: 1px solid rgba(255,255,255,.1); margin-top: 2px; margin-bottom: 4px; overflow: visible; }
.sb-sub.open { display: flex; }
.sb-sub .sb-item { font-size: 13px; font-weight: 500; padding: 6px 10px; color: rgba(255,255,255,.62); letter-spacing: 0; }
.sb-sub .sb-item:hover { color: rgba(255,255,255,.92); }
.sb-sub .sb-item.active { color: #fff; background: var(--sb-act); font-weight: 600; }
.sb-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.08); flex-shrink: 0; display: flex; flex-direction: column; gap: 6px; position: sticky; bottom: 0; background: linear-gradient(180deg, transparent 0%, #0b0b12 25%); z-index: 1; }
.sb-scroll-hint { position: sticky; top: auto; bottom: 72px; width: 100%; height: 40px; background: linear-gradient(to bottom, transparent, rgba(17,17,24,.95)); pointer-events: none; z-index: 2; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 6px; transition: opacity .3s; margin-top: -40px; align-self: flex-end; }
.sb-scroll-hint.hidden { opacity: 0; pointer-events: none; }
.sb-scroll-hint-arrow { display: flex; flex-direction: column; align-items: center; gap: 2px; animation: bounceDown 1.5s ease-in-out infinite; }
.sb-scroll-hint-dot { width: 4px; height: 4px; border-radius: 50%; background: rgba(22,104,216,.6); }
.sb-scroll-hint-dot:nth-child(2) { opacity: .5; margin-top: -2px; }
.sb-scroll-hint-dot:nth-child(3) { opacity: .25; margin-top: -2px; }
@keyframes bounceDown { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(4px); } }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: var(--r-sm); text-decoration: none; transition: background .15s, border-color .15s; border: 1px solid transparent; }
.sb-user:hover { background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.08); }
.sb-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #1668D8, #1260C9); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(22,104,216,.45), 0 2px 8px rgba(22,104,216,.3); letter-spacing: -.5px; }
.sb-uname { font-size: 13px; font-weight: 700; color: #fff; letter-spacing: -.2px; }
.sb-urole { font-size: 10.5px; color: rgba(255,255,255,.52); margin-top: 1px; font-weight: 500; }
.sb-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px; border-radius: var(--r-sm); background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.15); color: rgba(252,165,165,.9); font-size: 12.5px; font-weight: 600; font-family: var(--font); cursor: pointer; text-decoration: none; transition: background .15s, color .15s, border-color .15s; text-align: left; letter-spacing: -.1px; }
.sb-logout:hover { background: rgba(220,38,38,.18); border-color: rgba(220,38,38,.35); color: #fca5a5; }
.sb-logout .ico { font-size: 13px; flex-shrink: 0; }

/* MAIN */
.main { display: flex; flex-direction: column; min-width: 0; }

/* ══════════════════════════════════════════════════
   TOPBAR — responsive complet
   ══════════════════════════════════════════════════ */
.topbar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 0 16px;
    height: var(--top-h);
    display: flex; align-items: center; gap: 8px;
    position: sticky; top: 0; z-index: 30;
    box-shadow: var(--shadow-sm);
}
.tb-info { flex: 1; min-width: 0; overflow: hidden; }
.tb-title { font-size: 14px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tb-sub   { font-size: 11px; color: var(--muted); margin-top: 1px; }
.tb-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

/* Boutons */
.btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 11px; border-radius: var(--r-sm); font-size: 12px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--surface); color: var(--text-2); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; }
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); }
.btn-primary:hover { background: var(--brand-dk); border-color: var(--brand-dk); color: #fff; }
.btn-sm { padding: 5px 10px; font-size: 11px; }
.btn-ghost { background: transparent; border-color: transparent; color: var(--brand-dk); padding: 5px 8px; }
.btn-ghost:hover { background: var(--brand-mlt); border-color: var(--brand-lt); }
.btn-hamburger { display: none; background: none; border: none; cursor: pointer; padding: 6px; color: var(--text); display:flex;align-items:center;justify-content:center; }

/* Export : visible sur grand écran, caché sur petit */
.topbar-export-group { display: flex; gap: 6px; }

/* Dropdown export : caché sur grand écran, visible sur petit */
.topbar-export-dropdown { display: none; position: relative; }
.export-dropdown-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px; border-radius: var(--r-sm); font-size: 12px; font-weight: 700; font-family: var(--font); background: var(--surface); color: var(--text-2); border: 1px solid var(--border-dk); cursor: pointer; white-space: nowrap; }
.export-dropdown-btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.export-menu { position: absolute; top: calc(100% + 6px); right: 0; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-sm); box-shadow: var(--shadow); min-width: 130px; z-index: 200; display: none; flex-direction: column; overflow: hidden; }
.export-menu.open { display: flex; }
.export-menu a { display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 12.5px; font-weight: 600; color: var(--text-2); text-decoration: none; transition: background .12s; }
.export-menu a:hover { background: var(--bg); }

/* Responsive topbar */
@media (max-width: 680px) {
    .topbar-export-group { display: none; }
    .topbar-export-dropdown { display: block; }
}
@media (max-width: 540px) {
    .tb-sub { display: none; }
    .tb-title { font-size: 13px; }
    .msg-btn-label { display: none; }
}
@media (max-width: 420px) {
    .btn-commande-label { display: none; }
}

/* PAGE */
.content { padding: 22px 24px; flex: 1; }
.flash { margin: 12px 24px 0; padding: 10px 14px; font-size: 12.5px; font-weight: 500; border-radius: var(--r-sm); border: 1px solid; display: flex; align-items: center; gap: 8px; }
.flash-success { background: #EAF2FF; border-color: #7DB4FF; color: #0B1E3D; }
.flash-info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.flash-warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.flash-danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.comm-banner { background: var(--brand-mlt); border: 1px solid var(--brand-lt); border-radius: var(--r-sm); padding: 10px 14px; display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #0B1E3D; margin-bottom: 20px; font-weight: 500; }

/* KPI GRID */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 14px; }
.kpi { background: var(--surface); border: 1px solid var(--border); border-top: 3px solid var(--kpi-color, var(--brand)); border-radius: var(--r); padding: 18px 20px 16px; position: relative; overflow: hidden; box-shadow: var(--shadow-sm); transition: box-shadow .22s, transform .22s, border-color .15s; }
.kpi:hover { box-shadow: 0 8px 28px rgba(0,0,0,.09), 0 2px 6px rgba(0,0,0,.04); transform: translateY(-2px); }
.kpi-icon { width: 44px; height: 44px; background: var(--kpi-bg, var(--brand-mlt)); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; border: 1.5px solid var(--kpi-border, rgba(0,0,0,.08)); box-shadow: 0 0 0 3px var(--kpi-ring, transparent), 0 2px 10px var(--kpi-glow, rgba(0,0,0,.06)); }
.kpi-lbl { font-size: 10.5px; color: var(--muted); font-weight: 700; letter-spacing: .4px; margin-bottom: 5px; text-transform: uppercase; }
.kpi-val { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.8px; font-family: var(--mono); line-height: 1; }
.kpi-unit { font-size: 10px; color: var(--muted); margin-top: 4px; }
.kpi-delta { font-size: 11px; font-weight: 700; margin-top: 7px; display: flex; align-items: center; gap: 3px; }
.up   { color: #15803d; } /* contraste corrigé (#16a34a échouait à 3.3:1 sur fond blanc) */
.down { color: #dc2626; }

/* CARDS */
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm);   transition: all 0.3s ease; }
.card-hd { padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-bd { padding: 16px 18px; }
.card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

/* CHART */
.chart-wrap { margin-bottom: 22px; }
.rc-header { padding: 18px 22px 14px; border-bottom: 1px solid rgba(22,104,216,.1); display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: linear-gradient(135deg,rgba(22,104,216,.045) 0%,rgba(31,124,246,.02) 100%); }
.rc-header-left .rc-title { font-size: 10.5px; font-weight: 700; color: #1668D8; text-transform: uppercase; letter-spacing: .1em; margin-bottom: 6px; }
.rc-total { font-size: 28px; font-weight: 800; color: var(--text); font-family: var(--mono); line-height: 1; letter-spacing: -1px; }
.rc-total sup { font-size: 12px; font-weight: 600; vertical-align: super; color: var(--muted); margin-right: 2px; }
.rc-delta { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-top: 8px; }
.rc-delta.up   { background: #dcfce7; color: #15803d; }
.rc-delta.down { background: #fee2e2; color: #991b1b; }
.rc-delta.flat { background: #f3f4f6; color: #475569; }
.rc-header-right { text-align: right; font-size: 11px; color: var(--muted); background: rgba(22,104,216,.04); border: 1px solid rgba(22,104,216,.1); border-radius: 10px; padding: 10px 14px; }
.rc-header-right .rc-best { font-size: 12px; font-weight: 700; color: var(--brand); margin-top: 2px; }
.rc-svg-wrap { padding: 14px 22px 4px; position: relative; }
.rc-tooltip { position: absolute; background: linear-gradient(135deg,#1e1b4b,#2d2b6e); color: #f8fafc; border-radius: 10px; padding: 10px 14px; font-size: 11.5px; pointer-events: none; opacity: 0; transition: opacity .15s; white-space: nowrap; box-shadow: 0 8px 28px rgba(22,104,216,.4),0 2px 8px rgba(0,0,0,.2); z-index: 10; border: 1px solid rgba(165,180,252,.2); }
.rc-tooltip strong { display: block; font-size: 14px; font-family: var(--mono); color: #7DB4FF; }
.rc-tooltip span   { color: #94a3b8; font-size: 10px; }
.rc-day-dots { display: flex; justify-content: space-around; padding: 2px 0 4px; }
.rc-day-dot { flex: 1; text-align: center; font-size: 9.5px; color: var(--muted); font-family: var(--mono); }
.rc-day-dot.today { color: var(--brand-dk); font-weight: 700; }

/* CONTENT GRID */
.content-grid { display: grid; grid-template-columns: 1fr 380px; gap: 18px; margin-bottom: 22px; }
.right-col { display: flex; flex-direction: column; gap: 16px; }

/* TABLE */
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl th { text-align: left; font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; padding: 0 10px 10px 0; border-bottom: 1px solid var(--border); }
.tbl td { padding: 10px 10px 10px 0; border-bottom: 1px solid #f3f6f4; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: var(--bg); }
.oid  { font-family: var(--mono); font-size: 11px; color: var(--muted); }
.onam { font-weight: 600; color: var(--text); margin-top: 1px; }
.oamt { font-family: var(--mono); font-weight: 600; color: var(--text); white-space: nowrap; text-align: right; }
.pill { display: inline-block; font-size: 10.5px; font-weight: 600; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
.p-success { background: #dcfce7; color: #15803d; }
.p-warning { background: #fef3c7; color: #92400e; }
.p-info    { background: #dbeafe; color: #1e40af; }
.p-danger  { background: #fee2e2; color: #991b1b; }
.p-muted   { background: #f3f6f4; color: #475569; }

/* ═══ CARTE LIVRAISON ═══ */
.delivery-card { border-radius: 16px !important; overflow: hidden; border: none !important; box-shadow: 0 4px 20px rgba(0,0,0,.08) !important; }
.delivery-card-hero { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); padding: 18px 20px 14px; display: flex; align-items: center; justify-content: space-between; }
.delivery-card-hero-left { display: flex; align-items: center; gap: 12px; }
.delivery-card-icon { width: 44px; height: 44px; background: rgba(255,255,255,.12); border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.delivery-card-title { font-size: 15px; font-weight: 800; color: #fff; margin: 0; }
.delivery-card-sub { font-size: 11px; color: rgba(255,255,255,.55); margin-top: 2px; }
.delivery-card-manage { font-size: 11.5px; font-weight: 700; color: rgba(255,255,255,.8); text-decoration: none; background: rgba(255,255,255,.1); padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,.2); transition: all .15s; }
.delivery-card-manage:hover { background: rgba(255,255,255,.2); color: #fff; }

/* Tabs */
.tab-bar { display: flex; border-bottom: 1px solid var(--border); padding: 0 16px; gap: 0; background: #fff; }
.tab-btn { padding: 11px 14px; font-size: 12px; font-weight: 600; color: var(--muted); cursor: pointer; border: none; background: none; font-family: var(--font); border-bottom: 2.5px solid transparent; transition: color .15s, border-color .15s; display: flex; align-items: center; gap: 6px; white-space: nowrap; margin-bottom: -1px; }
.tab-btn:hover { color: var(--text); }
.tab-btn.active { color: var(--brand); border-bottom-color: var(--brand); }
.tab-count { background: var(--brand-lt); color: var(--brand); font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 10px; }
.tab-count.zero { background: #f3f6f4; color: var(--muted); }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* Livreurs */
.lv-list { display: flex; flex-direction: column; padding: 6px 0; }
.lv-row { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-bottom: 1px solid #f3f6f4; transition: background .12s; cursor: pointer; }
.lv-row:last-child { border-bottom: none; }
.lv-row:hover { background: #f8fafc; }
.lv-av { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
.lv-info { flex: 1; min-width: 0; }
.lv-nm { font-size: 13px; font-weight: 700; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.lv-phone { font-size: 11.5px; color: var(--brand); font-weight: 600; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.lv-phone-warn { font-size: 11px; color: #f59e0b; font-weight: 600; margin-top: 2px; }
.lv-status-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; flex-shrink: 0; }
.lv-status-badge.available { background: #dcfce7; color: #15803d; }
.lv-status-badge.busy { background: #fef3c7; color: #92400e; }
.lv-wa-btn { width: 30px; height: 30px; background: #25d366; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; text-decoration: none; transition: transform .15s, box-shadow .15s; }
.lv-wa-btn:hover { transform: scale(1.12); box-shadow: 0 4px 12px rgba(37,211,102,.4); }
.lv-footer { padding: 10px 16px; text-align: center; border-top: 1px solid #f3f6f4; }
.lv-footer a { font-size: 12px; font-weight: 700; color: var(--brand); text-decoration: none; }
.lv-footer a:hover { text-decoration: underline; }

/* Notice aucun livreur */
.no-livreur-notice { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 28px 20px; text-align: center; }
.no-livreur-notice .notice-icon { display:flex;align-items:center;justify-content:center; }
.no-livreur-notice .notice-title { font-size: 14px; font-weight: 700; color: var(--text); }
.no-livreur-notice .notice-sub   { font-size: 12px; color: var(--muted); line-height: 1.6; max-width: 260px; }

.status-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.status-dot.available { background: #22c55e; box-shadow: 0 0 6px rgba(34,197,94,.5); }
.status-dot.busy      { background: #f59e0b; box-shadow: 0 0 6px rgba(245,158,11,.5); }
.co-list { display: flex; flex-direction: column; }
.co-row { display: flex; align-items: center; gap: 11px; padding: 11px 18px; border-bottom: 1px solid #f3f6f4; transition: background .12s; cursor: pointer; }
.co-row:last-child { border-bottom: none; }
.co-row:hover { background: var(--bg); }
.co-logo { width: 38px; height: 38px; border-radius: var(--r-sm); background: var(--bg); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; overflow: hidden; }
.co-logo img { width: 28px; height: 28px; object-fit: contain; }
.co-info { flex: 1; min-width: 0; }
.co-nm { font-size: 12.5px; font-weight: 600; color: var(--text); }
.co-mt { font-size: 11px; color: var(--muted); margin-top: 1px; }
.co-commission { font-size: 10px; font-weight: 700; background: var(--brand-mlt); color: var(--brand-dk); padding: 2px 7px; border-radius: 12px; white-space: nowrap; }

/* TOP PRODUITS */
.sp-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.sp-row:last-child { margin-bottom: 0; }
.sp-lbl { font-size: 12px; font-weight: 500; color: var(--text-2); width: 110px; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sp-track { flex: 1; height: 7px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
.sp-fill { height: 100%; border-radius: 4px; background: var(--brand); transform-origin: left; transition: transform 1.1s cubic-bezier(.23,1,.32,1); }
.sp-val { font-family: var(--mono); font-size: 11.5px; font-weight: 600; color: var(--text); width: 30px; text-align: right; flex-shrink: 0; }

/* AVATAR COLORS */
.av-green  { background: #1668D8; }
.av-blue   { background: #2563eb; }
.av-amber  { background: #d97706; }
.av-purple { background: #1260C9; }
.av-teal   { background: #0891b2; }
.av-rose   { background: #e11d48; }

/* SIDEBAR OVERLAY */
.sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 39; }

/* RESPONSIVE GLOBAL */
@media (max-width: 1100px) { .content-grid { grid-template-columns: 1fr 340px; } }
@media (max-width: 900px) {
    :root { --sb-w: 230px; }
    .dash-wrap .main { margin-left: 0; }
    .sidebar { transform: translateX(-100%); transition: transform .25s cubic-bezier(.23,1,.32,1); }
    .sidebar.open { transform: translateX(0); }
    .sb-overlay.open { display: block; }
    .btn-hamburger { display: flex; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .content-grid { grid-template-columns: 1fr; }
    .right-col { gap: 14px; }
}
@media (max-width: 520px) {
    .content { padding: 14px; }
    .topbar  { padding: 0 10px; }
    .kpi-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .kpi-val  { font-size: 20px; }
    .flash { margin: 10px 14px 0; }
    .comm-banner { margin-bottom: 14px; font-size: 11.5px; }
    .kanban-grid { grid-template-columns: repeat(2,1fr); }
    .kanban-count { font-size: 22px; }
    .quick-grid  { grid-template-columns: repeat(2,1fr); }
    .quick-btn   { border-right: none; border-bottom: 1px solid var(--border); }
    .quick-btn:last-child { border-bottom: none; }
    .quick-btn-ico { width: 36px; height: 36px; font-size: 18px; }
    .quick-btn-lbl { font-size: 11.5px; }
    .today-grid  { grid-template-columns: 1fr; }
    .today-card  { padding: 16px; gap: 12px; }
    .today-val   { font-size: 22px; }
    .period-stats { grid-template-columns: repeat(2,1fr); }
    .period-stat:nth-child(2) { border-right: none; }
    .period-stat:nth-child(3) { border-top: 1px solid var(--border); }
    .tb-title { font-size: 13px; }
    .tb-sub   { display: none; }
}
@media (max-width: 380px) {
    .today-grid { grid-template-columns: 1fr; }
    .kanban-grid { grid-template-columns: repeat(2,1fr); }
}

/* ÉTAPE 1 CSS */
.today-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
.today-card { background: linear-gradient(90deg, #291d95, #291d95, #aa28d9 ); border: 1px solid rgba(22,104,216,.35); border-radius: var(--r); padding: 20px 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 8px 32px rgba(79,70,229,.35), 0 2px 8px rgba(0,0,0,.2); position: relative; overflow: hidden; }
.today-card::before { content: ''; position: absolute; right: -30px; top: -30px; width: 140px; height: 140px; background: rgba(255,255,255,.06); border-radius: 50%; pointer-events: none; }
.today-card::after  { content: ''; position: absolute; left: -20px; bottom: -30px; width: 110px; height: 110px; background: rgba(255,255,255,.04); border-radius: 50%; pointer-events: none; }
.today-icon { width: 48px; height: 48px; background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; backdrop-filter: blur(4px); }
.today-lbl  { font-size: 10px; font-weight: 700; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
.today-val  { font-size: 26px; font-weight: 800; color: #fff; font-family: var(--mono); letter-spacing: -1px; line-height: 1; text-shadow: 0 2px 8px rgba(0,0,0,.2); }
.today-unit { font-size: 10px; color: rgba(255,255,255,.45); margin-top: 3px; }
.today-delta { font-size: 11px; font-weight: 700; margin-top: 6px; display: inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 20px; }
.today-delta.up   { background: rgba(255,255,255,.15); color: #D6E6FF; border: 1px solid rgba(255,255,255,.2); }
.today-delta.down { background: rgba(239,68,68,.25);  color: #fca5a5; border: 1px solid rgba(239,68,68,.3); }
.today-delta.flat { background: rgba(255,255,255,.08); color: rgba(255,255,255,.5); }
.alerts-zone { margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; }
.alert-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: var(--r-sm); border: 1px solid; font-size: 12.5px; font-weight: 500; animation: slideIn .3s ease; }
@keyframes slideIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
.alert-item.danger  { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
.alert-item.warning { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.alert-item.success { background: #EAF2FF; border-color: #7DB4FF; color: #0B1E3D; }
.alert-item.info    { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
.alert-ico  { display:flex;align-items:center;justify-content:center;flex-shrink: 0; }
.alert-msg  { flex: 1; line-height: 1.4; }
.alert-cta  { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; border: 1px solid currentColor; text-decoration: none; color: inherit; white-space: nowrap; flex-shrink: 0; opacity: .75; transition: opacity .15s; }
.alert-cta:hover { opacity: 1; }
.kanban-section { margin-bottom: 22px; }
.kanban-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; }
.kanban-col { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 16px 14px 14px; text-align: center; box-shadow: var(--shadow-sm); transition: all .2s; cursor: default; position: relative; overflow: hidden; }
.kanban-col::after { content: ''; position: absolute; bottom: -10px; right: -10px; width: 55px; height: 55px; border-radius: 50%; background: var(--k-color); opacity: .05; pointer-events: none; }
.kanban-col:hover { box-shadow: var(--shadow); transform: translateY(-3px); border-color: var(--k-color); }
.kanban-ico-wrap { width: 46px; height: 46px; border-radius: 50%; background: var(--k-bg); border: 2.5px solid var(--k-color); box-shadow: 0 0 0 4px var(--k-bg), 0 4px 14px rgba(0,0,0,.10); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; transition: transform .2s; }
.kanban-col:hover .kanban-ico-wrap { transform: scale(1.1); }
.kanban-count { font-size: 30px; font-weight: 800; font-family: var(--mono); color: var(--k-color); line-height: 1; letter-spacing: -1.5px; }
.kanban-lbl   { font-size: 10px; font-weight: 700; color: var(--muted); margin-top: 5px; text-transform: uppercase; letter-spacing: .5px; }
.kanban-col.has-items { background: var(--k-bg); border-color: var(--k-color); }
.quick-section { margin-bottom: 22px; }
.quick-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); box-shadow: var(--shadow-sm); overflow: hidden; }
.quick-card-hd { padding: 13px 18px 12px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.quick-card-title { font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; }
.quick-card-title .title-ico { width: 24px; height: 24px; background: var(--brand-mlt); border-radius: 6px; display: flex; align-items: center; justify-content: center; }
.quick-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
.quick-btn { display: flex; flex-direction: column; align-items: center; gap: 9px; padding: 20px 14px 18px; text-decoration: none; border-right: 1px solid var(--border); transition: background .15s, transform .15s; position: relative; cursor: pointer; }
.quick-btn:last-child { border-right: none; }
.quick-btn:hover { background: var(--q-bg, var(--brand-mlt)); }
.quick-btn::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--q-color, var(--brand)); opacity: 0; transition: opacity .15s; }
.quick-btn:hover::before { opacity: 1; }
.quick-btn-ico { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--q-bg, var(--brand-mlt)); border: 1px solid var(--q-border, var(--brand-lt)); transition: transform .15s; flex-shrink: 0; }
.quick-btn:hover .quick-btn-ico { transform: scale(1.08); }
.quick-btn-lbl { font-size: 12.5px; font-weight: 700; color: var(--text); line-height: 1.2; text-align: center; }
.quick-btn-sub { font-size: 10.5px; color: var(--muted); line-height: 1.3; text-align: center; }

@media (max-width: 860px) {
    .kanban-grid { grid-template-columns: repeat(3,1fr); }
    .quick-grid  { grid-template-columns: repeat(2,1fr); }
    .quick-btn   { border-right: none; border-bottom: 1px solid var(--border); }
    .quick-btn:last-child { border-bottom: none; }
    .today-grid  { grid-template-columns: 1fr; }
    .e2-grid     { grid-template-columns: 1fr; }
}

/* ÉTAPE 2 CSS */
.e2-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px; }
.client-list { display: flex; flex-direction: column; }
.client-row { display: flex; align-items: center; gap: 12px; padding: 11px 18px; border-bottom: 1px solid #f3f6f4; transition: background .12s; }
.client-row:last-child { border-bottom: none; }
.client-row:hover { background: var(--bg); }
.client-av { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.client-info { flex: 1; min-width: 0; }
.client-name { font-size: 12.5px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.client-meta { font-size: 10.5px; color: var(--muted); margin-top: 1px; }
.client-bar-wrap { width: 60px; flex-shrink: 0; }
.client-bar-track { height: 5px; background: #eef1f0; border-radius: 3px; overflow: hidden; margin-bottom: 3px; }
.client-bar-fill { height: 100%; border-radius: 3px; background: var(--brand); transition: width 1s cubic-bezier(.23,1,.32,1); }
.client-amount { font-family: var(--mono); font-size: 10px; font-weight: 600; color: var(--text); text-align: right; white-space: nowrap; }
.client-rank { width: 20px; flex-shrink: 0; font-size: 11px; font-weight: 700; color: var(--muted); text-align: center; }
.client-rank.top { color: #f59e0b; font-size: 14px; }
.risk-list { display: flex; flex-direction: column; }
.risk-row { display: flex; align-items: center; gap: 12px; padding: 10px 18px; border-bottom: 1px solid #f3f6f4; transition: background .12s; }
.risk-row:last-child { border-bottom: none; }
.risk-row:hover { background: var(--bg); }
.risk-img { width: 38px; height: 38px; border-radius: var(--r-sm); object-fit: cover; flex-shrink: 0; border: 1px solid var(--border); }
.risk-img-placeholder { width: 38px; height: 38px; border-radius: var(--r-sm); background: #f3f6f4; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.risk-info { flex: 1; min-width: 0; }
.risk-name { font-size: 12.5px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.risk-meta { font-size: 10.5px; color: var(--muted); margin-top: 1px; }
.risk-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; white-space: nowrap; flex-shrink: 0; }
.risk-empty { padding: 20px 18px; text-align: center; font-size: 13px; color: var(--muted); }
.risk-empty .ico { display: flex; align-items:center; justify-content:center; margin-bottom: 6px; }

/* PÉRIODE */
.period-card { margin-bottom: 22px; }
.period-btns { display: flex; flex-wrap: wrap; gap: 6px; padding: 14px 18px; border-bottom: 1px solid var(--border); }
.period-btn { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; font-family: var(--font); border: 1px solid var(--border-dk); background: var(--bg); color: var(--text-2); cursor: pointer; transition: all .15s; white-space: nowrap; }
.period-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-mlt); }
.period-btn.active { background: var(--brand); color: #fff; border-color: var(--brand-dk); box-shadow: 0 2px 8px rgba(22,104,216,.25); }
.period-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
.period-stat { padding: 16px 18px; border-right: 1px solid var(--border); text-align: center; }
.period-stat:last-child { border-right: none; }
.period-stat-lbl { font-size: 10.5px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .3px; margin-bottom: 5px; }
.period-stat-val { font-size: 20px; font-weight: 700; font-family: var(--mono); color: var(--text); letter-spacing: -.5px; }
.period-stat-sub { font-size: 10px; color: var(--muted); margin-top: 2px; }
.period-chart { padding: 16px 18px 14px; border-top: 1px solid var(--border); }
.period-bars { display: flex; align-items: flex-end; gap: 3px; height: 70px; margin-bottom: 6px; }
.period-bar-wrap { flex: 1; height: 100%; display: flex; align-items: flex-end; position: relative; }

/* ══ TOOLTIP SURVOL BARRE ══ */
.period-bar-tooltip {
    position: absolute; bottom: calc(100% + 8px); left: 50%;
    transform: translateX(-50%);
    background: #0f1c18; color: #fff;
    font-size: 11px; font-weight: 600; font-family: var(--mono);
    padding: 7px 11px; border-radius: var(--r-sm);
    white-space: nowrap; pointer-events: none;
    opacity: 0; transition: opacity .15s; z-index: 50;
    text-align: center; min-width: 110px;
    box-shadow: 0 4px 14px rgba(0,0,0,.25);
}
.period-bar-tooltip::after {
    content: ''; position: absolute; top: 100%; left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent; border-top-color: #0f1c18;
}
.period-bar-wrap:hover .period-bar-tooltip { opacity: 1; }
.tt-date   { font-size: 9px; color: rgba(255,255,255,.45); display: block; margin-bottom: 3px; }
.tt-ca     { color: #7DB4FF; font-size: 12px; display: block; }
.tt-detail { color: rgba(255,255,255,.55); font-size: 9.5px; display: block; margin-top: 2px; }

.period-bar { width: 100%; border-radius: 3px 3px 0 0; background: var(--brand); opacity: .8; transform-origin: bottom; transition: transform .4s cubic-bezier(.23,1,.32,1), opacity .15s; cursor: pointer; position: relative; min-height: 2px; }
.period-bar:hover { opacity: 1; }
.period-bar.empty { opacity: .15; background: #9ca3af; }
.period-bar-labels { display: flex; gap: 3px; overflow: hidden; }
.period-bar-lbl { flex: 1; text-align: center; font-size: 9px; color: var(--muted); font-family: var(--mono); white-space: nowrap; overflow: hidden; text-overflow: clip; }
.period-loading { display: none; align-items: center; justify-content: center; padding: 32px; gap: 10px; font-size: 13px; color: var(--muted); font-weight: 500; }
.period-loading.show { display: flex; }
.spin { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--brand-lt); border-top-color: var(--brand); animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.period-label { font-size: 11px; color: var(--muted); font-weight: 500; padding: 0 18px 10px; }
.period-label strong { color: var(--text); }

@media (max-width: 640px) {
    .period-stats { grid-template-columns: repeat(2,1fr); }
    .period-stat:nth-child(2) { border-right: none; }
    .period-stat:nth-child(3) { border-top: 1px solid var(--border); }
}

/* ═══════════════════════════════
   PREMIUM ENHANCEMENTS
═══════════════════════════════ */

/* Sidebar active item — glow premium (override) */
.sb-item.active {
    background: linear-gradient(90deg, rgba(22,104,216,.58) 0%, rgba(22,104,216,.32) 100%);
    box-shadow: 0 2px 14px rgba(22,104,216,.22), inset 0 1px 0 rgba(255,255,255,.08);
}
.sb-item.active::before { box-shadow: 2px 0 10px rgba(165,180,252,.6), 0 0 8px rgba(22,104,216,.35); }

/* Topbar search */
.tb-search { display:flex; align-items:center; gap:8px; background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:6px 14px; font-size:12px; color:var(--muted); cursor:pointer; white-space:nowrap; transition:border-color .15s, box-shadow .15s; flex-shrink:0; min-width:160px; }
.tb-search:hover { border-color:var(--brand-lt); box-shadow:0 0 0 3px rgba(22,104,216,.08); }
.tb-search kbd { background:#f1f5f9; border:1px solid var(--border-dk); border-radius:4px; padding:1px 5px; font-size:10px; color:var(--muted); font-family:var(--mono); margin-left:auto; }
@media(max-width:680px){ .tb-search{ display:none; } }

/* Topbar icon button */
.tb-icon-btn { width:34px; height:34px; border-radius:8px; background:transparent; border:1px solid var(--border); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; color:var(--text-2); flex-shrink:0; text-decoration:none; }
.tb-icon-btn:hover { background:var(--brand-mlt); border-color:var(--brand-lt); color:var(--brand); }

/* Topbar greeting */
.tb-greeting { font-size:14px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tb-greeting-sub { font-size:11px; color:var(--muted); margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

@media(max-width:640px){
    .tb-greeting { font-size:13px; }
    .tb-greeting-sub { font-size:10px; }
}
@media(max-width:480px){
    .tb-greeting { font-size:12.5px; }
    .tb-greeting-sub { display:none; }
}
@media(max-width:360px){
    .tb-greeting { font-size:12px; }
}

/* Hero card wave decoration */
.today-wave { position:absolute; right:0; top:0; bottom:0; width:55%; pointer-events:none; overflow:hidden; z-index:0; }
.today-wave svg { position:absolute; right:-10px; bottom:0; width:115%; height:100%; }
.today-icon, .today-card > div:not(.today-wave) { position:relative; z-index:1; }

/* KPI card - subtle brand accent dot */
.kpi::before { content:''; position:absolute; top:0; right:14px; width:4px; height:4px; border-radius:50%; background:var(--kpi-color, var(--brand)); opacity:.5; margin-top:9px; }

/* KPI card hover lift */
.kpi { transition:box-shadow .2s, transform .2s; }
.kpi:hover { box-shadow:0 8px 32px rgba(22,104,216,.12), 0 2px 8px rgba(0,0,0,.05); transform:translateY(-2px); }

/* Charts side-by-side */
.charts-duo { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:24px; }
@media(max-width:860px){ .charts-duo{ grid-template-columns:1fr; } }
.charts-duo .card { border:1px solid rgba(22,104,216,.12); box-shadow:0 4px 28px rgba(22,104,216,.08),0 1px 6px rgba(0,0,0,.04); overflow:hidden; }
.card.chart-wrap { border-top:3px solid #1668D8; }
.charts-duo>.card:not(.chart-wrap) { border-top:3px solid #1F7CF6; }

/* Mini bar chart (commandes) */
.mini-chart-hd { padding:18px 22px 12px; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; background:linear-gradient(135deg,rgba(31,124,246,.045) 0%,rgba(22,104,216,.02) 100%); border-bottom:1px solid rgba(31,124,246,.1); }
.mini-chart-lbl { font-size:10.5px; font-weight:700; color:#1F7CF6; text-transform:uppercase; letter-spacing:.6px; margin-bottom:6px; }
.mini-chart-val { font-size:26px; font-weight:800; font-family:var(--mono); color:var(--text); letter-spacing:-.8px; line-height:1; }
.mini-chart-sub { font-size:10px; color:var(--muted); margin-top:3px; }
.mini-period-badge { font-size:10.5px; color:#1F7CF6; background:rgba(31,124,246,.09); border:1px solid rgba(31,124,246,.2); border-radius:20px; padding:4px 12px; font-weight:700; white-space:nowrap; cursor:pointer; align-self:flex-start; margin-top:4px; }
.mini-delta { font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:3px; padding:3px 10px; border-radius:20px; margin-top:7px; }
.mini-delta.up { background:#dcfce7; color:#15803d; } .mini-delta.down { background:#fee2e2; color:#991b1b; }
.mini-bars-wrap { display:flex; align-items:flex-end; gap:5px; padding:18px 22px 6px; height:120px; }
.mini-bar-col { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; height:100%; }
.mini-bar { width:80%; border-radius:6px 6px 0 0; background:linear-gradient(180deg,#a78bfa 0%,#1260C9 100%); opacity:.45; min-height:3px; transform-origin:bottom; transition:transform .65s cubic-bezier(.23,1,.32,1), opacity .2s, box-shadow .2s; cursor:pointer; }
.mini-bar:hover { opacity:.85; box-shadow:0 -4px 14px rgba(31,124,246,.4); }
.mini-bar.actuel { background:linear-gradient(180deg,#7DB4FF 0%,#1260C9 100%); opacity:1; box-shadow:0 -6px 20px rgba(31,124,246,.55); }
.mini-bar-lbl { font-size:9px; color:var(--muted); font-family:var(--mono); }
.mini-bar-labs { display:flex; gap:5px; padding:0 22px 14px; }

/* Résumé rapide */
.perf-grid { display:grid; grid-template-columns:1fr 268px; gap:16px; margin-bottom:22px; align-items:start; }
@media(max-width:1000px){ .perf-grid{ grid-template-columns:1fr; } }
.resume-items { display:flex; flex-direction:column; gap:10px; }
.resume-item { display:flex; align-items:center; gap:12px; padding:13px 16px; background:var(--bg); border:1px solid var(--border); border-radius:var(--r-sm); transition:all .18s; }
.resume-item:hover { border-color:var(--brand-lt); background:var(--brand-mlt); transform:translateX(3px); }
.resume-item-ico { width:36px; height:36px; border-radius:9px; background:var(--surface); border:1px solid var(--border); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.resume-item-val { font-size:19px; font-weight:800; font-family:var(--mono); color:var(--text); letter-spacing:-.5px; line-height:1; }
.resume-item-lbl { font-size:10.5px; color:var(--muted); margin-top:2px; font-weight:600; }

/* ── Chat boutique ↔ entreprise (modal depuis cloche) ── */
.bq-chat-overlay{
    display:flex;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;
    align-items:center;justify-content:center;padding:16px;
    visibility:hidden;opacity:0;pointer-events:none;
    transition:opacity .2s,visibility .2s;
}
.bq-chat-overlay.open{visibility:visible;opacity:1;pointer-events:all;}
.bq-chat-panel{background:#fff;border-radius:16px;width:100%;max-width:520px;display:flex;flex-direction:column;height:88vh;max-height:660px;box-shadow:0 28px 80px rgba(0,0,0,.22);overflow:hidden;animation:bqModalIn .2s ease;}
@keyframes bqModalIn{from{opacity:0;transform:scale(.95) translateY(-8px)}to{opacity:1;transform:scale(1) translateY(0)}}
.bq-chat-hd{padding:14px 18px;border-bottom:1px solid #e2e8f0;flex-shrink:0;display:flex;align-items:center;justify-content:space-between;gap:10px;background:linear-gradient(135deg,#1260C9,#1668D8);color:#fff;}
.bq-chat-hd-info{display:flex;align-items:center;gap:10px;}
.bq-chat-hd-av{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;overflow:hidden;}
.bq-chat-hd-av img{width:100%;height:100%;object-fit:cover;}
.bq-chat-hd-name{font-size:14px;font-weight:800;color:#fff;line-height:1.2;}
.bq-chat-hd-sub{font-size:11px;color:rgba(255,255,255,.7);margin-top:1px;}
.bq-msg-system{align-self:center;max-width:88%;margin:4px 0;}
.bq-msg-system-pill{display:inline-block;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-size:11.5px;font-style:italic;padding:6px 14px;border-radius:20px;line-height:1.4;text-align:center;}
.bq-msg-system-time{font-size:10px;color:#9ca3af;text-align:center;margin-top:2px;}
.bq-chat-close{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;font-size:16px;flex-shrink:0;transition:background .14s;}
.bq-chat-close:hover{background:rgba(255,255,255,.3);}
.bq-chat-msgs{flex:1;overflow-y:auto;padding:14px 16px;display:flex;flex-direction:column;gap:8px;background:#f8fafc;scrollbar-width:thin;scrollbar-color:#7DB4FF #D6E6FF;}
.bq-chat-msgs::-webkit-scrollbar{width:6px;}
.bq-chat-msgs::-webkit-scrollbar-track{background:#D6E6FF;border-radius:6px;}
.bq-chat-msgs::-webkit-scrollbar-thumb{background:#7DB4FF;border-radius:6px;}
.bq-chat-msgs::-webkit-scrollbar-thumb:hover{background:#1668D8;}
.bq-msg-row{display:flex;flex-direction:column;max-width:80%;}
.bq-msg-row.mine{align-self:flex-end;align-items:flex-end;}
.bq-msg-row.theirs{align-self:flex-start;align-items:flex-start;}
.bq-msg-bubble{padding:9px 13px;border-radius:14px;font-size:13px;line-height:1.5;word-break:break-word;white-space:pre-wrap;}
.bq-msg-row.mine .bq-msg-bubble{background:#1260C9;color:#fff;border-bottom-right-radius:4px;}
.bq-msg-row.theirs .bq-msg-bubble{background:#fff;color:#1e293b;border-bottom-left-radius:4px;border:1px solid #e2e8f0;}
.bq-msg-bubble.system{background:#fef9c3;color:#92400e;border:1px solid #fde68a;font-size:12px;font-style:italic;border-radius:10px;}
.bq-msg-meta{font-size:10px;color:#94a3b8;margin-top:2px;padding:0 2px;}
.bq-chat-empty{text-align:center;padding:32px 16px;color:#94a3b8;font-size:13px;}
.bq-chat-input-zone{padding:10px 14px;flex-shrink:0;border-top:1px solid #e2e8f0;background:#fff;display:flex;gap:8px;align-items:flex-end;}
.bq-chat-textarea{flex:1;resize:none;border:1.5px solid #e2e8f0;border-radius:10px;padding:9px 12px;font-size:13px;font-family:var(--font);color:var(--text);outline:none;line-height:1.4;max-height:120px;min-height:40px;transition:border-color .14s;scrollbar-width:thin;}
.bq-chat-textarea:focus{border-color:#1668D8;}
.bq-chat-send-btn{width:38px;height:38px;border-radius:10px;border:none;cursor:pointer;background:#1668D8;color:#fff;font-size:17px;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:background .14s;}
.bq-chat-send-btn:hover{background:#1260C9;}
.bq-chat-send-btn:disabled{opacity:.5;cursor:not-allowed;}
/* Toggle livraison boutique (mobile only) */
.bq-confier-toggle{display:none;}

.bq-confier-zone{padding:10px 14px;flex-shrink:0;background:#f0fdf4;border-bottom:1.5px solid #bbf7d0;}
.bq-confier-row{display:flex;gap:8px;align-items:center;}
.bq-order-select{flex:1;padding:8px 10px;border:1.5px solid #86efac;border-radius:9px;font-size:12.5px;font-family:var(--font);color:var(--text);background:#fff;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;background-size:14px;padding-right:28px;transition:border-color .14s;}
.bq-order-select:focus{border-color:#059669;}
.bq-btn-confier{padding:8px 14px;border-radius:9px;border:none;cursor:pointer;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:13px;font-weight:800;font-family:var(--font);display:flex;align-items:center;gap:6px;white-space:nowrap;box-shadow:0 3px 10px rgba(16,185,129,.3);transition:all .15s;flex-shrink:0;}
.bq-btn-confier:hover{transform:translateY(-1px);box-shadow:0 5px 16px rgba(16,185,129,.45);}
.bq-btn-confier:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.bq-btn-confier.done{background:linear-gradient(135deg,#6b7280,#9ca3af);box-shadow:none;}
.bq-order-card{padding:9px 12px;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;transition:all .14s;display:flex;align-items:center;gap:10px;}
.bq-order-card:hover{border-color:#86efac;background:#f0fdf4;}
.bq-order-card.selected{border-color:#059669;background:#f0fdf4;box-shadow:0 0 0 3px rgba(16,185,129,.12);}
.bq-order-card-num{font-size:11px;font-weight:700;color:#1668D8;font-family:var(--mono);flex-shrink:0;}
.bq-order-card-info{flex:1;min-width:0;}
.bq-order-card-client{font-size:12.5px;font-weight:700;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.bq-order-card-amount{font-size:11px;color:#6b7280;margin-top:1px;}
.bq-order-card-check{width:20px;height:20px;border-radius:4px;border:2px solid #d1d5db;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;transition:all .14s;color:transparent;}
.bq-order-card.selected .bq-order-card-check{background:#059669;border-color:#059669;color:#fff;}
.bq-order-card-thumb{width:40px;height:40px;border-radius:8px;flex-shrink:0;overflow:hidden;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:18px;border:1px solid #e2e8f0;}
.bq-order-card-thumb img{width:100%;height:100%;object-fit:cover;}
.bq-order-card-address{font-size:10.5px;color:#6b7280;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:200px;}
#bqOrdersList::-webkit-scrollbar{width:6px;}
#bqOrdersList::-webkit-scrollbar-track{background:#f0fdf4;border-radius:6px;}
#bqOrdersList::-webkit-scrollbar-thumb{background:#86efac;border-radius:6px;}
#bqOrdersList::-webkit-scrollbar-thumb:hover{background:#4ade80;}
.bq-confier-hint{font-size:11px;color:#059669;font-weight:600;margin-bottom:6px;}

/* ── Zone autocomplete ── */
.bq-zone-result{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;transition:border-color .12s,background .12s;-webkit-tap-highlight-color:transparent;touch-action:manipulation;}
.bq-zone-result:active,.bq-zone-result:hover{background:#f0fdf4;border-color:#6ee7b7;}
.bq-zone-result-dot{width:9px;height:9px;border-radius:50%;background:#059669;flex-shrink:0;}
.bq-zone-result-name{font-size:13.5px;font-weight:700;color:#0f172a;flex:1;min-width:0;}
.bq-zone-result-right{text-align:right;flex-shrink:0;}
.bq-zone-result-price{font-size:12px;font-weight:800;color:#059669;}
.bq-zone-result-delay{font-size:10px;color:#9ca3af;margin-top:1px;}
.bq-zone-chip{display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #6ee7b7;border-radius:10px;padding:10px 12px;}
.bq-zone-chip-dot{width:10px;height:10px;border-radius:50%;background:#059669;flex-shrink:0;}
.bq-zone-chip-info{flex:1;min-width:0;}
.bq-zone-chip-name{font-size:13px;font-weight:800;color:#065f46;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.bq-zone-chip-details{font-size:11px;color:#047857;margin-top:2px;}
.bq-zone-chip-change{flex-shrink:0;padding:5px 11px;border-radius:7px;border:1px solid #6ee7b7;background:#fff;color:#059669;font-size:11px;font-weight:700;font-family:inherit;cursor:pointer;touch-action:manipulation;}
.bq-zone-empty{text-align:center;padding:14px;font-size:12px;color:#9ca3af;border-radius:9px;background:#f9fafb;}

/* ══════════════════════════════════════════════════
   CORRECTIFS RESPONSIVE COMPLÉMENTAIRES
══════════════════════════════════════════════════ */

/* Table scrollable horizontalement sur mobile */
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;}
.tbl-wrap .tbl{min-width:420px;}

/* ── Cartes commandes mobiles ── */
.orders-mobile{display:none;flex-direction:column;gap:8px;padding:10px 14px;}
.om-card{background:var(--surface);border:1.5px solid var(--border);border-radius:12px;padding:11px 13px;display:flex;align-items:center;gap:11px;text-decoration:none;color:inherit;transition:border-color .13s,box-shadow .13s;}
.om-card:active{border-color:var(--brand);box-shadow:0 0 0 3px var(--brand-mlt);}
.om-card-av{width:36px;height:36px;border-radius:9px;background:var(--brand-mlt);border:1.5px solid var(--brand-lt);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:var(--brand);flex-shrink:0;font-family:var(--mono);}
.om-card-body{flex:1;min-width:0;}
.om-card-ref{font-size:11px;font-weight:700;color:var(--muted);font-family:var(--mono);}
.om-card-client{font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.om-card-right{text-align:right;flex-shrink:0;}
.om-card-amt{font-size:13px;font-weight:800;color:var(--text);}
.om-card-devise{font-size:9.5px;color:var(--muted);font-weight:500;}
@media(max-width:600px){
    .tbl-wrap{display:none;}
    .orders-mobile{display:flex;}
}

/* ══ PANNEAU NOTIFICATIONS BOUTIQUE ══ */
.bq-notif-backdrop {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.4); backdrop-filter:blur(2px);
    z-index:498;
}
.bq-notif-backdrop.active { display:block; }

.bq-notif-panel {
    position:absolute; top:calc(100% + 10px); right:0;
    width:320px; background:#fff;
    border:1px solid rgba(0,0,0,.09); border-radius:16px;
    box-shadow:0 20px 60px rgba(0,0,0,.14);
    z-index:500; overflow:hidden;
    visibility:hidden; opacity:0; transform:translateY(-8px) scale(.97); pointer-events:none;
    transition:opacity .22s cubic-bezier(.23,1,.32,1), transform .22s cubic-bezier(.23,1,.32,1), visibility 0s .22s;
}
.bq-notif-panel.open { visibility:visible; opacity:1; transform:translateY(0) scale(1); pointer-events:all; transition:opacity .22s cubic-bezier(.23,1,.32,1), transform .22s cubic-bezier(.23,1,.32,1), visibility 0s 0s; }

.bq-np-hd {
    padding:13px 16px 11px;
    border-bottom:1px solid rgba(0,0,0,.06);
    display:flex; align-items:center; justify-content:space-between;
}
.bq-np-title { font-size:13px; font-weight:800; color:#111827; display:flex; align-items:center; gap:6px; }
.bq-np-cnt { background:var(--brand); color:#fff; font-size:10px; font-weight:800; padding:1px 8px; border-radius:20px; }

.bq-np-list { max-height:340px; overflow-y:auto; scrollbar-width:thin; scrollbar-color:#e2e8f0 transparent; }
.bq-np-list::-webkit-scrollbar { width:3px; }
.bq-np-list::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:3px; }

.bq-np-section { padding:6px 12px 3px; font-size:9.5px; font-weight:800; letter-spacing:1.2px; color:#94a3b8; text-transform:uppercase; display:flex; align-items:center; gap:5px; }

.bq-np-item {
    display:flex; align-items:flex-start; gap:10px;
    padding:11px 16px; cursor:pointer;
    border-bottom:1px solid rgba(0,0,0,.04);
    transition:background .14s; text-decoration:none; color:inherit;
}
.bq-np-item:hover { background:rgba(22,104,216,.04); }
.bq-np-item:last-child { border-bottom:none; }

.bq-np-av {
    width:36px; height:36px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:800; color:#fff;
}
.bq-np-body { flex:1; min-width:0; }
.bq-np-name { font-size:12px; font-weight:700; color:#111827; }
.bq-np-msg { font-size:11.5px; color:#64748b; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; margin-top:2px; }
.bq-np-meta { display:flex; align-items:center; justify-content:space-between; margin-top:4px; }
.bq-np-time { font-size:10px; color:#94a3b8; }
.bq-np-badge { font-size:9.5px; font-weight:800; padding:2px 7px; border-radius:20px; }
.bq-np-badge.order   { background:#fef3c7; color:#92400e; }
.bq-np-badge.msg     { background:#D6E6FF; color:#5b21b6; }
.bq-np-badge.company { background:#EAF2FF; color:#0B1E3D; }
.bq-np-badge.support { background:#f0fdf4; color:#166534; }

.bq-np-close-btn {
    flex-shrink:0; width:24px; height:24px; border:none; background:none;
    color:#9ca3af; cursor:pointer; border-radius:6px; align-self:flex-start; margin-top:1px;
    display:flex; align-items:center; justify-content:center;
    transition:background .14s, color .14s;
}
.bq-np-close-btn:hover { background:#fee2e2; color:#dc2626; }

.bq-np-empty { padding:32px 16px; text-align:center; color:#94a3b8; font-size:12.5px; }
.bq-np-foot { padding:10px 14px; border-top:1px solid rgba(0,0,0,.06); display:flex; gap:6px; }
.bq-np-foot .btn { flex:1; justify-content:center; font-size:11px; gap:4px; }

/* ── Bottom-sheet sur mobile ── */
@media(max-width:640px) {
    .notif-bell-wrap { position:static !important; }
    .bq-notif-panel {
        position:fixed !important; top:auto !important; bottom:0 !important;
        left:0 !important; right:0 !important;
        width:100% !important; border-radius:18px 18px 0 0;
        max-height:78vh; overflow-y:auto;
        visibility:hidden; transform:translateY(102%); opacity:1;
        transition:transform .3s cubic-bezier(.23,1,.32,1), visibility 0s .3s;
    }
    .bq-notif-panel.open { visibility:visible; transform:translateY(0); transition:transform .3s cubic-bezier(.23,1,.32,1), visibility 0s 0s; }
    .bq-np-list { max-height:none; overflow-y:visible; }
    .bq-notif-panel::before {
        content:''; display:block; width:36px; height:4px;
        background:rgba(0,0,0,.1); border-radius:2px;
        margin:10px auto 0;
    }
}

/* co-row (liste entreprises partenaires) : bouton passe en dessous sur très petit écran */
@media(max-width:400px){
    .co-row{flex-wrap:wrap;gap:8px;}
    .co-info{min-width:0;flex:1 1 calc(100% - 50px);}
    .co-commission{margin-left:auto;}
    .co-row .btn{width:100%;justify-content:center;font-size:11px;}
}

/* Period stats : reste en 2 colonnes sous 400px */
@media(max-width:400px){
    .period-stats{grid-template-columns:repeat(2,1fr) !important;}
    .period-stat:nth-child(2){border-right:none !important;}
    .period-stat:nth-child(3){border-top:1px solid var(--border);}
}

/* risk-row : 1 par ligne sur très petit écran */
@media(max-width:400px){
    .risk-row{min-width:100% !important;border-right:none !important;}
}

/* Topbar : compresse les actions sur très petit écran */
@media(max-width:360px){
    .tb-actions{gap:4px;}
    .tb-icon-btn{width:30px;height:30px;font-size:13px;}
    .btn-hamburger{padding:4px;}
    .topbar{padding:0 8px;}
}

/* KPI : 1 colonne sous 340px */
@media(max-width:340px){
    .kpi-grid{grid-template-columns:1fr !important;}
}

/* Kanban : 2 colonnes sous 400px */
@media(max-width:400px){
    .kanban-grid{grid-template-columns:repeat(2,1fr) !important;gap:8px;}
    .kanban-count{font-size:24px;}
    .kanban-ico-wrap{width:38px;height:38px;font-size:17px;margin-bottom:8px;}
}

/* Quick actions : 2 colonnes, moins de padding sur mobile */
@media(max-width:400px){
    .quick-btn{padding:14px 8px 12px;}
    .quick-btn-ico{width:36px;height:36px;font-size:18px;}
    .quick-btn-lbl{font-size:11px;}
    .quick-btn-sub{display:none;}
}

/* Charts : hauteur adaptée sur mobile */
@media(max-width:520px){
    .mini-bars-wrap{height:80px;padding:12px 14px 4px;}
    .mini-chart-hd{padding:12px 14px 10px;}
    .mini-chart-val{font-size:22px;}
    .rc-svg-wrap{padding:10px 14px 2px;}
}

/* Today cards : empilées sur mobile */
@media(max-width:520px){
    .today-grid{grid-template-columns:1fr !important;}
    .today-card{padding:16px;}
    .today-val{font-size:22px;}
    .today-wave{opacity:.5;}
}

/* perf-grid : 1 colonne sur mobile */
@media(max-width:700px){
    .perf-grid{grid-template-columns:1fr !important;}
}

/* Bq chat panel : plein écran sur mobile */
@media(max-width:560px){
    .bq-chat-overlay{padding:0;align-items:flex-end;}
    .bq-chat-panel{
        border-radius:20px 20px 0 0;max-width:100%;
        /* dvh = hauteur visible réelle (iOS 16+), vh comme fallback */
        height:82vh;max-height:82vh;
        height:80dvh;max-height:80dvh;
    }
    .bq-confier-row{flex-wrap:wrap;}
    .bq-btn-confier{width:100%;justify-content:center;}
    /* Anti-zoom iOS : font-size >= 16px sur tous les champs */
    .bq-chat-textarea{font-size:16px !important;}
    #bqZoneSearch,#bqZonePicker,.bq-order-select{font-size:16px !important;}

    /* ── Toggle livraison ── */
    .bq-confier-toggle{
        display:flex;align-items:center;justify-content:space-between;
        padding:9px 14px;flex-shrink:0;
        background:#ecfdf5;border-bottom:1.5px solid #bbf7d0;
        cursor:pointer;border:none;width:100%;font-family:var(--font);
        touch-action:manipulation;user-select:none;
    }
    .bq-ct-left{display:flex;align-items:center;gap:7px;}
    .bq-ct-label{font-size:12.5px;font-weight:700;color:#059669;}
    .bq-ct-badge{
        display:none;background:#059669;color:#fff;
        font-size:9.5px;font-weight:800;padding:1px 7px;border-radius:20px;
        animation:bqPulseBadge 1.6s ease infinite;
    }
    .bq-ct-badge.visible{display:inline-block;}
    @keyframes bqPulseBadge{0%,100%{opacity:1;}50%{opacity:.55;}}
    .bq-ct-arrow{font-size:13px;color:#059669;transition:transform .28s ease;}
    .bq-confier-toggle.zone-open .bq-ct-arrow{transform:rotate(180deg);}

    /* Zone collapsible sur mobile */
    .bq-confier-zone{
        overflow:hidden;
        max-height:0;
        padding-top:0 !important;padding-bottom:0 !important;
        border-bottom:none;
        transition:max-height .3s ease, padding .3s ease;
    }
    .bq-confier-zone.zone-open{
        max-height:520px;
        padding-top:10px !important;padding-bottom:10px !important;
        border-bottom:1.5px solid #bbf7d0;
    }
}

/* Résumé rapide : compact sur mobile */
@media(max-width:520px){
    .resume-item{padding:10px 12px;}
    .resume-item-val{font-size:16px;}
    .resume-item-ico{width:30px;height:30px;font-size:15px;}
}

/* Card header : texte tronqué sur mobile */
@media(max-width:520px){
    .card-hd{flex-wrap:wrap;gap:6px;padding:12px 14px;}
    .card-title{font-size:12px;}
    .card-bd{padding:12px 14px;}
    .card-hd .btn-ghost{font-size:11px;padding:4px 6px;}
}

/* lv-row compact sur mobile */
@media(max-width:400px){
    .lv-row{padding:8px 12px;gap:8px;}
    .lv-av{width:34px;height:34px;font-size:11px;}
    .lv-nm{font-size:12px;}
}

/* Scrollbar globale fine (fallback pour éléments sans règle spécifique) */
*{scrollbar-width:thin;}
::-webkit-scrollbar{width:5px;height:5px;}
::-webkit-scrollbar-thumb{background:rgba(22,104,216,.25);border-radius:5px;}
::-webkit-scrollbar-thumb:hover{background:rgba(22,104,216,.5);}

/* ══════════════════════════════════════════
   PLAN INDICATORS — usage bars & lock overlays
══════════════════════════════════════════ */

/* Bannière plan en haut du dashboard */
.plan-banner{
    background:linear-gradient(135deg,#fff7ed 0%,#fef3c7 100%);
    border:1.5px solid #fcd34d;border-radius:var(--r);
    padding:14px 18px;margin-bottom:20px;
    display:flex;align-items:center;gap:16px;flex-wrap:wrap;
}
.plan-banner.pro{
    background:linear-gradient(135deg,#EAF2FF 0%,#EAF2FF 100%);
    border-color:rgba(22,104,216,.35);
}
.plan-banner-badge{
    display:inline-flex;align-items:center;gap:6px;
    font-size:11px;font-weight:800;padding:4px 12px;border-radius:20px;
    text-transform:uppercase;letter-spacing:.6px;white-space:nowrap;flex-shrink:0;
}
.plan-banner-badge.free{background:#fef3c7;color:#92400e;border:1px solid #fcd34d;}
.plan-banner-badge.pro{background:#EAF2FF;color:#0B1E3D;border:1px solid #7DB4FF;}
.plan-usages{display:flex;align-items:center;gap:20px;flex-wrap:wrap;flex:1;}
.plan-usage-item{display:flex;flex-direction:column;gap:4px;min-width:140px;}
.plan-usage-top{display:flex;align-items:center;justify-content:space-between;gap:8px;}
.plan-usage-lbl{font-size:11px;font-weight:700;color:var(--text-2);}
.plan-usage-count{font-size:12px;font-weight:800;font-family:var(--mono);color:var(--text);}
.plan-usage-count.warn{color:#d97706;}
.plan-usage-count.danger{color:#dc2626;}
.plan-usage-track{height:6px;background:#e2e8f0;border-radius:4px;overflow:hidden;}
.plan-usage-fill{height:100%;border-radius:4px;transition:width .6s cubic-bezier(.23,1,.32,1);}
.plan-usage-fill.ok{background:linear-gradient(90deg,#10b981,#34d399);}
.plan-usage-fill.warn{background:linear-gradient(90deg,#f59e0b,#fbbf24);}
.plan-usage-fill.danger{background:linear-gradient(90deg,#ef4444,#f87171);}
.plan-banner-cta{
    display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
    background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;
    font-size:12px;font-weight:800;border-radius:9px;text-decoration:none;
    white-space:nowrap;flex-shrink:0;transition:all .15s;
    box-shadow:0 3px 12px rgba(245,158,11,.35);
}
.plan-banner-cta:hover{transform:translateY(-1px);box-shadow:0 5px 18px rgba(245,158,11,.5);color:#fff;}
.plan-banner-cta.pro-active{
    background:linear-gradient(135deg,#1668D8,#1260C9);
    box-shadow:0 3px 12px rgba(22,104,216,.35);
}
@media (max-width: 640px) {
    .plan-banner-badge{
        white-space:normal;flex-shrink:1;width:100%;
        text-align:center;line-height:1.5;
    }
}

/* Overlay de verrouillage sur les sections bloquées */
.plan-locked-wrap{position:relative;}
.plan-locked-wrap .plan-locked-overlay{
    position:absolute;inset:0;z-index:10;
    background:rgba(248,250,252,.88);
    backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
    border-radius:var(--r);
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:12px;padding:24px;text-align:center;
}
.plan-locked-ico{
    width:52px;height:52px;border-radius:50%;
    background:linear-gradient(135deg,#fef3c7,#fde68a);
    border:2px solid #fcd34d;
    display:flex;align-items:center;justify-content:center;font-size:22px;
    box-shadow:0 4px 16px rgba(245,158,11,.25);
}
.plan-locked-title{font-size:14px;font-weight:800;color:var(--text);}
.plan-locked-sub{font-size:12px;color:var(--text-2);line-height:1.5;max-width:260px;}
.plan-locked-btn{
    display:inline-flex;align-items:center;gap:6px;padding:9px 20px;
    background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;
    font-size:12.5px;font-weight:800;border-radius:9px;text-decoration:none;
    transition:all .15s;box-shadow:0 3px 12px rgba(245,158,11,.35);
}
.plan-locked-btn:hover{transform:translateY(-1px);box-shadow:0 5px 18px rgba(245,158,11,.5);color:#fff;}

/* Quick btn verrouillé */
.quick-btn.locked{opacity:.7;cursor:default;position:relative;}
.quick-btn.locked::after{
    content:'🔒';position:absolute;top:8px;right:8px;
    font-size:12px;line-height:1;
}
.quick-btn.locked:hover{background:transparent !important;}
.quick-btn.locked:hover .quick-btn-ico{transform:none !important;}
.quick-btn.locked:hover::before{opacity:0 !important;}
.qb-locked-badge{
    font-size:9.5px;font-weight:800;color:#d97706;
    background:#fef3c7;border:1px solid #fcd34d;
    padding:1px 6px;border-radius:8px;margin-top:2px;
}

/* ══════════════════════════════════════════════════════════════════
   REFONTE « ESPACE VENDEUR » — bleu Shopio (maquette Main.dc.html)
   Placée en dernier : elle prend le pas sur les anciens styles sans
   toucher aux identifiants utilisés par les scripts de la page.
══════════════════════════════════════════════════════════════════ */
:root {
    --brand: #1668D8; --brand-dk: #1260C9; --brand-lt: #D6E6FF; --brand-mlt: #EAF2FF;
    --sv-blue: #1F7CF6; --sv-btn: #1668D8; --sv-btn-h: #1260C9; --sv-b50: #EAF2FF; --sv-b100: #D6E6FF;
    --sv-night: #0B1E3D; --sv-night2: #132B52; --sv-sky: #7DB4FF;
    --sv-amber: #F59E0B; --sv-amb50: #FEF3E0; --sv-amb-ink: #92400E;
    --sv-ok: #15803D; --sv-ok50: #E7F6EC; --sv-warn: #B45309; --sv-err: #B91C1C; --sv-err50: #FDECEC;
    --sv-e-out: cubic-bezier(.22,1,.36,1); --sv-e-io: cubic-bezier(.65,0,.35,1); --sv-e-pop: cubic-bezier(.34,1.56,.64,1);
    --bg: #F6F9FF; --surface: #FFFFFF; --border: #E3E8F2; --text: #16162A; --text-2: #45455E; --muted: #62627C;
    --mono: 'Plus Jakarta Sans', system-ui, sans-serif;
    --sb-w: 264px; --top-h: 72px;
}
[data-theme="dark"] {
    --brand-lt: #1B3563; --brand-mlt: #13284B;
    --sv-b50: #13284B; --sv-b100: #1B3563; --sv-amb50: #2E2208; --sv-amb-ink: #FBBF24;
    --sv-ok: #4ADE80; --sv-ok50: #0F2A1C; --sv-warn: #FBBF24; --sv-err: #F87171; --sv-err50: #2A1214;
    --bg: #0A1426; --surface: #10203B; --border: #22355A; --border-dk: #2B4370;
    --text: #EAF0FA; --text-2: #C3CEE0; --muted: #9DAAC2;
}
body { font-variant-numeric: tabular-nums; }
.sv-num { font-variant-numeric: tabular-nums; white-space: nowrap; }

/* Barre latérale bleu nuit, élément actif en pilule bleue */
.sidebar { background: var(--sv-night) !important; border-right: 0 !important; box-shadow: none !important; }
.sb-brand { border-bottom-color: #1C3560 !important; }
.sb-section { color: #8EA3C4 !important; font-size: 11px !important; letter-spacing: .09em !important; padding: 16px 12px 6px !important; }
.sb-nav .sb-item { min-height: 44px; padding: 0 12px; border-radius: 14px; color: #C9D6EA; font-size: 15px; gap: 12px; transition: background-color 180ms var(--sv-e-out), color 180ms, transform 180ms var(--sv-e-out); }
.sb-nav .sb-item:hover { background: var(--sv-night2); color: #fff; transform: translateX(2px); }
.sb-nav .sb-item.active { background: #1668D8 !important; color: #fff !important; box-shadow: 0 8px 18px rgba(22,104,216,.35) !important; }
.sb-nav .sb-item.active::before { display: none !important; }
.sb-nav .sb-item .ico { background: transparent !important; border: 0 !important; width: 22px; }
.sb-badge { border-radius: 999px; font-family: var(--font); font-weight: 800; font-size: 12px; min-width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; padding: 0 7px; }
.sb-group-toggle { min-height: 44px; border-radius: 14px; font-size: 15px !important; color: #C9D6EA !important; }
.sb-user { background: var(--sv-night2); border-radius: 16px; padding: 12px; }
.sb-av { background: var(--sv-blue) !important; box-shadow: none !important; width: 38px; height: 38px; font-size: 13px; }
.sb-logout { border-radius: 999px; min-height: 40px; }

/* Barre du haut */
/* Fond plein, sans backdrop-filter : un filtre ferait de la barre le repère des éléments
   position:fixed qu'elle contient (toast de négociation, menus) et les ferait apparaître dedans. */
.topbar { height: auto; min-height: var(--top-h); padding: 14px 28px; background: var(--bg); box-shadow: none; border-bottom: 1px solid var(--border); }
/* Cartes « aujourd'hui » : bleu Shopio et bleu nuit au lieu du dégradé violet */
.today-grid { gap: 16px; }
.today-card { background: linear-gradient(135deg, #1F7CF6, #1260C9) !important; border: 0 !important; box-shadow: 0 14px 30px -12px rgba(22,104,216,.55) !important; }
.today-grid .today-card + .today-card { background: var(--sv-night) !important; box-shadow: 0 14px 30px -12px rgba(11,30,61,.55) !important; }
.today-icon { border-radius: 14px; box-shadow: inset -5px -6px 10px rgba(0,0,0,.15), inset 4px 4px 8px rgba(255,255,255,.2); }
.today-val { font-family: var(--font) !important; font-weight: 800; }
.sv-title { display: flex; align-items: center; gap: 10px; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sv-title-ico { color: var(--sv-blue); display: flex; }
.sv-title-ico svg { width: 22px; height: 22px; }
.topbar .tb-greeting-sub { font-size: 13px; color: var(--muted); }
.topbar .btn, .topbar .tb-icon-btn, .topbar .export-dropdown-btn { min-height: 44px; min-width: 44px; justify-content: center; border-radius: 999px; padding: 0 16px; font-size: 14px; font-weight: 700; border: 0; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); transition: box-shadow 180ms var(--sv-e-out), color 180ms, transform 180ms var(--sv-e-out); }
.topbar .tb-icon-btn, .topbar #notifBellBtn { padding: 0 !important; width: 44px; }
.topbar .btn:hover, .topbar .tb-icon-btn:hover, .topbar .export-dropdown-btn:hover { background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--sv-blue); color: var(--sv-btn); }
.topbar .btn-primary { background: var(--sv-btn); color: #fff; box-shadow: none; }
.topbar .btn-primary:hover { background: var(--sv-btn-h); color: #fff; box-shadow: 0 8px 20px rgba(22,104,216,.28); transform: translateY(-1px); }
.topbar .btn:active { transform: scale(.97); }
.content { padding: 24px 28px 48px; }

/* Bannière du plan : alerte ambre douce */
.plan-banner { border-radius: 20px !important; }
.plan-banner:not(.pro) { background: var(--sv-amb50) !important; border: 0 !important; box-shadow: inset 0 0 0 1px #F7D59B; }
.plan-banner.pro { background: var(--sv-b50) !important; border: 0 !important; box-shadow: inset 0 0 0 1px var(--sv-b100); }
.plan-banner-cta { border-radius: 999px !important; background: var(--sv-amber) !important; color: #16162A !important; min-height: 44px; display: inline-flex !important; align-items: center; }

/* Cartes génériques */
.card, .kpi, .quick-card, .today-card { border-radius: 24px !important; }
.card { border: 0 !important; box-shadow: 0 0 0 1px var(--border) !important; }
.card.chart-wrap { border-top: 0 !important; }
.alert-item { border-radius: 18px; }
.flash { border-radius: 16px; }

/* Indicateurs : icône en relief, légère bascule 3D au survol */
.kpi-grid { gap: 16px !important; margin-bottom: 20px !important; }
.kpi { border: 0 !important; box-shadow: 0 0 0 1px var(--border) !important; padding: 20px !important; transition: transform 260ms var(--sv-e-out), box-shadow 260ms var(--sv-e-out) !important; }
.kpi::before { display: none !important; }
.kpi:hover { transform: perspective(800px) rotateX(7deg) rotateY(-7deg) translateY(-4px) !important; box-shadow: 0 0 0 1px var(--border), 10px 18px 32px rgba(11,30,61,.10) !important; }
.kpi-icon { width: 46px !important; height: 46px !important; border-radius: 14px !important; border: 0 !important; color: #fff; box-shadow: inset -5px -6px 10px rgba(0,0,0,.18), inset 4px 4px 8px rgba(255,255,255,.3), 0 8px 16px rgba(11,30,61,.12) !important; }
.kpi-icon svg { stroke: currentColor; }
.kpi-grid .kpi:nth-child(1) .kpi-icon { background: var(--sv-blue) !important; }
.kpi-grid .kpi:nth-child(2):not([style*="border:2px"]) .kpi-icon { background: var(--sv-night) !important; color: var(--sv-sky); }
.kpi-grid .kpi:nth-child(3) .kpi-icon { background: var(--sv-amber) !important; color: #16162A; }
.kpi-grid .kpi:nth-child(4) .kpi-icon { background: #15803D !important; }
.kpi-lbl { font-size: 12px !important; font-weight: 800 !important; letter-spacing: .07em !important; color: var(--muted) !important; margin-top: 8px; }
.kpi-val { font-family: var(--font) !important; font-size: 28px !important; font-weight: 800 !important; letter-spacing: -.02em !important; line-height: 1.1 !important; }
.kpi-unit { font-size: 13px; color: var(--muted); }
[data-theme="dark"] .kpi:hover { box-shadow: 0 0 0 1px var(--border), 10px 18px 32px rgba(0,0,0,.4) !important; }

/* Boutons de la refonte */
.sv-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 999px; font: 700 14px/1 var(--font); text-decoration: none; border: 0; cursor: pointer; white-space: nowrap; transition: transform 180ms var(--sv-e-out), background-color 180ms var(--sv-e-out), box-shadow 180ms var(--sv-e-out), color 180ms var(--sv-e-out); }
.sv-btn:active { transform: scale(.97); }
.sv-btn:focus-visible, .sv-pcol:focus-visible, .quick-btn:focus-visible { outline: 3px solid var(--sv-blue); outline-offset: 2px; }
.sv-btn-sm { min-height: 38px; padding: 0 14px; }
.sv-btn-amber { min-height: 48px; background: var(--sv-amber); color: #16162A; }
.sv-btn-amber:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(245,158,11,.35); color: #16162A; }
.sv-btn-line { min-height: 48px; background: transparent; color: #fff; box-shadow: inset 0 0 0 1.5px #2B4A7E; }
.sv-btn-line:hover { box-shadow: inset 0 0 0 1.5px var(--sv-sky); color: #fff; }
.sv-btn-line-dk { background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); }
.sv-btn-line-dk:hover { box-shadow: inset 0 0 0 1.5px var(--sv-blue); color: var(--sv-btn); }
.sv-btn-blue { background: var(--sv-btn); color: #fff; }
.sv-btn-blue:hover { background: var(--sv-btn-h); color: #fff; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(22,104,216,.28); }

/* Cartes de la refonte */
.sv-card { background: var(--surface); border-radius: 24px; box-shadow: 0 0 0 1px var(--border); padding: 22px; display: flex; flex-direction: column; gap: 14px; min-width: 0; }
.sv-card-hd { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.sv-card-hd h2, .sv-row-3 .quick-card-title { margin: 0; font-size: 17px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.sv-muted { margin: 0; color: var(--muted); font-size: 14px; }
.sv-ok { margin: 0; color: var(--sv-ok); font-weight: 700; }
.sv-badge { display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 999px; font-size: 12px; font-weight: 800; white-space: nowrap; }
.sv-badge-err { background: var(--sv-err50); color: var(--sv-err); }
.sv-tile { display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: inset -5px -6px 10px rgba(0,0,0,.10), inset 4px 4px 8px rgba(255,255,255,.3), 0 8px 16px rgba(11,30,61,.10); }
.sv-tile-sm { width: 40px; height: 40px; border-radius: 12px; }
.sv-tile-soft { background: var(--sv-b50); color: var(--sv-btn); }
[data-theme="dark"] .sv-tile-soft { color: var(--sv-sky); }

/* Bonjour + scène 3D */
.sv-hero { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 20px; margin-bottom: 20px; }
.sv-hello { position: relative; overflow: hidden; background: var(--sv-night); color: #fff; border-radius: 24px; padding: 30px; display: grid; grid-template-columns: minmax(0, 1fr) 220px; gap: 16px; align-items: center; }
.sv-hello-txt { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 14px; }
.sv-chip { align-self: flex-start; display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 999px; background: var(--sv-night2); color: var(--sv-sky); font-size: 12px; font-weight: 800; }
.sv-hello-title { margin: 0; font-size: 32px; line-height: 1.1; font-weight: 800; letter-spacing: -.02em; color: #fff; text-wrap: balance; }
.sv-hello-title span { color: var(--sv-sky); }
.sv-hello-sub { margin: 0; max-width: 460px; color: #C9D6EA; font-size: 15px; }
.sv-hello-cta { display: flex; flex-wrap: wrap; gap: 10px; }
.sv-scene { position: relative; height: 200px; perspective: 1200px; }
.sv-stage { position: absolute; inset: 0; transform-style: preserve-3d; transform: rotateX(var(--rx, 6deg)) rotateY(var(--ry, -16deg)); transition: transform 450ms var(--sv-e-out); }
.sv-layer { position: absolute; transform-style: preserve-3d; }
.sv-sphere { left: 20px; top: 0; width: 180px; height: 180px; border-radius: 50%; background: var(--sv-blue); box-shadow: inset -24px -30px 50px rgba(5,15,35,.5), inset 18px 18px 36px rgba(255,255,255,.18); transform: translateZ(-80px); }
.sv-float-card { left: 0; top: 112px; transform: translateZ(90px); }
.sv-float { padding: 10px 12px; border-radius: 16px; background: #fff; color: #16162A; box-shadow: 0 14px 28px rgba(0,0,0,.35); animation: svFloat 4s var(--sv-e-io) infinite; }
.sv-float-lbl { font-size: 11px; font-weight: 800; color: #62627C; text-transform: uppercase; letter-spacing: .06em; }
.sv-float-val { font-size: 22px; font-weight: 800; }
.sv-coin-wrap { right: 0; top: 18px; transform: translateZ(140px); }
.sv-coin { width: 58px; height: 58px; border-radius: 999px; background: var(--sv-amber); color: #16162A; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; box-shadow: inset -6px -8px 12px rgba(120,60,0,.45), inset 4px 4px 8px rgba(255,255,255,.4), 0 14px 26px rgba(0,0,0,.35); animation: svFloat 4s var(--sv-e-io) .8s infinite; }
.sv-visits-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
.sv-visit { padding: 14px; border-radius: 18px; }
.sv-visit-val { font-size: 26px; font-weight: 800; color: var(--text); }
.sv-visit-lbl { font-size: 13px; font-weight: 600; color: var(--text-2); }
.sv-tone-b { background: var(--sv-b50); } .sv-tone-b .sv-visit-val { color: var(--sv-btn-h); }
.sv-tone-a { background: var(--sv-amb50); } .sv-tone-a .sv-visit-val { color: var(--sv-amb-ink); }
.sv-tone-g { background: var(--sv-ok50); } .sv-tone-g .sv-visit-val { color: var(--sv-ok); }
.sv-tone-n { background: var(--bg); }
[data-theme="dark"] .sv-tone-b .sv-visit-val { color: var(--sv-sky); }

/* Rangées */
.sv-row { display: grid; gap: 20px; margin-bottom: 20px; }
.sv-row-pipe { grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); }
.sv-row-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: stretch; }

/* Pipeline en colonnes 3D (prismes) */
.sv-pipe { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 6px; padding-top: 22px; }
.sv-pcol { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 10px; min-height: 250px; padding: 14px 8px; border-radius: 18px; text-decoration: none; color: inherit; transition: background-color 180ms var(--sv-e-out), transform 220ms var(--sv-e-out); }
.sv-pcol:hover { background: var(--sv-b50); transform: translateY(-3px); }
.sv-prism { position: relative; display: block; width: 46px; height: var(--h); margin-right: 16px; background: var(--front); transform-origin: 50% 100%; animation: svGrow 900ms var(--sv-e-out) backwards; }
.sv-prism-s { position: absolute; left: 46px; top: 0; width: 16px; height: 100%; background: var(--side); transform-origin: 0 0; transform: skewY(-45deg); }
.sv-prism-t { position: absolute; left: 0; top: -16px; width: 46px; height: 16px; background: var(--top); transform-origin: 0 100%; transform: skewX(-45deg); }
.sv-pcol-n { font-size: 26px; font-weight: 800; color: var(--text); }
.sv-pcol-lbl { min-height: 3em; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--text-2); text-align: center; }
.sv-pcol-lbl small { font-size: 11px; font-weight: 600; text-transform: none; letter-spacing: 0; color: var(--muted); }

/* À traiter maintenant */
.sv-urgent-row { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 16px; background: var(--bg); }
.sv-urgent-txt { flex: 1; min-width: 0; }
.sv-urgent-prod { font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text); }
.sv-urgent-meta { font-size: 13px; color: var(--muted); }

/* Meilleurs clients */
.sv-client { display: flex; flex-direction: column; gap: 6px; }
.sv-client-top { display: flex; justify-content: space-between; gap: 8px; font-size: 14px; color: var(--text); }
.sv-client-top strong { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sv-client-top span { font-weight: 700; white-space: nowrap; }
.sv-bar { height: 10px; border-radius: 10px; background: var(--sv-b50); overflow: hidden; }
.sv-bar span { display: block; height: 100%; border-radius: 10px; background: var(--sv-blue); transform-origin: left center; transform: scaleX(var(--r)); animation: svBar 900ms var(--sv-e-out) backwards; }

/* Actions rapides dans la rangée de 3 cartes */
.sv-row-3 .quick-section { margin: 0; }
.sv-row-3 .quick-card { height: 100%; border: 0 !important; box-shadow: 0 0 0 1px var(--border); padding: 22px; display: flex; flex-direction: column; gap: 12px; overflow: visible; }
.sv-row-3 .quick-card-hd { padding: 0; border: 0; }
.sv-row-3 .quick-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 10px; }
.sv-row-3 .quick-btn { align-items: flex-start; text-align: left; gap: 8px; padding: 14px; border: 0 !important; border-radius: 18px; background: var(--bg) !important; transition: transform 260ms var(--sv-e-out), box-shadow 260ms var(--sv-e-out); }
.sv-row-3 .quick-btn::before { display: none !important; }
.sv-row-3 .quick-btn:hover { transform: perspective(800px) rotateX(7deg) rotateY(-7deg) translateY(-4px); box-shadow: 0 0 0 1px var(--border), 10px 18px 32px rgba(11,30,61,.10); }
.sv-row-3 .quick-btn-ico { width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--surface); box-shadow: inset -4px -5px 8px rgba(0,0,0,.08), 0 6px 12px rgba(11,30,61,.08); }
.sv-row-3 .quick-btn-ico svg { width: 18px; height: 18px; }
.sv-row-3 .quick-btn-lbl { font-size: 14px; font-weight: 800; color: var(--text); line-height: 1.25; }
.sv-row-3 .quick-btn-sub { font-size: 12.5px; color: var(--muted); }

/* Livraison */
.sv-rider { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 18px; background: var(--sv-ok50); color: var(--text); }
.sv-rider-off { background: var(--bg); }
.sv-rider-av { position: relative; width: 44px; height: 44px; border-radius: 999px; background: #15803D; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
.sv-rider-av i { position: absolute; right: 0; bottom: 0; width: 12px; height: 12px; border-radius: 999px; background: #4ADE80; box-shadow: 0 0 0 2px var(--sv-ok50); }
.sv-rider-off .sv-rider-av { background: var(--sv-b50); color: var(--sv-btn); }
.sv-rider-st { font-size: 13px; font-weight: 700; color: var(--sv-ok); }
.sv-kv { margin: 0; display: flex; flex-direction: column; gap: 8px; font-size: 14px; }
.sv-kv div { display: flex; justify-content: space-between; gap: 8px; }
.sv-kv dt { color: var(--text-2); }
.sv-kv dd { margin: 0; font-weight: 800; color: var(--text); }
.sv-kv dd.is-warn { color: var(--sv-warn); }

/* Message bref (partage de la boutique) */
.sv-toast { position: fixed; left: 50%; bottom: calc(24px + env(safe-area-inset-bottom, 0px)); z-index: 9500; display: flex; align-items: center; gap: 10px; padding: 14px 20px; border-radius: 999px; background: var(--sv-night); color: #fff; font-weight: 700; box-shadow: 0 16px 32px rgba(11,30,61,.35); transform: translateX(-50%); animation: svToast 300ms var(--sv-e-out) both; }

/* Mouvements */
@keyframes svFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
@keyframes svGrow { from { transform: scaleY(0); } }
@keyframes svBar { from { transform: scaleX(0); } }
@keyframes svRise { from { opacity: 0; transform: translateY(16px); } }
@keyframes svToast { from { opacity: 0; transform: translate(-50%, 16px); } }
.sv-in { animation: svRise 600ms var(--sv-e-out) backwards; }
.sv-row .sv-in:nth-child(2) { animation-delay: 80ms; }
.sv-row .sv-in:nth-child(3) { animation-delay: 160ms; }

/* Responsive */
@media (max-width: 1180px) {
    .sv-row-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sv-row-3 > .quick-section { grid-column: 1 / -1; grid-row: 1; }
}
@media (max-width: 900px) {
    .sv-hero, .sv-row-pipe, .sv-row-3 { grid-template-columns: minmax(0, 1fr); }
    .sv-row-3 > .quick-section { grid-column: auto; grid-row: auto; }
    .sv-hello { grid-template-columns: minmax(0, 1fr); }
    .sv-scene { display: none; }
    .topbar { padding: 10px 14px; }
    .content { padding: 16px; }
}
@media (max-width: 640px) {
    /* Titre sur la 1re ligne, boutons sur la 2e : le titre n'est plus tronqué */
    .topbar { flex-wrap: wrap; row-gap: 10px; }
    .topbar .tb-info { flex: 1 1 calc(100% - 60px); }
    .topbar .tb-actions { width: 100%; justify-content: flex-end; flex-wrap: wrap; }
    .sv-title { font-size: 19px; }
    .sv-hello { padding: 22px; }
    .sv-hello-title { font-size: 26px; }
    .sv-hello-cta .sv-btn { flex: 1 1 auto; }
    .sv-card { padding: 18px; }
    .sv-pipe { gap: 2px; padding-top: 14px; }
    .sv-pcol { min-height: 210px; padding: 10px 2px; gap: 8px; }
    .sv-prism { width: 30px; margin-right: 10px; }
    .sv-prism-s { left: 30px; width: 10px; }
    .sv-prism-t { top: -10px; width: 30px; height: 10px; }
    .sv-pcol-n { font-size: 20px; }
    .sv-pcol-lbl { font-size: 10px; letter-spacing: .02em; }
    .sv-urgent-row { flex-wrap: wrap; }
    .sv-urgent-row .sv-btn { margin-left: 52px; }
}
@media (prefers-reduced-motion: reduce) {
    .sv-in, .sv-float, .sv-coin, .sv-prism, .sv-bar span, .sv-toast { animation: none !important; }
    .kpi:hover, .sv-row-3 .quick-btn:hover, .sv-pcol:hover, .sb-nav .sb-item:hover { transform: none !important; }
    .sv-stage { transition: none; }
}
</style>
