{{-- resources/views/support/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🎧 Ticket #{{ $ticket->id }} — {{ $ticket->subject }}</h3>
    <div>
      @if($ticket->status === 'open')
        <form method="POST" action="{{ route('support.close',$ticket) }}" class="d-inline">
          @csrf
          <button class="btn btn-sm btn-outline-danger">Fermer</button>
        </form>
      @else
        <span class="badge bg-secondary">Fermé</span>
      @endif
    </div>
  </div>

  <div class="mb-2 text-muted">
    Boutique : <b>{{ $ticket->shop->name ?? '—' }}</b> • Ouvert par : <b>{{ $ticket->creator->name }}</b>
  </div>

  <div id="chat" class="border rounded p-3 bg-light" style="height: 380px; overflow-y: auto;">
    @foreach($ticket->messages as $m)
      <div class="mb-3">
        <div class="small text-muted">
          <b>{{ $m->author->name }}</b> — {{ $m->created_at->format('d/m/Y H:i') }}
        </div>
        <div class="p-2 bg-white rounded shadow-sm">{{ $m->body }}</div>
      </div>
    @endforeach
  </div>

  @if($ticket->status === 'open')
  <form method="POST" action="{{ route('support.messages.store', $ticket) }}" class="mt-3">
    @csrf
    <div class="input-group">
      <textarea name="body" rows="2" class="form-control" placeholder="Votre message..." required></textarea>
      <button class="btn btn-primary">Envoyer</button>
    </div>
  </form>
  @endif

  <div class="mt-4">
    📞 <b>Appel :</b>
    @php
      // Si tu as un numéro dans la boutique, remplace par $ticket->shop?->phone
      $phone = $ticket->shop->phone ?? '+224000000000';
    @endphp
    <a href="tel:{{ $phone }}">{{$ticket->shop->phone }}</a>
    &nbsp;•&nbsp;
    ✉️ <b>Email :</b>
    @php
      // Si tu stockes un email boutique, remplace par $ticket->shop?->email
      $email = $ticket->shop->email ?? 'support@exemple.com';
    @endphp
    <a href="mailto:{{ $email }}">{{ $ticket->shop->email  }}</a>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Rafraîchissement auto de la discussion toutes les 5s
const chat = document.getElementById('chat');
let firstLoad = true;

async function refreshChat() {
  try {
    const resp = await fetch("{{ route('support.messages.json', $ticket) }}", {
      headers: {'X-Requested-With':'XMLHttpRequest'}
    });
    if (!resp.ok) return;

    const msgs = await resp.json();
    chat.innerHTML = '';
    msgs.forEach(m => {
      const wrap = document.createElement('div');
      wrap.className = 'mb-3';
      wrap.innerHTML = `
        <div class="small text-muted">
          <b>${m.author.name}</b> — ${new Date(m.created_at).toLocaleString()}
        </div>
        <div class="p-2 bg-white rounded shadow-sm">${escapeHtml(m.body)}</div>
      `;
      chat.appendChild(wrap);
    });

    if (firstLoad) { chat.scrollTop = chat.scrollHeight; firstLoad = false; }
    else {
      // auto-scroll si on est déjà proche du bas
      if (chat.scrollHeight - chat.scrollTop - chat.clientHeight < 120) {
        chat.scrollTop = chat.scrollHeight;
      }
    }
  } catch(e){ console.warn(e); }
}

function escapeHtml(s){
  const d = document.createElement('div'); d.innerText = s; return d.innerHTML;
}

refreshChat();
setInterval(refreshChat, 5000);
</script>
@endpush
