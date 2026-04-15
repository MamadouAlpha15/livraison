{{--
    resources/views/dashboards/client.blade.php
    Route     : GET /client/dashboard → Client\DashboardController@index
    Variables :
      $shops        → LengthAwarePaginator<Shop>
      $recentOrders → Collection<Order>
      $myMessages   → Collection groupée par shopId-productId
      $myUnread     → int
      Auth::user()  → client connecté
--}}
@extends('layouts.app')

@section('title', 'Accueil — Marketplace')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
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
    overflow-x: auto; padding-bottom: 4px;
    scrollbar-width: none;
}
.cats::-webkit-scrollbar { display: none; }
.cat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 50px; flex-shrink: 0;
    font-size: 13px; font-weight: 600; font-family: var(--font);
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--text-2); cursor: pointer; white-space: nowrap;
    text-decoration: none; transition: all .15s;
}
.cat-pill:hover { border-color: var(--orange); color: var(--orange); background: var(--orange-lt); }
.cat-pill.active { background: var(--orange); color: #fff; border-color: var(--orange-dk); }

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

/* ══ RESPONSIVE ══ */
@media (max-width: 900px) {
    .hero { margin: 16px; padding: 28px 24px; }
    .steps { margin: 0 16px 20px; padding: 16px 20px; flex-wrap: wrap; gap: 12px; }
    .step-arrow { display: none; }
    .c-main { padding: 0 16px 50px; }
    .hero-right { display: none; }
    .nav { padding: 0 16px; }
    .nav-links { display: none; }
}
@media (max-width: 600px) {
    .shops-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
    .hero-title { font-size: 22px; }
    .steps { display: none; }
    .nav-search { display: none; }
    .msg-drawer { width: 100vw; }
    .nav-orders-btn span { display: none; }
}
@media (max-width: 400px) {
    .shops-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')

@php
    $user      = auth()->user();
    $parts     = explode(' ', $user->name);
    $initials  = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
    $firstName = $parts[0];

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
@endphp

{{-- ══ DRAWER MESSAGES ══ --}}
<div class="msg-overlay" id="msgOverlay" onclick="closeMsgDrawer()"></div>
<div class="msg-drawer" id="msgDrawer">
    <div class="msg-drawer-hd">
        <span class="msg-drawer-title">💬 Mes Messages</span>
        @if($myUnread > 0)
        <span class="msg-drawer-badge">{{ $myUnread }} non lu{{ $myUnread > 1 ? 's' : '' }}</span>
        @endif
        <button class="msg-drawer-close" onclick="closeMsgDrawer()">✕</button>
    </div>
    <div class="msg-conv-list" id="msgConvList">
        @forelse($myMessages as $convKey => $msgs)
        @php
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
        @endphp
        <div class="msg-conv-item {{ $unreadCnt > 0 ? 'has-unread' : '' }}"
             onclick="openMsgModal({{ $convData }})" data-conv-key="{{ $convKey }}">
            <div class="msg-conv-av">
                @if($shop?->image)
                    <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $vName }}">
                @else
                    {{ $vInit }}
                @endif
                @if($unreadCnt > 0)<span class="msg-conv-av-dot"></span>@endif
            </div>
            <div class="msg-conv-info">
                <div class="msg-conv-name">{{ $vName }}</div>
                @if($product)<div class="msg-conv-prod">🏷️ {{ Str::limit($product->name, 28) }}</div>@endif
                <div class="msg-conv-preview">{{ Str::limit($lastMsg->body, 42) }}</div>
            </div>
            <div class="msg-conv-meta">
                <span class="msg-conv-time">{{ $lastMsg->created_at->diffForHumans(null, true) }}</span>
                @if($unreadCnt > 0)<span class="msg-conv-unread">{{ $unreadCnt }}</span>@endif
            </div>
        </div>
        @empty
        <div class="msg-conv-empty">
            <span class="msg-conv-empty-ico">💬</span>
            <div class="msg-conv-empty-txt">Aucune conversation pour l'instant.<br>Posez une question depuis une boutique !</div>
        </div>
        @endforelse
    </div>
</div>

{{-- ══ MODAL DISCUSSION ══ --}}
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
                @csrf
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

{{-- ══ NAVBAR ══ --}}
<nav class="nav">
    <a href="{{ route('client.dashboard') }}" class="nav-logo">
        <span>Ma</span><span>Boutique</span>
    </a>

    <div class="nav-links">
        <a href="{{ route('client.dashboard') }}" class="nav-link active">🏠 Accueil</a>
        <a href="#boutiques" class="nav-link">🏪 Boutiques</a>
        <a href="#categories" class="nav-link">📦 Catégories</a>
    </div>

    <div class="nav-search">
        <input type="text" id="globalSearch" placeholder="Que recherchez-vous ?">
        <button class="nav-search-btn" onclick="doSearch()">🔍</button>
    </div>

    <div class="nav-actions">
        {{-- Bouton messages --}}
        <button class="nav-msg-btn" onclick="openMsgDrawer()" title="Mes messages">
            💬
            <span class="nav-msg-badge {{ $myUnread > 0 ? 'show' : '' }}" id="navMsgBadge">
                {{ $myUnread > 0 ? $myUnread : '' }}
            </span>
        </button>

        <a href="{{ route('client.orders.index') }}" class="nav-orders-btn">
            📦 <span>Mes commandes</span>
        </a>
        <div class="nav-av-wrap">
            <div class="nav-av" id="navAvatar" onclick="toggleAvatarMenu()">{{ $initials }}</div>
            <div class="nav-av-menu" id="avatarMenu">
                <div style="padding:10px 12px 8px;border-bottom:1px solid var(--border);margin-bottom:4px">
                    <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $user->name }}</div>
                    <div style="font-size:11px;color:var(--muted)">{{ $user->email }}</div>
                </div>
                <a href="{{ route('profile.edit') }}">👤 Mon profil</a>
                <a href="{{ route('client.orders.index') }}">📦 Mes commandes</a>
                <div class="sep"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout">⎋ Se déconnecter</button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- ══ HERO BANNER ══ --}}
<div class="hero">
    <div class="hero-text">
        <h1 class="hero-title">
            Commandez en Ligne<br>
            <span style="font-size:.65em;font-weight:700;color:rgba(255,255,255,.7)">
                Faites vos achats en quelques clics !
            </span>
        </h1>
        <div class="hero-btns">
            <a href="#boutiques" class="hero-btn-primary">🏪 Voir les Boutiques</a>
            <a href="{{ route('client.orders.index') }}" class="hero-btn-secondary">📦 Mes Commandes</a>
        </div>
    </div>
    <div class="hero-right">
        <div class="hero-icon-box">🛒</div>
        <div class="hero-icon-box">🚚</div>
        <div class="hero-icon-box">🛍️</div>
    </div>
</div>

{{-- ══ ÉTAPES ══ --}}
<div class="steps">
    <div class="step">
        <div class="step-ico">🛍️</div>
        <div class="step-text">
            <div class="step-title">Choisissez une Boutique</div>
            <div class="step-sub">Parcourez nos boutiques</div>
        </div>
    </div>
    <div class="step-arrow">›</div>
    <div class="step">
        <div class="step-ico">🏷️</div>
        <div class="step-text">
            <div class="step-title">Sélectionnez un Produit</div>
            <div class="step-sub">Choisissez vos articles</div>
        </div>
    </div>
    <div class="step-arrow">›</div>
    <div class="step">
        <div class="step-ico">📋</div>
        <div class="step-text">
            <div class="step-title">Validez votre Commande</div>
            <div class="step-sub">Confirmez vos informations</div>
        </div>
    </div>
    <div class="step-arrow">›</div>
    <div class="step">
        <div class="step-ico">🚚</div>
        <div class="step-text">
            <div class="step-title">Recevez votre Livraison</div>
            <div class="step-sub">Livraison rapide chez vous</div>
        </div>
    </div>
</div>

{{-- ══ MAIN ══ --}}
<div class="c-main">

    @foreach(['success','danger'] as $t)
        @if(session($t))<div class="c-flash c-flash-{{ $t }}"><span>{{ $t === 'success' ? '✓' : '✕' }}</span>{{ session($t) }}</div>@endif
    @endforeach

    {{-- Commandes récentes --}}
    @if(isset($recentOrders) && $recentOrders->isNotEmpty())
    <div style="margin-bottom:28px">
        <div class="sec-hd">
            <div class="sec-title"><strong>Mes</strong> Commandes Récentes</div>
            <a href="{{ route('client.orders.index') }}" class="sec-link">Voir tout →</a>
        </div>
        <div class="orders-card">
            <div class="orders-card-hd">
                <span class="orders-card-title">Historique</span>
                <span style="font-size:11px;color:var(--muted)">{{ $recentOrders->count() }} commande(s)</span>
            </div>
            @foreach($recentOrders as $order)
            @php
                $st   = $statusMap[$order->status] ?? ['pill-pending', ucfirst($order->status)];
                $oIco = match($order->status) {
                    'livrée'                     => ['🎉', 'background:#d1fae5'],
                    'en_livraison','en livraison' => ['🚴', 'background:#dbeafe'],
                    'annulée','cancelled'          => ['✕',  'background:#fee2e2'],
                    default                       => ['📦', 'background:#fef3c7'],
                };
            @endphp
            <a href="{{ route('client.orders.index') }}" class="order-row">
                <div class="order-ico" style="{{ $oIco[1] }}">{{ $oIco[0] }}</div>
                <div class="order-info">
                    <div class="order-ref">#{{ $order->id }}</div>
                    <div class="order-shop">{{ $order->shop?->name ?? 'Boutique' }}</div>
                </div>
                <span class="order-pill {{ $st[0] }}">{{ $st[1] }}</span>
                <div class="order-amount">{{ number_format($order->total, 0, ',', ' ') }} <span style="font-size:10px;font-weight:400;color:var(--muted)">GNF</span></div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Catégories — affiche seulement les types qui ont des boutiques --}}
    <div id="categories" style="scroll-margin-top:80px">
        <div class="cats" id="catFilter">
            {{-- Toutes --}}
            <a href="#boutiques"
               class="cat-pill {{ $activeType === '' ? 'active' : '' }}"
               onclick="filterByType('', this); return false;">
                🏪 Toutes
            </a>
            @php
                /* types distincts des boutiques approuvées */
                $existingTypes = \App\Models\Shop::where('is_approved', true)
                    ->whereNotNull('type')->distinct()->pluck('type')->toArray();
                $catDefs = [
                    'Alimentation'=>'🍽️','Restaurant'=>'🍽️','Épicerie'=>'🛒','Boulangerie'=>'🥖',
                    'Vêtements'=>'👗','Bijouterie'=>'💎','Mode'=>'👗',
                    'Électronique'=>'📱','Informatique'=>'💻','Téléphonie'=>'📞',
                    'Beauté & Cosmétiques'=>'💄','Pharmacie'=>'💊','Parfumerie'=>'🌸',
                ];
                /* on n'affiche que les types qui existent en base */
                $typesToShow = array_filter($existingTypes, fn($t) => isset($catDefs[$t]));
                /* dédoublonnage et tri */
                sort($typesToShow);
            @endphp
            @foreach($typesToShow as $t)
            <a href="#boutiques"
               class="cat-pill {{ $activeType === $t ? 'active' : '' }}"
               onclick="filterByType('{{ $t }}', this); return false;">
                {{ $catDefs[$t] ?? '🏪' }} {{ $t }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- Boutiques --}}
    <div id="boutiques" style="scroll-margin-top:80px">
        <div class="sec-hd">
            <div class="sec-title">
                <strong>Boutiques</strong> Populaires
                <span style="font-size:14px;font-weight:500;color:var(--muted);font-family:var(--font)">
                    (<span id="shopCount">{{ $shops->total() }}</span>)
                </span>
            </div>
        </div>

        <div class="shops-grid" id="shopsGrid">
            @forelse($shops as $shop)
            @php
                [$ico, $bgClass] = $typeIco[$shop->type ?? ''] ?? ['🛍️', 'bg-default'];
                $isNew = $shop->created_at->diffInDays(now()) <= 7;
            @endphp
            <a href="{{ route('client.shops.show', $shop) }}"
               class="shop-card"
               data-name="{{ strtolower($shop->name) }}"
               data-type="{{ strtolower($shop->type ?? '') }}">

                <div class="shop-card-img">
                    @if($shop->image)
                        <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}">
                    @else
                        <div class="shop-card-placeholder {{ $bgClass }}">{{ $ico }}</div>
                    @endif
                    @if($isNew)
                        <span class="shop-card-badge badge-new">✨ Nouveau</span>
                    @else
                        <span class="shop-card-badge badge-open">Ouvert</span>
                    @endif
                </div>

                <div class="shop-card-body">
                    @if($shop->type)<div class="shop-card-type">{{ $shop->type }}</div>@endif
                    <div class="shop-card-name">{{ $shop->name }}</div>
                    @if($shop->description)<p class="shop-card-desc">{{ $shop->description }}</p>@endif
                    <div class="shop-card-meta">
                        @if($shop->address ?? false)
                        <span class="shop-card-chip">📍 {{ Str::limit($shop->address, 18) }}</span>
                        @endif
                        @if(($shop->products_count ?? 0) > 0)
                        <span class="shop-card-chip">🏷️ {{ $shop->products_count }} produit{{ $shop->products_count > 1 ? 's' : '' }}</span>
                        @endif
                    </div>
                </div>

                <div class="shop-card-footer">
                    <div class="shop-card-rating">
                        ⭐ {{ $shop->avg_rating ? number_format($shop->avg_rating, 1) : '—' }}
                        <small>({{ $shop->reviews_count ?? 0 }} avis)</small>
                    </div>
                    <span class="shop-card-cta">Commander →</span>
                </div>
            </a>
            @empty
            <div class="c-empty">
                <span class="c-empty-ico">🏪</span>
                <div class="c-empty-title">Aucune boutique disponible</div>
                <p class="c-empty-sub">Revenez bientôt.</p>
            </div>
            @endforelse
        </div>

        <div class="c-pagination">{{ $shops->links() }}</div>
    </div>

</div>

@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════
   AVATAR MENU
══════════════════════════════════════════ */
function toggleAvatarMenu() {
    document.getElementById('avatarMenu').classList.toggle('open');
}
document.addEventListener('click', e => {
    const av  = document.getElementById('navAvatar');
    const men = document.getElementById('avatarMenu');
    if (av && !av.contains(e.target) && men && !men.contains(e.target)) {
        men.classList.remove('open');
    }
});

/* ══════════════════════════════════════════
   RECHERCHE BOUTIQUES (JS côté client)
══════════════════════════════════════════ */
function doSearch() {
    const q = document.getElementById('globalSearch').value.toLowerCase().trim();
    let count = 0;
    document.querySelectorAll('.shop-card').forEach(card => {
        const match = !q || card.dataset.name.includes(q) || card.dataset.type.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) count++;
    });
    const sc = document.getElementById('shopCount');
    if (sc) sc.textContent = count;
}
document.getElementById('globalSearch')?.addEventListener('input', doSearch);
document.getElementById('globalSearch')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') doSearch();
});

/* ══════════════════════════════════════════
   FILTRE CATÉGORIES — SANS SCROLL EN HAUT
══════════════════════════════════════════ */
function filterByType(type, pillEl) {
    /* 1. Mise à jour pills actives */
    document.querySelectorAll('#catFilter .cat-pill').forEach(p => p.classList.remove('active'));
    pillEl.classList.add('active');

    /* 2. Filtre visuel des cartes */
    let count = 0;
    document.querySelectorAll('#shopsGrid .shop-card').forEach(card => {
        const cardType = card.dataset.type || '';
        const match = !type || cardType.includes(type.toLowerCase());
        card.style.display = match ? '' : 'none';
        if (match) count++;
    });
    const sc = document.getElementById('shopCount');
    if (sc) sc.textContent = count;

    /* 3. Si besoin AJAX pour charger d'autres pages (optionnel — désactivé ici) */
    /* 4. Ne pas remonter en haut — pas de changement d'URL */
}

/* ══════════════════════════════════════════
   DRAWER MESSAGES
══════════════════════════════════════════ */
function openMsgDrawer() {
    document.getElementById('msgDrawer').classList.add('open');
    document.getElementById('msgOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeMsgDrawer() {
    document.getElementById('msgDrawer').classList.remove('open');
    document.getElementById('msgOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

/* ══════════════════════════════════════════
   MODAL DISCUSSION
══════════════════════════════════════════ */
let _currentProductId = null;
let _currentConvKey    = null;
let _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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
    document.getElementById('mmProd').textContent = conv.prodName ? '🏷️ ' + conv.prodName : '';
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
        thread.innerHTML = '<div style="text-align:center;padding:40px 20px;color:var(--muted);font-size:13px">Aucun message. Écrivez le premier ! 💬</div>';
    }

    /* Ouvrir le modal */
    document.getElementById('mmInput').value = '';
    document.getElementById('mmInput').style.height = '';
    document.getElementById('msgModalOverlay').classList.add('open');

    /* Marquer les messages comme lus dès l'ouverture */
    if (conv.productId) {
        markMessagesRead(conv.productId, conv.key);
    }
}

function closeMsgModal() {
    document.getElementById('msgModalOverlay').classList.remove('open');
    _currentProductId = null;
    _currentConvKey    = null;
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
        await fetch(`/client/products/${productId}/messages`, {
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
    /* Compte les items qui ont encore des non-lus */
    const remaining = document.querySelectorAll('.msg-conv-item.has-unread').length;
    const badge = document.getElementById('navMsgBadge');
    const drawerBadge = document.querySelector('.msg-drawer-badge');

    if (remaining > 0) {
        badge.textContent = remaining;
        badge.classList.add('show');
    } else {
        badge.textContent = '';
        badge.classList.remove('show');
    }

    if (drawerBadge) {
        if (remaining > 0) {
            drawerBadge.textContent = remaining + ' non lu' + (remaining > 1 ? 's' : '');
            drawerBadge.style.display = '';
        } else {
            drawerBadge.style.display = 'none';
        }
    }
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
            /* Ajouter la bulle directement dans le thread */
            const now = new Date();
            const timeStr = String(now.getDate()).padStart(2,'0') + '/'
                          + String(now.getMonth()+1).padStart(2,'0') + ' '
                          + String(now.getHours()).padStart(2,'0') + ':'
                          + String(now.getMinutes()).padStart(2,'0');

            const bubble = buildBubble({
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

/* ══════════════════════════════════════════
   ANIMATIONS ENTRÉE BOUTIQUES
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.shop-card').forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(18px)';
        setTimeout(() => {
            card.style.transition = 'opacity .35s ease, transform .35s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 50 + i * 35);
    });
});
</script>
@endpush