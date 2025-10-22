@extends('layouts.app')

@section('content')
<div class="container">
    <h2>📊 Tableau de bord - Statistiques</h2>

    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <h5 class="card-title">📦 Commandes totales</h5>
                    <p class="fs-4 fw-bold">{{ $totalOrders }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h5 class="card-title">💵 Revenus totaux</h5>
                    <p class="fs-4 fw-bold">{{ number_format($totalRevenue, 0, ',', ' ') }} GNF</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <h5 class="card-title">⏳ En attente</h5>
                    <p class="fs-4 fw-bold">{{ $pendingOrders }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-info">
                <div class="card-body">
                    <h5 class="card-title">🚚 En cours de livraison</h5>
                    <p class="fs-4 fw-bold">{{ $deliveringOrders }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h5 class="card-title">✅ Livrées</h5>
                    <p class="fs-4 fw-bold">{{ $deliveredOrders }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-dark">
                <div class="card-body">
                    <h5 class="card-title">🏪 Vendeurs actifs</h5>
                    <p class="fs-4 fw-bold">{{ $vendors }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-dark">
                <div class="card-body">
                    <h5 class="card-title">🚚 Livreurs</h5>
                    <p class="fs-4 fw-bold">{{ $livreurs }}</p>
                </div>
            </div>
        </div>
    </div>

   
</div>
@endsection
