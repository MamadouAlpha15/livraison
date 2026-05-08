<?php

namespace App\Http\Controllers\Vendeur;

use App\Http\Controllers\Controller;
use App\Models\CourierCommission;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $shop = $user->shop ?: $user->assignedShop;
        abort_unless($shop, 403);

        $status = $request->get('status', CourierCommission::STATUS_EN_ATTENTE);
        $type   = in_array($request->get('type'), ['shop', 'company']) ? $request->get('type') : 'shop';

        $query = CourierCommission::where('shop_id', $shop->id)
            ->with(['livreur', 'order.client', 'order.driver.user', 'order.deliveryCompany', 'order.deliveryZone'])
            ->orderByDesc('id');

        if (in_array($status, [CourierCommission::STATUS_EN_ATTENTE, CourierCommission::STATUS_PAYEE])) {
            $query->where('status', $status);
        }

        if ($type === 'shop') {
            $query->whereHas('order', fn($q) => $q->whereNull('driver_id'));
        } else {
            $query->whereHas('order', fn($q) => $q->whereNotNull('driver_id'));
        }

        $commissions = $query->paginate(20);

        /* Pour type=company : calculer combien de commandes étaient dans chaque trajet groupe */
        $groupCounts = collect();
        if ($type === 'company') {
            $groupCounts = Order::select(
                    'user_id', 'driver_id',
                    DB::raw('LOWER(TRIM(COALESCE(delivery_destination,\'\'))) AS dest_norm'),
                    DB::raw('COUNT(*) AS cnt')
                )
                ->where('shop_id', $shop->id)
                ->whereNotNull('driver_id')
                ->where('status', Order::STATUS_LIVREE)
                ->groupBy('user_id', 'driver_id', DB::raw('LOWER(TRIM(COALESCE(delivery_destination,\'\')))'))
                ->having('cnt', '>', 1)
                ->get()
                ->keyBy(fn($r) => $r->user_id . '::' . $r->driver_id . '::' . $r->dest_norm);
        }

        /* Pour type=shop : compter les commandes de chaque batch (livraison groupée boutique) */
        $batchCounts = collect();
        if ($type === 'shop') {
            $batchIds = $commissions->pluck('delivery_batch_id')->filter()->unique();
            if ($batchIds->isNotEmpty()) {
                $batchCounts = Order::select('delivery_batch_id', DB::raw('COUNT(*) AS cnt'))
                    ->whereIn('delivery_batch_id', $batchIds)
                    ->groupBy('delivery_batch_id')
                    ->get()
                    ->keyBy('delivery_batch_id');
            }
        }

        /* KPI globaux */
        $base = fn() => CourierCommission::where('shop_id', $shop->id);

        $totalPending = $base()->where('status', CourierCommission::STATUS_EN_ATTENTE)->sum('amount');
        $totalPaid    = $base()->where('status', CourierCommission::STATUS_PAYEE)->sum('amount');

        /* KPI par type */
        $shopPending = $base()
            ->where('status', CourierCommission::STATUS_EN_ATTENTE)
            ->whereHas('order', fn($q) => $q->whereNull('driver_id'))
            ->sum('amount');

        $companyPending = $base()
            ->where('status', CourierCommission::STATUS_EN_ATTENTE)
            ->whereHas('order', fn($q) => $q->whereNotNull('driver_id'))
            ->sum('amount');

        $shopPaid = $base()
            ->where('status', CourierCommission::STATUS_PAYEE)
            ->whereHas('order', fn($q) => $q->whereNull('driver_id'))
            ->sum('amount');

        $companyPaid = $base()
            ->where('status', CourierCommission::STATUS_PAYEE)
            ->whereHas('order', fn($q) => $q->whereNotNull('driver_id'))
            ->sum('amount');

        $devise = $shop->currency ?? 'GNF';

        return view('boutique.commissions.index', compact(
            'commissions', 'type', 'status',
            'totalPending', 'totalPaid',
            'shopPending', 'companyPending', 'shopPaid', 'companyPaid',
            'devise', 'shop', 'groupCounts', 'batchCounts'
        ));
    }

    public function pay(Request $request)
    {
        $data = $request->validate([
            'ids'          => ['required', 'array'],
            'ids.*'        => ['integer'],
            'amounts'      => ['nullable', 'array'],
            'amounts.*'    => ['nullable', 'numeric', 'min:0'],
            'payout_ref'   => ['nullable', 'string', 'max:190'],
            'payout_note'  => ['nullable', 'string'],
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
                $c->status      = CourierCommission::STATUS_PAYEE;
                $c->paid_at     = now();
                $c->payout_ref  = $data['payout_ref']  ?? $c->payout_ref;
                $c->payout_note = $data['payout_note'] ?? $c->payout_note;
                // Mettre à jour le montant si saisi
                if (isset($data['amounts'][$c->id]) && $data['amounts'][$c->id] !== null) {
                    $c->amount = $data['amounts'][$c->id];
                }
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

    public function export(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $shop = $user->shop ?: $user->assignedShop;
        abort_unless($shop, 403);

        $type = in_array($request->get('type'), ['shop', 'company']) ? $request->get('type') : 'shop';

        $query = CourierCommission::where('shop_id', $shop->id)
            ->where('status', CourierCommission::STATUS_PAYEE)
            ->with(['livreur', 'order.client', 'order.driver.user', 'order.deliveryCompany', 'order.deliveryZone'])
            ->orderByDesc('paid_at');

        if ($type === 'shop') {
            $query->whereHas('order', fn($q) => $q->whereNull('driver_id'));
        } else {
            $query->whereHas('order', fn($q) => $q->whereNotNull('driver_id'));
        }

        $commissions = $query->get();
        $devise   = $shop->currency ?? 'GNF';
        $typeSlug = $type === 'company' ? 'entreprises' : 'livreurs';
        $filename = 'commissions_' . $typeSlug . '_payees_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($commissions, $devise, $type) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");

            if ($type === 'shop') {
                fputcsv($out, [
                    'Réf commande', 'Livreur', 'Téléphone livreur',
                    'Destination livraison',
                    'Montant commande (' . $devise . ')',
                    'Commission payée (' . $devise . ')',
                    'Référence paiement', 'Note interne', 'Payée le',
                ], ';');
                foreach ($commissions as $c) {
                    fputcsv($out, [
                        $c->order_id ? '#' . $c->order_id : '—',
                        $c->livreur?->name ?? '—',
                        $c->livreur?->phone ?? '—',
                        $c->order?->delivery_destination ?: ($c->order?->client?->address ?? '—'),
                        $c->order?->total ?? '—',
                        number_format($c->amount, 0, ',', ' '),
                        $c->payout_ref  ?? '—',
                        $c->payout_note ?? '—',
                        optional($c->paid_at)->format('d/m/Y H:i') ?? '—',
                    ], ';');
                }
            } else {
                fputcsv($out, [
                    'Réf commande', 'Entreprise partenaire', 'Téléphone entreprise',
                    'Chauffeur', 'Destination livraison',
                    'Montant commande (' . $devise . ')',
                    'Commission payée (' . $devise . ')',
                    'Référence paiement', 'Note interne', 'Payée le',
                ], ';');
                foreach ($commissions as $c) {
                    $company = $c->order?->deliveryCompany;
                    $driver  = $c->order?->driver?->user;
                    fputcsv($out, [
                        $c->order_id ? '#' . $c->order_id : '—',
                        $company?->name ?? '—',
                        $company?->phone ?? '—',
                        $driver?->name ?? '—',
                        $c->order?->delivery_destination ?: ($c->order?->client?->address ?? '—'),
                        $c->order?->total ?? '—',
                        number_format($c->amount, 0, ',', ' '),
                        $c->payout_ref  ?? '—',
                        $c->payout_note ?? '—',
                        optional($c->paid_at)->format('d/m/Y H:i') ?? '—',
                    ], ';');
                }
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
