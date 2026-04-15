{{--
    resources/views/boutique/_partials/messages_btn_and_drawer.blade.php

    1) Ajoute un bouton 💬 dans la topbar (à inclure DANS .tb-actions)
    2) Drawer liste des conversations (slide-in depuis la droite)
    3) Modal discussion par client (ouvre au clic sur une conversation)

    Include dans boutique/dashboard.blade.php :
    ① Dans la topbar, avant le bouton "+ Commande" :
         @include('boutique._partials.messages_btn_and_drawer')

    Variables requises passées via le controller :
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
.msg-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 900;
    animation: fadeIn .2s ease;
}
.msg-overlay.open { display: block; }
@keyframes fadeIn { from{opacity:0}to{opacity:1} }

/* ── Drawer conversations ── */
.msg-drawer {
    position: fixed; top: 0; right: 0; bottom: 0;
    width: 380px; max-width: 95vw;
    background: var(--surface);
    border-left: 1px solid var(--border);
    box-shadow: -8px 0 40px rgba(0,0,0,.15);
    z-index: 901; display: flex; flex-direction: column;
    transform: translateX(110%);
    transition: transform .28s cubic-bezier(.23,1,.32,1);
}
.msg-drawer.open { transform: translateX(0); }

/* Header drawer */
.msg-drawer-hd {
    padding: 16px 18px;
    border-bottom: 1px solid var(--border);
    background: var(--sb-bg);
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
}
.msg-drawer-title {
    font-size: 14px; font-weight: 800; color: #fff;
    display: flex; align-items: center; gap: 9px;
}
.msg-drawer-badge {
    background: #ef4444; color: #fff;
    font-size: 9px; font-weight: 800;
    padding: 2px 7px; border-radius: 20px;
    font-family: var(--mono);
    animation: pulse-red 2s ease-in-out infinite;
}
@keyframes pulse-red { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.5)} 50%{box-shadow:0 0 0 6px rgba(239,68,68,0)} }
.msg-drawer-close {
    width: 32px; height: 32px; border-radius: 8px;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,255,255,.7); font-size: 16px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all .15s; flex-shrink: 0;
}
.msg-drawer-close:hover { background: rgba(239,68,68,.25); color: #fca5a5; border-color: rgba(239,68,68,.3); }

/* Sous-header info */
.msg-drawer-sub {
    padding: 8px 18px;
    background: rgba(16,185,129,.06);
    border-bottom: 1px solid var(--border);
    font-size: 11px; color: var(--muted);
    flex-shrink: 0;
}

/* Liste conversations */
.msg-drawer-list {
    flex: 1; overflow-y: auto;
}
.msg-drawer-list::-webkit-scrollbar { width: 4px; }
.msg-drawer-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

/* Item conversation */
.msg-conv-item {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 18px;
    border-bottom: 1px solid #f3f6f4;
    cursor: pointer; transition: background .12s;
    position: relative;
}
.msg-conv-item:hover { background: var(--bg); }
.msg-conv-item.unread { background: #fefcf0; }
.msg-conv-item.unread:hover { background: #fdf8e0; }
.msg-conv-item.unread::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: var(--brand); border-radius: 0 2px 2px 0;
}

/* Avatar */
.msg-conv-av {
    width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    position: relative;
}
.msg-conv-av .msg-unread-dot {
    position: absolute; bottom: 1px; right: 1px;
    width: 11px; height: 11px; border-radius: 50%;
    background: #ef4444; border: 2px solid var(--surface);
}

/* Infos */
.msg-conv-body { flex: 1; min-width: 0; }
.msg-conv-name {
    font-size: 13px; font-weight: 700; color: var(--text);
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 3px;
}
.msg-new-pill {
    font-size: 9px; font-weight: 800; background: #ef4444; color: #fff;
    padding: 1px 6px; border-radius: 10px; text-transform: uppercase; flex-shrink: 0;
}
.msg-conv-prod {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 600; color: var(--brand-dk);
    background: var(--brand-mlt); border: 1px solid var(--brand-lt);
    padding: 1px 7px; border-radius: 20px;
    margin-bottom: 3px; max-width: 100%;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.msg-conv-preview {
    font-size: 12px; color: var(--muted);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.msg-conv-preview.unread { color: var(--text-2); font-weight: 600; }

/* Meta droite */
.msg-conv-meta {
    display: flex; flex-direction: column; align-items: flex-end;
    gap: 5px; flex-shrink: 0;
}
.msg-conv-time { font-size: 10.5px; color: var(--muted); }
.msg-conv-nb {
    font-size: 10px; font-weight: 700; font-family: var(--mono);
    padding: 1px 7px; border-radius: 20px; white-space: nowrap;
    background: var(--bg); border: 1px solid var(--border); color: var(--muted);
}
.msg-conv-nb.unread { background: var(--brand); border-color: var(--brand-dk); color: #fff; }

/* Etat vide */
.msg-drawer-empty {
    padding: 48px 20px; text-align: center;
    font-size: 13px; color: var(--muted);
}
.msg-drawer-empty .ico { font-size: 36px; display: block; margin-bottom: 10px; opacity: .35; }

/* ── Modal discussion ── */
.msg-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 1000;
    align-items: center; justify-content: center;
    padding: 16px;
}
.msg-modal-overlay.open { display: flex; }

.msg-modal {
    background: var(--surface); border-radius: var(--r);
    width: 100%; max-width: 560px; max-height: 88vh;
    display: flex; flex-direction: column;
    box-shadow: 0 24px 80px rgba(0,0,0,.25);
    animation: modalIn .22s cubic-bezier(.23,1,.32,1);
    overflow: hidden;
}
@keyframes modalIn { from{opacity:0;transform:translateY(-16px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)} }

/* Header modal */
.msg-modal-hd {
    background: var(--sb-bg);
    padding: 14px 18px;
    display: flex; align-items: center; gap: 12px;
    flex-shrink: 0;
}
.msg-modal-av {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 800; color: #fff;
}
.msg-modal-info { flex: 1; min-width: 0; }
.msg-modal-name { font-size: 14px; font-weight: 800; color: #fff; }
.msg-modal-sub { font-size: 11px; color: rgba(255,255,255,.5); margin-top: 2px; }
.msg-modal-close {
    width: 32px; height: 32px; border-radius: 8px;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,255,255,.6); font-size: 16px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all .15s; flex-shrink: 0;
}
.msg-modal-close:hover { background: rgba(239,68,68,.25); color: #fca5a5; }

/* Barre produit */
.msg-modal-prod {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 18px;
    background: linear-gradient(90deg, #ecfdf5, #f0fdf4);
    border-bottom: 1px solid #d1fae5;
    flex-shrink: 0;
}
.msg-modal-prod-img {
    width: 38px; height: 38px; border-radius: var(--r-sm);
    object-fit: cover; border: 1px solid var(--border); flex-shrink: 0;
}
.msg-modal-prod-ph {
    width: 38px; height: 38px; border-radius: var(--r-sm);
    background: var(--bg); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}
.msg-modal-prod-name { font-size: 12.5px; font-weight: 700; color: var(--text); }
.msg-modal-prod-price { font-size: 11.5px; color: var(--brand); font-weight: 800; font-family: var(--mono); }

/* Thread */
.msg-modal-thread {
    flex: 1; overflow-y: auto;
    padding: 16px 18px;
    display: flex; flex-direction: column; gap: 10px;
    background: #fafcfb;
}
.msg-modal-thread::-webkit-scrollbar { width: 4px; }
.msg-modal-thread::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

/* Séparateur date */
.msg-date-sep {
    text-align: center; font-size: 10px; color: var(--muted);
    font-weight: 600; display: flex; align-items: center; gap: 8px;
    margin: 4px 0;
}
.msg-date-sep::before, .msg-date-sep::after {
    content: ''; flex: 1; height: 1px; background: var(--border);
}

/* Bulles */
.msg-bubble-row { display: flex; gap: 8px; max-width: 82%; }
.msg-bubble-row.mine { margin-left: auto; flex-direction: row-reverse; }
.msg-bav {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 800; color: #fff;
    align-self: flex-end;
}
.msg-bubble-row:not(.mine) .msg-bav { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.msg-bubble-row.mine .msg-bav { background: linear-gradient(135deg, var(--brand), #059669); }
.msg-bcontent { display: flex; flex-direction: column; gap: 3px; }
.msg-bsender { font-size: 10px; font-weight: 600; color: var(--muted); }
.msg-bubble-row.mine .msg-bsender { text-align: right; }
.msg-bubble {
    padding: 9px 13px; border-radius: 16px;
    font-size: 13px; line-height: 1.5; word-break: break-word;
}
.msg-bubble-row:not(.mine) .msg-bubble {
    background: var(--surface); border: 1px solid var(--border);
    color: var(--text); border-bottom-left-radius: 3px;
    box-shadow: 0 1px 2px rgba(0,0,0,.06);
}
.msg-bubble-row.mine .msg-bubble {
    background: var(--brand); color: #fff;
    border-bottom-right-radius: 3px;
}
.msg-bmeta {
    font-size: 9.5px; color: var(--muted);
    display: flex; align-items: center; gap: 5px;
}
.msg-bubble-row.mine .msg-bmeta { justify-content: flex-end; }
.msg-read-tick { color: var(--brand); }

/* Zone réponse */
.msg-modal-reply {
    padding: 12px 18px;
    border-top: 1px solid var(--border);
    background: var(--surface);
    display: flex; gap: 10px; align-items: flex-end;
    flex-shrink: 0;
}
.msg-modal-input {
    flex: 1; padding: 10px 14px;
    border: 1.5px solid var(--border); border-radius: 22px;
    font-size: 13px; font-family: var(--font); color: var(--text);
    background: var(--bg); outline: none; resize: none;
    min-height: 40px; max-height: 100px; line-height: 1.45;
    transition: border-color .15s, box-shadow .15s;
}
.msg-modal-input:focus {
    border-color: var(--brand); background: var(--surface);
    box-shadow: 0 0 0 3px rgba(16,185,129,.12);
}
.msg-modal-send {
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--brand); color: #fff; border: none;
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; font-size: 15px; flex-shrink: 0;
    transition: all .15s; box-shadow: 0 3px 10px rgba(16,185,129,.35);
}
.msg-modal-send:hover { background: var(--brand-dk); transform: scale(1.08); }

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
                $convId    = 'conv-' . $loop->index;

                // Données pour le modal (JSON encodé)
                $msgsData = $msgs->map(fn($m) => [
                    'id'       => $m->id,
                    'body'     => $m->body,
                    'mine'     => $m->sender_id === $vendeurId,
                    'sender'   => $m->sender->name ?? 'Inconnu',
                    'time'     => $m->created_at->format('H:i'),
                    'date'     => $m->created_at->isToday() ? "Aujourd'hui" : ($m->created_at->isYesterday() ? 'Hier' : $m->created_at->format('d/m/Y')),
                    'dateKey'  => $m->created_at->toDateString(),
                    'read'     => !is_null($m->read_at),
                ])->values()->toArray();

                $convData = [
                    'clientId'   => $client->id ?? 0,
                    'clientName' => $cName,
                    'clientInit' => $cInit,
                    'vendeurInit'=> $vInit,
                    'productId'  => $product?->id,
                    'productName'=> $product?->name,
                    'productPrice'=> $product ? number_format($product->price, 0, ',', ' ') . ' ' . ($devise ?? 'GNF') : null,
                    'productImg' => $product?->image ? asset('storage/'.$product->image) : null,
                    'replyUrl'   => route('boutique.messages.reply', ['client' => $client->id ?? 0, 'product' => $product?->id ?? 0]),
                    'messages'   => $msgsData,
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

{{-- ══ SCRIPTS ══ --}}
<script>
const VENDEUR_INIT = '{{ $vInit }}';

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

/* ── Modal ── */
f/* ── Modal ── */
/* ── Modal ── */
/* ── Modal ── */
function openMsgModal(conv) {
    
    // 1. Marquer comme lu sur le serveur
    fetch('{{ route("boutique.messages.read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            client_id: conv.clientId,
            product_id: conv.productId || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            removeUnreadVisual(conv.clientId);   // Mise à jour immédiate du drawer
        }
    })
    .catch(err => console.error('Erreur mark as read:', err));

    // Affichage du modal (ton code reste le même)
    document.getElementById('mmAv').textContent   = conv.clientInit;
    document.getElementById('mmName').textContent = conv.clientName;
    document.getElementById('mmSub').textContent  = conv.messages.length + ' message' + (conv.messages.length > 1 ? 's' : '');

    const prodBar = document.getElementById('mmProd');
    if (conv.productName) {
        prodBar.style.display = 'flex';
        prodBar.innerHTML = conv.productImg
            ? `<img src="${conv.productImg}" class="msg-modal-prod-img" alt="">`
            : `<div class="msg-modal-prod-ph">🏷️</div>`;
        prodBar.innerHTML += `<div>
            <div class="msg-modal-prod-name">${conv.productName}</div>
            ${conv.productPrice ? `<div class="msg-modal-prod-price">${conv.productPrice}</div>` : ''}
        </div>`;
    } else {
        prodBar.style.display = 'none';
    }

    const thread = document.getElementById('mmThread');
    thread.innerHTML = '';
    let lastDate = null;

    conv.messages.forEach(msg => {
        if (msg.dateKey !== lastDate) {
            const sep = document.createElement('div');
            sep.className = 'msg-date-sep';
            sep.textContent = msg.date;
            thread.appendChild(sep);
            lastDate = msg.dateKey;
        }

        const row = document.createElement('div');
        row.className = 'msg-bubble-row' + (msg.mine ? ' mine' : '');
        const avInit = msg.mine ? VENDEUR_INIT : conv.clientInit;
        row.innerHTML = `
            <div class="msg-bav">${avInit}</div>
            <div class="msg-bcontent">
                <div class="msg-bsender">${msg.mine ? 'Vous' : conv.clientName}</div>
                <div class="msg-bubble">${escHtml(msg.body)}</div>
                <div class="msg-bmeta">
                    <span>${msg.time}</span>
                    ${msg.mine ? (msg.read ? '<span class="msg-read-tick" title="Lu">✓✓ Lu</span>' : '<span style="font-size:9px">✓ Envoyé</span>') : ''}
                </div>
            </div>`;
        thread.appendChild(row);
    });

    setTimeout(() => { thread.scrollTop = thread.scrollHeight; }, 30);

    document.getElementById('mmForm').action  = conv.replyUrl;
    document.getElementById('mmClientId').value  = conv.clientId;
    document.getElementById('mmProductId').value = conv.productId || '';
    document.getElementById('mmInput').value     = '';
    document.getElementById('mmInput').style.height = 'auto';

    document.getElementById('msgModalOverlay').classList.add('open');
}

/* Mise à jour visuelle immédiate sans refresh */
function removeUnreadVisual(clientId) {
    // 1. Supprimer le badge du drawer
    const drawerBadge = document.querySelector('.msg-drawer-badge');
    if (drawerBadge) {
        drawerBadge.style.display = 'none';
    }

    // 2. Supprimer le badge du bouton topbar
    const topbarBtn = document.querySelector('.msg-topbar-btn');
    const topbarCount = document.querySelector('.msg-topbar-count');
    if (topbarCount) topbarCount.remove();
    topbarBtn.classList.remove('has-unread');

    // 3. Supprimer le style "unread" de la conversation concernée
    const convItems = document.querySelectorAll('.msg-conv-item');
    convItems.forEach(item => {
        const onclickText = item.getAttribute('onclick') || '';
        
        if (onclickText.includes(`clientId":${clientId}`) || 
            onclickText.includes(`"clientId":${clientId}`) ||
            onclickText.includes(`client_id":${clientId}`)) {
            
            item.classList.remove('unread');
            
            // Supprimer le point rouge
            const dot = item.querySelector('.msg-unread-dot');
            if (dot) dot.remove();
            
            // Supprimer le pill "NEW"
            const pill = item.querySelector('.msg-new-pill');
            if (pill) pill.remove();
        }
    });
}
function closeMsgModal() {
    document.getElementById('msgModalOverlay').classList.remove('open');
}
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}



/* Clavier */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeMsgModal(); closeMsgDrawer(); }
});
</script>