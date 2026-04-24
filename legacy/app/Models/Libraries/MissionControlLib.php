<?php

declare(strict_types=1);

namespace Xgp\App\Models\Libraries;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class MissionControlLib
{
    use PreparesLegacySql;

    public function getArrivingFleets()
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

    public function getReturningFleets()
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
}
