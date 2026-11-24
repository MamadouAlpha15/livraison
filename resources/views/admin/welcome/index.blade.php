@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#1668D8;--sb:#0B1E3D;--sb-text:rgba(255,255,255,.88);--sb-w:268px;
    --bg:#F6F9FF;--card:#fff;--bd:rgba(0,0,0,.07);
    --text:#16162A;--muted:#62627C;
    --green:#10b981;--amber:#f59e0b;--red:#ef4444;--blue:#3b82f6;
    --font:'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#7DB4FF,#1668D8);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(31,124,246,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#8FA6CC;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb-me{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7DB4FF,#1260C9);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0}
.sb-name{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-badge{font-size:10px;color:#7DB4FF;font-weight:600;margin-top:1px}
.sb-nav{padding:10px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:14px 18px 5px;color:rgba(255,255,255,.35)}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#8FA6CC}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(31,124,246,.22);border-left-color:#7DB4FF;color:#fff;font-weight:700;box-shadow:inset 0 0 20px rgba(22,104,216,.08)}
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-ft{border-top:1px solid rgba(255,255,255,.1);padding:10px 0}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 18px;color:rgba(255,255,255,.6);text-decoration:none;font-size:12.5px;font-weight:600;cursor:pointer;background:none;border:none;width:100%;transition:all .15s}
.sb-ft-row:hover{color:#fff;background:rgba(255,255,255,.07)}
.live-dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.mn{margin-left:var(--sb-w);flex:1;display:flex;flex-direction:column;min-width:0}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 24px;gap:16px;position:sticky;top:0;z-index:100}
.ham{width:34px;height:34px;border-radius:8px;background:none;border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text);flex-shrink:0;transition:background .15s}.ham:hover{background:var(--bg)}
.tb-bc{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;flex:1}
.tb-bc a{color:var(--muted);text-decoration:none}.tb-bc a:hover{color:var(--brand)}
.tb-ua{display:flex;align-items:center;gap:10px;margin-left:auto}
.tb-uav{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#7DB4FF,#1260C9);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff}
.tb-uname{font-size:13px;font-weight:700;color:var(--text)}.tb-urole{font-size:10.5px;color:var(--muted);font-weight:600}
.pw{padding:24px;max-width:1300px;margin:0 auto;width:100%}

.hero{background:linear-gradient(135deg,#0B1E3D 0%,#132B52 50%,#1260C9 100%);border-radius:16px;padding:28px 32px;display:flex;align-items:center;gap:24px;margin-bottom:24px;position:relative;overflow:hidden;flex-wrap:wrap}
.hero::before{content:'';position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:radial-gradient(circle,rgba(31,124,246,.2) 0%,transparent 70%);pointer-events:none}
.hero-ico{display:flex;align-items:center;justify-content:center;width:64px;height:64px;background:rgba(255,255,255,.12);border-radius:16px;color:#fff;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3));flex-shrink:0}
.hero-title{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.3px}
.hero-sub{font-size:13px;color:#8FA6CC;margin-top:3px}
.hero-stats{display:flex;gap:12px;flex-wrap:wrap}
.hs{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:12px 18px;text-align:center;min-width:90px}
.hs-v{font-size:22px;font-weight:900;color:#fff;line-height:1}
.hs-l{font-size:10px;font-weight:700;color:#8FA6CC;text-transform:uppercase;letter-spacing:.6px;margin-top:4px}
.hero-acts{margin-left:auto}
.btn-check{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:10px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);color:#fff;font-size:13px;font-weight:700;cursor:pointer;transition:all .15s;font-family:inherit}
.btn-check:hover{background:rgba(255,255,255,.22)}

.flash{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:12.5px;font-weight:600}
.flash.ok{background:rgba(16,185,129,.1);color:var(--ok-ink);border:1px solid rgba(16,185,129,.2)}
.flash.err{background:rgba(239,68,68,.1);color:var(--err-ink);border:1px solid rgba(239,68,68,.2)}

.card{background:var(--card);border:1px solid var(--bd);border-radius:14px;overflow:hidden}
.card-h{padding:16px 22px;border-bottom:1px solid var(--bd);font-size:14px;font-weight:800;display:flex;align-items:center;gap:8px}

table{width:100%;border-collapse:collapse}
th{padding:11px 18px;text-align:left;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);background:var(--bg);border-bottom:1px solid var(--bd);white-space:nowrap}
td{padding:13px 18px;border-bottom:1px solid var(--bd);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
.muted{color:var(--muted);font-size:12px}
.ph-ico{width:38px;height:38px;border-radius:9px;background:rgba(239,68,68,.1);color:#dc2626;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.prod-cell{display:flex;align-items:center;gap:10px}
.prod-name{font-weight:700;color:var(--text)}
.shop-name{font-size:11.5px;color:var(--muted);margin-top:1px}
.paths-list{font-size:10.5px;color:var(--muted);font-family:monospace;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.status-pill{font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:20px;display:inline-block}
.status-pill.open{background:rgba(239,68,68,.12);color:#b91c1c}
.status-pill.notified{background:rgba(245,158,11,.12);color:#b45309}
.row-acts{display:flex;gap:6px}
.btn-sm{padding:6px 12px;border-radius:7px;font-size:11.5px;font-weight:700;border:none;cursor:pointer;font-family:inherit;white-space:nowrap}
.btn-sm.notify{background:linear-gradient(135deg,#1668D8,#1260C9);color:#fff}
.btn-sm.notify:hover{opacity:.92}
.btn-sm.notify:disabled{opacity:.5;cursor:not-allowed}
.btn-sm.resolve{background:var(--bg);color:var(--muted);border:1px solid var(--bd)}
.btn-sm.resolve:hover{background:#E3E8F2;color:var(--text)}
.empty-state{padding:60px 20px;text-align:center}
.empty-ico{width:56px;height:56px;border-radius:50%;background:rgba(16,185,129,.1);color:#059669;display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
.empty-title{font-size:15px;font-weight:800;margin-bottom:5px}
.empty-sub{font-size:12.5px;color:var(--muted)}


/* ── Bienvenue WhatsApp ── */
.wa-green{--wa:#16A34A;--wa-dk:#15803D}
.box{background:var(--card);border:1px solid var(--bd);border-radius:14px;margin-bottom:18px}
.box > summary{padding:14px 20px;font-size:13.5px;font-weight:800;cursor:pointer;list-style:none;display:flex;align-items:center;gap:8px}
.box > summary::-webkit-details-marker{display:none}
.box > summary::after{content:'▾';margin-left:auto;color:var(--muted)}
.box[open] > summary{border-bottom:1px solid var(--bd)}
.box-in{padding:18px 20px;display:grid;gap:14px}
.wl-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.wl-f label{display:block;font-size:12.5px;font-weight:800;margin-bottom:6px}
.wl-f textarea{width:100%;min-height:200px;padding:12px 14px;border:1px solid var(--bd);border-radius:10px;font:500 13px/1.55 var(--font);color:var(--text);background:var(--card);resize:vertical}
.wl-f textarea:focus{outline:0;border-color:var(--brand);box-shadow:0 0 0 3px rgba(22,104,216,.12)}
.wl-vars{display:flex;flex-wrap:wrap;gap:6px 14px;font-size:12px;color:var(--muted)}
.wl-vars code{font-size:11.5px;font-weight:700;color:var(--text);background:var(--bg);padding:1px 6px;border-radius:5px}
.wl-acts{display:flex;gap:8px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 16px;border-radius:9px;font:700 12.5px var(--font);border:1px solid var(--bd);background:var(--card);color:var(--text);cursor:pointer;text-decoration:none;white-space:nowrap}
.btn:hover{border-color:#B9C7DD}
.btn.main{background:var(--brand);border-color:var(--brand);color:#fff}
.btn.wa{background:#16A34A;border-color:#16A34A;color:#fff}
.btn.wa:hover{background:#15803D}
.btn.sm{padding:6px 11px;font-size:11.5px;border-radius:8px}
.btn:disabled{opacity:.5;cursor:progress}
.tabs{display:flex;gap:4px;flex-wrap:wrap;padding:4px;border-radius:11px;background:var(--card);border:1px solid var(--bd)}
.tabs a{padding:7px 13px;border-radius:8px;font-size:12.5px;font-weight:700;color:var(--muted);text-decoration:none;white-space:nowrap}
.tabs a:hover{color:var(--text);background:var(--bg)}
.tabs a.on{background:var(--brand);color:#fff}
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.who{font-weight:700;color:var(--text)}
.sub{font-size:11.5px;color:var(--muted);margin-top:2px}
.kind{font-size:10.5px;font-weight:800;padding:2px 9px;border-radius:20px;display:inline-block;white-space:nowrap}
.kind.vendor{background:rgba(22,104,216,.1);color:#1260C9}
.kind.client{background:rgba(98,98,124,.12);color:var(--muted)}
.num{font-weight:700;white-space:nowrap}
.none{font-size:12px;color:var(--muted)}
.done-pill{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;padding:4px 10px;border-radius:20px;background:rgba(22,163,74,.1);color:#15803D;white-space:nowrap}
.row-acts{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
.msg-row td{background:var(--bg)}
.msg-row pre{margin:0;white-space:pre-wrap;font:500 12.5px/1.6 var(--font);color:var(--text)}
@media(max-width:900px){.wl-grid{grid-template-columns:1fr}}
@media(max-width:768px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0)}
    .sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199}.sb-ov.show{display:block}
    .mn{margin-left:0}.ham{display:flex}
    .hero{flex-direction:column;align-items:flex-start;padding:20px}
    .hero-acts{margin-left:0;width:100%}.btn-check{width:100%;justify-content:center}
    .tb{padding:0 12px;gap:10px}.tb-bc{min-width:0;overflow:hidden;white-space:nowrap}.tb-ua > div:last-child{display:none}
    .pw{padding:16px}
}
</style>
@include('admin._partials.shopio-theme')
@include('admin._partials.dark-mode')
@endpush

@section('content')
@php
$s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
$I = [
'brand_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
'home'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>',
'store'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9h18v1a4 4 0 0 1-8 0 4 4 0 0 1-8 0V9z"/><path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9"/><path d="M9 21v-7h6v7"/><path d="M3 9l2.5-5h13L21 9"/></svg>',
'bag'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
'box'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>',
'brief'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="12.01"/></svg>',
'user'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'users'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
'truck'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
'bike'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6h-3l-2 5H5.5"/><path d="M9 11l3-5h5l2 6h-4.5"/></svg>',
'map'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>',
'pin'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
'card'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
'trend'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
'dollar' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
'megaphone'=>'<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>',
'image_lg'=>'<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>',
'image'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>',
'star'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
'ticket' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>',
'cog'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
'profile'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
'menu'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
'refresh'=> '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg>',
'chat'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/></svg>',
'chat_lg'=> '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/><path d="M9 10.5h.01M12 10.5h.01M15 10.5h.01"/></svg>',
'check'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
];
$me     = auth()->user();
$meName = $me->name ?? 'Admin';
$meInit = strtoupper(substr($meName,0,1));
@endphp

<div class="sa">
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap"><img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="" width="42" height="42"></div>
        <div><div class="sb-appname">Shopio</div><div class="sb-apptag">Plateforme · Super Admin</div></div>
        <button class="sb-close" onclick="closeSb()">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0"><div class="sb-name">{{ Str::limit($meName,22) }}</div><div class="sb-badge">{{ $me->role === 'superadmin' ? 'Fondateur & Développeur' : 'Personnel plateforme' }}</div></div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a"><span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span></a>
        <a href="{{ route('admin.image-health.index') }}" class="sb-a"><span class="sb-i">{!! $I['image'] !!}</span><span>Santé des images</span></a>
        <div class="sb-sec livr">SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span></a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a"><span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span></a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a"><span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span></a>
        <div class="sb-sec fin">Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.payouts.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Règlements boutiques</span></a>
        <a href="{{ route('admin.campaigns.index') }}" class="sb-a"><span class="sb-i">{!! \App\Support\IconLibrary::svg('megaphone', '', 17) !!}</span><span>Campagnes</span></a>
        <a href="{{ route('admin.affiliation.index') }}" class="sb-a"><span class="sb-i">{!! \App\Support\IconLibrary::svg('gift', '', 17) !!}</span><span>Affiliation</span></a>
        <a href="{{ route('admin.reminders.index') }}" class="sb-a"><span class="sb-i">{!! \App\Support\IconLibrary::svg('bell', '', 17) !!}</span><span>Relances</span></a>
        <a href="{{ route('admin.shopio-ai.index') }}" class="sb-a"><span class="sb-i">{!! \App\Support\IconLibrary::svg('sparkles', '', 17) !!}</span><span>Shopio IA</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span></a>
        <div class="sb-sec plat">Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a"><span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span></a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a"><span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span></a>
        <a href="{{ route('admin.support.index') }}" class="sb-a"><span class="sb-i">{!! $I['ticket'] !!}</span><span>Support conversations</span></a>
        <a href="{{ route('admin.broadcasts.index') }}" class="sb-a"><span class="sb-i">{!! $I['megaphone'] !!}</span><span>Annonces par email</span></a>
        @include('admin.welcome._menu-link', ['active' => true])
        <a href="{{ route('admin.plans.index') }}" class="sb-a"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 18px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600"><div class="live-dot"></div>Système opérationnel</div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['profile'] !!}</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">@csrf<button type="submit" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion</button></form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

<div class="mn">
<header class="tb">
    <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
    <div class="tb-bc">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:5px">{!! $I['home'] !!}Accueil</a>
        <span>›</span><span>Plateforme</span>
        <span>›</span><span style="color:var(--text);font-weight:700">Bienvenue WhatsApp</span>
    </div>
    <div class="tb-ua">
        <div class="tb-uav">{{ $meInit }}</div>
        <div><div class="tb-uname">{{ Str::limit($meName,14) }}</div><div class="tb-urole">{{ $me->role === 'superadmin' ? 'Super Admin' : 'Personnel' }}</div></div>
    </div>
    @include('admin._partials.dark-toggle')
</header>

<div class="pw">

    <div class="hero">
        <div class="hero-ico">{!! $I['chat_lg'] !!}</div>
        <div>
            <div class="hero-title">Bienvenue WhatsApp</div>
            <div class="hero-sub">Écrivez à chaque nouvel inscrit pour lui souhaiter la bienvenue et lui proposer votre aide</div>
        </div>
        <div class="hero-stats">
            <div class="hs"><div class="hs-v" id="wlTodo">{{ $stats['todo'] }}</div><div class="hs-l">À contacter</div></div>
            <div class="hs"><div class="hs-v" id="wlDone">{{ $stats['done'] }}</div><div class="hs-l">Contactés</div></div>
            <div class="hs"><div class="hs-v">{{ $stats['week'] }}</div><div class="hs-l">Inscrits 7 j</div></div>
        </div>
    </div>

    @if(!empty($needsMigration))
    <div class="flash err">La base de données n’est pas encore à jour pour cette page. Lancez dans le terminal du projet : <code>php artisan migrate</code>, puis rechargez la page.</div>
    @endif
    @if(session('success'))<div class="flash ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="flash err">{{ $errors->first() }}</div>@endif

    {{-- Messages de bienvenue (vouvoiement) --}}
    <details class="box" @if($errors->any()) open @endif>
        <summary>{!! $I['chat'] !!} Modifier les messages de bienvenue</summary>
        <form method="POST" action="{{ route('admin.welcome.texts') }}" class="box-in">
            @csrf @method('PUT')
            <div class="wl-grid">
                <div class="wl-f">
                    <label for="wlVendor">Message aux vendeurs</label>
                    <textarea id="wlVendor" name="vendor" maxlength="1000" required>{{ old('vendor', $texts['vendor']) }}</textarea>
                </div>
                <div class="wl-f">
                    <label for="wlClient">Message aux clients</label>
                    <textarea id="wlClient" name="client" maxlength="1000" required>{{ old('client', $texts['client']) }}</textarea>
                </div>
            </div>
            <div class="wl-vars">
                <span>Variables :</span>
                @foreach($vars as $v => $label)<span><code>{{ $v }}</code> {{ $label }}</span>@endforeach
            </div>
            <div class="wl-acts">
                <button type="submit" class="btn main">Enregistrer les messages</button>
                <button type="submit" name="reset" value="1" class="btn" formnovalidate onclick="return confirm('Remettre les deux messages par défaut ?')">Remettre par défaut</button>
            </div>
        </form>
    </details>

    {{-- Filtres --}}
    @php $tab = fn ($k, $v) => route('admin.welcome.index', array_filter(['vue' => $k === 'vue' ? $v : $vue, 'qui' => $k === 'qui' ? $v : $qui], fn ($x) => $x !== 'todo' && $x !== 'all')); @endphp
    <div class="filters">
        <nav class="tabs" aria-label="Statut">
            @foreach(['todo' => 'À contacter', 'done' => 'Déjà contactés', 'all' => 'Tous'] as $k => $l)
            <a href="{{ $tab('vue', $k) }}" class="{{ $vue === $k ? 'on' : '' }}">{{ $l }}</a>
            @endforeach
        </nav>
        <nav class="tabs" aria-label="Type de compte">
            @foreach(['all' => 'Tout le monde', 'vendor' => 'Vendeurs', 'client' => 'Clients'] as $k => $l)
            <a href="{{ $tab('qui', $k) }}" class="{{ $qui === $k ? 'on' : '' }}">{{ $l }}</a>
            @endforeach
        </nav>
    </div>

    <div class="card">
        <div class="card-h">{!! $I['users'] !!} Inscrits des {{ \App\Http\Controllers\Admin\WelcomeController::DAYS }} derniers jours</div>
        @if($rows->isEmpty())
        <div class="empty-state">
            <div class="empty-ico">{!! $I['check'] !!}</div>
            <div class="empty-title">Tout le monde a reçu son message</div>
            <div class="empty-sub">Les nouveaux inscrits apparaîtront ici.</div>
        </div>
        @else
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr><th>Nom</th><th>Type</th><th>WhatsApp</th><th>Inscription</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                    @php $u = $r['user']; $c = $r['contact']; @endphp
                    <tr data-wl-row="{{ $u->id }}">
                        <td>
                            <div class="who">{{ $u->name }}</div>
                            @if($c['shop'])<div class="sub">{{ $c['shop']->name }}</div>@endif
                            <div class="sub">{{ $u->email }}</div>
                        </td>
                        <td><span class="kind {{ $c['kind'] }}">{{ $c['kind'] === 'vendor' ? 'Vendeur' : 'Client' }}</span></td>
                        <td>
                            @if($c['kind'] === 'vendor' && !$c['shop'])<span class="none">Boutique pas encore créée</span>
                            @elseif($c['number'])<span class="num">+{{ $c['number'] }}</span>
                            @else<span class="none">Pas de numéro</span>@endif
                        </td>
                        <td class="muted" title="{{ $u->created_at->format('d/m/Y H:i') }}">{{ $u->created_at->diffForHumans() }}</td>
                        <td>
                            <div class="row-acts" data-wl-acts="{{ $u->id }}" data-wl-url="{{ route('admin.welcome.mark', $u) }}">
                                @if($u->welcome_sent_at)
                                <span class="done-pill">✓ Contacté le {{ $u->welcome_sent_at->format('d/m H:i') }}@if($u->welcome_sent_by && isset($senders[$u->welcome_sent_by])) · {{ $senders[$u->welcome_sent_by] }}@endif</span>
                                <button type="button" class="btn sm" data-wl-undo>Annuler</button>
                                @elseif($r['link'])
                                <a href="{{ $r['link'] }}" target="_blank" rel="noopener" class="btn sm wa" data-wl-send>{!! $I['chat'] !!} Envoyer sur WhatsApp</a>
                                <button type="button" class="btn sm" data-wl-show="{{ $u->id }}" aria-expanded="false">Voir le message</button>
                                @else
                                <button type="button" class="btn sm" data-wl-mark>Marquer contacté</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @if($r['link'] && !$u->welcome_sent_at)
                    <tr class="msg-row" id="wlMsg{{ $u->id }}" hidden><td colspan="5"><pre>{{ $r['message'] }}</pre></td></tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div style="padding:14px 20px;border-top:1px solid var(--bd)">{{ $users->links() }}</div>
        @endif
        @endif
    </div>

</div>
</div>
</div>

@endsection

@push('scripts')
<script>
function toggleSb(){document.getElementById('sb').classList.toggle('open');document.getElementById('sbOv').classList.toggle('show')}
function closeSb(){document.getElementById('sb').classList.remove('open');document.getElementById('sbOv').classList.remove('show')}
(function () {
    'use strict';
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    // Pastilles du menu et compteurs, avec le nombre de personnes restant à contacter
    function setPending(n, doneDelta) {
        document.querySelectorAll('[data-wl-badge]').forEach(b => { b.textContent = n; b.style.display = n > 0 ? 'inline-flex' : 'none'; });
        const t = document.getElementById('wlTodo'); if (t) t.textContent = n;
        const d = document.getElementById('wlDone'); if (d) d.textContent = Math.max(0, (parseInt(d.textContent, 10) || 0) + doneDelta);
    }
    async function mark(box, done) {
        box.querySelectorAll('button, a').forEach(x => x.setAttribute('aria-disabled', 'true'));
        try {
            const r = await fetch(box.dataset.wlUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ done }) });
            if (!r.ok) throw new Error();
            const j = await r.json();
            const msg = document.getElementById('wlMsg' + box.dataset.wlActs);
            if (j.done) {
                box.innerHTML = '<span class="done-pill">✓ Contacté le ' + esc(j.at) + (j.by ? ' · ' + esc(j.by) : '') + '</span><button type="button" class="btn sm" data-wl-undo>Annuler</button>';
                if (msg) msg.hidden = true;
            } else {
                box.innerHTML = '<span class="none">Annulé : rechargez la page pour l’envoyer de nouveau.</span>';
            }
            setPending(j.pending, j.done ? 1 : -1);
        } catch (e) {
            box.querySelectorAll('[aria-disabled]').forEach(x => x.removeAttribute('aria-disabled'));
            alert('Impossible d’enregistrer. Vérifiez votre connexion puis réessayez.');
        }
    }
    document.addEventListener('click', e => {
        const send = e.target.closest('[data-wl-send]');
        // Le lien WhatsApp s'ouvre normalement (nouvel onglet) ; en même temps la personne est marquée contactée
        if (send) { mark(send.closest('[data-wl-acts]'), true); return; }
        const m = e.target.closest('[data-wl-mark]'); if (m) { mark(m.closest('[data-wl-acts]'), true); return; }
        const u = e.target.closest('[data-wl-undo]'); if (u) { mark(u.closest('[data-wl-acts]'), false); return; }
        const s = e.target.closest('[data-wl-show]');
        if (s) {
            const row = document.getElementById('wlMsg' + s.dataset.wlShow); if (!row) return;
            row.hidden = !row.hidden;
            s.setAttribute('aria-expanded', String(!row.hidden));
            s.textContent = row.hidden ? 'Voir le message' : 'Masquer le message';
        }
    });
})();
</script>
@endpush
