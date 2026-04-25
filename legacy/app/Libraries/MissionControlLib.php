<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class MissionControlLib
{
    use PreparesLegacySql;

    public function arrivingFleets(): void
    {
        $this->processMissions($this->getArrivingFleets());
    }

    public function returningFleets(): void
    {
        $this->processMissions($this->getReturningFleets());
    }

    private function getArrivingFleets(): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        f.*,
                        sp.`planet_name` AS `planet_start_name`,
                        ep.`planet_name` AS `planet_end_name`,
                        sr.`research_hyperspace_technology`
                    FROM `' . FLEETS . '` f
                    LEFT JOIN `' . PLANETS . '` sp
                        ON (sp.`planet_galaxy` = f.`fleet_start_galaxy` AND
                            sp.`planet_system` = f.`fleet_start_system` AND
                            sp.`planet_planet` = f.`fleet_start_planet` AND
                            sp.`planet_type` = f.`fleet_start_type`
                    )
                    LEFT JOIN `' . RESEARCH . '` sr
                        ON sr.`research_user_id` = f.`fleet_owner`
                    LEFT JOIN `' . PLANETS . "` ep
                        ON (ep.`planet_galaxy` = f.`fleet_end_galaxy` AND
                            ep.`planet_system` = f.`fleet_end_system` AND
                            ep.`planet_planet` = f.`fleet_end_planet` AND
                            ep.`planet_type` = f.`fleet_end_type`
                    )
                    WHERE f.`fleet_start_time` <= '" . time() . "'
                        AND f.`fleet_mess` = '0'
                    GROUP BY f.`fleet_id`, sp.`planet_name`, ep.`planet_name`
                    ORDER BY f.`fleet_id` ASC"
                )
            )
        );
    }

    private function getReturningFleets(): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        f.*,
                        sp.`planet_name` AS `planet_start_name`,
                        ep.`planet_name` AS `planet_end_name`,
                        sr.`research_hyperspace_technology`
                    FROM `' . FLEETS . '` f
                    LEFT JOIN `' . PLANETS . '` sp
                        ON (sp.`planet_galaxy` = f.`fleet_start_galaxy` AND
                            sp.`planet_system` = f.`fleet_start_system` AND
                            sp.`planet_planet` = f.`fleet_start_planet` AND
                            sp.`planet_type` = f.`fleet_start_type`
                    )
                    LEFT JOIN `' . RESEARCH . '` sr
                        ON sr.`research_user_id` = f.`fleet_owner`
                    LEFT JOIN `' . PLANETS . "` ep
                        ON (ep.`planet_galaxy` = f.`fleet_end_galaxy` AND
                            ep.`planet_system` = f.`fleet_end_system` AND
                            ep.`planet_planet` = f.`fleet_end_planet` AND
                            ep.`planet_type` = f.`fleet_end_type`
                    )
                    WHERE f.`fleet_end_time` <= '" . time() . "'
                        AND f.`fleet_mess` <> '0'
                    GROUP BY f.`fleet_id`, sp.`planet_name`, ep.`planet_name`
                    ORDER BY f.`fleet_id` ASC"
                )
            )
        );
    }

    private function processMissions(array $allFleets = []): void
    {
        // validate
        if (!is_array($allFleets) or empty($allFleets)) {
            return;
        }

        // missions list
        $missions = [
            1 => 'Attack',
            2 => 'Acs',
            3 => 'Transport',
            4 => 'Deploy',
            5 => 'Stay',
            6 => 'Spy',
            7 => 'Colonize',
            8 => 'Recycle',
            9 => 'Destroy',
            10 => 'Missile',
            15 => 'Expedition',
        ];

        // Process missions
        foreach ($allFleets as $fleet) {
            $name = $missions[$fleet['fleet_mission']];
            $mission_name = $name . 'Mission';
            $class_name = 'Xgp\App\Libraries\Missions\\' . $name;

            $mission = app($class_name);
            $mission->$mission_name($fleet);
        }
    }
}
