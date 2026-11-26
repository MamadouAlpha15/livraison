<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bénéfice réel par produit (Boutique → Bénéfices) :
 * - products.cost_price : prix d'achat d'une unité ;
 * - shops.delivery_cost / return_cost : ce que la boutique paie par commande livrée / retournée ;
 * - product_ad_spends : dépenses de publicité par produit et par mois (« 2026-11 »).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_price')->nullable()->after('original_price');
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->unsignedInteger('delivery_cost')->nullable()->after('currency');
            $table->unsignedInteger('return_cost')->nullable()->after('delivery_cost');
        });
        Schema::create('product_ad_spends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->char('month', 7);
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'month']);
            $table->index(['shop_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_ad_spends');
        Schema::table('shops', fn (Blueprint $t) => $t->dropColumn(['delivery_cost', 'return_cost']));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('cost_price'));
    }
};
