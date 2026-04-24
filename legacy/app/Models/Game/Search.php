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
class Search
{
    use PreparesLegacySql;

    public function getResultsByPlayerName(string $playerName): array
    {
        if (!empty($playerName)) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            u.`id`,
                            u.`name`,
                            u.`authlevel`,
                            p.`planet_name`,
                            p.`planet_galaxy`,
                            p.`planet_system`,
                            p.`planet_planet`,
                            s.`user_statistic_total_rank` AS `user_rank`,
                            a.`alliance_id`,
                            a.`alliance_name`
                        FROM `' . USERS . '` AS u
                            INNER JOIN `' . USERS_STATISTICS . '` AS s ON s.`user_statistic_user_id` = u.`id`
                            INNER JOIN `' . PLANETS . '` AS p ON p.`planet_id` = u.`home_planet_id`
                            LEFT JOIN `' . ALLIANCE . '` AS a ON a.alliance_id = u.`ally_id`
                        WHERE u.`name` LIKE ?
                        LIMIT ' . MAX_SEARCH_RESULTS . ';'
                    ),
                    ['%' . $playerName . '%']
                )
            );
        }

        return [];
    }

    public function getResultsByAllianceTag(string $allianceTag): array
    {
        if (!empty($allianceTag)) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            a.`alliance_id`,
                            a.`alliance_name`,
                            a.`alliance_tag`,
                            a.`alliance_request_notallow` AS `alliance_requests`,
                            s.`alliance_statistic_total_points` AS `alliance_points`,
                            (SELECT
                                COUNT(id) AS `ally_members`
                                FROM `' . USERS . '`
                                WHERE `ally_id` = a.`alliance_id`
                            ) AS `alliance_members`
                        FROM `' . ALLIANCE . '` AS a
                            LEFT JOIN `' . ALLIANCE_STATISTICS . '` AS s ON a.`alliance_id` = s.`alliance_statistic_alliance_id`
                        WHERE (a.alliance_name LIKE ?)
                            OR (a.alliance_tag LIKE ?)
                        LIMIT ' . MAX_SEARCH_RESULTS . ';'
                    ),
                    ['%' . $allianceTag . '%', '%' . $allianceTag . '%']
                )
            );
        }

        return [];
    }

    public function getResultsByPlanetName(string $planetName): array
    {
        if (!empty($planetName)) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            u.`id`,
                            u.`name`,
                            u.`authlevel`,
                            p.`planet_name`,
                            p.`planet_galaxy`,
                            p.`planet_system`,
                            p.`planet_planet`,
                            s.`user_statistic_total_rank` AS `user_rank`,
                            a.`alliance_id`,
                            a.`alliance_name`
                        FROM `' . USERS . '` AS u
                            INNER JOIN `' . USERS_STATISTICS . '` AS s ON s.`user_statistic_user_id` = u.`id`
                            INNER JOIN `' . PLANETS . '` AS p ON p.`planet_user_id` = u.`id`
                            LEFT JOIN `' . ALLIANCE . '` AS a ON a.`alliance_id` = u.`ally_id`
                        WHERE p.`planet_name` LIKE ?
                        LIMIT ' . MAX_SEARCH_RESULTS . ';'
                    ),
                    ['%' . $planetName . '%']
                )
            );
        }

        return [];
    }
}
