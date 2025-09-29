<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des catégories de filtres
        Schema::create('filter_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug',191)->unique();
            $table->string('type')->default('checkbox');
            $table->json('entity_types')->nullable(); // 👈 supports multiple entities
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table des options de filtres
        Schema::create('filter_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug',191);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table polymorphe de relation entités-filtres
        Schema::create('entity_filter_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id'); // e.g. property_id or system_id
            $table->string('entity_type');           // App\Models\Property, App\Models\System, etc.
            $table->foreignId('filter_option_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['entity_id', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_filter_values');
        Schema::dropIfExists('filter_options');
        Schema::dropIfExists('filter_categories');
    }
};
