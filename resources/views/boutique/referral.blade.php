{{--
    resources/views/boutique/referral.blade.php
    Route     : GET /boutique/parrainage → ReferralController@index → name('boutique.referral')
    Rôle      : Le lien d'invitation du vendeur, comment ça marche, et ses filleuls (inscrits, passés au Pro).
    Variables : $shop, $link, $referred, $rewarded, $days, $trial, $waShare, $message
--}}
@extends('layouts.app')
@section('title', 'Parrainage · ' . $shop->name)
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.rf { padding: 18px 28px 80px; max-width: 860px; width: 100%; }
.rf h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; }
.rf-sub { margin: 4px 0 16px; color: var(--text-2); font-size: 14px; }
.rf-hero { padding: 22px; border-radius: 18px; background: var(--surface); box-shadow: inset 0 0 0 2px var(--sv-blue, #1F7CF6); display: grid; gap: 14px; margin-bottom: 14px; }
.rf-hero h2 { margin: 0; font-size: 20px; letter-spacing: -.02em; }
.rf-hero p { margin: 0; color: var(--text-2); font-size: 14px; }
.rf-link { display: flex; gap: 8px; flex-wrap: wrap; }
.rf-link input { flex: 1; min-width: 220px; min-height: 44px; padding: 0 14px; border: 0; border-radius: 11px; background: var(--bg); color: var(--text); font: 700 14px var(--font); box-shadow: inset 0 0 0 1px var(--border); }
.rf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 44px; padding: 0 16px; border: 0; border-radius: 11px; font: 700 14px var(--font); cursor: pointer; text-decoration: none; background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); white-space: nowrap; }
.rf-btn.is-main { background: var(--sv-btn, #1F7CF6); color: #fff; box-shadow: none; }
.rf-steps { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
.rf-step { padding: 16px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); }
.rf-step b { display: grid; place-items: center; width: 28px; height: 28px; margin-bottom: 10px; border-radius: 999px; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); font-size: 13px; }
.rf-step strong { display: block; font-size: 14.5px; margin-bottom: 4px; }
.rf-step span { color: var(--text-2); font-size: 13px; }
.rf-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
.rf-stat { padding: 14px 16px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); }
.rf-stat small { display: block; color: var(--text-2); font-size: 12px; font-weight: 700; }
.rf-stat b { font-size: 24px; letter-spacing: -.02em; }
.rf-card { border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); overflow: hidden; }
.rf-card h2 { margin: 0; padding: 14px 16px; font-size: 15px; border-bottom: 1px solid var(--border); }
.rf-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 13.5px; }
.rf-row:last-child { border-bottom: 0; }
.rf-row small { display: block; color: var(--text-2); font-size: 12px; }
.rf-pill { padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; background: var(--bg); color: var(--text-2); white-space: nowrap; }
.rf-pill.ok { background: #F0FDF4; color: #15803D; }
.rf-empty { padding: 26px 16px; text-align: center; color: var(--text-2); font-size: 13.5px; }
.rf-ok { color: #15803D; font-weight: 700; font-size: 13px; min-height: 18px; }
@media (max-width: 900px) { .rf { padding: 14px 14px 80px; } .rf-steps { grid-template-columns: 1fr; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'referral'])
    <main class="main vs-main">
        <div class="rf">
            <button type="button" class="vs-burger" data-vs-open aria-label="Ouvrir le menu" style="margin-bottom:8px">☰</button>
            <h1>Parrainage</h1>
            <p class="rf-sub">Invite d’autres vendeurs sur Shopio. Vous gagnez chacun {{ $days }} jours de Plan Pro.</p>

            <section class="rf-hero" aria-labelledby="rfH">
                <h2 id="rfH">Ton lien d’invitation</h2>
                <p>Partage-le dans tes groupes WhatsApp, à tes fournisseurs ou à des amis qui vendent en ligne.</p>
                <div class="rf-link">
                    <label for="rfLink" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Ton lien d’invitation</label>
                    <input id="rfLink" value="{{ $link }}" readonly>
                    <button type="button" class="rf-btn" id="rfCopy">Copier le lien</button>
                    <a class="rf-btn is-main" href="{{ $waShare }}" target="_blank" rel="noopener">Partager sur WhatsApp</a>
                </div>
                <p class="rf-ok" id="rfOk" role="status"></p>
            </section>

            <div class="rf-steps">
                <div class="rf-step"><b>1</b><strong>Ton ami crée sa boutique</strong><span>avec ton lien, dans les 30 jours après l’avoir ouvert.</span></div>
                <div class="rf-step"><b>2</b><strong>Il reçoit {{ $days }} jours de Pro en plus</strong><span>en plus des {{ $trial }} jours d’essai offerts à toute nouvelle boutique.</span></div>
                <div class="rf-step"><b>3</b><strong>Tu reçois {{ $days }} jours de Pro</strong><span>quand sa boutique paie son premier abonnement Pro. Ils s’ajoutent à ton abonnement en cours.</span></div>
            </div>

            <div class="rf-stats">
                <div class="rf-stat"><small>Boutiques invitées</small><b>{{ $referred->count() }}</b></div>
                <div class="rf-stat"><small>Passées au Pro</small><b>{{ $rewarded }}</b></div>
                <div class="rf-stat"><small>Jours de Pro gagnés</small><b>{{ $rewarded * $days }}</b></div>
            </div>

            <section class="rf-card" aria-labelledby="rfLH">
                <h2 id="rfLH">Tes filleuls</h2>
                @forelse($referred as $r)
                    <div class="rf-row">
                        <span><b>{{ $r->name }}</b><small>Inscrite le {{ $r->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}</small></span>
                        @if($r->referral_rewarded_at)
                            <span class="rf-pill ok">A payé · +{{ $days }} jours pour toi</span>
                        @else
                            <span class="rf-pill">En essai · pas encore payé</span>
                        @endif
                    </div>
                @empty
                    <p class="rf-empty">Personne n’a encore créé sa boutique avec ton lien.</p>
                @endforelse
            </section>
        </div>
    </main>
</div>
<script>
document.getElementById('rfCopy').addEventListener('click', async () => {
    const i = document.getElementById('rfLink'), ok = document.getElementById('rfOk');
    try { await navigator.clipboard.writeText(i.value); } catch (e) { i.select(); document.execCommand && document.execCommand('copy'); }
    ok.textContent = 'Lien copié ✓';
    setTimeout(() => { ok.textContent = ''; }, 2500);
});
</script>
@endsection
