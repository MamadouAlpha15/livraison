/* boutique-dashboard.js — extrait de dashboard.blade.php pour être servi en fichier statique mis en cache (voir @vite dans le blade) */
const DEVISE = window.BQ_CFG.devise;

/* ── Icônes SVG JS (notification bell, toasts, boutons dynamiques) ── */
const _SVG = {
    msg:   '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    bldg:  '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    box:   '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
    hdp:   '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
    check: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>',
    clock: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    tag:   '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
    pin:   '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
    moon:  '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
    sun:   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>',
};

/* SIDEBAR */
function toggleGroup(btn) {
    const sub = btn.nextElementSibling;
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.sb-sub.open').forEach(s => { s.classList.remove('open'); s.previousElementSibling?.classList.remove('open'); });
    if (!isOpen) {
        sub.classList.add('open'); btn.classList.add('open');
        const sidebar = document.getElementById('sidebar');
        setTimeout(() => { const support = sidebar?.querySelector('a[href*="support"]'); if (support && sidebar) support.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }, 220);
    }
    setTimeout(() => {
        const sidebar = document.getElementById('sidebar'); const scrollHint = document.getElementById('sbScrollHint');
        if (!sidebar || !scrollHint) return;
        scrollHint.classList.toggle('hidden', sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16);
    }, 300);
}
window.toggleGroup = toggleGroup;
document.querySelectorAll('.sb-sub .sb-item.active').forEach(item => {
    const sub = item.closest('.sb-sub');
    if (sub) { sub.classList.add('open'); sub.previousElementSibling?.classList.add('open'); }
});

/* EXPORT DROPDOWN */
function toggleExportMenu(btn) {
    const menu = document.getElementById('exportMenu');
    menu.classList.toggle('open');
}
window.toggleExportMenu = toggleExportMenu;
document.addEventListener('click', e => {
    if (!e.target.closest('.topbar-export-dropdown')) document.getElementById('exportMenu')?.classList.remove('open');
});

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sbOverlay');
    const scrollHint = document.getElementById('sbScrollHint');
    document.getElementById('btnMenu')?.addEventListener('click', () => { sidebar.classList.add('open'); overlay.classList.add('open'); });
    overlay?.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
    document.getElementById('btnCloseSidebar')?.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
    function updateScrollHint() {
        if (!sidebar || !scrollHint) return;
        scrollHint.classList.toggle('hidden', sidebar.scrollTop + sidebar.clientHeight >= sidebar.scrollHeight - 16);
    }
    sidebar?.addEventListener('scroll', updateScrollHint);
    window.addEventListener('resize', updateScrollHint);
    setTimeout(updateScrollHint, 300);

    /* Tabs livraison */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tab; const card = btn.closest('.delivery-card');
            card.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            card.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            const panel = document.getElementById('tab-' + tabId); if (panel) panel.classList.add('active');
        });
    });

    /* Revenue Chart 7j — tooltip */
    (function() {
        const tip    = document.getElementById('rcTip');
        const tipDay = document.getElementById('rcTipDay');
        const tipVal = document.getElementById('rcTipVal');
        const pts    = document.querySelectorAll('.rc-pt');
        const hits   = document.querySelectorAll('.rc-hit');

        function showTip(pt, rect) {
            const v = parseInt(pt.dataset.val);
            tipDay.textContent = pt.dataset.lbl + (pt.dataset.today === '1' ? " (aujourd'hui)" : '');
            tipVal.textContent = v.toLocaleString('fr-FR') + ' ' + DEVISE;
            tip.style.opacity  = '1';
            // position relative to svg wrap
            const wrap = tip.parentElement.getBoundingClientRect();
            const cx   = rect ? rect.left + rect.width/2 - wrap.left : 0;
            const cy   = rect ? rect.top - wrap.top - 60 : 0;
            tip.style.left = Math.max(0, cx - tip.offsetWidth/2) + 'px';
            tip.style.top  = Math.max(0, cy) + 'px';
        }

        pts.forEach(pt => {
            pt.addEventListener('mouseenter', function(e) {
                this.setAttribute('r', '6');
                showTip(this, this.getBoundingClientRect());
            });
            pt.addEventListener('mouseleave', function() {
                this.setAttribute('r', '4');
                tip.style.opacity = '0';
            });
        });

        hits.forEach((hit, i) => {
            const pt = pts[i];
            if (!pt) return;
            hit.addEventListener('mouseenter', () => pt.dispatchEvent(new Event('mouseenter')));
            hit.addEventListener('mouseleave', () => pt.dispatchEvent(new Event('mouseleave')));
        });
    })();

    /* Sparklines produits */
    document.querySelectorAll('.sp-fill').forEach((el, i) => { setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 100 + i * 90); });

    /* Mini bars commandes */
    document.querySelectorAll('.mini-bar').forEach((bar, i) => {
        setTimeout(() => { bar.style.transition = 'height .5s cubic-bezier(.23,1,.32,1)'; bar.style.height = bar.dataset.h + '%'; }, 100 + i * 60);
    });

    /* Dark mode toggle */
    const btnDark = document.getElementById('btnDarkMode');
    if (btnDark) {
        btnDark.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            btnDark.innerHTML = document.documentElement.classList.contains('dark') ? _SVG.sun : _SVG.moon;
        });
    }

    /* Search bar (Ctrl+K) */
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); }
    });

    /* Période */
    const periodLabels = { yesterday:'Hier', today:"Aujourd'hui", '7days':'7 derniers jours', '30days':'30 derniers jours', this_month:'Ce mois', last_month:'Mois dernier', this_year:'Cette année', last_year:'Année dernière' };

    function fmt(n) {
        if (n >= 1_000_000) return (n / 1_000_000).toFixed(2).replace(/\.?0+$/, '') + 'M';
        return Math.round(n).toLocaleString('fr-FR');
    }

    function drawBars(points) {
        const barsEl   = document.getElementById('periodBars');
        const labelsEl = document.getElementById('periodLabels');
        if (!barsEl || !labelsEl) return;
        const max = Math.max(...points.map(p => p.ca), 1);

        barsEl.innerHTML = points.map(p => {
            const h     = p.ca > 0 ? Math.max(Math.round((p.ca / max) * 100), 3) : 2;
            const pMoy  = (p.nb > 0) ? Math.round(p.ca / p.nb) : 0;
            return `<div class="period-bar-wrap">
                <div class="period-bar-tooltip">
                    <span class="tt-date">${p.label}</span>
                    <span class="tt-ca">${fmt(p.ca)} ${DEVISE}</span>
                    <span class="tt-detail">${p.nb > 0 ? p.nb + ' cmd · panier ' + fmt(pMoy) + ' ' + DEVISE : 'Aucune vente'}</span>
                </div>
                <div class="period-bar ${p.ca === 0 ? 'empty' : ''}" style="height:0%" data-h="${h}"></div>
            </div>`;
        }).join('');

        labelsEl.innerHTML = points.map(p => `<div class="period-bar-lbl">${p.label}</div>`).join('');

        barsEl.querySelectorAll('.period-bar').forEach((bar, i) => {
            setTimeout(() => { bar.style.transition = 'height .4s cubic-bezier(.23,1,.32,1)'; bar.style.height = bar.dataset.h + '%'; }, i * 30);
        });
    }

    async function loadPeriod(period) {
        const loading   = document.getElementById('periodLoading');
        const statsWrap = document.getElementById('periodStatsWrap');
        const labelEl   = document.getElementById('periodLabel');
        if (labelEl) labelEl.innerHTML = 'Période : <strong>' + (periodLabels[period] || period) + '</strong>';
        loading.classList.add('show'); statsWrap.style.opacity = '.3';
        try {
            const res = await fetch(`${window.BQ_CFG.periodStatsUrl}?period=${period}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            document.getElementById('pCA').textContent     = fmt(data.ca);
            document.getElementById('pCA').title           = Math.round(data.ca).toLocaleString('fr-FR') + ' ' + DEVISE;
            document.getElementById('pCMD').textContent    = data.nb;
            document.getElementById('pPANIER').textContent = fmt(data.panier);
            document.getElementById('pPANIER').title       = Math.round(data.panier).toLocaleString('fr-FR') + ' ' + DEVISE;
            document.getElementById('pTAUX').textContent   = data.taux + '%';

            /* Valeur exacte sous le chiffre abrégé (visible mobile + desktop) */
            const caFull = document.getElementById('pCA-full');
            if (caFull) {
                if (data.ca >= 1_000_000) {
                    caFull.textContent  = '= ' + Math.round(data.ca).toLocaleString('fr-FR') + ' ' + DEVISE;
                    caFull.style.display = '';
                } else {
                    caFull.style.display = 'none';
                }
            }
            const panierFull = document.getElementById('pPANIER-full');
            if (panierFull) {
                if (data.panier >= 1_000_000) {
                    panierFull.textContent  = '= ' + Math.round(data.panier).toLocaleString('fr-FR') + ' ' + DEVISE;
                    panierFull.style.display = '';
                } else {
                    panierFull.style.display = 'none';
                }
            }
            drawBars(data.points);
        } catch (err) {
            console.error(err); document.getElementById('pCA').textContent = '—';
        } finally {
            loading.classList.remove('show'); statsWrap.style.opacity = '1';
        }
    }

    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active'); loadPeriod(btn.dataset.period);
        });
    });

    /* Badge "30 derniers jours ▾" → déclenche l'analyse 30j + scroll */
    document.querySelector('.mini-period-badge')?.addEventListener('click', () => {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        const btn30 = document.querySelector('.period-btn[data-period="30days"]');
        if (btn30) { btn30.classList.add('active'); loadPeriod('30days'); }
        document.getElementById('periodCard')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    loadPeriod('this_month');
});

(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const _UID = String(window.BQ_CFG.uid);

    /* ── Clés localStorage préfixées par UID (évite contamination cross-user) ── */
    const _KEY_MSG     = 'bq_last_msg_id_'       + _UID;
    const _KEY_CO      = 'bq_last_co_msg_id_'    + _UID;
    const _KEY_SUP     = 'bq_last_support_id_'   + _UID;
    const _KEY_ALERTS  = 'boutique_notif_alerts_' + _UID;
    const _KEY_CO_SEEN = 'bq_co_seen_'           + _UID;

    /* ── État local ── */
    let _prevMsg            = -1;
    let _prevOrders         = -1;
    let _prevCompanyMsg     = -1;
    let _notifOpen          = false;
    /* Persiste entre sessions via localStorage pour éviter re-notification après reconnexion */
    let _lastSeenMsgId        = parseInt(localStorage.getItem(_KEY_MSG) || '0', 10);
    let _lastSeenCompanyMsgId = parseInt(localStorage.getItem(_KEY_CO)  || '0', 10);
    let _lastSeenSupportId    = parseInt(localStorage.getItem(_KEY_SUP) || '0', 10);

    /* ── Sync cross-device : charger l'état depuis le serveur au démarrage ── */
    /* Retourne une promesse — le polling attend sa résolution avant de démarrer */
    const _serverSyncReady = fetch('/user/notif-state', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(state => {
            if (state.msg_id     > _lastSeenMsgId)        { _lastSeenMsgId        = state.msg_id;     try { localStorage.setItem(_KEY_MSG, _lastSeenMsgId); } catch(e){} }
            if (state.co_msg_id  > _lastSeenCompanyMsgId) { _lastSeenCompanyMsgId = state.co_msg_id;  try { localStorage.setItem(_KEY_CO,  _lastSeenCompanyMsgId); } catch(e){} }
            if (state.support_id > _lastSeenSupportId)    { _lastSeenSupportId    = state.support_id; try { localStorage.setItem(_KEY_SUP, _lastSeenSupportId); } catch(e){} }
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
                body: JSON.stringify({ msg_id: _lastSeenMsgId, co_msg_id: _lastSeenCompanyMsgId, support_id: _lastSeenSupportId }),
            }).catch(() => {});
        }, 1500);
    }

    /* Restaurer les alertes depuis localStorage (persiste entre sessions) */
    let _alerts = [];
    try {
        const saved = localStorage.getItem(_KEY_ALERTS);
        if (saved) {
            const raw = JSON.parse(saved);
            /* Dédupliquer les alertes 'msg' par senderName — garde la plus récente */
            const seen = {};
            _alerts = raw.filter(a => {
                if (a.type !== 'msg' || !a.senderName) return true;
                if (seen[a.senderName]) return false;
                seen[a.senderName] = true;
                return true;
            });
        }
    } catch(e) {}

    function _saveAlerts() {
        try { localStorage.setItem(_KEY_ALERTS, JSON.stringify(_alerts)); } catch(e) {}
    }

    /* ── Helpers badge générique ── */
    function setBadge(id, count) {
        const el = document.getElementById(id);
        if (!el) return;
        if (count > 0) {
            el.textContent = count > 99 ? '99+' : count;
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }

    /* ── Son notification (Web Audio API — aucun fichier externe) ── */
    let _audioCtx = null;
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

    /* Affiche le bouton son sur mobile si AudioContext bloqué */
    function _checkShowSoundBtn() {
        const isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);
        if (isMobile && (!_audioCtx || _audioCtx.state !== 'running')) {
            const btn = document.getElementById('btnEnableSound');
            if (btn) btn.style.display = 'inline-flex';
        }
    }
    setTimeout(_checkShowSoundBtn, 2000);

    function enableSoundManual() {
        _initAudio();
        if (_audioCtx) _audioCtx.resume().then(() => {
            playBeep();
            const btn = document.getElementById('btnEnableSound');
            if (btn) btn.style.display = 'none';
        }).catch(() => {});
    }
    window.enableSoundManual = enableSoundManual;
    /* Relance l'audio quand la page revient en avant-plan (iOS/Android) */
    async function _resumeAudio() {
        if (!_audioCtx) return;
        if (_audioCtx.state === 'suspended' || _audioCtx.state === 'interrupted') {
            try { await _audioCtx.resume(); } catch(e) { _audioCtx = null; }
        }
        /* iOS invalide parfois le contexte — en créer un nouveau */
        if (_audioCtx && _audioCtx.state === 'closed') { _audioCtx = null; }
    }
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') _resumeAudio();
    });
    window.addEventListener('focus',    _resumeAudio);
    window.addEventListener('pageshow', _resumeAudio);

    async function playBeep() {
        try {
            /* Recrée le contexte si fermé/invalide (iOS background) */
            if (!_audioCtx || _audioCtx.state === 'closed') {
                _audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (_audioCtx.state !== 'running') await _audioCtx.resume();
            if (_audioCtx.state !== 'running') return;
            const t = _audioCtx.currentTime;
            const o1 = _audioCtx.createOscillator(), g1 = _audioCtx.createGain();
            o1.connect(g1); g1.connect(_audioCtx.destination);
            o1.type = 'triangle';
            o1.frequency.setValueAtTime(1000, t);
            g1.gain.setValueAtTime(1, t);
            g1.gain.exponentialRampToValueAtTime(0.001, t + 0.25);
            o1.start(t); o1.stop(t + 0.25);
            const o2 = _audioCtx.createOscillator(), g2 = _audioCtx.createGain();
            o2.connect(g2); g2.connect(_audioCtx.destination);
            o2.type = 'triangle';
            o2.frequency.setValueAtTime(1300, t + 0.28);
            g2.gain.setValueAtTime(1, t + 0.28);
            g2.gain.exponentialRampToValueAtTime(0.001, t + 0.55);
            o2.start(t + 0.28); o2.stop(t + 0.55);
        } catch(e) {}
    }

    /* ── Toast bas d'écran ── */
    function showToast(msg, type) {
        const t = document.createElement('div');
        t.style.cssText = `
            position:fixed;bottom:${20 + document.querySelectorAll('.rt-toast').length * 60}px;
            right:20px;background:${type==='order'?'#111118':type==='msg'?'#1e40af':type==='company'?'#4f46e5':type==='support'?'#166534':'#1f2937'};
            color:#fff;padding:12px 18px;border-radius:12px;font-size:13px;font-weight:600;
            z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,.25);
            animation:slideInRight .3s cubic-bezier(.23,1,.32,1);
            display:flex;align-items:center;gap:10px;max-width:280px;cursor:pointer;
        `;
        t.className = 'rt-toast';
        t.innerHTML = msg;
        t.onclick   = () => { t.style.opacity='0'; setTimeout(()=>t.remove(),300); };
        document.body.appendChild(t);
        playBeep();
        setTimeout(() => { t.style.opacity='0'; t.style.transform='translateX(120%)';
            t.style.transition='all .3s'; setTimeout(()=>t.remove(),300); }, 5000);
    }

    /* ── Dropdown notifications ── */
    let _alertIdSeq = 0;

    /* Initiales depuis un nom (ex: "Jean Dupont" → "JD") */
    function _ini(name) {
        if (!name) return '?';
        return name.trim().split(/\s+/).map(w => w[0]).join('').toUpperCase().slice(0, 2);
    }

    function renderNotifList() {
        const list = document.getElementById('notifList');
        const cntEl = document.getElementById('notifDropdownTotal');
        if (!list) return;

        if (!_alerts.length) {
            list.innerHTML = `<div class="bq-np-empty">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 8px;display:block;color:#cbd5e1"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Aucune notification pour le moment
            </div>`;
            if (cntEl) cntEl.style.display = 'none';
            return;
        }

        const shown = _alerts.slice(0, 20);
        if (cntEl) { cntEl.textContent = shown.length > 99 ? '99+' : shown.length; cntEl.style.display = ''; }

        /* Grouper par type pour les sections */
        const orders     = shown.filter(a => a.type === 'order');
        const messages   = shown.filter(a => a.type === 'msg');
        const companies  = shown.filter(a => a.type === 'company_msg');
        const supports   = shown.filter(a => a.type === 'support');

        const CLOSE_SVG = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;

        function renderItem(a) {
            const isOrder  = a.type === 'order';
            const isCo     = a.type === 'company_msg';
            const isSup    = a.type === 'support';

            const avBg = isOrder ? 'linear-gradient(135deg,#f59e0b,#d97706)'
                       : isCo   ? 'linear-gradient(135deg,#6366f1,#4f46e5)'
                       : isSup  ? 'linear-gradient(135deg,#10b981,#059669)'
                                : 'linear-gradient(135deg,#818cf8,#6366f1)';
            const avLabel = isOrder ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>'
                         : isCo  ? _ini(a.companyName)
                         : isSup ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>'
                                 : _ini(a.senderName || a.msg);

            const badgeClass = isOrder ? 'order' : isCo ? 'company' : isSup ? 'support' : 'msg';
            const badgeLabel = isOrder ? 'En attente' : isCo ? 'Entreprise' : isSup ? 'SuperAdmin' : 'Message';

            const href = isCo && a.companyId ? '#' : (a.url || '#');
            const onclick = isCo && a.companyId
                ? `onclick="event.preventDefault();event.stopPropagation();bqOpenCompanyChat(${a.companyId},decodeURIComponent('${encodeURIComponent(a.companyName||'')}'));_dismissAlert(${a.id})"`
                : !isOrder ? `onclick="event.stopPropagation();_dismissAlert(${a.id})"` : '';

            const closeBtn = !isOrder
                ? `<button class="bq-np-close-btn" onclick="event.stopPropagation();event.preventDefault();_dismissAlert(${a.id})" title="Supprimer">${CLOSE_SVG}</button>`
                : '';

            return `<a href="${href}" class="bq-np-item" ${onclick}>
                <div class="bq-np-av" style="background:${avBg}">${avLabel}</div>
                <div class="bq-np-body">
                    <div class="bq-np-name">${a.msg.length > 40 ? a.msg.slice(0,40)+'…' : a.msg}</div>
                    <div class="bq-np-msg">${a.body || ''}</div>
                    <div class="bq-np-meta">
                        <span class="bq-np-time">${a.time}</span>
                        <span class="bq-np-badge ${badgeClass}">${badgeLabel}</span>
                    </div>
                </div>
                ${closeBtn}
            </a>`;
        }

        let html = '';
        if (orders.length) {
            html += `<div class="bq-np-section"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg> Commandes</div>`;
            html += orders.map(renderItem).join('');
        }
        if (messages.length) {
            html += `<div class="bq-np-section"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> Messages clients</div>`;
            html += messages.map(renderItem).join('');
        }
        if (companies.length) {
            html += `<div class="bq-np-section"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> Entreprises</div>`;
            html += companies.map(renderItem).join('');
        }
        if (supports.length) {
            html += `<div class="bq-np-section"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/></svg> Support</div>`;
            html += supports.map(renderItem).join('');
        }
        list.innerHTML = html;
    }

    /* ── Supprimer une alerte par son id (uniquement type message) ── */
    window._dismissAlert = function(id) {
        _alerts = _alerts.filter(a => a.id !== id);
        _saveAlerts();
        renderNotifList();
    };

    /* ── Retire toutes les alertes company_msg pour une entreprise donnée ── */
    window._bqRemoveCompanyAlert = function(companyId) {
        _alerts = _alerts.filter(a => !(a.type === 'company_msg' && String(a.companyId) === String(companyId)));
        _saveAlerts();
        if (_notifOpen) renderNotifList();
        const companyAlertCount = _alerts.filter(a => a.type === 'company_msg').length;
        const badge = document.getElementById('notifBellCount');
        /* badge sera recalculé au prochain poll */
    };

    function _openNotifPanel() {
        _notifOpen = true;
        const dd = document.getElementById('notifDropdown');
        const bd = document.getElementById('bqNotifBackdrop');
        if (dd) dd.classList.add('open');
        if (bd) bd.classList.add('active');
        renderNotifList();
    }
    function _closeNotifPanel() {
        _notifOpen = false;
        const dd = document.getElementById('notifDropdown');
        const bd = document.getElementById('bqNotifBackdrop');
        if (dd) dd.classList.remove('open');
        if (bd) bd.classList.remove('active');
    }
    window._closeNotifPanel = _closeNotifPanel;

    window.toggleNotifDropdown = function() {
        _notifOpen ? _closeNotifPanel() : _openNotifPanel();
    };

    document.getElementById('bqNotifBackdrop')?.addEventListener('click', _closeNotifPanel);
    document.addEventListener('click', e => {
        if (_notifOpen && !e.target.closest('#notifBellWrap') && e.target.id !== 'bqNotifBackdrop') {
            _closeNotifPanel();
        }
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') _closeNotifPanel(); });

    /* ── Push alerte dans la file ── */
    function pushAlert(ico, msg, url, type, body, time, companyId, companyName) {
        if (!time) {
            const now = new Date();
            time = now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0');
        }
        _alerts.unshift({
            id: ++_alertIdSeq, ico, msg, url, time,
            type: type || 'msg', body: body || '',
            companyId: companyId || null,
            companyName: companyName || ''
        });
        if (_alerts.length > 30) _alerts.pop();
        _saveAlerts();
    }

    /* ── Polling principal ── */
    async function pollNotifications() {
        try {
            const res = await fetch('/boutique/notifications/poll', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            if (!res.ok) return;
            const d = await res.json();

            /* Messages */
            setBadge('sbMsgBadge', d.messages_unread);
            setBadge('msgTopbarCount', d.messages_unread);
            const msgBtn = document.getElementById('msgTopbarBtn');
            if (msgBtn) msgBtn.classList.toggle('has-unread', d.messages_unread > 0);

            /* Si plus aucun message non lu → purger les alertes msg de la cloche */
            if (d.messages_unread === 0 && _alerts.some(a => a.type === 'msg')) {
                _alerts = _alerts.filter(a => a.type !== 'msg');
                _saveAlerts();
                _pushNotifState();
            }

            /* Nouveaux messages — même logique au 1er poll et aux suivants */
            if (Array.isArray(d.latest_messages) && d.latest_messages.length > 0) {
                const newMsgs = d.latest_messages.filter(m => m.id > _lastSeenMsgId);
                if (newMsgs.length > 0) {
                    /* Toast seulement si ce n'est pas le premier poll (évite le spam au chargement) */
                    if (_prevMsg >= 0) {
                        const n = newMsgs.length;
                        showToast(`${_SVG.msg} <div>${n} nouveau${n>1?'x':''} message${n>1?'s':''} non lu${n>1?'s':''}</div>`, 'msg');
                    }
                    /* Grouper par expéditeur — une seule alerte par client dans la cloche */
                    const bySender = {};
                    newMsgs.forEach(m => {
                        const key = m.sender_name;
                        if (!bySender[key]) bySender[key] = { sender_name: key, count: 0, time: m.time, product_name: m.product_name };
                        bySender[key].count++;
                        bySender[key].time = m.time;
                    });
                    Object.values(bySender).forEach(g => {
                        const label = g.count > 1
                            ? `${g.sender_name} — ${g.count} messages non lus`
                            : g.product_name
                                ? `${g.sender_name} · ${g.product_name} — message non lu`
                                : `${g.sender_name} — message non lu`;
                        const existing = _alerts.find(a => a.type === 'msg' && a.senderName === g.sender_name);
                        if (existing) {
                            existing.msg  = label;
                            existing.time = g.time;
                            _saveAlerts();
                        } else {
                            _alerts.unshift({ id: ++_alertIdSeq, ico: _SVG.msg, msg: label, url: window.BQ_CFG.messagesHubUrl, time: g.time, type: 'msg', body: '', companyId: null, companyName: '', senderName: g.sender_name });
                            if (_alerts.length > 30) _alerts.pop();
                            _saveAlerts();
                        }
                    });
                    _lastSeenMsgId = newMsgs[0].id;
                    try { localStorage.setItem(_KEY_MSG, _lastSeenMsgId); } catch(e) {}
                    _pushNotifState();
                }
            }
            _prevMsg = d.messages_unread;

            /* ── Messages des entreprises de livraison ── */
            if (Array.isArray(d.latest_company_messages) && d.latest_company_messages.length > 0) {
                const newCMsgs = d.latest_company_messages.filter(m => {
                    if (m.id <= _lastSeenCompanyMsgId) return false;
                    try {
                        const seen = JSON.parse(localStorage.getItem(_KEY_CO_SEEN) || '{}');
                        if (seen[m.company_id] && m.id <= seen[m.company_id]) return false;
                    } catch(e) {}
                    return true;
                });
                if (newCMsgs.length > 0) {
                    if (_prevCompanyMsg >= 0) {
                        const n = newCMsgs.length;
                        showToast(`${_SVG.bldg} <div>${n} nouveau${n>1?'x':''} message${n>1?'s':''} d'entreprise${n>1?'s':''}</div>`, 'company');
                    }
                    /* Grouper par entreprise — une seule alerte par entreprise */
                    const byCompany = {};
                    newCMsgs.forEach(m => {
                        const cid = m.company_id;
                        if (!byCompany[cid]) byCompany[cid] = { company_id: cid, company_name: m.company_name, count: 0, time: m.time, body: m.body };
                        byCompany[cid].count++;
                    });
                    Object.values(byCompany).forEach(g => {
                        const label = g.count > 1
                            ? `${g.company_name} — ${g.count} nouveaux messages`
                            : `${g.company_name} — ${g.body}`;
                        const existing = _alerts.find(a => a.type === 'company_msg' && a.companyId === g.company_id);
                        if (existing) {
                            existing.msg  = label;
                            existing.time = g.time;
                        } else {
                            pushAlert(_SVG.bldg, label, null, 'company_msg', '', g.time, g.company_id, g.company_name);
                        }
                    });
                    _saveAlerts();
                    _lastSeenCompanyMsgId = newCMsgs[0].id;
                    try { localStorage.setItem(_KEY_CO, _lastSeenCompanyMsgId); } catch(e) {}
                    _pushNotifState();
                }
            }
            _prevCompanyMsg = d.company_messages_unread ?? 0;

            /* Commandes — une seule entrée dans _alerts, compteur mis à jour */
            setBadge('sbOrdersBadge', d.orders_pending);
            if (d.orders_pending > 0) {
                const label = `${d.orders_pending} commande${d.orders_pending > 1 ? 's' : ''} en attente`;
                const existing = _alerts.find(a => a.type === 'order');
                if (existing) {
                    /* Mettre à jour le compteur de l'entrée existante */
                    existing.msg = label;
                    if (_prevOrders >= 0 && d.orders_pending > _prevOrders) {
                        const n = d.orders_pending - _prevOrders;
                        showToast(`${_SVG.box} <div>${n} nouvelle${n>1?'s':''} commande${n>1?'s':''} !</div>`, 'order');
                        existing.time = new Date().getHours().toString().padStart(2,'0')+':'+new Date().getMinutes().toString().padStart(2,'0');
                    }
                } else {
                    /* Créer l'entrée commande pour la première fois */
                    if (_prevOrders >= 0 && d.orders_pending > _prevOrders) {
                        const n = d.orders_pending - _prevOrders;
                        showToast(`${_SVG.box} <div>${n} nouvelle${n>1?'s':''} commande${n>1?'s':''} !</div>`, 'order');
                    }
                    pushAlert(_SVG.box, label, window.BQ_CFG.ordersIndexUrl, 'order');
                }
                _saveAlerts();
            } else if (d.orders_pending === 0 && _alerts.some(a => a.type === 'order')) {
                /* Plus aucune commande en attente → supprimer l'entrée */
                _alerts = _alerts.filter(a => a.type !== 'order');
                _saveAlerts();
            }
            _prevOrders = d.orders_pending;

            /* Livreurs */
            setBadge('sbLivreursBadge', d.livreurs_available);

            /* ── Réponses support SuperAdmin ── */
            if (Array.isArray(d.support_replies) && d.support_replies.length) {
                const newReplies = d.support_replies.filter(m => m.id > _lastSeenSupportId);
                if (newReplies.length) {
                    /* Toast une seule fois si c'est pas le 1er poll */
                    if (_lastSeenSupportId > 0) {
                        showToast(`${_SVG.hdp} <div>Le SuperAdmin a répondu à votre ticket support !</div>`, 'support');
                    }
                    /* Une alerte par ticket — si le SuperAdmin renvoie sur le même ticket, on met à jour */
                    [...newReplies].reverse().forEach(m => {
                        const existing = _alerts.find(a => a.type === 'support' && a.ticketId === m.ticket_id);
                        if (existing) {
                            existing.msg          = `SuperAdmin a répondu : « ${m.ticket_subject} »`;
                            existing.time         = m.time;
                            existing.supportMsgId = m.id;
                        } else {
                            pushAlert(
                                _SVG.hdp,
                                `SuperAdmin a répondu : « ${m.ticket_subject} »`,
                                '/support',
                                'support',
                                m.body,
                                m.time
                            );
                            _alerts[0].ticketId     = m.ticket_id;
                            _alerts[0].supportMsgId = m.id;
                        }
                    });
                    /* Mettre à jour le dernier ID vu */
                    _lastSeenSupportId = Math.max(...d.support_replies.map(m => m.id));
                    localStorage.setItem(_KEY_SUP, _lastSeenSupportId);
                    _saveAlerts();
                    _pushNotifState();
                } else if (_lastSeenSupportId === 0 && d.support_replies.length) {
                    /* Premier poll : initialiser sans notifier */
                    _lastSeenSupportId = Math.max(...d.support_replies.map(m => m.id));
                    localStorage.setItem(_KEY_SUP, _lastSeenSupportId);
                    _pushNotifState();
                }
            }

            /* Cloche totale : messages clients + commandes + alertes entreprises + support non dismissées */
            const companyAlertCount = _alerts.filter(a => a.type === 'company_msg').length;
            const supportAlertCount = _alerts.filter(a => a.type === 'support').length;
            setBadge('sbSupportBadge', supportAlertCount);
            const total = d.messages_unread + d.orders_pending + companyAlertCount + supportAlertCount;
            setBadge('notifBellCount', total);
            // Badge icône PWA (logo sur l'écran d'accueil)
            if (typeof window.updatePwaBadge === 'function') window.updatePwaBadge(total);
            const totalEl = document.getElementById('notifDropdownTotal');
            if (totalEl) totalEl.textContent = _alerts.length;

            if (_notifOpen) renderNotifList();
        } catch(e) {}
    }

    /* ── Démarrage : attendre la sync serveur avant le 1er poll ── */
    _serverSyncReady.then(() => {
        pollNotifications();
        setInterval(pollNotifications, 6000);
    });

    /* ── Animation CSS + scrollbar notifList ── */
    const s = document.createElement('style');
    s.textContent = `
        @keyframes slideInRight{from{opacity:0;transform:translateX(60px)}to{opacity:1;transform:translateX(0)}}
        #notifList::-webkit-scrollbar{width:5px;}
        #notifList::-webkit-scrollbar-track{background:#f9fafb;}
        #notifList::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:4px;}
        #notifList::-webkit-scrollbar-thumb:hover{background:#9ca3af;}
    `;
    document.head.appendChild(s);
})();

/* ════════════════════════════════════════════════════════
   CHAT BOUTIQUE ↔ ENTREPRISE — depuis la cloche de notif
   ════════════════════════════════════════════════════════ */
(function () {
    const SHOP_ID = window.BQ_CFG.shopId;
    const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let _bqCompanyId      = null;
    let _bqCompanyName    = '';
    let _bqLastMsgTime    = null;
    let _bqInterval       = null;
    let _bqConfierDone    = false;
    let _bqSelectedOrderIds = new Set();
    let _bqZonesAll       = [];
    let _bqSelectedZone   = null;

    /* ── Ouvre le chat pour une entreprise donnée ── */
    window.bqOpenCompanyChat = function(companyId, companyName) {
        _bqCompanyId      = companyId;
        _bqCompanyName    = companyName || 'Entreprise';
        _bqLastMsgTime    = null;
        _bqConfierDone    = false;
        _bqSelectedOrderIds = new Set();

        /* Ferme le dropdown notif proprement (met à jour _notifOpen) */
        if (typeof window._closeNotifPanel === 'function') window._closeNotifPanel();

        /* Dismiss la notification cloche pour cette entreprise */
        if (typeof window._bqRemoveCompanyAlert === 'function') {
            window._bqRemoveCompanyAlert(companyId);
        }

        /* Header */
        document.getElementById('bqChatAv').innerHTML   = _SVG.bldg;
        document.getElementById('bqChatName').textContent = _bqCompanyName;

        /* Vider messages */
        document.getElementById('bqChatMsgList').innerHTML =
            '<div class="bq-chat-empty" id="bqChatEmpty">Chargement…</div>';

        /* Reset zone confier */
        _bqHideConfierZone();
        document.getElementById('bqOrdersList').innerHTML        = '';
        document.getElementById('bqZonePickerWrap').style.display = 'none';
        _bqZonesAll     = [];
        _bqSelectedZone = null;
        const _bqSrch = document.getElementById('bqZoneSearch');
        if (_bqSrch) _bqSrch.value = '';
        const _bqRes  = document.getElementById('bqZoneResults');
        if (_bqRes)  _bqRes.innerHTML = '';
        const _bqChip = document.getElementById('bqZoneSelectedChip');
        if (_bqChip) { _bqChip.style.display = 'none'; _bqChip.innerHTML = ''; }
        const _bqClr  = document.getElementById('bqZoneSearchClear');
        if (_bqClr)  _bqClr.style.display = 'none';
        const _bqSBox = document.getElementById('bqZoneSearchBox');
        if (_bqSBox) _bqSBox.style.display = 'block';
        const btnC = document.getElementById('bqBtnConfier');
        btnC.disabled  = false;
        btnC.classList.remove('done');
        btnC.innerHTML = _SVG.box + ' Confier la livraison à cette entreprise';

        /* Ouvrir (retire le display:none inline posé par défaut côté HTML) */
        const bqModalEl = document.getElementById('bqChatModal');
        bqModalEl.style.display = '';
        bqModalEl.classList.add('open');
        document.body.style.overflow = 'hidden';
        _bqAdjustPanel();
        const _bqCi = document.getElementById('bqChatInput');
        _bqCi.focus();
        _bqCi.addEventListener('focus', _bqScrollMsgsBottom, { once: false });

        /* Charger messages + commandes */
        bqLoadMessages(true);
        bqLoadPendingOrders();
        _bqInterval = setInterval(() => bqLoadMessages(false), 3000);
    };

    /* ── Charge les commandes non assignées ── */
    function bqLoadPendingOrders() {
        const zone = document.getElementById('bqConfierZone');
        const list = document.getElementById('bqOrdersList');

        fetch('/employe/orders/pending-json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(orders => {
            list.innerHTML = '';
            _bqSelectedOrderIds = new Set();
            if (!orders.length) { _bqHideConfierZone(); return; }

            /* ── "Tout sélectionner" bar (only when >1 orders) ── */
            if (orders.length > 1) {
                const bar = document.createElement('div');
                bar.style.cssText = 'display:flex;align-items:center;gap:8px;padding:6px 4px 8px;border-bottom:1px solid #e2e8f0;margin-bottom:6px;';
                bar.innerHTML = `<input type="checkbox" id="bqSelectAll" onchange="bqToggleAll(this)" style="width:15px;height:15px;accent-color:#059669;cursor:pointer;border-radius:3px;flex-shrink:0;"><label for="bqSelectAll" style="font-size:11px;font-weight:700;color:#6b7280;cursor:pointer;user-select:none;">Tout sélectionner</label><span id="bqSelCount" style="margin-left:auto;font-size:11px;font-weight:700;color:#059669;"></span>`;
                list.appendChild(bar);
            }

            orders.forEach(o => {
                const card = document.createElement('div');
                card.className = 'bq-order-card';
                card.dataset.orderId = o.id;
                const thumbHtml = o.photo
                    ? `<div class="bq-order-card-thumb"><img src="${o.photo}" alt="" onerror="this.parentElement.innerHTML=_SVG.tag"></div>`
                    : `<div class="bq-order-card-thumb">${_SVG.tag}</div>`;
                const addrHtml = o.address
                    ? `<div class="bq-order-card-address">${_SVG.pin} ${bqEsc(o.address)}</div>`
                    : '';
                card.innerHTML =
                    thumbHtml +
                    `<div class="bq-order-card-info">` +
                        `<div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;">` +
                            `<span class="bq-order-card-num">${o.num}</span>` +
                            `<span class="bq-order-card-client">${bqEsc(o.client)}</span>` +
                        `</div>` +
                        addrHtml +
                        `<div class="bq-order-card-amount">${o.total} ${o.devise || DEVISE}</div>` +
                    `</div>` +
                    `<div class="bq-order-card-check">✓</div>`;
                card.addEventListener('click', () => bqSelectOrderCard(card, o.id));
                list.appendChild(card);
            });
            _bqShowConfierZone();
            /* La zone vient d'apparaître et a réduit la zone messages → re-scroller en bas */
            requestAnimationFrame(() => {
                const ml = document.getElementById('bqChatMsgList');
                if (ml) ml.scrollTop = ml.scrollHeight;
            });
        })
        .catch(() => { _bqHideConfierZone(); });
    }

    /* ── Met à jour le bouton confier ── */
    function bqUpdateConfierBtn() {
        const n   = _bqSelectedOrderIds.size;
        const btn = document.getElementById('bqBtnConfier');
        if (!btn) return;
        btn.disabled  = n === 0 || _bqConfierDone;
        btn.innerHTML = n > 1 ? `${_SVG.box} Confier ${n} commandes` : _SVG.box + ' Confier la livraison à cette entreprise';
        const cnt = document.getElementById('bqSelCount');
        if (cnt) cnt.textContent = n > 0 ? `${n} sélectionnée${n>1?'s':''}` : '';
        const allCards = document.querySelectorAll('#bqOrdersList .bq-order-card');
        const chk = document.getElementById('bqSelectAll');
        if (chk) { chk.checked = allCards.length > 0 && n === allCards.length; chk.indeterminate = n > 0 && n < allCards.length; }
    }

    /* ── Tout sélectionner / désélectionner ── */
    window.bqToggleAll = function(chk) {
        document.querySelectorAll('#bqOrdersList .bq-order-card').forEach(card => {
            const id = parseInt(card.dataset.orderId);
            if (chk.checked) { _bqSelectedOrderIds.add(id); card.classList.add('selected'); }
            else             { _bqSelectedOrderIds.delete(id); card.classList.remove('selected'); }
        });
        bqUpdateConfierBtn();
        if (_bqSelectedOrderIds.size > 0) {
            if (document.getElementById('bqZonePickerWrap').style.display === 'none') bqLoadZones();
        } else {
            document.getElementById('bqZonePickerWrap').style.display = 'none';
        }
    };

    /* ── Sélectionne / désélectionne une commande ── */
    function bqSelectOrderCard(card, orderId) {
        const wasEmpty = _bqSelectedOrderIds.size === 0;
        if (_bqSelectedOrderIds.has(orderId)) {
            _bqSelectedOrderIds.delete(orderId);
            card.classList.remove('selected');
        } else {
            _bqSelectedOrderIds.add(orderId);
            card.classList.add('selected');
        }
        bqUpdateConfierBtn();
        const nowEmpty = _bqSelectedOrderIds.size === 0;
        if (!wasEmpty && nowEmpty) {
            document.getElementById('bqZonePickerWrap').style.display = 'none';
        } else if (wasEmpty && !nowEmpty) {
            bqLoadZones();
        }
    }

    /* ── Charge les zones de l'entreprise ── */
    function bqLoadZones() {
        const wrap = document.getElementById('bqZonePickerWrap');
        wrap.style.display = 'none';
        bqClearZoneSelection();

        if (!_bqCompanyId) return;

        fetch(`/company-zones/${_bqCompanyId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(zones => {
            if (!zones || !zones.length) return;
            _bqZonesAll = zones;
            bqFilterZones('');
            wrap.style.display = 'block';
        })
        .catch(() => {});
    }

    /* ── Filtre et affiche les zones en temps réel ── */
    window.bqFilterZones = function(raw) {
        const q       = (raw || '').toLowerCase().trim();
        const results = document.getElementById('bqZoneResults');
        const clrBtn  = document.getElementById('bqZoneSearchClear');
        if (!results) return;
        if (clrBtn) clrBtn.style.display = q ? 'block' : 'none';
        const list = q ? _bqZonesAll.filter(z => z.name.toLowerCase().includes(q)) : _bqZonesAll;
        if (!list.length) {
            results.innerHTML = `<div class="bq-zone-empty">Aucune zone trouvée pour "<strong>${q}</strong>"</div>`;
            return;
        }
        results.innerHTML = '';
        list.forEach(z => {
            const item = document.createElement('div');
            item.className = 'bq-zone-result';
            const color = z.color || '#059669';
            const price = new Intl.NumberFormat('fr-FR').format(z.price);
            item.innerHTML =
                `<span class="bq-zone-result-dot" style="background:${color};box-shadow:0 0 5px ${color}88;"></span>` +
                `<span class="bq-zone-result-name">${z.name}</span>` +
                `<div class="bq-zone-result-right">` +
                    `<div class="bq-zone-result-price">${price} ${DEVISE}</div>` +
                    `<div class="bq-zone-result-delay">~${z.estimated_minutes} min</div>` +
                `</div>`;
            item.addEventListener('click', () => bqSelectZone(z));
            item.addEventListener('touchend', e => { e.preventDefault(); bqSelectZone(z); });
            results.appendChild(item);
        });
    };

    /* ── Sélectionne une zone et affiche la fiche ── */
    window.bqSelectZone = function(z) {
        _bqSelectedZone = z;
        const searchBox = document.getElementById('bqZoneSearchBox');
        const chip      = document.getElementById('bqZoneSelectedChip');
        if (searchBox) searchBox.style.display = 'none';
        if (!chip) return;
        const color = z.color || '#059669';
        const price = new Intl.NumberFormat('fr-FR').format(z.price);
        chip.innerHTML =
            `<div class="bq-zone-chip">` +
                `<span class="bq-zone-chip-dot" style="background:${color};box-shadow:0 0 5px ${color}88;"></span>` +
                `<div class="bq-zone-chip-info">` +
                    `<div class="bq-zone-chip-name">${z.name}</div>` +
                    `<div class="bq-zone-chip-details">💰 ${price} ${DEVISE} &nbsp;·&nbsp; ⏱ ~${z.estimated_minutes} min</div>` +
                `</div>` +
                `<button class="bq-zone-chip-change" onclick="bqClearZoneSelection()">Changer</button>` +
            `</div>`;
        chip.style.display = 'block';
    };

    /* ── Efface la sélection et revient à la recherche ── */
    window.bqClearZoneSelection = function() {
        _bqSelectedZone = null;
        const searchBox = document.getElementById('bqZoneSearchBox');
        const chip      = document.getElementById('bqZoneSelectedChip');
        const input     = document.getElementById('bqZoneSearch');
        const clrBtn    = document.getElementById('bqZoneSearchClear');
        if (chip)    { chip.style.display = 'none'; chip.innerHTML = ''; }
        if (input)   input.value = '';
        if (clrBtn)  clrBtn.style.display = 'none';
        if (searchBox) searchBox.style.display = 'block';
        bqFilterZones('');
        setTimeout(() => input?.focus(), 50);
    };

    /* ── Vide le champ de recherche (bouton ✕) ── */
    window.bqZoneClearSearch = function() {
        const input  = document.getElementById('bqZoneSearch');
        const clrBtn = document.getElementById('bqZoneSearchClear');
        if (input)  input.value = '';
        if (clrBtn) clrBtn.style.display = 'none';
        bqFilterZones('');
        input?.focus();
    };

    /* ── Confie les commandes sélectionnées ── */
    window.bqConfierLivraison = async function() {
        if (_bqConfierDone) return;
        if (_bqSelectedOrderIds.size === 0) {
            const list = document.getElementById('bqOrdersList');
            list.style.outline = '2px solid #f87171';
            list.style.borderRadius = '9px';
            setTimeout(() => { list.style.outline = ''; list.style.borderRadius = ''; }, 1200);
            return;
        }

        const btn     = document.getElementById('bqBtnConfier');
        btn.disabled  = true;
        btn.innerHTML = _SVG.clock + ' Envoi…';

        const zoneId = _bqSelectedZone?.id || null;

        const ids = Array.from(_bqSelectedOrderIds);
        let successCount = 0;
        const successNums = [];

        for (const orderId of ids) {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('delivery_company_id', _bqCompanyId);
            if (zoneId)                  formData.append('delivery_zone_id', zoneId);
            if (_bqSelectedZone?.price)  formData.append('delivery_fee', _bqSelectedZone.price);
            try {
                const r    = await fetch(`/employe/orders/${orderId}/send-to-company`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData
                });
                const data = await r.json();
                if (data.success) {
                    successCount++;
                    const card = document.querySelector(`#bqOrdersList .bq-order-card[data-order-id="${orderId}"]`);
                    successNums.push(card?.querySelector('.bq-order-card-num')?.textContent || ('#' + orderId));
                }
            } catch(e) {}
        }

        if (successCount > 0) {
            _bqConfierDone = true;
            btn.classList.add('done');
            btn.innerHTML = _SVG.check + ' Confiée !';
            _bqHideConfierZone();

            bqRenderMessages([{
                id: 'local-' + Date.now(),
                from_type: 'system',
                body: successCount === 1
                    ? `${_SVG.check} Commande ${successNums[0]} confiée à ${_bqCompanyName}. Statut : En attente.`
                    : `${_SVG.check} ${successCount} commandes confiées à ${_bqCompanyName}. Statut : En attente.`,
                created_at: new Date().toISOString()
            }], false);

            setTimeout(() => location.reload(), 2000);
        } else {
            btn.disabled  = false;
            btn.innerHTML = _SVG.box + ' Confier la livraison à cette entreprise';
            alert('Erreur lors de la soumission. Veuillez réessayer.');
        }
    };

    /* ── Toggle / helpers zone livraison (mobile) ── */
    function _bqShowConfierZone() {
        const zone   = document.getElementById('bqConfierZone');
        const toggle = document.getElementById('bqConfierToggle');
        const badge  = document.getElementById('bqConfierBadge');
        if (!zone) return;
        zone.style.display = 'block';
        if (window.innerWidth <= 560 && toggle) {
            toggle.style.display = 'flex';
            if (badge) badge.classList.add('visible');
        }
    }
    function _bqHideConfierZone() {
        const zone   = document.getElementById('bqConfierZone');
        const toggle = document.getElementById('bqConfierToggle');
        if (zone)   { zone.style.display = 'none'; zone.classList.remove('zone-open'); }
        if (toggle) { toggle.style.display = 'none'; toggle.classList.remove('zone-open'); }
        const badge = document.getElementById('bqConfierBadge');
        if (badge)  badge.classList.remove('visible');
    }
    window.bqToggleConfierZone = function() {
        const zone   = document.getElementById('bqConfierZone');
        const toggle = document.getElementById('bqConfierToggle');
        const badge  = document.getElementById('bqConfierBadge');
        if (!zone || !toggle) return;
        const opening = !zone.classList.contains('zone-open');
        zone.classList.toggle('zone-open', opening);
        toggle.classList.toggle('zone-open', opening);
        if (opening && badge) badge.classList.remove('visible');
    };
    function _bqResetConfierToggle() {
        const zone   = document.getElementById('bqConfierZone');
        const toggle = document.getElementById('bqConfierToggle');
        if (zone)   zone.classList.remove('zone-open');
        if (toggle) toggle.classList.remove('zone-open');
        const badge = document.getElementById('bqConfierBadge');
        if (badge)  badge.classList.remove('visible');
    }

    /* ── Ajuste la hauteur du panel selon le viewport réel (iOS keyboard) ── */
    function _bqAdjustPanel() {
        if (window.innerWidth > 560) return;
        const overlay = document.getElementById('bqChatModal');
        const panel   = document.querySelector('.bq-chat-panel');
        if (!overlay || !panel) return;
        const vv = window.visualViewport || { offsetTop: 0, height: window.innerHeight };
        /* Déplace l'overlay pour ne couvrir que la zone visible (au-dessus du clavier) */
        overlay.style.top    = vv.offsetTop + 'px';
        overlay.style.height = vv.height + 'px';
        overlay.style.bottom = 'auto';
        /* Ajuste la hauteur du panel dans cette zone visible */
        const h = Math.floor(vv.height * 0.82);
        panel.style.height    = h + 'px';
        panel.style.maxHeight = h + 'px';
    }
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', _bqAdjustPanel);
        window.visualViewport.addEventListener('scroll', _bqAdjustPanel);
    }

    function _bqScrollMsgsBottom() {
        const msgs = document.getElementById('bqChatMsgList');
        setTimeout(() => { if (msgs) msgs.scrollTop = msgs.scrollHeight; }, 300);
    }

    /* ── Ferme le chat ── */
    window.bqCloseChatModal = function() {
        clearInterval(_bqInterval);
        _bqInterval = null;
        const overlay = document.getElementById('bqChatModal');
        overlay.classList.remove('open');
        overlay.style.display = 'none';
        overlay.style.top    = '';
        overlay.style.height = '';
        overlay.style.bottom = '';
        const panel = overlay.querySelector('.bq-chat-panel');
        if (panel) { panel.style.height = ''; panel.style.maxHeight = ''; }
        document.body.style.overflow = '';
    };

    document.getElementById('bqChatModal')?.addEventListener('click', function(e) {
        if (e.target === this) window.bqCloseChatModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') window.bqCloseChatModal();
    });

    /* ── Charge les messages ── */
    function bqLoadMessages(initial) {
        if (!_bqCompanyId) return;
        const url = new URL(`/employe/companies/${_bqCompanyId}/chat/messages`, location.origin);
        url.searchParams.set('shop_id', SHOP_ID);
        if (_bqLastMsgTime && !initial) url.searchParams.set('after', _bqLastMsgTime);

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            const msgs = data.messages || [];
            if (msgs.length) {
                bqRenderMessages(msgs, initial);
                _bqLastMsgTime = msgs[msgs.length - 1].created_at || null;
                /* Mémoriser le dernier message vu pour ce fil — évite fausse notif au retour */
                try {
                    const lastId = msgs[msgs.length - 1].id;
                    const seen   = JSON.parse(sessionStorage.getItem('bq_co_seen') || '{}');
                    seen[String(_bqCompanyId)] = lastId;
                    sessionStorage.setItem('bq_co_seen', JSON.stringify(seen));
                } catch(e) {}
            } else if (initial) {
                document.getElementById('bqChatMsgList').innerHTML =
                    '<div class="bq-chat-empty" id="bqChatEmpty">Aucun message. Commencez la discussion !</div>';
            }
        })
        .catch(() => {
            if (initial)
                document.getElementById('bqChatMsgList').innerHTML =
                    '<div class="bq-chat-empty" id="bqChatEmpty">Aucun message. Commencez la discussion !</div>';
        });
    }

    /* ── Rend les messages ── */
    function bqRenderMessages(msgs, replace) {
        const list = document.getElementById('bqChatMsgList');
        if (replace) list.innerHTML = '';
        const empty = list.querySelector('.bq-chat-empty');
        if (empty) empty.remove();

        msgs.forEach(m => {
            if (document.getElementById('bqmsg-' + m.id)) return;
            const role     = m.from_type || m.sender_role || 'shop';
            const isMine   = role === 'shop';
            const isSystem = role === 'system';
            const row      = document.createElement('div');
            row.id         = 'bqmsg-' + m.id;
            const text     = m.body || m.message || '';
            const timeStr  = m.created_at
                ? new Date(m.created_at).toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'})
                : '';

            if (isSystem) {
                row.className = 'bq-msg-system';
                row.innerHTML = `<div class="bq-msg-system-pill">${bqEsc(text)}</div>` +
                                (timeStr ? `<div class="bq-msg-system-time">${timeStr}</div>` : '');
            } else {
                row.className = 'bq-msg-row ' + (isMine ? 'mine' : 'theirs');
                row.innerHTML = `<div class="bq-msg-bubble">${bqEsc(text)}</div>` +
                                `<div class="bq-msg-meta">${isMine ? 'Vous' : _bqCompanyName} · ${timeStr}</div>`;
            }
            list.appendChild(row);
        });
        /* Scroll bas garanti après rendu DOM */
        requestAnimationFrame(() => { list.scrollTop = list.scrollHeight; });
    }

    function bqEsc(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Envoie un message ── */
    window.bqSendMsg = function() {
        const input   = document.getElementById('bqChatInput');
        const msg     = input.value.trim();
        if (!msg || !_bqCompanyId) return;

        const sendBtn = document.getElementById('bqChatSendBtn');
        sendBtn.disabled = true;

        fetch(`/employe/companies/${_bqCompanyId}/chat/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message: msg, shop_id: SHOP_ID })
        })
        .then(r => r.json())
        .then(data => {
            input.value = '';
            sendBtn.disabled = false;
            if (data.ok && data.message) {
                bqRenderMessages([data.message], false);
                _bqLastMsgTime = data.message.created_at || null;
            } else {
                bqLoadMessages(false);
            }
        })
        .catch(() => { sendBtn.disabled = false; });
    };
})();

/* ════════════════════════════════════════════════════════
   KPI TEMPS RÉEL — polling 30s
   ════════════════════════════════════════════════════════ */
(function () {
    const KPI_URL = window.BQ_CFG.kpiLiveUrl;
    const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function fmt(n) {
        return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function set(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function setClass(id, cls) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('up', 'down', 'flat');
        el.classList.add(cls);
    }

    function pollKpi() {
        fetch(KPI_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.ok ? r.json() : null)
        .then(d => {
            if (!d) return;

            /* KPI Revenu net */
            set('kpiCaVal', d.ca_month);
            const caCls = d.ca_delta >= 0 ? 'up' : 'down';
            setClass('kpiCaDelta', caCls);
            set('kpiCaDelta', (d.ca_delta >= 0 ? '↑' : '↓') + ' ' + Math.abs(d.ca_delta) + '% vs mois précédent');

            /* KPI Commandes */
            set('kpiCmdVal', d.cmd_month);
            const cmdCls = d.cmd_today >= d.cmd_yest ? 'up' : 'down';
            setClass('kpiCmdDelta', cmdCls);
            set('kpiCmdDelta', (d.cmd_today >= d.cmd_yest ? '↑' : '↓') + ' ' + d.cmd_today + ' aujourd\'hui');

            /* KPI Panier moyen */
            set('kpiPanierVal', d.panier);
            setClass('kpiPanierDelta', d.panier_delta >= 0 ? 'up' : 'down');
            set('kpiPanierDelta', (d.panier_delta >= 0 ? '↑' : '↓') + ' ' + Math.abs(d.panier_delta) + '% vs mois précédent');

            /* KPI Taux livraison */
            set('kpiTauxVal', d.taux_liv + '%');
            set('kpiTauxUnit', d.livres + ' / ' + d.total_cmd_month + ' livrées');
            const tauxOk = d.taux_liv >= 90;
            setClass('kpiTauxDelta', tauxOk ? 'up' : 'down');
            set('kpiTauxDelta', tauxOk ? '✓ Excellent' : '⚠ À améliorer');

            /* Today CA */
            set('todayCaVal', d.ca_today);
            const todayCaCls = d.ca_today_delta > 0 ? 'up' : (d.ca_today_delta < 0 ? 'down' : 'flat');
            setClass('todayCaDelta', todayCaCls);
            if (d.ca_today_delta > 0)      set('todayCaDelta', '↑ +' + d.ca_today_delta + '% vs hier');
            else if (d.ca_today_delta < 0) set('todayCaDelta', '↓ ' + d.ca_today_delta + '% vs hier');
            else                           set('todayCaDelta', '— Même niveau qu\'hier');

            /* Today commandes */
            set('todayCmdVal', d.cmd_today);
            const todayCmdCls = d.cmd_today >= d.cmd_yest ? 'up' : 'down';
            setClass('todayCmdDelta', todayCmdCls);
            const diff = Math.abs(d.cmd_today - d.cmd_yest);
            if (d.cmd_today > d.cmd_yest)      set('todayCmdDelta', '↑ +' + diff + ' vs hier');
            else if (d.cmd_today < d.cmd_yest) set('todayCmdDelta', '↓ ' + diff + ' de moins vs hier');
            else                               set('todayCmdDelta', '— Même niveau qu\'hier');

            /* Kanban */
            if (d.kanban) {
                Object.entries(d.kanban).forEach(([key, cnt]) => {
                    const el = document.getElementById('kb-' + key);
                    if (el) el.textContent = cnt;
                });
            }

            /* Flèche chart Revenus 7j (today vs hier) */
            const rcBadge = document.getElementById('rcDeltaBadge');
            if (rcBadge) {
                const v = d.ca_today_delta;
                rcBadge.classList.remove('up','down','flat');
                if (v > 0)      { rcBadge.classList.add('up');   rcBadge.textContent = '↑ +' + v + '% aujourd\'hui vs hier'; }
                else if (v < 0) { rcBadge.classList.add('down'); rcBadge.textContent = '↓ ' + v + '% aujourd\'hui vs hier'; }
                else            { rcBadge.classList.add('flat'); rcBadge.textContent = '→ Stable aujourd\'hui'; }
            }

            /* Flèche chart Commandes 7j (today vs hier) */
            const cmdBadge = document.getElementById('cmdDeltaBadge');
            if (cmdBadge) {
                const diff = d.cmd_today - d.cmd_yest;
                cmdBadge.classList.remove('up','down','flat');
                if (diff > 0)      { cmdBadge.classList.add('up');   cmdBadge.textContent = '↑ ' + d.cmd_today + ' aujourd\'hui'; }
                else if (diff < 0) { cmdBadge.classList.add('down'); cmdBadge.textContent = '↓ ' + d.cmd_today + ' aujourd\'hui'; }
                else               { cmdBadge.classList.add('flat'); cmdBadge.textContent = '→ ' + d.cmd_today + ' aujourd\'hui'; }
            }
        })
        .catch(() => {});
    }

    /* Premier appel après 30s puis toutes les 30s */
    setInterval(pollKpi, 30000);
})();

/* ── Auto-refresh toutes les 90 secondes ── */
(function () {
    const INTERVAL = 90;
    const el = document.getElementById('dashLastUpdate');

    function fmt(d) {
        return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
    }

    let remaining = INTERVAL;
    if (el) el.textContent = '· mis à jour à ' + fmt(new Date());

    const tick = setInterval(function () {
        remaining--;
        if (remaining <= 0) {
            clearInterval(tick);
            location.reload();
        }
        if (el && remaining <= 10) {
            el.textContent = '· actualisation dans ' + remaining + 's…';
        }
    }, 1000);
})();
