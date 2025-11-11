@extends('layouts.app')

@section('content')
@if($shop)
    <div class="card">
        <div class="card-header">🏪 Ma boutique</div>
        <div class="card-body">
            <h5>{{ $shop->name }}</h5>
            {{-- Image de la boutique --}}
            @if($shop->image)
                <img src="{{ asset('storage/' . $shop->image) }}" alt="Image de la boutique" class="img-fluid mb-3" style="max-height: 200px;">
            @endif
            <p><b>Type :</b> {{ $shop->type }}</p>
            <p><b>Adresse :</b> {{ $shop->address }}</p>
            <p><b>Email :</b> {{ $shop->email }}</p>
            <p><b>Téléphone :</b> {{ $shop->phone }}</p>
            <p><b>pourcentage Livreur :</b> {{ $shop->commission_rate }}</p>
            <p><b>Description :</b> {{ $shop->description }}</p>
            <p><b>Status :</b> 
                @if($shop->is_approved)
                    ✅ Approuvée
                @else
                    ⏳ En attente de validation
                @endif
            </p>
        </div>
    </div>
@else
    <a href="{{ route('shop.create') }}" class="btn btn-success">Créer ma boutique</a>
@endif
<br>
@if($shop)
    <a href="{{ route('shop.edit', $shop->id) }}" class="btn btn-info">Modifier</a>
@endif
@endsection
