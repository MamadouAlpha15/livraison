/* ══════════════════════════════════════════
   MODAL DISCUSSION
══════════════════════════════════════════ */
let _currentProductId = null;
let _currentConvKey    = null;
let _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let _modalLastMsgId  = 0;
let _modalPollTimer  = null;

function openMsgModal(conv) {
    _currentProductId = conv.productId;
    _currentConvKey    = conv.key;

    /* Header */
    const av = document.getElementById('mmAv');
    if (conv.shopImg) {
        av.innerHTML = `<img src="${conv.shopImg}" alt="">`;
        av.style.padding = '0';
    } else {
        av.textContent = conv.shopInit;
        av.style.padding = '';
    }
    document.getElementById('mmName').textContent = conv.shopName;
    document.getElementById('mmProd').textContent = conv.prodName ? conv.prodName : '';
    document.getElementById('mmProductId').value = conv.productId ?? '';

    /* Bande produit */
    const bar = document.getElementById('mmProdBar');
    if (conv.prodName) {
        bar.style.display = 'flex';
        document.getElementById('mmProdName').textContent  = conv.prodName;
        document.getElementById('mmProdPrice').textContent = conv.prodPrice;
        document.getElementById('mmProdLink').href         = conv.prodUrl;
        const img = document.getElementById('mmProdImg');
        const ph  = document.getElementById('mmProdImgPh');
        if (conv.prodImg) {
            img.src = conv.prodImg; img.style.display = '';
            ph.style.display = 'none';
        } else {
            img.style.display = 'none'; ph.style.display = '';
        }
    } else {
        bar.style.display = 'none';
    }

    /* Thread — flex-direction:column-reverse → dernier msg en haut */
    const thread = document.getElementById('mmThread');
    thread.innerHTML = '';
    if (conv.msgs && conv.msgs.length > 0) {
        conv.msgs.forEach(m => thread.appendChild(buildBubble(m)));
    } else {
        thread.innerHTML = '<div style="text-align:center;padding:40px 20px;color:var(--muted);font-size:13px">Aucun message. Écrivez le premier !</div>';
    }

    /* Ouvrir le modal */
    document.getElementById('mmInput').value = '';
    document.getElementById('mmInput').style.height = '';
    document.getElementById('msgModalOverlay').classList.add('open');

    /* Dernier ID message connu — évite les doublons en polling */
    _modalLastMsgId = conv.msgs && conv.msgs.length > 0
        ? Math.max(...conv.msgs.map(m => m.id || 0))
        : 0;

    /* Marquer les messages comme lus dès l'ouverture */
    if (conv.productId) {
        markMessagesRead(conv.productId, conv.key);
    }

    /* Lancer le polling pour messages du vendeur en temps réel */
    startClientModalPolling();
}

function closeMsgModal() {
    document.getElementById('msgModalOverlay').classList.remove('open');
    stopClientModalPolling();
    _currentProductId = null;
    _currentConvKey    = null;
    _modalLastMsgId   = 0;
}

/* ══════════════════════════════════════════
   POLLING MESSAGES EN TEMPS RÉEL (modal client)
══════════════════════════════════════════ */
function startClientModalPolling() {
    stopClientModalPolling();
    _modalPollTimer = setInterval(async function() {
        if (!_currentProductId) return;
        try {
            const res = await fetch(`/client/products/${_currentProductId}/messages?poll=1&_t=${Date.now()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json',
                    'X-CSRF-TOKEN':     _csrfToken,
                }
            });
            if (!res.ok) return;
            const msgs = await res.json();
            const thread = document.getElementById('mmThread');
            const newMsgs = msgs.filter(m => m.id > _modalLastMsgId);

            for (const msg of newMsgs) {
                _modalLastMsgId = Math.max(_modalLastMsgId, msg.id);
                if (thread.querySelector('[data-msg-id="' + msg.id + '"]')) continue;
                if (msg.mine) continue; /* déjà affiché localement */
                if (msg.type && msg.type !== 'text') continue; /* cartes négociation ignorées */

                const parts = (msg.sender || '').split(' ');
                const av = (parts[0]?.[0] ?? '?').toUpperCase() + (parts[1]?.[0] ?? '').toUpperCase();

                /* Retirer le message "vide" si présent */
                const empty = thread.querySelector('[style*="text-align:center"]');
                if (empty) empty.remove();

                thread.prepend(buildBubble({ id: msg.id, body: msg.body, mine: false, av, time: msg.time, read: false }));
            }
        } catch(e) {}
    }, 3000);
}

function stopClientModalPolling() {
    clearInterval(_modalPollTimer);
    _modalPollTimer = null;
}

/* Fermer sur clic overlay */
document.getElementById('msgModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeMsgModal();
});

/* Escape */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeMsgModal(); closeMsgDrawer(); }
});

/* Construire une bulle de message */
function buildBubble(m) {
    const row = document.createElement('div');
    row.className = 'msg-bubble-row ' + (m.mine ? 'from-me' : 'from-them');
    if (m.id) row.dataset.msgId = m.id;
    row.innerHTML = `
        <div class="msg-bubble-av">${escHtml(m.av)}</div>
        <div>
            <div class="msg-bubble">${escHtml(m.body)}</div>
            <div class="msg-meta">
                <span class="msg-time">${m.time}</span>
                ${m.mine && m.read ? '<span class="msg-read">✓✓</span>' : ''}
            </div>
        </div>`;
    return row;
}

function escHtml(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ══════════════════════════════════════════
   MARQUER MESSAGES LUS (AJAX)
══════════════════════════════════════════ */
async function markMessagesRead(productId, convKey) {
    try {
        /* Appel AJAX GET sur la route existante — elle marque les msgs lus */
        await fetch(`/client/products/${productId}/messages?_t=${Date.now()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     _csrfToken,
            }
        });
    } catch(e) {}

    /* Mise à jour immédiate de l'UI sans attendre le serveur */

    /* 1. Retirer le dot rouge sur l'item de conversation */
    const convItem = document.querySelector(`[data-conv-key="${convKey}"]`);
    if (convItem) {
        convItem.classList.remove('has-unread');
        const dot  = convItem.querySelector('.msg-conv-av-dot');
        const cnt  = convItem.querySelector('.msg-conv-unread');
        if (dot) dot.remove();
        if (cnt) cnt.remove();
    }

    /* 2. Recalculer le badge total dans la navbar */
    updateNavBadge();
}

function updateNavBadge() {
    /* L'utilisateur vient d'explicitement lire des messages → forceIfZero=true */
    pollClientMessages(true);
}

/* ══════════════════════════════════════════
   ENVOI MESSAGE (AJAX)
══════════════════════════════════════════ */
async function sendMsg(e) {
    e.preventDefault();
    const input = document.getElementById('mmInput');
    const body  = input.value.trim();
    if (!body || !_currentProductId) return;

    const btn = document.querySelector('#mmForm .msg-send-btn');
    btn.disabled = true;

    try {
        const res = await fetch(`/client/products/${_currentProductId}/message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': _csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ body, product_id: _currentProductId }),
        });

        if (res.ok) {
            const data = await res.json().catch(() => ({}));
            if (data.message_id) _modalLastMsgId = Math.max(_modalLastMsgId, data.message_id);

            /* Ajouter la bulle directement dans le thread */
            const now = new Date();
            const timeStr = String(now.getDate()).padStart(2,'0') + '/'
                          + String(now.getMonth()+1).padStart(2,'0') + ' '
                          + String(now.getHours()).padStart(2,'0') + ':'
                          + String(now.getMinutes()).padStart(2,'0');

            const bubble = buildBubble({
                id: data.message_id || 0,
                body: body, mine: true,
                av: '{{ $initials }}',
                time: timeStr, read: false,
            });

            const thread = document.getElementById('mmThread');
            /* flex-direction:column-reverse → prepend = visuellement en haut */
            thread.prepend(bubble);

            input.value = '';
            input.style.height = '';

            /* Mettre à jour la preview dans la liste des convs */
            if (_currentConvKey) {
                const convItem = document.querySelector(`[data-conv-key="${_currentConvKey}"]`);
                if (convItem) {
                    const preview = convItem.querySelector('.msg-conv-preview');
                    if (preview) preview.textContent = body;
                    /* Retirer aussi les badges non-lus si présents */
                    convItem.classList.remove('has-unread');
                    const dot = convItem.querySelector('.msg-conv-av-dot');
                    const cnt = convItem.querySelector('.msg-conv-unread');
                    if (dot) dot.remove();
                    if (cnt) cnt.remove();
                    updateNavBadge();
                }
            }
        } else {
            alert('Erreur lors de l\'envoi. Veuillez réessayer.');
        }
    } catch(err) {
        alert('Erreur réseau.');
    } finally {
        btn.disabled = false;
        input.focus();
    }
}
