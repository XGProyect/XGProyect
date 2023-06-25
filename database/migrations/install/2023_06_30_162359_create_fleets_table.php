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
        Schema::create('fleets', function (Blueprint $table) {
            $table->id('fleet_id');
            $table->integer('fleet_owner')->default(0);
            $table->integer('fleet_mission')->default(0);
            $table->bigInteger('fleet_amount')->default(0);
            $table->text('fleet_array')->nullable();
            $table->integer('fleet_start_time')->default(0);
            $table->integer('fleet_start_galaxy')->default(0);
            $table->integer('fleet_start_system')->default(0);
            $table->integer('fleet_start_planet')->default(0);
            $table->integer('fleet_start_type')->default(0);
            $table->integer('fleet_end_time')->default(0);
            $table->integer('fleet_end_stay')->default(0);
            $table->integer('fleet_end_galaxy')->default(0);
            $table->integer('fleet_end_system')->default(0);
            $table->integer('fleet_end_planet')->default(0);
            $table->integer('fleet_end_type')->default(0);
            $table->integer('fleet_target_obj')->default(0);
            $table->bigInteger('fleet_resource_metal')->default(0);
            $table->bigInteger('fleet_resource_crystal')->default(0);
            $table->bigInteger('fleet_resource_deuterium')->default(0);
            $table->bigInteger('fleet_fuel')->default(0);
            $table->integer('fleet_target_owner')->default(0);
            $table->string('fleet_group', 15)->default('0');
            $table->boolean('fleet_mess')->default(0);
            $table->integer('fleet_creation')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleets');
    }
};
