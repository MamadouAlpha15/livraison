<?php

// app/Http/Controllers/Admin/PaymentController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $shopId = $this->currentShopId();

        $payments = Payment::with(['order.client','order.shop'])  // en francais : charge avec les relations order et client et shop
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId)) // filtre par shop_id si $shopId est defini
            ->latest()
            ->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }
}
