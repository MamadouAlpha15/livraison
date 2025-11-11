<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // orders.total
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 18, 2)->change();
        });

        // payments.amount
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 18, 2)->change();
        });

        // order_items.price (si présent)
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 18, 2)->change();
        });

        // products.price (par cohérence si tu vends des produits très chers)
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 18, 2)->change();
        });
    }

    public function down(): void
    {
        // remets tes anciens types si besoin
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->change();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });
    }
};
