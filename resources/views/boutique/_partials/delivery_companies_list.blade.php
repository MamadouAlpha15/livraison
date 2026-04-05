{{--
    resources/views/boutique/_partials/delivery_companies_list.blade.php
    Variables : $companies (Collection<DeliveryCompany>)
--}}
<div class="co-list">
    @forelse($companies as $company)
    <div class="co-row"
         onclick="window.location='{{ route('company.chat.show', $company) }}'"
         title="Ouvrir la discussion avec {{ $company->name }}">
        <div class="co-logo">
            @if(!empty($company->logo))
                <img src="{{ asset('storage/'.$company->logo) }}" alt="{{ $company->name }}">
            @else
                🚚
            @endif
        </div>
        <div class="co-info">
            <div class="co-nm">{{ $company->name }}</div>
            <div class="co-mt">{{ $company->phone ?? 'Contact non renseigné' }}</div>
        </div>
        @if($company->commission_rate)
        <span class="co-commission">{{ number_format($company->commission_rate*100,1) }}%</span>
        @endif
        <a href="{{ route('company.chat.show', $company) }}"
           class="btn btn-sm"
           onclick="event.stopPropagation()">
            💬 Chat
        </a>
    </div>
    @empty
    <div style="padding:20px;text-align:center;font-size:12px;color:var(--muted)">
        Aucune entreprise partenaire enregistrée.
    </div>
    @endforelse
</div>