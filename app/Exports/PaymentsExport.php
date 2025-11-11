<?php

namespace App\Exports;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PaymentsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Payment::with(['order.client', 'order.livreur', 'order.shop']);

        if (!empty($this->filters['shop_id'])) {
            $shopId = $this->filters['shop_id'];
            $query->whereHas('order', fn($q) => $q->where('shop_id', $shopId));
        }
        if (!empty($this->filters['order_id'])) {
            $query->where('order_id', $this->filters['order_id']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        $payments = $query->orderByDesc('created_at')->get();

        return $payments->map(function($p) {
            return [
                'ID' => $p->id,
                'Commande' => $p->order->id ?? '—',
                'Client' => $p->order->client->name ?? '—',
                'Livreur' => $p->order->livreur->name ?? '—',
                'Shop' => $p->order->shop->name ?? '—',
                'Montant' => $p->amount,
                'Date' => optional($p->created_at)->format('d/m/Y H:i')
            ];
        });
    }

    public function headings(): array
    {
        return ['ID','Commande','Client','Livreur','Shop','Montant','Date'];
    }
}
