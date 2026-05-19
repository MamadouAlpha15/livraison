<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Commandes</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; background: #fff; }

/* ── En-tête ── */
.header {
    background-color: #1e293b;
    color: #fff;
    padding: 10px 14px;
    margin-bottom: 10px;
}
.header-inner { width: 100%; border-collapse: collapse; }
.header-inner td { vertical-align: middle; }
.header-title { font-size: 15px; font-weight: bold; color: #fff; }
.header-sub { font-size: 9px; color: #94a3b8; margin-top: 3px; }
.header-right { text-align: right; font-size: 9px; color: #94a3b8; }
.header-date { font-size: 12px; font-weight: bold; color: #e0e7ff; }

/* ── Stats ── */
.stats-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.stats-table td {
    width: 20%;
    border: 1px solid #e2e8f0;
    padding: 7px 8px;
    text-align: center;
    background-color: #f8fafc;
    vertical-align: middle;
}
.stats-val { font-size: 15px; font-weight: bold; color: #1e293b; }
.stats-lbl { font-size: 8.5px; color: #64748b; margin-top: 2px; text-transform: uppercase; }

/* ── Tableau commandes ── */
.orders-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.orders-table thead tr { background-color: #334155; }
.orders-table thead th {
    color: #fff;
    padding: 6px 7px;
    text-align: left;
    font-size: 8.5px;
    font-weight: bold;
    border: 1px solid #475569;
    word-break: break-word;
    overflow: hidden;
}
.orders-table tbody tr { border-bottom: 1px solid #e2e8f0; }
.orders-table tbody tr.even { background-color: #f8fafc; }
.orders-table tbody td {
    padding: 6px 7px;
    vertical-align: top;
    border: 1px solid #e2e8f0;
    font-size: 9.5px;
    word-break: break-word;
    overflow: hidden;
}
.orders-table tfoot td {
    padding: 6px 8px;
    border: 1px solid #334155;
    background-color: #f1f5f9;
    font-weight: bold;
    font-size: 10.5px;
    word-break: break-word;
}

/* ── Statuts ── */
.s-wait   { color: #92400e; font-weight: bold; }
.s-conf   { color: #1e40af; font-weight: bold; }
.s-deliv  { color: #3730a3; font-weight: bold; }
.s-done   { color: #166534; font-weight: bold; }
.s-cancel { color: #991b1b; font-weight: bold; }

/* ── Articles ── */
.items { font-size: 8.5px; color: #475569; margin-top: 2px; line-height: 1.5; }
.item-line { display: block; margin-bottom: 1px; }

/* ── Texte atténué ── */
.muted { color: #94a3b8; font-size: 8.5px; }
.strong { font-weight: bold; }

/* ── Pied de page ── */
.footer {
    margin-top: 10px;
    border-top: 1px solid #e2e8f0;
    padding-top: 6px;
    font-size: 8.5px;
    color: #94a3b8;
}
.footer-inner { width: 100%; border-collapse: collapse; }
.footer-inner td { vertical-align: middle; }
.footer-right { text-align: right; }

/* ── Pagination DomPDF ── */
.page-num { font-size: 8.5px; color: #94a3b8; }

/* ── Badge statut ── */
.badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 8px;
    font-weight: bold;
}
.badge-wait   { background: #fef3c7; color: #92400e; }
.badge-conf   { background: #dbeafe; color: #1e40af; }
.badge-deliv  { background: #ede9fe; color: #3730a3; }
.badge-done   { background: #dcfce7; color: #166534; }
.badge-cancel { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>

@php
    $devise   = $shop->currency ?? 'GNF';
    $total    = 0;
    foreach ($orders as $o) { $total += (float)($o->total ?? 0); }
    $livrees  = $orders->filter(fn($o) => $o->status === 'livrée')->count();
    $annulees = $orders->filter(fn($o) => $o->status === 'annulée')->count();
    $enCours  = $orders->filter(fn($o) => in_array($o->status, ['confirmée','en_livraison']))->count();
    $enAttente = $orders->filter(fn($o) => $o->status === 'en_attente')->count();

    $statusMap = [
        'en_attente'   => ['En attente',   's-wait',   'badge-wait'],
        'confirmée'    => ['Confirmée',    's-conf',   'badge-conf'],
        'en_livraison' => ['En livraison', 's-deliv',  'badge-deliv'],
        'livrée'       => ['Livrée',       's-done',   'badge-done'],
        'annulée'      => ['Annulée',      's-cancel', 'badge-cancel'],
    ];
@endphp

{{-- EN-TÊTE --}}
<div class="header">
    <table class="header-inner">
        <tr>
            <td style="width:70%">
                <div class="header-title">
                    Rapport des commandes &mdash; {{ $shop->name }}
                </div>
                <div class="header-sub">
                    Export&eacute; le {{ now()->format('d/m/Y') }} &agrave; {{ now()->format('H:i') }}
                    @if(!empty($filters['date_from']) || !empty($filters['date_to']))
                        &nbsp;&bull;&nbsp; P&eacute;riode&nbsp;:
                        {{ $filters['date_from'] ?? '...' }} &rarr; {{ $filters['date_to'] ?? '...' }}
                    @endif
                    @if(!empty($filters['status']))
                        &nbsp;&bull;&nbsp; Statut&nbsp;: {{ $filters['status'] }}
                    @endif
                </div>
            </td>
            <td class="header-right">
                <div class="header-date">{{ now()->format('d/m/Y') }}</div>
                <div style="font-size:8.5px;margin-top:2px;color:#64748b">{{ $shop->name }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- STATS --}}
<table class="stats-table">
    <tr>
        <td>
            <div class="stats-val">{{ $orders->count() }}</div>
            <div class="stats-lbl">Commandes</div>
        </td>
        <td>
            <div class="stats-val">{{ number_format($total, 0, ',', ' ') }}</div>
            <div class="stats-lbl">Revenu ({{ $devise }})</div>
        </td>
        <td>
            <div class="stats-val" style="color:#166534">{{ $livrees }}</div>
            <div class="stats-lbl">Livr&eacute;es</div>
        </td>
        <td>
            <div class="stats-val" style="color:#3730a3">{{ $enCours }}</div>
            <div class="stats-lbl">En cours</div>
        </td>
        <td>
            <div class="stats-val" style="color:#991b1b">{{ $annulees }}</div>
            <div class="stats-lbl">Annul&eacute;es</div>
        </td>
    </tr>
</table>

{{-- TABLEAU --}}
<table class="orders-table">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:14%">Client</th>
            <th style="width:10%">T&eacute;l.</th>
            <th style="width:25%">Articles command&eacute;s</th>
            <th style="width:14%">Destination</th>
            <th style="width:11%">Livreur</th>
            <th style="width:10%">Total</th>
            <th style="width:8%">Statut</th>
            <th style="width:7%">Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $i => $o)
        @php
            $clientName = $o->client?->name ?? '&mdash;';
            $phone      = $o->client_phone ?? '&mdash;';
            $dest       = $o->delivery_destination
                ? mb_substr($o->delivery_destination, 0, 40)
                : '&mdash;';
            $st         = $statusMap[$o->status] ?? [ucfirst($o->status ?? ''), 's-wait', 'badge-wait'];
            $rowClass   = ($i % 2 === 1) ? 'even' : '';
        @endphp
        <tr class="{{ $rowClass }}">
            <td><span class="strong">#{{ str_pad($o->id, 4, '0', STR_PAD_LEFT) }}</span></td>
            <td><span class="strong">{{ $clientName }}</span></td>
            <td class="muted">{{ $phone }}</td>
            <td>
                @if($o->items && $o->items->isNotEmpty())
                    <div class="items">
                        @foreach($o->items as $item)
                            <span class="item-line">
                                &bull; {{ $item->product?->name ?? 'Produit #'.$item->product_id }}
                                &times;{{ $item->quantity ?? 1 }}
                                &mdash; {{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 0, ',', ' ') }} {{ $devise }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <span class="muted">&mdash;</span>
                @endif
            </td>
            <td class="muted">{{ $dest }}</td>
            <td>{{ $o->livreur?->name ?? '&mdash;' }}</td>
            <td>
                <span class="strong">{{ number_format((float)($o->total ?? 0), 0, ',', ' ') }}</span>
                <span class="muted">{{ $devise }}</span>
            </td>
            <td>
                <span class="badge {{ $st[2] }}">{{ $st[0] }}</span>
            </td>
            <td>
                {{ $o->created_at ? $o->created_at->format('d/m/Y') : '&mdash;' }}<br>
                <span class="muted">{{ $o->created_at ? $o->created_at->format('H:i') : '' }}</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" style="text-align:center; padding:24px; color:#94a3b8; font-size:11px;">
                Aucune commande pour cette p&eacute;riode
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($orders->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="6" style="text-align:right; padding-right:10px;">
                Total g&eacute;n&eacute;ral ({{ $orders->count() }} commande{{ $orders->count() > 1 ? 's' : '' }}) :
            </td>
            <td colspan="3">
                <span class="strong">{{ number_format($total, 0, ',', ' ') }} {{ $devise }}</span>
            </td>
        </tr>
    </tfoot>
    @endif
</table>

{{-- PIED DE PAGE --}}
<div class="footer">
    <table class="footer-inner">
        <tr>
            <td>
                Shopio &bull; {{ $shop->name }} &bull; {{ $orders->count() }} commande(s)
                &bull; G&eacute;n&eacute;r&eacute; le {{ now()->format('d/m/Y') }} &agrave; {{ now()->format('H:i') }}
            </td>
            <td class="footer-right">
                Page <span class="page-num"></span>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
