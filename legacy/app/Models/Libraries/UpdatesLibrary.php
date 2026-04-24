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
class UpdatesLibrary
{
    use PreparesLegacySql;

    public function deleteUsersByDeletedAndInactive(int $del_deleted, int $del_inactive): ?array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT u.`id`
                    FROM `' . USERS . '` AS u
                    INNER JOIN `' . PREFERENCES . "` AS p ON p.preference_user_id = u.id
                    WHERE (p.`preference_delete_mode` < '" . $del_deleted . "'
                        AND p.`preference_delete_mode` <> 0)
                        OR (u.`onlinetime` < '" . $del_inactive . "' AND u.`onlinetime` <> 0 AND u.`authlevel` <> 3)"
                )
            )
        );
    }

    public function deleteMessages($del_before)
    {
        DB::statement(
            $this->prepareSql('DELETE FROM ' . MESSAGES . " WHERE `message_time` < '" . $del_before . "';")
        );
    }

    public function deleteReports($del_before)
    {
        DB::statement(
            $this->prepareSql('DELETE FROM ' . REPORTS . " WHERE `report_time` < '" . $del_before . "';")
        );
    }

    public function deleteSessions($delBefore): void
    {
        DB::table('sessions')->where('last_activity', '<', $delBefore)->delete();
    }

    public function deleteDestroyedPlanets($del_before)
    {
        DB::statement(
            $this->prepareSql(
                'DELETE p,b,d,s FROM `' . PLANETS . '` AS p
                INNER JOIN `' . BUILDINGS . '` AS b ON b.building_planet_id = p.`planet_id`
                INNER JOIN `' . DEFENSES . '` AS d ON d.defense_planet_id = p.`planet_id`
                INNER JOIN `' . SHIPS . "` AS s ON s.ship_planet_id = p.`planet_id`
                WHERE `planet_destroyed` < '" . $del_before . "'
                    AND `planet_destroyed` <> 0;"
            )
        );
    }

    public function deleteExpiredAcs()
    {
        DB::statement(
            $this->prepareSql(
                'DELETE a,m1,m2 FROM `' . ACS . '` AS a
                INNER JOIN `' . ACS_MEMBERS . '` m1 ON m1.`acs_group_id` = a.`acs_id`
                RIGHT JOIN `' . ACS_MEMBERS . '` m2 ON m2.`acs_group_id` = a.`acs_id`
                LEFT JOIN `' . FLEETS . '` f ON f.`fleet_group` = a.`acs_id`
                WHERE f.`fleet_id` IS NULL'
            )
        );
    }

    public function updatePlanet($building_name, $amount, $planet)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . PLANETS . ' AS p
                INNER JOIN ' . USERS_STATISTICS . ' AS s ON s.user_statistic_user_id = p.planet_user_id
                INNER JOIN ' . BUILDINGS . ' AS b ON b.building_planet_id = p.`planet_id` SET
                `' . $building_name . "` = '" . $amount . "',
                `user_statistic_buildings_points` = `user_statistic_buildings_points` + '" .
                $planet['building_points'] . "',
                `planet_b_building` = '" . $planet['planet_b_building'] . "',
                `planet_b_building_id` = '" . $planet['planet_b_building_id'] . "',
                `planet_field_current` = '" . $planet['planet_field_current'] . "',
                `planet_field_max` = '" . $planet['planet_field_max'] . "'
                WHERE `planet_id` = '" . $planet['planet_id'] . "';"
            )
        );
    }

    public function updateBuildingsQueue($planet)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . PLANETS . " SET
                `planet_b_building` = '" . $planet['planet_b_building'] . "',
                `planet_b_building_id` = '" . $planet['planet_b_building_id'] . "'
                WHERE `planet_id` = '" . $planet['planet_id'] . "';"
            )
        );
    }

    public function updateQueueResources($planet)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PLANETS . "` SET
                    `planet_metal` = '" . $planet['planet_metal'] . "',
                    `planet_crystal` = '" . $planet['planet_crystal'] . "',
                    `planet_deuterium` = '" . $planet['planet_deuterium'] . "',
                    `planet_b_building` = '" . $planet['planet_b_building'] . "',
                    `planet_b_building_id` = '" . $planet['planet_b_building_id'] . "'
                WHERE `planet_id` = '" . $planet['planet_id'] . "';"
            )
        );
    }

    public function updateAllPlanetData($data = []): void
    {
        if (is_array($data)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . PLANETS . ' AS p
                    INNER JOIN ' . USERS_STATISTICS . ' AS us ON us.user_statistic_user_id = p.planet_user_id
                    INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
                    INNER JOIN ' . SHIPS . ' AS s ON s.ship_planet_id = p.`planet_id`
                    INNER JOIN ' . RESEARCH . " AS r ON r.research_user_id = p.planet_user_id SET
                        `planet_metal` = '" . $data['planet']['planet_metal'] . "',
                        `planet_crystal` = '" . $data['planet']['planet_crystal'] . "',
                        `planet_deuterium` = '" . $data['planet']['planet_deuterium'] . "',
                        `planet_last_update` = '" . $data['planet']['planet_last_update'] . "',
                        `planet_b_hangar_id` = '" . $data['planet']['planet_b_hangar_id'] . "',
                        `planet_metal_perhour` = '" . $data['planet']['planet_metal_perhour'] . "',
                        `planet_crystal_perhour` = '" . $data['planet']['planet_crystal_perhour'] . "',
                        `planet_deuterium_perhour` = '" . $data['planet']['planet_deuterium_perhour'] . "',
                        `planet_energy_used` = '" . $data['planet']['planet_energy_used'] . "',
                        `planet_energy_max` = '" . $data['planet']['planet_energy_max'] . "',
                        `user_statistic_ships_points` = `user_statistic_ships_points` + '" . $data['ship_points'] . "',
                        `user_statistic_defenses_points` = `user_statistic_defenses_points`  + '" . $data['defense_points'] . "',
                        {$data['sub_query']}
                        {$data['tech_query']}
                        `planet_b_hangar` = '" . $data['planet']['planet_b_hangar'] . "'
                    WHERE `planet_id` = '" . $data['planet']['planet_id'] . "';"
                )
            );
        }
    }
}
