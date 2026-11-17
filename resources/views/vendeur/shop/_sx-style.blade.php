{{-- Styles des éditeurs de sections (Boutique → Sections, éditeur produit) : variables --sx-*, champs, listes. --}}
<style>
:root {
    --sx-blue: #1668D8; --sx-blue-h: #1260C9; --sx-b50: #EAF2FF; --sx-night: #0B1E3D;
    --sx-bg: #F6F9FF; --sx-surf: #FFFFFF; --sx-line: #E3E8F2; --sx-soft: #F1F5FB; --sx-stage: #DCE6F5;
    --sx-ink: #16162A; --sx-ink2: #45455E; --sx-ink3: #62627C; --sx-ok: #15803D; --sx-ok50: #E7F6EC; --sx-err: #B91C1C; --sx-amb: #F59E0B; --sx-ambi: #92400E;
    --sx-font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; --sx-out: cubic-bezier(.22,1,.36,1);
}
[data-theme="dark"] {
    --sx-b50: #13284B; --sx-bg: #0A1426; --sx-surf: #10203B; --sx-line: #22355A; --sx-soft: #0D1A31; --sx-stage: #071022;
    --sx-ink: #EAF0FA; --sx-ink2: #C3CEE0; --sx-ink3: #9DAAC2; --sx-ok: #4ADE80; --sx-ok50: #0F2A1C; --sx-err: #F87171; --sx-ambi: #FBBF24;
}
#pwaModal, #pushNotifBanner { display: none !important; }
html, body { background: var(--sx-bg); }
.sx { font-family: var(--sx-font); color: var(--sx-ink); font-size: 14.5px; }
.sx *, .sx *::before, .sx *::after { box-sizing: border-box; }
.sx :focus-visible { outline: 3px solid #7DB4FF; outline-offset: 2px; }
.sx-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
.sx-top { background: var(--sx-night); color: #fff; }
.sx-top-in { max-width: 1500px; margin: 0 auto; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.sx-top h1 { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
.sx-top p { margin: 4px 0 0; color: #C9D6EA; font-size: 13.5px; }
.sx-links { display: flex; gap: 8px; flex-wrap: wrap; }
.sx-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 0 14px; border-radius: 11px; border: 1px solid var(--sx-line); background: var(--sx-surf); color: var(--sx-ink); font: 700 13.5px var(--sx-font); text-decoration: none; cursor: pointer; transition: background-color 160ms, border-color 160ms, transform 160ms var(--sx-out); }
.sx-btn:hover { border-color: #B9C7DD; }
.sx-btn:active { transform: scale(.98); }
.sx-btn:disabled { opacity: .5; cursor: not-allowed; }
.sx-btn-main { background: var(--sx-blue); border-color: var(--sx-blue); color: #fff; }
.sx-btn-main:hover { background: var(--sx-blue-h); border-color: var(--sx-blue-h); }
.sx-btn-sm { min-height: 34px; padding: 0 11px; font-size: 12.5px; border-radius: 9px; }
.sx-btn-danger { color: var(--sx-err); }
.sx-top .sx-btn { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); color: #fff; }
.sx-top .sx-btn:hover { background: rgba(255,255,255,.16); }

.sx-layout { max-width: 1500px; margin: 0 auto; padding: 18px 16px 110px; display: grid; grid-template-columns: minmax(0, 440px) minmax(0, 1fr); gap: 18px; align-items: start; }
.sx-layout > *, .sx-panel > *, .sx-list > *, .sx-add > *, .sx-add-grid > *, .sx-body > * { min-width: 0; }
.sx-panel { display: grid; gap: 12px; }
.sx-themes { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; padding: 5px; border-radius: 14px; background: var(--sx-surf); border: 1px solid var(--sx-line); }
.sx-themes a { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 2px 6px; min-height: 44px; padding: 4px 6px; text-align: center; font-size: 13px; border-radius: 10px; text-decoration: none; color: var(--sx-ink2); font-weight: 700; }
.sx-themes a[aria-current="page"] { background: var(--sx-night); color: #fff; }
.sx-themes small { font-size: 10.5px; padding: 2px 7px; border-radius: 999px; background: var(--sx-ok50); color: var(--sx-ok); font-weight: 800; }
.sx-hint { margin: 0; color: var(--sx-ink3); font-size: 13px; line-height: 1.5; }

.sx-list { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
.sx-item { background: var(--sx-surf); border: 1px solid var(--sx-line); border-radius: 14px; transition: box-shadow 200ms, border-color 200ms, opacity 200ms; }
.sx-item.is-open { border-color: var(--sx-blue); box-shadow: 0 0 0 3px var(--sx-b50); }
.sx-item.is-off .sx-item-name { opacity: .5; }
.sx-item.is-drag { opacity: .4; }
.sx-item.is-over { box-shadow: 0 -3px 0 var(--sx-blue); }
.sx-item-hd { display: flex; align-items: center; gap: 6px; padding: 8px 8px 8px 6px; }
.sx-grip { width: 26px; height: 36px; display: grid; place-items: center; color: var(--sx-ink3); cursor: grab; flex-shrink: 0; }
.sx-item-name { flex: 1; min-width: 0; border: 0; background: none; padding: 6px 4px; text-align: left; cursor: pointer; color: var(--sx-ink); font-family: var(--sx-font); }
.sx-item-name b { display: flex; align-items: center; gap: 6px; font-size: 14px; }
.sx-item-name span { display: block; font-size: 12px; color: var(--sx-ink3); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sx-tag { font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 999px; background: var(--sx-b50); color: var(--sx-blue); letter-spacing: .03em; }
.sx-icon { width: 34px; height: 34px; border: 0; border-radius: 9px; background: none; color: var(--sx-ink2); display: grid; place-items: center; cursor: pointer; flex-shrink: 0; }
.sx-icon:hover { background: var(--sx-soft); }
.sx-icon:disabled { opacity: .3; cursor: default; background: none; }
.sx-body { display: grid; gap: 12px; padding: 4px 14px 16px; border-top: 1px solid var(--sx-line); padding-top: 14px; }
.sx-lbl { display: block; font-weight: 700; font-size: 12.5px; margin-bottom: 5px; color: var(--sx-ink2); }
.sx-input, .sx-select, .sx-area { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid var(--sx-line); border-radius: 10px; background: var(--sx-surf); color: var(--sx-ink); font: 500 14px var(--sx-font); }
.sx-area { min-height: 90px; resize: vertical; line-height: 1.5; }
.sx-input:focus, .sx-select:focus, .sx-area:focus { outline: none; border-color: var(--sx-blue); box-shadow: 0 0 0 3px var(--sx-b50); }
.sx-count { display: block; text-align: right; font-size: 11.5px; color: var(--sx-ink3); margin-top: 3px; }
.sx-seg { display: inline-flex; gap: 4px; padding: 4px; border-radius: 10px; background: var(--sx-soft); }
.sx-seg button { min-height: 32px; padding: 0 12px; border: 0; border-radius: 7px; background: none; color: var(--sx-ink2); font: 700 12.5px var(--sx-font); cursor: pointer; }
.sx-seg button[aria-pressed="true"] { background: var(--sx-surf); color: var(--sx-ink); box-shadow: 0 1px 3px rgba(0,0,0,.12); }
.sx-img { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.sx-img i { width: 88px; height: 62px; border-radius: 10px; background: var(--sx-soft) center / cover; border: 1px solid var(--sx-line); }
.sx-pair { display: grid; gap: 6px; padding: 10px; border-radius: 12px; background: var(--sx-soft); }
.sx-pair-hd { display: flex; justify-content: space-between; align-items: center; font-size: 12px; font-weight: 800; color: var(--sx-ink3); }
.sx-msg { font-size: 12.5px; font-weight: 600; color: var(--sx-ink3); }
.sx-msg.is-err { color: var(--sx-err); } .sx-msg.is-ok { color: var(--sx-ok); }

.sx-add { border: 2px dashed #B9C7DD; border-radius: 14px; padding: 14px; display: grid; gap: 10px; background: transparent; }
.sx-tabs { display: flex; gap: 4px; flex-wrap: wrap; }
.sx-tabs button { min-height: 32px; padding: 0 11px; border: 0; border-radius: 999px; background: var(--sx-soft); color: var(--sx-ink2); font: 700 12.5px var(--sx-font); cursor: pointer; }
.sx-tabs button[aria-pressed="true"] { background: var(--sx-night); color: #fff; }
.sx-warn { margin: 0; padding: 10px 12px; border-radius: 12px; background: #FEF3E0; color: #92400E; font-size: 12.5px; line-height: 1.5; font-weight: 600; }
.sx-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px; align-items: center; }
.sx-check { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: var(--sx-ink2); cursor: pointer; }
.sx-check input { width: 18px; height: 18px; accent-color: var(--sx-blue); }
.sx-iconpick { display: flex; gap: 4px; flex-wrap: wrap; }
.sx-iconpick button { width: 34px; height: 34px; border: 1px solid var(--sx-line); border-radius: 9px; background: var(--sx-surf); color: var(--sx-ink2); display: grid; place-items: center; cursor: pointer; }
.sx-iconpick button[aria-pressed="true"] { border-color: var(--sx-blue); background: var(--sx-b50); color: var(--sx-blue); }
.sx-mini { display: flex; align-items: center; gap: 8px; }
.sx-mini i { width: 46px; height: 46px; border-radius: 10px; background: var(--sx-surf) center / cover; border: 1px solid var(--sx-line); flex-shrink: 0; }
.sx-add-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.sx-add-grid button { display: grid; gap: 4px; padding: 12px; border: 1px solid var(--sx-line); border-radius: 12px; background: var(--sx-surf); text-align: left; cursor: pointer; font-family: var(--sx-font); color: var(--sx-ink); transition: border-color 160ms, transform 160ms var(--sx-out); }
.sx-add-grid button:hover { border-color: var(--sx-blue); transform: translateY(-1px); }
.sx-add-grid b { font-size: 13.5px; } .sx-add-grid span { font-size: 12px; color: var(--sx-ink3); line-height: 1.4; }

.sx-preview { position: sticky; top: 78px; display: grid; gap: 10px; }
.sx-preview-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.sx-stage { border-radius: 18px; background: var(--sx-stage); padding: 14px; display: flex; justify-content: center; min-width: 0; height: calc(100vh - 170px); min-height: 520px; overflow: hidden; }
.sx-frame { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 40px -24px rgba(11,30,61,.5); position: relative; height: 100%; }
.sx-frame.is-phone { width: min(390px, 100%); }
.sx-frame.is-desk { width: 100%; }
.sx-frame iframe { border: 0; display: block; background: #fff; }
.sx-frame.is-phone iframe { width: 100%; height: 100%; }
.sx-frame.is-desk iframe { width: 1280px; transform-origin: 0 0; }
.sx-loading { position: absolute; inset: 0; display: grid; place-items: center; background: rgba(255,255,255,.7); font-weight: 700; color: var(--sx-ink2); opacity: 0; pointer-events: none; transition: opacity 200ms; }
.sx-frame.is-loading .sx-loading { opacity: 1; }

.sx-bar { position: fixed; left: 0; right: 0; bottom: 0; z-index: 900; padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px)); background: rgba(255,255,255,.94); backdrop-filter: blur(8px); border-top: 1px solid var(--sx-line); }
[data-theme="dark"] .sx-bar { background: rgba(10,20,38,.94); }
.sx-bar-in { max-width: 1500px; margin: 0 auto; display: flex; align-items: center; gap: 12px; }
.sx-bar-in p { flex: 1; margin: 0; font-size: 13.5px; font-weight: 600; color: var(--sx-ink3); }
.sx-bar-in p.is-dirty { color: var(--sx-ambi); }
.sx-bar-in p.is-err { color: var(--sx-err); }

@media (max-width: 1100px) {
    .sx-layout { grid-template-columns: minmax(0, 1fr); }
    .sx-preview { position: static; }
    .sx-stage { height: 640px; min-height: 0; }
}
@media (max-width: 560px) {
    .sx-add-grid { grid-template-columns: minmax(0, 1fr); }
    .sx-bar-in p { font-size: 12.5px; }
    .sx-stage { padding: 8px; height: 560px; }
}
/* Shopio IA : rédiger une section, ou toutes les sections */
.sx-ai { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px 12px; border-radius: 12px; background: var(--sx-b50, #EAF2FF); }
.sx-ai .sx-msg { flex: 1 1 180px; line-height: 1.45; }
.sx-ai-btn { background: var(--sx-blue, #1668D8); border: 0; color: #fff; }
.sx-ai-btn:hover { background: var(--sx-blue-h, #1260C9); }
.sx-ai-btn.is-busy { opacity: .7; cursor: progress; }
.sx-aibox { display: grid; gap: 8px; padding: 12px; border-radius: 14px; background: var(--sx-b50, #EAF2FF); }
.sx-aibox p { margin: 0; font-size: 12.5px; color: var(--sx-ink2); line-height: 1.45; }
.sx-aibox p.is-err { color: var(--sx-err); font-weight: 700; } .sx-aibox p.is-ok { color: var(--sx-ok); font-weight: 700; }
.sx-aimg { display: grid; gap: 8px; margin-top: 10px; padding: 12px; border-radius: 12px; background: var(--sx-b50, #EAF2FF); }
.sx-aimg > b { display: flex; align-items: center; gap: 6px; font-size: 12.5px; }
.sx-aimg > b svg { color: var(--sx-blue, #1668D8); }
[data-theme="dark"] .sx-ai, [data-theme="dark"] .sx-aibox, [data-theme="dark"] .sx-aimg { background: #13284B; }
@media (prefers-reduced-motion: reduce) { .sx *, .sx *::after { transition-duration: 1ms !important; } }
</style>
