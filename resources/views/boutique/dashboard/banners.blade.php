        @if($recentPayment)
        <div id="paymentSuccessBanner" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:14px 24px;display:flex;align-items:center;gap:12px;border-radius:12px;margin:16px 16px 0;box-shadow:0 4px 20px rgba(16,185,129,.35);">
            <span style="font-size:22px">🎉</span>
            <div style="flex:1">
                <strong style="font-size:15px">Paiement réussi ! Votre abonnement Pro est maintenant actif.</strong>
                <div style="font-size:12px;opacity:.85;margin-top:2px">Vous avez accès à toutes les fonctionnalités premium.</div>
            </div>
            <button onclick="document.getElementById('paymentSuccessBanner').remove()" style="background:rgba(255,255,255,.2);border:none;color:#fff;border-radius:8px;padding:6px 12px;cursor:pointer;font-size:13px">✕</button>
        </div>
        @endif

        {{-- ══ BANNIÈRE PAIEMENT ÉCHOUÉ ══ --}}
        @if(session('payment_failed'))
        <div id="paymentFailedBanner" style="background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;padding:14px 24px;display:flex;align-items:center;gap:12px;border-radius:12px;margin:16px 16px 0;box-shadow:0 4px 20px rgba(239,68,68,.35);">
            <span style="font-size:22px">⚠️</span>
            <div style="flex:1">
                <strong style="font-size:15px">Le paiement n'a pas abouti.</strong>
                <div style="font-size:12px;opacity:.85;margin-top:2px">Votre carte ou moyen de paiement a été refusé. Aucun montant n'a été débité — réessayez.</div>
            </div>
            <button onclick="document.getElementById('paymentFailedBanner').remove()" style="background:rgba(255,255,255,.2);border:none;color:#fff;border-radius:8px;padding:6px 12px;cursor:pointer;font-size:13px">✕</button>
        </div>
        @endif

        {{-- ══ NOUVEAUTÉS DES CAMPAGNES (non lues) ══ --}}
        @foreach($campaignNews ?? [] as $news)
        <div style="background:#0B1E3D;color:#fff;padding:14px 20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;border-radius:14px;margin:16px 16px 0">
            <span style="width:38px;height:38px;border-radius:10px;background:#1F7CF6;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0">{!! \App\Support\IconLibrary::svg('megaphone', '', 18) !!}</span>
            <div style="flex:1 1 220px;min-width:0">
                <strong style="font-size:14.5px">{{ $news->data['title'] ?? 'Campagne' }}</strong>
                <div style="font-size:12.5px;color:#C9D6EA;margin-top:2px;overflow-wrap:anywhere">{{ $news->data['body'] ?? '' }}</div>
            </div>
            <a href="{{ route('notifications.read', $news->id) }}" style="background:#1F7CF6;color:#fff;border-radius:999px;padding:9px 16px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap">{{ $news->data['action'] ?? 'Voir' }}</a>
        </div>
        @endforeach

        {{-- ══ ALERTES PHOTOS NON AFFICHÉES (envoyées par le SuperAdmin) ══ --}}
        @foreach($brokenImageAlerts ?? [] as $alert)
        <div style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:14px 24px;display:flex;align-items:center;gap:12px;border-radius:12px;margin:16px 16px 0;box-shadow:0 4px 20px rgba(245,158,11,.35);">
            <span style="font-size:22px">🖼️</span>
            <div style="flex:1">
                <strong style="font-size:15px">{{ $alert->data['title'] ?? 'Photo non affichée' }} — « {{ $alert->data['product_name'] ?? 'un produit' }} »</strong>
                <div style="font-size:12.5px;opacity:.9;margin-top:2px">{{ $alert->data['body'] ?? "Une photo que vous avez ajoutée ne s'affiche pas correctement pour vos clients." }}</div>
            </div>
            <a href="{{ route('notifications.read', $alert->id) }}" style="background:rgba(255,255,255,.22);border:none;color:#fff;border-radius:8px;padding:8px 16px;cursor:pointer;font-size:12.5px;font-weight:700;text-decoration:none;white-space:nowrap">{{ ($alert->data['title'] ?? '') === 'Produit sans photo' ? 'Ajouter une photo' : 'Vérifier la photo' }}</a>
        </div>
        @endforeach
