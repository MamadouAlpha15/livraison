<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class SuiviController extends Controller
{
       public function show(Order $order)
{
    return view('orders.show', compact('order'));
}
}
