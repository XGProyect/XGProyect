<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangelogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('changelog', function (Blueprint $table) {
            $table->increments('changelog_id');
            $table->integer('changelog_lang_id');
            $table->string('changelog_version', 16);
            $table->date('changelog_date');
            $table->text('changelog_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('changelog');
    }
}
