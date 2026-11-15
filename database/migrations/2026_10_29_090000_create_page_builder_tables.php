<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Constructeur de page produit (sections et blocs, à la manière d'un thème Shopify) :
 *   - product_pages         : la page d'un produit, brouillon et version publiée (JSON façon product.json) ;
 *   - product_page_versions : les dernières publications, pour revenir en arrière ;
 *   - page_templates        : modèles de page (fournis par Shopio si shop_id est vide, ou enregistrés par un vendeur) ;
 *   - order_items.options   : ce que le client a choisi sur la page (palier, lot, cadeau, champs personnalisés).
 *
 * Forme du JSON d'une page :
 *   { "sections": { "<id>": { "type": "…", "settings": {…}, "blocks": { "<id>": { "type": "…", "settings": {…}, "disabled": false } },
 *                             "block_order": ["<id>", …], "disabled": false } },
 *     "order": ["<id>", …] }
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete(); // vide : modèle fourni par Shopio
            $table->string('key', 40)->nullable()->unique();                            // modèles Shopio : « classique », « mode »…
            $table->string('name', 80);
            $table->string('category', 30)->default('general');                       // general | mode | beaute | electronique | unique
            $table->string('description')->nullable();
            $table->json('data');
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['shop_id', 'is_active']);
        });

        Schema::create('product_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();           // pour les droits et les recherches par boutique
            $table->json('draft')->nullable();                                         // ce que le vendeur modifie
            $table->json('published')->nullable();                                     // ce que voient les clients (vide : page actuelle)
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('version')->default(0);                            // numéro de la dernière publication
            $table->foreignId('page_template_id')->nullable()->constrained()->nullOnDelete(); // modèle d'origine
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['shop_id', 'published_at']);
        });

        Schema::create('product_page_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_page_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('data');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['product_page_id', 'version']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->json('options')->nullable()->after('variant_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('options'));
        Schema::dropIfExists('product_page_versions');
        Schema::dropIfExists('product_pages');
        Schema::dropIfExists('page_templates');
    }
};
