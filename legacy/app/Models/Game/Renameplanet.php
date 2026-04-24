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
class Renameplanet
{
    use PreparesLegacySql;

    public function getFleets(int $userId, int $galaxy, int $system, int $planet): ?array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        `fleet_owner`,
                        `fleet_target_owner`,
                        `fleet_end_type`,
                        `fleet_mess`
                    FROM `' . FLEETS . "`
                    WHERE (
                            fleet_owner = '" . $userId . "' AND
                            fleet_start_galaxy = '" . $galaxy . "' AND
                            fleet_start_system = '" . $system . "' AND
                            fleet_start_planet = '" . $planet . "'
                    )
                    OR
                    (
                        fleet_target_owner = '" . $userId . "' AND
                        fleet_end_galaxy = '" . $galaxy . "' AND
                        fleet_end_system = '" . $system . "' AND
                        fleet_end_planet = '" . $planet . "'
                    )"
                )
            )
        );
    }

    public function deleteMoonAndPlanet(int $userId, int $planet_id, int $galaxy, int $system, int $planet): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PLANETS . '` AS p, `' . PLANETS . '` AS m, `' . USERS . "` AS u SET
                    p.`planet_destroyed` = '" . (time() + (PLANETS_LIFE_TIME * 3600)) . "',
                    m.`planet_destroyed` = '" . (time() + (PLANETS_LIFE_TIME * 3600)) . "',
                    u.`current_planet` = u.`home_planet_id`
                WHERE p.`planet_id` = '" . $planet_id . "' AND
                    m.`planet_galaxy` = '" . $galaxy . "' AND
                    m.`planet_system` = '" . $system . "' AND
                    m.`planet_planet` = '" . $planet . "' AND
                    m.`planet_type` = '3' AND
                    u.`id` = '" . $userId . "';"
            )
        );
    }

    public function deletePlanet(int $userId, int $planet_id): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PLANETS . '` AS p, `' . USERS . "` AS u SET
                    p.`planet_destroyed` = '" . (time() + (PLANETS_LIFE_TIME * 3600)) . "',
                    u.`current_planet` = u.`home_planet_id`
                WHERE p.`planet_id` = '" . $planet_id . "' AND
                    u.`id` = '" . $userId . "';"
            )
        );
    }

    public function updatePlanetName(string $new_name, int $planet_id): void
    {
        DB::statement(
            $this->prepareSql('UPDATE `' . PLANETS . '` SET `planet_name` = ? WHERE `planet_id` = ? LIMIT 1;'),
            [$new_name, $planet_id]
        );
    }
}
