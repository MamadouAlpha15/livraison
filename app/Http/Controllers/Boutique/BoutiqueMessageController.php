<?php

// ============================================================
// FICHIER : app/Http/Controllers/Boutique/BoutiqueMessageController.php
// RÔLE    : Gère toute la messagerie côté VENDEUR (boutique).
//           - Répondre à un client (message texte)
//           - Envoyer une offre de prix au client (négociation)
//           - Refuser la proposition de prix d'un client
//           - Vérifier s'il y a de nouveaux messages (polling)
//           - Marquer des messages comme lus
// ============================================================

// Namespace : indique à Laravel où se trouve ce fichier
namespace App\Http\Controllers\Boutique;

// Classe de base dont on hérite
use App\Http\Controllers\Controller;

// Modèles nécessaires
use App\Models\ShopMessage;      // Messages entre clients et vendeurs
use App\Models\DeliveryMessage;  // Messages boutique ↔ entreprise de livraison
use App\Models\User;             // Utilisateurs (clients, vendeurs...)
use App\Models\Product;          // Produits de la boutique
use App\Services\ImageOptimizer;
use App\Jobs\ProcessImageJob;
use App\Services\PushService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Outils Laravel
use Illuminate\Http\Request;           // Données envoyées par le formulaire/AJAX
use Illuminate\Support\Facades\Auth;  // Utilisateur connecté
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

// ============================================================
// Classe BoutiqueMessageController
// ============================================================
class BoutiqueMessageController extends Controller
{
    // ============================================================
    // MÉTHODE : reply()
    // ROUTE   : POST /boutique/messages/reply/{client}/{product?}
    // RÔLE    : Le vendeur envoie un message TEXTE normal à un client.
    //           (pas une offre de prix — juste une réponse normale)
    // PARAMÈTRES :
    //   $request = données du formulaire
    //   $client  = l'ID du client dans l'URL (pas injecté automatiquement)
    //   $product = l'ID du produit dans l'URL (optionnel, peut être null)
    // ============================================================
    // ============================================================
    // MÉTHODE : hub()
    // ROUTE   : GET /boutique/messages
    // RÔLE    : Page principale de messagerie vendeur (style WhatsApp Web)
    // ============================================================
    public function hub(Request $request)
    {
        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        abort_unless($shop, 403);

        $allMsgs = ShopMessage::where('shop_id', $shop->id)
            ->with(['sender:id,name,role', 'receiver:id,name,role', 'product:id,name,price,image,description,gallery,stock'])
            ->orderBy('created_at', 'desc')
            ->get();

        $conversations = $allMsgs
            ->groupBy(function ($m) {
                $clientId = ($m->sender->role ?? '') === 'client' ? $m->sender_id : $m->receiver_id;
                return $clientId . '_' . ($m->product_id ?? 0);
            })
            ->map(function ($msgs) use ($vendeur) {
                $last     = $msgs->first();
                $clientId = ($last->sender->role ?? '') === 'client' ? $last->sender_id : $last->receiver_id;
                $client   = ($last->sender->role ?? '') === 'client' ? $last->sender  : $last->receiver;
                $unread   = $msgs->filter(fn ($m) => is_null($m->read_at) && $m->receiver_id === $vendeur->id)->count();
                return (object) [
                    'client'    => $client,
                    'product'   => $last->product,
                    'lastMsg'   => $last,
                    'unread'    => $unread,
                    'clientId'  => $clientId,
                    'productId' => $last->product_id,
                ];
            })
            ->values();

        return view('boutique.messages.hub', compact('conversations', 'shop'));
    }

    public function reply(Request $request, $client, $product = null)
    {
        // Validation des données envoyées par le formulaire
        $request->validate([
            'body'       => ['required', 'string', 'max:1000'], // Le message est obligatoire, max 1000 caractères
            'client_id'  => ['required', 'exists:users,id'],    // L'ID du client doit exister dans la table users
            'product_id' => ['nullable', 'integer'], // Simple référence historique : le produit a pu être supprimé depuis
        ]);

        // On récupère le vendeur connecté
        $vendeur = Auth::user();

        // On récupère la boutique du vendeur
        // $vendeur->shop = la boutique dont le vendeur est propriétaire (relation directe)
        // $vendeur->assignedShop = si le vendeur est un employé, la boutique à laquelle il est assigné
        // "??" = si le premier est null, on prend le second
        $shop = $vendeur->shop ?? $vendeur->assignedShop;

        // Si le vendeur n'a pas de boutique → erreur 403 (accès refusé)
        abort_unless($shop, 403);

        // On détermine l'ID du produit concerné par la conversation
        // On privilégie product_id du formulaire, sinon le paramètre $product de l'URL
        // Si les deux sont null ou 0, on met null (pas de produit lié)
        $productId = $request->product_id ?: ($product && $product != 0 ? $product : null);

        // L'ID du client destinataire du message
        $clientId = $request->client_id;

        // On crée le message du vendeur vers le client
        ShopMessage::create([
            'shop_id'     => $shop->id,              // La boutique du vendeur
            'product_id'  => $productId,             // Le produit concerné (peut être null)
            'sender_id'   => $vendeur->id,           // Qui envoie : le vendeur
            'receiver_id' => $clientId,              // Qui reçoit : le client
            'body'        => $request->body,         // Le texte du message
            'type'        => ShopMessage::TYPE_TEXT, // Type = message texte simple
            'read_at'     => null,                   // Le client n'a pas encore lu ce message
        ]);

        // On marque les messages du CLIENT vers ce VENDEUR comme lus
        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $clientId)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Notifier le client par push
        try {
            $clientUser = User::find($clientId);
            if ($clientUser) {
                app(PushService::class)->sendToUser(
                    $clientUser,
                    'Nouveau message de ' . $shop->name,
                    $request->body,
                    1,
                    '/client/messages'
                );
            }
        } catch (\Throwable $e) {}

        // Si c'est une requête AJAX → retourner JSON avec l'ID du message créé
        if ($request->ajax() || $request->wantsJson()) {
            $newMessage = ShopMessage::where('shop_id', $shop->id)
                ->where('sender_id', $vendeur->id)
                ->where('receiver_id', $clientId)
                ->latest()
                ->first();
            return response()->json(['success' => true, 'sent' => true, 'message_id' => $newMessage?->id]);
        }

        // Sinon → redirection classique (fallback)
        return back()->with('success', 'Réponse envoyée.');
    }

    // ============================================================
    // MÉTHODE : createPriceOffer()
    // ROUTE   : POST /boutique/messages/price-offer
    // RÔLE    : Le vendeur envoie une OFFRE DE PRIX au client.
    //           C'est différent d'un message texte : le client verra
    //           un bouton "Confirmer" pour créer une commande.
    //           Optionnellement, si le client avait fait une proposition,
    //           on peut la marquer comme acceptée en même temps.
    // ============================================================
    public function createPriceOffer(Request $request)
    {
        // Validation des données
        $request->validate([
            'client_id'          => ['required', 'exists:users,id'],         // ID du client destinataire
            'product_id'         => ['required', 'exists:products,id'],      // ID du produit
            'offered_price'      => ['required', 'numeric', 'min:1'],        // Prix proposé (nombre > 0)
            'proposal_message_id'=> ['nullable', 'exists:shop_messages,id'], // ID de la proposition client (optionnel)
        ]);

        // On récupère le vendeur connecté et sa boutique
        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;

        // Sécurité : le vendeur doit avoir une boutique
        abort_unless($shop, 403);

        // On charge le produit concerné
        $product = Product::findOrFail($request->product_id);

        // On convertit le prix proposé en décimal (float)
        $offeredPrice = (float) $request->offered_price;

        // ── Traitement optionnel de la proposition client ──
        // Si le vendeur répond à une proposition spécifique du client
        // (le formulaire inclut l'ID du message de proposition)
        if ($request->filled('proposal_message_id')) {
            // On met à jour le statut de la proposition du client → "acceptée"
            // On vérifie que ce message appartient bien à cette boutique (sécurité)
            // et que c'est bien une proposition en attente (pas déjà traitée)
            ShopMessage::where('id', $request->proposal_message_id)
                ->where('shop_id', $shop->id)                                 // Appartient à cette boutique
                ->where('type', ShopMessage::TYPE_PRICE_PROPOSAL)             // C'est une proposition client
                ->where('proposal_status', ShopMessage::STATUS_PENDING)       // Toujours en attente
                ->update(['proposal_status' => ShopMessage::STATUS_ACCEPTED]); // On la marque acceptée
        }

        // On récupère la devise de la boutique (GNF par défaut)
        $devise = $shop->currency ?? 'GNF';

        // On crée le message d'offre de prix (type spécial que le client peut confirmer)
        ShopMessage::create([
            'shop_id'         => $shop->id,
            'product_id'      => $request->product_id,
            'sender_id'       => $vendeur->id,             // Le vendeur envoie
            'receiver_id'     => $request->client_id,     // Le client reçoit
            // Texte du message visible dans la conversation
            'body'            => "🏷️ Je vous propose **{$product->name}** au prix négocié de "
                                . number_format($offeredPrice, 0, ',', ' ')
                                . " {$devise}. Cliquez sur « Confirmer » pour valider votre commande.",
            'type'            => ShopMessage::TYPE_PRICE_OFFER,  // Type "offre de prix" (bouton Confirmer côté client)
            'proposed_price'  => $offeredPrice,                  // Prix de l'offre stocké séparément
            'proposal_status' => ShopMessage::STATUS_PENDING,    // En attente de confirmation du client
        ]);

        // Notifier le client par push
        try {
            $clientUser = User::find($request->client_id);
            if ($clientUser) {
                app(PushService::class)->sendToUser(
                    $clientUser,
                    'Offre de prix de ' . $shop->name,
                    "🏷️ {$product->name} vous est proposé à " . number_format($offeredPrice, 0, ',', ' ') . " {$devise}. Cliquez pour confirmer.",
                    1,
                    '/client/messages'
                );
            }
        } catch (\Throwable $e) {}

        // On marque les messages du client vers ce vendeur comme lus
        // (le vendeur a lu les messages puisqu'il répond)
        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $request->client_id)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // On retourne une réponse JSON de succès au JavaScript
        return response()->json(['success' => true, 'message' => 'Offre envoyée au client.']);
    }

    // ============================================================
    // MÉTHODE : refuseProposal()
    // ROUTE   : POST /boutique/messages/refuse-proposal/{message}
    // RÔLE    : Le vendeur refuse la proposition de prix d'un client.
    //           Cela met à jour le statut de la proposition et envoie
    //           automatiquement un message de refus au client.
    // PARAMÈTRES :
    //   $request = requête HTTP
    //   $message = le message de proposition du client (injecté automatiquement par Laravel)
    // ============================================================
    public function refuseProposal(Request $request, ShopMessage $message)
    {
        // On récupère le vendeur connecté et sa boutique
        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;

        // Vérifications de sécurité (double protection) :
        // 1. Le vendeur doit avoir une boutique ET ce message doit appartenir à CETTE boutique
        //    (évite qu'un vendeur refuse des propositions d'une autre boutique)
        abort_unless($shop && $message->shop_id === $shop->id, 403);

        // 2. Le message doit être une proposition de prix ET être en attente
        //    (on ne peut pas refuser ce qui est déjà accepté ou refusé)
        abort_unless(
            $message->type === ShopMessage::TYPE_PRICE_PROPOSAL &&
            $message->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        // On marque la proposition comme "refusée"
        $message->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

        // On récupère la devise pour l'afficher dans le message de refus
        $devise = $shop->currency ?? 'GNF';

        // On envoie un message automatique au client pour l'informer du refus
        ShopMessage::create([
            'shop_id'     => $shop->id,
            'product_id'  => $message->product_id,    // Même produit que la proposition
            'sender_id'   => $vendeur->id,            // Le vendeur envoie le refus
            'receiver_id' => $message->sender_id,     // Au client qui avait proposé
            // Texte du message de refus
            'body'        => "❌ Votre proposition de "
                            . number_format($message->proposed_price, 0, ',', ' ')
                            . " {$devise} n'a pas été acceptée. N'hésitez pas à faire une nouvelle proposition.",
            'type'        => ShopMessage::TYPE_TEXT,  // Message texte simple (pas une offre)
        ]);

        // On marque les messages du client comme lus
        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $message->sender_id) // Messages du client
            ->where('receiver_id', $vendeur->id)      // Destinés au vendeur
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // POST /boutique/messages/counter-proposal — vendeur contre-propose un prix
    public function counterProposal(Request $request)
    {
        $request->validate([
            'message_id'     => ['required', 'exists:shop_messages,id'],
            'counter_price'  => ['required', 'numeric', 'min:1'],
            'message'        => ['nullable', 'string', 'max:500'], // Message libre du vendeur (optionnel)
        ]);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        abort_unless($shop, 403);

        $original = ShopMessage::findOrFail($request->message_id);
        abort_unless(
            $original->shop_id === $shop->id &&
            in_array($original->type, [ShopMessage::TYPE_PRICE_PROPOSAL, ShopMessage::TYPE_COUNTER_OFFER]) &&
            $original->proposal_status === ShopMessage::STATUS_PENDING,
            403
        );

        $devise       = $shop->currency ?? 'GNF';
        $counterPrice = (float) $request->counter_price;
        $customMessage = trim((string) $request->input('message', ''));

        // Marquer la proposition originale comme "refusée" (remplacée par la contre-offre)
        $original->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

        $autoText = "🔄 Contre-proposition du vendeur : "
                  . number_format($counterPrice, 0, ',', ' ') . " {$devise}.";

        // Créer la contre-offre du vendeur
        ShopMessage::create([
            'shop_id'         => $shop->id,
            'product_id'      => $original->product_id,
            'sender_id'       => $vendeur->id,
            'receiver_id'     => $original->sender_id,
            'body'            => $customMessage !== '' ? ($customMessage . "\n\n" . $autoText) : $autoText,
            'note'            => $customMessage !== '' ? $customMessage : null, // Message libre du vendeur, affiché à part dans la carte
            'type'            => ShopMessage::TYPE_COUNTER_OFFER,
            'proposed_price'  => $counterPrice,
            'proposal_status' => ShopMessage::STATUS_PENDING,
        ]);

        // Notifier le client par push (avec un aperçu du message libre du vendeur, s'il y en a un)
        try {
            $clientUser = User::find($original->sender_id);
            if ($clientUser) {
                $product  = $original->product_id ? Product::find($original->product_id) : null;
                $pushBody = $shop->name . ' vous propose ' . number_format($counterPrice, 0, ',', ' ') . " {$devise}"
                          . ($product ? " pour {$product->name}." : '.');
                if ($customMessage !== '') {
                    $pushBody .= ' « ' . Str::limit($customMessage, 60) . ' »';
                }
                app(PushService::class)->sendToUser(
                    $clientUser,
                    'Contre-offre de ' . $shop->name . ' 🔄',
                    $pushBody,
                    1,
                    '/client/messages'
                );
            }
        } catch (\Throwable $e) {}

        return response()->json(['success' => true]);
    }

    // ============================================================
    // MÉTHODE : poll()
    // ROUTE   : GET /boutique/messages/poll
    // RÔLE    : Vérification périodique (polling) pour savoir s'il y a
    //           de nouveaux messages non lus.
    //           Le JavaScript appelle cette route toutes les X secondes.
    //           Retourne le nombre de messages non lus + si on doit recharger.
    // ============================================================
    public function poll(Request $request)
    {
        // Sécurité : cette route ne doit être appelée que par JavaScript (AJAX)
        // Si ce n'est pas une requête AJAX → erreur 403
        if (!$request->ajax()) abort(403);

        // On récupère le vendeur connecté et sa boutique
        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;

        // Si le vendeur n'a pas de boutique, on retourne 0 non lus et pas de rechargement
        if (!$shop) {
            return response()->json(['unread' => 0, 'reload' => false]);
        }

        // On compte les messages non lus de cette boutique
        // whereHas('sender', ...) = on filtre les messages dont l'expéditeur a le rôle 'client'
        // (on ne compte pas les messages du vendeur à lui-même)
        $unread = ShopMessage::where('shop_id', $shop->id)
            ->whereNull('read_at')                                              // Non lus
            ->whereHas('sender', fn($q) => $q->where('role', 'client'))        // Envoyés par des clients
            ->count();                                                          // On compte

        // On vérifie s'il y a eu de nouveaux messages dans les 12 dernières secondes
        // subSeconds(12) = il y a 12 secondes
        // exists() = retourne true/false (plus efficace que count() > 0)
        $hasNew = ShopMessage::where('shop_id', $shop->id)
            ->where('created_at', '>=', now()->subSeconds(12))                 // Créés récemment
            ->whereHas('sender', fn($q) => $q->where('role', 'client'))        // Par des clients
            ->exists();

        // On retourne les données au JavaScript
        return response()->json([
            'unread' => $unread,   // Nombre total de messages non lus
            'reload' => $hasNew,   // true = JavaScript doit recharger la liste des conversations
        ]);
    }

    // ============================================================
    // MÉTHODE : markAsRead()
    // ROUTE   : POST /boutique/messages/read
    // RÔLE    : Marque les messages d'un client comme lus.
    //           Appelée automatiquement quand le vendeur ouvre
    //           une conversation dans le modal.
    // ============================================================
    public function markAsRead(Request $request)
    {
        // Validation : l'ID du client est obligatoire, le produit est optionnel
        $request->validate([
            'client_id'  => ['required', 'exists:users,id'],
            'product_id' => ['nullable', 'integer'],
        ]);

        // On récupère le vendeur connecté et sa boutique
        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;

        // Le vendeur doit avoir une boutique
        abort_unless($shop, 403);

        // On prépare la requête de base (sans filtre produit pour l'instant)
        $query = ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $request->client_id)   // Messages envoyés par ce client
            ->where('receiver_id', $vendeur->id)        // Destinés à ce vendeur
            ->whereNull('read_at');                     // Pas encore lus

        // Si un product_id est fourni, on filtre aussi par produit
        // filled() = vérifie que la valeur est présente ET non vide
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // On exécute la mise à jour et on récupère le nombre de lignes modifiées
        $updated = $query->update(['read_at' => now()]);

        // On retourne le résultat au JavaScript
        return response()->json([
            'success' => true,
            'marked'  => $updated,
        ]);
    }

    // ============================================================
    // MÉTHODE : getConversation()
    // ROUTE   : GET /boutique/messages/conversation?client_id=X&product_id=Y
    // RÔLE    : Retourne tous les messages d'une conversation en JSON.
    //           Appelée par le polling JavaScript toutes les 3 secondes
    //           pour mettre à jour le thread sans recharger la page.
    // ============================================================
    public function getConversation(Request $request)
    {
        $request->validate([
            'client_id'  => ['required', 'exists:users,id'],
            'product_id' => ['nullable', 'integer'],
        ]);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        abort_unless($shop, 403);

        $query = ShopMessage::where('shop_id', $shop->id)
            ->where(function ($q) use ($request, $vendeur) {
                $q->where('sender_id', $request->client_id)
                  ->orWhere('receiver_id', $request->client_id);
            })
            ->with(['sender:id,name'])
            ->orderBy('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $messages = $query->get()->map(fn($m) => [
            'id'                  => $m->id,
            'body'                => $m->body,
            'note'                => $m->note,
            'mine'                => $m->sender_id === $vendeur->id,
            'sender'              => $m->sender->name ?? 'Inconnu',
            'time'                => $m->created_at->format('H:i'),
            'dateKey'             => $m->created_at->toDateString(),
            'date'                => $m->created_at->isToday() ? "Aujourd'hui" : ($m->created_at->isYesterday() ? 'Hier' : $m->created_at->format('d/m/Y')),
            'read'                => !is_null($m->read_at),
            'type'                => $m->type ?? 'text',
            'proposed_price'      => $m->proposed_price ? (float)$m->proposed_price : null,
            'proposal_status'     => $m->proposal_status,
            'negotiated_order_id' => $m->negotiated_order_id,
            'images'              => ($m->image_status === 'ready' && $m->images)
                                        ? array_map(fn($p) => ImageOptimizer::url($p, 'medium') ?? asset('storage/'.$p), $m->images)
                                        : [],
            'image_status'        => $m->image_status ?? 'ready',
        ])->values();

        // Marquer les messages reçus comme lus automatiquement
        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $request->client_id)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }

    // POST /boutique/messages/images/{client}/{product?} — vendeur envoie des photos
    public function sendImages(Request $request, User $client, ?Product $product = null)
    {
        abort_unless($request->isXmlHttpRequest(), 403);

        $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:20'],
            'images.*' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:10240'],
        ]);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        abort_unless($shop, 403);

        $paths = [];
        foreach ($request->file('images') as $file) {
            try {
                $paths[] = ImageOptimizer::store($file, 'messages/' . $shop->id);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('sendImages optimize failed: ' . $e->getMessage());
            }
        }

        $count = count($request->file('images'));

        $msg = ShopMessage::create([
            'shop_id'      => $shop->id,
            'product_id'   => $product?->id,
            'sender_id'    => $vendeur->id,
            'receiver_id'  => $client->id,
            'body'         => $count . ' photo(s)',
            'images'       => $paths,
            'image_status' => count($paths) > 0 ? 'ready' : 'failed',
            'type'         => ShopMessage::TYPE_IMAGES,
        ]);

        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $client->id)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Push au client
        try {
            app(PushService::class)->sendToUser(
                $client,
                'Photo de ' . $shop->name,
                '📷 ' . $count . ' photo(s) reçue(s) — cliquez pour voir',
                1,
                '/client/messages'
            );
        } catch (\Throwable $e) {}

        $urls = array_map(
            fn($p) => ImageOptimizer::url($p, 'medium') ?? asset('storage/' . $p),
            $paths
        );

        return response()->json([
            'success'      => true,
            'sent'         => true,
            'message_id'   => $msg->id,
            'images'       => $urls,
            'image_status' => $msg->image_status,
            'count'        => count($paths),
        ]);
    }

    // GET /boutique/messages/image-status/{message} — vérifie si les images sont prêtes
    public function imageStatus(ShopMessage $message)
    {
        if ($message->image_status !== 'ready' || empty($message->images)) {
            return response()->json([
                'status' => $message->image_status,
                'images' => [],
            ]);
        }

        $urls = array_map(
            fn($p) => ImageOptimizer::url($p, 'medium') ?? asset('storage/' . $p),
            $message->images
        );

        return response()->json([
            'status' => 'ready',
            'images' => $urls,
        ]);
    }

    // ============================================================
    // MÉTHODE : suggestReply()
    // ROUTE   : POST /boutique/messages/ai-suggest-reply
    // RÔLE    : Shopio IA lit les derniers messages de la conversation
    //           et suggère une réponse au vendeur (éditable avant envoi).
    //           Réservé au plan Pro, comme le reste de Shopio IA.
    // ============================================================
    public function suggestReply(Request $request)
    {
        $request->validate([
            'client_id'  => ['required', 'exists:users,id'],
            'product_id' => ['nullable', 'integer'],
        ]);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        abort_unless($shop, 403);

        $isPro = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
        if (!$isPro) {
            return response()->json(['error' => '⭐ Shopio IA est réservé au plan Pro. Passez au plan Pro pour débloquer cette fonctionnalité.'], 403);
        }

        // Max 20 suggestions par vendeur par heure (même quota que la génération de description)
        $key = 'ai-reply:' . $vendeur->id;
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['error' => 'Limite atteinte. Réessayez dans une heure.'], 429);
        }
        RateLimiter::hit($key, 3600);

        // On récupère les derniers messages de la conversation pour donner le contexte à l'IA
        $query = ShopMessage::where('shop_id', $shop->id)
            ->where(function ($q) use ($request) {
                $q->where('sender_id', $request->client_id)
                  ->orWhere('receiver_id', $request->client_id);
            })
            ->orderByDesc('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $recentMessages = $query->limit(8)->get()->reverse()->values();

        if ($recentMessages->isEmpty()) {
            return response()->json(['error' => 'Aucun message dans cette conversation pour le moment.'], 422);
        }

        $product = $request->filled('product_id') ? Product::find($request->product_id) : null;
        $devise  = $shop->currency ?? 'GNF';

        // On construit une transcription lisible de la conversation pour l'IA
        $transcript = $recentMessages->map(function ($m) use ($vendeur, $devise) {
            $who  = $m->sender_id === $vendeur->id ? 'Vendeur' : 'Client';
            $text = $m->body;
            if (in_array($m->type, [ShopMessage::TYPE_PRICE_PROPOSAL, ShopMessage::TYPE_COUNTER_OFFER, ShopMessage::TYPE_PRICE_OFFER]) && $m->proposed_price) {
                $text .= ' [Prix proposé : ' . number_format($m->proposed_price, 0, ',', ' ') . " {$devise}]";
            }
            return "{$who} : {$text}";
        })->implode("\n");

        $prompt = "Tu es Shopio IA, un assistant qui aide un vendeur d'une marketplace africaine à répondre à ses clients par chat. "
            . "Voici la conversation récente entre le vendeur et un client"
            . ($product ? " au sujet du produit \"{$product->name}\"" : '') . " :\n\n"
            . $transcript . "\n\n"
            . "Rédige UNE réponse courte (1 à 3 phrases), polie, commerciale et chaleureuse que le VENDEUR peut envoyer maintenant "
            . "pour répondre au dernier message du client. Adapte le ton au contexte (négociation de prix, question, réclamation…). "
            . "Ne mets pas de guillemets ni de préfixe comme « Réponse : ». Réponds UNIQUEMENT avec le texte du message à envoyer.";

        try {
            $response = Http::withOptions(['verify' => app()->isProduction()])
                ->timeout(30)
                ->withHeaders([
                    'x-api-key'         => config('services.anthropic.key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'       => 'claude-haiku-4-5-20251001',
                    'max_tokens'  => 200,
                    'temperature' => 0.8,
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (!$response->successful()) {
                \Log::error('Anthropic API error (suggestReply): ' . $response->body());
                $errMsg = $response->json('error.message') ?? 'Erreur API Claude.';
                return response()->json(['error' => app()->isLocal() ? $errMsg : 'Erreur Shopio IA. Réessayez.'], 500);
            }

            $suggestion = trim($response->json('content.0.text') ?? '');

            if (!$suggestion) {
                return response()->json(['error' => 'Réponse vide. Réessayez.'], 500);
            }

            return response()->json(['suggestion' => $suggestion]);

        } catch (\Exception $e) {
            \Log::error('Shopio IA suggestReply error: ' . $e->getMessage());
            $msg = app()->isLocal() ? $e->getMessage() : 'Erreur Shopio IA. Réessayez.';
            return response()->json(['error' => $msg], 500);
        }
    }

    // ============================================================
    // MÉTHODE : suggestCounterOffer()
    // ROUTE   : POST /boutique/messages/ai-suggest-counter
    // RÔLE    : Shopio IA lit la proposition/contre-offre du client et
    //           suggère au vendeur À LA FOIS un prix de contre-offre ET
    //           un message à envoyer avec, en un seul clic.
    //           Réservé au plan Pro, comme le reste de Shopio IA.
    // ============================================================
    public function suggestCounterOffer(Request $request)
    {
        $request->validate([
            'message_id' => ['required', 'integer', 'exists:shop_messages,id'],
        ]);

        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        abort_unless($shop, 403);

        $isPro = $shop->plan === 'pro' && $shop->plan_expires_at?->isFuture();
        if (!$isPro) {
            return response()->json(['error' => '⭐ Shopio IA est réservé au plan Pro. Passez au plan Pro pour débloquer cette fonctionnalité.'], 403);
        }

        // Même quota que les autres suggestions Shopio IA (20 par vendeur par heure)
        $key = 'ai-reply:' . $vendeur->id;
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['error' => 'Limite atteinte. Réessayez dans une heure.'], 429);
        }
        RateLimiter::hit($key, 3600);

        $original = ShopMessage::where('shop_id', $shop->id)->findOrFail($request->message_id);

        if (!in_array($original->type, [ShopMessage::TYPE_PRICE_PROPOSAL, ShopMessage::TYPE_COUNTER_OFFER]) || !$original->proposed_price) {
            return response()->json(['error' => "Ce message n'est pas une proposition de prix."], 422);
        }

        $product = $original->product_id ? Product::find($original->product_id) : null;
        $devise  = $shop->currency ?? 'GNF';
        $clientPrice = (float) $original->proposed_price;

        if ($product) {
            $contexte = "Produit : \"{$product->name}\", prix normal affiché : " . number_format((float) $product->current_price, 0, ',', ' ') . " {$devise}.\n"
                . "Le client vient de proposer : " . number_format($clientPrice, 0, ',', ' ') . " {$devise}.\n\n"
                . "Propose une contre-offre raisonnable pour le vendeur (un prix entre la proposition du client et le prix normal, "
                . "en général plus proche du prix normal pour rester rentable).";
        } else {
            // Pas de produit rattaché (ex : produit supprimé depuis) — l'IA doit quand même répondre,
            // sans le prix normal on se base uniquement sur une majoration raisonnable de l'offre du client.
            $contexte = "Le client vient de proposer : " . number_format($clientPrice, 0, ',', ' ') . " {$devise} pour un produit de la boutique.\n\n"
                . "Tu ne connais pas le prix normal du produit. Propose quand même une contre-offre raisonnable, "
                . "un peu au-dessus de la proposition du client (par exemple +10 à +20%).";
        }

        $prompt = "Tu es Shopio IA, un assistant qui aide un vendeur d'une marketplace africaine à négocier un prix avec un client par chat.\n\n"
            . $contexte . "\n\n"
            . "Rédige aussi un court message poli et commercial (1 à 2 phrases) à envoyer avec cette contre-offre.\n\n"
            . "Tu dois TOUJOURS répondre avec une contre-offre concrète, même si des informations manquent — ne pose jamais de question, "
            . "ne demande jamais de précisions, fais une hypothèse raisonnable et propose un chiffre.\n\n"
            . "Réponds STRICTEMENT dans ce format, sur deux lignes, sans rien ajouter d'autre :\n"
            . "PRIX: [uniquement un nombre entier, sans espace ni devise ni texte]\n"
            . "MESSAGE: [le message à envoyer au client]";

        try {
            $response = Http::withOptions(['verify' => app()->isProduction()])
                ->timeout(30)
                ->withHeaders([
                    'x-api-key'         => config('services.anthropic.key'),
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'       => 'claude-haiku-4-5-20251001',
                    'max_tokens'  => 200,
                    'temperature' => 0.7,
                    'messages'    => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (!$response->successful()) {
                \Log::error('Anthropic API error (suggestCounterOffer): ' . $response->body());
                $errMsg = $response->json('error.message') ?? 'Erreur API Claude.';
                return response()->json(['error' => app()->isLocal() ? $errMsg : 'Erreur Shopio IA. Réessayez.'], 500);
            }

            $text = trim($response->json('content.0.text') ?? '');

            $price   = null;
            $message = null;

            if (preg_match('/PRIX\s*:\s*([\d\s]+)/iu', $text, $m)) {
                $digits = preg_replace('/\D/', '', $m[1]);
                if ($digits !== '') {
                    $price = (float) $digits;
                }
            }
            if (preg_match('/MESSAGE\s*:\s*(.+)/isu', $text, $m)) {
                $message = trim($m[1]);
            }

            if (!$price && !$message) {
                return response()->json(['error' => 'Réponse IA invalide. Réessayez.'], 500);
            }

            return response()->json(['price' => $price, 'message' => $message]);

        } catch (\Exception $e) {
            \Log::error('Shopio IA suggestCounterOffer error: ' . $e->getMessage());
            $msg = app()->isLocal() ? $e->getMessage() : 'Erreur Shopio IA. Réessayez.';
            return response()->json(['error' => $msg], 500);
        }
    }

    // GET /boutique/notifications/poll — toutes les compteurs en temps réel
    public function pollAll(Request $request)
    {
        $vendeur = Auth::user();
        $shop    = $vendeur->shop ?? $vendeur->assignedShop;
        if (!$shop) return response()->json(['messages_unread'=>0,'orders_pending'=>0,'livreurs_available'=>0,'total'=>0,'latest_messages'=>[]]);

        $unreadQuery = ShopMessage::where('shop_id', $shop->id)
            ->whereNull('read_at')
            ->whereHas('sender', fn($q) => $q->where('role', 'client'));

        $messagesUnread = $unreadQuery->count();

        // Derniers messages non lus avec expéditeur et produit
        $latestMessages = ShopMessage::where('shop_id', $shop->id)
            ->whereNull('read_at')
            ->whereHas('sender', fn($q) => $q->where('role', 'client'))
            ->with(['sender:id,name', 'product:id,name'])
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'sender_name'  => $m->sender?->name ?? 'Client',
                'body'         => $m->type === 'images'
                    ? '📷 ' . ($m->body ?: 'Photo(s)')
                    : \Illuminate\Support\Str::limit($m->body ?? '', 60),
                'product_name' => $m->product?->name,
                'time'         => $m->created_at->format('H:i'),
            ]);

        $ordersPending = $shop->orders()
            ->whereIn('status', ['pending','en_attente','en attente','nouvelle'])
            ->count();

        $livreursAvailable = \App\Models\User::where('role', 'livreur')
            ->where('is_available', true)
            ->where('shop_id', $shop->id)
            ->count();

        /* ── Messages d'entreprises de livraison non lus (company → shop) ── */
        $companyMessagesUnread = DeliveryMessage::where('shop_id', $shop->id)
            ->where('sender_role', 'company')
            ->whereNull('read_at')
            ->count();

        $latestCompanyMessages = DeliveryMessage::where('shop_id', $shop->id)
            ->where('sender_role', 'company')
            ->whereNull('read_at')
            ->with('company:id,name')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'company_id'   => $m->delivery_company_id,
                'company_name' => $m->company?->name ?? 'Entreprise',
                'body'         => \Illuminate\Support\Str::limit($m->message ?? '', 60),
                'time'         => $m->created_at->format('H:i'),
            ]);

        /* ── Réponses SuperAdmin sur les tickets support de cette boutique ── */
        $latestSupportReplies = \App\Models\SupportMessage::whereHas('ticket', fn($q) => $q->where('shop_id', $shop->id))
            ->whereHas('author', fn($q) => $q->where('role', 'superadmin'))
            ->with(['ticket:id,subject', 'author:id,name'])
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'ticket_id'       => $m->ticket_id,
                'ticket_subject'  => \Illuminate\Support\Str::limit($m->ticket?->subject ?? 'Ticket #'.$m->ticket_id, 55),
                'body'            => \Illuminate\Support\Str::limit($m->body ?? '', 60),
                'time'            => $m->created_at->format('H:i'),
            ]);

        return response()->json([
            'messages_unread'          => $messagesUnread,
            'orders_pending'           => $ordersPending,
            'livreurs_available'       => $livreursAvailable,
            'total'                    => $messagesUnread + $ordersPending,
            'latest_messages'          => $latestMessages,
            'company_messages_unread'  => $companyMessagesUnread,
            'latest_company_messages'  => $latestCompanyMessages,
            'support_replies'          => $latestSupportReplies,
        ]);
    }
}
