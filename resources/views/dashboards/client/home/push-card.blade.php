<div id="notifSettingsCard" style="display:flex;align-items:center;gap:14px;background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px 18px;margin-bottom:16px;">
    <span style="display:inline-flex;color:#F59E0B">{!! \App\Support\IconLibrary::svg('bell', '', 26) !!}</span>
    <div style="flex:1;min-width:160px">
        <div style="font-weight:700;font-size:14px;color:var(--text)">Notifications push</div>
        <div id="notifSettingsSub" style="font-size:13px;color:var(--muted);margin-top:2px">Recevez vos commandes et messages en temps réel, même application fermée.</div>
    </div>
    <button type="button" id="notifSettingsBtn"
            style="background:#059669;color:#fff;border:none;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
        Activer les notifications
    </button>
</div>
<script>
(function() {
    var card = document.getElementById('notifSettingsCard');
    var btn  = document.getElementById('notifSettingsBtn');
    var sub  = document.getElementById('notifSettingsSub');
    if (!card) return;

    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
        card.style.display = 'none';
        return;
    }

    function refresh() {
        if (Notification.permission === 'granted' && localStorage.getItem('push_subscribed')) {
            sub.textContent = 'Notifications activées ✓';
            btn.textContent = 'Déjà activées';
            btn.disabled = true;
            btn.style.opacity = '.6';
            btn.style.cursor = 'default';
        } else if (Notification.permission === 'denied') {
            sub.textContent = 'Bloquées dans les réglages de votre navigateur — autorisez les notifications pour ce site pour les activer.';
            btn.style.display = 'none';
        }
    }

    btn.addEventListener('click', function() {
        btn.disabled = true;
        btn.textContent = 'Activation…';
        Promise.resolve(window.enablePushNotifications ? window.enablePushNotifications() : null)
            .finally(function() {
                btn.disabled = false;
                btn.textContent = 'Activer les notifications';
                refresh();
            });
    });

    refresh();
})();
</script>
