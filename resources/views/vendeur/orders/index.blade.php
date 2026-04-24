{{-- resources/views/vendeur/orders/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Commandes reçues')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
:root {
    --brand:#6366f1; --brand-dk:#4f46e5; --brand-lt:#e0e7ff; --brand-mlt:#eef2ff;
    --navy:#0f172a; --navy2:#1e1b4b;
    --bg:#f8fafc; --surface:#fff; --border:#e2e8f0; --border-dk:#cbd5e1;
    --text:#0f172a; --text-2:#475569; --muted:#94a3b8;
    --danger:#ef4444; --danger-lt:#fef2f2;
    --shadow-sm:0 1px 3px rgba(0,0,0,.06);
    --shadow:0 4px 16px rgba(0,0,0,.07);
    --r:14px; --r-sm:9px;
    --font:'Segoe UI',system-ui,sans-serif;
    --mono:'JetBrains Mono',monospace;
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: var(--font); background: var(--bg); color: var(--text); }

/* ── HERO ── */
.ord-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy2) 60%, #312e81 100%);
    padding: 28px 28px 76px;
    position: relative; overflow: hidden;
}
.ord-hero::before {
    content: ''; position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Ccircle cx='30' cy='30' r='1.5' fill='%236366f1' opacity='.08'/%3E%3C/svg%3E");
}
.ord-hero-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; position: relative; }
.ord-hero-title { font-size: 22px; font-weight: 900; color: #fff; margin-top: 18px; position: relative; letter-spacing: -.4px; }
.ord-hero-sub { font-size: 13px; color: rgba(255,255,255,.55); margin-top: 4px; position: relative; }

/* Export buttons group */
.export-group { display: flex; gap: 7px; flex-wrap: wrap; }
.btn-export {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 8px 14px; border-radius: 8px; font-size: 11.5px; font-weight: 700;
    text-decoration: none; transition: all .15s; white-space: nowrap;
    border: 1.5px solid rgba(255,255,255,.2); background: rgba(255,255,255,.1); color: #fff;
}
.btn-export:hover { background: rgba(255,255,255,.2); color: #fff; }

/* ── KPI FLOTTANTS ── */
.ord-kpi-row {
    display: flex; gap: 14px;
    padding: 0 28px; margin-top: -48px;
    position: relative; z-index: 2; flex-wrap: wrap;
}
.ord-kpi {
    flex: 1; min-width: 130px;
    background: var(--surface); border-radius: var(--r);
    box-shadow: 0 4px 20px rgba(0,0,0,.09);
    padding: 14px 18px;
    display: flex; align-items: center; gap: 12px;
    border-top: 3px solid transparent;
}
.ord-kpi.indigo  { border-top-color: var(--brand); }
.ord-kpi.amber   { border-top-color: #f59e0b; }
.ord-kpi.blue    { border-top-color: #3b82f6; }
.ord-kpi.green   { border-top-color: #10b981; }
.ord-kpi-ico { font-size: 22px; flex-shrink: 0; }
.ord-kpi-val { font-size: 22px; font-weight: 900; color: var(--text); line-height: 1; font-family: var(--mono); }
.ord-kpi-lbl { font-size: 11px; font-weight: 600; color: var(--muted); margin-top: 3px; text-transform: uppercase; letter-spacing: .3px; }

/* ── BODY ── */
.ord-body { padding: 24px 28px 60px; }

/* Flash */
.flash-success { background: var(--brand-mlt); border: 1px solid #a5b4fc; color: #3730a3; padding: 12px 16px; border-radius: var(--r-sm); margin-bottom: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }

/* ── TABLE CARD ── */
.ord-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden; box-shadow: var(--shadow-sm);
}
.ord-card-hd {
    padding: 14px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--bg);
}
.ord-card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.ord-count { font-size: 11px; color: var(--muted); font-weight: 600; background: var(--bg); border: 1px solid var(--border); padding: 2px 10px; border-radius: 20px; }

/* Table */
.tbl { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.tbl thead th {
    padding: 11px 14px; text-align: left;
    font-size: 10px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: .6px;
    background: var(--bg); border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
.tbl tbody td { padding: 13px 14px; border-bottom: 1px solid #f3f6f9; vertical-align: middle; }
.tbl tbody tr:last-child td { border-bottom: none; }
.tbl tbody tr:hover td { background: #f8faff; }

/* Client cell */
.cli-name  { font-size: 13px; font-weight: 700; color: var(--text); }
.cli-email { font-size: 11px; color: var(--muted); margin-top: 2px; }

/* Product list */
.prod-list { display: flex; flex-direction: column; gap: 8px; max-width: 320px; }
.prod-item { display: flex; align-items: center; gap: 9px; }
.prod-img  { width: 46px; height: 46px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; }
.prod-ph   { width: 46px; height: 46px; border-radius: 8px; background: var(--bg); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.prod-name { font-size: 12.5px; font-weight: 600; color: var(--text); }
.prod-meta { font-size: 11px; color: var(--muted); margin-top: 2px; }

/* Amount */
.amount { font-family: var(--mono); font-weight: 800; font-size: 13px; color: var(--text); white-space: nowrap; }

/* Status badges */
.pill { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 700; padding: 4px 10px; border-radius: 20px; white-space: nowrap; }
.pill-pending  { background: #fef3c7; color: #92400e; }
.pill-confirm  { background: var(--brand-lt); color: #3730a3; }
.pill-delivery { background: #dbeafe; color: #1e40af; }
.pill-done     { background: #d1fae5; color: #065f46; }
.pill-cancel   { background: #fee2e2; color: #991b1b; }
.pill-unknown  { background: #f3f4f6; color: #6b7280; }

/* Action buttons */
.act-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 700;
    cursor: pointer; border: none; text-decoration: none; font-family: var(--font);
    transition: all .15s; white-space: nowrap;
}
.act-btn-confirm  { background: var(--brand); color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.25); }
.act-btn-confirm:hover  { background: var(--brand-dk); }
.act-btn-cancel   { background: var(--danger-lt); color: var(--danger); border: 1px solid #fca5a5; }
.act-btn-cancel:hover   { background: #fecaca; }
.act-btn-detail   { background: var(--brand-mlt); color: var(--brand-dk); border: 1px solid var(--brand-lt); }
.act-btn-detail:hover   { background: var(--brand-lt); }
.act-btn-assign   { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.act-btn-assign:hover   { background: #fef3c7; }
.act-btn-ordonnance { background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; }
.act-btn-ordonnance:hover { background: #e0f2fe; }

/* Actions column group */
.act-group { display: flex; flex-wrap: wrap; gap: 5px; }

/* ── MOBILE CARDS ── */
.mob-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); box-shadow: var(--shadow-sm);
    overflow: hidden; margin-bottom: 12px;
}
.mob-card-hd {
    padding: 12px 16px; display: flex; align-items: center;
    justify-content: space-between; border-bottom: 1px solid var(--border);
    background: var(--bg);
}
.mob-card-body { padding: 14px 16px; }
.mob-card-foot { padding: 10px 16px; border-top: 1px solid var(--border); display: flex; gap: 6px; flex-wrap: wrap; }

/* Pagination */
.pag-wrap { display: flex; justify-content: center; padding: 20px 0 8px; }

/* Responsive */
@media (max-width: 767px) {
    .ord-hero { padding: 20px 16px 68px; }
    .ord-kpi-row { padding: 0 16px; gap: 10px; }
    .ord-body { padding: 16px; }
    .ord-kpi-val { font-size: 18px; }
    .export-group { display: none; }
}
@media (min-width: 768px) {
    .mob-only { display: none !important; }
}
@media (max-width: 767px) {
    .desk-only { display: none !important; }
}
</style>
@endpush

@section('content')

{{-- ── HERO ── --}}
<div class="ord-hero">
    <div class="ord-hero-top">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(99,102,241,.25);display:flex;align-items:center;justify-content:center;font-size:18px;">📦</div>
            <span style="color:rgba(255,255,255,.8);font-size:13px;font-weight:600;">Boutique</span>
        </div>
        <div class="export-group">
            <a href="{{ route('boutique.export.orders.excel') }}" class="btn-export">⬇ Cmd Excel</a>
            <a href="{{ route('boutique.export.orders.pdf') }}" class="btn-export" target="_blank">⬇ Cmd PDF</a>
            <a href="{{ route('boutique.export.payments.excel') }}" class="btn-export">⬇ Paiem Excel</a>
            <a href="{{ route('boutique.export.payments.pdf') }}" class="btn-export">⬇ Paiem PDF</a>
            <a href="{{ route('boutique.export.stats.pdf') }}" class="btn-export">⬇ Stats PDF</a>
        </div>
    </div>
    <div class="ord-hero-title">📦 Commandes reçues</div>
    <div class="ord-hero-sub">Gérez, confirmez et suivez vos commandes rapidement.</div>
</div>

{{-- ── KPI FLOTTANTS ── --}}
<div class="ord-kpi-row">
    <div class="ord-kpi indigo">
        <div class="ord-kpi-ico">📦</div>
        <div>
            <div class="ord-kpi-val">{{ $orders->total() }}</div>
            <div class="ord-kpi-lbl">Total commandes</div>
        </div>
    </div>
    <div class="ord-kpi amber">
        <div class="ord-kpi-ico">⏳</div>
        <div>
            <div class="ord-kpi-val">{{ $orders->filter(fn($o) => $o->status === \App\Models\Order::STATUS_EN_ATTENTE)->count() }}</div>
            <div class="ord-kpi-lbl">En attente</div>
        </div>
    </div>
    <div class="ord-kpi blue">
        <div class="ord-kpi-ico">🚚</div>
        <div>
            <div class="ord-kpi-val">{{ $orders->filter(fn($o) => $o->status === \App\Models\Order::STATUS_EN_LIVRAISON)->count() }}</div>
            <div class="ord-kpi-lbl">En livraison</div>
        </div>
    </div>
    <div class="ord-kpi green">
        <div class="ord-kpi-ico">✅</div>
        <div>
            <div class="ord-kpi-val">{{ $orders->filter(fn($o) => $o->status === \App\Models\Order::STATUS_LIVREE)->count() }}</div>
            <div class="ord-kpi-lbl">Livrées</div>
        </div>
    </div>
</div>

{{-- ── BODY ── --}}
<div class="ord-body">

    @if(session('success'))
    <div class="flash-success">✅ {{ session('success') }}</div>
    @endif

    {{-- Exports mobile --}}
    <div class="mob-only" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;">
        <a href="{{ route('boutique.export.orders.excel') }}" class="act-btn act-btn-detail" style="font-size:11px;">⬇ Cmd Excel</a>
        <a href="{{ route('boutique.export.orders.pdf') }}" class="act-btn act-btn-detail" target="_blank" style="font-size:11px;">⬇ Cmd PDF</a>
        <a href="{{ route('boutique.export.stats.pdf') }}" class="act-btn act-btn-detail" style="font-size:11px;">⬇ Stats PDF</a>
    </div>

    {{-- ══ DESKTOP TABLE ══ --}}
    <div class="desk-only">
        <div class="ord-card">
            <div class="ord-card-hd">
                <span class="ord-card-title">Liste des commandes</span>
                <span class="ord-count">{{ $orders->total() }} commande(s)</span>
            </div>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Produits</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Ordonnance</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td style="font-family:var(--mono);font-size:11px;color:var(--muted);">#{{ $order->id }}</td>
                        <td>
                            <div class="cli-name">{{ $order->client->name }}</div>
                            <div class="cli-email">{{ $order->client->email }}</div>
                        </td>
                        <td>
                            <div class="prod-list">
                                @foreach($order->items as $item)
                                <div class="prod-item">
                                    @if($item->product && $item->product->image)
                                        <img class="prod-img" src="{{ asset('storage/'.$item->product->image) }}" alt="">
                                    @else
                                        <div class="prod-ph">📦</div>
                                    @endif
                                    <div>
                                        <div class="prod-name">{{ Str::limit($item->product->name ?? '—', 45) }}</div>
                                        <div class="prod-meta">Qté: {{ $item->quantity }} · {{ number_format($item->price,0,',',' ') }} GNF</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td><span class="amount">{{ number_format($order->total,0,',',' ') }} <small style="font-size:10px;color:var(--muted);font-weight:500;">GNF</small></span></td>
                        <td>
                            @switch($order->status)
                                @case(\App\Models\Order::STATUS_EN_ATTENTE)
                                    <span class="pill pill-pending">⏳ En attente</span>
                                @break
                                @case(\App\Models\Order::STATUS_CONFIRMEE)
                                    <span class="pill pill-confirm">📦 Confirmée</span>
                                @break
                                @case(\App\Models\Order::STATUS_EN_LIVRAISON)
                                    <span class="pill pill-delivery">🚚 En livraison</span>
                                @break
                                @case(\App\Models\Order::STATUS_LIVREE)
                                    <span class="pill pill-done">✅ Livrée</span>
                                @break
                                @case(\App\Models\Order::STATUS_ANNULEE)
                                    <span class="pill pill-cancel">❌ Annulée</span>
                                @break
                                @default
                                    <span class="pill pill-unknown">❔ Inconnu</span>
                            @endswitch
                        </td>
                        <td>
                            @if($order->shop && strtolower($order->shop->type) === 'pharmacie' && $order->ordonnance)
                                <button class="act-btn act-btn-ordonnance" data-bs-toggle="modal" data-bs-target="#ordonnanceModal" data-url="{{ asset('storage/'.$order->ordonnance) }}">📎 Voir</button>
                            @else
                                <span style="color:var(--muted);font-size:12px;">—</span>
                            @endif
                        </td>
                        <td style="font-size:11.5px;color:var(--text-2);white-space:nowrap;">{{ $order->created_at->format('d/m/Y') }}<br><span style="color:var(--muted);font-size:10.5px;">{{ $order->created_at->format('H:i') }}</span></td>
                        <td>
                            <div class="act-group">
                                @if($order->status == \App\Models\Order::STATUS_EN_ATTENTE)
                                    <form action="{{ route('orders.confirm', $order) }}" method="POST" style="display:inline">
                                        @csrf @method('PUT')
                                        <button class="act-btn act-btn-confirm" type="submit">✅ Confirmer</button>
                                    </form>
                                    <form action="{{ route('orders.cancel', $order) }}" method="POST" style="display:inline">
                                        @csrf @method('PUT')
                                        <button class="act-btn act-btn-cancel" type="submit">❌ Annuler</button>
                                    </form>
                                @else
                                    <a href="{{ route('orders.show', $order) }}" class="act-btn act-btn-detail">🔎 Détails</a>
                                @endif
                                <a href="{{ route('orders.assign.show', $order) }}" class="act-btn act-btn-assign">🚚 Assigner</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:48px;color:var(--muted);">
                            <div style="font-size:36px;opacity:.25;margin-bottom:12px;">📭</div>
                            <div style="font-size:14px;font-weight:600;">Aucune commande reçue</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pag-wrap">{{ $orders->links() }}</div>
    </div>

    {{-- ══ MOBILE CARDS ══ --}}
    <div class="mob-only">
        @forelse($orders as $order)
        <div class="mob-card">
            <div class="mob-card-hd">
                <div>
                    <div class="cli-name">{{ $order->client->name }}</div>
                    <div class="cli-email">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @switch($order->status)
                    @case(\App\Models\Order::STATUS_EN_ATTENTE)    <span class="pill pill-pending">⏳</span>  @break
                    @case(\App\Models\Order::STATUS_CONFIRMEE)      <span class="pill pill-confirm">📦</span>  @break
                    @case(\App\Models\Order::STATUS_EN_LIVRAISON)   <span class="pill pill-delivery">🚚</span> @break
                    @case(\App\Models\Order::STATUS_LIVREE)         <span class="pill pill-done">✅</span>     @break
                    @case(\App\Models\Order::STATUS_ANNULEE)        <span class="pill pill-cancel">❌</span>   @break
                    @default                                         <span class="pill pill-unknown">❔</span>
                @endswitch
            </div>
            <div class="mob-card-body">
                <div class="prod-list" style="max-width:none">
                    @foreach($order->items as $item)
                    <div class="prod-item">
                        @if($item->product && $item->product->image)
                            <img class="prod-img" src="{{ asset('storage/'.$item->product->image) }}" alt="">
                        @else
                            <div class="prod-ph">📦</div>
                        @endif
                        <div>
                            <div class="prod-name">{{ Str::limit($item->product->name ?? '—', 38) }}</div>
                            <div class="prod-meta">Qté: {{ $item->quantity }} · {{ number_format($item->price,0,',',' ') }} GNF</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:10px;border-top:1px solid var(--border);">
                    <span class="amount">{{ number_format($order->total,0,',',' ') }} GNF</span>
                    @if($order->shop && strtolower($order->shop->type) === 'pharmacie' && $order->ordonnance)
                        <button class="act-btn act-btn-ordonnance" data-bs-toggle="modal" data-bs-target="#ordonnanceModal" data-url="{{ asset('storage/'.$order->ordonnance) }}">📎 Ordonnance</button>
                    @endif
                </div>
            </div>
            <div class="mob-card-foot">
                @if($order->status == \App\Models\Order::STATUS_EN_ATTENTE)
                    <form action="{{ route('orders.confirm', $order) }}" method="POST" style="flex:1">
                        @csrf @method('PUT')
                        <button class="act-btn act-btn-confirm" type="submit" style="width:100%">✅ Confirmer</button>
                    </form>
                    <form action="{{ route('orders.cancel', $order) }}" method="POST" style="flex:1">
                        @csrf @method('PUT')
                        <button class="act-btn act-btn-cancel" type="submit" style="width:100%">❌ Annuler</button>
                    </form>
                @else
                    <a href="{{ route('orders.show', $order) }}" class="act-btn act-btn-detail" style="flex:1;justify-content:center">🔎 Détails</a>
                @endif
                <a href="{{ route('orders.assign.show', $order) }}" class="act-btn act-btn-assign" style="flex:1;justify-content:center">🚚 Assigner</a>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:48px 16px;color:var(--muted);">
            <div style="font-size:36px;opacity:.25;margin-bottom:10px;">📭</div>
            <div style="font-size:14px;font-weight:600;">Aucune commande reçue</div>
        </div>
        @endforelse
        <div class="pag-wrap">{{ $orders->links() }}</div>
    </div>

</div>

{{-- Modal ordonnance --}}
<div class="modal fade" id="ordonnanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:var(--navy);border:none;padding:16px 20px;">
                <h5 class="modal-title" style="color:#fff;font-size:15px;font-weight:700;">📎 Ordonnance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe src="" id="ordonnanceFrame" style="width:100%;height:70vh;" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
var ordonnanceModal = document.getElementById('ordonnanceModal');
if(ordonnanceModal){
    ordonnanceModal.addEventListener('show.bs.modal', function(event){
        document.getElementById('ordonnanceFrame').src = event.relatedTarget.getAttribute('data-url') || '';
    });
    ordonnanceModal.addEventListener('hidden.bs.modal', function(){
        document.getElementById('ordonnanceFrame').src = '';
    });
}
</script>
@endpush
