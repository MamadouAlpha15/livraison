<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\CourierCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $shop = $user->shop ?: $user->assignedShop;
        abort_unless($shop, 403);

        $status = $request->get('status', CourierCommission::STATUS_EN_ATTENTE);

        $query = CourierCommission::where('shop_id', $shop->id)
            ->with(['livreur', 'order'])
            ->orderByDesc('id');

        if (in_array($status, [CourierCommission::STATUS_EN_ATTENTE, CourierCommission::STATUS_PAYEE])) {
            $query->where('status', $status);
        }

        $commissions = $query->paginate(15);

        $totalPending = CourierCommission::where('shop_id', $shop->id)
            ->where('status', CourierCommission::STATUS_EN_ATTENTE)
            ->sum('amount');

        $totalPaid = CourierCommission::where('shop_id', $shop->id)
            ->where('status', CourierCommission::STATUS_PAYEE)
            ->sum('amount');

        return view('boutique.commissions.index', compact(
            'commissions', 'status', 'totalPending', 'totalPaid'
        ));
    }

    public function pay(Request $request)
    {
        $data = $request->validate([
            'ids'         => ['required', 'array'],
            'ids.*'       => ['integer'],
            'payout_ref'  => ['nullable', 'string', 'max:190'],
            'payout_note' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $shop = $user->shop ?: $user->assignedShop;
        abort_unless($shop, 403);

        DB::transaction(function () use ($data, $shop) {
            $rows = CourierCommission::whereIn('id', $data['ids'])
                ->where('shop_id', $shop->id)
                ->where('status', CourierCommission::STATUS_EN_ATTENTE)
                ->lockForUpdate()
                ->get();

            if ($rows->isEmpty()) {
                Log::warning('Aucune commission trouvée pour marquage PAYEE', [
                    'shop_id' => $shop->id,
                    'ids' => $data['ids'],
                ]);
                return;
            }

            foreach ($rows as $c) {
                $c->status     = CourierCommission::STATUS_PAYEE;
                $c->paid_at    = now();
                $c->payout_ref = $data['payout_ref']  ?? $c->payout_ref;
                $c->payout_note= $data['payout_note'] ?? $c->payout_note;
                $c->save();
            }

            Log::info('Commissions marquées comme payées', [
                'shop_id' => $shop->id,
                'paid_by' => Auth::id(),
                'ids' => $rows->pluck('id')->all(),
            ]);
        });

        return back()->with('success', 'Les commissions sélectionnées ont été marquées comme PAYÉES.');
    }
}
