<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** POST /api/v1/orders/{order}/review — { rating, comment? } */
    public function store(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::create([
            'order_id'   => $order->id,
            'user_id'    => $request->user()->id,
            'vendeur_id' => $order->shop->user_id,
            'livreur_id' => $order->livreur_id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return response()->json(['message' => 'Merci pour votre avis !', 'review_id' => $review->id], 201);
    }
}
