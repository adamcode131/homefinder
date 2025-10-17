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
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('cascade');
                $table->integer('added_points')->nullable();
                $table->integer('deducted_points')->nullable();
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
        Schema::dropIfExists('notifications');
    }
};
