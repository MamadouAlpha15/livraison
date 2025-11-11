<?php

// database/migrations/2025_01_02_120000_create_courier_commissions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courier_commissions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('order_id'); //
            $t->unsignedBigInteger('livreur_id');
            $t->unsignedBigInteger('shop_id')->nullable();

            $t->decimal('order_total', 12, 2)->default(0);
            $t->decimal('rate', 5, 4)->default(0.20); // 0.20 = 20%
            $t->decimal('amount', 12, 2)->default(0);

            $t->enum('status', ['pending','paid'])->default('pending');
            $t->timestamp('paid_at')->nullable();
            $t->string('payout_ref')->nullable();   // référence du règlement (ex: virement#XYZ)
            $t->text('payout_note')->nullable();    // note interne

            $t->timestamps();

            $t->unique(['order_id']); // 1 commission par commande
            $t->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $t->foreign('livreur_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('courier_commissions');
    }
};
