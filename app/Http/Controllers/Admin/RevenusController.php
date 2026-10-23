<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Shop;
use Illuminate\Http\Request;

class RevenusController extends Controller
{
    public function index(Request $request)
    {

        $nbBoutiques  = Shop::count();
        $nbEntreprises = DeliveryCompany::count();

        return view('admin.revenus.index', compact('nbBoutiques', 'nbEntreprises'));
    }
}
