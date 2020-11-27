<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acs', function (Blueprint $table) {
            $table->bigIncrements('acs_id');
            $table->string('acs_name', 50)->nullable()->unique('acs_name');
            $table->integer('acs_owner')->default(0);
            $table->integer('acs_galaxy')->nullable();
            $table->integer('acs_system')->nullable();
            $table->integer('acs_planet')->nullable();
            $table->boolean('acs_planet_type')->nullable();
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
}
