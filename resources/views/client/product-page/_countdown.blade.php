{{-- Page produit · compte à rebours, uniquement pendant une vraie vente flash du produit --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
@if($product->is_flash_active)
<div class="pp-countdown" data-ends="{{ $product->flash_ends_at->timestamp }}">
    {!! \App\Support\IconLibrary::svg('clock', '', 18) !!}
    <span>Vente flash : se termine dans <strong class="pp-num" data-timer>--:--:--</strong></span>
</div>
@endif
