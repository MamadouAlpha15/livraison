{{--
    resources/views/client/messages/show.blade.php
    Route   : GET /client/products/{product}/messages
    Variables : $product, $shop, $messages
--}}
@extends('layouts.app')
@section('title', 'Chat — ' . $shop->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --orange:    #f90;
    --orange-dk: #e47911;
    --navy:      #131921;
    --navy-2:    #232f3e;
    --blue:      #007185;
    --green:     #067d62;
    --green-lt:  #d1fae5;
    --red:       #dc2626;
    --red-lt:    #fee2e2;
    --yellow:    #f59e0b;
    --yellow-lt: #fef3c7;
    --grey:      #f3f3f3;
    --grey-2:    #eaeded;
    --border:    #ddd;
    --text:      #0f1111;
    --muted:     #565959;
    --surface:   #fff;
    --font:      'Noto Sans', sans-serif;
    --r:         8px;
    --nav-h:     60px;
}
html, body { font-family: var(--font); margin: 0; background: var(--grey); color: var(--text); height: 100%; -webkit-font-smoothing: antialiased; }

/* NAVBAR */
.nav { background: var(--navy); height: var(--nav-h); display: flex; align-items: center; padding: 0 16px; gap: 12px; position: sticky; top: 0; z-index: 100; }
.nav-logo { font-size: 20px; font-weight: 900; color: var(--orange); text-decoration: none; }
.nav-logo span { color: #fff; }
.nav-back { color: rgba(255,255,255,.85); font-size: 13px; font-weight: 600; text-decoration: none; padding: 6px 10px; border: 1px solid transparent; border-radius: 4px; transition: all .15s; }
.nav-back:hover { border-color: rgba(255,255,255,.5); color: #fff; }

/* LAYOUT */
.chat-wrap {
    max-width: 860px; margin: 24px auto; padding: 0 16px 80px;
    display: flex; flex-direction: column; gap: 0;
}

/* EN-TÊTE PRODUIT */
.chat-prod-header {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r) var(--r) 0 0;
    padding: 14px 18px;
    display: flex; align-items: center; gap: 14px;
    border-bottom: 2px solid var(--orange);
}
.chat-prod-img { width: 56px; height: 56px; border-radius: var(--r); object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; background: var(--grey); }
.chat-prod-img-ph { width: 56px; height: 56px; border-radius: var(--r); background: var(--grey-2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
.chat-prod-info { flex: 1; min-width: 0; }
.chat-prod-name { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.chat-prod-price { font-size: 17px; font-weight: 700; color: #b12704; font-family: monospace; }
.chat-prod-shop { font-size: 12px; color: var(--muted); margin-top: 2px; }
.chat-prod-shop a { color: var(--blue); text-decoration: none; }
.chat-prod-shop a:hover { text-decoration: underline; }

/* Bouton proposer un prix */
.btn-propose-price {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 20px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border: 1.5px solid var(--yellow);
    color: #92400e; font-size: 12.5px; font-weight: 700;
    cursor: pointer; transition: all .15s; white-space: nowrap;
    font-family: var(--font);
}
.btn-propose-price:hover { background: linear-gradient(135deg, #fde68a, #fbbf24); transform: scale(1.03); box-shadow: 0 2px 8px rgba(245,158,11,.3); }

/* Panneau proposition prix */
.propose-panel {
    display: none;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    border: 1.5px solid var(--yellow);
    border-radius: 0; border-top: none;
    padding: 14px 18px;
    flex-direction: column; gap: 10px;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
}
.propose-panel.open { display: flex; }
.propose-panel-title { font-size: 13px; font-weight: 700; color: #92400e; display: flex; align-items: center; gap: 6px; }
.propose-panel-sub { font-size: 11.5px; color: #a16207; }
.propose-panel-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.propose-input {
    flex: 1; min-width: 150px;
    padding: 9px 14px; border: 1.5px solid var(--yellow);
    border-radius: 20px; font-size: 14px; font-weight: 600;
    font-family: monospace; color: var(--text); outline: none;
    background: #fff; transition: border-color .15s, box-shadow .15s;
}
.propose-input:focus { border-color: var(--yellow); box-shadow: 0 0 0 3px rgba(245,158,11,.15); }
.propose-devise { font-size: 12px; font-weight: 700; color: #92400e; }
.btn-send-propose {
    padding: 9px 20px; border-radius: 20px;
    background: var(--yellow); color: #fff; border: none;
    font-size: 13px; font-weight: 700; cursor: pointer;
    transition: all .15s; font-family: var(--font);
}
.btn-send-propose:hover { background: #d97706; transform: scale(1.04); }
.btn-cancel-propose {
    padding: 9px 14px; border-radius: 20px;
    background: transparent; color: #92400e;
    border: 1.5px solid var(--yellow); font-size: 12px;
    font-weight: 600; cursor: pointer; font-family: var(--font);
    transition: all .15s;
}
.btn-cancel-propose:hover { background: #fde68a; }

/* THREAD */
.chat-thread {
    background: #f0f2f5;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
    padding: 20px 18px;
    display: flex; flex-direction: column; gap: 14px;
    min-height: 380px; max-height: 520px; overflow-y: auto;
}
.chat-thread::-webkit-scrollbar { width: 5px; }
.chat-thread::-webkit-scrollbar-thumb { background: #c5c5c5; border-radius: 5px; }

/* Messages normaux */
.msg-row { display: flex; gap: 8px; max-width: 75%; }
.msg-row.mine { margin-left: auto; flex-direction: row-reverse; }
.msg-av { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0; align-self: flex-end; }
.msg-row.mine    .msg-av { background: linear-gradient(135deg, var(--orange), var(--orange-dk)); }
.msg-row.theirs  .msg-av { background: linear-gradient(135deg, var(--navy-2), #3d5a73); }
.msg-content { display: flex; flex-direction: column; gap: 3px; }
.msg-bubble { padding: 10px 14px; border-radius: 16px; font-size: 13.5px; line-height: 1.55; word-break: break-word; }
.msg-row.mine   .msg-bubble { background: var(--orange); color: #fff; border-bottom-right-radius: 3px; }
.msg-row.theirs .msg-bubble { background: var(--surface); color: var(--text); border: 1px solid var(--border); border-bottom-left-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,.07); }
.msg-meta { display: flex; align-items: center; gap: 6px; }
.msg-time { font-size: 10.5px; color: var(--muted); }
.msg-row.mine .msg-meta { justify-content: flex-end; }
.msg-read { font-size: 11px; color: var(--green); }
.msg-sender { font-size: 11px; font-weight: 700; color: var(--muted); margin-bottom: 2px; }
.msg-row.mine .msg-sender { text-align: right; }

/* ── CARTE PROPOSITION PRIX (envoyée par le client) ── */
.price-card {
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.1);
    max-width: 300px; width: 100%;
}
.msg-row.mine .price-card { margin-left: auto; }
.price-card-header {
    padding: 10px 14px; font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .5px;
    display: flex; align-items: center; gap: 6px;
}
.price-card.proposal .price-card-header { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
.price-card.offer    .price-card-header { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
.price-card.order-ok .price-card-header { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }
.price-card.counter  .price-card-header { background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #5b21b6; }

.btn-counter-offer {
    display: inline-flex; align-items: center; gap: 7px;
    width: 100%; padding: 10px 16px; margin-top: 8px;
    background: transparent;
    border: 1.5px solid #7c3aed; border-radius: 10px;
    color: #7c3aed; font-size: 13px; font-weight: 700;
    cursor: pointer; transition: all .2s; font-family: var(--font);
    justify-content: center;
}
.btn-counter-offer:hover { background: #ede9fe; }
.price-card-body { background: #fff; padding: 14px; border: 1px solid var(--border); border-top: none; border-radius: 0 0 14px 14px; }
.price-card-amount { font-size: 22px; font-weight: 900; color: #0f1111; font-family: monospace; line-height: 1; margin-bottom: 4px; }
.price-card-original { font-size: 11px; color: var(--muted); text-decoration: line-through; margin-bottom: 2px; }
.price-card-discount { font-size: 11px; font-weight: 700; color: var(--green); background: var(--green-lt); display: inline-flex; padding: 2px 8px; border-radius: 20px; margin-bottom: 10px; }
.price-card-status { font-size: 11.5px; font-weight: 700; padding: 5px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px; margin-top: 4px; }
.status-pending  { background: var(--yellow-lt); color: #92400e; }
.status-accepted { background: var(--green-lt);  color: #065f46; }
.status-refused  { background: var(--red-lt);    color: #991b1b; }

/* Bouton confirmer offre */
.btn-confirm-offer {
    display: inline-flex; align-items: center; gap: 7px;
    width: 100%; padding: 11px 16px; margin-top: 10px;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none; border-radius: 10px;
    color: #fff; font-size: 14px; font-weight: 800;
    cursor: pointer; transition: all .2s; font-family: var(--font);
    justify-content: center;
    box-shadow: 0 4px 14px rgba(16,185,129,.35);
}
.btn-confirm-offer:hover { background: linear-gradient(135deg, #059669, #047857); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(16,185,129,.4); }
.btn-confirm-offer:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.btn-confirm-offer.loading { animation: pulse-btn 1.2s ease-in-out infinite; }
@keyframes pulse-btn { 0%,100%{opacity:1}50%{opacity:.6} }

/* Commande créée */
.order-badge { display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: linear-gradient(135deg, #dbeafe, #eff6ff); border: 1px solid #bfdbfe; border-radius: 10px; margin-top: 8px; }
.order-badge-ico { font-size: 20px; }
.order-badge-text { font-size: 12px; font-weight: 700; color: #1d4ed8; }
.order-badge-link { font-size: 11px; color: #2563eb; text-decoration: none; font-weight: 600; }
.order-badge-link:hover { text-decoration: underline; }

/* Vide */
.chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 40px 20px; }
.chat-empty-ico { font-size: 40px; opacity: .3; }
.chat-empty-txt { font-size: 14px; color: var(--muted); text-align: center; }

/* ZONE DE SAISIE */
.chat-input-zone { background: var(--surface); border: 1px solid var(--border); border-top: 2px solid var(--orange); border-radius: 0 0 var(--r) var(--r); padding: 12px 16px; display: flex; gap: 10px; align-items: flex-end; }
.chat-textarea { flex: 1; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 20px; font-size: 13.5px; font-family: var(--font); color: var(--text); background: var(--grey); outline: none; resize: none; min-height: 40px; max-height: 100px; line-height: 1.5; transition: border-color .15s, background .15s; }
.chat-textarea:focus { border-color: var(--orange); background: var(--surface); box-shadow: 0 0 0 3px rgba(255,153,0,.1); }
.chat-send-btn { width: 42px; height: 42px; border-radius: 50%; background: var(--orange); color: var(--navy); border: none; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .15s; box-shadow: 0 2px 8px rgba(255,153,0,.3); font-weight: 700; }
.chat-send-btn:hover { background: var(--orange-dk); transform: scale(1.07); }

/* Toast notification */
.chat-toast {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(100px);
    background: #0f1111; color: #fff;
    padding: 12px 24px; border-radius: 24px;
    font-size: 13.5px; font-weight: 600;
    box-shadow: 0 8px 30px rgba(0,0,0,.25);
    z-index: 9999; transition: transform .35s cubic-bezier(.23,1,.32,1);
    display: flex; align-items: center; gap: 8px;
}
.chat-toast.show { transform: translateX(-50%) translateY(0); }
.chat-toast.success { background: linear-gradient(135deg, #065f46, #047857); }
.chat-toast.error   { background: linear-gradient(135deg, #991b1b, #b91c1c); }

/* Flash */
.chat-flash { padding: 10px 14px; border-radius: var(--r); border: 1px solid; font-size: 13px; font-weight: 500; margin-bottom: 10px; }
.chat-flash-success { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }

/* ── Grille images dans un message ── */
.msg-img-grid { display: flex; flex-wrap: wrap; gap: 3px; max-width: 260px; }
.msg-img-thumb {
    width: calc(50% - 2px); aspect-ratio: 1/1; object-fit: cover;
    border-radius: 8px; cursor: pointer; transition: opacity .15s;
    border: 1px solid rgba(0,0,0,.06);
}
.msg-img-thumb:hover { opacity: .88; }
.msg-img-grid.count-1 .msg-img-thumb { width: 100%; max-width: 220px; aspect-ratio: 4/3; border-radius: 10px; }
.msg-img-grid.count-3 .msg-img-thumb:first-child { width: 100%; aspect-ratio: 16/9; }

/* traitement en cours */
.msg-img-processing {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 14px; background: rgba(0,0,0,.06);
    border-radius: 10px; min-width: 140px;
    font-size: 12px; color: var(--muted);
}
.msg-img-spinner {
    width: 15px; height: 15px; flex-shrink: 0;
    border: 2px solid var(--border); border-top-color: var(--orange);
    border-radius: 50%; animation: imgSpin .75s linear infinite;
}
@keyframes imgSpin { to { transform: rotate(360deg); } }

/* ── Bouton photo ── */
.chat-photo-btn {
    width: 42px; height: 42px; border-radius: 50%;
    border: 1.5px solid var(--border); background: var(--surface);
    color: var(--muted); cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 19px; transition: all .15s;
}
.chat-photo-btn:hover { border-color: var(--orange); color: var(--orange); background: #fff8f0; }

/* ── Preview avant envoi ── */
.chat-img-preview {
    display: flex; gap: 6px; padding: 8px 14px;
    overflow-x: auto; background: #fff8ef;
    border: 1px solid #fde68a; border-bottom: none;
    border-radius: var(--r) var(--r) 0 0;
    scrollbar-width: none;
}
.chat-img-preview::-webkit-scrollbar { display: none; }
.chat-img-preview.hidden { display: none; }
.chat-img-preview-item { position: relative; flex-shrink: 0; }
.chat-img-preview-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 7px; border: 1px solid var(--border); display: block; }
.chat-img-preview-rm {
    position: absolute; top: -5px; right: -5px;
    width: 17px; height: 17px; border-radius: 50%;
    background: var(--red); color: #fff; font-size: 10px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; border: 1.5px solid #fff; line-height: 1; font-weight: 700;
}

/* ── Lightbox ── */
.img-lightbox {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.93); z-index: 9999;
    align-items: center; justify-content: center;
    user-select: none;
}
.img-lightbox.open { display: flex; }
.img-lightbox-img {
    max-width: min(92vw, 900px); max-height: 85vh;
    object-fit: contain; border-radius: 6px;
    transition: opacity .18s;
}
.img-lightbox-close {
    position: absolute; top: 14px; right: 14px;
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,.15); border: none; color: #fff;
    font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .15s; z-index: 2;
}
.img-lightbox-close:hover { background: rgba(255,255,255,.28); }
.img-lb-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 46px; height: 46px; border-radius: 50%;
    background: rgba(255,255,255,.13); border: none; color: #fff;
    font-size: 28px; font-weight: 300; cursor: pointer; z-index: 2;
    display: none; align-items: center; justify-content: center;
    transition: background .15s;
}
.img-lb-nav:hover { background: rgba(255,255,255,.26); }
.img-lb-nav.visible { display: flex; }
.img-lb-prev { left: 14px; }
.img-lb-next { right: 14px; }
.img-lb-counter {
    position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%);
    background: rgba(0,0,0,.45); color: rgba(255,255,255,.85);
    font-size: 12.5px; font-weight: 600; padding: 4px 14px; border-radius: 20px;
    display: none; z-index: 2;
}
.img-lb-counter.visible { display: block; }
.img-lb-dots {
    position: absolute; bottom: 52px; left: 50%; transform: translateX(-50%);
    display: flex; gap: 5px; z-index: 2;
}
.img-lb-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: rgba(255,255,255,.35); transition: background .15s; cursor: pointer;
}
.img-lb-dot.active { background: #fff; }

/* RESPONSIVE */
@media (max-width: 600px) {
    /* ── Mise en page fixe : empêche iOS de remonter la page au focus ── */
    html, body { overflow: hidden; height: 100%; }
    .chat-wrap {
        position: fixed;
        top: var(--nav-h);
        left: 0; right: 0; bottom: 0;
        margin: 0; padding: 0;
        max-width: 100%;
        display: flex; flex-direction: column;
        overflow: hidden;
    }
    .chat-prod-header { flex-shrink: 0; border-radius: 0; }
    .propose-panel    { flex-shrink: 0; }
    .chat-thread {
        flex: 1; min-height: 0; max-height: none;
        padding: 14px 12px;
        overflow-y: auto; -webkit-overflow-scrolling: touch;
    }
    .chat-img-preview { flex-shrink: 0; }
    .chat-input-zone  { flex-shrink: 0; border-radius: 0; }
    .msg-row { max-width: 90%; }
    .price-card { max-width: 100%; }
    /* iOS anti-zoom : font-size < 16px déclenche un zoom automatique au focus */
    .chat-textarea, .propose-input { font-size: 16px !important; }
}

@media (max-width: 400px) {
    /* Entête produit : image plus petite, bouton pleine largeur */
    .chat-prod-header { flex-wrap: wrap; }
    .chat-prod-img,
    .chat-prod-img-ph { width: 44px; height: 44px; font-size: 18px; }
    .chat-prod-name  { font-size: 13.5px; }
    .chat-prod-price { font-size: 15px; }
    .btn-propose-price { width: 100%; justify-content: center; margin-top: 4px; font-size: 12px; padding: 7px 12px; }

    /* Panel proposition : champs + boutons empilés */
    .propose-panel { padding: 12px 12px; }
    .propose-panel-row { flex-direction: column; align-items: stretch; }
    .propose-input { min-width: 0; width: 100%; }
    .propose-devise { display: none; }
    .btn-send-propose,
    .btn-cancel-propose { width: 100%; text-align: center; justify-content: center; }

    /* Carte prix : montant plus lisible */
    .price-card-amount { font-size: 18px; }
    .price-card-body   { padding: 10px; }

    /* Thread */
    .chat-thread { padding: 10px 8px; }
    .chat-input-zone { padding: 8px 10px; }
    .chat-textarea { font-size: 16px !important; }
}
</style>
@endpush

@section('content')
@php
    $devise   = $shop->currency ?? 'GNF';
    $client   = auth()->user();
    $parts    = explode(' ', $client->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
@endphp

{{-- NAVBAR --}}
<nav class="nav">
    <a href="{{ route('client.dashboard') }}" class="nav-logo">Ma<span>Boutique</span></a>
    <a href="{{ route('client.shops.show', $shop) }}" class="nav-back">← {{ Str::limit($shop->name, 20) }}</a>
</nav>

<div class="chat-wrap">

    @if(session('chat_sent'))
    <div class="chat-flash chat-flash-success">✓ Message envoyé.</div>
    @endif

    {{-- EN-TÊTE PRODUIT --}}
    <div class="chat-prod-header">
        @if($product->image)
            <img src="{{ asset('storage/'.$product->image) }}" class="chat-prod-img" alt="{{ $product->name }}">
        @else
            <div class="chat-prod-img-ph">🏷️</div>
        @endif
        <div class="chat-prod-info">
            <div class="chat-prod-name">{{ $product->name }}</div>
            <div class="chat-prod-price">{{ number_format($product->price, 0, ',', ' ') }} {{ $devise }}</div>
            <div class="chat-prod-shop">
                Boutique : <a href="{{ route('client.shops.show', $shop) }}">{{ $shop->name }}</a>
            </div>
        </div>
        <button class="btn-propose-price" onclick="toggleProposePanel()" type="button" id="btnPropose">
            💰 Proposer un prix
        </button>
    </div>

    {{-- PANNEAU CONTRE-OFFRE CLIENT --}}
    <div class="propose-panel" id="counterPanel">
        <div class="propose-panel-title">🔄 Votre contre-offre</div>
        <div class="propose-panel-sub">
            Proposez un nouveau prix au vendeur.
        </div>
        <div class="propose-panel-row">
            <input type="number"
                   id="counterPriceInput"
                   class="propose-input"
                   placeholder="Votre prix…"
                   min="1"
                   step="500">
            <span class="propose-devise">{{ $devise }}</span>
            <button class="btn-send-propose" onclick="sendCounterOffer()" type="button">
                Envoyer
            </button>
            <button class="btn-cancel-propose" onclick="closeCounterPanel()" type="button">Annuler</button>
        </div>
    </div>

    {{-- PANNEAU PROPOSITION DE PRIX --}}
    <div class="propose-panel" id="proposePanel">
        <div class="propose-panel-title">💰 Faire une proposition de prix</div>
        <div class="propose-panel-sub">
            Prix actuel : <strong>{{ number_format($product->price, 0, ',', ' ') }} {{ $devise }}</strong> · Proposez votre prix, le vendeur pourra accepter ou refuser.
        </div>
        <div class="propose-panel-row">
            <input type="number"
                   id="proposePriceInput"
                   class="propose-input"
                   placeholder="Votre prix…"
                   min="1"
                   step="500"
                   value="{{ old('proposed_price', floor($product->price * 0.9)) }}">
            <span class="propose-devise">{{ $devise }}</span>
            <button class="btn-send-propose" onclick="sendPriceProposal()" type="button">
                Envoyer la proposition
            </button>
            <button class="btn-cancel-propose" onclick="toggleProposePanel()" type="button">Annuler</button>
        </div>
    </div>

    {{-- THREAD MESSAGES --}}
    <div class="chat-thread" id="chatThread">
        @forelse($messages as $msg)
        @php
            $isMine     = $msg->sender_id === $client->id;
            $senderName = $msg->sender->name ?? 'Inconnu';
            $sParts     = explode(' ', $senderName);
            $sInit      = strtoupper(substr($sParts[0],0,1)) . strtoupper(substr($sParts[1] ?? 'X',0,1));
            $msgType    = $msg->type ?? 'text';
        @endphp

        @if($msgType === 'images')
            {{-- ══ PHOTOS ══ --}}
            @php
                $imgUrls = collect($msg->images ?? [])->map(fn($p) =>
                    \App\Services\ImageOptimizer::url($p, 'medium') ?? asset('storage/'.$p)
                )->toArray();
                $imgCount = count($imgUrls);
            @endphp
            <div class="msg-row {{ $isMine ? 'mine' : 'theirs' }}" data-msg-id="{{ $msg->id }}">
                @if(!$isMine)<div class="msg-av">{{ $sInit }}</div>@endif
                <div class="msg-content">
                    @if(!$isMine)<div class="msg-sender">{{ $senderName }}</div>@endif
                    <div class="msg-img-grid count-{{ $imgCount }}"
                         id="imgGrid_{{ $msg->id }}"
                         data-status="{{ $msg->image_status ?? 'ready' }}">
                        @if(($msg->image_status ?? 'ready') === 'processing' || $imgCount === 0)
                            <div class="msg-img-processing">
                                <div class="msg-img-spinner"></div>
                                <span>Traitement en cours…</span>
                            </div>
                        @else
                            @foreach($imgUrls as $i => $imgUrl)
                            <img src="{{ $imgUrl }}" class="msg-img-thumb"
                                 onclick="openLightbox({{ json_encode($imgUrls) }}, {{ $i }})"
                                 alt="Photo {{ $i + 1 }}">
                            @endforeach
                        @endif
                    </div>
                    <div class="msg-meta">
                        <span class="msg-time">{{ $msg->created_at->format('d/m H:i') }}</span>
                        @if($isMine && $msg->read_at)<span class="msg-read">✓✓</span>@endif
                    </div>
                </div>
            </div>

        @elseif(in_array($msgType, ['price_proposal', 'price_offer', 'price_counter', 'order_created']))
            {{-- ══ CARTE NÉGOCIATION ══ --}}
            <div class="msg-row {{ $isMine ? 'mine' : 'theirs' }}" data-msg-id="{{ $msg->id }}">
                <div class="msg-av">{{ $isMine ? $initials : $sInit }}</div>
                <div class="msg-content">
                    <div class="msg-sender">{{ $isMine ? 'Vous' : $senderName }}</div>

                    @if($msgType === 'price_proposal')
                    {{-- Proposition de prix (envoyée par le client) --}}
                    <div class="price-card proposal">
                        <div class="price-card-header">💰 Proposition de prix</div>
                        <div class="price-card-body">
                            <div class="price-card-amount">{{ number_format($msg->proposed_price, 0, ',', ' ') }} {{ $devise }}</div>
                            <div class="price-card-original">Prix original : {{ number_format($product->price, 0, ',', ' ') }} {{ $devise }}</div>
                            @php $disc = round((1 - $msg->proposed_price / $product->price) * 100); @endphp
                            @if($disc > 0)
                            <div class="price-card-discount">-{{ $disc }}%</div>
                            @endif
                            @if($msg->proposal_status === 'pending')
                                <div class="price-card-status status-pending">⏳ En attente de réponse…</div>
                            @elseif($msg->proposal_status === 'accepted')
                                <div class="price-card-status status-accepted">✓ Proposition acceptée</div>
                            @elseif($msg->proposal_status === 'refused')
                                <div class="price-card-status status-refused">✕ Proposition refusée</div>
                            @endif
                        </div>
                    </div>

                    @elseif($msgType === 'price_offer')
                    {{-- Offre du vendeur (reçue par le client) --}}
                    <div class="price-card offer">
                        <div class="price-card-header">🎉 Offre spéciale du vendeur</div>
                        <div class="price-card-body">
                            <div class="price-card-amount">{{ number_format($msg->proposed_price, 0, ',', ' ') }} {{ $devise }}</div>
                            <div class="price-card-original">Prix original : {{ number_format($product->price, 0, ',', ' ') }} {{ $devise }}</div>
                            @php $disc2 = round((1 - $msg->proposed_price / $product->price) * 100); @endphp
                            @if($disc2 > 0)
                            <div class="price-card-discount">Vous économisez {{ $disc2 }}% !</div>
                            @endif
                            @if($msg->proposal_status === 'pending' && !$isMine)
                                <button class="btn-confirm-offer"
                                        id="confirmBtn_{{ $msg->id }}"
                                        onclick="confirmOffer({{ $msg->id }}, this)">
                                    ✓ Confirmer cette offre et commander
                                </button>
                            @elseif($msg->proposal_status === 'accepted')
                                <div class="price-card-status status-accepted">✓ Offre confirmée — commande créée</div>
                                @if($msg->negotiated_order_id)
                                <div class="order-badge">
                                    <span class="order-badge-ico">📦</span>
                                    <div>
                                        <div class="order-badge-text">Commande n°{{ $msg->negotiated_order_id }}</div>
                                        <a href="{{ route('client.orders.index') }}" class="order-badge-link">Voir mes commandes →</a>
                                    </div>
                                </div>
                                @endif
                            @elseif($msg->proposal_status === 'refused')
                                <div class="price-card-status status-refused">✕ Offre expirée</div>
                            @elseif($isMine)
                                <div class="price-card-status status-pending">⏳ En attente de confirmation client…</div>
                            @endif
                        </div>
                    </div>

                    @elseif($msgType === 'price_counter')
                    {{-- Contre-offre vendeur ou client --}}
                    <div class="price-card counter">
                        <div class="price-card-header">🔄 Contre-proposition {{ $isMine ? 'envoyée' : 'du vendeur' }}</div>
                        <div class="price-card-body">
                            <div class="price-card-amount">{{ number_format($msg->proposed_price, 0, ',', ' ') }} {{ $devise }}</div>
                            <div class="price-card-original">Prix original : {{ number_format($product->price, 0, ',', ' ') }} {{ $devise }}</div>
                            @php $discC = round((1 - $msg->proposed_price / $product->price) * 100); @endphp
                            @if($discC > 0)
                            <div class="price-card-discount">-{{ $discC }}%</div>
                            @endif

                            @if($msg->proposal_status === 'pending' && !$isMine)
                                <button class="btn-confirm-offer"
                                        id="confirmBtn_{{ $msg->id }}"
                                        onclick="confirmOffer({{ $msg->id }}, this)">
                                    ✓ Accepter cette contre-offre
                                </button>
                                <button class="btn-counter-offer"
                                        onclick="openCounterPanel({{ $msg->id }}, {{ $msg->proposed_price }})">
                                    🔄 Faire une contre-offre
                                </button>
                            @elseif($msg->proposal_status === 'pending' && $isMine)
                                <div class="price-card-status status-pending">⏳ En attente de réponse…</div>
                            @elseif($msg->proposal_status === 'accepted')
                                <div class="price-card-status status-accepted">✓ Acceptée — commande créée</div>
                            @elseif($msg->proposal_status === 'refused')
                                <div class="price-card-status status-refused">✕ Remplacée par une nouvelle offre</div>
                            @endif
                        </div>
                    </div>

                    @elseif($msgType === 'order_created')
                    {{-- Confirmation de commande --}}
                    <div class="price-card order-ok">
                        <div class="price-card-header">📦 Commande confirmée !</div>
                        <div class="price-card-body">
                            <div class="price-card-amount">{{ number_format($msg->proposed_price, 0, ',', ' ') }} {{ $devise }}</div>
                            <div class="price-card-status status-accepted">✅ Commande n°{{ $msg->negotiated_order_id }} créée</div>
                            <div class="order-badge" style="margin-top:10px">
                                <span class="order-badge-ico">📦</span>
                                <div>
                                    <div class="order-badge-text">Prix négocié appliqué</div>
                                    <a href="{{ route('client.orders.index') }}" class="order-badge-link">Voir mes commandes →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="msg-meta">
                        <span class="msg-time">{{ $msg->created_at->format('d/m H:i') }}</span>
                        @if($isMine && $msg->read_at)<span class="msg-read">✓✓</span>@endif
                    </div>
                </div>
            </div>

        @else
            {{-- ══ MESSAGE TEXTE NORMAL ══ --}}
            <div class="msg-row {{ $isMine ? 'mine' : 'theirs' }}" data-msg-id="{{ $msg->id }}">
                @if(!$isMine)<div class="msg-av">{{ $sInit }}</div>@endif
                <div class="msg-content">
                    @if(!$isMine)<div class="msg-sender">{{ $senderName }}</div>@endif
                    <div class="msg-bubble">{{ $msg->body }}</div>
                    <div class="msg-meta">
                        <span class="msg-time">{{ $msg->created_at->format('d/m H:i') }}</span>
                        @if($isMine && $msg->read_at)<span class="msg-read">✓✓</span>@endif
                    </div>
                </div>
            </div>
        @endif

        @empty
        <div class="chat-empty">
            <div class="chat-empty-ico">💬</div>
            <div class="chat-empty-txt">
                Aucun message pour le moment.<br>
                Posez votre première question au vendeur !
            </div>
        </div>
        @endforelse
    </div>

    {{-- ZONE DE SAISIE --}}
    <input type="file" id="photoFileInput" accept="image/*" multiple
           style="display:none" onchange="onFilesSelected(event)">
    <div class="chat-img-preview hidden" id="imgPreviewStrip"></div>
    <div class="chat-input-zone">
        <button type="button" class="chat-photo-btn" onclick="document.getElementById('photoFileInput').click()" title="Envoyer des photos">
            📷
        </button>
        <form method="POST"
              action="{{ route('client.messages.store', $product) }}"
              id="chatForm"
              style="display:flex;gap:10px;align-items:flex-end;flex:1">
            @csrf
            <textarea name="body"
                      id="chatInput"
                      class="chat-textarea"
                      placeholder="Écrire un message ou envoyer une photo…"
                      rows="1"
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();handleSendBtn()}"
                      oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
            <button type="button" onclick="handleSendBtn()" class="chat-send-btn" title="Envoyer">➤</button>
        </form>
    </div>

</div>

{{-- Toast global --}}
<div class="chat-toast" id="chatToast"></div>

{{-- Lightbox --}}
<div class="img-lightbox" id="imgLightbox" onclick="closeLightbox()">
    <button class="img-lightbox-close" onclick="closeLightbox()">✕</button>
    <button class="img-lb-nav img-lb-prev" id="lbPrevBtn" onclick="event.stopPropagation();lbPrev()">&#8249;</button>
    <img class="img-lightbox-img" id="lbImg" src="" alt="" onclick="event.stopPropagation()">
    <button class="img-lb-nav img-lb-next" id="lbNextBtn" onclick="event.stopPropagation();lbNext()">&#8250;</button>
    <div class="img-lb-dots" id="lbDots"></div>
    <div class="img-lb-counter" id="lbCounter"></div>
</div>

@endsection

@push('scripts')
<script>
const CSRF              = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const DEVISE            = '{{ $devise }}';
const INITIALS          = '{{ $initials }}';
const STORE_URL         = '{{ route("client.messages.store", $product) }}';
const POLL_URL          = '{{ route("client.messages.index", $product) }}';
const CONFIRM_URL       = '{{ url("client/messages/confirm-offer") }}';
const PROPOSE_URL       = '{{ route("client.messages.propose") }}';
const COUNTER_OFFER_URL = '{{ route("client.messages.counter-offer") }}';
const SEND_IMAGES_URL   = '{{ route("client.messages.client.send-images", $product) }}';
const IMAGE_STATUS_URL  = '{{ url("client/messages/image-status") }}';
const PRODUCT_ID        = {{ $product->id }};
const PRODUCT_PRICE     = {{ $product->price }};

let _counterMsgId = 0;

function openCounterPanel(msgId, suggestedPrice) {
    _counterMsgId = msgId;
    const input = document.getElementById('counterPriceInput');
    if (input) input.value = suggestedPrice;
    document.getElementById('counterPanel').classList.add('open');
    document.getElementById('proposePanel').classList.remove('open');
    input?.focus();
}

function closeCounterPanel() {
    document.getElementById('counterPanel').classList.remove('open');
    _counterMsgId = 0;
}

async function sendCounterOffer() {
    const input = document.getElementById('counterPriceInput');
    const price = parseFloat(input.value);
    if (!price || price < 1) { showToast('❌ Veuillez entrer un prix valide.', 'error'); return; }
    try {
        const res = await fetch(COUNTER_OFFER_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ message_id: _counterMsgId, counter_price: price }),
        });
        if (!res.ok) throw new Error();
        showToast('🔄 Contre-offre envoyée au vendeur !', 'success');
        closeCounterPanel();
        setTimeout(() => location.reload(), 1200);
    } catch(e) { showToast('❌ Erreur lors de l\'envoi. Réessayez.', 'error'); }
}

let _lastMsgId     = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};
let _selectedFiles = [];   // photos en attente d'envoi

/* ════════════════════════════════════
   UTILITAIRES
   ════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    scrollBottom();
    /* Lancer le polling image-status pour les messages "processing" déjà en page */
    document.querySelectorAll('.msg-img-grid[data-status="processing"]').forEach(grid => {
        const row = grid.closest('[data-msg-id]');
        if (row) pollImageStatus(parseInt(row.dataset.msgId), grid);
    });
});

function scrollBottom() {
    const t = document.getElementById('chatThread');
    if (t) t.scrollTop = t.scrollHeight;
}

function showToast(msg, type = 'success') {
    const el = document.getElementById('chatToast');
    el.textContent = msg;
    el.className = 'chat-toast ' + type + ' show';
    setTimeout(() => el.classList.remove('show'), 3500);
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function nowTime() {
    const d = new Date();
    return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
}

/* ════════════════════════════════════
   LIGHTBOX avec navigation galerie
   ════════════════════════════════════ */
let _lbImages = [];
let _lbIndex  = 0;
let _lbTouchX = 0;

function openLightbox(images, index = 0) {
    if (typeof images === 'string') images = [images];
    _lbImages = images;
    _lbIndex  = index;
    _renderLightbox();
    document.getElementById('imgLightbox').classList.add('open');
}

function _renderLightbox() {
    const total = _lbImages.length;
    document.getElementById('lbImg').src = _lbImages[_lbIndex];

    /* flèches */
    const show = total > 1;
    document.getElementById('lbPrevBtn').classList.toggle('visible', show);
    document.getElementById('lbNextBtn').classList.toggle('visible', show);

    /* compteur */
    const counter = document.getElementById('lbCounter');
    counter.textContent = total > 1 ? (_lbIndex + 1) + ' / ' + total : '';
    counter.classList.toggle('visible', total > 1);

    /* dots */
    const dotsEl = document.getElementById('lbDots');
    dotsEl.innerHTML = '';
    if (total > 1 && total <= 10) {
        _lbImages.forEach((_, i) => {
            const d = document.createElement('div');
            d.className = 'img-lb-dot' + (i === _lbIndex ? ' active' : '');
            d.onclick = e => { e.stopPropagation(); _lbIndex = i; _renderLightbox(); };
            dotsEl.appendChild(d);
        });
    }
}

function lbPrev() { _lbIndex = (_lbIndex - 1 + _lbImages.length) % _lbImages.length; _renderLightbox(); }
function lbNext() { _lbIndex = (_lbIndex + 1) % _lbImages.length; _renderLightbox(); }

function closeLightbox() {
    document.getElementById('imgLightbox').classList.remove('open');
}

/* clavier */
document.addEventListener('keydown', e => {
    const open = document.getElementById('imgLightbox').classList.contains('open');
    if (!open) return;
    if (e.key === 'Escape')      closeLightbox();
    if (e.key === 'ArrowLeft')   lbPrev();
    if (e.key === 'ArrowRight')  lbNext();
});

/* swipe mobile */
document.getElementById('imgLightbox').addEventListener('touchstart', e => {
    _lbTouchX = e.touches[0].clientX;
}, { passive: true });
document.getElementById('imgLightbox').addEventListener('touchend', e => {
    const dx = e.changedTouches[0].clientX - _lbTouchX;
    if (Math.abs(dx) > 50) { dx < 0 ? lbNext() : lbPrev(); }
}, { passive: true });

/* ════════════════════════════════════
   SÉLECTION & PREVIEW PHOTOS
   ════════════════════════════════════ */
function onFilesSelected(e) {
    const files = Array.from(e.target.files).slice(0, 20 - _selectedFiles.length);
    _selectedFiles = [..._selectedFiles, ...files];
    renderPreview();
    e.target.value = '';
}

function renderPreview() {
    const strip = document.getElementById('imgPreviewStrip');
    strip.innerHTML = '';
    if (!_selectedFiles.length) { strip.classList.add('hidden'); return; }
    strip.classList.remove('hidden');
    _selectedFiles.forEach((file, idx) => {
        const item = document.createElement('div');
        item.className = 'chat-img-preview-item';
        const img = document.createElement('img');
        img.className = 'chat-img-preview-thumb';
        img.src = URL.createObjectURL(file);
        const rm = document.createElement('div');
        rm.className = 'chat-img-preview-rm';
        rm.textContent = '×';
        rm.onclick = () => { _selectedFiles.splice(idx, 1); renderPreview(); };
        item.appendChild(img); item.appendChild(rm);
        strip.appendChild(item);
    });
}

/* ════════════════════════════════════
   CONSTRUIRE LES LIGNES DE MESSAGE
   ════════════════════════════════════ */
function buildTextRow(msg) {
    const row = document.createElement('div');
    row.className = 'msg-row ' + (msg.mine ? 'mine' : 'theirs');
    if (msg.id) row.dataset.msgId = msg.id;
    const tick = msg.mine ? (msg.read ? '<span class="msg-read">✓✓</span>' : '') : '';
    if (msg.mine) {
        row.innerHTML =
            '<div class="msg-content">' +
                '<div class="msg-bubble">' + escHtml(msg.body) + '</div>' +
                '<div class="msg-meta"><span class="msg-time">' + escHtml(msg.time) + '</span>' + tick + '</div>' +
            '</div>';
    } else {
        row.innerHTML =
            '<div class="msg-av">' + escHtml(msg.sender_init || '?') + '</div>' +
            '<div class="msg-content">' +
                '<div class="msg-sender">' + escHtml(msg.sender) + '</div>' +
                '<div class="msg-bubble">' + escHtml(msg.body) + '</div>' +
                '<div class="msg-meta"><span class="msg-time">' + escHtml(msg.time) + '</span></div>' +
            '</div>';
    }
    return row;
}

function buildImageRow(msg) {
    const row = document.createElement('div');
    row.className = 'msg-row ' + (msg.mine ? 'mine' : 'theirs');
    if (msg.id) row.dataset.msgId = msg.id;

    const imgs  = msg.images || [];
    const grid  = document.createElement('div');
    grid.className   = 'msg-img-grid count-' + imgs.length;
    grid.id          = 'imgGrid_' + (msg.id || ('tmp_' + Date.now()));
    grid.dataset.status = msg.image_status || 'processing';

    if (msg.image_status === 'processing' || !imgs.length) {
        grid.innerHTML = '<div class="msg-img-processing"><div class="msg-img-spinner"></div><span>Traitement en cours…</span></div>';
    } else {
        imgs.forEach((url, idx) => {
            const im = document.createElement('img');
            im.src = url; im.className = 'msg-img-thumb';
            im.onclick = () => openLightbox(imgs, idx);
            grid.appendChild(im);
        });
    }

    const meta    = document.createElement('div');
    meta.className = 'msg-meta';
    meta.innerHTML = '<span class="msg-time">' + escHtml(msg.time || nowTime()) + '</span>';

    const content    = document.createElement('div');
    content.className = 'msg-content';
    content.appendChild(grid);
    content.appendChild(meta);

    if (!msg.mine) {
        const av = document.createElement('div');
        av.className = 'msg-av';
        av.textContent = msg.sender_init || '?';
        row.appendChild(av);
    }
    row.appendChild(content);
    return row;
}

/* ════════════════════════════════════
   ENVOI
   ════════════════════════════════════ */
async function handleSendBtn() {
    if (_selectedFiles.length) await sendPhotos();
    const text = document.getElementById('chatInput').value.trim();
    if (text) await sendMessage();
}

async function sendMessage() {
    const input = document.getElementById('chatInput');
    const body  = input.value.trim();
    if (!body) return;
    const btn = document.querySelector('.chat-send-btn');
    if (btn) btn.disabled = true;
    try {
        const res  = await fetch(STORE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ body }),
        });
        const data = await res.json();
        if (data.sent) {
            input.value = ''; input.style.height = 'auto';
            if (data.message_id) _lastMsgId = Math.max(_lastMsgId, data.message_id);
            const thread = document.getElementById('chatThread');
            thread.querySelector('.chat-empty')?.remove();
            thread.appendChild(buildTextRow({ mine: true, body, time: nowTime(), read: false, id: data.message_id || 0 }));
            scrollBottom();
        }
    } catch(e) {
        showToast('❌ Erreur réseau. Réessayez.', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

async function sendPhotos() {
    if (!_selectedFiles.length) return;

    // Créer les blob URLs AVANT de vider la sélection → images visibles immédiatement
    const blobUrls = _selectedFiles.map(f => URL.createObjectURL(f));

    const formData = new FormData();
    _selectedFiles.forEach(f => formData.append('images[]', f));
    _selectedFiles = [];
    renderPreview();

    const thread = document.getElementById('chatThread');
    thread.querySelector('.chat-empty')?.remove();
    // Afficher les images tout de suite (blob local, pas de spinner)
    const row = buildImageRow({ mine: true, images: blobUrls, image_status: 'ready', time: nowTime() });
    thread.appendChild(row);
    scrollBottom();

    try {
        const res  = await fetch(SEND_IMAGES_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });
        const data = await res.json();
        if (data.success && data.message_id) {
            row.dataset.msgId = data.message_id;
            _lastMsgId = Math.max(_lastMsgId, data.message_id);
            if (data.image_status === 'ready' && data.images?.length > 0) {
                // Images déjà optimisées → remplacer les blobs immédiatement
                const gridEl = row.querySelector('.msg-img-grid');
                if (gridEl) {
                    gridEl.innerHTML = '';
                    gridEl.className = 'msg-img-grid count-' + data.images.length;
                    data.images.forEach((url, idx) => {
                        const im = document.createElement('img');
                        im.src = url; im.className = 'msg-img-thumb';
                        im.onclick = () => openLightbox(data.images, idx);
                        gridEl.appendChild(im);
                    });
                    gridEl.dataset.status = 'ready';
                }
            } else {
                // Fallback : polling si le traitement est encore en cours
                pollImageStatus(data.message_id, row.querySelector('.msg-img-grid'));
            }
        }
    } catch(e) {
        row.remove();
        showToast('❌ Erreur envoi photo. Réessayez.', 'error');
    }
}

/* ════════════════════════════════════
   POLLING IMAGE STATUS
   ════════════════════════════════════ */
function pollImageStatus(msgId, gridEl) {
    if (!gridEl) return;
    const check = async () => {
        try {
            const res  = await fetch(IMAGE_STATUS_URL + '/' + msgId, {
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.status === 'ready' && data.images?.length) {
                gridEl.innerHTML = '';
                gridEl.className = 'msg-img-grid count-' + data.images.length;
                data.images.forEach((url, idx) => {
                    const im = document.createElement('img');
                    im.src = url; im.className = 'msg-img-thumb';
                    im.onclick = () => openLightbox(data.images, idx);
                    gridEl.appendChild(im);
                });
                gridEl.dataset.status = 'ready';
            } else {
                setTimeout(check, 2500);
            }
        } catch(e) { setTimeout(check, 3000); }
    };
    setTimeout(check, 2000);
}

/* ════════════════════════════════════
   PROPOSITION DE PRIX
   ════════════════════════════════════ */
function toggleProposePanel() {
    const panel = document.getElementById('proposePanel');
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) document.getElementById('proposePriceInput').focus();
}

async function sendPriceProposal() {
    const input = document.getElementById('proposePriceInput');
    const price = parseFloat(input.value);
    if (!price || price < 1) { showToast('❌ Veuillez entrer un prix valide.', 'error'); return; }
    try {
        const res = await fetch(PROPOSE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ product_id: PRODUCT_ID, proposed_price: price }),
        });
        if (!res.ok) throw new Error();
        showToast('💰 Proposition envoyée au vendeur !', 'success');
        document.getElementById('proposePanel').classList.remove('open');
        input.value = '';
        setTimeout(() => location.reload(), 1200);
    } catch(e) { showToast('❌ Erreur lors de l\'envoi. Réessayez.', 'error'); }
}

/* ════════════════════════════════════
   CONFIRMER OFFRE VENDEUR
   ════════════════════════════════════ */
async function confirmOffer(messageId, btn) {
    if (!confirm('Confirmer cette offre et créer la commande ?')) return;
    btn.disabled = true; btn.classList.add('loading');
    btn.textContent = '⏳ Création de la commande…';
    try {
        const res  = await fetch(CONFIRM_URL + '/' + messageId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            showToast('🎉 Commande n°' + data.order_id + ' créée avec succès !', 'success');
            setTimeout(() => location.reload(), 1500);
        } else throw new Error();
    } catch(e) {
        btn.disabled = false; btn.classList.remove('loading');
        btn.textContent = '✓ Confirmer cette offre et commander';
        showToast('❌ Erreur. Réessayez.', 'error');
    }
}

/* ════════════════════════════════════
   POLLING MESSAGES (toutes les 3s)
   ════════════════════════════════════ */
async function pollMessages() {
    try {
        const res = await fetch(`${POLL_URL}?poll=1&_t=${Date.now()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!res.ok) return;
        const msgs = await res.json();

        const thread  = document.getElementById('chatThread');
        const newMsgs = msgs.filter(m => m.id > _lastMsgId);

        for (const msg of newMsgs) {
            if (thread.querySelector('[data-msg-id="' + msg.id + '"]')) continue;

            /* Photos du vendeur → on les affiche directement */
            if (msg.type === 'images') {
                thread.querySelector('.chat-empty')?.remove();
                const parts = (msg.sender || '').split(' ');
                msg.sender_init = (parts[0]?.[0] ?? '?').toUpperCase() + (parts[1]?.[0] ?? '').toUpperCase();
                const row = buildImageRow({ ...msg, mine: false });
                thread.appendChild(row);
                scrollBottom();
                if (msg.image_status !== 'ready' || !msg.images?.length) {
                    pollImageStatus(msg.id, row.querySelector('.msg-img-grid'));
                } else {
                    /* images déjà prêtes : brancher le lightbox */
                    row.querySelectorAll('.msg-img-thumb').forEach((im, idx) => {
                        im.onclick = () => openLightbox(msg.images, idx);
                    });
                }
                _lastMsgId = Math.max(_lastMsgId, msg.id);
                continue;
            }

            /* Cartes de négociation → reload */
            if (msg.type && msg.type !== 'text') {
                location.reload(); return;
            }

            /* Texte du vendeur */
            if (!msg.mine) {
                thread.querySelector('.chat-empty')?.remove();
                const parts = (msg.sender || '').split(' ');
                msg.sender_init = (parts[0]?.[0] ?? '?').toUpperCase() + (parts[1]?.[0] ?? '').toUpperCase();
                thread.appendChild(buildTextRow(msg));
                scrollBottom();
            }
            _lastMsgId = Math.max(_lastMsgId, msg.id);
        }
    } catch(e) {}
}

setInterval(pollMessages, 3000);

/* ── Polling badge navbar ── */
async function pollBadge() {
    try {
        const res = await fetch(`{{ route("client.messages.client.poll") }}?_t=${Date.now()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();
        if (typeof data.unread === 'undefined') return;
        const badge = document.getElementById('navMsgBadge');
        if (!badge) return;
        if (data.unread > 0) { badge.textContent = data.unread; badge.classList.add('show'); }
        else { badge.textContent = ''; badge.classList.remove('show'); }
    } catch(e) {}
}
setInterval(pollBadge, 3000);

/* ── Empêche iOS de remonter la page quand le clavier s'ouvre ── */
(function () {
    const wrap = document.querySelector('.chat-wrap');
    if (!wrap || !window.visualViewport) return;
    function adjust() {
        if (window.innerWidth > 600) { wrap.style.top = ''; wrap.style.height = ''; return; }
        const vv = window.visualViewport;
        wrap.style.top    = (vv.offsetTop + 60) + 'px';
        wrap.style.height = (vv.height  - 60) + 'px';
    }
    window.visualViewport.addEventListener('resize', adjust);
    window.visualViewport.addEventListener('scroll', adjust);
})();

/* ── Scroll en bas du thread quand le clavier s'ouvre ── */
document.getElementById('chatInput')?.addEventListener('focus', function () {
    const thread = document.getElementById('chatThread');
    setTimeout(() => { if (thread) thread.scrollTop = thread.scrollHeight; }, 300);
});
</script>
@endpush
