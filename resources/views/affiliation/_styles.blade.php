{{-- Styles du tableau de bord de parrainage (en plus des composants cp-* des pages Campagnes et du thème sv-theme). --}}
@include('boutique.campaigns._styles')
<style>
.af-h2 { margin: 0; font-size: 18px; font-weight: 800; letter-spacing: -.01em; text-wrap: balance; }
.af-lead { margin: 0; font-size: 15px; line-height: 1.55; color: var(--text-2); max-width: 62ch; }
.af-lead strong { color: var(--text); }
.af-note { display: block; font-size: 12.5px; color: var(--muted); line-height: 1.45; }

.af-share { padding: 24px; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 24px; align-items: start; }
.af-share-main { display: grid; gap: 14px; min-width: 0; }
.af-code { display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; }
.af-code-lbl { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-2); }
.af-code-val { font-size: 28px; font-weight: 800; letter-spacing: .08em; color: var(--sv-btn); font-variant-numeric: tabular-nums; }
.af-link { display: flex; gap: 10px; }
.af-link-input { flex: 1; min-width: 0; min-height: 44px; padding: 0 16px; border-radius: 999px; border: 0; background: var(--bg); color: var(--text);
    box-shadow: inset 0 0 0 1.5px var(--border); font: 600 14px var(--font); }
.af-link-input:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue); }
.af-share-btns { display: flex; flex-wrap: wrap; gap: 10px; }
.af-qr { display: grid; gap: 10px; justify-items: center; text-align: center; width: 220px; }
.af-qr-img { width: 200px; height: 200px; padding: 10px; border-radius: 16px; background: #fff; box-shadow: inset 0 0 0 1px var(--border); }
.af-qr-img svg { width: 100%; height: 100%; display: block; }

.af-money { padding: 0 0 20px; }
.af-money-hd { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 20px 24px; border-bottom: 1px solid var(--border); }
.af-money-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
.af-money-cell { padding: 20px 24px; display: grid; gap: 6px; align-content: start; }
.af-money-cell + .af-money-cell { border-left: 1px solid var(--border); }
.af-money-cell.is-main { background: var(--sv-b50); }
.af-money-lbl { font-size: 13px; font-weight: 600; color: var(--text-2); }
.af-money-val { font-size: 24px; font-weight: 800; overflow-wrap: anywhere; }
.af-money-cell.is-main .af-money-val { color: var(--sv-btn); }
.af-payout { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; padding: 4px 24px 0; }
.af-payout-msg { margin: 4px 24px 0; }
.af-field { display: grid; gap: 6px; flex: 1 1 180px; }
.af-field label { font-size: 13px; font-weight: 700; }
.af-input { min-height: 44px; padding: 0 14px; border-radius: 12px; border: 0; background: var(--surface); color: var(--text);
    box-shadow: inset 0 0 0 1.5px var(--border); font: 500 15px var(--font); width: 100%; }
.af-input:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue); }
.af-payout-btn { flex: 0 0 auto; }

.af-history { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; align-items: start; }
.af-list { list-style: none; margin: 0; padding: 0; }
.af-list li { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 24px; }
.af-list li + li { border-top: 1px solid var(--border); }
.af-list li > div { display: grid; gap: 2px; min-width: 0; }
.af-cap { padding: 0 24px 16px; }

.af-wait { padding: 24px; display: flex; gap: 18px; align-items: flex-start; background: var(--sv-amb50); border-color: transparent; }
.af-wait-ico { flex: none; width: 52px; height: 52px; border-radius: 16px; background: var(--surface); color: var(--sv-amb-ink); display: flex; align-items: center; justify-content: center; }
.af-wait .af-lead { margin-top: 6px; color: var(--text); }
.af-how { padding: 24px; display: grid; gap: 16px; }
.af-steps { margin: 0; padding: 0; list-style: none; counter-reset: af; display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; }
.af-steps li { counter-increment: af; display: grid; gap: 4px; align-content: start; padding-left: 44px; position: relative; font-size: 14px; }
.af-steps li::before { content: counter(af); position: absolute; left: 0; top: 0; width: 32px; height: 32px; border-radius: 999px;
    background: var(--sv-b50); color: var(--sv-btn); font-weight: 800; display: flex; align-items: center; justify-content: center; }
.af-steps span { color: var(--text-2); line-height: 1.45; }

@media (max-width: 760px) {
    .af-share { grid-template-columns: 1fr; padding: 18px; }
    .af-qr { width: 100%; }
    .af-code-val { font-size: 24px; }
    .af-link { flex-direction: column; }
    .af-share-btns .cp-btn { flex: 1 1 100%; }
    .af-money-hd { padding: 16px; }
    .af-money-grid { grid-template-columns: 1fr; }
    .af-money-cell { padding: 14px 16px; }
    .af-money-cell + .af-money-cell { border-left: 0; border-top: 1px solid var(--border); }
    .af-payout { padding: 8px 16px 0; }
    .af-payout-msg { margin: 8px 16px 0; }
    .af-payout-btn { width: 100%; }
    .af-list li { padding: 12px 16px; }
    .af-cap { padding: 0 16px 14px; }
    .af-how { padding: 18px; }
}
</style>
