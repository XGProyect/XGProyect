<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banned', function (Blueprint $table) {
            $table->bigInteger('banned_id', true);
            $table->string('banned_who', 64)->default('');
            $table->text('banned_theme');
            $table->integer('banned_time')->default(0);
            $table->integer('banned_longer')->default(0);
            $table->string('banned_author', 64)->default('');
            $table->string('banned_email', 64)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banned');
    }
}
