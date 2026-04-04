@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h4>Messages - {{ $company->name }}</h4>

  <div class="mb-3">
    <label for="filter-shop">Filtrer par boutique (shop id)</label>
    <input id="filter-shop" class="form-control" placeholder="Laisser vide pour toutes">
  </div>

  <div id="chat-box" style="height:500px; overflow:auto; border:1px solid #eee; padding:12px; border-radius:8px; background:#fff;"></div>

  <form id="chat-form" class="mt-3">
    @csrf
    <input type="hidden" name="shop_id" id="form-shop-id" value="">
    <div class="input-group">
      <input id="chat-input" name="message" class="form-control" placeholder="Répondre..." autocomplete="off">
      <button class="btn btn-success" type="submit">Envoyer</button>
    </div>
  </form>
</div>
@endsection

@section('scripts')
<script>
const companyId = @json($company->id);
let lastId = 0;
const authId = @json(auth()->id());

function escapeHtml(s){ return (s+'').replace(/[&<>"'`=\/]/g, function(ch){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#96;','=':'&#x3D;'}[ch]; }); }

function appendMessage(msg){
  const box = document.getElementById('chat-box');
  const fromMe = msg.from_user_id === authId;
  const who = fromMe ? 'Vous' : (msg.from_user ? (msg.from_user.name ?? 'Vendeur') : 'Vendeur');
  const el = document.createElement('div');
  el.className = 'mb-2';
  el.innerHTML = `<div class="${fromMe ? 'text-end' : 'text-start'}"><small class="text-muted">${escapeHtml(who)}</small></div>
                  <div style="display:inline-block; padding:8px; border-radius:8px; background:${fromMe ? '#dbeafe' : '#f8f9fa'}; max-width:75%;">${escapeHtml(msg.message)}</div>
                  <div class="small text-muted">${new Date(msg.created_at).toLocaleString()}</div>`;
  box.appendChild(el);
  box.scrollTop = box.scrollHeight;
  lastId = Math.max(lastId, msg.id || 0);
}

async function fetchMessages(){
  try{
    const shopFilter = document.getElementById('filter-shop').value || '';
    const url = `/company/${companyId}/chat/messages?shop_id=${encodeURIComponent(shopFilter)}&after_id=${lastId}`;
    const res = await fetch(url, { credentials: 'same-origin' });
    if (!res.ok) return;
    const json = await res.json();
    if (json.messages && json.messages.length) json.messages.forEach(m => appendMessage(m));
  }catch(e){ console.error(e) }
}

document.getElementById('chat-form').addEventListener('submit', async function(e){
  e.preventDefault();
  const input = document.getElementById('chat-input');
  const msg = input.value.trim();
  if (!msg) return;
  const shopVal = document.getElementById('filter-shop').value || '';
  const form = new FormData();
  form.append('_token', '{{ csrf_token() }}');
  form.append('message', msg);
  if (shopVal) form.append('shop_id', shopVal);

  try {
    const res = await fetch(`/company/${companyId}/chat/send`, {
      method: 'POST',
      body: form,
      credentials: 'same-origin'
    });
    const json = await res.json();
    if (json.ok) {
      appendMessage(json.message);
      input.value = '';
    } else {
      console.warn('send failed', json);
    }
  } catch(err) { console.error(err) }
});

document.getElementById('filter-shop').addEventListener('change', function(){
  document.getElementById('chat-box').innerHTML = '';
  lastId = 0;
  fetchMessages();
});

// start polling
fetchMessages();
setInterval(fetchMessages, 2500);
</script>
@endsection

