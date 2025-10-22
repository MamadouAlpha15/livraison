@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">👑 Tableau de bord ADMIN - {{ $shop->name ?? 'Ma Boutique' }}</h1>

    <p class="text-muted">
        Bienvenue <b>{{ Auth::user()->name }}</b>, vous êtes <span class="badge bg-danger">ADMIN</span> de cette boutique.
    </p>

    <div class="row">
        <!-- Gestion de la boutique -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">🏬 Ma Boutique</h5>
                    <p class="card-text">Modifier les informations de votre boutique.</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary">Gérer</a>
                </div>
            </div>
        </div>

        <!-- Produits -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">📦 Produits</h5>
                    <p class="card-text">Ajouter, modifier ou supprimer vos produits.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-success">Gérer</a>
                </div>
            </div>
        </div>

        <!-- Commandes -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">🛒 Commandes</h5>
                    <p class="card-text">Suivre et gérer les commandes de vos clients.</p>
                    <a href="{{ route('orders.index') }}" class="btn btn-warning">Voir</a>
                </div>
            </div>
        </div>

        <!-- Avis -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">⭐ Avis Clients</h5>
                    <p class="card-text">Consulter les retours et avis des clients.</p>
                    <a href="{{ route('reviews.index') }}" class="btn btn-info">Voir</a>
                </div>
            </div>
        </div>

        <!-- Paiements -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">💵 Paiements</h5>
                    <p class="card-text">Suivre vos paiements et revenus.</p>
                    <a href="{{ route('payments.index') }}" class="btn btn-success">Voir</a>
                </div>
            </div>
        </div>

        <!-- Gestion des rôles -->
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100 border-danger">
                <div class="card-body text-center">
                    <h5 class="card-title">⚙️ Gestion des rôles</h5>
                    <p class="card-text">Créer et gérer vos employés, livreurs et caissiers.</p>
                    <a href="{{ route('employees.index') }}" class="btn btn-danger">Gérer les rôles</a>
                </div>
            </div>
        </div>

         <!-- Rapports -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('employe.reports.index') }}" class="btn btn-info w-100 shadow-sm py-3 d-flex align-items-center justify-content-center gap-2">
                📑 <span>Rapports</span>
            </a>
        </div>

        <!-- Statistiques -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('employe.stats.index') }}" class="btn btn-warning w-100 shadow-sm py-3 d-flex align-items-center justify-content-center gap-2">
                📊 <span>Statistiques</span>
            </a>
        </div>
    </div>
</div>
@endsection
