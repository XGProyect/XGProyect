<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Core\GameObjects\Building;
use Xgp\App\Core\Enumerators\BuildingsEnumerator;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;

class SuppliesController extends BuildingsController
{
    protected string $page = 'supplies';

    /** @return int[] */
    protected function setAllowedBuildings(int $planetType): array
    {
        if ($planetType === PlanetTypesEnumerator::PLANET) {
            return $this->registry->resourceBuildings()->keys()->all();
        }

        return $this->registry->resourceBuildings()
            ->reject(fn (Building $b) => in_array($b->getId(), [
                BuildingsEnumerator::BUILDING_METAL_MINE,
                BuildingsEnumerator::BUILDING_CRYSTAL_MINE,
                BuildingsEnumerator::BUILDING_DEUTERIUM_SINTETIZER,
                BuildingsEnumerator::BUILDING_SOLAR_PLANT,
            ]))
            ->keys()
            ->all();
    }
}
