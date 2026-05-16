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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Outils Laravel
use Illuminate\Http\Request;           // Données envoyées par le formulaire/AJAX
use Illuminate\Support\Facades\Auth;  // Utilisateur connecté

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
            'product_id' => ['nullable', 'exists:products,id'], // L'ID du produit est optionnel
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
        // (puisque le vendeur répond, il a forcément lu les messages du client)
        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $clientId)      // Messages envoyés par le client
            ->where('receiver_id', $vendeur->id) // Destinés au vendeur
            ->whereNull('read_at')               // Pas encore marqués comme lus
            ->update(['read_at' => now()]);       // On les marque comme lus maintenant

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

        // Marquer la proposition originale comme "refusée" (remplacée par la contre-offre)
        $original->update(['proposal_status' => ShopMessage::STATUS_REFUSED]);

        // Créer la contre-offre du vendeur
        ShopMessage::create([
            'shop_id'         => $shop->id,
            'product_id'      => $original->product_id,
            'sender_id'       => $vendeur->id,
            'receiver_id'     => $original->sender_id,
            'body'            => "🔄 Contre-proposition du vendeur : "
                                . number_format($counterPrice, 0, ',', ' ') . " {$devise}.",
            'type'            => ShopMessage::TYPE_COUNTER_OFFER,
            'proposed_price'  => $counterPrice,
            'proposal_status' => ShopMessage::STATUS_PENDING,
        ]);

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
            'product_id' => ['nullable', 'exists:products,id'],
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
            'product_id' => ['nullable', 'exists:products,id'],
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
                                        ? array_map(fn($p) => ImageOptimizer::url($p, 'large') ?? asset('storage/'.$p), $m->images)
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

        // 1. Sauvegarder les fichiers bruts en temp (ultra rapide — pas de traitement)
        $tempPaths = [];
        foreach ($request->file('images') as $file) {
            $tempName  = 'temp/' . Str::random(24) . '.' . $file->getClientOriginalExtension();
            Storage::disk('local')->put($tempName, file_get_contents($file->getRealPath()));
            $tempPaths[] = $tempName;
        }

        $count = count($tempPaths);

        // 2. Créer le message en statut "processing" avec des URLs temporaires
        $msg = ShopMessage::create([
            'shop_id'      => $shop->id,
            'product_id'   => $product?->id,
            'sender_id'    => $vendeur->id,
            'receiver_id'  => $client->id,
            'body'         => $count . ' photo(s)',
            'images'       => [],
            'image_status' => 'processing',
            'type'         => ShopMessage::TYPE_IMAGES,
        ]);

        // 3. Dispatcher le job en arrière-plan (n'attend pas la fin)
        ProcessImageJob::dispatch($msg->id, $tempPaths, 'messages/' . $shop->id);

        // 4. Marquer les messages du client comme lus
        ShopMessage::where('shop_id', $shop->id)
            ->where('sender_id', $client->id)
            ->where('receiver_id', $vendeur->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success'    => true,
            'sent'       => true,
            'message_id' => $msg->id,
            'images'     => [],
            'image_status' => 'processing',
            'count'      => $count,
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
            fn($p) => ImageOptimizer::url($p, 'large') ?? asset('storage/' . $p),
            $message->images
        );

        return response()->json([
            'status' => 'ready',
            'images' => $urls,
        ]);
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
            ->whereIn('status', ['pending','en_attente','en attente','confirmée','processing','nouvelle'])
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
