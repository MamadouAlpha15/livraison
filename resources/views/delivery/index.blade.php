{{--
    resources/views/delivery/index.blade.php
    Route  : GET /delivery-companies → DeliveryCompanyController@index → name('delivery.companies.index')
    Variable: $companies  (LengthAwarePaginator<DeliveryCompany>)
--}}

@extends('layouts.app')

@section('title', 'Entreprises de livraison')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:     #10b981;
    --brand-dk:  #059669;
    --brand-lt:  #d1fae5;
    --brand-mlt: #ecfdf5;
    --bg:        #f6f8f7;
    --surface:   #ffffff;
    --border:    #e8eceb;
    --border-dk: #d4d9d7;
    --text:      #0f1c18;
    --text-2:    #4b5c56;
    --muted:     #8a9e98;
    --font:      'Plus Jakarta Sans', sans-serif;
    --mono:      'JetBrains Mono', monospace;
    --r:         14px;
    --r-sm:      9px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow:    0 4px 20px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
    --shadow-lg: 0 8px 32px rgba(0,0,0,.1), 0 2px 8px rgba(0,0,0,.06);
}

body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }

/* ── Page wrapper ── */
.page-wrap { max-width: 1100px; margin: 0 auto; padding: 28px 20px 60px; }

/* ── Back link ── */
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12.5px; font-weight: 600; color: var(--muted);
    text-decoration: none; margin-bottom: 22px;
    padding: 6px 10px 6px 6px;
    border-radius: var(--r-sm);
    transition: background .15s, color .15s;
}
.back-link:hover { background: var(--surface); color: var(--brand); }
.back-link .arrow { font-size: 15px; }

/* ── Hero header ── */
.page-hero {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 28px 32px;
    margin-bottom: 28px;
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 20px;
    box-shadow: var(--shadow-sm);
    position: relative; overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--brand), #34d399, #6ee7b7);
}
.hero-icon {
    width: 52px; height: 52px;
    background: var(--brand-mlt);
    border: 1px solid var(--brand-lt);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; flex-shrink: 0;
}
.hero-text { flex: 1; min-width: 0; }
.hero-title {
    font-size: 22px; font-weight: 700;
    color: var(--text); letter-spacing: -.4px;
    margin: 0 0 6px;
}
.hero-sub {
    font-size: 13px; color: var(--text-2);
    line-height: 1.6; margin: 0;
}
.hero-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* ── Stats bar ── */
.stats-bar {
    display: flex; gap: 6px;
    margin-bottom: 24px; flex-wrap: wrap;
}
.stat-chip {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 30px;
    padding: 6px 14px;
    font-size: 12px; font-weight: 600;
    color: var(--text-2);
    display: flex; align-items: center; gap: 6px;
    box-shadow: var(--shadow-sm);
}
.stat-chip .dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--brand);
    box-shadow: 0 0 5px rgba(16,185,129,.5);
}
.stat-chip strong { color: var(--text); font-family: var(--mono); }

/* ── Search / filter bar ── */
.filter-bar {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-sm);
    padding: 10px 14px;
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 22px;
    box-shadow: var(--shadow-sm);
}
.filter-bar input {
    flex: 1; border: none; outline: none;
    font-size: 13px; font-family: var(--font);
    color: var(--text); background: transparent;
}
.filter-bar input::placeholder { color: var(--muted); }
.filter-icon { font-size: 15px; flex-shrink: 0; }
.filter-count {
    font-size: 11px; font-weight: 600; color: var(--muted);
    font-family: var(--mono); white-space: nowrap;
}

/* ── Grid ── */
.companies-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-bottom: 32px;
}

/* ── Company card ── */
.company-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    display: flex; flex-direction: column;
    transition: box-shadow .2s, border-color .2s, transform .2s;
    animation: fadeUp .4s both;
}
.company-card:hover {
    box-shadow: var(--shadow-lg);
    border-color: var(--brand-lt);
    transform: translateY(-3px);
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Staggered animation */
.company-card:nth-child(1)  { animation-delay: .04s; }
.company-card:nth-child(2)  { animation-delay: .08s; }
.company-card:nth-child(3)  { animation-delay: .12s; }
.company-card:nth-child(4)  { animation-delay: .16s; }
.company-card:nth-child(5)  { animation-delay: .20s; }
.company-card:nth-child(6)  { animation-delay: .24s; }
.company-card:nth-child(7)  { animation-delay: .28s; }
.company-card:nth-child(8)  { animation-delay: .32s; }
.company-card:nth-child(9)  { animation-delay: .36s; }
.company-card:nth-child(10) { animation-delay: .40s; }
.company-card:nth-child(11) { animation-delay: .44s; }
.company-card:nth-child(12) { animation-delay: .48s; }

/* Card image zone */
.card-img-wrap {
    position: relative; height: 160px;
    background: linear-gradient(135deg, #f0f7f4, #e8f4f0);
    overflow: hidden; flex-shrink: 0;
}
.card-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s cubic-bezier(.23,1,.32,1);
}
.company-card:hover .card-img-wrap img { transform: scale(1.04); }

.card-img-placeholder {
    width: 100%; height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 6px;
}
.card-img-placeholder .pl-icon { font-size: 36px; opacity: .5; }
.card-img-placeholder .pl-txt  { font-size: 11px; color: var(--muted); font-weight: 500; }

/* Commission badge on image */
.commission-badge {
    position: absolute; top: 10px; right: 10px;
    background: rgba(15,28,24,.82);
    backdrop-filter: blur(6px);
    color: #fff; font-size: 11px; font-weight: 700;
    padding: 4px 10px; border-radius: 20px;
    font-family: var(--mono);
    border: 1px solid rgba(255,255,255,.12);
}
.commission-badge span { color: #34d399; }

/* Verified badge */
.verified-badge {
    position: absolute; top: 10px; left: 10px;
    background: var(--brand);
    color: #fff; font-size: 10px; font-weight: 700;
    padding: 3px 9px; border-radius: 20px;
    display: flex; align-items: center; gap: 4px;
}

/* Card body */
.card-body {
    padding: 16px 18px;
    display: flex; flex-direction: column;
    flex: 1;
}
.company-name {
    font-size: 15px; font-weight: 700; color: var(--text);
    margin: 0 0 4px; letter-spacing: -.2px;
}
.company-desc {
    font-size: 12px; color: var(--text-2);
    line-height: 1.55; margin: 0 0 12px;
    flex: 1;
}

/* Meta row */
.meta-row {
    display: flex; flex-direction: column; gap: 5px;
    margin-bottom: 14px; padding: 10px 12px;
    background: var(--bg);
    border-radius: var(--r-sm);
    border: 1px solid var(--border);
}
.meta-item {
    display: flex; align-items: center; gap: 7px;
    font-size: 11.5px; color: var(--text-2); font-weight: 500;
}
.meta-item .ico { font-size: 13px; width: 16px; text-align: center; flex-shrink: 0; }
.meta-item .val { color: var(--text); }

/* Rating */
.rating-row {
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 14px;
}
.stars { display: flex; gap: 2px; }
.star { font-size: 12px; color: #d1d5db; }
.star.on { color: #f59e0b; }
.rating-val { font-size: 12px; font-weight: 700; color: var(--text); font-family: var(--mono); }
.rating-count { font-size: 11px; color: var(--muted); }

/* Drivers chip */
.drivers-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--brand-mlt); color: var(--brand-dk);
    font-size: 11px; font-weight: 600;
    padding: 3px 9px; border-radius: 20px;
    border: 1px solid var(--brand-lt);
    margin-left: auto;
}

/* Card actions */
.card-actions {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 8px; margin-top: auto;
}
.card-actions.single { grid-template-columns: 1fr; }

/* ── Buttons ── */
.btn {
    display: inline-flex; align-items: center; justify-content: center;
    gap: 5px; padding: 8px 16px;
    border-radius: var(--r-sm);
    font-size: 12.5px; font-weight: 600; font-family: var(--font);
    border: 1px solid var(--border-dk); background: var(--surface);
    color: var(--text-2); cursor: pointer; text-decoration: none;
    transition: all .15s; white-space: nowrap;
}
.btn:hover { background: var(--bg); border-color: var(--brand); color: var(--brand); }
.btn-primary { background: var(--brand); color: #fff; border-color: var(--brand-dk); }
.btn-primary:hover { background: var(--brand-dk); color: #fff; border-color: var(--brand-dk); }
.btn-outline { background: transparent; }
.btn-lg { padding: 10px 22px; font-size: 13px; }
.btn-sm { padding: 6px 12px; font-size: 11.5px; }

/* ── Contact CTA (prominent) ── */
.btn-contact {
    background: var(--brand); color: #fff;
    border-color: var(--brand-dk);
    box-shadow: 0 2px 8px rgba(16,185,129,.3);
}
.btn-contact:hover {
    background: var(--brand-dk); color: #fff;
    box-shadow: 0 4px 14px rgba(16,185,129,.4);
}

/* ── Empty state ── */
.empty-state {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r);
    padding: 60px 32px;
    text-align: center;
    box-shadow: var(--shadow-sm);
}
.empty-icon { font-size: 48px; margin-bottom: 14px; }
.empty-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
.empty-sub { font-size: 13px; color: var(--muted); line-height: 1.6; max-width: 360px; margin: 0 auto 20px; }

/* ── Pagination ── */
.pagination-wrap {
    display: flex; justify-content: center;
}
.pagination-wrap nav { display: flex; gap: 4px; }
.pagination-wrap .page-link {
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    border-radius: var(--r-sm); border: 1px solid var(--border);
    font-size: 12.5px; font-weight: 600; color: var(--text-2);
    text-decoration: none; background: var(--surface);
    transition: all .15s;
}
.pagination-wrap .page-link:hover { border-color: var(--brand); color: var(--brand); }
.pagination-wrap .page-link.active { background: var(--brand); color: #fff; border-color: var(--brand-dk); }

/* ── Responsive ── */
@media (max-width: 860px) {
    .companies-grid { grid-template-columns: repeat(2, 1fr); }
    .page-hero { flex-direction: column; padding: 20px; }
    .hero-actions { width: 100%; }
}
@media (max-width: 540px) {
    .companies-grid { grid-template-columns: 1fr; gap: 14px; }
    .page-wrap { padding: 16px 14px 40px; }
    .page-hero { padding: 18px; }
    .hero-title { font-size: 18px; }
    .stats-bar { gap: 4px; }
    .stat-chip { font-size: 11px; padding: 5px 10px; }
}
</style>
@endpush

@section('content')
<div class="page-wrap">

    {{-- Back link --}}
    <a href="{{ url()->previous() }}" class="back-link">
        <span class="arrow">←</span> Retour au dashboard
    </a>

    {{-- Hero --}}
    <div class="page-hero">
        <div style="display:flex;align-items:flex-start;gap:16px;flex:1;min-width:0">
            <div class="hero-icon">🚚</div>
            <div class="hero-text">
                <h1 class="hero-title">Entreprises de livraison</h1>
                <p class="hero-sub">
                    Trouvez un partenaire de livraison fiable pour votre boutique.
                    Comparez les taux, consultez les livreurs disponibles et démarrez une discussion directement.
                </p>
            </div>
        </div>
        <div class="hero-actions">
            @auth
                @if(in_array(auth()->user()->role, ['admin', 'company']))
                <a href="{{ route('delivery.company.create') }}" class="btn btn-primary btn-lg">
                    ➕ Inscrire mon entreprise
                </a>
                @endif
            @endauth
        </div>
    </div>

    {{-- Stats chips --}}
    <div class="stats-bar">
        <div class="stat-chip">
            <span class="dot"></span>
            <strong>{{ $companies->total() }}</strong> entreprise{{ $companies->total() > 1 ? 's' : '' }} disponible{{ $companies->total() > 1 ? 's' : '' }}
        </div>
        <div class="stat-chip">
            📦 Livraison à la commande
        </div>
        <div class="stat-chip">
            💬 Contact direct par messagerie
        </div>
    </div>

    
    {{-- Flash messages --}}
    @foreach(['success','info','warning','danger'] as $type)
        @if(session($type))
        <div style="
            margin-bottom:16px; padding:10px 16px;
            font-size:12.5px; font-weight:500;
            border-radius:var(--r-sm); border:1px solid;
            background:{{ $type==='success'?'#ecfdf5':($type==='danger'?'#fef2f2':($type==='info'?'#eff6ff':'#fffbeb')) }};
            border-color:{{ $type==='success'?'#6ee7b7':($type==='danger'?'#fca5a5':($type==='info'?'#93c5fd':'#fcd34d')) }};
            color:{{ $type==='success'?'#065f46':($type==='danger'?'#991b1b':($type==='info'?'#1e40af':'#92400e')) }};
        ">{{ session($type) }}</div>
        @endif
    @endforeach

    {{-- Companies grid --}}
    @if($companies->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <div class="empty-title">Aucune entreprise disponible</div>
            <div class="empty-sub">
                Il n'y a pas encore d'entreprises de livraison approuvées.
                Vous pouvez en inscrire une ou revenir plus tard.
            </div>
            @auth
                @if(in_array(auth()->user()->role, ['admin', 'company']))
                <a href="{{ route('delivery.company.create') }}" class="btn btn-primary">
                    ➕ Inscrire une entreprise
                </a>
                @endif
            @endauth
        </div>
    @else
    <div class="companies-grid" id="companiesGrid">

        @foreach($companies as $company)
        @php
            $rating     = round($company->reviews_avg_rating ?? 0, 1);
            $fullStars  = floor($rating);
            $commission = $company->commission_percent ?? ($company->commission_rate ? $company->commission_rate * 100 : null);
        @endphp
        <div class="company-card" data-name="{{ strtolower($company->name) }} {{ strtolower($company->address ?? '') }}">

            {{-- Image --}}
            <div class="card-img-wrap">
                @if(!empty($company->image))
                    <img src="{{ asset('storage/'.$company->image) }}" alt="{{ $company->name }}">
                @else
                    <div class="card-img-placeholder">
                        <span class="pl-icon">🚚</span>
                        <span class="pl-txt">{{ $company->name }}</span>
                    </div>
                @endif

                {{-- Commission badge --}}
                @if($commission)
                <div class="commission-badge">
                    Commission <span>{{ number_format($commission, 1) }}%</span>
                </div>
                @endif

                {{-- Verified --}}
                <div class="verified-badge">✓ Approuvée</div>
            </div>

            {{-- Body --}}
            <div class="card-body">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px">
                    <h2 class="company-name">{{ $company->name }}</h2>
                    <div class="drivers-chip">
                        🚴 {{ $company->drivers()->count() }}
                    </div>
                </div>

                <p class="company-desc">
                    {{ $company->description ? Str::limit($company->description, 100) : 'Aucune description disponible.' }}
                </p>

                {{-- Meta --}}
                <div class="meta-row">
                    @if($company->phone)
                    <div class="meta-item">
                        <span class="ico">📞</span>
                        <span class="val">{{ $company->phone }}</span>
                    </div>
                    @endif
                    @if($company->address)
                    <div class="meta-item">
                        <span class="ico">📍</span>
                        <span class="val">{{ Str::limit($company->address, 40) }}</span>
                    </div>
                    @endif
                    @if($company->email)
                    <div class="meta-item">
                        <span class="ico">✉️</span>
                        <span class="val">{{ $company->email }}</span>
                    </div>
                    @endif
                </div>

                {{-- Rating --}}
                <div class="rating-row">
                    <div class="stars">
                        @for($s = 1; $s <= 5; $s++)
                            <span class="star {{ $s <= $fullStars ? 'on':'' }}">★</span>
                        @endfor
                    </div>
                    <span class="rating-val">{{ number_format($rating, 1) }}</span>
                    <span class="rating-count">/ 5</span>
                </div>

                {{-- Actions --}}
                <div class="card-actions">
                    <a href="{{ route('delivery.companies.show', $company) }}" class="btn btn-outline btn-sm">
                        👁 Voir détails
                    </a>
                    @auth
                        {{-- Bouton Contacter visible pour admin et vendeur --}}
                        @if(in_array(auth()->user()->role, ['admin', 'vendeur']))
                        <a href="{{ route('company.chat.show', $company) }}" class="btn btn-contact btn-sm">
                            💬 Contacter
                        </a>
                        @else
                        <span class="btn btn-sm" style="opacity:.4;cursor:default;pointer-events:none">
                            💬 Contacter
                        </span>
                        @endif
                    @else
                    <a href="{{ route('login') }}" class="btn btn-sm" style="border-color:var(--brand);color:var(--brand)">
                        Connexion requise
                    </a>
                    @endauth
                </div>

            </div>{{-- /card-body --}}
        </div>{{-- /company-card --}}
        @endforeach

    </div>{{-- /companies-grid --}}

    {{-- Pagination --}}
    @if($companies->hasPages())
    <div class="pagination-wrap">
        {{ $companies->links() }}
    </div>
    @endif

    @endif{{-- /empty check --}}

</div>{{-- /page-wrap --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Live search filter ── */
    const input   = document.getElementById('searchInput');
    const grid    = document.getElementById('companiesGrid');
    const counter = document.getElementById('filterCount');
    if (!input || !grid) return;

    const cards = Array.from(grid.querySelectorAll('.company-card'));

    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        let visible = 0;
        cards.forEach(card => {
            const match = !q || card.dataset.name.includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        counter.textContent = visible + ' résultat' + (visible > 1 ? 's' : '');
    });

});
</script>
@endpush