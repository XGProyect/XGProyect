<?php

declare(strict_types=1);

namespace Xgp\App\Libraries\Missions;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Objects;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\StatisticsLibrary;
use Xgp\App\Libraries\UpdatesLibrary;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 * @SuppressWarnings("PHPMD.TooManyMethods")
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 */
class Missions
{
    use PreparesLegacySql;

    protected $resource;
    protected $pricelist;
    protected $combat_caps;

    public function __construct()
    {
        $this->resource = Objects::getInstance()->getObjects();
        $this->pricelist = Objects::getInstance()->getPrice();
        $this->combat_caps = Objects::getInstance()->getCombatSpecs();
    }

    protected function removeFleet(int $fleetId): void
    {
        $this->deleteFleetById($fleetId);
    }

    protected function returnFleet(int $fleetId): void
    {
        $this->updateFleetStatusToReturnById($fleetId);
    }

    protected function restoreFleet(array $fleetRow, bool $start = true): void
    {
        if ($start) {
            $galaxy = $fleetRow['fleet_start_galaxy'];
            $system = $fleetRow['fleet_start_system'];
            $planet = $fleetRow['fleet_start_planet'];
            $type = $fleetRow['fleet_start_type'];
        } else {
            $galaxy = $fleetRow['fleet_end_galaxy'];
            $system = $fleetRow['fleet_end_system'];
            $planet = $fleetRow['fleet_end_planet'];
            $type = $fleetRow['fleet_end_type'];
        }

        $this->makeUpdate($galaxy, $system, $planet, $type);

        $ships = FleetsLib::getFleetShipsArray($fleetRow['fleet_array']);
        $ships_fields = '';

        foreach ($ships as $id => $amount) {
            $ships_fields .= '`' . $this->resource[$id] . '` = `' .
            $this->resource[$id] . "` + '" . $amount . "', ";
        }

        $fuel_return = 0;

        if ($fleetRow['fleet_mission'] == 4 && !$start) {
            $fuel_return = $fleetRow['fleet_fuel'] / 2;
        }

        $updateArray = [
            'resources' => [
                'metal' => $fleetRow['fleet_resource_metal'],
                'crystal' => $fleetRow['fleet_resource_crystal'],
                'deuterium' => ($fleetRow['fleet_resource_deuterium'] + $fuel_return),
            ],
            'ships' => $ships_fields,
            'coords' => [
                'galaxy' => $galaxy,
                'system' => $system,
                'planet' => $planet,
                'type' => $type,
            ],
        ];

        $this->updatePlanetsShipsByCoords($updateArray);
    }

    protected function storeResources(array $fleetRow, $start = false): void
    {
        if ($start) {
            $galaxy = $fleetRow['fleet_start_galaxy'];
            $system = $fleetRow['fleet_start_system'];
            $planet = $fleetRow['fleet_start_planet'];
            $type = $fleetRow['fleet_start_type'];
        } else {
            $galaxy = $fleetRow['fleet_end_galaxy'];
            $system = $fleetRow['fleet_end_system'];
            $planet = $fleetRow['fleet_end_planet'];
            $type = $fleetRow['fleet_end_type'];
        }

        $this->makeUpdate($galaxy, $system, $planet, $type);

        $updateArray = [
            'resources' => [
                'metal' => $fleetRow['fleet_resource_metal'],
                'crystal' => $fleetRow['fleet_resource_crystal'],
                'deuterium' => $fleetRow['fleet_resource_deuterium'],
            ],
            'coords' => [
                'galaxy' => $galaxy,
                'system' => $system,
                'planet' => $planet,
                'type' => $type,
            ],
        ];

        $this->updatePlanetResourcesByCoords($updateArray);
    }

    protected function makeUpdate(int $galaxy, int $system, int $planet, int $type): void
    {
        $target_planet = $this->getAllPlanetDataByCoords([
            'coords' => [
                'galaxy' => $galaxy,
                'system' => $system,
                'planet' => $planet,
                'type' => $type,
            ],
        ]);

        $target_user = $this->getAllUserDataByUserId(
            $target_planet['planet_user_id']
        );

        UpdatesLibrary::updatePlanetResources($target_user, $target_planet, time());
    }

    protected function canStartMission(array $fleet): bool
    {
        return ($fleet['fleet_mess'] == 0 && $fleet['fleet_start_time'] <= time() && $fleet['fleet_end_stay'] <= time());
    }

    protected function canCompleteMission(array $fleet): bool
    {
        return ($fleet['fleet_end_time'] <= time());
    }

    protected function deleteFleetById(int $fleedId): void
    {
        if ((int) $fleedId > 0) {
            DB::statement(
                $this->prepareSql(
                    'DELETE FROM `' . FLEETS . "` WHERE `fleet_id` = '" . $fleedId . "'"
                )
            );
        }
    }

    protected function updateFleetStatusToReturnById(int $fleedId): void
    {
        if ((int) $fleedId > 0) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . FLEETS . " SET
                        `fleet_mess` = '1'
                    WHERE `fleet_id` = '" . $fleedId . "'"
                )
            );
        }
    }

    protected function updateFleetStatusToStayById($fleedId): void
    {
        if ((int) $fleedId > 0) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . FLEETS . " SET
                        `fleet_mess` = '2'
                    WHERE `fleet_id` = '" . $fleedId . "'"
                )
            );
        }
    }

    protected function updatePlanetsShipsByCoords(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . PLANETS . ' AS p
                    INNER JOIN ' . SHIPS . " AS s ON s.ship_planet_id = p.`planet_id` SET
                        {$data['ships']}
                        `planet_metal` = `planet_metal` + '" . $data['resources']['metal'] . "',
                        `planet_crystal` = `planet_crystal` + '" . $data['resources']['crystal'] . "',
                        `planet_deuterium` = `planet_deuterium` + '" . $data['resources']['deuterium'] . "'
                    WHERE `planet_galaxy` = '" . $data['coords']['galaxy'] . "' AND
                        `planet_system` = '" . $data['coords']['system'] . "' AND
                        `planet_planet` = '" . $data['coords']['planet'] . "' AND
                        `planet_type` = '" . $data['coords']['type'] . "'"
                )
            );
        }
    }

    protected function updatePlanetResourcesByCoords(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . PLANETS . " SET
                        `planet_metal` = `planet_metal` + '" . $data['resources']['metal'] . "',
                        `planet_crystal` = `planet_crystal` + '" . $data['resources']['crystal'] . "',
                        `planet_deuterium` = `planet_deuterium` + '" . $data['resources']['deuterium'] . "'
                    WHERE `planet_galaxy` = '" . $data['coords']['galaxy'] . "' AND
                        `planet_system` = '" . $data['coords']['system'] . "' AND
                        `planet_planet` = '" . $data['coords']['planet'] . "' AND
                        `planet_type` = '" . $data['coords']['type'] . "'
                    LIMIT 1;"
                )
            );
        }
    }

    protected function getAllPlanetDataByCoords(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT *
                    FROM `' . PLANETS . '` AS p
                    LEFT JOIN `' . BUILDINGS . '` AS b ON b.building_planet_id = p.`planet_id`
                    LEFT JOIN `' . DEFENSES . '` AS d ON d.defense_planet_id = p.`planet_id`
                    LEFT JOIN `' . SHIPS . "` AS s ON s.ship_planet_id = p.`planet_id`
                    WHERE `planet_galaxy` = '" . (int) $data['coords']['galaxy'] . "' AND
                        `planet_system` = '" . (int) $data['coords']['system'] . "' AND
                        `planet_planet` = '" . (int) $data['coords']['planet'] . "' AND
                        `planet_type` = '" . (int) $data['coords']['type'] . "'
                    LIMIT 1;"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function getAllUserDataByUserId(int $userId): array
    {
        if ($userId > 0) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT u.*,
                        r.*,
                        pr.*,
                        pref.preference_vacation_mode
                    FROM `' . USERS . '` AS u
                    INNER JOIN `' . RESEARCH . '` AS r ON r.research_user_id = u.id
                    INNER JOIN `' . PREMIUM . '` AS pr ON pr.premium_user_id = u.id
                    INNER JOIN `' . PREFERENCES . "` AS pref ON pref.preference_user_id = u.id
                    WHERE u.`id` = '" . $userId . "'
                    LIMIT 1;"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function deleteAcsFleetById($fleetGroupId): void
    {
        if ((int) $fleetGroupId > 0) {
            DB::statement(
                $this->prepareSql(
                    'DELETE FROM `' . ACS . "`
                    WHERE `acs_id` = '" . $fleetGroupId . "'"
                )
            );
        }
    }

    protected function updateAcsFleetStatusByGroupId($fleetGroupId): void
    {
        if ((int) $fleetGroupId > 0) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . FLEETS . "` SET
                        `fleet_mess` = '1'
                    WHERE `fleet_group` = '" . $fleetGroupId . "'"
                )
            );
        }
    }

    protected function getAllAcsFleetsByGroupId(int $fleetGroupId): ?array
    {
        if ((int) $fleetGroupId > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            f.*,
                            r.`research_hyperspace_technology`
                        FROM `' . FLEETS . '` f
                        LEFT JOIN `' . RESEARCH . "` r
                            ON r.`research_user_id` = f.`fleet_owner`
                        WHERE f.`fleet_group` = '" . $fleetGroupId . "';"
                    )
                )
            );
        }

        return null;
    }

    protected function getAllFleetsByEndCoordsAndTimes(array $data = []): array
    {
        if (is_array($data)) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT *
                        FROM `' . FLEETS . "`
                        WHERE `fleet_end_galaxy` = '" . (int) $data['coords']['galaxy'] . "' AND
                            `fleet_end_system` = '" . (int) $data['coords']['system'] . "' AND
                            `fleet_end_planet` = '" . (int) $data['coords']['planet'] . "' AND
                            `fleet_end_type` = '" . (int) $data['coords']['type'] . "' AND
                            `fleet_start_time` < '" . $data['time'] . "' AND
                            `fleet_end_stay` >= '" . $data['time'] . "';"
                    )
                )
            );
        }

        return [];
    }

    protected function updatePlanetDebrisByCoords(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . PLANETS . " SET
                        `planet_invisible_start_time` = '" . $data['time'] . "',
                        `planet_debris_metal` = `planet_debris_metal` + '" . $data['debris']['metal'] . "',
                        `planet_debris_crystal` = `planet_debris_crystal` + '" . $data['debris']['crystal'] . "'
                    WHERE `planet_galaxy` = '" . (int) $data['coords']['galaxy'] . "' AND
                        `planet_system` = '" . (int) $data['coords']['system'] . "' AND
                        `planet_planet` = '" . (int) $data['coords']['planet'] . "' AND
                        `planet_type` = 1
                    LIMIT 1;"
                )
            );
        }
    }

    protected function getTechnologiesByUserId(int $userId): array
    {
        if ($userId > 0) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT u.name,
                        r.research_weapons_technology,
                        r.research_shielding_technology,
                        r.research_armour_technology,
                        r.research_hyperspace_technology
                    FROM ' . USERS . ' AS u
                    INNER JOIN `' . RESEARCH . "` AS r
                        ON r.research_user_id = u.id
                    WHERE u.id = '" . $userId . "';"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function getMoonIdByCoords(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT `planet_id`
                    FROM `' . PLANETS . "`
                    WHERE `planet_galaxy` = '" . $data['coords']['galaxy'] . "'
                        AND `planet_system` = '" . $data['coords']['system'] . "'
                        AND `planet_planet` = '" . $data['coords']['planet'] . "'
                        AND `planet_type` = '3';"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function insertReport(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'INSERT INTO `' . REPORTS . "` SET
                    `report_owners` = '" . $data['owners'] . "',
                    `report_rid` = '" . $data['rid'] . "',
                    `report_content` = '" . $data['content'] . "',
                    `report_destroyed` = '" . $data['destroyed'] . "',
                    `report_time` = '" . $data['time'] . "'"
                )
            );
        }
    }

    protected function updateReturningFleetData(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . FLEETS . "` SET
                    `fleet_array` = '" . $data['ships'] . "',
                    `fleet_amount` = '" . $data['amount'] . "',
                    `fleet_mess` = '1',
                    `fleet_resource_metal` = `fleet_resource_metal` + '" . $data['stolen']['metal'] . "' ,
                    `fleet_resource_crystal` = `fleet_resource_crystal` + '" . $data['stolen']['crystal'] . "' ,
                    `fleet_resource_deuterium` = `fleet_resource_deuterium` + '" . $data['stolen']['deuterium'] . "'
                    WHERE `fleet_id` = '" . $data['fleet_id'] . "';"
                )
            );
        }
    }

    protected function deleteMultipleFleetsByIds(string $idString): void
    {
        DB::statement(
            $this->prepareSql(
                'DELETE FROM `' . FLEETS . '`
                WHERE `fleet_id` IN (' . $idString . ')'
            )
        );
    }

    protected function updatePlanetLossesById(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . '`, `' . SHIPS . '`, `' . DEFENSES . '`  SET
                    ' . $data['ships'] . '
                    `planet_metal` = `planet_metal` -  ' . $data['stolen']['metal'] . ',
                    `planet_crystal` = `planet_crystal` -  ' . $data['stolen']['crystal'] . ',
                    `planet_deuterium` = `planet_deuterium` -  ' . $data['stolen']['deuterium'] . "
                    WHERE `planet_id` = '" . $data['planet_id'] . "' AND
                        `ship_planet_id` = '" . $data['planet_id'] . "' AND
                        `defense_planet_id` = '" . $data['planet_id'] . "'"
                )
            );
        }
    }

    protected function getPlanetAndUserCountsCounts(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        (SELECT COUNT(*)
                            FROM ' . PLANETS . " AS pc1
                            WHERE pc1.`planet_user_id` = '" . $data['id'] . "' AND
                                            pc1.`planet_type` = '1' AND
                                            pc1.`planet_destroyed` = '0') AS planet_count,
                        (SELECT COUNT(*)
                            FROM " . PLANETS . " AS pc2
                            WHERE pc2.`planet_galaxy` = '" . $data['coords']['galaxy'] . "' AND
                                            pc2.`planet_system` = '" . $data['coords']['system'] . "' AND
                                            pc2.`planet_planet` = '" . $data['coords']['planet'] . "' AND
                                            pc2.`planet_type` = '1') AS galaxy_count,
                        (SELECT `research_astrophysics`
                            FROM " . RESEARCH . "
                            WHERE `research_user_id` = '" . $data['id'] . "') AS astro_level"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function getFriendlyPlanetData(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        pc1.`planet_user_id` AS `start_id`,
                        pc1.`planet_name` AS `start_name`,
                        pc2.`planet_user_id` AS `target_id`,
                        pc2.`planet_name` AS `target_name`,
                        pc2.`planet_metal` AS `target_metal`,
                        pc2.`planet_crystal` AS `target_crystal`,
                        pc2.`planet_deuterium` AS `target_deuterium`,
                        u.`name` AS `start_user_name`
                    FROM `' . PLANETS . '` AS pc1 JOIN `' . PLANETS . '` AS pc2
                    LEFT JOIN `' . USERS . "` AS u
                        ON u.`id` = pc1.`planet_user_id`
                    WHERE pc1.planet_galaxy = '" . $data['coords']['start']['galaxy'] . "' AND
                        pc1.`planet_system` = '" . $data['coords']['start']['system'] . "' AND
                        pc1.`planet_planet` = '" . $data['coords']['start']['planet'] . "' AND
                        pc1.`planet_type` = '" . $data['coords']['start']['type'] . "' AND
                        pc2.`planet_galaxy` = '" . $data['coords']['end']['galaxy'] . "' AND
                        pc2.`planet_system` = '" . $data['coords']['end']['system'] . "' AND
                        pc2.`planet_planet` = '" . $data['coords']['end']['planet'] . "' AND
                        pc2.`planet_type` = '" . $data['coords']['end']['type'] . "'"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function updateLostShipsAndDefensePoints(int $playerId, array $lost): void
    {
        $shipPoints = 0;
        $defensePoints = 0;

        foreach ($lost as $unit => $lostCount) {
            if ($unit >= 401) {
                $defensePoints += StatisticsLibrary::calculatePoints($unit, 1) * $lostCount;
            } else {
                $shipPoints += StatisticsLibrary::calculatePoints($unit, 1) * $lostCount;
            }
        }

        DB::statement(
            $this->prepareSql(
                'UPDATE `' . USERS_STATISTICS . "` AS us SET
                    us.`user_statistic_ships_points` = us.`user_statistic_ships_points` - '" . $shipPoints . "' ,
                    us.`user_statistic_defenses_points` = us.`user_statistic_defenses_points` - '" . $defensePoints . "'
                WHERE us.`user_statistic_user_id` = '" . $playerId . "'"
            )
        );
    }

    protected function updateColonizationStatistics(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . USERS_STATISTICS . ' AS us SET
                    us.`user_statistic_ships_points` = us.`user_statistic_ships_points` - ' . $data['points'] . '
                    WHERE us.`user_statistic_user_id` = (
                        SELECT p.planet_user_id FROM ' . PLANETS . " AS p
                        WHERE p.planet_galaxy = '" . $data['coords']['galaxy'] . "' AND
                            p.planet_system = '" . $data['coords']['system'] . "' AND
                            p.planet_planet = '" . $data['coords']['planet'] . "' AND
                            p.planet_type = '" . $data['coords']['type'] . "'
                    );"
                )
            );
        }
    }

    protected function updateColonizatonReturningFleet(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . FLEETS . ', ' . USERS_STATISTICS . " SET
                    `fleet_array` = '" . $data['ships'] . "',
                    `fleet_amount` = `fleet_amount` - 1,
                    `fleet_resource_metal` = '0',
                    `fleet_resource_crystal` = '0',
                    `fleet_resource_deuterium` = '0',
                    `fleet_mess` = '1',
                    `user_statistic_ships_points` = `user_statistic_ships_points` - " . $data['points'] . "
                    WHERE `fleet_id` = '" . $data['fleet_id'] . "' AND
                        `user_statistic_user_id` = (
                        SELECT planet_user_id FROM " . PLANETS . "
                        WHERE planet_galaxy = '" . $data['coords']['galaxy'] . "' AND
                            planet_system = '" . $data['coords']['system'] . "' AND
                            planet_planet = '" . $data['coords']['planet'] . "' AND
                            planet_type = '" . $data['coords']['type'] . "'
                    );"
                )
            );
        }
    }

    protected function updateFleetsStatusToMakeThemReturn(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . FLEETS . "` AS f SET
                        f.`fleet_start_type` = '1'
                    WHERE f.`fleet_start_galaxy` = '" . $data['coords']['galaxy'] . "'
                        AND f.`fleet_start_system` = '" . $data['coords']['system'] . "'
                        AND f.`fleet_start_planet` = '" . $data['coords']['planet'] . "';"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . FLEETS . "` AS f SET
                        f.`fleet_end_type` = '1'
                    WHERE f.`fleet_end_galaxy` = '" . $data['coords']['galaxy'] . "'
                        AND f.`fleet_end_system` = '" . $data['coords']['system'] . "'
                        AND f.`fleet_end_planet` = '" . $data['coords']['planet'] . "';"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . "` AS p SET
                        `planet_destroyed` = '" . $data['time'] . "'
                    WHERE p.`planet_id` = '" . $data['planet_id'] . "';"
                )
            );
        }
    }

    protected function updateUserCurrentPlanetByCoordsAndUserId(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . USERS . ' SET
                        `current_planet` = (
                            SELECT `planet_id`
                            FROM ' . PLANETS . "
                            WHERE `planet_galaxy` = '" . $data['coords']['fleet_end_galaxy'] . "' AND
                                `planet_system` = '" . $data['coords']['fleet_end_system'] . "' AND
                                `planet_planet` = '" . $data['coords']['fleet_end_planet'] . "' AND
                                `planet_type` = '1')
                    WHERE `id` = '" . $data['planet_user_id'] . "';"
                )
            );
        }
    }

    protected function updateFleetArrayById(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . FLEETS . " SET
                    `fleet_array` = '" . $data['ships'] . "',
                    `fleet_mess` = '1'
                    WHERE `fleet_id` = '" . (int) $data['fleet_id'] . "';"
                )
            );
        }
    }

    protected function updateFleetResourcesById(int $fleetId, string $resource, int $amount): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . FLEETS . " AS f SET
                `fleet_resource_{$resource}` = `fleet_resource_{$resource}` + '" . $amount . "',
                `fleet_mess` = '1'
                WHERE `fleet_id` = '" . $fleetId . "';"
            )
        );
    }

    protected function updateDarkMatter(int $userId, int $darkMatter): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . PREMIUM . " AS p SET
                `premium_dark_matter` = `premium_dark_matter` + '" . $darkMatter . "'
                WHERE `premium_user_id` = '" . $userId . "';"
            )
        );
    }

    protected function updateFleetEndTime(int $fleetId, int $fleetEndTime): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . FLEETS . " AS f SET
                `fleet_end_time` = '" . $fleetEndTime . "',
                `fleet_mess` = '1'
                WHERE `fleet_id` = '" . $fleetId . "';"
            )
        );
    }

    protected function getMissileAttackerDataByCoords(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT p.`planet_name`, r.`research_weapons_technology`
                    FROM ' . PLANETS . ' AS p
                    INNER JOIN ' . RESEARCH . ' AS r ON r.research_user_id = p.planet_user_id
                    WHERE `planet_galaxy` = ' . $data['coords']['galaxy'] . ' AND
                        `planet_system` = ' . $data['coords']['system'] . ' AND
                        `planet_planet` = ' . $data['coords']['planet'] . ' AND
                        `planet_type` = ' . $data['coords']['type'] . ';'
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function getMissileTargetDataByCoords(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT p.`planet_id`, p.`planet_name`, p.`planet_user_id`, d.*, r.`research_shielding_technology`
                    FROM ' . PLANETS . ' AS p
                    INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
                    INNER JOIN ' . RESEARCH . ' AS r ON r.research_user_id = p.planet_user_id
                    WHERE `planet_galaxy` = ' . $data['coords']['galaxy'] . ' AND
                        `planet_system` = ' . $data['coords']['system'] . ' AND
                        `planet_planet` = ' . $data['coords']['planet'] . ' AND
                        `planet_type` = ' . $data['coords']['type'] . ';'
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function updatePlanetDefenses(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . DEFENSES . " SET
                    {$data['destroyed_query']}
                    `defense_anti-ballistic_missile` = '" . $data['amount'] . "'
                    WHERE defense_planet_id = '" . (int) $data['planet_id'] . "';"
                )
            );
        }
    }

    protected function updatePlanetDebrisFieldAndFleet(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . PLANETS . ', ' . FLEETS . " SET
                    `planet_debris_metal` = `planet_debris_metal` - '" . $data['recycled']['metal'] . "',
                    `planet_debris_crystal` = `planet_debris_crystal` - '" . $data['recycled']['crystal'] . "',
                    `fleet_resource_metal` = `fleet_resource_metal` + '" . $data['recycled']['metal'] . "',
                    `fleet_resource_crystal` = `fleet_resource_crystal` + '" . $data['recycled']['crystal'] . "',
                    `fleet_mess` = '1'
                    WHERE `planet_galaxy` = '" . $data['coords']['galaxy'] . "' AND
                        `planet_system` = '" . $data['coords']['system'] . "' AND
                        `planet_planet` = '" . $data['coords']['planet'] . "' AND
                        `planet_type` = 1 AND
                        `fleet_id` = '" . (int) $data['fleet_id'] . "'"
                )
            );
        }
    }

    protected function getPlanetDebris(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        `planet_name` AS target_name,
                        `planet_debris_metal`,
                        `planet_debris_crystal`
                    FROM `' . PLANETS . "`
                    WHERE `planet_galaxy` = '" . $data['coords']['galaxy'] . "' AND
                        `planet_system` = '" . $data['coords']['system'] . "' AND
                        `planet_planet` = '" . $data['coords']['planet'] . "' AND
                        `planet_type` = 1
                    LIMIT 1;"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function getSpyUserDataByCords(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        p.`planet_name`,
                        p.`planet_galaxy`,
                        p.`planet_system`,
                        p.`planet_planet`,
                        u.`name`,
                        r.`research_espionage_technology`,
                        pr.`premium_officier_technocrat`
                    FROM `' . PLANETS . '` AS p
                    INNER JOIN `' . USERS . '` AS u ON u.`id` = p.`planet_user_id`
                    INNER JOIN `' . PREMIUM . '` AS pr ON pr.`premium_user_id` = p.`planet_user_id`
                    INNER JOIN `' . RESEARCH . '` AS r ON r.`research_user_id` = p.`planet_user_id`
                    WHERE p.`planet_galaxy` = ' . $data['coords']['galaxy'] . ' AND
                        p.`planet_system` = ' . $data['coords']['system'] . ' AND
                        p.`planet_planet` = ' . $data['coords']['planet'] . ' AND
                        p.`planet_type` = ' . $data['coords']['type'] . ';'
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function getInquiredUserDataByCords(array $data = []): array
    {
        if (is_array($data)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        p.`planet_id`,
                        p.`planet_user_id`,
                        p.`planet_name`,
                        p.`planet_galaxy`,
                        p.`planet_system`,
                        p.`planet_planet`,
                        p.planet_metal,
                        p.`planet_crystal`,
                        p.`planet_deuterium`,
                        p.`planet_energy_max`,
                        s.*, d.*, b.*, r.*,
                        pr.`premium_officier_technocrat`
                    FROM `' . PLANETS . '` AS p
                    INNER JOIN `' . SHIPS . '` AS s ON s.`ship_planet_id` = p.`planet_id`
                    INNER JOIN `' . DEFENSES . '` AS d ON d.`defense_planet_id` = p.`planet_id`
                    INNER JOIN `' . BUILDINGS . '` AS b ON b.`building_planet_id` = p.`planet_id`
                    INNER JOIN `' . USERS . '` AS u ON u.`id` = p.`planet_user_id`
                    INNER JOIN `' . PREMIUM . '` AS pr ON pr.`premium_user_id` = p.`planet_user_id`
                    INNER JOIN `' . RESEARCH . "` AS r ON r.`research_user_id` = p.`planet_user_id`
                    WHERE p.`planet_galaxy` = '" . $data['coords']['galaxy'] . "' AND
                        p.`planet_system` = '" . $data['coords']['system'] . "' AND
                        p.`planet_planet` = '" . $data['coords']['planet'] . "' AND
                        p.`planet_type` = '" . $data['coords']['type'] . "';"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    protected function updateCrystalDebrisByPlanetId(array $data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . PLANETS . " SET
                    `planet_invisible_start_time` = '" . $data['time'] . "',
                    `planet_debris_crystal` = `planet_debris_crystal` + '" . $data['crystal'] . "'
                    WHERE `planet_id` = '" . $data['planet_id'] . "';"
                )
            );
        }
    }

    protected function updateReturningFleetResources(int $fleedId = 0): void
    {
        if ($fleedId > 0) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . FLEETS . " SET
                        `fleet_resource_metal` = '0' ,
                        `fleet_resource_crystal` = '0' ,
                        `fleet_resource_deuterium` = '0' ,
                        `fleet_mess` = '1'
                    WHERE `fleet_id` = '" . $fleedId . "'
                    LIMIT 1 ;"
                )
            );
        }
    }
}
