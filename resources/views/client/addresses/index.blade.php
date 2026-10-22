{{--
    resources/views/client/addresses/index.blade.php
    Route : GET /client/adresses → Client\AddressController@index
--}}
@extends('layouts.app')
@section('title', 'Mes adresses')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
.ad-page { --ink: #16162A; --ink-2: #45455E; --ink-3: #62627C; --line: #E3E8F2; --bg: #F6F9FF; --blue: #1668D8; --blue-dk: #1260C9; --blue-lt: #EAF2FF;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; color: var(--ink); background: var(--bg); min-height: 100vh; padding-bottom: 56px; }
.ad-head { background: radial-gradient(110% 170% at 100% 0%, rgba(31,124,246,.45), transparent 60%), linear-gradient(135deg, #0B1E3D, #132B52); color: #fff; padding: 22px max(16px, env(safe-area-inset-right, 0px)) 26px max(16px, env(safe-area-inset-left, 0px)); }
.ad-head-in { max-width: 760px; margin: 0 auto; display: grid; gap: 10px; }
.ad-back { display: inline-flex; align-items: center; gap: 6px; width: fit-content; min-height: 36px; padding: 0 14px; border-radius: 999px; background: rgba(255,255,255,.1); color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; }
.ad-back:hover { background: rgba(255,255,255,.18); color: #fff; }
.ad-head h1 { margin: 0; font-size: clamp(22px, 4vw, 28px); font-weight: 800; letter-spacing: -.02em; }
.ad-head p { margin: 0; color: #C9D6EA; font-size: 14px; max-width: 60ch; }
.ad-main { max-width: 760px; margin: 22px auto 0; padding: 0 16px; display: grid; gap: 18px; }
.ad-flash { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border-radius: 14px; font-size: 13.5px; font-weight: 600; }
.ad-flash.ok { background: #ECFDF5; color: #065F46; }
.ad-flash.warn { background: #FFFBEB; color: #92400E; }
.ad-list { display: grid; gap: 10px; }
.ad-item { display: flex; align-items: center; gap: 14px; padding: 16px 18px; background: #fff; border-radius: 20px; box-shadow: 0 0 0 1px var(--line); }
.ad-item.is-default { box-shadow: 0 0 0 2px #CFE1FF; }
.ad-ico { width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--blue-lt); color: var(--blue); }
.ad-txt { flex: 1; min-width: 0; }
.ad-label { font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ad-badge { font-size: 11px; font-weight: 800; padding: 3px 9px; border-radius: 999px; background: var(--blue-lt); color: var(--blue-dk); }
.ad-addr { font-size: 13.5px; color: var(--ink-2); margin-top: 2px; overflow-wrap: anywhere; }
.ad-actions { display: flex; gap: 6px; flex-shrink: 0; }
.ad-btn { min-height: 38px; padding: 0 14px; border-radius: 999px; border: none; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; background: rgba(22,22,42,.07); color: var(--ink); }
.ad-btn:hover { background: rgba(22,22,42,.12); }
.ad-btn.danger { color: #B91C1C; background: #FEF2F2; }
.ad-btn.danger:hover { background: #FEE2E2; }
.ad-empty { padding: 28px 20px; text-align: center; background: #fff; border-radius: 20px; box-shadow: 0 0 0 1px var(--line); color: var(--ink-2); font-size: 14px; }
.ad-form { background: #fff; border-radius: 22px; box-shadow: 0 0 0 1px var(--line); padding: 20px; display: grid; gap: 14px; }
.ad-form h2 { margin: 0; font-size: 17px; font-weight: 800; }
.ad-presets { display: flex; gap: 6px; flex-wrap: wrap; }
.ad-preset { min-height: 34px; padding: 0 14px; border-radius: 999px; border: none; background: rgba(22,22,42,.07); color: var(--ink-2); font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
.ad-preset.is-active { background: var(--blue); color: #fff; }
.ad-field { display: grid; gap: 6px; }
.ad-field label { font-size: 13.5px; font-weight: 700; }
.ad-input { width: 100%; min-height: 48px; padding: 0 14px; border: 1.5px solid var(--line); border-radius: 14px; font: inherit; font-size: 15px; color: var(--ink); background: #fff; }
.ad-input:focus { outline: none; border-color: #1F7CF6; box-shadow: 0 0 0 4px rgba(31,124,246,.12); }
.ad-err { font-size: 12.5px; color: #B91C1C; font-weight: 600; }
.ad-submit { justify-self: start; min-height: 46px; padding: 0 24px; border-radius: 999px; border: none; background: var(--blue); color: #fff; font-family: inherit; font-size: 14.5px; font-weight: 800; cursor: pointer; }
.ad-submit:hover { background: var(--blue-dk); }
.ad-note { font-size: 12.5px; color: var(--ink-3); margin: 0; }
@media (max-width: 560px) {
    .ad-item { flex-wrap: wrap; }
    .ad-actions { width: 100%; }
    .ad-actions form, .ad-actions .ad-btn { flex: 1; }
    .ad-actions .ad-btn { width: 100%; }
}
</style>
@endpush

@section('content')
<div class="ad-page">
    <header class="ad-head">
        <div class="ad-head-in">
            <a href="{{ route('client.dashboard') }}" class="ad-back">{!! \App\Support\IconLibrary::svg('arrow-left', '', 15) !!} Accueil</a>
            <h1>Mes adresses</h1>
            <p>Choisissez-les en un clic au moment de commander. L'adresse principale est proposée automatiquement.</p>
        </div>
    </header>

    <main class="ad-main">
        @if(session('success'))
        <div class="ad-flash ok" role="status">{!! \App\Support\IconLibrary::svg('check', '', 16) !!} {{ session('success') }}</div>
        @endif
        @if(session('warning'))
        <div class="ad-flash warn" role="alert">{!! \App\Support\IconLibrary::svg('alert', '', 16) !!} {{ session('warning') }}</div>
        @endif

        @if($addresses->isEmpty())
        <div class="ad-empty">Aucune adresse enregistrée pour l'instant. Ajoutez-en une ci-dessous, ou cochez « Enregistrer dans mes adresses » lors de votre prochaine commande.</div>
        @else
        <div class="ad-list">
            @foreach($addresses as $addr)
            <div class="ad-item {{ $addr->is_default ? 'is-default' : '' }}">
                <span class="ad-ico">{!! \App\Support\IconLibrary::svg($addr->is_default ? 'home' : 'map-pin', '', 18) !!}</span>
                <div class="ad-txt">
                    <div class="ad-label">{{ $addr->label }} @if($addr->is_default)<span class="ad-badge">Principale</span>@endif</div>
                    <div class="ad-addr">{{ $addr->address }}</div>
                </div>
                <div class="ad-actions">
                    @unless($addr->is_default)
                    <form method="POST" action="{{ route('client.addresses.default', $addr) }}">
                        @csrf
                        <button type="submit" class="ad-btn">Principale</button>
                    </form>
                    @endunless
                    <form method="POST" action="{{ route('client.addresses.destroy', $addr) }}" onsubmit="return confirm('Supprimer « {{ addslashes($addr->label) }} » ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ad-btn danger" aria-label="Supprimer {{ $addr->label }}">Supprimer</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($addresses->count() < $max)
        <form method="POST" action="{{ route('client.addresses.store') }}" class="ad-form">
            @csrf
            <h2>Ajouter une adresse</h2>
            <div class="ad-field">
                <label for="adLabel">Nom</label>
                <div class="ad-presets" role="group" aria-label="Noms courants">
                    @foreach(['Maison', 'Bureau', 'Famille'] as $preset)
                    <button type="button" class="ad-preset" data-preset="{{ $preset }}">{{ $preset }}</button>
                    @endforeach
                </div>
                <input type="text" class="ad-input" id="adLabel" name="label" value="{{ old('label') }}" maxlength="40" placeholder="Ex. Maison" required>
                @error('label')<div class="ad-err">{{ $message }}</div>@enderror
            </div>
            <div class="ad-field">
                <label for="adAddress">Adresse</label>
                <input type="text" class="ad-input" id="adAddress" name="address" value="{{ old('address') }}" maxlength="255" placeholder="Ex. Ratoma, Kipé, près de la pharmacie" autocomplete="street-address" required>
                @error('address')<div class="ad-err">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="ad-submit">Enregistrer l'adresse</button>
            <p class="ad-note">Jusqu'à {{ $max }} adresses.</p>
        </form>
        @endif
    </main>
</div>
<script>
document.querySelectorAll('.ad-preset').forEach(btn => btn.addEventListener('click', () => {
    document.getElementById('adLabel').value = btn.dataset.preset;
    document.querySelectorAll('.ad-preset').forEach(b => b.classList.toggle('is-active', b === btn));
}));
</script>
@endsection
