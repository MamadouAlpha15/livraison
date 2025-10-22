@extends('layouts.app')

@push('styles')
<style>
  .shop-thumb {
      height: 150px;
      object-fit: cover;
      border-radius: .5rem .5rem 0 0;
  }
</style>
@endpush

@section('content')
<div class="container">
    <h1 class="mb-4">🛒 Tableau de bord Client</h1>

    {{-- ====== Cartes principales ====== --}}
    <div class="row g-3">
        <!-- 📦 Mes commandes -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">📦 Mes commandes</h5>
                    <p class="card-text">Consultez l’historique et le suivi de vos commandes.</p>
                    <a href="{{ route('client.orders.index') }}" class="btn btn-primary w-100">Voir</a>
                </div>
            </div>
        </div>

        <!-- 🛒 Nouvelle commande -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">🛒 Nouvelle commande</h5>
                    <p class="card-text">Passez une nouvelle commande dans une boutique.</p>
                    <a href="{{ route('client.orders.create') }}" class="btn btn-success w-100">Commander</a>
                </div>
            </div>
        </div>

        <!-- ✍️ Avis -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">✍️ Avis & Évaluations</h5>
                    <p class="card-text">Donnez un avis sur vos commandes livrées.</p>
                    <a href="{{ route('client.orders.index') }}" class="btn btn-warning w-100">Laisser un avis</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ====== Profil & Notifications ====== --}}
    <div class="row g-3 mt-3">
        <!-- 👤 Profil -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">👤 Mon Profil</h5>
                    <p class="card-text">Modifier mes informations personnelles.</p>
                    <a href="{{ route('profile.edit') }}" class="btn btn-info w-100">Modifier</a>
                </div>
            </div>
        </div>

        <!-- 🔔 Notifications -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">🔔 Notifications</h5>
                    <p class="card-text">Consultez vos dernières notifications.</p>
                    <a href="{{ route('notifications.readAll') }}" class="btn btn-danger w-100">Voir tout</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ====== Boutiques disponibles ====== --}}
    <div class="mt-5">
        <h3 class="mb-3">🏪 Boutiques disponibles</h3>

        <div class="row g-3">
            @forelse($shops as $shop)
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        {{-- Image --}}
                        @if($shop->image)
                            <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}" class="shop-thumb w-100">
                        @endif

                        <div class="card-body d-flex flex-column text-center">
                            <h5 class="card-title">{{ $shop->name }}</h5>
                            <span class="badge bg-secondary mb-2">
                                {{ ucfirst($shop->type ?? 'Boutique') }}
                            </span>

                            <p class="text-muted small">{{ $shop->products_count }} produit(s)</p>
                            <p class="card-text flex-grow-1">{{ Str::limit($shop->description ?? 'Aucune description.', 60) }}</p>

                            <a href="{{ route('client.shops.show', $shop->id) }}" class="btn btn-outline-primary btn-sm mb-2">
                                👀 Voir les produits
                            </a>

                            {{-- Bouton suivre --}}
                            @auth
                                @if(Auth::user()->role === 'client')
                                    @if(!Auth::user()->subscribedShops->contains($shop->id))
                                        <form action="{{ route('client.shops.subscribe', $shop) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100">❤️ Suivre cette boutique</button>
                                        </form>
                                    @else
                                        <span class="badge bg-success w-100">✅ Déjà suivi</span>
                                    @endif
                                @endif
                            @else
                                {{-- ✅ On passe l’ID boutique à register --}}
                                <a href="{{ route('register', ['shop_id' => $shop->id]) }}" class="btn btn-success btn-sm w-100">
                                    ➕ Suivre cette boutique
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">Aucune boutique disponible pour le moment.</div>
                </div>
            @endforelse
        </div>

        <div class="mt-3">{{ $shops->links() }}</div>
    </div>
</div>
@endsection
