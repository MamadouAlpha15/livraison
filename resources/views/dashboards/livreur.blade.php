@extends('layouts.app')

@section('content')
<div class="p-5 bg-light rounded-3 shadow-sm">
    <h1 class="display-6 fw-bold">🚚 Tableau de bord - Livreur</h1>
    <p class="lead">Bonjour {{ Auth::user()->name }} 👋</p>
    <hr>

    {{-- ✅ Statut de disponibilité --}}
    <div class="card border-info mb-4 text-center">
        <div class="card-body">
            <h5 class="card-title mb-3">🟢 Statut de disponibilité</h5>

            @if(Auth::user()->is_available)
                <span class="badge bg-success fs-5">En ligne</span>
            @else
                <span class="badge bg-danger fs-5">Hors ligne</span>
            @endif

            <form action="{{ route('livreur.availability.toggle') }}" method="POST" class="mt-3">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-lg {{ Auth::user()->is_available ? 'btn-danger' : 'btn-success' }}">
                    {{ Auth::user()->is_available ? 'Se déconnecter (Hors ligne)' : 'Se connecter (En ligne)' }}
                </button>
            </form>
        </div>
    </div>

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
        <a href="{{ route('livreur.commissions.index') }}" class="btn btn-outline-success btn-sm">
  💸 Mes commissions
</a>

    </div>
</div>
@endsection
