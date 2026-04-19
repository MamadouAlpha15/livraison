{{--
    resources/views/boutique/_partials/messages_btn_and_drawer.blade.php

    1) Bouton 💬 dans la topbar
    2) Drawer liste des conversations
    3) Modal discussion avec support négociation de prix

    Variables requises :
      $clientMessages → Collection groupée par (client_id - product_id)
      $devise         → string
--}}

@php
    $vendeurId   = auth()->id();
    $totalUnread = isset($clientMessages)
        ? $clientMessages->sum(fn($g) => $g->filter(fn($m) => is_null($m->read_at) && $m->receiver_id === $vendeurId)->count())
        : 0;
    $vParts = explode(' ', auth()->user()->name);
    $vInit  = strtoupper(substr($vParts[0],0,1)) . strtoupper(substr($vParts[1] ?? 'X',0,1));
@endphp

{{-- ══ STYLES ══ --}}
<style>
/* ── Bouton topbar ── */
.msg-topbar-btn {
    position: relative;
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 13px; border-radius: var(--r-sm);
    font-size: 12px; font-weight: 700; font-family: var(--font);
    background: var(--surface); color: var(--text-2);
    border: 1px solid var(--border-dk);
    cursor: pointer; transition: all .15s; white-space: nowrap;
    text-decoration: none;
}
.msg-topbar-btn:hover { background: var(--brand-mlt); border-color: var(--brand); color: var(--brand); }
.msg-topbar-btn.has-unread { background: var(--brand-mlt); border-color: var(--brand); color: var(--brand-dk); }
.msg-topbar-count {
    position: absolute; top: -6px; right: -6px;
    background: #ef4444; color: #fff;
    font-size: 9px; font-weight: 800;
    min-width: 16px; height: 16px;
    border-radius: 20px; padding: 0 4px;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--surface);
    animation: badge-pop .3s cubic-bezier(.36,.07,.19,.97) both;
}
@keyframes badge-pop { 0%{transform:scale(0)}70%{transform:scale(1.2)}100%{transform:scale(1)} }

/* ── Overlay ── */
.msg-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 900; animation: fadeIn .2s ease; }
.msg-overlay.open { display: block; }
@keyframes fadeIn { from{opacity:0}to{opacity:1} }

/* ── Drawer ── */
.msg-drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 380px; max-width: 95vw; background: var(--surface); border-left: 1px solid var(--border); box-shadow: -8px 0 40px rgba(0,0,0,.15); z-index: 901; display: flex; flex-direction: column; transform: translateX(110%); transition: transform .28s cubic-bezier(.23,1,.32,1); }
.msg-drawer.open { transform: translateX(0); }
.msg-drawer-hd { padding: 16px 18px; border-bottom: 1px solid var(--border); background: var(--sb-bg); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.msg-drawer-title { font-size: 14px; font-weight: 800; color: #fff; display: flex; align-items: center; gap: 9px; }
.msg-drawer-badge { background: #ef4444; color: #fff; font-size: 9px; font-weight: 800; padding: 2px 7px; border-radius: 20px; font-family: var(--mono); animation: pulse-red 2s ease-in-out infinite; }
@keyframes pulse-red { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.5)} 50%{box-shadow:0 0 0 6px rgba(239,68,68,0)} }
.msg-drawer-close { width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); color: rgba(255,255,255,.7); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0; }
.msg-drawer-close:hover { background: rgba(239,68,68,.25); color: #fca5a5; border-color: rgba(239,68,68,.3); }
.msg-drawer-sub { padding: 8px 18px; background: rgba(16,185,129,.06); border-bottom: 1px solid var(--border); font-size: 11px; color: var(--muted); flex-shrink: 0; }

/* Liste conversations */
.msg-drawer-list { flex: 1; overflow-y: auto; }
.msg-drawer-list::-webkit-scrollbar { width: 4px; }
.msg-drawer-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.msg-conv-item { display: flex; align-items: center; gap: 12px; padding: 13px 18px; border-bottom: 1px solid #f3f6f4; cursor: pointer; transition: background .12s; position: relative; }
.msg-conv-item:hover { background: var(--bg); }
.msg-conv-item.unread { background: #fefcf0; }
.msg-conv-item.unread:hover { background: #fdf8e0; }
.msg-conv-item.unread::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: var(--brand); border-radius: 0 2px 2px 0; }
.msg-conv-av { width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; color: #fff; background: linear-gradient(135deg, #3b82f6, #1d4ed8); position: relative; }
.msg-conv-av .msg-unread-dot { position: absolute; bottom: 1px; right: 1px; width: 11px; height: 11px; border-radius: 50%; background: #ef4444; border: 2px solid var(--surface); }
.msg-conv-body { flex: 1; min-width: 0; }
.msg-conv-name { font-size: 13px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 6px; margin-bottom: 3px; }
.msg-new-pill { font-size: 9px; font-weight: 800; background: #ef4444; color: #fff; padding: 1px 6px; border-radius: 10px; text-transform: uppercase; flex-shrink: 0; }
.msg-conv-prod { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600; color: var(--brand-dk); background: var(--brand-mlt); border: 1px solid var(--brand-lt); padding: 1px 7px; border-radius: 20px; margin-bottom: 3px; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.msg-conv-preview { font-size: 12px; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.msg-conv-preview.unread { color: var(--text-2); font-weight: 600; }
.msg-conv-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0; }
.msg-conv-time { font-size: 10.5px; color: var(--muted); }
.msg-conv-nb { font-size: 10px; font-weight: 700; font-family: var(--mono); padding: 1px 7px; border-radius: 20px; white-space: nowrap; background: var(--bg); border: 1px solid var(--border); color: var(--muted); }
.msg-conv-nb.unread { background: var(--brand); border-color: var(--brand-dk); color: #fff; }

/* Négociation badge dans la liste */
.msg-nego-badge { font-size: 9px; font-weight: 800; padding: 1px 6px; border-radius: 10px; flex-shrink: 0; }
.msg-nego-badge.proposal { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.msg-nego-badge.offer    { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }

.msg-drawer-empty { padding: 48px 20px; text-align: center; font-size: 13px; color: var(--muted); }
.msg-drawer-empty .ico { font-size: 36px; display: block; margin-bottom: 10px; opacity: .35; }

/* ── Modal discussion ── */
.msg-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 1000; align-items: center; justify-content: center; padding: 16px; }
.msg-modal-overlay.open { display: flex; }
.msg-modal { background: var(--surface); border-radius: var(--r); width: 100%; max-width: 580px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 24px 80px rgba(0,0,0,.25); animation: modalIn .22s cubic-bezier(.23,1,.32,1); overflow: hidden; }
@keyframes modalIn { from{opacity:0;transform:translateY(-16px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)} }

/* Header modal */
.msg-modal-hd { background: var(--sb-bg); padding: 14px 18px; display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
.msg-modal-av { width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, #3b82f6, #1d4ed8); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #fff; }
.msg-modal-info { flex: 1; min-width: 0; }
.msg-modal-name { font-size: 14px; font-weight: 800; color: #fff; }
.msg-modal-sub { font-size: 11px; color: rgba(255,255,255,.5); margin-top: 2px; }
.msg-modal-close { width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); color: rgba(255,255,255,.6); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; flex-shrink: 0; }
.msg-modal-close:hover { background: rgba(239,68,68,.25); color: #fca5a5; }

/* Barre produit */
.msg-modal-prod { display: flex; align-items: center; gap: 10px; padding: 9px 18px; background: linear-gradient(90deg, #ecfdf5, #f0fdf4); border-bottom: 1px solid #d1fae5; flex-shrink: 0; }
.msg-modal-prod-img { width: 38px; height: 38px; border-radius: var(--r-sm); object-fit: cover; border: 1px solid var(--border); flex-shrink: 0; }
.msg-modal-prod-ph { width: 38px; height: 38px; border-radius: var(--r-sm); background: var(--bg); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
.msg-modal-prod-name { font-size: 12.5px; font-weight: 700; color: var(--text); }
.msg-modal-prod-price { font-size: 11.5px; color: var(--brand); font-weight: 800; font-family: var(--mono); }

/* Thread — fond style chat moderne */
.msg-modal-thread {
    flex: 1; overflow-y: auto;
    padding: 18px 16px;
    display: flex; flex-direction: column; gap: 6px;
    background: #efeae1;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4cfc8' fill-opacity='0.25'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.msg-modal-thread::-webkit-scrollbar { width: 5px; }
.msg-modal-thread::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 4px; }

/* ── Séparateur de date ── */
.msg-date-sep {
    display: flex; align-items: center; justify-content: center;
    margin: 10px 0;
}
.msg-date-sep span {
    background: rgba(255,255,255,.9);
    padding: 3px 14px; border-radius: 20px;
    font-size: 10.5px; color: #666; font-weight: 600;
    box-shadow: 0 1px 3px rgba(0,0,0,.12);
}

/* ── Ligne de bulle ── */
.msg-bubble-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    margin-bottom: 4px;
    animation: msgIn .18s ease;
}
@keyframes msgIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

/* Message CLIENT (gauche) */
.msg-bubble-row:not(.mine) {
    flex-direction: row;
    justify-content: flex-start;
}

/* Message VENDEUR (droite) */
.msg-bubble-row.mine {
    flex-direction: row-reverse;
    justify-content: flex-start;
}

/* Avatar client */
.msg-bav {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff; font-size: 11px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(0,0,0,.2);
}

/* Contenu (nom + bulle + méta) */
.msg-bcontent {
    display: flex; flex-direction: column;
    max-width: 72%;
}

/* Nom client au-dessus de sa bulle */
.msg-bsender {
    font-size: 10.5px; font-weight: 700;
    color: #3b82f6; margin-bottom: 3px; padding-left: 4px;
}

/* Bulle */
.msg-bubble {
    padding: 9px 14px 7px;
    border-radius: 18px;
    font-size: 13.5px; line-height: 1.55;
    word-break: break-word;
}

/* Bulle CLIENT — blanche, queue bas-gauche */
.msg-bubble-row:not(.mine) .msg-bubble {
    background: #fff;
    color: #111;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,.12);
}

/* Bulle VENDEUR — verte, queue bas-droite */
.msg-bubble-row.mine .msg-bubble {
    background: #dcf8c6;
    color: #0e1a0e;
    border-bottom-right-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,.12);
}

/* Méta : heure + ticks */
.msg-bmeta {
    display: flex; align-items: center; gap: 4px;
    font-size: 10px; color: #999;
    margin-top: 3px; padding: 0 4px;
}
.msg-bubble-row.mine .msg-bmeta { justify-content: flex-end; }
.msg-read-tick { color: #34b7f1; }
.msg-sent-tick { color: #aaa; }

/* Zone réponse — redesignée */
.msg-modal-reply {
    padding: 10px 14px; border-top: 1px solid rgba(0,0,0,.08);
    background: #f0f0f0;
    display: flex; gap: 8px; align-items: flex-end; flex-shrink: 0;
}
.msg-modal-input {
    flex: 1; padding: 10px 16px;
    border: none; border-radius: 24px;
    font-size: 13.5px; font-family: var(--font);
    color: #111; background: #fff;
    outline: none; resize: none;
    min-height: 42px; max-height: 110px;
    line-height: 1.45;
    box-shadow: 0 1px 3px rgba(0,0,0,.12);
    transition: box-shadow .15s;
}
.msg-modal-input:focus { box-shadow: 0 2px 8px rgba(0,0,0,.18); }
.msg-modal-input::placeholder { color: #aaa; }
.msg-modal-send {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #25d366, #128c7e);
    color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
    transition: all .18s;
    box-shadow: 0 3px 10px rgba(18,140,126,.4);
}
.msg-modal-send:hover { transform: scale(1.1); box-shadow: 0 5px 16px rgba(18,140,126,.5); }
.msg-modal-send:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* ── Cartes Négociation (modal) ── */
.nego-card { border-radius: 14px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.1); width: 100%; max-width: 290px; }
.msg-bubble-row.mine .nego-card { margin-left: auto; }

.nego-card-header { padding: 9px 13px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; display: flex; align-items: center; gap: 5px; }
.nego-card.proposal .nego-card-header { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
.nego-card.offer    .nego-card-header { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; }
.nego-card.order-ok .nego-card-header { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; }

.nego-card-body { background: #fff; padding: 13px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 14px 14px; }
.nego-card-amount { font-size: 20px; font-weight: 900; color: #0f1111; font-family: monospace; line-height: 1; margin-bottom: 3px; }
.nego-card-original { font-size: 10.5px; color: #6b7280; text-decoration: line-through; margin-bottom: 2px; }
.nego-card-discount { font-size: 10px; font-weight: 700; color: #059669; background: #d1fae5; display: inline-flex; padding: 2px 7px; border-radius: 20px; margin-bottom: 8px; }
.nego-card-status { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; }
.nego-status-pending  { background: #fef3c7; color: #92400e; }
.nego-status-accepted { background: #d1fae5; color: #065f46; }
.nego-status-refused  { background: #fee2e2; color: #991b1b; }

/* Boutons action vendeur */
.nego-actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
.nego-btn-accept {
    flex: 1; padding: 8px 12px; border-radius: 8px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; border: none; font-size: 12px; font-weight: 700;
    cursor: pointer; font-family: var(--font); transition: all .15s;
    display: flex; align-items: center; justify-content: center; gap: 4px;
}
.nego-btn-accept:hover { background: linear-gradient(135deg, #059669, #047857); transform: scale(1.03); }
.nego-btn-refuse {
    flex: 1; padding: 8px 12px; border-radius: 8px;
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    color: #dc2626; border: 1.5px solid #fca5a5;
    font-size: 12px; font-weight: 700;
    cursor: pointer; font-family: var(--font); transition: all .15s;
    display: flex; align-items: center; justify-content: center; gap: 4px;
}
.nego-btn-refuse:hover { background: #fecaca; transform: scale(1.03); }

/* Formulaire offre vendeur */
.nego-offer-form { display: none; background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1.5px solid #6ee7b7; border-radius: 10px; padding: 12px; margin-top: 10px; }
.nego-offer-form.open { display: block; }
.nego-offer-form-title { font-size: 11.5px; font-weight: 800; color: #065f46; margin-bottom: 8px; }
.nego-offer-row { display: flex; gap: 6px; align-items: center; }
.nego-offer-input {
    flex: 1; padding: 8px 12px; border: 1.5px solid #6ee7b7; border-radius: 8px;
    font-size: 14px; font-weight: 700; font-family: monospace; color: #0f1111;
    outline: none; background: #fff; transition: border-color .15s;
}
.nego-offer-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.15); }
.nego-offer-devise { font-size: 11px; font-weight: 700; color: #065f46; }
.nego-offer-send {
    padding: 8px 16px; border-radius: 8px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff; border: none; font-size: 12px; font-weight: 700;
    cursor: pointer; font-family: var(--font); white-space: nowrap;
    transition: all .15s;
}
.nego-offer-send:hover { background: linear-gradient(135deg, #059669, #047857); }
.nego-offer-send:disabled { opacity: .6; cursor: not-allowed; }

/* Commande créée badge */
.nego-order-badge { display: flex; align-items: center; gap: 8px; padding: 9px 12px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #bfdbfe; border-radius: 8px; margin-top: 8px; }
.nego-order-badge-ico { font-size: 18px; }
.nego-order-badge-text { font-size: 11.5px; font-weight: 700; color: #1d4ed8; }

/* Zone réponse */
.msg-modal-reply { padding: 12px 18px; border-top: 1px solid var(--border); background: var(--surface); display: flex; gap: 10px; align-items: flex-end; flex-shrink: 0; }
.msg-modal-input { flex: 1; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 22px; font-size: 13px; font-family: var(--font); color: var(--text); background: var(--bg); outline: none; resize: none; min-height: 40px; max-height: 100px; line-height: 1.45; transition: border-color .15s, box-shadow .15s; }
.msg-modal-input:focus { border-color: var(--brand); background: var(--surface); box-shadow: 0 0 0 3px rgba(16,185,129,.12); }
.msg-modal-send { width: 40px; height: 40px; border-radius: 50%; background: var(--brand); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; transition: all .15s; box-shadow: 0 3px 10px rgba(16,185,129,.35); }
.msg-modal-send:hover { background: var(--brand-dk); transform: scale(1.08); }

/* Toast boutique */
.nego-toast {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(100px);
    background: #0f1111; color: #fff;
    padding: 12px 22px; border-radius: 24px;
    font-size: 13px; font-weight: 600;
    box-shadow: 0 8px 30px rgba(0,0,0,.25);
    z-index: 2000; transition: transform .3s cubic-bezier(.23,1,.32,1);
}
.nego-toast.show { transform: translateX(-50%) translateY(0); }
.nego-toast.success { background: linear-gradient(135deg, #065f46, #047857); }
.nego-toast.error   { background: linear-gradient(135deg, #991b1b, #b91c1c); }

/* Responsive */
@media (max-width: 600px) {
    .msg-drawer { width: 100%; max-width: 100%; }
    .msg-modal { max-height: 96vh; border-radius: 16px 16px 0 0; align-self: flex-end; }
    .msg-modal-overlay { padding: 0; align-items: flex-end; }
    .msg-topbar-btn span.btn-label { display: none; }
}
</style>

{{-- ══ BOUTON TOPBAR → redirige vers le hub dédié ══ --}}
<a href="{{ route('boutique.messages.hub') }}"
   class="msg-topbar-btn {{ $totalUnread > 0 ? 'has-unread' : '' }}">
    💬 <span class="btn-label">Messages</span>
    @if($totalUnread > 0)
    <span class="msg-topbar-count" id="msgTopbarCount">{{ $totalUnread }}</span>
    @endif
</a>

{{-- ══ OVERLAY ══ --}}
<div class="msg-overlay" id="msgOverlay" onclick="closeMsgDrawer()"></div>

{{-- ══ DRAWER ══ --}}
<div class="msg-drawer" id="msgDrawer">
    <div class="msg-drawer-hd">
        <div class="msg-drawer-title">
            💬 Messages clients
            @if($totalUnread > 0)
            <span class="msg-drawer-badge">{{ $totalUnread }} non lu{{ $totalUnread > 1 ? 's' : '' }}</span>
            @endif
        </div>
        <button class="msg-drawer-close" onclick="closeMsgDrawer()">✕</button>
    </div>
    <div class="msg-drawer-sub">
        {{ isset($clientMessages) ? $clientMessages->count() : 0 }} conversation{{ (isset($clientMessages) && $clientMessages->count() > 1) ? 's' : '' }} · Cliquez pour ouvrir une discussion
    </div>

    <div class="msg-drawer-list">
        @if(isset($clientMessages) && $clientMessages->count() > 0)
            @foreach($clientMessages as $convKey => $msgs)
            @php
                $firstMsg  = $msgs->first();
                $lastMsg   = $msgs->last();
                $client    = optional($firstMsg->sender)->role === 'client' ? $firstMsg->sender : $firstMsg->receiver;
                if (!$client) {
                    $cid    = explode('-', $convKey)[0];
                    $client = \App\Models\User::find($cid);
                }
                $product   = $firstMsg->product;
                $hasUnread = $msgs->contains(fn($m) => is_null($m->read_at) && $m->receiver_id === $vendeurId);
                $unreadNb  = $msgs->filter(fn($m) => is_null($m->read_at) && $m->receiver_id === $vendeurId)->count();
                $cName     = $client->name ?? 'Client inconnu';
                $cParts    = explode(' ', $cName);
                $cInit     = strtoupper(substr($cParts[0],0,1)) . strtoupper(substr($cParts[1] ?? 'X',0,1));

                // Détecter une proposition de prix en attente
                $hasPendingProposal = $msgs->contains(
                    fn($m) => $m->type === 'price_proposal' && $m->proposal_status === 'pending' && $m->receiver_id === $vendeurId
                );
                $hasPendingOffer = $msgs->contains(
                    fn($m) => $m->type === 'price_offer' && $m->proposal_status === 'pending' && $m->sender_id === $vendeurId
                );

                // Préparer les données JSON
                $msgsData = $msgs->map(fn($m) => [
                    'id'                  => $m->id,
                    'body'                => $m->body,
                    'mine'                => $m->sender_id === $vendeurId,
                    'sender'              => $m->sender->name ?? 'Inconnu',
                    'time'                => $m->created_at->format('H:i'),
                    'date'                => $m->created_at->isToday() ? "Aujourd'hui" : ($m->created_at->isYesterday() ? 'Hier' : $m->created_at->format('d/m/Y')),
                    'dateKey'             => $m->created_at->toDateString(),
                    'read'                => !is_null($m->read_at),
                    'type'                => $m->type ?? 'text',
                    'proposed_price'      => $m->proposed_price ? (float)$m->proposed_price : null,
                    'proposal_status'     => $m->proposal_status,
                    'negotiated_order_id' => $m->negotiated_order_id,
                ])->values()->toArray();

                $convData = [
                    'clientId'      => $client->id ?? 0,
                    'clientName'    => $cName,
                    'clientInit'    => $cInit,
                    'vendeurInit'   => $vInit,
                    'vendeurId'     => $vendeurId,
                    'productId'     => $product?->id,
                    'productName'   => $product?->name,
                    'productPrice'  => $product ? number_format($product->price, 0, ',', ' ') . ' ' . ($devise ?? 'GNF') : null,
                    'productPriceRaw' => $product ? (float)$product->price : 0,
                    'productImg'    => $product?->image ? asset('storage/'.$product->image) : null,
                    'devise'        => $devise ?? 'GNF',
                    'replyUrl'      => route('boutique.messages.reply', ['client' => $client->id ?? 0, 'product' => $product?->id ?? 0]),
                    'priceOfferUrl' => route('boutique.messages.price-offer'),
                    'refuseUrl'     => url('boutique/messages/refuse-proposal'),
                    'messages'      => $msgsData,
                ];
            @endphp

            <div class="msg-conv-item {{ $hasUnread ? 'unread' : '' }}"
                 onclick='openMsgModal(@json($convData))'>
                <div class="msg-conv-av">
                    {{ $cInit }}
                    @if($hasUnread)<div class="msg-unread-dot"></div>@endif
                </div>
               
                <div class="msg-conv-body">
                    <div class="msg-conv-name">
                        {{ $cName }}
                        @if($hasUnread)<span class="msg-new-pill">{{ $unreadNb }} new</span>@endif
                        @if($hasPendingProposal)<span class="msg-nego-badge proposal">💰 Proposition</span>@endif
                        @if($hasPendingOffer)<span class="msg-nego-badge offer">🏷️ Offre envoyée</span>@endif
                    </div>
                    @if($product)
                    <div class="msg-conv-prod">🏷️ {{ Str::limit($product->name, 24) }}</div>
                    @endif
                    <div class="msg-conv-preview {{ $hasUnread ? 'unread' : '' }}">
                        @if($lastMsg->sender_id === $vendeurId)<span style="color:var(--brand)">Vous : </span>@endif
                        {{ Str::limit($lastMsg->body, 50) }}
                    </div>
                </div>
                <div class="msg-conv-meta">
                    <span class="msg-conv-time">{{ $lastMsg->created_at->format('H:i') }}</span>
                    <span class="msg-conv-nb {{ $hasUnread ? 'unread' : '' }}">{{ $msgs->count() }}</span>
                </div>
            </div>
            @endforeach
        @else
        <div class="msg-drawer-empty">
            <span class="ico">💬</span>
            Aucun message pour l'instant.<br>
            <span style="font-size:12px">Les messages de vos clients apparaîtront ici.</span>
        </div>
        @endif
    </div>
</div>

{{-- ══ MODAL DISCUSSION ══ --}}
<div class="msg-modal-overlay" id="msgModalOverlay" onclick="if(event.target===this)closeMsgModal()">
    <div class="msg-modal" id="msgModal">
        <div class="msg-modal-hd">
            <div class="msg-modal-av" id="mmAv"></div>
            <div class="msg-modal-info">
                <div class="msg-modal-name" id="mmName"></div>
                <div class="msg-modal-sub" id="mmSub"></div>
            </div>
            <button class="msg-modal-close" onclick="closeMsgModal()">✕</button>
        </div>
        <div class="msg-modal-prod" id="mmProd" style="display:none"></div>
        <div class="msg-modal-thread" id="mmThread"></div>
        <div class="msg-modal-reply">
            <form id="mmForm" method="POST" style="display:flex;gap:10px;align-items:flex-end;width:100%">
                @csrf
                <input type="hidden" name="client_id" id="mmClientId">
                <input type="hidden" name="product_id" id="mmProductId">
                <textarea name="body" id="mmInput" class="msg-modal-input"
                          placeholder="Écrire un message…" rows="1" required
                          onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('mmForm').dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}))}"
                          oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
                <button type="submit" class="msg-modal-send">➤</button>
            </form>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="nego-toast" id="negoToast"></div>

{{-- ══ SCRIPTS ══ --}}
<script>
const VENDEUR_INIT = '{{ $vInit }}';

/* CSRF token récupéré de façon sécurisée */
function _csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        ?? document.querySelector('input[name="_token"]')?.value
        ?? '';
}

/* ── Toast ── */
function negoToast(msg, type = 'success') {
    const el = document.getElementById('negoToast');
    if (!el) return;
    el.textContent = msg;
    el.className   = 'nego-toast ' + type + ' show';
    setTimeout(() => el.classList.remove('show'), 3500);
}

/* ── Drawer ── */
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

/* ── Modal — ouvre IMMÉDIATEMENT puis peuple le contenu ── */
function openMsgModal(conv) {
    if (!conv || !conv.messages) return;

    /* 1. Fermer le drawer + ouvrir la modal */
    closeMsgDrawer();
    document.getElementById('msgModalOverlay').classList.add('open');
    window._currentConv = conv;

    /* 2. Header */
    document.getElementById('mmAv').textContent   = conv.clientInit || '?';
    document.getElementById('mmName').textContent = conv.clientName || 'Client';
    document.getElementById('mmSub').textContent  =
        conv.messages.length + ' message' + (conv.messages.length !== 1 ? 's' : '');

    /* 3. Barre produit — construction en une seule assignation */
    const prodBar = document.getElementById('mmProd');
    if (conv.productName) {
        const imgHtml = conv.productImg
            ? `<img src="${escHtml(conv.productImg)}" class="msg-modal-prod-img" alt="">`
            : `<div class="msg-modal-prod-ph">🏷️</div>`;
        const priceHtml = conv.productPrice
            ? `<div class="msg-modal-prod-price">${escHtml(conv.productPrice)}</div>` : '';
        prodBar.style.display = 'flex';
        const prodUrl = conv.productId ? `/produit/${conv.productId}` : '#';
        prodBar.innerHTML = imgHtml + `<div style="flex:1;min-width:0">
            <div class="msg-modal-prod-name">${escHtml(conv.productName)}</div>
            ${priceHtml}
        </div>
        <a href="${prodUrl}" target="_blank"
           style="flex-shrink:0;display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:var(--primary,#3b82f6);color:#fff;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap"
           title="Voir la fiche produit">
            🖼️ Voir le produit
        </a>`;
    } else {
        prodBar.style.display = 'none';
    }

    

    /* 4. Thread messages */
    const thread = document.getElementById('mmThread');
    thread.innerHTML = '';
    let lastDate = null;

    (conv.messages || []).forEach(function(msg) {
        /* Séparateur de date */
        if (msg.dateKey !== lastDate) {
            const sep = document.createElement('div');
            sep.className = 'msg-date-sep';
            sep.innerHTML = '<span>' + escHtml(msg.date || '') + '</span>';
            thread.appendChild(sep);
            lastDate = msg.dateKey;
        }
        thread.appendChild(buildMsgRow(msg, conv));
    });

    setTimeout(function() { thread.scrollTop = thread.scrollHeight; }, 60);

    /* 4b. Initialiser le dernier ID connu + démarrer le polling temps réel */
    const msgs = conv.messages || [];
    _lastMsgId = msgs.length > 0 ? Math.max(...msgs.map(m => m.id || 0)) : 0;
    startModalPolling();

    /* 5. Formulaire réponse */
    document.getElementById('mmForm').action     = conv.replyUrl || '#';
    document.getElementById('mmClientId').value  = conv.clientId || '';
    document.getElementById('mmProductId').value = conv.productId || '';
    document.getElementById('mmInput').value     = '';
    document.getElementById('mmInput').style.height = 'auto';

    /* 6. Marquer comme lu (async, non-bloquant) */
    try {
        fetch('{{ route("boutique.messages.read") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf() },
            body: JSON.stringify({ client_id: conv.clientId, product_id: conv.productId || null })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) { if (d && d.success) removeUnreadVisual(conv.clientId); })
        .catch(function() {});
    } catch(e) {}
}

/* ── Construire une ligne de message (utilisée partout : init + polling + AJAX) ── */
function buildMsgRow(msg, conv) {
    const row     = document.createElement('div');
    row.className = 'msg-bubble-row' + (msg.mine ? ' mine' : '');
    if (msg.id) row.dataset.msgId = msg.id;

    const tick = msg.mine
        ? (msg.read
            ? '<span class="msg-read-tick">✓✓ Lu</span>'
            : '<span class="msg-sent-tick">✓ Envoyé</span>')
        : '';

    const meta = '<div class="msg-bmeta"><span>' + escHtml(msg.time || '') + '</span>' + tick + '</div>';

    const msgType = msg.type || 'text';
    const isNego  = msgType === 'price_proposal' || msgType === 'price_offer' || msgType === 'order_created';
    const content = isNego
        ? buildNegoCard(msg, conv) + meta
        : '<div class="msg-bubble">' + escHtml(msg.body || '') + '</div>' + meta;

    if (msg.mine) {
        /* VENDEUR — droite : pas d'avatar, pas de nom, juste la bulle verte */
        row.innerHTML = '<div class="msg-bcontent">' + content + '</div>';
    } else {
        /* CLIENT — gauche : avatar bleu + nom + bulle blanche */
        const avInit = conv.clientInit || '?';
        row.innerHTML =
            '<div class="msg-bav">' + escHtml(avInit) + '</div>' +
            '<div class="msg-bcontent">' +
                '<div class="msg-bsender">' + escHtml(conv.clientName || 'Client') + '</div>' +
                content +
            '</div>';
    }
    return row;
}

/* ── Construire une carte de négociation ── */
function buildNegoCard(msg, conv) {
    const fmt = n => Math.round(n).toLocaleString('fr-FR') + ' ' + conv.devise;
    const disc = conv.productPriceRaw > 0
        ? Math.round((1 - msg.proposed_price / conv.productPriceRaw) * 100)
        : 0;

    if (msg.type === 'price_proposal') {
        // Proposition du client → le vendeur peut accepter ou refuser
        let actions = '';
        let statusHtml = '';

        if (msg.proposal_status === 'pending' && !msg.mine) {
            // Boutons action (visible seulement du côté vendeur, et si ce n'est pas sa propre proposition)
            actions = `
                <div class="nego-actions">
                    <button class="nego-btn-accept" onclick="showOfferForm(${msg.id}, ${msg.proposed_price || 0})">✓ Accepter &amp; faire une offre</button>
                    <button class="nego-btn-refuse" onclick="refuseProposalAction(${msg.id})">✕ Refuser</button>
                </div>
                <div class="nego-offer-form" id="offerForm_${msg.id}">
                    <div class="nego-offer-form-title">📝 Créer l'offre pour le client</div>
                    <div class="nego-offer-row">
                        <input type="number" class="nego-offer-input" id="offerPrice_${msg.id}"
                               value="${msg.proposed_price || ''}" min="1" step="500"
                               placeholder="Prix négocié…">
                        <span class="nego-offer-devise">${escHtml(conv.devise)}</span>
                        <button class="nego-offer-send" onclick="sendPriceOffer(${msg.id})">Envoyer l'offre</button>
                    </div>
                </div>`;
        } else if (msg.proposal_status === 'accepted') {
            statusHtml = `<div class="nego-card-status nego-status-accepted">✓ Proposition acceptée</div>`;
        } else if (msg.proposal_status === 'refused') {
            statusHtml = `<div class="nego-card-status nego-status-refused">✕ Proposition refusée</div>`;
        } else if (msg.proposal_status === 'pending') {
            statusHtml = `<div class="nego-card-status nego-status-pending">⏳ En attente de réponse…</div>`;
        }

        return `
            <div class="nego-card proposal">
                <div class="nego-card-header">💰 Proposition de prix</div>
                <div class="nego-card-body">
                    <div class="nego-card-amount">${fmt(msg.proposed_price)}</div>
                    ${conv.productPriceRaw ? `<div class="nego-card-original">Prix original : ${fmt(conv.productPriceRaw)}</div>` : ''}
                    ${disc > 0 ? `<div class="nego-card-discount">-${disc}%</div>` : ''}
                    ${statusHtml}
                    ${actions}
                </div>
            </div>`;
    }

    if (msg.type === 'price_offer') {
        // Offre envoyée par le vendeur
        let statusHtml = '';
        if (msg.proposal_status === 'pending') {
            statusHtml = `<div class="nego-card-status nego-status-pending">⏳ En attente de confirmation client…</div>`;
        } else if (msg.proposal_status === 'accepted') {
            statusHtml = `<div class="nego-card-status nego-status-accepted">✓ Confirmée — commande créée</div>
                ${msg.negotiated_order_id ? `<div class="nego-order-badge"><span class="nego-order-badge-ico">📦</span><div class="nego-order-badge-text">Commande n°${msg.negotiated_order_id}</div></div>` : ''}`;
        } else if (msg.proposal_status === 'refused') {
            statusHtml = `<div class="nego-card-status nego-status-refused">✕ Refusée par le client</div>`;
        }

        return `
            <div class="nego-card offer">
                <div class="nego-card-header">🏷️ Offre envoyée au client</div>
                <div class="nego-card-body">
                    <div class="nego-card-amount">${fmt(msg.proposed_price)}</div>
                    ${conv.productPriceRaw ? `<div class="nego-card-original">Prix original : ${fmt(conv.productPriceRaw)}</div>` : ''}
                    ${disc > 0 ? `<div class="nego-card-discount">-${disc}%</div>` : ''}
                    ${statusHtml}
                </div>
            </div>`;
    }

    if (msg.type === 'order_created') {
        return `
            <div class="nego-card order-ok">
                <div class="nego-card-header">📦 Commande confirmée !</div>
                <div class="nego-card-body">
                    <div class="nego-card-amount">${fmt(msg.proposed_price)}</div>
                    <div class="nego-card-status nego-status-accepted">✅ Commande n°${msg.negotiated_order_id || '—'}</div>
                    <div class="nego-order-badge" style="margin-top:8px">
                        <span class="nego-order-badge-ico">📦</span>
                        <div class="nego-order-badge-text">Prix négocié appliqué</div>
                    </div>
                </div>
            </div>`;
    }

    return `<div class="msg-bubble">${escHtml(msg.body)}</div>`;
}

/* ── Afficher le formulaire d'offre ── */
function showOfferForm(msgId, suggestedPrice) {
    const form = document.getElementById('offerForm_' + msgId);
    if (form) {
        form.classList.add('open');
        const input = document.getElementById('offerPrice_' + msgId);
        if (input) { input.value = suggestedPrice || ''; input.focus(); }
    }
}

/* ── Refuser une proposition ── */
async function refuseProposalAction(msgId) {
    if (!confirm('Refuser cette proposition de prix ?')) return;

    try {
        const conv = window._currentConv;
        const res  = await fetch(`${conv.refuseUrl}/${msgId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf() },
        });
        const data = await res.json();
        if (data.success) {
            negoToast('✕ Proposition refusée. Un message a été envoyé au client.', 'success');
            setTimeout(() => { closeMsgModal(); location.reload(); }, 1500);
        } else throw new Error();
    } catch(e) {
        negoToast('❌ Erreur. Réessayez.', 'error');
    }
}

/* ── Envoyer une offre de prix ── */
async function sendPriceOffer(proposalMsgId) {
    const conv  = window._currentConv;
    const input = document.getElementById('offerPrice_' + proposalMsgId);
    const price = parseFloat(input?.value);

    if (!price || price < 1) {
        negoToast('❌ Entrez un prix valide.', 'error');
        return;
    }

    const btn = input?.closest('.nego-offer-form')?.querySelector('.nego-offer-send');
    if (btn) { btn.disabled = true; btn.textContent = '⏳…'; }

    try {
        const res = await fetch(conv.priceOfferUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf(), 'Accept': 'application/json' },
            body: JSON.stringify({
                client_id:           conv.clientId,
                product_id:          conv.productId,
                offered_price:       price,
                proposal_message_id: proposalMsgId,
            }),
        });
        const data = await res.json();
        if (data.success) {
            negoToast('🏷️ Offre envoyée au client !', 'success');
            setTimeout(() => { closeMsgModal(); location.reload(); }, 1500);
        } else throw new Error();
    } catch(e) {
        if (btn) { btn.disabled = false; btn.textContent = 'Envoyer l\'offre'; }
        negoToast('❌ Erreur lors de l\'envoi.', 'error');
    }
}

/* ── Nettoyer l'affichage "non lu" sans reload ── */
function removeUnreadVisual(clientId) {
    const drawerBadge = document.querySelector('.msg-drawer-badge');
    if (drawerBadge) drawerBadge.style.display = 'none';

    const topbarBtn   = document.querySelector('.msg-topbar-btn');
    const topbarCount = document.querySelector('.msg-topbar-count');
    if (topbarCount) topbarCount.remove();
    if (topbarBtn)   topbarBtn.classList.remove('has-unread');

    document.querySelectorAll('.msg-conv-item').forEach(item => {
        const onclick = item.getAttribute('onclick') || '';
        if (onclick.includes(`"clientId":${clientId}`) || onclick.includes(`clientId":${clientId}`)) {
            item.classList.remove('unread');
            const dot  = item.querySelector('.msg-unread-dot');
            const pill = item.querySelector('.msg-new-pill');
            if (dot)  dot.remove();
            if (pill) pill.remove();
        }
    });
}

/* ── Polling temps réel du modal ── */
let _modalPollTimer = null;
let _lastMsgId      = 0; // ID du dernier message connu (pour détecter les nouveaux)

function startModalPolling() {
    stopModalPolling(); // On s'assure qu'il n'y a qu'un seul timer actif
    _modalPollTimer = setInterval(async function() {
        const conv = window._currentConv;
        if (!conv) return; // Si le modal est fermé, on ne fait rien

        try {
            // On construit l'URL avec les paramètres de la conversation ouverte
            const params = new URLSearchParams({ client_id: conv.clientId });
            if (conv.productId) params.append('product_id', conv.productId);

            const res  = await fetch('{{ route("boutique.messages.conversation") }}?' + params, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': _csrf() }
            });
            const data = await res.json();
            if (!data.messages) return;

            // On cherche les messages plus récents que le dernier connu
            const thread   = document.getElementById('mmThread');
            const newMsgs  = data.messages.filter(m => m.id > _lastMsgId);

            if (newMsgs.length > 0) {
                newMsgs.forEach(function(msg) {
                    // On ne rajoute pas les messages déjà affichés
                    if (document.querySelector('[data-msg-id="' + msg.id + '"]')) return;

                    // Si c'est un nouveau groupe de date, on ajoute le séparateur
                    const lastSep = thread.querySelector('.msg-date-sep:last-of-type');
                    if (!lastSep || lastSep.dataset.dateKey !== msg.dateKey) {
                        const sep = document.createElement('div');
                        sep.className = 'msg-date-sep';
                        sep.dataset.dateKey = msg.dateKey;
                        sep.innerHTML = '<span>' + escHtml(msg.date || '') + '</span>';
                        thread.appendChild(sep);
                    }

                    thread.appendChild(buildMsgRow(msg, conv));
                });

                // On met à jour le dernier ID connu
                _lastMsgId = data.messages[data.messages.length - 1].id;

                // On fait défiler vers le bas automatiquement
                thread.scrollTop = thread.scrollHeight;

                // On met à jour le compteur de messages dans le header
                const total = data.messages.length;
                document.getElementById('mmSub').textContent = total + ' message' + (total !== 1 ? 's' : '');
            }
        } catch(e) { /* Erreur réseau silencieuse */ }
    }, 3000); // Toutes les 3 secondes
}

function stopModalPolling() {
    if (_modalPollTimer) {
        clearInterval(_modalPollTimer);
        _modalPollTimer = null;
    }
}

function closeMsgModal() {
    document.getElementById('msgModalOverlay').classList.remove('open');
    stopModalPolling(); // On arrête le polling quand le modal se ferme
    window._currentConv = null;
    _lastMsgId = 0;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeMsgModal(); closeMsgDrawer(); }
});

/* ── Soumission du formulaire de réponse en AJAX (sans recharger la page) ── */
document.getElementById('mmForm').addEventListener('submit', async function(e) {
    e.preventDefault(); // On bloque la soumission normale (qui rechargerait la page)

    const conv  = window._currentConv;
    const input = document.getElementById('mmInput');
    const body  = input.value.trim();

    // Si le message est vide, on ne fait rien
    if (!body) return;

    const btn = this.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = '⏳'; }

    try {
        // On envoie le message via fetch (sans recharger la page)
        const res = await fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _csrf(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                body:       body,
                client_id:  document.getElementById('mmClientId').value,
                product_id: document.getElementById('mmProductId').value || null,
            }),
        });

        const data = await res.json();

        if (data.success || data.sent) {
            // On vide le champ de saisie
            input.value = '';
            input.style.height = 'auto';

            // On ajoute le message dans le thread
            const thread = document.getElementById('mmThread');
            const now    = new Date();
            const time   = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');

            const fakeMsg = {
                id: data.message_id || 0,
                mine: true,
                body: body,
                time: time,
                read: false,
                type: 'text',
            };
            if (data.message_id) _lastMsgId = Math.max(_lastMsgId, data.message_id);
            thread.appendChild(buildMsgRow(fakeMsg, window._currentConv));

            thread.scrollTop = thread.scrollHeight;
        } else {
            negoToast('❌ Erreur lors de l\'envoi.', 'error');
        }
    } catch(e) {
        negoToast('❌ Erreur réseau. Réessayez.', 'error');
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = '➤'; }
    }
});
</script>
