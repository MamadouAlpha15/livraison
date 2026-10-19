{{--
    Barre « Modèles de boutique Shopio » : visible du seul vendeur, sur sa propre vitrine.
    Passer d'un modèle à l'autre (essai, rien n'est publié), adopter le modèle affiché, rejoindre l'éditeur.
    Variables : $current (modèle affiché), $publishedModel (modèle en ligne)
--}}
<div class="pmb" role="region" aria-label="Modèles de boutique Shopio">
    <style>
    .pmb { position: relative; z-index: 800; display: flex; align-items: center; gap: 10px 14px; flex-wrap: wrap; padding: 10px max(16px, env(safe-area-inset-left, 0px)); background: #EAF2FF; color: #0B1E3D; font: 600 13.5px 'Plus Jakarta Sans', system-ui, sans-serif; box-shadow: 0 1px 0 #D6E6FF; }
    .pmb-t { display: flex; flex-direction: column; line-height: 1.25; margin-right: 4px; }
    .pmb-t b { font-weight: 800; font-size: 14px; } .pmb-t small { color: #45455E; font-weight: 600; font-size: 12px; }
    .pmb-list { display: flex; gap: 4px; flex-wrap: wrap; flex: 1; }
    .pmb a, .pmb button { display: inline-flex; align-items: center; gap: 6px; min-height: 36px; padding: 0 13px; border: 0; border-radius: 999px; background: #fff; color: #0B1E3D; text-decoration: none; font: 700 13px 'Plus Jakarta Sans', system-ui, sans-serif; cursor: pointer; box-shadow: inset 0 0 0 1px #D6E6FF; transition: background-color 180ms, color 180ms; }
    .pmb a:hover { background: #D6E6FF; }
    .pmb a[aria-current="page"] { background: #1668D8; color: #fff; box-shadow: none; }
    .pmb a i { font-style: normal; font-size: 10.5px; font-weight: 800; padding: 2px 6px; border-radius: 999px; background: #E7F6EC; color: #15803D; }
    .pmb a[aria-current="page"] i { background: rgba(255,255,255,.22); color: #fff; }
    .pmb form { margin: 0; }
    .pmb .pmb-use { background: #F59E0B; color: #16162A; box-shadow: none; }
    .pmb .pmb-ok { padding: 6px 12px; border-radius: 999px; background: #E7F6EC; color: #15803D; font-weight: 800; }
    .pmb :focus-visible { outline: 3px solid #1F7CF6; outline-offset: 2px; }
    </style>
    <span class="pmb-t"><b>Modèles de boutique Shopio</b><small>Visible par vous seul · essayez sans rien publier</small></span>
    <nav class="pmb-list" aria-label="Modèles">
        @foreach(\App\Support\PageModels::MODELS as $k => [$label])
        <a href="{{ request()->url() }}?modele={{ $k }}" @if($k === $current) aria-current="page" @endif>{{ $label }}@if($k === $publishedModel)<i>en ligne</i>@endif</a>
        @endforeach
    </nav>
    @if(session('model_used'))<span class="pmb-ok" role="status">Modèle « {{ session('model_used') }} » en ligne</span>@endif
    @if($current !== $publishedModel)
    <form method="POST" action="{{ route('boutique.grow.model') }}">@csrf<input type="hidden" name="model" value="{{ $current }}"><button type="submit" class="pmb-use">Utiliser ce modèle</button></form>
    @endif
    <a href="{{ route('boutique.storefront.edit') }}">Éditeur</a>
    <a href="{{ route('boutique.grow', ['onglet' => 'personnalisation']) }}">Faire grandir</a>
</div>
