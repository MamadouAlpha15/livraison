<?php

namespace App\Http\Controllers\Boutique;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Support\ProfitReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Boutique → Bénéfices : ce que chaque produit rapporte vraiment sur un mois.
 * Le vendeur renseigne le prix d'achat de ses produits, sa pub du mois par produit et ce que lui coûtent
 * une livraison et un retour ; le calcul se fait sur les commandes réellement livrées (App\Support\ProfitReport).
 */
class ProfitController extends Controller
{
    private function shop(): Shop
    {
        $user = Auth::user();
        $shop = $user->shop;
        abort_unless($shop && (int) $shop->user_id === (int) $user->id, 403, 'Seul le propriétaire de la boutique voit les bénéfices.');
        return $shop;
    }

    public function index(Request $request): View
    {
        $shop = $this->shop();
        $month = ProfitReport::month($request->query('mois'));
        $report = ProfitReport::build($shop, $month);

        // Mode « coûts » : tous les produits, pour renseigner le prix d'achat et la pub du mois
        $editing = $request->boolean('couts');
        $all = $editing
            ? Product::where('shop_id', $shop->id)->orderByDesc('is_active')->orderBy('name')->get(['id', 'name', 'image', 'cost_price', 'price', 'is_active'])
            : collect();
        $ads = $editing
            ? DB::table('product_ad_spends')->where('shop_id', $shop->id)->where('month', $month)->pluck('amount', 'product_id')
            : collect();

        return view('boutique.profit', [
            'shop'    => $shop,
            'month'   => $month,
            'months'  => ProfitReport::months(),
            'report'  => $report,
            'editing' => $editing,
            'all'     => $all,
            'ads'     => $ads,
            'devise'  => $shop->currency ?? 'GNF',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = $this->shop();
        $month = ProfitReport::month($request->input('mois'));
        $data = $request->validate([
            'delivery_cost' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'return_cost'   => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'cost'          => ['array', 'max:500'],
            'cost.*'        => ['nullable', 'integer', 'min:0', 'max:10000000000'],
            'ads'           => ['array', 'max:500'],
            'ads.*'         => ['nullable', 'integer', 'min:0', 'max:10000000000'],
        ], [
            '*.integer' => 'Les montants doivent être des nombres entiers, sans espace ni point.',
            '*.min'     => 'Un montant ne peut pas être négatif.',
        ]);

        DB::transaction(function () use ($shop, $month, $data) {
            if (array_key_exists('delivery_cost', $data) || array_key_exists('return_cost', $data)) {
                $shop->forceFill([
                    'delivery_cost' => $data['delivery_cost'] ?? null,
                    'return_cost'   => $data['return_cost'] ?? null,
                ])->save();
            }
            $ids = Product::where('shop_id', $shop->id)->pluck('id')->flip(); // seulement les produits de la boutique
            foreach ($data['cost'] ?? [] as $pid => $cost) {
                if (!isset($ids[(int) $pid])) continue;
                Product::whereKey((int) $pid)->update(['cost_price' => $cost === null || $cost === '' ? null : (int) $cost]);
            }
            foreach ($data['ads'] ?? [] as $pid => $amount) {
                if (!isset($ids[(int) $pid])) continue;
                if ($amount === null || (int) $amount === 0) {
                    DB::table('product_ad_spends')->where('product_id', (int) $pid)->where('month', $month)->delete();
                    continue;
                }
                DB::table('product_ad_spends')->updateOrInsert(
                    ['product_id' => (int) $pid, 'month' => $month],
                    ['shop_id' => $shop->id, 'amount' => (int) $amount, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        return redirect()->route('boutique.profit.index', ['mois' => $month])->with('success', 'Coûts enregistrés. Les bénéfices sont recalculés.');
    }
}
