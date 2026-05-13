@extends('layouts.app')
@php $bodyClass = 'is-dashboard'; @endphp

@push('styles')
<style>
*,*::before,*::after { box-sizing:border-box; }

:root {
    --brand:#7c3aed; --brand-lt:#6d28d9; --brand-dk:#5b21b6;
    --bg:#f1f5f9; --surface:#ffffff; --surface2:#f8fafc;
    --border:rgba(0,0,0,.08);
    --text:#0f172a; --muted:#64748b;
    --green:#10b981; --amber:#f59e0b;
    --font:'Segoe UI',system-ui,sans-serif;
}

body {
    margin:0; font-family:var(--font);
    background:var(--bg); color:var(--text);
    min-height:100vh; -webkit-font-smoothing:antialiased;
}

/* ── Grille de fond ── */
body::before {
    content:''; position:fixed; inset:0; pointer-events:none;
    background-image:
        linear-gradient(rgba(124,58,237,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(124,58,237,.05) 1px, transparent 1px);
    background-size:48px 48px;
}

/* ── Orbe lumineux ── */
body::after {
    content:''; position:fixed;
    top:-120px; left:50%; transform:translateX(-50%);
    width:700px; height:400px; border-radius:50%;
    background:radial-gradient(ellipse, rgba(124,58,237,.08) 0%, transparent 70%);
    pointer-events:none;
}

/* ── Wrapper ── */
.wa-wrap {
    min-height:100vh;
    display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    padding:32px 20px;
    position:relative; z-index:1;
}

/* ── Card principale ── */
.wa-card {
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:20px;
    max-width:560px; width:100%;
    overflow:hidden;
    box-shadow:0 8px 32px rgba(0,0,0,.1), 0 0 0 1px rgba(124,58,237,.08);
}

/* ── Header card ── */
.wa-card-top {
    background:linear-gradient(135deg,#1e1b4b 0%,#2d2470 50%,#4c1d95 100%);
    padding:36px 32px 28px;
    text-align:center;
    position:relative; overflow:hidden;
}
.wa-card-top::before {
    content:''; position:absolute; inset:0;
    background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),
                     linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);
    background-size:32px 32px; pointer-events:none;
}

/* ── Icône animée ── */
.wa-icon-wrap {
    width:80px; height:80px; border-radius:50%;
    background:rgba(255,255,255,.1);
    border:2px solid rgba(255,255,255,.2);
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 20px;
    position:relative; z-index:1;
    animation:pulse-ring 2.5s ease-in-out infinite;
}
@keyframes pulse-ring {
    0%,100% { box-shadow:0 0 0 0 rgba(167,139,250,.4), 0 0 0 0 rgba(167,139,250,.2); }
    50%      { box-shadow:0 0 0 12px rgba(167,139,250,.15), 0 0 0 24px rgba(167,139,250,.05); }
}
.wa-icon { font-size:36px; animation:spin-slow 4s linear infinite; display:block; }
@keyframes spin-slow {
    0%   { transform:rotate(0deg); }
    25%  { transform:rotate(0deg); }
    50%  { transform:rotate(180deg); }
    75%  { transform:rotate(180deg); }
    100% { transform:rotate(360deg); }
}

.wa-title {
    font-size:22px; font-weight:900; color:#fff;
    letter-spacing:-.4px; margin-bottom:8px;
    position:relative; z-index:1;
}
.wa-subtitle {
    font-size:13px; color:rgba(255,255,255,.6);
    line-height:1.6; position:relative; z-index:1;
}

/* ── Corps card ── */
.wa-card-body { padding:28px 32px 32px; }

/* ── Infos entreprise ── */
.wa-company-box {
    display:flex; align-items:center; gap:14px;
    background:var(--surface2); border:1px solid var(--border);
    border-radius:12px; padding:16px 18px; margin-bottom:24px;
    box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.wa-company-av {
    width:46px; height:46px; border-radius:12px; flex-shrink:0;
    background:linear-gradient(135deg,#7c3aed,#4f46e5);
    display:flex; align-items:center; justify-content:center;
    font-size:20px; font-weight:800; color:#fff;
}
.wa-company-name { font-size:15px; font-weight:800; color:var(--text); }
.wa-company-sub  { font-size:12px; color:var(--muted); margin-top:3px; }

/* ── Étapes ── */
.wa-steps { display:flex; flex-direction:column; gap:0; margin-bottom:26px; }
.wa-step {
    display:flex; align-items:flex-start; gap:14px;
    padding:14px 0;
    border-bottom:1px solid var(--border);
    position:relative;
}
.wa-step:last-child { border-bottom:none; padding-bottom:0; }
.wa-step-line {
    position:absolute; left:17px; top:36px;
    width:2px; height:calc(100% - 8px);
    background:var(--border);
}
.wa-step:last-child .wa-step-line { display:none; }

.wa-step-dot {
    width:36px; height:36px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; font-weight:800;
    position:relative; z-index:1;
}
.wa-step-dot.done {
    background:rgba(16,185,129,.15); border:2px solid rgba(16,185,129,.3);
    color:var(--green);
}
.wa-step-dot.active {
    background:rgba(124,58,237,.2); border:2px solid rgba(124,58,237,.4);
    color:var(--brand-lt);
    animation:pulse-dot 1.8s ease-in-out infinite;
}
.wa-step-dot.pending {
    background:#f1f5f9; border:2px solid var(--border);
    color:var(--muted);
}
@keyframes pulse-dot {
    0%,100% { box-shadow:0 0 0 0 rgba(124,58,237,.4); }
    50%      { box-shadow:0 0 0 6px rgba(124,58,237,.0); }
}
.wa-step-info { flex:1; padding-top:6px; }
.wa-step-label { font-size:13.5px; font-weight:700; color:var(--text); }
.wa-step-desc  { font-size:12px; color:var(--muted); margin-top:2px; line-height:1.5; }
.wa-step-done-badge {
    font-size:10.5px; font-weight:700; color:var(--green);
    background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.2);
    padding:2px 8px; border-radius:20px; margin-top:4px;
    display:inline-block;
}
.wa-step-wait-badge {
    font-size:10.5px; font-weight:700; color:var(--brand-lt);
    background:rgba(124,58,237,.1); border:1px solid rgba(124,58,237,.2);
    padding:2px 8px; border-radius:20px; margin-top:4px;
    display:inline-block; animation:blink 2s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.5} }

/* ── Alerte email ── */
.wa-email-note {
    display:flex; align-items:flex-start; gap:12px;
    background:rgba(245,158,11,.06); border:1px solid rgba(245,158,11,.2);
    border-radius:10px; padding:14px 16px; margin-bottom:24px;
}
.wa-email-ico { font-size:20px; flex-shrink:0; margin-top:1px; }
.wa-email-txt { font-size:12.5px; color:#92400e; line-height:1.6; }
.wa-email-txt strong { color:#b45309; }

/* ── Actions ── */
.wa-actions {
    display:flex; gap:10px; flex-wrap:wrap;
}
.wa-btn {
    flex:1; min-width:120px;
    display:inline-flex; align-items:center; justify-content:center; gap:7px;
    padding:12px 18px; border-radius:10px;
    font-size:13px; font-weight:700; font-family:var(--font);
    cursor:pointer; transition:all .15s; text-decoration:none; border:none;
}
.wa-btn-primary {
    background:linear-gradient(135deg,var(--brand),var(--brand-dk));
    color:#fff; border:1px solid var(--brand-dk);
}
.wa-btn-primary:hover {
    transform:translateY(-1px);
    box-shadow:0 6px 20px rgba(124,58,237,.4);
}
.wa-btn-ghost {
    background:#f8fafc; color:var(--muted);
    border:1px solid var(--border);
}
.wa-btn-ghost:hover { background:#f1f5f9; color:var(--text); }

/* ── Footer ── */
.wa-footer {
    text-align:center; margin-top:28px;
    font-size:12px; color:var(--muted);
}
.wa-footer a { color:var(--brand-lt); text-decoration:none; }
.wa-footer a:hover { text-decoration:underline; }

/* ── Responsive ── */
@media(max-width:520px) {
    .wa-card-top  { padding:28px 20px 22px; }
    .wa-card-body { padding:22px 20px 24px; }
    .wa-icon-wrap { width:68px; height:68px; }
    .wa-icon      { font-size:28px; }
    .wa-title     { font-size:19px; }
    .wa-btn       { font-size:12.5px; padding:11px 14px; }
}
@media(max-width:380px) {
    .wa-actions { flex-direction:column; }
    .wa-btn     { flex:none; width:100%; }
}
</style>
@endpush

@section('content')
@php
    $nameParts = explode(' ', $company->name ?? 'E');
    $initials  = strtoupper(substr($nameParts[0],0,1)) . strtoupper(substr($nameParts[1] ?? '',0,1));
    $createdAt = $company->created_at ?? now();
@endphp

<div class="wa-wrap">
    <div class="wa-card">

        {{-- ── Header animé ── --}}
        <div class="wa-card-top">
            <div class="wa-icon-wrap">
                <span class="wa-icon">⏳</span>
            </div>
            <div class="wa-title">Compte en attente d'approbation</div>
            <div class="wa-subtitle">
                Votre entreprise a bien été enregistrée.<br>
                Un administrateur va examiner votre dossier sous peu.
            </div>
        </div>

        {{-- ── Corps ── --}}
        <div class="wa-card-body">

            {{-- Infos entreprise --}}
            <div class="wa-company-box">
                <div class="wa-company-av">{{ $initials ?: '🏢' }}</div>
                <div>
                    <div class="wa-company-name">{{ $company->name ?? 'Votre entreprise' }}</div>
                    <div class="wa-company-sub">
                        Créée {{ $createdAt->diffForHumans() }}
                        @if($company->country) · {{ $company->country }} @endif
                    </div>
                </div>
            </div>

            {{-- Étapes --}}
            <div class="wa-steps">

                <div class="wa-step">
                    <div class="wa-step-line"></div>
                    <div class="wa-step-dot done">✓</div>
                    <div class="wa-step-info">
                        <div class="wa-step-label">Compte créé</div>
                        <div class="wa-step-desc">Votre entreprise a été enregistrée avec succès.</div>
                        <span class="wa-step-done-badge">✅ Complété</span>
                    </div>
                </div>

                <div class="wa-step">
                    <div class="wa-step-line"></div>
                    <div class="wa-step-dot active">⋯</div>
                    <div class="wa-step-info">
                        <div class="wa-step-label">Vérification en cours</div>
                        <div class="wa-step-desc">Notre équipe examine votre dossier et vérifie vos informations.</div>
                        <span class="wa-step-wait-badge">⏳ En attente</span>
                    </div>
                </div>

                <div class="wa-step">
                    <div class="wa-step-dot pending">3</div>
                    <div class="wa-step-info">
                        <div class="wa-step-label">Accès activé</div>
                        <div class="wa-step-desc">Vous pourrez ajouter des chauffeurs, gérer vos livraisons et recevoir des demandes.</div>
                    </div>
                </div>

            </div>

           

            {{-- Actions --}}
            <div class="wa-actions">
                <button type="button" class="wa-btn wa-btn-primary" onclick="window.location.reload()">
                    🔄 Vérifier le statut
                </button>
                <form method="POST" action="{{ route('logout') }}" style="flex:1;display:contents">
                    @csrf
                    <button type="submit" class="wa-btn wa-btn-ghost">
                        ⎋ Se déconnecter
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- Footer --}}
    <div class="wa-footer">
       
        &nbsp;·&nbsp;
        <a href="{{ route('welcome') }}">Retour à l'accueil</a>
    </div>

</div>
@endsection

@push('scripts')
<script>
/* Auto-refresh toutes les 60s pour détecter l'approbation */
setTimeout(() => window.location.reload(), 60000);
</script>
@endpush
