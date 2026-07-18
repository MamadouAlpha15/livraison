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
        Schema::create('driver_bonuses', function (Blueprint $table) {
            $table->id();
            // livreur_id = l'utilisateur (User) livreur, qu'il soit chauffeur d'entreprise ou livreur boutique
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('bonus_date'); // le jour où l'objectif a été atteint
            $table->unsignedInteger('deliveries_count'); // nb de livraisons ce jour-là (pour affichage/historique)
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            // Un seul bonus par livreur et par jour
            $table->unique(['user_id', 'bonus_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_bonuses');
    }
};
