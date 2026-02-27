<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\PlanetLib;

class ResetController extends BaseController
{
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->runAction();

        Template::legacyView(
            'admin.reset'
        );
    }

    private function runAction(): void
    {
        if ($_POST) {
            if (!isset($_POST['resetall'])) {
                // reset defenses
                if (isset($_POST['defenses']) && $_POST['defenses'] == 'on') {
                    $this->resetDefenses();
                }

                // reset ships
                if (isset($_POST['ships']) && $_POST['ships'] == 'on') {
                    $this->resetShips();
                }

                // reset shipyard queues
                if (isset($_POST['h_d']) && $_POST['h_d'] == 'on') {
                    $this->resetShipyardQueues();
                }

                // reset planet buildings
                if (isset($_POST['edif_p']) && $_POST['edif_p'] == 'on') {
                    $this->resetPlanetBuildings();
                }

                // reset moon buildings
                if (isset($_POST['edif_l']) && $_POST['edif_l'] == 'on') {
                    $this->resetMoonBuildings();
                }

                // reset buildings queues
                if (isset($_POST['edif']) && $_POST['edif'] == 'on') {
                    $this->resetBuildingsQueues();
                }

                // reset research
                if (isset($_POST['inves']) && $_POST['inves'] == 'on') {
                    $this->resetResearch();
                }

                // reset research queues
                if (isset($_POST['inves_c']) && $_POST['inves_c'] == 'on') {
                    $this->resetResearchQueues();
                }

                // reset officiers
                if (isset($_POST['ofis']) && $_POST['ofis'] == 'on') {
                    $this->resetOfficiers();
                }

                // reset dark matter
                if (isset($_POST['dark']) && $_POST['dark'] == 'on') {
                    $this->resetDarkMatter();
                }

                // reset resources
                if (isset($_POST['resources']) && $_POST['resources'] == 'on') {
                    $this->resetResources();
                }

                // reset notes
                if (isset($_POST['notes']) && $_POST['notes'] == 'on') {
                    $this->resetNotes();
                }

                // reset reports
                if (isset($_POST['rw']) && $_POST['rw'] == 'on') {
                    $this->resetReports();
                }

                // reset friends
                if (isset($_POST['friends']) && $_POST['friends'] == 'on') {
                    $this->resetFriends();
                }

                // reset alliances
                if (isset($_POST['alliances']) && $_POST['alliances'] == 'on') {
                    $this->resetAlliances();
                }

                // reset fleets
                if (isset($_POST['fleets']) && $_POST['fleets'] == 'on') {
                    $this->resetFleets();
                }

                // reset banned
                if (isset($_POST['banneds']) && $_POST['banneds'] == 'on') {
                    $this->resetBanned();
                }

                // reset messages
                if (isset($_POST['messages']) && $_POST['messages'] == 'on') {
                    $this->resetMessages();
                }

                // reset statistics
                if (isset($_POST['statpoints']) && $_POST['statpoints'] == 'on') {
                    $this->resetStatistics();
                }

                // reset moons
                if (isset($_POST['moons']) && $_POST['moons'] == 'on') {
                    $this->resetMoons();
                }
            } else {
                // reset everything
                $this->resetAll();
            }

            session()->flash('success', __('admin/reset.re_reset_excess'));
        }
    }

    private function resetDefenses(): void
    {
        DB::table('defenses')->update([
            'defense_rocket_launcher' => 0,
            'defense_light_laser' => 0,
            'defense_heavy_laser' => 0,
            'defense_gauss_cannon' => 0,
            'defense_ion_cannon' => 0,
            'defense_plasma_turret' => 0,
            'defense_small_shield_dome' => 0,
            'defense_large_shield_dome' => 0,
            'defense_anti-ballistic_missile' => 0,
            'defense_interplanetary_missile' => 0,
        ]);
    }

    private function resetShips(): void
    {
        DB::table('ships')->update([
            'ship_small_cargo_ship' => 0,
            'ship_big_cargo_ship' => 0,
            'ship_light_fighter' => 0,
            'ship_heavy_fighter' => 0,
            'ship_cruiser' => 0,
            'ship_battleship' => 0,
            'ship_colony_ship' => 0,
            'ship_recycler' => 0,
            'ship_espionage_probe' => 0,
            'ship_bomber' => 0,
            'ship_solar_satellite' => 0,
            'ship_destroyer' => 0,
            'ship_deathstar' => 0,
            'ship_battlecruiser' => 0,
        ]);
    }

    private function resetShipyardQueues(): void
    {
        DB::table('planets')->update([
            'planet_b_hangar' => 0,
            'planet_b_hangar_id' => '',
        ]);
    }

    private function resetPlanetBuildings(): void
    {
        $this->resetBuildingsByType(1);
    }

    private function resetMoonBuildings(): void
    {
        $this->resetBuildingsByType(3);
    }

    private function resetBuildingsQueues(): void
    {
        DB::table('planets')->update([
            'planet_b_building' => 0,
            'planet_b_building_id' => '',
        ]);
    }

    private function resetResearch(): void
    {
        DB::table('research')->update([
            'research_espionage_technology' => 0,
            'research_computer_technology' => 0,
            'research_weapons_technology' => 0,
            'research_shielding_technology' => 0,
            'research_armour_technology' => 0,
            'research_energy_technology' => 0,
            'research_hyperspace_technology' => 0,
            'research_combustion_drive' => 0,
            'research_impulse_drive' => 0,
            'research_hyperspace_drive' => 0,
            'research_laser_technology' => 0,
            'research_ionic_technology' => 0,
            'research_plasma_technology' => 0,
            'research_intergalactic_research_network' => 0,
            'research_astrophysics' => 0,
            'research_graviton_technology' => 0,
        ]);
    }

    private function resetResearchQueues(): void
    {
        DB::table('planets')->update([
            'planet_b_tech' => 0,
            'planet_b_tech_id' => 0,
        ]);

        DB::table('research')->update([
            'research_current_research' => 0,
        ]);
    }

    private function resetOfficiers(): void
    {
        DB::table('premium')->update([
            'premium_officier_commander' => 0,
            'premium_officier_admiral' => 0,
            'premium_officier_engineer' => 0,
            'premium_officier_geologist' => 0,
            'premium_officier_technocrat' => 0,
        ]);
    }

    private function resetDarkMatter(): void
    {
        DB::table('premium')->update(['premium_dark_matter' => 0]);
    }

    private function resetResources(): void
    {
        DB::table('planets')->update([
            'planet_metal' => 0,
            'planet_crystal' => 0,
            'planet_deuterium' => 0,
        ]);
    }

    private function resetNotes(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('notes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function resetReports(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('reports')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function resetFriends(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('buddys')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function resetAlliances(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('alliance')->truncate();
        DB::table('alliance_statistics')->truncate();
        DB::table('users')->update([
            'ally_id' => 0,
            'ally_request' => 0,
            'ally_request_text' => null,
            'ally_register_time' => 0,
            'ally_rank_id' => 0,
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function resetFleets(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('acs')->truncate();
        DB::table('acs_members')->truncate();
        DB::table('fleets')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function resetBanned(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('bans')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function resetMessages(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('messages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function resetStatistics(): void
    {
        DB::table('users_statistics')->update([
            'user_statistic_buildings_old_rank' => 0,
            'user_statistic_buildings_rank' => 0,
            'user_statistic_defenses_old_rank' => 0,
            'user_statistic_defenses_rank' => 0,
            'user_statistic_ships_old_rank' => 0,
            'user_statistic_ships_rank' => 0,
            'user_statistic_technology_old_rank' => 0,
            'user_statistic_technology_rank' => 0,
            'user_statistic_total_old_rank' => 0,
            'user_statistic_total_rank' => 0,
            'user_statistic_update_time' => 0,
            'user_statistic_buildings_points' => 0,
            'user_statistic_defenses_points' => 0,
            'user_statistic_ships_points' => 0,
            'user_statistic_technology_points' => 0,
            'user_statistic_total_points' => 0,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('alliance_statistics')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function resetMoons(): void
    {
        DB::table('planets')->where('planet_type', 3)->delete();
    }

    private function resetAll(): void
    {
        DB::transaction(function () {
            $this->resetAlliances();
            $this->resetFleets();
            $this->resetFriends();
            $this->resetMessages();
            $this->resetNotes();
            $this->resetReports();
            $this->resetStatistics();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('planets')->truncate();
            DB::table('buildings')->truncate();
            DB::table('defenses')->truncate();
            DB::table('ships')->truncate();
            DB::table('preferences')->truncate();
            DB::table('premium')->truncate();
            DB::table('research')->truncate();
            DB::table('sessions')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $creator = new PlanetLib();

            foreach (User::all() as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'lastip' => '',
                    'ip_at_reg' => '',
                    'agent' => '',
                    'current_page' => '',
                    'fleet_shortcuts' => '',
                    'home_planet_id' => 0,
                    'current_planet' => 0,
                    'ally_id' => 0,
                    'ally_request' => 0,
                    'ally_request_text' => null,
                    'ally_register_time' => 0,
                    'ally_rank_id' => 0,
                    'onlinetime' => time(),
                ]);

                DB::table('research')->insert(['research_user_id' => $user->id]);

                DB::table('premium')->insert([
                    'premium_user_id' => $user->id,
                    'premium_dark_matter' => Options::getInstance()->get('registration_dark_matter'),
                ]);

                DB::table('preferences')->insert(['preference_user_id' => $user->id]);

                $creator->setNewPlanet(
                    (int) $user->galaxy,
                    (int) $user->system,
                    (int) $user->planet,
                    $user->id,
                    '',
                    true
                );

                $lastPlanetId = DB::getPdo()->lastInsertId();

                DB::table('users')->where('id', $user->id)->update([
                    'home_planet_id' => $lastPlanetId,
                    'current_planet' => $lastPlanetId,
                ]);
            }
        });
    }

    private function resetBuildingsByType(int $planet_type): void
    {
        DB::table('buildings')
            ->join('planets', 'buildings.building_planet_id', '=', 'planets.planet_id')
            ->where('planets.planet_type', $planet_type)
            ->update([
                'buildings.building_metal_mine' => 0,
                'buildings.building_crystal_mine' => 0,
                'buildings.building_deuterium_sintetizer' => 0,
                'buildings.building_solar_plant' => 0,
                'buildings.building_fusion_reactor' => 0,
                'buildings.building_robot_factory' => 0,
                'buildings.building_nano_factory' => 0,
                'buildings.building_hangar' => 0,
                'buildings.building_metal_store' => 0,
                'buildings.building_crystal_store' => 0,
                'buildings.building_deuterium_tank' => 0,
                'buildings.building_laboratory' => 0,
                'buildings.building_terraformer' => 0,
                'buildings.building_ally_deposit' => 0,
                'buildings.building_missile_silo' => 0,
                'buildings.building_mondbasis' => 0,
                'buildings.building_phalanx' => 0,
                'buildings.building_jump_gate' => 0,
            ]);
    }
}
