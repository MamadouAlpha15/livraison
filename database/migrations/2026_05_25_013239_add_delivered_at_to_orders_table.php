<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('updated_at');
        });

        // Backfill : utilise created_at (stable) pour éviter que des éditions post-livraison
        // ne faussent la date de livraison réelle.
        DB::table('orders')
            ->where('status', 'livrée')
            ->whereNull('delivered_at')
            ->update(['delivered_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivered_at');
        });
    }
};
