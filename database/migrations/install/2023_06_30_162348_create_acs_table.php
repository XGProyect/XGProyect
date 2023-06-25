<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acs', function (Blueprint $table) {
            $table->id('acs_id');
            $table->string('acs_name', 50)->nullable()->unique('acs_name');
            $table->integer('acs_owner')->default(0);
            $table->integer('acs_galaxy')->nullable();
            $table->integer('acs_system')->nullable();
            $table->integer('acs_planet')->nullable();
            $table->tinyInteger('acs_planet_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acs');
    }
};
