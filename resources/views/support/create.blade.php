{{-- resources/views/support/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width:720px">
  <h3 class="mb-3">➕ Nouveau ticket</h3>

  <form method="POST" action="{{ route('support.store') }}">
    @csrf

    <div class="mb-3">
      <label class="form-label">Boutique (cible)</label>
      <select name="shop_id" class="form-select">
        <option value="">— Général / Administration —</option>
        @foreach($shops as $s)
          <option value="{{ $s->id }}">{{ $s->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Sujet</label>
      <input type="text" name="subject" class="form-control" required maxlength="160" value="{{ old('subject') }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Message</label>
      <textarea name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea>
    </div>

    <button class="btn btn-primary">Créer le ticket</button>
  </form>
</div>
@endsection
