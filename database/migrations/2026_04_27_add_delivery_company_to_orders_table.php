<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_company_id')
                  ->nullable()
                  ->after('livreur_id')
                  ->constrained('delivery_companies')
                  ->nullOnDelete();

            $table->foreignId('driver_id')
                  ->nullable()
                  ->after('delivery_company_id')
                  ->constrained('drivers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_company_id']);
            $table->dropForeign(['driver_id']);
            $table->dropColumn(['delivery_company_id', 'driver_id']);
        });
    }
};
