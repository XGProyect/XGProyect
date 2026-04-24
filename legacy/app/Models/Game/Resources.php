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
class Resources
{
    use PreparesLegacySql;

    public function updateCurrentPlanet(array $planet, string $sub_query): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PLANETS . "` SET
                    `planet_id` = '" . $planet['planet_id'] . "'
                    $sub_query
                    WHERE `planet_id` = '" . $planet['planet_id'] . "';"
            )
        );
    }
}
