<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrdersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Order::with(['client', 'livreur', 'shop']); // Relations pour noms

        if (!empty($this->filters['shop_id'])) {
            $query->where('shop_id', $this->filters['shop_id']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['livreur_id'])) {
            $query->where('livreur_id', $this->filters['livreur_id']);
        }

        $orders = $query->orderByDesc('created_at')->get();

        return $orders->map(function($o) {
            return [
                'ID' => $o->id,
                'Client' => $o->client->name ?? '—',
                'Shop' => $o->shop->name ?? '—',
                'Livreur' => $o->livreur->name ?? '—',
                'Total' => $o->total,
                'Statut' => $o->status,
                'Date' => optional($o->created_at)->format('d/m/Y H:i')
            ];
        });
    }

    public function headings(): array
    {
        return ['ID','Client','Shop','Livreur','Total','Statut','Date'];
    }
}
