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

/* ─── SIDEBAR (compact, same style) ─── */
.sb{width:var(--sb-w);background:var(--sb);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:200;overflow-y:auto;overflow-x:hidden;transition:transform .28s cubic-bezier(.4,0,.2,1);scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.15) transparent}
.sb::-webkit-scrollbar{width:3px}.sb::-webkit-scrollbar-thumb{background:rgba(255,255,255,.2);border-radius:2px}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.12);display:flex;align-items:center;gap:11px;flex-shrink:0}
.sb-ico-wrap{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#a78bfa,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:19px;box-shadow:0 4px 16px rgba(167,139,250,.45);flex-shrink:0}
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
.sb-i{width:18px;text-align:center;font-size:15px;flex-shrink:0}
.sb-ft{padding:12px 18px;border-top:1px solid rgba(255,255,255,.12);flex-shrink:0;background:rgba(0,0,0,.12)}
.sb-ft-row{display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:8px;color:rgba(255,255,255,.7);font-size:12.5px;font-weight:600;background:none;border:none;cursor:pointer;font-family:var(--font);width:100%;text-align:left;text-decoration:none;transition:all .15s}
.sb-ft-row:hover{background:rgba(255,255,255,.1);color:#fff}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 2s ease-in-out infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:199;backdrop-filter:blur(2px)}

/* ─── MAIN ─── */
.mn{flex:1;margin-left:var(--sb-w);display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .28s}
.tb{height:60px;background:var(--card);border-bottom:1px solid var(--bd);display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:100}
.ham{display:flex;width:32px;height:32px;background:none;border:none;cursor:pointer;border-radius:7px;align-items:center;justify-content:center;color:var(--muted);font-size:18px;transition:all .13s;flex-shrink:0}
.ham:hover{background:var(--bg);color:var(--text)}
.sb-close{margin-left:auto;width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,.1);border:none;color:rgba(255,255,255,.7);font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;flex-shrink:0;font-family:var(--font)}
.sb-close:hover{background:rgba(255,255,255,.22);color:#fff}
.sb.closed{transform:translateX(-100%)}
.mn.sb-closed{margin-left:0}
.tb-ttl{font-size:14px;font-weight:800;color:var(--text)}.tb-ttl b{color:var(--brand)}
.tb-sp{flex:1}
.tb-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;border:1px solid var(--bd);background:var(--bg);color:var(--muted);text-decoration:none;transition:all .15s}
.tb-btn:hover{background:#e2e8f0;color:var(--text)}
.tb-btn.pri{background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;border-color:var(--bdk)}
.tb-btn.pri:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}

/* ─── CONTENT ─── */
.con{flex:1;padding:26px;max-width:1100px}
.flash{display:flex;align-items:center;gap:10px;padding:13px 16px;border-radius:10px;margin-bottom:22px;font-size:13px;font-weight:600}
.flash.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.2)}
.bc{display:flex;align-items:center;gap:5px;font-size:11.5px;color:var(--muted);margin-bottom:20px}
.bc a{color:var(--muted);text-decoration:none}.bc a:hover{color:var(--text)}.bc .bs{color:rgba(0,0,0,.15)}

/* ─── ORDER HEADER ─── */
.ord-hero{background:linear-gradient(135deg,#1e1b4b,#2d2470,#4c1d95);border-radius:16px;padding:26px 28px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;position:relative;overflow:hidden}
.ord-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:32px 32px;pointer-events:none}
.ord-hero-left{position:relative;z-index:1}
.ord-hero-id{font-size:28px;font-weight:900;color:#fff;letter-spacing:-1px;margin-bottom:6px}
.ord-hero-shop{font-size:13px;color:rgba(255,255,255,.7);display:flex;align-items:center;gap:6px}
.ord-hero-right{position:relative;z-index:1;text-align:right}

/* status display */
.ord-status{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:20px;font-size:13px;font-weight:800;margin-bottom:10px}
.ord-status.att{background:var(--abg);color:#92400e;border:1px solid rgba(245,158,11,.3)}
.ord-status.con{background:var(--bbg);color:#1d4ed8;border:1px solid rgba(59,130,246,.3)}
.ord-status.liv{background:var(--ibg);color:#3730a3;border:1px solid rgba(99,102,241,.3)}
.ord-status.ok{background:var(--gbg);color:#065f46;border:1px solid rgba(16,185,129,.3)}
.ord-status.ann{background:var(--rbg);color:#7f1d1d;border:1px solid rgba(239,68,68,.3)}
.ord-date{font-size:12px;color:rgba(255,255,255,.55)}

/* ─── LAYOUT GRID ─── */
.detail-grid{display:grid;grid-template-columns:1fr 340px;gap:18px;align-items:start}

/* ─── CARDS ─── */
.dc{background:var(--card);border-radius:14px;border:1px solid var(--bd);overflow:hidden;margin-bottom:18px}
.dc-h{padding:14px 20px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:8px}
.dc-t{font-size:13.5px;font-weight:800;color:var(--text)}
.dc-body{padding:18px 20px}

/* ─── INFO ROWS ─── */
.info-row{display:flex;align-items:flex-start;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--bd);gap:12px}
.info-row:last-child{border-bottom:none;padding-bottom:0}
.info-lbl{font-size:11.5px;color:var(--muted);font-weight:600;flex-shrink:0;min-width:110px}
.info-val{font-size:12.5px;color:var(--text);font-weight:600;text-align:right}

/* ─── ITEMS TABLE ─── */
.items-tbl{width:100%;border-collapse:collapse}
.items-tbl th{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;padding:8px 14px;background:#f8fafc;border-bottom:1px solid var(--bd);text-align:left}
.items-tbl td{padding:12px 14px;font-size:12.5px;color:var(--text);border-bottom:1px solid var(--bd);vertical-align:middle}
.items-tbl tr:last-child td{border-bottom:none}
.item-prod{display:flex;align-items:center;gap:9px}
.item-av{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--brand),#4f46e5);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
.item-img{width:34px;height:34px;border-radius:8px;object-fit:cover;border:1px solid var(--bd);flex-shrink:0}
.item-name{font-size:12.5px;font-weight:700;color:var(--text)}
.item-cat{font-size:10.5px;color:var(--muted)}
.items-total{padding:12px 14px;border-top:2px solid var(--bd);display:flex;justify-content:flex-end;align-items:center;gap:8px}

/* ─── STATUS CHANGE FORM ─── */
.status-form{display:flex;flex-direction:column;gap:10px}
.f-sel{width:100%;padding:9px 12px;border-radius:9px;border:1px solid var(--bd);background:var(--bg);font-size:13px;color:var(--text);font-family:var(--font);outline:none;cursor:pointer}
.f-sel:focus{border-color:var(--blt);box-shadow:0 0 0 3px rgba(124,58,237,.09)}
.btn-save{width:100%;padding:10px;border-radius:9px;background:linear-gradient(135deg,var(--brand),var(--bdk));color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:all .15s}
.btn-save:hover{transform:translateY(-1px);box-shadow:0 4px 14px var(--glow)}

/* status timeline */
.timeline{display:flex;flex-direction:column;gap:0;padding:0}
.tl-step{display:flex;align-items:flex-start;gap:12px;padding:10px 0;position:relative}
.tl-step:not(:last-child)::after{content:'';position:absolute;left:15px;top:34px;width:2px;height:calc(100% - 10px);background:var(--bd)}
.tl-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;position:relative;z-index:1}
.tl-dot.done{background:var(--gbg);border:2px solid rgba(16,185,129,.3)}
.tl-dot.current{background:rgba(124,58,237,.15);border:2px solid rgba(124,58,237,.4);animation:pulse-tl 1.8s ease-in-out infinite}
.tl-dot.wait{background:#f1f5f9;border:2px solid var(--bd)}
@keyframes pulse-tl{0%,100%{box-shadow:0 0 0 0 rgba(124,58,237,.3)}50%{box-shadow:0 0 0 6px rgba(124,58,237,0)}}
.tl-info{padding-top:5px}
.tl-lbl{font-size:12.5px;font-weight:700;color:var(--text)}
.tl-sub{font-size:11px;color:var(--muted);margin-top:1px}

/* badges misc */
.chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700}
.chip.g{color:#065f46;background:var(--gbg);border:1px solid rgba(16,185,129,.2)}
.chip.a{color:#92400e;background:var(--abg);border:1px solid rgba(245,158,11,.2)}
.chip.r{color:#7f1d1d;background:var(--rbg);border:1px solid rgba(239,68,68,.2)}
.chip.m{color:var(--muted);background:rgba(100,116,139,.1);border:1px solid rgba(100,116,139,.2)}

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

    $clientName = $order->client->name ?? $order->client_phone ?? $order->delivery_destination ?? 'Client inconnu';
    $shopName   = $order->shop->name ?? '—';
    $ownerName  = $order->shop->owner->name ?? '—';
    $livreurName= $order->driver->name ?? $order->livreur->name ?? null;

    $cur = match(strtoupper($order->shop->currency ?? 'GNF')) {
        'GNF'  => 'GNF',
        'XOF'  => 'FCFA',
        'XAF'  => 'FCFA',
        'EUR'  => '€',
        'USD'  => '$',
        'GBP'  => '£',
        'MAD'  => 'MAD',
        'DZD'  => 'DA',
        'TND'  => 'TND',
        default => strtoupper($order->shop->currency ?? 'GNF'),
    };

    $statusMap = [
        'en_attente'   => ['cls'=>'att','ico'=>'⏳','lbl'=>'En attente'],
        'confirmée'    => ['cls'=>'con','ico'=>'✅','lbl'=>'Confirmée'],
        'en_livraison' => ['cls'=>'liv','ico'=>'🏍️','lbl'=>'En livraison'],
        'livrée'       => ['cls'=>'ok', 'ico'=>'📦','lbl'=>'Livrée'],
        'annulée'      => ['cls'=>'ann','ico'=>'❌','lbl'=>'Annulée'],
    ];
    $st = $statusMap[$order->status] ?? ['cls'=>'att','ico'=>'?','lbl'=>$order->status];

    $allStatuses = ['en_attente','confirmée','en_livraison','livrée','annulée'];
    $stepsOrder  = $allStatuses;
    $currentIdx  = array_search($order->status, $stepsOrder);
@endphp

<div class="sa">

{{-- SIDEBAR --}}
<aside class="sb" id="sb">
    <div class="sb-logo">
        <div class="sb-ico-wrap">⚡</div>
        <div><div class="sb-appname">{{ config('app.name','Shopio') }}</div><div class="sb-apptag">Super Admin</div></div>
        <button class="sb-close" onclick="closeSb()" title="Fermer la sidebar">✕</button>
    </div>
    <div class="sb-me">
        <div class="sb-av">{{ $meInit }}</div>
        <div style="min-width:0"><div class="sb-name">{{ Str::limit($meName,22) }}</div><div class="sb-badge">Fondateur</div></div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('admin.dashboard') }}" class="sb-a"><span class="sb-i">🏠</span><span>Vue d'ensemble</span></a>
        <div class="sb-sec shop">── SaaS Boutiques</div>
        <a href="{{ route('admin.shops.index') }}" class="sb-a"><span class="sb-i">🏪</span><span>Boutiques</span></a>
        <a href="{{ route('admin.products.index') }}" class="sb-a"><span class="sb-i">🛍️</span><span>Produits</span></a>
        <a href="{{ route('admin.orders.index') }}" class="sb-a on"><span class="sb-i">📦</span><span>Commandes boutiques</span></a>
        <a href="{{ route('admin.vendeurs.index') }}" class="sb-a"><span class="sb-i">👔</span><span>Vendeurs &amp; Employés</span></a>
        <a href="{{ route('admin.clients.index') }}" class="sb-a"><span class="sb-i">🧑‍💼</span><span>Clients boutiques</span></a>
        <div class="sb-sec livr">── SaaS Livraison</div>
        <a href="{{ route('admin.entreprises.index') }}" class="sb-a"><span class="sb-i">🚚</span><span>Entreprises livraison</span></a>
        <a href="{{ route('admin.livreurs.index') }}" class="sb-a"><span class="sb-i">🏍️</span><span>Livreurs</span></a>
        <div class="sb-sec fin">── Finance</div>
        <a href="#" class="sb-a" onclick="return false"><span class="sb-i">💳</span><span>Paiements</span></a>
        <a href="#" class="sb-a" onclick="return false"><span class="sb-i">💹</span><span>Commissions</span></a>
        <div class="sb-sec plat">── Plateforme</div>
        <a href="#" class="sb-a" onclick="return false"><span class="sb-i">👥</span><span>Utilisateurs</span></a>
        <a href="#" class="sb-a" onclick="return false"><span class="sb-i">📊</span><span>Rapports</span></a>
        <a href="#" class="sb-a" onclick="return false"><span class="sb-i">⚙️</span><span>Paramètres</span></a>
    </nav>
    <div class="sb-ft">
        <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;font-size:11.5px;color:rgba(255,255,255,.6);font-weight:600">
            <div class="live-dot"></div>Système opérationnel
        </div>
        <a href="{{ route('profile.edit') }}" class="sb-ft-row"><span style="font-size:14px">👤</span>Mon profil</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf<button type="submit" class="sb-ft-row"><span style="font-size:14px">⎋</span>Déconnexion</button>
        </form>
    </div>
</aside>
<div class="sb-ov" id="sbOv" onclick="closeSb()"></div>

{{-- MAIN --}}
<div class="mn">
    <header class="tb">
        <button class="ham" onclick="toggleSb()">☰</button>
        <div class="tb-ttl">📦 Commande <b>#{{ $order->id }}</b></div>
        <div class="tb-sp"></div>
        <a href="{{ route('admin.orders.index') }}" class="tb-btn">← Retour</a>
    </header>

    <div class="con">

        @if(session('success'))
            <div class="flash ok">✅ {{ session('success') }}</div>
        @endif

        <div class="bc">
            <span>⚡</span><span class="bs">›</span>
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
                <div class="ord-hero-shop">🏪 {{ $shopName }} &nbsp;·&nbsp; 👤 {{ $ownerName }}</div>
            </div>
            <div class="ord-hero-right">
                <div>
                    <span class="ord-status {{ $st['cls'] }}">{{ $st['ico'] }} {{ $st['lbl'] }}</span>
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
                        <span style="font-size:16px">🛒</span>
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
                                            @if($pCat)<div class="item-cat">📂 {{ $pCat }}</div>@endif
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
                    <div class="dc-h"><span style="font-size:16px">👤</span><div class="dc-t">Informations client</div></div>
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
                            <span class="info-val">📞 {{ $order->client_phone }}</span>
                        </div>
                        @endif
                        @if($order->delivery_destination)
                        <div class="info-row">
                            <span class="info-lbl">Adresse livraison</span>
                            <span class="info-val" style="text-align:right;max-width:200px">📍 {{ $order->delivery_destination }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Livraison --}}
                @if($livreurName || $order->deliveryCompany || $order->deliveryZone)
                <div class="dc">
                    <div class="dc-h"><span style="font-size:16px">🏍️</span><div class="dc-t">Informations livraison</div></div>
                    <div class="dc-body">
                        @if($order->deliveryCompany)
                        <div class="info-row">
                            <span class="info-lbl">Entreprise</span>
                            <span class="info-val">🚚 {{ $order->deliveryCompany->name }}</span>
                        </div>
                        @endif
                        @if($livreurName)
                        <div class="info-row">
                            <span class="info-lbl">Livreur</span>
                            <span class="info-val">🏍️ {{ $livreurName }}</span>
                        </div>
                        @endif
                        @if($order->deliveryZone)
                        <div class="info-row">
                            <span class="info-lbl">Zone</span>
                            <span class="info-val">📍 {{ $order->deliveryZone->name }}</span>
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
                    <div class="dc-h"><span style="font-size:16px">💳</span><div class="dc-t">Paiement</div></div>
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
                                    <span class="chip g">✅ Payé</span>
                                @elseif($ps === 'pending' || $ps === 'en_attente')
                                    <span class="chip a">⏳ En attente</span>
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
                    <div class="dc-h"><span style="font-size:16px">⚙️</span><div class="dc-t">Modifier le statut</div></div>
                    <div class="dc-body">
                        <form method="POST" action="{{ route('admin.orders.status',$order) }}" class="status-form">
                            @csrf
                            <select name="status" class="f-sel">
                                @foreach($statusMap as $val => $info)
                                    <option value="{{ $val }}" {{ $order->status===$val ? 'selected' : '' }}>
                                        {{ $info['ico'] }} {{ $info['lbl'] }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-save">💾 Mettre à jour le statut</button>
                        </form>
                    </div>
                </div>

                {{-- Timeline progression --}}
                <div class="dc">
                    <div class="dc-h"><span style="font-size:16px">📋</span><div class="dc-t">Progression</div></div>
                    <div class="dc-body" style="padding:14px 20px">
                        <div class="timeline">
                            @php
                                $steps = [
                                    ['status'=>'en_attente',  'ico'=>'⏳','lbl'=>'En attente',   'sub'=>'Commande reçue, non confirmée'],
                                    ['status'=>'confirmée',   'ico'=>'✅','lbl'=>'Confirmée',    'sub'=>'Boutique a validé la commande'],
                                    ['status'=>'en_livraison','ico'=>'🏍️','lbl'=>'En livraison', 'sub'=>'Livreur en route'],
                                    ['status'=>'livrée',      'ico'=>'📦','lbl'=>'Livrée',       'sub'=>'Client a reçu sa commande'],
                                ];
                                $curIdx = array_search($order->status, array_column($steps,'status'));
                            @endphp
                            @foreach($steps as $idx => $step)
                            @php
                                $isDone    = $curIdx !== false && $idx < $curIdx;
                                $isCurrent = $curIdx !== false && $idx === $curIdx;
                                $cls = $isDone ? 'done' : ($isCurrent ? 'current' : 'wait');
                            @endphp
                            <div class="tl-step">
                                <div class="tl-dot {{ $cls }}">{{ $isDone ? '✓' : $step['ico'] }}</div>
                                <div class="tl-info">
                                    <div class="tl-lbl" style="color:{{ $isCurrent ? 'var(--brand)' : ($isDone ? 'var(--green)' : 'var(--muted)') }}">
                                        {{ $step['lbl'] }}
                                    </div>
                                    <div class="tl-sub">{{ $step['sub'] }}</div>
                                </div>
                            </div>
                            @endforeach
                            @if($order->status === 'annulée')
                            <div class="tl-step">
                                <div class="tl-dot" style="background:var(--rbg);border:2px solid rgba(239,68,68,.3)">❌</div>
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
                    <div class="dc-h"><span style="font-size:16px">🏪</span><div class="dc-t">Boutique</div></div>
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
                    <div class="dc-h"><span style="font-size:16px">⭐</span><div class="dc-t">Avis client</div></div>
                    <div class="dc-body">
                        <div style="font-size:18px;margin-bottom:6px">
                            @for($i=1;$i<=5;$i++)
                                {{ $i <= ($order->review->rating ?? 0) ? '⭐' : '☆' }}
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
