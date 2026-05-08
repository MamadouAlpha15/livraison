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
use App\Models\ShopMessage;  // Table "shop_messages" — les messages entre client et vendeur

// On importe Request : objet qui contient toutes les données envoyées par le formulaire (POST, GET...)
use Illuminate\Http\Request;

// On importe Auth : façade pour récupérer l'utilisateur connecté facilement
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        // Auth::user() = l'utilisateur connecté
        // ->orders() = toutes les commandes qui lui appartiennent (relation définie dans le modèle User)
        // ->with(['shop', 'items.product']) = on charge aussi la boutique et les produits de chaque commande
        //    en une seule requête SQL (évite les N+1 queries — problème de performance)
        // ->latest() = trie du plus récent au plus ancien (ORDER BY created_at DESC)
        // ->paginate(10) = affiche 10 commandes par page avec pagination automatique
        $orders = Auth::user()->orders()
            ->with(['shop', 'items.product', 'review'])
            ->latest()
            ->paginate(15);

        // On retourne la vue "resources/views/client/orders/index.blade.php"
        // compact('orders') = passe la variable $orders à la vue (raccourci de ['orders' => $orders])
        return view('client.orders.index', compact('orders'));
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

        // On redirige le client vers la liste de ses commandes
        // ->with('success', '...') = envoie un message flash (s'affiche une seule fois)
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
    public function createFromProduct(Product $product)
    {
        // abort_unless(condition, code_http) = si la condition est FAUSSE, on arrête avec une erreur
        // optional() = évite une erreur si $product->shop est null
        // ->is_approved = la boutique doit être validée par l'admin, sinon erreur 404
        abort_unless(optional($product->shop)->is_approved, 404);

        // On récupère le client connecté
        $client  = Auth::user();

        // On récupère la boutique qui vend ce produit (relation définie dans le modèle Product)
        $shop    = $product->shop;

        // On récupère le vendeur (propriétaire de la boutique)
        $vendeur = $shop->user;

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

        // On retourne la vue avec le produit et les messages
        return view('client.orders.create_from_product', [
            'product'  => $product,   // Les infos du produit
            'messages' => $messages,  // L'historique des messages
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
        // Validation des données du formulaire
        $request->validate([
            'product_id' => 'required|exists:products,id', // L'ID du produit doit exister en base
            'quantity'   => 'required|integer|min:1',      // Quantité : nombre entier, minimum 1
        ]);

        // On charge le produit avec sa boutique en une seule requête (optimisation)
        // findOrFail() = cherche par ID, si non trouvé renvoie une erreur 404 automatiquement
        $product = Product::with('shop')->findOrFail($request->product_id);

        // Vérification de sécurité : la boutique doit exister et être approuvée
        // abort_unless avec 403 = erreur "Accès refusé"
        abort_unless($product->shop && $product->shop->is_approved, 403);

        // Vérification du stock (si le produit a un stock géré)
        // $product->stock !== null = le produit a un stock défini (pas illimité)
        if ($product->stock !== null && $product->stock < $request->quantity) {
            // Le stock est insuffisant : on redirige avec un message d'erreur
            // withErrors() = passe les erreurs au formulaire (affichées en rouge)
            return back()->withErrors(['quantity' => "Stock insuffisant. Seulement {$product->stock} disponible(s)."]);
        }

        // On calcule le total : prix unitaire × quantité
        $total = $product->price * $request->quantity;

        // On crée la commande en base de données
        $order = Order::create([
            'user_id' => Auth::id(),                 // ID du client connecté
            'shop_id' => $product->shop->id,         // ID de la boutique du produit
            'total'   => $total,                     // Montant calculé ci-dessus
            'status'  => Order::STATUS_EN_ATTENTE,   // Statut initial : en attente
        ]);

        // On crée la ligne de commande (order item = détail du produit commandé)
        OrderItem::create([
            'order_id'   => $order->id,        // Lien avec la commande
            'product_id' => $product->id,      // Quel produit
            'quantity'   => $request->quantity,// Combien d'exemplaires
            'price'      => $product->price,   // Prix unitaire au moment de la commande
        ]);

        // Si le stock est géré, on le diminue du nombre commandé
        // decrement('stock', N) = fait stock = stock - N dans la base de données
        if ($product->stock !== null) {
            $product->decrement('stock', $request->quantity);
        }

        // On crée le paiement associé à cette commande
        Payment::create([
            'order_id' => $order->id,  // Lien avec la commande
            'method'   => 'cash',      // Paiement en espèces à la livraison
            'amount'   => $total,      // Montant du paiement = total de la commande
            'status'   => 'en_attente',// Paiement pas encore effectué
        ]);

        // On redirige vers la liste des commandes avec un message de succès
        return redirect()->route('client.orders.index')
            ->with('success', "Commande passée avec succès ! Vous recevrez une confirmation. 🎉");
    }
}
