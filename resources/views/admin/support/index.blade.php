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
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;border:2px solid rgba(196,181,253,.5)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;transition:all .15s;cursor:pointer}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700}
.sb-i{width:20px;height:20px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-ft-ico{width:16px;height:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.7}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;height:100vh;min-width:0;transition:margin-left .28s;overflow:hidden}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100;flex-shrink:0}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-btn{width:34px;height:34px;background:none;border:1px solid var(--bd);border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:15px;transition:all .13s;text-decoration:none}
.tb-btn:hover{background:var(--bg);color:var(--text)}
.tb-user{display:flex;align-items:center;gap:8px;padding:4px 10px 4px 5px;border:1px solid var(--bd);border-radius:8px;cursor:pointer;background:none;position:relative;transition:all .13s}
.tb-user:hover{background:var(--bg)}
.tb-uav{width:27px;height:27px;border-radius:50%;background:linear-gradient(135deg,var(--brand),var(--bdk));display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.tb-uname{font-size:12px;font-weight:600;color:var(--text)}.tb-urole{font-size:9.5px;color:var(--muted)}
.drop{position:absolute;top:calc(100% + 7px);right:0;background:#fff;border:1px solid var(--bd);border-radius:11px;padding:7px;box-shadow:0 8px 32px rgba(0,0,0,.13);min-width:185px;z-index:300;display:none;flex-direction:column;gap:2px}
.drop.open{display:flex}
.drop-i{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:7px;font-size:12.5px;color:var(--text);text-decoration:none;transition:background .13s;background:none;border:none;cursor:pointer;font-family:var(--font);font-weight:500;width:100%}
.drop-i:hover{background:var(--bg)}.drop-i.d{color:var(--red)}.drop-i.d:hover{background:var(--rbg)}
.drop-sep{height:1px;background:var(--bd);margin:4px 0}

/* ─── CONTENT ─── */
.con{flex:1;display:flex;flex-direction:column;padding:0;min-height:0}
.flash{display:flex;align-items:center;gap:10px;padding:11px 18px;font-size:13px;font-weight:600;border-bottom:1px solid rgba(16,185,129,.2);background:var(--gbg);color:#065f46;flex-shrink:0}

/* ─── CHAT HUB ─── */
.sa-hub{flex:1;min-height:0;display:flex;overflow:hidden;border-top:1px solid var(--bd)}

/* LEFT: conversation list */
.conv-list{width:310px;flex-shrink:0;display:flex;flex-direction:column;border-right:1px solid var(--bd);background:#fafbfc}
.cl-hdr{padding:14px 16px 10px;border-bottom:1px solid var(--bd);flex-shrink:0;display:flex;align-items:center;justify-content:space-between}
.cl-hdr-t{font-size:13.5px;font-weight:800;color:var(--text)}
.cl-hdr-c{font-size:11px;font-weight:700;background:rgba(124,58,237,.1);color:var(--brand);padding:2px 8px;border-radius:20px}
.cl-search{padding:10px 12px;border-bottom:1px solid var(--bd);flex-shrink:0}
.cl-search input{width:100%;padding:7px 12px;border-radius:8px;border:1px solid var(--bd);background:var(--bg);font-size:12.5px;color:var(--text);font-family:var(--font);outline:none;transition:border-color .13s,background .13s}
.cl-search input:focus{border-color:var(--blt);background:#fff}
.cl-search input::placeholder{color:var(--muted)}
.cl-items{flex:1;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(0,0,0,.1) transparent}
.cl-items::-webkit-scrollbar{width:3px}.cl-items::-webkit-scrollbar-thumb{background:rgba(0,0,0,.12);border-radius:2px}
.conv-item{display:flex;align-items:center;gap:11px;padding:13px 14px;cursor:pointer;border-bottom:1px solid rgba(0,0,0,.04);transition:background .13s;position:relative}
.conv-item:hover{background:rgba(124,58,237,.04)}
.conv-item.active{background:rgba(124,58,237,.09)}
.ci-av{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:#fff;flex-shrink:0}
.ci-body{flex:1;min-width:0}
.ci-top{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:3px}
.ci-name{font-size:13px;font-weight:700;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ci-time{font-size:10.5px;color:var(--muted);flex-shrink:0}
.ci-preview{font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ci-dot{width:8px;height:8px;border-radius:50%;background:var(--green);flex-shrink:0;animation:blink 2s ease-in-out infinite}
.cl-empty{padding:40px 20px;text-align:center;color:var(--muted);font-size:13px}

/* RIGHT: chat */
.conv-main{flex:1;display:flex;flex-direction:column;min-width:0}
.conv-welcome{flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;padding:40px;text-align:center;background:#f8f9fb}
.conv-welcome svg{opacity:.15;margin-bottom:8px}
.wt{font-size:15px;font-weight:800;color:var(--text)}
.ws{font-size:13px;color:var(--muted)}
.conv-chat{flex:1;display:flex;flex-direction:column;min-height:0}
.chat-hdr{display:flex;align-items:center;gap:10px;padding:12px 18px;border-bottom:1px solid var(--bd);flex-shrink:0;background:#fff}
.chat-back{display:none;width:32px;height:32px;border:none;background:var(--bg);border-radius:8px;cursor:pointer;font-size:18px;color:var(--text);align-items:center;justify-content:center;flex-shrink:0;transition:background .13s}
.chat-back:hover{background:#e2e8f0}
.chat-hdr-av{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;flex-shrink:0}
.chat-hdr-name{font-size:14px;font-weight:800;color:var(--text)}
.chat-hdr-sub{font-size:11.5px;color:var(--muted)}
.chat-hdr-badge{margin-left:auto}
.chat-thread{flex:1;overflow-y:auto;padding:18px 20px;display:flex;flex-direction:column;gap:8px;background:#f0f2f5;scrollbar-width:thin;scrollbar-color:rgba(0,0,0,.1) transparent}
.chat-thread::-webkit-scrollbar{width:4px}.chat-thread::-webkit-scrollbar-thumb{background:rgba(0,0,0,.14);border-radius:2px}
.chat-msg{display:flex}
.chat-msg.mine{justify-content:flex-end}
.chat-msg.theirs{justify-content:flex-start}
.chat-bubble{max-width:72%;padding:9px 13px;border-radius:14px;word-break:break-word}
.mine .chat-bubble{background:var(--brand);color:#fff;border-radius:14px 14px 4px 14px}
.theirs .chat-bubble{background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:14px 14px 14px 4px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.chat-author{font-size:10.5px;font-weight:700;color:var(--muted);margin-bottom:3px}
.chat-text{font-size:13.5px;line-height:1.45}
.chat-time{font-size:10px;margin-top:5px;text-align:right}
.mine .chat-time{color:rgba(255,255,255,.65)}
.theirs .chat-time{color:var(--muted)}
.chat-input-zone{display:flex;align-items:flex-end;gap:10px;padding:12px 16px;border-top:1px solid var(--bd);background:#fff;flex-shrink:0}
#chatInput{flex:1;padding:10px 14px;border-radius:22px;border:1px solid var(--bd);background:var(--bg);font-size:13.5px;font-family:var(--font);color:var(--text);resize:none;outline:none;max-height:120px;line-height:1.4;transition:border-color .13s,background .13s}
#chatInput:focus{border-color:var(--blt);background:#fff}
#chatInput::placeholder{color:var(--muted)}
#sendBtn{width:42px;height:42px;border-radius:50%;background:var(--brand);border:none;color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all .15s}
#sendBtn:hover{background:var(--bdk);transform:scale(1.05)}
#sendBtn:disabled{opacity:.5;transform:none;cursor:not-allowed}
.chat-loading,.chat-err{text-align:center;padding:30px;color:var(--muted);font-size:13px}
.chat-day{text-align:center;margin:8px 0;font-size:11px;color:var(--muted);font-weight:600;user-select:none}

/* ─── RESPONSIVE ─── */
@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:none}
    .mn{margin-left:0}.sb-ov.open{display:block}
}
@media(max-width:720px){
    .conv-list{width:240px}
}
@media(max-width:580px){
    .sa-hub{flex-direction:column}
    .conv-list{width:100%;border-right:none;border-bottom:1px solid var(--bd);max-height:45vh;flex-shrink:0}
    .conv-list.mob-hidden{display:none}
    .conv-main{display:none}
    .conv-main.mob-active{display:flex}
    .chat-back{display:flex!important}
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
'ticket' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>',
'cog'    => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
'logout' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
'profile'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
'close'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
'menu'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
'check'  => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="20 6 9 17 4 12"/></svg>',
'lock'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
'alert'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
'msg'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
'star'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
'receipt'=> '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1-2-1z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="14" y2="13"/></svg>',
'ticket_stat'=> '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v2z"/></svg>',
'check_stat'=> '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><polyline points="20 6 9 17 4 12"/></svg>',
'lock_stat' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" '.$s.'><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
'send'   => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" '.$s.'><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
];
    $u      = Auth::user();
    $meInit = strtoupper(substr($u->name ?? 'S', 0, 1));
    $meName = $u->name ?? 'SuperAdmin';
    $meId   = $u->id;
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
        <button class="sb-close" onclick="closeSb()" title="Fermer la sidebar">{!! $I['close'] !!}</button>
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
        <a href="{{ route('admin.paiements.index') }}" class="sb-a"><span class="sb-i">{!! $I['card'] !!}</span><span>Paiements</span></a>
        <a href="{{ route('admin.commissions.index') }}" class="sb-a"><span class="sb-i">{!! $I['trend'] !!}</span><span>Commissions</span></a>
        <a href="{{ route('admin.revenus-boutiques.index') }}" class="sb-a"><span class="sb-i">{!! $I['store'] !!}</span><span>Revenus boutiques</span></a>
        <a href="{{ route('admin.revenus-entreprises.index') }}" class="sb-a"><span class="sb-i">{!! $I['truck'] !!}</span><span>Revenus entreprises</span></a>
        <a href="{{ route('admin.revenus.index') }}" class="sb-a"><span class="sb-i">{!! $I['dollar'] !!}</span><span>Revenus plateforme</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="{{ route('admin.users.index') }}" class="sb-a"><span class="sb-i">{!! $I['users'] !!}</span><span>Tous les utilisateurs</span></a>
        <a href="{{ route('admin.avis.index') }}" class="sb-a"><span class="sb-i">{!! $I['star'] !!}</span><span>Avis &amp; Notation</span></a>
        <a href="{{ route('admin.support.index') }}" class="sb-a on"><span class="sb-i">{!! $I['ticket'] !!}</span><span>Support conversations</span></a>
        <a href="#" class="sb-a" onclick="nt();return false"><span class="sb-i">{!! $I['cog'] !!}</span><span>Paramètres système</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['profile'] !!}</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf<button type="submit" class="sb-ft-row"><span class="sb-ft-ico">{!! $I['logout'] !!}</span>Déconnexion</button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- ════════ MAIN ════════ --}}
<div class="mn" id="mn">
    <header class="tb">
        <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
        <div class="tb-ttl" style="display:flex;align-items:center;gap:6px">
            {!! $I['msg'] !!} <b>Support</b>
        </div>
        <div class="tb-sp"></div>
        <a href="{{ route('admin.dashboard') }}" class="tb-btn" title="Dashboard">{!! $I['home'] !!}</a>
        <div style="position:relative">
            <button class="tb-user" id="tbU" onclick="toggleDrop()">
                <div class="tb-uav">{{ $meInit }}</div>
                <div style="text-align:left;display:none" class="d-sm-block">
                    <div class="tb-uname">{{ Str::limit($meName,14) }}</div>
                    <div class="tb-urole">SuperAdmin</div>
                </div>
                <span style="font-size:11px;color:var(--muted)">▾</span>
            </button>
            <div class="drop" id="drop">
                <a href="{{ route('profile.edit') }}" class="drop-i">{!! $I['profile'] !!} Mon profil</a>
                <a href="{{ route('admin.dashboard') }}" class="drop-i">{!! $I['home'] !!} Dashboard</a>
                <div class="drop-sep"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">
                    @csrf<button type="submit" class="drop-i d">{!! $I['logout'] !!} Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <div class="con">
        @if(session('success'))
            <div class="flash">{!! $I['check_stat'] !!} {{ session('success') }}</div>
        @endif

        {{-- ════ CHAT HUB ════ --}}
        <div class="sa-hub">

            {{-- LEFT: conversation list --}}
            <div class="conv-list" id="convList">
                <div class="cl-hdr">
                    <span class="cl-hdr-t">Conversations</span>
                    <span class="cl-hdr-c">{{ $tickets->count() }}</span>
                </div>
                <div class="cl-search">
                    <input type="text" placeholder="Rechercher une boutique…" oninput="filterConvs(this.value)" autocomplete="off">
                </div>
                <div class="cl-items" id="clItems">
                    @forelse($tickets as $ticket)
                    @php
                        $shopName = $ticket->shop->name ?? $ticket->creator->name ?? 'Support';
                        $initial  = strtoupper(substr($shopName, 0, 1));
                        $colors   = ['#7c3aed','#2563eb','#059669','#d97706','#dc2626','#0891b2','#7c3aed'];
                        $color    = $colors[abs(crc32($shopName)) % count($colors)];
                    @endphp
                    <div class="conv-item"
                         id="ci-{{ $ticket->id }}"
                         onclick="openConv({{ $ticket->id }}, this)"
                         data-name="{{ $shopName }}"
                         data-color="{{ $color }}"
                         data-initial="{{ $initial }}"
                         data-search="{{ strtolower($shopName) }}"
                         >
                        <div class="ci-av" style="background:{{ $color }}">{{ $initial }}</div>
                        <div class="ci-body">
                            <div class="ci-top">
                                <div class="ci-name">{{ $shopName }}</div>
                                <div class="ci-time">{{ $ticket->updated_at->diffForHumans(null, true, true) }}</div>
                            </div>
                            <div class="ci-preview">
                                @if($ticket->messages_count > 0)
                                    {{ $ticket->messages_count }} message{{ $ticket->messages_count > 1 ? 's' : '' }}
                                @else
                                    Aucun message
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="cl-empty">Aucune conversation pour le moment</div>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT: chat area --}}
            <div class="conv-main" id="convMain">

                {{-- Welcome (default) --}}
                <div class="conv-welcome" id="convWelcome">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <div class="wt">Sélectionnez une conversation</div>
                    <div class="ws">Cliquez sur une boutique à gauche pour voir ses messages et répondre</div>
                </div>

                {{-- Chat (opened) --}}
                <div class="conv-chat" id="convChat" style="display:none">
                    <div class="chat-hdr">
                        <button class="chat-back" onclick="backToList()">‹</button>
                        <div class="chat-hdr-av" id="chatHdrAv">S</div>
                        <div style="flex:1;min-width:0">
                            <div class="chat-hdr-name" id="chatHdrName">Boutique</div>
                            <div class="chat-hdr-sub" id="chatHdrSub">Support</div>
                        </div>
                    </div>
                    <div class="chat-thread" id="chatThread">
                        <div class="chat-loading">Chargement…</div>
                    </div>
                    <div class="chat-input-zone">
                        <textarea id="chatInput" placeholder="Votre réponse…" rows="1"
                            onkeydown="onKey(event)" oninput="autoGrow(this)"></textarea>
                        <button id="sendBtn" onclick="sendMsg()" title="Envoyer">
                            {!! $I['send'] !!}
                        </button>
                    </div>
                </div>

            </div>{{-- /conv-main --}}
        </div>{{-- /sa-hub --}}
    </div>{{-- /con --}}
</div>{{-- /mn --}}
</div>{{-- /sa --}}
@endsection

@push('scripts')
<script>
const CSRF  = (document.querySelector('meta[name="csrf-token"]')||{}).content || '{{ csrf_token() }}';
const ME_ID = {{ $meId }};

let _activeTicket = null;
let _lastMsgId    = 0;
let _pollTimer    = null;

/* ─── Sidebar / topbar ─── */
function toggleSb(){const s=document.getElementById('sb'),m=document.getElementById('mn'),o=document.getElementById('sbOv');s.classList.toggle('open');s.classList.toggle('closed');m.classList.toggle('sb-closed');o.classList.toggle('open')}
function closeSb(){const s=document.getElementById('sb'),m=document.getElementById('mn'),o=document.getElementById('sbOv');s.classList.remove('open');s.classList.add('closed');m.classList.add('sb-closed');o.classList.remove('open')}
function toggleDrop(){document.getElementById('drop').classList.toggle('open')}
document.addEventListener('click',e=>{const d=document.getElementById('drop'),b=document.getElementById('tbU');if(d&&b&&!d.contains(e.target)&&!b.contains(e.target))d.classList.remove('open')});
function nt(){alert('Bientôt disponible !');}

/* ─── Helpers ─── */
function escHtml(s) {
    return String(s||'')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function fmtTime(iso) {
    if (!iso) return '';
    const d   = new Date(iso);
    const now = new Date();
    const min = Math.floor((now - d) / 60000);
    if (min < 1)  return 'maintenant';
    if (min < 60) return min + ' min';
    const h = Math.floor(min / 60);
    if (h < 24)   return h + 'h';
    const days = Math.floor(h / 24);
    if (days < 7) return days + 'j';
    return d.toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit' });
}

/* ─── Filter conversation list ─── */
function filterConvs(q) {
    const low = q.toLowerCase().trim();
    document.querySelectorAll('.conv-item').forEach(el => {
        el.style.display = (!low || el.dataset.search.includes(low)) ? '' : 'none';
    });
}

/* ─── Open a conversation ─── */
function openConv(ticketId, el) {
    document.querySelectorAll('.conv-item.active').forEach(x => x.classList.remove('active'));
    el.classList.add('active');

    _activeTicket = ticketId;
    _lastMsgId    = 0;

    /* update header */
    const name    = el.dataset.name;
    const color   = el.dataset.color || '#7c3aed';
    const initial = el.dataset.initial || name.charAt(0).toUpperCase();
    const status  = el.dataset.status;

    const av = document.getElementById('chatHdrAv');
    av.textContent     = initial;
    av.style.background = color;
    document.getElementById('chatHdrName').textContent = name;
    document.getElementById('chatHdrSub').textContent  = 'Conversation support';

    /* show chat panel */
    document.getElementById('convWelcome').style.display = 'none';
    const chat = document.getElementById('convChat');
    chat.style.display = 'flex';

    /* mobile toggle */
    document.getElementById('convList').classList.add('mob-hidden');
    document.getElementById('convMain').classList.add('mob-active');

    /* load messages */
    const thread = document.getElementById('chatThread');
    thread.innerHTML = '<div class="chat-loading">Chargement…</div>';

    fetch(`/support/${ticketId}/messages.json`, { headers:{ 'Accept':'application/json' } })
        .then(r => r.json())
        .then(msgs => {
            msgs.sort((a,b) => a.id - b.id);
            thread.innerHTML = '';
            if (msgs.length === 0) {
                thread.innerHTML = '<div class="chat-loading" style="color:var(--muted)">Aucun message pour le moment</div>';
                return;
            }
            msgs.forEach(m => { appendMsg(m, false); if (m.id > _lastMsgId) _lastMsgId = m.id; });
            thread.scrollTop = thread.scrollHeight;
        })
        .catch(() => { thread.innerHTML = '<div class="chat-err">Erreur de chargement</div>'; });

    /* start polling */
    clearInterval(_pollTimer);
    _pollTimer = setInterval(poll, 5000);
}

/* ─── Poll for new messages ─── */
function poll() {
    if (!_activeTicket) return;
    fetch(`/support/${_activeTicket}/messages.json`, { headers:{ 'Accept':'application/json' } })
        .then(r => r.json())
        .then(msgs => {
            const newer = msgs.filter(m => m.id > _lastMsgId);
            newer.sort((a,b) => a.id - b.id);
            if (!newer.length) return;
            newer.forEach(m => { appendMsg(m, true); if (m.id > _lastMsgId) _lastMsgId = m.id; });
            const thread = document.getElementById('chatThread');
            thread.scrollTop = thread.scrollHeight;
        })
        .catch(() => {});
}

/* ─── Append a bubble ─── */
function appendMsg(m, animate) {
    const isMine = m.user_id === ME_ID || (m.author && m.author.role === 'superadmin');
    const thread = document.getElementById('chatThread');
    const wrap   = document.createElement('div');
    wrap.className = 'chat-msg ' + (isMine ? 'mine' : 'theirs');
    wrap.dataset.id = m.id;
    if (animate) wrap.style.cssText = 'opacity:0;transform:translateY(6px);transition:opacity .2s,transform .2s';

    wrap.innerHTML =
        `<div class="chat-bubble">` +
        (isMine ? '' : `<div class="chat-author">${escHtml(m.author?.name ?? 'Boutique')}</div>`) +
        `<div class="chat-text">${escHtml(m.body).replace(/\n/g,'<br>')}</div>` +
        `<div class="chat-time">${fmtTime(m.created_at)}</div>` +
        `</div>`;

    thread.appendChild(wrap);
    if (animate) requestAnimationFrame(() => { wrap.style.opacity='1'; wrap.style.transform='translateY(0)'; });
}

/* ─── Send message ─── */
function sendMsg() {
    const input = document.getElementById('chatInput');
    const body  = input.value.trim();
    if (!body || !_activeTicket) return;

    const btn = document.getElementById('sendBtn');
    btn.disabled   = true;
    input.disabled = true;

    fetch(`/support/${_activeTicket}/messages`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({ body })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            input.value = '';
            input.style.height = '';
            appendMsg(data.msg, true);
            if (data.msg.id > _lastMsgId) _lastMsgId = data.msg.id;
            const thread = document.getElementById('chatThread');
            thread.scrollTop = thread.scrollHeight;
        }
    })
    .catch(() => {})
    .finally(() => {
        btn.disabled   = false;
        input.disabled = false;
        input.focus();
    });
}

function onKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
}

function autoGrow(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

/* ─── Mobile: back to list ─── */
function backToList() {
    document.getElementById('convList').classList.remove('mob-hidden');
    document.getElementById('convMain').classList.remove('mob-active');
    clearInterval(_pollTimer);
    _activeTicket = null;
}
</script>
@endpush
