{{--
    resources/views/academy/show.blade.php
    Route     : GET /academie/{slug} → AcademyController@show → name('academy.show')
    Rôle      : Un guide de l'Académie : étapes numérotées, lien vers Shopio, autres guides.
    Variables : $slug, $guide, $others, $community
--}}
@extends('site.layout')
@section('title', $guide['title'])
@section('description', $guide['summary'])
@push('styles')
<style>
.ag { max-width: 760px; margin: 0 auto; padding: 40px 20px 0; }
.ag-back { display: inline-flex; gap: 6px; color: var(--ink-2); font-weight: 700; font-size: 14px; text-decoration: none; }
.ag-back:hover { color: var(--ink); }
.ag h1 { margin: 14px 0 10px; font-size: clamp(32px, 4.6vw, 48px); line-height: 1.08; font-weight: 600; letter-spacing: -.045em; text-wrap: balance; }
.ag-lead { margin: 0 0 8px; color: var(--ink-2); font-size: 18px; }
.ag-meta { color: var(--ink-3); font-weight: 700; font-size: 13px; }
.ag-steps { list-style: none; margin: 30px 0 0; padding: 0; counter-reset: st; display: grid; gap: 14px; }
.ag-steps li { counter-increment: st; display: grid; grid-template-columns: 40px 1fr; gap: 4px 14px; padding: 20px; border-radius: 16px; background: var(--soft); }
.ag-steps li::before { content: counter(st); grid-row: span 2; display: grid; place-items: center; width: 34px; height: 34px; border-radius: 999px; background: var(--ink); color: #fff; font-weight: 800; font-size: 14px; }
.ag-steps h2 { margin: 4px 0 0; font-size: 18px; font-weight: 600; letter-spacing: -.02em; }
.ag-steps p { margin: 0; color: var(--ink-2); font-size: 16px; }
.ag-cta { margin-top: 26px; padding: 22px; border-radius: 18px; box-shadow: inset 0 0 0 2px var(--accent); display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.ag-cta p { margin: 0; flex: 1; min-width: 220px; font-weight: 600; }
.ag-more { margin-top: 40px; }
.ag-more h2 { font-size: 20px; font-weight: 600; letter-spacing: -.02em; margin: 0 0 12px; }
.ag-more a { display: flex; justify-content: space-between; gap: 12px; padding: 14px 0; border-top: 1px solid var(--line); text-decoration: none; font-weight: 600; }
.ag-more a:hover { color: var(--accent-dk); }
.ag-more a small { color: var(--ink-3); font-weight: 600; white-space: nowrap; }
</style>
@endpush
@section('content')
<article class="ag">
    <a class="ag-back" href="{{ route('academy.index') }}">← Tous les guides</a>
    <h1>{{ $guide['title'] }}</h1>
    <p class="ag-lead">{{ $guide['summary'] }}</p>
    <span class="ag-meta">{{ $guide['minutes'] }} min de lecture · Académie Shopio</span>
    <ol class="ag-steps">
        @foreach($guide['steps'] as [$t, $p])
        <li><h2>{{ $t }}</h2><p>{{ $p }}</p></li>
        @endforeach
    </ol>
    <div class="ag-cta">
        <p>Prêt à passer à l’action ? Crée ta boutique : {{ \App\Services\SubscriptionService::TRIAL_DAYS }} jours de Plan Pro offerts.</p>
        <a class="s-btn s-btn-main" href="{{ route('register', ['role' => 'admin']) }}">{{ $guide['cta'][0] }}</a>
    </div>
    @if($community)
    <p style="margin-top:16px;color:var(--ink-2)">Une question ? <a href="{{ $community }}" target="_blank" rel="noopener" style="color:var(--accent-dk);font-weight:700">Rejoins le groupe WhatsApp des vendeurs Shopio</a>.</p>
    @endif
    <section class="ag-more" aria-labelledby="agMore">
        <h2 id="agMore">Autres guides</h2>
        @foreach($others as $s => $g)
        <a href="{{ route('academy.show', $s) }}"><span>{{ $g['title'] }}</span><small>{{ $g['minutes'] }} min</small></a>
        @endforeach
    </section>
</article>
@endsection
