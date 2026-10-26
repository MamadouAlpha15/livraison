{{--
    En-tête des pages Affiliation de l'équipe Shopio (superadmin, staff), même barre que les pages Campagnes.
    Variable : $active ('index' | 'referrals' | 'commissions' | 'payouts' | 'settings')
--}}
@php
    $adminUser = auth()->user();
    $afFlagged = \App\Models\Referral::where('flagged', true)->where('status', '!=', \App\Enums\ReferralStatus::Cancelled)->count();
    $afPending = \App\Models\Affiliate::where('status', \App\Enums\AffiliateStatus::Pending)->count();
    $afPayouts =\App\Models\Payout::where('status', \App\Enums\PayoutStatus::Requested)->count();
    $afNav = [
        'index'       => ['Vue d\'ensemble', route('admin.affiliation.index'), $afPending],
        'referrals'   => ['Filleuls', route('admin.affiliation.referrals'), $afFlagged],
        'commissions' => ['Commissions', route('admin.affiliation.commissions'), 0],
        'payouts'     => ['Retraits', route('admin.affiliation.payouts'), $afPayouts],
        'settings'    => ['Réglages', route('admin.affiliation.settings'), 0],
    ];
@endphp
<header class="ac-bar">
    <div class="ac-bar-in">
        <a href="{{ route('admin.affiliation.index') }}" class="ac-brand" aria-label="Affiliation Shopio">
            <span class="ac-logo">{!! \App\Support\IconLibrary::svg('gift', '', 18) !!}</span>
            <span><strong>Shopio</strong> <span class="ac-brand-sub">Affiliation</span></span>
        </a>
        <nav class="ac-nav" aria-label="Affiliation">
            @foreach($afNav as $key => [$label, $href, $badge])
            <a href="{{ $href }}" @if(($active ?? '') === $key) aria-current="page" @endif>{{ $label }}@if($badge > 0) <span class="af-badge">{{ $badge }}</span>@endif</a>
            @endforeach
            <a href="{{ route('admin.dashboard') }}">Panneau admin</a>
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="ac-out">
            @csrf
            @include('admin._partials.dark-toggle', ['class' => 'ac-dark'])
            <span class="ac-me">{{ $adminUser?->name }}</span>
            <button type="submit">Déconnexion</button>
        </form>
    </div>
</header>
