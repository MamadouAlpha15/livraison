{{--
    resources/views/client/messages/show.blade.php
    Route   : GET /client/products/{product}/messages
    Variables : $product, $shop, $messages
--}}
@extends('layouts.app')
@section('title', 'Chat — ' . $shop->name)
@php $bodyClass = 'is-dashboard'; @endphp  

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --orange:    #f90;
    --orange-dk: #e47911;
    --navy:      #131921;
    --navy-2:    #232f3e;
    --blue:      #007185;
    --green:     #067d62;
    --grey:      #f3f3f3;
    --grey-2:    #eaeded;
    --border:    #ddd;
    --text:      #0f1111;
    --muted:     #565959;
    --surface:   #fff;
    --font:      'Noto Sans', sans-serif;
    --r:         8px;
    --nav-h:     60px;
}
html, body { font-family: var(--font); margin: 0; background: var(--grey); color: var(--text); height: 100%; -webkit-font-smoothing: antialiased; }

/* NAVBAR */
.nav { background: var(--navy); height: var(--nav-h); display: flex; align-items: center; padding: 0 16px; gap: 12px; position: sticky; top: 0; z-index: 100; }
.nav-logo { font-size: 20px; font-weight: 900; color: var(--orange); text-decoration: none; }
.nav-logo span { color: #fff; }
.nav-back { color: rgba(255,255,255,.85); font-size: 13px; font-weight: 600; text-decoration: none; padding: 6px 10px; border: 1px solid transparent; border-radius: 4px; transition: all .15s; }
.nav-back:hover { border-color: rgba(255,255,255,.5); color: #fff; }

/* LAYOUT */
.chat-wrap {
    max-width: 860px; margin: 24px auto; padding: 0 16px 60px;
    display: flex; flex-direction: column; gap: 0;
}

/* EN-TÊTE PRODUIT */
.chat-prod-header {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r) var(--r) 0 0;
    padding: 14px 18px;
    display: flex; align-items: center; gap: 14px;
    border-bottom: 2px solid var(--orange);
}
.chat-prod-img {
    width: 56px; height: 56px; border-radius: var(--r);
    object-fit: cover; border: 1px solid var(--border); flex-shrink: 0;
    background: var(--grey);
}
.chat-prod-img-ph {
    width: 56px; height: 56px; border-radius: var(--r);
    background: var(--grey-2); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; flex-shrink: 0;
}
.chat-prod-info { flex: 1; min-width: 0; }
.chat-prod-name { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.chat-prod-price { font-size: 17px; font-weight: 700; color: #b12704; font-family: monospace; }
.chat-prod-shop { font-size: 12px; color: var(--muted); margin-top: 2px; }
.chat-prod-shop a { color: var(--blue); text-decoration: none; }
.chat-prod-shop a:hover { text-decoration: underline; }

/* THREAD */
.chat-thread {
    background: #f0f2f5;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
    padding: 20px 18px;
    display: flex; flex-direction: column; gap: 14px;
    min-height: 380px; max-height: 520px; overflow-y: auto;
}
.chat-thread::-webkit-scrollbar { width: 5px; }
.chat-thread::-webkit-scrollbar-thumb { background: #c5c5c5; border-radius: 5px; }

/* Messages */
.msg-row { display: flex; gap: 8px; max-width: 75%; }
.msg-row.mine { margin-left: auto; flex-direction: row-reverse; }

.msg-av {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff;
    flex-shrink: 0; align-self: flex-end;
}
.msg-row.mine    .msg-av { background: linear-gradient(135deg, var(--orange), var(--orange-dk)); }
.msg-row.theirs  .msg-av { background: linear-gradient(135deg, var(--navy-2), #3d5a73); }

.msg-content { display: flex; flex-direction: column; gap: 3px; }
.msg-bubble {
    padding: 10px 14px; border-radius: 16px;
    font-size: 13.5px; line-height: 1.55; word-break: break-word;
}
.msg-row.mine   .msg-bubble { background: var(--orange); color: #fff; border-bottom-right-radius: 3px; }
.msg-row.theirs .msg-bubble { background: var(--surface); color: var(--text); border: 1px solid var(--border); border-bottom-left-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,.07); }

.msg-meta { display: flex; align-items: center; gap: 6px; }
.msg-time { font-size: 10.5px; color: var(--muted); }
.msg-row.mine .msg-meta { justify-content: flex-end; }
.msg-read { font-size: 11px; color: var(--green); }

/* Sender name */
.msg-sender { font-size: 11px; font-weight: 700; color: var(--muted); margin-bottom: 2px; }
.msg-row.mine .msg-sender { text-align: right; }

/* Vide */
.chat-empty {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 8px;
    padding: 40px 20px;
}
.chat-empty-ico { font-size: 40px; opacity: .3; }
.chat-empty-txt { font-size: 14px; color: var(--muted); text-align: center; }

/* ZONE DE SAISIE */
.chat-input-zone {
    background: var(--surface);
    border: 1px solid var(--border);
    border-top: 2px solid var(--orange);
    border-radius: 0 0 var(--r) var(--r);
    padding: 12px 16px;
    display: flex; gap: 10px; align-items: flex-end;
}
.chat-textarea {
    flex: 1; padding: 10px 14px;
    border: 1.5px solid var(--border); border-radius: 20px;
    font-size: 13.5px; font-family: var(--font); color: var(--text);
    background: var(--grey); outline: none; resize: none;
    min-height: 40px; max-height: 100px; line-height: 1.5;
    transition: border-color .15s, background .15s;
}
.chat-textarea:focus { border-color: var(--orange); background: var(--surface); box-shadow: 0 0 0 3px rgba(255,153,0,.1); }
.chat-send-btn {
    width: 42px; height: 42px; border-radius: 50%;
    background: var(--orange); color: var(--navy);
    border: none; cursor: pointer; font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .15s;
    box-shadow: 0 2px 8px rgba(255,153,0,.3);
    font-weight: 700;
}
.chat-send-btn:hover { background: var(--orange-dk); transform: scale(1.07); }

/* Indicateur frappe */
.chat-typing {
    display: none; padding: 6px 18px;
    font-size: 12px; color: var(--muted); font-style: italic;
    background: #f0f2f5;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
}
.typing-dots span {
    display: inline-block; width: 5px; height: 5px; border-radius: 50%;
    background: var(--muted); margin: 0 1px;
    animation: typingBounce .9s ease-in-out infinite;
}
.typing-dots span:nth-child(2) { animation-delay: .15s; }
.typing-dots span:nth-child(3) { animation-delay: .3s; }
@keyframes typingBounce { 0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-5px)} }

/* Flash */
.chat-flash { padding: 10px 14px; border-radius: var(--r); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 10px; }
.chat-flash-success { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }
.chat-flash-danger  { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }

/* RESPONSIVE */
@media (max-width: 600px) {
    .chat-wrap { margin: 12px auto; padding: 0 8px 50px; }
    .chat-thread { max-height: 400px; padding: 14px 12px; }
    .msg-row { max-width: 88%; }
    .chat-prod-header { padding: 10px 12px; gap: 10px; }
}
</style>
@endpush

@section('content')
@php
    $devise  = $shop->currency ?? 'GNF';
    $client  = auth()->user();
    $parts   = explode(' ', $client->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
@endphp

{{-- NAVBAR --}}
<nav class="nav">
    <a href="{{ route('client.dashboard') }}" class="nav-logo">Ma<span>Boutique</span></a>
    <a href="{{ route('client.shops.show', $shop) }}" class="nav-back">← {{ Str::limit($shop->name, 20) }}</a>
</nav>

<div class="chat-wrap">

    @if(session('chat_sent'))
    <div class="chat-flash chat-flash-success">✓ Message envoyé.</div>
    @endif

    {{-- EN-TÊTE PRODUIT --}}
    <div class="chat-prod-header">
        @if($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" class="chat-prod-img" alt="{{ $product->name }}">
        @else
            <div class="chat-prod-img-ph">🏷️</div>
        @endif
        <div class="chat-prod-info">
            <div class="chat-prod-name">{{ $product->name }}</div>
            <div class="chat-prod-price">{{ number_format($product->price, 0, ',', ' ') }} {{ $devise }}</div>
            <div class="chat-prod-shop">
                Boutique : <a href="{{ route('client.shops.show', $shop) }}">{{ $shop->name }}</a>
            </div>
        </div>
    </div>

    {{-- THREAD MESSAGES --}}
    <div class="chat-thread" id="chatThread">
        @forelse($messages as $msg)
        @php
            $isMine = $msg->sender_id === $client->id;
            $senderName = $msg->sender->name ?? 'Inconnu';
            $senderParts = explode(' ', $senderName);
            $senderInit = strtoupper(substr($senderParts[0],0,1)) . strtoupper(substr($senderParts[1] ?? 'X',0,1));
        @endphp
        <div class="msg-row {{ $isMine ? 'mine' : 'theirs' }}">
            <div class="msg-av">{{ $isMine ? $initials : $senderInit }}</div>
            <div class="msg-content">
                <div class="msg-sender">{{ $isMine ? 'Vous' : $senderName }}</div>
                <div class="msg-bubble">{{ $msg->body }}</div>
                <div class="msg-meta">
                    <span class="msg-time">{{ $msg->created_at->format('d/m H:i') }}</span>
                    @if($isMine && $msg->read_at)
                        <span class="msg-read">✓✓</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="chat-empty">
            <div class="chat-empty-ico">💬</div>
            <div class="chat-empty-txt">
                Aucun message pour le moment.<br>
                Posez votre première question au vendeur !
            </div>
        </div>
        @endforelse
    </div>

    {{-- Indicateur frappe --}}
    <div class="chat-typing" id="chatTyping">
        <span class="typing-dots">
            <span></span><span></span><span></span>
        </span>
        &nbsp; Le vendeur est en train d'écrire…
    </div>

    {{-- ZONE DE SAISIE --}}
    <div class="chat-input-zone">
        <form method="POST"
              action="{{ route('client.messages.store', $product) }}"
              id="chatForm"
              style="display:flex;gap:10px;align-items:flex-end;width:100%">
            @csrf
            <textarea name="body"
                      id="chatInput"
                      class="chat-textarea"
                      placeholder="Écrire un message au vendeur…"
                      rows="1"
                      required
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('chatForm').submit()}"
                      oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
            <button type="submit" class="chat-send-btn" title="Envoyer">➤</button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
/* Auto-scroll en bas au chargement */
document.addEventListener('DOMContentLoaded', () => {
    const thread = document.getElementById('chatThread');
    if (thread) thread.scrollTop = thread.scrollHeight;
});

/* Polling toutes les 5s pour nouveaux messages */
let lastCount = {{ $messages->count() }};

async function pollMessages() {
    try {
        const res = await fetch('{{ route('client.messages.index', $product) }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
            }
        });
        if (!res.ok) return;
        const msgs = await res.json();
        if (msgs.length > lastCount) {
            lastCount = msgs.length;
            /* Ajouter les nouveaux messages sans recharger */
            const thread = document.getElementById('chatThread');
            const empty  = thread.querySelector('.chat-empty');
            if (empty) empty.remove();
            const newMsgs = msgs.slice(lastCount - (msgs.length - lastCount));
            msgs.forEach((m, i) => {
                if (i < lastCount - (msgs.length - lastCount)) return;
                const row = document.createElement('div');
                row.className = 'msg-row ' + (m.mine ? 'mine' : 'theirs');
                row.innerHTML = `
                    <div class="msg-av">${m.mine ? '{{ $initials }}' : m.sender.substring(0,2).toUpperCase()}</div>
                    <div class="msg-content">
                        <div class="msg-sender">${m.mine ? 'Vous' : m.sender}</div>
                        <div class="msg-bubble">${escHtml(m.body)}</div>
                        <div class="msg-meta">
                            <span class="msg-time">${m.time}</span>
                            ${m.mine && m.read ? '<span class="msg-read">✓✓</span>' : ''}
                        </div>
                    </div>`;
                thread.appendChild(row);
            });
            thread.scrollTop = thread.scrollHeight;
        }
    } catch(e) {}
}

function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

setInterval(pollMessages, 5000);
</script>
@endpush