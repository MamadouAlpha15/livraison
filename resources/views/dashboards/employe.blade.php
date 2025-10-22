@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">📊 Dashboard Employé</h2>
    <p class="mb-4">Bienvenue <strong>{{ Auth::user()->name }}</strong> (Employé)</p>

    <div class="row g-3">
        <!-- Commandes -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('employe.orders.index') }}" class="btn btn-primary w-100 shadow-sm py-3 d-flex align-items-center justify-content-center gap-2">
                📦 <span>Commandes</span>
            </a>
        </div>

        <!-- Paiements -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('employe.payments.index') }}" class="btn btn-success w-100 shadow-sm py-3 d-flex align-items-center justify-content-center gap-2">
                💵 <span>Paiements</span>
            </a>
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
