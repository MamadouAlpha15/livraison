<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromoCodeController extends Controller
{
    protected function shop()
    {
        $shop = Auth::user()->shop ?? Auth::user()->assignedShop;
        abort_unless($shop, 403);

        return $shop;
    }

    public function index()
    {
        $shop = $this->shop();

        $promoCodes = PromoCode::where('shop_id', $shop->id)->latest()->get();

        return view('boutique.promo-codes.index', compact('promoCodes', 'shop'));
    }

    public function store(Request $request)
    {
        $shop = $this->shop();
        $data = $this->validated($request, $shop);

        PromoCode::create($data + ['shop_id' => $shop->id]);

        return back()->with('success', 'Code promo créé avec succès.');
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $shop = $this->shop();
        abort_unless($promoCode->shop_id === $shop->id, 403);

        $data = $this->validated($request, $shop, $promoCode->id);

        $promoCode->update($data);

        return back()->with('success', 'Code promo modifié avec succès.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $shop = $this->shop();
        abort_unless($promoCode->shop_id === $shop->id, 403);

        $promoCode->delete();

        return back()->with('success', 'Code promo supprimé.');
    }

    public function toggleActive(PromoCode $promoCode)
    {
        $shop = $this->shop();
        abort_unless($promoCode->shop_id === $shop->id, 403);

        $promoCode->update(['is_active' => !$promoCode->is_active]);

        return back()->with('success', $promoCode->is_active ? 'Code promo activé.' : 'Code promo désactivé.');
    }

    protected function validated(Request $request, $shop, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code'                 => [
                'required', 'string', 'max:30',
                function ($attribute, $value, $fail) use ($shop, $ignoreId) {
                    $exists = PromoCode::where('shop_id', $shop->id)
                        ->where('code', strtoupper(trim($value)))
                        ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                        ->exists();
                    if ($exists) {
                        $fail('Ce code existe déjà pour votre boutique.');
                    }
                },
            ],
            'type'                 => 'required|in:percent,fixed',
            'value'                => 'required|integer|min:1' . ($request->type === 'percent' ? '|max:100' : ''),
            'min_purchase_amount'  => 'nullable|integer|min:0',
            'max_uses'             => 'nullable|integer|min:1',
            'expires_at'           => 'nullable|date',
        ]);
    }
}
