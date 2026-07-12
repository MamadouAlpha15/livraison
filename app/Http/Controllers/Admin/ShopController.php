<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Purge définitive : supprime tout ce qui appartient aux boutiques sélectionnées.
     */
    public function purgeBulk(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []));
        if (empty($ids)) {
            return back()->with('error', 'Aucune boutique sélectionnée.');
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $shopId) {
                $shop = Shop::find($shopId);
                if (!$shop) continue;

                // --- Commandes et données liées ---
                $orderIds = DB::table('orders')->where('shop_id', $shopId)->pluck('id');
                if ($orderIds->isNotEmpty()) {
                    DB::table('courier_commissions')->whereIn('order_id', $orderIds)->delete();
                    DB::table('reviews')->whereIn('order_id', $orderIds)->delete();
                    DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                    DB::table('payments')->whereIn('order_id', $orderIds)->delete();
                    DB::table('orders')->whereIn('id', $orderIds)->delete();
                }

                // --- Messages boutique ---
                DB::table('shop_messages')->where('shop_id', $shopId)->delete();

                // --- Produits ---
                DB::table('products')->where('shop_id', $shopId)->delete();

                // --- Visites ---
                DB::table('shop_visits')->where('shop_id', $shopId)->delete();

                // --- Tickets support liés à cette boutique ---
                $ticketIds = DB::table('support_tickets')->where('shop_id', $shopId)->pluck('id');
                if ($ticketIds->isNotEmpty()) {
                    DB::table('support_messages')->whereIn('ticket_id', $ticketIds)->delete();
                    DB::table('support_tickets')->whereIn('id', $ticketIds)->delete();
                }

                // --- Tables pivot ---
                DB::table('shop_user')->where('shop_id', $shopId)->delete();
                DB::table('shop_favorites')->where('shop_id', $shopId)->delete();

                // --- Abonnements ---
                DB::table('subscriptions')
                    ->where('subscriber_type', Shop::class)
                    ->where('subscriber_id', $shopId)
                    ->delete();

                // --- Utilisateurs liés (employés, livreurs, propriétaire) ---
                $userIds = User::where('shop_id', $shopId)->pluck('id')->toArray();
                if ($shop->user_id && !in_array($shop->user_id, $userIds)) {
                    $userIds[] = $shop->user_id;
                }
                if (!empty($userIds)) {
                    DB::table('push_subscriptions')->whereIn('user_id', $userIds)->delete();
                    // Tickets support des utilisateurs
                    $uTicketIds = DB::table('support_tickets')->whereIn('user_id', $userIds)->pluck('id');
                    if ($uTicketIds->isNotEmpty()) {
                        DB::table('support_messages')->whereIn('ticket_id', $uTicketIds)->delete();
                        DB::table('support_tickets')->whereIn('id', $uTicketIds)->delete();
                    }
                    User::whereIn('id', $userIds)->delete();
                }

                // --- La boutique elle-même ---
                $shop->delete();
            }
        });

        $count = count($ids);
        return redirect()->route('admin.dashboard')
            ->with('success', "$count boutique(s) supprimée(s) définitivement.");
    }
}
