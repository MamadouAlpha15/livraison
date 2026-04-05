{{-- 
=====================================================
PAGE WELCOME.BLADE.PHP STYLE SELLIO (VERSION COMPLETE)
=====================================================

INSTRUCTIONS :
1. Copie TOUT ce fichier
2. Remplace ton fichier resources/views/welcome.blade.php
3. Ajoute ton image dans : public/images/dashboard.png

--}}

@extends('layouts.app')

@push('styles')
<style>
body {
    background: #f8fafc;
}

.hero {
    background: linear-gradient(135deg,#0d6efd,#6610f2);
    color: white;
    border-radius: 20px;
    padding: 70px 30px;
}

.hero h1 {
    font-weight: 800;
}

.hero p {
    opacity: .9;
}

.btn-premium {
    border-radius: 50px;
    padding: 10px 22px;
    font-weight: 600;
}

/* IMAGE DASHBOARD STYLE SELLIO */
.dashboard-preview {
    margin-top: 40px;
    display: flex;
    justify-content: center;
}

.dashboard-preview img {
    width: 100%;
    max-width: 900px;
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    transition: transform 0.3s ease;
}

.dashboard-preview img:hover {
    transform: scale(1.02);
}

.feature {
    padding: 20px;
}
</style>
@endpush

@section('content')
<div class="container py-4">

    {{-- ================= HERO ================= --}}
    <div class="hero text-center mb-5">
        <h1 class="display-5">Gérez votre boutique en toute simplicité</h1>
        <p class="lead">Suivez vos ventes, commandes et produits depuis un dashboard moderne 🚀</p>

        @auth
            @php
                $role = Auth::user()->role;
                $map = [
                    'superadmin' => 'admin.dashboard',
                    'admin' => 'boutique.dashboard',
                    'vendeur' => 'vendeur.dashboard',
                    'client' => 'client.dashboard',
                    'company' => 'company.dashboard',
                    'livreur' => 'livreur.dashboard',
                ];
            @endphp

            @if(isset($map[$role]))
                <a href="{{ route($map[$role]) }}" class="btn btn-light btn-premium me-2">Dashboard</a>
            @endif

            @if($role === 'vendeur')
                <a href="{{ route('shop.create') }}" class="btn btn-dark btn-premium">Créer boutique</a>
            @endif

        @else
            <a href="{{ route('register') }}" class="btn btn-light btn-premium me-2">S'inscrire</a>
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-premium">Connexion</a>
        @endauth

        {{-- IMAGE DASHBOARD (REMPLACE SELLIO) --}}
        <div class="dashboard-preview">
            <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard preview">
        </div>

    </div>

    {{-- ================= FEATURES ================= --}}
    <div class="row text-center mt-5">

        <div class="col-md-4 feature">
            <h5>⚡ Rapide</h5>
            <p class="text-muted">Commandes en quelques clics</p>
        </div>

        <div class="col-md-4 feature">
            <h5>🔒 Sécurisé</h5>
            <p class="text-muted">Paiements fiables</p>
        </div>

        <div class="col-md-4 feature">
            <h5>🚚 Livraison</h5>
            <p class="text-muted">Gestion des livreurs</p>
        </div>

    </div>
    {{-- ================= COMPANY CTA ================= --}}
    <div class="company-box mt-5 d-flex justify-content-between align-items-center">
        <div>
            <h4>Entreprise de livraison ?</h4>
            <p class="text-muted">Rejoignez la plateforme</p>
        </div>

        <a href="{{ route('register',['role'=>'company']) }}" class="btn btn-primary btn-premium">S'inscrire</a>
    </div>

</div>
@endsection
