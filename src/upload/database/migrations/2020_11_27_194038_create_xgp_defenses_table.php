<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('defenses', function (Blueprint $table) {
            $table->increments('defense_id');
            $table->unsignedInteger('defense_planet_id')->unique('defense_planet_id');
            $table->integer('defense_rocket_launcher')->default(0);
            $table->integer('defense_light_laser')->default(0);
            $table->integer('defense_heavy_laser')->default(0);
            $table->integer('defense_ion_cannon')->default(0);
            $table->integer('defense_gauss_cannon')->default(0);
            $table->integer('defense_plasma_turret')->default(0);
            $table->integer('defense_small_shield_dome')->default(0);
            $table->integer('defense_large_shield_dome')->default(0);
            $table->integer('defense_anti-ballistic_missile')->default(0);
            $table->integer('defense_interplanetary_missile')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('defenses');
    }
}
