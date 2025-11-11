<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Models\Order;
use App\Models\Payment;

class StatsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $from = $this->filters['date_from'] ?? null;
        $to   = $this->filters['date_to'] ?? null;
        $shopId = $this->filters['shop_id'] ?? null;

        $ordersQuery = Order::query();
        $paymentsQuery = Payment::query();

        if ($shopId) {
            $ordersQuery->where('shop_id', $shopId);
            $paymentsQuery->whereHas('order', function ($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            });
        }

        if ($from) {
            $ordersQuery->whereDate('created_at', '>=', $from);
            $paymentsQuery->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $ordersQuery->whereDate('created_at', '<=', $to);
            $paymentsQuery->whereDate('created_at', '<=', $to);
        }

        $totalOrders = $ordersQuery->count();
        $totalRevenue = $ordersQuery->sum('total');
        $avgOrder = $totalOrders ? ($totalRevenue / $totalOrders) : 0;
        $totalPayments = $paymentsQuery->sum('amount');

        $rows = [
            ['Clé', 'Valeur'],
            ['Période', ($from ?? '—') . ' — ' . ($to ?? '—')],
            ['Nombre de commandes', $totalOrders],
            ['Chiffre d\'affaires', round($totalRevenue, 2)],
            ['Montant total paiements', round($totalPayments, 2)],
            ['Moyenne par commande', round($avgOrder, 2)],
        ];

        return new Collection($rows);
    }

    public function headings(): array
    {
        return ['Clé','Valeur'];
    }
}
