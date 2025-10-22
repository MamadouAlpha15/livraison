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
    <h2 class="mb-0">🚚 Mes commandes à livrer</h2>
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
          <th>Produit</th>
          <th>Boutique</th>
          <th>Total</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td>{{ $loop->iteration }}</td>

            {{-- Infos client --}}
            <td>
              <strong>{{ $order->client->name }}</strong><br>
              📞 {{ $order->client->phone ?? '—' }}<br>
              📍 {{ $order->client->address ?? 'Adresse non renseignée' }}
            </td>

            {{-- Produit principal --}}
            <td>
              @if($order->items->count() > 0)
                @php $item = $order->items->first(); @endphp
                <div class="d-flex align-items-center gap-2">
                  @if($item->product && $item->product->image)
                    <img src="{{ asset('storage/'.$item->product->image) }}"
                         alt="{{ $item->product->name }}"
                         class="product-thumb border">
                  @endif
                  <div class="small">
                    <div class="fw-semibold">{{ $item->product->name ?? 'Produit supprimé' }}</div>
                    <div class="text-muted">Qté: {{ $item->quantity }}</div>
                  </div>
                </div>
              @else
                <span class="text-muted">Aucun produit lié</span>
              @endif
            </td>

            <td>{{ $order->shop->name }}</td>

            <td class="fw-bold text-nowrap">
              {{ number_format($order->total, 0, ',', ' ') }} GNF
            </td>

            <td>
              @switch($order->status)
                @case('confirmed')
                  <span class="badge text-bg-warning">📦 Confirmée</span>
                  @break
                @case('delivering')
                  <span class="badge text-bg-primary">🚚 En livraison</span>
                  @break
                @case('delivered')
                  <span class="badge text-bg-success">✅ Livrée</span>
                  @break
              @endswitch
            </td>

            <td>
              @if($order->status == 'confirmed')
                <form action="{{ route('livreur.orders.start', $order) }}" method="POST" class="d-inline">
                  @csrf @method('PUT')
                  <button type="submit" class="btn btn-sm btn-info">🚚 Commencer</button>
                </form>
              @elseif($order->status == 'delivering')
                <form action="{{ route('livreur.orders.complete', $order) }}" method="POST" class="d-inline">
                  @csrf @method('PUT')
                  <button type="submit" class="btn btn-sm btn-success">✅ Terminer</button>
                </form>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center">Aucune commande assignée.</td>
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
            <div class="text-muted small">
              📞 {{ $order->client->phone ?? '—' }}<br>
              📍 {{ $order->client->address ?? '—' }}
            </div>
          </div>
          <div>
            @switch($order->status)
              @case('confirmed')
                <span class="badge text-bg-warning">📦</span>
                @break
              @case('delivering')
                <span class="badge text-bg-primary">🚚</span>
                @break
              @case('delivered')
                <span class="badge text-bg-success">✅</span>
                @break
            @endswitch
          </div>
        </div>

        {{-- Produit --}}
        @if($order->items->count() > 0)
          @php $item = $order->items->first(); @endphp
          <div class="d-flex align-items-center gap-2 mb-2">
            @if($item->product && $item->product->image)
              <img src="{{ asset('storage/'.$item->product->image) }}"
                   alt="{{ $item->product->name }}"
                   class="product-thumb border">
            @endif
            <div class="small">
              <div class="fw-semibold">{{ $item->product->name ?? 'Produit supprimé' }}</div>
              <div class="text-muted">Qté: {{ $item->quantity }}</div>
            </div>
          </div>
        @endif

        {{-- Total --}}
        <div class="fw-bold fs-5 text-success mb-2">
          {{ number_format($order->total, 0, ',', ' ') }} GNF
        </div>

        {{-- Actions --}}
        <div class="d-flex gap-2">
          @if($order->status == 'confirmed')
            <form action="{{ route('livreur.orders.start', $order) }}" method="POST" class="flex-fill">
              @csrf @method('PUT')
              <button type="submit" class="btn btn-info btn-sm w-100">🚚 Commencer</button>
            </form>
          @elseif($order->status == 'delivering')
            <form action="{{ route('livreur.orders.complete', $order) }}" method="POST" class="flex-fill">
              @csrf @method('PUT')
              <button type="submit" class="btn btn-success btn-sm w-100">✅ Terminer</button>
            </form>
          @else
            <button class="btn btn-light btn-sm w-100" disabled>—</button>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="text-center text-muted">Aucune commande assignée.</div>
  @endforelse

  {{ $orders->links() }}
</div>
@endsection
