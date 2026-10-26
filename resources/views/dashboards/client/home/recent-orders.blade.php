@if(isset($recentOrders) && $recentOrders->isNotEmpty())
<div>
    <div class="sec-hd">
        <div class="sec-title"><strong>Mes</strong> Commandes Récentes</div>
        <a href="{{ route('client.orders.index') }}" class="sec-link">Voir tout →</a>
    </div>
    <div class="orders-card" id="rtOrdersCard">
        <div class="orders-card-hd">
            <span class="orders-card-title">Historique</span>
            <span style="font-size:11px;color:var(--muted)" id="rtOrderCount">{{ $recentOrders->count() }} commande(s)</span>
        </div>
        <div id="rtOrdersList">
        @foreach($recentOrders as $order)
        @php
            $st   = $statusMap[$order->status] ?? ['pill-pending', ucfirst($order->status)];
            $oIcoSvg = match($order->status) {
                'livrée'                     => [$si('check',14),  'background:#d1fae5;color:#059669'],
                'en_livraison','en livraison' => [$si('truck',14),  'background:#dbeafe;color:#3b82f6'],
                'annulée','cancelled'          => [$si('x',14),     'background:#fee2e2;color:#e53e3e'],
                default                       => [$si('box',14),   'background:#fef3c7;color:#d97706'],
            };
        @endphp
        <a href="{{ route('client.orders.index') }}#order-{{ $order->id }}" class="order-row" data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}">
            <div class="order-ico" id="oIco{{ $order->id }}" style="{{ $oIcoSvg[1] }}">{!! $oIcoSvg[0] !!}</div>
            <div class="order-info">
                <div class="order-ref">#{{ $order->id }}</div>
                <div class="order-shop">{{ $order->shop?->name ?? 'Boutique' }}</div>
            </div>
            <span class="order-pill {{ $st[0] }}" id="oPill{{ $order->id }}">{{ $st[1] }}</span>
            <div class="order-amount">{{ number_format($order->total, 0, ',', ' ') }} <span style="font-size:10px;font-weight:400;color:var(--muted)">{{ $order->shop?->currency ?? 'GNF' }}</span></div>
        </a>
        @endforeach
        </div>
    </div>
</div>
@endif
