<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        // Récupère tous les paiements avec leurs commandes et clients
        $payments = Payment::with(['order.client', 'order.shop'])
            ->latest()
            ->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }
}
