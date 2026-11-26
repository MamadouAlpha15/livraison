<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thème Lookbook (mode, pagne, accessoires) :
 * - shop_looks : un « look » = une tenue mise en scène (« Jour de marché ») avec sa photo portée ;
 * - shop_look_items : les produits qui composent le look (robe, sac…), dans l'ordre ;
 * - products.sizes : tailles proposées pour un produit (S, M, L, XL…), choisies à la commande.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_looks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('title', 60);
            $table->string('scene', 120)->nullable();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['shop_id', 'is_active', 'position']);
        });
        Schema::create('shop_look_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('look_id')->constrained('shop_looks')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->unique(['look_id', 'product_id']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->json('sizes')->nullable()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('sizes'));
        Schema::dropIfExists('shop_look_items');
        Schema::dropIfExists('shop_looks');
    }
};
