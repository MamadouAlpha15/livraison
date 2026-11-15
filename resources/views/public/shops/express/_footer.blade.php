{{--
    Questions fréquentes + pied de page du modèle « Express ».
    Variables : $pm, $faq (liste [q, a] propre à la page, facultative), $faqBg (fond de la section)
--}}
@php
    $s = $pm['shop'];
    $faq = !empty($faq) ? $faq : array_values(array_filter([
        ['q' => 'Comment passer commande ?', 'a' => 'Choisissez votre produit, indiquez votre nom, votre téléphone et votre commune, puis confirmez. Cela prend moins d’une minute.'],
        ['q' => 'Quand est-ce que je paie ?', 'a' => 'À la livraison, en espèces, au livreur. Vous ne payez rien avant d’avoir le colis entre les mains.'],
        ['q' => 'Quels sont les délais et les frais de livraison ?', 'a' => 'La boutique vous appelle après votre commande pour confirmer le délai et les frais de livraison avant d’envoyer le colis.'],
        ['q' => 'Puis-je commander plusieurs produits ?', 'a' => 'Oui. Ajoutez-les au panier : ils partent dans une seule commande. Les packs vous font économiser quand vous en prenez plusieurs.'],
        ['q' => 'Comment suivre ma commande ?', 'a' => 'Après l’envoi de la commande, un lien « Suivre ma commande » s’affiche : vous suivez chaque étape sur Shopio.'],
        $pm['settings']['nego'] ? ['q' => 'Puis-je négocier le prix ?', 'a' => 'Oui : ouvrez l’aperçu d’un produit puis touchez « Négocier le prix avec la boutique ».'] : null,
    ]));
    $who = collect($pm['reviews'])->pluck('name')->unique()->take(3);
@endphp
<section class="ex-sec" id="questions" aria-labelledby="exFaqH" style="background:{{ $faqBg ?? 'var(--mint)' }}">
    <div class="ex-wrap ex-faq">
        <div data-rv>
            <h2 class="ex-h2" id="exFaqH">Les réponses à vos questions <span class="ex-acc">importantes</span></h2>
            @if($who->count())
            <div class="ex-faq-who">@foreach($who as $n)<i aria-hidden="true">{{ mb_strtoupper(mb_substr($n, 0, 1)) }}</i>@endforeach<small>Ils nous font déjà confiance</small></div>
            @endif
        </div>
        <div data-rv style="--d:100ms">
            @foreach($faq as $i => $f)
            <details @if($i === 0) open @endif><summary>{{ $f['q'] }}</summary><p>{{ $f['a'] }}</p></details>
            @endforeach
        </div>
    </div>
</section>

<footer class="ex-foot" id="contact">
    <div class="ex-wrap">
        <div class="ex-foot-grid">
            <div><b class="ex-foot-name">{{ $s['name'] }}</b><p>{{ $s['slogan'] !== '' ? $s['slogan'] : 'Commandez en ligne en une minute et payez à la livraison.' }}@if($s['address'] !== '') <br>{{ \Illuminate\Support\Str::limit($s['address'], 60) }}@endif</p></div>
            <div><h3>Navigation</h3><ul><li><a href="{{ $s['url'] }}">Accueil</a></li><li><a href="{{ $s['url'] }}?vue=produits">Nos produits</a></li><li><a href="#questions">Questions fréquentes</a></li></ul></div>
            <div><h3>Informations</h3><ul><li>Paiement à la livraison</li><li>Confirmation par téléphone</li><li>Suivi de commande sur Shopio</li></ul></div>
        </div>
        <div class="ex-foot-bottom"><span>© {{ date('Y') }} {{ $s['name'] }}</span><span>Boutique sur Shopio</span></div>
    </div>
</footer>
