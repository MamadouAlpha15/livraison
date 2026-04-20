@extends('layouts.app')

@section('content')
@php $bodyClass = 'is-dashboard'; @endphp
<style>
.emp-card { max-width:520px; margin:32px auto; background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.08); overflow:hidden; }
.emp-card-head { background:linear-gradient(135deg,#131921,#232f3e); padding:20px 24px; color:#fff; display:flex; align-items:center; gap:12px; }
.emp-card-head h2 { margin:0; font-size:18px; font-weight:700; }
.emp-card-body { padding:24px; }
.emp-field { margin-bottom:18px; }
.emp-field label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
.emp-field input, .emp-field select {
    width:100%; padding:11px 14px; border:1.5px solid #d1d5db; border-radius:10px;
    font-size:14px; outline:none; font-family:inherit; box-sizing:border-box; transition:border-color .15s;
}
.emp-field input:focus, .emp-field select:focus { border-color:#f90; box-shadow:0 0 0 3px rgba(255,153,0,.15); }
.emp-submit { width:100%; padding:13px; background:linear-gradient(135deg,#f90,#e47911); color:#fff; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; font-family:inherit; }
.emp-submit:hover { opacity:.9; }
.emp-back-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; background:#131921; color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:700; text-decoration:none; transition:all .15s; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,.18); }
.emp-back-btn:hover { background:#232f3e; color:#f90; transform:translateX(-3px); }
</style>

<a href="{{ route('boutique.employees.index') }}" class="emp-back-btn">
    ← Retour à la liste
</a>

<div class="emp-card">
    <div class="emp-card-head">
        <span style="font-size:28px">✏️</span>
        <div>
            <h2>Modifier l'employé</h2>
            <div style="font-size:12px;opacity:.7">{{ $employee->email }}</div>
        </div>
    </div>
    <div class="emp-card-body">

        @if ($errors->any())
        <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:13.5px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('boutique.employees.update', $employee) }}">
            @csrf
            @method('PUT')

            <div class="emp-field">
                <label>Nom complet</label>
                <input type="text" name="name" value="{{ old('name', $employee->name) }}" required>
            </div>

            <div class="emp-field">
                <label>Téléphone</label>
                <input type="tel" name="phone" value="{{ old('phone', $employee->phone) }}" placeholder="Ex : +224 620 00 00 00">
            </div>

            <div class="emp-field">
                <label>Rôle</label>
                <select name="role_in_shop" required>
                    <option value="livreur" {{ $employee->role_in_shop === 'livreur' ? 'selected' : '' }}>🚚 Livreur</option>
                    <option value="vendeur" {{ $employee->role_in_shop === 'vendeur' ? 'selected' : '' }}>🛒 Vendeur</option>
                    <option value="employe" {{ $employee->role_in_shop === 'employe' ? 'selected' : '' }}>👤 Employé</option>
                </select>
            </div>

            <button type="submit" class="emp-submit">💾 Enregistrer les modifications</button>
        </form>
    </div>
</div>
@endsection
