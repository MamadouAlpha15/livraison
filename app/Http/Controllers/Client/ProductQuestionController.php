<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQuestion;
use App\Services\PushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

/** Le client pose une question publique sur une fiche produit. */
class ProductQuestionController extends Controller
{
    private const DAILY_LIMIT = 5;

    public function store(Request $request, Product $product)
    {
        abort_unless($product->is_active && optional($product->shop)->is_approved, 404);

        $data = $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'question.required' => 'Écrivez votre question.',
            'question.min'      => 'Votre question est un peu courte.',
            'question.max'      => 'Votre question dépasse 500 caractères.',
        ]);

        $key = 'product-question:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, self::DAILY_LIMIT)) {
            return back()->with('qa_warning', 'Vous avez déjà posé ' . self::DAILY_LIMIT . " questions aujourd'hui. Réessayez demain.")->withFragment('questions');
        }
        RateLimiter::hit($key, 86400);

        ProductQuestion::create([
            'product_id' => $product->id,
            'shop_id'    => $product->shop_id,
            'user_id'    => Auth::id(),
            'question'   => trim($data['question']),
        ]);

        // Prévenir la boutique
        try {
            $owner = $product->shop->user;
            if ($owner) {
                app(PushService::class)->sendToUser($owner, 'Nouvelle question sur un produit', $product->name . ' : « ' . mb_strimwidth(trim($data['question']), 0, 90, '…') . ' »', 0, '/boutique/questions');
            }
        } catch (\Throwable $e) {}

        return back()->with('qa_success', 'Question envoyée. Vous serez prévenu quand la boutique aura répondu.')->withFragment('questions');
    }
}
