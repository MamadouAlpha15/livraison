@extends('layouts.app')

@push('styles')
<style>
  .shop-card-img {
    height: 160px;
    object-fit: cover;
    border-radius: .5rem .5rem 0 0;
  }
  .rating {
    color: #ffc107; /* jaune pour étoiles */
    font-size: .9rem;
  }
</style>
@endpush

@section('content')
<div class="container">
    <!-- ✅ CTA haut de page -->
    <div class="p-4 p-md-5 mb-4 bg-light rounded-3">
    <div class="col-md-10 px-0">
        <h1 class="display-6 fw-bold">Bienvenue sur {{ config('app.name') }}</h1>
        <p class="lead my-3">
            Découvrez nos boutiques partenaires et commandez en quelques clics 🚀
        </p>

        {{-- ================== BOUTONS ================== --}}
        @auth
            {{-- 🔹 bouton dashboard selon rôle --}}
            @php
                $role = Auth::user()->role;
                $map = [
                    'superadmin' => 'admin.dashboard',
                    'admin'       => 'boutique.dashboard',
                    'employe'     => 'employe.dashboard',
                    'vendeur'     => 'vendeur.dashboard',
                    'livreur'     => 'livreur.dashboard',
                    'client'      => 'client.dashboard',
                ];
            @endphp

            @if(isset($map[$role]) && Route::has($map[$role]))
                <a href="{{ route($map[$role]) }}" class="btn btn-success me-2">
                    <i class="bi bi-speedometer2"></i> Tableau de bord
                </a>
            @endif

            {{-- 🔹 bouton spécifique vendeur --}}
            @if($role === 'vendeur')
                <a href="{{ route('shop.create') }}" class="btn btn-primary">
                    🛍️ Créer ma boutique
                </a>
            @else
                <a href="{{ route('register') }}" class="btn btn-outline-primary">
                    🛍️ Devenir vendeur
                </a>
            @endif

        @else
            {{-- 🔹 utilisateur non connecté --}}
            <a href="{{ route('register') }}" class="btn btn-primary me-2">
                🛍️ Créer une boutique (Devenir vendeur)
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                <i class="bi bi-box-arrow-in-right"></i> Se connecter
            </a>
        @endauth
    </div>
</div>

    </div>
   
    <!-- ✅ Barre de recherche -->
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
            <button type="submit" class="btn btn-primary w-100">Rechercher</button>
        </div>
    </form>

    <!-- ✅ Liste des boutiques -->
    <h2 class="h4 mb-3">Boutiques disponibles</h2>

    <div class="row g-3">
        @forelse($shops as $shop)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    {{-- Image de la boutique --}}
                    @if($shop->image)
                        <img src="{{ asset('storage/'.$shop->image) }}" 
                             alt="{{ $shop->name }}" 
                             class="shop-card-img">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center shop-card-img">
                            <span class="text-muted">Pas d’image</span>
                        </div>
                    @endif

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title mb-1">{{ $shop->name }}</h5>
                            <span class="badge bg-secondary text-capitalize">
                                {{ $shop->type ?? 'boutique' }}
                            </span>
                        </div>

                        {{-- Note moyenne si avis --}}
                        @if($shop->reviews_avg_rating)
                            <div class="rating mb-2">
                                ⭐ {{ number_format($shop->reviews_avg_rating,1) }}/5
                            </div>
                        @endif

                        <p class="text-muted small mb-2">
                            {{ $shop->products_count }} produit(s)
                        </p>

                        <div class="mt-auto d-grid gap-2">
                            <a href="{{ route('public.shops.products', $shop) }}" class="btn btn-outline-primary btn-sm">
                                👀 Voir les produits
                            </a>

                            {{-- Abonnement boutique --}}
                            @auth
                                @if(Auth::user()->role === 'client')
                                    @if(!Auth::user()->subscribedShops->contains($shop->id))
                                        <form action="{{ route('client.shops.subscribe', $shop) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                ❤️ Suivre cette boutique
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-success">✅ Vous suivez déjà</span>
                                    @endif
                                @endif
                            @else
                                <a href="{{ route('register') }}" class="btn btn-success btn-sm">
                                    ➕ Suivre cette boutique
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    Aucune boutique disponible pour le moment.
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $shops->links() }}
    </div>
</div>
@endsection
