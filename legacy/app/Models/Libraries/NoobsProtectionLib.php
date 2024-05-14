<?php

declare(strict_types=1);

namespace Xgp\App\Models\Libraries;

use Xgp\App\Core\Model;
use Xgp\App\Core\Options;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class NoobsProtectionLib extends Model
{
    public function readAllConfigs(): array
    {
        return Options::getInstance()->get();
    }

    public function returnBothPartiesPoints(int $current_user_id, int $other_user_id): array
    {
        return $this->db->queryFetch(
            'SELECT
                (
                    SELECT
                        `user_statistic_total_points`
                    FROM `' . USERS_STATISTICS . '`
                    WHERE `user_statistic_user_id` = ' . $current_user_id . '
                ) AS user_points,
                (
                    SELECT
                        `user_statistic_total_points`
                    FROM `' . USERS_STATISTICS . '`
                    WHERE `user_statistic_user_id` = ' . $other_user_id . '
                ) AS target_points'
        );
    }
}
