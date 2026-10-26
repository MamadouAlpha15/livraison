/* ══════════════════════════════════════════
   POLLING NOTIFICATIONS MESSAGES (toutes les 3s)
══════════════════════════════════════════ */
let _badgeSetAt   = 0;        /* timestamp où le badge est apparu */
const _BADGE_MIN  = 12000;    /* 12 s minimum avant qu'un poll puisse effacer le badge */

/* Si la page s'est chargée avec le badge déjà visible (rendu PHP),
   on active la protection immédiatement pour éviter qu'un premier poll
   avec réponse en cache efface le badge avant même d'avoir pollé le serveur. */
if (document.getElementById('navMsgBadge')?.classList.contains('show')) {
    _badgeSetAt = Date.now();
}

async function pollClientMessages(forceIfZero = false) {
    try {
        /* ?_t= évite la mise en cache agressive des navigateurs mobiles (iOS Safari) */
        const res = await fetch(`{{ route("client.messages.client.poll") }}?_t=${Date.now()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': _csrfToken,
                'Accept': 'application/json',
            }
        });
        if (!res.ok) return;
        const data = await res.json();

        /* Sécurité : si la réponse ne contient pas "unread", on ignore */
        if (typeof data.unread === 'undefined') return;

        const badge = document.getElementById('navMsgBadge');
        if (!badge) return;

        if (data.unread > 0) {
            /* Nouveau message : afficher et mémoriser le moment */
            if (!_badgeSetAt) _badgeSetAt = Date.now();
            badge.textContent = data.unread;
            badge.classList.add('show');
        } else {
            /* Zéro non-lus : n'effacer que si l'utilisateur a explicitement lu
               OU si le badge est affiché depuis plus de 12 s (vraiment lus ailleurs) */
            if (forceIfZero || Date.now() - _badgeSetAt >= _BADGE_MIN) {
                _badgeSetAt = 0;
                badge.textContent = '';
                badge.classList.remove('show');
            }
            /* Sinon : on garde le badge, c'est peut-être un faux 0 transitoire */
        }

        /* Pulse si nouveau message très récent */
        if (data.has_new) {
            const btn = document.querySelector('.nav-msg-btn');
            if (btn) {
                btn.style.animation = 'none';
                btn.offsetHeight;
                btn.style.animation = 'msgPulse .6s ease 3';
            }
        }
    } catch(e) {}
}

const _pulseStyle = document.createElement('style');
_pulseStyle.textContent = `
@keyframes msgPulse {
    0%,100% { transform: scale(1); }
    50%      { transform: scale(1.18); }
}`;
document.head.appendChild(_pulseStyle);

setInterval(pollClientMessages, 3000);
