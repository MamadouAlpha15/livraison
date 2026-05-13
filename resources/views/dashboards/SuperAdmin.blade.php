@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    /* Brand violet */
    --brand:#7c3aed;--blt:#8b5cf6;--bdk:#5b21b6;--glow:rgba(124,58,237,.22);
    /* Sidebar */
    --sb:#1e1b4b;--sb-text:rgba(255,255,255,.88);--sb-w:268px;
    /* Content */
    --bg:#f1f5f9;--card:#fff;--bd:rgba(0,0,0,.07);
    --text:#0f172a;--muted:#64748b;
    /* Colors */
    --green:#10b981;--gbg:rgba(16,185,129,.1);
    --amber:#f59e0b;--abg:rgba(245,158,11,.1);
    --red:#ef4444;--rbg:rgba(239,68,68,.1);
    --blue:#3b82f6;--bbg:rgba(59,130,246,.1);
    --indigo:#6366f1;--ibg:rgba(99,102,241,.1);
    --rose:#f43f5e;--rosebg:rgba(244,63,94,.1);
    --teal:#14b8a6;--tealbg:rgba(20,184,166,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}

/* ─── SHELL ─── */
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;
    position:fixed;top:0;left:0;bottom:0;z-index:200;
    overflow-y:auto;overflow-x:hidden;
    transition:transform .28s cubic-bezier(.4,0,.2,1);
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}
.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}

.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;
    background:linear-gradient(135deg,#a78bfa,#7c3aed);
    display:flex;align-items:center;justify-content:center;font-size:19px;
    box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px;text-shadow:0 1px 3px rgba(0,0,0,.2)}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;
    text-transform:uppercase;letter-spacing:.9px;margin-top:2px}

.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:10px;flex-shrink:0;
    background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;
    background:linear-gradient(135deg,#a78bfa,#6d28d9);
    display:flex;align-items:center;justify-content:center;
    font-size:14px;font-weight:900;color:#fff;flex-shrink:0;
    border:2px solid rgba(196,181,253,.5);
    box-shadow:0 2px 8px rgba(124,58,237,.4)}
.sb-name{font-size:13px;font-weight:800;color:#fff;
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px;
    text-shadow:0 1px 3px rgba(0,0,0,.2)}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;
    color:#e9d5ff;background:rgba(167,139,250,.22);
    border:1px solid rgba(196,181,253,.4);
    padding:2px 8px;border-radius:20px;margin-top:3px;
    text-transform:uppercase;letter-spacing:.6px}

.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;
    text-transform:uppercase;letter-spacing:1.3px;padding:16px 18px 6px;
    display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}
.sb-sec.livr{color:#93c5fd}
.sb-sec.fin{color:#fcd34d}
.sb-sec.plat{color:#c4b5fd}

.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;
    color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;
    border-left:3px solid transparent;transition:all .15s;cursor:pointer;letter-spacing:.1px}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;
    box-shadow:inset 0 0 0 1px rgba(167,139,250,.15)}
.sb-i{width:18px;text-align:center;font-size:15px;flex-shrink:0}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}
.sb-pill.a{background:var(--amber)}
.sb-pill.g{background:var(--green)}

.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;
    background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;
    border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;
    background:none;border:none;cursor:pointer;font-family:var(--font);
    width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);
    animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}

/* ─── TOPBAR ─── */
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);
    display:flex;align-items:center;padding:0 22px;gap:12px;
    position:sticky;top:0;z-index:100;box-shadow:0 1px 0 var(--bd)}
.ham{display:flex;width:32px;height:32px;background:none;border:none;
    cursor:pointer;border-radius:7px;align-items:center;justify-content:center;
    color:var(--muted);font-size:18px;transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;
    background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);
    font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}
.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-srch{display:flex;align-items:center;background:var(--bg);border:1px solid var(--bd);
    border-radius:9px;padding:0 11px;gap:7px;height:34px;width:210px;transition:all .18s}
.tb-srch:focus-within{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09);background:#fff}
.tb-srch input{border:none;background:none;font-size:12.5px;color:var(--text);width:100%;outline:none;font-family:var(--font)}
.tb-srch input::placeholder{color:var(--muted)}
.tb-acts{display:flex;align-items:center;gap:6px}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;
    cursor:pointer;display:flex;align-items:center;justify-content:center;
    color:var(--muted);font-size:15px;transition:all .13s;text-decoration:none;position:relative}
.tb-btn:hover{background:var(--bg);color:var(--text)}
.ndot{position:absolute;top:6px;right:6px;width:7px;height:7px;border-radius:50%;background:var(--red);border:2px solid #fff}
.tb-user{display:flex;align-items:center;gap:8px;padding:4px 10px 4px 5px;
    border:1px solid var(--bd);border-radius:8px;cursor:pointer;background:none;
    position:relative;transition:all .13s}
.tb-user:hover{background:var(--bg)}
.tb-uav{width:27px;height:27px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--bdk));
    display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.tb-uname{font-size:12px;font-weight:600;color:var(--text)}
.tb-urole{font-size:9.5px;color:var(--muted)}
.drop{position:absolute;top:calc(100% + 7px);right:0;background:#fff;
    border:1px solid var(--bd);border-radius:11px;padding:7px;
    box-shadow:0 8px 32px rgba(0,0,0,.13);min-width:185px;z-index:300;
    display:none;flex-direction:column;gap:2px}
.drop.open{display:flex}
.drop-i{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;
    font-size:12.5px;color:var(--text);text-decoration:none;transition:background .13s;
    background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:500;width:100%}
.drop-i:hover{background:var(--bg)}
.drop-i.d{color:var(--red)}
.drop-i.d:hover{background:var(--rbg)}
.drop-sep{height:1px;background:var(--bd);margin:4px 0}

/* ─── CONTENT ─── */
.con{flex:1;padding:24px}

/* flash */
.flash{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:12.5px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash.err{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}

/* breadcrumb */
.bc{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}
.bc .bs{color:rgba(0,0,0,.15)}
.bc .bc{color:var(--text);font-weight:600}

/* page header */
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:19px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px}
.ph-sub{font-size:11.5px;color:var(--muted)}
.ph-acts{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

/* ─── SAAS TAB BANNER ─── */
.saas-banner{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px}
.saas-card{border-radius:14px;padding:18px 20px;border:1px solid;display:flex;align-items:center;gap:14px;cursor:default;transition:transform .18s}
.saas-card:hover{transform:translateY(-1px)}
.saas-card.shop{background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-color:rgba(16,185,129,.25)}
.saas-card.livr{background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:rgba(59,130,246,.25)}
.saas-card-ico{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.saas-card.shop .saas-card-ico{background:rgba(16,185,129,.15)}
.saas-card.livr .saas-card-ico{background:rgba(59,130,246,.15)}
.saas-card-title{font-size:13px;font-weight:800;color:var(--text)}
.saas-card-sub{font-size:11px;color:var(--muted);margin-top:2px}
.saas-card-stat{margin-left:auto;text-align:right;flex-shrink:0}
.saas-big{font-size:22px;font-weight:900;letter-spacing:-1px}
.saas-card.shop .saas-big{color:var(--green)}
.saas-card.livr .saas-big{color:var(--blue)}
.saas-small{font-size:10.5px;color:var(--muted)}

/* ─── KPI GRID ─── */
.kpi-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(185px,1fr));gap:12px;margin-bottom:22px}
.kpi{background:var(--card);border-radius:13px;padding:16px;border:1px solid var(--bd);
    position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s;cursor:default}
.kpi:hover{transform:translateY(-2px);box-shadow:0 5px 18px rgba(0,0,0,.08)}
.kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.kpi.p::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.kpi.g::before{background:linear-gradient(90deg,#10b981,#34d399)}
.kpi.a::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.kpi.b::before{background:linear-gradient(90deg,#3b82f6,#60a5fa)}
.kpi.r::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.kpi.i::before{background:linear-gradient(90deg,#6366f1,#818cf8)}
.kpi.t::before{background:linear-gradient(90deg,#14b8a6,#2dd4bf)}
.kpi.ro::before{background:linear-gradient(90deg,#f43f5e,#fb7185)}
.kpi-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:11px}
.kpi-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.kpi-ic.p{background:rgba(139,92,246,.12)}.kpi-ic.g{background:var(--gbg)}.kpi-ic.a{background:var(--abg)}
.kpi-ic.b{background:var(--bbg)}.kpi-ic.r{background:var(--rbg)}.kpi-ic.i{background:var(--ibg)}
.kpi-ic.t{background:var(--tealbg)}.kpi-ic.ro{background:var(--rosebg)}
.kpi-tr{display:flex;align-items:center;gap:3px;font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px}
.kpi-tr.up{color:var(--green);background:var(--gbg)}.kpi-tr.dn{color:var(--red);background:var(--rbg)}.kpi-tr.fl{color:var(--muted);background:rgba(100,116,139,.1)}
.kpi-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px}
.kpi-l{font-size:11.5px;color:var(--muted);font-weight:500}
.kpi-s{font-size:10px;color:rgba(100,116,139,.6);margin-top:2px}

/* ─── SECTION CARD ─── */
.sc{background:var(--card);border-radius:13px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.sc-h{padding:14px 20px 12px;border-bottom:1px solid var(--bd);
    display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.sc-t{font-size:13px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}
.sc-acts{display:flex;align-items:center;gap:6px}

/* badges */
.bdg{font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center}
.bdg.g{color:#065f46;background:var(--gbg)}.bdg.a{color:#92400e;background:var(--abg)}
.bdg.r{color:#7f1d1d;background:var(--rbg)}.bdg.p{color:var(--bdk);background:rgba(124,58,237,.1)}
.bdg.b{color:#1d4ed8;background:var(--bbg)}.bdg.m{color:var(--muted);background:rgba(100,116,139,.1)}
.bdg.t{color:#0f766e;background:var(--tealbg)}

/* ─── PENDING COMPANIES ─── */
.pc-row{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--bd);transition:background .13s}
.pc-row:last-child{border-bottom:none}
.pc-row:hover{background:rgba(124,58,237,.018)}
.pc-av{width:37px;height:37px;border-radius:9px;background:linear-gradient(135deg,var(--brand),#4f46e5);
    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.pc-info{flex:1;min-width:0}
.pc-name{font-size:12.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pc-meta{font-size:10.5px;color:var(--muted);margin-top:2px;display:flex;align-items:center;gap:7px;flex-wrap:wrap}
.pc-right{text-align:right;flex-shrink:0}
.pc-date{font-size:10px;color:var(--muted)}
.pc-btns{display:flex;align-items:center;gap:5px;margin-top:5px;justify-content:flex-end}
.btn-ok{display:inline-flex;align-items:center;gap:4px;padding:5px 11px;border-radius:7px;
    background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:11px;font-weight:700;
    border:none;cursor:pointer;font-family:var(--font);transition:all .13s}
.btn-ok:hover{transform:translateY(-1px);box-shadow:0 3px 10px rgba(16,185,129,.4)}
.pc-empty{padding:38px 20px;text-align:center}
.pc-empty-ico{font-size:32px;opacity:.28;margin-bottom:8px}
.pc-empty-t{font-size:12px;font-weight:600;color:var(--muted)}

/* ─── ACTIVITY FEED ─── */
.af-row{display:flex;align-items:flex-start;gap:10px;padding:11px 20px;transition:background .13s}
.af-row:hover{background:rgba(0,0,0,.015)}
.af-dot{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;margin-top:1px}
.af-dot.g{background:var(--gbg)}.af-dot.p{background:rgba(124,58,237,.1)}
.af-dot.a{background:var(--abg)}.af-dot.b{background:var(--bbg)}.af-dot.r{background:var(--rbg)}
.af-txt{font-size:12px;color:var(--text);line-height:1.5}
.af-time{font-size:10px;color:var(--muted);margin-top:1px}

/* ─── QUICK ACTIONS ─── */
.qa-g{display:grid;grid-template-columns:repeat(5,1fr);gap:9px;padding:14px}
.qa{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;
    padding:15px 8px;background:var(--bg);border-radius:10px;border:1px solid var(--bd);
    text-decoration:none;cursor:pointer;transition:all .18s;text-align:center}
.qa:hover{background:#fff;transform:translateY(-2px);box-shadow:0 4px 14px rgba(0,0,0,.08);border-color:rgba(124,58,237,.18)}
.qa-ico{font-size:20px;line-height:1}
.qa-l{font-size:10.5px;font-weight:700;color:var(--text);line-height:1.3}

/* ─── TABLE ─── */
.tbl-wrap{overflow-x:auto}
.tbl{width:100%;border-collapse:collapse}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;
    letter-spacing:.5px;padding:9px 18px;background:var(--bg);border-bottom:1px solid var(--bd);
    white-space:nowrap;text-align:left}
.tbl td{padding:11px 18px;font-size:12px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover{background:rgba(124,58,237,.02)}
.t-user{display:flex;align-items:center;gap:9px}
.t-av{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--brand),#4f46e5);
    display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.t-uname{font-weight:600;font-size:12px}
.t-uemail{font-size:10.5px;color:var(--muted)}
.role-bdg{font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;display:inline-block}
.role-bdg.admin{background:rgba(16,185,129,.1);color:#065f46}
.role-bdg.company{background:rgba(59,130,246,.1);color:#1d4ed8}
.role-bdg.livreur{background:rgba(99,102,241,.1);color:#3730a3}
.role-bdg.client{background:rgba(100,116,139,.1);color:var(--muted)}
.role-bdg.vendeur{background:rgba(245,158,11,.1);color:#92400e}
.role-bdg.superadmin{background:rgba(124,58,237,.1);color:var(--bdk)}

/* ─── PROGRESS BARS ─── */
.bl{padding:14px 20px;display:flex;flex-direction:column;gap:13px}
.bh{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:5px}
.blbl{font-size:12px;font-weight:600;color:var(--text)}
.bval{font-size:11px;color:var(--muted)}
.bt{height:5px;background:var(--bg);border-radius:3px;overflow:hidden}
.bf{height:100%;border-radius:3px;transition:width 1s cubic-bezier(.4,0,.2,1);width:0}

/* ─── CHART ─── */
.cw{height:150px;display:flex;align-items:flex-end;gap:4px;padding:12px 18px 26px;position:relative}
.c-axis{height:1px;background:var(--bd);position:absolute;bottom:26px;left:18px;right:18px}
.cb{flex:1;border-radius:4px 4px 0 0;min-width:0;transition:opacity .15s;position:relative;cursor:default}
.cb:hover{opacity:.78}
.cb-l{position:absolute;bottom:-15px;left:50%;transform:translateX(-50%);font-size:8.5px;color:var(--muted);white-space:nowrap}

/* ─── SYSTEM STATUS ─── */
.ss-row{display:flex;align-items:center;justify-content:space-between;padding:10px 20px;border-bottom:1px solid var(--bd)}
.ss-row:last-child{border-bottom:none}
.ss-n{font-size:12px;font-weight:600;color:var(--text)}

/* ─── BUTTONS ─── */
.btn-p{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border-radius:8px;
    background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:11.5px;font-weight:700;
    border:none;cursor:pointer;text-decoration:none;transition:all .13s;font-family:var(--font)}
.btn-p:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.btn-g{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border-radius:8px;
    background:var(--bg);color:var(--muted);font-size:11.5px;font-weight:700;
    border:1px solid var(--bd);cursor:pointer;text-decoration:none;transition:all .13s;font-family:var(--font)}
.btn-g:hover{background:#e2e8f0;color:var(--text)}
.btn-w{width:100%;justify-content:center}
.btn-sm-green{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;
    background:var(--gbg);color:#065f46;font-size:11px;font-weight:700;
    border:1px solid rgba(16,185,129,.2);cursor:pointer;text-decoration:none;font-family:var(--font);transition:all .13s}
.btn-sm-green:hover{background:rgba(16,185,129,.18)}

/* ─── GRID ─── */
.g2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:16px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
.g-21{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px}

/* ─── RESPONSIVE ─── */
@media(max-width:1200px){
    .g3{grid-template-columns:1fr 1fr}
    .g-21{grid-template-columns:1fr}
    .qa-g{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:900px){
    .sb{transform:translateX(-100%)}
    .sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}
    .sb-ov.open{display:block}
    .mn{margin-left:0}
    .ham{display:flex}
    .tb-srch{display:none}
    .kpi-g{grid-template-columns:repeat(2,1fr)}
    .g2{grid-template-columns:1fr}
    .saas-banner{grid-template-columns:1fr}
}
@media(max-width:640px){
    .con{padding:13px}
    .tb{padding:0 13px}
    .g3{grid-template-columns:1fr}
    .g4{grid-template-columns:repeat(2,1fr)}
    .ph{flex-direction:column}
    .qa-g{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:400px){.kpi-g{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $me     = auth()->user();
    $meName = $me->name ?? 'Fondateur';
    $meInit = strtoupper(substr($meName,0,1));
    $meEmail= $me->email ?? '';
    $totalP = $pendingCompanies->total();
@endphp

<div class="sa">

{{-- ════════════════ SIDEBAR ════════════════ --}}
<aside class="sb" id="sb">

    {{-- Logo --}}
    <div class="sb-logo">
        <div class="sb-ico-wrap">⚡</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()" title="Fermer la sidebar">✕</button>
    </div>

    {{-- Moi --}}
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0">
            <div class="sb-name">{{ Str::limit($meName,22) }}</div>
            <div class="sb-badge">Fondateur &amp; Développeur</div>
        </div>
    </div>

    <nav class="sb-nav">

        {{-- Global --}}
        <a href="{{ route('admin.dashboard') }}" class="sb-a on">
            <span class="sb-i">🏠</span><span>Vue d'ensemble</span>
        </a>

        {{-- ── SaaS BOUTIQUES ── --}}
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a">
            <span class="sb-i">🏪</span><span>Boutiques</span>
        </a>
        <a href="{{ route('admin.products.index') }}" class="sb-a">
            <span class="sb-i">🛍️</span><span>Produits</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a">
            <span class="sb-i">📦</span><span>Commandes boutiques</span>
        </a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a">
            <span class="sb-i">👔</span><span>Vendeurs &amp; Employés</span>
        </a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a">
            <span class="sb-i">🧑‍💼</span><span>Clients boutiques</span>
        </a>

        {{-- ── SaaS LIVRAISON ── --}}
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a">
            <span class="sb-i">🚚</span><span>Entreprises livraison</span>
            @if($totalP>0)<span class="sb-pill r">{{ $totalP }}</span>@endif
        </a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a">
            <span class="sb-i">🏍️</span><span>Livreurs</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">🗺️</span><span>Zones de livraison</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">📍</span><span>Suivi en temps réel</span>
        </a>

        {{-- ── FINANCE ── --}}
        <div class="sb-sec fin">── Finance</div>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">💳</span><span>Paiements</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">💹</span><span>Commissions</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">💰</span><span>Revenus plateforme</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">🧾</span><span>Factures &amp; Exports</span>
        </a>

        {{-- ── PLATEFORME ── --}}
        <div class="sb-sec plat">── Plateforme</div>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">👥</span><span>Tous les utilisateurs</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">📊</span><span>Rapports globaux</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">📈</span><span>Statistiques</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">⭐</span><span>Avis &amp; Notation</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">🎫</span><span>Tickets support</span>
            <span class="sb-pill a">–</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">🔔</span><span>Notifications</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">⚙️</span><span>Paramètres système</span>
        </a>

    </nav>

    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row">
            <span style="font-size:14px">👤</span>Mon profil
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-ft-row">
                <span style="font-size:14px">⎋</span>Déconnexion
            </button>
        </form>
    </div>

</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════════════ MAIN ════════════════ --}}
<div class="mn">

    <header class="tb">
        <button class="ham" onclick="toggleSb()">☰</button>
        <div class="tb-ttl">Super<b>Admin</b></div>
        <div class="tb-sp"></div>
        <div class="tb-srch">
            <span style="color:var(--muted);font-size:13px">🔍</span>
            <input type="text" placeholder="Rechercher boutique, user, commande…">
        </div>
        <div class="tb-acts">
            <button class="tb-btn" onclick="nt()" title="Notifications">
                🔔@if($totalP>0)<span class="ndot"></span>@endif
            </button>
            <a href="{{ route('profile.edit') }}" class="tb-btn" title="Profil">👤</a>
        </div>
        <div style="position:relative">
            <button class="tb-user" id="tbU" onclick="toggleDrop()">
                <div class="tb-uav">{{ $meInit }}</div>
                <div class="d-none d-sm-block" style="text-align:left">
                    <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                    <div class="tb-urole">SuperAdmin</div>
                </div>
                <span style="font-size:11px;color:var(--muted)">▾</span>
            </button>
            <div class="drop" id="drop">
                <a href="{{ route('profile.edit') }}" class="drop-i">👤 Mon profil</a>
                <a href="#" class="drop-i" onclick="nt();return false">⚙️ Paramètres</a>
                <div class="drop-sep"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf
                    <button type="submit" class="drop-i d">⎋ Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <div class="con">

        @if(session('success'))
            <div class="flash ok">✅ {{ session('success') }}</div>
        @endif
        @if(session('error')||session('danger'))
            <div class="flash err">❌ {{ session('error')??session('danger') }}</div>
        @endif

        <div class="bc">
            <span>⚡</span><span class="bs">›</span><span style="color:var(--text);font-weight:600">Vue d'ensemble</span>
        </div>

        <div class="ph">
            <div>
                <h1>Tableau de bord — Contrôle total</h1>
                <div class="ph-sub">
                    Bienvenue, <strong>{{ $meName }}</strong> ·
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }} ·
                    <span style="color:var(--brand);font-weight:600">Plateforme double SaaS</span>
                </div>
            </div>
            <div class="ph-acts">
                <a href="{{ route('admin.shops.index') }}" class="btn-g">🏪 Boutiques</a>
                <button class="btn-p" onclick="nt()">📊 Rapport global</button>
            </div>
        </div>

        {{-- ── DOUBLE SAAS BANNER ── --}}
        <div class="saas-banner">
            <div class="saas-card shop">
                <div class="saas-card-ico">🏪</div>
                <div>
                    <div class="saas-card-title">SaaS Boutiques</div>
                    <div class="saas-card-sub">Gestion des boutiques, produits, commandes, vendeurs &amp; clients</div>
                </div>
                <div class="saas-card-stat">
                    <div class="saas-big">–</div>
                    <div class="saas-small">boutiques actives</div>
                </div>
            </div>
            <div class="saas-card livr">
                <div class="saas-card-ico">🚚</div>
                <div>
                    <div class="saas-card-title">SaaS Livraison</div>
                    <div class="saas-card-sub">Gestion des entreprises, livreurs, zones &amp; suivi en direct</div>
                </div>
                <div class="saas-card-stat">
                    <div class="saas-big">–</div>
                    <div class="saas-small">entreprises actives</div>
                </div>
            </div>
        </div>

        {{-- ── KPI GLOBAUX ── --}}
        <div class="kpi-g">
            @php
            $ks=[
                ['ic'=>'👥','l'=>'Utilisateurs','v'=>'–','s'=>'Total inscrits','c'=>'p','tr'=>'up','tv'=>'+12%'],
                ['ic'=>'🏪','l'=>'Boutiques','v'=>'–','s'=>'SaaS boutiques','c'=>'g','tr'=>'up','tv'=>'+5%'],
                ['ic'=>'🚚','l'=>'Entreprises','v'=>'–','s'=>'SaaS livraison','c'=>'b','tr'=>'fl','tv'=>'0%'],
                ['ic'=>'🏍️','l'=>'Livreurs','v'=>'–','s'=>'Actifs','c'=>'i','tr'=>'up','tv'=>'+3%'],
                ['ic'=>'📦','l'=>'Commandes','v'=>'–','s'=>"Aujourd'hui",'c'=>'a','tr'=>'up','tv'=>'+8%'],
                ['ic'=>'⏳','l'=>'Approbations','v'=>$totalP,'s'=>'En attente','c'=>'r','tr'=>$totalP>0?'dn':'fl','tv'=>(string)$totalP],
                ['ic'=>'💰','l'=>'Revenus','v'=>'–','s'=>'Ce mois','c'=>'t','tr'=>'up','tv'=>'+18%'],
                ['ic'=>'⭐','l'=>'Note moy.','v'=>'–','s'=>'/5 — avis clients','c'=>'ro','tr'=>'up','tv'=>'+0.2'],
            ];
            @endphp
            @foreach($ks as $k)
            <div class="kpi {{ $k['c'] }}">
                <div class="kpi-top">
                    <div class="kpi-ic {{ $k['c'] }}">{{ $k['ic'] }}</div>
                    <div class="kpi-tr {{ $k['tr'] }}">
                        @if($k['tr']==='up')▲@elseif($k['tr']==='dn')▼@else→@endif
                        {{ $k['tv'] }}
                    </div>
                </div>
                <div class="kpi-v">{{ $k['v'] }}</div>
                <div class="kpi-l">{{ $k['l'] }}</div>
                <div class="kpi-s">{{ $k['s'] }}</div>
            </div>
            @endforeach
        </div>

        {{-- ── ACTIONS RAPIDES ── --}}
        <div class="sc" style="margin-bottom:16px">
            <div class="sc-h"><div class="sc-t">⚡ Actions rapides</div></div>
            <div class="qa-g">
                <a href="{{ route('admin.shops.index') }}" class="qa">
                    <span class="qa-ico">🏪</span><span class="qa-l">Boutiques</span>
                </a>
                <a href="{{ route('admin.entreprises.index') }}" class="qa">
                    <span class="qa-ico">🚚</span><span class="qa-l">Entreprises livraison</span>
                </a>
                <a href="#" class="qa" onclick="nt();return false">
                    <span class="qa-ico">👥</span><span class="qa-l">Utilisateurs</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="qa">
                    <span class="qa-ico">📦</span><span class="qa-l">Commandes</span>
                </a>
                <a href="#" class="qa" onclick="nt();return false">
                    <span class="qa-ico">🏍️</span><span class="qa-l">Livreurs</span>
                </a>
                <a href="#" class="qa" onclick="nt();return false">
                    <span class="qa-ico">💳</span><span class="qa-l">Paiements</span>
                </a>
                <a href="#" class="qa" onclick="nt();return false">
                    <span class="qa-ico">💹</span><span class="qa-l">Commissions</span>
                </a>
                <a href="#" class="qa" onclick="nt();return false">
                    <span class="qa-ico">📊</span><span class="qa-l">Rapports</span>
                </a>
                <a href="#" class="qa" onclick="nt();return false">
                    <span class="qa-ico">🎫</span><span class="qa-l">Support</span>
                </a>
                <a href="#" class="qa" onclick="nt();return false">
                    <span class="qa-ico">⚙️</span><span class="qa-l">Paramètres</span>
                </a>
            </div>
        </div>

        {{-- ── PENDING + ACTIVITY ── --}}
        <div class="g2">

            {{-- Entreprises en attente --}}
            <div class="sc">
                <div class="sc-h">
                    <div class="sc-t">
                        ⏳ Entreprises en attente
                        @if($totalP>0)
                            <span class="bdg r" style="margin-left:4px">{{ $totalP }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.entreprises.index', ['status'=>'pending']) }}" class="btn-sm-green">Tout voir</a>
                </div>
                @forelse($pendingCompanies as $co)
                    @php
                        $pts=explode(' ',$co->name??'X');
                        $av=strtoupper(substr($pts[0],0,1)).strtoupper(substr($pts[1]??'',0,1));
                    @endphp
                    <div class="pc-row">
                        <div class="pc-av">{{ $av ?: '🏢' }}</div>
                        <div class="pc-info">
                            <div class="pc-name">{{ $co->name }}</div>
                            <div class="pc-meta">
                                @if($co->email)<span>📧 {{ $co->email }}</span>@endif
                                @if($co->phone)<span>📞 {{ $co->phone }}</span>@endif
                                @if($co->country)<span>🌍 {{ $co->country }}</span>@endif
                            </div>
                        </div>
                        <div class="pc-right">
                            <div class="pc-date">{{ optional($co->created_at)->diffForHumans() }}</div>
                            <div class="pc-btns">
                                <form method="POST" action="{{ route('admin.companies.approve',$co) }}" style="margin:0">
                                    @csrf
                                    <button type="submit" class="btn-ok">✓ Approuver</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="pc-empty">
                        <div class="pc-empty-ico">✅</div>
                        <div class="pc-empty-t">Aucune entreprise en attente</div>
                        <div style="font-size:11px;color:var(--muted);margin-top:3px">Tout est à jour.</div>
                    </div>
                @endforelse
                @if($pendingCompanies->hasPages())
                    <div style="padding:10px 20px;border-top:1px solid var(--bd)">
                        {{ $pendingCompanies->links() }}
                    </div>
                @endif
            </div>

            {{-- Activité récente --}}
            <div class="sc">
                <div class="sc-h">
                    <div class="sc-t">🕐 Activité récente</div>
                    <span class="bdg m">24h</span>
                </div>
                @php $acts=[
                    ['c'=>'g','ic'=>'🏪','t'=>'Nouvelle boutique enregistrée (SaaS)','h'=>'Il y a 5 min'],
                    ['c'=>'p','ic'=>'👤','t'=>'Nouvel utilisateur inscrit via Google','h'=>'Il y a 12 min'],
                    ['c'=>'a','ic'=>'🚚','t'=>'Entreprise de livraison soumise','h'=>'Il y a 1h'],
                    ['c'=>'b','ic'=>'📦','t'=>'Commande #1234 livrée avec succès','h'=>'Il y a 2h'],
                    ['c'=>'g','ic'=>'💳','t'=>'Commission payée — 3500 F CFA','h'=>'Il y a 3h'],
                    ['c'=>'r','ic'=>'⭐','t'=>'Avis négatif posté — 2/5','h'=>'Il y a 4h'],
                    ['c'=>'p','ic'=>'🏍️','t'=>'Nouveau livreur assigné','h'=>'Il y a 6h'],
                ]; @endphp
                @foreach($acts as $a)
                <div class="af-row">
                    <div class="af-dot {{ $a['c'] }}">{{ $a['ic'] }}</div>
                    <div>
                        <div class="af-txt">{{ $a['t'] }}</div>
                        <div class="af-time">{{ $a['h'] }}</div>
                    </div>
                </div>
                @endforeach
                <div style="padding:10px 20px;border-top:1px solid var(--bd)">
                    <button class="btn-g btn-w" onclick="nt()">Voir toute l'activité →</button>
                </div>
            </div>

        </div>

        {{-- ── STATS + CHART + SYSTEM ── --}}
        <div class="g3">

            {{-- Répartition double SaaS --}}
            <div class="sc">
                <div class="sc-h">
                    <div class="sc-t">📊 Répartition plateforme</div>
                </div>
                <div class="bl">
                    <div style="font-size:10.5px;font-weight:700;color:var(--green);padding:4px 0 6px;text-transform:uppercase;letter-spacing:.6px">SaaS Boutiques</div>
                    @php $brs1=[
                        ['l'=>'🏪 Boutiques actives','p'=>70,'gr'=>'#10b981,#34d399','v'=>'–'],
                        ['l'=>'🛍️ Clients boutiques','p'=>80,'gr'=>'#8b5cf6,#a78bfa','v'=>'–'],
                    ]; @endphp
                    @foreach($brs1 as $b)
                    <div>
                        <div class="bh"><span class="blbl">{{ $b['l'] }}</span><span class="bval">{{ $b['v'] }}</span></div>
                        <div class="bt"><div class="bf" data-w="{{ $b['p'] }}" style="background:linear-gradient(90deg,{{ $b['gr'] }})"></div></div>
                    </div>
                    @endforeach
                    <div style="font-size:10.5px;font-weight:700;color:var(--blue);padding:10px 0 6px;text-transform:uppercase;letter-spacing:.6px">SaaS Livraison</div>
                    @php $brs2=[
                        ['l'=>'🚚 Entreprises','p'=>45,'gr'=>'#3b82f6,#60a5fa','v'=>'–'],
                        ['l'=>'🏍️ Livreurs actifs','p'=>55,'gr'=>'#6366f1,#818cf8','v'=>'–'],
                    ]; @endphp
                    @foreach($brs2 as $b)
                    <div>
                        <div class="bh"><span class="blbl">{{ $b['l'] }}</span><span class="bval">{{ $b['v'] }}</span></div>
                        <div class="bt"><div class="bf" data-w="{{ $b['p'] }}" style="background:linear-gradient(90deg,{{ $b['gr'] }})"></div></div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Chart commandes + livraisons --}}
            <div class="sc">
                <div class="sc-h">
                    <div class="sc-t">📈 Activité / 7 jours</div>
                    <span class="bdg p">Aperçu</span>
                </div>
                <div style="display:flex;gap:14px;padding:10px 18px 4px">
                    <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted)">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#7c3aed;opacity:.7"></span>Commandes
                    </div>
                    <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted)">
                        <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#3b82f6;opacity:.7"></span>Livraisons
                    </div>
                </div>
                <div class="cw" id="chartWrap">
                    <div class="c-axis"></div>
                </div>
                <div style="padding:0 18px 11px;font-size:10.5px;color:var(--muted);text-align:center">
                    Données simulées — sera connecté à l'API
                </div>
            </div>

            {{-- Statut système --}}
            <div class="sc">
                <div class="sc-h"><div class="sc-t">🖥️ Statut système</div></div>
                @php $sys=[
                    ['n'=>'Application Laravel','c'=>'g','l'=>'● En ligne'],
                    ['n'=>'Base de données','c'=>'g','l'=>'● Connectée'],
                    ['n'=>'Cache Redis','c'=>'g','l'=>'● Actif'],
                    ['n'=>'Paiements','c'=>'a','l'=>'⏸ Sandbox'],
                    ['n'=>'Emails (SMTP)','c'=>'a','l'=>'⏸ À configurer'],
                    ['n'=>'Google OAuth','c'=>'g','l'=>'● Actif'],
                    ['n'=>'PDF / Excel','c'=>'g','l'=>'● Opérationnel'],
                ]; @endphp
                @foreach($sys as $s)
                <div class="ss-row">
                    <span class="ss-n">{{ $s['n'] }}</span>
                    <span class="bdg {{ $s['c'] }}">{{ $s['l'] }}</span>
                </div>
                @endforeach
                <div style="display:flex;align-items:center;gap:6px;padding:9px 20px;font-size:11px;color:var(--muted)">
                    <div class="live-dot"></div>Tous les services sont opérationnels
                </div>
            </div>

        </div>

    </div>{{-- /con --}}
</div>{{-- /mn --}}
</div>{{-- /sa --}}

{{-- Toast --}}
<div id="toast" style="position:fixed;bottom:22px;right:22px;background:#1e293b;color:#fff;padding:10px 17px;border-radius:10px;font-size:12px;font-weight:600;box-shadow:0 8px 28px rgba(0,0,0,.22);transform:translateY(80px);opacity:0;transition:all .27s cubic-bezier(.4,0,.2,1);pointer-events:none;z-index:9999;max-width:270px"></div>

@endsection

@push('scripts')
<script>
/* sidebar */
const sb=document.getElementById('sb'),ov=document.getElementById('sbOv'),mn=document.querySelector('.mn');
function closeSb(){sb.classList.remove('open');ov.classList.remove('open');sb.classList.add('closed');mn.classList.add('sb-closed')}
function toggleSb(){if(sb.classList.contains('closed')){sb.classList.remove('closed');mn.classList.remove('sb-closed')}else{sb.classList.toggle('open');ov.classList.toggle('open')}}

/* dropdown */
const dr=document.getElementById('drop');
function toggleDrop(){dr.classList.toggle('open')}
document.addEventListener('click',e=>{
    const b=document.getElementById('tbU');
    if(b&&!b.contains(e.target)&&!dr.contains(e.target))dr.classList.remove('open');
});

/* toast */
let _t;
function nt(msg='Bientôt disponible'){
    const el=document.getElementById('toast');
    el.textContent=msg;el.style.transform='translateY(0)';el.style.opacity='1';
    clearTimeout(_t);
    _t=setTimeout(()=>{el.style.transform='translateY(80px)';el.style.opacity='0';},2800);
}

/* progress bars */
setTimeout(()=>{document.querySelectorAll('.bf').forEach(el=>{el.style.width=el.dataset.w+'%'});},350);

/* animated chart — commandes (violet) + livraisons (bleu) */
(function(){
    const w=document.getElementById('chartWrap');if(!w)return;
    const days=['L','M','M','J','V','S','D'];
    const cmd=[38,58,47,76,52,88,65];
    const liv=[28,44,38,60,42,70,50];
    const maxH=100;
    days.forEach((d,i)=>{
        const wrap=document.createElement('div');
        wrap.style.cssText='flex:1;display:flex;align-items:flex-end;gap:2px;position:relative';
        // cmd bar
        const bc=document.createElement('div');
        bc.style.cssText=`flex:1;border-radius:3px 3px 0 0;height:0;background:#7c3aed;opacity:.68;transition:height 1s cubic-bezier(.4,0,.2,1) ${100+i*65}ms`;
        // liv bar
        const bl=document.createElement('div');
        bl.style.cssText=`flex:1;border-radius:3px 3px 0 0;height:0;background:#3b82f6;opacity:.62;transition:height 1s cubic-bezier(.4,0,.2,1) ${160+i*65}ms`;
        // label
        const lbl=document.createElement('div');lbl.className='cb-l';lbl.textContent=d;
        wrap.appendChild(bc);wrap.appendChild(bl);wrap.appendChild(lbl);
        w.appendChild(wrap);
        setTimeout(()=>{
            bc.style.height=((cmd[i]/100)*maxH)+'px';
            bl.style.height=((liv[i]/100)*maxH)+'px';
        },100);
    });
})();
</script>
@endpush
