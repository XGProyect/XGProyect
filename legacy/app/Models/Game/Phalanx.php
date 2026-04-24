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
class Phalanx
{
    use PreparesLegacySql;

    public function reduceDeuterium(int $planet_id): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PLANETS . "` SET
                    `planet_deuterium` = `planet_deuterium` - '" . PHALANX_COST . "'
                WHERE `planet_id` = '" . $planet_id . "';"
            )
        );
    }

    public function getTargetPlanetIdAndName(int $galaxy, int $system, int $planet): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    `planet_name`,
                    `planet_user_id`
                FROM `' . PLANETS . "`
                WHERE `planet_galaxy` = '" . $galaxy . "' AND
                        `planet_system` = '" . $system . "' AND
                        `planet_planet` = '" . $planet . "' AND
                        `planet_type` = 1"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function getTargetMoonStatus(int $galaxy, int $system, int $planet): ?array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    `planet_destroyed`
                FROM `' . PLANETS . "`
                WHERE `planet_galaxy` = '" . $galaxy . "' AND
                        `planet_system` = '" . $system . "' AND
                        `planet_planet` = '" . $planet . "' AND
                        `planet_type` = 3 "
            )
        );

        return $row !== null ? (array) $row : null;
    }

    public function getFleetsToTarget(int $galaxy, int $system, int $planet): ?array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        f.*,
                        po.`planet_name` AS `start_planet_name`,
                        pt.`planet_name` AS `target_planet_name`,
                        uo.`name` AS `start_planet_user`,
                        ut.`name` AS `target_planet_user`
                    FROM `' . FLEETS . '` f
                        INNER JOIN `' . USERS . '` uo
                            ON uo.`id` = f.`fleet_owner`
                        LEFT JOIN `' . USERS . '` ut
                            ON ut.`id` = f.`fleet_target_owner`
                        INNER JOIN `' . PLANETS . '` po
                            ON (
                                po.planet_galaxy = f.fleet_start_galaxy AND
                                po.planet_system = f.fleet_start_system AND
                                po.planet_planet = f.fleet_start_planet AND
                                po.planet_type = f.fleet_start_type
                            )
                        LEFT JOIN `' . PLANETS . "` pt
                            ON (
                            pt.planet_galaxy = f.fleet_end_galaxy AND
                            pt.planet_system = f.fleet_end_system AND
                            pt.planet_planet = f.fleet_end_planet AND
                            pt.planet_type = f.fleet_end_type
                        )
                        WHERE (
                            (
                                f.`fleet_start_galaxy` = '" . $galaxy . "' AND
                                f.`fleet_start_system` = '" . $system . "' AND
                                f.`fleet_start_planet` = '" . $planet . "'
                            )
                            OR
                            (
                                f.`fleet_end_galaxy` = '" . $galaxy . "' AND
                                f.`fleet_end_system` = '" . $system . "' AND
                                f.`fleet_end_planet` = '" . $planet . "'
                            )
                        ) ;"
                )
            )
        );
    }
}
