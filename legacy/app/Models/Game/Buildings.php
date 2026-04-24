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
class Buildings
{
    use PreparesLegacySql;

    public function updatePlanetBuildingQueue(array $planet): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . PLANETS . "` SET
                    `planet_b_building` = '" . $planet['planet_b_building'] . "',
                    `planet_b_building_id` = '" . $planet['planet_b_building_id'] . "'
                WHERE `planet_id` = '" . $planet['planet_id'] . "';"
            )
        );
    }
}
