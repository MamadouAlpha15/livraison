{{--
    resources/views/delivery/index.blade.php
    Route  : GET /delivery-companies → DeliveryCompanyController@index → name('delivery.companies.index')
    Variable: $companies  (LengthAwarePaginator<DeliveryCompany>)
--}}

@extends('layouts.app')
@section('title', 'Entreprises de livraison · ShipXpress')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box}

:root{
    /* ── Palette boutique (indigo) ── */
    --brand:      #6366f1;
    --brand-dk:   #4f46e5;
    --brand-dkk:  #4338ca;
    --brand-lt:   #e0e7ff;
    --brand-mlt:  #eef2ff;
    --brand-glow: rgba(99,102,241,.22);
    --violet:     #7c3aed;
    --violet-lt:  #ede9fe;

    /* ── Surfaces ── */
    --bg:         #f8fafc;
    --surface:    #ffffff;
    --surface2:   #f1f5f9;
    --border:     #e2e8f0;
    --border-md:  #cbd5e1;

    /* ── Texte ── */
    --text:       #0f172a;
    --text2:      #475569;
    --muted:      #94a3b8;

    /* ── Statuts ── */
    --green:      #10b981;
    --gold:       #f59e0b;
    --red:        #ef4444;

    /* ── Ombres ── */
    --sh-sm: 0 1px 3px rgba(0,0,0,.05),0 1px 2px rgba(0,0,0,.03);
    --sh:    0 4px 20px rgba(0,0,0,.06),0 1px 4px rgba(0,0,0,.03);
    --sh-lg: 0 12px 40px rgba(0,0,0,.10),0 2px 8px rgba(0,0,0,.05);
    --sh-xl: 0 24px 64px rgba(0,0,0,.12),0 4px 12px rgba(0,0,0,.06);

    --r:    16px;
    --r-sm: 10px;
    --r-xs: 7px;
}

body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text);margin:0;-webkit-font-smoothing:antialiased;}
a{text-decoration:none;color:inherit;}

/* ════════════════════════════════════
   BARRE SUPÉRIEURE — style boutique
════════════════════════════════════ */
.page-banner{
    width:100%;
    background:linear-gradient(135deg,#3730a3 0%,#4f46e5 40%,#6366f1 75%,#818cf8 100%);
    padding:36px 0 32px;
    position:relative;overflow:hidden;
}
/* Cercles décoratifs */
.page-banner::before{
    content:'';position:absolute;top:-60px;right:-80px;
    width:340px;height:340px;border-radius:50%;
    background:radial-gradient(circle,rgba(255,255,255,.08) 0%,transparent 65%);
    pointer-events:none;
}
.page-banner::after{
    content:'';position:absolute;bottom:-100px;left:-60px;
    width:280px;height:280px;border-radius:50%;
    background:radial-gradient(circle,rgba(124,58,237,.18) 0%,transparent 65%);
    pointer-events:none;
}
/* Grille subtile */
.page-banner-grid{
    position:absolute;inset:0;
    background-image:
        linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),
        linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);
    background-size:40px 40px;
    pointer-events:none;
}
.banner-inner{
    max-width:1180px;margin:0 auto;padding:0 24px;
    display:flex;align-items:center;justify-content:space-between;gap:24px;
    position:relative;z-index:1;
}
.banner-l{display:flex;align-items:center;gap:18px;}
.banner-ico{
    width:58px;height:58px;border-radius:16px;flex-shrink:0;
    background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.22);
    backdrop-filter:blur(4px);
    display:flex;align-items:center;justify-content:center;font-size:26px;
    box-shadow:0 4px 20px rgba(0,0,0,.18);
}
.banner-title{
    font-size:26px;font-weight:800;color:#fff;
    letter-spacing:-.5px;margin:0 0 5px;
    text-shadow:0 2px 8px rgba(0,0,0,.15);
}
.banner-sub{
    font-size:13.5px;color:rgba(255,255,255,.76);
    line-height:1.6;margin:0;max-width:540px;
}

/* Chips stats dans la barre */
.banner-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px;}
.b-chip{
    display:inline-flex;align-items:center;gap:6px;
    background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);
    color:rgba(255,255,255,.9);font-size:12px;font-weight:600;
    padding:5px 14px;border-radius:30px;backdrop-filter:blur(4px);
}
.b-chip-dot{width:6px;height:6px;border-radius:50%;background:#6ee7b7;box-shadow:0 0 5px #6ee7b7;flex-shrink:0;}

/* ════ LAYOUT ════ */
.pw{max-width:1180px;margin:0 auto;padding:28px 24px 80px;}

/* ════ BACK ════ */
.back{
    display:inline-flex;align-items:center;gap:6px;
    font-size:12.5px;font-weight:600;color:var(--muted);
    padding:6px 12px 6px 8px;border-radius:var(--r-xs);
    border:1px solid transparent;margin-bottom:20px;
    transition:all .15s;
}
.back:hover{background:var(--surface);border-color:var(--border);color:var(--brand);}
.back svg{flex-shrink:0;transition:transform .15s;}
.back:hover svg{transform:translateX(-2px);}

/* ════ TOOLBAR ════ */
.toolbar{
    display:flex;align-items:center;gap:10px;
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r-sm);padding:10px 16px;
    margin-bottom:24px;box-shadow:var(--sh-sm);flex-wrap:wrap;
}
.search-wrap{
    flex:1;min-width:200px;display:flex;align-items:center;gap:9px;
    background:var(--surface2);border:1px solid var(--border);
    border-radius:var(--r-xs);padding:8px 12px;
    transition:border-color .15s,box-shadow .15s;
}
.search-wrap:focus-within{border-color:rgba(99,102,241,.45);box-shadow:0 0 0 3px rgba(99,102,241,.09);}
.search-wrap input{flex:1;border:none;outline:none;background:none;font-size:13px;font-family:inherit;color:var(--text);}
.search-wrap input::placeholder{color:var(--muted);}
.filter-count{font-size:12px;font-weight:600;color:var(--muted);white-space:nowrap;flex-shrink:0;}
.tb-sep{width:1px;height:22px;background:var(--border);flex-shrink:0;}

/* ════ FLASH ════ */
.flash{
    display:flex;align-items:flex-start;gap:10px;
    margin-bottom:16px;padding:12px 16px;
    border-radius:var(--r-sm);border:1px solid;font-size:13px;font-weight:500;
}
.flash-success{background:#f0fdf4;border-color:#bbf7d0;color:#166534;}
.flash-danger {background:#fef2f2;border-color:#fecaca;color:#991b1b;}
.flash-info   {background:var(--brand-mlt);border-color:var(--brand-lt);color:var(--brand-dk);}
.flash-warning{background:#fffbeb;border-color:#fde68a;color:#92400e;}

/* ════ GRID ════ */
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:36px;}

/* ════ CARD ════ */
.card{
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);overflow:hidden;display:flex;flex-direction:column;
    box-shadow:var(--sh-sm);
    transition:box-shadow .25s,border-color .25s,transform .25s;
    animation:fadeUp .45s both;
}
.card:hover{box-shadow:var(--sh-xl);border-color:#c7d2fe;transform:translateY(-4px);}
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.card:nth-child(1){animation-delay:.04s}.card:nth-child(2){animation-delay:.09s}
.card:nth-child(3){animation-delay:.14s}.card:nth-child(4){animation-delay:.19s}
.card:nth-child(5){animation-delay:.24s}.card:nth-child(6){animation-delay:.29s}
.card:nth-child(7){animation-delay:.34s}.card:nth-child(8){animation-delay:.39s}
.card:nth-child(9){animation-delay:.44s}

/* Image zone */
.card-img{
    position:relative;height:168px;
    background:linear-gradient(135deg,#eef2ff,#e0e7ff);
    overflow:hidden;flex-shrink:0;
}
.card-img img{
    width:100%;height:100%;object-fit:cover;display:block;
    transition:transform .5s cubic-bezier(.23,1,.32,1);
}
.card:hover .card-img img{transform:scale(1.06);}
.card-img-overlay{
    position:absolute;inset:0;
    background:linear-gradient(to top,rgba(15,23,42,.6) 0%,rgba(15,23,42,.1) 50%,transparent 100%);
}
.img-placeholder{
    width:100%;height:100%;display:flex;flex-direction:column;
    align-items:center;justify-content:center;gap:8px;
}
.img-placeholder-ico{font-size:40px;opacity:.35;}
.img-placeholder-txt{
    font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;
    color:var(--brand);opacity:.6;
}

/* Badges flottants sur l'image */
.img-badges{position:absolute;inset:10px 10px auto;display:flex;align-items:flex-start;justify-content:space-between;gap:6px;}
.badge-approved{
    display:inline-flex;align-items:center;gap:4px;
    background:rgba(79,70,229,.88);backdrop-filter:blur(8px);
    color:#fff;font-size:10px;font-weight:700;
    padding:4px 10px;border-radius:20px;letter-spacing:.3px;
    border:1px solid rgba(255,255,255,.18);
}
.badge-commission{
    display:inline-flex;align-items:center;
    background:rgba(15,23,42,.75);backdrop-filter:blur(8px);
    color:#fff;font-size:10.5px;font-weight:700;
    padding:4px 11px;border-radius:20px;
    border:1px solid rgba(255,255,255,.15);
    white-space:nowrap;
}
.badge-commission em{color:#a5b4fc;font-style:normal;}

/* Livreurs bas de l'image */
.img-bottom{
    position:absolute;bottom:0;left:0;right:0;
    padding:10px 14px;display:flex;align-items:center;gap:7px;
}
.driv-avatars{display:flex;}
.driv-av{
    width:22px;height:22px;border-radius:50%;
    border:2px solid rgba(255,255,255,.65);
    background:linear-gradient(135deg,var(--brand),var(--brand-dkk));
    display:flex;align-items:center;justify-content:center;
    font-size:7.5px;font-weight:800;color:#fff;
    margin-left:-6px;flex-shrink:0;
}
.driv-av:first-child{margin-left:0;}
.driv-count{font-size:11px;font-weight:700;color:rgba(255,255,255,.9);text-shadow:0 1px 4px rgba(0,0,0,.5);}

/* Card body */
.card-body{padding:18px 20px;display:flex;flex-direction:column;flex:1;}

.cname{
    font-size:15.5px;font-weight:800;color:var(--text);
    letter-spacing:-.3px;margin:0 0 5px;
    display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;
}
.cdesc{
    font-size:12.5px;color:var(--text2);line-height:1.6;
    margin:0 0 14px;flex:1;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}

/* Rating */
.rating-row{display:flex;align-items:center;gap:8px;margin-bottom:14px;}
.stars{display:flex;gap:1px;}
.star{font-size:13px;color:#e5e7eb;}
.star.on{color:var(--gold);}
.star.half{position:relative;color:#e5e7eb;}
.star.half::before{content:'★';position:absolute;left:0;top:0;color:var(--gold);width:50%;overflow:hidden;}
.r-val{font-size:13px;font-weight:800;color:var(--text);}
.r-cnt{font-size:11.5px;color:var(--muted);}

/* Meta chips */
.meta-chips{display:flex;flex-direction:column;gap:5px;margin-bottom:16px;}
.meta-chip{
    display:flex;align-items:center;gap:8px;
    padding:6px 10px;border-radius:var(--r-xs);
    background:var(--surface2);border:1px solid var(--border);
    font-size:12px;color:var(--text2);
}
.mc-ico{
    width:22px;height:22px;border-radius:5px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;font-size:11px;
}
.mc-phone .mc-ico{background:var(--brand-mlt);}
.mc-addr  .mc-ico{background:#ede9fe;}
.mc-mail  .mc-ico{background:#f0fdf4;}
.mc-val{font-weight:500;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;}

/* Divider */
.card-div{height:1px;background:var(--border);margin:0 20px 16px;}

/* Actions */
.card-actions{padding:0 20px 18px;display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.card-actions.single{grid-template-columns:1fr;}

/* Buttons */
.btn{
    display:inline-flex;align-items:center;justify-content:center;gap:6px;
    padding:9px 18px;border-radius:var(--r-xs);
    font-size:12.5px;font-weight:700;font-family:inherit;
    border:1px solid var(--border-md);background:var(--surface2);
    color:var(--text2);cursor:pointer;transition:all .16s;white-space:nowrap;
}
.btn:hover{background:var(--surface);border-color:var(--brand);color:var(--brand);}

.btn-ghost-brand{
    background:var(--brand-mlt);color:var(--brand-dk);border-color:var(--brand-lt);
}
.btn-ghost-brand:hover{background:var(--brand-lt);border-color:var(--brand);color:var(--brand-dk);}

.btn-contact{
    background:linear-gradient(135deg,var(--brand-dk),var(--brand));
    color:#fff;border-color:var(--brand-dkk);
    box-shadow:0 3px 10px rgba(99,102,241,.3);
}
.btn-contact:hover{
    background:linear-gradient(135deg,var(--brand-dkk),var(--brand-dk));
    box-shadow:0 5px 18px rgba(99,102,241,.42);transform:translateY(-1px);
}
.btn-login{background:var(--brand-mlt);color:var(--brand);border-color:var(--brand-lt);}
.btn-login:hover{background:var(--brand-lt);}
.btn-disabled{opacity:.38;cursor:not-allowed;pointer-events:none;}

/* ════ EMPTY ════ */
.empty{
    background:var(--surface);border:1px solid var(--border);
    border-radius:var(--r);padding:80px 40px;text-align:center;
    box-shadow:var(--sh-sm);
}
.empty-ico{
    width:80px;height:80px;border-radius:22px;
    background:linear-gradient(135deg,var(--brand-mlt),var(--brand-lt));
    border:1px solid var(--brand-lt);
    display:flex;align-items:center;justify-content:center;
    font-size:36px;margin:0 auto 20px;
}
.empty-title{font-size:20px;font-weight:800;color:var(--text);margin:0 0 8px;}
.empty-sub{font-size:13.5px;color:var(--muted);line-height:1.65;max-width:380px;margin:0 auto 24px;}

/* ════ PAGINATION ════ */
.pag{display:flex;justify-content:center;align-items:center;gap:5px;flex-wrap:wrap;}
.pag-btn{
    min-width:38px;height:38px;padding:0 10px;
    display:inline-flex;align-items:center;justify-content:center;
    border:1px solid var(--border);border-radius:var(--r-xs);
    font-size:13px;font-weight:600;color:var(--text2);
    background:var(--surface);cursor:pointer;transition:all .15s;
}
.pag-btn:hover{border-color:var(--brand);color:var(--brand);background:var(--brand-mlt);}
.pag-btn.on{background:var(--brand);color:#fff;border-color:var(--brand-dk);box-shadow:0 2px 8px rgba(99,102,241,.32);}
.pag-btn[aria-disabled="true"]{opacity:.35;pointer-events:none;}

/* ════ RESPONSIVE ════ */
@media(max-width:900px){
    .grid{grid-template-columns:repeat(2,1fr);}
    .banner-inner{flex-direction:column;align-items:flex-start;}
    .page-banner{padding:28px 0 24px;}
}
@media(max-width:580px){
    .grid{grid-template-columns:1fr;gap:14px;}
    .pw{padding:20px 14px 60px;}
    .banner-title{font-size:21px;}
    .card-actions{grid-template-columns:1fr;}
    .page-banner{padding:22px 0 20px;}
}
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════
     BARRE SUPÉRIEURE — couleur boutique
══════════════════════════════════════ --}}
<div class="page-banner">
    <div class="page-banner-grid"></div>
    <div class="banner-inner">
        <div>
            <div class="banner-l">
                <div class="banner-ico">🚚</div>
                <div>
                    <h1 class="banner-title">Entreprises de livraison</h1>
                    <p class="banner-sub">Trouvez le partenaire logistique idéal — comparez les taux, consultez les livreurs disponibles et démarrez une conversation directement.</p>
                </div>
            </div>
            <div class="banner-chips">
                <span class="b-chip">
                    <span class="b-chip-dot"></span>
                    <strong>{{ $companies->total() }}</strong>&nbsp;partenaire{{ $companies->total() > 1 ? 's' : '' }} disponible{{ $companies->total() > 1 ? 's' : '' }}
                </span>
                <span class="b-chip">📦 Livraison à la commande</span>
                <span class="b-chip">💬 Contact direct</span>
                <span class="b-chip">✅ Entreprises vérifiées</span>
            </div>
        </div>
    </div>
</div>

{{-- ══ PAGE BODY ══ --}}
<div class="pw">

    {{-- Back --}}
    <a href="{{route('boutique.dashboard') }}" class="back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Retour au dashboard
    </a>

    {{-- Flash messages --}}
    @foreach(['success'=>'flash-success','danger'=>'flash-danger','info'=>'flash-info','warning'=>'flash-warning'] as $k=>$cls)
        @if(session($k))
        <div class="flash {{ $cls }}">
            <span>{{ $k==='success'?'✅':($k==='danger'?'❌':($k==='info'?'ℹ️':'⚠️')) }}</span>
            {{ session($k) }}
        </div>
        @endif
    @endforeach

    {{-- Toolbar --}}
    <div class="toolbar">
        <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="searchInput" placeholder="Rechercher par nom, ville, description…">
        </div>
        <div class="tb-sep"></div>
        <span class="filter-count" id="filterCount">{{ $companies->count() }} résultat{{ $companies->count() > 1 ? 's' : '' }}</span>
    </div>

    {{-- Grid --}}
    @if($companies->isEmpty())

        <div class="empty">
            <div class="empty-ico">🔍</div>
            <div class="empty-title">Aucune entreprise disponible</div>
            <p class="empty-sub">Il n'y a pas encore d'entreprises de livraison approuvées. Revenez plus tard.</p>
        </div>

    @else

    <div class="grid" id="companiesGrid">
        @foreach($companies as $company)
        @php
            $rating      = round($company->reviews_avg_rating ?? 0, 1);
            $fullStars   = (int) floor($rating);
            $halfStar    = ($rating - $fullStars) >= 0.4;
            $reviewCount = $company->reviews_count ?? 0;
            $commission  = $company->commission_percent ?? ($company->commission_rate ? $company->commission_rate * 100 : null);
            $driverCount = $company->drivers_count ?? $company->drivers()->count();
        @endphp

        <div class="card" data-name="{{ strtolower($company->name.' '.($company->address ?? '').' '.($company->description ?? '')) }}">

            {{-- Image --}}
            <div class="card-img">
                @if(!empty($company->image))
                    <img src="{{ asset('storage/'.$company->image) }}" alt="{{ $company->name }}">
                    <div class="card-img-overlay"></div>
                @else
                    <div class="img-placeholder">
                        <span class="img-placeholder-ico">🚚</span>
                        <span class="img-placeholder-txt">{{ Str::limit($company->name, 18) }}</span>
                    </div>
                @endif

                <div class="img-badges">
                    <span class="badge-approved">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Approuvée
                    </span>
                   
                </div>

                @if($driverCount > 0)
                <div class="img-bottom">
                    <div class="driv-avatars">
                        @for($d = 0; $d < min($driverCount, 3); $d++)
                            <div class="driv-av">{{ chr(65 + $d) }}</div>
                        @endfor
                    </div>
                    <span class="driv-count">{{ $driverCount }} livreur{{ $driverCount > 1 ? 's' : '' }}</span>
                </div>
                @endif
            </div>

            {{-- Body --}}
            <div class="card-body">
                <div class="cname">{{ $company->name }}</div>
                <p class="cdesc">{{ $company->description ?? 'Aucune description disponible pour cette entreprise de livraison.' }}</p>

                {{-- Rating --}}
                <div class="rating-row">
                    <div class="stars">
                        @for($s = 1; $s <= 5; $s++)
                            @if($s <= $fullStars)
                                <span class="star on">★</span>
                            @elseif($s == $fullStars + 1 && $halfStar)
                                <span class="star half">★</span>
                            @else
                                <span class="star">★</span>
                            @endif
                        @endfor
                    </div>
                    @if($reviewCount > 0)
                        <span class="r-val">{{ number_format($rating, 1) }}</span>
                        <span class="r-cnt">({{ $reviewCount }} avis)</span>
                    @else
                        <span class="r-cnt" style="font-size:11.5px;">Aucun avis</span>
                    @endif
                </div>

                {{-- Meta --}}
                <div class="meta-chips">
                    @if($company->phone)
                    <div class="meta-chip mc-phone">
                        <div class="mc-ico">📞</div>
                        <span class="mc-val">{{ $company->phone }}</span>
                    </div>
                    @endif
                    @if($company->address)
                    <div class="meta-chip mc-addr">
                        <div class="mc-ico">📍</div>
                        <span class="mc-val">{{ Str::limit($company->address, 42) }}</span>
                    </div>
                    @endif
                    @if($company->email)
                    <div class="meta-chip mc-mail">
                        <div class="mc-ico">✉️</div>
                        <span class="mc-val">{{ $company->email }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card-div"></div>

            {{-- Actions --}}
            <div class="card-actions">
                <a href="{{ route('delivery.companies.show', $company) }}" class="btn btn-ghost-brand btn-sm">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Voir le profil
                </a>
                @auth
                    @if(in_array(auth()->user()->role, ['admin','vendeur']))
                    <a href="{{ route('company.chat.show', $company) }}" class="btn btn-contact">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Contacter
                    </a>
                    @else
                    <span class="btn btn-disabled">💬 Contacter</span>
                    @endif
                @else
                <a href="{{ route('login') }}" class="btn btn-login">
                    🔑 Se connecter
                </a>
                @endauth
            </div>

        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($companies->hasPages())
    <div style="display:flex;justify-content:center;margin-top:4px;">
        @php $pag = $companies; @endphp
        <div class="pag">
            @if($pag->onFirstPage())
                <span class="pag-btn" aria-disabled="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <a href="{{ $pag->previousPageUrl() }}" class="pag-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            @endif

            @foreach($pag->getUrlRange(max(1,$pag->currentPage()-2), min($pag->lastPage(),$pag->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="pag-btn {{ $page == $pag->currentPage() ? 'on' : '' }}">{{ $page }}</a>
            @endforeach

            @if($pag->hasMorePages())
                <a href="{{ $pag->nextPageUrl() }}" class="pag-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @else
                <span class="pag-btn" aria-disabled="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </span>
            @endif
        </div>
    </div>
    @endif

    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input   = document.getElementById('searchInput');
    const grid    = document.getElementById('companiesGrid');
    const counter = document.getElementById('filterCount');
    if (!input || !grid) return;

    const cards = Array.from(grid.querySelectorAll('.card'));

    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        let n = 0;
        cards.forEach(card => {
            const match = !q || (card.dataset.name || '').includes(q);
            card.style.display = match ? '' : 'none';
            if (match) n++;
        });
        counter.textContent = n + ' résultat' + (n > 1 ? 's' : '');
    });
});
</script>
@endpush
