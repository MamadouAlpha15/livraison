@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp
@section('content')
@php
$isBusiness = $currentPlan === 'business';
$daysLeft   = $isBusiness ? (int) now()->diffInDays($company->plan_expires_at, false) : 0;
$bizXof     = number_format(config('genuispay.plans.business', 11400), 0, ',', ' ');
$bizGnf     = number_format(config('genuispay.plans_gnf.business', 150000), 0, ',', ' ');
$isGuinea   = ($company->country ?? '') === 'GN';
@endphp

<style>
*,*::before,*::after{box-sizing:border-box}
:root{
    --brand:#2563eb;--bdk:#1d4ed8;--blt:#3b82f6;
    --green:#10b981;--red:#ef4444;--amber:#f59e0b;
    --bg:#f1f5f9;--card:#fff;--bd:rgba(0,0,0,.08);
    --text:#0f172a;--muted:#64748b;
    --font:'Segoe UI',system-ui,sans-serif;
}
.upg-wrap{max-width:860px;margin:0 auto;padding:32px 16px}
.upg-head{text-align:center;margin-bottom:36px}
.upg-head h1{font-size:26px;font-weight:900;color:var(--text);margin:0 0 8px;letter-spacing:-.5px}
.upg-head p{font-size:14px;color:var(--muted);margin:0}
.active-banner{display:flex;align-items:center;gap:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:28px}
.active-banner svg{color:#16a34a;flex-shrink:0}
.active-banner-t{font-size:13px;font-weight:700;color:#15803d}
.active-banner-s{font-size:12px;color:#166534;margin-top:2px}
.plan-error{background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:24px;font-size:13px;color:#92400e;font-weight:600}
.plans{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:36px}
@media(max-width:600px){.plans{grid-template-columns:1fr}}
.plan-card{border-radius:16px;border:2px solid var(--bd);background:var(--card);overflow:hidden;transition:transform .18s}
.plan-card:hover{transform:translateY(-2px)}
.plan-card.featured{border-color:var(--brand);box-shadow:0 0 0 4px rgba(37,99,235,.08)}
.plan-head{padding:22px 22px 18px}
.plan-badge{display:inline-block;font-size:10px;font-weight:800;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px}
.plan-badge.free{background:rgba(100,116,139,.1);color:var(--muted)}
.plan-badge.business{background:rgba(37,99,235,.1);color:var(--brand)}
.plan-name{font-size:20px;font-weight:900;color:var(--text);margin-bottom:4px}
.plan-price{display:flex;align-items:baseline;gap:4px;margin:10px 0 4px}
.plan-price-v{font-size:28px;font-weight:900;color:var(--text);letter-spacing:-1px}
.plan-price-c{font-size:12px;color:var(--muted);font-weight:600}
.plan-period{font-size:11px;color:var(--muted)}
.plan-divider{height:1px;background:var(--bd);margin:16px 0}
.feat-list{padding:0 22px 22px;list-style:none;margin:0;display:flex;flex-direction:column;gap:9px}
.feat-item{display:flex;align-items:flex-start;gap:10px;font-size:13px}
.feat-ico{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.feat-ico.ok{background:#dcfce7;color:#16a34a}
.feat-ico.no{background:#fee2e2;color:#dc2626}
.feat-txt{color:var(--text);line-height:1.4}
.feat-txt.dim{color:var(--muted)}
.feat-limit{font-size:10.5px;font-weight:700;color:var(--amber);background:rgba(245,158,11,.1);padding:1px 6px;border-radius:10px;margin-left:4px}
.plan-btn-wrap{padding:0 22px 22px}
.plan-btn{display:block;width:100%;padding:13px;border-radius:10px;font-size:14px;font-weight:800;font-family:var(--font);text-align:center;border:none;cursor:pointer;text-decoration:none;transition:all .15s}
.plan-btn.business{background:linear-gradient(135deg,var(--brand),#1d4ed8);color:#fff}
.plan-btn.business:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(37,99,235,.4)}
.plan-btn.current{background:var(--bg);color:var(--muted);cursor:default;border:1.5px solid var(--bd)}
.faq{max-width:560px;margin:0 auto}
.faq h2{font-size:16px;font-weight:800;color:var(--text);margin-bottom:16px;text-align:center}
.faq-item{background:var(--card);border:1px solid var(--bd);border-radius:10px;margin-bottom:8px;overflow:hidden}
.faq-q{padding:13px 16px;font-size:13px;font-weight:700;color:var(--text);cursor:pointer;display:flex;justify-content:space-between;align-items:center}
.faq-a{padding:0 16px 13px;font-size:12.5px;color:var(--muted);line-height:1.6;display:none}
.faq-item.open .faq-a{display:block}
.faq-item.open .faq-arr{transform:rotate(180deg)}
.faq-arr{transition:transform .2s;font-size:12px}
</style>

<div class="upg-wrap">
    <div class="upg-head">
        <h1>Choisissez votre plan entreprise</h1>
        <p>Débloquez tout le potentiel de votre entreprise de livraison avec le Plan Business</p>
    </div>


    @if(session('plan_error'))
    <div class="plan-error">🔒 {{ session('plan_error') }}</div>
    @endif

    <div class="plans">

        {{-- Plan Gratuit --}}
        <div class="plan-card">
            <div class="plan-head">
                <div class="plan-badge free">Gratuit</div>
                <div class="plan-name">Plan Gratuit</div>
                <div class="plan-price">
                    <span class="plan-price-v">0</span>
                    <span class="plan-price-c">XOF</span>
                </div>
                <div class="plan-period">Pour toujours</div>
            </div>
            <div class="plan-divider" style="margin:0 22px"></div>
            <ul class="feat-list">
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt">Commandes <span class="feat-limit">max 10</span></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt">Chauffeurs <span class="feat-limit">max 1</span></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt">Zones de livraison <span class="feat-limit">max 5</span></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico no"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
                    <div class="feat-txt dim">Cartes en direct — <em>Plan Business</em></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico no"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
                    <div class="feat-txt dim">Rapports — <em>Plan Business</em></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico no"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
                    <div class="feat-txt dim">Gestion utilisateurs — <em>Plan Business</em></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico no"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
                    <div class="feat-txt dim">Statistiques &amp; Charts — <em>Plan Business</em></div>
                </li>
            </ul>
            <div class="plan-btn-wrap">
                <span class="plan-btn current">Plan actuel</span>
            </div>
        </div>

        {{-- Plan Business --}}
        <div class="plan-card featured">
            <div class="plan-head">
                <div class="plan-badge business">🚀 Business</div>
                <div class="plan-name">Plan Business</div>
                <div class="plan-price">
                    <span class="plan-price-v">{{ $bizXof }}</span>
                    <span class="plan-price-c">XOF</span>
                </div>
                @if($isGuinea)
                <div class="plan-period" style="color:#d97706;font-weight:700;margin-bottom:2px">≈ {{ $bizGnf }} GNF 🇬🇳</div>
                @endif
                <div class="plan-period">par mois · renouvellement manuel</div>
            </div>
            <div class="plan-divider" style="margin:0 22px"></div>
            <ul class="feat-list">
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt"><strong>Commandes illimitées</strong></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt"><strong>Chauffeurs illimités</strong></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt"><strong>Zones illimitées</strong></div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt">Cartes en direct</div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt">Rapports complets</div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt">Gestion utilisateurs</div>
                </li>
                <li class="feat-item">
                    <div class="feat-ico ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div class="feat-txt">Statistiques &amp; Charts complets</div>
                </li>
            </ul>
            <div class="plan-btn-wrap">
                @if($isBusiness)
                    <span class="plan-btn current">Plan actif ✓</span>
                @else
                    <a href="{{ route('payment.checkout', ['type'=>'company','id'=>$company->id]) }}"
                       class="plan-btn business">
                        Passer au Plan Business →
                    </a>
                @endif
            </div>
        </div>

    </div>

    {{-- FAQ --}}
    <div class="faq">
        <h2>Questions fréquentes</h2>
        <div class="faq-item" onclick="this.classList.toggle('open')">
            <div class="faq-q">Que se passe-t-il après 30 jours ? <span class="faq-arr">▾</span></div>
            <div class="faq-a">L'entreprise revient automatiquement au Plan Gratuit. Vos données sont conservées mais les fonctionnalités avancées sont bloquées jusqu'au prochain paiement.</div>
        </div>
        <div class="faq-item" onclick="this.classList.toggle('open')">
            <div class="faq-q">Mes chauffeurs sont-ils supprimés si je reviens au gratuit ? <span class="faq-arr">▾</span></div>
            <div class="faq-a">Non, vos données restent intactes. Seule la création de nouveaux chauffeurs sera limitée à 1 maximum.</div>
        </div>
        <div class="faq-item" onclick="this.classList.toggle('open')">
            <div class="faq-q">Comment payer ? <span class="faq-arr">▾</span></div>
            <div class="faq-a">Orange Money, Waves, Mobile Money et Carte Visa — via GenuisPay, sécurisé et instantané.</div>
        </div>
    </div>
</div>
@endsection
