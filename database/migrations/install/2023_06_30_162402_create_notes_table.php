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
        Schema::create('notes', function (Blueprint $table) {
            $table->id('note_id');
            $table->integer('note_owner')->nullable();
            $table->integer('note_time')->nullable();
            $table->tinyInteger('note_priority')->nullable();
            $table->string('note_title', 32)->nullable();
            $table->text('note_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
