@extends('layouts.app')

@push('styles')
<style>
  .product-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: .5rem; }
  @media (max-width: 767.98px) {
    .product-thumb { width: 48px; height: 48px; }
  }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">📦 Commandes reçues</h2>
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
          <th>Produits</th>
          <th>Total</th>
          <th>Status</th>
          <th>Ordonnance</th> <!-- ✅ colonne ajoutée -->
          <th class="d-none d-lg-table-cell">Date</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td>{{ $loop->iteration }}</td>

            <td>
              <strong>{{ $order->client->name }}</strong><br>
              <small class="text-muted">{{ $order->client->email }}</small>
            </td>

            {{-- Produits --}}
            <td>
              @foreach($order->items as $item)
                <div class="d-flex align-items-center gap-2 mb-2">
                  @if($item->product && $item->product->image)
                    <img src="{{ asset('storage/'.$item->product->image) }}"
                         alt="{{ $item->product->name }}"
                         class="product-thumb border">
                  @endif
                  <div class="small">
                    <div class="fw-semibold">{{ $item->product->name ?? '—' }}</div>
                    <div class="text-muted">
                      Qté: {{ $item->quantity }}
                      • PU: {{ number_format($item->price, 0, ',', ' ') }} GNF
                    </div>
                  </div>
                </div>
              @endforeach
            </td>

            <td class="fw-bold fs-6">
              {{ number_format($order->total, 0, ',', ' ') }} GNF
            </td>

            <td>
              @switch($order->status)
                @case('pending') <span class="badge text-bg-warning">⏳ En attente</span> @break
                @case('confirmed') <span class="badge text-bg-info">📦 Confirmée</span> @break
                @case('delivering') <span class="badge text-bg-primary">🚚 En livraison</span> @break
                @case('delivered') <span class="badge text-bg-success">✅ Livrée</span> @break
                @default <span class="badge text-bg-danger">❌ Annulée</span>
              @endswitch
            </td>

            {{-- ✅ Ordonnance uniquement si pharmacie --}}
            <td>
              @if($order->shop && strtolower($order->shop->type) === 'pharmacie' && $order->ordonnance)
                <a href="{{ asset('storage/'.$order->ordonnance) }}" target="_blank" class="btn btn-sm btn-info">
                  📎 Voir
                </a>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>

            <td class="d-none d-lg-table-cell">
              {{ $order->created_at->format('d/m/Y H:i') }}
            </td>

            <td>
              @if($order->status == 'pending')
                <form action="{{ route('orders.confirm', $order) }}" method="POST" class="d-inline">
                  @csrf @method('PUT')
                  <button type="submit" class="btn btn-sm btn-success">✅ Confirmer</button>
                </form>
                <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                  @csrf @method('PUT')
                  <button type="submit" class="btn btn-sm btn-danger">❌ Annuler</button>
                </form>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center">Aucune commande reçue.</td>
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
        {{-- En-tête --}}
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <div class="fw-semibold">{{ $order->client->name }}</div>
            <div class="text-muted small">{{ $order->client->email }}</div>
          </div>
          <div>
            @switch($order->status)
              @case('pending') <span class="badge text-bg-warning">⏳</span> @break
              @case('confirmed') <span class="badge text-bg-info">📦</span> @break
              @case('delivering') <span class="badge text-bg-primary">🚚</span> @break
              @case('delivered') <span class="badge text-bg-success">✅</span> @break
              @default <span class="badge text-bg-danger">❌</span>
            @endswitch
          </div>
        </div>

        {{-- Produits --}}
        <div class="mb-2">
          @foreach($order->items as $item)
            <div class="d-flex align-items-center gap-2 mb-2">
              @if($item->product && $item->product->image)
                <img src="{{ asset('storage/'.$item->product->image) }}"
                     alt="{{ $item->product->name }}"
                     class="product-thumb border">
              @endif
              <div class="small">
                <div class="fw-semibold">{{ $item->product->name ?? '—' }}</div>
                <div class="text-muted">
                  Qté: {{ $item->quantity }}
                  • PU: {{ number_format($item->price, 0, ',', ' ') }} GNF
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- ✅ Ordonnance pharmacie seulement --}}
        @if($order->shop && strtolower($order->shop->type) === 'pharmacie' && $order->ordonnance)
          <div class="mb-2">
            <a href="{{ asset('storage/'.$order->ordonnance) }}" target="_blank" class="btn btn-sm btn-info">
              📎 Voir ordonnance
            </a>
          </div>
        @endif

        {{-- Total + Date --}}
        <div class="d-flex justify-content-between align-items-center">
          <div class="fw-bold fs-5">
            {{ number_format($order->total, 0, ',', ' ') }} GNF
          </div>
          <div class="text-muted small">
            {{ $order->created_at->format('d/m/Y H:i') }}
          </div>
        </div>

        {{-- Actions --}}
        <div class="mt-3 d-flex gap-2">
          @if($order->status == 'pending')
            <form action="{{ route('orders.confirm', $order) }}" method="POST" class="flex-fill">
              @csrf @method('PUT')
              <button type="submit" class="btn btn-success btn-sm w-100">✅ Confirmer</button>
            </form>
            <form action="{{ route('orders.cancel', $order) }}" method="POST" class="flex-fill">
              @csrf @method('PUT')
              <button type="submit" class="btn btn-danger btn-sm w-100">❌ Annuler</button>
            </form>
          @else
            <button class="btn btn-light btn-sm w-100" disabled>—</button>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="text-center text-muted">Aucune commande reçue.</div>
  @endforelse

  {{ $orders->links() }}
</div>
@endsection
