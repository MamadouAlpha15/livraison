<?php

namespace App\Services;

use App\Enums\AffiliateStatus;
use App\Enums\AffiliateType;
use App\Enums\CommissionStatus;
use App\Enums\PayoutStatus;
use App\Enums\ReferralStatus;
use App\Models\Affiliate;
use App\Models\AffiliateReward;
use App\Models\AffiliateSetting;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Product;
use App\Models\Referral;
use App\Models\ReferralClick;
use App\Models\Shop;
use App\Models\ShopCreditEntry;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\AffiliateNotification;
use App\Support\Campaigns\ShopCredit;
use App\Support\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Programme d'affiliation des boutiques. Toute la logique est ici :
 *   - suivi des liens ?ref=CODE (cookie shopio_ref + clics) ;
 *   - attribution d'une nouvelle boutique à son parrain, auto-parrainage bloqué, fraude repérée ;
 *   - passage « Active » (produits en ligne + commandes), puis « Abonnée » au premier paiement ;
 *   - commission sur chaque paiement d'abonnement pendant N mois, validée après un délai ;
 *   - mois offert au parrain vendeur (plafond annuel), annulation après remboursement ;
 *   - retraits du solde validé.
 * Les réglages (taux, durées, seuils) sont dans AffiliateSetting.
 */
class AffiliateService
{
    public const COOKIE        = 'shopio_ref';
    public const DEVICE_COOKIE = 'shopio_did';

    /** Rôles de l'équipe Shopio qui gèrent l'affiliation */
    public const ADMIN_ROLES = ['superadmin', 'staff'];

    /** Commandes annulées : elles ne comptent pas pour rendre une boutique « Active » */
    private const CANCELLED_ORDER_STATUSES = ['annulee', 'annulée', 'cancelled', 'canceled', 'refusee', 'refusée'];

    private ?AffiliateSetting $settings = null;

    public function settings(): AffiliateSetting
    {
        return $this->settings ??= AffiliateSetting::current();
    }

    public function refreshSettings(): void
    {
        $this->settings = null;
    }

    /* ─────────────────────────── Affiliés ─────────────────────────── */

    public function findActiveByCode(?string $code): ?Affiliate
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '' || !preg_match('/^[A-Z0-9]{4,20}$/', $code)) {
            return null;
        }
        return Affiliate::where('code', $code)->where('status', AffiliateStatus::Active)->first();
    }

    /** Compte affilié du propriétaire d'une boutique, créé à sa première visite de « Parrainage » */
    public function vendorAffiliateFor(User $user): Affiliate
    {
        if ($existing = $user->affiliate()->first()) {
            return $existing;
        }
        $shop = $user->shop_id ? Shop::find($user->shop_id) : null;
        abort_unless($user->role === 'admin' && $shop, 403);

        try {
            return Affiliate::create([
                'user_id'         => $user->id,
                'type'            => AffiliateType::Vendor,
                'code'            => $this->generateCode($shop->name),
                'commission_rate' => $this->settings()->default_rate,
                'status'          => AffiliateStatus::Active,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Deux onglets ouverts en même temps : le compte vient d'être créé par l'autre
            return $user->affiliate()->firstOrFail();
        }
    }

    /**
     * Compte affilié d'une personne inscrite elle-même depuis /devenir-affilie (rôle « affilie »),
     * créé à sa première visite du tableau de bord, une fois son e-mail vérifié. Il reste « À valider »
     * (lien inactif) jusqu'à la validation par l'équipe, qui est prévenue.
     */
    public function externalAffiliateFor(User $user): Affiliate
    {
        if ($existing = $user->affiliate()->first()) {
            return $existing;
        }
        abort_unless($user->role === 'affilie', 403);

        try {
            $affiliate = Affiliate::create([
                'user_id'         => $user->id,
                'type'            => AffiliateType::External,
                'code'            => $this->generateCode($user->name),
                'name'            => $user->name,
                'email'           => $user->email,
                'phone'           => $user->phone,
                'commission_rate' => $this->settings()->default_rate,
                'status'          => AffiliateStatus::Pending,
            ]);
        } catch (UniqueConstraintViolationException) {
            return $user->affiliate()->firstOrFail();
        }

        $this->notifyAdmins('affilie_a_valider', 'Affilié à valider',
            "{$user->name} ({$user->email}, {$user->phone}) demande à rejoindre le programme d'affiliation.");

        return $affiliate;
    }

    /** L'équipe valide un affilié en attente : son lien fonctionne et il est prévenu (compte, e-mail, SMS) */
    public function approveAffiliate(Affiliate $affiliate): void
    {
        if ($affiliate->status !== AffiliateStatus::Pending) {
            return;
        }
        $affiliate->update(['status' => AffiliateStatus::Active]);
        $this->notifyAffiliate($affiliate, 'affilie_valide', 'Ton compte affilié est validé',
            "Bienvenue dans le programme d'affiliation Shopio ! Ton code est {$affiliate->code}. Tu peux maintenant partager ton lien : {$affiliate->link()}",
            "Shopio : ton compte affilié est validé. Ton code : {$affiliate->code}. Ton lien : {$affiliate->link()}");
    }

    /** L'équipe refuse un affilié en attente : son compte reste sans lien actif et il est prévenu */
    public function rejectAffiliate(Affiliate $affiliate, ?string $reason = null): void
    {
        if ($affiliate->status !== AffiliateStatus::Pending) {
            return;
        }
        $affiliate->update(['status' => AffiliateStatus::Suspended]);
        $this->notifyAffiliate($affiliate, 'affilie_refuse', 'Demande d\'affiliation refusée',
            'Ta demande pour rejoindre le programme d\'affiliation Shopio n\'a pas été acceptée.'
            . ($reason ? ' Motif : ' . $reason : '') . ' Pour en savoir plus, contacte l\'équipe Shopio.');
    }

    /**
     * Affilié externe créé par l'équipe. Avec $withAccess, un compte « affilie » est créé et reçoit
     * par e-mail un lien pour choisir son mot de passe (tableau de bord /affilie/tableau-de-bord).
     */
    public function createExternal(array $data, bool $withAccess = false): Affiliate
    {
        return DB::transaction(function () use ($data, $withAccess) {
            $user = null;
            if ($withAccess) {
                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => strtolower($data['email']),
                    'phone'    => $data['phone'] ?? null,
                    'password' => Hash::make(Str::random(40)),
                    'role'     => 'affilie',
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $affiliate = Affiliate::create([
                'user_id'         => $user?->id,
                'type'            => AffiliateType::External,
                'code'            => $this->generateCode($data['code'] ?? $data['name']),
                'name'            => $data['name'],
                'email'           => isset($data['email']) ? strtolower($data['email']) : null,
                'phone'           => $data['phone'] ?? null,
                'commission_rate' => $data['commission_rate'] ?? $this->settings()->default_rate,
                'status'          => AffiliateStatus::Active,
                'payout_method'   => $data['payout_method'] ?? null,
                'payout_phone'    => $data['payout_phone'] ?? null,
            ]);

            if ($user) {
                DB::afterCommit(fn () => Password::broker()->sendResetLink(['email' => $user->email]));
            }

            return $affiliate;
        });
    }

    /**
     * Code lisible tiré du nom (« BOUTIQUEAMINA » → « AMINA47 »), unique parmi les affiliés
     * et différent des codes de parrainage des clients (users.referral_code), qui utilisent aussi ?ref=.
     */
    public function generateCode(string $base): string
    {
        $words = collect(preg_split('/\s+/', Str::upper(Str::ascii($base))))
            ->map(fn ($w) => preg_replace('/[^A-Z0-9]/', '', $w))
            ->reject(fn ($w) => $w === '' || in_array($w, ['BOUTIQUE', 'SHOP', 'STORE', 'LA', 'LE', 'LES', 'DE', 'DU', 'DES', 'CHEZ', 'ET'], true));
        $stem = Str::limit($words->first() ?? 'SHOPIO', 8, '');
        if (strlen($stem) < 3) {
            $stem = 'SHOPIO';
        }

        for ($i = 0; $i < 20; $i++) {
            $code = $stem . random_int($i < 10 ? 10 : 100, $i < 10 ? 99 : 9999);
            $taken = Affiliate::withTrashed()->where('code', $code)->exists()
                || User::where('referral_code', $code)->exists();
            if (!$taken) {
                return $code;
            }
        }
        return 'SH' . strtoupper(Str::random(8));
    }

    /** Garde l'IP (hachée) et l'appareil du parrain pour reconnaître un auto-parrainage plus tard */
    public function rememberAffiliateDevice(Affiliate $affiliate, Request $request): void
    {
        $ip  = self::hash($request->ip());
        $dev = self::hash($this->ensureDeviceCookie($request));
        if ($affiliate->last_ip_hash !== $ip || $affiliate->device_hash !== $dev) {
            $affiliate->forceFill(['last_ip_hash' => $ip, 'device_hash' => $dev])->saveQuietly();
        }
    }

    /** Identifiant d'appareil (cookie de 5 ans), créé s'il n'existe pas encore */
    public function ensureDeviceCookie(Request $request): string
    {
        $id = $request->cookie(self::DEVICE_COOKIE);
        if (!is_string($id) || strlen($id) !== 32) {
            $id = Str::random(32);
            Cookie::queue(self::DEVICE_COOKIE, $id, 60 * 24 * 365 * 5);
            $request->cookies->set(self::DEVICE_COOKIE, $id);
        }
        return $id;
    }

    public static function hash(?string $value): ?string
    {
        return $value === null || $value === '' ? null : hash_hmac('sha256', $value, (string) config('app.key'));
    }

    /* ─────────────────────────── Suivi des liens ─────────────────────────── */

    /** Lien ?ref=CODE : garde le code dans un cookie et compte le clic (5 par heure et par IP au plus) */
    public function trackLink(string $code, Request $request): ?Affiliate
    {
        $affiliate = $this->findActiveByCode($code);
        if (!$affiliate) {
            return null;
        }

        Cookie::queue(self::COOKIE, $affiliate->code, $this->settings()->cookie_days * 1440);

        $ipHash = self::hash($request->ip()) ?? 'inconnue';
        $key = 'affiliate-click:' . $affiliate->id . ':' . $ipHash;
        if (!RateLimiter::tooManyAttempts($key, 5)) {
            RateLimiter::hit($key, 3600);
            ReferralClick::create([
                'affiliate_id' => $affiliate->id,
                'ip_hash'      => $ipHash,
                'user_agent'   => Str::limit((string) $request->userAgent(), 250, ''),
                'landing_url'  => Str::limit($request->fullUrl(), 495, ''),
            ]);
        }

        return $affiliate;
    }

    /** Code à pré-remplir à l'inscription (cookie d'un lien suivi), s'il est encore valide */
    public function codeFromCookie(Request $request): ?string
    {
        return $this->findActiveByCode($request->cookie(self::COOKIE))?->code;
    }

    /* ─────────────────────────── Attribution ─────────────────────────── */

    /**
     * Raison de refuser le code : le parrain est le même utilisateur, ou a le même téléphone
     * ou le même e-mail que le nouveau vendeur ou sa boutique. Null si le code est acceptable.
     */
    public function selfReferralReason(Affiliate $affiliate, User $user, ?string $shopEmail = null, ?string $shopPhone = null): ?string
    {
        if ($affiliate->user_id && $affiliate->user_id === $user->id) {
            return 'Tu ne peux pas utiliser ton propre code de parrainage.';
        }

        $sponsorShop = $affiliate->shop();
        $sponsorEmails = collect([$affiliate->email, $affiliate->user?->email, $sponsorShop?->email])
            ->filter()->map(fn ($e) => strtolower(trim($e)));
        $newEmails = collect([$user->email, $shopEmail])->filter()->map(fn ($e) => strtolower(trim($e)));
        if ($sponsorEmails->intersect($newEmails)->isNotEmpty()) {
            return 'Ce code de parrainage appartient à ton propre compte (même adresse e-mail).';
        }

        $sponsorPhones = collect([$affiliate->phone, $affiliate->payout_phone, $affiliate->user?->phone, $sponsorShop?->phone])
            ->map(fn ($p) => self::phoneKey($p))->filter();
        $newPhones = collect([$user->phone, $shopPhone])->map(fn ($p) => self::phoneKey($p))->filter();
        if ($sponsorPhones->intersect($newPhones)->isNotEmpty()) {
            return 'Ce code de parrainage appartient à ton propre compte (même numéro de téléphone).';
        }

        return null;
    }

    /** 9 derniers chiffres : « +224 622 45 81 37 » et « 622458137 » sont le même numéro */
    public static function phoneKey(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        return strlen($digits) >= 8 ? substr($digits, -9) : null;
    }

    /**
     * Rattache une boutique qui vient d'être créée à son parrain (appelé dans la transaction de création).
     * Le parrainage est marqué « à vérifier » si le nouveau vendeur a la même IP ou le même appareil que le parrain.
     */
    public function attribute(Shop $shop, User $owner, ?string $code, ?string $source, Request $request): ?Referral
    {
        $affiliate = $this->findActiveByCode($code);
        if (!$affiliate || $this->selfReferralReason($affiliate, $owner, $shop->email, $shop->phone)) {
            return null;
        }
        if (Referral::where('referred_shop_id', $shop->id)->exists()) {
            return null;
        }

        $ip  = self::hash($request->ip());
        $dev = self::hash($request->cookie(self::DEVICE_COOKIE));
        $reasons = [];
        if ($ip && ($ip === $affiliate->last_ip_hash || $this->sponsorUsedIp($affiliate, $request->ip()))) {
            $reasons[] = 'même adresse IP que le parrain';
        }
        if ($dev && $dev === $affiliate->device_hash) {
            $reasons[] = 'même appareil que le parrain';
        }

        $referral = Referral::create([
            'affiliate_id'     => $affiliate->id,
            'referred_shop_id' => $shop->id,
            'status'           => ReferralStatus::Registered,
            'source'           => $source,
            'flagged'          => $reasons !== [],
            'flag_reason'      => $reasons ? ucfirst(implode(', ', $reasons)) . '.' : null,
            'ip_hash'          => $ip,
            'device_hash'      => $dev,
        ]);

        Cookie::queue(Cookie::forget(self::COOKIE));

        DB::afterCommit(function () use ($referral, $affiliate, $shop) {
            $this->notifyAffiliate($affiliate, 'nouveau_filleul', 'Nouveau filleul',
                "La boutique « {$shop->name} » s'est inscrite avec ton code. Elle devient active quand elle a des produits en ligne et ses premières commandes.");
            if ($referral->flagged) {
                $this->notifyAdmins('filleul_a_verifier', 'Parrainage à vérifier',
                    "« {$shop->name} » parrainée par {$affiliate->displayName()} : {$referral->flag_reason}");
            }
        });

        return $referral;
    }

    /** Le parrain a-t-il une session ouverte depuis cette IP ? (sessions en base uniquement) */
    private function sponsorUsedIp(Affiliate $affiliate, ?string $ip): bool
    {
        if (!$ip || !$affiliate->user_id || config('session.driver') !== 'database') {
            return false;
        }
        try {
            return DB::table(config('session.table', 'sessions'))
                ->where('user_id', $affiliate->user_id)->where('ip_address', $ip)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /* ─────────────────────────── Statut de la boutique parrainée ─────────────────────────── */

    /** Inscrite → Active dès qu'elle a assez de produits en ligne et de commandes (réglages) */
    public function checkActivation(Shop|int $shop): bool
    {
        $shopId = $shop instanceof Shop ? $shop->id : $shop;
        $referral = Referral::with('affiliate', 'shop')->where('referred_shop_id', $shopId)
            ->where('status', ReferralStatus::Registered)->first();
        if (!$referral) {
            return false;
        }

        $s = $this->settings();
        $products = Product::where('shop_id', $shopId)->where('is_active', true)->count();
        if ($products < $s->active_min_products) {
            return false;
        }
        $orders = Order::where('shop_id', $shopId)->whereNotIn('status', self::CANCELLED_ORDER_STATUSES)->count();
        if ($orders < $s->active_min_orders) {
            return false;
        }

        $updated = Referral::whereKey($referral->id)->where('status', ReferralStatus::Registered)
            ->update(['status' => ReferralStatus::Active, 'activated_at' => now(), 'updated_at' => now()]);
        if ($updated && $referral->affiliate) {
            $this->notifyAffiliate($referral->affiliate, 'filleul_actif', 'Ton filleul est actif',
                "« {$referral->shop?->name} » a ses produits en ligne et ses premières commandes. Tu touches une commission sur ses abonnements.");
        }
        return (bool) $updated;
    }

    /* ─────────────────────────── Commissions ─────────────────────────── */

    /**
     * Paiement d'abonnement réussi : la boutique passe « Abonnée », une commission en attente est créée
     * (pendant la durée prévue après son premier paiement) et, au premier paiement, le parrain vendeur
     * reçoit son mois offert. Un même paiement ne crée jamais deux commissions.
     */
    public function handleSubscriptionPayment(Subscription $payment): ?Commission
    {
        if ($payment->subscriber_type !== Shop::class || $payment->status !== 'active'
            || (int) $payment->amount <= 0 || $payment->refunded_at || $payment->payment_method === 'essai_gratuit') {
            return null;
        }

        $referral = Referral::with('affiliate', 'shop')->where('referred_shop_id', $payment->subscriber_id)->first();
        if (!$referral || $referral->status === ReferralStatus::Cancelled || !$referral->affiliate?->isActive()) {
            return null;
        }
        $affiliate = $referral->affiliate;
        $s = $this->settings();

        [$commission, $firstPayment] = DB::transaction(function () use ($referral, $affiliate, $payment, $s) {
            $locked = Referral::whereKey($referral->id)->lockForUpdate()->first();
            $first = $locked->first_paid_at === null;
            $locked->first_paid_at ??= $payment->started_at ?? now();
            $locked->activated_at ??= now();
            $locked->status = ReferralStatus::Paying;
            $locked->save();

            $paidAt = $payment->started_at ?? now();
            $inWindow = $paidAt->lt($locked->first_paid_at->copy()->addMonths($s->commission_months));
            $amount = (int) floor((int) $payment->amount * (float) $affiliate->commission_rate / 100);

            $commission = null;
            if ($inWindow && $amount > 0 && !Commission::where('subscription_payment_id', $payment->id)->exists()) {
                try {
                    $commission = Commission::create([
                        'affiliate_id'            => $affiliate->id,
                        'referral_id'             => $locked->id,
                        'subscription_payment_id' => $payment->id,
                        'amount'                  => $amount,
                        'status'                  => CommissionStatus::Pending,
                        'available_at'            => now()->addDays($s->validation_days),
                    ]);
                } catch (UniqueConstraintViolationException) {
                    $commission = null; // déjà créée par un autre traitement du même paiement
                }
            }
            return [$commission, $first];
        });

        if ($commission) {
            $this->notifyAffiliate($affiliate, 'commission_creee', 'Nouvelle commission',
                '« ' . $referral->shop?->name . ' » a payé son abonnement : ' . Money::gnf($commission->amount)
                . ' pour toi, disponible le ' . $commission->available_at->format('d/m/Y') . '.');
        }
        if ($firstPayment) {
            $this->grantReward($referral->fresh(['affiliate', 'shop']));
        }

        return $commission;
    }

    /** Remboursement d'un paiement : sa commission est annulée (et retirée d'un retrait pas encore versé) */
    public function cancelForRefund(Subscription $payment): ?Commission
    {
        $commission = Commission::where('subscription_payment_id', $payment->id)->first();
        if ($commission?->status === CommissionStatus::Paid) {
            Log::warning("[Affiliation] Paiement #{$payment->id} remboursé mais commission #{$commission->id} déjà versée.");
        }
        $cancelled = $commission ? $this->cancelCommission($commission) : null;

        if ($cancelled) {
            $this->notifyAffiliate($cancelled->affiliate, 'commission_annulee', 'Commission annulée',
                'Le paiement lié à ta commission de ' . Money::gnf($cancelled->amount) . ' a été remboursé : la commission est annulée.');
        }
        return $cancelled;
    }

    /** Annule une commission pas encore versée ; null si elle l'est déjà (ou était annulée) */
    private function cancelCommission(Commission $commission): ?Commission
    {
        return DB::transaction(function () use ($commission) {
            $commission = Commission::whereKey($commission->id)->lockForUpdate()->first();
            if (!$commission || in_array($commission->status, [CommissionStatus::Cancelled, CommissionStatus::Paid], true)) {
                return null;
            }

            if ($commission->payout_id) {
                $payout = Payout::whereKey($commission->payout_id)->lockForUpdate()->first();
                if ($payout && $payout->status === PayoutStatus::Requested) {
                    $left = max(0, $payout->amount - $commission->amount);
                    $payout->update($left > 0
                        ? ['amount' => $left]
                        : ['amount' => 0, 'status' => PayoutStatus::Rejected, 'note' => 'Commission annulée après remboursement.', 'processed_at' => now()]);
                }
            }

            $commission->update(['status' => CommissionStatus::Cancelled, 'payout_id' => null]);
            return $commission;
        });
    }

    /** Tâche quotidienne : les commissions dont le délai est passé deviennent disponibles (hors parrainages à vérifier) */
    public function approveDueCommissions(): int
    {
        $due = Commission::with('affiliate')
            ->where('status', CommissionStatus::Pending)
            ->where('available_at', '<=', now())
            ->whereHas('referral', fn ($q) => $q->where('flagged', false)->where('status', '!=', ReferralStatus::Cancelled))
            ->whereHas('affiliate', fn ($q) => $q->where('status', AffiliateStatus::Active))
            ->get();

        $count = 0;
        foreach ($due->groupBy('affiliate_id') as $commissions) {
            $done = Commission::whereIn('id', $commissions->pluck('id'))->where('status', CommissionStatus::Pending)
                ->update(['status' => CommissionStatus::Approved, 'updated_at' => now()]);
            if ($done > 0) {
                $count += $done;
                $affiliate = $commissions->first()->affiliate;
                $this->notifyAffiliate($affiliate, 'commissions_disponibles', 'Commissions disponibles',
                    Money::gnf($commissions->sum('amount')) . ' de commissions sont maintenant disponibles. Solde à retirer : '
                    . Money::gnf($this->availableBalance($affiliate)) . '.');
            }
        }
        return $count;
    }

    /* ─────────────────────────── Mois offert au parrain ─────────────────────────── */

    /**
     * Premier paiement d'une boutique parrainée par un vendeur : son abonnement en cours est prolongé
     * d'un mois, ou il reçoit l'équivalent en Crédit Shopio s'il n'en a pas. Plafond : N mois par année civile.
     */
    public function grantReward(Referral $referral): ?AffiliateReward
    {
        $affiliate = $referral->affiliate;
        if (!$affiliate || $affiliate->type !== AffiliateType::Vendor || !$affiliate->isActive()
            || $referral->flagged || $referral->first_paid_at === null || $referral->status === ReferralStatus::Cancelled) {
            return null;
        }
        $shop = $affiliate->shop();
        $s = $this->settings();
        if (!$shop || $s->reward_months < 1) {
            return null;
        }

        $reward = DB::transaction(function () use ($affiliate, $referral, $shop, $s) {
            Affiliate::whereKey($affiliate->id)->lockForUpdate()->first();
            if (AffiliateReward::where('referral_id', $referral->id)->exists()) {
                return null;
            }
            $used = (int) AffiliateReward::where('affiliate_id', $affiliate->id)
                ->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()])->sum('months');
            $months = min($s->reward_months, $s->annual_reward_cap - $used);
            if ($months <= 0) {
                return null;
            }

            $current = Subscription::where('subscriber_type', Shop::class)->where('subscriber_id', $shop->id)
                ->where('status', 'active')->where('expires_at', '>', now())->orderByDesc('expires_at')->first();

            if ($current && $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture()) {
                $end = $current->expires_at->copy()->addDays(SubscriptionService::SUBSCRIPTION_DAYS * $months);
                $current->update(['expires_at' => $end]);
                $shop->updateQuietly(['plan_expires_at' => $end->max($shop->plan_expires_at)]);
                $data = ['kind' => AffiliateReward::KIND_EXTENSION, 'subscription_id' => $current->id];
            } else {
                $value = (int) config('chapchappay.plans.pro', 150000) * $months;
                $entry = ShopCredit::credit($shop, $value, ShopCreditEntry::REASON_PARRAINAGE, null,
                    'Parrainage de « ' . $referral->shop?->name . ' »');
                $data = ['kind' => AffiliateReward::KIND_CREDIT, 'amount_gnf' => $value, 'shop_credit_entry_id' => $entry->id];
            }

            return AffiliateReward::create($data + [
                'affiliate_id' => $affiliate->id,
                'referral_id'  => $referral->id,
                'shop_id'      => $shop->id,
                'months'       => $months,
            ]);
        });

        if ($reward) {
            $body = $reward->kind === AffiliateReward::KIND_EXTENSION
                ? "Merci pour ton parrainage de « {$referral->shop?->name} » : ton abonnement est prolongé de {$reward->months} mois."
                : "Merci pour ton parrainage de « {$referral->shop?->name} » : " . Money::gnf($reward->amount_gnf) . ' ajoutés à ton Crédit Shopio.';
            $this->notifyAffiliate($affiliate, 'mois_offert', 'Mois offert', $body);
        }
        return $reward;
    }

    /* ─────────────────────────── Solde et retraits ─────────────────────────── */

    /** Commissions validées qui ne sont pas encore dans un retrait */
    public function availableBalance(Affiliate $affiliate): int
    {
        return (int) Commission::where('affiliate_id', $affiliate->id)
            ->where('status', CommissionStatus::Approved)->whereNull('payout_id')->sum('amount');
    }

    /**
     * Demande de retrait de tout le solde disponible. L'affilié est verrouillé pendant le calcul :
     * deux demandes envoyées en même temps ne peuvent pas retirer deux fois les mêmes commissions.
     *
     * @throws ValidationException
     */
    public function requestPayout(Affiliate $affiliate, string $method, string $phone, ?int $amount = null): Payout
    {
        $payout = DB::transaction(function () use ($affiliate, $method, $phone, $amount) {
            $locked = Affiliate::whereKey($affiliate->id)->lockForUpdate()->firstOrFail();
            if (!$locked->isActive()) {
                throw ValidationException::withMessages(['payout' => 'Ton compte affilié est suspendu : contacte l\'équipe Shopio.']);
            }
            if (Payout::where('affiliate_id', $locked->id)->where('status', PayoutStatus::Requested)->exists()) {
                throw ValidationException::withMessages(['payout' => 'Tu as déjà une demande de retrait en cours de traitement.']);
            }

            $commissions = Commission::where('affiliate_id', $locked->id)->where('status', CommissionStatus::Approved)
                ->whereNull('payout_id')->lockForUpdate()->get();
            $balance = (int) $commissions->sum('amount');
            $threshold = $this->settings()->payout_threshold;

            if ($amount !== null && $amount > $balance) {
                throw ValidationException::withMessages(['payout' => 'Tu ne peux pas retirer plus que ton solde disponible (' . Money::gnf($balance) . ').']);
            }
            if ($balance <= 0 || $balance < $threshold) {
                throw ValidationException::withMessages(['payout' => 'Ton solde disponible (' . Money::gnf($balance) . ') n\'atteint pas le minimum de retrait (' . Money::gnf($threshold) . ').']);
            }

            $payout = Payout::create([
                'affiliate_id' => $locked->id,
                'amount'       => $balance,
                'method'       => $method,
                'phone'        => $phone,
                'status'       => PayoutStatus::Requested,
            ]);
            Commission::whereIn('id', $commissions->pluck('id'))->update(['payout_id' => $payout->id]);
            $locked->update(['payout_method' => $method, 'payout_phone' => $phone]);

            return $payout;
        });

        $this->notifyAdmins('retrait_demande', 'Demande de retrait',
            $affiliate->displayName() . ' demande ' . Money::gnf($payout->amount) . ' par ' . $payout->methodLabel() . ' (' . $payout->phone . ').');

        return $payout;
    }

    public function markPayoutPaid(Payout $payout, User $by, string $reference): Payout
    {
        $payout = DB::transaction(function () use ($payout, $by, $reference) {
            $locked = Payout::whereKey($payout->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== PayoutStatus::Requested) {
                throw ValidationException::withMessages(['payout' => 'Ce retrait a déjà été traité.']);
            }
            $locked->update(['status' => PayoutStatus::Paid, 'reference' => $reference, 'processed_by' => $by->id, 'processed_at' => now()]);
            Commission::where('payout_id', $locked->id)->where('status', CommissionStatus::Approved)
                ->update(['status' => CommissionStatus::Paid, 'updated_at' => now()]);
            return $locked;
        });

        $this->notifyAffiliate($payout->affiliate, 'retrait_paye', 'Retrait envoyé',
            Money::gnf($payout->amount) . ' ont été envoyés sur ton ' . $payout->methodLabel() . ' (' . $payout->phone . '). Référence : ' . $reference . '.',
            'Shopio : ' . Money::gnf($payout->amount) . ' de commissions envoyés sur ton ' . $payout->methodLabel() . '. Réf. ' . $reference);

        return $payout;
    }

    public function rejectPayout(Payout $payout, User $by, string $note): Payout
    {
        $payout = DB::transaction(function () use ($payout, $by, $note) {
            $locked = Payout::whereKey($payout->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== PayoutStatus::Requested) {
                throw ValidationException::withMessages(['payout' => 'Ce retrait a déjà été traité.']);
            }
            $locked->update(['status' => PayoutStatus::Rejected, 'note' => $note, 'processed_by' => $by->id, 'processed_at' => now()]);
            // Les commissions redeviennent disponibles pour une nouvelle demande
            Commission::where('payout_id', $locked->id)->update(['payout_id' => null]);
            return $locked;
        });

        $this->notifyAffiliate($payout->affiliate, 'retrait_refuse', 'Retrait refusé',
            'Ta demande de retrait de ' . Money::gnf($payout->amount) . ' a été refusée : ' . $note . ' Ton solde reste disponible.');

        return $payout;
    }

    /* ─────────────────────────── Actions de l'équipe ─────────────────────────── */

    /** Parrainage vérifié par l'équipe : ses commissions peuvent être validées et le mois offert accordé */
    public function clearFlag(Referral $referral): void
    {
        $referral->update(['flagged' => false]);
        if ($referral->status === ReferralStatus::Paying) {
            $this->grantReward($referral->fresh(['affiliate', 'shop']));
        }
    }

    /** Parrainage annulé (fraude) : ses commissions pas encore versées sont annulées */
    public function cancelReferral(Referral $referral): void
    {
        DB::transaction(function () use ($referral) {
            $referral->update(['status' => ReferralStatus::Cancelled]);
            foreach (Commission::where('referral_id', $referral->id)
                ->whereIn('status', [CommissionStatus::Pending, CommissionStatus::Approved])->get() as $commission) {
                $this->cancelCommission($commission);
            }
        });
        $this->notifyAffiliate($referral->affiliate, 'filleul_annule', 'Parrainage annulé',
            'Le parrainage de « ' . $referral->shop?->name . ' » a été annulé par l\'équipe Shopio. Ses commissions non versées sont annulées.');
    }

    /* ─────────────────────────── Tableau de bord ─────────────────────────── */

    public function stats(Affiliate $affiliate): array
    {
        $byStatus = Referral::where('affiliate_id', $affiliate->id)
            ->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $sums = Commission::where('affiliate_id', $affiliate->id)
            ->selectRaw('status, sum(amount) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'clicks'     => ReferralClick::where('affiliate_id', $affiliate->id)->count(),
            'registered' => (int) $byStatus->except(ReferralStatus::Cancelled->value)->sum(),
            'active'     => (int) ($byStatus[ReferralStatus::Active->value] ?? 0) + (int) ($byStatus[ReferralStatus::Paying->value] ?? 0),
            'paying'     => (int) ($byStatus[ReferralStatus::Paying->value] ?? 0),
            'pending'    => (int) ($sums[CommissionStatus::Pending->value] ?? 0),
            'available'  => $this->availableBalance($affiliate),
            'paid'       => (int) ($sums[CommissionStatus::Paid->value] ?? 0),
            'in_payout'  => (int) Payout::where('affiliate_id', $affiliate->id)->where('status', PayoutStatus::Requested)->sum('amount'),
        ];
    }

    /* ─────────────────────────── Notifications ─────────────────────────── */

    public function notifyAffiliate(?Affiliate $affiliate, string $event, string $title, string $body, ?string $sms = null): void
    {
        if (!$affiliate) {
            return;
        }
        try {
            $url = $affiliate->type === AffiliateType::Vendor ? route('vendeur.parrainage') : route('affilie.dashboard');
            $notification = new AffiliateNotification($event, $title, $body, $url, $sms, $sms ? $affiliate->contactPhone() : null);
            if ($affiliate->user) {
                $affiliate->user->notify($notification);
            } elseif ($affiliate->email) {
                Notification::route('mail', $affiliate->email)->notify($notification);
            }
        } catch (\Throwable $e) {
            Log::warning('[Affiliation] Notification non envoyée : ' . $e->getMessage());
        }
    }

    public function notifyAdmins(string $event, string $title, string $body): void
    {
        try {
            $admins = User::whereIn('role', self::ADMIN_ROLES)->get();
            Notification::send($admins, new AffiliateNotification($event, $title, $body, route('admin.affiliation.index')));
        } catch (\Throwable $e) {
            Log::warning('[Affiliation] Notification équipe non envoyée : ' . $e->getMessage());
        }
    }
}
