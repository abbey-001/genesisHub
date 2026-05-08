<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds search-optimized fields to the products table.
     *
     * NEW COLUMNS:
     *  - tags              Comma-separated keywords sellers assign (e.g. "wireless, noise-cancelling")
     *  - search_keywords   Hidden synonyms / alternate spellings for broader matching
     *  - specifications    JSON key-value product specs (Weight, Material, Dimensions, etc.)
     *  - use_cases         Free-text: what the product is used for
     *  - target_audience   Structured audience label (men, women, kids, unisex, business)
     *  - variants          JSON variant groups: {"sizes":["S","M"],"colors":["Red","Blue"]}
     *  - search_vector     Denormalised text column — auto-populated on every save.
     *                      Used for MySQL FULLTEXT search.
     *  - meta_title        SEO title
     *  - meta_description  SEO description
     *  - model_number      SKU / model / part number for exact-match lookups
     *  - condition         new | used | refurbished
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('tags')->nullable()->after('slug');
            $table->text('search_keywords')->nullable()->after('tags');
            $table->json('specifications')->nullable()->after('search_keywords');
            $table->text('use_cases')->nullable()->after('specifications');
            $table->string('target_audience')->nullable()->after('use_cases');
            $table->json('variants')->nullable()->after('target_audience');
            $table->longText('search_vector')->nullable()->after('variants');
            $table->string('meta_title')->nullable()->after('search_vector');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('model_number')->nullable()->after('meta_description');
            $table->string('condition')->default('new')->after('model_number');

            $table->index('target_audience', 'idx_products_target_audience');
            $table->index('condition', 'idx_products_condition');
        });

        // FULLTEXT index on the denormalised search_vector for fast boolean-mode queries
        DB::statement('ALTER TABLE products ADD FULLTEXT INDEX ft_products_search_vector (search_vector)');
        // Separate FULLTEXT index on name + short_description for basic name/description search
        DB::statement('ALTER TABLE products ADD FULLTEXT INDEX ft_products_name_desc (name, short_description)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products DROP INDEX ft_products_search_vector');
        DB::statement('ALTER TABLE products DROP INDEX ft_products_name_desc');

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_target_audience');
            $table->dropIndex('idx_products_condition');
            $table->dropColumn([
                'tags', 'search_keywords', 'specifications',
                'use_cases', 'target_audience', 'variants',
                'search_vector', 'meta_title', 'meta_description',
                'model_number', 'condition',
            ]);
        });
    }
};