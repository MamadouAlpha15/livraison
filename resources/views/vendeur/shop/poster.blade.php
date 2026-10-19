{{--
    Affiche A5 à imprimer (Faire grandir ma vitrine → Partage → QR code → Affiche A5).
    Page autonome : logo, nom, cible (boutique, catégorie ou produit), QR code et lien. S'imprime d'un clic.
    Variables : $shop, $title, $sub, $url, $qrSvg, $logoUrl, $initials, $accent
--}}
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Affiche · {{ $title }}</title>
<link href="{{ \App\Support\Assets::v('fonts/dashboard-fonts.css') }}" rel="stylesheet">
<style>
@page { size: A5 portrait; margin: 0; }
* { box-sizing: border-box; }
html, body { margin: 0; background: #DCE6F5; color: #16162A; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.tools { display: flex; justify-content: center; gap: 10px; padding: 16px; }
.tools button, .tools a { min-height: 44px; padding: 0 20px; border: 0; border-radius: 999px; font: 700 14px inherit; font-family: inherit; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
.tools button { background: #F59E0B; color: #16162A; }
.tools a { background: #fff; color: #16162A; box-shadow: inset 0 0 0 1.5px #E3E8F2; }
.sheet { width: 148mm; height: 210mm; margin: 0 auto 24px; background: #fff; display: flex; flex-direction: column; align-items: center; text-align: center; padding: 14mm 12mm 10mm; position: relative; overflow: hidden; box-shadow: 0 30px 60px -30px rgba(11,30,61,.5); }
.sheet::before { content: ''; position: absolute; inset: 0 0 auto; height: 42mm; background: radial-gradient(120% 140% at 100% 0%, rgba(31,124,246,.45), transparent 55%), linear-gradient(135deg, #0B1E3D, #132B52); }
.logo { position: relative; width: 26mm; height: 26mm; border-radius: 50%; border: 4px solid #fff; background: #fff; overflow: hidden; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 24px rgba(11,30,61,.25); margin-top: 8mm; }
.logo img { width: 100%; height: 100%; object-fit: cover; }
.logo span { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 10mm; color: #fff; background: {{ $accent }}; }
.shop { margin: 4mm 0 0; font-size: 6mm; font-weight: 800; letter-spacing: -.02em; }
.title { margin: 2mm 0 0; font-size: 8.5mm; font-weight: 800; letter-spacing: -.02em; line-height: 1.1; color: #1260C9; text-wrap: balance; }
.sub { margin: 2mm 0 0; font-size: 4.2mm; font-weight: 700; color: #45455E; }
.qr { position: relative; width: 82mm; height: 82mm; margin: 6mm 0 0; }
.qr svg { width: 100%; height: 100%; display: block; }
.qr i { position: absolute; left: 50%; top: 50%; width: 17mm; height: 17mm; transform: translate(-50%, -50%); border-radius: 3mm; border: 1.6mm solid #fff; overflow: hidden; background: #1F7CF6; display: flex; align-items: center; justify-content: center; color: #fff; font-style: normal; font-weight: 800; font-size: 5mm; }
.qr i img { width: 100%; height: 100%; object-fit: cover; }
.scan { margin: 5mm 0 0; font-size: 5mm; font-weight: 800; }
.url { margin: 1.5mm 0 0; font-size: 3.4mm; color: #45455E; word-break: break-all; }
.foot { margin-top: auto; display: flex; gap: 3mm; flex-wrap: wrap; justify-content: center; }
.foot span { padding: 1.6mm 3.4mm; border-radius: 999px; background: #EAF2FF; color: #1260C9; font-size: 3.2mm; font-weight: 800; }
@media print { html, body { background: #fff; } .tools { display: none; } .sheet { margin: 0; box-shadow: none; } }
</style>
</head>
<body>
<div class="tools"><button type="button" onclick="window.print()">Imprimer l’affiche (A5)</button><a href="{{ route('boutique.grow', ['onglet' => 'partage']) }}">Retour</a></div>
<main class="sheet">
    <div class="logo">@if($logoUrl)<img src="{{ $logoUrl }}" alt="">@else<span>{{ $initials }}</span>@endif</div>
    @if($title !== $shop->name)<p class="shop">{{ $shop->name }}</p>@endif
    <h1 class="title">{{ $title }}</h1>
    @if($sub)<p class="sub">{{ $sub }}</p>@endif
    <div class="qr">{!! $qrSvg !!}<i>@if($logoUrl)<img src="{{ $logoUrl }}" alt="">@else{{ $initials }}@endif</i></div>
    <p class="scan">Scannez pour commander</p>
    <p class="url">{{ preg_replace('#^https?://#', '', $url) }}</p>
    <div class="foot">@if($shop->is_approved)<span>Boutique vérifiée</span>@endif<span>Paiement à la livraison</span><span>Shopio</span></div>
</main>
</body>
</html>
