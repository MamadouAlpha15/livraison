<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identifiant du pixel Meta (Facebook) du vendeur, chargé uniquement sur les pages
 * de sa boutique (voir App\Support\MetaPixel et resources/views/partials/meta-pixel.blade.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('meta_pixel_id', 20)->nullable()->after('storefront');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('meta_pixel_id');
        });
    }
};
