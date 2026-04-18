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

/* Thread */
.msg-modal-thread { flex: 1; overflow-y: auto; padding: 16px 18px; display: flex; flex-direction: column; gap: 10px; background: #fafcfb; }
.msg-modal-thread::-webkit-scrollbar { width: 4px; }
.msg-modal-thread::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.msg-date-sep { text-align: center; font-size: 10px; color: var(--muted); font-weight: 600; display: flex; align-items: center; gap: 8px; margin: 4px 0; }
.msg-date-sep::before, .msg-date-sep::after { content: ''; flex: 1; height: 1px; background: var(--border); }

/* Bulles normales */
.msg-bubble-row { display: flex; gap: 8px; max-width: 82%; }
.msg-bubble-row.mine { margin-left: auto; flex-direction: row-reverse; }
.msg-bav { width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 800; color: #fff; align-self: flex-end; }
.msg-bubble-row:not(.mine) .msg-bav { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.msg-bubble-row.mine .msg-bav { background: linear-gradient(135deg, var(--brand), #059669); }
.msg-bcontent { display: flex; flex-direction: column; gap: 3px; }
.msg-bsender { font-size: 10px; font-weight: 600; color: var(--muted); }
.msg-bubble-row.mine .msg-bsender { text-align: right; }
.msg-bubble { padding: 9px 13px; border-radius: 16px; font-size: 13px; line-height: 1.5; word-break: break-word; }
.msg-bubble-row:not(.mine) .msg-bubble { background: var(--surface); border: 1px solid var(--border); color: var(--text); border-bottom-left-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,.06); }
.msg-bubble-row.mine .msg-bubble { background: var(--brand); color: #fff; border-bottom-right-radius: 3px; }
.msg-bmeta { font-size: 9.5px; color: var(--muted); display: flex; align-items: center; gap: 5px; }
.msg-bubble-row.mine .msg-bmeta { justify-content: flex-end; }
.msg-read-tick { color: var(--brand); }

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

{{-- ══ BOUTON TOPBAR ══ --}}
<button class="msg-topbar-btn {{ $totalUnread > 0 ? 'has-unread' : '' }}"
        onclick="openMsgDrawer()" type="button">
    💬 <span class="btn-label">Messages</span>
    @if($totalUnread > 0)
    <span class="msg-topbar-count">{{ $totalUnread }}</span>
    @endif
</button>

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
                          onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('mmForm').submit()}"
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

    /* 1. Ouvrir la modal en premier (avant tout traitement) */
    document.getElementById('msgModalOverlay').classList.add('open');
    window._currentConv = conv;

    /* 2. Header */
    document.getElementById('mmAv').textContent   = conv.clientInit || '?';
    document.getElementById('mmName').textContent = conv.clientName || 'Client';
    document.getElementById('mmSub').textContent  =
        conv.messages.length + ' message' + (conv.messages.length !== 1 ? 's' : '');

    /* 3. Barre produit */
    const prodBar = document.getElementById('mmProd');
    if (conv.productName) {
        prodBar.style.display = 'flex';
        prodBar.innerHTML = conv.productImg
            ? `<img src="${escHtml(conv.productImg)}" class="msg-modal-prod-img" alt="">`
            : `<div class="msg-modal-prod-ph">🏷️</div>`;
        prodBar.innerHTML += `<div>
            <div class="msg-modal-prod-name">${escHtml(conv.productName)}</div>
            ${conv.productPrice ? `<div class="msg-modal-prod-price">${escHtml(conv.productPrice)}</div>` : ''}
        </div>`;
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
            sep.className   = 'msg-date-sep';
            sep.textContent = msg.date || '';
            thread.appendChild(sep);
            lastDate = msg.dateKey;
        }

        const row    = document.createElement('div');
        const avInit = msg.mine ? VENDEUR_INIT : (conv.clientInit || '?');
        const msgType = msg.type || 'text';

        if (msgType === 'price_proposal' || msgType === 'price_offer' || msgType === 'order_created') {
            /* ── Carte négociation ── */
            row.className = 'msg-bubble-row' + (msg.mine ? ' mine' : '');
            row.innerHTML =
                '<div class="msg-bav">' + escHtml(avInit) + '</div>' +
                '<div class="msg-bcontent">' +
                    '<div class="msg-bsender">' + (msg.mine ? 'Vous' : escHtml(conv.clientName || '')) + '</div>' +
                    buildNegoCard(msg, conv) +
                    '<div class="msg-bmeta">' +
                        '<span>' + escHtml(msg.time || '') + '</span>' +
                        (msg.mine ? (msg.read ? '<span class="msg-read-tick">✓✓ Lu</span>' : '<span style="font-size:9px">✓ Envoyé</span>') : '') +
                    '</div>' +
                '</div>';
        } else {
            /* ── Message texte normal ── */
            row.className = 'msg-bubble-row' + (msg.mine ? ' mine' : '');
            row.innerHTML =
                '<div class="msg-bav">' + escHtml(avInit) + '</div>' +
                '<div class="msg-bcontent">' +
                    '<div class="msg-bsender">' + (msg.mine ? 'Vous' : escHtml(conv.clientName || '')) + '</div>' +
                    '<div class="msg-bubble">' + escHtml(msg.body || '') + '</div>' +
                    '<div class="msg-bmeta">' +
                        '<span>' + escHtml(msg.time || '') + '</span>' +
                        (msg.mine ? (msg.read ? '<span class="msg-read-tick">✓✓ Lu</span>' : '<span style="font-size:9px">✓ Envoyé</span>') : '') +
                    '</div>' +
                '</div>';
        }

        thread.appendChild(row);
    });

    setTimeout(function() { thread.scrollTop = thread.scrollHeight; }, 60);

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

function closeMsgModal() {
    document.getElementById('msgModalOverlay').classList.remove('open');
    window._currentConv = null;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeMsgModal(); closeMsgDrawer(); }
});
</script>
