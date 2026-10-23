<style>
.va-chat-fab {
    position: fixed !important; left: auto !important; top: auto !important;
    right: 20px !important; bottom: 20px !important; z-index: 850;
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, var(--brand), var(--brand-dk));
    color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 20px rgba(22,104,216,.45);
    font-size: 24px; transition: transform .2s;
}
.va-chat-fab:hover { transform: scale(1.06); }
.va-chat-fab-locked { text-decoration: none; opacity: .88; }
.va-chat-fab-locked:hover { opacity: 1; }
.va-chat-lock {
    position: absolute; top: -3px; right: -3px;
    width: 20px; height: 20px; border-radius: 50%;
    background: #f59e0b; border: 2px solid #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px;
}

.va-chat-panel {
    position: fixed; right: 20px; bottom: 90px; z-index: 851;
    width: 360px; max-width: calc(100vw - 32px);
    height: 520px; max-height: calc(100vh - 140px);
    background: var(--surface); border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    display: none; flex-direction: column; overflow: hidden;
    border: 1px solid var(--border);
}
.va-chat-panel.open { display: flex; }
.va-chat-hd {
    background: linear-gradient(135deg, var(--brand), var(--brand-dk));
    color: #fff; padding: 14px 16px; display: flex; align-items: center; gap: 10px; flex-shrink: 0;
}
.va-chat-hd-ico { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.2); display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0; }
.va-chat-hd-title { flex: 1; font-size: 14px; font-weight: 800; }
.va-chat-hd-sub { font-size: 10.5px; opacity: .85; margin-top: 1px; }
.va-chat-hd-btn { background: rgba(255,255,255,.15); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 13px; }
.va-chat-hd-btn:hover { background: rgba(255,255,255,.3); }

.va-chat-body { flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; background: var(--bg); }
.va-msg { max-width: 88%; padding: 9px 13px; border-radius: 14px; font-size: 13px; line-height: 1.45; white-space: pre-line; }
.va-msg.user { align-self: flex-end; background: var(--brand); color: #fff; border-bottom-right-radius: 4px; }
.va-msg.bot { align-self: flex-start; background: #fff; border: 1px solid var(--border); color: var(--text); border-bottom-left-radius: 4px; }
.va-msg.typing { align-self: flex-start; background: #fff; border: 1px solid var(--border); padding: 11px 15px; }
.va-typing-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--muted); margin-right: 3px; animation: vaTypingBounce 1.2s infinite; }
.va-typing-dot:nth-child(2) { animation-delay: .15s; }
.va-typing-dot:nth-child(3) { animation-delay: .3s; margin-right:0; }
@keyframes vaTypingBounce { 0%,60%,100% { transform: translateY(0); opacity:.4 } 30% { transform: translateY(-4px); opacity:1 } }

.va-chat-input-row { display: flex; gap: 8px; padding: 10px 12px; border-top: 1px solid var(--border); background: #fff; flex-shrink: 0; }
.va-chat-input { flex: 1; border: 1.5px solid var(--border); border-radius: 30px; padding: 9px 15px; font-size: 13px; outline: none; font-family: var(--font); }
.va-chat-input:focus { border-color: var(--brand); }
.va-chat-send { width: 38px; height: 38px; border-radius: 50%; background: var(--brand); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.va-chat-send:hover { background: var(--brand-dk); }
.va-chat-send:disabled { opacity: .5; cursor: default; }

@media (max-width: 640px) {
    .va-chat-fab { bottom: 20px !important; right: 14px !important; left: auto !important; width: 50px; height: 50px; font-size: 21px; }
    .va-chat-panel {
        top: 0; right: 0; bottom: 0; left: 0;
        width: 100%; max-width: 100%;
        height: 100vh; height: 100dvh; max-height: none;
        border-radius: 0; border: none; z-index: 900;
    }
    .va-chat-hd { padding-top: calc(14px + env(safe-area-inset-top, 0px)); }
    .va-chat-input-row { padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px)); }
}
</style>

{{-- Assistant IA : réservé au Plan Pro (comme les autres fonctionnalités Pro) --}}
@if($isPro)
<button class="va-chat-fab" id="vaChatFab" onclick="toggleVaChat()" title="Assistant de ventes">📊</button>
@else
<a href="{{ route('boutique.subscription.upgrade') }}" class="va-chat-fab va-chat-fab-locked" title="Assistant de ventes — réservé au Plan Pro">
    📊<span class="va-chat-lock">🔒</span>
</a>
@endif

<div class="va-chat-panel" id="vaChatPanel">
    <div class="va-chat-hd">
        <div class="va-chat-hd-ico">📊</div>
        <div style="flex:1;min-width:0">
            <div class="va-chat-hd-title">Assistant de ventes</div>
            <div class="va-chat-hd-sub">Posez une question sur vos performances</div>
        </div>
        <button class="va-chat-hd-btn" onclick="resetVaChat()" title="Nouvelle conversation">↺</button>
        <button class="va-chat-hd-btn" onclick="toggleVaChat()" title="Fermer">✕</button>
    </div>
    <div class="va-chat-body" id="vaChatBody">
        <div class="va-msg bot">Bonjour 👋 Je peux vous renseigner sur vos ventes : produits les plus vendus, chiffre d'affaires sur une période, stock faible... Que voulez-vous savoir ?</div>
    </div>
    <div class="va-chat-input-row">
        <input type="text" id="vaChatInput" class="va-chat-input" placeholder="Ex: quels sont mes produits les plus vendus ce mois-ci ?" autocomplete="off" onkeydown="if(event.key==='Enter')sendVaChat()">
        <button class="va-chat-send" id="vaChatSendBtn" onclick="sendVaChat()" title="Envoyer">➤</button>
    </div>
</div>

<script>
let _vaChatOpen = false;
function toggleVaChat() {
    _vaChatOpen = !_vaChatOpen;
    document.getElementById('vaChatPanel')?.classList.toggle('open', _vaChatOpen);
    if (window.matchMedia('(max-width: 640px)').matches) {
        document.body.style.overflow = _vaChatOpen ? 'hidden' : '';
    }
    if (_vaChatOpen) {
        document.getElementById('vaChatInput')?.focus();
    } else {
        document.activeElement?.blur();
    }
}

function resetVaChat() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const _fetch = typeof window.fetchTimeout === 'function' ? window.fetchTimeout : fetch;
    _fetch(@json(route('boutique.assistant.reset')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    }, 10000).catch(() => {});
    const body = document.getElementById('vaChatBody');
    if (body) body.innerHTML = '<div class="va-msg bot">Nouvelle conversation démarrée. Que voulez-vous savoir ?</div>';
}

function _vaAppendMsg(text, who) {
    const body = document.getElementById('vaChatBody');
    const div = document.createElement('div');
    div.className = 'va-msg ' + who;
    div.textContent = text;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
    return div;
}

function sendVaChat() {
    const input = document.getElementById('vaChatInput');
    const msg = input.value.trim();
    if (!msg) return;
    input.value = '';
    _vaAppendMsg(msg, 'user');

    const body = document.getElementById('vaChatBody');
    const typing = document.createElement('div');
    typing.className = 'va-msg typing';
    typing.id = 'vaTypingIndicator';
    typing.innerHTML = '<span class="va-typing-dot"></span><span class="va-typing-dot"></span><span class="va-typing-dot"></span>';
    body.appendChild(typing);
    body.scrollTop = body.scrollHeight;

    const sendBtn = document.getElementById('vaChatSendBtn');
    sendBtn.disabled = true;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    // fetchTimeout (défini dans boutique-dashboard.js, exposé sur window) : sans délai
    // d'expiration, une requête bloquée sur réseau lent laisserait le bouton grisé indéfiniment.
    const _fetch = typeof window.fetchTimeout === 'function' ? window.fetchTimeout : fetch;
    _fetch(@json(route('boutique.assistant.chat')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
    }, 20000)
    .then(r => r.json())
    .then(data => {
        document.getElementById('vaTypingIndicator')?.remove();
        _vaAppendMsg(data.reply, 'bot');
    })
    .catch(() => {
        document.getElementById('vaTypingIndicator')?.remove();
        _vaAppendMsg("Désolé, une erreur est survenue. Réessayez.", 'bot');
    })
    .finally(() => { sendBtn.disabled = false; });
}
</script>
