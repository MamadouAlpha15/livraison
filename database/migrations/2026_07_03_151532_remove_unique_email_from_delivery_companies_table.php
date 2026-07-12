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
        Schema::table('delivery_companies', function (Blueprint $table) {
            $table->dropUnique('delivery_companies_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_companies', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
