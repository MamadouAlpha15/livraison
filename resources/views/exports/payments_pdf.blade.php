<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiements - PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #444;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .small {
            font-size: 11px;
            color: #555;
        }
    </style>
</head>
<body>
    <h1>Paiements Boutique</h1>

    <p class="small">
        Période : {{ $filters['date_from'] ?? '—' }} — {{ $filters['date_to'] ?? '—' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>ID Paiement</th>
                <th>Commande</th>
                <th>Client</th>
                <th>Livreur</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>#{{ $payment->order->id ?? '—' }}</td>
                    <td>{{ $payment->order->client->name ?? '—' }}</td>
                    <td>{{ $payment->order->livreur->name ?? '—' }}</td>
                    <td>{{ number_format($payment->amount, 2, ',', ' ') }} {{ $payment->currency ?? 'GNF' }}</td>
                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($payment->status ?? '—') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">Aucun paiement trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="small">Total paiements : {{ number_format($payments->sum('amount'), 2, ',', ' ') }} GNF</p>
</body>
</html>
