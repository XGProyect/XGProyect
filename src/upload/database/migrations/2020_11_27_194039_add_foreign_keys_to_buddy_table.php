<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToBuddyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('buddies', function (Blueprint $table) {
            $table->foreign('buddy_receiver', 'user_buddy_receiver')->references('user_id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('buddy_sender', 'user_buddy_sender')->references('user_id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buddies', function (Blueprint $table) {
            $table->dropForeign('user_buddy_receiver');
            $table->dropForeign('user_buddy_sender');
        });
    }
}
