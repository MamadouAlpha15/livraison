{{-- Bloc Titre : nom du produit --}}
@once
@push('pb-styles')
<style data-pb>
.pb-title { margin: 0; line-height: 1.15; font-weight: 800; letter-spacing: -.015em; overflow-wrap: anywhere; }
.pb-title--medium { font-size: 22px; }
.pb-title--large { font-size: clamp(24px, 5vw, 32px); }
.pb-title--xlarge { font-size: clamp(28px, 6.5vw, 42px); }
</style>
@endpush
@endonce
<h1 class="pb-title pb-title--{{ $settings['size'] }}">{{ $ctx->product->name }}</h1>
