<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paiement sécurisé — {{ config('app.name') }}</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --brand:#6366f1;--bdk:#4f46e5;
    --bg:#f1f5f9;--card:#fff;--bd:#e2e8f0;
    --text:#0f172a;--text2:#475569;--muted:#94a3b8;
    --font:'Segoe UI',system-ui,-apple-system,sans-serif;
}
body{font-family:var(--font);background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}

.checkout-wrap{width:100%;max-width:440px;}

.app-header{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:24px;}
.app-logo{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(99,102,241,.35);}
.app-logo svg{color:#fff;}
.app-name{font-size:17px;font-weight:900;color:var(--text);letter-spacing:-.3px;}

.pay-card{background:var(--card);border-radius:20px;border:1px solid var(--bd);box-shadow:0 8px 40px rgba(0,0,0,.09);overflow:hidden;}

/* ── ORDER SUMMARY ── */
.order-head{
    background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);
    padding:26px;color:#fff;position:relative;overflow:hidden;
}
.order-head::before{content:'';position:absolute;right:-40px;top:-40px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.06);}
.order-head::after{content:'';position:absolute;right:40px;bottom:-60px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.04);}

.order-subscriber{display:flex;align-items:center;gap:10px;margin-bottom:18px;position:relative;z-index:1;}
.order-av{width:42px;height:42px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:800;color:#fff;flex-shrink:0;}
.order-sub-name{font-size:14px;font-weight:800;color:#fff;}
.order-sub-type{font-size:11px;color:rgba(255,255,255,.7);margin-top:2px;}

.order-amount-row{display:flex;align-items:flex-end;justify-content:space-between;position:relative;z-index:1;}
.order-amount-v{font-size:36px;font-weight:900;color:#fff;letter-spacing:-1px;line-height:1;}
.order-amount-c{font-size:15px;font-weight:700;color:rgba(255,255,255,.85);margin-left:4px;}
.order-amount-note{font-size:11px;color:rgba(255,255,255,.65);margin-top:5px;}
.order-plan-badge{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.25);border-radius:20px;padding:5px 14px;font-size:11px;font-weight:800;color:#fff;letter-spacing:.3px;white-space:nowrap;}

/* ── BODY ── */
.form-body{padding:26px;}

/* ── INFO MÉTHODES ── */
.methods-info{
    background:#f8fafc;border:1px solid var(--bd);border-radius:13px;
    padding:16px;margin-bottom:22px;
}
.methods-info-title{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);margin-bottom:12px;}
.methods-grid{display:flex;flex-wrap:wrap;gap:8px;}
.method-chip{
    display:flex;align-items:center;gap:6px;
    padding:6px 12px;border-radius:20px;
    font-size:12px;font-weight:700;border:1.5px solid var(--bd);
    background:#fff;color:var(--text2);
}
.method-chip span{font-size:15px;}

/* ── BOUTON PAYER ── */
.pay-btn{
    width:100%;padding:16px;border-radius:12px;
    background:linear-gradient(135deg,var(--brand),#7c3aed);
    border:none;color:#fff;font-size:15px;font-weight:800;
    font-family:var(--font);cursor:pointer;
    display:flex;align-items:center;justify-content:center;gap:10px;
    box-shadow:0 6px 22px rgba(99,102,241,.38);
    transition:all .15s;
}
.pay-btn:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(99,102,241,.5);}
.pay-btn:disabled{opacity:.55;transform:none;cursor:not-allowed;}

.err-box{background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;padding:11px 14px;font-size:13px;color:#dc2626;font-weight:600;margin-bottom:18px;display:flex;align-items:center;gap:8px;}

.pay-footer{margin-top:16px;text-align:center;}
.secure-row{display:flex;align-items:center;justify-content:center;gap:16px;margin-bottom:8px;}
.secure-item{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted);}
.powered{font-size:11px;color:var(--muted);}

@media(max-width:400px){
    .form-body{padding:18px;}
    .order-head{padding:20px;}
}
</style>
</head>
<body>

<div class="checkout-wrap">

    <div class="app-header">
        <div class="app-logo">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2 3 14h9l-1 8L21 10h-9l1-8z"/></svg>
        </div>
        <div class="app-name">{{ config('app.name') }}</div>
    </div>

    <div class="pay-card">

        {{-- ── Résumé ── --}}
        <div class="order-head">
            <div class="order-subscriber">
                <div class="order-av">{{ strtoupper(substr($subscriber->name, 0, 1)) }}</div>
                <div>
                    <div class="order-sub-name">{{ $subscriber->name }}</div>
                    <div class="order-sub-type">{{ $type === 'shop' ? '🛍 Boutique' : '🚚 Entreprise de livraison' }}</div>
                </div>
            </div>
            <div class="order-amount-row">
                <div>
                    <div>
                        <span class="order-amount-v">{{ number_format($amountXof, 0, ',', ' ') }}</span>
                        <span class="order-amount-c">XOF</span>
                    </div>
                    @if($userCountry === 'GN')
                    <div class="order-amount-note">≈ {{ number_format($amount, 0, ',', ' ') }} GNF 🇬🇳 — équivalent en francs guinéens</div>
                    @endif
                    <div class="order-amount-note">Valable 30 jours · Renouvellement manuel</div>
                </div>
                <div class="order-plan-badge">⚡ Plan {{ ucfirst($plan) }}</div>
            </div>
        </div>

        {{-- ── Corps ── --}}
        <div class="form-body">

            @error('payment')
            <div class="err-box">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ $message }}
            </div>
            @enderror

            {{-- Méthodes disponibles (informatif) --}}
            <div class="methods-info">
                <div class="methods-info-title">Moyens de paiement acceptés</div>
                <div class="methods-grid">
                    <div class="method-chip"><span>🟠</span> Orange Money</div>
                    <div class="method-chip"><span>🟡</span> MTN MoMo</div>
                    <div class="method-chip"><span>🔵</span> Wave</div>
                    <div class="method-chip"><span>💳</span> Visa / MasterCard</div>
                </div>
            </div>

            <form method="POST" action="{{ route('payment.initiate') }}" id="payForm">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="id" value="{{ $subscriber->id }}">

                <button type="submit" class="pay-btn" id="payBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Payer {{ number_format($amountXof, 0, ',', ' ') }} XOF
                </button>
            </form>

            <div class="pay-footer">
                <div class="secure-row">
                    <div class="secure-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Paiement 100% sécurisé
                    </div>
                    <div class="secure-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Chiffrement SSL
                    </div>
                </div>
                <div class="powered">Propulsé par <strong>GenuisPay</strong> — vous choisirez votre méthode sur la page suivante</div>
            </div>

        </div>
    </div>
</div>

<script>
document.getElementById('payForm').addEventListener('submit', function() {
    const btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Redirection vers GenuisPay…';
});
</script>
</body>
</html>
