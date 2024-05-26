<?php

declare(strict_types=1);

namespace Xgp\App\Models\Adm;

use Exception;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Model;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\PlanetLib;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Reset extends Model
{
    public function resetDefenses(): void
    {
        $this->db->query(
            'UPDATE `' . DEFENSES . "` SET
            `defense_rocket_launcher` = '0',
            `defense_light_laser` = '0',
            `defense_heavy_laser` = '0',
            `defense_gauss_cannon` = '0',
            `defense_ion_cannon` = '0',
            `defense_plasma_turret` = '0',
            `defense_small_shield_dome` = '0',
            `defense_large_shield_dome` = '0',
            `defense_anti-ballistic_missile` = '0',
            `defense_interplanetary_missile` = '0'"
        );
    }

    /**
     * Set to 0 all planet's ships
     *
     * @return void
     */
    public function resetShips(): void
    {
        $this->db->query(
            'UPDATE `' . SHIPS . "` SET
                `ship_small_cargo_ship` = '0',
                `ship_big_cargo_ship` = '0',
                `ship_light_fighter` = '0',
                `ship_heavy_fighter` = '0',
                `ship_cruiser` = '0',
                `ship_battleship` = '0',
                `ship_colony_ship` = '0',
                `ship_recycler` = '0',
                `ship_espionage_probe` = '0',
                `ship_bomber` = '0',
                `ship_solar_satellite` = '0',
                `ship_destroyer` = '0',
                `ship_deathstar` = '0',
                `ship_battlecruiser` = '0'"
        );
    }

    /**
     * Clears shipyard queues
     *
     * @return void
     */
    public function resetShipyardQueues(): void
    {
        $this->db->query(
            'UPDATE `' . PLANETS . "` SET
                `planet_b_hangar` = '0',
                `planet_b_hangar_id` = ''"
        );
    }

    /**
     * Set to 0 all planet's buildings
     *
     * @return void
     */
    public function resetPlanetBuildings(): void
    {
        $this->resetBuildingsByType(1);
    }

    /**
     * Set to 0 all moon's buildings
     *
     * @return void
     */
    public function resetMoonBuildings(): void
    {
        $this->resetBuildingsByType(3);
    }

    /**
     * Clears buildings queues
     *
     * @return void
     */
    public function resetBuildingsQueues(): void
    {
        $this->db->query(
            'UPDATE `' . PLANETS . "` SET
                `planet_b_building` = '0',
                `planet_b_building_id` = ''"
        );
    }

    /**
     * Set to 0 all research
     *
     * @return void
     */
    public function resetResearch(): void
    {
        $this->db->query(
            'UPDATE `' . RESEARCH . "` SET
                `research_espionage_technology` = '0',
                `research_computer_technology` = '0',
                `research_weapons_technology` = '0',
                `research_shielding_technology` = '0',
                `research_armour_technology` = '0',
                `research_energy_technology` = '0',
                `research_hyperspace_technology` = '0',
                `research_combustion_drive` = '0',
                `research_impulse_drive` = '0',
                `research_hyperspace_drive` = '0',
                `research_laser_technology` = '0',
                `research_ionic_technology` = '0',
                `research_plasma_technology` = '0',
                `research_intergalactic_research_network` = '0',
                `research_astrophysics` = '0',
                `research_graviton_technology` = '0'"
        );
    }

    /**
     * Clears research queues
     *
     * @return void
     */
    public function resetResearchQueues(): void
    {
        $this->db->query(
            'UPDATE `' . PLANETS . "` SET
                `planet_b_tech` = '0',
                `planet_b_tech_id` = '0'"
        );

        $this->db->query(
            'UPDATE `' . RESEARCH . "` SET
                `research_current_research` = '0'"
        );
    }

    /**
     * Set to 0 all user's officiers
     *
     * @return void
     */
    public function resetOfficiers(): void
    {
        $this->db->query(
            'UPDATE `' . PREMIUM . "` SET
                `premium_officier_commander` = '0',
                `premium_officier_admiral` = '0',
                `premium_officier_engineer` = '0',
                `premium_officier_geologist` = '0',
                `premium_officier_technocrat` = '0'"
        );
    }

    /**
     * Set to 0 all user's dark matter
     *
     * @return void
     */
    public function resetDarkMatter(): void
    {
        $this->db->query(
            'UPDATE `' . PREMIUM . "` SET
                `premium_dark_matter` = '0'"
        );
    }

    /**
     * Set to 0 all planets metal, crystal and deuterium
     *
     * @return void
     */
    public function resetResources(): void
    {
        $this->db->query(
            'UPDATE `' . PLANETS . "` SET
                `planet_metal` = '0',
                `planet_crystal` = '0',
                `planet_deuterium` = '0'"
        );
    }

    public function resetNotes(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('notes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function resetReports(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('reports')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function resetFriends(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('buddys')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function resetAlliances(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('alliance')->truncate();
        DB::table('alliance_statistics')->truncate();
        $this->db->query(
            'UPDATE `' . USERS . "` SET
                `ally_id` = '0',
                `ally_request` = '0',
                `ally_request_text` = 'NULL',
                `ally_register_time` = '0',
                `ally_rank_id` = '0'"
        );
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function resetFleets(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('acs')->truncate();
        DB::table('acs_members')->truncate();
        DB::table('fleets')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function resetBanned(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('bans')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function resetMessages(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('messages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Set to 0 all the statistics
     */
    public function resetStatistics(): void
    {
        $this->db->query(
            'UPDATE `' . USERS_STATISTICS . "` SET
            `user_statistic_buildings_old_rank` = '0',
            `user_statistic_buildings_rank` = '0',
            `user_statistic_defenses_old_rank` = '0',
            `user_statistic_defenses_rank` = '0',
            `user_statistic_ships_old_rank` = '0',
            `user_statistic_ships_rank` = '0',
            `user_statistic_technology_old_rank` = '0',
            `user_statistic_technology_rank` = '0',
            `user_statistic_total_old_rank` = '0',
            `user_statistic_total_rank` = '0',
            `user_statistic_update_time` = '0',
            `user_statistic_buildings_points` = '0',
            `user_statistic_defenses_points` = '0',
            `user_statistic_ships_points` = '0',
            `user_statistic_technology_points` = '0',
            `user_statistic_total_points` = '0'"
        );

        DB::table('alliance_statistics')->truncate();
    }

    /**
     * Deletes all moons
     *
     * @return void
     */
    public function resetMoons(): void
    {
        $this->db->query('DELETE FROM `' . PLANETS . "` WHERE `planet_type` = '3'");
    }

    /**
     * Reset the whole server
     *
     * @return void
     */
    public function resetAll(): void
    {
        try {
            $this->db->beginTransaction();

            // initial resets
            $this->resetAlliances();
            //$this->resetBanned();
            $this->resetFleets();
            $this->resetFriends();
            $this->resetMessages();
            $this->resetNotes();
            $this->resetReports();
            $this->resetStatistics();

            // other resets
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // planets and their data
            DB::table('planets')->truncate();
            DB::table('buildings')->truncate();
            DB::table('defenses')->truncate();
            DB::table('ships')->truncate();
            // users data
            DB::table('preferences')->truncate();
            DB::table('premium')->truncate();
            DB::table('research')->truncate();
            DB::table('sessions')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // new creator
            $creator = $this->newCreator();

            $allUsers = $this->db->query(
                'SELECT
                    `id`,
                    `name`,
                    `password`,
                    `email`,
                    `authlevel`,
                    `galaxy`,
                    `system`,
                    `planet`,
                    `register_time`
                FROM `' . USERS . '`;'
            );

            while ($user = $this->db->fetchAssoc($allUsers)) {
                $this->db->query(
                    'UPDATE `' . USERS . "` SET
                        `name` = '" . $user['name'] . "',
                        `password` = '" . $user['password'] . "',
                        `email` = '" . $user['email'] . "',
                        `lastip` = '',
                        `ip_at_reg` = '',
                        `agent` = '',
                        `current_page` = '',
                        `fleet_shortcuts` = '',
                        `authlevel` = '" . $user['authlevel'] . "',
                        `home_planet_id` = '0',
                        `galaxy` = '" . $user['galaxy'] . "',
                        `system` = '" . $user['system'] . "',
                        `planet` = '" . $user['planet'] . "',
                        `current_planet` = '0',
                        `register_time` = '" . $user['register_time'] . "',
                        `onlinetime` = '" . time() . "',
                        `ally_id` = '0',
                        `ally_request` = '0',
                        `ally_request_text` = NULL,
                        `ally_register_time` = '0',
                        `ally_rank_id` = '0'
                    WHERE `id` = '" . $user['id'] . "';"
                );

                $this->db->query(
                    'INSERT INTO `' . RESEARCH . "` SET
                        `research_user_id` = '" . $user['id'] . "';"
                );

                $this->db->query(
                    'INSERT INTO `' . PREMIUM . "` (`premium_user_id`, `premium_dark_matter`)
                    VALUES('" . $user['id'] . "', '" . Options::getInstance()->get('registration_dark_matter') . "');"
                );

                $this->db->query(
                    'INSERT INTO `' . PREFERENCES . "` SET
                        `preference_user_id` = '" . $user['id'] . "';"
                );

                $creator->setNewPlanet(
                    (int) $user['galaxy'],
                    (int) $user['system'],
                    (int) $user['planet'],
                    $user['id'],
                    '',
                    true
                );

                $lastPlanetId = $this->db->insertId();

                $this->db->query(
                    'UPDATE `' . USERS . "` SET
                    `home_planet_id` = '" . $lastPlanetId . "',
                    `current_planet` = '" . $lastPlanetId . "'
                    WHERE `id` = '" . $user['id'] . "';"
                );
            }

            $this->db->commitTransaction();
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
        }
    }

    /**
     * Reset buildings by type
     *
     * @param integer $planet_type
     *
     * @return void
     */
    private function resetBuildingsByType(int $planet_type): void
    {
        $this->db->query(
            'UPDATE `' . BUILDINGS . '` AS b
                INNER JOIN `' . PLANETS . "` AS p ON b.`building_planet_id` = p.`planet_id` SET
                `building_metal_mine` = '0',
                `building_crystal_mine` = '0',
                `building_deuterium_sintetizer` = '0',
                `building_solar_plant` = '0',
                `building_fusion_reactor` = '0',
                `building_robot_factory` = '0',
                `building_nano_factory` = '0',
                `building_hangar` = '0',
                `building_metal_store` = '0',
                `building_crystal_store` = '0',
                `building_deuterium_tank` = '0',
                `building_laboratory` = '0',
                `building_terraformer` = '0',
                `building_ally_deposit` = '0',
                `building_missile_silo` = '0',
                `building_mondbasis` = '0',
                `building_phalanx` = '0',
                `building_jump_gate` = '0'
                WHERE p.`planet_type` = '" . $planet_type . "'"
        );
    }

    /**
     * Get a new creator
     *
     * @return PlanetLib
     */
    private function newCreator(): PlanetLib
    {
        return new PlanetLib();
    }
}
