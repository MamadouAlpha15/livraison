<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Commandes</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; background: #fff; }

/* En-tête */
.header { background-color: #1e293b; color: #ffffff; padding: 12px 16px; margin-bottom: 14px; }
.header h1 { font-size: 16px; font-weight: bold; color: #ffffff; }
.header p  { font-size: 10px; color: #94a3b8; margin-top: 4px; }

/* Résumé */
.stats-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
.stats-table td {
    width: 20%; border: 1px solid #e2e8f0;
    padding: 8px 10px; text-align: center;
    background-color: #f8fafc;
}
.stats-val { font-size: 16px; font-weight: bold; color: #1e293b; }
.stats-lbl { font-size: 9px; color: #64748b; margin-top: 2px; }

/* Tableau commandes */
.orders-table { width: 100%; border-collapse: collapse; font-size: 10px; }
.orders-table thead tr { background-color: #334155; }
.orders-table thead th {
    color: #ffffff; padding: 7px 8px;
    text-align: left; font-size: 9px;
    font-weight: bold; border: 1px solid #475569;
}
.orders-table tbody tr { border-bottom: 1px solid #e2e8f0; }
.orders-table tbody tr.even { background-color: #f8fafc; }
.orders-table tbody td { padding: 7px 8px; vertical-align: top; border: 1px solid #e2e8f0; }
.orders-table tfoot td {
    padding: 7px 8px; border: 1px solid #334155;
    background-color: #f1f5f9;
    font-weight: bold; font-size: 11px;
}

/* Statuts */
.s-wait   { color: #92400e; font-weight: bold; }
.s-conf   { color: #1e40af; font-weight: bold; }
.s-deliv  { color: #3730a3; font-weight: bold; }
.s-done   { color: #166534; font-weight: bold; }
.s-cancel { color: #991b1b; font-weight: bold; }

/* Articles */
.items { font-size: 9px; color: #475569; margin-top: 3px; }

/* Pied de page */
.footer { margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 9px; color: #94a3b8; text-align: center; }

.muted { color: #94a3b8; font-size: 9px; }
</style>
</head>
<body>

@php
    $devise    = $shop->currency ?? 'GNF';
    $total     = 0;
    foreach ($orders as $o) { $total += (float)($o->total ?? 0); }
    $livrees   = $orders->filter(fn($o) => $o->status === 'livrée')->count();
    $annulees  = $orders->filter(fn($o) => $o->status === 'annulée')->count();
    $enCours   = $orders->filter(fn($o) => in_array($o->status, ['confirmée','en_livraison']))->count();

    $statusMap = [
        'en_attente'   => ['En attente',   's-wait'],
        'confirmée'    => ['Confirmée',    's-conf'],
        'en_livraison' => ['En livraison', 's-deliv'],
        'livrée'       => ['Livrée',       's-done'],
        'annulée'      => ['Annulée',      's-cancel'],
    ];
@endphp

{{-- EN-TÊTE --}}
<div class="header">
    <h1>Rapport des commandes &mdash; {{ $shop->name }}</h1>
    <p>
        Export&eacute; le {{ now()->format('d/m/Y H:i') }}
        @if(!empty($filters['date_from']) || !empty($filters['date_to']))
            &nbsp;&bull;&nbsp; P&eacute;riode : {{ $filters['date_from'] ?? '...' }} &rarr; {{ $filters['date_to'] ?? '...' }}
        @endif
        @if(!empty($filters['status'])) &nbsp;&bull;&nbsp; Statut : {{ $filters['status'] }} @endif
    </p>
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
            <th style="width:6%">#</th>
            <th style="width:16%">Client</th>
            <th style="width:24%">Articles command&eacute;s</th>
            <th style="width:14%">Destination</th>
            <th style="width:12%">Livreur</th>
            <th style="width:11%">Total</th>
            <th style="width:10%">Statut</th>
            <th style="width:7%">Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $i => $o)
        @php
            $clientName = $o->client?->name ?? $o->client_phone ?? '&mdash;';
            $dest       = $o->delivery_destination ? mb_substr($o->delivery_destination, 0, 35) : '&mdash;';
            $st         = $statusMap[$o->status] ?? [ucfirst($o->status ?? ''), 's-wait'];
            $rowClass   = ($i % 2 === 1) ? 'even' : '';
        @endphp
        <tr class="{{ $rowClass }}">
            <td><strong>#{{ str_pad($o->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
            <td>
                <strong>{{ $clientName }}</strong>
                @if($o->client_phone && $o->client)
                    <br><span class="muted">{{ $o->client_phone }}</span>
                @endif
            </td>
            <td>
                @if($o->items && $o->items->isNotEmpty())
                    <div class="items">
                        @foreach($o->items as $item)
                            &bull; {{ $item->product?->name ?? 'Produit #'.$item->product_id }}
                            &times; {{ $item->quantity ?? 1 }}
                            ({{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 0, ',', ' ') }} {{ $devise }})<br>
                        @endforeach
                    </div>
                @else
                    <span class="muted">&mdash;</span>
                @endif
            </td>
            <td class="muted">{{ $dest }}</td>
            <td>{{ $o->livreur?->name ?? '&mdash;' }}</td>
            <td><strong>{{ number_format((float)($o->total ?? 0), 0, ',', ' ') }}</strong> <span class="muted">{{ $devise }}</span></td>
            <td><span class="{{ $st[1] }}">{{ $st[0] }}</span></td>
            <td>
                {{ $o->created_at ? $o->created_at->format('d/m/Y') : '&mdash;' }}<br>
                <span class="muted">{{ $o->created_at ? $o->created_at->format('H:i') : '' }}</span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" style="text-align:center; padding:24px; color:#94a3b8;">
                Aucune commande pour cette p&eacute;riode
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($orders->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="5" style="text-align:right; padding-right:12px;">Total g&eacute;n&eacute;ral :</td>
            <td colspan="3"><strong>{{ number_format($total, 0, ',', ' ') }} {{ $devise }}</strong></td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="footer">
    Shopio &bull; {{ $shop->name }} &bull; {{ $orders->count() }} commande(s) &bull; G&eacute;n&eacute;r&eacute; le {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
