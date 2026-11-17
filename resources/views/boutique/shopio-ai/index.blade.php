{{--
    Espace vendeur → Shopio IA : réponses à valider, réglages (activation, mode, ton, remise maximale,
    infos de la boutique), conversations traitées avec « Reprendre la main », chiffres du mois.
    Données : Boutique\ShopioAiController@index
--}}
@extends('layouts.app')

@section('title', 'Shopio IA · ' . $shop->name)

@php
    $bodyClass = 'is-dashboard';
    use App\Models\ShopAiSetting;
    use App\Support\IconLibrary as Ico;
    use App\Support\Money;
    $pct = $limit > 0 ? min(100, (int) round($used * 100 / $limit)) : 100;
@endphp

@push('styles')
@include('boutique._partials.sv-theme')
@include('boutique.followups._styles')
<style>
.ai-use { padding: 18px 20px; display: grid; gap: 8px; }
.ai-bar { height: 10px; border-radius: 999px; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); overflow: hidden; }
.ai-bar i { display: block; height: 100%; background: var(--sv-blue); border-radius: 999px; }
.ai-sugg { padding: 16px 20px; display: grid; gap: 10px; }
.ai-sugg + .ai-sugg { border-top: 1px solid var(--border); }
.ai-q { font-size: 13px; color: var(--text-2); }
.ai-q strong { color: var(--text); }
.ai-sugg form { margin: 0; display: grid; gap: 8px; }
.ai-sugg .fu-textarea { min-height: 80px; }
.ai-sugg-act { display: flex; gap: 8px; flex-wrap: wrap; }
.ai-conv { display: flex; gap: 12px; align-items: center; padding: 14px 20px; }
.ai-conv + .ai-conv { border-top: 1px solid var(--border); }
.ai-conv-main { flex: 1; min-width: 0; }
.ai-conv-main p { margin: 2px 0 0; font-size: 13px; color: var(--text-2); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ai-conv form { margin: 0; }
@media (max-width: 760px) {
    .ai-sugg, .ai-conv, .ai-use { padding-inline: 16px; }
    .ai-conv { flex-wrap: wrap; }
    .ai-conv form, .ai-conv form .cp-btn, .ai-sugg-act .cp-btn { width: 100%; }
}
</style>
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'shopio-ai'])

    <main class="main vs-main">
        <div class="topbar cp-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <h1 class="cp-title">{!! Ico::svg('sparkles', '', 22) !!} Shopio IA</h1>
                <div class="tb-sub">Réponds à tes clients jour et nuit, avec les vraies infos de ta boutique.</div>
            </div>
        </div>

        <div class="cp-content">
            @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="cp-flash cp-flash-err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

            <section class="cp-kpis" aria-label="Chiffres des 30 derniers jours">
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Questions traitées · 30 j</div><div class="cp-kpi-val">{{ $stats['answered'] }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Passées au vendeur</div><div class="cp-kpi-val">{{ $stats['handoffs'] }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Commandes après une réponse</div><div class="cp-kpi-val">{{ $stats['orders']['count'] }}</div></div>
                <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Montant de ces commandes</div><div class="cp-kpi-val cp-num">{{ Money::gnf($stats['orders']['amount'], false) }} <small>GNF</small></div></div>
            </section>

            <section class="cp-card ai-use" aria-label="Messages du mois">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
                    <strong>Messages Shopio IA ce mois-ci</strong>
                    <span class="cp-num">{{ $used }} / {{ $limit }}</span>
                </div>
                <div class="ai-bar" role="progressbar" aria-valuenow="{{ $used }}" aria-valuemin="0" aria-valuemax="{{ $limit }}"><i style="width:{{ $pct }}%"></i></div>
                <span class="fu-help">
                    @if($used >= $limit) Limite atteinte : Shopio IA se remet en route le 1er du mois prochain. @endif
                    @unless($autoAllowed) Au plan gratuit, Shopio IA prépare des réponses que tu envoies en un clic. La réponse automatique est incluse dans le plan Pro. @endunless
                </span>
            </section>

            {{-- Réponses à valider --}}
            @if($suggestions->isNotEmpty())
            <section class="cp-card" aria-labelledby="ai-sugg-title">
                <div class="cp-list-hd"><h2 id="ai-sugg-title">Réponses à envoyer <span class="cp-news-n">{{ $suggestions->count() }}</span></h2></div>
                @foreach($suggestions as $s)
                <div class="ai-sugg">
                    <div class="ai-q"><strong>{{ $s->client?->name }}</strong>@if($s->product) · {{ $s->product->name }}@endif : « {{ \Illuminate\Support\Str::limit($s->replyTo?->body, 160) }} »</div>
                    <form method="POST" action="{{ route('boutique.shopio-ai.suggestions.send', $s) }}">
                        @csrf
                        <label class="sr-only" for="sg{{ $s->id }}">Réponse proposée</label>
                        <textarea id="sg{{ $s->id }}" name="body" class="fu-textarea" maxlength="1000">{{ $s->body }}</textarea>
                        <div class="ai-sugg-act">
                            <button type="submit" class="cp-btn cp-btn-blue cp-btn-sm">{!! Ico::svg('send', '', 16) !!} Envoyer</button>
                            <button type="submit" class="cp-btn cp-btn-line cp-btn-sm" formaction="{{ route('boutique.shopio-ai.suggestions.dismiss', $s) }}">Ignorer</button>
                        </div>
                    </form>
                </div>
                @endforeach
            </section>
            @endif

            {{-- Réglages --}}
            <form method="POST" action="{{ route('boutique.shopio-ai.update') }}" class="fu-form">
                @csrf @method('PUT')
                <section class="cp-card fu-sec" aria-labelledby="ai-set-title">
                    <h2 id="ai-set-title" class="fu-h2">Réglages</h2>
                    <label class="fu-pill" style="justify-self:start"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings->enabled))><span>{!! Ico::svg('check', '', 16) !!}&nbsp;Shopio IA activée</span></label>
                    <div class="fu-field">
                        <span class="fu-label">Quand un client écrit</span>
                        <div class="fu-pills">
                            <label class="fu-pill"><input type="radio" name="mode" value="{{ ShopAiSetting::MODE_SUGGESTION }}" @checked(old('mode', $settings->mode) === ShopAiSetting::MODE_SUGGESTION)><span>Me proposer une réponse</span></label>
                            <label class="fu-pill"><input type="radio" name="mode" value="{{ ShopAiSetting::MODE_AUTO }}" @checked(old('mode', $settings->mode) === ShopAiSetting::MODE_AUTO) @disabled(!$autoAllowed)><span>Répondre tout seul @unless($autoAllowed)· plan Pro @endunless</span></label>
                        </div>
                    </div>
                    <div class="fu-row">
                        <div class="fu-field">
                            <span class="fu-label">Avec les clients</span>
                            <div class="fu-pills">
                                <label class="fu-pill"><input type="radio" name="address_form" value="tu" @checked(old('address_form', $settings->address_form) === 'tu')><span>Tutoyer</span></label>
                                <label class="fu-pill"><input type="radio" name="address_form" value="vous" @checked(old('address_form', $settings->address_form) === 'vous')><span>Vouvoyer</span></label>
                            </div>
                        </div>
                        <div class="fu-field">
                            <label for="aiDiscount">Remise que l'IA peut accorder seule</label>
                            <div class="fu-pair"><input id="aiDiscount" name="max_discount_percent" type="number" min="1" max="90" class="fu-input" placeholder="Aucune" value="{{ old('max_discount_percent', $settings->max_discount_percent) }}"><span style="align-self:center">%</span></div>
                            <span class="fu-help">Vide : l'IA ne fait que te transmettre les propositions de prix.</span>
                        </div>
                    </div>
                    <div class="fu-field">
                        <label for="aiTone">Ton souhaité (facultatif)</label>
                        <input id="aiTone" name="tone_notes" class="fu-input" maxlength="500" placeholder="Ex. : chaleureux, toujours proposer la livraison" value="{{ old('tone_notes', $settings->tone_notes) }}">
                    </div>
                </section>

                <section class="cp-card fu-sec" aria-labelledby="ai-info-title">
                    <h2 id="ai-info-title" class="fu-h2">Infos de ta boutique pour l'IA</h2>
                    <p class="fu-help" style="margin:0">L'IA ne répond qu'avec ces infos et les vraies données de tes produits. Ce que tu laisses vide, elle dira qu'elle ne le sait pas.</p>
                    @foreach([
                        ['opening_hours', 'Horaires', 'Ex. : du lundi au samedi, de 9 h à 19 h', 500],
                        ['delivery_info', 'Livraison : zones, prix et délais', 'Ex. : Conakry 20 000 GNF, sous 48 h. Kindia 40 000 GNF.', 1000],
                        ['return_policy', 'Retours et échanges', 'Ex. : échange possible sous 3 jours si l\'article n\'est pas porté.', 1000],
                        ['faq', 'Réponses fréquentes', "Ex. : Les tailles vont du S au XL.\nLes photos sont prises en boutique.", 3000],
                    ] as [$name, $label, $placeholder, $max])
                    <div class="fu-field">
                        <label for="ai_{{ $name }}">{{ $label }}</label>
                        <textarea id="ai_{{ $name }}" name="{{ $name }}" class="fu-textarea" style="min-height:80px" maxlength="{{ $max }}" placeholder="{{ $placeholder }}">{{ old($name, $settings->$name) }}</textarea>
                    </div>
                    @endforeach
                </section>
                <div class="fu-save"><button type="submit" class="cp-btn cp-btn-blue">Enregistrer</button></div>
            </form>

            {{-- Conversations --}}
            <section class="cp-card" aria-labelledby="ai-conv-title">
                <div class="cp-list-hd"><h2 id="ai-conv-title">Conversations traitées par l'IA · 30 j</h2>
                    <a class="cp-link" href="{{ route('boutique.messages.hub') }}">Ouvrir la messagerie</a></div>
                @forelse($conversations as $c)
                    @php $paused = $c['state']?->handed_off_at; @endphp
                    <div class="ai-conv">
                        <div class="ai-conv-main">
                            <strong>{{ $c['client']->name }}</strong>
                            @if($paused)<span class="cp-st warn">Tu as la main</span>@else<span class="cp-st info">Shopio IA répond</span>@endif
                            <p>{{ $c['replies'] }} réponse(s) · dernière le {{ $c['last']?->created_at?->format('d/m à H:i') }} : {{ $c['last']?->body }}</p>
                            @if($c['state']?->handoff_reason)<p>Raison : {{ $c['state']->handoff_reason }}</p>@endif
                        </div>
                        @if($paused)
                            <form method="POST" action="{{ route('boutique.shopio-ai.hand-back', $c['client']) }}">@csrf<button class="cp-btn cp-btn-line cp-btn-sm" type="submit">Rendre la main à l'IA</button></form>
                        @else
                            <form method="POST" action="{{ route('boutique.shopio-ai.take-over', $c['client']) }}">@csrf<button class="cp-btn cp-btn-blue cp-btn-sm" type="submit">Reprendre la main</button></form>
                        @endif
                    </div>
                @empty
                    <div class="cp-empty"><strong>Aucune conversation pour l'instant</strong><span>Active Shopio IA : elle répondra aux prochains messages de tes clients.</span></div>
                @endforelse
            </section>
        </div>
    </main>
</div>
@endsection
