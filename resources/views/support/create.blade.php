@extends('layouts.app')
@section('title', 'Nouveau ticket · Support')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
:root {
    --brand:#6366f1;--brand-dk:#4f46e5;--brand-lt:#e0e7ff;--brand-mlt:#eef2ff;
    --sb-bg:#0e0e16;--sb-border:rgba(255,255,255,.08);--sb-act:rgba(99,102,241,.52);
    --sb-hov:rgba(255,255,255,.07);--sb-txt:rgba(255,255,255,.62);--sb-txt-act:#fff;
    --bg:#f8fafc;--surface:#ffffff;--border:#e2e8f0;--border-dk:#cbd5e1;
    --text:#0f172a;--text-2:#475569;--muted:#94a3b8;
    --font:'Plus Jakarta Sans',sans-serif;--mono:'JetBrains Mono',monospace;
    --r:14px;--r-sm:9px;--shadow-sm:0 1px 3px rgba(0,0,0,.06);--shadow:0 4px 16px rgba(0,0,0,.07);
    --sb-w:232px;--top-h:58px;
}
html{font-family:var(--font);}
body{background:var(--bg);margin:0;color:var(--text);-webkit-font-smoothing:antialiased;}
.dash-wrap{display:flex;min-height:100vh;}
.dash-wrap .main{margin-left:var(--sb-w);flex:1;min-width:0;}

/* ─── SIDEBAR ─── */
.sidebar{background:linear-gradient(180deg,#0f0f59 0%,#0e0e16 40%,#10103a 100%);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);overflow-y:scroll;scrollbar-width:thin;scrollbar-color:rgba(99,102,241,.35) transparent;z-index:40;border-right:1px solid rgba(99,102,241,.15);box-shadow:6px 0 30px rgba(0,0,0,.35);}
.sidebar::-webkit-scrollbar{width:4px;}
.sidebar::-webkit-scrollbar-track{background:rgba(255,255,255,.04);}
.sidebar::-webkit-scrollbar-thumb{background:rgba(99,102,241,.4);border-radius:4px;}
.sidebar::-webkit-scrollbar-thumb:hover{background:rgba(99,102,241,.7);}
.sb-brand{padding:18px 16px 14px;border-bottom:1px solid var(--sb-border);flex-shrink:0;position:relative;}
.sb-close{display:none;position:absolute;top:14px;right:12px;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.10);color:rgba(255,255,255,.6);font-size:18px;line-height:1;cursor:pointer;align-items:center;justify-content:center;transition:background .15s,color .15s;flex-shrink:0;}
.sb-close:hover{background:rgba(239,68,68,.18);border-color:rgba(239,68,68,.3);color:#fca5a5;}
@media(max-width:900px){.sb-close{display:flex;}}
.sb-logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:#fff;}
.sb-logo-icon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;}
.sb-shop-name{font-size:14.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:148px;letter-spacing:-.3px;color:#fff;}
.sb-status{display:flex;align-items:center;gap:6px;margin-top:9px;font-size:10.5px;color:var(--sb-txt);font-weight:500;}
.pulse{width:6px;height:6px;border-radius:50%;background:var(--brand);flex-shrink:0;animation:blink 2.2s ease-in-out infinite;box-shadow:0 0 5px var(--brand);}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}
.sb-nav{padding:10px 10px 32px;flex:1;display:flex;flex-direction:column;gap:1px;overflow:visible;}
.sb-section{font-size:9.5px;text-transform:uppercase;letter-spacing:1.8px;color:rgba(255,255,255,.48);padding:16px 10px 5px;font-weight:800;}
.sb-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);text-decoration:none;transition:background .15s,color .15s;position:relative;}
.sb-item:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-item.active{background:var(--sb-act);color:#fff;box-shadow:0 2px 12px rgba(99,102,241,.25);}
.sb-item.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:20px;background:#a5b4fc;border-radius:0 3px 3px 0;box-shadow:2px 0 8px rgba(165,180,252,.5);}
.sb-item .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);transition:background .15s;}
.sb-item:hover .ico{background:rgba(255,255,255,.09);}
.sb-item.active .ico{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.2);}
.sb-group{display:flex;flex-direction:column;}
.sb-group-toggle{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);font-size:13.5px;font-weight:600;color:rgba(255,255,255,.78);cursor:pointer;transition:background .15s,color .15s;user-select:none;border:none;background:none;width:100%;text-align:left;font-family:var(--font);}
.sb-group-toggle:hover{background:var(--sb-hov);color:rgba(255,255,255,.96);}
.sb-group-toggle.open{color:#fff;background:rgba(255,255,255,.05);}
.sb-group-toggle .ico{font-size:13px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06);}
.sb-group-toggle .sb-arrow{margin-left:auto;font-size:10px;color:rgba(255,255,255,.32);transition:transform .2s;flex-shrink:0;}
.sb-group-toggle.open .sb-arrow{transform:rotate(90deg);color:rgba(255,255,255,.6);}
.sb-sub{display:none;flex-direction:column;gap:1px;margin-left:12px;padding-left:14px;border-left:1px solid rgba(255,255,255,.1);margin-top:2px;margin-bottom:4px;overflow:visible;}
.sb-sub.open{display:flex;}
.sb-sub .sb-item{font-size:13px;font-weight:500;padding:6px 10px;color:rgba(255,255,255,.62);}
.sb-sub .sb-item:hover{color:rgba(255,255,255,.92);}
.sb-sub .sb-item.active{color:#fff;background:var(--sb-act);font-weight:600;}
.sb-scroll-hint{position:sticky;top:auto;bottom:72px;width:100%;height:40px;background:linear-gradient(to bottom,transparent,rgba(17,17,24,.95));pointer-events:none;z-index:2;display:flex;align-items:flex-end;justify-content:center;padding-bottom:6px;transition:opacity .3s;margin-top:-40px;align-self:flex-end;}
.sb-scroll-hint.hidden{opacity:0;pointer-events:none;}
.sb-scroll-hint-arrow{display:flex;flex-direction:column;align-items:center;gap:2px;animation:bounceDown 1.5s ease-in-out infinite;}
.sb-scroll-hint-dot{width:4px;height:4px;border-radius:50%;background:rgba(99,102,241,.6);}
.sb-scroll-hint-dot:nth-child(2){opacity:.5;margin-top:-2px;}
.sb-scroll-hint-dot:nth-child(3){opacity:.25;margin-top:-2px;}
@keyframes bounceDown{0%,100%{transform:translateY(0)}50%{transform:translateY(4px)}}
.sb-footer{padding:12px 10px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0;display:flex;flex-direction:column;gap:6px;position:sticky;bottom:0;background:linear-gradient(180deg,transparent 0%,#0b0b12 25%);z-index:1;}
.sb-user{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:var(--r-sm);text-decoration:none;transition:background .15s,border-color .15s;border:1px solid transparent;}
.sb-user:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.07);}
.sb-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#4338ca);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0;box-shadow:0 0 0 2px rgba(99,102,241,.45),0 2px 8px rgba(99,102,241,.3);letter-spacing:-.5px;}
.sb-uname{font-size:13px;font-weight:700;color:#fff;letter-spacing:-.2px;}
.sb-urole{font-size:10.5px;color:rgba(255,255,255,.52);margin-top:1px;font-weight:500;}
.sb-logout{display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border-radius:var(--r-sm);background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.15);color:rgba(252,165,165,.92);font-size:12.5px;font-weight:600;font-family:var(--font);cursor:pointer;text-decoration:none;transition:background .15s,color .15s,border-color .15s;text-align:left;}
.sb-logout:hover{background:rgba(220,38,38,.18);border-color:rgba(220,38,38,.35);color:#fca5a5;}
.sb-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:39;}

/* ─── TOPBAR & MAIN ─── */
.main{display:flex;flex-direction:column;min-width:0;}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 22px;height:var(--top-h);display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:30;box-shadow:var(--shadow-sm);}
.btn-hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;color:var(--text);font-size:20px;}
.tb-bc{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;flex:1;overflow:hidden;}
.tb-bc a{color:var(--muted);text-decoration:none}.tb-bc a:hover{color:var(--brand)}
.tb-bc span{color:var(--text);font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.content{padding:20px 22px;flex:1;}

/* ─── FORM CARD ─── */
.form-wrap{max-width:680px;margin:0 auto;}
.info-banner{display:flex;align-items:flex-start;gap:12px;padding:14px 18px;background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.2);border-radius:10px;margin-bottom:22px;font-size:13px;color:#3730a3;font-weight:500;}
.form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:28px 32px;box-shadow:var(--shadow-sm);}
.form-title{font-size:18px;font-weight:800;margin-bottom:4px;color:var(--text);}
.form-sub{font-size:13px;color:var(--muted);margin-bottom:24px;}
.field{margin-bottom:20px;}
.field label{display:block;font-size:13px;font-weight:700;color:var(--text);margin-bottom:7px;}
.field input,.field textarea,.field select{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:var(--r-sm);font-size:13.5px;font-family:var(--font);color:var(--text);background:var(--bg);outline:none;transition:border-color .15s,box-shadow .15s;resize:vertical;}
.field input:focus,.field textarea:focus{border-color:var(--brand);box-shadow:0 0 0 3px rgba(99,102,241,.1);background:#fff;}
.field .hint{font-size:11.5px;color:var(--muted);margin-top:5px;}
.category-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.cat-chip{padding:6px 14px;border-radius:20px;border:1.5px solid var(--border);background:transparent;font-size:12.5px;font-weight:600;color:var(--text-2);cursor:pointer;font-family:var(--font);transition:all .15s;}
.cat-chip:hover{border-color:var(--brand);color:var(--brand);}
.cat-chip.selected{background:var(--brand);border-color:var(--brand);color:#fff;}
.actions{display:flex;align-items:center;gap:12px;margin-top:28px;padding-top:20px;border-top:1px solid var(--border);}
.btn-submit{display:inline-flex;align-items:center;gap:8px;padding:11px 24px;border-radius:var(--r-sm);background:var(--brand);color:#fff;font-size:14px;font-weight:700;border:none;cursor:pointer;font-family:var(--font);transition:background .14s;}
.btn-submit:hover{background:var(--brand-dk);}
.btn-cancel-lnk{display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:var(--r-sm);background:transparent;border:1.5px solid var(--border);color:var(--text-2);font-size:13.5px;font-weight:600;text-decoration:none;font-family:var(--font);transition:all .14s;}
.btn-cancel-lnk:hover{border-color:var(--brand);color:var(--brand);}
.err{font-size:12px;color:#ef4444;margin-top:5px;font-weight:600;}

/* ─── RESPONSIVE ─── */
@media(max-width:900px){
    :root{--sb-w:230px;}
    .dash-wrap .main{margin-left:0;}
    .sidebar{transform:translateX(-100%);transition:transform .25s cubic-bezier(.23,1,.32,1);}
    .sidebar.open{transform:translateX(0);}
    .sb-overlay.open{display:block;}
    .btn-hamburger{display:flex;}
    .content{padding:14px;}
}
@media(max-width:640px){
    .content{padding:10px;}
    .topbar{padding:0 12px;gap:8px;}
    .form-card{padding:18px 16px;}
    .form-title{font-size:16px;}
    .actions{flex-direction:column;align-items:stretch;}
    .btn-submit,.btn-cancel-lnk{justify-content:center;}
    .category-chips{gap:6px;}
    .cat-chip{padding:5px 11px;font-size:12px;}
}
@media(max-width:480px){
    .content{padding:8px;}
    .form-card{padding:14px 12px;}
    .info-banner{padding:10px 12px;font-size:12px;}
}
@media(max-width:360px){
    .topbar{padding:0 8px;}
}
</style>
@endpush

@section('content')
@php
    $u        = Auth::user();
    $parts    = explode(' ', $u->name ?? 'U');
    $initials = strtoupper(substr($parts[0],0,1)).strtoupper(substr($parts[1] ?? 'X',0,1));
    $categories = ['Problème technique','Commande / Paiement','Livraison','Mon compte','Autre'];
@endphp

<div class="dash-wrap">

    {{-- ════ SIDEBAR ════ --}}
    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
                <div class="sb-logo-icon"><img src="/images/Shopio3.jpeg" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div>
                <span class="sb-shop-name">{{ $shop->name ?? 'Boutique' }}</span>
            </a>
            <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">✕</button>
            <div class="sb-status">
                <span class="pulse"></span>
                Boutique active &nbsp;·&nbsp; {{ ucfirst($u->role_in_shop ?? $u->role) }}
            </div>
        </div>
        <div class="sb-scroll-hint" id="sbScrollHint">
            <div class="sb-scroll-hint-arrow">
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
            </div>
        </div>
        <nav class="sb-nav">
            <a href="{{ route('boutique.dashboard') }}" class="sb-item" style="margin-bottom:4px"><span class="ico">⊞</span> Tableau de bord</a>
            <div class="sb-section">Boutique</div>
            <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">💬</span> Messages</a>
            <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">📦</span> Commandes</a>
            <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">🏷️</span> Produits</a>
            <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">👥</span> Clients</a>
            <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">🧑‍💼</span> Équipe</a>
            <div class="sb-section">Livraison</div>
            <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">🚴</span> Livreurs</a>
            <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">🏢</span> Partenaires</a>
            <div class="sb-section">Finances</div>
            <div class="sb-group">
                <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                    <span class="ico">💰</span> Finances &amp; Rapports <span class="sb-arrow">▶</span>
                </button>
                <div class="sb-sub">
                    <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">💳</span> Paiements</a>
                    <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">📊</span> Commissions</a>
                    <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">📋</span> Rapports</a>
                    @if($u->role === 'admin')
                    <a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">⚙️</span> Paramètres</a>
                    @endif
                </div>
            </div>
            <div class="sb-section">Aide</div>
            <a href="{{ route('support.index') }}" class="sb-item active"><span class="ico">🎧</span> Support</a>
        </nav>
        <div class="sb-footer">
            <a href="{{ route('profile.edit') }}" class="sb-user">
                <div class="sb-av">{{ $initials }}</div>
                <div style="flex:1;min-width:0">
                    <div class="sb-uname">{{ Str::limit($u->name, 20) }}</div>
                    <div class="sb-urole">{{ $u->role === 'admin' ? 'Administrateur' : ucfirst($u->role) }}</div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout"><span class="ico">⎋</span> Se déconnecter</button>
            </form>
        </div>
    </aside>

    <div class="sb-overlay" id="sbOverlay"></div>

    {{-- ════ MAIN ════ --}}
    <main class="main">
        <div class="topbar">
            <button class="btn-hamburger" id="btnMenu">☰</button>
            <div class="tb-bc">
                <a href="{{ route('boutique.dashboard') }}">🏪 {{ $shop->name ?? 'Boutique' }}</a>
                <span style="color:var(--muted);font-weight:400">›</span>
                <a href="{{ route('support.index') }}">🎧 Support</a>
                <span style="color:var(--muted);font-weight:400">›</span>
                <span>Nouveau ticket</span>
            </div>
        </div>

        <div class="content">
            <div class="form-wrap">

                <div class="info-banner">
                    <span style="font-size:18px">💬</span>
                    <div>Votre message sera transmis directement au <strong>SuperAdmin de la plateforme</strong>. Vous recevrez une réponse dans les meilleurs délais.</div>
                </div>

                <div class="form-card">
                    <div class="form-title">✚ Nouveau ticket de support</div>
                    <div class="form-sub">Décrivez votre problème ou question le plus précisément possible.</div>

                    <form method="POST" action="{{ route('support.store') }}" id="createForm">
                        @csrf
                        @if($shop)<input type="hidden" name="shop_id" value="{{ $shop->id }}">@endif
                        <input type="hidden" name="category" id="categoryInput" value="">

                        <div class="field">
                            <label>Catégorie du problème</label>
                            <div class="category-chips">
                                @foreach($categories as $cat)
                                <button type="button" class="cat-chip" onclick="selectCat(this, '{{ $cat }}')">{{ $cat }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="field">
                            <label>Sujet <span style="color:#ef4444">*</span></label>
                            <input type="text" name="subject" maxlength="160" required
                                   value="{{ old('subject') }}"
                                   placeholder="Ex : Impossible de créer une commande…"
                                   oninput="document.getElementById('subjectCount').textContent = this.value.length">
                            <div class="hint"><span id="subjectCount">0</span>/160 caractères</div>
                            @error('subject')<div class="err">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label>Description du problème <span style="color:#ef4444">*</span></label>
                            <textarea name="message" rows="6" required
                                      placeholder="Décrivez le problème en détail : ce que vous faisiez, ce qui s'est passé, les messages d'erreur éventuels…"
                                      oninput="document.getElementById('msgCount').textContent = this.value.length">{{ old('message') }}</textarea>
                            <div class="hint"><span id="msgCount">0</span> caractères</div>
                            @error('message')<div class="err">{{ $message }}</div>@enderror
                        </div>

                        <div class="actions">
                            <button type="submit" class="btn-submit">🚀 Envoyer le ticket</button>
                            <a href="{{ route('support.index') }}" class="btn-cancel-lnk">Annuler</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </main>

</div>
@endsection

@push('scripts')
<script>
// Sidebar mobile
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sbOverlay');
const btnMenu  = document.getElementById('btnMenu');
const btnClose = document.getElementById('btnCloseSidebar');

function openSb()  { sidebar.classList.add('open'); overlay.classList.add('open'); }
function closeSb() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }
if (btnMenu)  btnMenu.addEventListener('click', openSb);
if (btnClose) btnClose.addEventListener('click', closeSb);
if (overlay)  overlay.addEventListener('click', closeSb);

// Accordion finances
function toggleGroup(btn) {
    btn.classList.toggle('open');
    const sub = btn.nextElementSibling;
    if (sub) sub.classList.toggle('open');
}

// Scroll hint
function updateScrollHint() {
    const sb = sidebar;
    const hint = document.getElementById('sbScrollHint');
    if (!sb || !hint) return;
    const atBottom = sb.scrollHeight - sb.scrollTop - sb.clientHeight < 30;
    hint.classList.toggle('hidden', atBottom);
}
if (sidebar) { sidebar.addEventListener('scroll', updateScrollHint); updateScrollHint(); }

// Catégorie chips
function selectCat(el, val) {
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('categoryInput').value = val;
    const subj = document.querySelector('input[name=subject]');
    if (subj && !subj.value) {
        subj.value = val + ' — ';
        subj.focus();
        subj.selectionStart = subj.value.length;
        document.getElementById('subjectCount').textContent = subj.value.length;
    }
}

// Pré-remplir compteurs si old()
document.addEventListener('DOMContentLoaded', () => {
    const subj = document.querySelector('input[name=subject]');
    const msg  = document.querySelector('textarea[name=message]');
    if (subj) document.getElementById('subjectCount').textContent = subj.value.length;
    if (msg)  document.getElementById('msgCount').textContent = msg.value.length;
});
</script>
@endpush
