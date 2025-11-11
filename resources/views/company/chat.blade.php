@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h4>Chat avec {{ $company->name }}</h4>

  <div id="chat-box" style="height:400px; overflow:auto; border:1px solid #eee; padding:12px; border-radius:8px; background:#fafafa;">
    {{-- messages will be appended here by JS --}}
  </div>

  <form id="chat-form" class="mt-3">
    @csrf
    <input type="hidden" name="shop_id" value="{{ $shop->id ?? '' }}">
    <div class="input-group">
      <input id="chat-input" name="message" class="form-control" placeholder="Écrire un message..." autocomplete="off">
      <button class="btn btn-primary" id="chat-send" type="submit">Envoyer</button>
    </div>
  </form>
</div>
@endsection

@section('scripts')
<script>
const companyId = @json($company->id);
const shopId = @json($shop->id ?? null);
let lastId = 0;
const authId = @json(auth()->id());

// fonction pour échapper HTML basique
function escapeHtml(unsafe) {
  return (unsafe + '').replace(/[&<>"'`=\/]/g, function (s) {
    return ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
      "'": '&#39;', '/': '&#x2F;', '`': '&#96;', '=': '&#x3D;'
    })[s];
  });
}

function appendMessage(msg) {
  const box = document.getElementById('chat-box');
  const fromMe = msg.from_user_id === authId;
  const avatarLabel = fromMe ? 'Vous' : '{{ addslashes($company->name) }}';
  const wrapper = document.createElement('div');
  wrapper.className = 'mb-2';
  const sentAt = new Date(msg.created_at).toLocaleString();
  wrapper.innerHTML = `
    <div class="${fromMe ? 'text-end' : 'text-start'}"><small class="text-muted">${escapeHtml(avatarLabel)}</small></div>
    <div style="display:inline-block; padding:8px; border-radius:8px; background:${fromMe ? '#d1e7dd' : '#f1f3f5'}; max-width:75%;">${escapeHtml(msg.message)}</div>
    <div class="small text-muted">${escapeHtml(sentAt)}</div>
  `;
  box.appendChild(wrapper);
  box.scrollTop = box.scrollHeight;
  lastId = Math.max(lastId, msg.id || 0);
}

async function fetchMessages() {
  try {
    const url = `{{ url('/company') }}/${companyId}/chat/messages?shop_id=${shopId ?? ''}&after_id=${lastId}`;
    const res = await fetch(url, { credentials: 'same-origin' });
    if (!res.ok) return;
    const json = await res.json();
    if (json.messages && json.messages.length) {
      json.messages.forEach(m => appendMessage(m));
    }
  } catch(err) {
    console.error('fetchMessages error', err);
  }
}

document.getElementById('chat-form').addEventListener('submit', async function(e){
  e.preventDefault();
  const input = document.getElementById('chat-input');
  const msg = input.value.trim();
  if (!msg) return;
  const form = new FormData(this);

  try {
    const res = await fetch(`{{ url('/company') }}/${companyId}/chat/send`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: form,
      credentials: 'same-origin'
    });
    const json = await res.json();
    if (json.ok) {
      appendMessage(json.message);
      input.value = '';
    } else {
      console.warn('send error', json);
    }
  } catch(err){
    console.error('send exception', err);
  }
});

// start polling
fetchMessages();
setInterval(fetchMessages, 2500);
</script>
@endsection
