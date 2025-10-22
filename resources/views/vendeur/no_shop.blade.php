@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-info">
        👋 Bienvenue {{ Auth::user()->name }} !  
        Vous êtes vendeur, mais vous n’avez pas encore créé votre boutique.
    </div>

    <a href="{{ route('shop.create') }}" class="btn btn-primary">
        ➕ Créer ma boutique
    </a>
</div>
@endsection
