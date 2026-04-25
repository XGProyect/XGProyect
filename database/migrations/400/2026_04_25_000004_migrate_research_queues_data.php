<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // research_current_research = planet_id where the research is happening
        // planet_b_tech_id = which tech is being researched
        // planet_b_tech    = end timestamp
        // target_level is set to 0 for migrated rows; the service derives it
        // from current level + 1 for any item with target_level = 0.
        DB::table('research as r')
            ->join('planets as p', 'p.planet_id', '=', 'r.research_current_research')
            ->where('r.research_current_research', '!=', 0)
            ->where('p.planet_b_tech_id', '!=', 0)
            ->select([
                'r.research_user_id',
                'p.planet_id',
                'p.planet_b_tech_id',
                'p.planet_b_tech',
            ])
            ->orderBy('r.research_user_id')
            ->each(function (object $row): void {
                $endTime = (int) $row->planet_b_tech;
                $duration = max(0, $endTime - time());

                DB::table('research_queues')->insert([
                    'user_id'      => $row->research_user_id,
                    'planet_id'    => $row->planet_id,
                    'position'     => 1,
                    'tech_id'      => (int) $row->planet_b_tech_id,
                    'target_level' => 0,
                    'duration'     => $duration,
                    'end_time'     => $endTime,
                ]);
            });
    }

    public function down(): void
    {
        DB::table('research_queues')->delete();
    }
};
