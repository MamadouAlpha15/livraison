@extends('layouts.app')
@section('title', 'Mes livraisons')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
/* Mes livraisons — même univers que le tableau de bord livreur : en-tête bleu nuit, cartes blanches, actions en pilule */
.lo {
    --blue: #1F7CF6; --blue-2: #1668D8; --blue-3: #1260C9; --blue-lt: #EAF2FF; --blue-bd: #CFE1FF;
    --navy: #0B1E3D; --navy-2: #132B52; --amber: #F59E0B; --amber-lt: #FFF7E6;
    --green: #059669; --green-lt: #ECFDF5; --red: #B91C1C; --red-lt: #FEF2F2;
    --ink: #16162A; --ink-2: #45455E; --ink-3: #62627C; --line: #E3E8F2; --bg: #F6F9FF;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    color: var(--ink); background: var(--bg); min-height: 100vh; padding-bottom: 48px;
}
.lo *, .lo *::before, .lo *::after { box-sizing: border-box; }
.lo-num { font-variant-numeric: tabular-nums; }

/* ── En-tête ── */
.lo-head {
    background: radial-gradient(110% 170% at 100% 0%, rgba(31,124,246,.45), transparent 60%), linear-gradient(135deg, var(--navy), var(--navy-2));
    color: #fff;
    padding: 20px max(16px, env(safe-area-inset-right, 0px)) 22px max(16px, env(safe-area-inset-left, 0px));
}
.lo-head-in { max-width: 860px; margin: 0 auto; display: grid; gap: 18px; }
.lo-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.lo-back { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 16px 0 12px; border-radius: 999px; background: rgba(255,255,255,.1); color: #fff; font-size: 13.5px; font-weight: 700; text-decoration: none; transition: background-color .18s; }
.lo-back:hover { background: rgba(255,255,255,.18); color: #fff; }
.lo-total { font-size: 13px; font-weight: 600; color: #C9D6EA; }
.lo-title h1 { margin: 0; font-size: clamp(22px, 4vw, 28px); font-weight: 800; letter-spacing: -.02em; }
.lo-title p { margin: 4px 0 0; font-size: 14px; color: #C9D6EA; }

/* ── Onglets ── */
.lo-tabs { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; margin: 0 -2px; padding: 2px; }
.lo-tabs::-webkit-scrollbar { display: none; }
.lo-tab { display: inline-flex; align-items: center; gap: 7px; min-height: 40px; padding: 0 16px; border-radius: 999px; white-space: nowrap; background: rgba(255,255,255,.1); color: #fff; font-size: 13.5px; font-weight: 700; text-decoration: none; transition: background-color .18s, color .18s; }
.lo-tab:hover { background: rgba(255,255,255,.18); color: #fff; }
.lo-tab.is-active { background: #fff; color: var(--navy); }
.lo-tab-n { min-width: 22px; height: 22px; padding: 0 7px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; background: rgba(255,255,255,.16); }
.lo-tab.is-active .lo-tab-n { background: var(--blue-lt); color: var(--blue-3); }

/* ── Liste ── */
.lo-main { max-width: 860px; margin: 18px auto 0; padding: 0 16px; display: grid; gap: 14px; }
.lo-flash { display: flex; align-items: center; gap: 10px; padding: 13px 16px; border-radius: 16px; background: var(--green-lt); color: #065F46; font-size: 14px; font-weight: 700; box-shadow: inset 0 0 0 1px #A7F3D0; }

.lo-card { background: #fff; border-radius: 22px; box-shadow: 0 0 0 1px var(--line), 0 14px 30px -26px rgba(11,30,61,.45); overflow: hidden; }
.lo-card-hd { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--line); }
.lo-card-num { font-size: 15px; font-weight: 800; }
.lo-card-sub { font-size: 12.5px; color: var(--ink-3); margin-top: 2px; }
.lo-st { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 800; white-space: nowrap; flex-shrink: 0; }
.lo-st.s-ready { background: var(--amber-lt); color: #92400E; }
.lo-st.s-route { background: var(--blue-lt); color: var(--blue-3); }
.lo-st.s-done  { background: var(--green-lt); color: #065F46; }
.lo-st.s-cancel{ background: var(--red-lt); color: var(--red); }
.lo-st.s-other { background: #F1F3F8; color: var(--ink-3); }

.lo-card-bd { padding: 18px 20px; display: grid; gap: 16px; }

/* Trajet : retrait puis arrêt(s) client */
.lo-route { display: grid; }
.lo-stop { display: grid; grid-template-columns: 36px 1fr; gap: 12px; align-items: start; }
.lo-stop-ico { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.lo-stop.pickup .lo-stop-ico { background: var(--amber-lt); color: #B45309; }
.lo-stop.drop .lo-stop-ico   { background: var(--blue-lt); color: var(--blue-2); }
.lo-stop-line { width: 2px; height: 16px; margin: 4px 0 4px 17px; background: var(--line); }
.lo-stop-lbl { font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--ink-3); }
.lo-stop-name { font-size: 15px; font-weight: 800; margin-top: 2px; overflow-wrap: anywhere; }
.lo-stop-addr { display: flex; align-items: flex-start; gap: 5px; font-size: 13.5px; color: var(--ink-2); margin-top: 3px; overflow-wrap: anywhere; }
.lo-stop-addr svg { margin-top: 2px; }
.lo-stop-addr.is-missing { font-style: italic; color: var(--ink-3); }
.lo-slot { display: inline-flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 12.5px; font-weight: 700; color: var(--blue-3); }
.lo-contact { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.lo-pill { display: inline-flex; align-items: center; gap: 6px; min-height: 36px; padding: 0 14px; border-radius: 999px; font-size: 13px; font-weight: 700; text-decoration: none; transition: background-color .18s, color .18s; }
.lo-pill.call { background: var(--blue-lt); color: var(--blue-3); }
.lo-pill.call:hover { background: var(--blue-bd); color: var(--blue-3); }
.lo-pill.wa { background: #E8F8EE; color: #128C4A; }
.lo-pill.wa:hover { background: #25D366; color: #fff; }

/* Articles et montants */
.lo-items { display: flex; align-items: center; gap: 12px; }
.lo-thumb { width: 52px; height: 52px; border-radius: 14px; object-fit: cover; flex-shrink: 0; background: var(--bg); box-shadow: inset 0 0 0 1px var(--line); }
.lo-thumb-ph { width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--bg); color: var(--ink-3); box-shadow: inset 0 0 0 1px var(--line); }
.lo-items-txt { min-width: 0; font-size: 14px; font-weight: 700; overflow-wrap: anywhere; }
.lo-items-txt small { display: block; font-size: 12.5px; font-weight: 600; color: var(--ink-3); margin-top: 2px; }
.lo-facts { display: flex; flex-wrap: wrap; gap: 8px; }
.lo-fact { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 12px; background: var(--bg); font-size: 13px; font-weight: 600; color: var(--ink-2); }
.lo-fact strong { color: var(--ink); }
.lo-fact.cash { background: var(--amber-lt); color: #92400E; }
.lo-fact.cash strong { color: #78350F; }
.lo-fact.paid { background: var(--green-lt); color: #065F46; }
.lo-fact.earn { background: var(--navy); color: #C9D6EA; }
.lo-fact.earn strong { color: #fff; }

/* Actions */
.lo-card-ft { padding: 0 20px 20px; display: flex; gap: 8px; }
.lo-card-ft form { flex: 1; display: flex; }
.lo-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 50px; padding: 0 22px; border-radius: 999px; border: none; cursor: pointer; font-family: inherit; font-size: 15px; font-weight: 800; text-decoration: none; transition: background-color .18s, transform .18s; }
.lo-btn:active { transform: scale(.98); }
.lo-btn-primary { background: var(--blue-2); color: #fff; }
.lo-btn-primary:hover { background: var(--blue-3); color: #fff; }
.lo-btn-go { background: var(--navy); color: #fff; }
.lo-btn-go:hover { background: var(--navy-2); color: #fff; }

/* Vide */
.lo-empty { background: #fff; border-radius: 22px; box-shadow: 0 0 0 1px var(--line); padding: 40px 20px; text-align: center; display: grid; gap: 8px; justify-items: center; }
.lo-empty-ico { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: var(--blue-lt); color: var(--blue-2); }
.lo-empty-title { font-size: 16px; font-weight: 800; }
.lo-empty-sub { font-size: 14px; color: var(--ink-2); max-width: 42ch; }
.lo-pagination { margin-top: 4px; }

@media (max-width: 520px) {
    .lo-card-hd, .lo-card-bd { padding-left: 16px; padding-right: 16px; }
    .lo-card-ft { padding: 0 16px 16px; }
    .lo-back span { display: none; }
    .lo-back { padding: 0 12px; }
}
</style>
@endpush

@section('content')
@php
    use App\Support\IconLibrary as Ico;
    use App\Models\Payment;

    // Espaces insécables : un montant ne se coupe jamais en fin de ligne (« 5 000 GNF »)
    $fmt = fn ($n) => number_format($n ?? 0, 0, ',', "\u{00A0}") . "\u{00A0}" . $devise;

    $startStatuses   = ['confirmed', 'confirmée', 'confirmé'];
    $deliverStatuses = ['delivering', 'en_livraison', 'en-livraison', 'shipped'];
    $doneStatuses    = ['delivered', 'livrée', 'livree', 'completed'];

    // Statut → libellé, classe et icône
    $statusInfo = fn (string $s) => match (true) {
        in_array($s, $startStatuses, true)    => ['À récupérer', 's-ready', 'package'],
        in_array($s, $deliverStatuses, true)  => ['En route', 's-route', 'truck'],
        in_array($s, $doneStatuses, true)     => ['Livrée', 's-done', 'check'],
        in_array($s, ['cancelled', 'annulée'], true) => ['Annulée', 's-cancel', 'x'],
        default                               => [ucfirst($s), 's-other', 'clock'],
    };

    // Payée en ligne : rien à encaisser chez le client
    $isPaid = fn ($o) => $o->payment
        && $o->payment->method !== Payment::METHOD_CASH
        && $o->payment->status === Payment::STATUS_PAYE;

    $tab = request('status');
    $tabs = [
        [null,         'En cours',     ($counts[\App\Models\Order::STATUS_CONFIRMEE] ?? 0) + ($counts[\App\Models\Order::STATUS_EN_LIVRAISON] ?? 0)],
        ['confirmed',  'À récupérer',  $counts[\App\Models\Order::STATUS_CONFIRMEE] ?? 0],
        ['delivering', 'En route',     $counts[\App\Models\Order::STATUS_EN_LIVRAISON] ?? 0],
        ['delivered',  'Historique',   null],
    ];

    $waIco = '<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
@endphp

<div class="lo">
    {{-- ═══ En-tête ═══ --}}
    <header class="lo-head">
        <div class="lo-head-in">
            <div class="lo-top">
                <a href="{{ route('livreur.dashboard') }}" class="lo-back">{!! Ico::svg('arrow-left', '', 16) !!} <span>Tableau de bord</span></a>
                <div class="lo-total lo-num">{{ $orders->total() }} commande{{ $orders->total() > 1 ? 's' : '' }}</div>
            </div>
            <div class="lo-title">
                <h1>Mes livraisons</h1>
                <p>Les commandes qui vous sont confiées, de la boutique jusqu'au client.</p>
            </div>
            <nav class="lo-tabs" aria-label="Filtrer les livraisons">
                @foreach($tabs as [$key, $label, $n])
                <a href="{{ route('livreur.orders.index', $key ? ['status' => $key] : []) }}"
                   class="lo-tab {{ $tab === $key ? 'is-active' : '' }}" @if($tab === $key) aria-current="page" @endif>
                    {{ $label }}
                    @if($n !== null)<span class="lo-tab-n lo-num">{{ $n }}</span>@endif
                </a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="lo-main">
        @if(session('success'))
        <div class="lo-flash" role="status">{!! Ico::svg('check', '', 18) !!} {{ session('success') }}</div>
        @endif

        @forelse($groups as $group)
        @php
            $grpOrders = $group['orders'];          // tableau d'Order (1 ou plusieurs)
            $order     = $grpOrders[0];             // commande représentative (id, navigation…)
            $shop      = $group['shop'];
            $s         = strtolower($group['status'] ?? '');
            [$stLabel, $stCls, $stIco] = $statusInfo($s);
            $isCompany = (bool) $order->driver_id;  // chauffeur d'une société de livraison
            $isBulk    = count($grpOrders) > 1;
            $orderIds  = collect($grpOrders)->pluck('id')->toArray();
            $orderNums = collect($grpOrders)->map(fn ($o) => '#' . str_pad($o->id, 5, '0', STR_PAD_LEFT))->join(', ');
            $allItems  = collect($grpOrders)->flatMap(fn ($o) => $o->items)->values();
            $items     = $allItems->unique(fn ($i) => $i->product_id)->filter(fn ($i) => $i->product?->name);
            $firstItem = $allItems->first();
            $isActive  = in_array($s, $startStatuses, true) || in_array($s, $deliverStatuses, true);
            $toCollect = collect($grpOrders)->reject($isPaid)->sum('total');
            $shopWa    = preg_replace('/\D/', '', $shop?->phone ?? '');

            // Arrêts de livraison : même client + même adresse = un seul arrêt
            $stops = collect($grpOrders)->unique(function ($o) {
                $cl = strtolower(trim($o->client?->name ?? $o->client_name ?? ''));
                $ds = strtolower(trim($o->delivery_destination ?? $o->client?->address ?? ''));
                return $cl . '::' . $ds;
            })->values();
            $multiStop = $stops->count() > 1;
            // Le retrait en boutique n'apparaît que pour une société de livraison ou un trajet groupé ;
            // le livreur d'une boutique part déjà de la boutique.
            $showPickup = $isCompany || $isBulk;
        @endphp

        <article class="lo-card" data-order-id="{{ $order->id }}">
            <div class="lo-card-hd">
                <div style="min-width:0">
                    @if($isBulk)
                    <div class="lo-card-num">{{ count($grpOrders) }} commandes · 1 trajet</div>
                    <div class="lo-card-sub lo-num">{{ $orderNums }}</div>
                    @else
                    <div class="lo-card-num lo-num">Commande #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                    <div class="lo-card-sub">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                    @endif
                </div>
                <span class="lo-st {{ $stCls }}">{!! Ico::svg($stIco, '', 14) !!} {{ $stLabel }}</span>
            </div>

            <div class="lo-card-bd">
                {{-- Trajet --}}
                <div class="lo-route">
                    @if($showPickup)
                    <div class="lo-stop pickup">
                        <span class="lo-stop-ico">{!! Ico::svg('store', '', 17) !!}</span>
                        <div style="min-width:0">
                            <div class="lo-stop-lbl">Retrait</div>
                            <div class="lo-stop-name">{{ $shop?->name ?? 'Boutique' }}</div>
                            @if($shop?->address)
                            <div class="lo-stop-addr">{!! Ico::svg('map-pin', '', 14) !!} {{ $shop->address }}</div>
                            @else
                            <div class="lo-stop-addr is-missing">Adresse non renseignée</div>
                            @endif
                            @if($shop?->phone || $shopWa)
                            <div class="lo-contact">
                                @if($shop?->phone)<a href="tel:{{ $shop->phone }}" class="lo-pill call">{!! Ico::svg('phone', '', 15) !!} {{ $shop->phone }}</a>@endif
                                @if($shopWa)<a href="https://wa.me/{{ $shopWa }}" target="_blank" rel="noopener" class="lo-pill wa">{!! $waIco !!} WhatsApp</a>@endif
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="lo-stop-line" aria-hidden="true"></div>
                    @endif

                    @foreach($stops as $stop)
                    @php
                        $stClient = $stop->client ?? $stop->user;
                        $stName   = $stClient?->name ?? $stop->client_name ?? 'Client';
                        $stDest   = $stop->delivery_destination ?? $stClient?->address;
                        $stPhone  = $stop->client_phone ?: $stClient?->phone;
                        $stWa     = preg_replace('/\D/', '', $stPhone ?? '');
                    @endphp
                    @if(!$loop->first)<div class="lo-stop-line" aria-hidden="true"></div>@endif
                    <div class="lo-stop drop">
                        <span class="lo-stop-ico">{!! Ico::svg('map-pin', '', 17) !!}</span>
                        <div style="min-width:0">
                            <div class="lo-stop-lbl">{{ $multiStop ? 'Arrêt ' . ($loop->index + 1) . ' · Livraison' : 'Livraison' }}</div>
                            <div class="lo-stop-name">{{ $stName }}</div>
                            @if($stDest)
                            <div class="lo-stop-addr">{!! Ico::svg('map-pin', '', 14) !!} {{ $stDest }}</div>
                            @else
                            <div class="lo-stop-addr is-missing">Adresse de livraison non renseignée</div>
                            @endif
                            @if($stop->delivery_slot)
                            <div class="lo-slot">{!! Ico::svg('clock', '', 14) !!} Créneau : {{ $stop->delivery_slot }}</div>
                            @endif
                            @if($stPhone)
                            <div class="lo-contact">
                                <a href="tel:{{ $stPhone }}" class="lo-pill call">{!! Ico::svg('phone', '', 15) !!} {{ $stPhone }}</a>
                                @if($stWa)<a href="https://wa.me/{{ $stWa }}" target="_blank" rel="noopener" class="lo-pill wa">{!! $waIco !!} WhatsApp</a>@endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Articles --}}
                @if($items->isNotEmpty())
                <div class="lo-items">
                    @if($firstItem?->product?->image)
                    <img src="{{ \App\Services\ImageOptimizer::url($firstItem->product->image, 'thumb') ?? asset('storage/' . $firstItem->product->image) }}"
                         alt="" class="lo-thumb" loading="lazy">
                    @else
                    <span class="lo-thumb-ph">{!! Ico::svg('package', '', 22) !!}</span>
                    @endif
                    <div class="lo-items-txt">
                        {{ $items->map(fn ($i) => $i->product->name . ($i->quantity > 1 ? ' ×' . $i->quantity : ''))->join(', ') }}
                        <small>{{ $allItems->sum('quantity') }} article{{ $allItems->sum('quantity') > 1 ? 's' : '' }}</small>
                    </div>
                </div>
                @endif

                {{-- Montants --}}
                <div class="lo-facts">
                    @if($isActive && $toCollect > 0)
                    <span class="lo-fact cash">{!! Ico::svg('wallet', '', 14) !!} À encaisser <strong class="lo-num">{{ $fmt($toCollect) }}</strong></span>
                    @elseif($isActive)
                    <span class="lo-fact paid">{!! Ico::svg('check', '', 14) !!} Déjà payée en ligne</span>
                    @else
                    <span class="lo-fact">{!! Ico::svg('wallet', '', 14) !!} Montant <strong class="lo-num">{{ $fmt($group['total']) }}</strong></span>
                    @endif
                    @if($group['delivery_fee'])
                    <span class="lo-fact earn">{!! Ico::svg('trophy', '', 14) !!} Ma commission <strong class="lo-num">{{ $fmt($group['delivery_fee']) }}</strong>{{ $isBulk ? ' · 1 trajet' : '' }}</span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @if($isActive)
            <div class="lo-card-ft">
                @if($isCompany)
                    {{-- Société de livraison : tout se passe dans la navigation (retrait puis livraison) --}}
                    @if($isBulk)
                    <form action="{{ route('livreur.orders.autoBatchNav') }}" method="POST">
                        @csrf
                        @foreach($orderIds as $oid)<input type="hidden" name="order_ids[]" value="{{ $oid }}">@endforeach
                        <button type="submit" class="lo-btn lo-btn-primary">{!! Ico::svg('navigation', '', 18) !!} Commencer ({{ count($grpOrders) }} colis)</button>
                    </form>
                    @else
                    <a href="{{ route('orders.nav', $order) }}" class="lo-btn lo-btn-primary">{!! Ico::svg('navigation', '', 18) !!} Commencer</a>
                    @endif
                @elseif(in_array($s, $startStatuses, true))
                    {{-- Livreur de la boutique : déjà sur place, il part directement chez le client --}}
                    <form action="{{ route('livreur.orders.startBulkNav') }}" method="POST">
                        @csrf
                        @foreach($orderIds as $oid)<input type="hidden" name="order_ids[]" value="{{ $oid }}">@endforeach
                        <button type="submit" class="lo-btn lo-btn-primary">{!! Ico::svg('truck', '', 18) !!} Commencer la livraison{{ $isBulk ? ' (' . count($grpOrders) . ' colis)' : '' }}</button>
                    </form>
                @else
                    <a href="{{ route('orders.nav', $order) }}" class="lo-btn lo-btn-go">{!! Ico::svg('navigation', '', 18) !!} Naviguer chez le client{{ $isBulk ? ' (' . count($grpOrders) . ')' : '' }}</a>
                @endif
            </div>
            @endif
        </article>
        @empty
        <div class="lo-empty">
            <span class="lo-empty-ico">{!! Ico::svg('package', '', 28) !!}</span>
            @if($tab === 'delivered')
            <div class="lo-empty-title">Pas encore de livraison terminée</div>
            <div class="lo-empty-sub">Vos commandes livrées et annulées apparaîtront ici.</div>
            @else
            <div class="lo-empty-title">Aucune commande pour le moment</div>
            <div class="lo-empty-sub">Les commandes qu'on vous confie apparaîtront ici. Restez « disponible » sur votre tableau de bord pour en recevoir.</div>
            @endif
        </div>
        @endforelse

        @if($orders->hasPages())
        <div class="lo-pagination">{{ $orders->links() }}</div>
        @endif
    </main>
</div>
@endsection
