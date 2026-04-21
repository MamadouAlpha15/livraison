@extends('layouts.app')
@section('title', 'Mes livraisons')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
:root {
    --navy:#0f172a; --navy2:#1e3a5f; --orange:#f90; --orange-dk:#e47911;
    --green:#10b981; --green-lt:#d1fae5; --green-dk:#065f46;
    --blue:#3b82f6; --blue-lt:#dbeafe; --blue-dk:#1d4ed8;
    --yellow:#f59e0b; --yellow-lt:#fef3c7; --yellow-dk:#92400e;
    --red:#ef4444; --red-lt:#fee2e2; --red-dk:#991b1b;
    --border:#e9edef; --muted:#64748b; --text:#0f172a; --bg:#f1f5f9;
    --font:'Segoe UI',sans-serif;
}
*,*::before,*::after{box-sizing:border-box}
body{margin:0;font-family:var(--font);background:var(--bg);color:var(--text)}

/* ── HERO ── */
.ord-hero {
    background:linear-gradient(135deg,var(--navy) 0%,var(--navy2) 60%,#1a4a7a 100%);
    padding:24px 24px 72px; position:relative; overflow:hidden;
}
.ord-hero::before {
    content:'';position:absolute;inset:0;
    background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Ccircle cx='30' cy='30' r='1.5' fill='%23ffffff' opacity='.06'/%3E%3C/svg%3E");
}
.ord-hero-top { display:flex; align-items:center; justify-content:space-between; position:relative; gap:12px; }
.ord-hero-back { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; background:rgba(255,255,255,.12); color:#fff; border:1.5px solid rgba(255,255,255,.2); border-radius:10px; font-size:13px; font-weight:700; text-decoration:none; transition:all .15s; white-space:nowrap; }
.ord-hero-back:hover { background:rgba(255,255,255,.22); color:#fff; transform:translateX(-2px); }
.ord-hero-title { font-size:22px; font-weight:900; color:#fff; margin-top:18px; position:relative; }
.ord-hero-sub   { font-size:13px; color:rgba(255,255,255,.55); margin-top:4px; position:relative; }

/* ── FILTRES ── */
.ord-filters { display:flex; gap:8px; flex-wrap:wrap; padding:0 24px; margin-top:-48px; position:relative; z-index:2; }
.ord-filter-btn { padding:9px 16px; border-radius:20px; border:none; font-size:12.5px; font-weight:700; cursor:pointer; font-family:var(--font); background:#fff; color:var(--muted); box-shadow:0 2px 8px rgba(0,0,0,.08); transition:all .15s; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.ord-filter-btn:hover, .ord-filter-btn.active { background:var(--orange); color:#fff; box-shadow:0 4px 14px rgba(255,153,0,.35); }

/* ── BODY ── */
.ord-body { padding:20px 24px; display:flex; flex-direction:column; gap:14px; }

/* ── CARTE COMMANDE ── */
.ord-card {
    background:#fff; border-radius:16px;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    overflow:hidden; transition:transform .15s, box-shadow .15s;
    border-left:4px solid transparent;
}
.ord-card:hover { transform:translateY(-2px); box-shadow:0 6px 24px rgba(0,0,0,.1); }
.ord-card.delivering { border-left-color:var(--blue); }
.ord-card.confirmed  { border-left-color:var(--yellow); }
.ord-card.delivered  { border-left-color:var(--green); }
.ord-card.other      { border-left-color:var(--border); }

/* Header carte */
.ord-card-head { padding:14px 18px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f8fafc; gap:10px; }
.ord-card-num { font-size:13px; font-weight:800; color:var(--text); }
.ord-card-date { font-size:11px; color:var(--muted); margin-top:2px; }
.ord-badge { font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px; white-space:nowrap; flex-shrink:0; }
.ord-badge.delivering { background:var(--blue-lt); color:var(--blue-dk); }
.ord-badge.confirmed  { background:var(--yellow-lt); color:var(--yellow-dk); }
.ord-badge.delivered  { background:var(--green-lt); color:var(--green-dk); }
.ord-badge.cancelled  { background:var(--red-lt); color:var(--red-dk); }
.ord-badge.other      { background:#f3f4f6; color:var(--muted); }

/* Body carte */
.ord-card-body { padding:14px 18px; display:flex; gap:14px; align-items:flex-start; }
.ord-prod-img { width:56px; height:56px; border-radius:10px; object-fit:cover; border:1.5px solid var(--border); flex-shrink:0; }
.ord-prod-ph  { width:56px; height:56px; border-radius:10px; background:var(--bg); border:1.5px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
.ord-info { flex:1; min-width:0; display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.ord-info-block .lbl { font-size:10.5px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:3px; }
.ord-info-block .val { font-size:13px; font-weight:600; color:var(--text); }
.ord-info-block .val.amount { font-size:15px; font-weight:900; color:var(--navy); font-family:monospace; }
.ord-info-block .val.phone  { color:var(--green-dk); }
.ord-wa-link { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; color:#25d366; text-decoration:none; margin-top:3px; }

/* Footer carte — boutons action */
.ord-card-foot { padding:12px 18px; background:#f8fafc; border-top:1px solid var(--border); display:flex; gap:10px; }
.ord-action-btn {
    flex:1; padding:11px; border-radius:10px; border:none; cursor:pointer;
    font-size:13.5px; font-weight:700; font-family:var(--font);
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition:all .15s;
}
.ord-action-btn.start    { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; box-shadow:0 4px 14px rgba(59,130,246,.35); }
.ord-action-btn.start:hover { background:linear-gradient(135deg,#2563eb,#1d4ed8); transform:translateY(-1px); }
.ord-action-btn.complete { background:linear-gradient(135deg,var(--green),#059669); color:#fff; box-shadow:0 4px 14px rgba(16,185,129,.35); }
.ord-action-btn.complete:hover { background:linear-gradient(135deg,#059669,#047857); transform:translateY(-1px); }
.ord-action-gps { padding:11px 14px; border-radius:10px; border:1.5px solid var(--blue-lt); background:var(--blue-lt); color:var(--blue-dk); cursor:pointer; font-size:18px; transition:all .15s; }
.ord-action-gps:hover { background:var(--blue); color:#fff; }
.ord-action-gps.active { background:var(--blue); color:#fff; border-color:var(--blue); }

/* ── EMPTY ── */
.ord-empty { padding:60px 24px; text-align:center; color:var(--muted); }
.ord-empty-ico { font-size:52px; display:block; margin-bottom:14px; opacity:.3; }
.ord-empty-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:6px; }
.ord-empty-sub { font-size:13px; line-height:1.6; }

/* ── PAGINATION ── */
.ord-pagination { padding:0 24px 24px; }

/* ── SUCCESS TOAST ── */
.ord-toast { margin:0 24px 16px; padding:14px 18px; background:var(--green-lt); border:1px solid #a7f3d0; border-radius:12px; color:var(--green-dk); font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px; }

/* ── RESPONSIVE ── */
@media(max-width:768px) {
    .ord-hero { padding:18px 16px 64px; }
    .ord-hero-title { font-size:18px; }
    .ord-filters { padding:0 16px; }
    .ord-body { padding:14px 16px; }
    .ord-info { grid-template-columns:1fr; gap:8px; }
    .ord-card-foot { flex-direction:column; }
}
@media(max-width:400px) {
    .ord-hero-back span { display:none; }
    .ord-card-head { flex-wrap:wrap; }
}
</style>
@endpush

@section('content')
@php
    $fmt = fn($n) => number_format($n ?? 0, 0, ',', ' ') . ' ' . $devise;

    $statusMap = [
        'confirmed'    => ['label'=>'📦 Confirmée',    'cls'=>'confirmed'],
        'confirmée'    => ['label'=>'📦 Confirmée',    'cls'=>'confirmed'],
        'confirmé'     => ['label'=>'📦 Confirmée',    'cls'=>'confirmed'],
        'delivering'   => ['label'=>'🚴 En livraison', 'cls'=>'delivering'],
        'en_livraison' => ['label'=>'🚴 En livraison', 'cls'=>'delivering'],
        'en-livraison' => ['label'=>'🚴 En livraison', 'cls'=>'delivering'],
        'shipped'      => ['label'=>'🚴 En livraison', 'cls'=>'delivering'],
        'delivered'    => ['label'=>'✅ Livrée',        'cls'=>'delivered'],
        'livrée'       => ['label'=>'✅ Livrée',        'cls'=>'delivered'],
        'livree'       => ['label'=>'✅ Livrée',        'cls'=>'delivered'],
        'completed'    => ['label'=>'✅ Livrée',        'cls'=>'delivered'],
        'cancelled'    => ['label'=>'⚠️ Annulée',      'cls'=>'cancelled'],
        'annulée'      => ['label'=>'⚠️ Annulée',      'cls'=>'cancelled'],
    ];

    $startStatuses    = ['confirmed','confirmée','confirmé'];
    $deliverStatuses  = ['delivering','en_livraison','en-livraison','shipped'];
    $doneStatuses     = ['delivered','livrée','livree','completed'];
@endphp

{{-- HERO --}}
<div class="ord-hero">
    <div class="ord-hero-top">
        <a href="{{ route('livreur.dashboard') }}" class="ord-hero-back">← <span>Tableau de bord</span></a>
        <div style="color:rgba(255,255,255,.7);font-size:12px;font-weight:600">{{ $orders->total() }} commande(s)</div>
    </div>
    <div class="ord-hero-title">🚴 Mes livraisons</div>
    <div class="ord-hero-sub">Gérez toutes vos commandes assignées</div>
</div>

{{-- FILTRES --}}
<div class="ord-filters">
    <a href="{{ route('livreur.orders.index') }}" class="ord-filter-btn {{ !request('status') ? 'active' : '' }}">Toutes</a>
    <a href="{{ route('livreur.orders.index', ['status'=>'confirmed']) }}" class="ord-filter-btn {{ request('status')==='confirmed' ? 'active' : '' }}">📦 À récupérer</a>
    <a href="{{ route('livreur.orders.index', ['status'=>'delivering']) }}" class="ord-filter-btn {{ request('status')==='delivering' ? 'active' : '' }}">🚴 En cours</a>
    <a href="{{ route('livreur.orders.index', ['status'=>'delivered']) }}" class="ord-filter-btn {{ request('status')==='delivered' ? 'active' : '' }}">✅ Livrées</a>
</div>

{{-- BODY --}}
<div class="ord-body">

    @if(session('success'))
    <div class="ord-toast">✅ {{ session('success') }}</div>
    @endif

    @forelse($orders as $order)
    @php
        $s       = strtolower($order->status ?? '');
        $st      = $statusMap[$s] ?? ['label'=>ucfirst($order->status),'cls'=>'other'];
        $client  = $order->client ?? $order->user;
        $item    = $order->items->first();
        $waNum   = preg_replace('/\D/', '', $client?->phone ?? '');
    @endphp

    <div class="ord-card {{ $st['cls'] }}" data-order-id="{{ $order->id }}">

        {{-- Header --}}
        <div class="ord-card-head">
            <div>
                <div class="ord-card-num">Commande #{{ $order->id }}</div>
                <div class="ord-card-date">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
            </div>
            <span class="ord-badge {{ $st['cls'] }}">{{ $st['label'] }}</span>
        </div>

        {{-- Body --}}
        <div class="ord-card-body">
            {{-- Image produit --}}
            @if($item?->product?->image)
                <img src="{{ \App\Services\ImageOptimizer::url($item->product->image, 'thumb') ?? asset('storage/'.$item->product->image) }}"
                     alt="{{ $item->product->name }}" class="ord-prod-img" loading="lazy">
            @else
                <div class="ord-prod-ph">📦</div>
            @endif

            <div class="ord-info">
                <div class="ord-info-block">
                    <div class="lbl">Client</div>
                    <div class="val">{{ $client?->name ?? 'Inconnu' }}</div>
                    @if($client?->phone)
                    <a href="tel:{{ $client->phone }}" class="val phone" style="font-size:12px;text-decoration:none">📞 {{ $client->phone }}</a>
                    @if($waNum)
                    <a href="https://wa.me/{{ $waNum }}" target="_blank" class="ord-wa-link">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#25d366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    @endif
                    @endif
                </div>

                <div class="ord-info-block">
                    <div class="lbl">Montant</div>
                    <div class="val amount">{{ $fmt($order->total) }}</div>
                </div>

                <div class="ord-info-block">
                    <div class="lbl">Produit</div>
                    <div class="val" style="font-size:12px">{{ $item?->product?->name ?? 'N/A' }}</div>
                    @if($item)<div style="font-size:11px;color:var(--muted)">Qté : {{ $item->quantity }}</div>@endif
                </div>

                <div class="ord-info-block">
                    <div class="lbl">Adresse</div>
                    <div class="val" style="font-size:12px;color:var(--muted)">📍 {{ $client?->address ?? 'Non renseignée' }}</div>
                </div>
            </div>
        </div>

        {{-- Footer actions --}}
        @if(in_array($s, $startStatuses) || in_array($s, $deliverStatuses))
        <div class="ord-card-foot">
            @if(in_array($s, $startStatuses))
                <form action="{{ route('livreur.orders.start', $order) }}" method="POST" style="flex:1">
                    @csrf @method('PUT')
                    <button type="submit" class="ord-action-btn start" style="width:100%">
                        🚴 Commencer la livraison
                    </button>
                </form>

            @elseif(in_array($s, $deliverStatuses))
                <button type="button" class="ord-action-gps" id="gpsBtn_{{ $order->id }}"
                        onclick="toggleGps({{ $order->id }}, this)" title="GPS tracking">📡</button>
                <form action="{{ route('livreur.orders.complete', $order) }}" method="POST" style="flex:1"
                      onsubmit="return confirm('Confirmer la livraison de cette commande ?')">
                    @csrf @method('PUT')
                    <button type="submit" class="ord-action-btn complete" style="width:100%">
                        ✅ Marquer comme livrée
                    </button>
                </form>
            @endif
        </div>
        @endif

    </div>
    @empty
    <div class="ord-empty">
        <span class="ord-empty-ico">📭</span>
        <div class="ord-empty-title">Aucune commande assignée</div>
        <div class="ord-empty-sub">Vous n'avez pas encore de livraisons.<br>Revenez plus tard !</div>
    </div>
    @endforelse

</div>

@if($orders->hasPages())
<div class="ord-pagination">{{ $orders->links() }}</div>
@endif

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const gpsWatchers = {};

function toggleGps(orderId, btn) {
    if (gpsWatchers[orderId]) {
        stopGps(orderId, btn);
    } else {
        startGps(orderId, btn);
    }
}

function startGps(orderId, btn) {
    if (!('geolocation' in navigator)) { alert('GPS non supporté.'); return; }
    const opts = { enableHighAccuracy:true, maximumAge:5000, timeout:15000 };
    const id = navigator.geolocation.watchPosition(async pos => {
        try {
            await fetch(`/orders/${orderId}/position`, {
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest'},
                body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude })
            });
        } catch(e) { console.warn('GPS err:', e); }
    }, err => {
        console.warn('GPS error:', err);
        stopGps(orderId, btn);
    }, opts);
    gpsWatchers[orderId] = id;
    btn.classList.add('active');
    btn.title = 'GPS actif — cliquer pour arrêter';
}

function stopGps(orderId, btn) {
    navigator.geolocation.clearWatch(gpsWatchers[orderId]);
    delete gpsWatchers[orderId];
    btn.classList.remove('active');
    btn.title = 'Activer le GPS';
}

document.addEventListener('DOMContentLoaded', () => {
    @if(session('autostart_gps_order_id'))
    const btn = document.getElementById('gpsBtn_{{ session('autostart_gps_order_id') }}');
    if (btn) startGps({{ session('autostart_gps_order_id') }}, btn);
    @endif

    @foreach($orders as $o)
    @php $os = strtolower($o->status ?? '') @endphp
    @if(in_array($os, ['delivering','en_livraison','en-livraison','shipped']))
    (function(){ const btn = document.getElementById('gpsBtn_{{ $o->id }}'); if(btn) startGps({{ $o->id }}, btn); })();
    @endif
    @endforeach
});
</script>
@endpush
