{{-- Panneau droit : infos entreprise, zones, bouton confier --}}
{{-- Utilisé dans le right-panel desktop ET dans le drawer mobile --}}
@php $inDrawer = $inDrawer ?? false; @endphp

@if(!$inDrawer)
<aside class="right-panel">
@endif

    {{-- Infos entreprise --}}
    <div class="rp-section">
        <div class="rp-title">Entreprise de livraison</div>
        <div class="rp-company-card">
            <div class="chat-av" style="width:36px;height:36px;border-radius:9px;font-size:12px;flex-shrink:0;">
                @if($company->image)
                    <img src="{{ asset('storage/'.$company->image) }}" alt="{{ $company->name }}">
                @else
                    {{ strtoupper(substr($company->name,0,1)) }}
                @endif
            </div>
            <div style="min-width:0;">
                <div class="rp-co-name">{{ $company->name }}</div>
                @if($company->phone)
                <div class="rp-co-phone">📞 {{ $company->phone }}</div>
                @endif
            </div>
        </div>
        @if($company->address)
        <div style="font-size:11px;color:var(--text2);display:flex;gap:5px;margin-top:4px;">
            <span>📍</span><span>{{ $company->address }}</span>
        </div>
        @endif
    </div>

    {{-- Zones & Tarifs --}}
    @if($zones->isNotEmpty())
    <div class="rp-section">
        <div class="rp-title">📍 Zones & Tarifs</div>
        @foreach($zones as $zone)
        <div class="zone-pill">
            <span class="zone-dot" style="background:{{ $zone->color }};box-shadow:0 0 4px {{ $zone->color }}88;"></span>
            <span class="zone-pill-name">{{ $zone->name }}</span>
            <div style="text-align:right;flex-shrink:0;">
                <div class="zone-pill-price">{{ number_format($zone->price,0,',',' ') }} GNF</div>
                <div class="zone-pill-time">~{{ $zone->estimated_minutes }} min</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Confier une commande --}}
    @if($shopId)
    <div class="rp-section">
        <div class="rp-title">📦 Livraison</div>
        <button class="btn-confier" onclick="openOrderModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            Confier une commande
        </button>
        <div style="font-size:10px;color:var(--muted);margin-top:7px;text-align:center;line-height:1.6;">
            Assignez directement une commande<br>à <strong style="color:var(--text2);">{{ $company->name }}</strong>
        </div>
    </div>
    @endif

@if(!$inDrawer)
</aside>
@endif
