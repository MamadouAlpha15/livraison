{{-- Page produit · section « reviews » (variables de client/product-page) --}}
@php use App\Support\IconLibrary as Ico; use App\Support\Money; use App\Support\PageModels; @endphp
{{-- Sans avis : bloc d'attente seulement dans les modèles Classique et Confiance --}}
@if($reviews->isNotEmpty() || in_array($template, ['classique', 'confiance'], true))
                <section class="pp-sec">
                    <h2 class="pp-h2">Avis clients @if($reviewAvg)<span class="pp-stars" style="font-size:15px">★ {{ number_format($reviewAvg, 1, ',', '') }}</span> <small style="font-size:13px;color:var(--muted)">({{ $reviewCount }})</small>@endif</h2>
                    @if($reviews->isNotEmpty())
                    <div class="pp-reviews">
                        @foreach($reviews as $r)
                        <div class="pp-review">
                            <span class="pp-stars" aria-label="{{ $r->rating }} sur 5">{{ str_repeat('★', (int) $r->rating) }}{{ str_repeat('☆', 5 - (int) $r->rating) }}</span>
                            @if($r->comment)<p class="pp-text" style="font-size:14px">{{ $r->comment }}</p>@endif
                            <small style="color:var(--muted)">{{ \Illuminate\Support\Str::before($r->client?->name ?? 'Client', ' ') }} · {{ $r->created_at->format('d/m/Y') }}</small>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="pp-empty">Les avis vérifiés des clients livrés apparaîtront ici.</div>
                    @endif
                </section>
@endif
