<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('cart_items', function (Blueprint $table) {
            // Marque qu'une notification de relance a déjà été envoyée pour cette ligne
            // du panier — évite de relancer le client chaque jour pour le même article.
            $table->timestamp('reminded_at')->nullable()->after('quantity');
        });
    }

    public function down(): void {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('reminded_at');
        });
    }
};
