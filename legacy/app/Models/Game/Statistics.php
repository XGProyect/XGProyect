<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Statistics
{
    use PreparesLegacySql;

    public function countAlliances(): int
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT COUNT(`alliance_id`) AS `count` FROM `' . ALLIANCE . '`;'
            )
        );

        return $row !== null ? (int) $row->count : 0;
    }

    public function getAlliances(string $order, int $start): ?array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        s.*,
                        a.`alliance_id`,
                        a.`alliance_tag`,
                        a.`alliance_name`,
                        a.`alliance_request_notallow`,
                        (
                            SELECT
                                COUNT(id) AS `ally_members`
                            FROM `' . USERS . '`
                            WHERE `ally_id` = a.`alliance_id`
                        ) AS `ally_members`
                    FROM `' . ALLIANCE_STATISTICS . '` AS s
                    INNER JOIN  `' . ALLIANCE . '` AS a ON a.`alliance_id` = s.`alliance_statistic_alliance_id`
                    ORDER BY `alliance_statistic_' . $order . '` DESC, `alliance_statistic_total_rank` ASC
                    LIMIT ' . $start . ',100;'
                )
            )
        );
    }

    public function getUsers(string $order, int $start): ?array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        s.*,
                        u.`id`,
                        u.`name`,
                        u.`ally_id`,
                        a.`alliance_name`
                    FROM `' . USERS_STATISTICS . '` as s
                    INNER JOIN `' . USERS . '` as u ON u.`id` = s.`user_statistic_user_id`
                    LEFT JOIN `' . ALLIANCE . '` AS a ON a.`alliance_id` = u.`ally_id`
                    WHERE `authlevel` <= ' . app(SettingsService::class)->getInt('stat_admin_level') . '
                    ORDER BY `user_statistic_' . $order . '` DESC, `user_statistic_total_rank` ASC
                    LIMIT ' . $start . ',100;'
                )
            )
        );
    }
}
