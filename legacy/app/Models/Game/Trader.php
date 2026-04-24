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
class Trader
{
    use PreparesLegacySql;

    public function refillStorage(int $dark_matter, string $resource, float $amount, int $userId, int $planet_id): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PREMIUM . '` pr, `' . PLANETS . "` p SET
                pr.`premium_dark_matter` = pr.`premium_dark_matter` - '" . $dark_matter . "',
                p.`planet_" . $resource . "` = '" . $amount . "'
                WHERE pr.`premium_user_id` = '" . $userId . "'
                    AND p.`planet_id` = '" . $planet_id . "';"
            )
        );
    }
}
