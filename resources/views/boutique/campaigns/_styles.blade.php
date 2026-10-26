{{-- Styles communs des pages Campagnes (maquette « Mes campagnes »), couleurs du thème vendeur (sv-theme). --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; min-height: 100vh; -webkit-font-smoothing: antialiased; }
.cp-num { font-variant-numeric: tabular-nums; }

/* ── En-tête de page ── */
.cp-top { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; position: sticky; top: 0; z-index: 30; padding: 14px 28px !important; }
.cp-top .tb-info { flex: 1 1 240px; min-width: 0; }
.cp-title { display: flex; align-items: center; gap: 10px; margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: var(--text); }
.cp-title svg { color: var(--sv-blue); }
.cp-back { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--text-2); text-decoration: none; }
.cp-back:hover { color: var(--sv-btn); }

.cp-content { padding: 24px 28px 64px; display: flex; flex-direction: column; gap: 24px; max-width: 1180px; width: 100%; min-width: 0; }
.cp-content > * { min-width: 0; }
.cp-flash { border-radius: 16px; padding: 14px 18px; font-size: 14px; font-weight: 700; }
.cp-flash-ok { background: var(--sv-ok50); color: var(--sv-ok); }
.cp-flash-err { background: var(--sv-err50); color: var(--sv-err); }
.cp-flash-info { background: var(--sv-b50); color: var(--sv-btn); }
.cp-flash ul { margin: 6px 0 0; padding-left: 18px; font-weight: 600; }

/* ── Boutons ── */
.cp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 20px; border-radius: 999px; border: 0; font: 700 15px/1 var(--font); text-decoration: none; cursor: pointer; white-space: nowrap; transition: transform 180ms var(--sv-e-out), background-color 180ms, box-shadow 180ms, color 180ms; }
.cp-btn:active { transform: scale(.97); }
.cp-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
.cp-btn-blue { background: var(--sv-btn); color: #fff; }
.cp-btn-blue:hover:not(:disabled) { background: var(--sv-btn-h); color: #fff; box-shadow: 0 8px 20px rgba(22,104,216,.28); }
.cp-btn-line { background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1.5px var(--border); }
.cp-btn-line:hover:not(:disabled) { box-shadow: inset 0 0 0 1.5px var(--sv-blue); color: var(--sv-btn); }
.cp-btn-danger { background: var(--surface); color: var(--sv-err); box-shadow: inset 0 0 0 1.5px var(--sv-err50); }
.cp-btn-danger:hover { box-shadow: inset 0 0 0 1.5px var(--sv-err); }
.cp-btn-sm { min-height: 38px; padding: 0 14px; font-size: 13px; }
.cp-btn:focus-visible, .cp-pill:focus-visible, .cp-choice:focus-within { outline: 3px solid var(--sv-blue); outline-offset: 2px; }

/* ── Cartes et indicateurs ── */
.cp-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; }
.cp-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.cp-kpi { padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
.cp-kpi-lbl { font-size: 13px; color: var(--text-2); font-weight: 500; }
.cp-kpi-val { font-size: 30px; font-weight: 800; margin-top: 8px; letter-spacing: -.01em; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
.cp-kpi-val small { font-size: 15px; font-weight: 600; color: var(--text-2); }

.cp-types { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; }
.cp-type { padding: 24px; display: flex; gap: 20px; align-items: center; }
.cp-type-ico { flex: none; width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.cp-type-ico.blue { background: var(--sv-b50); color: var(--sv-btn); }
.cp-type-ico.amber { background: var(--sv-amb50); color: var(--sv-amb-ink); }
.cp-type-name { font-weight: 700; font-size: 17px; }
.cp-type-desc { font-size: 14px; color: var(--text-2); margin-top: 4px; line-height: 1.45; }
.cp-type-price { font-size: 14px; font-weight: 700; color: var(--sv-btn); margin-top: 8px; }
.cp-soon { display: inline-block; font-size: 11px; font-weight: 800; padding: 3px 9px; border-radius: 999px; background: var(--sv-amb50); color: var(--sv-amb-ink); margin-left: 6px; vertical-align: 2px; }

/* ── Pastilles de statut ── */
.cp-st { display: inline-block; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; white-space: nowrap; }
.cp-st.ok { background: var(--sv-ok50); color: var(--sv-ok); }
.cp-st.warn { background: var(--sv-amb50); color: var(--sv-amb-ink); }
.cp-st.info { background: var(--sv-b50); color: var(--sv-btn); }
.cp-st.err { background: var(--sv-err50); color: var(--sv-err); }
.cp-st.muted { background: color-mix(in srgb, var(--muted) 14%, transparent); color: var(--text-2); }

/* ── Liste des campagnes ── */
.cp-list-hd { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 20px 24px; border-bottom: 1px solid var(--border); }
.cp-list-hd h2 { margin: 0; font-size: 18px; font-weight: 700; }
.cp-pills { display: flex; gap: 8px; flex-wrap: wrap; }
.cp-pill { display: inline-flex; align-items: center; min-height: 36px; padding: 0 14px; border-radius: 999px; font-size: 13px; font-weight: 600; color: var(--text-2); text-decoration: none; box-shadow: inset 0 0 0 1px var(--border); background: var(--surface); }
.cp-pill[aria-current="page"] { background: var(--sv-b50); color: var(--sv-btn); box-shadow: inset 0 0 0 1px var(--sv-btn); }
.cp-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.cp-table th { text-align: left; color: var(--text-2); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; font-weight: 600; padding: 12px 16px; }
.cp-table th:first-child, .cp-table td:first-child { padding-left: 24px; }
.cp-table th:last-child, .cp-table td:last-child { padding-right: 24px; text-align: right; }
.cp-table td { padding: 16px; border-top: 1px solid var(--border); vertical-align: middle; }
.cp-table tr.is-todo td { background: color-mix(in srgb, var(--sv-amber) 7%, var(--surface)); }
.cp-row-name { font-weight: 600; overflow-wrap: anywhere; }
.cp-row-sub { font-size: 12px; color: var(--text-2); margin-top: 2px; }
.cp-link { font-weight: 700; color: var(--sv-btn); text-decoration: none; }
.cp-link:hover { text-decoration: underline; text-underline-offset: 3px; }
.cp-empty { padding: 40px 24px; text-align: center; display: grid; gap: 10px; justify-items: center; color: var(--text-2); }
.cp-empty strong { color: var(--text); font-size: 16px; }
.cp-pagination { padding: 16px 24px; }
/* ── Nouveautés (notifications non lues) ── */
.cp-news { overflow: hidden; }
.cp-news-hd { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 16px 24px; border-bottom: 1px solid var(--border); }
.cp-news-hd h2 { margin: 0; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
.cp-news-hd form { margin: 0; }
.cp-news-n { font-size: 12px; font-weight: 800; padding: 2px 8px; border-radius: 999px; background: var(--sv-amber); color: #16162A; }
.cp-news-all { background: none; border: 0; padding: 0; font: 700 13px var(--font); cursor: pointer; }
.cp-news-list { list-style: none; margin: 0; padding: 0; }
.cp-news-list li + li { border-top: 1px solid var(--border); }
.cp-news-item { display: flex; gap: 12px; align-items: flex-start; padding: 14px 24px; text-decoration: none; color: inherit; }
.cp-news-item:hover { background: var(--bg); }
.cp-news-item:focus-visible { outline: 3px solid var(--sv-blue); outline-offset: -3px; }
.cp-news-dot { flex: none; width: 8px; height: 8px; border-radius: 999px; background: var(--sv-blue); margin-top: 7px; }
.cp-news-title { display: block; font-weight: 700; font-size: 14.5px; }
.cp-news-body { display: block; font-size: 13.5px; color: var(--text-2); margin-top: 2px; line-height: 1.45; overflow-wrap: anywhere; }
.cp-news-time { display: block; font-size: 12px; color: var(--muted); margin-top: 4px; }
.cp-news-act { flex: none; font-size: 13px; font-weight: 700; color: var(--sv-btn); white-space: nowrap; align-self: center; }
@media (max-width: 760px) {
    .cp-news-hd, .cp-news-item { padding-inline: 16px; }
    .cp-news-act { display: none; }
}
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

@media (max-width: 760px) {
    .cp-top { padding: 12px 16px !important; }
    .cp-content { padding: 18px 16px 96px; gap: 18px; }
    .cp-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .cp-kpi { padding: 16px; }
    .cp-kpi-val { font-size: 22px; }
    .cp-type { padding: 18px; gap: 14px; align-items: flex-start; }
    .cp-type-ico { width: 46px; height: 46px; }
    .cp-list-hd { padding: 16px; }
    /* Tableau → cartes */
    .cp-table thead { display: none; }
    .cp-table, .cp-table tbody, .cp-table tr, .cp-table td { display: block; width: 100%; }
    .cp-table tr { border-top: 1px solid var(--border); padding: 14px 16px; display: grid; grid-template-columns: 1fr auto; gap: 6px 12px; }
    .cp-table td { border: 0; padding: 0 !important; text-align: left !important; }
    .cp-table td[data-label]::before { content: attr(data-label) " · "; color: var(--text-2); font-size: 12px; }
    .cp-table td.cp-cell-main { grid-column: 1 / 2; }
    .cp-table td.cp-cell-st { grid-column: 2; grid-row: 1; justify-self: end; }
    .cp-table td.cp-cell-act { grid-column: 1 / -1; margin-top: 6px; }
    .cp-table td.cp-cell-act .cp-btn { width: 100%; }
}
@media (prefers-reduced-motion: reduce) { .cp-btn { transition: none; } }
</style>
