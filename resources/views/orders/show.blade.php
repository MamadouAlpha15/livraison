{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app') {{-- ton layout principal (modifie si tu utilises un autre nom de layout) --}}

@section('title', 'Détails de la commande')

@section('content')
<div class="container py-4">

    {{-- Titre + résumé de la commande --}}
    <div class="card mb-3">
        <div class="card-header">
            <h4>Commande #{{ $order->id }}</h4>
        </div>
        <div class="card-body">
            <p><strong>Client :</strong> {{ $order->client->name ?? '—' }}</p>
            <p><strong>Boutique :</strong> {{ $order->shop->name ?? '—' }}</p>
            <p><strong>Status :</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Total :</strong> {{ number_format($order->total, 0, ',', ' ') }} GNF</p>
        </div>
    </div>

    {{-- Suivi en direct sur la carte --}}
    <div class="card">
        <div class="card-header">
            📍 Suivi de livraison en temps réel
        </div>
        <div class="card-body">
            <div id="map" style="height: 380px; border-radius: 10px;"></div>
            <p class="mt-2 text-muted" id="lastUpdate">Dernière mise à jour : —</p>
        </div>
    </div>
</div>
@endsection


{{-- ===== STYLES LEAFLET ===== --}}
@push('styles')
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""
/>
@endpush


{{-- ===== SCRIPT LEAFLET ===== --}}
@push('scripts')
@push('scripts')
<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin=""
></script>

<script>
(function() {
  // Position par défaut (Conakry) si pas encore de coordonnées
  let lat = {{ $order->current_lat ?? '9.6412' }};
  let lng = {{ $order->current_lng ?? '-13.5784' }};

  const mapEl  = document.getElementById('map');
  const lastEl = document.getElementById('lastUpdate');

  // 1) Carte
  const map = L.map(mapEl).setView([lat, lng], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // 2) Marqueur + trail (ligne du trajet)
  const marker = L.marker([lat, lng]).addTo(map);
  const pathCoords = [[lat, lng]];
  const pathLine = L.polyline(pathCoords, { weight: 4, opacity: 0.7 }).addTo(map);

  // 3) Petite fonction d’animation entre 2 points (~0.8s)
  function animateTo(from, to, onUpdate, duration = 800) {
    const start = performance.now();
    function frame(now) {
      const t = Math.min(1, (now - start) / duration);
      const cur = [ from[0] + (to[0]-from[0]) * t, from[1] + (to[1]-from[1]) * t ];
      onUpdate(cur);
      if (t < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  // 4) Récupérer la position serveur et animer si ça bouge
  async function refreshPosition() {
    try {
      const resp = await fetch("{{ route('orders.position.show', $order) }}", {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!resp.ok) return;
      const data = await resp.json();

      // Positions valides ?
      if (data.lat != null && data.lng != null) {
        const newLat = parseFloat(data.lat);
        const newLng = parseFloat(data.lng);

        // Si le livreur s’est déplacé d’au moins ~15m (seuil très petit)
        const dLat = newLat - lat, dLng = newLng - lng;
        const movedEnough = Math.abs(dLat) + Math.abs(dLng) > 0.00015; // ~15-20m
        if (movedEnough) {
          const from = [lat, lng];
          const to   = [newLat, newLng];

          // Animer le marqueur
          animateTo(from, to, (pt) => { marker.setLatLng(pt); });

          // Mettre à jour le trail & recadrer un peu
          pathCoords.push([newLat, newLng]);
          pathLine.setLatLngs(pathCoords);

          // Recentrer doucement si on s’éloigne (évite les recadrages trop fréquents)
          const far = Math.abs(dLat) + Math.abs(dLng) > 0.0005; // ~50-70m
          if (far) map.panTo([newLat, newLng], { animate: true, duration: 0.8 });

          lat = newLat; lng = newLng;
        }
      }

      if (data.updated) {
        const d = new Date(data.updated);
        lastEl.textContent = 'Dernière mise à jour : ' + d.toLocaleString();
      }
    } catch (e) {
      console.warn('Erreur maj position :', e);
    }
  }

  // 5) Lancer + poll toutes les 3s
  refreshPosition();
  setInterval(refreshPosition, 3000);
})();
</script>
@endpush

@endpush
