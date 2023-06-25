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
        Schema::create('alliance_statistics', function (Blueprint $table) {
            $table->unsignedBigInteger('alliance_statistic_alliance_id')->primary();
            $table->double('alliance_statistic_buildings_points', 132, 8)->default(0.00000000);
            $table->integer('alliance_statistic_buildings_old_rank')->default(0);
            $table->integer('alliance_statistic_buildings_rank')->default(0);
            $table->double('alliance_statistic_defenses_points', 132, 8)->default(0.00000000);
            $table->integer('alliance_statistic_defenses_old_rank')->default(0);
            $table->integer('alliance_statistic_defenses_rank')->default(0);
            $table->double('alliance_statistic_ships_points', 132, 8)->default(0.00000000);
            $table->integer('alliance_statistic_ships_old_rank')->default(0);
            $table->integer('alliance_statistic_ships_rank')->default(0);
            $table->double('alliance_statistic_technology_points', 132, 8)->default(0.00000000);
            $table->integer('alliance_statistic_technology_old_rank')->default(0);
            $table->integer('alliance_statistic_technology_rank')->default(0);
            $table->double('alliance_statistic_total_points', 132, 8)->default(0.00000000);
            $table->integer('alliance_statistic_total_old_rank')->default(0);
            $table->integer('alliance_statistic_total_rank')->default(0);
            $table->integer('alliance_statistic_update_time')->default(0);

            $table->foreign('alliance_statistic_alliance_id', 'alliance_statistics_alliance_statistic_alliance_id_foreign')->references('alliance_id')->on('alliance')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alliance_statistics');
    }
};
