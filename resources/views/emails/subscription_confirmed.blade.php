<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paiement confirmé</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Arial,sans-serif;background:#f1f5f9;color:#0f172a}
.wrap{max-width:580px;margin:32px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
.header{background:linear-gradient(135deg,#4c1d95,#7c3aed);padding:36px 32px;text-align:center}
.header-ico{width:60px;height:60px;background:rgba(255,255,255,.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:14px}
.header h1{color:#fff;font-size:22px;font-weight:800;letter-spacing:-.3px}
.header p{color:rgba(255,255,255,.75);font-size:13px;margin-top:6px}
.body{padding:32px}
.greeting{font-size:15px;font-weight:700;color:#0f172a;margin-bottom:6px}
.intro{font-size:13.5px;color:#475569;line-height:1.6;margin-bottom:24px}
.plan-card{background:linear-gradient(135deg,#f5f3ff,#ede9fe);border:1px solid rgba(124,58,237,.2);border-radius:12px;padding:20px 22px;margin-bottom:24px}
.plan-card-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.plan-badge{background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;font-size:11px;font-weight:800;padding:4px 12px;border-radius:20px;text-transform:uppercase;letter-spacing:.6px}
.plan-card-title{font-size:16px;font-weight:800;color:#4c1d95}
.plan-details{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.plan-detail{background:#fff;border-radius:8px;padding:12px 14px;border:1px solid rgba(124,58,237,.1)}
.plan-detail-label{font-size:10px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.plan-detail-value{font-size:13px;font-weight:700;color:#0f172a}
.plan-detail.full{grid-column:1/-1}
.divider{height:1px;background:#e2e8f0;margin:24px 0}
.features{margin-bottom:24px}
.features h3{font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px}
.feature{display:flex;align-items:center;gap:9px;padding:7px 0;font-size:13px;color:#334155}
.feature-ico{width:20px;height:20px;background:#dcfce7;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0}
.cta{text-align:center;margin-bottom:24px}
.cta a{display:inline-block;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;font-size:14px;font-weight:700;padding:13px 32px;border-radius:9px;text-decoration:none;letter-spacing:-.1px}
.footer{background:#f8fafc;padding:20px 32px;border-top:1px solid #e2e8f0;text-align:center}
.footer p{font-size:11.5px;color:#94a3b8;line-height:1.7}
.footer strong{color:#64748b}
</style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <div class="header-ico">✅</div>
        <h1>Paiement confirmé !</h1>
        <p>Votre abonnement est maintenant actif</p>
    </div>

    <div class="body">

        <div class="greeting">Bonjour {{ $subscriberName }},</div>
        <p class="intro">
            Votre paiement a été reçu et validé avec succès.
            Votre plan <strong>{{ ucfirst($subscription->plan) }}</strong> est immédiatement actif.
            Vous pouvez dès maintenant profiter de toutes les fonctionnalités incluses.
        </p>

        <div class="plan-card">
            <div class="plan-card-head">
                <span class="plan-badge">{{ $subscription->plan }}</span>
                <span class="plan-card-title">
                    @if($subscription->plan === 'pro') Plan Pro — SaaS Boutique
                    @else Plan Business — SaaS Livraison
                    @endif
                </span>
            </div>
            <div class="plan-details">
                @php
                    $guineaCountries = ['Guinea','Guinée','GN','guinea','guinée','GUINEA','GUINÉE'];
                    $isGuinea = in_array(trim($userCountry), $guineaCountries);
                    $rate = config('genuispay.gnf_to_xof_rate', 13.15);
                    if ($isGuinea || empty($userCountry)) {
                        $displayAmount   = number_format($subscription->amount, 0, ',', ' ');
                        $displayCurrency = 'GNF';
                    } else {
                        $displayAmount   = number_format((int) ceil($subscription->amount / $rate), 0, ',', ' ');
                        $displayCurrency = 'XOF';
                    }
                @endphp
                <div class="plan-detail">
                    <div class="plan-detail-label">Montant payé</div>
                    <div class="plan-detail-value">{{ $displayAmount }} {{ $displayCurrency }}</div>
                </div>
                <div class="plan-detail">
                    <div class="plan-detail-label">Référence</div>
                    <div class="plan-detail-value" style="font-size:11px;word-break:break-all">{{ $subscription->payment_reference }}</div>
                </div>
                <div class="plan-detail">
                    <div class="plan-detail-label">Activé le</div>
                    <div class="plan-detail-value">{{ $subscription->started_at?->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
                </div>
                <div class="plan-detail">
                    <div class="plan-detail-label">Expire le</div>
                    <div class="plan-detail-value" style="color:#7c3aed">{{ $subscription->expires_at?->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
                </div>
            </div>
        </div>

        <div class="features">
            <h3>Ce qui est inclus dans votre plan</h3>
            @if($subscription->plan === 'pro')
                <div class="feature"><span class="feature-ico">✓</span> Produits illimités</div>
                <div class="feature"><span class="feature-ico">✓</span> Commandes illimitées ce mois</div>
                <div class="feature"><span class="feature-ico">✓</span> Accès à tous les livreurs</div>
                <div class="feature"><span class="feature-ico">✓</span> Tableau de bord avancé</div>
                <div class="feature"><span class="feature-ico">✓</span> Support prioritaire</div>
            @else
                <div class="feature"><span class="feature-ico">✓</span> Livreurs illimités</div>
                <div class="feature"><span class="feature-ico">✓</span> Zones de livraison illimitées</div>
                <div class="feature"><span class="feature-ico">✓</span> Commandes illimitées</div>
                <div class="feature"><span class="feature-ico">✓</span> Tableau de bord entreprise complet</div>
                <div class="feature"><span class="feature-ico">✓</span> Support prioritaire</div>
            @endif
        </div>

        <div class="cta">
            <a href="{{ $dashboardUrl }}">Accéder à mon dashboard →</a>
        </div>

        <div class="divider"></div>

        <p style="font-size:12px;color:#94a3b8;text-align:center;line-height:1.6">
            Votre abonnement sera automatiquement désactivé à son expiration.<br>
            Pour toute question, contactez notre support.
        </p>

    </div>

    <div class="footer">
        <p>
            <strong>{{ config('app.name', 'Shopio') }}</strong> — Plateforme double SaaS<br>
            Cet email a été envoyé automatiquement suite à votre paiement.<br>
            Merci de votre confiance.
        </p>
    </div>

</div>
</body>
</html>
