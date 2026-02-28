<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Requests\Admin\ResetRequest;
use App\Services\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Xgp\App\Core\Options;
use Xgp\App\Libraries\PlanetLib;

class ResetController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.reset');
    }

    public function reset(ResetRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $data = $request->validated();

        if (isset($data['resetall'])) {
            $this->resetAll();

            return redirect()->route('admin.reset')->with('success', __('admin/reset.re_reset_excess'));
        }

        isset($data['defenses'])   && $this->resetDefenses();
        isset($data['ships'])      && $this->resetShips();
        isset($data['h_d'])        && $this->resetShipyardQueues();
        isset($data['edif_p'])     && $this->resetPlanetBuildings();
        isset($data['edif_l'])     && $this->resetMoonBuildings();
        isset($data['edif'])       && $this->resetBuildingsQueues();
        isset($data['inves'])      && $this->resetResearch();
        isset($data['inves_c'])    && $this->resetResearchQueues();
        isset($data['ofis'])       && $this->resetOfficiers();
        isset($data['dark'])       && $this->resetDarkMatter();
        isset($data['resources'])  && $this->resetResources();
        isset($data['notes'])      && $this->resetNotes();
        isset($data['rw'])         && $this->resetReports();
        isset($data['friends'])    && $this->resetFriends();
        isset($data['alliances'])  && $this->resetAlliances();
        isset($data['fleets'])     && $this->resetFleets();
        isset($data['banneds'])    && $this->resetBanned();
        isset($data['messages'])   && $this->resetMessages();
        isset($data['statpoints']) && $this->resetStatistics();
        isset($data['moons'])      && $this->resetMoons();

        return redirect()->route('admin.reset')->with('success', __('admin/reset.re_reset_excess'));
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
        // FK checks must live outside the transaction — SET is a session variable,
        // not rolled back by MySQL on transaction rollback.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            DB::transaction(function () {
                // Use DELETE (DML) instead of TRUNCATE (DDL) so the whole
                // operation can be rolled back if anything fails.
                DB::table('alliance')->delete();
                DB::table('alliance_statistics')->delete();
                DB::table('acs')->delete();
                DB::table('acs_members')->delete();
                DB::table('fleets')->delete();
                DB::table('buddys')->delete();
                DB::table('messages')->delete();
                DB::table('notes')->delete();
                DB::table('reports')->delete();

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

                DB::table('planets')->delete();
                DB::table('buildings')->delete();
                DB::table('defenses')->delete();
                DB::table('ships')->delete();
                DB::table('preferences')->delete();
                DB::table('premium')->delete();
                DB::table('research')->delete();
                DB::table('sessions')->delete();

                DB::table('users')->update([
                    'lastip' => '',
                    'ip_at_reg' => '',
                    'agent' => '',
                    'current_page' => '',
                    'fleet_shortcuts' => '',
                    'ally_id' => 0,
                    'ally_request' => 0,
                    'ally_request_text' => null,
                    'ally_register_time' => 0,
                    'ally_rank_id' => 0,
                    'onlinetime' => time(),
                ]);

                $creator = new PlanetLib();

                foreach (User::all() as $user) {
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
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
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
