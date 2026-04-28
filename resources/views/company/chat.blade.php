@extends('layouts.app')
@section('title', 'Chat · '.$company->name)

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}

.bc-page{
    min-height:100vh;background:#f1f5f9;
    display:flex;flex-direction:column;align-items:center;
    padding:28px 16px 48px;
}

.bc-wrap{width:100%;max-width:700px;display:flex;flex-direction:column;gap:0}

/* Header */
.bc-header{
    background:#fff;border:1px solid #e5e7eb;border-radius:16px 16px 0 0;
    padding:16px 20px;display:flex;align-items:center;gap:14px;
}
.bc-back{
    width:36px;height:36px;border-radius:10px;flex-shrink:0;
    background:#f8fafc;border:1px solid #e5e7eb;
    display:flex;align-items:center;justify-content:center;
    font-size:16px;cursor:pointer;transition:background .14s;
    color:#6b7280;text-decoration:none;
}
.bc-back:hover{background:#f1f5f9;color:#111}
.bc-av{
    width:46px;height:46px;border-radius:13px;flex-shrink:0;
    background:linear-gradient(135deg,#7c3aed,#4f46e5);
    display:flex;align-items:center;justify-content:center;
    font-size:16px;font-weight:800;color:#fff;
}
.bc-hd-info{flex:1;min-width:0}
.bc-hd-name{font-size:15px;font-weight:800;color:#111;line-height:1.2}
.bc-hd-sub{font-size:11.5px;color:#6b7280;margin-top:2px}
.bc-live{
    display:flex;align-items:center;gap:6px;flex-shrink:0;
    font-size:11px;font-weight:700;color:#10b981;
    padding:5px 12px;border-radius:20px;
    background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.18);
}
.bc-dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:blink 2s ease infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}

/* Messages window */
.bc-messages{
    background:#f8fafc;
    border-left:1px solid #e5e7eb;border-right:1px solid #e5e7eb;
    height:500px;overflow-y:auto;padding:20px 20px;
    display:flex;flex-direction:column;gap:6px;
    scrollbar-width:thin;scrollbar-color:#d1d5db transparent;
}
.bc-messages::-webkit-scrollbar{width:4px}
.bc-messages::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:4px}

.bc-empty{
    flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    color:#9ca3af;text-align:center;gap:10px;padding:30px;
}
.bc-empty .eico{font-size:40px}
.bc-empty p{font-size:12.5px;margin:0;line-height:1.6;max-width:240px}

/* Message rows */
.msg-row{display:flex;gap:8px;max-width:80%}
.msg-row.mine{align-self:flex-end;flex-direction:row-reverse}
.msg-row.other{align-self:flex-start}
.msg-av{
    width:32px;height:32px;border-radius:9px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:10.5px;font-weight:800;color:#fff;margin-top:2px;
}
.msg-body{display:flex;flex-direction:column;gap:3px}
.msg-row.mine .msg-body{align-items:flex-end}
.msg-bubble{
    padding:10px 14px;border-radius:16px;
    font-size:13.5px;line-height:1.55;word-break:break-word;
}
.msg-row.mine .msg-bubble{
    background:linear-gradient(135deg,#7c3aed,#5b21b6);
    color:#fff;border-bottom-right-radius:4px;
}
.msg-row.other .msg-bubble{
    background:#fff;border:1px solid #e5e7eb;
    color:#111827;border-bottom-left-radius:4px;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
}
.msg-time{font-size:10px;color:#9ca3af;padding:0 4px}
.msg-date-sep{text-align:center;margin:10px 0 4px}
.msg-date-sep span{
    background:#e5e7eb;color:#6b7280;
    font-size:10.5px;font-weight:600;padding:3px 14px;border-radius:20px;
}

/* Input */
.bc-input-area{
    background:#fff;border:1px solid #e5e7eb;border-top:1px solid #e5e7eb;
    border-radius:0 0 16px 16px;padding:14px 16px;
}
.bc-input-row{
    display:flex;align-items:flex-end;gap:10px;
    background:#f8fafc;border:1px solid #e5e7eb;
    border-radius:14px;padding:10px 14px;transition:border-color .15s,box-shadow .15s;
}
.bc-input-row:focus-within{border-color:#7c3aed;box-shadow:0 0 0 3px rgba(124,58,237,.08)}
.bc-textarea{
    flex:1;background:none;border:none;outline:none;
    color:#111;font-size:13.5px;font-family:inherit;
    resize:none;min-height:22px;max-height:130px;line-height:1.5;overflow-y:auto;
}
.bc-textarea::placeholder{color:#9ca3af}
.bc-send{
    width:40px;height:40px;border-radius:11px;flex-shrink:0;
    background:linear-gradient(135deg,#7c3aed,#5b21b6);border:none;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    font-size:17px;color:#fff;transition:opacity .14s,transform .1s;
}
.bc-send:hover{opacity:.88}
.bc-send:active{transform:scale(.95)}
.bc-send:disabled{opacity:.4;cursor:not-allowed}
.bc-hint{
    margin-top:8px;font-size:10.5px;color:#9ca3af;
    display:flex;align-items:center;justify-content:center;gap:6px;
}
.bc-hint kbd{
    background:#f1f5f9;border:1px solid #d1d5db;border-radius:4px;
    font-size:9.5px;padding:1px 5px;font-family:inherit;color:#6b7280;
}

/* Typing indicator */
.bc-typing{
    display:none;align-items:center;gap:6px;
    padding:4px 0;font-size:11.5px;color:#9ca3af;
}
.bc-typing-dot{
    width:6px;height:6px;border-radius:50%;background:#9ca3af;
    animation:bounce .8s ease infinite;
}
.bc-typing-dot:nth-child(2){animation-delay:.15s}
.bc-typing-dot:nth-child(3){animation-delay:.3s}
@keyframes bounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-5px)}}

/* Responsive */
@media(max-width:520px){
    .bc-hd-name{font-size:14px}
    .bc-live{display:none}
    .bc-page{padding:0}
    .bc-wrap{max-width:100%}
    .bc-header{border-radius:0}
    .bc-input-area{border-radius:0}
    .bc-messages{height:calc(100vh - 145px)}
}
</style>
@endpush

@section('content')
@php
    $u    = auth()->user();
    $uPts = explode(' ', $u->name ?? 'M E');
    $uIni = strtoupper(substr($uPts[0],0,1)) . strtoupper(substr($uPts[1]??'',0,1));

    $cPts = explode(' ', $company->name ?? 'C O');
    $cIni = strtoupper(substr($cPts[0],0,1)) . strtoupper(substr($cPts[1]??'',0,1));
@endphp

<div class="bc-page">
<div class="bc-wrap">

    {{-- Header --}}
    <div class="bc-header">
        <a href="javascript:history.back()" class="bc-back" title="Retour">←</a>
        <div class="bc-av">{{ $cIni }}</div>
        <div class="bc-hd-info">
            <div class="bc-hd-name">{{ $company->name }}</div>
            <div class="bc-hd-sub">Service de livraison · Conversation privée</div>
        </div>
        <div class="bc-live"><span class="bc-dot"></span> En ligne</div>
    </div>

    {{-- Messages --}}
    <div class="bc-messages" id="bcMessages">

        @if($messages->isEmpty())
        <div class="bc-empty">
            <div class="eico">👋</div>
            <p>Démarrez la conversation avec <strong>{{ $company->name }}</strong>.<br>
               Discutez des tarifs, zones de livraison et délais.</p>
        </div>
        @else
        @php $prevDate = null; @endphp
        @foreach($messages as $m)
        @php
            $isMine   = auth()->id() === $m->sender_id;
            $msgDate  = $m->created_at->format('d/m/Y');
            $today    = now()->format('d/m/Y');
            $yesterday= now()->subDay()->format('d/m/Y');
            $dateLabel = $msgDate === $today ? "Aujourd'hui" : ($msgDate === $yesterday ? 'Hier' : $m->created_at->translatedFormat('d F Y'));
        @endphp
        @if($prevDate !== $msgDate)
        <div class="msg-date-sep"><span>{{ $dateLabel }}</span></div>
        @php $prevDate = $msgDate; @endphp
        @endif
        <div class="msg-row {{ $isMine ? 'mine' : 'other' }}">
            <div class="msg-av" style="background:{{ $isMine
                ? 'linear-gradient(135deg,#7c3aed,#5b21b6)'
                : 'linear-gradient(135deg,#10b981,#059669)' }}">
                {{ $isMine ? $uIni : $cIni }}
            </div>
            <div class="msg-body">
                <div class="msg-bubble">{{ $m->message }}</div>
                <div class="msg-time">{{ $m->created_at->format('H:i') }}</div>
            </div>
        </div>
        @endforeach
        @endif

    </div>

    {{-- Input --}}
    <div class="bc-input-area">
        <div class="bc-input-row">
            <textarea class="bc-textarea" id="bcInput" rows="1"
                placeholder="Écrivez votre message à {{ $company->name }}...">{{ $init ?? '' }}</textarea>
            <button class="bc-send" id="bcSend" title="Envoyer">➤</button>
        </div>
        <div class="bc-hint">
            <kbd>Entrée</kbd> pour envoyer &nbsp;·&nbsp; <kbd>Maj+Entrée</kbd> pour aller à la ligne
        </div>
    </div>

</div>
</div>
@endsection

@push('scripts')
<script>
(function(){
const companyId = {{ $company->id }};
const authId    = {{ auth()->id() }};
const shopId    = @json((string)($shopId ?? ''));
const uIni      = @json($uIni);
const cIni      = @json($cIni);

let lastAt = null;

const box   = document.getElementById('bcMessages');
const input = document.getElementById('bcInput');
const send  = document.getElementById('bcSend');

// Init lastAt from last server-rendered message
@if($messages->count())
lastAt = @json($messages->last()->created_at->toDateTimeString());
@endif

// Auto-scroll to bottom
function scrollBottom(){ box.scrollTop = box.scrollHeight + 999; }

// Render one message bubble
function renderMsg(m) {
    // Determine "mine": sender_id matches authId or from_type is 'shop' (boutique user)
    const mine = m.sender_id
        ? (parseInt(m.sender_id) === authId)
        : (m.from_type === 'shop');

    // Remove empty state if first message
    const empty = box.querySelector('.bc-empty');
    if (empty) empty.remove();

    const row    = document.createElement('div');
    row.className = 'msg-row ' + (mine ? 'mine' : 'other');

    const av = document.createElement('div');
    av.className = 'msg-av';
    av.style.background = mine
        ? 'linear-gradient(135deg,#7c3aed,#5b21b6)'
        : 'linear-gradient(135deg,#10b981,#059669)';
    av.textContent = mine ? uIni : cIni;

    const body   = document.createElement('div'); body.className = 'msg-body';
    const bubble = document.createElement('div'); bubble.className = 'msg-bubble';
    bubble.textContent = m.body || m.message || '';
    const time   = document.createElement('div'); time.className = 'msg-time';
    time.textContent = new Date(m.created_at)
        .toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

    body.appendChild(bubble);
    body.appendChild(time);
    row.appendChild(av);
    row.appendChild(body);
    box.appendChild(row);
}

// Poll for new messages
async function poll() {
    if (!shopId) return;
    try {
        let url = `/company/${companyId}/chat/messages?shop_id=${encodeURIComponent(shopId)}`;
        if (lastAt) url += `&after=${encodeURIComponent(lastAt)}`;
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) return;
        const data = await res.json();
        if (data.ok && data.messages && data.messages.length) {
            data.messages.forEach(m => { renderMsg(m); lastAt = m.created_at; });
            scrollBottom();
        }
    } catch(e) { console.error(e); }
}

// Send message
async function sendMsg() {
    const txt = input.value.trim();
    if (!txt) return;
    send.disabled = true;
    try {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const res = await fetch(`/company/${companyId}/chat/send`, {
            method: 'POST', credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: txt, shop_id: shopId })
        });
        const data = await res.json();
        if (data.ok) {
            renderMsg({
                from_type: 'shop',
                sender_id: authId,
                body: txt,
                created_at: data.last || new Date().toISOString()
            });
            input.value = '';
            input.style.height = '';
            scrollBottom();
            lastAt = data.last;
        } else {
            alert(data.error || 'Erreur lors de l\'envoi.');
        }
    } catch(e) { console.error(e); alert('Erreur réseau.'); }
    finally { send.disabled = false; input.focus(); }
}

// Events
send.addEventListener('click', sendMsg);
input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
});
input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 130) + 'px';
});

// Init
scrollBottom();
if (shopId) setInterval(poll, 3000);

// Focus input if init text
if (input.value.trim()) {
    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);
}

})();
</script>
@endpush
