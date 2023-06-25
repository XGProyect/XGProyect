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
        Schema::create('users_statistics', function (Blueprint $table) {
            $table->unsignedBigInteger('user_statistic_user_id')->primary();
            $table->double('user_statistic_buildings_points', 132, 8)->default(0.00000000);
            $table->integer('user_statistic_buildings_old_rank')->default(0);
            $table->integer('user_statistic_buildings_rank')->default(0);
            $table->double('user_statistic_defenses_points', 132, 8)->default(0.00000000);
            $table->integer('user_statistic_defenses_old_rank')->default(0);
            $table->integer('user_statistic_defenses_rank')->default(0);
            $table->double('user_statistic_ships_points', 132, 8)->default(0.00000000);
            $table->integer('user_statistic_ships_old_rank')->default(0);
            $table->integer('user_statistic_ships_rank')->default(0);
            $table->double('user_statistic_technology_points', 132, 8)->default(0.00000000);
            $table->integer('user_statistic_technology_old_rank')->default(0);
            $table->integer('user_statistic_technology_rank')->default(0);
            $table->double('user_statistic_total_points', 132, 8)->default(0.00000000);
            $table->integer('user_statistic_total_old_rank')->default(0);
            $table->integer('user_statistic_total_rank')->default(0);
            $table->integer('user_statistic_update_time')->default(0);

            $table->foreign('user_statistic_user_id', 'users_statistics_user_statistic_user_id_foreign')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_statistics');
    }
};
