<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use Xgp\App\Core\Enumerators\BuildingsEnumerator;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;

class FacilitiesController extends BuildingsController
{
    protected string $page = 'facilities';

    /** @return int[] */
    protected function setAllowedBuildings(int $planetType): array
    {
        if ($planetType === PlanetTypesEnumerator::PLANET) {
            return $this->registry->facilityBuildings()->keys()->all();
        }

        return array_merge(
            [BuildingsEnumerator::BUILDING_ROBOT_FACTORY, BuildingsEnumerator::BUILDING_HANGAR],
            $this->registry->moonBuildings()->keys()->all()
        );
    }
}
