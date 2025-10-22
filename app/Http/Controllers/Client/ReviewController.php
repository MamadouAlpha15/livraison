<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function create(Order $order)
    {
        // Vérifier que le client est bien propriétaire
        if ($order->user_id !== Auth::id()) {
            abort(403, "Accès interdit");
        }

        return view('client.reviews.create', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, "Accès interdit");
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        Review::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'vendeur_id' => $order->shop->user_id,
            'livreur_id' => $order->livreur_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return redirect()->route('client.orders.index')->with('success', 'Merci pour votre avis ✅');
    }
}
