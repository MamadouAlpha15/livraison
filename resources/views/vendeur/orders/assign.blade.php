{{-- resources/views/vendeur/orders/assign.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
  /* Clean premium look */
  .livreur-card { border-radius: .8rem; transition: transform .12s, box-shadow .12s; background: #fff; }
  .livreur-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,0.08); }
  .status-dot { width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:.6rem; }
  .online { background: #28a745; box-shadow: 0 0 6px rgba(40,167,69,0.12); }
  .offline { background: #6c757d; }
  .product-thumb { width:64px; height:64px; object-fit:cover; border-radius:.6rem; }
  .card-detail { border-radius:1rem; box-shadow: 0 8px 20px rgba(12,38,63,0.04); }
  .btn-assign { border-radius: 8px; padding: .4rem .9rem; }
  .badge-assigned { background: linear-gradient(90deg,#20c997,#198754); color:#fff; padding:.45rem .6rem; border-radius:.6rem; font-weight:700; }
  @media (max-width:767.98px) {
    .product-thumb { width:52px; height:52px; }
  }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="mb-0">Assignation — Commande <small class="text-muted">#{{ $order->id }}</small></h3>
            <div class="small text-muted mt-1">
                Client : <strong>{{ $order->client->name }}</strong> — Total : <strong>{{ number_format($order->total,0,',',' ') }} GNF</strong>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">← Retour</a>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">Voir toutes les commandes</a>
        </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        {{-- LEFT: détails commande --}}
        <div class="col-12 col-lg-7">
            <div class="card card-detail p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-2">Détails de la commande</h5>
                        <div class="small text-muted mb-2">Produits</div>
                        <div class="list-group list-group-flush">
                            @foreach($order->items as $item)
                                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/'.$item->product->image) }}" class="product-thumb" alt="{{ $item->product->name }}">
                                    @endif
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $item->product->name ?? '—' }}</div>
                                        <div class="text-muted small">Qté : {{ $item->quantity }} • PU : {{ number_format($item->price,0,',',' ') }} GNF</div>
                                    </div>
                                    <div class="text-end small text-muted">Sous-total : <strong>{{ number_format($item->quantity * $item->price,0,',',' ') }} GNF</strong></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="ms-3 text-end">
                        <div class="small text-muted">Statut</div>
                        <div class="fw-bold">
                            @switch($order->status)
                                @case(\App\Models\Order::STATUS_EN_ATTENTE) <span class="badge bg-warning text-dark">⏳ En attente</span> @break
                                @case(\App\Models\Order::STATUS_CONFIRMEE) <span class="badge bg-info text-dark">📦 Confirmée</span> @break
                                @case(\App\Models\Order::STATUS_EN_LIVRAISON) <span class="badge bg-primary">🚚 En livraison</span> @break
                                @case(\App\Models\Order::STATUS_LIVREE) <span class="badge bg-success">✅ Livrée</span> @break
                                @case(\App\Models\Order::STATUS_ANNULEE) <span class="badge bg-danger">❌ Annulée</span> @break
                                @default <span class="badge bg-secondary">❔</span>
                            @endswitch
                        </div>

                        <div class="mt-3 small text-muted">Total commande</div>
                        <div class="fw-bold fs-5">{{ number_format($order->total,0,',',' ') }} GNF</div>

                        @if($order->ordonnance)
                            <div class="mt-3">
                                <a href="{{ asset('storage/'.$order->ordonnance) }}" target="_blank" class="btn btn-sm btn-outline-info">📎 Voir ordonnance</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informations client --}}
            <div class="card p-3 mb-3">
                <h6 class="mb-2">Informations client</h6>
                <div class="small text-muted">Email : <strong>{{ $order->client->email }}</strong></div>
                @if($order->client->phone)
                    <div class="small text-muted">Téléphone : <strong>{{ $order->client->phone }}</strong></div>
                @endif
                @if($order->address)
                    <div class="small text-muted">Adresse : <strong>{{ $order->address }}</strong></div>
                @endif
            </div>
        </div>

        {{-- RIGHT: liste livreurs --}}
        <div class="col-12 col-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Livreurs disponibles</h5>
                <small class="text-muted">Cliquez pour assigner</small>
            </div>

            <div class="row g-2">
                @forelse($livreurs as $livreur)
                    <div class="col-12">
                        <div class="p-3 livreur-card d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $livreur->name }}</div>
                                    <div class="small text-muted">{{ $livreur->email }}</div>
                                    <div class="mt-1 small text-muted">
                                        <span class="status-dot {{ $livreur->is_available ? 'online' : 'offline' }}"></span>
                                        {{ $livreur->is_available ? 'En ligne' : 'Hors ligne' }}
                                        @if(isset($livreur->last_seen))
                                            • <span class="text-muted small">vu {{ $livreur->last_seen->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                @if($order->livreur_id == $livreur->id)
                                    <div class="badge-assigned">✅ Assigné · {{ $livreur->name }}</div>
                                @else
                                    {{-- Formulaire assignation via fetch --}}
                                    <form class="assign-form m-0" action="{{ route('orders.assign', $order) }}" method="POST" data-livreur-name="{{ e($livreur->name) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="livreur_id" value="{{ $livreur->id }}">
                                        <button type="submit" class="btn btn-primary btn-assign assign-btn" data-livreur-id="{{ $livreur->id }}">
                                            <span class="d-none d-sm-inline">Assigner</span>
                                            <span class="d-inline d-sm-none">✅</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">Aucun livreur rattaché à cette boutique pour l'instant.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function getCsrfToken(form) {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var t = form.querySelector('input[name="_token"]');
        return t ? t.value : '';
    }

    document.querySelectorAll('.assign-form').forEach(function(form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = form.querySelector('.assign-btn');
            if (!submitBtn) return;
            var livreurName = form.dataset.livreurName || 'livreur';
            submitBtn.disabled = true;
            var originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Assignation...';

            var fd = new FormData(form);
            fd.set('_method', 'PUT');
            var csrf = getCsrfToken(form);
            if (csrf && !fd.get('_token')) fd.set('_token', csrf);

            fetch(form.action, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: fd,
                credentials: 'same-origin',
            })
            .then(resp => {
                if (!resp.ok) throw resp;
                return resp.json(); // le contrôleur doit retourner JSON
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Livreur assigné',
                    html: '<strong>' + escapeHtml(data.livreur_name) + '</strong><div class="small text-muted mt-1">La commande a bien été assignée.</div>',
                    timer: 2200,
                    showConfirmButton: false,
                    position: 'top-end',
                });

                var parent = form.closest('.livreur-card') || form.parentNode;
                if (parent) {
                    var badge = document.createElement('div');
                    badge.className = 'badge-assigned';
                    badge.innerText = '✅ Assigné · ' + data.livreur_name;
                    form.parentNode.replaceChild(badge, form);
                }

                document.querySelectorAll('.assign-btn').forEach(b => { b.disabled = true; b.innerText = 'Assigné'; });
            })
            .catch(err => {
                console.error('Assign error:', err);
                Swal.fire({ icon:'error', title:'Erreur', text:'Impossible d\'assigner — vérifie la console.' });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    });

    function escapeHtml(unsafe) {
      return unsafe.replace(/[&<"']/g, m => ({'&':'&amp;','<':'&lt;','"':'&quot;',"'":'&#039;'}[m]));
    }
});
</script>
@endpush
