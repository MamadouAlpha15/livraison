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
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;
    position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;
    transition:transform .28s cubic-bezier(.4,0,.2,1);
    scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}
.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;
    background:linear-gradient(135deg,#a78bfa,#7c3aed);
    display:flex;align-items:center;justify-content:center;color:#fff;
    box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
.sb-appname{font-size:14.5px;font-weight:900;color:#fff;letter-spacing:-.2px}
.sb-apptag{font-size:10px;font-weight:700;color:#c4b5fd;text-transform:uppercase;letter-spacing:.9px;margin-top:2px}
.sb-me{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.12);
    display:flex;align-items:center;gap:10px;flex-shrink:0;background:rgba(255,255,255,.05)}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#a78bfa,#6d28d9);
    display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;flex-shrink:0;
    border:2px solid rgba(196,181,253,.5);box-shadow:0 2px 8px rgba(124,58,237,.4)}
.sb-name{font-size:13px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:172px}
.sb-badge{display:inline-block;font-size:9.5px;font-weight:700;color:#e9d5ff;
    background:rgba(167,139,250,.22);border:1px solid rgba(196,181,253,.4);
    padding:2px 8px;border-radius:20px;margin-top:3px;text-transform:uppercase;letter-spacing:.6px}
.sb-nav{padding:8px 0;flex:1}
.sb-sec{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1.3px;
    padding:16px 18px 6px;display:flex;align-items:center;gap:6px}
.sb-sec.shop{color:#6ee7b7}.sb-sec.livr{color:#93c5fd}.sb-sec.fin{color:#fcd34d}.sb-sec.plat{color:#c4b5fd}
.sb-a{display:flex;align-items:center;gap:10px;padding:10px 18px;color:var(--sb-text);
    text-decoration:none;font-size:13px;font-weight:600;border-left:3px solid transparent;
    transition:all .15s;cursor:pointer;letter-spacing:.1px}
.sb-a:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-a.on{background:rgba(167,139,250,.22);border-left-color:#a78bfa;color:#fff;font-weight:700;
    box-shadow:inset 0 0 0 1px rgba(167,139,250,.15)}
.sb-i{width:18px;height:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0;opacity:.85}
.sb-a:hover .sb-i,.sb-a.on .sb-i{opacity:1}
.sb-pill{margin-left:auto;font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;color:#fff}
.sb-pill.r{background:var(--red)}.sb-pill.a{background:var(--amber)}.sb-pill.g{background:var(--green)}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;
    color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;
    font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;
    background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);
    cursor:pointer;display:flex;align-items:center;justify-content:center;
    transition:all .15s;flex-shrink:0}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);
    animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;
    padding:0 22px;gap:12px;position:sticky;top:0;z-index:100;box-shadow:0 1px 0 var(--bd)}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;
    border-radius:7px;align-items:center;justify-content:center;color:var(--muted);transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.con{flex:1;padding:24px}
.flash{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:12.5px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.flash.err{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.2)}
.bc{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}
.ph{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap}
.ph h1{font-size:19px;font-weight:900;color:var(--text);letter-spacing:-.4px;margin:0 0 3px;display:flex;align-items:center;gap:9px}
.ph-sub{font-size:11.5px;color:var(--muted)}
.kpi-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:26px}
.kpi{background:var(--card);border-radius:13px;padding:16px;border:1px solid var(--bd);position:relative;overflow:hidden}
.kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:13px 13px 0 0}
.kpi.p::before{background:linear-gradient(90deg,#7c3aed,#8b5cf6)}
.kpi.g::before{background:linear-gradient(90deg,#10b981,#34d399)}
.kpi.a::before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.kpi.r::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.kpi-ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-bottom:11px}
.kpi-ic.p{background:rgba(139,92,246,.12);color:var(--brand)}
.kpi-ic.g{background:var(--gbg);color:var(--green)}
.kpi-ic.a{background:var(--abg);color:var(--amber)}
.kpi-ic.r{background:var(--rbg);color:var(--red)}
.kpi-v{font-size:24px;font-weight:900;color:var(--text);letter-spacing:-1px;line-height:1;margin-bottom:4px}
.kpi-l{font-size:11.5px;color:var(--muted);font-weight:500}
.fb{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.fb-btn{height:34px;padding:0 14px;border-radius:8px;border:1px solid var(--bd);background:var(--card);
    font-size:12px;font-weight:700;color:var(--muted);cursor:pointer;font-family:var(--font);transition:all .13s;
    display:inline-flex;align-items:center;gap:5px;text-decoration:none}
.fb-btn:hover{background:var(--bg);color:var(--text)}
.fb-btn.on{background:var(--brand);color:#fff;border-color:var(--bdk)}
.sc{background:var(--card);border-radius:13px;border:1px solid var(--bd);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:24px}
.sc-h{padding:14px 20px 12px;border-bottom:1px solid var(--bd);
    display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.sc-t{font-size:13px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:7px}
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.tbl{width:100%;border-collapse:collapse;min-width:640px}
.tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;
    letter-spacing:.5px;padding:9px 16px;background:var(--bg);border-bottom:1px solid var(--bd);white-space:nowrap;text-align:left}
.tbl td{padding:11px 16px;font-size:12px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tbody tr:hover{background:rgba(124,58,237,.02)}
.t-name{font-weight:700;font-size:12.5px}
.t-sub{font-size:10.5px;color:var(--muted)}
.bdg{font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center;gap:3px;white-space:nowrap}
.bdg.g{color:#065f46;background:var(--gbg)}.bdg.a{color:#92400e;background:var(--abg)}
.bdg.m{color:var(--muted);background:rgba(100,116,139,.1)}.bdg.r{color:#7f1d1d;background:var(--rbg)}
.btn-sm{display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border-radius:7px;
    font-size:11px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .13s;text-decoration:none}
.btn-sm:hover{transform:translateY(-1px)}
.btn-send{background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff}
.btn-send:hover{box-shadow:0 4px 14px var(--glow)}
.empty{padding:40px 20px;text-align:center}
.empty-t{font-size:13px;font-weight:700;color:var(--muted)}
.empty-s{font-size:11px;color:rgba(100,116,139,.65);margin-top:4px}
.pag{padding:12px 20px;border-top:1px solid var(--bd)}
@media(max-width:900px){
    .sb{transform:translateX(-100%)}.sb.open{transform:translateX(0);box-shadow:4px 0 32px rgba(0,0,0,.32)}
    .sb-ov.open{display:block}.mn{margin-left:0}
    .kpi-g{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:640px){
    .con{padding:12px}.tb{padding:0 12px;gap:8px}
    .kpi-g{grid-template-columns:repeat(2,1fr);gap:9px}
    .kpi{padding:12px}.kpi-v{font-size:20px}.kpi-ic{width:32px;height:32px}
    .tbl td,.tbl th{padding:8px 8px;font-size:11px}
}
</style>
@endpush

@section('content')
@php
    $me     = auth()->user();
    $meName = $me->name ?? 'Fondateur';
    $meInit = strtoupper(substr($meName,0,1));
    $s = 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';
    $I = [
        'bolt'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'menu'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><line x1="3" y1="6" x2="21" y2="6" '.$s.'/><line x1="3" y1="12" x2="21" y2="12" '.$s.'/><line x1="3" y1="18" x2="21" y2="18" '.$s.'/></svg>',
        'x'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" '.$s.'/><line x1="6" y1="6" x2="18" y2="18" '.$s.'/></svg>',
        'check_c'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" '.$s.'/><polyline points="22 4 12 14.01 9 11.01" '.$s.'/></svg>',
        'card'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="1" y="4" width="22" height="16" rx="2" '.$s.'/><line x1="1" y1="10" x2="23" y2="10" '.$s.'/></svg>',
        'send'   => '<svg width="13" height="13" viewBox="0 0 24 24" fill="none"><line x1="22" y1="2" x2="11" y2="13" '.$s.'/><polygon points="22 2 15 22 11 13 2 9 22 2" '.$s.'/></svg>',
        'clock'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><polyline points="12 6 12 12 16 14" '.$s.'/></svg>',
        'check_sm'=>'<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><polyline points="20 6 9 17 4 12" '.$s.'/></svg>',
        'x_c'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" '.$s.'/><line x1="15" y1="9" x2="9" y2="15" '.$s.'/><line x1="9" y1="9" x2="15" y2="15" '.$s.'/></svg>',
        'user'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" '.$s.'/></svg>',
        'logout' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" '.$s.'/></svg>',
        'bolt_sm'=> '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'store'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-6 9 6v11a1 1 0 01-1 1H4a1 1 0 01-1-1V9z" '.$s.'/><polyline points="9 22 9 12 15 12 15 22" '.$s.'/></svg>',
    ];

    $payoutLabels = [
        'orange_money' => 'Orange Money', 'mtn_momo' => 'MTN MoMo', 'paycard' => 'PayCard',
        'kulu' => 'Kulu', 'soutra_money' => 'Soutra Money', 'akiba' => 'Akiba',
    ];
@endphp

<div class="sa">

<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap">{!! $I['bolt'] !!}</div>
        <div>
            <div class="sb-appname">{{ config('app.name','Shopio') }}</div>
            <div class="sb-apptag">Plateforme · Super Admin</div>
        </div>
        <button class="sb-close" onclick="closeSb()" title="Fermer">{!! $I['x'] !!}</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0">
            <div class="sb-name">{{ Str::limit($meName,22) }}</div>
            <div class="sb-badge">Fondateur &amp; Développeur</div>
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a">
            <span class="sb-i">{!! $I['bolt_sm'] !!}</span><span>Vue d'ensemble</span>
        </a>
        <div class="sb-sec fin">── Finance</div>
        <a href="{{ route('admin.payouts.index') }}" class="sb-a on">
            <span class="sb-i">{!! $I['send'] !!}</span><span>Règlements boutiques</span>
            @if($stats['due_count']>0)<span class="sb-pill a">{{ $stats['due_count'] }}</span>@endif
        </a>
        <a href="{{ route('admin.plans.index') }}" class="sb-a">
            <span class="sb-i">{!! $I['card'] !!}</span><span>Paramètres système</span>
        </a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row">
            <span style="display:inline-flex;opacity:.8">{!! $I['user'] !!}</span>Mon profil
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sb-ft-row">
                <span style="display:inline-flex;opacity:.8">{!! $I['logout'] !!}</span>Déconnexion
            </button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

<div class="mn">
<header class="tb">
    <button class="ham" onclick="toggleSb()">{!! $I['menu'] !!}</button>
    <div class="tb-ttl">Super<b>Admin</b></div>
    <div class="tb-sp"></div>
</header>

<div class="con">

    @if(session('success'))
        <div class="flash ok">{!! $I['check_c'] !!} {{ session('success') }}</div>
    @endif
    @if(session('danger'))
        <div class="flash err">{!! $I['x'] !!} {{ session('danger') }}</div>
    @endif

    <div class="bc">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:4px">
            {!! $I['bolt_sm'] !!} Accueil
        </a>
        <span class="bs">›</span>
        <span style="color:var(--text);font-weight:600">Règlements boutiques</span>
    </div>

    <div class="ph">
        <div>
            <h1>{!! $I['send'] !!} Règlements boutiques</h1>
            <div class="ph-sub">Reversement de la part des boutiques sur les commandes payées en ligne (ChapChap Pay) — Shopio garde {{ config('chapchappay.platform_fee_percent', 1) }}%.</div>
        </div>
    </div>

    <div class="kpi-g">
        <div class="kpi a">
            <div class="kpi-ic a">{!! $I['clock'] !!}</div>
            <div class="kpi-v">{{ $stats['due_count'] }}</div>
            <div class="kpi-l">En attente de reversement</div>
        </div>
        <div class="kpi p">
            <div class="kpi-ic p">{!! $I['card'] !!}</div>
            <div class="kpi-v">{{ number_format($stats['due_amount'] ?? 0, 0, ',', ' ') }}</div>
            <div class="kpi-l">GNF dus aux boutiques</div>
        </div>
        <div class="kpi g">
            <div class="kpi-ic g">{!! $I['check_sm'] !!}</div>
            <div class="kpi-v">{{ $stats['sent_count'] }}</div>
            <div class="kpi-l">Reversements envoyés</div>
        </div>
        <div class="kpi r">
            <div class="kpi-ic r">{!! $I['x_c'] !!}</div>
            <div class="kpi-v">{{ $stats['failed_count'] }}</div>
            <div class="kpi-l">Échecs à relancer</div>
        </div>
    </div>

    <div class="sc">
        <div class="sc-h">
            <div class="sc-t">{!! $I['send'] !!} Paiements en ligne</div>
        </div>
        <div class="sc-b" style="padding:16px 20px 4px">
            <div class="fb">
                <a href="{{ route('admin.payouts.index', ['filter'=>'due']) }}" class="fb-btn {{ $filter==='due' ? 'on' : '' }}">À reverser</a>
                <a href="{{ route('admin.payouts.index', ['filter'=>'sent']) }}" class="fb-btn {{ $filter==='sent' ? 'on' : '' }}">Envoyés</a>
                <a href="{{ route('admin.payouts.index', ['filter'=>'failed']) }}" class="fb-btn {{ $filter==='failed' ? 'on' : '' }}">Échecs</a>
                <a href="{{ route('admin.payouts.index', ['filter'=>'all']) }}" class="fb-btn {{ $filter==='all' ? 'on' : '' }}">Tous</a>
            </div>
        </div>

        @if($payments->isEmpty())
            <div class="empty">
                <div class="empty-t">Aucun paiement dans cette catégorie</div>
                <div class="empty-s">Les commandes payées en ligne par les clients apparaîtront ici.</div>
            </div>
        @else
        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Boutique</th>
                        <th>Montant total</th>
                        <th>Commission Shopio</th>
                        <th>Dû à la boutique</th>
                        <th>Mobile Money</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    @php $shop = $payment->order?->shop; @endphp
                    <tr>
                        <td>
                            <div class="t-name">#{{ $payment->order_id }}</div>
                            <div class="t-sub">{{ $payment->paid_at?->format('d/m/Y H:i') ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="t-name">{{ $shop->name ?? '—' }}</div>
                        </td>
                        <td>{{ number_format($payment->amount, 0, ',', ' ') }} GNF</td>
                        <td>{{ number_format($payment->platform_fee_amount ?? 0, 0, ',', ' ') }} GNF</td>
                        <td><strong>{{ number_format($payment->payout_amount ?? 0, 0, ',', ' ') }} GNF</strong></td>
                        <td>
                            @if($shop && $shop->payout_wallet_type && $shop->payout_wallet_number)
                                <div class="t-sub">{{ $payoutLabels[$shop->payout_wallet_type] ?? $shop->payout_wallet_type }}</div>
                                <div class="t-name" style="font-size:11.5px">{{ $shop->payout_wallet_number }}</div>
                            @else
                                <span class="bdg r">Non configuré</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->payout_status === 'sent')
                                <span class="bdg g">{!! $I['check_sm'] !!} Envoyé</span>
                            @elseif($payment->payout_status === 'processing')
                                <span class="bdg a">{!! $I['clock'] !!} En cours</span>
                            @elseif($payment->payout_status === 'failed')
                                <span class="bdg r">{!! $I['x_c'] !!} Échec</span>
                            @else
                                <span class="bdg m">À reverser</span>
                            @endif
                        </td>
                        <td>
                            @if(in_array($payment->payout_status, ['due','failed']) && $shop && $shop->payout_wallet_type && $shop->payout_wallet_number)
                            <form method="POST" action="{{ route('admin.payouts.send', $payment) }}" onsubmit="return confirm('Envoyer {{ number_format($payment->payout_amount ?? 0, 0, ',', ' ') }} GNF à {{ $shop->name }} maintenant ?');">
                                @csrf
                                <button type="submit" class="btn-sm btn-send">{!! $I['send'] !!} Reverser</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pag">{{ $payments->links() }}</div>
        @endif
    </div>

</div>
</div>
</div>

@push('scripts')
<script>
function toggleSb(){document.getElementById('sb').classList.toggle('open');document.getElementById('sbOv').classList.toggle('open');}
function closeSb(){document.getElementById('sb').classList.remove('open');document.getElementById('sbOv').classList.remove('open');}
</script>
@endpush
@endsection
