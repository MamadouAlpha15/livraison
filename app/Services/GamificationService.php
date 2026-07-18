<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverBonus;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;

class GamificationService
{
    /** Nombre de livraisons à atteindre dans la journée pour débloquer la prime. */
    const DAILY_GOAL = 10;

    /** Montant de la prime journalière (dans la devise du livreur). */
    const DAILY_BONUS = 5000;

    /** Nombre de livraisons complétées aujourd'hui par ce livreur (chauffeur d'entreprise ou livreur boutique). */
    public function deliveriesToday(User $livreur, ?Driver $driver): int
    {
        return Order::where(function ($q) use ($livreur, $driver) {
                $q->where('livreur_id', $livreur->id);
                if ($driver) {
                    $q->orWhere('driver_id', $driver->id);
                }
            })
            ->where('status', Order::STATUS_LIVREE)
            ->whereDate('updated_at', today())
            ->count();
    }

    /** Progression vers l'objectif du jour, prête pour l'affichage. */
    public function dailyProgress(User $livreur, ?Driver $driver): array
    {
        $count         = $this->deliveriesToday($livreur, $driver);
        $alreadyAwarded = DriverBonus::where('user_id', $livreur->id)->where('bonus_date', today())->exists();

        return [
            'count'    => $count,
            'goal'     => self::DAILY_GOAL,
            'bonus'    => self::DAILY_BONUS,
            'percent'  => min(100, (int) round($count / self::DAILY_GOAL * 100)),
            'rewarded' => $alreadyAwarded,
        ];
    }

    /**
     * Vérifie si le livreur vient d'atteindre l'objectif du jour et, si oui, enregistre
     * la prime (une seule fois par jour, grâce à la contrainte unique user_id+bonus_date).
     * Retourne true seulement si une NOUVELLE prime vient d'être débloquée à l'instant
     * (pour déclencher une célébration côté vue) — false si déjà attribuée ou objectif non atteint.
     */
    public function checkAndAwardDailyBonus(User $livreur, ?Driver $driver): bool
    {
        $count = $this->deliveriesToday($livreur, $driver);
        if ($count < self::DAILY_GOAL) {
            return false;
        }

        if (DriverBonus::where('user_id', $livreur->id)->where('bonus_date', today())->exists()) {
            return false;
        }

        DriverBonus::create([
            'user_id'          => $livreur->id,
            'bonus_date'       => today(),
            'deliveries_count' => $count,
            'amount'           => self::DAILY_BONUS,
        ]);

        return true;
    }

    /**
     * Classement des livreurs "pairs" du livreur donné, sur les livraisons de la semaine en cours
     * (lundi → dimanche). Pairs = autres chauffeurs de la même entreprise de livraison si chauffeur
     * d'entreprise, sinon autres livreurs de la même boutique.
     * Retourne un tableau trié par nombre de livraisons décroissant, avec le rang et un marqueur "is_me".
     */
    public function weeklyLeaderboard(User $livreur, ?Driver $driver, ?Shop $shop): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek   = now()->endOfWeek();

        if ($driver && $driver->delivery_company_id) {
            $peers = Driver::where('delivery_company_id', $driver->delivery_company_id)
                ->whereNotNull('user_id')
                ->with('user:id,name')
                ->get();

            $rows = $peers->map(function ($d) use ($startOfWeek, $endOfWeek) {
                $count = Order::where('driver_id', $d->id)
                    ->where('status', Order::STATUS_LIVREE)
                    ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                    ->count();
                return ['user_id' => $d->user_id, 'name' => $d->user?->name ?? $d->name, 'count' => $count];
            });
        } elseif ($shop) {
            $peers = User::where('shop_id', $shop->id)->where('role', 'livreur')->get(['id', 'name']);

            $rows = $peers->map(function ($u) use ($startOfWeek, $endOfWeek) {
                $count = Order::where('livreur_id', $u->id)
                    ->where('status', Order::STATUS_LIVREE)
                    ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                    ->count();
                return ['user_id' => $u->id, 'name' => $u->name, 'count' => $count];
            });
        } else {
            $rows = collect();
        }

        return $rows->sortByDesc('count')->values()
            ->map(function ($row, $i) use ($livreur) {
                $row['rank']  = $i + 1;
                $row['is_me'] = (int) $row['user_id'] === (int) $livreur->id;
                return $row;
            })->all();
    }
}
