<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    {
        // Table des catégories de filtres
        Schema::create('filter_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('checkbox');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table des options de filtres
        Schema::create('filter_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table de relation produits-filtres
        Schema::create('property_filter_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('filter_option_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['property_id', 'filter_option_id']);
        });
    }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('property_filter_values');
        Schema::dropIfExists('filter_options');
        Schema::dropIfExists('filter_categories');
    }
};
