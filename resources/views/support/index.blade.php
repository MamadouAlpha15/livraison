{{-- resources/views/support/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🎧 Support</h3>
    <a class="btn btn-primary" href="{{ route('support.create') }}">➕ Nouveau ticket</a>
  </div>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr>
        <th>#</th><th>Sujet</th><th>Boutique</th><th>Créé par</th><th>Statut</th><th>Action</th>
      </tr></thead>
      <tbody>
      @forelse($tickets as $t)
        <tr>
          <td>{{ $t->id }}</td>
          <td>{{ $t->subject }}</td>
          <td>{{ $t->shop->name ?? '—' }}</td>
          <td>{{ $t->creator->name }}</td>
          <td>
            <span class="badge {{ $t->status=='open'?'bg-success':'bg-secondary' }}">
              {{ $t->status=='open'?'Ouvert':'Fermé' }}
            </span>
          </td>
          <td><a href="{{ route('support.show',$t) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a></td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted">Aucun ticket.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{ $tickets->links() }}
</div>
@endsection
