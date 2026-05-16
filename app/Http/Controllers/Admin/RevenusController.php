<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenusController extends Controller
{
    public function index(Request $request)
    {
        $meInit = strtoupper(substr(Auth::user()->name ?? 'S', 0, 1));

        $nbBoutiques  = Shop::count();
        $nbEntreprises = DeliveryCompany::count();

        return view('admin.revenus.index', compact('meInit', 'nbBoutiques', 'nbEntreprises'));
    }
}
