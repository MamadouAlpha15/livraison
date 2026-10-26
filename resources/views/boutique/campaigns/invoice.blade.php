{{--
    Facture d'une campagne, rendue en PDF par Barryvdh\DomPDF (CSS simple, pas de JS).
    Variables : $campaign (shop.user, package, items.product), $number, $lines, $payment, $refunded
--}}
@php
    $fmt = fn (int $v) => number_format($v, 0, ',', ' ') . ' GNF';
    $methods = ['mobile_money' => 'Mobile Money', 'credit' => 'Crédit Shopio', 'manuel' => 'Paiement manuel'];
    $operators = ['orange_money' => 'Orange Money', 'mtn_momo' => 'MTN Mobile Money'];
    $shop = $campaign->shop;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Facture {{ $number }}</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', 'Helvetica', sans-serif; font-size: 11.5px; color: #16162A; margin: 0; }
    .page { padding: 32px 38px; }
    .head { width: 100%; border-collapse: collapse; margin-bottom: 26px; }
    .head td { vertical-align: top; }
    .brand { font-size: 24px; font-weight: bold; color: #1668D8; }
    .brand-sub { font-size: 10px; color: #62627C; margin-top: 3px; }
    .title { font-size: 18px; font-weight: bold; text-align: right; }
    .meta { font-size: 10.5px; color: #45455E; text-align: right; margin-top: 4px; line-height: 1.5; }
    .boxes { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 24px; }
    .box { width: 50%; background: #F6F9FF; border: 1px solid #E3E8F2; padding: 12px 14px; vertical-align: top; }
    .lbl { font-size: 9px; font-weight: bold; color: #62627C; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
    .name { font-size: 13px; font-weight: bold; margin-bottom: 3px; }
    .line { font-size: 10.5px; color: #45455E; margin-top: 2px; }
    table.items { width: 100%; border-collapse: collapse; }
    table.items th { background: #0B1E3D; color: #fff; font-size: 9.5px; text-transform: uppercase; letter-spacing: .4px; padding: 8px 10px; text-align: left; }
    table.items td { padding: 10px; border-bottom: 1px solid #E3E8F2; vertical-align: top; }
    .num { text-align: right; white-space: nowrap; }
    .detail { font-size: 10px; color: #62627C; margin-top: 3px; }
    .totals { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .totals td { padding: 5px 10px; }
    .totals .k { text-align: right; color: #45455E; }
    .totals .v { text-align: right; width: 150px; font-weight: bold; }
    .grand .k { font-size: 13px; color: #16162A; font-weight: bold; }
    .grand .v { font-size: 15px; color: #1668D8; }
    .paid { margin-top: 20px; background: #E7F6EC; border: 1px solid #BBE3C8; padding: 10px 14px; font-size: 11px; color: #15803D; }
    .refund { margin-top: 10px; background: #EAF2FF; border: 1px solid #D6E6FF; padding: 10px 14px; font-size: 11px; color: #1260C9; }
    .foot { margin-top: 36px; padding-top: 12px; border-top: 1px solid #E3E8F2; text-align: center; font-size: 9.5px; color: #62627C; }
</style>
</head>
<body>
<div class="page">
    <table class="head">
        <tr>
            <td style="width:55%">
                <div class="brand">Shopio</div>
                <div class="brand-sub">shopio-app.com · Campagnes publicitaires</div>
            </td>
            <td style="width:45%">
                <div class="title">FACTURE</div>
                <div class="meta">
                    N° {{ $number }}<br>
                    Date : {{ $campaign->paid_at->format('d/m/Y') }}<br>
                    Campagne n°{{ $campaign->id }}
                </div>
            </td>
        </tr>
    </table>

    <table class="boxes">
        <tr>
            <td class="box">
                <div class="lbl">Facturé à</div>
                <div class="name">{{ $shop?->name }}</div>
                @if($shop?->user)<div class="line">{{ $shop->user->name }}</div>@endif
                @if($shop?->address)<div class="line">{{ $shop->address }}</div>@endif
                @if($shop?->phone)<div class="line">{{ $shop->phone }}</div>@endif
                @if($shop?->email)<div class="line">{{ $shop->email }}</div>@endif
            </td>
            <td class="box" style="border-left:0">
                <div class="lbl">Campagne</div>
                <div class="name">{{ $campaign->isBoost() ? 'Mise en avant Shopio' : 'Campagne réseaux sociaux' }}</div>
                <div class="line">Formule {{ $campaign->package->name }} · {{ $campaign->duration_days }} jours</div>
                <div class="line">Du {{ $campaign->starts_on->format('d/m/Y') }} au {{ $campaign->ends_on->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead><tr><th>Désignation</th><th class="num">Montant</th></tr></thead>
        <tbody>
        @foreach($lines as $l)
            <tr>
                <td><strong>{{ $l['label'] }}</strong>@if($l['detail'])<div class="detail">{{ $l['detail'] }}</div>@endif</td>
                <td class="num">{{ $fmt($l['amount']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr class="grand"><td class="k">Total payé</td><td class="v">{{ $fmt($campaign->price_gnf) }}</td></tr>
    </table>

    @if($payment)
    <div class="paid">
        @php
            $paidBy = ($methods[$payment->method] ?? $payment->method)
                . ($payment->operator ? ' (' . ($operators[$payment->operator] ?? $payment->operator) . ')' : '')
                . ($payment->transaction_ref ? ' · Réf. ' . $payment->transaction_ref : '');
        @endphp
        Payé le {{ ($payment->paid_at ?? $campaign->paid_at)->format('d/m/Y à H:i') }} par {{ $paidBy }}.
    </div>
    @endif
    @if($refunded > 0)
    <div class="refund">
        Remboursé : {{ $fmt($refunded) }} sur le Crédit Shopio de la boutique ({{ mb_strtolower($campaign->statusLabel()) }}).
    </div>
    @endif

    <div class="foot">Shopio · Facture générée le {{ now()->format('d/m/Y') }} · Montants en francs guinéens (GNF).</div>
</div>
</body>
</html>
