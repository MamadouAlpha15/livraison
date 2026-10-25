{{--
    Paiement simulé d'une campagne (hors production, quand aucune clé ChapChap Pay n'est configurée).
    Remplace la page de paiement Orange Money / MTN pour tester le parcours de bout en bout.
--}}
@extends('layouts.app')

@section('title', 'Paiement de test')

@php
    $bodyClass = 'is-dashboard';
    use App\Support\Campaigns\CampaignPricing as P;
@endphp

@push('styles')
@include('boutique.campaigns._styles')
@include('boutique._partials.sv-theme')
@endpush

@section('content')
<main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 16px">
    <section class="cp-card" style="max-width:440px;width:100%;padding:28px;display:grid;gap:16px">
        <span class="cp-st warn" style="justify-self:start">Paiement de test · aucun argent réel</span>
        <h1 style="margin:0;font-size:22px;font-weight:800">Payer {{ P::format($payment->amount_gnf) }}</h1>
        <p style="margin:0;color:var(--text-2);font-size:14px;line-height:1.5">
            Campagne n°{{ $payment->ad_campaign_id }} · {{ $payment->campaign->package->name }}.
            En production, cette page est celle de ChapChap Pay, où le vendeur paie avec Orange Money ou MTN.
        </p>
        @if($payment->status === 'en_attente')
        <form method="POST" action="{{ route('boutique.campaigns.fake-pay.confirm', $payment) }}" style="display:grid;gap:10px">
            @csrf
            <input type="hidden" name="result" value="paye">
            <button class="cp-btn cp-btn-blue" name="operator" value="orange_money">Simuler un paiement Orange Money réussi</button>
            <button class="cp-btn cp-btn-line" name="operator" value="mtn_momo">Simuler un paiement MTN réussi</button>
        </form>
        <form method="POST" action="{{ route('boutique.campaigns.fake-pay.confirm', $payment) }}">
            @csrf <input type="hidden" name="result" value="echoue">
            <button class="cp-btn cp-btn-danger" style="width:100%">Simuler un paiement échoué</button>
        </form>
        @else
        <p style="margin:0;font-weight:700">Ce paiement est déjà traité.</p>
        <a class="cp-btn cp-btn-line" href="{{ route('boutique.campaigns.show', $payment->ad_campaign_id) }}">Retour à la campagne</a>
        @endif
    </section>
</main>
@endsection
