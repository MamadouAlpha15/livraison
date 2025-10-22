<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id'); // boutique
            $table->string('name'); // nom du produit
            $table->text('description')->nullable(); // description
            $table->decimal('price', 10, 2);    // prix 10 indique le nombre total de chiffres pouvant être stockés (avant et après la virgule). 2 indique le nombre de chiffres pouvant être stockés après la virgule.
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true); // produit actif ou non
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade'); // clé étrangère vers la table shops
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};
