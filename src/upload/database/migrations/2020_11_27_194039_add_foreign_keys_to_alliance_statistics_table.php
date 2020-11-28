<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAllianceStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alliance_statistics', function (Blueprint $table) {
            $table->foreign('alliance_statistic_alliance_id', 'alliance_statistic')->references('alliance_id')->on('alliances')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alliance_statistics', function (Blueprint $table) {
            $table->dropForeign('alliance_statistic');
        });
    }
}
