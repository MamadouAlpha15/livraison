@extends('layouts.app')

@push('styles')
<style>
  .product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: .5rem; }
  @media (max-width: 767.98px) { .product-thumb { width: 40px; height: 40px; } }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">📦 Commandes à assigner</h2>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- ======================== DESKTOP / TABLET (md+) ======================== --}}
<div class="d-none d-md-block">
  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-primary">
        <tr>
          <th>#</th>
          <th>Client</th>
          <th>Boutique</th>
          <th>Produit</th>
          <th>Total</th>
          <th>Ordonnance</th>
          <th>Avis</th>
          <th>Livreur assigné</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $order->client->name }}</td>
            <td>{{ $order->shop->name }}</td>
            
            {{-- Produit principal --}}
            <td>
              @if($order->items->count())
                @php $product = $order->items->first()->product; @endphp
                <div class="d-flex align-items-center gap-2">
                  @if($product && $product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" 
                         alt="{{ $product->name }}" 
                         class="product-thumb border">
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
                <a href="{{ asset('storage/'.$order->ordonnance) }}" target="_blank" class="btn btn-sm btn-info">📎 Voir</a>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>

            <td>
              @if($order->review)
                ⭐ {{ $order->review->rating }}/5 <br>
                <small>{{ $order->review->comment }}</small>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>

            <td>
              @if($order->livreur)
                🚚 {{ $order->livreur->name }}
              @else
                <span class="badge bg-warning">Aucun</span>
              @endif
            </td>

            <td>
              <form action="{{ route('employe.orders.assign', $order) }}" method="POST" class="d-flex">
                @csrf
                @method('PUT')
                <select name="livreur_id" class="form-select form-select-sm me-2" required>
                  <option value="">-- Choisir un livreur --</option>
                  @foreach($livreurs as $livreur)
                    <option value="{{ $livreur->id }}" {{ $order->livreur_id == $livreur->id ? 'selected' : '' }}>
                      {{ $livreur->name }}
                    </option>
                  @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-success">✅ Assigner</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center">Aucune commande à assigner.</td>
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
    <div class="card mb-3 shadow-sm">
      <div class="card-body">
        {{-- Client + Boutique --}}
        <div class="mb-2">
          <strong>{{ $order->client->name }}</strong> <br>
          <span class="text-muted small">🏪 {{ $order->shop->name }}</span>
        </div>

        {{-- Produit --}}
        @if($order->items->count())
          @php $product = $order->items->first()->product; @endphp
          <div class="d-flex align-items-center gap-2 mb-2">
            @if($product && $product->image)
              <img src="{{ asset('storage/'.$product->image) }}" 
                   alt="{{ $product->name }}" 
                   class="product-thumb border">
            @endif
            <span>{{ $product?->name ?? '—' }}</span>
          </div>
        @endif

        {{-- Total --}}
        <div class="fw-bold fs-5 text-success mb-2">
          {{ number_format($order->total, 0, ',', ' ') }} GNF
        </div>

        {{-- Ordonnance (si dispo) --}}
        @if($order->ordonnance)
          <a href="{{ asset('storage/'.$order->ordonnance) }}" target="_blank" class="btn btn-sm btn-outline-info mb-2 w-100">
            📎 Voir ordonnance
          </a>
        @endif

        {{-- Livreurs + Action --}}
        <form action="{{ route('employe.orders.assign', $order) }}" method="POST" class="d-flex gap-2">
          @csrf
          @method('PUT')
          <select name="livreur_id" class="form-select form-select-sm" required>
            <option value="">-- Choisir un livreur --</option>
            @foreach($livreurs as $livreur)
              <option value="{{ $livreur->id }}" {{ $order->livreur_id == $livreur->id ? 'selected' : '' }}>
                {{ $livreur->name }}
              </option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-success btn-sm">✅</button>
        </form>
      </div>
    </div>
  @empty
    <div class="text-center text-muted">Aucune commande à assigner.</div>
  @endforelse

  {{ $orders->links() }}
</div>
@endsection
