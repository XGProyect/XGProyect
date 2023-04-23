<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

class Resources extends Model
{
    /**
     * Update current planet
     *
     * @param array $planet
     * @param string $sub_query
     * @return void
     */
    public function updateCurrentPlanet(array $planet, string $sub_query): void
    {
        $this->db->query(
            'UPDATE `' . PLANETS . "` SET
                `planet_id` = '" . $planet['planet_id'] . "'
                $sub_query
                WHERE `planet_id` = '" . $planet['planet_id'] . "';"
        );
    }
}
