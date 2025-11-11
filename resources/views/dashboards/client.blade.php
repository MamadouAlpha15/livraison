@extends('layouts.app')

@push('styles')
<style>
/* ====== General ====== */
body { background-color: #f8f9fa; }
.card { border-radius: 1rem; transition: transform .2s, box-shadow .2s; }
.card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
h1, h3 { font-weight: 700; }
.badge { font-size: .85rem; }

/* ====== Shop cards ====== */
.shop-thumb {
    height: 180px;
    object-fit: cover;
    border-radius: 1rem 1rem 0 0;
    transition: transform .3s;
}
.shop-thumb:hover { transform: scale(1.05); }
.shop-card .card-body { display: flex; flex-direction: column; align-items: center; text-align: center; }

/* ====== Buttons ====== */
.btn-sm { border-radius: 1rem; font-weight: 600; }
.btn-outline-primary:hover { background-color: #0d6efd; color: white; }

/* ====== Responsive ====== */
@media(max-width:767.98px){
    .shop-thumb { height: 150px; }
    .card-body { padding: 1rem; }
}
</style>
@endpush

@section('content')
<div class="container py-4">

    {{-- ====== Header ====== --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <h1 class="mb-3 mb-md-0">🛒 Tableau de bord Client</h1>
        <a href="{{ route('support.index') }}" class="btn btn-outline-primary btn-sm">🎧 Support client</a>
    </div>

    {{-- ====== Cartes principales ====== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100 text-center p-3 bg-white">
                <h5 class="card-title mb-2">📦 Mes commandes</h5>
                <p class="text-muted mb-3">Consultez l’historique et le suivi de vos commandes.</p>
                <a href="{{ route('client.orders.index') }}" class="btn btn-primary w-100">Voir</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 text-center p-3 bg-white">
                <h5 class="card-title mb-2">🛒 Nouvelle commande</h5>
                <p class="text-muted mb-3">Passez une nouvelle commande dans une boutique.</p>
                <a href="{{ route('client.orders.create') }}" class="btn btn-success w-100">Commander</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 text-center p-3 bg-white">
                <h5 class="card-title mb-2">✍️ Avis & Évaluations</h5>
                <p class="text-muted mb-3">Donnez un avis sur vos commandes livrées.</p>
                <a href="{{ route('client.orders.index') }}" class="btn btn-warning w-100">Laisser un avis</a>
            </div>
        </div>
    </div>

    {{-- ====== Profil & Notifications ====== --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100 text-center p-3 bg-white">
                <h5 class="card-title mb-2">👤 Mon Profil</h5>
                <p class="text-muted mb-3">Modifier mes informations personnelles.</p>
                <a href="{{ route('profile.edit') }}" class="btn btn-info w-100">Modifier</a>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100 text-center p-3 bg-white">
                <h5 class="card-title mb-2">🔔 Notifications</h5>
                <p class="text-muted mb-3">Consultez vos dernières notifications.</p>
                <a href="{{ route('notifications.readAll') }}" class="btn btn-danger w-100">Voir tout</a>
            </div>
        </div>
    </div>

    {{-- ====== Boutiques disponibles ====== --}}
    <div class="mb-5">
        <h3 class="mb-3">🏪 Boutiques disponibles</h3>
        <div class="row g-4">
            @forelse($shops as $shop)
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card shop-card h-100 shadow-sm">
                        @if($shop->image)
                            <img src="{{ asset('storage/'.$shop->image) }}" alt="{{ $shop->name }}" class="shop-thumb w-100 mb-2">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $shop->name }}</h5>
                            <span class="badge bg-secondary mb-2">{{ ucfirst($shop->type ?? 'Boutique') }}</span>
                            <p class="text-muted small mb-2">{{ $shop->products_count }} produit(s)</p>
                            <p class="text-muted flex-grow-1">{{ Str::limit($shop->description ?? 'Aucune description.', 80) }}</p>
                            <a href="{{ route('client.shops.show', $shop->id) }}" class="btn btn-outline-primary btn-sm mb-2 w-100">👀 Voir les produits</a>

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
                                <a href="{{ route('register', ['shop_id' => $shop->id]) }}" class="btn btn-success btn-sm w-100">➕ Suivre cette boutique</a>
                            @endauth
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">Aucune boutique disponible pour le moment.</div>
                </div>
            @endforelse
        </div>
        <div class="mt-3">{{ $shops->links() }}</div>
    </div>
</div>
@endsection
