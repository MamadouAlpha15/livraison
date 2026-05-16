@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#7c3aed;--blt:#8b5cf6;--bdk:#5b21b6;--glow:rgba(124,58,237,.22);
    --sb:#1e1b4b;--sb-text:rgba(255,255,255,.88);--sb-w:268px;
    --bg:#f1f5f9;--card:#fff;--bd:rgba(0,0,0,.07);
    --text:#0f172a;--muted:#64748b;
    --green:#10b981;--gbg:rgba(16,185,129,.1);
    --amber:#f59e0b;--abg:rgba(245,158,11,.1);
    --red:#ef4444;--rbg:rgba(239,68,68,.1);
    --blue:#3b82f6;--bbg:rgba(59,130,246,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;
    position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;
    transition:transform .28s cubic-bezier(.4,0,.2,1);
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}
.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);
    display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb-me{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);
    display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0}
.sb-name{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sb-badge{font-size:10px;color:#a78bfa;font-weight:600;margin-top:1px}
.sb-nav{padding:10px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;
    padding:14px 18px 5px;color:rgba(255,255,255,.35)}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);
    text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;
    box-shadow:inset 0 0 20px rgba(124,58,237,.08)}
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-pill{font-size:10px;font-weight:800;padding:2px 7px;border-radius:20px;margin-left:auto}
.sb-pill.r{background:#ef4444;color:#fff}
.sb-ft{border-top:1px solid rgba(255,255,255,.1);padding:10px 0}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 18px;color:rgba(255,255,255,.6);
    text-decoration:none;font-size:12.5px;font-weight:600;cursor:pointer;background:none;border:none;width:100%;transition:all .15s;font-family:var(--font)}
.sb-ft-row:hover{color:#fff;background:rgba(255,255,255,.07)}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-ft-row:hover .sb-ft-ico{opacity:1}
.live-dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

/* ─── MAIN ─── */
.mn{margin-left:var(--sb-w);flex:1;display:flex;flex-direction:column;min-width:0}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);
    display:flex;align-items:center;padding:0 24px;gap:16px;position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-bc{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;flex:1}
.tb-bc a{color:var(--muted);text-decoration:none}.tb-bc a:hover{color:var(--brand)}
.tb-ua{display:flex;align-items:center;gap:10px;margin-left:auto}
.tb-uav{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);
    display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff}
.tb-uname{font-size:13px;font-weight:700;color:var(--text)}
.tb-urole{font-size:10.5px;color:var(--muted);font-weight:600}
.pw{padding:24px;max-width:1300px;margin:0 auto;width:100%}

/* ─── HERO ─── */
.hero{background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4338ca 100%);
    border-radius:16px;padding:28px 32px;display:flex;align-items:center;gap:24px;
    margin-bottom:24px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-30px;right:-30px;width:180px;height:180px;
    background:radial-gradient(circle,rgba(167,139,250,.25) 0%,transparent 70%);pointer-events:none}
.hero-ico{font-size:40px;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3));flex-shrink:0}
.hero-title{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.3px}
.hero-sub{font-size:13px;color:#c4b5fd;margin-top:3px}
.hero-stats{display:flex;gap:14px;margin-left:auto;flex-wrap:wrap}
.hs{background:rgba(255,255,255,.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);
    border-radius:12px;padding:12px 18px;text-align:center;min-width:90px}
.hs-v{font-size:22px;font-weight:900;color:#fff;line-height:1}
.hs-l{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.6px;margin-top:4px}
.hs-amount{font-size:13px}

/* ─── FILTRES ─── */
.filter-bar{background:var(--card);border:1px solid var(--bd);border-radius:12px;
    padding:16px 20px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:12px;align-items:center}
.chips{display:flex;gap:6px;flex-wrap:wrap}
.chip{padding:6px 14px;border-radius:20px;border:1px solid var(--bd);background:transparent;
    font-size:12.5px;font-weight:600;color:var(--muted);cursor:pointer;text-decoration:none;transition:all .15s}
.chip:hover{border-color:var(--brand);color:var(--brand)}
.chip.on{background:var(--brand);border-color:var(--brand);color:#fff}
.chip.pending{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.3);color:#d97706}
.chip.pending.on{background:#f59e0b;border-color:#f59e0b;color:#fff}
.chip.paid{background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.25);color:#059669}
.chip.paid.on{background:#10b981;border-color:#10b981;color:#fff}
.search-wrap{display:flex;gap:8px;margin-left:auto}
.fi{padding:8px 12px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);
    font-size:13px;color:var(--text);font-family:inherit;outline:none}
.fi:focus{border-color:var(--brand)}
.fb{padding:8px 16px;border-radius:8px;background:var(--brand);color:#fff;border:none;
    font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}

/* ─── TABLE ─── */
.tcard{background:var(--card);border:1px solid var(--bd);border-radius:12px;overflow:hidden}
.twrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{padding:12px 16px;text-align:left;font-size:11.5px;font-weight:800;text-transform:uppercase;
   letter-spacing:.6px;color:var(--muted);background:#f8fafc;border-bottom:1px solid var(--bd);white-space:nowrap}
td{padding:13px 16px;border-bottom:1px solid var(--bd);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafafa}

.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:700}
.badge-pending{background:var(--abg);color:var(--amber)}
.badge-paid{background:var(--gbg);color:var(--green)}

.amt{font-size:14px;font-weight:800;color:var(--text)}
.amt-pending{color:var(--amber)}
.amt-paid{color:var(--green)}
.order-link{font-weight:700;color:var(--brand);text-decoration:none;font-size:13px}
.order-link:hover{text-decoration:underline}
.muted{color:var(--muted);font-size:12px}

.btn-pay{padding:5px 12px;border-radius:8px;background:var(--gbg);border:1px solid rgba(16,185,129,.3);
    color:var(--green);font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.btn-pay:hover{background:#10b981;color:#fff}
.btn-pay:disabled{opacity:.5;cursor:not-allowed}

.empty-state{padding:60px 20px;text-align:center}
.empty-ico{font-size:48px;margin-bottom:12px}
.empty-title{font-size:16px;font-weight:800;color:var(--text);margin-bottom:6px}
.empty-sub{font-size:13px;color:var(--muted)}

.period-tabs{display:flex;gap:4px;background:#f1f5f9;border-radius:8px;padding:3px}
.pt{padding:6px 13px;border-radius:6px;border:none;background:transparent;font-size:12.5px;
    font-weight:600;color:var(--muted);cursor:pointer;font-family:inherit;text-decoration:none;transition:all .15s}
.pt.on{background:var(--card);color:var(--brand);box-shadow:0 1px 4px rgba(0,0,0,.1)}
.pt:hover:not(.on){color:var(--text)}

@media(max-width:768px){
    .sb{transform:translateX(-100%)}
    .sb.open{transform:translateX(0)}
    .sb-close{display:block}
    .sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:199}
    .sb-ov.show{display:block}
    .mn{margin-left:0}
    .ham{display:block}
    .hero{flex-direction:column;align-items:flex-start;padding:20px}
    .hero-stats{margin-left:0;width:100%}
    .hs{flex:1;min-width:70px}
    .filter-bar{flex-direction:column;align-items:stretch}
    .search-wrap{margin-left:0}
}
</style>
@endpush

@section('content')
@php
    $me     = Auth::user();
    $meName = $me->name ?? 'Super Admin';
    $meInit = strtoupper(substr($meName, 0, 1));

    $fmtCur  = fn($n, $cur) => number_format((float)$n, 0, ',', ' ') . ' ' . ($cur ?: 'GNF');
    $heroCur = $shopFilter?->currency ?: null; // null = vue multi-boutiques

    $periodLabels = [
        'all'       => 'Tout',
        'today'     => "Aujourd'hui",
        'yesterday' => 'Hier',
        'week'      => 'Cette semaine',
        'month'     => 'Ce mois',
    ];
    $statLabel = match($period) {
        'today'     => "auj.",
        'yesterday' => "hier",
        'week'      => "sem.",
        'month'     => "mois",
        default     => "total",
    };

    /* ═══ BIBLIOTHÈQUE D'ICÔNES SVG PREMIUM ═══ */
    $s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
    $sl= 'stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"';
    $I = [
        'brand_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
        'home'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
        'store'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'bag'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'box'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
        'brief'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
        'user'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'users'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'truck'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect width="7" height="7" x="14" y="12" rx="1"/><path d="M5 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/><path d="M15 19a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/></svg>',
        'bike'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>',
        'map'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>',
        'pin'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>',
        'card'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>',
        'trend'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
        'dollar' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'receipt'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"/><path d="M14 8H8m8 4H8m5 4H8"/></svg>',
        'star'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'ticket' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3Z"/></svg>',
        'cog'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
        'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        'profile'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        'menu'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>',
        'globe'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="10"/><line x1="2" x2="22" y1="12" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'check'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        'clock'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'phone_sm'=> '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.34 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
        'store_sm'=> '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        'card_sm' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>',
        // Grandes versions
        'card_lg' => '<svg width="38" height="38" viewBox="0 0 24 24" fill="none" '.$sl.'><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>',
    ];
@endphp

<div class="sa">

{{-- ════════ SIDEBAR ════════ --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap" style="color:#fff">{!! $I['brand_lg'] !!}</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()" title="Fermer">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0">
            <div class="sb-name">{{ Str::limit($meName,22) }}</div>
            <div class="sb-badge">Fondateur &amp; Développeur</div>
        </div>
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
        <a href="{{ route('admin.paiements.index') }}" class="sb-a on"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span></a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span></a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['receipt'] !!}</span><span>Factures &amp; Exports</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a"><span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span></a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a"><span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span></a>
        <a href="{{ route('admin.support.index') }}" class="sb-a"><span class="sb-i">{!! $I['ticket'] !!}</span><span>Tickets support</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row">
            <span class="sb-ft-ico">{!! $I['profile'] !!}</span>Mon profil
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-ft-row">
                <span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion
            </button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn">
<header class="tb">
    <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
    <div class="tb-bc">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:4px">{!! $I['home'] !!} Accueil</a>
        <span>›</span><span>Finance</span>
        <span>›</span><span style="color:var(--text);font-weight:700">Paiements</span>
    </div>
    <div class="tb-ua">
        <div class="tb-uav">{{ $meInit }}</div>
        <div>
            <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
            <div class="tb-urole">Super Admin</div>
        </div>
    </div>
</header>

<div class="pw">

    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:8px">
        <span style="display:inline-flex">{!! $I['check'] !!}</span> {{ session('success') }}
    </div>
    @endif

    {{-- HERO --}}
    <div class="hero">
        <div class="hero-ico" style="display:flex;align-items:center;justify-content:center;width:64px;height:64px;background:rgba(255,255,255,.12);border-radius:16px;color:#fff;filter:drop-shadow(0 4px 12px rgba(0,0,0,.3));flex-shrink:0">{!! $I['card_lg'] !!}</div>
        <div>
            <div class="hero-title">Paiements</div>
            <div class="hero-sub">Suivi des paiements des commandes · {{ $periodLabels[$period] ?? 'Tout' }}</div>
        </div>
        <div class="hero-stats">
            <div class="hs">
                <div class="hs-v">{{ $stats['total'] }}</div>
                <div class="hs-l">Total {{ $statLabel }}</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#fbbf24">{{ $stats['pending_count'] }}</div>
                <div class="hs-l">En attente</div>
            </div>
            <div class="hs">
                <div class="hs-v" style="color:#34d399">{{ $stats['paid_count'] }}</div>
                <div class="hs-l">Payés</div>
            </div>
            <div class="hs" style="min-width:130px">
                <div class="hs-v hs-amount" style="color:#fbbf24">
                    {{ number_format((float)$stats['pending_amount'], 0, ',', ' ') }}
                    @if($heroCur)<span style="font-size:11px;opacity:.8"> {{ $heroCur }}</span>@endif
                </div>
                <div class="hs-l">Montant en att.</div>
            </div>
            <div class="hs" style="min-width:130px">
                <div class="hs-v hs-amount" style="color:#34d399">
                    {{ number_format((float)$stats['paid_amount'], 0, ',', ' ') }}
                    @if($heroCur)<span style="font-size:11px;opacity:.8"> {{ $heroCur }}</span>@endif
                </div>
                <div class="hs-l">Montant encaissé</div>
            </div>
        </div>
    </div>

    {{-- FILTRES --}}
    <div class="filter-bar">
        {{-- Période --}}
        <div class="period-tabs">
            @foreach(['all'=>'Tout','today'=>"Auj.",'yesterday'=>'Hier','week'=>'Semaine','month'=>'Mois'] as $val=>$lbl)
            <a href="{{ route('admin.paiements.index', array_merge(request()->except('period','page'),['period'=>$val])) }}"
               class="pt {{ $period===$val ? 'on' : '' }}">{{ $lbl }}</a>
            @endforeach
        </div>

        {{-- Statut --}}
        <div class="chips">
            @php $cs = request('status','all'); @endphp
            <a href="{{ route('admin.paiements.index', array_merge(request()->except('status','page'),['status'=>'all'])) }}"
               class="chip {{ $cs==='all' ? 'on' : '' }}">Tous</a>
            <a href="{{ route('admin.paiements.index', array_merge(request()->except('status','page'),['status'=>'en_attente'])) }}"
               class="chip pending {{ $cs==='en_attente' ? 'on' : '' }}" style="display:inline-flex;align-items:center;gap:5px"><span style="display:inline-flex">{!! $I['clock'] !!}</span> En attente</a>
            <a href="{{ route('admin.paiements.index', array_merge(request()->except('status','page'),['status'=>'payé'])) }}"
               class="chip paid {{ $cs==='payé' ? 'on' : '' }}" style="display:inline-flex;align-items:center;gap:5px"><span style="display:inline-flex">{!! $I['check'] !!}</span> Payés</a>
        </div>

        {{-- Pays + Boutique + Recherche --}}
        <form method="GET" action="{{ route('admin.paiements.index') }}" class="search-wrap" style="flex-wrap:wrap;gap:8px;">
            @foreach(request()->except('search','shop_id','country','page') as $k=>$v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <select name="country" class="fi" onchange="this.form.submit()" style="min-width:160px">
                <option value="">🌍 Tous les pays</option>
                @foreach($countries as $c)
                <option value="{{ $c }}" {{ $country === $c ? 'selected' : '' }}>
                    {{ $c }}
                </option>
                @endforeach
            </select>
            <select name="shop_id" class="fi" onchange="this.form.submit()" style="min-width:180px">
                <option value="">🏪 Toutes les boutiques</option>
                @foreach($shops->when($country, fn($c) => $c->where('country', $country)) as $shop)
                <option value="{{ $shop->id }}" {{ $shopId == $shop->id ? 'selected' : '' }}>
                    {{ $shop->name }}
                    @if(!$country && $shop->country) ({{ $shop->country }}) @endif
                </option>
                @endforeach
            </select>
            <input type="text" name="search" class="fi" placeholder="Commande, client…"
                   value="{{ request('search') }}" style="width:200px">
            <button type="submit" class="fb">Chercher</button>
            @if(request()->hasAny(['search','shop_id','country','status','period']))
            <a href="{{ route('admin.paiements.index') }}" class="fb" style="background:#64748b;text-decoration:none">✕ Réinit.</a>
            @endif
        </form>
    </div>

    {{-- Badge pays filtré --}}
    @if($country && !$shopFilter)
    <div style="background:linear-gradient(90deg,rgba(16,185,129,.1),rgba(5,150,105,.05));border:1px solid rgba(16,185,129,.25);border-radius:10px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
        <span style="display:inline-flex;color:#059669">{!! $I['globe'] !!}</span>
        <span style="font-size:13.5px;font-weight:700;color:#059669">{{ $country }}</span>
        <span style="font-size:12px;color:#64748b">— paiements des boutiques de ce pays uniquement</span>
        <a href="{{ route('admin.paiements.index', request()->except('country','page')) }}" style="margin-left:auto;font-size:12px;color:#64748b;text-decoration:none;font-weight:600">✕ Retirer</a>
    </div>
    @endif

    {{-- Badge boutique filtrée --}}
    @if($shopFilter)
    <div style="background:linear-gradient(90deg,rgba(124,58,237,.12),rgba(99,102,241,.06));border:1px solid rgba(124,58,237,.2);border-radius:10px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
        <span style="display:inline-flex;color:#4f46e5">{!! $I['store_sm'] !!}</span>
        <span style="font-size:13.5px;font-weight:700;color:#4f46e5">{{ $shopFilter->name }}</span>
        <span style="font-size:12px;color:#64748b">— paiements de cette boutique uniquement</span>
        <a href="{{ route('admin.paiements.index', request()->except('shop_id','page')) }}" style="margin-left:auto;font-size:12px;color:#64748b;text-decoration:none;font-weight:600">✕ Retirer</a>
    </div>
    @endif

    {{-- TABLE --}}
    <div class="tcard">
        <div class="twrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Commande</th>
                        <th>Boutique</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                @php
                    $order  = $payment->order;
                    $shop   = $order?->shop;
                    $client = $order?->client;
                    $rowCur = $shop?->currency ?: 'GNF';
                @endphp
                <tr>
                    <td class="muted">#{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        @if($order)
                        <a href="{{ route('admin.orders.show', $order) }}" class="order-link">
                            #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                        </a>
                        @else
                        <span class="muted">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px">{{ $shop?->name ?? '—' }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px">{{ $client?->name ?? '—' }}</div>
                        @if($client?->phone)
                        <div class="muted" style="display:flex;align-items:center;gap:3px"><span style="display:inline-flex;opacity:.6">{!! $I['phone_sm'] !!}</span> {{ $client->phone }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="amt {{ $payment->status === 'payé' ? 'amt-paid' : 'amt-pending' }}">
                            {{ $fmtCur($payment->amount, $rowCur) }}
                        </span>
                    </td>
                    <td>
                        <span style="background:#e0e7ff;color:#4338ca;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;display:inline-flex;align-items:center;gap:4px">
                            <span style="display:inline-flex">{!! $I['card_sm'] !!}</span> Cash
                        </span>
                    </td>
                    <td>
                        @if($payment->status === 'payé')
                        <span class="badge badge-paid" style="display:inline-flex;align-items:center;gap:4px"><span style="display:inline-flex">{!! $I['check'] !!}</span> Payé</span>
                        @else
                        <span class="badge badge-pending" style="display:inline-flex;align-items:center;gap:4px"><span style="display:inline-flex">{!! $I['clock'] !!}</span> En attente</span>
                        @endif
                    </td>
                    <td class="muted">
                        {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/y H:i') }}
                    </td>
                    <td>
                        @if($payment->status === 'en_attente')
                        <form method="POST" action="{{ route('admin.paiements.mark-paid', $payment) }}" style="margin:0">
                            @csrf
                            <button type="submit" class="btn-pay" style="display:inline-flex;align-items:center;gap:5px"><span style="display:inline-flex">{!! $I['check'] !!}</span> Marquer payé</button>
                        </form>
                        @else
                        <span class="muted" style="font-size:12px">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-ico" style="display:flex;justify-content:center;opacity:.2;margin-bottom:12px"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg></div>
                            <div class="empty-title">Aucun paiement trouvé</div>
                            <div class="empty-sub">Aucun paiement ne correspond aux filtres sélectionnés.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div style="padding:16px 20px;border-top:1px solid var(--bd)">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

</div>{{-- /pw --}}
</div>{{-- /mn --}}
</div>{{-- /sa --}}
@endsection

@push('scripts')
<script>
function toggleSb(){
    document.getElementById('sb').classList.toggle('open');
    document.getElementById('sbOv').classList.toggle('show');
}
function closeSb(){
    document.getElementById('sb').classList.remove('open');
    document.getElementById('sbOv').classList.remove('show');
}
function nt(){ alert('Section en cours de développement.'); }
</script>
@endpush
