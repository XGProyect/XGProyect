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
        Schema::table('bans', function (Blueprint $table) {
            $table->renameColumn('banned_id', 'id');
            $table->bigInteger('user_id')->unsigned()->unique();
            $table->bigInteger('admin_id')->unsigned();
            $table->text('details');
            $table->timestamp('until')->nullable();
            $table->dropColumn('banned_email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
