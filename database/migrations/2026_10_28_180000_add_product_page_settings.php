<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Page produit personnalisable (App\Support\ProductPage) :
 * shops.product_page = réglages communs (bandeau, couleur, badges, étapes, FAQ, livraison par commune) ;
 * products.page      = réglages du produit (offres groupées, points forts, urgence, FAQ du produit).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->json('product_page')->nullable()->after('storefront');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->json('page')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('page'));
        Schema::table('shops', fn (Blueprint $table) => $table->dropColumn('product_page'));
    }
};
