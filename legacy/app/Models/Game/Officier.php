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
class Officier
{
    use PreparesLegacySql;

    public function setPremium(int $userId, int $price, string $officier, int $time_to_add): void
    {
        if ($userId > 0) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PREMIUM . "` SET
                        `premium_dark_matter` = `premium_dark_matter` - '" . $price . "',
                        `" . $officier . "` = '" . $time_to_add . "'
                    WHERE `premium_user_id` = '" . $userId . "';"
                )
            );
        }
    }
}
