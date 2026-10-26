<style>
.ai-chat-fab {
    position: fixed; right: 20px; bottom: 20px; z-index: 850;
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 20px rgba(31,124,246,.45);
    font-size: 24px; transition: transform .2s;
}
.ai-chat-fab:hover { transform: scale(1.06); }

.ai-chat-panel {
    position: fixed; right: 20px; bottom: 90px; z-index: 851;
    width: 360px; max-width: calc(100vw - 32px);
    height: 520px; max-height: calc(100vh - 140px);
    background: var(--surface); border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    display: none; flex-direction: column; overflow: hidden;
    border: 1px solid var(--border);
}
.ai-chat-panel.open { display: flex; }
.ai-chat-hd {
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    color: #fff; padding: 14px 16px; display: flex; align-items: center; gap: 10px; flex-shrink: 0;
}
.ai-chat-hd-ico { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,.2); display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0; }
.ai-chat-hd-title { flex: 1; font-size: 14px; font-weight: 800; }
.ai-chat-hd-sub { font-size: 10.5px; opacity: .85; margin-top: 1px; }
.ai-chat-hd-btn { background: rgba(255,255,255,.15); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 13px; }
.ai-chat-hd-btn:hover { background: rgba(255,255,255,.3); }

.ai-chat-body { flex: 1; overflow-y: auto; padding: 14px; display: flex; flex-direction: column; gap: 10px; background: var(--grey); }
.ai-msg { max-width: 85%; padding: 9px 13px; border-radius: 14px; font-size: 13px; line-height: 1.45; white-space: pre-line; }
.ai-msg.user { align-self: flex-end; background: var(--orange); color: #fff; border-bottom-right-radius: 4px; }
.ai-msg.bot { align-self: flex-start; background: #fff; border: 1px solid var(--border); color: var(--text); border-bottom-left-radius: 4px; }
.ai-msg.typing { align-self: flex-start; background: #fff; border: 1px solid var(--border); padding: 11px 15px; }
.ai-typing-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--muted); margin-right: 3px; animation: aiTypingBounce 1.2s infinite; }
.ai-typing-dot:nth-child(2) { animation-delay: .15s; }
.ai-typing-dot:nth-child(3) { animation-delay: .3s; margin-right:0; }
@keyframes aiTypingBounce { 0%,60%,100% { transform: translateY(0); opacity:.4 } 30% { transform: translateY(-4px); opacity:1 } }

.ai-products { display: flex; flex-direction: column; gap: 8px; align-self: stretch; }
.ai-products-label { align-self: stretch; font-size: 11px; font-weight: 700; color: var(--muted); margin-top: 2px; }

.ai-order-card { align-self: stretch; display: flex; gap: 10px; padding: 12px; border-radius: 12px; align-items: flex-start; }
.ai-order-card.ok { background: #ecfdf5; border: 1px solid #a7f3d0; }
.ai-order-card.fail { background: #fef2f2; border: 1px solid #fecaca; }
.ai-order-ico { font-size: 20px; flex-shrink: 0; }
.ai-order-title { font-size: 13px; font-weight: 800; color: var(--text); }
.ai-order-sub { font-size: 12px; color: var(--text-2); margin-top: 2px; }
.ai-order-link { display: inline-block; margin-top: 6px; font-size: 12px; font-weight: 700; color: var(--orange-dk); text-decoration: none; }
.ai-order-link:hover { text-decoration: underline; }
.ai-product-card { display: flex; gap: 10px; background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 8px; text-decoration: none; color: inherit; transition: border-color .15s; }
.ai-product-card:hover { border-color: var(--orange); }
.ai-product-img { width: 46px; height: 46px; border-radius: 8px; background: var(--grey); flex-shrink: 0; overflow: hidden; display:flex;align-items:center;justify-content:center; font-size:18px; }
.ai-product-img img { width: 100%; height: 100%; object-fit: cover; }
.ai-product-info { flex: 1; min-width: 0; }
.ai-product-name { font-size: 12px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ai-product-price { font-size: 12.5px; font-weight: 800; color: var(--orange-dk); font-variant-numeric: tabular-nums; margin-top: 2px; }
.ai-product-stock { font-size: 10px; color: var(--muted); margin-top: 1px; }
.ai-product-stock.out { color: #e53e3e; font-weight: 700; }

.ai-chat-input-row { display: flex; gap: 8px; padding: 10px 12px; border-top: 1px solid var(--border); background: #fff; flex-shrink: 0; }
.ai-chat-input { flex: 1; border: 1.5px solid var(--border); border-radius: 30px; padding: 9px 15px; font-size: 13px; outline: none; font-family: var(--font); }
.ai-chat-input:focus { border-color: var(--orange); }
.ai-chat-send { width: 38px; height: 38px; border-radius: 50%; background: var(--orange); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ai-chat-send:hover { background: var(--orange-dk); }
.ai-chat-send:disabled { opacity: .5; cursor: default; }

/* ── Mobile : le bouton reste au-dessus de la barre de navigation du bas
      (58px + zone de sécurité de l'écran), et le chat passe en plein écran
      pour être confortable à utiliser sans gêner le reste de la page. ── */
@media (max-width: 640px) {
    .ai-chat-fab {
        bottom: calc(58px + env(safe-area-inset-bottom, 0px) + 14px);
        right: 14px; width: 50px; height: 50px; font-size: 21px;
    }
    .ai-chat-fab.ai-hide-mobile { display: none; }

    .ai-chat-panel {
        top: 0; right: 0; bottom: 0; left: 0;
        width: 100%; max-width: 100%;
        height: 100vh; height: 100dvh; max-height: none;
        border-radius: 0; border: none; z-index: 900;
    }
    .ai-chat-hd { padding-top: calc(14px + env(safe-area-inset-top, 0px)); }
    .ai-chat-input-row { padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px)); }
}
</style>

<button class="ai-chat-fab" id="aiChatFab" onclick="toggleAiChat()" title="Assistant d'achat" aria-label="Assistant d'achat">{!! \App\Support\IconLibrary::svg('sparkles', '', 24) !!}</button>

<div class="ai-chat-panel" id="aiChatPanel">
    <div class="ai-chat-hd">
        <div class="ai-chat-hd-ico">{!! \App\Support\IconLibrary::svg('sparkles', '', 20) !!}</div>
        <div style="flex:1;min-width:0">
            <div class="ai-chat-hd-title">Assistant Shopio</div>
            <div class="ai-chat-hd-sub">Trouvez vite ce que vous cherchez</div>
        </div>
        <button class="ai-chat-hd-btn" onclick="resetAiChat()" title="Nouvelle conversation">↺</button>
        <button class="ai-chat-hd-btn" onclick="toggleAiChat()" title="Fermer">✕</button>
    </div>
    <div class="ai-chat-body" id="aiChatBody">
        <div class="ai-msg bot">Bonjour, je suis l'assistant Shopio. Dites-moi ce que vous cherchez (un produit, un budget, une catégorie…) et je vous aide à le trouver !</div>
    </div>
    <div class="ai-chat-input-row">
        <input type="text" id="aiChatInput" class="ai-chat-input" placeholder="Ex: une montre à moins de 500 000 GNF" autocomplete="off" onkeydown="if(event.key==='Enter')sendAiChat()">
        <button class="ai-chat-send" id="aiChatSendBtn" onclick="sendAiChat()" title="Envoyer">➤</button>
    </div>
</div>

<script>
let _aiChatOpen = false;
function toggleAiChat() {
    _aiChatOpen = !_aiChatOpen;
    document.getElementById('aiChatPanel')?.classList.toggle('open', _aiChatOpen);
    // Sur mobile le chat passe en plein écran : on cache le bouton flottant
    // pendant ce temps et on bloque le défilement de la page derrière.
    document.getElementById('aiChatFab')?.classList.toggle('ai-hide-mobile', _aiChatOpen);
    if (window.matchMedia('(max-width: 640px)').matches) {
        document.body.style.overflow = _aiChatOpen ? 'hidden' : '';
    }
    if (_aiChatOpen) document.getElementById('aiChatInput')?.focus();
}

function resetAiChat() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch('{{ route("client.assistant.reset") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    }).catch(() => {});
    const body = document.getElementById('aiChatBody');
    if (body) body.innerHTML = '<div class="ai-msg bot">Nouvelle conversation démarrée. Que cherchez-vous ?</div>';
}

function _aiAppendMsg(text, who) {
    const body = document.getElementById('aiChatBody');
    const div = document.createElement('div');
    div.className = 'ai-msg ' + who;
    div.textContent = text;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
    return div;
}

function _aiAppendProducts(products, label) {
    if (!products || !products.length) return;
    const body = document.getElementById('aiChatBody');
    if (label) {
        const lbl = document.createElement('div');
        lbl.className = 'ai-products-label';
        lbl.textContent = label;
        body.appendChild(lbl);
    }
    const wrap = document.createElement('div');
    wrap.className = 'ai-products';
    products.forEach(p => {
        const a = document.createElement('a');
        a.href = '{{ url("/client/produit") }}/' + p.id;
        a.className = 'ai-product-card';
        const stockTxt = p.out_of_stock ? 'Rupture de stock' : (p.stock !== null ? p.stock + ' en stock' : 'En stock');
        a.innerHTML = `
            <div class="ai-product-img">${p.image ? `<img src="${escHtml(p.image)}" alt="">` : SIco.package}</div>
            <div class="ai-product-info">
                <div class="ai-product-name">${escHtml(p.name)}</div>
                <div class="ai-product-price">${Number(p.price).toLocaleString('fr-FR')} ${escHtml(p.currency || 'GNF')}</div>
                <div class="ai-product-stock ${p.out_of_stock ? 'out' : ''}">${stockTxt}</div>
            </div>`;
        wrap.appendChild(a);
    });
    body.appendChild(wrap);
    body.scrollTop = body.scrollHeight;
}

function _aiAppendOrderCard(order) {
    if (!order) return;
    const body = document.getElementById('aiChatBody');
    const card = document.createElement('div');
    if (order.success) {
        card.className = 'ai-order-card ok';
        card.innerHTML = `
            <div class="ai-order-ico">${SIco.check}</div>
            <div class="ai-order-info">
                <div class="ai-order-title">Commande #${order.order_id} confirmée</div>
                <div class="ai-order-sub">${escHtml(order.product_name)} × ${escHtml(order.quantity)} — ${Number(order.total).toLocaleString('fr-FR')} GNF</div>
                <a href="{{ route('client.orders.index') }}" class="ai-order-link">Voir mes commandes →</a>
            </div>`;
    } else {
        card.className = 'ai-order-card fail';
        card.innerHTML = `
            <div class="ai-order-ico">${SIco.alert}</div>
            <div class="ai-order-info">
                <div class="ai-order-title">Commande impossible</div>
                <div class="ai-order-sub">${order.error || 'Une erreur est survenue.'}</div>
            </div>`;
    }
    body.appendChild(card);
    body.scrollTop = body.scrollHeight;
}

function sendAiChat() {
    const input = document.getElementById('aiChatInput');
    const msg = input.value.trim();
    if (!msg) return;
    input.value = '';
    _aiAppendMsg(msg, 'user');

    const body = document.getElementById('aiChatBody');
    const typing = document.createElement('div');
    typing.className = 'ai-msg typing';
    typing.id = 'aiTypingIndicator';
    typing.innerHTML = '<span class="ai-typing-dot"></span><span class="ai-typing-dot"></span><span class="ai-typing-dot"></span>';
    body.appendChild(typing);
    body.scrollTop = body.scrollHeight;

    const sendBtn = document.getElementById('aiChatSendBtn');
    sendBtn.disabled = true;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch('{{ route("client.assistant.chat") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: msg })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('aiTypingIndicator')?.remove();
        _aiAppendMsg(data.reply, 'bot');
        _aiAppendProducts(data.products);
        _aiAppendProducts(data.recommended, 'Vous pourriez aussi aimer');
        _aiAppendOrderCard(data.order);
    })
    .catch(() => {
        document.getElementById('aiTypingIndicator')?.remove();
        _aiAppendMsg("Désolé, une erreur est survenue. Réessayez.", 'bot');
    })
    .finally(() => { sendBtn.disabled = false; });
}
</script>
