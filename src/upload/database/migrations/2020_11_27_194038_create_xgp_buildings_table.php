<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->increments('building_id');
            $table->unsignedInteger('building_planet_id')->unique('building_planet_id');
            $table->integer('building_metal_mine')->default(0);
            $table->integer('building_crystal_mine')->default(0);
            $table->integer('building_deuterium_sintetizer')->default(0);
            $table->integer('building_solar_plant')->default(0);
            $table->integer('building_fusion_reactor')->default(0);
            $table->integer('building_robot_factory')->default(0);
            $table->integer('building_nano_factory')->default(0);
            $table->integer('building_hangar')->default(0);
            $table->integer('building_metal_store')->default(0);
            $table->integer('building_crystal_store')->default(0);
            $table->integer('building_deuterium_tank')->default(0);
            $table->integer('building_laboratory')->default(0);
            $table->integer('building_terraformer')->default(0);
            $table->integer('building_ally_deposit')->default(0);
            $table->integer('building_missile_silo')->default(0);
            $table->integer('building_mondbasis')->default(0);
            $table->integer('building_phalanx')->default(0);
            $table->integer('building_jump_gate')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buildings');
    }
}
