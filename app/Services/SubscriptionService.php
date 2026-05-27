<?php

namespace App\Services;

// ─────────────────────────────────────────────────────────────────────────────
// SubscriptionService
// Centralise toute la logique de gestion des abonnements :
//   - Vérifier le plan actif d'une boutique ou entreprise
//   - Activer un abonnement après paiement confirmé
//   - Expirer les abonnements dont la date est passée (appelé par le scheduler)
//   - Vérifier les limites du plan gratuit
// ─────────────────────────────────────────────────────────────────────────────

use App\Models\DeliveryCompany;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    // ── Limites du plan gratuit boutique ─────────────────────────────────────
    const SHOP_FREE_MAX_PRODUCTS  = 5;    // Max produits créés
    const SHOP_FREE_MAX_ORDERS    = 10;   // Max commandes ce mois

    // ── Limites du plan gratuit entreprise ────────────────────────────────────
    const COMP_FREE_MAX_ORDERS    = 10;   // Max commandes (cumulé)
    const COMP_FREE_MAX_DRIVERS   = 1;    // Max chauffeurs
    const COMP_FREE_MAX_ZONES     = 5;    // Max zones de livraison

    // ── Durée d'un abonnement ────────────────────────────────────────────────
    const SUBSCRIPTION_DAYS = 30;         // 30 jours = 1 mois

    // ─────────────────────────────────────────────────────────────────────────
    // Retourne le plan actif d'une boutique : 'pro' ou 'free'
    // On vérifie d'abord la colonne cache, puis on revalide via la table subscriptions.
    // ─────────────────────────────────────────────────────────────────────────
    public function shopPlan(Shop $shop): string
    {
        // Le plan Pro est valide uniquement si la date n'est pas dépassée
        if ($shop->plan === 'pro' && $shop->plan_expires_at && $shop->plan_expires_at->isFuture()) {
            return 'pro';
        }

        // Si la date est passée, on réinitialise au plan gratuit
        if ($shop->plan === 'pro' && $shop->plan_expires_at && $shop->plan_expires_at->isPast()) {
            $this->revertShopToFree($shop);
        }

        return 'free';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Retourne le plan actif d'une entreprise : 'business' ou 'free'
    // ─────────────────────────────────────────────────────────────────────────
    public function companyPlan(DeliveryCompany $company): string
    {
        if ($company->plan === 'business' && $company->plan_expires_at && $company->plan_expires_at->isFuture()) {
            return 'business';
        }

        if ($company->plan === 'business' && $company->plan_expires_at && $company->plan_expires_at->isPast()) {
            $this->revertCompanyToFree($company);
        }

        return 'free';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Active un abonnement après confirmation du paiement GenuisPay.
    // Appelé depuis le webhook ou la vérification de retour.
    // ─────────────────────────────────────────────────────────────────────────
    public function activate(Subscription $subscription, array $gatewayResponse = []): void
    {
        $startedAt  = now();
        $expiresAt  = now()->addDays(self::SUBSCRIPTION_DAYS);

        // Met à jour l'enregistrement d'abonnement
        $subscription->update([
            'status'           => 'active',
            'started_at'       => $startedAt,
            'expires_at'       => $expiresAt,
            'gateway_response' => $gatewayResponse,
        ]);

        // Met à jour le cache plan sur le modèle (Shop ou DeliveryCompany)
        $subscriber = $subscription->subscriber;

        if ($subscriber instanceof Shop) {
            $subscriber->update([
                'plan'            => 'pro',
                'plan_expires_at' => $expiresAt,
            ]);
            Log::info("[Subscription] Boutique #{$subscriber->id} activée en Pro jusqu'au {$expiresAt->toDateString()}");
        }

        if ($subscriber instanceof DeliveryCompany) {
            $subscriber->update([
                'plan'            => 'business',
                'plan_expires_at' => $expiresAt,
            ]);
            Log::info("[Subscription] Entreprise #{$subscriber->id} activée en Business jusqu'au {$expiresAt->toDateString()}");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Marque un paiement comme échoué.
    // ─────────────────────────────────────────────────────────────────────────
    public function markFailed(Subscription $subscription, array $gatewayResponse = []): void
    {
        $subscription->update([
            'status'           => 'failed',
            'gateway_response' => $gatewayResponse,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Expire tous les abonnements dont la date est passée.
    // Appelé quotidiennement par le scheduler Laravel (routes/console.php).
    // ─────────────────────────────────────────────────────────────────────────
    public function expireOldSubscriptions(): int
    {
        $expired = Subscription::where('status', 'active')
                               ->where('expires_at', '<=', now())
                               ->get();

        $count = 0;
        foreach ($expired as $sub) {
            $sub->update(['status' => 'expired']);

            $subscriber = $sub->subscriber;
            if ($subscriber instanceof Shop) {
                $this->revertShopToFree($subscriber);
            }
            if ($subscriber instanceof DeliveryCompany) {
                $this->revertCompanyToFree($subscriber);
            }
            $count++;
        }

        Log::info("[Subscription] Expiration quotidienne : {$count} abonnement(s) expiré(s).");
        return $count;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LIMITES PLAN GRATUIT — BOUTIQUE
    // ─────────────────────────────────────────────────────────────────────────

    // Vérifie si la boutique peut encore créer un produit (max 5 en free)
    public function canCreateProduct(Shop $shop): bool
    {
        if ($this->shopPlan($shop) === 'pro') return true;
        return $shop->products()->count() < self::SHOP_FREE_MAX_PRODUCTS;
    }

    // Vérifie si la boutique peut recevoir une commande ce mois (max 10 en free)
    public function canReceiveOrder(Shop $shop): bool
    {
        if ($this->shopPlan($shop) === 'pro') return true;
        $count = $shop->orders()
                      ->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year)
                      ->count();
        return $count < self::SHOP_FREE_MAX_ORDERS;
    }

    // Vérifie si la boutique peut encore TRAITER (assigner) une commande ce mois (max 10 en free)
    // Les clients peuvent toujours passer commande ; c'est le traitement (assignation livreur/entreprise) qui est limité.
    public function canProcessOrder(Shop $shop): bool
    {
        if ($this->shopPlan($shop) === 'pro') return true;
        $count = $shop->orders()
                      ->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year)
                      ->where(function ($q) {
                          $q->whereNotNull('livreur_id')
                            ->orWhereNotNull('delivery_company_id');
                      })
                      ->count();
        return $count < self::SHOP_FREE_MAX_ORDERS;
    }

    // Retourne le nombre de commandes traitées ce mois pour une boutique
    public function processedOrdersThisMonth(Shop $shop): int
    {
        return $shop->orders()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where(function ($q) {
                        $q->whereNotNull('livreur_id')
                          ->orWhereNotNull('delivery_company_id');
                    })
                    ->count();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LIMITES PLAN GRATUIT — ENTREPRISE
    // ─────────────────────────────────────────────────────────────────────────

    // Vérifie si l'entreprise peut créer un chauffeur (max 1 en free)
    public function canCreateDriver(DeliveryCompany $company): bool
    {
        if ($this->companyPlan($company) === 'business') return true;
        return $company->drivers()->count() < self::COMP_FREE_MAX_DRIVERS;
    }

    // Vérifie si l'entreprise peut créer une zone (max 5 en free)
    public function canCreateZone(DeliveryCompany $company): bool
    {
        if ($this->companyPlan($company) === 'business') return true;
        return $company->zones()->count() < self::COMP_FREE_MAX_ZONES;
    }

    // Vérifie si l'entreprise peut recevoir une commande (max 10/mois en free)
    public function canReceiveCompanyOrder(DeliveryCompany $company): bool
    {
        if ($this->companyPlan($company) === 'business') return true;
        $count = $company->orders()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        return $count < self::COMP_FREE_MAX_ORDERS;
    }

    // Retourne le nombre de commandes ce mois pour l'entreprise
    public function monthlyCompanyOrderCount(DeliveryCompany $company): int
    {
        return (int) $company->orders()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Privé : remet une boutique au plan gratuit
    // ─────────────────────────────────────────────────────────────────────────
    private function revertShopToFree(Shop $shop): void
    {
        $shop->updateQuietly(['plan' => 'free', 'plan_expires_at' => null]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Privé : remet une entreprise au plan gratuit
    // ─────────────────────────────────────────────────────────────────────────
    private function revertCompanyToFree(DeliveryCompany $company): void
    {
        $company->updateQuietly(['plan' => 'free', 'plan_expires_at' => null]);
    }
}
