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
        Schema::create('planets', function (Blueprint $table) {
            $table->id('planet_id');
            $table->string('planet_name')->nullable();
            $table->integer('planet_user_id')->nullable();
            $table->integer('planet_galaxy')->default(0);
            $table->integer('planet_system')->default(0);
            $table->integer('planet_planet')->default(0);
            $table->integer('planet_last_update')->nullable();
            $table->integer('planet_type')->default(1);
            $table->integer('planet_destroyed')->default(0);
            $table->integer('planet_b_building')->default(0);
            $table->text('planet_b_building_id');
            $table->integer('planet_b_tech')->default(0);
            $table->integer('planet_b_tech_id')->default(0);
            $table->integer('planet_b_hangar')->default(0);
            $table->text('planet_b_hangar_id')->nullable();
            $table->string('planet_image', 32)->default('normaltempplanet01');
            $table->integer('planet_diameter')->default(12800);
            $table->integer('planet_field_current')->default(0);
            $table->integer('planet_field_max')->default(163);
            $table->integer('planet_temp_min')->default(-17);
            $table->integer('planet_temp_max')->default(23);
            $table->double('planet_metal', 132, 8)->default(0.00000000);
            $table->integer('planet_metal_perhour')->default(0);
            $table->double('planet_crystal', 132, 8)->default(0.00000000);
            $table->integer('planet_crystal_perhour')->default(0);
            $table->double('planet_deuterium', 132, 8)->default(0.00000000);
            $table->integer('planet_deuterium_perhour')->default(0);
            $table->integer('planet_energy_used')->default(0);
            $table->bigInteger('planet_energy_max')->default(0);
            $table->integer('planet_building_metal_mine_percent')->default(10);
            $table->integer('planet_building_crystal_mine_percent')->default(10);
            $table->integer('planet_building_deuterium_sintetizer_percent')->default(10);
            $table->integer('planet_building_solar_plant_percent')->default(10);
            $table->integer('planet_building_fusion_reactor_percent')->default(10);
            $table->integer('planet_ship_solar_satellite_percent')->default(10);
            $table->integer('planet_last_jump_time')->default(0);
            $table->bigInteger('planet_debris_metal')->default(0);
            $table->bigInteger('planet_debris_crystal')->default(0);
            $table->integer('planet_invisible_start_time')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planets');
    }
};
