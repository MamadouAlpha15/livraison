<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Lien de suivi court et impossible à deviner : /suivi/{tracking_token} (App\Support\OrderAccess).
 * Il ne montre pas le numéro de la commande et ne dépend pas de la clé secrète du serveur.
 * Les commandes déjà passées reçoivent leur jeton ici ; les anciens liens signés continuent de marcher.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_token', 24)->nullable()->unique()->after('id');
        });

        DB::table('orders')->whereNull('tracking_token')->orderBy('id')->select('id')->chunkById(500, function ($orders) {
            foreach ($orders as $o) {
                DB::table('orders')->where('id', $o->id)->update(['tracking_token' => Str::lower(Str::random(20))]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['tracking_token']);
            $table->dropColumn('tracking_token');
        });
    }
};
