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
    --rose:#f43f5e;--rosebg:rgba(244,63,94,.1);
    --teal:#14b8a6;--tealbg:rgba(20,184,166,.1);
    --font:'Segoe UI',system-ui,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;
    position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;
    transition:transform .28s cubic-bezier(.4,0,.2,1);
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}
.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0;color:#fff}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);
    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;
    border:2px solid rgba(196,181,253,.5);box-shadow:0 2px 8px rgba(124,58,237,.4)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge-role{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;background:rgba(167,139,250,.22);
    border:1px solid rgba(196,181,253,.4);padding:2px 8px;border-radius:20px;margin-top:3px;
    text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;
    padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;
    font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s;cursor:pointer}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;
    box-shadow:inset 0 0 0 1px rgba(167,139,250,.15)}
/* icône SVG sidebar — hérite la couleur du lien parent */
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}.sb-pill.g{background:var(--green)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:10px;padding:9px 11px;border-radius:8px;
    color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;
    cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-ft-row:hover .sb-ft-ico{opacity:1}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);
    border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.sb.closed{transform:translateX(-100%)}.mn.sb-closed{margin-left:0}

/* ─── TOPBAR ─── */
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);
    display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;
    align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-acts{display:flex;align-items:center;gap:6px}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;cursor:pointer;
    display:flex;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;
    text-decoration:none;position:relative}
.tb-btn:hover{background:var(--bg);color:var(--brand)}
.ndot{position:absolute;top:6px;right:6px;width:7px;height:7px;border-radius:50%;background:var(--red);border:2px solid #fff}
.tb-user{display:flex;align-items:center;gap:8px;padding:4px 10px 4px 5px;border:1px solid var(--bd);
    border-radius:8px;cursor:pointer;background:none;position:relative;transition:all .13s}
.tb-user:hover{background:var(--bg)}
.tb-uav{width:27px;height:27px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--bdk));
    display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.tb-uname{font-size:12px;font-weight:600;color:var(--text)}.tb-urole{font-size:9.5px;color:var(--muted)}
.drop{position:absolute;top:calc(100% + 7px);right:0;background:#fff;border:1px solid var(--bd);border-radius:11px;
    padding:7px;box-shadow:0 8px 32px rgba(0,0,0,.13);min-width:185px;z-index:300;display:none;flex-direction:column;gap:2px}
.drop.open{display:flex}
.drop-i{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:7px;font-size:12.5px;color:var(--text);
    text-decoration:none;transition:background .13s;background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:500;width:100%}
.drop-i:hover{background:var(--bg)}.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
.drop-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;opacity:.7;flex-shrink:0}
.drop-sep{height:1px;background:var(--bd);margin:4px 0}

/* live badge */
.live-badge{display:flex;align-items:center;gap:5px;font-size:11px;font-weight:700;color:var(--green);
    background:var(--gbg);border:1px solid rgba(16,185,129,.2);border-radius:20px;padding:4px 10px}
#liveTs{font-size:10px;color:var(--muted);font-weight:600}

/* ─── CONTENT ─── */
.con{flex:1;padding:24px}
.flash{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:12.5px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash.err{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}
.bc{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:19px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px}
.ph-sub{font-size:11.5px;color:var(--muted)}
.ph-acts{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

/* ─── SAAS BANNER ─── */
.saas-banner{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px}
.saas-card{border-radius:14px;padding:20px 22px;border:1px solid;cursor:default;transition:transform .18s}
.saas-card:hover{transform:translateY(-1px)}
.saas-card.shop{background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-color:rgba(16,185,129,.25)}
.saas-card.livr{background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:rgba(59,130,246,.25)}
.saas-card-head{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.saas-card-ico{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.saas-card.shop .saas-card-ico{background:rgba(16,185,129,.15);color:#059669}
.saas-card.livr .saas-card-ico{background:rgba(59,130,246,.15);color:#2563eb}
.saas-card-title{font-size:13.5px;font-weight:800;color:var(--text)}.saas-card-sub{font-size:11px;color:var(--muted);margin-top:2px}
.saas-nums{display:flex;gap:0;border-radius:10px;overflow:hidden;border:1px solid rgba(0,0,0,.06)}
.saas-num{flex:1;padding:11px 10px;text-align:center;background:rgba(255,255,255,.7)}
.saas-num+.saas-num{border-left:1px solid rgba(0,0,0,.06)}
.saas-num-v{font-size:22px;font-weight:900;letter-spacing:-1px;line-height:1;transition:all .3s}
.saas-card.shop .saas-num-v.main{color:var(--green)}.saas-card.livr .saas-num-v.main{color:var(--blue)}
.saas-num-v.today{color:var(--amber)}.saas-num-v.pending{color:var(--red)}
.saas-num-l{font-size:9.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-top:4px}
.saas-arrow{font-size:10px;margin-right:2px}

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
.kpi-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.kpi-ic.p{background:rgba(139,92,246,.12);color:#7c3aed}
.kpi-ic.g{background:var(--gbg);color:#059669}
.kpi-ic.a{background:var(--abg);color:#d97706}
.kpi-ic.b{background:var(--bbg);color:#2563eb}
.kpi-ic.r{background:var(--rbg);color:#dc2626}
.kpi-ic.i{background:var(--ibg);color:#4f46e5}
.kpi-ic.t{background:var(--tealbg);color:#0d9488}
.kpi-ic.ro{background:var(--rosebg);color:#e11d48}
.kpi-tr{display:flex;align-items:center;gap:3px;font-size:10px;font-weight:700;padding:2px 6px;border-radius:20px}
.kpi-tr.up{color:var(--green);background:var(--gbg)}.kpi-tr.dn{color:var(--red);background:var(--rbg)}.kpi-tr.fl{color:var(--muted);background:rgba(100,116,139,.1)}
.kpi-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px;transition:all .3s}
.kpi-l{font-size:11.5px;color:var(--muted);font-weight:500}
.kpi-s{font-size:10px;color:rgba(100,116,139,.6);margin-top:2px}
.kpi-flash{animation:kpiFlash .6s ease}
@keyframes kpiFlash{0%{background:rgba(124,58,237,.07)}100%{background:transparent}}

/* ─── SECTION CARD ─── */
.sc{background:var(--card);border-radius:13px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.sc-h{padding:14px 20px 12px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.sc-t{font-size:13px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}
.sc-ico{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* badges */
.bdg{font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center}
.bdg.g{color:#065f46;background:var(--gbg)}.bdg.a{color:#92400e;background:var(--abg)}
.bdg.r{color:#7f1d1d;background:var(--rbg)}.bdg.p{color:var(--bdk);background:rgba(124,58,237,.1)}
.bdg.b{color:#1d4ed8;background:var(--bbg)}.bdg.m{color:var(--muted);background:rgba(100,116,139,.1)}

/* ─── PENDING ─── */
.pc-row{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--bd);transition:background .13s}
.pc-row:last-child{border-bottom:none}.pc-row:hover{background:rgba(124,58,237,.018)}
.pc-av{width:37px;height:37px;border-radius:9px;background:linear-gradient(135deg,var(--brand),#4f46e5);
    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.pc-info{flex:1;min-width:0}
.pc-name{font-size:12.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pc-meta{font-size:10.5px;color:var(--muted);margin-top:2px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pc-meta-item{display:flex;align-items:center;gap:4px}
.pc-right{text-align:right;flex-shrink:0}
.pc-date{font-size:10px;color:var(--muted)}
.pc-btns{display:flex;align-items:center;gap:5px;margin-top:5px;justify-content:flex-end}
.btn-ok{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:7px;
    background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:11px;font-weight:700;
    border:none;cursor:pointer;font-family:var(--font);transition:all .13s}
.btn-ok:hover{transform:translateY(-1px);box-shadow:0 3px 10px rgba(16,185,129,.4)}
.btn-ok-ico{width:13px;height:13px;display:flex;align-items:center;justify-content:center}
.pc-empty{padding:38px 20px;text-align:center}
.pc-empty-ico{width:48px;height:48px;border-radius:50%;background:var(--gbg);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;color:var(--green)}
.pc-empty-t{font-size:12px;font-weight:600;color:var(--muted)}

/* ─── ACTIVITY ─── */
.af-row{display:flex;align-items:flex-start;gap:11px;padding:11px 20px;transition:background .13s}
.af-row:hover{background:rgba(0,0,0,.015)}
.af-dot{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.af-dot.g{background:var(--gbg);color:#059669}.af-dot.p{background:rgba(124,58,237,.1);color:#7c3aed}
.af-dot.a{background:var(--abg);color:#d97706}.af-dot.b{background:var(--bbg);color:#2563eb}.af-dot.r{background:var(--rbg);color:#dc2626}
.af-txt{font-size:12px;color:var(--text);line-height:1.5}
.af-time{font-size:10px;color:var(--muted);margin-top:1px}

/* ─── QUICK ACTIONS ─── */
.qa-g{display:grid;grid-template-columns:repeat(5,1fr);gap:9px;padding:14px}
.qa{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;padding:16px 8px;
    background:var(--bg);border-radius:10px;border:1px solid var(--bd);text-decoration:none;
    cursor:pointer;transition:all .18s;text-align:center;color:var(--text)}
.qa:hover{background:#fff;transform:translateY(-2px);box-shadow:0 4px 14px rgba(0,0,0,.08);border-color:rgba(124,58,237,.2);color:var(--brand)}
.qa-ico{width:26px;height:26px;display:flex;align-items:center;justify-content:center;
    border-radius:7px;background:rgba(124,58,237,.09);color:var(--brand);transition:all .18s}
.qa:hover .qa-ico{background:var(--brand);color:#fff}
.qa-l{font-size:10.5px;font-weight:700;line-height:1.3}

/* ─── PROGRESS BARS ─── */
.bl{padding:14px 20px;display:flex;flex-direction:column;gap:13px}
.bh{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:5px}
.blbl{font-size:12px;font-weight:600;color:var(--text)}.bval{font-size:11px;color:var(--muted)}
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
.btn-p{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;
    background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:11.5px;font-weight:700;
    border:none;cursor:pointer;text-decoration:none;transition:all .13s;font-family:var(--font)}
.btn-p:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}
.btn-g{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;
    background:var(--bg);color:var(--muted);font-size:11.5px;font-weight:700;
    border:1px solid var(--bd);cursor:pointer;text-decoration:none;transition:all .13s;font-family:var(--font)}
.btn-g:hover{background:#e2e8f0;color:var(--text)}
.btn-w{width:100%;justify-content:center}
.btn-sm-green{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;
    background:var(--gbg);color:#065f46;font-size:11px;font-weight:700;border:1px solid rgba(16,185,129,.2);
    cursor:pointer;text-decoration:none;font-family:var(--font);transition:all .13s}
.btn-sm-green:hover{background:rgba(16,185,129,.18)}

/* ─── GRID ─── */
.g2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:16px}

/* ─── RESPONSIVE ─── */
@media(max-width:1200px){.g3{grid-template-columns:1fr 1fr}}
@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}
    .sb-ov.open{display:block}.mn{margin-left:0}.ham{display:flex}
    .kpi-g{grid-template-columns:repeat(2,1fr)}.g2{grid-template-columns:1fr}
    .saas-banner{grid-template-columns:1fr}.live-badge{display:none}
}
@media(max-width:640px){
    .con{padding:13px}.tb{padding:0 13px}.g3{grid-template-columns:1fr}
    .ph{flex-direction:column}.qa-g{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:400px){.kpi-g{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
$me      = auth()->user();
$meName  = $me->name ?? 'Fondateur';
$meInit  = strtoupper(substr($meName, 0, 1));

$shopPct    = $kpis['shopsTotal']   > 0 ? round($kpis['shopsActive']      / $kpis['shopsTotal']  * 100) : 0;
$compPct    = $kpis['compTotal']    > 0 ? round($kpis['compActive']       / $kpis['compTotal']   * 100) : 0;
$orderDelPct= $kpis['ordersTotal']  > 0 ? round($kpis['ordersDelivered']  / $kpis['ordersTotal'] * 100) : 0;
$driverPct  = min(100, $kpis['driversTotal'] * 10);

/* ═══ BIBLIOTHÈQUE D'ICÔNES SVG PREMIUM ═══ */
$s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
$I = [
// ── Marque / Logo
'brand'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',

// ── Navigation
'home'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
'store'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
'bag'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
'box'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
'brief'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
'users'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
'user'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
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
'bell'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>',
'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
'clock'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
'bar'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
'wave'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
'globe'  => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
'check'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
'phone'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.34 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
'mail'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
'ok'     => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
'menu'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>',
'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
'chevron'=> '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>',
// ── Grandes versions (banner + KPI)
'store_lg'=> '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
'truck_lg'=> '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect width="7" height="7" x="14" y="12" rx="1"/><path d="M5 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/><path d="M15 19a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/></svg>',
'brand_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>',
'users_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
'bike_lg' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg>',
'box_lg'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
'clock_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
'dollar_lg'=>'<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
'ticket_lg'=>'<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3Z"/></svg>',
'check_lg'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
];
@endphp

<div class="sa">

{{-- ════════ SIDEBAR ════════ --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap">{!! $I['brand_lg'] !!}</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0">
            <div class="sb-name">{{ Str::limit($meName,22) }}</div>
            <div class="sb-badge-role">Fondateur &amp; Développeur</div>
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a on">
            <span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span>
        </a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span>
        </a>
        <a href="{{ route('admin.products.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['bag'] !!}</span><span>Produits</span>
        </a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['box'] !!}</span><span>Commandes boutiques</span>
        </a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['brief'] !!}</span><span>Vendeurs &amp; Employés</span>
        </a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['user'] !!}</span><span>Clients boutiques</span>
        </a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span>
            @if($kpis['pendingAppr']>0)<span class="sb-pill r">{{ $kpis['pendingAppr'] }}</span>@endif
        </a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['bike'] !!}</span><span>Livreurs</span>
        </a>
        <a href="{{ route('admin.zones.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['map'] !!}</span><span>Zones de livraison</span>
        </a>
        <a href="{{ route('admin.suivi.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['pin'] !!}</span><span>Suivi en temps réel</span>
        </a>
        <div class="sb-sec fin">── Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span>
        </a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span>
        </a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span>
        </a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span>
        </a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span>
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">{!! $I['receipt'] !!}</span><span>Factures &amp; Exports</span>
        </a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span>
        </a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span>
        </a>
        <a href="{{ route('admin.support.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['ticket'] !!}</span><span>Tickets support</span>
            @if($kpis['openTickets']>0)<span class="sb-pill a">{{ $kpis['openTickets'] }}</span>@endif
        </a>
        <a href="#" class="sb-a" onclick="nt();return false">
            <span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span>
        </a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11px;color:rgba(255,255,255,.5);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row">
            <span class="sb-ft-ico">{!! $I['user'] !!}</span>Mon profil
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
    <div class="tb-ttl">Super<b>Admin</b></div>
    <div class="tb-sp"></div>
    <div class="live-badge">
        <div class="live-dot"></div>
        <span>Temps réel</span>
        <span id="liveTs">{{ now()->format('H:i:s') }}</span>
    </div>
    <div class="tb-acts">
        <button class="tb-btn" onclick="nt()" title="Notifications" style="position:relative">
            {!! $I['bell'] !!}
            @if($kpis['pendingAppr']>0)<span class="ndot"></span>@endif
        </button>
        <a href="{{ route('profile.edit') }}" class="tb-btn" title="Profil">{!! $I['user'] !!}</a>
    </div>
    <div style="position:relative">
        <button class="tb-user" id="tbU" onclick="toggleDrop()">
            <div class="tb-uav">{{ $meInit }}</div>
            <div style="text-align:left">
                <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                <div class="tb-urole">SuperAdmin</div>
            </div>
            <span style="color:var(--muted);margin-left:2px">{!! $I['chevron'] !!}</span>
        </button>
        <div class="drop" id="drop">
            <a href="{{ route('profile.edit') }}" class="drop-i">
                <span class="drop-ico">{!! $I['user'] !!}</span>Mon profil
            </a>
            <div class="drop-sep"></div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="drop-i d">
                    <span class="drop-ico">{!! $I['logout'] !!}</span>Déconnexion
                </button>
            </form>
        </div>
    </div>
</header>

<div class="con">

    @if(session('success'))
    <div class="flash ok">{!! $I['check'] !!} {{ session('success') }}</div>
    @endif
    @if(session('error')||session('danger'))
    <div class="flash err">{{ session('error')??session('danger') }}</div>
    @endif

    <div class="bc">
        <span style="display:flex;align-items:center">{!! $I['brand'] !!}</span>
        <span style="color:rgba(0,0,0,.15)">›</span>
        <span style="color:var(--text);font-weight:600">Vue d'ensemble</span>
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
            <a href="{{ route('admin.shops.index') }}" class="btn-g">{!! $I['store'] !!} Boutiques</a>
            <a href="{{ route('admin.entreprises.index') }}" class="btn-g">{!! $I['truck'] !!} Entreprises</a>
        </div>
    </div>

    {{-- ── DOUBLE SAAS BANNER ── --}}
    <div class="saas-banner">
        <div class="saas-card shop">
            <div class="saas-card-head">
                <div class="saas-card-ico">{!! $I['store_lg'] !!}</div>
                <div>
                    <div class="saas-card-title">SaaS Boutiques</div>
                    <div class="saas-card-sub">{{ $kpis['clientsTotal'] }} clients · {{ $kpis['ordersTotal'] }} commandes</div>
                </div>
            </div>
            <div class="saas-nums">
                <div class="saas-num">
                    <div class="saas-num-v main" id="saasShopsTotal">{{ $kpis['shopsTotal'] }}</div>
                    <div class="saas-num-l">Total</div>
                </div>
                <div class="saas-num">
                    <div class="saas-num-v main" id="saasShopsActive">{{ $kpis['shopsActive'] }}</div>
                    <div class="saas-num-l">Actives</div>
                </div>
                <div class="saas-num">
                    <div class="saas-num-v today" id="saasShopsToday">
                        <span class="saas-arrow">▲</span>{{ $kpis['shopsApprovedToday'] }}
                    </div>
                    <div class="saas-num-l">Activées auj.</div>
                </div>
                <div class="saas-num">
                    <div class="saas-num-v pending" id="saasShopsPending">{{ $kpis['shopsPending'] }}</div>
                    <div class="saas-num-l">En attente</div>
                </div>
            </div>
        </div>
        <div class="saas-card livr">
            <div class="saas-card-head">
                <div class="saas-card-ico">{!! $I['truck_lg'] !!}</div>
                <div>
                    <div class="saas-card-title">SaaS Livraison</div>
                    <div class="saas-card-sub">{{ $kpis['driversTotal'] }} livreurs · {{ $kpis['ordersDelivered'] }} livraisons</div>
                </div>
            </div>
            <div class="saas-nums">
                <div class="saas-num">
                    <div class="saas-num-v main" id="saasCompTotal">{{ $kpis['compTotal'] }}</div>
                    <div class="saas-num-l">Total</div>
                </div>
                <div class="saas-num">
                    <div class="saas-num-v main" id="saasCompActive">{{ $kpis['compActive'] }}</div>
                    <div class="saas-num-l">Actives</div>
                </div>
                <div class="saas-num">
                    <div class="saas-num-v today" id="saasCompToday">
                        <span class="saas-arrow">▲</span>{{ $kpis['compApprovedToday'] }}
                    </div>
                    <div class="saas-num-l">Activées auj.</div>
                </div>
                <div class="saas-num">
                    <div class="saas-num-v pending" id="saasCompPending">{{ $kpis['compPending'] }}</div>
                    <div class="saas-num-l">En attente</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── KPI GLOBAUX ── --}}
    <div class="kpi-g">
        <div class="kpi p">
            <div class="kpi-top">
                <div class="kpi-ic p">{!! $I['users_lg'] !!}</div>
                <div class="kpi-tr up">▲ +{{ $kpis['usersToday'] }} auj.</div>
            </div>
            <div class="kpi-v" id="kUsersTotal">{{ $kpis['usersTotal'] }}</div>
            <div class="kpi-l">Utilisateurs</div>
            <div class="kpi-s">Total inscrits</div>
        </div>
        <div class="kpi i">
            <div class="kpi-top">
                <div class="kpi-ic i">{!! $I['bike_lg'] !!}</div>
                <div class="kpi-tr fl">→ inscrits</div>
            </div>
            <div class="kpi-v" id="kDrivers">{{ $kpis['driversTotal'] }}</div>
            <div class="kpi-l">Livreurs</div>
            <div class="kpi-s">Enregistrés</div>
        </div>
        <div class="kpi a">
            <div class="kpi-top">
                <div class="kpi-ic a">{!! $I['box_lg'] !!}</div>
                <div class="kpi-tr {{ $kpis['ordersToday']>0?'up':'fl' }}">▲ {{ $kpis['ordersToday'] }} auj.</div>
            </div>
            <div class="kpi-v" id="kOrdersTotal">{{ $kpis['ordersTotal'] }}</div>
            <div class="kpi-l">Commandes</div>
            <div class="kpi-s">{{ $kpis['ordersDelivered'] }} livrées</div>
        </div>
        <div class="kpi r">
            <div class="kpi-top">
                <div class="kpi-ic r">{!! $I['clock_lg'] !!}</div>
                <div class="kpi-tr {{ $kpis['pendingAppr']>0?'dn':'fl' }}">
                    {{ $kpis['pendingAppr']>0?'▼ '.$kpis['pendingAppr']:'→ 0' }}
                </div>
            </div>
            <div class="kpi-v" id="kPending">{{ $kpis['pendingAppr'] }}</div>
            <div class="kpi-l">Approbations</div>
            <div class="kpi-s">En attente</div>
        </div>
        <div class="kpi t">
            <div class="kpi-top">
                <div class="kpi-ic t">{!! $I['dollar_lg'] !!}</div>
                <div class="kpi-tr up">▲ ce mois</div>
            </div>
            <div class="kpi-v" id="kRevMonth" style="font-size:16px">{{ number_format($kpis['revMonth'],0,',',' ') }}</div>
            <div class="kpi-l">Revenus mois</div>
            <div class="kpi-s">Paiements encaissés</div>
        </div>
        <div class="kpi ro">
            <div class="kpi-top">
                <div class="kpi-ic ro">{!! $I['ticket_lg'] !!}</div>
                <div class="kpi-tr {{ $kpis['openTickets']>0?'dn':'g' }}">
                    {{ $kpis['openTickets']>0?'▼ '.$kpis['openTickets'].' ouverts':'✓ 0 ouvert' }}
                </div>
            </div>
            <div class="kpi-v" id="kTickets">{{ $kpis['openTickets'] }}</div>
            <div class="kpi-l">Tickets support</div>
            <div class="kpi-s">Ouverts</div>
        </div>
    </div>

    {{-- ── ACTIONS RAPIDES ── --}}
    <div class="sc" style="margin-bottom:16px">
        <div class="sc-h">
            <div class="sc-t">
                <span class="sc-ico" style="color:var(--brand)">{!! $I['brand'] !!}</span>
                Actions rapides
            </div>
        </div>
        <div class="qa-g">
            <a href="{{ route('admin.shops.index') }}" class="qa">
                <span class="qa-ico">{!! $I['store'] !!}</span><span class="qa-l">Boutiques</span>
            </a>
            <a href="{{ route('admin.entreprises.index') }}" class="qa">
                <span class="qa-ico">{!! $I['truck'] !!}</span><span class="qa-l">Entreprises</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="qa">
                <span class="qa-ico">{!! $I['users'] !!}</span><span class="qa-l">Utilisateurs</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="qa">
                <span class="qa-ico">{!! $I['box'] !!}</span><span class="qa-l">Commandes</span>
            </a>
            <a href="{{ route('admin.livreurs.index') }}" class="qa">
                <span class="qa-ico">{!! $I['bike'] !!}</span><span class="qa-l">Livreurs</span>
            </a>
            <a href="{{ route('admin.paiements.index') }}" class="qa">
                <span class="qa-ico">{!! $I['card'] !!}</span><span class="qa-l">Paiements</span>
            </a>
            <a href="{{ route('admin.commissions.index') }}" class="qa">
                <span class="qa-ico">{!! $I['trend'] !!}</span><span class="qa-l">Commissions</span>
            </a>
            <a href="{{ route('admin.revenus.index') }}" class="qa">
                <span class="qa-ico">{!! $I['dollar'] !!}</span><span class="qa-l">Revenus</span>
            </a>
            <a href="{{ route('admin.support.index') }}" class="qa">
                <span class="qa-ico">{!! $I['ticket'] !!}</span><span class="qa-l">Support</span>
            </a>
            <a href="{{ route('admin.zones.index') }}" class="qa">
                <span class="qa-ico">{!! $I['map'] !!}</span><span class="qa-l">Zones</span>
            </a>
        </div>
    </div>

    {{-- ── PENDING + ACTIVITY ── --}}
    <div class="g2">

        {{-- Entreprises en attente --}}
        <div class="sc">
            <div class="sc-h">
                <div class="sc-t">
                    <span class="sc-ico" style="color:var(--red)">{!! $I['clock'] !!}</span>
                    Entreprises en attente
                    @if($kpis['pendingAppr']>0)
                        <span class="bdg r">{{ $kpis['pendingAppr'] }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.entreprises.index', ['status'=>'pending']) }}" class="btn-sm-green">
                    {!! $I['truck'] !!} Tout voir
                </a>
            </div>
            @forelse($pendingCompanies as $co)
                @php
                    $pts = explode(' ', $co->name ?? 'X');
                    $av  = strtoupper(substr($pts[0],0,1)).strtoupper(substr($pts[1]??'',0,1));
                @endphp
                <div class="pc-row">
                    <div class="pc-av">{{ $av ?: '?' }}</div>
                    <div class="pc-info">
                        <div class="pc-name">{{ $co->name }}</div>
                        <div class="pc-meta">
                            @if($co->email)
                            <span class="pc-meta-item" style="color:var(--muted)">
                                {!! $I['mail'] !!} {{ $co->email }}
                            </span>
                            @endif
                            @if($co->phone)
                            <span class="pc-meta-item" style="color:var(--muted)">
                                {!! $I['phone'] !!} {{ $co->phone }}
                            </span>
                            @endif
                            @if($co->country)
                            <span class="pc-meta-item" style="color:var(--muted)">
                                {!! $I['globe'] !!} {{ $co->country }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="pc-right">
                        <div class="pc-date">{{ optional($co->created_at)->diffForHumans() }}</div>
                        <div class="pc-btns">
                            <form method="POST" action="{{ route('admin.companies.approve',$co) }}" style="margin:0">
                                @csrf
                                <button type="submit" class="btn-ok">
                                    <span class="btn-ok-ico">{!! $I['ok'] !!}</span> Approuver
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="pc-empty">
                    <div class="pc-empty-ico">{!! $I['check_lg'] !!}</div>
                    <div class="pc-empty-t">Aucune entreprise en attente</div>
                    <div style="font-size:11px;color:var(--muted);margin-top:3px">Tout est à jour.</div>
                </div>
            @endforelse
            @if($pendingCompanies->hasPages())
                <div style="padding:10px 20px;border-top:1px solid var(--bd)">{{ $pendingCompanies->links() }}</div>
            @endif
        </div>

        {{-- Activité récente --}}
        <div class="sc">
            <div class="sc-h">
                <div class="sc-t">
                    <span class="sc-ico" style="color:var(--brand)">{!! $I['wave'] !!}</span>
                    Activité récente
                </div>
                <span class="bdg m">En direct</span>
            </div>
            <div id="activityFeed">
                @foreach($activity as $a)
                <div class="af-row">
                    <div class="af-dot {{ $a['c'] }}">
                        @php
                            $actIco = match($a['ic']) {
                                '👤' => $I['user'],
                                '📦' => $I['box'],
                                '🚚' => $I['truck'],
                                '💳' => $I['card'],
                                '🏪' => $I['store'],
                                default => $I['wave'],
                            };
                        @endphp
                        {!! $actIco !!}
                    </div>
                    <div>
                        <div class="af-txt">{{ $a['t'] }}</div>
                        <div class="af-time">{{ $a['h'] }}</div>
                    </div>
                </div>
                @endforeach
                @if(count($activity)===0)
                <div style="padding:30px 20px;text-align:center;font-size:12px;color:var(--muted)">Aucune activité récente.</div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── STATS + CHART + SYSTEM ── --}}
    <div class="g3">

        {{-- Répartition plateforme --}}
        <div class="sc">
            <div class="sc-h">
                <div class="sc-t">
                    <span class="sc-ico" style="color:var(--brand)">{!! $I['bar'] !!}</span>
                    Répartition plateforme
                </div>
            </div>
            <div class="bl">
                <div style="font-size:10.5px;font-weight:700;color:var(--green);padding:4px 0 6px;text-transform:uppercase;letter-spacing:.6px">SaaS Boutiques</div>
                <div>
                    <div class="bh">
                        <span class="blbl">Boutiques actives</span>
                        <span class="bval" id="brShopsV">{{ $kpis['shopsActive'] }}/{{ $kpis['shopsTotal'] }}</span>
                    </div>
                    <div class="bt"><div class="bf" id="brShops" data-w="{{ $shopPct }}" style="background:linear-gradient(90deg,#10b981,#34d399)"></div></div>
                </div>
                <div>
                    <div class="bh">
                        <span class="blbl">Commandes livrées</span>
                        <span class="bval" id="brOrdersV">{{ $kpis['ordersDelivered'] }}/{{ $kpis['ordersTotal'] }}</span>
                    </div>
                    <div class="bt"><div class="bf" id="brOrders" data-w="{{ $orderDelPct }}" style="background:linear-gradient(90deg,#8b5cf6,#a78bfa)"></div></div>
                </div>
                <div style="font-size:10.5px;font-weight:700;color:var(--blue);padding:10px 0 6px;text-transform:uppercase;letter-spacing:.6px">SaaS Livraison</div>
                <div>
                    <div class="bh">
                        <span class="blbl">Entreprises approuvées</span>
                        <span class="bval" id="brCompV">{{ $kpis['compActive'] }}/{{ $kpis['compTotal'] }}</span>
                    </div>
                    <div class="bt"><div class="bf" id="brComp" data-w="{{ $compPct }}" style="background:linear-gradient(90deg,#3b82f6,#60a5fa)"></div></div>
                </div>
                <div>
                    <div class="bh">
                        <span class="blbl">Livreurs inscrits</span>
                        <span class="bval" id="brDriversV">{{ $kpis['driversTotal'] }}</span>
                    </div>
                    <div class="bt"><div class="bf" id="brDrivers" data-w="{{ min(100,$driverPct) }}" style="background:linear-gradient(90deg,#6366f1,#818cf8)"></div></div>
                </div>
            </div>
        </div>

        {{-- Chart 7 jours --}}
        <div class="sc">
            <div class="sc-h">
                <div class="sc-t">
                    <span class="sc-ico" style="color:var(--brand)">{!! $I['trend'] !!}</span>
                    Activité / 7 jours
                </div>
                <span class="bdg p">Réel</span>
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
                {{ $kpis['ordersTotal'] }} commandes · {{ $kpis['ordersDelivered'] }} livrées
            </div>
        </div>

        {{-- Statut système --}}
        <div class="sc">
            <div class="sc-h">
                <div class="sc-t">
                    <span class="sc-ico" style="color:var(--muted)">{!! $I['cog'] !!}</span>
                    Statut système
                </div>
            </div>
            @php $sys=[
                ['n'=>'Application Laravel','c'=>'g','l'=>'En ligne'],
                ['n'=>'Base de données','c'=>'g','l'=>'Connectée'],
                ['n'=>'Cache Redis','c'=>'g','l'=>'Actif'],
                ['n'=>'Paiements','c'=>'a','l'=>'Sandbox'],
                ['n'=>'Emails (SMTP)','c'=>'a','l'=>'À configurer'],
                ['n'=>'Google OAuth','c'=>'g','l'=>'Actif'],
                ['n'=>'PDF / Excel','c'=>'g','l'=>'Opérationnel'],
            ]; @endphp
            @foreach($sys as $s_)
            <div class="ss-row">
                <span class="ss-n">{{ $s_['n'] }}</span>
                <span class="bdg {{ $s_['c'] }}">{{ $s_['l'] }}</span>
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

<div id="toast" style="position:fixed;bottom:22px;right:22px;background:#1e293b;color:#fff;padding:10px 17px;border-radius:10px;font-size:12px;font-weight:600;box-shadow:0 8px 28px rgba(0,0,0,.22);transform:translateY(80px);opacity:0;transition:all .27s cubic-bezier(.4,0,.2,1);pointer-events:none;z-index:9999;max-width:270px"></div>

@endsection

@push('scripts')
<script>
/* ── Sidebar ── */
const sb=document.getElementById('sb'),ov=document.getElementById('sbOv'),mn=document.querySelector('.mn');
function closeSb(){sb.classList.remove('open');ov.classList.remove('open');sb.classList.add('closed');mn.classList.add('sb-closed')}
function toggleSb(){if(sb.classList.contains('closed')){sb.classList.remove('closed');mn.classList.remove('sb-closed')}else{sb.classList.toggle('open');ov.classList.toggle('open')}}

/* ── Dropdown ── */
const dr=document.getElementById('drop');
function toggleDrop(){dr.classList.toggle('open')}
document.addEventListener('click',e=>{
    const b=document.getElementById('tbU');
    if(b&&!b.contains(e.target)&&!dr.contains(e.target))dr.classList.remove('open');
});

/* ── Toast ── */
let _t;
function nt(msg='Bientôt disponible'){
    const el=document.getElementById('toast');
    el.textContent=msg;el.style.transform='translateY(0)';el.style.opacity='1';
    clearTimeout(_t);_t=setTimeout(()=>{el.style.transform='translateY(80px)';el.style.opacity='0'},2800);
}

/* ── Progress bars ── */
setTimeout(()=>{document.querySelectorAll('.bf').forEach(el=>{el.style.width=el.dataset.w+'%'})},350);

/* ── Chart 7 jours ── */
const _chartData=@json($chart);
function buildChart(data){
    const w=document.getElementById('chartWrap');if(!w)return;
    w.querySelectorAll('.cb-pair').forEach(e=>e.remove());
    const maxVal=Math.max(1,...data.map(d=>Math.max(d.orders,d.deliveries)));
    data.forEach(d=>{
        const wrap=document.createElement('div');
        wrap.className='cb-pair';
        wrap.style.cssText='flex:1;display:flex;align-items:flex-end;gap:2px;position:relative';
        const bc=document.createElement('div');
        bc.style.cssText='flex:1;border-radius:3px 3px 0 0;height:0;background:#7c3aed;opacity:.7;transition:height 1s cubic-bezier(.4,0,.2,1)';
        const bl=document.createElement('div');
        bl.style.cssText='flex:1;border-radius:3px 3px 0 0;height:0;background:#3b82f6;opacity:.6;transition:height 1s cubic-bezier(.4,0,.2,1)';
        const lbl=document.createElement('div');lbl.className='cb-l';lbl.textContent=d.label;
        wrap.appendChild(bc);wrap.appendChild(bl);wrap.appendChild(lbl);
        w.appendChild(wrap);
        requestAnimationFrame(()=>{
            bc.style.height=((d.orders/maxVal)*100)+'px';
            bl.style.height=((d.deliveries/maxVal)*100)+'px';
        });
    });
}
setTimeout(()=>buildChart(_chartData),200);

/* ── Polling temps réel (30s) ── */
const STATS_URL='{{ route("admin.dashboard.stats") }}';
function setVal(id,val){
    const el=document.getElementById(id);
    if(!el||el.textContent==String(val))return;
    el.textContent=val;
    el.closest('.kpi')?.classList.remove('kpi-flash');
    void el.closest('.kpi')?.offsetWidth;
    el.closest('.kpi')?.classList.add('kpi-flash');
}
function updateActivity(items){
    const feed=document.getElementById('activityFeed');
    if(!feed||!items.length)return;
    const icons={'👤':'user','📦':'box','🚚':'truck','💳':'card','🏪':'store'};
    // activité texte simple sans SVG dynamique
    feed.innerHTML=items.map(a=>`
        <div class="af-row">
            <div class="af-dot ${a.c}" style="font-size:12px">${a.ic}</div>
            <div>
                <div class="af-txt">${a.t}</div>
                <div class="af-time">${a.h}</div>
            </div>
        </div>`).join('');
}
function pollStats(){
    fetch(STATS_URL,{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json())
        .then(d=>{
            const k=d.kpis;
            setVal('kUsersTotal',k.usersTotal);
            setVal('kDrivers',k.driversTotal);
            setVal('kOrdersTotal',k.ordersTotal);
            setVal('kPending',k.pendingAppr);
            setVal('kRevMonth',k.revMonth.toLocaleString('fr-FR',{maximumFractionDigits:0}));
            setVal('kTickets',k.openTickets);
            setVal('saasShopsTotal',k.shopsTotal);
            setVal('saasShopsActive',k.shopsActive);
            setVal('saasShopsPending',k.shopsPending);
            const sst=document.getElementById('saasShopsToday');
            if(sst)sst.innerHTML='<span class="saas-arrow">▲</span>'+k.shopsApprovedToday;
            setVal('saasCompTotal',k.compTotal);
            setVal('saasCompActive',k.compActive);
            setVal('saasCompPending',k.compPending);
            const sct=document.getElementById('saasCompToday');
            if(sct)sct.innerHTML='<span class="saas-arrow">▲</span>'+k.compApprovedToday;
            const brSV=document.getElementById('brShopsV');if(brSV)brSV.textContent=k.shopsActive+'/'+k.shopsTotal;
            const brOV=document.getElementById('brOrdersV');if(brOV)brOV.textContent=k.ordersDelivered+'/'+k.ordersTotal;
            const brCV=document.getElementById('brCompV');if(brCV)brCV.textContent=k.compActive+'/'+k.compTotal;
            const brDV=document.getElementById('brDriversV');if(brDV)brDV.textContent=k.driversTotal;
            buildChart(d.chart);
            updateActivity(d.activity);
            const ts=document.getElementById('liveTs');if(ts)ts.textContent=d.ts;
        })
        .catch(()=>{});
    setTimeout(pollStats,30000);
}
setTimeout(pollStats,30000);
</script>
@endpush
