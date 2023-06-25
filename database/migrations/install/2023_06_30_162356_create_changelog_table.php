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
        Schema::create('changelog', function (Blueprint $table) {
            $table->id('changelog_id');
            $table->integer('changelog_lang_id');
            $table->string('changelog_version', 16);
            $table->date('changelog_date');
            $table->text('changelog_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changelog');
    }
};
