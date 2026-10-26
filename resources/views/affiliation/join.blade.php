{{--
    Page publique « Devenir affilié » (/devenir-affilie), liée depuis l'accueil : présentation du programme
    (chiffres tirés des réglages réels) et inscription d'une personne externe. Données : AffiliateJoinController@show
--}}
@extends('layouts.app')

@section('title', 'Devenir affilié Shopio')

@php
    $bodyClass = 'is-dashboard'; // page autonome : sans la barre et le pied de page du gabarit
    use App\Support\IconLibrary as Ico;
    use App\Support\Money;
    $rate = rtrim(rtrim(number_format((float) $settings->default_rate, 2, ',', ''), '0'), ',');
    $user = auth()->user();
    // Exemple de calcul avec le vrai prix mensuel du plan Pro
    $proPrice = (int) config('chapchappay.plans.pro', 150000);
    $perMonth = (int) floor($proPrice * (float) $settings->default_rate / 100);
@endphp

@push('styles')
@include('boutique._partials.sv-theme')
@include('affiliation._styles')
<style>
.aj-bar { position: sticky; top: env(safe-area-inset-top, 0px); z-index: 40; background: var(--surface); border-bottom: 1px solid var(--border); }
.aj-bar-in { max-width: 1120px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 12px max(16px, env(safe-area-inset-right, 0px)) 12px max(16px, env(safe-area-inset-left, 0px)); }
.aj-brand { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text); font-weight: 800; font-size: 18px; }
.aj-brand img { width: 36px; height: 36px; border-radius: 10px; }
.aj-brand b { color: var(--sv-blue); }
.aj-page { max-width: 1120px; margin: 0 auto; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr); gap: 32px; align-items: start;
    padding: 40px max(16px, env(safe-area-inset-right, 0px)) 72px max(16px, env(safe-area-inset-left, 0px)); }
.aj-intro { display: grid; gap: 22px; }
.aj-eyebrow { display: inline-flex; align-items: center; gap: 8px; justify-self: start; padding: 6px 12px; border-radius: 999px; background: var(--sv-amb50); color: var(--sv-amb-ink); font-size: 13px; font-weight: 800; }
.aj-h1 { margin: 0; font-size: 40px; line-height: 1.12; font-weight: 800; letter-spacing: -.03em; text-wrap: balance; }
.aj-h1 span { color: var(--sv-blue); }
.aj-lead { margin: 0; font-size: 17px; line-height: 1.6; color: var(--text-2); max-width: 56ch; }
.aj-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.aj-fact { padding: 16px; display: grid; gap: 4px; }
.aj-fact strong { font-size: 24px; font-weight: 800; color: var(--sv-btn); font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
.aj-fact span { font-size: 13px; color: var(--text-2); line-height: 1.4; }
.aj-example { padding: 16px 18px; border-radius: 16px; background: var(--sv-b50); color: var(--text); font-size: 14.5px; line-height: 1.55; }
.aj-card { padding: 26px; display: grid; gap: 16px; position: sticky; top: 84px; }
.aj-card h2 { margin: 0; font-size: 22px; font-weight: 800; }
.aj-card p { margin: 0; color: var(--text-2); font-size: 14px; }
.aj-form { display: grid; gap: 14px; }
.aj-err { color: var(--sv-err); font-size: 13px; font-weight: 600; }
.aj-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13.5px; color: var(--text-2); line-height: 1.45; }
.aj-check input { width: 20px; height: 20px; margin: 0; flex: none; accent-color: var(--sv-btn); }
.aj-check a, .aj-login a { color: var(--sv-btn); font-weight: 700; }
.aj-login { font-size: 14px; color: var(--text-2); text-align: center; }
.aj-submit { width: 100%; min-height: 50px; font-size: 16px; }
.aj-jump { display: none; justify-self: start; }
#inscription { scroll-margin-top: 80px; }
@media (max-width: 900px) {
    .aj-page { grid-template-columns: 1fr; padding-top: 24px; gap: 24px; }
    .aj-card { position: static; padding: 20px; }
    .aj-h1 { font-size: 30px; }
    .aj-jump { display: inline-flex; }
}
@media (max-width: 520px) { .aj-facts { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<header class="aj-bar">
    <div class="aj-bar-in">
        <a href="{{ url('/') }}" class="aj-brand"><img src="{{ \App\Support\Assets::v('images/shopio-logo-192.png') }}" alt="" width="36" height="36"><span>Shop<b>io</b></span></a>
        @guest
            <a href="{{ route('login') }}" class="cp-btn cp-btn-line cp-btn-sm">Connexion</a>
        @endguest
    </div>
</header>

<main class="aj-page">
    <div class="aj-intro">
        <span class="aj-eyebrow">{!! Ico::svg('gift', '', 16) !!} Programme d'affiliation</span>
        <h1 class="aj-h1">Fais connaître Shopio et <span>gagne de l'argent</span></h1>
        <p class="aj-lead">Tu connais des commerçants qui vendent sur WhatsApp, au marché ou en boutique ? Partage-leur ton lien.
            Quand ils ouvrent leur boutique sur Shopio et paient leur abonnement, tu touches une commission. Pas besoin d'avoir une boutique.</p>
        @guest<a href="#inscription" class="cp-btn cp-btn-blue aj-jump">{!! Ico::svg('gift', '', 18) !!} Je m'inscris</a>@endguest

        <div class="aj-facts">
            <div class="cp-card aj-fact"><strong>{{ $rate }} %</strong><span>de chaque abonnement payé par tes filleuls</span></div>
            <div class="cp-card aj-fact"><strong>{{ $settings->commission_months }} mois</strong><span>de commissions après leur premier paiement</span></div>
            <div class="cp-card aj-fact"><strong>{{ Money::gnf($settings->payout_threshold) }}</strong><span>suffisent pour retirer par Mobile Money</span></div>
        </div>

        @if($perMonth > 0)
        <p class="aj-example">
            <strong>Exemple :</strong> une boutique que tu as invitée paie le plan Pro à {{ Money::gnf($proPrice) }} par mois.
            Tu reçois {{ Money::gnf($perMonth) }} chaque mois où elle paie, pendant {{ $settings->commission_months }} mois.
        </p>
        @endif

        <section class="cp-card af-how" aria-labelledby="aj-how">
            <h2 id="aj-how" class="af-h2">Comment ça marche</h2>
            <ol class="af-steps">
                <li><strong>Inscris-toi</strong><span>L'équipe Shopio vérifie ta demande, puis tu reçois ton code, ton lien et ton QR code.</span></li>
                <li><strong>Partage ton lien</strong><span>Sur WhatsApp, Facebook, ou en personne. Il reste valable {{ $settings->cookie_days }} jours.</span></li>
                <li><strong>Ils ouvrent leur boutique</strong><span>Ton code est déjà rempli. Ils peuvent aussi le saisir eux-mêmes.</span></li>
                <li><strong>Tu es payé</strong><span>Commission disponible {{ $settings->validation_days }} jours après chaque paiement, versée sur ton Mobile Money.</span></li>
            </ol>
        </section>
    </div>

    <aside class="cp-card aj-card" id="inscription" aria-labelledby="aj-form-title">
        @guest
            <h2 id="aj-form-title">Créer mon compte affilié</h2>
            <p>Gratuit. Un code de vérification est envoyé à ton adresse e-mail. L'équipe Shopio valide ensuite ton compte avant que ton lien fonctionne.</p>
            @if($errors->any())
                <div class="cp-flash cp-flash-err" role="alert">Vérifie les champs en rouge.</div>
            @endif
            <form method="POST" action="{{ route('affiliation.join.store') }}" class="aj-form" novalidate>
                @csrf
                <div class="af-field">
                    <label for="ajName">Nom complet</label>
                    <input id="ajName" name="name" class="af-input" autocomplete="name" required maxlength="120" value="{{ old('name') }}">
                    @error('name')<span class="aj-err">{{ $message }}</span>@enderror
                </div>
                <div class="af-field">
                    <label for="ajPhone">Téléphone (Mobile Money)</label>
                    <input id="ajPhone" name="phone" type="tel" inputmode="tel" class="af-input" autocomplete="tel" required placeholder="622 45 81 37" value="{{ old('phone') }}">
                    @error('phone')<span class="aj-err">{{ $message }}</span>@enderror
                </div>
                <div class="af-field">
                    <label for="ajEmail">E-mail</label>
                    <input id="ajEmail" name="email" type="email" class="af-input" autocomplete="email" required value="{{ old('email') }}">
                    @error('email')<span class="aj-err">{{ $message }}</span>@enderror
                </div>
                <div class="af-field">
                    <label for="ajPass">Mot de passe</label>
                    <input id="ajPass" name="password" type="password" class="af-input" autocomplete="new-password" required minlength="8">
                    @error('password')<span class="aj-err">{{ $message }}</span>@enderror
                </div>
                <div class="af-field">
                    <label for="ajPass2">Confirme le mot de passe</label>
                    <input id="ajPass2" name="password_confirmation" type="password" class="af-input" autocomplete="new-password" required minlength="8">
                </div>
                <label class="aj-check"><input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
                    <span>J'accepte les <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener">conditions d'utilisation</a>. Je ne m'inscris pas moi-même avec mon propre code.</span></label>
                @error('terms')<span class="aj-err">{{ $message }}</span>@enderror
                <button type="submit" class="cp-btn cp-btn-blue aj-submit">{!! Ico::svg('gift', '', 18) !!} Devenir affilié</button>
            </form>
            <p class="aj-login">Déjà affilié ? <a href="{{ route('login') }}">Connecte-toi</a></p>
        @else
            @if($user->role === 'affilie')
                <h2 id="aj-form-title">Tu es déjà inscrit</h2>
                <p>Retrouve dans ton tableau de bord l'état de ta demande, puis ton lien, ton QR code et tes commissions.</p>
                <a href="{{ route('affilie.dashboard') }}" class="cp-btn cp-btn-blue aj-submit">Mon tableau de bord</a>
            @elseif($user->role === 'admin')
                <h2 id="aj-form-title">Tu as déjà une boutique</h2>
                <p>Le parrainage est déjà dans ton espace vendeur : tu y gagnes aussi un mois d'abonnement offert pour chaque boutique qui paie.</p>
                <a href="{{ route('vendeur.parrainage') }}" class="cp-btn cp-btn-blue aj-submit">Mon parrainage</a>
            @else
                <h2 id="aj-form-title">Un compte à part</h2>
                <p>Le programme d'affiliation demande un compte affilié séparé de ton compte actuel. Déconnecte-toi, puis reviens sur cette page pour t'inscrire avec une autre adresse e-mail.</p>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="cp-btn cp-btn-line aj-submit">Me déconnecter</button>
                </form>
            @endif
        @endguest
    </aside>
</main>
@endsection
