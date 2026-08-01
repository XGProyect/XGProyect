<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('planets', 'planet_b_building_id')) {
            return;
        }

        // TEXT NOT NULL with no default fails INSERTs that omit it under MySQL strict mode.
        Schema::table('planets', function (Blueprint $table): void {
            $table->text('planet_b_building_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('planets', 'planet_b_building_id')) {
            return;
        }

        DB::table('planets')
            ->whereNull('planet_b_building_id')
            ->update(['planet_b_building_id' => '0']);

        Schema::table('planets', function (Blueprint $table): void {
            $table->text('planet_b_building_id')->nullable(false)->change();
        });
    }
};
