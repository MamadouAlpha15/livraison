@extends('layouts.app')

@section('content')
<div class="p-5 bg-light rounded-3 shadow-sm">
    <h1 class="display-6 fw-bold">🚚 Tableau de bord - Livreur</h1>
    <p class="lead">Bonjour {{ Auth::user()->name }} 👋</p>
    <hr>

    <div class="row">
        <!-- 📦 Commandes assignées -->
        <div class="col-md-4">
            <div class="card border-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">📦 Commandes assignées</h5>
                    <p class="card-text">Voir toutes les commandes que tu dois livrer.</p>
                    <a href="{{ route('livreur.orders.index') }}" class="btn btn-primary btn-sm">
                        Voir mes livraisons
                    </a>
                </div>
            </div>
        </div>

        <!-- 🚚 Commandes en cours -->
        <div class="col-md-4">
            <div class="card border-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">🚚 Livraisons en cours</h5>
                    <p class="card-text">Commandes que tu as commencées.</p>
                    <a href="{{ route('livreur.orders.index', ['status' => 'delivering']) }}" 
                       class="btn btn-warning btn-sm">
                        Voir en cours
                    </a>
                </div>
            </div>
        </div>

        <!-- ✅ Livraisons terminées -->
        <div class="col-md-4">
            <div class="card border-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">✅ Livraisons terminées</h5>
                    <p class="card-text">Historique de toutes tes livraisons.</p>
                    <a href="{{ route('livreur.orders.index', ['status' => 'delivered']) }}" 
                       class="btn btn-success btn-sm">
                        Voir l’historique
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
