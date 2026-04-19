{{-- resources/views/client/messages/hub.blade.php --}}
@extends('layouts.app')
@section('title', 'Mes messages')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --orange:#f90; --orange-dk:#e47911; --navy:#131921; --navy-2:#232f3e;
    --blue:#007185; --green:#067d62; --green-lt:#d1fae5; --yellow:#f59e0b;
    --border:#e9edef; --text:#111b21; --muted:#667781; --surface:#fff; --bg:#f0f2f5;
    --font:'Segoe UI',sans-serif; --nav-h:60px; --sidebar-w:360px;
}
body { margin:0; font-family:var(--font); background:var(--bg); color:var(--text); }
.is-dashboard { overflow:hidden; }
.hub { display:flex; height:calc(100vh - var(--nav-h)); overflow:hidden; }

/* ─── SIDEBAR ─── */
.hub-sidebar { width:var(--sidebar-w); flex-shrink:0; display:flex; flex-direction:column; background:var(--surface); border-right:1px solid var(--border); }
.hub-sidebar-head { padding:14px 16px 10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:10px; background:#f0f2f5; }
.hub-sidebar-title { font-size:19px; font-weight:700; color:var(--text); flex:1; }
.hub-back-dash { background:var(--orange); color:var(--navy); border:none; border-radius:50%; width:34px; height:34px; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center; text-decoration:none; font-weight:700; flex-shrink:0; transition:background .15s,transform .1s; }
.hub-back-dash:hover { background:var(--orange-dk); transform:scale(1.08); }
.hub-search-wrap { padding:8px 10px; background:var(--surface); border-bottom:1px solid var(--border); }
.hub-search { width:100%; padding:9px 14px; border-radius:8px; border:none; background:#f0f2f5; font-size:13.5px; color:var(--text); outline:none; }
.hub-search::placeholder { color:var(--muted); }
.hub-conv-list { flex:1; overflow-y:auto; }
.hub-conv-list::-webkit-scrollbar { width:4px; }
.hub-conv-list::-webkit-scrollbar-thumb { background:#ccc; border-radius:4px; }

.hub-conv-item { display:flex; align-items:center; gap:12px; padding:11px 16px; cursor:pointer; border-bottom:1px solid #f5f6f6; transition:background .12s; }
.hub-conv-item:hover { background:#f5f6f6; }
.hub-conv-item.active { background:#f0f2f5; }
.hub-conv-av { width:50px; height:50px; border-radius:50%; background:linear-gradient(135deg,var(--navy-2),#3d5a73); color:#fff; font-size:15px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden; }
.hub-conv-av img { width:100%; height:100%; object-fit:cover; }
.hub-conv-body { flex:1; min-width:0; }
.hub-conv-name { font-size:14px; font-weight:600; color:var(--text); margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hub-conv-preview { font-size:12.5px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hub-conv-meta { display:flex; flex-direction:column; align-items:flex-end; gap:5px; flex-shrink:0; }
.hub-conv-time { font-size:11px; color:var(--muted); }
.hub-conv-badge { background:var(--orange); color:var(--navy); font-size:11px; font-weight:700; border-radius:50%; min-width:20px; height:20px; display:flex; align-items:center; justify-content:center; padding:0 5px; }
.hub-conv-empty { padding:40px 20px; text-align:center; color:var(--muted); font-size:13.5px; }
.hub-conv-empty-ico { font-size:40px; display:block; margin-bottom:10px; opacity:.4; }

/* ─── MAIN ─── */
.hub-main { flex:1; display:flex; flex-direction:column; overflow:hidden; background:var(--bg); }
.hub-welcome { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px; background:var(--bg); }
.hub-welcome-ico { font-size:70px; opacity:.25; }
.hub-welcome-title { font-size:22px; font-weight:600; color:var(--text); opacity:.5; }
.hub-welcome-sub { font-size:14px; color:var(--muted); }

.hub-chat { flex:1; display:none; flex-direction:column; overflow:hidden; }

/* Header */
.hub-chat-head { background:#f0f2f5; border-bottom:1px solid var(--border); padding:10px 16px; display:flex; align-items:center; gap:12px; flex-shrink:0; }
.hub-chat-av { width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg,var(--navy-2),#3d5a73); color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden; }
.hub-chat-av img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.hub-chat-info { flex:1; min-width:0; }
.hub-chat-name { font-size:14.5px; font-weight:700; color:var(--text); margin-bottom:2px; }
.hub-chat-sub { font-size:12px; color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hub-back-btn { display:none; background:none; border:none; font-size:22px; color:var(--muted); cursor:pointer; padding:4px 6px; border-radius:6px; }
.hub-back-btn:hover { background:#e9edef; }

/* Bande produit */
.hub-prod-strip { background:#fff; border-bottom:2px solid var(--orange); padding:10px 16px; display:flex; align-items:center; gap:12px; flex-shrink:0; }
.hub-prod-img-wrap { flex-shrink:0; display:block; cursor:pointer; border-radius:8px; overflow:hidden; }
.hub-prod-img { width:54px; height:54px; border-radius:8px; object-fit:cover; border:2px solid var(--border); box-shadow:0 2px 8px rgba(0,0,0,.12); display:block; transition:opacity .15s; }
.hub-prod-img:hover { opacity:.85; }
.hub-prod-img-ph { width:54px; height:54px; border-radius:8px; background:#f0f2f5; border:2px solid var(--border); display:flex; align-items:center; justify-content:center; font-size:22px; }
.hub-prod-info { flex:1; min-width:0; }
.hub-prod-label { font-size:10px; font-weight:700; color:var(--orange); text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px; }
.hub-prod-name { font-size:13.5px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.hub-prod-price { font-size:13px; color:#b12704; font-weight:800; font-family:monospace; margin-top:1px; }
.hub-strip-actions { display:flex; gap:8px; align-items:center; flex-shrink:0; }
.hub-prod-link { font-size:12px; color:var(--blue); text-decoration:none; font-weight:700; white-space:nowrap; padding:7px 12px; border-radius:8px; border:1.5px solid var(--blue); background:#e8f4f8; transition:all .15s; }
.hub-prod-link:hover { background:#bee3f8; }
.hub-propose-btn { font-size:12px; font-weight:700; white-space:nowrap; padding:7px 12px; border-radius:8px; border:1.5px solid var(--yellow); background:#fef3c7; color:#92400e; cursor:pointer; font-family:var(--font); transition:all .15s; }
.hub-propose-btn:hover { background:#fde68a; }

/* Panneau proposition de prix */
.hub-propose-panel { display:none; background:linear-gradient(135deg,#fffbeb,#fef3c7); border-bottom:1px solid #fde68a; padding:12px 16px; flex-wrap:wrap; gap:8px; align-items:center; flex-shrink:0; }
.hub-propose-panel.open { display:flex; }
.hub-propose-label { font-size:12px; font-weight:700; color:#92400e; width:100%; margin-bottom:2px; }
.hub-propose-input { flex:1; min-width:120px; padding:9px 14px; border-radius:20px; border:1.5px solid var(--yellow); font-size:14px; font-weight:600; font-family:monospace; outline:none; background:#fff; transition:box-shadow .15s; }
.hub-propose-input:focus { box-shadow:0 0 0 3px rgba(245,158,11,.2); }
.hub-propose-devise { font-size:12px; font-weight:700; color:#92400e; }
.hub-propose-send { padding:9px 18px; border-radius:20px; background:var(--yellow); color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer; font-family:var(--font); transition:all .15s; }
.hub-propose-send:hover { background:#d97706; }
.hub-propose-cancel { padding:9px 12px; border-radius:20px; background:transparent; color:#92400e; border:1.5px solid var(--yellow); font-size:12px; font-weight:600; cursor:pointer; font-family:var(--font); }

/* Thread */
.hub-thread { flex:1; overflow-y:auto; padding:16px 12px; display:flex; flex-direction:column; gap:4px; background:#efeae2 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Ccircle cx='40' cy='40' r='1.5' fill='%23c8b89a' opacity='.18'/%3E%3C/svg%3E"); }
.hub-thread::-webkit-scrollbar { width:5px; }
.hub-thread::-webkit-scrollbar-thumb { background:#c5c5c5; border-radius:5px; }

/* Séparateur date */
.hub-date-sep { text-align:center; margin:10px 0; }
.hub-date-sep span { background:#fff; color:var(--muted); font-size:11.5px; font-weight:600; padding:4px 12px; border-radius:20px; box-shadow:0 1px 3px rgba(0,0,0,.1); }

/* Bulles texte */
.hub-msg-row { display:flex; gap:6px; max-width:72%; margin-bottom:2px; }
.hub-msg-row.mine { margin-left:auto; flex-direction:row-reverse; }
.hub-msg-bubble { padding:9px 13px; border-radius:8px; font-size:13.5px; line-height:1.55; word-break:break-word; max-width:100%; box-shadow:0 1px 2px rgba(0,0,0,.13); }
.hub-msg-row.mine   .hub-msg-bubble { background:#dcf8c6; color:#111; border-bottom-right-radius:2px; }
.hub-msg-row.theirs .hub-msg-bubble { background:#fff; color:#111; border-bottom-left-radius:2px; }
.hub-msg-meta { display:flex; align-items:center; gap:4px; margin-top:3px; justify-content:flex-end; }
.hub-msg-time { font-size:10.5px; color:#667781; }
.hub-msg-tick { font-size:11px; color:#53bdeb; }

/* ─── CARTES NÉGOCIATION ─── */
.nego-wrap { display:flex; max-width:300px; margin-bottom:2px; }
.nego-wrap.mine { margin-left:auto; }
.nego-card { border-radius:14px; overflow:hidden; box-shadow:0 3px 16px rgba(0,0,0,.13); width:100%; }
.nego-head { padding:10px 14px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.6px; display:flex; align-items:center; gap:7px; }
.nego-proposal .nego-head { background:linear-gradient(135deg,#fef3c7,#fde68a); color:#92400e; }
.nego-offer    .nego-head { background:linear-gradient(135deg,#d1fae5,#a7f3d0); color:#065f46; }
.nego-order    .nego-head { background:linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1d4ed8; }
.nego-body { background:#fff; padding:14px; border:1px solid var(--border); border-top:none; border-radius:0 0 14px 14px; }
.nego-amount { font-size:24px; font-weight:900; font-family:monospace; color:#111; line-height:1; margin-bottom:4px; }
.nego-original { font-size:11px; color:var(--muted); text-decoration:line-through; margin-bottom:3px; }
.nego-discount { font-size:11px; font-weight:700; color:var(--green); background:var(--green-lt); padding:2px 8px; border-radius:20px; display:inline-flex; margin-bottom:10px; }
.nego-status { font-size:11.5px; font-weight:700; padding:5px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:5px; }
.s-pending  { background:#fef3c7; color:#92400e; }
.s-accepted { background:#d1fae5; color:#065f46; }
.s-refused  { background:#fee2e2; color:#991b1b; }
.nego-confirm-btn { display:flex; align-items:center; justify-content:center; gap:7px; width:100%; padding:11px; margin-top:10px; border-radius:10px; background:linear-gradient(135deg,#10b981,#059669); color:#fff; border:none; font-size:14px; font-weight:800; cursor:pointer; font-family:var(--font); box-shadow:0 4px 14px rgba(16,185,129,.35); transition:all .2s; }
.nego-confirm-btn:hover { background:linear-gradient(135deg,#059669,#047857); transform:translateY(-1px); }
.nego-confirm-btn:disabled { opacity:.6; cursor:not-allowed; transform:none; }
.nego-meta { font-size:10.5px; color:#667781; margin-top:6px; text-align:right; }

/* Input zone */
.hub-input-zone { background:#f0f2f5; border-top:1px solid var(--border); padding:10px 14px; display:flex; gap:10px; align-items:flex-end; flex-shrink:0; }
.hub-textarea { flex:1; padding:10px 16px; border-radius:24px; border:none; background:#fff; font-size:13.5px; font-family:var(--font); outline:none; resize:none; min-height:42px; max-height:120px; line-height:1.5; box-shadow:0 1px 2px rgba(0,0,0,.1); }
.hub-send-btn { width:44px; height:44px; border-radius:50%; background:var(--orange); color:var(--navy); border:none; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:background .15s,transform .1s; box-shadow:0 2px 6px rgba(255,153,0,.35); }
.hub-send-btn:hover { background:var(--orange-dk); transform:scale(1.06); }
.hub-send-btn:disabled { opacity:.5; cursor:not-allowed; transform:none; }

.hub-thread-loader,.hub-thread-empty { text-align:center; padding:60px 20px; color:var(--muted); font-size:13.5px; }

/* ── Tablette large (≤1024px) ── */
@media (max-width:1024px) {
    :root { --sidebar-w:300px; }
}

/* ── Tablette (≤768px) ── */
@media (max-width:768px) {
    :root { --sidebar-w:260px; }
    .hub-prod-strip { gap:8px; padding:8px 12px; }
    .hub-prod-img { width:46px; height:46px; }
    .hub-prod-img-ph { width:46px; height:46px; }
    .hub-prod-label { display:none; }
    .hub-strip-actions { flex-direction:column; gap:4px; }
    .hub-prod-link, .hub-propose-btn { padding:5px 10px; font-size:11px; }
    .hub-msg-row { max-width:80%; }
    .nego-wrap { max-width:82%; }
}

/* ── Mobile (≤640px) — sidebar cachée, toggle WhatsApp ── */
@media (max-width:640px) {
    :root { --sidebar-w:100%; }
    .hub-main { display:none; }
    .hub.conv-open .hub-sidebar { display:none; }
    .hub.conv-open .hub-main { display:flex; }
    .hub-back-btn { display:flex !important; }

    /* Bulles */
    .hub-msg-row { max-width:86%; }
    .hub-msg-bubble { font-size:13px; }
    .nego-wrap { max-width:88%; }
    .nego-amount { font-size:20px; }
    .nego-confirm-btn { font-size:13px; padding:10px; }

    /* En-tête */
    .hub-chat-head { padding:8px 10px; gap:8px; }
    .hub-chat-av { width:36px; height:36px; font-size:11px; }
    .hub-chat-name { font-size:13px; }
    .hub-chat-sub { font-size:11px; }

    /* Strip produit */
    .hub-prod-strip { padding:7px 10px; gap:8px; flex-wrap:nowrap; }
    .hub-prod-img { width:42px; height:42px; }
    .hub-prod-img-ph { width:42px; height:42px; font-size:18px; }
    .hub-prod-name { font-size:12px; }
    .hub-prod-price { font-size:11.5px; }
    .hub-strip-actions { flex-direction:column; gap:4px; align-items:flex-end; }
    .hub-prod-link, .hub-propose-btn { padding:4px 9px; font-size:10.5px; }

    /* Panneau proposition */
    .hub-propose-panel { padding:10px 12px; gap:6px; }
    .hub-propose-input { font-size:13px; padding:8px 12px; min-width:90px; }
    .hub-propose-send { padding:8px 13px; font-size:12px; }
    .hub-propose-cancel { padding:8px 10px; font-size:11px; }

    /* Zone saisie */
    .hub-input-zone { padding:7px 8px; gap:7px; }
    .hub-textarea { font-size:13px; padding:8px 13px; }
    .hub-send-btn { width:40px; height:40px; font-size:16px; }

    /* Thread */
    .hub-thread { padding:12px 8px; }
}

/* ── Très petit mobile (≤400px) ── */
@media (max-width:400px) {
    .hub-chat-head { padding:7px 8px; }
    .hub-prod-strip { padding:6px 8px; gap:6px; }
    .hub-prod-img, .hub-prod-img-ph { width:38px; height:38px; }
    .hub-prod-name { font-size:11.5px; }
    .hub-prod-price { font-size:11px; }
    .hub-prod-link { display:none; }
    .hub-propose-btn { font-size:10px; padding:4px 8px; }
    .hub-msg-row { max-width:92%; }
    .nego-wrap { max-width:94%; }
    .hub-input-zone { padding:6px 6px; gap:5px; }
    .hub-send-btn { width:38px; height:38px; font-size:15px; }
}
</style>
@endpush

@section('content')
@php
    $user     = auth()->user();
    $parts    = explode(' ', $user->name);
    $initials = strtoupper(substr($parts[0],0,1)) . strtoupper(substr($parts[1] ?? 'X',0,1));
@endphp

<div class="hub" id="hub">

{{-- ═══ SIDEBAR ═══ --}}
<aside class="hub-sidebar">
    <div class="hub-sidebar-head">
        <span class="hub-sidebar-title">💬 Messages</span>
        <a href="{{ route('client.dashboard') }}" class="hub-back-dash" title="Tableau de bord">←</a>
    </div>
    <div class="hub-search-wrap">
        <input type="search" class="hub-search" placeholder="Rechercher…" oninput="filterConvs(this.value)">
    </div>
    <div class="hub-conv-list" id="convList">
        @forelse($conversations as $conv)
        @php
            $shop      = $conv->shop;
            $product   = $conv->product;
            $shopName  = $shop?->name ?? 'Boutique';
            $sParts    = explode(' ', $shopName);
            $sInit     = strtoupper(substr($sParts[0],0,1)) . strtoupper(substr($sParts[1] ?? 'X',0,1));
            $preview   = Str::limit($conv->lastMsg->body ?? '…', 40);
            $timeAgo   = $conv->lastMsg->created_at->diffForHumans(null, true);
            $shopImg   = $shop?->image  ? asset('storage/'.$shop->image)     : '';
            $prodImg   = $product?->image ? asset('storage/'.$product->image) : '';
            $prodName  = $product?->name  ?? '';
            $devise    = $shop?->currency ?? 'GNF';
            $prodPrice = $product ? number_format($product->price,0,',',' ').' '.$devise : '';
            $prodPriceRaw = $product?->price ?? 0;
            $prodUrl   = $product ? route('client.products.show', $product) : '#';
        @endphp
        <div class="hub-conv-item"
             data-product-id="{{ $conv->productId }}"
             data-shop-name="{{ $shopName }}"
             data-shop-init="{{ $sInit }}"
             data-shop-img="{{ $shopImg }}"
             data-prod-name="{{ $prodName }}"
             data-prod-price="{{ $prodPrice }}"
             data-prod-price-raw="{{ $prodPriceRaw }}"
             data-prod-img="{{ $prodImg }}"
             data-prod-url="{{ $prodUrl }}"
             data-devise="{{ $devise }}"
             data-search="{{ strtolower($shopName.' '.$prodName) }}"
             onclick="selectConv({{ $conv->productId ?? 'null' }}, this)">
            <div class="hub-conv-av">
                @if($shop?->image)<img src="{{ asset('storage/'.$shop->image) }}" alt="">@else{{ $sInit }}@endif
            </div>
            <div class="hub-conv-body">
                <div class="hub-conv-name">{{ $shopName }}</div>
                <div class="hub-conv-preview">@if($product)🏷️ {{ Str::limit($product->name,22) }} · @endif{{ $preview }}</div>
            </div>
            <div class="hub-conv-meta">
                <span class="hub-conv-time">{{ $timeAgo }}</span>
                @if($conv->unread > 0)<span class="hub-conv-badge">{{ $conv->unread }}</span>@endif
            </div>
        </div>
        @empty
        <div class="hub-conv-empty">
            <span class="hub-conv-empty-ico">💬</span>
            Aucune conversation.<br>Visitez une boutique et posez une question !
        </div>
        @endforelse
    </div>
</aside>

{{-- ═══ MAIN ═══ --}}
<main class="hub-main" id="hubMain">

    <div class="hub-welcome" id="hubWelcome">
        <div class="hub-welcome-ico">💬</div>
        <div class="hub-welcome-title">Shopio Messages</div>
        <div class="hub-welcome-sub">Sélectionnez une conversation pour commencer</div>
    </div>

    <div class="hub-chat" id="hubChat">

        {{-- Header --}}
        <div class="hub-chat-head">
            <button class="hub-back-btn" onclick="backToList()">←</button>
            <div class="hub-chat-av" id="hubChatAv">?</div>
            <div class="hub-chat-info">
                <div class="hub-chat-name" id="hubChatName">—</div>
                <div class="hub-chat-sub"  id="hubChatSub">—</div>
            </div>
        </div>

        {{-- Bande produit --}}
        <div class="hub-prod-strip" id="hubProdStrip" style="display:none">
            <a class="hub-prod-img-wrap" id="hubProdImgLink" href="#" target="_blank">
                <div class="hub-prod-img-ph" id="hubProdImgPh">🏷️</div>
                <img class="hub-prod-img" id="hubProdImg" src="" alt="" style="display:none">
            </a>
            <div class="hub-prod-info">
                <div class="hub-prod-label">Produit en discussion</div>
                <div class="hub-prod-name"  id="hubProdName">—</div>
                <div class="hub-prod-price" id="hubProdPrice"></div>
            </div>
            <div class="hub-strip-actions">
                <button class="hub-propose-btn" id="hubProposeBtn" onclick="toggleProposePanel()">💰 Proposer un prix</button>
                <a class="hub-prod-link" id="hubProdLink" href="#" target="_blank">Voir →</a>
            </div>
        </div>

        {{-- Panneau proposition prix --}}
        <div class="hub-propose-panel" id="hubProposePanel">
            <div class="hub-propose-label">💰 Faire une proposition de prix au vendeur</div>
            <input type="number" id="hubProposeInput" class="hub-propose-input" placeholder="Votre prix…" min="1" step="500">
            <span class="hub-propose-devise" id="hubProposeDevise">GNF</span>
            <button class="hub-propose-send" onclick="sendPriceProposal()">Envoyer la proposition</button>
            <button class="hub-propose-cancel" onclick="toggleProposePanel()">Annuler</button>
        </div>

        {{-- Thread --}}
        <div class="hub-thread" id="hubThread">
            <div class="hub-thread-loader">⏳ Chargement…</div>
        </div>

        {{-- Input --}}
        <div class="hub-input-zone">
            <textarea id="hubInput" class="hub-textarea" placeholder="Écrire un message…" rows="1"
                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendHubMsg()}"
                oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"></textarea>
            <button class="hub-send-btn" onclick="sendHubMsg()" id="hubSendBtn">➤</button>
        </div>
    </div>
</main>
</div>
@endsection

@push('scripts')
<script>
const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const INITIALS = '{{ $initials }}';

let _productId = null;
let _convEl    = null;
let _lastMsgId = 0;
let _pollTimer = null;
let _prodData  = {};

/* ── Filtrer ── */
function filterConvs(q) {
    const lq = q.toLowerCase();
    document.querySelectorAll('.hub-conv-item').forEach(el => {
        el.style.display = el.dataset.search?.includes(lq) ? '' : 'none';
    });
}

function backToList() { document.getElementById('hub').classList.remove('conv-open'); }

/* ── Sélectionner conversation ── */
async function selectConv(productId, el) {
    document.querySelectorAll('.hub-conv-item').forEach(i => i.classList.remove('active'));
    if (el) { el.classList.add('active'); el.querySelector('.hub-conv-badge')?.remove(); }
    _convEl    = el;
    _productId = productId;
    _prodData  = el?.dataset || {};

    /* Header */
    const av = document.getElementById('hubChatAv');
    if (_prodData.shopImg) {
        av.innerHTML = `<img src="${_prodData.shopImg}" alt="">`;
    } else {
        av.textContent = _prodData.shopInit || '?';
    }
    document.getElementById('hubChatName').textContent = _prodData.shopName || '—';
    document.getElementById('hubChatSub').textContent  = _prodData.prodName ? '🏷️ ' + _prodData.prodName : 'Discussion générale';

    /* Bande produit */
    updateProdStrip();

    document.getElementById('hub').classList.add('conv-open');
    document.getElementById('hubWelcome').style.display = 'none';
    document.getElementById('hubChat').style.display    = 'flex';

    /* Fermer panel propose si ouvert */
    document.getElementById('hubProposePanel').classList.remove('open');

    document.getElementById('hubThread').innerHTML = '<div class="hub-thread-loader">⏳ Chargement…</div>';
    _lastMsgId = 0;

    stopPoll();
    await loadConv();
    startPoll();
}

/* ── Bande produit ── */
function updateProdStrip() {
    const d = _prodData;
    const strip = document.getElementById('hubProdStrip');
    if (!d.prodName) { strip.style.display = 'none'; return; }

    strip.style.display = 'flex';
    document.getElementById('hubProdName').textContent  = d.prodName;
    document.getElementById('hubProdPrice').textContent = d.prodPrice || '';
    document.getElementById('hubProdLink').href         = d.prodUrl  || '#';
    document.getElementById('hubProdImgLink').href      = d.prodUrl  || '#';
    document.getElementById('hubProposeDevise').textContent = d.devise || 'GNF';

    /* Pré-remplir le champ prix à 90% du prix catalogue */
    const raw = parseFloat(d.prodPriceRaw || 0);
    if (raw > 0) document.getElementById('hubProposeInput').value = Math.floor(raw * 0.9);

    const img = document.getElementById('hubProdImg');
    const ph  = document.getElementById('hubProdImgPh');
    if (d.prodImg) {
        img.src = d.prodImg; img.alt = d.prodName;
        img.style.display = ''; ph.style.display = 'none';
    } else {
        img.style.display = 'none'; ph.style.display = 'flex';
    }
}

/* ── Panneau proposition ── */
function toggleProposePanel() {
    const panel = document.getElementById('hubProposePanel');
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) document.getElementById('hubProposeInput').focus();
}

/* ── Envoyer proposition de prix ── */
async function sendPriceProposal() {
    const price = parseFloat(document.getElementById('hubProposeInput').value);
    if (!price || price < 1) { alert('Entrez un prix valide.'); return; }

    const btn = document.querySelector('.hub-propose-send');
    btn.disabled = true; btn.textContent = '⏳ Envoi…';

    try {
        const res = await fetch('/client/messages/propose-price', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ product_id: _productId, proposed_price: price }),
        });
        const data = await res.json();
        if (data.sent || data.success) {
            document.getElementById('hubProposePanel').classList.remove('open');
            /* Recharger le thread pour afficher la carte */
            _lastMsgId = 0;
            await loadConv();
        } else {
            alert('Erreur lors de l\'envoi.');
        }
    } catch(e) { alert('Erreur réseau.'); }
    finally { btn.disabled = false; btn.textContent = 'Envoyer la proposition'; }
}

/* ── Charger conversation ── */
async function loadConv() {
    if (!_productId) return;
    try {
        const res = await fetch(`/client/products/${_productId}/messages`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!res.ok) return;
        const msgs = await res.json();
        const thread = document.getElementById('hubThread');
        thread.innerHTML = '';

        if (!msgs.length) {
            thread.innerHTML = '<div class="hub-thread-empty">💬 Aucun message. Posez votre première question !</div>';
            _lastMsgId = 0; return;
        }

        let lastDate = '';
        msgs.forEach(msg => {
            const dk = msg.date || '';
            if (dk && dk !== lastDate) { thread.appendChild(buildDateSep(dk)); lastDate = dk; }
            thread.appendChild(buildRow(msg));
        });

        _lastMsgId = Math.max(...msgs.map(m => m.id || 0));
        thread.scrollTop = thread.scrollHeight;
    } catch(e) {}
}

/* ── Polling ── */
function startPoll() { stopPoll(); _pollTimer = setInterval(pollConv, 3000); }
function stopPoll()  { clearInterval(_pollTimer); _pollTimer = null; }

async function pollConv() {
    if (!_productId) return;
    try {
        const res = await fetch(`/client/products/${_productId}/messages`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!res.ok) return;
        const msgs  = await res.json();
        const thread = document.getElementById('hubThread');
        const newMsgs = msgs.filter(m => m.id > _lastMsgId);

        for (const msg of newMsgs) {
            _lastMsgId = Math.max(_lastMsgId, msg.id);
            if (thread.querySelector(`[data-msg-id="${msg.id}"]`)) continue;
            if (msg.mine) continue;

            /* Cartes de négo : recharger pour avoir les boutons à jour */
            if (msg.type && msg.type !== 'text') { await loadConv(); return; }

            thread.querySelector('.hub-thread-empty')?.remove();
            thread.appendChild(buildRow(msg));
            thread.scrollTop = thread.scrollHeight;

            /* Mise à jour preview sidebar */
            if (_convEl) {
                _convEl.querySelector('.hub-conv-preview')?.textContent !== undefined &&
                    (_convEl.querySelector('.hub-conv-preview').textContent = (msg.body||'').substring(0,40));
                const t = _convEl.querySelector('.hub-conv-time');
                if (t) t.textContent = 'À l\'instant';
            }
        }
    } catch(e) {}
}

/* ── Envoyer message texte ── */
async function sendHubMsg() {
    if (!_productId) return;
    const input = document.getElementById('hubInput');
    const body  = input.value.trim();
    if (!body) return;

    const btn = document.getElementById('hubSendBtn');
    btn.disabled = true;
    try {
        const res  = await fetch(`/client/products/${_productId}/message`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ body }),
        });
        const data = await res.json();
        if (data.sent) {
            input.value = ''; input.style.height = 'auto';
            if (data.message_id) _lastMsgId = Math.max(_lastMsgId, data.message_id);

            const thread = document.getElementById('hubThread');
            thread.querySelector('.hub-thread-empty')?.remove();

            const now  = new Date();
            const time = now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0');
            thread.appendChild(buildRow({ id: data.message_id||0, mine:true, body, time, read:false, type:'text' }));
            thread.scrollTop = thread.scrollHeight;

            if (_convEl) {
                const p = _convEl.querySelector('.hub-conv-preview');
                if (p) p.textContent = body.substring(0,40);
                const t = _convEl.querySelector('.hub-conv-time');
                if (t) t.textContent = 'À l\'instant';
            }
        }
    } catch(e) {}
    finally { btn.disabled = false; document.getElementById('hubInput').focus(); }
}

/* ── Confirmer une offre vendeur ── */
async function confirmOffer(msgId, btn) {
    if (!confirm('Confirmer cette offre et créer la commande ?')) return;
    btn.disabled = true; btn.textContent = '⏳ Création…';
    try {
        const res  = await fetch(`/client/messages/confirm-offer/${msgId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            btn.textContent = '✅ Commande n°' + data.order_id + ' créée !';
            btn.style.background = '#065f46';
            setTimeout(() => loadConv(), 1500);
        } else throw new Error();
    } catch(e) {
        btn.disabled = false; btn.textContent = '✓ Confirmer cette offre et commander';
    }
}

/* ── Builders ── */
function escHtml(s) {
    return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function buildDateSep(label) {
    const d = document.createElement('div'); d.className = 'hub-date-sep';
    d.innerHTML = `<span>${escHtml(label)}</span>`; return d;
}
function buildRow(msg) {
    if (msg.type === 'price_proposal') return buildProposalCard(msg);
    if (msg.type === 'price_offer')    return buildOfferCard(msg);
    if (msg.type === 'order_created')  return buildOrderCard(msg);
    return buildTextRow(msg);
}

function buildTextRow(msg) {
    const row = document.createElement('div');
    row.className = 'hub-msg-row ' + (msg.mine ? 'mine' : 'theirs');
    if (msg.id) row.dataset.msgId = msg.id;
    const tick = msg.mine ? `<span class="hub-msg-tick">${msg.read?'✓✓':'✓'}</span>` : '';
    row.innerHTML = `<div class="hub-msg-bubble">${escHtml(msg.body)}<div class="hub-msg-meta"><span class="hub-msg-time">${escHtml(msg.time||'')}</span>${tick}</div></div>`;
    return row;
}

function buildProposalCard(msg) {
    const raw = _prodData.prodPriceRaw ? parseFloat(_prodData.prodPriceRaw) : 0;
    const devise = _prodData.devise || 'GNF';
    const disc = raw > 0 ? Math.round((1 - msg.proposed_price / raw) * 100) : 0;
    const statusMap = { pending:'⏳ En attente de réponse…', accepted:'✓ Proposition acceptée', refused:'✕ Proposition refusée' };
    const statusClass = { pending:'s-pending', accepted:'s-accepted', refused:'s-refused' };
    const wrap = document.createElement('div');
    wrap.className = 'nego-wrap mine'; if (msg.id) wrap.dataset.msgId = msg.id;
    wrap.innerHTML = `
      <div class="nego-card nego-proposal">
        <div class="nego-head">💰 Proposition de prix</div>
        <div class="nego-body">
          <div class="nego-amount">${fmtPrice(msg.proposed_price, devise)}</div>
          ${raw>0 ? `<div class="nego-original">Prix catalogue : ${fmtPrice(raw, devise)}</div>` : ''}
          ${disc>0 ? `<div class="nego-discount">-${disc}% de réduction</div>` : ''}
          <div class="nego-status ${statusClass[msg.proposal_status]||'s-pending'}">${statusMap[msg.proposal_status]||'⏳ En attente…'}</div>
          <div class="nego-meta">${escHtml(msg.time||'')}</div>
        </div>
      </div>`;
    return wrap;
}

function buildOfferCard(msg) {
    const raw = _prodData.prodPriceRaw ? parseFloat(_prodData.prodPriceRaw) : 0;
    const devise = _prodData.devise || 'GNF';
    const disc = raw > 0 ? Math.round((1 - msg.proposed_price / raw) * 100) : 0;
    const wrap = document.createElement('div');
    wrap.className = 'nego-wrap theirs'; if (msg.id) wrap.dataset.msgId = msg.id;
    const isPending = msg.proposal_status === 'pending';
    const confirmBtn = isPending
        ? `<button class="nego-confirm-btn" onclick="confirmOffer(${msg.id},this)">✓ Confirmer et commander</button>`
        : `<div class="nego-status ${msg.proposal_status==='accepted'?'s-accepted':'s-refused'}">${msg.proposal_status==='accepted'?'✅ Offre confirmée — commande créée':'✕ Offre expirée'}</div>`;
    wrap.innerHTML = `
      <div class="nego-card nego-offer">
        <div class="nego-head">🎉 Offre spéciale du vendeur</div>
        <div class="nego-body">
          <div class="nego-amount">${fmtPrice(msg.proposed_price, devise)}</div>
          ${raw>0 ? `<div class="nego-original">Prix catalogue : ${fmtPrice(raw, devise)}</div>` : ''}
          ${disc>0 ? `<div class="nego-discount">Vous économisez ${disc}% !</div>` : ''}
          ${confirmBtn}
          <div class="nego-meta">${escHtml(msg.time||'')}</div>
        </div>
      </div>`;
    return wrap;
}

function buildOrderCard(msg) {
    const devise = _prodData.devise || 'GNF';
    const wrap = document.createElement('div');
    wrap.className = 'nego-wrap mine'; if (msg.id) wrap.dataset.msgId = msg.id;
    wrap.innerHTML = `
      <div class="nego-card nego-order">
        <div class="nego-head">📦 Commande confirmée !</div>
        <div class="nego-body">
          <div class="nego-amount">${fmtPrice(msg.proposed_price, devise)}</div>
          <div class="nego-status s-accepted">✅ Prix négocié appliqué</div>
          ${msg.negotiated_order_id ? `<div style="margin-top:8px;font-size:12px;color:#1d4ed8;font-weight:700">Commande n°${msg.negotiated_order_id}</div>` : ''}
          <div class="nego-meta">${escHtml(msg.time||'')}</div>
        </div>
      </div>`;
    return wrap;
}

function fmtPrice(n, devise) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n||0)) + ' ' + (devise||'GNF');
}
</script>
@endpush
