<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlliancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alliances', function (Blueprint $table) {
            $table->increments('alliance_id');
            $table->string('alliance_name', 32)->nullable();
            $table->string('alliance_tag', 8)->nullable();
            $table->integer('alliance_owner')->default(0);
            $table->integer('alliance_register_time')->default(0);
            $table->text('alliance_description')->nullable();
            $table->string('alliance_web')->nullable();
            $table->text('alliance_text')->nullable();
            $table->string('alliance_image')->nullable();
            $table->text('alliance_request')->nullable();
            $table->tinyInteger('alliance_request_notallow')->default(0);
            $table->string('alliance_owner_range', 32)->nullable();
            $table->text('alliance_ranks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alliances');
    }
}
