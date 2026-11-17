<?php

namespace App\Services;

use App\Jobs\SendFollowUp;
use App\Models\FollowUp;
use App\Models\FollowUpOptOut;
use App\Models\FollowUpRule;
use App\Models\InactivityReminder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Shop;
use App\Models\ShopMessage;
use App\Models\StockAlert;
use App\Models\User;
use App\Notifications\FollowUpNotification;
use App\Services\FollowUp\FollowUpCatalog as Cat;
use App\Services\FollowUp\TemplateRenderer;
use App\Support\Money;
use App\Support\Storefront;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Relances automatiques et personnalisables.
 *
 *   run() (toutes les 10 minutes) : 1. crée la 1re relance des situations détectées ;
 *                                   2. crée la relance suivante des chaînes déjà commencées ;
 *                                   3. met en file d'attente l'envoi des relances dues (SendFollowUp).
 *   send() : au moment d'envoyer, revérifie tout (situation toujours d'actualité, STOP, heures,
 *            une relance par personne et par jour toutes boutiques confondues), remplace les variables,
 *            envoie sur les canaux choisis.
 *
 * Une relance n'existe qu'une fois (clé unique situation + objet + destinataire + n° de tentative)
 * et est verrouillée pendant l'envoi : jamais envoyée deux fois.
 */
class FollowUpService
{
    /** Au-delà, une situation est trop ancienne pour être relancée */
    private const LOOKBACK_DAYS = 14;
    private const STOP_WORDS = ['STOP', 'ARRET', 'ARRÊT', 'STOP.', 'STOP !'];

    /** @var array<string, FollowUpRule> règles déjà lues pendant ce passage */
    private array $rules = [];

    public function rule(?int $shopId, string $type): FollowUpRule
    {
        return $this->rules[($shopId ?? 'admin') . ':' . $type] ??= FollowUpRule::resolve($shopId, $type);
    }

    /** Règle d'une relance : celle de sa boutique, ou celle de l'admin (vendeurs inactifs) */
    private function ruleFor(FollowUp $f): FollowUpRule
    {
        return $this->rule(empty(Cat::get($f->type)['admin']) ? $f->shop_id : null, $f->type);
    }

    /* ═════════════════════════════ Passage planifié ═════════════════════════════ */

    /** @return array{created:int, next:int, dispatched:int} */
    public function run(): array
    {
        $this->rules = [];
        $created = $this->detectOffers(Cat::OFFER) + $this->detectOffers(Cat::COUNTER) + $this->detectAbandoned()
            + $this->detectPendingOrders() + $this->detectLoyalty() + $this->detectInactiveVendors();
        $next = $this->scheduleNextAttempts();

        $dispatched = 0;
        FollowUp::where('status', FollowUp::STATUS_SCHEDULED)->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')->limit(500)->pluck('id')
            ->each(function ($id) use (&$dispatched) {
                SendFollowUp::dispatch($id);
                $dispatched++;
            });

        return compact('created', 'next', 'dispatched');
    }

    /** Offre (ou contre-offre) du vendeur restée en attente */
    private function detectOffers(string $type): int
    {
        $count = 0;
        ShopMessage::query()
            ->where('type', $type === Cat::OFFER ? ShopMessage::TYPE_PRICE_OFFER : ShopMessage::TYPE_COUNTER_OFFER)
            ->where('proposal_status', ShopMessage::STATUS_PENDING)
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->whereHas('receiver', fn ($q) => $q->where('role', 'client'))
            ->each(function (ShopMessage $m) use ($type, &$count) {
                $rule = $this->rule($m->shop_id, $type);
                if ($rule->enabled && $m->created_at->lte(now()->subMinutes($rule->delay_minutes))) {
                    $count += (int) (bool) $this->start($rule, $m->shop_id, $m->receiver_id, 'message', $m->id, $m->product_id);
                }
            });
        return $count;
    }

    /** Le client a posé une question sur un produit, le vendeur a répondu, puis plus rien */
    private function detectAbandoned(): int
    {
        $count = 0;
        $since = now()->subDays(self::LOOKBACK_DAYS);
        $conversations = ShopMessage::query()
            ->whereNotNull('product_id')->where('created_at', '>=', $since)
            ->whereHas('sender', fn ($q) => $q->where('role', 'client'))
            ->select('shop_id', 'product_id', 'sender_id')->distinct()->get();

        foreach ($conversations as $c) {
            $rule = $this->rule($c->shop_id, Cat::ABANDONED);
            if (!$rule->enabled) {
                continue;
            }
            $last = ShopMessage::where('shop_id', $c->shop_id)->where('product_id', $c->product_id)
                ->where(fn ($q) => $q->where('sender_id', $c->sender_id)->orWhere('receiver_id', $c->sender_id))
                ->orderByDesc('id')->first();
            // Dernier message = réponse de la boutique (pas une relance), assez ancienne
            if ($last && (int) $last->receiver_id === (int) $c->sender_id && !$last->follow_up_id
                && in_array($last->type, [ShopMessage::TYPE_TEXT, ShopMessage::TYPE_IMAGES], true)
                && $last->created_at->lte(now()->subMinutes($rule->delay_minutes))) {
                $count += (int) (bool) $this->start($rule, $c->shop_id, (int) $c->sender_id, 'message', $last->id, $c->product_id);
            }
        }
        return $count;
    }

    /** Commande en attente de confirmation : rappel au vendeur */
    private function detectPendingOrders(): int
    {
        $count = 0;
        Order::with('shop:id,user_id')->where('status', Order::STATUS_EN_ATTENTE)
            ->where('created_at', '>=', now()->subDays(self::LOOKBACK_DAYS))
            ->whereNotNull('shop_id')
            ->each(function (Order $o) use (&$count) {
                $rule = $this->rule($o->shop_id, Cat::ORDER);
                if ($rule->enabled && $o->shop?->user_id && $o->created_at->lte(now()->subMinutes($rule->delay_minutes))) {
                    $count += (int) (bool) $this->start($rule, $o->shop_id, $o->shop->user_id, 'order', $o->id);
                }
            });
        return $count;
    }

    /** Client qui a commandé dans la boutique, mais plus depuis le délai */
    private function detectLoyalty(): int
    {
        $count = 0;
        $last = Order::query()
            ->whereNotNull('user_id')->whereNotNull('shop_id')->where('status', '!=', Order::STATUS_ANNULEE)
            ->groupBy('shop_id', 'user_id')->selectRaw('shop_id, user_id, max(id) as last_id')->get();

        foreach ($last as $row) {
            $rule = $this->rule($row->shop_id, Cat::LOYALTY);
            if (!$rule->enabled) {
                continue;
            }
            $order = Order::find($row->last_id);
            $due = now()->subMinutes($rule->delay_minutes);
            // Fenêtre de 60 jours après le délai : on ne relance pas des clients partis depuis des années
            if ($order && $order->created_at->lte($due) && $order->created_at->gte($due->copy()->subDays(60))) {
                $count += (int) (bool) $this->start($rule, $row->shop_id, (int) $row->user_id, 'order', $order->id);
            }
        }
        return $count;
    }

    /** Admin : vendeur sans produit en ligne, ou qui ne s'est pas connecté depuis le délai */
    private function detectInactiveVendors(): int
    {
        $rule = $this->rule(null, Cat::INACTIVE_VENDOR);
        if (!$rule->enabled) {
            return 0;
        }
        $due = now()->subMinutes($rule->delay_minutes);
        $count = 0;

        Shop::with('owner:id,role,last_active_at')->withCount(['products as active_products' => fn ($q) => $q->where('is_active', true)])
            ->where('created_at', '<=', $due)
            ->each(function (Shop $shop) use ($rule, $due, &$count) {
                $owner = $shop->owner;
                if (!$owner || $owner->role !== 'admin') {
                    return;
                }
                if ($shop->active_products === 0) {
                    $count += (int) (bool) $this->start($rule, $shop->id, $owner->id, 'shop_sans_produit', $shop->id);
                } elseif ($owner->last_active_at && $owner->last_active_at->lte($due)) {
                    // Une chaîne par période d'absence : repart si le vendeur revient puis s'absente à nouveau
                    $count += (int) (bool) $this->start($rule, $shop->id, $owner->id, 'absence', $owner->last_active_at->timestamp);
                }
            });
        return $count;
    }

    /** 1re relance d'une chaîne, à envoyer tout de suite ; null si elle existe déjà */
    private function start(FollowUpRule $rule, ?int $shopId, int $userId, string $subjectType, int $subjectId, ?int $productId = null, ?Carbon $at = null): ?FollowUp
    {
        if (FollowUp::where('type', $rule->type)->where('subject_type', $subjectType)->where('subject_id', $subjectId)->where('user_id', $userId)->exists()) {
            return null;
        }
        return $this->createAttempt($rule, $shopId, $userId, $subjectType, $subjectId, $productId, 1, $at ?? now());
    }

    private function createAttempt(FollowUpRule $rule, ?int $shopId, int $userId, string $subjectType, int $subjectId, ?int $productId, int $attempt, Carbon $at): ?FollowUp
    {
        try {
            return FollowUp::create([
                'follow_up_rule_id' => $rule->exists ? $rule->id : null,
                'shop_id'      => $shopId,
                'user_id'      => $userId,
                'type'         => $rule->type,
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'product_id'   => $productId,
                'attempt'      => $attempt,
                'status'       => FollowUp::STATUS_SCHEDULED,
                'scheduled_at' => $at,
            ]);
        } catch (UniqueConstraintViolationException) {
            return null; // déjà créée par un autre passage
        }
    }

    /** Relance suivante des chaînes dont la dernière relance est partie il y a au moins l'intervalle */
    private function scheduleNextAttempts(): int
    {
        $count = 0;
        FollowUp::where('status', FollowUp::STATUS_SENT)
            ->where('sent_at', '>=', now()->subDays(60))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('follow_ups as nxt')
                ->whereColumn('nxt.type', 'follow_ups.type')->whereColumn('nxt.subject_type', 'follow_ups.subject_type')
                ->whereColumn('nxt.subject_id', 'follow_ups.subject_id')->whereColumn('nxt.user_id', 'follow_ups.user_id')
                ->whereColumn('nxt.attempt', '>', 'follow_ups.attempt'))
            ->each(function (FollowUp $f) use (&$count) {
                $rule = $this->ruleFor($f);
                if ($rule->enabled && $f->attempt < $rule->max_attempts && !$f->responded_at
                    && $f->sent_at->lte(now()->subMinutes($rule->interval_minutes))
                    && $this->stopReason($f) === null) {
                    $count += (int) (bool) $this->createAttempt($rule, $f->shop_id, $f->user_id, $f->subject_type, $f->subject_id, $f->product_id, $f->attempt + 1, now());
                }
            });
        return $count;
    }

    /* ═════════════════════════════ Envoi ═════════════════════════════ */

    /** Envoie une relance due ; renvoie son statut (« scheduled » si elle doit attendre) */
    public function send(int $followUpId): string
    {
        return DB::transaction(function () use ($followUpId) {
            $f = FollowUp::whereKey($followUpId)->lockForUpdate()->first();
            if (!$f || $f->status !== FollowUp::STATUS_SCHEDULED) {
                return $f?->status ?? 'introuvable';
            }

            $rule = $this->ruleFor($f);
            $clientFacing = Cat::isClientFacing($f->type);
            $user = User::find($f->user_id);

            $reason = !$rule->enabled ? 'desactive'
                : (!$user ? 'plus_d_actualite'
                : ($clientFacing && FollowUpOptOut::blocks($f->user_id, $f->shop_id) ? 'stop'
                : $this->stopReason($f)));
            if ($reason) {
                return $this->stop($f, $reason);
            }

            // Heures et jours autorisés, puis une relance par personne et par jour : on réessaiera plus tard
            if (!$rule->allowsSendingAt(now()) || $this->alreadyRemindedToday($f->user_id)) {
                return FollowUp::STATUS_SCHEDULED;
            }

            $vars = $this->variables($f, $rule, $user);
            $text = TemplateRenderer::render($rule->effectiveTemplate(), $vars);
            $channels = $this->availableChannels($rule, $f, $user, $clientFacing);
            if ($channels === []) {
                return $this->stop($f, 'aucun_canal');
            }

            $f->fill(['message' => $text, 'channels' => $channels]);
            $this->deliver($f, $rule, $user, $text, $channels, $clientFacing, $vars);
            $f->fill(['status' => FollowUp::STATUS_SENT, 'sent_at' => now()])->save();

            return FollowUp::STATUS_SENT;
        });
    }

    private function stop(FollowUp $f, string $reason): string
    {
        $f->update(['status' => FollowUp::STATUS_STOPPED, 'stop_reason' => $reason]);
        return FollowUp::STATUS_STOPPED;
    }

    /** Une relance (ou un rappel « Tu nous manques ») est déjà partie aujourd'hui vers cette personne ? */
    public function alreadyRemindedToday(int $userId): bool
    {
        $start = now()->setTimezone('Africa/Conakry')->startOfDay()->utc();
        return FollowUp::where('user_id', $userId)->where('status', FollowUp::STATUS_SENT)->where('sent_at', '>=', $start)->exists()
            || InactivityReminder::where('user_id', $userId)->where('sent_at', '>=', $start)->exists();
    }

    /** Raison d'arrêter la chaîne, ou null si la situation est toujours d'actualité */
    public function stopReason(FollowUp $f): ?string
    {
        $chainStart = FollowUp::where('type', $f->type)->where('subject_type', $f->subject_type)
            ->where('subject_id', $f->subject_id)->where('user_id', $f->user_id)->min('created_at');
        $since = $chainStart ? Carbon::parse($chainStart) : $f->created_at;

        switch ($f->type) {
            case Cat::OFFER:
            case Cat::COUNTER:
                $m = ShopMessage::find($f->subject_id);
                if (!$m) return 'plus_d_actualite';
                if ($m->proposal_status !== ShopMessage::STATUS_PENDING) return 'accepte';
                if ($this->clientWroteSince($f->shop_id, $f->user_id, $m->created_at)) return 'repondu';
                if ($this->clientOrderedSince($f->shop_id, $f->user_id, $m->created_at)) return 'commande';
                return null;

            case Cat::ABANDONED:
                $m = ShopMessage::with('product')->find($f->subject_id);
                if (!$m) return 'plus_d_actualite';
                if ($this->clientWroteSince($f->shop_id, $f->user_id, $m->created_at)) return 'repondu';
                if ($this->clientOrderedSince($f->shop_id, $f->user_id, $m->created_at)) return 'commande';
                if (!$m->product || !$m->product->is_active || ($m->product->stock !== null && $m->product->stock <= 0)) return 'plus_d_actualite';
                return null;

            case Cat::ORDER:
                $o = Order::find($f->subject_id);
                return $o && $o->status === Order::STATUS_EN_ATTENTE ? null : 'annule';

            case Cat::LOYALTY:
                return Order::where('shop_id', $f->shop_id)->where('user_id', $f->user_id)->where('id', '>', $f->subject_id)->exists()
                    ? 'commande' : null;

            case Cat::RESTOCK:
                $p = Product::find($f->product_id);
                if (!$p || !$p->is_active || ($p->stock !== null && $p->stock <= 0)) return 'plus_d_actualite';
                $ordered = OrderItem::where('product_id', $p->id)
                    ->whereHas('order', fn ($q) => $q->where('user_id', $f->user_id)->where('created_at', '>=', $since))->exists();
                return $ordered ? 'commande' : null;

            case Cat::INACTIVE_VENDOR:
                if ($f->subject_type === 'shop_sans_produit') {
                    return Product::where('shop_id', $f->subject_id)->where('is_active', true)->exists() ? 'plus_d_actualite' : null;
                }
                $owner = User::find($f->user_id);
                return $owner?->last_active_at && $owner->last_active_at->timestamp > $f->subject_id ? 'repondu' : null;
        }
        return null;
    }

    private function clientWroteSince(?int $shopId, int $userId, Carbon $since): bool
    {
        return ShopMessage::where('shop_id', $shopId)->where('sender_id', $userId)->where('created_at', '>', $since)->exists();
    }

    private function clientOrderedSince(?int $shopId, int $userId, Carbon $since): bool
    {
        return Order::where('shop_id', $shopId)->where('user_id', $userId)->where('created_at', '>', $since)->exists();
    }

    /** Canaux choisis par le vendeur et réellement disponibles pour ce destinataire */
    private function availableChannels(FollowUpRule $rule, FollowUp $f, User $user, bool $clientFacing): array
    {
        $shopOwner = $f->shop_id ? Shop::whereKey($f->shop_id)->value('user_id') : null;
        return array_values(array_filter($rule->channels ?? [], fn ($c) => match ($c) {
            'in_app'    => true,
            'messaging' => $clientFacing && $shopOwner,
            'sms'       => filled($user->phone),
            'whatsapp'  => (bool) config('services.whatsapp.enabled') && filled($user->phone),
            default     => false,
        }));
    }

    private function deliver(FollowUp $f, FollowUpRule $rule, User $user, string $text, array $channels, bool $clientFacing, array $vars): void
    {
        $stopUrl = $clientFacing ? URL::signedRoute('followups.stop', array_filter(['user' => $user->id, 'shop' => $f->shop_id])) : null;
        $footer = $clientFacing ? "\n\n" . Cat::stopFooter($rule->locale) : '';

        if (in_array('messaging', $channels, true)) {
            $message = ShopMessage::create([
                'shop_id'      => $f->shop_id,
                'product_id'   => $this->productId($f),
                'sender_id'    => Shop::whereKey($f->shop_id)->value('user_id'),
                'receiver_id'  => $user->id,
                'body'         => $text . $footer,
                'type'         => ShopMessage::TYPE_TEXT,
                'follow_up_id' => $f->id,
            ]);
            $f->shop_message_id = $message->id;
        }

        $notify = array_values(array_intersect($channels, ['in_app', 'sms']));
        if ($notify !== []) {
            $title = $clientFacing ? ($vars['{nom_boutique}'] ?: 'Shopio') : Cat::get($f->type)['label'];
            $url = $clientFacing ? ($vars['{lien_produit}'] ?: ($vars['{lien_boutique}'] ?: url('/'))) : ($vars['{lien_commande}'] ?: route('boutique.dashboard'));
            $notification = new FollowUpNotification($f->type, $title, $text, $url, $notify, $stopUrl);
            $user->notify($notification);
            if (in_array('in_app', $notify, true)) {
                $f->notification_id = $notification->id;
            }
        }

        if (in_array('whatsapp', $channels, true)) {
            // Aucun fournisseur WhatsApp n'est branché : canal prévu, message seulement journalisé
            Log::info('[Relances] WhatsApp non branché, message non envoyé', ['follow_up' => $f->id]);
        }
    }

    private function productId(FollowUp $f): ?int
    {
        return $f->product_id ?? ($f->subject_type === 'message' ? ShopMessage::whereKey($f->subject_id)->value('product_id') : null);
    }

    /* ═════════════════════════════ Variables ═════════════════════════════ */

    /** Valeurs des variables d'une relance réelle */
    public function variables(FollowUp $f, FollowUpRule $rule, User $user): array
    {
        $shop = $f->shop_id ? Shop::find($f->shop_id) : null;
        $product = null;
        $negotiated = null;
        $order = null;
        $clientName = $user->name;

        if ($f->subject_type === 'message') {
            $m = ShopMessage::with('product')->find($f->subject_id);
            $product = $m?->product;
            $negotiated = $m?->proposed_price;
        } elseif ($f->subject_type === 'stock_alert') {
            $product = Product::find($f->product_id);
        } elseif ($f->subject_type === 'order') {
            $order = Order::with('items.product', 'user')->find($f->subject_id);
            $product = $order?->items->first()?->product;
            if ($f->type === Cat::ORDER) {
                $clientName = $order?->user?->name ?? $order?->client_name ?? 'un client';
            }
        }

        return $this->buildVariables($shop, $clientName, $product, $negotiated, $order, $rule, Cat::isClientFacing($f->type));
    }

    /** Variables d'exemple pour l'aperçu (vraie boutique, vrai produit si possible) */
    public function sampleVariables(?Shop $shop, FollowUpRule $rule): array
    {
        $product = $shop ? Product::where('shop_id', $shop->id)->where('is_active', true)->latest()->first() : null;
        $price = $product?->current_price ?? 150000;
        return $this->buildVariables($shop, 'Aïssatou Bah', $product, $product ? round($price * 0.9, -3) : 135000, null, $rule, Cat::isClientFacing($rule->type), [
            '{produit}' => $product?->name ?? 'Robe en wax',
            '{prix}'    => $this->money($shop, $price),
        ]);
    }

    private function buildVariables(?Shop $shop, string $name, ?Product $product, $negotiated, ?Order $order, FollowUpRule $rule, bool $clientFacing, array $override = []): array
    {
        $promo = $rule->promo_code_id ? PromoCode::find($rule->promo_code_id) : null;
        $promoOk = $promo && $promo->is_active && (int) $promo->shop_id === (int) $shop?->id
            && (!$promo->expires_at || $promo->expires_at->isFuture())
            && (!$promo->max_uses || $promo->uses_count < $promo->max_uses);

        $vars = [
            '{prenom_client}' => Str::of($name)->trim()->explode(' ')->first() ?: $name,
            '{nom_boutique}'  => $shop?->name ?? 'Shopio',
            '{produit}'       => $product?->name ?? '',
            '{prix}'          => $order ? $this->money($shop, $order->total) : ($product ? $this->money($shop, $product->current_price) : ''),
            '{prix_negocie}'  => $negotiated ? $this->money($shop, $negotiated) : '',
            '{lien_produit}'  => $product ? route('client.products.show', $product) : '',
            '{lien_commande}' => $clientFacing ? route('client.orders.index') : route('boutique.orders.index'),
            '{lien_boutique}' => $shop ? ($clientFacing ? Storefront::publicUrl($shop) : route('boutique.dashboard')) : url('/'),
            '{code_promo}'    => $promoOk ? $promo->code : '',
            '{date_limite}'   => $promoOk && $promo->expires_at ? $promo->expires_at->format('d/m/Y') : '',
        ];
        return array_merge($vars, $override);
    }

    private function money(?Shop $shop, $amount): string
    {
        $currency = $shop?->currency ?: 'GNF';
        return $currency === 'GNF' ? Money::gnf($amount) : number_format((float) $amount, 0, ',', "\u{00A0}") . "\u{00A0}" . $currency;
    }

    /* ═════════════════════════════ Événements ═════════════════════════════ */

    /** Nouveau message d'un client : STOP, réponse comptée, relances de cette boutique arrêtées */
    public function clientMessaged(ShopMessage $message): void
    {
        $shopId = $message->shop_id;
        $userId = (int) $message->sender_id;

        if (in_array(Str::upper(trim((string) $message->body)), self::STOP_WORDS, true)) {
            $this->optOut($userId, $shopId, 'stop');
            $owner = Shop::whereKey($shopId)->value('user_id');
            if ($owner) {
                // Message automatique : sans observateurs (il ne doit pas compter comme une réponse du vendeur)
                ShopMessage::withoutEvents(fn () => ShopMessage::create([
                    'shop_id' => $shopId, 'product_id' => $message->product_id, 'sender_id' => $owner, 'receiver_id' => $userId,
                    'type' => ShopMessage::TYPE_TEXT,
                    'body' => 'C\'est noté : tu ne recevras plus de rappels de ' . (Shop::whereKey($shopId)->value('name') ?? 'cette boutique') . '. Tu peux toujours nous écrire ici.',
                ]));
            }
            return;
        }

        FollowUp::where('user_id', $userId)->where('shop_id', $shopId)->where('status', FollowUp::STATUS_SENT)
            ->whereNull('responded_at')->where('sent_at', '>=', now()->subDays(7))
            ->update(['responded_at' => now()]);
        $this->stopScheduledForClient($userId, $shopId, 'repondu');
    }

    /** Nouvelle commande : attribuée à la dernière relance (7 jours), relances de cette boutique arrêtées */
    public function orderCreated(Order $order): void
    {
        if (!$order->user_id || !$order->shop_id) {
            return;
        }
        $last = FollowUp::where('user_id', $order->user_id)->where('shop_id', $order->shop_id)
            ->where('status', FollowUp::STATUS_SENT)->whereNull('recovered_order_id')
            ->where('sent_at', '>=', now()->subDays(7))->orderByDesc('sent_at')->first();
        if ($last && Cat::isClientFacing($last->type)) {
            $last->update(['recovered_order_id' => $order->id, 'recovered_amount' => (int) round((float) $order->total)]);
        }
        $this->stopScheduledForClient($order->user_id, $order->shop_id, 'commande');
    }

    private function stopScheduledForClient(int $userId, ?int $shopId, string $reason): void
    {
        FollowUp::where('user_id', $userId)->where('shop_id', $shopId)->where('status', FollowUp::STATUS_SCHEDULED)
            ->whereIn('type', array_keys(array_filter(Cat::types(), fn ($t) => $t['audience'] === 'client')))
            ->update(['status' => FollowUp::STATUS_STOPPED, 'stop_reason' => $reason]);
    }

    /**
     * Produit de nouveau en stock : si la boutique a activé cette relance, les clients inscrits
     * reçoivent son message personnalisé (au lieu de la notification simple). Renvoie false sinon.
     */
    public function productRestocked(Product $product): bool
    {
        $rule = $this->rule($product->shop_id, Cat::RESTOCK);
        if (!$rule->enabled) {
            return false;
        }
        // Une chaîne par alerte « Me prévenir » du client ; l'alerte est ensuite retirée, comme avant
        StockAlert::where('product_id', $product->id)->get()->each(function (StockAlert $alert) use ($rule, $product) {
            $this->start($rule, $product->shop_id, $alert->user_id, 'stock_alert', $alert->id, $product->id, now()->addMinutes($rule->delay_minutes));
            $alert->delete();
        });
        return true;
    }

    public function optOut(int $userId, ?int $shopId, string $source): void
    {
        FollowUpOptOut::firstOrCreate(['user_id' => $userId, 'shop_id' => $shopId], ['source' => $source]);
        FollowUp::where('user_id', $userId)->where('status', FollowUp::STATUS_SCHEDULED)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->update(['status' => FollowUp::STATUS_STOPPED, 'stop_reason' => 'stop']);
    }

    /* ═════════════════════════════ Statistiques ═════════════════════════════ */

    /** Chiffres d'une boutique (ou de toutes) sur N jours */
    public function stats(?int $shopId, int $days = 30): array
    {
        $q = FollowUp::where('status', FollowUp::STATUS_SENT)->where('sent_at', '>=', now()->subDays($days))
            ->when($shopId, fn ($w) => $w->where('shop_id', $shopId))
            ->whereIn('type', array_keys(array_filter(Cat::types(), fn ($t) => $t['audience'] === 'client')));
        $sent = (clone $q)->count();
        $responded = (clone $q)->whereNotNull('responded_at')->count();
        return [
            'sent'      => $sent,
            'responded' => $responded,
            'rate'      => $sent ? (int) round($responded * 100 / $sent) : null,
            'orders'    => (clone $q)->whereNotNull('recovered_order_id')->count(),
            'amount'    => (int) (clone $q)->sum('recovered_amount'),
        ];
    }
}
