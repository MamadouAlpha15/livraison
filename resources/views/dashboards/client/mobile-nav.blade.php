<nav class="mob-bottom-nav" aria-label="Navigation principale">
    <div class="mob-bottom-nav-inner">
        <a href="{{ route('client.dashboard') }}" class="mob-nav-item active">
            <div class="mob-nav-item-wrap">
                {!! $si('home', 21) !!}
                <span>Accueil</span>
            </div>
        </a>
        <a href="#catalogue" class="mob-nav-item" onclick="document.querySelectorAll('.mob-nav-item').forEach(i=>i.classList.remove('active'));this.classList.add('active')">
            <div class="mob-nav-item-wrap">
                {!! $si('bag', 21) !!}
                <span>Produits</span>
            </div>
        </a>
        <a href="{{ route('client.orders.index') }}" class="mob-nav-item">
            <div class="mob-nav-item-wrap">
                {!! $si('package', 21) !!}
                <span>Commandes</span>
            </div>
        </a>
        <button class="mob-nav-item" onclick="openProfileModal()" title="Profil">
            <div class="mob-nav-item-wrap">
                {!! $si('user', 21) !!}
                <span>Profil</span>
            </div>
        </button>
    </div>
</nav>
