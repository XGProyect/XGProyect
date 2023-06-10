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
        Schema::table('alliance', function (Blueprint $table) {
            $table->bigInteger('alliance_id')->change();
        });

        Schema::table('alliance', function (Blueprint $table) {
            $table->dropPrimary('alliance_id');
        });

        Schema::table('alliance', function (Blueprint $table) {
            $table->bigInteger('alliance_id')->unsigned()->autoIncrement()->change();
        });

        Schema::table('alliance_statistics', function (Blueprint $table) {
            $table
                ->bigInteger('alliance_statistic_alliance_id')
                ->unsigned()
                ->change();
            $table
                ->foreign('alliance_statistic_alliance_id')
                ->references('alliance_id')
                ->on('alliance')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
