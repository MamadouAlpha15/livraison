{{-- Page produit · garantie écrite par la boutique (rien n'est affiché si elle est vide) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
@if(filled($shopPage['guarantee']))
<section class="pp-sec pp-guarantee">
    <span class="pp-guarantee-ico">{!! \App\Support\IconLibrary::svg('shield', '', 22) !!}</span>
    <div><strong>Notre engagement</strong><p>{{ $shopPage['guarantee'] }}</p></div>
</section>
@endif
