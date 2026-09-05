<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /** GET /api/v1/orders/{order}/invoice — même PDF que le site (Client\OrderController::downloadInvoice) */
    public function show(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['items.product', 'items.variant', 'shop', 'payment', 'client']);

        $pdf = Pdf::loadView('client.orders.invoice', ['order' => $order])->setPaper('a4', 'portrait');

        return $pdf->download('Recu-Shopio-Commande-' . $order->id . '.pdf');
    }
}
