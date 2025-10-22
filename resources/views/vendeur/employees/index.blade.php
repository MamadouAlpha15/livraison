@extends('layouts.app')

@section('content')
<div class="container">
    <h2>👥 Employés de ma boutique</h2>
    <a href="{{ route('boutique.employees.create') }}" class="btn btn-success mb-3">➕ Ajouter un employé</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
                <tr>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ ucfirst($employee->role_in_shop) }}</td>
                     <!-- Bouton Supprimer -->
                            <form action="{{ route('boutique.employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet employé ?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑 Supprimer</button>
                            </form>
                        </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $employees->links() }}
</div>
@endsection
