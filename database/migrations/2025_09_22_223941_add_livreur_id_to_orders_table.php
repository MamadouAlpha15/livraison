<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('livreur_id')->nullable()->after('shop_id');
            $table->foreign('livreur_id')->references('id')->on('users')->onDelete('set null'); 
        });
    }

    public function down(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['livreur_id']);
            $table->dropColumn('livreur_id');
        });
    }
};
