<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Overview extends Model
{
    public function getOwnFleets(int $userId): ?array
    {
        if ($userId > 0) {
            return $this->db->queryFetchAll(
                'SELECT DISTINCT
                    fleets.*,
                    po.`planet_name` AS `start_planet_name`,
                    pt.`planet_name` AS `target_planet_name`,
                    uo.`name` AS `start_planet_user`,
                    ut.`name` AS `target_planet_user`,
                    (
                        SELECT
                            GROUP_CONCAT(am.`acs_user_id`)
                        FROM `' . ACS_MEMBERS . '` am
                        WHERE am.`acs_group_id` = fleets.`fleet_group`
                    ) AS `acs_members`
                FROM
                (
                    SELECT
                        f.*
                    FROM
                        `' . FLEETS . "` f
                    WHERE
                        f.`fleet_owner` = '" . $userId . "'
                    OR
                        f.`fleet_target_owner` = '" . $userId . "'
                    UNION ALL
                    SELECT
                        f.*
                    FROM
                        `" . ACS_MEMBERS . '` am
                    LEFT JOIN `' . FLEETS . "` f ON
                        f.`fleet_group` = am.`acs_group_id`
                    WHERE
                        f.`fleet_id` IS NOT NULL
                    AND
                        am.`acs_user_id` = '" . $userId . "'
                ) fleets
                INNER JOIN `" . USERS . '` uo ON
                    uo.`id` = fleets.`fleet_owner`
                LEFT JOIN `' . USERS . '` ut ON
                    ut.`id` = fleets.`fleet_target_owner`
                INNER JOIN `' . PLANETS . '` po ON
                (
                    po.`planet_galaxy` = fleets.`fleet_start_galaxy` AND
                    po.`planet_system` = fleets.`fleet_start_system` AND
                    po.`planet_planet` = fleets.`fleet_start_planet` AND
                    po.`planet_type` = fleets.`fleet_start_type`
                )
                LEFT JOIN `' . PLANETS . '` pt ON
                (
                    pt.`planet_galaxy` = fleets.`fleet_end_galaxy` AND
                    pt.`planet_system` = fleets.`fleet_end_system` AND
                    pt.`planet_planet` = fleets.`fleet_end_planet` AND
                    pt.`planet_type` = fleets.`fleet_end_type`
                )'
            );
        }

        return null;
    }

    public function getPlanets(int $userId): ?array
    {
        if ($userId > 0) {
            return $this->db->queryFetchAll(
                'SELECT *
                    FROM ' . PLANETS . ' AS p
                    INNER JOIN ' . BUILDINGS . ' AS b ON b.building_planet_id = p.`planet_id`
                    INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
                    INNER JOIN ' . SHIPS . " AS s ON s.ship_planet_id = p.`planet_id`
                    WHERE `planet_user_id` = '" . $userId . "'
                            AND `planet_destroyed` = 0;"
            );
        }

        return null;
    }
}
