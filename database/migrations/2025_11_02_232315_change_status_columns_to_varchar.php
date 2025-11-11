<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStatusColumnsToVarchar extends Migration
{
    public function up()
    {
        // Si tu utilises change() : composer require doctrine/dbal
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status', 50)->default('en_attente')->change();
        });

        Schema::table('courier_commissions', function (Blueprint $table) {
            $table->string('status', 50)->default('en_attente')->change();
        });

        // Si tu as une colonne status sur orders, aussi :
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 50)->default('en_attente')->change();
        });
    }

    public function down()
    {
        // ATTENTION : si tu reviens en arrière, tu dois remettre la définition ENUM d'origine manuellement
        // Ici on laisse tel quel pour éviter perte, adapte si nécessaire.
    }
}
