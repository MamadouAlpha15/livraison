<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shop_messages', function (Blueprint $table) {
            // ready = images optimisées prêtes | processing = job en cours | failed = échec
            $table->string('image_status')->default('ready')->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('shop_messages', function (Blueprint $table) {
            $table->dropColumn('image_status');
        });
    }
};
