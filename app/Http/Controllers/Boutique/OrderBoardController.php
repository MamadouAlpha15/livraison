<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use App\Services\ImageOptimizer;
use App\Services\PushService;
use App\Services\SubscriptionService;
use App\Support\OrderBoard;
use App\Support\PageModels;
use App\Support\ProductPage;
use App\Support\Storefront;
use App\Support\TrafficSource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Commandes → « Toutes les commandes » (façon Sellio) : étapes avec compteurs, recherche, filtres,
 * commandes groupées par jour, fiche en fenêtre (articles, client, Appeler / WhatsApp / SMS, copier,
 * actions de suivi, livraison (livreur de la boutique ou entreprise de livraison), notes), nouvelle commande
 * saisie à la main. Mise à jour en direct (toutes les 30 s). L'ancienne page « Livraisons » renvoie ici.
 */
class OrderBoardController extends Controller
{
    public const PERIODS = ['all' => 'Toutes les dates', 'today' => 'Aujourd’hui', 'yesterday' => 'Hier', '7d' => '7 derniers jours', '30d' => '30 derniers jours', 'month' => 'Ce mois-ci'];
    private const PER_PAGE = 30;

    private function shop(): Shop
    {
        $u = Auth::user();
        $shop = $u->shop ?? $u->assignedShop;
        abort_unless($shop, 404, 'Aucune boutique associée à ce compte.');
        return $shop;
    }

    private function own(Order $order): Shop
    {
        $shop = $this->shop();
        abort_unless((int) $order->shop_id === (int) $shop->id, 404);
        return $shop;
    }

    public function index(Request $request)
    {
        $shop = $this->shop();

        // Plan gratuit : 10 commandes traitées par mois (même règle que l'ancienne page)
        $isPro = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
        if (!$isPro && app(SubscriptionService::class)->processedOrdersThisMonth($shop) >= 10) {
            return redirect()->route('boutique.subscription.upgrade')
                ->with('error', '🔒 Limite atteinte : 10 commandes traitées ce mois-ci avec le Plan Gratuit. Passez au Plan Pro pour continuer sans limite.');
        }

        $stage = array_key_exists($request->query('etape'), OrderBoard::STAGES) ? $request->query('etape') : 'tous';
        $period = array_key_exists($request->query('periode'), self::PERIODS) ? $request->query('periode') : 'all';
        // « search » : ancien paramètre (liens du tableau de bord, ancienne page Livraisons)
        $search = trim((string) $request->query('q', $request->query('search', '')));
        $source = (string) $request->query('source', '');
        $productId = (int) $request->query('produit', 0);

        $base = function () use ($shop, $period, $search, $source, $productId) {
            $q = Order::where('shop_id', $shop->id);
            match ($period) {
                'today' => $q->whereDate('created_at', today()),
                'yesterday' => $q->whereDate('created_at', today()->subDay()),
                '7d' => $q->where('created_at', '>=', now()->subDays(7)),
                '30d' => $q->where('created_at', '>=', now()->subDays(30)),
                'month' => $q->where('created_at', '>=', now()->startOfMonth()),
                default => null,
            };
            if ($search !== '') {
                $digits = preg_replace('/\D/', '', $search);
                $q->where(function ($w) use ($search, $digits) {
                    $w->where('client_name', 'like', "%{$search}%")
                      ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                      ->orWhereHas('items.product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                    if ($digits !== '') $w->orWhere('id', (int) ltrim($digits, '0') ?: 0)->orWhere('client_phone', 'like', "%{$digits}%");
                });
            }
            if ($source !== '') $source === 'direct' ? $q->whereNull('source') : $q->where('source', $source);
            if ($productId) $q->whereHas('items', fn ($i) => $i->where('product_id', $productId));
            return $q;
        };

        $counts = ['tous' => $base()->count()];
        foreach (array_keys(OrderBoard::STAGES) as $k) $counts[$k] = OrderBoard::scope($base(), $k)->count();

        $q = $base()->with(['client:id,name,phone', 'items.product:id,name,image', 'livreur:id,name', 'deliveryCompany:id,name', 'driver:id,name'])->latest();
        if ($stage !== 'tous') OrderBoard::scope($q, $stage);
        $page = $q->paginate(self::PER_PAGE)->withQueryString();

        $settings = OrderBoard::settings(Storefront::forShop($shop));
        $cards = $page->getCollection()->map(fn ($o) => $this->card($o, $shop, $settings))->values();
        $groups = $cards->groupBy('day');

        $view = [
            'shop' => $shop, 'groups' => $groups, 'page' => $page, 'counts' => $counts, 'stage' => $stage, 'period' => $period,
            'search' => $search, 'source' => $source, 'productId' => $productId, 'settings' => $settings, 'devise' => $shop->currency ?? 'GNF',
        ];
        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('boutique.orders._cards', $view)->render(),
                'counts' => $counts, 'next' => $page->hasMorePages() ? $page->nextPageUrl() . '&partial=1' : null,
                'cartsWaiting' => $this->cartsWaiting($shop),
            ]);
        }

        $sources = Order::where('shop_id', $shop->id)->select('source')->distinct()->pluck('source')
            ->map(fn ($s) => $s ? ['value' => $s, 'label' => TrafficSource::label($s)] : ['value' => 'direct', 'label' => 'Direct (sans source)'])->values();
        $products = $shop->products()->orderBy('name')->get()
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (int) $p->current_price, 'stock' => $p->stock, 'active' => (bool) $p->is_active, 'variants' => (bool) $p->has_variants])->values();

        return view('boutique.orders.board', $view + [
            'sources' => $sources, 'products' => $products, 'communes' => PageModels::COMMUNES,
            'cartsWaiting' => $this->cartsWaiting($shop),
            'delivery' => $this->deliveryChoices($shop),
        ]);
    }

    /** Ancienne page « Livraisons » : tout se fait maintenant dans la fiche de la commande. */
    public function livraisons(Request $request)
    {
        $q = trim((string) $request->query('search', ''));
        return redirect()->route('boutique.orders.index', $q !== '' ? ['q' => $q] : []);
    }

    /**
     * Livreurs de la boutique et entreprises de livraison (validées, actives, du même pays) avec leurs zones,
     * pour la section « Livraison » de la fiche.
     */
    private function deliveryChoices(Shop $shop): array
    {
        $riders = User::livreurs()->inShop($shop->id)->orderBy('name')->get(['id', 'name', 'is_available'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'available' => (bool) $u->is_available])->values();
        $companies = DeliveryCompany::where('approved', true)->where('active', true)
            ->when($shop->country, fn ($q) => $q->where('country', $shop->country))
            ->with(['zones' => fn ($z) => $z->where('active', true)->orderBy('price')])
            ->orderBy('name')->get()
            ->map(fn ($c) => [
                'id' => $c->id, 'name' => $c->name,
                'zones' => $c->zones->map(fn ($z) => ['id' => $z->id, 'name' => $z->name, 'price' => (int) $z->price])->values(),
            ])->values();
        return ['riders' => $riders, 'companies' => $companies, 'ridersUrl' => route('boutique.livreurs.index')];
    }

    /**
     * Livraison d'une commande (JSON), depuis sa fiche : à un livreur de la boutique, ou confiée à une
     * entreprise de livraison. Mêmes règles et mêmes notifications que l'ancienne page Livraisons
     * (Employe\OrderController@assign et @sendToCompany).
     */
    public function delivery(Request $request, Order $order): JsonResponse
    {
        $shop = $this->own($order);
        if (in_array($order->status, [Order::STATUS_LIVREE, Order::STATUS_ANNULEE], true)) {
            return response()->json(['message' => $order->status === Order::STATUS_LIVREE ? 'Cette commande est déjà livrée.' : 'Cette commande est annulée : remettez-la « Non traitée » avant de l’assigner.'], 422);
        }
        if (!app(SubscriptionService::class)->canProcessOrder($shop)) {
            return response()->json(['message' => 'Limite de 10 commandes traitées ce mois-ci avec le Plan Gratuit. Passez au Plan Pro pour continuer.', 'upgrade' => route('boutique.subscription.upgrade')], 403);
        }
        $data = $request->validate([
            'kind'       => ['required', Rule::in(['rider', 'company'])],
            'rider_id'   => ['required_if:kind,rider', 'nullable', 'integer'],
            'company_id' => ['required_if:kind,company', 'nullable', 'integer'],
            'zone_id'    => ['nullable', 'integer'],
        ], [
            'rider_id.required_if'   => 'Choisissez un livreur.',
            'company_id.required_if' => 'Choisissez une entreprise de livraison.',
        ]);
        $cur = $shop->currency ?? 'GNF';
        $push = app(PushService::class);

        if ($data['kind'] === 'rider') {
            $rider = User::livreurs()->inShop($shop->id)->find($data['rider_id']);
            if (!$rider) return response()->json(['message' => 'Ce livreur ne fait pas partie de votre boutique.', 'errors' => ['rider_id' => ['Choisissez un livreur de votre boutique.']]], 422);

            $order->livreur_id = $rider->id;
            $order->delivery_company_id = null;
            $order->driver_id = null;
            if ($order->status === Order::STATUS_EN_ATTENTE) {
                $order->status = Order::STATUS_CONFIRMEE;
                $order->forceFill(['followup' => null, 'followup_at' => null]);
            }
            $order->save();

            try { $rider->notify(new OrderStatusNotification($order, 'Une nouvelle commande vous a été assignée.')); } catch (\Throwable $e) {}
            try { $push->sendToUser($rider, 'Nouvelle commande assignée 📦', 'Commande #' . str_pad($order->id, 4, '0', STR_PAD_LEFT) . ' · ' . number_format($order->total, 0, ',', ' ') . ' ' . $cur, 1, '/livreur/orders'); } catch (\Throwable $e) {}
            if ($order->user) {
                try { $push->sendToUser($order->user, 'Commande confirmée ✅', 'Votre commande a été confirmée et un livreur a été assigné.', 1, '/client/orders'); } catch (\Throwable $e) {}
            }
            $done = '🛵 Commande #' . $order->id . ' assignée à ' . $rider->name;
        } else {
            $company = DeliveryCompany::where('approved', true)->where('active', true)->find($data['company_id']);
            if (!$company) return response()->json(['message' => 'Cette entreprise de livraison n’est plus disponible.', 'errors' => ['company_id' => ['Choisissez une autre entreprise.']]], 422);
            $zone = !empty($data['zone_id']) ? $company->zones()->where('active', true)->find($data['zone_id']) : null;

            // Comme l'ancienne page : la commande attend que l'entreprise l'accepte
            $order->fill([
                'delivery_company_id' => $company->id,
                'delivery_zone_id'    => $zone?->id,
                'delivery_fee'        => $zone?->price ?? $order->delivery_fee,
                'livreur_id'          => null,
                'driver_id'           => null,
                'status'              => Order::STATUS_EN_ATTENTE,
            ]);
            $order->forceFill(['followup' => null, 'followup_at' => null])->save();
            if ($company->user) {
                try { $push->sendToUser($company->user, 'Nouvelle commande à livrer 📦', 'La boutique vous a confié une commande de ' . number_format($order->total, 0, ',', ' ') . ' ' . $cur . '.', 1, '/company/orders'); } catch (\Throwable $e) {}
            }
            $done = '🏢 Commande #' . $order->id . ' confiée à ' . $company->name;
        }

        $order->load(['client:id,name,phone', 'items.product:id,name,image', 'livreur:id,name', 'deliveryCompany:id,name', 'driver:id,name']);
        return response()->json(['message' => $done, 'order' => $this->card($order, $shop, OrderBoard::settings(Storefront::forShop($shop)))]);
    }

    /** Paniers abandonnés à rappeler (pastille de l'onglet). */
    private function cartsWaiting(Shop $shop): int
    {
        $c = OrderBoard::settings(Storefront::forShop($shop))['carts'];
        if (!$c['enabled']) return 0;
        return AbandonedCart::where('shop_id', $shop->id)->where('state', 'a_rappeler')->whereNotNull('phone')
            ->where('last_activity_at', '<=', now()->subMinutes($c['delay']))->count();
    }

    /** Données d'une commande pour sa carte et sa fiche. */
    private function card(Order $o, Shop $shop, array $settings): array
    {
        $cur = $shop->currency ?? 'GNF';
        $money = fn ($v) => number_format((float) $v, 0, ',', ' ') . ' ' . $cur;
        $stage = OrderBoard::stageOf($o);
        $name = $o->client->name ?? $o->client_name ?? 'Client invité';
        $phone = $o->client_phone ?: ($o->client->phone ?? null);
        $items = $o->items->map(fn ($i) => [
            'name' => ($i->product->name ?? 'Produit supprimé') . ($i->variant_name ? ' — ' . $i->variant_name : ''),
            'qty' => (int) $i->quantity, 'price' => $money($i->price), 'line' => $money($i->price * $i->quantity),
            'thumb' => $i->product && $i->product->image ? (ImageOptimizer::url($i->product->image, 'thumb') ?? asset('storage/' . $i->product->image)) : null,
        ])->values();
        $count = (int) $o->items->sum('quantity');
        $at = $o->created_at->timezone('Africa/Conakry')->locale('fr');
        $first = Str::of($name)->explode(' ')->first();
        $products = $o->items->map(fn ($i) => ($i->product->name ?? 'Produit') . ' × ' . $i->quantity)->implode(', ');
        $wa = OrderBoard::fill($settings['orders']['wa'], ['prenom' => $first, 'boutique' => $shop->name, 'produits' => $products, 'total' => $money($o->total), 'numero' => $o->id]);
        $intl = OrderBoard::intl($phone);

        // followup_at n'est pas typé dans le modèle Order : lu ici comme une date UTC
        $fa = $o->followup_at ? Carbon::parse($o->followup_at, 'UTC')->timezone('Africa/Conakry')->locale('fr') : null;
        $day = $at->isToday() ? 'Aujourd’hui' : ($at->isYesterday() ? 'Hier' : Str::ucfirst($at->translatedFormat('l j F Y')));
        return [
            'id' => $o->id, 'day' => $day, 'stage' => $stage,
            'name' => $name, 'phone' => $phone, 'phoneShow' => $phone ? trim(chunk_split(preg_replace('/\D/', '', $phone), 3, ' ')) : null,
            'address' => (string) $o->delivery_destination,
            'total' => $money($o->total), 'fee' => $o->delivery_fee ? $money($o->delivery_fee) : null,
            'count' => $count, 'time' => $at->format('H:i'), 'date' => $at->translatedFormat('j F Y à H:i'), 'dateShort' => $at->translatedFormat('j F Y'),
            'items' => $items, 'source' => $o->source ? TrafficSource::label($o->source) : null,
            'note' => (string) $o->vendor_note,
            'followupAt' => $fa?->format('Y-m-d\TH:i'),
            'followupLabel' => $fa?->translatedFormat('j F à H:i'),
            'livreur' => $o->livreur->name ?? ($o->deliveryCompany->name ?? null),
            // Qui livre : livreur de la boutique, ou entreprise de livraison (avec son chauffeur une fois acceptée)
            'delivery' => $o->livreur_id
                ? ['kind' => 'rider', 'id' => $o->livreur_id, 'name' => $o->livreur->name ?? 'Livreur']
                : ($o->delivery_company_id
                    ? ['kind' => 'company', 'id' => $o->delivery_company_id, 'name' => $o->deliveryCompany->name ?? 'Entreprise de livraison', 'driver' => $o->driver_id ? ($o->driver->name ?? null) : null, 'zone' => $o->delivery_zone_id]
                    : null),
            'tel' => $intl ? 'tel:+' . $intl : null,
            'wa' => $intl ? 'https://wa.me/' . $intl . '?text=' . rawurlencode($wa) : null,
            'sms' => $intl ? 'sms:+' . $intl . '?body=' . rawurlencode($wa) : null,
            'copy' => 'Commande n° ' . $o->id . ' — ' . $at->format('d/m/Y H:i') . "\n" . $name . ($phone ? ' · ' . $phone : '') . "\n" . ($o->delivery_destination ? 'Adresse : ' . $o->delivery_destination . "\n" : '')
                . $o->items->map(fn ($i) => '• ' . ($i->product->name ?? 'Produit') . ' × ' . $i->quantity . ' = ' . $money($i->price * $i->quantity))->implode("\n") . "\nTotal : " . $money($o->total) . ' (paiement à la livraison)',
            'urls' => ['stage' => route('boutique.orders.stage', $o), 'note' => route('boutique.orders.note', $o), 'delivery' => route('boutique.orders.delivery', $o)],
        ];
    }

    /** Change l'étape de suivi d'une commande (JSON). */
    public function stage(Request $request, Order $order): JsonResponse
    {
        $shop = $this->own($order);
        $data = $request->validate([
            'stage' => ['required', Rule::in(array_keys(OrderBoard::STAGES))],
            'at' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ], ['stage.in' => 'Étape inconnue.']);
        [, $status, $follow] = OrderBoard::STAGES[$data['stage']];
        $before = OrderBoard::stageOf($order);

        $at = null;
        if ($data['stage'] === 'programmee') {
            if (empty($data['at'])) return response()->json(['message' => 'Choisissez la date et l’heure prévues.', 'errors' => ['at' => ['Choisissez la date et l’heure prévues.']]], 422);
            $at = Carbon::createFromFormat('Y-m-d\TH:i', $data['at'], 'Africa/Conakry')->utc();
        }
        $order->status = $status;
        $order->forceFill(['followup' => $follow, 'followup_at' => $at])->save();

        // Le client est prévenu quand sa commande est confirmée (comme sur l'ancienne page)
        if ($data['stage'] === 'confirmee' && $before !== 'confirmee' && $order->user) {
            try {
                app(PushService::class)->sendToUser($order->user, 'Commande confirmée ✅', 'Votre commande de ' . number_format($order->total, 0, ',', ' ') . ' ' . ($shop->currency ?? 'GNF') . ' a été confirmée.', 1, '/client/orders');
            } catch (\Throwable $e) {}
        }

        $order->load(['client:id,name,phone', 'items.product:id,name,image', 'livreur:id,name', 'deliveryCompany:id,name', 'driver:id,name']);
        return response()->json(['order' => $this->card($order, $shop, OrderBoard::settings(Storefront::forShop($shop)))]);
    }

    /** Notes de la boutique sur la commande (JSON). */
    public function note(Request $request, Order $order): JsonResponse
    {
        $this->own($order);
        $data = $request->validate(['note' => ['nullable', 'string', 'max:2000']], ['note.max' => 'La note ne peut pas dépasser 2 000 caractères.']);
        $order->forceFill(['vendor_note' => trim(strip_tags((string) ($data['note'] ?? ''))) ?: null])->save();
        return response()->json(['ok' => true]);
    }

    /** Message WhatsApp de confirmation (bouton « Réglages »). */
    public function settings(Request $request)
    {
        $shop = $this->shop();
        $data = $request->validate(['wa' => ['required', 'string', 'max:600']], ['wa.required' => 'Écrivez le message.', 'wa.max' => 'Le message ne peut pas dépasser 600 caractères.']);
        $sf = Storefront::forShop($shop);
        $sf['orders'] = ['wa' => $data['wa']];
        $shop->update(['storefront' => Storefront::normalize($sf)]);
        return response()->json(['ok' => true]);
    }

    /**
     * Nouvelle commande saisie par la boutique (appel, message, client venu en boutique) :
     * mêmes règles que la commande en ligne (stock, packs), payée à la livraison.
     */
    public function store(Request $request): JsonResponse
    {
        $shop = $this->shop();
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'commune' => ['required', Rule::in(PageModels::COMMUNES)],
            'landmark' => ['nullable', 'string', 'max:120'],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
            'cart' => ['nullable', 'integer'],
        ], [
            'name.required' => 'Indiquez le nom du client.', 'phone.required' => 'Indiquez le téléphone du client.',
            'commune.required' => 'Choisissez la commune.', 'items.required' => 'Ajoutez au moins un produit.',
        ]);
        $phone = preg_replace('/\D/', '', $data['phone']);
        if (strlen($phone) === 12 && str_starts_with($phone, '224')) $phone = substr($phone, 3);
        if (!preg_match('/^6\d{8}$/', $phone)) {
            return response()->json(['message' => 'Le numéro doit avoir 9 chiffres et commencer par 6.', 'errors' => ['phone' => ['Le numéro doit avoir 9 chiffres et commencer par 6.']]], 422);
        }

        try {
            $order = self::createOrder($shop, $data['items'], trim($data['name']), $phone, $data['commune'], (string) ($data['landmark'] ?? ''));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Commande créée depuis un panier abandonné : le panier est récupéré
        if (!empty($data['cart'])) {
            AbandonedCart::where('shop_id', $shop->id)->whereKey($data['cart'])->update(['state' => 'recupere', 'order_id' => $order->id]);
        }

        return response()->json(['id' => $order->id], 201);
    }

    /** Crée une commande payée à la livraison (stock vérifié et décompté, packs appliqués). */
    public static function createOrder(Shop $shop, array $items, string $name, string $phone, string $commune, string $landmark): Order
    {
        $qty = [];
        foreach ($items as $it) $qty[(int) $it['id']] = ($qty[(int) $it['id']] ?? 0) + (int) $it['qty'];

        return DB::transaction(function () use ($shop, $qty, $name, $phone, $commune, $landmark) {
            $products = Product::where('shop_id', $shop->id)->whereIn('id', array_keys($qty))->lockForUpdate()->get()->keyBy('id');
            $total = 0; $lines = [];
            foreach ($qty as $id => $n) {
                $p = $products->get($id);
                if (!$p) throw new \RuntimeException('Un des produits n’existe plus dans la boutique.');
                if ($p->has_variants) throw new \RuntimeException('« ' . $p->name . ' » a des options (taille, couleur…) : il ne peut pas être ajouté ici.');
                if ($p->stock !== null && $p->stock < $n) throw new \RuntimeException($p->stock > 0 ? 'Il ne reste que ' . $p->stock . ' « ' . $p->name . ' » en stock.' : '« ' . $p->name . ' » est en rupture de stock.');
                $lines[$id] = ProductPage::lineTotal($p, $n);
                $total += $lines[$id];
            }
            $order = Order::create([
                'user_id' => null, 'client_name' => $name, 'shop_id' => $shop->id, 'total' => $total,
                'status' => Order::STATUS_EN_ATTENTE, 'client_phone' => $phone,
                'delivery_destination' => $commune . (trim($landmark) !== '' ? ' — ' . trim($landmark) : ''),
            ]);
            foreach ($qty as $id => $n) {
                $p = $products[$id];
                OrderItem::create(['order_id' => $order->id, 'product_id' => $p->id, 'quantity' => $n, 'price' => round($lines[$id] / $n, 2)]);
                if ($p->stock !== null) $p->decrement('stock', $n);
            }
            Payment::create(['order_id' => $order->id, 'method' => Payment::METHOD_CASH, 'amount' => $total, 'status' => 'en_attente']);
            return $order;
        });
    }
}
