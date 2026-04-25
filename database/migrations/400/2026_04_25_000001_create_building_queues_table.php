<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('building_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('planet_id');
            $table->unsignedSmallInteger('position');
            $table->unsignedInteger('building_id');
            $table->integer('target_level');
            $table->enum('mode', ['build', 'destroy']);
            $table->unsignedInteger('duration');
            $table->unsignedInteger('end_time');

            $table->unique(['planet_id', 'position']);
            $table->foreign('planet_id')->references('planet_id')->on('planets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('building_queues');
    }
};
