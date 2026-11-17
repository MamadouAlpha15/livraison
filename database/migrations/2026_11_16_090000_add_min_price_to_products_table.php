<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prix minimum privé d'un produit : jamais montré aux clients. Calculé par l'Importateur Produit Shopio
 * (prix de revient arrondi aux 1 000 GNF supérieurs) : Shopio IA ne propose jamais un prix plus bas dans le chat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('min_price', 15, 2)->nullable()->after('original_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('min_price');
        });
    }
};
