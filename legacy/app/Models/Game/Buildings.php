<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Buildings extends Model
{
    public function updatePlanetBuildingQueue(array $planet): void
    {
        $this->db->query(
            'UPDATE `' . PLANETS . "` SET
                `planet_b_building` = '" . $planet['planet_b_building'] . "',
                `planet_b_building_id` = '" . $planet['planet_b_building_id'] . "'
            WHERE `planet_id` = '" . $planet['planet_id'] . "';"
        );
    }
}
