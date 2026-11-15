{{--
    resources/views/boutique/carts/index.blade.php
    Route     : GET /boutique/paniers-abandonnes → Boutique\AbandonedCartController@index → name('boutique.carts.index')
                (employés : employe.carts.index, si la boutique ouvre la page à ses closers)
    Rôle      : Commandes → « Paniers abandonnés » : chiffres (à rappeler, montant à récupérer, récupérés, partis sans
                numéro), onglets À rappeler / Injoignables / Pas intéressés / Récupérés, Appeler / WhatsApp, créer la commande.
    Variables : $shop, $tab, $counts, $stats, $carts, $settings, $isOwner, $communes, $devise
--}}
@extends('layouts.app')
@section('title', 'Paniers abandonnés · ' . $shop->name)
@php
    use App\Models\AbandonedCart as AC;
    $bodyClass = 'is-dashboard';
    $base = $isOwner ? 'boutique' : 'employe';
    $empty = [
        'a_rappeler' => ['Aucun client à rappeler', 'Dès qu’un acheteur laisse son numéro sans valider sa commande, il apparaît ici ' . \App\Support\OrderBoard::DELAYS[$settings['delay']] . ' plus tard.'],
        'injoignable' => ['Aucun client injoignable', 'Les clients qui ne répondent pas à votre appel se rangent ici. Vous pouvez les rappeler plus tard.'],
        'pas_interesse' => ['Aucun client pas intéressé', 'Les clients qui ne veulent plus commander se rangent ici.'],
        'recupere' => ['Aucun panier récupéré pour l’instant', 'Un panier devient « récupéré » quand le client finit par commander, seul ou avec votre aide.'],
    ];
    $cartsData = ['products' => $shop->products()->where('is_active', true)->pluck('name', 'id'), 'store' => $isOwner ? route('boutique.orders.store') : null];
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.ac { padding: 18px 28px 80px; max-width: 1240px; width: 100%; min-width: 0; }
.ac-top { display: flex; align-items: center; gap: 10px; padding: 12px 28px !important; position: sticky; top: 0; z-index: 30; }
.ac-tabs { display: flex; gap: 22px; border-bottom: 1px solid var(--border); margin-bottom: 18px; }
.ac-tabs a { padding: 10px 2px; font-weight: 700; font-size: 14px; color: var(--text-2); text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; }
.ac-tabs a[aria-current="page"] { color: var(--text); border-bottom-color: var(--text); }
.ac-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.ac-head h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; flex: 1; }
.ac-btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 38px; padding: 0 13px; border: 0; border-radius: 10px; font: 700 13px var(--font); cursor: pointer; text-decoration: none; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); }
.ac-btn:hover { box-shadow: inset 0 0 0 1px #9DB4D6; }
.ac-btn-main { background: var(--sv-btn); color: #fff; box-shadow: none; }
.ac-btn-wa { background: #25D366; color: #fff; box-shadow: none; }
.ac-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
.ac-stat { padding: 14px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); }
.ac-stat small { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-2); font-weight: 600; }
.ac-stat b { display: block; margin-top: 6px; font-size: clamp(18px, 2.2vw, 24px); white-space: nowrap; font-weight: 800; font-variant-numeric: tabular-nums; }
.ac-stat span { font-size: 12px; color: var(--text-2); }
.ac-chips { display: inline-flex; gap: 4px; padding: 4px; border-radius: 12px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); margin-bottom: 14px; flex-wrap: wrap; }
.ac-chips a { display: inline-flex; align-items: center; gap: 6px; min-height: 32px; padding: 0 12px; border-radius: 9px; font-size: 13px; font-weight: 700; color: var(--text-2); text-decoration: none; }
.ac-chips a[aria-current="page"] { background: var(--bg); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); }
.ac-list { display: grid; gap: 8px; }
.ac-card { display: grid; grid-template-columns: minmax(0, 1fr); gap: 10px 14px; align-items: center; padding: 14px 16px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); }
.ac-card b { font-size: 15px; }
.ac-card p { margin: 4px 0 0; font-size: 13px; color: var(--text-2); }
.ac-card .ac-total { font-weight: 800; color: #16A34A; }
.ac-acts { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-start; }
.ac-empty { display: grid; place-items: center; gap: 6px; text-align: center; padding: 54px 20px; border-radius: 16px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); color: var(--text-2); font-size: 13.5px; }
.ac-empty b { color: var(--text); font-size: 15px; }
.ac-off { margin-bottom: 14px; padding: 12px 14px; border-radius: 12px; background: #FEF3C7; color: #78350F; font-weight: 600; font-size: 13.5px; }
.ac-modal { position: fixed; inset: 0; z-index: 1100; display: grid; place-items: center; padding: 16px; background: rgba(11,18,32,.55); }
.ac-modal[hidden] { display: none; }
.ac-sheet { position: relative; width: min(460px, 100%); max-height: calc(100vh - 32px); overflow-y: auto; border-radius: 18px; background: var(--surface); padding: 20px; display: grid; gap: 12px; }
.ac-sheet h2 { margin: 0; font-size: 19px; }
.ac-input { width: 100%; min-height: 42px; padding: 9px 12px; border: 0; border-radius: 10px; background: var(--bg); color: var(--text); font: 500 14px var(--font); box-shadow: inset 0 0 0 1px var(--border); }
.ac-lbl { display: block; font-size: 12.5px; font-weight: 700; margin-bottom: 5px; color: var(--text-2); }
.ac-msg { font-size: 12.5px; font-weight: 700; color: #B91C1C; min-height: 1em; margin: 0; }
.ac-toast { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%); z-index: 1300; padding: 10px 16px; border-radius: 10px; background: #0B1E3D; color: #fff; font-weight: 700; font-size: 13.5px; }
@media (max-width: 900px) { .ac { padding: 14px 14px 80px; } .ac-top { padding: 10px 14px !important; } .ac-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="{{ $isOwner ? 'vs-wrap' : '' }}">
    @if($isOwner)@include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'orders'])@endif
    <main class="{{ $isOwner ? 'main vs-main' : '' }}">
        @if($isOwner)
        <div class="topbar ac-top">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
            <span style="font-weight:800">Commandes</span>
        </div>
        @endif
        <div class="ac">
            <nav class="ac-tabs" aria-label="Commandes">
                <a href="{{ $isOwner ? route('boutique.orders.index') : route('employe.orders.index') }}">Toutes les commandes</a>
                <a href="{{ route($base . '.carts.index') }}" aria-current="page">Paniers abandonnés</a>
            </nav>
            <div class="ac-head">
                <h1>Paniers abandonnés</h1>
                @if($isOwner)<a class="ac-btn" href="{{ route('boutique.carts.settings') }}">⚙️ Réglages</a>@endif
                <button type="button" class="ac-btn" id="acHow">ⓘ Comment ça marche ?</button>
            </div>
            @if(!$settings['enabled'])<p class="ac-off">La collecte des paniers abandonnés est coupée : aucun nouveau client n’est enregistré. @if($isOwner)<a href="{{ route('boutique.carts.settings') }}">La réactiver</a>@endif</p>@endif
            <p class="ac-off" id="acHowText" hidden style="background:#EAF2FF;color:#0B1E3D">Quand un client commence le formulaire de commande (page produit ou panier) et laisse son numéro sans valider, Shopio enregistre ce qu’il a rempli. Après {{ \App\Support\OrderBoard::DELAYS[$settings['delay']] }} sans activité, il apparaît « À rappeler ». S’il finit par commander, il passe tout seul dans « Récupérés ». Le formulaire prévient le client que son numéro sert à finaliser sa commande.</p>

            <div class="ac-stats">
                <div class="ac-stat"><small>🛒 À rappeler</small><b>{{ $stats['waiting'] }}</b><span>clients partis sans commander</span></div>
                <div class="ac-stat"><small>💰 Montant à récupérer</small><b>{{ $stats['amount'] }}</b><span>total des paniers à rappeler</span></div>
                <div class="ac-stat"><small>📈 Récupérés</small><b>{{ $stats['recovered'] }}</b><span>{{ $stats['rate'] }} % des clients partis</span></div>
                <div class="ac-stat"><small>👤 Partis sans numéro</small><b>{{ $stats['nophone'] }}</b><span>impossibles à rappeler</span></div>
            </div>

            <nav class="ac-chips" aria-label="Paniers">
                @foreach(AC::STATES as $k => $l)<a href="{{ route($base . '.carts.index', ['onglet' => $k]) }}" @if($tab === $k) aria-current="page" @endif>{{ $l }} <small>{{ $counts[$k] }}</small></a>@endforeach
            </nav>

            <div class="ac-list" id="acList">
                @forelse($carts as $c)
                <article class="ac-card" data-cart="{{ json_encode($c) }}">
                    <div>
                        <b>{{ $c['name'] }}</b> <span style="color:var(--text-2);font-size:13px">· {{ $c['phoneShow'] }} · {{ $c['ago'] }}</span>
                        <p>{{ $c['products'] }} · <span class="ac-total">{{ $c['total'] }}</span>@if($c['commune']) · {{ $c['commune'] }}@endif @if($c['landmark']) — {{ $c['landmark'] }}@endif</p>
                        @if($c['order'])<p>Devenu la commande <a href="{{ $isOwner ? route('boutique.orders.index', ['q' => $c['order']]) : '#' }}">n° {{ $c['order'] }}</a></p>@endif
                    </div>
                    <div class="ac-acts">
                        @if($c['tel'])<a class="ac-btn ac-btn-main" href="{{ $c['tel'] }}">📞 Appeler</a><a class="ac-btn ac-btn-wa" href="{{ $c['wa'] }}" target="_blank" rel="noopener">WhatsApp</a>@endif
                        @if($c['state'] !== 'recupere')
                        @if($isOwner)<button type="button" class="ac-btn" data-convert>✅ Créer la commande</button>@endif
                        @foreach(['a_rappeler' => 'À rappeler', 'injoignable' => 'Injoignable', 'pas_interesse' => 'Pas intéressé'] as $k => $l)
                        @if($c['state'] !== $k)<button type="button" class="ac-btn" data-state="{{ $k }}">{{ $l }}</button>@endif
                        @endforeach
                        @endif
                    </div>
                </article>
                @empty
                <div class="ac-empty"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg><b>{{ $empty[$tab][0] }}</b><span>{{ $empty[$tab][1] }}</span></div>
                @endforelse
            </div>
        </div>
    </main>
</div>

@if($isOwner)
<div class="ac-modal" id="acConvert" hidden role="dialog" aria-modal="true" aria-labelledby="acConvH">
    <form class="ac-sheet" id="acConvForm" novalidate>
        <h2 id="acConvH">Créer la commande</h2>
        <p style="margin:0;color:var(--text-2);font-size:13px" id="acConvSum"></p>
        <div><label class="ac-lbl" for="acName">Nom du client</label><input class="ac-input" id="acName" maxlength="120"></div>
        <div><label class="ac-lbl" for="acCommune">Commune</label><select class="ac-input" id="acCommune"><option value="">Choisir…</option>@foreach($communes as $cm)<option>{{ $cm }}</option>@endforeach</select></div>
        <div><label class="ac-lbl" for="acLandmark">Adresse</label><input class="ac-input" id="acLandmark" maxlength="120" placeholder="Quartier, rue, repère"></div>
        <p class="ac-msg" id="acMsg" role="alert"></p>
        <div style="display:flex;gap:8px"><button type="button" class="ac-btn" style="flex:1" data-close>Annuler</button><button type="submit" class="ac-btn ac-btn-main" style="flex:1" id="acSend">Créer la commande</button></div>
    </form>
</div>
@endif
<div class="ac-toast" id="acToast" hidden role="status"></div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const G = @json($cartsData);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    // Fenêtres et messages au niveau de la page (pas dans un conteneur animé) : fond sombre sur tout l'écran
    document.querySelectorAll('.ac-modal, #acToast').forEach(el => document.body.appendChild(el));
    const post = async (url, body) => { const r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify(body) }); return { ok: r.ok, j: await r.json().catch(() => ({})) }; };
    const toast = t => { const el = $('acToast'); el.textContent = t; el.hidden = false; setTimeout(() => { el.hidden = true; }, 2200); };
    $('acHow').addEventListener('click', () => { $('acHowText').hidden = !$('acHowText').hidden; });
    let cur = null;
    $('acList').addEventListener('click', async e => {
        const card = e.target.closest('[data-cart]'); if (!card) return;
        const c = JSON.parse(card.dataset.cart);
        const s = e.target.closest('[data-state]');
        if (s) {
            s.disabled = true;
            const r = await post(c.urls.state, { state: s.dataset.state });
            if (!r.ok) { s.disabled = false; toast(r.j.message || 'Action impossible.'); return; }
            card.style.transition = 'opacity .3s'; card.style.opacity = '0'; setTimeout(() => card.remove(), 300);
            toast('Panier rangé dans « ' + s.textContent + ' »');
            return;
        }
        if (e.target.closest('[data-convert]') && $('acConvert')) {
            cur = { c: c, card: card };
            $('acConvSum').textContent = c.products + ' · ' + c.total + ' · ' + c.phoneShow;
            $('acName').value = c.name === 'Client sans nom' ? '' : c.name; $('acCommune').value = c.commune || ''; $('acLandmark').value = c.landmark || ''; $('acMsg').textContent = '';
            $('acConvert').hidden = false; $('acName').focus();
        }
    });
    if ($('acConvert')) {
        $('acConvert').addEventListener('click', e => { if (e.target === $('acConvert') || e.target.closest('[data-close]')) $('acConvert').hidden = true; });
        $('acConvForm').addEventListener('submit', async e => {
            e.preventDefault();
            if (!cur) return;
            const b = $('acSend'); b.disabled = true;
            const r = await post(G.store, { name: $('acName').value.trim(), phone: cur.c.phone, commune: $('acCommune').value, landmark: $('acLandmark').value.trim(), items: (cur.c.items || []).map(i => ({ id: i.id, qty: i.qty })), cart: cur.c.id });
            b.disabled = false;
            if (!r.ok) { $('acMsg').textContent = (r.j.errors && Object.values(r.j.errors)[0][0]) || r.j.message || 'La commande n’a pas pu être créée.'; return; }
            $('acConvert').hidden = true; cur.card.remove(); toast('Commande n° ' + r.j.id + ' créée : le panier est récupéré');
        });
    }
})();
</script>
@endpush
