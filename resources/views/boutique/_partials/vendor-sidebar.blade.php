{{--
    Menu latéral de l'espace vendeur (maquette Main.dc.html), réutilisable.
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'settings'])
    Clés de $active : dash, messages, orders, products, campaigns, referral, promos, clients, marketing, grow, team, riders, partners, finances, commissions, reports, settings, support.
    À utiliser dans une page qui inclut aussi boutique._partials.sv-theme (couleurs).
    Structure attendue : <div class="vs-wrap"> [ce menu] <main class="main vs-main"> … </main> </div>
--}}
@php
    $active   = $active ?? '';
    $vsUser   = auth()->user();
    $vsIsPro  = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
    $vsPending = $shop->orders()->whereIn('status', ['pending', 'en attente', 'en_attente'])->count();
    $vsCampaignNews = $vsUser->role === 'admin' ? \App\Support\Campaigns\CampaignInbox::count($vsUser) : 0;
    $vsParts  = preg_split('/\s+/', trim($vsUser->name));
    $vsIni    = mb_strtoupper(mb_substr($vsParts[0], 0, 1) . mb_substr($vsParts[1] ?? $vsParts[0], isset($vsParts[1]) ? 0 : 1, 1));
    $vsIco = [
        'dash'     => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
        'messages' => 'M4 5h16v11H9l-5 4zM8 9h8M8 12h5',
        'orders'   => 'M12 3 4 7v10l8 4 8-4V7zM4 7l8 4 8-4M12 11v10',
        'products' => 'M3 12 12 3h8v8l-9 9zM15.5 8.5h.01',
        'campaigns'=> 'M12 3l2.6 5.6 6.1.6-4.6 4.1 1.3 6-5.4-3.1-5.4 3.1 1.3-6L3.3 9.2l6.1-.6z',
        'referral' => 'M4 11h16v9H4zM3 7h18v4H3zM12 7v13M12 7c-1.6-3.2-5-3.4-5-1.2C7 7 9.5 7 12 7zm0 0c1.6-3.2 5-3.4 5-1.2C17 7 14.5 7 12 7z',
        'promos'   => 'M19 5 5 19M7.5 7.5h.01M16.5 16.5h.01',
        'clients'  => 'M9 11.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM3 20c.8-3.4 3.2-5.5 6-5.5s5.2 2.1 6 5.5M16 4.5a3.5 3.5 0 0 1 0 7M21 20c-.5-2.4-1.8-4.1-3.6-4.9',
        'marketing'=> 'M4 10v4h3l6 4V6l-6 4zM16 9a4 4 0 0 1 0 6M18.5 6.5a8 8 0 0 1 0 11',
        'grow'     => 'M4 19h16M6 15l4-4 3 3 6-7M15 7h4v4',
        'team'     => 'M4 7h16v12H4zM9 7V5h6v2M12 11v4M10 13h4',
        'riders'   => 'M6 19.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM18 19.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM8.5 17h7M15 7h2.5l2.5 6.5M6 14.5 9 9h5l1 5',
        'partners' => 'M4 10.5 12 4l8 6.5V20H4zM10 20v-5h4v5',
        'finances' => 'M4 6.5h16v11H4zM4 10h16M8 14.5h3',
        'commissions' => 'M4 19h16M7 16V9M12 16V5M17 16v-4',
        'reports'  => 'M8 4h8v3H8zM6 5.5H5v15h14v-15h-1M9 12h6M9 16h4',
        'settings' => 'M4 9l1.5-5h13L20 9M4 9v11h16V9M4 9c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3M10 20v-5h4v5',
        'support'  => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .8-1 1.5v.7M12 17h.01',
        'logout'   => 'M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 16l-4-4 4-4M6 12h10',
    ];
    $upgrade = route('boutique.subscription.upgrade');
    // [clé, libellé, lien, réservé au plan Pro]
    $vsNav = [
        'Boutique' => [
            ['dash', 'Tableau de bord', route('boutique.dashboard'), false],
            ['messages', 'Messages', route('boutique.messages.hub'), false],
            ['orders', 'Commandes', route('boutique.orders.index'), false],
            ['products', 'Produits', route('products.index'), false],
            // Campagnes payées : réservé au propriétaire (pas aux employés)
            ...($vsUser->role === 'admin' ? [['campaigns', 'Campagnes', route('boutique.campaigns.index'), false]] : []),
            // Parrainage (programme d'affiliation) : réservé au propriétaire
            ...($vsUser->role === 'admin' ? [['referral', 'Parrainage', route('vendeur.parrainage'), false]] : []),
            ['promos', 'Codes promo', route('boutique.promo-codes.index'), true],
            ['clients', 'Clients', route('boutique.clients.index'), false],
            ['marketing', 'Marketing', route('boutique.marketing'), false],
            ['grow', 'Faire grandir ma vitrine', route('boutique.grow'), false],
            ['team', 'Équipe', route('boutique.employees.index'), true],
        ],
        'Livraison' => [
            ['riders', 'Livreurs', route('boutique.livreurs.index'), true],
            ['partners', 'Partenaires', route('delivery.companies.index'), true],
        ],
        'Finances' => [
            ['finances', 'Paiements', route('boutique.payments.index'), false],
            ['commissions', 'Commissions', route('boutique.commissions.index'), false],
            ['reports', 'Rapports', route('boutique.reports.index'), true],
        ],
        'Réglages' => [
            ['settings', 'Ma boutique', route('shop.edit', $shop), false],
        ],
        'Aide' => [
            ['support', 'Support', route('support.index'), false],
        ],
    ];
    $vsSvg = fn ($k, $s = 20) => '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$vsIco[$k].'"/></svg>';
@endphp
<style>
/* Structure du menu vendeur — les couleurs viennent de sv-theme */
.vs-wrap { display: flex; min-height: 100vh; }
.vs-wrap .sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 264px; z-index: 40; display: flex; flex-direction: column; gap: 4px; padding: 20px 14px calc(14px + env(safe-area-inset-bottom, 0px)); overflow-y: auto; scrollbar-width: thin; scrollbar-color: #1C3560 transparent; }
.vs-wrap .vs-main { margin-left: 264px; flex: 1; min-width: 0; display: flex; flex-direction: column; }
.vs-brand { display: flex; align-items: center; gap: 12px; padding: 4px 8px 14px; border-bottom: 1px solid #1C3560; text-decoration: none; }
.vs-brand img { width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0; object-fit: cover; }
.vs-brand-name { font-weight: 800; font-size: 17px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 160px; }
.vs-brand-st { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #9FB3D3; }
.vs-brand-st i { width: 8px; height: 8px; border-radius: 999px; background: #4ADE80; }
.vs-nav { display: flex; flex-direction: column; gap: 2px; padding-top: 6px; }
.vs-wrap .sb-section { display: block; text-transform: uppercase; font-weight: 800; }
.vs-wrap .sb-item { display: flex; align-items: center; text-decoration: none; }
.vs-wrap .vs-lbl { flex: 1; min-width: 0; }
.vs-wrap .sb-item.is-locked { opacity: .55; }
.vs-lock { font-size: 11px; font-weight: 800; color: #FBBF24; }
.vs-wrap .sb-badge { background: #F59E0B; color: #16162A; }
.vs-user { display: flex; align-items: center; gap: 10px; margin-top: auto; padding: 12px; border-radius: 16px; background: #132B52; }
.vs-user-av { width: 38px; height: 38px; border-radius: 999px; background: #1F7CF6; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0; }
.vs-user-name { font-weight: 800; color: #fff; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.vs-user-role { font-size: 12px; color: #9FB3D3; }
.vs-logout { width: 40px; height: 40px; border-radius: 999px; border: 0; background: transparent; color: #FCA5A5; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.vs-logout:hover { background: rgba(220,38,38,.18); }
.vs-close, .vs-burger { display: none; }
.vs-overlay { display: none; position: fixed; inset: 0; background: rgba(11,30,61,.5); z-index: 39; border: 0; }
@media (max-width: 900px) {
    .vs-wrap .sidebar { transform: translateX(-100%); transition: transform .3s cubic-bezier(.22,1,.36,1); }
    .vs-wrap .sidebar.open { transform: none; }
    .vs-wrap .vs-main { margin-left: 0; }
    .vs-overlay.open { display: block; }
    .vs-burger { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 999px; border: 0; background: var(--surface, #fff); color: var(--text, #16162A); box-shadow: inset 0 0 0 1.5px var(--border, #E3E8F2); cursor: pointer; flex-shrink: 0; }
    .vs-close { display: inline-flex; margin-left: auto; width: 36px; height: 36px; border-radius: 999px; border: 0; background: #132B52; color: #C9D6EA; align-items: center; justify-content: center; cursor: pointer; }
}
@media (prefers-reduced-motion: reduce) { .vs-wrap .sidebar { transition: none; } }
</style>

<aside class="sidebar" id="vsSidebar" aria-label="Menu de la boutique">
    <div style="display:flex;align-items:flex-start">
        <a href="{{ route('boutique.dashboard') }}" class="vs-brand" style="flex:1;min-width:0">
            <img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="" width="44" height="44">
            <span style="min-width:0">
                <span class="vs-brand-name" style="display:block">{{ $shop->name }}</span>
                <span class="vs-brand-st"><i></i>{{ $shop->is_approved ? 'Boutique active' : 'En attente de validation' }} · {{ $vsUser->role === 'admin' ? 'Admin' : ucfirst($vsUser->role) }}</span>
            </span>
        </a>
        <button type="button" class="vs-close" id="vsClose" aria-label="Fermer le menu">&times;</button>
    </div>
    <nav class="vs-nav" aria-label="Navigation">
        @foreach($vsNav as $vsSection => $vsItems)
            <div class="sb-section">{{ $vsSection }}</div>
            @foreach($vsItems as [$vsKey, $vsLabel, $vsHref, $vsProOnly])
                @php $vsLocked = $vsProOnly && !$vsIsPro; @endphp
                <a href="{{ $vsLocked ? $upgrade : $vsHref }}" class="sb-item {{ $active === $vsKey ? 'active' : '' }} {{ $vsLocked ? 'is-locked' : '' }}"
                   @if($active === $vsKey) aria-current="page" @endif @if($vsLocked) title="Plan Pro requis" @endif>
                    <span class="ico">{!! $vsSvg($vsKey) !!}</span>
                    <span class="vs-lbl">{{ $vsLabel }}</span>
                    @if($vsLocked)<span class="vs-lock">Pro</span>
                    @elseif($vsKey === 'orders' && $vsPending > 0)<span class="sb-badge">{{ $vsPending }}</span>
                    @elseif($vsKey === 'campaigns' && $vsCampaignNews > 0)<span class="sb-badge" aria-label="{{ $vsCampaignNews }} nouveauté(s)">{{ $vsCampaignNews }}</span>@endif
                </a>
            @endforeach
        @endforeach
    </nav>
    <div class="vs-user">
        <a href="{{ route('profile.edit') }}" class="vs-user-av" aria-label="Mon profil">{{ $vsIni }}</a>
        <div style="flex:1;min-width:0">
            <div class="vs-user-name">{{ $vsUser->name }}</div>
            <div class="vs-user-role">{{ $vsUser->role === 'admin' ? 'Administrateur' : ucfirst($vsUser->role) }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="vs-logout" aria-label="Se déconnecter">{!! $vsSvg('logout') !!}</button>
        </form>
    </div>
</aside>
<button type="button" class="vs-overlay" id="vsOverlay" aria-label="Fermer le menu"></button>
<script>
(function () {
    const sb = document.getElementById('vsSidebar'), ov = document.getElementById('vsOverlay');
    const open = () => { sb.classList.add('open'); ov.classList.add('open'); };
    const close = () => { sb.classList.remove('open'); ov.classList.remove('open'); };
    document.addEventListener('click', e => { if (e.target.closest('[data-vs-open]')) open(); });
    ov.addEventListener('click', close);
    document.getElementById('vsClose').addEventListener('click', close);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
})();
</script>
