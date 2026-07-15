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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('flash_price', 10, 2)->nullable()->after('original_price');
            $table->timestamp('flash_starts_at')->nullable()->after('flash_price');
            $table->timestamp('flash_ends_at')->nullable()->after('flash_starts_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['flash_price', 'flash_starts_at', 'flash_ends_at']);
        });
    }
};
