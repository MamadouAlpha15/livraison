@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex mb-3 align-items-center justify-content-between">
        <div>
            <h4 class="mb-0">Discussion — {{ $company->name }}</h4>
            <div class="small text-muted">Négociez tarifs et conditions de livraison.</div>
        </div>
        <div>
            <a href="{{ route('delivery.companies.index') }}" class="btn btn-outline-secondary btn-sm">Retour</a>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    {{-- Chat window --}}
                    <div id="chatWindow" style="height:420px; overflow:auto; padding:1rem; background:#f9fafb;">
                        {{-- messages server-rendered (initial) --}}
                        @foreach($messages as $m)
                            @php
                                $isMine = auth()->id() === $m->sender_id;
                            @endphp
                            <div class="d-flex mb-2 {{ $isMine ? 'justify-content-end' : '' }}">
                                <div style="max-width:80%;">
                                    <div class="p-2 rounded {{ $isMine ? 'bg-primary text-white' : 'bg-white border' }}">
                                        <div class="small">{!! nl2br(e($m->message)) !!}</div>
                                    </div>
                                    <div class="small text-muted mt-1 {{ $isMine ? 'text-end' : '' }}">
                                        {{ $m->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-top p-3">
                        <form id="chatForm" onsubmit="return false;">
                            @csrf
                            <input type="hidden" id="shopId" value="{{ $shopId }}">
                            <div class="mb-2">
                                <textarea id="messageInput" class="form-control" rows="3" placeholder="Écrivez votre message...">{{ $init ?? '' }}</textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button id="sendBtn" class="btn btn-primary">Envoyer</button>
                                <button id="clearBtn" class="btn btn-outline-secondary">Effacer</button>
                                <div class="ms-auto small text-muted align-self-center">Conversation privée entre vous et {{ $company->name }}</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mt-3 text-center small text-muted">
                Messages synchronisés toutes les <strong id="pollIntervalLabel">2s</strong>. Restez sur la page pour recevoir en temps réel.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const chatWindow = document.getElementById('chatWindow');
    const input = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const clearBtn = document.getElementById('clearBtn');
    const shopId = document.getElementById('shopId')?.value || '';
    const companyId = '{{ $company->id }}';
    let lastFetchAt = null;
    let pollingInterval = 2000; // ms
    const messagesEndpoint = (after=null) => {
        let url = `/company/${companyId}/chat/messages?shop_id=${encodeURIComponent(shopId)}`;
        if (after) url += `&after=${encodeURIComponent(after)}`;
        return url;
    };
    const sendEndpoint = `/company/${companyId}/chat/send`;

    // utility: scroll bottom
    function scrollBottom() {
        chatWindow.scrollTop = chatWindow.scrollHeight + 200;
    }

    // render a single message (obj as from API)
    function renderMessage(m) {
        const mine = parseInt(m.sender_id) === {{ auth()->id() ?? 'null' }};
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : '');
        const inner = document.createElement('div');
        inner.style.maxWidth = '80%';

        const bubble = document.createElement('div');
        bubble.className = 'p-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border');
        bubble.innerHTML = (m.message || '').replace(/\n/g, '<br>');
        inner.appendChild(bubble);

        const meta = document.createElement('div');
        meta.className = 'small text-muted mt-1 ' + (mine ? 'text-end' : '');
        meta.textContent = new Date(m.created_at).toLocaleString();
        inner.appendChild(meta);

        wrapper.appendChild(inner);
        chatWindow.appendChild(wrapper);
    }

    // fetch new messages
    async function fetchMessages() {
        try {
            const url = lastFetchAt ? messagesEndpoint(lastFetchAt) : messagesEndpoint();
            const res = await fetch(url, { credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.ok) return;
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(m => {
                    renderMessage(m);
                    lastFetchAt = m.created_at;
                });
                scrollBottom();
            }
        } catch (err) {
            console.error('fetchMessages error', err);
        }
    }

    // initial set lastFetchAt from last message in DOM (if any)
    (function initLastAtFromDom(){
        // try to find last message time from server-rendered block
        const metas = chatWindow.querySelectorAll('.small.text-muted.mt-1');
        if (metas.length) {
            const text = metas[metas.length - 1].textContent.trim();
            // best-effort parse - fallback to now
            lastFetchAt = new Date().toISOString();
        } else {
            lastFetchAt = null;
        }
    })();

    // send message
    async function sendMessage() {
        const txt = input.value.trim();
        if (!txt) return;
        sendBtn.disabled = true;
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const payload = { message: txt, shop_id: shopId };
            const res = await fetch(sendEndpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                alert(data?.error || 'Erreur lors de l\'envoi');
            } else {
                // afficher immédiatement
                renderMessage({
                    id: data.message.id,
                    sender_id: data.message.sender_id,
                    sender_role: data.message.sender_role,
                    message: data.message.message,
                    created_at: data.message.created_at
                });
                input.value = '';
                scrollBottom();
                lastFetchAt = data.message.created_at;
            }
        } catch (err) {
            console.error('sendMessage error', err);
            alert('Erreur réseau.');
        } finally {
            sendBtn.disabled = false;
        }
    }

    // events
    sendBtn.addEventListener('click', sendMessage);
    clearBtn.addEventListener('click', () => { input.value = ''; input.focus(); });

    // Enter to send (shift+enter for newline)
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Polling
    setInterval(fetchMessages, pollingInterval);

    // scroll to bottom initially
    scrollBottom();

})();
</script>
@endpush
