<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stats</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px;}
        table { width:60%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #ccc; padding:6px; text-align:left; }
        th { background:#f5f5f5; width:40%; }
    </style>
</head>
<body>
    <h2>Rapport statistique</h2>

    <table>
        <tr><th>Période</th><td>{{ $stats['period'] }}</td></tr>
        <tr><th>Nombre de commandes</th><td>{{ $stats['total_orders'] }}</td></tr>
        <tr><th>Chiffre d'affaires</th><td>{{ number_format($stats['total_revenue'],2,',',' ') }}</td></tr>
        <tr><th>Montant paiements</th><td>{{ number_format($stats['total_payments'],2,',',' ') }}</td></tr>
        <tr><th>Moyenne par commande</th><td>{{ number_format($stats['avg_order'],2,',',' ') }}</td></tr>
    </table>
</body>
</html>
