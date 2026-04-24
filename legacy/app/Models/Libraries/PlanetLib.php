<?php

declare(strict_types=1);

namespace Xgp\App\Models\Libraries;

use App\Models\Planets;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class PlanetLib
{
    use PreparesLegacySql;

    public function checkPlanetExists(int $galaxy, int $system, int $position): bool
    {
        return Planets::where([
            'planet_galaxy' => $galaxy,
            'planet_system' => $system,
            'planet_planet' => $position,
        ])->first() !== null;
    }

    public function checkMoonExists(int $galaxy, int $system, int $position): ?array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT pm2.`planet_id`,
                    pm2.`planet_name`,
                    pm2.`planet_temp_max`,
                    pm2.`planet_temp_min`,
                    (
                        SELECT
                            pm.`planet_id` AS `id_moon`
                        FROM `' . PLANETS . "` AS pm
                            WHERE pm.`planet_galaxy` = '" . $galaxy . "' AND
                                    pm.`planet_system` = '" . $system . "' AND
                                    pm.`planet_planet` = '" . $position . "' AND
                                    pm.`planet_type` = 3) AS `id_moon`
                    FROM `" . PLANETS . "` AS pm2
                    WHERE pm2.`planet_galaxy` = '" . $galaxy . "' AND
                            pm2.`planet_system` = '" . $system . "' AND
                            pm2.`planet_planet` = '" . $position . "';"
            )
        );

        return $row !== null ? (array) $row : null;
    }

    public function createNewPlanet(array $data, bool $full_insert = true): void
    {
        if (is_array($data)) {
            $insert_query = 'INSERT INTO `' . PLANETS . '` SET ';

            foreach ($data as $column => $value) {
                $insert_query .= '`' . $column . "` = '" . $value . "', ";
            }

            $insert_query = substr_replace($insert_query, '', -2) . ';';

            DB::statement($this->prepareSql($insert_query));

            if ($full_insert) {
                $planet_id = (int) DB::getPdo()->lastInsertId();

                $this->insertPlanetBuildings($planet_id);
                $this->insertPlanetDefenses($planet_id);
                $this->insertPlanetShips($planet_id);
            }
        }
    }

    private function insertPlanetBuildings(int $planet_id): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO `' . BUILDINGS . "` SET `building_planet_id` = '" . $planet_id . "';"
            )
        );
    }

    private function insertPlanetDefenses(int $planet_id): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO `' . DEFENSES . "` SET `defense_planet_id` = '" . $planet_id . "';"
            )
        );
    }

    private function insertPlanetShips(int $planet_id): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO `' . SHIPS . "` SET `ship_planet_id` = '" . $planet_id . "';"
            )
        );
    }
}
