{{--
    En-tête des pages Campagnes de l'équipe Shopio (superadmin, staff, marketing).
    Le rôle marketing n'a accès qu'à ces pages : pas de lien vers le reste du panneau admin.
    Variables : $active ('campaigns' | 'packages'), $back (optionnel : [url, libellé])
--}}
@php $adminUser = auth()->user(); @endphp
<header class="ac-bar">
    <div class="ac-bar-in">
        <a href="{{ route('admin.campaigns.index') }}" class="ac-brand" aria-label="Campagnes Shopio">
            <span class="ac-logo">{!! \App\Support\IconLibrary::svg('megaphone', '', 18) !!}</span>
            <span><strong>Shopio</strong> <span class="ac-brand-sub">Campagnes</span></span>
        </a>
        <nav class="ac-nav" aria-label="Campagnes">
            <a href="{{ route('admin.campaigns.index') }}" @if(($active ?? '') === 'campaigns') aria-current="page" @endif>Campagnes</a>
            <a href="{{ route('admin.campaigns.packages') }}" @if(($active ?? '') === 'packages') aria-current="page" @endif>Formules</a>
            @if(in_array($adminUser?->role, ['superadmin', 'staff'], true))
            <a href="{{ route('admin.dashboard') }}">Panneau admin</a>
            @endif
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="ac-out">
            @csrf
            @include('admin._partials.dark-toggle', ['class' => 'ac-dark'])
            <span class="ac-me">{{ $adminUser?->name }}</span>
            <button type="submit">Déconnexion</button>
        </form>
    </div>
</header>
@if(isset($back))
<a href="{{ $back[0] }}" class="cp-back" style="margin:0">{!! \App\Support\IconLibrary::svg('arrow-left', '', 15) !!} {{ $back[1] }}</a>
@endif
