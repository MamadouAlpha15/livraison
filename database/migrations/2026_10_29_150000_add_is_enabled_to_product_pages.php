<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Interrupteur par produit de la page construite : désactivée par défaut (les clients voient la page produit
 * habituelle). Les pages déjà publiées avant cette migration restent visibles : elles sont activées ici.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_pages', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(false)->after('published_at');
        });

        DB::table('product_pages')->whereNotNull('published_at')->update(['is_enabled' => true]);
    }

    public function down(): void
    {
        Schema::table('product_pages', fn (Blueprint $table) => $table->dropColumn('is_enabled'));
    }
};
