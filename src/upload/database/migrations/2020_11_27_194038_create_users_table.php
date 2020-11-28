<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('user_name', 64)->default('');
            $table->string('user_password', 64)->default('');
            $table->string('user_email', 64)->default('');
            $table->tinyInteger('user_authlevel')->default(0);
            $table->integer('user_current_planet')->default(0);
            $table->string('user_lastip', 39)->default('');
            $table->string('user_ip_at_reg', 39)->default('');
            $table->text('user_agent')->nullable();
            $table->text('user_current_page')->nullable();
            $table->integer('user_register_time')->default(0);
            $table->integer('user_onlinetime')->default(0);
            $table->text('user_fleet_shortcuts')->nullable();
            $table->integer('user_ally_id')->default(0);
            $table->integer('user_ally_request')->default(0);
            $table->text('user_ally_request_text')->nullable();
            $table->integer('user_ally_register_time')->default(0);
            $table->integer('user_ally_rank_id')->default(0);
            $table->integer('user_banned')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
