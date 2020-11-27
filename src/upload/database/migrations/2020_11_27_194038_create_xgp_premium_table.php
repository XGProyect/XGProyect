<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePremiumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium', function (Blueprint $table) {
            $table->increments('premium_id');
            $table->unsignedInteger('premium_user_id')->unique('premium_user_id');
            $table->integer('premium_dark_matter')->default(0);
            $table->integer('premium_officier_commander')->default(0);
            $table->integer('premium_officier_admiral')->default(0);
            $table->integer('premium_officier_engineer')->default(0);
            $table->integer('premium_officier_geologist')->default(0);
            $table->integer('premium_officier_technocrat')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('premium');
    }
}
