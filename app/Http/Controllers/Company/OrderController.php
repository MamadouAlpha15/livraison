<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private function company(): DeliveryCompany
    {
        $company = DeliveryCompany::where('user_id', auth()->id())->first();

        if (!$company) {
            abort(403, "Aucune entreprise de livraison liée à ce compte.");
        }

        return $company;
    }

    public function index(Request $request)
    {
        $company = $this->company();

        $query = Order::with(['client', 'shop', 'driver', 'items'])
            ->where('delivery_company_id', $company->id)
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('id', 'like', "%{$s}%")
                  ->orWhere('delivery_destination', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($u) => $u->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
                  ->orWhereHas('shop',   fn($sh) => $sh->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('boutique')) {
            $query->where('shop_id', $request->boutique);
        }

        $orders = $query->paginate(20)->withQueryString();

        $base = fn() => Order::where('delivery_company_id', $company->id);

        $stats = [
            'total'         => $base()->count(),
            'en_attente'    => $base()->where('status', Order::STATUS_EN_ATTENTE)->count(),
            'en_livraison'  => $base()->where('status', Order::STATUS_EN_LIVRAISON)->count(),
            'livrees_today' => $base()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', today())->count(),
            'revenus_today' => $base()->where('status', Order::STATUS_LIVREE)->whereDate('updated_at', today())->sum('delivery_fee'),
        ];

        $drivers = $company->drivers()->orderByRaw("FIELD(status,'available','busy','offline')")->orderBy('name')->get();

        return view('company.orders.index', compact('orders', 'stats', 'drivers', 'company'));
    }

    public function assign(Request $request, Order $order)
    {
        $company = $this->company();

        abort_unless((int) $order->delivery_company_id === $company->id, 403, 'Cette commande ne vous appartient pas.');

        $data = $request->validate([
            'driver_id'            => ['required', 'exists:drivers,id'],
            'delivery_fee'         => ['required', 'numeric', 'min:0'],
            'delivery_destination' => ['nullable', 'string', 'max:255'],
        ]);

        $driver = Driver::where('id', $data['driver_id'])
            ->where('delivery_company_id', $company->id)
            ->firstOrFail();

        $order->update([
            'driver_id'            => $driver->id,
            'delivery_fee'         => $data['delivery_fee'],
            'delivery_destination' => $data['delivery_destination'] ?? $order->delivery_destination,
            'status'               => Order::STATUS_EN_LIVRAISON,
        ]);

        $driver->update(['status' => 'busy']);

        // Synchroniser User.is_available → false (en mission)
        if ($driver->user_id) {
            \App\Models\User::where('id', $driver->user_id)->update(['is_available' => false]);
        }

        return response()->json([
            'success'      => true,
            'driver_name'  => $driver->name,
            'driver_phone' => $driver->phone,
            'delivery_fee' => number_format($data['delivery_fee'], 0, ',', ' '),
            'status'       => Order::STATUS_EN_LIVRAISON,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $company = $this->company();

        abort_unless((int) $order->delivery_company_id === $company->id, 403, 'Cette commande ne vous appartient pas.');

        $allowed = [
            Order::STATUS_EN_LIVRAISON,
            Order::STATUS_LIVREE,
            Order::STATUS_ANNULEE,
        ];

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', $allowed)],
        ]);

        $order->update(['status' => $data['status']]);

        // Libérer le chauffeur si livraison terminée
        if (in_array($data['status'], [Order::STATUS_LIVREE, Order::STATUS_ANNULEE], true) && $order->driver_id) {
            $freedDriver = Driver::find($order->driver_id);
            if ($freedDriver) {
                $freedDriver->update(['status' => 'available']);
                // Synchroniser User.is_available → true (de nouveau disponible)
                if ($freedDriver->user_id) {
                    \App\Models\User::where('id', $freedDriver->user_id)->update(['is_available' => true]);
                }
            }
        }

        return response()->json(['success' => true, 'status' => $data['status']]);
    }
}
