<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    /**
     * GET /api/v1/loyalty — reprend Client\LoyaltyController@index (points,
     * lien de parrainage, filleuls, historique des transactions).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = $user->loyaltyTransactions()->latest()->paginate(20);

        $referrals = $user->referrals()
            ->select('id', 'name', 'created_at', 'referral_rewarded_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => [
                'loyalty_points' => $user->loyalty_points,
                'referral_code'  => $user->referral_code,
                'referral_url'   => url('/register?ref=' . $user->referral_code),
                'referrals'      => $referrals->map(fn ($r) => [
                    'id'         => $r->id,
                    'name'       => $r->name,
                    'created_at' => $r->created_at?->toIso8601String(),
                    'rewarded'   => (bool) $r->referral_rewarded_at,
                ]),
                'transactions' => collect($transactions->items())->map(fn ($t) => [
                    'id'          => $t->id,
                    'description' => $t->description,
                    'points'      => $t->points,
                    'created_at'  => $t->created_at?->toIso8601String(),
                ]),
            ],
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }
}
