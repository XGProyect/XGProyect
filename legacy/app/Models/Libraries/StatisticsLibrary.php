<?php

namespace Xgp\App\Models\Libraries;

use Xgp\App\Core\Model;
use Xgp\App\Core\Options;

class StatisticsLibrary extends Model
{
    public function getResearchToUpdate(int $userId): array
    {
        return $this->db->queryFetch(
            'SELECT *
            FROM `' . RESEARCH . "` ttu
            WHERE ttu.research_user_id = '" . $userId . "';"
        );
    }

    /**
     *
     * @param string $what      What
     * @param int    $planet_id Planet ID
     *
     * @return array
     */
    public function getPlanetElementToUpdate($what, $planet_id)
    {
        return $this->db->queryFetch(
            'SELECT *
            FROM `' . config('DB_PREFIX') . $what . '` ttu
            WHERE ttu.' . rtrim($what, 's') . "_planet_id = '" . $planet_id . "';"
        );
    }

    /**
     * Update points based on the provided parameters
     *
     * @param string $what    What
     * @param int    $points  Points
     * @param int    $userId User ID
     *
     * @return void
     */
    public function updatePoints($what, $points, $userId)
    {
        $this->db->query(
            'UPDATE ' . USERS_STATISTICS . ' SET
                `user_statistic_' . $what . "_points` = '" . $points . "'
            WHERE `user_statistic_user_id` = '" . $userId . "'"
        );
    }

    /**
     * Fetch all users statistics
     *
     * @return array
     */
    public function getAllUserStatsData()
    {
        return $this->db->queryFetchAll(
            'SELECT
                us.`user_statistic_user_id`,
                us.`user_statistic_technology_rank`,
                us.`user_statistic_technology_points`,
                us.`user_statistic_buildings_rank`,
                us.`user_statistic_buildings_points`,
                us.`user_statistic_defenses_rank`,
                us.`user_statistic_defenses_points`,
                us.`user_statistic_ships_rank`,
                us.`user_statistic_ships_points`,
                us.`user_statistic_total_rank`,
                (
                    us.`user_statistic_buildings_points`
                    + us.`user_statistic_defenses_points`
                    + us.`user_statistic_ships_points`
                    + us.`user_statistic_technology_points`
                ) AS total_points
            FROM ' . USERS_STATISTICS . ' us
            INNER JOIN ' . USERS . ' AS u
                ON us.`user_statistic_user_id` = u.`user_id` AND u.`user_authlevel` <= ' . Options::getInstance()->get('stat_admin_level') . '
            ORDER BY us.`user_statistic_user_id` ASC;'
        );
    }

    /**
     * Fetch all alliance statistics
     *
     * @return array
     */
    public function getAllAllianceStatsData()
    {
        return $this->db->queryFetchAll(
            'SELECT a.`alliance_id`,
            ass.alliance_statistic_technology_rank,
            ass.alliance_statistic_buildings_rank,
            ass.alliance_statistic_defenses_rank,
            ass.alliance_statistic_ships_rank,
            ass.alliance_statistic_total_rank,
            SUM(us.user_statistic_buildings_points) AS buildings_points,
            SUM(us.user_statistic_defenses_points) AS defenses_points,
            SUM(us.user_statistic_ships_points) AS ships_points,
            SUM(us.user_statistic_technology_points) AS technology_points,
            SUM(us.user_statistic_total_points) AS total_points
            FROM ' . ALLIANCE . ' AS a
            INNER JOIN ' . USERS . ' AS u
                ON a.`alliance_id` = u.`user_ally_id` AND u.`user_authlevel` <= ' . Options::getInstance()->get('stat_admin_level') . '
                INNER JOIN ' . USERS_STATISTICS . ' AS us ON us.`user_statistic_user_id` = u.`user_id`
                INNER JOIN ' . ALLIANCE_STATISTICS . ' AS ass ON ass.`alliance_statistic_alliance_id` = a.`alliance_id`
            GROUP BY alliance_id'
        );
    }

    /**
     * Run a single query based on a provided query string
     *
     * @param string $query Query
     *
     * @return void
     */
    public function runSingleQuery($query)
    {
        $this->db->query($query);
    }
}
