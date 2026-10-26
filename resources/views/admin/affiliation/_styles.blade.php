{{-- Styles des pages Affiliation de l'équipe : ceux des pages Campagnes (ac-*, cp-*) + quelques compléments. --}}
@include('admin.campaigns._styles')
<style>
.af-badge { display: inline-block; min-width: 20px; padding: 1px 7px; margin-left: 4px; border-radius: 999px; background: var(--sv-amber); color: #16162A; font-size: 11px; font-weight: 800; text-align: center; }
.af-sec { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
.af-sec h2 { margin: 0; font-size: 18px; font-weight: 800; }
.af-inline { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; margin: 0; }
.af-inline .ac-input, .af-inline .ac-select { min-height: 38px; }
.af-rate { width: 84px; font-variant-numeric: tabular-nums; }
.af-form { padding: 22px; display: grid; gap: 16px; }
.af-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
.af-grid .ac-input, .af-grid .ac-select { width: 100%; }
.af-check { display: flex; gap: 10px; align-items: center; font-size: 14px; font-weight: 600; cursor: pointer; }
.af-check input { width: 20px; height: 20px; margin: 0; accent-color: var(--sv-btn); }
.af-flag { display: block; margin-top: 4px; font-size: 12.5px; font-weight: 700; color: var(--sv-err); }
.af-actions { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; }
.af-actions form { margin: 0; }
.af-actions .ac-input { min-height: 38px; width: 160px; }
.af-code { font-weight: 800; letter-spacing: .05em; color: var(--sv-btn); }
.af-kpis3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
@media (max-width: 760px) { .af-kpis3 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
details.af-new > summary { list-style: none; cursor: pointer; }
details.af-new > summary::-webkit-details-marker { display: none; }
@media (max-width: 760px) {
    .af-inline, .af-actions { justify-content: stretch; }
    .af-inline > *, .af-actions > *, .af-actions .ac-input { flex: 1 1 100%; width: 100%; }
    .af-form { padding: 16px; }
}
</style>
