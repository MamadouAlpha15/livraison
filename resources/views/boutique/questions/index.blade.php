{{--
    resources/views/boutique/questions/index.blade.php
    Route : GET /boutique/questions → Boutique\ProductQuestionController@index
--}}
@extends('layouts.app')
@section('title', 'Questions des clients')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
.bq { --ink: #16162A; --ink-2: #45455E; --ink-3: #62627C; --line: #E3E8F2; --bg: #F6F9FF; --blue: #1668D8; --blue-dk: #1260C9; --blue-lt: #EAF2FF;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; color: var(--ink); background: var(--bg); min-height: 100vh; padding-bottom: 56px; }
.bq-head { background: radial-gradient(110% 170% at 100% 0%, rgba(31,124,246,.45), transparent 60%), linear-gradient(135deg, #0B1E3D, #132B52); color: #fff; padding: 22px max(16px, env(safe-area-inset-right, 0px)) 26px max(16px, env(safe-area-inset-left, 0px)); }
.bq-head-in { max-width: 860px; margin: 0 auto; display: grid; gap: 10px; }
.bq-back { display: inline-flex; align-items: center; gap: 6px; width: fit-content; min-height: 36px; padding: 0 14px; border-radius: 999px; background: rgba(255,255,255,.1); color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; }
.bq-back:hover { background: rgba(255,255,255,.18); color: #fff; }
.bq-head h1 { margin: 0; font-size: clamp(22px, 4vw, 28px); font-weight: 800; letter-spacing: -.02em; }
.bq-head p { margin: 0; color: #C9D6EA; font-size: 14px; max-width: 62ch; }
.bq-main { max-width: 860px; margin: 22px auto 0; padding: 0 16px; display: grid; gap: 14px; }
.bq-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
.bq-tab { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 16px; border-radius: 999px; background: rgba(22,22,42,.07); color: var(--ink-2); font-size: 13.5px; font-weight: 700; text-decoration: none; }
.bq-tab.is-active { background: var(--blue); color: #fff; }
.bq-count { font-size: 11.5px; font-weight: 800; padding: 1px 8px; border-radius: 999px; background: #F59E0B; color: #fff; }
.bq-flash { padding: 12px 16px; border-radius: 14px; font-size: 13.5px; font-weight: 600; background: #ECFDF5; color: #065F46; }
.bq-card { background: #fff; border-radius: 20px; box-shadow: 0 0 0 1px var(--line); padding: 16px 18px; display: grid; gap: 10px; }
.bq-prod { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; color: var(--blue-dk); text-decoration: none; }
.bq-prod img, .bq-prod-ph { width: 36px; height: 36px; border-radius: 10px; object-fit: cover; background: var(--blue-lt); flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: var(--blue); }
.bq-q { font-size: 15px; font-weight: 700; line-height: 1.5; overflow-wrap: anywhere; }
.bq-meta { font-size: 12px; color: var(--ink-3); }
.bq-a { font-size: 14px; color: var(--ink-2); line-height: 1.5; padding: 10px 12px; border-radius: 12px; background: var(--bg); overflow-wrap: anywhere; }
.bq-form { display: grid; gap: 8px; }
.bq-form textarea { width: 100%; padding: 12px 14px; border: 1.5px solid var(--line); border-radius: 14px; font: inherit; font-size: 14px; color: var(--ink); resize: vertical; min-height: 70px; }
.bq-form textarea:focus { outline: none; border-color: #1F7CF6; box-shadow: 0 0 0 4px rgba(31,124,246,.12); }
.bq-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.bq-btn { min-height: 40px; padding: 0 18px; border-radius: 999px; border: none; font-family: inherit; font-size: 13.5px; font-weight: 800; cursor: pointer; background: var(--blue); color: #fff; }
.bq-btn:hover { background: var(--blue-dk); }
.bq-btn.ghost { background: transparent; color: #B91C1C; font-weight: 700; }
.bq-btn.ghost:hover { background: #FEF2F2; }
.bq-empty { padding: 28px 20px; text-align: center; background: #fff; border-radius: 20px; box-shadow: 0 0 0 1px var(--line); color: var(--ink-2); font-size: 14px; }
.bq-err { font-size: 12.5px; color: #B91C1C; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="bq">
    <header class="bq-head">
        <div class="bq-head-in">
            <a href="{{ route('boutique.dashboard') }}" class="bq-back">{!! \App\Support\IconLibrary::svg('arrow-left', '', 15) !!} Tableau de bord</a>
            <h1>Questions des clients</h1>
            <p>Vos réponses s'affichent sur la fiche produit et servent à tous les acheteurs. Le client est prévenu dès que vous répondez.</p>
        </div>
    </header>

    <main class="bq-main">
        <nav class="bq-tabs" aria-label="Filtrer les questions">
            <a href="{{ route('boutique.questions.index') }}" class="bq-tab {{ $filter === 'a-repondre' ? 'is-active' : '' }}">
                À répondre @if($toAnswer > 0)<span class="bq-count">{{ $toAnswer }}</span>@endif
            </a>
            <a href="{{ route('boutique.questions.index', ['filtre' => 'repondues']) }}" class="bq-tab {{ $filter === 'repondues' ? 'is-active' : '' }}">Répondues</a>
        </nav>

        @if(session('success'))<div class="bq-flash" role="status">{{ session('success') }}</div>@endif

        @forelse($questions as $q)
        <article class="bq-card">
            <a href="{{ route('client.products.show', $q->product_id) }}#questions" class="bq-prod" target="_blank" rel="noopener">
                @if($q->product?->image)
                    <img src="{{ \App\Services\ImageOptimizer::url($q->product->image, 'thumb') ?? asset('storage/' . $q->product->image) }}" alt="" width="36" height="36" loading="lazy">
                @else
                    <span class="bq-prod-ph">{!! \App\Support\IconLibrary::svg('tag', '', 16) !!}</span>
                @endif
                {{ $q->product->name ?? 'Produit supprimé' }}
            </a>
            <div class="bq-q">{{ $q->question }}</div>
            <div class="bq-meta">{{ $q->author_first_name }} · {{ $q->created_at->diffForHumans() }}</div>

            @if($q->answered_at)
            <div class="bq-a">{{ $q->answer }}</div>
            @endif

            <form method="POST" action="{{ route('boutique.questions.answer', $q) }}" class="bq-form">
                @csrf
                <label class="bq-meta" for="ans{{ $q->id }}">{{ $q->answered_at ? 'Modifier la réponse' : 'Votre réponse' }}</label>
                <textarea id="ans{{ $q->id }}" name="answer" rows="2" maxlength="1000" required>{{ $q->answered_at ? $q->answer : '' }}</textarea>
                <div class="bq-actions">
                    <button type="submit" class="bq-btn">{{ $q->answered_at ? 'Mettre à jour' : 'Publier la réponse' }}</button>
                    <button type="submit" form="del{{ $q->id }}" class="bq-btn ghost">Supprimer la question</button>
                </div>
            </form>
            <form method="POST" action="{{ route('boutique.questions.destroy', $q) }}" id="del{{ $q->id }}" onsubmit="return confirm('Supprimer cette question ? Elle disparaîtra de la fiche produit.')">
                @csrf @method('DELETE')
            </form>
        </article>
        @empty
        <div class="bq-empty">
            {{ $filter === 'a-repondre' ? 'Aucune question en attente. Les nouvelles questions apparaîtront ici.' : 'Aucune question répondue pour l\'instant.' }}
        </div>
        @endforelse

        {{ $questions->links() }}
    </main>
</div>
@endsection
