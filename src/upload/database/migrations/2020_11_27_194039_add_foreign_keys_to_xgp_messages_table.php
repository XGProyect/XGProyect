<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('message_receiver', 'user_message_receiver')->references('user_id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('message_sender', 'user_message_sender')->references('user_id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign('user_message_receiver');
            $table->dropForeign('user_message_sender');
        });
    }
}
