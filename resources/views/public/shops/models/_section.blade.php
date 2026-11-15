{{--
    Section ajoutée par le vendeur sur la page d'accueil (Boutique → Sections), dans le style du thème :
    texte, image et texte, bannière, produits d'une catégorie, questions et réponses.
    Variables : $sec (section normalisée, voir App\Support\HomeSections), $theme (express | signature), $pm
--}}
@php
    use App\Support\HomeSections as HS;
    $c = $theme === 'signature'
        ? ['wrap' => 'sg-wrap', 'h2' => 'sg-h2', 'sub' => 'sg-lead', 'btn' => 'sg-btn', 'sec' => 'sg-sec', 'faq' => 'sg-wrap sg-faq']
        : ['wrap' => 'ex-wrap', 'h2' => 'ex-h2', 'sub' => 'ex-sub', 'btn' => 'ex-btn is-acc', 'sec' => 'ex-sec', 'faq' => 'ex-wrap ex-faq'];
    $d = $sec['d'];
    $catalog = $pm['shop']['url'] . '?vue=produits';
    $hid = 'cs-' . $sec['id'];
@endphp
@once
<style>
.cs-txt { white-space: pre-line; }
.cs-center { text-align: center; }
.cs-center .cs-txt { margin-inline: auto; }
.cs-imgtext { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: clamp(24px, 5vw, 64px); align-items: center; }
.cs-imgtext.is-right .cs-media { order: 2; }
.cs-media { border-radius: var(--m-radius); overflow: hidden; aspect-ratio: 4 / 3; background: var(--m-soft); }
.cs-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cs-banner { border-radius: var(--m-radius); padding: clamp(28px, 5vw, 52px); display: flex; align-items: center; justify-content: space-between; gap: 18px 28px; flex-wrap: wrap; }
.cs-banner.is-accent { background: var(--m-accent); color: var(--m-accent-ink); }
.cs-banner.is-dark { background: #111; color: #fff; }
.cs-banner.is-soft { background: var(--m-soft); color: var(--m-ink); }
.cs-banner h2 { margin: 0; font: 700 clamp(22px, 3vw, 32px)/1.2 var(--m-title); letter-spacing: -.02em; text-wrap: balance; }
.cs-banner p { margin: 8px 0 0; opacity: .85; }
.cs-banner .cs-bbtn { display: inline-flex; align-items: center; min-height: 48px; padding: 0 24px; border-radius: var(--m-btn-radius); font: 700 14px var(--m-body); text-decoration: none; background: #fff; color: #111; }
.cs-banner.is-soft .cs-bbtn { background: var(--m-accent); color: var(--m-accent-ink); }
.cs-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin-top: 24px; }
.cs-card { display: block; text-decoration: none; color: inherit; }
.cs-card i { display: block; aspect-ratio: 1; border-radius: calc(var(--m-radius) * .75); background: var(--m-soft) center / cover; transition: transform 400ms cubic-bezier(.22,1,.36,1); }
.cs-card:hover i { transform: translateY(-4px); }
.cs-card b { display: block; margin-top: 10px; font-size: 14.5px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cs-card span { display: block; margin-top: 2px; font-weight: 800; color: var(--m-accent-text, var(--m-ink)); font-variant-numeric: tabular-nums; }
@media (max-width: 760px) {
    .cs-imgtext { grid-template-columns: minmax(0, 1fr); }
    .cs-imgtext.is-right .cs-media { order: 0; }
    .cs-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
}
</style>
@endonce

@switch($sec['type'])
    @case('text')
    @if($d['title'] !== '' || $d['text'] !== '')
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }} {{ $d['align'] === 'center' ? 'cs-center' : '' }}" data-rv>
            @if($d['title'] !== '')<h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $d['title'] }}</h2>@endif
            @if($d['text'] !== '')<p class="{{ $c['sub'] }} cs-txt" style="max-width:70ch">{{ $d['text'] }}</p>@endif
        </div>
    </section>
    @endif
    @break

    @case('imgtext')
    @if($d['img'] || $d['title'] !== '' || $d['text'] !== '')
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }} cs-imgtext {{ $d['side'] === 'right' ? 'is-right' : '' }}">
            <div class="cs-media" data-rv>@if($d['img'])<img src="{{ HS::imgUrl($d['img']) }}" alt="" loading="lazy">@endif</div>
            <div data-rv style="--d:100ms">
                @if($d['title'] !== '')<h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $d['title'] }}</h2>@endif
                @if($d['text'] !== '')<p class="{{ $c['sub'] }} cs-txt">{{ $d['text'] }}</p>@endif
                @if($d['button'] !== '')<a class="{{ $c['btn'] }}" style="margin-top:22px" href="{{ $d['link'] ?: $catalog }}" @if($d['link']) rel="noopener" @endif>{{ $d['button'] }}</a>@endif
            </div>
        </div>
    </section>
    @endif
    @break

    @case('banner')
    @if($d['title'] !== '')
    <section style="padding:clamp(20px,3vw,32px) 0" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-banner is-{{ $d['tone'] }}" data-rv>
                <div><h2 id="{{ $hid }}">{{ $d['title'] }}</h2>@if($d['text'] !== '')<p>{{ $d['text'] }}</p>@endif</div>
                @if($d['button'] !== '')<a class="cs-bbtn" href="{{ $d['link'] ?: $catalog }}" @if($d['link']) rel="noopener" @endif>{{ $d['button'] }}</a>@endif
            </div>
        </div>
    </section>
    @endif
    @break

    @case('category')
    @php $items = collect($pm['products'])->filter(fn ($p) => $d['cat'] !== '' && $p['cat'] === $d['cat'])->take($d['limit']); $catLink = collect($pm['categories'])->firstWhere('name', $d['cat']); @endphp
    @if($items->isNotEmpty())
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap" data-rv>
                <h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $d['title'] !== '' ? $d['title'] : $d['cat'] }}</h2>
                @if($catLink)<a href="{{ $catLink['url'] }}" style="font-weight:700;text-decoration:underline;text-underline-offset:4px">Tout voir ({{ $catLink['count'] }})</a>@endif
            </div>
            <div class="cs-grid">
                @foreach($items as $i => $p)
                <a class="cs-card" href="{{ $p['page'] }}" data-rv style="--d:{{ min($i, 6) * 60 }}ms"><i @if($p['photo']) style="background-image:url('{{ $p['photo'] }}')" @endif></i><b>{{ $p['name'] }}</b><span>{{ number_format($p['price'], 0, ',', ' ') }} {{ $pm['shop']['currency'] }}</span></a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('qa')
    @php $qs = collect($d['items'])->filter(fn ($q) => $q['q'] !== ''); @endphp
    @if($qs->isNotEmpty())
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['faq'] }}">
            <div data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $d['title'] !== '' ? $d['title'] : 'Questions et réponses' }}</h2></div>
            <div data-rv style="--d:100ms">
                @foreach($qs->values() as $i => $q)<details @if($i === 0) open @endif><summary>{{ $q['q'] }}</summary>@if($q['a'] !== '')<p class="cs-txt">{{ $q['a'] }}</p>@endif</details>@endforeach
            </div>
        </div>
    </section>
    @endif
    @break
@endswitch
