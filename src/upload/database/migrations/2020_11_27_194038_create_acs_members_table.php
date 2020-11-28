<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcsMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acs_members', function (Blueprint $table) {
            $table->increments('acs_member_id');
            $table->unsignedInteger('acs_group_id');
            $table->unsignedInteger('acs_user_id');
            $table->unique(['acs_group_id', 'acs_user_id'], 'acs_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acs_members');
    }
}
