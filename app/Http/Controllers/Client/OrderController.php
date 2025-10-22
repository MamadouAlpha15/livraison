<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Shop; // ✅ import du modèle Shop
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller 
{
    // 📌 Liste des commandes du client connecté
    public function index()
    {
        $orders = Auth::user()->orders()->latest()->paginate(10);
        return view('client.orders.index', compact('orders')); 
    }

    // 📌 Formulaire de commande "générale" (ex: pharmacie, saisie manuelle du total)
    // App\Http\Controllers\Client\OrderController.php

public function create()
{
    // Boutiques approuvées + leurs produits (champs utiles)
    $shops = Shop::where('is_approved', true)
        ->with(['products' => function ($q) {
            $q->select('id','shop_id','name','price','image','description');
        }])
        ->get(['id','name','type']);

    // Map [shopId => [ {id,name,price,image_url,description}, ... ]]
    $allProducts = $shops->mapWithKeys(function ($s) {
        return [
            $s->id => $s->products->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'price'       => (float) $p->price,
                    'image_url'   => $p->image ? asset('storage/'.$p->image) : null,
                    'description' => $p->description,
                ];
            })->values(),
        ];
    });

    return view('client.orders.create', [
        'shops'             => $shops,
        'allProducts'       => $allProducts,      // <— prêt pour @json sans fn() PHP
        'selectedShopId'    => request('shop_id'),
        'selectedProductId' => request('product_id'),
    ]);
}


    // 📌 Enregistrement d’une commande "générale"
    public function store(Request $request)
    {
        $request->validate([
            'shop_id'    => 'required|exists:shops,id',
            'total'      => 'required|numeric|min:1',
            'ordonnance' => 'nullable|file|mimes:pdf,jpg,png|max:2048'
        ]);

        // Upload ordonnance si envoyée
        $ordonnancePath = null;
        if ($request->hasFile('ordonnance')) {
            $ordonnancePath = $request->file('ordonnance')->store('ordonnances', 'public');
        }

        // Création de la commande
        $order = Order::create([
            'user_id'    => Auth::id(),
            'shop_id'    => $request->shop_id,
            'total'      => $request->total,
            'status'     => 'pending',
            'ordonnance' => $ordonnancePath
        ]);

        // Paiement cash
        Payment::create([
            'order_id' => $order->id,
            'method'   => 'cash',
            'amount'   => $request->total,
            'status'   => 'pending',
        ]);

        return redirect()->route('client.orders.index')
            ->with('success', 'Commande passée avec succès ! Paiement en cash à la livraison.');
    }

    // 📌 Formulaire à partir d’un produit (cas restaurant, boutique, etc.)
    public function createFromProduct(Product $product)
    {
        // Sécurité : uniquement si la boutique est approuvée
        abort_unless(optional($product->shop)->is_approved, 404);

        return view('client.orders.create_from_product', [
            'product' => $product
        ]);
    }

    // 📌 Enregistrement d’une commande PRODUIT
    public function storeProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::with('shop')->findOrFail($request->product_id);
        abort_unless($product->shop && $product->shop->is_approved, 403);

        $total = $product->price * $request->quantity;

        // Création commande
        $order = Order::create([
            'user_id'    => Auth::id(),
            'shop_id'    => $product->shop->id,
            'total'      => $total,
            'status'     => 'pending',
        ]);

        // Item associé
        OrderItem::create([
            'order_id'   => $order->id, 
            'product_id' => $product->id,
            'quantity'   => $request->quantity,
            'price'      => $product->price,
        ]);

        // Paiement cash
        Payment::create([
            'order_id' => $order->id,
            'method'   => 'cash',
            'amount'   => $total,
            'status'   => 'pending',
        ]);

        return redirect()->route('client.orders.index')->with('success', 'Commande passée avec succès ! ✅');
    }
}
