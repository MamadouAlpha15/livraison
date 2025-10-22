@extends('layouts.app')

@section('content')
<div class="container py-4">

    <!-- ================== TITRE ET MESSAGE ================== -->
    <div class="text-center mb-5">
        <h1 class="fw-bold text-uppercase">👑 Tableau de bord Admin</h1>
        <p class="text-muted mb-1">
            Bienvenue <b>{{ Auth::user()->name }}</b>, vous êtes 
            <span class="badge bg-danger">ADMIN</span> de cette boutique.
        </p>
    </div>

    <!-- ================== SI AUCUNE BOUTIQUE ================== -->
    @if(!$shop)
        <div class="alert alert-info text-center shadow-sm mb-4">
            Vous n’avez pas encore de boutique. Créez-la pour commencer. 👇
        </div>
        <div class="text-center mb-5">
            <a href="{{ route('boutique.shops.create') }}" class="btn btn-primary btn-lg shadow-sm">
                ➕ Créer ma boutique
            </a>
        </div>
    @endif

    <!-- ================== SECTION GESTION ================== -->
    <div class="row g-4">
        <!-- Ma Boutique -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">🏬</div>
                    <h5 class="card-title fw-bold">Ma Boutique</h5>
                    <p class="text-muted small">Créer / modifier les informations.</p>
                    <a href="{{ route('boutique.shops.index') }}" class="btn btn-primary w-100">Gérer</a>
                </div>
            </div>
        </div>

        <!-- Produits -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">📦</div>
                    <h5 class="card-title fw-bold">Produits</h5>
                    <p class="text-muted small">Ajouter, modifier ou supprimer.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-success w-100">Gérer</a>
                </div>
            </div>
        </div>

        <!-- Commandes (Voir toutes les commandes) -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">🛒</div>
                    <h5 class="card-title fw-bold">Commandes</h5>
                    <p class="text-muted small">Suivre et gérer les commandes.</p>
                    <a href="{{ route('orders.index') }}" class="btn btn-warning w-100">Voir les commandes</a>
                </div>
            </div>
        </div>

        <!-- ✅ Nouveau : Assigner les commandes aux livreurs -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-success">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">🚚</div>
                    <h5 class="card-title fw-bold text-success">Assignation Commandes</h5>
                    <p class="text-muted small">Assigner les commandes aux livreurs disponibles.</p>
                    <a href="{{ route('employe.orders.index') }}" class="btn btn-success w-100">
                        ➕ Assigner une commande
                    </a>
                </div>
            </div>
        </div>

        <!-- Avis clients -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">⭐</div>
                    <h5 class="card-title fw-bold">Avis Clients</h5>
                    <p class="text-muted small">Consulter les retours clients.</p>
                    <a href="{{ route('reviews.index') }}" class="btn btn-info w-100">Voir</a>
                </div>
            </div>
        </div>

        <!-- Paiements -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">💵</div>
                    <h5 class="card-title fw-bold">Paiements</h5>
                    <p class="text-muted small">Suivre vos paiements et revenus.</p>
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-success w-100">Voir</a>
                </div>
            </div>
        </div>

        <!-- Gestion des rôles -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 border-danger">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">⚙️</div>
                    <h5 class="card-title fw-bold text-danger">Gestion des rôles</h5>
                    <p class="text-muted small">Créer vendeurs et livreurs.</p>
                    <a href="{{ route('boutique.employees.index') }}" class="btn btn-danger w-100">
                        Gérer les rôles
                    </a>
                </div>
            </div>
        </div>

        <!-- Rapports -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">📑</div>
                    <h5 class="card-title fw-bold">Rapports</h5>
                    <p class="text-muted small">Consulter les rapports mensuels.</p>
                    <a href="{{ route('boutique.reports.index') }}" class="btn btn-info w-100">Voir</a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="fs-3 mb-2">📊</div>
                    <h5 class="card-title fw-bold">Statistiques</h5>
                    <p class="text-muted small">Suivre les ventes et performances.</p>
                    <a href="{{ route('boutique.stats.index') }}" class="btn btn-warning w-100">Voir</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
