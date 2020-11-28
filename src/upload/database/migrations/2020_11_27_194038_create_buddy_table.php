<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuddyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buddy', function (Blueprint $table) {
            $table->increments('buddy_id');
            $table->unsignedInteger('buddy_sender')->index('user_buddy_sender');
            $table->unsignedInteger('buddy_receiver')->index('user_buddy_receiver');
            $table->boolean('buddy_status')->default(0);
            $table->text('buddy_request_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buddy');
    }
}
