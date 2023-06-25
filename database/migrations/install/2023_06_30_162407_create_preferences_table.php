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
        Schema::create('preferences', function (Blueprint $table) {
            $table->id('preference_id');
            $table->integer('preference_user_id')->unique('preference_user_id');
            $table->integer('preference_nickname_change')->nullable();
            $table->boolean('preference_spy_probes')->default(1);
            $table->boolean('preference_planet_sort')->default(0);
            $table->boolean('preference_planet_sort_sequence')->default(0);
            $table->integer('preference_vacation_mode')->nullable();
            $table->integer('preference_delete_mode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferences');
    }
};
