@extends('layouts.app')

@push('styles')
<style>
/* --- styles généraux du dashboard (préservés) --- */
.header {
  background: linear-gradient(90deg, rgba(2,132,199,0.06), rgba(14,77,146,0.03));
  padding: 1.2rem;
  border-radius: .6rem;
}
.driver-photo { width:64px; height:64px; object-fit:cover; border-radius:10px; }
.card-soft { box-shadow: 0 8px 24px rgba(4,40,90,.06); border-radius:10px; }

/* --- Offcanvas / chat styles --- */
.offcanvas-fullscreen-md.offcanvas-bottom { position: fixed; top: 0; right: 0; bottom: 0; left: 0; display: flex; }
.offcanvas-fullscreen-md .offcanvas-body { padding: 0; }
.msg-row { display:flex; margin-bottom:0.8rem; }
.msg-bubble { padding:.6rem .8rem; border-radius:12px; max-width:78%; word-break:break-word; }
.msg-left { background:#fff; border:1px solid #eef2f7; border-radius:12px 12px 12px 6px; }
.msg-right { background:#0d6efd; color:#fff; border-radius:12px 12px 6px 12px; }
.conv-time { font-size:0.75rem; color:#8a94a6; margin-top:.35rem; }
.request-item { cursor:pointer; }
.list-group-item-action:focus, .list-group-item-action:hover { background:#f8f9ff; }
.offcanvas .list-group { max-height: calc(100vh - 170px); overflow:auto; }

/* Responsive tweaks */
@media (max-width:767.98px) {
  .offcanvas-fullscreen-md.offcanvas-bottom { position: fixed; top: 0; left: 0; right: 0; bottom: 0; }
  .offcanvas .list-group { max-height: 180px; }
}
</style>
@endpush

@section('content')
<div class="container py-4">

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Header premium --}}
    <div class="rounded-3 p-4 mb-4 header text-dark">
        <div class="d-flex align-items-center justify-content-between flex-column flex-md-row">
            <div>
                <h3 class="mb-0">{{ $company->name ?? 'Mon entreprise' }} — Dashboard</h3>
                <div class="small text-muted">Gérez vos livreurs et discutez avec les boutiques</div>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <button class="btn btn-outline-light btn-sm shadow-sm" onclick="location.reload()">⟳ Rafraîchir</button>
                @if($company && $company->approved)
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addDriverModal">➕ Ajouter un livreur</button>
                @endif
                {{-- BOUTON PRINCIPAL: Voir les demandes --}}
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#requestsOffcanvas" aria-controls="requestsOffcanvas">
                    💬 Voir les demandes
                </button>
            </div>
        </div>
    </div>

    {{-- Top cards / stats (reste inchangé) --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-soft p-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $company->image ? asset('storage/'.$company->image) : asset('images/placeholder-company.png') }}" 
                         alt="" style="width:80px; height:80px; object-fit:cover; border-radius:8px;">
                    <div>
                        <div class="fw-semibold">{{ $company->name ?? '—' }}</div>
                        <div class="small text-muted">Commission: <strong>{{ number_format($company->commission_percent ?? 0,2) }}%</strong></div>
                        <div class="small text-muted">Livreurs: <strong>{{ $drivers->total() ?? 0 }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3">
                <h6 class="mb-3">Statistiques rapides</h6>
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="small text-muted">Commandes</div>
                        <div class="fw-semibold">—</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="small text-muted">Revenu (estim.)</div>
                        <div class="fw-semibold">—</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="small text-muted">Livreurs actifs</div>
                        <div class="fw-semibold">{{ $drivers->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste livreurs --}}
    <div class="card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Mes livreurs</h5>
            <div class="small text-muted">{{ $drivers->total() ?? 0 }} résultats</div>
        </div>

        <div class="row g-3">
            @forelse($drivers as $driver)
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-3 p-3 border rounded">
                        <img src="{{ $driver->photo ? asset('storage/'.$driver->photo) : asset('images/avatar-placeholder.png') }}" 
                             alt="" class="driver-photo">
                        <div class="flex-fill">
                            <div class="fw-semibold">{{ $driver->name }}</div>
                            <div class="small text-muted">{{ $driver->phone ?? '—' }} • {{ $driver->vehicle ?? 'Véhicule non défini' }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('company.drivers.update', $driver) }}" class="btn btn-outline-secondary btn-sm">✎ Modifier</a>
                            <form action="{{ route('company.drivers.destroy', $driver) }}" method="POST" onsubmit="return confirm('Supprimer ce livreur ?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center text-muted py-4">Aucun livreur ajouté — commencez par en ajouter un.</div>
                </div>
            @endforelse
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $drivers->links() }}
        </div>
    </div>

    {{-- Modal Ajouter un livreur --}}
    @if($company && $company->approved)
    <div class="modal fade" id="addDriverModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form action="{{ route('company.drivers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title">Ajouter un livreur</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-2">
                <label class="form-label">Nom *</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="mb-2">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" accept="image/*" class="form-control">
              </div>
              <div class="mb-2">
                <label class="form-label">Véhicule (optionnel)</label>
                <input type="text" name="vehicle" class="form-control">
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Annuler</button>
              <button class="btn btn-success" type="submit">Ajouter le livreur</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif

</div> {{-- container --}}

{{-- ---------------------------
     OFFCANVAS: Voir les demandes + Chat (sélection de conversation)
   --------------------------- --}}
<div class="offcanvas offcanvas-bottom offcanvas-fullscreen-md" tabindex="-1" id="requestsOffcanvas" aria-labelledby="requestsOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 id="requestsOffcanvasLabel" class="mb-0">Demandes de livraison</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body d-flex flex-column p-0" style="min-height:60vh;">
    <div class="row g-0 flex-fill" style="height:100%;">
      {{-- Left: liste boutiques --}}
      <div class="col-12 col-md-4 border-end" style="max-height:100%; overflow:auto;">
        <div class="p-3">
          <div class="small text-muted">Sélectionnez une demande pour voir la conversation</div>
        </div>

        <div id="requestsList" class="list-group list-group-flush">
          {{-- Itération fournie côté controller en $chattedShops ou $incomingRequests --}}
          @foreach($chattedShops ?? $incomingRequests ?? [] as $shop)
            <button class="list-group-item list-group-item-action d-flex align-items-start gap-2 request-item" 
                    data-shop-id="{{ $shop->id }}" data-shop-name="{{ $shop->name }}">
                <div>
                    <div class="fw-semibold">{{ $shop->name }}</div>
                    <div class="small text-muted">{{ $shop->email ?? '' }} • ID: {{ $shop->id }}</div>
                </div>
                <div class="ms-auto text-end">
                    <small class="text-muted" id="unread-{{ $shop->id }}">
                        @if(isset($shop->unread) && $shop->unread) <span class="badge bg-danger">{{ $shop->unread }}</span> @endif
                    </small>
                </div>
            </button>
          @endforeach

          @if((empty($chattedShops) && empty($incomingRequests)) || (count($chattedShops ?? []) + count($incomingRequests ?? []) === 0))
            <div class="p-3 text-center text-muted small">Aucune demande pour le moment.</div>
          @endif
        </div>

        <div class="p-3 border-top">
            <label class="form-label small mb-1">Ouvrir manuellement une conversation (ID boutique)</label>
            <div class="d-flex gap-2">
                <input id="chatShopManual" class="form-control" placeholder="ID boutique (ex: 12)"/>
                <button id="chatOpenManual" class="btn btn-outline-primary">Ouvrir</button>
            </div>
        </div>
      </div>

      {{-- Right: conversation --}}
      <div class="col-12 col-md-8 d-flex flex-column">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
          <div>
            <div id="convShopName" class="fw-semibold">— Aucune conversation sélectionnée —</div>
            <div id="convShopMeta" class="small text-muted">Sélectionnez une demande à gauche.</div>
          </div>
          <div>
            <button id="btnRefreshConv" class="btn btn-sm btn-outline-secondary">↻ Rafraîchir</button>
          </div>
        </div>

        <div id="convMessages" class="flex-fill p-3" style="overflow:auto; background:#fafbfd;">
          <div class="text-center text-muted">Sélectionnez une conversation pour afficher les messages.</div>
        </div>

        <div class="p-3 border-top bg-white">
          <form id="convForm" class="d-flex gap-2" onsubmit="return false;">
            <input type="hidden" id="convCompanyId" value="{{ $company->id ?? '' }}">
            <input type="hidden" id="convShopId" value="">
            <textarea id="convInput" class="form-control" rows="2" placeholder="Écrivez une réponse..." required></textarea>
            <button id="convSendBtn" class="btn btn-primary">Envoyer</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Elements
  const convCompanyId = document.getElementById('convCompanyId').value;
  const requestsList = document.getElementById('requestsList');
  const convMessages = document.getElementById('convMessages');
  const convShopName = document.getElementById('convShopName');
  const convShopMeta = document.getElementById('convShopMeta');
  const convShopIdInput = document.getElementById('convShopId');
  const convInput = document.getElementById('convInput');
  const convSendBtn = document.getElementById('convSendBtn');
  const btnRefreshConv = document.getElementById('btnRefreshConv');

  // Manual open elements (outside offcanvas too)
  const manualInput = document.getElementById('chatShopManual');
  const manualBtn = document.getElementById('chatOpenManual');

  if (!convCompanyId) return; // sécurité

  let currentShopId = null;
  let pollTimer = null;
  let lastTimestamp = null;
  let isLoading = false;

  function buildMessagesUrl(shopId, after = null) {
    const base = `${window.location.origin}/company/${encodeURIComponent(convCompanyId)}/chat/messages`;
    const params = new URLSearchParams();
    if (shopId) params.set('shop', shopId);
    if (after) params.set('after', after);
    return base + (params.toString() ? '?' + params.toString() : '');
  }
  function buildSendUrl() {
    return `${window.location.origin}/company/${encodeURIComponent(convCompanyId)}/chat/send`;
  }

  function markActiveRequest(button) {
    requestsList.querySelectorAll('.request-item').forEach(b => b.classList.remove('active'));
    if (button) button.classList.add('active');
  }

  // render single message
  function appendMessage(msg, scroll = true) {
    // msg: { id, body, from_type, sender_name, created_at }
    const wrapper = document.createElement('div');
    wrapper.className = 'msg-row ' + ((msg.from_type === 'company' || msg.from === 'company') ? 'justify-content-end' : 'justify-content-start');

    const box = document.createElement('div');
    box.className = 'msg-bubble ' + ((msg.from_type === 'company' || msg.from === 'company') ? 'msg-right' : 'msg-left');

    const nameEl = document.createElement('div');
    nameEl.className = 'small fw-semibold';
    nameEl.style.marginBottom = '0.25rem';
    nameEl.textContent = msg.sender_name ?? (msg.from_type === 'company' ? 'Vous' : 'Boutique');

    const bodyEl = document.createElement('div');
    bodyEl.textContent = msg.body;

    const timeEl = document.createElement('div');
    timeEl.className = 'conv-time';
    timeEl.textContent = new Date(msg.created_at).toLocaleString();

    box.appendChild(nameEl);
    box.appendChild(bodyEl);
    box.appendChild(timeEl);
    wrapper.appendChild(box);

    convMessages.appendChild(wrapper);
    if (scroll) convMessages.scrollTop = convMessages.scrollHeight;
  }

  function clearConversation() {
    convMessages.innerHTML = '<div class="text-center text-muted">Aucune conversation sélectionnée.</div>';
    convShopName.textContent = '— Aucune conversation sélectionnée —';
    convShopMeta.textContent = 'Sélectionnez une demande à gauche.';
    convShopIdInput.value = '';
    currentShopId = null;
    lastTimestamp = null;
    stopPolling();
  }

  async function loadConversation(shopId, shopName) {
    if (!shopId) return;
    convMessages.innerHTML = '<div class="text-center py-4 text-muted">Chargement des messages…</div>';
    convShopName.textContent = shopName || ('Boutique #' + shopId);
    convShopMeta.textContent = 'ID: ' + shopId;
    convShopIdInput.value = shopId;
    currentShopId = shopId;
    lastTimestamp = null;

    await fetchAndRender(true);
    startPolling();
  }

  async function fetchAndRender(initial = false) {
    if (!currentShopId || isLoading) return;
    isLoading = true;
    try {
      const url = buildMessagesUrl(currentShopId, initial ? null : lastTimestamp);
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Network');
      const json = await res.json();
      // expected { messages: [...], last: 'timestamp' }
      if (initial) convMessages.innerHTML = '';
      if (Array.isArray(json.messages) && json.messages.length) {
        json.messages.forEach(m => appendMessage(m));
        if (json.last) lastTimestamp = json.last;
      } else if (initial) {
        convMessages.innerHTML = '<div class="text-center text-muted py-4">Aucune conversation.</div>';
      }
      convMessages.scrollTop = convMessages.scrollHeight;
    } catch (e) {
      console.error('fetch conv', e);
    } finally {
      isLoading = false;
    }
  }

  // Polling loop
  function startPolling() {
    stopPolling();
    pollTimer = setInterval(() => {
      fetchAndRender(false);
    }, 3000);
  }
  function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
  }

  // Send message
  async function sendMessage() {
    if (!currentShopId) { alert('Sélectionnez d’abord une demande.'); return; }
    const text = convInput.value.trim();
    if (!text) return;
    convSendBtn.disabled = true;
    try {
      const res = await fetch(buildSendUrl(), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message: text, shop_id: currentShopId })
      });
      if (!res.ok) {
        const txt = await res.text();
        throw new Error(txt || 'send failed');
      }
      const data = await res.json();
      if (data.message) {
        appendMessage(data.message);
      } else {
        await fetchAndRender(true);
      }
      convInput.value = '';
      convInput.focus();
      convMessages.scrollTop = convMessages.scrollHeight;
    } catch (e) {
      console.error('send', e);
      alert('Erreur envoi message.');
    } finally {
      convSendBtn.disabled = false;
    }
  }

  // Attach clicks on request items (delegation)
  requestsList?.addEventListener('click', function (ev) {
    const btn = ev.target.closest('.request-item');
    if (!btn) return;
    const shopId = btn.dataset.shopId;
    const shopName = btn.dataset.shopName || null;
    // visual
    requestsList.querySelectorAll('.request-item').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadConversation(shopId, shopName);
  });

  // manual open
  manualBtn?.addEventListener('click', function () {
    const id = manualInput?.value?.trim();
    if (!id) return alert('Entrez l’ID de la boutique.');
    requestsList.querySelectorAll('.request-item').forEach(b => b.classList.remove('active'));
    loadConversation(id, 'Boutique #' + id);
  });

  // send events
  convSendBtn?.addEventListener('click', function () { sendMessage(); });
  convInput?.addEventListener('keypress', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  // refresh
  btnRefreshConv?.addEventListener('click', function () { fetchAndRender(true); });

  // when offcanvas closes, stop polling
  const offcanvasEl = document.getElementById('requestsOffcanvas');
  offcanvasEl?.addEventListener('hidden.bs.offcanvas', function () { stopPolling(); });

  // cleanup on leave
  window.addEventListener('beforeunload', () => stopPolling());
});
</script>
@endpush

@endsection
