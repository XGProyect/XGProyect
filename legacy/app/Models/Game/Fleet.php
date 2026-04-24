<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Exception;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Entity\FleetEntity;
use Xgp\App\Core\Enumerators\MissionsEnumerator as Missions;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Fleet
{
    use PreparesLegacySql;

    public function getShipsByPlanetId($planet_id)
    {
        if ((int) $planet_id > 0) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        s.`ship_small_cargo_ship`,
                        s.`ship_big_cargo_ship`,
                        s.`ship_light_fighter`,
                        s.`ship_heavy_fighter`,
                        s.`ship_cruiser`,
                        s.`ship_battleship`,
                        s.`ship_colony_ship`,
                        s.`ship_recycler`,
                        s.`ship_espionage_probe`,
                        s.`ship_bomber`,
                        s.`ship_solar_satellite`,
                        s.`ship_destroyer`,
                        s.`ship_deathstar`,
                        s.`ship_battlecruiser`
                    FROM `' . SHIPS . "` AS s
                    WHERE s.`ship_planet_id` = '" . $planet_id . "';"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    public function getAllFleetsByUserId(int $userId): array
    {
        if ($userId > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT f.*
                        FROM `' . FLEETS . "` f
                        WHERE f.`fleet_owner` = '" . $userId . "';"
                    )
                )
            );
        }

        return [];
    }

    public function getAcsDataByGroupId(string $group_id): array
    {
        if (!empty($group_id)) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        acs.*,
                        (
                            SELECT COUNT(*)
                            FROM `' . ACS_MEMBERS . '` am
                            WHERE am.`acs_group_id` = acs.`acs_id`
                        ) AS `acs_members`
                    FROM `' . ACS . "` acs
                    WHERE acs.`acs_id` = '" . $group_id . "';"
                )
            );

            return $row !== null ? (array) $row : [];
        }

        return [];
    }

    public function getAllPlanetsByUserId(int $userId): array
    {
        if ($userId > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            p.`planet_id`,
                            p.`planet_name`,
                            p.`planet_galaxy`,
                            p.`planet_system`,
                            p.`planet_planet`,
                            p.`planet_type`
                        FROM `' . PLANETS . "` AS p
                        WHERE p.`planet_user_id` = '" . $userId . "';"
                    )
                )
            );
        }

        return [];
    }

    public function getOngoingAcs($userId)
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT acs.*
                    FROM `' . ACS_MEMBERS . '` am
                    INNER JOIN `' . ACS . '` acs ON acs.`acs_id` = am.`acs_group_id`
                    INNER JOIN `' . FLEETS . "` f ON f.`fleet_group` = acs.`acs_id`
                    WHERE am.`acs_user_id` = '" . $userId . "';"
                )
            )
        );
    }

    public function getPlanetOwnerByCoords(int $g, int $s, int $p, int $pt): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    p.`planet_user_id`,
                    u.`ally_id`
                FROM `' . PLANETS . '` AS p
                INNER JOIN `' . USERS . "` AS u
                    ON u.`id` = p.`planet_user_id`
                WHERE p.`planet_galaxy` = '" . $g . "'
                    AND p.`planet_system` = '" . $s . "'
                    AND p.`planet_planet` = '" . $p . "'
                    AND p.`planet_type` = '" . $pt . "';"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function getTargetDataByCoords(int $g, int $s, int $p, int $pt): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    p.`planet_user_id`,
                    p.`planet_debris_metal`,
                    p.`planet_debris_crystal`,
                    p.`planet_invisible_start_time`,
                    p.`planet_destroyed`,
                    u.`id`,
                    u.`authlevel`,
                    u.`onlinetime`,
                    u.`ally_id`,
                    pr.`preference_vacation_mode`
                FROM `' . PLANETS . '` p
                INNER JOIN `' . USERS . '` u ON u.`id` = p.`planet_user_id`
                INNER JOIN `' . PREFERENCES . "` pr ON pr.`preference_user_id` = u.`id`
                WHERE p.`planet_galaxy` = '" . $g . "'
                    AND p.`planet_system` = '" . $s . "'
                    AND p.`planet_planet` = '" . $p . "'
                    AND p.`planet_type` = '" . $pt . "'"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function getAcsCount(int $acs_id): int
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT COUNT(`acs_id`) AS `acs_amount`
                FROM `' . ACS . "`
                WHERE `acs_id` = '" . $acs_id . "'"
            )
        );

        return $row !== null ? (int) $row->acs_amount : 0;
    }

    public function insertNewFleet(array $fleet_data, array $planet_data, array $fleet_ships): bool
    {
        try {
            DB::transaction(function () use ($fleet_data, $planet_data, $fleet_ships): void {
                $sql = [];

                foreach ($fleet_data as $field => $value) {
                    $sql[] = '`' . $field . "` = '" . $value . "'";
                }

                DB::statement(
                    $this->prepareSql(
                        'INSERT INTO `' . FLEETS . '` SET '
                        . join(', ', $sql) .
                        ", `fleet_creation` = '" . time() . "';"
                    )
                );

                $this->updatePlanet($planet_data, $fleet_data, $fleet_ships);
            });

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function insertNewMissilesMission(array $data): void
    {
        DB::transaction(function () use ($data): void {
            DB::statement(
                $this->prepareSql(
                    'INSERT INTO `' . FLEETS . "` SET
                    `fleet_owner` = '" . $data['fleet_owner'] . "',
                    `fleet_mission` = '10',
                    `fleet_amount` = " . $data['fleet_amount'] . ",
                    `fleet_array` = '" . $data['fleet_array'] . "',
                    `fleet_start_time` = '" . $data['fleet_start_time'] . "',
                    `fleet_start_galaxy` = '" . $data['fleet_start_galaxy'] . "',
                    `fleet_start_system` = '" . $data['fleet_start_system'] . "',
                    `fleet_start_planet` ='" . $data['fleet_start_planet'] . "',
                    `fleet_start_type` = '1',
                    `fleet_end_time` = '" . $data['fleet_end_time'] . "',
                    `fleet_end_stay` = '0',
                    `fleet_end_galaxy` = '" . $data['fleet_end_galaxy'] . "',
                    `fleet_end_system` = '" . $data['fleet_end_system'] . "',
                    `fleet_end_planet` = '" . $data['fleet_end_planet'] . "',
                    `fleet_end_type` = '1',
                    `fleet_target_obj` = '" . $data['fleet_target_obj'] . "',
                    `fleet_resource_metal` = '0',
                    `fleet_resource_crystal` = '0',
                    `fleet_resource_deuterium` = '0',
                    `fleet_target_owner` = '" . $data['fleet_target_owner'] . "',
                    `fleet_group` = '0',
                    `fleet_mess` = '0',
                    `fleet_creation` = '" . time() . "';"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . DEFENSES . '` SET
                        `defense_interplanetary_missile` = `defense_interplanetary_missile` - ' . $data['fleet_amount'] . "
                    WHERE `defense_planet_id` =  '" . $data['current_planet'] . "'"
                )
            );
        });
    }

    public function updatePlanet(array $planet_data, array $fleet_data, array $fleet_ships): void
    {
        $sql = [];

        foreach ($fleet_ships as $field => $value) {
            $sql[] = '`' . $field . '` = `' . $field . "` - '" . $value . "'";
        }

        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PLANETS . '` AS p
                INNER JOIN `' . SHIPS . '` AS s ON s.`ship_planet_id` = p.`planet_id` SET
                ' . join(', ', $sql) . ',
                `planet_metal` = `planet_metal` - ' . $fleet_data['fleet_resource_metal'] . ',
                `planet_crystal` = `planet_crystal` - ' . $fleet_data['fleet_resource_crystal'] . ',
                `planet_deuterium` = `planet_deuterium` - ' . ($fleet_data['fleet_resource_deuterium'] + $fleet_data['fleet_fuel']) . '
                WHERE `planet_id` = ' . $planet_data['planet_id'] . ';'
            )
        );
    }

    public function getBuddies(int $current_planet, int $target_planet): string
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT COUNT(*) AS buddies
                FROM  `' . BUDDY . "`
                WHERE (
                    (
                        buddy_sender = '" . $current_planet . "'
                        AND buddy_receiver = '" . $target_planet . "'
                    )
                    OR (
                        buddy_sender = '" . $target_planet . "'
                        AND buddy_receiver = '" . $current_planet . "'
                    )
                )
                AND buddy_status = 1"
            )
        );

        return $row !== null ? (string) $row->buddies : '0';
    }

    public function getAcsMaxTime(int $group_id): string
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT MAX(`fleet_start_time`) AS start_time
                FROM `' . FLEETS . "`
                WHERE `fleet_group` = '" . $group_id . "';"
            )
        );

        return $row !== null ? (string) $row->start_time : '';
    }

    public function updateAcsTimes(int $group_id, int $start_time, int $end_time)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . FLEETS . "` SET
                `fleet_start_time` = '" . $start_time . "',
                `fleet_end_time` = fleet_end_time + '" . $end_time . "'
                WHERE `fleet_group` = '" . $group_id . "';"
            )
        );
    }

    public function getAcsOwner(int $fleet_group): int
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT af.`acs_owner`
                FROM `' . ACS . "` af
                WHERE af.`acs_id` = '" . $fleet_group . "';"
            )
        );

        return $row !== null ? (int) $row->acs_owner : 0;
    }

    public function removeAcs(int $fleet_group): void
    {
        DB::statement(
            $this->prepareSql(
                'DELETE FROM `' . ACS . "`
                WHERE `acs_id` = '" . $fleet_group . "';"
            )
        );

        DB::statement(
            $this->prepareSql(
                'UPDATE `' . FLEETS . "` f SET
                    f.`fleet_group` = '0'
                WHERE f.`fleet_group` = '" . $fleet_group . "';"
            )
        );
    }

    public function returnFleet(FleetEntity $fleet, int $userId): bool
    {
        try {
            DB::transaction(function () use ($fleet, $userId): void {
                if ($fleet->getFleetGroup() > 0) {
                    $acs = $this->getAcsOwner($fleet->getFleetGroup());

                    if (!empty($acs['acs_owner']) &&
                        $acs['acs_owner'] == $fleet->getFleetOwner() &&
                        $fleet->getFleetMission() == Missions::ATTACK) {
                        $this->removeAcs($fleet->getFleetGroup());
                    }

                    if ($fleet->getFleetMission() == Missions::ACS) {
                        DB::statement(
                            $this->prepareSql(
                                'UPDATE `' . FLEETS . "` f SET
                                    f.`fleet_group` = '0'
                                WHERE f.`fleet_id` = '" . $fleet->getFleetId() . "';"
                            )
                        );
                    }
                }

                $base_time = time();
                $fleet_creation = $fleet->getFleetCreation();
                $current_time = $base_time - $fleet_creation;
                $flight_lenght = $fleet->getFleetStartTime() - $fleet_creation;
                $return_time = $base_time + $current_time;

                if ($fleet->getFleetEndStay() != 0 &&
                    $current_time > $flight_lenght) {
                    $return_time = $base_time + $flight_lenght;
                }

                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . FLEETS . "` f SET
                            f.`fleet_start_time` = '" . $base_time . "',
                            f.`fleet_end_stay` = '0',
                            f.`fleet_end_time` = '" . $return_time . "',
                            f.`fleet_target_owner` = '" . $userId . "',
                            f.`fleet_mess` = '1'
                        WHERE f.`fleet_id` = '" . $fleet->getFleetId() . "';"
                    )
                );
            });

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function createNewAcs(string $acs_code, FleetEntity $fleet): bool | int
    {
        try {
            return DB::transaction(function () use ($acs_code, $fleet): int {
                DB::statement(
                    $this->prepareSql(
                        'INSERT INTO `' . ACS . "` SET
                            `acs_name` = ?,
                            `acs_owner` = '" . $fleet->getFleetOwner() . "',
                            `acs_galaxy` = '" . $fleet->getFleetEndGalaxy() . "',
                            `acs_system` = '" . $fleet->getFleetEndSystem() . "',
                            `acs_planet` = '" . $fleet->getFleetEndPlanet() . "',
                            `acs_planet_type` = '" . $fleet->getFleetEndType() . "'"
                    ),
                    [$acs_code]
                );

                $group_id = (int) DB::getPdo()->lastInsertId();

                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . FLEETS . "` SET
                            `fleet_group` = '" . $group_id . "'
                        WHERE `fleet_id` = '" . $fleet->getFleetId() . "'"
                    )
                );

                $this->insertNewAcsMember($fleet->getFleetOwner(), $group_id);

                return $group_id;
            });
        } catch (Exception $e) {
            return false;
        }
    }

    public function getListOfAcsMembers($group_id)
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT u.`id`, u.`name`
                    FROM `' . ACS_MEMBERS . '` am
                    INNER JOIN `' . USERS . "` u ON u.`id` = am.`acs_user_id`
                    WHERE am.`acs_group_id` = '" . $group_id . "'"
                )
            )
        );
    }

    public function updateAcsName(string $acs_name, int $acs_id, int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . ACS . "` acs SET
                    acs.`acs_name` = ?
                WHERE acs.`acs_id` = '" . $acs_id . "'
                    AND acs.`acs_owner` = '" . $userId . "';"
            ),
            [$acs_name]
        );
    }

    public function insertNewAcsMember(int $member, int $group_id): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO `' . ACS_MEMBERS . "` SET
                    `acs_group_id` = '" . $group_id . "',
                    `acs_user_id` = '" . $member . "'"
            )
        );
    }

    public function removeAcsMember(int $member, int $group_id): void
    {
        DB::statement(
            $this->prepareSql(
                'DELETE FROM `' . ACS_MEMBERS . "`
                WHERE `acs_group_id` = '" . $group_id . "'
                    AND `acs_user_id` = '" . $member . "'"
            )
        );
    }

    public function getUserIdByName(string $username, int $group_id): int
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT u.`id`
                FROM `' . USERS . "` u
                WHERE u.`name` = '" . $username . "'
                AND u.`id` NOT IN (
                    SELECT acs.`acs_user_id`
                    FROM `" . ACS_MEMBERS . "` acs
                    WHERE acs.`acs_group_id` = '" . $group_id . "'
                )"
            )
        );

        return $row !== null ? (int) $row->id : 0;
    }
}
