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
    --indigo:#6366f1;--ibg:rgba(99,102,241,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff}.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;border:2px solid rgba(196,181,253,.5)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:16px 18px 6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s;cursor:pointer}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700}
.sb-i{width:18px;height:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-ft-ico{width:15px;height:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.8}
.sb-ft-row:hover .sb-ft-ico{opacity:1}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}.mn.sb-closed{margin-left:0}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;border:1px solid var(--bd);background:var(--bg);color:var(--muted);text-decoration:none;transition:all .15s}
.tb-btn:hover{background:#e2e8f0;color:var(--text)}
.tb-ico{width:13px;height:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* ─── CONTENT ─── */
.con{flex:1;padding:26px;max-width:1100px}
.flash{display:flex;align-items:center;gap:10px;padding:13px 16px;border-radius:10px;margin-bottom:22px;font-size:13px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.bc{display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.bc-ico{width:13px;height:13px;display:flex;align-items:center;justify-content:center;color:var(--brand)}

/* ─── ORDER HEADER ─── */
.ord-hero{background:linear-gradient(135deg,#1e1b4b,#2d2470,#4c1d95);border-radius:16px;padding:26px 28px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;position:relative;overflow:hidden}
.ord-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:32px 32px;pointer-events:none}
.ord-hero-left{position:relative;z-index:1}
.ord-hero-id{font-size:28px;font-weight:900;color:#fff;letter-spacing:-1px;margin-bottom:6px}
.ord-hero-shop{font-size:13px;color:rgba(255,255,255,.7);display:flex;align-items:center;gap:6px}
.hero-ico{width:14px;height:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.75}
.ord-hero-right{position:relative;z-index:1;text-align:right}
.ord-status{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:20px;font-size:13px;font-weight:800;margin-bottom:10px}
.ord-status.att{background:var(--abg);color:#92400e;border:1px solid rgba(245,158,11,.3)}
.ord-status.con{background:var(--bbg);color:#1d4ed8;border:1px solid rgba(59,130,246,.3)}
.ord-status.liv{background:var(--ibg);color:#3730a3;border:1px solid rgba(99,102,241,.3)}
.ord-status.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.3)}
.ord-status.ann{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.3)}
.st-ico{width:14px;height:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ord-date{font-size:12px;color:rgba(255,255,255,.55)}

/* ─── LAYOUT GRID ─── */
.detail-grid{display:grid;grid-template-columns:1fr 340px;gap:18px;align-items:start}

/* ─── CARDS ─── */
.dc{background:var(--card);border-radius:14px;border:1px solid var(--bd);overflow:hidden;margin-bottom:18px}
.dc-h{padding:14px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:8px}
.dc-ico{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--brand)}
.dc-t{font-size:13.5px;font-weight:800;color:var(--text)}
.dc-body{padding:18px 20px}

/* ─── INFO ROWS ─── */
.info-row{display:flex;align-items:flex-start;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--bd);gap:12px}
.info-row:last-child{border-bottom:none;padding-bottom:0}
.info-lbl{font-size:11.5px;color:var(--muted);font-weight:600;flex-shrink:0;min-width:110px}
.info-val{font-size:12.5px;color:var(--text);font-weight:600;text-align:right;display:flex;align-items:center;gap:5px;justify-content:flex-end}
.ic-sm{width:13px;height:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--muted)}

/* ─── ITEMS TABLE ─── */
.items-tbl{width:100%;border-collapse:collapse}
.items-tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:8px 14px;background:#f8fafc;border-bottom:1px solid var(--bd);text-align:left}
.items-tbl td{padding:12px 14px;font-size:12.5px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.items-tbl tr:last-child td{border-bottom:none}
.item-prod{display:flex;align-items:center;gap:9px}
.item-av{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--brand),#4f46e5);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
.item-img{width:34px;height:34px;border-radius:8px;object-fit:cover;border:1px solid var(--bd);flex-shrink:0}
.item-name{font-size:12.5px;font-weight:700;color:var(--text)}
.item-cat{font-size:10.5px;color:var(--muted);display:flex;align-items:center;gap:3px;margin-top:2px}
.cat-ico{width:11px;height:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.items-total{padding:12px 14px;border-top:2px solid var(--bd);display:flex;justify-content:flex-end;align-items:center;gap:8px}

/* ─── STATUS CHANGE FORM ─── */
.status-form{display:flex;flex-direction:column;gap:10px}
.f-sel{width:100%;padding:9px 12px;border-radius:9px;border:1px solid var(--bd);background:var(--bg);font-size:13px;color:var(--text);font-family:var(--font);outline:none;cursor:pointer}
.f-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.btn-save{width:100%;padding:10px;border-radius:9px;background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .15s;display:flex;align-items:center;justify-content:center;gap:7px}
.btn-save:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.btn-ico{width:14px;height:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* ─── TIMELINE ─── */
.timeline{display:flex;flex-direction:column;gap:0;padding:0}
.tl-step{display:flex;align-items:flex-start;gap:12px;padding:10px 0;position:relative}
.tl-step:not(:last-child)::after{content:'';position:absolute;left:15px;top:34px;width:2px;height:calc(100% - 10px);background:var(--bd)}
.tl-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;z-index:1}
.tl-dot.done{background:var(--gbg);border:2px solid rgba(16,185,129,.3);color:var(--green)}
.tl-dot.current{background:rgba(124,58,237,.15);border:2px solid rgba(124,58,237,.4);color:var(--brand);animation:pulse-tl 1.8s ease-in-out infinite}
.tl-dot.wait{background:#f1f5f9;border:2px solid var(--bd);color:var(--muted)}
.tl-dot.ann{background:var(--rbg);border:2px solid rgba(239,68,68,.3);color:var(--red)}
.tl-ico{width:15px;height:15px;display:flex;align-items:center;justify-content:center}
@keyframes pulse-tl{0%,100%{box-shadow:0 0 0 0 rgba(124,58,237,.3)}50%{box-shadow:0 0 0 6px rgba(124,58,237,0)}}
.tl-info{padding-top:5px}
.tl-lbl{font-size:12.5px;font-weight:700;color:var(--text)}
.tl-sub{font-size:11px;color:var(--muted);margin-top:1px}

/* ─── CHIPS ─── */
.chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700}
.chip.g{color:#065f46;background:var(--gbg);border:1px solid rgba(16,185,129,.2)}
.chip.a{color:#92400e;background:var(--abg);border:1px solid rgba(245,158,11,.2)}
.chip.r{color:#7f1d1d;background:var(--rbg);border:1px solid rgba(239,68,68,.2)}
.chip.m{color:var(--muted);background:rgba(100,116,139,.1);border:1px solid rgba(100,116,139,.2)}
.chip-ico{width:11px;height:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* ─── STARS ─── */
.stars{display:flex;align-items:center;gap:2px;margin-bottom:6px}
.star-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center}

/* ─── RESPONSIVE ─── */
@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}
    .sb-ov.open{display:block}.mn{margin-left:0}.ham{display:flex}
    .detail-grid{grid-template-columns:1fr}
}
@media(max-width:600px){.con{padding:13px}.tb{padding:0 13px}.ord-hero{padding:18px 20px}}
</style>
@endpush

@section('content')
@php
    $me     = auth()->user();
    $meName = $me->name ?? 'SuperAdmin';
    $meInit = strtoupper(substr($meName,0,1));

    $clientName  = $order->client->name ?? $order->client_phone ?? $order->delivery_destination ?? 'Client inconnu';
    $shopName    = $order->shop->name ?? '—';
    $ownerName   = $order->shop->owner->name ?? '—';
    $livreurName = $order->driver->name ?? $order->livreur->name ?? null;

    $cur = match(strtoupper($order->shop->currency ?? 'GNF')) {
        'GNF'  => 'GNF',  'XOF'  => 'FCFA', 'XAF'  => 'FCFA',
        'EUR'  => '€',    'USD'  => '$',     'GBP'  => '£',
        'MAD'  => 'MAD',  'DZD'  => 'DA',    'TND'  => 'TND',
        default => strtoupper($order->shop->currency ?? 'GNF'),
    };

    $s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
    $I = [
        'brand'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" '.$s.'/></svg>',
        'home'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" '.$s.'/><polyline points="9 22 9 12 15 12 15 22" '.$s.'/></svg>',
        'store'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" '.$s.'/><path d="M3 9l2.45-4.9A2 2 0 017.24 3h9.52a2 2 0 011.8 1.1L21 9" '.$s.'/><line x1="12" y1="3" x2="12" y2="9" '.$s.'/></svg>',
        'store_sm' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" '.$s.'/><path d="M3 9l2.45-4.9A2 2 0 017.24 3h9.52a2 2 0 011.8 1.1L21 9" '.$s.'/><line x1="12" y1="3" x2="12" y2="9" '.$s.'/></svg>',
        'bag'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" '.$s.'/><line x1="3" y1="6" x2="21" y2="6" '.$s.'/><path d="M16 10a4 4 0 01-8 0" '.$s.'/></svg>',
        'box'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" '.$s.'/><polyline points="3.27 6.96 12 12.01 20.73 6.96" '.$s.'/><line x1="12" y1="22.08" x2="12" y2="12" '.$s.'/></svg>',
        'box_sm'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" '.$s.'/><polyline points="3.27 6.96 12 12.01 20.73 6.96" '.$s.'/><line x1="12" y1="22.08" x2="12" y2="12" '.$s.'/></svg>',
        'brief'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="7" width="20" height="14" rx="2" ry="2" '.$s.'/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16" '.$s.'/></svg>',
        'users'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" '.$s.'/><circle cx="9" cy="7" r="4" '.$s.'/><path d="M23 21v-2a4 4 0 00-3-3.87" '.$s.'/><path d="M16 3.13a4 4 0 010 7.75" '.$s.'/></svg>',
        'user'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" '.$s.'/><circle cx="12" cy="7" r="4" '.$s.'/></svg>',
        'truck'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" '.$s.'/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8" '.$s.'/><circle cx="5.5" cy="18.5" r="2.5" '.$s.'/><circle cx="18.5" cy="18.5" r="2.5" '.$s.'/></svg>',
        'truck_sm' => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><rect x="1" y="3" width="15" height="13" '.$s.'/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8" '.$s.'/><circle cx="5.5" cy="18.5" r="2.5" '.$s.'/><circle cx="18.5" cy="18.5" r="2.5" '.$s.'/></svg>',
        'bike'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="5.5" cy="17.5" r="3.5" '.$s.'/><circle cx="18.5" cy="17.5" r="3.5" '.$s.'/><path d="M15 6h-3l-3 8h9" '.$s.'/><path d="M5.5 17.5L9 10l3 4" '.$s.'/><circle cx="15" cy="5" r="1" fill="currentColor"/></svg>',
        'bike_sm'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="5.5" cy="17.5" r="3.5" '.$s.'/><circle cx="18.5" cy="17.5" r="3.5" '.$s.'/><path d="M15 6h-3l-3 8h9" '.$s.'/><path d="M5.5 17.5L9 10l3 4" '.$s.'/></svg>',
        'map'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" '.$s.'/><line x1="8" y1="2" x2="8" y2="18" '.$s.'/><line x1="16" y1="6" x2="16" y2="22" '.$s.'/></svg>',
        'pin'      => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" '.$s.'/><circle cx="12" cy="10" r="3" '.$s.'/></svg>',
        'card'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="2" ry="2" '.$s.'/><line x1="1" y1="10" x2="23" y2="10" '.$s.'/></svg>',
        'trend'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18" '.$s.'/><polyline points="17 6 23 6 23 12" '.$s.'/></svg>',
        'dollar'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="12" y1="1" x2="12" y2="23" '.$s.'/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" '.$s.'/></svg>',
        'cog'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" '.$s.'/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" '.$s.'/></svg>',
        'cog_lg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" '.$s.'/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" '.$s.'/></svg>',
        'logout'   => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" '.$s.'/><polyline points="16 17 21 12 16 7" '.$s.'/><line x1="21" y1="12" x2="9" y2="12" '.$s.'/></svg>',
        'menu'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><line x1="3" y1="6" x2="21" y2="6" '.$s.'/><line x1="3" y1="12" x2="21" y2="12" '.$s.'/><line x1="3" y1="18" x2="21" y2="18" '.$s.'/></svg>',
        'close'    => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" '.$s.'/><line x1="6" y1="6" x2="18" y2="18" '.$s.'/></svg>',
        'arrow_l'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><polyline points="15 18 9 12 15 6" '.$s.'/></svg>',
        'check'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" '.$s.'/></svg>',
        'check_sm' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" '.$s.'/></svg>',
        'clock'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><polyline points="12 6 12 12 16 14" '.$s.'/></svg>',
        'clock_sm' => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><polyline points="12 6 12 12 16 14" '.$s.'/></svg>',
        'x_sm'     => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" '.$s.'/><line x1="6" y1="6" x2="18" y2="18" '.$s.'/></svg>',
        'cart'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="9" cy="21" r="1" '.$s.'/><circle cx="20" cy="21" r="1" '.$s.'/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-7.43H6" '.$s.'/></svg>',
        'phone'    => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.67 9.5a19.79 19.79 0 01-3.07-8.63A2 2 0 012.47 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-.81a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" '.$s.'/></svg>',
        'folder'   => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z" '.$s.'/></svg>',
        'save'     => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" '.$s.'/><polyline points="17 21 17 13 7 13 7 21" '.$s.'/><polyline points="7 3 7 8 15 8" '.$s.'/></svg>',
        'receipt'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polyline points="9 11 12 14 22 4" '.$s.'/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" '.$s.'/></svg>',
        'user_lg'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" '.$s.'/><circle cx="12" cy="7" r="4" '.$s.'/></svg>',
        'star'     => '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'star_e'   => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" '.$s.'/></svg>',
        'star_lg'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" '.$s.'/></svg>',
    ];

    $statusMap = [
        'en_attente'   => ['cls'=>'att','svg'=>$I['clock_sm'],'lbl'=>'En attente'],
        'confirmée'    => ['cls'=>'con','svg'=>$I['check'],   'lbl'=>'Confirmée'],
        'en_livraison' => ['cls'=>'liv','svg'=>$I['bike_sm'], 'lbl'=>'En livraison'],
        'livrée'       => ['cls'=>'ok', 'svg'=>$I['box_sm'],  'lbl'=>'Livrée'],
        'annulée'      => ['cls'=>'ann','svg'=>$I['x_sm'],    'lbl'=>'Annulée'],
    ];
    $st = $statusMap[$order->status] ?? ['cls'=>'att','svg'=>$I['clock_sm'],'lbl'=>$order->status];

    $steps = [
        ['status'=>'en_attente',  'svg'=>$I['clock_sm'],'lbl'=>'En attente',   'sub'=>'Commande reçue, non confirmée'],
        ['status'=>'confirmée',   'svg'=>$I['check_sm'],'lbl'=>'Confirmée',    'sub'=>'Boutique a validé la commande'],
        ['status'=>'en_livraison','svg'=>$I['bike_sm'],  'lbl'=>'En livraison', 'sub'=>'Livreur en route'],
        ['status'=>'livrée',      'svg'=>$I['box_sm'],   'lbl'=>'Livrée',       'sub'=>'Client a reçu sa commande'],
    ];
    $curIdx = array_search($order->status, array_column($steps,'status'));
@endphp

<div class="sa">

{{-- SIDEBAR --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap">{!! $I['brand'] !!}</div>
        <div><div class="sb-appname">{{ config('app.name','Shopio') }}</div><div class="sb-apptag">Super Admin</div></div>
        <button class="sb-close" onclick="closeSb()" title="Fermer">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0"><div class="sb-name">{{ Str::limit($meName,22) }}</div><div class="sb-badge">Fondateur</div></div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a on"><span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">{!! $I['users'] !!}</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span></a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span></a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a"><span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span></a>
        <div class="sb-sec fin">── Finance</div>
        <a href="#" class="sb-a" onclick="return false"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span></a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="#" class="sb-a" onclick="return false"><span class="sb-i">{!! $I['users'] !!}</span><span>Utilisateurs</span></a>
        <a href="#" class="sb-a" onclick="return false"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['user'] !!}</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf<button type="submit" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion</button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- MAIN --}}
<div class="mn">
    <header class="tb">
        <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
        <div class="tb-ttl">
            <span class="tb-ico" style="color:var(--brand)">{!! $I['box'] !!}</span>
            Commande <b>#{{ $order->id }}</b>
        </div>
        <div class="tb-sp"></div>
        <a href="{{ route('admin.orders.index') }}" class="tb-btn">
            <span class="tb-ico">{!! $I['arrow_l'] !!}</span>Retour
        </a>
    </header>

    <div class="con">

        @if(session('success'))
            <div class="flash ok">
                <span class="flash-ico" style="color:var(--green)">{!! $I['check'] !!}</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="bc">
            <span class="bc-ico">{!! $I['brand'] !!}</span>
            <span class="bs">›</span>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="bs">›</span>
            <a href="{{ route('admin.orders.index') }}">Commandes</a>
            <span class="bs">›</span>
            <span style="color:var(--text);font-weight:600">#{{ $order->id }}</span>
        </div>

        {{-- Order hero --}}
        <div class="ord-hero">
            <div class="ord-hero-left">
                <div class="ord-hero-id">Commande #{{ $order->id }}</div>
                <div class="ord-hero-shop">
                    <span class="hero-ico">{!! $I['store_sm'] !!}</span>
                    {{ $shopName }}
                    &nbsp;·&nbsp;
                    <span class="hero-ico">{!! $I['user'] !!}</span>
                    {{ $ownerName }}
                </div>
            </div>
            <div class="ord-hero-right">
                <div>
                    <span class="ord-status {{ $st['cls'] }}">
                        <span class="st-ico">{!! $st['svg'] !!}</span>
                        {{ $st['lbl'] }}
                    </span>
                </div>
                <div class="ord-date">
                    Passée le {{ optional($order->created_at)->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>

        <div class="detail-grid">

            {{-- ── COLONNE GAUCHE ── --}}
            <div>

                {{-- Articles commandés --}}
                <div class="dc">
                    <div class="dc-h">
                        <span class="dc-ico">{!! $I['cart'] !!}</span>
                        <div class="dc-t">Articles commandés ({{ $order->items->count() }})</div>
                    </div>
                    <table class="items-tbl">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th style="text-align:center">Qté</th>
                                <th style="text-align:right">Prix unit.</th>
                                <th style="text-align:right">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            @php
                                $pName = $item->product->name ?? 'Produit supprimé';
                                $pInit = strtoupper(substr($pName,0,1));
                                $pCat  = $item->product->category ?? '';
                            @endphp
                            <tr>
                                <td>
                                    <div class="item-prod">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset('storage/'.$item->product->image) }}" class="item-img" alt="">
                                        @else
                                            <div class="item-av">{{ $pInit }}</div>
                                        @endif
                                        <div>
                                            <div class="item-name">{{ $pName }}</div>
                                            @if($pCat)
                                            <div class="item-cat">
                                                <span class="cat-ico">{!! $I['folder'] !!}</span>
                                                {{ $pCat }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align:center;font-weight:800;color:var(--brand)">× {{ $item->quantity }}</td>
                                <td style="text-align:right;font-weight:600">{{ number_format($item->price,0,',',' ') }} {{ $cur }}</td>
                                <td style="text-align:right;font-weight:800">{{ number_format($item->price * $item->quantity,0,',',' ') }} {{ $cur }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="items-total">
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
                            <div style="font-size:12px;color:var(--muted)">Sous-total articles : <strong>{{ number_format($order->items->sum(fn($i)=>$i->price*$i->quantity),0,',',' ') }} {{ $cur }}</strong></div>
                            @if($order->delivery_fee)
                                <div style="font-size:12px;color:var(--muted)">Frais de livraison : <strong>{{ number_format($order->delivery_fee,0,',',' ') }} {{ $cur }}</strong></div>
                            @endif
                            <div style="font-size:16px;font-weight:900;color:var(--text);border-top:1px solid var(--bd);padding-top:6px;margin-top:2px">
                                Total : {{ number_format($order->total,0,',',' ') }} {{ $cur }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Infos client --}}
                <div class="dc">
                    <div class="dc-h">
                        <span class="dc-ico">{!! $I['user_lg'] !!}</span>
                        <div class="dc-t">Informations client</div>
                    </div>
                    <div class="dc-body">
                        <div class="info-row">
                            <span class="info-lbl">Nom</span>
                            <span class="info-val">{{ $clientName }}</span>
                        </div>
                        @if($order->client)
                        <div class="info-row">
                            <span class="info-lbl">Email</span>
                            <span class="info-val">{{ $order->client->email ?? '—' }}</span>
                        </div>
                        @endif
                        @if($order->client_phone)
                        <div class="info-row">
                            <span class="info-lbl">Téléphone</span>
                            <span class="info-val">
                                <span class="ic-sm">{!! $I['phone'] !!}</span>
                                {{ $order->client_phone }}
                            </span>
                        </div>
                        @endif
                        @if($order->delivery_destination)
                        <div class="info-row">
                            <span class="info-lbl">Adresse livraison</span>
                            <span class="info-val" style="max-width:200px;text-align:right">
                                <span class="ic-sm">{!! $I['pin'] !!}</span>
                                {{ $order->delivery_destination }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Livraison --}}
                @if($livreurName || $order->deliveryCompany || $order->deliveryZone)
                <div class="dc">
                    <div class="dc-h">
                        <span class="dc-ico">{!! $I['bike'] !!}</span>
                        <div class="dc-t">Informations livraison</div>
                    </div>
                    <div class="dc-body">
                        @if($order->deliveryCompany)
                        <div class="info-row">
                            <span class="info-lbl">Entreprise</span>
                            <span class="info-val">
                                <span class="ic-sm">{!! $I['truck_sm'] !!}</span>
                                {{ $order->deliveryCompany->name }}
                            </span>
                        </div>
                        @endif
                        @if($livreurName)
                        <div class="info-row">
                            <span class="info-lbl">Livreur</span>
                            <span class="info-val">
                                <span class="ic-sm">{!! $I['bike_sm'] !!}</span>
                                {{ $livreurName }}
                            </span>
                        </div>
                        @endif
                        @if($order->deliveryZone)
                        <div class="info-row">
                            <span class="info-lbl">Zone</span>
                            <span class="info-val">
                                <span class="ic-sm">{!! $I['pin'] !!}</span>
                                {{ $order->deliveryZone->name }}
                            </span>
                        </div>
                        @endif
                        @if($order->delivery_fee)
                        <div class="info-row">
                            <span class="info-lbl">Frais livraison</span>
                            <span class="info-val">{{ number_format($order->delivery_fee,0,',',' ') }} {{ $cur }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Paiement --}}
                @if($order->payment)
                <div class="dc">
                    <div class="dc-h">
                        <span class="dc-ico">{!! $I['card'] !!}</span>
                        <div class="dc-t">Paiement</div>
                    </div>
                    <div class="dc-body">
                        <div class="info-row">
                            <span class="info-lbl">Montant</span>
                            <span class="info-val">{{ number_format($order->payment->amount ?? $order->total,0,',',' ') }} {{ $cur }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-lbl">Méthode</span>
                            <span class="info-val">{{ $order->payment->method ?? '—' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-lbl">Statut</span>
                            <span class="info-val">
                                @php $ps = $order->payment->status ?? 'pending'; @endphp
                                @if($ps === 'paid' || $ps === 'payé')
                                    <span class="chip g">
                                        <span class="chip-ico">{!! $I['check_sm'] !!}</span>Payé
                                    </span>
                                @elseif($ps === 'pending' || $ps === 'en_attente')
                                    <span class="chip a">
                                        <span class="chip-ico">{!! $I['clock_sm'] !!}</span>En attente
                                    </span>
                                @else
                                    <span class="chip m">{{ $ps }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- ── COLONNE DROITE ── --}}
            <div>

                {{-- Changer le statut --}}
                <div class="dc">
                    <div class="dc-h">
                        <span class="dc-ico">{!! $I['cog_lg'] !!}</span>
                        <div class="dc-t">Modifier le statut</div>
                    </div>
                    <div class="dc-body">
                        <form method="POST" action="{{ route('admin.orders.status',$order) }}" class="status-form">
                            @csrf
                            <select name="status" class="f-sel">
                                @foreach($statusMap as $val => $info)
                                    <option value="{{ $val }}" {{ $order->status===$val ? 'selected' : '' }}>
                                        {{ $info['lbl'] }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-save">
                                <span class="btn-ico">{!! $I['save'] !!}</span>
                                Mettre à jour le statut
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Timeline progression --}}
                <div class="dc">
                    <div class="dc-h">
                        <span class="dc-ico">{!! $I['receipt'] !!}</span>
                        <div class="dc-t">Progression</div>
                    </div>
                    <div class="dc-body" style="padding:14px 20px">
                        <div class="timeline">
                            @foreach($steps as $idx => $step)
                            @php
                                $isDone    = $curIdx !== false && $idx < $curIdx;
                                $isCurrent = $curIdx !== false && $idx === $curIdx;
                                $dotCls    = $isDone ? 'done' : ($isCurrent ? 'current' : 'wait');
                                $lblColor  = $isCurrent ? 'var(--brand)' : ($isDone ? 'var(--green)' : 'var(--muted)');
                            @endphp
                            <div class="tl-step">
                                <div class="tl-dot {{ $dotCls }}">
                                    <span class="tl-ico">
                                        {!! $isDone ? $I['check_sm'] : $step['svg'] !!}
                                    </span>
                                </div>
                                <div class="tl-info">
                                    <div class="tl-lbl" style="color:{{ $lblColor }}">{{ $step['lbl'] }}</div>
                                    <div class="tl-sub">{{ $step['sub'] }}</div>
                                </div>
                            </div>
                            @endforeach
                            @if($order->status === 'annulée')
                            <div class="tl-step">
                                <div class="tl-dot ann">
                                    <span class="tl-ico">{!! $I['x_sm'] !!}</span>
                                </div>
                                <div class="tl-info">
                                    <div class="tl-lbl" style="color:var(--red)">Annulée</div>
                                    <div class="tl-sub">Commande annulée</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Récapitulatif boutique --}}
                <div class="dc">
                    <div class="dc-h">
                        <span class="dc-ico">{!! $I['store'] !!}</span>
                        <div class="dc-t">Boutique</div>
                    </div>
                    <div class="dc-body">
                        <div class="info-row">
                            <span class="info-lbl">Boutique</span>
                            <span class="info-val">{{ $shopName }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-lbl">Propriétaire</span>
                            <span class="info-val">{{ $ownerName }}</span>
                        </div>
                        @if($order->shop && $order->shop->phone)
                        <div class="info-row">
                            <span class="info-lbl">Téléphone</span>
                            <span class="info-val">{{ $order->shop->phone }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Avis client --}}
                @if($order->review)
                <div class="dc">
                    <div class="dc-h">
                        <span class="dc-ico">{!! $I['star_lg'] !!}</span>
                        <div class="dc-t">Avis client</div>
                    </div>
                    <div class="dc-body">
                        <div class="stars">
                            @for($i=1;$i<=5;$i++)
                                <span class="star-ico" style="color:{{ $i <= ($order->review->rating ?? 0) ? '#f59e0b' : '#cbd5e1' }}">
                                    {!! $i <= ($order->review->rating ?? 0) ? $I['star'] : $I['star_e'] !!}
                                </span>
                            @endfor
                            <span style="font-size:13px;font-weight:700;color:var(--text);margin-left:4px">{{ $order->review->rating ?? '—' }}/5</span>
                        </div>
                        @if($order->review->comment)
                            <p style="font-size:12.5px;color:var(--muted);margin:0;font-style:italic">"{{ $order->review->comment }}"</p>
                        @endif
                    </div>
                </div>
                @endif

            </div>

        </div>

    </div>{{-- /con --}}
</div>{{-- /mn --}}
</div>{{-- /sa --}}
@endsection

@push('scripts')
<script>
const sb=document.getElementById('sb'),ov=document.getElementById('sbOv'),mn=document.querySelector('.mn');
function closeSb(){sb.classList.remove('open');ov.classList.remove('open');sb.classList.add('closed');mn.classList.add('sb-closed')}
function toggleSb(){if(sb.classList.contains('closed')){sb.classList.remove('closed');mn.classList.remove('sb-closed')}else{sb.classList.toggle('open');ov.classList.toggle('open')}}
</script>
@endpush
