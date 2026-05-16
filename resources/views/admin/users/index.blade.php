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

/* HERO */
.hero{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4338ca 100%);border-radius:16px;padding:28px 32px;display:flex;align-items:center;gap:24px;margin-bottom:24px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:radial-gradient(circle,rgba(167,139,250,.2) 0%,transparent 70%);pointer-events:none}
.hero-ico{display:flex;align-items:center;justify-content:center;width:64px;height:64px;background:rgba(255,255,255,.12);border-radius:16px;color:#fff;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3));flex-shrink:0}
.hero-title{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.3px}
.hero-sub{font-size:13px;color:#c4b5fd;margin-top:3px}
.hero-stats{display:flex;gap:12px;margin-left:auto;flex-wrap:wrap}
.hs{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:12px 18px;text-align:center;min-width:90px}
.hs-v{font-size:22px;font-weight:900;color:#fff;line-height:1}
.hs-l{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.6px;margin-top:4px}

/* FILTRES */
.filter-bar{background:var(--card);border:1px solid var(--bd);border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.chips{display:flex;gap:6px;flex-wrap:wrap}
.chip{padding:6px 14px;border-radius:20px;border:1px solid var(--bd);background:transparent;font-size:12.5px;font-weight:600;color:var(--muted);cursor:pointer;text-decoration:none;transition:all .15s}
.chip:hover{border-color:var(--brand);color:var(--brand)}
.chip.on{background:var(--brand);border-color:var(--brand);color:#fff}
.chip.c-client.on{background:#10b981;border-color:#10b981}
.chip.c-livreur.on{background:#f59e0b;border-color:#f59e0b}
.chip.c-employe.on{background:#3b82f6;border-color:#3b82f6}
.chip.c-co.on{background:#8b5cf6;border-color:#8b5cf6}
.chip.c-company.on{background:#0ea5e9;border-color:#0ea5e9}
.chip.c-admin.on{background:#ef4444;border-color:#ef4444}
.fi{padding:8px 12px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:13px;color:var(--text);font-family:inherit;outline:none}
.fi:focus{border-color:var(--brand)}
.fb{padding:8px 16px;border-radius:8px;background:var(--brand);color:#fff;border:none;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}

/* TABLE */
.tcard{background:var(--card);border:1px solid var(--bd);border-radius:12px;overflow:hidden}
.twrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{padding:12px 16px;text-align:left;font-size:11.5px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--bd);white-space:nowrap}
td{padding:13px 16px;border-bottom:1px solid var(--bd);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafafa}
.muted{color:var(--muted);font-size:12px}
.av{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0}
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700}
.role-superadmin{background:#fef3c7;color:#92400e}
.role-client{background:rgba(16,185,129,.12);color:#047857}
.role-livreur{background:rgba(245,158,11,.12);color:#b45309}
.role-employe,.role-vendeur,.role-admin{background:rgba(59,130,246,.12);color:#1d4ed8}
.role-co{background:rgba(139,92,246,.12);color:#6d28d9}
.empty-state{padding:60px 20px;text-align:center}
.empty-ico{display:flex;justify-content:center;margin-bottom:12px}
.empty-title{font-size:16px;font-weight:800;margin-bottom:6px}
.empty-sub{font-size:13px;color:var(--muted)}

@media(max-width:768px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0)}.sb-close{display:block}
    .sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199}.sb-ov.show{display:block}
    .mn{margin-left:0}.ham{display:block}
    .hero{flex-direction:column;align-items:flex-start;padding:20px}
    .hero-stats{margin-left:0;width:100%}.hs{flex:1;min-width:70px}
    .filter-bar{flex-direction:column;align-items:stretch}
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
'phone_sm'=> '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
'store_sm'=> '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 9h18v1a4 4 0 0 1-8 0 4 4 0 0 1-8 0V9z"/><path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9"/><path d="M9 21v-7h6v7"/><path d="M3 9l2.5-5h13L21 9"/></svg>',
'truck_sm'=> '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
'users_lg'=> '<svg width="38" height="38" viewBox="0 0 24 24" fill="none" '.$sl.'><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
'bolt_xs' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
'key_xs'  => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3"/></svg>',
'brief_xs'=> '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
'bike_xs' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6h-3l-2 5H5.5"/><path d="M9 11l3-5h5l2 6h-4.5"/></svg>',
'user_xs' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'truck_xs'=> '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
];
    $me     = Auth::user();
    $meName = $me->name ?? 'Super Admin';
    $meInit = strtoupper(substr($meName, 0, 1));

    $roleLabels = [
        'all'       => 'Tous',
        'client'    => 'Clients',
        'livreur'   => 'Livreurs',
        'employe'   => 'Employés boutique',
        'company'   => 'Sociétés livraison',
        'superadmin'=> 'Super Admins',
    ];

    $roleColors = [
        'superadmin' => ['bg'=>'#7c3aed','txt'=>'#fff','ico'=>$I['bolt_xs']],
        'admin'      => ['bg'=>'#ef4444','txt'=>'#fff','ico'=>$I['key_xs']],
        'employe'    => ['bg'=>'#3b82f6','txt'=>'#fff','ico'=>$I['brief_xs']],
        'vendeur'    => ['bg'=>'#3b82f6','txt'=>'#fff','ico'=>$I['brief_xs']],
        'livreur'    => ['bg'=>'#f59e0b','txt'=>'#fff','ico'=>$I['bike_xs']],
        'client'     => ['bg'=>'#10b981','txt'=>'#fff','ico'=>$I['user_xs']],
        'company'    => ['bg'=>'#0ea5e9','txt'=>'#fff','ico'=>$I['truck_xs']],
    ];
    $getRoleColor = fn($r) => $roleColors[$r] ?? ['bg'=>'#94a3b8','txt'=>'#fff','ico'=>$I['user_xs']];

    $flags = [
        'GN'=>'🇬🇳','SN'=>'🇸🇳','ML'=>'🇲🇱','CI'=>'🇨🇮','CM'=>'🇨🇲',
        'FR'=>'🇫🇷','MA'=>'🇲🇦','BF'=>'🇧🇫','NE'=>'🇳🇪','TG'=>'🇹🇬','BJ'=>'🇧🇯','CG'=>'🇨🇬',
        'Guinée'=>'🇬🇳','Sénégal'=>'🇸🇳','Mali'=>'🇲🇱','France'=>'🇫🇷',
    ];
    $getFlag = fn($c) => $flags[$c] ?? ($c ? '🌍' : '');
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
        <a href="{{ route('admin.users.index') }}" class="sb-a on"><span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span></a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a"><span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span></a>
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
        <span>›</span><span style="color:var(--text);font-weight:700">Tous les utilisateurs</span>
    </div>
    <div class="tb-ua">
        <div class="tb-uav">{{ $meInit }}</div>
        <div><div class="tb-uname">{{ Str::limit($meName,14) }}</div><div class="tb-urole">Super Admin</div></div>
    </div>
</header>

<div class="pw">

    {{-- HERO --}}
    <div class="hero">
        <div class="hero-ico">{!! $I['users_lg'] !!}</div>
        <div>
            <div class="hero-title">Tous les utilisateurs</div>
            <div class="hero-sub">Vue globale de tous les comptes de la plateforme</div>
        </div>
        <div class="hero-stats">
            <div class="hs">
                <div class="hs-v">{{ number_format($stats['total']) }}</div>
                <div class="hs-l">Total</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#34d399">{{ number_format($stats['clients']) }}</div>
                <div class="hs-l">Clients</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#fbbf24">{{ number_format($stats['livreurs']) }}</div>
                <div class="hs-l">Livreurs</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#93c5fd">{{ number_format($stats['employes']) }}</div>
                <div class="hs-l">Employés</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#c4b5fd">{{ number_format($stats['entreprises']) }}</div>
                <div class="hs-l">Sociétés</div>
            </div>
        </div>
    </div>

    {{-- FILTRES --}}
    <div class="filter-bar">
        {{-- Rôle --}}
        <div class="chips">
            @foreach($roleLabels as $val => $lbl)
            <a href="{{ route('admin.users.index', array_merge(request()->except('role','page'), ['role'=>$val])) }}"
               class="chip c-{{ $val }} {{ $role === $val ? 'on' : '' }}">{{ $lbl }}</a>
            @endforeach
        </div>
        {{-- Recherche --}}
        <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex;gap:8px;margin-left:auto;flex-wrap:wrap">
            @foreach(request()->except('search','page') as $k=>$v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <div style="position:relative">
                <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;display:flex;color:var(--muted)">{!! $I['search'] !!}</span>
                <input type="text" name="search" class="fi" placeholder="Nom, email, téléphone…"
                       value="{{ $search }}" style="padding-left:32px;width:240px">
            </div>
            <button type="submit" class="fb">Chercher</button>
            @if($search)
            <a href="{{ route('admin.users.index', request()->except('search','page')) }}"
               class="fb" style="background:#64748b;text-decoration:none;display:inline-flex;align-items:center;justify-content:center">{!! $I['close'] !!}</a>
            @endif
        </form>
    </div>

    {{-- TABLE --}}
    <div class="tcard">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:12px">
            <div style="font-size:14px;font-weight:800">
                {{ $roleLabels[$role] ?? 'Tous les utilisateurs' }}
            </div>
            <span style="font-size:12px;color:var(--muted)">{{ $users->total() }} compte{{ $users->total() > 1 ? 's' : '' }}</span>
            @if($search)
            <span style="font-size:12px;color:var(--brand);font-weight:600">· Résultats pour « {{ $search }} »</span>
            @endif
        </div>
        <div class="twrap">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Contact</th>
                        <th>Rôle</th>
                        <th>Boutique / Entreprise</th>
                        <th>Pays</th>
                        <th>Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                @php
                    $rc   = $getRoleColor($user->role);
                    $init = strtoupper(substr($user->name ?? '?', 0, 1));
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="av" style="background:{{ $rc['bg'] }}">{{ $init }}</div>
                            <div>
                                <div style="font-weight:700;font-size:13.5px">{{ $user->name }}</div>
                                @if($user->email)
                                <div class="muted">{{ $user->email }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->phone)
                        <div style="display:flex;align-items:center;gap:5px;font-weight:600;font-size:13px"><span style="display:flex;color:var(--muted)">{!! $I['phone_sm'] !!}</span>{{ $user->phone }}</div>
                        @else
                        <span class="muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge role-{{ $user->role }}" style="background:{{ $rc['bg'] }}1a;color:{{ $rc['bg'] }};display:inline-flex;align-items:center;gap:4px">
                            {!! $rc['ico'] !!} {{ ucfirst($user->role) }}
                        </span>
                        @if($user->role_in_shop && $user->role_in_shop !== $user->role)
                        <div class="muted" style="margin-top:3px">{{ $user->role_in_shop }} boutique</div>
                        @endif
                    </td>
                    <td>
                        @if($user->shop)
                        <div style="display:flex;align-items:center;gap:6px">
                            <span style="display:flex;color:var(--muted)">{!! $I['store_sm'] !!}</span>
                            <span style="font-weight:600;font-size:13px">{{ Str::limit($user->shop->name, 22) }}</span>
                        </div>
                        @elseif($user->ownedCompany)
                        <div style="display:flex;align-items:center;gap:6px">
                            <span style="display:flex;color:var(--muted)">{!! $I['truck_sm'] !!}</span>
                            <span style="font-weight:600;font-size:13px">{{ Str::limit($user->ownedCompany->name, 22) }}</span>
                        </div>
                        <div class="muted" style="margin-top:2px;font-size:11px;padding-left:19px">Propriétaire</div>
                        @elseif($user->deliveryCompany)
                        <div style="display:flex;align-items:center;gap:6px">
                            <span style="display:flex;color:var(--muted)">{!! $I['truck_sm'] !!}</span>
                            <span style="font-weight:600;font-size:13px">{{ Str::limit($user->deliveryCompany->name, 22) }}</span>
                        </div>
                        @else
                        <span class="muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($user->country)
                        <span style="font-size:15px">{{ $getFlag($user->country) }}</span>
                        <span style="font-size:12.5px;font-weight:600;margin-left:4px">{{ $user->country }}</span>
                        @else
                        <span class="muted">—</span>
                        @endif
                    </td>
                    <td class="muted">
                        {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y') }}
                        <div style="font-size:11px;margin-top:1px">{{ \Carbon\Carbon::parse($user->created_at)->diffForHumans() }}</div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="empty-ico" style="opacity:.2"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                        <div class="empty-title">Aucun utilisateur trouvé</div>
                        <div class="empty-sub">Aucun compte ne correspond aux filtres sélectionnés.</div>
                    </div>
                </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div style="padding:16px 20px;border-top:1px solid var(--bd)">{{ $users->links() }}</div>
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
