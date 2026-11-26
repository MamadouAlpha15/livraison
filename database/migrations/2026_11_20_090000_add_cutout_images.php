<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photos détourées (fond retiré par rembg, Boutique → Looks → « Retirer le fond ») :
 * gardées à côté de la photo d'origine, utilisées par le thème Lookbook (produit, motif, look).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->string('image_cutout')->nullable()->after('image'));
        Schema::table('product_variants', fn (Blueprint $t) => $t->string('image_cutout')->nullable()->after('image'));
        Schema::table('shop_looks', fn (Blueprint $t) => $t->string('image_cutout')->nullable()->after('image'));
    }

    public function down(): void
    {
        Schema::table('shop_looks', fn (Blueprint $t) => $t->dropColumn('image_cutout'));
        Schema::table('product_variants', fn (Blueprint $t) => $t->dropColumn('image_cutout'));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('image_cutout'));
    }
};
