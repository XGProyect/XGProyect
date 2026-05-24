<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('ships', 'ship_battlecruiser') || Schema::hasColumn('ships', 'ship_reaper')) {
            return;
        }

        Schema::table('ships', function (Blueprint $table): void {
            $table->renameColumn('ship_battlecruiser', 'ship_reaper');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('ships', 'ship_reaper') || Schema::hasColumn('ships', 'ship_battlecruiser')) {
            return;
        }

        Schema::table('ships', function (Blueprint $table): void {
            $table->renameColumn('ship_reaper', 'ship_battlecruiser');
        });
    }
};
