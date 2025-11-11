{{-- resources/views/vendeur/orders/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="h4 fw-bold mb-1">📦 Commandes reçues</h2>
            <div class="small text-muted">Gérez, confirmez et suivez vos commandes rapidement.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('boutique.export.orders.excel') }}" class="btn btn-outline-primary btn-sm">Export commandes Excel</a>
            <a href="{{ route('boutique.export.orders.pdf') }}" class="btn btn-outline-secondary btn-sm" target="_blank">Export commandes PDF</a>
            <a href="{{ route('boutique.export.payments.excel') }}" class="btn btn-outline-primary btn-sm">Export paiements Excel</a>
            <a href="{{ route('boutique.export.payments.pdf') }}" class="btn btn-outline-primary btn-sm">Export paiements PDF</a>
            <a href="{{ route('boutique.export.stats.pdf') }}" class="btn btn-outline-info btn-sm">Export stats PDF</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    {{-- Desktop Table --}}
    <div class="d-none d-md-block">
        <div class="card shadow-sm mb-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>#</th>
                                <th>Client</th>
                                <th>Produits</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Ordonnance</th>
                                <th class="d-none d-lg-table-cell">Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="fw-semibold">{{ $loop->iteration }}</td>
                                    <td style="min-width:180px;">
                                        <div>{{ $order->client->name }}</div>
                                        <div class="small text-muted">{{ $order->client->email }}</div>
                                    </td>
                                    <td style="min-width:300px;">
                                        <div class="d-flex flex-column small">
                                            @foreach($order->items as $item)
                                                <div class="d-flex align-items-center gap-2 py-1 border-bottom">
                                                    @if($item->product && $item->product->image)
                                                        <img src="{{ asset('storage/'.$item->product->image) }}" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:.5rem">
                                                    @endif
                                                    <div>
                                                        <div class="fw-semibold">{{ Str::limit($item->product->name ?? '—', 50) }}</div>
                                                        <div class="text-muted">Qté: {{ $item->quantity }} • PU: {{ number_format($item->price,0,',',' ') }} GNF</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="fw-bold">{{ number_format($order->total,0,',',' ') }} GNF</td>
                                    <td>
                                        @switch($order->status)
                                            @case(\App\Models\Order::STATUS_EN_ATTENTE)
                                                <span class="badge bg-warning text-dark">⏳ En attente</span>
                                            @break
                                            @case(\App\Models\Order::STATUS_CONFIRMEE)
                                                <span class="badge bg-info text-dark">📦 Confirmée</span>
                                            @break
                                            @case(\App\Models\Order::STATUS_EN_LIVRAISON)
                                                <span class="badge bg-primary">🚚 En livraison</span>
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
                                    <td>
                                        @if($order->shop && strtolower($order->shop->type) === 'pharmacie' && $order->ordonnance)
                                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#ordonnanceModal" data-url="{{ asset('storage/'.$order->ordonnance) }}">📎 Voir</button>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td style="min-width:180px;">
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($order->status == \App\Models\Order::STATUS_EN_ATTENTE)
                                                <form action="{{ route('orders.confirm', $order) }}" method="POST" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-sm btn-success">✅ Confirmer</button>
                                                </form>
                                                <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-sm btn-danger">❌ Annuler</button>
                                                </form>
                                            @else
                                                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">🔎 Détails</a>
                                            @endif

                                            <a href="{{ route('orders.assign.show', $order) }}" class="btn btn-sm btn-outline-warning">Assignation</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Aucune commande reçue.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center">{{ $orders->links() }}</div>
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        @forelse($orders as $order)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $order->client->name }}</div>
                            <div class="small text-muted">{{ $order->client->email }}</div>
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
                                    <span class="badge bg-primary">🚚</span>
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

                    <div class="mt-3 small">
                        @foreach($order->items as $item)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                @if($item->product && $item->product->image)
                                    <img src="{{ asset('storage/'.$item->product->image) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:.5rem"/>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ Str::limit($item->product->name ?? '—', 40) }}</div>
                                    <div class="text-muted">Qté: {{ $item->quantity }} • PU: {{ number_format($item->price,0,',',' ') }} GNF</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($order->shop && strtolower($order->shop->type) === 'pharmacie' && $order->ordonnance)
                        <div class="mt-2">
                            <button class="btn btn-sm btn-info w-100" data-bs-toggle="modal" data-bs-target="#ordonnanceModal" data-url="{{ asset('storage/'.$order->ordonnance) }}">📎 Voir ordonnance</button>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="fw-bold">{{ number_format($order->total,0,',',' ') }} GNF</div>
                        <div class="small text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="mt-3 d-flex gap-1 flex-wrap">
                        @if($order->status == \App\Models\Order::STATUS_EN_ATTENTE)
                            <form action="{{ route('orders.confirm', $order) }}" method="POST" class="flex-fill">
                                @csrf @method('PUT')
                                <button class="btn btn-success btn-sm w-100">✅ Confirmer</button>
                            </form>
                            <form action="{{ route('orders.cancel', $order) }}" method="POST" class="flex-fill">
                                @csrf @method('PUT')
                                <button class="btn btn-danger btn-sm w-100">❌ Annuler</button>
                            </form>
                        @else
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm w-100">🔎 Détails</a>
                        @endif

                        <a href="{{ route('orders.assign.show', $order) }}" class="btn btn-outline-warning btn-sm w-100">Assignation</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted">Aucune commande reçue.</div>
        @endforelse
        <div class="d-flex justify-content-center">{{ $orders->links() }}</div>
    </div>

</div>

{{-- Ordonnance modal --}}
<div class="modal fade" id="ordonnanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ordonnance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe src="" id="ordonnanceFrame" style="width:100%;height:70vh;" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Modal ordonnance
    var ordonnanceModal = document.getElementById('ordonnanceModal');
    if(ordonnanceModal){
        ordonnanceModal.addEventListener('show.bs.modal', function (event) {
            var url = event.relatedTarget.getAttribute('data-url');
            document.getElementById('ordonnanceFrame').src = url || '';
        });
        ordonnanceModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('ordonnanceFrame').src = '';
        });
    }
});
</script>
@endsection

@section('styles')
<style>
.table-hover tbody tr:hover{background: rgba(13,110,253,0.03);}
.card{border-radius:.9rem;}
.product-thumb{width:60px;height:60px;object-fit:cover;border-radius:.5rem;}
@media(max-width:767.98px){.product-thumb{width:48px;height:48px;}}
</style>
@endsection
