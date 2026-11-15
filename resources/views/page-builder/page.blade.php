{{-- Page construite : les sections visibles dans l'ordre. Données : $sections (App\PageBuilder\Renderer::sections), $ctx --}}
@extends('page-builder.layout')

@section('main')
    @foreach ($sections as $s)
        @include('page-builder._section', ['s' => $s])
    @endforeach
@endsection
