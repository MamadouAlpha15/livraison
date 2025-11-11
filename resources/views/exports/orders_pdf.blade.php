<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export commandes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px;}
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ccc; padding:6px; text-align:left; }
        th { background:#f5f5f5; }
    </style>
</head>
<body>
    <h2>Export commandes</h2>
    <p>Période : {{ $filters['date_from'] ?? '—' }} — {{ $filters['date_to'] ?? '—' }}</p>

    <table>
    <thead>
        <tr>
            <th>#Commande</th>
            <th>Client</th>
            <th>Boutique</th>
            <th>Livreur</th>
            <th>Total</th>
            <th>Statut</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $o)
        <tr>
            <td>#{{ str_pad($o->id, 4, '0', STR_PAD_LEFT) }}</td>
            <td>{{ $o->client->name ?? '—' }}</td>
            <td>{{ $o->shop->name ?? '—' }}</td>
            <td>{{ $o->livreur->name ?? '—' }}</td>
            <td>{{ number_format($o->total, 0, ',', ' ') }} GNF</td>
            <td>{{ ucfirst($o->status) }}</td>
            <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
