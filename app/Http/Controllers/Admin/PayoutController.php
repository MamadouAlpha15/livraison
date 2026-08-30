<?php

namespace App\Http\Controllers\Admin;

// ─────────────────────────────────────────────────────────────────────────────
// Admin\PayoutController
// Reversement aux boutiques de leur part sur les commandes payées en ligne
// (ChapChap Pay). Déclenché manuellement ici, commande par commande, le temps
// de valider que le système fonctionne correctement avec de l'argent réel.
// ─────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\ShopPayoutService;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct(private ShopPayoutService $payoutService) {}

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'due'); // due | sent | failed | all

        $payments = Payment::with(['order.shop'])
            ->where('method', Payment::METHOD_CHAPCHAPPAY)
            ->where('status', 'payé')
            ->when($filter !== 'all', function ($q) use ($filter) {
                $q->where('payout_status', $filter);
            })
            ->orderByDesc('paid_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'due_count'   => Payment::where('method', Payment::METHOD_CHAPCHAPPAY)->where('status', 'payé')->where('payout_status', Payment::PAYOUT_DUE)->count(),
            'due_amount'  => Payment::where('method', Payment::METHOD_CHAPCHAPPAY)->where('status', 'payé')->where('payout_status', Payment::PAYOUT_DUE)->sum('payout_amount'),
            'sent_count'  => Payment::where('method', Payment::METHOD_CHAPCHAPPAY)->where('payout_status', Payment::PAYOUT_SENT)->count(),
            'failed_count'=> Payment::where('method', Payment::METHOD_CHAPCHAPPAY)->where('payout_status', Payment::PAYOUT_FAILED)->count(),
        ];

        return view('admin.payouts.index', compact('payments', 'stats', 'filter'));
    }

    // Déclenche le reversement pour UN paiement
    public function send(Payment $payment)
    {
        $result = $this->payoutService->sendPayout($payment);

        if ($result['success']) {
            return back()->with('success', "Reversement envoyé pour la commande #{$payment->order_id}.");
        }

        return back()->with('danger', $result['message'] ?? 'Échec du reversement.');
    }
}
