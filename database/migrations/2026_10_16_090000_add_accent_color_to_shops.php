<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Couleur d'accent de la vitrine, choisie par le vendeur dans « Ma boutique » → Concevoir.
 * Valeurs autorisées (voir ShopController@update) : bleu Shopio, bleu nuit, ambre, vert.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('accent_color', 7)->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('accent_color');
        });
    }
};
