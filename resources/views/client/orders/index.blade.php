@extends('layouts.app')

@push('styles')
<style>
/* ====== General ====== */
body { background-color: #f8f9fa; }
.card { border-radius: 1rem; transition: transform .2s, box-shadow .2s; }
.card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
.badge { font-size: .85rem; }
.table thead { background-color: #0d6efd; color: white; }
.table td, .table th { vertical-align: middle; }
img.product-thumb { width: 80px; height: 80px; object-fit: cover; border-radius: .5rem; }

/* ====== Responsive Cards ====== */
.order-card { border-radius: 1rem; transition: transform .2s, box-shadow .2s; }
.order-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
@media(max-width:767.98px){
    .order-card { padding: 1rem; margin-bottom: 1rem; }
    img.product-thumb { width: 60px; height: 60px; }
}
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📦 Mes commandes</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Desktop Table --}}
    <div class="d-none d-md-block">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Boutique</th>
                    <th>Produit</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $order->shop->name }}</td>
                    <td>
                        @if($order->items->first()?->product?->image)
                            <img src="{{ asset('storage/' . $order->items->first()->product->image) }}" 
                                 alt="Produit" class="product-thumb">
                            <div class="small mt-1">{{ Str::limit($order->items->first()->product->name, 30) }}</div>
                        @else
                            <span class="text-muted">Aucun produit</span>
                        @endif
                    </td>
                    <td class="fw-bold">{{ number_format($order->total, 0, ',', ' ') }} GNF</td>
                    <td>
                        @switch($order->status)
                            @case(\App\Models\Order::STATUS_EN_ATTENTE)
                                <span class="badge bg-warning text-dark">⏳ En attente</span>
                            @break
                            @case(\App\Models\Order::STATUS_CONFIRMEE)
                                <span class="badge bg-info text-dark">📦 Confirmée</span>
                            @break
                            @case(\App\Models\Order::STATUS_EN_LIVRAISON)
                                <span class="badge bg-primary text-white">🚚 En livraison</span>
                            @break
                            @case(\App\Models\Order::STATUS_LIVREE)
                                <span class="badge bg-success">✅ Livrée</span>
                            @break
                            @case(\App\Models\Order::STATUS_ANNULEE)
                                <span class="badge bg-danger">❌ Annulée</span>
                            @break
                            @default
                                <span class="badge bg-secondary">❔ Inconnu</span>
                        @endswitch
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Aucune commande.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $orders->links() }}</div>
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        @forelse($orders as $order)
        <div class="card shadow-sm order-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="fw-bold mb-1">{{ $order->shop->name }}</h5>
                        <p class="text-muted mb-1">Total : {{ number_format($order->total, 0, ',', ' ') }} GNF</p>
                        <p class="text-muted mb-0">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        @switch($order->status)
                            @case(\App\Models\Order::STATUS_EN_ATTENTE)
                                <span class="badge bg-warning text-dark">⏳</span>
                            @break
                            @case(\App\Models\Order::STATUS_CONFIRMEE)
                                <span class="badge bg-info text-dark">📦</span>
                            @break
                            @case(\App\Models\Order::STATUS_EN_LIVRAISON)
                                <span class="badge bg-primary text-white">🚚</span>
                            @break
                            @case(\App\Models\Order::STATUS_LIVREE)
                                <span class="badge bg-success">✅</span>
                            @break
                            @case(\App\Models\Order::STATUS_ANNULEE)
                                <span class="badge bg-danger">❌</span>
                            @break
                            @default
                                <span class="badge bg-secondary">❔</span>
                        @endswitch
                    </div>
                </div>

                {{-- Produit image --}}
                @if($order->items->first()?->product?->image)
                    <div class="text-center mb-2">
                        <img src="{{ asset('storage/' . $order->items->first()->product->image) }}" 
                             alt="Produit" class="product-thumb">
                        <div class="small mt-1">{{ Str::limit($order->items->first()->product->name, 30) }}</div>
                    </div>
                @endif

                {{-- Action buttons --}}
                <div class="d-flex gap-2 mt-2">
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm flex-fill">🔎 Détails</a>
                    @if($order->status == \App\Models\Order::STATUS_EN_ATTENTE)
                        <form action="{{ route('orders.confirm', $order) }}" method="POST" class="flex-fill">
                            @csrf @method('PUT')
                            <button class="btn btn-success btn-sm w-100">✅ Confirmer</button>
                        </form>
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="flex-fill">
                            @csrf @method('PUT')
                            <button class="btn btn-danger btn-sm w-100">❌ Annuler</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted">Aucune commande.</div>
        @endforelse
        <div class="mt-3">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
