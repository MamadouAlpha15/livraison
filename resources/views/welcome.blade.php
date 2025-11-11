@extends('layouts.app')

@push('styles')
<style>
.shop-card-img {
    height: 200px;
    object-fit: cover;
    border-radius: 15px 15px 0 0;
    transition: transform .3s;
}
.shop-card-img:hover { transform: scale(1.05); }
.shop-card { border-radius: 15px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.12); transition: transform .3s, box-shadow .3s; }
.shop-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.18); }
.btn-premium { border-radius: 50px; padding: 0.5rem 1.5rem; font-weight: 600; }
.rating { color: #ffc107; font-size: 0.9rem; }

/* CTA entreprise */
.company-cta {
    background: linear-gradient(90deg,#0d6efd08,#e9f2ff);
    border: 1px dashed rgba(13,110,253,0.15);
    padding: 18px;
    border-radius: .8rem;
}
.company-badge { background: linear-gradient(90deg,#0d6efd,#6610f2); color: #fff; padding: .25rem .6rem; border-radius: .6rem; font-weight:700; font-size:.85rem; }

/* réductions images produit */
.product-thumb { width:60px; height:60px; object-fit:cover; border-radius:.5rem; }
@media (max-width:767.98px){ .product-thumb{ width:48px; height:48px; } }

.card{ border-radius:.9rem; }
.table-hover tbody tr:hover{ background: rgba(13,110,253,0.03); }
</style>
@endpush

@section('content')
<div class="container py-4">

    <!-- Hero Section -->
    <div class="text-center mb-5 p-5 bg-light rounded-4 shadow-sm">
        <h1 class="display-5 fw-bold">Bienvenue sur {{ config('app.name') }}</h1>
        <p class="lead">Découvrez nos boutiques partenaires et commandez vos produits favoris en quelques clics 🚀</p>

        @auth
            @php
                $role = Auth::user()->role;
                $map = [
                    'superadmin' => 'admin.dashboard',
                    'admin'      => 'boutique.dashboard',
                    'employe'    => 'employe.dashboard',
                    'vendeur'    => 'vendeur.dashboard',
                    'livreur'    => 'livreur.dashboard',
                    'client'     => 'client.dashboard',
                    'company'    => 'company.dashboard'
                ];
            @endphp

            {{-- Dashboard pour tous les connectés --}}
            @if(isset($map[$role]) && Route::has($map[$role]))
                <a href="{{ route($map[$role]) }}" class="btn btn-success btn-premium me-2">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            @endif

            {{-- Si vendeur, bouton Créer boutique --}}
            @if($role === 'vendeur')
                <a href="{{ route('shop.create') }}" class="btn btn-primary btn-premium">
                    🏪 Créer ma boutique
                </a>
            @endif

            {{-- Si connecté mais pas entreprise, proposer de s'inscrire en tant qu'entreprise --}}
            @if(!in_array($role, ['company','livreur']) && Route::has('register'))
                <a href="{{ route('register', ['role' => 'company']) }}" class="btn btn-outline-dark btn-premium ms-2">
                    🚚 S'inscrire en tant qu'entreprise de livraison
                </a>
            @endif

        @else
            {{-- Visiteurs non connectés --}}
            <a href="{{ route('register') }}" class="btn btn-primary btn-premium me-2">
                🛍️ S’inscrire (Client ou Vendeur)
            </a>
            <a href="{{ route('register', ['role' => 'company']) }}" class="btn btn-outline-success btn-premium me-2">
                🚚 S’inscrire en tant qu’entreprise (livraison)
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-premium">
                Se connecter
            </a>
        @endauth
    </div>

    <!-- Recherche -->
    <form method="GET" action="{{ url('/') }}" class="row g-2 mb-4">
        <div class="col-12 col-md-6">
            <input type="text" name="q" class="form-control" placeholder="🔎 Rechercher une boutique ou un produit..." value="{{ request('q') }}">
        </div>
        <div class="col-12 col-md-3">
            <select name="type" class="form-select">
                <option value="">-- Tous les types --</option>
                <option value="restaurant" {{ request('type')=='restaurant'?'selected':'' }}>🍔 Restaurant</option>
                <option value="pharmacie" {{ request('type')=='pharmacie'?'selected':'' }}>💊 Pharmacie</option>
                <option value="mode" {{ request('type')=='mode'?'selected':'' }}>👗 Mode</option>
                <option value="electronique" {{ request('type')=='electronique'?'selected':'' }}>📱 Électronique</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <button type="submit" class="btn btn-primary w-100 btn-premium">Rechercher</button>
        </div>
    </form>

    <!-- Boutiques -->
    <h2 class="h4 mb-3">Boutiques disponibles</h2>
    <div class="row g-4">
        @forelse($shops as $shop)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card shop-card h-100">
                    @if($shop->image)
                        <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}" class="shop-card-img w-100">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center shop-card-img">
                            <span class="text-muted">Pas d’image</span>
                        </div>
                    @endif

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $shop->name }}</h5>
                        <span class="badge bg-secondary text-capitalize mb-2">{{ $shop->type ?? 'boutique' }}</span>

                        @if($shop->reviews_avg_rating)
                            <div class="rating mb-2">⭐ {{ number_format($shop->reviews_avg_rating,1) }}/5</div>
                        @endif
                        <p class="text-muted small mb-3">{{ $shop->products_count }} produit(s)</p>

                        <div class="mt-auto d-grid gap-2">
                            <a href="{{ route('public.shops.products', $shop) }}" class="btn btn-outline-primary btn-sm btn-premium">
                                👀 Voir les produits
                            </a>

                            @auth
                                @if(Auth::user()->role === 'client')
                                    <a href="{{ route('orders.create', ['shop' => $shop->id]) }}" class="btn btn-success btn-sm btn-premium">🛒 Commander</a>
                                @elseif(Auth::user()->role === 'vendeur')
                                    <a href="{{ route('shop.create') }}" class="btn btn-warning btn-sm btn-premium">
                                        🏪 Créer ma boutique
                                    </a>
                               
                                @endif
                           
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">Aucune boutique disponible pour le moment.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $shops->links() }}
    </div>

    <!-- Section spéciale : Entreprises de livraison (CTA + listing) -->
    <div class="mt-5">
        <div class="company-cta d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="company-badge d-inline-block mb-2">ENTREPRISES DE LIVRAISON</div>
                <h4 class="mb-1">Vous êtes une entreprise de livraison ? Rejoignez notre plateforme</h4>
                <p class="mb-0 text-muted">Inscrivez votre entreprise, ajoutez vos livreurs avec photo et recevez des demandes de boutiques en recherche de livraison.</p>
            </div>

            <div class="text-end">
                @auth
                    @if(Auth::user()->role !== 'company')
                        <a href="{{ route('register', ['role' => 'company']) }}" class="btn btn-primary btn-premium">🚚 S’inscrire en tant qu’entreprise</a>
                    @else
                        <a href="{{ route('company.dashboard') }}" class="btn btn-outline-primary btn-premium">📋 Mon espace entreprise</a>
                    @endif
                @else
                    <a href="{{ route('register', ['role' => 'company']) }}" class="btn btn-primary btn-premium">🚚 S’inscrire en tant qu’entreprise</a>
                @endauth
            </div>
        </div>

        @if(isset($companies) && $companies->count())
            <div class="row g-3">
                @foreach($companies as $c)
                    <div class="col-12 col-md-4">
                        <div class="card p-3 h-100">
                            <div class="d-flex gap-3">
                                @php
                                    $companyImage = $c->image && file_exists(storage_path('app/public/'.$c->image))
                                        ? asset('storage/'.$c->image)
                                        : asset('images/company-placeholder.png');
                                @endphp
                                <img src="{{ $companyImage }}"
                                     style="width:80px; height:80px; object-fit:cover; border-radius:.6rem;">
                                <div>
                                    <h5 class="mb-1">{{ $c->name }}</h5>
                                    <div class="small text-muted">{{ Str::limit($c->description, 80) }}</div>
                                    <div class="mt-2">
                                        <a href="{{ route('delivery.companies.show', $c) }}" class="btn btn-sm btn-outline-primary">Contacter</a>
                                        <a href="{{ route('company.chat.show', $c, ['shop' => null]) }}" class="btn btn-sm btn-primary">Chat</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
