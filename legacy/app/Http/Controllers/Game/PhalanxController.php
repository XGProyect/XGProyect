<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FleetsLib;
use Xgp\App\Libraries\Formulas;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class PhalanxController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $planet = [];

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Galaxy));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();

        $this->buildPage();
    }

    private function buildPage(): void
    {
        /* range */
        $radar_limit_inf = $this->planet['planet_system'] - Formulas::phalanxRange($this->planet['building_phalanx']);
        $radar_limit_sup = $this->planet['planet_system'] + Formulas::phalanxRange($this->planet['building_phalanx']);
        $radar_limit_inf = max($radar_limit_inf, 1);
        $radar_limit_sup = min($radar_limit_sup, MAX_SYSTEM_IN_GALAXY);

        /* input validation */
        $Galaxy = (int) $_GET['galaxy'];
        $System = (int) $_GET['system'];
        $Planet = (int) $_GET['planet'];
        $PlType = (int) $_GET['planettype'];
        /* cheater detection */
        if ($System < $radar_limit_inf or $System > $radar_limit_sup or $Galaxy != $this->planet['planet_galaxy'] or $PlType != PlanetTypesEnumerator::PLANET or $this->planet['planet_type'] != PlanetTypesEnumerator::MOON) {
            Functions::redirect('game.php?page=galaxy');
        }

        $TargetName = '';

        /* main page */
        if ($this->planet['planet_deuterium'] >= 10000) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . "` SET
                        `planet_deuterium` = `planet_deuterium` - '" . PHALANX_COST . "'
                    WHERE `planet_id` = '" . $this->user['current_planet'] . "';"
                )
            );

            $planetRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        `planet_name`,
                        `planet_user_id`
                    FROM `' . PLANETS . "`
                    WHERE `planet_galaxy` = '" . $Galaxy . "' AND
                            `planet_system` = '" . $System . "' AND
                            `planet_planet` = '" . $Planet . "' AND
                            `planet_type` = 1"
                )
            );
            $target_planet_info = $planetRow !== null ? (array) $planetRow : [];

            $TargetID = $target_planet_info['planet_user_id'];
            $TargetName = $target_planet_info['planet_name'];

            $moonRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                        `planet_destroyed`
                    FROM `' . PLANETS . "`
                    WHERE `planet_galaxy` = '" . $Galaxy . "' AND
                            `planet_system` = '" . $System . "' AND
                            `planet_planet` = '" . $Planet . "' AND
                            `planet_type` = 3 "
                )
            );
            $target_moon = $moonRow !== null ? (array) $moonRow : null;

            //if there isn't a moon,
            if ($target_moon === false) {
                $TargetMoonIsDestroyed = true;
            } else {
                $TargetMoonIsDestroyed = (isset($target_moon['planet_destroyed']) && $target_moon['planet_destroyed'] !== 0);
            }

            $FleetToTarget = array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            f.*,
                            po.`planet_name` AS `start_planet_name`,
                            pt.`planet_name` AS `target_planet_name`,
                            uo.`name` AS `start_planet_user`,
                            ut.`name` AS `target_planet_user`
                        FROM `' . FLEETS . '` f
                            INNER JOIN `' . USERS . '` uo
                                ON uo.`id` = f.`fleet_owner`
                            LEFT JOIN `' . USERS . '` ut
                                ON ut.`id` = f.`fleet_target_owner`
                            INNER JOIN `' . PLANETS . '` po
                                ON (
                                    po.planet_galaxy = f.fleet_start_galaxy AND
                                    po.planet_system = f.fleet_start_system AND
                                    po.planet_planet = f.fleet_start_planet AND
                                    po.planet_type = f.fleet_start_type
                                )
                            LEFT JOIN `' . PLANETS . "` pt
                                ON (
                                pt.planet_galaxy = f.fleet_end_galaxy AND
                                pt.planet_system = f.fleet_end_system AND
                                pt.planet_planet = f.fleet_end_planet AND
                                pt.planet_type = f.fleet_end_type
                            )
                            WHERE (
                                (
                                    f.`fleet_start_galaxy` = '" . $Galaxy . "' AND
                                    f.`fleet_start_system` = '" . $System . "' AND
                                    f.`fleet_start_planet` = '" . $Planet . "'
                                )
                                OR
                                (
                                    f.`fleet_end_galaxy` = '" . $Galaxy . "' AND
                                    f.`fleet_end_system` = '" . $System . "' AND
                                    f.`fleet_end_planet` = '" . $Planet . "'
                                )
                            ) ;"
                    )
                )
            );

            $Record = 0;
            $fpage = [];
            foreach ($FleetToTarget as $FleetRow) {
                $Record++;

                $ArrivetoTargetTime = $FleetRow['fleet_start_time'];
                $EndStayTime = $FleetRow['fleet_end_stay'];
                $ReturnTime = $FleetRow['fleet_end_time'];
                $Mission = $FleetRow['fleet_mission'];
                $myFleet = ($FleetRow['fleet_owner'] == $TargetID) ? true : false;
                $FleetRow['fleet_resource_metal'] = 0;
                $FleetRow['fleet_resource_crystal'] = 0;
                $FleetRow['fleet_resource_deuterium'] = 0;
                $isStartedfromThis = $FleetRow['fleet_start_galaxy'] == $Galaxy && $FleetRow['fleet_start_system'] == $System && $FleetRow['fleet_start_planet'] == $Planet;
                $isTheTarget = $FleetRow['fleet_end_galaxy'] == $Galaxy && $FleetRow['fleet_end_system'] == $System && $FleetRow['fleet_end_planet'] == $Planet;

                $fpage[$ArrivetoTargetTime] = '';
                $fpage[$EndStayTime] = '';
                $fpage[$ReturnTime] = '';

                /* 1)the arrive to target fleet table event
                 * you can see start-fleet event only if this is a planet(or destroyed moon)
                 * and if the fleet mission started from this planet is different from hold
                 * or if it's a enemy mission.
                 */
                if ($ArrivetoTargetTime > time()) {
                    //scannig of fleet started planet
                    if ($isStartedfromThis && ($FleetRow['fleet_start_type'] == 1 || ($FleetRow['fleet_start_type'] == 3 && $TargetMoonIsDestroyed))) {
                        if ($Mission != 4) {
                            $Label = 'fs';
                            $fpage[$ArrivetoTargetTime] .= "\n" . FleetsLib::flyingFleetsTable($FleetRow, 0, $myFleet, $Label, $Record, $this->user);
                        }
                    }
                    //scanning of destination fleet planet
                    elseif (!$isStartedfromThis && ($FleetRow['fleet_end_type'] == 1 || ($FleetRow['fleet_end_type'] == 3 && $TargetMoonIsDestroyed))) {
                        $Label = 'fs';
                        $fpage[$ArrivetoTargetTime] .= "\n" . FleetsLib::flyingFleetsTable($FleetRow, 0, $myFleet, $Label, $Record, $this->user);
                    }
                }
                /* 2)the stay fleet table event
                 * you can see stay-fleet event only if the target is a planet(or destroyed moon) and is the targetPlanet
                 */
                if ($EndStayTime > time() && $Mission == 5 && ($FleetRow['fleet_end_type'] == 1 || ($FleetRow['fleet_end_type'] == 3 && $TargetMoonIsDestroyed)) && $isTheTarget) {
                    $Label = 'ft';
                    $fpage[$EndStayTime] .= "\n" . FleetsLib::flyingFleetsTable($FleetRow, 1, $myFleet, $Label, $Record, $this->user);
                }
                /* 3)the return fleet table event
                 * you can see the return fleet if this is the started planet(or destroyed moon)
                 * but no if it is a hold mission or mip
                 */
                if ($ReturnTime > time() && $Mission != 4 && $Mission != 10 && $isStartedfromThis && ($FleetRow['fleet_start_type'] == 1 || ($FleetRow['fleet_start_type'] == 3 && $TargetMoonIsDestroyed))) {
                    $Label = 'fe';
                    $fpage[$ReturnTime] .= "\n" . FleetsLib::flyingFleetsTable($FleetRow, 2, $myFleet, $Label, $Record, $this->user);
                }
            }
            ksort($fpage);
            $Fleets = '';
            foreach ($fpage as $FleetTime => $FleetContent) {
                $Fleets .= $FleetContent . "\n";
            }

            $parse['phl_fleets_table'] = $Fleets;
            $parse['phl_er_deuter'] = '';
        } else {
            $parse['phl_fleets_table'] = '';
            $parse['phl_er_deuter'] = __('game/phalanx.px_no_deuterium');
        }

        $parse['phl_pl_galaxy'] = $Galaxy;
        $parse['phl_pl_system'] = $System;
        $parse['phl_pl_place'] = $Planet;
        $parse['phl_pl_name'] = $TargetName;

        // view with no topvar and no leftmenu
        Template::legacyView(
            'galaxy.phalanx_body',
            $parse
        );
    }
}
