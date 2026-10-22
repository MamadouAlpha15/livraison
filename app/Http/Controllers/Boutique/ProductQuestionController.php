<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\ProductQuestion;
use App\Services\PushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** La boutique répond aux questions publiques posées sur ses produits. */
class ProductQuestionController extends Controller
{
    private function shopId(): int
    {
        $user = Auth::user();
        $shop = $user->shop ?: $user->assignedShop;
        abort_unless($shop, 403, 'Aucune boutique associée à ce compte.');
        return $shop->id;
    }

    public function index(Request $request)
    {
        $shopId = $this->shopId();
        $filter = $request->get('filtre') === 'repondues' ? 'repondues' : 'a-repondre';

        $questions = ProductQuestion::with(['product:id,name,image', 'user:id,name'])
            ->where('shop_id', $shopId)
            ->when($filter === 'a-repondre', fn ($q) => $q->unanswered()->oldest())
            ->when($filter === 'repondues', fn ($q) => $q->answered()->latest('answered_at'))
            ->paginate(20)->withQueryString();

        return view('boutique.questions.index', [
            'questions'  => $questions,
            'filter'     => $filter,
            'toAnswer'   => ProductQuestion::where('shop_id', $shopId)->unanswered()->count(),
        ]);
    }

    public function answer(Request $request, ProductQuestion $question)
    {
        abort_unless($question->shop_id === $this->shopId(), 403);

        $data = $request->validate([
            'answer' => ['required', 'string', 'min:2', 'max:1000'],
        ], ['answer.required' => 'Écrivez votre réponse.']);

        $first = $question->answered_at === null;
        $question->update([
            'answer'      => trim($data['answer']),
            'answered_at' => $question->answered_at ?? now(),
            'answered_by' => Auth::id(),
        ]);

        // Prévenir le client à la première réponse
        if ($first && $question->user) {
            try {
                app(PushService::class)->sendToUser(
                    $question->user,
                    'La boutique a répondu à votre question',
                    ($question->product->name ?? 'Produit') . ' : « ' . mb_strimwidth($question->answer, 0, 90, '…') . ' »',
                    1,
                    route('client.products.show', $question->product_id, false) . '#questions'
                );
            } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Réponse publiée sur la fiche produit.');
    }

    public function destroy(ProductQuestion $question)
    {
        abort_unless($question->shop_id === $this->shopId(), 403);
        $question->delete();

        return back()->with('success', 'Question supprimée.');
    }
}
