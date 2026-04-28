@extends('layouts.app')
@section('title', 'Messages · '.$company->name)
@php $bodyClass = 'cx-dashboard'; @endphp

@push('styles')
{{-- Anti-flash : applique le fond avant le premier paint --}}
<script>
(function(){
    if(localStorage.getItem('cx-theme')==='light')
        document.documentElement.classList.add('cx-prelight');
})();
</script>
<style>
html.cx-prelight body{background:#eef1f7!important}
</style>
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --cx-bg:#07091a; --cx-surface:#0d1226; --cx-surface2:#111930;
    --cx-border:rgba(255,255,255,.07); --cx-brand:#7c3aed;
    --cx-brand-lt:#a78bfa; --cx-brand-mlt:rgba(124,58,237,.12);
    --cx-text:#e2e8f0; --cx-text2:#94a3b8; --cx-muted:#475569;
    --cx-green:#10b981; --r:12px; --r-sm:8px;
}
html,body{margin:0;font-family:'Segoe UI',sans-serif;background:var(--cx-bg)!important;color:var(--cx-text)}
a{text-decoration:none;color:inherit}
body.cx-dashboard>nav,body.cx-dashboard>header,body.cx-dashboard .navbar{display:none!important}

/* ══ MODE CLAIR — inbox ══ */
body.cx-light{
    --cx-bg:#eef1f7; --cx-surface:#ffffff; --cx-surface2:#f4f6fb;
    --cx-border:rgba(0,0,0,.09); --cx-text:#111827;
    --cx-text2:#4b5563; --cx-muted:#9ca3af;
    --cx-brand-mlt:rgba(124,58,237,.07);
    background:var(--cx-bg)!important;
}

/* Sidebar : reste sombre dans les deux modes */

/* Zone principale */
body.cx-light .ic-main{background:var(--cx-bg)}

/* Header conversation */
body.cx-light .ic-main-hd{background:var(--cx-surface);border-bottom-color:rgba(0,0,0,.08)}
body.cx-light .ic-main-name{color:#111827}
body.cx-light .ic-hd-btn{
    background:rgba(0,0,0,.05);border-color:rgba(0,0,0,.08);
}
body.cx-light .ic-hd-btn:hover{background:rgba(0,0,0,.1)}
body.cx-light .ic-mob-btn{background:rgba(0,0,0,.05);border-color:rgba(0,0,0,.08);color:#374151}

/* Écran d'accueil */
body.cx-light .ic-welcome h3{color:#111827}
body.cx-light .ic-welcome p{color:#4b5563}

/* Bulles de messages */
body.cx-light .msg-row.other .msg-bubble{
    background:#ffffff;border-color:rgba(0,0,0,.1);color:#111827;
    box-shadow:0 1px 3px rgba(0,0,0,.07);
}
body.cx-light .msg-time{color:#9ca3af}

/* Séparateur de date */
body.cx-light .msg-date-sep span{
    background:rgba(0,0,0,.07);color:#6b7280;
}

/* Zone de saisie */
body.cx-light .ic-input-area{
    background:var(--cx-surface);border-top-color:rgba(0,0,0,.08);
}
body.cx-light .ic-input-row{
    background:rgba(0,0,0,.04);border-color:rgba(0,0,0,.1);
}
body.cx-light .ic-input-row:focus-within{border-color:rgba(124,58,237,.4);background:#fff}
body.cx-light .ic-textarea{color:#111827}
body.cx-light .ic-textarea::placeholder{color:#9ca3af}
body.cx-light .ic-input-hint{color:#9ca3af}

/* ── Layout ── */
.ic-wrap{display:flex;height:100vh;overflow:hidden}

/* ── Sidebar conversations ── */
.ic-sidebar{
    width:310px;flex-shrink:0;background:#06070f;
    border-right:1px solid var(--cx-border);
    display:flex;flex-direction:column;
}
.ic-sb-hd{
    padding:14px 16px;border-bottom:1px solid var(--cx-border);
    display:flex;align-items:center;gap:10px;flex-shrink:0;
}
.ic-back{
    width:34px;height:34px;border-radius:8px;flex-shrink:0;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);
    display:flex;align-items:center;justify-content:center;font-size:15px;
    cursor:pointer;transition:background .14s;color:var(--cx-text2);
}
.ic-back:hover{background:rgba(255,255,255,.1);color:#fff}
.ic-sb-title{font-size:14.5px;font-weight:800;color:#fff}
.ic-sb-sub{font-size:10.5px;color:var(--cx-text2);margin-top:1px}
.ic-search-wrap{padding:10px 12px;border-bottom:1px solid var(--cx-border);flex-shrink:0}
.ic-search-wrap input{
    width:100%;background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.08);border-radius:8px;
    padding:8px 12px;color:var(--cx-text);font-size:13px;font-family:inherit;outline:none;
}
.ic-search-wrap input:focus{border-color:rgba(139,92,246,.4)}
.ic-search-wrap input::placeholder{color:var(--cx-muted)}

.ic-conv-list{flex:1;overflow-y:auto;scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.25) transparent}
.ic-conv-list::-webkit-scrollbar{width:3px}
.ic-conv-list::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:3px}

.ic-conv{
    display:flex;align-items:center;gap:11px;
    padding:12px 14px;cursor:pointer;
    border-bottom:1px solid rgba(255,255,255,.04);
    transition:background .14s;
}
.ic-conv:hover{background:rgba(255,255,255,.04)}
.ic-conv.active{background:rgba(124,58,237,.12)}
.ic-conv-av{
    width:44px;height:44px;border-radius:12px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:14px;font-weight:800;color:#fff;
}
.ic-conv-body{flex:1;min-width:0}
.ic-conv-name{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ic-conv-preview{font-size:11.5px;color:var(--cx-text2);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ic-conv-meta{display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0}
.ic-conv-time{font-size:10px;color:var(--cx-muted)}
.ic-conv-badge{
    background:var(--cx-brand);color:#fff;
    font-size:9.5px;font-weight:800;padding:1px 6px;border-radius:10px;min-width:18px;text-align:center;
}
.ic-empty-conv{
    flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:30px;text-align:center;color:var(--cx-text2);
}
.ic-empty-conv .eico{font-size:38px;margin-bottom:12px}
.ic-empty-conv p{font-size:12px;margin:0;line-height:1.6}

/* ── Main area ── */
.ic-main{flex:1;display:flex;flex-direction:column;min-width:0;background:var(--cx-bg)}

.ic-main-hd{
    height:60px;background:var(--cx-surface);border-bottom:1px solid var(--cx-border);
    display:flex;align-items:center;gap:12px;padding:0 20px;flex-shrink:0;
}
.ic-main-av{
    width:38px;height:38px;border-radius:10px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:13px;font-weight:800;color:#fff;
}
.ic-main-name{font-size:14px;font-weight:800;color:#fff}
.ic-main-status{font-size:11px;color:var(--cx-green);font-weight:600}
.ic-main-hd-right{margin-left:auto;display:flex;align-items:center;gap:8px}
.ic-hd-btn{
    width:34px;height:34px;border-radius:8px;
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.07);
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;font-size:15px;transition:background .14s;
}
.ic-hd-btn:hover{background:rgba(255,255,255,.1)}

/* Welcome / no selection */
.ic-welcome{
    flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
    color:var(--cx-text2);text-align:center;gap:14px;
}
.ic-welcome .wico{font-size:54px}
.ic-welcome h3{font-size:18px;font-weight:800;color:#fff;margin:0}
.ic-welcome p{font-size:12.5px;margin:0;max-width:270px;line-height:1.65}

/* Messages */
.ic-messages{
    flex:1;overflow-y:auto;padding:20px 24px;
    display:flex;flex-direction:column;gap:4px;
    scrollbar-width:thin;scrollbar-color:rgba(124,58,237,.25) transparent;
}
.ic-messages::-webkit-scrollbar{width:4px}
.ic-messages::-webkit-scrollbar-thumb{background:rgba(124,58,237,.3);border-radius:4px}

.msg-row{display:flex;gap:8px;max-width:74%}
.msg-row.mine{align-self:flex-end;flex-direction:row-reverse}
.msg-row.other{align-self:flex-start}
.msg-av{
    width:30px;height:30px;border-radius:8px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:10px;font-weight:800;color:#fff;margin-top:2px;
}
.msg-body{display:flex;flex-direction:column;gap:3px}
.msg-row.mine .msg-body{align-items:flex-end}
.msg-bubble{
    padding:10px 14px;border-radius:16px;
    font-size:13.5px;line-height:1.55;word-break:break-word;
}
.msg-row.mine .msg-bubble{
    background:linear-gradient(135deg,#7c3aed,#5b21b6);
    color:#fff;border-bottom-right-radius:4px;
}
.msg-row.other .msg-bubble{
    background:var(--cx-surface2);border:1px solid var(--cx-border);
    color:var(--cx-text);border-bottom-left-radius:4px;
}
.msg-time{font-size:10px;color:var(--cx-muted);padding:0 4px}
.msg-date-sep{text-align:center;margin:14px 0 8px}
.msg-date-sep span{
    background:rgba(255,255,255,.06);color:var(--cx-text2);
    font-size:10.5px;font-weight:600;padding:3px 14px;border-radius:20px;
}

/* Input */
.ic-input-area{
    padding:14px 18px;border-top:1px solid var(--cx-border);
    background:var(--cx-surface);flex-shrink:0;
}
.ic-input-row{
    display:flex;align-items:flex-end;gap:10px;
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
    border-radius:16px;padding:10px 14px;transition:border-color .15s;
}
.ic-input-row:focus-within{border-color:rgba(139,92,246,.4)}
.ic-textarea{
    flex:1;background:none;border:none;outline:none;
    color:var(--cx-text);font-size:13.5px;font-family:inherit;
    resize:none;min-height:22px;max-height:130px;line-height:1.5;overflow-y:auto;
}
.ic-textarea::placeholder{color:var(--cx-muted)}
.ic-send{
    width:38px;height:38px;border-radius:10px;flex-shrink:0;
    background:var(--cx-brand);border:none;cursor:pointer;
    display:flex;align-items:center;justify-content:center;
    font-size:16px;color:#fff;transition:background .14s;
}
.ic-send:hover{background:#6d28d9}
.ic-send:disabled{opacity:.4;cursor:not-allowed}
.ic-input-hint{margin-top:6px;text-align:center;font-size:10.5px;color:var(--cx-muted)}

/* Responsive */
@media(max-width:768px){
    .ic-sidebar{
        width:100%;position:fixed;top:0;left:0;bottom:0;z-index:40;
        transform:translateX(-100%);transition:transform .25s cubic-bezier(.23,1,.32,1);
    }
    .ic-sidebar.open{transform:translateX(0)}
    .ic-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:39}
    .ic-overlay.open{display:block}
    .ic-mob-btn{
        display:flex;align-items:center;justify-content:center;
        width:34px;height:34px;border-radius:8px;
        background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);
        font-size:18px;cursor:pointer;flex-shrink:0;
    }
}
@media(min-width:769px){
    .ic-mob-btn,.ic-overlay{display:none!important}
}
</style>
@endpush

@section('content')
@php
    $u    = auth()->user();
    $uPts = explode(' ', $u->name ?? 'S X');
    $uIni = strtoupper(substr($uPts[0],0,1)) . strtoupper(substr($uPts[1]??'',0,1));

    $palette = [
        'linear-gradient(135deg,#10b981,#059669)',
        'linear-gradient(135deg,#3b82f6,#1d4ed8)',
        'linear-gradient(135deg,#f59e0b,#d97706)',
        'linear-gradient(135deg,#ec4899,#be185d)',
        'linear-gradient(135deg,#06b6d4,#0891b2)',
        'linear-gradient(135deg,#84cc16,#4d7c0f)',
        'linear-gradient(135deg,#8b5cf6,#6d28d9)',
    ];
    $activeConv      = $conversations->firstWhere('shop_id', (int)$activeShopId);
    $activeConvIndex = $conversations->search(fn($c) => (string)$c['shop_id'] === (string)$activeShopId);
    $activeConvBg    = $activeConvIndex !== false ? $palette[$activeConvIndex % count($palette)] : $palette[0];
    $activeConvIni   = '';
    if ($activeConv) {
        $p = explode(' ', $activeConv['shop_name']);
        $activeConvIni = strtoupper(substr($p[0],0,1)) . strtoupper(substr($p[1]??'',0,1));
    }
@endphp

<div class="ic-overlay" id="icOverlay"></div>
<div class="ic-wrap">

{{-- ── SIDEBAR ── --}}
<div class="ic-sidebar" id="icSidebar">
    <div class="ic-sb-hd">
        <a href="{{ route('company.dashboard') }}" class="ic-back" title="Tableau de bord">←</a>
        <div>
            <div class="ic-sb-title">Conversations</div>
            <div class="ic-sb-sub">{{ $company->name }}</div>
        </div>
    </div>

    <div class="ic-search-wrap">
        <input type="text" id="icSearch" placeholder="🔍  Rechercher une boutique...">
    </div>

    <div class="ic-conv-list" id="icConvList">
        @forelse($conversations as $i => $conv)
        @php
            $bg      = $palette[$i % count($palette)];
            $cPts    = explode(' ', $conv['shop_name']);
            $cIni    = strtoupper(substr($cPts[0],0,1)) . strtoupper(substr($cPts[1]??'',0,1));
            $isAct   = (string)$activeShopId === (string)$conv['shop_id'];
            $timeStr = isset($conv['last_at']) ? \Carbon\Carbon::parse($conv['last_at'])->format('H:i') : '';
        @endphp
        <div class="ic-conv {{ $isAct ? 'active' : '' }}"
             data-shop="{{ $conv['shop_id'] }}"
             data-name="{{ e($conv['shop_name']) }}"
             data-bg="{{ $bg }}"
             data-ini="{{ $cIni }}"
             onclick="selectConv(this)">
            <div class="ic-conv-av" style="background:{{ $bg }}">{{ $cIni }}</div>
            <div class="ic-conv-body">
                <div class="ic-conv-name">{{ $conv['shop_name'] }}</div>
                <div class="ic-conv-preview">{{ Str::limit($conv['last_message'], 50) }}</div>
            </div>
            <div class="ic-conv-meta">
                <span class="ic-conv-time">{{ $timeStr }}</span>
                @if($conv['unread'] > 0)
                <span class="ic-conv-badge" id="badge-{{ $conv['shop_id'] }}">{{ $conv['unread'] }}</span>
                @endif
            </div>
        </div>
        @empty
        <div class="ic-empty-conv">
            <div class="eico">💬</div>
            <p>Aucune conversation.<br>Les boutiques vous contacteront depuis leur espace.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- ── MAIN ── --}}
<div class="ic-main">

    {{-- Header conversation --}}
    <div class="ic-main-hd" id="icMainHd" style="{{ $activeShopId ? '' : 'display:none' }}">
        <div class="ic-mob-btn" onclick="document.getElementById('icSidebar').classList.add('open');document.getElementById('icOverlay').classList.add('open')">☰</div>
        <div class="ic-main-av" id="icMainAv"
             style="background:{{ $activeConv ? $activeConvBg : '#333' }}">
            {{ $activeConvIni }}
        </div>
        <div>
            <div class="ic-main-name" id="icMainName">{{ $activeConv['shop_name'] ?? '' }}</div>
            <div class="ic-main-status">● Conversation active</div>
        </div>
        <div class="ic-main-hd-right">
            <div class="ic-hd-btn" title="Informations">ℹ️</div>
        </div>
    </div>

    {{-- Welcome --}}
    <div class="ic-welcome" id="icWelcome" style="{{ $activeShopId ? 'display:none' : '' }}">
        <div class="wico">💬</div>
        <h3>Bienvenue dans votre messagerie</h3>
        <p>Sélectionnez une boutique dans la liste pour voir et répondre à ses messages.</p>
    </div>

    {{-- Messages --}}
    <div class="ic-messages" id="icMessages" style="{{ $activeShopId ? '' : 'display:none' }}">
        @foreach($messages as $m)
        @php $isMine = $m->sender_role === 'company'; @endphp
        <div class="msg-row {{ $isMine ? 'mine' : 'other' }}">
            <div class="msg-av" style="background:{{ $isMine
                ? 'linear-gradient(135deg,#7c3aed,#5b21b6)'
                : $activeConvBg }}">
                {{ $isMine ? $uIni : $activeConvIni }}
            </div>
            <div class="msg-body">
                <div class="msg-bubble">{{ $m->message }}</div>
                <div class="msg-time">{{ $m->created_at->format('H:i') }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Input --}}
    <div class="ic-input-area" id="icInputArea" style="{{ $activeShopId ? '' : 'display:none' }}">
        <div class="ic-input-row">
            <textarea class="ic-textarea" id="icInput" rows="1" placeholder="Tapez votre réponse..."></textarea>
            <button class="ic-send" id="icSend" title="Envoyer (Entrée)">➤</button>
        </div>
        <div class="ic-input-hint">Entrée pour envoyer · Maj+Entrée pour aller à la ligne</div>
    </div>

</div>{{-- /ic-main --}}
</div>{{-- /ic-wrap --}}
@endsection

@push('scripts')
<script>
(function(){
const companyId = {{ $company->id }};
const authId    = {{ auth()->id() }};
const uIni      = @json($uIni);

let activeShopId = {{ $activeShopId ? $activeShopId : 'null' }};
let activeBg     = '';
let activeIni    = '';
let lastAt       = null;
let pollTimer    = null;
let convPollTimer= null;

const AV_COLORS = [
    'linear-gradient(135deg,#10b981,#059669)',
    'linear-gradient(135deg,#3b82f6,#1d4ed8)',
    'linear-gradient(135deg,#f59e0b,#d97706)',
    'linear-gradient(135deg,#ec4899,#be185d)',
    'linear-gradient(135deg,#06b6d4,#0891b2)',
    'linear-gradient(135deg,#84cc16,#4d7c0f)',
    'linear-gradient(135deg,#8b5cf6,#6d28d9)',
    'linear-gradient(135deg,#f97316,#c2410c)',
];
function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, c =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
    );
}
function getIni(name) {
    const n = (name || '').trim();
    if (!n) return '?';
    const p = n.split(' ');
    const a = p[0]?.[0] || '';
    const b = p[1]?.[0] || '';
    return (a + b).toUpperCase() || n[0].toUpperCase();
}
function fmtTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr), now = new Date(), diff = now - d;
    if (diff < 60000) return 'À l\'instant';
    if (diff < 3600000) return Math.floor(diff / 60000) + ' min';
    if (d.toDateString() === now.toDateString())
        return d.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
    return d.toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit' });
}

// DOM
const sidebar   = document.getElementById('icSidebar');
const overlay   = document.getElementById('icOverlay');
const mainHd    = document.getElementById('icMainHd');
const mainAv    = document.getElementById('icMainAv');
const mainName  = document.getElementById('icMainName');
const welcome   = document.getElementById('icWelcome');
const messages  = document.getElementById('icMessages');
const inputArea = document.getElementById('icInputArea');
const icInput   = document.getElementById('icInput');
const icSend    = document.getElementById('icSend');

// Overlay close
overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
});

// Select conversation
window.selectConv = function(el) {
    const shopId = el.dataset.shop;
    const name   = el.dataset.name;
    const bg     = el.dataset.bg;
    const ini    = el.dataset.ini;

    document.querySelectorAll('.ic-conv').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    el.querySelector('.ic-conv-badge')?.remove();

    mainAv.style.background = bg;
    mainAv.textContent      = ini;
    mainName.textContent    = name;
    mainHd.style.display    = '';
    welcome.style.display   = 'none';
    messages.style.display  = '';
    inputArea.style.display = '';

    messages.innerHTML = '';
    lastAt       = null;
    activeShopId = shopId;
    activeBg     = bg;
    activeIni    = ini;

    // Close sidebar on mobile
    sidebar.classList.remove('open');
    overlay.classList.remove('open');

    // Marquer les messages comme lus (action explicite de l'utilisateur)
    markRead(shopId);

    if (pollTimer) clearInterval(pollTimer);
    loadMessages(true);
    pollTimer = setInterval(() => loadMessages(false), 3000);

    icInput.focus();
};

// Appel serveur : marquer messages lus — uniquement quand l'utilisateur ouvre la conv
function markRead(shopId) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!token || !shopId) return;
    fetch('{{ route('company.chat.markRead') }}', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ shop_id: shopId })
    }).catch(() => {});
}

// Render one message
function renderMsg(m) {
    const mine = m.from_type === 'company';
    const row  = document.createElement('div');
    row.className = 'msg-row ' + (mine ? 'mine' : 'other');

    const av = document.createElement('div');
    av.className = 'msg-av';
    av.style.background = mine ? 'linear-gradient(135deg,#7c3aed,#5b21b6)' : activeBg;
    av.textContent = mine ? uIni : (activeIni || '?');

    const body   = document.createElement('div'); body.className = 'msg-body';
    const bubble = document.createElement('div'); bubble.className = 'msg-bubble';
    bubble.textContent = m.body || '';
    const time   = document.createElement('div'); time.className = 'msg-time';
    time.textContent = new Date(m.created_at).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});

    body.appendChild(bubble); body.appendChild(time);
    row.appendChild(av); row.appendChild(body);
    messages.appendChild(row);
}

// Load messages
async function loadMessages(initial) {
    if (!activeShopId) return;
    try {
        let url = `/company/${companyId}/chat/messages?shop_id=${encodeURIComponent(activeShopId)}`;
        if (!initial && lastAt) url += `&after=${encodeURIComponent(lastAt)}`;
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.ok || !data.messages.length) return;
        data.messages.forEach(m => { renderMsg(m); lastAt = m.created_at; });
        messages.scrollTop = messages.scrollHeight;
    } catch(e) { console.error(e); }
}

// Send message
async function sendMsg() {
    const txt = icInput.value.trim();
    if (!txt || !activeShopId) return;
    icSend.disabled = true;
    try {
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const res = await fetch(`/company/${companyId}/chat/send`, {
            method: 'POST', credentials: 'same-origin',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
            body: JSON.stringify({ message: txt, shop_id: activeShopId })
        });
        const data = await res.json();
        if (data.ok) {
            renderMsg({ from_type:'company', body:txt, created_at: data.last || new Date().toISOString() });
            icInput.value = ''; icInput.style.height = '';
            messages.scrollTop = messages.scrollHeight;
            lastAt = data.last;
            // Update sidebar preview
            const prev = document.querySelector(`.ic-conv[data-shop="${activeShopId}"] .ic-conv-preview`);
            if (prev) prev.textContent = txt.length > 50 ? txt.slice(0,47)+'…' : txt;
        } else {
            alert(data.error || 'Erreur lors de l\'envoi.');
        }
    } catch(e) { console.error(e); alert('Erreur réseau.'); }
    finally { icSend.disabled = false; icInput.focus(); }
}

icSend.addEventListener('click', sendMsg);
icInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
});
icInput.addEventListener('input', () => {
    icInput.style.height = 'auto';
    icInput.style.height = Math.min(icInput.scrollHeight, 130) + 'px';
});

// Search conversations
document.getElementById('icSearch')?.addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('.ic-conv').forEach(el => {
        el.style.display = (el.dataset.name||'').toLowerCase().includes(q) ? '' : 'none';
    });
});

// Poll conversation list — met à jour badges, previews, et ajoute les nouvelles convs
async function pollConversations() {
    try {
        const res = await fetch('/company/chat/conversations', { credentials: 'same-origin' });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.ok || !Array.isArray(data.conversations)) return;

        const convList = document.getElementById('icConvList');
        if (!convList) return;

        data.conversations.forEach((conv, i) => {
            const shopId   = String(conv.shop_id);
            const isActive = shopId === String(activeShopId);
            let convEl = convList.querySelector(`.ic-conv[data-shop="${shopId}"]`);

            if (!convEl) {
                // Nouvelle conversation — l'ajouter en haut de la liste
                const emptyEl = convList.querySelector('.ic-empty-conv');
                if (emptyEl) emptyEl.remove();

                const bg  = AV_COLORS[i % AV_COLORS.length];
                const ini = getIni(conv.shop_name);
                convEl = document.createElement('div');
                convEl.className    = 'ic-conv';
                convEl.dataset.shop = shopId;
                convEl.dataset.name = conv.shop_name || '';
                convEl.dataset.bg   = bg;
                convEl.dataset.ini  = ini;
                convEl.setAttribute('onclick', 'selectConv(this)');
                convEl.innerHTML = `
                    <div class="ic-conv-av" style="background:${bg}">${esc(ini)}</div>
                    <div class="ic-conv-body">
                        <div class="ic-conv-name">${esc(conv.shop_name || '')}</div>
                        <div class="ic-conv-preview">${esc((conv.last_message || '').slice(0, 50))}</div>
                    </div>
                    <div class="ic-conv-meta">
                        <span class="ic-conv-time">${fmtTime(conv.last_at)}</span>
                        ${(conv.unread || 0) > 0 ? `<span class="ic-conv-badge" id="badge-${shopId}">${conv.unread}</span>` : ''}
                    </div>
                `;
                convList.insertBefore(convEl, convList.firstChild);
            } else {
                // Mettre à jour preview et temps
                const preview = convEl.querySelector('.ic-conv-preview');
                if (preview && conv.last_message) preview.textContent = conv.last_message.slice(0, 50);
                const timeEl = convEl.querySelector('.ic-conv-time');
                if (timeEl && conv.last_at) timeEl.textContent = fmtTime(conv.last_at);

                // Badge — seulement si conv non active
                if (!isActive) {
                    let badge = document.getElementById('badge-' + shopId);
                    if ((conv.unread || 0) > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'ic-conv-badge';
                            badge.id = 'badge-' + shopId;
                            convEl.querySelector('.ic-conv-meta')?.appendChild(badge);
                        }
                        badge.textContent = conv.unread > 99 ? '99+' : conv.unread;
                    } else if (badge) {
                        badge.remove();
                    }
                }
            }
        });
    } catch(e) {}
}
// Lancer immédiatement puis toutes les 5 secondes
pollConversations();
convPollTimer = setInterval(pollConversations, 5000);

// Init: if preloaded active shop (URL contains ?shop_id=X)
if (activeShopId) {
    @if($messages->count())
    lastAt = '{{ $messages->last()->created_at->toDateTimeString() }}';
    @endif
    messages.scrollTop = messages.scrollHeight;
    // L'utilisateur a navigué vers cette conversation : marquer comme lus
    markRead(activeShopId);
    pollTimer = setInterval(() => loadMessages(false), 3000);
}

})();

/* ── Synchroniser le thème avec le dashboard ── */
(function(){
    const saved = localStorage.getItem('cx-theme') || 'dark';
    if (saved === 'light') {
        document.body.classList.add('cx-light');
    } else {
        document.body.classList.remove('cx-light');
    }
    document.documentElement.classList.remove('cx-prelight');
})();
</script>
@endpush
