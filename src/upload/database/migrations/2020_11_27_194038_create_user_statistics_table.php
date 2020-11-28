<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_statistics', function (Blueprint $table) {
            $table->increments('user_statistic_id');
            $table->unsignedInteger('user_statistic_user_id')->unique('user_statistic_user_id');
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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_statistics');
    }
}
