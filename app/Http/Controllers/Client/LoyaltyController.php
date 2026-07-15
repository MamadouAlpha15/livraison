<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = $user->loyaltyTransactions()->paginate(20);

        $referrals = $user->referrals()
            ->select('id', 'name', 'created_at', 'referral_rewarded_at')
            ->orderByDesc('created_at')
            ->get();

        $referralUrl = url('/register?ref=' . $user->referral_code);

        return view('client.loyalty.index', compact('user', 'transactions', 'referrals', 'referralUrl'));
    }
}
