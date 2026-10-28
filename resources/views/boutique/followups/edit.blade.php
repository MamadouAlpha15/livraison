{{--
    Espace vendeur → Relances → réglage d'une situation : quand, combien, message (variables, ton, langue,
    « Générer avec Shopio IA », aperçu), code promo, heures et jours d'envoi, canaux.
    Données : Boutique\FollowUpController@edit
--}}
@extends('layouts.app')

@section('title', $meta['label'] . ' · Relances')

@php
    $bodyClass = 'is-dashboard';
    use App\Services\FollowUp\FollowUpCatalog as Cat;
    use App\Support\IconLibrary as Ico;

    $split = fn (int $m) => $m >= 1440 && $m % 1440 === 0 ? [$m / 1440, 'jours'] : [(int) round($m / 60), 'heures'];
    [$delayValue, $delayUnit] = $split($rule->delay_minutes);
    [$intervalValue, $intervalUnit] = $split($rule->interval_minutes);
    $clientFacing = $meta['audience'] === 'client';
    $days = [1 => 'Lun', 2 => 'Mar', 3 => 'Mer', 4 => 'Jeu', 5 => 'Ven', 6 => 'Sam', 0 => 'Dim'];
    $whatsapp = (bool) config('services.whatsapp.enabled');
    $excluded = old('excluded_days', $rule->excluded_days ?? []);
    $channels = old('channels', $rule->channels ?? []);
@endphp

@push('styles')
@include('boutique._partials.sv-theme')
@include('boutique.followups._styles')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'followups'])

    <main class="main vs-main">
        <div class="topbar cp-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="tb-info">
                <a href="{{ route('boutique.followups.index') }}" class="cp-back">{!! Ico::svg('arrow-left', '', 15) !!} Relances</a>
                <h1 class="cp-title">{{ $meta['label'] }}</h1>
                <div class="tb-sub">{{ $meta['help'] }}</div>
            </div>
        </div>

        <div class="cp-content">
            @if($errors->any())
                <div class="cp-flash cp-flash-err" role="alert">Vérifie les champs en rouge.</div>
            @endif

            <form method="POST" action="{{ route('boutique.followups.update', $type) }}" class="fu-form" id="fuForm"
                  data-preview="{{ route('boutique.followups.preview', $type) }}" data-generate="{{ route('boutique.followups.generate', $type) }}">
                @csrf @method('PUT')

                <section class="cp-card fu-sec">
                    <label class="fu-pill" style="justify-self:start">
                        <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $rule->enabled))>
                        <span>{!! Ico::svg('check', '', 16) !!}&nbsp;Relance activée</span>
                    </label>
                    <div class="fu-row">
                        <div class="fu-field">
                            <label for="fuDelay">Première relance après</label>
                            <div class="fu-pair">
                                <input id="fuDelay" name="delay_value" type="number" min="0" max="365" class="fu-input" value="{{ old('delay_value', $delayValue) }}" required>
                                <select name="delay_unit" class="fu-select" aria-label="Unité">
                                    <option value="heures" @selected(old('delay_unit', $delayUnit) === 'heures')>heures</option>
                                    <option value="jours" @selected(old('delay_unit', $delayUnit) === 'jours')>jours</option>
                                </select>
                            </div>
                            @error('delay_value')<span class="fu-err">{{ $message }}</span>@enderror
                        </div>
                        <div class="fu-field">
                            <label for="fuMax">Nombre de relances au plus</label>
                            <select id="fuMax" name="max_attempts" class="fu-select">
                                @foreach(range(1, 5) as $n)<option value="{{ $n }}" @selected((int) old('max_attempts', $rule->max_attempts) === $n)>{{ $n }}</option>@endforeach
                            </select>
                        </div>
                        <div class="fu-field">
                            <label for="fuInterval">Entre deux relances</label>
                            <div class="fu-pair">
                                <input id="fuInterval" name="interval_value" type="number" min="1" max="365" class="fu-input" value="{{ old('interval_value', $intervalValue) }}" required>
                                <select name="interval_unit" class="fu-select" aria-label="Unité">
                                    <option value="heures" @selected(old('interval_unit', $intervalUnit) === 'heures')>heures</option>
                                    <option value="jours" @selected(old('interval_unit', $intervalUnit) === 'jours')>jours</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <p class="fu-help" style="margin:0">La relance s'arrête toute seule dès que le client répond, accepte, commande ou annule.</p>
                </section>

                <section class="cp-card fu-sec" aria-labelledby="fuMsgTitle">
                    <h2 id="fuMsgTitle" class="fu-h2">Message</h2>
                    <div class="fu-field">
                        <span class="fu-label">Ton</span>
                        <div class="fu-pills">
                            @foreach(Cat::TONES as $key => $label)
                            <label class="fu-pill"><input type="radio" name="tone" value="{{ $key }}" @checked(old('tone', $rule->tone) === $key)><span>{{ $label }}</span></label>
                            @endforeach
                        </div>
                    </div>
                    <div class="fu-row">
                        <div class="fu-field">
                            <label for="fuLocale">Langue</label>
                            <select id="fuLocale" name="locale" class="fu-select">
                                @foreach(Cat::LOCALES as $key => $label)<option value="{{ $key }}" @selected(old('locale', $rule->locale) === $key)>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                        @if($clientFacing)
                        <div class="fu-field">
                            <label for="fuPromo">Code promo à joindre</label>
                            <select id="fuPromo" name="promo_code_id" class="fu-select">
                                <option value="">Aucun</option>
                                @foreach($promos as $promo)
                                <option value="{{ $promo->id }}" @selected((int) old('promo_code_id', $rule->promo_code_id) === $promo->id)>{{ $promo->code }}@if($promo->expires_at) (jusqu'au {{ $promo->expires_at->format('d/m') }})@endif</option>
                                @endforeach
                            </select>
                            @if($promos->isEmpty())<span class="fu-help">Crée d'abord un code dans <a class="cp-link" href="{{ route('boutique.promo-codes.index') }}">Codes promo</a>.</span>@endif
                            @error('promo_code_id')<span class="fu-err">{{ $message }}</span>@enderror
                        </div>
                        @endif
                    </div>

                    <div class="fu-field">
                        <label for="fuTemplate">Texte du message</label>
                        <textarea id="fuTemplate" name="template" class="fu-textarea" maxlength="1000">{{ old('template', $rule->effectiveTemplate()) }}</textarea>
                        @error('template')<span class="fu-err">{{ $message }}</span>@enderror
                        <span class="fu-help">Touche une variable pour l'ajouter. Elle sera remplacée par la vraie valeur à l'envoi.
                            Une phrase entre [[ ]] disparaît si une de ses variables est vide (pas de code promo, par exemple).</span>
                        <div class="fu-vars">
                            @foreach(Cat::VARIABLES as $var => $label)
                            <button type="button" class="fu-var" data-var="{{ $var }}" title="{{ $label }}">{{ $var }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="fu-field">
                        <span class="fu-label">{!! Ico::svg('sparkles', '', 15) !!} Générer avec Shopio IA</span>
                        <div class="fu-ai">
                            <label class="sr-only" for="fuWish">Ton souhait pour l'IA (facultatif)</label>
                            <input id="fuWish" class="fu-input" maxlength="200" placeholder="Facultatif : mentionner la livraison rapide…">
                            <button type="button" class="cp-btn cp-btn-blue cp-btn-sm" id="fuGenerate">{!! Ico::svg('sparkles', '', 16) !!} Générer</button>
                            <button type="button" class="cp-btn cp-btn-line cp-btn-sm" id="fuDefault">Modèle par défaut</button>
                        </div>
                        <span class="fu-status" id="fuAiStatus" role="status" aria-live="polite"></span>
                    </div>

                    <div class="fu-field">
                        <span class="fu-preview-lbl">Aperçu avec des données d'exemple</span>
                        <div class="fu-preview" id="fuPreview">{{ $preview }}@if($clientFacing){{ "\n\n" . Cat::stopFooter($rule->locale) }}@endif</div>
                    </div>
                </section>

                <section class="cp-card fu-sec" aria-labelledby="fuWhenTitle">
                    <h2 id="fuWhenTitle" class="fu-h2">Heures et canaux</h2>
                    <div class="fu-row">
                        <div class="fu-field">
                            <label for="fuFrom">Envoyer à partir de</label>
                            <select id="fuFrom" name="send_from_hour" class="fu-select">
                                @foreach(range(0, 22) as $h)<option value="{{ $h }}" @selected((int) old('send_from_hour', $rule->send_from_hour) === $h)>{{ $h }} h</option>@endforeach
                            </select>
                        </div>
                        <div class="fu-field">
                            <label for="fuTo">Jusqu'à</label>
                            <select id="fuTo" name="send_to_hour" class="fu-select">
                                @foreach(range(1, 23) as $h)<option value="{{ $h }}" @selected((int) old('send_to_hour', $rule->send_to_hour) === $h)>{{ $h }} h</option>@endforeach
                            </select>
                            @error('send_to_hour')<span class="fu-err">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <span class="fu-help">Heure de Conakry. En dehors de cette plage, la relance attend le prochain créneau.</span>
                    <div class="fu-field">
                        <span class="fu-label">Jours sans envoi</span>
                        <div class="fu-checks">
                            @foreach($days as $num => $label)
                            <label class="fu-pill"><input type="checkbox" name="excluded_days[]" value="{{ $num }}" @checked(in_array($num, array_map('intval', $excluded), true))><span>{{ $label }}</span></label>
                            @endforeach
                        </div>
                        @error('excluded_days')<span class="fu-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="fu-field">
                        <span class="fu-label">Canaux</span>
                        <div class="fu-checks">
                            @foreach(Cat::CHANNELS as $key => $label)
                                @php $disabled = ($key === 'messaging' && !$clientFacing) || ($key === 'whatsapp' && !$whatsapp); @endphp
                                <label class="fu-pill"><input type="checkbox" name="channels[]" value="{{ $key }}" @checked(in_array($key, $channels, true) && !$disabled) @disabled($disabled)>
                                    <span>{{ $label }}@if($key === 'whatsapp' && !$whatsapp) · bientôt @endif</span></label>
                            @endforeach
                        </div>
                        @error('channels')<span class="fu-err">{{ $message }}</span>@enderror
                        <span class="fu-help">Une seule relance par client et par jour, toutes boutiques confondues. Un client qui écrit STOP n'est plus jamais relancé.</span>
                    </div>
                </section>

                <div class="fu-save">
                    <a href="{{ route('boutique.followups.index') }}" class="cp-btn cp-btn-line">Annuler</a>
                    <button type="submit" class="cp-btn cp-btn-blue">Enregistrer</button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('fuForm');
    const tpl = document.getElementById('fuTemplate');
    const preview = document.getElementById('fuPreview');
    const status = document.getElementById('fuAiStatus');
    const csrf = form.querySelector('input[name=_token]').value;
    const field = name => (form.querySelector(`[name="${name}"]:checked`) || form.querySelector(`[name="${name}"]`) || {}).value || '';
    let timer = null;

    async function post(url, body) {
        const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body) });
        return [res, await res.json().catch(() => ({}))];
    }

    async function refresh(template) {
        const [res, data] = await post(form.dataset.preview, { template: template ?? tpl.value, tone: field('tone'), locale: field('locale'), promo_code_id: field('promo_code_id') });
        if (!res.ok) return;
        preview.textContent = data.preview;
        if (data.unknown && data.unknown.length) setStatus('Variable inconnue : ' + data.unknown.join(', '), 'err');
        return data;
    }
    const later = () => { clearTimeout(timer); timer = setTimeout(() => refresh(), 450); };

    function setStatus(text, tone) { status.textContent = text; status.className = 'fu-status' + (tone ? ' ' + tone : ''); }

    tpl.addEventListener('input', later);
    form.querySelectorAll('[name=tone], [name=locale], [name=promo_code_id]').forEach(el => el.addEventListener('change', later));

    form.querySelectorAll('[data-var]').forEach(btn => btn.addEventListener('click', () => {
        const v = btn.dataset.var, s = tpl.selectionStart ?? tpl.value.length, e = tpl.selectionEnd ?? s;
        tpl.value = tpl.value.slice(0, s) + v + tpl.value.slice(e);
        tpl.focus(); tpl.setSelectionRange(s + v.length, s + v.length);
        later();
    }));

    document.getElementById('fuDefault').addEventListener('click', async () => {
        const data = await refresh('');
        if (data) { tpl.value = data.template; setStatus('Modèle par défaut remis pour ce ton et cette langue.', 'ok'); }
    });

    document.getElementById('fuGenerate').addEventListener('click', async (ev) => {
        const btn = ev.currentTarget; btn.disabled = true;
        setStatus('Shopio IA écrit ton message…');
        try {
            const [res, data] = await post(form.dataset.generate, { tone: field('tone'), locale: field('locale'), wish: document.getElementById('fuWish').value });
            if (!res.ok || !data.template) { setStatus(data.error || 'Shopio IA n\'a pas pu écrire le message. Réessaie.', 'err'); return; }
            tpl.value = data.template;
            await refresh();
            setStatus('Message proposé par Shopio IA : relis-le et modifie-le si besoin avant d\'enregistrer.', 'ok');
        } catch (e) { setStatus('Connexion perdue : réessaie.', 'err'); }
        finally { btn.disabled = false; }
    });
})();
</script>
@endpush
