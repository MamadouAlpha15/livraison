{{--
    resources/views/boutique/testimonial.blade.php
    Route     : GET/PUT/DELETE /boutique/avis-shopio → Boutique\TestimonialController → name('boutique.testimonial.*')
    Rôle      : Le vendeur écrit son avis sur Shopio. Après validation par l'équipe Shopio, il apparaît sur la
                page d'accueil avec le nom de la boutique, un lien vers sa vitrine et son vrai nombre de commandes livrées.
    Variables : $shop, $testimonial (ou null), $delivered, $firstName
--}}
@extends('layouts.app')
@section('title', 'Ton avis sur Shopio · ' . $shop->name)
@php
    $bodyClass = 'is-dashboard';
    $t = $testimonial;
    $rating = (int) old('rating', $t->rating ?? 5);
    $stLabel = $t ? \App\Models\ShopTestimonial::STATUSES[$t->status] ?? $t->status : null;
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }
html, body { font-family: var(--font); background: var(--bg); color: var(--text); margin: 0; -webkit-font-smoothing: antialiased; }
.tm { padding: 18px 28px 80px; max-width: 760px; width: 100%; }
.tm-back { display: inline-grid; place-items: center; width: 34px; height: 34px; border-radius: 9px; color: var(--text-2); text-decoration: none; box-shadow: inset 0 0 0 1px var(--border); background: var(--surface); }
.tm-head { margin: 14px 0; }
.tm-head h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; }
.tm-head p { margin: 4px 0 0; color: var(--text-2); font-size: 14px; }
.tm-card { padding: 18px; border-radius: 14px; background: var(--surface); box-shadow: inset 0 0 0 1px var(--border); margin-bottom: 12px; display: grid; gap: 14px; }
.tm-card h2 { margin: 0; font-size: 15px; }
.tm-lbl { display: block; font-weight: 700; font-size: 13.5px; margin-bottom: 6px; }
.tm-hint { display: block; color: var(--text-2); font-size: 12px; margin-top: 4px; }
.tm-in, .tm-area { width: 100%; min-height: 42px; padding: 9px 12px; border: 0; border-radius: 10px; background: var(--bg); color: var(--text); font: 600 14px var(--font); box-shadow: inset 0 0 0 1px var(--border); }
.tm-area { min-height: 120px; font-weight: 500; line-height: 1.5; resize: vertical; }
.tm-in:focus, .tm-area:focus { outline: none; box-shadow: inset 0 0 0 2px var(--sv-blue, #1F7CF6); }
.tm-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.tm-stars { display: flex; gap: 4px; flex-direction: row-reverse; justify-content: flex-end; }
.tm-stars input { position: absolute; opacity: 0; pointer-events: none; }
.tm-stars label { font-size: 30px; line-height: 1; color: #CBD5E1; cursor: pointer; transition: color .15s, transform .1s; }
.tm-stars label:hover, .tm-stars label:hover ~ label, .tm-stars input:checked ~ label { color: #F59E0B; }
.tm-stars input:focus-visible + label { outline: 3px solid #93C5FD; outline-offset: 2px; border-radius: 6px; }
.tm-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13.5px; font-weight: 600; color: var(--text-2); }
.tm-check input { width: 18px; height: 18px; margin-top: 2px; accent-color: var(--sv-btn, #1F7CF6); flex-shrink: 0; }
.tm-acts { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.tm-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 18px; border: 0; border-radius: 10px; font: 700 14px var(--font); cursor: pointer; background: var(--sv-btn, #1F7CF6); color: #fff; text-decoration: none; }
.tm-btn.is-line { background: var(--surface); color: var(--text); box-shadow: inset 0 0 0 1px var(--border); }
.tm-flash { padding: 12px 14px; border-radius: 12px; font-weight: 700; font-size: 13.5px; margin-bottom: 12px; }
.tm-flash.is-ok { background: var(--sv-ok50, #F0FDF4); color: var(--sv-ok, #15803D); } .tm-flash.is-err { background: var(--sv-err50, #FEF2F2); color: var(--sv-err, #DC2626); }
.tm-status { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 12px; background: var(--bg); box-shadow: inset 0 0 0 1px var(--border); font-size: 13.5px; }
.tm-pill { padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; }
.tm-pill.pending { background: #FFFBEB; color: #B45309; } .tm-pill.approved { background: #F0FDF4; color: #15803D; } .tm-pill.rejected { background: #FEF2F2; color: #DC2626; }
.tm-prev { padding: 18px; border-radius: 16px; background: #fff; box-shadow: inset 0 0 0 1px var(--border); }
.tm-prev q { display: block; margin: 8px 0 12px; font-size: 15px; line-height: 1.55; quotes: '« ' ' »'; color: #0F172A; }
.tm-prev .who { font-size: 13px; color: #475569; } .tm-prev .who b { color: #0F172A; }
.tm-prev .st { color: #F59E0B; letter-spacing: 2px; }
@media (max-width: 900px) { .tm { padding: 14px 14px 80px; } .tm-row { grid-template-columns: 1fr; } }
</style>
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<div class="vs-wrap">
    @include('boutique._partials.vendor-sidebar', ['shop' => $shop, 'active' => 'dash'])
    <main class="main vs-main">
        <div class="tm">
            <a class="tm-back" href="{{ route('boutique.dashboard') }}" aria-label="Retour au tableau de bord">‹</a>
            <div class="tm-head">
                <h1>Ton avis sur Shopio</h1>
                <p>Raconte en quelques mots ce que Shopio change pour ta boutique. Après validation par l’équipe Shopio, ton avis peut apparaître sur la page d’accueil.</p>
            </div>
            @if(session('success'))<p class="tm-flash is-ok" role="status">{{ session('success') }}</p>@endif
            @if($errors->any())<p class="tm-flash is-err" role="alert">{{ $errors->first() }}</p>@endif

            @if($t)
            <div class="tm-status" style="margin-bottom:12px">
                <span class="tm-pill {{ $t->status }}">{{ $stLabel }}</span>
                <span>
                    @if($t->status === 'approved') Ton avis est visible sur la page d’accueil de Shopio.
                    @elseif($t->status === 'rejected') Ton avis n’a pas été retenu. Tu peux le modifier et l’envoyer à nouveau.
                    @else L’équipe Shopio va le relire. Modifier ton avis le remet en attente.
                    @endif
                </span>
            </div>
            @endif

            <form class="tm-card" method="POST" action="{{ route('boutique.testimonial.update') }}">
                @csrf @method('PUT')
                <div>
                    <span class="tm-lbl" id="tmRateL">Ta note</span>
                    <div class="tm-stars" role="radiogroup" aria-labelledby="tmRateL">
                        @for($n = 5; $n >= 1; $n--)
                        <input type="radio" name="rating" id="tmR{{ $n }}" value="{{ $n }}" @checked($rating === $n)><label for="tmR{{ $n }}" aria-label="{{ $n }} étoile{{ $n > 1 ? 's' : '' }}">★</label>
                        @endfor
                    </div>
                </div>
                <div>
                    <label class="tm-lbl" for="tmText">Ton avis</label>
                    <textarea class="tm-area" id="tmText" name="text" maxlength="400" required placeholder="Ex. : Avant, mes commandes se perdaient dans WhatsApp. Maintenant je les vois toutes au même endroit et je rappelle les clients qui hésitent.">{{ old('text', $t->text ?? '') }}</textarea>
                    <span class="tm-hint"><span id="tmCount">0</span> / 400 caractères · 20 minimum</span>
                </div>
                <div class="tm-row">
                    <div><label class="tm-lbl" for="tmAuthor">Nom affiché</label><input class="tm-in" id="tmAuthor" name="author" maxlength="60" required value="{{ old('author', $t->author ?? $firstName) }}"><span class="tm-hint">Ton prénom suffit.</span></div>
                    <div><label class="tm-lbl" for="tmRole">Ton rôle (facultatif)</label><input class="tm-in" id="tmRole" name="author_role" maxlength="60" placeholder="Ex. : Fondatrice" value="{{ old('author_role', $t->author_role ?? '') }}"></div>
                </div>
                <label class="tm-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent', $t ? true : false))>
                    <span>J’accepte que cet avis, mon nom affiché, le nom de ma boutique avec un lien vers sa vitrine{{ $delivered > 0 ? ' et son nombre de commandes livrées (' . $delivered . ' aujourd’hui)' : '' }} soient montrés sur la page d’accueil de Shopio.</span></label>
                <div class="tm-acts">
                    <button type="submit" class="tm-btn">{{ $t ? 'Envoyer la nouvelle version' : 'Envoyer mon avis' }}</button>
                </div>
            </form>
            @if($t)
            <form method="POST" action="{{ route('boutique.testimonial.destroy') }}" onsubmit="return confirm('Retirer ton avis ? Il ne sera plus affiché.')">
                @csrf @method('DELETE')
                <button type="submit" class="tm-btn is-line">Retirer mon avis</button>
            </form>
            @endif

            <section class="tm-card" style="margin-top:12px" aria-labelledby="tmPrevH">
                <h2 id="tmPrevH">Aperçu sur la page d’accueil</h2>
                <div class="tm-prev">
                    <span class="st" id="tmPStars" aria-hidden="true">★★★★★</span>
                    <q id="tmPText">{{ old('text', $t->text ?? 'Ton avis apparaîtra ici.') }}</q>
                    <div class="who"><b id="tmPAuthor">{{ old('author', $t->author ?? $firstName) }}</b><span id="tmPRole">{{ ($r = old('author_role', $t->author_role ?? '')) ? ', ' . $r : '' }}</span> · {{ $shop->name }}@if($delivered > 0) · {{ $delivered }} commande{{ $delivered > 1 ? 's' : '' }} livrée{{ $delivered > 1 ? 's' : '' }}@endif</div>
                </div>
            </section>
        </div>
    </main>
</div>
<script>
(function () {
    const $ = id => document.getElementById(id);
    const sync = () => {
        const r = +(document.querySelector('input[name=rating]:checked') || { value: 5 }).value;
        $('tmPStars').textContent = '★'.repeat(r) + '☆'.repeat(5 - r);
        $('tmPText').textContent = $('tmText').value.trim() || 'Ton avis apparaîtra ici.';
        $('tmPAuthor').textContent = $('tmAuthor').value.trim();
        $('tmPRole').textContent = $('tmRole').value.trim() ? ', ' + $('tmRole').value.trim() : '';
        $('tmCount').textContent = $('tmText').value.length;
    };
    document.querySelector('.tm-card').addEventListener('input', sync);
    document.querySelector('.tm-card').addEventListener('change', sync);
    sync();
})();
</script>
@endsection
