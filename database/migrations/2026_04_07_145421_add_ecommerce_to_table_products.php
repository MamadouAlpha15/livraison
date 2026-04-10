<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : ajout des colonnes e-commerce sur la table products
 *
 * Lance avec : php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            /* ── Colonnes à ajouter seulement si elles n'existent pas ── */

            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category', 100)->nullable()->after('description');
            }

            if (!Schema::hasColumn('products', 'original_price')) {
                $table->decimal('original_price', 12, 2)->nullable()->after('price');
            }

            if (!Schema::hasColumn('products', 'stock')) {
                $table->unsignedInteger('stock')->default(0)->after('original_price');
            }

            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit', 30)->default('pièce')->after('stock');
            }

            if (!Schema::hasColumn('products', 'preparation_time')) {
                $table->unsignedSmallInteger('preparation_time')->nullable()->after('unit');
                /* Temps de préparation en minutes — utile pour les restaurants */
            }

            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('preparation_time');
            }

            if (!Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }

            if (!Schema::hasColumn('products', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('is_featured');
                /* Disponible aujourd'hui — pour les plats du jour (restaurant) */
            }

            if (!Schema::hasColumn('products', 'allergens')) {
                $table->string('allergens', 500)->nullable()->after('is_available');
            }

            if (!Schema::hasColumn('products', 'tags')) {
                $table->string('tags', 500)->nullable()->after('allergens');
            }

            if (!Schema::hasColumn('products', 'gallery')) {
                $table->json('gallery')->nullable()->after('image');
                /* JSON : tableau de chemins d'images supplémentaires */
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'category', 'original_price', 'stock', 'unit',
                'preparation_time', 'is_active', 'is_featured',
                'is_available', 'allergens', 'tags', 'gallery',
            ]);
        });
    }
};