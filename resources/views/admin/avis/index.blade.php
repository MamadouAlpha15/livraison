@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#7c3aed;--sb:#1e1b4b;--sb-text:rgba(255,255,255,.88);--sb-w:268px;
    --bg:#f1f5f9;--card:#fff;--bd:rgba(0,0,0,.07);
    --text:#0f172a;--muted:#64748b;
    --green:#10b981;--amber:#f59e0b;--red:#ef4444;--blue:#3b82f6;
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb-me{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0}
.sb-name{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-badge{font-size:10px;color:#a78bfa;font-weight:600;margin-top:1px}
.sb-nav{padding:10px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:14px 18px 5px;color:rgba(255,255,255,.35)}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;box-shadow:inset 0 0 20px rgba(124,58,237,.08)}
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-ft{border-top:1px solid rgba(255,255,255,.1);padding:10px 0}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 18px;color:rgba(255,255,255,.6);text-decoration:none;font-size:12.5px;font-weight:600;cursor:pointer;background:none;border:none;width:100%;transition:all .15s}
.sb-ft-row:hover{color:#fff;background:rgba(255,255,255,.07)}
.live-dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.mn{margin-left:var(--sb-w);flex:1;display:flex;flex-direction:column;min-width:0}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 24px;gap:16px;position:sticky;top:0;z-index:100}
.ham{width:34px;height:34px;border-radius:8px;background:none;border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text);flex-shrink:0;transition:background .15s}.ham:hover{background:#f1f5f9}
.tb-bc{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;flex:1}
.tb-bc a{color:var(--muted);text-decoration:none}.tb-bc a:hover{color:var(--brand)}
.tb-ua{display:flex;align-items:center;gap:10px;margin-left:auto}
.tb-uav{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff}
.tb-uname{font-size:13px;font-weight:700;color:var(--text)}.tb-urole{font-size:10.5px;color:var(--muted);font-weight:600}
.pw{padding:24px;max-width:1400px;margin:0 auto;width:100%}

.hero{background:linear-gradient(135deg,#78350f 0%,#92400e 45%,#b45309 100%);border-radius:16px;padding:28px 32px;display:flex;align-items:center;gap:24px;margin-bottom:24px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:radial-gradient(circle,rgba(251,191,36,.2) 0%,transparent 70%);pointer-events:none}
.hero-ico{display:flex;align-items:center;justify-content:center;width:64px;height:64px;background:rgba(255,255,255,.12);border-radius:16px;color:#fff;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3));flex-shrink:0}
.hero-title{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.3px}
.hero-sub{font-size:13px;color:#fcd34d;margin-top:3px}
.hero-stats{display:flex;gap:12px;margin-left:auto;flex-wrap:wrap}
.hs{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:12px 18px;text-align:center;min-width:90px}
.hs-v{font-size:22px;font-weight:900;color:#fff;line-height:1}
.hs-l{font-size:10px;font-weight:700;color:#fcd34d;text-transform:uppercase;letter-spacing:.6px;margin-top:4px}

.tabs{display:flex;gap:4px;background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:6px;margin-bottom:16px}
.tab-btn{flex:1;padding:9px 16px;border-radius:8px;border:none;background:transparent;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;transition:all .15s;font-family:var(--font);text-decoration:none;display:flex;align-items:center;justify-content:center;gap:7px}
.tab-btn:hover{background:var(--bg);color:var(--text)}
.tab-btn.on{background:var(--brand);color:#fff;box-shadow:0 2px 8px rgba(124,58,237,.3)}
.tab-cnt{font-size:11px;font-weight:800;padding:2px 7px;border-radius:20px;background:rgba(255,255,255,.25)}
.tab-btn:not(.on) .tab-cnt{background:var(--bg);color:var(--muted)}

.filter-bar{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.chips{display:flex;gap:6px;flex-wrap:wrap}
.chip{padding:5px 12px;border-radius:20px;border:1px solid var(--bd);background:transparent;font-size:12.5px;font-weight:600;color:var(--muted);cursor:pointer;text-decoration:none;transition:all .15s}
.chip:hover{border-color:var(--amber);color:var(--amber)}
.chip.on{background:var(--amber);border-color:var(--amber);color:#fff}
.chip.r1.on,.chip.r2.on{background:#ef4444;border-color:#ef4444}
.chip.r3.on{background:#f59e0b;border-color:#f59e0b}
.chip.r4.on,.chip.r5.on{background:#10b981;border-color:#10b981}
.fi{padding:8px 12px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:13px;color:var(--text);font-family:inherit;outline:none}
.fi:focus{border-color:var(--brand)}
.fb{padding:8px 16px;border-radius:8px;background:var(--brand);color:#fff;border:none;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}

.tcard{background:var(--card);border:1px solid var(--bd);border-radius:12px;overflow:hidden}
.twrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{padding:12px 16px;text-align:left;font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--bd);white-space:nowrap}
td{padding:13px 16px;border-bottom:1px solid var(--bd);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafafa}
.muted{color:var(--muted);font-size:12px}
.stars{display:flex;gap:2px;align-items:center}
.star{font-size:14px;line-height:1}
.comment-cell{max-width:280px}
.comment-txt{font-size:12.5px;color:var(--text);line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.no-comment{font-size:12px;color:var(--muted);font-style:italic}
.btn-del{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#ef4444;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.btn-del:hover{background:#ef4444;color:#fff}
.empty-state{padding:60px 20px;text-align:center}
.empty-ico{display:flex;justify-content:center;margin-bottom:12px}
.empty-title{font-size:16px;font-weight:800;margin-bottom:6px}
.empty-sub{font-size:13px;color:var(--muted)}
.flash-ok{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:10px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#b91c1c;font-size:13px;font-weight:600;margin-bottom:18px}

@media(max-width:768px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0)}.sb-close{display:block}
    .sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199}.sb-ov.show{display:block}
    .mn{margin-left:0}.ham{display:block}
    .hero{flex-direction:column;align-items:flex-start;padding:20px}
    .hero-stats{margin-left:0;width:100%}.hs{flex:1;min-width:70px}
}
</style>
@endpush

@section('content')
@php
$s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
$sl= 'stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"';
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
'receipt'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="14" y2="13"/></svg>',
'star'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
'ticket' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>',
'cog'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
'profile'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
'menu'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
'search' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
'star_lg'=> '<svg width="38" height="38" viewBox="0 0 24 24" fill="none" '.$sl.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
'store_sm'=> '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9h18v1a4 4 0 0 1-8 0 4 4 0 0 1-8 0V9z"/><path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9"/><path d="M9 21v-7h6v7"/><path d="M3 9l2.5-5h13L21 9"/></svg>',
'truck_sm'=> '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
'store_tab'=> '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9h18v1a4 4 0 0 1-8 0 4 4 0 0 1-8 0V9z"/><path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9"/><path d="M9 21v-7h6v7"/><path d="M3 9l2.5-5h13L21 9"/></svg>',
'truck_tab'=> '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
'trash'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
];
    $me     = Auth::user();
    $meName = $me->name ?? 'Super Admin';
    $meInit = strtoupper(substr($meName, 0, 1));

    $starsHtml = function(int $n): string {
        $out = '';
        for ($i = 1; $i <= 5; $i++) {
            $fill = $i <= $n ? '#f59e0b' : '#e2e8f0';
            $out .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="'.$fill.'" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
        }
        return $out;
    };

    $ratingColor = fn($r) => match(true) {
        $r <= 2  => '#ef4444',
        $r == 3  => '#f59e0b',
        default  => '#10b981',
    };
@endphp

<div class="sa">
{{-- SIDEBAR --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap" style="color:#fff">{!! $I['brand_lg'] !!}</div>
        <div><div class="sb-appname">{{ config('app.name','Shopio') }}</div><div class="sb-apptag">Plateforme · Super Admin</div></div>
        <button class="sb-close" onclick="closeSb()">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0"><div class="sb-name">{{ Str::limit($meName,22) }}</div><div class="sb-badge">Fondateur &amp; Développeur</div></div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a"><span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span></a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a"><span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span></a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a"><span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span></a>
        <div class="sb-sec fin">── Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span></a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span></a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['receipt'] !!}</span><span>Factures &amp; Exports</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a"><span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span></a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a on"><span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span></a>
        <a href="{{ route('admin.support.index') }}" class="sb-a"><span class="sb-i">{!! $I['ticket'] !!}</span><span>Tickets support</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600"><div class="live-dot"></div>Système opérationnel</div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['profile'] !!}</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">@csrf<button type="submit" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion</button></form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- MAIN --}}
<div class="mn">
<header class="tb">
    <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
    <div class="tb-bc">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:5px"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>Accueil</a>
        <span>›</span><span>Plateforme</span>
        <span>›</span><span style="color:var(--text);font-weight:700">Avis &amp; Notation</span>
    </div>
    <div class="tb-ua">
        <div class="tb-uav">{{ $meInit }}</div>
        <div><div class="tb-uname">{{ Str::limit($meName,14) }}</div><div class="tb-urole">Super Admin</div></div>
    </div>
</header>

<div class="pw">

    @if(session('deleted'))
    <div class="flash-ok"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg> {{ session('deleted') }}</div>
    @endif

    {{-- HERO --}}
    <div class="hero">
        <div class="hero-ico">{!! $I['star_lg'] !!}</div>
        <div>
            <div class="hero-title">Avis &amp; Notation</div>
            <div class="hero-sub">Modération des avis clients sur toute la plateforme</div>
        </div>
        <div class="hero-stats">
            <div class="hs">
                <div class="hs-v">{{ number_format($statsShop['total'] + $statsCo['total']) }}</div>
                <div class="hs-l">Total avis</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#fbbf24">
                    {{ $statsShop['avg'] > 0 ? $statsShop['avg'] : '—' }}
                    <span style="font-size:14px">★</span>
                </div>
                <div class="hs-l">Moy. boutiques</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#93c5fd">
                    {{ $statsCo['avg'] > 0 ? $statsCo['avg'] : '—' }}
                    <span style="font-size:14px">★</span>
                </div>
                <div class="hs-l">Moy. entreprises</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#fca5a5">{{ number_format($statsShop['bad'] + $statsCo['bad']) }}</div>
                <div class="hs-l">Mauvais avis ≤2★</div>
            </div>
        </div>
    </div>

    {{-- TABS --}}
    <div class="tabs">
        <a href="{{ route('admin.avis.index', array_merge(request()->except('tab','page'), ['tab'=>'boutiques'])) }}"
           class="tab-btn {{ $tab === 'boutiques' ? 'on' : '' }}">
            {!! $I['store_tab'] !!} Boutiques
            <span class="tab-cnt">{{ number_format($statsShop['total']) }}</span>
        </a>
        <a href="{{ route('admin.avis.index', array_merge(request()->except('tab','page'), ['tab'=>'entreprises'])) }}"
           class="tab-btn {{ $tab === 'entreprises' ? 'on' : '' }}">
            {!! $I['truck_tab'] !!} Entreprises livraison
            <span class="tab-cnt">{{ number_format($statsCo['total']) }}</span>
        </a>
    </div>

    {{-- FILTRES --}}
    <div class="filter-bar">
        <div class="chips">
            @foreach([1,2,3,4,5] as $r)
            <a href="{{ route('admin.avis.index', array_merge(request()->except('rating','page'), ['tab'=>$tab, 'rating'=> $rating == $r ? null : $r])) }}"
               class="chip r{{ $r }} {{ $rating == $r ? 'on' : '' }}">
                {{ $r }}★
            </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.avis.index') }}" style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if($rating)<input type="hidden" name="rating" value="{{ $rating }}">@endif
            <div style="position:relative">
                <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;display:flex;color:var(--muted)">{!! $I['search'] !!}</span>
                <input type="text" name="search" class="fi" placeholder="{{ $tab === 'boutiques' ? 'Boutique, client…' : 'Entreprise, client…' }}"
                       value="{{ $search }}" style="padding-left:32px;width:220px">
            </div>
            <button type="submit" class="fb">Chercher</button>
            @if($search)
            <a href="{{ route('admin.avis.index', array_merge(request()->except('search','page'), ['tab'=>$tab])) }}"
               class="fb" style="background:#64748b;text-decoration:none;display:inline-flex;align-items:center;justify-content:center">{!! $I['close'] !!}</a>
            @endif
        </form>
    </div>

    {{-- TABLE --}}
    <div class="tcard">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:12px">
            <div style="font-size:14px;font-weight:800">
                {{ $tab === 'boutiques' ? 'Avis boutiques' : 'Avis entreprises livraison' }}
            </div>
            <span style="font-size:12px;color:var(--muted)">{{ $reviews->total() }} avis</span>
            @if($rating)
            <span style="font-size:12px;color:var(--amber);font-weight:600">· Filtre {{ $rating }}★</span>
            @endif
            @if($search)
            <span style="font-size:12px;color:var(--brand);font-weight:600">· « {{ $search }} »</span>
            @endif
        </div>
        <div class="twrap">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>{{ $tab === 'boutiques' ? 'Boutique' : 'Entreprise' }}</th>
                        @if($tab === 'boutiques')<th>Commande</th>@endif
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reviews as $review)
                <tr>
                    {{-- Client --}}
                    <td>
                        @php $client = $tab === 'boutiques' ? $review->client : $review->user; @endphp
                        @if($client)
                        <div style="font-weight:700;font-size:13px">{{ $client->name }}</div>
                        <div class="muted">{{ $client->email }}</div>
                        @else
                        <span class="muted">—</span>
                        @endif
                    </td>

                    {{-- Boutique / Entreprise --}}
                    <td>
                        @if($tab === 'boutiques')
                            @php $shop = $review->order?->shop; @endphp
                            @if($shop)
                            <div style="display:flex;align-items:center;gap:6px">
                                <span style="display:flex;color:var(--muted)">{!! $I['store_sm'] !!}</span>
                                <span style="font-weight:600;font-size:13px">{{ Str::limit($shop->name, 22) }}</span>
                            </div>
                            @else
                            <span class="muted">—</span>
                            @endif
                        @else
                            @if($review->company)
                            <div style="display:flex;align-items:center;gap:6px">
                                <span style="display:flex;color:var(--muted)">{!! $I['truck_sm'] !!}</span>
                                <span style="font-weight:600;font-size:13px">{{ Str::limit($review->company->name, 22) }}</span>
                            </div>
                            @else
                            <span class="muted">—</span>
                            @endif
                        @endif
                    </td>

                    {{-- Commande (boutiques seulement) --}}
                    @if($tab === 'boutiques')
                    <td>
                        @if($review->order)
                        <span style="font-size:12px;font-weight:600;color:var(--brand)">#{{ $review->order->id }}</span>
                        @else
                        <span class="muted">—</span>
                        @endif
                    </td>
                    @endif

                    {{-- Note --}}
                    <td>
                        <div class="stars">{!! $starsHtml($review->rating) !!}</div>
                        <div style="font-size:11px;font-weight:800;color:{{ $ratingColor($review->rating) }};margin-top:2px">
                            {{ $review->rating }}/5
                        </div>
                    </td>

                    {{-- Commentaire --}}
                    <td class="comment-cell">
                        @if($review->comment)
                        <div class="comment-txt">{{ $review->comment }}</div>
                        @else
                        <span class="no-comment">Aucun commentaire</span>
                        @endif
                    </td>

                    {{-- Date --}}
                    <td class="muted">
                        {{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y') }}
                        <div style="font-size:11px;margin-top:1px">{{ \Carbon\Carbon::parse($review->created_at)->diffForHumans() }}</div>
                    </td>

                    {{-- Supprimer --}}
                    <td>
                        <form method="POST"
                              action="{{ $tab === 'boutiques'
                                  ? route('admin.avis.destroy-shop', $review)
                                  : route('admin.avis.destroy-company', $review) }}"
                              onsubmit="return confirm('Supprimer cet avis ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-del">{!! $I['trash'] !!} Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ $tab === 'boutiques' ? 7 : 6 }}">
                    <div class="empty-state">
                        <div class="empty-ico" style="opacity:.2"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                        <div class="empty-title">Aucun avis trouvé</div>
                        <div class="empty-sub">Aucun avis ne correspond aux filtres sélectionnés.</div>
                    </div>
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div style="padding:16px 20px;border-top:1px solid var(--bd)">{{ $reviews->links() }}</div>
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
function nt(){alert('Section en cours de développement.')}
</script>
@endpush
