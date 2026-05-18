<?php $__env->startSection('title', 'Accueil — Marketplace'); ?>
<?php $bodyClass = 'is-dashboard'; ?>

<?php $__env->startPush('styles'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --orange:     #f06a0f;
    --orange-dk:  #d45a08;
    --orange-lt:  #fff3ec;
    --navy:       #2c3e50;
    --navy-2:     #34495e;
    --grey:       #f4f6f8;
    --grey-2:     #e8ecf0;
    --border:     #dde3ea;
    --text:       #2c3e50;
    --text-2:     #5a6a7a;
    --muted:      #8a9bb0;
    --surface:    #ffffff;
    --font:       'Open Sans', sans-serif;
    --display:    'Nunito', sans-serif;
    --r:          10px;
    --r-sm:       7px;
    --shadow-sm:  0 1px 4px rgba(0,0,0,.07);
    --shadow:     0 4px 16px rgba(0,0,0,.1);
    --shadow-lg:  0 8px 32px rgba(0,0,0,.13);
    --nav-h:      60px;
}

html { font-family: var(--font); scroll-behavior: smooth; overflow-x: hidden; }
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; overflow-x: hidden; }

/* ══ NAVBAR ══ */
.nav {
    height: var(--nav-h);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 32px; gap: 20px;
    position: sticky; top: 0; z-index: 100;
    box-shadow: var(--shadow-sm);
}
.nav-logo {
    font-family: var(--display); font-weight: 900; font-size: 22px;
    text-decoration: none; flex-shrink: 0;
    display: flex; align-items: center; gap: 2px;
}
.nav-logo span:first-child { color: var(--navy); }
.nav-logo span:last-child  { color: var(--orange); }
.nav-links {
    display: flex; align-items: center; gap: 4px;
    flex: 1;
}
.nav-link {
    padding: 8px 14px; border-radius: var(--r-sm);
    font-size: 13.5px; font-weight: 600; color: var(--text-2);
    text-decoration: none; transition: all .15s;
    display: flex; align-items: center; gap: 6px;
    white-space: nowrap;
}
.nav-link:hover { background: var(--grey); color: var(--text); }
.nav-link.active { color: var(--orange); }

/* Barre de recherche */
.nav-search {
    flex: 1; max-width: 420px;
    display: flex; align-items: center;
    border: 1.5px solid var(--border);
    border-radius: 50px; overflow: hidden;
    background: var(--grey);
    transition: border-color .2s, box-shadow .2s;
}
.nav-search:focus-within {
    border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(240,106,15,.1);
    background: var(--surface);
}
.nav-search input {
    flex: 1; border: none; outline: none; background: transparent;
    padding: 9px 16px; font-size: 13px; font-family: var(--font);
    color: var(--text);
}
.nav-search input::placeholder { color: var(--muted); }
.nav-search-btn {
    padding: 9px 16px; background: var(--orange); border: none;
    cursor: pointer; color: #fff; font-size: 14px;
    transition: background .15s;
}
.nav-search-btn:hover { background: var(--orange-dk); }

/* Nav actions */
.nav-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.nav-orders-btn {
    display: flex; align-items: center; gap: 7px;
    padding: 8px 16px; border-radius: 50px;
    font-size: 12.5px; font-weight: 700; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text); cursor: pointer; text-decoration: none;
    transition: all .15s;
}
.nav-orders-btn:hover { border-color: var(--orange); color: var(--orange); background: var(--orange-lt); }

/* Bouton messages navbar */
.nav-msg-btn {
    position: relative;
    width: 38px; height: 38px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; cursor: pointer;
    transition: all .15s; flex-shrink: 0;
}
.nav-msg-btn:hover { border-color: var(--orange); background: var(--orange-lt); }
.nav-msg-badge {
    position: absolute; top: -4px; right: -4px;
    background: var(--orange); color: #fff;
    font-size: 9px; font-weight: 800;
    border-radius: 20px; padding: 1px 5px;
    min-width: 16px; text-align: center;
    font-family: monospace; border: 1.5px solid var(--surface);
    display: none;
}
.nav-msg-badge.show { display: block; }

.nav-av-wrap { position: relative; }
.nav-av {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; color: #fff;
    font-family: var(--display); cursor: pointer;
    border: 2px solid var(--surface);
    box-shadow: 0 0 0 2px var(--orange);
}
.nav-av-menu {
    position: absolute; top: calc(100% + 10px); right: 0;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); box-shadow: var(--shadow-lg);
    min-width: 200px; padding: 8px; display: none; z-index: 200;
    animation: dropIn .18s ease;
}
.nav-av-menu.open { display: block; }
@keyframes dropIn { from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)} }
.nav-av-menu a, .nav-av-menu button {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 12px; border-radius: var(--r-sm);
    font-size: 13px; font-weight: 500; color: var(--text-2);
    text-decoration: none; background: none; border: none;
    width: 100%; cursor: pointer; font-family: var(--font);
    transition: background .12s;
}
.nav-av-menu a:hover, .nav-av-menu button:hover { background: var(--grey); color: var(--text); }
.nav-av-menu .sep { height: 1px; background: var(--border); margin: 4px 0; }
.nav-av-menu .logout { color: #e53e3e; }
.nav-av-menu .logout:hover { background: #fff5f5; }

/* ══ DRAWER MESSAGES ══ */
.msg-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.35); z-index: 400;
}
.msg-overlay.open { display: block; }

.msg-drawer {
    position: fixed; top: 0; right: 0; bottom: 0;
    width: 420px; max-width: 100vw;
    background: var(--surface);
    box-shadow: -4px 0 32px rgba(0,0,0,.15);
    z-index: 500;
    display: flex; flex-direction: column;
    transform: translateX(100%);
    transition: transform .28s cubic-bezier(.23,1,.32,1);
    visibility: hidden;
}
.msg-drawer.open { transform: translateX(0); visibility: visible; }

.msg-drawer-hd {
    padding: 16px 18px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 10px;
    background: var(--surface);
    flex-shrink: 0;
}
.msg-drawer-title {
    font-family: var(--display); font-size: 16px; font-weight: 800;
    color: var(--text); flex: 1;
}
.msg-drawer-badge {
    background: var(--orange); color: #fff;
    font-size: 10px; font-weight: 700;
    border-radius: 20px; padding: 2px 8px;
    font-family: monospace;
}
.msg-drawer-close {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1px solid var(--border); background: var(--grey);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: var(--text-2);
    transition: all .15s; flex-shrink: 0;
}
.msg-drawer-close:hover { background: #fee2e2; border-color: #fca5a5; color: #e53e3e; }

/* Liste conversations */
.msg-conv-list {
    flex: 1; overflow-y: auto;
    scrollbar-width: thin; scrollbar-color: var(--border) transparent;
}
.msg-conv-list::-webkit-scrollbar { width: 4px; }
.msg-conv-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

.msg-conv-item {
    padding: 13px 18px;
    border-bottom: 1px solid var(--grey);
    cursor: pointer; transition: background .12s;
    display: flex; align-items: center; gap: 12px;
}
.msg-conv-item:hover { background: var(--grey); }
.msg-conv-item.active { background: var(--orange-lt); border-left: 3px solid var(--orange); }
.msg-conv-item.has-unread { background: #fff8f3; }

.msg-conv-av {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, var(--navy), var(--navy-2));
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800; color: #fff;
    flex-shrink: 0; position: relative; overflow: hidden;
}
.msg-conv-av img { width: 100%; height: 100%; object-fit: cover; }
.msg-conv-av-dot {
    position: absolute; bottom: 1px; right: 1px;
    width: 10px; height: 10px; border-radius: 50%;
    background: var(--orange); border: 2px solid var(--surface);
}

.msg-conv-info { flex: 1; min-width: 0; }
.msg-conv-name {
    font-size: 13.5px; font-weight: 700; color: var(--text);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.msg-conv-prod {
    font-size: 11px; color: var(--muted); margin-top: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.msg-conv-preview {
    font-size: 12px; color: var(--muted);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    max-width: 220px;
}
.msg-conv-meta {
    display: flex; flex-direction: column; align-items: flex-end; gap: 4px;
    flex-shrink: 0;
}
.msg-conv-time { font-size: 10px; color: var(--muted); }
.msg-conv-unread {
    background: var(--orange); color: #fff;
    font-size: 9px; font-weight: 800; border-radius: 20px;
    padding: 1px 6px; font-family: monospace; min-width: 16px; text-align: center;
}

.msg-conv-empty {
    padding: 48px 20px; text-align: center;
    color: var(--muted);
}
.msg-conv-empty-ico { font-size: 40px; display: block; opacity: .3; margin-bottom: 12px; }
.msg-conv-empty-txt { font-size: 13.5px; }

/* ══ MODAL DISCUSSION ══ */
.msg-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 600;
    align-items: flex-end; justify-content: center;
    padding: 0;
}
.msg-modal-overlay.open { display: flex; }

.msg-modal {
    background: var(--surface);
    border-radius: var(--r) var(--r) 0 0;
    width: 100%; max-width: 560px;
    height: 82vh; max-height: 680px;
    display: flex; flex-direction: column;
    box-shadow: 0 -8px 40px rgba(0,0,0,.2);
    animation: slideUp .25s ease;
}
@keyframes slideUp { from{transform:translateY(100%)}to{transform:translateY(0)} }

@media (min-width: 600px) {
    .msg-modal-overlay {
        align-items: center;
        padding: 20px;
    }
    .msg-modal {
        border-radius: var(--r);
        height: 76vh;
    }
}

.msg-modal-hd {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 12px;
    flex-shrink: 0;
}
.msg-modal-av {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, var(--navy), var(--navy-2));
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; color: #fff;
    flex-shrink: 0; overflow: hidden;
}
.msg-modal-av img { width: 100%; height: 100%; object-fit: cover; }
.msg-modal-info { flex: 1; min-width: 0; }
.msg-modal-name { font-size: 14px; font-weight: 700; color: var(--text); }
.msg-modal-prod {
    font-size: 11px; color: var(--muted);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.msg-modal-back {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1px solid var(--border); background: var(--grey);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 14px; color: var(--text-2); transition: all .15s; flex-shrink: 0;
}
.msg-modal-back:hover { background: var(--orange-lt); border-color: var(--orange); color: var(--orange); }
.msg-modal-close {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1px solid var(--border); background: var(--grey);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: var(--text-2); transition: all .15s; flex-shrink: 0;
}
.msg-modal-close:hover { background: #fee2e2; border-color: #fca5a5; color: #e53e3e; }

/* Bande produit */
.msg-prod-bar {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 18px; background: var(--grey);
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.msg-prod-img {
    width: 38px; height: 38px; border-radius: var(--r-sm);
    object-fit: cover; border: 1px solid var(--border); flex-shrink: 0;
}
.msg-prod-ph {
    width: 38px; height: 38px; border-radius: var(--r-sm);
    background: var(--grey-2); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.msg-prod-name { font-size: 12px; font-weight: 700; color: var(--text); }
.msg-prod-price { font-size: 11px; font-weight: 800; color: var(--orange); font-family: monospace; }
.msg-prod-link {
    font-size: 11px; color: var(--orange); font-weight: 600;
    text-decoration: none; margin-left: auto; white-space: nowrap;
}
.msg-prod-link:hover { text-decoration: underline; }

/* Thread messages */
.msg-thread {
    flex: 1; overflow-y: auto; padding: 16px 18px;
    display: flex; flex-direction: column-reverse; /* dernier message EN HAUT */
    gap: 10px; background: #f6f8fa;
    scrollbar-width: thin; scrollbar-color: var(--border) transparent;
}
.msg-thread::-webkit-scrollbar { width: 4px; }
.msg-thread::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

.msg-bubble-row { display: flex; gap: 8px; max-width: 82%; }
.msg-bubble-row.from-me { margin-left: auto; flex-direction: row-reverse; }

.msg-bubble-av {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 800; color: #fff;
    flex-shrink: 0; align-self: flex-end;
}
.msg-bubble-row.from-me .msg-bubble-av    { background: linear-gradient(135deg, var(--orange), var(--orange-dk)); }
.msg-bubble-row.from-them .msg-bubble-av  { background: linear-gradient(135deg, var(--navy), var(--navy-2)); }

.msg-bubble {
    padding: 9px 13px; border-radius: 16px;
    font-size: 13px; line-height: 1.55; word-break: break-word;
}
.msg-bubble-row.from-me .msg-bubble {
    background: var(--orange); color: #fff;
    border-bottom-right-radius: 3px;
}
.msg-bubble-row.from-them .msg-bubble {
    background: var(--surface); color: var(--text);
    border: 1px solid var(--border);
    border-bottom-left-radius: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.msg-meta { display: flex; align-items: center; gap: 5px; margin-top: 3px; }
.msg-time { font-size: 9.5px; color: var(--muted); }
.msg-bubble-row.from-me .msg-meta { justify-content: flex-end; }
.msg-read { font-size: 10px; color: #6ee7b7; }

/* Zone réponse */
.msg-reply-zone {
    padding: 12px 18px;
    border-top: 1px solid var(--border);
    background: var(--surface);
    display: flex; gap: 10px; align-items: flex-end;
    flex-shrink: 0;
}
.msg-reply-input {
    flex: 1; padding: 10px 14px;
    border: 1.5px solid var(--border); border-radius: 22px;
    font-size: 13px; font-family: var(--font); color: var(--text);
    background: var(--grey); outline: none; resize: none;
    min-height: 42px; max-height: 100px; line-height: 1.5;
    transition: border-color .15s, background .15s;
}
.msg-reply-input:focus {
    border-color: var(--orange); background: var(--surface);
    box-shadow: 0 0 0 3px rgba(240,106,15,.1);
}
.msg-send-btn {
    width: 42px; height: 42px; border-radius: 50%;
    background: var(--orange); color: #fff; border: none;
    cursor: pointer; font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .15s;
    box-shadow: 0 2px 8px rgba(240,106,15,.3);
}
.msg-send-btn:hover { background: var(--orange-dk); transform: scale(1.08); }

/* ══ HERO BANNER ══ */
.hero {
    background: linear-gradient(135deg, var(--navy) 0%, #3d5a73 100%);
    margin: 0 32px 24px;
    border-radius: var(--r);
    padding: 36px 40px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 24px; overflow: hidden; position: relative;
    margin-top: 24px;
}
.hero::before {
    content: '';
    position: absolute; right: -60px; top: -60px;
    width: 280px; height: 280px; border-radius: 50%;
    background: rgba(255,255,255,.04); pointer-events: none;
}
.hero::after {
    content: '';
    position: absolute; right: 100px; bottom: -80px;
    width: 200px; height: 200px; border-radius: 50%;
    background: rgba(240,106,15,.12); pointer-events: none;
}
.hero-text { flex: 1; position: relative; z-index: 1; }
.hero-title {
    font-family: var(--display); font-weight: 900;
    font-size: clamp(26px, 3.5vw, 40px);
    color: #fff; line-height: 1.15; margin-bottom: 12px;
    letter-spacing: -.5px;
}
.hero-btns { display: flex; gap: 12px; flex-wrap: wrap; }
.hero-btn-primary {
    padding: 12px 24px; border-radius: 50px;
    font-size: 13.5px; font-weight: 700; font-family: var(--font);
    background: var(--orange); color: #fff; border: none;
    cursor: pointer; text-decoration: none; transition: all .15s;
    display: inline-flex; align-items: center; gap: 6px;
}
.hero-btn-primary:hover { background: var(--orange-dk); transform: scale(1.03); color: #fff; }
.hero-btn-secondary {
    padding: 12px 24px; border-radius: 50px;
    font-size: 13.5px; font-weight: 700; font-family: var(--font);
    background: rgba(255,255,255,.12); color: #fff;
    border: 1.5px solid rgba(255,255,255,.25);
    cursor: pointer; text-decoration: none; transition: all .15s;
    display: inline-flex; align-items: center; gap: 6px;
    backdrop-filter: blur(8px);
}
.hero-btn-secondary:hover { background: rgba(255,255,255,.2); color: #fff; }
.hero-right {
    display: flex; align-items: center; gap: 16px;
    flex-shrink: 0; position: relative; z-index: 1;
}
.hero-icon-box {
    width: 80px; height: 80px; border-radius: 20px;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 32px; flex-shrink: 0;
    animation: floatBox 3.5s ease-in-out infinite;
}
.hero-icon-box:nth-child(2) { animation-delay: -1.5s; width: 70px; height: 70px; font-size: 28px; }
.hero-icon-box:nth-child(3) { animation-delay: -3s; width: 76px; height: 76px; font-size: 30px; }
@keyframes floatBox { 0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)} }

/* ══ ÉTAPES ══ */
.steps {
    display: flex; align-items: center; gap: 0;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); margin: 0 32px 24px;
    padding: 20px 32px; box-shadow: var(--shadow-sm);
}
.step { display: flex; align-items: center; gap: 14px; flex: 1; }
.step-ico {
    width: 48px; height: 48px; border-radius: 12px;
    background: var(--orange-lt); display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.step-title { font-size: 13px; font-weight: 700; color: var(--text); }
.step-sub   { font-size: 11.5px; color: var(--muted); margin-top: 2px; }
.step-arrow { font-size: 18px; color: var(--orange); padding: 0 14px; flex-shrink: 0; }

/* ══ MAIN CONTENT ══ */
.c-main { padding: 0 32px 60px; }

.sec-hd {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; gap: 12px;
}
.sec-title {
    font-family: var(--display); font-size: 19px; font-weight: 800;
    color: var(--text); letter-spacing: -.3px;
}
.sec-title strong { color: var(--orange); }
.sec-link { font-size: 12.5px; font-weight: 700; color: var(--orange); text-decoration: none; }
.sec-link:hover { text-decoration: underline; }

/* ══ COMMANDES RÉCENTES ══ */
.orders-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); margin-bottom: 28px;
}
.orders-card-hd {
    padding: 14px 20px; background: var(--grey);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.orders-card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.order-row {
    display: flex; align-items: center; gap: 14px;
    padding: 13px 20px; border-bottom: 1px solid #f3f6f9;
    transition: background .12s; text-decoration: none; color: inherit;
}
.order-row:last-child { border-bottom: none; }
.order-row:hover { background: var(--grey); }
.order-ico {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}
.order-info { flex: 1; min-width: 0; }
.order-ref  { font-size: 12.5px; font-weight: 700; color: var(--text); font-family: monospace; }
.order-shop { font-size: 11.5px; color: var(--muted); margin-top: 1px; }
.order-amount { font-size: 13.5px; font-weight: 700; color: var(--text); font-family: monospace; white-space: nowrap; }
.order-pill {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
    white-space: nowrap;
}
.pill-livree    { background: #d1fae5; color: #065f46; }
.pill-pending   { background: #fef3c7; color: #92400e; }
.pill-livraison { background: #dbeafe; color: #1e40af; }
.pill-cancelled { background: #fee2e2; color: #991b1b; }

/* ══ FILTRES CATÉGORIES ══ */
.cats {
    display: flex; gap: 8px; margin-bottom: 20px;
    overflow-x: auto; padding-bottom: 6px;
    scrollbar-width: none; -ms-overflow-style: none;
}
.cats::-webkit-scrollbar { display: none; }

/* ── Pill ── */
.cat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 50px; flex-shrink: 0;
    font-size: 13px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text-2); cursor: pointer; white-space: nowrap;
    text-decoration: none; transition: all .18s;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
    outline: none;
    /* bouton reset */
    appearance: none; -webkit-appearance: none;
}
.cat-pill:hover {
    border-color: var(--orange); color: var(--orange);
    background: var(--orange-lt);
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(240,106,15,.18);
}
.cat-pill.active {
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    color: #fff; border-color: var(--orange-dk);
    box-shadow: 0 4px 14px rgba(240,106,15,.35);
    transform: translateY(-1px);
}
.cat-pill.active:hover { opacity: .9; }

/* Compteur dans la pill */
.cat-pill-cnt {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 18px; height: 18px; padding: 0 5px;
    border-radius: 10px; font-size: 10.5px; font-weight: 700;
    background: rgba(0,0,0,.08); color: inherit;
    transition: all .18s;
}
.cat-pill.active .cat-pill-cnt { background: rgba(255,255,255,.25); }

/* ══ GRILLE BOUTIQUES ══ */
.shops-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 28px;
}

/* ══ CARD BOUTIQUE ══ */
.shop-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, transform .2s, border-color .2s;
    text-decoration: none; color: inherit;
    display: flex; flex-direction: column;
}
.shop-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
    border-color: var(--orange);
}
.shop-card-img { height: 160px; overflow: hidden; position: relative; flex-shrink: 0; }
.shop-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
.shop-card:hover .shop-card-img img { transform: scale(1.07); }
.shop-card-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 42px; }
.bg-food    { background: linear-gradient(135deg, #e8f5e9, #c8e6c9); }
.bg-fashion { background: linear-gradient(135deg, #fce4ec, #f8bbd0); }
.bg-tech    { background: linear-gradient(135deg, #e3f2fd, #bbdefb); }
.bg-beauty  { background: linear-gradient(135deg, #fff8e1, #ffecb3); }
.bg-default { background: linear-gradient(135deg, #f3f4f6, #e5e7eb); }

.shop-card-badge {
    position: absolute; top: 10px; left: 10px;
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10px; font-weight: 700; padding: 4px 9px;
    border-radius: 20px; backdrop-filter: blur(6px);
}
.badge-open { background: rgba(16,185,129,.9); color: #fff; }
.badge-open::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: #fff; animation: pulse 1.8s ease-in-out infinite; display: inline-block; }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.3} }
.badge-new { background: var(--orange); color: #fff; }

.shop-card-body { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
.shop-card-type { font-size: 10.5px; font-weight: 700; color: var(--orange); text-transform: uppercase; letter-spacing: .5px; }
.shop-card-name { font-family: var(--display); font-size: 14.5px; font-weight: 800; color: var(--text); line-height: 1.3; }
.shop-card-desc {
    font-size: 12px; color: var(--muted); line-height: 1.5;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.shop-card-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
.shop-card-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600; color: var(--text-2);
    background: var(--grey); border: 1px solid var(--border);
    padding: 3px 8px; border-radius: 5px;
}
.shop-card-footer {
    padding: 12px 16px; border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
}
.shop-card-rating { display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 700; color: var(--orange); }
.shop-card-rating small { color: var(--muted); font-weight: 400; }
.shop-card-cta {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 8px 16px; border-radius: 50px;
    font-size: 12.5px; font-weight: 700; font-family: var(--font);
    background: var(--orange); color: #fff; border: none;
    cursor: pointer; text-decoration: none; transition: all .15s;
}
.shop-card-cta:hover { background: var(--orange-dk); transform: scale(1.04); }

/* ══ FLASH ══ */
.c-flash {
    padding: 12px 16px; border-radius: var(--r-sm); border: 1px solid;
    font-size: 13px; font-weight: 500; margin-bottom: 20px;
    display: flex; align-items: center; gap: 8px;
}
.c-flash-success { background: #f0fff4; border-color: #6ee7b7; color: #065f46; }
.c-flash-danger  { background: #fff5f5; border-color: #fca5a5; color: #991b1b; }

/* ══ VIDE ══ */
.c-empty { grid-column: 1/-1; padding: 72px 20px; text-align: center; }
.c-empty-ico { font-size: 52px; display: block; opacity: .3; margin-bottom: 14px; }
.c-empty-title { font-family: var(--display); font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 6px; }
.c-empty-sub { font-size: 13.5px; color: var(--muted); }

/* ══ PAGINATION ══ */
.c-pagination { display: flex; justify-content: center; padding: 8px 0; }

/* ══ PAGE WRAP (sidebar + main) ══ */
.page-wrap {
    display: flex; gap: 22px; align-items: flex-start;
    max-width: 1440px; margin: 0 auto;
    padding: 20px 24px 60px;
}

/* ══ SIDEBAR ══ */
.sidebar {
    width: 240px; flex-shrink: 0;
    display: flex; flex-direction: column; gap: 14px;
    position: sticky; top: calc(var(--nav-h) + 14px);
    max-height: calc(100vh - var(--nav-h) - 28px);
    overflow-y: auto; scrollbar-width: none;
}
.sidebar::-webkit-scrollbar { display: none; }

.sb-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); box-shadow: var(--shadow-sm); overflow: hidden;
}
.sb-hd {
    padding: 11px 14px; border-bottom: 1px solid var(--border);
    font-family: var(--display); font-size: 12px; font-weight: 800;
    color: var(--text); text-transform: uppercase; letter-spacing: .5px;
    display: flex; align-items: center; gap: 7px;
}
.sb-cat-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 14px; border-bottom: 1px solid var(--grey);
    cursor: pointer; transition: background .12s;
    font-size: 12.5px; font-weight: 500; color: var(--text-2);
    text-decoration: none; background: none; border-left: none;
    width: 100%; text-align: left; font-family: var(--font);
}
.sb-cat-item:last-child { border-bottom: none; }
.sb-cat-item:hover { background: var(--grey); color: var(--text); }
.sb-cat-item.active { background: var(--orange-lt); color: var(--orange); font-weight: 700; border-left: 3px solid var(--orange); padding-left: 11px; }
.sb-cat-ico { font-size: 16px; flex-shrink: 0; }
.sb-cat-name { flex: 1; }
.sb-cat-cnt {
    font-size: 10.5px; font-weight: 700; background: var(--grey-2);
    border-radius: 20px; padding: 1px 7px; color: var(--muted);
}

.sb-location {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 11px 14px;
    display: flex; align-items: center; gap: 8px;
    font-size: 12.5px; font-weight: 600; color: var(--text);
    box-shadow: var(--shadow-sm);
}

.sb-cta {
    background: linear-gradient(135deg, var(--navy) 0%, #3d5a73 100%);
    border-radius: var(--r); padding: 18px 14px; text-align: center;
    position: relative; overflow: hidden;
}
.sb-cta::before {
    content: ''; position: absolute; right: -25px; top: -25px;
    width: 90px; height: 90px; border-radius: 50%;
    background: rgba(255,255,255,.06); pointer-events: none;
}
.sb-cta-title {
    font-family: var(--display); font-size: 13px; font-weight: 900;
    color: #fff; margin-bottom: 5px; line-height: 1.3; position: relative; z-index: 1;
}
.sb-cta-sub {
    font-size: 11px; color: rgba(255,255,255,.65);
    margin-bottom: 12px; line-height: 1.5; position: relative; z-index: 1;
}
.sb-cta-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 8px 16px; border-radius: 50px;
    font-size: 11.5px; font-weight: 700; background: var(--orange); color: #fff;
    border: none; cursor: pointer; text-decoration: none; transition: all .15s;
    position: relative; z-index: 1;
}
.sb-cta-btn:hover { background: var(--orange-dk); color: #fff; }

.sb-top-shop {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 14px; border-bottom: 1px solid var(--grey);
    text-decoration: none; color: inherit; transition: background .12s;
}
.sb-top-shop:last-child { border-bottom: none; }
.sb-top-shop:hover { background: var(--grey); }
.sb-top-num {
    font-size: 14px; font-weight: 900; color: var(--orange);
    font-family: var(--display); width: 18px; flex-shrink: 0; text-align: center;
}
.sb-top-av {
    width: 34px; height: 34px; border-radius: 8px;
    background: var(--grey-2); display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0; overflow: hidden;
}
.sb-top-av img { width: 100%; height: 100%; object-fit: cover; }
.sb-top-info { flex: 1; min-width: 0; }
.sb-top-name { font-size: 12px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-top-rating { font-size: 10.5px; color: var(--orange); font-weight: 600; }

/* ══ MAIN COLUMN ══ */
.main-col { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 18px; }

/* ══ HERO (adapted) ══ */
.hero {
    background: linear-gradient(135deg, var(--navy) 0%, #3d5a73 100%);
    border-radius: var(--r); padding: 32px 36px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 24px; overflow: hidden; position: relative;
}
.hero::before {
    content: ''; position: absolute; right: -60px; top: -60px;
    width: 260px; height: 260px; border-radius: 50%;
    background: rgba(255,255,255,.04); pointer-events: none;
}
.hero::after {
    content: ''; position: absolute; right: 90px; bottom: -70px;
    width: 180px; height: 180px; border-radius: 50%;
    background: rgba(240,106,15,.12); pointer-events: none;
}
.hero-text { flex: 1; position: relative; z-index: 1; }
.hero-title {
    font-family: var(--display); font-weight: 900;
    font-size: clamp(20px, 2.8vw, 34px);
    color: #fff; line-height: 1.15; margin-bottom: 14px; letter-spacing: -.4px;
}
.hero-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.hero-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 12px; border-radius: 50px;
    font-size: 11.5px; font-weight: 700;
    background: rgba(255,255,255,.12); color: #fff;
    border: 1px solid rgba(255,255,255,.2);
    backdrop-filter: blur(6px); white-space: nowrap;
}
.hero-right {
    display: flex; align-items: center; gap: 14px;
    flex-shrink: 0; position: relative; z-index: 1;
}
.hero-icon-box {
    width: 72px; height: 72px; border-radius: 18px;
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; flex-shrink: 0;
    animation: floatBox 3.5s ease-in-out infinite;
}
.hero-icon-box:nth-child(2) { animation-delay: -1.5s; width: 62px; height: 62px; font-size: 24px; }
.hero-icon-box:nth-child(3) { animation-delay: -3s; width: 68px; height: 68px; font-size: 26px; }
@keyframes floatBox { 0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)} }

/* ══ STATS ROW ══ */
.stats-row {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;
}
.stat-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 14px 16px;
    box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 12px;
}
.stat-ico {
    width: 42px; height: 42px; border-radius: 11px;
    background: var(--orange-lt); display: flex; align-items: center; justify-content: center;
    font-size: 19px; flex-shrink: 0;
}
.stat-val {
    font-family: var(--display); font-size: 20px; font-weight: 900;
    color: var(--text); line-height: 1;
}
.stat-lbl { font-size: 11px; color: var(--muted); margin-top: 3px; }

/* ══ POP CATEGORIES GRID ══ */
.pop-cats-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 10px;
}
.pop-cat-card {
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: var(--r); padding: 13px 8px; text-align: center;
    cursor: pointer; transition: all .18s; display: flex;
    flex-direction: column; align-items: center; gap: 5px;
    box-shadow: var(--shadow-sm); text-decoration: none;
}
.pop-cat-card:hover {
    border-color: var(--orange); box-shadow: 0 4px 12px rgba(240,106,15,.15);
    transform: translateY(-2px);
}
.pop-cat-card.active { background: var(--orange-lt); border-color: var(--orange); }
.pop-cat-ico { font-size: 26px; }
.pop-cat-name { font-size: 10.5px; font-weight: 700; color: var(--text); line-height: 1.3; }
.pop-cat-cnt { font-size: 10px; color: var(--muted); }

/* ══ TOP SHOPS GRID (3 cards) ══ */
.top-shops-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;
}
.top-shop-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); transition: all .2s;
    display: flex; flex-direction: column;
}
.top-shop-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-3px); border-color: var(--orange); }
.top-shop-img { height: 110px; overflow: hidden; position: relative; background: var(--grey); }
.top-shop-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.top-shop-card:hover .top-shop-img img { transform: scale(1.06); }
.top-shop-img-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 32px; }
.top-shop-body { padding: 11px 13px; flex: 1; }
.top-shop-name { font-family: var(--display); font-size: 13px; font-weight: 800; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.top-shop-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 6px; }
.top-shop-rating { display: flex; align-items: center; gap: 3px; font-size: 11.5px; font-weight: 700; color: var(--orange); }
.top-shop-sales { font-size: 10.5px; color: var(--muted); }
.top-shop-btn {
    display: block; margin: 0 13px 12px; padding: 8px;
    border-radius: 50px; text-align: center;
    font-size: 12px; font-weight: 700;
    background: var(--orange); color: #fff; text-decoration: none;
    transition: background .15s;
}
.top-shop-btn:hover { background: var(--orange-dk); color: #fff; }

/* ══ RESPONSIVE ══ */
@media (max-width: 1100px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .top-shops-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .page-wrap { flex-direction: column; padding: 14px 14px 50px; gap: 16px; }
    .sidebar { width: 100%; position: static; max-height: none; flex-direction: row; flex-wrap: wrap; }
    .sb-card { flex: 1; min-width: 200px; }
    .hero { padding: 24px 20px; }
    .hero-right { display: none; }
    .nav { padding: 0 16px; }
    .nav-links { display: none; }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .shops-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .hero-title { font-size: 20px; }
    .nav-search { display: none; }
    .msg-drawer { width: 100vw; }
    .nav-orders-btn span { display: none; }
    .top-shops-grid { grid-template-columns: 1fr 1fr; }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .pop-cats-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 400px) {
    .shops-grid { grid-template-columns: 1fr; }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $user      = auth()->user();
    $parts     = explode(' ', $user->name);
    $initials  = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $firstName = $parts[0];

    // Drapeau pays
    $countryFlag = '';
    if ($user->country) {
        $c = strtoupper($user->country);
        $countryFlag = mb_convert_encoding(
            '&#'.(127397+ord($c[0])).';&#'.(127397+ord($c[1])).';',
            'UTF-8', 'HTML-ENTITIES'
        );
    }

    $countryNames = [
        // Afrique de l'Ouest
        'BJ'=>'Bénin','BF'=>'Burkina Faso','CV'=>'Cap-Vert','CI'=>"Côte d'Ivoire",
        'GM'=>'Gambie','GH'=>'Ghana','GN'=>'Guinée','GW'=>'Guinée-Bissau','LR'=>'Libéria',
        'ML'=>'Mali','MR'=>'Mauritanie','NE'=>'Niger','NG'=>'Nigéria','SN'=>'Sénégal',
        'SL'=>'Sierra Leone','TG'=>'Togo',
        // Afrique Centrale
        'CM'=>'Cameroun','CF'=>'Centrafrique','TD'=>'Tchad','CG'=>'Congo','CD'=>'RD Congo',
        'GQ'=>'Guinée Équatoriale','GA'=>'Gabon','ST'=>'São Tomé','BI'=>'Burundi','RW'=>'Rwanda',
        // Afrique de l'Est
        'DJ'=>'Djibouti','ER'=>'Érythrée','ET'=>'Éthiopie','KE'=>'Kenya','KM'=>'Comores',
        'MG'=>'Madagascar','MW'=>'Malawi','MU'=>'Maurice','MZ'=>'Mozambique','SC'=>'Seychelles',
        'SO'=>'Somalie','SS'=>'Soudan du Sud','SD'=>'Soudan','TZ'=>'Tanzanie','UG'=>'Ouganda',
        'ZM'=>'Zambie','ZW'=>'Zimbabwe',
        // Afrique du Nord
        'DZ'=>'Algérie','EG'=>'Égypte','LY'=>'Libye','MA'=>'Maroc','TN'=>'Tunisie',
        // Afrique Australe
        'AO'=>'Angola','BW'=>'Botswana','LS'=>'Lesotho','NA'=>'Namibie','ZA'=>'Afrique du Sud','SZ'=>'Eswatini',
        // Europe
        'AL'=>'Albanie','DE'=>'Allemagne','AT'=>'Autriche','BE'=>'Belgique','BA'=>'Bosnie',
        'BG'=>'Bulgarie','HR'=>'Croatie','CY'=>'Chypre','DK'=>'Danemark','ES'=>'Espagne',
        'EE'=>'Estonie','FI'=>'Finlande','FR'=>'France','GR'=>'Grèce','HU'=>'Hongrie',
        'IE'=>'Irlande','IS'=>'Islande','IT'=>'Italie','LV'=>'Lettonie','LT'=>'Lituanie',
        'LU'=>'Luxembourg','MT'=>'Malte','MD'=>'Moldavie','MC'=>'Monaco','ME'=>'Monténégro',
        'NO'=>'Norvège','NL'=>'Pays-Bas','PL'=>'Pologne','PT'=>'Portugal','CZ'=>'Rép. Tchèque',
        'RO'=>'Roumanie','GB'=>'Royaume-Uni','RU'=>'Russie','RS'=>'Serbie','SK'=>'Slovaquie',
        'SI'=>'Slovénie','SE'=>'Suède','CH'=>'Suisse','UA'=>'Ukraine',
        // Amériques
        'AR'=>'Argentine','BR'=>'Brésil','CA'=>'Canada','CL'=>'Chili','CO'=>'Colombie',
        'CU'=>'Cuba','DO'=>'Rép. Dominicaine','EC'=>'Équateur','US'=>'États-Unis',
        'GT'=>'Guatemala','HT'=>'Haïti','MX'=>'Mexique','PA'=>'Panama','PE'=>'Pérou',
        'UY'=>'Uruguay','VE'=>'Venezuela',
        // Asie
        'SA'=>'Arabie Saoudite','AM'=>'Arménie','AZ'=>'Azerbaïdjan','BD'=>'Bangladesh',
        'CN'=>'Chine','KR'=>'Corée du Sud','AE'=>'Émirats Arabes','IN'=>'Inde',
        'ID'=>'Indonésie','IR'=>'Iran','IQ'=>'Irak','IL'=>'Israël','JP'=>'Japon',
        'JO'=>'Jordanie','KW'=>'Koweït','LB'=>'Liban','MY'=>'Malaisie','NP'=>'Népal',
        'OM'=>'Oman','PK'=>'Pakistan','PH'=>'Philippines','QA'=>'Qatar','SG'=>'Singapour',
        'LK'=>'Sri Lanka','TH'=>'Thaïlande','TR'=>'Turquie','VN'=>'Viêt Nam',
        // Océanie
        'AU'=>'Australie','NZ'=>'Nouvelle-Zélande',
    ];
    $countryName = $countryNames[$user->country ?? ''] ?? $user->country ?? '';
?>

<?php
    $myMessages ??= collect();
    $myUnread   ??= 0;

    $statusMap = [
        'livrée'       => ['pill-livree',    '✓ Livrée'],
        'pending'      => ['pill-pending',   '⏳ En attente'],
        'en attente'   => ['pill-pending',   '⏳ En attente'],
        'en_attente'   => ['pill-pending',   '⏳ En attente'],
        'confirmée'    => ['pill-livraison', '✓ Confirmée'],
        'en_livraison' => ['pill-livraison', '🚴 En livraison'],
        'annulée'      => ['pill-cancelled', '✕ Annulée'],
        'cancelled'    => ['pill-cancelled', '✕ Annulée'],
    ];

    $typeIco = [
        'Alimentation' => ['🥩', 'bg-food'],    'Restaurant'  => ['🍽️', 'bg-food'],
        'Épicerie'     => ['🛒', 'bg-food'],    'Boulangerie' => ['🥖', 'bg-food'],
        'Vêtements'    => ['👗', 'bg-fashion'], 'Bijouterie'  => ['💎', 'bg-fashion'],
        'Électronique' => ['📱', 'bg-tech'],    'Informatique'=> ['💻', 'bg-tech'],
        'Téléphonie'   => ['📞', 'bg-tech'],    'Beauté & Cosmétiques' => ['💄', 'bg-beauty'],
        'Pharmacie'    => ['💊', 'bg-beauty'],  'Parfumerie'  => ['🌸', 'bg-beauty'],
    ];

    // Catégories à afficher seulement si des boutiques de ce type existent
    $allTypes = ['Alimentation','Restaurant','Épicerie','Boulangerie','Vêtements','Bijouterie',
                 'Électronique','Informatique','Téléphonie','Beauté & Cosmétiques','Pharmacie','Parfumerie'];
    $activeType = request('type', '');
?>


<div class="msg-overlay" id="msgOverlay" onclick="closeMsgDrawer()"></div>
<div class="msg-drawer" id="msgDrawer">
    <div class="msg-drawer-hd">
        <span class="msg-drawer-title">💬 Mes Messages</span>
        <?php if($myUnread > 0): ?>
        <span class="msg-drawer-badge"><?php echo e($myUnread); ?> non lu<?php echo e($myUnread > 1 ? 's' : ''); ?></span>
        <?php endif; ?>
        <button class="msg-drawer-close" onclick="closeMsgDrawer()">✕</button>
    </div>
    <div class="msg-conv-list" id="msgConvList">
        <?php $__empty_1 = true; $__currentLoopData = $myMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $convKey => $msgs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $firstMsg = $msgs->first();
            $lastMsg  = $msgs->first(); /* déjà orderByDesc donc premier = plus récent */
            $product  = $firstMsg->product;
            $shop     = $product?->shop;
            $vendeur  = ($firstMsg->sender_id === $user->id) ? $firstMsg->receiver : $firstMsg->sender;
            $unreadCnt = $msgs->filter(fn($m) => is_null($m->read_at) && $m->receiver_id === $user->id)->count();
            $vName  = $shop?->name ?? ($vendeur?->name ?? 'Vendeur');
            $vParts = explode(' ', $vName);
            $vInit  = strtoupper(substr($vParts[0],0,1)) . strtoupper(substr($vParts[1] ?? 'X',0,1));
            $convData = json_encode([
                'key'      => $convKey,
                'shopName' => $vName,
                'shopImg'  => $shop?->image ? asset('storage/'.$shop->image) : null,
                'shopInit' => $vInit,
                'prodName' => $product?->name ?? '',
                'prodImg'  => $product?->image ? asset('storage/'.$product->image) : null,
                'prodPrice'=> $product ? number_format($product->price,0,',',' ').' GNF' : '',
                'prodUrl'  => $product ? route('client.products.show', $product) : '#',
                'productId'=> $product?->id,
                'msgs'     => $msgs->map(fn($m) => [
                    'id'   => $m->id,
                    'body' => $m->body,
                    'mine' => $m->sender_id === $user->id,
                    'av'   => $m->sender_id === $user->id ? $initials : $vInit,
                    'time' => $m->created_at->format('d/m H:i'),
                    'read' => !is_null($m->read_at),
                ])->values(),
            ]);
        ?>
        <div class="msg-conv-item <?php echo e($unreadCnt > 0 ? 'has-unread' : ''); ?>"
             onclick="openMsgModal(<?php echo e($convData); ?>)" data-conv-key="<?php echo e($convKey); ?>">
            <div class="msg-conv-av">
                <?php if($shop?->image): ?>
                    <img src="<?php echo e(\App\Services\ImageOptimizer::url($shop->image, 'thumb')); ?>"
                         alt="<?php echo e($vName); ?>" loading="lazy" decoding="async" width="42" height="42">
                <?php else: ?>
                    <?php echo e($vInit); ?>

                <?php endif; ?>
                <?php if($unreadCnt > 0): ?><span class="msg-conv-av-dot"></span><?php endif; ?>
            </div>
            <div class="msg-conv-info">
                <div class="msg-conv-name"><?php echo e($vName); ?></div>
                <?php if($product): ?><div class="msg-conv-prod">🏷️ <?php echo e(Str::limit($product->name, 28)); ?></div><?php endif; ?>
                <div class="msg-conv-preview"><?php echo e(Str::limit($lastMsg->body, 42)); ?></div>
            </div>
            <div class="msg-conv-meta">
                <span class="msg-conv-time"><?php echo e($lastMsg->created_at->diffForHumans(null, true)); ?></span>
                <?php if($unreadCnt > 0): ?><span class="msg-conv-unread"><?php echo e($unreadCnt); ?></span><?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="msg-conv-empty">
            <span class="msg-conv-empty-ico">💬</span>
            <div class="msg-conv-empty-txt">Aucune conversation pour l'instant.<br>Posez une question depuis une boutique !</div>
        </div>
        <?php endif; ?>
    </div>
</div>


<div class="msg-modal-overlay" id="msgModalOverlay">
    <div class="msg-modal" id="msgModal">
        <div class="msg-modal-hd">
            <button class="msg-modal-back" onclick="closeMsgModal()" title="Retour">←</button>
            <div class="msg-modal-av" id="mmAv">??</div>
            <div class="msg-modal-info">
                <div class="msg-modal-name" id="mmName">Vendeur</div>
                <div class="msg-modal-prod" id="mmProd"></div>
            </div>
            <button class="msg-modal-close" onclick="closeMsgModal(); closeMsgDrawer()">✕</button>
        </div>
        <div class="msg-prod-bar" id="mmProdBar" style="display:none">
            <div class="msg-prod-ph" id="mmProdImgPh">🏷️</div>
            <img class="msg-prod-img" id="mmProdImg" src="" alt="" style="display:none">
            <div>
                <div class="msg-prod-name" id="mmProdName"></div>
                <div class="msg-prod-price" id="mmProdPrice"></div>
            </div>
            <a class="msg-prod-link" id="mmProdLink" href="#" target="_blank">Voir →</a>
        </div>
        <div class="msg-thread" id="mmThread">
            <div style="text-align:center;padding:40px 20px;color:var(--muted);font-size:13px">
                Chargement…
            </div>
        </div>
        <div class="msg-reply-zone">
            <form id="mmForm" style="display:flex;gap:10px;align-items:flex-end;width:100%" onsubmit="sendMsg(event)">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="mmProductId" name="product_id" value="">
                <textarea name="body" id="mmInput" class="msg-reply-input"
                    placeholder="Écrire au vendeur…" rows="1" required
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg(event)}"
                    oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
                <button type="submit" class="msg-send-btn">➤</button>
            </form>
        </div>
    </div>
</div>


<nav class="nav">
    <a href="<?php echo e(route('client.dashboard')); ?>" class="nav-logo">
         <img src="<?php echo e(asset('images/Shopio2.jpeg')); ?>" alt="Shopio" style="height:60px;width:auto;object-fit:contain;border-radius:8px">
       
    </a>

    <div class="nav-links">
        <a href="<?php echo e(route('client.dashboard')); ?>" class="nav-link active">🏠 Accueil</a>
        <a href="#boutiques" class="nav-link">🏪 Boutiques</a>
        <a href="#categories" class="nav-link">📦 Catégories</a>
    </div>

    <div class="nav-search">
        <input type="text" id="globalSearch" placeholder="Que recherchez-vous ?">
        <button class="nav-search-btn" onclick="doSearch()">🔍</button>
    </div>

    <div class="nav-actions">
        
        <a href="<?php echo e(route('client.messages.hub')); ?>" class="nav-msg-btn" title="Mes messages" style="text-decoration:none">
            💬
            <span class="nav-msg-badge <?php echo e($myUnread > 0 ? 'show' : ''); ?>" id="navMsgBadge">
                <?php echo e($myUnread > 0 ? $myUnread : ''); ?>

            </span>
        </a>

        <a href="<?php echo e(route('client.orders.index')); ?>" class="nav-orders-btn">
            📦 <span>Mes commandes</span>
        </a>
        <div class="nav-av-wrap">
            <div style="position:relative;cursor:pointer" onclick="toggleAvatarMenu()">
                <div class="nav-av" id="navAvatar"><?php echo e($initials); ?></div>
                <?php if($countryFlag): ?>
                <span style="position:absolute;bottom:-4px;right:-6px;font-size:14px;line-height:1;background:var(--surface);border-radius:50%;padding:1px;box-shadow:0 0 0 1.5px var(--border)"><?php echo e($countryFlag); ?></span>
                <?php endif; ?>
            </div>
            <div class="nav-av-menu" id="avatarMenu">
                <div style="padding:12px 14px 10px;border-bottom:1px solid var(--border);margin-bottom:4px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--orange),var(--orange-dk));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0"><?php echo e($initials); ?></div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text)"><?php echo e($user->name); ?></div>
                            <div style="font-size:11px;color:var(--muted)"><?php echo e($user->email); ?></div>
                        </div>
                    </div>
                    <?php if($countryFlag): ?>
                    <div style="display:inline-flex;align-items:center;gap:5px;background:var(--grey);border:1px solid var(--border);border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;color:var(--text-2);margin-top:4px">
                        <?php echo e($countryFlag); ?> <?php echo e($countryName); ?>

                    </div>
                    <?php endif; ?>
                </div>
                <a href="#" onclick="openProfileModal();return false;">👤 Modifier mon profil</a>
                <a href="<?php echo e(route('client.orders.index')); ?>">📦 Mes commandes</a>
                <div class="sep"></div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="logout">⎋ Se déconnecter</button>
                </form>
            </div>
        </div>
    </div>
</nav>


<div class="page-wrap">


<aside class="sidebar">

    
    <?php if(isset($categories) && $categories->isNotEmpty()): ?>
    <div class="sb-card">
        <div class="sb-hd">🗂️ Explorer</div>
        <button class="sb-cat-item active" data-cat-type="" onclick="filterByCat('')">
            <span class="sb-cat-ico">🏪</span>
            <span class="sb-cat-name">Toutes les boutiques</span>
            <span class="sb-cat-cnt"><?php echo e($shopCount ?? $shops->total()); ?></span>
        </button>
        <?php
            $sbEmojis = [
                'alimentation'=>'🍽️','restaurant'=>'🍽️','épicerie'=>'🛒','epicerie'=>'🛒',
                'boulangerie'=>'🥖','pâtisserie'=>'🎂','patisserie'=>'🎂','vêtements'=>'👗',
                'vetements'=>'👗','mode'=>'👗','bijouterie'=>'💎','bijoux'=>'💎',
                'électronique'=>'📱','electronique'=>'📱','informatique'=>'💻',
                'téléphonie'=>'📞','telephonie'=>'📞','beauté & cosmétiques'=>'💄',
                'beaute & cosmetiques'=>'💄','beauté'=>'💄','beaute'=>'💄',
                'cosmétiques'=>'💄','cosmetiques'=>'💄','pharmacie'=>'💊',
                'parfumerie'=>'🌸','auto & moto'=>'🚗','automobile'=>'🚗','sport'=>'⚽',
                'maison'=>'🏠','décoration'=>'🏠','decoration'=>'🏠','librairie'=>'📚',
                'musique'=>'🎵','jardin'=>'🌿','agriculture'=>'🌾','santé'=>'🏥',
                'sante'=>'🏥','construction'=>'🏗️','quincaillerie'=>'🔧','supermarché'=>'🛒',
                'supermarche'=>'🛒','chaussures'=>'👟','accessoires'=>'👜','sacs'=>'👜',
            ];
            $getSbEmoji = function(string $t) use ($sbEmojis): string {
                $k = mb_strtolower(trim($t));
                if (isset($sbEmojis[$k])) return $sbEmojis[$k];
                foreach ($sbEmojis as $key => $ico) { if (str_contains($k, $key)) return $ico; }
                return '🏪';
            };
        ?>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button class="sb-cat-item" data-cat-type="<?php echo e($cat->type); ?>" onclick="filterByCat(this.dataset.catType)">
            <span class="sb-cat-ico"><?php echo e($getSbEmoji($cat->type)); ?></span>
            <span class="sb-cat-name"><?php echo e($cat->type); ?></span>
            <span class="sb-cat-cnt"><?php echo e($cat->shop_count); ?></span>
        </button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <?php if($countryFlag && $countryName): ?>
    <div class="sb-location">
        <span style="font-size:22px"><?php echo e($countryFlag); ?></span>
        <div>
            <div style="font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px">Votre zone</div>
            <div style="font-size:13px;font-weight:700;color:var(--text)"><?php echo e($countryName); ?></div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="sb-cta">
        <div class="sb-cta-title">⭐ Devenez vendeur premium</div>
        <div class="sb-cta-sub">Ouvrez votre boutique et touchez des milliers de clients.</div>
        <a href="#" class="sb-cta-btn">Commencer →</a>
    </div>

    
    <?php if(isset($topShops) && $topShops->isNotEmpty()): ?>
    <div class="sb-card">
        <div class="sb-hd">🏆 Meilleures boutiques</div>
        <?php $__currentLoopData = $topShops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $ts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('client.shops.show', $ts)); ?>" class="sb-top-shop" data-shop-id="<?php echo e($ts->id); ?>">
            <span class="sb-top-num"><?php echo e($i + 1); ?></span>
            <div class="sb-top-av">
                <?php if($ts->image): ?>
                    <img src="<?php echo e(\App\Services\ImageOptimizer::url($ts->image, 'thumb')); ?>" alt="<?php echo e($ts->name); ?>" loading="lazy">
                <?php else: ?>
                    🏪
                <?php endif; ?>
            </div>
            <div class="sb-top-info">
                <div class="sb-top-name"><?php echo e($ts->name); ?></div>
                <div class="sb-top-rating">
                    ⭐ <?php echo e($ts->avg_rating ? number_format($ts->avg_rating, 1) : '—'); ?>

                    <span style="color:var(--muted);font-weight:400"> · <?php echo e($ts->reviews_count ?? 0); ?> avis</span>
                </div>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

