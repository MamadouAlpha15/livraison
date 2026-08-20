<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('cascade');

            // Un même produit (avec la même variante, ou sans variante) ne fait qu'une ligne
            // dans le panier d'un client : on incrémente la quantité au lieu de dupliquer.
            $table->unique(['user_id', 'product_id', 'product_variant_id'], 'cart_items_unique_line');
        });
    }

    public function down(): void {
        Schema::dropIfExists('cart_items');
    }
};
