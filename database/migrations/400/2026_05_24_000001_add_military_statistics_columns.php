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
        $this->addMilitaryColumns('users_statistics', 'user_statistic');
        $this->addMilitaryColumns('alliance_statistics', 'alliance_statistic');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropMilitaryColumns('users_statistics', 'user_statistic');
        $this->dropMilitaryColumns('alliance_statistics', 'alliance_statistic');
    }

    private function addMilitaryColumns(string $tableName, string $prefix): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $prefix): void {
            if (!Schema::hasColumn($tableName, $prefix . '_military_points')) {
                $table->double($prefix . '_military_points')->default(0.00000000);
            }

            if (!Schema::hasColumn($tableName, $prefix . '_military_old_rank')) {
                $table->integer($prefix . '_military_old_rank')->default(0);
            }

            if (!Schema::hasColumn($tableName, $prefix . '_military_rank')) {
                $table->integer($prefix . '_military_rank')->default(0);
            }
        });
    }

    private function dropMilitaryColumns(string $tableName, string $prefix): void
    {
        $columns = array_filter([
            $prefix . '_military_points',
            $prefix . '_military_old_rank',
            $prefix . '_military_rank',
        ], static fn (string $column): bool => Schema::hasColumn($tableName, $column));

        if ($columns === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
