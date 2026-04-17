<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('country', 2)->nullable()->after('currency');
        });

        // Copier le pays du propriétaire vers chaque boutique existante
        DB::statement('
            UPDATE shops
            SET country = (
                SELECT country FROM users WHERE users.id = shops.user_id LIMIT 1
            )
            WHERE country IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
};
