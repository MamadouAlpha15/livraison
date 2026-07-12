const CACHE = 'shopio-v9';
const OFFLINE_URL = '/';

const PRECACHE = [
    '/',
    '/images/shopio-logo-192.png',
];

/* ── Installation ── */
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE)
            .then(c => c.addAll(PRECACHE).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

/* ── Activation ── */
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

/* ── Fetch : réseau d'abord, cache en fallback ── */
self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;
    if (e.request.url.includes('/api/') || e.request.url.includes('poll')) return;

    e.respondWith(
        fetch(e.request)
            .then(res => {
                if (res.ok) {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                }
                return res;
            })
            .catch(() => caches.match(e.request).then(r => r || caches.match(OFFLINE_URL)))
    );
});

/* ── Push Notifications ── */
self.addEventListener('push', e => {
    const data = e.data ? e.data.json() : { title: 'Shopio', body: 'Nouvelle notification', badge: 1 };

    const options = {
        body:    data.body  || 'Vous avez une nouvelle notification.',
        icon:    '/images/shopio-logo-192.png',
        badge:   '/images/shopio-logo-96.png',
        vibrate: [200, 100, 200],
        data:    { url: data.url || '/' },
        actions: [
            { action: 'open',  title: 'Voir' },
            { action: 'close', title: 'Fermer' },
        ],
    };

    const badgeCount = parseInt(data.badge) || 0;

    e.waitUntil(
        Promise.all([
            self.registration.showNotification(data.title || 'Shopio', options),
            badgeCount > 0 && 'setAppBadge' in self.navigator
                ? self.navigator.setAppBadge(badgeCount).catch(() => {})
                : Promise.resolve(),
        ])
    );
});

/* ── Clic sur notification ── */
self.addEventListener('notificationclick', e => {
    e.notification.close();
    const url = e.notification.data?.url || '/';

    if (e.action === 'close') return;

    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(list => {
                for (const client of list) {
                    if (client.url.includes(self.location.origin)) {
                        client.focus();
                        client.navigate(url);
                        return;
                    }
                }
                return clients.openWindow(url);
            })
    );
});

/* ── Message depuis la page (mise à jour du badge) ── */
self.addEventListener('message', e => {
    if (e.data?.type === 'SET_BADGE' && 'setAppBadge' in self.navigator) {
        const count = parseInt(e.data.count) || 0;
        count > 0
            ? self.navigator.setAppBadge(count).catch(() => {})
            : self.navigator.clearAppBadge().catch(() => {});
    }
    if (e.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
