<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --orange:     #1668D8;
    --orange-dk:  #1260C9;
    --orange-lt:  #EAF2FF;
    --navy:       #0B1E3D;
    --navy-2:     #132B52;
    --grey:       #F6F9FF;
    --grey-2:     #EEF2F8;
    --border:     #E3E8F2;
    --text:       #0B1E3D;
    --text-2:     #45455E;
    --muted:      #62627C;
    --surface:    #ffffff;
    --font:       'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --display:    'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
    --r:          10px;
    --r-sm:       7px;
    --shadow-sm:  0 1px 4px rgba(0,0,0,.07);
    --shadow:     0 4px 16px rgba(0,0,0,.1);
    --shadow-lg:  0 8px 32px rgba(0,0,0,.13);
    --nav-h:      60px;
}

html { font-family: var(--font); scroll-behavior: smooth; overflow-x: hidden; }
/* Pas de overflow-x ici : html le gère déjà, et un overflow-x sans overflow-y
   explicite force ce dernier à devenir "auto" (règle CSS), ce qui transforme
   body en une deuxième zone de défilement cachée — cassant "position:sticky"
   pour tout élément (ex: la sidebar) dont body devient alors le plus proche
   ancêtre défilant, au lieu de la page elle-même. */
body { background: var(--grey); margin: 0; color: var(--text); -webkit-font-smoothing: antialiased; }

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
    box-shadow: 0 0 0 3px rgba(31,124,246,.1);
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

/* ── Tri + filtre prix du catalogue (même principe que l'accueil) ── */
.filter-row { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
.sort-row { display: flex; align-items: center; gap: 8px; }
.sort-select {
    padding: 8px 32px 8px 14px; border-radius: 50px; border: 1.5px solid var(--border);
    background: var(--surface); color: var(--text); font-size: 12.5px; font-weight: 600; font-family: var(--font);
    cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,.05); transition: border-color .15s;
    appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%238a9bb0' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
}
.sort-select:hover, .sort-select:focus { border-color: var(--orange); outline: none; }
.price-filter-form {
    display: flex; align-items: center; gap: 7px;
    background: var(--surface); border: 1.5px solid var(--border); border-radius: 50px;
    padding: 5px 8px 5px 14px; box-shadow: 0 1px 3px rgba(0,0,0,.05); color: var(--muted);
}
.price-filter-form:focus-within { border-color: var(--orange); }
.price-input {
    width: 92px; border: none; outline: none; background: transparent;
    font-size: 12.5px; font-family: var(--font); color: var(--text); padding: 6px 2px;
    -moz-appearance: textfield;
}
.price-input::-webkit-outer-spin-button, .price-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.price-input::placeholder { color: var(--muted); }
.price-sep { font-size: 12px; color: var(--muted); flex-shrink: 0; }
.price-filter-btn {
    flex-shrink: 0; padding: 7px 15px; border-radius: 50px; border: none;
    background: var(--orange); color: #fff; font-size: 12px; font-weight: 700; font-family: var(--font);
    cursor: pointer; transition: background .15s; white-space: nowrap;
}
.price-filter-btn:hover { background: var(--orange-dk); }
.price-filter-clear {
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    width: 26px; height: 26px; border-radius: 50%; background: var(--grey);
    color: var(--muted); text-decoration: none; transition: all .15s;
}
.price-filter-clear:hover { background: #fee2e2; color: #dc2626; }
@media (max-width: 640px) {
    .price-filter-form { flex-wrap: nowrap; width: 100%; }
    .price-input { width: 0; flex: 1; min-width: 0; }
}

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
    font-variant-numeric: tabular-nums; border: 1.5px solid var(--surface);
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
    font-variant-numeric: tabular-nums;
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
    padding: 1px 6px; font-variant-numeric: tabular-nums; min-width: 16px; text-align: center;
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
.msg-prod-price { font-size: 11px; font-weight: 800; color: var(--orange); font-variant-numeric: tabular-nums; }
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
    box-shadow: 0 0 0 3px rgba(31,124,246,.1);
}
.msg-send-btn {
    width: 42px; height: 42px; border-radius: 50%;
    background: var(--orange); color: #fff; border: none;
    cursor: pointer; font-size: 16px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .15s;
    box-shadow: 0 2px 8px rgba(31,124,246,.3);
}
.msg-send-btn:hover { background: var(--orange-dk); transform: scale(1.08); }

/* ══ HERO BANNER ══ */
.hero {
    background: linear-gradient(135deg, var(--navy) 0%, #1F3F75 100%);
    border-radius: var(--r);
    display: flex; align-items: center;
    overflow: hidden; position: relative;
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
    background: rgba(31,124,246,.12); pointer-events: none;
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
.order-info { flex: 1; min-width: 0; overflow: hidden; }
.order-ref  { font-size: 12.5px; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.order-shop { font-size: 11.5px; color: var(--muted); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.order-amount { font-size: 13.5px; font-weight: 700; color: var(--text); font-variant-numeric: tabular-nums; white-space: nowrap; flex-shrink: 0; }
.order-pill {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
    white-space: nowrap; flex-shrink: 0;
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
    box-shadow: 0 3px 10px rgba(31,124,246,.18);
}
.cat-pill.active {
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    color: #fff; border-color: var(--orange-dk);
    box-shadow: 0 4px 14px rgba(31,124,246,.35);
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

/* ══ GRILLE PRODUITS ══ */
.prod-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.prod-grid .reco-card { width: 100%; }

/* ══ RECOMMANDÉ POUR VOUS (carrousel produits — défilement automatique) ══ */
.reco-row-outer {
    position: relative;
    padding: 2px 0 16px;
    /* Par défaut (pas assez d'articles pour boucler) : défilement manuel au doigt/souris */
    overflow-x: auto; overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
    scrollbar-width: thin; scrollbar-color: var(--border) transparent;
}
.reco-row-outer::-webkit-scrollbar { height: 6px; }
.reco-row-outer::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
/* Défilement automatique = scroll natif incrémenté en JS (voir script) → pas de snap, sinon ça saccade */
.reco-row-outer.reco-loop { scroll-snap-type: none; }

.reco-row-outer::before, .reco-row-outer::after {
    content: ''; position: absolute; top: 0; bottom: 16px; width: 40px; z-index: 2; pointer-events: none;
}
.reco-row-outer::before { left: 0;  background: linear-gradient(to right, var(--grey), transparent); }
.reco-row-outer::after  { right: 0; background: linear-gradient(to left,  var(--grey), transparent); }

.reco-row {
    display: flex; gap: 16px; width: max-content;
}
.reco-card { scroll-snap-align: start; }

.reco-card {
    flex: 0 0 190px; width: 190px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: box-shadow .2s, transform .2s, border-color .2s;
    text-decoration: none; color: inherit;
    display: flex; flex-direction: column;
}
.reco-card-img { height: 130px; position: relative; overflow: hidden; flex-shrink: 0; background: var(--grey); }
.reco-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s ease; }
.reco-card-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
.reco-card-badge {
    position: absolute; top: 8px; left: 8px;
    background: var(--orange); color: #fff; font-size: 10px; font-weight: 800;
    padding: 3px 8px; border-radius: 20px;
}
.reco-card-fav {
    position: absolute; top: 8px; right: 8px;
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(255,255,255,.92); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: var(--text-2); transition: all .15s;
}
.reco-card-fav.favorited { background: #fff0f0; color: #e53e3e; }
.reco-card-fav.favorited svg { stroke: #e53e3e; fill: #e53e3e; }
.reco-card-body { padding: 11px 13px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
.reco-card-shop { font-size: 10.5px; color: var(--muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.reco-card-name {
    font-size: 12.5px; font-weight: 700; color: var(--text); line-height: 1.3;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    min-height: 2.6em;
}
.reco-card-rating { display: flex; align-items: center; gap: 3px; font-size: 11px; color: var(--muted); }
.reco-card-rating svg { width: 12px; height: 12px; fill: #f59e0b; stroke: #f59e0b; flex-shrink: 0; }
.reco-card-rating strong { color: var(--text); font-weight: 700; }
.reco-card-price-row { display: flex; align-items: baseline; gap: 6px; margin-top: auto; flex-wrap: wrap; }
.reco-card-price { font-size: 14px; font-weight: 800; color: var(--orange); font-variant-numeric: tabular-nums; }
.reco-card-orig { font-size: 10.5px; color: var(--muted); text-decoration: line-through; font-variant-numeric: tabular-nums; }

/* Bouton "Ajouter au panier" sur les cartes produit */
.reco-card-addcart {
    display: flex; align-items: center; justify-content: center; gap: 5px;
    width: 100%; margin-top: 8px; padding: 7px 0;
    border: 1.5px solid var(--orange); border-radius: 30px;
    background: var(--surface); color: var(--orange-dk);
    font-size: 11px; font-weight: 700; font-family: var(--font);
    cursor: pointer; transition: all .15s;
}
.reco-card-addcart.added { background: #10b981; border-color: #10b981; color: #fff; }
.reco-card-addcart:disabled { opacity: .7; cursor: default; }

/* Boutons "Commander" / "Contacter" sur les cartes produit */
.reco-card-actions-row { display: flex; gap: 6px; margin-top: 6px; }
.reco-card-order, .reco-card-contact {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 4px;
    padding: 7px 4px; border-radius: 30px;
    font-size: 10.5px; font-weight: 700; font-family: var(--font);
    text-decoration: none; border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text-2); transition: all .15s; white-space: nowrap; cursor: pointer;
}
/* Effets "survol" uniquement sur souris réelle — sur mobile/tactile, un
   tap peut déclencher :hover et le laisser "coincé" (transform actif en
   permanence), ce qui produit un artefact de rendu flou sur la carte. */
@media (hover: hover) and (pointer: fine) {
    .reco-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--orange); }
    .reco-card:hover .reco-card-img img { transform: scale(1.07); }
    .reco-card-fav:hover { transform: scale(1.08); }
    .reco-card-addcart:hover { background: var(--orange); color: #fff; }
    .reco-card-order:hover, .reco-card-contact:hover { border-color: var(--orange); color: var(--orange-dk); background: var(--orange-lt); }
}

@media (max-width: 480px) {
    .reco-card { flex-basis: 152px; width: 152px; }
    .reco-card-img { height: 105px; }
    /* Carte trop étroite pour mettre "Commander" et "Contacter" côte à côte avec le
       texte lisible : on les empile en pleine largeur (comme le bouton "Ajouter"),
       au lieu de les serrer ou de cacher le texte. */
    .reco-card-actions-row { flex-direction: column; gap: 5px; }
    .reco-card-order, .reco-card-contact { width: 100%; padding: 7px 0; font-size: 10px; }
}

/* ══ VENTES FLASH (défilement automatique, urgence) ══ */
.flash-section {
    background: linear-gradient(135deg, var(--navy) 0%, var(--orange-dk) 55%, var(--orange) 100%);
    border-radius: var(--r); padding: 20px 20px 22px;
    position: relative; overflow: hidden; margin-bottom: 28px;
    box-shadow: 0 8px 28px rgba(220,38,38,.25);
}
.flash-section::before {
    content: ''; position: absolute; right: -40px; top: -40px; width: 180px; height: 180px;
    border-radius: 50%; background: rgba(255,255,255,.08); pointer-events: none;
}
.flash-section-hd { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; position: relative; z-index: 1; flex-wrap: wrap; }
.flash-section-title { font-family: var(--display); font-size: 18px; font-weight: 900; color: #fff; display: flex; align-items: center; gap: 8px; }
.flash-section-title .bolt { display: inline-block; animation: boltPulse 1.4s ease-in-out infinite; }
@keyframes boltPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.25); } }
.flash-section-sub { font-size: 12px; color: rgba(255,255,255,.85); font-weight: 600; }

.flash-row-outer {
    position: relative;
    /* Par défaut (pas assez d'articles pour boucler) : défilement manuel au doigt/souris */
    overflow-x: auto; overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
    scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.4) transparent;
}
.flash-row-outer::-webkit-scrollbar { height: 6px; }
.flash-row-outer::-webkit-scrollbar-thumb { background: rgba(255,255,255,.4); border-radius: 4px; }
/* Défilement automatique = scroll natif incrémenté en JS (voir script) → pas de snap, sinon ça saccade */
.flash-row-outer.flash-loop { scroll-snap-type: none; }

.flash-row-outer::before, .flash-row-outer::after {
    content: ''; position: absolute; top: 0; bottom: 0; width: 32px; z-index: 2; pointer-events: none;
}
.flash-row-outer::before { left: 0;  background: linear-gradient(to right, var(--navy), transparent); }
.flash-row-outer::after  { right: 0; background: linear-gradient(to left,  var(--orange), transparent); }

.flash-row { display: flex; gap: 14px; width: max-content; }
.flash-card { scroll-snap-align: start; }

.flash-card {
    flex: 0 0 168px; width: 168px;
    background: var(--surface); border-radius: 14px; overflow: hidden;
    text-decoration: none; color: inherit; display: flex; flex-direction: column;
    box-shadow: 0 4px 14px rgba(0,0,0,.18);
    transition: transform .2s;
}
.flash-card:hover { transform: translateY(-4px); }
.flash-card-img { height: 112px; position: relative; background: var(--grey); overflow: hidden; }
.flash-card-img img { width: 100%; height: 100%; object-fit: cover; }
.flash-card-badge {
    position: absolute; top: 7px; left: 7px;
    background: var(--navy); color: #D6E6FF; font-size: 10.5px; font-weight: 900;
    padding: 3px 8px; border-radius: 20px;
}
.flash-card-body { padding: 9px 11px 11px; display: flex; flex-direction: column; gap: 3px; }
.flash-card-shop { font-size: 10px; color: var(--muted); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.flash-card-name {
    font-size: 12px; font-weight: 700; color: var(--text); line-height: 1.25;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    min-height: 2.4em;
}
.flash-card-price-row { display: flex; align-items: baseline; gap: 5px; flex-wrap: wrap; }
.flash-card-price { font-size: 14px; font-weight: 900; color: var(--orange); font-variant-numeric: tabular-nums; }
.flash-card-orig { font-size: 10px; color: var(--muted); text-decoration: line-through; font-variant-numeric: tabular-nums; }
.flash-card-countdown {
    margin-top: 4px; display: inline-flex; align-items: center; gap: 4px;
    background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
    font-size: 10.5px; font-weight: 800; padding: 3px 8px; border-radius: 8px;
    font-variant-numeric: tabular-nums; width: fit-content;
}

@media (max-width: 480px) {
    .flash-card { flex-basis: 138px; width: 138px; }
    .flash-card-img { height: 92px; }
}

/* ══ AVATAR BOUTIQUE (cercle initiales) ══ */
.shop-av {
    width: 46px; height: 46px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 900; color: #fff;
    font-family: var(--display); letter-spacing: .5px;
    flex-shrink: 0;
    border: 2.5px solid rgba(255,255,255,.9);
    box-shadow: 0 4px 16px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.25);
    position: relative; overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}
.shop-av::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(160deg, rgba(255,255,255,.18) 0%, transparent 60%);
    border-radius: 50%; pointer-events: none;
}
.shop-card:hover .shop-av,
.top-shop-card:hover .shop-av {
    transform: scale(1.08);
    box-shadow: 0 6px 20px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.3);
}

/* Nom boutique — court = tout afficher, long = 2 lignes + ellipsis */
.shop-name-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
    line-height: 1.25;
    font-family: var(--display); font-size: 14px; font-weight: 800; color: var(--text);
}

/* ══ CARD BOUTIQUE ══ */
.shop-card {
    scroll-margin-top: calc(var(--nav-h, 64px) + 12px);
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
.bg-default { background: linear-gradient(135deg, #EEF2F8, #E3E8F2); }

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

/* ══ SYSTÈME ICÔNES SVG ══ */
.si { display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
.si svg { display:block; }
.nav-link .si { vertical-align:-.15em; }

/* ══ PAGE WRAP (sidebar + main) ══ */
.page-wrap {
    display: flex; gap: 22px; align-items: stretch;
    max-width: 1440px; margin: 0 auto;
    padding: 20px 24px 60px;
}

/* ══ SIDEBAR ══
   .sidebar (colonne flex, pleine hauteur, "align-items:stretch" du parent la
   fait s'étirer jusqu'à la hauteur de la colonne principale) contient
   .sidebar-sticky (le contenu réel, collé en haut de l'écran pendant le
   scroll). Sans ce découpage en deux niveaux, le "position:sticky" restait
   collé seulement le temps de SA PROPRE hauteur (plus courte que la liste de
   produits) puis disparaissait, laissant un vide à gauche pour le reste du
   scroll — .sidebar donne maintenant au sticky une hauteur de référence
   aussi longue que la page, donc il reste visible jusqu'en bas. */
.sidebar { width: 260px; flex-shrink: 0; }
.sidebar-sticky {
    display: flex; flex-direction: column; gap: 14px;
    position: sticky; top: calc(var(--nav-h) + 14px);
    max-height: calc(100vh - var(--nav-h) - 28px);
    overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--border) transparent;
}
.sidebar-sticky::-webkit-scrollbar { width: 4px; }
.sidebar-sticky::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.sidebar-sticky::-webkit-scrollbar-track { background: transparent; }

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
    display: flex; align-items: center; gap: 9px;
    padding: 9px 12px;
    cursor: pointer; transition: background .12s;
    font-size: 12px; font-weight: 500; color: var(--text-2);
    text-decoration: none; background: none; outline: none;
    width: 100%; text-align: left; font-family: var(--font);
    border: none;
    border-bottom: 1px solid var(--grey);
    border-left: 3px solid transparent;
}
.sb-cat-item:last-child { border-bottom: none; }
.sb-cat-item:hover { background: var(--grey); color: var(--text); }
.sb-cat-item.active { background: var(--orange-lt); color: var(--orange); font-weight: 700; border-left-color: var(--orange); }
.sb-cat-ico { display: flex; flex-shrink: 0; }
.sb-cat-name { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-cat-cnt {
    font-size: 10px; font-weight: 700; background: var(--grey-2);
    border-radius: 20px; padding: 1px 6px; color: var(--muted);
    flex-shrink: 0; white-space: nowrap;
}

.sb-location {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 11px 14px;
    display: flex; align-items: center; gap: 8px;
    font-size: 12.5px; font-weight: 600; color: var(--text);
    box-shadow: var(--shadow-sm);
}

/* ══ FIDÉLITÉ (sidebar) ══ */
.sb-loyalty-card {
    background: linear-gradient(135deg, #EAF2FF, #EAF2FF);
    border: 1px solid #D6E6FF;
    border-radius: var(--r); padding: 14px 16px;
    box-shadow: var(--shadow-sm);
}
.sb-loyalty-top { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; text-decoration: none; color: inherit; }
.sb-loyalty-ico { font-size: 26px; flex-shrink: 0; }
.sb-loyalty-pts { font-family: var(--display); font-size: 16px; font-weight: 900; color: var(--orange-dk); line-height: 1.1; }
.sb-loyalty-pts span { font-size: 11px; font-weight: 600; color: var(--text-2); }
.sb-loyalty-value { font-size: 11px; color: var(--text-2); margin-top: 2px; }
.sb-loyalty-bar-wrap { height: 7px; background: rgba(31,124,246,.15); border-radius: 20px; overflow: hidden; margin-bottom: 6px; }
.sb-loyalty-bar { height: 100%; background: linear-gradient(90deg, var(--orange), var(--orange-dk)); border-radius: 20px; transition: width .4s ease; }
.sb-loyalty-next { font-size: 10.5px; color: var(--text-2); line-height: 1.4; margin-bottom: 10px; text-decoration: none; display: block; }
.sb-loyalty-refer {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 8px 10px; border-radius: 20px;
    background: var(--surface); border: 1.5px solid var(--orange);
    color: var(--orange-dk); font-size: 11.5px; font-weight: 700;
    text-decoration: none; transition: all .15s;
}
.sb-loyalty-refer:hover { background: var(--orange); color: #fff; }

.sb-cta {
    background: linear-gradient(135deg, var(--navy) 0%, #1F3F75 100%);
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
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 900; color: #fff;
    font-family: var(--display); flex-shrink: 0; overflow: hidden;
    border: 2px solid rgba(255,255,255,.88);
    box-shadow: 0 3px 10px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.2);
    position: relative; letter-spacing: .3px;
}
.sb-top-av::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(160deg, rgba(255,255,255,.16) 0%, transparent 55%);
    border-radius: 50%;
}
.sb-top-av img { width: 100%; height: 100%; object-fit: cover; }
.sb-top-info { flex: 1; min-width: 0; }
.sb-top-name { font-size: 12px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-top-rating { font-size: 10.5px; color: var(--orange); font-weight: 600; }

/* ══ MODALE CLASSEMENT ══ */
.rank-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 800;
    align-items: center; justify-content: center;
    padding: 16px; backdrop-filter: blur(4px);
    animation: fadeOverlay .2s ease;
}
@keyframes fadeOverlay { from{opacity:0} to{opacity:1} }
.rank-overlay.open { display: flex; }
.rank-modal {
    background: var(--surface); border-radius: 20px;
    width: 100%; max-width: 480px; max-height: 88vh;
    display: flex; flex-direction: column;
    box-shadow: 0 24px 80px rgba(0,0,0,.3);
    animation: slideUpModal .28s cubic-bezier(.23,1,.32,1);
    overflow: hidden;
}
@keyframes slideUpModal { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }
.rank-modal-head {
    padding: 18px 20px 14px;
    border-bottom: 1px solid var(--grey);
    display: flex; align-items: center; gap: 10px;
    flex-shrink: 0;
}
.rank-modal-title { font-size: 17px; font-weight: 800; color: var(--text); flex: 1; }
.rank-modal-close {
    width: 32px; height: 32px; border-radius: 50%; border: none;
    background: var(--grey); color: var(--text); font-size: 16px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.rank-modal-close:hover { background: var(--border); }
.rank-modal-body { flex: 1; overflow-y: auto; padding: 8px 0; }
.rank-row {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 20px; border-bottom: 1px solid var(--grey);
    text-decoration: none; color: inherit; transition: background .12s;
}
.rank-row:last-child { border-bottom: none; }
.rank-row:hover { background: var(--grey); }
.rank-pos {
    font-size: 18px; font-weight: 900; font-family: var(--display);
    width: 30px; text-align: center; flex-shrink: 0;
    color: var(--orange);
}
.rank-pos.gold   { color: #f59e0b; font-size: 22px; }
.rank-pos.silver { color: #94a3b8; font-size: 22px; }
.rank-pos.bronze { color: #cd7c3c; font-size: 22px; }
.rank-av {
    width: 46px; height: 46px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 900; color: #fff;
    overflow: hidden; border: 2px solid rgba(255,255,255,.8);
    box-shadow: 0 3px 10px rgba(0,0,0,.2);
}
.rank-av img { width: 100%; height: 100%; object-fit: cover; }
.rank-info { flex: 1; min-width: 0; }
.rank-name { font-size: 13.5px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rank-stats { font-size: 11.5px; color: var(--muted); margin-top: 2px; }
.rank-stats strong { color: var(--orange); }
.rank-link {
    flex-shrink: 0; padding: 6px 13px; border-radius: 20px;
    background: var(--orange); color: #fff;
    font-size: 11.5px; font-weight: 700; text-decoration: none;
    transition: background .15s;
}
.rank-link:hover { background: var(--orange-dk); }
.rank-modal-foot {
    padding: 10px 16px; border-top: 1px solid var(--grey); flex-shrink: 0;
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
}
.rank-pg-info { font-size: 12px; color: var(--muted); font-weight: 600; }
.rank-pg-btns { display: flex; gap: 6px; }
.rank-pg-btn {
    padding: 6px 14px; border-radius: 20px; border: 1.5px solid var(--border);
    background: var(--surface); color: var(--text); font-size: 12px; font-weight: 700;
    cursor: pointer; font-family: var(--font); transition: all .15s;
}
.rank-pg-btn:hover:not(:disabled) { border-color: var(--orange); color: var(--orange); }
.rank-pg-btn:disabled { opacity: .35; cursor: not-allowed; }

/* Wrapper catégories sidebar — transparent sur desktop, flex horizontal sur mobile */
.sb-cat-wrap-mob { display: contents; }

/* ══ MAIN COLUMN ══ */
.main-col { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 18px; }

/* ══ HERO ══ */
.hero {
    border-radius: var(--r); overflow: hidden; position: relative;
    height: 300px;
    background:
        linear-gradient(to right,
            #07111f        0%,
            #07111f       43%,
            rgba(7,17,31,.86) 53%,
            rgba(7,17,31,.18) 64%,
            transparent    72%
        ),
        url('/images/phone.png') right center / auto 100% no-repeat,
        #07111f;
    display: flex; align-items: center;
}
.hero-content {
    position: relative; z-index: 1;
    padding: 32px 42px; max-width: 560px;
}
.hero-welcome {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 50px; margin-bottom: 10px;
    font-size: 11.5px; font-weight: 700; color: rgba(255,255,255,.85);
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.16);
    letter-spacing: .2px;
}
.hero-title {
    font-family: var(--display); font-weight: 900; font-size: 30px;
    color: #fff; line-height: 1.15; margin: 0 0 8px; letter-spacing: -.5px;
}
.hero-title .country {
    color: #7DB4FF;
    text-shadow: 0 0 24px rgba(31,124,246,.45);
}
.hero-subtitle {
    font-size: 12.5px; color: rgba(255,255,255,.58);
    line-height: 1.55; margin: 0 0 14px; max-width: 380px;
}
.hero-subtitle strong { color: rgba(255,255,255,.85); }
.hero-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 10px; border-radius: 8px;
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
    backdrop-filter: blur(10px); white-space: nowrap;
}
.hero-badge-ico { font-size: 13px; flex-shrink: 0; }
.hero-badge-txt { display: flex; flex-direction: column; gap: 1px; }
.hero-badge-label { font-size: 10.5px; font-weight: 700; color: #fff; line-height: 1; }
.hero-badge-sub { font-size: 9px; color: rgba(255,255,255,.4); line-height: 1; }
/* Carte rating haut droite */
.hero-rating-card {
    position: absolute; top: 16px; right: 16px; z-index: 3;
    background: rgba(7,17,31,.62); border: 1px solid rgba(255,255,255,.18);
    border-radius: 12px; padding: 11px 16px; text-align: center;
    backdrop-filter: blur(24px); min-width: 120px;
    box-shadow: 0 6px 24px rgba(0,0,0,.45);
}
.hero-rating-stars { color: #fbbf24; font-size: 13px; letter-spacing: 2px; margin-bottom: 3px; }
.hero-rating-val { font-family: var(--display); font-size: 22px; font-weight: 900; color: #fff; line-height: 1; }
.hero-rating-lbl { font-size: 9.5px; color: rgba(255,255,255,.5); margin-top: 2px; }
.hero-rating-reviews { font-size: 10.5px; font-weight: 700; color: var(--orange); margin-top: 4px; }

/* ══ STATS ROW ══ */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.stat-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 16px 18px;
    box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 14px;
    transition: box-shadow .2s;
}
.stat-card:hover { box-shadow: var(--shadow); }
.stat-ico {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.stat-ico.s1 { background: #eff6ff; color: #3b82f6; }
.stat-ico.s2 { background: #EAF2FF; color: var(--orange); }
.stat-ico.s3 { background: #f0fdf4; color: #22c55e; }
.stat-ico.s4 { background: #fdf4ff; color: #7DB4FF; }
.stat-val {
    font-family: var(--display); font-size: 22px; font-weight: 900;
    color: var(--text); line-height: 1; white-space: nowrap;
}
.stat-lbl { font-size: 12px; font-weight: 600; color: var(--text-2); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stat-sub { font-size: 10.5px; color: var(--muted); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
/* Wrapper texte stat — min-width: 0 obligatoire pour que ellipsis fonctionne dans flex */
.stat-card > div:last-child { min-width: 0; overflow: hidden; }

/* ══ POP CATEGORIES ROW ══ */
.pop-cats-row {
    display: flex; gap: 10px; overflow-x: auto;
    padding-bottom: 4px; scrollbar-width: none;
}
.pop-cats-row::-webkit-scrollbar { display: none; }
.pop-cat-chip {
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: 12px; padding: 11px 16px;
    cursor: pointer; transition: all .18s; display: flex;
    flex-direction: column; align-items: center; gap: 4px;
    box-shadow: var(--shadow-sm); text-decoration: none;
    flex-shrink: 0; min-width: 90px; text-align: center;
}
.pop-cat-chip:hover { border-color: var(--orange); box-shadow: 0 4px 14px rgba(31,124,246,.18); transform: translateY(-2px); }
.pop-cat-chip.active { background: var(--orange-lt); border-color: var(--orange); }
.pop-cat-chip.active .pop-cat-name { color: var(--orange); }
.pop-cat-ico { font-size: 24px; line-height: 1; }
.pop-cat-name { font-size: 11px; font-weight: 700; color: var(--text); line-height: 1.2; white-space: nowrap; }
.pop-cat-cnt { font-size: 10px; color: var(--muted); white-space: nowrap; }

/* ══ TOP SHOPS GRID (4 cards) ══ */
.top-shops-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.top-shop-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); transition: all .22s;
    display: flex; flex-direction: column;
}
.top-shop-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: transparent; }
.top-shop-img {
    height: 140px; overflow: hidden; position: relative;
    background: var(--grey); flex-shrink: 0;
}
.top-shop-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.top-shop-card:hover .top-shop-img img { transform: scale(1.07); }
.top-shop-img-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 38px; }
.top-shop-open-badge {
    position: absolute; top: 10px; left: 10px;
    background: #22c55e; color: #fff;
    font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 20px;
    letter-spacing: .3px;
}
.top-shop-heart {
    position: absolute; top: 8px; right: 8px;
    width: 30px; height: 30px; border-radius: 50%;
    background: rgba(255,255,255,.9); display: flex; align-items: center; justify-content: center;
    font-size: 14px; cursor: pointer; border: none;
    transition: background .15s; box-shadow: 0 2px 6px rgba(0,0,0,.15);
}
.top-shop-heart:hover { background: #fff; }
.top-shop-heart.favorited { background: #fff0f0; color: #e53e3e; }
.top-shop-heart.favorited svg { stroke: #e53e3e; fill: #e53e3e; }

/* Bouton cœur sur cartes boutiques principales */
.shop-card-fav-btn {
    position: absolute; top: 8px; right: 8px;
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(255,255,255,.9); display: flex; align-items: center; justify-content: center;
    border: none; cursor: pointer; z-index: 2;
    transition: all .18s; box-shadow: 0 2px 8px rgba(0,0,0,.15);
    color: var(--text-2);
}
.shop-card-fav-btn:hover { background: #fff0f0; color: #e53e3e; transform: scale(1.12); }
.shop-card-fav-btn.favorited { background: #fff0f0; color: #e53e3e; }
.shop-card-fav-btn.favorited svg { stroke: #e53e3e; fill: #e53e3e; }

/* Bouton cœur navbar */
.nav-fav-btn {
    position: relative;
    width: 38px; height: 38px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s; flex-shrink: 0; color: var(--text-2);
}
.nav-fav-btn:hover { border-color: #e53e3e; background: #fff0f0; color: #e53e3e; }
.nav-fav-badge {
    position: absolute; top: -4px; right: -4px;
    background: #e53e3e; color: #fff;
    font-size: 9px; font-weight: 800;
    border-radius: 20px; padding: 1px 5px;
    min-width: 16px; text-align: center;
    font-variant-numeric: tabular-nums; border: 1.5px solid var(--surface);
    display: none;
}
.nav-fav-badge.show { display: block; }

/* Bouton panier navbar */
.nav-cart-btn {
    position: relative;
    width: 38px; height: 38px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s; flex-shrink: 0; color: var(--text-2);
    text-decoration: none;
}
.nav-cart-btn:hover { border-color: var(--orange); background: var(--orange-lt); color: var(--orange-dk); }
.nav-cart-badge {
    position: absolute; top: -4px; right: -4px;
    background: var(--orange); color: #fff;
    font-size: 9px; font-weight: 800;
    border-radius: 20px; padding: 1px 5px;
    min-width: 16px; text-align: center;
    font-variant-numeric: tabular-nums; border: 1.5px solid var(--surface);
    display: none;
}
.nav-cart-badge.show { display: block; }

/* ══ DRAWER FAVORIS ══ */
.fav-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.35); z-index: 400;
}
.fav-overlay.open { display: block; }

.fav-drawer {
    position: fixed; top: 0; right: 0; bottom: 0;
    width: 440px; max-width: 100vw;
    background: var(--surface);
    box-shadow: -4px 0 32px rgba(0,0,0,.15);
    z-index: 500; display: flex; flex-direction: column;
    transform: translateX(100%);
    transition: transform .28s cubic-bezier(.23,1,.32,1);
    visibility: hidden;
}
.fav-drawer.open { transform: translateX(0); visibility: visible; }

.fav-drawer-hd {
    padding: 18px 20px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; gap: 12px;
    background: linear-gradient(135deg, #fff5f5, var(--surface));
    flex-shrink: 0;
}
.fav-drawer-ico {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #e53e3e, #f97316);
    display: flex; align-items: center; justify-content: center;
    color: #fff; flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(229,62,62,.35);
}
.fav-drawer-title {
    font-family: var(--display); font-size: 17px; font-weight: 900;
    color: var(--text); flex: 1;
}
.fav-drawer-sub { font-size: 11px; color: var(--muted); margin-top: 1px; }
.fav-drawer-close {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1px solid var(--border); background: var(--grey);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--text-2); transition: all .15s; flex-shrink: 0;
}
.fav-drawer-close:hover { background: #fee2e2; border-color: #fca5a5; color: #e53e3e; }

.fav-list {
    flex: 1; overflow-y: auto; padding: 16px;
    scrollbar-width: thin; scrollbar-color: var(--border) transparent;
    display: flex; flex-direction: column; gap: 12px;
}
.fav-list::-webkit-scrollbar { width: 4px; }
.fav-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

.fav-shop-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); overflow: hidden;
    box-shadow: var(--shadow-sm); display: flex;
    transition: all .18s; text-decoration: none; color: inherit;
}
.fav-shop-card:hover { box-shadow: var(--shadow); border-color: var(--orange); }
.fav-shop-img {
    width: 90px; flex-shrink: 0;
    background: var(--grey); overflow: hidden;
}
.fav-shop-img img { width: 100%; height: 100%; object-fit: cover; }
.fav-shop-img-ph {
    width: 100%; height: 100%; min-height: 80px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
}
.fav-shop-info { flex: 1; padding: 12px 14px; min-width: 0; }
.fav-shop-type { font-size: 10px; font-weight: 700; color: var(--orange); text-transform: uppercase; letter-spacing: .5px; }
.fav-shop-name { font-family: var(--display); font-size: 13.5px; font-weight: 800; color: var(--text); margin: 2px 0 6px; }
.fav-shop-meta { display: flex; gap: 8px; flex-wrap: wrap; }
.fav-shop-chip {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10.5px; color: var(--text-2);
    background: var(--grey); border: 1px solid var(--border);
    border-radius: 4px; padding: 2px 7px;
}
.fav-shop-rm {
    display: flex; align-items: center; padding: 0 12px;
    border: none; background: none; cursor: pointer;
    color: var(--muted); transition: color .15s; flex-shrink: 0;
}
.fav-shop-rm:hover { color: #e53e3e; }

.fav-empty {
    padding: 48px 20px; text-align: center; color: var(--muted);
}
.fav-empty-ico {
    width: 72px; height: 72px; border-radius: 50%;
    background: #fff5f5; display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; color: #f9a8a8;
}
.fav-empty-title { font-family: var(--display); font-size: 16px; font-weight: 800; color: var(--text); margin-bottom: 6px; }
.fav-empty-sub { font-size: 13px; color: var(--muted); line-height: 1.5; }

.fav-drawer-footer {
    padding: 14px 20px; border-top: 1px solid var(--border);
    flex-shrink: 0;
}
.fav-visit-all-btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 12px; border-radius: var(--r);
    background: linear-gradient(135deg, var(--orange), var(--orange-dk));
    color: #fff; font-size: 13px; font-weight: 700; font-family: var(--font);
    border: none; cursor: pointer; text-decoration: none;
    transition: opacity .15s;
}
.fav-visit-all-btn:hover { opacity: .9; }

.top-shop-av-circle {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; font-weight: 900; color: #fff;
    font-family: var(--display); letter-spacing: .5px;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(0,0,0,.22);
}
.top-shop-body { padding: 12px 13px 10px; flex: 1; }
.top-shop-body-row { display: flex; gap: 11px; align-items: flex-start; }
.top-shop-head { display: flex; align-items: center; gap: 5px; margin-bottom: 4px; }
.top-shop-name { font-family: var(--display); font-size: 13.5px; font-weight: 800; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
.top-shop-verified { color: #3b82f6; font-size: 13px; flex-shrink: 0; }
.top-shop-tag {
    display: inline-block; font-size: 10px; font-weight: 700;
    color: var(--orange); background: var(--orange-lt);
    border-radius: 5px; padding: 2px 7px; margin-bottom: 5px;
    text-transform: uppercase; letter-spacing: .3px;
}
.top-shop-desc { font-size: 11.5px; color: var(--text-2); line-height: 1.45; margin-bottom: 5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.top-shop-loc { font-size: 10.5px; color: var(--muted); display: flex; align-items: center; gap: 3px; }
.top-shop-footer {
    padding: 10px 13px 13px; border-top: 1px solid var(--grey);
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
}
.top-shop-stats { display: flex; align-items: center; gap: 10px; }
.top-shop-rating { display: flex; align-items: center; gap: 3px; font-size: 11.5px; font-weight: 700; color: var(--orange); }
.top-shop-sales { font-size: 10.5px; color: var(--muted); display: flex; align-items: center; gap: 3px; }
.top-shop-btn {
    padding: 7px 13px; border-radius: 50px; text-align: center;
    font-size: 11.5px; font-weight: 700;
    background: var(--orange); color: #fff; text-decoration: none;
    transition: background .15s; white-space: nowrap; flex-shrink: 0;
}
.top-shop-btn:hover { background: var(--orange-dk); color: #fff; }

/* ══════════════════════════════════════
   RESPONSIVE — adapté à tous les écrans
   ══════════════════════════════════════ */

/* ── Grand écran (≤ 1280px) ── */
@media (max-width: 1280px) {
    .page-wrap { padding: 18px 18px 60px; }
    .top-shops-grid { grid-template-columns: repeat(3, 1fr); }
    /* Stats compactes pour tenir en 4 colonnes dans la zone 1100-1280px */
    .stat-card { padding: 13px 14px; gap: 11px; }
    .stat-ico { width: 40px; height: 40px; font-size: 17px; border-radius: 10px; }
    .stat-val { font-size: 19px; }
    .stat-lbl { font-size: 11px; }
    .stat-sub { display: none; }
}

/* ── Tablette paysage (≤ 1100px) ── */
@media (max-width: 1100px) {
    .page-wrap { padding: 16px 16px 60px; gap: 18px; }
    .sidebar { width: 234px; }
    .top-shops-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .hero { height: 270px; }
    .hero-title { font-size: 26px; }
    /* Liens nav cachés dès 1100px — trop encombrés avec sidebar + actions */
    .nav-links { display: none; }
}

/* ── Tablette portrait (≤ 900px) ── */
@media (max-width: 900px) {
    /* Nav */
    .nav { padding: 0 14px; gap: 12px; }

    /* Layout : sidebar passe au-dessus en ligne */
    .page-wrap {
        flex-direction: column;
        padding: 12px 12px 60px;
        gap: 14px;
    }
    .sidebar { width: 100%; }
    .sidebar-sticky {
        position: static;
        max-height: none;
        overflow-y: visible;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }
    .sb-card { min-width: 0; }

    /* Hero */
    .hero {
        height: 240px;
        background-size: auto 95% !important;
    }
    .hero-content { padding: 24px 28px; max-width: 480px; }
    .hero-title { font-size: 23px; }
    .hero-subtitle { font-size: 12px; margin-bottom: 10px; }
    .hero-badge { padding: 5px 8px; }
    .hero-badge-sub { display: none; }
    /* Rating card : cachée sur tablette — elle bloque l'image téléphone */
    .hero-rating-card { display: none; }
    /* Contenu occupe plus d'espace */
    .hero-content { max-width: 58%; }

    /* Grilles */
    .stats-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .top-shops-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .prod-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
}

/* ══════════════════════════════════════════
   MOBILE (≤ 640px) — body.is-dashboard + !important = priorité absolue
══════════════════════════════════════════ */
@media (max-width: 640px) {

    /* ── Navbar ── */
    body.is-dashboard .nav { padding: 0 10px !important; gap: 6px !important; flex-wrap: nowrap !important; height: var(--nav-h) !important; }
    body.is-dashboard .nav-logo img { height: 38px !important; width: auto !important; max-width: 90px !important; object-fit: contain !important; }
    body.is-dashboard .nav-links { display: none !important; }
    body.is-dashboard .nav-search { flex: 1 !important; min-width: 0 !important; max-width: 120px !important; }
    body.is-dashboard .nav-search input { font-size: 16px !important; } /* évite le zoom automatique iOS */
    body.is-dashboard .nav-orders-btn { display: none !important; }
    body.is-dashboard .nav-actions { gap: 4px !important; flex-shrink: 0 !important; }
    body.is-dashboard .nav-av { width: 32px !important; height: 32px !important; font-size: 11px !important; }
    body.is-dashboard .cn-bell-btn,
    body.is-dashboard .nav-fav-btn,
    body.is-dashboard .nav-msg-btn { width: 32px !important; height: 32px !important; }

    /* ── Layout ── */
    body.is-dashboard .page-wrap { flex-direction: column !important; padding: 10px 10px 70px !important; gap: 14px !important; }
    body.is-dashboard .main-col { gap: 14px !important; order: 1 !important; }

    /* ── Sidebar : on cache les cartes inutiles, on garde le top des boutiques ── */
    body.is-dashboard .sidebar { display: flex !important; flex-direction: column !important; order: 2 !important; width: 100% !important; }
    body.is-dashboard .sidebar-sticky { gap: 0 !important; position: static !important; max-height: none !important; overflow-y: visible !important; }
    body.is-dashboard .sb-card-explorer { display: none !important; }
    body.is-dashboard .sidebar-sticky > .sb-card:not(.sb-card-topshops):not(.sb-card-explorer) { display: none !important; }
    body.is-dashboard .sb-card-topshops { display: block !important; border-radius: 10px !important; overflow: hidden !important; }

    /* ── Hero mobile ── */
    body.is-dashboard .hero {
        height: auto !important;
        min-height: 180px !important;
        background:
            linear-gradient(to right,
                rgba(7,17,31,1)   0%,
                rgba(7,17,31,.97) 30%,
                rgba(7,17,31,.75) 50%,
                rgba(7,17,31,.20) 68%,
                transparent       80%
            ),
            url('/images/phone.png') center center / cover no-repeat,
            #07111f !important;
        flex-direction: row !important;
        align-items: flex-start !important;
        padding: 0 !important;
    }
    body.is-dashboard .hero-content { padding: 16px 14px !important; max-width: 62% !important; z-index: 2 !important; position: relative !important; }
    body.is-dashboard .hero-welcome { font-size: 9px !important; padding: 2px 7px !important; margin-bottom: 5px !important; }
    body.is-dashboard .hero-title { font-size: 16px !important; letter-spacing: -.3px !important; margin-bottom: 5px !important; line-height: 1.25 !important; }
    body.is-dashboard .hero-subtitle { font-size: 11px !important; line-height: 1.4 !important; margin-bottom: 6px !important; display: block !important; }
    body.is-dashboard .hero-badges { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 3px !important; }
    body.is-dashboard .hero-badge { padding: 3px 5px !important; gap: 2px !important; border-radius: 5px !important; }
    body.is-dashboard .hero-badge-ico { font-size: 9px !important; }
    body.is-dashboard .hero-badge-label { font-size: 8px !important; }
    body.is-dashboard .hero-badge-sub { display: none !important; }
    body.is-dashboard .hero-rating-card { display: none !important; }

    /* ── Stats 2×2 ── */
    body.is-dashboard .stats-row { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 8px !important; }
    body.is-dashboard .stat-card { padding: 12px 10px !important; gap: 8px !important; flex-direction: row !important; align-items: center !important; }
    body.is-dashboard .stat-ico { width: 36px !important; height: 36px !important; font-size: 16px !important; border-radius: 9px !important; flex-shrink: 0 !important; }
    body.is-dashboard .stat-val { font-size: 16px !important; }
    body.is-dashboard .stat-lbl { font-size: 10.5px !important; white-space: normal !important; overflow: visible !important; text-overflow: clip !important; }
    body.is-dashboard .stat-sub { display: block !important; font-size: 8.5px !important; white-space: normal !important; overflow: visible !important; }

    /* ── Catégories — scroll horizontal ── */
    body.is-dashboard .pop-cat-extra { display: flex !important; }
    body.is-dashboard #popCatsMoreBtn { display: none !important; }
    body.is-dashboard .pop-cats-row { -webkit-overflow-scrolling: touch !important; }
    body.is-dashboard .pop-cat-chip { min-width: 72px !important; padding: 8px 10px !important; flex-shrink: 0 !important; }
    body.is-dashboard .pop-cat-ico { font-size: 20px !important; }
    body.is-dashboard .pop-cat-name { font-size: 10px !important; }
    body.is-dashboard .pop-cat-cnt { display: none !important; }

    /* ── Boutiques populaires — carousel horizontal ── */
    body.is-dashboard .top-shops-grid { display: flex !important; flex-direction: row !important; overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; gap: 10px !important; padding-bottom: 8px !important; scrollbar-width: none !important; }
    body.is-dashboard .top-shops-grid::-webkit-scrollbar { display: none !important; }
    body.is-dashboard .top-shop-card { flex-shrink: 0 !important; width: 165px !important; min-width: 165px !important; max-width: 165px !important; }
    body.is-dashboard .top-shop-img { height: 100px !important; }
    body.is-dashboard .top-shop-body { padding: 7px 8px !important; }
    body.is-dashboard .top-shop-name { font-size: 11.5px !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important; }
    body.is-dashboard .top-shop-desc { display: none !important; }
    body.is-dashboard .top-shop-loc { display: none !important; }
    body.is-dashboard .top-shop-tag { font-size: 8px !important; padding: 1px 5px !important; }
    body.is-dashboard .shop-av { width: 30px !important; height: 30px !important; font-size: 10px !important; flex-shrink: 0 !important; }
    body.is-dashboard .top-shop-footer { padding: 5px 8px 8px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; gap: 4px !important; }
    body.is-dashboard .top-shop-stats { font-size: 9px !important; gap: 2px !important; }
    body.is-dashboard .top-shop-sales { display: none !important; }
    body.is-dashboard .top-shop-btn { padding: 4px 8px !important; font-size: 9.5px !important; white-space: nowrap !important; flex-shrink: 0 !important; }

    /* ── Commandes récentes ── */
    body.is-dashboard .orders-card { margin-bottom: 0 !important; }
    body.is-dashboard .order-row { padding: 10px 12px !important; gap: 8px !important; }
    body.is-dashboard .order-ico { width: 34px !important; height: 34px !important; font-size: 13px !important; border-radius: 8px !important; flex-shrink: 0 !important; }
    body.is-dashboard .order-ref { font-size: 12px !important; }
    body.is-dashboard .order-shop { font-size: 10.5px !important; }
    body.is-dashboard .order-amount { font-size: 12px !important; white-space: nowrap !important; }
    body.is-dashboard .order-pill { font-size: 9px !important; padding: 2px 6px !important; }

    /* ── Filtres catégories ── */
    body.is-dashboard .cats { flex-wrap: nowrap !important; overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; gap: 6px !important; padding-bottom: 6px !important; margin-bottom: 0 !important; }
    body.is-dashboard .cat-pill { padding: 6px 12px !important; font-size: 11px !important; flex-shrink: 0 !important; }

    /* ── Grille produits 2 colonnes (espacée, uniforme) ── */
    body.is-dashboard .prod-grid { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 14px !important; }
    body.is-dashboard .prod-grid .reco-card { border-radius: 14px !important; }
    body.is-dashboard .prod-grid .reco-card-img { height: 150px !important; }
    body.is-dashboard .prod-grid .reco-card-body { padding: 12px !important; gap: 6px !important; }
    body.is-dashboard .prod-grid .reco-card-shop { font-size: 10.5px !important; }
    body.is-dashboard .prod-grid .reco-card-name { font-size: 12.5px !important; min-height: 2.4em !important; }
    body.is-dashboard .prod-grid .reco-card-price { font-size: 14px !important; }
    body.is-dashboard .prod-grid .reco-card-addcart { padding: 8px 0 !important; font-size: 11px !important; margin-top: 8px !important; }
    body.is-dashboard .prod-grid .reco-card-actions-row { margin-top: 6px !important; }
    body.is-dashboard .shop-card-img { height: 110px !important; }
    body.is-dashboard .shop-card-body { padding: 7px 8px !important; gap: 2px !important; }
    body.is-dashboard .shop-card-footer { padding: 6px 8px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; }
    body.is-dashboard .shop-card-name { font-size: 11.5px !important; line-height: 1.2 !important; }
    body.is-dashboard .shop-card-type { font-size: 9px !important; }
    body.is-dashboard .shop-card-desc { display: none !important; }
    body.is-dashboard .shop-card-cta { padding: 4px 7px !important; font-size: 9.5px !important; display: inline-flex !important; flex-shrink: 0 !important; white-space: nowrap !important; }
    body.is-dashboard .shop-card-rating { font-size: 9px !important; gap: 2px !important; flex: 1 !important; min-width: 0 !important; }
    body.is-dashboard .shop-card-rating small { display: none !important; }

    /* ── Titres ── */
    body.is-dashboard .sec-title { font-size: 15px !important; letter-spacing: -.2px !important; }
    body.is-dashboard .sec-title > span { display: none !important; }
    body.is-dashboard .sec-hd { margin-bottom: 10px !important; gap: 8px !important; }
    body.is-dashboard .sec-link { font-size: 11.5px !important; flex-shrink: 0 !important; white-space: nowrap !important; }

    /* ── Notifications — bottom-sheet géré par la règle @media ci-dessous ── */

    /* ── Drawers ── */
    body.is-dashboard .msg-drawer { width: 100vw !important; }
    body.is-dashboard .fav-drawer { width: 100vw !important; }
}

/* ── 390px ── */
@media (max-width: 390px) {
    body.is-dashboard .nav-search { max-width: 90px !important; }
    body.is-dashboard .hero-content { max-width: 60% !important; padding: 14px 12px !important; }
    body.is-dashboard .hero-subtitle { font-size: 10px !important; }
    body.is-dashboard .hero-badges { display: none !important; }
    body.is-dashboard .stat-val { font-size: 15px !important; }
    body.is-dashboard .stat-sub { font-size: 8px !important; }
    body.is-dashboard .top-shop-card { width: 150px !important; min-width: 150px !important; max-width: 150px !important; }
    body.is-dashboard .top-shop-img { height: 90px !important; }
    body.is-dashboard .prod-grid { gap: 11px !important; }
    body.is-dashboard .prod-grid .reco-card-img { height: 135px !important; }
    body.is-dashboard .shop-card-img { height: 95px !important; }
    body.is-dashboard .page-wrap { padding-bottom: 70px !important; }
    body.is-dashboard .order-amount { font-size: 11px !important; }
    body.is-dashboard .order-pill { font-size: 8px !important; padding: 2px 5px !important; }
}

/* ── 360px ── */
@media (max-width: 360px) {
    body.is-dashboard .nav { padding: 0 8px !important; }
    body.is-dashboard .nav-search { display: none !important; }
    body.is-dashboard .page-wrap { padding: 8px 8px 74px !important; }
    body.is-dashboard .hero-content { max-width: 62% !important; padding: 12px 10px !important; }
    body.is-dashboard .hero-title { font-size: 15px !important; }
    body.is-dashboard .top-shop-card { width: 135px !important; min-width: 135px !important; max-width: 135px !important; }
    body.is-dashboard .top-shop-img { height: 80px !important; }
    body.is-dashboard .prod-grid { gap: 9px !important; }
    body.is-dashboard .prod-grid .reco-card-img { height: 125px !important; }
    body.is-dashboard .prod-grid .reco-card-body { padding: 10px !important; }
    body.is-dashboard .shop-card-img { height: 95px !important; }
    body.is-dashboard .shop-card-footer { flex-direction: column !important; gap: 4px !important; align-items: stretch !important; }
    body.is-dashboard .shop-card-cta { text-align: center !important; justify-content: center !important; width: 100% !important; }
    body.is-dashboard .order-ico { width: 30px !important; height: 30px !important; font-size: 12px !important; }
    body.is-dashboard .order-pill { display: none !important; }
    body.is-dashboard .order-amount { font-size: 11px !important; }
}

/* ── 320px (iPhone SE ancien / Galaxy petit) ── */
@media (max-width: 320px) {
    body.is-dashboard .page-wrap { padding: 6px 6px 74px !important; }
    body.is-dashboard .hero-content { max-width: 65% !important; padding: 10px 9px !important; }
    body.is-dashboard .hero-title { font-size: 13px !important; }
    body.is-dashboard .hero-subtitle { display: none !important; }
    body.is-dashboard .hero-welcome { display: none !important; }
    body.is-dashboard .stats-row { grid-template-columns: 1fr 1fr !important; gap: 5px !important; }
    body.is-dashboard .stat-card { padding: 8px 7px !important; gap: 6px !important; }
    body.is-dashboard .stat-ico { width: 28px !important; height: 28px !important; font-size: 13px !important; }
    body.is-dashboard .stat-val { font-size: 13px !important; }
    body.is-dashboard .stat-lbl { font-size: 9px !important; }
    body.is-dashboard .stat-sub { display: none !important; }
    body.is-dashboard .prod-grid { grid-template-columns: 1fr 1fr !important; gap: 7px !important; }
    body.is-dashboard .prod-grid .reco-card-img { height: 110px !important; }
    body.is-dashboard .prod-grid .reco-card-body { padding: 8px !important; gap: 4px !important; }
    body.is-dashboard .prod-grid .reco-card-name { font-size: 11.5px !important; min-height: 2.2em !important; }
    body.is-dashboard .shop-card-img { height: 80px !important; }
    body.is-dashboard .shop-card-name { font-size: 10px !important; }
    body.is-dashboard .shop-card-footer { flex-direction: column !important; gap: 3px !important; align-items: stretch !important; }
    body.is-dashboard .shop-card-cta { text-align: center !important; justify-content: center !important; width: 100% !important; font-size: 9px !important; }
    body.is-dashboard .top-shop-card { width: 120px !important; min-width: 120px !important; }
    body.is-dashboard .order-row { gap: 6px !important; padding: 8px 8px !important; }
    body.is-dashboard .order-ico { width: 28px !important; height: 28px !important; }
    body.is-dashboard .order-ref { font-size: 11px !important; }
    body.is-dashboard .order-shop { font-size: 10px !important; }
    body.is-dashboard .order-amount { font-size: 10px !important; }
    body.is-dashboard .order-pill { display: none !important; }
    body.is-dashboard .nav-av { width: 28px !important; height: 28px !important; }
    body.is-dashboard .nav-fav-btn,
    body.is-dashboard .nav-msg-btn,
    body.is-dashboard .cn-bell-btn { width: 28px !important; height: 28px !important; }
}

/* ══ BARRE NAVIGATION BAS — MOBILE ══ */
.mob-bottom-nav {
    display: none;
    position: fixed; bottom: 0; left: 0; right: 0;
    height: 58px;
    background: var(--surface);
    border-top: 1px solid var(--border);
    box-shadow: 0 -2px 14px rgba(0,0,0,.09);
    z-index: 200;
    padding: 0;
    padding-bottom: env(safe-area-inset-bottom, 0px);
}
.mob-bottom-nav-inner {
    display: flex; align-items: stretch;
    height: 58px;
}
.mob-nav-item {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 2px; color: var(--muted);
    text-decoration: none; border: none; background: none;
    font-size: 9.5px; font-weight: 700; font-family: var(--font);
    border-radius: 0; cursor: pointer;
    transition: color .15s, background .15s;
    padding: 4px 2px 4px;
    -webkit-tap-highlight-color: transparent;
}
.mob-nav-item svg { flex-shrink: 0; }
.mob-nav-item.active { color: var(--orange); }
.mob-nav-item:hover { color: var(--orange); }
.mob-nav-item.active svg { stroke: var(--orange); }
.mob-nav-badge {
    position: absolute; top: 4px; right: calc(50% - 18px);
    background: var(--orange); color: #fff;
    font-size: 8px; font-weight: 800;
    border-radius: 10px; padding: 1px 4px;
    min-width: 14px; text-align: center;
    font-variant-numeric: tabular-nums; border: 1.5px solid var(--surface);
    display: none; line-height: 1.4;
}
.mob-nav-badge.show { display: block; }
.mob-nav-item-wrap { position: relative; display: flex; flex-direction: column; align-items: center; gap: 2px; }

@media (max-width: 640px) {
    .mob-bottom-nav { display: block; }

    /* Shop card body — légèrement plus compact */
    body.is-dashboard .shop-card-body > div:first-child { gap: 7px !important; margin-bottom: 4px !important; }

    /* Order row — lisibilité */
    body.is-dashboard .order-info { min-width: 0; overflow: hidden; }

    /* Empêcher tout débordement horizontal */
    body.is-dashboard .main-col,
    body.is-dashboard .sidebar { max-width: 100%; overflow-x: hidden; }

    /* Pagination responsive */
    body.is-dashboard .c-pagination .pagination { flex-wrap: wrap; gap: 4px; justify-content: center; }
    body.is-dashboard .c-pagination .page-item .page-link { padding: 6px 10px; font-size: 12px; }
}

/* ══════════════════════════════════════
   FLUIDITÉ — GPU, TOUCH, SCROLL
══════════════════════════════════════ */

/* Préparer les couches GPU pour les éléments animés */
.shop-card,
.top-shop-card,
.pop-cat-chip,
.cat-pill,
.mob-bottom-nav,
.msg-drawer,
.fav-drawer {
    will-change: transform;
}
.shop-card-img img,
.top-shop-img img {
    will-change: transform;
}

/* Supprimer le flash bleu au tap sur tout élément interactif */
a, button, [role="button"],
.shop-card, .top-shop-card,
.sb-cat-item, .sb-top-shop,
.cat-pill, .pop-cat-chip,
.order-row, .msg-conv-item {
    -webkit-tap-highlight-color: transparent;
}

/* Scroll interne plus réactif sur iOS */
.msg-conv-list, .msg-thread,
.fav-list, .pop-cats-row,
.top-shops-grid, .cats,
.sidebar-sticky, .cn-drop-list {
    -webkit-overflow-scrolling: touch;
}

/* Éviter le rebond de page (overscroll) sur mobile */
html, body { overscroll-behavior-y: none; }

/* Scroll snap sur le carousel boutiques populaires (mobile) */
@media (max-width: 640px) {
    body.is-dashboard .top-shops-grid {
        scroll-snap-type: x mandatory !important;
        scroll-padding: 0 10px !important;
    }
    body.is-dashboard .top-shop-card {
        scroll-snap-align: start !important;
    }
}

/* Animation d'entrée des cartes boutiques au chargement */
@keyframes cardFadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
.shops-grid .shop-card {
    animation: cardFadeIn .3s ease both;
}
.shops-grid .shop-card:nth-child(1)  { animation-delay: .03s; }
.shops-grid .shop-card:nth-child(2)  { animation-delay: .06s; }
.shops-grid .shop-card:nth-child(3)  { animation-delay: .09s; }
.shops-grid .shop-card:nth-child(4)  { animation-delay: .12s; }
.shops-grid .shop-card:nth-child(5)  { animation-delay: .15s; }
.shops-grid .shop-card:nth-child(6)  { animation-delay: .18s; }
.shops-grid .shop-card:nth-child(7)  { animation-delay: .21s; }
.shops-grid .shop-card:nth-child(8)  { animation-delay: .24s; }
.shops-grid .shop-card:nth-child(n+9){ animation-delay: .27s; }

/* Fade du conteneur lors du filtrage */
#shopsGrid { transition: opacity .18s ease; }

/* ── Recherche live — badge produit + highlight + info + empty ── */
.shop-prod-match {
    display: none; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 700;
    color: var(--orange); background: rgba(31,124,246,.08);
    border: 1px solid rgba(31,124,246,.25);
    padding: 2px 8px; border-radius: 20px;
    margin-top: 4px; width: fit-content;
}
.shop-card.prod-match .shop-prod-match { display: inline-flex; }
.shop-name-clamp mark {
    background: rgba(31,124,246,.18); color: var(--orange-dk);
    border-radius: 3px; padding: 0 2px; font-style: normal;
}
#searchLiveInfo {
    font-size: 12px; color: var(--muted);
    padding: 4px 0 0; display: none;
    animation: fadeIn .2s ease;
}
#searchLiveInfo strong { color: var(--text); }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
#shopsLiveEmpty {
    display: none; text-align: center; padding: 2.5rem 1rem;
    background: var(--surface); border-radius: var(--r);
    border: 1px dashed var(--border); margin-bottom: 20px;
}
#shopsLiveEmpty .ico { font-size: 2.5rem; display: block; margin-bottom: .6rem; }
#shopsLiveEmpty p { color: var(--muted); font-size: .88rem; margin: 0; }

/* Stat cards — légère animation d'entrée */
@keyframes statIn {
    from { opacity: 0; transform: scale(.95); }
    to   { opacity: 1; transform: scale(1); }
}
.stat-card {
    animation: statIn .35s ease both;
}
.stat-card:nth-child(1) { animation-delay: .05s; }
.stat-card:nth-child(2) { animation-delay: .10s; }
.stat-card:nth-child(3) { animation-delay: .15s; }
.stat-card:nth-child(4) { animation-delay: .20s; }

/* Touch feedback visuel sur les cartes (mobile) */
@media (hover: none) {
    .shop-card:active  { transform: scale(.98) !important; box-shadow: var(--shadow-sm) !important; }
    .top-shop-card:active { transform: scale(.97) !important; }
    .cat-pill:active, .pop-cat-chip:active { transform: scale(.96) !important; }
    .hero-btn-primary:active, .hero-btn-secondary:active { transform: scale(.97) !important; }
    .shop-card-cta:active, .top-shop-btn:active { transform: scale(.96) !important; }
    .mob-nav-item:active { background: var(--orange-lt) !important; }
}

/* ══ CLOCHE NOTIFICATION CLIENT ══ */
.cn-bell-wrap { position: relative; display: inline-flex; }
.cn-bell-btn {
    position: relative; width: 38px; height: 38px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s; flex-shrink: 0; color: var(--text-2);
}
.cn-bell-btn:hover,.cn-bell-btn.has-notif { border-color: var(--orange); background: var(--orange-lt); color: var(--orange); }
.cn-bell-badge {
    position: absolute; top: -4px; right: -4px;
    background: var(--orange); color: #fff; font-size: 9px; font-weight: 800;
    border-radius: 20px; padding: 1px 5px; min-width: 16px; text-align: center;
    font-variant-numeric: tabular-nums; border: 1.5px solid var(--surface); display: none;
}
.cn-bell-badge.show { display: block; }

/* Backdrop (mobile) */
.cn-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.38); backdrop-filter: blur(2px);
    z-index: 598;
}
.cn-backdrop.active { display: block; }

/* Panel */
.cn-dropdown {
    position: absolute; top: calc(100% + 10px); right: 0;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); box-shadow: var(--shadow-lg);
    width: 310px; z-index: 600; overflow: hidden;
    visibility: hidden; opacity: 0; transform: translateY(-8px) scale(.97); pointer-events: none;
    transition: opacity .22s cubic-bezier(.23,1,.32,1), transform .22s cubic-bezier(.23,1,.32,1), visibility 0s .22s;
}
.cn-dropdown.open { visibility: visible; opacity: 1; transform: translateY(0) scale(1); pointer-events: all; transition: opacity .22s cubic-bezier(.23,1,.32,1), transform .22s cubic-bezier(.23,1,.32,1), visibility 0s 0s; }

.cn-drop-hd {
    padding: 12px 14px 11px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--grey);
}
.cn-drop-title { font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 6px; }
.cn-drop-total { background: var(--orange); color: #fff; font-size: 10px; font-weight: 700; border-radius: 20px; padding: 1px 8px; }
.cn-drop-list { max-height: 340px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--border) transparent; }
.cn-drop-list::-webkit-scrollbar { width: 4px; }
.cn-drop-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

.cn-notif-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 11px 14px; border-bottom: 1px solid #f3f6f9;
    cursor: pointer; transition: background .12s; text-decoration: none; color: inherit;
}
.cn-notif-item:hover { background: var(--grey); }
.cn-notif-item:last-child { border-bottom: none; }
.cn-notif-section { padding: 6px 14px 3px; font-size: 9.5px; font-weight: 800; letter-spacing: 1.2px; color: #94a3b8; text-transform: uppercase; display: flex; align-items: center; gap: 5px; }
.cn-notif-ico { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; }
.cn-notif-ico.msg   { background: linear-gradient(135deg, #7DB4FF, #1668D8); }
.cn-notif-ico.c-ok  { background: linear-gradient(135deg, #22c55e, #16a34a); }
.cn-notif-ico.c-del { background: linear-gradient(135deg, #f59e0b, #d97706); }
.cn-notif-ico.c-done{ background: linear-gradient(135deg, #1F7CF6, #1260C9); }
.cn-notif-body { flex: 1; min-width: 0; }
.cn-notif-name { font-size: 12px; font-weight: 700; color: var(--text); }
.cn-notif-txt { font-size: 11.5px; color: #64748b; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin-top: 2px; }
.cn-notif-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 4px; }
.cn-notif-time { font-size: 10px; color: #94a3b8; }
.cn-notif-badge { font-size: 9.5px; font-weight: 800; padding: 2px 7px; border-radius: 20px; }
.cn-notif-badge.badge-msg  { background: #EAF2FF; color: #5b21b6; }
.cn-notif-badge.badge-ok   { background: #d1fae5; color: #065f46; }
.cn-notif-badge.badge-del  { background: #fef3c7; color: #92400e; }
.cn-notif-badge.badge-done { background: #EAF2FF; color: #5b21b6; }
.cn-notif-dismiss { width: 22px; height: 22px; border-radius: 6px; border: none; background: none; cursor: pointer; color: var(--muted); flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: all .12s; align-self: flex-start; margin-top: 1px; }
.cn-notif-dismiss:hover { background: #fee2e2; color: #e53e3e; }
.cn-drop-empty { padding: 28px 16px; text-align: center; color: var(--muted); font-size: 12.5px; }
.cn-drop-ft { padding: 8px 14px; border-top: 1px solid var(--border); }
.cn-drop-ft a { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; border-radius: var(--r-sm); font-size: 11.5px; font-weight: 700; text-decoration: none; background: var(--orange-lt); color: var(--orange); border: 1px solid var(--orange); transition: all .15s; }
.cn-drop-ft a:hover { background: var(--orange); color: #fff; }

/* Bottom-sheet sur mobile */
@media(max-width: 640px) {
    .cn-bell-wrap { position: static !important; }
    .cn-dropdown {
        position: fixed !important; top: auto !important; bottom: 0 !important;
        left: 0 !important; right: 0 !important;
        width: 100% !important; border-radius: 18px 18px 0 0;
        max-height: 78vh; overflow-y: auto;
        visibility: hidden; transform: translateY(102%); opacity: 1;
        transition: transform .3s cubic-bezier(.23,1,.32,1), visibility 0s .3s;
        z-index: 9000;
    }
    .cn-dropdown.open { visibility: visible; transform: translateY(0); transition: transform .3s cubic-bezier(.23,1,.32,1), visibility 0s 0s; }
    .cn-dropdown::before {
        content: ''; display: block; width: 36px; height: 4px;
        background: rgba(0,0,0,.1); border-radius: 2px;
        margin: 10px auto 0;
    }
    .cn-drop-list { max-height: none; overflow-y: visible; }
    .cn-backdrop { z-index: 8999; }
}
</style>
