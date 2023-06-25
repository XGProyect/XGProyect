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
        Schema::create('premium', function (Blueprint $table) {
            $table->unsignedInteger('premium_user_id')->unique('premium_user_id');
            $table->integer('premium_dark_matter')->default(0);
            $table->integer('premium_officier_commander')->default(0);
            $table->integer('premium_officier_admiral')->default(0);
            $table->integer('premium_officier_engineer')->default(0);
            $table->integer('premium_officier_geologist')->default(0);
            $table->integer('premium_officier_technocrat')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premium');
    }
};
