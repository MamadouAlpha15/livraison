@extends('layouts.app')

@section('content')
<div class="container">
    <h2>💰 Mes revenus</h2>

    <div class="alert alert-success">
        Total des revenus confirmés : <strong>{{ number_format($totalRevenue, 0, ',', ' ') }} GNF</strong>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Commande</th>
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
                <td>#{{ $payment->order->id }}</td>
                <td>{{ number_format($payment->amount, 0, ',', ' ') }} GNF</td>
                <td>{{ ucfirst($payment->method) }}</td>
                <td>
                    @if($payment->status === 'paid')
                        <span class="badge bg-success">Payé</span>
                    @else
                        <span class="badge bg-warning">En attente</span>
                    @endif
                </td>
                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Aucun revenu disponible.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $payments->links() }}
</div>
@endsection
