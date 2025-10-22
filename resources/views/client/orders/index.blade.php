@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>📦 Mes commandes</h2>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-striped">
    <thead class="table-primary">
        <tr>
            <th>#</th>
            <th>Boutique</th>
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
            <td>{{ number_format($order->total, 0, ',', ' ') }} GNF</td>
            <td>
                @if($order->status == 'pending')
                    <span class="badge bg-warning">⏳ En attente</span>
                @elseif($order->status == 'confirmed')
                    <span class="badge bg-info">📦 Confirmée</span>
                @elseif($order->status == 'delivering')
                    <span class="badge bg-primary">🚚 En livraison</span>
                @elseif($order->status == 'delivered')
                    <span class="badge bg-success">✅ Livrée</span>
                @else
                    <span class="badge bg-danger">❌ Annulée</span>
                @endif
            </td>
            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Aucune commande.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $orders->links() }}
@endsection
