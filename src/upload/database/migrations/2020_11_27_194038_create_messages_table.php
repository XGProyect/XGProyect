<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('message_id');
            $table->unsignedInteger('message_sender')->default(0)->index('user_message_sender');
            $table->unsignedInteger('message_receiver')->default(0)->index('user_message_receiver');
            $table->integer('message_time')->default(0);
            $table->integer('message_type')->default(0);
            $table->string('message_from', 128)->nullable();
            $table->text('message_subject')->nullable();
            $table->text('message_text')->nullable();
            $table->boolean('message_read')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
