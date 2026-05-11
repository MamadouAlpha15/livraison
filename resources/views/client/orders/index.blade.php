@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp


@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --orange:    #f06a0f;
    --orange-dk: #d45a00;
    --orange-lt: #fff4ec;
    --orange-bd: #fcd9b6;
    --green:     #10b981;
    --green-lt:  #ecfdf5;
    --blue:      #3b82f6;
    --blue-lt:   #eff6ff;
    --yellow:    #f59e0b;
    --yellow-lt: #fffbeb;
    --red:       #ef4444;
    --red-lt:    #fef2f2;
    --text:      #0f172a;
    --text-2:    #475569;
    --muted:     #94a3b8;
    --border:    #e2e8f0;
    --surface:   #ffffff;
    --bg:        #f8f9fc;
    --font:      system-ui, -apple-system, 'Segoe UI', sans-serif;
    --mono:      'JetBrains Mono', 'Fira Code', monospace;
    --r:         14px;
    --r-sm:      9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
    --shadow:    0 4px 20px rgba(0,0,0,.08);
    --shadow-lg: 0 12px 40px rgba(0,0,0,.12);
}

html, body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    margin: 0;
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
}

/* ── Page wrapper ── */
.orders-page {
    max-width: 960px;
    margin: 0 auto;
    padding: 28px 16px 60px;
}

/* ── Top Bar ── */
.top-bar {
    background: linear-gradient(135deg, var(--orange) 0%, var(--orange-dk) 60%, #b84e00 100%);
    margin: -28px -16px 28px;
    padding: 0 20px;
    height: 62px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 4px 18px rgba(240,106,15,.35);
    position: relative;
    overflow: hidden;
}
.top-bar::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.top-bar::after {
    content: '';
    position: absolute;
    top: -30px; right: -30px;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,.07);
    pointer-events: none;
}
.btn-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 16px;
    background: rgba(255,255,255,.18);
    border: 1.5px solid rgba(255,255,255,.35);
    border-radius: 30px;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    font-family: var(--font);
    text-decoration: none;
    backdrop-filter: blur(4px);
    transition: all .18s;
    white-space: nowrap;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.btn-back:hover {
    background: rgba(255,255,255,.30);
    border-color: rgba(255,255,255,.6);
    color: #fff;
    transform: translateX(-2px);
    box-shadow: 0 3px 12px rgba(0,0,0,.15);
}
.btn-back svg {
    width: 15px; height: 15px;
    stroke: #fff;
    flex-shrink: 0;
    transition: transform .18s;
}
.btn-back:hover svg { transform: translateX(-2px); }
.top-bar-title {
    flex: 1;
    position: relative;
    z-index: 1;
}
.top-bar-title h1 {
    font-size: 17px;
    font-weight: 800;
    color: #fff;
    margin: 0;
    letter-spacing: -.3px;
    line-height: 1.2;
    text-shadow: 0 1px 3px rgba(0,0,0,.15);
}
.top-bar-title p {
    font-size: 11.5px;
    color: rgba(255,255,255,.78);
    margin: 2px 0 0;
    font-weight: 500;
}
.top-bar-ico {
    font-size: 28px;
    position: relative;
    z-index: 1;
    opacity: .9;
    flex-shrink: 0;
}

/* ── Stats ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: var(--shadow-sm);
    transition: transform .15s, box-shadow .15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
.stat-ico {
    width: 42px; height: 42px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 19px; flex-shrink: 0;
}
.stat-ico.orange { background: var(--orange-lt); }
.stat-ico.yellow { background: var(--yellow-lt); }
.stat-ico.blue   { background: var(--blue-lt); }
.stat-ico.green  { background: var(--green-lt); }
.stat-val {
    font-size: 22px; font-weight: 800; color: var(--text);
    letter-spacing: -.5px; line-height: 1;
}
.stat-lbl { font-size: 11.5px; color: var(--muted); margin-top: 3px; font-weight: 500; }

/* ── Filtres ── */
.filter-bar {
    display: flex;
    gap: 6px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: 20px;
    border: 1.5px solid var(--border);
    background: var(--surface);
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-2);
    cursor: pointer;
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
}
.filter-btn:hover { border-color: var(--orange); color: var(--orange); }
.filter-btn.active {
    background: var(--orange);
    border-color: var(--orange-dk);
    color: #fff;
    box-shadow: 0 2px 8px rgba(240,106,15,.3);
}
.filter-btn .cnt {
    background: rgba(0,0,0,.08);
    border-radius: 20px;
    padding: 1px 7px;
    font-size: 11px;
}
.filter-btn.active .cnt { background: rgba(255,255,255,.25); }

/* ── Order cards ── */
.orders-list { display: flex; flex-direction: column; gap: 14px; }

.order-card {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--r);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: transform .15s, box-shadow .15s, border-color .15s;
}
.order-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
    border-color: var(--orange-bd);
}

/* Card top strip coloré selon le statut */
.order-card-strip {
    height: 3px;
    width: 100%;
}
.strip-pending  { background: linear-gradient(90deg, var(--yellow), #fbbf24); }
.strip-confirm  { background: linear-gradient(90deg, var(--blue), #60a5fa); }
.strip-delivery { background: linear-gradient(90deg, var(--orange), #fb923c); }
.strip-done     { background: linear-gradient(90deg, var(--green), #34d399); }
.strip-cancel   { background: linear-gradient(90deg, var(--red), #f87171); }

.order-card-body {
    padding: 18px 20px;
    display: grid;
    grid-template-columns: 72px 1fr auto;
    gap: 14px;
    align-items: start;
}

/* Image produit */
.order-thumb {
    width: 72px; height: 72px;
    border-radius: 10px;
    object-fit: cover;
    border: 1.5px solid var(--border);
    flex-shrink: 0;
}
.order-thumb-placeholder {
    width: 72px; height: 72px;
    border-radius: 10px;
    background: var(--orange-lt);
    border: 1.5px solid var(--orange-bd);
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; flex-shrink: 0;
}

/* Info centrale */
.order-info {}
.order-meta {
    display: flex; align-items: center; gap: 8px;
    flex-wrap: wrap; margin-bottom: 5px;
}
.order-num {
    font-size: 11px; font-weight: 700;
    color: var(--orange); font-family: var(--mono);
    background: var(--orange-lt);
    border: 1px solid var(--orange-bd);
    border-radius: 5px; padding: 2px 7px;
}
.order-date { font-size: 11.5px; color: var(--muted); }
.order-shop {
    font-size: 15px; font-weight: 700; color: var(--text);
    margin-bottom: 3px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.order-product {
    font-size: 12.5px; color: var(--text-2);
    margin-bottom: 8px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.order-items-extra {
    font-size: 11.5px; color: var(--muted);
    font-style: italic;
}

/* Badge statut */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 11px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
    border: 1.5px solid;
}
.status-pending  { background: var(--yellow-lt); color: #92400e; border-color: #fde68a; }
.status-confirm  { background: var(--blue-lt);   color: #1e40af; border-color: #bfdbfe; }
.status-delivery { background: var(--orange-lt); color: var(--orange-dk); border-color: var(--orange-bd); }
.status-done     { background: var(--green-lt);  color: #065f46; border-color: #a7f3d0; }
.status-cancel   { background: var(--red-lt);    color: #991b1b; border-color: #fecaca; }

/* Droite */
.order-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    flex-shrink: 0;
}
.order-total {
    font-size: 17px; font-weight: 800; color: var(--text);
    font-family: var(--mono); letter-spacing: -.5px;
    white-space: nowrap;
}
.order-currency {
    font-size: 11px; font-weight: 600; color: var(--muted);
}

/* Barre de progression statut */
.order-progress {
    padding: 12px 20px 16px;
    border-top: 1px solid var(--border);
    background: #fafbfc;
}
.progress-steps {
    display: flex;
    align-items: center;
    position: relative;
}
.progress-steps::before {
    content: '';
    position: absolute;
    top: 14px; left: 14px; right: 14px;
    height: 2px;
    background: var(--border);
    z-index: 0;
}
.progress-fill {
    position: absolute;
    top: 14px; left: 14px;
    height: 2px;
    background: linear-gradient(90deg, var(--orange), #fb923c);
    z-index: 1;
    transition: width .4s ease;
}
.prog-step {
    display: flex; flex-direction: column; align-items: center;
    flex: 1; position: relative; z-index: 2;
}
.prog-dot {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--surface);
    border: 2px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; margin-bottom: 5px;
    transition: all .25s;
}
.prog-dot.done {
    background: var(--orange);
    border-color: var(--orange-dk);
    color: #fff;
    box-shadow: 0 0 0 3px var(--orange-lt);
}
.prog-dot.current {
    background: var(--surface);
    border-color: var(--orange);
    color: var(--orange);
    box-shadow: 0 0 0 3px var(--orange-lt);
    animation: pulse-dot 1.5s infinite;
}
.prog-dot.canceled {
    background: var(--red-lt);
    border-color: var(--red);
    color: var(--red);
}
@keyframes pulse-dot {
    0%,100% { box-shadow: 0 0 0 3px var(--orange-lt); }
    50%      { box-shadow: 0 0 0 6px rgba(240,106,15,.15); }
}
.prog-lbl {
    font-size: 10px; font-weight: 600;
    color: var(--muted); text-align: center;
    line-height: 1.2;
}
.prog-lbl.active { color: var(--orange); }

/* Footer carte */
.order-card-footer {
    padding: 10px 20px;
    border-top: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    flex-wrap: wrap;
    background: #fafbfc;
}
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: var(--r-sm);
    font-size: 12.5px;
    font-weight: 600;
    font-family: var(--font);
    cursor: pointer;
    text-decoration: none;
    border: 1.5px solid;
    transition: all .15s;
    white-space: nowrap;
}
.btn-track {
    background: var(--orange-lt);
    border-color: var(--orange-bd);
    color: var(--orange-dk);
}
.btn-track:hover {
    background: var(--orange);
    color: #fff;
    border-color: var(--orange-dk);
}
.btn-review {
    background: var(--green-lt);
    border-color: #a7f3d0;
    color: #065f46;
}
.btn-review:hover {
    background: var(--green);
    color: #fff;
    border-color: var(--green);
}
.btn-cancel-sm {
    background: var(--red-lt);
    border-color: #fecaca;
    color: #991b1b;
}
.btn-cancel-sm:hover {
    background: var(--red);
    color: #fff;
    border-color: var(--red);
}

/* ── Empty state ── */
.empty-state {
    text-align: center;
    padding: 64px 24px;
    background: var(--surface);
    border: 2px dashed var(--border);
    border-radius: var(--r);
}
.empty-ico { font-size: 52px; margin-bottom: 16px; opacity: .6; }
.empty-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
.empty-sub { font-size: 14px; color: var(--muted); margin-bottom: 24px; }

/* ── Pagination ── */
.pagination-wrap { margin-top: 28px; display: flex; justify-content: center; }
.pagination-wrap .pagination { gap: 4px; display: flex; flex-wrap: wrap; justify-content: center; }
.pagination-wrap .page-link {
    border-radius: 8px !important;
    border: 1.5px solid var(--border) !important;
    color: var(--text-2) !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    padding: 7px 13px !important;
    transition: all .15s !important;
}
.pagination-wrap .page-link:hover {
    background: var(--orange-lt) !important;
    border-color: var(--orange-bd) !important;
    color: var(--orange) !important;
}
.pagination-wrap .page-item.active .page-link {
    background: var(--orange) !important;
    border-color: var(--orange-dk) !important;
    color: #fff !important;
    box-shadow: 0 2px 8px rgba(240,106,15,.3) !important;
}

/* ── Flash ── */
.flash-success {
    display: flex; align-items: center; gap: 10px;
    background: var(--green-lt);
    border: 1.5px solid #a7f3d0;
    border-radius: var(--r-sm);
    padding: 12px 16px;
    font-size: 13.5px; font-weight: 500; color: #065f46;
    margin-bottom: 20px;
}

/* ── Responsive ── */
@media (max-width: 700px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .order-card-body { grid-template-columns: 56px 1fr; }
    .order-right { flex-direction: row; align-items: center; grid-column: 1 / -1; border-top: 1px solid var(--border); padding-top: 10px; }
    .order-total { font-size: 15px; }
    .order-thumb, .order-thumb-placeholder { width: 56px; height: 56px; }
    .prog-lbl { display: none; }
    .top-bar { margin: -28px -12px 24px; padding: 0 14px; height: 56px; }
    .top-bar-title h1 { font-size: 15px; }
    .btn-back { padding: 7px 13px; font-size: 12.5px; }
}
@media (max-width: 420px) {
    .stats-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
    .stat-card { padding: 12px 14px; }
    .stat-val { font-size: 18px; }
    .orders-page { padding: 16px 12px 40px; }
    .filter-bar { gap: 4px; }
    .filter-btn { padding: 6px 11px; font-size: 12px; }
    .top-bar { margin: -16px -12px 20px; height: 52px; }
    .top-bar-title p { display: none; }
}
</style>
@endpush

@section('content')
@php
use App\Models\Order;

$statusConfig = [
    Order::STATUS_EN_ATTENTE   => ['class'=>'pending',  'strip'=>'strip-pending',  'label'=>'En attente',  'ico'=>'⏳', 'step'=>0],
    Order::STATUS_CONFIRMEE    => ['class'=>'confirm',  'strip'=>'strip-confirm',  'label'=>'Confirmée',   'ico'=>'📦', 'step'=>1],
    Order::STATUS_EN_LIVRAISON => ['class'=>'delivery', 'strip'=>'strip-delivery', 'label'=>'En livraison','ico'=>'🚚', 'step'=>2],
    Order::STATUS_LIVREE       => ['class'=>'done',     'strip'=>'strip-done',     'label'=>'Livrée',      'ico'=>'✅', 'step'=>3],
    Order::STATUS_ANNULEE      => ['class'=>'cancel',   'strip'=>'strip-cancel',   'label'=>'Annulée',     'ico'=>'❌', 'step'=>-1],
];

// Compteurs globaux (passés par le contrôleur, indépendants du filtre actif)
$totalCount    = $counts['all'];
$pendingCount  = $counts['en_attente'];
$deliveryCount = $counts['en_livraison'];
$doneCount     = $counts['livrée'];

// Filtre actif (URL ?status=...)
$activeFilter  = request('status', 'all');

// Devise de la première commande
$devise = $orders->first()?->shop?->currency ?? 'GNF';

// ── Grouper uniquement les commandes bulk-assignées ensemble (même delivery_batch_id)
// Une assignation individuelle (batch_id null) → chaque commande reste seule
$_statusPriority = ['en_attente'=>0,'confirmée'=>1,'en_livraison'=>2,'livrée'=>3,'annulée'=>4];
$groups = $orders->getCollection()
    ->groupBy(function ($o) {
        // Regrouper seulement si batch_id explicite (assignation groupée intentionnelle)
        return $o->delivery_batch_id ?: ('__solo__' . $o->id);
    })
    ->map(function ($grp) use ($_statusPriority) {
        $status = $grp->sortBy(fn($o) => $_statusPriority[$o->status] ?? 99)->first()->status;
        $first  = $grp->first();
        return [
            'orders'        => $grp,
            'order'         => $first,
            'count'         => $grp->count(),
            'total'         => $grp->sum('total'),
            'status'        => $status,
            'all_items'     => $grp->flatMap(fn($o) => $o->items)->values(),
            'shops'         => $grp->map(fn($o) => $o->shop)->filter()->unique('id')->values(),
            'all_delivered' => $grp->every(fn($o) => $o->status === Order::STATUS_LIVREE),
            'has_review'    => $grp->every(fn($o) => $o->review !== null),
        ];
    })->values();
@endphp

<div class="orders-page">

    {{-- ── Top Bar ── --}}
    <div class="top-bar">
        <a href="{{ route('client.dashboard') }}" class="btn-back">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            Retour
        </a>
        <div class="top-bar-title">
            <h1>Mes commandes</h1>
            <p>Suivez toutes vos commandes en temps réel</p>
        </div>
        <div class="top-bar-ico">📦</div>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
    <div class="flash-success">
        <span style="font-size:18px">✅</span>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Stats ── --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-ico orange">📦</div>
            <div>
                <div class="stat-val">{{ $orders->total() }}</div>
                <div class="stat-lbl">Total commandes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico yellow">⏳</div>
            <div>
                <div class="stat-val">{{ $pendingCount }}</div>
                <div class="stat-lbl">En attente</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico blue">🚚</div>
            <div>
                <div class="stat-val">{{ $deliveryCount }}</div>
                <div class="stat-lbl">En livraison</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico green">✅</div>
            <div>
                <div class="stat-val">{{ $doneCount }}</div>
                <div class="stat-lbl">Livrées</div>
            </div>
        </div>
    </div>

    {{-- ── Filtres ── --}}
    <div class="filter-bar">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}"
           class="filter-btn {{ $activeFilter === 'all' ? 'active' : '' }}">
            Toutes <span class="cnt">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => Order::STATUS_EN_ATTENTE, 'page' => 1]) }}"
           class="filter-btn {{ $activeFilter === Order::STATUS_EN_ATTENTE ? 'active' : '' }}">
            ⏳ En attente @if($counts['en_attente'] > 0)<span class="cnt">{{ $counts['en_attente'] }}</span>@endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => Order::STATUS_CONFIRMEE, 'page' => 1]) }}"
           class="filter-btn {{ $activeFilter === Order::STATUS_CONFIRMEE ? 'active' : '' }}">
            📦 Confirmées @if($counts['confirmée'] > 0)<span class="cnt">{{ $counts['confirmée'] }}</span>@endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => Order::STATUS_EN_LIVRAISON, 'page' => 1]) }}"
           class="filter-btn {{ $activeFilter === Order::STATUS_EN_LIVRAISON ? 'active' : '' }}">
            🚚 En livraison @if($counts['en_livraison'] > 0)<span class="cnt">{{ $counts['en_livraison'] }}</span>@endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => Order::STATUS_LIVREE, 'page' => 1]) }}"
           class="filter-btn {{ $activeFilter === Order::STATUS_LIVREE ? 'active' : '' }}">
            ✅ Livrées @if($counts['livrée'] > 0)<span class="cnt">{{ $counts['livrée'] }}</span>@endif
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => Order::STATUS_ANNULEE, 'page' => 1]) }}"
           class="filter-btn {{ $activeFilter === Order::STATUS_ANNULEE ? 'active' : '' }}">
            ❌ Annulées @if($counts['annulée'] > 0)<span class="cnt">{{ $counts['annulée'] }}</span>@endif
        </a>
    </div>

    {{-- ── Liste des commandes ── --}}
    @if($orders->isEmpty())
    <div class="empty-state">
        <div class="empty-ico">🛍️</div>
        <div class="empty-title">Aucune commande trouvée</div>
        <div class="empty-sub">Vous n'avez pas encore passé de commande.<br>Découvrez nos boutiques et commencez vos achats !</div>
        <a href="{{ route('client.dashboard') }}" class="btn-back" style="background:linear-gradient(135deg,var(--orange),var(--orange-dk));border-color:transparent;box-shadow:0 4px 14px rgba(240,106,15,.35)">
            🏪 Découvrir les boutiques
        </a>
    </div>
    @else
    <div class="orders-list">
        @foreach($groups as $group)
        @php
            $order        = $group['order'];
            $isBulk       = $group['count'] > 1;
            $cfg          = $statusConfig[$group['status']] ?? $statusConfig[Order::STATUS_EN_ATTENTE];
            $step         = $cfg['step'];
            $allItems     = $group['all_items'];
            $firstItem    = $allItems->first();
            $firstProduct = $firstItem?->product;
            $extraItems   = $allItems->count() - 1;
            $devise       = $order->shop?->currency ?? 'GNF';
            $totalQty     = $allItems->sum('quantity');
            $totalProds   = $allItems->count();

            $steps = [
                ['ico'=>'🕐','lbl'=>'Reçue'],
                ['ico'=>'📦','lbl'=>'Confirmée'],
                ['ico'=>'🚚','lbl'=>'En route'],
                ['ico'=>'🏠','lbl'=>'Livrée'],
            ];
            $fillPct = $step >= 0 ? min(($step / 3) * 100, 100) : 0;
        @endphp

        <div class="order-card">
            {{-- Strip coloré --}}
            <div class="order-card-strip {{ $cfg['strip'] }}"></div>

            {{-- Corps principal --}}
            <div class="order-card-body">

                {{-- Image produit --}}
                @if($firstProduct?->image)
                    <img src="{{ \App\Services\ImageOptimizer::url($firstProduct->image, 'thumb') ?? asset('storage/'.$firstProduct->image) }}"
                         alt="{{ $firstProduct->name }}"
                         class="order-thumb"
                         loading="lazy"
                         width="72" height="72">
                @else
                    <div class="order-thumb-placeholder">{{ $isBulk ? '📦' : '🛍️' }}</div>
                @endif

                {{-- Infos --}}
                <div class="order-info">
                    <div class="order-meta">
                        @if($isBulk)
                            <span class="order-num" style="font-size:11px">
                                {{ $group['count'] }} commandes · 1 livraison
                            </span>
                            <span class="order-date" style="font-size:10px;color:var(--muted)">
                                {{ $group['orders']->map(fn($o)=>'#'.str_pad($o->id,5,'0',STR_PAD_LEFT))->implode(' · ') }}
                            </span>
                        @else
                            <span class="order-num">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <span class="order-date">{{ $order->created_at->format('d/m/Y · H:i') }}</span>
                        @endif
                    </div>
                    <div class="order-shop">
                        🏪 {{ $group['shops']->pluck('name')->implode(' · ') ?: '—' }}
                    </div>
                    @if($firstProduct)
                    <div class="order-product">
                        {{ Str::limit($firstProduct->name, 40) }}
                        @if($extraItems > 0)
                            <span class="order-items-extra">+ {{ $extraItems }} autre{{ $extraItems > 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                    @endif
                    <span class="status-badge status-{{ $cfg['class'] }}">
                        {{ $cfg['ico'] }} {{ $cfg['label'] }}
                    </span>
                </div>

                {{-- Total --}}
                <div class="order-right">
                    <div>
                        <div class="order-total">{{ number_format($group['total'], 0, ',', ' ') }}</div>
                        <div class="order-currency">{{ $devise }}</div>
                    </div>
                </div>

            </div>

            {{-- Barre de progression (pas pour les annulées) --}}
            @if($step >= 0)
            <div class="order-progress">
                <div class="progress-steps">
                    <div class="progress-fill" style="width: {{ $fillPct }}%"></div>
                    @foreach($steps as $i => $s)
                    <div class="prog-step">
                        <div class="prog-dot {{ $i < $step ? 'done' : ($i === $step ? 'current' : '') }}">
                            @if($i < $step) ✓ @else {{ $s['ico'] }} @endif
                        </div>
                        <div class="prog-lbl {{ $i === $step ? 'active' : '' }}">{{ $s['lbl'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div style="padding:10px 20px;background:#fef2f2;border-top:1px solid #fecaca;font-size:12px;color:#991b1b;font-weight:600;display:flex;align-items:center;gap:6px">
                ❌ Cette commande a été annulée
            </div>
            @endif

            {{-- Footer actions --}}
            <div class="order-card-footer">
                <div style="font-size:11.5px;color:var(--muted)">
                    Qté : <strong style="color:var(--text)">{{ $totalQty }}</strong> article{{ $totalQty > 1 ? 's' : '' }}
                    &middot; {{ $totalProds }} produit{{ $totalProds > 1 ? 's' : '' }}
                </div>
                <div style="display:flex;gap:7px;flex-wrap:wrap">
                    {{-- Suivre : lien vers la 1ère commande du groupe --}}
                    <a href="{{ route('suivi.show', $order) }}" class="btn-action btn-track">
                        🔍 Suivre
                    </a>
                    {{-- Avis : seulement si toutes les commandes du groupe sont livrées et sans avis --}}
                    @if($group['all_delivered'] && !$group['has_review'])
                    <a href="{{ route('client.reviews.create', $order) }}" class="btn-action btn-review">
                        ⭐ Laisser un avis
                    </a>
                    @endif
                </div>
            </div>

        </div>
        @endforeach
    </div>

    {{-- Pagination ── --}}
    <div class="pagination-wrap">
        {{ $orders->appends(request()->query())->links() }}
    </div>
    @endif

</div>
@endsection
