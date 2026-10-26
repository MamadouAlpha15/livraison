<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Formules de départ des campagnes (modifiables ensuite par l'équipe Shopio).
 * Mise en avant : prix 7 jours ; 3 jours = 50 % ; 30 jours = 3 × le prix 7 jours.
 * Réseaux sociaux : prix = budget pub + frais Shopio, affichés séparément au vendeur.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $boost = fn (int $week) => json_encode(['3' => intdiv($week, 2), '7' => $week, '30' => $week * 3]);

        $rows = [
            [
                'type' => 'boost', 'code' => 'boost_produit', 'name' => 'Boost produit', 'placement' => 'category_search',
                'duration_days' => 7, 'prices' => $boost(50000), 'ad_budget_gnf' => 0, 'fee_gnf' => 0,
                'max_items' => 1, 'allows_shop_wide' => false, 'max_actives' => 6, 'capacity_scope' => 'category',
                'details' => json_encode(['promotes' => '1 produit', 'where' => 'Catégorie + recherche, avec le badge « Sponsorisé »']),
                'sort_order' => 1,
            ],
            [
                'type' => 'boost', 'code' => 'top_categorie', 'name' => 'Top catégorie', 'placement' => 'category_top',
                'duration_days' => 7, 'prices' => $boost(100000), 'ad_budget_gnf' => 0, 'fee_gnf' => 0,
                'max_items' => 3, 'allows_shop_wide' => false, 'max_actives' => 4, 'capacity_scope' => 'category',
                'details' => json_encode(['promotes' => "Jusqu'à 3 produits", 'where' => 'Premières places de leur catégorie']),
                'sort_order' => 2,
            ],
            [
                'type' => 'boost', 'code' => 'boutique_une', 'name' => 'Boutique à la une', 'placement' => 'home_featured',
                'duration_days' => 7, 'prices' => $boost(150000), 'ad_budget_gnf' => 0, 'fee_gnf' => 0,
                'max_items' => 0, 'allows_shop_wide' => true, 'max_actives' => 6, 'capacity_scope' => 'global',
                'details' => json_encode(['promotes' => 'La boutique entière', 'where' => "Carrousel « Boutiques à la une » sur l'accueil"]),
                'sort_order' => 3,
            ],
            [
                'type' => 'social', 'code' => 'social_starter', 'name' => 'Starter', 'placement' => 'social',
                'duration_days' => 7, 'prices' => json_encode(['7' => 300000]), 'ad_budget_gnf' => 200000, 'fee_gnf' => 100000,
                'max_items' => 5, 'allows_shop_wide' => true, 'max_actives' => null, 'capacity_scope' => 'global',
                'details' => json_encode(['networks' => ['facebook', 'instagram'], 'images' => 1, 'videos' => 0, 'targeting' => 'Conakry']),
                'sort_order' => 4,
            ],
            [
                'type' => 'social', 'code' => 'social_pro', 'name' => 'Pro', 'placement' => 'social',
                'duration_days' => 14, 'prices' => json_encode(['14' => 750000]), 'ad_budget_gnf' => 550000, 'fee_gnf' => 200000,
                'max_items' => 5, 'allows_shop_wide' => true, 'max_actives' => null, 'capacity_scope' => 'global',
                'details' => json_encode(['networks' => ['facebook', 'instagram', 'tiktok'], 'images' => 3, 'videos' => 1, 'targeting' => 'Conakry']),
                'sort_order' => 5,
            ],
            [
                'type' => 'social', 'code' => 'social_premium', 'name' => 'Premium', 'placement' => 'social',
                'duration_days' => 30, 'prices' => json_encode(['30' => 1500000]), 'ad_budget_gnf' => 1150000, 'fee_gnf' => 350000,
                'max_items' => 5, 'allows_shop_wide' => true, 'max_actives' => null, 'capacity_scope' => 'global',
                'details' => json_encode(['networks' => ['facebook', 'instagram', 'tiktok'], 'images' => 5, 'videos' => 2, 'targeting' => 'Par commune']),
                'sort_order' => 6,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('ad_packages')->updateOrInsert(
                ['code' => $row['code']],
                $row + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('ad_packages')->whereIn('code', [
            'boost_produit', 'top_categorie', 'boutique_une', 'social_starter', 'social_pro', 'social_premium',
        ])->delete();
    }
};
