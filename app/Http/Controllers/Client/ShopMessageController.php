<?php

// ============================================================
// FICHIER : app/Http/Controllers/Client/ShopMessageController.php
// RÔLE    : Gère toute la messagerie côté CLIENT.
//           - Afficher le chat entre client et vendeur
//           - Envoyer un message texte
//           - Proposer un prix au vendeur (négociation)
//           - Confirmer une offre du vendeur (crée une commande)
// ============================================================

// Namespace : indique à Laravel où se trouve ce fichier
namespace App\Http\Controllers\Client;

// Classe de base dont on hérite (contient validate, authorize...)
use App\Http\Controllers\Controller;

// Modèles (représentent les tables de la base de données)
use App\Models\Order;        // Table "orders" — les commandes
use App\Models\OrderItem;    // Table "order_items" — lignes de commande
use App\Models\Payment;      // Table "payments" — paiements
use App\Models\Product;      // Table "products" — produits
use App\Models\ShopMessage;  // Table "shop_messages" — messages

// Outils Laravel
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\ImageOptimizer;
use App\Jobs\ProcessImageJob;

// ============================================================
// Classe ShopMessageController
// ============================================================
class ShopMessageController extends Controller
{
    // ============================================================
    // MÉTHODE : index()
    // ROUTE   : GET /client/products/{product}/messages
    // RÔLE    : Affiche tous les messages entre le client et le vendeur
    //           pour un produit donné.
    //           Fonctionne en 2 modes :
    //           - Mode AJAX (JavaScript) : retourne du JSON
    //           - Mode normal (navigateur) : retourne une vue HTML
    // PARAMÈTRE : $product = le produit concerné par la conversation
    // ============================================================
    public function index(Product $product)
    {
        // On récupère la boutique liée à ce produit
        $shop   = $product->shop;

        // On récupère le client connecté
        $client = Auth::user();

        // Si la boutique n'existe pas, on arrête avec une erreur 404 (page non trouvée)
        abort_unless($shop, 404);

        // ── MODE AJAX ──
        // Si la requête vient de JavaScript (fetch, axios...) on retourne du JSON
        // ajax() = vrai si la requête a le header "X-Requested-With: XMLHttpRequest"
        // wantsJson() = vrai si la requête accepte du JSON (header "Accept: application/json")
        if (request()->ajax() || request()->wantsJson()) {

            // On charge les messages de cette conversation
            $messages = ShopMessage::where('shop_id', $shop->id)       // De cette boutique
                ->where('product_id', $product->id)                    // Pour ce produit
                ->where(function ($q) use ($client) {
                    // Messages envoyés par le client OU reçus par le client
                    $q->where('sender_id', $client->id)
                      ->orWhere('receiver_id', $client->id);
                })
                ->with(['sender:id,name'])  // On charge le nom de l'expéditeur
                ->orderBy('created_at')     // Du plus ancien au plus récent
                ->get()
                // ->map() = transforme chaque message en un tableau simplifié
                // C'est ce tableau qu'on envoie au JavaScript
                ->map(fn($m) => [
                    'id'                  => $m->id,
                    'body'                => $m->body,                        // Texte du message
                    'mine'                => $m->sender_id === $client->id,   // true si c'est le client qui a envoyé
                    'sender'              => $m->sender->name,                // Nom de l'expéditeur
                    'time'                => $m->created_at->format('H:i'),   // Heure (ex: "14:30")
                    'date'                => $m->created_at->diffForHumans(), // Durée relative (ex: "il y a 5 min")
                    'read'                => $m->read_at !== null,            // true si le message a été lu
                    'type'                => $m->type ?? 'text',
                    'proposed_price'      => $m->proposed_price,
                    'proposal_status'     => $m->proposal_status,
                    'negotiated_order_id' => $m->negotiated_order_id,
                    'images'              => ($m->image_status === 'ready' && $m->images)
                                                ? array_map(fn($p) => ImageOptimizer::url($p, 'large') ?? asset('storage/'.$p), $m->images)
                                                : [],
                    'image_status'        => $m->image_status ?? 'ready',
                ]);

            // On marque tous les messages reçus par ce client comme lus
            ShopMessage::where('shop_id', $shop->id)
                ->where('product_id', $product->id)
                ->where('receiver_id', $client->id)  // Destinataire = ce client
                ->whereNull('read_at')               // Pas encore lus
                ->update(['read_at' => now()]);       // On met la date/heure de lecture

            // On retourne les messages en JSON pour JavaScript
            return response()->json($messages);
        }

        // ── MODE NORMAL (affichage HTML) ──
        // Si ce n'est pas une requête AJAX, on charge les messages pour la vue Blade

        $messages = ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where(function ($q) use ($client) {
                $q->where('sender_id', $client->id)
                  ->orWhere('receiver_id', $client->id);
            })
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->get();

        // On marque aussi les messages comme lus pour le mode HTML
        ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where('receiver_id', $client->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // On retourne la vue HTML du chat
        // compact() = passe les variables $product, $shop, $messages à la vue
        return view('client.messages.show', compact('product', 'shop', 'messages'));
    }

    // ============================================================
    // MÉTHODE : store()
    // ROUTE   : POST /client/products/{product}/message
    // RÔLE    : Envoie un message TEXTE simple du client vers le vendeur
    // PARAMÈTRES :
    //   $request = les données du formulaire (le texte du message)
    //   $product = le produit concerné par la conversation
    // ============================================================
    public function store(Request $request, Product $product)
    {
        // Validation : le message doit exister, être du texte, max 1000 caractères
        $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        // On récupère la boutique du produit
        $shop = $product->shop;

        // La boutique doit exister ET être approuvée par l'admin
        abort_unless($shop && $shop->is_approved, 404);

        // On récupère le client connecté
        $client  = Auth::user();

        // On récupère le vendeur (propriétaire de la boutique)
        $vendeur = $shop->user;

        // Le vendeur doit exister
        abort_unless($vendeur, 404);

        // On crée le message en base de données
        ShopMessage::create([
            'shop_id'     => $shop->id,              // Boutique concernée
            'product_id'  => $product->id,           // Produit concerné
            'sender_id'   => $client->id,            // Qui envoie : le client
            'receiver_id' => $vendeur->id,           // Qui reçoit : le vendeur
            'body'        => $request->body,         // Le texte du message
            'type'        => ShopMessage::TYPE_TEXT, // Type = message texte simple
        ]);

        // Si c'est une requête AJAX (JavaScript), on retourne une confirmation JSON avec l'ID
        if (request()->ajax() || request()->wantsJson()) {
            $newMsg = ShopMessage::where('shop_id', $shop->id)
                ->where('sender_id', $client->id)
                ->latest()->first();
            return response()->json(['sent' => true, 'message_id' => $newMsg?->id]);
        }

        // Sinon, on redirige vers la page précédente avec un flag de succès
        return back()->with('chat_sent', true);
    }

    // ============================================================
    // MÉTHODE : proposePrice()
    // ROUTE   : POST /client/messages/propose-price
    // RÔLE    : Le client propose un prix inférieur pour un produit.
    //           Cela crée un message de type "price_proposal"
    //           que le vendeur verra dans son dashboard.
    // ============================================================
    public function proposePrice(Request $request)
    {
        // Validation des données
        $request->validate([
            'product_id'     => ['required', 'exists:products,id'], // L'ID du produit doit exister
            'proposed_price' => ['required', 'numeric', 'min:1'],   // Prix : nombre positif obligatoire
        ]);

        // On charge le produit avec sa boutique (en une seule requête)
        $product = Product::with('shop')->findOrFail($request->product_id);
        $shop    = $product->shop;

        // Vérifications de sécurité
        abort_unless($shop && $shop->is_approved, 403); // La boutique doit être approuvée

        $client  = Auth::user();
        $vendeur = $shop->user;

        abort_unless($vendeur, 403); // Le vendeur doit exister

        // On récupère la devise de la boutique (GNF par défaut si non définie)
        $devise = $shop->currency ?? 'GNF';

        // On convertit le prix en nombre décimal (float)
        $price  = (float) $request->proposed_price;

        // On crée le message de proposition de prix
        ShopMessage::create([
            'shop_id'         => $shop->id,
            'product_id'      => $product->id,
            'sender_id'       => $client->id,             // Le client envoie
            'receiver_id'     => $vendeur->id,            // Le vendeur reçoit
            // Texte du message affiché dans la conversation :
            'body'            => "💰 Je propose d'acheter **{$product->name}** à "
                                . number_format($price, 0, ',', ' ')         // Ex: "80 000"
                                . " {$devise} au lieu de "
                                . number_format($product->price, 0, ',', ' ')// Ex: "100 000"
                                . " {$devise}.",
            'type'            => ShopMessage::TYPE_PRICE_PROPOSAL, // Type spécial "proposition de prix"
            'proposed_price'  => $price,                           // Prix proposé stocké séparément
            'proposal_status' => ShopMessage::STATUS_PENDING,      // Statut : en attente de réponse
        ]);

        // On confirme à JavaScript que le message a été envoyé
        return response()->json(['sent' => true]);
    }

    // ============================================================
    // MÉTHODE : confirmOffer()
    // ROUTE   : POST /client/messages/confirm-offer/{message}
    // RÔLE    : Le client accepte l'offre de prix envoyée par le vendeur.
    //           Cette action crée automatiquement :
    //           1. Une commande (Order) au prix négocié
    //           2. Une ligne de commande (OrderItem)
    //           3. Un paiement (Payment)
    //           4. Un message de confirmation dans le chat
    //           IMPORTANT : le prix du produit en base n'est JAMAIS modifié.
    // PARAMÈTRES :
    //   $request = requête HTTP
    //   $message = le message d'offre du vendeur (injecté automatiquement par Laravel via l'ID dans l'URL)
    // ============================================================
    public function confirmOffer(Request $request, ShopMessage $message)
    {
        // On récupère le client connecté
        $client = Auth::user();

        // Vérifications de sécurité AVANT de créer quoi que ce soit :
        // 1. Le message doit être de type "price_offer" (offre du vendeur, pas une proposition client)
        // 2. Ce message doit être destiné à CE client (pas à quelqu'un d'autre)
        // 3. Le statut doit être "pending" (pas déjà accepté ou refusé)
        // Si l'une de ces conditions est fausse → erreur 403 (accès refusé)
        abort_unless(
            in_array($message->type, [ShopMessage::TYPE_PRICE_OFFER, ShopMessage::TYPE_COUNTER_OFFER]) &&
            $message->receiver_id === $client->id &&
            $message->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        // On charge le produit et sa boutique
        $product = Product::with('shop')->findOrFail($message->product_id);
        $shop    = $product->shop;

        // La boutique doit exister
        abort_unless($shop, 403);

        // On récupère le prix négocié depuis le message (pas depuis product.price !)
        $negotiatedPrice = (float) $message->proposed_price;

        // On récupère la devise de la boutique
        $devise = $shop->currency ?? 'GNF';

        // Variable pour stocker l'ID de la commande créée (partagée avec la closure)
        $orderId = null;

        // ── TRANSACTION SQL ──
        // DB::transaction() = groupe plusieurs opérations SQL en une seule "transaction"
        // Si l'une d'elles échoue, TOUTES sont annulées (rollback automatique)
        // Cela garantit qu'on ne se retrouve jamais avec une commande sans paiement, etc.
        DB::transaction(function () use ($message, $client, $product, $shop, $negotiatedPrice, $devise, &$orderId) {
            // "use (...)" = on passe les variables de l'extérieur dans cette fonction anonyme
            // "&$orderId" = on passe par référence (les modifications dans la closure affectent l'extérieur)

            // ── ÉTAPE 1 : Créer la commande ──
            // IMPORTANT : on utilise $negotiatedPrice, JAMAIS $product->price
            $order = Order::create([
                'user_id' => $client->id,               // Le client qui commande
                'shop_id' => $shop->id,                 // La boutique
                'total'   => $negotiatedPrice,          // Le prix négocié (pas le prix catalogue)
                'status'  => Order::STATUS_EN_ATTENTE,  // Statut initial
            ]);

            // ── ÉTAPE 2 : Créer la ligne de commande ──
            OrderItem::create([
                'order_id'   => $order->id,         // Lien avec la commande
                'product_id' => $product->id,       // Quel produit
                'quantity'   => 1,                  // 1 unité (la négociation porte sur 1 article)
                'price'      => $negotiatedPrice,   // Prix négocié stocké ici aussi
            ]);

            // ── ÉTAPE 3 : Décrémenter le stock si géré ──
            // Si $product->stock est null, le stock est illimité → on ne fait rien
            if ($product->stock !== null) {
                $product->decrement('stock', 1); // stock = stock - 1
            }

            // ── ÉTAPE 4 : Créer le paiement ──
            Payment::create([
                'order_id' => $order->id,         // Lien avec la commande
                'method'   => 'cash',             // Paiement en espèces
                'amount'   => $negotiatedPrice,   // Montant = prix négocié
                'status'   => 'en_attente',       // Pas encore payé
            ]);

            // ── ÉTAPE 5 : Marquer l'offre comme acceptée ──
            // On met à jour le message d'offre du vendeur
            $message->update([
                'proposal_status'     => ShopMessage::STATUS_ACCEPTED, // Offre acceptée
                'negotiated_order_id' => $order->id,                   // Lien avec la commande créée
            ]);

            // ── ÉTAPE 6 : Envoyer un message de confirmation dans le chat ──
            ShopMessage::create([
                'shop_id'             => $shop->id,
                'product_id'          => $product->id,
                'sender_id'           => $client->id,         // Le client envoie la confirmation
                'receiver_id'         => $message->sender_id, // Au vendeur (qui avait fait l'offre)
                // Texte du message de confirmation
                'body'                => "✅ Commande confirmée au prix négocié de "
                                        . number_format($negotiatedPrice, 0, ',', ' ')
                                        . " {$devise}. Commande n°{$order->id} créée avec succès !",
                'type'                => ShopMessage::TYPE_ORDER_CREATED, // Type spécial "commande créée"
                'proposed_price'      => $negotiatedPrice,
                'proposal_status'     => ShopMessage::STATUS_ACCEPTED,
                'negotiated_order_id' => $order->id,
            ]);

            // On sauvegarde l'ID pour pouvoir le retourner après la transaction
            $orderId = $order->id;
        });

        // On retourne une réponse JSON au JavaScript qui a fait la requête
        return response()->json([
            'success'  => true,
            'order_id' => $orderId,                      // L'ID de la commande créée
            'message'  => 'Commande créée avec succès !',
        ]);
    }

    // ============================================================
    // MÉTHODE PRIVÉE : getDevise()
    // RÔLE    : Retourne la devise de la boutique (GNF par défaut)
    // NOTE    : Cette méthode n'est pas utilisée directement (on extrait
    //           $devise avant les closures pour éviter des erreurs PHP)
    // ============================================================
    // ============================================================
    // MÉTHODE : poll()
    // ROUTE   : GET /client/messages/poll
    // RÔLE    : Retourne le nombre de messages non lus du client.
    //           Appelée toutes les 3 secondes par JavaScript
    //           pour mettre à jour le badge de notification.
    // ============================================================
    // ============================================================
    // MÉTHODE : hub()
    // ROUTE   : GET /client/messages
    // RÔLE    : Page principale de messagerie (style WhatsApp Web)
    //           Charge toutes les conversations du client.
    // ============================================================
    public function hub(Request $request)
    {
        $client = Auth::user();

        $allMsgs = ShopMessage::where(function ($q) use ($client) {
                $q->where('sender_id', $client->id)
                  ->orWhere('receiver_id', $client->id);
            })
            ->with(['shop', 'product:id,name,price,image', 'sender:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $conversations = $allMsgs
            ->groupBy(fn ($m) => ($m->shop_id ?? 0) . '_' . ($m->product_id ?? 0))
            ->map(function ($msgs) use ($client) {
                $last   = $msgs->first(); // desc order → first = most recent
                $unread = $msgs->filter(fn ($m) => is_null($m->read_at) && $m->receiver_id === $client->id)->count();
                return (object) [
                    'shop'      => $last->shop,
                    'product'   => $last->product,
                    'lastMsg'   => $last,
                    'unread'    => $unread,
                    'productId' => $last->product_id,
                ];
            })
            ->values();

        return view('client.messages.hub', compact('conversations'));
    }

    public function poll(Request $request)
    {
        $client = Auth::user();

        $unread = ShopMessage::where('receiver_id', $client->id)
            ->whereNull('read_at')
            ->count();

        $hasNew = ShopMessage::where('receiver_id', $client->id)
            ->where('created_at', '>=', now()->subSeconds(4))
            ->exists();

        return response()->json([
            'unread'  => $unread,
            'has_new' => $hasNew,
        ]);
    }

    // GET /client/notifications/poll — temps réel commandes + messages
    public function pollAll(Request $request)
    {
        $client = Auth::user();

        $messagesUnread = ShopMessage::where('receiver_id', $client->id)
            ->whereNull('read_at')
            ->count();

        $orders = \App\Models\Order::where('user_id', $client->id)
            ->with('shop:id,name,image')
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'status'       => $o->status,
                'updated_at'   => $o->updated_at->toIso8601String(),
                'shop_name'    => $o->shop?->name ?? 'Boutique',
                'total'        => $o->total,
            ]);

        $popularShops = \App\Models\Shop::where('is_approved', true)
            ->withCount(['orders as orders_count' => fn($q) => $q->where('status','livrée')])
            ->orderByDesc('orders_count')
            ->take(5)
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'orders_count'=> $s->orders_count,
                'image'       => $s->image ? asset('storage/'.$s->image) : null,
            ]);

        return response()->json([
            'messages_unread' => $messagesUnread,
            'orders'          => $orders,
            'popular_shops'   => $popularShops,
        ]);
    }

    // POST /client/messages/refuse-offer/{message} — client refuse une offre/contre-offre vendeur
    public function refuseOffer(Request $request, ShopMessage $message)
    {
        $client = Auth::user();
        abort_unless(
            in_array($message->type, [ShopMessage::TYPE_PRICE_OFFER, ShopMessage::TYPE_COUNTER_OFFER]) &&
            $message->receiver_id === $client->id &&
            $message->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        $message->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

        $product = Product::with('shop')->find($message->product_id);
        $devise  = $product?->shop?->currency ?? 'GNF';

        ShopMessage::create([
            'shop_id'     => $message->shop_id,
            'product_id'  => $message->product_id,
            'sender_id'   => $client->id,
            'receiver_id' => $message->sender_id,
            'body'        => "❌ Le client a refusé l'offre de "
                            . number_format($message->proposed_price, 0, ',', ' ')
                            . " {$devise}. Une nouvelle négociation est possible.",
            'type'        => ShopMessage::TYPE_TEXT,
        ]);

        return response()->json(['success' => true]);
    }

    // POST /client/messages/counter-offer — client contre-propose face à l'offre vendeur
    public function counterOffer(Request $request)
    {
        $request->validate([
            'message_id'    => ['required', 'exists:shop_messages,id'],
            'counter_price' => ['required', 'numeric', 'min:1'],
        ]);

        $client   = Auth::user();
        $original = ShopMessage::findOrFail($request->message_id);

        abort_unless(
            in_array($original->type, [ShopMessage::TYPE_PRICE_OFFER, ShopMessage::TYPE_COUNTER_OFFER]) &&
            $original->receiver_id === $client->id &&
            $original->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        $product = Product::with('shop')->find($original->product_id);
        $shop    = $product?->shop;
        $devise  = $shop?->currency ?? 'GNF';
        $vendeur = $shop?->user;
        abort_unless($vendeur, 403);

        $counterPrice = (float) $request->counter_price;

        // Marquer l'offre originale comme refusée (remplacée)
        $original->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

        // Créer la contre-offre du client
        ShopMessage::create([
            'shop_id'         => $original->shop_id,
            'product_id'      => $original->product_id,
            'sender_id'       => $client->id,
            'receiver_id'     => $vendeur->id,
            'body'            => "🔄 Contre-proposition du client : "
                                . number_format($counterPrice, 0, ',', ' ') . " {$devise}.",
            'type'            => ShopMessage::TYPE_COUNTER_OFFER,
            'proposed_price'  => $counterPrice,
            'proposal_status' => ShopMessage::STATUS_PENDING,
        ]);

        return response()->json(['success' => true]);
    }

    // POST /client/messages/images/{product?} — client envoie des photos au vendeur
    public function sendImages(Request $request, ?Product $product = null)
    {
        abort_unless($request->isXmlHttpRequest(), 403);

        $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:10240'],
        ]);

        $client = Auth::user();

        // Résoudre le produit depuis le form si non passé en route
        if (!$product && $request->filled('product_id')) {
            $product = Product::find($request->product_id);
        }

        $shop = $product?->shop;
        abort_unless($shop && $shop->is_approved, 404);

        $vendeur = $shop->user;
        abort_unless($vendeur, 404);

        $tempPaths = [];
        foreach ($request->file('images') as $file) {
            $tempName  = 'temp/' . Str::random(24) . '.' . $file->getClientOriginalExtension();
            Storage::disk('local')->put($tempName, file_get_contents($file->getRealPath()));
            $tempPaths[] = $tempName;
        }

        $count = count($tempPaths);

        $msg = ShopMessage::create([
            'shop_id'      => $shop->id,
            'product_id'   => $product?->id,
            'sender_id'    => $client->id,
            'receiver_id'  => $vendeur->id,
            'body'         => $count . ' photo(s)',
            'images'       => [],
            'image_status' => 'processing',
            'type'         => ShopMessage::TYPE_IMAGES,
        ]);

        ProcessImageJob::dispatch($msg->id, $tempPaths, 'messages/' . $shop->id);

        return response()->json([
            'success'      => true,
            'sent'         => true,
            'message_id'   => $msg->id,
            'images'       => [],
            'image_status' => 'processing',
            'count'        => $count,
        ]);
    }

    // GET /client/messages/image-status/{message} — vérifie si les images du client sont prêtes
    public function imageStatus(ShopMessage $message)
    {
        $client = Auth::user();
        abort_unless($message->sender_id === $client->id, 403);

        if ($message->image_status !== 'ready' || empty($message->images)) {
            return response()->json([
                'status' => $message->image_status,
                'images' => [],
            ]);
        }

        $urls = array_map(
            fn($p) => ImageOptimizer::url($p, 'large') ?? asset('storage/' . $p),
            $message->images
        );

        return response()->json([
            'status' => 'ready',
            'images' => $urls,
        ]);
    }

    private function getDevise($shop): string
    {
        return $shop->currency ?? 'GNF';
    }
}
