<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\ShopMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /* ── Liste des commandes du client ── */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with(['shop', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('client.orders.index', compact('orders'));
    }

    /* ── Formulaire commande générale (pharmacie, saisie manuelle) ── */
    public function create()
    {
        $shops = Shop::where('is_approved', true)
            ->with(['products' => fn($q) => $q->select('id','shop_id','name','price','image','description')])
            ->get(['id','name','type']);

        $allProducts = $shops->mapWithKeys(fn($s) => [
            $s->id => $s->products->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'price'       => (float) $p->price,
                'image_url'   => $p->image ? asset('storage/'.$p->image) : null,
                'description' => $p->description,
            ])->values(),
        ]);

        return view('client.orders.create', [
            'shops'             => $shops,
            'allProducts'       => $allProducts,
            'selectedShopId'    => request('shop_id'),
            'selectedProductId' => request('product_id'),
        ]);
    }

    /* ── Enregistrement commande générale ── */
    public function store(Request $request)
    {
        $request->validate([
            'shop_id'    => 'required|exists:shops,id',
            'total'      => 'required|numeric|min:1',
            'ordonnance' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'image'      => 'nullable|image|max:4096',
        ]);

        $imagePath      = null;
        $ordonnancePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('order_images', 'public');
        }
        if ($request->hasFile('ordonnance')) {
            $ordonnancePath = $request->file('ordonnance')->store('ordonnances', 'public');
        }

        $order = Order::create([
            'user_id'    => Auth::id(),
            'shop_id'    => $request->shop_id,
            'total'      => $request->total,
            'status'     => Order::STATUS_EN_ATTENTE,
            'ordonnance' => $ordonnancePath,
            'image'      => $imagePath,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'method'   => 'cash',
            'amount'   => $request->total,
            'status'   => 'en_attente',
        ]);

        return redirect()->route('client.orders.index')
            ->with('success', 'Commande passée avec succès ! Paiement en cash à la livraison.');
    }

    /* ── Formulaire commande depuis un produit ── */
    public function createFromProduct(Product $product)
    {
        abort_unless(optional($product->shop)->is_approved, 404);

        $client  = Auth::user();
        $shop    = $product->shop;
        $vendeur = $shop->user;

        /* Charger l'historique des messages client ↔ vendeur sur ce produit */
        $messages = ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where(function ($q) use ($client, $vendeur) {
                $q->where('sender_id', $client->id)
                  ->orWhere('sender_id', optional($vendeur)->id);
            })
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->get();

        /* Marquer les messages reçus comme lus */
        ShopMessage::where('shop_id', $shop->id)
            ->where('product_id', $product->id)
            ->where('receiver_id', $client->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('client.orders.create_from_product', [
            'product'  => $product,
            'messages' => $messages,
        ]);
    }

    /* ── Enregistrement commande produit ── */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::with('shop')->findOrFail($request->product_id);
        abort_unless($product->shop && $product->shop->is_approved, 403);

        /* Vérifier le stock si disponible */
        if ($product->stock !== null && $product->stock < $request->quantity) {
            return back()->withErrors(['quantity' => "Stock insuffisant. Seulement {$product->stock} disponible(s)."]);
        }

        $total = $product->price * $request->quantity;

        $order = Order::create([
            'user_id' => Auth::id(),
            'shop_id' => $product->shop->id,
            'total'   => $total,
            'status'  => Order::STATUS_EN_ATTENTE,
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => $request->quantity,
            'price'      => $product->price,
        ]);

        /* Décrémenter le stock si géré */
        if ($product->stock !== null) {
            $product->decrement('stock', $request->quantity);
        }

        Payment::create([
            'order_id' => $order->id,
            'method'   => 'cash',
            'amount'   => $total,
            'status'   => 'en_attente',
        ]);

        return redirect()->route('client.orders.index')
            ->with('success', "Commande passée avec succès ! Vous recevrez une confirmation. 🎉");
    }
}