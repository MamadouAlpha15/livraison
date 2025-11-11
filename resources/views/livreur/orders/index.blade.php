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

            {{-- ---------------- STATUS BADGE ---------------- --}}
            <td>
              @php
                // Normaliser le statut pour comparaisons (tout en minuscules)
                $s = strtolower($order->status ?? '');
              @endphp

              {{-- On accepte les versions FR et EN pour compatibilité --}}
              @if(in_array($s, ['confirmed', 'confirmée', 'confirmée', 'confirmée', 'confirmé'])) 
                <span class="badge text-bg-warning">📦 Confirmée</span>

              @elseif(in_array($s, ['delivering', 'en_livraison', 'en-livraison']))
                <span class="badge text-bg-primary">🚚 En livraison</span>

              @elseif(in_array($s, ['delivered', 'livrée', 'livree']))
                <span class="badge text-bg-success">✅ Livrée</span>

              @elseif(in_array($s, ['cancelled','annulée','annulee','annulé']))
                <span class="badge text-bg-secondary">⚠️ Annulée</span>

              @else
                {{-- Valeur inconnue — affiche brute pour debug --}}
                <span class="badge bg-light text-dark"># {{ $order->status }}</span>
              @endif
            </td>

            {{-- ---------------- ACTIONS ---------------- --}}
            <td>
              {{-- Commencer si commande confirmée (anglais ou français) --}}
              @if(in_array($s, ['confirmed', 'confirmée', 'confirmé']))
                <form action="{{ route('livreur.orders.start', $order) }}" method="POST" class="d-inline">
                  @csrf @method('PUT')
                  <button type="submit" class="btn btn-sm btn-info">🚚 Commencer</button>
                </form>

              {{-- Terminer si en cours de livraison --}}
              @elseif(in_array($s, ['delivering', 'en_livraison', 'en-livraison']))
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
    @php $s = strtolower($order->status ?? ''); @endphp

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
            @if(in_array($s, ['confirmed', 'confirmée', 'confirmé']))
              <span class="badge text-bg-warning">📦</span>
            @elseif(in_array($s, ['delivering', 'en_livraison', 'en-livraison']))
              <span class="badge text-bg-primary">🚚</span>
            @elseif(in_array($s, ['delivered', 'livrée', 'livree']))
              <span class="badge text-bg-success">✅</span>
            @else
              <span class="badge bg-light text-dark">#</span>
            @endif
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
          @if(in_array($s, ['confirmed', 'confirmée', 'confirmé']))
            <form action="{{ route('livreur.orders.start', $order) }}" method="POST" class="flex-fill">
              @csrf @method('PUT')
              <button type="submit" class="btn btn-info btn-sm w-100">🚚 Commencer</button>
            </form>
          @elseif(in_array($s, ['delivering', 'en_livraison', 'en-livraison']))
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

@push('scripts')
<script>
const gpsWatchers = {};

function startTracking(orderId) {
  if (!('geolocation' in navigator)) {
    return alert("Géolocalisation non supportée.");
  }
  if (gpsWatchers[orderId]) return; // déjà actif

  // HTTPS recommandé en prod
  if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
    alert("Le suivi GPS nécessite HTTPS en production.");
  }

  const opts = { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 };
  const watchId = navigator.geolocation.watchPosition(async pos => {
    try {
      await fetch(`{{ url('/orders') }}/${orderId}/position`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude })
      });
    } catch(e) {
      console.warn('Erreur envoi position:', e);
    }
  }, err => {
    console.warn('GPS error:', err);
    alert("Permission ou signal GPS indisponible.");
    stopTracking(orderId);
  }, opts);

  gpsWatchers[orderId] = watchId;
}

function stopTracking(orderId) {
  const id = gpsWatchers[orderId];
  if (id) {
    navigator.geolocation.clearWatch(id);
    delete gpsWatchers[orderId];
  }
}

document.addEventListener('DOMContentLoaded', () => {
  @if(session('autostart_gps_order_id'))
    startTracking({{ session('autostart_gps_order_id') }});
  @endif

  // lancer pour toutes les commandes en livraison (FR/EN)
  @foreach($orders as $o)
    @php $s = strtolower($o->status ?? '') @endphp
    @if(in_array($s, ['delivering','en_livraison','en-livraison']))
      startTracking({{ $o->id }});
    @endif
  @endforeach
});
</script>
@endpush
