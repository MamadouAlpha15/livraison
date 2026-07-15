{{--
    resources/views/client/orders/invoice.blade.php
    Rendu en PDF via Barryvdh\DomPDF — pas de JS, CSS simple (compatibilité dompdf)
    Variables : $order (avec items.product, items.variant, shop, payment, client)
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Reçu commande #{{ $order->id }}</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #0f172a; margin: 0; padding: 0; }
    .page { padding: 30px 36px; }

    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .header-table td { vertical-align: top; }
    .brand { font-size: 24px; font-weight: bold; color: #f97316; }
    .brand span { color: #0f172a; }
    .brand-tag { font-size: 10px; color: #64748b; margin-top: 2px; }
    .doc-title { font-size: 18px; font-weight: bold; text-align: right; color: #0f172a; }
    .doc-meta { font-size: 11px; color: #64748b; text-align: right; margin-top: 4px; }

    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; margin-top: 6px; }
    .status-livree { background: #dcfce7; color: #166534; }
    .status-attente { background: #fef3c7; color: #92400e; }
    .status-annulee { background: #fee2e2; color: #991b1b; }
    .status-autre { background: #e0e7ff; color: #3730a3; }

    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .info-box { width: 48%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; }
    .info-label { font-size: 9.5px; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
    .info-name { font-size: 13px; font-weight: bold; color: #0f172a; margin-bottom: 2px; }
    .info-line { font-size: 11px; color: #475569; margin-top: 2px; }

    table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.items th { background: #0f172a; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: .4px; padding: 8px 10px; text-align: left; }
    table.items th.num, table.items td.num { text-align: right; }
    table.items td { padding: 9px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11.5px; }
    table.items .variant { font-size: 10px; color: #6366f1; font-weight: bold; }

    .totals-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .totals-table td { padding: 5px 0; font-size: 12px; }
    .totals-table .lbl { color: #64748b; text-align: right; padding-right: 16px; }
    .totals-table .val { text-align: right; width: 130px; font-weight: bold; }
    .totals-table .grand-row .lbl { font-size: 14px; color: #0f172a; font-weight: bold; }
    .totals-table .grand-row .val { font-size: 16px; color: #f97316; }
    .totals-table .discount .val { color: #16a34a; }

    .payment-box { margin-top: 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px 14px; font-size: 11.5px; color: #166534; }

    .footer { margin-top: 36px; padding-top: 14px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 10px; color: #94a3b8; }
</style>
</head>
<body>
<div class="page">

    {{-- EN-TÊTE --}}
    <table class="header-table">
        <tr>
            <td style="width:55%">
                <div class="brand">Shop<span>io</span></div>
                <div class="brand-tag">Marketplace africaine — Crée. Vends. Encaisse.</div>
            </td>
            <td style="width:45%">
                <div class="doc-title">REÇU DE COMMANDE</div>
                <div class="doc-meta">
                    N° {{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}<br>
                    {{ $order->created_at->format('d/m/Y à H:i') }}
                </div>
                <div style="text-align:right">
                    @php
                        $statusCls = match($order->status) {
                            'livrée' => 'status-livree',
                            'en_attente' => 'status-attente',
                            'annulée' => 'status-annulee',
                            default => 'status-autre',
                        };
                        $statusLbl = match($order->status) {
                            'en_attente' => 'En attente',
                            'confirmée' => 'Confirmée',
                            'en_livraison' => 'En livraison',
                            'livrée' => 'Livrée',
                            'annulée' => 'Annulée',
                            default => ucfirst($order->status),
                        };
                    @endphp
                    <span class="status-badge {{ $statusCls }}">{{ $statusLbl }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- VENDEUR / CLIENT --}}
    <table class="info-table">
        <tr>
            <td style="width:50%;padding-right:8px">
                <div class="info-box">
                    <div class="info-label">Vendu par</div>
                    <div class="info-name">{{ $order->shop->name ?? 'Boutique' }}</div>
                    @if($order->shop->address)<div class="info-line">Adresse : {{ $order->shop->address }}</div>@endif
                    @if($order->shop->phone)<div class="info-line">Tél : {{ $order->shop->phone }}</div>@endif
                </div>
            </td>
            <td style="width:50%;padding-left:8px">
                <div class="info-box">
                    <div class="info-label">Client</div>
                    <div class="info-name">{{ $order->display_name }}</div>
                    @if($order->display_phone)<div class="info-line">Tél : {{ $order->display_phone }}</div>@endif
                    @if($order->delivery_destination)<div class="info-line">Adresse : {{ $order->delivery_destination }}</div>@endif
                </div>
            </td>
        </tr>
    </table>

    {{-- ARTICLES --}}
    <table class="items">
        <thead>
        <tr>
            <th>Produit</th>
            <th class="num">Qté</th>
            <th class="num">Prix unitaire</th>
            <th class="num">Sous-total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
        <tr>
            <td>
                {{ $item->product->name ?? 'Produit' }}
                @if($item->variant_name)<div class="variant">Option : {{ $item->variant_name }}</div>@endif
            </td>
            <td class="num">{{ $item->quantity }}</td>
            <td class="num">{{ number_format($item->price, 0, ',', ' ') }} {{ $order->shop->currency ?? 'GNF' }}</td>
            <td class="num">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} {{ $order->shop->currency ?? 'GNF' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>

    {{-- TOTAUX --}}
    @php
        $itemsTotal = $order->items->sum(fn($i) => $i->price * $i->quantity);
    @endphp
    <table class="totals-table">
        <tr>
            <td class="lbl">Sous-total articles</td>
            <td class="val">{{ number_format($itemsTotal, 0, ',', ' ') }} {{ $order->shop->currency ?? 'GNF' }}</td>
        </tr>
        @if($order->loyalty_points_used > 0)
        <tr class="discount">
            <td class="lbl">Réduction (points fidélité)</td>
            <td class="val">− {{ number_format($order->loyalty_points_used, 0, ',', ' ') }} {{ $order->shop->currency ?? 'GNF' }}</td>
        </tr>
        @endif
        <tr class="grand-row">
            <td class="lbl">TOTAL À PAYER</td>
            <td class="val">{{ number_format($order->total, 0, ',', ' ') }} {{ $order->shop->currency ?? 'GNF' }}</td>
        </tr>
    </table>

    <div class="payment-box">
        Paiement : <strong>{{ $order->payment?->method === 'cash' ? 'Cash à la livraison' : ($order->payment?->method ?? 'Cash à la livraison') }}</strong>
        — Statut : {{ $order->payment?->status === 'payé' ? 'Payé' : 'En attente de paiement' }}
    </div>

    <div class="footer">
        Merci d'avoir commandé sur Shopio — Document généré le {{ now()->format('d/m/Y à H:i') }}<br>
        Ce document tient lieu de reçu de commande, pas de facture fiscale officielle.
    </div>

</div>
</body>
</html>
