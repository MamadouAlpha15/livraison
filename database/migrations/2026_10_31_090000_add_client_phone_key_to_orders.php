<?php

use App\Support\PhoneKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Numéro du client normalisé (9 derniers chiffres) pour reconnaître un même client d'une commande à l'autre :
 * commandes en double, historique de livraison du numéro (App\Support\CustomerRisk). Rempli pour les anciennes commandes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('client_phone_key', 12)->nullable()->after('client_phone');
            $table->index(['client_phone_key', 'created_at']);
        });

        DB::table('orders')->whereNotNull('client_phone')->orderBy('id')->select('id', 'client_phone')
            ->chunkById(500, function ($orders) {
                foreach ($orders as $o) {
                    $key = PhoneKey::of($o->client_phone);
                    if ($key) {
                        DB::table('orders')->where('id', $o->id)->update(['client_phone_key' => $key]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['client_phone_key', 'created_at']);
            $table->dropColumn('client_phone_key');
        });
    }
};
