{{-- Page produit · bouton WhatsApp vers le téléphone de la boutique (si activé) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
@if($waUrl)
<a class="pp-wa" href="{{ $waUrl }}" target="_blank" rel="noopener">{!! \App\Support\IconLibrary::svg('message', '', 18) !!} Une question ? Écris-nous sur WhatsApp</a>
@endif
