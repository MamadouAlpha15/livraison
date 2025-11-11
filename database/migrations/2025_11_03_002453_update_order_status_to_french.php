<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Mettre à jour les statuts existants en français
        DB::table('orders')->where('status', 'pending')->update(['status' => 'en_attente']);
        DB::table('orders')->where('status', 'confirmed')->update(['status' => 'confirmée']);
        DB::table('orders')->where('status', 'delivering')->update(['status' => 'en_livraison']);
        DB::table('orders')->where('status', 'delivered')->update(['status' => 'livrée']);
        DB::table('orders')->where('status', 'canceled')->update(['status' => 'annulée']);
    }

    public function down(): void
    {
        // Optionnel : revenir aux statuts anglais si rollback
        DB::table('orders')->where('status', 'en_attente')->update(['status' => 'pending']);
        DB::table('orders')->where('status', 'confirmée')->update(['status' => 'confirmed']);
        DB::table('orders')->where('status', 'en_livraison')->update(['status' => 'delivering']);
        DB::table('orders')->where('status', 'livrée')->update(['status' => 'delivered']);
        DB::table('orders')->where('status', 'annulée')->update(['status' => 'canceled']);
    }
};
