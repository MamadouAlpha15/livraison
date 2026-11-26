{{--
    resources/views/academy/index.blade.php
    Route     : GET /academie → AcademyController@index → name('academy.index')
    Rôle      : Liste des guides de l'Académie Shopio, et le groupe WhatsApp des vendeurs s'il est configuré.
    Variables : $guides (App\Support\Academy::GUIDES), $community (lien ou null)
--}}
@extends('site.layout')
@section('title', 'Académie : apprendre à vendre en ligne')
@section('description', 'Guides gratuits et pratiques pour vendre en ligne en Afrique : trouver un produit, photos au téléphone, page produit, publicité Facebook, confirmer les commandes, calculer son bénéfice.')
@push('styles')
<style>
.ac-hero { padding: 56px 0 30px; text-align: center; background: radial-gradient(900px 360px at 50% -20%, var(--accent-50), transparent 70%); }
.ac-kick { display: inline-block; margin-bottom: 10px; color: var(--accent); font-weight: 800; font-size: 13px; letter-spacing: .08em; text-transform: uppercase; }
.ac-hero h1 { margin: 0 auto 12px; max-width: 760px; font-size: clamp(34px, 5vw, 54px); line-height: 1.06; font-weight: 600; letter-spacing: -.045em; text-wrap: balance; }
.ac-hero p { margin: 0 auto; max-width: 600px; color: var(--ink-2); font-size: 17px; }
.ac-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-top: 30px; }
.ac-card { display: flex; flex-direction: column; gap: 10px; padding: 22px; border-radius: 18px; background: #fff; box-shadow: inset 0 0 0 1px var(--line); text-decoration: none; transition: box-shadow .2s, transform .2s; }
.ac-card:hover { transform: translateY(-2px); box-shadow: 0 14px 30px -16px rgba(15,23,42,.3), inset 0 0 0 1px var(--line); }
.ac-card .i { width: 42px; height: 42px; border-radius: 12px; background: var(--soft-2); display: grid; place-items: center; color: var(--ink); }
.ac-card h2 { margin: 0; font-size: 18px; font-weight: 600; letter-spacing: -.02em; line-height: 1.25; }
.ac-card p { margin: 0; color: var(--ink-2); font-size: 14.5px; flex: 1; }
.ac-card small { color: var(--ink-3); font-weight: 700; font-size: 12.5px; }
.ac-comm { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 22px; padding: 22px; border-radius: 18px; background: var(--ink); color: #fff; }
.ac-comm .i { width: 46px; height: 46px; border-radius: 999px; background: #16A34A; display: grid; place-items: center; flex-shrink: 0; }
.ac-comm div { flex: 1; min-width: 220px; } .ac-comm b { display: block; font-size: 17px; } .ac-comm span { color: #CBD5E1; font-size: 14.5px; }
.ac-comm .s-btn { background: #fff; color: var(--ink); }
@media (max-width: 900px) { .ac-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 600px) { .ac-grid { grid-template-columns: 1fr; } }
</style>
@endpush
@section('content')
@php
    // Icônes des guides de l'Académie (traits, couleur héritée)
    $acIco = [
        'spark'  => 'M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8zM19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z',
        'phone'  => 'M8 3h8a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM11 18h2',
        'layers' => 'M12 3 3 8l9 5 9-5zM3 13l9 5 9-5M3 17.5l9 5 9-5',
        'chart'  => 'M4 19h16M7 16V9M12 16V5M17 16v-4',
        'whats'  => 'M4 20l1.3-4A8 8 0 1 1 8 18.7zM9 9.5c0 3 2.5 5.5 5.5 5.5l1.2-1.2-2-1-1 .8a4 4 0 0 1-2.3-2.3l.8-1-1-2z',
        'cash'   => 'M3 6.5h18v11H3zM12 14.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zM6 9.5v.01M18 14.5v.01',
        'users'  => 'M9 11.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM3 20c.8-3.4 3.2-5.5 6-5.5s5.2 2.1 6 5.5M16 4.5a3.5 3.5 0 0 1 0 7M21 20c-.5-2.4-1.8-4.1-3.6-4.9',
    ];
    $acSvg = fn ($k, $s = 22) => '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="'.($acIco[$k] ?? $acIco['spark']).'"/></svg>';
@endphp
<section class="ac-hero">
    <div class="wrap">
        <span class="ac-kick">Académie Shopio</span>
        <h1>Apprends à vendre en ligne, pas à pas</h1>
        <p>Des guides courts et gratuits, écrits pour les vendeurs qui livrent et encaissent à la livraison.</p>
    </div>
</section>
<div class="wrap">
    <div class="ac-grid">
        @foreach($guides as $slug => $g)
        <a class="ac-card" href="{{ route('academy.show', $slug) }}">
            <span class="i">{!! $acSvg($g['icon']) !!}</span>
            <h2>{{ $g['title'] }}</h2>
            <p>{{ $g['summary'] }}</p>
            <small>{{ $g['minutes'] }} min de lecture · {{ count($g['steps']) }} étapes</small>
        </a>
        @endforeach
    </div>
    @if($community)
    <div class="ac-comm">
        <span class="i">{!! $acSvg('whats', 22) !!}</span>
        <div><b>Rejoins les vendeurs Shopio sur WhatsApp</b><span>Pose tes questions, partage tes astuces et reçois les nouveautés.</span></div>
        <a class="s-btn" href="{{ $community }}" target="_blank" rel="noopener">Rejoindre le groupe</a>
    </div>
    @endif
</div>
@endsection
