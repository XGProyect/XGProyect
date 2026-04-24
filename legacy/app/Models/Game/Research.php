<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Research
{
    use PreparesLegacySql;

    public function startNewResearch(array $working_planet, array $current_user): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PLANETS . '` AS p, `' . RESEARCH . "` AS r SET
                    p.`planet_b_tech_id` = '" . $working_planet['planet_b_tech_id'] . "',
                    p.`planet_b_tech` = '" . $working_planet['planet_b_tech'] . "',
                    p.`planet_metal` = '" . $working_planet['planet_metal'] . "',
                    p.`planet_crystal` = '" . $working_planet['planet_crystal'] . "',
                    p.`planet_deuterium` = '" . $working_planet['planet_deuterium'] . "',
                    r.`research_current_research` = '" . $current_user['research_current_research'] . "'
                WHERE p.`planet_id` = '" . $working_planet['planet_id'] . "'
                    AND r.`research_user_id` = '" . $current_user['id'] . "';"
            )
        );
    }

    public function getPlanetResearching(int $current_research): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    `planet_id`,
                    `planet_name`,
                    `planet_b_tech`,
                    `planet_b_tech_id`,
                    `planet_galaxy`,
                    `planet_system`,
                    `planet_planet`
                FROM `' . PLANETS . "`
                WHERE `planet_id` = '" . $current_research . "';"
            )
        );

        return $row !== null ? (array) $row : [];
    }
}
