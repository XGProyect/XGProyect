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
        Schema::create('research', function (Blueprint $table) {
            $table->id('research_id');
            $table->unsignedInteger('research_user_id')->unique('research_user_id');
            $table->integer('research_current_research')->default(0);
            $table->integer('research_espionage_technology')->default(0);
            $table->integer('research_computer_technology')->default(0);
            $table->integer('research_weapons_technology')->default(0);
            $table->integer('research_shielding_technology')->default(0);
            $table->integer('research_armour_technology')->default(0);
            $table->integer('research_energy_technology')->default(0);
            $table->integer('research_hyperspace_technology')->default(0);
            $table->integer('research_combustion_drive')->default(0);
            $table->integer('research_impulse_drive')->default(0);
            $table->integer('research_hyperspace_drive')->default(0);
            $table->integer('research_laser_technology')->default(0);
            $table->integer('research_ionic_technology')->default(0);
            $table->integer('research_plasma_technology')->default(0);
            $table->integer('research_intergalactic_research_network')->default(0);
            $table->integer('research_astrophysics')->default(0);
            $table->integer('research_graviton_technology')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research');
    }
};
