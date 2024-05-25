<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Research extends Model
{
    public function startNewResearch(array $working_planet, array $current_user): void
    {
        $this->db->query(
            'UPDATE `' . PLANETS . '` AS p, `' . RESEARCH . "` AS r SET
                p.`planet_b_tech_id` = '" . $working_planet['planet_b_tech_id'] . "',
                p.`planet_b_tech` = '" . $working_planet['planet_b_tech'] . "',
                p.`planet_metal` = '" . $working_planet['planet_metal'] . "',
                p.`planet_crystal` = '" . $working_planet['planet_crystal'] . "',
                p.`planet_deuterium` = '" . $working_planet['planet_deuterium'] . "',
                r.`research_current_research` = '" . $current_user['research_current_research'] . "'
            WHERE p.`planet_id` = '" . $working_planet['planet_id'] . "'
                AND r.`research_user_id` = '" . $current_user['id'] . "';"
        );
    }

    public function getPlanetResearching(int $current_research): array
    {
        return $this->db->queryFetch(
            'SELECT
                `planet_id`,
                `planet_name`,
                `planet_b_tech`,
                `planet_b_tech_id`,
                `planet_galaxy`,
                `planet_system`,
                `planet_planet`
            FROM `' . PLANETS . "`
            WHERE `planet_id` = '" . $current_research . "';"
        );
    }
}
