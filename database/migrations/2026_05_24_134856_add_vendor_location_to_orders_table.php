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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('vendor_lat', 10, 7)->nullable()->after('client_location_shared_at');
            $table->decimal('vendor_lng', 10, 7)->nullable()->after('vendor_lat');
            $table->timestamp('vendor_location_shared_at')->nullable()->after('vendor_lng');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['vendor_lat', 'vendor_lng', 'vendor_location_shared_at']);
        });
    }
};
