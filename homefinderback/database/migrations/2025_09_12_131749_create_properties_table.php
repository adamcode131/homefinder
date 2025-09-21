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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type',['Appartement','Villa','Maison','Studio','Duplex','Terrain','Bureau','Local Commercial','Chambre']); ; 
            $table->foreignId('ville_id')->constrained()->onDelete('cascade');
            $table->foreignId('quartier_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('description'); 
            $table->enum('intention', ['vente', 'loyer']);
            $table->integer('sale_price'); 
            $table->integer('rent_price'); 
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_validated')->default(false) ; 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('properties');
    }
};
