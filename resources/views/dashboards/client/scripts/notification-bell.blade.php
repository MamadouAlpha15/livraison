<script>
(function () {
    const POLL_URL  = @json(route('client.notifications.poll'));
    const MSG_URL   = @json(route('client.orders.index'));
    const CSRF      = document.querySelector('meta[name=csrf-token]')?.content ?? '';
    const _UID      = @json(auth()->id());
    const _KEY      = 'cn_alerts_' + _UID;
    const _KEY_MSG  = 'cn_last_msg_' + _UID;
    const _KEY_ORD  = 'cn_last_ord_' + _UID;

    /* ── État ── */
    let _open    = false;
    let _seq     = 0;
    let _alerts  = [];
    let _lastMsg = parseInt(localStorage.getItem(_KEY_MSG) || '0', 10);
    let _lastOrd = {};
    try { _lastOrd = JSON.parse(localStorage.getItem(_KEY_ORD) || '{}'); } catch(e) {}

    /* ── Sync cross-device : charger l'état depuis le serveur au démarrage ── */
    const _serverSyncReady = fetch('/user/notif-state', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(state => {
            if (state.msg_id > _lastMsg) {
                _lastMsg = state.msg_id;
                try { localStorage.setItem(_KEY_MSG, _lastMsg); } catch(e) {}
            }
            if (state.ord) {
                for (const [oid, st] of Object.entries(state.ord)) {
                    if (!_lastOrd[oid]) { _lastOrd[oid] = st; }
                }
                try { localStorage.setItem(_KEY_ORD, JSON.stringify(_lastOrd)); } catch(e) {}
            }
            /* Purger les alertes 'msg' déjà lues (id ≤ _lastMsg) */
            const before = _alerts.length;
            _alerts = _alerts.filter(a => a.type !== 'msg');
            if (_alerts.length !== before) {
                try { localStorage.setItem(_KEY, JSON.stringify(_alerts)); } catch(e) {}
                const totalEl = document.getElementById('notifCount');
                if (totalEl) totalEl.textContent = _alerts.length || '';
                if (!_alerts.length) {
                    document.getElementById('notifBadge')?.classList.remove('show');
                }
            }
        })
        .catch(() => {});

    /* ── Pousser l'état vers le serveur (debouncé 1.5s) ── */
    let _syncTimer = null;
    function _pushNotifState() {
        clearTimeout(_syncTimer);
        _syncTimer = setTimeout(() => {
            fetch('/user/notif-state', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ msg_id: _lastMsg, ord: _lastOrd }),
            }).catch(() => {});
        }, 1500);
    }

    /* Restaurer alertes depuis localStorage + dédupliquer messages par sender */
    try {
        const raw = JSON.parse(localStorage.getItem(_KEY) || '[]');
        const seenSenders = {};
        _alerts = raw.filter(a => {
            if (a.type === 'msg') {
                if (seenSenders[a.senderId]) return false;
                seenSenders[a.senderId] = true;
            }
            return true;
        });
    } catch(e) {}

    /* ── Son (Web Audio API — aucun fichier externe) ── */
    let _audioCtx = null;

    /* iOS/Android bloquent l'audio sans geste utilisateur.
       On crée le contexte au premier tap et on le garde actif. */
    function _initAudio() {
        if (_audioCtx) return;
        try {
            _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (_audioCtx.state !== 'running') _audioCtx.resume();
            const buf = _audioCtx.createBuffer(1, 1, 22050);
            const src = _audioCtx.createBufferSource();
            src.buffer = buf; src.connect(_audioCtx.destination); src.start(0);
        } catch(e) {}
    }
    document.addEventListener('touchstart', _initAudio, { once: true, passive: true });
    document.addEventListener('click',      _initAudio, { once: true });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && _audioCtx && _audioCtx.state !== 'running') {
            _audioCtx.resume().catch(() => {});
        }
    });

    async function playBeep() {
        try {
            if (!_audioCtx) _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (_audioCtx.state !== 'running') await _audioCtx.resume();

            const t = _audioCtx.currentTime;

            /* 1er bip */
            const o1 = _audioCtx.createOscillator();
            const g1 = _audioCtx.createGain();
            o1.connect(g1); g1.connect(_audioCtx.destination);
            o1.type = 'triangle';
            o1.frequency.setValueAtTime(1000, t);
            g1.gain.setValueAtTime(1, t);
            g1.gain.exponentialRampToValueAtTime(0.001, t + 0.25);
            o1.start(t); o1.stop(t + 0.25);

            /* 2e bip */
            const o2 = _audioCtx.createOscillator();
            const g2 = _audioCtx.createGain();
            o2.connect(g2); g2.connect(_audioCtx.destination);
            o2.type = 'triangle';
            o2.frequency.setValueAtTime(1300, t + 0.28);
            g2.gain.setValueAtTime(1, t + 0.28);
            g2.gain.exponentialRampToValueAtTime(0.001, t + 0.55);
            o2.start(t + 0.28); o2.stop(t + 0.55);
        } catch(e) {}
    }

    /* ── Sauvegarder ── */
    function save() {
        try { localStorage.setItem(_KEY, JSON.stringify(_alerts.slice(0, 30))); } catch(e) {}
    }

    /* ── Badge cloche ── */
    function updateBadge() {
        const btn   = document.getElementById('cnBellBtn');
        const badge = document.getElementById('cnBadge');
        const total = document.getElementById('cnTotal');
        const n = _alerts.length;
        if (badge) {
            badge.textContent = n > 99 ? '99+' : n;
            badge.classList.toggle('show', n > 0);
        }
        if (btn)   btn.classList.toggle('has-notif', n > 0);
        if (total) total.textContent = n;
    }

    /* ── Icônes SVG pour le panel ── */
    const _ICO = {
        msg:        `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`,
        order_ok:   `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
        order_del:  `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>`,
        order_done: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>`,
    };
    const _DISMISS_SVG = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;

    /* ── Rendre le dropdown ── */
    function render() {
        const list  = document.getElementById('cnList');
        const total = document.getElementById('cnTotal');
        if (!list) return;
        if (!_alerts.length) {
            list.innerHTML = `<div class="cn-drop-empty">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 8px;display:block;color:#d1d5db"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Aucune notification
            </div>`;
            if (total) total.style.display = 'none';
            return;
        }
        const shown = _alerts.slice(0, 25);
        if (total) { total.textContent = shown.length > 99 ? '99+' : shown.length; total.style.display = ''; }

        const orders   = shown.filter(a => a.type !== 'msg');
        const messages = shown.filter(a => a.type === 'msg');

        function renderItem(a) {
            const isOk   = a.type === 'order_ok';
            const isDel  = a.type === 'order_del';
            const isDone = a.type === 'order_done';
            const isMsg  = a.type === 'msg';

            const avBg = isOk   ? 'linear-gradient(135deg,#22c55e,#16a34a)'
                       : isDel  ? 'linear-gradient(135deg,#f59e0b,#d97706)'
                       : isDone ? 'linear-gradient(135deg,#1F7CF6,#1260C9)'
                                : 'linear-gradient(135deg,#7DB4FF,#1668D8)';
            const avIco = isMsg
                ? _ICO.msg
                : isOk   ? _ICO.order_ok
                : isDel  ? _ICO.order_del
                         : _ICO.order_done;

            const badgeClass = isOk ? 'badge-ok' : isDel ? 'badge-del' : isDone ? 'badge-done' : 'badge-msg';
            const badgeLabel = isOk ? 'Confirmée' : isDel ? 'En livraison' : isDone ? 'Livrée' : 'Message';

            const subClean = (a.sub || '').replace(/📦|💬|🏪/g, '').trim();
            const closeBtn = `<button class="cn-notif-dismiss" onclick="event.stopPropagation();event.preventDefault();cnDismiss(${a.id})" title="Supprimer">${_DISMISS_SVG}</button>`;

            return `<a class="cn-notif-item" href="${a.url || '#'}" onclick="cnGoTo('${a.url || '#'}',${a.id},event)">
                <div class="cn-notif-ico" style="background:${avBg}">${avIco}</div>
                <div class="cn-notif-body">
                    <div class="cn-notif-name">${a.txt}</div>
                    <div class="cn-notif-txt">${subClean}</div>
                    <div class="cn-notif-meta">
                        <span class="cn-notif-time">${a.time || ''}</span>
                        <span class="cn-notif-badge ${badgeClass}">${badgeLabel}</span>
                    </div>
                </div>
                ${closeBtn}
            </a>`;
        }

        let html = '';
        if (orders.length) {
            html += `<div class="cn-notif-section"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg> Commandes</div>`;
            html += orders.map(renderItem).join('');
        }
        if (messages.length) {
            html += `<div class="cn-notif-section"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Messages</div>`;
            html += messages.map(renderItem).join('');
        }
        list.innerHTML = html;
    }

    /* ── Ouvrir/fermer ── */
    function _cnOpen() {
        _open = true;
        document.getElementById('cnDropdown')?.classList.add('open');
        document.getElementById('cnBackdrop')?.classList.add('active');
        render();
    }
    function _cnClose() {
        _open = false;
        document.getElementById('cnDropdown')?.classList.remove('open');
        document.getElementById('cnBackdrop')?.classList.remove('active');
    }
    window.cnToggle = function() { _open ? _cnClose() : _cnOpen(); };

    document.getElementById('cnBackdrop')?.addEventListener('click', _cnClose);
    document.addEventListener('click', e => {
        if (!e.target.closest('#cnBellWrap') && e.target.id !== 'cnBackdrop') _cnClose();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') _cnClose(); });

    /* ── Naviguer + dismiss ── */
    window.cnGoTo = function(url, id, e) {
        if (e) e.preventDefault();
        cnDismiss(id);
        if (url && url !== '#') window.location.href = url;
    };
    window.cnDismiss = function(id) {
        const dismissed = _alerts.find(a => a.id === id);
        _alerts = _alerts.filter(a => a.id !== id);
        save(); updateBadge(); render();
        if (typeof updatePwaBadge === 'function') updatePwaBadge(_alerts.length);

        // Commande dismissée → acquitter côté serveur pour que le badge PWA
        // ne revienne pas au prochain polling (30s)
        if (dismissed && dismissed.type !== 'msg') {
            fetch(@json(route('client.orders.badge-seen')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            }).catch(() => {});
        }
    };

    /* ── Ajouter une alerte (messages — groupé par sender) ── */
    function pushMsg(senderId, senderName, shopName, count, time) {
        const txt = count > 1
            ? `${senderName} — ${count} messages non lus`
            : `${senderName} — nouveau message`;
        const sub = shopName ? `${shopName}` : 'Message';
        const existing = _alerts.find(a => a.type === 'msg' && a.senderId === senderId);
        if (existing) { existing.txt = txt; existing.sub = sub; existing.time = time; }
        else { _alerts.unshift({ id: ++_seq, type: 'msg', txt, sub, time, senderId, url: @json(route('client.messages.hub')) }); }
        save();
    }

    /* ── Ajouter une alerte commande ── */
    function pushOrder(orderId, status, shopName, time) {
        const map = {
            'confirmée':    { type: 'order_ok',   txt: `Commande #${orderId} confirmée`, sub: `${shopName}` },
            'en_livraison': { type: 'order_del',  txt: `Commande #${orderId} en livraison`, sub: `${shopName}` },
            'livrée':       { type: 'order_done', txt: `Commande #${orderId} livrée`, sub: `${shopName}` },
        };
        const info = map[status]; if (!info) return;
        const key  = `${orderId}_${status}`;
        if (_alerts.find(a => a.orderKey === key)) return;
        _alerts.unshift({ id: ++_seq, ...info, time, orderKey: key, url: @json(route('client.orders.index')) });
        if (_alerts.length > 30) _alerts.pop();
        save();
    }

    /* ── Polling principal ── */
    let _firstPoll = true;
    async function poll() {
        try {
            const res = await fetch(`${POLL_URL}?_t=${Date.now()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            if (!res.ok) return;
            const d = await res.json();
            let hasNew = false;

            /* — Messages clients — */
            if (Array.isArray(d.latest_messages) && d.latest_messages.length) {
                const newMsgs = d.latest_messages.filter(m => m.id > _lastMsg);
                if (newMsgs.length) {
                    /* Grouper par sender */
                    const bySender = {};
                    newMsgs.forEach(m => {
                        if (!bySender[m.sender_id]) bySender[m.sender_id] = { ...m, count: 0 };
                        bySender[m.sender_id].count++;
                        bySender[m.sender_id].time = m.time;
                    });
                    Object.values(bySender).forEach(g => {
                        pushMsg(g.sender_id, g.sender_name, g.shop_name, g.count, g.time);
                    });
                    _lastMsg = d.latest_messages[0].id;
                    try { localStorage.setItem(_KEY_MSG, _lastMsg); } catch(e) {}
                    _pushNotifState();
                    if (!_firstPoll) hasNew = true;
                }
            }

            /* — Commandes — */
            if (Array.isArray(d.order_updates)) {
                d.order_updates.forEach(o => {
                    const key = `${o.id}_${o.status}`;
                    if (!_lastOrd[key]) {
                        _lastOrd[key] = true;
                        if (!_firstPoll) {
                            pushOrder(o.id, o.status, o.shop_name, new Date(o.updated_at).toLocaleTimeString('fr', {hour:'2-digit',minute:'2-digit'}));
                            hasNew = true;
                        } else {
                            /* Premier poll : enregistrer sans notifier */
                            pushOrder(o.id, o.status, o.shop_name, new Date(o.updated_at).toLocaleTimeString('fr', {hour:'2-digit',minute:'2-digit'}));
                        }
                    }
                });
                try { localStorage.setItem(_KEY_ORD, JSON.stringify(_lastOrd)); } catch(e) {}
                _pushNotifState();
            }

            /* Badge géré exclusivement par pollClientMessages */

            /* — Son + badge — */
            if (hasNew) playBeep();
            updateBadge();
            if (_open) render();
            _firstPoll = false;

        } catch(e) {}
    }

    /* ── Init ── */
    updateBadge();
    poll();
    setInterval(poll, 7000);
})();
</script>
