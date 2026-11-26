<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thème Lookbook : position du point numéroté de chaque pièce sur la photo du look (en % de la largeur
 * et de la hauteur). Le client touche le point pour voir la pièce. Vide : placé automatiquement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_look_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('pin_x')->nullable()->after('position');
            $table->unsignedTinyInteger('pin_y')->nullable()->after('pin_x');
        });
    }

    public function down(): void
    {
        Schema::table('shop_look_items', fn (Blueprint $t) => $t->dropColumn(['pin_x', 'pin_y']));
    }
};
