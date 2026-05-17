<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('client_lat', 10, 7)->nullable()->after('last_ping_at');
            $table->decimal('client_lng', 10, 7)->nullable()->after('client_lat');
            $table->timestamp('client_location_shared_at')->nullable()->after('client_lng');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['client_lat', 'client_lng', 'client_location_shared_at']);
        });
    }
};
