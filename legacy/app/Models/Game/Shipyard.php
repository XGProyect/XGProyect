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
class Shipyard
{
    use PreparesLegacySql;

    public function insertItemsToBuild(array $resources, string $shipyardQueue, $planetId): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . PLANETS . " AS p SET
                    p.`planet_b_hangar_id` = CONCAT(p.`planet_b_hangar_id`, '" . $shipyardQueue . "'),
                    p.`planet_metal` = '" . $resources['metal'] . "',
                    p.`planet_crystal` = '" . $resources['crystal'] . "',
                    p.`planet_deuterium` = '" . $resources['deuterium'] . "'
                WHERE p.`planet_id` = '" . $planetId . "';"
            )
        );
    }
}
