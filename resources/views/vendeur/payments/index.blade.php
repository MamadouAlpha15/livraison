@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h2 class="h4 fw-bold mb-0">💰 Mes revenus</h2>
    </div>

    {{-- TOTAL REVENUS --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <div class="text-muted small">Total des revenus confirmés</div>
                <div class="h5 fw-bold">{{ number_format($totalRevenue, 0, ',', ' ') }} GNF</div>
            </div>
            <div class="d-none d-md-block">
                <i class="bi bi-cash-stack fs-2 text-success"></i>
            </div>
        </div>
    </div>

    {{-- TABLE DES PAIEMENTS --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase">
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
                            <td class="fw-semibold">{{ $loop->iteration }}</td>
                            <td>#{{ $payment->order->id }}</td>
                            <td class="fw-bold">{{ number_format($payment->amount, 0, ',', ' ') }} GNF</td>
                            <td>{{ ucfirst($payment->method) }}</td>
                            <td>
                                @if($payment->status === 'payé')
                                    <span class="badge bg-success">Payé</span>
                                @else
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @endif
                            </td>
                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucun revenu disponible.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $payments->links() }}
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Table hover & card styling */
    .table-hover tbody tr:hover { background: rgba(13,110,253,0.05); }
    .card { border-radius: 1rem; }
    .badge { font-size: 0.85rem; padding: 0.45em 0.65em; }
    @media (max-width: 767.98px) {
        .card-body { flex-direction: column !important; gap: 0.75rem; }
    }
</style>
@endsection
