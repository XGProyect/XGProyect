<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('research_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('planet_id');
            $table->unsignedSmallInteger('position');
            $table->unsignedInteger('tech_id');
            $table->unsignedInteger('target_level');
            $table->unsignedInteger('duration');
            $table->unsignedInteger('end_time');

            $table->unique(['user_id', 'position']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('planet_id')->references('planet_id')->on('planets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_queues');
    }
};
