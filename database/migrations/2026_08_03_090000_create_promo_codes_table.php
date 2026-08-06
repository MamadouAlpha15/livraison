<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('code', 30);
            $table->enum('type', ['percent', 'fixed']); // pourcentage ou montant fixe en GNF
            $table->unsignedInteger('value'); // 10 (=10%) ou 5000 (=5000 GNF)
            $table->unsignedInteger('min_purchase_amount')->nullable(); // achat minimum requis
            $table->unsignedInteger('max_uses')->nullable(); // null = illimité
            $table->unsignedInteger('uses_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shop_id', 'code']);
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
