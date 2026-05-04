@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>🏪 Gestion des boutiques</h2>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-striped">
    <thead class="table-primary">
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Vendeur</th>
            <th>Téléphone</th>
            <th>Adresse</th>
            <th>Statut</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($shops as $shop)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $shop->name }}</td>
            <td>{{ $shop->owner?->name ?? 'Utilisateur supprimé' }} ({{ $shop->owner?->email ?? 'N/A' }})</td>
            <td>{{ $shop->phone }}</td>
            <td>{{ $shop->address }}</td>
            <td>
                @if($shop->is_approved)
                    <span class="badge bg-success">✅ Approuvée</span>
                @else
                    <span class="badge bg-warning">⏳ En attente</span>
                @endif
            </td>
            <td>
                <form action="{{ route('admin.shops.update', $shop) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if($shop->is_approved)
                        <button type="submit" class="btn btn-sm btn-danger">❌ Désactiver</button>
                    @else
                        <button type="submit" class="btn btn-sm btn-success">✅ Approuver</button>
                    @endif
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">Aucune boutique trouvée.</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $shops->links() }}
@endsection
