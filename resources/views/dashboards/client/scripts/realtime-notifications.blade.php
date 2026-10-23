<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── Map statuts → libellés + style ── */
    const STATUS_MAP = {
        'livrée':        { label:'Livré',        cls:'pill-success', ico:'check', bg:'background:#d1fae5' },
        'en_livraison':  { label:'En livraison',  cls:'pill-info',    ico:'truck', bg:'background:#dbeafe' },
        'en livraison':  { label:'En livraison',  cls:'pill-info',    ico:'truck', bg:'background:#dbeafe' },
        'annulée':       { label:'Annulée',        cls:'pill-danger',  ico:'x',  bg:'background:#fee2e2' },
        'cancelled':     { label:'Annulée',        cls:'pill-danger',  ico:'x',  bg:'background:#fee2e2' },
        'pending':       { label:'En attente',    cls:'pill-pending', ico:'package', bg:'background:#fef3c7' },
        'confirmée':     { label:'Confirmée',      cls:'pill-info',    ico:'package', bg:'background:#dbeafe' },
        'processing':    { label:'En traitement', cls:'pill-info',    ico:'package', bg:'background:#fef3c7' },
    };

    /* ── Toast ── */
    function showClientToast(msg, type) {
        const t = document.createElement('div');
        t.style.cssText = `
            position:fixed;bottom:20px;right:20px;
            background:${type==='order'?'#0B1E3D':'#1e40af'};
            color:#fff;padding:12px 18px;border-radius:12px;
            font-size:13px;font-weight:600;z-index:99999;
            box-shadow:0 8px 24px rgba(0,0,0,.25);
            display:flex;align-items:center;gap:10px;
            max-width:300px;cursor:pointer;
            animation:rtSlideIn .3s cubic-bezier(.23,1,.32,1);
        `;
        t.innerHTML = msg;
        t.onclick = () => { t.style.opacity='0'; setTimeout(()=>t.remove(),300); };
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity='0'; t.style.transition='all .3s';
            setTimeout(()=>t.remove(),300); }, 6000);
    }

    /* ── Mettre à jour le badge message navbar ── */
    function setMsgBadge(count) {
        const el = document.getElementById('navMsgBadge');
        if (!el) return;
        el.textContent = count > 99 ? '99+' : count;
        el.className = count > 0 ? 'nav-msg-badge show' : 'nav-msg-badge';
    }

    /* ── État local ── */
    const _orderStatuses = {};
    document.querySelectorAll('[data-order-id]').forEach(row => {
        _orderStatuses[row.dataset.orderId] = row.dataset.orderStatus;
    });
    let _prevMsg = {{ $myUnread ?? 0 }};

    /* ── Polling ── */
    async function pollClientNotifs() {
        try {
            const res = await fetch(`/client/notifications/poll?_t=${Date.now()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            if (!res.ok) return;
            const d = await res.json();

            /* Badge géré exclusivement par pollClientMessages — pas de conflit */
            if (d.messages_unread > _prevMsg && _prevMsg >= 0) {
                const n = d.messages_unread - _prevMsg;
                showClientToast(`${SIco.message}<div>${n} nouveau${n>1?'x':''} message${n>1?'s':''} de vendeur</div>`, 'msg');
            }
            _prevMsg = d.messages_unread;

            /* Commandes — mise à jour des statuts */
            (d.orders || []).forEach(order => {
                const prev = _orderStatuses[order.id];
                if (!prev) return;

                if (prev !== order.status) {
                    /* Statut changé → mettre à jour l'UI */
                    _orderStatuses[order.id] = order.status;
                    const info = STATUS_MAP[order.status] || { label: order.status, cls: 'pill-pending', ico:'package', bg:'background:#fef3c7' };

                    const pill = document.getElementById('oPill' + order.id);
                    if (pill) {
                        pill.className = 'order-pill ' + info.cls;
                        pill.textContent = info.label;
                        pill.style.animation = 'none';
                        requestAnimationFrame(() => { pill.style.animation = 'rtPulse .5s ease'; });
                    }
                    const ico = document.getElementById('oIco' + order.id);
                    if (ico) { ico.style.cssText = ico.style.cssText.replace(/background:[^;]+/, info.bg); ico.innerHTML = SIco[info.ico] || SIco.package; }

                    /* Toast */
                    showClientToast(`${SIco.package}<div>Commande <strong>#${order.id}</strong> — ${info.label}<br><small style="opacity:.75">${escHtml(order.shop_name)}</small></div>`, 'order');
                }
            });

            /* Boutiques populaires — mise à jour des compteurs */
            (d.popular_shops || []).forEach(shop => {
                const el = document.querySelector(`[data-shop-id="${shop.id}"] .shop-popular-count`);
                if (el) el.textContent = shop.orders_count + ' cmd';
            });

        } catch(e) {}
    }

    /* ── Démarrage ── */
    pollClientNotifs();
    setInterval(pollClientNotifs, 8000);

    /* ── CSS animations ── */
    const s = document.createElement('style');
    s.textContent = `
        @keyframes rtSlideIn { from{opacity:0;transform:translateX(60px)} to{opacity:1;transform:translateX(0)} }
        @keyframes rtPulse { 0%{transform:scale(1)} 40%{transform:scale(1.12)} 100%{transform:scale(1)} }
    `;
    document.head.appendChild(s);
})();
</script>
