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
        Schema::table('cached_results', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('properties_ids')->index();
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cached_results', function (Blueprint $table) {
            $table->dropColumn('latitude') ; 
            $table->dropColumn('longitude');
        });
    }
};
