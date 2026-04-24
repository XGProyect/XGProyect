<?php

declare(strict_types=1);

namespace Xgp\App\Models\Libraries;

use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class StatisticsLibrary
{
    use PreparesLegacySql;

    public function getResearchToUpdate(int $userId): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT * FROM `' . RESEARCH . "` ttu WHERE ttu.research_user_id = '" . $userId . "';"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function getPlanetElementToUpdate($what, $planet_id)
    {
        $row = DB::selectOne(
            'SELECT * FROM `' . config('DB_PREFIX') . $what . '` ttu
            WHERE ttu.' . rtrim($what, 's') . "_planet_id = '" . $planet_id . "';"
        );

        return $row !== null ? (array) $row : [];
    }

    public function updatePoints($what, $points, $userId)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . USERS_STATISTICS . ' SET
                    `user_statistic_' . $what . "_points` = '" . $points . "'
                WHERE `user_statistic_user_id` = '" . $userId . "'"
            )
        );
    }

    public function getAllUserStatsData()
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
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
                        ON us.`user_statistic_user_id` = u.`id` AND u.`authlevel` <= ' . app(SettingsService::class)->getInt('stat_admin_level') . '
                    ORDER BY us.`user_statistic_user_id` ASC;'
                )
            )
        );
    }

    public function getAllAllianceStatsData()
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
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
                        ON a.`alliance_id` = u.`ally_id` AND u.`authlevel` <= ' . app(SettingsService::class)->getInt('stat_admin_level') . '
                        INNER JOIN ' . USERS_STATISTICS . ' AS us ON us.`user_statistic_user_id` = u.`id`
                        INNER JOIN ' . ALLIANCE_STATISTICS . ' AS ass ON ass.`alliance_statistic_alliance_id` = a.`alliance_id`
                    GROUP BY alliance_id'
                )
            )
        );
    }

    public function runSingleQuery($query)
    {
        DB::statement($this->prepareSql($query));
    }
}
