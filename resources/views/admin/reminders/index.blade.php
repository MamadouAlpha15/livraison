{{--
    Équipe Shopio → Relances : rappels « Tu nous manques » aux clients et entreprises de livraison inactifs.
    Données : Admin\ReminderController@index
--}}
@extends('layouts.app')

@section('title', 'Relances · Équipe Shopio')

@php
    $bodyClass = 'is-dashboard';
    use App\Models\InactivityReminder;
    use App\Support\IconLibrary as Ico;
    $n = fn ($v) => number_format((int) $v, 0, ',', "\u{00A0}");
    $filters = ['' => 'Tous'] + InactivityReminder::AUDIENCES;
@endphp

@push('styles')
@include('admin.campaigns._styles')
<style>
.rl-kpis { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.rl-kpi small { display: block; margin-top: 6px; font-size: 13px; color: var(--text-2); font-weight: 500; }
.rl-form { padding: 22px; display: grid; gap: 18px; }
.rl-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; }
.rl-unit { display: flex; align-items: center; gap: 8px; }
.rl-unit .ac-input { flex: 1; min-width: 0; font-variant-numeric: tabular-nums; }
.rl-unit span { font-size: 13px; color: var(--text-2); white-space: nowrap; }
.rl-checks { display: flex; flex-wrap: wrap; gap: 10px 22px; }
.rl-check { display: flex; gap: 10px; align-items: center; font-size: 14px; font-weight: 600; cursor: pointer; }
.rl-check input { width: 20px; height: 20px; margin: 0; accent-color: var(--sv-btn); }
.rl-switch { padding: 14px 16px; border-radius: 14px; background: var(--sv-b50); }
.rl-sec { margin: 0; font-size: 15px; font-weight: 800; }
.rl-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; }
.rl-head form { margin: 0; }
.rl-chan { display: inline-flex; gap: 4px; flex-wrap: wrap; }
.rl-off { padding: 12px 16px; border-radius: 14px; background: var(--sv-amb50); color: var(--sv-amb-ink); font-weight: 700; font-size: 14px; }
@media (max-width: 760px) {
    .rl-kpis { grid-template-columns: 1fr; }
    .rl-form { padding: 16px; }
    .rl-head form, .rl-head .cp-btn { width: 100%; }
}
</style>
@endpush

@section('content')
<header class="ac-bar">
    <div class="ac-bar-in">
        <a href="{{ route('admin.reminders.index') }}" class="ac-brand" aria-label="Relances Shopio">
            <span class="ac-logo">{!! Ico::svg('bell', '', 18) !!}</span>
            <span><strong>Shopio</strong> <span class="ac-brand-sub">Relances</span></span>
        </a>
        <nav class="ac-nav" aria-label="Panneau admin">
            <a href="{{ route('admin.reminders.index') }}" aria-current="page">Relances</a>
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
    <div class="rl-head">
        <div>
            <h1 class="ac-h1">Relances des inactifs</h1>
            <p class="ac-sub">Un rappel « Tu nous manques » part chaque jour à 10 h vers les clients et les entreprises de livraison qui ne sont pas revenus sur Shopio.</p>
        </div>
        <form method="POST" action="{{ route('admin.reminders.send') }}" onsubmit="return confirm('Envoyer maintenant les rappels dus aujourd\'hui ?')">
            @csrf
            <button type="submit" class="cp-btn cp-btn-blue" @disabled(!$settings->enabled)>{!! Ico::svg('send', '', 18) !!} Envoyer maintenant</button>
        </form>
    </div>

    @if(session('success'))<div class="cp-flash cp-flash-ok" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="cp-flash cp-flash-err" role="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    @unless($settings->enabled)<div class="rl-off" role="status">Les relances sont désactivées : aucun rappel ne part.</div>@endunless

    <section class="rl-kpis" aria-label="Chiffres des relances">
        <div class="cp-card cp-kpi rl-kpi">
            <div class="cp-kpi-lbl">À relancer aujourd'hui</div>
            <div class="cp-kpi-val">{{ $n($due['client'] + $due['company']) }}</div>
            <small>{{ $n($due['client']) }} client(s) · {{ $n($due['company']) }} entreprise(s) · {{ $n($sentToday) }} déjà envoyé(s) aujourd'hui, plafond {{ $n($settings->daily_limit) }}</small>
        </div>
        @foreach(InactivityReminder::AUDIENCES as $key => $label)
        <div class="cp-card cp-kpi rl-kpi">
            <div class="cp-kpi-lbl">{{ $label }} · 30 derniers jours</div>
            <div class="cp-kpi-val">{{ $stats[$key]['rate'] !== null ? $stats[$key]['rate'] . ' %' : '—' }} <small style="display:inline">revenus</small></div>
            <small>{{ $n($stats[$key]['returned']) }} revenu(s) sur {{ $n($stats[$key]['sent']) }} rappel(s) envoyé(s)</small>
        </div>
        @endforeach
    </section>

    {{-- Réglages --}}
    <form method="POST" action="{{ route('admin.reminders.update') }}" class="cp-card rl-form">
        @csrf @method('PUT')
        <label class="rl-check rl-switch"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings->enabled))> Relances activées</label>

        <div class="rl-grid">
            @foreach([
                ['client_days', 'Client inactif depuis', 'jours'],
                ['company_days', 'Entreprise inactive depuis', 'jours'],
                ['interval_days', 'Entre deux rappels', 'jours'],
                ['max_steps', 'Rappels par absence, au plus', 'rappels'],
                ['daily_limit', 'Envois par jour, au plus', 'envois'],
            ] as [$name, $label, $unit])
            <div class="ac-field">
                <label for="r_{{ $name }}">{{ $label }}</label>
                <div class="rl-unit">
                    <input id="r_{{ $name }}" name="{{ $name }}" type="number" min="1" class="ac-input" required value="{{ old($name, $settings->$name) }}">
                    <span>{{ $unit }}</span>
                </div>
                @error($name)<span class="ac-err">{{ $message }}</span>@enderror
            </div>
            @endforeach
        </div>

        <div class="ac-field">
            <span class="rl-sec">Canaux</span>
            <div class="rl-checks">
                <label class="rl-check"><input type="checkbox" name="channel_push" value="1" @checked(old('channel_push', $settings->channel_push))> Notification (téléphone et navigateur)</label>
                <label class="rl-check"><input type="checkbox" name="channel_mail" value="1" @checked(old('channel_mail', $settings->channel_mail))> E-mail, avec lien de désinscription</label>
                <label class="rl-check"><input type="checkbox" name="channel_sms" value="1" @checked(old('channel_sms', $settings->channel_sms))> SMS</label>
            </div>
            <span class="ac-help">Aucun fournisseur SMS n'est branché pour l'instant : les SMS sont seulement écrits dans le journal.
                {{ $n($stats['opt_outs']) }} personne(s) se sont désinscrites des rappels.</span>
        </div>
        <div><button type="submit" class="cp-btn cp-btn-line">Enregistrer les réglages</button></div>
    </form>

    {{-- Relances des boutiques : vendeur inactif (règle de l'équipe) + chiffres de toutes les boutiques --}}
    @php
        $vr = $vendorRule;
        $vrDays = fn ($m) => max(1, (int) round($m / 1440));
        $vrChannels = old('channels', $vr->channels ?? []);
    @endphp
    <form method="POST" action="{{ route('admin.reminders.vendor-rule') }}" class="cp-card rl-form">
        @csrf @method('PUT')
        <div>
            <span class="rl-sec">Vendeurs inactifs</span>
            <p class="ac-help" style="margin:4px 0 0">Rappel au vendeur dont la boutique n'a aucun produit en ligne, ou qui ne s'est pas connecté depuis le délai.
                {{ $n($vendorSent) }} rappel(s) envoyé(s) ces 30 derniers jours. Relances des boutiques vers leurs clients sur 30 jours :
                {{ $n($shopStats['sent']) }} envoyée(s), {{ $shopStats['rate'] !== null ? $shopStats['rate'] . ' %' : '—' }} de réponses,
                {{ $n($shopStats['orders']) }} commande(s) récupérée(s), {{ \App\Support\Money::gnf($shopStats['amount']) }}.</p>
        </div>
        <label class="rl-check rl-switch"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $vr->enabled))> Rappel des vendeurs inactifs activé</label>
        <input type="hidden" name="delay_unit" value="jours"><input type="hidden" name="interval_unit" value="jours">
        <input type="hidden" name="locale" value="fr">
        <input type="hidden" name="send_from_hour" value="{{ $vr->send_from_hour }}"><input type="hidden" name="send_to_hour" value="{{ $vr->send_to_hour }}">
        <div class="rl-grid">
            <div class="ac-field"><label for="vr_delay">Inactif depuis</label>
                <div class="rl-unit"><input id="vr_delay" name="delay_value" type="number" min="1" class="ac-input" required value="{{ old('delay_value', $vrDays($vr->delay_minutes)) }}"><span>jours</span></div></div>
            <div class="ac-field"><label for="vr_max">Rappels au plus</label>
                <div class="rl-unit"><input id="vr_max" name="max_attempts" type="number" min="1" max="5" class="ac-input" required value="{{ old('max_attempts', $vr->max_attempts) }}"><span>rappels</span></div></div>
            <div class="ac-field"><label for="vr_int">Entre deux rappels</label>
                <div class="rl-unit"><input id="vr_int" name="interval_value" type="number" min="1" class="ac-input" required value="{{ old('interval_value', $vrDays($vr->interval_minutes)) }}"><span>jours</span></div></div>
            <div class="ac-field"><label for="vr_tone">Ton</label>
                <select id="vr_tone" name="tone" class="ac-select">@foreach(\App\Services\FollowUp\FollowUpCatalog::TONES as $k => $l)<option value="{{ $k }}" @selected(old('tone', $vr->tone) === $k)>{{ $l }}</option>@endforeach</select></div>
        </div>
        <div class="ac-field">
            <label for="vr_tpl">Message</label>
            <textarea id="vr_tpl" name="template" class="ac-textarea" maxlength="1000">{{ old('template', $vr->effectiveTemplate()) }}</textarea>
            <span class="ac-help">Variables : {prenom_client} (prénom du vendeur), {nom_boutique}, {lien_boutique}.</span>
            @error('template')<span class="ac-err">{{ $message }}</span>@enderror
        </div>
        <div class="rl-checks">
            <label class="rl-check"><input type="checkbox" name="channels[]" value="in_app" @checked(in_array('in_app', $vrChannels, true))> Notification</label>
            <label class="rl-check"><input type="checkbox" name="channels[]" value="sms" @checked(in_array('sms', $vrChannels, true))> SMS</label>
        </div>
        @error('channels')<span class="ac-err">{{ $message }}</span>@enderror
        <div><button type="submit" class="cp-btn cp-btn-line">Enregistrer le rappel des vendeurs</button></div>
    </form>

    {{-- Derniers envois --}}
    <section class="cp-card">
        <div class="cp-list-hd">
            <h2>Derniers rappels envoyés</h2>
            <nav class="cp-pills" aria-label="Filtrer">
                @foreach($filters as $key => $label)
                <a class="cp-pill" href="{{ route('admin.reminders.index', array_filter(['public' => $key])) }}" @if((string) $audience === (string) $key) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        @if($recent->isEmpty())
            <div class="cp-empty"><strong>Aucun rappel envoyé pour l'instant</strong><span>Les rappels partent chaque jour à 10 h, ou tout de suite avec « Envoyer maintenant ».</span></div>
        @else
        <table class="cp-table">
            <thead><tr><th>Personne</th><th>Rappel</th><th>Canaux</th><th>Envoyé le</th><th>Retour</th></tr></thead>
            <tbody>
            @foreach($recent as $reminder)
                <tr>
                    <td class="cp-cell-main">
                        <div class="cp-row-name">{{ $reminder->user?->name ?? 'Compte supprimé' }}</div>
                        <div class="cp-row-sub">{{ InactivityReminder::AUDIENCES[$reminder->audience] ?? $reminder->audience }} · {{ $reminder->user?->email }}</div>
                    </td>
                    <td data-label="Rappel">n° {{ $reminder->step }}</td>
                    <td data-label="Canaux"><span class="rl-chan">@foreach($reminder->channels as $c)<span class="cp-st muted">{{ InactivityReminder::CHANNELS[$c] ?? $c }}</span>@endforeach</span></td>
                    <td data-label="Envoyé le">{{ $reminder->sent_at->format('d/m/Y à H:i') }}</td>
                    <td class="cp-cell-st">
                        @if($reminder->returned_at)<span class="cp-st ok">Revenu le {{ $reminder->returned_at->format('d/m') }}</span>
                        @else<span class="cp-st muted">Pas encore</span>@endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($recent->hasPages())<div class="cp-pagination">{{ $recent->links() }}</div>@endif
        @endif
    </section>
</main>
@endsection
