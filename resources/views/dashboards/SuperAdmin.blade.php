@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">⚙️ Tableau de bord SuperAdmin</h1>

   

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

       
   
</div>
@endsection
