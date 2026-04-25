<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use App\Models\Planets;
use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class PlanetLib
{
    use PreparesLegacySql;

    /**
     * @param int     $galaxy   Galaxy
     * @param int     $system   System
     * @param int     $position Position
     * @param int     $owner    Planet owner Id
     * @param string  $name     Planet name
     * @param boolean $main     Main planet
     *
     */
    public function setNewPlanet($galaxy, $system, $position, $owner, $name = '', $main = false): bool
    {
        $planetExists = Planets::where([
            'planet_galaxy' => $galaxy,
            'planet_system' => $system,
            'planet_planet' => $position,
        ])->first() !== null;

        if (!$planetExists) {
            $planet = Formulas::getPlanetSize($position, $main);
            $temp = Formulas::setPlanetTemp($position);
            $name = ($name == '') ? __('game/global.colony') : $name;

            if ($main == true) {
                $name = __('game/global.homeworld');
            }

            $newPlanet = Planets::create([
                'planet_name' => $name,
                'planet_user_id' => $owner,
                'planet_galaxy' => $galaxy,
                'planet_system' => $system,
                'planet_planet' => $position,
                'planet_last_update' => time(),
                'planet_type' => PlanetTypesEnumerator::PLANET,
                'planet_image' => Formulas::setPlanetImage($system, $position),
                'planet_diameter' => $planet['planet_diameter'],
                'planet_field_max' => $planet['planet_field_max'],
                'planet_temp_min' => $temp['min'],
                'planet_temp_max' => $temp['max'],
                'planet_metal' => BUILD_METAL,
                'planet_metal_perhour' => app(SettingsService::class)->getInt('metal_basic_income'),
                'planet_crystal' => BUILD_CRISTAL,
                'planet_crystal_perhour' => app(SettingsService::class)->getInt('crystal_basic_income'),
                'planet_deuterium' => BUILD_DEUTERIUM,
                'planet_deuterium_perhour' => app(SettingsService::class)->getInt('deuterium_basic_income'),
                'planet_b_building_id' => '0',
                'planet_b_hangar_id' => '',
            ]);

            $newPlanet->buildings()->create();
            $newPlanet->defenses()->create();
            $newPlanet->ships()->create();

            return true;
        }

        return false;
    }

    /**
     * setNewMoon
     *
     * @param int    $galaxy     Galaxy
     * @param int    $system     System
     * @param int    $position   Position
     * @param int    $owner      Owner
     * @param string $name       Moon name
     * @param int    $chance     Chance
     * @param int    $size       Size
     * @param int    $max_fields Max Fields
     * @param int    $min_temp   Min Temp
     * @param int    $max_temp   Max Temp
     *
     * @return string
     */
    public function setNewMoon($galaxy, $system, $position, $owner, $name = '', $chance = 0, $size = 0, $max_fields = 1, $min_temp = 0, $max_temp = 0)
    {
        $moonRow = DB::selectOne(
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
        $MoonPlanet = $moonRow !== null ? (array) $moonRow : null;

        if ($MoonPlanet['id_moon'] == '' && $MoonPlanet['planet_id'] != 0) {
            $SizeMin = 2000 + ($chance * 100);
            $SizeMax = 6000 + ($chance * 200);
            $temp = Formulas::setPlanetTemp($position);
            $size = $chance == 0 ? $size : mt_rand($SizeMin, $SizeMax);
            $size = $size == 0 ? mt_rand(2000, 6000) : $size;
            $max_fields = $max_fields == 0 ? 1 : $max_fields;

            $data = [
                'planet_name' => $name == '' ? __('game/global.moon') : $name,
                'planet_user_id' => $owner,
                'planet_galaxy' => $galaxy,
                'planet_system' => $system,
                'planet_planet' => $position,
                'planet_last_update' => time(),
                'planet_type' => PlanetTypesEnumerator::MOON,
                'planet_image' => 'mond',
                'planet_diameter' => $size,
                'planet_field_max' => $max_fields,
                'planet_temp_min' => $min_temp == 0 ? $temp['min'] : $min_temp,
                'planet_temp_max' => $max_temp == 0 ? $temp['max'] : $max_temp,
                'planet_b_building_id' => '0',
                'planet_b_hangar_id' => '',
            ];

            $insert_query = 'INSERT INTO `' . PLANETS . '` SET ';

            foreach ($data as $column => $value) {
                $insert_query .= '`' . $column . "` = '" . $value . "', ";
            }

            $insert_query = substr_replace($insert_query, '', -2) . ';';

            DB::statement($this->prepareSql($insert_query));

            $planet_id = (int) DB::getPdo()->lastInsertId();

            DB::statement($this->prepareSql('INSERT INTO `' . BUILDINGS . "` SET `building_planet_id` = '" . $planet_id . "';"));
            DB::statement($this->prepareSql('INSERT INTO `' . DEFENSES . "` SET `defense_planet_id` = '" . $planet_id . "';"));
            DB::statement($this->prepareSql('INSERT INTO `' . SHIPS . "` SET `ship_planet_id` = '" . $planet_id . "';"));

            return true;
        }

        return false;
    }
}
