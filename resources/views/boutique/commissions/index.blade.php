@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3 class="mb-3">💸 Commissions livreurs</h3>

  {{-- Totaux et filtre --}}
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card border-warning">
        <div class="card-body text-center">
          <div class="text-muted">Total en attente</div>
          <div class="fs-4 text-warning fw-bold">{{ number_format($totalPending, 0, ',', ' ') }} GNF</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-success">
        <div class="card-body text-center">
          <div class="text-muted">Total payé</div>
          <div class="fs-4 text-success fw-bold">{{ number_format($totalPaid, 0, ',', ' ') }} GNF</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select">
          <option value="en_attente" {{ $status=='en_attente'?'selected':'' }}>En attente</option>
          <option value="payé"       {{ $status=='payé'?'selected':'' }}>Payées</option>
        </select>
        <button class="btn btn-primary">Filtrer</button>
      </form>
    </div>
  </div>

  {{-- Si on affiche les en attente, on propose un seul formulaire qui englobe tout --}}
  @if($status === 'en_attente' && $commissions->count())
    <form id="payForm" action="{{ route('boutique.commissions.pay') }}" method="POST">
      @csrf

      {{-- Sticky formulaire de paiement (reste en haut) --}}
      <div class="sticky-top bg-white shadow-sm p-3 mb-3" style="z-index: 1020;">
        <div class="d-flex flex-wrap align-items-center gap-2">
          <input type="text" name="payout_ref" class="form-control me-2 mb-2" style="flex: 1" placeholder="Référence de paiement (ex: VIRM/2025-001)">
          <input type="text" name="payout_note" class="form-control me-2 mb-2" style="flex: 2" placeholder="Note interne (optionnel)">
          <button type="submit" id="markPaidBtn" class="btn btn-success mb-2" disabled>Marquer PAYÉ</button>
        </div>
      </div>

      {{-- Table des commissions (les checkbox sont maintenant DANS le même form) --}}
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-sm table-striped align-middle">
            <thead>
              <tr>
                <th><input type="checkbox" id="checkAll"></th>
                <th>#</th>
                <th>Commande</th>
                <th>Livreur</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Réf</th>
                <th>Payée le</th>
              </tr>
            </thead>
            <tbody>
              @forelse($commissions as $c)
                <tr>
                  <td><input type="checkbox" name="ids[]" value="{{ $c->id }}" class="rowCheckbox"></td>
                  <td>{{ $c->id }}</td>
                  <td>#{{ $c->order_id }}</td>
                  <td>{{ $c->livreur->name ?? '—' }}</td>
                  <td class="fw-bold">{{ number_format($c->amount, 0, ',', ' ') }} GNF</td>
                  <td><span class="badge bg-warning">En attente</span></td>
                  <td>{{ $c->payout_ref ?? '—' }}</td>
                  <td>{{ optional($c->paid_at)->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted">Aucune commission</td>
                </tr>
              @endforelse
            </tbody>
          </table>

          {{ $commissions->withQueryString()->links() }}
        </div>
      </div>
    </form>
  @else
    {{-- Quand on n'est pas en "en_attente", on affiche la table en lecture seule --}}
    <div class="card">
      <div class="card-body table-responsive">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Commande</th>
              <th>Livreur</th>
              <th>Montant</th>
              <th>Statut</th>
              <th>Réf</th>
              <th>Payée le</th>
            </tr>
          </thead>
          <tbody>
            @forelse($commissions as $c)
              <tr>
                <td>{{ $c->id }}</td>
                <td>#{{ $c->order_id }}</td>
                <td>{{ $c->livreur->name ?? '—' }}</td>
                <td class="fw-bold">{{ number_format($c->amount, 0, ',', ' ') }} GNF</td>
                <td>
                  @if($c->status==='en_attente')
                    <span class="badge bg-warning">En attente</span>
                  @else
                    <span class="badge bg-success">Payée</span>
                  @endif
                </td>
                <td>{{ $c->payout_ref ?? '—' }}</td>
                <td>{{ optional($c->paid_at)->format('d/m/Y H:i') ?? '—' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted">Aucune commission</td>
              </tr>
            @endforelse
          </tbody>
        </table>

        {{ $commissions->withQueryString()->links() }}
      </div>
    </div>
  @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const checkAll = document.getElementById('checkAll');
  const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
  const markPaidBtn = document.getElementById('markPaidBtn');
  const payForm = document.getElementById('payForm');

  function updateMarkPaidState() {
    const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
    if (markPaidBtn) markPaidBtn.disabled = !anyChecked;
  }

  checkAll?.addEventListener('change', function(e){
    rowCheckboxes.forEach(cb => cb.checked = e.target.checked);
    updateMarkPaidState();
  });

  rowCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateMarkPaidState);
  });

  // Sécurité côté client : empêcher l'envoi si aucun ids[] sélectionné
  payForm?.addEventListener('submit', function (e) {
    const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
    if (!anyChecked) {
      e.preventDefault();
      alert('Sélectionnez au moins une commission à marquer comme payée.');
      return false;
    }

    // Optionnel : on peut afficher un petit loading / désactiver le bouton pour éviter double submit
    if (markPaidBtn) {
      markPaidBtn.disabled = true;
      markPaidBtn.innerText = 'Traitement...';
    }
  });

  // Fallback (si tu préfères garder le sticky en dehors du form) :
  // On pourrait collecter les ids cochés et les ajouter dynamiquement comme inputs hidden au moment du submit.
  // Exemple (commenté) :
  /*
  payForm?.addEventListener('submit', function (e) {
    const existingHidden = payForm.querySelectorAll('input[name="ids[]"][type="hidden"]');
    existingHidden.forEach(h => h.remove());
    Array.from(rowCheckboxes).filter(cb => cb.checked).forEach(cb => {
      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'ids[]';
      hidden.value = cb.value;
      payForm.appendChild(hidden);
    });
  });
  */
});
</script>
@endpush

@endsection
