{{-- resources/views/employe/orders/index.blade.php --}}
@extends('layouts.app')

@push('styles')
<style>
  /* Images produits */
  .product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: .5rem; }
  @media (max-width: 767.98px) { .product-thumb { width: 40px; height: 40px; } }

  /* Cartes desktop */
  .card-order { border-radius: 1rem; transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out; }
  .card-order:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); }

  /* Hover table */
  .table-hover tbody tr:hover { background: rgba(13,110,253,0.05); }

  /* Badges */
  .badge-status { font-size: 0.8rem; font-weight: 600; }

  /* Actions desktop */
  .action-btn { margin-right: 0.25rem; }

  /* Mobile cards */
  .mobile-card { border-radius: 1rem; box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.08); }
  .mobile-card .btn { font-size: 0.85rem; }

  /* Responsive spacing */
  @media (max-width: 767.98px) {
      .form-select-sm { font-size: 0.85rem; }
      .btn-sm { font-size: 0.85rem; padding: 0.35rem 0.6rem; }
  }
</style>
@endpush

@section('content')
<div class="container py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <h2 class="h4 fw-bold mb-0">📦 Commandes à assigner</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif

    {{-- ======================== DESKTOP / TABLET (md+) ======================== --}}
    <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 shadow-sm rounded">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Boutique</th>
                        <th>Produit</th>
                        <th>Total</th>
                        <th>Ordonnance</th>
                        <th>Avis</th>
                        <th>Livreur</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr class="card-order" id="order-row-{{ $order->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->client->name }}</td>
                        <td>{{ $order->shop->name }}</td>
                        <td>
                            @if($order->items->count())
                                @php $product = $order->items->first()->product; @endphp
                                <div class="d-flex align-items-center gap-2">
                                    @if($product && $product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" class="product-thumb border" alt="{{ $product->name }}">
                                    @endif
                                    <span>{{ $product?->name ?? '—' }}</span>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-bold text-nowrap">{{ number_format($order->total, 0, ',', ' ') }} GNF</td>
                        <td>
                            @if($order->ordonnance)
                                <span class="badge bg-info badge-status">📎 Disponible</span>
                            @else
                                <span class="badge bg-secondary badge-status">—</span>
                            @endif
                        </td>
                        <td>
                            @if($order->review)
                                <span class="badge bg-warning text-dark badge-status">⭐ {{ $order->review->rating }}/5</span>
                                <div class="small text-muted">{{ Str::limit($order->review->comment, 60) }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- LIVREUR cell: montre soit le livreur assigné (badge vert) soit "Aucun" --}}
                        <td>
                            @if($order->livreur)
                                <span class="badge bg-success badge-status">✅ Assignée à {{ $order->livreur->name }}</span>
                                @if($order->pivot?->assigned_at ?? false)
                                  <div class="small text-muted">le {{ optional($order->pivot->assigned_at)->format('d/m H:i') }}</div>
                                @elseif($order->updated_at)
                                  <div class="small text-muted">depuis {{ $order->updated_at->diffForHumans() }}</div>
                                @endif
                            @else
                                <span class="badge bg-warning badge-status">Aucun</span>
                            @endif
                        </td>

                        {{-- ACTIONS: si commande déjà assignée => n'affiche plus le form d'assignation --}}
                        <td class="d-flex flex-wrap align-items-center gap-2">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary action-btn">🔍 Détails</a>
                           
                            @if(!$order->livreur)
                                {{-- Affiche le formulaire d'assignation seulement s'il n'y a PAS de livreur --}}
                                <form action="{{ route('employe.orders.assign', $order) }}" method="POST" class="d-inline-flex align-items-center mt-1 mt-md-0">
                                    @csrf
                                    @method('PUT')
                                    <select name="livreur_id" class="form-select form-select-sm me-1" required>
                                        <option value="">-- Choisir --</option>
                                        @foreach($livreurs as $livreur)
                                            <option value="{{ $livreur->id }}">{{ $livreur->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success">✅ Assigner</button>
                                </form>
                            @else
                                {{-- Bouton disabled pour signifier que c'est déjà assigné --}}
                                <button class="btn btn-sm btn-outline-success" disabled>✔ Assignée</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">Aucune commande à assigner.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $orders->links() }}
    </div>

    {{-- ============================ MOBILE (< md) ============================ --}}
    <div class="d-md-none">
        @forelse($orders as $order)
        <div class="card mobile-card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="mb-2 fw-bold">{{ $order->client->name }}</div>
                        <div class="small text-muted">🏪 {{ $order->shop->name }}</div>
                    </div>

                    <div>
                        @if($order->livreur)
                            <span class="badge bg-success">✅ Assignée</span>
                        @else
                            <span class="badge bg-warning">Aucun</span>
                        @endif
                    </div>
                </div>

                @if($order->items->count())
                    @php $product = $order->items->first()->product; @endphp
                    <div class="d-flex align-items-center gap-2 my-2">
                        @if($product && $product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" class="product-thumb border" alt="{{ $product->name }}">
                        @endif
                        <div>
                            <div>{{ $product?->name ?? '—' }}</div>
                            <div class="small text-muted">Qté: {{ $order->items->first()->quantity ?? 1 }}</div>
                        </div>
                    </div>
                @endif

                <div class="fw-bold fs-5 text-success mb-2">{{ number_format($order->total, 0, ',', ' ') }} GNF</div>

                <div class="d-flex gap-2 mb-2">
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm flex-fill">🔍 Détails</a>

                    @if(!$order->livreur)
                        {{-- Assign form shown only when not assigned --}}
                        <form action="{{ route('employe.orders.assign', $order) }}" method="POST" class="d-flex flex-fill">
                            @csrf @method('PUT')
                            <select name="livreur_id" class="form-select form-select-sm me-2" required>
                                <option value="">Choisir</option>
                                @foreach($livreurs as $liv)
                                    <option value="{{ $liv->id }}">{{ $liv->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-success btn-sm">✅</button>
                        </form>
                    @else
                        <button class="btn btn-success btn-sm flex-fill" disabled>✅ Assignée à {{ $order->livreur->name }}</button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted">Aucune commande à assigner.</div>
        @endforelse

        {{ $orders->links() }}
    </div>

</div>
@endsection
