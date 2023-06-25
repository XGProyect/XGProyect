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
        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->integer('message_sender')->default(0);
            $table->integer('message_receiver')->default(0);
            $table->integer('message_time')->default(0);
            $table->integer('message_type')->default(0);
            $table->string('message_from', 128)->nullable();
            $table->text('message_subject')->nullable();
            $table->text('message_text')->nullable();
            $table->unsignedTinyInteger('message_read')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
