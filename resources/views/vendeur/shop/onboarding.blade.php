{{--
    resources/views/vendeur/shop/onboarding.blade.php
    Route     : GET /boutique/shops/create (boutique.shops.create) et GET /shop/create (shop.create)
                → ShopController@create → ShopOnboardingController@create
                POST /shop/demarrer → ShopOnboardingController@store → name('shop.onboard')
    Rôle      : Création de la boutique, une question par écran (façon Sellio) : bienvenue, nom, expérience,
                ventes, équipe, livraison, pays, WhatsApp, puis « Création en cours… » et le tableau de bord.
    Variables : $user, $country, $countries, $questions
--}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex">
<title>Créer ma boutique · Shopio</title>
<link rel="icon" href="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --ink: #111827; --ink-2: #6B7280; --ink-3: #9CA3AF; --line: #E5E7EB; --soft: #F9FAFB;
    --accent: #1F7CF6; --accent-dk: #1667D6; --accent-50: #EFF6FF; --err: #DC2626; --ok: #16A34A;
    --font: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
}
*, *::before, *::after { box-sizing: border-box; }
html, body { margin: 0; background: #fff; color: var(--ink); font-family: var(--font); -webkit-font-smoothing: antialiased; }
body { min-height: 100vh; min-height: 100dvh; display: flex; align-items: center; justify-content: center; padding: 24px 20px calc(24px + env(safe-area-inset-bottom, 0px)); }
.ob { width: 100%; max-width: 400px; }
.ob-step { display: none; }
.ob-step.is-on { display: block; animation: obIn .32s cubic-bezier(.22,1,.36,1); }
.ob-step.is-back { animation-name: obBack; }
@keyframes obIn { from { opacity: 0; transform: translateX(18px); } to { opacity: 1; transform: none; } }
@keyframes obBack { from { opacity: 0; transform: translateX(-18px); } to { opacity: 1; transform: none; } }
.ob-back { display: inline-grid; place-items: center; width: 36px; height: 36px; margin: 0 0 14px -8px; border: 0; border-radius: 10px; background: transparent; color: var(--ink-2); cursor: pointer; }
.ob-back:hover { background: var(--soft); color: var(--ink); }
.ob-logo { display: block; width: 48px; height: 48px; margin: 0 auto 16px; border-radius: 13px; }
.ob h1 { margin: 0; text-align: center; font-size: 23px; line-height: 1.25; font-weight: 800; letter-spacing: -.02em; outline: none; }
.ob-sub { margin: 6px 0 22px; text-align: center; color: var(--ink-2); font-size: 14px; }
.ob-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; min-height: 48px; padding: 0 18px; border: 0; border-radius: 12px; background: var(--accent); color: #fff; font: 700 15px var(--font); cursor: pointer; transition: background-color .15s, transform .1s; }
.ob-btn:hover { background: var(--accent-dk); }
.ob-btn:active { transform: scale(.985); }
.ob-btn:disabled { opacity: .6; cursor: default; }
.ob-link { display: block; margin: 14px auto 0; border: 0; background: none; color: var(--ink-2); font: 600 13px var(--font); cursor: pointer; text-decoration: underline; text-underline-offset: 3px; }
.ob-input { width: 100%; min-height: 50px; padding: 0 16px; border: 1.5px solid var(--line); border-radius: 12px; background: #fff; color: var(--ink); font: 600 16px var(--font); text-align: center; outline: none; transition: border-color .15s, box-shadow .15s; }
.ob-input:focus { border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-50); }
.ob-input.is-err { border-color: var(--err); box-shadow: 0 0 0 4px #FEF2F2; }
.ob-err { min-height: 18px; margin: 6px 0 8px; text-align: center; color: var(--err); font-size: 13px; font-weight: 600; }
.ob-hint { margin: -2px 0 10px; text-align: center; color: var(--ink-3); font-size: 12.5px; word-break: break-all; }
.ob-hint b { color: var(--ink-2); font-weight: 700; }
.ob-opts { display: grid; gap: 10px; }
.ob-opts.is-grid { grid-template-columns: 1fr 1fr; }
.ob-opt { display: flex; align-items: center; gap: 12px; width: 100%; min-height: 54px; padding: 12px 14px; border: 1.5px solid var(--line); border-radius: 12px; background: #fff; color: var(--ink); font: 600 14.5px var(--font); text-align: left; cursor: pointer; transition: border-color .15s, background-color .15s, transform .1s; }
.ob-opt:hover { border-color: #CBD5E1; background: var(--soft); }
.ob-opt:active { transform: scale(.99); }
.ob-opt[aria-pressed="true"] { border-color: var(--accent); background: var(--accent-50); }
.ob-opt svg { flex-shrink: 0; color: var(--ink-2); }
.ob-opt[aria-pressed="true"] svg { color: var(--accent); }
.ob-opts.is-grid .ob-opt { flex-direction: column; justify-content: center; gap: 8px; min-height: 96px; text-align: center; font-size: 13.5px; }
.ob-code { display: inline-grid; place-items: center; min-width: 34px; height: 24px; padding: 0 6px; border-radius: 6px; background: var(--soft); box-shadow: inset 0 0 0 1px var(--line); color: var(--ink-2); font-size: 11.5px; font-weight: 800; letter-spacing: .04em; }
.ob-opt[aria-pressed="true"] .ob-code { background: #fff; color: var(--accent); }
.ob-opt .ob-dial { margin-left: auto; color: var(--ink-3); font-size: 12.5px; font-weight: 600; }
.ob-search { width: 100%; min-height: 42px; margin-bottom: 10px; padding: 0 14px; border: 1.5px solid var(--line); border-radius: 10px; font: 500 14px var(--font); outline: none; }
.ob-search:focus { border-color: var(--accent); }
.ob-list { max-height: min(46vh, 330px); overflow-y: auto; padding: 2px 4px 2px 2px; margin: 0 -4px 16px -2px; scrollbar-width: thin; }
.ob-tel { display: flex; align-items: center; border: 1.5px solid var(--line); border-radius: 12px; background: #fff; transition: border-color .15s, box-shadow .15s; }
.ob-tel:focus-within { border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-50); }
.ob-tel.is-err { border-color: var(--err); box-shadow: 0 0 0 4px #FEF2F2; }
.ob-tel span { padding: 0 4px 0 16px; color: var(--ink-2); font-weight: 700; font-size: 16px; }
.ob-tel input { flex: 1; min-width: 0; min-height: 50px; padding: 0 16px 0 6px; border: 0; background: none; font: 600 16px var(--font); color: var(--ink); outline: none; }
.ob-dots { display: flex; justify-content: center; gap: 6px; margin-top: 26px; }
.ob-dots i { width: 6px; height: 6px; border-radius: 999px; background: var(--line); transition: width .25s, background-color .25s; }
.ob-dots i.is-done { background: #BFDBFE; }
.ob-dots i.is-on { width: 22px; background: var(--accent); }
/* Création en cours */
.ob-make h1 { display: flex; align-items: center; gap: 8px; justify-content: flex-start; text-align: left; font-size: 19px; }
.ob-make .ob-sub { text-align: left; margin-bottom: 16px; }
.ob-tasks { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
.ob-task { display: flex; align-items: center; gap: 10px; min-height: 46px; padding: 0 14px; border-radius: 11px; border: 1px solid transparent; color: var(--ink-3); font-size: 14px; font-weight: 600; transition: background-color .2s, color .2s, border-color .2s; }
.ob-task .st { display: grid; place-items: center; width: 20px; height: 20px; flex-shrink: 0; border-radius: 999px; box-shadow: inset 0 0 0 1.5px var(--line); }
.ob-task.is-run { color: var(--ink); border-color: var(--line); }
.ob-task.is-run .st { box-shadow: inset 0 0 0 2px var(--accent-50); border: 2px solid transparent; border-top-color: var(--accent); animation: obSpin .8s linear infinite; }
.ob-task.is-done { color: var(--ink); background: #F0FDF4; }
.ob-task.is-done .st { box-shadow: none; background: var(--ok); color: #fff; }
@keyframes obSpin { to { transform: rotate(360deg); } }
.ob-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
:focus-visible { outline: 3px solid #93C5FD; outline-offset: 2px; }
@media (prefers-reduced-motion: reduce) { .ob-step.is-on, .ob-task.is-run .st { animation: none; } .ob-dots i { transition: none; } }
</style>
</head>
<body>
@php
    $ico = [
        'sprout'   => 'M12 20v-8M12 12c0-3.5-2.5-6-7-6 0 4 2.5 6 7 6zM12 14c0-3 2-5.5 6.5-5.5 0 3.5-2 5.5-6.5 5.5',
        'phone'    => 'M8 3h8a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM11 18h2',
        'store'    => 'M4 9l1.5-5h13L20 9M4 9v11h16V9M4 9c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3M10 20v-5h4v5',
        'seed'     => 'M12 21c-4 0-7-3-7-7 0-5 7-11 7-11s7 6 7 11c0 4-3 7-7 7z',
        'bar1'     => 'M5 20V15M10 20v-2M15 20v-1M19 20v-1M3 20h18',
        'bar2'     => 'M5 20v-6M10 20v-9M15 20v-3M19 20v-2M3 20h18',
        'bar3'     => 'M5 20v-7M10 20v-11M15 20v-8M19 20v-5M3 20h18',
        'rocket'   => 'M5 15c-1.5 1.5-2 4-2 6 2 0 4.5-.5 6-2M9 15l-3-3c1-4 4.5-8 12-9-1 7.5-5 11-9 12zM15 9.5h.01',
        'user'     => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21c1-4 4-6 8-6s7 2 8 6',
        'users'    => 'M9 11.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM3 20c.8-3.4 3.2-5.5 6-5.5s5.2 2.1 6 5.5M16 4.5a3.5 3.5 0 0 1 0 7M21 20c-.5-2.4-1.8-4.1-3.6-4.9',
        'building' => 'M5 21V4h10v17M15 9h4v12M8 8h4M8 12h4M8 16h4M3 21h18',
        'help'     => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .8-1 1.5v.7M12 17h.01',
        'truck'    => 'M3 6h11v10H3zM14 10h4l3 3v3h-7M7 19.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM17.5 19.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z',
        'bike'     => 'M6 19.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM18 19.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM8.5 17h7M15 7h2.5l2.5 6.5M6 14.5 9 9h5l1 5',
        'both'     => 'M4 7h9v8H4zM13 10h3l2.5 2.5V15H13M6.5 18a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM16 18a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z',
    ];
    $svg = fn ($k, $s = 22) => '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.$ico[$k].'"/></svg>';
    $logo = \App\Support\Assets::v('images/shopio-logo-192.png');
    $back = '<button type="button" class="ob-back" data-back aria-label="Étape précédente"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg></button>';
    $choice = [
        'experience' => ['Ton expérience e-commerce', 'Pour mieux t’accompagner', false],
        'sales'      => ['Tes ventes chaque mois', 'Pour adapter Shopio à ton activité', false],
        'team'       => ['La taille de ton équipe', 'Combien de personnes travaillent avec toi', true],
        'delivery'   => ['Comment tu gères la livraison ?', 'On adapte les options à ta situation', false],
    ];
    $first = trim(explode(' ', trim((string) $user->name))[0] ?? '');
@endphp
<main class="ob" id="ob" aria-live="polite">

    {{-- 1. Bienvenue --}}
    <section class="ob-step is-on" data-step="welcome" aria-labelledby="obT1">
        <img class="ob-logo" src="{{ $logo }}" alt="Shopio" width="48" height="48">
        <h1 id="obT1" tabindex="-1">Bienvenue sur Shopio{{ $first !== '' ? ', ' . $first : '' }}</h1>
        <p class="ob-sub">Crée ta boutique en ligne et commence à vendre en quelques minutes.</p>
        <button type="button" class="ob-btn" data-next>Commencer</button>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="ob-link">Se déconnecter</button></form>
    </section>

    {{-- 2. Nom --}}
    <section class="ob-step" data-step="name" aria-labelledby="obT2">
        {!! $back !!}
        <img class="ob-logo" src="{{ $logo }}" alt="" width="48" height="48">
        <h1 id="obT2" tabindex="-1">Comment s’appelle ta boutique ?</h1>
        <p class="ob-sub">Tu pourras le changer plus tard</p>
        <label class="ob-sr" for="obName">Nom de la boutique</label>
        <input class="ob-input" id="obName" maxlength="60" autocomplete="organization" placeholder="Ex : Maison Kadi" aria-describedby="obNameErr obSlug">
        <p class="ob-err" id="obNameErr" role="alert"></p>
        <p class="ob-hint" id="obSlug" hidden>Ton adresse : <b>{{ request()->getHost() }}/s/<span id="obSlugV"></span></b></p>
        <button type="button" class="ob-btn" data-next>Continuer <span aria-hidden="true">→</span></button>
    </section>

    {{-- 3 à 6. Questions à choix --}}
    @foreach($choice as $field => [$title, $sub, $grid])
    <section class="ob-step" data-step="{{ $field }}" aria-labelledby="obT{{ $field }}">
        {!! $back !!}
        <h1 id="obT{{ $field }}" tabindex="-1">{{ $title }}</h1>
        <p class="ob-sub">{{ $sub }}</p>
        <div class="ob-opts {{ $grid ? 'is-grid' : '' }}" role="group" aria-labelledby="obT{{ $field }}">
            @foreach($questions[$field] as $k => [$label, $icon])
                <button type="button" class="ob-opt" data-pick="{{ $field }}" data-value="{{ $k }}" aria-pressed="false">{!! $svg($icon) !!}<span>{{ $label }}</span></button>
            @endforeach
        </div>
    </section>
    @endforeach

    {{-- 7. Pays --}}
    <section class="ob-step" data-step="country" aria-labelledby="obT7">
        {!! $back !!}
        <h1 id="obT7" tabindex="-1">Tu es basé où ?</h1>
        <p class="ob-sub">Pour la devise et le format du numéro</p>
        <label class="ob-sr" for="obCSearch">Chercher un pays</label>
        <input class="ob-search" id="obCSearch" type="search" placeholder="Chercher un pays" autocomplete="off">
        <div class="ob-list ob-opts" id="obCList" role="group" aria-labelledby="obT7">
            @foreach($countries as $code => [$cname, $dial])
                <button type="button" class="ob-opt" data-pick="country" data-value="{{ $code }}" data-dial="{{ $dial }}" data-name="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($cname)) }}" aria-pressed="{{ $code === $country ? 'true' : 'false' }}">
                    <span class="ob-code">{{ $code }}</span><span>{{ $cname }}</span><span class="ob-dial">+{{ $dial }}</span>
                </button>
            @endforeach
        </div>
        <button type="button" class="ob-btn" data-next>Continuer <span aria-hidden="true">→</span></button>
    </section>

    {{-- 8. WhatsApp --}}
    <section class="ob-step" data-step="whatsapp" aria-labelledby="obT8">
        {!! $back !!}
        <h1 id="obT8" tabindex="-1">Ton numéro WhatsApp</h1>
        <p class="ob-sub">Tes clients pourront t’écrire sur WhatsApp</p>
        <label class="ob-sr" for="obWa">Numéro WhatsApp</label>
        <div class="ob-tel" id="obTel"><span id="obDial">+224</span><input id="obWa" type="tel" inputmode="tel" autocomplete="tel-national" maxlength="20" placeholder="622 12 34 56" aria-describedby="obWaErr"></div>
        <p class="ob-err" id="obWaErr" role="alert"></p>
        <button type="button" class="ob-btn" id="obCreate">Créer ma boutique <span aria-hidden="true">→</span></button>
    </section>

    {{-- Création en cours --}}
    <section class="ob-step ob-make" data-step="make" aria-labelledby="obTm">
        <h1 id="obTm" tabindex="-1"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/></svg>Création en cours…</h1>
        <p class="ob-sub">Ça prend quelques secondes, on met tout en place pour toi.</p>
        <ol class="ob-tasks">
            <li class="ob-task"><span class="st" aria-hidden="true"></span>Réservation de ton adresse</li>
            <li class="ob-task"><span class="st" aria-hidden="true"></span>Création de la boutique</li>
            <li class="ob-task"><span class="st" aria-hidden="true"></span>Mise en place de ta vitrine</li>
            <li class="ob-task"><span class="st" aria-hidden="true"></span>Préparation du tableau de bord</li>
        </ol>
    </section>

    <div class="ob-dots" id="obDots" aria-hidden="true"></div>
    <p class="ob-sr" id="obProgress"></p>
</main>

<script>
(function () {
    const $ = id => document.getElementById(id);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const URL_STORE = @json(route('shop.onboard'));
    const steps = [...document.querySelectorAll('.ob-step')];
    const order = steps.map(s => s.dataset.step);
    const QUESTIONS = 8; // de « Bienvenue » à « WhatsApp »
    const data = { country: @json($country) };
    let cur = 0, busy = false;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Points de progression
    const dots = $('obDots');
    for (let i = 0; i < QUESTIONS; i++) dots.appendChild(document.createElement('i'));

    function show(i, back) {
        steps[cur].classList.remove('is-on', 'is-back');
        cur = i;
        const s = steps[cur];
        s.classList.add('is-on'); s.classList.toggle('is-back', !!back);
        [...dots.children].forEach((d, k) => { d.className = k === cur ? 'is-on' : (k < cur ? 'is-done' : ''); });
        dots.hidden = order[cur] === 'make';
        $('obProgress').textContent = cur < QUESTIONS ? 'Étape ' + (cur + 1) + ' sur ' + QUESTIONS : '';
        const field = s.querySelector('.ob-input, #obWa');
        const h = s.querySelector('h1');
        if (field && window.matchMedia('(hover: hover)').matches) field.focus(); else if (h && cur > 0) h.focus({ preventScroll: true });
        if (order[cur] === 'country') { const on = s.querySelector('[aria-pressed="true"]'); if (on) on.scrollIntoView({ block: 'center' }); }
        if (order[cur] === 'whatsapp') $('obDial').textContent = '+' + dialOf(data.country);
        window.scrollTo(0, 0);
    }
    const go = name => show(order.indexOf(name));
    function next() {
        if (busy) return;
        const step = order[cur];
        if (step === 'name') {
            const v = $('obName').value.trim();
            if (v.length < 2) return err('obName', 'obNameErr', 'Donne un nom à ta boutique.');
            data.name = v;
        }
        if (step === 'country' && !data.country) return;
        show(cur + 1);
    }
    function err(inputId, errId, msg) {
        const box = inputId === 'obWa' ? $('obTel') : $(inputId);
        box.classList.add('is-err'); $(errId).textContent = msg; $(inputId).focus();
    }
    function clearErr(inputId, errId) { (inputId === 'obWa' ? $('obTel') : $(inputId)).classList.remove('is-err'); $(errId).textContent = ''; }

    document.addEventListener('click', e => {
        if (e.target.closest('[data-next]')) next();
        else if (e.target.closest('[data-back]') && cur > 0) show(cur - 1, true);
        const pick = e.target.closest('[data-pick]');
        if (pick) {
            const f = pick.dataset.pick;
            data[f] = pick.dataset.value;
            pick.parentElement.querySelectorAll('[data-pick]').forEach(b => b.setAttribute('aria-pressed', b === pick ? 'true' : 'false'));
            if (f !== 'country') setTimeout(next, reduce ? 0 : 220); // une réponse = étape suivante
        }
    });

    // Nom : adresse prévue /s/…
    const slugify = v => v.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 40);
    $('obName').addEventListener('input', () => {
        clearErr('obName', 'obNameErr');
        const s = slugify($('obName').value);
        $('obSlug').hidden = s.length < 3; $('obSlugV').textContent = s;
    });
    $('obName').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); next(); } });

    // Pays : recherche
    $('obCSearch').addEventListener('input', () => {
        const q = $('obCSearch').value.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
        $('obCList').querySelectorAll('[data-pick]').forEach(b => { b.hidden = q !== '' && !b.dataset.name.includes(q) && !b.dataset.value.toLowerCase().startsWith(q); });
    });
    const dialOf = code => (document.querySelector('[data-pick="country"][data-value="' + code + '"]') || {}).dataset?.dial || '';

    // WhatsApp puis création
    $('obWa').addEventListener('input', () => clearErr('obWa', 'obWaErr'));
    $('obWa').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); create(); } });
    $('obCreate').addEventListener('click', create);

    const wait = ms => new Promise(r => setTimeout(r, reduce ? 0 : ms));
    async function create() {
        if (busy) return;
        const wa = $('obWa').value.replace(/[^\d+]/g, '');
        if (wa.replace(/\D/g, '').length < 7) return err('obWa', 'obWaErr', 'Entre ton numéro WhatsApp.');
        busy = true; $('obCreate').disabled = true;
        go('make');
        const tasks = [...document.querySelectorAll('.ob-task')];
        const run = i => { tasks[i].classList.add('is-run'); };
        const done = i => { tasks[i].classList.remove('is-run'); tasks[i].classList.add('is-done'); tasks[i].querySelector('.st').innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5 10 17 19 7"/></svg>'; };

        run(0);
        const req = fetch(URL_STORE, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(Object.assign({}, data, { whatsapp: wa })),
        }).then(async r => ({ ok: r.ok, status: r.status, body: await r.json().catch(() => ({})) }))
          .catch(() => ({ ok: false, status: 0, body: {} }));

        await wait(650); done(0); run(1);
        const res = await req;
        if (!res.ok) return fail(res);
        await wait(550); done(1); run(2);
        await wait(650); done(2); run(3);
        await wait(550); done(3);
        await wait(300);
        window.location.href = res.body.redirect;
    }
    function fail(res) {
        busy = false; $('obCreate').disabled = false;
        document.querySelectorAll('.ob-task').forEach(t => { t.className = 'ob-task'; t.querySelector('.st').innerHTML = ''; });
        const e = (res.body && res.body.errors) || {};
        const first = Object.keys(e)[0];
        const msg = first ? e[first][0] : (res.status === 419 ? 'La page a expiré. Recharge-la puis réessaie.' : 'La création n’a pas marché. Vérifie ta connexion puis réessaie.');
        if (first === 'name') { go('name'); err('obName', 'obNameErr', msg); return; }
        if (first && first !== 'whatsapp') { go(first in { experience: 1, sales: 1, team: 1, delivery: 1, country: 1 } ? first : 'whatsapp'); }
        if (!first || first === 'whatsapp') { go('whatsapp'); err('obWa', 'obWaErr', msg); }
    }

    show(0);
})();
</script>
</body>
</html>
