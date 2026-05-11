@extends('layouts.app')
@section('title', 'Chat · '.$company->name)
@php $bodyClass = 'is-dashboard'; @endphp



@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --bg:#f0f2f5;--surface:#ffffff;--surface2:#f7f8fa;--border:rgba(0,0,0,.09);
    --brand:#7c3aed;--brand2:#6d28d9;--brand-lt:#7c3aed;
    --text:#1a202c;--text2:#4a5568;--muted:#9ca3af;
    --green:#059669;--green-lt:#d1fae5;--red:#ef4444;
    --r:14px;--r-sm:10px;--r-xs:7px;
}

/* Flex column sur body + main pour que le chat remplisse l'espace restant */
body{display:flex!important;flex-direction:column!important;min-height:100vh!important;}
main.app-main{
    flex:1!important;display:flex!important;flex-direction:column!important;
    padding:0!important;margin:0!important;
    max-width:100%!important;width:100%!important;min-height:0!important;
}

/* ══ LAYOUT PRINCIPAL ══ */
.chat-page{
    flex:1;min-height:0;
    display:flex;overflow:hidden;
    background:var(--bg);
    font-family:'Segoe UI',system-ui,sans-serif;
    -webkit-font-smoothing:antialiased;
}

/* ══ ZONE CHAT ══ */
.chat-area{
    flex:1;min-width:0;display:flex;flex-direction:column;
    background:var(--bg);
}

/* Header */
.chat-header{
    height:62px;background:var(--surface);
    border-bottom:1px solid var(--border);
    display:flex;align-items:center;gap:12px;
    padding:0 16px;flex-shrink:0;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
    position:sticky;top:0;z-index:10;
}
.chat-back{
    width:34px;height:34px;border-radius:9px;flex-shrink:0;
    background:var(--surface2);border:1px solid var(--border);
    display:flex;align-items:center;justify-content:center;
    font-size:15px;cursor:pointer;transition:background .14s;color:var(--text2);
    text-decoration:none;
}
.chat-back:hover{background:#ede9fe;color:var(--brand);}
.chat-av{
    width:40px;height:40px;border-radius:11px;flex-shrink:0;overflow:hidden;
    background:linear-gradient(135deg,var(--brand),#4f46e5);
    display:flex;align-items:center;justify-content:center;
    font-size:14px;font-weight:800;color:#fff;
}
.chat-av img{width:100%;height:100%;object-fit:cover;}
.chat-hd-name{font-size:14.5px;font-weight:800;color:var(--text);}
.chat-hd-sub{font-size:11px;color:var(--muted);margin-top:1px;}
.chat-online{
    margin-left:auto;display:flex;align-items:center;gap:5px;flex-shrink:0;
    font-size:11px;font-weight:700;color:var(--green);
    padding:4px 11px;border-radius:20px;
    background:var(--green-lt);border:1px solid rgba(5,150,105,.2);
}
.online-dot{width:6px;height:6px;border-radius:50%;background:var(--green);animation:blink 2s ease infinite;flex-shrink:0;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}

/* Bouton panel toggle (mobile/tablette) */
.btn-panel-toggle{
    display:none;
    width:34px;height:34px;border-radius:9px;border:1px solid var(--border);
    background:var(--surface2);cursor:pointer;
    align-items:center;justify-content:center;flex-shrink:0;
    font-size:16px;color:var(--text2);transition:background .14s;
    margin-left:8px;
}
.btn-panel-toggle:hover{background:#ede9fe;color:var(--brand);}

/* Messages */
.chat-msgs{
    flex:1;overflow-y:auto;padding:18px 16px;
    display:flex;flex-direction:column;gap:6px;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.4) rgba(0,0,0,.05);
}
.chat-msgs::-webkit-scrollbar{width:6px;}
.chat-msgs::-webkit-scrollbar-track{background:rgba(0,0,0,.04);border-radius:6px;}
.chat-msgs::-webkit-scrollbar-thumb{background:rgba(124,58,237,.4);border-radius:6px;}
.chat-msgs::-webkit-scrollbar-thumb:hover{background:rgba(124,58,237,.7);}

.msg-empty{
    flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    color:var(--muted);text-align:center;gap:10px;padding:40px;
}
.msg-empty-ico{font-size:44px;opacity:.5;}
.msg-empty-txt{font-size:13px;line-height:1.7;max-width:240px;color:var(--text2);}

.msg-row{display:flex;gap:8px;max-width:80%;}
.msg-row.mine{align-self:flex-end;flex-direction:row-reverse;}
.msg-row.other{align-self:flex-start;}
.msg-av-sm{
    width:28px;height:28px;border-radius:8px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:10px;font-weight:800;color:#fff;margin-top:3px;
}
.msg-body-col{display:flex;flex-direction:column;gap:2px;}
.msg-row.mine .msg-body-col{align-items:flex-end;}
.msg-bubble{
    padding:9px 13px;border-radius:16px;
    font-size:13.5px;line-height:1.55;word-break:break-word;white-space:pre-wrap;
}
.msg-row.mine  .msg-bubble{
    background:linear-gradient(135deg,var(--brand),var(--brand2));
    color:#fff;border-bottom-right-radius:4px;
    box-shadow:0 2px 8px rgba(124,58,237,.25);
}
.msg-row.other .msg-bubble{
    background:var(--surface);border:1px solid var(--border);
    color:var(--text);border-bottom-left-radius:4px;
    box-shadow:0 1px 4px rgba(0,0,0,.07);
}
.msg-time{font-size:10px;color:var(--muted);padding:0 4px;}
.msg-sys{
    align-self:center;max-width:90%;
    background:#f0fdf4;border:1px solid #bbf7d0;
    border-radius:var(--r-sm);padding:9px 13px;
    font-size:12px;color:#15803d;line-height:1.6;white-space:pre-wrap;
}
.date-sep{text-align:center;margin:8px 0 4px;}
.date-sep span{
    background:var(--surface2);color:var(--muted);
    font-size:10.5px;font-weight:600;padding:3px 14px;border-radius:20px;
    border:1px solid var(--border);
}

/* Input */
.chat-input-area{
    background:var(--surface);border-top:1px solid var(--border);
    padding:12px 16px;flex-shrink:0;
    box-shadow:0 -1px 4px rgba(0,0,0,.05);
}
.chat-input-row{
    display:flex;align-items:flex-end;gap:8px;
    background:var(--surface2);border:1.5px solid var(--border);
    border-radius:var(--r-sm);padding:9px 12px;
    transition:border-color .15s,box-shadow .15s;
}
.chat-input-row:focus-within{
    border-color:var(--brand);
    box-shadow:0 0 0 3px rgba(124,58,237,.1);
}
.chat-textarea{
    flex:1;background:none;border:none;outline:none;color:var(--text);
    font-size:13.5px;font-family:inherit;resize:none;
    min-height:22px;max-height:120px;line-height:1.5;
}
.chat-textarea::placeholder{color:var(--muted);}
.chat-send{
    width:36px;height:36px;border-radius:9px;flex-shrink:0;
    background:linear-gradient(135deg,var(--brand),var(--brand2));
    border:none;cursor:pointer;
    display:flex;align-items:center;justify-content:center;color:#fff;
    transition:opacity .14s,transform .1s;
    box-shadow:0 2px 8px rgba(124,58,237,.3);
}
.chat-send:hover{opacity:.88;}
.chat-send:active{transform:scale(.94);}
.chat-send:disabled{opacity:.4;cursor:not-allowed;}
.chat-hint{margin-top:6px;font-size:10px;color:var(--muted);text-align:center;}
.chat-hint kbd{
    background:var(--surface2);border:1px solid var(--border);
    border-radius:4px;font-size:9px;padding:1px 5px;color:var(--text2);font-family:inherit;
}

/* FAB mobile "Confier" */
.fab-confier{
    display:none;
    position:fixed;bottom:90px;right:16px;z-index:100;
    width:52px;height:52px;border-radius:50%;border:none;cursor:pointer;
    background:linear-gradient(135deg,var(--green),#10b981);color:#fff;
    font-size:22px;box-shadow:0 4px 16px rgba(5,150,105,.4);
    align-items:center;justify-content:center;
    transition:transform .18s;
}
.fab-confier:hover{transform:scale(1.08);}

/* ══ RIGHT PANEL ══ */
.right-panel{
    width:290px;flex-shrink:0;display:flex;flex-direction:column;
    background:var(--surface);border-left:1px solid var(--border);
    overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.35) rgba(0,0,0,.04);
    transition:transform .3s,width .3s;
}
.right-panel::-webkit-scrollbar{width:6px;}
.right-panel::-webkit-scrollbar-track{background:rgba(0,0,0,.03);border-radius:6px;}
.right-panel::-webkit-scrollbar-thumb{background:rgba(124,58,237,.35);border-radius:6px;}
.right-panel::-webkit-scrollbar-thumb:hover{background:rgba(124,58,237,.65);}

.rp-section{padding:16px;}
.rp-section+.rp-section{border-top:1px solid var(--border);}
.rp-title{
    font-size:10.5px;font-weight:800;text-transform:uppercase;
    letter-spacing:1px;color:var(--muted);margin-bottom:10px;
}

/* Info entreprise dans le panel */
.rp-company-card{
    display:flex;align-items:center;gap:10px;
    padding:10px;border-radius:var(--r-sm);
    background:var(--surface2);border:1px solid var(--border);
    margin-bottom:8px;
}
.rp-co-name{font-size:13px;font-weight:800;color:var(--text);}
.rp-co-phone{font-size:11px;color:var(--muted);margin-top:1px;}

/* Zones */
.zone-pill{
    display:flex;align-items:center;gap:8px;
    padding:9px 11px;border-radius:var(--r-xs);
    background:var(--surface2);border:1px solid var(--border);margin-bottom:6px;
    transition:border-color .14s;
}
.zone-pill:hover{border-color:rgba(124,58,237,.3);}
.zone-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.zone-pill-name{font-size:12px;font-weight:700;color:var(--text);flex:1;min-width:0;}
.zone-pill-price{font-size:12px;font-weight:900;color:var(--brand);white-space:nowrap;}
.zone-pill-time{font-size:10px;color:var(--muted);}

/* Bouton confier */
.btn-confier{
    width:100%;padding:12px;border-radius:var(--r-sm);border:none;cursor:pointer;
    background:linear-gradient(135deg,var(--green),#10b981);color:#fff;
    font-size:13px;font-weight:800;font-family:inherit;
    display:flex;align-items:center;justify-content:center;gap:7px;
    box-shadow:0 3px 12px rgba(5,150,105,.25);transition:all .18s;
}
.btn-confier:hover{transform:translateY(-1px);box-shadow:0 5px 18px rgba(5,150,105,.35);}
.btn-confier:disabled{opacity:.5;cursor:not-allowed;transform:none;}

/* ══ DRAWER MOBILE (right panel en slide-up) ══ */
.panel-overlay{
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200;
    opacity:0;transition:opacity .25s;
}
.panel-overlay.open{opacity:1;}
.panel-drawer{
    position:fixed;bottom:0;left:0;right:0;z-index:201;
    background:var(--surface);border-radius:20px 20px 0 0;
    max-height:85vh;display:flex;flex-direction:column;
    transform:translateY(100%);transition:transform .3s cubic-bezier(.32,.72,0,1);
    overflow:hidden;
}
.panel-drawer.open{transform:translateY(0);}
.drawer-handle{
    width:36px;height:4px;background:var(--border);border-radius:2px;
    margin:12px auto 0;flex-shrink:0;
}
.drawer-body{overflow-y:auto;padding:4px 0 24px;}

/* ══ ORDER MODAL ══ */
.order-modal-overlay{
    position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:3000;
    display:flex;align-items:center;justify-content:center;padding:16px;
    backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .22s;
}
.order-modal-overlay.open{opacity:1;pointer-events:all;}
.order-modal{
    background:var(--surface);border:1px solid var(--border);border-radius:20px;
    width:100%;max-width:540px;max-height:92vh;display:flex;flex-direction:column;
    box-shadow:0 24px 64px rgba(0,0,0,.2);
    transform:translateY(18px) scale(.98);transition:transform .22s;overflow:hidden;
}
.order-modal-overlay.open .order-modal{transform:translateY(0) scale(1);}
.om-header{
    padding:18px 20px 14px;border-bottom:1px solid var(--border);flex-shrink:0;
    display:flex;align-items:center;justify-content:space-between;
}
.om-title{font-size:15px;font-weight:900;color:var(--text);display:flex;align-items:center;gap:8px;}
.om-close{
    width:28px;height:28px;border-radius:8px;background:var(--surface2);
    border:1px solid var(--border);display:flex;align-items:center;justify-content:center;
    color:var(--text2);cursor:pointer;font-size:15px;transition:all .14s;
}
.om-close:hover{background:#fee2e2;border-color:#fca5a5;color:#dc2626;}
.om-body{overflow-y:auto;flex:1;padding:14px;scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.4) rgba(0,0,0,.04);}
.om-body::-webkit-scrollbar{width:6px;}
.om-body::-webkit-scrollbar-track{background:rgba(0,0,0,.03);border-radius:6px;}
.om-body::-webkit-scrollbar-thumb{background:rgba(124,58,237,.4);border-radius:6px;}
.om-body::-webkit-scrollbar-thumb:hover{background:rgba(124,58,237,.7);}

/* Cartes commandes */
.order-card{
    display:flex;align-items:flex-start;gap:11px;
    padding:12px;border-radius:var(--r-sm);border:2px solid var(--border);
    background:var(--surface2);cursor:pointer;transition:all .16s;margin-bottom:8px;
}
.order-card:hover{border-color:rgba(124,58,237,.35);background:#faf5ff;}
.order-card.selected{border-color:var(--brand);background:#faf5ff;box-shadow:0 0 0 3px rgba(124,58,237,.12);}
.order-card-check{
    width:19px;height:19px;border-radius:4px;border:2px solid var(--border);
    flex-shrink:0;margin-top:2px;
    display:flex;align-items:center;justify-content:center;transition:all .15s;
}
.order-card.selected .order-card-check{background:var(--brand);border-color:var(--brand);}
.order-card-check-ico{color:#fff;font-size:10px;display:none;}
.order-card.selected .order-card-check-ico{display:block;}

/* Thumbs produits */
.product-thumbs{display:flex;flex-shrink:0;}
.prod-thumb{
    width:44px;height:44px;border-radius:9px;overflow:hidden;flex-shrink:0;
    border:2px solid var(--surface);background:var(--surface2);
    display:flex;align-items:center;justify-content:center;font-size:17px;
}
.prod-thumb img{width:100%;height:100%;object-fit:cover;}
.prod-thumb+.prod-thumb{margin-left:-8px;}

.order-info{flex:1;min-width:0;}
.order-num{font-size:10.5px;font-weight:700;color:var(--brand);margin-bottom:2px;}
.order-client{font-size:13px;font-weight:800;color:var(--text);margin-bottom:1px;}
.order-client-phone{font-size:10.5px;color:var(--muted);margin-bottom:5px;}
.order-items-txt{font-size:11px;color:var(--text2);margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.order-total{font-size:13.5px;font-weight:900;color:var(--green);}

/* Footer modal */
.om-footer{padding:14px 16px 18px;border-top:1px solid var(--border);flex-shrink:0;}
.zone-select-wrap{margin-bottom:10px;}
.zone-select-lbl{font-size:10.5px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.4px;display:block;margin-bottom:5px;}
.zone-select{
    width:100%;padding:9px 12px;border-radius:var(--r-sm);
    border:1.5px solid var(--border);
    background:var(--surface2);color:var(--text);
    font-size:13px;font-family:inherit;outline:none;cursor:pointer;
    transition:border-color .15s;
}
.zone-select:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(124,58,237,.1);}
.zone-price-hint{
    display:none;padding:8px 12px;border-radius:var(--r-xs);
    background:#f0fdf4;border:1px solid #86efac;
    font-size:12px;font-weight:700;color:#15803d;margin-top:6px;
}
.btn-confirm-assign{
    width:100%;padding:13px;border-radius:var(--r-sm);border:none;cursor:pointer;
    background:linear-gradient(135deg,var(--brand),var(--brand2));color:#fff;
    font-size:13.5px;font-weight:800;font-family:inherit;
    display:flex;align-items:center;justify-content:center;gap:8px;
    box-shadow:0 3px 14px rgba(124,58,237,.3);transition:all .18s;
}
.btn-confirm-assign:hover{transform:translateY(-1px);box-shadow:0 5px 20px rgba(124,58,237,.4);}
.btn-confirm-assign:disabled{opacity:.4;cursor:not-allowed;transform:none;}
.no-orders-msg{text-align:center;padding:36px 20px;color:var(--muted);}
.no-orders-ico{font-size:34px;margin-bottom:8px;opacity:.45;}
.no-orders-txt{font-size:13px;}

/* ══ RESPONSIVE ══ */
@media(max-width:1023px){
    .right-panel{display:none;}
    .btn-panel-toggle{display:flex;}
    .panel-overlay,.panel-drawer{display:flex;flex-direction:column;}
    .fab-confier{display:flex;}
}
@media(max-width:600px){
    .chat-header{padding:0 12px;gap:9px;}
    .chat-msgs{padding:12px 10px;}
    .chat-input-area{padding:10px 12px;}
    .order-modal{border-radius:16px 16px 0 0;max-height:95vh;margin:0;}
    .order-modal-overlay{align-items:flex-end;padding:0;}
    .om-header{padding:14px 16px 11px;}
    .om-body{padding:10px;}
    .msg-row{max-width:90%;}
}
@media(max-width:380px){
    .chat-hd-sub{display:none;}
    .chat-online span:last-child{display:none;}
}
</style>
@endpush

@section('content')
@php
    $u    = auth()->user();
    $uPts = explode(' ', $u->name ?? 'M E');
    $uIni = strtoupper(substr($uPts[0],0,1)) . strtoupper(substr($uPts[1]??'',0,1));
    $cPts = explode(' ', $company->name ?? 'C O');
    $cIni = strtoupper(substr($cPts[0],0,1)) . strtoupper(substr($cPts[1]??'',0,1));
    $devise = $company->currency ?? 'GNF';
    $fmt = fn($n) => number_format($n??0, 0, ',', ' ') . ' ' . $devise;
@endphp

<div class="chat-page">
   

    {{-- ══ CHAT AREA ══ --}}
    <div class="chat-area">

        {{-- Header --}}
        <div class="chat-header">
            <a href="javascript:history.back()" class="chat-back" title="Retour">←</a>
            <div class="chat-av">
                @if($company->image)
                    <img src="{{ asset('storage/'.$company->image) }}" alt="{{ $company->name }}">
                @else
                    {{ $cIni }}
                @endif
            </div>
            <div style="min-width:0;">
                <div class="chat-hd-name">{{ $company->name }}</div>
                <div class="chat-hd-sub">Service de livraison</div>
            </div>
            <div class="chat-online">
                <span class="online-dot"></span> <span>En ligne</span>
            </div>
            {{-- Toggle panel sur tablette/mobile --}}
            <button class="btn-panel-toggle" onclick="openDrawer()" title="Infos & zones">
                ℹ️
            </button>
        </div>

        {{-- Messages --}}
        <div class="chat-msgs" id="bcMessages">
            @if($messages->isEmpty())
            <div class="msg-empty">
                <div class="msg-empty-ico">💬</div>
                <p class="msg-empty-txt">Démarrez la conversation avec <strong>{{ $company->name }}</strong>.<br>Posez vos questions sur les tarifs, zones et délais.</p>
            </div>
            @else
            @php $prevDate = null; @endphp
            @foreach($messages as $m)
            @php
                $isMine   = auth()->id() === $m->sender_id;
                $isSystem = in_array($m->sender_role ?? '', ['system','auto']);
                $msgDate  = $m->created_at->format('d/m/Y');
                $today    = now()->format('d/m/Y');
                $yesterday= now()->subDay()->format('d/m/Y');
                $dateLabel= $msgDate===$today?"Aujourd'hui":($msgDate===$yesterday?'Hier':$m->created_at->translatedFormat('d F Y'));
            @endphp
            @if($prevDate !== $msgDate)
            <div class="date-sep"><span>{{ $dateLabel }}</span></div>
            @php $prevDate = $msgDate; @endphp
            @endif
            @if($isSystem)
                <div class="msg-sys">{{ $m->message }}</div>
            @else
            <div class="msg-row {{ $isMine ? 'mine' : 'other' }}">
                <div class="msg-av-sm" style="background:{{ $isMine ? 'linear-gradient(135deg,#7c3aed,#5b21b6)' : 'linear-gradient(135deg,#059669,#10b981)' }}">
                    {{ $isMine ? $uIni : $cIni }}
                </div>
                <div class="msg-body-col">
                    <div class="msg-bubble">{{ $m->message }}</div>
                    <div class="msg-time">{{ $m->created_at->format('H:i') }}</div>
                </div>
            </div>
            @endif
            @endforeach
            @endif
        </div>

        {{-- Input --}}
        <div class="chat-input-area">
            <div class="chat-input-row">
                <textarea class="chat-textarea" id="bcInput" rows="1"
                    placeholder="Écrivez à {{ $company->name }}…">{{ $init ?? '' }}</textarea>
                <button class="chat-send" id="bcSend" title="Envoyer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
            <div class="chat-hint"><kbd>Entrée</kbd> envoyer &nbsp;·&nbsp; <kbd>Maj+Entrée</kbd> nouvelle ligne</div>
        </div>

    </div>{{-- /chat-area --}}

    {{-- ══ RIGHT PANEL (desktop) ══ --}}
    @include('company._chat_panel')

</div>{{-- /chat-page --}}

{{-- ══ DRAWER MOBILE ══ --}}
<div class="panel-overlay" id="panelOverlay" onclick="closeDrawer()"></div>
<div class="panel-drawer" id="panelDrawer">
    <div class="drawer-handle"></div>
    <div class="drawer-body">
        @include('company._chat_panel', ['inDrawer' => true])
    </div>
</div>

{{-- FAB mobile --}}
@if($shopId)
<button class="fab-confier" onclick="openOrderModal()" title="Confier une commande">📦</button>
@endif

{{-- ══ ORDER MODAL ══ --}}
<div class="order-modal-overlay" id="orderModal">
    <div class="order-modal">
        <div class="om-header">
            <div class="om-title">
                <span style="width:28px;height:28px;border-radius:8px;background:#d1fae5;border:1px solid #6ee7b7;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">📦</span>
                Confier une commande
            </div>
            <button class="om-close" onclick="closeOrderModal()">✕</button>
        </div>

        <div class="om-body">
            @if($pendingOrders->isEmpty())
            <div class="no-orders-msg">
                <div class="no-orders-ico">📭</div>
                <div class="no-orders-txt">Aucune commande en attente de livraison.</div>
            </div>
            @else
            <p style="font-size:11.5px;color:var(--muted);margin:0 0 10px;">
                Sélectionnez une ou plusieurs commandes à confier à <strong style="color:var(--brand);">{{ $company->name }}</strong>
            </p>
            <div style="display:flex;align-items:center;gap:8px;padding:6px 4px 10px;border-bottom:1px solid var(--border);margin-bottom:8px;">
                <input type="checkbox" id="omSelectAll" onchange="omToggleAll(this)"
                       style="width:16px;height:16px;accent-color:var(--brand);cursor:pointer;border-radius:4px;flex-shrink:0;">
                <label for="omSelectAll" style="font-size:11.5px;font-weight:700;color:var(--text2);cursor:pointer;user-select:none;">Tout sélectionner</label>
                <span id="omSelCount" style="margin-left:auto;font-size:11px;font-weight:700;color:var(--brand);"></span>
            </div>

            @foreach($pendingOrders as $order)
            @php
                $thumbs     = $order->items->take(3);
                $itemsNames = $order->items->pluck('product.name')->filter()->implode(', ');
            @endphp
            <div class="order-card" data-order-id="{{ $order->id }}" onclick="selectOrder(this, {{ $order->id }})">
                <div class="order-card-check">
                    <span class="order-card-check-ico">✓</span>
                </div>
                <div class="product-thumbs">
                    @foreach($thumbs as $item)
                    <div class="prod-thumb">
                        @if($item->product?->image)
                            <img src="{{ asset('storage/'.$item->product->image) }}" alt="">
                        @else
                            🛒
                        @endif
                    </div>
                    @endforeach
                    @if($order->items->count() > 3)
                    <div class="prod-thumb" style="background:var(--surface);font-size:9px;font-weight:800;color:var(--muted);">+{{ $order->items->count()-3 }}</div>
                    @endif
                </div>
                <div class="order-info">
                    <div class="order-num">#{{ str_pad($order->id,5,'0',STR_PAD_LEFT) }}</div>
                    <div class="order-client">{{ $order->client->name ?? 'Client inconnu' }}</div>
                    @if($order->client?->phone)
                    <div class="order-client-phone">📞 {{ $order->client->phone }}</div>
                    @endif

                     @if($order->client?->address)
                    <div class="address-client-address">📍 {{ $order->client->address }}</div>
                    @endif

                    @if($itemsNames)
                    <div class="order-items-txt">{{ Str::limit($itemsNames, 45) }}</div>
                    @endif
                    <div class="order-total">{{ $fmt($order->total) }}</div>
                </div>
            </div>
            @endforeach
            @endif
        </div>

        @if($pendingOrders->isNotEmpty())
        <div class="om-footer">
            @if($zones->isNotEmpty())
            <div class="zone-select-wrap">
                <label class="zone-select-lbl">📍 Zone de livraison</label>
                <input type="text" id="zoneModalSearch" placeholder="🔍 Rechercher une zone…" autocomplete="off"
                       oninput="filterZoneModal(this)"
                       style="width:100%;padding:7px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:12px;font-family:inherit;background:var(--surface2);color:var(--text);outline:none;margin-bottom:6px;box-sizing:border-box;transition:border-color .15s;"
                       onfocus="this.style.borderColor='var(--brand)'" onblur="this.style.borderColor='var(--border)'">
                <select class="zone-select" id="zoneSelectModal" onchange="onZoneChange(this)">
                    <option value="">— Choisir une zone (optionnel) —</option>
                    @foreach($zones as $zone)
                    <option value="{{ $zone->id }}"
                            data-price="{{ $zone->price }}"
                            data-minutes="{{ $zone->estimated_minutes }}">
                        {{ $zone->name }} — {{ number_format($zone->price,0,',',' ') }} {{ $devise }} · ~{{ $zone->estimated_minutes }} min
                    </option>
                    @endforeach
                </select>
                <div class="zone-price-hint" id="zonePriceHint">
                    💰 Frais : <span id="zonePriceDisplay"></span>
                </div>
            </div>
            @endif
            <button class="btn-confirm-assign" id="btnConfirmAssign" onclick="confirmAssign()" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                <span class="btn-label">Confier cette commande</span>
            </button>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
const DEVISE     = @json($devise);
const companyId = {{ $company->id }};
const authId    = {{ auth()->id() }};
const shopId    = @json((string)($shopId ?? ''));
const uIni      = @json($uIni);
const cIni      = @json($cIni);

let lastAt = null;
let _selectedOrderIds = new Set();

const box   = document.getElementById('bcMessages');
const input = document.getElementById('bcInput');
const send  = document.getElementById('bcSend');

@if($messages->count())
lastAt = @json($messages->last()->created_at->toDateTimeString());
@endif

function scrollBottom(){ box.scrollTop = box.scrollHeight + 9999; }

function renderMsg(m) {
    const mine   = m.sender_id ? (parseInt(m.sender_id) === authId) : (m.from_type === 'shop');
    const system = m.sender_role === 'system' || m.sender_role === 'auto';
    box.querySelector('.msg-empty')?.remove();

    if (system) {
        const d = document.createElement('div');
        d.className = 'msg-sys';
        d.textContent = m.body || m.message || '';
        box.appendChild(d);
        return;
    }

    const row = document.createElement('div');
    row.className = 'msg-row ' + (mine ? 'mine' : 'other');

    const av = document.createElement('div'); av.className = 'msg-av-sm';
    av.style.background = mine
        ? 'linear-gradient(135deg,#7c3aed,#5b21b6)'
        : 'linear-gradient(135deg,#059669,#10b981)';
    av.textContent = mine ? uIni : cIni;

    const col    = document.createElement('div'); col.className = 'msg-body-col';
    const bubble = document.createElement('div'); bubble.className = 'msg-bubble';
    bubble.textContent = m.body || m.message || '';
    const time   = document.createElement('div'); time.className = 'msg-time';
    time.textContent = new Date(m.created_at).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});

    col.appendChild(bubble); col.appendChild(time);
    row.appendChild(av); row.appendChild(col);
    box.appendChild(row);
}

async function poll() {
    if (!shopId) return;
    try {
        let url = `/company/${companyId}/chat/messages?shop_id=${encodeURIComponent(shopId)}`;
        if (lastAt) url += `&after=${encodeURIComponent(lastAt)}`;
        const res  = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) return;
        const data = await res.json();
        if (data.ok && data.messages?.length) {
            data.messages.forEach(m => { renderMsg(m); lastAt = m.created_at; });
            scrollBottom();
        }
    } catch(e) {}
}

async function sendMsg() {
    const txt = input.value.trim();
    if (!txt) return;
    send.disabled = true;
    try {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const res   = await fetch(`/company/${companyId}/chat/send`, {
            method:'POST', credentials:'same-origin',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
            body: JSON.stringify({ message: txt, shop_id: shopId })
        });
        const data = await res.json();
        if (data.ok) {
            renderMsg({ from_type:'shop', sender_id:authId, body:txt, created_at:data.last||new Date().toISOString() });
            input.value = ''; input.style.height = '';
            scrollBottom(); lastAt = data.last;
        } else { alert(data.error || 'Erreur lors de l\'envoi.'); }
    } catch(e) { alert('Erreur réseau.'); }
    finally { send.disabled = false; input.focus(); }
}

send.addEventListener('click', sendMsg);
input.addEventListener('keydown', e => { if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMsg();} });
input.addEventListener('input', () => {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 120) + 'px';
});

scrollBottom();
if (shopId) setInterval(poll, 3000);
if (input.value.trim()) { input.focus(); input.setSelectionRange(input.value.length, input.value.length); }

/* ── DRAWER mobile ── */
window.openDrawer = function() {
    document.getElementById('panelOverlay').style.display = 'block';
    document.getElementById('panelDrawer').style.display  = 'flex';
    requestAnimationFrame(() => {
        document.getElementById('panelOverlay').classList.add('open');
        document.getElementById('panelDrawer').classList.add('open');
    });
    document.body.style.overflow = 'hidden';
};
window.closeDrawer = function() {
    document.getElementById('panelOverlay').classList.remove('open');
    document.getElementById('panelDrawer').classList.remove('open');
    setTimeout(() => {
        document.getElementById('panelOverlay').style.display = 'none';
        document.getElementById('panelDrawer').style.display  = 'none';
    }, 300);
    document.body.style.overflow = '';
};

/* ── ORDER MODAL ── */
window.openOrderModal = function() {
    closeDrawer();
    document.getElementById('orderModal').classList.add('open');
    document.body.style.overflow = 'hidden';
};
window.closeOrderModal = function() {
    document.getElementById('orderModal').classList.remove('open');
    document.body.style.overflow = '';
    _selectedOrderIds.clear();
    const btn = document.getElementById('btnConfirmAssign');
    if (btn) { btn.disabled = true; btn.querySelector('.btn-label').textContent = 'Confier cette commande'; }
    const chk = document.getElementById('omSelectAll');
    if (chk) { chk.checked = false; chk.indeterminate = false; }
    const cnt = document.getElementById('omSelCount');
    if (cnt) cnt.textContent = '';
};
document.getElementById('orderModal')?.addEventListener('click', e => {
    if (e.target === document.getElementById('orderModal')) closeOrderModal();
});

function omUpdateBtn() {
    const n   = _selectedOrderIds.size;
    const btn = document.getElementById('btnConfirmAssign');
    if (!btn) return;
    btn.disabled = n === 0;
    btn.querySelector('.btn-label').textContent = n > 1 ? `Confier ${n} commandes` : 'Confier cette commande';
    const cnt = document.getElementById('omSelCount');
    if (cnt) cnt.textContent = n > 0 ? `${n} sélectionnée${n>1?'s':''}` : '';
    const allCards = document.querySelectorAll('.order-card');
    const chk = document.getElementById('omSelectAll');
    if (chk) {
        chk.checked       = allCards.length > 0 && n === allCards.length;
        chk.indeterminate = n > 0 && n < allCards.length;
    }
}

window.omToggleAll = function(chk) {
    document.querySelectorAll('.order-card').forEach(card => {
        const id = parseInt(card.dataset.orderId);
        if (chk.checked) { _selectedOrderIds.add(id); card.classList.add('selected'); }
        else             { _selectedOrderIds.delete(id); card.classList.remove('selected'); }
    });
    omUpdateBtn();
};

window.selectOrder = function(el, orderId) {
    if (_selectedOrderIds.has(orderId)) {
        _selectedOrderIds.delete(orderId);
        el.classList.remove('selected');
    } else {
        _selectedOrderIds.add(orderId);
        el.classList.add('selected');
    }
    omUpdateBtn();
};

window.filterZoneModal = function(input) {
    const q   = input.value.toLowerCase().trim();
    const sel = document.getElementById('zoneSelectModal');
    if (!sel) return;
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.hidden = q.length > 0 && !opt.text.toLowerCase().includes(q);
    });
    const cur = sel.options[sel.selectedIndex];
    if (cur && cur.value && cur.hidden) {
        sel.value = '';
        sel.dispatchEvent(new Event('change'));
    }
};

window.onZoneChange = function(sel) {
    const hint    = document.getElementById('zonePriceHint');
    const display = document.getElementById('zonePriceDisplay');
    const opt     = sel.options[sel.selectedIndex];
    if (!sel.value) { hint.style.display = 'none'; return; }
    display.textContent = new Intl.NumberFormat('fr-FR').format(opt.dataset.price) + ' ' + DEVISE + ' · ~' + opt.dataset.minutes + ' min';
    hint.style.display = 'block';
};

window.confirmAssign = async function() {
    if (_selectedOrderIds.size === 0) return;
    const btn     = document.getElementById('btnConfirmAssign');
    const zoneSel = document.getElementById('zoneSelectModal');
    const zoneId  = zoneSel?.value || null;
    const zoneOpt = zoneId ? zoneSel.options[zoneSel.selectedIndex] : null;
    const token   = document.querySelector('meta[name="csrf-token"]').content;

    btn.disabled = true;
    btn.querySelector('.btn-label').textContent = '⏳ Assignation…';

    const ids = Array.from(_selectedOrderIds);
    let successCount = 0;
    const successNums = [];

    for (const orderId of ids) {
        const fd = new FormData();
        fd.append('_method', 'PUT');
        fd.append('delivery_company_id', companyId);
        if (zoneId)                  fd.append('delivery_zone_id', zoneId);
        if (zoneOpt?.dataset?.price) fd.append('delivery_fee', zoneOpt.dataset.price);
        try {
            const res  = await fetch(`/employe/orders/${orderId}/send-to-company`, {
                method:'POST',
                headers:{'X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                body: fd
            });
            const data = await res.json();
            if (data.success) { successCount++; successNums.push('#' + String(orderId).padStart(5,'0')); }
        } catch(e) {}
    }

    if (successCount > 0) {
        closeOrderModal();
        const zoneSuffix = zoneOpt ? '\nZone : ' + zoneOpt.textContent.split('—')[0].trim() : '';
        renderMsg({
            sender_role:'system',
            body: successCount === 1
                ? '✅ Commande ' + successNums[0] + ' confiée à {{ $company->name }}.' + zoneSuffix
                : `✅ ${successCount} commandes confiées à {{ $company->name }}.` + zoneSuffix,
            created_at: new Date().toISOString()
        });
        scrollBottom();
    } else {
        btn.disabled = false;
        btn.querySelector('.btn-label').textContent = 'Confier cette commande';
        alert('Erreur lors de l\'assignation.');
    }
};

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeOrderModal(); closeDrawer(); }
});

})();
</script>
@endpush
