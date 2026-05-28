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
.order-info { flex: 1; min-width: 0; overflow: hidden; }
.order-ref  { font-size: 12.5px; font-weight: 700; color: var(--text); font-family: monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.order-shop { font-size: 11.5px; color: var(--muted); margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.order-amount { font-size: 13.5px; font-weight: 700; color: var(--text); font-family: monospace; white-space: nowrap; flex-shrink: 0; }
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

/* ══ SYSTÈME ICÔNES SVG ══ */
.si { display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
.si svg { display:block; }
.nav-link .si { vertical-align:-.15em; }

/* ══ PAGE WRAP (sidebar + main) ══ */
.page-wrap {
    display: flex; gap: 22px; align-items: flex-start;
    max-width: 1440px; margin: 0 auto;
    padding: 20px 24px 60px;
}

/* ══ SIDEBAR ══ */
.sidebar {
    width: 260px; flex-shrink: 0;
    display: flex; flex-direction: column; gap: 14px;
    position: sticky; top: calc(var(--nav-h) + 14px);
    max-height: calc(100vh - var(--nav-h) - 28px);
    overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--border) transparent;
}
.sidebar::-webkit-scrollbar { width: 4px; }
.sidebar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.sidebar::-webkit-scrollbar-track { background: transparent; }

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
    color: var(--orange);
    text-shadow: 0 0 24px rgba(240,106,15,.45);
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
.stat-ico.s2 { background: #fff7ed; color: var(--orange); }
.stat-ico.s3 { background: #f0fdf4; color: #22c55e; }
.stat-ico.s4 { background: #fdf4ff; color: #a855f7; }
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
.pop-cat-chip:hover { border-color: var(--orange); box-shadow: 0 4px 14px rgba(240,106,15,.18); transform: translateY(-2px); }
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
    font-family: monospace; border: 1.5px solid var(--surface);
    display: none;
}
.nav-fav-badge.show { display: block; }

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
    .sidebar {
        width: 100%;
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
    .shops-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }
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

    /* ── Sidebar : on cache les cartes inutiles, on garde 🏆 top boutiques ── */
    body.is-dashboard .sidebar { display: flex !important; flex-direction: column !important; order: 2 !important; width: 100% !important; gap: 0 !important; position: static !important; max-height: none !important; overflow-y: visible !important; }
    body.is-dashboard .sb-card-explorer { display: none !important; }
    body.is-dashboard .sidebar > .sb-card:not(.sb-card-topshops):not(.sb-card-explorer) { display: none !important; }
    body.is-dashboard .sb-card-topshops { display: block !important; border-radius: 10px !important; overflow: hidden !important; }

    /* ── Hero — phone.png visible avec cover ── */
    body.is-dashboard .hero {
        height: 200px !important;
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
        align-items: center !important;
        padding: 0 !important;
    }
    body.is-dashboard .hero-content { padding: 16px 14px !important; max-width: 62% !important; z-index: 2 !important; position: relative !important; }
    body.is-dashboard .hero-welcome { font-size: 9px !important; padding: 2px 7px !important; margin-bottom: 5px !important; }
    body.is-dashboard .hero-title { font-size: 18px !important; letter-spacing: -.3px !important; margin-bottom: 5px !important; line-height: 1.2 !important; }
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

    /* ── Grille boutiques 2 colonnes ── */
    body.is-dashboard .shops-grid { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 8px !important; }
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
    body.is-dashboard .hero { height: 185px !important; }
    body.is-dashboard .hero-content { max-width: 58% !important; padding: 13px 12px !important; }
    body.is-dashboard .hero-title { font-size: 16px !important; }
    body.is-dashboard .hero-subtitle { font-size: 10px !important; }
    body.is-dashboard .stat-val { font-size: 15px !important; }
    body.is-dashboard .stat-sub { font-size: 8px !important; }
    body.is-dashboard .top-shop-card { width: 150px !important; min-width: 150px !important; max-width: 150px !important; }
    body.is-dashboard .top-shop-img { height: 90px !important; }
    body.is-dashboard .shops-grid { gap: 6px !important; }
    body.is-dashboard .shop-card-img { height: 95px !important; }
    body.is-dashboard .page-wrap { padding-bottom: 70px !important; }
}

/* ── 360px ── */
@media (max-width: 360px) {
    body.is-dashboard .nav { padding: 0 8px !important; }
    body.is-dashboard .nav-search { display: none !important; }
    body.is-dashboard .page-wrap { padding: 8px 8px 74px !important; }
    body.is-dashboard .hero { height: 168px !important; }
    body.is-dashboard .hero-content { max-width: 60% !important; padding: 11px 10px !important; }
    body.is-dashboard .hero-title { font-size: 15px !important; }
    body.is-dashboard .top-shop-card { width: 135px !important; min-width: 135px !important; max-width: 135px !important; }
    body.is-dashboard .top-shop-img { height: 80px !important; }
    body.is-dashboard .shops-grid { gap: 5px !important; }
    body.is-dashboard .shop-card-img { height: 95px !important; }
    body.is-dashboard .shop-card-footer { flex-direction: column !important; gap: 4px !important; align-items: stretch !important; }
    body.is-dashboard .shop-card-cta { text-align: center !important; justify-content: center !important; width: 100% !important; }
    body.is-dashboard .order-ico { width: 30px !important; height: 30px !important; font-size: 12px !important; }
    body.is-dashboard .order-pill { display: none !important; }
    body.is-dashboard .order-amount { font-size: 11px !important; }
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
    font-family: monospace; border: 1.5px solid var(--surface);
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
.sidebar, .cn-drop-list {
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
    color: var(--orange); background: rgba(240,106,15,.08);
    border: 1px solid rgba(240,106,15,.25);
    padding: 2px 8px; border-radius: 20px;
    margin-top: 4px; width: fit-content;
}
.shop-card.prod-match .shop-prod-match { display: inline-flex; }
.shop-name-clamp mark {
    background: rgba(240,106,15,.18); color: var(--orange-dk);
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
    font-family: monospace; border: 1.5px solid var(--surface); display: none;
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
.cn-notif-ico.msg   { background: linear-gradient(135deg, #818cf8, #6366f1); }
.cn-notif-ico.c-ok  { background: linear-gradient(135deg, #22c55e, #16a34a); }
.cn-notif-ico.c-del { background: linear-gradient(135deg, #f59e0b, #d97706); }
.cn-notif-ico.c-done{ background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.cn-notif-body { flex: 1; min-width: 0; }
.cn-notif-name { font-size: 12px; font-weight: 700; color: var(--text); }
.cn-notif-txt { font-size: 11.5px; color: #64748b; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin-top: 2px; }
.cn-notif-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 4px; }
.cn-notif-time { font-size: 10px; color: #94a3b8; }
.cn-notif-badge { font-size: 9.5px; font-weight: 800; padding: 2px 7px; border-radius: 20px; }
.cn-notif-badge.badge-msg  { background: #ede9fe; color: #5b21b6; }
.cn-notif-badge.badge-ok   { background: #d1fae5; color: #065f46; }
.cn-notif-badge.badge-del  { background: #fef3c7; color: #92400e; }
.cn-notif-badge.badge-done { background: #ede9fe; color: #5b21b6; }
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
@endpush

@section('content')

@php
/* ════════════════════════════════════
   LIBRAIRIE ICÔNES SVG PREMIUM (Lucide)
   ════════════════════════════════════ */
$_p = [
  // UI
  'x'          => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
  'check'      => '<polyline points="20 6 9 17 4 12"/>',
  'arrow-l'    => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
  'send'       => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
  'search'     => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
  'chevron-r'  => '<polyline points="9 18 15 12 9 6"/>',
  // Navigation
  'home'       => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
  'store'      => '<path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path d="M3 9l2-6h14l2 6"/><line x1="12" y1="9" x2="12" y2="21"/>',
  'grid'       => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
  'package'    => '<line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
  'chat'       => '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
  'user'       => '<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
  'users'      => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
  'logout'     => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
  // Commerce
  'truck'      => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
  'bag'        => '<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>',
  'tag'        => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
  'box'        => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
  'party'      => '<path d="M5.8 11.3L2 22l10.7-3.79"/><path d="M22 2l-7.64 19.64a.5.5 0 01-.91.01L11 13 2.36 9.55a.5.5 0 01.01-.91z"/>',
  // Hero badges
  'shield-ok'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
  'lock'       => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>',
  'headset'    => '<path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3z"/><path d="M3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>',
  // Cartes boutique
  'pin'        => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>',
  'star-o'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
  'heart'      => '<path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>',
  'verified'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
  'trending'   => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
  // Catégories
  'utensils'   => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 002-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 00-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>',
  'cart'       => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.97-1.67l1.63-9.33H6"/>',
  'bread'      => '<path d="M6 20h12"/><path d="M3.5 10h17a1 1 0 01.95 1.316l-1.5 5A2 2 0 0118 18H6a2 2 0 01-1.95-1.684l-1.5-5A1 1 0 013.5 10z"/><path d="M8 10V8a4 4 0 118 0v2"/>',
  'shirt'      => '<path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.57a1 1 0 00.99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 002-2V10h2.15a1 1 0 00.99-.84l.58-3.57a2 2 0 00-1.34-2.23z"/>',
  'gem'        => '<polygon points="6 3 18 3 22 9 12 22 2 9 6 3"/><line x1="2" y1="9" x2="22" y2="9"/><polyline points="12 3 6 9 12 22 18 9 12 3"/>',
  'smartphone' => '<rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
  'monitor'    => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
  'phone-call' => '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.67a19.79 19.79 0 01-3.07-8.62A2 2 0 012 .01h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92v2z"/>',
  'sparkles'   => '<path d="M12 3l1.68 5.17L19 9l-3.85 3.75.91 5.32L12 15.27 7.94 18.07l.91-5.32L5 9l5.32-.83z"/>',
  'medical'    => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
  'flower'     => '<circle cx="12" cy="12" r="3"/><circle cx="12" cy="5" r="2"/><circle cx="17.6" cy="8.4" r="2"/><circle cx="17.6" cy="15.6" r="2"/><circle cx="12" cy="19" r="2"/><circle cx="6.4" cy="15.6" r="2"/><circle cx="6.4" cy="8.4" r="2"/>',
  'car'        => '<path d="M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v9a2 2 0 01-2 2h-2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
  'activity'   => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
  'book'       => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>',
  'music'      => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
  'leaf'       => '<path d="M11 20A7 7 0 014 13c0-5 3.5-9.3 8-11 4.5 1.7 8 6 8 11a7 7 0 01-9 7z"/><path d="M11 20c0-5.5 2.5-10 6-13"/>',
  'wheat'      => '<path d="M2 22L16 8"/><path d="M3.5 12.5l8-8"/><path d="M7 9a4.5 4.5 0 006.5-6.5"/><path d="M10.5 18.5l5-5"/><path d="M13 16a4.5 4.5 0 006.5-6.5"/>',
  'health'     => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
  'wrench'     => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
  'shoe'       => '<path d="M2 18l5.5-11 3.5 4 3-3.5L21 18H2z"/><line x1="2" y1="18" x2="22" y2="18"/>',
  'handbag'    => '<path d="M16 11V7a4 4 0 00-8 0v4"/><rect x="3" y="7" width="18" height="13" rx="2" ry="2"/>',
  'globe'      => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>',
];
// Icône outline
$si = function(string $k, int $sz=18) use ($_p): string {
    return '<svg class="si" width="'.$sz.'" height="'.$sz.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'.($_p[$k]??'').'</svg>';
};
// Icône remplie (étoile, etc.)
$sif = function(string $k, int $sz=18) use ($_p): string {
    return '<svg class="si" width="'.$sz.'" height="'.$sz.'" viewBox="0 0 24 24" fill="currentColor" stroke="none">'.($_p[$k]??'').'</svg>';
};
@endphp

@php
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
@endphp

@php
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
        'Alimentation' => ['utensils','bg-food'],  'Restaurant'  => ['utensils','bg-food'],
        'Épicerie'     => ['cart','bg-food'],       'Boulangerie' => ['bread','bg-food'],
        'Vêtements'    => ['shirt','bg-fashion'],   'Bijouterie'  => ['gem','bg-fashion'],
        'Électronique' => ['smartphone','bg-tech'], 'Informatique'=> ['monitor','bg-tech'],
        'Téléphonie'   => ['phone-call','bg-tech'], 'Beauté & Cosmétiques' => ['sparkles','bg-beauty'],
        'Pharmacie'    => ['medical','bg-beauty'],  'Parfumerie'  => ['flower','bg-beauty'],
    ];

    // Catégories à afficher seulement si des boutiques de ce type existent
    $allTypes = ['Alimentation','Restaurant','Épicerie','Boulangerie','Vêtements','Bijouterie',
                 'Électronique','Informatique','Téléphonie','Beauté & Cosmétiques','Pharmacie','Parfumerie'];
    $activeType = request('type', '');
    $favCount   = count($favoriteIds ?? []);
@endphp

{{-- ══ DRAWER MESSAGES ══ --}}
<div class="msg-overlay" id="msgOverlay" onclick="closeMsgDrawer()"></div>
<div class="msg-drawer" id="msgDrawer">
    <div class="msg-drawer-hd">
        <span class="msg-drawer-title">{!! $si('chat',18) !!} Mes Messages</span>
        @if($myUnread > 0)
        <span class="msg-drawer-badge">{{ $myUnread }} non lu{{ $myUnread > 1 ? 's' : '' }}</span>
        @endif
        <button class="msg-drawer-close" onclick="closeMsgDrawer()">{!! $si('x',14) !!}</button>
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
                    <img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') }}"
                         alt="{{ $vName }}" loading="lazy" decoding="async" width="42" height="42">
                @else
                    {{ $vInit }}
                @endif
                @if($unreadCnt > 0)<span class="msg-conv-av-dot"></span>@endif
            </div>
            <div class="msg-conv-info">
                <div class="msg-conv-name">{{ $vName }}</div>
                @if($product)<div class="msg-conv-prod">{!! $si('tag',11) !!} {{ Str::limit($product->name, 28) }}</div>@endif
                <div class="msg-conv-preview">{{ Str::limit($lastMsg->body, 42) }}</div>
            </div>
            <div class="msg-conv-meta">
                <span class="msg-conv-time">{{ $lastMsg->created_at->diffForHumans(null, true) }}</span>
                @if($unreadCnt > 0)<span class="msg-conv-unread">{{ $unreadCnt }}</span>@endif
            </div>
        </div>
        @empty
        <div class="msg-conv-empty">
            <span class="msg-conv-empty-ico">{!! $si('chat',40) !!}</span>
            <div class="msg-conv-empty-txt">Aucune conversation pour l'instant.<br>Posez une question depuis une boutique !</div>
        </div>
        @endforelse
    </div>
</div>

{{-- ══ DRAWER FAVORIS ══ --}}
<div class="fav-overlay" id="favOverlay" onclick="closeFavDrawer()"></div>
<div class="fav-drawer" id="favDrawer">
    <div class="fav-drawer-hd">
        <div class="fav-drawer-ico">{!! $si('heart',20) !!}</div>
        <div>
            <div class="fav-drawer-title">Mes Favoris</div>
            <div class="fav-drawer-sub" id="favDrawerSub">{{ $favCount }} boutique{{ $favCount !== 1 ? 's' : '' }} sauvegardée{{ $favCount !== 1 ? 's' : '' }}</div>
        </div>
        <button class="fav-drawer-close" onclick="closeFavDrawer()">{!! $si('x',14) !!}</button>
    </div>
    <div class="fav-list" id="favList">
        <div class="fav-empty" id="favEmptyState" style="{{ $favCount > 0 ? 'display:none' : '' }}">
            <div class="fav-empty-ico">{!! $si('heart',32) !!}</div>
            <div class="fav-empty-title">Aucun favori</div>
            <div class="fav-empty-sub">Cliquez sur le cœur d'une boutique<br>pour la sauvegarder ici.</div>
        </div>
        <div id="favShopsContainer">
            {{-- Rempli dynamiquement via JS --}}
        </div>
    </div>
    <div class="fav-drawer-footer" id="favDrawerFooter" style="{{ $favCount === 0 ? 'display:none' : '' }}">
        <a href="#boutiques" onclick="closeFavDrawer()" class="fav-visit-all-btn">
            {!! $si('store',16) !!} Explorer toutes les boutiques
        </a>
    </div>
</div>

{{-- ══ MODAL DISCUSSION ══ --}}
<div class="msg-modal-overlay" id="msgModalOverlay">
    <div class="msg-modal" id="msgModal">
        <div class="msg-modal-hd">
            <button class="msg-modal-back" onclick="closeMsgModal()" title="Retour">{!! $si('arrow-l',15) !!}</button>
            <div class="msg-modal-av" id="mmAv">??</div>
            <div class="msg-modal-info">
                <div class="msg-modal-name" id="mmName">Vendeur</div>
                <div class="msg-modal-prod" id="mmProd"></div>
            </div>
            <button class="msg-modal-close" onclick="closeMsgModal(); closeMsgDrawer()">{!! $si('x',14) !!}</button>
        </div>
        <div class="msg-prod-bar" id="mmProdBar" style="display:none">
            <div class="msg-prod-ph" id="mmProdImgPh">{!! $si('tag',18) !!}</div>
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
                <button type="submit" class="msg-send-btn">{!! $si('send',16) !!}</button>
            </form>
        </div>
    </div>
</div>

{{-- ══ NAVBAR ══ --}}
<nav class="nav">
    <a href="{{ route('client.dashboard') }}" class="nav-logo">
         <img src="{{ asset('images/Shopio2.jpeg') }}" alt="Shopio" style="height:60px;width:auto;object-fit:contain;border-radius:8px">
       
    </a>

    <div class="nav-links">
        <a href="{{ route('client.dashboard') }}" class="nav-link active">{!! $si('home',17) !!} Accueil</a>
        <a href="#boutiques" class="nav-link">{!! $si('store',17) !!} Boutiques</a>
        <a href="#categories" class="nav-link">{!! $si('grid',17) !!} Catégories</a>
    </div>

    <div style="flex:1;max-width:420px;display:flex;flex-direction:column;gap:0">
        <div class="nav-search" style="max-width:100%">
            <input type="text" id="globalSearch" placeholder="Que recherchez-vous ?" autocomplete="off">
            <button class="nav-search-btn" onclick="doSearch()">{!! $si('search',16) !!}</button>
        </div>
        <div id="searchLiveInfo"></div>
    </div>

    <div class="nav-actions">
        {{-- ══ CLOCHE NOTIFICATIONS ══ --}}
        <div class="cn-backdrop" id="cnBackdrop"></div>
        <div class="cn-bell-wrap" id="cnBellWrap">
            <button class="cn-bell-btn" id="cnBellBtn" onclick="cnToggle()" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span class="cn-bell-badge" id="cnBadge"></span>
            </button>
            <div class="cn-dropdown" id="cnDropdown">
                <div class="cn-drop-hd">
                    <span class="cn-drop-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        Notifications
                    </span>
                    <span class="cn-drop-total" id="cnTotal">0</span>
                </div>
                <div class="cn-drop-list" id="cnList">
                    <div class="cn-drop-empty">Aucune notification</div>
                </div>
                <div class="cn-drop-ft">
                    <a href="{{ route('client.orders.index') }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        Voir mes commandes
                    </a>
                </div>
            </div>
        </div>

        {{-- Bouton favoris --}}
        <button class="nav-fav-btn" id="navFavBtn" onclick="openFavDrawer()" title="Mes boutiques favorites">
            {!! $si('heart',20) !!}
            <span class="nav-fav-badge {{ $favCount > 0 ? 'show' : '' }}" id="navFavBadge">{{ $favCount > 0 ? $favCount : '' }}</span>
        </button>

        <a href="{{ route('client.messages.hub') }}" class="nav-msg-btn" title="Mes messages" style="text-decoration:none">
            {!! $si('chat',20) !!}
            <span class="nav-msg-badge {{ $myUnread > 0 ? 'show' : '' }}" id="navMsgBadge">
                {{ $myUnread > 0 ? $myUnread : '' }}
            </span>
        </a>

        <a href="{{ route('client.orders.index') }}" class="nav-orders-btn">
            {!! $si('package',16) !!} <span>Mes commandes</span>
        </a>
        <div class="nav-av-wrap">
            <div style="position:relative;cursor:pointer" onclick="toggleAvatarMenu()">
                <div class="nav-av" id="navAvatar">{{ $initials }}</div>
                @if($countryFlag)
                <span style="position:absolute;bottom:-4px;right:-6px;font-size:14px;line-height:1;background:var(--surface);border-radius:50%;padding:1px;box-shadow:0 0 0 1.5px var(--border)">{{ $countryFlag }}</span>
                @endif
            </div>
            <div class="nav-av-menu" id="avatarMenu">
                <div style="padding:12px 14px 10px;border-bottom:1px solid var(--border);margin-bottom:4px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--orange),var(--orange-dk));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0">{{ $initials }}</div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $user->name }}</div>
                            <div style="font-size:11px;color:var(--muted)">{{ $user->email }}</div>
                        </div>
                    </div>
                    @if($countryFlag)
                    <div style="display:inline-flex;align-items:center;gap:5px;background:var(--grey);border:1px solid var(--border);border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;color:var(--text-2);margin-top:4px">
                        {{ $countryFlag }} {{ $countryName }}
                    </div>
                    @endif
                </div>
                <a href="#" onclick="openProfileModal();return false;">{!! $si('user',15) !!} Modifier mon profil</a>
                <a href="{{ route('client.orders.index') }}">{!! $si('package',15) !!} Mes commandes</a>
                <div class="sep"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout">{!! $si('logout',15) !!} Se déconnecter</button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- ══ PAGE WRAP ══ --}}
<div class="page-wrap">

{{-- ══ SIDEBAR ══ --}}
<aside class="sidebar">

    {{-- Explorer catégories --}}
    @if(isset($categories) && $categories->isNotEmpty())
    <div class="sb-card sb-card-explorer">
        <div class="sb-hd">{!! $si('grid',15) !!} Explorer</div>
        <div class="sb-cat-wrap-mob">
        <button class="sb-cat-item active" data-cat-type="" onclick="filterByCat('')">
            <span class="sb-cat-ico">{!! $si('store',17) !!}</span>
            <span class="sb-cat-name">Toutes</span>
            <span class="sb-cat-cnt">{{ $shopCount ?? $shops->total() }}</span>
        </button>
        @php
            $sbIcoMap = [
                'alimentation'=>'utensils','restaurant'=>'utensils','épicerie'=>'cart','epicerie'=>'cart',
                'boulangerie'=>'bread','pâtisserie'=>'bread','patisserie'=>'bread',
                'vêtements'=>'shirt','vetements'=>'shirt','mode'=>'shirt',
                'bijouterie'=>'gem','bijoux'=>'gem',
                'électronique'=>'smartphone','electronique'=>'smartphone','informatique'=>'monitor',
                'téléphonie'=>'phone-call','telephonie'=>'phone-call',
                'beauté & cosmétiques'=>'sparkles','beaute & cosmetiques'=>'sparkles',
                'beauté'=>'sparkles','beaute'=>'sparkles','cosmétiques'=>'sparkles','cosmetiques'=>'sparkles',
                'pharmacie'=>'medical','parfumerie'=>'flower',
                'auto & moto'=>'car','automobile'=>'car',
                'sport'=>'activity','maison'=>'home','décoration'=>'home','decoration'=>'home',
                'librairie'=>'book','musique'=>'music','jardin'=>'leaf',
                'agriculture'=>'wheat','santé'=>'health','sante'=>'health',
                'construction'=>'wrench','quincaillerie'=>'wrench',
                'supermarché'=>'cart','supermarche'=>'cart',
                'chaussures'=>'shoe','accessoires'=>'handbag','sacs'=>'handbag',
            ];
            $getSbEmoji = function(string $t) use ($sbIcoMap, $si): string {
                $k = mb_strtolower(trim($t));
                if (isset($sbIcoMap[$k])) return $si($sbIcoMap[$k], 17);
                foreach ($sbIcoMap as $key => $icoKey) { if (str_contains($k, $key)) return $si($icoKey, 17); }
                return $si('store', 17);
            };
        @endphp
        @foreach($categories as $cat)
        <button class="sb-cat-item" data-cat-type="{{ $cat->type }}" onclick="filterByCat(this.dataset.catType)">
            <span class="sb-cat-ico">{!! $getSbEmoji($cat->type) !!}</span>
            <span class="sb-cat-name">{{ $cat->type }}</span>
            <span class="sb-cat-cnt">{{ $cat->shop_count }}</span>
        </button>
        @endforeach
        </div>
    </div>
    @endif

    {{-- Localisation --}}
    @if($countryName)
    <div class="sb-card" style="padding:12px 14px">
        <div style="font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px">Où souhaitez-vous être livré ?</div>
        <div style="display:flex;align-items:center;justify-content:space-between;background:var(--grey);border:1px solid var(--border);border-radius:8px;padding:8px 12px">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:18px">{{ $countryFlag ?: '📍' }}</span>
                <span style="font-size:12.5px;font-weight:700;color:var(--text)">{{ $countryName }}</span>
            </div>
            <span style="color:var(--muted);font-size:13px">→</span>
        </div>
    </div>
    @endif

   

    {{-- Top boutiques --}}
    @if(isset($topShops) && $topShops->isNotEmpty())
    <div class="sb-card sb-card-topshops">
        <div class="sb-hd" style="justify-content:space-between">
            <span>🏆 Meilleures boutiques</span>
            <button onclick="openRankingModal()" style="background:var(--orange);color:#fff;border:none;border-radius:20px;padding:3px 10px;font-size:10px;font-weight:700;cursor:pointer;font-family:var(--font);flex-shrink:0">Classement →</button>
        </div>
        @foreach($topShops as $i => $ts)
        @php
            $sbParts = explode(' ', $ts->name);
            $sbInit  = strtoupper(substr($sbParts[0],0,1)) . strtoupper(substr($sbParts[1] ?? 'X',0,1));
            $sbGrads = [
                'linear-gradient(135deg,#667eea,#764ba2)',
                'linear-gradient(135deg,#f5576c,#f093fb)',
                'linear-gradient(135deg,#4facfe,#00c6fb)',
                'linear-gradient(135deg,#cc2b5e,#753a88)',
                'linear-gradient(135deg,#ee0979,#ff6a00)',
                'linear-gradient(135deg,#24c6dc,#514a9d)',
                'linear-gradient(135deg,#11998e,#38ef7d)',
                'linear-gradient(135deg,#f59e0b,#f97316)',
            ];
            $sbGrad = $sbGrads[abs(crc32($ts->name)) % count($sbGrads)];
        @endphp
        <a href="{{ route('client.shops.show', $ts) }}" class="sb-top-shop" data-shop-id="{{ $ts->id }}">
            <span class="sb-top-num">{{ $i + 1 }}</span>
            <div class="sb-top-av" style="background:{{ $sbGrad }}">{{ $sbInit }}</div>
            <div class="sb-top-info">
                <div class="sb-top-name">{{ $ts->name }}</div>
                <div class="sb-top-rating">
                    ⭐ {{ $ts->avg_rating ? number_format($ts->avg_rating, 1) : '—' }}
                    <span style="color:var(--muted);font-weight:400"> · +{{ number_format($ts->sales_count ?? 0) }} ventes</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

</aside>

{{-- ══ MAIN COLUMN ══ --}}
<div class="main-col">

{{-- Hero --}}
<div class="hero">
    {{-- Image de fond téléphone (droite, sans recadrage) --}}
    {{-- Carte rating flottante en haut à droite --}}
    <div class="hero-rating-card">
        <div class="hero-rating-stars" style="display:flex;gap:2px;justify-content:center">{!! $sif('star-o',13).$sif('star-o',13).$sif('star-o',13).$sif('star-o',13).$sif('star-o',13) !!}</div>
        <div class="hero-rating-val">4.8<span style="font-size:13px;font-weight:600;color:rgba(255,255,255,.6)">/5</span></div>
        <div class="hero-rating-lbl">Note moyenne</div>
        <div class="hero-rating-reviews">+{{ number_format(($shopCount ?? 0) * 12) }} avis clients</div>
    </div>

    {{-- Contenu texte par-dessus l'image de fond --}}
    <div class="hero-content">
        <div class="hero-welcome">Bienvenue sur Shopio 👋</div>
        <h1 class="hero-title">
            Le meilleur des boutiques de<br>
            <span class="country">{{ $countryName ?: 'Guinée' }}</span>, au même endroit.
        </h1>
        <p class="hero-subtitle">
            Découvrez des boutiques vérifiées, des produits de qualité
            et une livraison rapide <strong>dans tout le pays.</strong>
        </p>
        <div class="hero-badges">
            <span class="hero-badge">
                <span class="hero-badge-ico">{!! $si('shield-ok',15) !!}</span>
                <span class="hero-badge-txt">
                    <span class="hero-badge-label">Boutiques vérifiées</span>
                    <span class="hero-badge-sub">Sélectionnées avec soin</span>
                </span>
            </span>
            <span class="hero-badge">
                <span class="hero-badge-ico">{!! $si('truck',15) !!}</span>
                <span class="hero-badge-txt">
                    <span class="hero-badge-label">Livraison rapide</span>
                    <span class="hero-badge-sub">Partout en {{ $countryName ?: 'Guinée' }}</span>
                </span>
            </span>
            <span class="hero-badge">
                <span class="hero-badge-ico">{!! $si('lock',15) !!}</span>
                <span class="hero-badge-txt">
                    <span class="hero-badge-label">Paiement sécurisé</span>
                    <span class="hero-badge-sub">100% sécurisé</span>
                </span>
            </span>
            <span class="hero-badge">
                <span class="hero-badge-ico">{!! $si('headset',15) !!}</span>
                <span class="hero-badge-txt">
                    <span class="hero-badge-label">Support 24/7</span>
                    <span class="hero-badge-sub">Toujours disponible</span>
                </span>
            </span>
        </div>
        <div class="hero-btns" style="display:flex;gap:12px;margin-top:22px;flex-wrap:wrap">
            <a href="{{ route('client.products.index') }}"
               style="display:inline-flex;align-items:center;gap:8px;padding:13px 26px;border-radius:14px;background:linear-gradient(135deg,#ff6a00,#ee0979);color:#fff;font-size:14px;font-weight:700;text-decoration:none;box-shadow:0 4px 16px rgba(238,9,121,.35);transition:.2s">
                {!! $si('tag',16) !!} Voir tous les produits
            </a>
            <a href="#boutiques"
               style="display:inline-flex;align-items:center;gap:8px;padding:13px 26px;border-radius:14px;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.25);color:#fff;font-size:14px;font-weight:700;text-decoration:none;backdrop-filter:blur(8px);transition:.2s">
                {!! $si('store',16) !!} Explorer les boutiques
            </a>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-ico s1">{!! $si('store',22) !!}</div>
        <div>
            <div class="stat-val">{{ number_format($shopCount ?? 0) }}+</div>
            <div class="stat-lbl">Boutiques actives</div>
            <div class="stat-sub">Vérifiées et fiables</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-ico s2">{!! $si('bag',22) !!}</div>
        <div>
            <div class="stat-val">{{ number_format($productCount ?? 0) }}+</div>
            <div class="stat-lbl">Produits disponibles</div>
            <div class="stat-sub">Dans toutes les catégories</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-ico s3">{!! $si('truck',22) !!}</div>
        <div>
            <div class="stat-val">{{ number_format($deliveredCount ?? 0) }}+</div>
            <div class="stat-lbl">Livraisons réussies</div>
            <div class="stat-sub">Dans les délais</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-ico s4">{!! $si('users',22) !!}</div>
        <div>
            <div class="stat-val">{{ number_format($clientCount ?? 0) }}+</div>
            <div class="stat-lbl">Clients satisfaits</div>
            <div class="stat-sub">Rejoignez-les !</div>
        </div>
    </div>
</div>

{{-- Flash --}}
@foreach(['success','danger'] as $t)
    @if(session($t))<div class="c-flash c-flash-{{ $t }}"><span>{{ $t === 'success' ? '✓' : '✕' }}</span>{{ session($t) }}</div>@endif
@endforeach

{{-- Commandes récentes --}}
@if(isset($recentOrders) && $recentOrders->isNotEmpty())
<div>
    <div class="sec-hd">
        <div class="sec-title"><strong>Mes</strong> Commandes Récentes</div>
        <a href="{{ route('client.orders.index') }}" class="sec-link">Voir tout →</a>
    </div>
    <div class="orders-card" id="rtOrdersCard">
        <div class="orders-card-hd">
            <span class="orders-card-title">Historique</span>
            <span style="font-size:11px;color:var(--muted)" id="rtOrderCount">{{ $recentOrders->count() }} commande(s)</span>
        </div>
        <div id="rtOrdersList">
        @foreach($recentOrders as $order)
        @php
            $st   = $statusMap[$order->status] ?? ['pill-pending', ucfirst($order->status)];
            $oIcoSvg = match($order->status) {
                'livrée'                     => [$si('check',14),  'background:#d1fae5;color:#059669'],
                'en_livraison','en livraison' => [$si('truck',14),  'background:#dbeafe;color:#3b82f6'],
                'annulée','cancelled'          => [$si('x',14),     'background:#fee2e2;color:#e53e3e'],
                default                       => [$si('box',14),   'background:#fef3c7;color:#d97706'],
            };
        @endphp
        <a href="{{ route('client.orders.index') }}#order-{{ $order->id }}" class="order-row" data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}">
            <div class="order-ico" id="oIco{{ $order->id }}" style="{{ $oIcoSvg[1] }}">{!! $oIcoSvg[0] !!}</div>
            <div class="order-info">
                <div class="order-ref">#{{ $order->id }}</div>
                <div class="order-shop">{{ $order->shop?->name ?? 'Boutique' }}</div>
            </div>
            <span class="order-pill {{ $st[0] }}" id="oPill{{ $order->id }}">{{ $st[1] }}</span>
            <div class="order-amount">{{ number_format($order->total, 0, ',', ' ') }} <span style="font-size:10px;font-weight:400;color:var(--muted)">{{ $order->shop?->currency ?? 'GNF' }}</span></div>
        </a>
        @endforeach
        </div>
    </div>
</div>
@endif

{{-- Catégories populaires --}}
@if(isset($categories) && $categories->isNotEmpty())
<div id="categories" style="scroll-margin-top:80px">
   
    <div class="pop-cats-row" id="popCatsRow">
        <button class="pop-cat-chip active" data-cat-type="" onclick="filterByCat('')">
            <span class="pop-cat-ico">{!! $si('store',22) !!}</span>
            <span class="pop-cat-name">Toutes</span>
            <span class="pop-cat-cnt">{{ $shopCount ?? $shops->total() }} boutiques</span>
        </button>
        @foreach($categories->take(6) as $cat)
        <button class="pop-cat-chip" data-cat-type="{{ $cat->type }}" onclick="filterByCat(this.dataset.catType)">
            <span class="pop-cat-ico">{!! $getSbEmoji($cat->type) !!}</span>
            <span class="pop-cat-name">{{ $cat->type }}</span>
            <span class="pop-cat-cnt">{{ $cat->shop_count }} boutique{{ $cat->shop_count > 1 ? 's' : '' }}</span>
        </button>
        @endforeach
        @if($categories->count() > 6)
        @foreach($categories->skip(6) as $cat)
        <button class="pop-cat-chip pop-cat-extra" data-cat-type="{{ $cat->type }}" onclick="filterByCat(this.dataset.catType)" style="display:none">
            <span class="pop-cat-ico">{!! $getSbEmoji($cat->type) !!}</span>
            <span class="pop-cat-name">{{ $cat->type }}</span>
            <span class="pop-cat-cnt">{{ $cat->shop_count }} boutique{{ $cat->shop_count > 1 ? 's' : '' }}</span>
        </button>
        @endforeach
        @endif
    </div>
    @if($categories->count() > 6)
    <div style="text-align:center;margin-top:10px">
        <button id="popCatsMoreBtn" onclick="togglePopCats(this)" data-count="{{ $categories->count() - 6 }}"
            style="display:inline-flex;align-items:center;gap:6px;padding:7px 18px;border-radius:20px;border:1.5px solid var(--orange);background:var(--orange-lt);color:var(--orange);font-size:12px;font-weight:700;cursor:pointer;font-family:var(--font);transition:all .2s">
            ＋ Voir les {{ $categories->count() - 6 }} autres catégories
        </button>
    </div>
    @endif
</div>
@endif

{{-- Boutiques populaires --}}
@if(isset($topShops) && $topShops->isNotEmpty())
<div>
    <div class="sec-hd">
        <div class="sec-title">
            <strong>Boutiques</strong> populaires
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:13px;font-weight:500;color:var(--muted);font-family:var(--font)">{!! $si('trending',14) !!} Tendances cette semaine</span>
        </div>
        <button onclick="openRankingModal()" style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:var(--orange);color:#fff;border:none;border-radius:50px;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);box-shadow:0 3px 10px rgba(240,106,15,.3);transition:background .15s,transform .1s;flex-shrink:0" onmouseover="this.style.background='var(--orange-dk)'" onmouseout="this.style.background='var(--orange)'">🏆 Voir le classement</button>
    </div>
    <div class="top-shops-grid">
        @foreach($topShops as $ts)
        @php
            [$tsIcoKey, $tsBg] = $typeIco[$ts->type ?? ''] ?? ['bag', 'bg-default'];
            $tsParts  = explode(' ', $ts->name);
            $tsInit   = strtoupper(substr($tsParts[0],0,1)) . strtoupper(substr($tsParts[1] ?? 'X',0,1));
            $avGrads  = [
                'linear-gradient(135deg,#667eea,#764ba2)',
                'linear-gradient(135deg,#f5576c,#f093fb)',
                'linear-gradient(135deg,#4facfe,#00c6fb)',
                'linear-gradient(135deg,#cc2b5e,#753a88)',
                'linear-gradient(135deg,#ee0979,#ff6a00)',
                'linear-gradient(135deg,#24c6dc,#514a9d)',
                'linear-gradient(135deg,#11998e,#38ef7d)',
                'linear-gradient(135deg,#fc4a1a,#f7b733)',
            ];
            $tsGrad = $avGrads[abs(crc32($ts->name)) % count($avGrads)];
        @endphp
        <div class="top-shop-card" data-shop-id="{{ $ts->id }}">
            {{-- Image --}}
            <div class="top-shop-img">
                @if($ts->image)
                    <img src="{{ \App\Services\ImageOptimizer::url($ts->image, 'medium') }}" alt="{{ $ts->name }}" loading="lazy">
                @else
                    <div class="top-shop-img-ph">{!! $si($tsIcoKey, 36) !!}</div>
                @endif
                <span class="top-shop-open-badge">● Ouvert</span>
                <button class="top-shop-heart {{ in_array($ts->id, $favoriteIds) ? 'favorited' : '' }}"
                        data-shop-id="{{ $ts->id }}"
                        onclick="event.preventDefault();toggleFavorite({{ $ts->id }}, this)"
                        title="{{ in_array($ts->id, $favoriteIds) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                    {!! $si('heart',14) !!}
                </button>
            </div>
            {{-- Body --}}
            <div class="top-shop-body">
                <div class="top-shop-body-row">
                    <div class="shop-av" style="background:{{ $tsGrad }};width:50px;height:50px;font-size:15px">{{ $tsInit }}</div>
                    <div style="flex:1;min-width:0;padding-top:2px">
                        @if($ts->type)
                        <span class="top-shop-tag">{{ strtoupper($ts->type) }}</span>
                        @endif
                        <div style="display:flex;align-items:flex-start;gap:4px;margin:2px 0 4px">
                            <span class="shop-name-clamp top-shop-name" style="font-size:13.5px;flex:1;min-width:0">{{ $ts->name }}</span>
                            <span class="top-shop-verified" style="margin-top:1px;flex-shrink:0" title="Boutique vérifiée">{!! $si('verified',14) !!}</span>
                        </div>
                        <p class="top-shop-desc">{{ $ts->description ?? 'Boutique vérifiée sur Shopio.' }}</p>
                        <div class="top-shop-loc">
                            {!! $si('pin',12) !!}
                            {{ $ts->address ?? ($ts->city ?? $ts->country ?? 'Guinée') }}
                        </div>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            <div class="top-shop-footer">
                <div class="top-shop-stats">
                    <div class="top-shop-rating">
                        {!! $sif('star-o',13) !!} {{ $ts->avg_rating ? number_format($ts->avg_rating, 1) : '—' }}
                        <span style="color:var(--muted);font-weight:400;font-size:10px"> ({{ $ts->reviews_count ?? 0 }} avis)</span>
                    </div>
                    <div class="top-shop-sales">{!! $si('bag',12) !!} +{{ number_format($ts->sales_count ?? 0) }} ventes</div>
                </div>
                <a href="{{ route('client.shops.show', $ts) }}" class="top-shop-btn">Visiter la boutique →</a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Catégories filtre pills + grille boutiques --}}
<div id="boutiques" style="scroll-margin-top:80px">
    <div class="sec-hd">
        <div class="sec-title">
            <strong>Toutes</strong> les Boutiques
            <span style="font-size:14px;font-weight:500;color:var(--muted);font-family:var(--font)">
                (<span id="shopCount">{{ $shops->total() }}</span>)
            </span>
        </div>
    </div>

    <div class="cats" id="catFilter">
            @php
                /* ── Emojis prédéfinis (extensible) ── */
                $catIcoMap = [
                    'alimentation'=>'utensils','restaurant'=>'utensils','épicerie'=>'cart','epicerie'=>'cart',
                    'boulangerie'=>'bread','pâtisserie'=>'bread','patisserie'=>'bread',
                    'vêtements'=>'shirt','vetements'=>'shirt','mode'=>'shirt',
                    'bijouterie'=>'gem','bijoux'=>'gem',
                    'électronique'=>'smartphone','electronique'=>'smartphone',
                    'informatique'=>'monitor','téléphonie'=>'phone-call','telephonie'=>'phone-call',
                    'beauté & cosmétiques'=>'sparkles','beaute & cosmetiques'=>'sparkles',
                    'beauté'=>'sparkles','beaute'=>'sparkles','cosmétiques'=>'sparkles','cosmetiques'=>'sparkles',
                    'pharmacie'=>'medical','parfumerie'=>'flower',
                    'auto & moto'=>'car','auto'=>'car','automobile'=>'car','moto'=>'car',
                    'sport'=>'activity','sport & loisirs'=>'activity',
                    'jouets'=>'heart','enfants'=>'heart',
                    'maison'=>'home','décoration'=>'home','decoration'=>'home','mobilier'=>'home',
                    'librairie'=>'book','livres'=>'book',
                    'musique'=>'music','téléphone'=>'smartphone','telephone'=>'smartphone',
                    'high-tech'=>'monitor','high tech'=>'monitor',
                    'jardin'=>'leaf','agriculture'=>'wheat',
                    'animalerie'=>'heart','voyage'=>'globe','artisanat'=>'sparkles','art'=>'sparkles',
                    'santé'=>'health','sante'=>'health','médical'=>'medical','medical'=>'medical',
                    'construction'=>'wrench','quincaillerie'=>'wrench','outillage'=>'wrench',
                    'fournitures'=>'tag','bureau'=>'tag',
                    'supermarché'=>'cart','supermarche'=>'cart',
                    'épices'=>'utensils','épice'=>'utensils','boissons'=>'utensils',
                    'chaussures'=>'shoe','accessoires'=>'handbag','sacs'=>'handbag',
                ];

                /* Récupère TOUS les types distincts des boutiques approuvées du pays */
                $existingTypes = \App\Models\Shop::where('is_approved', true)
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->when(auth()->user()?->country, fn($q, $c) => $q->where('country', $c))
                    ->distinct()
                    ->orderBy('type')
                    ->pluck('type')
                    ->toArray();

                $getEmoji = function(string $type) use ($catIcoMap, $si): string {
                    $key = mb_strtolower(trim($type));
                    if (isset($catIcoMap[$key])) return $si($catIcoMap[$key], 15);
                    foreach ($catIcoMap as $k => $icoKey) { if (str_contains($key, $k)) return $si($icoKey, 15); }
                    return $si('store', 15);
                };
            @endphp

            {{-- Pill "Toutes" --}}
            <button class="cat-pill {{ $activeType === '' ? 'active' : '' }}"
                    onclick="filterByType('', this)">
                {!! $si('store',15) !!} Toutes <span class="cat-pill-cnt" id="catCntAll">{{ $shops->total() }}</span>
            </button>

            {{-- Une pill par type existant en base --}}
            @foreach($existingTypes as $t)
            <button class="cat-pill {{ $activeType === $t ? 'active' : '' }}"
                    data-type-val="{{ $t }}"
                    onclick="filterByType(this.dataset.typeVal, this)">
                {!! $getEmoji($t) !!} {{ $t }}
                <span class="cat-pill-cnt" data-cat="{{ $t }}">…</span>
            </button>
            @endforeach
        </div>
    </div>

        <div class="shops-grid" id="shopsGrid">
            @forelse($shops as $shop)
            @php
                [$icoKey, $bgClass] = $typeIco[$shop->type ?? ''] ?? ['bag', 'bg-default'];
                $isNew   = $shop->created_at->diffInDays(now()) <= 7;
                $sParts  = explode(' ', $shop->name);
                $sInit   = strtoupper(substr($sParts[0],0,1)) . strtoupper(substr($sParts[1] ?? 'X',0,1));
                $sGrads  = [
                    'linear-gradient(135deg,#667eea,#764ba2)',
                    'linear-gradient(135deg,#f5576c,#f093fb)',
                    'linear-gradient(135deg,#4facfe,#00c6fb)',
                    'linear-gradient(135deg,#cc2b5e,#753a88)',
                    'linear-gradient(135deg,#ee0979,#ff6a00)',
                    'linear-gradient(135deg,#24c6dc,#514a9d)',
                    'linear-gradient(135deg,#0f2027,#203a43,#2c5364)',
                    'linear-gradient(135deg,#c94b4b,#4b134f)',
                    'linear-gradient(135deg,#11998e,#38ef7d)',
                    'linear-gradient(135deg,#fc4a1a,#f7b733)',
                    'linear-gradient(135deg,#1a1a2e,#e94560)',
                    'linear-gradient(135deg,#2c3e50,#4ca1af)',
                ];
                $sGrad = $sGrads[abs(crc32($shop->name)) % count($sGrads)];
            @endphp
            @php
                $dProdKw = $shop->products
                    ->map(function($p){ return strtolower($p->name.' '.($p->category ?? '')); })
                    ->implode(' ');
            @endphp
            <a href="{{ route('client.shops.show', $shop) }}"
               class="shop-card"
               data-name="{{ strtolower($shop->name) }}"
               data-type="{{ strtolower($shop->type ?? '') }}"
               data-products="{{ $dProdKw }}">

                <div class="shop-card-img">
                    @if($shop->image)
                        <img src="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') }}"
                             srcset="{{ \App\Services\ImageOptimizer::url($shop->image, 'thumb') }} 300w,
                                     {{ \App\Services\ImageOptimizer::url($shop->image, 'medium') }} 800w"
                             sizes="(max-width:600px) 50vw, (max-width:900px) 33vw, 220px"
                             alt="{{ $shop->name }}"
                             loading="lazy" decoding="async" width="220" height="160">
                    @else
                        <div class="shop-card-placeholder {{ $bgClass }}">{!! $si($icoKey, 40) !!}</div>
                    @endif
                    @if($isNew)
                        <span class="shop-card-badge badge-new">✨ Nouveau</span>
                    @else
                        <span class="shop-card-badge badge-open">Ouvert</span>
                    @endif
                    <button class="shop-card-fav-btn {{ in_array($shop->id, $favoriteIds) ? 'favorited' : '' }}"
                            data-shop-id="{{ $shop->id }}"
                            onclick="event.preventDefault();event.stopPropagation();toggleFavorite({{ $shop->id }}, this)"
                            title="{{ in_array($shop->id, $favoriteIds) ? 'Retirer des favoris' : 'Ajouter aux favoris' }}">
                        {!! $si('heart',15) !!}
                    </button>
                </div>

                <div class="shop-card-body">
                    {{-- Ligne avatar + nom + icône vérifié --}}
                    <div style="display:flex;gap:11px;align-items:flex-start;margin-bottom:7px">
                        <div class="shop-av" style="background:{{ $sGrad }}">{{ $sInit }}</div>
                        <div style="flex:1;min-width:0;padding-top:2px">
                            @if($shop->type)
                            <div class="shop-card-type" style="margin-bottom:2px">{{ strtoupper($shop->type) }}</div>
                            @endif
                            <div style="display:flex;align-items:flex-start;gap:4px">
                                <div class="shop-name-clamp" style="flex:1;min-width:0">{{ $shop->name }}</div>
                                <span style="color:#3b82f6;flex-shrink:0;margin-top:1px" title="Boutique approuvée">{!! $si('verified',14) !!}</span>
                            </div>
                        </div>
                    </div>
                    {{-- Description --}}
                    @if($shop->description)
                    <p class="shop-card-desc">{{ $shop->description }}</p>
                    @endif
                    {{-- Adresse --}}
                    @if($shop->address ?? false)
                    <div style="display:flex;align-items:center;gap:4px;font-size:11px;color:var(--muted);margin-top:4px">
                        {!! $si('pin',11) !!} {{ Str::limit($shop->address, 24) }}
                    </div>
                    @endif
                    <span class="shop-prod-match">📦 A ce produit</span>
                </div>

                <div class="shop-card-footer">
                    <div style="display:flex;flex-direction:column;gap:3px">
                        <div class="shop-card-rating">
                            {!! $sif('star-o',13) !!} {{ $shop->avg_rating ? number_format($shop->avg_rating, 1) : '—' }}
                            <small>({{ $shop->reviews_count ?? 0 }} avis)</small>
                        </div>
                        <div style="font-size:10.5px;color:var(--muted);display:flex;align-items:center;gap:3px">
                            {!! $si('bag',11) !!} +{{ number_format($shop->sales_count ?? 0) }} ventes
                        </div>
                    </div>
                    <span class="shop-card-cta">Visiter →</span>
                </div>
            </a>
            @empty
            <div class="c-empty">
                <span class="c-empty-ico">{!! $si('store',48) !!}</span>
                <div class="c-empty-title">Aucune boutique disponible</div>
                <p class="c-empty-sub">Revenez bientôt.</p>
            </div>
            @endforelse
        </div>
        <div id="shopsLiveEmpty">
            <span class="ico">🔍</span>
            <p id="shopsLiveEmptyMsg">Aucun résultat pour votre recherche.</p>
        </div>

        <div class="c-pagination">{{ $shops->links() }}</div>

</div>{{-- /.main-col --}}
</div>{{-- /.page-wrap --}}

{{-- ══ BARRE NAVIGATION MOBILE (bas d'écran) ══ --}}
<nav class="mob-bottom-nav" aria-label="Navigation principale">
    <div class="mob-bottom-nav-inner">
        <a href="{{ route('client.dashboard') }}" class="mob-nav-item active">
            <div class="mob-nav-item-wrap">
                {!! $si('home', 21) !!}
                <span>Accueil</span>
            </div>
        </a>
        <a href="#boutiques" class="mob-nav-item" onclick="document.querySelectorAll('.mob-nav-item').forEach(i=>i.classList.remove('active'));this.classList.add('active')">
            <div class="mob-nav-item-wrap">
                {!! $si('store', 21) !!}
                <span>Boutiques</span>
            </div>
        </a>
        <a href="{{ route('client.orders.index') }}" class="mob-nav-item">
            <div class="mob-nav-item-wrap">
                {!! $si('package', 21) !!}
                <span>Commandes</span>
            </div>
        </a>
        <button class="mob-nav-item" onclick="openProfileModal()" title="Profil">
            <div class="mob-nav-item-wrap">
                {!! $si('user', 21) !!}
                <span>Profil</span>
            </div>
        </button>
    </div>
</nav>

{{-- ══ MODALE PROFIL (3 onglets) ══ --}}
{{-- ══ MODALE CLASSEMENT ══ --}}
@if(isset($allTopShops) && $allTopShops->isNotEmpty())
@php
$rGrads = [
    'linear-gradient(135deg,#667eea,#764ba2)',
    'linear-gradient(135deg,#f5576c,#f093fb)',
    'linear-gradient(135deg,#4facfe,#00c6fb)',
    'linear-gradient(135deg,#cc2b5e,#753a88)',
    'linear-gradient(135deg,#ee0979,#ff6a00)',
    'linear-gradient(135deg,#24c6dc,#514a9d)',
    'linear-gradient(135deg,#11998e,#38ef7d)',
    'linear-gradient(135deg,#f59e0b,#f97316)',
];
@endphp
<div class="rank-overlay" id="rankOverlay" onclick="if(event.target===this)closeRankingModal()">
    <div class="rank-modal">
        <div class="rank-modal-head">
            <span class="rank-modal-title">🏆 Classement des boutiques</span>
            <button class="rank-modal-close" onclick="closeRankingModal()">✕</button>
        </div>
        <div class="rank-modal-body" id="rankModalBody">
            @foreach($allTopShops as $i => $ts)
            @php
                $rParts   = explode(' ', $ts->name);
                $rInit    = strtoupper(substr($rParts[0],0,1)) . strtoupper(substr($rParts[1] ?? 'X',0,1));
                $rGrad    = $rGrads[abs(crc32($ts->name)) % count($rGrads)];
                $medal    = $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : null));
                $posClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
            @endphp
            <a href="{{ route('client.shops.show', $ts) }}" class="rank-row" data-rank-idx="{{ $i }}">
                <div class="rank-pos {{ $posClass }}">{{ $medal ?? ($i + 1) }}</div>
                <div class="rank-av" style="background:{{ $rGrad }}">
                    @if($ts->image)
                        <img src="{{ \App\Services\ImageOptimizer::url($ts->image, 'thumb') }}" alt="{{ $ts->name }}">
                    @else
                        {{ $rInit }}
                    @endif
                </div>
                <div class="rank-info">
                    <div class="rank-name">{{ $ts->name }}</div>
                    <div class="rank-stats">
                        ⭐ <strong>{{ $ts->avg_rating ? number_format($ts->avg_rating, 1) : '—' }}</strong>
                        &nbsp;·&nbsp; {{ number_format($ts->sales_count ?? 0) }} ventes
                    </div>
                </div>
                <span class="rank-link">Voir →</span>
            </a>
            @endforeach
        </div>
        <div class="rank-modal-foot" id="rankModalFoot" style="display:none;">
            <span class="rank-pg-info" id="rankPgInfo"></span>
            <div class="rank-pg-btns">
                <button class="rank-pg-btn" id="rankPgPrev" onclick="rankChangePage(-1)">← Préc.</button>
                <button class="rank-pg-btn" id="rankPgNext" onclick="rankChangePage(1)">Suiv. →</button>
            </div>
        </div>
    </div>
</div>
@endif

<div id="profileOverlay" onclick="if(event.target===this)closeProfileModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:700;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(3px)">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:500px;max-height:90vh;overflow:hidden;box-shadow:0 24px 80px rgba(0,0,0,.3);animation:slideUp .28s cubic-bezier(.23,1,.32,1);display:flex;flex-direction:column">

        {{-- ── HEADER ── --}}
        <div style="background:linear-gradient(135deg,#f06a0f 0%,#d45a08 100%);padding:24px 24px 20px;flex-shrink:0;position:relative;overflow:hidden">
            {{-- Cercles déco --}}
            <div style="position:absolute;right:-30px;top:-30px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.08);pointer-events:none"></div>
            <div style="position:absolute;right:50px;bottom:-40px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.06);pointer-events:none"></div>

            <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1">
                {{-- Avatar --}}
                <div style="position:relative;flex-shrink:0">
                    <div style="width:58px;height:58px;border-radius:50%;background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:900;color:#fff;font-family:var(--display)">
                        {{ $initials }}
                    </div>
                    @if($countryFlag)
                    <span style="position:absolute;bottom:-2px;right:-4px;font-size:18px;line-height:1;background:#fff;border-radius:50%;padding:2px;box-shadow:0 0 0 2px rgba(240,106,15,.4)">{{ $countryFlag }}</span>
                    @endif
                </div>
                {{-- Infos --}}
                <div style="flex:1;min-width:0">
                    <div style="font-size:17px;font-weight:900;color:#fff;font-family:var(--display);letter-spacing:-.3px">{{ $user->name }}</div>
                    <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $user->email }}</div>
                    @if($countryName)
                    <div style="display:inline-flex;align-items:center;gap:4px;margin-top:5px;background:rgba(255,255,255,.18);border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;color:#fff">
                        {{ $countryFlag }} {{ $countryName }}
                    </div>
                    @endif
                </div>
                {{-- Fermer --}}
                <button onclick="closeProfileModal()"
                        style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.18);border:1.5px solid rgba(255,255,255,.3);color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s"
                        onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.18)'">✕</button>
            </div>

            {{-- Onglets --}}
            <div style="display:flex;gap:6px;margin-top:18px;position:relative;z-index:1">
                <button id="ptab-info" onclick="switchProfileTab('info')"
                        style="flex:1;padding:8px 4px;border-radius:10px;border:none;font-size:12px;font-weight:700;font-family:var(--font);cursor:pointer;transition:all .18s;background:rgba(255,255,255,.25);color:#fff">
                    👤 Profil
                </button>
                <button id="ptab-pwd" onclick="switchProfileTab('pwd')"
                        style="flex:1;padding:8px 4px;border-radius:10px;border:none;font-size:12px;font-weight:700;font-family:var(--font);cursor:pointer;transition:all .18s;background:rgba(255,255,255,.12);color:rgba(255,255,255,.7)">
                    🔒 Mot de passe
                </button>
                <button id="ptab-del" onclick="switchProfileTab('del')"
                        style="flex:1;padding:8px 4px;border-radius:10px;border:none;font-size:12px;font-weight:700;font-family:var(--font);cursor:pointer;transition:all .18s;background:rgba(255,255,255,.12);color:rgba(255,255,255,.7)">
                    🗑️ Supprimer
                </button>
            </div>
        </div>

        {{-- ── CORPS SCROLLABLE ── --}}
        <div style="overflow-y:auto;flex:1;scrollbar-width:thin;scrollbar-color:#dde3ea transparent">

            {{-- ════ ONGLET 1 : INFORMATIONS ════ --}}
            <div id="ptab-info-body">
                <form method="POST" action="{{ route('profile.update') }}" id="profileForm">
                    @csrf @method('PATCH')
                    <div style="padding:22px 24px;display:flex;flex-direction:column;gap:14px">

                        {{-- Flash erreurs profil --}}
                        @if($errors->any() && !$errors->updatePassword->any() && !$errors->userDeletion->any())
                        <div style="background:#fff7ed;border:1.5px solid #f06a0f;border-radius:10px;padding:10px 14px;font-size:12.5px;color:#92400e;display:flex;gap:8px;align-items:flex-start">
                            <span>⚠️</span>
                            <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                        </div>
                        @endif
                        @if(session('status') === 'profile-updated')
                        <div style="background:#f0fdf4;border:1.5px solid #6ee7b7;border-radius:10px;padding:10px 14px;font-size:12.5px;color:#065f46;display:flex;gap:8px;align-items:center">
                            ✓ Profil mis à jour avec succès !
                        </div>
                        @endif

                        @php
                        $iStyle = "width:100%;padding:10px 14px 10px 40px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box";
                        $lStyle = "display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px";
                        @endphp

                        {{-- Nom --}}
                        <div>
                            <label style="{{ $lStyle }}">Nom complet</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none">👤</span>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="{{ $iStyle }}"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(240,106,15,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                        </div>

                        {{-- Email --}}
                        <div>
                            <label style="{{ $lStyle }}">Adresse email</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none">✉️</span>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="{{ $iStyle }}"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(240,106,15,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                        </div>

                        {{-- Téléphone --}}
                        <div>
                            <label style="{{ $lStyle }}">Téléphone</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none">📱</span>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+224 6XX XXX XXX" style="{{ $iStyle }}"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(240,106,15,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                        </div>

                        {{-- Adresse --}}
                        <div>
                            <label style="{{ $lStyle }}">Adresse de livraison</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none">📍</span>
                                <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Quartier, rue…" style="{{ $iStyle }}"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(240,106,15,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                            </div>
                        </div>

                        {{-- Pays --}}
                        <div>
                            <label style="{{ $lStyle }}">
                                🌍 Pays
                                <span style="font-size:10px;font-weight:500;color:var(--orange);text-transform:none;letter-spacing:0"> — changer de pays actualise vos boutiques</span>
                            </label>
                            <div style="position:relative">
                                <span id="modalFlagPreview" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:20px;pointer-events:none;z-index:1;line-height:1">{{ $countryFlag ?: '🌍' }}</span>
                                <select name="country" id="modalCountry" onchange="updateModalFlag(this)"
                                        style="width:100%;padding:10px 14px 10px 44px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;appearance:none;-webkit-appearance:none;cursor:pointer;outline:none;box-sizing:border-box"
                                        onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(240,106,15,.1)'"
                                        onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                                    <option value="">-- Sélectionner un pays --</option>
                                    @php
                                    $allCountries = [
                                        'Afrique de l\'Ouest' => [
                                            'BJ'=>'🇧🇯 Bénin','BF'=>'🇧🇫 Burkina Faso','CV'=>'🇨🇻 Cap-Vert',
                                            'CI'=>"🇨🇮 Côte d'Ivoire",'GM'=>'🇬🇲 Gambie','GH'=>'🇬🇭 Ghana',
                                            'GN'=>'🇬🇳 Guinée','GW'=>'🇬🇼 Guinée-Bissau','LR'=>'🇱🇷 Libéria',
                                            'ML'=>'🇲🇱 Mali','MR'=>'🇲🇷 Mauritanie','NE'=>'🇳🇪 Niger',
                                            'NG'=>'🇳🇬 Nigéria','SN'=>'🇸🇳 Sénégal','SL'=>'🇸🇱 Sierra Leone',
                                            'TG'=>'🇹🇬 Togo',
                                        ],
                                        'Afrique Centrale' => [
                                            'CM'=>'🇨🇲 Cameroun','CF'=>'🇨🇫 Centrafrique','TD'=>'🇹🇩 Tchad',
                                            'CG'=>'🇨🇬 Congo','CD'=>'🇨🇩 RD Congo','GQ'=>'🇬🇶 Guinée Équatoriale',
                                            'GA'=>'🇬🇦 Gabon','ST'=>'🇸🇹 São Tomé','BI'=>'🇧🇮 Burundi',
                                            'RW'=>'🇷🇼 Rwanda',
                                        ],
                                        'Afrique de l\'Est' => [
                                            'DJ'=>'🇩🇯 Djibouti','ER'=>'🇪🇷 Érythrée','ET'=>'🇪🇹 Éthiopie',
                                            'KE'=>'🇰🇪 Kenya','KM'=>'🇰🇲 Comores','MG'=>'🇲🇬 Madagascar',
                                            'MW'=>'🇲🇼 Malawi','MU'=>'🇲🇺 Maurice','MZ'=>'🇲🇿 Mozambique',
                                            'SC'=>'🇸🇨 Seychelles','SO'=>'🇸🇴 Somalie','SS'=>'🇸🇸 Soudan du Sud',
                                            'SD'=>'🇸🇩 Soudan','TZ'=>'🇹🇿 Tanzanie','UG'=>'🇺🇬 Ouganda',
                                            'ZM'=>'🇿🇲 Zambie','ZW'=>'🇿🇼 Zimbabwe',
                                        ],
                                        'Afrique du Nord' => [
                                            'DZ'=>'🇩🇿 Algérie','EG'=>'🇪🇬 Égypte','LY'=>'🇱🇾 Libye',
                                            'MA'=>'🇲🇦 Maroc','SD'=>'🇸🇩 Soudan','TN'=>'🇹🇳 Tunisie',
                                        ],
                                        'Afrique Australe' => [
                                            'AO'=>'🇦🇴 Angola','BW'=>'🇧🇼 Botswana','LS'=>'🇱🇸 Lesotho',
                                            'NA'=>'🇳🇦 Namibie','ZA'=>'🇿🇦 Afrique du Sud','SZ'=>'🇸🇿 Eswatini',
                                        ],
                                        'Europe' => [
                                            'AL'=>'🇦🇱 Albanie','DE'=>'🇩🇪 Allemagne','AT'=>'🇦🇹 Autriche',
                                            'BE'=>'🇧🇪 Belgique','BA'=>'🇧🇦 Bosnie','BG'=>'🇧🇬 Bulgarie',
                                            'HR'=>'🇭🇷 Croatie','CY'=>'🇨🇾 Chypre','DK'=>'🇩🇰 Danemark',
                                            'ES'=>'🇪🇸 Espagne','EE'=>'🇪🇪 Estonie','FI'=>'🇫🇮 Finlande',
                                            'FR'=>'🇫🇷 France','GR'=>'🇬🇷 Grèce','HU'=>'🇭🇺 Hongrie',
                                            'IE'=>'🇮🇪 Irlande','IS'=>'🇮🇸 Islande','IT'=>'🇮🇹 Italie',
                                            'XK'=>'🇽🇰 Kosovo','LV'=>'🇱🇻 Lettonie','LI'=>'🇱🇮 Liechtenstein',
                                            'LT'=>'🇱🇹 Lituanie','LU'=>'🇱🇺 Luxembourg','MK'=>'🇲🇰 Macédoine',
                                            'MT'=>'🇲🇹 Malte','MD'=>'🇲🇩 Moldavie','MC'=>'🇲🇨 Monaco',
                                            'ME'=>'🇲🇪 Monténégro','NO'=>'🇳🇴 Norvège','NL'=>'🇳🇱 Pays-Bas',
                                            'PL'=>'🇵🇱 Pologne','PT'=>'🇵🇹 Portugal','CZ'=>'🇨🇿 Rép. Tchèque',
                                            'RO'=>'🇷🇴 Roumanie','GB'=>'🇬🇧 Royaume-Uni','RU'=>'🇷🇺 Russie',
                                            'RS'=>'🇷🇸 Serbie','SK'=>'🇸🇰 Slovaquie','SI'=>'🇸🇮 Slovénie',
                                            'SE'=>'🇸🇪 Suède','CH'=>'🇨🇭 Suisse','UA'=>'🇺🇦 Ukraine',
                                        ],
                                        'Amériques' => [
                                            'AR'=>'🇦🇷 Argentine','BB'=>'🇧🇧 Barbade','BO'=>'🇧🇴 Bolivie',
                                            'BR'=>'🇧🇷 Brésil','CA'=>'🇨🇦 Canada','CL'=>'🇨🇱 Chili',
                                            'CO'=>'🇨🇴 Colombie','CR'=>'🇨🇷 Costa Rica','CU'=>'🇨🇺 Cuba',
                                            'DM'=>'🇩🇲 Dominique','DO'=>'🇩🇴 Rép. Dominicaine','EC'=>'🇪🇨 Équateur',
                                            'SV'=>'🇸🇻 Salvador','US'=>'🇺🇸 États-Unis','GT'=>'🇬🇹 Guatemala',
                                            'GY'=>'🇬🇾 Guyana','HT'=>'🇭🇹 Haïti','HN'=>'🇭🇳 Honduras',
                                            'JM'=>'🇯🇲 Jamaïque','MX'=>'🇲🇽 Mexique','NI'=>'🇳🇮 Nicaragua',
                                            'PA'=>'🇵🇦 Panama','PY'=>'🇵🇾 Paraguay','PE'=>'🇵🇪 Pérou',
                                            'TT'=>'🇹🇹 Trinité-et-Tobago','UY'=>'🇺🇾 Uruguay','VE'=>'🇻🇪 Venezuela',
                                        ],
                                        'Asie' => [
                                            'AF'=>'🇦🇫 Afghanistan','AM'=>'🇦🇲 Arménie','AZ'=>'🇦🇿 Azerbaïdjan',
                                            'BH'=>'🇧🇭 Bahreïn','BD'=>'🇧🇩 Bangladesh','BT'=>'🇧🇹 Bhoutan',
                                            'MM'=>'🇲🇲 Birmanie','BN'=>'🇧🇳 Brunei','KH'=>'🇰🇭 Cambodge',
                                            'CN'=>'🇨🇳 Chine','KP'=>'🇰🇵 Corée du Nord','KR'=>'🇰🇷 Corée du Sud',
                                            'AE'=>'🇦🇪 Émirats Arabes','GE'=>'🇬🇪 Géorgie','IN'=>'🇮🇳 Inde',
                                            'ID'=>'🇮🇩 Indonésie','IR'=>'🇮🇷 Iran','IQ'=>'🇮🇶 Irak',
                                            'IL'=>'🇮🇱 Israël','JP'=>'🇯🇵 Japon','JO'=>'🇯🇴 Jordanie',
                                            'KZ'=>'🇰🇿 Kazakhstan','KW'=>'🇰🇼 Koweït','KG'=>'🇰🇬 Kirghizistan',
                                            'LA'=>'🇱🇦 Laos','LB'=>'🇱🇧 Liban','MY'=>'🇲🇾 Malaisie',
                                            'MV'=>'🇲🇻 Maldives','MN'=>'🇲🇳 Mongolie','NP'=>'🇳🇵 Népal',
                                            'OM'=>'🇴🇲 Oman','UZ'=>'🇺🇿 Ouzbékistan','PK'=>'🇵🇰 Pakistan',
                                            'PS'=>'🇵🇸 Palestine','PH'=>'🇵🇭 Philippines','QA'=>'🇶🇦 Qatar',
                                            'SA'=>'🇸🇦 Arabie Saoudite','SG'=>'🇸🇬 Singapour','LK'=>'🇱🇰 Sri Lanka',
                                            'SY'=>'🇸🇾 Syrie','TJ'=>'🇹🇯 Tadjikistan','TW'=>'🇹🇼 Taïwan',
                                            'TH'=>'🇹🇭 Thaïlande','TL'=>'🇹🇱 Timor-Leste','TM'=>'🇹🇲 Turkménistan',
                                            'TR'=>'🇹🇷 Turquie','VN'=>'🇻🇳 Viêt Nam','YE'=>'🇾🇪 Yémen',
                                        ],
                                        'Océanie' => [
                                            'AU'=>'🇦🇺 Australie','FJ'=>'🇫🇯 Fidji','KI'=>'🇰🇮 Kiribati',
                                            'MH'=>'🇲🇭 Îles Marshall','FM'=>'🇫🇲 Micronésie','NR'=>'🇳🇷 Nauru',
                                            'NZ'=>'🇳🇿 Nouvelle-Zélande','PW'=>'🇵🇼 Palaos','PG'=>'🇵🇬 Papouasie',
                                            'WS'=>'🇼🇸 Samoa','SB'=>'🇸🇧 Salomon','TO'=>'🇹🇴 Tonga',
                                            'TV'=>'🇹🇻 Tuvalu','VU'=>'🇻🇺 Vanuatu',
                                        ],
                                    ];
                                    @endphp
                                    @foreach($allCountries as $region => $pays)
                                        <optgroup label="{{ $region }}">
                                            @foreach($pays as $code => $label)
                                            <option value="{{ $code }}" {{ $user->country === $code ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                    <div style="padding:14px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end">
                        <button type="button" onclick="closeProfileModal()"
                                style="padding:10px 20px;border-radius:9px;border:1.5px solid var(--border);background:#fff;font-size:13px;font-weight:700;color:var(--text-2);cursor:pointer;font-family:var(--font);transition:all .15s"
                                onmouseover="this.style.background='var(--grey)'" onmouseout="this.style.background='#fff'">
                            Annuler
                        </button>
                        <button type="submit"
                                style="padding:10px 26px;border-radius:9px;border:none;background:linear-gradient(135deg,var(--orange),var(--orange-dk));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);display:flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(240,106,15,.4);transition:all .15s"
                                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(240,106,15,.5)'"
                                onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 14px rgba(240,106,15,.4)'">
                            💾 Enregistrer
                        </button>
                    </div>
                </form>
            </div>

            {{-- ════ ONGLET 2 : MOT DE PASSE ════ --}}
            <div id="ptab-pwd-body" style="display:none">
                <form method="POST" action="{{ route('password.update') }}" id="pwdForm">
                    @csrf
                    @method('PUT')

                    <div style="padding:22px 24px 0">
                        {{-- Info --}}
                        <div style="background:linear-gradient(135deg,#fff7ed,#fff3e0);border:1.5px solid #fed7aa;border-radius:12px;padding:14px 16px;margin-bottom:18px;display:flex;gap:10px;align-items:flex-start">
                            <span style="font-size:20px;flex-shrink:0">🔒</span>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:#92400e">Changer votre mot de passe</div>
                                <div style="font-size:12px;color:#b45309;margin-top:3px">Choisissez un mot de passe fort avec au moins 8 caractères.</div>
                            </div>
                        </div>

                        {{-- Erreurs validation --}}
                        @if($errors->updatePassword->any())
                        <div style="background:#fff5f5;border:1.5px solid #fca5a5;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#991b1b">
                            @foreach($errors->updatePassword->all() as $e)
                                <div>⚠️ {{ $e }}</div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Succès --}}
                        @if(session('status') === 'password-updated')
                        <div style="background:#f0fdf4;border:1.5px solid #6ee7b7;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#065f46">
                            ✅ Mot de passe modifié avec succès !
                        </div>
                        @endif
                    </div>

                    <div style="padding:0 24px;display:flex;flex-direction:column;gap:14px">

                        {{-- Mot de passe actuel --}}
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px">Mot de passe actuel</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1">🔑</span>
                                <input type="password" name="current_password" id="pwdCurrent" placeholder="••••••••" autocomplete="current-password"
                                       style="width:100%;padding:10px 44px 10px 40px;border:1.5px solid {{ $errors->updatePassword->has('current_password') ? '#ef4444' : 'var(--border)' }};border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(240,106,15,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                                <button type="button" onclick="togglePwd('pwdCurrent',this)"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--muted);z-index:1">👁</button>
                            </div>
                        </div>

                        {{-- Nouveau mot de passe --}}
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px">Nouveau mot de passe</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1">🔐</span>
                                <input type="password" name="password" id="pwdNew" placeholder="Min. 8 caractères" autocomplete="new-password"
                                       oninput="evalPwdStrength(this.value)"
                                       style="width:100%;padding:10px 44px 10px 40px;border:1.5px solid {{ $errors->updatePassword->has('password') ? '#ef4444' : 'var(--border)' }};border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(240,106,15,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                                <button type="button" onclick="togglePwd('pwdNew',this)"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--muted);z-index:1">👁</button>
                            </div>
                            {{-- Barre de force --}}
                            <div style="margin-top:8px">
                                <div style="height:4px;background:#e5e7eb;border-radius:4px;overflow:hidden">
                                    <div id="pwdStrengthBar" style="height:100%;width:0%;border-radius:4px;transition:width .3s,background .3s"></div>
                                </div>
                                <div id="pwdStrengthLabel" style="font-size:11px;color:var(--muted);margin-top:4px"></div>
                            </div>
                        </div>

                        {{-- Confirmer --}}
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px">Confirmer le nouveau mot de passe</label>
                            <div style="position:relative">
                                <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1">✅</span>
                                <input type="password" name="password_confirmation" id="pwdConfirm" placeholder="Répétez le nouveau mot de passe" autocomplete="new-password"
                                       style="width:100%;padding:10px 44px 10px 40px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box"
                                       onfocus="this.style.borderColor='var(--orange)';this.style.boxShadow='0 0 0 3px rgba(240,106,15,.1)'"
                                       onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                                <button type="button" onclick="togglePwd('pwdConfirm',this)"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--muted);z-index:1">👁</button>
                            </div>
                        </div>

                    </div>
                    <div style="padding:16px 24px 20px;border-top:1px solid var(--border);margin-top:18px;display:flex;gap:10px;justify-content:flex-end">
                        <button type="button" onclick="closeProfileModal()"
                                style="padding:10px 20px;border-radius:9px;border:1.5px solid var(--border);background:#fff;font-size:13px;font-weight:700;color:var(--text-2);cursor:pointer;font-family:var(--font)"
                                onmouseover="this.style.background='var(--grey)'" onmouseout="this.style.background='#fff'">
                            Annuler
                        </button>
                        <button type="submit"
                                style="padding:10px 26px;border-radius:9px;border:none;background:linear-gradient(135deg,var(--orange),var(--orange-dk));color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:var(--font);display:flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(240,106,15,.4);transition:all .15s"
                                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(240,106,15,.5)'"
                                onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 14px rgba(240,106,15,.4)'">
                            🔒 Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>

            {{-- ════ ONGLET 3 : SUPPRIMER ════ --}}
            <div id="ptab-del-body" style="display:none">
                <form method="POST" action="{{ route('profile.destroy') }}" id="delForm" onsubmit="return confirmDel()">
                    @csrf
                    @method('DELETE')

                    <div style="padding:22px 24px">

                        {{-- Zone danger --}}
                        <div style="background:#fff5f5;border:2px solid #fca5a5;border-radius:14px;padding:18px 20px;margin-bottom:20px">
                            <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:14px">
                                <div style="width:40px;height:40px;border-radius:10px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">⚠️</div>
                                <div>
                                    <div style="font-size:14px;font-weight:800;color:#991b1b">Suppression définitive du compte</div>
                                    <div style="font-size:12.5px;color:#b91c1c;margin-top:4px;line-height:1.55">
                                        Cette action est <strong>irréversible</strong>. Toutes vos données seront définitivement supprimées.
                                    </div>
                                </div>
                            </div>
                            <div style="border-top:1px solid #fca5a5;padding-top:12px;display:flex;flex-direction:column;gap:8px">
                                <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#b91c1c"><span style="color:#ef4444">✗</span> Vos commandes et l'historique seront supprimés</div>
                                <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#b91c1c"><span style="color:#ef4444">✗</span> Vos messages avec les boutiques seront supprimés</div>
                                <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#b91c1c"><span style="color:#ef4444">✗</span> Votre accès à toutes les boutiques sera révoqué</div>
                            </div>
                        </div>

                        {{-- Erreur validation --}}
                        @if($errors->userDeletion->any())
                        <div style="background:#fff5f5;border:1.5px solid #fca5a5;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#991b1b">
                            @foreach($errors->userDeletion->all() as $e)
                                <div>⚠️ {{ $e }}</div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Confirmation --}}
                        <div style="display:flex;flex-direction:column;gap:14px">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:700;color:#991b1b;margin-bottom:6px;text-transform:uppercase;letter-spacing:.6px">
                                    Confirmez avec votre mot de passe
                                </label>
                                <div style="position:relative">
                                    <span style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none;z-index:1">🔑</span>
                                    <input type="password" name="password" id="delPassword" placeholder="Votre mot de passe actuel" autocomplete="current-password"
                                           style="width:100%;padding:10px 44px 10px 40px;border:2px solid {{ $errors->userDeletion->has('password') ? '#ef4444' : '#fca5a5' }};border-radius:9px;font-size:14px;font-family:var(--font);color:var(--text);background:#fff;outline:none;box-sizing:border-box"
                                           onfocus="this.style.borderColor='#ef4444';this.style.boxShadow='0 0 0 3px rgba(239,68,68,.15)'"
                                           onblur="this.style.borderColor='#fca5a5';this.style.boxShadow='none'">
                                    <button type="button" onclick="togglePwd('delPassword',this)"
                                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--muted);z-index:1">👁</button>
                                </div>
                            </div>

                            {{-- Checkbox --}}
                            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;background:#fff5f5;border:1.5px solid #fca5a5;border-radius:9px">
                                <input type="checkbox" id="delConfirmCheck" onchange="toggleDelBtn()"
                                       style="margin-top:2px;width:16px;height:16px;accent-color:#ef4444;flex-shrink:0;cursor:pointer">
                                <span style="font-size:12.5px;font-weight:600;color:#991b1b;line-height:1.5">
                                    Je comprends que cette action est irréversible et je veux supprimer définitivement mon compte.
                                </span>
                            </label>
                        </div>

                    </div>
                    <div style="padding:14px 24px 20px;border-top:1px solid #fca5a5;display:flex;gap:10px;justify-content:flex-end">
                        <button type="button" onclick="closeProfileModal()"
                                style="padding:10px 20px;border-radius:9px;border:1.5px solid var(--border);background:#fff;font-size:13px;font-weight:700;color:var(--text-2);cursor:pointer;font-family:var(--font)"
                                onmouseover="this.style.background='var(--grey)'" onmouseout="this.style.background='#fff'">
                            Annuler
                        </button>
                        <button type="submit" id="delBtn" disabled
                                style="padding:10px 20px;border-radius:9px;border:none;background:#d1d5db;color:#fff;font-size:13px;font-weight:700;cursor:not-allowed;font-family:var(--font);display:flex;align-items:center;gap:7px;transition:all .2s">
                            🗑️ Supprimer mon compte
                        </button>
                    </div>
                </form>
            </div>

        </div>{{-- fin corps scrollable --}}
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
(function () {
    const inp       = document.getElementById('globalSearch');
    const grid      = document.getElementById('shopsGrid');
    const liveInfo  = document.getElementById('searchLiveInfo');
    const liveEmpty = document.getElementById('shopsLiveEmpty');
    const liveMsg   = document.getElementById('shopsLiveEmptyMsg');
    if (!inp || !grid) return;

    function escRe(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
    function hlText(text, q) {
        if (!q) return text;
        return text.replace(new RegExp('(' + escRe(q) + ')', 'gi'), '<mark>$1</mark>');
    }
    function scrollToEl(el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function doSearch() {
        const q  = inp.value.trim();
        const ql = q.toLowerCase();

        /* ── 1. Filtrage SYNCHRONE (avant toute animation) ── */
        const cards = Array.from(grid.querySelectorAll('.shop-card'));
        let visible = 0, firstCard = null;
        cards.forEach(card => {
            const name     = card.dataset.name     || '';
            const type     = card.dataset.type     || '';
            const products = card.dataset.products || '';
            const matchShop = !ql || name.includes(ql) || type.includes(ql);
            const matchProd = !matchShop && !!ql && products.includes(ql);
            const match     = matchShop || matchProd;
            card.style.display = match ? '' : 'none';
            card.classList.toggle('prod-match', matchProd);
            const nameEl = card.querySelector('.shop-name-clamp');
            if (nameEl) {
                if (!card.dataset.origName) card.dataset.origName = nameEl.textContent.trim();
                nameEl.innerHTML = ql ? hlText(card.dataset.origName, q) : card.dataset.origName;
            }
            if (match) { visible++; if (!firstCard) firstCard = card; }
        });

        /* ── 2. Mise à jour UI ── */
        if (liveInfo) {
            if (ql) {
                liveInfo.innerHTML = visible > 0
                    ? `${visible} boutique${visible > 1 ? 's' : ''} pour <strong>"${q}"</strong>`
                    : `Aucun résultat pour <strong>"${q}"</strong>`;
                liveInfo.style.display = 'block';
            } else {
                liveInfo.style.display = 'none';
                cards.forEach(c => c.classList.remove('prod-match'));
            }
        }
        const sc = document.getElementById('shopCount');
        if (sc) sc.textContent = visible;
        if (liveEmpty) {
            liveEmpty.style.display = (visible === 0 && ql) ? 'block' : 'none';
            if (liveMsg) liveMsg.textContent = `Aucun résultat pour "${q}". Essayez un autre terme.`;
        }

        /* ── 3. Scroll après reflow complet ── */
        if (ql) {
            const target = firstCard || liveEmpty;
            if (target) setTimeout(() => scrollToEl(target), 50);
        } else {
            setTimeout(() => scrollToEl(grid), 50);
        }
    }

    let timer;
    inp.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(doSearch, 200); });
    inp.addEventListener('keydown', e => { if (e.key === 'Enter') { clearTimeout(timer); doSearch(); } });
    window.doSearch = doSearch;
})();

/* ══════════════════════════════════════════
   FILTRE CATÉGORIES
══════════════════════════════════════════ */

/* Initialise les compteurs par catégorie au chargement */
(function initCatCounts() {
    const counts = {};
    document.querySelectorAll('#shopsGrid .shop-card').forEach(card => {
        const t = (card.dataset.type || '').toLowerCase();
        counts[t] = (counts[t] || 0) + 1;
    });

    // Total dans pill "Toutes"
    const allEl = document.getElementById('catCntAll');
    if (allEl) {
        const total = document.querySelectorAll('#shopsGrid .shop-card').length;
        allEl.textContent = total;
    }

    // Compteur par catégorie
    document.querySelectorAll('#catFilter .cat-pill-cnt[data-cat]').forEach(el => {
        const key = (el.dataset.cat || '').toLowerCase();
        el.textContent = counts[key] || 0;
    });
})();

function filterByType(type, pillEl) {
    const grid = document.getElementById('shopsGrid');

    /* 1. Pills actives — aussi dans la sidebar */
    document.querySelectorAll('#catFilter .cat-pill, .sb-cat-item').forEach(p => p.classList.remove('active'));
    pillEl.classList.add('active');
    document.querySelectorAll(`.sb-cat-item[data-cat-type="${type}"]`).forEach(s => s.classList.add('active'));

    /* 2. Fade + filtrage */
    if (grid) grid.style.opacity = '0';
    setTimeout(() => {
        let count = 0;
        document.querySelectorAll('#shopsGrid .shop-card').forEach(card => {
            const match = !type || (card.dataset.type || '').toLowerCase() === type.toLowerCase();
            card.style.display = match ? '' : 'none';
            if (match) count++;
        });
        const sc = document.getElementById('shopCount');
        if (sc) sc.textContent = count;
        const allEl = document.getElementById('catCntAll');
        if (allEl && !type) allEl.textContent = document.querySelectorAll('#shopsGrid .shop-card').length;
        if (grid) grid.style.opacity = '1';
    }, 180);
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
   FAVORIS
══════════════════════════════════════════ */
const _favRouteBase  = '{{ route("client.favorites.index") }}';
const _favToggleBase = '{{ url("/client/favorites") }}';
let   _favoriteIds   = new Set({{ json_encode($favoriteIds) }});

function openFavDrawer() {
    loadFavList();
    document.getElementById('favDrawer').classList.add('open');
    document.getElementById('favOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeFavDrawer() {
    document.getElementById('favDrawer').classList.remove('open');
    document.getElementById('favOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

function toggleFavorite(shopId, btn) {
    // Feedback visuel immédiat (optimistic UI)
    btn.style.opacity = '0.5';
    btn.disabled = true;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch(`${_favToggleBase}/${shopId}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.favorited) {
            _favoriteIds.add(shopId);
        } else {
            _favoriteIds.delete(shopId);
        }
        // Met à jour TOUS les boutons cœur liés à cette boutique
        document.querySelectorAll(`[data-shop-id="${shopId}"]`).forEach(b => {
            if (b.classList.contains('top-shop-heart') || b.classList.contains('shop-card-fav-btn')) {
                b.disabled = false;
                b.style.opacity = '';
                if (data.favorited) {
                    b.classList.add('favorited');
                    b.title = 'Retirer des favoris';
                } else {
                    b.classList.remove('favorited');
                    b.title = 'Ajouter aux favoris';
                }
            }
        });
        // Badge navbar
        const badge = document.getElementById('navFavBadge');
        if (badge) {
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.classList.add('show');
            } else {
                badge.textContent = '';
                badge.classList.remove('show');
            }
        }
        // Sous-titre drawer
        const sub = document.getElementById('favDrawerSub');
        if (sub) sub.textContent = data.count + ' boutique' + (data.count !== 1 ? 's' : '') + ' sauvegardée' + (data.count !== 1 ? 's' : '');
        // Footer
        const footer = document.getElementById('favDrawerFooter');
        if (footer) footer.style.display = data.count === 0 ? 'none' : '';
        // Si le drawer est ouvert, rafraîchir
        if (document.getElementById('favDrawer').classList.contains('open')) {
            loadFavList();
        }
    })
    .catch(err => {
        console.error('Erreur favoris:', err);
        btn.style.opacity = '';
        btn.disabled = false;
    });
}

function loadFavList() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch(_favRouteBase, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('favShopsContainer');
        const empty     = document.getElementById('favEmptyState');
        const footer    = document.getElementById('favDrawerFooter');
        if (!container) return;
        container.innerHTML = '';
        if (!data.shops || data.shops.length === 0) {
            empty.style.display = '';
            if (footer) footer.style.display = 'none';
            return;
        }
        empty.style.display = 'none';
        if (footer) footer.style.display = '';
        data.shops.forEach(s => {
            const shopUrl = `{{ url('/client/shops') }}/${s.id}`;
            const imgHtml = s.image
                ? `<img src="/storage/${s.image}" alt="${s.name}" loading="lazy">`
                : `<div class="fav-shop-img-ph" style="background:linear-gradient(135deg,#f4f6f8,#e8ecf0)"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#8a9bb0" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path d="M3 9l2-6h14l2 6"/><line x1="12" y1="9" x2="12" y2="21"/></svg></div>`;
            const card = document.createElement('div');
            card.style.cssText = 'display:flex;position:relative';
            card.innerHTML = `
                <a href="${shopUrl}" class="fav-shop-card" style="flex:1;text-decoration:none;color:inherit">
                    <div class="fav-shop-img">${imgHtml}</div>
                    <div class="fav-shop-info">
                        ${s.type ? `<div class="fav-shop-type">${s.type}</div>` : ''}
                        <div class="fav-shop-name">${s.name}</div>
                        <div class="fav-shop-meta">
                            ${s.products_count > 0 ? `<span class="fav-shop-chip"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg> ${s.products_count} produit${s.products_count > 1 ? 's' : ''}</span>` : ''}
                            ${s.sales_count > 0 ? `<span class="fav-shop-chip"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> +${s.sales_count} ventes</span>` : ''}
                        </div>
                    </div>
                </a>
                <button class="fav-shop-rm" onclick="toggleFavorite(${s.id}, this)" title="Retirer des favoris">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>`;
            container.appendChild(card);
        });
    })
    .catch(() => {});
}

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

/* ══════════════════════════════════════════
   MODALE PROFIL — OUVERTURE / FERMETURE
══════════════════════════════════════════ */
function openProfileModal(tab) {
    document.getElementById('profileOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    switchProfileTab(tab || 'info');
}
function closeProfileModal() {
    document.getElementById('profileOverlay').style.display = 'none';
    document.body.style.overflow = '';
}

/* ── Pagination classement ── */
const RANK_PER_PAGE = 10;
let _rankPage = 0;

function _rankRender() {
    const rows = document.querySelectorAll('#rankModalBody .rank-row');
    const total = rows.length;
    const pages = Math.ceil(total / RANK_PER_PAGE);
    const start = _rankPage * RANK_PER_PAGE;
    const end   = start + RANK_PER_PAGE;
    rows.forEach((r, i) => { r.style.display = (i >= start && i < end) ? '' : 'none'; });
    const foot = document.getElementById('rankModalFoot');
    const info = document.getElementById('rankPgInfo');
    const prev = document.getElementById('rankPgPrev');
    const next = document.getElementById('rankPgNext');
    if (total > RANK_PER_PAGE) {
        foot.style.display = '';
        info.textContent   = `Page ${_rankPage + 1} / ${pages}  ·  ${total} boutiques`;
        prev.disabled = _rankPage === 0;
        next.disabled = _rankPage >= pages - 1;
    } else {
        foot.style.display = 'none';
    }
    document.getElementById('rankModalBody').scrollTop = 0;
}

function rankChangePage(dir) {
    const total = document.querySelectorAll('#rankModalBody .rank-row').length;
    const pages = Math.ceil(total / RANK_PER_PAGE);
    _rankPage = Math.max(0, Math.min(pages - 1, _rankPage + dir));
    _rankRender();
}

function openRankingModal() {
    const el = document.getElementById('rankOverlay');
    if (!el) return;
    _rankPage = 0;
    _rankRender();
    el.classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeRankingModal() {
    const el = document.getElementById('rankOverlay');
    if (!el) return;
    el.classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRankingModal(); });

/* ── Onglets ── */
function switchProfileTab(name) {
    ['info','pwd','del'].forEach(function(t) {
        var body = document.getElementById('ptab-' + t + '-body');
        var btn  = document.getElementById('ptab-' + t);
        if (!body || !btn) return;
        var active = (t === name);
        body.style.display  = active ? 'block' : 'none';
        btn.style.background = active ? 'rgba(255,255,255,.95)' : 'rgba(255,255,255,.12)';
        btn.style.color      = active ? '#f06a0f' : 'rgba(255,255,255,.75)';
        btn.style.fontWeight = active ? '800' : '700';
        btn.style.boxShadow  = active ? '0 2px 8px rgba(0,0,0,.12)' : 'none';
    });
}

/* ── Drapeau pays ── */
function updateModalFlag(select) {
    var opt  = select.options[select.selectedIndex];
    var text = opt.textContent.trim();
    var flag = text.split(' ')[0];
    document.getElementById('modalFlagPreview').textContent =
        (flag && flag !== '--') ? flag : '🌍';
}

/* ── Bascule visibilité mot de passe ── */
function togglePwd(inputId, btn) {
    var inp = document.getElementById(inputId);
    if (!inp) return;
    inp.type = inp.type === 'password' ? 'text' : 'password';
    btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}

/* ── Force mot de passe ── */
function evalPwdStrength(val) {
    var bar   = document.getElementById('pwdStrengthBar');
    var label = document.getElementById('pwdStrengthLabel');
    if (!bar || !label) return;
    if (!val) { bar.style.width = '0%'; label.textContent = ''; return; }
    var score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    var levels = [
        { pct:'20%', color:'#ef4444', text:'⚠️ Très faible' },
        { pct:'40%', color:'#f97316', text:'🔸 Faible' },
        { pct:'60%', color:'#eab308', text:'🔶 Moyen' },
        { pct:'80%', color:'#22c55e', text:'✅ Fort' },
        { pct:'100%',color:'#15803d', text:'🛡️ Très fort' },
    ];
    var lv = levels[Math.max(0, score - 1)] || levels[0];
    bar.style.width      = lv.pct;
    bar.style.background = lv.color;
    label.textContent    = lv.text;
    label.style.color    = lv.color;
}

/* ── Activer/désactiver le bouton suppression ── */
function toggleDelBtn() {
    var checked = document.getElementById('delConfirmCheck').checked;
    var btn     = document.getElementById('delBtn');
    btn.disabled              = !checked;
    btn.style.background      = checked ? 'linear-gradient(135deg,#ef4444,#dc2626)' : '#d1d5db';
    btn.style.cursor          = checked ? 'pointer' : 'not-allowed';
    btn.style.boxShadow       = checked ? '0 4px 14px rgba(239,68,68,.4)' : 'none';
}

/* ── Confirmation avant suppression ── */
function confirmDel() {
    var pwd = document.getElementById('delPassword').value.trim();
    if (!pwd) {
        alert('Veuillez saisir votre mot de passe pour confirmer.');
        return false;
    }
    return confirm('Êtes-vous absolument certain de vouloir supprimer votre compte ? Cette action est irréversible.');
}

/* ══════════════════════════════════════════
   AUTO-OUVERTURE MODALE PROFIL (erreurs / status)
══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    @if(session('status') === 'profile-updated')
        openProfileModal('info');
    @endif
    @if($errors->updatePassword->any())
        openProfileModal('pwd');
    @endif
    @if($errors->userDeletion->any())
        openProfileModal('del');
    @endif
});

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

/* ══════════════════════════════════════════
   FILTRE PAR CATÉGORIE (sidebar + pop-cats)
══════════════════════════════════════════ */
/* ── Toggle catégories pop-cats ── */
function togglePopCats(btn) {
    const extras = document.querySelectorAll('.pop-cat-extra');
    const row    = document.getElementById('popCatsRow');
    const open   = extras.length > 0 && extras[0].style.display !== 'none';
    if (open) {
        extras.forEach(el => el.style.display = 'none');
        btn.innerHTML = '＋ ' + btn.dataset.count + ' catégories';
    } else {
        extras.forEach(el => el.style.display = '');
        btn.innerHTML = '✕ Réduire';
        setTimeout(() => row.scrollLeft = row.scrollWidth, 50);
    }
}

function filterByCat(type) {
    /* Trouver la pill correspondante et déléguer à filterByType */
    let pill;
    if (!type) {
        pill = document.querySelector('#catFilter .cat-pill');
    } else {
        pill = document.querySelector(`#catFilter .cat-pill[data-type-val="${type.replace(/"/g,'&quot;')}"]`);
    }
    if (pill) filterByType(type, pill);

    /* Sync états actifs sidebar + pop-cats + pop-cat-chips */
    document.querySelectorAll('.sb-cat-item').forEach(el => {
        el.classList.toggle('active', el.dataset.catType === type);
    });
    document.querySelectorAll('.pop-cat-chip').forEach(el => {
        el.classList.toggle('active', el.dataset.catType === type);
    });
    document.querySelectorAll('.pop-cat-card').forEach(el => {
        el.classList.toggle('active', el.dataset.catType === type);
    });

    /* Scroll vers la grille */
    const boutiques = document.getElementById('boutiques');
    if (boutiques) boutiques.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* Animation d'entrée gérée en CSS (cardFadeIn) — plus performante que JS */
</script>

{{-- ══════════════════════════════════════════════════
     NOTIFICATIONS TEMPS RÉEL — polling client
══════════════════════════════════════════════════ --}}
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    /* ── Map statuts → libellés + style ── */
    const STATUS_MAP = {
        'livrée':        { label:'Livré 🎉',        cls:'pill-success', ico:'🎉', bg:'background:#d1fae5' },
        'en_livraison':  { label:'En livraison 🚴',  cls:'pill-info',    ico:'🚴', bg:'background:#dbeafe' },
        'en livraison':  { label:'En livraison 🚴',  cls:'pill-info',    ico:'🚴', bg:'background:#dbeafe' },
        'annulée':       { label:'Annulée ✕',        cls:'pill-danger',  ico:'✕',  bg:'background:#fee2e2' },
        'cancelled':     { label:'Annulée ✕',        cls:'pill-danger',  ico:'✕',  bg:'background:#fee2e2' },
        'pending':       { label:'En attente ⏳',    cls:'pill-pending', ico:'📦', bg:'background:#fef3c7' },
        'confirmée':     { label:'Confirmée ✓',      cls:'pill-info',    ico:'📦', bg:'background:#dbeafe' },
        'processing':    { label:'En traitement ⚙️', cls:'pill-info',    ico:'📦', bg:'background:#fef3c7' },
    };

    /* ── Toast ── */
    function showClientToast(msg, type) {
        const t = document.createElement('div');
        t.style.cssText = `
            position:fixed;bottom:20px;right:20px;
            background:${type==='order'?'#2c3e50':'#1e40af'};
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
                showClientToast(`💬 <div>${n} nouveau${n>1?'x':''} message${n>1?'s':''} de vendeur</div>`, 'msg');
            }
            _prevMsg = d.messages_unread;

            /* Commandes — mise à jour des statuts */
            (d.orders || []).forEach(order => {
                const prev = _orderStatuses[order.id];
                if (!prev) return;

                if (prev !== order.status) {
                    /* Statut changé → mettre à jour l'UI */
                    _orderStatuses[order.id] = order.status;
                    const info = STATUS_MAP[order.status] || { label: order.status, cls: 'pill-pending', ico:'📦', bg:'background:#fef3c7' };

                    const pill = document.getElementById('oPill' + order.id);
                    if (pill) {
                        pill.className = 'order-pill ' + info.cls;
                        pill.textContent = info.label;
                        pill.style.animation = 'none';
                        requestAnimationFrame(() => { pill.style.animation = 'rtPulse .5s ease'; });
                    }
                    const ico = document.getElementById('oIco' + order.id);
                    if (ico) { ico.style.cssText = ico.style.cssText.replace(/background:[^;]+/, info.bg); ico.textContent = info.ico; }

                    /* Toast */
                    showClientToast(`📦 <div>Commande <strong>#${order.id}</strong> — ${info.label}<br><small style="opacity:.75">${order.shop_name}</small></div>`, 'order');
                }
            });

            /* Boutiques populaires — mise à jour des compteurs */
            (d.popular_shops || []).forEach(shop => {
                const el = document.querySelector(`[data-shop-id="${shop.id}"] .shop-popular-count`);
                if (el) el.textContent = shop.orders_count + ' cmd';
            });

        } catch(e) {}
    }

    /* ── Démarrage : attendre la sync serveur avant le 1er poll ── */
    _serverSyncReady.then(() => {
        pollClientNotifs();
        setInterval(pollClientNotifs, 8000);
    });

    /* ── CSS animations ── */
    const s = document.createElement('style');
    s.textContent = `
        @keyframes rtSlideIn { from{opacity:0;transform:translateX(60px)} to{opacity:1;transform:translateX(0)} }
        @keyframes rtPulse { 0%{transform:scale(1)} 40%{transform:scale(1.12)} 100%{transform:scale(1)} }
    `;
    document.head.appendChild(s);
})();
</script>

{{-- ══════════════════════════════════════════════════
     CLOCHE NOTIFICATIONS CLIENT — polling temps réel
══════════════════════════════════════════════════ --}}
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
                       : isDone ? 'linear-gradient(135deg,#8b5cf6,#7c3aed)'
                                : 'linear-gradient(135deg,#818cf8,#6366f1)';
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
        _alerts = _alerts.filter(a => a.id !== id);
        save(); updateBadge(); render();
    };

    /* ── Ajouter une alerte (messages — groupé par sender) ── */
    function pushMsg(senderId, senderName, shopName, count, time) {
        const txt = count > 1
            ? `${senderName} — ${count} messages non lus`
            : `${senderName} — nouveau message`;
        const sub = shopName ? `📦 ${shopName}` : '💬 Message';
        const existing = _alerts.find(a => a.type === 'msg' && a.senderId === senderId);
        if (existing) { existing.txt = txt; existing.sub = sub; existing.time = time; }
        else { _alerts.unshift({ id: ++_seq, type: 'msg', txt, sub, time, senderId, url: @json(route('client.messages.hub')) }); }
        save();
    }

    /* ── Ajouter une alerte commande ── */
    function pushOrder(orderId, status, shopName, time) {
        const map = {
            'confirmée':    { type: 'order_ok',   txt: `Commande #${orderId} confirmée ✅`, sub: `🏪 ${shopName}` },
            'en_livraison': { type: 'order_del',  txt: `Commande #${orderId} en livraison 🚴`, sub: `🏪 ${shopName}` },
            'livrée':       { type: 'order_done', txt: `Commande #${orderId} livrée ! 🎉`, sub: `🏪 ${shopName}` },
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
@endpush