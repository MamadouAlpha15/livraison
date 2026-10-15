<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige la faute de frappe « Lunnetes » saisie par des vendeurs comme catégorie
 * de produit : elle apparaissait telle quelle dans la barre de catégories de
 * l'accueil et séparait ces produits de la catégorie « Lunettes ».
 * Ne touche qu'aux valeurs exactement fautives (variantes de casse/espaces incluses).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereIn(DB::raw('LOWER(TRIM(category))'), ['lunnetes', 'lunnette', 'lunnettes', 'lunetes'])
            ->update(['category' => 'Lunettes']);
    }

    public function down(): void
    {
        // Correction de données volontairement irréversible (on ne réintroduit pas une faute).
    }
};
