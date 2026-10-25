    <aside class="sidebar" id="sidebar">
          <div class="sb-brand">
            <a href="{{ route('boutique.dashboard') }}" class="sb-logo">
               <div class="sb-logo-icon"><img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="Shopio" style="width:100%;height:100%;object-fit:cover;border-radius:9px"></div> 
                <span class="sb-shop-name">{{ $shop->name }}</span>
            </a>  
            <button class="sb-close" id="btnCloseSidebar" aria-label="Fermer le menu">{!! $I['close'] !!}</button>
            <div class="sb-status">
                <span class="pulse"></span>
                {{ $shop->is_approved ? 'Boutique active' : 'En attente de validation' }}
                &nbsp;·&nbsp;
                {{ ucfirst(auth()->user()->role_in_shop ?? auth()->user()->role) }}
            </div>
        </div>
        <div class="sb-scroll-hint" id="sbScrollHint">
            <div class="sb-scroll-hint-arrow">
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
                <div class="sb-scroll-hint-dot"></div>
            </div>
        </div>
        <nav class="sb-nav">
            <a href="{{ route('boutique.dashboard') }}" class="sb-item active" style="margin-bottom:4px"><span class="ico">{!! $I['grid'] !!}</span> Tableau de bord</a>
            <div class="sb-section">Boutique</div>
            <a href="{{ route('boutique.messages.hub') }}" class="sb-item"><span class="ico">{!! $I['msg'] !!}</span> Messages <span class="sb-badge" id="sbMsgBadge" style="display:none"></span></a>
            <a href="{{ route('boutique.orders.index') }}" class="sb-item"><span class="ico">{!! $I['box'] !!}</span> Commandes
                @if(!$isPro)
                    <span class="sb-badge" style="background:{{ $cmdClass === 'danger' ? '#dc2626' : ($cmdClass === 'warn' ? '#d97706' : '#1668D8') }};margin-left:auto;">{{ $cmdMonth }}/{{ $maxCmdMois }}</span>
                @else
                    <span class="sb-badge" id="sbOrdersBadge" style="{{ $pendingCount > 0 ? '' : 'display:none' }}">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('products.index') }}" class="sb-item"><span class="ico">{!! $I['tag'] !!}</span> Produits
                @if(!$isPro)<span class="sb-badge" style="background:{{ $prodClass === 'danger' ? '#ef4444' : ($prodClass === 'warn' ? '#f59e0b' : '#1F7CF6') }};color:#fff;font-size:10px;padding:1px 5px;border-radius:8px;margin-left:auto">{{ $totalProduits }}/{{ $maxProduits }}</span>@endif
            </a>
            @if(auth()->user()?->role === 'admin')<a href="{{ route('boutique.campaigns.index') }}" class="sb-item"><span class="ico">{!! \App\Support\IconLibrary::svg('star', '', 16) !!}</span> Campagnes</a>@endif
            @if($isPro)
            <a href="{{ route('boutique.promo-codes.index') }}" class="sb-item"><span class="ico">{!! $I['promo'] !!}</span> Codes promo</a>
            @else
            <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['promo'] !!}</span> Codes promo <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
            @endif
            <a href="{{ route('boutique.clients.index') }}" class="sb-item"><span class="ico">{!! $I['users'] !!}</span> Clients</a>
            <a href="{{ route('boutique.marketing') }}" class="sb-item"><span class="ico"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10v4h3l6 4V6l-6 4zM16 9a4 4 0 0 1 0 6M18.5 6.5a8 8 0 0 1 0 11"/></svg></span> Marketing</a>
            @if($isPro)
            <a href="{{ route('boutique.employees.index') }}" class="sb-item"><span class="ico">{!! $I['briefcase'] !!}</span> Équipe</a>
            @else
            <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['briefcase'] !!}</span> Équipe <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
            @endif
            <div class="sb-section">Livraison</div>
            @if($isPro)
            <a href="{{ route('boutique.livreurs.index') }}" class="sb-item"><span class="ico">{!! $I['bike'] !!}</span> Livreurs <span class="sb-badge" id="sbLivreursBadge" style="{{ $livreursDisponibles->count() > 0 ? '' : 'display:none' }}">{{ $livreursDisponibles->count() }}</span></a>
            @else
            <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['bike'] !!}</span> Livreurs <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
            @endif
            @if($isPro)
            <a href="{{ route('delivery.companies.index') }}" class="sb-item"><span class="ico">{!! $I['building'] !!}</span> Partenaires</a>
            @else
            <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['building'] !!}</span> Partenaires <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
            @endif
            <div class="sb-section">Finances</div>
            <div class="sb-group">
                <button class="sb-group-toggle" onclick="toggleGroup(this)" type="button">
                    <span class="ico">{!! $I['coin'] !!}</span> Finances & Rapports
                    @if(!$isPro)<span style="font-size:11px;margin-left:4px;opacity:.7;">🔒</span>@endif
                    <span class="sb-arrow">▶</span>
                </button>
                <div class="sb-sub">
                    <a href="{{ route('boutique.payments.index') }}" class="sb-item"><span class="ico">{!! $I['card'] !!}</span> Paiements</a>
                    <a href="{{ route('boutique.commissions.index') }}" class="sb-item"><span class="ico">{!! $I['chart'] !!}</span> Commissions</a>
                    @if($isPro)
                    <a href="{{ route('boutique.reports.index') }}" class="sb-item"><span class="ico">{!! $I['clip'] !!}</span> Rapports</a>
                    @else
                    <a href="{{ route('boutique.subscription.upgrade') }}" class="sb-item" style="opacity:.6;" title="Plan Pro requis"><span class="ico">{!! $I['clip'] !!}</span> Rapports <span class="sb-badge" style="background:#f59e0b;">🔒</span></a>
                    @endif
                    @if(auth()->user()->role === 'admin')<a href="{{ route('shop.edit', $shop) }}" class="sb-item"><span class="ico">{!! $I['gear'] !!}</span> Paramètres</a>@endif
                </div>
            </div>
            <div class="sb-section">Aide</div>
            <a href="{{ route('support.index') }}" class="sb-item"><span class="ico">{!! $I['headphone'] !!}</span> Support <span class="sb-badge" id="sbSupportBadge" style="display:none;background:#10b981"></span></a>
        </nav>
        <div class="sb-footer">
           
            <a href="{{ route('profile.edit') }}" class="sb-user">
                <div class="sb-av">{{ $initials }}</div>
                <div style="flex:1;min-width:0">
                    <div class="sb-uname">{{ Str::limit(auth()->user()->name, 20) }}</div>
                    <div class="sb-urole">{{ auth()->user()->role === 'admin' ? 'Administrateur' : ucfirst(auth()->user()->role) }}</div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout"><span class="sb-ft-ico">{!! $I['logout'] !!}</span> {{ __('app.logout') }}</button>
            </form>
        </div>
    </aside>
