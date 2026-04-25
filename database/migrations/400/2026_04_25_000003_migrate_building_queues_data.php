<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('planets')
            ->whereNotNull('planet_b_building_id')
            ->where('planet_b_building_id', '!=', '')
            ->where('planet_b_building_id', '!=', '0')
            ->orderBy('planet_id')
            ->each(function (object $planet): void {
                $items = array_values(array_filter(explode(';', $planet->planet_b_building_id)));

                foreach ($items as $index => $item) {
                    $parts = explode(',', $item);

                    if (count($parts) < 5) {
                        continue;
                    }

                    DB::table('building_queues')->insert([
                        'planet_id'    => $planet->planet_id,
                        'position'     => $index + 1,
                        'building_id'  => (int) $parts[0],
                        'target_level' => (int) $parts[1],
                        'duration'     => (int) $parts[2],
                        'end_time'     => (int) $parts[3],
                        'mode'         => $parts[4] === 'destroy' ? 'destroy' : 'build',
                    ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('building_queues')->delete();
    }
};
