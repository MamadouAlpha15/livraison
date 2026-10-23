<div class="hero">
    {{-- Image de fond téléphone (droite, sans recadrage) --}}
    {{-- Note moyenne réelle des boutiques : seulement à partir de 3 avis de clients livrés --}}
    @if($siteRating)
    <div class="hero-rating-card">
        <div class="hero-rating-stars" style="display:flex;gap:2px;justify-content:center">{!! str_repeat($sif('star-o',13), (int) round($siteRating['avg'])) !!}</div>
        <div class="hero-rating-val">{{ number_format($siteRating['avg'], 1, ',', ' ') }}<span style="font-size:13px;font-weight:600;color:rgba(255,255,255,.6)">/5</span></div>
        <div class="hero-rating-lbl">Note moyenne</div>
        <div class="hero-rating-reviews">{{ number_format($siteRating['count'], 0, ',', ' ') }} avis clients</div>
    </div>
    @endif

    {{-- Contenu texte par-dessus l'image de fond --}}
    <div class="hero-content">
        <div class="hero-welcome">Bonjour {{ \Illuminate\Support\Str::of(Auth::user()->name)->explode(' ')->first() }}</div>
        <h1 class="hero-title">
            Achetez en confiance. <span class="country">Payez à la livraison.</span>
        </h1>
        <p class="hero-subtitle">
            Boutiques, produits et livraison au même endroit, en {{ $countryName ?: 'Guinée' }}.
            Vous ne payez <strong>qu’à la réception.</strong>
        </p>
        <div class="hero-badges">
            <span class="hero-badge">
                <span class="hero-badge-ico">{!! $si('truck',15) !!}</span>
                <span class="hero-badge-txt">
                    <span class="hero-badge-label">Paiement à la livraison</span>
                    <span class="hero-badge-sub">En espèces, au livreur</span>
                </span>
            </span>
            <span class="hero-badge">
                <span class="hero-badge-ico">{!! $si('shield-ok',15) !!}</span>
                <span class="hero-badge-txt">
                    <span class="hero-badge-label">Boutiques vérifiées</span>
                    <span class="hero-badge-sub">Contrôlées par Shopio</span>
                </span>
            </span>
            <span class="hero-badge">
                <span class="hero-badge-ico">{!! $si('package',15) !!}</span>
                <span class="hero-badge-txt">
                    <span class="hero-badge-label">Suivi de commande</span>
                    <span class="hero-badge-sub">Jusqu’à chez vous</span>
                </span>
            </span>
            <span class="hero-badge">
                <span class="hero-badge-ico">{!! $si('message',15) !!}</span>
                <span class="hero-badge-txt">
                    <span class="hero-badge-label">Écrivez aux boutiques</span>
                    <span class="hero-badge-sub">Questions et prix, dans le chat</span>
                </span>
            </span>
        </div>
        <div class="hero-btns" style="display:flex;gap:12px;margin-top:22px;flex-wrap:wrap">
            <a href="#catalogue"
               style="display:inline-flex;align-items:center;gap:8px;padding:13px 26px;border-radius:999px;background:var(--orange);color:#fff;font-size:14px;font-weight:700;text-decoration:none;box-shadow:0 4px 16px rgba(31,124,246,.35);transition:.2s">
                {!! $si('tag',16) !!} Voir tous les produits
            </a>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-ico s1">{!! $si('store',22) !!}</div>
        <div>
            <div class="stat-val">{{ number_format($shopCount ?? 0, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Boutiques vérifiées</div>
            <div class="stat-sub">Contrôlées par Shopio</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-ico s2">{!! $si('bag',22) !!}</div>
        <div>
            <div class="stat-val">{{ number_format($productCount ?? 0, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Produits en ligne</div>
            <div class="stat-sub">Dans toutes les catégories</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-ico s3">{!! $si('truck',22) !!}</div>
        <div>
            <div class="stat-val">{{ number_format($deliveredCount ?? 0, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Commandes livrées</div>
            <div class="stat-sub">Payées à la réception</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-ico s4">{!! $si('users',22) !!}</div>
        <div>
            <div class="stat-val">{{ number_format($clientCount ?? 0, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Clients inscrits</div>
            <div class="stat-sub">Sur Shopio</div>
        </div>
    </div>
</div>
