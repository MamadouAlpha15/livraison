@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">⚙️ Tableau de bord SuperAdmin</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Gestion des boutiques -->
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center">
                <h5 class="card-title">🏪 Boutiques</h5>
                <p class="card-text">Approuver ou désactiver les boutiques des vendeurs.</p>
                <a href="{{ route('admin.shops.index') }}" class="btn btn-success">Accéder</a>
            </div>
        </div>
    </div>

    <!-- Demandes des entreprises de livraison -->
    <h3 class="mt-5">🚚 Entreprises en attente</h3>

    @if($pendingCompanies->count() > 0)
        <div class="row g-3">
            @foreach($pendingCompanies as $company)
                <div class="col-md-6">
                    <div class="card p-3 shadow-sm">
                        <h5>{{ $company->name }}</h5>
                        <p>{{ $company->description ?? 'Pas de description' }}</p>
                        <p class="small text-muted">Email: {{ $company->email ?? '—' }} | Téléphone: {{ $company->phone ?? '—' }}</p>

                        <form action="{{ route('admin.companies.approve', $company) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">✅ Approuver</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $pendingCompanies->links() }}
        </div>
    @else
        <p class="text-muted">Aucune demande d'entreprise en attente.</p>
    @endif

</div>
@endsection
