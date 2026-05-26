<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Liste toutes les boutiques (avec leur propriétaire).
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $search = $request->query('search', '');

        $query = Shop::with('owner')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('owner', fn($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        match ($filter) {
            'pro'       => $query->where('plan', 'pro')->where('plan_expires_at', '>', now()),
            'free'      => $query->where(fn($q) => $q->where('plan', 'free')->orWhereNull('plan')),
            'suspended' => $query->where('is_approved', false),
            default     => null,
        };

        $shops = $query->paginate(15)->withQueryString();

        return view('admin.shops.index', compact('shops', 'filter', 'search'));
    }

    /**
     * Suspendre ou réactiver une boutique (contrôle admin).
     * L'approbation est automatique à la création — ici on gère les suspensions pour abus/fraude.
     */
    public function update(Request $request, Shop $shop)
    {
        $shop->is_approved = !$shop->is_approved;
        $shop->save();

        // Si réactivée, s'assurer que le propriétaire a bien son shop_id et son rôle
        if ($shop->is_approved && $shop->owner) {
            $shop->owner->update([
                'shop_id'      => $shop->id,
                'role_in_shop' => 'admin',
            ]);
        }

        $msg = $shop->is_approved
            ? 'Boutique réactivée avec succès.'
            : 'Boutique suspendue avec succès.';

        return redirect()
            ->route('admin.shops.index')
            ->with('success', $msg);
    }

    /**
     * Supprimer une boutique (et ses relations si cascade activée).
     */
    public function destroy(Shop $shop)
    {
        $shop->delete();
        return redirect()
            ->route('admin.shops.index')
            ->with('success', 'Boutique supprimée avec succès.');
    }
}
