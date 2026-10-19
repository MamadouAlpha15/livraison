{{--
    Haut de page commun aux modèles : barre « Modèles de boutique Shopio » (le vendeur seul la voit),
    bandeau du lien d'aperçu, bandeau du mode vacances.
    Variables : $shop, $pm, $model, $isOwner, $publishedModel, $previewLink
--}}
@if($isOwner && !$previewLink)
@include('public.shops.models._owner-bar', ['current' => $model])
@endif
@if($previewLink)
<div class="sm-banner is-plink" role="note"><span><b>Aperçu</b> de la vitrine de {{ $shop->name }} · pas encore visible des clients · lien valable jusqu’au {{ $previewLink['expires']->timezone('Africa/Conakry')->translatedFormat('j F à H:i') }}</span></div>
@endif
@if($pm['settings']['vacation'])
<div class="sm-banner is-vac" role="status"><strong>Boutique en pause</strong><span>{{ $pm['settings']['vacationMsg'] !== '' ? $pm['settings']['vacationMsg'] . ' · ' : '' }}vous pouvez parcourir les produits, les commandes reprendront au retour de la boutique.</span></div>
@endif
