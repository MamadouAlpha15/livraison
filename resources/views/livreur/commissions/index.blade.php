@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3 class="mb-3">💸 Mes commissions</h3>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card border-warning">
        <div class="card-body">
          <h5 class="card-title">En attente</h5>
          <div class="fs-4 text-warning fw-bold">{{ number_format($pendingTotal, 0, ',', ' ') }} GNF</div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-success">
        <div class="card-body">
          <h5 class="card-title">Payées</h5>
          <div class="fs-4 text-success fw-bold">{{ number_format($paidTotal, 0, ',', ' ') }} GNF</div>
        </div>
      </div>
    </div>
  </div>

  <h5 class="mt-3">En attente</h5>
  <div class="table-responsive mb-4">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>#</th><th>Commande</th><th>Montant</th><th>Taux</th><th>Total Cmd</th><th>Date</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pending as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>#{{ $c->order_id }}</td>
            <td class="fw-bold">{{ number_format($c->amount, 0, ',', ' ') }} GNF</td>
            <td>{{ $c->rate * 100 }}%</td>
            <td>{{ number_format($c->order_total, 0, ',', ' ') }} GNF</td>
            <td>{{ $c->created_at->format('d/m/Y H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">Aucune commission en attente</td></tr>
        @endforelse
      </tbody>
    </table>
    {{ $pending->links() }}
  </div>

  <h5>Payées</h5>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead>
        <tr>
          <th>#</th><th>Commande</th><th>Montant</th><th>Référence</th><th>Payée le</th>
        </tr>
      </thead>
      <tbody>
        @forelse($paid as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>#{{ $c->order_id }}</td>
            <td class="fw-bold">{{ number_format($c->amount, 0, ',', ' ') }} GNF</td>
            <td>{{ $c->payout_ref ?? '—' }}</td>
            <td>{{ optional($c->paid_at)->format('d/m/Y H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted">Pas de commission payée</td></tr>
        @endforelse
      </tbody>
    </table>
    {{ $paid->links() }}
  </div>
</div>
@endsection
