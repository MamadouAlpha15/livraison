{{--
    Questions fréquentes (sauf $noFaq : l'accueil les place lui-même, Boutique → Sections) + pied de page du modèle « Express ».
    Variables : $pm, $faq, $faqBg, $faqTitle (voir _faq), $noFaq
--}}
@php $s = $pm['shop']; @endphp
@if(empty($noFaq))
@include('public.shops.express._faq')
@endif

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
