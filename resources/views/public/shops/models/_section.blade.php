{{--
    Section ajoutée par le vendeur sur la page d'accueil (Boutique → Sections), dans le style du thème.
    Contenu : barre d'icônes, texte, image et texte, FAQ, FAQ 2 colonnes, barre d'annonces, séparateur.
    Preuve sociale : avis clients, témoignages Facebook, témoignages en photos, résultats (vrais chiffres),
    tableau de comparaison. Média : diaporama, grille vidéos, vitrine vidéo. Commerce : bannière,
    bannière code promo, paiement en confiance, produits d'une catégorie.
    Une section vide (rien de rempli) n'affiche rien. Variables : $sec, $theme (express | signature | cabine), $pm
--}}
@php
    use App\Support\HomeSections as HS;
    $c = match ($theme) {
        'signature' => ['wrap' => 'sg-wrap', 'h2' => 'sg-h2', 'sub' => 'sg-lead', 'btn' => 'sg-btn', 'sec' => 'sg-sec', 'faq' => 'sg-wrap sg-faq'],
        'cabine'    => ['wrap' => 'cb-wrap', 'h2' => 'cb-h2', 'sub' => 'cb-sub', 'btn' => 'cb-btn', 'sec' => 'cb-sec', 'faq' => 'cb-wrap cb-faq'],
        default     => ['wrap' => 'ex-wrap', 'h2' => 'ex-h2', 'sub' => 'ex-sub', 'btn' => 'ex-btn is-acc', 'sec' => 'ex-sec', 'faq' => 'ex-wrap ex-faq'],
    };
    $d = $sec['d'];
    $t = fn ($k) => HS::txt($theme, $sec, $k);
    $catalog = $pm['shop']['url'] . '?vue=produits';
    $hid = 'cs-' . $sec['id'];
    $stars = fn ($n) => str_repeat('★', max(1, min(5, $n))) . '<span style="opacity:.25">' . str_repeat('★', 5 - max(1, min(5, $n))) . '</span>';
    $initial = fn ($n) => mb_strtoupper(mb_substr($n !== '' ? $n : '?', 0, 1));
    $ico = fn ($k, $s = 26) => '<svg width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . (HS::ICONS[$k][1] ?? HS::ICONS['check'][1]) . '"/></svg>';
    $play = '<svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>';
@endphp
@once
<style>
.cs-txt { white-space: pre-line; }
.cs-center { text-align: center; }
.cs-center .cs-txt, .cs-center .cs-sub { margin-inline: auto; }
.cs-hd { display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: clamp(22px, 3vw, 34px); }
.cs-hd p { max-width: 62ch; }
.cs-serif { font-style: italic; font-weight: 400; }
.cs-scroll { display: grid; grid-auto-flow: column; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; padding: 4px 2px 12px; }
.cs-scroll::-webkit-scrollbar { display: none; }
.cs-scroll > * { scroll-snap-align: start; }
/* Image et texte */
.cs-imgtext { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: clamp(24px, 5vw, 64px); align-items: center; }
.cs-imgtext.is-right .cs-media { order: 2; }
.cs-media { border-radius: var(--m-radius); overflow: hidden; aspect-ratio: 4 / 3; background: var(--m-soft); }
.cs-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
/* Couleurs (bannière, code promo, annonces) */
.cs-tone-accent { background: var(--m-accent); color: var(--m-accent-ink); }
.cs-tone-dark { background: #111; color: #fff; }
.cs-tone-soft { background: var(--m-soft); color: var(--m-ink); }
.cs-banner { border-radius: var(--m-radius); padding: clamp(28px, 5vw, 52px); display: flex; align-items: center; justify-content: space-between; gap: 18px 28px; flex-wrap: wrap; }
.cs-banner h2 { margin: 0; font: 700 clamp(22px, 3vw, 32px)/1.2 var(--m-title); letter-spacing: -.02em; text-wrap: balance; }
.cs-banner p { margin: 8px 0 0; opacity: .85; }
.cs-bbtn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 22px; border: 0; border-radius: var(--m-btn-radius); font: 700 14px var(--m-body); text-decoration: none; background: #fff; color: #111; cursor: pointer; }
.cs-tone-soft .cs-bbtn { background: var(--m-accent); color: var(--m-accent-ink); }
/* Barre d'icônes */
.cs-icons { display: flex; justify-content: center; gap: clamp(18px, 4vw, 54px); flex-wrap: wrap; }
.cs-icon { display: flex; flex-direction: column; align-items: center; text-align: center; max-width: 190px; }
.cs-icon i { width: 60px; height: 60px; border-radius: 18px; display: grid; place-items: center; margin-bottom: 10px; background: color-mix(in srgb, var(--m-accent) 12%, transparent); color: var(--m-accent-text, var(--m-accent)); transition: transform 300ms cubic-bezier(.22,1,.36,1); }
.cs-icon:hover i { transform: translateY(-4px) rotate(-4deg); }
.cs-icon b { font: 700 15px var(--m-title); }
.cs-icon span { margin-top: 3px; font-size: 12.5px; color: var(--m-ink2); }
/* Annonces qui défilent */
.cs-marquee { overflow: hidden; padding: 12px 0; }
.cs-marquee-track { display: flex; width: max-content; animation: csMarq 32s linear infinite; }
.cs-marquee-track span { display: inline-flex; align-items: center; gap: 28px; padding-right: 28px; font: 700 13.5px var(--m-body); white-space: nowrap; }
.cs-marquee-track span::after { content: '•'; opacity: .6; }
.cs-marquee:hover .cs-marquee-track { animation-play-state: paused; }
@keyframes csMarq { to { transform: translateX(-50%); } }
/* Séparateur */
.cs-wave { display: block; width: 100%; height: clamp(40px, 6vw, 80px); color: var(--m-soft); }
/* Avis (cartes, Facebook, photos) */
.cs-quotes, .cs-photos { grid-auto-columns: calc((100% - 32px) / 3); justify-content: safe center; }
.cs-quote { display: flex; flex-direction: column; gap: 10px; padding: 22px; border-radius: var(--m-radius); background: var(--m-surface); box-shadow: 0 0 0 1px var(--m-line); height: 100%; }
.cs-stars { color: #F5A524; letter-spacing: 2px; font-size: 15px; }
.cs-quote b { font-size: 15.5px; }
.cs-quote p { margin: 0; color: var(--m-ink2); line-height: 1.6; flex: 1; }
.cs-who { display: flex; align-items: center; gap: 10px; font-size: 13.5px; }
.cs-who i { width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; font-style: normal; font-weight: 800; background: var(--m-soft) center / cover; color: var(--m-ink2); flex-shrink: 0; }
.cs-who b { display: block; font-size: 14px; }
.cs-who small { display: block; color: var(--m-ink2); font-size: 12px; }
.cs-fb { display: flex; flex-direction: column; gap: 10px; padding: 16px; border-radius: 14px; background: #fff; color: #1C1E21; box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 0 0 1px rgba(0,0,0,.05); font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; height: 100%; }
.cs-fb header { display: flex; align-items: center; gap: 10px; }
.cs-fb header i { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; font-style: normal; font-weight: 700; background: #E4E6EB center / cover; color: #65676B; }
.cs-fb header b { display: block; font-size: 14.5px; }
.cs-fb header small { color: #65676B; font-size: 12px; display: flex; align-items: center; gap: 4px; }
.cs-fb p { margin: 0; font-size: 14.5px; line-height: 1.5; flex: 1; }
.cs-fb footer { display: flex; justify-content: space-around; border-top: 1px solid #E4E6EB; padding-top: 8px; color: #65676B; font-size: 13px; font-weight: 600; }
.cs-fb-blue { color: #1877F2; }
.cs-photo figure, .cs-photo { margin: 0; display: flex; flex-direction: column; gap: 10px; }
.cs-photo-img { aspect-ratio: 1; border-radius: calc(var(--m-radius) * .8); background: var(--m-soft) center / cover; }
.cs-photo h3 { margin: 4px 0 0; font: 700 19px/1.25 var(--m-title); }
.cs-photo p { margin: 0; color: var(--m-ink2); line-height: 1.6; font-size: 14px; }
.cs-ok { color: #1877F2; }
/* Résultats */
.cs-stats { display: flex; justify-content: center; gap: clamp(26px, 6vw, 80px); flex-wrap: wrap; }
.cs-stat { text-align: center; }
.cs-stat b { display: block; font: 800 clamp(36px, 5vw, 54px)/1 var(--m-title); color: var(--m-accent-text, var(--m-accent)); font-variant-numeric: tabular-nums; letter-spacing: -.03em; }
.cs-stat span { display: block; margin-top: 8px; color: var(--m-ink2); font-weight: 600; font-size: 14px; }
/* Comparaison */
.cs-compare { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr); gap: clamp(24px, 5vw, 70px); align-items: center; }
.cs-table { display: grid; grid-template-columns: minmax(0, 1fr) 92px 82px; align-items: stretch; position: relative; }
.cs-table > div { display: flex; align-items: center; min-height: 56px; }
.cs-table .cs-th { min-height: 46px; justify-content: center; font-weight: 800; font-size: 14px; }
.cs-table .cs-us { justify-content: center; background: #111; color: #fff; }
.cs-table .cs-th.cs-us { border-radius: 22px 22px 0 0; }
.cs-table .cs-last.cs-us { border-radius: 0 0 22px 22px; }
.cs-table .cs-them { justify-content: center; }
.cs-table .cs-lab { font-weight: 600; font-size: 14.5px; padding-right: 10px; border-bottom: 1px solid var(--m-line); }
.cs-yes, .cs-no { width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; }
.cs-yes { background: #22C55E; color: #fff; }
.cs-no { background: rgba(0,0,0,.08); color: #444; }
/* Diaporama */
.cs-slides { position: relative; border-radius: var(--m-radius); overflow: hidden; background: #111; aspect-ratio: 16 / 7; }
.cs-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 700ms ease; }
.cs-slide.is-on { opacity: 1; z-index: 1; }
.cs-slide img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cs-slide-txt { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 24px; color: #fff; background: linear-gradient(transparent 30%, rgba(0,0,0,.55)); }
.cs-slide-txt h2 { margin: 0; font: 800 clamp(24px, 4vw, 44px)/1.1 var(--m-title); letter-spacing: -.02em; text-shadow: 0 2px 18px rgba(0,0,0,.4); max-width: 22ch; }
.cs-slide-txt p { margin: 10px 0 0; max-width: 52ch; }
.cs-slide-txt .cs-bbtn { margin-top: 18px; }
.cs-dots { position: absolute; z-index: 2; left: 0; right: 0; bottom: 12px; display: flex; justify-content: center; gap: 6px; }
.cs-dots button { width: 9px; height: 9px; border-radius: 50%; border: 0; padding: 0; background: rgba(255,255,255,.5); cursor: pointer; }
.cs-dots button[aria-current="true"] { background: #fff; width: 22px; border-radius: 6px; }
.cs-arrow { position: absolute; z-index: 2; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border: 0; border-radius: 50%; background: rgba(255,255,255,.85); color: #111; font-size: 20px; cursor: pointer; }
.cs-arrow.is-prev { left: 12px; } .cs-arrow.is-next { right: 12px; }
/* Vidéos */
.cs-vgrid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.cs-video { position: relative; border-radius: calc(var(--m-radius) * .8); overflow: hidden; background: #111; aspect-ratio: 9 / 16; }
.cs-video.is-square { aspect-ratio: 1; } .cs-video.is-landscape { aspect-ratio: 16 / 9; }
.cs-video iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
.cs-video button, .cs-video a { position: absolute; inset: 0; width: 100%; border: 0; padding: 0; background: #111 center / cover; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; color: #fff; text-decoration: none; font: 700 14px var(--m-body); }
.cs-video button::before, .cs-video a::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,.25); }
.cs-play { position: relative; width: 62px; height: 62px; border-radius: 50%; background: rgba(255,255,255,.92); color: #111; display: grid; place-items: center; transition: transform 200ms; }
.cs-video:hover .cs-play { transform: scale(1.08); }
.cs-video a span:last-child { position: relative; }
.cs-reel { grid-auto-columns: minmax(200px, 240px); }
/* Code promo */
.cs-promo { display: flex; align-items: center; justify-content: center; gap: 12px 16px; flex-wrap: wrap; padding: 14px 18px; border-radius: calc(var(--m-radius) * .7); font-weight: 700; text-align: center; }
.cs-code { display: inline-flex; align-items: center; gap: 8px; padding: 6px 8px 6px 14px; border-radius: 999px; border: 1.5px dashed currentColor; font: 800 15px var(--m-body); letter-spacing: .08em; }
.cs-code button { min-height: 32px; padding: 0 12px; border: 0; border-radius: 999px; background: #fff; color: #111; font: 700 12.5px var(--m-body); cursor: pointer; letter-spacing: 0; }
/* Paiement en confiance */
.cs-trust { display: flex; flex-direction: column; align-items: center; gap: 10px; text-align: center; padding: 8px 0; }
.cs-trust p { margin: 0; display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px; }
.cs-trust p svg { color: #16A34A; flex-shrink: 0; }
.cs-chips { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
.cs-chips span { padding: 5px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 800; }
.cs-chip-cash { background: #E7F6EC; color: #15803D; }
.cs-chip-om { background: #FF7900; color: #fff; }
.cs-chip-momo { background: #FFCC00; color: #111; }
/* Produits d'une catégorie */
.cs-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin-top: 24px; }
.cs-card { display: block; text-decoration: none; color: inherit; }
.cs-card i { display: block; aspect-ratio: 1; border-radius: calc(var(--m-radius) * .75); background: var(--m-soft) center / cover; transition: transform 400ms cubic-bezier(.22,1,.36,1); }
.cs-card:hover i { transform: translateY(-4px); }
.cs-card b { display: block; margin-top: 10px; font-size: 14.5px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cs-card span { display: block; margin-top: 2px; font-weight: 800; color: var(--m-accent-text, var(--m-ink)); font-variant-numeric: tabular-nums; }
/* FAQ 1 colonne */
.cs-faq1 { max-width: 780px; margin: 0 auto; }
.cs-faq1 details { border-bottom: 1px solid var(--m-line); padding: 4px 0; }
.cs-faq1 summary { cursor: pointer; padding: 14px 0; font-weight: 700; list-style: none; display: flex; justify-content: space-between; gap: 14px; }
.cs-faq1 summary::after { content: '+'; font-size: 22px; line-height: 1; color: var(--m-accent-text, var(--m-accent)); transition: transform 200ms; }
.cs-faq1 details[open] summary::after { transform: rotate(45deg); }
.cs-faq1 details p { margin: 0 0 14px; color: var(--m-ink2); line-height: 1.6; }
@media (max-width: 860px) {
    .cs-compare { grid-template-columns: minmax(0, 1fr); }
    .cs-vgrid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 760px) {
    .cs-imgtext { grid-template-columns: minmax(0, 1fr); }
    .cs-imgtext.is-right .cs-media { order: 0; }
    .cs-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .cs-quotes, .cs-photos { grid-auto-columns: 82%; }
    .cs-reel { grid-auto-columns: 62%; }
    .cs-slides { aspect-ratio: 4 / 5; }
    .cs-table { grid-template-columns: minmax(0, 1fr) 76px 64px; }
    .cs-icons { flex-wrap: nowrap; overflow-x: auto; justify-content: flex-start; scroll-snap-type: x mandatory; scrollbar-width: none; padding-bottom: 6px; }
    .cs-icon { flex: 0 0 42%; scroll-snap-align: start; max-width: none; }
}
@media (min-width: 761px) and (max-width: 1000px) {
    .cs-quotes, .cs-photos { grid-auto-columns: calc((100% - 16px) / 2); }
}
@media (prefers-reduced-motion: reduce) {
    .cs-marquee-track { animation: none; flex-wrap: wrap; width: auto; justify-content: center; }
    .cs-slide { transition: none; }
}
</style>
<script>
(function () {
    'use strict';
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    /* Vidéos : le lecteur YouTube ne se charge qu'au clic (page légère) */
    document.addEventListener('click', e => {
        const b = e.target.closest('[data-cs-play]'); if (!b) return;
        const f = document.createElement('iframe');
        f.src = b.dataset.csPlay + '&autoplay=1'; f.title = 'Vidéo'; f.allow = 'autoplay; encrypted-media; picture-in-picture'; f.allowFullscreen = true;
        b.replaceWith(f);
    });
    /* Code promo : copié en un clic */
    document.addEventListener('click', async e => {
        const b = e.target.closest('[data-cs-copy]'); if (!b) return;
        try { await navigator.clipboard.writeText(b.dataset.csCopy); } catch (x) { window.prompt('Copiez ce code :', b.dataset.csCopy); return; }
        const t = b.textContent; b.textContent = 'Copié ✓'; setTimeout(() => { b.textContent = t; }, 1800);
    });
    /* Diaporamas : flèches, points, défilement automatique (coupé si l'acheteur réduit les animations) */
    function slides(root) {
        const items = [...root.querySelectorAll('.cs-slide')], dots = [...root.querySelectorAll('[data-cs-dot]')];
        if (items.length < 2) return;
        let i = 0, timer = null;
        const show = n => { i = (n + items.length) % items.length; items.forEach((s, k) => s.classList.toggle('is-on', k === i)); dots.forEach((d, k) => d.setAttribute('aria-current', k === i ? 'true' : 'false')); };
        const auto = () => { clearInterval(timer); if (root.dataset.auto === '1' && !reduce) timer = setInterval(() => { if (!document.hidden) show(i + 1); }, 4500); };
        root.addEventListener('click', e => {
            const d = e.target.closest('[data-cs-dot]'), a = e.target.closest('[data-cs-step]');
            if (d) { show(+d.dataset.csDot); auto(); }
            if (a) { show(i + +a.dataset.csStep); auto(); }
        });
        auto();
    }
    const init = () => document.querySelectorAll('[data-cs-slides]').forEach(slides);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
</script>
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

    @case('icons')
    @if(count($d['items']))
    <section style="padding:clamp(28px,4vw,48px) 0" @if($d['title'] !== '') aria-labelledby="{{ $hid }}" @else aria-label="Nos engagements" @endif>
        <div class="{{ $c['wrap'] }}">
            @if($d['title'] !== '')<div class="cs-hd" data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $d['title'] }}</h2></div>@endif
            <div class="cs-icons">
                @foreach($d['items'] as $i => $it)
                <div class="cs-icon" data-rv style="--d:{{ min($i, 6) * 70 }}ms"><i>{!! $ico($it['icon']) !!}</i>@if($it['title'] !== '')<b>{{ $it['title'] }}</b>@endif @if($it['text'] !== '')<span>{{ $it['text'] }}</span>@endif</div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('marquee')
    @if(count($d['items']))
    <div class="cs-marquee cs-tone-{{ $d['tone'] }}" role="note" aria-label="{{ implode(' · ', $d['items']) }}">
        <div class="cs-marquee-track" aria-hidden="true">
            @for($k = 0; $k < 2; $k++)
            @foreach(array_merge($d['items'], count($d['items']) < 4 ? $d['items'] : []) as $m)<span>{{ $m }}</span>@endforeach
            @endfor
        </div>
    </div>
    @endif
    @break

    @case('divider')
    @if($d['style'] === 'wave')
    <svg class="cs-wave" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40 C 320 90, 620 0, 900 34 S 1300 70, 1440 20 V90 H0z" fill="currentColor" opacity=".55"/><path d="M0 60 C 360 100, 700 20, 1000 50 S 1340 80, 1440 46 V90 H0z" fill="currentColor"/></svg>
    @elseif($d['style'] === 'line')
    <div class="{{ $c['wrap'] }}" aria-hidden="true"><hr style="border:0;border-top:1px solid var(--m-line);margin:clamp(20px,3vw,36px) 0"></div>
    @else
    <div style="height:clamp(32px,5vw,64px)" aria-hidden="true"></div>
    @endif
    @break

    @case('qa')
    @php $qs = collect($d['items'])->filter(fn ($q) => $q['q'] !== '')->values(); @endphp
    @if($qs->isNotEmpty())
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-hd" data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $t('title') }}</h2></div>
            <div class="cs-faq1" data-rv style="--d:100ms">
                @foreach($qs as $i => $q)<details @if($i === 0) open @endif><summary>{{ $q['q'] }}</summary>@if($q['a'] !== '')<p class="cs-txt">{{ $q['a'] }}</p>@endif</details>@endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('faq2')
    @php $qs = collect($d['items'])->filter(fn ($q) => $q['q'] !== '')->values(); @endphp
    @if($qs->isNotEmpty())
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}" style="background:var(--m-soft)">
        <div class="{{ $c['faq'] }}">
            <div data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $t('title') }}</h2>@if($d['text'] !== '')<p class="{{ $c['sub'] }}">{{ $d['text'] }}</p>@endif</div>
            <div data-rv style="--d:100ms">
                @foreach($qs as $i => $q)<details @if($i === 0) open @endif><summary>{{ $q['q'] }}</summary>@if($q['a'] !== '')<p class="cs-txt">{{ $q['a'] }}</p>@endif</details>@endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('quotes')
    @if(count($d['items']))
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-hd" data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $t('title') }}</h2></div>
            <div class="cs-scroll cs-quotes">
                @foreach($d['items'] as $i => $p)
                <figure class="cs-quote" data-rv style="margin:0;--d:{{ min($i, 5) * 70 }}ms">
                    <span class="cs-stars" role="img" aria-label="{{ $p['rating'] }} sur 5">{!! $stars($p['rating']) !!}</span>
                    @if($p['title'] !== '')<b>{{ $p['title'] }}</b>@endif
                    @if($p['text'] !== '')<p class="cs-txt">{{ $p['text'] }}</p>@endif
                    <figcaption class="cs-who"><i @if($p['img']) style="background-image:url('{{ HS::imgUrl($p['img']) }}')" @endif aria-hidden="true">@unless($p['img']){{ $initial($p['name']) }}@endunless</i><span>@if($p['name'] !== '')<b>{{ $p['name'] }}</b>@endif @if($p['label'] !== '')<small>{{ $p['label'] }}</small>@endif</span></figcaption>
                </figure>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('fbposts')
    @if(count($d['items']))
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}" style="background:var(--m-soft)">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-hd" data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">@if($d['title'] !== ''){{ $d['title'] }}@else Ils en parlent sur <span class="cs-fb-blue">Facebook</span>@endif</h2></div>
            <div class="cs-scroll cs-quotes">
                @foreach($d['items'] as $i => $p)
                <article class="cs-fb" data-rv style="--d:{{ min($i, 5) * 70 }}ms">
                    <header><i @if($p['img']) style="background-image:url('{{ HS::imgUrl($p['img']) }}')" @endif aria-hidden="true">@unless($p['img']){{ $initial($p['name']) }}@endunless</i><span><b>{{ $p['name'] !== '' ? $p['name'] : 'Client' }}</b><small>{{ $p['label'] !== '' ? $p['label'] : 'Avis client' }} · <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.9 6h-3a15.7 15.7 0 0 0-1.4-3.6A8 8 0 0 1 18.9 8zM12 4c.8 1.2 1.5 2.5 1.9 4h-3.8c.4-1.5 1.1-2.8 1.9-4z"/></svg></small></span></header>
                    @if($p['title'] !== '')<b>{{ $p['title'] }}</b>@endif
                    @if($p['text'] !== '')<p class="cs-txt">{{ $p['text'] }}</p>@endif
                    <footer aria-hidden="true"><span>👍 J’aime</span><span>💬 Commenter</span><span>↗ Partager</span></footer>
                </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('testimonials')
    @if(count($d['items']))
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-hd" data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $t('title') }}@if($t('title2') !== '')<br><span class="cs-serif" style="font-family:{{ $theme === 'express' ? 'var(--serif)' : 'var(--m-title)' }}">{{ $t('title2') }}</span>@endif</h2>@if($t('text') !== '')<p class="{{ $c['sub'] }}">{{ $t('text') }}</p>@endif</div>
            <div class="cs-scroll cs-photos">
                @foreach($d['items'] as $i => $p)
                <figure class="cs-photo" data-rv style="--d:{{ min($i, 5) * 70 }}ms">
                    <div class="cs-photo-img" @if($p['img']) style="background-image:url('{{ HS::imgUrl($p['img']) }}')" @endif role="img" aria-label="{{ $p['name'] !== '' ? 'Photo de ' . $p['name'] : 'Photo du client' }}"></div>
                    @if($p['title'] !== '')<h3>« {{ $p['title'] }} »</h3>@endif
                    @if($p['text'] !== '')<p class="cs-txt">« {{ $p['text'] }} »</p>@endif
                    <figcaption class="cs-who" style="gap:6px"><span>@if($p['name'] !== '')<b>{{ $p['name'] }} <span class="cs-ok" aria-hidden="true">✓</span></b>@endif @if($p['label'] !== '')<small class="cs-ok">{{ $p['label'] }}</small>@endif</span></figcaption>
                </figure>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('stats')
    @if($pm['rating'] || $pm['delivered'] || count($pm['products']))
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-hd" data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $t('title') }}</h2></div>
            <div class="cs-stats" data-rv>
                @if($pm['rating'])<div class="cs-stat"><b>{{ number_format($pm['rating']['avg'], 1, ',', ' ') }}<small style="font-size:.45em"> / 5</small></b><span>note moyenne · {{ $pm['rating']['count'] }} avis</span></div>@endif
                @if($pm['delivered'])<div class="cs-stat"><b>{{ number_format($pm['delivered'], 0, ',', ' ') }}</b><span>commandes livrées</span></div>@endif
                @if(count($pm['products']))<div class="cs-stat"><b>{{ count($pm['products']) }}</b><span>produit{{ count($pm['products']) > 1 ? 's' : '' }} en vente</span></div>@endif
            </div>
            <p class="{{ $c['sub'] }}" style="text-align:center;font-size:12.5px">Calculé sur les vrais avis et les vraies commandes de la boutique.</p>
        </div>
    </section>
    @endif
    @break

    @case('compare')
    @if(count($d['items']))
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }} cs-compare">
            <div data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $t('title') }}</h2>@if($t('text') !== '')<p class="{{ $c['sub'] }}">{{ $t('text') }}</p>@endif</div>
            <div class="cs-table" data-rv style="--d:100ms" role="table" aria-label="{{ $t('title') }}">
                <div role="columnheader"></div><div class="cs-th cs-us" role="columnheader">{{ $d['us'] !== '' ? $d['us'] : \Illuminate\Support\Str::limit($pm['shop']['name'], 14) }}</div><div class="cs-th cs-them" role="columnheader">{{ $t('them') }}</div>
                @foreach($d['items'] as $i => $r)
                @php $last = $loop->last ? ' cs-last' : ''; @endphp
                <div class="cs-lab" role="cell">{{ $r['label'] }}</div>
                <div class="cs-us{{ $last }}" role="cell">@if($r['us'])<span class="cs-yes">{!! $ico('check', 16) !!}<span class="sm-sr">Oui</span></span>@else<span class="cs-no">✕<span class="sm-sr">Non</span></span>@endif</div>
                <div class="cs-them" role="cell" style="border-bottom:1px solid var(--m-line)">@if($r['them'])<span class="cs-yes">{!! $ico('check', 16) !!}<span class="sm-sr">Oui</span></span>@else<span class="cs-no">✕<span class="sm-sr">Non</span></span>@endif</div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('slideshow')
    @if(count($d['items']))
    <section style="padding:clamp(20px,3vw,36px) 0" aria-roledescription="carrousel" aria-label="Diaporama">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-slides" data-cs-slides data-auto="{{ $d['auto'] ? 1 : 0 }}" data-rv>
                @foreach($d['items'] as $i => $s)
                <div class="cs-slide {{ $i === 0 ? 'is-on' : '' }}" role="group" aria-roledescription="diapositive" aria-label="{{ $i + 1 }} sur {{ count($d['items']) }}">
                    <img src="{{ HS::imgUrl($s['img']) }}" alt="{{ $s['title'] }}" @if($i > 0) loading="lazy" @endif>
                    @if($s['title'] !== '' || $s['text'] !== '' || $s['button'] !== '')
                    <div class="cs-slide-txt">@if($s['title'] !== '')<h2>{{ $s['title'] }}</h2>@endif @if($s['text'] !== '')<p>{{ $s['text'] }}</p>@endif @if($s['button'] !== '')<a class="cs-bbtn" href="{{ $s['link'] ?: $catalog }}" @if($s['link']) rel="noopener" @endif>{{ $s['button'] }}</a>@endif</div>
                    @endif
                </div>
                @endforeach
                @if(count($d['items']) > 1)
                <button type="button" class="cs-arrow is-prev" data-cs-step="-1" aria-label="Diapositive précédente">‹</button>
                <button type="button" class="cs-arrow is-next" data-cs-step="1" aria-label="Diapositive suivante">›</button>
                <div class="cs-dots">@foreach($d['items'] as $i => $s)<button type="button" data-cs-dot="{{ $i }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Diapositive {{ $i + 1 }}"></button>@endforeach</div>
                @endif
            </div>
        </div>
    </section>
    @endif
    @break

    @case('videos')
    @case('reel')
    @php $vids = array_values(array_filter(array_map(fn ($u) => HS::videoEmbed($u), $d['items']))); $isReel = $sec['type'] === 'reel'; $fmt = $isReel ? 'portrait' : $d['format']; @endphp
    @if(count($vids))
    <section class="{{ $c['sec'] }}" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-hd" data-rv><h2 class="{{ $c['h2'] }}" id="{{ $hid }}">{{ $t('title') }}</h2></div>
            <div class="{{ $isReel ? 'cs-scroll cs-reel' : 'cs-vgrid' }}">
                @foreach($vids as $i => $v)
                <div class="cs-video is-{{ $fmt }}" data-rv style="--d:{{ min($i, 5) * 70 }}ms">
                    @if($v['kind'] === 'youtube')
                    <button type="button" data-cs-play="{{ $v['src'] }}" style="background-image:url('{{ $v['thumb'] }}')" aria-label="Lire la vidéo {{ $i + 1 }}"><span class="cs-play">{!! $play !!}</span></button>
                    @else
                    <a href="{{ $v['url'] }}" target="_blank" rel="noopener" style="background:linear-gradient(160deg,#222,#000)"><span class="cs-play">{!! $play !!}</span><span>Voir la vidéo sur {{ $v['site'] }}</span></a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @break

    @case('banner')
    @if($d['title'] !== '')
    <section style="padding:clamp(20px,3vw,32px) 0" aria-labelledby="{{ $hid }}">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-banner cs-tone-{{ $d['tone'] }}" data-rv>
                <div><h2 id="{{ $hid }}">{{ $d['title'] }}</h2>@if($d['text'] !== '')<p>{{ $d['text'] }}</p>@endif</div>
                @if($d['button'] !== '')<a class="cs-bbtn" href="{{ $d['link'] ?: $catalog }}" @if($d['link']) rel="noopener" @endif>{{ $d['button'] }}</a>@endif
            </div>
        </div>
    </section>
    @endif
    @break

    @case('promo')
    @if($d['code'] !== '')
    <section style="padding:clamp(14px,2vw,22px) 0" aria-label="Code promo">
        <div class="{{ $c['wrap'] }}">
            <div class="cs-promo cs-tone-{{ $d['tone'] }}" data-rv><span>{{ $t('text') }}</span><span class="cs-code">{{ $d['code'] }}<button type="button" data-cs-copy="{{ $d['code'] }}">Copier</button></span></div>
        </div>
    </section>
    @endif
    @break

    @case('trust')
    <section style="padding:clamp(18px,3vw,30px) 0" aria-label="Paiement">
        <div class="{{ $c['wrap'] }} cs-trust" data-rv>
            <p>@if($d['shield']){!! $ico('shield', 20) !!}@endif{{ $t('text') }}</p>
            @if($d['cash'] || $d['om'] || $d['momo'])
            <div class="cs-chips">@if($d['cash'])<span class="cs-chip-cash">Espèces à la livraison</span>@endif @if($d['om'])<span class="cs-chip-om">Orange Money</span>@endif @if($d['momo'])<span class="cs-chip-momo">MTN MoMo</span>@endif</div>
            @endif
        </div>
    </section>
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
@endswitch
