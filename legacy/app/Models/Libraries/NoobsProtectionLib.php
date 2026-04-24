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
class NoobsProtectionLib
{
    use PreparesLegacySql;

    public function readAllConfigs(): array
    {
        return app(SettingsService::class)->all();
    }

    public function returnBothPartiesPoints(int $current_user_id, int $other_user_id): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
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
            )
        );

        return $row !== null ? (array) $row : [];
    }
}
