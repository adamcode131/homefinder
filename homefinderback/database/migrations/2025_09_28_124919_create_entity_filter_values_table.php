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
        Schema::create('entity_filter_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id',191); // e.g. property_id or system_id
            $table->string('entity_type',191)->nullable();           // App\Models\Property, App\Models\System, etc.
            $table->foreignId('filter_option_id',191)->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['entity_id', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entity_filter_values');
    }
};
