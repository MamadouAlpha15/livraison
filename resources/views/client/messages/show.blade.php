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

/* RESPONSIVE */
@media (max-width: 600px) {
    .chat-wrap { margin: 12px auto; padding: 0 8px 70px; }
    .chat-thread { max-height: 400px; padding: 14px 12px; }
    .msg-row { max-width: 90%; }
    .chat-prod-header { padding: 10px 12px; gap: 10px; flex-wrap: wrap; }
    .price-card { max-width: 100%; }
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

        @if(in_array($msgType, ['price_proposal', 'price_offer', 'order_created']))
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
    <div class="chat-input-zone">
        <form method="POST"
              action="{{ route('client.messages.store', $product) }}"
              id="chatForm"
              style="display:flex;gap:10px;align-items:flex-end;width:100%">
            @csrf
            <textarea name="body"
                      id="chatInput"
                      class="chat-textarea"
                      placeholder="Écrire un message au vendeur…"
                      rows="1"
                      required
                      onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage()}"
                      oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'"></textarea>
            <button type="button" onclick="sendMessage()" class="chat-send-btn" title="Envoyer">➤</button>
        </form>
    </div>

</div>

{{-- Toast global --}}
<div class="chat-toast" id="chatToast"></div>

@endsection

@push('scripts')
<script>
const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const DEVISE    = '{{ $devise }}';
const INITIALS  = '{{ $initials }}';
const STORE_URL = '{{ route("client.messages.store", $product) }}';
const POLL_URL  = '{{ route("client.messages.index", $product) }}';
const CONFIRM_URL = '{{ url("client/messages/confirm-offer") }}';
const PROPOSE_URL = '{{ route("client.messages.propose") }}';
const PRODUCT_ID  = {{ $product->id }};
const PRODUCT_PRICE = {{ $product->price }};

/* Dernier ID message connu — pour ne pas dupliquer */
let _lastMsgId = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};

/* ── Auto-scroll au chargement ── */
document.addEventListener('DOMContentLoaded', () => scrollBottom());
function scrollBottom() {
    const t = document.getElementById('chatThread');
    if (t) t.scrollTop = t.scrollHeight;
}

/* ── Toast ── */
function showToast(msg, type = 'success') {
    const el = document.getElementById('chatToast');
    el.textContent = msg;
    el.className = 'chat-toast ' + type + ' show';
    setTimeout(() => el.classList.remove('show'), 3500);
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── Panneau proposition ── */
function toggleProposePanel() {
    const panel = document.getElementById('proposePanel');
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) document.getElementById('proposePriceInput').focus();
}

/* ── Construire une bulle texte simple ── */
function buildTextRow(msg) {
    const row = document.createElement('div');
    row.className = 'msg-row ' + (msg.mine ? 'mine' : 'theirs');
    if (msg.id) row.dataset.msgId = msg.id;

    const tick = msg.mine
        ? (msg.read ? '<span class="msg-read">✓✓</span>' : '')
        : '';

    if (msg.mine) {
        /* CLIENT — droite : pas d'avatar, pas de nom */
        row.innerHTML =
            '<div class="msg-content">' +
                '<div class="msg-bubble">' + escHtml(msg.body) + '</div>' +
                '<div class="msg-meta"><span class="msg-time">' + escHtml(msg.time) + '</span>' + tick + '</div>' +
            '</div>';
    } else {
        /* VENDEUR — gauche : avatar + nom */
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

/* ── Envoyer un message texte en AJAX ── */
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
            input.value = '';
            input.style.height = 'auto';

            const now  = new Date();
            const time = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
            const fakeMsg = { mine: true, body, time, read: false, id: data.message_id || 0 };

            if (data.message_id) _lastMsgId = Math.max(_lastMsgId, data.message_id);

            const thread = document.getElementById('chatThread');
            /* Retirer le message "vide" si présent */
            const empty = thread.querySelector('.chat-empty');
            if (empty) empty.remove();

            thread.appendChild(buildTextRow(fakeMsg));
            scrollBottom();
        }
    } catch(e) {
        showToast('❌ Erreur réseau. Réessayez.', 'error');
    } finally {
        if (btn) btn.disabled = false;
    }
}

/* ── Envoyer une proposition de prix ── */
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
    } catch(e) {
        showToast('❌ Erreur lors de l\'envoi. Réessayez.', 'error');
    }
}

/* ── Confirmer une offre vendeur ── */
async function confirmOffer(messageId, btn) {
    if (!confirm('Confirmer cette offre et créer la commande ?')) return;
    btn.disabled = true;
    btn.classList.add('loading');
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
        btn.disabled = false;
        btn.classList.remove('loading');
        btn.textContent = '✓ Confirmer cette offre et commander';
        showToast('❌ Erreur. Réessayez.', 'error');
    }
}

/* ── Polling toutes les 3s — ajoute les nouveaux messages sans recharger ── */
async function pollMessages() {
    try {
        const res = await fetch(POLL_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!res.ok) return;
        const msgs = await res.json();

        const thread  = document.getElementById('chatThread');
        const newMsgs = msgs.filter(m => m.id > _lastMsgId);

        for (const msg of newMsgs) {
            /* Éviter les doublons */
            if (thread.querySelector('[data-msg-id="' + msg.id + '"]')) continue;

            /* Cartes de négociation → reload pour afficher les boutons PHP */
            if (msg.type && msg.type !== 'text') {
                location.reload();
                return;
            }

            /* Message texte du vendeur → on l'ajoute directement */
            if (!msg.mine) {
                const empty = thread.querySelector('.chat-empty');
                if (empty) empty.remove();

                /* Calculer les initiales du vendeur depuis le nom */
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

/* ── Polling badge navbar (toutes les 3s) ── */
async function pollBadge() {
    try {
        const res = await fetch('{{ route("client.messages.client.poll") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        });
        if (!res.ok) return;
        const data = await res.json();
        const badge = document.getElementById('navMsgBadge');
        if (!badge) return;
        if (data.unread > 0) {
            badge.textContent = data.unread;
            badge.classList.add('show');
        } else {
            badge.textContent = '';
            badge.classList.remove('show');
        }
    } catch(e) {}
}
setInterval(pollBadge, 3000);
</script>
@endpush
