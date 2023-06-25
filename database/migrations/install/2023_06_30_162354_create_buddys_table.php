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
        Schema::create('buddys', function (Blueprint $table) {
            $table->id('buddy_id');
            $table->unsignedInteger('buddy_sender');
            $table->unsignedInteger('buddy_receiver');
            $table->boolean('buddy_status')->default(0);
            $table->text('buddy_request_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buddys');
    }
};
