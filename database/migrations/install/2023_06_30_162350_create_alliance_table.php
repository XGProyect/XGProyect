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
        Schema::create('alliance', function (Blueprint $table) {
            $table->id('alliance_id');
            $table->string('alliance_name', 32)->nullable();
            $table->string('alliance_tag', 8)->nullable();
            $table->integer('alliance_owner')->default(0);
            $table->integer('alliance_register_time')->default(0);
            $table->text('alliance_description')->nullable();
            $table->string('alliance_web')->nullable();
            $table->text('alliance_text')->nullable();
            $table->string('alliance_image')->nullable();
            $table->text('alliance_request')->nullable();
            $table->boolean('alliance_request_notallow')->default(0);
            $table->text('alliance_ranks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alliance');
    }
};
