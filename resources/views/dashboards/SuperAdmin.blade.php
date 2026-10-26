@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
/* Palette Shopio : bleu #1F7CF6 / #1668D8 / #1260C9, nuit #0B1E3D / #132B52, ambre #F59E0B,
   encre #16162A / #45455E / #62627C, lignes #E3E8F2, fond #F6F9FF. Vert / ambre / rouge = états. */
:root{
    --brand:#1668D8;--blt:#1F7CF6;--bdk:#1260C9;--glow:rgba(22,104,216,.28);
    --sb:#0B1E3D;--sb2:#132B52;--sb-line:#1C3560;--sb-text:#C9D6EA;--sb-mut:#8FA6CC;--sb-w:268px;
    --bg:#F6F9FF;--card:#fff;--bd:#E3E8F2;--border:var(--bd);
    --text:#16162A;--text2:#45455E;--muted:#62627C;
    --b50:#EAF2FF;--b100:#D6E6FF;--sky:#7DB4FF;
    --green:#15803D;--gbg:#E7F6EC;
    --amber:#F59E0B;--abg:#FEF3E0;--aink:#92400E;
    --red:#B91C1C;--rbg:#FDECEC;
    --blue:#1F7CF6;--bbg:#EAF2FF;
    --indigo:#132B52;--ibg:#E6ECF5;
    --rose:#B91C1C;--rosebg:#FDECEC;
    --teal:#15803D;--tealbg:#E7F6EC;
    --font:'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased}
.sa{display:flex;min-height:100vh}

/* ─── SIDEBAR ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;
    position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;
    transition:transform .28s cubic-bezier(.22,1,.36,1);
    scrollbar-width:thin;scrollbar-color:var(--sb-line) transparent}
.sb::-webkit-scrollbar{width:3px}
.sb::-webkit-scrollbar-thumb{background:var(--sb-line);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid var(--sb-line);
    display:flex;align-items:center;gap:12px;flex-shrink:0}
.sb-ico-wrap{width:42px;height:42px;border-radius:12px;background:var(--blt);overflow:hidden;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff}
.sb-ico-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.sb-appname{font-size:17px;font-weight:800;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:11px;font-weight:600;color:var(--sb-mut);margin-top:2px}
.sb-me{margin:14px 14px 4px;padding:12px;border-radius:16px;background:var(--sb2);
    display:flex;align-items:center;gap:10px;flex-shrink:0}
.sb-av{width:38px;height:38px;border-radius:999px;background:var(--blt);
    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.sb-name{font-size:14px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px}
.sb-badge-role{display:inline-block;font-size:11px;font-weight:600;color:var(--sb-mut);margin-top:2px}
.sb-nav{padding:6px 14px 10px;flex:1;display:flex;flex-direction:column;gap:2px}
.sb-sec{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--sb-mut);
    padding:16px 10px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop,.sb-sec.livr,.sb-sec.fin,.sb-sec.plat{color:var(--sb-mut)}
.sb-a{display:flex;align-items:center;gap:12px;padding:10px 12px;color:var(--sb-text);text-decoration:none;
    font-size:14px;font-weight:600;border-radius:12px;transition:background-color .15s,color .15s;cursor:pointer}
.sb-a:hover{background:var(--sb2);color:#fff}
.sb-a.on{background:var(--blt);color:#fff;font-weight:700;box-shadow:0 8px 20px -10px rgba(31,124,246,.7)}
.sb-a:focus-visible{outline:3px solid var(--sky);outline-offset:2px}
/* icône SVG sidebar — hérite la couleur du lien parent */
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.9}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-pill{margin-left:auto;font-size:11px;font-weight:800;padding:2px 8px;border-radius:999px;color:var(--text)}
.sb-pill.r{background:#F87171;color:#fff}.sb-pill.a{background:var(--amber)}.sb-pill.g{background:#4ADE80}
.sb-ft{padding:12px 14px calc(14px + env(safe-area-inset-bottom,0px));border-top:1px solid var(--sb-line);flex-shrink:0}
.sb-ft-row{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:12px;
    color:var(--sb-text);font-size:13px;font-weight:600;background:none;border:none;
    cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:background-color .15s,color .15s}
.sb-ft-row:hover{background:var(--sb2);color:#fff}
form:last-child>.sb-ft-row{color:#FCA5A5}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-ft-row:hover .sb-ft-ico{opacity:1}
.live-dot{width:7px;height:7px;border-radius:50%;background:#4ADE80;animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(11,30,61,.5);z-index:199}
.sb-close{margin-left:auto;width:32px;height:32px;border-radius:999px;background:var(--sb2);
    border:none;color:var(--sb-text);cursor:pointer;display:none;align-items:center;justify-content:center;
    transition:background-color .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:var(--sb-line);color:#fff}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.sb.closed{transform:translateX(-100%)}.mn.sb-closed{margin-left:0}

/* ─── TOPBAR ─── */
.tb{height:64px;background:var(--card);border-bottom:1px solid var(--bd);
    display:flex;align-items:center;padding:0 24px;gap:12px;position:sticky;top:env(safe-area-inset-top,0px);z-index:100}
.ham{display:flex;width:40px;height:40px;background:none;border:none;cursor:pointer;border-radius:999px;
    align-items:center;justify-content:center;color:var(--text2);transition:background-color .15s;flex-shrink:0;box-shadow:inset 0 0 0 1.5px var(--bd)}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:16px;font-weight:800;color:var(--text);letter-spacing:-.01em}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-acts{display:flex;align-items:center;gap:8px}
.tb-btn{width:40px;height:40px;background:var(--card);border:0;box-shadow:inset 0 0 0 1.5px var(--bd);border-radius:999px;cursor:pointer;
    display:flex;align-items:center;justify-content:center;color:var(--text2);transition:background-color .15s,color .15s,box-shadow .15s;
    text-decoration:none;position:relative}
.tb-btn:hover{background:var(--b50);color:var(--brand);box-shadow:inset 0 0 0 1.5px var(--b100)}
.ndot{position:absolute;top:7px;right:7px;width:8px;height:8px;border-radius:50%;background:var(--red);border:2px solid #fff}
.tb-user{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border:0;box-shadow:inset 0 0 0 1.5px var(--bd);
    border-radius:999px;cursor:pointer;background:var(--card);position:relative;transition:background-color .15s;font-family:var(--font)}
.tb-user:hover{background:var(--bg)}
.tb-uav{width:32px;height:32px;border-radius:50%;background:var(--blt);
    display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff}
.tb-uname{font-size:13px;font-weight:700;color:var(--text)}.tb-urole{font-size:11px;color:var(--muted)}
.drop{position:absolute;top:calc(100% + 8px);right:0;background:var(--card);border:1px solid var(--bd);border-radius:16px;
    padding:8px;box-shadow:0 16px 40px -12px rgba(11,30,61,.3);min-width:200px;z-index:300;display:none;flex-direction:column;gap:2px}
.drop.open{display:flex}
.drop-i{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;font-size:13.5px;color:var(--text);
    text-decoration:none;transition:background-color .13s;background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:600;width:100%}
.drop-i:hover{background:var(--bg)}.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
.drop-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;opacity:.8;flex-shrink:0}
.drop-sep{height:1px;background:var(--bd);margin:4px 0}

/* live badge */
.live-badge{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--green);
    background:var(--gbg);border-radius:999px;padding:6px 12px}
.live-badge .live-dot{background:var(--green)}
#liveTs{font-size:11px;color:var(--muted);font-weight:600;font-variant-numeric:tabular-nums}

/* ─── CONTENT ─── */
.con{flex:1;padding:24px 28px 56px;max-width:1360px;width:100%}
.flash{display:flex;align-items:center;gap:10px;padding:14px 18px;border-radius:16px;margin-bottom:20px;font-size:14px;font-weight:700}
.flash.ok{background:var(--gbg);color:var(--green)}
.flash.err{background:var(--rbg);color:var(--red)}
.bc{display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--muted);margin-bottom:16px}
.bc>span:first-child{color:var(--brand)}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}
.ph{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:24px;font-weight:800;color:var(--text);letter-spacing:-.02em;margin:0 0 4px}
.ph-sub{font-size:13.5px;color:var(--text2)}
.ph-acts{display:flex;align-items:center;gap:8px;flex-wrap:wrap}

/* ─── SAAS BANNER ─── */
.saas-banner{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px}
.saas-card{border-radius:20px;padding:20px 22px;border:1px solid var(--bd);background:var(--card);cursor:default}
.saas-card.shop,.saas-card.livr{background:var(--card)}
.saas-card-head{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.saas-card-ico{width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.saas-card.shop .saas-card-ico{background:var(--b50);color:var(--brand)}
.saas-card.livr .saas-card-ico{background:var(--abg);color:var(--aink)}
.saas-card-title{font-size:16px;font-weight:800;color:var(--text)}.saas-card-sub{font-size:12.5px;color:var(--muted);margin-top:2px}
.saas-nums{display:flex;gap:0;border-radius:14px;overflow:hidden;background:var(--bg)}
.saas-num{flex:1;padding:12px 10px;text-align:center}
.saas-num+.saas-num{border-left:1px solid var(--bd)}
.saas-num-v{font-size:24px;font-weight:800;letter-spacing:-.02em;line-height:1;transition:all .3s;font-variant-numeric:tabular-nums}
.saas-card.shop .saas-num-v.main{color:var(--brand)}.saas-card.livr .saas-num-v.main{color:var(--sb)}
.saas-num-v.today{color:var(--green)}.saas-num-v.pending{color:var(--aink)}
.saas-num-l{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin-top:6px}
.saas-arrow{font-size:10px;margin-right:2px}

/* ─── KPI GRID ─── */
.kpi-g{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:22px}
.kpi{background:var(--card);border-radius:20px;padding:18px;border:1px solid var(--bd);
    position:relative;overflow:hidden;transition:transform .2s cubic-bezier(.22,1,.36,1),box-shadow .2s;cursor:default}
.kpi:hover{transform:translateY(-2px);box-shadow:0 14px 28px -18px rgba(11,30,61,.35)}
.kpi::before{display:none}
.kpi-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;gap:8px}
.kpi-ic{width:42px;height:42px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.kpi-ic.p{background:var(--b50);color:var(--brand)}
.kpi-ic.g{background:var(--gbg);color:var(--green)}
.kpi-ic.a{background:var(--abg);color:var(--aink)}
.kpi-ic.b{background:var(--b50);color:var(--blt)}
.kpi-ic.r{background:var(--rbg);color:var(--red)}
.kpi-ic.i{background:var(--ibg);color:var(--sb2)}
.kpi-ic.t{background:var(--gbg);color:var(--green)}
.kpi-ic.ro{background:var(--rbg);color:var(--red)}
.kpi-tr{display:flex;align-items:center;gap:3px;font-size:11px;font-weight:700;padding:3px 8px;border-radius:999px;white-space:nowrap}
.kpi-tr.up{color:var(--green);background:var(--gbg)}.kpi-tr.dn{color:var(--red);background:var(--rbg)}.kpi-tr.fl{color:var(--muted);background:var(--bg)}
.kpi-v{font-size:28px;font-weight:800;color:var(--text);letter-spacing:-.02em;line-height:1;margin-bottom:6px;transition:all .3s;font-variant-numeric:tabular-nums}
.kpi-l{font-size:13px;color:var(--text2);font-weight:600}
.kpi-s{font-size:11.5px;color:var(--muted);margin-top:2px}
.kpi-flash{animation:kpiFlash .6s ease}
@keyframes kpiFlash{0%{background:var(--b50)}100%{background:var(--card)}}

/* ─── SECTION CARD ─── */
.sc{background:var(--card);border-radius:20px;border:1px solid var(--bd);overflow:hidden}
.sc-h{padding:16px 20px 14px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.sc-t{font-size:16px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px}
.sc-ico{width:22px;height:22px;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* badges */
.bdg{font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:999px;display:inline-flex;align-items:center;white-space:nowrap}
.bdg.g{color:var(--green);background:var(--gbg)}.bdg.a{color:var(--aink);background:var(--abg)}
.bdg.r{color:var(--red);background:var(--rbg)}.bdg.p{color:var(--brand);background:var(--b50)}
.bdg.b{color:var(--bdk);background:var(--b50)}.bdg.m{color:var(--text2);background:var(--bg)}

/* ─── PENDING ─── */
.pc-row{display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--bd);transition:background-color .13s}
.pc-row:last-child{border-bottom:none}.pc-row:hover{background:var(--bg)}
.pc-av{width:40px;height:40px;border-radius:12px;background:var(--sb);
    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.pc-info{flex:1;min-width:0}
.pc-name{font-size:14px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pc-meta{font-size:12px;color:var(--muted);margin-top:3px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pc-meta-item{display:flex;align-items:center;gap:4px}
.pc-right{text-align:right;flex-shrink:0}
.pc-date{font-size:11.5px;color:var(--muted)}
.pc-btns{display:flex;align-items:center;gap:5px;margin-top:6px;justify-content:flex-end}
.btn-ok{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:999px;
    background:var(--brand);color:#fff;font-size:12.5px;font-weight:700;
    border:none;cursor:pointer;font-family:var(--font);transition:background-color .15s,box-shadow .15s}
.btn-ok:hover{background:var(--bdk);box-shadow:0 8px 20px -8px var(--glow)}
.btn-ok-ico{width:14px;height:14px;display:flex;align-items:center;justify-content:center}
.pc-empty{padding:40px 20px;text-align:center}
.pc-empty-ico{width:52px;height:52px;border-radius:50%;background:var(--gbg);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:var(--green)}
.pc-empty-t{font-size:14px;font-weight:700;color:var(--text2)}

/* ─── ACTIVITY ─── */
.af-row{display:flex;align-items:flex-start;gap:12px;padding:12px 20px;transition:background-color .13s}
.af-row+.af-row{border-top:1px solid var(--bd)}
.af-row:hover{background:var(--bg)}
.af-dot{width:34px;height:34px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.af-dot.g{background:var(--gbg);color:var(--green)}.af-dot.p{background:var(--b50);color:var(--brand)}
.af-dot.a{background:var(--abg);color:var(--aink)}.af-dot.b{background:var(--b50);color:var(--blt)}.af-dot.r{background:var(--rbg);color:var(--red)}
.af-txt{font-size:13.5px;color:var(--text);line-height:1.5}
.af-time{font-size:11.5px;color:var(--muted);margin-top:2px}

/* ─── QUICK ACTIONS ─── */
.qa-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;padding:16px}
.qa{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:18px 10px;
    background:var(--bg);border-radius:16px;border:1px solid transparent;text-decoration:none;
    cursor:pointer;transition:background-color .18s,border-color .18s,transform .18s cubic-bezier(.22,1,.36,1);text-align:center;color:var(--text)}
.qa:hover{background:var(--card);transform:translateY(-2px);border-color:var(--b100);color:var(--brand)}
.qa:focus-visible{outline:3px solid var(--blt);outline-offset:2px}
.qa-ico{width:40px;height:40px;display:flex;align-items:center;justify-content:center;
    border-radius:12px;background:var(--b50);color:var(--brand);transition:background-color .18s,color .18s}
.qa:hover .qa-ico{background:var(--brand);color:#fff}
.qa-l{font-size:13px;font-weight:700;line-height:1.3}

/* ─── PROGRESS BARS ─── */
.bl{padding:16px 20px;display:flex;flex-direction:column;gap:14px}
.bh{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px}
.blbl{font-size:13.5px;font-weight:600;color:var(--text)}.bval{font-size:12.5px;color:var(--muted);font-variant-numeric:tabular-nums}
.bt{height:8px;background:var(--bg);border-radius:999px;overflow:hidden;box-shadow:inset 0 0 0 1px var(--bd)}
.bf{height:100%;border-radius:999px;transition:width 1s cubic-bezier(.22,1,.36,1);width:0}

/* ─── CHART ─── */
.cw{height:150px;display:flex;align-items:flex-end;gap:6px;padding:12px 18px 26px;position:relative}
.c-axis{height:1px;background:var(--bd);position:absolute;bottom:26px;left:18px;right:18px}
.cb{flex:1;border-radius:4px 4px 0 0;min-width:0;transition:opacity .15s;position:relative;cursor:default}
.cb:hover{opacity:.78}
.cb-l{position:absolute;bottom:-17px;left:50%;transform:translateX(-50%);font-size:10.5px;color:var(--muted);white-space:nowrap}

/* ─── SYSTEM STATUS ─── */
.ss-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 20px;border-bottom:1px solid var(--bd)}
.ss-row:last-child{border-bottom:none}
.ss-n{font-size:13.5px;font-weight:600;color:var(--text)}

/* ─── BUTTONS ─── */
.btn-p{display:inline-flex;align-items:center;gap:8px;min-height:40px;padding:0 18px;border-radius:999px;
    background:var(--brand);color:#fff;font-size:13.5px;font-weight:700;
    border:none;cursor:pointer;text-decoration:none;transition:background-color .15s,box-shadow .15s;font-family:var(--font)}
.btn-p:hover{background:var(--bdk);box-shadow:0 8px 20px -8px var(--glow)}
.btn-g{display:inline-flex;align-items:center;gap:8px;min-height:40px;padding:0 18px;border-radius:999px;
    background:var(--card);color:var(--text);font-size:13.5px;font-weight:700;
    border:0;box-shadow:inset 0 0 0 1.5px var(--bd);cursor:pointer;text-decoration:none;transition:box-shadow .15s,color .15s;font-family:var(--font)}
.btn-g:hover{box-shadow:inset 0 0 0 1.5px var(--blt);color:var(--brand)}
.btn-w{width:100%;justify-content:center}
.btn-sm-green{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:999px;
    background:var(--b50);color:var(--brand);font-size:12.5px;font-weight:700;border:none;
    cursor:pointer;text-decoration:none;font-family:var(--font);transition:background-color .15s}
.btn-sm-green:hover{background:var(--b100)}
.btn-p:focus-visible,.btn-g:focus-visible,.btn-ok:focus-visible,.btn-sm-green:focus-visible,.tb-btn:focus-visible,.tb-user:focus-visible{outline:3px solid var(--blt);outline-offset:2px}

/* ─── GRID ─── */
.g2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px}

/* ─── RESPONSIVE ─── */
@media(max-width:1200px){
    .g3{grid-template-columns:1fr 1fr}
    :root{--sb-w:250px}
}
@media(max-width:1024px){
    :root{--sb-w:236px}
}
@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(11,30,61,.35)}
    .sb-ov.open{display:block}.mn{margin-left:0}.ham{display:flex}.sb-close{display:inline-flex}
    .kpi-g{grid-template-columns:repeat(2,1fr)}.g2{grid-template-columns:1fr}
    .saas-banner{grid-template-columns:1fr}.live-badge{display:none}
    .tb-uname,.tb-urole{display:none}.tb-user{padding:4px}
    .saas-num{padding:10px 7px}.saas-num-v{font-size:20px}
}
@media(max-width:768px){
    .ph h1{font-size:20px}.ph-sub{font-size:12.5px}
    .ph-acts .btn-g span:last-child{display:none}
    .saas-num-v{font-size:18px}.saas-num-l{font-size:10px}
    .saas-num{padding:9px 5px}
    .kpi-g{gap:10px}
}
@media(max-width:640px){
    .con{padding:16px max(16px,env(safe-area-inset-right,0px)) 48px max(16px,env(safe-area-inset-left,0px))}.tb{padding:0 16px;gap:8px}
    .g3{grid-template-columns:1fr}
    .ph{flex-direction:column;align-items:flex-start;gap:10px}
    .qa-g{grid-template-columns:repeat(2,1fr)}
    .kpi{padding:14px;border-radius:16px}.kpi-v{font-size:22px}.kpi-ic{width:36px;height:36px;border-radius:12px}
    .kpi-l{font-size:12px}.kpi-s{font-size:11px}
    .saas-card{padding:16px}.saas-card-ico{width:40px;height:40px}
    .saas-num-v{font-size:16px}.saas-num{padding:8px 4px}
    .pc-row{flex-wrap:wrap}
    .pc-right{flex:0 0 100%;display:flex;align-items:center;justify-content:space-between;margin-top:4px}
    .pc-date{margin-top:0}
    .cw{height:110px}
    .sc-h{padding:12px 16px}.af-row{padding:10px 16px}
    .bl{padding:12px 16px;gap:12px}
    .tb-ttl{font-size:15px}
    .drop{min-width:170px}
}
@media(max-width:480px){
    .kpi-g{grid-template-columns:repeat(2,1fr);gap:8px}
    .saas-nums{flex-wrap:wrap}
    .saas-num{flex:0 0 50%}
    .saas-num:nth-child(3),.saas-num:nth-child(4){border-top:1px solid var(--bd)}
    .saas-num:nth-child(odd){border-left:none}
    .saas-num+.saas-num:nth-child(even){border-left:1px solid var(--bd)}
    .qa-g{grid-template-columns:repeat(2,1fr);gap:8px}
    .qa{padding:14px 8px}.qa-l{font-size:12px}
    .pc-av{width:34px;height:34px;font-size:12px;border-radius:10px}
    .ph-acts{gap:6px}.btn-g{font-size:12.5px;padding:0 14px}
    .sc-h{flex-wrap:wrap;gap:6px}
}
@media(max-width:360px){
    .kpi-g{grid-template-columns:1fr}
    .tb-acts{gap:4px}
    .tb-btn{width:36px;height:36px}
    .saas-num{flex:0 0 100%;border-left:none!important;border-top:1px solid var(--bd)}
    .saas-num:first-child{border-top:none}
}

/* ─── REVENUE BANNER ─── */
.rev-banner{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px}
.rev-card{border-radius:22px;padding:24px 26px;position:relative;overflow:hidden;cursor:default;color:#fff}
.rev-card.ca{background:var(--sb)}
.rev-card.today{background:var(--brand)}
/* motif décoratif fond */
.rev-card::before{content:'';position:absolute;top:-60px;right:-50px;width:200px;height:200px;border-radius:50%;
    background:rgba(31,124,246,.35);pointer-events:none}
.rev-card.today::before{background:rgba(255,255,255,.12)}
.rev-card::after{content:'';position:absolute;bottom:-70px;right:70px;width:150px;height:150px;border-radius:50%;
    background:rgba(255,255,255,.05);pointer-events:none}
.rev-card>*{position:relative;z-index:1}
.rev-tag{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:800;
    text-transform:uppercase;letter-spacing:.08em;padding:5px 12px;border-radius:999px;margin-bottom:18px}
.rev-card.ca .rev-tag{background:var(--sb2);color:var(--sky)}
.rev-card.today .rev-tag{background:rgba(255,255,255,.16);color:#fff}
.rev-body{display:flex;align-items:flex-end;justify-content:space-between;gap:12px}
.rev-left{flex:1;min-width:0}
.rev-amount{font-size:36px;font-weight:800;color:#fff;letter-spacing:-.02em;line-height:1;transition:all .3s;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
.rev-currency{font-size:15px;font-weight:700;opacity:.7;margin-left:6px;letter-spacing:0}
.rev-label{font-size:14px;font-weight:700;color:rgba(255,255,255,.9);margin-top:10px}
.rev-sub{font-size:12.5px;color:rgba(255,255,255,.65);margin-top:3px}
.rev-ico-wrap{width:58px;height:58px;border-radius:16px;flex-shrink:0;display:flex;align-items:center;justify-content:center}
.rev-card.ca .rev-ico-wrap{background:var(--blt)}
.rev-card.today .rev-ico-wrap{background:rgba(255,255,255,.16)}
.rev-ico-wrap svg{width:26px;height:26px;color:#fff}
.rev-footer{display:flex;align-items:center;gap:8px;margin-top:18px;padding-top:14px;border-top:1px solid rgba(255,255,255,.14)}
.rev-badge{display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;padding:4px 12px;border-radius:999px}
.rev-card.ca .rev-badge{background:var(--amber);color:var(--text)}
.rev-card.today .rev-badge{background:var(--card);color:var(--bdk)}
.rev-date{font-size:12px;color:rgba(255,255,255,.6);margin-left:auto}
@media(max-width:900px){
    .rev-card{padding:20px 22px}
    .rev-amount{font-size:30px}
}
@media(max-width:640px){
    .rev-banner{grid-template-columns:1fr;gap:12px}
    .rev-card{padding:18px 20px;border-radius:20px}
    .rev-amount{font-size:28px}
    .rev-ico-wrap{width:48px;height:48px;border-radius:14px}
}
@media(max-width:480px){
    .rev-amount{font-size:24px}
    .rev-currency{font-size:13px}
}

/* ─── NOTIFICATION BELL ─── */
@keyframes bellShake{
    0%,100%{transform:rotate(0)}
    10%{transform:rotate(16deg)}25%{transform:rotate(-14deg)}
    40%{transform:rotate(11deg)}55%{transform:rotate(-8deg)}
    70%{transform:rotate(5deg)}85%{transform:rotate(-3deg)}
}
.bell-shake{animation:bellShake .65s ease}
.bell-badge{position:absolute;top:2px;right:2px;min-width:17px;height:17px;padding:0 4px;border-radius:999px;
    background:var(--amber);color:var(--text);font-size:9.5px;font-weight:800;
    display:flex;align-items:center;justify-content:center;border:2px solid #fff;pointer-events:none;line-height:1}
.np{position:absolute;top:calc(100% + 10px);right:-6px;width:350px;max-height:510px;
    background:var(--card);border:1px solid var(--bd);border-radius:20px;
    box-shadow:0 24px 60px -18px rgba(11,30,61,.4);
    z-index:700;display:none;flex-direction:column;overflow:hidden}
.np.open{display:flex}
.np-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px 12px;border-bottom:1px solid var(--bd);flex-shrink:0}
.np-title{font-size:15px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px}
.np-clear{font-size:12px;font-weight:700;color:var(--brand);background:none;border:none;
    cursor:pointer;padding:5px 10px;border-radius:999px;transition:background-color .13s;font-family:var(--font)}
.np-clear:hover{background:var(--b50)}
.np-body{overflow-y:auto;flex:1;scrollbar-width:thin;scrollbar-color:var(--bd) transparent}
.np-body::-webkit-scrollbar{width:3px}
.np-body::-webkit-scrollbar-thumb{background:var(--bd);border-radius:2px}
.np-sep{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:10px 16px 6px;background:var(--bg)}
.np-item{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--bd);
    text-decoration:none;transition:background-color .13s;position:relative;cursor:pointer}
.np-item:last-child{border-bottom:none}
.np-item:hover{background:var(--bg)}
.np-item.new{background:var(--b50)}
.np-item.new::before{content:'';position:absolute;left:0;top:10px;bottom:10px;width:3px;background:var(--blt);border-radius:0 3px 3px 0}
.np-ico{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.np-ico.r{background:var(--rbg);color:var(--red)}
.np-ico.a{background:var(--abg);color:var(--aink)}
.np-ico.p{background:var(--b50);color:var(--brand)}
.np-ico.g{background:var(--gbg);color:var(--green)}
.np-ico.b{background:var(--b50);color:var(--blt)}
.np-info{flex:1;min-width:0}
.np-n{font-size:13px;font-weight:700;color:var(--text);line-height:1.4;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.np-d{font-size:12px;color:var(--muted);margin-top:2px;line-height:1.35}
.np-cnt{flex-shrink:0;min-width:24px;height:24px;padding:0 7px;border-radius:999px;font-size:11px;font-weight:800;color:#fff;
    display:flex;align-items:center;justify-content:center}
.np-cnt.r{background:var(--red)}.np-cnt.a{background:var(--amber);color:var(--text)}
.np-cnt.p{background:var(--brand)}.np-cnt.g{background:var(--green)}.np-cnt.b{background:var(--blt)}
.np-empty{padding:40px 20px;text-align:center}
.np-empty-ico{width:48px;height:48px;border-radius:50%;background:var(--gbg);color:var(--green);
    display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:22px}
.np-empty-t{font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px}
.np-empty-s{font-size:12px;color:var(--muted)}
.np-foot{border-top:1px solid var(--bd);padding:11px 16px;text-align:center;flex-shrink:0}
.np-foot a{font-size:13px;font-weight:700;color:var(--brand);text-decoration:none}
.np-foot a:hover{text-decoration:underline}
@media(max-width:500px){
    .np{position:fixed;top:68px;left:8px;right:8px;width:auto;max-height:calc(100vh - 86px);border-radius:16px}
}
@media(prefers-reduced-motion:reduce){
    .kpi,.qa,.sb,.mn,.bf,.live-dot{transition:none;animation:none}
}
</style>
@include('admin._partials.dark-mode')
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
'megaphone' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>',
'image_ico' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>',
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
        <div class="sb-ico-wrap"><img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="" width="42" height="42"></div>
        <div>
            <div class="sb-appname">Shopio</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()">{!! $I['close'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0">
            <div class="sb-name">{{ Str::limit($meName,22) }}</div>
            <div class="sb-badge-role">{{ $me->role === 'superadmin' ? 'Fondateur & Développeur' : 'Personnel plateforme' }}</div>
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a on">
            <span class="sb-i">{!! $I['home'] !!}</span><span>Vue d'ensemble</span>
        </a>
        <div class="sb-sec shop">SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['store'] !!}</span><span>Boutiques</span>
            <span class="sb-pill a" id="sbBadgeShops" style="{{ $kpis['shopsPending']>0?'':'display:none' }}">{{ $kpis['shopsPending'] }}</span>
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
        <a href="{{ route('admin.image-health.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['image_ico'] !!}</span><span>Santé des images</span>
        </a>
        <div class="sb-sec livr">SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['truck'] !!}</span><span>Entreprises livraison</span>
            <span class="sb-pill r" id="sbBadgeComp" style="{{ $kpis['pendingAppr']>0?'':'display:none' }}">{{ $kpis['pendingAppr'] }}</span>
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
        <div class="sb-sec fin">Finance</div>
        <a href="{{ route('admin.paiements.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span>
        </a>
        <a href="{{ route('admin.payouts.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['dollar'] !!}</span><span>Règlements boutiques</span>
        </a>
        <a href="{{ route('admin.campaigns.index') }}" class="sb-a"><span class="sb-i">{!! \App\Support\IconLibrary::svg('megaphone', '', 17) !!}</span><span>Campagnes</span></a>
        <a href="{{ route('admin.affiliation.index') }}" class="sb-a"><span class="sb-i">{!! \App\Support\IconLibrary::svg('gift', '', 17) !!}</span><span>Affiliation</span></a>
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
        <div class="sb-sec plat">Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span>
        </a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span>
        </a>
        <a href="{{ route('admin.support.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['ticket'] !!}</span><span>Support conversations</span>
            <span class="sb-pill a" id="sbBadgeSupport" style="display:none"></span>
        </a>
        <a href="{{ route('admin.broadcasts.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['megaphone'] !!}</span><span>Annonces par email</span>
        </a>
        <a href="{{ route('admin.plans.index') }}" class="sb-a"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
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
        @include('admin._partials.dark-toggle')
        {{-- ── CLOCHE DE NOTIFICATION ── --}}
        <div style="position:relative">
            <button class="tb-btn" id="bellBtn" onclick="toggleNotifPanel()" title="Notifications">
                {!! $I['bell'] !!}
                <span class="bell-badge" id="bellBadge" style="display:none"></span>
            </button>
            <div class="np" id="np">
                <div class="np-head">
                    <div class="np-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        Notifications
                    </div>
                    <button class="np-clear" onclick="clearNotifs(event)">✓ Tout marquer lu</button>
                </div>
                <div class="np-body" id="npBody"></div>
                <div class="np-foot">
                    <a href="{{ route('admin.support.index') }}">Voir tous les tickets support →</a>
                </div>
            </div>
        </div>
        <a href="{{ route('profile.edit') }}" class="tb-btn" title="Profil">{!! $I['user'] !!}</a>
    </div>
    <div style="position:relative">
        <button class="tb-user" id="tbU" onclick="toggleDrop()">
            <div class="tb-uav">{{ $meInit }}</div>
            <div style="text-align:left">
                <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                <div class="tb-urole">{{ $me->role === 'superadmin' ? 'SuperAdmin' : 'Personnel' }}</div>
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

    {{-- Notifications push — nouvelles inscriptions, abonnements Pro/Business payés… --}}
    <div id="notifSettingsCard" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;background:var(--sb);border-radius:20px;padding:16px 20px;margin-bottom:18px;color:#fff">
        <span style="width:42px;height:42px;border-radius:12px;background:var(--blt);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0">{!! $I['bell'] !!}</span>
        <div style="flex:1;min-width:160px">
            <div style="font-weight:800;font-size:15px;color:#fff">Notifications push</div>
            <div id="notifSettingsSub" style="font-size:13px;color:#C9D6EA;margin-top:2px">Soyez alerté des nouvelles inscriptions et des abonnements payés, même application fermée.</div>
        </div>
        <button type="button" id="notifSettingsBtn"
                style="background:var(--blt);color:#fff;border:none;border-radius:999px;padding:11px 18px;font-size:13.5px;font-weight:700;cursor:pointer;white-space:nowrap;font-family:var(--font)">
            Activer les notifications
        </button>
    </div>
    <script>
    (function() {
        var card = document.getElementById('notifSettingsCard');
        var btn  = document.getElementById('notifSettingsBtn');
        var sub  = document.getElementById('notifSettingsSub');
        if (!card) return;

        if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
            card.style.display = 'none';
            return;
        }

        function refresh() {
            if (Notification.permission === 'granted' && localStorage.getItem('push_subscribed')) {
                sub.textContent = 'Notifications activées ✓';
                btn.textContent = 'Déjà activées';
                btn.disabled = true;
                btn.style.opacity = '.6';
                btn.style.cursor = 'default';
            } else if (Notification.permission === 'denied') {
                sub.textContent = 'Bloquées dans les réglages de votre navigateur — autorisez les notifications pour ce site pour les activer.';
                btn.style.display = 'none';
            }
        }

        btn.addEventListener('click', function() {
            btn.disabled = true;
            btn.textContent = 'Activation…';
            Promise.resolve(window.enablePushNotifications ? window.enablePushNotifications() : null)
                .finally(function() {
                    btn.disabled = false;
                    btn.textContent = 'Activer les notifications';
                    refresh();
                });
        });

        refresh();
    })();
    </script>

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

    {{-- ── REVENUE ABONNEMENTS ── --}}
    <div class="rev-banner">

        {{-- Chiffre d'affaires total --}}
        <div class="rev-card ca">
            <div class="rev-tag">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>
                Chiffre d'affaires
            </div>
            <div class="rev-body">
                <div class="rev-left">
                    <div class="rev-amount" id="kSubCaTotal">{{ number_format($kpis['subCaTotal'],0,',',' ') }}<span class="rev-currency">GNF</span></div>
                    <div class="rev-label">Revenus abonnements</div>
                    <div class="rev-sub">Boutiques Pro + Entreprises Business</div>
                </div>
                <div class="rev-ico-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1Z"/><path d="M14 8H8m8 4H8m5 4H8"/></svg>
                </div>
            </div>
            <div class="rev-footer">
                <span class="rev-badge" id="kSubCountMonth">
                    {{ $kpis['subCountMonth']>0?'▲ '.$kpis['subCountMonth'].' abonnement'.($kpis['subCountMonth']>1?'s':'').' ce mois':'→ Aucun ce mois' }}
                </span>
                <span class="rev-date">{{ now()->locale('fr')->isoFormat('MMMM YYYY') }}</span>
            </div>
        </div>

        {{-- Revenue aujourd'hui --}}
        <div class="rev-card today">
            <div class="rev-tag">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                Revenue aujourd'hui
            </div>
            <div class="rev-body">
                <div class="rev-left">
                    <div class="rev-amount" id="kSubCaToday">{{ number_format($kpis['subCaToday'],0,',',' ') }}<span class="rev-currency">GNF</span></div>
                    <div class="rev-label">Abonnements du jour</div>
                    <div class="rev-sub" id="kSubCountToday">{{ $kpis['subCountToday']>0?$kpis['subCountToday'].' abonnement'.($kpis['subCountToday']>1?'s':'').' souscrit'.($kpis['subCountToday']>1?'s':'')." aujourd'hui":"Aucun abonnement aujourd'hui" }}</div>
                </div>
                <div class="rev-ico-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
            <div class="rev-footer">
                <span class="rev-badge" id="kSubTodayFooter">
                    {{ $kpis['subCaToday']>0?'✓ '.number_format($kpis['subCaToday'],0,',',' ').' GNF encaissé':'→ 0 GNF aujourd\'hui' }}
                </span>
                <span class="rev-date">{{ now()->locale('fr')->isoFormat('D MMMM') }}</span>
            </div>
        </div>

    </div>

    {{-- ── DOUBLE SAAS BANNER ── --}}
    <div class="saas-banner">
        <div class="saas-card shop">
            <div class="saas-card-head">
                <div class="saas-card-ico">{!! $I['store_lg'] !!}</div>
                <div>
                    <div class="saas-card-title">SaaS Boutiques</div>
                    <div class="saas-card-sub" id="saasBannerShopSub">{{ $kpis['clientsTotal'] }} clients · {{ $kpis['ordersTotal'] }} commandes</div>
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
                    <div class="saas-card-sub" id="saasBannerLivrSub">{{ $kpis['driversTotal'] }} livreurs · {{ $kpis['ordersDelivered'] }} livraisons</div>
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
                <div class="kpi-tr up" id="kTrendUsers">▲ +{{ $kpis['usersToday'] }} auj.</div>
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
                <div class="kpi-tr {{ $kpis['ordersToday']>0?'up':'fl' }}" id="kTrendOrders">▲ {{ $kpis['ordersToday'] }} auj.</div>
            </div>
            <div class="kpi-v" id="kOrdersTotal">{{ $kpis['ordersTotal'] }}</div>
            <div class="kpi-l">Commandes</div>
            <div class="kpi-s" id="kOrdersDelivered">{{ $kpis['ordersDelivered'] }} livrées</div>
        </div>
        <div class="kpi r">
            <div class="kpi-top">
                <div class="kpi-ic r">{!! $I['clock_lg'] !!}</div>
                <div class="kpi-tr {{ $kpis['pendingAppr']>0?'dn':'fl' }}" id="kTrendPending">
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
                @php
                    $revLast = $kpis['revLastMonth'] ?? 0;
                    $revNow  = $kpis['revMonth'] ?? 0;
                    if ($revLast > 0) {
                        $pct = round(($revNow - $revLast) / $revLast * 100);
                        $trendTxt = ($pct >= 0 ? '▲ +' : '▼ ') . $pct . '% vs mois dernier';
                        $trendCls = $pct >= 0 ? 'up' : 'dn';
                    } else {
                        $trendTxt = '▲ ce mois';
                        $trendCls = 'up';
                    }
                @endphp
                <div class="kpi-tr {{ $trendCls }}">{{ $trendTxt }}</div>
            </div>
            <div id="kRevMonth" style="display:flex;flex-direction:column;gap:3px;margin:4px 0 2px">
                @forelse($kpis['revByCurrency'] as $devise => $montant)
                    <div style="font-size:15px;font-weight:800;line-height:1.2;color:inherit">
                        {{ number_format($montant, 0, ',', ' ') }}
                        <span style="font-size:10px;font-weight:600;opacity:.65;margin-left:2px">{{ $devise }}</span>
                    </div>
                @empty
                    <div style="font-size:15px;font-weight:800">0</div>
                @endforelse
            </div>
            <div class="kpi-l">Revenus mois</div>
            <div class="kpi-s">Boutiques + livraison · toute plateforme</div>
        </div>
        <div class="kpi b">
            <div class="kpi-top">
                <div class="kpi-ic b">{!! $I['user'] !!}</div>
                <div class="kpi-tr {{ $kpis['clientsToday']>0?'up':'fl' }}" id="kTrendClients">
                    {{ $kpis['clientsToday']>0?'▲ +'.$kpis['clientsToday'].' auj.':'→ 0 auj.' }}
                </div>
            </div>
            <div class="kpi-v" id="kClientsTotal">{{ $kpis['clientsTotal'] }}</div>
            <div class="kpi-l">Clients inscrits</div>
            <div class="kpi-s" id="kClientsToday">{{ $kpis['clientsToday']>0?'+'.$kpis['clientsToday']." aujourd'hui"."":"Aucun aujourd'hui" }}</div>
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
            <a href="{{ route('admin.payouts.index') }}" class="qa">
                <span class="qa-ico">{!! $I['dollar'] !!}</span><span class="qa-l">Règlements boutiques</span>
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
                    <span class="bdg r" id="kPendingBadge" style="{{ $kpis['pendingAppr']>0?'':'display:none' }}">{{ $kpis['pendingAppr'] }}</span>
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
                <div style="font-size:11px;font-weight:800;color:var(--brand);padding:2px 0 4px;text-transform:uppercase;letter-spacing:.08em">SaaS Boutiques</div>
                <div>
                    <div class="bh">
                        <span class="blbl">Boutiques actives</span>
                        <span class="bval" id="brShopsV">{{ $kpis['shopsActive'] }}/{{ $kpis['shopsTotal'] }}</span>
                    </div>
                    <div class="bt"><div class="bf" id="brShops" data-w="{{ $shopPct }}" style="background:var(--brand)"></div></div>
                </div>
                <div>
                    <div class="bh">
                        <span class="blbl">Commandes livrées</span>
                        <span class="bval" id="brOrdersV">{{ $kpis['ordersDelivered'] }}/{{ $kpis['ordersTotal'] }}</span>
                    </div>
                    <div class="bt"><div class="bf" id="brOrders" data-w="{{ $orderDelPct }}" style="background:var(--blt)"></div></div>
                </div>
                <div style="font-size:11px;font-weight:800;color:var(--sb2);padding:8px 0 4px;text-transform:uppercase;letter-spacing:.08em">SaaS Livraison</div>
                <div>
                    <div class="bh">
                        <span class="blbl">Entreprises approuvées</span>
                        <span class="bval" id="brCompV">{{ $kpis['compActive'] }}/{{ $kpis['compTotal'] }}</span>
                    </div>
                    <div class="bt"><div class="bf" id="brComp" data-w="{{ $compPct }}" style="background:var(--sb)"></div></div>
                </div>
                <div>
                    <div class="bh">
                        <span class="blbl">Livreurs inscrits</span>
                        <span class="bval" id="brDriversV">{{ $kpis['driversTotal'] }}</span>
                    </div>
                    <div class="bt"><div class="bf" id="brDrivers" data-w="{{ min(100,$driverPct) }}" style="background:var(--amber)"></div></div>
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
                    <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#1668D8"></span>Commandes
                </div>
                <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted)">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#7DB4FF"></span>Livraisons
                </div>
            </div>
            <div class="cw" id="chartWrap">
                <div class="c-axis"></div>
            </div>
            <div style="padding:0 18px 11px;font-size:10.5px;color:var(--muted);text-align:center" id="kChartFooter">
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

    {{-- ══ ZONE DANGER — SUPPRESSION DÉFINITIVE — réservée au superadmin (fondateur),
         jamais au staff plateforme même s'il a accès au reste de ce tableau de bord ══ --}}
    @if($me->role === 'superadmin')
    <div style="margin-top:28px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
            <div style="width:36px;height:36px;border-radius:10px;background:rgba(239,68,68,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
            </div>
            <div>
                <div style="font-size:14px;font-weight:900;color:var(--err-ink)">Zone de suppression définitive</div>
                <div style="font-size:11px;color:#b91c1c">Suppression irréversible — utilisateur + toutes ses données effacés de la base</div>
            </div>
        </div>

        {{-- Tabs --}}
        <div style="display:flex;gap:0;border-radius:10px 10px 0 0;overflow:hidden;border:1px solid rgba(239,68,68,.2);border-bottom:none">
            <button onclick="dangerTab('shops')" id="dtab-shops" style="flex:1;padding:10px;font-size:12px;font-weight:700;background:var(--rbg);color:#dc2626;border:none;cursor:pointer;border-right:1px solid rgba(239,68,68,.2)">
                Boutiques ({{ $allShops->count() }})
            </button>
            <button onclick="dangerTab('companies')" id="dtab-companies" style="flex:1;padding:10px;font-size:12px;font-weight:700;background:var(--card);color:var(--muted);border:none;cursor:pointer">
                Entreprises ({{ $allCompanies->count() }})
            </button>
        </div>

        {{-- Panel Boutiques --}}
        <div id="dpanel-shops" style="border:1px solid rgba(239,68,68,.2);border-radius:0 0 10px 10px;background:var(--card);overflow:hidden">
            <form method="POST" action="{{ route('admin.shops.purge') }}" id="form-purge-shops">
                @csrf
                <div style="padding:10px 14px;border-bottom:1px solid var(--rbd);display:flex;align-items:center;gap:8px;background:var(--rbg)">
                    <input type="text" placeholder="Rechercher une boutique…" oninput="filterDanger('shops',this.value)"
                        style="flex:1;border:1px solid var(--rbd);border-radius:7px;padding:6px 10px;font-size:12px;color:var(--text);outline:none">
                    <label style="display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--err-ink);cursor:pointer">
                        <input type="checkbox" onchange="selectAllDanger('shops',this.checked)"> Tout sélectionner
                    </label>
                </div>
                <div id="list-shops" style="max-height:280px;overflow-y:auto">
                    @forelse($allShops as $shop)
                    <label class="drow drow-shop" data-name="{{ strtolower($shop->name) }}"
                        style="display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid var(--rbg);cursor:pointer;transition:background .1s">
                        <input type="checkbox" name="ids[]" value="{{ $shop->id }}" style="accent-color:#dc2626;width:15px;height:15px;flex-shrink:0">
                        <div style="flex:1;min-width:0">
                            <div style="font-size:12.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $shop->name }}</div>
                            <div style="font-size:10.5px;color:var(--muted);margin-top:1px">
                                Propriétaire : {{ $shop->owner?->name ?? '—' }} ·
                                {{ $shop->created_at?->format('d/m/Y') }}
                            </div>
                        </div>
                        <span style="font-size:9.5px;font-weight:700;padding:2px 8px;border-radius:20px;{{ $shop->is_approved ? 'background:var(--gbg);color:var(--ok-ink)' : 'background:#fecaca;color:var(--err-ink)' }}">
                            {{ $shop->is_approved ? 'active' : 'suspendue' }}
                        </span>
                    </label>
                    @empty
                    <div style="padding:30px;text-align:center;font-size:12px;color:var(--muted)">Aucune boutique</div>
                    @endforelse
                </div>
                <div style="padding:10px 14px;background:var(--rbg);border-top:1px solid var(--rbd);display:flex;justify-content:flex-end">
                    <button type="button" onclick="confirmPurge('shops')"
                        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                        Supprimer définitivement
                    </button>
                </div>
            </form>
        </div>

        {{-- Panel Entreprises --}}
        <div id="dpanel-companies" style="display:none;border:1px solid rgba(239,68,68,.2);border-radius:0 0 10px 10px;background:var(--card);overflow:hidden">
            <form method="POST" action="{{ route('admin.entreprises.purge') }}" id="form-purge-companies">
                @csrf
                <div style="padding:10px 14px;border-bottom:1px solid var(--rbd);display:flex;align-items:center;gap:8px;background:var(--rbg)">
                    <input type="text" placeholder="Rechercher une entreprise…" oninput="filterDanger('companies',this.value)"
                        style="flex:1;border:1px solid var(--rbd);border-radius:7px;padding:6px 10px;font-size:12px;color:var(--text);outline:none">
                    <label style="display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--err-ink);cursor:pointer">
                        <input type="checkbox" onchange="selectAllDanger('companies',this.checked)"> Tout sélectionner
                    </label>
                </div>
                <div id="list-companies" style="max-height:280px;overflow-y:auto">
                    @forelse($allCompanies as $co)
                    <label class="drow drow-company" data-name="{{ strtolower($co->name) }}"
                        style="display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid var(--rbg);cursor:pointer;transition:background .1s">
                        <input type="checkbox" name="ids[]" value="{{ $co->id }}" style="accent-color:#dc2626;width:15px;height:15px;flex-shrink:0">
                        <div style="flex:1;min-width:0">
                            <div style="font-size:12.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $co->name }}</div>
                            <div style="font-size:10.5px;color:var(--muted);margin-top:1px">
                                {{ $co->created_at?->format('d/m/Y') }}
                            </div>
                        </div>
                        <span style="font-size:9.5px;font-weight:700;padding:2px 8px;border-radius:20px;{{ $co->approved ? 'background:var(--gbg);color:var(--ok-ink)' : 'background:var(--abg);color:var(--warn-ink)' }}">
                            {{ $co->approved ? 'approuvée' : 'en attente' }}
                        </span>
                    </label>
                    @empty
                    <div style="padding:30px;text-align:center;font-size:12px;color:var(--muted)">Aucune entreprise</div>
                    @endforelse
                </div>
                <div style="padding:10px 14px;background:var(--rbg);border-top:1px solid var(--rbd);display:flex;justify-content:flex-end">
                    <button type="button" onclick="confirmPurge('companies')"
                        style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                        Supprimer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal de confirmation (rendu même si masqué ci-dessus : inerte sans les
         boutons déclencheurs, laissé en place pour ne pas complexifier le JS) --}}
    <div id="purgeModal" style="display:none;position:fixed;inset:0;z-index:99998;background:rgba(0,0,0,.6);align-items:center;justify-content:center">
        <div style="background:var(--card);border-radius:16px;padding:28px 28px 24px;width:90%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.25)">
            <div style="width:48px;height:48px;border-radius:50%;background:var(--rbg);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div style="text-align:center;margin-bottom:18px">
                <div style="font-size:15px;font-weight:900;color:var(--text);margin-bottom:6px">Suppression irréversible</div>
                <div id="purgeModalDesc" style="font-size:12px;color:#64748b;line-height:1.6">Cette action supprimera définitivement les données sélectionnées.</div>
            </div>
            <div style="margin-bottom:16px">
                <label style="font-size:11px;font-weight:700;color:var(--err-ink);display:block;margin-bottom:6px">Tapez <strong>SUPPRIMER</strong> pour confirmer :</label>
                <input type="text" id="purgeConfirmInput" placeholder="SUPPRIMER"
                    style="width:100%;border:1.5px solid var(--rbd);border-radius:8px;padding:9px 12px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box"
                    oninput="document.getElementById('purgeConfirmBtn').disabled=this.value!=='SUPPRIMER'">
            </div>
            <div style="display:flex;gap:8px">
                <button onclick="closePurgeModal()" style="flex:1;padding:10px;border:1px solid var(--bd);border-radius:8px;font-size:12px;font-weight:600;background:#f8fafc;cursor:pointer">Annuler</button>
                <button id="purgeConfirmBtn" onclick="executePurge()" disabled
                    style="flex:1;padding:10px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;opacity:.5;transition:opacity .15s"
                    onfocus="this.style.opacity=this.disabled?'.5':'1'" onmouseover="this.style.opacity=this.disabled?'.5':'1'">
                    Supprimer tout
                </button>
            </div>
        </div>
    </div>

</div>{{-- /con --}}
</div>{{-- /mn --}}
</div>{{-- /sa --}}

<div id="toast" style="position:fixed;bottom:22px;right:22px;background:#0B1E3D;color:#fff;padding:12px 18px;border-radius:14px;font-size:13px;font-weight:600;box-shadow:0 8px 28px rgba(0,0,0,.22);transform:translateY(80px);opacity:0;transition:all .27s cubic-bezier(.4,0,.2,1);pointer-events:none;z-index:9999;max-width:270px"></div>

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

/* ═══════════════════════════════════════════════
   NOTIFICATION BELL
═══════════════════════════════════════════════ */
const NP_URLS={
    shops:  '{{ route("admin.shops.index") }}',
    comp:   '{{ route("admin.entreprises.index") }}',
    support:'{{ route("admin.support.index") }}',
    orders: '{{ route("admin.orders.index") }}',
    users:  '{{ route("admin.users.index") }}',
};
const NP_ICO={
    store: `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>`,
    truck: `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/><rect width="7" height="7" x="14" y="12" rx="1"/><path d="M5 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/><path d="M15 19a2 2 0 1 0 4 0 2 2 0 0 0-4 0"/></svg>`,
    ticket:`<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3Z"/></svg>`,
    box:   `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>`,
    users: `<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
};

let _latestKpis=@json($kpis);
let _npOpen=false;
let _prevBellBadge=null;

function _getSeenState(){
    try{
        const s=JSON.parse(localStorage.getItem('sa_notif_seen')||'{}');
        // Reset des compteurs journaliers si jour différent
        const today=new Date().toISOString().slice(0,10);
        if(s._date!==today){s.ordersToday=0;s.usersToday=0;}
        return s;
    }catch(e){return {};}
}
function _saveSeenState(k){
    try{localStorage.setItem('sa_notif_seen',JSON.stringify({
        shops:k.shopsPending,
        comp:k.pendingAppr,
        unreadMessages:k.unreadMessages||0,
        ordersToday:k.ordersToday||0,
        usersToday:k.usersToday||0,
        _date:new Date().toISOString().slice(0,10)
    }));}catch(e){}
}
function computeBadge(k){
    const s=_getSeenState();
    return Math.max(0,k.shopsPending-(s.shops||0))
          +Math.max(0,k.pendingAppr-(s.comp||0))
          +Math.max(0,(k.unreadMessages||0)-(s.unreadMessages||0));
}
function updateSupportSidebarBadge(k){
    const s=_getSeenState();
    const cnt=Math.max(0,(k.unreadMessages||0)-(s.unreadMessages||0));
    const el=document.getElementById('sbBadgeSupport');
    if(!el)return;
    if(cnt>0){el.textContent=cnt;el.style.display='';}
    else{el.style.display='none';}
}
function updateBellBadge(count){
    const el=document.getElementById('bellBadge');
    if(el){
        if(count>0){el.textContent=count>99?'99+':count;el.style.display='flex';}
        else{el.style.display='none';}
    }
    // Le badge sur l'icône de l'app (PWA) se pose tout seul à la réception
    // d'une notification push, mais rien ne le remettait jamais à zéro pour
    // le superadmin (contrairement au client/vendeur/entreprise qui ont leur
    // propre polling) — on le synchronise ici avec le même compteur.
    if(window.updatePwaBadge) window.updatePwaBadge(count);
}
function playBellSound(){
    try{
        const ac=new(window.AudioContext||window.webkitAudioContext)();
        const t=ac.currentTime;
        // Son de cloche : harmoniques + décroissance naturelle
        [[523,.55,1.9],[1047,.28,1.5],[2093,.14,1.1],[4186,.06,.65]].forEach(([f,g,d])=>{
            const o=ac.createOscillator(),gn=ac.createGain();
            o.type='sine';o.frequency.value=f;
            gn.gain.setValueAtTime(0,t);
            gn.gain.linearRampToValueAtTime(g,t+.004);
            gn.gain.exponentialRampToValueAtTime(.001,t+d);
            o.connect(gn);gn.connect(ac.destination);
            o.start(t);o.stop(t+d+.06);
        });
        // Animation cloche
        const btn=document.getElementById('bellBtn');
        if(btn){btn.classList.remove('bell-shake');void btn.offsetWidth;btn.classList.add('bell-shake');}
    }catch(e){}
}
function renderNotifPanel(k){
    const body=document.getElementById('npBody');if(!body)return;
    const s=_getSeenState();
    const pl=(n,word)=>n+' '+word+(n>1?'s':'');
    const urgent=[];const info=[];
    // Urgent : seulement si count DÉPASSE le niveau déjà vu → disparaît après "marquer lu"
    if(k.shopsPending>(s.shops||0))urgent.push({
        cls:'a',ico:NP_ICO.store,cnt:k.shopsPending,url:NP_URLS.shops,
        n:'Boutiques en attente',
        d:pl(k.shopsPending,'boutique')+' en attente d\'approbation',
        type:'shops'
    });
    if(k.pendingAppr>(s.comp||0))urgent.push({
        cls:'r',ico:NP_ICO.truck,cnt:k.pendingAppr,url:NP_URLS.comp,
        n:'Entreprises en attente',
        d:pl(k.pendingAppr,'entreprise')+' en attente d\'approbation',
        type:'comp'
    });
    if((k.unreadMessages||0)>(s.unreadMessages||0))urgent.push({
        cls:'p',ico:NP_ICO.ticket,cnt:k.unreadMessages,url:NP_URLS.support,
        n:'Support — messages non lus',
        d:(k.unreadMessages||0)+' message'+(k.unreadMessages>1?'s':'')+' en attente de votre réponse',
        type:'support'
    });
    // Info : disparaît au clic, réapparaît si nouveaux événements (reset à minuit)
    if((k.ordersToday||0)>(s.ordersToday||0))info.push({
        cls:'g',ico:NP_ICO.box,cnt:k.ordersToday,url:NP_URLS.orders,
        n:'Commandes aujourd\'hui',
        d:pl(k.ordersToday,'commande')+' passée'+(k.ordersToday>1?'s':'')+' aujourd\'hui',
        type:'orders'
    });
    if((k.usersToday||0)>(s.usersToday||0))info.push({
        cls:'b',ico:NP_ICO.users,cnt:k.usersToday,url:NP_URLS.users,
        n:'Nouveaux inscrits',
        d:pl(k.usersToday,'utilisateur')+' inscrit'+(k.usersToday>1?'s':'')+' aujourd\'hui',
        type:'users'
    });
    if(!urgent.length&&!info.length){
        body.innerHTML=`<div class="np-empty"><div class="np-empty-ico">✓</div><div class="np-empty-t">Tout est à jour</div><div class="np-empty-s">Aucune action requise pour le moment.</div></div>`;
        return;
    }
    // Urgent : toujours "new" (par définition : non encore vu), clic = marque comme lu
    const urgRow=i=>`<a href="${i.url}" class="np-item new" onclick="markItemSeen('${i.type}')"><div class="np-ico ${i.cls}">${i.ico}</div><div class="np-info"><div class="np-n">${i.n}</div><div class="np-d">${i.d}</div></div><span class="np-cnt ${i.cls}">${i.cnt}</span></a>`;
    const infRow=i=>`<a href="${i.url}" class="np-item" onclick="markItemSeen('${i.type}')"><div class="np-ico ${i.cls}">${i.ico}</div><div class="np-info"><div class="np-n">${i.n}</div><div class="np-d">${i.d}</div></div><span class="np-cnt ${i.cls}">${i.cnt}</span></a>`;
    let html='';
    if(urgent.length)html+=`<div class="np-sep">⚡ Actions requises</div>`+urgent.map(urgRow).join('');
    if(info.length)html+=`<div class="np-sep">📊 Activité du jour</div>`+info.map(infRow).join('');
    body.innerHTML=html;
}
function toggleNotifPanel(){
    const np=document.getElementById('np');if(!np)return;
    _npOpen=!_npOpen;
    np.classList.toggle('open',_npOpen);
    if(_npOpen)renderNotifPanel(_latestKpis);
}
function markItemSeen(type){
    const s=_getSeenState();
    if(type==='shops')         s.shops         = _latestKpis.shopsPending;
    else if(type==='comp')    s.comp          = _latestKpis.pendingAppr;
    else if(type==='support') s.unreadMessages = _latestKpis.unreadMessages||0;
    else if(type==='orders')  { s.ordersToday = _latestKpis.ordersToday||0; s._date=new Date().toISOString().slice(0,10); }
    else if(type==='users')   { s.usersToday  = _latestKpis.usersToday||0;  s._date=new Date().toISOString().slice(0,10); }
    try{localStorage.setItem('sa_notif_seen',JSON.stringify(s));}catch(e){}
    const badge=computeBadge(_latestKpis);
    _prevBellBadge=badge;
    updateBellBadge(badge);
    updateSupportSidebarBadge(_latestKpis);
    renderNotifPanel(_latestKpis); // ← re-render immédiat : l'item disparaît du panel
}
function clearNotifs(e){
    if(e)e.stopPropagation();
    _saveSeenState(_latestKpis);
    const badge=computeBadge(_latestKpis); // = 0 après save
    _prevBellBadge=badge;
    updateBellBadge(badge);
    updateSupportSidebarBadge(_latestKpis);
    // Ferme le panel et le re-render (seuls les info restent)
    renderNotifPanel(_latestKpis);
    _npOpen=false;
    const np=document.getElementById('np');
    if(np)np.classList.remove('open');
}
document.addEventListener('click',e=>{
    if(!_npOpen)return;
    const btn=document.getElementById('bellBtn'),np=document.getElementById('np');
    if(btn&&np&&!btn.contains(e.target)&&!np.contains(e.target)){
        _npOpen=false;np.classList.remove('open');
    }
});
// Initialisation
(function initNotif(){
    const badge=computeBadge(_latestKpis);
    _prevBellBadge=badge;
    updateBellBadge(badge);
    updateSupportSidebarBadge(_latestKpis);
    renderNotifPanel(_latestKpis);
})();

/* ── Progress bars ── */
setTimeout(()=>{document.querySelectorAll('.bf').forEach(el=>{el.style.width=el.dataset.w+'%'})},350);

/* ── Zone Danger ── */
var _purgeTarget=null;
function dangerTab(t){
    document.getElementById('dpanel-shops').style.display    = t==='shops'     ? '' : 'none';
    document.getElementById('dpanel-companies').style.display= t==='companies' ? '' : 'none';
    document.getElementById('dtab-shops').style.background    = t==='shops'     ? '#fef2f2' : '#fff';
    document.getElementById('dtab-shops').style.color         = t==='shops'     ? '#dc2626' : '#6b7280';
    document.getElementById('dtab-companies').style.background= t==='companies' ? '#fef2f2' : '#fff';
    document.getElementById('dtab-companies').style.color     = t==='companies' ? '#dc2626' : '#6b7280';
}
function filterDanger(type,val){
    var cls = type==='shops' ? '.drow-shop' : '.drow-company';
    document.querySelectorAll(cls).forEach(function(el){
        el.style.display = el.dataset.name.includes(val.toLowerCase()) ? '' : 'none';
    });
}
function selectAllDanger(type,checked){
    var cls = type==='shops' ? '.drow-shop input[type=checkbox]' : '.drow-company input[type=checkbox]';
    document.querySelectorAll(cls).forEach(function(cb){ cb.checked = checked; });
}
function confirmPurge(type){
    var cls = type==='shops' ? '.drow-shop input[type=checkbox]:checked' : '.drow-company input[type=checkbox]:checked';
    var checked = document.querySelectorAll(cls);
    if(!checked.length){ alert('Sélectionnez au moins un élément.'); return; }
    _purgeTarget = type;
    var label = type==='shops' ? 'boutique(s)' : 'entreprise(s)';
    document.getElementById('purgeModalDesc').textContent = checked.length+' '+label+' et TOUTES leurs données (produits, commandes, paiements, utilisateurs) seront supprimées définitivement.';
    document.getElementById('purgeConfirmInput').value = '';
    document.getElementById('purgeConfirmBtn').disabled = true;
    document.getElementById('purgeConfirmBtn').style.opacity = '.5';
    document.getElementById('purgeModal').style.display = 'flex';
}
function closePurgeModal(){
    document.getElementById('purgeModal').style.display='none';
    _purgeTarget=null;
}
function executePurge(){
    if(!_purgeTarget) return;
    var formId = _purgeTarget==='shops' ? 'form-purge-shops' : 'form-purge-companies';
    document.getElementById(formId).submit();
    closePurgeModal();
}
document.getElementById('purgeConfirmBtn').addEventListener('click', function(){
    this.style.opacity = this.disabled ? '.5' : '1';
});
document.querySelectorAll('.drow').forEach(function(el){
    el.addEventListener('mouseover',function(){ this.style.background='#fef2f2'; });
    el.addEventListener('mouseout', function(){ this.style.background=''; });
});
document.getElementById('purgeModal').addEventListener('click', function(e){
    if(e.target===this) closePurgeModal();
});

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
        bc.style.cssText='flex:1;border-radius:3px 3px 0 0;height:0;background:#1668D8;transition:height 1s cubic-bezier(.4,0,.2,1)';
        const bl=document.createElement('div');
        bl.style.cssText='flex:1;border-radius:3px 3px 0 0;height:0;background:#7DB4FF;transition:height 1s cubic-bezier(.4,0,.2,1)';
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
let _pollTimer=null;
function schedulePoll(){ clearTimeout(_pollTimer); _pollTimer=setTimeout(pollStats,15000); }
function pollStats(){
    if(document.visibilityState!=='visible'){ schedulePoll(); return; }
    fetch(STATS_URL,{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json())
        .then(d=>{
            const k=d.kpis;
            setVal('kUsersTotal',k.usersTotal);
            setVal('kDrivers',k.driversTotal);
            setVal('kOrdersTotal',k.ordersTotal);
            setVal('kPending',k.pendingAppr);
            setVal('kClientsTotal',k.clientsTotal);
            const kct=document.getElementById('kClientsToday');
            if(kct)kct.textContent=k.clientsToday>0?'+'+k.clientsToday+" aujourd'hui":"Aucun aujourd'hui";
            // Revenue multi-devises
            const revEl=document.getElementById('kRevMonth');
            if(revEl&&k.revByCurrency){
                const entries=Object.entries(k.revByCurrency);
                revEl.innerHTML=entries.length===0
                    ?'<div style="font-size:15px;font-weight:800">0</div>'
                    :entries.map(([dev,amt])=>`<div style="font-size:15px;font-weight:800;line-height:1.2;color:inherit">${Number(amt).toLocaleString('fr-FR',{maximumFractionDigits:0})}<span style="font-size:10px;font-weight:600;opacity:.65;margin-left:2px">${dev}</span></div>`).join('');
            }
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
            // Progress bars — valeurs + largeur dynamique
            const brSV=document.getElementById('brShopsV');if(brSV)brSV.textContent=k.shopsActive+'/'+k.shopsTotal;
            const brOV=document.getElementById('brOrdersV');if(brOV)brOV.textContent=k.ordersDelivered+'/'+k.ordersTotal;
            const brCV=document.getElementById('brCompV');if(brCV)brCV.textContent=k.compActive+'/'+k.compTotal;
            const brDV=document.getElementById('brDriversV');if(brDV)brDV.textContent=k.driversTotal;
            const brShops=document.getElementById('brShops');
            if(brShops)brShops.style.width=(k.shopsTotal>0?Math.round(k.shopsActive/k.shopsTotal*100):0)+'%';
            const brOrders=document.getElementById('brOrders');
            if(brOrders)brOrders.style.width=(k.ordersTotal>0?Math.round(k.ordersDelivered/k.ordersTotal*100):0)+'%';
            const brComp=document.getElementById('brComp');
            if(brComp)brComp.style.width=(k.compTotal>0?Math.round(k.compActive/k.compTotal*100):0)+'%';
            const brDrivers=document.getElementById('brDrivers');
            if(brDrivers)brDrivers.style.width=Math.min(100,k.driversTotal*10)+'%';
            // Trend badges KPI
            const trU=document.getElementById('kTrendUsers');
            if(trU)trU.textContent='▲ +'+(k.usersToday||0)+' auj.';
            const trO=document.getElementById('kTrendOrders');
            if(trO){trO.textContent='▲ '+(k.ordersToday||0)+' auj.';trO.className='kpi-tr '+(k.ordersToday>0?'up':'fl');}
            const trP=document.getElementById('kTrendPending');
            if(trP){trP.textContent=k.pendingAppr>0?'▼ '+k.pendingAppr:'→ 0';trP.className='kpi-tr '+(k.pendingAppr>0?'dn':'fl');}
            const trC=document.getElementById('kTrendClients');
            if(trC){trC.textContent=k.clientsToday>0?'▲ +'+(k.clientsToday)+' auj.':'→ 0 auj.';trC.className='kpi-tr '+(k.clientsToday>0?'up':'fl');}
            // KPI secondary text
            const kOD=document.getElementById('kOrdersDelivered');
            if(kOD)kOD.textContent=(k.ordersDelivered||0)+' livrées';
            // SaaS banner sub-text
            const sbShopSub=document.getElementById('saasBannerShopSub');
            if(sbShopSub)sbShopSub.textContent=(k.clientsTotal||0)+' clients · '+(k.ordersTotal||0)+' commandes';
            const sbLivrSub=document.getElementById('saasBannerLivrSub');
            if(sbLivrSub)sbLivrSub.textContent=(k.driversTotal||0)+' livreurs · '+(k.ordersDelivered||0)+' livraisons';
            // Revenue banner badges
            const subCM=document.getElementById('kSubCountMonth');
            if(subCM&&k.subCountMonth!==undefined)
                subCM.textContent=k.subCountMonth>0?'▲ '+k.subCountMonth+' abonnement'+(k.subCountMonth>1?'s':'')+' ce mois':'→ Aucun ce mois';
            const subTF=document.getElementById('kSubTodayFooter');
            if(subTF&&k.subCaToday!==undefined)
                subTF.textContent=k.subCaToday>0?'✓ '+Number(k.subCaToday).toLocaleString('fr-FR',{maximumFractionDigits:0})+' GNF encaissé':'→ 0 GNF aujourd\'hui';
            // Pending section badge
            const kPB=document.getElementById('kPendingBadge');
            if(kPB){kPB.textContent=k.pendingAppr;kPB.style.display=k.pendingAppr>0?'':'none';}
            // Chart footer
            const kCF=document.getElementById('kChartFooter');
            if(kCF)kCF.textContent=(k.ordersTotal||0)+' commandes · '+(k.ordersDelivered||0)+' livrées';
            // badges sidebar temps réel
            const sbS=document.getElementById('sbBadgeShops');
            if(sbS){sbS.style.display=k.shopsPending>0?'':'none';sbS.textContent=k.shopsPending;}
            const sbC=document.getElementById('sbBadgeComp');
            if(sbC){sbC.style.display=k.pendingAppr>0?'':'none';sbC.textContent=k.pendingAppr;}
            updateSupportSidebarBadge(k);
            // Notifications bell temps réel
            _latestKpis=k;
            const badge=computeBadge(k);
            if(_prevBellBadge!==null&&badge>_prevBellBadge)playBellSound();
            _prevBellBadge=badge;
            updateBellBadge(badge);
            if(_npOpen)renderNotifPanel(k);
            // Abonnements CA
            const subTotEl=document.getElementById('kSubCaTotal');
            if(subTotEl&&k.subCaTotal!==undefined)
                subTotEl.innerHTML=Number(k.subCaTotal).toLocaleString('fr-FR',{maximumFractionDigits:0})+'<span class="rev-currency">GNF</span>';
            const subTodEl=document.getElementById('kSubCaToday');
            if(subTodEl&&k.subCaToday!==undefined)
                subTodEl.innerHTML=Number(k.subCaToday).toLocaleString('fr-FR',{maximumFractionDigits:0})+'<span class="rev-currency">GNF</span>';
            const subCntEl=document.getElementById('kSubCountToday');
            if(subCntEl&&k.subCountToday!==undefined)
                subCntEl.textContent=k.subCountToday>0?k.subCountToday+' abonnement'+(k.subCountToday>1?'s':'')+(k.subCountToday>1?' souscrits':' souscrit')+" aujourd'hui":"Aucun abonnement aujourd'hui";
            buildChart(d.chart);
            updateActivity(d.activity);
            const ts=document.getElementById('liveTs');if(ts)ts.textContent=d.ts;
        })
        .catch(()=>{});
    schedulePoll();
}
schedulePoll();
// Resynchronise immédiatement quand l'onglet redevient actif (au lieu d'attendre
// jusqu'à 15s de plus) — schedulePoll() annule systématiquement le minuteur en
// attente avant d'en reposer un, donc jamais deux boucles en parallèle même si
// l'onglet change de visibilité plusieurs fois de suite.
document.addEventListener('visibilitychange',function(){
    if(document.visibilityState==='visible')pollStats();
});
</script>
@endpush
