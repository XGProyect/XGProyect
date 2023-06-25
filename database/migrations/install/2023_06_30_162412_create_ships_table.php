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
        Schema::create('ships', function (Blueprint $table) {
            $table->id('ship_id');
            $table->unsignedInteger('ship_planet_id')->unique('ship_planet_id');
            $table->integer('ship_small_cargo_ship')->default(0);
            $table->integer('ship_big_cargo_ship')->default(0);
            $table->integer('ship_light_fighter')->default(0);
            $table->integer('ship_heavy_fighter')->default(0);
            $table->integer('ship_cruiser')->default(0);
            $table->integer('ship_battleship')->default(0);
            $table->integer('ship_colony_ship')->default(0);
            $table->integer('ship_recycler')->default(0);
            $table->integer('ship_espionage_probe')->default(0);
            $table->integer('ship_bomber')->default(0);
            $table->integer('ship_solar_satellite')->default(0);
            $table->integer('ship_destroyer')->default(0);
            $table->integer('ship_deathstar')->default(0);
            $table->integer('ship_battlecruiser')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ships');
    }
};
