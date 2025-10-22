@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>💵 Suivi des paiements</h2>
</div>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Client</th>
            <th>Boutique</th>
            <th>Montant</th>
            <th>Méthode</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payments as $payment)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $payment->order->client->name ?? '—' }}</td>
            <td>{{ $payment->order->shop->name ?? '—' }}</td>
            <td>{{ number_format($payment->amount, 0, ',', ' ') }} GNF</td>
            <td>💵 Cash</td>
            <td>
                @if($payment->status == 'pending')
                    <span class="badge bg-warning">⏳ En attente</span>
                @else
                    <span class="badge bg-success">✅ Payé</span>
                @endif
            </td>
            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">Aucun paiement trouvé.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $payments->links() }}
@endsection
