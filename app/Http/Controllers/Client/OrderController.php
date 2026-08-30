<?php

// ============================================================
// FICHIER : app/Http/Controllers/Client/OrderController.php
// RÔLE    : Gère toutes les commandes côté CLIENT.
//           - Afficher la liste des commandes du client
//           - Créer une commande manuelle (pharmacie, etc.)
//           - Créer une commande depuis un produit spécifique
// ============================================================

// Indique à PHP le "dossier logique" (namespace) de ce fichier
namespace App\Http\Controllers\Client;

// On importe la classe Controller de base (celle qu'on vient de commenter)
use App\Http\Controllers\Controller;

// On importe les modèles (chaque modèle représente une table de la base de données)
use App\Models\Order;        // Table "orders" — les commandes
use App\Models\OrderItem;    // Table "order_items" — les lignes d'une commande (produit + quantité)
use App\Models\Product;      // Table "products" — les produits
use App\Models\Payment;      // Table "payments" — les paiements liés aux commandes
use App\Models\Shop;         // Table "shops" — les boutiques
use App\Models\PromoCode;    // Table "promo_codes" — les codes de réduction boutique
use App\Models\ShopMessage;  // Table "shop_messages" — les messages entre client et vendeur
use App\Services\SubscriptionService; // Vérification des limites du plan
use App\Services\PushService;
use App\Services\LoyaltyService;
use App\Services\PromoCodeService;
use App\Rules\RealisticGuineaPhone;
use App\Rules\RealisticFullName;
use App\Services\ChapChapPayService;
use Illuminate\Support\Facades\Log;

// On importe Request : objet qui contient toutes les données envoyées par le formulaire (POST, GET...)
use Illuminate\Http\Request;

// On importe Auth : façade pour récupérer l'utilisateur connecté facilement
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

// ============================================================
// Déclaration de la classe
// "extends Controller" = hérite des fonctionnalités de base (validate, authorize...)
// ============================================================
class OrderController extends Controller
{
    // ============================================================
    // MÉTHODE : index()
    // ROUTE   : GET /client/orders
    // RÔLE    : Affiche la liste de toutes les commandes du client connecté
    // ============================================================
    public function index(Request $request)
    {
        $user   = Auth::user();
        $status = $request->get('status', 'all');

        $query = $user->orders()
            ->with(['shop', 'items.product', 'review'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Le client vient de consulter ses commandes → efface le badge PWA
        $user->update(['orders_badge_seen_at' => now()]);

        // Compteurs globaux (indépendants du filtre actif) pour les onglets et les stats
        $counts = [
            'all'          => $user->orders()->count(),
            'en_attente'   => $user->orders()->where('status', Order::STATUS_EN_ATTENTE)->count(),
            'confirmée'    => $user->orders()->where('status', Order::STATUS_CONFIRMEE)->count(),
            'en_livraison' => $user->orders()->where('status', Order::STATUS_EN_LIVRAISON)->count(),
            'livrée'       => $user->orders()->where('status', Order::STATUS_LIVREE)->count(),
            'annulée'      => $user->orders()->where('status', Order::STATUS_ANNULEE)->count(),
        ];

        return view('client.orders.index', compact('orders', 'counts'));
    }

    // ============================================================
    // MÉTHODE : markBadgeSeen()
    // ROUTE   : POST /client/orders/badge-seen
    // RÔLE    : Acquitte le badge PWA sans recharger la page (ex: dismiss
    //           d'une notif de commande dans la cloche du dashboard)
    // ============================================================
    public function markBadgeSeen(Request $request)
    {
        $request->user()->update(['orders_badge_seen_at' => now()]);
        return response()->json(['ok' => true]);
    }

    // ============================================================
    // MÉTHODE : create()
    // ROUTE   : GET /client/orders/create
    // RÔLE    : Affiche le formulaire pour créer une commande manuelle
    //           (ex : commande de pharmacie où on saisit manuellement)
    // ============================================================
    public function create()
    {
        // On récupère toutes les boutiques approuvées par l'admin
        // ->with(['products' => ...]) = on charge les produits de chaque boutique
        //   mais uniquement certaines colonnes (id, shop_id, name, price, image, description)
        //   pour ne pas charger des données inutiles
        // ->get(['id','name','type']) = on ne récupère que ces 3 colonnes de la table shops
        $shops = Shop::where('is_approved', true)
            ->with(['products' => fn($q) => $q->select('id','shop_id','name','price','image','description')])
            ->get(['id','name','type']);

        // On crée un tableau associatif (clé = id boutique, valeur = liste de produits formatés)
        // mapWithKeys() = transforme une collection en tableau avec des clés personnalisées
        // C'est ce tableau qu'on envoie en JavaScript pour afficher les produits selon la boutique choisie
        $allProducts = $shops->mapWithKeys(fn($s) => [
            // Pour chaque boutique $s, on crée une entrée [shop_id => [liste de produits]]
            $s->id => $s->products->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'price'       => (float) $p->price,              // (float) = convertit en nombre décimal
                'image_url'   => $p->image                       // Si l'image existe...
                    ? asset('storage/'.$p->image)                // ...on génère l'URL publique
                    : null,                                       // ...sinon null
                'description' => $p->description,
            ])->values(), // ->values() = reindexe le tableau de 0 à N (supprime les clés originales)
        ]);

        // On retourne la vue avec les données nécessaires
        return view('client.orders.create', [
            'shops'             => $shops,           // Liste des boutiques pour le menu déroulant
            'allProducts'       => $allProducts,     // Produits groupés par boutique (pour JavaScript)
            'selectedShopId'    => request('shop_id'),    // Si l'URL contient ?shop_id=X, on le pré-sélectionne
            'selectedProductId' => request('product_id'), // Si l'URL contient ?product_id=X, on le pré-sélectionne
        ]);
    }

    // ============================================================
    // MÉTHODE : store()
    // ROUTE   : POST /client/orders
    // RÔLE    : Enregistre une commande manuelle en base de données
    //           (appelée quand le client soumet le formulaire de create())
    // ============================================================
    public function store(Request $request)
    {
        // On valide les données envoyées par le formulaire
        // Si une règle échoue, Laravel redirige automatiquement avec les erreurs
        $request->validate([
            'shop_id'    => 'required|exists:shops,id',           // Obligatoire + doit exister dans la table shops
            'total'      => 'required|numeric|min:1',             // Obligatoire + nombre + minimum 1
            'ordonnance' => 'nullable|file|mimes:pdf,jpg,png|max:2048', // Optionnel + fichier PDF/image max 2 Mo
            'image'      => 'nullable|image|max:4096',            // Optionnel + image max 4 Mo
        ]);

        // On initialise les chemins des fichiers à null (au cas où aucun fichier n'est envoyé)
        $imagePath      = null;
        $ordonnancePath = null;

        // Si le formulaire contient un fichier "image"...
        if ($request->hasFile('image')) {
            // ...on le sauvegarde dans storage/app/public/order_images/
            // ->store('dossier', 'disk') retourne le chemin relatif du fichier sauvegardé
            $imagePath = $request->file('image')->store('order_images', 'public');
        }

        // Si le formulaire contient un fichier "ordonnance" (prescription médicale)...
        if ($request->hasFile('ordonnance')) {
            // ...on le sauvegarde dans storage/app/public/ordonnances/
            $ordonnancePath = $request->file('ordonnance')->store('ordonnances', 'public');
        }

        // On crée la commande en base de données avec la méthode create()
        // Elle insère une ligne dans la table "orders"
        $order = Order::create([
            'user_id'    => Auth::id(),                  // ID de l'utilisateur connecté
            'shop_id'    => $request->shop_id,           // ID de la boutique choisie
            'total'      => $request->total,             // Montant total
            'status'     => Order::STATUS_EN_ATTENTE,    // Statut initial : "en_attente" (constante du modèle)
            'ordonnance' => $ordonnancePath,             // Chemin de l'ordonnance (null si pas fournie)
            'image'      => $imagePath,                  // Chemin de l'image (null si pas fournie)
        ]);

        // On crée le paiement associé à cette commande
        // Chaque commande a un paiement (même en attente)
        Payment::create([
            'order_id' => $order->id,     // Lien avec la commande qu'on vient de créer
            'method'   => 'cash',         // Mode de paiement : espèces à la livraison
            'amount'   => $request->total,// Montant identique au total de la commande
            'status'   => 'en_attente',   // Le paiement n'a pas encore été effectué
        ]);

        // Notifier le vendeur par push
        try {
            $shopOwner = $order->shop->user;
            if ($shopOwner) {
                $push  = app(PushService::class);
                $push->sendToUser(
                    $shopOwner,
                    'Nouvelle commande !',
                    'Vous avez reçu une nouvelle commande de ' . number_format($order->total, 0, ',', ' ') . ' GNF.',
                    $push->vendorBadgeCount($shopOwner),
                    '/employe/orders'
                );
            }
        } catch (\Throwable $e) {}

        // Notifier aussi les employés/vendeurs de la boutique (pas seulement le propriétaire)
        try {
            app(PushService::class)->notifyShopStaff(
                $order->shop,
                'Nouvelle commande !',
                'Vous avez reçu une nouvelle commande de ' . number_format($order->total, 0, ',', ' ') . ' GNF.',
                '/employe/orders',
                $order->shop->user_id
            );
        } catch (\Throwable $e) {}

        return redirect()->route('client.orders.index')
            ->with('success', 'Commande passée avec succès ! Paiement en cash à la livraison.');
    }

    // ============================================================
    // MÉTHODE : createFromProduct()
    // ROUTE   : GET /client/products/{product}/order
    // RÔLE    : Affiche la page de commande pour un produit précis
    //           Charge aussi l'historique des messages avec le vendeur
    //           (car cette page contient le chat intégré)
    // PARAMÈTRE : $product = Laravel injecte automatiquement le produit
    //             dont l'ID est dans l'URL (route model binding)
    // ============================================================
    public function createFromProduct(Product $product, Request $request)
    {
        // abort_unless(condition, code_http) = si la condition est FAUSSE, on arrête avec une erreur
        // optional() = évite une erreur si $product->shop est null
        // ->is_approved = la boutique doit être validée par l'admin, sinon erreur 404
        abort_unless(optional($product->shop)->is_approved, 404);

        // On récupère le client connecté (peut être null : commande possible sans compte)
        $client  = Auth::user();

        // On récupère la boutique qui vend ce produit (relation définie dans le modèle Product)
        $shop    = $product->shop;

        // Un visiteur non connecté n'a pas d'historique de messages (il faut un compte pour discuter)
        $messages = collect();

        if ($client) {
            // On charge tous les messages échangés entre CE client et le vendeur SUR CE produit
            $messages = ShopMessage::where('shop_id', $shop->id)          // Messages de cette boutique
                ->where('product_id', $product->id)                       // Pour ce produit spécifique
                ->where(function ($q) use ($client) {
                    // On veut les messages où le client est soit l'envoyeur soit le destinataire
                    // "use ($client)" = on passe la variable $client dans cette fonction anonyme
                    $q->where('sender_id', $client->id)    // Messages envoyés par le client
                      ->orWhere('receiver_id', $client->id); // OU messages reçus par le client
                })
                ->with(['sender:id,name'])  // On charge aussi le nom de l'envoyeur (uniquement id et name)
                ->orderBy('created_at')     // Trie par date d'envoi (du plus ancien au plus récent)
                ->get();                    // Exécute la requête et retourne les résultats

            // On marque tous les messages reçus par le client comme "lus"
            // (met à jour la colonne read_at avec la date et l'heure actuelle)
            ShopMessage::where('shop_id', $shop->id)
                ->where('product_id', $product->id)
                ->where('receiver_id', $client->id)   // Messages destinés au client
                ->whereNull('read_at')                // Seulement ceux pas encore lus
                ->update(['read_at' => now()]);        // now() = date et heure actuelle
        }

        // Variantes actives du produit (taille/couleur…), + variante présélectionnée via ?variant_id=
        $variants = $product->activeVariants()->get();
        $selectedVariantId = $request->integer('variant_id') ?: null;

        // Code promo actif de la boutique à mettre en avant sur la fiche produit (le plus récent, s'il en existe un valide)
        $activePromo = PromoCode::where('shop_id', $shop->id)
            ->active()
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('uses_count', '<', 'max_uses');
            })
            ->latest()
            ->first();

        // Autres produits de la même boutique, à suggérer en bas de page (favorise le
        // panier moyen : le client voit qu'il y a autre chose à acheter avant de repartir).
        // On privilégie la même catégorie d'abord, puis on complète avec le reste de la
        // boutique si besoin, en excluant toujours le produit déjà affiché.
        $relatedProducts = Product::where('shop_id', $shop->id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->when($product->category, function ($q) use ($product) {
                $q->orderByRaw('category = ? DESC', [$product->category]);
            })
            ->latest()
            ->limit(6)
            ->get();

        // On retourne la vue avec le produit et les messages
        return view('client.orders.create_from_product', [
            'product'  => $product,   // Les infos du produit
            'messages' => $messages,  // L'historique des messages
            'loyaltyBalance' => $client->loyalty_points ?? 0, // Solde de points du client (0 si invité)
            'variants' => $variants,
            'selectedVariantId' => $selectedVariantId,
            'activePromo' => $activePromo,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    // ============================================================
    // MÉTHODE : storeProduct()
    // ROUTE   : POST /client/orders/product
    // RÔLE    : Enregistre une commande pour un produit spécifique
    //           (appelée quand le client clique "Commander" sur un produit)
    // ============================================================
    public function storeProduct(Request $request)
    {
        // Règles de base
        // client_phone est vérifié avec RealisticGuineaPhone que le client soit
        // invité ou connecté (le champ reste modifiable dans les deux cas) : ça
        // filtre les numéros bidons (ex: 6222222222) qui rendent la commande
        // injoignable pour le vendeur, sans bloquer un vrai numéro guinéen.
        $rules = [
            'product_id'           => 'required|exists:products,id',
            'variant_id'           => 'nullable|integer|exists:product_variants,id',
            'quantity'             => 'required|integer|min:1',
            'delivery_destination' => ['nullable', 'string', 'min:2', 'max:255'],
            'client_phone'         => ['nullable', 'string', 'max:30', new RealisticGuineaPhone],
            'payment_method'       => ['nullable', 'in:cash,online'],
        ];

        // Un visiteur sans compte doit obligatoirement donner son nom, son téléphone et son adresse
        // (on n'a pas de profil utilisateur pour récupérer ces infos)
        if (!Auth::check()) {
            $rules['client_name']          = ['required', 'string', 'max:255', new RealisticFullName];
            $rules['client_phone']         = ['required', 'string', 'max:30', new RealisticGuineaPhone];
            $rules['delivery_destination'] = ['required', 'string', 'min:2', 'max:255'];
        }

        // Messages explicites pour les règles standard (required/min) : le projet
        // n'a pas de lang/fr/validation.php, donc sans ça un message générique
        // (voire une clé brute non traduite) pourrait s'afficher au client.
        $messages = [
            'client_name.required'          => "Merci d'indiquer votre nom complet.",
            'client_phone.required'         => 'Le numéro de téléphone est obligatoire.',
            'delivery_destination.required' => "L'adresse de livraison est obligatoire.",
            'delivery_destination.min'      => 'Merci de préciser une adresse plus complète.',
        ];

        // Validation des données du formulaire
        $request->validate($rules, $messages);

        // On charge le produit avec sa boutique en une seule requête (optimisation)
        // findOrFail() = cherche par ID, si non trouvé renvoie une erreur 404 automatiquement
        $product = Product::with('shop')->findOrFail($request->product_id);

        // Vérification de sécurité : la boutique doit exister et être approuvée
        abort_unless($product->shop && $product->shop->is_approved, 403);

        // Variante (taille/couleur) : optionnelle — le client peut commander "sans préférence"
        $variant = null;
        if ($product->has_variants && $request->filled('variant_id')) {
            $variant = $product->variants()->findOrFail($request->variant_id);

            if ($variant->stock < $request->quantity) {
                return back()->withErrors(['quantity' => "Stock insuffisant pour \"{$variant->name}\". Seulement {$variant->stock} disponible(s)."]);
            }
        } elseif (!$product->has_variants && $product->stock !== null && $product->stock < $request->quantity) {
            // Produit sans variantes : on vérifie le stock global
            return back()->withErrors(['quantity' => "Stock insuffisant. Seulement {$product->stock} disponible(s)."]);
        }

        // Prix unitaire : celui de la variante si sélectionnée, sinon celui du produit
        $unitPrice = $variant ? $variant->effective_price : $product->current_price;

        // On calcule le total : prix unitaire × quantité
        $total = $unitPrice * $request->quantity;

        // Code promo : appliqué en premier, sur le sous-total avant réduction fidélité
        $promo         = null;
        $promoDiscount = 0;
        if ($request->filled('promo_code')) {
            $result = app(PromoCodeService::class)->findValid($request->promo_code, $product->shop, $total);
            if (!$result['promo']) {
                return back()->withErrors(['promo_code' => $result['error']]);
            }
            $promo         = $result['promo'];
            $promoDiscount = $result['discount'];
            $total         = $total - $promoDiscount;
        }

        // Points fidélité : le client peut utiliser jusqu'à 50% du total (déjà réduit du code promo) en points (1 point = 1 GNF)
        $pointsToUse = 0;
        $user = Auth::user();
        if ($user) {
            $loyalty       = app(LoyaltyService::class);
            $maxRedeemable = $loyalty->maxRedeemableFor($user, $total);
            $pointsToUse   = max(0, min((int) $request->input('points_to_use', 0), $maxRedeemable));
            $total         = $total - $pointsToUse;
        }

        $order = Order::create([
            'user_id'              => Auth::id(),                                    // null si invité
            'client_name'          => Auth::check() ? null : $request->client_name,  // nom de l'invité (sinon on utilise le compte)
            'shop_id'              => $product->shop->id,
            'total'                => $total,
            'loyalty_points_used'  => $pointsToUse,
            'promo_code_id'        => $promo?->id,
            'discount_amount'      => $promoDiscount,
            'status'               => Order::STATUS_EN_ATTENTE,
            'delivery_destination' => $request->delivery_destination,
            'client_phone'         => $request->client_phone,
        ]);

        if ($pointsToUse > 0) {
            app(LoyaltyService::class)->redeemPoints($user, $pointsToUse, $order->id);
        }

        if ($promo) {
            app(PromoCodeService::class)->redeem($promo);
        }

        // On crée la ligne de commande (order item = détail du produit commandé)
        OrderItem::create([
            'order_id'           => $order->id,        // Lien avec la commande
            'product_id'         => $product->id,      // Quel produit
            'product_variant_id' => $variant?->id,
            'variant_name'       => $variant?->name,
            'quantity'           => $request->quantity,// Combien d'exemplaires
            'price'              => $unitPrice,         // Prix unitaire au moment de la commande
        ]);

        // Diminue le stock du nombre commandé — au niveau variante si elle existe, sinon au niveau produit
        // (si le produit gère des variantes mais qu'aucune n'a été choisie, on ne touche à aucun stock :
        // le champ stock du produit n'est pas pertinent dans ce cas)
        if ($variant) {
            $variant->decrement('stock', $request->quantity);
        } elseif (!$product->has_variants && $product->stock !== null) {
            $product->decrement('stock', $request->quantity);
        }

        // On crée le paiement associé à cette commande — cash par défaut, ou en
        // ligne (ChapChap Pay) si le client a choisi de payer tout de suite.
        // Paiement en ligne temporairement désactivé (réactivation prévue plus
        // tard, une fois ChapChap Pay + le reversement aux boutiques en place) :
        // on ignore payment_method et on reste toujours en cash pour l'instant,
        // même si un ancien lien/formulaire envoyait encore 'online'.
        $onlinePaymentEnabled = false;
        $wantsOnlinePayment = $onlinePaymentEnabled && $request->input('payment_method') === 'online';

        $payment = Payment::create([
            'order_id' => $order->id,
            'method'   => $wantsOnlinePayment ? Payment::METHOD_CHAPCHAPPAY : Payment::METHOD_CASH,
            'amount'   => $total,
            'status'   => 'en_attente',
        ]);

        // Si paiement en ligne demandé : on initie l'opération ChapChap Pay et on
        // redirige le client vers leur page de paiement hébergée. En cas d'échec
        // d'initiation, on ne bloque pas la commande — elle reste valable en cash.
        $onlinePaymentFailedMessage = null;
        if ($wantsOnlinePayment) {
            $result = app(ChapChapPayService::class)->createOperation(
                amountGnf:   $total,
                orderId:     'ORDER-' . $order->id . '-' . now()->timestamp,
                description: 'Commande #' . $order->id . ' — ' . ($product->shop->name ?? 'Shopio'),
                notifyUrl:   route('payment.order.callback'),
                returnUrl:   route('payment.order.success', ['order' => $order->id]),
                cancelUrl:   route('payment.order.failed', ['order' => $order->id]),
            );

            if ($result['success']) {
                $payment->update(['gateway_operation_id' => $result['operation_id']]);
                return redirect($result['payment_url']);
            }

            // Échec d'initiation : on repasse en cash pour ne pas perdre la commande,
            // mais on prévient le client au lieu de le laisser croire qu'il a payé en ligne.
            $payment->update(['method' => Payment::METHOD_CASH]);
            $onlinePaymentFailedMessage = "Le paiement en ligne n'a pas pu être initié"
                . (!empty($result['message']) ? ' (' . $result['message'] . ')' : '')
                . " — votre commande est enregistrée en cash à la livraison.";
            Log::warning('[OrderPayment] Initiation paiement en ligne échouée, commande conservée en cash', [
                'order_id' => $order->id,
                'message'  => $result['message'] ?? '',
            ]);
        }

        // Notifier le vendeur par push
        try {
            $shopOwner = $order->shop->user ?? $product->shop->user ?? null;
            if ($shopOwner) {
                $push = app(PushService::class);
                $push->sendToUser(
                    $shopOwner,
                    'Nouvelle commande !',
                    $product->name . ($variant ? ' (' . $variant->name . ')' : '') . ' × ' . $request->quantity . ' — ' . number_format($total, 0, ',', ' ') . ' GNF',
                    $push->vendorBadgeCount($shopOwner),
                    '/employe/orders'
                );
            }
        } catch (\Throwable $e) {}

        // Notifier aussi les employés/vendeurs de la boutique (pas seulement le propriétaire)
        try {
            $notifShop = $order->shop ?? $product->shop ?? null;
            if ($notifShop) {
                app(PushService::class)->notifyShopStaff(
                    $notifShop,
                    'Nouvelle commande !',
                    $product->name . ($variant ? ' (' . $variant->name . ')' : '') . ' × ' . $request->quantity . ' — ' . number_format($total, 0, ',', ' ') . ' GNF',
                    '/employe/orders',
                    $notifShop->user_id
                );
            }
        } catch (\Throwable $e) {}

        $successMsg = "Commande passée avec succès ! Vous recevrez une confirmation. 🎉";

        // Un invité n'a pas de compte pour voir "Mes commandes" → on l'envoie sur le suivi public de sa commande
        if (!Auth::check()) {
            $redirect = redirect()->route('suivi.show', $order)->with('success', $successMsg);
            return $onlinePaymentFailedMessage ? $redirect->with('warning', $onlinePaymentFailedMessage) : $redirect;
        }

        $redirect = redirect()->route('client.orders.index')->with('success', $successMsg);
        return $onlinePaymentFailedMessage ? $redirect->with('warning', $onlinePaymentFailedMessage) : $redirect;
    }

    // ============================================================
    // MÉTHODE : checkPromoCode()
    // ROUTE   : POST /client/orders/promo/check
    // RÔLE    : Vérifie un code promo en AJAX pour l'aperçu du total
    //           (la validation définitive a lieu à nouveau dans storeProduct())
    // ============================================================
    public function checkPromoCode(Request $request)
    {
        $request->validate([
            'code'     => 'required|string|max:30',
            'shop_id'  => 'required|exists:shops,id',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $shop   = Shop::findOrFail($request->shop_id);
        $result = app(PromoCodeService::class)->findValid($request->code, $shop, (float) $request->subtotal);

        if (!$result['promo']) {
            return response()->json(['valid' => false, 'message' => $result['error']]);
        }

        return response()->json(['valid' => true, 'discount' => $result['discount']]);
    }

    // ============================================================
    // MÉTHODE : downloadInvoice()
    // ROUTE   : GET /client/orders/{order}/invoice
    // RÔLE    : Génère et télécharge le reçu/facture PDF d'une commande
    // ============================================================
    public function downloadInvoice(Order $order)
    {
        // Seul le client propriétaire de la commande peut télécharger son reçu
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load(['items.product', 'items.variant', 'shop', 'payment', 'client']);

        $pdf = Pdf::loadView('client.orders.invoice', ['order' => $order])
            ->setPaper('a4', 'portrait');

        return $pdf->download('Recu-Shopio-Commande-' . $order->id . '.pdf');
    }
}
