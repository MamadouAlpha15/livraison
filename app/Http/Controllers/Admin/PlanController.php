<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCompany;
use App\Models\Shop;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    const SUBSCRIPTION_DAYS = 30;

    public function index(Request $request)
    {
        $shopSearch = $request->get('shop_search');
        $companySearch = $request->get('company_search');

        $shops = Shop::query()
            ->when($shopSearch, function ($q) use ($shopSearch) {
                $q->where(function ($sub) use ($shopSearch) {
                    $sub->where('name', 'like', "%{$shopSearch}%")
                        ->orWhere('email', 'like', "%{$shopSearch}%")
                        ->orWhere('phone', 'like', "%{$shopSearch}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'shops_page')
            ->withQueryString();

        $companies = DeliveryCompany::query()
            ->when($companySearch, function ($q) use ($companySearch) {
                $q->where(function ($sub) use ($companySearch) {
                    $sub->where('name', 'like', "%{$companySearch}%")
                        ->orWhere('email', 'like', "%{$companySearch}%")
                        ->orWhere('phone', 'like', "%{$companySearch}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'companies_page')
            ->withQueryString();

        $stats = [
            'shops_total'        => Shop::count(),
            'shops_pro'          => Shop::where('plan', 'pro')->where('plan_expires_at', '>', now())->count(),
            'companies_total'    => DeliveryCompany::count(),
            'companies_business' => DeliveryCompany::where('plan', 'business')->where('plan_expires_at', '>', now())->count(),
        ];

        return view('admin.plans.index', compact('shops', 'companies', 'stats', 'shopSearch', 'companySearch'));
    }

    public function updateShop(Request $request, Shop $shop)
    {
        $request->validate(['plan' => 'required|in:free,pro']);

        $shop->update($request->plan === 'pro'
            ? ['plan' => 'pro', 'plan_expires_at' => now()->addDays(self::SUBSCRIPTION_DAYS)]
            : ['plan' => 'free', 'plan_expires_at' => null]
        );

        $msg = $request->plan === 'pro'
            ? "Boutique « {$shop->name} » passée au plan Pro jusqu'au " . $shop->plan_expires_at->format('d/m/Y') . '.'
            : "Boutique « {$shop->name} » repassée au plan Gratuit.";

        return back()->with('success', $msg);
    }

    public function updateCompany(Request $request, DeliveryCompany $company)
    {
        $request->validate(['plan' => 'required|in:free,business']);

        $company->update($request->plan === 'business'
            ? ['plan' => 'business', 'plan_expires_at' => now()->addDays(self::SUBSCRIPTION_DAYS)]
            : ['plan' => 'free', 'plan_expires_at' => null]
        );

        $msg = $request->plan === 'business'
            ? "Entreprise « {$company->name} » passée au plan Business jusqu'au " . $company->plan_expires_at->format('d/m/Y') . '.'
            : "Entreprise « {$company->name} » repassée au plan Gratuit.";

        return back()->with('success', $msg);
    }
}
