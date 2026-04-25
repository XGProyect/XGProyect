<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class NoobsProtectionLib
{
    use PreparesLegacySql;

    private $protection;
    private $protectiontime;
    private $protectionmulti;
    private $allowed_level;

    public function __construct()
    {
        // set configs
        $this->setAllSettings();
    }

    public function setAllSettings(): void
    {
        /** @var SettingsService $settings */
        $settings = app(SettingsService::class);

        $this->protection = $settings->getBool('noobprotection');
        $this->protectiontime = $settings->getInt('noobprotectiontime');
        $this->protectionmulti = $settings->getInt('noobprotectionmulti');
        $this->allowed_level = $settings->getInt('stat_admin_level');
    }

    public function isWeak(int $current_points, int $other_points): bool
    {
        if ($this->protection) {
            if ($this->protectionmulti == 0) {
                $this->protectionmulti = 1;
            }

            if ($current_points > $other_points * $this->protectionmulti) {
                if ($other_points > $this->protectiontime && $this->protectiontime > 0) {
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    public function isStrong(int $current_points, int $other_points): bool
    {
        if ($this->protection) {
            if ($this->protectionmulti == 0) {
                $this->protectionmulti = 1;
            }

            if ($current_points * $this->protectionmulti < $other_points) {
                if ($current_points > $this->protectiontime && $this->protectiontime > 0) {
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    public function returnPoints(int $current_user_id, int $other_user_id): array
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

    public function isRankVisible(int $user_auth_level): bool
    {
        return ($user_auth_level <= $this->allowed_level);
    }
}
