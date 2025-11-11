@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-primary fw-bold">📊 Statistiques Boutique</h2>

    {{-- Cartes statistiques --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center border-0 bg-light">
                <div class="card-body">
                    <h4 class="fw-bold text-primary">{{ $totalOrders }}</h4>
                    <p class="text-muted mb-0">Total Commandes</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center border-0 bg-light">
                <div class="card-body">
                    <h4 class="fw-bold text-success">{{ $deliveredOrders }}</h4>
                    <p class="text-muted mb-0">Commandes Livrées</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center border-0 bg-light">
                <div class="card-body">
                    <h4 class="fw-bold text-warning">{{ number_format($totalRevenue,0,',',' ') }} GNF</h4>
                    <p class="text-muted mb-0">Revenus Totaux</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center border-0 bg-light">
                <div class="card-body">
                    <h4 class="fw-bold text-info">{{ $ordersToday }}</h4>
                    <p class="text-muted mb-0">Commandes Aujourd'hui</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mt-3 mt-md-0">
            <div class="card shadow-sm text-center border-0 bg-light">
                <div class="card-body">
                    <h4 class="fw-bold text-danger">{{ number_format($revenueToday,0,',',' ') }} GNF</h4>
                    <p class="text-muted mb-0">Revenus Aujourd'hui</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="row mb-4">
        <div class="col-12 col-md-6 mb-4">
            <div class="card shadow-sm p-3">
                <h5 class="fw-bold mb-3">📈 Commandes par Mois</h5>
                <canvas id="monthlyOrdersChart"></canvas>
            </div>
        </div>
    
    </div>

    {{-- Top 5 Vendeurs --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-3">🏆 Top 5 Vendeurs</h4>
        <div class="card shadow-sm p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Revenus (GNF)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topVendeurs as $vendeur)
                        <tr>
                            <td>{{ $vendeur->name }}</td>
                            <td>{{ $vendeur->email }}</td>
                            <td class="fw-bold text-success">{{ number_format($vendeur->revenue,0,',',' ') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Aucun vendeur trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top 5 Livreurs --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-3">🚚 Top 5 Livreurs</h4>
        <div class="card shadow-sm p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Livraisons</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topLivreurs as $livreur)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $livreur->name }}</td>
                            <td>{{ $livreur->email }}</td>
                            <td class="fw-bold text-info">{{ $livreur->deliveries }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Aucun livreur trouvé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.card { border-radius: 1rem; transition: transform 0.2s, box-shadow 0.2s; }
.card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
.table-hover tbody tr:hover { background: rgba(13,110,253,0.05); }
h2,h4,h5 { font-weight: 600; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique Commandes par Mois
    new Chart(document.getElementById('monthlyOrdersChart'), {
        type: 'bar',
        data: {
            labels: @json($ordersPerMonth->keys()),
            datasets: [{
                label: 'Commandes',
                data: @json($ordersPerMonth->values()),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    
</script>
@endpush
