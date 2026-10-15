<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broken_image_reports', function (Blueprint $table) {
            // InnoDB explicite : le reste de la base tourne en MyISAM (audit précédent),
            // pas de transactions/verrous fiables dessus. Cette table est nouvelle.
            $table->engine = 'InnoDB';
            $table->id();
            // Pas de FK vers `products`/`shops` (MyISAM) : intégrité gérée côté app.
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('shop_id');
            $table->json('missing_paths');          // liste des chemins d'images manquants détectés
            $table->string('status')->default('open'); // open | notified | resolved
            $table->timestamp('notified_at')->nullable();
            $table->unsignedBigInteger('notified_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broken_image_reports');
    }
};
