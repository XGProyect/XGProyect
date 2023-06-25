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
        Schema::create('bans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique('bans_user_id_unique');
            $table->unsignedBigInteger('admin_id');
            $table->text('details');
            $table->timestamp('until')->nullable();
            $table->timestamps();

            $table->foreign('admin_id', 'bans_admin_id_foreign')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('user_id', 'bans_user_id_foreign')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bans');
    }
};
