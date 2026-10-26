<nav class="nav">
    <a href="{{ route('client.dashboard') }}" class="nav-logo">
         <img src="{{ asset('images/shopio-logo-192.png') }}" alt="Shopio" style="height:60px;width:auto;object-fit:contain;border-radius:8px">
       
    </a>

    <div class="nav-links">
        <a href="{{ route('client.dashboard') }}" class="nav-link active">{!! $si('home',17) !!} Accueil</a>
        <a href="#catalogue" class="nav-link">{!! $si('bag',17) !!} Produits</a>
        <a href="#categories" class="nav-link">{!! $si('grid',17) !!} Catégories</a>
    </div>

    <div style="flex:1;max-width:420px;display:flex;flex-direction:column;gap:0">
        {{-- Recherche PRODUITS (rechargement, comme l'accueil — le serveur filtre déjà
             $products via ?s=..., voir DashboardController@index) EN PLUS du filtrage
             instantané des boutiques déjà affichées ci-dessous (doSearch(), inchangé). --}}
        <form method="GET" action="{{ route('client.dashboard') }}#catalogue" class="nav-search" style="max-width:100%">
            <input type="text" id="globalSearch" name="s" value="{{ request('s') }}" placeholder="Que recherchez-vous ?" autocomplete="off">
            <button class="nav-search-btn" type="submit">{!! $si('search',16) !!}</button>
        </form>
        <div id="searchLiveInfo"></div>
    </div>

    <div class="nav-actions">
        {{-- ══ CLOCHE NOTIFICATIONS ══ --}}
        <div class="cn-backdrop" id="cnBackdrop"></div>
        <div class="cn-bell-wrap" id="cnBellWrap">
            <button class="cn-bell-btn" id="cnBellBtn" onclick="cnToggle()" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span class="cn-bell-badge" id="cnBadge"></span>
            </button>
            <div class="cn-dropdown" id="cnDropdown">
                <div class="cn-drop-hd">
                    <span class="cn-drop-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        Notifications
                    </span>
                    <span class="cn-drop-total" id="cnTotal">0</span>
                </div>
                <div class="cn-drop-list" id="cnList">
                    <div class="cn-drop-empty">Aucune notification</div>
                </div>
                <div class="cn-drop-ft">
                    <a href="{{ route('client.orders.index') }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        Voir mes commandes
                    </a>
                </div>
            </div>
        </div>

        {{-- Bouton panier --}}
        <a href="{{ route('client.cart.index') }}" class="nav-cart-btn" id="navCartBtn" title="Mon panier">
            {!! $si('cart',20) !!}
            <span class="nav-cart-badge {{ ($cartCount ?? 0) > 0 ? 'show' : '' }}" id="navCartBadge">{{ ($cartCount ?? 0) > 0 ? $cartCount : '' }}</span>
        </a>

        {{-- Bouton favoris --}}
        @php $favProdCount = count($favoriteProductIds ?? []); @endphp
        <a href="{{ route('client.wishlist.index') }}" class="nav-fav-btn" id="navFavBtn" title="Mes favoris">
            {!! $si('heart',20) !!}
            <span class="nav-fav-badge {{ $favProdCount > 0 ? 'show' : '' }}" id="navFavBadge">{{ $favProdCount > 0 ? $favProdCount : '' }}</span>
        </a>

        <a href="{{ route('client.messages.hub') }}" class="nav-msg-btn" title="Mes messages" style="text-decoration:none">
            {!! $si('chat',20) !!}
            <span class="nav-msg-badge {{ $myUnread > 0 ? 'show' : '' }}" id="navMsgBadge">
                {{ $myUnread > 0 ? $myUnread : '' }}
            </span>
        </a>

        <a href="{{ route('client.orders.index') }}" class="nav-orders-btn">
            {!! $si('package',16) !!} <span>Mes commandes</span>
        </a>
        <div class="nav-av-wrap">
            <div style="position:relative;cursor:pointer" onclick="toggleAvatarMenu()">
                <div class="nav-av" id="navAvatar">{{ $initials }}</div>
                @if($countryFlag)
                <span style="position:absolute;bottom:-4px;right:-6px;font-size:14px;line-height:1;background:var(--surface);border-radius:50%;padding:1px;box-shadow:0 0 0 1.5px var(--border)">{{ $countryFlag }}</span>
                @endif
            </div>
            <div class="nav-av-menu" id="avatarMenu">
                <div style="padding:12px 14px 10px;border-bottom:1px solid var(--border);margin-bottom:4px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--orange),var(--orange-dk));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0">{{ $initials }}</div>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $user->name }}</div>
                            <div style="font-size:11px;color:var(--muted)">{{ $user->email }}</div>
                        </div>
                    </div>
                    @if($countryFlag)
                    <div style="display:inline-flex;align-items:center;gap:5px;background:var(--grey);border:1px solid var(--border);border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;color:var(--text-2);margin-top:4px">
                        {{ $countryFlag }} {{ $countryName }}
                    </div>
                    @endif
                </div>
                <a href="#" onclick="openProfileModal();return false;">{!! $si('user',15) !!} Modifier mon profil</a>
                <a href="{{ route('client.orders.index') }}">{!! $si('package',15) !!} Mes commandes</a>
                <a href="{{ route('client.loyalty.index') }}" style="display:flex;align-items:center;justify-content:space-between;gap:8px">
                    <span style="display:flex;align-items:center;gap:8px">{!! \App\Support\IconLibrary::svg('gift', '', 16) !!} Mes points &amp; Parrainage</span>
                    <span style="background:var(--orange,#1668D8);color:#fff;font-size:10.5px;font-weight:800;padding:1px 7px;border-radius:20px">{{ number_format(auth()->user()->loyalty_points ?? 0, 0, ',', ' ') }}</span>
                </a>
                <a href="{{ route('client.wishlist.index') }}">{!! \App\Support\IconLibrary::svg('heart', '', 16) !!} Ma liste de souhaits</a>
                <a href="{{ route('client.addresses.index') }}">{!! \App\Support\IconLibrary::svg('map-pin', '', 16) !!} Mes adresses</a>
                <div class="sep"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout">{!! $si('logout',15) !!} Se déconnecter</button>
                </form>
            </div>
        </div>
    </div>
</nav>
