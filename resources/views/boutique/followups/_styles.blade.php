{{-- Styles des pages Relances (en plus des composants cp-* des pages Campagnes et du thème sv-theme). --}}
@include('boutique.campaigns._styles')
<style>
.fu-h2 { margin: 0; font-size: 18px; font-weight: 800; }
.fu-help { font-size: 13px; color: var(--text-2); line-height: 1.45; }
.fu-list { display: grid; gap: 12px; }
.fu-item { padding: 18px 20px; display: flex; gap: 16px; align-items: center; }
.fu-item-main { flex: 1; min-width: 0; display: grid; gap: 4px; }
.fu-item-name { font-weight: 700; font-size: 16px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.fu-sum { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
.fu-sum span { font-size: 12px; font-weight: 600; padding: 3px 9px; border-radius: 999px; background: var(--bg); color: var(--text-2); box-shadow: inset 0 0 0 1px var(--border); }
.fu-item-act { display: flex; gap: 10px; align-items: center; flex: none; }
.fu-item-act form { margin: 0; }
.fu-switch { position: relative; width: 52px; height: 30px; border-radius: 999px; border: 0; cursor: pointer; background: var(--border); transition: background-color .18s; flex: none; }
.fu-switch::after { content: ''; position: absolute; top: 3px; left: 3px; width: 24px; height: 24px; border-radius: 999px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .18s; }
.fu-switch[aria-checked="true"] { background: var(--sv-blue); }
.fu-switch[aria-checked="true"]::after { transform: translateX(22px); }
.fu-switch:focus-visible { outline: 3px solid var(--sv-blue); outline-offset: 2px; }
.fu-tag-vendor { font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 999px; background: var(--sv-amb50); color: var(--sv-amb-ink); }

/* Formulaire */
.fu-form { display: grid; gap: 16px; }
.fu-sec { padding: 20px; display: grid; gap: 14px; }
.fu-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
.fu-field { display: grid; gap: 6px; min-width: 0; }
.fu-field label, .fu-label { font-size: 13px; font-weight: 700; }
.fu-input, .fu-select, .fu-textarea { width: 100%; min-height: 44px; padding: 0 14px; border-radius: 12px; border: 0; background: var(--surface); color: var(--text);
    box-shadow: inset 0 0 0 1.5px var(--border); font: 500 15px var(--font); }
.fu-textarea { padding: 12px 14px; min-height: 120px; resize: vertical; line-height: 1.5; }
.fu-input:focus, .fu-select:focus, .fu-textarea:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue); }
.fu-pair { display: flex; gap: 8px; }
.fu-pair .fu-input { flex: 1; min-width: 0; font-variant-numeric: tabular-nums; }
.fu-pair .fu-select { flex: 1; }
.fu-pills { display: flex; gap: 8px; flex-wrap: wrap; }
.fu-pill { position: relative; }
.fu-pill input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
.fu-pill span { display: inline-flex; align-items: center; min-height: 40px; padding: 0 16px; border-radius: 999px; font-size: 14px; font-weight: 600;
    background: var(--surface); box-shadow: inset 0 0 0 1.5px var(--border); color: var(--text-2); }
.fu-pill input:checked + span { background: var(--sv-b50); color: var(--sv-btn); box-shadow: inset 0 0 0 1.5px var(--sv-btn); }
.fu-pill input:focus-visible + span { outline: 3px solid var(--sv-blue); outline-offset: 2px; }
.fu-pill input:disabled + span { opacity: .5; }
.fu-vars { display: flex; gap: 6px; flex-wrap: wrap; }
.fu-var { min-height: 32px; padding: 0 10px; border-radius: 8px; border: 0; background: var(--sv-b50); color: var(--sv-btn); font: 600 12.5px var(--font); cursor: pointer; }
.fu-var:hover { background: var(--sv-b100); }
.fu-ai { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.fu-ai .fu-input { flex: 1 1 200px; min-height: 40px; font-size: 14px; }
.fu-status { font-size: 13px; font-weight: 600; color: var(--text-2); }
.fu-status.err { color: var(--sv-err); }
.fu-status.ok { color: var(--sv-btn); }
.fu-preview { padding: 14px 16px; border-radius: 16px 16px 16px 4px; background: var(--sv-b50); color: var(--text); font-size: 14.5px; line-height: 1.5; white-space: pre-line; overflow-wrap: anywhere; max-width: 520px; }
.fu-preview-lbl { font-size: 12px; font-weight: 700; color: var(--text-2); text-transform: uppercase; letter-spacing: .05em; }
.fu-checks { display: flex; flex-wrap: wrap; gap: 8px; }
.fu-err { color: var(--sv-err); font-size: 13px; font-weight: 600; }
.fu-save { position: sticky; bottom: 0; z-index: 5; display: flex; gap: 10px; justify-content: flex-end; padding: 12px 0 calc(12px + env(safe-area-inset-bottom, 0px)); background: linear-gradient(to top, var(--bg) 70%, transparent); }

/* Historique */
.fu-st-sub { display: block; font-size: 12px; color: var(--text-2); margin-top: 3px; }
@media (max-width: 760px) {
    .fu-item { padding: 16px; align-items: flex-start; }
    .fu-item-act { flex-direction: column-reverse; align-items: flex-end; }
    .fu-sec { padding: 16px; }
    .fu-save .cp-btn { flex: 1; }
}
</style>
