{{--
    Équipe Shopio → Shopio IA : coût par boutique et par mois, limites par plan, FAQ lue par l'IA,
    journal des outils appelés. Données : Admin\ShopioAiController@index
--}}
@extends('layouts.app')

@section('title', 'Shopio IA · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Models\FaqEntry;
    use App\Support\IconLibrary as Ico;
    $n = fn ($v) => number_format((int) $v, 0, ',', "\u{00A0}");
    $usd = fn ($v) => number_format((float) $v, 2, ',', "\u{00A0}") . "\u{00A0}$";
    $monthLabel = fn ($m) => \Illuminate\Support\Carbon::createFromFormat('Y-m', $m)->locale('fr')->translatedFormat('F Y');
@endphp

@push('styles')
@include('admin.campaigns._styles')
<style>
.sa-form { padding: 22px; display: grid; gap: 16px; }
.sa-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
.sa-check { display: flex; gap: 10px; align-items: center; font-size: 14px; font-weight: 600; cursor: pointer; }
.sa-check input { width: 20px; height: 20px; margin: 0; accent-color: var(--sv-btn); }
.sa-faq { padding: 16px 22px; display: grid; gap: 10px; }
.sa-faq + .sa-faq { border-top: 1px solid var(--border); }
.sa-faq summary { cursor: pointer; font-weight: 700; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.sa-faq form { display: grid; gap: 10px; margin-top: 10px; }
.sa-faq .ac-input, .sa-faq .ac-select, .sa-faq .ac-textarea { width: 100%; }
.sa-row { display: flex; gap: 8px; flex-wrap: wrap; }
.sa-log td { font-size: 13px; }
.sa-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12px; overflow-wrap: anywhere; }
@media (max-width: 760px) { .sa-form, .sa-faq { padding: 16px; } }
</style>
@endpush

@section('content')
<header class="ac-bar">
    <div class="ac-bar-in">
        <a href="{{ route('admin.shopio-ai.index') }}" class="ac-brand" aria-label="Shopio IA">
            <span class="ac-logo">{!! Ico::svg('sparkles', '', 18) !!}</span>
            <span><strong>Shopio</strong> <span class="ac-brand-sub">IA</span></span>
        </a>
        <nav class="ac-nav" aria-label="Panneau admin">
            <a href="{{ route('admin.shopio-ai.index') }}" aria-current="page">Shopio IA</a>
            <a href="{{ route('admin.reminders.index') }}">Relances</a>
            <a href="{{ route('admin.dashboard') }}">Panneau admin</a>
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="ac-out">
            @csrf
            @include('admin._partials.dark-toggle', ['class' => 'ac-dark'])
            <span class="ac-me">{{ auth()->user()->name }}</span>
            <button type="submit">Déconnexion</button>
        </form>
    </div>
</header>

<main class="ac-page">
    <div>
        <h1 class="ac-h1">Shopio IA</h1>
        <p class="ac-sub">L'assistant qui répond aux clients dans la messagerie des boutiques, avec les vraies données de Shopio.</p>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="cp-flash cp-flash-err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    {{-- Coût --}}
    <section class="cp-card">
        <div class="cp-list-hd">
            <h2>Coût par boutique</h2>
            <form method="GET" class="sa-row" style="margin:0">
                <label class="sr-only" for="saMonth">Mois</label>
                <select id="saMonth" name="mois" class="ac-select" onchange="this.form.submit()">
                    @foreach($months as $m)<option value="{{ $m }}" @selected($m === $month)>{{ ucfirst($monthLabel($m)) }}</option>@endforeach
                </select>
                <noscript><button class="cp-btn cp-btn-line cp-btn-sm">Voir</button></noscript>
            </form>
        </div>
        <div class="cp-kpis" style="padding:16px 24px">
            <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Messages</div><div class="cp-kpi-val">{{ $n($totals['messages']) }}</div></div>
            <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Passés au vendeur</div><div class="cp-kpi-val">{{ $n($totals['handoffs']) }}</div></div>
            <div class="cp-card cp-kpi"><div class="cp-kpi-lbl">Coût estimé</div><div class="cp-kpi-val">{{ $usd($totals['cost']) }}</div></div>
        </div>
        @if($usages->isEmpty())
            <div class="cp-empty"><strong>Aucune réponse de Shopio IA ce mois-ci</strong><span>Les boutiques l'activent dans leur espace vendeur, menu Shopio IA.</span></div>
        @else
        <table class="cp-table">
            <thead><tr><th>Boutique</th><th>Messages</th><th>Tokens (entrée / sortie)</th><th>Coût</th></tr></thead>
            <tbody>
            @foreach($usages as $u)
                <tr>
                    <td class="cp-cell-main"><div class="cp-row-name">{{ $u->shop?->name ?? 'Boutique supprimée' }}</div>
                        <div class="cp-row-sub">{{ $u->shop && $u->shop->plan === 'pro' && $u->shop->plan_expires_at?->isFuture() ? 'Plan Pro' : 'Plan gratuit' }} · {{ $n($u->handoffs) }} passage(s) au vendeur</div></td>
                    <td data-label="Messages" class="cp-num">{{ $n($u->messages) }}</td>
                    <td data-label="Tokens" class="cp-num">{{ $n($u->input_tokens) }} / {{ $n($u->output_tokens) }}</td>
                    <td data-label="Coût" class="cp-num"><strong>{{ $usd($u->costUsd()) }}</strong></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
        <p class="ac-help" style="padding:0 24px 18px;margin:0">Coût estimé au tarif de Claude Opus 5.5 : 4 $ par million de tokens en entrée, 20 $ en sortie.</p>
    </section>

    {{-- Réglages --}}
    <form method="POST" action="{{ route('admin.shopio-ai.settings') }}" class="cp-card sa-form">
        @csrf @method('PUT')
        <h2 class="ac-h1" style="font-size:18px">Réglages</h2>
        <label class="sa-check"><input type="checkbox" name="enabled" value="1" @checked($settings->enabled)> Shopio IA activée sur Shopio (désactivée : aucune boutique ne reçoit de réponse de l'IA)</label>
        <div class="sa-grid">
            <div class="ac-field"><label for="saFree">Messages par mois · plan gratuit</label><input id="saFree" name="free_monthly_limit" type="number" min="0" class="ac-input" value="{{ old('free_monthly_limit', $settings->free_monthly_limit) }}" required></div>
            <div class="ac-field"><label for="saPro">Messages par mois · plan Pro</label><input id="saPro" name="pro_monthly_limit" type="number" min="0" class="ac-input" value="{{ old('pro_monthly_limit', $settings->pro_monthly_limit) }}" required></div>
        </div>
        <label class="sa-check"><input type="checkbox" name="free_auto_allowed" value="1" @checked($settings->free_auto_allowed)> Réponse automatique aussi au plan gratuit (sinon : suggestions à valider)</label>
        <div><button type="submit" class="cp-btn cp-btn-line">Enregistrer les réglages</button></div>
    </form>

    {{-- FAQ --}}
    <section class="cp-card">
        <div class="cp-list-hd"><h2>FAQ de Shopio</h2><span class="ac-help">Shopio IA la lit en direct (outil faq_shopio).</span></div>
        <details class="sa-faq" @if($errors->any() && !old('_faq_id')) open @endif>
            <summary>{!! Ico::svg('plus', '', 16) !!} Ajouter une question</summary>
            <form method="POST" action="{{ route('admin.shopio-ai.faq.store') }}">
                @csrf
                <select name="category" class="ac-select" aria-label="Catégorie">@foreach(FaqEntry::CATEGORIES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
                <input name="question" class="ac-input" maxlength="255" placeholder="Question" required aria-label="Question">
                <textarea name="answer" class="ac-textarea" maxlength="2000" placeholder="Réponse, en français simple" required aria-label="Réponse"></textarea>
                <div><button type="submit" class="cp-btn cp-btn-blue cp-btn-sm">Ajouter</button></div>
            </form>
        </details>
        @foreach($faq as $q)
        <details class="sa-faq">
            <summary><span class="cp-st {{ $q->is_active ? 'info' : 'muted' }}">{{ FaqEntry::CATEGORIES[$q->category] ?? $q->category }}</span> {{ $q->question }}</summary>
            <form method="POST" action="{{ route('admin.shopio-ai.faq.update', $q) }}">
                @csrf @method('PUT')
                <select name="category" class="ac-select" aria-label="Catégorie">@foreach(FaqEntry::CATEGORIES as $k => $l)<option value="{{ $k }}" @selected($q->category === $k)>{{ $l }}</option>@endforeach</select>
                <input name="question" class="ac-input" maxlength="255" value="{{ $q->question }}" required aria-label="Question">
                <textarea name="answer" class="ac-textarea" maxlength="2000" required aria-label="Réponse">{{ $q->answer }}</textarea>
                <label class="sa-check"><input type="checkbox" name="is_active" value="1" @checked($q->is_active)> Utilisée par Shopio IA</label>
                <div class="sa-row">
                    <button type="submit" class="cp-btn cp-btn-line cp-btn-sm">Enregistrer</button>
                    <button type="submit" class="cp-btn cp-btn-danger cp-btn-sm" form="del{{ $q->id }}">Supprimer</button>
                </div>
            </form>
            <form method="POST" action="{{ route('admin.shopio-ai.faq.destroy', $q) }}" id="del{{ $q->id }}" onsubmit="return confirm('Supprimer cette question ?')">@csrf @method('DELETE')</form>
        </details>
        @endforeach
    </section>

    {{-- Journal des outils --}}
    <section class="cp-card">
        <div class="cp-list-hd"><h2>Derniers outils appelés</h2><span class="ac-help">Pour vérifier d'où viennent les réponses (sans données personnelles).</span></div>
        @if($calls->isEmpty())
            <div class="cp-empty"><strong>Aucun appel pour l'instant</strong></div>
        @else
        <table class="cp-table sa-log">
            <thead><tr><th>Outil</th><th>Boutique</th><th>Paramètres</th><th>Résultat</th></tr></thead>
            <tbody>
            @foreach($calls as $c)
                <tr>
                    <td class="cp-cell-main"><div class="cp-row-name">{{ $c->tool }}</div><div class="cp-row-sub">{{ $c->created_at->format('d/m H:i') }} · {{ $c->duration_ms }} ms</div></td>
                    <td data-label="Boutique">{{ $c->shop?->name ?? '—' }}</td>
                    <td data-label="Paramètres"><span class="sa-mono">{{ $c->input ? json_encode($c->input, JSON_UNESCAPED_UNICODE) : '—' }}</span></td>
                    <td class="cp-cell-st"><span class="cp-st {{ $c->ok ? 'ok' : 'warn' }}">{{ $c->summary }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </section>
</main>
@endsection
