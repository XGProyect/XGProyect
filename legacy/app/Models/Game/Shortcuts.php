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
class Shortcuts
{
    use PreparesLegacySql;

    public function updateShortcuts(int $userId, string $shortcuts): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . USERS . "` u SET
                    u.`fleet_shortcuts` = '" . $shortcuts . "'
                WHERE u.`id` = '" . $userId . "'"
            )
        );
    }
}
