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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->default('');
            $table->string('password', 64)->default('');
            $table->string('email')->unique('users_email_unique');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('authlevel')->default(0);
            $table->integer('home_planet_id')->unique('users_home_planet_id_unique');
            $table->integer('galaxy')->default(0);
            $table->integer('system')->default(0);
            $table->integer('planet')->default(0);
            $table->integer('current_planet')->unique('users_current_planet_unique');
            $table->string('lastip', 39)->default('');
            $table->string('ip_at_reg', 39)->default('');
            $table->text('agent')->nullable();
            $table->text('current_page')->nullable();
            $table->integer('register_time')->default(0);
            $table->integer('onlinetime')->default(0);
            $table->text('fleet_shortcuts')->nullable();
            $table->integer('ally_id')->default(0);
            $table->integer('ally_request')->default(0);
            $table->text('ally_request_text')->nullable();
            $table->integer('ally_register_time')->default(0);
            $table->integer('ally_rank_id')->default(0);
            $table->rememberToken();
            $table->timestamps();
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
};
