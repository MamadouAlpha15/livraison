@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h2 class="h4 fw-bold mb-0">👥 Employés de ma boutique</h2>
        <a href="{{ route('boutique.employees.create') }}" class="btn btn-success">➕ Ajouter un employé</a>
    </div>

    {{-- TABLE DES EMPLOYÉS --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ ucfirst($employee->role_in_shop) }}</td>
                            <td>
                                <form action="{{ route('boutique.employees.destroy', $employee) }}" method="POST" 
                                      onsubmit="return confirm('Voulez-vous vraiment supprimer cet employé ?')" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">🗑 Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucun employé disponible.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $employees->links() }}
    </div>
</div>
@endsection

@section('styles')
<style>
    .table-hover tbody tr:hover { background: rgba(13,110,253,0.05); }
    .card { border-radius: 1rem; }
    .btn-sm { font-size: 0.85rem; padding: 0.35rem 0.6rem; }
    @media (max-width: 767.98px) {
        .table-responsive { overflow-x: auto; }
        .btn { font-size: 0.9rem; }
    }
</style>
@endsection
