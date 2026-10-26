{{-- Styles des pages Campagnes de l'équipe Shopio : composants des pages vendeur (cp-*) + barre du haut. --}}
@include('boutique._partials.sv-theme')
@include('boutique.campaigns._styles')
<style>
.ac-bar { position: sticky; top: env(safe-area-inset-top, 0px); z-index: 40; background: var(--sv-night); color: #fff; }
.ac-bar-in { max-width: 1240px; margin: 0 auto; display: flex; align-items: center; gap: 12px 24px; flex-wrap: wrap;
    padding: 12px max(16px, env(safe-area-inset-right, 0px)) 12px max(16px, env(safe-area-inset-left, 0px)); }
.ac-brand { display: inline-flex; align-items: center; gap: 10px; color: #fff; text-decoration: none; font-size: 16px; }
.ac-brand-sub { color: var(--sv-sky); font-weight: 600; }
.ac-logo { width: 34px; height: 34px; border-radius: 10px; background: var(--sv-blue); display: inline-flex; align-items: center; justify-content: center; }
.ac-nav { display: flex; gap: 4px; flex-wrap: wrap; }
.ac-nav a { color: rgba(255,255,255,.78); text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 14px; border-radius: 999px; }
.ac-nav a:hover { color: #fff; background: rgba(255,255,255,.08); }
.ac-nav a[aria-current="page"] { background: var(--sv-night2); color: #fff; box-shadow: inset 0 0 0 1px rgba(125,180,255,.35); }
.ac-nav a:focus-visible, .ac-out button:focus-visible { outline: 3px solid var(--sv-sky); outline-offset: 2px; }
.ac-out { margin: 0 0 0 auto; display: flex; align-items: center; gap: 12px; }
.ac-me { font-size: 13px; color: rgba(255,255,255,.7); max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ac-out button { background: none; border: 1px solid rgba(255,255,255,.25); color: #fff; border-radius: 999px; padding: 7px 14px; font: 600 13px var(--font); cursor: pointer; }
.ac-dark { width: 36px; height: 36px; flex-shrink: 0; border-radius: 999px; border: 1px solid rgba(255,255,255,.25); background: none; color: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
.ac-dark:hover { background: rgba(255,255,255,.1); }
.ac-dark:focus-visible { outline: 3px solid var(--sv-sky); outline-offset: 2px; }
[data-theme="dark"] .ac-bar { background: #07152B; border-bottom: 1px solid #1C3560; }
.ac-page { max-width: 1240px; margin: 0 auto; padding: 24px max(16px, env(safe-area-inset-right, 0px)) 64px max(16px, env(safe-area-inset-left, 0px)); display: flex; flex-direction: column; gap: 22px; }
.ac-page > * { min-width: 0; }
.ac-h1 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -.02em; }
.ac-sub { margin: 4px 0 0; color: var(--text-2); font-size: 14px; }
.ac-alerts { display: flex; flex-wrap: wrap; gap: 10px; }
.ac-alert { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 14px; background: var(--sv-amb50); color: var(--sv-amb-ink); font-size: 14px; font-weight: 700; text-decoration: none; }
.ac-alert strong { font-size: 16px; font-variant-numeric: tabular-nums; }
.ac-tools { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.ac-input, .ac-select, .ac-textarea { min-height: 42px; padding: 0 14px; border-radius: 12px; border: 0; box-shadow: inset 0 0 0 1.5px var(--border); background: var(--surface); color: var(--text); font: 500 14px var(--font); }
.ac-textarea { padding: 10px 14px; min-height: 84px; width: 100%; resize: vertical; }
.ac-input:focus, .ac-select:focus, .ac-textarea:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue); }
.ac-field { display: grid; gap: 6px; }
.ac-field label, .ac-label { font-size: 13px; font-weight: 700; }
.ac-help { font-size: 12.5px; color: var(--text-2); }
.ac-err { color: var(--sv-err); font-size: 13px; font-weight: 600; }
@media (max-width: 760px) {
    .ac-out { margin-left: 0; width: 100%; justify-content: space-between; }
    .ac-page { padding-top: 18px; gap: 18px; }
    .ac-h1 { font-size: 20px; }
    .ac-tools > * { flex: 1 1 100%; }
    .ac-page .cp-kpis > :last-child:nth-child(odd) { grid-column: 1 / -1; }
}
</style>
