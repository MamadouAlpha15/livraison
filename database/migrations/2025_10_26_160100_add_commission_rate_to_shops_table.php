<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('shops', function (Blueprint $t) {
            $t->decimal('commission_rate', 5, 4) // taux de commission pour les livreurs
              ->default(0.20) // 20 % par défaut
              ->after('description');
        });
    }

    public function down(): void {
        Schema::table('shops', function (Blueprint $t) {
            $t->dropColumn('commission_rate');
        });
    }
};
