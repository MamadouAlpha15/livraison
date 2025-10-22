@extends('layouts.app')

@section('content')
<div class="container">
    <h2>📊 Statistiques globales</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4>{{ $totalOrders }}</h4>
                    <p>Total Commandes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4>{{ $deliveredOrders }}</h4>
                    <p>Commandes Livrées</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4>{{ number_format($totalRevenue, 0, ',', ' ') }} GNF</h4>
                    <p>Revenus Totaux</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4>{{ $totalShops }}</h4>
                    <p>Boutiques</p>
                </div>
            </div>
        </div>
    </div>

    <h4>🏆 Top 5 Vendeurs</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Vendeur</th>
                <th>Email</th>
                <th>Revenus (GNF)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topVendeurs as $vendeur)
                <tr>
                    <td>{{ $vendeur->name }}</td>
                    <td>{{ $vendeur->email }}</td>
                    <td>{{ number_format($vendeur->revenue, 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Aucun vendeur trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>


   <h3>🏆 Top 5 Livreurs</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Livraisons effectuées</th>
        </tr>
    </thead>
    <tbody>
        @forelse($topLivreurs as $livreur)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $livreur->name }}</td>
                <td>{{ $livreur->email }}</td>
                <td>{{ $livreur->deliveries }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">Aucun livreur n’a encore livré de commande.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection
